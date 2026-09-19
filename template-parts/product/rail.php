<?php
/**
 * شريط منتجات أفقي قابل للسحب.
 *
 * يُستدعى بوسائط:
 *   get_template_part( 'template-parts/product/rail', null, array(
 *       'title'   => 'الأكثر مبيعاً',
 *       'orderby' => 'popularity',
 *       'limit'   => 8,
 *   ) );
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

if ( ! matjar_pro_has_woocommerce() ) {
	return;
}

$mp = wp_parse_args(
	isset( $args ) && is_array( $args ) ? $args : array(),
	array(
		'title'    => '',
		'orderby'  => 'date',
		'order'    => 'DESC',
		'limit'    => 8,
		'featured' => false,
		'link'     => '',
	)
);

$mp_query = array(
	'status'   => 'publish',
	'visibility' => 'catalog',
	'limit'    => max( 2, (int) $mp['limit'] ),
	'orderby'  => $mp['orderby'],
	'order'    => $mp['order'],
);

if ( $mp['featured'] ) {
	$mp_query['featured'] = true;
}

$mp_products = wc_get_products( $mp_query );

if ( empty( $mp_products ) ) {
	return;
}
?>
<section class="mp-rail-section py-6" aria-label="<?php echo esc_attr( $mp['title'] ); ?>">
	<div class="mx-auto max-w-screen-xl px-3 lg:px-4">
		<div class="mb-4 flex items-baseline justify-between gap-3">
			<?php if ( '' !== $mp['title'] ) : ?>
				<h2 class="m-0 text-lg font-bold text-ink lg:text-xl"><?php echo esc_html( $mp['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( '' !== $mp['link'] ) : ?>
				<a class="shrink-0 text-xs font-semibold text-cta no-underline hover:underline" href="<?php echo esc_url( $mp['link'] ); ?>">
					<?php esc_html_e( 'عرض الكل', 'matjar-pro' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>

	<?php
	/*
	 * الشريط يبدأ من حافة الشاشة ولا يُحدّه الحاوي: البطاقة النصف-ظاهرة عند
	 * الحافة هي ما يُخبر الإصبع أن هناك المزيد. شبكة كاملة تُوهم بأن
	 * المحتوى انتهى.
	 */
	?>
	<ul class="mp-rail mp-rail--products px-3 lg:mx-auto lg:max-w-screen-xl lg:px-4">
		<?php
		foreach ( $mp_products as $mp_product ) {
			$mp_post = get_post( $mp_product->get_id() );

			if ( ! $mp_post ) {
				continue;
			}

			$GLOBALS['post']    = $mp_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			$GLOBALS['product'] = $mp_product; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			setup_postdata( $mp_post );

			wc_get_template_part( 'content', 'product' );
		}

		wp_reset_postdata();
		?>
	</ul>
</section>
