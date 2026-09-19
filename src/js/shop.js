/**
 * أصول صفحات الأقسام — تُحمَّل في الأرشيف والبحث وحدهما.
 */

import filter from './modules/filter.js';
import range from './modules/range.js';

const boot = () => {
	filter();
	range();

	// أجزاء الفلترة تُستبدل بعد كل تطبيق، فيُعاد ربط الشريط على الجديد.
	document.addEventListener( 'matjar:filter-applied', range );
};

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
