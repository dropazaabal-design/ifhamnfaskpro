<?php
/**
 * نافذة نيّة الخروج.
 *
 * تُطبع في الصفحة ومخفيّة، ولا تُفتح إلا بمنطق JavaScript. طباعتها مسبقاً
 * أرخص من بنائها عند الحاجة، ولا تُكلّف شيئاً وهي مخفيّة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_title  = trim( (string) matjar_pro_mod( 'matjar_pro_exit_title' ) );
$mp_text   = trim( (string) matjar_pro_mod( 'matjar_pro_exit_text' ) );
$mp_coupon = trim( (string) matjar_pro_mod( 'matjar_pro_exit_coupon' ) );
?>
<div class="mp-modal" data-mp-exit hidden>
	<div class="mp-modal__backdrop" data-mp-exit-close></div>

	<div class="mp-modal__panel" role="dialog" aria-modal="true" aria-labelledby="mp-exit-title">
		<button type="button" class="mp-modal__close" data-mp-exit-close
			aria-label="<?php esc_attr_e( 'إغلاق', 'matjar-pro' ); ?>">
			<?php echo matjar_pro_get_icon( 'close', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</button>

		<h2 class="mp-modal__title" id="mp-exit-title"><?php echo esc_html( $mp_title ); ?></h2>

		<?php if ( '' !== $mp_text ) : ?>
			<p class="mp-modal__text"><?php echo esc_html( $mp_text ); ?></p>
		<?php endif; ?>

		<?php if ( '' !== $mp_coupon ) : ?>
			<div class="mp-coupon-box">
				<code class="mp-coupon-box__code" data-mp-coupon><?php echo esc_html( $mp_coupon ); ?></code>

				<button type="button" class="mp-coupon-box__copy" data-mp-coupon-copy
					data-mp-copied="<?php esc_attr_e( 'نُسخ', 'matjar-pro' ); ?>">
					<?php esc_html_e( 'انسخ', 'matjar-pro' ); ?>
				</button>
			</div>
		<?php endif; ?>
	</div>
</div>
