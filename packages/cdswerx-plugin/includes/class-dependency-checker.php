<?php

/**
 * Dependency Checker Class
 *
 * Monitors Hello Elementor theme status, validates dependencies,
 * and manages independent mode functionality for CDSWerx ecosystem.
 *
 * @link       https://cdswerx.com
 * @since      1.0.4
 *
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    die;
}

/**
 * Dependency Checker Class
 *
 * Handles Hello Elementor dependency monitoring, validation checks,
 * and independent mode management for the CDSWerx ecosystem.
 *
 * @since      1.0.4
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 * @author     CDSWerx <info@cdswerx.com>
 */
class Dependency_Checker
{
    /**
     * Dependency checker identifier
     *
     * @since    1.0.4
     * @access   protected
     * @var      string    $checker_name    The string used to identify this dependency checker.
     */
    protected $checker_name;

    /**
     * Current checker version
     *
     * @since    1.0.4
     * @access   protected
     * @var      string    $version    The current version of the dependency checker.
     */
    protected $version;

    /**
     * Dependency status information
     *
     * @since    1.0.4
     * @access   protected
     * @var      array    $dependency_status    Current dependency status for all components.
     */
    protected $dependency_status;

    /**
     * Hello Elementor requirements
     *
     * @since    1.0.4
     * @access   protected
     * @var      array    $hello_elementor_requirements    Required Hello Elementor components.
     */
    protected $hello_elementor_requirements;

    /**
     * Independent mode capabilities
     *
     * @since    1.0.4
     * @access   protected
     * @var      array    $independent_capabilities    Available independent mode features.
     */
    protected $independent_capabilities;

    /**
     * Validation rules
     *
     * @since    1.0.4
     * @access   protected
     * @var      array    $validation_rules    Rules for dependency validation.
     */
    protected $validation_rules;

    /**
     * Independent mode manager instance
     *
     * @since    1.1.0
     * @access   protected
     * @var      CDSWerx_Independent_Mode_Manager    $independent_manager    Independent mode manager instance.
     */
    protected $independent_manager;

    /**
     * Initialize the Dependency Checker
     *
     * @since    1.0.4
     */
    public function __construct()
    {
        $this->checker_name = 'cdswerx-dependency-checker';
        $this->version = '1.1.0';
        $this->dependency_status = array();
        $this->hello_elementor_requirements = array();
        $this->independent_capabilities = array();
        
        $this->load_checker_settings();
        $this->init_dependency_monitoring();
        $this->setup_validation_hooks();
        $this->init_independent_mode_manager();
    }

    /**
     * Initialize independent mode manager
     *
     * @since    1.1.0
     */
    private function init_independent_mode_manager()
    {
        // Load independent mode manager if it doesn't exist
        if (!class_exists('CDSWerx_Independent_Mode_Manager')) {
            require_once plugin_dir_path(__FILE__) . 'class-independent-mode-manager.php';
        }
        
        $this->independent_manager = new CDSWerx_Independent_Mode_Manager();
    }

    /**
     * Load dependency checker settings
     *
     * @since    1.0.4
     */
    private function load_checker_settings()
    {
        $this->hello_elementor_requirements = array(
            'minimum_version' => '2.0.0',
            'required_files' => array(
                'style.css',
                'functions.php',
                'index.php',
                'theme.json',
            ),
            'required_functions' => array(
                'hello_elementor_setup',
                'hello_elementor_scripts_styles',
                'hello_elementor_enqueue_style',
            ),
            'required_hooks' => array(
                'after_setup_theme',
                'wp_enqueue_scripts',
            ),
        );

        $this->independent_capabilities = array(
            'theme_support' => array(
                'post-thumbnails',
                'automatic-feed-links',
                'title-tag',
                'html5',
                'custom-logo',
                'customize-selective-refresh-widgets',
            ),
            'elementor_support' => array(
                'elementor-page-builder',
                'elementor-theme-style-override',
            ),
            'essential_functions' => array(
                'hello_elementor_setup',
                'hello_elementor_scripts_styles',
                'hello_elementor_enqueue_style',
                'hello_elementor_add_description_meta_tag',
            ),
        );

        $this->validation_rules = get_option('cdswerx_validation_rules', array(
            'strict_mode' => false,
            'allow_partial_independence' => true,
            'require_backup' => true,
            'validate_on_activation' => true,
        ));
    }

    /**
     * Initialize dependency monitoring
     *
     * @since    1.0.4
     */
    private function init_dependency_monitoring()
    {
        $this->dependency_status = get_option('cdswerx_dependency_status', array(
            'hello_elementor' => array(
                'status' => 'unknown',
                'version' => null,
                'last_check' => null,
                'missing_requirements' => array(),
            ),
            'wordpress' => array(
                'status' => 'unknown',
                'version' => get_bloginfo('version'),
                'meets_requirements' => null,
            ),
            'php' => array(
                'status' => 'unknown',
                'version' => PHP_VERSION,
                'meets_requirements' => null,
            ),
            'elementor' => array(
                'status' => 'unknown',
                'version' => null,
                'active' => null,
            ),
        ));

        $this->check_all_dependencies();
    }

    /**
     * Setup validation hooks
     *
     * @since    1.0.4
     */
    private function setup_validation_hooks()
    {
        // Theme activation/deactivation hooks
        add_action('switch_theme', array($this, 'handle_theme_switch'), 10, 2);
        add_action('after_setup_theme', array($this, 'validate_current_setup'));
        
        // Plugin activation/deactivation hooks
        add_action('activated_plugin', array($this, 'handle_plugin_activation'));
        add_action('deactivated_plugin', array($this, 'handle_plugin_deactivation'));
        
        // Scheduled dependency checks
        add_action('init', array($this, 'schedule_dependency_checks'));
        add_action('cdswerx_dependency_check', array($this, 'perform_scheduled_check'));
        
        // Admin notices for dependency issues
        add_action('admin_notices', array($this, 'display_dependency_notices'));
    }

    /**
     * Check all dependencies
     *
     * @since    1.0.4
     */
    public function check_all_dependencies()
    {
        $this->check_hello_elementor_dependency();
        $this->check_wordpress_dependency();
        $this->check_php_dependency();
        $this->check_elementor_dependency();
        
        // Update last check time
        $this->dependency_status['last_full_check'] = current_time('mysql');
        update_option('cdswerx_dependency_status', $this->dependency_status);
        
        // Trigger dependency check completion
        do_action('cdswerx_dependency_check_completed', $this->dependency_status);
    }

    /**
     * Check Hello Elementor dependency
     *
     * @since    1.0.4
     */
    public function check_hello_elementor_dependency()
    {
        $hello_theme = wp_get_theme('hello-elementor');
        $missing_requirements = array();
        $independent_mode_available = false;

        if (!$hello_theme->exists()) {
            $this->dependency_status['hello_elementor']['status'] = 'missing';
            $this->dependency_status['hello_elementor']['version'] = null;
            $missing_requirements[] = 'theme_not_installed';
            
            // Check if independent mode can compensate
            if ($this->independent_manager && $this->independent_manager->is_independent_mode_active()) {
                $independent_mode_available = true;
                $this->dependency_status['hello_elementor']['status'] = 'independent_mode';
            }
        } else {
            $theme_version = $hello_theme->get('Version');
            $this->dependency_status['hello_elementor']['version'] = $theme_version;

            // Check version requirement
            if (version_compare($theme_version, $this->hello_elementor_requirements['minimum_version'], '<')) {
                $missing_requirements[] = 'version_too_old';
                $this->dependency_status['hello_elementor']['status'] = 'outdated';
            }

            // Check required files
            $theme_path = $hello_theme->get_template_directory();
            foreach ($this->hello_elementor_requirements['required_files'] as $required_file) {
                if (!file_exists($theme_path . '/' . $required_file)) {
                    $missing_requirements[] = 'missing_file_' . $required_file;
                }
            }

            // Check required functions (if theme is active)
            if (get_template() === 'hello-elementor') {
                foreach ($this->hello_elementor_requirements['required_functions'] as $required_function) {
                    if (!function_exists($required_function)) {
                        $missing_requirements[] = 'missing_function_' . $required_function;
                    }
                }
            }

            // Set overall status
            if (empty($missing_requirements)) {
                $this->dependency_status['hello_elementor']['status'] = 'satisfied';
            } else {
                $this->dependency_status['hello_elementor']['status'] = 'partially_satisfied';
                
                // Check if independent mode can compensate for missing requirements
                if ($this->independent_manager && $this->independent_manager->is_independent_mode_active()) {
                    $independent_mode_available = true;
                }
            }
        }

        // Check independent mode capability
        if ($this->independent_manager) {
            $independent_mode_available = $this->independent_manager->is_independent_mode_active();
            $this->dependency_status['hello_elementor']['independent_mode_active'] = $independent_mode_available;
            $this->dependency_status['hello_elementor']['fallback_functions_available'] = count($this->independent_manager->get_available_fallback_functions());
        }

        $this->dependency_status['hello_elementor']['missing_requirements'] = $missing_requirements;
        $this->dependency_status['hello_elementor']['last_check'] = current_time('mysql');

        // Return true if Hello Elementor is satisfied OR independent mode is handling it
        return empty($missing_requirements) || $independent_mode_available;
    }

    /**
     * Check WordPress dependency
     *
     * @since    1.0.4
     */
    public function check_wordpress_dependency()
    {
        $wp_version = get_bloginfo('version');
        $minimum_wp_version = '5.0.0'; // Minimum WordPress version required
        
        $this->dependency_status['wordpress']['version'] = $wp_version;
        $this->dependency_status['wordpress']['meets_requirements'] = version_compare($wp_version, $minimum_wp_version, '>=');
        $this->dependency_status['wordpress']['status'] = $this->dependency_status['wordpress']['meets_requirements'] ? 'satisfied' : 'outdated';
        
        return $this->dependency_status['wordpress']['meets_requirements'];
    }

    /**
     * Check PHP dependency
     *
     * @since    1.0.4
     */
    public function check_php_dependency()
    {
        $php_version = PHP_VERSION;
        $minimum_php_version = '7.4.0'; // Minimum PHP version required
        
        $this->dependency_status['php']['version'] = $php_version;
        $this->dependency_status['php']['meets_requirements'] = version_compare($php_version, $minimum_php_version, '>=');
        $this->dependency_status['php']['status'] = $this->dependency_status['php']['meets_requirements'] ? 'satisfied' : 'outdated';
        
        return $this->dependency_status['php']['meets_requirements'];
    }

    /**
     * Check Elementor dependency
     *
     * @since    1.0.4
     */
    public function check_elementor_dependency()
    {
        $elementor_active = is_plugin_active('elementor/elementor.php');
        $elementor_version = null;
        
        if ($elementor_active && defined('ELEMENTOR_VERSION')) {
            $elementor_version = ELEMENTOR_VERSION;
        }
        
        $this->dependency_status['elementor']['active'] = $elementor_active;
        $this->dependency_status['elementor']['version'] = $elementor_version;
        $this->dependency_status['elementor']['status'] = $elementor_active ? 'satisfied' : 'missing';
        
        return $elementor_active;
    }

    /**
     * Validate current setup for independent mode capability
     *
     * @since    1.0.4
     * @return   array    Validation results
     */
    public function validate_current_setup()
    {
        $validation_results = array(
            'can_run_independent' => false,
            'missing_capabilities' => array(),
            'warnings' => array(),
            'recommendations' => array(),
        );

        // Check if Hello Elementor is currently active
        $hello_active = (get_template() === 'hello-elementor');
        
        if (!$hello_active) {
            // Already running independently, validate essential functions
            $validation_results = $this->validate_independent_mode();
        } else {
            // Check if we can transition to independent mode
            $validation_results = $this->validate_independence_readiness();
        }

        // Store validation results
        update_option('cdswerx_independence_validation', array_merge($validation_results, array(
            'validated_at' => current_time('mysql'),
            'hello_elementor_active' => $hello_active,
        )));

        return $validation_results;
    }

    /**
     * Validate independent mode functionality
     *
     * @since    1.0.4
     * @return   array    Validation results for independent mode
     */
    public function validate_independent_mode()
    {
        $validation_results = array(
            'can_run_independent' => true,
            'missing_capabilities' => array(),
            'warnings' => array(),
            'recommendations' => array(),
        );

        // Check essential theme supports
        foreach ($this->independent_capabilities['theme_support'] as $support) {
            if (!current_theme_supports($support)) {
                $validation_results['missing_capabilities'][] = "theme_support_{$support}";
                $validation_results['can_run_independent'] = false;
            }
        }

        // Check essential functions
        foreach ($this->independent_capabilities['essential_functions'] as $function) {
            if (!function_exists($function)) {
                $validation_results['missing_capabilities'][] = "function_{$function}";
                $validation_results['warnings'][] = "Missing function: {$function}";
            }
        }

        // Check Elementor compatibility
        if (is_plugin_active('elementor/elementor.php')) {
            foreach ($this->independent_capabilities['elementor_support'] as $elementor_support) {
                // Check if Elementor-specific features are available
                if ($elementor_support === 'elementor-page-builder' && !defined('ELEMENTOR_VERSION')) {
                    $validation_results['warnings'][] = 'Elementor is active but version not detected';
                }
            }
        }

        // Add recommendations
        if (!empty($validation_results['missing_capabilities'])) {
            $validation_results['recommendations'][] = 'Enable CDSWerx independent mode functions';
        }

        if (empty($validation_results['warnings'])) {
            $validation_results['recommendations'][] = 'Independent mode is fully operational';
        }

        return $validation_results;
    }

    /**
     * Validate readiness for independence
     *
     * @since    1.0.4
     * @return   array    Validation results for independence transition
     */
    public function validate_independence_readiness()
    {
        $validation_results = array(
            'can_run_independent' => true,
            'missing_capabilities' => array(),
            'warnings' => array(),
            'recommendations' => array(),
        );

        // Check if Hello Elementor functions can be replaced
        foreach ($this->independent_capabilities['essential_functions'] as $function) {
            if (function_exists($function)) {
                // Function exists, we can potentially replace it
                $validation_results['recommendations'][] = "Can provide independent version of {$function}";
            } else {
                $validation_results['missing_capabilities'][] = "function_{$function}";
                $validation_results['warnings'][] = "Hello Elementor function {$function} not found";
            }
        }

        // Check current theme support features
        foreach ($this->independent_capabilities['theme_support'] as $support) {
            if (current_theme_supports($support)) {
                $validation_results['recommendations'][] = "Theme support for {$support} is active";
            } else {
                $validation_results['warnings'][] = "Theme support for {$support} is not active";
            }
        }

        // Check if backup is possible
        if ($this->validation_rules['require_backup']) {
            $backup_possible = $this->check_backup_capability();
            if (!$backup_possible) {
                $validation_results['missing_capabilities'][] = 'backup_capability';
                $validation_results['can_run_independent'] = false;
            }
        }

        return $validation_results;
    }

    /**
     * Check backup capability for independence transition
     *
     * @since    1.0.4
     * @return   bool    Whether backup is possible
     */
    private function check_backup_capability()
    {
        $upload_dir = wp_upload_dir();
        
        // Check if uploads directory is writable
        if (!wp_is_writable($upload_dir['basedir'])) {
            return false;
        }

        // Check available disk space (minimum 100MB)
        $free_space = disk_free_space($upload_dir['basedir']);
        if ($free_space < (100 * 1024 * 1024)) {
            return false;
        }

        return true;
    }

    /**
     * Provide essential functions for independent mode
     *
     * @since    1.0.4
     */
    public function provide_essential_functions()
    {
        // Only provide functions if not running Hello Elementor
        if (get_template() === 'hello-elementor') {
            return;
        }

        $this->load_essential_theme_functions();
        $this->setup_essential_theme_support();
        $this->enqueue_essential_assets();
    }

    /**
     * Load essential theme functions for independent mode
     *
     * @since    1.0.4
     */
    private function load_essential_theme_functions()
    {
        // Hello Elementor theme setup function
        if (!function_exists('hello_elementor_setup')) {
            function hello_elementor_setup() {
                // Add theme support for various features
                add_theme_support('post-thumbnails');
                add_theme_support('automatic-feed-links');
                add_theme_support('title-tag');
                add_theme_support('html5', array(
                    'search-form',
                    'comment-form',
                    'comment-list',
                    'gallery',
                    'caption',
                    'navigation-widgets',
                ));
                add_theme_support('custom-logo', array(
                    'height' => 100,
                    'width' => 350,
                    'flex-height' => true,
                    'flex-width' => true,
                ));
                add_theme_support('customize-selective-refresh-widgets');
                
                // Elementor theme support
                add_theme_support('elementor-page-builder');
                add_theme_support('elementor-theme-style-override');
                
                // Register navigation menus
                register_nav_menus(array(
                    'menu-1' => esc_html__('Header', 'hello-elementor'),
                    'menu-2' => esc_html__('Footer', 'hello-elementor'),
                ));
            }
        }

        // Hello Elementor scripts and styles function
        if (!function_exists('hello_elementor_scripts_styles')) {
            function hello_elementor_scripts_styles() {
                $min_suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
                
                wp_enqueue_style(
                    'hello-elementor',
                    get_template_directory_uri() . '/style' . $min_suffix . '.css',
                    array(),
                    CDSWERX_VERSION
                );
                
                wp_enqueue_style(
                    'hello-elementor-theme-style',
                    get_template_directory_uri() . '/theme/theme' . $min_suffix . '.css',
                    array(),
                    CDSWERX_VERSION
                );
            }
        }

        // Hello Elementor enqueue style function
        if (!function_exists('hello_elementor_enqueue_style')) {
            function hello_elementor_enqueue_style() {
                wp_enqueue_style(
                    'hello-elementor-child-style',
                    get_stylesheet_directory_uri() . '/style.css',
                    array('hello-elementor'),
                    wp_get_theme()->get('Version')
                );
            }
        }

        // Hello Elementor description meta tag function
        if (!function_exists('hello_elementor_add_description_meta_tag')) {
            function hello_elementor_add_description_meta_tag() {
                if (!current_user_can('manage_options')) {
                    return;
                }
                
                $description = get_bloginfo('description', 'display');
                if ($description) {
                    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
                }
            }
        }
    }

    /**
     * Setup essential theme support for independent mode
     *
     * @since    1.0.4
     */
    private function setup_essential_theme_support()
    {
        // Hook essential functions
        add_action('after_setup_theme', 'hello_elementor_setup');
        add_action('wp_enqueue_scripts', 'hello_elementor_scripts_styles');
        add_action('wp_enqueue_scripts', 'hello_elementor_enqueue_style', 20);
        add_action('wp_head', 'hello_elementor_add_description_meta_tag');
        
        // Additional independent mode setup
        add_action('after_setup_theme', array($this, 'setup_independent_mode_features'));
    }

    /**
     * Setup independent mode specific features
     *
     * @since    1.0.4
     */
    public function setup_independent_mode_features()
    {
        // Enable additional theme features for independent mode
        add_theme_support('widgets');
        add_theme_support('menus');
        add_theme_support('custom-header');
        add_theme_support('custom-background');
        
        // Set content width for independent mode
        if (!isset($GLOBALS['content_width'])) {
            $GLOBALS['content_width'] = 800;
        }
        
        // Register sidebars for independent mode
        add_action('widgets_init', array($this, 'register_independent_sidebars'));
    }

    /**
     * Register sidebars for independent mode
     *
     * @since    1.0.4
     */
    public function register_independent_sidebars()
    {
        register_sidebar(array(
            'name' => esc_html__('Sidebar', 'hello-elementor'),
            'id' => 'sidebar-1',
            'description' => esc_html__('Add widgets here.', 'hello-elementor'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h2 class="widget-title">',
            'after_title' => '</h2>',
        ));
    }

    /**
     * Enqueue essential assets for independent mode
     *
     * @since    1.0.4
     */
    private function enqueue_essential_assets()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_independent_assets'));
    }

    /**
     * Enqueue independent mode assets
     *
     * @since    1.0.4
     */
    public function enqueue_independent_assets()
    {
        // Only enqueue if not running Hello Elementor
        if (get_template() === 'hello-elementor') {
            return;
        }

        // Enqueue essential CSS for independent mode
        wp_enqueue_style(
            'cdswerx-independent-mode',
            plugin_dir_url(__FILE__) . '../assets/css/independent-mode.css',
            array(),
            CDSWERX_VERSION
        );

        // Enqueue essential JavaScript for independent mode
        wp_enqueue_script(
            'cdswerx-independent-mode',
            plugin_dir_url(__FILE__) . '../assets/js/independent-mode.js',
            array('jquery'),
            CDSWERX_VERSION,
            true
        );
    }

    /**
     * Handle theme switch
     *
     * @since    1.0.4
     * @param    string    $new_name       New theme name
     * @param    object    $new_theme      New theme object
     */
    public function handle_theme_switch($new_name, $new_theme)
    {
        // Re-check dependencies after theme switch
        $this->check_all_dependencies();
        
        // Validate new setup
        $validation_results = $this->validate_current_setup();
        
        // Log theme switch for dependency tracking
        $this->log_dependency_event('theme_switch', array(
            'new_theme' => $new_name,
            'validation_results' => $validation_results,
        ));

        // If switching away from Hello Elementor, provide essential functions
        if ($new_name !== 'hello-elementor') {
            $this->provide_essential_functions();
        }
    }

    /**
     * Handle plugin activation
     *
     * @since    1.0.4
     * @param    string    $plugin    Plugin path
     */
    public function handle_plugin_activation($plugin)
    {
        if (strpos($plugin, 'elementor') !== false) {
            $this->check_elementor_dependency();
        }
    }

    /**
     * Handle plugin deactivation
     *
     * @since    1.0.4
     * @param    string    $plugin    Plugin path
     */
    public function handle_plugin_deactivation($plugin)
    {
        if (strpos($plugin, 'elementor') !== false) {
            $this->check_elementor_dependency();
        }
    }

    /**
     * Schedule dependency checks
     *
     * @since    1.0.4
     */
    public function schedule_dependency_checks()
    {
        if (!wp_next_scheduled('cdswerx_dependency_check')) {
            wp_schedule_event(time(), 'twicedaily', 'cdswerx_dependency_check');
        }
    }

    /**
     * Perform scheduled dependency check
     *
     * @since    1.0.4
     */
    public function perform_scheduled_check()
    {
        $this->check_all_dependencies();
    }

    /**
     * Display dependency notices in admin
     *
     * @since    1.0.4
     */
    public function display_dependency_notices()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $critical_issues = $this->get_critical_dependency_issues();
        
        foreach ($critical_issues as $issue) {
            $this->display_admin_notice($issue);
        }
    }

    /**
     * Get critical dependency issues
     *
     * @since    1.0.4
     * @return   array    Critical issues that need attention
     */
    private function get_critical_dependency_issues()
    {
        $issues = array();

        // Check for critical WordPress version issues
        if ($this->dependency_status['wordpress']['status'] === 'outdated') {
            $issues[] = array(
                'type' => 'error',
                'message' => sprintf(
                    __('WordPress version %s is below the minimum required version. Please update WordPress.', 'cdswerx'),
                    $this->dependency_status['wordpress']['version']
                ),
            );
        }

        // Check for critical PHP version issues
        if ($this->dependency_status['php']['status'] === 'outdated') {
            $issues[] = array(
                'type' => 'error',
                'message' => sprintf(
                    __('PHP version %s is below the minimum required version. Please contact your hosting provider.', 'cdswerx'),
                    $this->dependency_status['php']['version']
                ),
            );
        }

        // Check for Hello Elementor issues
        if ($this->dependency_status['hello_elementor']['status'] === 'missing') {
            $issues[] = array(
                'type' => 'warning',
                'message' => __('Hello Elementor theme is not installed. CDSWerx is running in independent mode.', 'cdswerx'),
            );
        }

        return $issues;
    }

    /**
     * Display admin notice
     *
     * @since    1.0.4
     * @param    array    $notice    Notice information
     */
    private function display_admin_notice($notice)
    {
        $class = 'notice notice-' . $notice['type'] . ' is-dismissible';
        printf('<div class="%s"><p><strong>CDSWerx:</strong> %s</p></div>', esc_attr($class), esc_html($notice['message']));
    }

    /**
     * Log dependency event
     *
     * @since    1.0.4
     * @param    string    $event_type    Type of dependency event
     * @param    array     $data          Event data
     */
    private function log_dependency_event($event_type, $data = array())
    {
        $dependency_log = get_option('cdswerx_dependency_log', array());
        
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'event_type' => $event_type,
            'data' => $data,
            'dependency_status_snapshot' => $this->dependency_status,
        );

        $dependency_log[] = $log_entry;

        // Keep only last 100 entries
        if (count($dependency_log) > 100) {
            $dependency_log = array_slice($dependency_log, -100);
        }

        update_option('cdswerx_dependency_log', $dependency_log);
    }

    /**
     * Get dependency status
     *
     * @since    1.0.4
     * @return   array    Current dependency status
     */
    public function get_dependency_status()
    {
        return $this->dependency_status;
    }

    /**
     * Get validation rules
     *
     * @since    1.0.4
     * @return   array    Current validation rules
     */
    public function get_validation_rules()
    {
        return $this->validation_rules;
    }

    /**
     * Update validation rules
     *
     * @since    1.0.4
     * @param    array    $rules    New validation rules
     * @return   bool     Success status
     */
    public function update_validation_rules($rules)
    {
        $this->validation_rules = array_merge($this->validation_rules, $rules);
        return update_option('cdswerx_validation_rules', $this->validation_rules);
    }

    /**
     * Get checker name
     *
     * @since    1.0.4
     * @return   string    Dependency checker name
     */
    public function get_checker_name()
    {
        return $this->checker_name;
    }

    /**
     * Check if Hello Elementor is available (installed and meets requirements)
     *
     * @since    1.0.4
     * @return   bool    True if Hello Elementor is available, false otherwise
     */
    public function is_hello_elementor_available()
    {
        $this->check_hello_elementor_dependency();
        return $this->dependency_status['hello_elementor']['status'] === 'satisfied';
    }

    /**
     * Get comprehensive Hello Elementor information
     *
     * @since    1.0.4
     * @return   array    Hello Elementor information array
     */
    public function get_hello_elementor_info()
    {
        $hello_theme = wp_get_theme('hello-elementor');
        $current_theme = wp_get_theme();
        
        $info = [
            'installed' => $hello_theme->exists(),
            'active' => ($current_theme->get_template() === 'hello-elementor'),
            'version' => $hello_theme->exists() ? $hello_theme->get('Version') : null,
            'name' => $hello_theme->exists() ? $hello_theme->get('Name') : 'Hello Elementor',
            'description' => $hello_theme->exists() ? $hello_theme->get('Description') : null,
            'author' => $hello_theme->exists() ? $hello_theme->get('Author') : 'Elementor Team',
            'template_directory' => $hello_theme->exists() ? $hello_theme->get_template_directory() : null,
            'stylesheet_directory' => $hello_theme->exists() ? $hello_theme->get_stylesheet_directory() : null,
            'parent_theme' => null
        ];

        // Check if it's a child theme
        if ($hello_theme->exists() && $hello_theme->parent()) {
            $info['parent_theme'] = [
                'name' => $hello_theme->parent()->get('Name'),
                'version' => $hello_theme->parent()->get('Version'),
                'template' => $hello_theme->parent()->get_template()
            ];
        }

        // Add dependency status
        $this->check_hello_elementor_dependency();
        $info['dependency_status'] = $this->dependency_status['hello_elementor'] ?? [];
        $info['meets_requirements'] = ($info['dependency_status']['status'] ?? 'unknown') === 'satisfied';

        return $info;
    }

    /**
     * Get checker version
     *
     * @since    1.0.4
     * @return   string    Dependency checker version
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * Get comprehensive Hello Elementor status including independent mode
     *
     * @since    1.1.0
     * @return   array    Complete Hello Elementor status information
     */
    public function get_hello_elementor_status()
    {
        $basic_info = $this->get_hello_elementor_info();
        $dependency_status = $this->dependency_status['hello_elementor'] ?? array();
        
        $status = array_merge($basic_info, array(
            'dependency_status' => $dependency_status,
            'requirements_met' => empty($dependency_status['missing_requirements'] ?? array()),
            'independent_mode' => array(
                'available' => $this->independent_manager !== null,
                'active' => $this->independent_manager ? $this->independent_manager->is_independent_mode_active() : false,
                'fallback_functions' => $this->independent_manager ? $this->independent_manager->get_available_fallback_functions() : array(),
                'theme_settings' => $this->independent_manager ? $this->independent_manager->get_theme_settings_fallbacks() : array()
            ),
            'state' => $this->determine_hello_elementor_state(),
            'message' => $this->get_hello_elementor_status_message(),
            'action_required' => $this->get_hello_elementor_required_action()
        ));

        return $status;
    }

    /**
     * Determine the current Hello Elementor state
     *
     * @since    1.1.0
     * @return   string    Current state identifier
     */
    private function determine_hello_elementor_state()
    {
        $dependency_status = $this->dependency_status['hello_elementor'] ?? array();
        $status = $dependency_status['status'] ?? 'unknown';
        
        if ($this->independent_manager && $this->independent_manager->is_independent_mode_active()) {
            return 'independent_mode';
        }
        
        switch ($status) {
            case 'satisfied':
                return 'satisfied';
            case 'partially_satisfied':
                return 'partially_satisfied';
            case 'missing':
                return 'missing';
            case 'outdated':
                return 'outdated';
            default:
                return 'unknown';
        }
    }

    /**
     * Get Hello Elementor status message
     *
     * @since    1.1.0
     * @return   string    Human-readable status message
     */
    private function get_hello_elementor_status_message()
    {
        $state = $this->determine_hello_elementor_state();
        
        switch ($state) {
            case 'satisfied':
                return 'Hello Elementor theme is installed, active, and all requirements are met.';
            case 'partially_satisfied':
                return 'Hello Elementor theme is installed but some requirements are not met.';
            case 'missing':
                return 'Hello Elementor theme is not installed.';
            case 'outdated':
                return 'Hello Elementor theme is installed but needs to be updated.';
            case 'independent_mode':
                return 'CDSWerx Independent Mode is active - providing Hello Elementor compatibility layer.';
            default:
                return 'Hello Elementor dependency status is unknown.';
        }
    }

    /**
     * Get required action for Hello Elementor dependency
     *
     * @since    1.1.0
     * @return   string|null    Required action identifier or null if no action needed
     */
    private function get_hello_elementor_required_action()
    {
        $state = $this->determine_hello_elementor_state();
        
        switch ($state) {
            case 'missing':
                return 'install_hello_elementor';
            case 'outdated':
                return 'update_hello_elementor';
            case 'partially_satisfied':
                return 'fix_hello_elementor_issues';
            case 'independent_mode':
            case 'satisfied':
            default:
                return null;
        }
    }

    /**
     * Get independent mode manager instance
     *
     * @since    1.1.0
     * @return   CDSWerx_Independent_Mode_Manager|null    Independent mode manager instance
     */
    public function get_independent_mode_manager()
    {
        return $this->independent_manager;
    }

    /**
     * Check if independent mode is recommended
     *
     * @since    1.1.0
     * @return   bool    Whether independent mode is recommended
     */
    public function is_independent_mode_recommended()
    {
        $dependency_status = $this->dependency_status['hello_elementor'] ?? array();
        $status = $dependency_status['status'] ?? 'unknown';
        
        // Recommend independent mode if Hello Elementor has issues
        return in_array($status, array('missing', 'outdated', 'partially_satisfied'));
    }
}