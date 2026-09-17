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

$GLOBALS['mp_smoke_with_wc'] = in_array( '--woocommerce', $argv, true ) || in_array( '--funnel', $argv, true ) || in_array( '--card', $argv, true );
$GLOBALS['mp_smoke_funnel']  = in_array( '--funnel', $argv, true );
$GLOBALS['mp_smoke_card']    = in_array( '--card', $argv, true );

/* ---------- مُحاكي ووردبريس ---------- */

function __( $t, $d = '' ) { return $t; }                      // phpcs:ignore
function _x( $t, $c = '', $d = '' ) { return $t; }             // phpcs:ignore
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
function sanitize_text_field( $t ) { return $t; }              // phpcs:ignore
function sanitize_textarea_field( $t ) { return $t; }          // phpcs:ignore
function sanitize_hex_color( $t ) { return $t; }               // phpcs:ignore
function absint( $v ) { return abs( (int) $v ); }              // phpcs:ignore
function add_action( ...$a ) {}                                // phpcs:ignore
function add_filter( ...$a ) {}                                // phpcs:ignore
function do_action( ...$a ) {}                                 // phpcs:ignore
function remove_action( ...$a ) {}                             // phpcs:ignore
function apply_filters( $h, $v ) { return $v; }                // phpcs:ignore
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
function wp_date( $f, $t = null ) { return gmdate( $f, $t ?? time() ); } // phpcs:ignore
function current_time( $t ) { return time(); }                 // phpcs:ignore
function is_active_sidebar( $id ) { return false; }            // phpcs:ignore
function dynamic_sidebar( $id ) {}                             // phpcs:ignore
function register_sidebar( $a ) {}                             // phpcs:ignore
function is_wp_error( $t ) { return false; }                   // phpcs:ignore
function get_terms( $a = array() ) { return array(); }         // phpcs:ignore
function get_term_link( $t ) { return 'https://example.test/cat'; } // phpcs:ignore
function wp_get_attachment_image( $id, $size = '', $icon = false, $attr = array() ) { // phpcs:ignore
	$out = '<img src="https://example.test/i.jpg"';
	foreach ( (array) $attr as $k => $v ) {
		$out .= sprintf( ' %s="%s"', $k, esc_attr( $v ) );
	}
	return $out . ' width="1600" height="900">';
}
function get_search_query() { return ''; }                     // phpcs:ignore
function is_front_page() { return true; }                      // phpcs:ignore
function is_singular() { return false; }                       // phpcs:ignore
function is_home() { return false; }                           // phpcs:ignore
function is_archive() { return false; }                        // phpcs:ignore
function is_search() { return false; }                         // phpcs:ignore
function comments_open() { return false; }                     // phpcs:ignore
function have_posts() { return false; }                        // phpcs:ignore
function the_post() {}                                         // phpcs:ignore
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
function get_template_part( $slug, $name = '' ) {              // phpcs:ignore
	$file = MATJAR_PRO_DIR . '/' . $slug . ( $name ? "-{$name}" : '' ) . '.php';

	if ( ! file_exists( $file ) ) {
		throw new RuntimeException( "get_template_part: ملف غير موجود {$slug}" );
	}

	include $file;
}
function get_header() { include MATJAR_PRO_DIR . '/header.php'; } // phpcs:ignore
function get_footer() { include MATJAR_PRO_DIR . '/footer.php'; } // phpcs:ignore

/* ---------- مُحاكي ووكومرس ---------- */

if ( $GLOBALS['mp_smoke_with_wc'] ) {
	class WooCommerce {} // phpcs:ignore
	class MP_Smoke_Cart { // phpcs:ignore
		/** @return int */   public function get_cart_contents_count() { return 3; }
		/** @return bool */  public function is_empty() { return false; }
		/** @return array */ public function get_cart() {
			return array(
				'abc123' => array( 'data' => new WC_Product(), 'quantity' => 2, 'product_id' => 101 ),
			);
		}
		/** @param object $p المنتج. @return string */ public function get_product_price( $p ) { return '179.00 ر.س'; }
		/** @param object $p المنتج. @param int $q الكمية. @return string */ public function get_product_subtotal( $p, $q ) { return '358.00 ر.س'; }
		/** @return string */public function get_cart_subtotal() { return '358.00 ر.س'; }
		/** @return string */public function get_total() { return '358.00 ر.س'; }
		/** @return float */ public function get_displayed_subtotal() { return 358.0; }
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
	function is_cart() { return false; }                                // phpcs:ignore
	function is_checkout() { return (bool) $GLOBALS['mp_smoke_funnel']; }         // phpcs:ignore
	function is_order_received_page() { return false; }                 // phpcs:ignore
	function wc_get_checkout_url() { return 'https://example.test/checkout'; }    // phpcs:ignore
	function wc_get_price_to_display( $p, $a = array() ) { return isset( $a['price'] ) ? (float) $a['price'] : 179.0; } // phpcs:ignore
	function wc_product_class( $class = '', $product = null ) { echo 'class="' . esc_attr( ( is_array( $class ) ? implode( ' ', $class ) : $class ) . ' product type-simple' ) . '"'; } // phpcs:ignore
	function wc_tax_enabled() { return true; }                          // phpcs:ignore
	function wc_coupons_enabled() { return true; }                      // phpcs:ignore
	function the_permalink() { echo 'https://example.test/product/demo'; }        // phpcs:ignore
	function the_ID() { echo '101'; }                                   // phpcs:ignore

	class WC_Product { // phpcs:ignore
		/** @var bool */
		public $variable = false;
		/** @param bool $variable هل المنتج ذو متغيّرات. */
		public function __construct( $variable = false ) { $this->variable = $variable; }
		/** @return int */   public function get_id() { return 101; }
		/** @return bool */  public function is_visible() { return true; }
		/** @return int */   public function get_image_id() { return 12; }
		/** @return string */public function get_name() { return 'عباية كلاسيك مطرّزة'; }
		/** @return bool */  public function is_in_stock() { return true; }
		/** @return bool */  public function is_on_sale() { return true; }
		/** @return string */public function get_regular_price() { return '279'; }
		/** @return string */public function get_price_html() { return '<del>279.00 ر.س</del> <ins>179.00 ر.س</ins>'; }
		/** @return int */   public function get_review_count() { return 42; }
		/** @return string */public function get_average_rating() { return '4.8'; }
		/** @param string $t النوع. @return bool */ public function is_type( $t ) { return $this->variable ? 'variable' === $t : 'simple' === $t; }
		/** @return bool */  public function is_purchasable() { return true; }
		/** @return string */public function get_permalink() { return 'https://example.test/product/demo'; }
		/** @return bool */  public function managing_stock() { return true; }
		/** @return int */   public function get_stock_quantity() { return 3; }
		/** @return string */public function get_short_description() { return '<ul><li>كريب ثقيل لا يشفّ</li><li>تطريز يدوي</li></ul>'; }
		/** @return string */public function get_sku() { return 'AB-01'; }
		/** @return bool */  public function exists() { return true; }
		/** @param string $size المقاس. @return string */ public function get_image( $size = '' ) { return '<img src="https://example.test/i.jpg" width="150" height="188" alt="">'; }
		/** @return bool */  public function is_sold_individually() { return false; }
		/** @return int */   public function get_max_purchase_quantity() { return 10; }
		/** @return int */   public function get_min_purchase_quantity() { return 1; }
		/** @return bool */  public function backorders_require_notification() { return false; }
		/** @param int $q الكمية. @return bool */ public function is_on_backorder( $q = 1 ) { return false; }
		/** @return string */public function single_add_to_cart_text() { return 'أضف إلى السلة'; }
	}
	function is_shop() { return false; }                                // phpcs:ignore
	function is_product_taxonomy() { return false; }                    // phpcs:ignore
	function is_account_page() { return false; }                        // phpcs:ignore
	function wc_get_products( $a = array() ) { return array( new WC_Product(), new WC_Product( true ) ); } // phpcs:ignore
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
	function setup_postdata( $p ) { return true; }                      // phpcs:ignore
	function wp_reset_postdata() {}                                     // phpcs:ignore
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
	'matjar_pro_promo_1_image'           => 13,
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
}

set_error_handler(
	function ( $severity, $message, $file, $line ) {
		throw new ErrorException( $message, 0, $severity, $file, $line );
	}
);

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
} elseif ( $GLOBALS['mp_smoke_funnel'] ) {
	$variant = 'قُمع الدفع';
} else {
	$variant = $GLOBALS['mp_smoke_with_wc'] ? 'مع ووكومرس' : 'بدون ووكومرس';
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
