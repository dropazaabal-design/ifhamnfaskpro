<?php
/**
 * إضافة منتج بسيط إلى السلة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

if ( ! $product->is_in_stock() ) {
	return;
}

do_action( 'woocommerce_before_add_to_cart_form' );
?>
<form id="mp-buy" class="mp-buy cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data">

	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

	<div class="mp-buy__row" data-mp-buy-anchor>
		<?php
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		woocommerce_quantity_input(
			array(
				'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
				'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
				'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput
			)
		);

		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="mp-btn mp-btn--cta mp-btn--lg single_add_to_cart_button grow">
			<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 19 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
		</button>
	</div>

	<?php
	/*
	 * «اشترِ الآن» يتجاوز السلة إلى الدفع مباشرة، لمشتري الجوال القادم من
	 * إعلان ولا يريد تصفّح المزيد.
	 *
	 * الحقل المخفي هو ما يميّزه، ويُعبَّأ بـ JavaScript. بدون JavaScript يبقى
	 * زراً يضيف إلى السلة كالمعتاد بلا كسر.
	 */
	?>
	<input type="hidden" name="mp-buy-now" value="" data-mp-buy-now-flag>

	<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="mp-btn mp-btn--outline w-full" data-mp-buy-now>
		<?php esc_html_e( 'اشترِ الآن — تجاوز السلة', 'matjar-pro' ); ?>
	</button>

	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
</form>
<?php
do_action( 'woocommerce_after_add_to_cart_form' );
