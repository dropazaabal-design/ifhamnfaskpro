<?php
/**
 * شريط الشراء الثابت — الجوال.
 *
 * لا يظهر إلا بعد اختفاء زر الإضافة الأصلي من الشاشة، فلا يوجد زران
 * بالوظيفة نفسها في وقت واحد. الديسكتوب لا يحتاجه لأن كتلة الشراء ملصقة
 * في عمودها.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! matjar_pro_mod( 'matjar_pro_sticky_buy_bar' ) || ! $product instanceof WC_Product ) {
	return;
}

if ( ! $product->is_in_stock() ) {
	return;
}
?>
<div class="mp-sticky-bar mp-buy-bar lg:hidden" data-mp-buy-bar data-mp-hidden="true">
	<div class="flex items-center gap-3 px-3 py-2.5">
		<?php if ( $product->get_image_id() ) : ?>
			<?php
			echo wp_get_attachment_image(
				$product->get_image_id(),
				'woocommerce_gallery_thumbnail',
				false,
				array(
					'class'   => 'mp-buy-bar__thumb',
					'alt'     => '',
					'loading' => 'lazy',
				)
			);
			?>
		<?php endif; ?>

		<div class="flex min-w-0 shrink-0 flex-col">
			<span class="mp-buy-bar__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
		</div>

		<?php if ( $product->is_type( 'simple' ) && $product->is_purchasable() ) : ?>
			<button type="button" class="mp-btn mp-btn--cta grow" data-mp-quick-add="<?php echo esc_attr( (string) $product->get_id() ); ?>">
				<span class="mp-product__quick-idle"><?php esc_html_e( 'أضف إلى السلة', 'matjar-pro' ); ?></span>
				<span class="mp-product__quick-done"><?php esc_html_e( 'تمت الإضافة', 'matjar-pro' ); ?></span>
				<span class="mp-product__quick-busy" aria-hidden="true"></span>
			</button>
		<?php else : ?>
			<a class="mp-btn mp-btn--cta grow" href="#mp-buy">
				<?php esc_html_e( 'اختر الخيارات', 'matjar-pro' ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
