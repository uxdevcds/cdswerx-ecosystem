<?php

/**
 * CDSWerx Custom Code Manager Class
 * 
 * Core API for managing custom CSS and JavaScript code
 * Handles CRUD operations and code retrieval
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CDSWerx_Code_Manager {
    
    /**
     * Singleton instance
     */
    private static $instance = null;
    
    /**
     * Default categories for code organization
     */
    private static $default_categories = array(
        'global' => 'Global Styles',
        'admin' => 'Admin Area Styles', 
        'icons' => 'Icon Font Styles',
        'theme' => 'Theme Specific Styles',
        'page' => 'Page Specific Styles'
    );
    
    /**
     * Get singleton instance
     * 
     * @return CDSWerx_Code_Manager
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Initialize if needed
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    private function __wakeup() {}
    
    /**
     * Get custom code by type, category and status
     * 
     * @param string $type Code type (css|js)
     * @param string $category Category filter
     * @param string $status Status filter (active|inactive|all)
     * @return array Array of code objects
     */
    public function get_code($type = 'css', $category = '', $status = 'active') {
        global $wpdb;
        
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        $where_clauses = array("code_type = %s");
        $prepare_values = array($type);
        
        if (!empty($category)) {
            $where_clauses[] = "category = %s";
            $prepare_values[] = $category;
        }
        
        if ($status !== 'all') {
            $where_clauses[] = "is_active = %d";
            $prepare_values[] = ($status === 'active') ? 1 : 0;
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        $sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY load_priority ASC, created_date ASC";
        
        return $wpdb->get_results($wpdb->prepare($sql, $prepare_values));
    }
    
    /**
     * Get codes by type and optionally category
     * 
     * @param string $type Code type (css|js)
     * @param string $category Optional category filter
     * @return array Array of code objects
     */
    public function get_codes_by_type($type, $category = '') {
        return $this->get_code($type, $category, 'all');
    }
    
    /**
     * Get single code item by ID
     * 
     * @param int $id Code ID
     * @return object|null Code object or null if not found
     */
    public function get_code_by_id($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $id
        ));
    }
    
    /**
     * Save custom code (create or update)
     * 
     * @param array $data Code data array
     * @return int|false Code ID on success, false on failure
     */
    public function save_code($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        // Validate required fields
        $required_fields = array('code_type', 'name', 'content');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return false;
            }
        }
        
        // Set defaults
        $defaults = array(
            'category' => 'global',
            'is_active' => 1,
            'load_priority' => 10,
            'conditions' => ''
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // Backup existing code if updating
        if (!empty($data['id'])) {
            $existing_code = $this->get_code_by_id($data['id']);
            if ($existing_code) {
                self::backup_code($data['id'], 'Auto-backup before update');
            }
        }
        
        // Prepare data for database
        $db_data = array(
            'code_type' => sanitize_text_field($data['code_type']),
            'category' => sanitize_text_field($data['category']),
            'name' => sanitize_text_field($data['name']),
            'content' => $data['content'], // Don't sanitize code content
            'is_active' => intval($data['is_active']),
            'load_priority' => intval($data['load_priority']),
            'conditions' => sanitize_textarea_field($data['conditions'])
        );
        
        $format = array('%s', '%s', '%s', '%s', '%d', '%d', '%s');
        
        if (!empty($data['id'])) {
            // Update existing
            $result = $wpdb->update(
                $table,
                $db_data,
                array('id' => intval($data['id'])),
                $format,
                array('%d')
            );
            
            return ($result !== false) ? intval($data['id']) : false;
        } else {
            // Insert new
            $result = $wpdb->insert($table, $db_data, $format);
            
            return ($result !== false) ? $wpdb->insert_id : false;
        }
    }
    
    /**
     * Delete custom code
     * 
     * @param int $id Code ID
     * @return bool Success status
     */
    public function delete_code($id) {
        global $wpdb;
        
        // Backup before deletion
        self::backup_code($id, 'Backup before deletion');
        
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        $result = $wpdb->delete(
            $table,
            array('id' => intval($id)),
            array('%d')
        );
        
        return ($result !== false);
    }
    
    /**
     * Toggle code active status
     * 
     * @param int $id Code ID
     * @return bool Success status
     */
    public function toggle_code_status($id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        // Get current status
        $current_status = $wpdb->get_var($wpdb->prepare(
            "SELECT is_active FROM {$table} WHERE id = %d",
            $id
        ));
        
        if ($current_status === null) {
            return false;
        }
        
        $new_status = ($current_status == 1) ? 0 : 1;
        
        $result = $wpdb->update(
            $table,
            array('is_active' => $new_status),
            array('id' => intval($id)),
            array('%d'),
            array('%d')
        );
        
        return ($result !== false);
    }
    
    /**
     * Get available categories
     * 
     * @return array Categories array
     */
    public function get_categories() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        // Get categories from database
        $db_categories = $wpdb->get_col("SELECT DISTINCT category FROM {$table} ORDER BY category");
        
        // Merge with default categories
        $all_categories = array_merge(self::$default_categories, array_flip($db_categories));
        
        return $all_categories;
    }
    
    /**
     * Backup code to history table
     * 
     * @param int $code_id Code ID to backup
     * @param string $description Backup description
     * @return int|false History ID on success, false on failure
     */
    public function backup_code($code_id, $description = '') {
        global $wpdb;
        
        $code = $this->get_code_by_id($code_id);
        if (!$code) {
            return false;
        }
        
        $history_table = $wpdb->prefix . 'cdswerx_code_history';
        
        $result = $wpdb->insert(
            $history_table,
            array(
                'code_id' => intval($code_id),
                'content_backup' => $code->content,
                'change_description' => sanitize_text_field($description),
                'changed_by' => get_current_user_id()
            ),
            array('%d', '%s', '%s', '%d')
        );
        
        return ($result !== false) ? $wpdb->insert_id : false;
    }
    
    /**
     * Get code history
     * 
     * @param int $code_id Code ID
     * @param int $limit Number of history entries to retrieve
     * @return array History entries
     */
    public function get_code_history($code_id, $limit = 10) {
        global $wpdb;
        
        $history_table = $wpdb->prefix . 'cdswerx_code_history';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT h.*, u.display_name as changed_by_name 
             FROM {$history_table} h 
             LEFT JOIN {$wpdb->users} u ON h.changed_by = u.ID 
             WHERE h.code_id = %d 
             ORDER BY h.created_date DESC 
             LIMIT %d",
            $code_id,
            $limit
        ));
    }
    
    /**
     * Restore code from history
     * 
     * @param int $code_id Code ID
     * @param int $history_id History entry ID
     * @return bool Success status
     */
    public function restore_from_history($code_id, $history_id) {
        global $wpdb;
        
        $history_table = $wpdb->prefix . 'cdswerx_code_history';
        
        // Get backup content
        $backup = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$history_table} WHERE id = %d AND code_id = %d",
            $history_id,
            $code_id
        ));
        
        if (!$backup) {
            return false;
        }
        
        // Backup current version first
        $this->backup_code($code_id, 'Auto-backup before restore');
        
        // Update with restored content
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        $result = $wpdb->update(
            $table,
            array('content' => $backup->content_backup),
            array('id' => intval($code_id)),
            array('%s'),
            array('%d')
        );
        
        return ($result !== false);
    }
    
    /**
     * Get combined CSS content for frontend injection
     * 
     * @param string $category Optional category filter
     * @return string Combined CSS content
     */
    public function get_combined_css($category = '') {
        $css_codes = self::get_code('css', $category, 'active');
        
        if (empty($css_codes)) {
            return '';
        }
        
        $combined = array();
        foreach ($css_codes as $code) {
            if (!empty($code->content)) {
                $combined[] = "/* {$code->name} */\n" . $code->content;
            }
        }
        
        return implode("\n\n", $combined);
    }
    
    /**
     * Get combined JavaScript content for frontend injection
     * 
     * @param string $category Optional category filter
     * @return string Combined JavaScript content
     */
    public function get_combined_js($category = '') {
        $js_codes = self::get_code('js', $category, 'active');
        
        if (empty($js_codes)) {
            return '';
        }
        
        $combined = array();
        foreach ($js_codes as $code) {
            if (!empty($code->content)) {
                $combined[] = "/* {$code->name} */\n" . $code->content;
            }
        }
        
        return implode("\n\n", $combined);
    }
    
    /**
     * Get codes with conditional loading rules
     * 
     * @param string $type Code type (css|js)
     * @param array $context Loading context (device, page, user_role, etc.)
     * @return array Array of code objects that should be loaded
     */
    public function get_codes_for_context($type, $context = array()) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        // Base query for active codes
        $sql = "SELECT * FROM {$table} WHERE code_type = %s AND is_active = 1";
        $codes = $wpdb->get_results($wpdb->prepare($sql, $type));
        
        $filtered_codes = array();
        
        foreach ($codes as $code) {
            if ($this->should_load_code($code, $context)) {
                $filtered_codes[] = $code;
            }
        }
        
        // Sort by priority
        usort($filtered_codes, function($a, $b) {
            return $a->load_priority - $b->load_priority;
        });
        
        return $filtered_codes;
    }
    
    /**
     * Check if code should be loaded based on conditions
     * 
     * @param object $code Code object
     * @param array $context Loading context
     * @return bool Whether code should be loaded
     */
    private function should_load_code($code, $context) {
        // Parse metadata for loading rules
        $metadata = !empty($code->metadata) ? json_decode($code->metadata, true) : array();
        $loading_rules = isset($metadata['loading_rules']) ? $metadata['loading_rules'] : array();
        
        // If no rules defined, load by default
        if (empty($loading_rules)) {
            return true;
        }
        
        // Device-specific loading
        if (isset($loading_rules['device'])) {
            $current_device = $this->detect_device();
            if (!in_array($current_device, $loading_rules['device'])) {
                return false;
            }
        }
        
        // Page-specific loading
        if (isset($loading_rules['pages'])) {
            if (!$this->matches_page_rules($loading_rules['pages'])) {
                return false;
            }
        }
        
        // User role specific loading
        if (isset($loading_rules['user_roles'])) {
            if (!$this->matches_user_role($loading_rules['user_roles'])) {
                return false;
            }
        }
        
        // Admin area loading
        if (isset($loading_rules['admin_only']) && $loading_rules['admin_only']) {
            if (!is_admin()) {
                return false;
            }
        }
        
        // Frontend only loading
        if (isset($loading_rules['frontend_only']) && $loading_rules['frontend_only']) {
            if (is_admin()) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Detect current device type
     * 
     * @return string Device type (desktop|tablet|mobile)
     */
    private function detect_device() {
        if (!class_exists('Mobile_Detect')) {
            // Simple fallback detection
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            if (preg_match('/Mobile|Android|iPhone|iPad/', $user_agent)) {
                return preg_match('/iPad/', $user_agent) ? 'tablet' : 'mobile';
            }
            return 'desktop';
        }
        
        $detect = new Mobile_Detect();
        if ($detect->isTablet()) {
            return 'tablet';
        } elseif ($detect->isMobile()) {
            return 'mobile';
        }
        return 'desktop';
    }
    
    /**
     * Check if current page matches page rules
     * 
     * @param array $page_rules Page matching rules
     * @return bool Whether current page matches
     */
    private function matches_page_rules($page_rules) {
        global $post;
        
        // Check for specific page IDs
        if (isset($page_rules['page_ids']) && is_array($page_rules['page_ids'])) {
            if (is_page() && in_array(get_the_ID(), $page_rules['page_ids'])) {
                return true;
            }
        }
        
        // Check for post types
        if (isset($page_rules['post_types']) && is_array($page_rules['post_types'])) {
            if (in_array(get_post_type(), $page_rules['post_types'])) {
                return true;
            }
        }
        
        // Check for page templates
        if (isset($page_rules['templates']) && is_array($page_rules['templates'])) {
            $template = get_page_template_slug();
            if (in_array($template, $page_rules['templates'])) {
                return true;
            }
        }
        
        // Check for URL patterns
        if (isset($page_rules['url_patterns']) && is_array($page_rules['url_patterns'])) {
            $current_url = $_SERVER['REQUEST_URI'] ?? '';
            foreach ($page_rules['url_patterns'] as $pattern) {
                if (fnmatch($pattern, $current_url)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Check if current user matches role requirements
     * 
     * @param array $required_roles Required user roles
     * @return bool Whether user has required role
     */
    private function matches_user_role($required_roles) {
        if (!is_user_logged_in()) {
            return in_array('guest', $required_roles);
        }
        
        $user = wp_get_current_user();
        $user_roles = $user->roles;
        
        return !empty(array_intersect($user_roles, $required_roles));
    }
    
    /**
     * Update code metadata with loading rules
     * 
     * @param int $code_id Code ID
     * @param array $loading_rules Loading rules configuration
     * @return bool Success status
     */
    public function update_loading_rules($code_id, $loading_rules) {
        global $wpdb;
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        // Get existing metadata
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT metadata FROM {$table} WHERE id = %d",
            $code_id
        ));
        
        $metadata = $existing ? json_decode($existing, true) : array();
        $metadata['loading_rules'] = $loading_rules;
        
        return $wpdb->update(
            $table,
            array('metadata' => json_encode($metadata)),
            array('id' => $code_id),
            array('%s'),
            array('%d')
        ) !== false;
    }
}