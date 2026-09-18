<?php
/**
 * محتوى الفلترة — يُستدعى في عمود الديسكتوب وفي لوح الجوال.
 *
 * كل خيار رابط GET عادي، وفلتر السعر نموذج GET عادي: الفلترة تعمل كاملة
 * بلا JavaScript. طبقة AJAX تستبدل هذا الجزء بعد كل تغيير لتحديث الحالات.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_context    = isset( $args['context'] ) ? sanitize_key( $args['context'] ) : 'aside';
$mp_attributes = matjar_pro_filter_attributes();
$mp_bounds     = matjar_pro_price_bounds();
$mp_price      = matjar_pro_chosen_price();
$mp_count      = matjar_pro_filter_count();
?>
<div class="mp-filter" data-mp-filter-body="<?php echo esc_attr( $mp_context ); ?>">

	<?php if ( $mp_count > 0 ) : ?>
		<a class="mp-filter__clear" href="<?php echo esc_url( matjar_pro_filter_clear_url() ); ?>" data-mp-filter-link>
			<?php echo matjar_pro_get_icon( 'close', array( 'size' => 13 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php
			printf(
				/* translators: %s: عدد الفلاتر المُفعَّلة. */
				esc_html__( 'إزالة الفلاتر (%s)', 'matjar-pro' ),
				'<span class="mp-num">' . esc_html( number_format_i18n( $mp_count ) ) . '</span>'
			);
			?>
		</a>
	<?php endif; ?>

	<?php foreach ( $mp_attributes as $mp_taxonomy => $mp_attribute ) : ?>
		<?php $mp_chosen = matjar_pro_chosen_filter_terms( $mp_taxonomy ); ?>
		<section class="mp-filter__group">
			<h3 class="mp-filter__title"><?php echo esc_html( $mp_attribute['label'] ); ?></h3>

			<div class="mp-filter__options">
				<?php foreach ( $mp_attribute['terms'] as $mp_term ) : ?>
					<?php
					$mp_active = in_array( $mp_term->slug, $mp_chosen, true );
					$mp_colour = matjar_pro_get_swatch_color( $mp_taxonomy, $mp_term->slug );
					?>
					<a
						class="mp-swatch<?php echo $mp_colour ? ' mp-swatch--colour' : ''; ?> no-underline"
						href="<?php echo esc_url( matjar_pro_filter_toggle_url( $mp_taxonomy, $mp_term->slug ) ); ?>"
						data-mp-filter-link
						role="checkbox"
						aria-checked="<?php echo $mp_active ? 'true' : 'false'; ?>"
						<?php if ( $mp_colour ) : ?>
							style="--mp-swatch: <?php echo esc_attr( $mp_colour ); ?>"
							aria-label="<?php echo esc_attr( $mp_term->name ); ?>"
						<?php endif; ?>
					><?php
						if ( ! $mp_colour ) {
							echo esc_html( $mp_term->name );
						}
					?></a>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endforeach; ?>

	<?php if ( $mp_bounds ) : ?>
		<?php
		$mp_from = null !== $mp_price['min'] ? $mp_price['min'] : $mp_bounds['min'];
		$mp_to   = null !== $mp_price['max'] ? $mp_price['max'] : $mp_bounds['max'];
		$mp_step = max( 1, (int) round( ( $mp_bounds['max'] - $mp_bounds['min'] ) / 100 ) );
		?>
		<section class="mp-filter__group">
			<h3 class="mp-filter__title"><?php esc_html_e( 'السعر', 'matjar-pro' ); ?></h3>

			<form class="mp-price" method="get" action="<?php echo esc_url( matjar_pro_filter_base_url() ); ?>" data-mp-filter-form>
				<?php matjar_pro_filter_hidden_inputs(); ?>

				<div class="mp-range" data-mp-range>
					<span class="mp-range__track" aria-hidden="true">
						<span class="mp-range__fill" data-mp-range-fill></span>
					</span>

					<label class="sr-only" for="mp-min-<?php echo esc_attr( $mp_context ); ?>"><?php esc_html_e( 'أدنى سعر', 'matjar-pro' ); ?></label>
					<input
						id="mp-min-<?php echo esc_attr( $mp_context ); ?>"
						type="range" name="min_price" data-mp-range-min
						min="<?php echo esc_attr( (string) $mp_bounds['min'] ); ?>"
						max="<?php echo esc_attr( (string) $mp_bounds['max'] ); ?>"
						step="<?php echo esc_attr( (string) $mp_step ); ?>"
						value="<?php echo esc_attr( (string) $mp_from ); ?>">

					<label class="sr-only" for="mp-max-<?php echo esc_attr( $mp_context ); ?>"><?php esc_html_e( 'أعلى سعر', 'matjar-pro' ); ?></label>
					<input
						id="mp-max-<?php echo esc_attr( $mp_context ); ?>"
						type="range" name="max_price" data-mp-range-max
						min="<?php echo esc_attr( (string) $mp_bounds['min'] ); ?>"
						max="<?php echo esc_attr( (string) $mp_bounds['max'] ); ?>"
						step="<?php echo esc_attr( (string) $mp_step ); ?>"
						value="<?php echo esc_attr( (string) $mp_to ); ?>">
				</div>

				<p class="mp-price__values">
					<output data-mp-range-out-min><?php echo wp_kses_post( wc_price( $mp_from ) ); ?></output>
					<span aria-hidden="true">–</span>
					<output data-mp-range-out-max><?php echo wp_kses_post( wc_price( $mp_to ) ); ?></output>
				</p>

				<button type="submit" class="mp-btn mp-btn--outline w-full min-h-[44px] text-sm">
					<?php esc_html_e( 'تطبيق', 'matjar-pro' ); ?>
				</button>
			</form>
		</section>
	<?php endif; ?>

	<?php if ( is_active_sidebar( 'shop-sidebar' ) ) : ?>
		<div class="mp-filter__widgets">
			<?php dynamic_sidebar( 'shop-sidebar' ); ?>
		</div>
	<?php endif; ?>
</div>
