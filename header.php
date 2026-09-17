<?php
/**
 * ترويسة الصفحة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_announcement = matjar_pro_mod( 'matjar_pro_announcement_enabled' ) ? matjar_pro_announcement_message() : '';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'bg-cream font-sans text-body antialiased' ); ?>>
<?php wp_body_open(); ?>

<a class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:m-3 focus:rounded-lg focus:bg-ink focus:px-4 focus:py-2 focus:text-white" href="#mp-main">
	<?php esc_html_e( 'تخطَّ إلى المحتوى', 'matjar-pro' ); ?>
</a>

<?php if ( '' !== $mp_announcement ) : ?>
	<div class="mp-announcement bg-ink text-white" data-mp-announcement>
		<div class="mx-auto flex max-w-screen-xl items-center gap-2 px-3 py-2">
			<span class="text-accent-ink shrink-0" aria-hidden="true">
				<?php echo matjar_pro_get_icon( 'truck', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
			<p class="mp-announcement__text m-0 grow text-center text-xs font-semibold sm:text-sm">
				<?php echo esc_html( $mp_announcement ); ?>
			</p>
			<button type="button" class="mp-announcement__close -me-1 grid h-8 w-8 shrink-0 place-items-center rounded text-white/70 transition hover:text-white" data-mp-dismiss aria-label="<?php esc_attr_e( 'إغلاق الإعلان', 'matjar-pro' ); ?>">
				<?php echo matjar_pro_get_icon( 'close', array( 'size' => 13 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</div>
<?php endif; ?>

<header class="mp-header sticky top-0 z-40 border-b border-line bg-surface" data-mp-header>
	<div class="mx-auto flex h-14 max-w-screen-xl items-center gap-2 px-3 lg:h-16">

		<button type="button" class="grid h-11 w-11 shrink-0 place-items-center rounded-lg text-ink transition hover:bg-cream lg:hidden" data-mp-drawer-open="nav" aria-expanded="false" aria-controls="mp-nav-drawer" aria-label="<?php esc_attr_e( 'القائمة', 'matjar-pro' ); ?>">
			<?php echo matjar_pro_get_icon( 'menu', array( 'size' => 23 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>

		<div class="mp-header__brand flex grow justify-center lg:grow-0 lg:justify-start">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="text-lg font-bold tracking-tight text-ink no-underline lg:text-xl" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav class="hidden grow lg:block" aria-label="<?php esc_attr_e( 'القائمة الرئيسية', 'matjar-pro' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'depth'          => 2,
						'menu_class'     => 'mp-nav flex items-center justify-center gap-6 text-sm font-semibold',
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<button type="button" class="grid h-11 w-11 shrink-0 place-items-center rounded-lg text-ink transition hover:bg-cream" data-mp-drawer-open="search" aria-expanded="false" aria-controls="mp-search-drawer" aria-label="<?php esc_attr_e( 'البحث', 'matjar-pro' ); ?>">
			<?php echo matjar_pro_get_icon( 'search', array( 'size' => 21 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>

		<?php if ( matjar_pro_has_woocommerce() ) : ?>
			<a class="relative grid h-11 w-11 shrink-0 place-items-center rounded-lg text-ink no-underline transition hover:bg-cream" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'السلة', 'matjar-pro' ); ?>">
				<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 21 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php $mp_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
				<span class="mp-cart-count absolute end-1 top-1 grid h-[17px] min-w-[17px] place-items-center rounded-full bg-cta px-1 text-[10px] font-bold leading-none text-cta-fg<?php echo $mp_count > 0 ? '' : ' hidden'; ?>" data-count="<?php echo esc_attr( (string) $mp_count ); ?>">
					<?php echo esc_html( number_format_i18n( $mp_count ) ); ?>
				</span>
			</a>
		<?php endif; ?>
	</div>
</header>
