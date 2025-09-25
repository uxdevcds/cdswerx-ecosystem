<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CDSWerx Operations Monitoring Class
 *
 * Handles system monitoring, health checks, error tracking, and performance metrics
 * for the CDSWerx ecosystem.
 *
 * @since 1.2.0
 * @package CDSWerx
 */
class CDSWerx_Operations_Monitoring {

    /**
     * The plugin name
     *
     * @since 1.2.0
     * @var string
     */
    private $plugin_name;

    /**
     * The version of this plugin
     *
     * @since 1.2.0
     * @var string
     */
    private $version;

    /**
     * System health status
     *
     * @since 1.2.0
     * @var array
     */
    private $health_status;

    /**
     * Performance metrics
     *
     * @since 1.2.0
     * @var array
     */
    private $performance_metrics;

    /**
     * Error tracking data
     *
     * @since 1.2.0
     * @var array
     */
    private $error_tracking;

    /**
     * Initialize the class and set its properties
     *
     * @since 1.2.0
     * @param string $plugin_name The name of this plugin
     * @param string $version The version of this plugin
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        $this->init_health_status();
        $this->init_performance_metrics();
        $this->init_error_tracking();
    }

    /**
     * Initialize health status monitoring
     *
     * @since 1.2.0
     */
    private function init_health_status() {
        $this->health_status = array(
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'plugin_version' => $this->version,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'last_checked' => time()
        );
    }

    /**
     * Initialize performance metrics tracking
     *
     * @since 1.2.0
     */
    private function init_performance_metrics() {
        $this->performance_metrics = array(
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(),
            'database_queries' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0
        );
    }

    /**
     * Initialize error tracking
     *
     * @since 1.2.0
     */
    private function init_error_tracking() {
        $this->error_tracking = array(
            'fatal_errors' => 0,
            'warnings' => 0,
            'notices' => 0,
            'last_error' => null,
            'error_log' => array()
        );
    }

    /**
     * Register hooks for monitoring
     *
     * @since 1.2.0
     */
    public function register_hooks() {
        // WordPress error monitoring
        add_action('wp_die_handler', array($this, 'track_wp_error'), 10, 1);
        
        // Performance monitoring
        add_action('init', array($this, 'start_performance_tracking'));
        add_action('wp_footer', array($this, 'end_performance_tracking'));
        add_action('admin_footer', array($this, 'end_performance_tracking'));
        
        // Health monitoring
        add_action('wp_loaded', array($this, 'perform_health_check'));
        
        // AJAX handlers for admin dashboard
        add_action('wp_ajax_cdswerx_get_system_status', array($this, 'ajax_get_system_status'));
        add_action('wp_ajax_cdswerx_get_performance_data', array($this, 'ajax_get_performance_data'));
        add_action('wp_ajax_cdswerx_get_error_log', array($this, 'ajax_get_error_log'));
        
        // Scheduled maintenance
        add_action('cdswerx_daily_maintenance', array($this, 'daily_maintenance'));
        
        // Schedule daily maintenance if not already scheduled
        if (!wp_next_scheduled('cdswerx_daily_maintenance')) {
            wp_schedule_event(time(), 'daily', 'cdswerx_daily_maintenance');
        }
    }

    /**
     * Start performance tracking
     *
     * @since 1.2.0
     */
    public function start_performance_tracking() {
        $this->performance_metrics['start_time'] = microtime(true);
        $this->performance_metrics['start_memory'] = memory_get_usage();
    }

    /**
     * End performance tracking and store metrics
     *
     * @since 1.2.0
     */
    public function end_performance_tracking() {
        $this->performance_metrics['end_time'] = microtime(true);
        $this->performance_metrics['end_memory'] = memory_get_usage();
        $this->performance_metrics['peak_memory'] = memory_get_peak_usage();
        
        // Calculate execution time and memory usage
        $this->performance_metrics['execution_time'] = $this->performance_metrics['end_time'] - $this->performance_metrics['start_time'];
        $this->performance_metrics['memory_used'] = $this->performance_metrics['end_memory'] - $this->performance_metrics['start_memory'];
        
        // Store metrics
        $this->store_performance_metrics();
    }

    /**
     * Store performance metrics in database
     *
     * @since 1.2.0
     */
    private function store_performance_metrics() {
        $metrics_history = get_option('cdswerx_performance_metrics', array());
        
        $current_metrics = array(
            'timestamp' => time(),
            'execution_time' => $this->performance_metrics['execution_time'],
            'memory_used' => $this->performance_metrics['memory_used'],
            'peak_memory' => $this->performance_metrics['peak_memory'],
            'database_queries' => get_num_queries()
        );
        
        $metrics_history[] = $current_metrics;
        
        // Keep only last 100 records
        $metrics_history = array_slice($metrics_history, -100);
        
        update_option('cdswerx_performance_metrics', $metrics_history);
    }

    /**
     * Perform system health check
     *
     * @since 1.2.0
     */
    public function perform_health_check() {
        // Update health status
        $this->health_status['current_memory'] = memory_get_usage();
        $this->health_status['peak_memory'] = memory_get_peak_usage();
        $this->health_status['disk_free_space'] = disk_free_space(ABSPATH);
        $this->health_status['last_checked'] = time();
        
        // Check critical thresholds
        $this->check_memory_usage();
        $this->check_disk_space();
        
        // Store health status
        update_option('cdswerx_system_health', $this->health_status);
    }

    /**
     * Check memory usage and alert if necessary
     *
     * @since 1.2.0
     */
    private function check_memory_usage() {
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $current_usage = memory_get_usage();
        $usage_percentage = ($current_usage / $memory_limit) * 100;
        
        if ($usage_percentage > 80) {
            $this->log_system_alert('High memory usage detected: ' . round($usage_percentage, 2) . '%');
        }
    }

    /**
     * Check disk space and alert if necessary
     *
     * @since 1.2.0
     */
    private function check_disk_space() {
        $free_space = disk_free_space(ABSPATH);
        $total_space = disk_total_space(ABSPATH);
        
        if ($free_space && $total_space) {
            $usage_percentage = (($total_space - $free_space) / $total_space) * 100;
            
            if ($usage_percentage > 90) {
                $this->log_system_alert('Low disk space detected: ' . round($usage_percentage, 2) . '% used');
            }
        }
    }

    /**
     * Track WordPress errors - FIXED LINE 374 ERROR
     *
     * @since 1.2.0
     * @param mixed $error Error data (could be WP_Error object or string)
     */
    public function track_wp_error($error) {
        $error_message = '';
        $error_code = '';
        
        // FIX FOR LINE 374: Proper type checking before calling methods
        if (is_wp_error($error)) {
            $error_message = $error->get_error_message();
            $error_code = $error->get_error_code();
        } elseif (is_string($error)) {
            $error_message = $error;
            $error_code = 'unknown';
        } elseif (is_array($error) && isset($error['message'])) {
            $error_message = $error['message'];
            $error_code = isset($error['code']) ? $error['code'] : 'unknown';
        } else {
            $error_message = 'Unknown error occurred';
            $error_code = 'unknown';
        }
        
        // Log the error
        $this->log_error($error_message, $error_code);
        
        return $error; // Return original error for WordPress to handle normally
    }

    /**
     * Log system error
     *
     * @since 1.2.0
     * @param string $message Error message
     * @param string $code Error code
     */
    private function log_error($message, $code = 'general') {
        $error_entry = array(
            'timestamp' => time(),
            'message' => sanitize_text_field($message),
            'code' => sanitize_text_field($code),
            'user_id' => get_current_user_id(),
            'url' => isset($_SERVER['REQUEST_URI']) ? esc_url_raw($_SERVER['REQUEST_URI']) : '',
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : ''
        );
        
        // Add to error tracking
        $this->error_tracking['error_log'][] = $error_entry;
        $this->error_tracking['last_error'] = $error_entry;
        
        // Update counters
        if (strpos($code, 'fatal') !== false) {
            $this->error_tracking['fatal_errors']++;
        } elseif (strpos($code, 'warning') !== false) {
            $this->error_tracking['warnings']++;
        } else {
            $this->error_tracking['notices']++;
        }
        
        // Store in database
        $error_log = get_option('cdswerx_error_log', array());
        $error_log[] = $error_entry;
        
        // Keep only last 500 errors
        $error_log = array_slice($error_log, -500);
        
        update_option('cdswerx_error_log', $error_log);
        update_option('cdswerx_error_tracking', $this->error_tracking);
    }

    /**
     * Log system alert
     *
     * @since 1.2.0
     * @param string $message Alert message
     */
    private function log_system_alert($message) {
        $alert_entry = array(
            'timestamp' => time(),
            'message' => sanitize_text_field($message),
            'type' => 'system_alert',
            'severity' => 'warning'
        );
        
        $alerts = get_option('cdswerx_system_alerts', array());
        $alerts[] = $alert_entry;
        
        // Keep only last 100 alerts
        $alerts = array_slice($alerts, -100);
        
        update_option('cdswerx_system_alerts', $alerts);
        
        // Log to WordPress error log as well
        error_log('CDSWerx System Alert: ' . $message);
    }

    /**
     * AJAX handler for system status
     *
     * @since 1.2.0
     */
    public function ajax_get_system_status() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'cdswerx'));
        }
        
        $health_status = get_option('cdswerx_system_health', $this->health_status);
        
        wp_send_json_success($health_status);
    }

    /**
     * AJAX handler for performance data
     *
     * @since 1.2.0
     */
    public function ajax_get_performance_data() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'cdswerx'));
        }
        
        $performance_data = get_option('cdswerx_performance_metrics', array());
        
        // Get last 24 hours of data
        $cutoff_time = time() - (24 * 60 * 60);
        $recent_data = array_filter($performance_data, function($entry) use ($cutoff_time) {
            return isset($entry['timestamp']) && $entry['timestamp'] > $cutoff_time;
        });
        
        wp_send_json_success($recent_data);
    }

    /**
     * AJAX handler for error log
     *
     * @since 1.2.0
     */
    public function ajax_get_error_log() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_admin_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'cdswerx'));
        }
        
        $error_log = get_option('cdswerx_error_log', array());
        $error_tracking = get_option('cdswerx_error_tracking', $this->error_tracking);
        
        $response = array(
            'recent_errors' => array_slice($error_log, -50), // Last 50 errors
            'error_summary' => $error_tracking
        );
        
        wp_send_json_success($response);
    }

    /**
     * Daily maintenance routine
     *
     * @since 1.2.0
     */
    public function daily_maintenance() {
        // Clean up old performance metrics (keep 30 days)
        $this->cleanup_old_metrics();
        
        // Clean up old error logs (keep 30 days)
        $this->cleanup_old_errors();
        
        // Clean up old system alerts (keep 7 days)
        $this->cleanup_old_alerts();
        
        // Perform health check
        $this->perform_health_check();
        
        // Log maintenance completion
        error_log('CDSWerx: Daily maintenance completed');
    }

    /**
     * Clean up old performance metrics
     *
     * @since 1.2.0
     */
    private function cleanup_old_metrics() {
        $metrics = get_option('cdswerx_performance_metrics', array());
        $cutoff_time = time() - (30 * 24 * 60 * 60); // 30 days
        
        $filtered_metrics = array_filter($metrics, function($entry) use ($cutoff_time) {
            return isset($entry['timestamp']) && $entry['timestamp'] > $cutoff_time;
        });
        
        update_option('cdswerx_performance_metrics', array_values($filtered_metrics));
    }

    /**
     * Clean up old error logs
     *
     * @since 1.2.0
     */
    private function cleanup_old_errors() {
        $errors = get_option('cdswerx_error_log', array());
        $cutoff_time = time() - (30 * 24 * 60 * 60); // 30 days
        
        $filtered_errors = array_filter($errors, function($entry) use ($cutoff_time) {
            return isset($entry['timestamp']) && $entry['timestamp'] > $cutoff_time;
        });
        
        update_option('cdswerx_error_log', array_values($filtered_errors));
    }

    /**
     * Clean up old system alerts
     *
     * @since 1.2.0
     */
    private function cleanup_old_alerts() {
        $alerts = get_option('cdswerx_system_alerts', array());
        $cutoff_time = time() - (7 * 24 * 60 * 60); // 7 days
        
        $filtered_alerts = array_filter($alerts, function($entry) use ($cutoff_time) {
            return isset($entry['timestamp']) && $entry['timestamp'] > $cutoff_time;
        });
        
        update_option('cdswerx_system_alerts', array_values($filtered_alerts));
    }

    /**
     * Get monitoring dashboard data
     *
     * @since 1.2.0
     * @return array Dashboard data
     */
    public function get_dashboard_data() {
        return array(
            'health_status' => get_option('cdswerx_system_health', $this->health_status),
            'performance_summary' => $this->get_performance_summary(),
            'error_summary' => get_option('cdswerx_error_tracking', $this->error_tracking),
            'recent_alerts' => array_slice(get_option('cdswerx_system_alerts', array()), -10)
        );
    }

    /**
     * Get performance summary
     *
     * @since 1.2.0
     * @return array Performance summary
     */
    private function get_performance_summary() {
        $metrics = get_option('cdswerx_performance_metrics', array());
        $recent_metrics = array_slice($metrics, -10); // Last 10 entries
        
        if (empty($recent_metrics)) {
            return array(
                'avg_execution_time' => 0,
                'avg_memory_usage' => 0,
                'peak_memory' => 0,
                'total_requests' => 0
            );
        }
        
        $total_execution_time = 0;
        $total_memory_usage = 0;
        $peak_memory = 0;
        
        foreach ($recent_metrics as $metric) {
            $total_execution_time += isset($metric['execution_time']) ? $metric['execution_time'] : 0;
            $total_memory_usage += isset($metric['memory_used']) ? $metric['memory_used'] : 0;
            $peak_memory = max($peak_memory, isset($metric['peak_memory']) ? $metric['peak_memory'] : 0);
        }
        
        $count = count($recent_metrics);
        
        return array(
            'avg_execution_time' => round($total_execution_time / $count, 4),
            'avg_memory_usage' => round($total_memory_usage / $count),
            'peak_memory' => $peak_memory,
            'total_requests' => $count
        );
    }
}