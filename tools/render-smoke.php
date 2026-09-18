<?php
/**
 * تصيير القوالب مقابل مُحاكٍ خفيف لووردبريس — `npm run smoke`.
 *
 * `php -l` يتحقّق من النحو فقط؛ هذا المُحاكي ينفّذ القوالب فعلاً فيكشف
 * المتغيّرات غير المعرَّفة والدوال المستدعاة بوسائط خاطئة وفروع الشروط
 * التي لا يمرّ عليها الفحص النحوي. يُشغَّل مرتين: بدون ووكومرس ومعه.
 *
 * ليس بديلاً عن تشغيل القالب على موقع حقيقي، لكنه يمنع الأخطاء التي
 * تُكتشف عادةً على الشاشة البيضاء.
 *
 * @package MatjarPro
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'MATJAR_PRO_DIR', dirname( __DIR__ ) );
define( 'MATJAR_PRO_URI', 'https://example.test/wp-content/themes/matjar-pro' );
define( 'MATJAR_PRO_VERSION', '0.1.0' );
define( 'DATE_W3C_STUB', 'Y-m-d\TH:i:sP' );

$GLOBALS['mp_smoke_with_wc'] = (bool) array_intersect(
	array( '--woocommerce', '--funnel', '--card', '--filter', '--account', '--archive', '--product', '--checkout', '--minicart', '--catalog', '--cart' ),
	$argv
);
$GLOBALS['mp_smoke_funnel']  = in_array( '--funnel', $argv, true );
$GLOBALS['mp_smoke_card']    = in_array( '--card', $argv, true );
$GLOBALS['mp_smoke_filter']  = in_array( '--filter', $argv, true );
$GLOBALS['mp_smoke_account'] = in_array( '--account', $argv, true );
$GLOBALS['mp_smoke_archive'] = in_array( '--archive', $argv, true );
$GLOBALS['mp_smoke_product']  = in_array( '--product', $argv, true );
$GLOBALS['mp_smoke_variable'] = in_array( '--variable', $argv, true );
$GLOBALS['mp_smoke_checkout'] = in_array( '--checkout', $argv, true );
$GLOBALS['mp_smoke_minicart'] = in_array( '--minicart', $argv, true );
$GLOBALS['mp_smoke_cartpage'] = in_array( '--cart', $argv, true );
$GLOBALS['mp_smoke_funnel']   = $GLOBALS['mp_smoke_funnel'] || $GLOBALS['mp_smoke_checkout'];

// الخطّافات تُطلق فقط في الأنماط التي تحتاج دورة ووكومرس الحقيقية.
$GLOBALS['mp_smoke_hooks'] = $GLOBALS['mp_smoke_product']
	|| $GLOBALS['mp_smoke_checkout']
	|| $GLOBALS['mp_smoke_cartpage']
	|| $GLOBALS['mp_smoke_minicart'];

// --state=filter_size=m&max_price=200 — حالة الفلترة كما تصل من الرابط.
$GLOBALS['mp_smoke_state_given'] = false;

foreach ( $argv as $mp_arg ) {
	if ( 0 === strpos( $mp_arg, '--state=' ) ) {
		$GLOBALS['mp_smoke_state_given'] = true;
		parse_str( substr( $mp_arg, 8 ), $mp_state );

		if ( array_key_exists( 'cart', $mp_state ) ) {
			$GLOBALS['mp_smoke_cart'] = $mp_state['cart'];
			unset( $mp_state['cart'] );
		}

		$_GET = array_merge( $_GET, $mp_state );
	}
}

/* ---------- مُحاكي ووردبريس ---------- */

function __( $t, $d = '' ) { return $t; }                      // phpcs:ignore
function _x( $t, $c = '', $d = '' ) { return $t; }             // phpcs:ignore
function _n( $s, $p, $n, $d = '' ) { return 1 === (int) $n ? $s : $p; }      // phpcs:ignore
function _nx( $s, $p, $n, $c = '', $d = '' ) { return 1 === (int) $n ? $s : $p; }              // phpcs:ignore
function esc_html__( $t, $d = '' ) { return $t; }              // phpcs:ignore
function esc_attr__( $t, $d = '' ) { return $t; }              // phpcs:ignore
function esc_html_e( $t, $d = '' ) { echo htmlspecialchars( $t, ENT_QUOTES ); } // phpcs:ignore
function esc_attr_e( $t, $d = '' ) { echo htmlspecialchars( $t, ENT_QUOTES ); } // phpcs:ignore
function esc_html( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES ); } // phpcs:ignore
function esc_attr( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES ); } // phpcs:ignore
function esc_url( $u ) { return htmlspecialchars( (string) $u, ENT_QUOTES ); }  // phpcs:ignore
function esc_url_raw( $u ) { return $u; }                      // phpcs:ignore
function esc_textarea( $t ) { return htmlspecialchars( (string) $t, ENT_QUOTES ); } // phpcs:ignore
function wp_kses_post( $t ) { return $t; }                     // phpcs:ignore
function wp_strip_all_tags( $t ) { return strip_tags( (string) $t ); } // phpcs:ignore
function sanitize_text_field( $t ) { return is_string( $t ) ? trim( strip_tags( $t ) ) : $t; }             // phpcs:ignore
function wp_unslash( $v ) { return is_array( $v ) ? array_map( 'wp_unslash', $v ) : stripslashes( (string) $v ); }  // phpcs:ignore
function sanitize_textarea_field( $t ) { return $t; }          // phpcs:ignore
function sanitize_hex_color( $t ) { return $t; }               // phpcs:ignore
function absint( $v ) { return abs( (int) $v ); }              // phpcs:ignore
/*
 * نظام الخطّافات: التسجيل يعمل دائماً، والإطلاق فقط عندما يُطلب (--product،
 * --checkout). بهذا تبقى مخارج الأنماط السابقة كما هي بايتاً ببايت، ويُختبر
 * في نمط المنتج ترتيب الأولويات الحقيقي الذي كتبناه في inc/wc-product.php.
 */
$GLOBALS['mp_hooks'] = array();

function add_action( $hook, $cb, $priority = 10, $args = 1 ) { // phpcs:ignore
	$GLOBALS['mp_hooks'][ $hook ][ (int) $priority ][] = $cb;
}
function add_filter( $hook, $cb, $priority = 10, $args = 1 ) { // phpcs:ignore
	add_action( $hook, $cb, $priority, $args );
}
function remove_action( $hook, $cb, $priority = 10 ) {         // phpcs:ignore
	$bucket = &$GLOBALS['mp_hooks'][ $hook ][ (int) $priority ];

	if ( empty( $bucket ) ) {
		return false;
	}

	foreach ( $bucket as $i => $registered ) {
		if ( $registered === $cb ) {
			unset( $bucket[ $i ] );

			return true;
		}
	}

	return false;
}
function remove_filter( $hook, $cb, $priority = 10 ) { return remove_action( $hook, $cb, $priority ); } // phpcs:ignore
function has_action( $hook, $cb = false ) {                    // phpcs:ignore
	foreach ( $GLOBALS['mp_hooks'][ $hook ] ?? array() as $priority => $bucket ) {
		foreach ( $bucket as $registered ) {
			if ( false === $cb || $registered === $cb ) {
				return false === $cb ? true : $priority;
			}
		}
	}

	return false;
}
function has_filter( $hook, $cb = false ) { return has_action( $hook, $cb ); } // phpcs:ignore
function mp_smoke_callbacks( $hook ) {                         // phpcs:ignore
	if ( empty( $GLOBALS['mp_smoke_hooks'] ) || empty( $GLOBALS['mp_hooks'][ $hook ] ) ) {
		return array();
	}

	$by_priority = $GLOBALS['mp_hooks'][ $hook ];
	ksort( $by_priority );
	$out = array();

	foreach ( $by_priority as $bucket ) {
		foreach ( $bucket as $cb ) {
			if ( ! is_callable( $cb ) ) {
				throw new RuntimeException( "خطّاف غير قابل للنداء: {$hook} · " . ( is_string( $cb ) ? $cb : gettype( $cb ) ) );
			}

			$out[] = $cb;
		}
	}

	return $out;
}
function do_action( $hook, ...$args ) {                        // phpcs:ignore
	foreach ( mp_smoke_callbacks( $hook ) as $cb ) {
		$cb( ...$args );
	}
}
function apply_filters( $hook, $value, ...$args ) {            // phpcs:ignore
	foreach ( mp_smoke_callbacks( $hook ) as $cb ) {
		$value = $cb( $value, ...$args );
	}

	return $value;
}
function wp_parse_args( $a, $d = array() ) { return array_merge( $d, (array) $a ); } // phpcs:ignore
function get_theme_mod( $k, $d = false ) { return $GLOBALS['mp_smoke_mods'][ $k ] ?? $d; } // phpcs:ignore
function get_option( $k, $d = false ) { return $d; }           // phpcs:ignore
function home_url( $p = '/' ) { return 'https://example.test' . $p; } // phpcs:ignore
function get_permalink( $id = 0 ) { return 'https://example.test/page/' . (int) $id; } // phpcs:ignore
function get_template_directory() { return MATJAR_PRO_DIR; }   // phpcs:ignore
function get_template_directory_uri() { return MATJAR_PRO_URI; } // phpcs:ignore
function language_attributes() { echo 'lang="ar" dir="rtl"'; } // phpcs:ignore
function bloginfo( $s = '' ) { echo 'charset' === $s ? 'UTF-8' : 'متجر تجريبي'; } // phpcs:ignore
function get_bloginfo( $s = '' ) { return 'متجر تجريبي'; }     // phpcs:ignore
function wp_head() { echo "<!-- wp_head -->\n"; }              // phpcs:ignore
function wp_footer() { echo "<!-- wp_footer -->\n"; }          // phpcs:ignore
function wp_body_open() {}                                     // phpcs:ignore
function body_class( $c = '' ) { echo 'class="' . esc_attr( is_array( $c ) ? implode( ' ', $c ) : $c ) . '"'; } // phpcs:ignore
function is_rtl() { return true; }                             // phpcs:ignore
function has_custom_logo() { return false; }                   // phpcs:ignore
function the_custom_logo() {}                                  // phpcs:ignore
function has_nav_menu( $l ) { return in_array( $l, array( 'primary', 'footer', 'legal' ), true ); } // phpcs:ignore
function wp_nav_menu( $a = array() ) { printf( '<ul class="%s"><li><a href="#">عنصر</a></li></ul>', esc_attr( $a['menu_class'] ?? '' ) ); } // phpcs:ignore
function number_format_i18n( $n ) { return (string) $n; }      // phpcs:ignore
function wp_date( $f, $t = null ) {                            // phpcs:ignore
	// ووردبريس العربي يُعرِّب أسماء الشهور؛ الاسم الإنجليزي هنا يُضلّل عن
	// القالب لا عنه، فتُستبدل الشهور في المعاينة.
	$months = array(
		'January' => 'يناير', 'February' => 'فبراير', 'March' => 'مارس',
		'April' => 'أبريل', 'May' => 'مايو', 'June' => 'يونيو',
		'July' => 'يوليو', 'August' => 'أغسطس', 'September' => 'سبتمبر',
		'October' => 'أكتوبر', 'November' => 'نوفمبر', 'December' => 'ديسمبر',
	);

	return str_replace( array_keys( $months ), array_values( $months ), gmdate( $f, $t ?? time() ) );
}
function current_time( $t ) { return time(); }                 // phpcs:ignore
function is_active_sidebar( $id ) { return false; }            // phpcs:ignore
function dynamic_sidebar( $id ) {}                             // phpcs:ignore
function register_sidebar( $a ) {}                             // phpcs:ignore
/** مُصطلح تصنيف: الكود يتحقّق من instanceof WP_Term، فلا يكفي stdClass. */
class WP_Term { // phpcs:ignore
	/** @var int */    public $term_id = 0;
	/** @var string */ public $name = '';
	/** @var string */ public $slug = '';
	/** @var int */    public $count = 0;
	/** @param array $props الخصائص. */
	public function __construct( $props = array() ) {
		foreach ( $props as $key => $value ) {
			$this->$key = $value;
		}
	}
}
/**
 * ووردبريس يقبل صيغتين: add_query_arg( array, url ) و
 * add_query_arg( key, value, url ). المُحاكي يجب أن يقبل الاثنتين، وإلا
 * ضاع الرابط وظهرت القيمة وحدها — وهو ما حدث فعلاً في أول تشغيل.
 */
function add_query_arg( ...$a ) {                               // phpcs:ignore
	if ( is_array( $a[0] ) ) {
		$pairs = $a[0];
		$url   = isset( $a[1] ) ? (string) $a[1] : '';
	} else {
		$pairs = array( (string) $a[0] => isset( $a[1] ) ? $a[1] : '' );
		$url   = isset( $a[2] ) ? (string) $a[2] : '';
	}

	$parts = explode( '?', $url, 2 );
	$args  = array();

	if ( isset( $parts[1] ) ) {
		parse_str( $parts[1], $args );
	}

	$args = array_merge( $args, $pairs );

	return $parts[0] . ( empty( $args ) ? '' : '?' . http_build_query( $args ) );
}
function remove_query_arg( $keys, $url = '' ) {                 // phpcs:ignore
	$parts = explode( '?', (string) $url, 2 );
	$args  = array();

	if ( isset( $parts[1] ) ) {
		parse_str( $parts[1], $args );
	}

	foreach ( (array) $keys as $key ) {
		unset( $args[ $key ] );
	}

	return $parts[0] . ( empty( $args ) ? '' : '?' . http_build_query( $args ) );
}
function wp_json_encode( $v, $flags = 0, $depth = 512 ) { return json_encode( $v, $flags | JSON_UNESCAPED_UNICODE ); }   // phpcs:ignore
function _wp_specialchars( $t, $q = ENT_NOQUOTES, $c = '', $double = false ) { return htmlspecialchars( (string) $t, ENT_QUOTES, 'UTF-8', $double ); } // phpcs:ignore
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }      // phpcs:ignore
function wp_parse_str( $string, &$array ) { parse_str( (string) $string, $array ); }          // phpcs:ignore
function sanitize_key( $k ) { return preg_replace( '/[^a-z0-9_\\-]/', '', strtolower( (string) $k ) ); } // phpcs:ignore
function sanitize_title( $t ) { return preg_replace( '/[^\\p{L}\\p{N}_\\-]/u', '-', strtolower( (string) $t ) ); }  // phpcs:ignore
function sanitize_html_class( $c ) { return preg_replace( '/[^A-Za-z0-9_\\-]/', '', (string) $c ); }     // phpcs:ignore
function taxonomy_exists( $t ) { return in_array( $t, array( 'pa_size', 'pa_color' ), true ); }           // phpcs:ignore
function get_term_by( $field, $value, $taxonomy ) {             // phpcs:ignore
	foreach ( mp_smoke_terms() as $tax => $terms ) {
		if ( '' !== (string) $taxonomy && $tax !== $taxonomy ) {
			continue;
		}

		foreach ( $terms as $term ) {
			if ( $term[ 'slug' === $field ? 'slug' : 'name' ] === (string) $value ) {
				return new WP_Term(
					array(
						'term_id' => $term['id'],
						'name'    => $term['name'],
						'slug'    => $term['slug'],
						'count'   => $term['count'],
					)
				);
			}
		}
	}

	return false;
}
function get_term_meta( $id, $key, $single = false ) {          // phpcs:ignore
	if ( 'matjar_pro_swatch' !== $key ) {
		return '';
	}

	foreach ( mp_smoke_terms() as $terms ) {
		foreach ( $terms as $term ) {
			if ( (int) $term['id'] === (int) $id ) {
				return $term['swatch'];
			}
		}
	}

	return '';
}
function is_post_type_archive( $t = '' ) { return empty( $GLOBALS['mp_smoke_product'] ) && empty( $GLOBALS['mp_smoke_checkout'] ); }  // phpcs:ignore
function is_page( $p = '' ) { return false; }                               // phpcs:ignore
function is_product_category() { return false; }                            // phpcs:ignore
function is_product_tag() { return false; }                                 // phpcs:ignore
function get_post_type_archive_link( $t ) { return 'https://example.test/shop'; }             // phpcs:ignore
function get_queried_object() { return null; }                              // phpcs:ignore
function get_transient( $k ) { return 'matjar_pro_price_bounds' === $k ? array( 'min' => 45.0, 'max' => 890.0 ) : false; } // phpcs:ignore
function set_transient( $k, $v, $t = 0 ) { return true; }                   // phpcs:ignore
function delete_transient( $k ) { return true; }                            // phpcs:ignore
function get_query_var( $v, $d = '' ) { return $d; }                        // phpcs:ignore
function get_current_user_id() { return 3; }                                // phpcs:ignore
function is_user_logged_in() { return true; }                               // phpcs:ignore
function wp_lostpassword_url() { return 'https://example.test/lost'; }      // phpcs:ignore
function wp_nonce_field( $a = '', $n = '' ) { echo '<input type="hidden" name="' . esc_attr( $n ) . '" value="nonce">'; } // phpcs:ignore
function is_wp_error( $t ) { return false; }                   // phpcs:ignore
/**
 * مُصطلحات الخيارات التجريبية: مصدر واحد تقرأه get_terms و get_term_by
 * و get_term_meta، فلا تتعارض المعرّفات ولا الألوان.
 *
 * @return array
 */
function mp_smoke_terms() {                                     // phpcs:ignore
	return array(
		'pa_size'  => array(
			array( 'id' => 201, 'slug' => 's',  'name' => 'S',  'count' => 3, 'swatch' => '' ),
			array( 'id' => 202, 'slug' => 'm',  'name' => 'M',  'count' => 4, 'swatch' => '' ),
			array( 'id' => 203, 'slug' => 'l',  'name' => 'L',  'count' => 4, 'swatch' => '' ),
			array( 'id' => 204, 'slug' => 'xl', 'name' => 'XL', 'count' => 1, 'swatch' => '' ),
		),
		'pa_color' => array(
			array( 'id' => 211, 'slug' => 'black', 'name' => 'أسود', 'count' => 4, 'swatch' => '#1F2430' ),
			array( 'id' => 212, 'slug' => 'beige', 'name' => 'بيج',  'count' => 4, 'swatch' => '#D9C7A7' ),
			array( 'id' => 213, 'slug' => 'navy',  'name' => 'كحلي', 'count' => 3, 'swatch' => '#243B5A' ),
			array( 'id' => 214, 'slug' => 'olive', 'name' => 'زيتي', 'count' => 0, 'swatch' => '#6B7A44' ),
		),
	);
}
function get_terms( $a = array() ) {                            // phpcs:ignore
	$taxonomy = isset( $a['taxonomy'] ) ? $a['taxonomy'] : '';
	$sets     = mp_smoke_terms();

	if ( ! isset( $sets[ $taxonomy ] ) ) {
		return array();
	}

	$out = array();

	foreach ( $sets[ $taxonomy ] as $term ) {
		$out[] = new WP_Term(
			array(
				'term_id' => $term['id'],
				'name'    => $term['name'],
				'slug'    => $term['slug'],
				'count'   => $term['count'],
			)
		);
	}

	return $out;
}
function get_term_link( $t ) { return 'https://example.test/cat'; } // phpcs:ignore
/**
 * خريطة المرفقات التجريبية: معرّف ← ملف صورة، بدل باقي القسمة، حتى تكون
 * صور المنتج ومعرضه متّسقة بصرياً.
 *
 * @param int $id معرّف المرفق.
 * @return int
 */
function mp_smoke_image( $id ) {                               // phpcs:ignore
	$map = array( 12 => 1, 13 => 2, 14 => 3, 15 => 4, 16 => 5, 17 => 6 );

	return $map[ (int) $id ] ?? ( ( (int) $id % 6 ) + 1 );
}
function wp_get_attachment_image( $id, $size = '', $icon = false, $attr = array() ) { // phpcs:ignore
	$out = '<img src="https://example.test/i-' . mp_smoke_image( $id ) . '.jpg"';
	foreach ( (array) $attr as $k => $v ) {
		$out .= sprintf( ' %s="%s"', $k, esc_attr( $v ) );
	}
	return $out . ' width="1600" height="900">';
}
function get_search_query() { return ''; }                     // phpcs:ignore
function is_front_page() { return empty( $GLOBALS['mp_smoke_product'] ) && empty( $GLOBALS['mp_smoke_checkout'] ); } // phpcs:ignore
function is_singular( $t = '' ) { return ! empty( $GLOBALS['mp_smoke_product'] ); }   // phpcs:ignore
function is_home() { return false; }                           // phpcs:ignore
function is_archive() { return false; }                        // phpcs:ignore
function is_search() { return false; }                         // phpcs:ignore
function comments_open() { return false; }                     // phpcs:ignore
/**
 * ينفّذ ما ينفّذه WC_Query على معطيات تجريبية: يختار المنتجات المطابقة
 * لمتغيّرات الفلترة في الرابط. الغرض أن يكون ما يُصيَّر متّسقاً مع الشرائح
 * والعدّاد، لا أن يُحاكى محرّك استعلامات ووكومرس.
 *
 * @return int[] فهارس المنتجات المطابقة.
 */
function mp_smoke_matching_products() {                        // phpcs:ignore
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$sizes   = array_filter( explode( ',', (string) ( $_GET['filter_size'] ?? '' ) ) );      // phpcs:ignore
	$colours = array_filter( explode( ',', (string) ( $_GET['filter_color'] ?? '' ) ) );     // phpcs:ignore
	$min     = isset( $_GET['min_price'] ) ? (float) $_GET['min_price'] : null;              // phpcs:ignore
	$max     = isset( $_GET['max_price'] ) ? (float) $_GET['max_price'] : null;              // phpcs:ignore
	$out     = array();

	foreach ( array_keys( WC_Product::$demo ) as $i ) {
		$product = new WC_Product( false, $i );

		// منتج بلا مقاسات لا يُستبعد بفلتر المقاس إلّا إن كان له مقاسات.
		if ( $sizes && $product->get_demo_sizes() && ! array_intersect( $sizes, $product->get_demo_sizes() ) ) {
			continue;
		}

		if ( $sizes && ! $product->get_demo_sizes() ) {
			continue;
		}

		if ( $colours && ! array_intersect( $colours, $product->get_demo_colours() ) ) {
			continue;
		}

		if ( null !== $min && $product->get_price() < $min ) {
			continue;
		}

		if ( null !== $max && $product->get_price() > $max ) {
			continue;
		}

		$out[] = $i;
	}

	$orderby = (string) ( $_GET['orderby'] ?? '' );                                          // phpcs:ignore

	if ( 'price' === $orderby || 'price-desc' === $orderby ) {
		usort(
			$out,
			static function ( $a, $b ) {
				return ( new WC_Product( false, $a ) )->get_price() <=> ( new WC_Product( false, $b ) )->get_price();
			}
		);

		if ( 'price-desc' === $orderby ) {
			$out = array_reverse( $out );
		}
	}

	$cache = $out;

	return $cache;
}
function have_posts() {                                        // phpcs:ignore
	if ( empty( $GLOBALS['mp_smoke_archive'] ) && empty( $GLOBALS['mp_single_only'] ) ) {
		return false;
	}

	$GLOBALS['mp_loop_i'] = $GLOBALS['mp_loop_i'] ?? 0;

	// صفحة منتج واحد: دورة واحدة فقط.
	$total = empty( $GLOBALS['mp_single_only'] ) ? count( mp_smoke_matching_products() ) : 1;

	return $GLOBALS['mp_loop_i'] < $total;
}
function the_post() {                                          // phpcs:ignore
	if ( empty( $GLOBALS['mp_smoke_archive'] ) && empty( $GLOBALS['mp_single_only'] ) ) {
		return;
	}

	$i = $GLOBALS['mp_loop_i'] ?? 0;

	if ( empty( $GLOBALS['mp_single_only'] ) ) {
		$matching = mp_smoke_matching_products();
		$index    = $matching[ $i ] ?? 0;
		// منتج واحد بمتغيّرات في الشبكة، ليُختبر مسار البطاقة المتغيّرة.
		$GLOBALS['product'] = new WC_Product( 0 === $index, $index );
	} else {
		$GLOBALS['product'] = new WC_Product( ! empty( $GLOBALS['mp_smoke_variable'] ), 0 );
	}

	$GLOBALS['woocommerce_loop'] = array( 'loop' => $i + 1 );
	$GLOBALS['mp_loop_i'] = $i + 1;
}
function get_the_content() { return ''; }                      // phpcs:ignore
function the_content() {}                                      // phpcs:ignore
function rest_url( $p = '' ) { return 'https://example.test/wp-json/' . $p; } // phpcs:ignore
function wp_create_nonce( $a ) { return 'nonce'; }             // phpcs:ignore
function load_theme_textdomain( ...$a ) {}                     // phpcs:ignore
function add_theme_support( ...$a ) {}                         // phpcs:ignore
function register_nav_menus( $a ) {}                           // phpcs:ignore
function add_image_size( ...$a ) {}                            // phpcs:ignore
function get_search_form( $a = array() ) {                     // phpcs:ignore
	$args = is_array( $a ) ? $a : array();
	include MATJAR_PRO_DIR . '/searchform.php';
}
function get_template_part( $slug, $name = '', $args = array() ) { // phpcs:ignore
	$file = MATJAR_PRO_DIR . '/' . $slug . ( $name ? "-{$name}" : '' ) . '.php';

	if ( ! file_exists( $file ) ) {
		throw new RuntimeException( "get_template_part: ملف غير موجود {$slug}" );
	}

	// ووردبريس يمرّر $args إلى القالب منذ 5.5، ولا يُوَرَّث من النطاق الأعلى.
	$loader = static function ( $file, $args ) {
		include $file;
	};

	$loader( $file, $args );
}
function get_header() { include MATJAR_PRO_DIR . '/header.php'; } // phpcs:ignore
function get_footer() { include MATJAR_PRO_DIR . '/footer.php'; } // phpcs:ignore

/* ---------- مُحاكي ووكومرس ---------- */

if ( $GLOBALS['mp_smoke_with_wc'] ) {
	class WooCommerce {} // phpcs:ignore
	class MP_Smoke_Cart { // phpcs:ignore
		/**
		 * محتوى السلة يُقاد من --state=cart=0:2,1:1 (فهرس المنتج:الكمية)،
		 * فيمكن تصيير اللوح لأي تركيبة بالخادم لا ببناء المحتوى في المتصفح.
		 *
		 * @return array
		 */
		public function get_cart() {
			$spec = $GLOBALS['mp_smoke_cart'] ?? '0:2,1:1';
			$out  = array();

			foreach ( array_filter( explode( ',', (string) $spec ) ) as $pair ) {
				$parts    = explode( ':', $pair );
				$index    = (int) $parts[0];
				$quantity = max( 1, (int) ( $parts[1] ?? 1 ) );
				$product  = new WC_Product( false, $index );

				$out[ 'key' . $index ] = array(
					'data'       => $product,
					'quantity'   => $quantity,
					'product_id' => $product->get_id(),
				);
			}

			return $out;
		}
		/** @return int */   public function get_cart_contents_count() {
			$n = 0;

			foreach ( $this->get_cart() as $item ) {
				$n += (int) $item['quantity'];
			}

			return $n;
		}
		/** @return bool */  public function is_empty() { return ! $this->get_cart(); }
		/** @return float */ private function subtotal() {
			$sum = 0.0;

			foreach ( $this->get_cart() as $item ) {
				$sum += $item['data']->get_price() * (int) $item['quantity'];
			}

			return $sum;
		}
		/** @param object $p المنتج. @return string */ public function get_product_price( $p ) { return wc_price( $p->get_price() ); }
		/** @param object $p المنتج. @param int $q الكمية. @return string */ public function get_product_subtotal( $p, $q ) { return wc_price( $p->get_price() * (int) $q ); }
		/** @return string */public function get_cart_subtotal() { return wc_price( $this->subtotal() ); }
		/** @return string */public function get_total() { return wc_price( $this->subtotal() ); }
		/** @return float */ public function get_displayed_subtotal() { return $this->subtotal(); }
		/** @return bool */  public function needs_shipping() { return true; }
		/** @return bool */  public function show_shipping() { return true; }
		/** @return array */ public function get_coupons() { return array(); }
		/** @return array */ public function get_fees() { return array(); }
		/** @return array */ public function get_tax_totals() { return array(); }
		/** @return bool */  public function display_prices_including_tax() { return true; }
	}
	class MP_Smoke_WC { // phpcs:ignore
		/** @var MP_Smoke_Cart */
		public $cart;
		public function __construct() { $this->cart = new MP_Smoke_Cart(); }
	}
	function WC() { static $i; return $i ?: $i = new MP_Smoke_WC(); }   // phpcs:ignore
	function wc_get_cart_url() { return 'https://example.test/cart'; }  // phpcs:ignore
	function wc_get_page_id( $p ) { return 5; }                         // phpcs:ignore
	function wc_get_page_permalink( $p ) { return 'https://example.test/shop'; } // phpcs:ignore
	function get_woocommerce_currency_symbol() { return 'ر.س'; }        // phpcs:ignore
	function get_woocommerce_currency() { return 'SAR'; }               // phpcs:ignore
	function wc_price( $v ) { return '<span class="woocommerce-Price-amount">' . number_format( (float) $v, 2 ) . ' ر.س</span>'; } // phpcs:ignore
	function is_cart() { return (bool) $GLOBALS['mp_smoke_cartpage']; }        // phpcs:ignore
	function is_checkout() { return (bool) $GLOBALS['mp_smoke_funnel']; }         // phpcs:ignore
	function is_order_received_page() { return false; }                 // phpcs:ignore
	function wc_get_checkout_url() { return 'https://example.test/checkout'; }    // phpcs:ignore
	function wc_get_price_to_display( $p, $a = array() ) { return isset( $a['price'] ) ? (float) $a['price'] : 179.0; } // phpcs:ignore
	function wc_product_class( $class = '', $product = null ) { echo 'class="' . esc_attr( ( is_array( $class ) ? implode( ' ', $class ) : $class ) . ' product type-simple' ) . '"'; } // phpcs:ignore
	function wc_tax_enabled() { return true; }                          // phpcs:ignore
	function wc_clean( $v ) { return is_array( $v ) ? array_map( 'wc_clean', $v ) : sanitize_text_field( $v ); }  // phpcs:ignore
	function woocommerce_breadcrumb( $a = array() ) { echo '<nav class="mp-breadcrumb"><span class="mp-breadcrumb__item"><a href="#">الرئيسية</a></span><span class="mp-breadcrumb__item">عبايات</span></nav>'; } // phpcs:ignore
	function woocommerce_page_title( $echo = true ) { echo 'عبايات'; }          // phpcs:ignore
	function woocommerce_product_loop() { return true; }                        // phpcs:ignore
	function wc_get_loop_display_mode() { return 'products'; }                  // phpcs:ignore
	function woocommerce_product_loop_start( $echo = true ) { include MATJAR_PRO_DIR . '/woocommerce/loop/loop-start.php'; } // phpcs:ignore
	function woocommerce_product_loop_end( $echo = true ) { include MATJAR_PRO_DIR . '/woocommerce/loop/loop-end.php'; }     // phpcs:ignore
	function woocommerce_result_count() {                               // phpcs:ignore
		// ووكومرس يمرّر total/per_page/current إلى القالب؛ نمرّرها كما هي.
		$n = count( mp_smoke_matching_products() );

		if ( ! $n ) {
			return;
		}

		wc_get_template( 'loop/result-count.php', array( 'total' => $n, 'per_page' => 12, 'current' => 1 ) );
	}
	function woocommerce_catalog_ordering() {                                   // phpcs:ignore
		echo '<form class="woocommerce-ordering" method="get" action="https://example.test/shop">'
			. '<label class="sr-only" for="mp-orderby">ترتيب المنتجات</label>'
			. '<select id="mp-orderby" name="orderby">'
			. '<option>الأحدث</option><option selected>الأقل سعراً</option><option>الأعلى سعراً</option><option>الأكثر مبيعاً</option>'
			. '</select></form>';
	}
	function wc_coupons_enabled() { return true; }                      // phpcs:ignore
	function the_permalink() { echo 'https://example.test/product/demo'; }        // phpcs:ignore
	function the_ID() { echo '101'; }                                   // phpcs:ignore

	class WC_Product { // phpcs:ignore
		/** @var bool */
		public $variable = false;
		/** @var int */
		public $index = 0;
		/** @var array */
		public static $demo = array(
			array( 'عباية كلاسيك مطرّزة', 179, 279, 12, array( 's', 'm', 'l' ), array( 'black' ), 42, '4.8' ),
			array( 'عطر شرقي فاخر 100مل', 245, 0, 13, array(), array( 'beige' ), 118, '4.9' ),
			array( 'حقيبة يد جلد طبيعي', 219, 289, 14, array(), array( 'black', 'beige' ), 27, '4.6' ),
			array( 'طرحة كريب سادة', 59, 0, 15, array( 'm', 'l', 'xl' ), array( 'black', 'navy' ), 63, '4.7' ),
			array( 'عباية سادة بأكمام واسعة', 149, 0, 12, array( 's', 'm' ), array( 'navy' ), 31, '4.5' ),
			array( 'طقم مباخر خشب', 320, 420, 13, array(), array( 'beige' ), 54, '4.8' ),
			array( 'حقيبة كتف صغيرة', 129, 0, 14, array(), array( 'black', 'navy' ), 19, '4.4' ),
			array( 'شيلة حرير مطرّزة', 89, 119, 15, array( 'm', 'l' ), array( 'beige', 'navy' ), 76, '4.9' ),
		);
		/** @param bool $variable هل المنتج ذو متغيّرات. @param int $index رقم المنتج التجريبي. */
		public function __construct( $variable = false, $index = 0 ) { $this->variable = $variable; $this->index = $index % count( self::$demo ); }
		/** @return array */ private function demo() { return self::$demo[ $this->index ]; }
		/** @return float */ public function get_price() { return (float) $this->demo()[1]; }
		/** @return array */ public function get_demo_sizes() { return $this->demo()[4]; }
		/** @return array */ public function get_demo_colours() { return $this->demo()[5]; }
		/** @return int */   public function get_id() { return 101 + $this->index; }
		/** @return bool */  public function is_visible() { return true; }
		/** @return int */   public function get_image_id() { return $this->demo()[3]; }
		/** @return string */public function get_name() { return $this->demo()[0]; }
		/** @return bool */  public function is_in_stock() { return true; }
		/** @return bool */  public function is_on_sale() { return $this->demo()[2] > 0; }
		/** @return string */public function get_regular_price() { return (string) ( $this->demo()[2] > 0 ? $this->demo()[2] : $this->demo()[1] ); }
		/** @return string */public function get_price_html() {
			$d = $this->demo();
			return $d[2] > 0
				? '<ins>' . number_format( $d[1], 2 ) . ' ر.س</ins> <del>' . number_format( $d[2], 2 ) . ' ر.س</del>'
				: '<span>' . number_format( $d[1], 2 ) . ' ر.س</span>';
		}
		/** @return int */   public function get_review_count() { return (int) $this->demo()[6]; }
		/** @return string */public function get_average_rating() { return (string) $this->demo()[7]; }
		/** @param string $t النوع. @return bool */ public function is_type( $t ) { return $this->variable ? 'variable' === $t : 'simple' === $t; }
		/** @return bool */  public function is_purchasable() { return true; }
		/** @return string */public function get_permalink() { return 'https://example.test/product/demo'; }
		/** @return bool */  public function managing_stock() { return true; }
		/** @return int */   public function get_stock_quantity() { return 3; }
		/** @return string */public function get_short_description() { return '<ul><li>كريب ثقيل لا يشفّ</li><li>تطريز يدوي</li></ul>'; }
		/** @return string */public function get_sku() { return 'AB-01'; }
		/** @return bool */  public function exists() { return true; }
		/** @param string $size المقاس. @return string */ public function get_image( $size = '' ) { return '<img src="https://example.test/i-' . mp_smoke_image( $this->demo()[3] ) . '.jpg" width="150" height="188" alt="' . esc_attr( $this->get_name() ) . '">'; }
		/** @return bool */  public function is_sold_individually() { return false; }
		/** @return int */   public function get_max_purchase_quantity() { return 10; }
		/** @return int */   public function get_min_purchase_quantity() { return 1; }
		/** @return bool */  public function backorders_require_notification() { return false; }
		/** @param int $q الكمية. @return bool */ public function is_on_backorder( $q = 1 ) { return false; }
		/** @return string */public function single_add_to_cart_text() { return 'أضف إلى السلة'; }
		/** @return string */public function get_type() { return $this->variable ? 'variable' : 'simple'; }
		/** @return array */ public function get_gallery_image_ids() { return array( 16, 17 ); }
		/** @return string */public function get_description() { return '<p>قماش كريب ثقيل لا يشفّ، بتطريز يدوي على الأكمام. قَصّة واسعة مريحة تناسب الطول من 160 إلى 175 سم.</p>'; }
		/** @return string */public function get_stock_status() { return 'instock'; }
		/** @return int */   public function get_rating_count() { return $this->get_review_count(); }
		/** @return string */public function get_title() { return $this->get_name(); }
		/** @param string $n اسم الخاصية. @return string */ public function get_variation_default_attribute( $n ) { return 'pa_size' === $n ? 'm' : ''; }
		/** @return array */ public function get_variation_attributes() {
			return array(
				'pa_size'  => array( 's', 'm', 'l', 'xl' ),
				'pa_color' => array( 'black', 'beige', 'navy' ),
			);
		}
		/** @return array */ public function get_available_variations() {
			$out = array();
			$i   = 0;

			foreach ( $this->get_variation_attributes()['pa_size'] as $size ) {
				foreach ( $this->get_variation_attributes()['pa_color'] as $colour ) {
					++$i;
					$price = 179 + ( 'xl' === $size ? 20 : 0 );
					$out[] = array(
						'variation_id'          => 900 + $i,
						'attributes'            => array( 'attribute_pa_size' => $size, 'attribute_pa_color' => $colour ),
						'display_price'         => $price,
						'display_regular_price' => 279,
						'price_html'            => '<ins>' . number_format( $price, 2 ) . ' ر.س</ins> <del>279.00 ر.س</del>',
						'availability_html'     => 'navy' === $colour && 'xl' === $size ? '<p class="stock out-of-stock">نفدت الكمية</p>' : '',
						'is_in_stock'           => ! ( 'navy' === $colour && 'xl' === $size ),
						'is_purchasable'        => true,
						'max_qty'               => 10,
						'min_qty'               => 1,
						'image'                 => array( 'src' => 'https://example.test/i-' . array( 1, 5, 6 )[ $i % 3 ] . '.jpg', 'srcset' => '', 'sizes' => '', 'alt' => '' ),
					);
				}
			}

			return $out;
		}
	}
	function is_shop() { return false; }                                // phpcs:ignore
	function is_product_taxonomy() { return false; }                    // phpcs:ignore
	function is_account_page() { return false; }                        // phpcs:ignore
	function wc_get_attribute_taxonomies() {                        // phpcs:ignore
		return array(
			(object) array( 'attribute_name' => 'size' ),
			(object) array( 'attribute_name' => 'color' ),
		);
	}
	function wc_attribute_taxonomy_name( $n ) { return 'pa_' . $n; }        // phpcs:ignore
	function wc_attribute_label( $t ) { return 'pa_size' === $t ? 'المقاس' : 'اللون'; }        // phpcs:ignore
	function wc_get_price_decimals() { return 2; }                          // phpcs:ignore
	function wc_get_price_thousand_separator() { return ','; }              // phpcs:ignore
	function wc_get_price_decimal_separator() { return '.'; }               // phpcs:ignore
	function wc_ship_to_billing_address_only() { return false; }            // phpcs:ignore
	function wc_shipping_enabled() { return true; }                         // phpcs:ignore
	function wc_get_account_menu_items() {                                  // phpcs:ignore
		return array(
			'dashboard'       => 'نظرة عامة',
			'orders'          => 'طلباتي',
			'edit-address'    => 'عناويني',
			'edit-account'    => 'بياناتي',
			'customer-logout' => 'خروج',
		);
	}
	function wc_get_account_menu_item_classes( $e ) { return 'woocommerce-MyAccount-navigation-link woocommerce-MyAccount-navigation-link--' . $e . ( 'dashboard' === $e ? ' is-active' : '' ); } // phpcs:ignore
	function wc_get_account_endpoint_url( $e ) { return 'https://example.test/account/' . $e; }            // phpcs:ignore
	function wc_get_endpoint_url( $e, $v = '', $p = '' ) { return 'https://example.test/account/' . $e . ( $v ? '/' . $v : '' ); } // phpcs:ignore
	function wc_get_account_formatted_address( $t = 'billing' ) { return 'نورة القحطاني<br>حي النرجس، شارع الأمير سلطان<br>الرياض'; } // phpcs:ignore
	function wc_format_datetime( $d, $f = '' ) { return '18 سبتمبر 2026'; }  // phpcs:ignore
	function wc_get_order_status_name( $s ) { return array( 'processing' => 'قيد التجهيز', 'completed' => 'مكتمل' )[ $s ] ?? $s; } // phpcs:ignore
	function wc_get_account_orders_actions( $o ) { return array( 'view' => array( 'url' => '#', 'name' => 'عرض' ), 'pay' => array( 'url' => '#', 'name' => 'إتمام الدفع' ) ); } // phpcs:ignore

	class MP_Smoke_Item { // phpcs:ignore
		/** @var int */
		public $index;
		/** @param int $index رقم المنتج. */
		public function __construct( $index = 0 ) { $this->index = $index; }
		/** @return WC_Product */ public function get_product() { return new WC_Product( false, $this->index ); }
	}
	class MP_Smoke_Date { // phpcs:ignore
		/** @param string $f الصيغة. @return string */
		public function date( $f ) { return '2026-09-18T10:00:00+00:00'; }
	}
	class WC_Order { // phpcs:ignore
		/** @var string */
		public $status;
		/** @param string $status الحالة. */
		public function __construct( $status = 'processing' ) { $this->status = $status; }
		/** @return string */public function get_status() { return $this->status; }
		/** @return string */public function get_order_number() { return '10248'; }
		/** @return MP_Smoke_Date */ public function get_date_created() { return new MP_Smoke_Date(); }
		/** @return string */public function get_formatted_order_total() { return '<span>358.00 ر.س</span>'; }
		/** @return string */public function get_view_order_url() { return 'https://example.test/account/view-order/10248'; }
		/** @return int */   public function get_item_count() { return 2; }
		/** @return array */ public function get_items() { return array( new MP_Smoke_Item(), new MP_Smoke_Item( 1 ) ); }
		/** @return string */public function get_billing_phone() { return '0551234567'; }
		/** @return string */public function get_payment_method_title() { return 'مدى'; }
		/** @return string */public function get_payment_method() { return 'mada'; }
		/** @return int */   public function get_id() { return 10248; }
		/** @param string $s الحالة. @return bool */ public function has_status( $s ) { return $this->status === $s; }
		/** @return string */public function get_checkout_payment_url() { return '#'; }
	}
	function wc_get_orders( $a = array() ) { return array( new WC_Order( 'processing' ) ); }    // phpcs:ignore

	function wc_get_products( $a = array() ) { // phpcs:ignore
		$out = array();

		for ( $i = 0; $i < 4; $i++ ) {
			$out[] = new WC_Product( 2 === $i, $i );
		}

		return $out;
	}
	function wc_get_cart_remove_url( $k ) { return 'https://example.test/cart?remove=' . $k; }             // phpcs:ignore
	function wc_get_formatted_cart_item_data( $i, $flat = false ) { return ''; }                           // phpcs:ignore
	function wc_get_template_part( $slug, $name = '' ) {                // phpcs:ignore
		$file = MATJAR_PRO_DIR . '/woocommerce/' . $slug . ( $name ? "-{$name}" : '' ) . '.php';

		if ( file_exists( $file ) ) {
			include $file;
		}
	}
	function woocommerce_mini_cart( $a = array() ) {                    // phpcs:ignore
		include MATJAR_PRO_DIR . '/woocommerce/cart/mini-cart.php';
	}
	/* ---------- دوال قوالب ووكومرس الأساسية ---------- */

	function wpautop( $t, $br = true ) { return $t; }                   // phpcs:ignore
	function wc_stock_amount( $v ) { return (float) $v; }               // phpcs:ignore
	function wc_get_stock_html( $product ) { return ''; }               // phpcs:ignore
	function the_title( $before = '', $after = '', $echo = true ) {     // phpcs:ignore
		$out = $before . ( $GLOBALS['product'] ? $GLOBALS['product']->get_name() : 'منتج' ) . $after;

		if ( $echo ) {
			echo $out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $out;
	}
	function get_the_title( $id = 0 ) { return $GLOBALS['product'] ? $GLOBALS['product']->get_name() : 'منتج'; }  // phpcs:ignore
	function wc_get_template( $slug, $args = array() ) {                // phpcs:ignore
		$file = MATJAR_PRO_DIR . '/woocommerce/' . $slug;

		if ( ! file_exists( $file ) ) {
			throw new RuntimeException( "wc_get_template: قالب غير موجود {$slug}" );
		}

		$loader = static function ( $file, $args ) {
			if ( $args ) {
				extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			}

			include $file;
		};

		$loader( $file, $args );
	}
	function woocommerce_quantity_input( $args = array(), $product = null, $echo = true ) { // phpcs:ignore
		// مفاتيح ووكومرس الافتراضية بالحرف: هي كل ما يراه القالب المنسوخ.
		$product = $product ?: $GLOBALS['product'];

		wc_get_template(
			'global/quantity-input.php',
			wp_parse_args(
				$args,
				array(
					'input_id'     => uniqid( 'quantity_' ),
					'input_name'   => 'quantity',
					'input_value'  => 1,
					'classes'      => array( 'input-text', 'qty', 'text' ),
					'max_value'    => 10,
					'min_value'    => 1,
					'step'         => 1,
					'pattern'      => '[0-9]*',
					'inputmode'    => 'numeric',
					'product_name' => $product ? $product->get_name() : '',
					'placeholder'  => '',
				)
			)
		);
	}
	function woocommerce_show_product_images() { wc_get_template( 'single-product/product-image.php' ); }         // phpcs:ignore
	function woocommerce_show_product_sale_flash() {}                   // phpcs:ignore
	function woocommerce_template_single_title() { the_title( '<h1 class="product_title entry-title">', '</h1>' ); } // phpcs:ignore
	function woocommerce_template_single_rating() {}                    // phpcs:ignore
	function woocommerce_template_single_price() {}                     // phpcs:ignore
	function woocommerce_template_single_excerpt() {}                   // phpcs:ignore
	function woocommerce_template_single_meta() {}                      // phpcs:ignore
	function woocommerce_template_single_sharing() {}                   // phpcs:ignore
	function woocommerce_upsell_display( ...$a ) {}                     // phpcs:ignore
	function woocommerce_output_related_products() {}                   // phpcs:ignore
	function woocommerce_template_single_add_to_cart() {                // phpcs:ignore
		global $product;

		if ( $product->is_type( 'variable' ) ) {
			wc_get_template(
				'single-product/add-to-cart/variable.php',
				array(
					'available_variations' => $product->get_available_variations(),
					'attributes'           => $product->get_variation_attributes(),
					'selected_attributes'  => array(),
				)
			);

			return;
		}

		wc_get_template( 'single-product/add-to-cart/simple.php' );
	}
	function woocommerce_single_variation() { echo '<div class="woocommerce-variation single_variation"></div>'; } // phpcs:ignore
	function woocommerce_single_variation_add_to_cart_button() { wc_get_template( 'single-product/add-to-cart/variation-add-to-cart-button.php' ); } // phpcs:ignore
	function woocommerce_output_product_data_tabs() { wc_get_template( 'single-product/tabs/tabs.php' ); }         // phpcs:ignore
	function wc_get_product( $id = 0 ) { return $GLOBALS['product']; }  // phpcs:ignore
	function wc_dropdown_variation_attribute_options( $args = array() ) {   // phpcs:ignore
		$name  = $args['attribute'];
		$class = $args['class'] ?? '';

		printf(
			'<select id="%s" class="%s" name="attribute_%s" data-attribute_name="attribute_%s">',
			esc_attr( sanitize_title( $name ) ),
			esc_attr( $class ),
			esc_attr( sanitize_title( $name ) ),
			esc_attr( sanitize_title( $name ) )
		);

		printf( '<option value="">%s</option>', esc_html( 'اختر ' . wc_attribute_label( $name ) ) );

		foreach ( (array) $args['options'] as $option ) {
			$term = get_term_by( 'slug', $option, $name );

			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $option ),
				selected( $args['selected'] ?? '', $option, false ),
				esc_html( $term ? $term->name : $option )
			);
		}

		echo '</select>';
	}
	function selected( $a, $b, $echo = true ) { $r = (string) $a === (string) $b ? ' selected' : ''; if ( $echo ) { echo $r; } return $r; }  // phpcs:ignore
	function checked( $a, $b = true, $echo = true ) { $r = (string) $a === (string) $b ? ' checked' : ''; if ( $echo ) { echo $r; } return $r; } // phpcs:ignore

	/* ---------- الدفع ---------- */

	class MP_Smoke_Checkout { // phpcs:ignore
		/** @var array */
		public $checkout_fields = array();
		/** @return array */ public function get_checkout_fields( $section = '' ) {
			$fields = apply_filters( 'woocommerce_checkout_fields', $this->defaults() );

			return $section ? ( $fields[ $section ] ?? array() ) : $fields;
		}
		/** @return array */ private function defaults() {
			return array(
				'billing' => array(
					'billing_first_name' => array( 'label' => 'الاسم الأول', 'required' => true, 'class' => array( 'form-row-first' ) ),
					'billing_last_name'  => array( 'label' => 'اسم العائلة', 'required' => true, 'class' => array( 'form-row-last' ) ),
					'billing_country'    => array( 'label' => 'الدولة', 'required' => true, 'type' => 'country', 'class' => array( 'form-row-wide' ) ),
					'billing_state'      => array( 'label' => 'المنطقة', 'required' => true, 'type' => 'state', 'class' => array( 'form-row-wide' ) ),
					'billing_city'       => array( 'label' => 'المدينة', 'required' => true, 'class' => array( 'form-row-wide' ) ),
					'billing_address_1'  => array( 'label' => 'الشارع', 'required' => true, 'class' => array( 'form-row-wide' ) ),
					'billing_address_2'  => array( 'label' => 'الحي', 'required' => false, 'class' => array( 'form-row-wide' ) ),
					'billing_phone'      => array( 'label' => 'الجوال', 'required' => true, 'type' => 'tel', 'class' => array( 'form-row-wide' ) ),
					'billing_email'      => array( 'label' => 'البريد الإلكتروني', 'required' => true, 'type' => 'email', 'class' => array( 'form-row-wide' ) ),
					'billing_company'    => array( 'label' => 'الشركة', 'required' => false, 'class' => array( 'form-row-wide' ) ),
					'billing_postcode'   => array( 'label' => 'الرمز البريدي', 'required' => false, 'class' => array( 'form-row-wide' ) ),
				),
				'order'   => array(
					'order_comments' => array( 'label' => 'ملاحظات الطلب', 'type' => 'textarea', 'required' => false, 'class' => array( 'form-row-wide' ) ),
				),
			);
		}
		/** @return string */ public function get_value( $key ) { return ''; }
		/** @return bool */   public function is_registration_enabled() { return false; }
		/** @return bool */   public function is_registration_required() { return false; }
		/** @return bool */   public function get_checkout_url() { return 'https://example.test/checkout'; }
		/** @return bool */   public function enable_signup() { return false; }
	}
	function WC_Checkout() { static $i; return $i ?: $i = new MP_Smoke_Checkout(); }  // phpcs:ignore
	function wc_get_checkout_fields( $s = '' ) { return WC_Checkout()->get_checkout_fields( $s ); } // phpcs:ignore
	function woocommerce_form_field( $key, $args = array(), $value = null ) {   // phpcs:ignore
		$args     = wp_parse_args( $args, array( 'type' => 'text', 'label' => '', 'required' => false, 'class' => array(), 'placeholder' => '', 'options' => array(), 'priority' => '' ) );
		$required = $args['required'] ? ' <abbr class="required" title="مطلوب">*</abbr>' : '';
		$classes  = implode( ' ', array_merge( array( 'form-row' ), (array) $args['class'] ) );

		$field  = sprintf( '<p class="%s" id="%s_field">', esc_attr( $classes ), esc_attr( $key ) );
		$field .= sprintf( '<label for="%s">%s%s</label>', esc_attr( $key ), esc_html( $args['label'] ), $required );

		if ( 'textarea' === $args['type'] ) {
			$field .= sprintf( '<textarea id="%s" name="%s" class="input-text" rows="2" placeholder="%s"></textarea>', esc_attr( $key ), esc_attr( $key ), esc_attr( $args['placeholder'] ) );
		} elseif ( in_array( $args['type'], array( 'country', 'state', 'select' ), true ) ) {
			$options = 'country' === $args['type']
				? array( 'SA' => 'السعودية', 'AE' => 'الإمارات', 'KW' => 'الكويت' )
				: array( 'riyadh' => 'الرياض', 'makkah' => 'مكة المكرمة', 'eastern' => 'الشرقية' );

			$field .= sprintf( '<select id="%s" name="%s" class="input-text">', esc_attr( $key ), esc_attr( $key ) );

			foreach ( $options as $v => $l ) {
				$field .= sprintf( '<option value="%s">%s</option>', esc_attr( $v ), esc_html( $l ) );
			}

			$field .= '</select>';
		} else {
			$autocomplete = empty( $args['autocomplete'] ) ? '' : ' autocomplete="' . esc_attr( $args['autocomplete'] ) . '"';

			$field .= sprintf(
				'<input type="%s" id="%s" name="%s" class="input-text" placeholder="%s"%s%s>',
				esc_attr( in_array( $args['type'], array( 'tel', 'email' ), true ) ? $args['type'] : 'text' ),
				esc_attr( $key ),
				esc_attr( $key ),
				esc_attr( $args['placeholder'] ),
				$autocomplete, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$args['required'] ? ' required' : ''
			);
		}

		$field .= '</p>';

		// ووكومرس يمرّر الحقل المبني عبر المرشِّح، وعليه يعتمد وسم «(اختياري)».
		echo apply_filters( 'woocommerce_form_field', $field, $key, $args, $value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	function wc_get_shipping_method_count( $include_legacy = false, $enabled_only = false ) { return 1; }   // phpcs:ignore
	function wc_ship_to_billing_address_only_disabled() { return false; }        // phpcs:ignore
	function wc_get_chosen_shipping_method_ids() { return array( 'flat_rate' ); }     // phpcs:ignore
	function wc_cart_totals_subtotal_html() { echo '<span class="woocommerce-Price-amount">358.00 ر.س</span>'; }    // phpcs:ignore
	function wc_cart_totals_order_total_html() { echo '<strong><span class="woocommerce-Price-amount">358.00 ر.س</span></strong>'; }  // phpcs:ignore
	function wc_cart_totals_shipping_html() { echo '<tr class="woocommerce-shipping-totals shipping"><th>الشحن</th><td>مجاني</td></tr>'; }  // phpcs:ignore
	function wc_cart_totals_coupon_html( $c ) { echo '—'; }                     // phpcs:ignore
	function wc_cart_totals_coupon_label( $c, $echo = true ) { echo 'كوبون'; }  // phpcs:ignore
	function wc_cart_totals_fee_html( $f ) { echo '—'; }                        // phpcs:ignore
	function wc_cart_totals_taxes_total_html() { echo '—'; }                    // phpcs:ignore
	function wc_get_cart_item_data( $i ) { return ''; }                         // phpcs:ignore

	class MP_Smoke_Gateway { // phpcs:ignore
		/** @var string */ public $id;
		/** @var string */ public $title;
		/** @var string */ public $chosen;
		/** @var string */ public $description = '';
		/** @param string $id المعرّف. @param string $title العنوان. @param bool $chosen هل هو المختار. */
		public function __construct( $id, $title, $chosen = false ) { $this->id = $id; $this->title = $title; $this->chosen = $chosen; }
		/** @return string */ public function get_title() { return $this->title; }
		/** @return string */ public function get_description() { return $this->description; }
		/** @return string */ public function get_icon() { return ''; }
		/** @return bool */   public function has_fields() { return false; }
		/** @return void */   public function payment_fields() {}
	}
	function wc_get_payment_gateways() { return array(); }                      // phpcs:ignore
	function mp_smoke_gateways() {                                              // phpcs:ignore
		return array(
			'mada'   => new MP_Smoke_Gateway( 'mada', 'مدى / بطاقة بنكية', true ),
			'applepay' => new MP_Smoke_Gateway( 'applepay', 'Apple Pay' ),
			'tabby'  => new MP_Smoke_Gateway( 'tabby', 'تابي — قسّمها على 4' ),
			'cod'    => new MP_Smoke_Gateway( 'cod', 'الدفع عند الاستلام' ),
		);
	}
	function wc_print_notices() {}                                              // phpcs:ignore
	function wc_get_privacy_policy_text( $t = '' ) { return ''; }               // phpcs:ignore
	function wc_terms_and_conditions_checkbox_enabled() { return false; }       // phpcs:ignore
	function wc_terms_and_conditions_page_id() { return 0; }                    // phpcs:ignore
	function wc_get_endpoint_url_checkout() { return 'https://example.test/checkout'; }    // phpcs:ignore

	function setup_postdata( $p ) { return true; }                      // phpcs:ignore
	function wp_reset_postdata() {}                                     // phpcs:ignore
	/*
	 * خطّافات قوالب ووكومرس الافتراضية كما يسجّلها wc-template-hooks.php.
	 * تسجيلها هنا هو ما يجعل remove_action في inc/wc-product.php تعمل فعلاً،
	 * فيُختبر الترتيب النهائي لا الترتيب المفترض.
	 */
	add_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	add_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
	add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
	add_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
	add_action( 'woocommerce_single_variation', 'woocommerce_single_variation', 10 );
	add_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );

	/*
	 * تبويبات ووكومرس الافتراضية: يسجّلها ووكومرس على المرشِّح نفسه، وبها
	 * يُختبر أن matjar_pro_product_tabs تُعيد التسمية ولا تُفرغ القائمة.
	 */
	function woocommerce_default_product_tabs( $tabs = array() ) {          // phpcs:ignore
		global $product;

		$tabs['description'] = array(
			'title'    => 'Description',
			'priority' => 10,
			'callback' => static function () {
				echo wp_kses_post( $GLOBALS['product']->get_description() );
			},
		);

		$tabs['additional_information'] = array(
			'title'    => 'Additional information',
			'priority' => 20,
			'callback' => static function () {
				echo '<table class="woocommerce-product-attributes shop_attributes">'
					. '<tr><th>المقاس</th><td>S · M · L · XL</td></tr>'
					. '<tr><th>اللون</th><td>أسود · بيج · كحلي</td></tr>'
					. '<tr><th>القماش</th><td>كريب ثقيل</td></tr>'
					. '</table>';
			},
		);

		$tabs['reviews'] = array(
			'title'    => 'Reviews (' . $product->get_review_count() . ')',
			'priority' => 30,
			'callback' => static function () {
				echo '<p>لا مراجعات منشورة في بيانات المعاينة.</p>';
			},
		);

		return $tabs;
	}
	add_filter( 'woocommerce_product_tabs', 'woocommerce_default_product_tabs', 10 );

	function is_product() { return ! empty( $GLOBALS['mp_smoke_product'] ); }   // phpcs:ignore

	/*
	 * ما يصلّه ووكومرس بـ WC_Checkout: الحقول ثم الملخّص ثم الدفع. تسجيلها
	 * هنا هو ما يجعل ترتيب الحقول المُرشَّح في inc/wc-checkout.php ظاهراً.
	 */
	function mp_smoke_checkout_billing() { // phpcs:ignore
		$fields = wc_get_checkout_fields( 'billing' );

		// ووكومرس يرتّب الحقول بالأولوية قبل طباعتها.
		uasort(
			$fields,
			static function ( $a, $b ) {
				return ( $a['priority'] ?? 0 ) <=> ( $b['priority'] ?? 0 );
			}
		);

		echo '<div class="woocommerce-billing-fields__field-wrapper">';

		foreach ( $fields as $key => $args ) {
			woocommerce_form_field( $key, $args, WC_Checkout()->get_value( $key ) );
		}

		echo '</div>';

		$order_fields = wc_get_checkout_fields( 'order' );

		foreach ( $order_fields as $key => $args ) {
			woocommerce_form_field( $key, $args, WC_Checkout()->get_value( $key ) );
		}
	}
	function mp_smoke_checkout_review() { wc_get_template( 'checkout/review-order.php', array( 'checkout' => WC_Checkout() ) ); } // phpcs:ignore
	function mp_smoke_checkout_payment() { // phpcs:ignore
		echo '<div id="payment" class="woocommerce-checkout-payment">';
		echo '<ul class="wc_payment_methods payment_methods methods">';

		foreach ( mp_smoke_gateways() as $gateway ) {
			printf(
				'<li class="wc_payment_method payment_method_%1$s">'
					. '<input id="payment_method_%1$s" type="radio" class="input-radio" name="payment_method" value="%1$s"%2$s>'
					. '<label for="payment_method_%1$s">%3$s</label>'
				. '</li>',
				esc_attr( $gateway->id ),
				$gateway->chosen ? ' checked' : '',
				esc_html( $gateway->get_title() )
			);
		}

		echo '</ul>';
		echo '<div class="form-row place-order">';
		printf(
			'<button type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="%1$s">%1$s</button>',
			esc_html( apply_filters( 'woocommerce_order_button_text', __( 'أكمل الطلب', 'matjar-pro' ) ) )
		);
		echo '</div></div>';
	}
	add_action( 'woocommerce_checkout_billing', 'mp_smoke_checkout_billing' );
	add_action( 'woocommerce_checkout_order_review', 'mp_smoke_checkout_review', 10 );
	add_action( 'woocommerce_checkout_order_review', 'mp_smoke_checkout_payment', 20 );

	/*
	 * ما يصلّه ووكومرس بصفحة السلة: كتلة الإجماليات في عمودها الجانبي.
	 */
	function mp_smoke_cart_totals() {                                   // phpcs:ignore
		$file = MATJAR_PRO_DIR . '/woocommerce/cart/cart-totals.php';

		if ( file_exists( $file ) ) {
			include $file;

			return;
		}

		printf(
			'<div class="cart_totals"><h2>إجمالي السلة</h2>'
				. '<table class="shop_table shop_table_responsive"><tbody>'
				. '<tr class="cart-subtotal"><th>المجموع</th><td>%s</td></tr>'
				. '<tr class="woocommerce-shipping-totals shipping"><th>الشحن</th><td>مجاني</td></tr>'
				. '<tr class="order-total"><th>الإجمالي</th><td><strong>%s</strong></td></tr>'
				. '</tbody></table>'
				. '<a class="mp-btn mp-btn--cta mp-btn--lg w-full checkout-button" href="%s">إتمام الشراء</a>'
			. '</div>',
			wp_kses_post( WC()->cart->get_cart_subtotal() ),
			wp_kses_post( WC()->cart->get_total() ),
			esc_url( wc_get_checkout_url() )
		);
	}
	add_action( 'woocommerce_cart_collaterals', 'mp_smoke_cart_totals', 10 );

	function get_post( $id = 0 ) { return (object) array( 'ID' => 101, 'post_title' => 'منتج' ); }         // phpcs:ignore
}

/* ---------- الإعدادات المُحاكاة ---------- */

$GLOBALS['mp_smoke_mods'] = array(
	'matjar_pro_hero_image'              => 12,
	'matjar_pro_hero_eyebrow'            => 'وصل حديثاً',
	'matjar_pro_hero_headline'           => 'تشكيلة الخريف',
	'matjar_pro_hero_subtext'            => 'قطع محدودة الكمية.',
	'matjar_pro_hero_cta_label'          => 'تسوّق الآن',
	'matjar_pro_hero_layout'             => in_array( '--overlay', $argv, true ) ? 'overlay' : 'stacked',
	'matjar_pro_trust_1_subtitle'        => 'خلال 48 ساعة',
	'matjar_pro_promo_1_image'           => 12,
	'matjar_pro_promo_2_image'           => 13,
	'matjar_pro_promo_1_label'           => 'عبايات',
	'matjar_pro_promo_1_url'             => 'https://example.test/abayas',
	'matjar_pro_promo_2_label'           => 'عطور',
	'matjar_pro_cr_number'               => '1010123456',
	'matjar_pro_vat_number'              => '300012345600003',
	'matjar_pro_whatsapp'                => '+966 55 123 4567',
	'matjar_pro_free_shipping_threshold' => 200,
);

/* ---------- التشغيل ---------- */

require_once MATJAR_PRO_DIR . '/inc/helpers.php';
require_once MATJAR_PRO_DIR . '/inc/tokens.php';
require_once MATJAR_PRO_DIR . '/inc/setup.php';
require_once MATJAR_PRO_DIR . '/inc/assets.php';
require_once MATJAR_PRO_DIR . '/inc/customizer.php';
require_once MATJAR_PRO_DIR . '/inc/front-page.php';

if ( function_exists( 'WC' ) ) {
	require_once MATJAR_PRO_DIR . '/inc/woocommerce.php';
	require_once MATJAR_PRO_DIR . '/inc/wc-loop.php';
	require_once MATJAR_PRO_DIR . '/inc/wc-product.php';
	require_once MATJAR_PRO_DIR . '/inc/wc-checkout.php';
	require_once MATJAR_PRO_DIR . '/inc/wc-filter.php';
	require_once MATJAR_PRO_DIR . '/inc/wc-account.php';
}

set_error_handler(
	function ( $severity, $message, $file, $line ) {
		throw new ErrorException( $message, 0, $severity, $file, $line );
	}
);

/*
 * --catalog يُطبع وحده بلا تنسيق: جدول المنتجات التجريبية بصيغة JSON،
 * ليقرأه المحاكي بدل تكرار الأسماء والأسعار يدوياً.
 */
if ( in_array( '--catalog', $argv, true ) ) {
	$mp_catalog = array();

	foreach ( array_keys( WC_Product::$demo ) as $mp_i ) {
		$mp_product = new WC_Product( false, $mp_i );

		$mp_catalog[] = array(
			'id'    => $mp_product->get_id(),
			'name'  => $mp_product->get_name(),
			'price' => $mp_product->get_price(),
			'image' => 'img/i-' . mp_smoke_image( $mp_product->get_image_id() ) . '.svg',
			'link'  => $mp_product->get_permalink(),
		);
	}

	echo wp_json_encode( $mp_catalog );
	exit( 0 );
}

ob_start();
matjar_pro_print_critical_css();
matjar_pro_preload_fonts();

if ( $GLOBALS['mp_smoke_card'] ) {
	// بطاقة المنتج وحدها: أكثر قالب يُصيَّر في المتجر.
	$GLOBALS['product'] = new WC_Product();
	echo '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"></head><body><ul class="mp-grid">';
	include MATJAR_PRO_DIR . '/woocommerce/content-product.php';
	$GLOBALS['product'] = new WC_Product( true );
	include MATJAR_PRO_DIR . '/woocommerce/content-product.php';
	echo '</ul></body></html>';
} elseif ( $GLOBALS['mp_smoke_filter'] ) {
	// فلاتر مُفعَّلة فعلاً: تُختبر بها الشرائح والحقول المحفوظة ورابط التفريغ.
	$_GET['filter_size'] = 's,m';
	$_GET['min_price']   = '120';
	$_GET['max_price']   = '600';
	$_GET['orderby']     = 'price';

	echo '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"></head><body>';
	get_template_part( 'template-parts/shop/filter-body', null, array( 'context' => 'aside' ) );
	get_template_part( 'template-parts/shop/active-filters' );
	get_template_part( 'template-parts/shop/filter-drawer' );
	echo '</body></html>';
} elseif ( $GLOBALS['mp_smoke_archive'] ) {
	// صفحة الأقسام كاملة: الفلترة والشبكة والترتيب.
	if ( ! $GLOBALS['mp_smoke_state_given'] ) {
		$_GET['filter_size'] = 'm';
	}

	include MATJAR_PRO_DIR . '/woocommerce/archive-product.php';
} elseif ( $GLOBALS['mp_smoke_account'] ) {
	// صفحة الحساب: النظرة العامة والطلبات والعناوين.
	$current_user   = (object) array( 'ID' => 3, 'display_name' => 'نورة' );
	$has_orders     = true;
	$customer_orders = (object) array( 'orders' => array( new WC_Order( 'processing' ), new WC_Order( 'completed' ) ), 'max_num_pages' => 1 );
	$current_page   = 1;
	$order          = new WC_Order( 'processing' );

	include MATJAR_PRO_DIR . '/header.php';
	echo '<main id="mp-main" class="mx-auto max-w-screen-xl px-3 py-6 lg:px-4"><h1 class="mb-5 mt-0 text-2xl font-bold text-ink">حسابي</h1><div class="mp-account lg:flex lg:items-start lg:gap-8">';
	include MATJAR_PRO_DIR . '/woocommerce/myaccount/navigation.php';
	echo '<div class="woocommerce-MyAccount-content mp-account__content min-w-0 grow">';
	include MATJAR_PRO_DIR . '/woocommerce/myaccount/dashboard.php';
	echo '<h2 class="mb-4 mt-8 text-lg font-bold text-ink">طلباتي</h2>';
	include MATJAR_PRO_DIR . '/woocommerce/myaccount/orders.php';
	echo '<h2 class="mb-4 mt-8 text-lg font-bold text-ink">عناويني</h2>';
	include MATJAR_PRO_DIR . '/woocommerce/myaccount/my-address.php';
	echo '</div></div></main>';
	include MATJAR_PRO_DIR . '/footer.php';
} elseif ( $GLOBALS['mp_smoke_cartpage'] ) {
	// صفحة السلة كاملة: الجدول والكميات والإجماليات وزر المتابعة.
	do_action( 'init' );

	include MATJAR_PRO_DIR . '/header.php';
	echo '<main id="mp-main" class="mx-auto max-w-screen-lg px-3 py-5 lg:px-4 lg:py-8"><h1 class="mb-5 mt-0 text-2xl font-bold text-ink">سلة التسوّق</h1>';
	include MATJAR_PRO_DIR . '/woocommerce/cart/cart.php';
	echo '</main>';
	include MATJAR_PRO_DIR . '/footer.php';
} elseif ( $GLOBALS['mp_smoke_minicart'] ) {
	// محتوى لوح السلة وحده: هو ما تستبدله أجزاء ووكومرس بعد كل إضافة.
	do_action( 'init' );
	woocommerce_mini_cart();
} elseif ( $GLOBALS['mp_smoke_product'] ) {
	/*
	 * صفحة المنتج كاملة عبر دورة الخطّافات الحقيقية: init يشغّل إعادة
	 * الترتيب، ثم القالب يُطلق woocommerce_single_product_summary فيُصيَّر
	 * التسلسل السلوكي بأولوياته المكتوبة، لا بترتيب مكتوب يدوياً هنا.
	 */
	do_action( 'init' );

	$GLOBALS['mp_loop_i']      = 0;
	$GLOBALS['mp_single_only'] = true;   // دورة واحدة في have_posts/the_post.

	include MATJAR_PRO_DIR . '/woocommerce/single-product.php';
} elseif ( $GLOBALS['mp_smoke_checkout'] ) {
	// قُمع الدفع بنموذجه الكامل: الحقول بترتيبها، الملخّص، طرق الدفع، الزر.
	do_action( 'init' );

	$checkout = WC_Checkout();

	include MATJAR_PRO_DIR . '/header.php';
	echo '<main id="mp-main" class="mx-auto max-w-screen-lg px-3 py-5 lg:px-4 lg:py-8">';
	echo '<h1 class="sr-only">إتمام الطلب</h1>';
	matjar_pro_free_shipping_progress();
	include MATJAR_PRO_DIR . '/woocommerce/checkout/form-checkout.php';
	echo '</main>';
	include MATJAR_PRO_DIR . '/footer.php';
} elseif ( $GLOBALS['mp_smoke_funnel'] ) {
	// قُمع الدفع: الهيدر المصغّر والتذييل المصغّر فقط.
	include MATJAR_PRO_DIR . '/header.php';
	echo '<main id="mp-main"></main>';
	include MATJAR_PRO_DIR . '/footer.php';
} else {
	include MATJAR_PRO_DIR . '/front-page.php';
}

$html = ob_get_clean();

if ( in_array( '--print', $argv, true ) ) {
	echo $html;
	exit( 0 );
}

if ( $GLOBALS['mp_smoke_card'] ) {
	$variant = 'بطاقة المنتج';
} elseif ( $GLOBALS['mp_smoke_filter'] ) {
	$variant = 'الفلترة';
} elseif ( $GLOBALS['mp_smoke_account'] ) {
	$variant = 'صفحة الحساب';
} elseif ( $GLOBALS['mp_smoke_archive'] ) {
	$variant = 'صفحة الأقسام';
} elseif ( $GLOBALS['mp_smoke_cartpage'] ) {
	$variant = 'صفحة السلة';
} elseif ( $GLOBALS['mp_smoke_minicart'] ) {
	$variant = 'لوح السلة';
} elseif ( $GLOBALS['mp_smoke_product'] ) {
	$variant = $GLOBALS['mp_smoke_variable'] ? 'صفحة المنتج (متغيّرات)' : 'صفحة المنتج';
} elseif ( $GLOBALS['mp_smoke_checkout'] ) {
	$variant = 'نموذج الدفع';
} elseif ( $GLOBALS['mp_smoke_funnel'] ) {
	$variant = 'قُمع الدفع';
} else {
	$variant  = $GLOBALS['mp_smoke_with_wc'] ? 'مع ووكومرس' : 'بدون ووكومرس';
	$variant .= in_array( '--overlay', $argv, true ) ? ' + overlay' : '';
}

printf(
	"صُيّر بنجاح · %-24s %6d بايت · صور %d · أزرار %d · حقول %d
",
	$variant,
	strlen( $html ),
	substr_count( $html, '<img' ),
	substr_count( $html, '<button' ),
	substr_count( $html, '<input' )
);
