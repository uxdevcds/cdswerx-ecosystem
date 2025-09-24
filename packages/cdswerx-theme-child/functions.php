<?php
/**
 * CDSWerx Theme Child Functions
 *
 * Functions and definitions for CDSWerx Theme Child.
 * Provides asset management, CDSWerx plugin integration, and enhanced functionality.
 *
 * @package CDSWerxThemeChild
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'CDSWERX_CHILD_VERSION', '1.0.0' );
define( 'CDSWERX_CHILD_PATH', get_stylesheet_directory() );
define( 'CDSWERX_CHILD_URL', get_stylesheet_directory_uri() );

/**
 * Enqueue Child Theme Scripts & Styles
 *
 * @return void
 */
function cdswerx_child_enqueue_styles() {
    // Parent theme styles
    wp_enqueue_style(
        'cdswerx-elementor-style',
        get_template_directory_uri() . '/style.css',
        [],
        wp_get_theme()->get('Version')
    );
    
    // Child theme styles
    wp_enqueue_style(
        'cdswerx-child-style',
        get_stylesheet_directory_uri() . '/style.css',
        ['cdswerx-elementor-style'],
        CDSWERX_CHILD_VERSION
    );
    
    // CDSWerx Asset Management (will be automated in TE-5)
    cdswerx_child_enqueue_assets();
}
add_action( 'wp_enqueue_scripts', 'cdswerx_child_enqueue_styles', 20 );

/**
 * TE-5: Intelligent CDSWerx Asset Management
 * 
 * Enhanced asset loading with Elementor Pro detection, conditional CSS injection,
 * and performance optimization based on site configuration.
 * 
 * @since TE-5
 */
function cdswerx_child_enqueue_assets() {
    $asset_base_url = get_stylesheet_directory_uri() . '/assets/css/';
    $asset_manager = new CDSWerx_Asset_Manager();
    
    // Core assets - Always loaded
    wp_enqueue_style(
        'cdswerx-admin-styles',
        $asset_base_url . 'admin-style.css',
        [],
        CDSWERX_CHILD_VERSION
    );
    
    // Global styles with conditional enhancements
    wp_enqueue_style(
        'cdswerx-global-styles',
        $asset_base_url . 'cds-globalstyles.css',
        ['cdswerx-child-style'],
        CDSWERX_CHILD_VERSION
    );
    
    // Intelligent theme styles loading
    if ( $asset_manager->should_load_theme_styles() ) {
        wp_enqueue_style(
            'cdswerx-theme-styles',
            $asset_base_url . 'theme-styles.css',
            ['cdswerx-global-styles'],
            CDSWERX_CHILD_VERSION
        );
    }
    
    // Elementor-specific optimizations
    if ( $asset_manager->is_elementor_page() ) {
        $asset_manager->enqueue_elementor_optimizations();
    }
    
    // Elementor Pro enhancements
    if ( $asset_manager->is_elementor_pro_active() ) {
        $asset_manager->enqueue_elementor_pro_assets();
    }
    
    // Icon libraries with intelligent loading
    $asset_manager->enqueue_icon_libraries();
    
    // Custom CSS injection point
    $asset_manager->inject_custom_css();
}

/**
 * TE-5: CDSWerx Asset Manager Class
 * 
 * Handles intelligent asset loading, Elementor detection, and performance optimization
 * 
 * @since TE-5
 */
class CDSWerx_Asset_Manager {
    
    private $elementor_active = null;
    private $elementor_pro_active = null;
    private $is_elementor_page = null;
    
    /**
     * Check if Elementor is active
     */
    public function is_elementor_active() {
        if ( $this->elementor_active === null ) {
            $this->elementor_active = is_plugin_active( 'elementor/elementor.php' );
        }
        return $this->elementor_active;
    }
    
    /**
     * Check if Elementor Pro is active
     */
    public function is_elementor_pro_active() {
        if ( $this->elementor_pro_active === null ) {
            $this->elementor_pro_active = is_plugin_active( 'elementor-pro/elementor-pro.php' );
        }
        return $this->elementor_pro_active;
    }
    
    /**
     * Check if current page is built with Elementor
     */
    public function is_elementor_page() {
        if ( $this->is_elementor_page === null ) {
            if ( ! $this->is_elementor_active() ) {
                $this->is_elementor_page = false;
            } else {
                global $post;
                $this->is_elementor_page = $post && \Elementor\Plugin::$instance->documents->get( $post->ID )->is_built_with_elementor();
            }
        }
        return $this->is_elementor_page;
    }
    
    /**
     * Determine if theme styles should be loaded
     */
    public function should_load_theme_styles() {
        // Load theme styles if:
        // 1. Not an Elementor page (standard WordPress content)
        // 2. Elementor page but no Pro (needs theme styling support)
        // 3. Admin preference to always load theme styles
        
        if ( ! $this->is_elementor_page() ) {
            return true; // Standard WordPress content needs theme styles
        }
        
        if ( $this->is_elementor_page() && ! $this->is_elementor_pro_active() ) {
            return true; // Elementor Free needs more theme support
        }
        
        // Check if forced via filter or admin setting
        return apply_filters( 'cdswerx_force_theme_styles', false );
    }
    
    /**
     * Enqueue Elementor-specific optimizations
     */
    public function enqueue_elementor_optimizations() {
        $asset_base_url = get_stylesheet_directory_uri() . '/assets/css/';
        
        // Elementor-specific styles
        if ( file_exists( CDSWERX_CHILD_PATH . '/assets/css/elementor-optimizations.css' ) ) {
            wp_enqueue_style(
                'cdswerx-elementor-optimizations',
                $asset_base_url . 'elementor-optimizations.css',
                ['cdswerx-global-styles'],
                CDSWERX_CHILD_VERSION
            );
        }
        
        // Remove theme styles that conflict with Elementor
        $this->remove_conflicting_styles();
    }
    
    /**
     * Enqueue Elementor Pro specific assets
     */
    public function enqueue_elementor_pro_assets() {
        $asset_base_url = get_stylesheet_directory_uri() . '/assets/css/';
        
        // Enhanced Pro optimizations
        if ( file_exists( CDSWERX_CHILD_PATH . '/assets/css/elementor-pro-enhancements.css' ) ) {
            wp_enqueue_style(
                'cdswerx-elementor-pro-enhancements',
                $asset_base_url . 'elementor-pro-enhancements.css',
                ['cdswerx-elementor-optimizations'],
                CDSWERX_CHILD_VERSION
            );
        }
    }
    
    /**
     * Intelligently load icon libraries
     */
    public function enqueue_icon_libraries() {
        $asset_base_url = get_stylesheet_directory_uri() . '/assets/css/';
        
        // Load based on usage detection or admin preference
        $load_bricons = $this->should_load_bricons();
        $load_carbonguicon = $this->should_load_carbonguicon();
        
        if ( $load_bricons && file_exists( CDSWERX_CHILD_PATH . '/assets/css/bricons-style.css' ) ) {
            wp_enqueue_style(
                'cdswerx-bricons',
                $asset_base_url . 'bricons-style.css',
                [],
                CDSWERX_CHILD_VERSION
            );
        }
        
        if ( $load_carbonguicon && file_exists( CDSWERX_CHILD_PATH . '/assets/css/carbonguicon-styles.css' ) ) {
            wp_enqueue_style(
                'cdswerx-carbonguicon',
                $asset_base_url . 'carbonguicon-styles.css',
                [],
                CDSWERX_CHILD_VERSION
            );
        }
    }
    
    /**
     * Inject custom CSS based on configuration
     */
    public function inject_custom_css() {
        $custom_css = $this->get_custom_css();
        
        if ( ! empty( $custom_css ) ) {
            wp_add_inline_style( 'cdswerx-global-styles', $custom_css );
        }
    }
    
    /**
     * Remove conflicting styles for Elementor pages
     */
    private function remove_conflicting_styles() {
        if ( $this->is_elementor_page() ) {
            // Remove specific theme styles that conflict with Elementor
            wp_dequeue_style( 'hello-elementor-theme-style' );
            
            // Allow filtering of additional styles to remove
            $styles_to_remove = apply_filters( 'cdswerx_elementor_conflicting_styles', [] );
            foreach ( $styles_to_remove as $handle ) {
                wp_dequeue_style( $handle );
            }
        }
    }
    
    /**
     * Determine if Bricons should be loaded
     */
    private function should_load_bricons() {
        // Check for Bricons usage in content, widgets, or admin setting
        return apply_filters( 'cdswerx_load_bricons', $this->detect_icon_usage( 'bricons' ) );
    }
    
    /**
     * Determine if Carbonguicon should be loaded
     */
    private function should_load_carbonguicon() {
        // Check for Carbonguicon usage in content, widgets, or admin setting
        return apply_filters( 'cdswerx_load_carbonguicon', $this->detect_icon_usage( 'carbonguicon' ) );
    }
    
    /**
     * Detect icon library usage in content
     */
    private function detect_icon_usage( $icon_library ) {
        global $post;
        
        if ( ! $post ) {
            return false;
        }
        
        // Check post content for icon classes
        $content = $post->post_content;
        $patterns = [
            'bricons' => '/class=["\'][^"\']*br-icon[^"\']*["\']/',
            'carbonguicon' => '/class=["\'][^"\']*cg-[^"\']*["\']/',
        ];
        
        if ( isset( $patterns[ $icon_library ] ) ) {
            return preg_match( $patterns[ $icon_library ], $content );
        }
        
        return false;
    }
    
    /**
     * Get custom CSS from plugin settings or theme customizer
     */
    private function get_custom_css() {
        $custom_css = '';
        
        // From CDSWerx plugin settings
        if ( function_exists( 'cdswerx_get_option' ) ) {
            $plugin_css = cdswerx_get_option( 'custom_css', '' );
            if ( ! empty( $plugin_css ) ) {
                $custom_css .= $plugin_css . "\n";
            }
        }
        
        // From theme customizer
        $customizer_css = get_theme_mod( 'cdswerx_custom_css', '' );
        if ( ! empty( $customizer_css ) ) {
            $custom_css .= $customizer_css . "\n";
        }
        
        // Allow filtering
        return apply_filters( 'cdswerx_custom_css', trim( $custom_css ) );
    }
}

/**
 * CDSWerx Plugin Integration
 *
 * Register this child theme with the CDSWerx plugin ecosystem
 */
function cdswerx_child_plugin_integration() {
    if ( function_exists( 'CDSWerx_Theme_Manager' ) ) {
        // Register child theme with CDSWerx plugin
        do_action( 'cdswerx_child_theme_ready', 'cdswerx-theme-child', CDSWERX_CHILD_VERSION );
    }
}
add_action( 'init', 'cdswerx_child_plugin_integration', 15 );

/**
 * Theme Customizer Enhancements
 * 
 * Add child theme specific customizer options
 */
function cdswerx_child_customize_register( $wp_customize ) {
    // CDSWerx Child Theme Section
    $wp_customize->add_section( 'cdswerx_child_options', array(
        'title' => __( 'CDSWerx Child Options', 'cdswerx-theme-child' ),
        'priority' => 35,
        'description' => __( 'Options specific to CDSWerx Child Theme', 'cdswerx-theme-child' )
    ) );
    
    // Asset Loading Preference (placeholder for TE-5 enhancement)
    $wp_customize->add_setting( 'cdswerx_asset_loading', array(
        'default' => 'auto',
        'sanitize_callback' => 'sanitize_text_field'
    ) );
    
    $wp_customize->add_control( 'cdswerx_asset_loading', array(
        'label' => __( 'Asset Loading Method', 'cdswerx-theme-child' ),
        'section' => 'cdswerx_child_options',
        'type' => 'select',
        'choices' => array(
            'auto' => __( 'Automatic (Recommended)', 'cdswerx-theme-child' ),
            'manual' => __( 'Manual Control', 'cdswerx-theme-child' )
        )
    ) );
}
add_action( 'customize_register', 'cdswerx_child_customize_register' );

/**
 * Helper function to check if CDSWerx plugin is active
 */
function cdswerx_child_plugin_active() {
    return function_exists( 'CDSWerx_Theme_Manager' );
}

/**
 * Load CDSWerx Typography Fallback System - Child Theme
 */
if ( file_exists( get_stylesheet_directory() . '/includes/typography-fallback.php' ) ) {
    require_once get_stylesheet_directory() . '/includes/typography-fallback.php';
}
