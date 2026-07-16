<?php
if ( ! defined( 'ABSPATH' ) ) exit;

require_once __DIR__ . '/class-cr-role.php';

class PDCR_Settings {

	const OPTION = 'pdcr_settings';

	const FONTS = [
		'Oswald'           => [400, 500, 600],
		'Bebas Neue'       => [400],
		'Anton'            => [400],
		'Montserrat'       => [400, 500, 600, 700],
		'Raleway'          => [400, 500, 600],
		'Barlow Condensed' => [400, 500, 600],
		'Playfair Display' => [400, 500, 700],
		'Poppins'          => [400, 500, 600],
		'Inter'            => [400, 500, 600],
		'Roboto'           => [400, 500, 700],
		'Open Sans'        => [400, 500, 600],
		'Lato'             => [400, 700],
		'Nunito'           => [400, 500, 600],
		'DM Sans'          => [400, 500],
		'Outfit'           => [400, 500, 600],
		'Work Sans'        => [400, 500, 600],
	];

	const DEFAULTS = [
		'heading_font' => 'Oswald',
		'body_font'    => 'Poppins',

		// Registration form text
		'form_heading'      => "You're invited to review",
		'form_subtitle'     => 'Create an account to access the preview and leave your feedback.',
		'form_button'       => 'Create account & start reviewing',
		'form_login_prompt' => 'Already have an account?',
		'form_login_link'   => 'Log in',
		'expired_heading'   => 'This invite link is no longer valid.',
		'expired_body'      => 'It may have expired or already been used. Please ask for a new link.',

		'login_heading'  => 'Welcome Back',
		'login_subtitle' => 'Sign in to continue your review.',
		'login_button'   => 'Sign in',

		'accent'            => '#F7941D',
		'btn_border_radius'  => '30',
		'btn_font_weight'    => '600',

		'btn_primary_bg'                   => '#F7941D',
		'btn_primary_border'               => '#F7941D',
		'btn_primary_color'                => '#ffffff',
		'btn_primary_hover_bg'             => '#F7941D',
		'btn_primary_hover_bg_transparent' => '1',
		'btn_primary_hover_border'         => '#F7941D',
		'btn_primary_hover_color'          => '#F7941D',

		'btn_secondary_bg'                    => '#F7941D',
		'btn_secondary_bg_transparent'        => '1',
		'btn_secondary_border'                => '#F7941D',
		'btn_secondary_color'                 => '#e8e8e8',
		'btn_secondary_hover_bg'              => '#F7941D',
		'btn_secondary_hover_bg_transparent'  => '',
		'btn_secondary_hover_border'          => '#F7941D',
		'btn_secondary_hover_color'           => '#ffffff',

		'use_remote_google_fonts' => '',
	];

	public static function get(): array {
		return wp_parse_args( get_option( self::OPTION, [] ), self::DEFAULTS );
	}

	public static function init(): void {
		add_action( 'admin_post_pdcr_save_settings', [ __CLASS__, 'save' ] );
	}

	public static function save(): void {
		check_admin_referer( 'pdcr_save_settings' );
		if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Unauthorized' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- each field is sanitized/unslashed individually below.
		$raw  = isset( $_POST['pdcr_settings'] ) ? wp_unslash( $_POST['pdcr_settings'] ) : [];
		$data = [];

		$allowed_fonts = array_keys( self::FONTS );

		$data['heading_font'] = in_array( $raw['heading_font'] ?? '', $allowed_fonts, true )
			? $raw['heading_font'] : self::DEFAULTS['heading_font'];
		$data['body_font'] = in_array( $raw['body_font'] ?? '', $allowed_fonts, true )
			? $raw['body_font'] : self::DEFAULTS['body_font'];

		$color_keys = [
			'accent',
			'btn_primary_bg', 'btn_primary_border', 'btn_primary_color',
			'btn_primary_hover_bg', 'btn_primary_hover_border', 'btn_primary_hover_color',
			'btn_secondary_bg', 'btn_secondary_border', 'btn_secondary_color',
			'btn_secondary_hover_bg', 'btn_secondary_hover_border', 'btn_secondary_hover_color',
		];
		foreach ( $color_keys as $key ) {
			$val        = sanitize_hex_color( $raw[ $key ] ?? '' );
			$data[$key] = $val ?: self::DEFAULTS[$key];
		}

		$data['btn_border_radius'] = (string) absint( $raw['btn_border_radius'] ?? self::DEFAULTS['btn_border_radius'] );

		$allowed_weights = [ '300', '400', '500', '600', '700', '800', '900' ];
		$data['btn_font_weight'] = in_array( $raw['btn_font_weight'] ?? '', $allowed_weights, true )
			? $raw['btn_font_weight'] : self::DEFAULTS['btn_font_weight'];

		$text_keys = [
			'form_heading', 'form_subtitle', 'form_button',
			'form_login_prompt', 'form_login_link',
			'expired_heading', 'expired_body',
			'login_heading', 'login_subtitle', 'login_button',
		];
		foreach ( $text_keys as $key ) {
			$data[$key] = sanitize_text_field( $raw[$key] ?? '' ) ?: self::DEFAULTS[$key];
		}

		$transparent_keys = [
			'btn_primary_hover_bg_transparent',
			'btn_secondary_bg_transparent',
			'btn_secondary_hover_bg_transparent',
		];
		foreach ( $transparent_keys as $key ) {
			$data[$key] = isset( $raw[$key] ) ? '1' : '';
		}

		$data['use_remote_google_fonts'] = isset( $raw['use_remote_google_fonts'] ) ? '1' : '';

		update_option( self::OPTION, $data );
		wp_safe_redirect( add_query_arg( [ 'page' => 'pdcr-settings', 'saved' => '1' ], admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function plugin_url(): string {
		return plugin_dir_url( __DIR__ . '/../client-review.php' );
	}

	/**
	 * Strips every enqueued style/script down to an explicit allow-list.
	 *
	 * The login, registration, and preview-shell pages are standalone,
	 * fully custom screens — they were never meant to inherit the active
	 * theme's global stylesheet or layout scripts. Now that they properly
	 * call wp_head()/wp_footer() (required to enqueue our own assets the
	 * WordPress-approved way), the theme's own assets load right alongside
	 * ours and can visually conflict (e.g. a theme-wide `iframe { height:
	 * auto }` reset, or global input/box-sizing resets). Pruning the queue
	 * down to our own handles at the last possible moment keeps these
	 * pages isolated the way they were originally designed, while still
	 * using wp_enqueue_style()/wp_enqueue_script() throughout.
	 */
	public static function isolate_page_assets( array $style_handles, array $script_handles = [] ): void {
		add_action( 'wp_enqueue_scripts', static function () use ( $style_handles, $script_handles ): void {
			global $wp_styles, $wp_scripts;
			if ( $wp_styles instanceof WP_Styles ) {
				$wp_styles->queue = array_values( array_intersect( $wp_styles->queue, $style_handles ) );
			}
			if ( $wp_scripts instanceof WP_Scripts ) {
				$wp_scripts->queue = array_values( array_intersect( $wp_scripts->queue, $script_handles ) );
			}
		}, PHP_INT_MAX );
	}

	/**
	 * URL of the stylesheet that supplies the plugin's font-family declarations.
	 *
	 * Defaults to the bundled, self-hosted @font-face stylesheet so nothing is
	 * requested from Google's servers unless the site owner explicitly opts in
	 * via the "use_remote_google_fonts" setting (off by default).
	 */
	public static function fonts_stylesheet_url( array $s ): string {
		if ( ! empty( $s['use_remote_google_fonts'] ) ) {
			$remote = self::remote_google_fonts_url( $s );
			if ( $remote ) return $remote;
		}
		return self::plugin_url() . 'assets/css/fonts-local.css';
	}

	/**
	 * Enqueues the appropriate fonts stylesheet (local by default) and, only
	 * when the site owner has opted into remote fonts, registers preconnect
	 * resource hints for Google's font domains.
	 */
	public static function enqueue_fonts( array $s ): void {
		wp_enqueue_style( 'pdcr-fonts', self::fonts_stylesheet_url( $s ), [], PDCR_VERSION );

		if ( ! empty( $s['use_remote_google_fonts'] ) ) {
			add_filter( 'wp_resource_hints', static function ( array $hints, string $relation_type ): array {
				if ( 'preconnect' === $relation_type ) {
					$hints[] = [ 'href' => 'https://fonts.googleapis.com' ];
					$hints[] = [ 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' ];
				}
				return $hints;
			}, 10, 2 );
		}
	}

	public static function remote_google_fonts_url( array $s ): string {
		$params = [];
		foreach ( array_unique( [ $s['heading_font'], $s['body_font'] ] ) as $font ) {
			if ( isset( self::FONTS[$font] ) ) {
				$weights  = implode( ';', self::FONTS[$font] );
				$params[] = 'family=' . urlencode( $font ) . ':wght@' . $weights;
			}
		}
		if ( empty( $params ) ) return '';
		return 'https://fonts.googleapis.com/css2?' . implode( '&', $params ) . '&display=swap';
	}

	public static function text_field( string $key, string $value ): void {
		echo '<input type="text" id="cr_' . esc_attr( $key ) . '" name="pdcr_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" class="regular-text">';
	}

	public static function color_field( string $key, string $value, string $transparent_key = '', string $is_transparent = '' ): void {
		$id = 'cr_' . $key;
		echo '<span class="cr-color-wrap">';
		echo '<input type="color" id="' . esc_attr( $id ) . '" name="pdcr_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '">';
		echo '<input type="text"  value="' . esc_attr( $value ) . '" maxlength="7" placeholder="#000000">';
		if ( $transparent_key ) {
			$cb_id = 'cr_' . $transparent_key;
			echo '<label class="cr-transparent-label">';
			echo '<input type="checkbox" id="' . esc_attr( $cb_id ) . '" name="pdcr_settings[' . esc_attr( $transparent_key ) . ']" value="1"' . checked( $is_transparent, '1', false ) . '>';
			echo 'Transparent</label>';
		}
		echo '</span>';
	}

	public static function css_vars( array $s ): string {
		$heading = esc_js( $s['heading_font'] );
		$body    = esc_js( $s['body_font'] );

		$p_bg       = esc_attr( $s['btn_primary_bg'] );
		$p_border   = esc_attr( $s['btn_primary_border'] );
		$p_color    = esc_attr( $s['btn_primary_color'] );
		$p_h_bg     = $s['btn_primary_hover_bg_transparent'] ? 'transparent' : esc_attr( $s['btn_primary_hover_bg'] );
		$p_h_border = esc_attr( $s['btn_primary_hover_border'] );
		$p_h_color  = esc_attr( $s['btn_primary_hover_color'] );

		$s_bg       = $s['btn_secondary_bg_transparent'] ? 'transparent' : esc_attr( $s['btn_secondary_bg'] );
		$s_border   = esc_attr( $s['btn_secondary_border'] );
		$s_color    = esc_attr( $s['btn_secondary_color'] );
		$s_h_bg     = $s['btn_secondary_hover_bg_transparent'] ? 'transparent' : esc_attr( $s['btn_secondary_hover_bg'] );
		$s_h_border = esc_attr( $s['btn_secondary_hover_border'] );
		$s_h_color  = esc_attr( $s['btn_secondary_hover_color'] );

		$accent = esc_attr( $s['accent'] );
		$radius = absint( $s['btn_border_radius'] ) . 'px';
		$weight = esc_attr( $s['btn_font_weight'] );

		return ":root {
	--cr-font-heading: '{$heading}', sans-serif;
	--cr-font-body: '{$body}', sans-serif;
	--cr-accent: {$accent};
	--cr-btn-radius: {$radius};
	--cr-btn-font-weight: {$weight};
	--cr-btn-p-bg: {$p_bg};
	--cr-btn-p-border: {$p_border};
	--cr-btn-p-color: {$p_color};
	--cr-btn-p-hover-bg: {$p_h_bg};
	--cr-btn-p-hover-border: {$p_h_border};
	--cr-btn-p-hover-color: {$p_h_color};
	--cr-btn-s-bg: {$s_bg};
	--cr-btn-s-border: {$s_border};
	--cr-btn-s-color: {$s_color};
	--cr-btn-s-hover-bg: {$s_h_bg};
	--cr-btn-s-hover-border: {$s_h_border};
	--cr-btn-s-hover-color: {$s_h_color};
}";
	}
}
