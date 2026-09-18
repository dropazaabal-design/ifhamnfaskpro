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
		'width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"%2$s %3$s',
		$size,
		'' === $args['class'] ? '' : ' class="' . esc_attr( $args['class'] ) . '"',
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
		'instagram' => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1.1" fill="currentColor" stroke="none"/>',
		'tiktok'   => '<path d="M14 4v9.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M14 4c0 2.5 2 4.5 4.5 4.5"/>',
		'snapchat' => '<path d="M12 3.5c2.8 0 4.2 2 4.2 4.6 0 1 .2 2 .2 2s.8-.4 1.3-.2c.6.2.5.9 0 1.3-.6.5-1.6.8-1.6 1.4 0 1 2.2 3 3.4 3.3.4.1.4.6 0 .8-.8.4-1.9.3-2.3.7-.3.3-.2 1-.7 1.1-.7.2-1.7-.4-2.8-.4-1.1 0-2.1.6-2.8.4-.5-.1-.4-.8-.7-1.1-.4-.4-1.5-.3-2.3-.7-.4-.2-.4-.7 0-.8 1.2-.3 3.4-2.3 3.4-3.3 0-.6-1-.9-1.6-1.4-.5-.4-.6-1.1 0-1.3.5-.2 1.3.2 1.3.2s.2-1 .2-2C7.8 5.5 9.2 3.5 12 3.5z"/>',
		'x'        => '<path d="M4 4l16 16"/><path d="M20 4L4 20"/>',
		'youtube'  => '<rect x="2.5" y="6" width="19" height="12" rx="3.5"/><path d="M10.5 9.5l5 2.5-5 2.5z"/>',
		'whatsapp' => '<path d="M21 11.5a8.4 8.4 0 0 1-8.5 8.5 9 9 0 0 1-3.8-.85L4.5 20.5l1.35-4.1A8.4 8.4 0 0 1 4 11.5 8.4 8.4 0 0 1 12.5 3 8.4 8.4 0 0 1 21 11.5z"/>',
		'lock'     => '<path d="M6 11V8a6 6 0 0 1 12 0v3"/><path d="M5 11h14v9H5z"/>',
		'check'    => '<path d="M5 13l4 4L19 7"/>',
		'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
		'chevron'  => '<path d="M6 9l6 6 6-6"/>',
		'back'     => '<path d="M10 6l6 6-6 6"/>',
		'card'     => '<path d="M3 6h18v12H3z"/><path d="M3 10h18"/>',
		'wallet'   => '<path d="M3 8a2 2 0 0 1 2-2h11l3 3v3"/><path d="M3 8v9a2 2 0 0 0 2 2h13a2 2 0 0 0 2-2v-2h-5a2.5 2.5 0 0 1 0-5h5"/><circle cx="16" cy="12.5" r="0.9" fill="currentColor" stroke="none"/>',
		'split'    => '<circle cx="12" cy="12" r="8.5"/><path d="M12 3.5v17"/><path d="M3.5 12h17"/>',
		'arrow'    => '<path d="M19 12H5"/><path d="M11 6l-6 6 6 6"/>',
		'heart'    => '<path d="M12 20s-7-4.4-7-9.2A4.1 4.1 0 0 1 12 8a4.1 4.1 0 0 1 7 2.8C19 15.6 12 20 12 20z"/>',
		'shield'   => '<path d="M12 3l7.5 3v5c0 4.6-3.2 8.2-7.5 10C7.7 19.2 4.5 15.6 4.5 11V6z"/>',
		'star'     => '<path d="M12 3l2.7 5.7 6.3.9-4.5 4.3 1.1 6.1L12 17.2 6.4 20l1.1-6.1L3 9.6l6.3-.9z"/>',
		'filter'   => '<path d="M4 6h16"/><path d="M7 12h10"/><path d="M10 18h4"/>',
		'tag'      => '<path d="M20 4h-7L4 13l7 7 9-9z"/><circle cx="16" cy="8" r="1.4"/>',
		'plus'     => '<path d="M12 5v14M5 12h14"/>',
		'minus'    => '<path d="M5 12h14"/>',
		'zoom'     => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4.3-4.3M8 11h6M11 8v6"/>',
		'info'     => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 8h.01"/>',
		'alert'    => '<path d="M12 3l9.5 17h-19z"/><path d="M12 9.5v5M12 17.5h.01"/>',
		'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7.5V12l3.2 1.9"/>',
		'trash'    => '<path d="M4 7h16"/><path d="M9 7V5h6v2"/><path d="M6 7l1 13h10l1-13"/>',
		'gift'     => '<path d="M3 8h18v4H3z"/><path d="M5 12v8h14v-8"/><path d="M12 8v12"/>',
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

		// ألوان التاجر — فارغة تعني: اتبع اللوحة الجاهزة.
		'matjar_pro_color_primary'           => '',
		'matjar_pro_color_cta'               => '',
		'matjar_pro_color_bg'                => '',
		'matjar_pro_color_accent'            => '',

		// طرق الدفع والضمانات
		'matjar_pro_payment_badges_image'    => 0,
		'matjar_pro_cod_highlight'           => true,
		'matjar_pro_cod_note'                => 'ادفع نقداً للمندوب عند وصول الطلب',
		'matjar_pro_returns_label'           => 'إرجاع مجاني خلال ١٤ يوماً',
		'matjar_pro_secure_label'            => 'دفع آمن ومشفّر',
		'matjar_pro_payment_on_product'      => true,
		'matjar_pro_payment_in_cart'         => true,

		// روابط التواصل في التذييل
		'matjar_pro_social_instagram'        => '',
		'matjar_pro_social_tiktok'           => '',
		'matjar_pro_social_snapchat'         => '',
		'matjar_pro_social_x'                => '',
		'matjar_pro_social_youtube'          => '',

		// الهيرو
		'matjar_pro_hero_enabled'            => true,
		'matjar_pro_hero_layout'             => 'stacked',
		'matjar_pro_hero_image'              => 0,
		'matjar_pro_hero_eyebrow'            => '',
		'matjar_pro_hero_headline'           => '',
		'matjar_pro_hero_subtext'            => '',
		'matjar_pro_hero_cta_label'          => '',
		'matjar_pro_hero_cta_url'            => '',

		// شريط الثقة
		'matjar_pro_trust_enabled'           => true,
		'matjar_pro_trust_1_title'           => 'شحن سريع',
		'matjar_pro_trust_1_subtitle'        => '',
		'matjar_pro_trust_2_title'           => 'دفع عند الاستلام',
		'matjar_pro_trust_2_subtitle'        => '',
		'matjar_pro_trust_3_title'           => 'إرجاع مجاني',
		'matjar_pro_trust_3_subtitle'        => '',
		'matjar_pro_trust_4_title'           => 'دعم واتساب',
		'matjar_pro_trust_4_subtitle'        => '',

		// بلاطتا الترويج
		'matjar_pro_promo_1_image'           => 0,
		'matjar_pro_promo_1_label'           => '',
		'matjar_pro_promo_1_url'             => '',
		'matjar_pro_promo_2_image'           => 0,
		'matjar_pro_promo_2_label'           => '',
		'matjar_pro_promo_2_url'             => '',

		// أشرطة المنتجات في الصفحة الأولى
		'matjar_pro_rail_best_enabled'       => true,
		'matjar_pro_rail_best_title'         => 'الأكثر مبيعاً',
		'matjar_pro_rail_new_enabled'        => true,
		'matjar_pro_rail_new_title'          => 'وصل حديثاً',

		// الدفع
		'matjar_pro_optional_email'          => true,
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
 * شبكات التواصل المدعومة في التذييل.
 *
 * الترتيب ترتيب أهمّيتها لسوق الخليج: إنستغرام وتيك توك وسناب شات هي
 * مصادر الزيارة الفعلية للمتاجر هنا، ثم إكس ويوتيوب.
 *
 * @return array<string,array>
 */
function matjar_pro_social_networks() {
	return array(
		'instagram' => array(
			'label' => __( 'إنستغرام', 'matjar-pro' ),
			'icon'  => 'instagram',
		),
		'tiktok'    => array(
			'label' => __( 'تيك توك', 'matjar-pro' ),
			'icon'  => 'tiktok',
		),
		'snapchat'  => array(
			'label' => __( 'سناب شات', 'matjar-pro' ),
			'icon'  => 'snapchat',
		),
		'x'         => array(
			'label' => __( 'إكس', 'matjar-pro' ),
			'icon'  => 'x',
		),
		'youtube'   => array(
			'label' => __( 'يوتيوب', 'matjar-pro' ),
			'icon'  => 'youtube',
		),
	);
}

/**
 * روابط التواصل التي عبّأها التاجر.
 *
 * @return array<string,array> slug => array( label، icon، url )
 */
function matjar_pro_active_social_links() {
	$out = array();

	foreach ( matjar_pro_social_networks() as $slug => $network ) {
		$url = trim( (string) matjar_pro_mod( 'matjar_pro_social_' . $slug ) );

		if ( '' === $url ) {
			continue;
		}

		$out[ $slug ] = array(
			'label' => $network['label'],
			'icon'  => $network['icon'],
			'url'   => $url,
		);
	}

	return $out;
}

/**
 * يقرأ لوناً مخصّصاً من إعدادات التاجر بعد التحقّق من صيغته.
 *
 * ما لم يكن ستّ خانات ست عشرية يُعَدّ غير مضبوط، فتُستخدم قيمة اللوحة.
 *
 * @param string $key اسم الإعداد.
 * @return string اللون، أو سلسلة فارغة.
 */
function matjar_pro_custom_color( $key ) {
	$value = trim( (string) matjar_pro_mod( $key ) );

	return preg_match( '/^#[0-9a-fA-F]{6}$/', $value ) ? strtoupper( $value ) : '';
}

/**
 * أدوار طرق الدفع.
 *
 * التصنيف ليس تجميلاً: منصّات المنطقة القيادية تعرض خياراتها في هذه
 * المجموعات نفسها — محفظة رقمية، بطاقة بنكية، تقسيط، نقداً عند الاستلام —
 * فالمشتري العربي يقرأ الصفّ بلمحة لأنه رآه قبلاً في كل متجر تعامل معه.
 *
 * ولكل دور لون من رموز اللوحة لا لون علامة تجارية: يظلّ الصفّ متناغماً مع
 * القالب أيّاً كانت اللوحة، ولا يشحن القالب شعارات مملوكة لغيره.
 *
 * @return array<string,array>
 */
function matjar_pro_payment_groups() {
	return array(
		'cash'   => array(
			'label' => __( 'نقداً عند الاستلام', 'matjar-pro' ),
			'icon'  => 'cash',
		),
		'card'   => array(
			'label' => __( 'البطاقات البنكية', 'matjar-pro' ),
			'icon'  => 'card',
		),
		'wallet' => array(
			'label' => __( 'المحافظ الرقمية', 'matjar-pro' ),
			'icon'  => 'wallet',
		),
		'split'  => array(
			'label' => __( 'قسّمها على دفعات', 'matjar-pro' ),
			'icon'  => 'split',
		),
	);
}

/**
 * شارات الثقة وطرق الدفع المتاحة للتاجر.
 *
 * القالب منتج عام: التاجر يُفعّل ما يخصّ سوقه فقط. الافتراضيات المُعلَّمة
 * هنا تناسب العرض التجريبي السعودي، ولا تفرض شيئاً على غيره.
 *
 * الترتيب هو ترتيب العرض: الدفع عند الاستلام أولاً لأنه ما يفتح الثقة
 * للمشتري الذي يشتري من المتجر أول مرة، ثم البطاقات ثم المحافظ ثم التقسيط.
 *
 * @return array<string,array>
 */
function matjar_pro_badge_choices() {
	return array(
		'cod'        => array(
			'label'   => __( 'الدفع عند الاستلام', 'matjar-pro' ),
			'group'   => 'cash',
			'note'    => __( 'ادفع نقداً للمندوب عند وصول الطلب', 'matjar-pro' ),
			'default' => true,
		),
		'mada'       => array(
			'label'   => __( 'مدى', 'matjar-pro' ),
			'group'   => 'card',
			'default' => true,
		),
		'visa'       => array(
			'label'   => __( 'Visa', 'matjar-pro' ),
			'group'   => 'card',
			'default' => true,
		),
		'mastercard' => array(
			'label'   => __( 'Mastercard', 'matjar-pro' ),
			'group'   => 'card',
			'default' => true,
		),
		'knet'       => array(
			'label'   => __( 'KNET', 'matjar-pro' ),
			'group'   => 'card',
			'default' => false,
		),
		'benefit'    => array(
			'label'   => __( 'BENEFIT', 'matjar-pro' ),
			'group'   => 'card',
			'default' => false,
		),
		'applepay'   => array(
			'label'   => __( 'Apple Pay', 'matjar-pro' ),
			'group'   => 'wallet',
			'default' => true,
		),
		'stcpay'     => array(
			'label'   => __( 'STC Pay', 'matjar-pro' ),
			'group'   => 'wallet',
			'default' => false,
		),
		'paypal'     => array(
			'label'   => __( 'PayPal', 'matjar-pro' ),
			'group'   => 'wallet',
			'default' => false,
		),
		'tabby'      => array(
			'label'   => __( 'تابي', 'matjar-pro' ),
			'group'   => 'split',
			'default' => true,
		),
		'tamara'     => array(
			'label'   => __( 'تمارا', 'matjar-pro' ),
			'group'   => 'split',
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

/**
 * طرق الدفع المُفعَّلة مُجمَّعة بأدوارها.
 *
 * تُعيد المجموعات غير الفارغة فقط وبترتيب matjar_pro_payment_groups، فلا
 * يُطبع عنوان مجموعة لا وسيلة تحتها.
 *
 * @return array<string,array> دور => array( label، icon، methods )
 */
function matjar_pro_grouped_payment_methods() {
	$choices = matjar_pro_badge_choices();
	$active  = matjar_pro_active_badges();
	$out     = array();

	foreach ( matjar_pro_payment_groups() as $group => $meta ) {
		$methods = array();

		foreach ( $active as $slug => $label ) {
			if ( ( $choices[ $slug ]['group'] ?? '' ) !== $group ) {
				continue;
			}

			$methods[ $slug ] = array(
				'label' => $label,
				'note'  => $choices[ $slug ]['note'] ?? '',
			);
		}

		if ( $methods ) {
			$out[ $group ] = array(
				'label'   => $meta['label'],
				'icon'    => $meta['icon'],
				'methods' => $methods,
			);
		}
	}

	return $out;
}

/**
 * هل الدفع عند الاستلام مُفعَّل.
 *
 * @return bool
 */
function matjar_pro_has_cod() {
	return array_key_exists( 'cod', matjar_pro_active_badges() );
}
