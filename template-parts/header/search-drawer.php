<?php
/**
 * لوح البحث — يُغطّي الشاشة على الجوال.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="mp-drawer" data-mp-drawer="search" data-mp-open="false" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'البحث في المتجر', 'matjar-pro' ); ?>">
	<div class="mp-drawer__backdrop" data-mp-drawer-backdrop></div>

	<div class="mp-drawer__panel mp-drawer__panel--top" id="mp-search-drawer">
		<div class="flex items-center gap-2 px-3 py-3">
			<div class="grow">
				<?php get_search_form( array( 'mp_context' => 'drawer' ) ); ?>
			</div>
			<button type="button" class="grid h-11 w-11 shrink-0 place-items-center rounded-lg text-ink transition hover:bg-cream" data-mp-drawer-close aria-label="<?php esc_attr_e( 'إغلاق البحث', 'matjar-pro' ); ?>">
				<?php echo matjar_pro_get_icon( 'close', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</div>
</div>
