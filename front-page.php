<?php
/**
 * الصفحة الأولى.
 *
 * الترتيب مقصود: هيرو، ثم ثقة، ثم ترويج، ثم محتوى الصفحة. شريط الثقة قبل
 * المنتجات لأن الزائر البارد يقرّر البقاء على أساس مصداقية المتجر لا على
 * أساس تشكيلته.
 *
 * أقسام المنتجات (الأكثر مبيعاً، الفئات، التقييمات) تُبنى في المرحلة
 * الرابعة مع قوالب ووكومرس.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<?php get_template_part( 'template-parts/hero' ); ?>
<?php get_template_part( 'template-parts/trust-strip' ); ?>
<?php get_template_part( 'template-parts/promo-tiles' ); ?>

<main id="mp-main" class="mp-main">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();

			$mp_content = trim( get_the_content() );

			if ( '' === $mp_content ) {
				continue;
			}
			?>
			<div class="mp-prose mx-auto max-w-screen-xl px-4 py-8">
				<?php the_content(); ?>
			</div>
			<?php
		endwhile;
	endif; ?>
</main>

<?php
get_footer();
