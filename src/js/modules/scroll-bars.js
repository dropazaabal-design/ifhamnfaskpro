import { qs, qsa, onFrame } from './util.js';

/**
 * سلوك الأشرطة عند التمرير.
 *
 * شريط التنقل السفلي يختفي مع التمرير للأسفل ويعود مع التمرير للأعلى، فلا
 * يأكل مساحة أثناء التصفّح. وشريط الشراء لا يظهر إلا بعد اختفاء زر الإضافة
 * الأصلي من الشاشة، فلا يتكرّر زران بالوظيفة نفسها.
 */
export default function scrollBars() {
	const bottomNav = qs( '[data-mp-bottom-nav]' );
	const header = qs( '[data-mp-header]' );
	let previous = window.scrollY;

	if ( bottomNav || header ) {
		const onScroll = onFrame( () => {
			const current = window.scrollY;
			const goingDown = current > previous && current > 120;

			if ( bottomNav ) {
				bottomNav.dataset.mpHidden = goingDown ? 'true' : 'false';
			}

			if ( header ) {
				header.dataset.mpCompact = current > 24 ? 'true' : 'false';
			}

			previous = current;
		} );

		window.addEventListener( 'scroll', onScroll, { passive: true } );
	}

	const buyBar = qs( '[data-mp-buy-bar]' );
	const anchor = qs( '[data-mp-buy-anchor]' );

	if ( buyBar && anchor && 'IntersectionObserver' in window ) {
		buyBar.dataset.mpHidden = 'true';

		const observer = new IntersectionObserver(
			( entries ) => {
				entries.forEach( ( entry ) => {
					buyBar.dataset.mpHidden = entry.isIntersecting ? 'true' : 'false';
				} );
			},
			{ rootMargin: '0px 0px -40% 0px' }
		);

		observer.observe( anchor );
	}

	// أشرطة السحب: تعطيل السحب الرأسي العارض أثناء السحب الأفقي.
	qsa( '.mp-rail' ).forEach( ( rail ) => {
		rail.addEventListener( 'wheel', ( event ) => {
			if ( Math.abs( event.deltaX ) > Math.abs( event.deltaY ) ) {
				return;
			}
		}, { passive: true } );
	} );
}
