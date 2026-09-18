<?php
/**
 * لوحة إعدادات القالب في الـ Customizer.
 *
 * كل ما قد يريد التاجر تغييره يعيش هنا: اللوحة اللونية، نمط زر الشراء،
 * الخط، الشارات، بيانات السجل التجاري، وأرقام التحويل.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * تسجيل الإعدادات.
 *
 * @param WP_Customize_Manager $wp_customize كائن الـ Customizer.
 */
function matjar_pro_customize_register( $wp_customize ) {
	$defaults = matjar_pro_defaults();

	$wp_customize->add_panel(
		'matjar_pro_panel',
		array(
			'title'       => __( 'إعدادات Matjar Pro', 'matjar-pro' ),
			'description' => __( 'كل إعدادات القالب. العملة نفسها تُضبط من ووكومرس، والقالب يتبعها.', 'matjar-pro' ),
			'priority'    => 10,
		)
	);

	/* ---------- الهوية ---------- */

	$wp_customize->add_section(
		'matjar_pro_identity',
		array(
			'title'       => __( 'هوية المتجر', 'matjar-pro' ),
			'description' => __( 'الشعار واسم المتجر. يظهر الشعار في الهيدر وفي قُمع الدفع.', 'matjar-pro' ),
			'panel'       => 'matjar_pro_panel',
			'priority'    => 5,
		)
	);

	/*
	 * شعار ووردبريس الأساسي يُنقل إلى لوحة القالب بدل تكراره: التاجر يجد كل
	 * إعداداته في موضع واحد، ويبقى الشعار مرفوعاً بآلية ووردبريس نفسها
	 * فتعمل معه أحجام الصور والـ srcset بلا كود إضافي.
	 */
	if ( $wp_customize->get_control( 'custom_logo' ) ) {
		$wp_customize->get_control( 'custom_logo' )->section  = 'matjar_pro_identity';
		$wp_customize->get_control( 'custom_logo' )->priority = 10;
	}

	if ( $wp_customize->get_control( 'blogname' ) ) {
		$wp_customize->get_control( 'blogname' )->section  = 'matjar_pro_identity';
		$wp_customize->get_control( 'blogname' )->priority = 20;
	}

	/* ---------- الهوية البصرية ---------- */

	$wp_customize->add_section(
		'matjar_pro_visual',
		array(
			'title' => __( 'الألوان والخط', 'matjar-pro' ),
			'panel' => 'matjar_pro_panel',
		)
	);

	$palette_choices = array();
	foreach ( matjar_pro_palettes() as $key => $palette ) {
		$palette_choices[ $key ] = $palette['label'];
	}

	$wp_customize->add_setting(
		'matjar_pro_palette',
		array(
			'default'           => $defaults['matjar_pro_palette'],
			'sanitize_callback' => 'matjar_pro_sanitize_palette',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_palette',
		array(
			'label'       => __( 'اللوحة اللونية', 'matjar-pro' ),
			'description' => __( 'أربع لوحات مُتحقَّق من تباينها بالكامل حسب معيار WCAG AA.', 'matjar-pro' ),
			'section'     => 'matjar_pro_visual',
			'type'        => 'select',
			'choices'     => $palette_choices,
		)
	);

	$cta_choices = array();
	foreach ( matjar_pro_cta_styles() as $key => $style ) {
		$cta_choices[ $key ] = $style['label'];
	}

	$wp_customize->add_setting(
		'matjar_pro_cta_style',
		array(
			'default'           => $defaults['matjar_pro_cta_style'],
			'sanitize_callback' => 'matjar_pro_sanitize_cta_style',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_cta_style',
		array(
			'label'       => __( 'نمط زر الشراء', 'matjar-pro' ),
			'description' => __( 'البرتقالي المحروق هو الافتراضي. الكهرماني بديل أعلى بروزاً على الخلفيات الفاتحة.', 'matjar-pro' ),
			'section'     => 'matjar_pro_visual',
			'type'        => 'select',
			'choices'     => $cta_choices,
		)
	);

	/*
	 * ألوان التاجر الأربعة. الوصف يشرح ما سيحدث فعلاً: القالب يقبل أي لون
	 * ثم يدفعه إلى حدّ التباين المطلوب، فلا يخرج متجر بنصّ لا يُقرأ.
	 */
	$mp_colors = array(
		'matjar_pro_color_primary' => array(
			'label'       => __( 'لون العلامة الأساسي', 'matjar-pro' ),
			'description' => __( 'العناوين والتذييل والنصوص. يُشتَقّ منه لون النص والنص الباهت والحدود، ويُعتَّم تلقائياً حتى يعبر ٧:١ على خلفيتك.', 'matjar-pro' ),
		),
		'matjar_pro_color_cta'     => array(
			'label'       => __( 'لون زر الشراء', 'matjar-pro' ),
			'description' => __( 'الزر الوحيد الذي يحمل مساحة لون مشبعة في الصفحة. لون نصّه يُحسَب تلقائياً بأعلى تباين.', 'matjar-pro' ),
		),
		'matjar_pro_color_bg'      => array(
			'label'       => __( 'لون خلفية الصفحات', 'matjar-pro' ),
			'description' => __( 'أرضية الموقع. الخلفيات متوسطة السطوع لا تحمل نصّاً مقروءاً، فيُدفع اللون إلى أقرب درجة تحمله.', 'matjar-pro' ),
		),
		'matjar_pro_color_surface' => array(
			'label'       => __( 'لون أسطح البطاقات', 'matjar-pro' ),
			'description' => __( 'سطح بطاقات المنتجات والحقول. اتركه فارغاً ليُشتَقّ من خلفية الصفحات.', 'matjar-pro' ),
		),
		'matjar_pro_color_accent'  => array(
			'label'       => __( 'لون الإبراز', 'matjar-pro' ),
			'description' => __( 'الشارات والعناصر الثانوية. لا يُستخدم للزر حتى لا يُزاحمه.', 'matjar-pro' ),
		),
	);

	$mp_color_priority = 30;

	foreach ( $mp_colors as $mp_key => $mp_meta ) {
		$wp_customize->add_setting(
			$mp_key,
			array(
				'default'           => $defaults[ $mp_key ],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$mp_key,
				array(
					'label'       => $mp_meta['label'],
					'description' => $mp_meta['description'],
					'section'     => 'matjar_pro_visual',
					'priority'    => $mp_color_priority,
				)
			)
		);

		$mp_color_priority += 10;
	}

	$font_choices = array();
	foreach ( matjar_pro_fonts() as $key => $font ) {
		$font_choices[ $key ] = $font['label'];
	}

	$wp_customize->add_setting(
		'matjar_pro_font',
		array(
			'default'           => $defaults['matjar_pro_font'],
			'sanitize_callback' => 'matjar_pro_sanitize_font',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_font',
		array(
			'label'       => __( 'الخط', 'matjar-pro' ),
			'description' => __( 'العائلتان مرفقتان في القالب ومستضافتان ذاتياً، ولا يُحمَّل على الزائر إلا المختارة. لا اتصال بأي خدمة خطوط خارجية.', 'matjar-pro' ),
			'section'     => 'matjar_pro_visual',
			'type'        => 'select',
			'choices'     => $font_choices,
		)
	);

	/* ---------- شريط الإعلان ---------- */

	$wp_customize->add_section(
		'matjar_pro_topbar',
		array(
			'title' => __( 'شريط الإعلان العلوي', 'matjar-pro' ),
			'panel' => 'matjar_pro_panel',
		)
	);

	$wp_customize->add_setting(
		'matjar_pro_announcement_enabled',
		array(
			'default'           => $defaults['matjar_pro_announcement_enabled'],
			'sanitize_callback' => 'matjar_pro_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_announcement_enabled',
		array(
			'label'   => __( 'إظهار شريط الإعلان', 'matjar-pro' ),
			'section' => 'matjar_pro_topbar',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'matjar_pro_announcement_text',
		array(
			'default'           => $defaults['matjar_pro_announcement_text'],
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'postMessage',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_announcement_text',
		array(
			'label'       => __( 'نص الشريط', 'matjar-pro' ),
			'description' => __( 'اتركه فارغاً ليُبنى تلقائياً من حدّ الشحن المجاني بعملة متجرك.', 'matjar-pro' ),
			'section'     => 'matjar_pro_topbar',
			'type'        => 'text',
		)
	);

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'matjar_pro_announcement_text',
			array(
				'selector'        => '.mp-announcement__text',
				'render_callback' => 'matjar_pro_announcement_message',
			)
		);
	}

	/* ---------- الشارات ---------- */

	$wp_customize->add_section(
		'matjar_pro_badges',
		array(
			'title'       => __( 'شارات الثقة وطرق الدفع', 'matjar-pro' ),
			'description' => __( 'فعّل ما يخصّ سوقك فقط. الشارات المُطفأة لا تُطبع في الصفحة إطلاقاً.', 'matjar-pro' ),
			'panel'       => 'matjar_pro_panel',
		)
	);

	// الاختيارات مرتّبة بأدوارها، فيرى التاجر في الإعدادات التجميع نفسه
	// الذي سيراه المشتري في الصفحة.
	$mp_groups   = matjar_pro_payment_groups();
	$mp_choices  = matjar_pro_badge_choices();
	$mp_priority = 10;

	foreach ( $mp_groups as $mp_group => $mp_meta ) {
		foreach ( $mp_choices as $slug => $badge ) {
			if ( ( $badge['group'] ?? '' ) !== $mp_group ) {
				continue;
			}

			$wp_customize->add_setting(
				'matjar_pro_badge_' . $slug,
				array(
					'default'           => $badge['default'],
					'sanitize_callback' => 'matjar_pro_sanitize_checkbox',
				)
			);
			$wp_customize->add_control(
				'matjar_pro_badge_' . $slug,
				array(
					/* translators: 1: اسم وسيلة الدفع. 2: دورها. */
					'label'    => sprintf( __( '%1$s — %2$s', 'matjar-pro' ), $badge['label'], $mp_meta['label'] ),
					'section'  => 'matjar_pro_badges',
					'type'     => 'checkbox',
					'priority' => $mp_priority,
				)
			);

			$mp_priority += 10;
		}
	}

	/*
	 * صورة شارات الدفع: القالب لا يشحن شعارات مدى وApple Pay وVisa وتابي —
	 * ملكيّتها لأصحابها، وشحنها في قالب يُباع مسألة ترخيص لا تصميم. التاجر
	 * يحصل على الصورة الرسمية من مزوّد الدفع ويرفعها هنا، فتظهر بدل
	 * الشارات النصّية.
	 */
	$wp_customize->add_setting(
		'matjar_pro_payment_badges_image',
		array(
			'default'           => $defaults['matjar_pro_payment_badges_image'],
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'matjar_pro_payment_badges_image',
			array(
				'label'       => __( 'صورة شارات الدفع', 'matjar-pro' ),
				'description' => __( 'صورة واحدة تجمع شعارات وسائل الدفع، تحصل عليها من مزوّد الدفع. ترفعها هنا فتظهر بدل الشارات النصّية. القالب لا يشحن شعارات العلامات التجارية.', 'matjar-pro' ),
				'section'     => 'matjar_pro_badges',
				'mime_type'   => 'image',
				'priority'    => 150,
			)
		)
	);

	$wp_customize->add_setting(
		'matjar_pro_cod_highlight',
		array(
			'default'           => $defaults['matjar_pro_cod_highlight'],
			'sanitize_callback' => 'matjar_pro_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_cod_highlight',
		array(
			'label'       => __( 'إبراز الدفع عند الاستلام ببطاقة مستقلّة', 'matjar-pro' ),
			'description' => __( 'مُفعَّلاً: بطاقة بلون النقد وسطر شرح في صفحة المنتج والسلة والقُمع. مُطفأً: شارة نصّية كبقيّة الوسائل.', 'matjar-pro' ),
			'section'     => 'matjar_pro_badges',
			'type'        => 'checkbox',
			'priority'    => 160,
		)
	);

	$wp_customize->add_setting(
		'matjar_pro_cod_note',
		array(
			'default'           => $defaults['matjar_pro_cod_note'],
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_cod_note',
		array(
			'label'       => __( 'سطر شرح الدفع عند الاستلام', 'matjar-pro' ),
			'description' => __( 'يظهر تحت عنوان الوسيلة في صفحة المنتج والسلة وقائمة بوابات الدفع. اتركه فارغاً لإخفائه.', 'matjar-pro' ),
			'section'     => 'matjar_pro_badges',
			'type'        => 'text',
			'priority'    => 200,
		)
	);

	foreach ( array(
		'matjar_pro_payment_on_product' => __( 'إظهار طرق الدفع في صفحة المنتج', 'matjar-pro' ),
		'matjar_pro_payment_in_cart'    => __( 'إظهار طرق الدفع في السلة', 'matjar-pro' ),
	) as $mp_key => $mp_label ) {
		$wp_customize->add_setting(
			$mp_key,
			array(
				'default'           => $defaults[ $mp_key ],
				'sanitize_callback' => 'matjar_pro_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			$mp_key,
			array(
				'label'    => $mp_label,
				'section'  => 'matjar_pro_badges',
				'type'     => 'checkbox',
				'priority' => 210,
			)
		);
	}

	foreach ( array(
		'matjar_pro_returns_label' => __( 'ضمان الإرجاع', 'matjar-pro' ),
		'matjar_pro_secure_label'  => __( 'ضمان أمان الدفع', 'matjar-pro' ),
	) as $mp_key => $mp_label ) {
		$wp_customize->add_setting(
			$mp_key,
			array(
				'default'           => $defaults[ $mp_key ],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$mp_key,
			array(
				'label'       => $mp_label,
				'description' => __( 'يظهر شارةً أسفل طرق الدفع في صفحة المنتج. اتركه فارغاً لإخفائه.', 'matjar-pro' ),
				'section'     => 'matjar_pro_badges',
				'type'        => 'text',
				'priority'    => 220,
			)
		);
	}

	/* ---------- التذييل والتواصل ---------- */

	$wp_customize->add_section(
		'matjar_pro_social',
		array(
			'title'       => __( 'روابط التواصل', 'matjar-pro' ),
			'description' => __( 'تظهر أيقونةً في التذييل. اترك الحقل فارغاً ولا تُطبع أيقونته إطلاقاً.', 'matjar-pro' ),
			'panel'       => 'matjar_pro_panel',
		)
	);

	$mp_social_priority = 10;

	foreach ( matjar_pro_social_networks() as $mp_slug => $mp_network ) {
		$wp_customize->add_setting(
			'matjar_pro_social_' . $mp_slug,
			array(
				'default'           => $defaults[ 'matjar_pro_social_' . $mp_slug ],
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			'matjar_pro_social_' . $mp_slug,
			array(
				'label'       => $mp_network['label'],
				'section'     => 'matjar_pro_social',
				'type'        => 'url',
				'input_attrs' => array( 'placeholder' => 'https://' ),
				'priority'    => $mp_social_priority,
			)
		);

		$mp_social_priority += 10;
	}

	/* ---------- بيانات المتجر ---------- */

	$wp_customize->add_section(
		'matjar_pro_store',
		array(
			'title'       => __( 'بيانات المتجر', 'matjar-pro' ),
			'description' => __( 'تظهر في الفوتر. عرض السجل التجاري والرقم الضريبي مطلب امتثال في عدة دول، وإشارة ثقة في كل الأحوال.', 'matjar-pro' ),
			'panel'       => 'matjar_pro_panel',
		)
	);

	$text_fields = array(
		'matjar_pro_cr_number'  => __( 'رقم السجل التجاري', 'matjar-pro' ),
		'matjar_pro_vat_number' => __( 'الرقم الضريبي', 'matjar-pro' ),
		'matjar_pro_whatsapp'   => __( 'رقم واتساب بالصيغة الدولية', 'matjar-pro' ),
	);

	foreach ( $text_fields as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'matjar_pro_store',
				'type'    => 'text',
			)
		);
	}

	/* ---------- إعدادات التحويل ---------- */

	$wp_customize->add_section(
		'matjar_pro_conversion',
		array(
			'title' => __( 'إعدادات التحويل', 'matjar-pro' ),
			'panel' => 'matjar_pro_panel',
		)
	);

	$wp_customize->add_setting(
		'matjar_pro_free_shipping_threshold',
		array(
			'default'           => $defaults['matjar_pro_free_shipping_threshold'],
			'sanitize_callback' => 'matjar_pro_sanitize_amount',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_free_shipping_threshold',
		array(
			'label'       => __( 'حدّ الشحن المجاني', 'matjar-pro' ),
			/* translators: %s: رمز عملة المتجر. */
			'description' => sprintf( __( 'بعملة متجرك الحالية (%s). صفر يعني تعطيل شريط تقدّم الشحن.', 'matjar-pro' ), matjar_pro_currency_symbol() ),
			'section'     => 'matjar_pro_conversion',
			'type'        => 'number',
			'input_attrs' => array(
				'min'  => 0,
				'step' => 1,
			),
		)
	);

	$wp_customize->add_setting(
		'matjar_pro_bnpl_enabled',
		array(
			'default'           => $defaults['matjar_pro_bnpl_enabled'],
			'sanitize_callback' => 'matjar_pro_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_bnpl_enabled',
		array(
			'label'       => __( 'إظهار سطر التقسيط في صفحة المنتج', 'matjar-pro' ),
			'description' => __( 'يُحسب القسط بقسمة السعر ويُنسَّق بعملة متجرك عبر ووكومرس.', 'matjar-pro' ),
			'section'     => 'matjar_pro_conversion',
			'type'        => 'checkbox',
		)
	);

	$numbers = array(
		'matjar_pro_bnpl_parts'          => array(
			'label' => __( 'عدد دفعات التقسيط', 'matjar-pro' ),
			'min'   => 2,
			'max'   => 12,
		),
		'matjar_pro_low_stock_threshold' => array(
			'label' => __( 'حدّ تحذير قلّة المخزون', 'matjar-pro' ),
			'min'   => 0,
			'max'   => 50,
		),
		'matjar_pro_delivery_min_days'   => array(
			'label' => __( 'أقل عدد أيام للتوصيل', 'matjar-pro' ),
			'min'   => 0,
			'max'   => 60,
		),
		'matjar_pro_delivery_max_days'   => array(
			'label' => __( 'أكثر عدد أيام للتوصيل', 'matjar-pro' ),
			'min'   => 0,
			'max'   => 90,
		),
	);

	foreach ( $numbers as $key => $number ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'absint',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'       => $number['label'],
				'section'     => 'matjar_pro_conversion',
				'type'        => 'number',
				'input_attrs' => array(
					'min' => $number['min'],
					'max' => $number['max'],
				),
			)
		);
	}

	$wp_customize->add_setting(
		'matjar_pro_optional_email',
		array(
			'default'           => $defaults['matjar_pro_optional_email'],
			'sanitize_callback' => 'matjar_pro_sanitize_checkbox',
		)
	);
	$wp_customize->add_control(
		'matjar_pro_optional_email',
		array(
			'label'       => __( 'البريد الإلكتروني اختياري في الدفع', 'matjar-pro' ),
			'description' => __( 'الجوال هو الهوية الفعلية في السوق المستهدف، وإجبار البريد يرفع ترك السلة. تنبيه: بعض بوّابات الدفع تطلب بريداً، ورسالة تأكيد الطلب لا تُرسل بدونه — أطفئه إن كانت بوّابتك تحتاجه.', 'matjar-pro' ),
			'section'     => 'matjar_pro_conversion',
			'type'        => 'checkbox',
		)
	);

	$toggles = array(
		'matjar_pro_sticky_buy_bar' => __( 'شريط الشراء الثابت في صفحة المنتج', 'matjar-pro' ),
		'matjar_pro_bottom_nav'     => __( 'شريط التنقل السفلي على الجوال', 'matjar-pro' ),
	);

	foreach ( $toggles as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'matjar_pro_sanitize_checkbox',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'matjar_pro_conversion',
				'type'    => 'checkbox',
			)
		);
	}
}
add_action( 'customize_register', 'matjar_pro_customize_register' );

/**
 * يبني نص شريط الإعلان.
 *
 * @return string
 */
function matjar_pro_announcement_message() {
	$custom = matjar_pro_mod( 'matjar_pro_announcement_text' );

	if ( '' !== trim( (string) $custom ) ) {
		return $custom;
	}

	$threshold = (float) matjar_pro_mod( 'matjar_pro_free_shipping_threshold' );

	if ( $threshold > 0 && function_exists( 'wc_price' ) ) {
		/* translators: %s: المبلغ منسّقاً بعملة المتجر. */
		return sprintf( __( 'توصيل مجاني للطلبات فوق %s', 'matjar-pro' ), wp_strip_all_tags( wc_price( $threshold ) ) );
	}

	return '';
}

/* ---------- دوال التنقية ---------- */

/**
 * @param mixed $value القيمة.
 * @return bool
 */
function matjar_pro_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * @param string $value القيمة.
 * @return string
 */
function matjar_pro_sanitize_palette( $value ) {
	return array_key_exists( $value, matjar_pro_palettes() ) ? $value : 'navy';
}

/**
 * @param string $value القيمة.
 * @return string
 */
function matjar_pro_sanitize_cta_style( $value ) {
	return array_key_exists( $value, matjar_pro_cta_styles() ) ? $value : 'palette';
}

/**
 * @param string $value القيمة.
 * @return string
 */
function matjar_pro_sanitize_font( $value ) {
	$defaults = matjar_pro_defaults();

	return array_key_exists( $value, matjar_pro_fonts() ) ? $value : $defaults['matjar_pro_font'];
}

/**
 * @param mixed $value القيمة.
 * @return float
 */
function matjar_pro_sanitize_amount( $value ) {
	return max( 0, (float) $value );
}
