<?php
/**
 * ميزات تعزيز التحويل: الدعم الفني، نيّة الخروج، إشعارات النشاط.
 *
 * ثلاث قواعد تحكم هذا الملف:
 *
 * ١ — لا شيء يُحمَّل ما لم يُفعّله التاجر. الحزمة cro.js لا تُدرَج إطلاقاً
 *     إن كانت الميزتان مُطفأتين، والقوالب لا تُطبع، فالمتجر الذي لا
 *     يستخدمها لا يدفع بايتاً واحداً ثمنها.
 *
 * ٢ — لا استعلام ولا AJAX. نصوص الإشعارات تُطبع مصفوفةَ JSON في الصفحة
 *     مرّة واحدة، ويدوّرها JavaScript. لا طلب شبكة ولا استعلام قاعدة
 *     بيانات عند التحميل.
 *
 * ٣ — لا مقاطعة داخل قُمع الدفع. زائرٌ يكتب عنوانه لا تُعرض عليه نافذة
 *     خصم ولا إشعار: المقاطعة في تلك اللحظة تُفقد طلباً لا تكسبه.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * هل نحن في موضع يُمنع فيه أي عنصر مقاطِع.
 *
 * @return bool
 */
function matjar_pro_cro_quiet_zone() {
	if ( ! matjar_pro_has_woocommerce() ) {
		return false;
	}

	return matjar_pro_is_funnel() || is_cart() || is_order_received_page();
}

/**
 * نصوص إشعارات النشاط، سطراً بسطر كما كتبها التاجر.
 *
 * @return string[]
 */
function matjar_pro_proof_messages() {
	$raw = (string) matjar_pro_mod( 'matjar_pro_proof_messages' );
	$out = array();

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( wp_strip_all_tags( $line ) );

		if ( '' !== $line ) {
			$out[] = $line;
		}
	}

	// خمسة تكفي للإيهام بالحركة، وأكثر منها يُثقل الصفحة بلا فائدة.
	return array_slice( $out, 0, 5 );
}

/**
 * هل إشعارات النشاط ستُعرض في هذه الصفحة.
 *
 * @return bool
 */
function matjar_pro_proof_active() {
	return (bool) matjar_pro_mod( 'matjar_pro_proof_enabled' )
		&& ! matjar_pro_cro_quiet_zone()
		&& (bool) matjar_pro_proof_messages();
}

/**
 * هل نافذة نيّة الخروج ستُعرض في هذه الصفحة.
 *
 * @return bool
 */
function matjar_pro_exit_intent_active() {
	return (bool) matjar_pro_mod( 'matjar_pro_exit_enabled' )
		&& ! matjar_pro_cro_quiet_zone()
		&& '' !== trim( (string) matjar_pro_mod( 'matjar_pro_exit_title' ) );
}

/**
 * يحمّل حزمة CRO عند الحاجة وحدها.
 */
function matjar_pro_cro_assets() {
	if ( ! matjar_pro_exit_intent_active() && ! matjar_pro_proof_active() ) {
		return;
	}

	$file = '/assets/js/cro.js';

	if ( ! file_exists( MATJAR_PRO_DIR . $file ) ) {
		return;
	}

	wp_enqueue_script(
		'matjar-pro-cro',
		MATJAR_PRO_URI . $file,
		array( 'matjar-pro' ),
		matjar_pro_asset_version( $file ),
		true
	);

	wp_script_add_data( 'matjar-pro-cro', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'matjar_pro_cro_assets', 25 );

/**
 * يطبع قوالب CRO في التذييل.
 */
function matjar_pro_cro_templates() {
	if ( matjar_pro_exit_intent_active() ) {
		get_template_part( 'template-parts/cro/exit-intent' );
	}

	if ( matjar_pro_proof_active() ) {
		get_template_part( 'template-parts/cro/proof' );
	}
}
add_action( 'wp_footer', 'matjar_pro_cro_templates', 20 );

/**
 * يحقن كود منصّة المحادثة الذي لصقه التاجر.
 *
 * يُطبع كما هو لأنه كود تشغيلي: تهذيبه يكسره. وحمايته ليست في التهذيب بل
 * في من يملك حفظه — راجع matjar_pro_sanitize_script أدناه.
 */
function matjar_pro_chat_script() {
	$code = (string) get_theme_mod( 'matjar_pro_chat_script', '' );

	if ( '' === trim( $code ) ) {
		return;
	}

	echo "\n<!-- matjar-pro: chat -->\n";
	echo $code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo "\n<!-- /matjar-pro: chat -->\n";
}
add_action( 'wp_footer', 'matjar_pro_chat_script', 99 );
