<?php
/**
 * صفحة الشكر.
 *
 * ليست نهاية الرحلة: ملخّص واضح، ورابط تتبّع على واتساب (قناة المتابعة
 * الفعلية في السوق المستهدف)، ثم دعوة لمواصلة التصفّح.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_whatsapp = preg_replace( '/[^0-9]/', '', (string) matjar_pro_mod( 'matjar_pro_whatsapp' ) );
?>
<div class="mp-thankyou">

	<?php if ( ! $order ) : ?>

		<p class="woocommerce-notice woocommerce-notice--success">
			<?php echo esc_html( apply_filters( 'woocommerce_thankyou_order_received_text', __( 'شكراً لك. تم استلام طلبك.', 'matjar-pro' ), null ) ); ?>
		</p>

	<?php else : ?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error">
				<?php esc_html_e( 'تعذّر إتمام الدفع. جرّب مرة أخرى أو اختر طريقة دفع أخرى.', 'matjar-pro' ); ?>
			</p>

			<div class="mt-4 flex flex-wrap gap-2">
				<a class="mp-btn mp-btn--cta" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">
					<?php esc_html_e( 'إعادة المحاولة', 'matjar-pro' ); ?>
				</a>
				<?php if ( is_user_logged_in() ) : ?>
					<a class="mp-btn mp-btn--outline" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">
						<?php esc_html_e( 'حسابي', 'matjar-pro' ); ?>
					</a>
				<?php endif; ?>
			</div>

		<?php else : ?>

			<div class="mp-thankyou__hero">
				<span class="mp-thankyou__check" aria-hidden="true">
					<?php echo matjar_pro_get_icon( 'check', array( 'size' => 30 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</span>

				<h1 class="m-0 text-xl font-bold text-ink lg:text-2xl">
					<?php echo esc_html( apply_filters( 'woocommerce_thankyou_order_received_text', __( 'تم استلام طلبك', 'matjar-pro' ), $order ) ); ?>
				</h1>

				<p class="m-0 text-sm leading-relaxed text-body">
					<?php
					printf(
						/* translators: %s: رقم الطلب. */
						esc_html__( 'رقم طلبك %s. سنتواصل معك لتأكيد التوصيل.', 'matjar-pro' ),
						'<strong class="mp-num">' . esc_html( $order->get_order_number() ) . '</strong>'
					);
					?>
				</p>

				<?php $mp_estimate = matjar_pro_delivery_estimate(); ?>
				<?php if ( '' !== $mp_estimate ) : ?>
					<p class="mp-single__delivery mt-1">
						<?php echo matjar_pro_get_icon( 'truck', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php echo esc_html( $mp_estimate ); ?></span>
					</p>
				<?php endif; ?>

				<?php if ( '' !== $mp_whatsapp ) : ?>
					<a class="mp-btn mp-btn--outline w-full sm:w-auto"
						href="<?php
							echo esc_url(
								'https://wa.me/' . $mp_whatsapp . '?text=' . rawurlencode(
									sprintf(
										/* translators: %s: رقم الطلب. */
										__( 'مرحباً، أريد متابعة الطلب رقم %s', 'matjar-pro' ),
										$order->get_order_number()
									)
								)
							);
						?>"
						target="_blank" rel="noopener">
						<?php echo matjar_pro_get_icon( 'whatsapp', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php esc_html_e( 'تتبّع طلبك على واتساب', 'matjar-pro' ); ?>
					</a>
				<?php endif; ?>
			</div>

			<ul class="mp-thankyou__facts">
				<li>
					<span><?php esc_html_e( 'رقم الطلب', 'matjar-pro' ); ?></span>
					<strong class="mp-num"><?php echo esc_html( $order->get_order_number() ); ?></strong>
				</li>
				<li>
					<span><?php esc_html_e( 'التاريخ', 'matjar-pro' ); ?></span>
					<strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
				</li>
				<li>
					<span><?php esc_html_e( 'الإجمالي', 'matjar-pro' ); ?></span>
					<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
				</li>
				<?php if ( $order->get_payment_method_title() ) : ?>
					<li>
						<span><?php esc_html_e( 'طريقة الدفع', 'matjar-pro' ); ?></span>
						<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
					</li>
				<?php endif; ?>
			</ul>

			<?php if ( $order->get_billing_phone() ) : ?>
				<p class="text-xs text-faint">
					<?php
					printf(
						/* translators: %s: رقم الجوال. */
						esc_html__( 'سنتواصل معك على %s', 'matjar-pro' ),
						'<span class="mp-num">' . esc_html( $order->get_billing_phone() ) . '</span>'
					);
					?>
				</p>
			<?php endif; ?>

		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

		<a class="mp-btn mp-btn--ghost mt-2" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
			<?php esc_html_e( 'مواصلة التصفّح', 'matjar-pro' ); ?>
		</a>

	<?php endif; ?>
</div>
