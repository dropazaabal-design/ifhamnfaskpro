<?php
/**
 * صفحة المنتج — ترتيب العناصر وحلّ الاعتراضات.
 *
 * الترتيب هنا ليس تنظيمياً بل سلوكي: كل عنصر يُزيل اعتراضاً قبل أن يصل
 * الزائر إلى زر الشراء، بالتسلسل المتفق عليه:
 *
 *   الاسم ← التقييم ← السعر والتوفير ← التقسيط ← الخيارات
 *   ← الكمية وزر الشراء ← شارات الثقة ← موعد التوصيل ← المخزون
 *   ← نقاط المنفعة ← الأكورديون ← التقييمات ← منتجات ذات صلة
 *
 * شارات الثقة وموعد التوصيل بعد الزر مباشرة لا قبله: مكانهما الطبيعي هو
 * لحظة التردد التي تلي رؤية الزر، لا قبل رؤية السعر.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * يعيد ترتيب ملخّص المنتج.
 */
function matjar_pro_reorder_product_summary() {
	// إزالة الترتيب الافتراضي بالكامل، ثم إعادة بنائه.
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );

	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 10 );
	add_action( 'woocommerce_single_product_summary', 'matjar_pro_single_rating', 15 );
	add_action( 'woocommerce_single_product_summary', 'matjar_pro_single_price', 20 );
	add_action( 'woocommerce_single_product_summary', 'matjar_pro_single_installment', 25 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	add_action( 'woocommerce_single_product_summary', 'matjar_pro_single_trust', 40 );
	add_action( 'woocommerce_single_product_summary', 'matjar_pro_single_delivery', 45 );
	add_action( 'woocommerce_single_product_summary', 'matjar_pro_single_stock', 50 );
	add_action( 'woocommerce_single_product_summary', 'matjar_pro_single_benefits', 55 );

	// التبويبات تصبح أكورديون أسفل الصفحة لا بجانب المنتج.
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
}
add_action( 'init', 'matjar_pro_reorder_product_summary' );

/**
 * التقييم مع رابط قافز إلى المراجعات.
 */
function matjar_pro_single_rating() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$count = (int) $product->get_review_count();

	if ( $count < 1 ) {
		return;
	}

	printf(
		'<a class="mp-single__rating" href="#reviews"><span class="mp-stars" aria-hidden="true">%1$s</span>'
			. '<span class="mp-num font-bold">%2$s</span>'
			. '<span class="underline">%3$s</span></a>',
		str_repeat( matjar_pro_get_icon( 'star', array( 'size' => 15, 'class' => 'mp-star' ) ), 5 ),
		esc_html( number_format_i18n( (float) $product->get_average_rating(), 1 ) ),
		esc_html(
			sprintf(
				/* translators: %s: عدد التقييمات. */
				_n( '(%s تقييم)', '(%s تقييماً)', $count, 'matjar-pro' ),
				number_format_i18n( $count )
			)
		)
	);
}

/**
 * كتلة السعر: السعر الحالي، والقديم مشطوباً، ومبلغ التوفير.
 *
 * «وفّرت 100» أقوى من «خصم 36%»: المبلغ ملموس والنسبة مجرّدة. يُعرض
 * الاثنان معاً، الشارة على الصورة والمبلغ هنا.
 */
function matjar_pro_single_price() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	echo '<div class="mp-single__price">';
	echo '<div class="mp-single__price-row">' . wp_kses_post( $product->get_price_html() ) . '</div>';

	$discount = matjar_pro_sale_percentage( $product );

	if ( $discount > 0 ) {
		$regular = (float) wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) );
		$saved   = $regular - (float) wc_get_price_to_display( $product );

		if ( $saved > 0 ) {
			printf(
				'<span class="mp-pill mp-pill--discount mp-single__saved">%1$s %2$s</span>',
				matjar_pro_get_icon( 'tag', array( 'size' => 13 ) ),
				wp_kses_post(
					sprintf(
						/* translators: %s: المبلغ الموفّر منسّقاً بعملة المتجر. */
						__( 'وفّرت %s', 'matjar-pro' ),
						wc_price( $saved )
					)
				)
			);
		}
	}

	if ( wc_tax_enabled() ) {
		echo '<span class="mp-single__tax-note">' . esc_html__( 'السعر شامل ضريبة القيمة المضافة', 'matjar-pro' ) . '</span>';
	}

	echo '</div>';
}

/**
 * سطر التقسيط.
 */
function matjar_pro_single_installment() {
	get_template_part( 'template-parts/product/installment' );
}

/**
 * شارات الثقة وطرق الدفع.
 */
function matjar_pro_single_trust() {
	get_template_part( 'template-parts/product/trust-row' );
}

/**
 * موعد التوصيل المتوقع.
 */
function matjar_pro_single_delivery() {
	get_template_part( 'template-parts/product/delivery' );
}

/**
 * تحذير قلّة المخزون — من مخزون ووكومرس الحقيقي.
 *
 * لا عدّاد وهمي ولا «5 أشخاص يشاهدون». الشحّ المزيّف يُكتشف، ويحرق الثقة،
 * ويعرّض التاجر لمساءلة نظامية في أسواق عدة.
 */
function matjar_pro_single_stock() {
	global $product;

	if ( ! $product instanceof WC_Product || ! $product->managing_stock() ) {
		return;
	}

	$threshold = (int) matjar_pro_mod( 'matjar_pro_low_stock_threshold' );
	$quantity  = $product->get_stock_quantity();

	if ( $threshold < 1 || null === $quantity || $quantity < 1 || $quantity > $threshold ) {
		return;
	}

	printf(
		'<p class="mp-single__stock">%1$s<span>%2$s</span></p>',
		matjar_pro_get_icon( 'alert', array( 'size' => 16 ) ),
		esc_html(
			sprintf(
				/* translators: %s: الكمية المتبقية. */
				_n( 'آخر %s قطعة', 'آخر %s قطع', $quantity, 'matjar-pro' ),
				number_format_i18n( $quantity )
			)
		)
	);
}

/**
 * نقاط المنفعة، من الوصف المختصر.
 *
 * الوصف المختصر يُعرض كنقاط لا كفقرة: النقاط تُقرأ بالمسح السريع، والفقرة
 * تُتخطّى على الجوال.
 */
function matjar_pro_single_benefits() {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$excerpt = $product->get_short_description();

	if ( '' === trim( wp_strip_all_tags( $excerpt ) ) ) {
		return;
	}

	echo '<div class="mp-single__benefits">' . wp_kses_post( wpautop( $excerpt ) ) . '</div>';
}

/**
 * يحوّل تبويبات المنتج إلى أكورديون.
 *
 * التبويبات تخفي محتواها خلف نقرة على الجوال وتُقرأ كعناصر ثانوية. الأكورديون
 * يُبقي العناوين كلها مرئية فيعرف الزائر ما تحتها.
 *
 * @param array $tabs التبويبات.
 * @return array
 */
function matjar_pro_product_tabs( $tabs ) {
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = __( 'المواصفات والقياسات', 'matjar-pro' );
	}

	if ( isset( $tabs['description'] ) ) {
		$tabs['description']['title'] = __( 'الوصف', 'matjar-pro' );
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'matjar_pro_product_tabs', 98 );

/**
 * ألوان عيّنات الخيارات.
 *
 * ووكومرس لا يحفظ لوناً للخيار، فيُقرأ من حقل مخصّص على المُصطلح إن وُجد،
 * وإلا يُعرض الخيار كزر نصّي. التاجر يضبط الحقل matjar_pro_swatch على
 * المُصطلح، أو يستخدم هذه التصفية لربط أسماء الخيارات بألوانها.
 *
 * @param string $value    اللون أو سلسلة فارغة.
 * @param string $taxonomy التصنيف.
 * @param string $slug     معرّف الخيار.
 * @return string
 */
function matjar_pro_swatch_color( $value, $taxonomy, $slug ) {
	if ( '' !== $value || ! taxonomy_exists( $taxonomy ) ) {
		return $value;
	}

	$term = get_term_by( 'slug', $slug, $taxonomy );

	if ( ! $term instanceof WP_Term ) {
		return '';
	}

	$stored = get_term_meta( $term->term_id, 'matjar_pro_swatch', true );

	return is_string( $stored ) && preg_match( '/^#[0-9a-fA-F]{6}$/', $stored ) ? $stored : '';
}
add_filter( 'matjar_pro_swatch_color', 'matjar_pro_swatch_color', 10, 3 );

/**
 * يعيد لون عيّنة لخيار معيّن.
 *
 * @param string $taxonomy التصنيف.
 * @param string $slug     معرّف الخيار.
 * @return string
 */
function matjar_pro_get_swatch_color( $taxonomy, $slug ) {
	/**
	 * تصفية لون العيّنة.
	 *
	 * @param string $value    القيمة الابتدائية.
	 * @param string $taxonomy التصنيف.
	 * @param string $slug     معرّف الخيار.
	 */
	return (string) apply_filters( 'matjar_pro_swatch_color', '', $taxonomy, $slug );
}

/**
 * «اشترِ الآن» يحوّل إلى صفحة الدفع بعد الإضافة.
 *
 * الحقل المخفي علامة تحويل فقط ولا يحمل أي أثر أمني: لا يفعل إلا استبدال
 * وجهة التحويل بصفحة دفع المتجر نفسه.
 *
 * @param string $url الوجهة الافتراضية.
 * @return string
 */
function matjar_pro_buy_now_redirect( $url ) {
	if ( empty( $_REQUEST['mp-buy-now'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return $url;
	}

	if ( function_exists( 'wc_get_checkout_url' ) && wc_notice_count( 'error' ) < 1 ) {
		return wc_get_checkout_url();
	}

	return $url;
}
add_filter( 'woocommerce_add_to_cart_redirect', 'matjar_pro_buy_now_redirect', 20 );

/**
 * منتجات ذات صلة كشريط أفقي بدل شبكة.
 *
 * @param array $args وسائط الاستعلام.
 * @return array
 */
function matjar_pro_related_args( $args ) {
	$args['posts_per_page'] = 8;
	$args['columns']        = 4;

	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'matjar_pro_related_args', 20 );
