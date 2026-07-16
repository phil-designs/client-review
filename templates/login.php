<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- standalone template page; vars are local to this include.
require_once __DIR__ . '/../includes/class-cr-settings.php';
/** @var string $error */
$_cr_s      = PDCR_Settings::get();
$_cr_action = home_url( '/' . PDCR_Role::SHELL_SLUG . '/login/' );
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only redirect hint; sanitized via esc_url_raw().
if ( isset( $_GET['redirect_to'] ) ) {
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended -- sanitized via esc_url_raw(); read-only redirect hint.
	$_cr_action = add_query_arg( 'redirect_to', rawurlencode( esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) ), $_cr_action );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Sign In &mdash; <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<?php wp_head(); ?>
</head>
<body>
<div class="card">
	<p class="logo"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
	<h1><?php echo esc_html( $_cr_s['login_heading'] ); ?></h1>
	<p class="subtitle"><?php echo esc_html( $_cr_s['login_subtitle'] ); ?></p>

	<?php if ( $error ) :
		$messages = [
			'email'         => 'Please enter your email address.',
			'password'      => 'Please enter your password.',
			'invalid'       => 'Incorrect email or password. Please try again.',
			'invalid_nonce' => 'Security check failed. Please try again.',
		];
		echo '<div class="error-banner">' . esc_html( $messages[ $error ] ?? 'Something went wrong. Please try again.' ) . '</div>';
	endif; ?>

	<form method="post" action="<?php echo esc_url( $_cr_action ); ?>">
		<?php wp_nonce_field( 'pdcr_login', 'pdcr_login_nonce' ); ?>

		<div class="field">
			<label for="cr_email">Email address</label>
			<input
				type="email"
				id="cr_email"
				name="cr_email"
				value="<?php echo esc_attr( wp_unslash( $_POST['cr_email'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- sticky field; nonce verified in PDCR_Preview::render_login(). ?>"
				required
				autocomplete="email"
				class="<?php echo 'email' === $error ? 'error' : ''; ?>"
			>
		</div>

		<div class="field">
			<label for="cr_password">Password</label>
			<input
				type="password"
				id="cr_password"
				name="cr_password"
				required
				autocomplete="current-password"
				class="<?php echo 'password' === $error ? 'error' : ''; ?>"
			>
		</div>

		<button type="submit" class="submit-btn"><?php echo esc_html( $_cr_s['login_button'] ); ?></button>
	</form>
</div>
<?php wp_footer(); ?>
</body>
</html>
