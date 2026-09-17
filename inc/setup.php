<?php
/**
 * تهيئة القالب ودعومات ووردبريس وووكومرس.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * دعومات القالب وقوائم التنقل ومقاسات الصور.
 */
function matjar_pro_setup() {
	load_theme_textdomain( 'matjar-pro', MATJAR_PRO_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );

	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'القائمة الرئيسية', 'matjar-pro' ),
			'footer'  => __( 'قائمة الفوتر', 'matjar-pro' ),
			'legal'   => __( 'روابط السياسات', 'matjar-pro' ),
		)
	);

	// مقاسات الصور: 4:5 لبطاقة المنتج و1:1 لبلاطة القسم، بما يمنع انزياح التصميم.
	add_image_size( 'matjar-pro-card', 400, 500, true );
	add_image_size( 'matjar-pro-hero', 780, 975, true );
	add_image_size( 'matjar-pro-category', 200, 200, true );

	matjar_pro_setup_woocommerce_support();
}
add_action( 'after_setup_theme', 'matjar_pro_setup' );

/**
 * دعومات ووكومرس.
 *
 * تنبيه مقصود: لا نُعلن wc-product-gallery-zoom ولا -lightbox ولا -slider.
 * هذه الثلاثة تُحمّل flexslider و photoswipe و zoom وتعتمد على jQuery،
 * ومعرض القالب يُبنى بـ Vanilla JavaScript بديلاً عنها في المرحلة الثالثة.
 */
function matjar_pro_setup_woocommerce_support() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width'         => 400,
			'single_image_width'            => 780,
			'gallery_thumbnail_image_width' => 150,
			'product_grid'                  => array(
				'default_rows'    => 4,
				'min_rows'        => 1,
				'default_columns' => 2,
				'min_columns'     => 1,
				'max_columns'     => 4,
			),
		)
	);
}

/**
 * عرض المحتوى الأقصى للمحرّر والوسائط.
 */
function matjar_pro_content_width() {
	$GLOBALS['content_width'] = 780;
}
add_action( 'after_setup_theme', 'matjar_pro_content_width', 0 );

/**
 * مناطق الودجات.
 */
function matjar_pro_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'الشريط الجانبي للمتجر', 'matjar-pro' ),
			'id'            => 'shop-sidebar',
			'description'   => __( 'يظهر في صفحة الأقسام على الشاشات الكبيرة، وكلوح فلترة سفلي على الجوال.', 'matjar-pro' ),
			'before_widget' => '<section id="%1$s" class="mp-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="mp-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'matjar_pro_widgets_init' );

/**
 * أصناف body إضافية تُعبّر عن حالة الصفحة، ليبنى عليها التنسيق.
 *
 * @param string[] $classes الأصناف الحالية.
 * @return string[]
 */
function matjar_pro_body_classes( $classes ) {
	$classes[] = 'mp';

	if ( is_rtl() ) {
		$classes[] = 'mp-rtl';
	}

	if ( matjar_pro_has_woocommerce() && ( is_cart() || is_checkout() ) ) {
		$classes[] = 'mp-funnel';
	}

	return $classes;
}
add_filter( 'body_class', 'matjar_pro_body_classes' );
