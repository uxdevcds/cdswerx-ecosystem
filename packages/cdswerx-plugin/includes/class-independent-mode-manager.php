<?php

/**
 * Independent Mode Manager Class
 *
 * Provides Hello Elementor fallback functions and manages standalone
 * functionality when Hello Elementor theme is not present.
 *
 * @link       https://cdswerx.com
 * @since      1.1.0
 *
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Independent Mode Manager Class
 *
 * Handles Hello Elementor compatibility layer and provides essential
 * functions for independent operation of the CDSWerx ecosystem.
 *
 * @since      1.1.0
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 * @author     CDSWerx <info@cdswerx.com>
 */
class CDSWerx_Independent_Mode_Manager
{
    /**
     * Independent mode manager identifier
     *
     * @since    1.1.0
     * @access   protected
     * @var      string    $manager_name    The string used to identify this manager.
     */
    protected $manager_name;

    /**
     * Current manager version
     *
     * @since    1.1.0
     * @access   protected
     * @var      string    $version    The current version of the manager.
     */
    protected $version;

    /**
     * Independent mode status
     *
     * @since    1.1.0
     * @access   protected
     * @var      bool    $independent_mode_active    Whether independent mode is currently active.
     */
    protected $independent_mode_active;

    /**
     * Hello Elementor fallback functions
     *
     * @since    1.1.0
     * @access   protected
     * @var      array    $fallback_functions    List of functions to provide in independent mode.
     */
    protected $fallback_functions;

    /**
     * Theme settings fallbacks
     *
     * @since    1.1.0
     * @access   protected
     * @var      array    $theme_settings_fallbacks    Default theme settings for independent mode.
     */
    protected $theme_settings_fallbacks;

    /**
     * Initialize the Independent Mode Manager
     *
     * @since    1.1.0
     */
    public function __construct()
    {
        $this->manager_name = 'cdswerx-independent-mode-manager';
        $this->version = '1.1.0';
        $this->independent_mode_active = false;
        
        $this->init_fallback_functions();
        $this->init_theme_settings_fallbacks();
        $this->check_independent_mode_status();
        
        if ($this->independent_mode_active) {
            $this->activate_independent_mode();
        }
    }

    /**
     * Initialize fallback function definitions
     *
     * @since    1.1.0
     */
    private function init_fallback_functions()
    {
        $this->fallback_functions = array(
            'hello_elementor_setup',
            'hello_elementor_scripts_styles',
            'hello_elementor_enqueue_style',
            'hello_elementor_display_header_footer',
            'hello_elementor_get_setting',
            'hello_elementor_check_hide_title',
            'hello_elementor_add_description_meta_tag',
            'hello_elementor_register_elementor_locations',
            'hello_elementor_content_width',
            'hello_header_footer_experiment_active',
            'hello_show_or_hide',
            'hello_get_header_layout_class',
            'hello_get_footer_layout_class'
        );
    }

    /**
     * Initialize theme settings fallbacks
     *
     * @since    1.1.0
     */
    private function init_theme_settings_fallbacks()
    {
        $this->theme_settings_fallbacks = array(
            'hello_header_logo_type' => 'logo',
            'hello_header_tagline_display' => 'yes',
            'hello_header_layout' => 'default',
            'hello_header_width' => 'boxed',
            'hello_header_menu_dropdown' => 'tablet',
            'hello_header_menu_layout' => 'horizontal',
            'hello_footer_logo_type' => 'logo',
            'hello_footer_copyright_display' => 'yes',
            'hello_footer_copyright_text' => get_bloginfo('name') . ' © ' . date('Y'),
            'hello_footer_layout' => 'default',
            'hello_footer_width' => 'boxed'
        );
    }

    /**
     * Check if independent mode should be activated
     *
     * @since    1.1.0
     */
    private function check_independent_mode_status()
    {
        // Check if Hello Elementor theme is active
        $current_theme = get_template();
        $hello_elementor_active = ($current_theme === 'hello-elementor');
        
        // Check if CDSWerx theme is active (which requires Hello Elementor functions)
        $cdswerx_theme_active = ($current_theme === 'cdswerx-theme' || get_stylesheet() === 'cdswerx-theme-child');
        
        // Independent mode is needed if CDSWerx theme is active but Hello Elementor is not
        $this->independent_mode_active = $cdswerx_theme_active && !$hello_elementor_active;
        
        // Allow manual override via option
        if (get_option('cdswerx_force_independent_mode', false)) {
            $this->independent_mode_active = true;
        }
    }

    /**
     * Activate independent mode functionality
     *
     * @since    1.1.0
     */
    private function activate_independent_mode()
    {
        // Provide Hello Elementor fallback functions
        $this->provide_fallback_functions();
        
        // Setup theme support
        $this->setup_theme_support();
        
        // Enqueue independent mode assets
        $this->setup_independent_assets();
        
        // Setup hooks
        $this->setup_independent_hooks();
        
        // Log independent mode activation
        error_log('CDSWerx Independent Mode activated - providing Hello Elementor compatibility layer');
    }

    /**
     * Provide Hello Elementor fallback functions
     *
     * @since    1.1.0
     */
    private function provide_fallback_functions()
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
                    'menu-1' => esc_html__('Header', 'cdswerx-theme'),
                    'menu-2' => esc_html__('Footer', 'cdswerx-theme'),
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
                    wp_get_theme()->get('Version')
                );
                
                wp_enqueue_style(
                    'hello-elementor-theme-style',
                    get_template_directory_uri() . '/theme/theme' . $min_suffix . '.css',
                    array(),
                    wp_get_theme()->get('Version')
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

        // Hello Elementor display header footer function
        if (!function_exists('hello_elementor_display_header_footer')) {
            function hello_elementor_display_header_footer() {
                $hello_elementor_header_footer = true;
                return apply_filters('hello_elementor_header_footer', $hello_elementor_header_footer);
            }
        }

        // Hello Elementor get setting function - CRITICAL for CDSWerx theme
        if (!function_exists('hello_elementor_get_setting')) {
            function hello_elementor_get_setting($setting_id) {
                // Get fallback settings from independent mode manager
                $manager = new CDSWerx_Independent_Mode_Manager();
                return $manager->get_theme_setting_fallback($setting_id);
            }
        }

        // Hello Elementor check hide title function
        if (!function_exists('hello_elementor_check_hide_title')) {
            function hello_elementor_check_hide_title($val) {
                $current_post_id = get_queried_object_id();
                $hide_title = get_post_meta($current_post_id, '_elementor_page_title_display', true);
                
                if ('yes' === $hide_title) {
                    $val = false;
                }
                
                return $val;
            }
        }

        // Hello Elementor add description meta tag function
        if (!function_exists('hello_elementor_add_description_meta_tag')) {
            function hello_elementor_add_description_meta_tag() {
                if (!is_front_page() || !is_home()) {
                    return;
                }
                
                $description = get_bloginfo('description', 'display');
                if ($description) {
                    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
                }
            }
        }

        // Hello Elementor register Elementor locations
        if (!function_exists('hello_elementor_register_elementor_locations')) {
            function hello_elementor_register_elementor_locations($elementor_theme_manager) {
                if (apply_filters('hello_elementor_register_elementor_locations', true)) {
                    $elementor_theme_manager->register_all_core_location();
                }
            }
        }

        // Hello Elementor content width
        if (!function_exists('hello_elementor_content_width')) {
            function hello_elementor_content_width() {
                $GLOBALS['content_width'] = apply_filters('hello_elementor_content_width', 800);
            }
        }

        // Hello header footer experiment active
        if (!function_exists('hello_header_footer_experiment_active')) {
            function hello_header_footer_experiment_active() {
                // Always return true in independent mode to enable header/footer functionality
                return true;
            }
        }

        // Hello show or hide utility function
        if (!function_exists('hello_show_or_hide')) {
            function hello_show_or_hide($setting_id) {
                return ('yes' === hello_elementor_get_setting($setting_id) ? 'show' : 'hide');
            }
        }

        // Hello get header layout class
        if (!function_exists('hello_get_header_layout_class')) {
            function hello_get_header_layout_class() {
                $layout_classes = [];
                
                $header_layout = hello_elementor_get_setting('hello_header_layout');
                if ('inverted' === $header_layout) {
                    $layout_classes[] = 'header-inverted';
                } elseif ('stacked' === $header_layout) {
                    $layout_classes[] = 'header-stacked';
                }
                
                $header_width = hello_elementor_get_setting('hello_header_width');
                if ('full-width' === $header_width) {
                    $layout_classes[] = 'header-full-width';
                }
                
                return implode(' ', $layout_classes);
            }
        }

        // Hello get footer layout class
        if (!function_exists('hello_get_footer_layout_class')) {
            function hello_get_footer_layout_class() {
                $layout_classes = [];
                
                $footer_layout = hello_elementor_get_setting('hello_footer_layout');
                if ('inverted' === $footer_layout) {
                    $layout_classes[] = 'footer-inverted';
                } elseif ('stacked' === $footer_layout) {
                    $layout_classes[] = 'footer-stacked';
                }
                
                $footer_width = hello_elementor_get_setting('hello_footer_width');
                if ('full-width' === $footer_width) {
                    $layout_classes[] = 'footer-full-width';
                }
                
                return implode(' ', $layout_classes);
            }
        }
    }

    /**
     * Get theme setting fallback value
     *
     * @since    1.1.0
     * @param    string    $setting_id    The setting ID to retrieve
     * @return   mixed     The fallback value for the setting
     */
    public function get_theme_setting_fallback($setting_id)
    {
        // Try to get from Elementor kit settings first (if Elementor is active)
        if (class_exists('\Elementor\Plugin')) {
            try {
                $kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
                $kit_settings = $kit->get_settings();
                
                if (isset($kit_settings[$setting_id])) {
                    return $kit_settings[$setting_id];
                }
            } catch (Exception $e) {
                // Fallback to default if Elementor kit access fails
            }
        }
        
        // Return fallback value
        if (isset($this->theme_settings_fallbacks[$setting_id])) {
            return $this->theme_settings_fallbacks[$setting_id];
        }
        
        // Apply filter for custom fallbacks
        return apply_filters('cdswerx_independent_theme_setting_fallback', '', $setting_id);
    }

    /**
     * Setup theme support for independent mode
     *
     * @since    1.1.0
     */
    private function setup_theme_support()
    {
        add_action('after_setup_theme', 'hello_elementor_setup');
        add_action('after_setup_theme', 'hello_elementor_content_width', 0);
        add_action('wp_enqueue_scripts', 'hello_elementor_scripts_styles');
        add_action('wp_enqueue_scripts', 'hello_elementor_enqueue_style', 20);
        add_action('wp_head', 'hello_elementor_add_description_meta_tag');
        add_action('elementor/theme/register_locations', 'hello_elementor_register_elementor_locations');
        add_filter('hello_elementor_page_title', 'hello_elementor_check_hide_title');
    }

    /**
     * Setup independent mode assets
     *
     * @since    1.1.0
     */
    private function setup_independent_assets()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_independent_mode_styles'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_independent_mode_admin_styles'));
    }

    /**
     * Enqueue independent mode styles
     *
     * @since    1.1.0
     */
    public function enqueue_independent_mode_styles()
    {
        // Only enqueue if we're in independent mode
        if (!$this->independent_mode_active) {
            return;
        }

        wp_enqueue_style(
            'cdswerx-independent-mode',
            plugin_dir_url(__FILE__) . '../assets/css/independent-mode.css',
            array(),
            $this->version
        );
    }

    /**
     * Enqueue independent mode admin styles
     *
     * @since    1.1.0
     */
    public function enqueue_independent_mode_admin_styles()
    {
        // Only enqueue if we're in independent mode
        if (!$this->independent_mode_active) {
            return;
        }

        wp_enqueue_style(
            'cdswerx-independent-mode-admin',
            plugin_dir_url(__FILE__) . '../assets/css/independent-mode-admin.css',
            array(),
            $this->version
        );
    }

    /**
     * Setup independent mode hooks
     *
     * @since    1.1.0
     */
    private function setup_independent_hooks()
    {
        // Add admin notices for independent mode
        add_action('admin_notices', array($this, 'display_independent_mode_notice'));
        
        // Add independent mode info to dependency status
        add_filter('cdswerx_dependency_status', array($this, 'add_independent_mode_status'));
        
        // Allow theme settings customization in independent mode
        add_action('customize_register', array($this, 'add_independent_mode_customizer_settings'));
    }

    /**
     * Display independent mode admin notice
     *
     * @since    1.1.0
     */
    public function display_independent_mode_notice()
    {
        if (!$this->independent_mode_active) {
            return;
        }

        $class = 'notice notice-info';
        $message = __('CDSWerx Independent Mode is active. Hello Elementor compatibility layer is providing theme functions.', 'cdswerx');

        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }

    /**
     * Add independent mode status to dependency status
     *
     * @since    1.1.0
     * @param    array    $status    Current dependency status
     * @return   array    Modified dependency status
     */
    public function add_independent_mode_status($status)
    {
        $status['independent_mode'] = array(
            'active' => $this->independent_mode_active,
            'version' => $this->version,
            'functions_provided' => count($this->fallback_functions),
            'theme_settings_available' => count($this->theme_settings_fallbacks)
        );

        return $status;
    }

    /**
     * Add independent mode customizer settings
     *
     * @since    1.1.0
     * @param    WP_Customize_Manager    $wp_customize    Theme Customizer object
     */
    public function add_independent_mode_customizer_settings($wp_customize)
    {
        if (!$this->independent_mode_active) {
            return;
        }

        // Add independent mode section
        $wp_customize->add_section('cdswerx_independent_mode', array(
            'title' => __('CDSWerx Independent Mode', 'cdswerx'),
            'priority' => 30,
        ));

        // Add setting for forcing independent mode
        $wp_customize->add_setting('cdswerx_force_independent_mode', array(
            'default' => false,
            'transport' => 'refresh',
        ));

        $wp_customize->add_control('cdswerx_force_independent_mode', array(
            'label' => __('Force Independent Mode', 'cdswerx'),
            'description' => __('Enable independent mode even when Hello Elementor is available', 'cdswerx'),
            'section' => 'cdswerx_independent_mode',
            'type' => 'checkbox',
        ));
    }

    /**
     * Check if independent mode is active
     *
     * @since    1.1.0
     * @return   bool    Whether independent mode is active
     */
    public function is_independent_mode_active()
    {
        return $this->independent_mode_active;
    }

    /**
     * Get available fallback functions
     *
     * @since    1.1.0
     * @return   array    List of available fallback functions
     */
    public function get_available_fallback_functions()
    {
        return $this->fallback_functions;
    }

    /**
     * Get theme settings fallbacks
     *
     * @since    1.1.0
     * @return   array    Theme settings fallback values
     */
    public function get_theme_settings_fallbacks()
    {
        return $this->theme_settings_fallbacks;
    }

    /**
     * Force activate independent mode
     *
     * @since    1.1.0
     */
    public function force_activate_independent_mode()
    {
        update_option('cdswerx_force_independent_mode', true);
        $this->independent_mode_active = true;
        $this->activate_independent_mode();
    }

    /**
     * Deactivate independent mode
     *
     * @since    1.1.0
     */
    public function deactivate_independent_mode()
    {
        update_option('cdswerx_force_independent_mode', false);
        $this->independent_mode_active = false;
    }
}