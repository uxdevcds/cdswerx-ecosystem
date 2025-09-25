<?php
/**
 * CDSWerx Code Manager
 *
 * Custom CSS and JavaScript management
 * Consolidates code injection functionality from multiple classes
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
 * CDSWerx Code Manager Class
 * 
 * Handles custom CSS, JavaScript injection, and code management
 * Consolidates functionality from advanced CSS and custom code classes
 * 
 * @since 2.0.0
 */
class CDSWerx_Code_Manager {
    
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
     * Advanced CSS settings
     * 
     * @since 2.0.0
     * @access private
     * @var array $css_settings
     */
    private $css_settings = array();
    
    /**
     * Custom JavaScript settings
     * 
     * @since 2.0.0
     * @access private
     * @var array $js_settings
     */
    private $js_settings = array();
    
    /**
     * Code injection points
     * 
     * @since 2.0.0
     * @access private
     * @var array $injection_points
     */
    private $injection_points = array();
    
    /**
     * Initialize code manager
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
        $this->init_settings();
    }
    
    /**
     * Register code management hooks
     * 
     * @since 2.0.0
     * @param Cdswerx_Loader $loader The loader instance
     */
    public function register_hooks($loader) {
        // CSS injection
        $loader->add_action('wp_enqueue_scripts', $this, 'enqueue_custom_styles');
        $loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_styles');
        
        // JavaScript injection
        $loader->add_action('wp_enqueue_scripts', $this, 'enqueue_custom_scripts');
        $loader->add_action('admin_enqueue_scripts', $this, 'enqueue_admin_scripts');
        
        // Code injection points
        $loader->add_action('wp_head', $this, 'inject_head_code');
        $loader->add_action('wp_footer', $this, 'inject_footer_code');
        $loader->add_action('admin_head', $this, 'inject_admin_head_code');
        
        // Advanced CSS handling
        $loader->add_action('wp_ajax_cdswerx_save_advanced_css', $this, 'ajax_save_advanced_css');
        $loader->add_action('wp_ajax_cdswerx_preview_css', $this, 'ajax_preview_css');
        
        // Settings management
        $loader->add_action('init', $this, 'load_settings');
    }
    
    /**
     * Initialize default settings
     * 
     * @since 2.0.0
     */
    private function init_settings() {
        $this->css_settings = array(
            'advanced_css' => '',
            'admin_css' => '',
            'elementor_css' => '',
            'minify_css' => false,
            'css_priority' => 10
        );
        
        $this->js_settings = array(
            'custom_js' => '',
            'admin_js' => '',
            'header_js' => '',
            'footer_js' => '',
            'minify_js' => false,
            'js_priority' => 10
        );
        
        $this->injection_points = array(
            'head' => array(),
            'footer' => array(),
            'admin_head' => array()
        );
    }
    
    /**
     * Load settings from database
     * 
     * @since 2.0.0
     */
    public function load_settings() {
        $stored_css = get_option('cdswerx_css_settings', array());
        $stored_js = get_option('cdswerx_js_settings', array());
        
        $this->css_settings = wp_parse_args($stored_css, $this->css_settings);
        $this->js_settings = wp_parse_args($stored_js, $this->js_settings);
        
        // Load custom injection points
        $this->injection_points = get_option('cdswerx_injection_points', $this->injection_points);
    }
    
    /**
     * Enqueue custom styles
     * 
     * @since 2.0.0
     */
    public function enqueue_custom_styles() {
        // Advanced CSS
        if (!empty($this->css_settings['advanced_css'])) {
            $this->inject_inline_css('advanced', $this->css_settings['advanced_css']);
        }
        
        // Elementor-specific CSS
        if (!empty($this->css_settings['elementor_css']) && $this->is_elementor_page()) {
            $this->inject_inline_css('elementor', $this->css_settings['elementor_css']);
        }
        
        // Theme-specific CSS coordination
        $this->coordinate_theme_css();
    }
    
    /**
     * Enqueue admin styles
     * 
     * @since 2.0.0
     */
    public function enqueue_admin_styles() {
        if (!empty($this->css_settings['admin_css'])) {
            $this->inject_inline_css('admin', $this->css_settings['admin_css']);
        }
    }
    
    /**
     * Enqueue custom scripts
     * 
     * @since 2.0.0
     */
    public function enqueue_custom_scripts() {
        // Custom JavaScript
        if (!empty($this->js_settings['custom_js'])) {
            $this->inject_inline_js('custom', $this->js_settings['custom_js']);
        }
        
        // Footer JavaScript
        if (!empty($this->js_settings['footer_js'])) {
            $this->inject_inline_js('footer', $this->js_settings['footer_js'], true);
        }
    }
    
    /**
     * Enqueue admin scripts
     * 
     * @since 2.0.0
     */
    public function enqueue_admin_scripts() {
        if (!empty($this->js_settings['admin_js'])) {
            $this->inject_inline_js('admin', $this->js_settings['admin_js']);
        }
    }
    
    /**
     * Inject inline CSS
     * 
     * @since 2.0.0
     * @param string $handle CSS handle
     * @param string $css CSS code
     */
    private function inject_inline_css($handle, $css) {
        // Sanitize CSS
        $css = $this->sanitize_css($css);
        
        // Minify if enabled
        if ($this->css_settings['minify_css']) {
            $css = $this->minify_css($css);
        }
        
        // Generate unique handle
        $full_handle = $this->plugin_name . '-' . $handle . '-css';
        
        // Register and enqueue
        wp_register_style($full_handle, false);
        wp_enqueue_style($full_handle);
        wp_add_inline_style($full_handle, $css);
    }
    
    /**
     * Inject inline JavaScript
     * 
     * @since 2.0.0
     * @param string $handle JS handle
     * @param string $js JavaScript code
     * @param bool $in_footer Whether to load in footer
     */
    private function inject_inline_js($handle, $js, $in_footer = false) {
        // Sanitize JavaScript
        $js = $this->sanitize_js($js);
        
        // Minify if enabled
        if ($this->js_settings['minify_js']) {
            $js = $this->minify_js($js);
        }
        
        // Generate unique handle
        $full_handle = $this->plugin_name . '-' . $handle . '-js';
        
        // Register and enqueue
        wp_register_script($full_handle, false, array(), $this->version, $in_footer);
        wp_enqueue_script($full_handle);
        wp_add_inline_script($full_handle, $js);
    }
    
    /**
     * Inject head code
     * 
     * @since 2.0.0
     */
    public function inject_head_code() {
        // Header JavaScript
        if (!empty($this->js_settings['header_js'])) {
            echo '<script type="text/javascript">' . "\n";
            echo $this->sanitize_js($this->js_settings['header_js']);
            echo "\n" . '</script>' . "\n";
        }
        
        // Custom head injection points
        if (!empty($this->injection_points['head'])) {
            foreach ($this->injection_points['head'] as $code) {
                echo $this->sanitize_head_code($code) . "\n";
            }
        }
    }
    
    /**
     * Inject footer code
     * 
     * @since 2.0.0
     */
    public function inject_footer_code() {
        // Custom footer injection points
        if (!empty($this->injection_points['footer'])) {
            foreach ($this->injection_points['footer'] as $code) {
                echo $this->sanitize_footer_code($code) . "\n";
            }
        }
    }
    
    /**
     * Inject admin head code
     * 
     * @since 2.0.0
     */
    public function inject_admin_head_code() {
        // Custom admin head injection points
        if (!empty($this->injection_points['admin_head'])) {
            foreach ($this->injection_points['admin_head'] as $code) {
                echo $this->sanitize_head_code($code) . "\n";
            }
        }
    }
    
    /**
     * Coordinate theme CSS
     * 
     * @since 2.0.0
     */
    private function coordinate_theme_css() {
        $current_theme = get_template();
        
        // Theme-specific CSS coordination
        switch ($current_theme) {
            case 'hello-elementor':
                $this->coordinate_hello_elementor_css();
                break;
            case 'cdswerx-theme':
                $this->coordinate_cdswerx_theme_css();
                break;
        }
    }
    
    /**
     * Coordinate Hello Elementor CSS
     * 
     * @since 2.0.0
     */
    private function coordinate_hello_elementor_css() {
        // Hello Elementor specific CSS coordination
        if (!empty($this->css_settings['advanced_css'])) {
            // Ensure proper CSS priority
            add_action('wp_enqueue_scripts', function() {
                wp_enqueue_style('hello-elementor');
            }, 5);
        }
    }
    
    /**
     * Coordinate CDSWerx theme CSS
     * 
     * @since 2.0.0
     */
    private function coordinate_cdswerx_theme_css() {
        // CDSWerx theme native integration
        do_action('cdswerx_theme_css_coordination', $this->css_settings);
    }
    
    /**
     * Check if current page uses Elementor
     * 
     * @since 2.0.0
     * @return bool Elementor status
     */
    private function is_elementor_page() {
        if (!class_exists('\Elementor\Plugin')) {
            return false;
        }
        
        global $post;
        if (!$post) {
            return false;
        }
        
        return \Elementor\Plugin::$instance->documents->get($post->ID)->is_built_with_elementor();
    }
    
    /**
     * AJAX save advanced CSS
     * 
     * @since 2.0.0
     */
    public function ajax_save_advanced_css() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_css_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        // Sanitize and save CSS
        $css = $this->sanitize_css($_POST['css']);
        $this->css_settings['advanced_css'] = $css;
        update_option('cdswerx_css_settings', $this->css_settings);
        
        // Trigger version update
        do_action('cdswerx_advanced_css_updated', $this->version);
        
        wp_send_json_success(__('Advanced CSS saved successfully', 'cdswerx'));
    }
    
    /**
     * AJAX preview CSS
     * 
     * @since 2.0.0
     */
    public function ajax_preview_css() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_css_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        // Return sanitized CSS for preview
        $css = $this->sanitize_css($_POST['css']);
        wp_send_json_success(array('css' => $css));
    }
    
    /**
     * Sanitize CSS code
     * 
     * @since 2.0.0
     * @param string $css Raw CSS
     * @return string Sanitized CSS
     */
    private function sanitize_css($css) {
        // Remove potentially dangerous CSS
        $css = preg_replace('/javascript:/i', '', $css);
        $css = preg_replace('/@import/i', '', $css);
        $css = preg_replace('/expression\s*\(/i', '', $css);
        
        return wp_strip_all_tags($css);
    }
    
    /**
     * Sanitize JavaScript code
     * 
     * @since 2.0.0
     * @param string $js Raw JavaScript
     * @return string Sanitized JavaScript
     */
    private function sanitize_js($js) {
        // Basic XSS prevention
        $js = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $js);
        
        return $js;
    }
    
    /**
     * Sanitize head code
     * 
     * @since 2.0.0
     * @param string $code Raw HTML code
     * @return string Sanitized code
     */
    private function sanitize_head_code($code) {
        // Allow specific tags for head section
        $allowed_tags = array(
            'script' => array('type' => array(), 'src' => array()),
            'style' => array('type' => array()),
            'link' => array('rel' => array(), 'href' => array(), 'type' => array()),
            'meta' => array('name' => array(), 'content' => array(), 'property' => array())
        );
        
        return wp_kses($code, $allowed_tags);
    }
    
    /**
     * Sanitize footer code
     * 
     * @since 2.0.0
     * @param string $code Raw HTML code
     * @return string Sanitized code
     */
    private function sanitize_footer_code($code) {
        // Allow specific tags for footer section
        $allowed_tags = array(
            'script' => array('type' => array(), 'src' => array()),
            'noscript' => array()
        );
        
        return wp_kses($code, $allowed_tags);
    }
    
    /**
     * Minify CSS
     * 
     * @since 2.0.0
     * @param string $css CSS code
     * @return string Minified CSS
     */
    private function minify_css($css) {
        // Remove comments
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        
        // Remove whitespace
        $css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
        
        return $css;
    }
    
    /**
     * Minify JavaScript
     * 
     * @since 2.0.0
     * @param string $js JavaScript code
     * @return string Minified JavaScript
     */
    private function minify_js($js) {
        // Basic JavaScript minification
        $js = preg_replace('/\/\*[\s\S]*?\*\//', '', $js);
        $js = preg_replace('/\/\/.*$/m', '', $js);
        $js = preg_replace('/\s+/', ' ', $js);
        
        return trim($js);
    }
    
    /**
     * Add injection point
     * 
     * @since 2.0.0
     * @param string $location Injection location (head, footer, admin_head)
     * @param string $code Code to inject
     */
    public function add_injection_point($location, $code) {
        if (isset($this->injection_points[$location])) {
            $this->injection_points[$location][] = $code;
            update_option('cdswerx_injection_points', $this->injection_points);
        }
    }
    
    /**
     * Remove injection point
     * 
     * @since 2.0.0
     * @param string $location Injection location
     * @param int $index Code index
     */
    public function remove_injection_point($location, $index) {
        if (isset($this->injection_points[$location][$index])) {
            unset($this->injection_points[$location][$index]);
            update_option('cdswerx_injection_points', $this->injection_points);
        }
    }
    
    /**
     * Get CSS settings
     * 
     * @since 2.0.0
     * @return array CSS settings
     */
    public function get_css_settings() {
        return $this->css_settings;
    }
    
    /**
     * Get JavaScript settings
     * 
     * @since 2.0.0
     * @return array JavaScript settings
     */
    public function get_js_settings() {
        return $this->js_settings;
    }
    
    /**
     * Update CSS settings
     * 
     * @since 2.0.0
     * @param array $settings CSS settings
     */
    public function update_css_settings($settings) {
        $this->css_settings = array_merge($this->css_settings, $settings);
        update_option('cdswerx_css_settings', $this->css_settings);
    }
    
    /**
     * Update JavaScript settings
     * 
     * @since 2.0.0
     * @param array $settings JavaScript settings
     */
    public function update_js_settings($settings) {
        $this->js_settings = array_merge($this->js_settings, $settings);
        update_option('cdswerx_js_settings', $this->js_settings);
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