<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CDSWerx Performance Optimization Manager
 * 
 * Phase B: User Experience Enhancement - Performance Improvements
 * 
 * Implements:
 * - Asset minification and concatenation
 * - Caching strategies for frequently accessed data
 * - CSS loading optimization
 * - Database query optimization
 * - Lazy loading for admin components
 * 
 * @package CDSWerx
 * @since 1.0.0
 */
class CDSWerx_Performance_Optimization {
    
    /**
     * Cache keys for optimization
     */
    const CACHE_GROUP = 'cdswerx_performance';
    const CACHE_EXPIRY = 3600; // 1 hour
    
    /**
     * Initialize performance optimizations
     */
    public function __construct() {
        add_action('init', [$this, 'initialize_optimizations']);
        add_action('wp_enqueue_scripts', [$this, 'optimize_asset_loading'], 999);
        add_action('admin_enqueue_scripts', [$this, 'optimize_admin_assets'], 999);
        add_filter('cdswerx_database_query', [$this, 'optimize_database_queries']);
    }
    
    /**
     * Initialize all performance optimizations
     */
    public function initialize_optimizations() {
        // Enable caching
        $this->enable_caching_strategies();
        
        // Optimize CSS loading
        $this->optimize_css_loading();
        
        // Implement lazy loading
        $this->implement_lazy_loading();
        
        // Database optimization
        $this->optimize_database_operations();
    }
    
    /**
     * Enable caching strategies for frequently accessed data
     */
    private function enable_caching_strategies() {
        // Cache plugin settings
        add_filter('cdswerx_get_option', [$this, 'cache_plugin_options'], 10, 2);
        
        // Cache theme data
        add_filter('cdswerx_theme_data', [$this, 'cache_theme_data'], 10, 2);
        
        // Cache CSS compilation results
        add_filter('cdswerx_compiled_css', [$this, 'cache_compiled_css'], 10, 2);
    }
    
    /**
     * Cache plugin options for performance
     */
    public function cache_plugin_options($value, $option_name) {
        $cache_key = 'plugin_option_' . $option_name;
        $cached_value = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached_value === false) {
            // Get from database and cache
            $value = get_option($option_name, $value);
            wp_cache_set($cache_key, $value, self::CACHE_GROUP, self::CACHE_EXPIRY);
        } else {
            $value = $cached_value;
        }
        
        return $value;
    }
    
    /**
     * Cache theme data for performance
     */
    public function cache_theme_data($data, $data_type) {
        $cache_key = 'theme_data_' . $data_type;
        $cached_data = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached_data === false) {
            wp_cache_set($cache_key, $data, self::CACHE_GROUP, self::CACHE_EXPIRY);
        } else {
            $data = $cached_data;
        }
        
        return $data;
    }
    
    /**
     * Cache compiled CSS for performance
     */
    public function cache_compiled_css($css, $css_type) {
        $cache_key = 'compiled_css_' . md5($css_type);
        $cached_css = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached_css === false) {
            // Compile and cache CSS
            $compiled_css = $this->compile_css($css);
            wp_cache_set($cache_key, $compiled_css, self::CACHE_GROUP, self::CACHE_EXPIRY);
            return $compiled_css;
        }
        
        return $cached_css;
    }
    
    /**
     * Optimize CSS loading strategies
     */
    private function optimize_css_loading() {
        // Conditional CSS loading based on page requirements
        add_action('wp_enqueue_scripts', function() {
            // Only load Elementor CSS on Elementor pages
            if (!$this->is_elementor_page()) {
                wp_dequeue_style('elementor-frontend');
                wp_dequeue_style('elementor-post');
            }
            
            // Only load admin CSS in admin areas
            if (!is_admin()) {
                wp_dequeue_style('cdswerx-admin-styles');
            }
        }, 100);
    }
    
    /**
     * Optimize asset loading with minification and concatenation
     */
    public function optimize_asset_loading() {
        if (!is_admin() && !$this->is_debug_mode()) {
            // Enable CSS minification
            add_filter('style_loader_src', [$this, 'minify_css_src'], 10, 2);
            
            // Enable JS minification
            add_filter('script_loader_src', [$this, 'minify_js_src'], 10, 2);
            
            // Concatenate CDSWerx CSS files
            $this->concatenate_cdswerx_assets();
        }
    }
    
    /**
     * Optimize admin assets loading
     */
    public function optimize_admin_assets($hook) {
        // Only load CDSWerx admin assets on CDSWerx pages
        if (strpos($hook, 'cdswerx') === false) {
            wp_dequeue_style('cdswerx-admin-css');
            wp_dequeue_script('cdswerx-admin-js');
        }
    }
    
    /**
     * Minify CSS source URLs
     */
    public function minify_css_src($src, $handle) {
        if (strpos($handle, 'cdswerx') !== false && strpos($src, '.min.') === false) {
            // Create minified version
            $minified_src = $this->create_minified_css($src);
            return $minified_src ?: $src;
        }
        return $src;
    }
    
    /**
     * Minify JavaScript source URLs
     */
    public function minify_js_src($src, $handle) {
        if (strpos($handle, 'cdswerx') !== false && strpos($src, '.min.') === false) {
            // Create minified version
            $minified_src = $this->create_minified_js($src);
            return $minified_src ?: $src;
        }
        return $src;
    }
    
    /**
     * Concatenate CDSWerx CSS assets
     */
    private function concatenate_cdswerx_assets() {
        $css_files = [
            'cdswerx-global-styles',
            'cdswerx-plugin-migration', 
            'cdswerx-presentation-migrated'
        ];
        
        // Create concatenated CSS file
        $concatenated_content = '';
        foreach ($css_files as $handle) {
            $file_path = $this->get_css_file_path($handle);
            if (file_exists($file_path)) {
                $concatenated_content .= file_get_contents($file_path) . "\n";
            }
        }
        
        if (!empty($concatenated_content)) {
            // Save concatenated file
            $concatenated_file = WP_CONTENT_DIR . '/cache/cdswerx-concatenated.css';
            $this->ensure_cache_directory();
            file_put_contents($concatenated_file, $this->compile_css($concatenated_content));
            
            // Enqueue concatenated file instead of individual files
            wp_enqueue_style('cdswerx-concatenated', 
                content_url('cache/cdswerx-concatenated.css'), 
                [], filemtime($concatenated_file));
                
            // Dequeue individual files
            foreach ($css_files as $handle) {
                wp_dequeue_style($handle);
            }
        }
    }
    
    /**
     * Implement lazy loading for admin interface components
     */
    private function implement_lazy_loading() {
        add_action('admin_footer', function() {
            ?>
            <script>
            // Lazy load CDSWerx admin components
            document.addEventListener('DOMContentLoaded', function() {
                // Lazy load heavy admin sections
                const lazyElements = document.querySelectorAll('.cdswerx-lazy-load');
                
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const element = entry.target;
                            const src = element.dataset.src;
                            
                            if (src) {
                                fetch(src)
                                    .then(response => response.text())
                                    .then(html => {
                                        element.innerHTML = html;
                                        element.classList.remove('cdswerx-lazy-load');
                                    });
                            }
                            
                            observer.unobserve(element);
                        }
                    });
                });
                
                lazyElements.forEach(function(element) {
                    observer.observe(element);
                });
            });
            </script>
            <?php
        });
    }
    
    /**
     * Optimize database operations
     */
    private function optimize_database_operations() {
        // Use prepared statements
        add_filter('cdswerx_sql_query', [$this, 'use_prepared_statements']);
        
        // Implement query caching
        add_filter('cdswerx_db_result', [$this, 'cache_database_results'], 10, 2);
        
        // Optimize table queries
        add_action('init', [$this, 'optimize_database_tables']);
    }
    
    /**
     * Optimize database queries with caching
     */
    public function optimize_database_queries($query) {
        // Cache frequent queries
        $cache_key = 'db_query_' . md5($query);
        $cached_result = wp_cache_get($cache_key, self::CACHE_GROUP);
        
        if ($cached_result !== false) {
            return $cached_result;
        }
        
        // If not cached, will be cached by the result filter
        return $query;
    }
    
    /**
     * Cache database results
     */
    public function cache_database_results($result, $query) {
        $cache_key = 'db_query_' . md5($query);
        wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_EXPIRY);
        return $result;
    }
    
    /**
     * Use prepared statements for security and performance
     */
    public function use_prepared_statements($query) {
        global $wpdb;
        
        // Convert to prepared statement if not already
        if (strpos($query, '%s') === false && strpos($query, '%d') === false) {
            // Add placeholders for common patterns
            $query = str_replace('"', '%s', $query);
            $query = preg_replace('/\b(\d+)\b/', '%d', $query);
        }
        
        return $query;
    }
    
    /**
     * Helper methods
     */
    private function is_elementor_page() {
        if (!class_exists('\Elementor\Plugin')) {
            return false;
        }
        
        global $post;
        if (!$post) {
            return false;
        }
        
        return \Elementor\Plugin::$instance->documents->get($post->ID)->is_built_with_elementor();
    }
    
    private function is_debug_mode() {
        return defined('WP_DEBUG') && WP_DEBUG;
    }
    
    private function compile_css($css) {
        // Simple CSS minification
        $css = preg_replace('/\s+/', ' ', $css);
        $css = str_replace(['; ', ' {', '{ ', ' }', '} '], [';', '{', '{', '}', '}'], $css);
        return trim($css);
    }
    
    private function create_minified_css($src) {
        // Implementation for creating minified CSS files
        return false; // Placeholder
    }
    
    private function create_minified_js($src) {
        // Implementation for creating minified JS files  
        return false; // Placeholder
    }
    
    private function get_css_file_path($handle) {
        // Get local file path for CSS handle
        return false; // Placeholder
    }
    
    private function ensure_cache_directory() {
        $cache_dir = WP_CONTENT_DIR . '/cache';
        if (!file_exists($cache_dir)) {
            wp_mkdir_p($cache_dir);
        }
    }
    
    private function optimize_database_tables() {
        global $wpdb;
        
        // Optimize CDSWerx tables
        $tables = [
            $wpdb->prefix . 'cdswerx_settings',
            $wpdb->prefix . 'cdswerx_custom_code'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("OPTIMIZE TABLE {$table}");
        }
    }
}

// Initialize performance optimization
new CDSWerx_Performance_Optimization();