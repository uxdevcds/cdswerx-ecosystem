<?php
/**
 * CDSWerx Extensions Manager
 *
 * Theme compatibility, integrations, and extension coordination
 * Consolidates extension functionality from multiple classes
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
 * CDSWerx Extensions Manager Class
 * 
 * Handles theme compatibility, integrations, and extension coordination
 * Consolidates functionality from hello-elementor-sync and extension classes
 * 
 * @since 2.0.0
 */
class CDSWerx_Extensions_Manager {
    
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
     * Registered extensions
     * 
     * @since 2.0.0
     * @access private
     * @var array $extensions
     */
    private $extensions = array();
    
    /**
     * Theme compatibility status
     * 
     * @since 2.0.0
     * @access private
     * @var array $theme_compatibility
     */
    private $theme_compatibility = array();
    
    /**
     * Initialize extensions manager
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
    }
    
    /**
     * Register extension hooks
     * 
     * @since 2.0.0
     * @param Cdswerx_Loader $loader The loader instance
     */
    public function register_hooks($loader) {
        // Theme compatibility
        $loader->add_action('after_setup_theme', $this, 'check_theme_compatibility');
        $loader->add_action('switch_theme', $this, 'on_theme_switch');
        
        // Elementor integration
        $loader->add_action('elementor/loaded', $this, 'elementor_loaded');
        $loader->add_action('elementor/widgets/widgets_registered', $this, 'register_elementor_widgets');
        
        // Extension initialization
        $loader->add_action('init', $this, 'init_extensions');
        $loader->add_action('plugins_loaded', $this, 'load_extensions');
        
        // Hello Elementor specific
        $loader->add_action('wp_enqueue_scripts', $this, 'hello_elementor_compatibility');
    }
    
    /**
     * Initialize extensions
     * 
     * @since 2.0.0
     */
    public function init_extensions() {
        // Register built-in extensions
        $this->register_extension('hello_elementor_sync', array(
            'name' => __('Hello Elementor Sync', 'cdswerx'),
            'description' => __('Synchronization with Hello Elementor theme', 'cdswerx'),
            'version' => '1.0.0',
            'active' => true
        ));
        
        $this->register_extension('typography_system', array(
            'name' => __('Typography System', 'cdswerx'),
            'description' => __('Advanced typography management', 'cdswerx'),
            'version' => '1.0.0',
            'active' => true
        ));
        
        $this->register_extension('custom_code', array(
            'name' => __('Custom Code', 'cdswerx'),
            'description' => __('Custom CSS and JavaScript injection', 'cdswerx'),
            'version' => '1.0.0',
            'active' => true
        ));
        
        // Allow external extensions to register
        do_action('cdswerx_register_extensions', $this);
    }
    
    /**
     * Load active extensions
     * 
     * @since 2.0.0
     */
    public function load_extensions() {
        foreach ($this->extensions as $id => $extension) {
            if ($extension['active']) {
                do_action('cdswerx_load_extension', $id, $extension);
            }
        }
    }
    
    /**
     * Register an extension
     * 
     * @since 2.0.0
     * @param string $id Extension ID
     * @param array $args Extension arguments
     */
    public function register_extension($id, $args) {
        $defaults = array(
            'name' => '',
            'description' => '',
            'version' => '1.0.0',
            'active' => false,
            'callback' => null
        );
        
        $this->extensions[$id] = wp_parse_args($args, $defaults);
    }
    
    /**
     * Activate an extension
     * 
     * @since 2.0.0
     * @param string $id Extension ID
     * @return bool Success status
     */
    public function activate_extension($id) {
        if (!isset($this->extensions[$id])) {
            return false;
        }
        
        $this->extensions[$id]['active'] = true;
        
        // Trigger activation hook
        do_action('cdswerx_extension_activated', $id, $this->extensions[$id]);
        
        return true;
    }
    
    /**
     * Deactivate an extension
     * 
     * @since 2.0.0
     * @param string $id Extension ID
     * @return bool Success status
     */
    public function deactivate_extension($id) {
        if (!isset($this->extensions[$id])) {
            return false;
        }
        
        $this->extensions[$id]['active'] = false;
        
        // Trigger deactivation hook
        do_action('cdswerx_extension_deactivated', $id, $this->extensions[$id]);
        
        return true;
    }
    
    /**
     * Check theme compatibility
     * 
     * @since 2.0.0
     */
    public function check_theme_compatibility() {
        $current_theme = get_template();
        
        // Hello Elementor compatibility
        if ($current_theme === 'hello-elementor') {
            $this->theme_compatibility['hello_elementor'] = array(
                'status' => 'compatible',
                'version' => wp_get_theme()->get('Version'),
                'sync_enabled' => true
            );
        } else {
            $this->theme_compatibility['hello_elementor'] = array(
                'status' => 'independent',
                'version' => null,
                'sync_enabled' => false
            );
        }
        
        // CDSWerx Theme compatibility
        if ($current_theme === 'cdswerx-theme' || get_template() === 'cdswerx-theme') {
            $this->theme_compatibility['cdswerx_theme'] = array(
                'status' => 'native',
                'version' => wp_get_theme()->get('Version'),
                'integration_level' => 'full'
            );
        }
        
        // Update compatibility status
        update_option('cdswerx_theme_compatibility', $this->theme_compatibility);
        
        // Trigger compatibility check hook
        do_action('cdswerx_theme_compatibility_checked', $this->theme_compatibility);
    }
    
    /**
     * Handle theme switch
     * 
     * @since 2.0.0
     */
    public function on_theme_switch() {
        // Re-check compatibility
        $this->check_theme_compatibility();
        
        // Clear any cached data
        delete_transient('cdswerx_theme_data');
        
        // Trigger theme switch hook
        do_action('cdswerx_theme_switched', get_template());
    }
    
    /**
     * Elementor loaded callback
     * 
     * @since 2.0.0
     */
    public function elementor_loaded() {
        // Register Elementor integrations
        $this->register_elementor_integrations();
        
        // Check Elementor Pro status
        $this->check_elementor_pro();
    }
    
    /**
     * Register Elementor integrations
     * 
     * @since 2.0.0
     */
    private function register_elementor_integrations() {
        // Custom Elementor widgets and controls
        add_action('elementor/controls/controls_registered', array($this, 'register_elementor_controls'));
        add_action('elementor/elements/categories_registered', array($this, 'register_elementor_categories'));
    }
    
    /**
     * Register Elementor widgets
     * 
     * @since 2.0.0
     */
    public function register_elementor_widgets() {
        // Register CDSWerx custom widgets
        do_action('cdswerx_elementor_widgets_registered');
    }
    
    /**
     * Register Elementor controls
     * 
     * @since 2.0.0
     */
    public function register_elementor_controls() {
        // Register custom controls
        do_action('cdswerx_elementor_controls_registered');
    }
    
    /**
     * Register Elementor categories
     * 
     * @since 2.0.0
     */
    public function register_elementor_categories($elements_manager) {
        $elements_manager->add_category(
            'cdswerx',
            array(
                'title' => __('CDSWerx', 'cdswerx'),
                'icon' => 'fa fa-plug',
            )
        );
    }
    
    /**
     * Check Elementor Pro status
     * 
     * @since 2.0.0
     */
    private function check_elementor_pro() {
        $is_pro = defined('ELEMENTOR_PRO_VERSION');
        update_option('cdswerx_elementor_pro_active', $is_pro);
        
        if ($is_pro) {
            // Enable Pro-specific features
            do_action('cdswerx_elementor_pro_active');
        }
    }
    
    /**
     * Hello Elementor compatibility styles
     * 
     * @since 2.0.0
     */
    public function hello_elementor_compatibility() {
        if (!isset($this->theme_compatibility['hello_elementor']) || 
            $this->theme_compatibility['hello_elementor']['status'] !== 'compatible') {
            return;
        }
        
        // Enqueue compatibility styles
        wp_enqueue_style(
            'cdswerx-hello-elementor-compat',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'public/css/hello-elementor-compat.css',
            array(),
            $this->version
        );
    }
    
    /**
     * Get registered extensions
     * 
     * @since 2.0.0
     * @return array Registered extensions
     */
    public function get_extensions() {
        return $this->extensions;
    }
    
    /**
     * Get active extensions
     * 
     * @since 2.0.0
     * @return array Active extensions
     */
    public function get_active_extensions() {
        return array_filter($this->extensions, function($extension) {
            return $extension['active'];
        });
    }
    
    /**
     * Get theme compatibility status
     * 
     * @since 2.0.0
     * @return array Theme compatibility data
     */
    public function get_theme_compatibility() {
        return $this->theme_compatibility;
    }
    
    /**
     * Check if extension is active
     * 
     * @since 2.0.0
     * @param string $id Extension ID
     * @return bool Active status
     */
    public function is_extension_active($id) {
        return isset($this->extensions[$id]) && $this->extensions[$id]['active'];
    }
    
    /**
     * Check if Hello Elementor sync is enabled
     * 
     * @since 2.0.0
     * @return bool Sync status
     */
    public function is_hello_elementor_sync_enabled() {
        return isset($this->theme_compatibility['hello_elementor']) && 
               $this->theme_compatibility['hello_elementor']['sync_enabled'];
    }
    
    /**
     * Check if CDSWerx theme is active
     * 
     * @since 2.0.0
     * @return bool Theme status
     */
    public function is_cdswerx_theme_active() {
        return isset($this->theme_compatibility['cdswerx_theme']) && 
               $this->theme_compatibility['cdswerx_theme']['status'] === 'native';
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