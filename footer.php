<?php
/**
 * تذييل الصفحة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_badges   = matjar_pro_active_badges();
$mp_cr       = matjar_pro_mod( 'matjar_pro_cr_number' );
$mp_vat      = matjar_pro_mod( 'matjar_pro_vat_number' );
$mp_whatsapp = preg_replace( '/[^0-9]/', '', (string) matjar_pro_mod( 'matjar_pro_whatsapp' ) );
?>
<footer class="mp-footer mt-10 bg-ink text-white">
	<div class="mx-auto max-w-screen-xl px-4 py-10">

		<?php if ( has_nav_menu( 'footer' ) ) : ?>
			<nav class="mb-8" aria-label="<?php esc_attr_e( 'قائمة الفوتر', 'matjar-pro' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'depth'          => 2,
						'menu_class'     => 'mp-footer__nav grid grid-cols-2 gap-x-6 gap-y-3 text-sm text-white/80 sm:grid-cols-3 lg:grid-cols-4',
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<?php if ( ! empty( $mp_badges ) ) : ?>
			<div class="mb-6 flex flex-wrap gap-1.5" aria-label="<?php esc_attr_e( 'طرق الدفع المتاحة', 'matjar-pro' ); ?>">
				<?php foreach ( $mp_badges as $mp_slug => $mp_label ) : ?>
					<span class="mp-badge mp-badge--<?php echo esc_attr( $mp_slug ); ?> rounded-md bg-white/10 px-2.5 py-1.5 text-[11px] font-semibold text-white/90">
						<?php echo esc_html( $mp_label ); ?>
					</span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="flex flex-col gap-3 border-t border-white/10 pt-6 text-xs leading-relaxed text-white/60">
			<?php if ( '' !== $mp_cr || '' !== $mp_vat ) : ?>
				<p class="m-0">
					<?php if ( '' !== $mp_cr ) : ?>
						<?php esc_html_e( 'السجل التجاري', 'matjar-pro' ); ?>
						<span dir="ltr"><?php echo esc_html( $mp_cr ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $mp_cr && '' !== $mp_vat ) : ?>
						<span aria-hidden="true"> · </span>
					<?php endif; ?>
					<?php if ( '' !== $mp_vat ) : ?>
						<?php esc_html_e( 'الرقم الضريبي', 'matjar-pro' ); ?>
						<span dir="ltr"><?php echo esc_html( $mp_vat ); ?></span>
					<?php endif; ?>
				</p>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'legal' ) ) : ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'legal',
						'container'      => false,
						'depth'          => 1,
						'menu_class'     => 'mp-footer__legal flex flex-wrap gap-x-4 gap-y-1',
						'fallback_cb'    => false,
					)
				);
				?>
			<?php endif; ?>

			<p class="m-0">
				<?php
				printf(
					/* translators: 1: السنة، 2: اسم المتجر. */
					esc_html__( '© %1$s %2$s. جميع الحقوق محفوظة.', 'matjar-pro' ),
					esc_html( wp_date( 'Y' ) ),
					esc_html( get_bloginfo( 'name' ) )
				);
				?>
			</p>
		</div>
	</div>
</footer>

<?php if ( '' !== $mp_whatsapp ) : ?>
	<a class="mp-fab fixed bottom-4 end-4 z-40 grid h-12 w-12 place-items-center rounded-full bg-success text-white no-underline shadow-lg transition hover:brightness-110"
		href="<?php echo esc_url( 'https://wa.me/' . $mp_whatsapp ); ?>"
		target="_blank" rel="noopener"
		aria-label="<?php esc_attr_e( 'تواصل معنا عبر واتساب', 'matjar-pro' ); ?>">
		<?php echo matjar_pro_get_icon( 'whatsapp', array( 'size' => 22 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
