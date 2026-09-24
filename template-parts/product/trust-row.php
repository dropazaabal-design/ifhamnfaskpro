<?php
/**
 * طرق الدفع والضمانات في صفحة المنتج.
 *
 * موضعها بعد زر الشراء مباشرة: لحظة التردد تأتي بعد رؤية الزر لا قبلها،
 * وهناك يُسأل «هل أدفع عند الاستلام؟».
 *
 * طرق الدفع تُطبع بالمكوّن المشترك، فما يراه الزائر هنا هو نفسه الذي
 * سيراه في لوح السلة وفي قُمع الدفع: لا مفاجأة في الخطوة الأخيرة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_guarantees = array(
	array(
		'icon'  => 'return',
		'label' => matjar_pro_mod( 'matjar_pro_returns_label' ),
	),
	array(
		'icon'  => 'shield',
		'label' => matjar_pro_mod( 'matjar_pro_secure_label' ),
	),
);

$mp_guarantees = array_values(
	array_filter(
		$mp_guarantees,
		static function ( $item ) {
			return '' !== (string) $item['label'];
		}
	)
);

$mp_has_payments = matjar_pro_mod( 'matjar_pro_payment_on_product' )
	&& (bool) matjar_pro_grouped_payment_methods();

if ( ! $mp_has_payments && ! $mp_guarantees ) {
	return;
}
?>
<div class="mp-single__trust">
	<?php
	if ( $mp_has_payments ) {
		// سطر التقسيط فوق الزر يذكر المزوّدين بسعرهم، فتُحجب مجموعتهم هنا.
		$mp_skip = '' === matjar_pro_installment_line() ? array() : array( 'split' );

		get_template_part(
			'template-parts/payment/methods',
			null,
			array(
				'variant' => 'panel',
				'skip'    => $mp_skip,
			)
		);
	}
	?>

	<?php if ( $mp_guarantees ) : ?>
		<div class="mp-single__guarantees">
			<?php foreach ( $mp_guarantees as $mp_item ) : ?>
				<span class="mp-pill mp-pill--outline">
					<?php echo matjar_pro_get_icon( $mp_item['icon'], array( 'size' => 13 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php echo esc_html( $mp_item['label'] ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
