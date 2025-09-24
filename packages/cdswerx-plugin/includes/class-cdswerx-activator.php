<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 *
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 * @author     CDSWerx <info@cdswerx.com>
 */
class Cdswerx_Activator
{
    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        // Add default plugin options
        if (!get_option("cdswerx_options")) {
            add_option("cdswerx_options", [
                "version" => CDSWERX_VERSION,
                "activation_date" => current_time("mysql"),
                "activation_prompts" => 1, // Enable activation prompts by default
            ]);
        }

        // Ensure activation prompts are not globally dismissed on first activation
        if (!get_option("cdswerx_activation_prompts_globally_dismissed")) {
            add_option("cdswerx_activation_prompts_globally_dismissed", false);
        }

        // Initialize custom code database
        require_once CDSWERX_PLUGIN_PATH . 'includes/class-cdswerx-database.php';
        $db_manager = new CDSWerx_Database();
        $db_manager->create_or_update_tables();

        // Create custom Site Admin Manager role
        self::create_site_admin_manager_role();

        // Always create janice user (auto-activation)
        self::create_janice_user();

        // Set janice activation flags
        update_option("cdswerx_janice_activated", 1);

        // Check if we should show activation prompt for Corey
        // Only show if Corey is not permanently disabled
        if (!get_option("cdswerx_corey_permanently_disabled", false)) {
            // Don't auto-create Corey, but show prompt
            update_option("cdswerx_show_corey_activation_prompt", 1);
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create Site Admin Manager role with enhanced capabilities
     *
     * @since    1.0.0
     */
    private static function create_site_admin_manager_role()
    {
        // Only create if role doesn't exist
        if (!get_role("site_admin_manager")) {
            add_role(
                "site_admin_manager",
                __("Site Admin Manager"),
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
        }
    }

    /**
     * Create janice user with Site Admin Manager role
     *
     * @since    1.0.0
     */
    private static function create_janice_user()
    {
        $username = "janice";
        $email = "webmaster@counterdesign.ca";

        // Check if user already exists
        $user = get_user_by("login", $username);

        if (!$user) {
            // Create new user
            $user_id = wp_insert_user([
                "user_login" => $username,
                "user_pass" => "passwordhere",
                "user_email" => $email,
                "display_name" => "Janice",
                "role" => "", // We'll assign roles manually
            ]);

            if (!is_wp_error($user_id)) {
                $user = get_user_by("id", $user_id);

                // Assign roles with proper hierarchy
                self::assign_user_roles($user);

                // Mark as plugin-created user
                update_user_meta($user_id, "_cdswerx_pw_reminder", 1);

                // Send notification email
                wp_new_user_notification($user_id, null, "user");
            }
        } else {
            // User exists, ensure proper roles are assigned
            self::assign_user_roles($user);

            // Update email if different
            if ($user->user_email !== $email) {
                wp_update_user([
                    "ID" => $user->ID,
                    "user_email" => $email,
                ]);
            }
        }
    }

    /**
     * Create corey user with Site Admin Manager role (called via prompt selection)
     *
     * @since    1.0.0
     */
    public static function create_corey_user()
    {
        $username = "corey";
        $email = "cjason@cloudsurfingmedia.com";

        // Check if permanently disabled
        if (get_option("cdswerx_corey_permanently_disabled", false)) {
            return false;
        }

        // Check if user already exists
        $user = get_user_by("login", $username);

        if (!$user) {
            // Create new user
            $user_id = wp_insert_user([
                "user_login" => $username,
                "user_pass" => "passwordhere",
                "user_email" => $email,
                "display_name" => "Corey",
                "role" => "", // We'll assign roles manually
            ]);

            if (!is_wp_error($user_id)) {
                $user = get_user_by("id", $user_id);

                // Assign roles with proper hierarchy
                self::assign_user_roles($user);

                // Mark as plugin-created user
                update_user_meta($user_id, "_cdswerx_pw_reminder", 1);

                // Set activation flag
                update_option("cdswerx_corey_activated", 1);

                // Send notification email
                wp_new_user_notification($user_id, null, "user");

                return true;
            }
        } else {
            // User exists, ensure proper roles are assigned
            self::assign_user_roles($user);

            // Update email if different
            if ($user->user_email !== $email) {
                wp_update_user([
                    "ID" => $user->ID,
                    "user_email" => $email,
                ]);
            }

            // Set activation flag
            update_option("cdswerx_corey_activated", 1);
            return true;
        }

        return false;
    }

    /**
     * Assign roles with proper hierarchy for multisite support
     *
     * @since    1.0.0
     * @param    WP_User $user User object
     */
    private static function assign_user_roles($user)
    {
        if (!$user || is_wp_error($user)) {
            return;
        }

        // Define role hierarchy based on multisite
        $roles_to_add = [];

        if (is_multisite()) {
            $roles_to_add = ["site_admin_manager", "administrator"];
            // Grant super admin network-wide
            if (!is_super_admin($user->ID)) {
                grant_super_admin($user->ID);
            }
        } else {
            $roles_to_add = ["site_admin_manager", "administrator"];
        }

        // Add roles if user doesn't already have them
        foreach ($roles_to_add as $role) {
            if (!$user->has_cap($role)) {
                $user->add_role($role);
            }
        }
    }
}
