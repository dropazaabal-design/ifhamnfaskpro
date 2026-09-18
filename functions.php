<?php
/**
 * نقطة إقلاع قالب Matjar Pro.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

define( 'MATJAR_PRO_VERSION', '0.1.0' );
define( 'MATJAR_PRO_DIR', get_template_directory() );
define( 'MATJAR_PRO_URI', get_template_directory_uri() );

/**
 * يعيد رقم إصدار ملف أصل بناءً على وقت تعديله، ليُبطل كاش المتصفح تلقائياً.
 *
 * @param string $relative مسار الملف نسبةً إلى جذر القالب.
 * @return string
 */
function matjar_pro_asset_version( $relative ) {
	$path = MATJAR_PRO_DIR . '/' . ltrim( $relative, '/' );

	if ( file_exists( $path ) ) {
		return (string) filemtime( $path );
	}

	return MATJAR_PRO_VERSION;
}

require_once MATJAR_PRO_DIR . '/inc/helpers.php';
require_once MATJAR_PRO_DIR . '/inc/tokens.php';
require_once MATJAR_PRO_DIR . '/inc/setup.php';
require_once MATJAR_PRO_DIR . '/inc/assets.php';
require_once MATJAR_PRO_DIR . '/inc/customizer.php';
require_once MATJAR_PRO_DIR . '/inc/front-page.php';
require_once MATJAR_PRO_DIR . '/inc/pwa.php';
require_once MATJAR_PRO_DIR . '/inc/cro.php';

if ( matjar_pro_has_woocommerce() ) {
	require_once MATJAR_PRO_DIR . '/inc/woocommerce.php';
	require_once MATJAR_PRO_DIR . '/inc/wc-loop.php';
	require_once MATJAR_PRO_DIR . '/inc/wc-product.php';
	require_once MATJAR_PRO_DIR . '/inc/wc-checkout.php';
	require_once MATJAR_PRO_DIR . '/inc/wc-filter.php';
	require_once MATJAR_PRO_DIR . '/inc/wc-account.php';
}
