<?php
/**
 * جرد ألوان المنصّات — يُشغَّل بـ `npm run inventory`.
 *
 * القيم في هذا الملف منقولة حرفياً من أنظمة تصميم منشورة، ومقيسة هنا
 * بدوال القالب نفسها: التدرّج والتشبّع والسطوع والتباين على الأبيض وعلى
 * الأسود. عليه بُنيت اللوحات الأربع في inc/tokens.php.
 *
 * المصادر:
 *   Shopify Dawn        config/settings_data.json
 *   Shopify Polaris     polaris-tokens/src/themes/base/color.ts + colors.ts
 *   Salla theme-raed    tailwind.config.js + src/views/layouts/master.twig
 *   WooCommerce Storefront  inc/customizer/class-storefront-customizer.php
 *   WordPress TT4       theme.json
 *   IBM Carbon          packages/colors
 *
 * @package MatjarPro
 */
define( 'ABSPATH', __DIR__ );
function __( $t, $d = '' ) { return $t; }
function apply_filters( $h, $v ) { return $v; }
function get_theme_mod( $k, $d = false ) { return $d; }
function esc_url( $u ) { return $u; }
require_once dirname( __DIR__ ) . '/inc/helpers.php';
require_once dirname( __DIR__ ) . '/inc/tokens.php';

function hsl( $hex ) {
	list( $r, $g, $b ) = array_map( function ( $v ) { return $v / 255; }, matjar_pro_hex_to_rgb( $hex ) );
	$max = max( $r, $g, $b ); $min = min( $r, $g, $b ); $d = $max - $min;
	$l = ( $max + $min ) / 2;
	if ( 0.0 === (float) $d ) { return array( 0, 0, round( $l * 100 ) ); }
	if ( $max === $r )      { $h = fmod( 60 * ( ( $g - $b ) / $d ) + 360, 360 ); }
	elseif ( $max === $g )  { $h = 60 * ( ( $b - $r ) / $d ) + 120; }
	else                    { $h = 60 * ( ( $r - $g ) / $d ) + 240; }
	$s = $l > 0.5 ? $d / ( 2 - $max - $min ) : $d / ( $max + $min );
	return array( round( $h ), round( $s * 100 ), round( $l * 100 ) );
}

// دافئ = تدرّج بين ٢٠° و٧٠° بتشبّع محسوس. هذه هي التي مُنعت.
function warm( $hex ) {
	list( $h, $s ) = hsl( $hex );
	return $s >= 20 && $h >= 18 && $h <= 72;
}

$inventory = array(
	array( 'Shopify Dawn', 'أرضية ١ و٣ و٤', '#FFFFFF' ),
	array( 'Shopify Dawn', 'نصّ / زر ١', '#121212' ),
	array( 'Shopify Dawn', 'أرضية ٢ (رمادي)', '#F3F3F3' ),
	array( 'Shopify Dawn', 'أرضية ٣ (كحلي رمادي)', '#242833' ),
	array( 'Shopify Dawn', 'أرضية ٥ (أزرق ملكي)', '#334FB4' ),
	array( 'Shopify Polaris', 'العلامة / التفاعل', '#303030' ),
	array( 'Shopify Polaris', 'العلامة — تحويم', '#1A1A1A' ),
	array( 'Shopify Polaris', 'نصّ العلامة', '#4A4A4A' ),
	array( 'Shopify Polaris', 'نصّ باهت', '#616161' ),
	array( 'Shopify Polaris', 'حدّ', '#E3E3E3' ),
	array( 'Shopify Polaris', 'رمادي ١٠', '#CCCCCC' ),
	array( 'Shopify Polaris', 'خطر / خصم', '#C70A24' ),
	array( 'Shopify Polaris', 'خطر — تحويم', '#A30A24' ),
	array( 'Shopify Polaris', 'نجاح', '#047B5D' ),
	array( 'Shopify Polaris', 'تأكيد / تركيز', '#005BD3' ),
	array( 'Salla theme-raed', 'غامق', '#1D1F1F' ),
	array( 'Salla theme-raed', 'أغمق', '#0E0F0F' ),
	array( 'Salla theme-raed', 'خطر', '#AE0A0A' ),
	array( 'WooCommerce Storefront', 'أرضية', '#FFFFFF' ),
	array( 'WooCommerce Storefront', 'نصّ', '#6D6D6D' ),
	array( 'WooCommerce Storefront', 'عنوان', '#333333' ),
	array( 'WooCommerce Storefront', 'إبراز (بنفسجي ووكومرس)', '#7F54B3' ),
	array( 'WooCommerce Storefront', 'أرضية التذييل', '#F0F0F0' ),
	array( 'WordPress TT4', 'أساس', '#F9F9F9' ),
	array( 'WordPress TT4', 'تباين', '#111111' ),
	array( 'WordPress TT4', 'تباين ٢', '#636363' ),
	array( 'WordPress TT4', 'تباين ٣', '#A4A4A4' ),
	array( 'WordPress TT4', 'إبراز ١ (دافئ)', '#CFCABE' ),
	array( 'WordPress TT4', 'إبراز ٢ (دافئ)', '#C2A990' ),
	array( 'WordPress TT4', 'إبراز ٣ (دافئ)', '#D8613C' ),
	array( 'IBM Carbon', 'أزرق ٦٠', '#0F62FE' ),
	array( 'IBM Carbon', 'أزرق ٧٠', '#0043CE' ),
	array( 'IBM Carbon', 'أزرق ٩٠ (كحلي عميق)', '#001D6C' ),
	array( 'IBM Carbon', 'أزرق ١٠٠', '#001141' ),
);

printf( "%-24s %-26s %-9s %-16s %-7s %-7s %s\n",
	'المصدر', 'الدور', 'اللون', 'تدرّج/تشبّع/سطوع', 'على أبيض', 'على أسود', 'دافئ؟' );
echo str_repeat( '-', 116 ), "\n";

$warm_count = 0;

foreach ( $inventory as $row ) {
	list( $source, $role, $hex ) = $row;
	list( $h, $s, $l )           = hsl( $hex );
	$is_warm                     = warm( $hex );
	$warm_count                 += $is_warm ? 1 : 0;

	printf(
		"%-24s %-26s %-9s %3d° / %3d%% / %3d%%   %5.2f   %5.2f   %s\n",
		$source, $role, $hex, $h, $s, $l,
		matjar_pro_contrast_ratio( $hex, '#FFFFFF' ),
		matjar_pro_contrast_ratio( $hex, '#121212' ),
		$is_warm ? 'دافئ ✗' : '—'
	);
}

printf( "\n%d من %d قيمة دافئة — وكلّها من لوحات إبراز لا من أزرار ولا أرضيات.\n", $warm_count, count( $inventory ) );
