<?php
/**
 * تكامل ووكومرس — الطبقة الهيكلية.
 *
 * هذا الملف يُحمَّل فقط عند تفعيل ووكومرس. تخصيص رحلة العميل (السلة
 * والدفع) يأتي في المرحلة الرابعة؛ ما هنا هو الهيكل الذي تُبنى عليه.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * يستبدل أغلفة ووكومرس الافتراضية بأغلفة القالب.
 */
function matjar_pro_replace_woocommerce_wrappers() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

	add_action( 'woocommerce_before_main_content', 'matjar_pro_wrapper_start', 10 );
	add_action( 'woocommerce_after_main_content', 'matjar_pro_wrapper_end', 10 );

	// لا شريط جانبي افتراضي: صفحة الأقسام تستخدم لوح فلترة على الجوال.
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

	// عنوان الصفحة يُطبع من قوالب القالب لا من ووكومرس.
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}
add_action( 'init', 'matjar_pro_replace_woocommerce_wrappers' );

/**
 * أصناف غلاف المحتوى.
 *
 * صفحة المنتج تبدأ أقرب إلى الرأس لأن الصورة هي أول ما يجب أن يُرى؛
 * بقيّة الصفحات تأخذ هامشاً علوياً كاملاً.
 *
 * @return string
 */
function matjar_pro_main_class() {
	$classes = 'mp-main mx-auto max-w-screen-xl px-3 py-5 lg:px-4 lg:py-8';

	if ( function_exists( 'is_product' ) && is_product() ) {
		$classes = 'mp-main mx-auto max-w-screen-xl px-3 pb-8 pt-3 lg:px-4 lg:pt-6';
	}

	return (string) apply_filters( 'matjar_pro_main_class', $classes );
}

/**
 * فتح غلاف المحتوى.
 *
 * هذا هو معلَم main الوحيد في صفحات ووكومرس، وهدف رابط «تخطَّ إلى
 * المحتوى» في الرأس. القوالب تُطلق woocommerce_before_main_content ولا
 * تفتح div بديلاً، وإلّا بقي المعلَم غائباً والرابط يشير إلى لا شيء.
 */
function matjar_pro_wrapper_start() {
	echo '<main id="mp-main" class="' . esc_attr( matjar_pro_main_class() ) . '">';
}

/**
 * إغلاق غلاف المحتوى.
 */
function matjar_pro_wrapper_end() {
	echo '</main>';
}

/**
 * عدد أعمدة شبكة المنتجات على الشاشات الكبيرة.
 *
 * الجوال يعرض عمودين عبر CSS، وهذه القيمة تحكم الشاشات الواسعة فقط.
 *
 * @return int
 */
function matjar_pro_loop_columns() {
	return 4;
}
add_filter( 'loop_shop_columns', 'matjar_pro_loop_columns' );

/**
 * عدد المنتجات في الصفحة.
 *
 * @return int
 */
function matjar_pro_products_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'matjar_pro_products_per_page' );

/**
 * إعدادات مسار التنقل: فاصل خفيف وبلا أغلفة ثقيلة.
 *
 * @param array $defaults الإعدادات الافتراضية.
 * @return array
 */
function matjar_pro_breadcrumb_defaults( $defaults ) {
	$defaults['delimiter']   = '';
	$defaults['wrap_before'] = '<nav class="mp-breadcrumb" aria-label="' . esc_attr__( 'مسار التنقل', 'matjar-pro' ) . '">';
	$defaults['wrap_after']  = '</nav>';
	$defaults['before']      = '<span class="mp-breadcrumb__item">';
	$defaults['after']       = '</span>';

	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'matjar_pro_breadcrumb_defaults' );

/**
 * يُبلّغ ووكومرس أن القالب يتولّى تحديث السلة بنفسه عبر Store API.
 *
 * @param array $fragments أجزاء السلة.
 * @return array
 */
function matjar_pro_cart_count_fragment( $fragments ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $fragments;
	}

	$fragments['.mp-cart-count'] = sprintf(
		'<span class="mp-cart-count" data-count="%1$d">%2$s</span>',
		(int) WC()->cart->get_cart_contents_count(),
		esc_html( number_format_i18n( WC()->cart->get_cart_contents_count() ) )
	);

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'matjar_pro_cart_count_fragment' );

/**
 * سطر التقسيط لمنتج معيّن.
 *
 * يُستدعى من قوالب المرحلة الثالثة. يعيد سلسلة فارغة إذا كان الإعداد مُطفأً
 * أو السعر غير قابل للقسمة، فلا يُطبع سطر ناقص.
 *
 * @param WC_Product|null $product المنتج، أو المنتج الحالي عند الإغفال.
 * @return string
 */
function matjar_pro_installment_line( $product = null ) {
	if ( ! matjar_pro_mod( 'matjar_pro_bnpl_enabled' ) ) {
		return '';
	}

	$product = $product ?: ( function_exists( 'wc_get_product' ) ? wc_get_product() : null );

	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$price = wc_get_price_to_display( $product );
	$parts = (int) matjar_pro_mod( 'matjar_pro_bnpl_parts' );
	$each  = matjar_pro_installment_price( $price, $parts );

	if ( '' === $each ) {
		return '';
	}

	/* translators: 1: عدد الدفعات، 2: قيمة الدفعة منسّقة بعملة المتجر. */
	return sprintf(
		esc_html__( 'أو %1$s دفعات بدون فوائد بقيمة %2$s', 'matjar-pro' ),
		number_format_i18n( $parts ),
		wp_kses_post( $each )
	);
}

/**
 * نطاق موعد التوصيل المتوقع بتاريخين فعليين.
 *
 * التاريخ الملموس يزيل أكبر سبب تردد قبل الشراء، بخلاف «3 إلى 5 أيام عمل».
 *
 * @return string
 */
function matjar_pro_delivery_estimate() {
	$min = (int) matjar_pro_mod( 'matjar_pro_delivery_min_days' );
	$max = (int) matjar_pro_mod( 'matjar_pro_delivery_max_days' );

	if ( $max < $min ) {
		$max = $min;
	}

	if ( $max <= 0 ) {
		return '';
	}

	$format = _x( 'j F', 'صيغة تاريخ موعد التوصيل', 'matjar-pro' );
	$from   = wp_date( $format, strtotime( "+{$min} days", current_time( 'timestamp' ) ) );
	$to     = wp_date( $format, strtotime( "+{$max} days", current_time( 'timestamp' ) ) );

	if ( $from === $to ) {
		/* translators: %s: تاريخ. */
		return sprintf( esc_html__( 'يوصلك في %s', 'matjar-pro' ), $from );
	}

	/* translators: 1: تاريخ البداية، 2: تاريخ النهاية. */
	return sprintf( esc_html__( 'يوصلك بين %1$s و%2$s', 'matjar-pro' ), $from, $to );
}
