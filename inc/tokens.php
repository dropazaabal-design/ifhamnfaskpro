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
	return array(
		'trust'  => array(
			'label'  => __( 'ثقة — الافتراضي', 'matjar-pro' ),
			'tokens' => array(
				'ink'       => '#0F1E33',
				'bg'        => '#FAF9F7',
				'surface'   => '#FFFFFF',
				'border'    => '#E7E5E1',
				'text'      => '#4B5563',
				'muted'     => '#6B7280',
				'cta'       => '#C2410C',
				'cta-hover' => '#9A3412',
				'accent'     => '#EA580C',
				'accent-ink' => '#F97316',
				'amber'      => '#F59E0B',
				'sale'       => '#B91C1C',
				'success'    => '#15803D',
				'info'       => '#0E7490',
			),
		),
		'luxury' => array(
			'label'  => __( 'فخامة', 'matjar-pro' ),
			'tokens' => array(
				'ink'       => '#14110E',
				'bg'        => '#FAF8F4',
				'surface'   => '#FFFFFF',
				'border'    => '#E8E2D8',
				'text'      => '#4A4034',
				'muted'     => '#6B5F50',
				'cta'       => '#7A611F',
				'cta-hover' => '#5C4917',
				'accent'     => '#B8860B',
				'accent-ink' => '#D4AF37',
				'amber'      => '#D4AF37',
				'sale'      => '#9B2226',
				'success'   => '#15803D',
				'info'      => '#0E7490',
			),
		),
		'vivid'  => array(
			'label'  => __( 'حيوي — أزياء وشباب', 'matjar-pro' ),
			'tokens' => array(
				'ink'       => '#111827',
				'bg'        => '#FDF9FB',
				'surface'   => '#FFFFFF',
				'border'    => '#EFE6EB',
				'text'      => '#4B5563',
				'muted'     => '#6B7280',
				'cta'       => '#BE185D',
				'cta-hover' => '#9D174D',
				'accent'     => '#EC4899',
				'accent-ink' => '#F472B6',
				'amber'      => '#F59E0B',
				'sale'      => '#B91C1C',
				'success'   => '#15803D',
				'info'      => '#0E7490',
			),
		),
		'calm'   => array(
			'label'  => __( 'هادئ — عناية وتجميل', 'matjar-pro' ),
			'tokens' => array(
				'ink'       => '#1F2937',
				'bg'        => '#F8F7F4',
				'surface'   => '#FFFFFF',
				'border'    => '#E5E3DD',
				'text'      => '#4B5563',
				'muted'     => '#6B7280',
				'cta'       => '#0F766E',
				'cta-hover' => '#115E59',
				'accent'     => '#0D9488',
				'accent-ink' => '#2DD4BF',
				'amber'      => '#D6A77A',
				'sale'      => '#B91C1C',
				'success'   => '#15803D',
				'info'      => '#0E7490',
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
		'burnt'   => array(
			'label' => __( 'برتقالي محروق — نص أبيض', 'matjar-pro' ),
			'cta'   => '#C2410C',
			'hover' => '#9A3412',
		),
		'amber'   => array(
			'label' => __( 'كهرماني — نص كحلي', 'matjar-pro' ),
			'cta'   => '#F59E0B',
			'hover' => '#D97706',
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
			'label'  => __( 'IBM Plex Sans Arabic — أعلى مقروئية (‎187KB / 6 ملفات)', 'matjar-pro' ),
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
			'label'  => __( 'Cairo متغيّر — الأخف (‎63KB / ملفان)', 'matjar-pro' ),
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
 * يعيد مجموعة الرموز النهائية بعد دمج اللوحة المختارة ونمط الزر وأي تخصيص.
 *
 * @return array<string,string> خريطة اسم الرمز إلى لون سِتّ عشري.
 */
function matjar_pro_resolved_tokens() {
	$palettes = matjar_pro_palettes();
	$choice   = matjar_pro_mod( 'matjar_pro_palette' );

	if ( ! isset( $palettes[ $choice ] ) ) {
		$choice = 'trust';
	}

	$tokens = $palettes[ $choice ]['tokens'];

	// نمط زر الشراء يعلو على لون اللوحة عند اختياره صريحاً.
	$styles = matjar_pro_cta_styles();
	$style  = matjar_pro_mod( 'matjar_pro_cta_style' );

	if ( isset( $styles[ $style ] ) && '' !== $styles[ $style ]['cta'] ) {
		$tokens['cta']       = $styles[ $style ]['cta'];
		$tokens['cta-hover'] = $styles[ $style ]['hover'];
	}

	// لون مخصّص كامل من التاجر، إن أدخله.
	$custom = matjar_pro_mod( 'matjar_pro_cta_custom' );

	if ( '' !== $custom && preg_match( '/^#[0-9a-fA-F]{6}$/', $custom ) ) {
		$tokens['cta']       = $custom;
		$tokens['cta-hover'] = matjar_pro_darken( $custom, 14 );
	}

	// هنا تُفرض المقروئية: لون نص الزر ليس خياراً بل نتيجة حساب.
	$tokens['cta-fg'] = matjar_pro_readable_foreground( $tokens['cta'], '#FFFFFF', $tokens['ink'] );

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
 * يبني كتلة :root بمتغيّرات CSS.
 *
 * تُطبع القيم كقنوات RGB مفصولة بمسافات لأن هذه الصيغة وحدها هي التي تُبقي
 * مُعدِّلات الشفافية في Tailwind (مثل bg-cta/10) عاملة فوق متغيّرات CSS.
 *
 * @return string
 */
function matjar_pro_css_variables() {
	$tokens = matjar_pro_resolved_tokens();
	$fonts  = matjar_pro_fonts();
	$key    = matjar_pro_mod( 'matjar_pro_font' );

	if ( ! isset( $fonts[ $key ] ) ) {
		$key = 'plex';
	}

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
	$key   = matjar_pro_mod( 'matjar_pro_font' );

	if ( ! isset( $fonts[ $key ] ) ) {
		$key = 'plex';
	}

	$font  = $fonts[ $key ];
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
	$key   = matjar_pro_mod( 'matjar_pro_font' );

	if ( ! isset( $fonts[ $key ] ) ) {
		$key = 'plex';
	}

	$urls = array();

	foreach ( $fonts[ $key ]['faces'] as $face ) {
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
