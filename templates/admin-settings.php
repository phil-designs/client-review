<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- template included by plugin class; vars are local to this include.
require_once __DIR__ . '/../includes/class-cr-settings.php';
/** @var array $settings */
$s = $settings;
$fonts = PDCR_Settings::FONTS;
?>
<div class="wrap cr-admin-wrap">
	<h1>Client Review &mdash; Settings</h1>

	<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only flag set after redirect from save action; no state change here.
	if ( isset( $_GET['saved'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p>Settings saved.</p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="pdcr_save_settings">
		<?php wp_nonce_field( 'pdcr_save_settings' ); ?>

		<!-- ── Typography ─────────────────────────────────────────── -->
		<div class="cr-admin-card">
			<h2>Typography</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cr_heading_font">Heading Font</label></th>
					<td>
						<select id="cr_heading_font" name="pdcr_settings[heading_font]" class="cr-font-select" data-target="cr-heading-preview">
							<?php foreach ( $fonts as $name => $weights ) : ?>
								<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $s['heading_font'], $name ); ?>>
									<?php echo esc_html( $name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<span id="cr-heading-preview" class="cr-font-preview" style="font-family:'<?php echo esc_attr( $s['heading_font'] ); ?>',sans-serif">
							<?php echo esc_html( $s['heading_font'] ); ?>
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cr_body_font">Body Font</label></th>
					<td>
						<select id="cr_body_font" name="pdcr_settings[body_font]" class="cr-font-select" data-target="cr-body-preview">
							<?php foreach ( $fonts as $name => $weights ) : ?>
								<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $s['body_font'], $name ); ?>>
									<?php echo esc_html( $name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<span id="cr-body-preview" class="cr-font-preview" style="font-family:'<?php echo esc_attr( $s['body_font'] ); ?>',sans-serif">
							The quick brown fox
						</span>
					</td>
				</tr>
				<tr>
					<th scope="row">Remote Google Fonts</th>
					<td>
						<label>
							<input type="checkbox" name="pdcr_settings[use_remote_google_fonts]" value="1" <?php checked( $s['use_remote_google_fonts'], '1' ); ?>>
							Load fonts directly from Google's servers instead of the bundled versions.
						</label>
						<p class="description">This sends visitor IP addresses to Google on every page load. Off by default — the plugin ships with self-hosted copies of every font above.</p>
					</td>
				</tr>
			</table>
		</div>

		<!-- ── Accent Colour ──────────────────────────────────────── -->
		<div class="cr-admin-card">
			<h2>Accent Colour</h2>
			<p class="description">Used for pins, toolbar border, overlay, and active device button.</p>
			<table class="form-table cr-settings-table" role="presentation">
				<tr>
					<th scope="row">Accent</th>
					<td><?php PDCR_Settings::color_field( 'accent', $s['accent'] ); ?></td>
				</tr>
			</table>
		</div>

		<!-- ── Primary Button ─────────────────────────────────────── -->
		<div class="cr-admin-card">
			<h2>Primary Button</h2>
			<table class="form-table" role="presentation" style="margin-bottom:16px">
				<tr>
					<th scope="row"><label for="cr_btn_border_radius">Border Radius (px)</label></th>
					<td>
						<input type="number" id="cr_btn_border_radius" name="pdcr_settings[btn_border_radius]"
							value="<?php echo esc_attr( $s['btn_border_radius'] ); ?>"
							min="0" max="100" step="1" style="width:80px">
						<p class="description">Applies to both buttons. 0 = square, 30 = pill.</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cr_btn_font_weight">Font Weight</label></th>
					<td>
						<select id="cr_btn_font_weight" name="pdcr_settings[btn_font_weight]">
							<?php foreach ( [ '300' => 'Light', '400' => 'Regular', '500' => 'Medium', '600' => 'Semi Bold', '700' => 'Bold', '800' => 'Extra Bold', '900' => 'Black' ] as $val => $label ) : ?>
								<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $s['btn_font_weight'], $val ); ?>>
									<?php echo esc_html( $val . ' — ' . $label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">Applies to both buttons.</p>
					</td>
				</tr>
			</table>
			<table class="form-table cr-settings-table" role="presentation">
				<thead>
					<tr>
						<th></th>
						<th>Background</th>
						<th>Border</th>
						<th>Label</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th scope="row">Default</th>
						<td><?php PDCR_Settings::color_field( 'btn_primary_bg',    $s['btn_primary_bg'] ); ?></td>
						<td><?php PDCR_Settings::color_field( 'btn_primary_border', $s['btn_primary_border'] ); ?></td>
						<td><?php PDCR_Settings::color_field( 'btn_primary_color',  $s['btn_primary_color'] ); ?></td>
					</tr>
					<tr>
						<th scope="row">Hover</th>
						<td><?php PDCR_Settings::color_field( 'btn_primary_hover_bg', $s['btn_primary_hover_bg'], 'btn_primary_hover_bg_transparent', $s['btn_primary_hover_bg_transparent'] ); ?></td>
						<td><?php PDCR_Settings::color_field( 'btn_primary_hover_border', $s['btn_primary_hover_border'] ); ?></td>
						<td><?php PDCR_Settings::color_field( 'btn_primary_hover_color',  $s['btn_primary_hover_color'] ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- ── Secondary Button ───────────────────────────────────── -->
		<div class="cr-admin-card">
			<h2>Secondary Button</h2>
			<table class="form-table cr-settings-table" role="presentation">
				<thead>
					<tr>
						<th></th>
						<th>Background</th>
						<th>Border</th>
						<th>Label</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<th scope="row">Default</th>
						<td><?php PDCR_Settings::color_field( 'btn_secondary_bg', $s['btn_secondary_bg'], 'btn_secondary_bg_transparent', $s['btn_secondary_bg_transparent'] ); ?></td>
						<td><?php PDCR_Settings::color_field( 'btn_secondary_border', $s['btn_secondary_border'] ); ?></td>
						<td><?php PDCR_Settings::color_field( 'btn_secondary_color',  $s['btn_secondary_color'] ); ?></td>
					</tr>
					<tr>
						<th scope="row">Hover</th>
						<td><?php PDCR_Settings::color_field( 'btn_secondary_hover_bg', $s['btn_secondary_hover_bg'], 'btn_secondary_hover_bg_transparent', $s['btn_secondary_hover_bg_transparent'] ); ?></td>
						<td><?php PDCR_Settings::color_field( 'btn_secondary_hover_border', $s['btn_secondary_hover_border'] ); ?></td>
						<td><?php PDCR_Settings::color_field( 'btn_secondary_hover_color',  $s['btn_secondary_hover_color'] ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<!-- ── Registration Form Text ────────────────────────────── -->
		<div class="cr-admin-card">
			<h2>Registration Form Text</h2>
			<p class="description">Text shown on the invite registration page clients see when they open their invite link.</p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cr_form_heading">Heading</label></th>
					<td><?php PDCR_Settings::text_field( 'form_heading', $s['form_heading'] ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cr_form_subtitle">Subtitle</label></th>
					<td><?php PDCR_Settings::text_field( 'form_subtitle', $s['form_subtitle'] ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cr_form_button">Submit Button</label></th>
					<td><?php PDCR_Settings::text_field( 'form_button', $s['form_button'] ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cr_form_login_prompt">Login Prompt</label></th>
					<td>
						<?php PDCR_Settings::text_field( 'form_login_prompt', $s['form_login_prompt'] ); ?>
						<p class="description">Text before the login link, e.g. "Already have an account?"</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="cr_form_login_link">Login Link Label</label></th>
					<td><?php PDCR_Settings::text_field( 'form_login_link', $s['form_login_link'] ); ?></td>
				</tr>
			</table>

			<h3 style="margin:20px 0 8px;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;">Expired / Invalid Link</h3>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cr_expired_heading">Heading</label></th>
					<td><?php PDCR_Settings::text_field( 'expired_heading', $s['expired_heading'] ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cr_expired_body">Body Text</label></th>
					<td><?php PDCR_Settings::text_field( 'expired_body', $s['expired_body'] ); ?></td>
				</tr>
			</table>
		</div>

		<!-- ── Login Page Text ──────────────────────────────────── -->
		<div class="cr-admin-card">
			<h2>Login Page Text</h2>
			<p class="description">Text shown on the sign-in page for returning clients at <code>/client-review/login/</code>.</p>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="cr_login_heading">Heading</label></th>
					<td><?php PDCR_Settings::text_field( 'login_heading', $s['login_heading'] ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cr_login_subtitle">Subtitle</label></th>
					<td><?php PDCR_Settings::text_field( 'login_subtitle', $s['login_subtitle'] ); ?></td>
				</tr>
				<tr>
					<th scope="row"><label for="cr_login_button">Submit Button</label></th>
					<td><?php PDCR_Settings::text_field( 'login_button', $s['login_button'] ); ?></td>
				</tr>
			</table>
		</div>

		<?php submit_button( 'Save Settings' ); ?>
	</form>
</div>
