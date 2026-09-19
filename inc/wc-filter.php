<?php
/**
 * فلترة المنتجات.
 *
 * تعتمد على متغيّرات استعلام ووكومرس الأصلية (filter_pa_* و min_price و
 * max_price)، وهي من نواة ووكومرس لا من إضافة: WC_Query يقرؤها ويطبّقها
 * على استعلام المنتجات بنفسه.
 *
 * النتيجة: الفلترة تعمل كروابط ونماذج GET عادية بلا JavaScript إطلاقاً،
 * وطبقة AJAX تُحسّنها فقط ولا تشترطها.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * خصائص المنتجات القابلة للفلترة.
 *
 * تُستبعد الخصائص التي لا مُصطلحات لها، فلا يُطبع قسم فلترة فارغ.
 *
 * @return array<string,array{label: string, terms: WP_Term[]}>
 */
function matjar_pro_filter_attributes() {
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return array();
	}

	$out = array();

	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		$out[ $taxonomy ] = array(
			'label' => wc_attribute_label( $taxonomy ),
			'terms' => $terms,
		);
	}

	/**
	 * تصفية الخصائص المعروضة في لوح الفلترة.
	 *
	 * @param array $out الخصائص.
	 */
	return apply_filters( 'matjar_pro_filter_attributes', $out );
}

/**
 * المُصطلحات المختارة حالياً لخاصية.
 *
 * ووكومرس يتوقّع قيمة واحدة مفصولة بفواصل (filter_pa_size=s,m) لا مصفوفة،
 * فنقرأها بالطريقة نفسها التي يقرؤها بها.
 *
 * @param string $taxonomy التصنيف.
 * @return string[]
 */
function matjar_pro_chosen_filter_terms( $taxonomy ) {
	$key = 'filter_' . str_replace( 'pa_', '', $taxonomy );

	if ( empty( $_GET[ $key ] ) || ! is_string( $_GET[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return array();
	}

	$raw = sanitize_text_field( wp_unslash( $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	return array_filter( array_map( 'sanitize_title', explode( ',', $raw ) ) );
}

/**
 * العنوان الأساسي الذي تُبنى عليه روابط الفلترة.
 *
 * منقول عن منطق ودجت ووكومرس نفسه، فتبقى الروابط متوافقة مع ما يتوقّعه.
 *
 * @return string
 */
function matjar_pro_filter_base_url() {
	if ( defined( 'SHOP_IS_ON_FRONT' ) ) {
		$link = home_url( '/' );
	} elseif ( is_post_type_archive( 'product' ) || is_page( wc_get_page_id( 'shop' ) ) ) {
		$link = get_post_type_archive_link( 'product' );
	} elseif ( is_product_category() || is_product_tag() || is_product_taxonomy() ) {
		$term = get_queried_object();
		$link = $term instanceof WP_Term ? get_term_link( $term ) : home_url( '/' );

		if ( is_wp_error( $link ) ) {
			$link = home_url( '/' );
		}
	} else {
		$link = home_url( '/' );
	}

	// يُحفظ الترتيب والسعر والبحث، وتُسقط الصفحة: أي تغيير في الفلترة يعيد
	// الزائر إلى الصفحة الأولى، وإلا رأى «لا نتائج» في صفحة لم تعد موجودة.
	$keep = array();

	foreach ( array( 'orderby', 'min_price', 'max_price', 's', 'post_type', 'product_cat' ) as $arg ) {
		if ( isset( $_GET[ $arg ] ) && is_string( $_GET[ $arg ] ) && '' !== $_GET[ $arg ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$keep[ $arg ] = sanitize_text_field( wp_unslash( $_GET[ $arg ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	}

	foreach ( array_keys( matjar_pro_filter_attributes() ) as $taxonomy ) {
		$chosen = matjar_pro_chosen_filter_terms( $taxonomy );

		if ( ! empty( $chosen ) ) {
			$keep[ 'filter_' . str_replace( 'pa_', '', $taxonomy ) ] = implode( ',', $chosen );
		}
	}

	return empty( $keep ) ? $link : add_query_arg( $keep, $link );
}

/**
 * رابط يضيف مُصطلحاً إلى الفلترة أو يزيله منها.
 *
 * @param string $taxonomy التصنيف.
 * @param string $slug     معرّف المُصطلح.
 * @return string
 */
function matjar_pro_filter_toggle_url( $taxonomy, $slug ) {
	$key    = 'filter_' . str_replace( 'pa_', '', $taxonomy );
	$chosen = matjar_pro_chosen_filter_terms( $taxonomy );
	$slug   = sanitize_title( $slug );

	if ( in_array( $slug, $chosen, true ) ) {
		$chosen = array_values( array_diff( $chosen, array( $slug ) ) );
	} else {
		$chosen[] = $slug;
	}

	$base = matjar_pro_filter_base_url();

	return empty( $chosen )
		? remove_query_arg( $key, $base )
		: add_query_arg( $key, implode( ',', $chosen ), $base );
}

/**
 * رابط تفريغ كل الفلاتر.
 *
 * @return string
 */
function matjar_pro_filter_clear_url() {
	$args = array( 'min_price', 'max_price' );

	foreach ( array_keys( matjar_pro_filter_attributes() ) as $taxonomy ) {
		$args[] = 'filter_' . str_replace( 'pa_', '', $taxonomy );
	}

	return remove_query_arg( $args, matjar_pro_filter_base_url() );
}

/**
 * حدّا السعر في المتجر.
 *
 * يُقرآن من جدول ووكومرس المُهيَّأ للبحث (wc_product_meta_lookup) لا بمرور
 * على المنتجات، ويُخزَّنان مؤقتاً: هذا استعلام لا يتغيّر إلا بتغيّر الأسعار.
 *
 * @return array{min: float, max: float}|null
 */
function matjar_pro_price_bounds() {
	global $wpdb;

	$cached = get_transient( 'matjar_pro_price_bounds' );

	if ( is_array( $cached ) ) {
		return $cached;
	}

	$table = $wpdb->prefix . 'wc_product_meta_lookup';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$row = $wpdb->get_row(
		"SELECT MIN( min_price ) AS min_price, MAX( max_price ) AS max_price
		 FROM {$table} AS lookup
		 INNER JOIN {$wpdb->posts} AS posts ON posts.ID = lookup.product_id
		 WHERE posts.post_status = 'publish'"
	);

	if ( ! $row || null === $row->min_price || null === $row->max_price ) {
		return null;
	}

	$bounds = array(
		'min' => (float) floor( (float) $row->min_price ),
		'max' => (float) ceil( (float) $row->max_price ),
	);

	if ( $bounds['max'] <= $bounds['min'] ) {
		return null;
	}

	set_transient( 'matjar_pro_price_bounds', $bounds, 12 * HOUR_IN_SECONDS );

	return $bounds;
}

/**
 * يُفرِّغ الحدّين المخزّنين عند تغيّر سعر أي منتج.
 */
function matjar_pro_flush_price_bounds() {
	delete_transient( 'matjar_pro_price_bounds' );
}
add_action( 'woocommerce_after_product_object_save', 'matjar_pro_flush_price_bounds' );
add_action( 'woocommerce_delete_product_transients', 'matjar_pro_flush_price_bounds' );

/**
 * السعر المختار حالياً.
 *
 * @return array{min: float|null, max: float|null}
 */
function matjar_pro_chosen_price() {
	$read = static function ( $key ) {
		if ( ! isset( $_GET[ $key ] ) || '' === $_GET[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return null;
		}

		return (float) wc_clean( wp_unslash( $_GET[ $key ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	};

	return array(
		'min' => $read( 'min_price' ),
		'max' => $read( 'max_price' ),
	);
}

/**
 * الفلاتر المُفعَّلة حالياً، كشرائح قابلة للإزالة.
 *
 * @return array<int,array{label: string, url: string}>
 */
function matjar_pro_active_filters() {
	$chips = array();

	foreach ( matjar_pro_filter_attributes() as $taxonomy => $attribute ) {
		foreach ( matjar_pro_chosen_filter_terms( $taxonomy ) as $slug ) {
			$term = get_term_by( 'slug', $slug, $taxonomy );

			if ( ! $term instanceof WP_Term ) {
				continue;
			}

			$chips[] = array(
				'label' => $term->name,
				'url'   => matjar_pro_filter_toggle_url( $taxonomy, $slug ),
			);
		}
	}

	$price = matjar_pro_chosen_price();

	if ( null !== $price['min'] || null !== $price['max'] ) {
		$bounds = matjar_pro_price_bounds();
		$from   = null !== $price['min'] ? $price['min'] : ( $bounds ? $bounds['min'] : 0 );
		$to     = null !== $price['max'] ? $price['max'] : ( $bounds ? $bounds['max'] : 0 );

		$chips[] = array(
			'label' => sprintf(
				/* translators: 1: أدنى سعر، 2: أعلى سعر، منسّقان بعملة المتجر. */
				__( 'السعر %1$s – %2$s', 'matjar-pro' ),
				wp_strip_all_tags( wc_price( $from ) ),
				wp_strip_all_tags( wc_price( $to ) )
			),
			'url'   => remove_query_arg( array( 'min_price', 'max_price' ), matjar_pro_filter_base_url() ),
		);
	}

	return $chips;
}

/**
 * عدد الفلاتر المُفعَّلة.
 *
 * @return int
 */
function matjar_pro_filter_count() {
	return count( matjar_pro_active_filters() );
}

/**
 * يطبع حقولاً مخفية تحفظ بقية معايير الاستعلام.
 *
 * نموذج GET يستبدل سلسلة الاستعلام بالكامل، فلولا هذه الحقول لأسقط تطبيق
 * فلتر السعر الترتيب والفلاتر الأخرى معه.
 *
 * @param string[] $exclude معايير تُستثنى (التي يملكها النموذج نفسه).
 */
function matjar_pro_filter_hidden_inputs( $exclude = array( 'min_price', 'max_price' ) ) {
	$query = wp_parse_url( matjar_pro_filter_base_url(), PHP_URL_QUERY );

	if ( ! $query ) {
		return;
	}

	$args = array();
	wp_parse_str( $query, $args );

	foreach ( $args as $key => $value ) {
		if ( in_array( $key, $exclude, true ) || ! is_string( $value ) ) {
			continue;
		}

		printf(
			'<input type="hidden" name="%1$s" value="%2$s">',
			esc_attr( $key ),
			esc_attr( $value )
		);
	}
}

/**
 * إعدادات تنسيق العملة لـ JavaScript.
 *
 * شريط السعر يعرض المبلغ أثناء السحب، ولا يمكنه انتظار الخادم. تُمرَّر
 * إعدادات التاجر نفسها من ووكومرس بدل تنسيق مثبّت.
 *
 * @param array $data بيانات JavaScript.
 * @return array
 */
function matjar_pro_filter_script_data( $data ) {
	if ( ! function_exists( 'get_woocommerce_currency_symbol' ) ) {
		return $data;
	}

	$data['currency'] = array_merge(
		isset( $data['currency'] ) && is_array( $data['currency'] ) ? $data['currency'] : array(),
		array(
			'symbol'    => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
			'position'  => (string) get_option( 'woocommerce_currency_pos', 'right' ),
			'decimals'  => (int) wc_get_price_decimals(),
			'thousand'  => (string) wc_get_price_thousand_separator(),
			'decimal'   => (string) wc_get_price_decimal_separator(),
		)
	);

	$data['strings']['applyFilter'] = __( 'تطبيق', 'matjar-pro' );
	$data['strings']['filtering']   = __( 'جارٍ الفلترة…', 'matjar-pro' );

	return $data;
}
add_filter( 'matjar_pro_script_data', 'matjar_pro_filter_script_data', 20 );

/**
 * يحمّل حزمة الأقسام في صفحات الأرشيف وحدها.
 */
function matjar_pro_enqueue_shop_assets() {
	if ( ! function_exists( 'is_shop' ) ) {
		return;
	}

	if ( ! ( is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ) ) {
		return;
	}

	$file = '/assets/js/shop.js';

	if ( ! file_exists( MATJAR_PRO_DIR . $file ) ) {
		return;
	}

	wp_enqueue_script(
		'matjar-pro-shop',
		MATJAR_PRO_URI . $file,
		array( 'matjar-pro' ),
		matjar_pro_asset_version( $file ),
		true
	);

	wp_script_add_data( 'matjar-pro-shop', 'strategy', 'defer' );
}
add_action( 'wp_enqueue_scripts', 'matjar_pro_enqueue_shop_assets', 20 );

/**
 * هل هناك ما يُفلتَر به أصلاً؟
 *
 * @return bool
 */
function matjar_pro_has_filters() {
	return ! empty( matjar_pro_filter_attributes() ) || null !== matjar_pro_price_bounds();
}
