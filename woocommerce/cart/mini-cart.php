<?php
/**
 * محتوى لوح السلة.
 *
 * يُعاد بناؤه على الخادم بعد كل إضافة عبر أجزاء ووكومرس، فلا تُكرَّر منطق
 * الأسعار والعملة والترجمة في المتصفح.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' );

$mp_progress = matjar_pro_free_shipping_progress();
?>

<?php if ( ! WC()->cart->is_empty() ) : ?>

	<?php if ( $mp_progress && $mp_progress['remaining'] > 0 ) : ?>
		<div class="mp-ship-progress">
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
	<?php elseif ( $mp_progress ) : ?>
		<p class="mp-ship-progress__done">
			<?php echo matjar_pro_get_icon( 'check', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php esc_html_e( 'حصلت على الشحن المجاني', 'matjar-pro' ); ?>
		</p>
	<?php endif; ?>

	<ul class="mp-mini-cart">
		<?php
		foreach ( WC()->cart->get_cart() as $mp_key => $mp_item ) {
			$mp_product = $mp_item['data'];

			if ( ! $mp_product || ! $mp_product->exists() || $mp_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_widget_cart_item_visible', true, $mp_item, $mp_key ) ) {
				continue;
			}

			$mp_permalink = apply_filters( 'woocommerce_cart_item_permalink', $mp_product->is_visible() ? $mp_product->get_permalink( $mp_item ) : '', $mp_item, $mp_key );
			?>
			<li class="mp-mini-cart__item">
				<a class="mp-mini-cart__thumb" href="<?php echo esc_url( $mp_permalink ? $mp_permalink : '#' ); ?>" tabindex="-1" aria-hidden="true">
					<?php echo wp_kses_post( $mp_product->get_image( 'woocommerce_gallery_thumbnail' ) ); ?>
				</a>

				<div class="mp-mini-cart__body">
					<a class="mp-mini-cart__name" href="<?php echo esc_url( $mp_permalink ? $mp_permalink : '#' ); ?>">
						<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $mp_product->get_name(), $mp_item, $mp_key ) ); ?>
					</a>

					<?php echo wp_kses_post( wc_get_formatted_cart_item_data( $mp_item, true ) ); ?>

					<span class="mp-mini-cart__meta">
						<span class="mp-num"><?php echo esc_html( $mp_item['quantity'] ); ?></span>
						<span aria-hidden="true">×</span>
						<span class="mp-num"><?php echo wp_kses_post( WC()->cart->get_product_price( $mp_product ) ); ?></span>
					</span>
				</div>

				<a class="mp-mini-cart__remove"
					href="<?php echo esc_url( wc_get_cart_remove_url( $mp_key ) ); ?>"
					aria-label="<?php
						printf(
							/* translators: %s: اسم المنتج. */
							esc_attr__( 'حذف %s من السلة', 'matjar-pro' ),
							esc_attr( $mp_product->get_name() )
						);
					?>">
					<?php echo matjar_pro_get_icon( 'trash', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</a>
			</li>
			<?php
		}
		?>
	</ul>

	<?php do_action( 'woocommerce_mini_cart_contents' ); ?>

	<div class="mp-mini-cart__foot">
		<div class="mp-mini-cart__total">
			<span><?php esc_html_e( 'الإجمالي', 'matjar-pro' ); ?></span>
			<strong><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></strong>
		</div>

		<a class="mp-btn mp-btn--cta mp-btn--lg w-full" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
			<?php esc_html_e( 'إتمام الشراء', 'matjar-pro' ); ?>
		</a>

		<a class="mp-btn mp-btn--ghost w-full text-sm" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
			<?php esc_html_e( 'عرض السلة', 'matjar-pro' ); ?>
		</a>
	</div>

<?php else : ?>

	<div class="mp-mini-cart__empty">
		<span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-cream text-faint" aria-hidden="true">
			<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 22 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>
		<p class="m-0 text-sm font-semibold text-ink"><?php esc_html_e( 'سلتك فارغة', 'matjar-pro' ); ?></p>
		<a class="mp-btn mp-btn--outline" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
			<?php esc_html_e( 'ابدأ التصفّح', 'matjar-pro' ); ?>
		</a>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>
