<?php
/**
 * لوح القائمة الجانبي — الجوال.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="mp-drawer" data-mp-drawer="nav" data-mp-open="false" aria-hidden="true" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'قائمة المتجر', 'matjar-pro' ); ?>">
	<div class="mp-drawer__backdrop" data-mp-drawer-backdrop></div>

	<div class="mp-drawer__panel mp-drawer__panel--side" id="mp-nav-drawer">
		<div class="flex h-14 items-center gap-2 border-b border-line px-4">
			<span class="grow font-bold text-ink"><?php esc_html_e( 'القائمة', 'matjar-pro' ); ?></span>
			<button type="button" class="-me-2 grid h-11 w-11 place-items-center rounded-lg text-ink transition hover:bg-cream" data-mp-drawer-close aria-label="<?php esc_attr_e( 'إغلاق القائمة', 'matjar-pro' ); ?>">
				<?php echo matjar_pro_get_icon( 'close', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>

		<div class="flex-1 overflow-y-auto overscroll-contain px-4 py-4">
			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<nav aria-label="<?php esc_attr_e( 'القائمة الرئيسية', 'matjar-pro' ); ?>">
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'depth'          => 2,
							'menu_class'     => 'mp-drawer__menu',
							'fallback_cb'    => false,
						)
					);
					?>
				</nav>
			<?php endif; ?>

			<?php
			$mp_terms = matjar_pro_has_woocommerce()
				? get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'hide_empty' => true,
						'parent'     => 0,
						'number'     => 12,
					)
				)
				: array();
			?>
			<?php if ( ! is_wp_error( $mp_terms ) && ! empty( $mp_terms ) ) : ?>
				<p class="mb-2 mt-6 text-xs font-bold uppercase tracking-wide text-faint">
					<?php esc_html_e( 'الأقسام', 'matjar-pro' ); ?>
				</p>
				<ul class="mp-drawer__menu">
					<?php foreach ( $mp_terms as $mp_term ) : ?>
						<li>
							<a href="<?php echo esc_url( get_term_link( $mp_term ) ); ?>">
								<span class="grow"><?php echo esc_html( $mp_term->name ); ?></span>
								<span class="mp-num text-xs text-faint"><?php echo esc_html( number_format_i18n( $mp_term->count ) ); ?></span>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<div class="border-t border-line px-4 py-4">
			<?php if ( matjar_pro_has_woocommerce() ) : ?>
				<a class="mp-btn mp-btn--outline w-full" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>">
					<?php echo matjar_pro_get_icon( 'user', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php esc_html_e( 'حسابي وطلباتي', 'matjar-pro' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>
