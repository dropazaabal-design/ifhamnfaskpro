/**
 * «اشترِ الآن» يتجاوز السلة.
 *
 * الزر نفسه زر إضافة عادي؛ الحقل المخفي هو ما يُخبر PHP أن يحوّل إلى صفحة
 * الدفع. بدون JavaScript يعمل الزر كإضافة عادية بلا كسر.
 */
export default function buyNow() {
	document.addEventListener( 'click', ( event ) => {
		const button = event.target.closest( '[data-mp-buy-now]' );

		if ( ! button ) {
			return;
		}

		const form = button.closest( 'form' );
		const flag = form ? form.querySelector( '[data-mp-buy-now-flag]' ) : null;

		if ( flag ) {
			flag.value = '1';
		}
	} );
}
