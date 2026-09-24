<?php
/**
 * بطاقة المنتج في الحلقة.
 *
 * تصميم مينيمال بلا إطار: الصورة نفسها هي البطاقة، والمساحة السلبية حولها
 * هي ما يُبرزها. الإطار والظلّ يضيفان ضجيجاً بصرياً ينافس المنتج.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

global $product, $woocommerce_loop;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

// أول صفّ في الشبكة يُحمَّل بأولوية: صوره هي مرشّح LCP في صفحة الأقسام.
$mp_index    = isset( $woocommerce_loop['loop'] ) ? (int) $woocommerce_loop['loop'] : 0;
$mp_eager    = $mp_index > 0 && $mp_index <= 2;
$mp_discount = matjar_pro_sale_percentage( $product );
$mp_stock    = $product->is_in_stock();
?>
<li <?php wc_product_class( 'mp-product', $product ); ?>>

	<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

	<div class="mp-product__media">
		<a class="mp-product__media-link" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php echo matjar_pro_product_thumbnail( $product, $mp_eager ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>

		<?php if ( ! $mp_stock ) : ?>
			<span class="mp-product__flag mp-product__flag--out"><?php esc_html_e( 'نفدت الكمية', 'matjar-pro' ); ?></span>
		<?php elseif ( $mp_discount > 0 ) : ?>
			<span class="mp-product__flag mp-product__flag--sale">
				<?php
				printf(
					/* translators: %s: نسبة الخصم. */
					esc_html__( 'خصم %s', 'matjar-pro' ),
					'<span class="mp-num">' . esc_html( number_format_i18n( $mp_discount ) ) . '%</span>'
				);
				?>
			</span>
		<?php endif; ?>

		<?php do_action( 'woocommerce_before_shop_loop_item_title' ); ?>

		<?php echo matjar_pro_quick_add_button( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<div class="mp-product__body">
		<h2 class="mp-product__title">
			<a href="<?php the_permalink(); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
		</h2>

		<?php do_action( 'woocommerce_shop_loop_item_title' ); ?>

		<?php echo matjar_pro_product_rating( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

		<div class="mp-product__price">
			<?php echo wp_kses_post( $product->get_price_html() ); ?>
		</div>

		<?php do_action( 'woocommerce_after_shop_loop_item_title' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
</li>
