<?php
/**
 * حقل الكمية بزرّي زيادة ونقص.
 *
 * الأسهم الصغيرة في حقل الرقم الافتراضي هدف لمس صغير جداً على الجوال.
 * الزرّان هنا 40 بكسل لكل منهما، والحقل نفسه يبقى قابلاً للكتابة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/*
 * ووكومرس يمرّر product_name لا label، وقالبه الأصلي هو من يبني النص. عند
 * نسخ القالب يجب نسخ هذا السطر معه، وإلّا بقي الحقل بلا اسم يُقرأ.
 */
$mp_label = ! empty( $product_name )
	/* translators: %s: اسم المنتج. */
	? sprintf( __( 'كمية %s', 'matjar-pro' ), wp_strip_all_tags( $product_name ) )
	: __( 'الكمية', 'matjar-pro' );

if ( $max_value && $min_value === $max_value ) {
	?>
	<input type="hidden" id="<?php echo esc_attr( $input_id ); ?>" class="qty" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $min_value ); ?>">
	<?php
	return;
}
?>
<div class="mp-qty" data-mp-qty>
	<button type="button" class="mp-qty__btn" data-mp-qty-step="-1" aria-label="<?php esc_attr_e( 'إنقاص الكمية', 'matjar-pro' ); ?>">
		<?php echo matjar_pro_get_icon( 'minus', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>

	<label class="sr-only" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $mp_label ); ?></label>
	<input
		type="number"
		id="<?php echo esc_attr( $input_id ); ?>"
		class="mp-qty__input qty mp-num"
		name="<?php echo esc_attr( $input_name ); ?>"
		value="<?php echo esc_attr( $input_value ); ?>"
		aria-label="<?php echo esc_attr( $mp_label ); ?>"
		size="4"
		min="<?php echo esc_attr( $min_value ); ?>"
		<?php echo $max_value ? 'max="' . esc_attr( $max_value ) . '"' : ''; ?>
		<?php echo isset( $step ) ? 'step="' . esc_attr( $step ) . '"' : ''; ?>
		inputmode="numeric"
		autocomplete="off"
	>

	<button type="button" class="mp-qty__btn" data-mp-qty-step="1" aria-label="<?php esc_attr_e( 'زيادة الكمية', 'matjar-pro' ); ?>">
		<?php echo matjar_pro_get_icon( 'plus', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
</div>
