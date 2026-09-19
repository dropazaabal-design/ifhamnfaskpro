import { qs, qsa, settings } from './util.js';

/**
 * فلترة بلا إعادة تحميل.
 *
 * لا نبني نتائج في المتصفح: نطلب الصفحة نفسها بالرابط الجديد ونستبدل منها
 * منطقة النتائج وأجزاء الفلترة. التصيير يبقى كلّه على الخادم، فلا يتكرّر
 * منطق البطاقة والسعر والعملة، ولا يختلف ما يراه الزائر عن الرابط الذي
 * يمكن أن يشاركه.
 *
 * الروابط والنماذج تعمل كما هي عند تعطيل JavaScript، وهذه الوحدة تعترضها
 * فقط. ولذلك تُدار حالة المتصفح بـ pushState: زر «رجوع» يعمل.
 */

const SWAP = [ '[data-mp-results]', '[data-mp-active-filters]' ];

let busy = false;

const setBusy = ( on ) => {
	busy = on;
	const results = qs( '[data-mp-results]' );

	if ( results ) {
		results.dataset.mpBusy = on ? 'true' : 'false';
		results.setAttribute( 'aria-busy', on ? 'true' : 'false' );
	}
};

const announce = ( text ) => {
	const live = qs( '[data-mp-live]' );

	if ( live && text ) {
		live.textContent = text;
	}
};

const applyDocument = ( doc ) => {
	SWAP.forEach( ( selector ) => {
		const next = doc.querySelector( selector );
		const current = qs( selector );

		if ( next && current ) {
			current.innerHTML = next.innerHTML;
		}
	} );

	// أجزاء الفلترة موجودة مرتين (عمود الديسكتوب ولوح الجوال): كل واحد
	// يُستبدل بنظيره حتى تتوافق الحالات في الاثنين.
	qsa( '[data-mp-filter-body]' ).forEach( ( body ) => {
		const next = doc.querySelector( `[data-mp-filter-body="${ body.dataset.mpFilterBody }"]` );

		if ( next ) {
			body.innerHTML = next.innerHTML;
		}
	} );

	const title = doc.querySelector( 'title' );

	if ( title ) {
		document.title = title.textContent;
	}

	const count = qs( '.mp-result-count' );
	announce( count ? count.textContent.trim() : '' );

	// أجزاء الفلترة استُبدلت: من يعتمد عليها يُعيد الربط.
	document.dispatchEvent( new CustomEvent( 'matjar:filter-applied' ) );
};

const load = async ( url, push = true ) => {
	if ( busy ) {
		return;
	}

	setBusy( true );
	announce( settings().strings?.filtering || '' );

	try {
		const response = await fetch( url, {
			credentials: 'same-origin',
			headers: { 'X-Requested-With': 'XMLHttpRequest' },
		} );

		if ( ! response.ok ) {
			throw new Error( String( response.status ) );
		}

		const doc = new DOMParser().parseFromString( await response.text(), 'text/html' );
		applyDocument( doc );

		if ( push ) {
			window.history.pushState( { mpFilter: true }, '', url );
		}

		const results = qs( '[data-mp-results]' );

		if ( results && window.scrollY > results.getBoundingClientRect().top + window.scrollY ) {
			results.scrollIntoView( { block: 'start', behavior: 'smooth' } );
		}
	} catch ( error ) {
		// أي فشل شبكة يعود إلى السلوك الأصلي: انتقال كامل للرابط.
		window.location.assign( url );
		return;
	}

	setBusy( false );
};

export default function filter() {
	const results = qs( '[data-mp-results]' );

	if ( ! results ) {
		return;
	}

	document.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( '[data-mp-filter-link], .woocommerce-pagination a' );

		if ( ! link || ! link.href || link.target ) {
			return;
		}

		// روابط خارج الأصل تُترك للمتصفح.
		if ( new URL( link.href ).origin !== window.location.origin ) {
			return;
		}

		event.preventDefault();
		load( link.href );
	} );

	document.addEventListener( 'submit', ( event ) => {
		const form = event.target.closest( '[data-mp-filter-form]' );

		if ( ! form ) {
			return;
		}

		event.preventDefault();

		const data = new FormData( form );
		const url = new URL( form.action, window.location.origin );
		url.search = new URLSearchParams( data ).toString();
		load( url.toString() );
	} );

	// ترتيب ووكومرس نموذج GET أيضاً: يُفلتَر بلا تحميل كذلك.
	document.addEventListener( 'change', ( event ) => {
		const select = event.target.closest( '.woocommerce-ordering select' );

		if ( ! select ) {
			return;
		}

		const form = select.closest( 'form' );

		if ( ! form ) {
			return;
		}

		event.preventDefault();

		const url = new URL( form.action || window.location.href, window.location.origin );
		url.search = new URLSearchParams( new FormData( form ) ).toString();
		load( url.toString() );
	} );

	window.addEventListener( 'popstate', () => {
		load( window.location.href, false );
	} );
}
