<?php
/**
 * ميزات تعزيز التحويل: الدعم الفني، نيّة الخروج، إشعارات النشاط.
 *
 * أربع قواعد تحكم هذا الملف:
 *
 * ١ — لا شيء يُحمَّل ما لم يُفعّله التاجر. الحزمة cro.js لا تُدرَج إطلاقاً
 *     إن كانت الميزتان مُطفأتين، والقوالب لا تُطبع، فالمتجر الذي لا
 *     يستخدمها لا يدفع بايتاً واحداً ثمنها.
 *
 * ٢ — لا AJAX ولا استعلام على كل تحميل. الإشعارات تُطبع مصفوفةَ JSON في
 *     الصفحة مرّة واحدة ويدوّرها JavaScript. وحين تُفعَّل المبيعات
 *     الحقيقية يُستعلَم عنها مرّة كل عشر دقائق ويُخزَّن الناتج، فتقرأ
 *     الصفحة صفّاً واحداً لا استعلام طلبات.
 *
 * ٣ — لا مقاطعة داخل قُمع الدفع. زائرٌ يكتب عنوانه لا تُعرض عليه نافذة
 *     خصم ولا إشعار: المقاطعة في تلك اللحظة تُفقد طلباً لا تكسبه.
 *
 * ٤ — لا إشعار كاذب. الإشعار الذي يدّعي شراءً لم يقع إعلانٌ مضلّل يعرّض
 *     التاجر للمساءلة، ويُفقده ثقة المشتري إن انكشف — ولن يعجز مشترٍ
 *     واحد عن كشفه: يكفي أن يفتح الصفحة مرّتين فيرى «نفس الشراء» يتكرّر.
 *     لذلك مصدر الشراء هنا هو طلبات المتجر نفسها، وحقل التاجر النصّي
 *     لا يلبس ثوب الشراء إطلاقاً مهما كتب فيه.
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
 * مشتريات حقيقية من طلبات المتجر.
 *
 * تُقرأ من ووكومرس لا من حقل نصّي: هذا هو الفرق بين دليلٍ اجتماعي ودعاية
 * كاذبة. وإن لم يكن في المتجر بيعٌ خلال المدّة، لا يُعرض شيء — الصمت
 * أصدق من اختلاق حركة لا وجود لها.
 *
 * الخصوصية: لا اسم مشترٍ ولا عنوان ولا رقم طلب. اسم المنتج علنيٌّ أصلاً
 * في صفحته، ووقتٌ تقريبيّ لا يدلّ على شخص.
 *
 * الكلفة: استعلام واحد كل عشر دقائق، لا استعلام على كل تحميل.
 *
 * @return array<int,array{text: string, kind: string}>
 */
function matjar_pro_proof_orders() {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$cache = array();

	if ( ! matjar_pro_has_woocommerce() || ! function_exists( 'wc_get_orders' ) ) {
		return $cache;
	}

	$rows = get_transient( 'matjar_pro_proof_orders' );

	if ( false === $rows ) {
		$rows = array();

		/**
		 * تصفية المدّة التي تُقرأ منها المبيعات (بالأيام).
		 *
		 * @param int $days الأيام.
		 */
		$days = max( 1, (int) apply_filters( 'matjar_pro_proof_window_days', 7 ) );

		$orders = wc_get_orders(
			array(
				'limit'        => 12,
				'status'       => array( 'processing', 'completed' ),
				'date_created' => '>' . ( time() - ( $days * DAY_IN_SECONDS ) ),
				'orderby'      => 'date',
				'order'        => 'DESC',
				'return'       => 'objects',
			)
		);

		foreach ( (array) $orders as $order ) {
			if ( ! $order instanceof WC_Order ) {
				continue;
			}

			$created = $order->get_date_created();

			foreach ( $order->get_items() as $item ) {
				$name = trim( wp_strip_all_tags( (string) $item->get_name() ) );

				if ( '' === $name || ! $created ) {
					continue;
				}

				$rows[] = array(
					'name' => $name,
					'time' => (int) $created->getTimestamp(),
				);

				break;
			}

			if ( count( $rows ) >= 5 ) {
				break;
			}
		}

		set_transient( 'matjar_pro_proof_orders', $rows, 10 * MINUTE_IN_SECONDS );
	}

	foreach ( (array) $rows as $row ) {
		if ( empty( $row['name'] ) || empty( $row['time'] ) ) {
			continue;
		}

		$cache[] = array(
			'kind' => 'order',
			'text' => sprintf(
				/* translators: 1: اسم المنتج. 2: مدّة مثل «١٢ دقيقة». */
				__( 'تم شراء %1$s قبل %2$s', 'matjar-pro' ),
				$row['name'],
				human_time_diff( (int) $row['time'], time() )
			),
		);
	}

	return $cache;
}

/**
 * حقائق المتجر التي كتبها التاجر.
 *
 * هذه ليست إشعارات شراء ولا تُعرض بمظهرها: تأخذ أيقونة معلومة لا أيقونة
 * سلّة، ويُعلَّم صنفها في الـ JSON. فحتى لو كتب التاجر جملةً تدّعي شراءً،
 * لا تخرج للزائر في هيئة إشعار شراء.
 *
 * @return array<int,array{text: string, kind: string}>
 */
function matjar_pro_proof_facts() {
	$raw = (string) matjar_pro_mod( 'matjar_pro_proof_messages' );
	$out = array();

	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( wp_strip_all_tags( $line ) );

		if ( '' !== $line ) {
			$out[] = array(
				'kind' => 'fact',
				'text' => $line,
			);
		}
	}

	return $out;
}

/**
 * الإشعارات كلّها: مشتريات حقيقية أوّلاً، ثم حقائق المتجر.
 *
 * @return array<int,array{text: string, kind: string}>
 */
function matjar_pro_proof_messages() {
	$out = array();

	if ( matjar_pro_mod( 'matjar_pro_proof_real' ) ) {
		$out = matjar_pro_proof_orders();
	}

	$out = array_merge( $out, matjar_pro_proof_facts() );

	// خمسة تكفي، وأكثر منها يُثقل الصفحة بلا فائدة.
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
