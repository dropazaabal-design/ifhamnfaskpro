<?php
/**
 * هيدر مصغّر لصفحة الدفع.
 *
 * لا قائمة ولا بحث ولا روابط: كل عنصر تنقّل في صفحة الدفع هو مخرج تسرّب.
 * يبقى الشعار (للطمأنينة أنك في المتجر الصحيح)، وعلامة الدفع الآمن، ورابط
 * واحد للرجوع إلى السلة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

?>
<header class="mp-checkout-header">
	<div class="mx-auto flex h-14 max-w-screen-md items-center gap-2 px-3">
		<a class="mp-checkout-header__back" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
			<?php
			// أيقونة الرجوع تشير يميناً أصلاً، وهو اتجاه «الخلف» في العربية.
			echo matjar_pro_get_icon( 'back', array( 'size' => 17 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			?>
			<?php esc_html_e( 'السلة', 'matjar-pro' ); ?>
		</a>

		<div class="grow text-center">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="text-base font-bold text-ink no-underline" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<span class="mp-checkout-header__secure">
			<?php echo matjar_pro_get_icon( 'lock', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php esc_html_e( 'دفع آمن', 'matjar-pro' ); ?>
		</span>
	</div>

	<?php
	/*
	 * الشريط كامل لا منتقىً: كان يُخفي التقسيط في القُمع، فيصل الزائر إلى
	 * الخطوة الأخيرة ليكتشف خياراً لم يعلم به. ما يراه في صفحة المنتج هو
	 * ما يراه هنا.
	 */
	?>
	<div class="mp-checkout-header__badges">
		<?php
		get_template_part(
			'template-parts/payment/methods',
			null,
			array( 'variant' => 'funnel' )
		);
		?>
	</div>
</header>
