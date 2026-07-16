<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- standalone template page; vars are local to this include.
require_once __DIR__ . '/../includes/class-cr-settings.php';
/** @var object|null $invite */
/** @var string      $error  */
/** @var string      $token  */
$_cr_s = PDCR_Settings::get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Review Access &mdash; <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<?php wp_head(); ?>
</head>
<body>
<div class="card">
	<p class="logo"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>

	<?php if ( ! $invite ) : ?>

		<div class="expired-banner">
			<strong><?php echo esc_html( $_cr_s['expired_heading'] ); ?></strong><br>
			<?php echo esc_html( $_cr_s['expired_body'] ); ?>
		</div>

	<?php else : ?>

		<h1><?php echo esc_html( $_cr_s['form_heading'] ); ?></h1>
		<p class="subtitle"><?php echo esc_html( $_cr_s['form_subtitle'] ); ?></p>

		<?php if ( $error ) :
			$messages = [
				'name'          => 'Please enter your full name.',
				'email'         => 'Please enter a valid email address.',
				'password'      => 'Password must be at least 8 characters.',
				'email_taken'   => 'That email is already registered. <a href="' . esc_url( wp_login_url( home_url( '/' . PDCR_Role::SHELL_SLUG . '/' ) ) ) . '">Log in instead</a>.',
				'create_failed' => 'Something went wrong creating your account. Please try again.',
				'expired'       => 'This invite link has expired.',
			];
			foreach ( explode( ',', $error ) as $e ) {
				if ( isset( $messages[ $e ] ) ) {
					echo '<div class="error-banner">' . wp_kses( $messages[ $e ], [ 'a' => [ 'href' => [] ] ] ) . '</div>';
					break;
				}
			}
		endif; ?>

		<form method="post" action="<?php echo esc_url( add_query_arg( 'cr_invite', esc_attr( $token ), home_url( '/' ) ) ); ?>">
			<?php wp_nonce_field( 'pdcr_register_' . $token, 'pdcr_register_nonce' ); ?>

			<div class="field">
				<label for="cr_name">Your name</label>
				<input
					type="text"
					id="cr_name"
					name="cr_name"
					value="<?php echo esc_attr( wp_unslash( $_POST['cr_name'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- sticky field; nonce verified in PDCR_Invite::handle_registration_post(). ?>"
					required
					autocomplete="name"
					class="<?php echo esc_attr( str_contains( $error, 'name' ) ? 'error' : '' ); ?>"
				>
			</div>

			<div class="field">
				<label for="cr_email">Email address</label>
				<input
					type="email"
					id="cr_email"
					name="cr_email"
					value="<?php echo esc_attr( wp_unslash( $_POST['cr_email'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- sticky field; nonce verified in PDCR_Invite::handle_registration_post(). ?>"
					required
					autocomplete="email"
					class="<?php echo esc_attr( str_contains( $error, 'email' ) ? 'error' : '' ); ?>"
				>
			</div>

			<div class="field">
				<label for="cr_password">Create a password</label>
				<input
					type="password"
					id="cr_password"
					name="cr_password"
					required
					autocomplete="new-password"
					class="<?php echo esc_attr( str_contains( $error, 'password' ) ? 'error' : '' ); ?>"
				>
				<p class="hint">Minimum 8 characters</p>
			</div>

			<button type="submit" class="submit-btn"><?php echo esc_html( $_cr_s['form_button'] ); ?></button>
		</form>

		<p class="login-link">
			<?php echo esc_html( $_cr_s['form_login_prompt'] ); ?>
			<a href="<?php echo esc_url( wp_login_url( home_url( '/' . PDCR_Role::SHELL_SLUG . '/' ) ) ); ?>">
				<?php echo esc_html( $_cr_s['form_login_link'] ); ?>
			</a>
		</p>

	<?php endif; ?>
</div>
<?php wp_footer(); ?>
</body>
</html>
