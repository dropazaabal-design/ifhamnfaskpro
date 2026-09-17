import { qsa } from './util.js';

/**
 * الأكورديون: يقصّ طول صفحة المنتج دون حجب المعلومة.
 */
export default function accordion() {
	qsa( '[data-mp-accordion] > [aria-controls]' ).forEach( ( button ) => {
		const panel = document.getElementById( button.getAttribute( 'aria-controls' ) );

		if ( ! panel ) {
			return;
		}

		const expanded = 'true' === button.getAttribute( 'aria-expanded' );
		panel.hidden = ! expanded;

		button.addEventListener( 'click', () => {
			const isOpen = 'true' === button.getAttribute( 'aria-expanded' );
			button.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
			panel.hidden = isOpen;
		} );
	} );
}
