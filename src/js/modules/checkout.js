import { qs, qsa, settings } from './util.js';

const DEBOUNCE = 650;

/**
 * صفحة السلة: تحديث تلقائي عند تغيير الكمية.
 *
 * زر «تحديث السلة» خطوة إضافية لا يتوقّعها أحد: يغيّر الزائر الكمية ويرى
 * المجموع كما هو فيظنّ المتجر معطّلاً. الزر يبقى في الصفحة للعمل بلا
 * JavaScript، ومع JavaScript يُضغط تلقائياً.
 */
const autoUpdateCart = () => {
	const form = qs( '[data-mp-cart-form]' );

	if ( ! form ) {
		return;
	}

	const button = qs( '[data-mp-update-cart]', form );
	let timer = 0;

	form.addEventListener( 'change', ( event ) => {
		if ( ! event.target.classList.contains( 'qty' ) ) {
			return;
		}

		window.clearTimeout( timer );

		timer = window.setTimeout( () => {
			if ( button ) {
				button.disabled = false;
				button.click();
			} else {
				form.submit();
			}
		}, DEBOUNCE );
	} );
};

/**
 * تحقّق فوري في صفحة الدفع.
 *
 * تحقّق واجهة فقط: ووكومرس يبقى هو من يتحقّق على الخادم. الغرض أن يعرف
 * الزائر بالخطأ عند مغادرة الحقل، لا بعد أن يضغط «أكمل الطلب» ويعود
 * لأعلى الصفحة يبحث عن السبب.
 */
const inlineValidation = () => {
	const form = qs( '[data-mp-checkout]' );

	if ( ! form ) {
		return;
	}

	const strings = settings().strings || {};

	const message = ( field ) => {
		const value = field.value.trim();
		const required = field.closest( '.validate-required' ) || field.required;

		if ( required && '' === value ) {
			return strings.required || '';
		}

		if ( '' === value ) {
			return '';
		}

		if ( field.closest( '.validate-phone' ) || 'tel' === field.type ) {
			// الأرقام فقط بعد تجاهل الفواصل والرموز الدولية.
			const digits = value.replace( /[^0-9]/g, '' );

			if ( digits.length < 8 ) {
				return strings.badPhone || '';
			}
		}

		if ( 'email' === field.type && ! /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test( value ) ) {
			return strings.badEmail || '';
		}

		return '';
	};

	const paint = ( field ) => {
		const row = field.closest( '.form-row' );

		if ( ! row ) {
			return true;
		}

		const error = message( field );
		let note = qs( '[data-mp-field-error]', row );

		if ( '' === error ) {
			field.removeAttribute( 'aria-invalid' );
			field.classList.remove( 'mp-field--invalid' );

			if ( note ) {
				note.remove();
			}

			if ( '' !== field.value.trim() ) {
				field.classList.add( 'mp-field--valid' );
			}

			return true;
		}

		field.setAttribute( 'aria-invalid', 'true' );
		field.classList.add( 'mp-field--invalid' );
		field.classList.remove( 'mp-field--valid' );

		if ( ! note ) {
			note = document.createElement( 'span' );
			note.className = 'mp-error';
			note.setAttribute( 'data-mp-field-error', '' );
			row.appendChild( note );
		}

		note.textContent = error;

		return false;
	};

	form.addEventListener(
		'blur',
		( event ) => {
			if ( event.target.matches( 'input, select, textarea' ) ) {
				paint( event.target );
			}
		},
		true
	);

	form.addEventListener( 'submit', ( event ) => {
		const fields = qsa( 'input, select, textarea', form ).filter(
			( field ) => 'hidden' !== field.type && ! field.disabled
		);

		let first = null;

		fields.forEach( ( field ) => {
			if ( ! paint( field ) && ! first ) {
				first = field;
			}
		} );

		if ( first ) {
			event.preventDefault();
			first.focus();
			first.scrollIntoView( { block: 'center', behavior: 'smooth' } );
		}
	} );
};

export default function checkout() {
	autoUpdateCart();
	inlineValidation();
}
