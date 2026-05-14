<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/class-cr-role.php';
require_once __DIR__ . '/class-cr-email.php';

class CR_Preview {

	public static function init(): void {
		add_filter( 'query_vars',        [ __CLASS__, 'add_query_vars' ] );
		add_action( 'init',              [ __CLASS__, 'add_rewrite_rule' ] );
		add_action( 'template_redirect', [ __CLASS__, 'render_shell' ] );
		add_action( 'template_redirect', [ __CLASS__, 'render_login' ] );
		add_filter( 'login_url',         [ __CLASS__, 'custom_login_url' ], 10, 2 );
		add_action( 'wp_ajax_cr_finish_review', [ __CLASS__, 'ajax_finish_review' ] );
	}

	public static function add_query_vars( array $vars ): array {
		$vars[] = 'cr_preview_shell';
		$vars[] = 'cr_login_page';
		return $vars;
	}

	public static function add_rewrite_rule(): void {
		add_rewrite_rule( '^' . CR_Role::SHELL_SLUG . '/?$',          'index.php?cr_preview_shell=1', 'top' );
		add_rewrite_rule( '^' . CR_Role::SHELL_SLUG . '/login/?$',    'index.php?cr_login_page=1',    'top' );
	}

	public static function render_shell(): void {
		if ( ! get_query_var( 'cr_preview_shell' ) ) return;

		if ( ! is_user_logged_in() ) {
			wp_redirect( wp_login_url( home_url( '/' . CR_Role::SHELL_SLUG . '/' ) ) );
			exit;
		}

		if ( ! current_user_can( CR_Role::CAP ) && ! current_user_can( 'manage_options' ) ) {
			wp_redirect( home_url( '/' ) );
			exit;
		}

		include __DIR__ . '/../templates/preview-shell.php';
		exit;
	}

	public static function render_login(): void {
		if ( ! get_query_var( 'cr_login_page' ) ) return;

		if ( is_user_logged_in() ) {
			$dest = ( current_user_can( CR_Role::CAP ) || current_user_can( 'manage_options' ) )
				? home_url( '/' . CR_Role::SHELL_SLUG . '/' )
				: home_url( '/' );
			wp_redirect( $dest );
			exit;
		}

		$error = '';

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] ) {
			$nonce = isset( $_POST['cr_login_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['cr_login_nonce'] ) ) : '';
			if ( ! wp_verify_nonce( $nonce, 'cr_login' ) ) {
				$error = 'invalid_nonce';
			} else {
				$email    = sanitize_email( wp_unslash( $_POST['cr_email']    ?? '' ) );
				$password = wp_unslash( $_POST['cr_password'] ?? '' );

				if ( empty( $email ) ) {
					$error = 'email';
				} elseif ( empty( $password ) ) {
					$error = 'password';
				} else {
					$user = wp_signon( [
						'user_login'    => $email,
						'user_password' => $password,
						'remember'      => true,
					], is_ssl() );

					if ( is_wp_error( $user ) ) {
						$error = 'invalid';
					} else {
						$redirect = home_url( '/' . CR_Role::SHELL_SLUG . '/' );
						if ( isset( $_GET['redirect_to'] ) ) {
							$to = esc_url_raw( wp_unslash( $_GET['redirect_to'] ) );
							if ( false !== strpos( $to, '/' . CR_Role::SHELL_SLUG . '/' ) ) {
								$redirect = $to;
							}
						}
						wp_redirect( $redirect );
						exit;
					}
				}
			}
		}

		include __DIR__ . '/../templates/login.php';
		exit;
	}

	public static function custom_login_url( string $login_url, string $redirect ): string {
		if ( $redirect && false !== strpos( urldecode( $redirect ), '/' . CR_Role::SHELL_SLUG . '/' ) ) {
			$custom = home_url( '/' . CR_Role::SHELL_SLUG . '/login/' );
			return add_query_arg( 'redirect_to', urlencode( $redirect ), $custom );
		}
		return $login_url;
	}

	public static function ajax_finish_review(): void {
		check_ajax_referer( 'cr_finish_review', 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'Not logged in.' ] );
		}

		$sent = CR_Email::send_review_summary( get_current_user_id() );

		if ( $sent ) {
			wp_send_json_success( [ 'message' => 'Review submitted — the team has been notified.' ] );
		} else {
			wp_send_json_error( [ 'message' => 'No comments found to submit, or email delivery failed.' ] );
		}
	}
}
