<?php
/**
 * إشعارات النشاط.
 *
 * النصوص تُطبع مصفوفةَ JSON في وسم script من نوع application/json: لا
 * كود مضمّن يعترضه Content-Security-Policy، ولا طلب شبكة، ولا استعلام.
 * يقرؤها JavaScript مرّة ويدوّرها.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_messages = matjar_pro_proof_messages();
$mp_every    = max( 4, (int) matjar_pro_mod( 'matjar_pro_proof_interval' ) );
?>
<script type="application/json" id="mp-proof-data"><?php
	echo wp_json_encode(
		array(
			'messages' => $mp_messages,
			'every'    => $mp_every * 1000,
		),
		JSON_UNESCAPED_UNICODE
	);
?></script>

<div class="mp-toast" data-mp-proof hidden>
	<span class="mp-toast__mark" aria-hidden="true">
		<?php echo matjar_pro_get_icon( 'cart', array( 'size' => 16 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</span>

	<p class="mp-toast__text m-0" data-mp-proof-text></p>

	<button type="button" class="mp-toast__close" data-mp-proof-close
		aria-label="<?php esc_attr_e( 'إخفاء الإشعارات', 'matjar-pro' ); ?>">
		<?php echo matjar_pro_get_icon( 'close', array( 'size' => 14 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</button>
</div>
