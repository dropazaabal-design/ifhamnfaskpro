<?php
/**
 * لوح الفلترة — لوح سفلي على الجوال.
 *
 * لوح سفلي لا جانبي: الفلترة إجراء يخصّ القائمة التي يراها الزائر، واللوح
 * السفلي يُبقي أعلى الشاشة مرئياً ويأتي من جهة الإبهام.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

if ( ! matjar_pro_has_filters() ) {
	return;
}
?>
<div class="mp-drawer lg:hidden" data-mp-drawer="filter" data-mp-open="false" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'فلترة المنتجات', 'matjar-pro' ); ?>">
	<div class="mp-drawer__backdrop" data-mp-drawer-backdrop></div>

	<div class="mp-drawer__panel mp-drawer__panel--sheet" id="mp-filter-drawer">
		<div class="mp-sheet__grip" aria-hidden="true"></div>

		<div class="flex h-12 items-center gap-2 border-b border-line px-4">
			<span class="grow font-bold text-ink"><?php esc_html_e( 'فلترة', 'matjar-pro' ); ?></span>
			<button type="button" class="-me-2 grid h-11 w-11 place-items-center rounded-lg text-ink transition hover:bg-page" data-mp-drawer-close aria-label="<?php esc_attr_e( 'إغلاق الفلترة', 'matjar-pro' ); ?>">
				<?php echo matjar_pro_get_icon( 'close', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>

		<div class="mp-drawer__body">
			<?php get_template_part( 'template-parts/shop/filter-body', null, array( 'context' => 'sheet' ) ); ?>
		</div>

		<div class="border-t border-line p-3">
			<button type="button" class="mp-btn mp-btn--cta w-full" data-mp-drawer-close>
				<?php esc_html_e( 'عرض النتائج', 'matjar-pro' ); ?>
			</button>
		</div>
	</div>
</div>
