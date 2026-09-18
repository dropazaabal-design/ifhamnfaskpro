<?php
/**
 * فحص سلامة إعدادات القالب — يُشغَّل بـ `npm run verify:settings`.
 *
 * يتحقّق أن كل إعداد في الـCustomizer له مُنقّي موجود وافتراضي معرَّف، وأن
 * كل إعداد يُقرأ في قالب له افتراضي، وأن لا افتراضي بلا تحكّم يصل إليه.
 * إعدادٌ بلا مُنقّي ثغرة، وإعدادٌ بلا افتراضي يُصيَّر فارغاً بلا سبب ظاهر،
 * وافتراضيٌّ بلا تحكّم كود ميت.
 *
 * @package MatjarPro
 */
define( 'ABSPATH', __DIR__ );
function __( $t, $d = '' ) { return $t; }            // phpcs:ignore
function apply_filters( $h, $v ) { return $v; }      // phpcs:ignore
function get_theme_mod( $k, $d = false ) { return $d; } // phpcs:ignore
function esc_url( $u ) { return $u; }                // phpcs:ignore

$root = dirname( __DIR__ );
require_once $root . '/inc/helpers.php';
require_once $root . '/inc/tokens.php';

$defaults = matjar_pro_defaults();
$src      = file_get_contents( $root . '/inc/customizer.php' );
$problems = array();

preg_match_all( "/add_setting\(\s*([^,]+),\s*array\((.*?)\)\s*\);/s", $src, $sets, PREG_SET_ORDER );

$known_core = array( 'sanitize_hex_color', 'esc_url_raw', 'sanitize_text_field', 'absint', 'sanitize_key' );

// المُنقّيات معرَّفة في نفس الملف، فتُستخرج منه نصّاً لا بتحميله.
preg_match_all( '/function (matjar_pro_sanitize_[a-z_]+)/', $src, $local );
$known_core = array_merge( $known_core, $local[1] );

foreach ( $sets as $set ) {
	if ( preg_match( "/sanitize_callback[^=]*=>\s*'([^']+)'/", $set[2], $cb ) ) {
		if ( ! function_exists( $cb[1] ) && ! in_array( $cb[1], $known_core, true ) ) {
			$problems[] = 'مُنقّي غير موجود: ' . $cb[1];
		}
	} else {
		$problems[] = 'إعداد بلا مُنقّي: ' . trim( $set[1] );
	}

	if ( preg_match( "/\\\$defaults\[\s*'([a-z_]+)'\s*\]/", $set[2], $d )
		&& ! array_key_exists( $d[1], $defaults ) ) {
		$problems[] = 'افتراضي مفقود: ' . $d[1];
	}
}

printf( "%d إعداداً مُسجَّلاً في الـCustomizer\n", count( $sets ) );

// كل إعداد يُقرأ في قالب يجب أن يكون له افتراضي.
$code = '';
foreach ( array( '/*.php', '/inc/*.php', '/template-parts/*/*.php', '/woocommerce/*/*.php' ) as $glob ) {
	foreach ( glob( $root . $glob ) as $file ) {
		$code .= file_get_contents( $file );
	}
}

preg_match_all( "/matjar_pro_mod\(\s*'([a-z_]+)'/", $code, $used );
// المفاتيح المبنية بالدمج تنتهي بشرطة سفلية: تُستثنى.
$read    = array_filter( array_unique( $used[1] ), static function ( $k ) { return '_' !== substr( $k, -1 ); } );
$missing = array_diff( $read, array_keys( $defaults ) );

foreach ( $missing as $key ) {
	$problems[] = 'إعداد يُقرأ بلا افتراضي: ' . $key;
}

// وكل افتراضي يجب أن يكون له تحكّم، وإلّا فهو إعداد ميت.
$controlled = array();
preg_match_all( "/'(matjar_pro_[a-z_]+)'/", $src, $named );
$controlled = array_unique( $named[1] );
$orphans    = array();

foreach ( array_keys( $defaults ) as $key ) {
	$dynamic = preg_match( '/^matjar_pro_(badge|social|trust|promo|hero|rail)_/', $key );

	if ( ! in_array( $key, $controlled, true ) && ! $dynamic ) {
		$orphans[] = $key;
	}
}

if ( $orphans ) {
	$problems[] = 'افتراضيات بلا تحكّم: ' . implode( ', ', $orphans );
}

echo $problems ? implode( "\n", array_unique( $problems ) ) . "\n" : "لا ملاحظات\n";
exit( $problems ? 1 : 0 );
