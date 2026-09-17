<?php
/**
 * نموذج البحث.
 *
 * يُستدعى من الهيدر ومن لوح البحث بسياق مختلف. لوحة المفاتيح على الجوال
 * تُظهر زر «بحث» بفضل type="search" داخل form، ولا تحتاج زراً مرئياً.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_context = isset( $args['mp_context'] ) ? $args['mp_context'] : 'header';
// بادئة منفصلة عن معرّفات الألواح: 'mp-search-drawer' اسم لوح البحث نفسه،
// فلو سمّينا الحقل بالبادئة نفسها لتصادم المعرّفان في المستند الواحد.
$mp_id      = 'mp-search-field-' . $mp_context;
?>
<form role="search" method="get" class="mp-search relative w-full" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-mp-search>
	<label class="sr-only" for="<?php echo esc_attr( $mp_id ); ?>">
		<?php esc_html_e( 'ابحث في المتجر', 'matjar-pro' ); ?>
	</label>

	<div class="relative flex items-center">
		<span class="pointer-events-none absolute start-0 grid h-full w-11 place-items-center text-faint" aria-hidden="true">
			<?php echo matjar_pro_get_icon( 'search', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>

		<input
			id="<?php echo esc_attr( $mp_id ); ?>"
			class="mp-field ps-11 pe-10"
			type="search"
			name="s"
			value="<?php echo esc_attr( get_search_query() ); ?>"
			placeholder="<?php esc_attr_e( 'ابحث عن منتج…', 'matjar-pro' ); ?>"
			autocomplete="off"
			enterkeyhint="search"
			<?php echo 'drawer' === $mp_context ? 'data-mp-autofocus' : ''; ?>
			aria-describedby="<?php echo esc_attr( $mp_id ); ?>-status"
		>

		<button type="reset" class="absolute end-0 grid h-full w-10 place-items-center text-faint transition hover:text-ink" data-mp-search-clear hidden aria-label="<?php esc_attr_e( 'تفريغ البحث', 'matjar-pro' ); ?>">
			<?php echo matjar_pro_get_icon( 'close', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>
	</div>

	<?php if ( matjar_pro_has_woocommerce() ) : ?>
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>

	<p id="<?php echo esc_attr( $mp_id ); ?>-status" class="sr-only" role="status" aria-live="polite" data-mp-search-status></p>

	<div class="mp-search__results" data-mp-search-results hidden></div>
</form>
