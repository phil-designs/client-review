<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CR_Role {

	const ROLE      = 'client_reviewer';
	const CAP       = 'client_reviewer';
	const SHELL_SLUG = 'client-review';

	public static function init(): void {
		add_action( 'admin_init',     [ __CLASS__, 'block_admin_access' ] );
		add_filter( 'login_redirect', [ __CLASS__, 'login_redirect' ], 10, 3 );
		add_filter( 'show_admin_bar', [ __CLASS__, 'hide_admin_bar' ] );
	}

	public static function hide_admin_bar( bool $show ): bool {
		if ( current_user_can( self::CAP ) ) {
			return false;
		}
		return $show;
	}

	public static function register(): void {
		if ( get_role( self::ROLE ) ) {
			return;
		}
		add_role( self::ROLE, 'Client Reviewer', [
			'read'          => true,
			self::CAP       => true,
		] );
	}

	public static function block_admin_access(): void {
		if ( current_user_can( self::CAP ) && ! defined( 'DOING_AJAX' ) ) {
			wp_redirect( home_url( '/' . self::SHELL_SLUG . '/' ) );
			exit;
		}
	}

	public static function login_redirect( string $redirect_to, string $_requested, WP_User|WP_Error $user ): string {
		if ( $user instanceof WP_User && in_array( self::ROLE, $user->roles, true ) ) {
			return home_url( '/' . self::SHELL_SLUG . '/' );
		}
		return $redirect_to;
	}
}
