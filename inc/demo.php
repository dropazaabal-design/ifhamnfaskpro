<?php
/**
 * مستورد المتجر التجريبي.
 *
 * قالبٌ يُركَّب فيظهر فارغاً يترك التاجر أمام شاشة بيضاء لا يعرف منها ما
 * اشترى. هذا المستورد يبني متجراً كاملاً بضغطة: أقساماً ومنتجات وصوراً
 * وصفحات سياسات وقائمة تنقّل وصفحة رئيسية مضبوطة.
 *
 * ثلاث قواعد تحكمه:
 *
 * ١ — لا يُحمَّل إلا في لوحة التحكم. الملف كلّه خلف is_admin()، فزائر
 *     المتجر لا يدفع بايتاً واحداً ثمن أداة لا تخصّه.
 *
 * ٢ — لا أثر بلا رجعة. كل ما يُنشأ يُوسَم بـ _matjar_pro_demo ويُسجَّل
 *     معرّفه، فزرّ الحذف يزيل ما أنشأناه وحده ولا يقترب من بيانات التاجر.
 *
 * ٣ — لا استيراد صامت فوق متجر عامل. إن وجدنا منتجات ليست من صنعنا،
 *     نطلب تأكيداً صريحاً قبل أي كتابة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

require_once MATJAR_PRO_DIR . '/inc/demo-data.php';

const MATJAR_PRO_DEMO_OPTION = 'matjar_pro_demo_created';
const MATJAR_PRO_DEMO_FLAG   = '_matjar_pro_demo';

/**
 * سجلّ ما أنشأه المستورد.
 *
 * @return array{posts: int[], terms: array<int,array{id: int, tax: string}>, attributes: int[], menu: int}
 */
function matjar_pro_demo_log() {
	$log = get_option( MATJAR_PRO_DEMO_OPTION, array() );

	return wp_parse_args(
		is_array( $log ) ? $log : array(),
		array(
			'posts'      => array(),
			'terms'      => array(),
			'attributes' => array(),
			'menu'       => 0,
		)
	);
}

/**
 * هل سبق أن استُورد المحتوى.
 *
 * @return bool
 */
function matjar_pro_demo_imported() {
	$log = matjar_pro_demo_log();

	return ! empty( $log['posts'] );
}

/**
 * عدد المنتجات التي لم ينشئها المستورد.
 *
 * وجودها يعني متجراً عاملاً، فلا نكتب فوقه بلا تأكيد صريح.
 *
 * @return int
 */
function matjar_pro_demo_foreign_products() {
	if ( ! post_type_exists( 'product' ) ) {
		return 0;
	}

	$found = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => MATJAR_PRO_DEMO_FLAG,
					'compare' => 'NOT EXISTS',
				),
			),
		)
	);

	return count( $found );
}

/* ---------------------------------------------------------------------
 * الوسائط
 * ------------------------------------------------------------------ */

/**
 * يُدرج صورة تجريبية في مكتبة الوسائط.
 *
 * الملفّ مشحون مع القالب، فلا تنزيل ولا اتّصال بخادم خارجي: مستوردٌ
 * يحتاج الإنترنت يفشل عند أوّل تاجر خلف جدار ناري.
 *
 * @param string $slug اسم الملفّ بلا امتداد.
 * @param string $title عنوان الصورة.
 * @return int معرّف المرفق أو صفر.
 */
function matjar_pro_demo_image( $slug, $title ) {
	static $seen = array();

	if ( isset( $seen[ $slug ] ) ) {
		return $seen[ $slug ];
	}

	$source = MATJAR_PRO_DIR . '/assets/demo/' . $slug . '.webp';

	if ( ! file_exists( $source ) ) {
		return 0;
	}

	// الملفّ محمّل أصلاً في معظم شاشات اللوحة؛ الطلب غير المشروط يعيد
	// تحميله بلا داعٍ ويكسر أي سياق لا يملك ABSPATH كاملاً.
	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$bits = file_get_contents( $source ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	if ( false === $bits ) {
		return 0;
	}

	$upload = wp_upload_bits( 'matjar-' . $slug . '.webp', null, $bits );

	if ( ! empty( $upload['error'] ) ) {
		return 0;
	}

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => 'image/webp',
			'post_title'     => $title,
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file']
	);

	if ( is_wp_error( $id ) || ! $id ) {
		return 0;
	}

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

	// النصّ البديل: صورة منتج بلا بديل تُسقط الصفحة في أي تدقيق إتاحة.
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	update_post_meta( $id, MATJAR_PRO_DEMO_FLAG, 1 );

	$seen[ $slug ] = (int) $id;

	return (int) $id;
}

/* ---------------------------------------------------------------------
 * الخصائص
 * ------------------------------------------------------------------ */

/**
 * ينشئ خاصّية منتج ويسجّل تصنيفها في الطلب نفسه.
 *
 * ووكومرس يسجّل تصنيفات الخصائص عند init، وخاصّيةٌ تُنشأ بعده لا يوجد
 * تصنيفها حتى إعادة التحميل — فلا يمكن إدراج حدودها في الطلب نفسه.
 * التسجيل اليدوي هنا يحلّ ذلك.
 *
 * @param string $slug معرّف الخاصّية.
 * @param string $label اسمها.
 * @return string اسم التصنيف أو نصّ فارغ.
 */
function matjar_pro_demo_attribute( $slug, $label ) {
	if ( ! function_exists( 'wc_create_attribute' ) || ! function_exists( 'wc_attribute_taxonomy_name' ) ) {
		return '';
	}

	$taxonomy = wc_attribute_taxonomy_name( $slug );
	$existing = wc_attribute_taxonomy_id_by_name( $slug );

	if ( ! $existing ) {
		$id = wc_create_attribute(
			array(
				'name'         => $label,
				'slug'         => $slug,
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			)
		);

		if ( is_wp_error( $id ) ) {
			return '';
		}

		$log                 = matjar_pro_demo_log();
		$log['attributes'][] = (int) $id;
		update_option( MATJAR_PRO_DEMO_OPTION, $log, false );
	}

	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy(
			$taxonomy,
			'product',
			array(
				'hierarchical' => false,
				'show_ui'      => false,
				'query_var'    => true,
				'rewrite'      => false,
				'public'       => false,
			)
		);
	}

	return $taxonomy;
}

/* ---------------------------------------------------------------------
 * الاستيراد
 * ------------------------------------------------------------------ */

/**
 * يبني المتجر التجريبي كاملاً.
 *
 * @return array{products: int, categories: int, pages: int, images: int}|WP_Error
 */
function matjar_pro_demo_import() {
	if ( ! matjar_pro_has_woocommerce() ) {
		return new WP_Error( 'no_wc', __( 'ووكومرس غير مفعّل. فعّله أوّلاً ثم أعد المحاولة.', 'matjar-pro' ) );
	}

	@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_set_time_limit

	$log   = matjar_pro_demo_log();
	$count = array(
		'products'   => 0,
		'categories' => 0,
		'pages'      => 0,
		'images'     => 0,
	);

	$remember = static function ( $id ) use ( &$log ) {
		$log['posts'][] = (int) $id;
		update_post_meta( $id, MATJAR_PRO_DEMO_FLAG, 1 );
	};

	/* ---------- الأقسام ---------- */

	$cats = array();

	foreach ( matjar_pro_demo_categories() as $slug => $cat ) {
		$term = term_exists( $slug, 'product_cat' );

		if ( ! $term ) {
			$term = wp_insert_term(
				$cat['name'],
				'product_cat',
				array(
					'slug'        => $slug,
					'description' => $cat['description'],
				)
			);

			if ( is_wp_error( $term ) ) {
				continue;
			}

			$log['terms'][] = array(
				'id'  => (int) $term['term_id'],
				'tax' => 'product_cat',
			);

			$count['categories']++;
		}

		$cats[ $slug ] = (int) $term['term_id'];

		$thumb = matjar_pro_demo_image( $cat['image'], $cat['name'] );

		if ( $thumb ) {
			update_term_meta( $cats[ $slug ], 'thumbnail_id', $thumb );
			$count['images']++;
		}
	}

	/* ---------- الخصائص ---------- */

	$taxonomies = array();

	foreach ( matjar_pro_demo_attributes() as $slug => $meta ) {
		$taxonomy = matjar_pro_demo_attribute( $slug, $meta['label'] );

		if ( ! $taxonomy ) {
			continue;
		}

		$taxonomies[ $slug ] = array(
			'taxonomy' => $taxonomy,
			'terms'    => array(),
		);

		foreach ( $meta['terms'] as $term_slug => $term_label ) {
			$term = term_exists( $term_slug, $taxonomy );

			if ( ! $term ) {
				$term = wp_insert_term( $term_label, $taxonomy, array( 'slug' => $term_slug ) );

				if ( is_wp_error( $term ) ) {
					continue;
				}

				$log['terms'][] = array(
					'id'  => (int) $term['term_id'],
					'tax' => $taxonomy,
				);
			}

			$taxonomies[ $slug ]['terms'][ $term_slug ] = (int) $term['term_id'];
		}
	}

	/* ---------- المنتجات ---------- */

	foreach ( matjar_pro_demo_products() as $item ) {
		$variable = ! empty( $item['variable'] ) && isset( $taxonomies[ $item['variable'] ] );
		$product  = $variable ? new WC_Product_Variable() : new WC_Product_Simple();

		$product->set_name( $item['name'] );
		$product->set_status( 'publish' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_short_description( isset( $item['excerpt'] ) ? $item['excerpt'] : '' );
		$product->set_description( isset( $item['body'] ) ? $item['body'] : ( isset( $item['excerpt'] ) ? $item['excerpt'] : '' ) );
		$product->set_featured( ! empty( $item['featured'] ) );

		if ( isset( $cats[ $item['cat'] ] ) ) {
			$product->set_category_ids( array( $cats[ $item['cat'] ] ) );
		}

		$product->set_manage_stock( false );
		$product->set_stock_status( isset( $item['stock'] ) ? $item['stock'] : 'instock' );

		// الصور: الأولى بارزة والباقي معرِض.
		$gallery = array();

		foreach ( (array) $item['images'] as $index => $slug ) {
			$image = matjar_pro_demo_image( $slug, $item['name'] );

			if ( ! $image ) {
				continue;
			}

			$count['images']++;

			if ( 0 === $index ) {
				$product->set_image_id( $image );
			} else {
				$gallery[] = $image;
			}
		}

		$product->set_gallery_image_ids( $gallery );

		if ( $variable ) {
			$meta  = $taxonomies[ $item['variable'] ];
			$attr  = new WC_Product_Attribute();
			$attr->set_id( wc_attribute_taxonomy_id_by_name( $item['variable'] ) );
			$attr->set_name( $meta['taxonomy'] );
			$attr->set_options( array_values( $meta['terms'] ) );
			$attr->set_visible( true );
			$attr->set_variation( true );

			$product->set_attributes( array( $attr ) );

			$id = $product->save();
		} else {
			$product->set_regular_price( (string) $item['price'] );

			if ( ! empty( $item['sale'] ) ) {
				$product->set_sale_price( (string) $item['sale'] );
			}

			$id = $product->save();
		}

		if ( ! $id ) {
			continue;
		}

		$remember( $id );
		$count['products']++;

		if ( ! $variable ) {
			continue;
		}

		// النُسَخ: لكل حدٍّ نسخة بسعرها، والمقاسات الكبيرة أغلى قليلاً كما
		// يفعل تجّار الملابس فعلاً.
		$meta  = $taxonomies[ $item['variable'] ];
		$step  = 0;

		foreach ( $meta['terms'] as $term_slug => $term_id ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $id );
			$variation->set_attributes( array( $meta['taxonomy'] => $term_slug ) );
			$variation->set_status( 'publish' );

			$price = (float) $item['price'] + ( 'size' === $item['variable'] ? $step * 20 : 0 );

			$variation->set_regular_price( (string) $price );

			if ( ! empty( $item['sale'] ) ) {
				$variation->set_sale_price( (string) ( (float) $item['sale'] + ( 'size' === $item['variable'] ? $step * 20 : 0 ) ) );
			}

			$variation->set_stock_status( 'instock' );

			$variation_id = $variation->save();

			if ( $variation_id ) {
				$remember( $variation_id );
			}

			$step++;
		}

		// إعادة التزامن حتى يعرف الأب مدى أسعاره.
		WC_Product_Variable::sync( $id );
	}

	/* ---------- الصفحات ---------- */

	$pages = array();

	foreach ( matjar_pro_demo_pages() as $slug => $page ) {
		$existing = get_page_by_path( $slug );

		if ( $existing ) {
			$pages[ $slug ] = (int) $existing->ID;

			continue;
		}

		$id = wp_insert_post(
			array(
				'post_title'   => $page['title'],
				'post_name'    => $slug,
				'post_content' => $page['body'],
				'post_status'  => 'publish',
				'post_type'    => 'page',
			)
		);

		if ( is_wp_error( $id ) || ! $id ) {
			continue;
		}

		$remember( $id );
		$pages[ $slug ] = (int) $id;
		$count['pages']++;
	}

	/* ---------- الصفحة الرئيسية والقائمة ---------- */

	$home = get_page_by_path( 'matjar-home' );

	if ( ! $home ) {
		$home_id = wp_insert_post(
			array(
				'post_title'   => __( 'الرئيسية', 'matjar-pro' ),
				'post_name'    => 'matjar-home',
				'post_content' => '',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			)
		);

		if ( ! is_wp_error( $home_id ) && $home_id ) {
			$remember( $home_id );
			$count['pages']++;

			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_id );
		}
	}

	if ( ! $log['menu'] ) {
		$menu_id = wp_create_nav_menu( __( 'قائمة المتجر', 'matjar-pro' ) );

		if ( ! is_wp_error( $menu_id ) ) {
			$log['menu'] = (int) $menu_id;

			foreach ( matjar_pro_demo_categories() as $slug => $cat ) {
				if ( ! isset( $cats[ $slug ] ) ) {
					continue;
				}

				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => $cat['name'],
						'menu-item-object'    => 'product_cat',
						'menu-item-object-id' => $cats[ $slug ],
						'menu-item-type'      => 'taxonomy',
						'menu-item-status'    => 'publish',
					)
				);
			}

			foreach ( array( 'about', 'shipping', 'contact' ) as $slug ) {
				if ( ! isset( $pages[ $slug ] ) ) {
					continue;
				}

				wp_update_nav_menu_item(
					$menu_id,
					0,
					array(
						'menu-item-title'     => get_the_title( $pages[ $slug ] ),
						'menu-item-object'    => 'page',
						'menu-item-object-id' => $pages[ $slug ],
						'menu-item-type'      => 'post_type',
						'menu-item-status'    => 'publish',
					)
				);
			}

			$locations             = (array) get_theme_mod( 'nav_menu_locations', array() );
			$locations['primary']  = (int) $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	update_option( MATJAR_PRO_DEMO_OPTION, $log, false );

	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients();
	}

	delete_transient( 'matjar_pro_price_bounds' );

	return $count;
}

/**
 * يحذف ما أنشأه المستورد ولا يقترب من غيره.
 *
 * @return int عدد العناصر المحذوفة.
 */
function matjar_pro_demo_remove() {
	$log     = matjar_pro_demo_log();
	$removed = 0;

	foreach ( $log['posts'] as $id ) {
		// الوسم شرطٌ ثانٍ: لو أعاد التاجر استعمال المعرّف لسبب ما، لا نحذف
		// منشوراً ليس لنا.
		if ( get_post( $id ) && get_post_meta( $id, MATJAR_PRO_DEMO_FLAG, true ) ) {
			wp_delete_post( $id, true );
			$removed++;
		}
	}

	// المرفقات تُحذف بوسمها لا بسجلّها: بعضها أُنشئ لأقسام لا لمنتجات.
	$attachments = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => MATJAR_PRO_DEMO_FLAG, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		)
	);

	foreach ( $attachments as $id ) {
		wp_delete_attachment( $id, true );
		$removed++;
	}

	foreach ( $log['terms'] as $term ) {
		if ( term_exists( (int) $term['id'], $term['tax'] ) ) {
			wp_delete_term( (int) $term['id'], $term['tax'] );
			$removed++;
		}
	}

	foreach ( $log['attributes'] as $id ) {
		if ( function_exists( 'wc_delete_attribute' ) ) {
			wc_delete_attribute( (int) $id );
			$removed++;
		}
	}

	if ( $log['menu'] && is_nav_menu( $log['menu'] ) ) {
		wp_delete_nav_menu( $log['menu'] );
		$removed++;
	}

	delete_option( MATJAR_PRO_DEMO_OPTION );

	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients();
	}

	delete_transient( 'matjar_pro_price_bounds' );

	return $removed;
}
