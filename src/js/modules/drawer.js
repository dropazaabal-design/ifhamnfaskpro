import { qs, qsa } from './util.js';

const FOCUSABLE = 'a[href], button:not(:disabled), input:not(:disabled), select:not(:disabled), textarea:not(:disabled), [tabindex]:not([tabindex="-1"])';

let active = null;
let lastTrigger = null;

const close = () => {
	if ( ! active ) {
		return;
	}

	active.setAttribute( 'aria-hidden', 'true' );
	active.dataset.mpOpen = 'false';
	document.documentElement.style.removeProperty( 'overflow' );

	qsa( `[data-mp-drawer-open="${ active.dataset.mpDrawer }"]` ).forEach( ( button ) => {
		button.setAttribute( 'aria-expanded', 'false' );
	} );

	if ( lastTrigger ) {
		lastTrigger.focus();
	}

	active = null;
	lastTrigger = null;
};

const open = ( name, trigger ) => {
	const drawer = qs( `[data-mp-drawer="${ name }"]` );

	if ( ! drawer ) {
		return;
	}

	close();

	active = drawer;
	lastTrigger = trigger || null;

	drawer.setAttribute( 'aria-hidden', 'false' );
	drawer.dataset.mpOpen = 'true';
	document.documentElement.style.setProperty( 'overflow', 'hidden' );

	if ( trigger ) {
		trigger.setAttribute( 'aria-expanded', 'true' );
	}

	document.dispatchEvent( new CustomEvent( 'matjar:drawer-opened', { detail: { name, drawer } } ) );

	const autofocus = qs( '[data-mp-autofocus]', drawer );
	const first = autofocus || qs( FOCUSABLE, drawer );

	if ( first ) {
		first.focus();
	}
};

/**
 * الألواح الجانبية والسفلية: القائمة، البحث، السلة، الفلترة.
 *
 * تُحصر فيها لوحة المفاتيح، وتُغلق بـ Escape وبالنقر على الخلفية، ويرتدّ
 * التركيز إلى الزر الذي فتحها.
 */
export default function drawers() {
	document.addEventListener( 'click', ( event ) => {
		const opener = event.target.closest( '[data-mp-drawer-open]' );

		if ( opener ) {
			event.preventDefault();
			open( opener.dataset.mpDrawerOpen, opener );
			return;
		}

		if ( event.target.closest( '[data-mp-drawer-close]' ) ) {
			event.preventDefault();
			close();
			return;
		}

		if ( active && event.target.hasAttribute( 'data-mp-drawer-backdrop' ) ) {
			close();
		}
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( ! active ) {
			return;
		}

		if ( 'Escape' === event.key ) {
			close();
			return;
		}

		if ( 'Tab' !== event.key ) {
			return;
		}

		const items = qsa( FOCUSABLE, active );

		if ( ! items.length ) {
			return;
		}

		const first = items[ 0 ];
		const last = items[ items.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );
}
