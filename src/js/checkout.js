/**
 * أصول السلة والدفع — تُحمَّل في الصفحتين وحدهما.
 */

import checkout from './modules/checkout.js';

const boot = () => {
	checkout();
};

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
