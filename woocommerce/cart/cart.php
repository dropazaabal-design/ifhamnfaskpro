<?php
/**
 * صفحة السلة.
 *
 * بطاقات لا جدول: الجدول على الجوال يُضغط في أعمدة لا تُقرأ. كل سطر هنا
 * بطاقة مستقلة بصورة واسم وكمية ومجموع.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' );

$mp_progress = matjar_pro_free_shipping_progress();
?>
<div class="mp-cart lg:flex lg:items-start lg:gap-8">

	<div class="min-w-0 grow">
		<form class="woocommerce-cart-form mp-cart__form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" data-mp-cart-form>
			<?php do_action( 'woocommerce_before_cart_table' ); ?>

			<?php if ( $mp_progress && $mp_progress['remaining'] > 0 ) : ?>
				<div class="mp-ship-progress mb-5">
					<p class="m-0 text-xs font-semibold text-ink">
						<?php
						printf(
							/* translators: %s: المبلغ المتبقي منسّقاً بعملة المتجر. */
							esc_html__( 'أضف %s ليصبح الشحن مجانياً', 'matjar-pro' ),
							wp_kses_post( wc_price( $mp_progress['remaining'] ) )
						);
						?>
					</p>
					<span class="mp-ship-progress__track">
						<span class="mp-ship-progress__fill" style="width: <?php echo esc_attr( (string) $mp_progress['percent'] ); ?>%"></span>
					</span>
				</div>
			<?php endif; ?>

			<ul class="mp-cart__items">
				<?php do_action( 'woocommerce_before_cart_contents' ); ?>

				<?php
				foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
					$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
					$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

					if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
						continue;
					}

					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<li class="mp-cart__item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

						<a class="mp-cart__thumb" href="<?php echo esc_url( $product_permalink ? $product_permalink : '#' ); ?>" tabindex="-1" aria-hidden="true">
							<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( 'matjar-pro-card' ), $cart_item, $cart_item_key ) ); ?>
						</a>

						<div class="mp-cart__body">
							<a class="mp-cart__name" href="<?php echo esc_url( $product_permalink ? $product_permalink : '#' ); ?>">
								<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
							</a>

							<?php echo wp_kses_post( wc_get_formatted_cart_item_data( $cart_item ) ); ?>

							<?php if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) : ?>
								<p class="m-0 text-xs font-semibold text-info"><?php esc_html_e( 'يُشحن عند التوفّر', 'matjar-pro' ); ?></p>
							<?php endif; ?>

							<div class="mp-cart__controls">
								<?php
								if ( $_product->is_sold_individually() ) {
									printf( '<input type="hidden" name="cart[%s][qty]" value="1">', esc_attr( $cart_item_key ) );
								} else {
									woocommerce_quantity_input(
										array(
											'input_name'   => "cart[{$cart_item_key}][qty]",
											'input_value'  => $cart_item['quantity'],
											'max_value'    => $_product->get_max_purchase_quantity(),
											'min_value'    => '0',
											'product_name' => $_product->get_name(),
										),
										$_product,
										true
									);
								}
								?>

								<span class="mp-cart__line-total">
									<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); ?>
								</span>

								<?php
								echo wp_kses_post(
									apply_filters(
										'woocommerce_cart_item_remove_link',
										sprintf(
											'<a href="%s" class="mp-cart__remove remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
											esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
											esc_attr(
												sprintf(
													/* translators: %s: اسم المنتج. */
													__( 'حذف %s من السلة', 'matjar-pro' ),
													$_product->get_name()
												)
											),
											esc_attr( $product_id ),
											esc_attr( $_product->get_sku() ),
											matjar_pro_get_icon( 'trash', array( 'size' => 16 ) )
										),
										$cart_item_key
									)
								);
								?>
							</div>
						</div>
					</li>
					<?php
				}
				?>

				<?php do_action( 'woocommerce_cart_contents' ); ?>
				<?php do_action( 'woocommerce_after_cart_contents' ); ?>
			</ul>

			<div class="mp-cart__actions">
				<?php if ( wc_coupons_enabled() ) : ?>
					<div class="mp-coupon" data-mp-accordion>
						<button type="button" class="mp-coupon__toggle" aria-expanded="false" aria-controls="mp-coupon-panel">
							<?php echo matjar_pro_get_icon( 'tag', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php esc_html_e( 'لديك كود خصم؟', 'matjar-pro' ); ?>
						</button>

						<div class="mp-coupon__panel" id="mp-coupon-panel">
							<label class="sr-only" for="coupon_code"><?php esc_html_e( 'كود الخصم', 'matjar-pro' ); ?></label>
							<input type="text" name="coupon_code" class="mp-field input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'أدخل الكود', 'matjar-pro' ); ?>" autocomplete="off">
							<button type="submit" class="mp-btn mp-btn--outline" name="apply_coupon" value="<?php esc_attr_e( 'تطبيق', 'matjar-pro' ); ?>">
								<?php esc_html_e( 'تطبيق', 'matjar-pro' ); ?>
							</button>
						</div>
					</div>
				<?php endif; ?>

				<?php
				/*
				 * زر التحديث موجود للعمل بلا JavaScript. مع JavaScript يُضغط
				 * تلقائياً عند تغيير الكمية، فلا يحتاج الزائر خطوة إضافية.
				 */
				?>
				<button type="submit" class="mp-btn mp-btn--ghost text-sm" name="update_cart" value="<?php esc_attr_e( 'تحديث السلة', 'matjar-pro' ); ?>" data-mp-update-cart>
					<?php esc_html_e( 'تحديث السلة', 'matjar-pro' ); ?>
				</button>

				<?php do_action( 'woocommerce_cart_actions' ); ?>
				<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
			</div>

			<?php do_action( 'woocommerce_after_cart_table' ); ?>
		</form>
	</div>

	<div class="mp-cart__aside">
		<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>
		<?php do_action( 'woocommerce_cart_collaterals' ); ?>
	</div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
