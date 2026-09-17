<?php
/**
 * ترويسة الصفحة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_announcement = matjar_pro_mod( 'matjar_pro_announcement_enabled' ) ? matjar_pro_announcement_message() : '';
$mp_has_wc       = matjar_pro_has_woocommerce();
$mp_count        = ( $mp_has_wc && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class( 'bg-cream font-sans text-base text-body antialiased' ); ?>>
<?php wp_body_open(); ?>

<a class="mp-skip" href="#mp-main">
	<?php esc_html_e( 'تخطَّ إلى المحتوى', 'matjar-pro' ); ?>
</a>

<?php if ( '' !== $mp_announcement ) : ?>
	<div class="mp-announcement bg-ink text-white" data-mp-announcement>
		<div class="mx-auto flex max-w-screen-xl items-center gap-2 px-3 py-2">
			<span class="shrink-0 text-accent-ink" aria-hidden="true">
				<?php echo matjar_pro_get_icon( 'truck', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
			<p class="mp-announcement__text m-0 grow text-center text-xs font-semibold sm:text-sm">
				<?php echo esc_html( $mp_announcement ); ?>
			</p>
			<button type="button" class="-me-1 grid h-8 w-8 shrink-0 place-items-center rounded text-white/70 transition hover:text-white" data-mp-dismiss aria-label="<?php esc_attr_e( 'إغلاق الإعلان', 'matjar-pro' ); ?>">
				<?php echo matjar_pro_get_icon( 'close', array( 'size' => 13 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</button>
		</div>
	</div>
<?php endif; ?>

<header class="mp-header" data-mp-header data-mp-compact="false">

	<div class="mx-auto flex h-14 max-w-screen-xl items-center gap-2 px-3 lg:h-[72px] lg:gap-5">

		<button type="button" class="mp-icon-btn lg:hidden" data-mp-drawer-open="nav" aria-expanded="false" aria-controls="mp-nav-drawer" aria-label="<?php esc_attr_e( 'القائمة', 'matjar-pro' ); ?>">
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

		<div class="hidden grow justify-center lg:flex">
			<div class="w-full max-w-xl">
				<?php get_search_form( array( 'mp_context' => 'header' ) ); ?>
			</div>
		</div>

		<button type="button" class="mp-icon-btn lg:hidden" data-mp-drawer-open="search" aria-expanded="false" aria-controls="mp-search-drawer" aria-label="<?php esc_attr_e( 'البحث', 'matjar-pro' ); ?>">
			<?php echo matjar_pro_get_icon( 'search', array( 'size' => 21 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>

		<?php if ( $mp_has_wc ) : ?>
			<a class="mp-icon-btn hidden lg:grid" href="<?php echo esc_url( get_permalink( wc_get_page_id( 'myaccount' ) ) ); ?>" aria-label="<?php esc_attr_e( 'حسابي', 'matjar-pro' ); ?>">
				<?php echo matjar_pro_get_icon( 'user', array( 'size' => 21 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>

			<a class="mp-icon-btn relative" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'السلة', 'matjar-pro' ); ?>">
				<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 21 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span class="mp-cart-count<?php echo $mp_count > 0 ? '' : ' hidden'; ?>" data-count="<?php echo esc_attr( (string) $mp_count ); ?>">
					<?php echo esc_html( number_format_i18n( $mp_count ) ); ?>
				</span>
			</a>
		<?php endif; ?>
	</div>

	<?php if ( has_nav_menu( 'primary' ) ) : ?>
		<div class="hidden border-t border-line lg:block">
			<nav class="mx-auto max-w-screen-xl px-3" aria-label="<?php esc_attr_e( 'القائمة الرئيسية', 'matjar-pro' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'depth'          => 2,
						'menu_class'     => 'mp-nav',
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		</div>
	<?php endif; ?>
</header>
