<?php
/**
 * غلاف صفحة المنتج.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<div class="mx-auto max-w-screen-xl px-3 pb-8 pt-3 lg:px-4 lg:pt-6">
	<?php woocommerce_breadcrumb(); ?>

	<?php while ( have_posts() ) : ?>
		<?php
		the_post();
		wc_get_template_part( 'content', 'single-product' );
		?>
	<?php endwhile; ?>
</div>

<?php
get_footer();
