<?php
/**
 * CDSWerx Core Plugin Manager
 *
 * Main plugin orchestration and lifecycle management
 * Consolidates core initialization functionality
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
 * CDSWerx Core Manager Class
 * 
 * Handles plugin initialization, dependency loading, and core orchestration
 * Replaces the main Cdswerx class with focused responsibility
 * 
 * @since 2.0.0
 */
class CDSWerx_Core {
    
    /**
     * The unique identifier of this plugin
     * 
     * @since 2.0.0
     * @access private
     * @var string $plugin_name
     */
    private $plugin_name;
    
    /**
     * The current version of the plugin
     * 
     * @since 2.0.0
     * @access private
     * @var string $version
     */
    private $version;
    
    /**
     * The loader that's responsible for maintaining and registering all hooks
     * 
     * @since 2.0.0
     * @access private
     * @var Cdswerx_Loader $loader
     */
    private $loader;
    
    /**
     * Array of manager instances
     * 
     * @since 2.0.0
     * @access private
     * @var array $managers
     */
    private $managers = array();
    
    /**
     * Initialize the core plugin functionality
     * 
     * @since 2.0.0
     */
    public function __construct() {
        if (defined('CDSWERX_VERSION')) {
            $this->version = CDSWERX_VERSION;
        } else {
            $this->version = '2.0.0';
        }
        
        $this->plugin_name = 'cdswerx';
        
        $this->load_dependencies();
        $this->initialize_managers();
        $this->set_locale();
        $this->register_core_hooks();
    }
    
    /**
     * Load core dependencies
     * 
     * @since 2.0.0
     * @access private
     */
    private function load_dependencies() {
        // Load essential classes
        require_once plugin_dir_path(dirname(__FILE__)) . 'class-cdswerx-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'class-cdswerx-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'class-cdswerx-autoloader.php';
        
        // Initialize autoloader
        $autoloader = new Cdswerx_Autoloader();
        $autoloader->register();
        
        // Create loader instance
        $this->loader = new Cdswerx_Loader();
    }
    
    /**
     * Initialize all manager classes
     * 
     * @since 2.0.0
     * @access private
     */
    private function initialize_managers() {
        // Load manager classes
        $this->managers['admin'] = new CDSWerx_Admin_Manager($this->get_plugin_name(), $this->get_version(), $this->get_loader());
        $this->managers['extensions'] = new CDSWerx_Extensions_Manager($this->get_plugin_name(), $this->get_version());
        $this->managers['sync'] = new CDSWerx_Sync_Manager($this->get_plugin_name(), $this->get_version());
        $this->managers['code'] = new CDSWerx_Code_Manager($this->get_plugin_name(), $this->get_version());
        $this->managers['typography'] = new CDSWerx_Typography_Manager($this->get_plugin_name(), $this->get_version());
        $this->managers['system'] = new CDSWerx_System_Manager($this->get_plugin_name(), $this->get_version());
        $this->managers['database'] = new CDSWerx_Database_Manager($this->get_plugin_name(), $this->get_version());
    }
    
    /**
     * Define the locale for this plugin for internationalization
     * 
     * @since 2.0.0
     * @access private
     */
    private function set_locale() {
        $plugin_i18n = new Cdswerx_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }
    
    /**
     * Register core hooks and filters
     * 
     * @since 2.0.0
     * @access private
     */
    private function register_core_hooks() {
        // Plugin lifecycle hooks
        $this->loader->add_action('init', $this, 'init_plugin');
        $this->loader->add_action('wp_loaded', $this, 'plugin_loaded');
        
        // Manager initialization hooks
        foreach ($this->managers as $manager_name => $manager) {
            if (method_exists($manager, 'register_hooks')) {
                $manager->register_hooks($this->loader);
            }
        }
    }
    
    /**
     * Initialize plugin after WordPress is loaded
     * 
     * @since 2.0.0
     */
    public function init_plugin() {
        // Plugin initialization logic
        do_action('cdswerx_core_initialized', $this);
    }
    
    /**
     * Run after WordPress is fully loaded
     * 
     * @since 2.0.0
     */
    public function plugin_loaded() {
        // Post-load initialization
        do_action('cdswerx_plugin_loaded', $this);
    }
    
    /**
     * Run the loader to execute all hooks
     * 
     * @since 2.0.0
     */
    public function run() {
        $this->loader->run();
    }
    
    /**
     * Get the plugin name
     * 
     * @since 2.0.0
     * @return string The plugin name
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    /**
     * Get the plugin version
     * 
     * @since 2.0.0
     * @return string The plugin version
     */
    public function get_version() {
        return $this->version;
    }
    
    /**
     * Get the loader instance
     * 
     * @since 2.0.0
     * @return Cdswerx_Loader The loader instance
     */
    public function get_loader() {
        return $this->loader;
    }
    
    /**
     * Get a specific manager instance
     * 
     * @since 2.0.0
     * @param string $manager_name The manager name
     * @return object|false The manager instance or false if not found
     */
    public function get_manager($manager_name) {
        return isset($this->managers[$manager_name]) ? $this->managers[$manager_name] : false;
    }
    
    /**
     * Get all manager instances
     * 
     * @since 2.0.0
     * @return array All manager instances
     */
    public function get_managers() {
        return $this->managers;
    }
    
    /**
     * Check if plugin is properly initialized
     * 
     * @since 2.0.0
     * @return bool True if initialized
     */
    public function is_initialized() {
        return !empty($this->managers) && $this->loader instanceof Cdswerx_Loader;
    }
    
    /**
     * Get plugin information
     * 
     * @since 2.0.0
     * @return array Plugin information
     */
    public function get_plugin_info() {
        return array(
            'name' => $this->plugin_name,
            'version' => $this->version,
            'managers' => array_keys($this->managers),
            'initialized' => $this->is_initialized()
        );
    }
}