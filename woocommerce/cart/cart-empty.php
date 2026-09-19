<?php
/**
 * السلة فارغة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_cart_is_empty' );
?>
<div class="mx-auto max-w-md py-8 text-center">
	<span class="mx-auto mb-4 grid h-16 w-16 place-items-center rounded-full bg-surface text-faint" aria-hidden="true">
		<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 28 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</span>

	<h2 class="m-0 text-xl font-bold text-ink"><?php esc_html_e( 'سلتك فارغة', 'matjar-pro' ); ?></h2>
	<p class="mb-6 mt-2 text-sm leading-relaxed text-body">
		<?php esc_html_e( 'تصفّح الأقسام واختر ما يناسبك — الشحن سريع والإرجاع مجاني.', 'matjar-pro' ); ?>
	</p>

	<a class="mp-btn mp-btn--cta mp-btn--lg" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
		<?php esc_html_e( 'ابدأ التصفّح', 'matjar-pro' ); ?>
	</a>
</div>
