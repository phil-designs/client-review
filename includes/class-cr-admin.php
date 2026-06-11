<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/class-cr-invite.php';
require_once __DIR__ . '/class-cr-settings.php';

class CR_Admin {

	public static function init(): void {
		add_action( 'admin_menu',            [ __CLASS__, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
	}

	public static function add_menu(): void {
		add_menu_page(
			'Client Review',
			'Client Review',
			'manage_options',
			'client-review',
			[ __CLASS__, 'render_invites_page' ],
			'dashicons-visibility',
			30
		);
		add_submenu_page( 'client-review', 'Invite Links', 'Invite Links', 'manage_options', 'client-review',          [ __CLASS__, 'render_invites_page' ] );
		add_submenu_page( 'client-review', 'Reviews',      'Reviews',      'manage_options', 'client-review-reviews',  [ __CLASS__, 'render_reviews_page' ] );
		add_submenu_page( 'client-review', 'Settings',     'Settings',     'manage_options', 'cr-settings',           [ __CLASS__, 'render_settings_page' ] );
	}

	public static function enqueue_scripts( string $hook ): void {
		if ( strpos( $hook, 'client-review' ) === false ) return;

		$plugin_url = plugin_dir_url( __DIR__ . '/../client-review.php' );
		wp_enqueue_style(  'cr-admin', $plugin_url . 'assets/css/admin-review.css', [], '1.0.0' );
		wp_enqueue_script( 'cr-admin', $plugin_url . 'assets/js/admin-review.js',  [ 'jquery' ], '1.0.0', true );
		wp_localize_script( 'cr-admin', 'crAdmin', [
			'nonce'    => wp_create_nonce( 'cr_admin_nonce' ),
			'restNonce' => wp_create_nonce( 'wp_rest' ),
			'restUrl'  => rest_url( 'client-review/v1/' ),
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		] );
	}

	// -------------------------------------------------------------------------

	public static function render_invites_page(): void {
		$invites = CR_Invite::get_all();
		include __DIR__ . '/../templates/admin-invites.php';
	}

	public static function render_settings_page(): void {
		$settings = CR_Settings::get();
		include __DIR__ . '/../templates/admin-settings.php';
	}

	public static function render_reviews_page(): void {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// $wpdb->users and $wpdb->prefix are WP core internals, not user input.
		$reviewers = $wpdb->get_results(
			"SELECT u.ID, u.display_name,
			        COUNT(a.id)                                              AS total,
			        SUM(CASE WHEN a.status = 'open' THEN 1 ELSE 0 END)     AS open_count,
			        SUM(CASE WHEN a.status = 'resolved' THEN 1 ELSE 0 END) AS done_count,
			        MAX(a.created_at)                                        AS last_activity
			 FROM {$wpdb->users} u
			 INNER JOIN {$wpdb->prefix}cr_annotations a ON u.ID = a.user_id
			 GROUP BY u.ID
			 ORDER BY last_activity DESC"
		) ?: [];
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- read-only filter; (int) cast ensures safe use.
		$selected_reviewer = isset( $_GET['reviewer'] ) ? (int) wp_unslash( $_GET['reviewer'] ) : 0;
		$pages             = [];
		$reviewer_name     = '';

		if ( $selected_reviewer ) {
			$rev = get_user_by( 'id', $selected_reviewer );
			if ( $rev ) $reviewer_name = $rev->display_name;

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$annotations = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}cr_annotations WHERE user_id = %d ORDER BY page_url, device, created_at",
				$selected_reviewer
			) ) ?: [];
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			foreach ( $annotations as $ann ) {
				$pages[ $ann->page_url ][ $ann->device ][] = $ann;
			}
		}

		include __DIR__ . '/../templates/admin-reviews.php';
	}
}
