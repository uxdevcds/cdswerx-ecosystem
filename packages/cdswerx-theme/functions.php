<?php
/**
 * Theme functions and definitions
 *
 * @package CDSWerxElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'CDSWERX_ELEMENTOR_VERSION', '1.1.0' );
define( 'EHP_THEME_SLUG', 'cdswerx-theme' );

define( 'CDSWERX_THEME_PATH', get_template_directory() );
define( 'CDSWERX_THEME_URL', get_template_directory_uri() );
define( 'CDSWERX_THEME_ASSETS_PATH', CDSWERX_THEME_PATH . '/assets/' );
define( 'CDSWERX_THEME_ASSETS_URL', CDSWERX_THEME_URL . '/assets/' );
define( 'CDSWERX_THEME_SCRIPTS_PATH', CDSWERX_THEME_ASSETS_PATH . 'js/' );
define( 'CDSWERX_THEME_SCRIPTS_URL', CDSWERX_THEME_ASSETS_URL . 'js/' );
define( 'CDSWERX_THEME_STYLE_PATH', CDSWERX_THEME_ASSETS_PATH . 'css/' );
define( 'CDSWERX_THEME_STYLE_URL', CDSWERX_THEME_ASSETS_URL . 'css/' );
define( 'CDSWERX_THEME_IMAGES_PATH', CDSWERX_THEME_ASSETS_PATH . 'images/' );
define( 'CDSWERX_THEME_IMAGES_URL', CDSWERX_THEME_ASSETS_URL . 'images/' );

if ( ! isset( $content_width ) ) {
	$content_width = 800; // Pixels.
}

if ( ! function_exists( 'cdswerx_elementor_setup' ) ) {
	/**
	 * Set up theme support.
	 *
	 * @return void
	 */
	function cdswerx_elementor_setup() {
		if ( is_admin() ) {
			cdswerx_maybe_update_theme_version_in_db();
		}

		if ( apply_filters( 'cdswerx_elementor_register_menus', true ) ) {
			register_nav_menus( [ 'menu-1' => esc_html__( 'Header', 'cdswerx-theme' ) ] );
			register_nav_menus( [ 'menu-2' => esc_html__( 'Footer', 'cdswerx-theme' ) ] );
		}

		if ( apply_filters( 'cdswerx_elementor_post_type_support', true ) ) {
			add_post_type_support( 'page', 'excerpt' );
		}

		if ( apply_filters( 'cdswerx_elementor_add_theme_support', true ) ) {
			add_theme_support( 'post-thumbnails' );
			add_theme_support( 'automatic-feed-links' );
			add_theme_support( 'title-tag' );
			add_theme_support(
				'html5',
				[
					'search-form',
					'comment-form',
					'comment-list',
					'gallery',
					'caption',
					'script',
					'style',
					'navigation-widgets',
				]
			);
			add_theme_support(
				'custom-logo',
				[
					'height'      => 100,
					'width'       => 350,
					'flex-height' => true,
					'flex-width'  => true,
				]
			);
			add_theme_support( 'align-wide' );
			add_theme_support( 'responsive-embeds' );

			/*
			 * Editor Styles
			 */
			add_theme_support( 'editor-styles' );
			add_editor_style( 'editor-styles.css' );

			/*
			 * WooCommerce.
			 */
			if ( apply_filters( 'cdswerx_add_woocommerce_support', true ) ) {
				// WooCommerce in general.
				add_theme_support( 'woocommerce' );
				// Enabling WooCommerce product gallery features (are off by default since WC 3.0.0).
				// zoom.
				add_theme_support( 'wc-product-gallery-zoom' );
				// lightbox.
				add_theme_support( 'wc-product-gallery-lightbox' );
				// swipe.
				add_theme_support( 'wc-product-gallery-slider' );
			}
		}
	}
}
add_action( 'after_setup_theme', 'cdswerx_elementor_setup' );

function cdswerx_maybe_update_theme_version_in_db() {
	$theme_version_option_name = 'cdswerx_theme_version';
	// The theme version saved in the database.
	$cdswerx_theme_db_version = get_option( $theme_version_option_name );

	// If the 'cdswerx_theme_version' option does not exist in the DB, or the version needs to be updated, do the update.
	if ( ! $cdswerx_theme_db_version || version_compare( $cdswerx_theme_db_version, CDSWERX_ELEMENTOR_VERSION, '<' ) ) {
		update_option( $theme_version_option_name, CDSWERX_ELEMENTOR_VERSION );
	}
}

if ( ! function_exists( 'cdswerx_display_header_footer' ) ) {
	/**
	 * Check whether to display header footer.
	 *
	 * @return bool
	 */
	function cdswerx_display_header_footer() {
		$cdswerx_header_footer = true;

		return apply_filters( 'cdswerx_header_footer', $cdswerx_header_footer );
	}
}

if ( ! function_exists( 'cdswerx_scripts_styles' ) ) {
	/**
	 * Theme Scripts & Styles.
	 *
	 * @return void
	 */
	function cdswerx_scripts_styles() {
		if ( apply_filters( 'cdswerx_enqueue_style', true ) ) {
			wp_enqueue_style(
				'cdswerx-theme',
				CDSWERX_THEME_STYLE_URL . 'reset.css',
				[],
				CDSWERX_ELEMENTOR_VERSION
			);
		}

		if ( apply_filters( 'cdswerx_enqueue_theme_style', true ) ) {
			wp_enqueue_style(
				'cdswerx-theme-style',
				CDSWERX_THEME_STYLE_URL . 'theme.css',
				[],
				CDSWERX_ELEMENTOR_VERSION
			);
		}

		if ( cdswerx_display_header_footer() ) {
			wp_enqueue_style(
				'cdswerx-theme-header-footer',
				CDSWERX_THEME_STYLE_URL . 'header-footer.css',
				[],
				CDSWERX_ELEMENTOR_VERSION
			);
		}
	}
}
add_action( 'wp_enqueue_scripts', 'cdswerx_scripts_styles' );

if ( ! function_exists( 'cdswerx_register_elementor_locations' ) ) {
	/**
	 * Register Elementor Locations.
	 *
	 * @param ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $elementor_theme_manager theme manager.
	 *
	 * @return void
	 */
	function cdswerx_register_elementor_locations( $elementor_theme_manager ) {
		if ( apply_filters( 'cdswerx_register_elementor_locations', true ) ) {
			$elementor_theme_manager->register_all_core_location();
		}
	}
}
add_action( 'elementor/theme/register_locations', 'cdswerx_register_elementor_locations' );

if ( ! function_exists( 'cdswerx_content_width' ) ) {
	/**
	 * Set default content width.
	 *
	 * @return void
	 */
	function cdswerx_content_width() {
		$GLOBALS['content_width'] = apply_filters( 'cdswerx_content_width', 800 );
	}
}
add_action( 'after_setup_theme', 'cdswerx_content_width', 0 );

if ( ! function_exists( 'cdswerx_add_description_meta_tag' ) ) {
	/**
	 * Add description meta tag with excerpt text.
	 *
	 * @return void
	 */
	function cdswerx_add_description_meta_tag() {
		if ( ! apply_filters( 'cdswerx_description_meta_tag', true ) ) {
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( empty( $post->post_excerpt ) ) {
			return;
		}

		echo '<meta name="description" content="' . esc_attr( wp_strip_all_tags( $post->post_excerpt ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'cdswerx_add_description_meta_tag' );

// Settings page
require get_template_directory() . '/includes/settings-functions.php';

// CDSWerx Settings Handler
require get_template_directory() . '/includes/cdswerx-settings-handler.php';

// Header & footer styling option, inside Elementor
require get_template_directory() . '/includes/elementor-functions.php';

if ( ! function_exists( 'cdswerx_customizer' ) ) {
	// Customizer controls
	function cdswerx_customizer() {
		if ( ! is_customize_preview() ) {
			return;
		}

		if ( ! cdswerx_display_header_footer() ) {
			return;
		}

		require get_template_directory() . '/includes/customizer-functions.php';
	}
}
add_action( 'init', 'cdswerx_customizer' );

if ( ! function_exists( 'cdswerx_check_hide_title' ) ) {
	/**
	 * Check whether to display the page title.
	 *
	 * @param bool $val default value.
	 *
	 * @return bool
	 */
	function cdswerx_check_hide_title( $val ) {
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$current_doc = Elementor\Plugin::instance()->documents->get( get_the_ID() );
			if ( $current_doc && 'yes' === $current_doc->get_settings( 'hide_title' ) ) {
				$val = false;
			}
		}
		return $val;
	}
}
add_filter( 'cdswerx_page_title', 'cdswerx_check_hide_title' );

/**
 * BC:
 * Backward compatibility function for CDSWerx themes.
 * The following code prevents fatal errors in child themes that still use this function.
 */
if ( ! function_exists( 'cdswerx_body_open' ) ) {
	function cdswerx_body_open() {
		wp_body_open();
	}
}

require CDSWERX_THEME_PATH . '/theme.php';

CDSWerxTheme\Theme::instance();

/**
 * CDSWerx Plugin Integration
 *
 * Register this theme with the CDSWerx plugin ecosystem
 * for enhanced functionality and asset management.
 */
if ( function_exists( 'CDSWerx_Theme_Manager' ) ) {
	add_action( 'init', 'cdswerx_theme_plugin_integration', 10 );
}

/**
 * Initialize CDSWerx plugin integration
 */
function cdswerx_theme_plugin_integration() {
	// Register theme with CDSWerx plugin
	if ( class_exists( 'CDSWerx_Theme_Manager' ) ) {
		// Let the plugin know this theme is active and ready
		do_action( 'cdswerx_theme_ready', 'cdswerx-theme', CDSWERX_ELEMENTOR_VERSION );
	}
}

/**
 * Hello Elementor Compatibility Monitor
 * 
 * Monitor Hello Elementor theme for updates and compatibility
 * without creating dependencies on Hello Elementor functionality
 */
function cdswerx_hello_elementor_compatibility_monitor() {
	// Check if Hello Elementor theme is available for update monitoring
	$hello_theme = wp_get_theme( 'hello-elementor' );
	
	if ( $hello_theme->exists() ) {
		// Monitor version for compatibility updates
		$hello_version = $hello_theme->get( 'Version' );
		update_option( 'cdswerx_hello_elementor_version', $hello_version );
		
		// Trigger compatibility check if version changed
		$stored_version = get_option( 'cdswerx_hello_elementor_stored_version' );
		if ( $stored_version !== $hello_version ) {
			do_action( 'cdswerx_hello_elementor_version_changed', $hello_version, $stored_version );
			update_option( 'cdswerx_hello_elementor_stored_version', $hello_version );
		}
	}
}
add_action( 'init', 'cdswerx_hello_elementor_compatibility_monitor' );

/**
 * CDSWerx Theme Independence Validation
 * 
 * Verify theme can operate independently without Hello Elementor build requirements
 */
function cdswerx_validate_theme_independence() {
	$independence_status = array(
		'hello_elementor_files' => false,
		'hellotheme_namespace' => false,
		'cdswerx_functions' => true,
		'plugin_integration' => false,
		'independent_operation' => true
	);
	
	// Check for Hello Elementor contamination
	$hello_files = glob( CDSWERX_THEME_PATH . '/assets/js/hello-*' );
	$independence_status['hello_elementor_files'] = empty( $hello_files );
	
	// Check CDSWerx plugin integration
	$independence_status['plugin_integration'] = class_exists( 'Cdswerx' );
	
	// Store validation results
	update_option( 'cdswerx_theme_independence_status', $independence_status );
	
	return $independence_status;
}

/**
 * CDSWerx Theme Health Check
 * 
 * Comprehensive system validation for theme functionality
 */
function cdswerx_theme_health_check() {
	$health_status = array(
		'theme_loaded' => true,
		'cdswerx_namespace' => class_exists( 'CDSWerxTheme\Theme' ),
		'elementor_integration' => did_action( 'elementor/loaded' ),
		'typography_fallback' => function_exists( 'cdswerx_parent_load_typography_fallback' ),
		'independence_validated' => false
	);
	
	// Run independence validation
	$independence = cdswerx_validate_theme_independence();
	$health_status['independence_validated'] = $independence['independent_operation'];
	
	// Log health check results for debugging
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		error_log( 'CDSWerx Theme Health Check: ' . print_r( $health_status, true ) );
	}
	
	return $health_status;
}

// Run health check on theme activation and admin init
add_action( 'after_switch_theme', 'cdswerx_theme_health_check' );
add_action( 'admin_init', 'cdswerx_theme_health_check' );

/**
 * CDSWerx Independent Operation Functions
 * 
 * Fallback functions to ensure theme operates independently
 * without external theme dependencies
 */

/**
 * CDSWerx Body Open fallback for backward compatibility
 */
if ( ! function_exists( 'hello_elementor_body_open' ) ) {
	function hello_elementor_body_open() {
		// Fallback to CDSWerx implementation
		cdswerx_body_open();
	}
}

/**
 * CDSWerx Hello Elementor function fallbacks for compatibility
 * These functions provide fallbacks when Hello Elementor functions are referenced
 * but maintain CDSWerx independence
 */
if ( ! function_exists( 'hello_header_footer_experiment_active' ) ) {
	function hello_header_footer_experiment_active() {
		// Fallback to CDSWerx implementation
		return cdswerx_header_footer_experiment_active();
	}
}

if ( ! function_exists( 'hello_elementor_display_header_footer' ) ) {
	function hello_elementor_display_header_footer() {
		// Fallback to CDSWerx implementation
		return cdswerx_display_header_footer();
	}
}

/**
 * EMERGENCY: Hello Elementor Fatal Error Prevention
 * 
 * These functions prevent fatal errors when Hello Elementor functions are called
 * but Hello Elementor theme is not active/installed
 */
if ( ! function_exists( 'hello_elementor_get_setting' ) ) {
	function hello_elementor_get_setting( $setting_id ) {
		// CDSWerx fallback - prevent fatal errors with sensible defaults
		$defaults = [
			'hello_header_logo_type' => 'logo',
			'hello_header_tagline_display' => false,
			'hello_header_logo_display' => true,
			'hello_header_menu_display' => true,
			'hello_footer_logo_type' => 'logo',
			'hello_footer_logo_display' => true,
			'hello_footer_tagline_display' => false,
			'hello_footer_menu_display' => true,
			'hello_footer_copyright_text' => '',
			'hello_footer_copyright_display' => false
		];
		
		$value = isset( $defaults[$setting_id] ) ? $defaults[$setting_id] : '';
		
		// Allow filtering for customization
		return apply_filters( 'cdswerx_hello_elementor_setting_fallback', $value, $setting_id );
	}
}

if ( ! function_exists( 'hello_show_or_hide' ) ) {
	function hello_show_or_hide( $setting_id ) {
		// CDSWerx fallback - prevent fatal errors
		$setting_value = hello_elementor_get_setting( $setting_id );
		return $setting_value ? 'show' : 'hide';
	}
}

/**
 * Load CDSWerx Typography Fallback System
 */
if ( file_exists( get_template_directory() . '/includes/typography-fallback.php' ) ) {
	require_once get_template_directory() . '/includes/typography-fallback.php';
}
