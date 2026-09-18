<?php
/**
 * طلباتي — بطاقات لا جدول.
 *
 * جدول ووكومرس الافتراضي ينكسر على الجوال: ستة أعمدة في 390 بكسل تصبح
 * أرقاماً متلاصقة لا تُقرأ. كل طلب هنا بطاقة مستقلة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders );
?>

<?php if ( $has_orders ) : ?>

	<div class="mp-orders">
		<?php foreach ( $customer_orders->orders as $mp_order ) : ?>
			<?php
			$mp_progress = matjar_pro_order_progress( $mp_order );
			$mp_thumbs   = matjar_pro_order_thumbnails( $mp_order, 4 );
			$mp_count    = matjar_pro_order_item_count( $mp_order );
			?>
			<article class="mp-order">
				<div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
					<a class="text-sm font-bold text-ink no-underline hover:text-cta" href="<?php echo esc_url( $mp_order->get_view_order_url() ); ?>">
						<span class="mp-num">#<?php echo esc_html( $mp_order->get_order_number() ); ?></span>
					</a>
					<time class="grow text-xs text-faint" datetime="<?php echo esc_attr( $mp_order->get_date_created()->date( 'c' ) ); ?>">
						<?php echo esc_html( wc_format_datetime( $mp_order->get_date_created() ) ); ?>
					</time>
					<span class="mp-order__status mp-order__status--<?php echo esc_attr( $mp_order->get_status() ); ?>">
						<?php echo esc_html( wc_get_order_status_name( $mp_order->get_status() ) ); ?>
					</span>
				</div>

				<?php if ( ! empty( $mp_thumbs ) ) : ?>
					<div class="flex flex-wrap gap-1.5">
						<?php foreach ( $mp_thumbs as $mp_thumb ) : ?>
							<?php echo wp_kses_post( $mp_thumb ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $mp_progress ) : ?>
					<ol class="mp-progress mp-progress--compact">
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
								<span class="mp-progress__dot" aria-hidden="true"></span>
								<span class="mp-progress__label"><?php echo esc_html( $mp_step ); ?></span>
							</li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>

				<div class="mt-auto flex items-center gap-3 border-t border-line pt-3">
					<span class="grow text-xs text-faint">
						<?php
						printf(
							/* translators: %s: عدد القطع. */
							esc_html( _n( 'قطعة واحدة', '%s قطع', $mp_count, 'matjar-pro' ) ),
							'<span class="mp-num">' . esc_html( number_format_i18n( $mp_count ) ) . '</span>'
						);
						?>
					</span>
					<strong class="shrink-0 text-sm text-ink"><?php echo wp_kses_post( $mp_order->get_formatted_order_total() ); ?></strong>
				</div>

				<div class="flex flex-wrap gap-2">
					<a class="mp-btn mp-btn--outline min-h-[38px] px-3 text-xs" href="<?php echo esc_url( $mp_order->get_view_order_url() ); ?>">
						<?php esc_html_e( 'تفاصيل الطلب', 'matjar-pro' ); ?>
					</a>

					<?php foreach ( wc_get_account_orders_actions( $mp_order ) as $mp_key => $mp_action ) : ?>
						<?php if ( 'view' !== $mp_key ) : ?>
							<a class="mp-btn mp-btn--ghost min-h-[38px] px-3 text-xs" href="<?php echo esc_url( $mp_action['url'] ); ?>">
								<?php echo esc_html( $mp_action['name'] ); ?>
							</a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="woocommerce-pagination mt-6">
			<ul>
				<?php if ( 1 !== $current_page ) : ?>
					<li><a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'السابق', 'matjar-pro' ); ?></a></li>
				<?php endif; ?>
				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
					<li><a href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'التالي', 'matjar-pro' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>
	<?php endif; ?>

<?php else : ?>

	<div class="mx-auto max-w-sm py-8 text-center">
		<span class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-full bg-surface text-faint" aria-hidden="true">
			<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 24 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>
		<h2 class="m-0 text-lg font-bold text-ink"><?php esc_html_e( 'لا طلبات بعد', 'matjar-pro' ); ?></h2>
		<p class="mb-5 mt-2 text-sm leading-relaxed text-body"><?php esc_html_e( 'أول طلب لك سيظهر هنا مع حالته ومواعيد توصيله.', 'matjar-pro' ); ?></p>
		<a class="mp-btn mp-btn--cta" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">
			<?php esc_html_e( 'ابدأ التصفّح', 'matjar-pro' ); ?>
		</a>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
