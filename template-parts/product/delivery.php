<?php
/**
 * موعد التوصيل المتوقع.
 *
 * تاريخ ملموس لا «3 إلى 5 أيام عمل». السؤال الأول قبل الشراء في السوق
 * المستهدف هو «متى يوصلني؟»، والتاريخ الصريح يزيله.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_estimate = matjar_pro_delivery_estimate();

if ( '' === $mp_estimate ) {
	return;
}
?>
<p class="mp-single__delivery">
	<?php echo matjar_pro_get_icon( 'truck', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<span><?php echo esc_html( $mp_estimate ); ?></span>
</p>
