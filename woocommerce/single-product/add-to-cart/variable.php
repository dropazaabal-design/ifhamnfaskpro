<?php
/**
 * إضافة منتج ذي متغيّرات إلى السلة.
 *
 * البنية التي يتوقّعها سكربت ووكومرس محفوظة كاملة: form.variations_form
 * مع data-product_variations، وحقول select داخل .variations، وغلاف
 * .single_variation_wrap. هذا ما يجعل تحديث السعر والمخزون والصورة عند
 * اختيار خيار يعمل بمنطق ووكومرس المُجرَّب لا بإعادة كتابته.
 *
 * وحدة variations.js تبني أزرار الخيارات المرئية فوق هذه الحقول وتخفيها،
 * فيبقى الـ select يعمل عند تعطيل JavaScript.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

global $product;

$mp_attribute_keys  = array_keys( $attributes );
$mp_variations_json = wp_json_encode( $available_variations );
$mp_variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $mp_variations_json ) : _wp_specialchars( $mp_variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' );
?>
<form id="mp-buy" class="mp-buy variations_form cart"
	action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>"
	method="post" enctype="multipart/form-data"
	data-product_id="<?php echo absint( $product->get_id() ); ?>"
	data-product_variations="<?php echo $mp_variations_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">

	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock mp-single__stock">
			<?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'هذا المنتج غير متوفر حالياً.', 'matjar-pro' ) ) ); ?>
		</p>
	<?php else : ?>

		<div class="variations mp-variations">
			<?php foreach ( $attributes as $mp_name => $mp_options ) : ?>
				<?php
				$mp_taxonomy = taxonomy_exists( $mp_name ) ? $mp_name : '';
				$mp_selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $mp_name ) ] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					? wc_clean( urldecode( wp_unslash( $_REQUEST[ 'attribute_' . sanitize_title( $mp_name ) ] ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput
					: $product->get_variation_default_attribute( $mp_name );
				?>
				<?php
				// ألوان العيّنات تُمرَّر كخريطة على الحاوي: دالة ووكومرس التي
				// تبني القائمة المنسدلة لا تسمح بسمات على كل خيار.
				$mp_colours = array();

				if ( '' !== $mp_taxonomy ) {
					foreach ( $mp_options as $mp_option ) {
						$mp_colour = matjar_pro_get_swatch_color( $mp_taxonomy, $mp_option );

						if ( '' !== $mp_colour ) {
							$mp_colours[ $mp_option ] = $mp_colour;
						}
					}
				}
				?>
				<div class="mp-variation"
					data-mp-variation="<?php echo esc_attr( sanitize_title( $mp_name ) ); ?>"
					data-mp-taxonomy="<?php echo esc_attr( $mp_taxonomy ); ?>"
					<?php if ( ! empty( $mp_colours ) ) : ?>
						data-mp-colours="<?php echo esc_attr( (string) wp_json_encode( $mp_colours ) ); ?>"
					<?php endif; ?>
				>
					<div class="mp-variation__head">
						<label class="mp-variation__label" for="<?php echo esc_attr( sanitize_title( $mp_name ) ); ?>">
							<?php echo esc_html( wc_attribute_label( $mp_name ) ); ?>
						</label>
						<span class="mp-variation__chosen" data-mp-variation-chosen></span>
					</div>

					<?php
					/*
					 * الحقل المنسدل هو مصدر الحقيقة الذي يقرأه ووكومرس. تبنيه
					 * الوحدة أزراراً مرئية وتُخفيه، فلا يرى الزائر عنصرين
					 * بالوظيفة نفسها، ويبقى الحقل عاملاً بلا JavaScript.
					 */
					wc_dropdown_variation_attribute_options(
						array(
							'options'   => $mp_options,
							'attribute' => $mp_name,
							'product'   => $product,
							'selected'  => $mp_selected,
							'class'     => 'mp-variation__select',
						)
					);
					?>

					<?php if ( end( $mp_attribute_keys ) === $mp_name ) : ?>
						<?php
						echo wp_kses_post(
							apply_filters(
								'woocommerce_reset_variations_link',
								'<a class="reset_variations mp-variation__reset" href="#">' . esc_html__( 'إلغاء الاختيار', 'matjar-pro' ) . '</a>'
							)
						);
						?>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap mp-buy__variation">
			<?php do_action( 'woocommerce_before_single_variation' ); ?>

			<?php
			/**
			 * سعر المتغيّر وزر الإضافة.
			 *
			 * @hooked woocommerce_single_variation - 10
			 * @hooked woocommerce_single_variation_add_to_cart_button - 20
			 */
			do_action( 'woocommerce_single_variation' );
			?>

			<?php do_action( 'woocommerce_after_single_variation' ); ?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>
<?php
do_action( 'woocommerce_after_add_to_cart_form' );
