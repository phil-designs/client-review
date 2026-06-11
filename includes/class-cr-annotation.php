<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/class-cr-role.php';

class CR_Annotation {

	public static function init(): void {
		add_action( 'rest_api_init', [ __CLASS__, 'register_routes' ] );
	}

	public static function register_routes(): void {
		register_rest_route( 'client-review/v1', '/annotations', [
			[
				'methods'             => 'GET',
				'callback'            => [ __CLASS__, 'get_annotations' ],
				'permission_callback' => [ __CLASS__, 'can_access' ],
				'args'                => [
					'page_url' => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
					'device'   => [ 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ],
				],
			],
			[
				'methods'             => 'POST',
				'callback'            => [ __CLASS__, 'create_annotation' ],
				'permission_callback' => [ __CLASS__, 'can_access' ],
			],
		] );

		register_rest_route( 'client-review/v1', '/annotations/(?P<id>\d+)', [
			[
				'methods'             => 'PUT',
				'callback'            => [ __CLASS__, 'update_annotation' ],
				'permission_callback' => [ __CLASS__, 'can_access' ],
			],
			[
				'methods'             => 'DELETE',
				'callback'            => [ __CLASS__, 'delete_annotation' ],
				'permission_callback' => [ __CLASS__, 'can_access' ],
			],
			[
				'methods'             => 'PATCH',
				'callback'            => [ __CLASS__, 'patch_status' ],
				'permission_callback' => '__return_true',
			],
		] );
	}

	public static function can_access(): bool {
		return is_user_logged_in() && (
			current_user_can( 'manage_options' ) ||
			current_user_can( CR_Role::CAP )
		);
	}

	// -------------------------------------------------------------------------

	public static function get_annotations( WP_REST_Request $request ): WP_REST_Response {
		global $wpdb;

		$page_url = $request->get_param( 'page_url' );
		$device   = $request->get_param( 'device' );
		$user_id  = get_current_user_id();
		$is_admin = current_user_can( 'manage_options' );

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT a.*, u.display_name AS author_name
			 FROM {$wpdb->prefix}cr_annotations a
			 LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
			 WHERE a.page_url = %s AND a.device = %s
			 ORDER BY a.created_at ASC",
			$page_url,
			$device
		) ) ?: [];
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		foreach ( $rows as $row ) {
			$row->can_edit      = $is_admin || (int) $row->user_id === $user_id;
			$row->author_is_admin = user_can( (int) $row->user_id, 'manage_options' );
		}

		return rest_ensure_response( $rows );
	}

	public static function create_annotation( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		global $wpdb;
		$data = $request->get_json_params();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$result = $wpdb->insert( "{$wpdb->prefix}cr_annotations", [
			'user_id'   => get_current_user_id(),
			'page_url'  => sanitize_text_field( $data['page_url'] ?? '' ),
			'device'    => sanitize_text_field( $data['device']   ?? 'desktop' ),
			'x_percent' => (float) ( $data['x_percent'] ?? 0 ),
			'y_percent' => (float) ( $data['y_percent'] ?? 0 ),
			'comment'   => sanitize_textarea_field( $data['comment'] ?? '' ),
			'status'    => 'open',
		] );

		if ( ! $result ) {
			return new WP_Error( 'db_error', 'Failed to save annotation.', [ 'status' => 500 ] );
		}

		$id  = $wpdb->insert_id;
		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT a.*, u.display_name AS author_name
			 FROM {$wpdb->prefix}cr_annotations a
			 LEFT JOIN {$wpdb->users} u ON a.user_id = u.ID
			 WHERE a.id = %d",
			$id
		) );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row->can_edit       = true;
		$row->author_is_admin = current_user_can( 'manage_options' );

		return rest_ensure_response( $row );
	}

	public static function update_annotation( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		global $wpdb;
		$id      = (int) $request->get_param( 'id' );
		$user_id = get_current_user_id();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cr_annotations WHERE id = %d", $id ) );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $existing ) return new WP_Error( 'not_found', 'Not found.', [ 'status' => 404 ] );

		if ( (int) $existing->user_id !== $user_id && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'forbidden', 'You can only edit your own comments.', [ 'status' => 403 ] );
		}

		$data = $request->get_json_params();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update( "{$wpdb->prefix}cr_annotations", [
			'comment' => sanitize_textarea_field( $data['comment'] ?? $existing->comment ),
		], [ 'id' => $id ] );

		return rest_ensure_response( [ 'success' => true ] );
	}

	public static function delete_annotation( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		global $wpdb;
		$id      = (int) $request->get_param( 'id' );
		$user_id = get_current_user_id();

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}cr_annotations WHERE id = %d", $id ) );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		if ( ! $existing ) return new WP_Error( 'not_found', 'Not found.', [ 'status' => 404 ] );

		if ( (int) $existing->user_id !== $user_id && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'forbidden', 'You can only delete your own comments.', [ 'status' => 403 ] );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->delete( "{$wpdb->prefix}cr_annotations", [ 'id' => $id ] );
		return rest_ensure_response( [ 'success' => true ] );
	}

	public static function patch_status( WP_REST_Request $request ): WP_REST_Response|WP_Error {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'forbidden', 'Admins only.', [ 'status' => 403 ] );
		}

		global $wpdb;
		$id   = (int) $request->get_param( 'id' );
		$data = $request->get_json_params();

		$allowed = [ 'open', 'resolved', 'needs_clarification' ];
		$status  = sanitize_text_field( $data['status'] ?? '' );
		if ( ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'invalid_status', 'Invalid status.', [ 'status' => 400 ] );
		}

		$update = [ 'status' => $status ];
		if ( isset( $data['admin_note'] ) ) {
			$update['admin_note'] = sanitize_textarea_field( $data['admin_note'] );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update( "{$wpdb->prefix}cr_annotations", $update, [ 'id' => $id ] );
		return rest_ensure_response( [ 'success' => true ] );
	}
}
