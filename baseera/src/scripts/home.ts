/**
 * البحث والتصفية في الرئيسية.
 *
 * البطاقات موجودة في HTML أصلاً؛ هذا السكربت يُخفي ويُظهر فقط. ويقرأ
 * ‎?q=‎ من العنوان، فيعمل البحث الذي تعلنه البيانات المنظَّمة لمحرّك البحث.
 */
const input = document.querySelector<HTMLInputElement>( '#q' );
const chips = Array.from( document.querySelectorAll<HTMLButtonElement>( '.filters .chip' ) );
const sections = Array.from( document.querySelectorAll<HTMLElement>( '[data-section]' ) );
const featured = document.querySelector<HTMLElement>( '[data-featured]' );
const empty = document.querySelector<HTMLElement>( '[data-empty]' );

let cat = 'all';

// توحيد بسيط للمطابقة: الألف بهمزاتها، والتاء المربوطة، والياء.
const norm = ( s: string ) =>
	s
		.replace( /[أإآ]/g, 'ا' )
		.replace( /ة/g, 'ه' )
		.replace( /ى/g, 'ي' )
		.replace( /[ً-ْـ]/g, '' )
		.toLowerCase()
		.trim();

function apply() {
	const q = norm( input?.value ?? '' );
	let any = false;

	if ( featured ) {
		featured.hidden = cat !== 'all' || q !== '';
	}

	for ( const sec of sections ) {
		let shown = 0;
		const catOk = cat === 'all' || sec.dataset.section === cat;

		sec.querySelectorAll<HTMLElement>( '.tcard' ).forEach( ( card ) => {
			const ok = catOk && ( ! q || norm( card.dataset.title ?? '' ).includes( q ) );
			card.hidden = ! ok;
			if ( ok ) {
				shown++;
			}
		} );

		sec.hidden = shown === 0;
		any ||= shown > 0;
	}

	if ( empty ) {
		empty.hidden = any;
	}
}

chips.forEach( ( chip ) =>
	chip.addEventListener( 'click', () => {
		cat = chip.dataset.cat ?? 'all';
		chips.forEach( ( c ) => c.setAttribute( 'aria-pressed', String( c === chip ) ) );
		apply();
	} )
);

input?.addEventListener( 'input', apply );

const fromUrl = new URLSearchParams( location.search ).get( 'q' );

if ( input && fromUrl ) {
	input.value = fromUrl;
	apply();
}
