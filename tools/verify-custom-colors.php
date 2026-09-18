<?php
/**
 * فحص ألوان التاجر — يُشغَّل بـ `npm run verify:colors`.
 *
 * الحرية التي أُعطيت للتاجر في اختيار الألوان لا قيمة لها إن أنتجت متجراً
 * لا يُقرأ. هذا الفحص يجرّب آلافاً من التركيبات العشوائية — لون علامة ولون
 * خلفية ولون زر ولون إبراز — ويتحقّق أن كل زوج نص/خلفية في الناتج يعبر
 * حدّ WCAG 2.1. يعيد رمز خروج غير صفري عند أول تركيبة تفشل.
 *
 * @package MatjarPro
 */

define( 'ABSPATH', __DIR__ );

if ( ! function_exists( '__' ) ) {
	/**
	 * @param string $text النص.
	 * @return string
	 */
	function __( $text ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		return $text;
	}
}
if ( ! function_exists( 'apply_filters' ) ) {
	/**
	 * @param string $hook  الخطّاف.
	 * @param mixed  $value القيمة.
	 * @return mixed
	 */
	function apply_filters( $hook, $value ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		return $value;
	}
}
if ( ! function_exists( 'esc_url' ) ) {
	/**
	 * @param string $url العنوان.
	 * @return string
	 */
	function esc_url( $url ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		return $url;
	}
}

// إعدادات التاجر تُضبط من هذا المصفوف قبل كل تركيبة.
$GLOBALS['mp_mods'] = array();

if ( ! function_exists( 'get_theme_mod' ) ) {
	/**
	 * @param string $key     المفتاح.
	 * @param mixed  $default القيمة الافتراضية.
	 * @return mixed
	 */
	function get_theme_mod( $key, $default = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		return $GLOBALS['mp_mods'][ $key ] ?? $default;
	}
}

require_once dirname( __DIR__ ) . '/inc/helpers.php';
require_once dirname( __DIR__ ) . '/inc/tokens.php';

/**
 * يمزج لوناً بخلفية بنسبة شفافية، كما تفعل طبقات القالب الشفّافة.
 *
 * @param string $hex   اللون الأمامي.
 * @param string $over  الخلفية.
 * @param float  $alpha الشفافية.
 * @return string
 */
function mp_blend( $hex, $over, $alpha ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$a = matjar_pro_hex_to_rgb( $hex );
	$b = matjar_pro_hex_to_rgb( $over );

	return sprintf(
		'#%02X%02X%02X',
		(int) round( $a[0] * $alpha + $b[0] * ( 1 - $alpha ) ),
		(int) round( $a[1] * $alpha + $b[1] * ( 1 - $alpha ) ),
		(int) round( $a[2] * $alpha + $b[2] * ( 1 - $alpha ) )
	);
}

/**
 * أزواج النص/الخلفية التي يجب أن تعبر في أي تركيبة.
 *
 * @param array $t الرموز الناتجة.
 * @return array
 */
function mp_pairs( $t ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	return array(
		array( 'heading on page', $t['ink'], $t['bg'], 4.5 ),
		array( 'heading on card', $t['ink'], $t['surface'], 4.5 ),
		array( 'body on page', $t['text'], $t['bg'], 4.5 ),
		array( 'body on card', $t['text'], $t['surface'], 4.5 ),
		array( 'muted on page', $t['muted'], $t['bg'], 4.5 ),
		array( 'muted on card', $t['muted'], $t['surface'], 4.5 ),
		array( 'CTA label', $t['cta-fg'], $t['cta'], 4.5 ),
		array( 'CTA hover label', $t['cta-fg'], $t['cta-hover'], 4.5 ),
		array( 'border on card', $t['border'], $t['surface'], 3.0 ),
		array( 'accent on card', $t['accent'], $t['surface'], 3.0 ),
		array( 'accent on ink', $t['accent-ink'], $t['ink'], 3.0 ),
		array( 'sale on card', $t['sale'], $t['surface'], 4.5 ),
		array( 'success on card', $t['success'], $t['surface'], 4.5 ),
		array( 'info on card', $t['info'], $t['surface'], 4.5 ),
		array( 'bnpl on card', $t['bnpl'], $t['surface'], 4.5 ),
		array( 'amber bar on track', $t['amber'], $t['border'], 3.0 ),
		array( 'COD label on tint', $t['success'], mp_blend( $t['success'], $t['surface'], 0.08 ), 4.5 ),
		array( 'COD note on tint', $t['text'], mp_blend( $t['success'], $t['surface'], 0.08 ), 4.5 ),
		array( 'BNPL line on tint', $t['bnpl'], mp_blend( $t['bnpl'], $t['surface'], 0.08 ), 4.5 ),
		array( 'saved pill on tint', $t['sale'], mp_blend( $t['sale'], $t['surface'], 0.08 ), 4.5 ),
		array( 'delivery on tint', $t['info'], mp_blend( $t['info'], $t['surface'], 0.08 ), 4.5 ),
	);
}

$rounds   = isset( $argv[1] ) ? max( 1, (int) $argv[1] ) : 2000;
$failures = array();
$checked  = 0;

mt_srand( 20260918 );

/**
 * لون عشوائي.
 *
 * @return string
 */
function mp_random_color() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	return sprintf( '#%02X%02X%02X', mt_rand( 0, 255 ), mt_rand( 0, 255 ), mt_rand( 0, 255 ) );
}

$palettes = array_keys( matjar_pro_palettes() );

for ( $round = 0; $round < $rounds; $round++ ) {
	// نصف الجولات بخلفيات عشوائية تماماً، ونصفها بخلفيات فاتحة كما يختار
	// التجّار عملياً — فلا يضيع الفحص كلّه في حالات لا تُشبه الاستخدام.
	$bg = 0 === $round % 2
		? mp_random_color()
		: sprintf( '#%02X%02X%02X', mt_rand( 230, 255 ), mt_rand( 230, 255 ), mt_rand( 230, 255 ) );

	$GLOBALS['mp_mods'] = array(
		'matjar_pro_palette'       => $palettes[ $round % count( $palettes ) ],
		'matjar_pro_cta_style'     => 'palette',
		'matjar_pro_color_primary' => mp_random_color(),
		'matjar_pro_color_bg'      => $bg,
		'matjar_pro_color_cta'     => mp_random_color(),
		'matjar_pro_color_accent'  => mp_random_color(),
	);

	$tokens = matjar_pro_resolved_tokens();

	foreach ( mp_pairs( $tokens ) as $pair ) {
		list( $label, $foreground, $background, $minimum ) = $pair;

		++$checked;
		$ratio = matjar_pro_contrast_ratio( $foreground, $background );

		if ( $ratio < $minimum ) {
			$failures[] = sprintf(
				"%-20s %5.2f < %.1f  |  primary %s  bg %s  cta %s  accent %s",
				$label,
				$ratio,
				$minimum,
				$GLOBALS['mp_mods']['matjar_pro_color_primary'],
				$GLOBALS['mp_mods']['matjar_pro_color_bg'],
				$GLOBALS['mp_mods']['matjar_pro_color_cta'],
				$GLOBALS['mp_mods']['matjar_pro_color_accent']
			);
		}
	}
}

printf( "جُرِّبت %d تركيبة لون · فُحص %d زوجاً\n", $rounds, $checked );

if ( $failures ) {
	echo "\nتركيبات تُنتج نصّاً غير مقروء:\n";

	foreach ( array_slice( $failures, 0, 15 ) as $line ) {
		echo '  ', $line, "\n";
	}

	printf( "\n%d زوجاً تحت حدّ WCAG.\n", count( $failures ) );
	exit( 1 );
}

echo "لا تركيبة واحدة تُنتج نصّاً غير مقروء.\n";
