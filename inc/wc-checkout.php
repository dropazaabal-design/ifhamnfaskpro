<?php
/**
 * السلة والدفع — هندسة إزالة الاحتكاك.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * هل نحن داخل قُمع الدفع؟
 *
 * صفحة الشكر ليست داخله: الطلب تمّ، ونُعيد الهيدر الكامل لتشجيع التصفّح.
 *
 * @return bool
 */
function matjar_pro_is_funnel() {
	if ( ! function_exists( 'is_checkout' ) ) {
		return false;
	}

	return is_checkout() && ! is_order_received_page();
}

/**
 * يعيد ترتيب حقول الدفع وتسمياتها.
 *
 * الترتيب سلوكي لا إداري:
 *   الجوال (الهوية الفعلية في السوق) ← الاسم ← المدينة ← الحي ← الشارع
 *   ← البريد اختياري ← ملاحظة المندوب.
 *
 * حقل اسم واحد لا حقلان: الأسماء العربية لا تتجزّأ إلى «الأول» و«الأخير»
 * بشكل طبيعي، وحقلان يضاعفان الاحتكاك مقابل لا شيء.
 *
 * @param array $fields حقول الدفع.
 * @return array
 */
function matjar_pro_checkout_fields( $fields ) {
	if ( ! isset( $fields['billing'] ) ) {
		return $fields;
	}

	$billing = &$fields['billing'];

	// الجوال أولاً، بلوحة أرقام واتجاه لاتيني داخل النص العربي.
	if ( isset( $billing['billing_phone'] ) ) {
		$billing['billing_phone']['priority']          = 10;
		$billing['billing_phone']['required']          = true;
		$billing['billing_phone']['label']             = __( 'رقم الجوال', 'matjar-pro' );
		$billing['billing_phone']['description']       = __( 'هويتك في المتجر ورقم تتبّع الطلب.', 'matjar-pro' );
		$billing['billing_phone']['custom_attributes'] = array(
			'inputmode' => 'numeric',
			'dir'       => 'ltr',
		);
	}

	// حقل اسم واحد.
	if ( isset( $billing['billing_first_name'] ) ) {
		$billing['billing_first_name']['label']    = __( 'الاسم الكامل', 'matjar-pro' );
		$billing['billing_first_name']['priority'] = 20;
		$billing['billing_first_name']['class']    = array( 'form-row-wide' );
	}

	unset( $billing['billing_last_name'], $billing['billing_company'] );

	if ( isset( $billing['billing_country'] ) ) {
		$billing['billing_country']['priority'] = 30;
	}

	if ( isset( $billing['billing_state'] ) ) {
		$billing['billing_state']['priority'] = 35;
		$billing['billing_state']['class']    = array( 'form-row-wide' );
	}

	if ( isset( $billing['billing_city'] ) ) {
		$billing['billing_city']['label']    = __( 'المدينة', 'matjar-pro' );
		$billing['billing_city']['priority'] = 40;
		$billing['billing_city']['class']    = array( 'form-row-wide' );
	}

	// الحي مطلوب: بدونه لا يصل المندوب، وهو أشهر سبب لفشل التوصيل.
	if ( isset( $billing['billing_address_2'] ) ) {
		$billing['billing_address_2']['label']       = __( 'الحي / المنطقة', 'matjar-pro' );
		$billing['billing_address_2']['placeholder'] = '';
		$billing['billing_address_2']['required']    = true;
		$billing['billing_address_2']['priority']    = 50;
		$billing['billing_address_2']['class']       = array( 'form-row-wide' );
		$billing['billing_address_2']['label_class'] = array();
	}

	if ( isset( $billing['billing_address_1'] ) ) {
		$billing['billing_address_1']['label']       = __( 'الشارع ووصف إضافي', 'matjar-pro' );
		$billing['billing_address_1']['placeholder'] = '';
		$billing['billing_address_1']['priority']    = 60;
		$billing['billing_address_1']['class']       = array( 'form-row-wide' );
	}

	if ( isset( $billing['billing_email'] ) ) {
		$billing['billing_email']['priority']          = 70;
		$billing['billing_email']['class']             = array( 'form-row-wide' );
		$billing['billing_email']['custom_attributes'] = array( 'dir' => 'ltr' );

		if ( matjar_pro_mod( 'matjar_pro_optional_email' ) ) {
			$billing['billing_email']['required'] = false;
			$billing['billing_email']['label']    = __( 'البريد الإلكتروني', 'matjar-pro' );
		}
	}

	// الرمز البريدي غير مستخدم في أسواق عدة: يُخفى إن لم يكن مطلوباً.
	if ( isset( $billing['billing_postcode'] ) && empty( $billing['billing_postcode']['required'] ) ) {
		unset( $billing['billing_postcode'] );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'matjar_pro_checkout_fields', 20 );

/**
 * يوسم الحقل الاختياري بكلمة صريحة.
 *
 * ووكومرس يوسم المطلوب بنجمة ويترك الاختياري بلا علامة، فيظنّ الزائر أن
 * كل حقل مطلوب. الوسم الصريح على البريد هو ما يجعله يتخطّاه فعلاً.
 *
 * @param string $field الحقل المبني.
 * @param string $key   مفتاح الحقل.
 * @param array  $args  وسائط الحقل.
 * @return string
 */
function matjar_pro_mark_optional( $field, $key, $args ) {
	if ( ! empty( $args['required'] ) || ! is_string( $field ) ) {
		return $field;
	}

	if ( ! in_array( $key, array( 'billing_email', 'order_comments' ), true ) ) {
		return $field;
	}

	return str_replace(
		'</label>',
		' <span class="mp-optional">' . esc_html__( '(اختياري)', 'matjar-pro' ) . '</span></label>',
		$field
	);
}
add_filter( 'woocommerce_form_field', 'matjar_pro_mark_optional', 20, 3 );

/**
 * ملاحظة الطلب تصبح ملاحظة للمندوب.
 *
 * @param array $fields حقول الطلب.
 * @return array
 */
function matjar_pro_order_fields( $fields ) {
	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = __( 'ملاحظة للمندوب', 'matjar-pro' );
		$fields['order']['order_comments']['placeholder'] = __( 'مثال: اتصل قبل الوصول', 'matjar-pro' );
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'matjar_pro_order_fields', 30 );

/**
 * نسبة تقدّم الشحن المجاني.
 *
 * @return array{threshold: float, total: float, remaining: float, percent: int}|null
 */
function matjar_pro_free_shipping_progress() {
	$threshold = (float) matjar_pro_mod( 'matjar_pro_free_shipping_threshold' );

	if ( $threshold <= 0 || ! function_exists( 'WC' ) || ! WC()->cart ) {
		return null;
	}

	$total = (float) WC()->cart->get_displayed_subtotal();

	return array(
		'threshold' => $threshold,
		'total'     => $total,
		'remaining' => max( 0, $threshold - $total ),
		'percent'   => (int) min( 100, round( ( $total / $threshold ) * 100 ) ),
	);
}

/**
 * يضيف عنوان نقطة نهاية أجزاء السلة إلى بيانات JavaScript.
 *
 * بعد الإضافة عبر Store API نطلب أجزاء ووكومرس الجاهزة بدل بناء HTML في
 * المتصفح: الترجمة والعملة والأسعار كلها من الخادم، فلا تتكرّر المنطق ولا
 * تختلف النتيجة.
 *
 * @param array $data البيانات.
 * @return array
 */
function matjar_pro_checkout_script_data( $data ) {
	if ( class_exists( 'WC_AJAX' ) ) {
		$data['fragmentsUrl'] = esc_url_raw( WC_AJAX::get_endpoint( 'get_refreshed_fragments' ) );
	}

	$data['strings']['cartEmpty'] = __( 'سلتك فارغة', 'matjar-pro' );
	$data['strings']['required']  = __( 'هذا الحقل مطلوب', 'matjar-pro' );
	$data['strings']['badPhone']  = __( 'تحقّق من رقم الجوال', 'matjar-pro' );
	$data['strings']['badEmail']  = __( 'تحقّق من البريد الإلكتروني', 'matjar-pro' );

	return $data;
}
add_filter( 'matjar_pro_script_data', 'matjar_pro_checkout_script_data' );

/**
 * يحمّل حزمة الدفع في صفحتي السلة والدفع وحدهما.
 */
function matjar_pro_enqueue_checkout_assets() {
	if ( ! function_exists( 'is_checkout' ) || ! ( is_checkout() || is_cart() ) ) {
		return;
	}

	$file = '/assets/js/checkout.js';

	if ( ! file_exists( MATJAR_PRO_DIR . $file ) ) {
		return;
	}

	wp_enqueue_script(
		'matjar-pro-checkout',
		MATJAR_PRO_URI . $file,
		array( 'matjar-pro' ),
		matjar_pro_asset_version( $file ),
		true
	);

	wp_script_add_data( 'matjar-pro-checkout', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'matjar_pro_enqueue_checkout_assets', 20 );

/**
 * يضع مبلغ الطلب على زر إتمام الدفع.
 *
 * «أكمل الطلب • 358» يلغي سؤال «كم سيُخصم منّي؟» في اللحظة الأخيرة.
 *
 * النص يُبنى في الخادم داخل جزء الدفع الذي يُعيد ووكومرس بناءه عند تغيّر
 * الشحن أو طريقة الدفع، فيبقى المبلغ مطابقاً بلا سطر JavaScript واحد.
 *
 * @param string $text النص الافتراضي.
 * @return string
 */
function matjar_pro_order_button_text( $text ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $text;
	}

	$total = wp_strip_all_tags( WC()->cart->get_total() );

	if ( '' === $total ) {
		return $text;
	}

	/* translators: %s: إجمالي الطلب منسّقاً بعملة المتجر. */
	return sprintf( __( 'أكمل الطلب • %s', 'matjar-pro' ), $total );
}
add_filter( 'woocommerce_order_button_text', 'matjar_pro_order_button_text' );

/**
 * يُخفي شريط التنقل السفلي داخل قُمع الدفع.
 *
 * كل عنصر تنقّل في صفحة الدفع هو مخرج تسرّب.
 *
 * @param array $classes أصناف body.
 * @return array
 */
function matjar_pro_funnel_body_class( $classes ) {
	if ( matjar_pro_is_funnel() ) {
		$classes = array_diff( $classes, array( 'mp-has-bottom-nav' ) );
		$classes[] = 'mp-checkout-page';
	}

	return $classes;
}
add_filter( 'body_class', 'matjar_pro_funnel_body_class', 20 );

/**
 * يزيل روابط «العودة للمتجر» المكرّرة من إشعارات السلة.
 *
 * @return string
 */
function matjar_pro_continue_shopping_url() {
	return wc_get_page_permalink( 'shop' );
}
add_filter( 'woocommerce_continue_shopping_redirect', 'matjar_pro_continue_shopping_url' );

/**
 * يشرح «الدفع عند الاستلام» في قائمة بوابات الدفع.
 *
 * ووكومرس يعرض عنوان البوابة وحده، فيقرأ الزائر «الدفع عند الاستلام» ولا
 * يعرف إن كان سيدفع للمندوب أم يُحوِّل لاحقاً. السطر يُضاف فقط إن لم يكتب
 * التاجر وصفاً بنفسه: إعداده في ووكومرس أحقّ من إعداد القالب.
 *
 * @param string $description وصف البوابة.
 * @param string $gateway_id  معرّف البوابة.
 * @return string
 */
function matjar_pro_cod_description( $description, $gateway_id ) {
	if ( 'cod' !== $gateway_id || '' !== trim( wp_strip_all_tags( (string) $description ) ) ) {
		return $description;
	}

	$note = (string) matjar_pro_mod( 'matjar_pro_cod_note' );

	if ( '' === $note ) {
		return $description;
	}

	return '<span class="mp-cod-note">' . esc_html( $note ) . '</span>';
}
add_filter( 'woocommerce_gateway_description', 'matjar_pro_cod_description', 10, 2 );

/**
 * طرق الدفع في صفحة السلة، فوق زر الإتمام.
 *
 * الموضع نفسه الذي تشغله في لوح السلة، فلا يختلف ترتيب ما يراه الزائر بين
 * اللوح والصفحة.
 */
function matjar_pro_cart_payment_row() {
	if ( ! matjar_pro_mod( 'matjar_pro_payment_in_cart' ) ) {
		return;
	}

	get_template_part(
		'template-parts/payment/methods',
		null,
		array( 'variant' => 'row' )
	);
}
add_action( 'woocommerce_proceed_to_checkout', 'matjar_pro_cart_payment_row', 5 );
