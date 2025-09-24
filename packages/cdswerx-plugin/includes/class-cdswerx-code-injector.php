<?php

/**
 * CDSWerx Code Injector Class
 * 
 * Handles automatic injection of custom CSS and JavaScript
 * into the frontend with optimized loading strategies
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class CDSWerx_Code_Injector {
    
    /**
     * Code manager instance
     */
    private $code_manager;
    
    /**
     * Initialize code injection hooks
     */
    public function __construct() {
        $this->code_manager = CDSWerx_Code_Manager::get_instance();
        // CSS injection hooks
        add_action('wp_head', array($this, 'inject_critical_css'), 1);
        add_action('wp_head', array($this, 'inject_header_css'), 10);
        add_action('wp_footer', array($this, 'inject_footer_css'), 5);
        
        // JavaScript injection hooks
        add_action('wp_head', array($this, 'inject_header_js'), 15);
        add_action('wp_footer', array($this, 'inject_footer_js'), 20);
        
        // Admin injection for admin-specific styles
        add_action('admin_head', array($this, 'inject_admin_css'));
        add_action('admin_footer', array($this, 'inject_admin_js'));
    }
    
    /**
     * Inject critical CSS in head (high priority styles)
     */
    public function inject_critical_css() {
        if (is_admin()) return;
        
        // Get context-aware CSS codes for critical loading
        $context = $this->get_loading_context();
        $codes = $this->code_manager->get_codes_for_context('css', $context);
        
        // Filter for critical priority (1-5)
        $critical_codes = array_filter($codes, function($code) {
            return $code->load_priority >= 1 && $code->load_priority <= 5;
        });
        
        if (!empty($critical_codes)) {
            $critical_css = $this->combine_codes($critical_codes);
        } else {
            $critical_css = '';
        }
        
        if (!empty($critical_css)) {
            echo "\n<!-- CDSWerx Critical CSS -->\n";
            echo '<style id="cdswerx-critical-css" type="text/css">' . "\n";
            echo $this->minify_css($critical_css);
            echo "\n</style>\n";
        }
    }
    
    /**
     * Inject standard CSS in head
     */
    public function inject_header_css() {
        if (is_admin()) return;
        
        // Get standard CSS (priority 6-10)
        $header_css = $this->get_css_by_priority(6, 10);
        
        if (!empty($header_css)) {
            echo "\n<!-- CDSWerx Header CSS -->\n";
            echo '<style id="cdswerx-header-css" type="text/css">' . "\n";
            echo $this->minify_css($header_css);
            echo "\n</style>\n";
        }
    }
    
    /**
     * Inject non-critical CSS in footer
     */
    public function inject_footer_css() {
        if (is_admin()) return;
        
        // Get non-critical CSS (priority 11+)
        $footer_css = $this->get_css_by_priority(11, 999);
        
        if (!empty($footer_css)) {
            echo "\n<!-- CDSWerx Footer CSS -->\n";
            echo '<style id="cdswerx-footer-css" type="text/css">' . "\n";
            echo $this->minify_css($footer_css);
            echo "\n</style>\n";
        }
    }
    
    /**
     * Inject JavaScript in header
     */
    public function inject_header_js() {
        if (is_admin()) return;
        
        // Get header JavaScript (priority 1-10)
        $header_js = $this->get_js_by_priority(1, 10);
        
        if (!empty($header_js)) {
            echo "\n<!-- CDSWerx Header JavaScript -->\n";
            echo '<script id="cdswerx-header-js" type="text/javascript">' . "\n";
            echo $this->minify_js($header_js);
            echo "\n</script>\n";
        }
    }
    
    /**
     * Inject JavaScript in footer
     */
    public function inject_footer_js() {
        if (is_admin()) return;
        
        // Get footer JavaScript (priority 11+)
        $footer_js = $this->get_js_by_priority(11, 999);
        
        if (!empty($footer_js)) {
            echo "\n<!-- CDSWerx Footer JavaScript -->\n";
            echo '<script id="cdswerx-footer-js" type="text/javascript">' . "\n";
            echo $this->minify_js($footer_js);
            echo "\n</script>\n";
        }
    }
    
    /**
     * Inject admin-specific CSS
     */
    public function inject_admin_css() {
        $admin_css = $this->code_manager->get_combined_css('admin');
        
        if (!empty($admin_css)) {
            echo "\n<!-- CDSWerx Admin CSS -->\n";
            echo '<style id="cdswerx-admin-css" type="text/css">' . "\n";
            echo $this->minify_css($admin_css);
            echo "\n</style>\n";
        }
    }
    
    /**
     * Inject admin-specific JavaScript
     */
    public function inject_admin_js() {
        $admin_js = $this->code_manager->get_combined_js('admin');
        
        if (!empty($admin_js)) {
            echo "\n<!-- CDSWerx Admin JavaScript -->\n";
            echo '<script id="cdswerx-admin-js" type="text/javascript">' . "\n";
            echo $this->minify_js($admin_js);
            echo "\n</script>\n";
        }
    }
    
    /**
     * Get CSS by priority range
     * 
     * @param int $min_priority Minimum priority
     * @param int $max_priority Maximum priority
     * @return string Combined CSS content
     */
    private function get_css_by_priority($min_priority, $max_priority) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        $css_codes = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE code_type = 'css' 
             AND is_active = 1 
             AND load_priority >= %d 
             AND load_priority <= %d 
             ORDER BY load_priority ASC, created_date ASC",
            $min_priority,
            $max_priority
        ));
        
        if (empty($css_codes)) {
            return '';
        }
        
        $combined = array();
        foreach ($css_codes as $code) {
            if (!empty($code->content) && $this->should_load_code($code)) {
                $combined[] = "/* {$code->name} */\n" . $code->content;
            }
        }
        
        return implode("\n\n", $combined);
    }
    
    /**
     * Get JavaScript by priority range
     * 
     * @param int $min_priority Minimum priority
     * @param int $max_priority Maximum priority
     * @return string Combined JavaScript content
     */
    private function get_js_by_priority($min_priority, $max_priority) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'cdswerx_custom_code';
        
        $js_codes = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} 
             WHERE code_type = 'js' 
             AND is_active = 1 
             AND load_priority >= %d 
             AND load_priority <= %d 
             ORDER BY load_priority ASC, created_date ASC",
            $min_priority,
            $max_priority
        ));
        
        if (empty($js_codes)) {
            return '';
        }
        
        $combined = array();
        foreach ($js_codes as $code) {
            if (!empty($code->content) && $this->should_load_code($code)) {
                $combined[] = "/* {$code->name} */\n" . $code->content;
            }
        }
        
        return implode("\n\n", $combined);
    }
    
    /**
     * Check if code should be loaded based on conditions
     * 
     * @param object $code Code object
     * @return bool Whether code should be loaded
     */
    private function should_load_code($code) {
        // If no conditions, always load
        if (empty($code->conditions)) {
            return true;
        }
        
        // Parse conditions (simple implementation for now)
        $conditions = json_decode($code->conditions, true);
        if (!$conditions) {
            return true; // Invalid JSON, load anyway
        }
        
        // Check page conditions
        if (isset($conditions['pages'])) {
            $current_page = $this->get_current_page_type();
            if (!in_array($current_page, $conditions['pages'])) {
                return false;
            }
        }
        
        // Check user role conditions
        if (isset($conditions['user_roles'])) {
            $current_user = wp_get_current_user();
            $user_roles = $current_user->roles;
            
            $has_matching_role = false;
            foreach ($conditions['user_roles'] as $required_role) {
                if (in_array($required_role, $user_roles)) {
                    $has_matching_role = true;
                    break;
                }
            }
            
            if (!$has_matching_role) {
                return false;
            }
        }
        
        // Check device conditions (basic user agent detection)
        if (isset($conditions['devices'])) {
            $device = $this->detect_device();
            if (!in_array($device, $conditions['devices'])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get current page type for conditional loading
     * 
     * @return string Page type
     */
    private function get_current_page_type() {
        if (is_front_page()) return 'front_page';
        if (is_home()) return 'blog_home';
        if (is_single()) return 'single_post';
        if (is_page()) return 'page';
        if (is_category()) return 'category';
        if (is_tag()) return 'tag';
        if (is_archive()) return 'archive';
        if (is_search()) return 'search';
        if (is_404()) return '404';
        
        return 'other';
    }
    
    /**
     * Simple device detection
     * 
     * @return string Device type
     */
    private function detect_device() {
        if (wp_is_mobile()) {
            // Basic tablet detection
            if (preg_match('/tablet|ipad/i', $_SERVER['HTTP_USER_AGENT'])) {
                return 'tablet';
            }
            return 'mobile';
        }
        
        return 'desktop';
    }
    
    /**
     * Minify CSS content
     * 
     * @param string $css CSS content
     * @return string Minified CSS
     */
    private function minify_css($css) {
        // Basic CSS minification
        $css = preg_replace('/\/\*.*?\*\//s', '', $css); // Remove comments
        $css = preg_replace('/\s+/', ' ', $css); // Reduce whitespace
        $css = str_replace(array(' {', '{ ', ' }', '} ', ': ', ' :', '; ', ' ;'), 
                          array('{', '{', '}', '}', ':', ':', ';', ';'), $css);
        
        return trim($css);
    }
    
    /**
     * Minify JavaScript content
     * 
     * @param string $js JavaScript content
     * @return string Minified JavaScript
     */
    private function minify_js($js) {
        // Basic JavaScript minification (be careful not to break code)
        $js = preg_replace('/\/\*.*?\*\//s', '', $js); // Remove block comments
        $js = preg_replace('/\/\/.*$/m', '', $js); // Remove line comments
        $js = preg_replace('/\s+/', ' ', $js); // Reduce whitespace
        
        return trim($js);
    }
    
    /**
     * Get cache key for code content
     * 
     * @param string $type Code type
     * @param string $context Loading context
     * @return string Cache key
     */
    private function get_cache_key($type, $context) {
        return 'cdswerx_code_' . $type . '_' . $context . '_' . md5(get_current_user_id() . get_queried_object_id());
    }
    
    /**
     * Clear all code injection caches
     */
    public function clear_cache() {
        $cache_keys = array(
            'cdswerx_code_css_critical',
            'cdswerx_code_css_header', 
            'cdswerx_code_css_footer',
            'cdswerx_code_js_header',
            'cdswerx_code_js_footer',
            'cdswerx_code_css_admin',
            'cdswerx_code_js_admin'
        );
        
        foreach ($cache_keys as $key) {
            wp_cache_delete($key, 'cdswerx');
        }
    }
    
    /**
     * Get current loading context for smart asset loading
     * 
     * @return array Loading context information
     */
    private function get_loading_context() {
        global $post;
        
        return array(
            'device' => $this->detect_device(),
            'is_admin' => is_admin(),
            'is_frontend' => !is_admin(),
            'post_type' => get_post_type(),
            'page_id' => is_page() ? get_the_ID() : null,
            'post_id' => is_single() ? get_the_ID() : null,
            'is_home' => is_home(),
            'is_front_page' => is_front_page(),
            'template' => get_page_template_slug(),
            'user_logged_in' => is_user_logged_in(),
            'user_roles' => is_user_logged_in() ? wp_get_current_user()->roles : array('guest')
        );
    }
    
    /**
     * Combine code objects into single string
     * 
     * @param array $codes Array of code objects
     * @return string Combined code content
     */
    private function combine_codes($codes) {
        $combined = array();
        
        foreach ($codes as $code) {
            if (!empty($code->content)) {
                $combined[] = "/* {$code->name} - Priority: {$code->load_priority} */\n" . $code->content;
            }
        }
        
        return implode("\n\n", $combined);
    }
}

// Initialize code injection
new CDSWerx_Code_Injector();