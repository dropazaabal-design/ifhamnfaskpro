<?php
/**
 * شارات الثقة وطرق الدفع في صفحة المنتج.
 *
 * موضعها بعد زر الشراء مباشرة: لحظة التردد تأتي بعد رؤية الزر لا قبلها.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_badges = matjar_pro_active_badges();

if ( empty( $mp_badges ) ) {
	return;
}
?>
<div class="mp-single__trust" aria-label="<?php esc_attr_e( 'طرق الدفع والضمانات', 'matjar-pro' ); ?>">
	<?php foreach ( $mp_badges as $mp_slug => $mp_label ) : ?>
		<?php if ( 'cod' === $mp_slug ) : ?>
			<span class="mp-pill mp-pill--success mp-badge--cod">
				<?php echo matjar_pro_get_icon( 'cash', array( 'size' => 13 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php echo esc_html( $mp_label ); ?>
			</span>
		<?php else : ?>
			<span class="mp-pill mp-pill--outline mp-badge--<?php echo esc_attr( $mp_slug ); ?>">
				<?php echo esc_html( $mp_label ); ?>
			</span>
		<?php endif; ?>
	<?php endforeach; ?>

	<span class="mp-pill mp-pill--outline">
		<?php echo matjar_pro_get_icon( 'return', array( 'size' => 13 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php esc_html_e( 'إرجاع مجاني', 'matjar-pro' ); ?>
	</span>
</div>
