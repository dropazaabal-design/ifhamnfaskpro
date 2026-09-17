import { openDrawer } from './drawer.js';
import { refreshFragments } from './cart.js';

/**
 * لوح السلة.
 *
 * بعد كل إضافة: نطلب أجزاء ووكومرس المحدّثة ثم نفتح اللوح. الترتيب مقصود —
 * فتح لوح ثم تحديث محتواه يُري الزائر محتوى قديماً لحظةً.
 */
export default function cartDrawer() {
	document.addEventListener( 'matjar:item-added', async () => {
		try {
			await refreshFragments();
		} catch ( error ) {
			// اللوح يُفتح على أي حال: الإضافة نجحت، والمحتوى القديم أفضل من
			// لا شيء، والزائر يرى الصحيح عند أول تحديث.
		}

		openDrawer( 'cart' );
	} );
}
