<?php
/**
 * رموز التصميم: لوحات الألوان، أنماط زر الشراء، الخطوط، ومخرجات متغيّرات CSS.
 *
 * كل قيمة لون هنا مصدرها الوحيد. لا يُكتب لون سِتّ عشري في أي قالب أو ملف
 * Tailwind، بل تُقرأ كلها من متغيّرات CSS التي تطبعها هذه الملفات.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * النطاقات النصية للحروف، منقولة عن Google Fonts.
 *
 * تُستخدم في unicode-range حتى لا يُنزّل ملف الحروف اللاتينية على صفحة
 * عربية خالصة، ولا العكس.
 */
const MATJAR_PRO_RANGE_ARABIC = 'U+0600-06FF, U+0750-077F, U+0870-088E, U+0890-0891, U+0897-08E1, U+08E3-08FF, U+200C-200E, U+2010-2011, U+204F, U+2E41, U+FB50-FDFF, U+FE70-FE74, U+FE76-FEFC, U+102E0-102FB, U+10E60-10E7E, U+10EC2-10EC4, U+10EFC-10EFF, U+1EE00-1EE03, U+1EE05-1EE1F, U+1EE21-1EE22, U+1EE24, U+1EE27, U+1EE29-1EE32, U+1EE34-1EE37, U+1EE39, U+1EE3B, U+1EE42, U+1EE47, U+1EE49, U+1EE4B, U+1EE4D-1EE4F, U+1EE51-1EE52, U+1EE54, U+1EE57, U+1EE59, U+1EE5B, U+1EE5D, U+1EE5F, U+1EE61-1EE62, U+1EE64, U+1EE67-1EE6A, U+1EE6C-1EE72, U+1EE74-1EE77, U+1EE79-1EE7C, U+1EE7E, U+1EE80-1EE89, U+1EE8B-1EE9B, U+1EEA1-1EEA3, U+1EEA5-1EEA9, U+1EEAB-1EEBB, U+1EEF0-1EEF1';

const MATJAR_PRO_RANGE_LATIN = 'U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD';

/**
 * لوحات الألوان الجاهزة.
 *
 * ملاحظة: لا يوجد مفتاح cta-fg. لون نص الزر يُحسَب تلقائياً بأعلى تباين
 * ممكن عبر matjar_pro_readable_foreground، فلا يمكن لأي لوحة أو لون مخصّص
 * أن ينتج زراً غير مقروء.
 *
 * @return array<string,array>
 */
function matjar_pro_palettes() {
	/*
	 * اللوحات مبنية على جرد قيم موثّقة من أنظمة تصميم منشورة، لا على ذوق:
	 *
	 *   Shopify Dawn        القالب المرجعي لشوبيفاي — أرضياته وأزراره
	 *   Shopify Polaris     نظام تصميم شوبيفاي — العلامة والدلالات
	 *   Salla theme-raed    القالب المرجعي لسلة — رماديّاته ولون الخطر
	 *   WooCommerce Storefront  المنصّة التي يعمل عليها هذا القالب
	 *   WordPress TT4       سلّم المحيّدات
	 *   IBM Carbon          سلّم الأزرق
	 *
	 * وما قاسه الجرد: محيّدات هذه الأنظمة رمادي خالص (٠° و٠٪ تشبّع) في
	 * أربعة عشر قيمة من أصل أربع وثلاثين، وأزرقها كلّه في نطاق ٢١٤°–٢٢٧°
	 * — ثلاثة عشر درجة فقط — وألوان التفاعل كلّها في الطرف الغامق، والخطر
	 * قرمزي عند ٣٥٠°–٣٥٢° لا أحمر برتقالي. اللوحات تتبع هذا.
	 *
	 * سلّم المحيّدات والدلالات مشترك بين اللوحات الفاتحة الثلاث: يتغيّر لون
	 * التفاعل وحده. هذا ما يجعلها أربع لوحات لا أربع هويّات متنافرة.
	 *
	 * القيم التي لا يُنشرها أي نظام (سلّم داكن للدلالات مثلاً) تُترك
	 * لدوال التصحيح في هذا الملف: تدفعها حتى تعبر الحدّ، فلا قيمة مُختارة
	 * بالذوق ولا قيمة غير مفحوصة.
	 */
	$neutral = array(
		'bg'      => '#FFFFFF',   // Dawn scheme-1 · Storefront
		'surface' => '#F3F3F3',   // Dawn scheme-2
		'border'  => '#E3E3E3',   // Polaris color-border
		'field'   => '#A4A4A4',   // TT4 contrast-3 — يُصحَّح إلى ٣:١
		'text'    => '#4A4A4A',   // Polaris text-brand
		'muted'   => '#616161',   // Polaris text-secondary
		'ink'     => '#121212',   // Dawn scheme-1 text
		'inverse'     => '#121212',
		'inverse-ink' => '#FFFFFF',
		'shadow'      => '#121212',
	);

	$semantic = array(
		'sale'     => '#C70A24',  // Polaris bg-fill-critical
		'sale-ink' => '#A30A24',  // Polaris bg-fill-critical-hover
		'success'  => '#047B5D',  // Polaris bg-fill-success
		'info'     => '#005BD3',  // Polaris bg-fill-emphasis
		'bnpl'     => '#7F54B3',  // WooCommerce Storefront accent
	);

	return array(
		'navy'    => array(
			'label'  => __( 'كحلي عميق — الافتراضي', 'matjar-pro' ),
			'tokens' => array_merge(
				$neutral,
				$semantic,
				array(
					'cta'        => '#001D6C',   // Carbon blue-90
					'cta-hover'  => '#001141',   // Carbon blue-100
					'accent'     => '#005BD3',   // Polaris emphasis
					'accent-ink' => '#0F62FE',   // Carbon blue-60
					'progress'   => '#005BD3',
				)
			),
		),
		'emerald' => array(
			'label'  => __( 'زمردي — أزياء وطبيعي', 'matjar-pro' ),
			'tokens' => array_merge(
				$neutral,
				$semantic,
				array(
					'cta'        => '#047B5D',   // Polaris success
					'cta-hover'  => '#03614A',   // مُشتَقّ: أغمق ١٥٪
					'accent'     => '#047B5D',
					'accent-ink' => '#3FC79A',   // مُشتَقّ ليعبر على الحبر
					'progress'   => '#047B5D',
				)
			),
		),
		'mono'    => array(
			'label'  => __( 'أحادي — إلكترونيات', 'matjar-pro' ),
			'tokens' => array_merge(
				$neutral,
				$semantic,
				array(
					'cta'        => '#303030',   // Polaris bg-fill-brand
					'cta-hover'  => '#1A1A1A',   // Polaris bg-fill-brand-hover
					'accent'     => '#005BD3',   // Polaris emphasis — الأزرق التقني وحده
					'accent-ink' => '#0F62FE',   // Carbon blue-60
					'progress'   => '#005BD3',
				)
			),
		),
		'carbon'  => array(
			'label'  => __( 'كربوني — متجر داكن', 'matjar-pro' ),
			'tokens' => array(
				'bg'          => '#121212',   // Dawn scheme-4 background
				'surface'     => '#242833',   // Dawn scheme-3 — لمسة الرمادي المزرق
				'border'      => '#3A4152',   // مُشتَقّ من السطح
				'field'       => '#697389',   // مُشتَقّ — يُصحَّح إلى ٣:١
				'text'        => '#CCCCCC',   // Polaris gray-10
				'muted'       => '#A4A4A4',   // TT4 contrast-3
				'ink'         => '#FFFFFF',   // Dawn scheme-4 text
				'inverse'     => '#242833',   // Dawn scheme-3
				'inverse-ink' => '#FFFFFF',
				'shadow'      => '#000000',
				'cta'         => '#FFFFFF',   // Dawn scheme-4 button
				'cta-hover'   => '#E3E3E3',   // Polaris gray-8
				'accent'      => '#0F62FE',   // Carbon blue-60
				'accent-ink'  => '#0F62FE',
				'progress'    => '#0F62FE',
				'sale'        => '#C70A24',   // Polaris — تبقى مصمتة بنصّ محسوب
				'sale-ink'    => '#FF8FA3',   // مُشتَقّ ليُقرأ على السطح الداكن
				'success'     => '#3FC79A',   // مُشتَقّ من Polaris ليُقرأ على الداكن
				'info'        => '#78BFFF',   // مُشتَقّ من Polaris
				'bnpl'        => '#BCA0E8',   // مُشتَقّ من بنفسجي ووكومرس
			),
		),
	);
}

/**
 * أنماط زر الشراء الجاهزة.
 *
 * القرار المعتمد: البرتقالي المحروق هو الافتراضي، والكهرماني متاح للتاجر.
 * لون النص في كلٍّ منهما محسوب لا مكتوب.
 *
 * @return array<string,array>
 */
function matjar_pro_cta_styles() {
	return array(
		'palette' => array(
			'label' => __( 'حسب اللوحة المختارة', 'matjar-pro' ),
			'cta'   => '',
			'hover' => '',
		),
		'navy'    => array(
			'label' => __( 'كحلي عميق', 'matjar-pro' ),
			'cta'   => '#173F73',
			'hover' => '#102C52',
		),
		'emerald' => array(
			'label' => __( 'أخضر زمردي', 'matjar-pro' ),
			'cta'   => '#046B4E',
			'hover' => '#03513B',
		),
		'carbon'  => array(
			'label' => __( 'أسود كربوني', 'matjar-pro' ),
			'cta'   => '#14181D',
			'hover' => '#000000',
		),
	);
}


/**
 * عائلات الخطوط المرفقة مع القالب.
 *
 * تُرفق العائلتان في ملفات القالب، ولا يُحمَّل على الواجهة إلا واحدة:
 * المختارة من لوحة التحكم. لا اتصال بخدمة خطوط خارجية على الإطلاق.
 *
 * @return array<string,array>
 */
function matjar_pro_fonts() {
	return array(
		'plex'  => array(
			'label'  => __( 'IBM Plex Sans Arabic — أعلى مقروئية، أثقل (‎187KB / 6 ملفات)', 'matjar-pro' ),
			'family' => 'IBM Plex Sans Arabic',
			'stack'  => "'IBM Plex Sans Arabic', system-ui, -apple-system, 'Segoe UI', Tahoma, sans-serif",
			'faces'  => array(
				array(
					'file'    => 'ibm-plex-sans-arabic-arabic-400-normal.woff2',
					'weight'  => '400',
					'range'   => MATJAR_PRO_RANGE_ARABIC,
					'preload' => true,
				),
				array(
					'file'   => 'ibm-plex-sans-arabic-arabic-600-normal.woff2',
					'weight' => '600',
					'range'  => MATJAR_PRO_RANGE_ARABIC,
				),
				array(
					'file'    => 'ibm-plex-sans-arabic-arabic-700-normal.woff2',
					'weight'  => '700',
					'range'   => MATJAR_PRO_RANGE_ARABIC,
					'preload' => true,
				),
				array(
					'file'   => 'ibm-plex-sans-arabic-latin-400-normal.woff2',
					'weight' => '400',
					'range'  => MATJAR_PRO_RANGE_LATIN,
				),
				array(
					'file'   => 'ibm-plex-sans-arabic-latin-600-normal.woff2',
					'weight' => '600',
					'range'  => MATJAR_PRO_RANGE_LATIN,
				),
				array(
					'file'   => 'ibm-plex-sans-arabic-latin-700-normal.woff2',
					'weight' => '700',
					'range'  => MATJAR_PRO_RANGE_LATIN,
				),
			),
		),
		'cairo' => array(
			'label'  => __( 'Cairo متغيّر — الافتراضي، الأخف (‎63KB / ملفان)', 'matjar-pro' ),
			'family' => 'Cairo Variable',
			'stack'  => "'Cairo Variable', system-ui, -apple-system, 'Segoe UI', Tahoma, sans-serif",
			'faces'  => array(
				array(
					'file'    => 'cairo-arabic-wght-normal.woff2',
					'weight'  => '200 1000',
					'range'   => MATJAR_PRO_RANGE_ARABIC,
					'preload' => true,
				),
				array(
					'file'   => 'cairo-latin-wght-normal.woff2',
					'weight' => '200 1000',
					'range'  => MATJAR_PRO_RANGE_LATIN,
				),
			),
		),
	);
}

/**
 * يعيد مفتاح عائلة الخط المختارة، مُتحقَّقاً منه.
 *
 * مركزية هذه الدالة تمنع تكرار الافتراضي في ثلاثة مواضع — وهو ما كان
 * يجعل تغيير الخط الافتراضي تعديلاً في أربعة أماكن.
 *
 * @return string
 */
function matjar_pro_font_key() {
	$fonts = matjar_pro_fonts();
	$key   = matjar_pro_mod( 'matjar_pro_font' );

	if ( isset( $fonts[ $key ] ) ) {
		return $key;
	}

	$defaults = matjar_pro_defaults();

	return isset( $fonts[ $defaults['matjar_pro_font'] ] ) ? $defaults['matjar_pro_font'] : array_key_first( $fonts );
}

/**
 * يعيد مجموعة الرموز النهائية بعد دمج اللوحة المختارة ونمط الزر وأي تخصيص.
 *
 * @return array<string,string> خريطة اسم الرمز إلى لون سِتّ عشري.
 */
function matjar_pro_resolved_tokens() {
	$palettes = matjar_pro_palettes();
	$choice   = matjar_pro_mod( 'matjar_pro_palette' );

	if ( ! isset( $palettes[ $choice ] ) ) {
		$choice = 'navy';
	}

	$tokens = $palettes[ $choice ]['tokens'];

	// نمط زر الشراء يعلو على لون اللوحة عند اختياره صريحاً.
	$styles = matjar_pro_cta_styles();
	$style  = matjar_pro_mod( 'matjar_pro_cta_style' );

	if ( isset( $styles[ $style ] ) && '' !== $styles[ $style ]['cta'] ) {
		$tokens['cta']       = $styles[ $style ]['cta'];
		$tokens['cta-hover'] = $styles[ $style ]['hover'];
	}

	/*
	 * ألوان التاجر تعلو على اللوحة الجاهزة، لكنّها لا تُعتمد كما جاءت: كل
	 * لون يمرّ على matjar_pro_ensure_contrast قبل أن يدخل الرموز. هكذا
	 * يملك التاجر حريّة كاملة في الاختيار ولا يملك القدرة على نشر متجر
	 * بنصّ لا يُقرأ.
	 */
	$primary = matjar_pro_custom_color( 'matjar_pro_color_primary' );
	$page    = matjar_pro_custom_color( 'matjar_pro_color_bg' );

	if ( '' !== $primary || '' !== $page ) {
		$tokens = array_merge(
			$tokens,
			matjar_pro_derive_neutrals(
				'' !== $primary ? $primary : $tokens['ink'],
				'' !== $page ? $page : $tokens['bg']
			)
		);
	}

	// لون الإبراز: شارات العلامة والعناصر الثانوية.
	$accent = matjar_pro_custom_color( 'matjar_pro_color_accent' );

	if ( '' !== $accent ) {
		$tokens['accent']     = $accent;
		$tokens['accent-ink'] = $accent;
	}

	// لون مخصّص كامل لزر الشراء، إن أدخله التاجر.
	$custom = matjar_pro_custom_color( 'matjar_pro_color_cta' );

	if ( '' !== $custom ) {
		$tokens['cta']       = $custom;
		$tokens['cta-hover'] = matjar_pro_darken( $custom, 14 );
	}

	/*
	 * هنا تُفرض المقروئية: ألوان نصوص الأزرار والشارات المصمتة ليست خياراً
	 * بل نتيجة حساب.
	 *
	 * والمُرشَّح الغامق أغمق ما في اللوحة لا «الحبر» دائماً: في اللوحة
	 * الكربونية الحبر لونٌ فاتح، فزرٌّ أبيض كان سيُحسَب له نصّ فاتح فوق
	 * فاتح ثم يُعتَّم الزر نفسه — ويُهدَم تصميم الزر الأبيض من أصله.
	 */
	$darkest = matjar_pro_relative_luminance( $tokens['ink'] ) <= matjar_pro_relative_luminance( $tokens['bg'] )
		? $tokens['ink']
		: $tokens['bg'];

	$tokens['cta-fg'] = matjar_pro_readable_foreground( $tokens['cta'], '#FFFFFF', $darkest );

	/*
	 * الزر مساحة لون مشبعة كبيرة: إن لم يعبر أيٌّ من المُرشَّحين حدّ ٤٫٥
	 * فوقه، فاللون نفسه هو المشكلة لا نصّه، فيُدفَع حتى يعبر أحدهما.
	 */
	if ( matjar_pro_contrast_ratio( $tokens['cta-fg'], $tokens['cta'] ) < 4.5 ) {
		$tokens['cta']       = matjar_pro_ensure_contrast( $tokens['cta'], $tokens['cta-fg'], 4.5 );
		$tokens['cta-hover'] = matjar_pro_darken( $tokens['cta'], 14 );
		$tokens['cta-fg']    = matjar_pro_readable_foreground( $tokens['cta'], '#FFFFFF', $darkest );
	}

	/*
	 * الشارات المصمتة: شارة نسبة الخصم وعلامة تأكيد الاستلام تُرسمان بلونٍ
	 * كامل، فنصّهما يُحسَب كنصّ الزر. بلا هذا لا يستطيع لون دلالي أن يكون
	 * فاتحاً على لوحة داكنة ومصمتاً بنصٍّ أبيض في الوقت نفسه.
	 */
	foreach ( array( 'sale', 'success' ) as $solid ) {
		$tokens[ $solid . '-fg' ] = matjar_pro_readable_foreground( $tokens[ $solid ], '#FFFFFF', $darkest );
	}

	/*
	 * حالة الزر الفاتح: لون زر نصّه الأبيض عند ٤٫٦ يصبح عند ٤٫١ بعد تعتيمه
	 * ١٤٪ للتحويم. فيُصحَّح لون التحويم مقابل نص الزر نفسه لا مقابل شيء آخر.
	 */
	$tokens['cta-hover'] = matjar_pro_ensure_contrast( $tokens['cta-hover'], $tokens['cta-fg'], 4.5 );

	/*
	 * الألوان الدلالية تُصحَّح مقابل السطح ومقابل طبقتها الشفّافة فوقه: كتلة
	 * الدفع عند الاستلام مثلاً نصُّها بلون النقد فوق النقد بشفافية ٠٫٠٨.
	 * التصحيح يُعاد مرّتين لأن الطبقة تتغيّر بتغيّر اللون.
	 */
	/*
	 * ما يُقرأ نصّاً يُصحَّح، وما يُرسَم مصمتاً لا. لون الخصم مثلاً يبقى
	 * قرمزياً غامقاً لشارة النسبة (نصّها أبيض)، ونسخة sale-ink هي التي
	 * تُقرأ نصّاً على الطبقة — وعليها وحدها يجري التصحيح.
	 */
	foreach ( array( 'sale-ink', 'success', 'info', 'bnpl' ) as $semantic ) {
		$tokens[ $semantic ] = matjar_pro_ensure_contrast_on_tint(
			$tokens[ $semantic ],
			$tokens['surface'],
			4.5
		);
	}

	// والنص العادي يظهر أيضاً فوق هذه الطبقات (سطر شرح الاستلام مثلاً).
	$tokens['text'] = matjar_pro_ensure_contrast(
		$tokens['text'],
		array(
			$tokens['bg'],
			$tokens['surface'],
			matjar_pro_blend( $tokens['success'], $tokens['surface'] ),
			matjar_pro_blend( $tokens['sale-ink'], $tokens['surface'] ),
			matjar_pro_blend( $tokens['info'], $tokens['surface'] ),
			matjar_pro_blend( $tokens['bnpl'], $tokens['surface'] ),
		),
		4.5
	);

	/*
	 * مِلء شريط الشحن المجاني. الحدّ الذي يُقرأ منه الزائر المسافة المتبقّية
	 * هو حدّ المِلء على المجرى — والمجرى بلون الحدود — لا حدّه على السطح
	 * تحت الكتلة. اشتراط الاثنين معاً كان يُسقط تركيبات لا مشكلة فيها.
	 */
	$tokens['progress'] = matjar_pro_ensure_contrast( $tokens['progress'], $tokens['border'], 3.0, 3 );

	// حدّ الحقول وحده يُعرّف الحقل، فحدّه ٣:١ لا زخرفة.
	$tokens['field'] = matjar_pro_ensure_contrast( $tokens['field'], $tokens['surface'], 3.0, 3 );

	/*
	 * لون الإبراز نجومُ التقييم، وهي رسم يحمل معلومة فحدّها ٣:١ على البطاقة
	 * وعلى الأرضية. و accent-ink أيقونة شريط الإعلان على السطح المعكوس.
	 * يُصحَّحان أيّاً كان مصدرهما — مشحوناً في اللوحة أو مختاراً من التاجر.
	 * قيمة مشحونة لا تُعفى من الفحص: Carbon blue-60 على سطح Dawn الكحلي
	 * كان ٢٫٩٤ لا ٣٫٠٠.
	 */
	$tokens['accent'] = matjar_pro_ensure_contrast(
		$tokens['accent'],
		array( $tokens['surface'], $tokens['bg'] ),
		3.0,
		3
	);

	// accent-ink أيقونة شريط الإعلان، وهي على السطح المعكوس لا على الحبر.
	$tokens['accent-ink'] = matjar_pro_ensure_contrast( $tokens['accent-ink'], $tokens['inverse'], 3.0, 3 );

	/**
	 * تصفية رموز التصميم النهائية.
	 *
	 * @param array  $tokens الرموز.
	 * @param string $choice معرّف اللوحة المختارة.
	 */
	return apply_filters( 'matjar_pro_tokens', $tokens, $choice );
}

/**
 * يُعتِم لوناً بنسبة مئوية من قيمته.
 *
 * @param string $hex     اللون.
 * @param int    $percent النسبة.
 * @return string
 */
function matjar_pro_darken( $hex, $percent = 12 ) {
	$rgb    = matjar_pro_hex_to_rgb( $hex );
	$factor = max( 0, 1 - ( $percent / 100 ) );

	$out = '#';
	foreach ( $rgb as $channel ) {
		$out .= str_pad( dechex( (int) round( $channel * $factor ) ), 2, '0', STR_PAD_LEFT );
	}

	return strtoupper( $out );
}

/**
 * يُفتِح لوناً نحو الأبيض بنسبة مئوية.
 *
 * @param string $hex     اللون.
 * @param int    $percent النسبة.
 * @return string
 */
function matjar_pro_lighten( $hex, $percent = 12 ) {
	$rgb   = matjar_pro_hex_to_rgb( $hex );
	$ratio = max( 0, min( 100, $percent ) ) / 100;

	$out = '#';
	foreach ( $rgb as $channel ) {
		$out .= str_pad( dechex( (int) round( $channel + ( 255 - $channel ) * $ratio ) ), 2, '0', STR_PAD_LEFT );
	}

	return strtoupper( $out );
}

/**
 * يدفع لوناً حتى يعبر حدّ التباين مقابل كل خلفية قد يظهر عليها.
 *
 * هذه الدالة هي ما يجعل حرية التاجر في اختيار الألوان آمنة: يختار لوناً
 * لعلامته، والقالب لا يرفضه ولا يقبله كما هو، بل يجرّب الاتجاهين — نحو
 * العتمة ونحو الفتح — ويأخذ أوّل قيمة تعبر الحدّ بأقلّ تغيير عن اختياره.
 *
 * تجريب الاتجاهين ليس ترفاً: خلفية متوسطة السطوع لا يعبر عليها التفتيح
 * إطلاقاً ويعبر عليها التعتيم، والعكس. وقياس اتجاه واحد من سطوع الخلفية
 * كان يُنتج نصوصاً عند ٤٫٠٧:١ على خلفيات بنفسجية — وهو ما كشفه فحص
 * verify-custom-colors.php على ٢٠٠٠ تركيبة.
 *
 * @param string          $hex         اللون المطلوب.
 * @param string|string[] $backgrounds الخلفية أو الخلفيات.
 * @param float           $minimum     حدّ التباين.
 * @param int             $step        خطوة التعديل بالنسبة المئوية.
 * @return string
 */
function matjar_pro_ensure_contrast( $hex, $backgrounds, $minimum = 4.5, $step = 4 ) {
	$backgrounds = (array) $backgrounds;

	/**
	 * أدنى تباين للّون مقابل كل الخلفيات.
	 *
	 * @param string $candidate اللون المُرشَّح.
	 * @return float
	 */
	$worst = static function ( $candidate ) use ( $backgrounds ) {
		$min = INF;

		foreach ( $backgrounds as $background ) {
			$min = min( $min, matjar_pro_contrast_ratio( $candidate, $background ) );
		}

		return $min;
	};

	if ( $worst( $hex ) >= $minimum ) {
		return strtoupper( $hex );
	}

	// الاتجاهان معاً: يُؤخذ أوّل ما يعبر، والأقرب إلى اختيار التاجر أولاً.
	for ( $i = $step; $i <= 100; $i += $step ) {
		foreach ( array( matjar_pro_darken( $hex, $i ), matjar_pro_lighten( $hex, $i ) ) as $candidate ) {
			if ( $worst( $candidate ) >= $minimum ) {
				return $candidate;
			}
		}
	}

	// لم يعبر أيٌّ منهما: تُؤخذ النهاية الأفضل. المقروئية ليست خياراً.
	return $worst( '#000000' ) >= $worst( '#FFFFFF' ) ? '#000000' : '#FFFFFF';
}

/**
 * يدفع لوناً حتى يُقرأ على السطح وعلى طبقته الشفّافة فوقه معاً.
 *
 * كتل القالب الملوّنة تُبنى بلونٍ فوق طبقةٍ من اللون نفسه بشفافية ٠٫٠٨:
 * نصّ «الدفع عند الاستلام» بلون النقد فوق خلفية من النقد. الخلفية هنا
 * تتحرّك مع اللون، فلا يصحّ تثبيتها قبل البحث — وهذا ما جعل التصحيح
 * بمرورَين لا يتقارب. الحلّ أن يُحسَب المزيج من المُرشَّح نفسه في كل خطوة.
 *
 * @param string $hex     اللون.
 * @param string $surface السطح تحت الطبقة.
 * @param float  $minimum حدّ التباين.
 * @param float  $alpha   شفافية الطبقة.
 * @param int    $step    خطوة التعديل.
 * @return string
 */
function matjar_pro_ensure_contrast_on_tint( $hex, $surface, $minimum = 4.5, $alpha = 0.08, $step = 4 ) {
	/**
	 * هل يعبر المُرشَّح على السطح وعلى مزيجه فوقه.
	 *
	 * @param string $candidate اللون المُرشَّح.
	 * @return bool
	 */
	$passes = static function ( $candidate ) use ( $surface, $minimum, $alpha ) {
		return matjar_pro_contrast_ratio( $candidate, $surface ) >= $minimum
			&& matjar_pro_contrast_ratio( $candidate, matjar_pro_blend( $candidate, $surface, $alpha ) ) >= $minimum;
	};

	if ( $passes( $hex ) ) {
		return strtoupper( $hex );
	}

	for ( $i = $step; $i <= 100; $i += $step ) {
		foreach ( array( matjar_pro_darken( $hex, $i ), matjar_pro_lighten( $hex, $i ) ) as $candidate ) {
			if ( $passes( $candidate ) ) {
				return $candidate;
			}
		}
	}

	return $passes( '#000000' ) || ! $passes( '#FFFFFF' ) ? '#000000' : '#FFFFFF';
}

/**
 * يدفع لون الخلفية حتى يصبح قادراً على حمل نصّ مقروء.
 *
 * بعض الألوان لا تحمل نصّاً يعبر ٤٫٥:١ إطلاقاً — لا بالأسود ولا بالأبيض.
 * ‎#5E71B3‎ مثلاً أقصى تباين عليه ٤٫٤٩ بالأسود و٤٫٦٨ بالأبيض، وسطحه
 * المشتَقّ يعكس الترتيب، فلا يوجد لون نصّ واحد يعبر على الاثنين. المسألة
 * حسابية لا خطأ في الحساب: الخلفيات متوسطة السطوع منطقة ميّتة.
 *
 * فيُدفع لون الخلفية نفسه إلى أقرب درجة تحمل نصّاً، بدل أن يُبحَث عن نصّ
 * غير موجود. التاجر يرى لوناً قريباً من اختياره، ولا يرى متجراً لا يُقرأ.
 *
 * @param string $page    لون الخلفية المطلوب.
 * @param float  $minimum حدّ التباين المطلوب للنص العادي.
 * @return string
 */
function matjar_pro_usable_ground( $page, $minimum = 4.5 ) {
	$dark = matjar_pro_relative_luminance( $page ) <= 0.35;

	for ( $i = 0; $i <= 100; $i += 3 ) {
		$candidate = 0 === $i
			? strtoupper( $page )
			: ( $dark ? matjar_pro_darken( $page, $i ) : matjar_pro_lighten( $page, $i ) );

		$surface = $dark ? matjar_pro_lighten( $candidate, 7 ) : '#FFFFFF';

		// يجب أن يعبر لونُ نصٍّ واحد على الخلفية وعلى السطح معاً: نصّ يعبر
		// على الأرضية ويسقط على البطاقة لا يحلّ شيئاً.
		foreach ( array( '#000000', '#FFFFFF' ) as $candidate_text ) {
			// والطبقات الشفّافة كذلك: كتلة ملوّنة بشفافية ٠٫٠٨ فوق السطح
			// تُزيح سطوعه قليلاً، وهذا القليل يكفي لإسقاط زوج عند الحدّ.
			$tint = matjar_pro_blend( $candidate_text, $surface );

			if ( matjar_pro_contrast_ratio( $candidate_text, $candidate ) >= $minimum
				&& matjar_pro_contrast_ratio( $candidate_text, $surface ) >= $minimum
				&& matjar_pro_contrast_ratio( $candidate_text, $tint ) >= $minimum ) {
				return $candidate;
			}
		}
	}

	return $dark ? '#0B0F14' : '#FFFFFF';
}

/**
 * يبني الرموز المحيّدة من لون علامة التاجر ولون خلفيته.
 *
 * التاجر يختار لونين فقط — علامته وخلفية صفحاته — ويُشتَقّ منهما ما يبقى.
 * كل مشتَقّ يُفحَص مقابل كل سطح قد يظهر عليه لا مقابل واحد: النص يظهر على
 * الصفحة وعلى البطاقة، وفرقهما يكفي لإسقاط زوج تحت الحدّ.
 *
 * @param string $primary لون العلامة.
 * @param string $page    لون خلفية الصفحة.
 * @return array
 */
function matjar_pro_derive_neutrals( $primary, $page ) {
	// أوّل خطوة: أن تكون الأرضية قادرة على حمل نصّ أصلاً.
	$page = matjar_pro_usable_ground( $page );
	$dark = matjar_pro_relative_luminance( $page ) <= 0.35;

	// الأسطح ورقة أفتح (أو أغمق) من الصفحة بقليل، فيتمايز الكرت عن الأرضية.
	$surface = $dark ? matjar_pro_lighten( $page, 7 ) : '#FFFFFF';
	$grounds = array( $page, $surface );

	// العناوين أكبر ما يُقرأ في الصفحة، ولا يكفيها الحدّ الأدنى.
	$ink = matjar_pro_ensure_contrast( $primary, $grounds, 7.0 );

	// النص والنص الباهت: من الحبر، مخفَّفان نحو الخلفية ثم مُصحَّحان.
	$text  = matjar_pro_ensure_contrast(
		$dark ? matjar_pro_darken( $ink, 25 ) : matjar_pro_lighten( $ink, 25 ),
		$grounds,
		4.5
	);
	$muted = matjar_pro_ensure_contrast(
		$dark ? matjar_pro_darken( $ink, 40 ) : matjar_pro_lighten( $ink, 40 ),
		$grounds,
		4.5
	);

	// الحدود رسم واجهة لا نص: حدّها ٣:١ مقابل السطح الذي تُرسم عليه.
	$border = matjar_pro_ensure_contrast(
		$dark ? matjar_pro_lighten( $page, 18 ) : matjar_pro_darken( $page, 12 ),
		$surface,
		3.0,
		3
	);

	// حدّ الحقول أقوى من الحدّ الزخرفي: وحده يُعلِم الزائر أين يكتب.
	$field = matjar_pro_ensure_contrast(
		$dark ? matjar_pro_lighten( $page, 32 ) : matjar_pro_darken( $page, 38 ),
		$surface,
		3.0,
		3
	);

	// السطح المعكوس غامق دائماً أيّاً كانت اللوحة، ونصّه يُصحَّح فوقه.
	$inverse     = $dark ? matjar_pro_lighten( $page, 6 ) : $ink;
	$inverse_ink = matjar_pro_ensure_contrast( '#FFFFFF', $inverse, 4.5 );

	return array(
		'ink'         => $ink,
		'inverse'     => $inverse,
		'inverse-ink' => $inverse_ink,
		'bg'      => strtoupper( $page ),
		'surface' => $surface,
		'border'  => $border,
		'field'   => $field,
		'text'    => $text,
		'muted'   => $muted,
		'shadow'  => $ink,
	);
}

/**
 * يمزج لوناً بخلفية بنسبة شفافية.
 *
 * الطبقات الملوّنة في القالب شفّافة بنسبة ٠٫٠٨ فوق السطح، وما يقرأه الزائر
 * هو الناتج المُركَّب. فحص اللون الأصلي وحده يُمرّر أزواجاً تفشل فعلاً.
 *
 * @param string $hex   اللون.
 * @param string $over  الخلفية.
 * @param float  $alpha الشفافية.
 * @return string
 */
function matjar_pro_blend( $hex, $over, $alpha = 0.08 ) {
	$a = matjar_pro_hex_to_rgb( $hex );
	$b = matjar_pro_hex_to_rgb( $over );

	return sprintf(
		'#%02X%02X%02X',
		(int) round( $a[0] * $alpha + $b[0] * ( 1 - $alpha ) ),
		(int) round( $a[1] * $alpha + $b[1] * ( 1 - $alpha ) ),
		(int) round( $a[2] * $alpha + $b[2] * ( 1 - $alpha ) )
	);
}

/**
 * يبني كتلة :root بمتغيّرات CSS.
 *
 * تُطبع القيم كقنوات RGB مفصولة بمسافات لأن هذه الصيغة وحدها هي التي تُبقي
 * مُعدِّلات الشفافية في Tailwind (مثل bg-cta/10) عاملة فوق متغيّرات CSS.
 *
 * @return string
 */
function matjar_pro_css_variables() {
	$tokens = matjar_pro_resolved_tokens();
	$fonts = matjar_pro_fonts();
	$key   = matjar_pro_font_key();
	$lines = array();

	foreach ( $tokens as $name => $hex ) {
		$lines[] = sprintf( '--mp-%s:%s;', $name, matjar_pro_hex_to_channels( $hex ) );
	}

	// نسخ «كاملة» من ألوان الزر: theme.json لا يقبل قنوات RGB وحدها، فيحتاج
	// قيمة لون جاهزة. هكذا يتبع زر المحرّر اختيار التاجر بدل لون مثبّت.
	$lines[] = '--mp-cta-solid:rgb(var(--mp-cta));';
	$lines[] = '--mp-cta-fg-solid:rgb(var(--mp-cta-fg));';

	$lines[] = sprintf( '--mp-font-sans:%s;', $fonts[ $key ]['stack'] );

	return ':root{' . implode( '', $lines ) . '}';
}

/**
 * يبني قواعد @font-face للعائلة المختارة فقط.
 *
 * @return string
 */
function matjar_pro_font_faces_css() {
	$fonts = matjar_pro_fonts();
	$font  = $fonts[ matjar_pro_font_key() ];
	$base  = MATJAR_PRO_URI . '/assets/fonts/';
	$rules = array();

	foreach ( $font['faces'] as $face ) {
		if ( ! file_exists( MATJAR_PRO_DIR . '/assets/fonts/' . $face['file'] ) ) {
			continue;
		}

		$rules[] = sprintf(
			'@font-face{font-family:"%1$s";font-style:normal;font-weight:%2$s;font-display:swap;src:url("%3$s") format("woff2");unicode-range:%4$s;}',
			$font['family'],
			$face['weight'],
			esc_url( $base . $face['file'] ),
			$face['range']
		);
	}

	return implode( '', $rules );
}

/**
 * يعيد ملفات الخط التي تستحق preload (ما يُرسم فوق الطيّة).
 *
 * @return string[] عناوين كاملة.
 */
function matjar_pro_font_preloads() {
	$fonts = matjar_pro_fonts();
	$urls  = array();

	foreach ( $fonts[ matjar_pro_font_key() ]['faces'] as $face ) {
		if ( empty( $face['preload'] ) ) {
			continue;
		}

		if ( ! file_exists( MATJAR_PRO_DIR . '/assets/fonts/' . $face['file'] ) ) {
			continue;
		}

		$urls[] = MATJAR_PRO_URI . '/assets/fonts/' . $face['file'];
	}

	return $urls;
}
