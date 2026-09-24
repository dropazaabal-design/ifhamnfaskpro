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

	/*
	 * محفّزان لا واحد.
	 *
	 * mouseleave لا يقع على شاشة اللمس: لا مؤشّر يغادر النافذة. وبناء
	 * الميزة عليه وحده يعني أنها لا تعمل عند الأغلبية العظمى من زوّار
	 * متجرٍ يأتيه الناس من تيك توك وسناب شات — أي أنها ميزة سطح مكتب
	 * في قالبٍ جوّالٍ أولاً.
	 *
	 * فعلى اللمس نقرأ نيّة المغادرة من السلوك بدل المؤشّر: تمرير صاعد
	 * حاسم بعد أن يكون الزائر نزل في الصفحة ومكث فيها. ولا نخطف زرّ
	 * الرجوع: خطفه يكسر أهم زرّ في متصفّح الجوال ليربح عرضاً واحداً.
	 */
	const fine = window.matchMedia( '(hover: hover) and (pointer: fine)' );

	if ( fine.matches ) {
		document.addEventListener( 'mouseleave', ( event ) => {
			// الخروج من أعلى النافذة وحده: الخروج من الجوانب تنقّلٌ عادي.
			if ( event.clientY <= 0 && ! event.relatedTarget ) {
				open();
			}
		} );
	} else {
		const START = Date.now();
		const DWELL = 8000;
		const DEPTH = 0.25;
		const RISE = 200;
		const WINDOW = 700;

		let last = window.scrollY;
		let from = 0;
		let since = 0;
		let deep = false;

		/*
		 * السرعة تُقاس على نافذة زمنية لا على الفرق بين حدثين.
		 *
		 * ‎scroll-behavior: smooth‎ مُعلَن في هذا القالب، فالمتصفّح يفتّت
		 * أي قفزة إلى عشرات الأحداث الصغيرة ولا يبلغ فرقُ حدثين واحدين
		 * أي عتبة معقولة. قياس مجموع الصعود خلال نافذة يعطي النيّة نفسها
		 * ولا ينكسر مع التمرير الناعم ولا مع اختلاف معدّل التحديث.
		 */
		window.addEventListener(
			'scroll',
			() => {
				const now = window.scrollY;
				const height = Math.max( 1, document.documentElement.scrollHeight - window.innerHeight );
				const stamp = Date.now();

				if ( now / height >= DEPTH ) {
					deep = true;
				}

				if ( now >= last ) {
					// نزول أو ثبات: تنتهي جولة الصعود الجارية.
					from = 0;
					since = 0;
					last = now;

					return;
				}

				if ( ! from || stamp - since > WINDOW ) {
					from = last;
					since = stamp;
				}

				last = now;

				if ( deep && from - now >= RISE && stamp - START >= DWELL ) {
					open();
				}
			},
			{ passive: true }
		);
	}

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
