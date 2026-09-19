<?php
/**
 * عناويني.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$mp_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'عنوان الفاتورة', 'matjar-pro' ),
			'shipping' => __( 'عنوان التوصيل', 'matjar-pro' ),
		),
		$mp_customer_id
	);
} else {
	$mp_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array( 'billing' => __( 'عنواني', 'matjar-pro' ) ),
		$mp_customer_id
	);
}
?>
<p class="mb-5 mt-0 text-sm leading-relaxed text-body">
	<?php esc_html_e( 'عنوان محفوظ وصحيح يعني طلباً أسرع ومندوباً يجدك من أول مرة.', 'matjar-pro' ); ?>
</p>

<div class="grid gap-4 sm:grid-cols-2">
	<?php foreach ( $mp_addresses as $mp_name => $mp_title ) : ?>
		<?php $mp_address = wc_get_account_formatted_address( $mp_name ); ?>
		<section class="mp-account__card">
			<div class="flex items-baseline gap-2">
				<h2 class="m-0 grow text-base font-bold text-ink"><?php echo esc_html( $mp_title ); ?></h2>
				<a class="text-xs font-semibold text-cta no-underline hover:underline" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $mp_name ) ); ?>">
					<?php echo $mp_address ? esc_html__( 'تعديل', 'matjar-pro' ) : esc_html__( 'إضافة', 'matjar-pro' ); ?>
				</a>
			</div>

			<?php if ( $mp_address ) : ?>
				<address class="m-0 text-sm not-italic leading-relaxed text-body"><?php echo wp_kses_post( $mp_address ); ?></address>
			<?php else : ?>
				<p class="m-0 text-sm leading-relaxed text-faint"><?php esc_html_e( 'لم يُضف بعد.', 'matjar-pro' ); ?></p>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>
</div>
