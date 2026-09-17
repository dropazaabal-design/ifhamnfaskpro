/**
 * أصول صفحة المنتج — تُحمَّل في صفحات المنتج وحدها.
 *
 * فصلها عن الحزمة الرئيسية يعني ألّا تحمل الصفحة الأولى وصفحات الأقسام
 * كوداً لا تستخدمه.
 */

import gallery from './modules/gallery.js';
import variations from './modules/variations.js';
import buyNow from './modules/buy-now.js';

const boot = () => {
	gallery();
	variations();
	buyNow();
};

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
