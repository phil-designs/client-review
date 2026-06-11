<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- standalone template page; vars are local to this include.
require_once __DIR__ . '/../includes/class-cr-settings.php';
/** @var object|null $invite */
/** @var string      $error  */
/** @var string      $token  */
$_cr_s  = CR_Settings::get();
$_cr_gf = CR_Settings::google_fonts_url( $_cr_s );
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Review Access &mdash; <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php // phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet -- standalone HTML page; wp_enqueue_style() not applicable. ?>
	<?php if ( $_cr_gf ) : ?>
	<link rel="stylesheet" href="<?php echo esc_url( $_cr_gf ); ?>">
	<?php endif; ?>
	<?php // phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet ?>
	<style>
		<?php echo CR_Settings::css_vars( $_cr_s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS built from sanitized values in CR_Settings::css_vars(). ?>

		*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
		body {
			font-family: var(--cr-font-body, 'Poppins', sans-serif);
			background: #111111;
			color: #e8e8e8;
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 24px;
		}
		.card {
			background: #1a1a1a;
			border: 1px solid #2a2a2a;
			border-top: 3px solid var(--cr-accent, #F7941D);
			padding: 40px;
			width: 100%;
			max-width: 420px;
		}
		.logo {
			font-family: var(--cr-font-heading, 'Oswald', sans-serif);
			font-size: 13px;
			font-weight: 400;
			color: var(--cr-accent, #F7941D);
			margin-bottom: 28px;
			text-transform: uppercase;
			letter-spacing: 0.08em;
		}
		h1 {
			font-family: var(--cr-font-heading, 'Oswald', sans-serif);
			font-size: 26px;
			font-weight: 500;
			letter-spacing: 0.03em;
			text-transform: uppercase;
			margin-bottom: 8px;
			color: #ffffff;
		}
		.subtitle { font-size: 13px; color: #888888; margin-bottom: 28px; line-height: 1.6; }
		.field { margin-bottom: 16px; }
		label {
			display: block;
			font-family: var(--cr-font-heading, 'Oswald', sans-serif);
			font-size: 11px;
			font-weight: 400;
			letter-spacing: 0.07em;
			text-transform: uppercase;
			margin-bottom: 6px;
			color: #aaaaaa;
		}
		input {
			width: 100%;
			padding: 10px 14px;
			background: #111111;
			border: 2px solid #333333;
			border-radius: var(--cr-btn-radius, 30px);
			color: #e8e8e8;
			font-size: 14px;
			font-family: var(--cr-font-body, 'Poppins', sans-serif);
			transition: border-color 0.2s;
		}
		input:focus { outline: none; border-color: var(--cr-accent, #F7941D); }
		input.error { border-color: #cc4444; }
		.hint { font-size: 12px; color: #555555; margin-top: 4px; }
		.error-banner {
			background: rgba(204,68,68,0.12);
			border-left: 3px solid #cc4444;
			padding: 12px 14px;
			font-size: 13px;
			color: #f08080;
			margin-bottom: 20px;
		}
		.expired-banner {
			background: color-mix(in srgb, var(--cr-accent, #F7941D) 10%, transparent);
			border-left: 3px solid var(--cr-accent, #F7941D);
			padding: 16px;
			font-size: 14px;
			color: var(--cr-accent, #F7941D);
			text-align: center;
		}
		.submit-btn {
			width: 100%;
			padding: 13px;
			background: var(--cr-btn-p-bg, #F7941D);
			color: var(--cr-btn-p-color, #ffffff);
			border: 2px solid var(--cr-btn-p-border, #F7941D);
			border-radius: 30px;
			font-family: var(--cr-font-body, 'Poppins', sans-serif);
			font-size: 14px;
			font-weight: var(--cr-btn-font-weight, 600);
			cursor: pointer;
			margin-top: 8px;
			letter-spacing: 0.02em;
			transition: all 0.25s ease;
		}
		.submit-btn:hover {
			background: var(--cr-btn-p-hover-bg, transparent);
			color: var(--cr-btn-p-hover-color, #F7941D);
			border-color: var(--cr-btn-p-hover-border, #F7941D);
		}
		.login-link {
			text-align: center;
			margin-top: 20px;
			font-size: 13px;
			color: #555555;
		}
		.login-link a { color: var(--cr-accent, #F7941D); text-decoration: none; }
		.login-link a:hover { text-decoration: underline; }
	</style>
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
				'email_taken'   => 'That email is already registered. <a href="' . esc_url( wp_login_url( home_url( '/' . CR_Role::SHELL_SLUG . '/' ) ) ) . '">Log in instead</a>.',
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
			<?php wp_nonce_field( 'cr_register_' . $token, 'cr_register_nonce' ); ?>

			<div class="field">
				<label for="cr_name">Your name</label>
				<input
					type="text"
					id="cr_name"
					name="cr_name"
					value="<?php echo esc_attr( wp_unslash( $_POST['cr_name'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- sticky field; nonce verified in CR_Invite::handle_registration_post(). ?>"
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
					value="<?php echo esc_attr( wp_unslash( $_POST['cr_email'] ?? '' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- sticky field; nonce verified in CR_Invite::handle_registration_post(). ?>"
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
			<a href="<?php echo esc_url( wp_login_url( home_url( '/' . CR_Role::SHELL_SLUG . '/' ) ) ); ?>">
				<?php echo esc_html( $_cr_s['form_login_link'] ); ?>
			</a>
		</p>

	<?php endif; ?>
</div>
</body>
</html>
