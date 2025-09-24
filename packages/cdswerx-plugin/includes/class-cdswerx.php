<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 *
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 * @author     CDSWerx <info@cdswerx.com>
 */
class Cdswerx
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Cdswerx_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined("CDSWERX_VERSION")) {
            $this->version = CDSWERX_VERSION;
        } else {
            $this->version = "1.0.0";
        }
        $this->plugin_name = "cdswerx";

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Cdswerx_Loader. Orchestrates the hooks of the plugin.
     * - Cdswerx_i18n. Defines internationalization functionality.
     * - Cdswerx_Admin. Defines all hooks for the admin area.
     * - Cdswerx_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            "includes/class-cdswerx-loader.php";

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            "includes/class-cdswerx-i18n.php";

        /**
         * The class responsible for user roles and user management functionality.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            "admin/class/class-cdswerx-user-roles.php";

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            "admin/class/class-cdswerx-admin.php";

        /**
         * The class responsible for defining extension-specific functionality.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            "admin/class/class-cdswerx-extensions.php";

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            "public/class-cdswerx-public.php";

        /**
         * The class responsible for theme integration and ecosystem management.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            "includes/class-theme-integration.php";

        /**
         * Custom Code System Classes
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            "includes/class-cdswerx-database.php";
        require_once plugin_dir_path(dirname(__FILE__)) .
            "includes/class-cdswerx-code-manager.php";
        require_once plugin_dir_path(dirname(__FILE__)) .
            "includes/class-cdswerx-code-injector.php";

        /**
         * The class responsible for injecting custom code into frontend.
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            "includes/class-cdswerx-code-injector.php";

        /**
         * Typography System Classes
         */
        require_once plugin_dir_path(dirname(__FILE__)) .
            "includes/class-typography-css-reader.php";
        require_once plugin_dir_path(dirname(__FILE__)) .
            "includes/class-cdswerx-typography-sync.php";

        $this->loader = new Cdswerx_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Cdswerx_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new Cdswerx_i18n();

        $this->loader->add_action(
            "plugins_loaded",
            $plugin_i18n,
            "load_plugin_textdomain",
        );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Cdswerx_Admin(
            $this->get_plugin_name(),
            $this->get_version(),
        );
        $plugin_extensions = new Cdswerx_Extensions(
            $this->get_plugin_name(),
            $this->get_version(),
        );

        // Register the admin styles and scripts
        $this->loader->add_action(
            "admin_enqueue_scripts",
            $plugin_admin,
            "enqueue_styles",
        );
        $this->loader->add_action(
            "admin_enqueue_scripts",
            $plugin_admin,
            "enqueue_scripts",
        );
        // Register the admin menu for the plugin
        $this->loader->add_action(
            "admin_menu",
            $plugin_admin,
            "register_cdswerx_admin_menu",
        );
        // Register settings for the plugin
        $this->loader->add_action(
            "admin_init",
            $plugin_admin,
            "register_cdswerx_admin_settings",
        );
        // Register settings to show admin notices/messages
        $this->loader->add_action(
            "admin_notices",
            $plugin_admin,
            "display_cdswerx_admin_notices",
        );
        // Register global activation prompt
        $this->loader->add_action(
            "admin_notices",
            $plugin_admin,
            "show_global_activation_prompt",
        );

        //Only needed if you want AJAX functionality (e.g., saving settings without page reload).
        //AJAX actions for dynamic features
        //$this->loader->add_action( 'wp_ajax_cdswerx_save_settings', $plugin_admin, 'save_settings' );
        //$this->loader->add_action( 'wp_ajax_cdswerx_reset_settings', $plugin_admin, 'reset_settings' );

        // AJAX handler for dismissing activation prompt
        $this->loader->add_action(
            "wp_ajax_cdswerx_dismiss_activation_prompt",
            $plugin_admin,
            "handle_dismiss_activation_prompt",
        );

        // Custom Code AJAX handlers
        $this->loader->add_action(
            "wp_ajax_cdswerx_save_custom_code",
            $plugin_admin,
            "handle_save_custom_code",
        );
        $this->loader->add_action(
            "wp_ajax_cdswerx_update_custom_code",
            $plugin_admin,
            "handle_update_custom_code",
        );
        $this->loader->add_action(
            "wp_ajax_cdswerx_load_custom_code", 
            $plugin_admin,
            "handle_load_custom_code",
        );
        $this->loader->add_action(
            "wp_ajax_cdswerx_get_code_by_id",
            $plugin_admin,
            "handle_get_code_by_id",
        );
        $this->loader->add_action(
            "wp_ajax_cdswerx_delete_custom_code",
            $plugin_admin,
            "handle_delete_custom_code",
        );
        $this->loader->add_action(
            "wp_ajax_cdswerx_toggle_code_status",
            $plugin_admin,
            "handle_toggle_code_status",
        );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Cdswerx_Public(
            $this->get_plugin_name(),
            $this->get_version(),
        );

        $this->loader->add_action(
            "wp_enqueue_scripts",
            $plugin_public,
            "enqueue_styles",
        );
        $this->loader->add_action(
            "wp_enqueue_scripts",
            $plugin_public,
            "enqueue_scripts",
        );

        // Initialize custom code injection (hooks are registered in constructor)
        $code_injector = new CDSWerx_Code_Injector();
        // TODO: Implement enqueue_non_critical_css method or remove this hook
        /*
        $this->loader->add_action(
            "wp_enqueue_scripts",
            $code_injector,
            "enqueue_non_critical_css",
            20
        );
        */
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Cdswerx_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
