<?php
/**
 * CDSWerx System Manager
 *
 * System utilities, performance optimization, and maintenance
 * Consolidates system functionality from multiple utility classes
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
 * CDSWerx System Manager Class
 * 
 * Handles system utilities, performance optimization, and maintenance tasks
 * Consolidates functionality from utility and system classes
 * 
 * @since 2.0.0
 */
class CDSWerx_System_Manager {
    
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
     * System settings
     * 
     * @since 2.0.0
     * @access private
     * @var array $system_settings
     */
    private $system_settings = array();
    
    /**
     * Performance metrics
     * 
     * @since 2.0.0
     * @access private
     * @var array $performance_metrics
     */
    private $performance_metrics = array();
    
    /**
     * System status
     * 
     * @since 2.0.0
     * @access private
     * @var array $system_status
     */
    private $system_status = array();
    
    /**
     * Initialize system manager
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
        
        // Initialize settings
        $this->init_system_settings();
    }
    
    /**
     * Register system hooks
     * 
     * @since 2.0.0
     * @param Cdswerx_Loader $loader The loader instance
     */
    public function register_hooks($loader) {
        // System initialization
        $loader->add_action('init', $this, 'system_init');
        $loader->add_action('admin_init', $this, 'admin_system_init');
        
        // Performance optimization
        $loader->add_action('wp_enqueue_scripts', $this, 'optimize_assets');
        $loader->add_action('wp_head', $this, 'inject_performance_hints', 1);
        
        // Maintenance tasks
        $loader->add_action('wp_loaded', $this, 'run_maintenance_checks');
        $loader->add_action('cdswerx_daily_maintenance', $this, 'daily_maintenance');
        
        // System monitoring
        $loader->add_action('shutdown', $this, 'collect_performance_metrics');
        
        // AJAX handlers
        $loader->add_action('wp_ajax_cdswerx_system_info', $this, 'ajax_system_info');
        $loader->add_action('wp_ajax_cdswerx_clear_cache', $this, 'ajax_clear_cache');
        $loader->add_action('wp_ajax_cdswerx_run_diagnostics', $this, 'ajax_run_diagnostics');
        
        // Cleanup hooks
        $loader->add_action('deactivate_' . plugin_basename(dirname(dirname(dirname(__FILE__))) . '/cdswerx.php'), $this, 'on_deactivation');
    }
    
    /**
     * Initialize system settings
     * 
     * @since 2.0.0
     */
    private function init_system_settings() {
        $this->system_settings = array(
            'performance' => array(
                'cache_enabled' => true,
                'minify_assets' => false,
                'lazy_load_images' => false,
                'preload_critical' => true
            ),
            'maintenance' => array(
                'auto_cleanup' => true,
                'log_retention_days' => 30,
                'backup_settings' => true,
                'update_checks' => true
            ),
            'monitoring' => array(
                'collect_metrics' => true,
                'error_reporting' => true,
                'performance_tracking' => true,
                'debug_mode' => false
            ),
            'compatibility' => array(
                'php_version_check' => true,
                'wp_version_check' => true,
                'plugin_compatibility' => true,
                'theme_compatibility' => true
            )
        );
    }
    
    /**
     * System initialization
     * 
     * @since 2.0.0
     */
    public function system_init() {
        // Load system settings
        $this->load_system_settings();
        
        // Check system requirements
        $this->check_system_requirements();
        
        // Initialize performance monitoring
        if ($this->system_settings['monitoring']['performance_tracking']) {
            $this->init_performance_tracking();
        }
        
        // Schedule maintenance if not already scheduled
        if (!wp_next_scheduled('cdswerx_daily_maintenance')) {
            wp_schedule_event(time(), 'daily', 'cdswerx_daily_maintenance');
        }
    }
    
    /**
     * Admin system initialization
     * 
     * @since 2.0.0
     */
    public function admin_system_init() {
        // Admin-specific system checks
        $this->check_admin_requirements();
        
        // Update system status for admin display
        $this->update_system_status();
    }
    
    /**
     * Load system settings from database
     * 
     * @since 2.0.0
     */
    private function load_system_settings() {
        $stored_settings = get_option('cdswerx_system_settings', array());
        $this->system_settings = wp_parse_args($stored_settings, $this->system_settings);
    }
    
    /**
     * Check system requirements
     * 
     * @since 2.0.0
     */
    private function check_system_requirements() {
        $requirements = array();
        
        // PHP version check
        if ($this->system_settings['compatibility']['php_version_check']) {
            $requirements['php_version'] = version_compare(PHP_VERSION, '7.4', '>=');
        }
        
        // WordPress version check
        if ($this->system_settings['compatibility']['wp_version_check']) {
            $requirements['wp_version'] = version_compare(get_bloginfo('version'), '5.0', '>=');
        }
        
        // Memory limit check
        $requirements['memory_limit'] = $this->check_memory_limit();
        
        // Write permissions check
        $requirements['write_permissions'] = $this->check_write_permissions();
        
        // Store requirements check results
        update_option('cdswerx_system_requirements', $requirements);
        
        // Trigger action for failed requirements
        $failed_requirements = array_filter($requirements, function($status) {
            return !$status;
        });
        
        if (!empty($failed_requirements)) {
            do_action('cdswerx_system_requirements_failed', $failed_requirements);
        }
    }
    
    /**
     * Check admin requirements
     * 
     * @since 2.0.0
     */
    private function check_admin_requirements() {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check for plugin conflicts
        if ($this->system_settings['compatibility']['plugin_compatibility']) {
            $this->check_plugin_conflicts();
        }
        
        // Check theme compatibility
        if ($this->system_settings['compatibility']['theme_compatibility']) {
            $this->check_theme_compatibility();
        }
    }
    
    /**
     * Check memory limit
     * 
     * @since 2.0.0
     * @return bool Memory limit adequate
     */
    private function check_memory_limit() {
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $required_memory = 128 * 1024 * 1024; // 128MB
        
        return $memory_limit >= $required_memory;
    }
    
    /**
     * Check write permissions
     * 
     * @since 2.0.0
     * @return bool Write permissions available
     */
    private function check_write_permissions() {
        $upload_dir = wp_upload_dir();
        return wp_is_writable($upload_dir['basedir']);
    }
    
    /**
     * Check for plugin conflicts
     * 
     * @since 2.0.0
     */
    private function check_plugin_conflicts() {
        $active_plugins = get_option('active_plugins');
        $known_conflicts = array(
            // Add known conflicting plugins
        );
        
        $conflicts = array_intersect($active_plugins, $known_conflicts);
        
        if (!empty($conflicts)) {
            update_option('cdswerx_plugin_conflicts', $conflicts);
            do_action('cdswerx_plugin_conflicts_detected', $conflicts);
        }
    }
    
    /**
     * Check theme compatibility
     * 
     * @since 2.0.0
     */
    private function check_theme_compatibility() {
        $current_theme = get_template();
        $compatibility_data = get_option('cdswerx_theme_compatibility', array());
        
        // Update compatibility status
        update_option('cdswerx_theme_compatibility_checked', time());
    }
    
    /**
     * Initialize performance tracking
     * 
     * @since 2.0.0
     */
    private function init_performance_tracking() {
        $this->performance_metrics['start_time'] = microtime(true);
        $this->performance_metrics['memory_start'] = memory_get_usage();
    }
    
    /**
     * Optimize assets
     * 
     * @since 2.0.0
     */
    public function optimize_assets() {
        if (!$this->system_settings['performance']['cache_enabled']) {
            return;
        }
        
        // Asset optimization logic
        if ($this->system_settings['performance']['minify_assets']) {
            $this->enable_asset_minification();
        }
        
        if ($this->system_settings['performance']['lazy_load_images']) {
            $this->enable_lazy_loading();
        }
    }
    
    /**
     * Inject performance hints
     * 
     * @since 2.0.0
     */
    public function inject_performance_hints() {
        if (!$this->system_settings['performance']['preload_critical']) {
            return;
        }
        
        // DNS prefetch for external resources
        echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
        
        // Preconnect for critical resources
        echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . "\n";
    }
    
    /**
     * Enable asset minification
     * 
     * @since 2.0.0
     */
    private function enable_asset_minification() {
        // Basic asset minification logic
        add_filter('style_loader_tag', array($this, 'optimize_css_delivery'), 10, 2);
        add_filter('script_loader_tag', array($this, 'optimize_js_delivery'), 10, 2);
    }
    
    /**
     * Enable lazy loading
     * 
     * @since 2.0.0
     */
    private function enable_lazy_loading() {
        // Enable WordPress native lazy loading
        add_filter('wp_lazy_loading_enabled', '__return_true');
    }
    
    /**
     * Optimize CSS delivery
     * 
     * @since 2.0.0
     * @param string $tag CSS tag
     * @param string $handle CSS handle
     * @return string Optimized CSS tag
     */
    public function optimize_css_delivery($tag, $handle) {
        // Add async loading for non-critical CSS
        if (strpos($handle, $this->plugin_name) !== false && strpos($handle, 'critical') === false) {
            $tag = str_replace('rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Optimize JS delivery
     * 
     * @since 2.0.0
     * @param string $tag JS tag
     * @param string $handle JS handle
     * @return string Optimized JS tag
     */
    public function optimize_js_delivery($tag, $handle) {
        // Add defer attribute for non-critical scripts
        if (strpos($handle, $this->plugin_name) !== false && !is_admin()) {
            $tag = str_replace('<script ', '<script defer ', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Run maintenance checks
     * 
     * @since 2.0.0
     */
    public function run_maintenance_checks() {
        if (!$this->system_settings['maintenance']['auto_cleanup']) {
            return;
        }
        
        // Check if maintenance is needed
        $last_maintenance = get_option('cdswerx_last_maintenance', 0);
        $maintenance_interval = 24 * HOUR_IN_SECONDS; // Daily
        
        if (time() - $last_maintenance > $maintenance_interval) {
            $this->run_quick_maintenance();
        }
    }
    
    /**
     * Run quick maintenance
     * 
     * @since 2.0.0
     */
    private function run_quick_maintenance() {
        // Clean up temporary files
        $this->cleanup_temp_files();
        
        // Clear expired transients
        $this->clear_expired_transients();
        
        // Update last maintenance timestamp
        update_option('cdswerx_last_maintenance', time());
    }
    
    /**
     * Daily maintenance tasks
     * 
     * @since 2.0.0
     */
    public function daily_maintenance() {
        // Comprehensive daily maintenance
        $this->cleanup_temp_files();
        $this->clear_expired_transients();
        $this->rotate_logs();
        $this->backup_settings();
        $this->check_for_updates();
        
        // Update maintenance log
        $this->log_maintenance_completion();
    }
    
    /**
     * Cleanup temporary files
     * 
     * @since 2.0.0
     */
    private function cleanup_temp_files() {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/cdswerx-temp/';
        
        if (is_dir($temp_dir)) {
            $files = glob($temp_dir . '*');
            $cutoff_time = time() - (24 * HOUR_IN_SECONDS);
            
            foreach ($files as $file) {
                if (filemtime($file) < $cutoff_time) {
                    unlink($file);
                }
            }
        }
    }
    
    /**
     * Clear expired transients
     * 
     * @since 2.0.0
     */
    private function clear_expired_transients() {
        // Clear CDSWerx-specific expired transients
        $transients = array(
            'cdswerx_theme_data',
            'cdswerx_compatibility_data',
            'cdswerx_system_status'
        );
        
        foreach ($transients as $transient) {
            if (get_transient($transient) === false) {
                delete_transient($transient);
            }
        }
    }
    
    /**
     * Rotate logs
     * 
     * @since 2.0.0
     */
    private function rotate_logs() {
        if (!$this->system_settings['maintenance']['log_retention_days']) {
            return;
        }
        
        $log_dir = wp_upload_dir()['basedir'] . '/cdswerx-logs/';
        if (!is_dir($log_dir)) {
            return;
        }
        
        $retention_days = $this->system_settings['maintenance']['log_retention_days'];
        $cutoff_time = time() - ($retention_days * DAY_IN_SECONDS);
        
        $log_files = glob($log_dir . '*.log');
        foreach ($log_files as $log_file) {
            if (filemtime($log_file) < $cutoff_time) {
                unlink($log_file);
            }
        }
    }
    
    /**
     * Backup settings
     * 
     * @since 2.0.0
     */
    private function backup_settings() {
        if (!$this->system_settings['maintenance']['backup_settings']) {
            return;
        }
        
        $settings_backup = array(
            'cdswerx_settings' => get_option('cdswerx_settings'),
            'cdswerx_system_settings' => get_option('cdswerx_system_settings'),
            'cdswerx_typography_settings' => get_option('cdswerx_typography_settings'),
            'backup_timestamp' => time()
        );
        
        update_option('cdswerx_settings_backup', $settings_backup);
    }
    
    /**
     * Check for updates
     * 
     * @since 2.0.0
     */
    private function check_for_updates() {
        if (!$this->system_settings['maintenance']['update_checks']) {
            return;
        }
        
        // Check for plugin updates
        wp_update_plugins();
        
        // Check for theme updates
        wp_update_themes();
        
        // Log update check
        update_option('cdswerx_last_update_check', time());
    }
    
    /**
     * Log maintenance completion
     * 
     * @since 2.0.0
     */
    private function log_maintenance_completion() {
        $maintenance_log = get_option('cdswerx_maintenance_log', array());
        
        $maintenance_log[] = array(
            'timestamp' => time(),
            'tasks_completed' => array(
                'cleanup_temp_files',
                'clear_expired_transients',
                'rotate_logs',
                'backup_settings',
                'check_for_updates'
            )
        );
        
        // Keep only last 30 maintenance records
        $maintenance_log = array_slice($maintenance_log, -30);
        
        update_option('cdswerx_maintenance_log', $maintenance_log);
    }
    
    /**
     * Collect performance metrics
     * 
     * @since 2.0.0
     */
    public function collect_performance_metrics() {
        if (!$this->system_settings['monitoring']['collect_metrics']) {
            return;
        }
        
        $this->performance_metrics['end_time'] = microtime(true);
        $this->performance_metrics['memory_end'] = memory_get_usage();
        $this->performance_metrics['memory_peak'] = memory_get_peak_usage();
        
        // Calculate metrics
        $this->performance_metrics['execution_time'] = $this->performance_metrics['end_time'] - $this->performance_metrics['start_time'];
        $this->performance_metrics['memory_used'] = $this->performance_metrics['memory_end'] - $this->performance_metrics['memory_start'];
        
        // Store metrics for analysis
        $this->store_performance_metrics();
    }
    
    /**
     * Store performance metrics
     * 
     * @since 2.0.0
     */
    private function store_performance_metrics() {
        $metrics_history = get_option('cdswerx_performance_metrics', array());
        
        $metrics_history[] = array(
            'timestamp' => time(),
            'execution_time' => $this->performance_metrics['execution_time'],
            'memory_used' => $this->performance_metrics['memory_used'],
            'memory_peak' => $this->performance_metrics['memory_peak']
        );
        
        // Keep only last 100 records
        $metrics_history = array_slice($metrics_history, -100);
        
        update_option('cdswerx_performance_metrics', $metrics_history);
    }
    
    /**
     * Update system status
     * 
     * @since 2.0.0
     */
    private function update_system_status() {
        $this->system_status = array(
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'plugin_version' => $this->version,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'last_updated' => time()
        );
        
        set_transient('cdswerx_system_status', $this->system_status, HOUR_IN_SECONDS);
    }
    
    /**
     * AJAX system info
     * 
     * @since 2.0.0
     */
    public function ajax_system_info() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_system_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        $system_info = $this->get_system_info();
        wp_send_json_success($system_info);
    }
    
    /**
     * AJAX clear cache
     * 
     * @since 2.0.0
     */
    public function ajax_clear_cache() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_system_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        $this->clear_all_caches();
        wp_send_json_success(__('All caches cleared successfully', 'cdswerx'));
    }
    
    /**
     * AJAX run diagnostics
     * 
     * @since 2.0.0
     */
    public function ajax_run_diagnostics() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_system_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        $diagnostics = $this->run_system_diagnostics();
        wp_send_json_success($diagnostics);
    }
    
    /**
     * Get system information
     * 
     * @since 2.0.0
     * @return array System information
     */
    public function get_system_info() {
        return array(
            'status' => $this->system_status,
            'requirements' => get_option('cdswerx_system_requirements', array()),
            'performance' => get_option('cdswerx_performance_metrics', array()),
            'maintenance' => get_option('cdswerx_maintenance_log', array())
        );
    }
    
    /**
     * Clear all caches
     * 
     * @since 2.0.0
     */
    public function clear_all_caches() {
        // Clear WordPress caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Clear CDSWerx transients
        $transients = array(
            'cdswerx_theme_data',
            'cdswerx_compatibility_data',
            'cdswerx_system_status',
            'cdswerx_css_cache'
        );
        
        foreach ($transients as $transient) {
            delete_transient($transient);
        }
        
        // Clear Elementor cache if available
        if (class_exists('\Elementor\Plugin')) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }
    }
    
    /**
     * Run system diagnostics
     * 
     * @since 2.0.0
     * @return array Diagnostic results
     */
    private function run_system_diagnostics() {
        return array(
            'system_requirements' => $this->check_system_requirements(),
            'plugin_conflicts' => get_option('cdswerx_plugin_conflicts', array()),
            'theme_compatibility' => get_option('cdswerx_theme_compatibility', array()),
            'performance_metrics' => $this->performance_metrics,
            'error_log' => $this->get_recent_errors()
        );
    }
    
    /**
     * Get recent errors
     * 
     * @since 2.0.0
     * @return array Recent errors
     */
    private function get_recent_errors() {
        if (!$this->system_settings['monitoring']['error_reporting']) {
            return array();
        }
        
        // Get recent PHP errors related to CDSWerx
        $error_log = ini_get('error_log');
        if (!file_exists($error_log)) {
            return array();
        }
        
        $recent_errors = array();
        $log_lines = file($error_log);
        $cutoff_time = time() - HOUR_IN_SECONDS;
        
        foreach (array_reverse($log_lines) as $line) {
            if (strpos($line, 'cdswerx') !== false || strpos($line, 'CDSWerx') !== false) {
                $recent_errors[] = trim($line);
                if (count($recent_errors) >= 10) {
                    break;
                }
            }
        }
        
        return $recent_errors;
    }
    
    /**
     * Handle plugin deactivation
     * 
     * @since 2.0.0
     */
    public function on_deactivation() {
        // Cleanup scheduled events
        wp_clear_scheduled_hook('cdswerx_daily_maintenance');
        
        // Optional: Clear caches
        $this->clear_all_caches();
        
        // Log deactivation
        update_option('cdswerx_last_deactivation', time());
    }
    
    /**
     * Get system settings
     * 
     * @since 2.0.0
     * @return array System settings
     */
    public function get_system_settings() {
        return $this->system_settings;
    }
    
    /**
     * Update system settings
     * 
     * @since 2.0.0
     * @param array $settings System settings
     */
    public function update_system_settings($settings) {
        $this->system_settings = array_merge($this->system_settings, $settings);
        update_option('cdswerx_system_settings', $this->system_settings);
    }
    
    /**
     * Get performance metrics
     * 
     * @since 2.0.0
     * @return array Performance metrics
     */
    public function get_performance_metrics() {
        return $this->performance_metrics;
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