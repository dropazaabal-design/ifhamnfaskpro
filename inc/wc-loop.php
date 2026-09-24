<?php
/**
 * بطاقة المنتج وشبكة الأقسام.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * يُفرِّغ حلقة المنتجات من مخرجات ووكومرس الافتراضية.
 *
 * البطاقة تُبنى بالكامل في woocommerce/content-product.php، لكن الخطّافات
 * نفسها تبقى تعمل فتظلّ الإضافات التي تعتمد عليها متوافقة.
 */
function matjar_pro_strip_loop_defaults() {
	remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
	remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
	remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
}
add_action( 'init', 'matjar_pro_strip_loop_defaults' );

/**
 * نسبة الخصم الصحيحة لمنتج، أو صفر إن لم يكن عليه خصم.
 *
 * تُحسب من السعر المعروض لا من سعر التخزين، فتحترم إعدادات الضريبة.
 *
 * @param WC_Product $product المنتج.
 * @return int
 */
function matjar_pro_sale_percentage( $product ) {
	if ( ! $product instanceof WC_Product || ! $product->is_on_sale() ) {
		return 0;
	}

	$regular = (float) wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) );
	$current = (float) wc_get_price_to_display( $product );

	if ( $regular <= 0 || $current >= $regular ) {
		return 0;
	}

	return (int) round( ( ( $regular - $current ) / $regular ) * 100 );
}

/**
 * يقدّم السعر الجديد على القديم في عرض الخصم.
 *
 * ووكومرس يُخرج <del> ثم <ins>، أي أن العين في RTL تقرأ السعر القديم أولاً:
 * «279 ثم 179». وهذا يضعف أثر الخصم — أول رقم يقع عليه النظر يجب أن يكون
 * ما سيدفعه العميل فعلاً.
 *
 * النسختان المرئيتان مخفيتان عن قارئ الشاشة، ويُضاف جملة صريحة بدلاً منهما،
 * لأن «179 279» بلا سياق لا تُفهم مسموعة.
 *
 * @param string     $html          الناتج الافتراضي.
 * @param string|int $regular_price السعر الأصلي.
 * @param string|int $sale_price    سعر الخصم.
 * @return string
 */
function matjar_pro_sale_price_html( $html, $regular_price, $sale_price ) {
	$regular = is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price;
	$sale    = is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price;

	return '<ins aria-hidden="true">' . $sale . '</ins> <del aria-hidden="true">' . $regular . '</del>'
		. '<span class="screen-reader-text">'
		. sprintf(
			/* translators: 1: السعر الأصلي، 2: السعر بعد الخصم. */
			esc_html__( 'السعر الأصلي %1$s، والسعر الآن %2$s.', 'matjar-pro' ),
			wp_strip_all_tags( $regular ),
			wp_strip_all_tags( $sale )
		)
		. '</span>';
}
add_filter( 'woocommerce_format_sale_price', 'matjar_pro_sale_price_html', 20, 3 );

/**
 * صورة المنتج بنسبة ثابتة 4:5.
 *
 * النسبة الثابتة مع سمتي width و height هي ما يمنع انزياح التخطيط (CLS):
 * المتصفح يحجز المساحة قبل وصول الصورة. والمقاس matjar-pro-card مقصوص
 * بالقوة على 400×500، فلا تُفلت صورة بنسبة مختلفة وتكسر الشبكة.
 *
 * @param WC_Product $product المنتج.
 * @param bool       $eager   هل تُحمَّل بأولوية (أول صفّ في الشبكة).
 * @return string
 */
function matjar_pro_product_thumbnail( $product, $eager = false ) {
	$id = $product->get_image_id();

	if ( ! $id ) {
		return '<div class="mp-product__placeholder" aria-hidden="true"><span>'
			. esc_html__( 'لا صورة', 'matjar-pro' ) . '</span></div>';
	}

	return wp_get_attachment_image(
		$id,
		'matjar-pro-card',
		false,
		array(
			'class'         => 'mp-product__img',
			'alt'           => esc_attr( $product->get_name() ),
			'sizes'         => '(min-width: 1024px) 25vw, (min-width: 640px) 33vw, 50vw',
			'loading'       => $eager ? 'eager' : 'lazy',
			'decoding'      => $eager ? 'sync' : 'async',
			'fetchpriority' => $eager ? 'high' : 'auto',
		)
	);
}

/**
 * زر الإضافة السريعة على البطاقة.
 *
 * المنتج ذو المتغيّرات لا يمكن إضافته بنقرة واحدة — يحتاج اختيار المقاس
 * واللون — فيتحوّل الزر عنده إلى رابط للصفحة بأيقونة مختلفة، بدل زر يفشل
 * ويُحبط العميل.
 *
 * @param WC_Product $product المنتج.
 * @return string
 */
function matjar_pro_quick_add_button( $product ) {
	$label_open = __( 'اعرض الخيارات', 'matjar-pro' );
	$label_add  = __( 'أضف إلى السلة', 'matjar-pro' );

	if ( ! $product->is_in_stock() ) {
		return '';
	}

	$needs_page = $product->is_type( 'variable' ) || $product->is_type( 'grouped' ) || $product->is_type( 'external' ) || ! $product->is_purchasable();

	if ( $needs_page ) {
		return sprintf(
			'<a class="mp-product__quick" href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( $product->get_permalink() ),
			esc_attr( $label_open . ' — ' . $product->get_name() ),
			matjar_pro_get_icon( 'arrow', array( 'size' => 19 ) )
		);
	}

	return sprintf(
		'<button type="button" class="mp-product__quick" data-mp-quick-add="%1$d" aria-label="%2$s">'
			. '<span class="mp-product__quick-idle">%3$s</span>'
			. '<span class="mp-product__quick-done">%4$s</span>'
			. '<span class="mp-product__quick-busy" aria-hidden="true"></span>'
			. '</button>',
		(int) $product->get_id(),
		esc_attr( $label_add . ' — ' . $product->get_name() ),
		matjar_pro_get_icon( 'plus', array( 'size' => 20 ) ),
		matjar_pro_get_icon( 'check', array( 'size' => 20 ) )
	);
}

/**
 * سطر التقييم — يُطبع فقط عند وجود تقييمات فعلية.
 *
 * بطاقة تعرض «0.0 (0)» تضرّ أكثر مما تنفع: تُبلّغ الزائر أن لا أحد اشترى.
 *
 * @param WC_Product $product المنتج.
 * @return string
 */
function matjar_pro_product_rating( $product ) {
	$count = (int) $product->get_review_count();

	if ( $count < 1 ) {
		return '';
	}

	$average = (float) $product->get_average_rating();

	return sprintf(
		'<span class="mp-product__rating">%1$s<span class="mp-num">%2$s</span><span class="mp-product__rating-count mp-num">(%3$s)</span></span>',
		matjar_pro_get_icon( 'star', array( 'size' => 13, 'class' => 'mp-star' ) ),
		esc_html( number_format_i18n( $average, 1 ) ),
		esc_html( number_format_i18n( $count ) )
	);
}

/**
 * عدد أعمدة الشبكة كصنف Tailwind.
 *
 * @return string
 */
function matjar_pro_grid_classes() {
	/**
	 * تصفية أصناف شبكة المنتجات.
	 *
	 * @param string $classes الأصناف.
	 */
	return apply_filters(
		'matjar_pro_grid_classes',
		'grid grid-cols-2 gap-x-3 gap-y-8 sm:grid-cols-3 sm:gap-x-4 lg:grid-cols-4 lg:gap-x-5'
	);
}
