<?php

/**
 * CDSWerx Import/Export Manager Class
 * 
 * Handles backup, restore, import and export of custom code
 * Provides version control and bulk operations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CDSWerx_Import_Export_Manager {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Code manager instance
     */
    private $code_manager;
    
    /**
     * Export formats supported
     */
    private $supported_formats = array(
        'json' => 'JSON Format',
        'zip' => 'ZIP Archive',
        'sql' => 'SQL Dump'
    );
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor
     */
    private function __construct() {
        $this->code_manager = CDSWerx_Code_Manager::get_instance();
        
        // Add admin actions for import/export
        add_action('wp_ajax_cdswerx_export_codes', array($this, 'ajax_export_codes'));
        add_action('wp_ajax_cdswerx_import_codes', array($this, 'ajax_import_codes'));
        add_action('wp_ajax_cdswerx_create_backup', array($this, 'ajax_create_backup'));
        add_action('wp_ajax_cdswerx_restore_backup', array($this, 'ajax_restore_backup'));
    }
    
    /**
     * Export codes to various formats
     * 
     * @param array $options Export options
     * @return array Export result
     */
    public function export_codes($options = array()) {
        $defaults = array(
            'format' => 'json',
            'code_type' => 'all', // css, js, or all
            'category' => 'all',
            'include_inactive' => false,
            'include_metadata' => true,
            'filename' => 'cdswerx_export_' . date('Y-m-d_H-i-s')
        );
        
        $options = wp_parse_args($options, $defaults);
        
        // Get codes based on criteria
        $codes = $this->get_export_codes($options);
        
        switch ($options['format']) {
            case 'json':
                return $this->export_to_json($codes, $options);
            default:
                return array('success' => false, 'message' => 'Currently only JSON format is supported');
        }
    }
    
    /**
     * Import codes from various formats
     * 
     * @param string $file_path Path to import file
     * @param array $options Import options
     * @return array Import result
     */
    public function import_codes($file_path, $options = array()) {
        $defaults = array(
            'overwrite_existing' => false,
            'update_priorities' => true,
            'preserve_ids' => false,
            'create_backup' => true
        );
        
        $options = wp_parse_args($options, $defaults);
        
        // Create backup before import if requested
        if ($options['create_backup']) {
            $backup_result = $this->create_backup('pre_import_' . date('Y-m-d_H-i-s'));
            if (!$backup_result['success']) {
                return array('success' => false, 'message' => 'Failed to create backup before import');
            }
        }
        
        // Detect file format
        $format = $this->detect_import_format($file_path);
        
        switch ($format) {
            case 'json':
                return $this->import_from_json($file_path, $options);
            default:
                return array('success' => false, 'message' => 'Currently only JSON format is supported');
        }
    }
    
    /**
     * Create full system backup
     * 
     * @param string $backup_name Optional backup name
     * @return array Backup result
     */
    public function create_backup($backup_name = null) {
        if (!$backup_name) {
            $backup_name = 'backup_' . date('Y-m-d_H-i-s');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        $history_table = $wpdb->prefix . 'cdswerx_code_history';
        
        // Get all data
        $codes = $wpdb->get_results("SELECT * FROM {$table}");
        $history = $wpdb->get_results("SELECT * FROM {$history_table}");
        
        $backup_data = array(
            'timestamp' => current_time('mysql'),
            'version' => CDSWERX_VERSION,
            'site_url' => get_site_url(),
            'codes' => $codes,
            'history' => $history,
            'metadata' => array(
                'total_codes' => count($codes),
                'total_history' => count($history),
                'backup_name' => $backup_name
            )
        );
        
        // Save backup
        $backup_dir = $this->get_backup_directory();
        $backup_file = $backup_dir . '/' . sanitize_file_name($backup_name) . '.json';
        
        if (!wp_mkdir_p($backup_dir)) {
            return array('success' => false, 'message' => 'Cannot create backup directory');
        }
        
        $result = file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));
        
        if ($result === false) {
            return array('success' => false, 'message' => 'Failed to write backup file');
        }
        
        return array(
            'success' => true,
            'message' => 'Backup created successfully',
            'backup_file' => $backup_file,
            'backup_name' => $backup_name,
            'size' => filesize($backup_file)
        );
    }
    
    /**
     * Restore from backup
     * 
     * @param string $backup_file Backup file path
     * @param array $options Restore options
     * @return array Restore result
     */
    public function restore_backup($backup_file, $options = array()) {
        $defaults = array(
            'clear_existing' => true,
            'create_pre_restore_backup' => true
        );
        
        $options = wp_parse_args($options, $defaults);
        
        // Create backup before restore
        if ($options['create_pre_restore_backup']) {
            $pre_backup = $this->create_backup('pre_restore_' . date('Y-m-d_H-i-s'));
            if (!$pre_backup['success']) {
                return array('success' => false, 'message' => 'Failed to create pre-restore backup');
            }
        }
        
        // Read backup file
        if (!file_exists($backup_file)) {
            return array('success' => false, 'message' => 'Backup file not found');
        }
        
        $backup_data = json_decode(file_get_contents($backup_file), true);
        
        if (!$backup_data) {
            return array('success' => false, 'message' => 'Invalid backup file format');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        $history_table = $wpdb->prefix . 'cdswerx_code_history';
        
        // Clear existing data if requested
        if ($options['clear_existing']) {
            $wpdb->query("TRUNCATE TABLE {$table}");
            $wpdb->query("TRUNCATE TABLE {$history_table}");
        }
        
        // Restore codes
        $imported_codes = 0;
        foreach ($backup_data['codes'] as $code) {
            $result = $wpdb->insert(
                $table,
                array(
                    'name' => $code->name,
                    'content' => $code->content,
                    'code_type' => $code->code_type,
                    'category' => $code->category,
                    'load_priority' => $code->load_priority,
                    'is_active' => $code->is_active,
                    'metadata' => $code->metadata,
                    'created_date' => $code->created_date,
                    'modified_date' => current_time('mysql')
                )
            );
            
            if ($result) {
                $imported_codes++;
            }
        }
        
        // Restore history
        $imported_history = 0;
        if (isset($backup_data['history'])) {
            foreach ($backup_data['history'] as $history) {
                $result = $wpdb->insert(
                    $history_table,
                    array(
                        'code_id' => $history->code_id,
                        'content' => $history->content,
                        'version_number' => $history->version_number,
                        'change_description' => $history->change_description,
                        'created_date' => $history->created_date
                    )
                );
                
                if ($result) {
                    $imported_history++;
                }
            }
        }
        
        return array(
            'success' => true,
            'message' => 'Backup restored successfully',
            'imported_codes' => $imported_codes,
            'imported_history' => $imported_history
        );
    }
    
    /**
     * Get backup directory path
     * 
     * @return string Backup directory path
     */
    private function get_backup_directory() {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'] . '/cdswerx-backups';
    }
    
    /**
     * List available backups
     * 
     * @return array List of backups
     */
    public function list_backups() {
        $backup_dir = $this->get_backup_directory();
        
        if (!is_dir($backup_dir)) {
            return array();
        }
        
        $backups = array();
        $files = glob($backup_dir . '/*.json');
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            $backups[] = array(
                'file' => $file,
                'name' => basename($file, '.json'),
                'timestamp' => $data['timestamp'] ?? filemtime($file),
                'size' => filesize($file),
                'version' => $data['version'] ?? 'Unknown',
                'total_codes' => $data['metadata']['total_codes'] ?? 0
            );
        }
        
        // Sort by timestamp descending
        usort($backups, function($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        
        return $backups;
    }
    
    /**
     * Get codes for export based on criteria
     * 
     * @param array $options Export options
     * @return array Codes to export
     */
    private function get_export_codes($options) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        $where_clauses = array();
        $prepare_values = array();
        
        // Filter by code type
        if ($options['code_type'] !== 'all') {
            $where_clauses[] = "code_type = %s";
            $prepare_values[] = $options['code_type'];
        }
        
        // Filter by category
        if ($options['category'] !== 'all') {
            $where_clauses[] = "category = %s";
            $prepare_values[] = $options['category'];
        }
        
        // Filter by active status
        if (!$options['include_inactive']) {
            $where_clauses[] = "is_active = 1";
        }
        
        $where_sql = empty($where_clauses) ? '1=1' : implode(' AND ', $where_clauses);
        $sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY code_type, category, load_priority";
        
        if (!empty($prepare_values)) {
            return $wpdb->get_results($wpdb->prepare($sql, $prepare_values));
        } else {
            return $wpdb->get_results($sql);
        }
    }
    
    /**
     * Export to JSON format
     * 
     * @param array $codes Codes to export
     * @param array $options Export options
     * @return array Export result
     */
    private function export_to_json($codes, $options) {
        $export_data = array(
            'export_date' => current_time('mysql'),
            'version' => CDSWERX_VERSION,
            'site_url' => get_site_url(),
            'codes' => $codes,
            'metadata' => array(
                'total_codes' => count($codes),
                'export_options' => $options
            )
        );
        
        $json_content = json_encode($export_data, JSON_PRETTY_PRINT);
        $filename = $options['filename'] . '.json';
        
        return array(
            'success' => true,
            'content' => $json_content,
            'filename' => $filename,
            'mime_type' => 'application/json',
            'size' => strlen($json_content)
        );
    }
    
    /**
     * Detect import file format
     * 
     * @param string $file_path File path
     * @return string Format (json|zip|sql)
     */
    private function detect_import_format($file_path) {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'json':
                return 'json';
            case 'zip':
                return 'zip';
            case 'sql':
                return 'sql';
            default:
                return 'unknown';
        }
    }
    
    /**
     * Import from JSON format
     * 
     * @param string $file_path File path
     * @param array $options Import options
     * @return array Import result
     */
    private function import_from_json($file_path, $options) {
        $content = file_get_contents($file_path);
        $data = json_decode($content, true);
        
        if (!$data || !isset($data['codes'])) {
            return array('success' => false, 'message' => 'Invalid JSON format');
        }
        
        $imported = 0;
        $skipped = 0;
        
        foreach ($data['codes'] as $code_data) {
            $result = $this->import_single_code($code_data, $options);
            if ($result) {
                $imported++;
            } else {
                $skipped++;
            }
        }
        
        return array(
            'success' => true,
            'message' => "Import completed: {$imported} imported, {$skipped} skipped",
            'imported' => $imported,
            'skipped' => $skipped
        );
    }
    
    /**
     * Import single code entry
     * 
     * @param array $code_data Code data
     * @param array $options Import options
     * @return bool Success status
     */
    private function import_single_code($code_data, $options) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        // Check if code already exists
        if (!$options['overwrite_existing']) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table} WHERE name = %s AND code_type = %s",
                $code_data['name'],
                $code_data['code_type']
            ));
            
            if ($exists) {
                return false; // Skip existing
            }
        }
        
        // Prepare data for insert/update
        $insert_data = array(
            'name' => $code_data['name'],
            'content' => $code_data['content'],
            'code_type' => $code_data['code_type'],
            'category' => $code_data['category'],
            'load_priority' => $code_data['load_priority'],
            'is_active' => $code_data['is_active'],
            'metadata' => $code_data['metadata'] ?? '',
            'created_date' => current_time('mysql'),
            'modified_date' => current_time('mysql')
        );
        
        return $wpdb->insert($table, $insert_data) !== false;
    }
    
    /**
     * AJAX handler for export codes
     */
    public function ajax_export_codes() {
        check_ajax_referer('cdswerx_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $options = array(
            'format' => sanitize_text_field($_POST['format'] ?? 'json'),
            'code_type' => sanitize_text_field($_POST['code_type'] ?? 'all'),
            'category' => sanitize_text_field($_POST['category'] ?? 'all'),
            'include_inactive' => (bool)($_POST['include_inactive'] ?? false),
            'filename' => sanitize_file_name($_POST['filename'] ?? 'cdswerx_export_' . date('Y-m-d_H-i-s'))
        );
        
        $result = $this->export_codes($options);
        
        wp_send_json($result);
    }
    
    /**
     * AJAX handler for create backup
     */
    public function ajax_create_backup() {
        check_ajax_referer('cdswerx_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $backup_name = sanitize_text_field($_POST['backup_name'] ?? '');
        $result = $this->create_backup($backup_name);
        
        wp_send_json($result);
    }
}