<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/class-cr-role.php';

class CR_Invite {

	public static function init(): void {
		add_action( 'init',             [ __CLASS__, 'handle_registration_post' ] );
		add_action( 'template_redirect', [ __CLASS__, 'maybe_show_registration' ] );
		add_action( 'wp_ajax_cr_generate_invite', [ __CLASS__, 'ajax_generate' ] );
		add_action( 'wp_ajax_cr_delete_invite',   [ __CLASS__, 'ajax_delete' ] );
	}

	// -------------------------------------------------------------------------
	// Public helpers

	public static function generate( string $label = '', int $expires_days = 30 ): string {
		global $wpdb;
		$token = bin2hex( random_bytes( 32 ) );
		$wpdb->insert( "{$wpdb->prefix}cr_invites", [
			'token'      => $token,
			'label'      => sanitize_text_field( $label ),
			'created_by' => get_current_user_id(),
			'expires_at' => gmdate( 'Y-m-d H:i:s', time() + $expires_days * DAY_IN_SECONDS ),
		] );
		return $token;
	}

	public static function get_invite_url( string $token ): string {
		return add_query_arg( 'cr_invite', $token, home_url( '/' ) );
	}

	public static function get_all(): array {
		global $wpdb;
		return $wpdb->get_results(
			"SELECT i.*, u.display_name AS reviewer_name
			 FROM {$wpdb->prefix}cr_invites i
			 LEFT JOIN {$wpdb->users} u ON i.user_id = u.ID
			 ORDER BY i.created_at DESC"
		) ?: [];
	}

	// -------------------------------------------------------------------------
	// Request handlers

	public static function handle_registration_post(): void {
		if ( empty( $_GET['cr_invite'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] || empty( $_POST['cr_register_nonce'] ) ) {
			return;
		}

		$token  = sanitize_text_field( wp_unslash( $_GET['cr_invite'] ) );
		$invite = self::validate_token( $token );

		if ( ! $invite ) {
			wp_redirect( add_query_arg( [ 'cr_invite' => $token, 'cr_error' => 'expired' ], home_url( '/' ) ) );
			exit;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['cr_register_nonce'] ) ), 'cr_register_' . $token ) ) {
			wp_die( 'Security check failed.' );
		}

		$name     = sanitize_text_field( wp_unslash( $_POST['cr_name']     ?? '' ) );
		$email    = sanitize_email( wp_unslash( $_POST['cr_email']    ?? '' ) );
		$password = wp_unslash( $_POST['cr_password'] ?? '' );

		$errors = [];
		if ( strlen( $name ) < 2 )    $errors[] = 'name';
		if ( ! is_email( $email ) )   $errors[] = 'email';
		if ( strlen( $password ) < 8 ) $errors[] = 'password';

		if ( $errors ) {
			wp_redirect( add_query_arg( [ 'cr_invite' => $token, 'cr_error' => implode( ',', $errors ) ], home_url( '/' ) ) );
			exit;
		}

		if ( email_exists( $email ) ) {
			wp_redirect( add_query_arg( [ 'cr_invite' => $token, 'cr_error' => 'email_taken' ], home_url( '/' ) ) );
			exit;
		}

		$user_id = wp_create_user( $email, $password, $email );
		if ( is_wp_error( $user_id ) ) {
			wp_redirect( add_query_arg( [ 'cr_invite' => $token, 'cr_error' => 'create_failed' ], home_url( '/' ) ) );
			exit;
		}

		wp_update_user( [ 'ID' => $user_id, 'display_name' => $name, 'role' => CR_Role::ROLE ] );

		global $wpdb;
		$wpdb->update( "{$wpdb->prefix}cr_invites", [ 'user_id' => $user_id ], [ 'token' => $token ] );

		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id );
		wp_redirect( home_url( '/' . CR_Role::SHELL_SLUG . '/' ) );
		exit;
	}

	public static function maybe_show_registration(): void {
		if ( empty( $_GET['cr_invite'] ) ) {
			return;
		}

		// Already logged in — send them straight to the shell.
		if ( is_user_logged_in() ) {
			wp_redirect( home_url( '/' . CR_Role::SHELL_SLUG . '/' ) );
			exit;
		}

		$token  = sanitize_text_field( wp_unslash( $_GET['cr_invite'] ) );
		$invite = self::validate_token( $token );
		$error  = sanitize_text_field( wp_unslash( $_GET['cr_error'] ?? '' ) );

		include __DIR__ . '/../templates/invite-register.php';
		exit;
	}

	// -------------------------------------------------------------------------
	// AJAX

	public static function ajax_generate(): void {
		check_ajax_referer( 'cr_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		$label = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) );
		$token = self::generate( $label );
		wp_send_json_success( [ 'url' => self::get_invite_url( $token ), 'token' => $token ] );
	}

	public static function ajax_delete(): void {
		check_ajax_referer( 'cr_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

		global $wpdb;
		$token = sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );
		$wpdb->delete( "{$wpdb->prefix}cr_invites", [ 'token' => $token ] );
		wp_send_json_success();
	}

	// -------------------------------------------------------------------------
	// Internal

	private static function validate_token( string $token ): ?object {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}cr_invites
			 WHERE token = %s
			   AND ( expires_at IS NULL OR expires_at > NOW() )
			   AND user_id IS NULL",
			$token
		) ) ?: null;
	}
}
