<?php
/**
 * الدخول والتسجيل.
 *
 * @package MatjarPro
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_customer_login_form' );
?>
<div class="mx-auto grid max-w-3xl gap-5 <?php echo 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ? 'sm:grid-cols-2' : 'max-w-md'; ?>">

	<section class="mp-account__card">
		<h2 class="m-0 text-lg font-bold text-ink"><?php esc_html_e( 'تسجيل الدخول', 'matjar-pro' ); ?></h2>

		<form class="woocommerce-form woocommerce-form-login login flex flex-col gap-3" method="post">
			<?php do_action( 'woocommerce_login_form_start' ); ?>

			<p class="form-row">
				<label class="mp-label" for="username"><?php esc_html_e( 'البريد الإلكتروني أو اسم المستخدم', 'matjar-pro' ); ?></label>
				<input type="text" class="mp-field" name="username" id="username" autocomplete="username" dir="ltr" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput ?>">
			</p>

			<p class="form-row">
				<label class="mp-label" for="password"><?php esc_html_e( 'كلمة المرور', 'matjar-pro' ); ?></label>
				<input class="mp-field" type="password" name="password" id="password" autocomplete="current-password" dir="ltr">
			</p>

			<?php do_action( 'woocommerce_login_form' ); ?>

			<label class="mp-remember">
				<input class="h-4 w-4" name="rememberme" type="checkbox" id="rememberme" value="forever">
				<span><?php esc_html_e( 'تذكّرني', 'matjar-pro' ); ?></span>
			</label>

			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>

			<button type="submit" class="mp-btn mp-btn--cta w-full" name="login" value="<?php esc_attr_e( 'دخول', 'matjar-pro' ); ?>">
				<?php esc_html_e( 'دخول', 'matjar-pro' ); ?>
			</button>

			<a class="self-start text-xs font-semibold text-cta no-underline hover:underline" href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
				<?php esc_html_e( 'نسيت كلمة المرور؟', 'matjar-pro' ); ?>
			</a>

			<?php do_action( 'woocommerce_login_form_end' ); ?>
		</form>
	</section>

	<?php if ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) ) : ?>
		<section class="mp-account__card">
			<h2 class="m-0 text-lg font-bold text-ink"><?php esc_html_e( 'حساب جديد', 'matjar-pro' ); ?></h2>

			<form method="post" class="woocommerce-form woocommerce-form-register register flex flex-col gap-3" <?php do_action( 'woocommerce_register_form_tag' ); ?>>
				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
					<p class="form-row">
						<label class="mp-label" for="reg_username"><?php esc_html_e( 'اسم المستخدم', 'matjar-pro' ); ?></label>
						<input type="text" class="mp-field" name="username" id="reg_username" autocomplete="username" dir="ltr" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput ?>">
					</p>
				<?php endif; ?>

				<p class="form-row">
					<label class="mp-label" for="reg_email"><?php esc_html_e( 'البريد الإلكتروني', 'matjar-pro' ); ?></label>
					<input type="email" class="mp-field" name="email" id="reg_email" autocomplete="email" dir="ltr" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput ?>">
				</p>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
					<p class="form-row">
						<label class="mp-label" for="reg_password"><?php esc_html_e( 'كلمة المرور', 'matjar-pro' ); ?></label>
						<input type="password" class="mp-field" name="password" id="reg_password" autocomplete="new-password" dir="ltr">
					</p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>

				<button type="submit" class="mp-btn mp-btn--outline w-full" name="register" value="<?php esc_attr_e( 'إنشاء الحساب', 'matjar-pro' ); ?>">
					<?php esc_html_e( 'إنشاء الحساب', 'matjar-pro' ); ?>
				</button>

				<?php do_action( 'woocommerce_register_form_end' ); ?>
			</form>
		</section>
	<?php endif; ?>
</div>
<?php
do_action( 'woocommerce_after_customer_login_form' );
