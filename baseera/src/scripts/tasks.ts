/**
 * المهامّ التفاعلية: تقيس بالأداء لا بالسؤال.
 *
 * مدى الأرقام (للذاكرة العاملة) ومهمّة SART (للانتباه المستمرّ) نسختان
 * مختصرتان من مهمّتين كلاسيكيتين. كل شيء على جهاز الزائر، والتوقيت
 * بـ performance.now، ولا يغادر شيء المتصفّح.
 */
import { num, countNoun, DIGITS, TIMES } from '../lib/format.ts';

export interface TaskOutcome {
	scores: Record<string, number>;
	/** سطور تُعرض مع النتيجة: تفاصيل الأداء كما حدثت. */
	details: string[];
	/** تنبيه لميزان الثقة حين يبدو الأداء غير صالح للقراءة. */
	caution?: string;
}

const sleep = ( ms: number ) => new Promise<void>( ( r ) => window.setTimeout( r, ms ) );

function shuffle<T>( a: T[] ): T[] {
	const out = [ ...a ];

	for ( let i = out.length - 1; i > 0; i-- ) {
		const j = Math.floor( Math.random() * ( i + 1 ) );
		[ out[ i ], out[ j ] ] = [ out[ j ], out[ i ] ];
	}

	return out;
}

function waitClick( el: HTMLElement ): Promise<void> {
	return new Promise( ( r ) => el.addEventListener( 'click', () => r(), { once: true } ) );
}

/* ─────────────── مدى الأرقام ─────────────── */

/**
 * التسلسل يبدأ بثلاثة أرقام (وبرقمين في العكسي) ويطول رقماً كل مرحلة.
 * لكل طول محاولتان، وتتوقّف المرحلة حين تفشل المحاولتان معاً — كما في
 * الإجراء المعتاد. الدرجة أطول تسلسل أُعيد صحيحاً مرّة على الأقل.
 */
export async function runDigitSpan( box: HTMLElement ): Promise<TaskOutcome> {
	box.innerHTML = `
		<div class="task">
			<p class="task__phase" data-phase></p>
			<div class="task__stage num" data-stage aria-live="assertive"></div>
			<div class="task__input" data-input hidden>
				<output class="task__typed num" data-typed aria-live="polite"></output>
				<div class="keypad" role="group" aria-label="لوحة الأرقام">
					${ [ 1, 2, 3, 4, 5, 6, 7, 8, 9 ].map( ( d ) => `<button type="button" class="key num" data-key="${ d }">${ d }</button>` ).join( '' ) }
					<button type="button" class="key key--del" data-del aria-label="احذف آخر رقم">⌫</button>
					<button type="button" class="key key--ok" data-ok>تمّ</button>
				</div>
			</div>
			<div class="task__gate" data-gate hidden>
				<p data-gate-text></p>
				<button type="button" class="btn btn--primary btn--block" data-gate-go>تابع</button>
			</div>
		</div>`;

	const phaseEl = box.querySelector<HTMLElement>( '[data-phase]' )!;
	const stage = box.querySelector<HTMLElement>( '[data-stage]' )!;
	const input = box.querySelector<HTMLElement>( '[data-input]' )!;
	const typed = box.querySelector<HTMLElement>( '[data-typed]' )!;
	const gate = box.querySelector<HTMLElement>( '[data-gate]' )!;

	let buffer = '';
	let submit: ( ( v: string ) => void ) | null = null;

	const renderTyped = () => ( typed.textContent = buffer.split( '' ).join( ' ' ) || '—' );
	const press = ( d: string ) => {
		if ( submit && buffer.length < 12 ) {
			buffer += d;
			renderTyped();
		}
	};
	const del = () => {
		buffer = buffer.slice( 0, -1 );
		renderTyped();
	};
	const done = () => {
		if ( submit && buffer.length ) {
			const s = submit;
			submit = null;
			s( buffer );
		}
	};

	box.querySelectorAll<HTMLButtonElement>( '[data-key]' ).forEach( ( b ) => b.addEventListener( 'click', () => press( b.dataset.key! ) ) );
	box.querySelector( '[data-del]' )!.addEventListener( 'click', del );
	box.querySelector( '[data-ok]' )!.addEventListener( 'click', done );

	const onKey = ( e: KeyboardEvent ) => {
		if ( ! submit ) {
			return;
		}

		if ( /^[1-9]$/.test( e.key ) ) {
			press( e.key );
		} else if ( e.key === 'Backspace' ) {
			del();
		} else if ( e.key === 'Enter' ) {
			done();
		} else {
			return;
		}

		e.preventDefault();
	};
	document.addEventListener( 'keydown', onKey );

	const collect = () =>
		new Promise<string>( ( r ) => {
			buffer = '';
			renderTyped();
			input.hidden = false;
			submit = r;
		} );

	const showGate = async ( text: string, go = 'تابع' ) => {
		stage.hidden = true;
		input.hidden = true;
		gate.hidden = false;
		gate.querySelector( '[data-gate-text]' )!.textContent = text;
		const btn = gate.querySelector<HTMLButtonElement>( '[data-gate-go]' )!;
		btn.textContent = go;
		btn.focus( { preventScroll: true } );
		await waitClick( btn );
		gate.hidden = true;
		stage.hidden = false;
	};

	async function phase( backward: boolean, from: number, to: number ): Promise<number> {
		let best = 0;

		for ( let len = from; len <= to; len++ ) {
			let passed = 0;

			for ( let t = 0; t < 2; t++ ) {
				phaseEl.textContent = `${ backward ? 'بالعكس' : 'بالترتيب' } · ${ countNoun( len, DIGITS ) } · المحاولة ${ num( t + 1 ) } من 2`;
				const seq = shuffle( [ 1, 2, 3, 4, 5, 6, 7, 8, 9 ] ).slice( 0, len ).map( String );

				input.hidden = true;
				stage.className = 'task__stage msg';
				stage.textContent = 'انتبه…';
				await sleep( 900 );
				stage.className = 'task__stage num';

				for ( const d of seq ) {
					stage.textContent = d;
					await sleep( 800 );
					stage.textContent = '';
					await sleep( 200 );
				}

				stage.className = 'task__stage msg';
				stage.textContent = backward ? 'اكتبها من الأخير' : 'اكتبها بالترتيب';
				const answer = await collect();
				const expected = ( backward ? [ ...seq ].reverse() : seq ).join( '' );
				const ok = answer === expected;

				input.hidden = true;
				stage.className = `task__stage ${ ok ? 'is-ok' : 'is-no' }`;
				stage.textContent = ok ? '✓' : '✗';
				await sleep( 650 );

				if ( ok ) {
					passed++;
					best = Math.max( best, len );
				}
			}

			if ( ! passed ) {
				break;
			}
		}

		return best;
	}

	await showGate( 'ستظهر أرقام واحداً بعد الآخر، رقم كل ثانية. بعد آخر رقم، اكتبها بالترتيب نفسه. التسلسل يطول كلما أصبت.', 'ابدأ' );
	const forward = await phase( false, 3, 9 );
	await showGate( 'أحسنت. الآن الجزء الثاني: اكتب الأرقام بالعكس، من آخر رقم ظهر إلى أوّله. مثلاً: إن ظهر 3 ثم 8، تكتب 8 ثم 3.', 'ابدأ بالعكس' );
	const backward = await phase( true, 2, 8 );

	document.removeEventListener( 'keydown', onKey );

	return {
		scores: { FWD: forward, BWD: backward },
		details: [
			`أطول تسلسل أعدته بالترتيب: ${ forward ? countNoun( forward, DIGITS ) : 'لا شيء' }`,
			`أطول تسلسل أعدته بالعكس: ${ backward ? countNoun( backward, DIGITS ) : 'لا شيء' }`,
		],
		caution: forward < 3 ? 'لم تُعِد أيّ تسلسل من ثلاثة أرقام بالترتيب. هذا غير معتاد، وقد يعني تشتّتاً أثناء المهمّة أو سوء فهم للتعليمات. جرّبها مرّة أخرى في مكان هادئ.' : undefined,
	};
}

/* ─────────────── مهمّة الانتباه المستمرّ (SART) ─────────────── */

/**
 * نسخة مختصرة من SART (Robertson وزملاؤه، 1997): تسعون رقماً، كل رقم
 * 250 مللي ثانية ثم قناع 900 — كالأصل — لكن تسعون بدل 225. اضغط لكل رقم
 * إلا 3. الضغط على 3 «خطأ إقدام»، وهو المؤشّر الأساسي للهفوة الانتباهية.
 */
export async function runSart( box: HTMLElement ): Promise<TaskOutcome> {
	const DIGIT_MS = 250;
	const MASK_MS = 900;
	const SIZES = [ 44, 56, 68, 80, 96 ];

	box.innerHTML = `
		<div class="task">
			<p class="task__phase" data-phase>اضغط لكل رقم… إلا <b class="num">3</b></p>
			<button type="button" class="sart" data-pad aria-label="اضغط هنا لكل رقم إلا 3">
				<span class="sart__digit num" data-digit aria-live="off"></span>
			</button>
			<div class="task__bar" aria-hidden="true"><span data-prog></span></div>
			<div class="task__gate" data-gate>
				<p>ستظهر أرقام من 1 إلى 9 بسرعة، بأحجام مختلفة. <b>اضغط على المربّع (أو زرّ المسافة) لكل رقم، إلّا الرقم 3</b>: لا تضغط حين يظهر.</p>
				<p class="fine">المهمّة نحو دقيقتين. السرعة والدقّة مهمّتان معاً.</p>
				<button type="button" class="btn btn--primary btn--block" data-gate-go>ابدأ</button>
			</div>
		</div>`;

	const pad = box.querySelector<HTMLButtonElement>( '[data-pad]' )!;
	const digitEl = box.querySelector<HTMLElement>( '[data-digit]' )!;
	const prog = box.querySelector<HTMLElement>( '[data-prog]' )!;
	const gate = box.querySelector<HTMLElement>( '[data-gate]' )!;
	const go = box.querySelector<HTMLButtonElement>( '[data-gate-go]' )!;

	pad.hidden = true;
	go.focus( { preventScroll: true } );
	await waitClick( go );
	gate.hidden = true;
	pad.hidden = false;

	for ( const c of [ '3', '2', '1' ] ) {
		digitEl.style.fontSize = '40px';
		digitEl.textContent = c === '3' ? 'استعدّ' : c === '2' ? '…' : 'الآن';
		await sleep( 700 );
	}

	const trials = shuffle( Array.from( { length: 90 }, ( _, i ) => ( i % 9 ) + 1 ) );
	const rts: number[] = [];
	let commissions = 0;
	let omissions = 0;
	let onset = 0;
	let responded = false;
	let rt = 0;

	const respond = () => {
		if ( onset && ! responded ) {
			responded = true;
			rt = performance.now() - onset;
			pad.classList.add( 'hit' );
			window.setTimeout( () => pad.classList.remove( 'hit' ), 90 );
		}
	};

	pad.addEventListener( 'pointerdown', ( e ) => {
		e.preventDefault();
		respond();
	} );
	const onKey = ( e: KeyboardEvent ) => {
		if ( e.key === ' ' || e.key === 'Enter' ) {
			e.preventDefault();
			respond();
		}
	};
	document.addEventListener( 'keydown', onKey );

	for ( let i = 0; i < trials.length; i++ ) {
		const d = trials[ i ];

		responded = false;
		rt = 0;
		digitEl.style.fontSize = `${ SIZES[ Math.floor( Math.random() * SIZES.length ) ] }px`;
		digitEl.textContent = String( d );
		onset = performance.now();
		await sleep( DIGIT_MS );
		digitEl.style.fontSize = '60px';
		digitEl.textContent = '⊗';
		await sleep( MASK_MS );

		if ( d === 3 ) {
			commissions += responded ? 1 : 0;
		} else if ( responded ) {
			rts.push( rt );
		} else {
			omissions++;
		}

		prog.style.width = `${ ( ( i + 1 ) / trials.length ) * 100 }%`;
	}

	onset = 0;
	document.removeEventListener( 'keydown', onKey );

	const mean = rts.length ? rts.reduce( ( a, b ) => a + b, 0 ) / rts.length : 0;
	const sd = rts.length > 1 ? Math.sqrt( rts.reduce( ( a, b ) => a + ( b - mean ) ** 2, 0 ) / ( rts.length - 1 ) ) : 0;
	const go80 = trials.filter( ( d ) => d !== 3 ).length;

	let caution: string | undefined;

	if ( omissions > go80 / 2 ) {
		caution = `لم تضغط لأكثر من نصف الأرقام (${ num( omissions ) } من ${ num( go80 ) }). قد تكون المهمّة لم تُفهم، أو انقطع انتباهك لسبب خارجي؛ النتيجة هنا لا تُقرأ.`;
	} else if ( mean && mean < 300 && commissions >= 5 ) {
		caution = `ضغطت بسرعة كبيرة (متوسّط ${ num( Math.round( mean ) ) } مللي ثانية) ووقعت في أغلب الأرقام 3. في هذه المهمّة، السرعة العالية وحدها ترفع الأخطاء: قد تقيس النتيجة أسلوبك في الاستجابة أكثر من انتباهك.`;
	}

	return {
		scores: { COM: commissions },
		details: [
			`ضغطت على الرقم 3 في ${ commissions ? countNoun( commissions, TIMES, 'gen' ) : 'ولا مرّة' } من 10`,
			`لم تضغط لرقم غيره: ${ num( omissions ) } من ${ num( go80 ) }`,
			rts.length ? `متوسّط سرعة استجابتك: ${ num( Math.round( mean ) ) } مللي ثانية، وتذبذبها: ${ num( Math.round( sd ) ) }` : 'لم تُسجَّل استجابات لحساب السرعة',
		],
		caution,
	};
}
