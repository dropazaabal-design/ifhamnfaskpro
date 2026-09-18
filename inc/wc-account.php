<?php
/**
 * صفحة الحساب.
 *
 * المشتري لا يريد لوحة تحكم. يريد جوابين: «أين طلبي؟» و«هل عنواني صحيح؟».
 * كل ما في هذه الصفحة مرتّب حول هذين السؤالين.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * تقدّم الطلب على شكل خطوات.
 *
 * ثلاث خطوات لا أكثر: ووكومرس لا يملك حالة «تم الشحن» في نواته، وإضافة
 * خطوة لا تقابلها حالة حقيقية تعني شريط تقدّم يكذب.
 *
 * الحالات النهائية غير الناجحة (ملغى، مُرجَع، فاشل) لا تُعرض كتقدّم، بل
 * كحالة صريحة: شريط تقدّم لطلب ملغى مُضلِّل.
 *
 * @param WC_Order $order الطلب.
 * @return array{steps: array<int,string>, current: int}|null
 */
function matjar_pro_order_progress( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return null;
	}

	$steps = array(
		__( 'تم استلام الطلب', 'matjar-pro' ),
		__( 'قيد التجهيز', 'matjar-pro' ),
		__( 'تم التسليم', 'matjar-pro' ),
	);

	$map = array(
		'pending'    => 0,
		'on-hold'    => 0,
		'processing' => 1,
		'completed'  => 2,
	);

	$status  = $order->get_status();
	$current = isset( $map[ $status ] ) ? $map[ $status ] : null;

	/**
	 * تصفية تقدّم الطلب.
	 *
	 * تسمح للتاجر بإضافة حالة مخصّصة (مثل «تم الشحن») إلى المقياس.
	 *
	 * @param array{steps: array<int,string>, current: int|null} $progress التقدّم.
	 * @param WC_Order                                           $order    الطلب.
	 */
	$progress = apply_filters(
		'matjar_pro_order_progress',
		array(
			'steps'   => $steps,
			'current' => $current,
		),
		$order
	);

	if ( ! isset( $progress['current'] ) || null === $progress['current'] ) {
		return null;
	}

	return $progress;
}

/**
 * أحدث طلب ما زال في الطريق.
 *
 * هذا هو ما يبحث عنه العميل عند دخوله صفحة حسابه، فيُعرض أولاً وبأكبر مساحة.
 *
 * @param int $user_id معرّف المستخدم.
 * @return WC_Order|null
 */
function matjar_pro_current_order( $user_id = 0 ) {
	if ( ! function_exists( 'wc_get_orders' ) ) {
		return null;
	}

	$user_id = $user_id ? (int) $user_id : get_current_user_id();

	if ( ! $user_id ) {
		return null;
	}

	$orders = wc_get_orders(
		array(
			'customer_id' => $user_id,
			'status'      => array( 'pending', 'on-hold', 'processing' ),
			'limit'       => 1,
			'orderby'     => 'date',
			'order'       => 'DESC',
		)
	);

	return ! empty( $orders ) ? $orders[0] : null;
}

/**
 * يعيد تسمية عناصر قائمة الحساب بلغة المشتري.
 *
 * «لوحة التحكم» و«تفاصيل الحساب» مصطلحات إدارية. العميل يفكّر بـ«طلباتي»
 * و«عنواني».
 *
 * @param array $items العناصر.
 * @return array
 */
function matjar_pro_account_menu_items( $items ) {
	$rename = array(
		'dashboard'        => __( 'نظرة عامة', 'matjar-pro' ),
		'orders'           => __( 'طلباتي', 'matjar-pro' ),
		'edit-address'     => __( 'عناويني', 'matjar-pro' ),
		'edit-account'     => __( 'بياناتي', 'matjar-pro' ),
		'customer-logout'  => __( 'خروج', 'matjar-pro' ),
		'downloads'        => __( 'ملفاتي', 'matjar-pro' ),
		'payment-methods'  => __( 'طرق الدفع', 'matjar-pro' ),
	);

	foreach ( $rename as $key => $label ) {
		if ( isset( $items[ $key ] ) ) {
			$items[ $key ] = $label;
		}
	}

	return $items;
}
add_filter( 'woocommerce_account_menu_items', 'matjar_pro_account_menu_items', 20 );

/**
 * أيقونة عنصر قائمة الحساب.
 *
 * @param string $endpoint معرّف العنصر.
 * @return string
 */
function matjar_pro_account_icon( $endpoint ) {
	$icons = array(
		'dashboard'       => 'home',
		'orders'          => 'cart',
		'edit-address'    => 'truck',
		'edit-account'    => 'user',
		'downloads'       => 'gift',
		'payment-methods' => 'card',
		'customer-logout' => 'back',
	);

	$name = isset( $icons[ $endpoint ] ) ? $icons[ $endpoint ] : 'chevron';

	return matjar_pro_get_icon( $name, array( 'size' => 17 ) );
}

/**
 * عدد قطع الطلب.
 *
 * @param WC_Order $order الطلب.
 * @return int
 */
function matjar_pro_order_item_count( $order ) {
	return $order instanceof WC_Order ? (int) $order->get_item_count() : 0;
}

/**
 * صور أول ثلاث قطع في الطلب، لتمييزه بصرياً في البطاقة.
 *
 * @param WC_Order $order الطلب.
 * @param int      $limit العدد.
 * @return string[] وسوم img.
 */
function matjar_pro_order_thumbnails( $order, $limit = 3 ) {
	if ( ! $order instanceof WC_Order ) {
		return array();
	}

	$out = array();

	foreach ( $order->get_items() as $item ) {
		if ( count( $out ) >= $limit ) {
			break;
		}

		$product = $item->get_product();

		if ( ! $product || ! $product->get_image_id() ) {
			continue;
		}

		$out[] = wp_get_attachment_image(
			$product->get_image_id(),
			'woocommerce_gallery_thumbnail',
			false,
			array(
				'class'   => 'mp-order__thumb',
				'alt'     => '',
				'loading' => 'lazy',
			)
		);
	}

	return $out;
}
