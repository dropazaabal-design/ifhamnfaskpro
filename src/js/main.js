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
import search from './modules/search.js';
import cart from './modules/cart.js';
import quickAdd from './modules/quick-add.js';
import quantity from './modules/quantity.js';
import cartDrawer from './modules/cart-drawer.js';
import pwa from './modules/pwa.js';

const boot = () => {
	announcement();
	drawers();
	scrollBars();
	search();
	accordion();
	cart();
	quickAdd();
	quantity();
	cartDrawer();
	pwa();
};

if ( 'loading' === document.readyState ) {
	document.addEventListener( 'DOMContentLoaded', boot, { once: true } );
} else {
	boot();
}
