import { qs, qsa, settings } from './util.js';

/**
 * شريط سعر بمقبضين، بلا مكتبة.
 *
 * حقلا range متراكبان: الأحداث معطّلة على الحقل نفسه ومفعّلة على مقبضه
 * وحده، فيعمل المقبضان دون أن يحجب أحدهما الآخر.
 *
 * في العربية يقلب المتصفح اتجاه حقل range تلقائياً (الأدنى يميناً)، ولذلك
 * يُحسب الملء بخصائص منطقية: inset-inline-start و inline-size، فينقلب معه
 * بلا حساب اتجاه.
 */
const formatPrice = ( value ) => {
	const c = settings().currency || {};
	const decimals = Number.isInteger( c.decimals ) ? c.decimals : 2;
	const parts = Number( value ).toFixed( decimals ).split( '.' );

	parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, c.thousand ?? ',' );

	const body = parts.length > 1 ? parts.join( c.decimal ?? '.' ) : parts[ 0 ];
	const symbol = c.symbol ?? '';

	if ( 'left' === c.position ) {
		return symbol + body;
	}

	if ( 'left_space' === c.position ) {
		return symbol + ' ' + body;
	}

	if ( 'right' === c.position ) {
		return body + symbol;
	}

	return body + ' ' + symbol;
};

export default function range() {
	qsa( '[data-mp-range]' ).forEach( ( root ) => {
		const min = qs( '[data-mp-range-min]', root );
		const max = qs( '[data-mp-range-max]', root );
		const fill = qs( '[data-mp-range-fill]', root );
		const outMin = qs( '[data-mp-range-out-min]', root.parentElement || root );
		const outMax = qs( '[data-mp-range-out-max]', root.parentElement || root );

		if ( ! min || ! max ) {
			return;
		}

		const floor = parseFloat( min.min );
		const ceiling = parseFloat( min.max );
		const span = ceiling - floor;

		const paint = () => {
			let low = parseFloat( min.value );
			let high = parseFloat( max.value );

			// لا يتجاوز المقبضان أحدهما الآخر.
			if ( low > high ) {
				if ( document.activeElement === min ) {
					low = high;
					min.value = String( high );
				} else {
					high = low;
					max.value = String( low );
				}
			}

			if ( fill && span > 0 ) {
				const start = ( ( low - floor ) / span ) * 100;
				const width = ( ( high - low ) / span ) * 100;
				fill.style.insetInlineStart = start + '%';
				fill.style.inlineSize = width + '%';
			}

			if ( outMin ) {
				outMin.textContent = formatPrice( low );
			}

			if ( outMax ) {
				outMax.textContent = formatPrice( high );
			}
		};

		min.addEventListener( 'input', paint );
		max.addEventListener( 'input', paint );
		paint();
	} );
}
