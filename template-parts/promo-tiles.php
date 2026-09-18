<?php
/**
 * بلاطتا الترويج تحت الهيرو.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_tiles = matjar_pro_promo_tiles();

if ( empty( $mp_tiles ) ) {
	return;
}
?>
<section class="mx-auto grid max-w-screen-xl grid-cols-2 gap-3 px-3 py-4" aria-label="<?php esc_attr_e( 'عروض مختارة', 'matjar-pro' ); ?>">
	<?php foreach ( $mp_tiles as $mp_tile ) : ?>
		<?php $mp_tag = '' !== $mp_tile['url'] ? 'a' : 'div'; ?>
		<<?php echo esc_attr( $mp_tag ); ?>
			class="mp-promo group relative block overflow-hidden rounded-2xl bg-line no-underline"
			<?php if ( 'a' === $mp_tag ) : ?>
				href="<?php echo esc_url( $mp_tile['url'] ); ?>"
			<?php endif; ?>
		>
			<?php if ( $mp_tile['image'] ) : ?>
				<?php
				echo wp_get_attachment_image(
					$mp_tile['image'],
					'matjar-pro-card',
					false,
					array(
						'class'   => 'aspect-[4/5] w-full object-cover transition duration-300 group-hover:scale-[1.03] sm:aspect-[16/9]',
						'sizes'   => '(min-width: 640px) 50vw, 50vw',
						'loading' => 'lazy',
						'alt'     => esc_attr( $mp_tile['label'] ),
					)
				);
				?>
			<?php endif; ?>

			<?php if ( '' !== $mp_tile['label'] ) : ?>
				<?php /* طبقة 65% هنا أيضاً: العنوان أبيض فوق صورة لا نعرفها. */ ?>
				<span class="absolute inset-x-0 bottom-0 bg-ink/65 px-3 py-2.5 text-sm font-bold text-white">
					<?php echo esc_html( $mp_tile['label'] ); ?>
				</span>
			<?php endif; ?>
		</<?php echo esc_attr( $mp_tag ); ?>>
	<?php endforeach; ?>
</section>
