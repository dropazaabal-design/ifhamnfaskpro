import { qs, qsa } from './util.js';

/**
 * أزرار خيارات المنتج المرئية.
 *
 * القائمة المنسدلة تكلّف نقرتين وتخفي الخيارات، فتُخفى ويُبنى فوقها صفّ
 * أزرار — واللوحة المنسدلة تبقى مصدر الحقيقة الذي يقرأه ووكومرس.
 *
 * منطق المتغيّرات نفسه (السعر والمخزون والصورة عند الاختيار) يبقى لسكربت
 * ووكومرس المُجرَّب. نحن نكتب في الحقل ونُطلق حدث change أصلياً، ومعالجات
 * jQuery تستجيب للأحداث الأصلية، فلا نُعيد كتابة منطق مُختبَر.
 *
 * ووكومرس يُعيد بناء خيارات الحقل عند كل اختيار (ليُعطّل التوليفات غير
 * المتاحة)، فنراقب تغيّر أبنائه بـ MutationObserver ونعيد البناء: أحداث
 * ووكومرس المخصّصة تُطلَق عبر jQuery ولا تصل إلى مستمع أصلي.
 */
const buildGroup = ( wrapper, select ) => {
	const taxonomy = wrapper.dataset.mpTaxonomy || '';
	const chosen = qs( '[data-mp-variation-chosen]', wrapper );
	let group = qs( '[data-mp-swatches]', wrapper );

	if ( ! group ) {
		group = document.createElement( 'div' );
		group.className = 'mp-variation__options';
		group.setAttribute( 'role', 'radiogroup' );
		group.setAttribute( 'data-mp-swatches', '' );
		select.insertAdjacentElement( 'afterend', group );
	}

	let colours = {};

	try {
		colours = wrapper.dataset.mpColours ? JSON.parse( wrapper.dataset.mpColours ) : {};
	} catch ( error ) {
		colours = {};
	}

	const options = qsa( 'option', select ).filter( ( option ) => '' !== option.value );

	group.replaceChildren();

	options.forEach( ( option ) => {
		const button = document.createElement( 'button' );
		const colour = colours[ option.value ] || '';
		const selected = select.value === option.value;

		button.type = 'button';
		button.className = colour ? 'mp-swatch mp-swatch--colour' : 'mp-swatch';
		button.setAttribute( 'role', 'radio' );
		button.setAttribute( 'aria-checked', selected ? 'true' : 'false' );
		button.dataset.mpValue = option.value;
		button.tabIndex = selected ? 0 : -1;

		if ( option.disabled ) {
			button.disabled = true;
			button.setAttribute( 'aria-disabled', 'true' );
		}

		if ( colour ) {
			button.style.setProperty( '--mp-swatch', colour );
			button.setAttribute( 'aria-label', option.textContent.trim() );
		} else {
			button.textContent = option.textContent.trim();
		}

		group.appendChild( button );
	} );

	if ( chosen ) {
		const active = options.find( ( option ) => option.value === select.value );
		chosen.textContent = active ? active.textContent.trim() : '';
	}

	group.dataset.mpTaxonomy = taxonomy;
};

const focusable = ( group ) => qsa( '.mp-swatch:not([disabled])', group );

export default function variations() {
	qsa( '[data-mp-variation]' ).forEach( ( wrapper ) => {
		const select = qs( 'select', wrapper );

		if ( ! select ) {
			return;
		}

		buildGroup( wrapper, select );
		select.hidden = true;

		// ووكومرس يعيد كتابة الخيارات عند كل اختيار: نعيد البناء معه.
		new MutationObserver( () => buildGroup( wrapper, select ) ).observe( select, {
			childList: true,
			subtree: true,
			attributes: true,
			attributeFilter: [ 'disabled' ],
		} );

		// تغيير الحقل من أي مصدر (ومنه زر «إلغاء الاختيار») يُحدّث الأزرار.
		select.addEventListener( 'change', () => buildGroup( wrapper, select ) );

		wrapper.addEventListener( 'click', ( event ) => {
			const button = event.target.closest( '.mp-swatch' );

			if ( ! button || button.disabled ) {
				return;
			}

			event.preventDefault();
			select.value = button.dataset.mpValue;
			select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		} );

		wrapper.addEventListener( 'keydown', ( event ) => {
			const group = qs( '[data-mp-swatches]', wrapper );

			if ( ! group || ! event.target.closest( '.mp-swatch' ) ) {
				return;
			}

			const keys = [ 'ArrowRight', 'ArrowLeft', 'ArrowUp', 'ArrowDown' ];

			if ( ! keys.includes( event.key ) ) {
				return;
			}

			event.preventDefault();

			const items = focusable( group );
			const index = items.indexOf( event.target );
			const rtl = 'rtl' === ( document.documentElement.dir || '' );

			// في العربية السهم الأيسر يتقدّم والأيمن يتراجع، عكس اللاتينية.
			let delta = 0;

			if ( 'ArrowDown' === event.key ) {
				delta = 1;
			} else if ( 'ArrowUp' === event.key ) {
				delta = -1;
			} else if ( 'ArrowLeft' === event.key ) {
				delta = rtl ? 1 : -1;
			} else {
				delta = rtl ? -1 : 1;
			}

			const next = items[ ( index + delta + items.length ) % items.length ];

			if ( next ) {
				next.focus();
				next.click();
			}
		} );
	} );
}
