<?php
/**
 * لوح السلة الجانبي.
 *
 * السلة لوح لا صفحة: التوجيه إلى صفحة السلة يقطع زخم التصفّح، واللوح يُبقي
 * الزائر في مكانه ويعرض تقدّم الشحن المجاني وعروضاً مكمّلة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

if ( ! matjar_pro_has_woocommerce() || matjar_pro_is_funnel() ) {
	return;
}
?>
<div class="mp-drawer" data-mp-drawer="cart" data-mp-open="false" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'سلة المشتريات', 'matjar-pro' ); ?>">
	<div class="mp-drawer__backdrop" data-mp-drawer-backdrop></div>

	<div class="mp-drawer__panel mp-drawer__panel--cart" id="mp-cart-drawer">
		<div class="flex h-14 items-center gap-2 border-b border-line px-4">
			<span class="grow font-bold text-ink"><?php esc_html_e( 'سلتك', 'matjar-pro' ); ?></span>
			<button type="button" class="-me-2 grid h-11 w-11 place-items-center rounded-lg text-ink transition hover:bg-cream" data-mp-drawer-close aria-label="<?php esc_attr_e( 'إغلاق السلة', 'matjar-pro' ); ?>">
				<?php echo matjar_pro_get_icon( 'close', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>

		<?php
		/*
		 * الصنف widget_shopping_cart_content هو ما تستبدله أجزاء ووكومرس
		 * بعد كل إضافة، فيتحدّث اللوح بمخرَج الخادم لا ببناء في المتصفح.
		 */
		?>
		<div class="widget_shopping_cart_content mp-drawer__body" data-mp-cart-content>
			<?php woocommerce_mini_cart(); ?>
		</div>
	</div>
</div>
