<?php
/**
 * CDSWerx Typography Manager
 *
 * Typography system management and font coordination
 * Consolidates typography functionality from multiple classes
 *
 * @package CDSWerx
 * @subpackage Core
 * @since 2.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CDSWerx Typography Manager Class
 * 
 * Handles typography system, font loading, and text styling coordination
 * Consolidates functionality from typography-related classes
 * 
 * @since 2.0.0
 */
class CDSWerx_Typography_Manager {
    
    /**
     * The plugin name
     * 
     * @since 2.0.0
     * @access private
     * @var string $plugin_name
     */
    private $plugin_name;
    
    /**
     * The plugin version
     * 
     * @since 2.0.0
     * @access private
     * @var string $version
     */
    private $version;
    
    /**
     * The loader instance
     * 
     * @since 2.0.0
     * @access private
     * @var Cdswerx_Loader $loader
     */
    private $loader;
    
    /**
     * Typography settings
     * 
     * @since 2.0.0
     * @access private
     * @var array $typography_settings
     */
    private $typography_settings = array();
    
    /**
     * Font configurations
     * 
     * @since 2.0.0
     * @access private
     * @var array $font_configs
     */
    private $font_configs = array();
    
    /**
     * Typography fallbacks
     * 
     * @since 2.0.0
     * @access private
     * @var array $fallbacks
     */
    private $fallbacks = array();
    
    /**
     * Initialize typography manager
     * 
     * @since 2.0.0
     * @param string $plugin_name The plugin name
     * @param string $version The plugin version
     * @param Cdswerx_Loader $loader The loader instance
     */
    public function __construct($plugin_name, $version, $loader) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->loader = $loader;
        
        // Initialize settings
        $this->init_typography_settings();
    }
    
    /**
     * Register typography hooks
     * 
     * @since 2.0.0
     * @param Cdswerx_Loader $loader The loader instance
     */
    public function register_hooks($loader) {
        // Font loading
        $loader->add_action('wp_enqueue_scripts', $this, 'enqueue_fonts');
        $loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_fonts');
        
        // Typography CSS
        $loader->add_action('wp_head', $this, 'inject_typography_css', 5);
        $loader->add_action('admin_head', $this, 'inject_admin_typography_css', 5);
        
        // Theme coordination
        $loader->add_action('after_setup_theme', $this, 'coordinate_theme_typography');
        
        // Elementor integration
        $loader->add_action('elementor/fonts/groups_registered', $this, 'register_elementor_fonts');
        
        // Settings management
        $loader->add_action('init', $this, 'load_typography_settings');
        
        // AJAX handlers
        $loader->add_action('wp_ajax_cdswerx_save_typography', $this, 'ajax_save_typography');
        $loader->add_action('wp_ajax_cdswerx_reset_typography', $this, 'ajax_reset_typography');
    }
    
    /**
     * Initialize typography settings
     * 
     * @since 2.0.0
     */
    private function init_typography_settings() {
        $this->typography_settings = array(
            'body_font' => array(
                'family' => 'System Default',
                'variant' => 'regular',
                'size' => '16px',
                'line_height' => '1.6'
            ),
            'heading_font' => array(
                'family' => 'System Default',
                'variant' => '600',
                'size' => '32px',
                'line_height' => '1.2'
            ),
            'button_font' => array(
                'family' => 'System Default',
                'variant' => '500',
                'size' => '14px',
                'line_height' => '1.4'
            ),
            'navigation_font' => array(
                'family' => 'System Default',
                'variant' => '400',
                'size' => '16px',
                'line_height' => '1.5'
            )
        );
        
        $this->font_configs = array(
            'google_fonts' => array(
                'enabled' => true,
                'display' => 'swap',
                'subset' => 'latin'
            ),
            'local_fonts' => array(
                'enabled' => true,
                'preload' => true
            ),
            'font_optimization' => array(
                'preload_critical' => true,
                'async_load' => false,
                'font_display' => 'swap'
            )
        );
        
        $this->fallbacks = array(
            'system_stack' => '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
            'serif_stack' => 'Georgia, "Times New Roman", Times, serif',
            'mono_stack' => 'Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace'
        );
    }
    
    /**
     * Load typography settings from database
     * 
     * @since 2.0.0
     */
    public function load_typography_settings() {
        $stored_settings = get_option('cdswerx_typography_settings', array());
        $stored_configs = get_option('cdswerx_font_configs', array());
        
        $this->typography_settings = wp_parse_args($stored_settings, $this->typography_settings);
        $this->font_configs = wp_parse_args($stored_configs, $this->font_configs);
    }
    
    /**
     * Enqueue fonts for frontend
     * 
     * @since 2.0.0
     */
    public function enqueue_fonts() {
        // Google Fonts
        if ($this->font_configs['google_fonts']['enabled']) {
            $this->enqueue_google_fonts();
        }
        
        // Local fonts
        if ($this->font_configs['local_fonts']['enabled']) {
            $this->enqueue_local_fonts();
        }
        
        // Typography CSS
        $this->enqueue_typography_css();
    }
    
    /**
     * Enqueue fonts for admin
     * 
     * @since 2.0.0
     */
    public function enqueue_admin_fonts() {
        // Admin-specific font loading
        if ($this->should_load_admin_fonts()) {
            $this->enqueue_google_fonts();
            $this->enqueue_local_fonts();
        }
    }
    
    /**
     * Enqueue Google Fonts
     * 
     * @since 2.0.0
     */
    private function enqueue_google_fonts() {
        $google_fonts = $this->get_google_fonts_to_load();
        
        if (empty($google_fonts)) {
            return;
        }
        
        // Build Google Fonts URL
        $font_url = $this->build_google_fonts_url($google_fonts);
        
        if ($font_url) {
            wp_enqueue_style(
                'cdswerx-google-fonts',
                $font_url,
                array(),
                $this->version
            );
            
            // Preconnect for performance
            add_action('wp_head', function() {
                echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
                echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
            }, 1);
        }
    }
    
    /**
     * Enqueue local fonts
     * 
     * @since 2.0.0
     */
    private function enqueue_local_fonts() {
        wp_enqueue_style(
            'cdswerx-local-fonts',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/local-fonts.css',
            array(),
            $this->version
        );
        
        // Preload critical fonts if enabled
        if ($this->font_configs['font_optimization']['preload_critical']) {
            add_action('wp_head', array($this, 'preload_critical_fonts'), 1);
        }
    }
    
    /**
     * Enqueue typography CSS
     * 
     * @since 2.0.0
     */
    private function enqueue_typography_css() {
        wp_enqueue_style(
            'cdswerx-typography',
            false,
            array(),
            $this->version
        );
        
        wp_add_inline_style('cdswerx-typography', $this->generate_typography_css());
    }
    
    /**
     * Generate typography CSS
     * 
     * @since 2.0.0
     * @return string Typography CSS
     */
    private function generate_typography_css() {
        $css = '';
        
        // Body typography
        $body_font = $this->typography_settings['body_font'];
        $css .= 'body { ';
        $css .= 'font-family: ' . $this->build_font_family($body_font['family']) . '; ';
        $css .= 'font-weight: ' . $this->parse_font_weight($body_font['variant']) . '; ';
        $css .= 'font-size: ' . $body_font['size'] . '; ';
        $css .= 'line-height: ' . $body_font['line_height'] . '; ';
        $css .= '}' . "\n";
        
        // Heading typography
        $heading_font = $this->typography_settings['heading_font'];
        $css .= 'h1, h2, h3, h4, h5, h6 { ';
        $css .= 'font-family: ' . $this->build_font_family($heading_font['family']) . '; ';
        $css .= 'font-weight: ' . $this->parse_font_weight($heading_font['variant']) . '; ';
        $css .= 'line-height: ' . $heading_font['line_height'] . '; ';
        $css .= '}' . "\n";
        
        // Button typography
        $button_font = $this->typography_settings['button_font'];
        $css .= 'button, .button, .btn, input[type="submit"] { ';
        $css .= 'font-family: ' . $this->build_font_family($button_font['family']) . '; ';
        $css .= 'font-weight: ' . $this->parse_font_weight($button_font['variant']) . '; ';
        $css .= 'font-size: ' . $button_font['size'] . '; ';
        $css .= 'line-height: ' . $button_font['line_height'] . '; ';
        $css .= '}' . "\n";
        
        // Navigation typography
        $nav_font = $this->typography_settings['navigation_font'];
        $css .= 'nav, .navigation, .menu { ';
        $css .= 'font-family: ' . $this->build_font_family($nav_font['family']) . '; ';
        $css .= 'font-weight: ' . $this->parse_font_weight($nav_font['variant']) . '; ';
        $css .= 'font-size: ' . $nav_font['size'] . '; ';
        $css .= 'line-height: ' . $nav_font['line_height'] . '; ';
        $css .= '}' . "\n";
        
        return $css;
    }
    
    /**
     * Inject typography CSS into head
     * 
     * @since 2.0.0
     */
    public function inject_typography_css() {
        echo '<style id="cdswerx-typography-vars">' . "\n";
        echo $this->generate_css_variables();
        echo '</style>' . "\n";
    }
    
    /**
     * Inject admin typography CSS
     * 
     * @since 2.0.0
     */
    public function inject_admin_typography_css() {
        if ($this->should_load_admin_fonts()) {
            echo '<style id="cdswerx-admin-typography">' . "\n";
            echo $this->generate_admin_typography_css();
            echo '</style>' . "\n";
        }
    }
    
    /**
     * Generate CSS variables for typography
     * 
     * @since 2.0.0
     * @return string CSS variables
     */
    private function generate_css_variables() {
        $css = ':root {' . "\n";
        
        foreach ($this->typography_settings as $type => $font) {
            $css .= '--cdswerx-' . str_replace('_', '-', $type) . '-family: ' . $this->build_font_family($font['family']) . ';' . "\n";
            $css .= '--cdswerx-' . str_replace('_', '-', $type) . '-weight: ' . $this->parse_font_weight($font['variant']) . ';' . "\n";
            $css .= '--cdswerx-' . str_replace('_', '-', $type) . '-size: ' . $font['size'] . ';' . "\n";
            $css .= '--cdswerx-' . str_replace('_', '-', $type) . '-line-height: ' . $font['line_height'] . ';' . "\n";
        }
        
        $css .= '}' . "\n";
        
        return $css;
    }
    
    /**
     * Generate admin typography CSS
     * 
     * @since 2.0.0
     * @return string Admin CSS
     */
    private function generate_admin_typography_css() {
        // Admin-specific typography adjustments
        return '.cdswerx-admin { font-family: ' . $this->build_font_family($this->typography_settings['body_font']['family']) . '; }';
    }
    
    /**
     * Coordinate theme typography
     * 
     * @since 2.0.0
     */
    public function coordinate_theme_typography() {
        $current_theme = get_template();
        
        // Theme-specific coordination
        switch ($current_theme) {
            case 'hello-elementor':
                $this->coordinate_hello_elementor_typography();
                break;
            case 'cdswerx-theme':
                $this->coordinate_cdswerx_theme_typography();
                break;
        }
    }
    
    /**
     * Coordinate Hello Elementor typography
     * 
     * @since 2.0.0
     */
    private function coordinate_hello_elementor_typography() {
        // Override Hello Elementor's default typography
        add_action('wp_enqueue_scripts', function() {
            wp_dequeue_style('hello-elementor-theme-style');
        }, 20);
    }
    
    /**
     * Coordinate CDSWerx theme typography
     * 
     * @since 2.0.0
     */
    private function coordinate_cdswerx_theme_typography() {
        // Native integration with CDSWerx theme
        do_action('cdswerx_theme_typography_coordination', $this->typography_settings);
    }
    
    /**
     * Register Elementor fonts
     * 
     * @since 2.0.0
     */
    public function register_elementor_fonts($font_groups) {
        // Add custom font group for CDSWerx fonts
        $font_groups['cdswerx'] = __('CDSWerx Fonts', 'cdswerx');
    }
    
    /**
     * Get Google Fonts to load
     * 
     * @since 2.0.0
     * @return array Google Fonts
     */
    private function get_google_fonts_to_load() {
        $fonts = array();
        
        foreach ($this->typography_settings as $font_setting) {
            if ($this->is_google_font($font_setting['family'])) {
                $fonts[$font_setting['family']][] = $font_setting['variant'];
            }
        }
        
        return $fonts;
    }
    
    /**
     * Check if font is a Google Font
     * 
     * @since 2.0.0
     * @param string $font_family Font family name
     * @return bool Google Font status
     */
    private function is_google_font($font_family) {
        // Simple check - exclude system fonts
        $system_fonts = array('System Default', 'Arial', 'Helvetica', 'Georgia', 'Times', 'Courier');
        return !in_array($font_family, $system_fonts);
    }
    
    /**
     * Build Google Fonts URL
     * 
     * @since 2.0.0
     * @param array $fonts Fonts to load
     * @return string Google Fonts URL
     */
    private function build_google_fonts_url($fonts) {
        if (empty($fonts)) {
            return '';
        }
        
        $families = array();
        foreach ($fonts as $family => $variants) {
            $variants = array_unique($variants);
            $families[] = urlencode($family) . ':' . implode(',', $variants);
        }
        
        $query_args = array(
            'family' => implode('|', $families),
            'display' => $this->font_configs['google_fonts']['display'],
            'subset' => $this->font_configs['google_fonts']['subset']
        );
        
        return 'https://fonts.googleapis.com/css?' . http_build_query($query_args);
    }
    
    /**
     * Build font family with fallbacks
     * 
     * @since 2.0.0
     * @param string $font_family Primary font family
     * @return string Complete font family stack
     */
    private function build_font_family($font_family) {
        if ($font_family === 'System Default') {
            return $this->fallbacks['system_stack'];
        }
        
        // Add quotes if needed
        if (strpos($font_family, ' ') !== false) {
            $font_family = '"' . $font_family . '"';
        }
        
        // Add fallback stack
        return $font_family . ', ' . $this->fallbacks['system_stack'];
    }
    
    /**
     * Parse font weight from variant
     * 
     * @since 2.0.0
     * @param string $variant Font variant
     * @return string Font weight
     */
    private function parse_font_weight($variant) {
        if (is_numeric($variant)) {
            return $variant;
        }
        
        $weights = array(
            'regular' => '400',
            'italic' => '400',
            'bold' => '700',
            'bolditalic' => '700'
        );
        
        return $weights[$variant] ?? '400';
    }
    
    /**
     * Preload critical fonts
     * 
     * @since 2.0.0
     */
    public function preload_critical_fonts() {
        $critical_fonts = array(
            plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/fonts/primary.woff2',
        );
        
        foreach ($critical_fonts as $font_url) {
            echo '<link rel="preload" href="' . esc_url($font_url) . '" as="font" type="font/woff2" crossorigin>' . "\n";
        }
    }
    
    /**
     * Check if admin fonts should be loaded
     * 
     * @since 2.0.0
     * @return bool Load status
     */
    private function should_load_admin_fonts() {
        return is_admin() && current_user_can('manage_options');
    }
    
    /**
     * AJAX save typography settings
     * 
     * @since 2.0.0
     */
    public function ajax_save_typography() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_typography_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        // Sanitize and save settings
        $settings = $this->sanitize_typography_settings($_POST['typography']);
        $this->typography_settings = $settings;
        update_option('cdswerx_typography_settings', $settings);
        
        wp_send_json_success(__('Typography settings saved successfully', 'cdswerx'));
    }
    
    /**
     * AJAX reset typography settings
     * 
     * @since 2.0.0
     */
    public function ajax_reset_typography() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_typography_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        // Reset to defaults
        delete_option('cdswerx_typography_settings');
        $this->init_typography_settings();
        
        wp_send_json_success(__('Typography settings reset successfully', 'cdswerx'));
    }
    
    /**
     * Sanitize typography settings
     * 
     * @since 2.0.0
     * @param array $settings Raw settings
     * @return array Sanitized settings
     */
    private function sanitize_typography_settings($settings) {
        $sanitized = array();
        
        foreach ($settings as $type => $font) {
            $sanitized[$type] = array(
                'family' => sanitize_text_field($font['family']),
                'variant' => sanitize_text_field($font['variant']),
                'size' => sanitize_text_field($font['size']),
                'line_height' => sanitize_text_field($font['line_height'])
            );
        }
        
        return $sanitized;
    }
    
    /**
     * Get typography settings
     * 
     * @since 2.0.0
     * @return array Typography settings
     */
    public function get_typography_settings() {
        return $this->typography_settings;
    }
    
    /**
     * Get font configurations
     * 
     * @since 2.0.0
     * @return array Font configurations
     */
    public function get_font_configs() {
        return $this->font_configs;
    }
    
    /**
     * Update typography settings
     * 
     * @since 2.0.0
     * @param array $settings Typography settings
     */
    public function update_typography_settings($settings) {
        $this->typography_settings = array_merge($this->typography_settings, $settings);
        update_option('cdswerx_typography_settings', $this->typography_settings);
    }
    
    /**
     * Get plugin name
     * 
     * @since 2.0.0
     * @return string Plugin name
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    /**
     * Get plugin version
     * 
     * @since 2.0.0
     * @return string Plugin version
     */
    public function get_version() {
        return $this->version;
    }
}