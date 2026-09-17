<?php
/**
 * محتوى صفحة المنتج.
 *
 * عمود واحد على الجوال، وعمودان على الشاشات الكبيرة: الصور في جهة البداية
 * وكتلة الشراء في جهة النهاية وتُلصق عند التمرير، فلا يحتاج الديسكتوب شريطاً
 * سفلياً ثابتاً.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

global $product;
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'mp-single', $product ); ?>>

	<?php do_action( 'woocommerce_before_single_product' ); ?>

	<div class="lg:flex lg:items-start lg:gap-10">

		<div class="mp-single__media min-w-0 lg:w-[54%]">
			<?php
			/**
			 * صور المنتج.
			 *
			 * @hooked matjar_pro_show_product_images - 20
			 */
			do_action( 'woocommerce_before_single_product_summary' );
			?>
		</div>

		<div class="mp-single__summary min-w-0 lg:w-[46%]">
			<?php
			/**
			 * ملخّص المنتج بالترتيب السلوكي المعرَّف في inc/wc-product.php.
			 */
			do_action( 'woocommerce_single_product_summary' );
			?>
		</div>
	</div>

	<?php do_action( 'woocommerce_after_single_product_summary' ); ?>

	<?php do_action( 'woocommerce_after_single_product' ); ?>
</div>

<?php get_template_part( 'template-parts/product/sticky-buy' ); ?>
