<?php
/**
 * Plugin Name:       PhilDesigns Client Review
 * Plugin URI:        https://github.com/phil-designs/client-review
 * Description:       Send clients a private preview link to annotate your site before launch. Includes responsive device switcher, visual pin annotations, and an admin review dashboard.
 * Version:           1.1.0
 * Author:            PhilDesigns
 * Author URI:        https://phildesigns.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       client-review
 * Domain Path:       /languages
 * Requires at least: 6.7
 * Tested up to:      7.0
 * Requires PHP:      8.0
 */

defined( 'ABSPATH' ) || exit;

define( 'PDCR_VERSION', '1.1.0' );

require_once __DIR__ . '/includes/class-cr-activator.php';
require_once __DIR__ . '/includes/class-cr-role.php';
require_once __DIR__ . '/includes/class-cr-invite.php';
require_once __DIR__ . '/includes/class-cr-annotation.php';
require_once __DIR__ . '/includes/class-cr-email.php';
require_once __DIR__ . '/includes/class-cr-settings.php';
require_once __DIR__ . '/includes/class-cr-admin.php';
require_once __DIR__ . '/includes/class-cr-preview.php';

register_activation_hook( __FILE__, [ 'PDCR_Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'PDCR_Activator', 'deactivate' ] );

function client_review_init(): void {
	PDCR_Role::init();
	PDCR_Invite::init();
	PDCR_Annotation::init();
	PDCR_Settings::init();
	PDCR_Admin::init();
	PDCR_Preview::init();
}
add_action( 'plugins_loaded', 'client_review_init' );
