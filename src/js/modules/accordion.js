import { qs, qsa } from './util.js';

/**
 * الأكورديون: يقصّ طول صفحة المنتج دون حجب المعلومة.
 *
 * الاختيار هنا نَسَبي لا ابنٌ مباشر. كان `[data-mp-accordion] > [aria-controls]`
 * فلم يطابق أكورديون تبويبات المنتج إطلاقاً، لأن زرّه حفيدٌ داخل
 * ‎.mp-accordion__item‎ لا ابن مباشر: بقيت اللوحات كلّها مفتوحة، والأزرار
 * لا تفعل شيئاً، وaria-expanded يقول «مطويّ» عن محتوى ظاهر — أي أن قارئ
 * الشاشة كان يتلقّى عكس الحقيقة.
 *
 * والتحقّق من أقرب أكورديون يمنع أن يبتلع أكورديونٌ خارجي أزرار آخر متداخل.
 */
const items = ( root ) =>
	qsa( '[aria-controls]', root ).filter( ( button ) => button.closest( '[data-mp-accordion]' ) === root );

/**
 * يفتح اللوحة التي تحوي العنصر المقصود، ويعيد صحيحاً إن فتح شيئاً.
 *
 * رابط تقييمات المنتج يشير إلى ‎#reviews‎، وهو داخل لوحة التقييمات وهي
 * ثالثة التبويبات فمطويّة دائماً. بلا هذا تقفز الصفحة إلى عنصر مخفيّ
 * فلا يرى الزائر شيئاً بعد أن نقر على النجوم.
 *
 * @param {string} hash جزء العنوان بعد #.
 * @return {boolean} هل فُتحت لوحة.
 */
const revealHash = ( hash ) => {
	if ( ! hash || hash.length < 2 ) {
		return false;
	}

	let target;

	try {
		target = qs( hash );
	} catch ( error ) {
		return false;
	}

	if ( ! target ) {
		return false;
	}

	const panel = target.closest( '[data-mp-accordion] [id]' );
	const holder = target.closest( '[data-mp-accordion]' );

	if ( ! holder ) {
		return false;
	}

	const button = items( holder ).find( ( node ) => {
		const owned = document.getElementById( node.getAttribute( 'aria-controls' ) );

		return owned && ( owned === panel || owned.contains( target ) || owned === target );
	} );

	if ( ! button || 'true' === button.getAttribute( 'aria-expanded' ) ) {
		return false;
	}

	button.click();

	return true;
};

export default function accordion() {
	qsa( '[data-mp-accordion]' ).forEach( ( root ) => {
		items( root ).forEach( ( button ) => {
			const panel = document.getElementById( button.getAttribute( 'aria-controls' ) );

			if ( ! panel ) {
				return;
			}

			panel.hidden = 'true' !== button.getAttribute( 'aria-expanded' );

			button.addEventListener( 'click', () => {
				const isOpen = 'true' === button.getAttribute( 'aria-expanded' );
				button.setAttribute( 'aria-expanded', isOpen ? 'false' : 'true' );
				panel.hidden = isOpen;
			} );
		} );
	} );

	// نقرة على رابط داخلي: تُفتح اللوحة قبل أن يقفز المتصفّح إليها.
	document.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( 'a[href^="#"]' );

		if ( ! link ) {
			return;
		}

		const hash = link.getAttribute( 'href' );

		if ( revealHash( hash ) ) {
			const target = qs( hash );

			if ( target ) {
				event.preventDefault();
				target.scrollIntoView( { block: 'start' } );

				if ( window.history && window.history.replaceState ) {
					window.history.replaceState( null, '', hash );
				}
			}
		}
	} );

	// وصولٌ مباشر بعنوان يحمل #: يُفتح ثم يُعاد التمرير.
	const open = () => {
		if ( revealHash( window.location.hash ) ) {
			const target = qs( window.location.hash );

			if ( target ) {
				target.scrollIntoView( { block: 'start' } );
			}
		}
	};

	window.addEventListener( 'hashchange', open );
	open();
}
