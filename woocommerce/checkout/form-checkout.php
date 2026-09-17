<?php
/**
 * نموذج الدفع.
 *
 * عمود واحد على الجوال، وعمودان على الشاشات الكبيرة: النموذج في جهة
 * البداية وملخّص الطلب لاصق في جهة النهاية.
 *
 * على الجوال يُطوى الملخّص في أعلى الصفحة: الزائر يريد التأكّد من المبلغ
 * دون أن يُجبَر على تمرير قائمة منتجات قبل أن يصل إلى الحقول.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'يجب تسجيل الدخول لإتمام الطلب.', 'matjar-pro' ) ) );
	return;
}
?>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
	<div class="mp-guest-note">
		<?php echo matjar_pro_get_icon( 'check', array( 'size' => 17 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span><?php esc_html_e( 'لا حاجة لإنشاء حساب — أكمل طلبك كضيف، وننشئ لك حساباً بنقرة بعد الشراء.', 'matjar-pro' ); ?></span>
	</div>
<?php endif; ?>

<form name="checkout" method="post" class="checkout woocommerce-checkout mp-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" data-mp-checkout>

	<?php
	/*
	 * ملخّص مطوي للجوال: الإجمالي مرئي دائماً في الزرّ نفسه، والتفصيل بنقرة.
	 * لا يُطبع على الديسكتوب لأن الملخّص الكامل ظاهر في عموده.
	 */
	?>
	<div class="mp-checkout__peek lg:hidden" data-mp-accordion>
		<button type="button" class="mp-checkout__peek-toggle" aria-expanded="false" aria-controls="mp-order-peek">
			<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span class="grow text-start">
				<?php
				printf(
					/* translators: %s: عدد القطع. */
					esc_html__( 'عرض طلبك (%s)', 'matjar-pro' ),
					'<span class="mp-num">' . esc_html( number_format_i18n( WC()->cart->get_cart_contents_count() ) ) . '</span>'
				);
				?>
			</span>
			<span class="mp-accordion__icon" aria-hidden="true">
				<?php echo matjar_pro_get_icon( 'chevron', array( 'size' => 17 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
			<strong class="mp-checkout__peek-total"><?php echo wp_kses_post( WC()->cart->get_total() ); ?></strong>
		</button>

		<div class="mp-checkout__peek-panel" id="mp-order-peek">
			<ul class="mp-mini-cart">
				<?php foreach ( WC()->cart->get_cart() as $mp_key => $mp_item ) : ?>
					<?php
					$mp_product = $mp_item['data'];

					if ( ! $mp_product || ! $mp_product->exists() ) {
						continue;
					}
					?>
					<li class="mp-mini-cart__item">
						<span class="mp-mini-cart__thumb"><?php echo wp_kses_post( $mp_product->get_image( 'woocommerce_gallery_thumbnail' ) ); ?></span>
						<div class="mp-mini-cart__body">
							<span class="mp-mini-cart__name"><?php echo esc_html( $mp_product->get_name() ); ?></span>
							<span class="mp-mini-cart__meta">
								<span class="mp-num"><?php echo esc_html( $mp_item['quantity'] ); ?></span>
								<span aria-hidden="true">×</span>
								<span class="mp-num"><?php echo wp_kses_post( WC()->cart->get_product_price( $mp_product ) ); ?></span>
							</span>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<div class="mp-checkout__grid">

		<div class="mp-checkout__main">
			<?php if ( $checkout->get_checkout_fields() ) : ?>

				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<section class="mp-checkout__section" id="customer_details">
					<h2 class="mp-checkout__heading"><?php esc_html_e( 'معلومات التوصيل', 'matjar-pro' ); ?></h2>

					<?php do_action( 'woocommerce_checkout_billing' ); ?>
					<?php do_action( 'woocommerce_checkout_shipping' ); ?>
				</section>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

			<?php endif; ?>
		</div>

		<div class="mp-checkout__aside">
			<h2 class="mp-checkout__heading" id="order_review_heading"><?php esc_html_e( 'ملخّص الطلب', 'matjar-pro' ); ?></h2>

			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>
			</div>
		</div>
	</div>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
