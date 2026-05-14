<?php defined( 'ABSPATH' ) || exit; ?>
<div class="wrap cr-admin-wrap">
	<h1 class="wp-heading-inline">Client Review &mdash; Invite Links</h1>

	<div class="cr-admin-card">
		<h2>Generate a new invite</h2>
		<p class="description">Each link is single-use. Once the client registers, the link is marked as used.</p>
		<div class="cr-generate-row">
			<input
				type="text"
				id="cr-invite-label"
				placeholder="Client name or project label (optional)"
				class="regular-text"
			>
			<button id="cr-generate-btn" class="button button-primary">Generate Link</button>
		</div>
		<div id="cr-generated-result" style="display:none" class="cr-result-box">
			<p class="cr-result-label">Share this link with your client:</p>
			<div class="cr-result-url-row">
				<input type="text" id="cr-result-url" readonly class="cr-url-input">
				<button id="cr-copy-btn" class="button">Copy</button>
			</div>
			<p class="cr-result-note">The link expires in 30 days and can only be used once.</p>
		</div>
	</div>

	<div class="cr-admin-card">
		<h2>All invite links</h2>
		<?php if ( empty( $invites ) ) : ?>
			<p class="description">No invite links yet. Generate one above.</p>
		<?php else : ?>
			<table class="widefat striped cr-table">
				<thead>
					<tr>
						<th>Label</th>
						<th>Created</th>
						<th>Expires</th>
						<th>Status</th>
						<th>Reviewer</th>
						<th>Link</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $invites as $inv ) :
						$used    = ! empty( $inv->user_id );
						$expired = ! $used && $inv->expires_at && strtotime( $inv->expires_at ) < time();
						$status  = $used ? 'used' : ( $expired ? 'expired' : 'active' );
						$labels  = [ 'used' => 'Used', 'expired' => 'Expired', 'active' => 'Active' ];
					?>
					<tr>
						<td><?php echo esc_html( $inv->label ?: '—' ); ?></td>
						<td><?php echo esc_html( wp_date( 'M j, Y', strtotime( $inv->created_at ) ) ); ?></td>
						<td><?php echo $inv->expires_at ? esc_html( wp_date( 'M j, Y', strtotime( $inv->expires_at ) ) ) : '—'; ?></td>
						<td><span class="cr-status-badge cr-status-<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $labels[ $status ] ); ?></span></td>
						<td><?php echo $used ? esc_html( $inv->reviewer_name ?: 'Unknown' ) : '—'; ?></td>
						<td>
							<?php if ( $status === 'active' ) : ?>
								<code class="cr-token-url"><?php echo esc_html( CR_Invite::get_invite_url( $inv->token ) ); ?></code>
							<?php else : ?>
								<span class="cr-muted">—</span>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $status !== 'used' ) : ?>
								<button class="button cr-delete-invite-btn" data-token="<?php echo esc_attr( $inv->token ); ?>">Delete</button>
							<?php endif; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
