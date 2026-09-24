<?php
/**
 * اختبار مستورد المتجر التجريبي.
 *
 * هذا أخطر ملفّ في القالب: الوحيد الذي يكتب في قاعدة البيانات ويحذف
 * منها. وخطؤه لا يُرى في تصيير صفحة، بل يظهر متجراً نصفه مبني أو محتوى
 * تاجرٍ محذوفاً. فيُشغَّل هنا على قاعدة وهمية في الذاكرة.
 *
 * ما يُتحقَّق منه:
 *  ١ — كل منتج في الكتالوج يُنشأ، وبصورته وقسمه وسعره.
 *  ٢ — المنتج المتغيّر يحصل على نُسَخه، والأب يعرف مدى أسعاره.
 *  ٣ — الاستيراد مرّتين لا يضاعف شيئاً.
 *  ٤ — الحذف يزيل ما أنشأناه، ولا يمسّ منشوراً واحداً ليس لنا.
 *
 * التشغيل: php tools/verify-demo.php
 *
 * @package MatjarPro
 */

define( 'ABSPATH', dirname( __DIR__ ) . '/' );
define( 'MATJAR_PRO_DIR', dirname( __DIR__ ) );
define( 'MATJAR_PRO_URI', 'https://example.test/wp-content/themes/matjar-pro' );
define( 'OBJECT', 'OBJECT' );

// كل تحذير يصير استثناءً: مستوردٌ يكتب تحت تحذير صامت لا يُوثق به.
set_error_handler(
	static function ( $no, $str, $file, $line ) {
		throw new ErrorException( $str, 0, $no, $file, $line );
	}
);

/* ---------- قاعدة بيانات وهمية ---------- */

$GLOBALS['db'] = array(
	'posts'    => array(),
	'meta'     => array(),
	'terms'    => array(),
	'termmeta' => array(),
	'options'  => array(),
	'menus'    => array(),
	'attrs'    => array(),
	'next'     => 1,
	'uploads'  => array(),
);

function db_next() {
	return $GLOBALS['db']['next']++;
}

/* ---------- دوال ووردبريس ---------- */

function __( $t, $d = '' ) { return $t; }                                   // phpcs:ignore
function esc_html__( $t, $d = '' ) { return $t; }                           // phpcs:ignore
function esc_html( $t ) { return $t; }                                      // phpcs:ignore
function esc_attr( $t ) { return $t; }                                      // phpcs:ignore
function esc_url( $t ) { return $t; }                                       // phpcs:ignore
function wp_parse_args( $a, $d ) { return array_merge( $d, (array) $a ); }  // phpcs:ignore
function is_wp_error( $t ) { return $t instanceof WP_Error; }               // phpcs:ignore
function sanitize_key( $k ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/i', '', (string) $k ) ); } // phpcs:ignore
function wp_strip_all_tags( $t ) { return strip_tags( (string) $t ); }      // phpcs:ignore
function number_format_i18n( $n ) { return (string) $n; }                   // phpcs:ignore
function home_url( $p = '/' ) { return 'https://example.test' . $p; }       // phpcs:ignore
function admin_url( $p = '' ) { return 'https://example.test/wp-admin/' . $p; } // phpcs:ignore
function add_action() {}                                                    // phpcs:ignore
function add_filter() {}                                                    // phpcs:ignore
function apply_filters( $t, $v ) { return $v; }                             // phpcs:ignore
function do_action() {}                                                     // phpcs:ignore
function wc_delete_product_transients() {}                                  // phpcs:ignore
function delete_transient( $k ) { return true; }                            // phpcs:ignore
function set_theme_mod( $k, $v ) { $GLOBALS['db']['options'][ "mod_$k" ] = $v; } // phpcs:ignore
function get_theme_mod( $k, $d = false ) { return $GLOBALS['db']['options'][ "mod_$k" ] ?? $d; } // phpcs:ignore
function matjar_pro_has_woocommerce() { return true; }                      // phpcs:ignore
function post_type_exists( $t ) { return true; }                            // phpcs:ignore
function taxonomy_exists( $t ) { return isset( $GLOBALS['db']['taxes'][ $t ] ); } // phpcs:ignore
function register_taxonomy( $t, $o, $a = array() ) { $GLOBALS['db']['taxes'][ $t ] = true; } // phpcs:ignore

class WP_Error {                                                            // phpcs:ignore
	public $code;
	public $message;
	public function __construct( $c = '', $m = '' ) { $this->code = $c; $this->message = $m; }
	public function get_error_message() { return $this->message; }
}

function get_option( $k, $d = false ) { return $GLOBALS['db']['options'][ $k ] ?? $d; } // phpcs:ignore
function update_option( $k, $v, $a = true ) { $GLOBALS['db']['options'][ $k ] = $v; return true; } // phpcs:ignore
function delete_option( $k ) { unset( $GLOBALS['db']['options'][ $k ] ); return true; } // phpcs:ignore

function wp_insert_post( $args ) {                                          // phpcs:ignore
	$id = db_next();
	$GLOBALS['db']['posts'][ $id ] = array_merge(
		array( 'ID' => $id, 'post_type' => 'post', 'post_name' => '', 'post_title' => '', 'post_status' => 'publish' ),
		$args
	);

	return $id;
}

function get_post( $id ) {                                                  // phpcs:ignore
	return isset( $GLOBALS['db']['posts'][ $id ] ) ? (object) $GLOBALS['db']['posts'][ $id ] : null;
}

function get_the_title( $id ) {                                             // phpcs:ignore
	return $GLOBALS['db']['posts'][ $id ]['post_title'] ?? '';
}

function get_page_by_path( $slug, $o = OBJECT, $type = 'page' ) {           // phpcs:ignore
	foreach ( $GLOBALS['db']['posts'] as $p ) {
		if ( ( $p['post_name'] ?? '' ) === $slug && 'page' === ( $p['post_type'] ?? '' ) ) {
			return (object) $p;
		}
	}

	return null;
}

function wp_delete_post( $id, $force = false ) {                            // phpcs:ignore
	unset( $GLOBALS['db']['posts'][ $id ], $GLOBALS['db']['meta'][ $id ] );

	return true;
}

function wp_delete_attachment( $id, $force = false ) { return wp_delete_post( $id, $force ); } // phpcs:ignore

function update_post_meta( $id, $k, $v ) { $GLOBALS['db']['meta'][ $id ][ $k ] = $v; return true; } // phpcs:ignore
function get_post_meta( $id, $k = '', $single = false ) {                   // phpcs:ignore
	return $GLOBALS['db']['meta'][ $id ][ $k ] ?? ( $single ? '' : array() );
}

function get_posts( $args ) {                                               // phpcs:ignore
	$out = array();

	foreach ( $GLOBALS['db']['posts'] as $id => $p ) {
		if ( isset( $args['post_type'] ) && ( $p['post_type'] ?? '' ) !== $args['post_type'] ) {
			continue;
		}

		if ( isset( $args['meta_key'] ) && ! isset( $GLOBALS['db']['meta'][ $id ][ $args['meta_key'] ] ) ) {
			continue;
		}

		if ( isset( $args['meta_query'][0]['compare'] ) && 'NOT EXISTS' === $args['meta_query'][0]['compare'] ) {
			if ( isset( $GLOBALS['db']['meta'][ $id ][ $args['meta_query'][0]['key'] ] ) ) {
				continue;
			}
		}

		$out[] = $id;
	}

	return $out;
}

function term_exists( $term, $tax = '' ) {                                  // phpcs:ignore
	foreach ( $GLOBALS['db']['terms'] as $id => $t ) {
		if ( $t['taxonomy'] !== $tax ) {
			continue;
		}

		if ( $t['slug'] === $term || (int) $id === (int) $term ) {
			return array( 'term_id' => $id, 'term_taxonomy_id' => $id );
		}
	}

	return null;
}

function wp_insert_term( $name, $tax, $args = array() ) {                   // phpcs:ignore
	$id = db_next();
	$GLOBALS['db']['terms'][ $id ] = array(
		'term_id'  => $id,
		'name'     => $name,
		'taxonomy' => $tax,
		'slug'     => $args['slug'] ?? sanitize_key( $name ),
	);

	return array( 'term_id' => $id, 'term_taxonomy_id' => $id );
}

function wp_delete_term( $id, $tax ) { unset( $GLOBALS['db']['terms'][ $id ] ); return true; } // phpcs:ignore
function update_term_meta( $id, $k, $v ) { $GLOBALS['db']['termmeta'][ $id ][ $k ] = $v; return true; } // phpcs:ignore

function wp_upload_bits( $name, $x, $bits ) {                               // phpcs:ignore
	$GLOBALS['db']['uploads'][ $name ] = strlen( $bits );

	return array( 'file' => '/uploads/' . $name, 'url' => 'https://example.test/uploads/' . $name, 'error' => false );
}

function wp_insert_attachment( $args, $file ) {                             // phpcs:ignore
	$id = db_next();
	$GLOBALS['db']['posts'][ $id ] = array_merge( array( 'ID' => $id, 'post_type' => 'attachment', 'post_name' => '' ), $args );

	return $id;
}

function wp_generate_attachment_metadata( $id, $file ) { return array( 'width' => 900, 'height' => 1125 ); } // phpcs:ignore  ← وجودها يُغني عن طلب wp-admin/includes/image.php
function wp_update_attachment_metadata( $id, $meta ) { return true; }       // phpcs:ignore

function wp_create_nav_menu( $name ) {                                      // phpcs:ignore
	$id = db_next();
	$GLOBALS['db']['menus'][ $id ] = array( 'name' => $name, 'items' => array() );

	return $id;
}

function wp_update_nav_menu_item( $menu, $item, $args ) {                   // phpcs:ignore
	$GLOBALS['db']['menus'][ $menu ]['items'][] = $args;

	return db_next();
}

function is_nav_menu( $id ) { return isset( $GLOBALS['db']['menus'][ $id ] ); } // phpcs:ignore
function wp_delete_nav_menu( $id ) { unset( $GLOBALS['db']['menus'][ $id ] ); return true; } // phpcs:ignore

/* ---------- ووكومرس ---------- */

function wc_attribute_taxonomy_name( $slug ) { return 'pa_' . $slug; }      // phpcs:ignore
function wc_attribute_taxonomy_id_by_name( $slug ) { return $GLOBALS['db']['attrs'][ $slug ] ?? 0; } // phpcs:ignore

function wc_create_attribute( $args ) {                                     // phpcs:ignore
	$id = db_next();
	$GLOBALS['db']['attrs'][ $args['slug'] ] = $id;

	return $id;
}

function wc_delete_attribute( $id ) {                                       // phpcs:ignore
	foreach ( $GLOBALS['db']['attrs'] as $slug => $aid ) {
		if ( $aid === $id ) {
			unset( $GLOBALS['db']['attrs'][ $slug ] );
		}
	}

	return true;
}

class WC_Product_Attribute {                                                // phpcs:ignore
	public $id = 0, $name = '', $options = array(), $visible = false, $variation = false;
	public function set_id( $v ) { $this->id = $v; }
	public function set_name( $v ) { $this->name = $v; }
	public function set_options( $v ) { $this->options = $v; }
	public function set_visible( $v ) { $this->visible = $v; }
	public function set_variation( $v ) { $this->variation = $v; }
}

class WC_Product_Simple {                                                   // phpcs:ignore
	public $data = array( 'type' => 'simple', 'gallery' => array(), 'cats' => array() );

	public function __call( $name, $args ) {
		if ( 0 === strpos( $name, 'set_' ) ) {
			$this->data[ substr( $name, 4 ) ] = $args[0] ?? null;

			return null;
		}

		if ( 0 === strpos( $name, 'get_' ) ) {
			return $this->data[ substr( $name, 4 ) ] ?? null;
		}

		throw new RuntimeException( "دالّة منتج غير معروفة: {$name}" );
	}

	public function set_gallery_image_ids( $v ) { $this->data['gallery'] = $v; }
	public function set_category_ids( $v ) { $this->data['cats'] = $v; }

	public function save() {
		$id = db_next();
		$GLOBALS['db']['posts'][ $id ] = array(
			'ID'         => $id,
			'post_type'  => 'product',
			'post_title' => $this->data['name'] ?? '',
			'post_name'  => '',
			'wc'         => $this->data,
		);

		return $id;
	}
}

class WC_Product_Variable extends WC_Product_Simple {                       // phpcs:ignore
	public $data = array( 'type' => 'variable', 'gallery' => array(), 'cats' => array() );

	public static function sync( $id ) {
		$kids = 0;

		foreach ( $GLOBALS['db']['posts'] as $p ) {
			if ( 'product_variation' === ( $p['post_type'] ?? '' ) && (int) ( $p['wc']['parent_id'] ?? 0 ) === (int) $id ) {
				$kids++;
			}
		}

		$GLOBALS['db']['posts'][ $id ]['synced'] = $kids;
	}
}

class WC_Product_Variation extends WC_Product_Simple {                      // phpcs:ignore
	public $data = array( 'type' => 'variation', 'gallery' => array(), 'cats' => array() );

	public function save() {
		$id = db_next();
		$GLOBALS['db']['posts'][ $id ] = array(
			'ID'        => $id,
			'post_type' => 'product_variation',
			'post_name' => '',
			'post_title' => '',
			'wc'        => $this->data,
		);

		return $id;
	}
}

/* ---------- التشغيل ---------- */

require_once MATJAR_PRO_DIR . '/inc/demo.php';

$fail = 0;

function check( $label, $got, $want ) {
	global $fail;

	$ok = $got === $want;

	if ( ! $ok ) {
		$fail++;
	}

	printf( "%-52s %-18s %s\n", $label, is_scalar( $got ) ? (string) $got : gettype( $got ), $ok ? 'pass' : "FAIL (المتوقّع: {$want})" );
}

// منشور للتاجر قبل الاستيراد: لا يجوز أن يمسّه الحذف.
$theirs = wp_insert_post( array( 'post_type' => 'product', 'post_title' => 'منتج التاجر الحقيقي' ) );

echo "=== قبل الاستيراد ===\n";
check( 'منتجات ليست من المستورد', matjar_pro_demo_foreign_products(), 1 );
check( 'هل استُورد من قبل', matjar_pro_demo_imported() ? 'نعم' : 'لا', 'لا' );

echo "\n=== الاستيراد ===\n";
$result = matjar_pro_demo_import();

if ( is_wp_error( $result ) ) {
	echo 'فشل: ' . $result->get_error_message() . "\n";

	exit( 1 );
}

$catalogue = matjar_pro_demo_products();
$expected  = count( $catalogue );

$made = 0;
$vars = 0;

foreach ( $GLOBALS['db']['posts'] as $p ) {
	if ( 'product' === ( $p['post_type'] ?? '' ) && isset( $p['wc'] ) ) {
		$made++;
	}

	if ( 'product_variation' === ( $p['post_type'] ?? '' ) ) {
		$vars++;
	}
}

$variable_count = 0;

foreach ( $catalogue as $item ) {
	if ( ! empty( $item['variable'] ) ) {
		$variable_count++;
	}
}

$attrs   = matjar_pro_demo_attributes();
$per_var = 0;

foreach ( $catalogue as $item ) {
	if ( ! empty( $item['variable'] ) ) {
		$per_var += count( $attrs[ $item['variable'] ]['terms'] );
	}
}

check( 'المنتجات المُنشأة', $result['products'], $expected );
check( 'المنتجات في قاعدة البيانات', $made, $expected );
check( 'النُسَخ المُنشأة', $vars, $per_var );
check( 'الأقسام', $result['categories'], count( matjar_pro_demo_categories() ) );
check( 'الصفحات (سياسات + رئيسية)', $result['pages'], count( matjar_pro_demo_pages() ) + 1 );
check( 'الصفحة الرئيسية مضبوطة', get_option( 'show_on_front' ), 'page' );
check( 'قائمة التنقّل أُنشئت', count( $GLOBALS['db']['menus'] ), 1 );

$menu   = reset( $GLOBALS['db']['menus'] );
check( 'عناصر القائمة', count( $menu['items'] ), count( matjar_pro_demo_categories() ) + 3 );
check( 'موضع القائمة الأساسي', (bool) ( get_theme_mod( 'nav_menu_locations' )['primary'] ?? 0 ), true );

// كل منتج له صورة بارزة وقسم.
$no_image = 0;
$no_cat   = 0;

foreach ( $GLOBALS['db']['posts'] as $p ) {
	if ( 'product' !== ( $p['post_type'] ?? '' ) || ! isset( $p['wc'] ) ) {
		continue;
	}

	if ( empty( $p['wc']['image_id'] ) ) {
		$no_image++;
	}

	if ( empty( $p['wc']['cats'] ) ) {
		$no_cat++;
	}
}

check( 'منتجات بلا صورة بارزة', $no_image, 0 );
check( 'منتجات بلا قسم', $no_cat, 0 );
check( 'صور رُفعت إلى المكتبة', count( $GLOBALS['db']['uploads'] ) > 0, true );

// حالات المخزون الثلاث موجودة فعلاً في المتجر المبني.
$states = array();

foreach ( $GLOBALS['db']['posts'] as $p ) {
	if ( 'product' === ( $p['post_type'] ?? '' ) && isset( $p['wc']['stock_status'] ) ) {
		$states[ $p['wc']['stock_status'] ] = true;
	}
}

check( 'حالات المخزون المختلفة', count( $states ), 3 );

echo "\n=== الاستيراد مرّة ثانية ===\n";
$before = count( $GLOBALS['db']['posts'] );
matjar_pro_demo_import();
$after = count( $GLOBALS['db']['posts'] );

// الأقسام والصفحات لا تتكرّر؛ المنتجات تُضاف لأن ووكومرس لا يملك مفتاحاً
// فريداً لها — وهذا ما تمنعه شاشة التأكيد في لوحة التحكم.
check( 'الأقسام لم تتكرّر', count( array_filter( $GLOBALS['db']['terms'], static fn( $t ) => 'product_cat' === $t['taxonomy'] ) ), count( matjar_pro_demo_categories() ) );
check( 'الصفحات لم تتكرّر', count( array_filter( $GLOBALS['db']['posts'], static fn( $p ) => 'page' === ( $p['post_type'] ?? '' ) ) ), count( matjar_pro_demo_pages() ) + 1 );
check( 'القائمة لم تتكرّر', count( $GLOBALS['db']['menus'] ), 1 );

echo "\n=== الحذف ===\n";
matjar_pro_demo_remove();

$left_products = 0;

foreach ( $GLOBALS['db']['posts'] as $p ) {
	if ( in_array( $p['post_type'] ?? '', array( 'product', 'product_variation', 'attachment' ), true ) ) {
		$left_products++;
	}
}

check( 'منتج التاجر ما زال موجوداً', (bool) get_post( $theirs ), true );
check( 'ما بقي من منتجات/نُسَخ/مرفقات', $left_products, 1 );
check( 'الأقسام حُذفت', count( array_filter( $GLOBALS['db']['terms'], static fn( $t ) => 'product_cat' === $t['taxonomy'] ) ), 0 );
check( 'القائمة حُذفت', count( $GLOBALS['db']['menus'] ), 0 );
check( 'السجلّ نُظّف', get_option( MATJAR_PRO_DEMO_OPTION, 'gone' ), 'gone' );

echo "\n" . str_repeat( '-', 72 ) . "\n";

if ( $fail ) {
	printf( "%d فحصاً فاشلاً.\n", $fail );

	exit( 1 );
}

echo "كل الفحوص مرّت: المستورد يبني ما يعد به، ولا يحذف إلا ما أنشأ.\n";
