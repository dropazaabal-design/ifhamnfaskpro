import { qs, settings } from './util.js';
import { addItem } from './cart.js';

const RESET_AFTER = 1600;

/**
 * الإضافة السريعة من بطاقة المنتج.
 *
 * مندوبة على المستند لا مربوطة بكل زر: الشبكة قد تتغيّر بالفلترة أو بتحميل
 * صفحة تالية، والتفويض يجعل الأزرار الجديدة تعمل بلا إعادة ربط.
 */
export default function quickAdd() {
	document.addEventListener( 'click', async ( event ) => {
		const button = event.target.closest( '[data-mp-quick-add]' );

		if ( ! button || 'busy' === button.dataset.mpState ) {
			return;
		}

		event.preventDefault();

		const id = parseInt( button.dataset.mpQuickAdd, 10 );

		if ( ! id ) {
			return;
		}

		const status = qs( '[data-mp-live]' );
		button.dataset.mpState = 'busy';

		try {
			await addItem( id, 1 );

			button.dataset.mpState = 'done';

			if ( status ) {
				status.textContent = settings().strings?.added || '';
			}

			document.dispatchEvent( new CustomEvent( 'matjar:item-added', { detail: { id } } ) );
		} catch ( error ) {
			button.dataset.mpState = '';

			if ( status ) {
				status.textContent = settings().strings?.error || '';
			}
		}

		window.setTimeout( () => {
			if ( 'done' === button.dataset.mpState ) {
				button.dataset.mpState = '';
			}
		}, RESET_AFTER );
	} );
}
