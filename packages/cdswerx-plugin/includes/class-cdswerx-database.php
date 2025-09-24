<?php

/**
 * CDSWerx Database Management Class
 * 
 * Handles database schema creation, migration, and version control
 * for the Custom Code System
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CDSWerx_Database {
    
    /**
     * Current database schema version
     */
    const SCHEMA_VERSION = '1.0.0';
    
    /**
     * Option key for storing schema version
     */
    const VERSION_OPTION_KEY = 'cdswerx_custom_code_schema_version';
    
    /**
     * Initialize database management
     */
    public function __construct() {
        add_action('plugins_loaded', array($this, 'check_database_version'));
    }
    
    /**
     * Check if database needs to be created or updated
     */
    public function check_database_version() {
        $installed_version = get_option(self::VERSION_OPTION_KEY, '0.0.0');
        
        if (version_compare($installed_version, self::SCHEMA_VERSION, '<')) {
            $this->create_or_update_tables();
            update_option(self::VERSION_OPTION_KEY, self::SCHEMA_VERSION);
        }
    }
    
    /**
     * Create or update database tables
     */
    public function create_or_update_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Main custom code storage table
        $custom_code_table = $wpdb->prefix . 'cdswerx_custom_code';
        $sql_custom_code = "CREATE TABLE {$custom_code_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            code_type varchar(10) NOT NULL DEFAULT 'css',
            category varchar(50) NOT NULL DEFAULT 'global',
            name varchar(255) NOT NULL,
            content longtext,
            is_active tinyint(1) DEFAULT 1,
            load_priority int(11) DEFAULT 10,
            conditions text,
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            modified_date datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY code_type_category (code_type, category),
            KEY is_active (is_active),
            KEY load_priority (load_priority)
        ) {$charset_collate};";
        
        // Version control/history table
        $history_table = $wpdb->prefix . 'cdswerx_code_history';
        $sql_history = "CREATE TABLE {$history_table} (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            code_id bigint(20) UNSIGNED NOT NULL,
            content_backup longtext,
            change_description varchar(500),
            changed_by bigint(20) UNSIGNED,
            created_date datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY code_id (code_id),
            KEY changed_by (changed_by)
        ) {$charset_collate};";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_custom_code);
        dbDelta($sql_history);
        
        // Migrate existing data if this is first installation
        $this->migrate_existing_data();
    }
    
    /**
     * Migrate existing custom CSS/JS from options to new table structure
     */
    private function migrate_existing_data() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        // Check if migration has already been done
        $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
        if ($existing_count > 0) {
            return; // Already migrated
        }
        
        // Get existing custom CSS/JS from options
        $options = get_option('cdswerx_options', array());
        
        if (!empty($options['custom_css'])) {
            $wpdb->insert(
                $table,
                array(
                    'code_type' => 'css',
                    'category' => 'global',
                    'name' => 'Migrated Custom CSS',
                    'content' => $options['custom_css'],
                    'is_active' => 1,
                    'load_priority' => 10
                ),
                array('%s', '%s', '%s', '%s', '%d', '%d')
            );
        }
        
        if (!empty($options['custom_js'])) {
            $wpdb->insert(
                $table,
                array(
                    'code_type' => 'js',
                    'category' => 'global', 
                    'name' => 'Migrated Custom JavaScript',
                    'content' => $options['custom_js'],
                    'is_active' => 1,
                    'load_priority' => 10
                ),
                array('%s', '%s', '%s', '%s', '%d', '%d')
            );
        }
        
        // Also check theme customizer
        $theme_css = get_theme_mod('cdswerx_custom_css', '');
        if (!empty($theme_css)) {
            $wpdb->insert(
                $table,
                array(
                    'code_type' => 'css',
                    'category' => 'theme',
                    'name' => 'Migrated Theme CSS',
                    'content' => $theme_css,
                    'is_active' => 1,
                    'load_priority' => 5
                ),
                array('%s', '%s', '%s', '%s', '%d', '%d')
            );
        }
    }
    
    /**
     * Drop all custom code tables (for uninstallation)
     */
    public function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'cdswerx_custom_code',
            $wpdb->prefix . 'cdswerx_code_history'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table}");
        }
        
        delete_option(self::VERSION_OPTION_KEY);
    }
    
    /**
     * Get current schema version
     */
    public function get_schema_version() {
        return get_option(self::VERSION_OPTION_KEY, '0.0.0');
    }
    
    /**
     * Backup existing data before major changes
     */
    public function backup_data() {
        global $wpdb;
        
        $backup_data = array();
        $tables = array('cdswerx_custom_code', 'cdswerx_code_history');
        
        foreach ($tables as $table) {
            $full_table_name = $wpdb->prefix . $table;
            $results = $wpdb->get_results("SELECT * FROM {$full_table_name}", ARRAY_A);
            $backup_data[$table] = $results;
        }
        
        $backup_file = WP_CONTENT_DIR . '/uploads/cdswerx-backup-' . date('Y-m-d-H-i-s') . '.json';
        file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));
        
        return $backup_file;
    }
    
    /**
     * Test database connectivity and table structure
     */
    public function test_database() {
        global $wpdb;
        
        $tests = array();
        $tables = array('cdswerx_custom_code', 'cdswerx_code_history');
        
        foreach ($tables as $table) {
            $full_table_name = $wpdb->prefix . $table;
            $exists = $wpdb->get_var("SHOW TABLES LIKE '{$full_table_name}'");
            $tests[$table] = ($exists === $full_table_name);
        }
        
        return $tests;
    }
}

// Initialize database management
new CDSWerx_Database();