import { qs, qsa } from './util.js';

const SEEN = 'mp-exit-seen';

/**
 * نافذة نيّة الخروج.
 *
 * تُفتح مرّة واحدة لكل جلسة، وعلى الفأرة وحدها: حدث mouseleave لا يقع على
 * اللمس أصلاً، فلا حاجة لحيلة «الرجوع للخلف» على الجوال — وهي حيلة تُغضِب
 * الزائر أكثر مما تكسب.
 */
export default function exitIntent() {
	const modal = qs( '[data-mp-exit]' );

	if ( ! modal ) {
		return;
	}

	// جلسة واحدة: sessionStorage لا localStorage، فالزيارة القادمة تستحقّ
	// عرضاً جديداً والجلسة الواحدة لا تستحقّ عرضين.
	let seen = false;

	try {
		seen = '1' === window.sessionStorage.getItem( SEEN );
	} catch ( error ) {
		seen = false;
	}

	if ( seen ) {
		return;
	}

	const panel = qs( '.mp-modal__panel', modal );
	let opener = null;

	const close = () => {
		modal.hidden = true;
		document.documentElement.style.removeProperty( 'overflow' );

		if ( opener && opener.focus ) {
			opener.focus();
		}
	};

	const open = () => {
		let blocked = false;

		try {
			blocked = '1' === window.sessionStorage.getItem( SEEN );
		} catch ( error ) {
			blocked = false;
		}

		if ( blocked || ! modal.hidden ) {
			return;
		}

		try {
			window.sessionStorage.setItem( SEEN, '1' );
		} catch ( error ) {
			// التصفّح الخاص يمنع الحفظ: تُعرض النافذة ولا تُحفظ الحالة.
		}

		opener = document.activeElement;
		modal.hidden = false;
		document.documentElement.style.setProperty( 'overflow', 'hidden' );

		const target = qs( '.mp-modal__close', modal );

		if ( target ) {
			target.focus();
		}
	};

	document.addEventListener( 'mouseleave', ( event ) => {
		// الخروج من أعلى النافذة وحده: الخروج من الجوانب تنقّلٌ عادي.
		if ( event.clientY <= 0 && ! event.relatedTarget ) {
			open();
		}
	} );

	qsa( '[data-mp-exit-close]', modal ).forEach( ( node ) =>
		node.addEventListener( 'click', close )
	);

	document.addEventListener( 'keydown', ( event ) => {
		if ( modal.hidden ) {
			return;
		}

		if ( 'Escape' === event.key ) {
			close();

			return;
		}

		// حبس التركيز داخل النافذة ما دامت مفتوحة.
		if ( 'Tab' !== event.key || ! panel ) {
			return;
		}

		const focusable = qsa( 'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])', panel )
			.filter( ( node ) => ! node.disabled && null !== node.offsetParent );

		if ( ! focusable.length ) {
			return;
		}

		const first = focusable[ 0 ];
		const last = focusable[ focusable.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	} );

	const copy = qs( '[data-mp-coupon-copy]', modal );
	const code = qs( '[data-mp-coupon]', modal );

	if ( copy && code ) {
		copy.addEventListener( 'click', async () => {
			const value = code.textContent.trim();
			const original = copy.textContent;

			try {
				await navigator.clipboard.writeText( value );
			} catch ( error ) {
				// المتصفحات القديمة وغير الآمنة: تحديد النص ليَنسخه الزائر.
				const range = document.createRange();
				range.selectNodeContents( code );
				const selection = window.getSelection();
				selection.removeAllRanges();
				selection.addRange( range );
			}

			copy.textContent = copy.dataset.mpCopied || original;
			window.setTimeout( () => {
				copy.textContent = original;
			}, 1600 );
		} );
	}
}
