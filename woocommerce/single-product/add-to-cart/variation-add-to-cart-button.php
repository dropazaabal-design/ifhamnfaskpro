<?php
/**
 * زر الإضافة لمنتج ذي متغيّرات.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>
<div class="woocommerce_variation_add_to_cart_variation"></div>

<div class="mp-buy__row" data-mp-buy-anchor>
	<?php
	do_action( 'woocommerce_before_add_to_cart_quantity' );

	woocommerce_quantity_input(
		array(
			'min_value' => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
			'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
		)
	);

	do_action( 'woocommerce_after_add_to_cart_quantity' );
	?>

	<button type="submit" class="mp-btn mp-btn--cta mp-btn--lg single_add_to_cart_button grow">
		<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 19 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
	</button>
</div>

<input type="hidden" name="mp-buy-now" value="" data-mp-buy-now-flag>

<button type="submit" class="mp-btn mp-btn--outline w-full single_add_to_cart_button" data-mp-buy-now>
	<?php esc_html_e( 'اشترِ الآن — تجاوز السلة', 'matjar-pro' ); ?>
</button>

<input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>">
<input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>">
<input type="hidden" name="variation_id" class="variation_id" value="0">
