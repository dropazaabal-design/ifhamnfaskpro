<?php
/**
 * دوال مساعدة عامة: العملة، التباين، الأيقونات.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * هل ووكومرس مُفعَّل؟
 *
 * @return bool
 */
function matjar_pro_has_woocommerce() {
	return class_exists( 'WooCommerce' );
}

/**
 * يحوّل لوناً سِتّ عشرياً إلى قنوات RGB مفصولة بمسافات، بالصيغة التي تفهمها
 * دالة rgb() الحديثة: «194 65 12». هذه الصيغة هي ما يسمح بعمل مُعدِّلات
 * الشفافية في Tailwind (مثل bg-cta/50) فوق متغيّرات CSS.
 *
 * @param string $hex لون بصيغة #RGB أو #RRGGBB.
 * @return string
 */
function matjar_pro_hex_to_channels( $hex ) {
	$rgb = matjar_pro_hex_to_rgb( $hex );

	return implode( ' ', $rgb );
}

/**
 * يحوّل لوناً سِتّ عشرياً إلى مصفوفة [r, g, b].
 *
 * @param string $hex لون بصيغة #RGB أو #RRGGBB.
 * @return int[]
 */
function matjar_pro_hex_to_rgb( $hex ) {
	$hex = ltrim( (string) $hex, '#' );

	if ( 3 === strlen( $hex ) ) {
		$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
	}

	if ( 6 !== strlen( $hex ) || ! ctype_xdigit( $hex ) ) {
		return array( 0, 0, 0 );
	}

	return array(
		(int) hexdec( substr( $hex, 0, 2 ) ),
		(int) hexdec( substr( $hex, 2, 2 ) ),
		(int) hexdec( substr( $hex, 4, 2 ) ),
	);
}

/**
 * يحسب الإضاءة النسبية للون حسب معادلة WCAG 2.1.
 *
 * @param string $hex لون سِتّ عشري.
 * @return float
 */
function matjar_pro_relative_luminance( $hex ) {
	$channels = matjar_pro_hex_to_rgb( $hex );
	$linear   = array();

	foreach ( $channels as $channel ) {
		$value    = $channel / 255;
		$linear[] = $value <= 0.04045 ? $value / 12.92 : pow( ( $value + 0.055 ) / 1.055, 2.4 );
	}

	return ( 0.2126 * $linear[0] ) + ( 0.7152 * $linear[1] ) + ( 0.0722 * $linear[2] );
}

/**
 * يحسب نسبة التباين بين لونين (من 1 إلى 21) حسب WCAG 2.1.
 *
 * @param string $foreground لون النص.
 * @param string $background لون الخلفية.
 * @return float
 */
function matjar_pro_contrast_ratio( $foreground, $background ) {
	$one = matjar_pro_relative_luminance( $foreground );
	$two = matjar_pro_relative_luminance( $background );

	$lighter = max( $one, $two );
	$darker  = min( $one, $two );

	return round( ( $lighter + 0.05 ) / ( $darker + 0.05 ), 2 );
}

/**
 * يختار لون النص الأعلى تبايناً فوق خلفية معيّنة.
 *
 * هذا ما يضمن أن أي لون يختاره التاجر لزر الشراء يبقى مقروءاً: لا يمكن أن
 * ينتهي القالب بزر برتقالي فاتح بنص أبيض عند 2.8:1.
 *
 * @param string $background لون الخلفية.
 * @param string $light      مرشّح النص الفاتح.
 * @param string $dark       مرشّح النص الغامق.
 * @return string
 */
function matjar_pro_readable_foreground( $background, $light = '#FFFFFF', $dark = '#0F1E33' ) {
	$on_light = matjar_pro_contrast_ratio( $light, $background );
	$on_dark  = matjar_pro_contrast_ratio( $dark, $background );

	return $on_light >= $on_dark ? $light : $dark;
}

/**
 * يعيد رمز عملة المتجر كما ضبطها التاجر في ووكومرس.
 *
 * لا يوجد في هذا القالب أي عملة مثبّتة في الكود.
 *
 * @return string
 */
function matjar_pro_currency_symbol() {
	if ( ! function_exists( 'get_woocommerce_currency_symbol' ) ) {
		return '';
	}

	return get_woocommerce_currency_symbol();
}

/**
 * يعيد قيمة القسط الواحد منسّقة بعملة المتجر.
 *
 * يمرّ الرقم عبر wc_price فيحترم العملة وموضع رمزها وفواصل الآلاف
 * وعدد الخانات العشرية كما ضبطها التاجر.
 *
 * @param float|string $price السعر الكامل.
 * @param int          $parts عدد الدفعات.
 * @return string سلسلة HTML جاهزة للطباعة، أو سلسلة فارغة إذا تعذّر الحساب.
 */
function matjar_pro_installment_price( $price, $parts = 4 ) {
	if ( ! function_exists( 'wc_price' ) || ! is_numeric( $price ) ) {
		return '';
	}

	$parts = max( 2, (int) $parts );
	$price = (float) $price;

	if ( $price <= 0 ) {
		return '';
	}

	return wc_price( $price / $parts );
}

/**
 * يطبع أيقونة SVG مضمّنة من مجموعة القالب.
 *
 * الأيقونات مضمّنة في PHP لا في ملف خارجي، فلا طلب شبكة إضافي ولا وميض،
 * وتأخذ لونها من currentColor.
 *
 * @param string $name  اسم الأيقونة.
 * @param array  $args  size: المقاس بالبكسل، class: أصناف إضافية، label: نص بديل لقارئ الشاشة.
 * @return string
 */
function matjar_pro_get_icon( $name, $args = array() ) {
	$paths = matjar_pro_icon_paths();

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	$args = wp_parse_args(
		$args,
		array(
			'size'  => 24,
			'class' => '',
			'label' => '',
		)
	);

	$size  = (int) $args['size'];
	$label = (string) $args['label'];

	$attributes = sprintf(
		'width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="%2$s" %3$s',
		$size,
		esc_attr( $args['class'] ),
		'' === $label ? 'aria-hidden="true" focusable="false"' : 'role="img" aria-label="' . esc_attr( $label ) . '"'
	);

	return '<svg ' . $attributes . '>' . $paths[ $name ] . '</svg>';
}

/**
 * مسارات الأيقونات. تُوسَّع في المراحل التالية.
 *
 * @return array<string,string>
 */
function matjar_pro_icon_paths() {
	return array(
		'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'search'   => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4.3-4.3"/>',
		'cart'     => '<circle cx="9" cy="20" r="1.4"/><circle cx="17" cy="20" r="1.4"/><path d="M3 4h2l2.4 11.2a1 1 0 0 0 1 .8h8.9a1 1 0 0 0 1-.8L20 8H6"/>',
		'user'     => '<circle cx="12" cy="8" r="3.4"/><path d="M5 20c0-3.5 3.1-6 7-6s7 2.5 7 6"/>',
		'home'     => '<path d="M4 11l8-6.5 8 6.5"/><path d="M6 10.5V20h12v-9.5"/>',
		'grid'     => '<path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/>',
		'truck'    => '<path d="M3 7h11v9H3z"/><path d="M14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/>',
		'cash'     => '<path d="M3 7h18v10H3z"/><circle cx="12" cy="12" r="2.4"/>',
		'return'   => '<path d="M3.5 12a8.5 8.5 0 1 0 2.8-6.3"/><path d="M3 4.5V10h5.5"/>',
		'whatsapp' => '<path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.5 9 9 0 0 1-3.8-.85L4.5 20.5l1.35-4.1A8.4 8.4 0 0 1 4 11.5 8.4 8.4 0 0 1 12.5 3 8.4 8.4 0 0 1 21 11.5z"/>',
		'lock'     => '<path d="M6 11V8a6 6 0 0 1 12 0v3"/><path d="M5 11h14v9H5z"/>',
		'check'    => '<path d="M5 13l4 4L19 7"/>',
		'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
		'chevron'  => '<path d="M6 9l6 6 6-6"/>',
		'back'     => '<path d="M10 6l6 6-6 6"/>',
		'card'     => '<path d="M3 6h18v12H3z"/><path d="M3 10h18"/>',
	);
}

/**
 * القيم الافتراضية لكل إعدادات القالب، في مكان واحد.
 *
 * مصدر واحد للافتراضيات يمنع انحرافها بين لوحة التحكم والقوالب — وهو أشهر
 * سبب لاختلاف المعاينة عن الموقع الفعلي.
 *
 * @return array<string,mixed>
 */
function matjar_pro_defaults() {
	return array(
		'matjar_pro_palette'                 => 'trust',
		'matjar_pro_cta_style'               => 'burnt',
		'matjar_pro_cta_custom'              => '',
		'matjar_pro_font'                    => 'cairo',
		'matjar_pro_announcement_enabled'    => true,
		'matjar_pro_announcement_text'       => '',
		'matjar_pro_whatsapp'                => '',
		'matjar_pro_cr_number'               => '',
		'matjar_pro_vat_number'              => '',
		'matjar_pro_free_shipping_threshold' => 0,
		'matjar_pro_bnpl_enabled'            => true,
		'matjar_pro_bnpl_parts'              => 4,
		'matjar_pro_low_stock_threshold'     => 5,
		'matjar_pro_delivery_min_days'       => 2,
		'matjar_pro_delivery_max_days'       => 4,
		'matjar_pro_sticky_buy_bar'          => true,
		'matjar_pro_bottom_nav'              => true,
	);
}

/**
 * يقرأ إعداداً من الـ Customizer مع افتراضيه المركزي.
 *
 * @param string $key اسم الإعداد.
 * @return mixed
 */
function matjar_pro_mod( $key ) {
	$defaults = matjar_pro_defaults();
	$default  = $defaults[ $key ] ?? '';

	return get_theme_mod( $key, $default );
}

/**
 * شارات الثقة وطرق الدفع المتاحة للتاجر.
 *
 * القالب منتج عام: التاجر يُفعّل ما يخصّ سوقه فقط. الافتراضيات المُعلَّمة
 * هنا تناسب العرض التجريبي السعودي، ولا تفرض شيئاً على غيره.
 *
 * @return array<string,array>
 */
function matjar_pro_badge_choices() {
	return array(
		'applepay'   => array(
			'label'   => __( 'Apple Pay', 'matjar-pro' ),
			'default' => true,
		),
		'mada'       => array(
			'label'   => __( 'مدى', 'matjar-pro' ),
			'default' => true,
		),
		'visa'       => array(
			'label'   => __( 'Visa', 'matjar-pro' ),
			'default' => true,
		),
		'mastercard' => array(
			'label'   => __( 'Mastercard', 'matjar-pro' ),
			'default' => true,
		),
		'tabby'      => array(
			'label'   => __( 'تابي', 'matjar-pro' ),
			'default' => true,
		),
		'tamara'     => array(
			'label'   => __( 'تمارا', 'matjar-pro' ),
			'default' => true,
		),
		'stcpay'     => array(
			'label'   => __( 'STC Pay', 'matjar-pro' ),
			'default' => false,
		),
		'knet'       => array(
			'label'   => __( 'KNET', 'matjar-pro' ),
			'default' => false,
		),
		'benefit'    => array(
			'label'   => __( 'BENEFIT', 'matjar-pro' ),
			'default' => false,
		),
		'paypal'     => array(
			'label'   => __( 'PayPal', 'matjar-pro' ),
			'default' => false,
		),
		'cod'        => array(
			'label'   => __( 'الدفع عند الاستلام', 'matjar-pro' ),
			'default' => true,
		),
	);
}

/**
 * الشارات المُفعَّلة حالياً.
 *
 * @return array<string,string> slug => label
 */
function matjar_pro_active_badges() {
	$active = array();

	foreach ( matjar_pro_badge_choices() as $slug => $badge ) {
		if ( get_theme_mod( 'matjar_pro_badge_' . $slug, $badge['default'] ) ) {
			$active[ $slug ] = $badge['label'];
		}
	}

	return $active;
}
