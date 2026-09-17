<?php
/**
 * شريط التنقل السفلي — الجوال.
 *
 * يختفي مع التمرير للأسفل ويعود مع التمرير للأعلى (‏src/js/modules/scroll-bars.js)،
 * فلا يأكل مساحة الشاشة أثناء التصفّح.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

if ( ! matjar_pro_mod( 'matjar_pro_bottom_nav' ) ) {
	return;
}

$mp_shop  = matjar_pro_has_woocommerce() ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$mp_count = ( matjar_pro_has_woocommerce() && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
?>
<nav class="mp-bottom-nav lg:hidden" data-mp-bottom-nav aria-label="<?php esc_attr_e( 'التنقل السريع', 'matjar-pro' ); ?>">
	<a class="mp-bottom-nav__item<?php echo is_front_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php echo matjar_pro_get_icon( 'home', array( 'size' => 21 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span><?php esc_html_e( 'الرئيسية', 'matjar-pro' ); ?></span>
	</a>

	<a class="mp-bottom-nav__item<?php echo ( matjar_pro_has_woocommerce() && ( is_shop() || is_product_taxonomy() ) ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( $mp_shop ); ?>">
		<?php echo matjar_pro_get_icon( 'grid', array( 'size' => 21 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span><?php esc_html_e( 'الأقسام', 'matjar-pro' ); ?></span>
	</a>

	<button type="button" class="mp-bottom-nav__item" data-mp-drawer-open="search" aria-expanded="false" aria-controls="mp-search-drawer">
		<?php echo matjar_pro_get_icon( 'search', array( 'size' => 21 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span><?php esc_html_e( 'البحث', 'matjar-pro' ); ?></span>
	</button>

	<?php if ( matjar_pro_has_woocommerce() ) : ?>
		<a class="mp-bottom-nav__item<?php echo is_cart() ? ' is-active' : ''; ?>" href="<?php echo esc_url( wc_get_cart_url() ); ?>" data-mp-drawer-open="cart" aria-controls="mp-cart-drawer">
			<span class="relative">
				<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 21 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="mp-cart-count mp-cart-count--dot<?php echo $mp_count > 0 ? '' : ' hidden'; ?>" data-count="<?php echo esc_attr( (string) $mp_count ); ?>">
					<?php echo esc_html( number_format_i18n( $mp_count ) ); ?>
				</span>
			</span>
			<span><?php esc_html_e( 'السلة', 'matjar-pro' ); ?></span>
		</a>

		<a class="mp-bottom-nav__item<?php echo is_account_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>">
			<?php echo matjar_pro_get_icon( 'user', array( 'size' => 21 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php esc_html_e( 'حسابي', 'matjar-pro' ); ?></span>
		</a>
	<?php endif; ?>
</nav>
