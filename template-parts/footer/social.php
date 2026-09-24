<?php
/**
 * روابط التواصل في التذييل.
 *
 * زيارة متاجر الخليج تأتي من إنستغرام وتيك توك وسناب شات، فوجود الروابط في
 * التذييل ليس تزييناً: هو ما يُعيد الزائر إلى القناة التي يتابعها أصلاً.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_links = matjar_pro_active_social_links();

if ( empty( $mp_links ) ) {
	return;
}
?>
<ul class="mp-social" aria-label="<?php esc_attr_e( 'تابعنا على', 'matjar-pro' ); ?>">
	<?php foreach ( $mp_links as $mp_slug => $mp_link ) : ?>
		<li>
			<a class="mp-social__link"
				href="<?php echo esc_url( $mp_link['url'] ); ?>"
				target="_blank" rel="noopener"
				aria-label="<?php echo esc_attr( $mp_link['label'] ); ?>">
				<?php echo matjar_pro_get_icon( $mp_link['icon'], array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
