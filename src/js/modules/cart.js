import { qsa, settings } from './util.js';

/**
 * عميل السلة عبر WooCommerce Store API.
 *
 * Store API أسرع من admin-ajax ولا يحتاج jQuery. الأخيرة تُحمّل ملفاً كاملاً
 * وتمرّ بدورة تحميل ووردبريس، وهذه تُعيد JSON مباشرة.
 *
 * تُستكمل واجهات السلة واللوح الجانبي في المرحلة الرابعة؛ ما هنا هو العميل
 * وتحديث عدّاد السلة، وهما ما تحتاجه المرحلتان القادمتان.
 */

let storeNonce = '';

const endpoint = ( path ) => `${ ( settings().restUrl || '' ) }${ path }`;

const request = async ( path, options = {} ) => {
	const config = settings();
	const headers = {
		'Content-Type': 'application/json',
		...( options.headers || {} ),
	};

	if ( config.nonce ) {
		headers[ 'X-WP-Nonce' ] = config.nonce;
	}

	if ( storeNonce ) {
		headers.Nonce = storeNonce;
	}

	const response = await fetch( endpoint( path ), {
		credentials: 'same-origin',
		...options,
		headers,
	} );

	// Store API يعيد nonce محدّثاً في الترويسة، ويجب إعادته في الطلب التالي.
	const fresh = response.headers.get( 'Nonce' );

	if ( fresh ) {
		storeNonce = fresh;
	}

	if ( ! response.ok ) {
		throw new Error( `Store API ${ response.status }` );
	}

	return response.json();
};

/**
 * يحدّث كل عدّادات السلة في الصفحة.
 *
 * @param {number} count عدد القطع.
 */
export const paintCount = ( count ) => {
	qsa( '.mp-cart-count' ).forEach( ( node ) => {
		node.dataset.count = String( count );
		node.textContent = String( count );
		node.classList.toggle( 'hidden', count < 1 );
	} );
};

/**
 * يضيف منتجاً إلى السلة دون إعادة تحميل الصفحة.
 *
 * @param {number} id       معرّف المنتج أو المتغيّر.
 * @param {number} quantity الكمية.
 * @return {Promise<Object>} حالة السلة بعد الإضافة.
 */
export const addItem = async ( id, quantity = 1 ) => {
	const cart = await request( 'cart/add-item', {
		method: 'POST',
		body: JSON.stringify( { id, quantity } ),
	} );

	paintCount( cart.items_count || 0 );
	document.dispatchEvent( new CustomEvent( 'matjar:cart-updated', { detail: cart } ) );

	return cart;
};

/**
 * يقرأ حالة السلة الحالية.
 *
 * @return {Promise<Object>} حالة السلة.
 */
export const getCart = async () => {
	const cart = await request( 'cart' );

	paintCount( cart.items_count || 0 );

	return cart;
};

export default function cart() {
	// ووكومرس يطلق هذا الحدث عبر jQuery من إضافات أخرى؛ نستمع له بلا jQuery.
	document.body.addEventListener( 'wc_fragments_refreshed', () => {
		getCart().catch( () => {} );
	} );
}
