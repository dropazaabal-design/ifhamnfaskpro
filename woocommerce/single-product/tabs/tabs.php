<?php
/**
 * تبويبات المنتج كأكورديون.
 *
 * التبويبات تخفي ما تحتها خلف نقرة وتُقرأ كعناصر ثانوية على الجوال.
 * الأكورديون يُبقي كل العناوين مرئية، فيعرف الزائر ما هو متاح.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( empty( $product_tabs ) ) {
	return;
}
?>
<div class="mp-accordion" data-mp-accordion>
	<?php $mp_first = true; ?>
	<?php foreach ( $product_tabs as $mp_key => $mp_tab ) : ?>
		<?php $mp_panel_id = 'mp-panel-' . sanitize_html_class( $mp_key ); ?>
		<div class="mp-accordion__item">
			<button type="button" class="mp-accordion__trigger" aria-expanded="<?php echo $mp_first ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr( $mp_panel_id ); ?>">
				<span class="grow text-start"><?php echo wp_kses_post( apply_filters( 'woocommerce_product_' . $mp_key . '_tab_title', $mp_tab['title'], $mp_key ) ); ?></span>
				<span class="mp-accordion__icon" aria-hidden="true">
					<?php echo matjar_pro_get_icon( 'chevron', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
			</button>

			<div class="mp-accordion__panel mp-prose" id="<?php echo esc_attr( $mp_panel_id ); ?>">
				<?php
				if ( isset( $mp_tab['callback'] ) ) {
					call_user_func( $mp_tab['callback'], $mp_key, $mp_tab );
				}
				?>
			</div>
		</div>
		<?php $mp_first = false; ?>
	<?php endforeach; ?>
</div>
