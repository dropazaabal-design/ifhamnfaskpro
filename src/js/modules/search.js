import { qs, qsa, settings, onFrame } from './util.js';

const MIN_CHARS = 2;
const DEBOUNCE = 250;
const LIMIT = 6;

/**
 * ينسّق سعراً من Store API بإعدادات عملة المتجر نفسها.
 *
 * Store API يعيد السعر بوحدات صغرى كسلسلة، ومعه رمز العملة وموضعه وفواصل
 * الآلاف وعدد الخانات العشرية كما ضبطها التاجر. نستخدمها كلها بدل تنسيق
 * مثبّت، فيعمل القالب مع أي عملة دون تعديل سطر.
 *
 * @param {Object} prices كائن prices من Store API.
 * @return {string} السعر منسّقاً.
 */
const formatPrice = ( prices ) => {
	if ( ! prices || undefined === prices.price ) {
		return '';
	}

	const minor = Number.isInteger( prices.currency_minor_unit ) ? prices.currency_minor_unit : 2;
	const value = Number( prices.price ) / Math.pow( 10, minor );

	if ( ! Number.isFinite( value ) ) {
		return '';
	}

	const parts = value.toFixed( minor ).split( '.' );
	const thousands = prices.currency_thousand_separator ?? ',';
	const decimal = prices.currency_decimal_separator ?? '.';

	parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, thousands );

	const body = parts.length > 1 ? parts.join( decimal ) : parts[ 0 ];

	return `${ prices.currency_prefix ?? '' }${ body }${ prices.currency_suffix ?? '' }`;
};

const buildItem = ( product ) => {
	const link = document.createElement( 'a' );
	link.className = 'mp-search__item';
	link.href = product.permalink || '#';

	const image = product.images && product.images[ 0 ];

	if ( image && image.thumbnail ) {
		const thumb = document.createElement( 'img' );
		thumb.className = 'mp-search__thumb';
		thumb.src = image.thumbnail;
		thumb.alt = '';
		thumb.loading = 'lazy';
		thumb.width = 48;
		thumb.height = 48;
		link.appendChild( thumb );
	} else {
		const blank = document.createElement( 'span' );
		blank.className = 'mp-search__thumb';
		link.appendChild( blank );
	}

	const name = document.createElement( 'span' );
	name.className = 'mp-search__name';
	// textContent لا innerHTML: اسم المنتج بيانات من الخادم، لا HTML يُنفَّذ.
	name.textContent = product.name || '';
	link.appendChild( name );

	const price = formatPrice( product.prices );

	if ( price ) {
		const tag = document.createElement( 'span' );
		tag.className = 'mp-search__price';
		tag.textContent = price;
		link.appendChild( tag );
	}

	return link;
};

const setupForm = ( form ) => {
	const input = qs( 'input[type="search"]', form );
	const panel = qs( '[data-mp-search-results]', form );
	const status = qs( '[data-mp-search-status]', form );
	const clear = qs( '[data-mp-search-clear]', form );
	const config = settings();

	if ( ! input || ! panel || ! config.restUrl ) {
		return;
	}

	let timer = 0;
	let controller = null;

	const hide = () => {
		panel.hidden = true;
		panel.replaceChildren();
	};

	const toggleClear = () => {
		if ( clear ) {
			clear.hidden = '' === input.value;
		}
	};

	const render = ( products, term ) => {
		panel.replaceChildren();

		if ( ! products.length ) {
			const empty = document.createElement( 'p' );
			empty.className = 'mp-search__empty';
			empty.textContent = config.strings?.noResults || 'لا نتائج';
			panel.appendChild( empty );
			panel.hidden = false;

			if ( status ) {
				status.textContent = config.strings?.noResults || '';
			}

			return;
		}

		products.forEach( ( product ) => panel.appendChild( buildItem( product ) ) );

		const all = document.createElement( 'a' );
		all.className = 'mp-search__item justify-center text-sm font-bold text-cta';
		all.href = `${ form.action }?s=${ encodeURIComponent( term ) }&post_type=product`;
		all.textContent = config.strings?.seeAll || '';
		if ( all.textContent ) {
			panel.appendChild( all );
		}

		panel.hidden = false;

		if ( status ) {
			status.textContent = `${ products.length }`;
		}
	};

	const run = async ( term ) => {
		if ( controller ) {
			controller.abort();
		}

		controller = new AbortController();

		const url = new URL( `${ config.restUrl }products`, window.location.origin );
		url.searchParams.set( 'search', term );
		url.searchParams.set( 'per_page', String( LIMIT ) );
		url.searchParams.set( '_fields', 'id,name,permalink,prices,images' );

		try {
			const response = await fetch( url, {
				credentials: 'same-origin',
				signal: controller.signal,
			} );

			if ( ! response.ok ) {
				throw new Error( String( response.status ) );
			}

			render( await response.json(), term );
		} catch ( error ) {
			if ( 'AbortError' !== error.name ) {
				hide();
			}
		}
	};

	input.addEventListener( 'input', () => {
		toggleClear();
		window.clearTimeout( timer );

		const term = input.value.trim();

		if ( term.length < MIN_CHARS ) {
			hide();
			return;
		}

		timer = window.setTimeout( () => run( term ), DEBOUNCE );
	} );

	form.addEventListener( 'reset', () => {
		window.clearTimeout( timer );
		hide();
		window.setTimeout( toggleClear, 0 );
	} );

	input.addEventListener( 'keydown', ( event ) => {
		if ( 'Escape' === event.key && ! panel.hidden ) {
			event.stopPropagation();
			hide();
			return;
		}

		if ( 'ArrowDown' === event.key && ! panel.hidden ) {
			const first = qs( '.mp-search__item', panel );

			if ( first ) {
				event.preventDefault();
				first.focus();
			}
		}
	} );

	// إغلاق اللوحة عند النقر خارج النموذج.
	document.addEventListener( 'click', ( event ) => {
		if ( ! panel.hidden && ! form.contains( event.target ) ) {
			hide();
		}
	} );

	toggleClear();
};

/**
 * البحث الفوري في المنتجات عبر Store API.
 *
 * المدخل الواحد يخدم نسختي النموذج — الهيدر على الديسكتوب ولوح البحث على
 * الجوال — فلا تكرار للمنطق.
 */
export default function search() {
	qsa( '[data-mp-search]' ).forEach( setupForm );

	// تركيز حقل اللوح عند فتحه.
	document.addEventListener( 'matjar:drawer-opened', ( event ) => {
		const field = qs( '[data-mp-autofocus]', event.detail?.drawer || document );

		if ( field ) {
			onFrame( () => field.focus() )();
		}
	} );
}
