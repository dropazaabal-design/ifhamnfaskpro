<?php
/**
 * صفحة الانقطاع — يقدّمها عامل الخدمة حين تنقطع الشبكة.
 *
 * صفحة مستقلّة بلا هيدر ولا تذييل ولا JavaScript: هي آخر ما يُعرض حين لا
 * يعمل شيء، فيجب أن تعمل وحدها.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

$mp_text = trim( (string) matjar_pro_mod( 'matjar_pro_pwa_offline_text' ) );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'لا اتصال بالإنترنت', 'matjar-pro' ); ?></title>
	<?php
	matjar_pro_print_critical_css();
	?>
</head>
<body class="bg-page font-sans text-base text-body antialiased">
	<main class="mx-auto flex min-h-screen max-w-sm flex-col items-center justify-center gap-4 px-4 text-center">
		<span class="grid h-14 w-14 place-items-center rounded-full bg-surface text-faint" aria-hidden="true">
			<?php echo matjar_pro_get_icon( 'alert', array( 'size' => 26 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</span>

		<h1 class="m-0 text-xl font-bold text-ink"><?php esc_html_e( 'لا اتصال بالإنترنت', 'matjar-pro' ); ?></h1>

		<p class="m-0 text-sm leading-relaxed text-body">
			<?php echo esc_html( '' !== $mp_text ? $mp_text : __( 'تحقّق من اتصالك ثم أعد المحاولة. الصفحات التي زرتها قبل قليل ما زالت متاحة.', 'matjar-pro' ) ); ?>
		</p>

		<a class="mp-btn mp-btn--cta" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php esc_html_e( 'إعادة المحاولة', 'matjar-pro' ); ?>
		</a>
	</main>
</body>
</html>
