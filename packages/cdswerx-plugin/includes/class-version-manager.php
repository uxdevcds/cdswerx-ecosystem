<?php

/**
 * Version Manager Class
 *
 * Handles dynamic version management for CDSWerx ecosystem components,
 * including automatic version increments, cache management, and coordination
 * between parent and child themes.
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
 * Version Manager Class
 *
 * Manages version coordination across CDSWerx ecosystem components
 * with automatic versioning, cache management, and sync coordination.
 *
 * @since      1.0.4
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 * @author     CDSWerx <info@cdswerx.com>
 */
class Version_Manager
{
    /**
     * Version manager identifier
     *
     * @since    1.0.4
     * @access   protected
     * @var      string    $manager_name    The string used to identify this version manager.
     */
    protected $manager_name;

    /**
     * Current version manager version
     *
     * @since    1.0.4
     * @access   protected
     * @var      string    $version    The current version of the version manager.
     */
    protected $version;

    /**
     * Version history storage
     *
     * @since    1.0.4
     * @access   protected
     * @var      array    $version_history    Stores version change history.
     */
    protected $version_history;

    /**
     * Current version information
     *
     * @since    1.0.4
     * @access   protected
     * @var      array    $version_info    Current version information for all components.
     */
    protected $version_info;

    /**
     * Auto-versioning settings
     *
     * @since    1.0.4
     * @access   protected
     * @var      array    $auto_version_settings    Auto-versioning configuration.
     */
    protected $auto_version_settings;

    /**
     * Cache management settings
     *
     * @since    1.0.4
     * @access   protected
     * @var      array    $cache_settings    Cache management configuration.
     */
    protected $cache_settings;

    /**
     * Initialize the Version Manager
     *
     * @since    1.0.4
     */
    public function __construct()
    {
        $this->manager_name = 'cdswerx-version-manager';
        $this->version = '1.0.4';
        $this->version_history = array();
        $this->version_info = array();
        
        $this->load_version_settings();
        $this->init_version_tracking();
        $this->setup_version_hooks();
    }

    /**
     * Load version management settings
     *
     * @since    1.0.4
     */
    private function load_version_settings()
    {
        $this->auto_version_settings = get_option('cdswerx_auto_version_settings', array(
            'enabled' => true,
            'css_changes' => true,
            'major_updates' => true,
            'minor_updates' => true,
            'patch_updates' => true,
            'increment_type' => 'patch', // 'major', 'minor', 'patch'
        ));

        $this->cache_settings = get_option('cdswerx_cache_settings', array(
            'enabled' => true,
            'auto_clear' => true,
            'version_based' => true,
            'supported_caches' => array('wp_cache', 'object_cache', 'opcache'),
        ));
    }

    /**
     * Initialize version tracking
     *
     * @since    1.0.4
     */
    private function init_version_tracking()
    {
        $this->version_info = get_option('cdswerx_version_info', array(
            'plugin' => array(
                'current' => CDSWERX_VERSION,
                'last_updated' => null,
            ),
            'theme' => array(
                'current' => $this->get_theme_version(),
                'last_updated' => null,
            ),
            'child_theme' => array(
                'current' => $this->get_child_theme_version(),
                'last_updated' => null,
            ),
            'hello_elementor' => array(
                'current' => $this->get_hello_elementor_version(),
                'last_updated' => null,
            ),
        ));

        $this->version_history = get_option('cdswerx_version_history', array());
    }

    /**
     * Setup version management hooks
     *
     * @since    1.0.4
     */
    private function setup_version_hooks()
    {
        // CSS change detection hooks
        add_action('cdswerx_advanced_css_updated', array($this, 'handle_css_version_increment'));
        add_action('customize_save_after', array($this, 'handle_customizer_version_increment'));
        
        // Theme update hooks
        add_action('upgrader_process_complete', array($this, 'handle_theme_update'), 10, 2);
        add_action('switch_theme', array($this, 'handle_theme_switch'));
        
        // Cache management hooks
        add_action('cdswerx_version_incremented', array($this, 'handle_cache_clearing'));
        
        // Frontend version management
        add_action('wp_enqueue_scripts', array($this, 'manage_frontend_versions'), 5);
        add_action('admin_enqueue_scripts', array($this, 'manage_admin_versions'), 5);
    }

    /**
     * Get current theme version
     *
     * @since    1.0.4
     * @return   string    Theme version
     */
    private function get_theme_version()
    {
        $theme = wp_get_theme();
        return $theme->get('Version') ?: '1.0.0';
    }

    /**
     * Get current child theme version
     *
     * @since    1.0.4
     * @return   string    Child theme version
     */
    private function get_child_theme_version()
    {
        if (is_child_theme()) {
            $child_theme = wp_get_theme();
            return $child_theme->get('Version') ?: '1.0.0';
        }
        return null;
    }

    /**
     * Get Hello Elementor version
     *
     * @since    1.0.4
     * @return   string|null    Hello Elementor version or null if not installed
     */
    private function get_hello_elementor_version()
    {
        $hello_theme = wp_get_theme('hello-elementor');
        if ($hello_theme->exists()) {
            return $hello_theme->get('Version');
        }
        return null;
    }

    /**
     * Handle CSS change version increment
     *
     * @since    1.0.4
     * @param    array    $css_data    CSS change information
     */
    public function handle_css_version_increment($css_data = array())
    {
        if (!$this->auto_version_settings['enabled'] || !$this->auto_version_settings['css_changes']) {
            return;
        }

        // Increment child theme version if it exists
        if (is_child_theme()) {
            $this->increment_child_theme_version($css_data);
        }

        // Log version change
        $this->log_version_change('css_update', $css_data);

        // Trigger cache clearing
        do_action('cdswerx_version_incremented', 'css_change', $css_data);
    }

    /**
     * Increment child theme version
     *
     * @since    1.0.4
     * @param    array    $context    Context information for version increment
     */
    public function increment_child_theme_version($context = array())
    {
        if (!is_child_theme()) {
            return false;
        }

        $current_version = $this->version_info['child_theme']['current'];
        $new_version = $this->calculate_next_version($current_version, $this->auto_version_settings['increment_type']);

        // Update version info
        $this->version_info['child_theme']['current'] = $new_version;
        $this->version_info['child_theme']['last_updated'] = current_time('mysql');
        
        update_option('cdswerx_version_info', $this->version_info);

        // Update child theme style.css header
        $this->update_child_theme_style_version($new_version);

        // Log the version change
        $this->log_version_change('child_theme_increment', array_merge($context, array(
            'old_version' => $current_version,
            'new_version' => $new_version,
        )));

        return $new_version;
    }

    /**
     * Calculate next version number
     *
     * @since    1.0.4
     * @param    string    $current_version    Current version string
     * @param    string    $increment_type     Type of increment (major, minor, patch)
     * @return   string    Next version number
     */
    private function calculate_next_version($current_version, $increment_type = 'patch')
    {
        // Parse current version
        $version_parts = explode('.', $current_version);
        
        // Ensure we have at least 3 parts (major.minor.patch)
        while (count($version_parts) < 3) {
            $version_parts[] = '0';
        }

        $major = (int) $version_parts[0];
        $minor = (int) $version_parts[1];
        $patch = (int) $version_parts[2];

        switch ($increment_type) {
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'patch':
            default:
                $patch++;
                break;
        }

        return "{$major}.{$minor}.{$patch}";
    }

    /**
     * Update child theme style.css version header
     *
     * @since    1.0.4
     * @param    string    $new_version    New version number
     * @return   bool      Success status
     */
    private function update_child_theme_style_version($new_version)
    {
        $child_theme_path = get_stylesheet_directory();
        $style_css_path = $child_theme_path . '/style.css';

        if (!file_exists($style_css_path) || !is_writable($style_css_path)) {
            return false;
        }

        $style_content = file_get_contents($style_css_path);
        
        // Update version in CSS header
        $pattern = '/(\*\s*Version:\s*)([0-9]+\.[0-9]+\.[0-9]+)/i';
        $replacement = '${1}' . $new_version;
        
        $new_content = preg_replace($pattern, $replacement, $style_content);
        
        if ($new_content && $new_content !== $style_content) {
            return file_put_contents($style_css_path, $new_content) !== false;
        }

        return false;
    }

    /**
     * Handle Hello Elementor update
     *
     * @since    1.0.4
     * @param    string    $old_version    Previous Hello Elementor version
     * @param    string    $new_version    New Hello Elementor version
     */
    public function handle_hello_elementor_update($old_version, $new_version)
    {
        // Update version info
        $this->version_info['hello_elementor']['current'] = $new_version;
        $this->version_info['hello_elementor']['last_updated'] = current_time('mysql');
        
        update_option('cdswerx_version_info', $this->version_info);

        // Determine increment type based on version change
        $increment_type = $this->determine_increment_type($old_version, $new_version);
        
        // Update CDSWerx component versions if auto-versioning is enabled
        if ($this->auto_version_settings['enabled']) {
            if ($increment_type === 'major' && $this->auto_version_settings['major_updates']) {
                $this->increment_child_theme_version(array('trigger' => 'hello_elementor_major_update'));
            } elseif ($increment_type === 'minor' && $this->auto_version_settings['minor_updates']) {
                $this->increment_child_theme_version(array('trigger' => 'hello_elementor_minor_update'));
            } elseif ($increment_type === 'patch' && $this->auto_version_settings['patch_updates']) {
                $this->increment_child_theme_version(array('trigger' => 'hello_elementor_patch_update'));
            }
        }

        // Log the Hello Elementor update
        $this->log_version_change('hello_elementor_update', array(
            'old_version' => $old_version,
            'new_version' => $new_version,
            'increment_type' => $increment_type,
        ));

        // Trigger cache clearing and version coordination
        do_action('cdswerx_version_incremented', 'hello_elementor_update', array(
            'old_version' => $old_version,
            'new_version' => $new_version,
        ));
    }

    /**
     * Determine increment type based on version comparison
     *
     * @since    1.0.4
     * @param    string    $old_version    Old version
     * @param    string    $new_version    New version
     * @return   string    Increment type (major, minor, patch)
     */
    private function determine_increment_type($old_version, $new_version)
    {
        $old_parts = explode('.', $old_version);
        $new_parts = explode('.', $new_version);

        // Ensure both have 3 parts
        while (count($old_parts) < 3) $old_parts[] = '0';
        while (count($new_parts) < 3) $new_parts[] = '0';

        if ((int) $old_parts[0] !== (int) $new_parts[0]) {
            return 'major';
        } elseif ((int) $old_parts[1] !== (int) $new_parts[1]) {
            return 'minor';
        } else {
            return 'patch';
        }
    }

    /**
     * Handle customizer version increment
     *
     * @since    1.0.4
     */
    public function handle_customizer_version_increment()
    {
        if ($this->auto_version_settings['enabled']) {
            $this->increment_child_theme_version(array('trigger' => 'customizer_save'));
        }
    }

    /**
     * Handle theme update completion
     *
     * @since    1.0.4
     * @param    object    $upgrader    Upgrader instance
     * @param    array     $hook_extra  Extra data for the upgrade
     */
    public function handle_theme_update($upgrader, $hook_extra)
    {
        if (!isset($hook_extra['type']) || $hook_extra['type'] !== 'theme') {
            return;
        }

        if (!isset($hook_extra['themes'])) {
            return;
        }

        foreach ($hook_extra['themes'] as $theme_slug) {
            if ($theme_slug === 'hello-elementor') {
                $new_version = $this->get_hello_elementor_version();
                $old_version = $this->version_info['hello_elementor']['current'] ?? '0.0.0';
                
                if (version_compare($new_version, $old_version, '>')) {
                    $this->handle_hello_elementor_update($old_version, $new_version);
                }
            }
        }
    }

    /**
     * Handle theme switch
     *
     * @since    1.0.4
     * @param    string    $new_theme_name    New theme name
     */
    public function handle_theme_switch($new_theme_name)
    {
        // Update version info for new theme
        $this->version_info['theme']['current'] = $this->get_theme_version();
        $this->version_info['theme']['last_updated'] = current_time('mysql');
        
        if (is_child_theme()) {
            $this->version_info['child_theme']['current'] = $this->get_child_theme_version();
            $this->version_info['child_theme']['last_updated'] = current_time('mysql');
        }

        update_option('cdswerx_version_info', $this->version_info);

        // Log theme switch
        $this->log_version_change('theme_switch', array(
            'new_theme' => $new_theme_name,
        ));
    }

    /**
     * Manage frontend asset versions
     *
     * @since    1.0.4
     */
    public function manage_frontend_versions()
    {
        if (!$this->cache_settings['enabled'] || !$this->cache_settings['version_based']) {
            return;
        }

        // Add version parameter to enqueued styles and scripts
        add_filter('style_loader_src', array($this, 'add_version_to_asset'), 10, 2);
        add_filter('script_loader_src', array($this, 'add_version_to_asset'), 10, 2);
    }

    /**
     * Manage admin asset versions
     *
     * @since    1.0.4
     */
    public function manage_admin_versions()
    {
        $this->manage_frontend_versions(); // Same logic for admin
    }

    /**
     * Add version parameter to asset URLs
     *
     * @since    1.0.4
     * @param    string    $src     Asset source URL
     * @param    string    $handle  Asset handle
     * @return   string    Modified asset URL with version parameter
     */
    public function add_version_to_asset($src, $handle)
    {
        // Only add version to CDSWerx-related assets
        if (strpos($handle, 'cdswerx') === false && strpos($handle, 'hello') === false) {
            return $src;
        }

        $version_suffix = $this->get_cache_version_suffix();
        
        if ($version_suffix) {
            $separator = (strpos($src, '?') !== false) ? '&' : '?';
            $src .= $separator . 'cdswerx_v=' . $version_suffix;
        }

        return $src;
    }

    /**
     * Get cache version suffix
     *
     * @since    1.0.4
     * @return   string    Version suffix for cache busting
     */
    private function get_cache_version_suffix()
    {
        $versions = array();
        
        if ($this->version_info['child_theme']['current']) {
            $versions[] = 'ct' . str_replace('.', '', $this->version_info['child_theme']['current']);
        }
        
        if ($this->version_info['theme']['current']) {
            $versions[] = 't' . str_replace('.', '', $this->version_info['theme']['current']);
        }
        
        $versions[] = 'p' . str_replace('.', '', CDSWERX_VERSION);

        return implode('_', $versions);
    }

    /**
     * Handle cache clearing after version increment
     *
     * @since    1.0.4
     * @param    string    $change_type    Type of change that triggered version increment
     * @param    array     $context        Context information
     */
    public function handle_cache_clearing($change_type, $context = array())
    {
        if (!$this->cache_settings['enabled'] || !$this->cache_settings['auto_clear']) {
            return;
        }

        $caches_cleared = array();

        // WordPress object cache
        if (in_array('object_cache', $this->cache_settings['supported_caches']) && function_exists('wp_cache_flush')) {
            wp_cache_flush();
            $caches_cleared[] = 'object_cache';
        }

        // OPcache
        if (in_array('opcache', $this->cache_settings['supported_caches']) && function_exists('opcache_reset')) {
            opcache_reset();
            $caches_cleared[] = 'opcache';
        }

        // WordPress page cache (if available)
        if (in_array('wp_cache', $this->cache_settings['supported_caches'])) {
            // Try common caching plugins
            do_action('wp_cache_clear_cache');
            do_action('litespeed_purge_all');
            do_action('w3tc_flush_all');
            $caches_cleared[] = 'wp_cache';
        }

        // Log cache clearing
        $this->log_version_change('cache_cleared', array(
            'trigger' => $change_type,
            'caches_cleared' => $caches_cleared,
            'context' => $context,
        ));
    }

    /**
     * Log version change
     *
     * @since    1.0.4
     * @param    string    $change_type    Type of version change
     * @param    array     $data           Additional data for the change
     */
    private function log_version_change($change_type, $data = array())
    {
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'type' => $change_type,
            'data' => $data,
            'version_info_snapshot' => $this->version_info,
        );

        $this->version_history[] = $log_entry;

        // Keep only last 50 entries
        if (count($this->version_history) > 50) {
            $this->version_history = array_slice($this->version_history, -50);
        }

        update_option('cdswerx_version_history', $this->version_history);
    }

    /**
     * Handle AJAX actions for version management
     *
     * @since    1.0.4
     * @param    string    $action    AJAX action name
     * @param    array     $data      POST data
     * @return   array     Response data
     */
    public function handle_ajax_action($action, $data)
    {
        switch ($action) {
            case 'get_version_info':
                return array(
                    'success' => true,
                    'version_info' => $this->version_info,
                    'auto_version_settings' => $this->auto_version_settings,
                    'cache_settings' => $this->cache_settings,
                );

            case 'toggle_auto_versioning':
                $this->auto_version_settings['enabled'] = !$this->auto_version_settings['enabled'];
                update_option('cdswerx_auto_version_settings', $this->auto_version_settings);
                return array(
                    'success' => true,
                    'enabled' => $this->auto_version_settings['enabled'],
                );

            case 'manual_version_increment':
                $increment_type = sanitize_text_field($data['increment_type'] ?? 'patch');
                $new_version = $this->increment_child_theme_version(array(
                    'trigger' => 'manual',
                    'increment_type' => $increment_type,
                ));
                return array(
                    'success' => $new_version !== false,
                    'new_version' => $new_version,
                );

            case 'clear_caches':
                $this->handle_cache_clearing('manual');
                return array(
                    'success' => true,
                    'message' => __('Caches cleared successfully', 'cdswerx'),
                );

            case 'get_version_history':
                $limit = (int) ($data['limit'] ?? 20);
                return array(
                    'success' => true,
                    'history' => array_slice(array_reverse($this->version_history), 0, $limit),
                );

            default:
                return array(
                    'success' => false,
                    'message' => __('Unknown action', 'cdswerx'),
                );
        }
    }

    /**
     * Get current version information
     *
     * @since    1.0.4
     * @return   array    Current version information
     */
    public function get_version_info()
    {
        return $this->version_info;
    }

    /**
     * Get version history
     *
     * @since    1.0.4
     * @param    int      $limit    Number of entries to return
     * @return   array    Version history entries
     */
    public function get_version_history($limit = 20)
    {
        return array_slice(array_reverse($this->version_history), 0, $limit);
    }

    /**
     * Update auto-versioning settings
     *
     * @since    1.0.4
     * @param    array    $settings    New settings
     * @return   bool     Success status
     */
    public function update_auto_version_settings($settings)
    {
        $this->auto_version_settings = array_merge($this->auto_version_settings, $settings);
        return update_option('cdswerx_auto_version_settings', $this->auto_version_settings);
    }

    /**
     * Update cache settings
     *
     * @since    1.0.4
     * @param    array    $settings    New cache settings
     * @return   bool     Success status
     */
    public function update_cache_settings($settings)
    {
        $this->cache_settings = array_merge($this->cache_settings, $settings);
        return update_option('cdswerx_cache_settings', $this->cache_settings);
    }

    /**
     * Increment version for a specific component
     *
     * @since    1.0.4
     * @param    string    $component       Component to increment (plugin, theme, child_theme)
     * @param    string    $increment_type  Type of increment (major, minor, patch)
     * @return   array     Result of version increment
     */
    public function increment_version($component = 'plugin', $increment_type = 'patch')
    {
        try {
            $result = ['success' => false, 'old_version' => null, 'new_version' => null];
            
            switch ($component) {
                case 'plugin':
                    $result = $this->increment_plugin_version($increment_type);
                    break;
                    
                case 'theme':
                    $result = $this->increment_theme_version($increment_type);
                    break;
                    
                case 'child_theme':
                    $result = $this->increment_child_theme_version_manual($increment_type);
                    break;
                    
                default:
                    throw new Exception("Unknown component: {$component}");
            }
            
            if ($result['success']) {
                // Record version history
                $this->record_version_change($component, $increment_type, $result);
                
                // Clear caches if enabled
                if (get_option('cdswerx_cache_bust_on_version_change', true)) {
                    $this->handle_cache_clearing('manual_version_increment', [
                        'component' => $component,
                        'increment_type' => $increment_type
                    ]);
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log('Version Manager increment error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Version increment failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Increment plugin version
     *
     * @since    1.0.4
     * @param    string    $increment_type  Type of increment (major, minor, patch)
     * @return   array     Result of version increment
     */
    protected function increment_plugin_version($increment_type)
    {
        $current_version = CDSWERX_VERSION;
        $new_version = $this->calculate_new_version($current_version, $increment_type);
        
        // Update plugin version constant (would require file modification in real implementation)
        // For now, we'll store it as an option
        update_option('cdswerx_current_plugin_version', $new_version);
        
        return [
            'success' => true,
            'old_version' => $current_version,
            'new_version' => $new_version,
            'component' => 'plugin'
        ];
    }

    /**
     * Increment theme version
     *
     * @since    1.0.4
     * @param    string    $increment_type  Type of increment (major, minor, patch)
     * @return   array     Result of version increment
     */
    protected function increment_theme_version($increment_type)
    {
        $theme = wp_get_theme();
        $current_version = $theme->get('Version');
        $new_version = $this->calculate_new_version($current_version, $increment_type);
        
        // Update theme version (would require style.css modification in real implementation)
        update_option('cdswerx_current_theme_version', $new_version);
        
        return [
            'success' => true,
            'old_version' => $current_version,
            'new_version' => $new_version,
            'component' => 'theme'
        ];
    }

    /**
     * Manual child theme version increment
     *
     * @since    1.0.4
     * @param    string    $increment_type  Type of increment (major, minor, patch)
     * @return   array     Result of version increment
     */
    protected function increment_child_theme_version_manual($increment_type)
    {
        $current_version = get_option('cdswerx_child_theme_version', '1.0.0');
        $new_version = $this->calculate_new_version($current_version, $increment_type);
        
        // Update child theme version
        update_option('cdswerx_child_theme_version', $new_version);
        
        return [
            'success' => true,
            'old_version' => $current_version,
            'new_version' => $new_version,
            'component' => 'child_theme'
        ];
    }

    /**
     * Calculate new version based on increment type
     *
     * @since    1.0.4
     * @param    string    $current_version Current version
     * @param    string    $increment_type  Type of increment
     * @return   string    New version
     */
    protected function calculate_new_version($current_version, $increment_type)
    {
        $version_parts = explode('.', $current_version);
        $major = intval($version_parts[0] ?? 1);
        $minor = intval($version_parts[1] ?? 0);
        $patch = intval($version_parts[2] ?? 0);
        
        switch ($increment_type) {
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'patch':
            default:
                $patch++;
                break;
        }
        
        return "{$major}.{$minor}.{$patch}";
    }

    /**
     * Record version change in history
     *
     * @since    1.0.4
     * @param    string    $component Component that was updated
     * @param    string    $type      Type of increment
     * @param    array     $result    Version increment result
     */
    protected function record_version_change($component, $type, $result)
    {
        $history = get_option('cdswerx_version_history', []);
        
        $entry = [
            'timestamp' => current_time('mysql'),
            'type' => 'manual_increment',
            'component' => $component,
            'increment_type' => $type,
            'data' => $result,
            'user_id' => get_current_user_id()
        ];
        
        array_unshift($history, $entry);
        
        // Keep only last 100 entries
        $history = array_slice($history, 0, 100);
        
        update_option('cdswerx_version_history', $history);
    }

    /**
     * Get all component versions
     *
     * @since    1.0.4
     * @return   array    All component versions
     */
    public function get_all_versions()
    {
        return [
            'plugin' => [
                'current' => get_option('cdswerx_current_plugin_version', CDSWERX_VERSION),
                'constant' => CDSWERX_VERSION
            ],
            'theme' => [
                'current' => get_option('cdswerx_current_theme_version', wp_get_theme()->get('Version'))
            ],
            'child_theme' => [
                'current' => get_option('cdswerx_child_theme_version', '1.0.0')
            ],
            'hello_elementor' => [
                'current' => wp_get_theme('hello-elementor')->exists() ? wp_get_theme('hello-elementor')->get('Version') : null
            ]
        ];
    }

    /**
     * Handle CSS change and increment version if needed
     *
     * @since    1.0.4
     * @return   array    Result of CSS version handling
     */
    public function handle_css_change()
    {
        if (get_option('cdswerx_auto_version_on_css_change', true)) {
            $increment_type = get_option('cdswerx_css_version_increment_type', 'patch');
            return $this->increment_version('child_theme', $increment_type);
        }
        
        return [
            'success' => true,
            'message' => 'Auto-increment disabled for CSS changes'
        ];
    }

    /**
     * Clear all caches
     *
     * @since    1.0.4
     * @return   array    Cache clearing results
     */
    public function clear_all_caches()
    {
        $results = [];
        
        // WordPress object cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
            $results[] = 'WordPress object cache';
        }
        
        // Theme/plugin caches
        if (function_exists('wp_clean_themes_cache')) {
            wp_clean_themes_cache();
            $results[] = 'Theme cache';
        }
        
        if (function_exists('wp_clean_plugins_cache')) {
            wp_clean_plugins_cache();
            $results[] = 'Plugin cache';
        }
        
        // Update cache clear timestamp
        update_option('cdswerx_last_cache_clear', current_time('mysql'));
        
        return $results;
    }

    /**
     * Get manager name
     *
     * @since    1.0.4
     * @return   string    Version manager name
     */
    public function get_manager_name()
    {
        return $this->manager_name;
    }

    /**
     * Get manager version
     *
     * @since    1.0.4
     * @return   string    Version manager version
     */
    public function get_version()
    {
        return $this->version;
    }
}