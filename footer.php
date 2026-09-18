<?php
/**
 * تذييل الصفحة، والعناصر الثابتة والألواح.
 *
 * الألواح وشريط التنقل السفلي في نهاية المستند عن قصد: كلها عناصر مثبّتة
 * بالتنسيق، ووضعها متأخراً يجعل ترتيب لوحة المفاتيح منطقياً بعد المحتوى.
 *
 * داخل قُمع الدفع لا شيء من هذا يُطبع: تذييل واحد بسطر طمأنينة، وبلا
 * روابط ولا ألواح ولا شريط تنقّل.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_funnel   = matjar_pro_has_woocommerce() && matjar_pro_is_funnel();
$mp_cr       = trim( (string) matjar_pro_mod( 'matjar_pro_cr_number' ) );
$mp_vat      = trim( (string) matjar_pro_mod( 'matjar_pro_vat_number' ) );
$mp_whatsapp = preg_replace( '/[^0-9]/', '', (string) matjar_pro_mod( 'matjar_pro_whatsapp' ) );
?>

<?php if ( $mp_funnel ) : ?>

	<footer class="mp-checkout-footer">
		<div class="mx-auto flex max-w-screen-md flex-col items-center gap-2 px-4 py-6 text-center">
			<div class="flex flex-wrap items-center justify-center gap-4">
				<span class="mp-checkout-footer__item">
					<?php echo matjar_pro_get_icon( 'lock', array( 'size' => 14 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'معلوماتك محميّة', 'matjar-pro' ); ?>
				</span>
				<span class="mp-checkout-footer__item">
					<?php echo matjar_pro_get_icon( 'return', array( 'size' => 14 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'إرجاع مجاني', 'matjar-pro' ); ?>
				</span>
			</div>

			<?php if ( '' !== $mp_cr || '' !== $mp_vat ) : ?>
				<p class="m-0 text-[11px] leading-relaxed text-faint">
					<?php if ( '' !== $mp_cr ) : ?>
						<?php esc_html_e( 'السجل التجاري', 'matjar-pro' ); ?>
						<span class="mp-num"><?php echo esc_html( $mp_cr ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $mp_cr && '' !== $mp_vat ) : ?>
						<span aria-hidden="true"> · </span>
					<?php endif; ?>
					<?php if ( '' !== $mp_vat ) : ?>
						<?php esc_html_e( 'الرقم الضريبي', 'matjar-pro' ); ?>
						<span class="mp-num"><?php echo esc_html( $mp_vat ); ?></span>
					<?php endif; ?>
				</p>
			<?php endif; ?>
		</div>
	</footer>

<?php else : ?>

	<?php
	$mp_widgets  = array( 'footer-1', 'footer-2', 'footer-3' );
	$mp_has_cols = (bool) array_filter( $mp_widgets, 'is_active_sidebar' );
	$mp_badges   = matjar_pro_active_badges();
	?>
	<footer class="mp-footer bg-inverse text-inverse-ink">
		<div class="mx-auto max-w-screen-xl px-4 py-10">

			<?php if ( $mp_has_cols ) : ?>
				<div class="mb-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
					<?php foreach ( $mp_widgets as $mp_area ) : ?>
						<?php if ( is_active_sidebar( $mp_area ) ) : ?>
							<div class="mp-footer__col">
								<?php dynamic_sidebar( $mp_area ); ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php elseif ( has_nav_menu( 'footer' ) ) : ?>
				<nav class="mb-8" aria-label="<?php esc_attr_e( 'قائمة الفوتر', 'matjar-pro' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'footer',
							'container'      => false,
							'depth'          => 1,
							'menu_class'     => 'mp-footer__nav',
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/footer/social' ); ?>

			<?php if ( ! empty( $mp_badges ) ) : ?>
				<div class="mb-6">
					<?php
					get_template_part(
						'template-parts/payment/methods',
						null,
						array(
							'variant' => 'row',
							'dark'    => true,
						)
					);
					?>
				</div>
			<?php endif; ?>

			<div class="flex flex-col gap-3 border-t border-white/10 pt-6 text-xs leading-relaxed text-white/60">
				<?php if ( '' !== $mp_cr || '' !== $mp_vat ) : ?>
					<p class="m-0">
						<?php if ( '' !== $mp_cr ) : ?>
							<?php esc_html_e( 'السجل التجاري', 'matjar-pro' ); ?>
							<span class="mp-num"><?php echo esc_html( $mp_cr ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== $mp_cr && '' !== $mp_vat ) : ?>
							<span aria-hidden="true"> · </span>
						<?php endif; ?>
						<?php if ( '' !== $mp_vat ) : ?>
							<?php esc_html_e( 'الرقم الضريبي', 'matjar-pro' ); ?>
							<span class="mp-num"><?php echo esc_html( $mp_vat ); ?></span>
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
							'menu_class'     => 'mp-footer__legal',
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
		<a class="mp-fab"
			href="<?php echo esc_url( 'https://wa.me/' . $mp_whatsapp ); ?>"
			target="_blank" rel="noopener"
			aria-label="<?php esc_attr_e( 'تواصل معنا عبر واتساب', 'matjar-pro' ); ?>">
			<?php echo matjar_pro_get_icon( 'whatsapp', array( 'size' => 22 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/header/nav-drawer' );
	get_template_part( 'template-parts/header/search-drawer' );
	get_template_part( 'template-parts/cart/drawer' );
	get_template_part( 'template-parts/header/bottom-nav' );
	?>

<?php endif; ?>

<?php
/* منطقة إعلان مباشرة لقارئات الشاشة: تُملأ عند الإضافة إلى السلة. */
?>
<p class="sr-only" role="status" aria-live="polite" data-mp-live></p>

<?php wp_footer(); ?>
</body>
</html>
