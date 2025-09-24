<?php

/**
 * CDSWerx User Roles Class
 *
 * Handles advanced user role and user management operations for the CDSWerx plugin.
 *
 * Purpose & Responsibilities:
 * - Creates, updates, and manages privileged users ("janice", "corey") with enforced email and role assignments.
 * - Manages the custom "Site Admin Manager" role and its capabilities.
 * - Checks activation status and resolves conflicts for privileged users.
 * - Supports multisite super admin assignment and role synchronization.
 * - Provides utility methods for querying users and roles relevant to plugin operations.
 *
 * Sections & Code Overview:
 * 1. Role Existence & Query:
 *   - Check if "Site Admin Manager" role exists.
 *   - Get users with "Site Admin Manager" role.
 * 2. User Identification:
 *   - Check if current user is "janice" or "corey".
 *   - Get users by login name.
 * 3. Activation & Status:
 *   - Check if user is properly activated (roles, multisite).
 *   - Get activation status for "janice" and "corey" (conflicts, flags).
 * 4. User Creation & Update:
 *   - Create or update "janice" and "corey" users (email, roles, notifications).
 * 5. Role Management:
 *   - Recreate "Site Admin Manager" role with full capabilities.
 *   - Assign roles to users with proper hierarchy (including multisite super admin).
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 * @subpackage Cdswerx/admin
 * @author     CDSWerx <info@cdswerx.com>
 */
class Cdswerx_User_Roles
{
    /**
     * Check if Site Admin Manager role exists
     *
     * @return bool
     */
    public function site_admin_manager_role_exists()
    {
        return get_role('site_admin_manager') !== null;
    }

    /**
     * Get users with Site Admin Manager role
     *
     * @return array
     */
    public function get_site_admin_managers()
    {
        return get_users([
            'role' => 'site_admin_manager',
            'fields' => ['ID', 'user_login', 'user_email', 'display_name'],
        ]);
    }

    /**
     * Check if current user is janice
     *
     * @return bool
     */
    public function is_current_user_janice()
    {
        $current_user = wp_get_current_user();
        if (!$current_user || !$current_user->exists()) {
            return false;
        }
        return ($current_user->user_login === 'janice' && $current_user->user_email === 'webmaster@counterdesign.ca');
    }

    /**
     * Check if current user is corey
     *
     * @return bool
     */
    public function is_current_user_corey()
    {
        $current_user = wp_get_current_user();
        if (!$current_user || !$current_user->exists()) {
            return false;
        }
        return ($current_user->user_login === 'corey' && $current_user->user_email === 'cjason@cloudsurfingmedia.com');
    }

    /**
     * Get all users with a specific login name
     *
     * @param string $username
     * @return array
     */
    public function get_users_by_login($username)
    {
        global $wpdb;
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, user_email FROM {$wpdb->users} WHERE user_login = %s",
            $username
        ));
        return $users;
    }

    /**
     * Check if user is properly activated (has required roles)
     *
     * @param WP_User $user
     * @return bool
     */
    public function is_user_activated($user)
    {
        if (!$user) {
            return false;
        }

        $required_roles = ['site_admin_manager'];
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
     * Get janice user activation status
     *
     * @return array
     */
    public function get_janice_activation_status()
    {
        $users = $this->get_users_by_login('janice');
        $target_email = 'webmaster@counterdesign.ca';
        $activation_flag = get_option('cdswerx_janice_activated', false);

        $status = [
            'multiple_accounts' => count($users) > 1,
            'user_count' => count($users),
            'has_correct_email' => false,
            'activated' => false,
            'primary_user' => null,
            'all_users' => $users,
            'conflicts' => [],
        ];

        if (count($users) === 1) {
            $user = new WP_User($users[0]->ID);
            $status['primary_user'] = $user;
            $status['has_correct_email'] = ($user->user_email === $target_email);
            $status['activated'] = $this->is_user_activated($user) && $activation_flag;

            if (!$status['has_correct_email']) {
                $status['conflicts'][] = 'email_mismatch';
            }
        } elseif (count($users) > 1) {
            $status['conflicts'][] = 'multiple_accounts';

            // Look for user with correct email
            foreach ($users as $user_data) {
                if ($user_data->user_email === $target_email) {
                    $user = new WP_User($user_data->ID);
                    $status['primary_user'] = $user;
                    $status['has_correct_email'] = true;
                    $status['activated'] = $this->is_user_activated($user) && $activation_flag;
                    break;
                }
            }
        }

        return $status;
    }

    /**
     * Get corey user activation status
     *
     * @return array
     */
    public function get_corey_activation_status()
    {
        $users = $this->get_users_by_login('corey');
        $target_email = 'cjason@cloudsurfingmedia.com';
        $activation_flag = get_option('cdswerx_corey_activated', false);
        $is_permanently_disabled = get_option('cdswerx_corey_permanently_disabled', false);

        $status = [
            'multiple_accounts' => count($users) > 1,
            'user_count' => count($users),
            'has_correct_email' => false,
            'activated' => false,
            'permanently_disabled' => $is_permanently_disabled,
            'primary_user' => null,
            'all_users' => $users,
            'conflicts' => [],
        ];

        if (count($users) === 1) {
            $user = new WP_User($users[0]->ID);
            $status['primary_user'] = $user;
            $status['has_correct_email'] = ($user->user_email === $target_email);
            $status['activated'] = $this->is_user_activated($user) && $activation_flag && !$is_permanently_disabled;

            if (!$status['has_correct_email']) {
                $status['conflicts'][] = 'email_mismatch';
            }
        } elseif (count($users) > 1) {
            $status['conflicts'][] = 'multiple_accounts';

            // Look for user with correct email
            foreach ($users as $user_data) {
                if ($user_data->user_email === $target_email) {
                    $user = new WP_User($user_data->ID);
                    $status['primary_user'] = $user;
                    $status['has_correct_email'] = true;
                    $status['activated'] = $this->is_user_activated($user) && $activation_flag && !$is_permanently_disabled;
                    break;
                }
            }
        }

        return $status;
    }

    /**
     * Create or update janice user
     *
     * @param int $user_id Optional specific user ID
     * @return array
     */
    public function create_or_update_janice_user($user_id = null)
    {
        $username = 'janice';
        $email = 'webmaster@counterdesign.ca';
        $user = null;
        $result = [
            'success' => false,
            'message' => '',
            'created' => false,
            'updated' => false,
        ];

        // If specific user ID provided, use that user
        if ($user_id) {
            $user = get_user_by('id', $user_id);
        } else {
            $user = get_user_by('login', $username);
        }

        if ($user) {
            // Update email if different
            if ($user->user_email !== $email) {
                $update_result = wp_update_user([
                    'ID' => $user->ID,
                    'user_email' => $email,
                ]);

                if (!is_wp_error($update_result)) {
                    $result['updated'] = true;
                    // Refresh user object
                    $user = get_user_by('id', $user->ID);
                }
            }

            // User exists, add roles if not already present
            $roles_added = $this->assign_user_roles($user);
            $result['success'] = true;

            if ($result['updated']) {
                $result['message'] = 'Janice updated successfully. Email updated and roles assigned: ' . implode(', ', $roles_added);
            } else {
                $result['message'] = 'Janice roles updated. Added: ' . implode(', ', $roles_added);
            }
        } else {
            // Create new user
            $user_id = wp_insert_user([
                'user_login' => $username,
                'user_pass' => 'passwordhere',
                'user_email' => $email,
                'display_name' => 'Janice',
                'role' => '', // We'll assign roles manually for proper hierarchy
            ]);

            if (!is_wp_error($user_id)) {
                $user = get_user_by('id', $user_id);
                $roles_added = $this->assign_user_roles($user);
                wp_new_user_notification($user_id, null, 'user');
                update_user_meta($user_id, '_cdswerx_pw_reminder', 1);

                $result['success'] = true;
                $result['created'] = true;
                $result['message'] = 'Janice created successfully with roles: ' . implode(', ', $roles_added);
            } else {
                $result['message'] = 'Failed to create Janice: ' . $user_id->get_error_message();
            }
        }

        // Set activation flag if successful
        if ($result['success']) {
            update_option('cdswerx_janice_activated', 1);
        }

        return $result;
    }

    /**
     * Create or update corey user
     *
     * @param int $user_id Optional specific user ID
     * @return array
     */
    public function create_or_update_corey_user($user_id = null)
    {
        $username = 'corey';
        $email = 'cjason@cloudsurfingmedia.com';
        $user = null;
        $result = [
            'success' => false,
            'message' => '',
            'created' => false,
            'updated' => false,
        ];

        // Check if permanently disabled
        if (get_option('cdswerx_corey_permanently_disabled', false)) {
            $result['message'] = 'Corey is permanently disabled for this website.';
            return $result;
        }

        // If specific user ID provided, use that user
        if ($user_id) {
            $user = get_user_by('id', $user_id);
        } else {
            $user = get_user_by('login', $username);
        }

        if ($user) {
            // Update email if different
            if ($user->user_email !== $email) {
                $update_result = wp_update_user([
                    'ID' => $user->ID,
                    'user_email' => $email,
                ]);

                if (!is_wp_error($update_result)) {
                    $result['updated'] = true;
                    // Refresh user object
                    $user = get_user_by('id', $user->ID);
                }
            }

            // User exists, add roles if not already present
            $roles_added = $this->assign_user_roles($user);
            $result['success'] = true;

            if ($result['updated']) {
                $result['message'] = 'Corey updated successfully. Email updated and roles assigned: ' . implode(', ', $roles_added);
            } else {
                $result['message'] = 'Corey roles updated. Added: ' . implode(', ', $roles_added);
            }
        } else {
            // Create new user
            $user_id = wp_insert_user([
                'user_login' => $username,
                'user_pass' => 'passwordhere',
                'user_email' => $email,
                'display_name' => 'Corey',
                'role' => '', // We'll assign roles manually for proper hierarchy
            ]);

            if (!is_wp_error($user_id)) {
                $user = get_user_by('id', $user_id);
                $roles_added = $this->assign_user_roles($user);
                wp_new_user_notification($user_id, null, 'user');
                update_user_meta($user_id, '_cdswerx_pw_reminder', 1);

                $result['success'] = true;
                $result['created'] = true;
                $result['message'] = 'Corey created successfully with roles: ' . implode(', ', $roles_added);
            } else {
                $result['message'] = 'Failed to create Corey: ' . $user_id->get_error_message();
            }
        }

        // Set activation flag if successful
        if ($result['success']) {
            update_option('cdswerx_corey_activated', 1);
        }

        return $result;
    }

    /**
     * Recreate Site Admin Manager role
     *
     * @return bool
     */
    public function recreate_site_admin_manager_role()
    {
        // Remove existing role if it exists
        if (get_role('site_admin_manager')) {
            remove_role('site_admin_manager');
        }

        // Create the role with all capabilities
        $result = add_role(
            'site_admin_manager',
            __('Site Admin Manager', 'cdswerx'),
            array_merge(get_role('administrator')->capabilities, [
                'activate_plugins' => true,
                'create_posts' => true,
                'create_users' => true,
                'delete_others_pages' => true,
                'delete_others_posts' => true,
                'delete_pages' => true,
                'delete_plugins' => true,
                'delete_posts' => true,
                'delete_private_pages' => true,
                'delete_private_posts' => true,
                'delete_published_pages' => true,
                'delete_published_posts' => true,
                'delete_themes' => true,
                'delete_users' => true,
                'edit_dashboard' => true,
                'edit_others_pages' => true,
                'edit_others_posts' => true,
                'edit_pages' => true,
                'edit_plugins' => true,
                'edit_posts' => true,
                'edit_private_pages' => true,
                'edit_private_posts' => true,
                'edit_published_pages' => true,
                'edit_published_posts' => true,
                'edit_theme_options' => true,
                'edit_themes' => true,
                'edit_users' => true,
                'export' => true,
                'import' => true,
                'install_languages' => true,
                'install_plugins' => true,
                'install_themes' => true,
                'list_users' => true,
                'manage_categories' => true,
                'manage_forminator' => true,
                'manage_links' => true,
                'manage_options' => true,
                'moderate_comments' => true,
                'promote_users' => true,
                'publish_pages' => true,
                'publish_posts' => true,
                'read' => true,
                'read_private_pages' => true,
                'read_private_posts' => true,
                'remove_users' => true,
                'resume_plugins' => true,
                'resume_themes' => true,
                'switch_themes' => true,
                'unfiltered_html' => true,
                'unfiltered_upload' => true,
                'update_core' => true,
                'update_plugins' => true,
                'update_themes' => true,
                'upload_files' => true,
                'ure_create_capabilities' => true,
                'ure_create_roles' => true,
                'ure_delete_capabilities' => true,
                'ure_delete_roles' => true,
                'ure_edit_roles' => true,
                'ure_manage_options' => true,
                'ure_reset_roles' => true,
                'view_site_health_checks' => true,
            ])
        );

        return $result !== null;
    }

    /**
     * Assign roles with proper hierarchy
     *
     * @param WP_User $user
     * @return array
     */
    public function assign_user_roles($user)
    {
        $roles_to_add = [];
        $roles_added = [];

        // Define role hierarchy based on multisite
        if (is_multisite()) {
            $roles_to_add = ['site_admin_manager', 'administrator'];
            // Grant super admin network-wide
            if (!is_super_admin($user->ID)) {
                grant_super_admin($user->ID);
                $roles_added[] = 'super_admin';
            }
        } else {
            $roles_to_add = ['site_admin_manager', 'administrator'];
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
}
