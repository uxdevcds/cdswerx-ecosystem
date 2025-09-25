<?php
/**
 * CDSWerx Sync Manager
 *
 * Theme synchronization and version coordination
 * Consolidates sync functionality from hello-elementor-sync classes
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
 * CDSWerx Sync Manager Class
 * 
 * Handles theme synchronization, version coordination, and compatibility monitoring
 * Consolidates functionality from class-hello-elementor-sync.php (1,947 lines)
 * 
 * @since 2.0.0
 */
class CDSWerx_Sync_Manager {
    
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
     * Sync configurations
     * 
     * @since 2.0.0
     * @access private
     * @var array $sync_configs
     */
    private $sync_configs = array();
    
    /**
     * Version tracking
     * 
     * @since 2.0.0
     * @access private
     * @var array $version_tracking
     */
    private $version_tracking = array();
    
    /**
     * Sync status
     * 
     * @since 2.0.0
     * @access private
     * @var array $sync_status
     */
    private $sync_status = array();
    
    /**
     * Initialize sync manager
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
        
        // Initialize sync configurations
        $this->init_sync_configs();
    }
    
    /**
     * Register sync hooks
     * 
     * @since 2.0.0
     * @param Cdswerx_Loader $loader The loader instance
     */
    public function register_hooks($loader) {
        // Version monitoring
        $loader->add_action('init', $this, 'monitor_versions');
        $loader->add_action('upgrader_process_complete', $this, 'on_theme_update', 10, 2);
        
        // Advanced CSS monitoring
        $loader->add_action('cdswerx_advanced_css_updated', $this, 'on_advanced_css_change');
        
        // Theme coordination
        $loader->add_action('switch_theme', $this, 'on_theme_switch');
        $loader->add_action('after_setup_theme', $this, 'coordinate_theme_versions');
        
        // Sync operations
        $loader->add_action('wp_ajax_cdswerx_sync_versions', $this, 'ajax_sync_versions');
        $loader->add_action('wp_ajax_cdswerx_reset_sync', $this, 'ajax_reset_sync');
        
        // Scheduled sync
        $loader->add_action('cdswerx_scheduled_sync', $this, 'perform_scheduled_sync');
    }
    
    /**
     * Initialize sync configurations
     * 
     * @since 2.0.0
     */
    private function init_sync_configs() {
        $this->sync_configs = array(
            'hello_elementor' => array(
                'enabled' => true,
                'auto_sync' => true,
                'version_tracking' => true,
                'css_coordination' => true
            ),
            'cdswerx_theme' => array(
                'enabled' => true,
                'auto_sync' => true,
                'version_tracking' => true,
                'css_coordination' => true
            ),
            'advanced_css' => array(
                'enabled' => true,
                'version_increment' => true,
                'cache_bust' => true,
                'theme_notification' => true
            )
        );
    }
    
    /**
     * Monitor theme and plugin versions
     * 
     * @since 2.0.0
     */
    public function monitor_versions() {
        // Get current versions
        $current_versions = $this->get_current_versions();
        
        // Check for version changes
        $stored_versions = get_option('cdswerx_version_tracking', array());
        
        // Compare and update if needed
        if ($current_versions !== $stored_versions) {
            $this->handle_version_changes($current_versions, $stored_versions);
            update_option('cdswerx_version_tracking', $current_versions);
        }
        
        $this->version_tracking = $current_versions;
    }
    
    /**
     * Get current component versions
     * 
     * @since 2.0.0
     * @return array Current versions
     */
    private function get_current_versions() {
        $versions = array(
            'cdswerx_plugin' => $this->version,
            'wordpress' => get_bloginfo('version')
        );
        
        // Hello Elementor theme
        $hello_theme = wp_get_theme('hello-elementor');
        if ($hello_theme->exists()) {
            $versions['hello_elementor'] = $hello_theme->get('Version');
        }
        
        // CDSWerx Theme
        $cdswerx_theme = wp_get_theme('cdswerx-theme');
        if ($cdswerx_theme->exists()) {
            $versions['cdswerx_theme'] = $cdswerx_theme->get('Version');
        }
        
        // CDSWerx Child Theme
        $cdswerx_child = wp_get_theme('cdswerx-theme-child');
        if ($cdswerx_child->exists()) {
            $versions['cdswerx_theme_child'] = $cdswerx_child->get('Version');
        }
        
        // Elementor
        if (defined('ELEMENTOR_VERSION')) {
            $versions['elementor'] = ELEMENTOR_VERSION;
        }
        
        // Elementor Pro
        if (defined('ELEMENTOR_PRO_VERSION')) {
            $versions['elementor_pro'] = ELEMENTOR_PRO_VERSION;
        }
        
        return $versions;
    }
    
    /**
     * Handle version changes
     * 
     * @since 2.0.0
     * @param array $current_versions Current versions
     * @param array $stored_versions Previously stored versions
     */
    private function handle_version_changes($current_versions, $stored_versions) {
        foreach ($current_versions as $component => $version) {
            if (!isset($stored_versions[$component]) || $stored_versions[$component] !== $version) {
                // Version changed
                do_action('cdswerx_version_changed', $component, $version, $stored_versions[$component] ?? null);
                
                // Component-specific handlers
                switch ($component) {
                    case 'hello_elementor':
                        $this->handle_hello_elementor_update($version);
                        break;
                    case 'cdswerx_theme':
                        $this->handle_cdswerx_theme_update($version);
                        break;
                    case 'elementor':
                    case 'elementor_pro':
                        $this->handle_elementor_update($component, $version);
                        break;
                }
            }
        }
    }
    
    /**
     * Handle Hello Elementor theme update
     * 
     * @since 2.0.0
     * @param string $new_version New version
     */
    private function handle_hello_elementor_update($new_version) {
        if (!$this->sync_configs['hello_elementor']['enabled']) {
            return;
        }
        
        // Update compatibility data
        $compatibility = get_option('cdswerx_theme_compatibility', array());
        if (isset($compatibility['hello_elementor'])) {
            $compatibility['hello_elementor']['version'] = $new_version;
            $compatibility['hello_elementor']['last_updated'] = current_time('timestamp');
            update_option('cdswerx_theme_compatibility', $compatibility);
        }
        
        // Trigger sync if auto-sync enabled
        if ($this->sync_configs['hello_elementor']['auto_sync']) {
            $this->sync_hello_elementor_compatibility();
        }
    }
    
    /**
     * Handle CDSWerx theme update
     * 
     * @since 2.0.0
     * @param string $new_version New version
     */
    private function handle_cdswerx_theme_update($new_version) {
        if (!$this->sync_configs['cdswerx_theme']['enabled']) {
            return;
        }
        
        // Update theme coordination
        $this->coordinate_theme_versions();
        
        // Clear theme cache
        delete_transient('cdswerx_theme_data');
    }
    
    /**
     * Handle Elementor update
     * 
     * @since 2.0.0
     * @param string $component Component name
     * @param string $new_version New version
     */
    private function handle_elementor_update($component, $new_version) {
        // Regenerate Elementor cache
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        
        // Update extension compatibility
        do_action('cdswerx_elementor_updated', $component, $new_version);
    }
    
    /**
     * Handle theme update via upgrader
     * 
     * @since 2.0.0
     * @param object $upgrader_object Upgrader object
     * @param array $options Update options
     */
    public function on_theme_update($upgrader_object, $options) {
        if ($options['type'] !== 'theme') {
            return;
        }
        
        // Force version check on next load
        delete_option('cdswerx_version_tracking');
        
        // Schedule immediate sync
        wp_schedule_single_event(time() + 60, 'cdswerx_scheduled_sync');
    }
    
    /**
     * Handle Advanced CSS changes
     * 
     * @since 2.0.0
     * @param string $css_version CSS version
     */
    public function on_advanced_css_change($css_version) {
        if (!$this->sync_configs['advanced_css']['enabled']) {
            return;
        }
        
        // Increment CSS version for cache busting
        if ($this->sync_configs['advanced_css']['version_increment']) {
            $current_css_version = get_option('cdswerx_css_version', 1);
            update_option('cdswerx_css_version', $current_css_version + 1);
        }
        
        // Notify themes if enabled
        if ($this->sync_configs['advanced_css']['theme_notification']) {
            do_action('cdswerx_advanced_css_updated', $css_version);
        }
        
        // Clear CSS cache
        if ($this->sync_configs['advanced_css']['cache_bust']) {
            $this->clear_css_cache();
        }
    }
    
    /**
     * Handle theme switch
     * 
     * @since 2.0.0
     */
    public function on_theme_switch() {
        // Reset sync status
        $this->sync_status = array();
        
        // Re-coordinate versions
        $this->coordinate_theme_versions();
        
        // Clear all caches
        $this->clear_all_caches();
    }
    
    /**
     * Coordinate theme versions
     * 
     * @since 2.0.0
     */
    public function coordinate_theme_versions() {
        $current_theme = get_template();
        
        // Theme-specific coordination
        switch ($current_theme) {
            case 'hello-elementor':
                $this->coordinate_hello_elementor();
                break;
            case 'cdswerx-theme':
                $this->coordinate_cdswerx_theme();
                break;
            default:
                $this->coordinate_generic_theme();
        }
        
        // Update coordination timestamp
        update_option('cdswerx_last_coordination', current_time('timestamp'));
    }
    
    /**
     * Coordinate Hello Elementor integration
     * 
     * @since 2.0.0
     */
    private function coordinate_hello_elementor() {
        // Sync Hello Elementor compatibility
        $this->sync_hello_elementor_compatibility();
        
        // Update sync status
        $this->sync_status['hello_elementor'] = array(
            'status' => 'synchronized',
            'last_sync' => current_time('timestamp')
        );
    }
    
    /**
     * Coordinate CDSWerx theme integration
     * 
     * @since 2.0.0
     */
    private function coordinate_cdswerx_theme() {
        // Full integration mode
        $this->sync_status['cdswerx_theme'] = array(
            'status' => 'native_integration',
            'last_sync' => current_time('timestamp')
        );
    }
    
    /**
     * Coordinate generic theme integration
     * 
     * @since 2.0.0
     */
    private function coordinate_generic_theme() {
        // Independent mode
        $this->sync_status['generic'] = array(
            'status' => 'independent',
            'last_sync' => current_time('timestamp')
        );
    }
    
    /**
     * Sync Hello Elementor compatibility
     * 
     * @since 2.0.0
     */
    private function sync_hello_elementor_compatibility() {
        // Check if Hello Elementor is active
        if (!$this->is_hello_elementor_active()) {
            return false;
        }
        
        // Sync theme functions
        $this->sync_hello_elementor_functions();
        
        // Sync styles
        $this->sync_hello_elementor_styles();
        
        return true;
    }
    
    /**
     * Check if Hello Elementor is active
     * 
     * @since 2.0.0
     * @return bool Active status
     */
    private function is_hello_elementor_active() {
        return get_template() === 'hello-elementor';
    }
    
    /**
     * Sync Hello Elementor functions
     * 
     * @since 2.0.0
     */
    private function sync_hello_elementor_functions() {
        // Implementation for function synchronization
        do_action('cdswerx_sync_hello_elementor_functions');
    }
    
    /**
     * Sync Hello Elementor styles
     * 
     * @since 2.0.0
     */
    private function sync_hello_elementor_styles() {
        // Implementation for style synchronization
        do_action('cdswerx_sync_hello_elementor_styles');
    }
    
    /**
     * Clear CSS cache
     * 
     * @since 2.0.0
     */
    private function clear_css_cache() {
        // Clear various CSS caches
        delete_transient('cdswerx_css_cache');
        
        // Clear Elementor cache if available
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
        
        // Clear other caching plugins
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
    }
    
    /**
     * Clear all caches
     * 
     * @since 2.0.0
     */
    private function clear_all_caches() {
        $this->clear_css_cache();
        delete_transient('cdswerx_theme_data');
        delete_transient('cdswerx_compatibility_data');
    }
    
    /**
     * AJAX sync versions
     * 
     * @since 2.0.0
     */
    public function ajax_sync_versions() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_sync_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        // Perform sync
        $this->coordinate_theme_versions();
        
        wp_send_json_success(__('Versions synchronized successfully', 'cdswerx'));
    }
    
    /**
     * AJAX reset sync
     * 
     * @since 2.0.0
     */
    public function ajax_reset_sync() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_sync_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        // Reset sync data
        delete_option('cdswerx_version_tracking');
        delete_option('cdswerx_theme_compatibility');
        $this->sync_status = array();
        
        wp_send_json_success(__('Sync data reset successfully', 'cdswerx'));
    }
    
    /**
     * Perform scheduled sync
     * 
     * @since 2.0.0
     */
    public function perform_scheduled_sync() {
        $this->monitor_versions();
        $this->coordinate_theme_versions();
    }
    
    /**
     * Get sync status
     * 
     * @since 2.0.0
     * @return array Sync status
     */
    public function get_sync_status() {
        return $this->sync_status;
    }
    
    /**
     * Get version tracking data
     * 
     * @since 2.0.0
     * @return array Version tracking data
     */
    public function get_version_tracking() {
        return $this->version_tracking;
    }
    
    /**
     * Get sync configurations
     * 
     * @since 2.0.0
     * @return array Sync configurations
     */
    public function get_sync_configs() {
        return $this->sync_configs;
    }
    
    /**
     * Update sync configuration
     * 
     * @since 2.0.0
     * @param string $component Component name
     * @param array $config Configuration array
     */
    public function update_sync_config($component, $config) {
        if (isset($this->sync_configs[$component])) {
            $this->sync_configs[$component] = array_merge(
                $this->sync_configs[$component],
                $config
            );
        }
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
}