<?php
/**
 * شرائح الفلاتر المُفعَّلة.
 *
 * تُظهر ما يُفلتَر به الآن بلا فتح اللوح، وكل شريحة تُزال بنقرة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_chips = matjar_pro_active_filters();
?>
<div class="mp-active-filters" data-mp-active-filters>
	<?php if ( ! empty( $mp_chips ) ) : ?>
		<?php foreach ( $mp_chips as $mp_chip ) : ?>
			<a class="mp-chip" href="<?php echo esc_url( $mp_chip['url'] ); ?>" data-mp-filter-link>
				<span><?php echo esc_html( $mp_chip['label'] ); ?></span>
				<?php echo matjar_pro_get_icon( 'close', array( 'size' => 12 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		<?php endforeach; ?>
	<?php endif; ?>
</div>
