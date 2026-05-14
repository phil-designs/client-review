<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/class-cr-role.php';

class CR_Activator {

	public static function activate(): void {
		self::create_tables();
		CR_Role::register();
		add_rewrite_rule( '^' . CR_Role::SHELL_SLUG . '/?$', 'index.php?cr_preview_shell=1', 'top' );
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}

	public static function create_tables(): void {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		$invites = "CREATE TABLE {$wpdb->prefix}cr_invites (
			id         bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			token      varchar(64)         NOT NULL,
			label      varchar(255)        NOT NULL DEFAULT '',
			created_by bigint(20) UNSIGNED NOT NULL,
			created_at datetime            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			expires_at datetime            DEFAULT NULL,
			user_id    bigint(20) UNSIGNED DEFAULT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY token (token)
		) $charset;";

		$annotations = "CREATE TABLE {$wpdb->prefix}cr_annotations (
			id         bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id    bigint(20) UNSIGNED NOT NULL,
			page_url   varchar(500)        NOT NULL,
			device     varchar(20)         NOT NULL DEFAULT 'desktop',
			x_percent  decimal(8,4)        NOT NULL DEFAULT 0,
			y_percent  decimal(8,4)        NOT NULL DEFAULT 0,
			comment    text                NOT NULL,
			status     varchar(30)         NOT NULL DEFAULT 'open',
			admin_note text                DEFAULT NULL,
			created_at datetime            NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY user_id  (user_id),
			KEY page_url (page_url(191))
		) $charset;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $invites );
		dbDelta( $annotations );
	}
}
