import { qs } from './util.js';

const DISMISSED = 'mp-proof-off';
const MAX_ROUNDS = 3;

/**
 * إشعارات النشاط.
 *
 * النصوص مقروءة من وسم JSON طُبع في الصفحة: لا طلب شبكة ولا استعلام.
 * وتتوقّف بعد ثلاث دورات كاملة — الإشعار الذي لا يتوقّف يصير ضجيجاً
 * يتعلّم الزائر تجاهله، ثم يُغلق الصفحة.
 */
export default function socialProof() {
	const toast = qs( '[data-mp-proof]' );
	const holder = qs( '#mp-proof-data' );

	if ( ! toast || ! holder ) {
		return;
	}

	let data;

	try {
		data = JSON.parse( holder.textContent );
	} catch ( error ) {
		return;
	}

	if ( ! data || ! Array.isArray( data.messages ) || ! data.messages.length ) {
		return;
	}

	try {
		if ( '1' === window.sessionStorage.getItem( DISMISSED ) ) {
			return;
		}
	} catch ( error ) {
		// لا تخزين: تُعرض الإشعارات عادةً.
	}

	const text = qs( '[data-mp-proof-text]', toast );
	const close = qs( '[data-mp-proof-close]', toast );
	const every = Math.max( 4000, Number( data.every ) || 10000 );
	const motion = window.matchMedia( '(prefers-reduced-motion: reduce)' );

	let index = 0;
	let rounds = 0;
	let timer = 0;

	const hide = () => {
		toast.dataset.mpShown = 'false';

		window.setTimeout( () => {
			toast.hidden = true;
		}, motion.matches ? 0 : 320 );
	};

	const show = () => {
		text.textContent = data.messages[ index % data.messages.length ];
		toast.hidden = false;

		// إطار واحد بين الإظهار وتغيير الحالة، وإلّا لم تقع الحركة.
		window.requestAnimationFrame( () => {
			toast.dataset.mpShown = 'true';
		} );

		window.setTimeout( hide, Math.min( 5000, every - 1200 ) );

		index++;

		if ( 0 === index % data.messages.length ) {
			rounds++;
		}

		if ( rounds >= MAX_ROUNDS ) {
			window.clearInterval( timer );
		}
	};

	if ( close ) {
		close.addEventListener( 'click', () => {
			window.clearInterval( timer );
			hide();

			try {
				window.sessionStorage.setItem( DISMISSED, '1' );
			} catch ( error ) {
				// لا تخزين: يعود الإشعار في الصفحة التالية.
			}
		} );
	}

	// أوّل إشعار بعد مهلة: ظهوره فور التحميل يُقرأ إعلاناً لا نشاطاً.
	window.setTimeout( () => {
		show();
		timer = window.setInterval( show, every );
	}, every );
}
