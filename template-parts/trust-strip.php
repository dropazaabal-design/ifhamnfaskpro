<?php
/**
 * شريط الثقة.
 *
 * موضعه مباشرة بعد الهيرو قرار مقصود: الزائر القادم من إعلان لا يعرف
 * المتجر، وأول سؤال في رأسه «هل هذا متجر حقيقي؟» لا «ما لديكم من منتجات؟».
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

if ( ! matjar_pro_mod( 'matjar_pro_trust_enabled' ) ) {
	return;
}

$mp_items = matjar_pro_trust_items();

if ( empty( $mp_items ) ) {
	return;
}
?>
<section class="mp-trust border-y border-line bg-surface" aria-label="<?php esc_attr_e( 'ضمانات المتجر', 'matjar-pro' ); ?>">
	<ul class="mp-rail mx-auto max-w-screen-xl list-none gap-2 px-3 py-4 sm:grid sm:grid-cols-4 sm:gap-4 sm:overflow-visible">
		<?php foreach ( $mp_items as $mp_item ) : ?>
			<li class="flex min-w-[80px] flex-1 flex-col items-center gap-1.5 text-center sm:min-w-0">
				<span class="text-info" aria-hidden="true">
					<?php echo matjar_pro_get_icon( $mp_item['icon'], array( 'size' => 24 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>
				<span class="text-xs font-semibold leading-snug text-ink sm:text-sm">
					<?php echo esc_html( $mp_item['title'] ); ?>
				</span>
				<?php if ( '' !== $mp_item['subtitle'] ) : ?>
					<span class="text-xs leading-snug text-faint">
						<?php echo esc_html( $mp_item['subtitle'] ); ?>
					</span>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ul>
</section>
