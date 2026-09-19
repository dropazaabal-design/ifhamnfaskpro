<?php
/**
 * معرض صور المنتج.
 *
 * الجوال: شريط بالسحب الأصلي عبر scroll-snap — لا مكتبة سحب ولا JavaScript
 * للحركة نفسها، فقط لتحديث المؤشّرات. المتصفح يتولّى الزخم واللمس.
 *
 * الديسكتوب: شبكة من عمودين تُظهر كل الصور معاً، فلا حالة ولا JavaScript
 * إطلاقاً، ولا نقرات لرؤية الصورة الثانية.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

global $product;

$mp_ids = array();
$mp_main = $product->get_image_id();

if ( $mp_main ) {
	$mp_ids[] = (int) $mp_main;
}

foreach ( $product->get_gallery_image_ids() as $mp_gallery_id ) {
	$mp_ids[] = (int) $mp_gallery_id;
}

$mp_ids   = array_values( array_unique( array_filter( $mp_ids ) ) );
$mp_total = count( $mp_ids );
$mp_flag  = matjar_pro_sale_percentage( $product );
?>
<div class="mp-gallery" data-mp-gallery>

	<?php if ( 0 === $mp_total ) : ?>
		<div class="mp-gallery__placeholder"><span><?php esc_html_e( 'لا صورة', 'matjar-pro' ); ?></span></div>
	<?php else : ?>

		<div class="mp-gallery__stage">
			<ul class="mp-gallery__rail" data-mp-gallery-rail>
				<?php foreach ( $mp_ids as $mp_index => $mp_id ) : ?>
					<li class="mp-gallery__slide" data-mp-gallery-slide>
						<?php
						echo wp_get_attachment_image(
							$mp_id,
							'woocommerce_single',
							false,
							array(
								'class'         => 'mp-gallery__img',
								'alt'           => esc_attr( $product->get_name() ),
								'sizes'         => '(min-width: 1024px) 27vw, 100vw',
								'loading'       => 0 === $mp_index ? 'eager' : 'lazy',
								'decoding'      => 0 === $mp_index ? 'sync' : 'async',
								'fetchpriority' => 0 === $mp_index ? 'high' : 'auto',
							)
						);
						?>
					</li>
				<?php endforeach; ?>
			</ul>

			<?php if ( $mp_flag > 0 ) : ?>
				<span class="mp-gallery__flag">
					<?php
					printf(
						/* translators: %s: نسبة الخصم. */
						esc_html__( 'خصم %s', 'matjar-pro' ),
						'<span class="mp-num">' . esc_html( number_format_i18n( $mp_flag ) ) . '%</span>'
					);
					?>
				</span>
			<?php endif; ?>

			<?php if ( $mp_total > 1 ) : ?>
				<span class="mp-gallery__counter mp-num" data-mp-gallery-counter aria-hidden="true">
					<span data-mp-gallery-current>1</span> / <?php echo esc_html( number_format_i18n( $mp_total ) ); ?>
				</span>

				<div class="mp-gallery__dots lg:hidden" role="tablist" aria-label="<?php esc_attr_e( 'صور المنتج', 'matjar-pro' ); ?>">
					<?php foreach ( $mp_ids as $mp_index => $mp_id ) : ?>
						<button type="button" class="mp-gallery__dot" role="tab"
							data-mp-gallery-dot="<?php echo esc_attr( (string) $mp_index ); ?>"
							aria-selected="<?php echo 0 === $mp_index ? 'true' : 'false'; ?>"
							aria-label="<?php
								printf(
									/* translators: %s: رقم الصورة. */
									esc_attr__( 'الصورة %s', 'matjar-pro' ),
									esc_attr( number_format_i18n( $mp_index + 1 ) )
								);
							?>"></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
