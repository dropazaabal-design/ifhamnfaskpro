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

$mp_badges = array_intersect_key(
	matjar_pro_active_badges(),
	array_flip( array( 'applepay', 'mada', 'visa', 'mastercard', 'cod' ) )
);
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

	<?php if ( ! empty( $mp_badges ) ) : ?>
		<div class="mp-checkout-header__badges">
			<?php foreach ( $mp_badges as $mp_slug => $mp_label ) : ?>
				<span class="mp-pill mp-pill--outline mp-badge--<?php echo esc_attr( $mp_slug ); ?>">
					<?php echo esc_html( $mp_label ); ?>
				</span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</header>
