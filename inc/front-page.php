<?php
/**
 * إعدادات محتوى الواجهة: الهيرو، شريط الثقة، بلاطات الترويج.
 *
 * فُصلت عن inc/customizer.php لأن تلك لوحة «كيف يبدو المتجر» وهذه لوحة
 * «ما يظهر في صفحته الأولى».
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * تخطيطات الهيرو المتاحة.
 *
 * stacked هو الافتراضي عن قصد: الصورة كاملة ثم النص على سطح صريح تحتها.
 * لا يحتاج طبقة تعتيم، فتظهر صورة المنتج بكامل ألوانها، ويبقى التباين
 * مضموناً حسابياً بلا افتراضات عن الصورة التي سيرفعها التاجر.
 *
 * @return array<string,string>
 */
function matjar_pro_hero_layouts() {
	return array(
		'stacked' => __( 'الصورة ثم النص — موصى به', 'matjar-pro' ),
		'overlay' => __( 'النص فوق الصورة', 'matjar-pro' ),
	);
}

/**
 * عناصر شريط الثقة.
 *
 * الأيقونة ثابتة لكل خانة والنص من التاجر. الخانة التي يُفرَّغ عنوانها لا
 * تُطبع، فيستطيع التاجر إخفاء ما لا يخصّ متجره بحذف النص لا بإعداد إضافي.
 *
 * @return array<int,array>
 */
function matjar_pro_trust_items() {
	$icons = array(
		1 => 'truck',
		2 => 'cash',
		3 => 'return',
		4 => 'whatsapp',
	);

	$items = array();

	foreach ( $icons as $index => $icon ) {
		$title = trim( (string) matjar_pro_mod( "matjar_pro_trust_{$index}_title" ) );

		if ( '' === $title ) {
			continue;
		}

		$items[] = array(
			'icon'     => $icon,
			'title'    => $title,
			'subtitle' => trim( (string) matjar_pro_mod( "matjar_pro_trust_{$index}_subtitle" ) ),
		);
	}

	return $items;
}

/**
 * بلاطات الترويج المُعبّأة فقط.
 *
 * @return array<int,array>
 */
function matjar_pro_promo_tiles() {
	$tiles = array();

	foreach ( array( 1, 2 ) as $index ) {
		$image = (int) matjar_pro_mod( "matjar_pro_promo_{$index}_image" );
		$label = trim( (string) matjar_pro_mod( "matjar_pro_promo_{$index}_label" ) );

		if ( ! $image && '' === $label ) {
			continue;
		}

		$tiles[] = array(
			'image' => $image,
			'label' => $label,
			'url'   => (string) matjar_pro_mod( "matjar_pro_promo_{$index}_url" ),
		);
	}

	return $tiles;
}

/**
 * هل للهيرو محتوى يستحق الطباعة؟
 *
 * @return bool
 */
function matjar_pro_has_hero() {
	if ( ! matjar_pro_mod( 'matjar_pro_hero_enabled' ) ) {
		return false;
	}

	return (bool) matjar_pro_mod( 'matjar_pro_hero_image' )
		|| '' !== trim( (string) matjar_pro_mod( 'matjar_pro_hero_headline' ) );
}

/**
 * تسجيل إعدادات محتوى الواجهة.
 *
 * @param WP_Customize_Manager $wp_customize كائن الـ Customizer.
 */
function matjar_pro_front_page_customize( $wp_customize ) {
	$defaults = matjar_pro_defaults();

	/* ---------- الهيرو ---------- */

	$wp_customize->add_section(
		'matjar_pro_hero',
		array(
			'title'       => __( 'الهيرو', 'matjar-pro' ),
			'description' => __( 'صورة واحدة ثابتة بلا كاروسيل: الشرائح بعد الأولى تحصل على تفاعل مهمل وتكلّف قياس LCP مباشرة.', 'matjar-pro' ),
			'panel'       => 'matjar_pro_panel',
		)
	);

	$wp_customize->add_setting(
		'matjar_pro_hero_enabled',
		array(
			'default'           => $defaults['matjar_pro_hero_enabled'],
			'sanitize_callback' => 'matjar_pro_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_hero_enabled',
		array(
			'label'   => __( 'إظهار الهيرو في الصفحة الأولى', 'matjar-pro' ),
			'section' => 'matjar_pro_hero',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'matjar_pro_hero_layout',
		array(
			'default'           => $defaults['matjar_pro_hero_layout'],
			'sanitize_callback' => 'matjar_pro_sanitize_hero_layout',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_hero_layout',
		array(
			'label'       => __( 'التخطيط', 'matjar-pro' ),
			'description' => __( '«النص فوق الصورة» يفرض طبقة تعتيم بشفافية 65% لأنها أدنى قيمة تضمن تبايناً 5.25:1 للنص الأبيض فوق أي صورة. لذلك يُنصح بالتخطيط الأول: صورتك تظهر بكامل ألوانها.', 'matjar-pro' ),
			'section'     => 'matjar_pro_hero',
			'type'        => 'select',
			'choices'     => matjar_pro_hero_layouts(),
		)
	);

	$wp_customize->add_setting(
		'matjar_pro_hero_image',
		array(
			'default'           => $defaults['matjar_pro_hero_image'],
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'matjar_pro_hero_image',
			array(
				'label'       => __( 'صورة الهيرو', 'matjar-pro' ),
				'description' => __( 'نسبة 4:5 على الجوال. يُفضَّل 1600 بكسل عرضاً على الأقل.', 'matjar-pro' ),
				'section'     => 'matjar_pro_hero',
				'mime_type'   => 'image',
			)
		)
	);

	$hero_texts = array(
		'matjar_pro_hero_eyebrow'   => array(
			'label' => __( 'سطر فوق العنوان', 'matjar-pro' ),
			'type'  => 'text',
		),
		'matjar_pro_hero_headline'  => array(
			'label' => __( 'العنوان', 'matjar-pro' ),
			'type'  => 'text',
		),
		'matjar_pro_hero_subtext'   => array(
			'label' => __( 'سطر الوصف', 'matjar-pro' ),
			'type'  => 'textarea',
		),
		'matjar_pro_hero_cta_label' => array(
			'label' => __( 'نص زر الهيرو', 'matjar-pro' ),
			'type'  => 'text',
		),
	);

	foreach ( $hero_texts as $key => $field ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'textarea' === $field['type'] ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'transport'         => 'postMessage',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $field['label'],
				'section' => 'matjar_pro_hero',
				'type'    => $field['type'],
			)
		);
	}

	$wp_customize->add_setting(
		'matjar_pro_hero_cta_url',
		array(
			'default'           => $defaults['matjar_pro_hero_cta_url'],
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_hero_cta_url',
		array(
			'label'       => __( 'رابط زر الهيرو', 'matjar-pro' ),
			'description' => __( 'اتركه فارغاً ليتجه إلى صفحة المتجر.', 'matjar-pro' ),
			'section'     => 'matjar_pro_hero',
			'type'        => 'url',
		)
	);

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'matjar_pro_hero_text',
			array(
				'selector'            => '.mp-hero__copy',
				'settings'            => array( 'matjar_pro_hero_eyebrow', 'matjar_pro_hero_headline', 'matjar_pro_hero_subtext', 'matjar_pro_hero_cta_label' ),
				'container_inclusive' => false,
				'render_callback'     => 'matjar_pro_render_hero_copy',
			)
		);
	}

	/* ---------- شريط الثقة ---------- */

	$wp_customize->add_section(
		'matjar_pro_trust_strip',
		array(
			'title'       => __( 'شريط الثقة', 'matjar-pro' ),
			'description' => __( 'يأتي مباشرة بعد الهيرو لأن الزائر القادم من إعلان يقرّر البقاء أو الخروج في أول ثوانٍ، وقراره يعتمد على «هل هذا متجر حقيقي؟» لا على المنتجات. فرّغ عنوان أي خانة لإخفائها.', 'matjar-pro' ),
			'panel'       => 'matjar_pro_panel',
		)
	);

	$wp_customize->add_setting(
		'matjar_pro_trust_enabled',
		array(
			'default'           => $defaults['matjar_pro_trust_enabled'],
			'sanitize_callback' => 'matjar_pro_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_trust_enabled',
		array(
			'label'   => __( 'إظهار شريط الثقة', 'matjar-pro' ),
			'section' => 'matjar_pro_trust_strip',
			'type'    => 'checkbox',
		)
	);

	$trust_labels = array(
		1 => __( 'الخانة الأولى — أيقونة شحن', 'matjar-pro' ),
		2 => __( 'الخانة الثانية — أيقونة دفع نقدي', 'matjar-pro' ),
		3 => __( 'الخانة الثالثة — أيقونة إرجاع', 'matjar-pro' ),
		4 => __( 'الخانة الرابعة — أيقونة واتساب', 'matjar-pro' ),
	);

	foreach ( $trust_labels as $index => $label ) {
		foreach ( array( 'title' => __( 'العنوان', 'matjar-pro' ), 'subtitle' => __( 'سطر التفصيل', 'matjar-pro' ) ) as $part => $part_label ) {
			$key = "matjar_pro_trust_{$index}_{$part}";

			$wp_customize->add_setting(
				$key,
				array(
					'default'           => $defaults[ $key ],
					'sanitize_callback' => 'sanitize_text_field',
					'transport'         => 'refresh',
				)
			);
			$wp_customize->add_control(
				$key,
				array(
					'label'       => 'title' === $part ? $label : $part_label,
					'description' => 'subtitle' === $part ? __( 'اكتب الوعد الفعلي لمتجرك، مثل مدة الشحن أو مهلة الإرجاع.', 'matjar-pro' ) : '',
					'section'     => 'matjar_pro_trust_strip',
					'type'        => 'text',
				)
			);
		}
	}

	/* ---------- بلاطات الترويج ---------- */

	$wp_customize->add_section(
		'matjar_pro_promo',
		array(
			'title'       => __( 'بلاطتا الترويج', 'matjar-pro' ),
			'description' => __( 'تظهران تحت الهيرو. اتركهما فارغتين لإخفائهما.', 'matjar-pro' ),
			'panel'       => 'matjar_pro_panel',
		)
	);

	foreach ( array( 1, 2 ) as $index ) {
		$wp_customize->add_setting(
			"matjar_pro_promo_{$index}_image",
			array(
				'default'           => $defaults[ "matjar_pro_promo_{$index}_image" ],
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				"matjar_pro_promo_{$index}_image",
				array(
					/* translators: %d: رقم البلاطة. */
					'label'     => sprintf( __( 'صورة البلاطة %d', 'matjar-pro' ), $index ),
					'section'   => 'matjar_pro_promo',
					'mime_type' => 'image',
				)
			)
		);

		$wp_customize->add_setting(
			"matjar_pro_promo_{$index}_label",
			array(
				'default'           => $defaults[ "matjar_pro_promo_{$index}_label" ],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			"matjar_pro_promo_{$index}_label",
			array(
				/* translators: %d: رقم البلاطة. */
				'label'   => sprintf( __( 'عنوان البلاطة %d', 'matjar-pro' ), $index ),
				'section' => 'matjar_pro_promo',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			"matjar_pro_promo_{$index}_url",
			array(
				'default'           => $defaults[ "matjar_pro_promo_{$index}_url" ],
				'sanitize_callback' => 'esc_url_raw',
			)
		);
		$wp_customize->add_control(
			"matjar_pro_promo_{$index}_url",
			array(
				/* translators: %d: رقم البلاطة. */
				'label'   => sprintf( __( 'رابط البلاطة %d', 'matjar-pro' ), $index ),
				'section' => 'matjar_pro_promo',
				'type'    => 'url',
			)
		);
	}
}
add_action( 'customize_register', 'matjar_pro_front_page_customize' );

/**
 * إعدادات أشرطة المنتجات.
 *
 * @param WP_Customize_Manager $wp_customize كائن الـ Customizer.
 */
function matjar_pro_rails_customize( $wp_customize ) {
	$defaults = matjar_pro_defaults();

	$wp_customize->add_section(
		'matjar_pro_rails',
		array(
			'title'       => __( 'أشرطة المنتجات', 'matjar-pro' ),
			'description' => __( 'تظهر في الصفحة الأولى تحت بلاطات الترويج، وتُسحب أفقياً على الجوال.', 'matjar-pro' ),
			'panel'       => 'matjar_pro_panel',
		)
	);

	$rails = array(
		'best' => __( 'شريط الأكثر مبيعاً', 'matjar-pro' ),
		'new'  => __( 'شريط وصل حديثاً', 'matjar-pro' ),
	);

	foreach ( $rails as $key => $label ) {
		$wp_customize->add_setting(
			"matjar_pro_rail_{$key}_enabled",
			array(
				'default'           => $defaults[ "matjar_pro_rail_{$key}_enabled" ],
				'sanitize_callback' => 'matjar_pro_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			"matjar_pro_rail_{$key}_enabled",
			array(
				'label'   => $label,
				'section' => 'matjar_pro_rails',
				'type'    => 'checkbox',
			)
		);

		$wp_customize->add_setting(
			"matjar_pro_rail_{$key}_title",
			array(
				'default'           => $defaults[ "matjar_pro_rail_{$key}_title" ],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			"matjar_pro_rail_{$key}_title",
			array(
				'label'   => __( 'العنوان', 'matjar-pro' ),
				'section' => 'matjar_pro_rails',
				'type'    => 'text',
			)
		);
	}
}
add_action( 'customize_register', 'matjar_pro_rails_customize' );

/**
 * يطبع نصوص الهيرو — تُستدعى أيضاً من التحديث الجزئي في المعاينة.
 */
function matjar_pro_render_hero_copy() {
	get_template_part( 'template-parts/hero-copy' );
}

/**
 * @param string $value القيمة.
 * @return string
 */
function matjar_pro_sanitize_hero_layout( $value ) {
	$defaults = matjar_pro_defaults();

	return array_key_exists( $value, matjar_pro_hero_layouts() ) ? $value : $defaults['matjar_pro_hero_layout'];
}

/**
 * رابط زر الهيرو، وإلا صفحة المتجر، وإلا الرئيسية.
 *
 * @return string
 */
function matjar_pro_hero_cta_url() {
	$url = trim( (string) matjar_pro_mod( 'matjar_pro_hero_cta_url' ) );

	if ( '' !== $url ) {
		return $url;
	}

	if ( matjar_pro_has_woocommerce() ) {
		$shop = wc_get_page_permalink( 'shop' );

		if ( $shop ) {
			return $shop;
		}
	}

	return home_url( '/' );
}
