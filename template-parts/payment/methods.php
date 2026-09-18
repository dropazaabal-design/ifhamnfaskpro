<?php
/**
 * طرق الدفع المتاحة — مكوّن واحد لكل مواضع العرض.
 *
 * السبب في وجوده: المشتري في الخليج يسأل «هل أدفع عند الاستلام؟» قبل أن
 * يسأل عن أي شيء آخر، ولا يجب أن ينتظر الخطوة الأخيرة ليعرف. المكوّن نفسه
 * يُطبع في صفحة المنتج وفي لوح السلة وفي قُمع الدفع، فما يراه في الثلاثة
 * واحد ولا يتفاجأ بشيء تغيّر.
 *
 * الوسائل مُجمَّعة بأدوارها لا مسرودة سرداً: صفٌّ من تسع شارات متشابهة لا
 * يُقرأ، وأربع مجموعات معنونة تُقرأ بلمحة.
 *
 * الوسائط:
 *   variant  panel  كتلة كاملة بعنوان (صفحة المنتج، صفحة السلة).
 *            row    صفّ مضغوط (لوح السلة، التذييل).
 *            funnel شريط قُمع الدفع.
 *   cod      false  لإخفاء بطاقة الدفع عند الاستلام (حيث تُعرض وحدها).
 *   dark     true   نسخة الخلفيات الداكنة (التذييل).
 *   skip     مجموعات تُحجب في هذا السياق.
 *   title    عنوان بديل، أو '' لإخفائه.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_variant = isset( $args['variant'] ) ? sanitize_key( $args['variant'] ) : 'panel';
$mp_groups  = matjar_pro_grouped_payment_methods();

if ( empty( $mp_groups ) ) {
	return;
}

$mp_show_cod = ! isset( $args['cod'] ) || $args['cod'];
$mp_cod      = $mp_show_cod && isset( $mp_groups['cash'] ) ? $mp_groups['cash'] : null;

// الدفع عند الاستلام يُعرض بطاقةً مستقلّة، فيُرفع من صفّ المجموعات.
unset( $mp_groups['cash'] );

// مجموعات يطلب السياق حجبها: صفحة المنتج تعرض التقسيط فوق الزر بسعره،
// فلا تُعاد أسماء مزوّديه في اللوحة أسفله.
foreach ( (array) ( $args['skip'] ?? array() ) as $mp_skip ) {
	unset( $mp_groups[ sanitize_key( $mp_skip ) ] );
}

$mp_title = isset( $args['title'] )
	? $args['title']
	: __( 'طرق الدفع المتاحة', 'matjar-pro' );

if ( 'row' === $mp_variant || 'funnel' === $mp_variant ) {
	$mp_title = isset( $args['title'] ) ? $args['title'] : '';
}
?>
<div class="mp-pay mp-pay--<?php echo esc_attr( $mp_variant ); ?><?php echo empty( $args['dark'] ) ? '' : ' mp-pay--dark'; ?>"
	aria-label="<?php esc_attr_e( 'طرق الدفع المتاحة', 'matjar-pro' ); ?>">

	<?php if ( '' !== $mp_title ) : ?>
		<p class="mp-pay__title">
			<?php echo matjar_pro_get_icon( 'lock', array( 'size' => 14 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php echo esc_html( $mp_title ); ?>
		</p>
	<?php endif; ?>

	<?php if ( $mp_cod ) : ?>
		<?php
		/*
		 * بطاقة الدفع عند الاستلام: أكبر من شارة وأصغر من إعلان. حدٌّ ملوّن
		 * وأيقونة وسطر يشرح ما سيحدث فعلاً، فلا يبقى سؤال.
		 */
		$mp_cod_method = reset( $mp_cod['methods'] );
		$mp_cod_note   = matjar_pro_mod( 'matjar_pro_cod_note' );
		$mp_cod_note   = '' !== $mp_cod_note ? $mp_cod_note : ( $mp_cod_method['note'] ?? '' );
		?>
		<div class="mp-cod">
			<span class="mp-cod__mark" aria-hidden="true">
				<?php echo matjar_pro_get_icon( 'cash', array( 'size' => 20 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>

			<div class="mp-cod__body">
				<strong class="mp-cod__label"><?php echo esc_html( $mp_cod_method['label'] ); ?></strong>

				<?php if ( '' !== $mp_cod_note ) : ?>
					<span class="mp-cod__note"><?php echo esc_html( $mp_cod_note ); ?></span>
				<?php endif; ?>
			</div>

			<span class="mp-cod__tick" aria-hidden="true">
				<?php echo matjar_pro_get_icon( 'check', array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $mp_groups ) ) : ?>
		<ul class="mp-pay__groups">
			<?php foreach ( $mp_groups as $mp_key => $mp_group ) : ?>
				<li class="mp-pay__group" data-mp-pay-group="<?php echo esc_attr( $mp_key ); ?>">
					<span class="mp-pay__group-icon" aria-hidden="true">
						<?php echo matjar_pro_get_icon( $mp_group['icon'], array( 'size' => 15 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</span>

					<span class="mp-pay__group-label"><?php echo esc_html( $mp_group['label'] ); ?></span>

					<span class="mp-pay__marks">
						<?php foreach ( $mp_group['methods'] as $mp_slug => $mp_method ) : ?>
							<span class="mp-pay__mark mp-badge--<?php echo esc_attr( $mp_slug ); ?>">
								<?php echo esc_html( $mp_method['label'] ); ?>
							</span>
						<?php endforeach; ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
