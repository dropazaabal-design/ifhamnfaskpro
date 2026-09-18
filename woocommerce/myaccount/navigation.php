<?php
/**
 * قائمة الحساب.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_navigation' );
?>
<nav class="woocommerce-MyAccount-navigation mp-account__nav" aria-label="<?php esc_attr_e( 'قائمة الحساب', 'matjar-pro' ); ?>">
	<ul class="mp-account__nav-list mp-rail lg:flex-col lg:overflow-visible">
		<?php foreach ( wc_get_account_menu_items() as $mp_endpoint => $mp_label ) : ?>
			<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $mp_endpoint ) ); ?>">
				<a class="mp-account__nav-link" href="<?php echo esc_url( wc_get_account_endpoint_url( $mp_endpoint ) ); ?>">
					<span aria-hidden="true"><?php echo matjar_pro_account_icon( $mp_endpoint ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span><?php echo esc_html( $mp_label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
<?php
do_action( 'woocommerce_after_account_navigation' );
