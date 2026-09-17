<?php
/**
 * سطر التقسيط.
 *
 * موضعه فوق زر الشراء لا تحته: اعتراض السعر يجب أن يُحلّ قبل لحظة القرار،
 * لا بعد أن يتخذها الزائر أو يتراجع عنها.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_line = matjar_pro_installment_line();

if ( '' === $mp_line ) {
	return;
}

$mp_badges = array_intersect_key(
	matjar_pro_active_badges(),
	array_flip( array( 'tabby', 'tamara' ) )
);
?>
<div class="mp-single__bnpl">
	<p class="m-0 text-sm font-bold text-[#7C2D12]"><?php echo wp_kses_post( $mp_line ); ?></p>

	<?php if ( ! empty( $mp_badges ) ) : ?>
		<div class="flex flex-wrap items-center gap-1.5">
			<?php foreach ( $mp_badges as $mp_slug => $mp_label ) : ?>
				<span class="mp-pill mp-pill--outline mp-badge--<?php echo esc_attr( $mp_slug ); ?>">
					<?php echo esc_html( $mp_label ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
