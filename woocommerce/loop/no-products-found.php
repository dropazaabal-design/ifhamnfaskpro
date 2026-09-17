<?php
/**
 * لا منتجات مطابقة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="mx-auto max-w-md rounded-2xl border border-line bg-surface p-8 text-center">
	<span class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-full bg-cream text-faint" aria-hidden="true">
		<?php echo matjar_pro_get_icon( 'search', array( 'size' => 22 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</span>
	<h2 class="m-0 text-lg font-bold text-ink"><?php esc_html_e( 'لا منتجات مطابقة', 'matjar-pro' ); ?></h2>
	<p class="mb-5 mt-2 text-sm leading-relaxed text-body">
		<?php esc_html_e( 'جرّب تخفيف الفلترة أو تصفّح قسماً آخر.', 'matjar-pro' ); ?>
	</p>
	<a class="mp-btn mp-btn--outline" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
		<?php esc_html_e( 'كل المنتجات', 'matjar-pro' ); ?>
	</a>
</div>
