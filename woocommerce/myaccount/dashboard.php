<?php
/**
 * نظرة عامة على الحساب.
 *
 * ترتيب مقصود: الطلب الجاري أولاً وبأكبر مساحة، لأنه سبب دخول العميل.
 * ثم العنوان، ثم روابط قصيرة. لا إحصاءات ولا ترحيب طويل.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_order   = matjar_pro_current_order( $current_user->ID );
$mp_address = wc_get_account_formatted_address( 'shipping' );
?>
<div class="flex flex-col gap-6">

	<?php if ( $mp_order ) : ?>
		<?php
		$mp_progress = matjar_pro_order_progress( $mp_order );
		$mp_thumbs   = matjar_pro_order_thumbnails( $mp_order );
		?>
		<section class="mp-order-live" aria-label="<?php esc_attr_e( 'الطلب الجاري', 'matjar-pro' ); ?>">
			<div class="flex flex-wrap items-baseline gap-2">
				<h2 class="m-0 text-base font-bold text-ink"><?php esc_html_e( 'طلبك الجاري', 'matjar-pro' ); ?></h2>
				<span class="mp-num text-xs text-faint">#<?php echo esc_html( $mp_order->get_order_number() ); ?></span>
				<a class="mp-order-live__link" href="<?php echo esc_url( $mp_order->get_view_order_url() ); ?>">
					<?php esc_html_e( 'التفاصيل', 'matjar-pro' ); ?>
				</a>
			</div>

			<?php if ( $mp_progress ) : ?>
				<ol class="mp-progress">
					<?php foreach ( $mp_progress['steps'] as $mp_index => $mp_step ) : ?>
						<?php
						$mp_state = 'todo';

						if ( $mp_index < $mp_progress['current'] ) {
							$mp_state = 'done';
						} elseif ( $mp_index === $mp_progress['current'] ) {
							$mp_state = 'now';
						}
						?>
						<li class="mp-progress__step" data-mp-state="<?php echo esc_attr( $mp_state ); ?>">
							<span class="mp-progress__dot" aria-hidden="true">
								<?php if ( 'done' === $mp_state ) : ?>
									<?php echo matjar_pro_get_icon( 'check', array( 'size' => 12 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								<?php endif; ?>
							</span>
							<span class="mp-progress__label"><?php echo esc_html( $mp_step ); ?></span>
						</li>
					<?php endforeach; ?>
				</ol>
			<?php else : ?>
				<p class="mp-pill mp-pill--outline self-start">
					<?php echo esc_html( wc_get_order_status_name( $mp_order->get_status() ) ); ?>
				</p>
			<?php endif; ?>

			<div class="flex items-center gap-3 border-t border-line pt-3">
				<?php if ( ! empty( $mp_thumbs ) ) : ?>
					<span class="flex shrink-0 gap-1.5">
						<?php foreach ( $mp_thumbs as $mp_thumb ) : ?>
							<?php echo wp_kses_post( $mp_thumb ); ?>
						<?php endforeach; ?>
					</span>
				<?php endif; ?>

				<span class="grow text-xs text-faint">
					<?php
					printf(
						/* translators: %s: عدد القطع. */
						esc_html( _n( 'قطعة واحدة', '%s قطع', matjar_pro_order_item_count( $mp_order ), 'matjar-pro' ) ),
						'<span class="mp-num">' . esc_html( number_format_i18n( matjar_pro_order_item_count( $mp_order ) ) ) . '</span>'
					);
					?>
				</span>

				<strong class="shrink-0 text-sm text-ink"><?php echo wp_kses_post( $mp_order->get_formatted_order_total() ); ?></strong>
			</div>
		</section>
	<?php endif; ?>

	<section class="mp-account__card" aria-label="<?php esc_attr_e( 'عنوان التوصيل', 'matjar-pro' ); ?>">
		<div class="flex items-baseline gap-2">
			<h2 class="m-0 grow text-base font-bold text-ink"><?php esc_html_e( 'عنوان التوصيل', 'matjar-pro' ); ?></h2>
			<a class="text-xs font-semibold text-cta no-underline hover:underline" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'shipping' ) ); ?>">
				<?php echo $mp_address ? esc_html__( 'تعديل', 'matjar-pro' ) : esc_html__( 'إضافة', 'matjar-pro' ); ?>
			</a>
		</div>

		<?php if ( $mp_address ) : ?>
			<address class="m-0 text-sm not-italic leading-relaxed text-body"><?php echo wp_kses_post( $mp_address ); ?></address>
		<?php else : ?>
			<p class="m-0 text-sm leading-relaxed text-faint">
				<?php esc_html_e( 'لم تُضف عنواناً بعد. إضافته الآن تجعل طلبك القادم أسرع.', 'matjar-pro' ); ?>
			</p>
		<?php endif; ?>
	</section>

	<div class="grid grid-cols-2 gap-3">
		<a class="mp-account__tile" href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>">
			<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php esc_html_e( 'كل طلباتي', 'matjar-pro' ); ?></span>
		</a>

		<a class="mp-account__tile" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
			<?php echo matjar_pro_get_icon( 'grid', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<span><?php esc_html_e( 'تصفّح المتجر', 'matjar-pro' ); ?></span>
		</a>
	</div>

	<?php do_action( 'woocommerce_account_dashboard' ); ?>
</div>
