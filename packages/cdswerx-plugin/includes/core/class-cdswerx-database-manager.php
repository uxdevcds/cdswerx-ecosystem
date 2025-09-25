<?php
/**
 * CDSWerx Database Manager
 *
 * Database operations, schema management, and data integrity
 * Consolidates database functionality from multiple classes
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
 * CDSWerx Database Manager Class
 * 
 * Handles database operations, schema management, and data integrity
 * Consolidates functionality from database-related classes
 * 
 * @since 2.0.0
 */
class CDSWerx_Database_Manager {
    
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
     * Database version
     * 
     * @since 2.0.0
     * @access private
     * @var string $db_version
     */
    private $db_version = '2.0.0';
    
    /**
     * Table definitions
     * 
     * @since 2.0.0
     * @access private
     * @var array $tables
     */
    private $tables = array();
    
    /**
     * Database settings
     * 
     * @since 2.0.0
     * @access private
     * @var array $db_settings
     */
    private $db_settings = array();
    
    /**
     * Initialize database manager
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
        
        // Initialize table definitions
        $this->init_table_definitions();
        
        // Initialize database settings
        $this->init_db_settings();
    }
    
    /**
     * Register database hooks
     * 
     * @since 2.0.0
     * @param Cdswerx_Loader $loader The loader instance
     */
    public function register_hooks($loader) {
        // Database initialization
        $loader->add_action('init', $this, 'check_database_version');
        
        // Installation/upgrade hooks
        register_activation_hook(plugin_dir_path(dirname(dirname(__FILE__))) . 'cdswerx.php', array($this, 'on_activation'));
        register_deactivation_hook(plugin_dir_path(dirname(dirname(__FILE__))) . 'cdswerx.php', array($this, 'on_deactivation'));
        
        // Multisite hooks
        $loader->add_action('wpmu_new_blog', $this, 'on_new_blog', 10, 6);
        $loader->add_filter('wpmu_drop_tables', $this, 'on_blog_delete');
        
        // Maintenance hooks
        $loader->add_action('cdswerx_database_maintenance', $this, 'run_database_maintenance');
        
        // AJAX handlers
        $loader->add_action('wp_ajax_cdswerx_optimize_database', $this, 'ajax_optimize_database');
        $loader->add_action('wp_ajax_cdswerx_backup_settings', $this, 'ajax_backup_settings');
        $loader->add_action('wp_ajax_cdswerx_restore_settings', $this, 'ajax_restore_settings');
    }
    
    /**
     * Initialize table definitions
     * 
     * @since 2.0.0
     */
    private function init_table_definitions() {
        global $wpdb;
        
        $this->tables = array(
            'cdswerx_settings' => array(
                'name' => $wpdb->prefix . 'cdswerx_settings',
                'schema' => "
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    setting_name varchar(255) NOT NULL,
                    setting_value longtext,
                    autoload varchar(20) NOT NULL DEFAULT 'yes',
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY setting_name (setting_name),
                    KEY autoload (autoload)
                "
            ),
            'cdswerx_logs' => array(
                'name' => $wpdb->prefix . 'cdswerx_logs',
                'schema' => "
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    log_level varchar(20) NOT NULL DEFAULT 'info',
                    message text NOT NULL,
                    context longtext,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY log_level (log_level),
                    KEY created_at (created_at)
                "
            ),
            'cdswerx_cache' => array(
                'name' => $wpdb->prefix . 'cdswerx_cache',
                'schema' => "
                    cache_key varchar(255) NOT NULL,
                    cache_value longtext,
                    expires_at datetime,
                    created_at datetime DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (cache_key),
                    KEY expires_at (expires_at)
                "
            )
        );
    }
    
    /**
     * Initialize database settings
     * 
     * @since 2.0.0
     */
    private function init_db_settings() {
        $this->db_settings = array(
            'charset' => 'utf8mb4',
            'collate' => 'utf8mb4_unicode_ci',
            'maintenance_mode' => false,
            'backup_retention_days' => 30,
            'cache_enabled' => true,
            'logging_enabled' => true,
            'log_retention_days' => 7
        );
    }
    
    /**
     * Check database version and upgrade if needed
     * 
     * @since 2.0.0
     */
    public function check_database_version() {
        $installed_version = get_option('cdswerx_db_version', '0.0.0');
        
        if (version_compare($installed_version, $this->db_version, '<')) {
            $this->upgrade_database($installed_version, $this->db_version);
        }
    }
    
    /**
     * Handle plugin activation
     * 
     * @since 2.0.0
     */
    public function on_activation() {
        // Create tables
        $this->create_tables();
        
        // Insert default data
        $this->insert_default_data();
        
        // Set database version
        update_option('cdswerx_db_version', $this->db_version);
        
        // Schedule database maintenance
        if (!wp_next_scheduled('cdswerx_database_maintenance')) {
            wp_schedule_event(time(), 'weekly', 'cdswerx_database_maintenance');
        }
        
        // Handle multisite activation
        if (is_multisite()) {
            $this->handle_multisite_activation();
        }
    }
    
    /**
     * Handle plugin deactivation
     * 
     * @since 2.0.0
     */
    public function on_deactivation() {
        // Clear scheduled events
        wp_clear_scheduled_hook('cdswerx_database_maintenance');
        
        // Optional: Cleanup temporary data
        $this->cleanup_temporary_data();
    }
    
    /**
     * Create database tables
     * 
     * @since 2.0.0
     */
    private function create_tables() {
        global $wpdb;
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($this->tables as $table_key => $table_data) {
            $sql = "CREATE TABLE {$table_data['name']} (
                {$table_data['schema']}
            ) {$this->get_charset_collate()};";
            
            dbDelta($sql);
            
            // Verify table creation
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_data['name'])) !== $table_data['name']) {
                error_log("CDSWerx: Failed to create table {$table_data['name']}");
            }
        }
    }
    
    /**
     * Insert default data
     * 
     * @since 2.0.0
     */
    private function insert_default_data() {
        // Insert default settings
        $default_settings = array(
            'version' => $this->version,
            'installation_date' => current_time('mysql'),
            'database_version' => $this->db_version
        );
        
        foreach ($default_settings as $key => $value) {
            $this->set_setting($key, $value);
        }
    }
    
    /**
     * Get charset collate
     * 
     * @since 2.0.0
     * @return string Charset collate
     */
    private function get_charset_collate() {
        global $wpdb;
        
        $charset_collate = '';
        
        if (!empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        }
        
        if (!empty($wpdb->collate)) {
            $charset_collate .= " COLLATE {$wpdb->collate}";
        }
        
        return $charset_collate;
    }
    
    /**
     * Upgrade database
     * 
     * @since 2.0.0
     * @param string $from_version From version
     * @param string $to_version To version
     */
    private function upgrade_database($from_version, $to_version) {
        // Log upgrade start
        $this->log('info', "Starting database upgrade from {$from_version} to {$to_version}");
        
        // Run version-specific upgrades
        if (version_compare($from_version, '1.0.0', '<')) {
            $this->upgrade_to_1_0_0();
        }
        
        if (version_compare($from_version, '2.0.0', '<')) {
            $this->upgrade_to_2_0_0();
        }
        
        // Update database version
        update_option('cdswerx_db_version', $to_version);
        
        // Log upgrade completion
        $this->log('info', "Database upgrade completed to version {$to_version}");
    }
    
    /**
     * Upgrade to version 1.0.0
     * 
     * @since 2.0.0
     */
    private function upgrade_to_1_0_0() {
        // Initial installation
        $this->create_tables();
        $this->insert_default_data();
    }
    
    /**
     * Upgrade to version 2.0.0
     * 
     * @since 2.0.0
     */
    private function upgrade_to_2_0_0() {
        // Migrate existing options to new structure
        $this->migrate_legacy_options();
        
        // Add new tables if needed
        $this->create_tables();
        
        // Update schema if needed
        $this->update_table_schemas();
    }
    
    /**
     * Migrate legacy options
     * 
     * @since 2.0.0
     */
    private function migrate_legacy_options() {
        // Migrate old option names to new structure
        $legacy_options = array(
            'cdswerx_advanced_css' => 'cdswerx_css_settings',
            'cdswerx_custom_js' => 'cdswerx_js_settings'
        );
        
        foreach ($legacy_options as $old_option => $new_option) {
            $value = get_option($old_option);
            if ($value !== false) {
                update_option($new_option, $value);
                delete_option($old_option);
            }
        }
    }
    
    /**
     * Update table schemas
     * 
     * @since 2.0.0
     */
    private function update_table_schemas() {
        global $wpdb;
        
        // Add new columns to existing tables if needed
        $alterations = array(
            $this->tables['cdswerx_settings']['name'] => array(
                'updated_at' => "ADD COLUMN updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP"
            )
        );
        
        foreach ($alterations as $table => $columns) {
            foreach ($columns as $column => $sql) {
                // Check if column exists
                $column_exists = $wpdb->get_results($wpdb->prepare(
                    "SHOW COLUMNS FROM {$table} LIKE %s",
                    $column
                ));
                
                if (empty($column_exists)) {
                    $wpdb->query("ALTER TABLE {$table} {$sql}");
                }
            }
        }
    }
    
    /**
     * Handle multisite activation
     * 
     * @since 2.0.0
     */
    private function handle_multisite_activation() {
        global $wpdb;
        
        // Get all blog IDs
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
        
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            $this->create_tables();
            $this->insert_default_data();
            restore_current_blog();
        }
    }
    
    /**
     * Handle new blog creation in multisite
     * 
     * @since 2.0.0
     * @param int $blog_id Blog ID
     * @param int $user_id User ID
     * @param string $domain Domain
     * @param string $path Path
     * @param int $site_id Site ID
     * @param array $meta Site meta
     */
    public function on_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta) {
        if (is_plugin_active_for_network(plugin_basename(dirname(dirname(dirname(__FILE__))) . '/cdswerx.php'))) {
            switch_to_blog($blog_id);
            $this->create_tables();
            $this->insert_default_data();
            restore_current_blog();
        }
    }
    
    /**
     * Handle blog deletion in multisite
     * 
     * @since 2.0.0
     * @param array $tables Tables to drop
     * @return array Modified tables array
     */
    public function on_blog_delete($tables) {
        global $wpdb;
        
        foreach ($this->tables as $table_data) {
            $tables[] = $table_data['name'];
        }
        
        return $tables;
    }
    
    /**
     * Set a setting value
     * 
     * @since 2.0.0
     * @param string $name Setting name
     * @param mixed $value Setting value
     * @param bool $autoload Autoload setting
     * @return bool Success status
     */
    public function set_setting($name, $value, $autoload = true) {
        global $wpdb;
        
        $table_name = $this->tables['cdswerx_settings']['name'];
        $autoload_value = $autoload ? 'yes' : 'no';
        
        $result = $wpdb->replace(
            $table_name,
            array(
                'setting_name' => $name,
                'setting_value' => maybe_serialize($value),
                'autoload' => $autoload_value
            ),
            array('%s', '%s', '%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Get a setting value
     * 
     * @since 2.0.0
     * @param string $name Setting name
     * @param mixed $default Default value
     * @return mixed Setting value
     */
    public function get_setting($name, $default = false) {
        global $wpdb;
        
        $table_name = $this->tables['cdswerx_settings']['name'];
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM {$table_name} WHERE setting_name = %s",
            $name
        ));
        
        if ($value === null) {
            return $default;
        }
        
        return maybe_unserialize($value);
    }
    
    /**
     * Delete a setting
     * 
     * @since 2.0.0
     * @param string $name Setting name
     * @return bool Success status
     */
    public function delete_setting($name) {
        global $wpdb;
        
        $table_name = $this->tables['cdswerx_settings']['name'];
        
        $result = $wpdb->delete(
            $table_name,
            array('setting_name' => $name),
            array('%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Log a message
     * 
     * @since 2.0.0
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Log context
     * @return bool Success status
     */
    public function log($level, $message, $context = array()) {
        if (!$this->db_settings['logging_enabled']) {
            return false;
        }
        
        global $wpdb;
        
        $table_name = $this->tables['cdswerx_logs']['name'];
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'log_level' => $level,
                'message' => $message,
                'context' => maybe_serialize($context)
            ),
            array('%s', '%s', '%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Get logs
     * 
     * @since 2.0.0
     * @param array $args Query arguments
     * @return array Log entries
     */
    public function get_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'level' => '',
            'limit' => 100,
            'offset' => 0,
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        $table_name = $this->tables['cdswerx_logs']['name'];
        
        $where = '';
        if (!empty($args['level'])) {
            $where = $wpdb->prepare(' WHERE log_level = %s', $args['level']);
        }
        
        $query = "SELECT * FROM {$table_name}{$where} ORDER BY created_at {$args['order']} LIMIT {$args['limit']} OFFSET {$args['offset']}";
        
        $results = $wpdb->get_results($query, ARRAY_A);
        
        // Unserialize context data
        foreach ($results as &$result) {
            $result['context'] = maybe_unserialize($result['context']);
        }
        
        return $results;
    }
    
    /**
     * Set cache value
     * 
     * @since 2.0.0
     * @param string $key Cache key
     * @param mixed $value Cache value
     * @param int $expiration Expiration time in seconds
     * @return bool Success status
     */
    public function set_cache($key, $value, $expiration = 3600) {
        if (!$this->db_settings['cache_enabled']) {
            return false;
        }
        
        global $wpdb;
        
        $table_name = $this->tables['cdswerx_cache']['name'];
        $expires_at = date('Y-m-d H:i:s', time() + $expiration);
        
        $result = $wpdb->replace(
            $table_name,
            array(
                'cache_key' => $key,
                'cache_value' => maybe_serialize($value),
                'expires_at' => $expires_at
            ),
            array('%s', '%s', '%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Get cache value
     * 
     * @since 2.0.0
     * @param string $key Cache key
     * @param mixed $default Default value
     * @return mixed Cache value
     */
    public function get_cache($key, $default = false) {
        if (!$this->db_settings['cache_enabled']) {
            return $default;
        }
        
        global $wpdb;
        
        $table_name = $this->tables['cdswerx_cache']['name'];
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT cache_value FROM {$table_name} WHERE cache_key = %s AND (expires_at IS NULL OR expires_at > NOW())",
            $key
        ));
        
        if ($value === null) {
            return $default;
        }
        
        return maybe_unserialize($value);
    }
    
    /**
     * Delete cache value
     * 
     * @since 2.0.0
     * @param string $key Cache key
     * @return bool Success status
     */
    public function delete_cache($key) {
        global $wpdb;
        
        $table_name = $this->tables['cdswerx_cache']['name'];
        
        $result = $wpdb->delete(
            $table_name,
            array('cache_key' => $key),
            array('%s')
        );
        
        return $result !== false;
    }
    
    /**
     * Run database maintenance
     * 
     * @since 2.0.0
     */
    public function run_database_maintenance() {
        // Clean expired cache entries
        $this->clean_expired_cache();
        
        // Clean old log entries
        $this->clean_old_logs();
        
        // Optimize tables
        $this->optimize_tables();
        
        // Log maintenance completion
        $this->log('info', 'Database maintenance completed');
    }
    
    /**
     * Clean expired cache entries
     * 
     * @since 2.0.0
     */
    private function clean_expired_cache() {
        global $wpdb;
        
        $table_name = $this->tables['cdswerx_cache']['name'];
        
        $wpdb->query("DELETE FROM {$table_name} WHERE expires_at < NOW()");
    }
    
    /**
     * Clean old log entries
     * 
     * @since 2.0.0
     */
    private function clean_old_logs() {
        global $wpdb;
        
        $table_name = $this->tables['cdswerx_logs']['name'];
        $retention_days = $this->db_settings['log_retention_days'];
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
    }
    
    /**
     * Optimize database tables
     * 
     * @since 2.0.0
     */
    private function optimize_tables() {
        global $wpdb;
        
        foreach ($this->tables as $table_data) {
            $wpdb->query("OPTIMIZE TABLE {$table_data['name']}");
        }
    }
    
    /**
     * Cleanup temporary data
     * 
     * @since 2.0.0
     */
    private function cleanup_temporary_data() {
        // Clear cache table
        global $wpdb;
        $cache_table = $this->tables['cdswerx_cache']['name'];
        $wpdb->query("TRUNCATE TABLE {$cache_table}");
    }
    
    /**
     * AJAX optimize database
     * 
     * @since 2.0.0
     */
    public function ajax_optimize_database() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_db_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        $this->run_database_maintenance();
        wp_send_json_success(__('Database optimized successfully', 'cdswerx'));
    }
    
    /**
     * AJAX backup settings
     * 
     * @since 2.0.0
     */
    public function ajax_backup_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_db_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        $backup_data = $this->export_settings();
        wp_send_json_success(array('backup' => $backup_data));
    }
    
    /**
     * AJAX restore settings
     * 
     * @since 2.0.0
     */
    public function ajax_restore_settings() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_db_nonce')) {
            wp_send_json_error(__('Security check failed', 'cdswerx'));
        }
        
        $backup_data = $_POST['backup_data'];
        $success = $this->import_settings($backup_data);
        
        if ($success) {
            wp_send_json_success(__('Settings restored successfully', 'cdswerx'));
        } else {
            wp_send_json_error(__('Failed to restore settings', 'cdswerx'));
        }
    }
    
    /**
     * Export settings
     * 
     * @since 2.0.0
     * @return array Exported settings
     */
    public function export_settings() {
        global $wpdb;
        
        $table_name = $this->tables['cdswerx_settings']['name'];
        
        $settings = $wpdb->get_results(
            "SELECT setting_name, setting_value FROM {$table_name}",
            ARRAY_A
        );
        
        $export_data = array();
        foreach ($settings as $setting) {
            $export_data[$setting['setting_name']] = maybe_unserialize($setting['setting_value']);
        }
        
        return array(
            'version' => $this->version,
            'export_date' => current_time('mysql'),
            'settings' => $export_data
        );
    }
    
    /**
     * Import settings
     * 
     * @since 2.0.0
     * @param array $backup_data Backup data
     * @return bool Success status
     */
    public function import_settings($backup_data) {
        if (!isset($backup_data['settings']) || !is_array($backup_data['settings'])) {
            return false;
        }
        
        $success = true;
        
        foreach ($backup_data['settings'] as $name => $value) {
            if (!$this->set_setting($name, $value)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    /**
     * Get database statistics
     * 
     * @since 2.0.0
     * @return array Database statistics
     */
    public function get_database_stats() {
        global $wpdb;
        
        $stats = array();
        
        foreach ($this->tables as $table_key => $table_data) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_data['name']}");
            $size = $wpdb->get_var($wpdb->prepare(
                "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size' 
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE() AND table_name = %s",
                $table_data['name']
            ));
            
            $stats[$table_key] = array(
                'name' => $table_data['name'],
                'rows' => (int) $count,
                'size_mb' => (float) $size
            );
        }
        
        return $stats;
    }
    
    /**
     * Get database settings
     * 
     * @since 2.0.0
     * @return array Database settings
     */
    public function get_db_settings() {
        return $this->db_settings;
    }
    
    /**
     * Update database settings
     * 
     * @since 2.0.0
     * @param array $settings Database settings
     */
    public function update_db_settings($settings) {
        $this->db_settings = array_merge($this->db_settings, $settings);
        update_option('cdswerx_db_settings', $this->db_settings);
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
     * Get database version
     * 
     * @since 2.0.0
     * @return string Database version
     */
    public function get_db_version() {
        return $this->db_version;
    }
}