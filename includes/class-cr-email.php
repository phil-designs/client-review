<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class CR_Email {

	public static function send_review_summary( int $user_id ): bool {
		global $wpdb;

		$user = get_user_by( 'id', $user_id );
		if ( ! $user ) return false;

		$all_annotations = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$wpdb->prefix}cr_annotations
			 WHERE user_id = %d
			 ORDER BY page_url, device, created_at",
			$user_id
		) );

		if ( empty( $all_annotations ) ) return false;

		$pages = [];
		foreach ( $all_annotations as $ann ) {
			$pages[ $ann->page_url ][ $ann->device ][] = $ann;
		}

		$admin_email = get_option( 'admin_email' );
		$site_name   = get_bloginfo( 'name' );
		$subject     = sprintf( '[%s] Client Review from %s — %d comments', $site_name, $user->display_name, count( $all_annotations ) );

		ob_start();
		include __DIR__ . '/../templates/email-summary.php';
		$body = ob_get_clean();

		return wp_mail( $admin_email, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );
	}
}
