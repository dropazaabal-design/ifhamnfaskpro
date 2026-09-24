/**
 * مشغّل الاختبار في المتصفّح.
 *
 * كل ما يحدث هنا على جهاز الزائر: العرض، والتوقيت، والتصحيح، وميزان
 * الثقة، والحفظ. لا طلب شبكة واحد حتى النهاية.
 *
 * والعرض مختصر عمداً: سؤال في الشاشة، ونتيجة بجملة واحدة، وكل شرح خلف
 * «اقرأ أكثر». القارئ الذي يريد التفاصيل يجدها كاملة بنقرة.
 */
import type { Test, Item, Option } from '../lib/types.ts';
import { scoreTest, resultFor, intuitiveCount, type Answer, type ScaleResult } from '../lib/scoring.ts';
import { assessQuality } from '../lib/quality.ts';
import { saveResult, previousOf, reliableChange } from '../lib/profile.ts';
import { num, formatDate, countNoun, TRAPS } from '../lib/format.ts';
import { splitLead } from '../lib/text.ts';
import { drawShareCard } from './share-card.ts';
import { copy } from './copy.ts';
import { runDigitSpan, runSart, type TaskOutcome } from './tasks.ts';

const esc = ( s: string ) =>
	s.replace( /[&<>"']/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' } )[ c ]! );

const TONE: Record<string, string> = { low: 't-low', mid: 't-mid', high: 't-high', alert: 't-alert' };

/** رسائل صغيرة على الطريق: الاختبار الطويل يُترك في منتصفه بلا إحساس بالتقدّم. */
function milestone( index: number, total: number ): string {
	if ( total < 6 ) {
		return '';
	}

	if ( index === total - 1 ) {
		return 'آخر سؤال ✨';
	}

	if ( index === Math.floor( total / 2 ) ) {
		return 'نصّ الطريق 💪';
	}

	if ( total >= 20 && index === Math.floor( total * 0.8 ) ) {
		return 'قربت 🔥';
	}

	return '';
}

function boot( root: HTMLElement ) {
	const test: Test = JSON.parse( root.querySelector( '[data-test]' )!.textContent! );
	const site = root.dataset.site ?? location.origin;
	const store = `baseera-progress-${ test.slug }`;
	const screens = {
		start: root.querySelector<HTMLElement>( '[data-screen="start"]' )!,
		question: root.querySelector<HTMLElement>( '[data-screen="question"]' )!,
		result: root.querySelector<HTMLElement>( '[data-screen="result"]' )!,
		task: root.querySelector<HTMLElement>( '[data-screen="task"]' )!,
	};
	const el = {
		bar: root.querySelector<HTMLElement>( '[data-bar]' )!,
		count: root.querySelector<HTMLElement>( '[data-count]' )!,
		cheer: root.querySelector<HTMLElement>( '[data-cheer]' )!,
		question: root.querySelector<HTMLElement>( '[data-question]' )!,
		figure: root.querySelector<HTMLElement>( '[data-figure]' )!,
		options: root.querySelector<HTMLElement>( '[data-options]' )!,
		back: root.querySelector<HTMLButtonElement>( '[data-back]' )!,
		setup: root.querySelector<HTMLInputElement>( '[data-setup]' ),
	};

	let index = 0;
	let answers: Record<string, Answer> = {};
	let personal = '';
	let shownAt = 0;
	let advancing = 0;

	const persist = () => {
		try {
			sessionStorage.setItem( store, JSON.stringify( { index, answers, personal } ) );
		} catch {
			// بلا تخزين: يعمل الاختبار ولا يُستأنف بعد التحديث.
		}
	};

	const show = ( name: keyof typeof screens ) => {
		( Object.keys( screens ) as ( keyof typeof screens )[] ).forEach( ( k ) => ( screens[ k ].hidden = k !== name ) );
	};

	const optionsFor = ( item: Item ): Option[] => item.options ?? test.options ?? [];
	const textOf = ( item: Item ) => ( test.setup ? item.text.replaceAll( `{${ test.setup.token }}`, personal ) : item.text );

	const render = () => {
		const item = test.items[ index ];
		const total = test.items.length;

		el.bar.style.width = `${ ( index / total ) * 100 }%`;
		el.count.textContent = `${ num( index + 1 ) } / ${ num( total ) }`;
		el.cheer.textContent = milestone( index, total );
		el.question.textContent = textOf( item );
		el.figure.innerHTML = item.figure ?? '';
		el.back.disabled = index === 0;

		const current = answers[ item.id ]?.value;
		const name = `q-${ test.slug }-${ item.id }`;
		let html = '';

		el.options.classList.remove( 'figs', 'scale' );

		if ( test.kind === 'choice' ) {
			const figs = item.choices!.some( ( c ) => c.figure );
			el.options.classList.toggle( 'figs', figs );
			item.choices!.forEach( ( c, i ) => {
				// الرقم ظاهر فوق الرسم أيضاً، كي يشير إليه شرح الحلّ («الشكل 3»).
				const body = c.figure ? `<span class="k" aria-hidden="true">${ num( i + 1 ) }</span>${ c.figure }<span class="sr-only">${ esc( c.label ) }</span>` : `<span class="k">${ num( i + 1 ) }</span><span>${ esc( c.label ) }</span>`;
				html += `<label class="opt${ c.figure ? ' opt--fig' : '' }"><input type="radio" name="${ name }" value="${ i }"${ current === i ? ' checked' : '' }>${ body }</label>`;
			} );
		} else if ( test.optionsLayout === 'scale' ) {
			const opts = optionsFor( item );
			el.options.classList.add( 'scale' );
			html += '<div class="scale-row">';
			opts.forEach( ( o ) => {
				html += `<label class="opt opt--num"><input type="radio" name="${ name }" value="${ o.value }"${ current === o.value ? ' checked' : '' }><span>${ num( o.value ) }</span></label>`;
			} );
			html += `</div><div class="scale-ends"><span>${ esc( opts[ 0 ].label ) }</span><span>${ esc( opts[ opts.length - 1 ].label ) }</span></div>`;
		} else {
			optionsFor( item ).forEach( ( o, i ) => {
				html += `<label class="opt"><input type="radio" name="${ name }" value="${ o.value }"${ current === o.value ? ' checked' : '' }><span class="k">${ num( i + 1 ) }</span><span>${ esc( o.label ) }</span></label>`;
			} );
		}

		el.options.innerHTML = html;
		shownAt = performance.now();

		const first = el.options.querySelector<HTMLInputElement>( 'input:checked' ) ?? el.options.querySelector<HTMLInputElement>( 'input' );
		first?.focus( { preventScroll: true } );
	};

	const choose = ( value: number ) => {
		const item = test.items[ index ];
		const spent = performance.now() - shownAt;

		answers[ item.id ] = { value, ms: ( answers[ item.id ]?.ms ?? 0 ) + spent };
		persist();

		window.clearTimeout( advancing );
		advancing = window.setTimeout( () => {
			if ( index < test.items.length - 1 ) {
				index++;
				render();
			} else {
				finish();
			}
		}, 200 );
	};

	el.options.addEventListener( 'change', ( e ) => {
		const input = e.target as HTMLInputElement;

		if ( input.type === 'radio' ) {
			choose( Number( input.value ) );
		}
	} );

	el.back.addEventListener( 'click', () => {
		window.clearTimeout( advancing );

		if ( index > 0 ) {
			index--;
			render();
		}
	} );

	root.addEventListener( 'keydown', ( e ) => {
		if ( screens.question.hidden || e.altKey || e.ctrlKey || e.metaKey ) {
			return;
		}

		const n = Number( e.key );
		const inputs = el.options.querySelectorAll<HTMLInputElement>( 'input' );

		// في مقياس 0–10 يعني المفتاح 0 البديل الأوّل؛ في غيره المفتاح 1.
		const at = test.optionsLayout === 'scale' ? n : n - 1;

		if ( e.key.length === 1 && ! Number.isNaN( n ) && at >= 0 && at < inputs.length ) {
			e.preventDefault();
			inputs[ at ].checked = true;
			choose( Number( inputs[ at ].value ) );
		}
	} );

	const startBtn = root.querySelector<HTMLButtonElement>( '[data-start]' )!;

	if ( el.setup ) {
		const sync = () => ( startBtn.disabled = el.setup!.value.trim().length < 2 );
		el.setup.addEventListener( 'input', sync );
		sync();
	}

	const start = async ( resume = false ) => {
		if ( test.kind === 'task' ) {
			show( 'task' );
			root.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			finish( test.task === 'digit-span' ? await runDigitSpan( screens.task ) : await runSart( screens.task ) );

			return;
		}

		if ( ! resume ) {
			index = 0;
			answers = {};
			personal = el.setup?.value.trim().slice( 0, 60 ) ?? '';
		}

		show( 'question' );
		render();
		root.scrollIntoView( { behavior: 'smooth', block: 'start' } );
	};

	startBtn.addEventListener( 'click', () => start() );

	// استئناف ما لم يكتمل.
	try {
		const saved = JSON.parse( sessionStorage.getItem( store ) ?? 'null' );

		if ( saved && saved.answers && Object.keys( saved.answers ).length && saved.index < test.items.length ) {
			const note = root.querySelector<HTMLElement>( '[data-resume]' )!;
			note.hidden = false;
			root.querySelector( '[data-resume-go]' )!.addEventListener( 'click', () => {
				index = saved.index;
				answers = saved.answers;
				personal = saved.personal ?? '';
				start( true );
			} );
		}
	} catch {
		// لا تخزين.
	}

	/* ─────────────── النتيجة ─────────────── */

	const fmt = ( s: ScaleResult, v: number ) => num( s.mean ? v.toFixed( 2 ) : String( Math.round( v ) ) );

	function meter( s: ScaleResult ) {
		const pos = ( v: number ) => ( ( v - s.min ) / ( s.max - s.min ) ) * 100;
		const ci = s.uncalibrated || s.high <= s.low ? '' : `<span class="ci" style="right:${ pos( s.low ) }%;width:${ pos( s.high ) - pos( s.low ) }%"></span>`;
		const [ lead, rest ] = splitLead( s.band.text );
		const range = s.uncalibrated ? '' : `<span class="rng-chip">📏 ${ fmt( s, s.low ) }–${ fmt( s, s.high ) }</span>`;

		return `
			<div class="scale ${ TONE[ s.band.tone ] }">
				<h3><span>${ esc( s.name ) }</span><span class="num">${ fmt( s, s.score ) }</span></h3>
				<div class="chips"><span class="band-label">${ esc( s.band.label ) }</span>${ range }</div>
				<div class="meter" role="img" aria-label="${ esc( `${ s.name }: ${ fmt( s, s.score ) } من ${ fmt( s, s.min ) } إلى ${ fmt( s, s.max ) }` ) }">
					${ ci }<span class="pt" style="right:${ pos( s.score ) }%;transform:translate(50%,-50%)"></span>
				</div>
				<div class="meter-axis"><span class="num">${ fmt( s, s.min ) }</span><span class="num">${ fmt( s, s.max ) }</span></div>
				<p class="lead-line">${ esc( lead ) }</p>
				${ rest ? `<details class="more"><summary>اقرأ أكثر</summary><p>${ esc( rest ) }</p></details>` : '' }
			</div>`;
	}

	function typologyCard( scales: ScaleResult[] ) {
		const t = test.typology;

		if ( ! t ) {
			return '';
		}

		const x = scales.find( ( s ) => s.id === t.x );
		const y = scales.find( ( s ) => s.id === t.y );

		if ( ! x || ! y ) {
			return '';
		}

		// فوق منتصف المقياس «مرتفع»؛ المنتصف نفسه («بين بين») لا يُحسب ميلاً.
		const key = `${ x.score > t.cut ? 'high' : 'low' }${ y.score > t.cut ? 'high' : 'low' }` as keyof typeof t.cells;
		const cell = t.cells[ key ];

		return `<div class="typology"><span class="kick">الأقرب إليك</span><h3>${ esc( cell.label ) }</h3><p>${ esc( cell.text ) }</p><p class="fine">الأنماط الأربعة تبسيط: البُعدان أدقّ من النمط، ومن يقترب من الحدّ بينها لا يُصنَّف بثقة.</p></div>`;
	}

	function trustPanel( scales: ScaleResult[], flags: { text: string }[], savedAt: string, shaky: boolean ) {
		const items: string[] = [];
		const calibrated = scales.filter( ( s ) => ! s.uncalibrated );
		const chips: string[] = [ '<span class="tchip">⚖️ تقدير لا حكم</span>' ];

		items.push( '<li><span class="i">⚖️</span><span><b>هذا تقدير لا حكم.</b> أيّ اختبار نفسي يقيس بهامش خطأ، ومن يعطيك رقماً بلا هامش يخفي عنك شيئاً.</span></li>' );

		if ( calibrated.length ) {
			chips.push( '<span class="tchip">📏 له هامش خطأ</span>' );
			items.push( '<li><span class="i">📏</span><span>الشريط الفاتح حول درجتك هو <b>النطاق المرجّح</b>: لو أعدت الاختبار، ففي نحو 9 من 10 مرّات تقع درجتك الحقيقية داخله. يُحسب من ثبات المقياس المنشور — كلما قلّ ثباته اتّسع النطاق.</span></li>' );
		} else {
			chips.push( '<span class="tchip">📏 بلا نطاق</span>' );
			items.push( '<li><span class="i">📏</span><span>لا نعرض نطاق ثقة هنا: البنود لم يُقَس ثباتها بعد، واختراع رقم لها أسوأ من غيابه.</span></li>' );
		}

		if ( test.kind === 'task' ) {
			if ( flags.length ) {
				chips.push( '<span class="tchip warn">⚠️ أداء غير معتاد</span>' );
				flags.forEach( ( f ) => items.push( `<li class="caution-line"><span class="i">⚠️</span><span>${ esc( f.text ) }</span></li>` ) );
			} else {
				chips.push( '<span class="tchip ok">✅ المهمّة اكتملت</span>' );
				items.push( '<li><span class="i">✅</span><span>اكتملت المهمّة بلا علامة على سوء فهم أو انقطاع. أداؤك في مهمّة واحدة يتأثّر بالتعب والتشتّت والجهاز: أعدها في وقت آخر لترى إن ثبت.</span></li>' );
			}
		}

		if ( test.kind === 'likert' ) {
			if ( flags.length ) {
				chips.push( '<span class="tchip warn">⚠️ إجابات متسرّعة</span>' );
				flags.forEach( ( f ) => items.push( `<li class="caution-line"><span class="i">⚠️</span><span>${ esc( f.text ) }</span></li>` ) );
			} else {
				chips.push( '<span class="tchip ok">✅ إجاباتك متّسقة</span>' );
				items.push( '<li><span class="i">✅</span><span>إجاباتك متّسقة: لا سرعة زائدة، ولا الإجابة نفسها على كل شيء، ولا موافقة على العبارة ونقيضها.</span></li>' );
			}
		}

		if ( scales.some( ( s ) => ! s.verified && ! s.uncalibrated ) ) {
			items.push( '<li><span class="i">🔎</span><span>بعض قيم الثبات المستخدمة تقديرية من الأدبيات، ومصادرها في <a href="/methodology/" style="color:var(--color-primary)">صفحة المنهجية</a>.</span></li>' );
		}

		const prev = previousOf( test.slug, savedAt );

		if ( prev ) {
			chips.push( '<span class="tchip">📈 مقارنة بمرّتك السابقة</span>' );
			const lines = scales
				.map( ( s ) => {
					const p = prev.scales.find( ( x ) => x.id === s.id );

					if ( ! p ) {
						return '';
					}

					const c = reliableChange( p.score, s.score, s.sem, s.higherIs );
					const d = c.delta === 0 ? 'لا تغيّر' : `${ c.delta > 0 ? '+' : '−' }${ num( Math.abs( Math.round( c.delta * 100 ) / 100 ) ) }`;
					// مؤشّر التغيّر يفترض أن القياسين كليهما صالحان. إن رصد الميزان
					// إجابات متسرّعة في أيّ منهما، فالحساب صحيح والاستنتاج لا.
					const unreliableRun = shaky || prev.quality === 'caution';
					const verdict = c.rci === null
						? 'لا نستطيع الحكم: البنود غير معايَرة.'
						: unreliableRun
							? 'لا نحكم: رصد ميزان الثقة إجابات متسرّعة في إحدى المرّتين، والمقارنة بها غير موثوقة.'
							: c.reliable
								? `تغيّر موثوق${ c.direction === 'better' ? ' نحو الأفضل' : c.direction === 'worse' ? ' يستحقّ الانتباه' : '' } — أكبر من هامش خطأ القياس.`
								: 'ضمن هامش الخطأ: لا يمكن القول إنه تغيّر حقيقي.';

					return `<div class="change">${ esc( s.name ) }: <b>${ d }</b> منذ ${ formatDate( prev.at, 'short' ) } — ${ verdict }</div>`;
				} )
				.join( '' );

			items.push( `<li><span class="i">📈</span><span><b>مقارنة بمرّتك السابقة</b> (مؤشّر التغيّر الموثوق، Jacobson & Truax 1991):${ lines }</span></li>` );
		}

		// مفتوح تلقائياً حين يكون فيه ما يجب ألّا يفوت: تسرّع أو مقارنة.
		const open = shaky || Boolean( prev );

		return `<details class="trust"${ open ? ' open' : '' }><summary><span class="t-title">ميزان الثقة</span><span class="tchips">${ chips.join( '' ) }</span></summary><ul>${ items.join( '' ) }</ul></details>`;
	}

	function answersReview() {
		if ( test.kind !== 'choice' ) {
			return '';
		}

		const rows = test.items
			.map( ( item, i ) => {
				const a = answers[ item.id ];
				const chosen = a ? item.choices![ a.value ] : undefined;
				const right = item.choices!.find( ( c ) => c.correct )!;
				const ok = Boolean( chosen?.correct );
				const trap = chosen?.intuitive ? ' — وهو الجواب الذي يقع فيه أغلب الناس' : '';

				return `<details><summary><span class="mark ${ ok ? 'ok' : 'no' }">${ ok ? '✓' : '✗' }</span><span>${ num( i + 1 ) }. ${ esc( item.text ) }</span></summary><div class="body">جوابك: <b>${ esc( chosen?.label ?? '—' ) }</b>${ trap }<br>الصحيح: <b>${ esc( right.label ) }</b><br>${ esc( item.solution ?? '' ) }</div></details>`;
			} )
			.join( '' );

		const traps = intuitiveCount( test, answers );
		const trapItems = test.items.filter( ( i ) => i.choices?.some( ( c ) => c.intuitive ) ).length;
		const trapLine = trapItems
			? `<p class="trap-line">🪤 ${ traps ? `وقعت في <b>${ countNoun( traps, TRAPS, 'gen' ) }</b> من ${ num( trapItems ) }` : `لم تقع في أيّ فخّ من ${ num( trapItems ) }` }</p>`
			: '';

		return `${ trapLine }<details class="solutions"><summary>الحلول (${ num( test.items.length ) })</summary><div class="answers">${ rows }</div></details>`;
	}

	function finish( outcome?: TaskOutcome ) {
		// المهمّة تحسب درجاتها بنفسها، ثم تمرّ بالعرض وميزان الثقة نفسيهما.
		const scales = outcome ? test.subscales.map( ( s ) => resultFor( s, outcome.scores[ s.id ] ?? 0 ) ) : scoreTest( test, answers );
		const quality = outcome
			? { level: outcome.caution ? ( 'caution' as const ) : ( 'good' as const ), flags: outcome.caution ? [ { id: 'task' as const, text: outcome.caution } ] : [] }
			: assessQuality( test, answers );
		const at = new Date().toISOString();
		const url = `${ site }/tests/${ test.slug }/`;
		const alert = scales.some( ( s ) => s.band.tone === 'alert' );
		const title = test.setup && personal ? `${ test.title } — ${ personal }` : test.title;

		try {
			sessionStorage.removeItem( store );
		} catch {
			// لا شيء.
		}

		const basis = test.instrument.license === 'original' ? 'بنود أصلية مع شرح كل حلّ' : 'مبني على مقياس منشور';
		const shareText = test.sensitive
			? `«${ test.title }» على بصيرة — ${ basis }، ويخبرك بحدوده بصدق:\n${ url }`
			: `أخذت «${ test.title }» على بصيرة: ${ scales.map( ( s ) => `${ s.name } — ${ s.band.label }` ).join( '، ' ) }.\n${ basis }، ويخبرك بحدوده بصدق. جرّبه:\n${ url }`;

		screens.result.innerHTML = `
			<div class="res">
				<h2>نتيجتك</h2>
				${ test.sensitive && alert ? '<p class="caution" style="margin-top:12px"><span>نتيجتك في المستوى الذي يُنصح عنده بالحديث مع مختصّ. <a href="#crisis-h" style="color:var(--color-primary);font-weight:700">موارد الدعم أدناه</a>.</span></p>' : '' }
				${ scales.map( meter ).join( '' ) }
				${ outcome ? `<ul class="task-details">${ outcome.details.map( ( d ) => `<li>${ esc( d ) }</li>` ).join( '' ) }</ul>` : '' }
				${ typologyCard( scales ) }
				${ trustPanel( scales, quality.flags, at, quality.level === 'caution' ) }
				${ answersReview() }
				<div class="share">
					<a class="btn btn--wa wide" href="https://wa.me/?text=${ encodeURIComponent( shareText ) }" rel="noopener" target="_blank">
						<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.3-.5 0-1 .2-3.2-.7-2.7-1.1-4.4-3.9-4.5-4-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.4.6-.4.4c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.1 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1.1c.2-.3.4-.2.7-.1l1.9.9c.3.1.5.2.5.3.1.2.1.6-.1 1.2Z"/></svg>
						شارك على واتساب
					</a>
					<a class="btn btn--ghost" href="https://x.com/intent/post?text=${ encodeURIComponent( shareText ) }" rel="noopener" target="_blank">X</a>
					<button type="button" class="btn btn--ghost" data-copy-link>انسخ الرابط</button>
					${ test.sensitive ? '' : '<button type="button" class="btn btn--ghost wide" data-story>📸 صورة للقصص</button>' }
				</div>
				<div class="share" style="margin-top:9px">
					<button type="button" class="btn btn--primary" data-save>احفظ في ملفّي</button>
					<button type="button" class="btn btn--ghost" data-retake>أعد الاختبار</button>
				</div>
				<p class="save-note">🔒 الحفظ على جهازك وحده. تحذفه من <a href="/me/" style="color:var(--color-primary)">ملفّي</a> متى شئت.</p>
			</div>`;

		show( 'result' );
		el.bar.style.width = '100%';
		screens.result.focus( { preventScroll: true } );
		root.scrollIntoView( { behavior: 'smooth', block: 'start' } );

		screens.result.querySelector( '[data-copy-link]' )?.addEventListener( 'click', () => copy( url ) );
		screens.result.querySelector( '[data-retake]' )?.addEventListener( 'click', () => {
			show( 'start' );
			root.scrollIntoView( { behavior: 'smooth', block: 'start' } );
		} );

		screens.result.querySelector<HTMLButtonElement>( '[data-save]' )?.addEventListener( 'click', ( e ) => {
			const btn = e.currentTarget as HTMLButtonElement;
			const ok = saveResult( {
				slug: test.slug,
				title,
				at,
				quality: quality.level,
				scales: scales.map( ( s ) => ( {
					id: s.id,
					name: s.name,
					score: s.score,
					min: s.min,
					max: s.max,
					low: s.low,
					high: s.high,
					sem: s.sem,
					higherIs: s.higherIs,
					uncalibrated: s.uncalibrated,
					mean: s.mean,
					band: s.band.label,
				} ) ),
			} );

			btn.disabled = true;
			btn.textContent = ok ? 'حُفظ ✓' : 'تعذّر الحفظ في هذا المتصفّح';
		} );

		screens.result.querySelector<HTMLButtonElement>( '[data-story]' )?.addEventListener( 'click', async ( e ) => {
			const btn = e.currentTarget as HTMLButtonElement;
			btn.disabled = true;
			const blob = await drawShareCard( title, scales, site );
			btn.disabled = false;

			if ( ! blob ) {
				return;
			}

			const file = new File( [ blob ], `baseera-${ test.slug }.png`, { type: 'image/png' } );

			if ( navigator.canShare?.( { files: [ file ] } ) ) {
				try {
					await navigator.share( { files: [ file ], text: url } );

					return;
				} catch {
					// ألغى المستخدم المشاركة: نعرض التنزيل بديلاً.
				}
			}

			const a = document.createElement( 'a' );
			a.href = URL.createObjectURL( blob );
			a.download = file.name;
			a.click();
			window.setTimeout( () => URL.revokeObjectURL( a.href ), 4000 );
		} );
	}
}

document.querySelectorAll<HTMLElement>( '[data-runner]' ).forEach( boot );
