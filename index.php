<?php
/**
 * القالب الاحتياطي العام.
 *
 * ووردبريس يعود إلى هذا الملف حين لا يجد قالباً أخصّ منه. القوالب
 * المتخصّصة (الرئيسية، الأرشيف، المنتج، الدفع) تُبنى في المرحلتين
 * الثالثة والرابعة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="mp-main" class="mp-main mx-auto max-w-screen-xl px-4 py-8">

	<?php if ( have_posts() ) : ?>

		<?php if ( ! is_front_page() && ( is_home() || is_archive() || is_search() ) ) : ?>
			<header class="mb-6">
				<h1 class="m-0 text-2xl font-bold text-ink lg:text-3xl">
					<?php
					if ( is_search() ) {
						printf(
							/* translators: %s: عبارة البحث. */
							esc_html__( 'نتائج البحث عن: %s', 'matjar-pro' ),
							'<span class="text-cta">' . esc_html( get_search_query() ) . '</span>'
						);
					} elseif ( is_home() ) {
						echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) );
					} else {
						the_archive_title();
					}
					?>
				</h1>
				<?php the_archive_description( '<div class="mt-2 text-sm leading-relaxed text-body">', '</div>' ); ?>
			</header>
		<?php endif; ?>

		<div class="grid gap-6 <?php echo is_singular() ? '' : 'sm:grid-cols-2 lg:grid-cols-3'; ?>">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'mp-card overflow-hidden rounded-2xl border border-line bg-surface' ); ?>>

					<?php if ( has_post_thumbnail() && ! is_singular() ) : ?>
						<a class="block" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
							<?php the_post_thumbnail( 'matjar-pro-card', array( 'class' => 'aspect-[4/5] w-full object-cover' ) ); ?>
						</a>
					<?php endif; ?>

					<div class="p-4">
						<?php if ( is_singular() ) : ?>
							<h1 class="m-0 text-2xl font-bold leading-snug text-ink lg:text-3xl"><?php the_title(); ?></h1>
						<?php else : ?>
							<h2 class="m-0 text-base font-semibold leading-snug text-ink">
								<a class="text-ink no-underline transition hover:text-cta" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							</h2>
						<?php endif; ?>

						<?php if ( 'post' === get_post_type() ) : ?>
							<p class="mb-0 mt-2 text-xs text-faint">
								<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
							</p>
						<?php endif; ?>

						<div class="mp-prose mt-3 text-sm leading-relaxed text-body">
							<?php
							if ( is_singular() ) {
								the_content();
							} else {
								the_excerpt();
							}
							?>
						</div>
					</div>
				</article>
				<?php
			endwhile;
			?>
		</div>

		<?php
		the_posts_pagination(
			array(
				'class'              => 'mp-pagination mt-8 flex justify-center gap-2 text-sm font-semibold',
				'mid_size'           => 1,
				'prev_text'          => esc_html__( 'السابق', 'matjar-pro' ),
				'next_text'          => esc_html__( 'التالي', 'matjar-pro' ),
				'screen_reader_text' => esc_html__( 'تنقّل بين الصفحات', 'matjar-pro' ),
			)
		);
		?>

	<?php else : ?>

		<div class="mx-auto max-w-md rounded-2xl border border-line bg-surface p-8 text-center">
			<h1 class="m-0 text-xl font-bold text-ink"><?php esc_html_e( 'لا توجد نتائج', 'matjar-pro' ); ?></h1>
			<p class="mb-5 mt-2 text-sm leading-relaxed text-body"><?php esc_html_e( 'جرّب كلمة أخرى، أو تصفّح الأقسام من القائمة.', 'matjar-pro' ); ?></p>
			<?php get_search_form(); ?>
		</div>

	<?php endif; ?>
</main>

<?php
get_footer();
