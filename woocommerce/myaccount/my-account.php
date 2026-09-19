<?php
/**
 * غلاف صفحة الحساب.
 *
 * القائمة شرائح أفقية على الجوال وعمود على الديسكتوب: قائمة رأسية على
 * الجوال تدفع المحتوى كله تحت الطيّة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="mp-account lg:flex lg:items-start lg:gap-8">

	<?php do_action( 'woocommerce_account_navigation' ); ?>

	<div class="woocommerce-MyAccount-content mp-account__content min-w-0 grow">
		<?php do_action( 'woocommerce_account_content' ); ?>
	</div>
</div>
