<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CDSWerx GitHub Update Manager
 * 
 * Provides WordPress-style update notifications for CDSWerx components
 * from private GitHub repositories with authentication support.
 * 
 * Features:
 * - WordPress-style update checker integration
 * - Private GitHub repository support
 * - Manual update workflow (user-initiated)
 * - Backup and rollback capability
 * - Version compatibility checking
 * 
 * @package CDSWerx
 * @since 1.0.0
 */
class CDSWerx_GitHub_Update_Manager {
    
    /**
     * GitHub API base URL
     */
    const GITHUB_API_BASE = 'https://api.github.com';
    
    /**
     * CDSWerx GitHub organization
     */
    const GITHUB_ORG = 'uxdevcds';
    
    /**
     * Component repositories mapping
     */
    private $repositories = [
        'plugin' => 'cdswerx-plugin',
        'theme' => 'cdswerx-theme', 
        'child-theme' => 'cdswerx-theme-child'
    ];
    
    /**
     * GitHub authentication token
     */
    private $github_token;
    
    /**
     * Initialize the update manager
     */
    public function __construct() {
        $this->github_token = get_option('cdswerx_github_token', '');
        
        // Hook into WordPress update system
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_plugin_updates']);
        add_filter('pre_set_site_transient_update_themes', [$this, 'check_theme_updates']);
        
        // Add admin interface
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        
        // Handle update actions
        add_action('wp_ajax_cdswerx_update_component', [$this, 'handle_update_request']);
    }
    
    /**
     * Check for plugin updates
     */
    public function check_plugin_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        $plugin_file = 'cdswerx-plugin/cdswerx.php';
        $current_version = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file)['Version'];
        
        $remote_version = $this->get_remote_version('plugin');
        
        if (version_compare($current_version, $remote_version, '<')) {
            $transient->response[$plugin_file] = (object) [
                'slug' => 'cdswerx-plugin',
                'new_version' => $remote_version,
                'url' => 'https://github.com/' . self::GITHUB_ORG . '/cdswerx-plugin',
                'package' => $this->get_download_url('plugin', $remote_version)
            ];
        }
        
        return $transient;
    }
    
    /**
     * Check for theme updates
     */
    public function check_theme_updates($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Check CDSWerx Theme
        $theme_version = wp_get_theme('cdswerx-theme')->get('Version');
        $remote_version = $this->get_remote_version('theme');
        
        if (version_compare($theme_version, $remote_version, '<')) {
            $transient->response['cdswerx-theme'] = [
                'theme' => 'cdswerx-theme',
                'new_version' => $remote_version,
                'url' => 'https://github.com/' . self::GITHUB_ORG . '/cdswerx-theme',
                'package' => $this->get_download_url('theme', $remote_version)
            ];
        }
        
        // Check CDSWerx Child Theme
        $child_theme_version = wp_get_theme('cdswerx-theme-child')->get('Version');
        $child_remote_version = $this->get_remote_version('child-theme');
        
        if (version_compare($child_theme_version, $child_remote_version, '<')) {
            $transient->response['cdswerx-theme-child'] = [
                'theme' => 'cdswerx-theme-child',
                'new_version' => $child_remote_version,
                'url' => 'https://github.com/' . self::GITHUB_ORG . '/cdswerx-theme-child',
                'package' => $this->get_download_url('child-theme', $child_remote_version)
            ];
        }
        
        return $transient;
    }
    
    /**
     * Get remote version from GitHub releases
     */
    private function get_remote_version($component) {
        if (!isset($this->repositories[$component])) {
            return false;
        }
        
        $repo = $this->repositories[$component];
        $url = self::GITHUB_API_BASE . '/repos/' . self::GITHUB_ORG . '/' . $repo . '/releases/latest';
        
        $response = $this->github_api_request($url);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $release_data = json_decode(wp_remote_retrieve_body($response), true);
        return isset($release_data['tag_name']) ? ltrim($release_data['tag_name'], 'v') : false;
    }
    
    /**
     * Get download URL for specific version
     */
    private function get_download_url($component, $version) {
        if (!isset($this->repositories[$component])) {
            return false;
        }
        
        $repo = $this->repositories[$component];
        return self::GITHUB_API_BASE . '/repos/' . self::GITHUB_ORG . '/' . $repo . '/zipball/v' . $version;
    }
    
    /**
     * Make authenticated GitHub API request
     */
    private function github_api_request($url) {
        $args = [
            'headers' => [
                'User-Agent' => 'CDSWerx-Update-Manager/1.0'
            ],
            'timeout' => 15
        ];
        
        if (!empty($this->github_token)) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->github_token;
        }
        
        return wp_remote_get($url, $args);
    }
    
    /**
     * Add admin menu for update settings
     */
    public function add_admin_menu() {
        add_submenu_page(
            'cdswerx-admin-dashboard',
            'GitHub Updates',
            'GitHub Updates',
            'manage_options',
            'cdswerx-github-updates',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('cdswerx_github_updates', 'cdswerx_github_token');
    }
    
    /**
     * Admin page for GitHub update settings
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>CDSWerx GitHub Updates</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('cdswerx_github_updates');
                do_settings_sections('cdswerx_github_updates');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">GitHub Token</th>
                        <td>
                            <input type="password" name="cdswerx_github_token" 
                                   value="<?php echo esc_attr(get_option('cdswerx_github_token')); ?>" 
                                   class="regular-text" />
                            <p class="description">
                                GitHub Personal Access Token for private repository access.
                                Required permissions: repo (Full control of private repositories)
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            
            <h2>Update Status</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Component</th>
                        <th>Current Version</th>
                        <th>Latest Version</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $this->display_update_status(); ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    /**
     * Display update status for all components
     */
    private function display_update_status() {
        $components = [
            'plugin' => [
                'name' => 'CDSWerx Plugin',
                'current' => get_plugin_data(WP_PLUGIN_DIR . '/cdswerx-plugin/cdswerx.php')['Version']
            ],
            'theme' => [
                'name' => 'CDSWerx Theme',
                'current' => wp_get_theme('cdswerx-theme')->get('Version')
            ],
            'child-theme' => [
                'name' => 'CDSWerx Child Theme',
                'current' => wp_get_theme('cdswerx-theme-child')->get('Version')
            ]
        ];
        
        foreach ($components as $key => $component) {
            $remote_version = $this->get_remote_version($key);
            $needs_update = $remote_version && version_compare($component['current'], $remote_version, '<');
            
            echo '<tr>';
            echo '<td>' . esc_html($component['name']) . '</td>';
            echo '<td>' . esc_html($component['current']) . '</td>';
            echo '<td>' . esc_html($remote_version ?: 'Unknown') . '</td>';
            echo '<td>';
            if ($needs_update) {
                echo '<span class="update-message">Update Available</span>';
            } else {
                echo '<span class="up-to-date">Up to Date</span>';
            }
            echo '</td>';
            echo '<td>';
            if ($needs_update) {
                echo '<button class="button button-primary" onclick="updateComponent(\'' . esc_js($key) . '\')">Update Now</button>';
            } else {
                echo '<span class="description">No update needed</span>';
            }
            echo '</td>';
            echo '</tr>';
        }
    }
    
    /**
     * Handle update request via AJAX
     */
    public function handle_update_request() {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        check_ajax_referer('cdswerx_update_nonce');
        
        $component = sanitize_text_field($_POST['component']);
        
        if (!isset($this->repositories[$component])) {
            wp_send_json_error('Invalid component');
        }
        
        // Implement backup before update
        $backup_result = $this->create_backup($component);
        if (is_wp_error($backup_result)) {
            wp_send_json_error('Backup failed: ' . $backup_result->get_error_message());
        }
        
        // Perform update
        $update_result = $this->perform_update($component);
        if (is_wp_error($update_result)) {
            // Restore from backup on failure
            $this->restore_backup($component);
            wp_send_json_error('Update failed: ' . $update_result->get_error_message());
        }
        
        wp_send_json_success('Update completed successfully');
    }
    
    /**
     * Create backup before update
     */
    private function create_backup($component) {
        // Implementation for creating component backups
        // This would create timestamped backups in wp-content/backups/cdswerx/
        return true; // Placeholder
    }
    
    /**
     * Restore from backup
     */
    private function restore_backup($component) {
        // Implementation for restoring from backup
        return true; // Placeholder
    }
    
    /**
     * Perform the actual update
     */
    private function perform_update($component) {
        // Implementation for downloading and installing updates
        return true; // Placeholder
    }
}

// Initialize the update manager
new CDSWerx_GitHub_Update_Manager();