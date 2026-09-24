/**
 * مشغّل الاختبار في المتصفّح.
 *
 * كل ما يحدث هنا على جهاز الزائر: العرض، والتوقيت، والتصحيح، وميزان
 * الثقة، والحفظ. لا طلب شبكة واحد حتى النهاية.
 */
import type { Test, Item, Option } from '../lib/types.ts';
import { scoreTest, intuitiveCount, type Answer, type ScaleResult } from '../lib/scoring.ts';
import { assessQuality } from '../lib/quality.ts';
import { saveResult, previousOf, reliableChange } from '../lib/profile.ts';
import { toArabicDigits, formatDate } from '../lib/format.ts';
import { drawShareCard } from './share-card.ts';
import { copy } from './copy.ts';

const esc = ( s: string ) =>
	s.replace( /[&<>"']/g, ( c ) => ( { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' } )[ c ]! );

const TONE: Record<string, string> = { low: 't-low', mid: 't-mid', high: 't-high', alert: 't-alert' };

function boot( root: HTMLElement ) {
	const test: Test = JSON.parse( root.querySelector( '[data-test]' )!.textContent! );
	const site = root.dataset.site ?? location.origin;
	const store = `baseera-progress-${ test.slug }`;
	const screens = {
		start: root.querySelector<HTMLElement>( '[data-screen="start"]' )!,
		question: root.querySelector<HTMLElement>( '[data-screen="question"]' )!,
		result: root.querySelector<HTMLElement>( '[data-screen="result"]' )!,
	};
	const el = {
		bar: root.querySelector<HTMLElement>( '[data-bar]' )!,
		count: root.querySelector<HTMLElement>( '[data-count]' )!,
		question: root.querySelector<HTMLElement>( '[data-question]' )!,
		figure: root.querySelector<HTMLElement>( '[data-figure]' )!,
		options: root.querySelector<HTMLElement>( '[data-options]' )!,
		back: root.querySelector<HTMLButtonElement>( '[data-back]' )!,
	};

	let index = 0;
	let answers: Record<string, Answer> = {};
	let shownAt = 0;
	let advancing = 0;

	const persist = () => {
		try {
			sessionStorage.setItem( store, JSON.stringify( { index, answers } ) );
		} catch {
			// بلا تخزين: يعمل الاختبار ولا يُستأنف بعد التحديث.
		}
	};

	const show = ( name: keyof typeof screens ) => {
		( Object.keys( screens ) as ( keyof typeof screens )[] ).forEach( ( k ) => ( screens[ k ].hidden = k !== name ) );
	};

	const optionsFor = ( item: Item ): Option[] => item.options ?? test.options ?? [];

	const render = () => {
		const item = test.items[ index ];
		const total = test.items.length;

		el.bar.style.width = `${ ( index / total ) * 100 }%`;
		el.count.textContent = `${ toArabicDigits( index + 1 ) } من ${ toArabicDigits( total ) }`;
		el.question.textContent = item.text;
		el.figure.innerHTML = item.figure ?? '';
		el.back.disabled = index === 0;

		const current = answers[ item.id ]?.value;
		const name = `q-${ test.slug }-${ item.id }`;
		let html = '';

		if ( test.kind === 'choice' ) {
			const figs = item.choices!.some( ( c ) => c.figure );
			el.options.classList.toggle( 'figs', figs );
			item.choices!.forEach( ( c, i ) => {
				const body = c.figure ? `${ c.figure }<span class="sr-only">${ esc( c.label ) }</span>` : `<span class="k">${ toArabicDigits( i + 1 ) }</span><span>${ esc( c.label ) }</span>`;
				html += `<label class="opt${ c.figure ? ' opt--fig' : '' }"><input type="radio" name="${ name }" value="${ i }"${ current === i ? ' checked' : '' }>${ body }</label>`;
			} );
		} else {
			el.options.classList.remove( 'figs' );
			optionsFor( item ).forEach( ( o, i ) => {
				html += `<label class="opt"><input type="radio" name="${ name }" value="${ o.value }"${ current === o.value ? ' checked' : '' }><span class="k">${ toArabicDigits( i + 1 ) }</span><span>${ esc( o.label ) }</span></label>`;
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

		if ( n >= 1 && n <= inputs.length ) {
			e.preventDefault();
			inputs[ n - 1 ].checked = true;
			choose( Number( inputs[ n - 1 ].value ) );
		}
	} );

	const start = ( resume = false ) => {
		if ( ! resume ) {
			index = 0;
			answers = {};
		}

		show( 'question' );
		render();
		root.scrollIntoView( { behavior: 'smooth', block: 'start' } );
	};

	root.querySelector( '[data-start]' )!.addEventListener( 'click', () => start() );

	// استئناف ما لم يكتمل.
	try {
		const saved = JSON.parse( sessionStorage.getItem( store ) ?? 'null' );

		if ( saved && saved.answers && Object.keys( saved.answers ).length && saved.index < test.items.length ) {
			const note = root.querySelector<HTMLElement>( '[data-resume]' )!;
			note.hidden = false;
			root.querySelector( '[data-resume-go]' )!.addEventListener( 'click', () => {
				index = saved.index;
				answers = saved.answers;
				start( true );
			} );
		}
	} catch {
		// لا تخزين.
	}

	/* ─────────────── النتيجة ─────────────── */

	function meter( s: ScaleResult ) {
		const pos = ( v: number ) => ( ( v - s.min ) / ( s.max - s.min ) ) * 100;
		const mean = s.score % 1 !== 0 || s.max <= 5;
		const f = ( v: number ) => toArabicDigits( mean ? v.toFixed( 2 ) : String( Math.round( v ) ) );
		const ci = s.uncalibrated || s.high <= s.low ? '' : `<span class="ci" style="right:${ pos( s.low ) }%;width:${ pos( s.high ) - pos( s.low ) }%"></span>`;
		const range = s.uncalibrated
			? '<p class="band-range" style="font-size:12px;color:var(--color-text-muted);margin-top:6px">بلا نطاق ثقة: البنود لم تُعايَر بعد.</p>'
			: `<p class="band-range" style="font-size:12px;color:var(--color-text-muted);margin-top:6px">النطاق المرجّح لدرجتك: <b class="num">${ f( s.low ) }–${ f( s.high ) }</b></p>`;

		return `
			<div class="scale ${ TONE[ s.band.tone ] }">
				<h3><span>${ esc( s.name ) }</span><span class="num">${ f( s.score ) }</span></h3>
				<span class="band-label">${ esc( s.band.label ) }</span>
				<div class="meter" role="img" aria-label="${ esc( `${ s.name }: ${ f( s.score ) } من مدى ${ f( s.min ) } إلى ${ f( s.max ) }` ) }">
					${ ci }<span class="pt" style="right:${ pos( s.score ) }%;transform:translate(50%,-50%)"></span>
				</div>
				<div class="meter-axis"><span class="num">${ f( s.min ) }</span><span class="num">${ f( s.max ) }</span></div>
				${ range }
				<p>${ esc( s.band.text ) }</p>
			</div>`;
	}

	function trustPanel( scales: ScaleResult[], flags: { text: string }[], savedAt: string, shaky: boolean ) {
		const items: string[] = [];
		const calibrated = scales.filter( ( s ) => ! s.uncalibrated );

		items.push( '<li><span class="i">⚖️</span><span><b>هذا تقدير لا حكم.</b> أيّ اختبار نفسي يقيس بهامش خطأ، ومن يعطيك رقماً بلا هامش يخفي عنك شيئاً.</span></li>' );

		if ( calibrated.length ) {
			items.push( '<li><span class="i">📏</span><span>الشريط الفاتح حول درجتك هو <b>النطاق المرجّح</b>: لو أعدت الاختبار، ففي نحو ٩ من ١٠ مرّات تقع درجتك الحقيقية داخله. يُحسب من ثبات المقياس المنشور — كلما قلّ ثباته اتّسع النطاق.</span></li>' );
		} else {
			items.push( '<li><span class="i">📏</span><span>لا نعرض نطاق ثقة لهذا الاختبار: بنوده أصلية ولم يُقَس ثباتها بعد، واختراع رقم لها أسوأ من غيابه.</span></li>' );
		}

		if ( test.kind === 'likert' ) {
			if ( flags.length ) {
				flags.forEach( ( f ) => items.push( `<li class="caution-line"><span class="i">⚠️</span><span>${ esc( f.text ) }</span></li>` ) );
			} else {
				items.push( '<li><span class="i">✅</span><span>إجاباتك متّسقة: لم نرصد سرعة زائدة، ولا الإجابة نفسها على كل شيء، ولا موافقة على العبارة ونقيضها.</span></li>' );
			}
		}

		if ( scales.some( ( s ) => ! s.verified && ! s.uncalibrated ) ) {
			items.push( '<li><span class="i">🔎</span><span>بعض قيم الثبات المستخدمة في النطاق تقديرية من الأدبيات، ومصادرها كلّها في <a href="/methodology/" style="color:var(--color-primary)">صفحة المنهجية</a>.</span></li>' );
		}

		// مقارنة بالمرّة السابقة، بمؤشّر التغيّر الموثوق.
		const prev = previousOf( test.slug, savedAt );

		if ( prev ) {
			const lines = scales
				.map( ( s ) => {
					const p = prev.scales.find( ( x ) => x.id === s.id );

					if ( ! p ) {
						return '';
					}

					const c = reliableChange( p.score, s.score, s.sem, s.higherIs );
					const d = c.delta === 0 ? 'لا تغيّر' : `${ c.delta > 0 ? '+' : '−' }${ toArabicDigits( Math.abs( Math.round( c.delta * 100 ) / 100 ) ) }`;
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

		return `<section class="trust" aria-labelledby="trust-h-${ test.slug }"><h3 id="trust-h-${ test.slug }"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3v18M5 7h14M7 7l-3 6a3 3 0 0 0 6 0L7 7Zm10 0-3 6a3 3 0 0 0 6 0l-3-6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>ميزان الثقة</h3><ul>${ items.join( '' ) }</ul></section>`;
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
				const trap = chosen?.intuitive ? ' — وهو الجواب الحدسي الذي يقع فيه أغلب الناس' : '';

				return `<details><summary><span class="mark ${ ok ? 'ok' : 'no' }">${ ok ? '✓' : '✗' }</span><span>${ toArabicDigits( i + 1 ) }. ${ esc( item.text ) }</span></summary><div class="body">جوابك: <b>${ esc( chosen?.label ?? '—' ) }</b>${ trap }<br>الصحيح: <b>${ esc( right.label ) }</b><br>${ esc( item.solution ?? '' ) }</div></details>`;
			} )
			.join( '' );

		const traps = intuitiveCount( test, answers );
		const trapLine = test.items.some( ( i ) => i.choices?.some( ( c ) => c.intuitive ) )
			? `<p style="font-size:14px;margin-top:14px">وقعت في <b class="num">${ toArabicDigits( traps ) }</b> من الفخاخ الحدسية. كل لغز صُمّم ليقفز فيه جواب خاطئ أوّلاً.</p>`
			: '';

		return `${ trapLine }<h3 style="font-size:15px;font-weight:800;margin-top:18px">الحلول</h3><div class="answers">${ rows }</div>`;
	}

	function finish() {
		const scales = scoreTest( test, answers );
		const quality = assessQuality( test, answers );
		const at = new Date().toISOString();
		const url = `${ site }/tests/${ test.slug }/`;
		const alert = scales.some( ( s ) => s.band.tone === 'alert' );

		try {
			sessionStorage.removeItem( store );
		} catch {
			// لا شيء.
		}

		const shareText = test.sensitive
			? `مقياس «${ test.title }» على بصيرة — مبني على مقياس منشور، ويخبرك بحدوده بصدق:\n${ url }`
			: `أخذت «${ test.title }» على بصيرة: ${ scales.map( ( s ) => `${ s.name } — ${ s.band.label }` ).join( '، ' ) }.\nمبني على مقياس منشور، ويعطيك هامش الخطأ مع النتيجة. جرّبه:\n${ url }`;

		const html = `
			<div class="res">
				<h2>نتيجتك</h2>
				${ test.sensitive && alert ? '<p class="caution" style="margin-top:12px"><span>نتيجتك في المستوى الذي يُنصح عنده بالحديث مع مختصّ. <a href="#crisis-h" style="color:var(--color-primary);font-weight:700">موارد الدعم أدناه</a>.</span></p>' : '' }
				${ scales.map( meter ).join( '' ) }
				${ trustPanel( scales, quality.flags, at, quality.level === 'caution' ) }
				${ answersReview() }
				<div class="share">
					<a class="btn btn--wa wide" href="https://wa.me/?text=${ encodeURIComponent( shareText ) }" rel="noopener" target="_blank">
						<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.3-.5 0-1 .2-3.2-.7-2.7-1.1-4.4-3.9-4.5-4-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .5l-.4.6-.4.4c-.1.2-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.1 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1.1c.2-.3.4-.2.7-.1l1.9.9c.3.1.5.2.5.3.1.2.1.6-.1 1.2Z"/></svg>
						شارك على واتساب
					</a>
					<a class="btn btn--ghost" href="https://x.com/intent/post?text=${ encodeURIComponent( shareText ) }" rel="noopener" target="_blank">شارك على X</a>
					<button type="button" class="btn btn--ghost" data-copy-link>انسخ الرابط</button>
					${ test.sensitive ? '' : '<button type="button" class="btn btn--ghost wide" data-story>صورة للقصص (سناب وتيك توك)</button>' }
				</div>
				<div class="share" style="margin-top:9px">
					<button type="button" class="btn btn--primary" data-save>احفظ في ملفّي</button>
					<button type="button" class="btn btn--ghost" data-retake>أعد الاختبار</button>
				</div>
				<p class="save-note">الحفظ على هذا الجهاز وحده، ولا يُرسل شيء إلى أي خادم. تحذفه من <a href="/me/" style="color:var(--color-primary)">ملفّي</a> متى شئت.</p>
			</div>`;

		screens.result.innerHTML = html;
		show( 'result' );
		el.bar.style.width = '100%';
		screens.result.focus( { preventScroll: true } );
		root.scrollIntoView( { behavior: 'smooth', block: 'start' } );

		screens.result.querySelector( '[data-copy-link]' )?.addEventListener( 'click', () => copy( url ) );

		screens.result.querySelector( '[data-retake]' )?.addEventListener( 'click', () => start() );

		screens.result.querySelector<HTMLButtonElement>( '[data-save]' )?.addEventListener( 'click', ( e ) => {
			const btn = e.currentTarget as HTMLButtonElement;
			const ok = saveResult( {
				slug: test.slug,
				title: test.title,
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
					band: s.band.label,
				} ) ),
			} );

			btn.disabled = true;
			btn.textContent = ok ? 'حُفظ في ملفّي ✓' : 'تعذّر الحفظ في هذا المتصفّح';
		} );

		screens.result.querySelector<HTMLButtonElement>( '[data-story]' )?.addEventListener( 'click', async ( e ) => {
			const btn = e.currentTarget as HTMLButtonElement;
			btn.disabled = true;
			const blob = await drawShareCard( test.title, scales, site );
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

