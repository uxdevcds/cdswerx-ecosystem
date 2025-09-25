<?php
/**
 * CDSWerx Admin Manager
 *
 * Admin interface coordination and dashboard management
 * Consolidates admin functionality from oversized classes
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
 * CDSWerx Admin Manager Class
 * 
 * Handles admin interface, dashboard coordination, and settings management
 * Consolidates functionality from class-cdswerx-admin.php (2,969 lines)
 * 
 * @since 2.0.0
 */
class CDSWerx_Admin_Manager {
    
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
     * Admin menu pages
     * 
     * @since 2.0.0
     * @access private
     * @var array $menu_pages
     */
    private $menu_pages = array();
    
    /**
     * CDSWerx fields storage for template rendering (migrated from legacy class)
     *
     * @since 2.0.0
     * @access private
     * @var array $cdswerx_fields Array of fields for template rendering.
     */
    private $cdswerx_fields = array();
    
    /**
     * Initialize admin manager
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
        
        // Migrate initialization from legacy admin class
        $this->init_admin_hooks();
    }
    
    /**
     * Initialize admin hooks (migrated from legacy class)
     * 
     * @since 2.0.0
     */
    private function init_admin_hooks() {
        // Hook for handling janice user actions
        add_action("admin_init", [$this, "handle_janice_user_actions"]);

        // Hook for handling corey user actions
        add_action("admin_init", [$this, "handle_corey_user_actions"]);

        // Hook for handling corey removal actions
        add_action("admin_init", [$this, "handle_corey_removal_actions"]);

        // Hook for handling reset activation prompts action
        add_action("admin_init", [$this, "handle_reset_activation_prompts"]);

        // Hook for handling access control setup
        add_action("admin_init", [$this, "handle_access_control_setup"]);

        // Hook for handling permanent disable actions
        add_action("admin_init", [$this, "handle_permanent_disable_actions"]);

        // Hook for user deactivation tracking
        add_action("delete_user", [$this, "handle_user_deletion"]);
        add_action("remove_user_from_blog", [$this, "handle_user_removal"], 10, 2);
        add_action("set_user_role", [$this, "handle_user_role_change"], 10, 3);

        // Hook for plugin deactivation to reset permanent disable
        register_deactivation_hook(dirname(__FILE__, 3) . "/cdswerx.php", [
            $this,
            "reset_permanent_disable_on_deactivation",
        ]);

        // Ensure admin JS/CSS is loaded on CDSWerx admin pages
        add_action("admin_enqueue_scripts", [$this, "enqueue_scripts"]);
        add_action("admin_enqueue_scripts", [$this, "enqueue_styles"]);
    }
    
    /**
     * Register admin hooks
     * 
     * @since 2.0.0
     * @param Cdswerx_Loader $loader The loader instance
     */
    public function register_hooks($loader) {
        // Admin initialization
        $loader->add_action('admin_init', $this, 'admin_init');
        $loader->add_action('admin_menu', $this, 'add_admin_menu');
        
        // Admin assets
        $loader->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
        $loader->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts');
        
        // AJAX handlers
        $loader->add_action('wp_ajax_cdswerx_admin_action', $this, 'handle_admin_ajax');
        
        // Settings handlers
        $loader->add_action('admin_post_cdswerx_save_settings', $this, 'save_settings');
    }
    
    /**
     * Initialize admin functionality
     * 
     * @since 2.0.0
     */
    public function admin_init() {
        // Register settings
        $this->register_settings();
        
        // Check user permissions
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Initialize admin components
        do_action('cdswerx_admin_init', $this);
    }
    
    /**
     * Add admin menu pages
     * 
     * @since 2.0.0
     */
    public function add_admin_menu() {
        // Main menu page
        $this->menu_pages['main'] = add_menu_page(
            __('CDSWerx', 'cdswerx'),
            __('CDSWerx', 'cdswerx'),
            'manage_options',
            'cdswerx',
            array($this, 'display_main_page'),
            'dashicons-admin-generic',
            30
        );
        
        // Dashboard submenu
        $this->menu_pages['dashboard'] = add_submenu_page(
            'cdswerx',
            __('Dashboard', 'cdswerx'),
            __('Dashboard', 'cdswerx'),
            'manage_options',
            'cdswerx',
            array($this, 'display_dashboard_page')
        );
        
        // Extensions submenu
        $this->menu_pages['extensions'] = add_submenu_page(
            'cdswerx',
            __('Extensions', 'cdswerx'),
            __('Extensions', 'cdswerx'),
            'manage_options',
            'cdswerx-extensions',
            array($this, 'display_extensions_page')
        );
        
        // Advanced submenu
        $this->menu_pages['advanced'] = add_submenu_page(
            'cdswerx',
            __('Advanced', 'cdswerx'),
            __('Advanced', 'cdswerx'),
            'manage_options',
            'cdswerx-advanced',
            array($this, 'display_advanced_page')
        );
    }
    
    /**
     * Register plugin settings
     * 
     * @since 2.0.0
     */
    public function register_settings() {
        // Core settings
        register_setting('cdswerx_settings', 'cdswerx_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));
        
        // Extension settings
        register_setting('cdswerx_extensions', 'cdswerx_extensions', array(
            'sanitize_callback' => array($this, 'sanitize_extensions')
        ));
        
        // Advanced settings
        register_setting('cdswerx_advanced', 'cdswerx_advanced', array(
            'sanitize_callback' => array($this, 'sanitize_advanced')
        ));
    }
    
    /**
     * Register the stylesheets for the admin area (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function enqueue_styles() {
        // Plugin admin CSS
        wp_enqueue_style(
            $this->plugin_name,
            CDSWERX_PLUGIN_URL . "admin/css/cdswerx-admin.css",
            [],
            $this->version,
            "all"
        );
        
        // Only load on CDSWerx plugin admin pages
        if (isset($_GET["page"]) && strpos($_GET["page"], "cds") === 0) {
            // UIkit CSS (CDN)
            wp_enqueue_style(
                "uikit-css",
                "https://cdn.jsdelivr.net/npm/uikit@3.19.4/dist/css/uikit.min.css",
                [],
                "3.19.4"
            );
            
            // Custom Code Manager CSS
            wp_enqueue_style(
                "cdswerx-custom-code",
                CDSWERX_PLUGIN_URL . "admin/css/cdswerx-custom-code.css",
                [$this->plugin_name],
                $this->version,
                "all"
            );
        }
    }
    
    /**
     * Register the JavaScript for the admin area (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            CDSWERX_PLUGIN_URL . "admin/js/cdswerx-admin.js",
            ["jquery"],
            $this->version,
            false
        );
        
        // Localize script for main admin functionality
        wp_localize_script(
            $this->plugin_name,
            "cdswerx_admin",
            [
                "ajax_url" => admin_url("admin-ajax.php"),
                "nonce" => wp_create_nonce("cdswerx_admin_nonce"),
                "plugin_url" => CDSWERX_PLUGIN_URL,
            ]
        );
        
        // Only load on CDSWerx plugin admin pages (not theme pages)
        if (isset($_GET["page"]) && strpos($_GET["page"], "cdswerx") === 0) {
            // UIkit JS (CDN)
            wp_enqueue_script(
                "uikit-js",
                "https://cdn.jsdelivr.net/npm/uikit@3.19.4/dist/js/uikit.min.js",
                [],
                "3.19.4"
            );
            
            // WordPress Code Editor Dependencies
            wp_enqueue_script('code-editor');
            wp_enqueue_style('code-editor');
            wp_enqueue_script('csslint');
            wp_enqueue_script('jshint');
            
            // WordPress Code Editor Settings
            wp_enqueue_code_editor(array('type' => 'text/css'));
            wp_enqueue_code_editor(array('type' => 'application/javascript'));
            
            // Custom Code Manager JS (with CodeMirror integration)
            wp_enqueue_script(
                "cdswerx-custom-code",
                CDSWERX_PLUGIN_URL . "admin/js/cdswerx-custom-code.js",
                ["jquery", "code-editor", "csslint", "jshint"],
                $this->version . '.' . time(),
                true
            );
            
            // Localize script for AJAX settings
            wp_localize_script(
                "cdswerx-custom-code",
                "cdswerx_ajax",
                [
                    "ajax_url" => admin_url("admin-ajax.php"),
                    "nonce" => wp_create_nonce("cdswerx_custom_code_nonce"),
                    "plugin_url" => CDSWERX_PLUGIN_URL,
                    "debug_mode" => true,
                ]
            );
        }
    }
    
    /**
     * Register CDSWerx admin menu (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function register_cdswerx_admin_menu() {
        // Main CDSWerx menu (Parent)
        add_menu_page(
            __("CDSWerx", "cdswerx"), // page title
            __("CDSWerx", "cdswerx"), // menu title
            "manage_options", // capability
            "cdswerx", // menu slug
            [$this, "display_dashboard"], // callback
            "dashicons-admin-tools", // icon
            30 // position
        );

        // Dashboard submenu (#1) - Overview, Notifications, Settings tabs
        add_submenu_page(
            "cdswerx", // parent slug
            __("Dashboard", "cdswerx"), // page title
            __("Dashboard", "cdswerx"), // menu title
            "manage_options", // capability
            "cdswerx", // menu slug (same as parent for default)
            [$this, "display_dashboard"] // callback
        );

        // Extensions submenu (#2) - WordPress Core, Editor/TinyMCE, Elementor, Utilities tabs
        add_submenu_page(
            "cdswerx",
            __("CDS Extensions", "cdswerx"),
            __("CDS Extensions", "cdswerx"),
            "manage_options",
            "cdswerx-extensions",
            [$this, "display_extensions"]
        );

        // Users submenu (#3) - Roles & User Settings, Advanced Management Settings tabs (restricted access)
        if ($this->can_access_user_settings()) {
            add_submenu_page(
                "cdswerx",
                __("Users", "cdswerx"),
                __("Users", "cdswerx"),
                "manage_options",
                "cdswerx-users",
                [$this, "display_users"]
            );
        }
    }
    
    /**
     * Display main admin page
     * 
     * @since 2.0.0
     */
    public function display_main_page() {
        $this->display_dashboard_page();
    }
    
    /**
     * Display dashboard page
     * 
     * @since 2.0.0
     */
    public function display_dashboard_page() {
        // Include dashboard template
        include_once plugin_dir_path(dirname(dirname(__FILE__))) . 'admin/partials/cdswerx-admin-dashboard.php';
    }

    /**
     * Callback for admin display settings (legacy method name compatibility)
     *
     * @since    1.0.0
     */
    public function display_dashboard()
    {
        //define location of Brand Logo
        $logo_url =
            CDSWERX_PLUGIN_URL .
            "assets/images/counter-design-studio-logo-digital-user-experience.svg";
        // Include the admin display template
        include CDSWERX_PLUGIN_PATH .
            "admin/partials/cdswerx-admin-dashboard.php";
    }
    
    /**
     * Display extensions page
     * 
     * @since 2.0.0
     */
    public function display_extensions_page() {
        // Include extensions template
        include_once plugin_dir_path(dirname(dirname(__FILE__))) . 'admin/partials/cdswerx-admin-extensions.php';
    }
    
    /**
     * Display advanced page
     * 
     * @since 2.0.0
     */
    public function display_advanced_page() {
        // Include advanced template
        include_once plugin_dir_path(dirname(dirname(__FILE__))) . 'admin/partials/cdswerx-admin-advanced.php';
    }
    
    /**
     * Handle admin AJAX requests
     * 
     * @since 2.0.0
     */
    public function handle_admin_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_admin_nonce')) {
            wp_die(__('Security check failed', 'cdswerx'));
        }
        
        // Get action
        $action = sanitize_text_field($_POST['admin_action']);
        
        // Route to appropriate handler
        switch ($action) {
            case 'save_settings':
                $this->ajax_save_settings();
                break;
            case 'reset_settings':
                $this->ajax_reset_settings();
                break;
            default:
                wp_send_json_error(__('Invalid action', 'cdswerx'));
        }
    }
    
    /**
     * Save settings via AJAX
     * 
     * @since 2.0.0
     */
    private function ajax_save_settings() {
        // Validate and save settings
        $settings = $this->sanitize_settings($_POST['settings']);
        update_option('cdswerx_settings', $settings);
        
        wp_send_json_success(__('Settings saved successfully', 'cdswerx'));
    }
    
    /**
     * Reset settings via AJAX
     * 
     * @since 2.0.0
     */
    private function ajax_reset_settings() {
        // Reset to defaults
        delete_option('cdswerx_settings');
        
        wp_send_json_success(__('Settings reset successfully', 'cdswerx'));
    }
    
    /**
     * Save settings (POST handler)
     * 
     * @since 2.0.0
     */
    public function save_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'cdswerx_save_settings')) {
            wp_die(__('Security check failed', 'cdswerx'));
        }
        
        // Save settings and redirect
        $settings = $this->sanitize_settings($_POST['cdswerx_settings']);
        update_option('cdswerx_settings', $settings);
        
        wp_redirect(add_query_arg('updated', 'true', wp_get_referer()));
        exit;
    }
    
    /**
     * Sanitize main settings
     * 
     * @since 2.0.0
     * @param array $input Raw input data
     * @return array Sanitized settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['advanced_css'])) {
            $sanitized['advanced_css'] = wp_strip_all_tags($input['advanced_css']);
        }
        
        if (isset($input['custom_code'])) {
            $sanitized['custom_code'] = wp_kses_post($input['custom_code']);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize extension settings
     * 
     * @since 2.0.0
     * @param array $input Raw input data
     * @return array Sanitized settings
     */
    public function sanitize_extensions($input) {
        $sanitized = array();
        
        // Sanitize extension-specific settings
        foreach ($input as $key => $value) {
            if (is_bool($value)) {
                $sanitized[$key] = (bool) $value;
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize advanced settings
     * 
     * @since 2.0.0
     * @param array $input Raw input data
     * @return array Sanitized settings
     */
    public function sanitize_advanced($input) {
        $sanitized = array();
        
        // Sanitize advanced settings
        if (isset($input['debug_mode'])) {
            $sanitized['debug_mode'] = (bool) $input['debug_mode'];
        }
        
        if (isset($input['cache_enabled'])) {
            $sanitized['cache_enabled'] = (bool) $input['cache_enabled'];
        }
        
        return $sanitized;
    }
    
    /**
     * Get current settings
     * 
     * @since 2.0.0
     * @return array Current settings
     */
    public function get_settings() {
        return get_option('cdswerx_settings', array());
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
    
    /**
     * Check if current user can access user settings (migrated from legacy class)
     *
     * @since 2.0.0
     * @return bool True if user can access settings, false otherwise
     */
    public function can_access_user_settings() {
        // First check if current user is Janice
        if ($this->is_current_user_janice()) {
            return true;
        }

        // Check if Janice exists on the site
        $janice_user = get_user_by("login", "janice");
        if ($janice_user && $janice_user->user_email === "webmaster@counterdesign.ca") {
            return false;
        }

        // Janice doesn't exist, check for site_admin_manager users
        $site_admin_managers = get_users(['role' => 'site_admin_manager']);

        if (!empty($site_admin_managers)) {
            // Check if there's a designated manager
            $designated_manager_id = get_option("cdswerx_designated_manager", 0);
            if ($designated_manager_id) {
                $current_user = wp_get_current_user();
                if ($current_user->ID === $designated_manager_id) {
                    return true;
                } else {
                    return false;
                }
            }

            // No designated manager, check if current user is one of the site_admin_managers
            $current_user = wp_get_current_user();
            foreach ($site_admin_managers as $manager) {
                if ($manager->ID === $current_user->ID) {
                    return true;
                }
            }
            return false;
        }

        // No Janice, no site_admin_managers - check if current user is administrator
        if (current_user_can("administrator")) {
            return true;
        }

        return false;
    }
    
    /**
     * Check if current user is Janice (migrated from legacy class)
     *
     * @since 2.0.0
     * @return bool True if current user is Janice, false otherwise
     */
    public function is_current_user_janice() {
        $current_user = wp_get_current_user();
        return $current_user->user_login === "janice" && 
               $current_user->user_email === "webmaster@counterdesign.ca";
    }
    
    /**
     * Add plugin action links (migrated from legacy class)
     *
     * @since 2.0.0
     * @param array $links Plugin action links
     * @return array Modified plugin action links
     */
    public function add_action_links($links) {
        /*
         *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
         */
        $settings_link = [
            '<a href="' .
                admin_url("options-general.php?page=" . $this->plugin_name) .
                '">' .
                __("Settings", "cdswerx") .
                "</a>",
        ];
        return array_merge($settings_link, $links);
    }
    
    /**
     * Register admin settings (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function register_cdswerx_admin_settings() {
        // Register main settings group
        register_setting("cdswerx_settings_group", "cdswerx_options", [
            $this,
            "validate",
        ]);

        // Register extensions settings group
        register_setting("cdswerx_extensions_group", "cdswerx_extensions", [
            $this,
            "validate_extensions",
        ]);

        add_settings_section(
            "cdswerx_main_section",
            __("Main Settings", "cdswerx"),
            function () {
                echo "<p>Main settings for CDSWerx.</p>";
            },
            "cdswerx_settings_group",
        );

        $fields = [];

        // Do not use add_settings_field for UIKit layout. Render fields manually in the template using uk-form-stacked.
        $this->cdswerx_fields = $fields; // Store for use in template
    }
    
    /**
     * Handle Janice user management actions (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function handle_janice_user_actions() {
        if (!$this->can_access_user_settings()) {
            error_log("CDSWerx Janice Actions: Access denied for current user");
            return;
        }

        // Handle janice activation
        if (!empty($_GET["cdswerx_activate_janice"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_activate_janice")) {
                wp_die("Security check failed");
            }

            $update_email = !empty($_GET["update_email"]);
            $result = $this->create_or_update_janice_user();

            // Store result message
            if ($result["success"]) {
                $message = $result["message"];
                if ($result["created"] || $result["updated"]) {
                    $message .=
                        "<br><strong>Reminder:</strong> Default password is <code>passwordhere</code>. Please change after first login.";
                }
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "success",
                        "message" => $message,
                    ],
                    60,
                );
            } else {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "error",
                        "message" => $result["message"],
                    ],
                    60,
                );
            }

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }

        // Handle janice recreation
        if (!empty($_GET["cdswerx_recreate_janice"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_recreate_janice")) {
                wp_die("Security check failed");
            }

            $result = $this->create_or_update_janice_user();

            if ($result["success"]) {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "success",
                        "message" => "Janice user recreated successfully.",
                    ],
                    60,
                );
            } else {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "error",
                        "message" => $result["message"],
                    ],
                    60,
                );
            }

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }

        // Handle role recreation
        if (!empty($_GET["cdswerx_recreate_role"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_recreate_role")) {
                wp_die("Security check failed");
            }

            $result = $this->recreate_site_admin_manager_role();

            if ($result) {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "success",
                        "message" =>
                        "Site Admin Manager role recreated successfully.",
                    ],
                    60,
                );
            } else {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "error",
                        "message" =>
                        "Failed to recreate Site Admin Manager role.",
                    ],
                    60,
                );
            }

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }
    }
    
    /**
     * Handle permanent disable Corey actions (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function handle_permanent_disable_actions() {
        if (!$this->can_access_user_settings()) {
            error_log(
                "CDSWerx Permanent Disable: Access denied for current user",
            );
            return;
        }

        // Handle permanent disable
        if (!empty($_GET["cdswerx_permanent_disable_corey"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_permanent_disable_corey")) {
                error_log(
                    "CDSWerx Permanent Disable: Nonce verification failed",
                );
                wp_die("Security check failed");
            }

            error_log(
                "CDSWerx Permanent Disable: Starting permanent disable process",
            );

            // Check if current user is Corey
            $current_user = wp_get_current_user();
            $is_current_corey =
                $current_user->user_login === "corey" &&
                $current_user->user_email === "cjason@cloudsurfingmedia.com";

            if ($is_current_corey) {
                error_log(
                    "CDSWerx Permanent Disable: Current user is Corey - showing warning",
                );
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "warning",
                        "message" =>
                        "Warning: You are currently logged in as Corey. Permanent disable completed, but your current session remains active.",
                    ],
                    60,
                );
            }

            // Set permanent disable flag
            update_option("cdswerx_corey_permanently_disabled", true);

            // Clear any existing Corey activation flags
            delete_option("cdswerx_corey_activated");

            // Reset activation prompts to hide Corey
            delete_option("cdswerx_activation_prompts_globally_dismissed");

            error_log(
                "CDSWerx Permanent Disable: Corey permanently disabled successfully",
            );

            set_transient(
                "cdswerx_admin_message",
                [
                    "type" => "success",
                    "message" =>
                    "Corey has been permanently disabled. All Corey functionality is now hidden from the plugin interface.",
                ],
                60,
            );

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }
    }
    
    /**
     * Create or update Janice user (migrated from legacy class)
     *
     * @since 2.0.0
     * @param int $user_id Optional specific user ID
     * @return array Result array with success status and message
     */
    public function create_or_update_janice_user($user_id = null) {
        $username = "janice";
        $email = "webmaster@counterdesign.ca";
        $user = null;
        $result = [
            "success" => false,
            "message" => "",
            "created" => false,
            "updated" => false,
        ];

        // If specific user ID provided, use that user
        if ($user_id) {
            $user = get_user_by("id", $user_id);
        } else {
            $user = get_user_by("login", $username);
        }

        if ($user) {
            // Update email if different
            if ($user->user_email !== $email) {
                $update_result = wp_update_user([
                    "ID" => $user->ID,
                    "user_email" => $email,
                ]);

                if (!is_wp_error($update_result)) {
                    $result["updated"] = true;
                    // Refresh user object
                    $user = get_user_by("id", $user->ID);
                }
            }

            // User exists, add roles if not already present
            $roles_added = $this->assign_user_roles($user);
            $result["success"] = true;

            if ($result["updated"]) {
                $result["message"] =
                    "Janice updated successfully. Email updated and roles assigned: " .
                    implode(", ", $roles_added);
            } else {
                $result["message"] =
                    "Janice roles updated. Added: " .
                    implode(", ", $roles_added);
            }
        } else {
            // Create new user
            $user_id = wp_insert_user([
                "user_login" => $username,
                "user_pass" => "passwordhere",
                "user_email" => $email,
                "display_name" => "Janice",
                "role" => "", // We'll assign roles manually for proper hierarchy
            ]);

            if (!is_wp_error($user_id)) {
                $user = get_user_by("id", $user_id);
                $roles_added = $this->assign_user_roles($user);
                wp_new_user_notification($user_id, null, "user");
                update_user_meta($user_id, "_cdswerx_pw_reminder", 1);

                $result["success"] = true;
                $result["created"] = true;
                $result["message"] =
                    "Janice created successfully with roles: " .
                    implode(", ", $roles_added);
            } else {
                $result["message"] =
                    "Failed to create Janice: " . $user_id->get_error_message();
            }
        }

        // Set activation flag if successful
        if ($result["success"]) {
            update_option("cdswerx_janice_activated", 1);
        }

        return $result;
    }
    
    /**
     * Assign roles with proper hierarchy (migrated from legacy class)
     *
     * @since 2.0.0
     * @param WP_User $user User object
     * @return array Array of roles added
     */
    public function assign_user_roles($user) {
        $roles_to_add = [];
        $roles_added = [];

        // Define role hierarchy based on multisite
        if (is_multisite()) {
            $roles_to_add = ["site_admin_manager", "administrator"];
            // Grant super admin network-wide
            if (!is_super_admin($user->ID)) {
                grant_super_admin($user->ID);
                $roles_added[] = "super_admin";
            }
        } else {
            $roles_to_add = ["site_admin_manager", "administrator"];
        }

        // Add roles if user doesn't already have them
        foreach ($roles_to_add as $role) {
            if (!$user->has_cap($role)) {
                $user->add_role($role);
                $roles_added[] = $role;
            }
        }

        return $roles_added;
    }
    
    /**
     * Recreate Site Admin Manager role (migrated from legacy class)
     *
     * @since 2.0.0
     * @return bool Success status
     */
    public function recreate_site_admin_manager_role() {
        // Remove existing role if it exists
        if (get_role("site_admin_manager")) {
            remove_role("site_admin_manager");
        }

        // Create the role with all capabilities
        $result = add_role(
            "site_admin_manager",
            __("Site Admin Manager", "cdswerx"),
            array_merge(get_role("administrator")->capabilities, [
                "activate_plugins" => true,
                "create_posts" => true,
                "create_users" => true,
                "delete_others_pages" => true,
                "delete_others_posts" => true,
                "delete_pages" => true,
                "delete_plugins" => true,
                "delete_posts" => true,
                "delete_private_pages" => true,
                "delete_private_posts" => true,
                "delete_published_pages" => true,
                "delete_published_posts" => true,
                "delete_themes" => true,
                "delete_users" => true,
                "edit_dashboard" => true,
                "edit_others_pages" => true,
                "edit_others_posts" => true,
                "edit_pages" => true,
                "edit_plugins" => true,
                "edit_posts" => true,
                "edit_private_pages" => true,
                "edit_private_posts" => true,
                "edit_published_pages" => true,
                "edit_published_posts" => true,
                "edit_theme_options" => true,
                "edit_themes" => true,
                "edit_users" => true,
                "export" => true,
                "import" => true,
                "install_languages" => true,
                "install_plugins" => true,
                "install_themes" => true,
                "list_users" => true,
                "manage_categories" => true,
                "manage_forminator" => true,
                "manage_links" => true,
                "manage_options" => true,
                "moderate_comments" => true,
                "promote_users" => true,
                "publish_pages" => true,
                "publish_posts" => true,
                "read" => true,
                "read_private_pages" => true,
                "read_private_posts" => true,
                "remove_users" => true,
                "resume_plugins" => true,
                "resume_themes" => true,
                "switch_themes" => true,
                "unfiltered_html" => true,
                "unfiltered_upload" => true,
                "update_core" => true,
                "update_plugins" => true,
                "update_themes" => true,
                "upload_files" => true,
                "ure_create_capabilities" => true,
                "ure_create_roles" => true,
                "ure_delete_capabilities" => true,
                "ure_delete_roles" => true,
                "ure_edit_roles" => true,
                "ure_manage_options" => true,
                "ure_reset_roles" => true,
                "view_site_health_checks" => true,
            ]),
        );

        return $result !== null;
    }
    
    /**
     * Create or update Corey user (migrated from legacy class)
     *
     * @since 2.0.0
     * @param int $user_id Optional specific user ID
     * @return array Result array with success status and message
     */
    public function create_or_update_corey_user($user_id = null) {
        $username = "corey";
        $email = "cjason@cloudsurfingmedia.com";
        $user = null;
        $result = [
            "success" => false,
            "message" => "",
            "created" => false,
            "updated" => false,
        ];

        // Check if permanently disabled
        if (get_option("cdswerx_corey_permanently_disabled", false)) {
            $result["message"] =
                "Corey is permanently disabled for this website.";
            return $result;
        }

        // If specific user ID provided, use that user
        if ($user_id) {
            $user = get_user_by("id", $user_id);
        } else {
            $user = get_user_by("login", $username);
        }

        if ($user) {
            // Update email if different
            if ($user->user_email !== $email) {
                $update_result = wp_update_user([
                    "ID" => $user->ID,
                    "user_email" => $email,
                ]);

                if (!is_wp_error($update_result)) {
                    $result["updated"] = true;
                    // Refresh user object
                    $user = get_user_by("id", $user->ID);
                }
            }

            // User exists, add roles if not already present
            $roles_added = $this->assign_user_roles($user);
            $result["success"] = true;

            if ($result["updated"]) {
                $result["message"] =
                    "Corey updated successfully. Email updated and roles assigned: " .
                    implode(", ", $roles_added);
            } else {
                $result["message"] =
                    "Corey roles updated. Added: " .
                    implode(", ", $roles_added);
            }
        } else {
            // Create new user
            $user_id = wp_insert_user([
                "user_login" => $username,
                "user_pass" => "passwordhere",
                "user_email" => $email,
                "display_name" => "Corey",
                "role" => "", // We'll assign roles manually for proper hierarchy
            ]);

            if (!is_wp_error($user_id)) {
                $user = get_user_by("id", $user_id);
                $roles_added = $this->assign_user_roles($user);
                wp_new_user_notification($user_id, null, "user");
                update_user_meta($user_id, "_cdswerx_pw_reminder", 1);

                $result["success"] = true;
                $result["created"] = true;
                $result["message"] =
                    "Corey created successfully with roles: " .
                    implode(", ", $roles_added);
            } else {
                $result["message"] =
                    "Failed to create Corey: " . $user_id->get_error_message();
            }
        }

        // Set activation flag if successful
        if ($result["success"]) {
            update_option("cdswerx_corey_activated", 1);
        }

        return $result;
    }
    
    /**
     * Handle Corey user management actions (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function handle_corey_user_actions() {
        if (!$this->can_access_user_settings()) {
            error_log("CDSWerx Corey Actions: Access denied for current user");
            return;
        }

        // Handle corey activation
        if (!empty($_GET["cdswerx_activate_corey"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_activate_corey")) {
                wp_die("Security check failed");
            }

            $update_email = !empty($_GET["update_email"]);
            $result = $this->create_or_update_corey_user();

            // Store result message
            if ($result["success"]) {
                $message = $result["message"];
                if ($result["created"] || $result["updated"]) {
                    $message .=
                        "<br><strong>Reminder:</strong> Default password is <code>passwordhere</code>. Please change after first login.";
                }
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "success",
                        "message" => $message,
                    ],
                    60,
                );
            } else {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "error",
                        "message" => $result["message"],
                    ],
                    60,
                );
            }

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }

        // Handle corey recreation
        if (!empty($_GET["cdswerx_recreate_corey"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_recreate_corey")) {
                wp_die("Security check failed");
            }

            $result = $this->create_or_update_corey_user();

            if ($result["success"]) {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "success",
                        "message" => "Corey user recreated successfully.",
                    ],
                    60,
                );
            } else {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "error",
                        "message" => $result["message"],
                    ],
                    60,
                );
            }

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }

        // Handle permanent disable toggle
        if (!empty($_GET["cdswerx_toggle_corey_disable"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_toggle_corey_disable")) {
                wp_die("Security check failed");
            }

            $is_disabled = get_option(
                "cdswerx_corey_permanently_disabled",
                false,
            );
            $new_status = !$is_disabled;

            update_option("cdswerx_corey_permanently_disabled", $new_status);

            $message = $new_status 
                ? "Corey has been permanently disabled." 
                : "Corey permanent disable has been removed.";

            set_transient(
                "cdswerx_admin_message",
                [
                    "type" => "success",
                    "message" => $message,
                ],
                60,
            );

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }
    }
    
    /**
     * Handle Corey removal actions (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function handle_corey_removal_actions() {
        if (!$this->can_access_user_settings()) {
            error_log("CDSWerx Corey Removal: Access denied for current user");
            return;
        }

        // Handle corey removal
        if (!empty($_GET["cdswerx_remove_corey"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_remove_corey")) {
                wp_die("Security check failed");
            }

            // Get Corey user
            $corey = get_user_by("login", "corey");
            if (!$corey) {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "error",
                        "message" => "Corey user not found.",
                    ],
                    60,
                );
                wp_safe_redirect(
                    admin_url("admin.php?page=cdswerx-users"),
                );
                exit();
            }

            // Handle content reassignment
            $reassign_user_id = null;
            if (!empty($_GET["reassign_user"])) {
                $reassign_user_id = intval($_GET["reassign_user"]);
                // Verify the reassign user exists
                $reassign_user = get_user_by("ID", $reassign_user_id);
                if (!$reassign_user) {
                    set_transient(
                        "cdswerx_admin_message",
                        [
                            "type" => "error",
                            "message" =>
                            "Selected user for content reassignment not found.",
                        ],
                        60,
                    );
                    wp_safe_redirect(
                        admin_url("admin.php?page=cdswerx-users"),
                    );
                    exit();
                }
            }

            // Log the removal action
            $log_message =
                "CDSWerx: Corey user removed by " .
                wp_get_current_user()->user_login;
            if ($reassign_user_id) {
                $reassign_user = get_user_by("ID", $reassign_user_id);
                $log_message .=
                    ", content reassigned to " . $reassign_user->user_login;
            } else {
                $log_message .= ", all content deleted";
            }
            error_log($log_message);

            // Delete the user (WordPress handles content reassignment/deletion)
            require_once ABSPATH . "wp-admin/includes/user.php";
            $deletion_result = wp_delete_user($corey->ID, $reassign_user_id);

            if ($deletion_result) {
                // Check if we should continue with permanent disable
                $continue_disable = !empty($_GET["continue_permanent_disable"]);

                if ($continue_disable) {
                    error_log(
                        "CDSWerx: Corey user removed, continuing with permanent disable",
                    );

                    // Set permanent disable flag
                    update_option("cdswerx_corey_permanently_disabled", true);

                    // Clear activation flags
                    delete_option("cdswerx_corey_activated");

                    set_transient(
                        "cdswerx_admin_message",
                        [
                            "type" => "success",
                            "message" =>
                            "Corey user has been removed and Corey functionality permanently disabled.",
                        ],
                        60,
                    );
                } else {
                    // Reset activation prompts to show again
                    delete_option(
                        "cdswerx_activation_prompts_globally_dismissed",
                    );

                    // Log successful removal
                    error_log(
                        "CDSWerx: Corey user successfully removed and activation prompts reset",
                    );

                    set_transient(
                        "cdswerx_admin_message",
                        [
                            "type" => "success",
                            "message" =>
                            "Corey user has been successfully removed. Activation prompts have been reset.",
                        ],
                        60,
                    );
                }
            } else {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "error",
                        "message" =>
                        "Failed to remove Corey user. Please try again.",
                    ],
                    60,
                );
            }

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }
    }
    
    /**
     * Handle reset activation prompts (migrated from legacy class) 
     *
     * @since 2.0.0
     */
    public function handle_reset_activation_prompts() {
        if (!$this->can_access_user_settings()) {
            error_log("CDSWerx Reset Prompts: Access denied for current user");
            return;
        }

        error_log("CDSWerx DEBUG: handle_reset_activation_prompts() called");

        // Handle reset activation prompts action
        if (!empty($_GET["cdswerx_reset_activation_prompts"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_reset_activation_prompts")) {
                wp_die("Security check failed");
            }

            // Reset all activation prompts
            delete_option("cdswerx_activation_prompts_globally_dismissed");
            delete_option("cdswerx_show_corey_activation_prompt");
            delete_option("cdswerx_janice_activation_prompt_dismissed");
            delete_option("cdswerx_corey_activation_prompt_dismissed");

            error_log("CDSWerx: Activation prompts reset successfully");

            set_transient(
                "cdswerx_admin_message",
                [
                    "type" => "success",
                    "message" => "Activation prompts have been reset and will show again.",
                ],
                60,
            );

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }
    }
    
    /**
     * Handle access control setup (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function handle_access_control_setup() {
        if (!$this->can_access_user_settings()) {
            return;
        }

        // Handle designation of site admin manager
        if (!empty($_GET["cdswerx_designate_manager"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_designate_manager")) {
                wp_die("Security check failed");
            }

            $manager_id = intval($_GET["manager_id"] ?? 0);
            if ($manager_id) {
                $user = get_user_by("ID", $manager_id);
                if ($user && in_array("site_admin_manager", $user->roles)) {
                    update_option("cdswerx_designated_manager", $manager_id);
                    
                    set_transient(
                        "cdswerx_admin_message",
                        [
                            "type" => "success",
                            "message" => "Site admin manager designated successfully.",
                        ],
                        60,
                    );
                } else {
                    set_transient(
                        "cdswerx_admin_message",
                        [
                            "type" => "error",
                            "message" => "Invalid user or user doesn't have site_admin_manager role.",
                        ],
                        60,
                    );
                }
            }

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }
    }
    
    /**
     * Display CDSWerx admin notices (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function display_cdswerx_admin_notices() {
        // Simple implementation for now - can be expanded later
        // Original method was just a comment, so keeping minimal
    }
    
    /**
     * Show global activation prompt (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function show_global_activation_prompt() {
        // DEBUG: Log that method was called
        error_log("CDSWerx DEBUG: show_global_activation_prompt() called");

        // Only show to users who can manage options
        if (!current_user_can("manage_options")) {
            error_log("CDSWerx DEBUG: User cannot manage_options - returning");
            return;
        }
        error_log("CDSWerx DEBUG: User has manage_options capability");

        // Only show on admin pages, not on CDSWerx plugin pages
        $screen = get_current_screen();
        error_log("CDSWerx DEBUG: Current screen ID: " . $screen->id);
        if (strpos($screen->id, "cdswerx") !== false) {
            error_log("CDSWerx DEBUG: On CDSWerx page - returning");
            return;
        }
        error_log("CDSWerx DEBUG: Not on CDSWerx page - continuing");

        // Check if we should show the prompt
        $show_prompt = get_option(
            "cdswerx_show_corey_activation_prompt",
            false,
        );
        error_log(
            "CDSWerx DEBUG: Show prompt flag: " . ($show_prompt ? "YES" : "NO"),
        );
        if (!$show_prompt) {
            error_log("CDSWerx DEBUG: Show prompt flag is false - returning");
            return;
        }

        // Check if globally dismissed
        $globally_dismissed = get_option(
            "cdswerx_activation_prompts_globally_dismissed",
            false,
        );
        error_log(
            "CDSWerx DEBUG: Globally dismissed: " .
                ($globally_dismissed ? "YES" : "NO"),
        );
        if ($globally_dismissed) {
            error_log("CDSWerx DEBUG: Globally dismissed - returning");
            return;
        }

        // For now, simplified implementation - full method can be migrated later if needed
        error_log("CDSWerx DEBUG: Would show activation prompt here");
    }
    
    /**
     * Temporary AJAX handler methods (simplified for integration)
     * These will be fully migrated in the next phase
     *
     * @since 2.0.0
     */
    public function handle_dismiss_activation_prompt() {
        // Basic AJAX security
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_ajax_nonce')) {
            wp_die('Security check failed');
        }
        wp_send_json_success(['message' => 'Activation prompt dismissed']);
    }
    
    public function handle_save_custom_code() {
        // Basic AJAX security
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_ajax_nonce')) {
            wp_die('Security check failed');
        }
        wp_send_json_success(['message' => 'Custom code saved']);
    }
    
    public function handle_update_custom_code() {
        // Basic AJAX security
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_ajax_nonce')) {
            wp_die('Security check failed');
        }
        wp_send_json_success(['message' => 'Custom code updated']);
    }
    
    public function handle_load_custom_code() {
        // Basic AJAX security
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_ajax_nonce')) {
            wp_die('Security check failed');
        }
        wp_send_json_success(['message' => 'Custom code loaded']);
    }
    
    public function handle_get_code_by_id() {
        // Basic AJAX security
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_ajax_nonce')) {
            wp_die('Security check failed');
        }
        wp_send_json_success(['message' => 'Code retrieved']);
    }
    
    public function handle_delete_custom_code() {
        // Basic AJAX security
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_ajax_nonce')) {
            wp_die('Security check failed');
        }
        wp_send_json_success(['message' => 'Custom code deleted']);
    }
    
    public function handle_toggle_code_status() {
        // Basic AJAX security
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_ajax_nonce')) {
            wp_die('Security check failed');
        }
        wp_send_json_success(['message' => 'Code status toggled']);
    }
    
    /**
     * Check if user is properly activated (migrated from legacy class)
     *
     * @since 2.0.0
     * @param WP_User $user User object
     * @return bool True if user has required roles
     */
    public function is_user_activated($user) {
        if (!$user) {
            return false;
        }

        $required_roles = ["site_admin_manager"];
        $has_required_role = false;

        foreach ($required_roles as $role) {
            if ($user->has_cap($role)) {
                $has_required_role = true;
                break;
            }
        }

        // Also check for super admin in multisite
        if (is_multisite() && is_super_admin($user->ID)) {
            $has_required_role = true;
        }

        return $has_required_role;
    }
    
    /**
     * Get users by login (migrated from legacy class)
     *
     * @since 2.0.0
     * @param string $username Username to search for
     * @return array Array of user objects
     */
    public function get_users_by_login($username) {
        global $wpdb;
        $users = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT ID, user_email FROM {$wpdb->users} WHERE user_login = %s",
                $username,
            ),
        );
        return $users;
    }
    
    /**
     * Display users management page (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function display_users() {
        error_log("CDSWerx: display_users called");

        // Define location of Brand Logo
        $logo_url = plugins_url(
            "assets/images/counter-design-studio-logo-digital-user-experience.svg",
            dirname(__FILE__, 3) . "/cdswerx.php",
        );

        // Check access control
        $access_status = $this->get_access_status();

        if (!$access_status["can_access"]) {
            error_log(
                "CDSWerx Access Control: User denied access to users page",
            );
            $this->display_access_restricted_page($access_status);
            return;
        }

        error_log(
            "CDSWerx Access Control: User granted access to users page",
        );

        // Include the users management display template
        include CDSWERX_PLUGIN_PATH .
            "admin/partials/cdswerx-admin-users-settings.php";
    }
    
    /**
     * Get access status (migrated from legacy class)
     *
     * @since 2.0.0
     * @return array Access status information
     */
    public function get_access_status() {
        // Simplified implementation for now - can be expanded later
        return [
            "can_access" => $this->can_access_user_settings(),
            "reason" => "access_granted"
        ];
    }
    
    /**
     * Display access restricted page (migrated from legacy class)
     *
     * @since 2.0.0
     * @param array $access_status Access status information
     */
    public function display_access_restricted_page($access_status) {
        echo '<div class="wrap">';
        echo '<h1>Access Restricted</h1>';
        echo '<p>You do not have permission to access this page.</p>';
        echo '</div>';
    }
    
    /**
     * Display plugin admin page (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function display_plugin_admin_page() {
        // Check access control
        $access_status = $this->get_access_status();

        if (!$access_status["can_access"]) {
            $this->display_access_restricted_page($access_status);
            return;
        }

        // Include the main admin dashboard template
        include CDSWERX_PLUGIN_PATH . "admin/partials/cdswerx-admin-display.php";
    }
    
    /**
     * Display extensions page (migrated from legacy class)
     *
     * @since 2.0.0
     */
    public function display_extensions() {
        // Check access control
        $access_status = $this->get_access_status();

        if (!$access_status["can_access"]) {
            $this->display_access_restricted_page($access_status);
            return;
        }

        // Include the extensions template
        include CDSWERX_PLUGIN_PATH . "admin/partials/cdswerx-admin-extensions.php";
    }

    /**
     * Validate fields from admin area plugin settings form ('validate_callback')
     *
     * @param  array $input as values inputted in form
     * @return array as validated values to be saved to database
     *
     * @since  1.0.0
     */
    public function validate($input)
    {
        // All checkboxes inputs
        $valid = [];

        // Cleanup
        $valid["cleanup"] =
            isset($input["cleanup"]) && !empty($input["cleanup"]) ? 1 : 0;
        $valid["comments_css_cleanup"] =
            isset($input["comments_css_cleanup"]) &&
            !empty($input["comments_css_cleanup"])
            ? 1
            : 0;
        $valid["gallery_css_cleanup"] =
            isset($input["gallery_css_cleanup"]) &&
            !empty($input["gallery_css_cleanup"])
            ? 1
            : 0;
        $valid["body_class_slug"] =
            isset($input["body_class_slug"]) &&
            !empty($input["body_class_slug"])
            ? 1
            : 0;
        $valid["jquery_cdn"] =
            isset($input["jquery_cdn"]) && !empty($input["jquery_cdn"]) ? 1 : 0;
        $valid["cdn_provider"] = sanitize_text_field($input["cdn_provider"]);

        return $valid;
    }

    /**
     * Validate extensions settings form
     *
     * @param  array $input as values inputted in form
     * @return array as validated values to be saved to database
     *
     * @since  1.0.0
     */
    public function validate_extensions($input)
    {
        // All checkbox inputs
        $valid = [];

        // WordPress Core Features
        $valid["disable_comments"] = isset($input["disable_comments"]) ? 1 : 0;
        $valid["disable_widget_blocks"] = isset($input["disable_widget_blocks"]) ? 1 : 0;
        $valid["disable_gutenberg"] = isset($input["disable_gutenberg"]) ? 1 : 0;

        // Admin Interface
        $valid["default_published_post_view"] = isset($input["default_published_post_view"]) ? 1 : 0;
        $valid["show_media_dimensions_column"] = isset($input["show_media_dimensions_column"]) ? 1 : 0;
        $valid["show_post_id_column"] = isset($input["show_post_id_column"]) ? 1 : 0;
        $valid["show_featured_image_column"] = isset($input["show_featured_image_column"]) ? 1 : 0;
        $valid["remove_yoast_columns"] = isset($input["remove_yoast_columns"]) ? 1 : 0;

        // Other settings
        $valid["rename_default_template"] = isset($input["rename_default_template"]) ? 1 : 0;
        $valid["elementor_default_editor"] = isset($input["elementor_default_editor"]) ? 1 : 0;
        $valid["elementor_edit_links"] = isset($input["elementor_edit_links"]) ? 1 : 0;
        $valid["elementor_styling"] = isset($input["elementor_styling"]) ? 1 : 0;
        $valid["elementor_tinymce_colors"] = isset($input["elementor_tinymce_colors"]) ? 1 : 0;

        // Log the saved options
        error_log("CDSWerx Extensions: Options saved - " . json_encode($valid));

        return $valid;
    }

    /**
     * Check if user is one of the target users (janice or corey)
     *
     * @since    1.0.0
     * @param    WP_User $user
     * @return   bool
     */
    private function is_target_user($user)
    {
        return ($user->user_login === "janice" &&
            $user->user_email === "webmaster@counterdesign.ca") ||
            ($user->user_login === "corey" &&
                $user->user_email === "cjason@cloudsurfingmedia.com");
    }

    /**
     * Reset activation prompts when user is deactivated
     *
     * @since    1.0.0
     * @param    string $username Username that was deactivated
     */
    private function reset_activation_prompts_on_deactivation($username)
    {
        error_log(
            "CDSWerx DEBUG: Resetting activation prompts due to $username deactivation",
        );

        // Clear the global dismissal flag
        delete_option("cdswerx_activation_prompts_globally_dismissed");

        // Re-enable the show prompt flag
        update_option("cdswerx_show_corey_activation_prompt", true);

        // Update the respective user's activation flag
        if ($username === "janice") {
            update_option("cdswerx_janice_activated", false);
        } elseif ($username === "corey") {
            update_option("cdswerx_corey_activated", false);
        }

        error_log(
            "CDSWerx DEBUG: Activation prompts reset completed for $username deactivation",
        );
    }

    // ==========================
    // USER MANAGEMENT CALLBACKS
    // ==========================

    /**
     * Handle user deletion tracking
     * Called when a user is deleted from WordPress
     *
     * @param int $user_id The ID of the deleted user
     * @since 1.0.0
     */
    public function handle_user_deletion($user_id) {
        error_log("CDSWerx: User deletion tracked - User ID: $user_id");
        // TODO: Implement user deletion tracking logic if needed
    }

    /**
     * Handle user removal from site in multisite
     * Called when a user is removed from a specific site
     *
     * @param int $user_id The ID of the user being removed
     * @param int $blog_id The ID of the site they're being removed from
     * @since 1.0.0
     */
    public function handle_user_removal($user_id, $blog_id) {
        error_log("CDSWerx: User removal tracked - User ID: $user_id, Blog ID: $blog_id");
        // TODO: Implement user removal tracking logic if needed
    }

    /**
     * Handle user role changes
     * Called when a user's role is changed
     *
     * @param int $user_id The ID of the user whose role changed
     * @param string $role The new role
     * @param array $old_roles Array of the user's previous roles
     * @since 1.0.0
     */
    public function handle_user_role_change($user_id, $role, $old_roles) {
        error_log("CDSWerx: User role change tracked - User ID: $user_id, New Role: $role");
        // TODO: Implement user role change tracking logic if needed
    }

    /**
     * Render admin section templates (header/footer)
     * Called by admin dashboard template
     *
     * @param string $section The section to render ('header' or 'footer')
     * @param string $logo_url Optional logo URL for header section
     * @since 1.0.0
     */
    public function render_cdswerx_admin_section($section, $logo_url = '') {
        switch ($section) {
            case 'header':
                echo '<div class="cdswerx-admin-header">';
                if ($logo_url) {
                    echo '<img src="' . esc_url($logo_url) . '" alt="CDSWerx Logo" class="cdswerx-logo" />';
                }
                echo '<h1>CDSWerx Administration</h1>';
                echo '</div>';
                break;
            
            case 'footer':
                echo '<div class="cdswerx-admin-footer">';
                echo '<p>&copy; ' . date('Y') . ' CDSWerx - Counter Design Studio</p>';
                echo '</div>';
                break;
                
            default:
                // Unknown section, output nothing
                break;
        }
    }

    /**
     * Reset permanent disable flag on plugin deactivation
     *
     * @since    1.0.0
     */
    public function reset_permanent_disable_on_deactivation()
    {
        error_log(
            "CDSWerx: Plugin deactivated - resetting permanent disable flag",
        );
        delete_option("cdswerx_corey_permanently_disabled");
        delete_option("cdswerx_designated_manager");
        error_log(
            "CDSWerx: Permanent disable settings reset on plugin deactivation",
        );
    }
}