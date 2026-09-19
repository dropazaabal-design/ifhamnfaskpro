<?php
/**
 * تفاصيل طلب واحد.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_progress = matjar_pro_order_progress( $order );
$mp_status   = matjar_pro_order_status_meta( $order->get_status() );
?>
<div class="flex flex-col gap-5">

	<a class="flex items-center gap-1.5 self-start text-xs font-semibold text-body no-underline hover:text-cta" href="<?php echo esc_url( wc_get_endpoint_url( 'orders' ) ); ?>">
		<?php echo matjar_pro_get_icon( 'back', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php esc_html_e( 'كل الطلبات', 'matjar-pro' ); ?>
	</a>

	<section class="mp-account__card">
		<div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
			<h2 class="m-0 text-base font-bold text-ink">
				<span class="mp-num">#<?php echo esc_html( $order->get_order_number() ); ?></span>
			</h2>
			<time class="grow text-xs text-faint" datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>">
				<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
			</time>
			<span class="mp-order__status <?php echo esc_attr( $mp_status['class'] ); ?>">
				<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
			</span>
		</div>

		<?php if ( '' !== $mp_status['note'] ) : ?>
			<p class="m-0 text-sm leading-relaxed text-body"><?php echo esc_html( $mp_status['note'] ); ?></p>
		<?php endif; ?>

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
		<?php endif; ?>
	</section>

	<?php do_action( 'woocommerce_view_order', $order->get_id() ); ?>
</div>
