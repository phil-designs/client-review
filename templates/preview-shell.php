<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- standalone template page; vars are local to this include.
require_once __DIR__ . '/../includes/class-cr-settings.php';
$_cr_url  = plugin_dir_url( dirname( __FILE__ ) . '/../client-review.php' );
$_cr_ver  = '1.4.0';
$_cr_s    = CR_Settings::get();
$_cr_gf   = CR_Settings::google_fonts_url( $_cr_s );
$_cr_vars = CR_Settings::css_vars( $_cr_s );
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Client Review &mdash; <?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php // phpcs:disable WordPress.WP.EnqueuedResources -- standalone HTML page rendered outside WP template system; wp_enqueue_style/script() not applicable. ?>
	<?php if ( $_cr_gf ) : ?>
	<link rel="stylesheet" href="<?php echo esc_url( $_cr_gf ); ?>">
	<?php endif; ?>
	<link rel="stylesheet" href="<?php echo esc_url( $_cr_url . 'assets/css/preview.css' ); ?>?v=<?php echo esc_attr( $_cr_ver ); ?>">
	<?php // phpcs:enable WordPress.WP.EnqueuedResources ?>
	<style><?php echo $_cr_vars; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS custom properties built from sanitized values in CR_Settings::css_vars(). ?></style>
</head>
<body class="cr-shell">

<!-- ═══════════════════════════════ TOOLBAR ═══════════════════════════════ -->
<header class="cr-toolbar">
	<div class="cr-toolbar__left">
		<span class="cr-brand"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
	</div>

	<div class="cr-toolbar__center">
		<div class="cr-device-switcher">
			<button class="cr-device-btn active" data-device="desktop" data-width="1440" title="Desktop (1440px)">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
				Desktop
			</button>
			<button class="cr-device-btn" data-device="tablet" data-width="768" title="Tablet (768px)">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
				Tablet
			</button>
			<button class="cr-device-btn" data-device="mobile" data-width="390" title="Mobile (390px)">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
				Mobile
			</button>
		</div>
	</div>

	<div class="cr-toolbar__right">
		<button id="cr-annotate-toggle" class="cr-btn cr-btn--secondary" title="Click anywhere on the page to leave a comment">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
			<span>Add Comment</span>
		</button>
		<button id="cr-sidebar-toggle" class="cr-btn cr-btn--secondary">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
			Comments <span id="cr-comment-count" class="cr-badge">0</span>
		</button>
		<button id="cr-finish-review" class="cr-btn cr-btn--primary">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
			Finish Review
		</button>
		<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" class="cr-btn cr-btn--ghost">Logout</a>
	</div>
</header>

<!-- ═══════════════════════════ WORKSPACE ═══════════════════════════════ -->
<div class="cr-workspace">

	<!-- Frame area -->
	<div class="cr-frame-container" id="cr-frame-container">
		<div class="cr-frame-outer">
			<div class="cr-frame-wrapper" id="cr-frame-wrapper">
				<iframe
					id="cr-iframe"
					src="<?php echo esc_url( home_url( '/' ) ); ?>"
					sandbox="allow-same-origin allow-scripts allow-forms allow-popups"
				></iframe>
				<div class="cr-overlay" id="cr-overlay" title="Click to drop a comment pin"></div>
			</div>
		</div>
	</div>

	<!-- Sidebar -->
	<aside class="cr-sidebar" id="cr-sidebar">
		<div class="cr-sidebar__header">
			<div class="cr-sidebar__title">
				<h3>Comments</h3>
				<span id="cr-sidebar-device" class="cr-device-tag">Desktop</span>
			</div>
			<p id="cr-sidebar-page" class="cr-sidebar__page"></p>
			<button id="cr-sidebar-close" class="cr-sidebar__close" aria-label="Close sidebar">&times;</button>
		</div>
		<div class="cr-sidebar__body" id="cr-comments-list">
			<p class="cr-empty-state">No comments yet.<br>Switch on <strong>Add Comment</strong> and click anywhere on the page.</p>
		</div>
	</aside>

</div><!-- .cr-workspace -->

<!-- ═══════════════════════ COMMENT INPUT POPUP ═══════════════════════ -->
<div class="cr-popup" id="cr-popup">
	<textarea id="cr-popup-input" placeholder="Describe what you're noticing… (Ctrl+Enter to save)" rows="4"></textarea>
	<div class="cr-popup__actions">
		<button id="cr-popup-cancel" class="cr-btn cr-btn--ghost cr-btn--sm">Cancel</button>
		<button id="cr-popup-submit" class="cr-btn cr-btn--primary cr-btn--sm">Save Comment</button>
	</div>
</div>

<!-- ═══════════════════════ FINISH REVIEW MODAL ═══════════════════════ -->
<div class="cr-modal-backdrop" id="cr-modal-backdrop">
	<div class="cr-modal" role="dialog" aria-modal="true">
		<h3>Submit your review?</h3>
		<p>An email summary of all your comments will be sent to the team. You can continue adding comments after submitting.</p>
		<div class="cr-modal__actions">
			<button id="cr-modal-cancel" class="cr-btn cr-btn--ghost">Not yet</button>
			<button id="cr-modal-confirm" class="cr-btn cr-btn--primary">Yes, Submit</button>
		</div>
	</div>
</div>

<!-- ═══════════════════════ ANNOTATE MODE HINT ═══════════════════════ -->
<div class="cr-annotate-hint" id="cr-annotate-hint">
	Click anywhere on the page to drop a pin &mdash; or press <kbd>Esc</kbd> to cancel
</div>

<script>
var crPreview = <?php echo wp_json_encode( [
	'restUrl'   => rest_url( 'client-review/v1/' ),
	'restNonce' => wp_create_nonce( 'wp_rest' ),
	'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
	'ajaxNonce' => wp_create_nonce( 'cr_finish_review' ),
	'siteUrl'   => home_url( '/' ),
	'currentUser' => [
		'id'   => get_current_user_id(),
		'name' => wp_get_current_user()->display_name,
	],
	'isAdmin'   => current_user_can( 'manage_options' ),
] ); ?>;
</script>
<?php wp_print_script_tag( [ 'src' => esc_url( $_cr_url . 'assets/js/preview.js' ) . '?v=' . esc_attr( $_cr_ver ) ] ); ?>

</body>
</html>
