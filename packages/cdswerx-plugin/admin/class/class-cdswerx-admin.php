<?php

/**
 * CDSWerx Admin Class
 *
 * Handles all admin-side functionality for the CDSWerx plugin.
 *
 * Purpose & Responsibilities:
 * - Renders the admin UI for plugin settings, dashboard, extensions, and user management.
 * - Registers and manages plugin options and settings fields.
 * - Controls the WordPress admin menu structure for CDSWerx pages.
 * - Manages access control and user role assignment for plugin-specific admin features.
 * - Handles admin notices, prompts, and AJAX actions related to plugin management.
 *
 * Separation of Concerns:
 * - This class is focused on UI, settings registration, and option management.
 * - Actual WordPress feature logic (hooks/filters that modify WP behavior) is implemented in the features/extensions class.
 * - The admin class interacts with the features/extensions class by passing option values and triggering feature logic as needed.
 *
 * Sections & Code Overview:
 * 1. Initialization & Asset Loading:
 *   - Constructor, hooks for admin actions, JS/CSS enqueue.
 * 2. Menu & Settings Registration:
 *   - Adds CDSWerx admin menu and submenus.
 *   - Registers plugin settings and fields.
 *   - Adds action links to plugins page.
 * 3. Field Rendering:
 *   - Renders form fields for settings pages.
 * 4. Notices & Prompts:
 *   - Displays admin notices and global activation prompts.
 *   - Handles AJAX dismissal and reset of activation prompts.
 * 5. Page Rendering:
 *   - Renders dashboard, extensions, and users management pages.
 *   - Includes partials/templates for each section.
 * 6. Access Control & User Management:
 *   - Manages access restrictions, user setup, and manager selection.
 *   - Handles creation, update, and removal of special users (Janice, Corey).
 *   - Assigns custom roles and capabilities.
 *   - Tracks user deletion, removal, and role changes.
 *   - Handles permanent disable actions for users.
 * 7. Utility Methods:
 *   - Helper functions for role checks, user status, and option validation.
 *   - Resets plugin-specific flags on deactivation.
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 * @package    Cdswerx
 * @subpackage Cdswerx/admin
 * @author     CDSWerx <info@cdswerx.com>
 */
class Cdswerx_Admin
{
    /**
     * Stores all settings fields for the admin UI.
     *
     * @var array
     */
    public $cdswerx_fields = [];

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

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

        // Hook for user deactivation tracking (Option 3)
        add_action("delete_user", [$this, "handle_user_deletion"]);
        add_action(
            "remove_user_from_blog",
            [$this, "handle_user_removal"],
            10,
            2,
        );
        add_action("set_user_role", [$this, "handle_user_role_change"], 10, 3);

        // Hook for plugin deactivation to reset permanent disable
        register_deactivation_hook(dirname(__FILE__, 2) . "/cdswerx.php", [
            $this,
            "reset_permanent_disable_on_deactivation",
        ]);

        // Ensure admin JS/CSS is loaded on CDSWerx admin pages
        add_action("admin_enqueue_scripts", [$this, "enqueue_scripts"]);
        add_action("admin_enqueue_scripts", [$this, "enqueue_styles"]);
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        // Plugin admin CSS
        wp_enqueue_style(
            $this->plugin_name,
            CDSWERX_PLUGIN_URL . "admin/css/cdswerx-admin.css",
            [],
            $this->version,
            "all",
        );
        
        // Only load on CDSWerx plugin admin pages
        if (isset($_GET["page"]) && strpos($_GET["page"], "cds") === 0) {
            // UIkit CSS (CDN)
            wp_enqueue_style(
                "uikit-css",
                "https://cdn.jsdelivr.net/npm/uikit@3.19.4/dist/css/uikit.min.css",
                [],
                "3.19.4",
            );
            
            // Custom Code Manager CSS
            wp_enqueue_style(
                "cdswerx-custom-code",
                CDSWERX_PLUGIN_URL . "admin/css/cdswerx-custom-code.css",
                [$this->plugin_name],
                $this->version,
                "all",
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script(
            $this->plugin_name,
            CDSWERX_PLUGIN_URL . "admin/js/cdswerx-admin.js",
            ["jquery"],
            $this->version,
            false,
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
        
        // Debug: Log page check
        error_log("CDSWerx Debug: Current page = " . (isset($_GET["page"]) ? $_GET["page"] : "none"));
        error_log("CDSWerx Debug: Current tab = " . (isset($_GET["tab"]) ? $_GET["tab"] : "none"));
        error_log("CDSWerx Debug: Full query string = " . $_SERVER['QUERY_STRING']);
        
        // Only load on CDSWerx plugin admin pages (not theme pages)
        if (isset($_GET["page"]) && strpos($_GET["page"], "cdswerx") === 0) {
            error_log("CDSWerx Debug: Loading scripts for CDSWerx page");
            // UIkit JS (CDN)
            wp_enqueue_script(
                "uikit-js",
                "https://cdn.jsdelivr.net/npm/uikit@3.19.4/dist/js/uikit.min.js",
                [],
                "3.19.4",
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
                $this->version . '.' . time(), // Add timestamp for cache busting
                true,
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
            
            // Debug: Log script enqueue status
            wp_add_inline_script(
                "cdswerx-custom-code",
                "
                console.log('CDSWerx Debug: Scripts enqueued at " . current_time('H:i:s') . "');
                console.log('CDSWerx Debug: Current URL:', window.location.href);
                console.log('CDSWerx Debug: Page contains Advanced tab?', window.location.href.indexOf('tab=advanced') > -1);
                "
            );
        }
    }
    /**
     * Add menu item
     *
     * @since    1.0.0
     */
    public function register_cdswerx_admin_menu()
    {
        /*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         *        Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
        // Main CDSWerx menu (Parent)
        add_menu_page(
            __("CDSWerx", "cdswerx"), // page title
            __("CDSWerx", "cdswerx"), // menu title
            "manage_options", // capability
            "cdswerx", // menu slug
            [$this, "display_dashboard"], // callback
            "dashicons-admin-tools", // icon
            30, // position
        );

        // Dashboard submenu (#1) - Overview, Notifications, Settings tabs
        add_submenu_page(
            "cdswerx", // parent slug
            __("Dashboard", "cdswerx"), // page title
            __("Dashboard", "cdswerx"), // menu title
            "manage_options", // capability
            "cdswerx", // menu slug (same as parent for default)
            [$this, "display_dashboard"], // callback
        );

        // Extensions submenu (#2) - WordPress Core, Editor/TinyMCE, Elementor, Utilities tabs
        add_submenu_page(
            "cdswerx",
            __("CDS Extensions", "cdswerx"),
            __("CDS Extensions", "cdswerx"),
            "manage_options",
            "cdswerx-extensions",
            [$this, "display_extensions"],
        );

        // Users submenu (#3) - Roles & User Settings, Advanced Management Settings tabs (restricted access)
        if ($this->can_access_user_settings()) {
            add_submenu_page(
                "cdswerx",
                __("Users", "cdswerx"),
                __("Users", "cdswerx"),
                "manage_options",
                "cdswerx-users",
                [$this, "display_users"],
            );
            error_log(
                "CDSWerx Menu: Users menu added for authorized user",
            );
        } else {
            error_log(
                "CDSWerx Menu: Users menu hidden from unauthorized user",
            );
        }
    }

    /**
     * Add settings action link to the plugins page.
     *
     * @since    1.0.0
     */
    public function add_action_links($links)
    {
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
     * Render the admin settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function register_cdswerx_admin_settings()
    {
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

    public function render_field($args)
    {
        $options = get_option("cdswerx_options");
        $id = $args["id"];
        $type = $args["type"];
        $value = isset($options[$id]) ? $options[$id] : "";
        $checkbox_label = isset($args["checkbox_label"])
            ? $args["checkbox_label"]
            : "";
        $description = !empty($args["description"])
            ? '<div class="uk-text-meta uk-margin-small-top">' .
            esc_html($args["description"]) .
            "</div>"
            : "";

        // For checkbox, keep inline layout (label and input together)
        if ($type === "checkbox") {
            echo '<div class="uk-width-1-2@s uk-form-label">';
            if (!empty($args["title"])) {
                echo '<label class="uk-form-label" for="cdswerx_options_' .
                    esc_attr($id) .
                    '">' .
                    esc_html($args["title"]) .
                    "</label>";
            }
            echo "</div>";
            echo '<div class="uk-width-expand@s uk-form-controls">';
            echo '<label style="font-weight:400;">';
            echo '<input id="cdswerx_options_' .
                esc_attr($id) .
                '" class="uk-checkbox" type="checkbox" name="cdswerx_options[' .
                esc_attr($id) .
                ']" value="1" ' .
                checked($value, 1, false) .
                " />";
            if ($checkbox_label) {
                echo " " . esc_html($checkbox_label);
            }
            echo "</label>";
            echo $description;
            echo "</div>";
        } else {
            // For all other fields, label and input in separate columns
            echo '<div class="uk-width-1-2@s uk-form-label">';
            if (!empty($args["title"])) {
                echo '<label class="uk-form-label" for="cdswerx_options_' .
                    esc_attr($id) .
                    '">' .
                    esc_html($args["title"]) .
                    "</label>";
            }
            echo "</div>";
            echo '<div class="uk-width-expand@s uk-form-controls">';
            switch ($type) {
                case "select":
                    echo '<select class="uk-select" id="cdswerx_options_' .
                        esc_attr($id) .
                        '" name="cdswerx_options[' .
                        esc_attr($id) .
                        ']">';
                    foreach ($args["options"] as $val => $label) {
                        echo '<option value="' .
                            esc_attr($val) .
                            '" ' .
                            selected($value, $val, false) .
                            ">" .
                            esc_html($label) .
                            "</option>";
                    }
                    echo "</select>";
                    break;
                case "text":
                default:
                    echo '<input class="uk-input" id="cdswerx_options_' .
                        esc_attr($id) .
                        '" type="text" name="cdswerx_options[' .
                        esc_attr($id) .
                        ']" value="' .
                        esc_attr($value) .
                        '" />';
                    break;
            }
            echo $description;
            echo "</div>";
        }
    }

    /**
     * Render the settings to show admin notices/messages
     *
     * @since    1.0.0
     */
    public function display_cdswerx_admin_notices()
    {
        //include_once('partials/cdswerx-admin-dashboard.php');
    }

    /**
     * Display global activation prompt notice
     *
     * @since    1.0.0
     */
    public function show_global_activation_prompt()
    {
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

        // Get user statuses
        $janice_status = $this->get_janice_activation_status();
        $corey_status = $this->get_corey_activation_status();

        error_log(
            "CDSWerx DEBUG: Janice activated: " .
                ($janice_status["activated"] ? "YES" : "NO"),
        );
        error_log(
            "CDSWerx DEBUG: Corey activated: " .
                ($corey_status["activated"] ? "YES" : "NO"),
        );

        // Don't show if both users are already activated
        if ($janice_status["activated"] && $corey_status["activated"]) {
            error_log("CDSWerx DEBUG: Both users activated - returning");
            return;
        }

        error_log("CDSWerx DEBUG: All conditions passed - showing prompt");

        // Generate nonces for the action links
        $activate_corey_url = admin_url(
            "admin.php?page=cdswerx-users&cdswerx_activate_corey=1&_wpnonce=" .
                wp_create_nonce("cdswerx_activate_corey"),
        );
        $activate_janice_url = admin_url(
            "admin.php?page=cdswerx-users&cdswerx_activate_janice=1&_wpnonce=" .
                wp_create_nonce("cdswerx_activate_janice"),
        );
        $activate_both_url = admin_url(
            "admin.php?page=cdswerx-users&cdswerx_activate_both_users=1&_wpnonce=" .
                wp_create_nonce("cdswerx_activate_both_users"),
        );
?>
        <div class="notice notice-info cdswerx-activation-prompt is-dismissible">
            <p>
                <strong>CDSWerx:</strong> Would you like to activate admin roles for:
                <?php if (!$corey_status["activated"]): ?>
                    <a href="<?php echo esc_url(
                                    $activate_corey_url,
                                ); ?>" class="cdswerx-button-primary" style="margin-left: 10px;">Activate Corey</a>
                <?php endif; ?>
                <?php if (!$corey_status["activated"] && !$janice_status["activated"]): ?>
                    <span style="margin: 0 5px;">|</span>
                <?php endif; ?>
                <?php if (!$janice_status["activated"]): ?>
                    <a href="<?php echo esc_url(
                                    $activate_janice_url,
                                ); ?>" class="cdswerx-button-primary">Activate Janice</a>
                <?php endif; ?>
                <?php if (!$corey_status["activated"] && !$janice_status["activated"]): ?>
                    <span style="margin: 0 5px;">|</span>
                    <a href="<?php echo esc_url(
                                    $activate_both_url,
                                ); ?>" class="cdswerx-button-primary">Activate Both Users</a>
                <?php endif; ?>
                <span style="margin: 0 10px;">|</span>
                <button type="button" class="button-link cdswerx-dismiss-prompt" style="color: #666; text-decoration: underline;">
                    Dismiss All Prompts
                </button>
            </p>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Handle dismissible notice (both X button and explicit dismiss button)
                $(document).on('click', '.cdswerx-activation-prompt .notice-dismiss, .cdswerx-dismiss-prompt', function(e) {
                    e.preventDefault();

                    // Hide the notice immediately for better UX
                    $('.cdswerx-activation-prompt').fadeOut();

                    $.post(ajaxurl, {
                        action: 'cdswerx_dismiss_activation_prompt',
                        nonce: '<?php echo wp_create_nonce("cdswerx_dismiss_activation_prompt"); ?>'
                    }).done(function() {
                        // Auto-refresh the page after successful dismissal
                        location.reload();
                    }).fail(function() {
                        // Show error message if AJAX fails
                        $('.cdswerx-activation-prompt').fadeIn();
                        alert('Failed to dismiss prompts. Please try again.');
                    });
                });
            });
        </script>
        <?php error_log("CDSWerx DEBUG: Prompt HTML output completed");
    }

    public function render_cdswerx_admin_section(
        $section,
        $logo_url = "",
        $title = "",
    ) {
        if ($section === "header") { ?>
            <div class="cdswerx-admin page-header">
                <div class="uk-flex uk-flex-middle uk-flex-between uk-flex-wrap uk-margin-small-bottom">
                    <h1><?php echo esc_html($title ?: get_admin_page_title()); ?></h1>
                    <img src="<?php echo esc_url(
                                    $logo_url,
                                ); ?>" alt="Counter Design Studio Logo" class="cdswerx-logo uk-margin-remove-right" style="margin-right:32px;">
                </div>
            </div>
        <?php } elseif ($section === "footer") { ?>
            <div class="cdswerx-admin page-footer uk-margin-large-top uk-text-center uk-text-meta">
                <p class="uk-text-small">Counter Design Studio | UX Web Design & Development</p>
            </div>
<?php }
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
     * Initializes display methods and admin functionality for admin pages
     */

    // Callback to for admin display settings
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
     * Display access restricted page for unauthorized users
     *
     * @since    1.0.0
     */
    public function display_access_restricted_page($access_status)
    {
        $current_user = $access_status["current_user"];

        error_log(
            "CDSWerx Access Control: Displaying access restricted page for user " .
                $current_user->user_login,
        );

        echo '<div class="wrap">';
        echo "<h1>Access Restricted</h1>";
        echo '<div class="notice notice-error">';
        echo '<p><strong>This page is only accessible to user "Janice" or designated site administrators.</strong></p>';
        echo "<p><strong>Current user:</strong> " .
            esc_html($current_user->user_login) .
            " (" .
            esc_html($current_user->user_email) .
            ")</p>";
        echo "<p>Contact Janice or a site administrator for access to CDSWerx user management.</p>";
        echo "</div>";

        // Show setup options if needed
        if ($access_status["needs_setup"]) {
            $this->display_user_setup_prompt($access_status);
        } elseif ($access_status["needs_selection"]) {
            $this->display_manager_selection_prompt($access_status);
        }

        echo "</div>";
    }

    /**
     * Display user setup prompt when no authorized users exist
     *
     * @since    1.0.0
     */
    public function display_user_setup_prompt($access_status)
    {
        error_log("CDSWerx Access Control: Displaying user setup prompt");

        echo '<div class="card">';
        echo "<h2>Setup CDSWerx Access</h2>";
        echo "<p>No authorized users found. Grant CDSWerx user management access to:</p>";

        echo '<form method="post" action="' .
            admin_url("admin.php?page=cdswerx-users") .
            '">';
        wp_nonce_field("cdswerx_setup_access", "cdswerx_setup_nonce");

        echo '<table class="form-table">';
        echo "<tr>";
        echo '<th scope="row">Select User:</th>';
        echo "<td>";
        echo '<select name="cdswerx_setup_user" required>';
        echo '<option value="">Select a user...</option>';
        echo '<option value="create_janice">Create new Janice user</option>';

        $users = get_users(["orderby" => "display_name"]);
        foreach ($users as $user) {
            echo '<option value="' .
                esc_attr($user->ID) .
                '">' .
                esc_html($user->display_name) .
                " (" .
                esc_html($user->user_login) .
                ")</option>";
        }

        echo "</select>";
        echo "</td>";
        echo "</tr>";
        echo "</table>";

        echo '<p class="submit">';
        echo '<input type="submit" name="cdswerx_grant_access" class="button button-primary" value="Grant Access">';
        echo '<input type="submit" name="cdswerx_create_janice" class="button button-secondary" value="Create Janice" style="margin-left: 10px;">';
        echo "</p>";
        echo "</form>";
        echo "</div>";
    }

    /**
     * Display manager selection prompt when multiple site_admin_managers exist
     *
     * @since    1.0.0
     */
    public function display_manager_selection_prompt($access_status)
    {
        error_log(
            "CDSWerx Access Control: Displaying manager selection prompt",
        );

        echo '<div class="card">';
        echo "<h2>Janice Not Found - Select Replacement</h2>";
        echo "<p>Multiple site administrators found. Who should manage CDSWerx users?</p>";

        echo '<form method="post" action="' .
            admin_url("admin.php?page=cdswerx-users") .
            '">';
        wp_nonce_field("cdswerx_select_manager", "cdswerx_select_nonce");

        echo '<table class="form-table">';
        echo "<tr>";
        echo '<th scope="row">Site Administrator:</th>';
        echo "<td>";
        echo '<select name="cdswerx_selected_manager" required>';
        echo '<option value="">Select administrator...</option>';

        foreach ($access_status["site_admin_managers"] as $manager) {
            echo '<option value="' .
                esc_attr($manager->ID) .
                '">' .
                esc_html($manager->display_name) .
                " (" .
                esc_html($manager->user_login) .
                ")</option>";
        }

        echo "</select>";
        echo "</td>";
        echo "</tr>";
        echo "</table>";

        echo '<p class="submit">';
        echo '<input type="submit" name="cdswerx_set_manager" class="button button-primary" value="Set as CDSWerx Manager">';
        echo "</p>";
        echo "</form>";
        echo "</div>";
    }

    /**
     * Handle access control setup and manager selection
     *
     * @since    1.0.0
     */
    public function handle_access_control_setup()
    {
        if (!current_user_can("administrator")) {
            return;
        }

        // Handle user setup (grant access to existing user or create Janice)
        if (
            !empty($_POST["cdswerx_grant_access"]) ||
            !empty($_POST["cdswerx_create_janice"])
        ) {
            $nonce = $_POST["cdswerx_setup_nonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_setup_access")) {
                error_log("CDSWerx Access Setup: Nonce verification failed");
                wp_die("Security check failed");
            }

            if (!empty($_POST["cdswerx_create_janice"])) {
                error_log("CDSWerx Access Setup: Creating new Janice user");
                $result = $this->create_or_update_janice_user();

                if ($result["success"]) {
                    error_log(
                        "CDSWerx Access Setup: Janice user created successfully",
                    );
                    set_transient(
                        "cdswerx_admin_message",
                        [
                            "type" => "success",
                            "message" =>
                            "Janice user created successfully. You can now access CDSWerx user management with the Janice account. Default password: passwordhere",
                        ],
                        60,
                    );
                } else {
                    error_log(
                        "CDSWerx Access Setup: Failed to create Janice user - " .
                            $result["message"],
                    );
                    set_transient(
                        "cdswerx_admin_message",
                        [
                            "type" => "error",
                            "message" =>
                            "Failed to create Janice user: " .
                                $result["message"],
                        ],
                        60,
                    );
                }
            } elseif (!empty($_POST["cdswerx_setup_user"])) {
                $selected_user = sanitize_text_field(
                    $_POST["cdswerx_setup_user"],
                );

                if ($selected_user === "create_janice") {
                    error_log(
                        "CDSWerx Access Setup: Creating Janice via dropdown selection",
                    );
                    $result = $this->create_or_update_janice_user();

                    if ($result["success"]) {
                        error_log(
                            "CDSWerx Access Setup: Janice user created via dropdown",
                        );
                        set_transient(
                            "cdswerx_admin_message",
                            [
                                "type" => "success",
                                "message" =>
                                "Janice user created successfully. Default password: passwordhere",
                            ],
                            60,
                        );
                    } else {
                        error_log(
                            "CDSWerx Access Setup: Failed to create Janice via dropdown - " .
                                $result["message"],
                        );
                        set_transient(
                            "cdswerx_admin_message",
                            [
                                "type" => "error",
                                "message" =>
                                "Failed to create Janice: " .
                                    $result["message"],
                            ],
                            60,
                        );
                    }
                } else {
                    $user_id = intval($selected_user);
                    $user = get_user_by("ID", $user_id);

                    if ($user) {
                        error_log(
                            "CDSWerx Access Setup: Granting site_admin_manager role to user " .
                                $user->user_login,
                        );
                        $user->add_role("site_admin_manager");

                        set_transient(
                            "cdswerx_admin_message",
                            [
                                "type" => "success",
                                "message" =>
                                "Granted CDSWerx access to " .
                                    $user->display_name .
                                    " (" .
                                    $user->user_login .
                                    ")",
                            ],
                            60,
                        );

                        error_log(
                            "CDSWerx Access Setup: Successfully granted access to " .
                                $user->user_login,
                        );
                    } else {
                        error_log(
                            "CDSWerx Access Setup: Selected user not found - ID: " .
                                $user_id,
                        );
                        set_transient(
                            "cdswerx_admin_message",
                            [
                                "type" => "error",
                                "message" => "Selected user not found",
                            ],
                            60,
                        );
                    }
                }
            }

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }

        // Handle manager selection
        if (!empty($_POST["cdswerx_set_manager"])) {
            $nonce = $_POST["cdswerx_select_nonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_select_manager")) {
                error_log(
                    "CDSWerx Manager Selection: Nonce verification failed",
                );
                wp_die("Security check failed");
            }

            $manager_id = intval($_POST["cdswerx_selected_manager"]);
            $manager = get_user_by("ID", $manager_id);

            if ($manager) {
                error_log(
                    "CDSWerx Manager Selection: Setting " .
                        $manager->user_login .
                        " as designated manager",
                );

                // Store the designated manager ID
                update_option("cdswerx_designated_manager", $manager_id);

                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "success",
                        "message" =>
                        $manager->display_name .
                            " (" .
                            $manager->user_login .
                            ") has been set as the CDSWerx manager",
                    ],
                    60,
                );

                error_log(
                    "CDSWerx Manager Selection: Successfully set " .
                        $manager->user_login .
                        " as manager",
                );
            } else {
                error_log(
                    "CDSWerx Manager Selection: Selected manager not found - ID: " .
                        $manager_id,
                );
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "error",
                        "message" => "Selected manager not found",
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

    // Callback for extensions display page
    public function display_extensions()
    {
        // Define location of Brand Logo
        $logo_url =
            CDSWERX_PLUGIN_URL .
            "assets/images/counter-design-studio-logo-digital-user-experience.svg";

        // Include the extensions display template
        include CDSWERX_PLUGIN_PATH .
            "admin/partials/cdswerx-admin-extensions.php";
    }

    // Callback for users management display page
    public function display_users()
    {
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
     * Check if Site Admin Manager role exists
     *
     * @since    1.0.0
     * @return   bool
     */
    public function site_admin_manager_role_exists()
    {
        return get_role("site_admin_manager") !== null;
    }

    /**
     * Get users with Site Admin Manager role
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_site_admin_managers()
    {
        return get_users([
            "role" => "site_admin_manager",
            "fields" => ["ID", "user_login", "user_email", "display_name"],
        ]);
    }

    /**
     * Check if current user is janice
     *
     * @since    1.0.0
     * @return   bool
     */
    public function is_current_user_janice()
    {
        $current_user = wp_get_current_user();
        if (!$current_user || !$current_user->exists()) {
            error_log("CDSWerx Access Control: No current user found");
            return false;
        }

        $is_janice =
            $current_user->user_login === "janice" &&
            $current_user->user_email === "webmaster@counterdesign.ca";

        error_log(
            "CDSWerx Access Control: User " .
                $current_user->user_login .
                " (" .
                $current_user->user_email .
                ") - Janice check: " .
                ($is_janice ? "PASS" : "FAIL"),
        );

        return $is_janice;
    }

    /**
     * Get janice user activation status
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_janice_activation_status()
    {
        $users = $this->get_users_by_login("janice");
        $target_email = "webmaster@counterdesign.ca";
        $activation_flag = get_option("cdswerx_janice_activated", false);

        $status = [
            "multiple_accounts" => count($users) > 1,
            "user_count" => count($users),
            "has_correct_email" => false,
            "activated" => false,
            "primary_user" => null,
            "all_users" => $users,
            "conflicts" => [],
        ];

        if (count($users) === 1) {
            $user = new WP_User($users[0]->ID);
            $status["primary_user"] = $user;
            $status["has_correct_email"] = $user->user_email === $target_email;
            $status["activated"] =
                $this->is_user_activated($user) && $activation_flag;

            if (!$status["has_correct_email"]) {
                $status["conflicts"][] = "email_mismatch";
            }
        } elseif (count($users) > 1) {
            $status["conflicts"][] = "multiple_accounts";

            // Look for user with correct email
            foreach ($users as $user_data) {
                if ($user_data->user_email === $target_email) {
                    $user = new WP_User($user_data->ID);
                    $status["primary_user"] = $user;
                    $status["has_correct_email"] = true;
                    $status["activated"] =
                        $this->is_user_activated($user) && $activation_flag;
                    break;
                }
            }
        }

        return $status;
    }

    /**
     * Get all users with a specific login name
     *
     * @since    1.0.0
     * @param    string $username
     * @return   array
     */
    public function get_users_by_login($username)
    {
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
     * Check if user is properly activated (has required roles)
     *
     * @since    1.0.0
     * @param    WP_User $user
     * @return   bool
     */
    public function is_user_activated($user)
    {
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
     * Create or update janice user
     *
     * @since    1.0.0
     * @param    int $user_id Optional specific user ID
     * @return   array
     */
    public function create_or_update_janice_user($user_id = null)
    {
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
     * Assign roles with proper hierarchy
     *
     * @since    1.0.0
     * @param    WP_User $user
     * @return   array
     */
    public function assign_user_roles($user)
    {
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
     * Recreate Site Admin Manager role
     *
     * @since    1.0.0
     * @return   bool
     */
    public function recreate_site_admin_manager_role()
    {
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
     * Check if current user is corey
     *
     * @since    1.0.0
     * @return   bool
     */
    public function is_current_user_corey()
    {
        $current_user = wp_get_current_user();
        if (!$current_user || !$current_user->exists()) {
            return false;
        }

        return $current_user->user_login === "corey" &&
            $current_user->user_email === "cjason@cloudsurfingmedia.com";
    }

    /**
     * Get corey user activation status
     *
     * @since    1.0.0
     * @return   array
     */
    public function get_corey_activation_status()
    {
        $users = $this->get_users_by_login("corey");
        $target_email = "cjason@cloudsurfingmedia.com";
        $activation_flag = get_option("cdswerx_corey_activated", false);
        $is_permanently_disabled = get_option(
            "cdswerx_corey_permanently_disabled",
            false,
        );

        $status = [
            "multiple_accounts" => count($users) > 1,
            "user_count" => count($users),
            "has_correct_email" => false,
            "activated" => false,
            "permanently_disabled" => $is_permanently_disabled,
            "primary_user" => null,
            "all_users" => $users,
            "conflicts" => [],
        ];

        if (count($users) === 1) {
            $user = new WP_User($users[0]->ID);
            $status["primary_user"] = $user;
            $status["has_correct_email"] = $user->user_email === $target_email;
            $status["activated"] =
                $this->is_user_activated($user) &&
                $activation_flag &&
                !$is_permanently_disabled;

            if (!$status["has_correct_email"]) {
                $status["conflicts"][] = "email_mismatch";
            }
        } elseif (count($users) > 1) {
            $status["conflicts"][] = "multiple_accounts";

            // Look for user with correct email
            foreach ($users as $user_data) {
                if ($user_data->user_email === $target_email) {
                    $user = new WP_User($user_data->ID);
                    $status["primary_user"] = $user;
                    $status["has_correct_email"] = true;
                    $status["activated"] =
                        $this->is_user_activated($user) &&
                        $activation_flag &&
                        !$is_permanently_disabled;
                    break;
                }
            }
        }

        return $status;
    }

    /**
     * Create or update corey user
     *
     * @since    1.0.0
     * @param    int $user_id Optional specific user ID
     * @return   array
     */
    public function create_or_update_corey_user($user_id = null)
    {
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
     * Handle janice user management actions
     *
     * @since    1.0.0
     */
    public function handle_janice_user_actions()
    {
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
     * Handle permanent disable Corey actions
     *
     * @since    1.0.0
     */
    public function handle_permanent_disable_actions()
    {
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
     * Handle corey user management actions
     *
     * @since    1.0.0
     */
    public function handle_corey_user_actions()
    {
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

            if ($new_status) {
                // Also deactivate if being disabled
                update_option("cdswerx_corey_activated", 0);
                $message = "Corey permanently disabled for this website.";
            } else {
                $message =
                    "Corey permanent disable removed. You can now activate Corey.";
            }

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

        // Handle activation prompt actions
        if (!empty($_GET["cdswerx_activate_both_users"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_activate_both_users")) {
                wp_die("Security check failed");
            }

            // Create Corey user via activator
            require_once CDSWERX_PLUGIN_PATH .
                "includes/class-cdswerx-activator.php";
            $corey_result = Cdswerx_Activator::create_corey_user();

            // Clear the prompt flag
            delete_option("cdswerx_show_corey_activation_prompt");

            if ($corey_result) {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "success",
                        "message" =>
                        "Both Janice and Corey users have been activated successfully!",
                    ],
                    60,
                );
            } else {
                set_transient(
                    "cdswerx_admin_message",
                    [
                        "type" => "error",
                        "message" =>
                        "Janice activated successfully, but failed to create Corey user.",
                    ],
                    60,
                );
            }

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }

        // Handle dismiss prompt
        if (!empty($_GET["cdswerx_dismiss_corey_prompt"])) {
            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "cdswerx_dismiss_corey_prompt")) {
                wp_die("Security check failed");
            }

            // Clear the prompt flag
            delete_option("cdswerx_show_corey_activation_prompt");

            set_transient(
                "cdswerx_admin_message",
                [
                    "type" => "success",
                    "message" =>
                    "Activation prompt dismissed. You can manually activate Corey later if needed.",
                ],
                60,
            );

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }

        // Handle test trigger prompt action
        if (!empty($_GET["test_trigger_prompt"])) {
            error_log("CDSWerx DEBUG: test_trigger_prompt action detected");

            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "test_trigger_prompt")) {
                error_log("CDSWerx DEBUG: test_trigger_prompt nonce failed");
                wp_die("Security check failed");
            }

            error_log(
                "CDSWerx DEBUG: Setting cdswerx_show_corey_activation_prompt to 1",
            );
            // Set the flag to show the activation prompt
            update_option("cdswerx_show_corey_activation_prompt", 1);

            // Also make sure global dismissal is cleared
            delete_option("cdswerx_activation_prompts_globally_dismissed");
            error_log("CDSWerx DEBUG: Cleared global dismissal flag");

            set_transient(
                "cdswerx_admin_message",
                [
                    "type" => "success",
                    "message" =>
                    "Activation prompt flag has been triggered. Check other admin pages to see the global prompt.",
                ],
                60,
            );

            wp_safe_redirect(
                admin_url("admin.php?page=cdswerx-users"),
            );
            exit();
        }

        // Handle test clear dismissal action
        if (!empty($_GET["test_clear_dismissal"])) {
            error_log("CDSWerx DEBUG: test_clear_dismissal action detected");

            $nonce = $_GET["_wpnonce"] ?? "";
            if (!wp_verify_nonce($nonce, "test_clear_dismissal")) {
                error_log("CDSWerx DEBUG: test_clear_dismissal nonce failed");
                wp_die("Security check failed");
            }

            error_log("CDSWerx DEBUG: Clearing global dismissal flag");
            // Clear the global dismissal flag
            delete_option("cdswerx_activation_prompts_globally_dismissed");

            set_transient(
                "cdswerx_admin_message",
                [
                    "type" => "success",
                    "message" =>
                    "Global dismissal flag has been cleared. The activation prompt can now appear again.",
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
     * Handle Corey user removal actions.
     *
     * @since    1.0.0
     */
    public function handle_corey_removal_actions()
    {
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
     * Check if current user can access CDSWerx user settings
     *
     * @since    1.0.0
     */
    public function can_access_user_settings()
    {
        // First check if current user is Janice
        if ($this->is_current_user_janice()) {
            error_log(
                "CDSWerx Access Control: Access GRANTED - User is Janice",
            );
            return true;
        }

        // Check if Janice exists on the site
        $janice_user = get_user_by("login", "janice");
        if (
            $janice_user &&
            $janice_user->user_email === "webmaster@counterdesign.ca"
        ) {
            error_log(
                "CDSWerx Access Control: Access DENIED - Janice exists but current user is not Janice",
            );
            return false;
        }

        // Janice doesn't exist, check for site_admin_manager users
        $site_admin_managers = get_users([
            "role" => "site_admin_manager",
        ]);

        if (!empty($site_admin_managers)) {
            // Check if there's a designated manager
            $designated_manager_id = get_option(
                "cdswerx_designated_manager",
                0,
            );
            if ($designated_manager_id) {
                $current_user = wp_get_current_user();
                if ($current_user->ID === $designated_manager_id) {
                    error_log(
                        "CDSWerx Access Control: Access GRANTED - User is designated manager",
                    );
                    return true;
                } else {
                    error_log(
                        "CDSWerx Access Control: Access DENIED - Designated manager exists but current user is not the designated manager",
                    );
                    return false;
                }
            }

            // No designated manager, check if current user is one of the site_admin_managers
            $current_user = wp_get_current_user();
            foreach ($site_admin_managers as $manager) {
                if ($manager->ID === $current_user->ID) {
                    error_log(
                        "CDSWerx Access Control: Access GRANTED - User is site_admin_manager (Janice fallback)",
                    );
                    return true;
                }
            }
            error_log(
                "CDSWerx Access Control: Access DENIED - site_admin_managers exist but current user is not one",
            );
            return false;
        }

        // No Janice, no site_admin_managers - check if current user is administrator
        if (current_user_can("administrator")) {
            error_log(
                "CDSWerx Access Control: Access GRANTED - No authorized users found, current user is administrator",
            );
            return true;
        }

        error_log(
            "CDSWerx Access Control: Access DENIED - User is not administrator",
        );
        return false;
    }

    /**
     * Get access control status for UI display
     *
     * @since    1.0.0
     */
    public function get_access_status()
    {
        $current_user = wp_get_current_user();
        $janice_user = get_user_by("login", "janice");
        $site_admin_managers = get_users(["role" => "site_admin_manager"]);

        $designated_manager_id = get_option("cdswerx_designated_manager", 0);

        $status = [
            "can_access" => $this->can_access_user_settings(),
            "is_janice" => $this->is_current_user_janice(),
            "janice_exists" =>
            $janice_user &&
                $janice_user->user_email === "webmaster@counterdesign.ca",
            "site_admin_managers" => $site_admin_managers,
            "current_user" => $current_user,
            "designated_manager_id" => $designated_manager_id,
            "needs_setup" => false,
            "needs_selection" => false,
        ];

        // Determine what setup is needed
        if (!$status["janice_exists"] && empty($site_admin_managers)) {
            $status["needs_setup"] = true;
        } elseif (
            !$status["janice_exists"] &&
            count($site_admin_managers) > 1 &&
            !$designated_manager_id
        ) {
            $status["needs_selection"] = true;
        }

        error_log(
            "CDSWerx Access Control: Status - " .
                json_encode([
                    "can_access" => $status["can_access"],
                    "is_janice" => $status["is_janice"],
                    "janice_exists" => $status["janice_exists"],
                    "site_admin_count" => count($site_admin_managers),
                    "needs_setup" => $status["needs_setup"],
                    "needs_selection" => $status["needs_selection"],
                ]),
        );

        return $status;
    }

    //function custom_editor_styles() {

    //			wp_enqueue_style( 'customMCEstyles',  CDSWERX_PLUGIN_URL . 'admin/css/cdswerx-admin.css' );
    //			add_editor_style(  CDSWERX_PLUGIN_URL . 'admin/css/cdswerx-admin.css' );
    //		  }

    /* The function called that sets elementor as default editor by replacing hyperlink in post titles on Page, Post, or Template lists with Elementor's editor link as default editor.*/
    //	public function fd_init() {
    //	   require_once 'partials/'.$this->plugin_name.'-admin-elementor-default.php';
    //   }

    /* The function called that registers custom widgets for elementor.*/
    //	public function elementor_widgets_init() {
    //		require_once 'partials/'.$this->plugin_name.'-admin-elementor-custom-widgets.php';
    //	}
    /* The function called that registers customizations for JetEngine Plugin.*/
    //	public function jetengine_customization_init() {
    //	require_once 'partials/'.$this->plugin_name.'-admin-jetengine-customizations.php';
    //	}
    /*		* The function called that registers custom user roles and capabilities.
     * This function is used to create custom user roles and capabilities for the plugin.*/
    //	public function cdswerx_user_roles_init() {
    //	require_once 'partials/'.$this->plugin_name.'-admin-user-roles.php';
    //	}

    /* The function called that registers plugin settings and update management.
     * This function is used to load the plugin settings page and update functionality.*/
    //	public function cdswerx_plugin_settings_init() {
    //		require_once 'partials/'.$this->plugin_name.'-admin-plugin-settings.php';
    //	}

    /**
     * Handle AJAX request to dismiss activation prompt
     *
     * @since    1.0.0
     */
    public function handle_dismiss_activation_prompt()
    {
        error_log("CDSWerx DEBUG: handle_dismiss_activation_prompt() called");

        // Verify nonce
        if (
            !wp_verify_nonce(
                $_POST["nonce"] ?? "",
                "cdswerx_dismiss_activation_prompt",
            )
        ) {
            error_log("CDSWerx DEBUG: AJAX nonce verification failed");
            wp_die("Security check failed");
        }

        error_log(
            "CDSWerx DEBUG: AJAX nonce verified, setting dismissal flags",
        );

        // Set the global dismissal flag
        update_option("cdswerx_activation_prompts_globally_dismissed", true);

        // Also clear the show prompt flag
        delete_option("cdswerx_show_corey_activation_prompt");

        error_log("CDSWerx DEBUG: AJAX dismissal completed");
        wp_die(); // Required for AJAX
    }

    /**
     * Handle reset activation prompts action
     *
     * @since    1.0.0
     */
    public function handle_reset_activation_prompts()
    {
        // Check if this is the reset action
        if (!isset($_GET["cdswerx_reset_activation_prompts"])) {
            return;
        }

        error_log("CDSWerx DEBUG: handle_reset_activation_prompts() called");

        // Verify user capability using new access control
        if (!$this->can_access_user_settings()) {
            error_log("CDSWerx Reset Prompts: Access denied for current user");
            wp_die(
                __(
                    "You do not have sufficient permissions to access this page.",
                    "cdswerx",
                ),
            );
        }

        // Verify nonce
        if (
            !wp_verify_nonce(
                $_GET["_wpnonce"] ?? "",
                "cdswerx_reset_activation_prompts",
            )
        ) {
            wp_die(__("Security check failed."));
        }

        error_log(
            "CDSWerx DEBUG: Resetting activation prompts - clearing global dismissal",
        );

        // Clear the global dismissal flag
        delete_option("cdswerx_activation_prompts_globally_dismissed");

        // Re-enable the show prompt flag
        update_option("cdswerx_show_corey_activation_prompt", true);

        error_log("CDSWerx DEBUG: Activation prompts reset completed");

        // Set success message
        set_transient(
            "cdswerx_admin_message",
            [
                "type" => "success",
                "message" => __(
                    "Activation prompts have been reset and re-enabled.",
                    "cdswerx",
                ),
            ],
            30,
        );

        // Redirect to prevent resubmission
        wp_redirect(admin_url("admin.php?page=cdswerx-users"));
        exit();
    }

    /**
     * Handle user deletion - reset activation prompts if Janice or Corey is deleted
     *
     * @since    1.0.0
     * @param    int $user_id User ID being deleted
     */
    public function handle_user_deletion($user_id)
    {
        error_log(
            "CDSWerx DEBUG: handle_user_deletion() called for user ID: $user_id",
        );

        $user = get_user_by("id", $user_id);
        if (!$user) {
            error_log("CDSWerx DEBUG: User not found for deletion tracking");
            return;
        }

        // Check if this is Janice or Corey
        if ($this->is_target_user($user)) {
            error_log(
                "CDSWerx DEBUG: Target user ({$user->user_login}) is being deleted - resetting activation prompts",
            );
            $this->reset_activation_prompts_on_deactivation($user->user_login);
        }
    }

    /**
     * Handle user removal from blog (multisite)
     *
     * @since    1.0.0
     * @param    int $user_id User ID being removed
     * @param    int $blog_id Blog ID user is being removed from
     */
    public function handle_user_removal($user_id, $blog_id)
    {
        error_log(
            "CDSWerx DEBUG: handle_user_removal() called for user ID: $user_id, blog ID: $blog_id",
        );

        $user = get_user_by("id", $user_id);
        if (!$user) {
            error_log("CDSWerx DEBUG: User not found for removal tracking");
            return;
        }

        // Check if this is Janice or Corey
        if ($this->is_target_user($user)) {
            error_log(
                "CDSWerx DEBUG: Target user ({$user->user_login}) is being removed from blog - resetting activation prompts",
            );
            $this->reset_activation_prompts_on_deactivation($user->user_login);
        }
    }

    /**
     * Handle user role changes - reset if Janice or Corey loses required roles
     *
     * @since    1.0.0
     * @param    int    $user_id   User ID whose role is changing
     * @param    string $role      New role
     * @param    array  $old_roles Previous roles
     */
    public function handle_user_role_change($user_id, $role, $old_roles)
    {
        error_log(
            "CDSWerx DEBUG: handle_user_role_change() called for user ID: $user_id",
        );

        $user = get_user_by("id", $user_id);
        if (!$user) {
            error_log("CDSWerx DEBUG: User not found for role change tracking");
            return;
        }

        // Check if this is Janice or Corey
        if ($this->is_target_user($user)) {
            // Check if user lost required roles
            $required_roles = ["site_admin_manager", "administrator"];
            $had_required_role = false;
            $has_required_role = false;

            // Check old roles
            foreach ($old_roles as $old_role) {
                if (in_array($old_role, $required_roles)) {
                    $had_required_role = true;
                    break;
                }
            }

            // Check current roles
            foreach ($user->roles as $current_role) {
                if (in_array($current_role, $required_roles)) {
                    $has_required_role = true;
                    break;
                }
            }

            // If user had required role but no longer has it, reset prompts
            if ($had_required_role && !$has_required_role) {
                error_log(
                    "CDSWerx DEBUG: Target user ({$user->user_login}) lost required roles - resetting activation prompts",
                );
                $this->reset_activation_prompts_on_deactivation(
                    $user->user_login,
                );
            }
        }
    }

    /**
     * Check if user is a target user (Janice or Corey)
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
     * Handle AJAX request to save custom code
     *
     * @since    1.0.0
     */
    public function handle_save_custom_code()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_custom_code_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        // Get and validate input
        $code_type = sanitize_text_field($_POST['code_type'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? 'general');
        $name = sanitize_text_field($_POST['name'] ?? '');
        $content = wp_unslash($_POST['content'] ?? '');
        $is_active = intval($_POST['is_active'] ?? 1);
        $load_priority = intval($_POST['load_priority'] ?? 10);

        if (!in_array($code_type, ['css', 'js'])) {
            wp_send_json_error(['message' => 'Invalid code type']);
        }

        if (empty($name) || empty($content)) {
            wp_send_json_error(['message' => 'Name and content are required']);
        }

        // Initialize code manager
        $code_manager = CDSWerx_Code_Manager::get_instance();

        // Save the code
        $result = $code_manager->save_code([
            'code_type' => $code_type,
            'category' => $category,
            'name' => $name,
            'content' => $content,
            'is_active' => $is_active,
            'load_priority' => $load_priority
        ]);

        if ($result) {
            wp_send_json_success(['message' => 'Code saved successfully', 'id' => $result]);
        } else {
            wp_send_json_error(['message' => 'Failed to save code']);
        }
    }

    /**
     * Handle AJAX request to update custom code
     *
     * @since    1.0.0
     */
    public function handle_update_custom_code()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_custom_code_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        // Get and validate input
        $code_id = intval($_POST['code_id'] ?? 0);
        $code_type = sanitize_text_field($_POST['code_type'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? 'general');
        $name = sanitize_text_field($_POST['name'] ?? '');
        $content = wp_unslash($_POST['content'] ?? '');
        $is_active = intval($_POST['is_active'] ?? 1);
        $load_priority = intval($_POST['load_priority'] ?? 10);

        if (!$code_id) {
            wp_send_json_error(['message' => 'Invalid code ID']);
        }

        if (!in_array($code_type, ['css', 'js'])) {
            wp_send_json_error(['message' => 'Invalid code type']);
        }

        if (empty($name) || empty($content)) {
            wp_send_json_error(['message' => 'Name and content are required']);
        }

        // Initialize code manager
        $code_manager = CDSWerx_Code_Manager::get_instance();

        // Update the code using save_code method with ID
        $result = $code_manager->save_code([
            'id' => $code_id,
            'code_type' => $code_type,
            'category' => $category,
            'name' => $name,
            'content' => $content,
            'is_active' => $is_active,
            'load_priority' => $load_priority
        ]);

        if ($result) {
            wp_send_json_success(['message' => 'Code updated successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to update code']);
        }
    }

    /**
     * Handle AJAX request to load custom code
     *
     * @since    1.0.0
     */
    public function handle_load_custom_code()
    {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'cdswerx_custom_code_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $code_type = sanitize_text_field($_GET['code_type'] ?? '');
        $category = sanitize_text_field($_GET['category'] ?? '');

        if (!in_array($code_type, ['css', 'js'])) {
            wp_send_json_error(['message' => 'Invalid code type']);
        }

        // Initialize code manager
        $code_manager = CDSWerx_Code_Manager::get_instance();

        // Get codes
        $codes = $code_manager->get_codes_by_type($code_type, $category);

        wp_send_json_success(['codes' => $codes]);
    }

    /**
     * Handle AJAX request to get code by ID
     *
     * @since    1.0.0
     */
    public function handle_get_code_by_id()
    {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'cdswerx_custom_code_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $code_id = intval($_GET['code_id'] ?? 0);

        if (!$code_id) {
            wp_send_json_error(['message' => 'Invalid code ID']);
        }

        // Initialize code manager
        $code_manager = CDSWerx_Code_Manager::get_instance();

        // Get code
        $code = $code_manager->get_code_by_id($code_id);

        if ($code) {
            wp_send_json_success(['code' => $code]);
        } else {
            wp_send_json_error(['message' => 'Code not found']);
        }
    }

    /**
     * Handle AJAX request to delete custom code
     *
     * @since    1.0.0
     */
    public function handle_delete_custom_code()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_custom_code_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $code_id = intval($_POST['code_id'] ?? 0);

        if (!$code_id) {
            wp_send_json_error(['message' => 'Invalid code ID']);
        }

        // Initialize code manager
        $code_manager = CDSWerx_Code_Manager::get_instance();

        // Delete the code
        $result = $code_manager->delete_code($code_id);

        if ($result) {
            wp_send_json_success(['message' => 'Code deleted successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to delete code']);
        }
    }

    /**
     * Handle AJAX request to toggle code status
     *
     * @since    1.0.0
     */
    public function handle_toggle_code_status()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'cdswerx_custom_code_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $code_id = intval($_POST['code_id'] ?? 0);

        if (!$code_id) {
            wp_send_json_error(['message' => 'Invalid code ID']);
        }

        // Initialize code manager
        $code_manager = CDSWerx_Code_Manager::get_instance();

        // Get current code
        $code = $code_manager->get_code_by_id($code_id);
        
        if (!$code) {
            wp_send_json_error(['message' => 'Code not found']);
        }

        // Toggle status
        $new_status = $code->is_active ? 0 : 1;
        
        // Update the code
        $result = $code_manager->save_code([
            'id' => $code_id,
            'code_type' => $code->code_type,
            'category' => $code->category,
            'name' => $code->name,
            'content' => $code->content,
            'is_active' => $new_status,
            'load_priority' => $code->load_priority
        ]);

        if ($result) {
            wp_send_json_success(['message' => 'Code status updated successfully']);
        } else {
            wp_send_json_error(['message' => 'Failed to update code status']);
        }
    }
}
error_log(
    "CDSWerx: Permanent disable settings reset on plugin deactivation",
);
