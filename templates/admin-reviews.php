<?php defined( 'ABSPATH' ) || exit;
$status_labels = [
	'open'                => 'Open',
	'resolved'            => 'Done',
	'needs_clarification' => 'Needs Clarification',
];
$device_labels = [ 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile' ];
?>
<div class="wrap cr-admin-wrap">
	<h1>Client Review &mdash; Reviews</h1>

	<div class="cr-reviews-layout">

		<!-- Reviewer list -->
		<div class="cr-reviewer-list cr-admin-card">
			<h2>Reviewers</h2>
			<?php if ( empty( $reviewers ) ) : ?>
				<p class="description">No reviews yet. Send an invite link to your client to get started.</p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=client-review' ) ); ?>" class="button button-primary" style="margin-top:12px">Go to Invite Links</a>
			<?php else : ?>
				<ul class="cr-reviewer-items">
					<?php foreach ( $reviewers as $rev ) : ?>
						<li class="cr-reviewer-item <?php echo (int) $rev->ID === $selected_reviewer ? 'active' : ''; ?>">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=client-review-reviews&reviewer=' . $rev->ID ) ); ?>">
								<strong><?php echo esc_html( $rev->display_name ); ?></strong>
								<span class="cr-reviewer-meta">
									<?php echo (int) $rev->open_count; ?> open &middot; <?php echo (int) $rev->total; ?> total
								</span>
								<time class="cr-reviewer-time"><?php echo esc_html( wp_date( 'M j', strtotime( $rev->last_activity ) ) ); ?></time>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>

		<!-- Annotation view -->
		<div class="cr-annotation-view">
			<?php if ( ! $selected_reviewer ) : ?>
				<div class="cr-admin-card cr-placeholder">
					<p>Select a reviewer on the left to see their comments.</p>
				</div>
			<?php elseif ( empty( $pages ) ) : ?>
				<div class="cr-admin-card cr-placeholder">
					<p><?php echo esc_html( $reviewer_name ); ?> has not left any comments yet.</p>
				</div>
			<?php else : ?>
				<div class="cr-admin-card">
					<div class="cr-reviewer-header">
						<h2><?php echo esc_html( $reviewer_name ); ?></h2>
						<a href="<?php echo esc_url( home_url( '/' . CR_Role::SHELL_SLUG . '/' ) ); ?>" target="_blank" class="button">Open Preview Shell</a>
					</div>

					<?php foreach ( $pages as $page_url => $devices ) : ?>
						<div class="cr-page-group">
							<h3 class="cr-page-heading">
								<a href="<?php echo esc_url( home_url( $page_url ) ); ?>" target="_blank"><?php echo esc_html( $page_url ); ?></a>
							</h3>

							<?php foreach ( $devices as $device => $annotations ) : ?>
								<div class="cr-device-group">
									<h4 class="cr-device-heading"><?php echo esc_html( $device_labels[ $device ] ?? $device ); ?></h4>
									<div class="cr-annotation-list">
										<?php foreach ( $annotations as $i => $ann ) : ?>
											<div class="cr-annotation-card cr-ann-status--<?php echo esc_attr( $ann->status ); ?>" data-id="<?php echo (int) $ann->id; ?>">
												<div class="cr-ann-header">
													<span class="cr-ann-number"><?php echo $i + 1; ?></span>
													<span class="cr-ann-meta">
														<?php echo esc_html( wp_date( 'M j, g:ia', strtotime( $ann->created_at ) ) ); ?>
													</span>
													<div class="cr-ann-status-controls">
														<select class="cr-status-select" data-id="<?php echo (int) $ann->id; ?>">
															<?php foreach ( $status_labels as $val => $lbl ) : ?>
																<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $ann->status, $val ); ?>><?php echo esc_html( $lbl ); ?></option>
															<?php endforeach; ?>
														</select>
													</div>
												</div>

												<p class="cr-ann-comment"><?php echo nl2br( esc_html( $ann->comment ) ); ?></p>

												<div class="cr-ann-note-row">
													<textarea
														class="cr-admin-note-input"
														data-id="<?php echo (int) $ann->id; ?>"
														placeholder="Add a note for the client (optional)…"
														rows="2"
													><?php echo esc_textarea( $ann->admin_note ?? '' ); ?></textarea>
													<button class="button cr-save-note-btn" data-id="<?php echo (int) $ann->id; ?>">Save note</button>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

	</div><!-- .cr-reviews-layout -->
</div>
