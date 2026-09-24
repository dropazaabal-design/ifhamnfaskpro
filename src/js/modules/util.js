/**
 * أدوات صغيرة مشتركة. لا مكتبات، ولا jQuery.
 */

export const qs = ( selector, scope = document ) => scope.querySelector( selector );

export const qsa = ( selector, scope = document ) => Array.from( scope.querySelectorAll( selector ) );

/**
 * تخزين محلي آمن: يرمي استثناءً في التصفّح الخاص وعند حجب بيانات الموقع،
 * فيُغلَّف كل وصول حتى لا يسقط السكربت كاملاً بسبب إعداد متصفح.
 */
export const store = {
	get( key ) {
		try {
			return window.localStorage.getItem( key );
		} catch ( error ) {
			return null;
		}
	},
	set( key, value ) {
		try {
			window.localStorage.setItem( key, value );
			return true;
		} catch ( error ) {
			return false;
		}
	},
};

/**
 * يحدّ من تكرار تنفيذ دالة على إطار العرض.
 */
export const onFrame = ( callback ) => {
	let queued = false;

	return ( ...args ) => {
		if ( queued ) {
			return;
		}

		queued = true;
		window.requestAnimationFrame( () => {
			queued = false;
			callback( ...args );
		} );
	};
};

export const settings = () => window.matjarPro || {};
