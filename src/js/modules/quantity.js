import { qsa, qs } from './util.js';

/**
 * حقل الكمية.
 *
 * أسهم حقل الرقم الافتراضية هدف لمس لا يتجاوز بضعة بكسلات على الجوال.
 * الزرّان هنا 40 بكسل، ويُطلقان حدث change أصلياً ليتقاطاه ووكومرس في
 * صفحة السلة.
 */
export default function quantity() {
	qsa( '[data-mp-qty]' ).forEach( ( wrapper ) => {
		const input = qs( 'input[type="number"]', wrapper );

		if ( ! input ) {
			return;
		}

		wrapper.addEventListener( 'click', ( event ) => {
			const button = event.target.closest( '[data-mp-qty-step]' );

			if ( ! button ) {
				return;
			}

			const step = parseFloat( input.step ) || 1;
			const min = '' === input.min ? 1 : parseFloat( input.min );
			const max = '' === input.max ? Infinity : parseFloat( input.max );
			const delta = parseInt( button.dataset.mpQtyStep, 10 ) * step;
			const next = Math.min( max, Math.max( min, ( parseFloat( input.value ) || min ) + delta ) );

			if ( next === parseFloat( input.value ) ) {
				return;
			}

			input.value = String( next );
			input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			input.dispatchEvent( new Event( 'input', { bubbles: true } ) );
		} );
	} );
}
