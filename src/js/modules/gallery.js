import { qs, qsa, onFrame } from './util.js';

/**
 * معرض صور المنتج.
 *
 * السحب نفسه من المتصفح عبر scroll-snap: لا مكتبة، ولا محاكاة للمس، ولا
 * زخم مكتوب بيدنا. JavaScript هنا لا يفعل إلا تحديث المؤشّرات والعدّاد.
 *
 * حساب الصورة النشطة يعتمد على قياس موضع كل شريحة لا على حساب
 * scrollLeft: قيمة scrollLeft سالبة في RTL في المحرّكات الحديثة وتختلف
 * تاريخياً بينها، فالقياس المباشر أسلم من الحساب.
 */
export default function gallery() {
	qsa( '[data-mp-gallery]' ).forEach( ( root ) => {
		const rail = qs( '[data-mp-gallery-rail]', root );

		if ( ! rail ) {
			return;
		}

		const slides = qsa( '[data-mp-gallery-slide]', rail );
		const dots = qsa( '[data-mp-gallery-dot]', root );
		const current = qs( '[data-mp-gallery-current]', root );

		if ( slides.length < 2 ) {
			return;
		}

		const activeIndex = () => {
			const railStart = rail.getBoundingClientRect();
			let best = 0;
			let bestDistance = Infinity;

			slides.forEach( ( slide, index ) => {
				const box = slide.getBoundingClientRect();
				// جهة البداية: اليسار في LTR واليمين في RTL.
				const distance = Math.abs(
					'rtl' === getComputedStyle( rail ).direction
						? box.right - railStart.right
						: box.left - railStart.left
				);

				if ( distance < bestDistance ) {
					bestDistance = distance;
					best = index;
				}
			} );

			return best;
		};

		const paint = () => {
			const index = activeIndex();

			if ( current ) {
				current.textContent = String( index + 1 );
			}

			dots.forEach( ( dot, i ) => {
				dot.setAttribute( 'aria-selected', i === index ? 'true' : 'false' );
			} );
		};

		rail.addEventListener( 'scroll', onFrame( paint ), { passive: true } );

		dots.forEach( ( dot ) => {
			dot.addEventListener( 'click', () => {
				const index = parseInt( dot.dataset.mpGalleryDot, 10 );
				const slide = slides[ index ];

				if ( slide ) {
					slide.scrollIntoView( { inline: 'start', block: 'nearest', behavior: 'smooth' } );
				}
			} );
		} );

		paint();
	} );
}
