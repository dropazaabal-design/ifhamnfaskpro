<?php
/**
 * نصوص الهيرو — مفصولة في ملفها ليستهلكها التحديث الجزئي في المعاينة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_eyebrow  = trim( (string) matjar_pro_mod( 'matjar_pro_hero_eyebrow' ) );
$mp_headline = trim( (string) matjar_pro_mod( 'matjar_pro_hero_headline' ) );
$mp_subtext  = trim( (string) matjar_pro_mod( 'matjar_pro_hero_subtext' ) );
$mp_cta      = trim( (string) matjar_pro_mod( 'matjar_pro_hero_cta_label' ) );
$mp_overlay  = 'overlay' === matjar_pro_mod( 'matjar_pro_hero_layout' );
?>
<?php if ( '' !== $mp_eyebrow ) : ?>
	<p class="m-0 text-xs font-bold tracking-widest <?php echo $mp_overlay ? 'text-white/85' : 'text-cta'; ?>">
		<?php echo esc_html( $mp_eyebrow ); ?>
	</p>
<?php endif; ?>

<?php if ( '' !== $mp_headline ) : ?>
	<h1 class="m-0 text-2xl font-bold leading-tight <?php echo $mp_overlay ? 'text-white' : 'text-ink'; ?> sm:text-3xl lg:text-4xl">
		<?php echo esc_html( $mp_headline ); ?>
	</h1>
<?php endif; ?>

<?php if ( '' !== $mp_subtext ) : ?>
	<p class="m-0 max-w-prose text-sm leading-relaxed <?php echo $mp_overlay ? 'text-white/90' : 'text-body'; ?> sm:text-base">
		<?php echo esc_html( $mp_subtext ); ?>
	</p>
<?php endif; ?>

<?php if ( '' !== $mp_cta ) : ?>
	<a class="mp-btn mp-btn--cta mp-btn--lg mt-1 w-full sm:w-auto" href="<?php echo esc_url( matjar_pro_hero_cta_url() ); ?>">
		<?php echo esc_html( $mp_cta ); ?>
		<?php
		// السهم مكتوب أصلاً يشير يساراً، أي «إلى الأمام» في العربية. قلبه
		// بـ mp-flip يجعله يشير إلى الخلف، وهو عكس معنى الزر.
		echo matjar_pro_get_icon( 'arrow', array( 'size' => 18 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
	</a>
<?php endif; ?>
