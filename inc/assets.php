<?php
/**
 * تحميل الأصول: الخطوط، وTailwind المبني، وVanilla JS، وإزالة ما لا نستخدمه.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * روابط preload لملفات الخط التي تُرسم فوق الطيّة.
 *
 * تُطبع قبل أي شيء آخر في الترويسة، لأن الخط أثقل أصل في الصفحة وأول ما
 * يؤثّر في قياس LCP.
 */
function matjar_pro_preload_fonts() {
	foreach ( matjar_pro_font_preloads() as $url ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $url )
		);
	}
}
add_action( 'wp_head', 'matjar_pro_preload_fonts', 1 );

/**
 * يطبع قواعد @font-face ومتغيّرات CSS مضمّنة في الترويسة.
 *
 * مضمّنة لا مُحمَّلة من ملف: توفير طلب شبكة كامل على المسار الحرج، ومنع
 * وميض الألوان الافتراضية قبل وصول ورقة التنسيق.
 */
function matjar_pro_print_critical_css() {
	$css = matjar_pro_font_faces_css() . matjar_pro_css_variables();

	if ( '' === $css ) {
		return;
	}

	printf( "<style id=\"matjar-pro-tokens\">%s</style>\n", $css );
}
add_action( 'wp_head', 'matjar_pro_print_critical_css', 2 );

/**
 * تحميل أصول الواجهة.
 */
function matjar_pro_enqueue_assets() {
	$css = '/assets/css/main.css';
	$js  = '/assets/js/main.js';

	if ( file_exists( MATJAR_PRO_DIR . $css ) ) {
		wp_enqueue_style(
			'matjar-pro',
			MATJAR_PRO_URI . $css,
			array(),
			matjar_pro_asset_version( $css )
		);
	}

	if ( file_exists( MATJAR_PRO_DIR . $js ) ) {
		wp_enqueue_script(
			'matjar-pro',
			MATJAR_PRO_URI . $js,
			array(),
			matjar_pro_asset_version( $js ),
			true
		);

		wp_script_add_data( 'matjar-pro', 'strategy', 'defer' );

		wp_localize_script(
			'matjar-pro',
			'matjarPro',
			matjar_pro_script_data()
		);
	}

	/*
	 * حزمة صفحة المنتج تُحمَّل في صفحات المنتج وحدها. لا داعي أن تحمل
	 * الصفحة الأولى وصفحات الأقسام كود المعرّض والخيارات والكمية.
	 */
	$product_js = '/assets/js/product.js';

	if ( function_exists( 'is_product' ) && is_product() && file_exists( MATJAR_PRO_DIR . $product_js ) ) {
		wp_enqueue_script(
			'matjar-pro-product',
			MATJAR_PRO_URI . $product_js,
			array( 'matjar-pro' ),
			matjar_pro_asset_version( $product_js ),
			true
		);

		wp_script_add_data( 'matjar-pro-product', 'strategy', 'defer' );
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'matjar_pro_enqueue_assets' );

/**
 * البيانات التي يحتاجها JavaScript.
 *
 * تُمرَّر من PHP لا تُستنتج من الـ DOM: عناوين الـ Store API ونصوص الترجمة
 * وإعدادات العملة، حتى يبقى الكود خالياً من أي قيمة مثبّتة.
 *
 * @return array
 */
function matjar_pro_script_data() {
	$data = array(
		'restUrl'  => esc_url_raw( rest_url( 'wc/store/v1/' ) ),
		'nonce'    => wp_create_nonce( 'wp_rest' ),
		'isRtl'    => is_rtl(),
		'strings'  => array(
			'added'      => __( 'تمت الإضافة إلى السلة', 'matjar-pro' ),
			'error'      => __( 'تعذّر إكمال الطلب، حاول مرة أخرى', 'matjar-pro' ),
			'loading'    => __( 'جارٍ التحديث…', 'matjar-pro' ),
			'outOfStock' => __( 'غير متوفر حالياً', 'matjar-pro' ),
			'noResults'  => __( 'لا توجد منتجات مطابقة', 'matjar-pro' ),
			'seeAll'     => __( 'عرض كل النتائج', 'matjar-pro' ),
		),
		'currency' => array(
			'code'   => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '',
			'symbol' => matjar_pro_currency_symbol(),
		),
	);

	/**
	 * تصفية بيانات JavaScript.
	 *
	 * @param array $data البيانات.
	 */
	return apply_filters( 'matjar_pro_script_data', $data );
}

/**
 * يُلغي أوراق تنسيق ووكومرس الثلاث.
 *
 * القالب يبني تنسيق المتجر كاملاً بـ Tailwind، فإبقاء أوراق ووكومرس يعني
 * تحميل عشرات الكيلوبايتات من قواعد يُلغيها تنسيقنا سطراً بسطر.
 *
 * @param array $styles أوراق ووكومرس.
 * @return array
 */
function matjar_pro_remove_woocommerce_styles( $styles ) {
	unset( $styles['woocommerce-general'], $styles['woocommerce-layout'], $styles['woocommerce-smallscreen'] );

	return $styles;
}
add_filter( 'woocommerce_enqueue_styles', 'matjar_pro_remove_woocommerce_styles' );

/**
 * يُنظّف ما يبقى من أصول غير مستخدمة.
 *
 * أصول معرّض ووكومرس (flexslider و photoswipe و zoom) لا تُحمَّل أصلاً لأننا
 * لم نُعلن دعومات wc-product-gallery-*، لكن الإلغاء هنا احتياط في حال أضافها
 * إضافة أخرى.
 */
function matjar_pro_dequeue_unused() {
	wp_dequeue_style( 'wc-blocks-style' );
	wp_dequeue_style( 'wc-blocks-packages-style' );
	wp_dequeue_style( 'classic-theme-styles' );

	foreach ( array( 'flexslider', 'photoswipe', 'photoswipe-ui-default', 'zoom' ) as $handle ) {
		wp_dequeue_script( $handle );
		wp_dequeue_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'matjar_pro_dequeue_unused', 99 );

/**
 * يوقف سكربت الإيموجي وصورها.
 */
function matjar_pro_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
}
add_action( 'init', 'matjar_pro_disable_emojis' );

/**
 * ملاحظة على أولوية تحميل الصور:
 *
 * لا فلتر هنا عن قصد. ووردبريس 6.3 وما بعده يضيف fetchpriority="high" لصورة
 * LCP المرشّحة و loading="lazy" لما تحتها، عبر
 * wp_get_loading_optimization_attributes، وهو أدقّ من أي استدلال يدوي على
 * «أول صورة في الصفحة» — ذاك الاستدلال يمنح الأولوية للشعار لا لصورة المنتج.
 * قوالب المرحلة الثالثة تضبط الأولوية صريحاً حيث يلزم فقط.
 */
