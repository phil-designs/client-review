<?php
defined( 'ABSPATH' ) || exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template included by plugin class; vars are local to this include.
/** @var WP_User $user             */
/** @var array   $all_annotations  */
/** @var array   $pages            */
require_once __DIR__ . '/../includes/class-cr-settings.php';
$admin_review_url = admin_url( 'admin.php?page=client-review-reviews&reviewer=' . $user->ID );
$site_name        = get_bloginfo( 'name' );
$site_url         = home_url( '/' );
$_cr_s            = CR_Settings::get();
$_cr_accent       = $_cr_s['accent'];
$_cr_radius       = absint( $_cr_s['btn_border_radius'] ) . 'px';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Client Review Summary</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#1e293b;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 0;">
<tr><td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">

	<!-- Header -->
	<tr>
		<td style="background:#0f172a;padding:28px 36px;">
			<p style="margin:0;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:0.08em;"><?php echo esc_html( $site_name ); ?></p>
			<h1 style="margin:8px 0 0;font-size:22px;color:#ffffff;font-weight:700;">Client Review Summary</h1>
		</td>
	</tr>

	<!-- Intro -->
	<tr>
		<td style="padding:28px 36px 20px;">
			<p style="margin:0;font-size:15px;line-height:1.6;">
				<strong><?php echo esc_html( $user->display_name ); ?></strong> has submitted a review with
				<strong><?php echo (int) count( $all_annotations ); ?> comment<?php echo count( $all_annotations ) !== 1 ? 's' : ''; ?></strong>
				across <?php echo (int) count( $pages ); ?> page<?php echo count( $pages ) !== 1 ? 's' : ''; ?>.
			</p>
			<p style="margin:12px 0 0;">
				<a href="<?php echo esc_url( $admin_review_url ); ?>" style="display:inline-block;background:<?php echo esc_attr( $_cr_accent ); ?>;color:#ffffff;padding:10px 20px;border-radius:<?php echo esc_attr( $_cr_radius ); ?>;text-decoration:none;font-size:14px;font-weight:600;">View in Dashboard &rarr;</a>
			</p>
		</td>
	</tr>

	<!-- Divider -->
	<tr><td style="padding:0 36px;"><hr style="border:none;border-top:1px solid #e2e8f0;margin:0;"></td></tr>

	<!-- Comments grouped by page -->
	<?php $ann_index = 1; foreach ( $pages as $page_url => $devices ) : ?>
	<tr>
		<td style="padding:24px 36px 8px;">
			<p style="margin:0;font-size:11px;color:#94a3b8;text-transform:uppercase;letter-spacing:0.06em;">Page</p>
			<h2 style="margin:4px 0 0;font-size:16px;font-weight:700;">
				<a href="<?php echo esc_url( home_url( $page_url ) ); ?>" style="color:#0f172a;text-decoration:none;"><?php echo esc_html( $page_url ); ?></a>
			</h2>
		</td>
	</tr>

	<?php foreach ( $devices as $device => $annotations ) : ?>
	<tr>
		<td style="padding:4px 36px 16px;">
			<p style="margin:0 0 10px;font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;">
				<?php echo esc_html( ucfirst( $device ) ); ?>
			</p>
			<?php foreach ( $annotations as $ann ) :
				$status_color = [ 'open' => $_cr_accent, 'resolved' => '#16a34a', 'needs_clarification' => '#d97706' ][ $ann->status ] ?? '#64748b';
				$status_label = [ 'open' => 'Open', 'resolved' => 'Done', 'needs_clarification' => 'Needs Clarification' ][ $ann->status ] ?? $ann->status;
			?>
			<table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:10px;overflow:hidden;">
				<tr>
					<td style="padding:12px 16px;">
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td style="font-size:13px;color:#64748b;">#<?php echo (int) $ann_index++; ?></td>
								<td align="right">
									<span style="font-size:11px;font-weight:600;color:<?php echo esc_attr( $status_color ); ?>;background:<?php echo esc_attr( $status_color ); ?>1a;padding:2px 8px;border-radius:10px;">
										<?php echo esc_html( $status_label ); ?>
									</span>
								</td>
							</tr>
						</table>
						<p style="margin:8px 0 0;font-size:14px;line-height:1.6;color:#1e293b;"><?php echo nl2br( esc_html( $ann->comment ) ); ?></p>
					</td>
				</tr>
			</table>
			<?php endforeach; ?>
		</td>
	</tr>
	<?php endforeach; ?>

	<!-- Divider between pages -->
	<tr><td style="padding:0 36px;"><hr style="border:none;border-top:1px solid #e2e8f0;margin:0;"></td></tr>

	<?php endforeach; ?>

	<!-- Footer -->
	<tr>
		<td style="padding:24px 36px;background:#f8fafc;">
			<p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.6;">
				This summary was sent automatically by the Client Review plugin on <strong><?php echo esc_html( $site_name ); ?></strong>.<br>
				<a href="<?php echo esc_url( $admin_review_url ); ?>" style="color:#64748b;">Manage this review in your dashboard</a>
			</p>
		</td>
	</tr>

</table>

</td></tr>
</table>

</body>
</html>
