<?php
/**
 * صفحة الأقسام وأرشيف المنتجات.
 *
 * منطقة النتائج مغلّفة في data-mp-results ليستبدلها AJAX كاملةً، فيبقى
 * التصيير كلّه على الخادم ولا يُعاد بناء بطاقة منتج في المتصفح.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

get_header();

$mp_has_filters = matjar_pro_has_filters();
?>

<div class="mx-auto max-w-screen-xl px-3 py-5 lg:px-4 lg:py-8">

	<?php woocommerce_breadcrumb(); ?>

	<header class="mb-5 flex flex-col gap-2">
		<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
			<h1 class="m-0 text-2xl font-bold text-ink lg:text-3xl"><?php woocommerce_page_title(); ?></h1>
		<?php endif; ?>
		<?php do_action( 'woocommerce_archive_description' ); ?>
	</header>

	<div class="lg:flex lg:items-start lg:gap-8">

		<?php if ( $mp_has_filters ) : ?>
			<aside class="mp-shop-sidebar hidden lg:block" aria-label="<?php esc_attr_e( 'فلترة المنتجات', 'matjar-pro' ); ?>">
				<?php get_template_part( 'template-parts/shop/filter-body', null, array( 'context' => 'aside' ) ); ?>
			</aside>
		<?php endif; ?>

		<div class="min-w-0 grow" data-mp-results>

			<div class="mb-3 flex items-center gap-3 border-b border-line pb-3">
				<?php if ( $mp_has_filters ) : ?>
					<button type="button" class="mp-filter-btn lg:hidden" data-mp-drawer-open="filter" aria-expanded="false" aria-controls="mp-filter-drawer">
						<?php echo matjar_pro_get_icon( 'filter', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'فلترة', 'matjar-pro' ); ?>
						<?php $mp_count = matjar_pro_filter_count(); ?>
						<?php if ( $mp_count > 0 ) : ?>
							<span class="mp-filter-btn__count mp-num"><?php echo esc_html( number_format_i18n( $mp_count ) ); ?></span>
						<?php endif; ?>
					</button>
				<?php endif; ?>

				<p class="mp-result-count grow text-xs text-faint"><?php woocommerce_result_count(); ?></p>

				<?php woocommerce_catalog_ordering(); ?>
			</div>

			<?php if ( $mp_has_filters ) : ?>
				<?php get_template_part( 'template-parts/shop/active-filters' ); ?>
			<?php endif; ?>

			<?php if ( woocommerce_product_loop() ) : ?>

				<?php
				woocommerce_product_loop_start();

				if ( wc_get_loop_display_mode() !== 'subcategories' ) {
					while ( have_posts() ) {
						the_post();
						do_action( 'woocommerce_shop_loop' );
						wc_get_template_part( 'content', 'product' );
					}
				}

				woocommerce_product_loop_end();
				?>

				<?php do_action( 'woocommerce_after_shop_loop' ); ?>

			<?php else : ?>
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php
if ( $mp_has_filters ) {
	get_template_part( 'template-parts/shop/filter-drawer' );
}

get_footer();
