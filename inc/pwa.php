<?php
/**
 * تطبيق ويب تقدّمي — البيان وعامل الخدمة.
 *
 * القرار الهندسي: البيان (manifest) مجاني تقريباً — ملف JSON صغير وبضعة
 * وسوم meta، بلا سطر JavaScript واحد على المسار الحرج. وعامل الخدمة
 * (service worker) يعمل في خيط منفصل ولا يدخل في LCP إطلاقاً، ويُسجَّل
 * بعد load حتى لا ينافس الأصول الحرجة على النطاق.
 *
 * الخطر كلّه في ما يُخزَّن لا في الحجم: متجرٌ يُخزّن صفحة سلة أو سعراً
 * قديماً كارثة تجارية لا مشكلة أداء. لذلك عامل الخدمة هنا محافظ عمداً:
 *
 *   - لا يعترض إلا طلبات GET من الأصل نفسه.
 *   - لا يقترب من السلة والدفع والحساب ولوحة التحكّم وwp-json وwc-ajax.
 *   - صفحات HTML: الشبكة أولاً دائماً. لا تُخزَّن إلا استجابة زائر مجهول
 *     (بلا كعكة دخول أو سلة)، ولا تُقدَّم النسخة المخزّنة إلا عند انقطاع
 *     الشبكة. فلا يرى زائرٌ متّصلٌ سعراً قديماً أبداً.
 *   - الأصول الثابتة: المخزّن أولاً مع تحديث في الخلفية.
 *
 * ويُقدَّم عامل الخدمة من جذر الموقع عبر قاعدة إعادة كتابة، لا من مجلّد
 * القالب: نطاق عامل الخدمة هو مجلّده، فلو قُدِّم من wp-content لما تحكّم
 * في الموقع كلّه.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * هل ميزة التطبيق التقدّمي مُفعَّلة.
 *
 * @return bool
 */
function matjar_pro_pwa_enabled() {
	return (bool) matjar_pro_mod( 'matjar_pro_pwa_enabled' );
}

/**
 * عنوان أحد موارد التطبيق.
 *
 * القواعد الجميلة (‎/matjar-pro-sw.js‎) تعمل فقط حين يكون تركيب الروابط
 * الدائمة مفعّلاً. مع التركيب «العادي» لا تُطبَّق قاعدة إعادة كتابة واحدة،
 * فيرجع الملفّان 404 بينما يظلّ وسم البيان مطبوعاً في الرأس: ميزة ميتة
 * بلا رسالة خطأ. لذلك نعود هنا إلى متغيّر استعلام على الجذر.
 *
 * ومسار العنوان يبقى «/» في الحالتين، فنطاق عامل الخدمة يظلّ الموقع كلّه —
 * ويؤكّده ترويسة Service-Worker-Allowed التي نرسلها مع الملفّ.
 *
 * @param string $what sw أو manifest أو offline.
 * @return string
 */
function matjar_pro_pwa_url( $what ) {
	$pretty = array(
		'sw'       => 'matjar-pro-sw.js',
		'manifest' => 'matjar-pro-manifest.json',
		'offline'  => 'matjar-pro-offline/',
	);

	if ( ! isset( $pretty[ $what ] ) ) {
		return '';
	}

	if ( get_option( 'permalink_structure' ) ) {
		return home_url( '/' . $pretty[ $what ] );
	}

	return add_query_arg( 'matjar_pro_pwa', $what, home_url( '/' ) );
}

/**
 * يسجّل مسارَي البيان وعامل الخدمة في جذر الموقع.
 */
function matjar_pro_pwa_rewrites() {
	add_rewrite_rule( '^matjar-pro-sw\.js$', 'index.php?matjar_pro_pwa=sw', 'top' );
	add_rewrite_rule( '^matjar-pro-manifest\.json$', 'index.php?matjar_pro_pwa=manifest', 'top' );
	add_rewrite_rule( '^matjar-pro-offline/?$', 'index.php?matjar_pro_pwa=offline', 'top' );
}
add_action( 'init', 'matjar_pro_pwa_rewrites' );

/**
 * يضيف متغيّر الاستعلام الذي تقرؤه القواعد أعلاه.
 *
 * @param array $vars المتغيّرات.
 * @return array
 */
function matjar_pro_pwa_query_var( $vars ) {
	$vars[] = 'matjar_pro_pwa';

	return $vars;
}
add_filter( 'query_vars', 'matjar_pro_pwa_query_var' );

/**
 * يُحدّث قواعد إعادة الكتابة عند تفعيل القالب.
 *
 * بلا هذا لا يعمل المساران إلا بعد حفظ الروابط الدائمة يدوياً، وهو ما لا
 * يخطر لتاجر.
 */
function matjar_pro_pwa_flush() {
	matjar_pro_pwa_rewrites();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'matjar_pro_pwa_flush' );

/**
 * أيقونة التطبيق: اختيار التاجر، أو أيقونة الموقع، أو لا شيء.
 *
 * @param int $size المقاس المطلوب.
 * @return string العنوان، أو سلسلة فارغة.
 */
function matjar_pro_pwa_icon( $size = 512 ) {
	$custom = (int) matjar_pro_mod( 'matjar_pro_pwa_icon' );

	if ( $custom ) {
		$src = wp_get_attachment_image_src( $custom, array( $size, $size ) );

		if ( $src ) {
			return $src[0];
		}
	}

	return get_site_icon_url( $size );
}

/**
 * يبني البيان.
 *
 * الألوان من رموز اللوحة نفسها، فشريط النظام في التطبيق المثبّت يطابق
 * المتجر ولا يحتاج إعداداً منفصلاً.
 *
 * @return array
 */
function matjar_pro_pwa_manifest() {
	$tokens = matjar_pro_resolved_tokens();
	$name   = trim( (string) matjar_pro_mod( 'matjar_pro_pwa_name' ) );
	$short  = trim( (string) matjar_pro_mod( 'matjar_pro_pwa_short_name' ) );

	$manifest = array(
		'name'             => '' !== $name ? $name : get_bloginfo( 'name' ),
		'short_name'       => '' !== $short ? $short : get_bloginfo( 'name' ),
		'start_url'        => home_url( '/?utm_source=pwa' ),
		'scope'            => home_url( '/' ),
		'display'          => 'standalone',
		'orientation'      => 'portrait',
		'dir'              => is_rtl() ? 'rtl' : 'ltr',
		'lang'             => get_bloginfo( 'language' ),
		'background_color' => $tokens['bg'],
		'theme_color'      => $tokens['bg'],
		'icons'            => array(),
	);

	foreach ( array( 192, 512 ) as $size ) {
		$icon = matjar_pro_pwa_icon( $size );

		if ( '' === $icon ) {
			continue;
		}

		$manifest['icons'][] = array(
			'src'     => $icon,
			'sizes'   => $size . 'x' . $size,
			'type'    => 'image/png',
			'purpose' => 'any',
		);
	}

	/**
	 * تصفية بيان التطبيق.
	 *
	 * @param array $manifest البيان.
	 */
	return apply_filters( 'matjar_pro_pwa_manifest', $manifest );
}

/**
 * المسارات التي لا يقترب منها عامل الخدمة إطلاقاً.
 *
 * @return string[]
 */
function matjar_pro_pwa_excluded_paths() {
	$paths = array( '/wp-admin', '/wp-login', '/wp-json', '/wp-cron' );

	if ( matjar_pro_has_woocommerce() ) {
		foreach ( array( 'cart', 'checkout', 'myaccount' ) as $page ) {
			$url = wc_get_page_permalink( $page );

			if ( ! $url ) {
				continue;
			}

			$path = wp_parse_url( $url, PHP_URL_PATH );

			if ( $path && '/' !== $path ) {
				$paths[] = untrailingslashit( $path );
			}
		}
	}

	/**
	 * تصفية المسارات المستثناة من التخزين.
	 *
	 * @param string[] $paths المسارات.
	 */
	return array_values( array_unique( apply_filters( 'matjar_pro_pwa_excluded_paths', $paths ) ) );
}

/**
 * يقدّم البيان وعامل الخدمة وصفحة الانقطاع.
 */
function matjar_pro_pwa_serve() {
	$what = get_query_var( 'matjar_pro_pwa' );

	if ( ! $what || ! matjar_pro_pwa_enabled() ) {
		return;
	}

	if ( 'manifest' === $what ) {
		header( 'Content-Type: application/manifest+json; charset=utf-8' );
		header( 'Cache-Control: public, max-age=3600' );
		echo wp_json_encode( matjar_pro_pwa_manifest(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		exit;
	}

	if ( 'sw' === $what ) {
		header( 'Content-Type: text/javascript; charset=utf-8' );
		// عامل الخدمة نفسه لا يُخزَّن: وإلّا بقي القديم يحكم بعد التحديث.
		header( 'Cache-Control: no-cache, must-revalidate' );
		header( 'Service-Worker-Allowed: /' );
		matjar_pro_pwa_service_worker();
		exit;
	}

	if ( 'offline' === $what ) {
		status_header( 200 );
		header( 'Content-Type: text/html; charset=utf-8' );
		get_template_part( 'template-parts/pwa/offline' );
		exit;
	}
}
add_action( 'template_redirect', 'matjar_pro_pwa_serve' );

/**
 * يطبع عامل الخدمة.
 *
 * مكتوب بيد لا بمكتبة: Workbox وحدها أثقل من حزم القالب كلّها مجتمعة،
 * وما نحتاجه ثلاث استراتيجيات لا ثلاثين.
 */
function matjar_pro_pwa_service_worker() {
	$version   = matjar_pro_asset_version( '/assets/css/main.css' );
	$offline   = matjar_pro_pwa_url( 'offline' );
	$precache  = array( $offline );
	$css       = MATJAR_PRO_URI . '/assets/css/main.css';
	$precache[] = $css;

	foreach ( matjar_pro_font_preloads() as $font ) {
		$precache[] = $font['href'];
	}

	/**
	 * تصفية ما يُخزَّن مسبقاً.
	 *
	 * @param string[] $precache العناوين.
	 */
	$precache = apply_filters( 'matjar_pro_pwa_precache', $precache );

	printf(
		"/* Matjar Pro service worker */\nconst MP_VERSION = %s;\nconst MP_SHELL = %s;\nconst MP_OFFLINE = %s;\nconst MP_SKIP = %s;\n",
		wp_json_encode( (string) $version ),
		wp_json_encode( array_values( array_unique( $precache ) ) ),
		wp_json_encode( $offline ),
		wp_json_encode( matjar_pro_pwa_excluded_paths() )
	);

	echo file_get_contents( MATJAR_PRO_DIR . '/assets/js/sw-body.js' ); // phpcs:ignore WordPress.WP.AlternativeFunctions, WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * وسوم رأس الصفحة: البيان وألوان شريط النظام وأيقونة iOS.
 *
 * iOS لا يقرأ البيان لتثبيت التطبيق: يحتاج apple-touch-icon ووسومه
 * الخاصّة. وإغفالها يعني أيقونة مشوّهة على نصف أجهزة السوق الخليجي.
 */
function matjar_pro_pwa_head() {
	if ( ! matjar_pro_pwa_enabled() ) {
		return;
	}

	$tokens = matjar_pro_resolved_tokens();
	$icon   = matjar_pro_pwa_icon( 192 );

	printf(
		'<link rel="manifest" href="%s">' . "\n",
		esc_url( matjar_pro_pwa_url( 'manifest' ) )
	);
	printf( '<meta name="theme-color" content="%s">' . "\n", esc_attr( $tokens['bg'] ) );
	echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";

	printf(
		'<meta name="apple-mobile-web-app-title" content="%s">' . "\n",
		esc_attr( matjar_pro_mod( 'matjar_pro_pwa_short_name' ) ? matjar_pro_mod( 'matjar_pro_pwa_short_name' ) : get_bloginfo( 'name' ) )
	);

	if ( '' !== $icon ) {
		printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $icon ) );
	}
}
add_action( 'wp_head', 'matjar_pro_pwa_head', 3 );

/**
 * يمرّر عنوان عامل الخدمة إلى JavaScript.
 *
 * التسجيل لا يُطبع سطراً مضمّناً في الصفحة: Content-Security-Policy صارمة
 * تمنعه، وهي شائعة في المتاجر التي تمرّ على مدقّق دفع. يذهب العنوان مع
 * بقيّة بيانات القالب، وتتكفّل الحزمة الرئيسية بالتسجيل بعد load.
 *
 * @param array $data البيانات.
 * @return array
 */
function matjar_pro_pwa_script_data( $data ) {
	if ( matjar_pro_pwa_enabled() ) {
		$data['swUrl'] = matjar_pro_pwa_url( 'sw' );
	}

	return $data;
}
add_filter( 'matjar_pro_script_data', 'matjar_pro_pwa_script_data' );
