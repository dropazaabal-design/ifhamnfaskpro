<?php
/**
 * شاشة المتجر التجريبي في لوحة التحكم.
 *
 * مفصولة عن المستورد: المنطق يُختبر بلا لوحة، والشاشة لا تُحمَّل خارجها.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

/**
 * صلاحية التشغيل.
 *
 * تعديل القالب لا يكفي: المستورد ينشئ منشورات ويعدّل خيارات الموقع،
 * وهذه صلاحية مدير لا محرّر مظهر.
 *
 * @return string
 */
function matjar_pro_demo_capability() {
	return 'manage_options';
}

/**
 * يسجّل الصفحة تحت «المظهر».
 */
function matjar_pro_demo_menu() {
	add_theme_page(
		__( 'المتجر التجريبي', 'matjar-pro' ),
		__( 'المتجر التجريبي', 'matjar-pro' ),
		matjar_pro_demo_capability(),
		'matjar-pro-demo',
		'matjar_pro_demo_screen'
	);
}
add_action( 'admin_menu', 'matjar_pro_demo_menu' );

/**
 * ينفّذ الإجراء المطلوب قبل رسم الصفحة.
 *
 * التنفيذ في admin_post لا في رسم الصفحة: الاستيراد يكتب، والكتابة في
 * طلب GET تتكرّر مع كل تحديث للصفحة.
 */
function matjar_pro_demo_handle() {
	if ( ! current_user_can( matjar_pro_demo_capability() ) ) {
		wp_die( esc_html__( 'لا تملك صلاحية هذا الإجراء.', 'matjar-pro' ), 403 );
	}

	check_admin_referer( 'matjar_pro_demo' );

	$action = isset( $_POST['matjar_pro_demo_action'] ) ? sanitize_key( wp_unslash( $_POST['matjar_pro_demo_action'] ) ) : '';
	$back   = admin_url( 'themes.php?page=matjar-pro-demo' );

	if ( 'import' === $action ) {
		// متجر عامل لا يُكتب فوقه إلا بتأكيد مكتوب.
		if ( matjar_pro_demo_foreign_products() && empty( $_POST['matjar_pro_demo_confirm'] ) ) {
			wp_safe_redirect( add_query_arg( 'mp_demo', 'needs-confirm', $back ) );

			exit;
		}

		$result = matjar_pro_demo_import();

		if ( is_wp_error( $result ) ) {
			wp_safe_redirect( add_query_arg( 'mp_demo', 'no-wc', $back ) );

			exit;
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'mp_demo'  => 'imported',
					'products' => (int) $result['products'],
					'pages'    => (int) $result['pages'],
				),
				$back
			)
		);

		exit;
	}

	if ( 'remove' === $action ) {
		$removed = matjar_pro_demo_remove();

		wp_safe_redirect( add_query_arg( array( 'mp_demo' => 'removed', 'items' => $removed ), $back ) );

		exit;
	}

	wp_safe_redirect( $back );

	exit;
}
add_action( 'admin_post_matjar_pro_demo', 'matjar_pro_demo_handle' );

/**
 * يرسم الشاشة.
 */
function matjar_pro_demo_screen() {
	if ( ! current_user_can( matjar_pro_demo_capability() ) ) {
		return;
	}

	$imported = matjar_pro_demo_imported();
	$foreign  = matjar_pro_demo_foreign_products();
	$notice   = isset( $_GET['mp_demo'] ) ? sanitize_key( wp_unslash( $_GET['mp_demo'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'المتجر التجريبي — Matjar Pro', 'matjar-pro' ); ?></h1>

		<?php if ( 'imported' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php
				printf(
					/* translators: 1: عدد المنتجات. 2: عدد الصفحات. */
					esc_html__( 'تمّ: %1$s منتجاً و%2$s صفحة. افتح المتجر لتراه.', 'matjar-pro' ),
					esc_html( number_format_i18n( (int) ( $_GET['products'] ?? 0 ) ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					esc_html( number_format_i18n( (int) ( $_GET['pages'] ?? 0 ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				);
				?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'عرض المتجر', 'matjar-pro' ); ?></a>
			</p></div>
		<?php elseif ( 'removed' === $notice ) : ?>
			<div class="notice notice-success is-dismissible"><p>
				<?php
				printf(
					/* translators: %s: عدد العناصر. */
					esc_html__( 'حُذف %s عنصراً من المحتوى التجريبي. لم يُمسّ أي محتوى آخر.', 'matjar-pro' ),
					esc_html( number_format_i18n( (int) ( $_GET['items'] ?? 0 ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				);
				?>
			</p></div>
		<?php elseif ( 'no-wc' === $notice ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'ووكومرس غير مفعّل. فعّله أوّلاً ثم أعد المحاولة.', 'matjar-pro' ); ?></p></div>
		<?php elseif ( 'needs-confirm' === $notice ) : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'متجرك يحتوي منتجات ليست من المحتوى التجريبي. علّم مربّع التأكيد إن كنت تريد الاستيراد رغم ذلك.', 'matjar-pro' ); ?></p></div>
		<?php endif; ?>

		<?php if ( ! matjar_pro_has_woocommerce() ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'هذه الأداة تحتاج ووكومرس مفعّلاً.', 'matjar-pro' ); ?></p></div>
		<?php endif; ?>

		<div class="card" style="max-width:46rem;padding:1.5rem 1.75rem;">
			<?php if ( $imported ) : ?>

				<h2 style="margin-top:0;"><?php esc_html_e( 'المحتوى التجريبي مُركَّب', 'matjar-pro' ); ?></h2>
				<p><?php esc_html_e( 'متجرك يعرض الآن منتجات وأقسام وصفحات سياسات جاهزة. استبدلها بمنتجاتك الحقيقية تدريجياً، أو احذفها كلّها بضغطة حين تنتهي.', 'matjar-pro' ); ?></p>
				<p><strong><?php esc_html_e( 'الحذف يزيل ما أنشأته هذه الأداة وحده.', 'matjar-pro' ); ?></strong> <?php esc_html_e( 'كل عنصر موسوم عند إنشائه، فمنتجاتك وصفحاتك وصورك لا تُمسّ.', 'matjar-pro' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'matjar_pro_demo' ); ?>
					<input type="hidden" name="action" value="matjar_pro_demo">
					<input type="hidden" name="matjar_pro_demo_action" value="remove">
					<?php submit_button( __( 'حذف المحتوى التجريبي', 'matjar-pro' ), 'delete', 'submit', false ); ?>
				</form>

			<?php else : ?>

				<h2 style="margin-top:0;"><?php esc_html_e( 'ابدأ بمتجر كامل لا بصفحة فارغة', 'matjar-pro' ); ?></h2>
				<p><?php esc_html_e( 'ضغطة واحدة تبني متجراً يعمل: ٢٠ منتجاً في أربعة أقسام، بينها منتجات بمقاسات وألوان، ومخفَّضة، ونافدة، وبالطلب المسبق — حتى ترى كل حالة يرسمها القالب فعلاً. ومعها صفحات الشحن والإرجاع والأسئلة الشائعة، وقائمة تنقّل، وصفحة رئيسية مضبوطة.', 'matjar-pro' ); ?></p>
				<p><?php esc_html_e( 'الصور رسوم مسطّحة نملكها بالكامل، لا صور فوتوغرافية: شحن صور منتجات حقيقية مع قالب تجاري يورّث كل مشترٍ نزاع حقوق لم يطلبه. استبدلها بصور بضاعتك.', 'matjar-pro' ); ?></p>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'matjar_pro_demo' ); ?>
					<input type="hidden" name="action" value="matjar_pro_demo">
					<input type="hidden" name="matjar_pro_demo_action" value="import">

					<?php if ( $foreign ) : ?>
						<p style="padding:.75rem 1rem;border-inline-start:4px solid #dba617;background:#fcf9e8;">
							<label>
								<input type="checkbox" name="matjar_pro_demo_confirm" value="1">
								<?php esc_html_e( 'متجري يحتوي منتجات بالفعل، وأفهم أن الاستيراد سيضيف منتجات تجريبية فوقها.', 'matjar-pro' ); ?>
							</label>
						</p>
					<?php endif; ?>

					<?php submit_button( __( 'ركّب المتجر التجريبي', 'matjar-pro' ), 'primary', 'submit', false, matjar_pro_has_woocommerce() ? array() : array( 'disabled' => 'disabled' ) ); ?>
				</form>

			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * تنبيه بعد تفعيل القالب: التاجر لا يبحث عن أداة لا يعرف بوجودها.
 */
function matjar_pro_demo_notice() {
	if ( ! current_user_can( matjar_pro_demo_capability() ) || matjar_pro_demo_imported() ) {
		return;
	}

	$screen = get_current_screen();

	if ( $screen && 'appearance_page_matjar-pro-demo' === $screen->id ) {
		return;
	}

	if ( ! get_option( 'matjar_pro_demo_offer', true ) ) {
		return;
	}
	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Matjar Pro:', 'matjar-pro' ); ?></strong>
			<?php esc_html_e( 'تقدر تبدأ بمتجر كامل بمنتجات وأقسام وصفحات بدل صفحة فارغة.', 'matjar-pro' ); ?>
			<a href="<?php echo esc_url( admin_url( 'themes.php?page=matjar-pro-demo' ) ); ?>"><?php esc_html_e( 'ركّب المتجر التجريبي', 'matjar-pro' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'matjar_pro_demo_notice' );
