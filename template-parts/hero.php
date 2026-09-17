<?php
/**
 * الهيرو.
 *
 * صورة واحدة ثابتة، لا كاروسيل: الشرائح بعد الأولى تحصل على نسبة تفاعل
 * مهملة وتُثقل قياس LCP مقابل لا شيء.
 *
 * التخطيط الافتراضي «الصورة ثم النص» لا يحتاج طبقة تعتيم، فتظهر صورة
 * المنتج بكامل ألوانها ويبقى التباين مضموناً بلا افتراض عن الصورة.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

if ( ! matjar_pro_has_hero() ) {
	return;
}

$mp_image   = (int) matjar_pro_mod( 'matjar_pro_hero_image' );
$mp_overlay = 'overlay' === matjar_pro_mod( 'matjar_pro_hero_layout' );
?>
<section class="mp-hero <?php echo $mp_overlay ? 'relative' : 'bg-surface'; ?>">

	<?php if ( $mp_image ) : ?>
		<div class="<?php echo $mp_overlay ? 'absolute inset-0' : 'relative'; ?>">
			<?php
			echo wp_get_attachment_image(
				$mp_image,
				'full',
				false,
				array(
					'class'         => 'aspect-[4/5] w-full object-cover sm:aspect-[16/9] lg:aspect-[21/9]',
					'fetchpriority' => 'high',
					'decoding'      => 'sync',
					'sizes'         => '100vw',
					'alt'           => esc_attr( trim( (string) matjar_pro_mod( 'matjar_pro_hero_headline' ) ) ),
				)
			);
			?>
			<?php if ( $mp_overlay ) : ?>
				<?php
				/*
				 * شفافية 65% ليست ذوقاً. النص الأبيض فوق طبقة كحلية بهذه
				 * الشفافية يعطي 5.25:1 حتى فوق صورة بيضاء ناصعة، وهي أسوأ
				 * حالة ممكنة. أي قيمة أقل (60% تعطي 4.47:1) تعني أن مقروئية
				 * العنوان تصبح رهينة الصورة التي يرفعها التاجر.
				 */
				?>
				<div class="absolute inset-0 bg-ink/65" aria-hidden="true"></div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div class="<?php echo $mp_overlay ? 'relative mx-auto flex min-h-[420px] max-w-screen-xl flex-col items-start justify-end gap-3 px-4 py-8 sm:min-h-[480px] lg:min-h-[560px] lg:justify-center' : 'mx-auto flex max-w-screen-xl flex-col items-start gap-3 px-4 py-6'; ?> mp-hero__copy">
		<?php get_template_part( 'template-parts/hero-copy' ); ?>
	</div>
</section>
