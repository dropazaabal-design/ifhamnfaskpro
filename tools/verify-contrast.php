<?php
/**
 * فحص تباين اللوحات اللونية — يُشغَّل بـ `npm run verify:contrast`.
 *
 * يفحص كل زوج لون/خلفية في اللوحات الأربع مقابل حدود WCAG 2.1:
 * 4.5:1 للنص العادي و3:1 لرسوم الواجهة. يعيد رمز خروج غير صفري عند أي
 * إخفاق، فيصلح للاستخدام في CI ويمنع تسلّل لوحة غير مقروءة إلى إصدار.
 *
 * @package MatjarPro
 */

define( 'ABSPATH', __DIR__ );

// بدائل دوال ووردبريس، حتى يعمل الفحص من سطر الأوامر بلا تحميل ووردبريس.
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
	 * @param string $hook الخطّاف.
	 * @param mixed  $value القيمة.
	 * @return mixed
	 */
	function apply_filters( $hook, $value ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		return $value;
	}
}
if ( ! function_exists( 'get_theme_mod' ) ) {
	/**
	 * @param string $key     المفتاح.
	 * @param mixed  $default القيمة الافتراضية.
	 * @return mixed
	 */
	function get_theme_mod( $key, $default = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		return $default;
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

require_once dirname( __DIR__ ) . '/inc/helpers.php';
require_once dirname( __DIR__ ) . '/inc/tokens.php';

/**
 * يمزج لوناً بخلفية بنسبة شفافية.
 *
 * كتل طرق الدفع تُبنى بخلفيات شفّافة فوق السطح (‎pay-cash/0.07‎ مثلاً)، وما
 * يراه المستخدم هو الناتج المُركَّب لا اللون الأصلي. لذلك يُفحص المزيج.
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

$failures = 0;
$total    = 0;

printf( "%-8s | %-30s | %6s | %5s | %s\n", 'palette', 'pair', 'ratio', 'min', 'result' );
echo str_repeat( '-', 72 ), "\n";

foreach ( matjar_pro_palettes() as $name => $palette ) {
	$t  = $palette['tokens'];
	$fg = matjar_pro_readable_foreground( $t['cta'], '#FFFFFF', $t['ink'] );

	$pairs = array(
		array( 'CTA + computed label', $fg, $t['cta'], 4.5 ),
		array( 'CTA hover + label', $fg, $t['cta-hover'], 4.5 ),
		array( 'body text on page', $t['text'], $t['bg'], 4.5 ),
		array( 'muted text on page', $t['muted'], $t['bg'], 4.5 ),
		array( 'heading on page', $t['ink'], $t['bg'], 4.5 ),
		array( 'body text on card', $t['text'], $t['surface'], 4.5 ),
		array( 'sale badge + white', '#FFFFFF', $t['sale'], 4.5 ),
		array( 'success badge + white', '#FFFFFF', $t['success'], 4.5 ),
		array( 'info badge + white', '#FFFFFF', $t['info'], 4.5 ),
		// العنبري شريط تقدّم لا خلفية نص: حدّه 3:1 كرسم واجهة، ومقابل
		// المجرى الذي يُرسم عليه لا مقابل السطح تحت الكتلة.
		array( 'amber bar on track', $t['amber'], $t['border'], 3.0 ),
		array( 'ship bar fill on track', $t['amber'], $t['border'], 3.0 ),
		array( 'accent graphic on card', $t['accent'], $t['surface'], 3.0 ),
		array( 'accent graphic on ink', $t['accent-ink'], $t['ink'], 3.0 ),

		// كتل طرق الدفع: النص فوق المزيج المُركَّب الذي يراه المستخدم.
		array( 'COD label on COD card', $t['success'], mp_blend( $t['success'], $t['surface'], 0.07 ), 4.5 ),
		array( 'COD note on COD card', $t['text'], mp_blend( $t['success'], $t['surface'], 0.07 ), 4.5 ),
		array( 'COD tick + white', '#FFFFFF', $t['success'], 4.5 ),
		array( 'COD gateway label', $t['ink'], mp_blend( $t['success'], $t['surface'], 0.07 ), 4.5 ),
		array( 'card group icon', $t['info'], mp_blend( $t['info'], $t['surface'], 0.10 ), 3.0 ),
		array( 'wallet group icon', $t['ink'], mp_blend( $t['ink'], $t['surface'], 0.08 ), 3.0 ),
		array( 'split group icon', $t['bnpl'], mp_blend( $t['bnpl'], $t['surface'], 0.10 ), 3.0 ),
		array( 'split mark text', $t['bnpl'], mp_blend( $t['bnpl'], $t['surface'], 0.07 ), 4.5 ),
		array( 'BNPL line on block', $t['bnpl'], mp_blend( $t['bnpl'], $t['surface'], 0.07 ), 4.5 ),
		array( 'pay mark text on cream', $t['text'], $t['bg'], 4.5 ),
		array( 'saved pill on card', $t['sale'], mp_blend( $t['sale'], $t['surface'], 0.08 ), 4.5 ),
	);

	foreach ( $pairs as $pair ) {
		list( $label, $foreground, $background, $minimum ) = $pair;

		$ratio = matjar_pro_contrast_ratio( $foreground, $background );
		$pass  = $ratio >= $minimum;
		$total++;

		if ( ! $pass ) {
			$failures++;
		}

		printf(
			"%-8s | %-30s | %6.2f | %5.1f | %s\n",
			$name,
			$label,
			$ratio,
			$minimum,
			$pass ? 'pass' : 'FAIL'
		);
	}

	echo str_repeat( '-', 72 ), "\n";
}

if ( $failures > 0 ) {
	printf( "\n%d of %d pairs below the WCAG minimum.\n", $failures, $total );
	exit( 1 );
}

printf( "\nAll %d pairs meet the WCAG 2.1 minimum.\n", $total );
exit( 0 );
