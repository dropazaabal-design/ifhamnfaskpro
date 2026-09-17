/**
 * نقطة دخول JavaScript لقالب Matjar Pro.
 *
 * Vanilla خالص: لا jQuery، ولا مكتبة سحب، ولا أي اعتماد خارجي. كل وحدة
 * تتحقّق من وجود عناصرها أولاً فلا تُكلّف شيئاً في الصفحات التي لا تحتاجها.
 */

import announcement from './modules/announcement.js';
import drawers from './modules/drawer.js';
import scrollBars from './modules/scroll-bars.js';
import accordion from './modules/accordion.js';
import cart from './modules/cart.js';

const boot = () => {
	announcement();
	drawers();
	scrollBars();
	accordion();
	cart();
};

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
