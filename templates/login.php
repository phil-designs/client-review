<?php
if ( ! defined( 'ABSPATH' ) ) exit;
require_once __DIR__ . '/../includes/class-cr-settings.php';
/** @var string $error */
$_cr_s  = CR_Settings::get();
$_cr_gf = CR_Settings::google_fonts_url( $_cr_s );
$_cr_action = home_url( '/' . CR_Role::SHELL_SLUG . '/login/' );
if ( isset( $_GET['redirect_to'] ) ) {
	$_cr_action = add_query_arg( 'redirect_to', rawurlencode( esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) ), $_cr_action );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Sign In &mdash; <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php if ( $_cr_gf ) : ?>
	<link rel="stylesheet" href="<?php echo esc_url( $_cr_gf ); ?>">
	<?php endif; ?>
	<style>
		<?php echo CR_Settings::css_vars( $_cr_s ); ?>

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
		.error-banner {
			background: rgba(204,68,68,0.12);
			border-left: 3px solid #cc4444;
			padding: 12px 14px;
			font-size: 13px;
			color: #f08080;
			margin-bottom: 20px;
		}
		.submit-btn {
			width: 100%;
			padding: 13px;
			background: var(--cr-btn-p-bg, #F7941D);
			color: var(--cr-btn-p-color, #ffffff);
			border: 2px solid var(--cr-btn-p-border, #F7941D);
			border-radius: var(--cr-btn-radius, 30px);
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
	</style>
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
		<?php wp_nonce_field( 'cr_login', 'cr_login_nonce' ); ?>

		<div class="field">
			<label for="cr_email">Email address</label>
			<input
				type="email"
				id="cr_email"
				name="cr_email"
				value="<?php echo esc_attr( wp_unslash( $_POST['cr_email'] ?? '' ) ); ?>"
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
</body>
</html>
