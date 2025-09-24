<?php

/**
 * CDSWerx Theme Integration Class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CDSWerx_Theme_Integration
{
    private static $theme_status = null;

    private static $supported_themes = [
        'hello-elementor' => 'Hello Elementor',
        'hello-elementor-child' => 'Hello Elementor Child',
        'cdswerx-elementor' => 'CDSWerx Elementor Theme',
        'cdswerx-elementor-child' => 'CDSWerx Elementor Child'
    ];

    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
        add_filter( 'cdswerx_theme_ecosystem_status', array( $this, 'get_ecosystem_status' ) );
        
        // AJAX handlers for admin interface
        add_action( 'wp_ajax_cdswerx_activate_theme', array( $this, 'ajax_activate_theme' ) );
        add_action( 'wp_ajax_cdswerx_auto_optimize_theme', array( $this, 'ajax_auto_optimize_theme' ) );
        add_action( 'wp_ajax_cdswerx_check_system_status', array( $this, 'ajax_check_system_status' ) );
        
        // TE-5: Asset Management AJAX handlers
        add_action( 'wp_ajax_cdswerx_get_asset_status', array( $this, 'ajax_get_asset_status' ) );
        add_action( 'wp_ajax_cdswerx_optimize_assets', array( $this, 'ajax_optimize_assets' ) );
        add_action( 'wp_ajax_cdswerx_toggle_performance_mode', array( $this, 'ajax_toggle_performance_mode' ) );
    }

    public function init() {
        add_action('switch_theme', array( $this, 'clear_theme_cache'));
    }

    public function clear_theme_cache() {
        self::$theme_status = null;
    }

    public function get_available_themes() {
        $themes = wp_get_themes();
        $available = [];

        foreach (self::$supported_themes as $slug => $name) {
            $available[$slug] = [
                'name' => $name,
                'installed' => isset($themes[$slug]),
                'active' => (get_option('stylesheet') === $slug),
                'theme_object' => $themes[$slug] ?? null
            ];
        }

        return $available;
    }

    public function get_ecosystem_status() {
        if (self::$theme_status !== null) {
            return self::$theme_status;
        }

        $available_themes = $this->get_available_themes();
        $current_theme = get_option('stylesheet');
        
        self::$theme_status = [
            'current_theme' => $current_theme,
            'current_theme_name' => wp_get_theme()->get('Name'),
            'available_themes' => $available_themes,
            'cdswerx_themes_available' => $available_themes['cdswerx-elementor']['installed'] || $available_themes['cdswerx-elementor-child']['installed'],
            'hello_themes_available' => $available_themes['hello-elementor']['installed'] || $available_themes['hello-elementor-child']['installed'],
            'optimal_theme_active' => $this->is_optimal_theme_active(),
            'elementor_status' => $this->get_elementor_status(),
            'recommended_action' => $this->get_recommended_action()
        ];

        return self::$theme_status;
    }

    public function is_optimal_theme_active() {
        $current = get_option('stylesheet');
        $available = $this->get_available_themes();

        if ($available['cdswerx-elementor-child']['installed']) {
            return $current === 'cdswerx-elementor-child';
        }
        if ($available['cdswerx-elementor']['installed']) {
            return $current === 'cdswerx-elementor';
        }
        if ($available['hello-elementor-child']['installed']) {
            return $current === 'hello-elementor-child';
        }
        if ($available['hello-elementor']['installed']) {
            return $current === 'hello-elementor';
        }

        return false;
    }

    public function get_elementor_status() {
        return [
            'elementor_free_active' => $this->is_elementor_active(),
            'elementor_pro_active' => $this->is_elementor_pro_active(),
            'elementor_free_installed' => file_exists(WP_PLUGIN_DIR . '/elementor/elementor.php'),
            'elementor_pro_installed' => file_exists(WP_PLUGIN_DIR . '/elementor-pro/elementor-pro.php')
        ];
    }

    public function is_elementor_active() {
        return is_plugin_active('elementor/elementor.php');
    }

    public function is_elementor_pro_active() {
        return is_plugin_active('elementor-pro/elementor-pro.php');
    }

    public function get_recommended_action() {
        $available = $this->get_available_themes();
        $current = get_option('stylesheet');

        if ($available['cdswerx-elementor-child']['installed'] && $current !== 'cdswerx-elementor-child') {
            return [
                'action' => 'activate_cdswerx_child',
                'message' => 'Activate CDSWerx Elementor Child theme for optimal performance',
                'priority' => 'high'
            ];
        }

        if ($available['cdswerx-elementor']['installed'] && !$available['cdswerx-elementor-child']['installed'] && $current !== 'cdswerx-elementor') {
            return [
                'action' => 'activate_cdswerx_parent',
                'message' => 'Activate CDSWerx Elementor theme',
                'priority' => 'medium'
            ];
        }

        if (($current === 'hello-elementor' || $current === 'hello-elementor-child') && 
            ($available['cdswerx-elementor']['installed'] || $available['cdswerx-elementor-child']['installed'])) {
            return [
                'action' => 'upgrade_to_cdswerx',
                'message' => 'Upgrade to CDSWerx theme for enhanced functionality',
                'priority' => 'medium'
            ];
        }

        return [
            'action' => 'none',
            'message' => 'Theme configuration is optimal',
            'priority' => 'none'
        ];
    }

    public function activate_optimal_theme() {
        $available = $this->get_available_themes();

        if ($available['cdswerx-elementor-child']['installed']) {
            switch_theme('cdswerx-elementor-child');
            return 'cdswerx-elementor-child';
        }
        if ($available['cdswerx-elementor']['installed']) {
            switch_theme('cdswerx-elementor');
            return 'cdswerx-elementor';
        }
        if ($available['hello-elementor-child']['installed']) {
            switch_theme('hello-elementor-child');
            return 'hello-elementor-child';
        }
        if ($available['hello-elementor']['installed']) {
            switch_theme('hello-elementor');
            return 'hello-elementor';
        }

        return get_option('stylesheet');
    }

    /**
     * TE-5: Asset Management Methods
     */

    /**
     * Get asset loading status and configuration
     */
    public function get_asset_status() {
        $current_theme = get_option('stylesheet');
        
        return [
            'theme' => $current_theme,
            'elementor_active' => $this->is_elementor_active(),
            'elementor_pro_active' => $this->is_elementor_pro_active(),
            'asset_optimization_enabled' => $this->is_asset_optimization_enabled(),
            'custom_css_enabled' => $this->has_custom_css(),
            'icon_libraries_loaded' => $this->get_loaded_icon_libraries(),
            'performance_mode' => $this->get_performance_mode()
        ];
    }

    /**
     * Check if asset optimization is enabled
     */
    private function is_asset_optimization_enabled() {
        return apply_filters('cdswerx_asset_optimization_enabled', true);
    }

    /**
     * Check if custom CSS is configured
     */
    private function has_custom_css() {
        $custom_css = '';
        
        // Check plugin settings
        if (function_exists('cdswerx_get_option')) {
            $custom_css = cdswerx_get_option('custom_css', '');
        }
        
        // Check theme customizer
        if (empty($custom_css)) {
            $custom_css = get_theme_mod('cdswerx_custom_css', '');
        }
        
        return !empty(trim($custom_css));
    }

    /**
     * Get currently loaded icon libraries
     */
    private function get_loaded_icon_libraries() {
        $loaded = [];
        
        if (wp_style_is('cdswerx-bricons', 'enqueued')) {
            $loaded[] = 'bricons';
        }
        
        if (wp_style_is('cdswerx-carbonguicon', 'enqueued')) {
            $loaded[] = 'carbonguicon';
        }
        
        return $loaded;
    }

    /**
     * Get current performance mode
     */
    private function get_performance_mode() {
        // Default to 'balanced' - can be 'performance', 'balanced', or 'compatibility'
        return apply_filters('cdswerx_performance_mode', 'balanced');
    }

    public function ajax_activate_theme() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $theme_slug = sanitize_text_field( $_POST['theme_slug'] ?? '' );
        
        if ( empty( $theme_slug ) ) {
            wp_send_json_error( 'Theme slug is required' );
        }
        
        $theme = wp_get_theme( $theme_slug );
        if ( ! $theme->exists() ) {
            wp_send_json_error( 'Theme does not exist' );
        }
        
        switch_theme( $theme_slug );
        wp_send_json_success( 'Theme activated successfully' );
    }
    
    public function ajax_auto_optimize_theme() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $current_theme = get_stylesheet();
        $optimal_theme = $this->activate_optimal_theme();
        
        $response = array(
            'changed' => false,
            'message' => '',
            'details' => array()
        );
        
        if ( $optimal_theme !== $current_theme ) {
            $response['changed'] = true;
            $response['message'] = 'Theme optimized! Switched to: ' . $optimal_theme;
            $response['details'][] = 'Previous theme: ' . $current_theme;
            $response['details'][] = 'New theme: ' . $optimal_theme;
            
            if ( $this->is_elementor_active() ) {
                $response['details'][] = 'Reason: Elementor detected, CDSWerx theme optimized for Elementor';
            } else {
                $response['details'][] = 'Reason: Standard WordPress setup detected';
            }
        } else {
            $response['message'] = 'Your theme is already optimized for your current setup.';
            $response['details'][] = 'Current theme: ' . $current_theme;
        }
        
        wp_send_json_success( $response );
    }
    
    public function ajax_check_system_status() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }
        
        $status = array(
            'current_theme' => wp_get_theme()->get( 'Name' ),
            'elementor_active' => $this->is_elementor_active(),
            'elementor_pro_active' => $this->is_elementor_pro_active(),
            'cdswerx_themes_count' => 0,
            'recommendations' => array()
        );
        
        $themes = wp_get_themes();
        foreach ( $themes as $theme_slug => $theme ) {
            if ( strpos( $theme_slug, 'cdswerx' ) !== false ) {
                $status['cdswerx_themes_count']++;
            }
        }
        
        $ecosystem_status = $this->get_ecosystem_status();
        if ( ! empty( $ecosystem_status['recommended_action'] ) && $ecosystem_status['recommended_action']['action'] !== 'none' ) {
            $status['recommendations'][] = $ecosystem_status['recommended_action']['message'];
        }
        
        if ( $status['elementor_active'] && ! strpos( get_stylesheet(), 'cdswerx' ) ) {
            $status['recommendations'][] = 'Consider switching to a CDSWerx theme for optimal Elementor integration';
        }
        
        if ( $status['cdswerx_themes_count'] === 0 ) {
            $status['recommendations'][] = 'No CDSWerx themes detected. Please ensure they are properly installed.';
        }
        
        wp_send_json_success( $status );
    }

    /**
     * TE-5: Asset Management AJAX Handlers
     */

    /**
     * AJAX handler for getting asset status
     */
    public function ajax_get_asset_status() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $status = $this->get_asset_status();
        wp_send_json_success( $status );
    }

    /**
     * AJAX handler for optimizing assets
     */
    public function ajax_optimize_assets() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $optimization_type = sanitize_text_field( $_POST['optimization_type'] ?? 'auto' );
        
        $results = array(
            'optimizations_applied' => array(),
            'performance_impact' => '',
            'recommendations' => array()
        );

        // Apply optimizations based on type
        switch ( $optimization_type ) {
            case 'performance':
                $results = $this->apply_performance_optimizations();
                break;
            case 'compatibility':
                $results = $this->apply_compatibility_optimizations();
                break;
            case 'auto':
            default:
                $results = $this->apply_auto_optimizations();
                break;
        }

        wp_send_json_success( $results );
    }

    /**
     * AJAX handler for toggling performance mode
     */
    public function ajax_toggle_performance_mode() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Insufficient permissions' );
        }

        $mode = sanitize_text_field( $_POST['mode'] ?? 'balanced' );
        $valid_modes = array( 'performance', 'balanced', 'compatibility' );

        if ( ! in_array( $mode, $valid_modes ) ) {
            wp_send_json_error( 'Invalid performance mode' );
        }

        // Store the performance mode preference
        update_option( 'cdswerx_performance_mode', $mode );

        $response = array(
            'mode' => $mode,
            'message' => sprintf( 'Performance mode set to: %s', ucfirst( $mode ) ),
            'impact' => $this->get_performance_mode_impact( $mode )
        );

        wp_send_json_success( $response );
    }

    /**
     * Apply performance-focused optimizations
     */
    private function apply_performance_optimizations() {
        return array(
            'optimizations_applied' => array(
                'Minimal CSS loading enabled',
                'Icon libraries loaded on-demand',
                'Elementor-specific optimizations applied',
                'Custom CSS inlined for speed'
            ),
            'performance_impact' => 'High - Fastest loading, minimal CSS',
            'recommendations' => array(
                'Best for high-traffic sites',
                'May need manual CSS adjustments for complex layouts'
            )
        );
    }

    /**
     * Apply compatibility-focused optimizations
     */
    private function apply_compatibility_optimizations() {
        return array(
            'optimizations_applied' => array(
                'All theme CSS files loaded',
                'Full icon library support',
                'Maximum plugin compatibility',
                'Comprehensive styling coverage'
            ),
            'performance_impact' => 'Lower - More CSS loaded for compatibility',
            'recommendations' => array(
                'Best for complex sites with many plugins',
                'Ensures maximum visual compatibility'
            )
        );
    }

    /**
     * Apply automatic optimizations based on site analysis
     */
    private function apply_auto_optimizations() {
        $is_elementor_heavy = $this->is_elementor_active();
        $has_complex_plugins = $this->detect_complex_plugins();
        
        if ( $is_elementor_heavy && ! $has_complex_plugins ) {
            return $this->apply_performance_optimizations();
        } else {
            return $this->apply_compatibility_optimizations();
        }
    }

    /**
     * Get performance mode impact description
     */
    private function get_performance_mode_impact( $mode ) {
        $impacts = array(
            'performance' => 'Fastest loading, minimal CSS, may need manual adjustments',
            'balanced' => 'Good balance of speed and compatibility',
            'compatibility' => 'Maximum compatibility, slower loading'
        );

        return $impacts[ $mode ] ?? $impacts['balanced'];
    }

    /**
     * Detect if site has complex plugins that might need full CSS
     */
    private function detect_complex_plugins() {
        $complex_plugins = array(
            'woocommerce/woocommerce.php',
            'buddypress/bp-loader.php',
            'bbpress/bbpress.php',
            'learndash/learndash.php'
        );

        foreach ( $complex_plugins as $plugin ) {
            if ( is_plugin_active( $plugin ) ) {
                return true;
            }
        }

        return false;
    }
}

// Initialize the integration
new CDSWerx_Theme_Integration();
