<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/class-cr-invite.php';
require_once __DIR__ . '/class-cr-settings.php';

class PDCR_Admin {

	public static function init(): void {
		add_action( 'admin_menu',            [ __CLASS__, 'add_menu' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
		add_action( 'admin_post_pdcr_export_csv', [ __CLASS__, 'export_csv' ] );
	}

	/**
	 * URL that downloads the comments spreadsheet — all reviewers, or just one.
	 */
	public static function export_url( int $reviewer = 0 ): string {
		$args = [ 'action' => 'pdcr_export_csv' ];
		if ( $reviewer ) $args['reviewer'] = $reviewer;
		return wp_nonce_url( add_query_arg( $args, admin_url( 'admin-post.php' ) ), 'pdcr_export_csv' );
	}

	/**
	 * Streams every comment as a CSV spreadsheet (opens in Excel, Numbers,
	 * and Google Sheets). Pin numbers match the shell: counted per page and
	 * viewport across all reviewers, oldest first.
	 */
	public static function export_csv(): void {
		check_admin_referer( 'pdcr_export_csv' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

		global $wpdb;
		$reviewer = isset( $_GET['reviewer'] ) ? absint( wp_unslash( $_GET['reviewer'] ) ) : 0;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		// $wpdb->users and $wpdb->prefix are WP core internals, not user input.
		$rows = $wpdb->get_results(
			"SELECT a.*, u.display_name AS author_name, u.user_email AS author_email
			 FROM {$wpdb->prefix}cr_annotations a
			 LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
			 ORDER BY a.page_url, FIELD(a.device, 'desktop', 'tablet', 'mobile'), a.created_at, a.id"
		) ?: [];
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$status_labels = [
			'open'                => 'Open',
			'resolved'            => 'Done',
			'needs_clarification' => 'Needs Clarification',
		];
		$device_labels = [
			'desktop' => 'Desktop (1440px)',
			'tablet'  => 'Tablet (768px)',
			'mobile'  => 'Mobile (390px)',
		];

		$lines   = [];
		$lines[] = self::csv_line( [ 'Page', 'Page URL', 'Viewport', 'Pin #', 'Status', 'Comment', 'Commenter', 'Commenter Email', 'Note to Client', 'Created', 'Last Updated' ] );

		$pin_counts = [];
		foreach ( $rows as $row ) {
			$group                = $row->page_url . '|' . $row->device;
			$pin_counts[ $group ] = ( $pin_counts[ $group ] ?? 0 ) + 1;

			if ( $reviewer && (int) $row->user_id !== $reviewer ) continue;

			$lines[] = self::csv_line( [
				$row->page_url,
				home_url( $row->page_url ),
				$device_labels[ $row->device ] ?? $row->device,
				$pin_counts[ $group ],
				$status_labels[ $row->status ] ?? $row->status,
				$row->comment,
				$row->author_name ?? 'Deleted user',
				$row->author_email ?? '',
				$row->admin_note ?? '',
				wp_date( 'Y-m-d g:i a', strtotime( $row->created_at ) ),
				wp_date( 'Y-m-d g:i a', strtotime( $row->updated_at ) ),
			] );
		}

		$name = 'client-review-comments';
		if ( $reviewer ) {
			$user = get_user_by( 'id', $reviewer );
			if ( $user ) $name .= '-' . sanitize_title( $user->display_name );
		}
		$name .= '-' . wp_date( 'Y-m-d' ) . '.csv';

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $name . '"' );

		// UTF-8 BOM so Excel reads accented characters and emoji correctly.
		echo "\xEF\xBB\xBF" . implode( "\r\n", $lines ) . "\r\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV download, not HTML; every cell is quoted by csv_line().
		exit;
	}

	/**
	 * One RFC 4180 CSV row. Cells starting with a formula trigger character
	 * are prefixed with an apostrophe so spreadsheet apps show them as text
	 * instead of running them (CSV/formula injection).
	 */
	private static function csv_line( array $cells ): string {
		return implode( ',', array_map( static function ( $cell ): string {
			$cell = (string) $cell;
			if ( '' !== $cell && in_array( $cell[0], [ '=', '+', '-', '@', "\t", "\r" ], true ) ) {
				$cell = "'" . $cell;
			}
			return '"' . str_replace( '"', '""', $cell ) . '"';
		}, $cells ) );
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
		add_submenu_page( 'client-review', 'Settings',     'Settings',     'manage_options', 'pdcr-settings',           [ __CLASS__, 'render_settings_page' ] );
	}

	public static function enqueue_scripts( string $hook ): void {
		if ( strpos( $hook, 'client-review' ) === false ) return;

		$plugin_url = PDCR_Settings::plugin_url();
		wp_enqueue_style(  'pdcr-admin', $plugin_url . 'assets/css/admin-review.css', [], PDCR_VERSION );
		wp_enqueue_script( 'pdcr-admin', $plugin_url . 'assets/js/admin-review.js',  [ 'jquery' ], PDCR_VERSION, true );
		wp_localize_script( 'pdcr-admin', 'pdcrAdmin', [
			'nonce'    => wp_create_nonce( 'pdcr_admin_nonce' ),
			'restNonce' => wp_create_nonce( 'wp_rest' ),
			'restUrl'  => rest_url( 'client-review/v1/' ),
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		] );

		if ( 'client-review_page_pdcr-settings' === $hook ) {
			wp_enqueue_style( 'pdcr-admin-settings', $plugin_url . 'assets/css/admin-settings.css', [], PDCR_VERSION );
			wp_enqueue_style( 'pdcr-fonts-local',    $plugin_url . 'assets/css/fonts-local.css',    [], PDCR_VERSION );
			wp_enqueue_script( 'pdcr-admin-settings', $plugin_url . 'assets/js/admin-settings.js', [], PDCR_VERSION, true );
		}
	}

	// -------------------------------------------------------------------------

	public static function render_invites_page(): void {
		$invites = PDCR_Invite::get_all();
		include __DIR__ . '/../templates/admin-invites.php';
	}

	public static function render_settings_page(): void {
		$settings = PDCR_Settings::get();
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
