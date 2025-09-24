<?php
/**
 * CDSWerx Theme Settings Registration
 * 
 * Register and handle CDSWerx theme settings
 * 
 * @package CDSWerxElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register CDSWerx Theme Settings
 */
function cdswerx_register_theme_settings() {
	// Structure and Layout Settings
	register_setting(
		'cdswerx_theme_settings',
		'cdswerx_disable_header_footer',
		array(
			'type' => 'boolean',
			'default' => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);

	register_setting(
		'cdswerx_theme_settings',
		'cdswerx_hide_page_title',
		array(
			'type' => 'boolean',
			'default' => false,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);

	// SEO and Accessibility Settings
	register_setting(
		'cdswerx_theme_settings',
		'cdswerx_skip_link',
		array(
			'type' => 'boolean',
			'default' => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);

	// CSS and Styling Settings
	register_setting(
		'cdswerx_theme_settings',
		'cdswerx_css_optimization',
		array(
			'type' => 'boolean',
			'default' => true,
			'sanitize_callback' => 'rest_sanitize_boolean',
		)
	);
}
add_action( 'admin_init', 'cdswerx_register_theme_settings' );

/**
 * Apply CDSWerx Settings to Theme Functionality
 */

// Disable header/footer if setting is enabled
function cdswerx_maybe_disable_header_footer() {
	if ( get_option( 'cdswerx_disable_header_footer', false ) ) {
		add_filter( 'cdswerx_header_footer', '__return_false' );
	}
}
add_action( 'init', 'cdswerx_maybe_disable_header_footer' );

// Hide page title if setting is enabled
function cdswerx_maybe_hide_page_title( $show_title ) {
	if ( get_option( 'cdswerx_hide_page_title', false ) ) {
		return false;
	}
	return $show_title;
}
add_filter( 'cdswerx_page_title', 'cdswerx_maybe_hide_page_title' );

// Add skip link if setting is enabled
function cdswerx_maybe_add_skip_link() {
	if ( get_option( 'cdswerx_skip_link', true ) ) {
		echo '<a class="skip-link screen-reader-text" href="#main">' . esc_html__( 'Skip to content', 'cdswerx-theme' ) . '</a>';
	}
}
add_action( 'wp_body_open', 'cdswerx_maybe_add_skip_link' );

// CSS optimization
function cdswerx_maybe_optimize_css() {
	if ( get_option( 'cdswerx_css_optimization', true ) ) {
		// Add CSS optimization logic here
		add_action( 'wp_enqueue_scripts', 'cdswerx_optimize_css_loading', 999 );
	}
}
add_action( 'init', 'cdswerx_maybe_optimize_css' );

function cdswerx_optimize_css_loading() {
	// Defer non-critical CSS loading
	// This is a placeholder for CSS optimization functionality
}

/**
 * Add CDSWerx Settings Page Styles to Admin
 */
function cdswerx_admin_settings_styles( $hook ) {
	// Only load on CDSWerx settings page
	if ( 'cdswerx-theme_page_cdswerx-theme-settings' !== $hook ) {
		return;
	}

	// Enqueue jQuery for tab functionality
	wp_enqueue_script( 'jquery' );
}
add_action( 'admin_enqueue_scripts', 'cdswerx_admin_settings_styles' );

/**
 * Add Skip Link Styles
 */
function cdswerx_skip_link_styles() {
	if ( get_option( 'cdswerx_skip_link', true ) ) {
		?>
		<style>
		.skip-link {
			position: absolute;
			left: -9999px;
			z-index: 999999999;
			padding: 8px 16px;
			background: #000;
			color: #fff;
			text-decoration: none;
		}
		.skip-link:focus {
			left: 6px;
			top: 7px;
		}
		</style>
		<?php
	}
}
add_action( 'wp_head', 'cdswerx_skip_link_styles' );