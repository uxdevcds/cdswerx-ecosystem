<?php
/**
 * CDSWerx Plugin Autoloader
 *
 * Implements PSR-4 autoloading for CDSWerx plugin classes
 * Provides lazy loading for improved performance
 *
 * @package CDSWerx
 * @subpackage Includes
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CDSWerx Autoloader Class
 * 
 * Handles automatic loading of CDSWerx classes following PSR-4 standards
 * Improves performance by loading classes only when needed
 * 
 * @since 1.0.0
 */
class Cdswerx_Autoloader {
    
    /**
     * Class prefix for CDSWerx classes
     * 
     * @since 1.0.0
     * @var string
     */
    private $prefix = 'Cdswerx';
    
    /**
     * Base directory for CDSWerx classes
     * 
     * @since 1.0.0
     * @var string
     */
    private $base_dir;
    
    /**
     * Class mapping for performance optimization
     * 
     * @since 1.0.0
     * @var array
     */
    private $class_map = array();
    
    /**
     * Initialize autoloader
     * 
     * @since 1.0.0
     * @param string $base_dir Base directory for class files
     */
    public function __construct($base_dir = null) {
        if ($base_dir === null) {
            $base_dir = plugin_dir_path(dirname(__FILE__));
        }
        
        $this->base_dir = rtrim($base_dir, '/') . '/';
        $this->build_class_map();
    }
    
    /**
     * Register autoloader with SPL
     * 
     * @since 1.0.0
     * @return void
     */
    public function register() {
        spl_autoload_register(array($this, 'load_class'));
    }
    
    /**
     * Unregister autoloader
     * 
     * @since 1.0.0
     * @return void
     */
    public function unregister() {
        spl_autoload_unregister(array($this, 'load_class'));
    }
    
    /**
     * Load class file
     * 
     * @since 1.0.0
     * @param string $class_name Full class name
     * @return bool True if class file was loaded, false otherwise
     */
    public function load_class($class_name) {
        // Only handle CDSWerx classes
        if (strpos($class_name, $this->prefix) !== 0) {
            return false;
        }
        
        $file_path = $this->get_class_file_path($class_name);
        
        if ($file_path && file_exists($file_path)) {
            require_once $file_path;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get file path for class
     * 
     * @since 1.0.0
     * @param string $class_name Full class name
     * @return string|false File path or false if not found
     */
    private function get_class_file_path($class_name) {
        // Check class map first for performance
        if (isset($this->class_map[$class_name])) {
            return $this->class_map[$class_name];
        }
        
        // Convert class name to file path
        $file_path = $this->convert_class_name_to_file_path($class_name);
        
        if ($file_path && file_exists($file_path)) {
            // Cache the mapping for future use
            $this->class_map[$class_name] = $file_path;
            return $file_path;
        }
        
        return false;
    }
    
    /**
     * Convert class name to file path
     * 
     * @since 1.0.0
     * @param string $class_name Full class name
     * @return string File path
     */
    private function convert_class_name_to_file_path($class_name) {
        // Handle different class naming patterns
        $patterns = array(
            // Admin classes: Cdswerx_Admin -> admin/class/class-cdswerx-admin.php
            '/^Cdswerx_Admin$/' => 'admin/class/class-cdswerx-admin.php',
            '/^Cdswerx_User_Roles$/' => 'admin/class/class-cdswerx-user-roles.php',
            '/^Cdswerx_Extensions$/' => 'admin/class/class-cdswerx-extensions.php',
            
            // Public classes: Cdswerx_Public -> public/class-cdswerx-public.php
            '/^Cdswerx_Public$/' => 'public/class-cdswerx-public.php',
            
            // Core includes: Cdswerx_* -> includes/class-*.php
            '/^Cdswerx_(.+)$/' => 'includes/class-cdswerx-$1.php',
            
            // Hello Elementor classes: Hello_Elementor_* -> includes/class-hello-elementor-*.php
            '/^Hello_Elementor_(.+)$/' => 'includes/class-hello-elementor-$1.php',
        );
        
        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $class_name)) {
                $file_name = preg_replace($pattern, $replacement, $class_name);
                $file_name = strtolower(str_replace('_', '-', $file_name));
                return $this->base_dir . $file_name;
            }
        }
        
        // Default pattern: Class_Name -> includes/class-class-name.php
        $file_name = 'includes/class-' . strtolower(str_replace('_', '-', $class_name)) . '.php';
        return $this->base_dir . $file_name;
    }
    
    /**
     * Build class mapping for performance
     * 
     * @since 1.0.0
     * @return void
     */
    private function build_class_map() {
        $directories = array(
            'includes/',
            'admin/class/',
            'public/'
        );
        
        foreach ($directories as $directory) {
            $full_path = $this->base_dir . $directory;
            
            if (!is_dir($full_path)) {
                continue;
            }
            
            $files = glob($full_path . 'class-*.php');
            
            foreach ($files as $file) {
                $class_name = $this->extract_class_name_from_file($file);
                if ($class_name) {
                    $this->class_map[$class_name] = $file;
                }
            }
        }
    }
    
    /**
     * Extract class name from file path
     * 
     * @since 1.0.0
     * @param string $file_path Full file path
     * @return string|false Class name or false if not found
     */
    private function extract_class_name_from_file($file_path) {
        $file_name = basename($file_path, '.php');
        
        // Handle different file naming patterns
        $patterns = array(
            // class-cdswerx-admin.php -> Cdswerx_Admin
            '/^class-cdswerx-(.+)$/' => 'Cdswerx_$1',
            
            // class-hello-elementor-*.php -> Hello_Elementor_*
            '/^class-hello-elementor-(.+)$/' => 'Hello_Elementor_$1',
            
            // Generic class-*.php -> *
            '/^class-(.+)$/' => '$1',
        );
        
        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $file_name)) {
                $class_name = preg_replace($pattern, $replacement, $file_name);
                $class_name = str_replace('-', '_', $class_name);
                
                // Convert to proper case: word_word -> Word_Word
                $parts = explode('_', $class_name);
                $parts = array_map('ucfirst', $parts);
                return implode('_', $parts);
            }
        }
        
        return false;
    }
    
    /**
     * Get loaded classes count
     * 
     * @since 1.0.0
     * @return int Number of classes in mapping
     */
    public function get_loaded_classes_count() {
        return count($this->class_map);
    }
    
    /**
     * Get class mapping for debugging
     * 
     * @since 1.0.0
     * @return array Class mapping array
     */
    public function get_class_map() {
        return $this->class_map;
    }
    
    /**
     * Check if class can be autoloaded
     * 
     * @since 1.0.0
     * @param string $class_name Class name to check
     * @return bool True if class can be autoloaded
     */
    public function can_load_class($class_name) {
        return $this->get_class_file_path($class_name) !== false;
    }
    
    /**
     * Preload critical classes for performance
     * 
     * @since 1.0.0
     * @return void
     */
    public function preload_critical_classes() {
        $critical_classes = array(
            'Cdswerx',
            'Cdswerx_Loader',
            'Cdswerx_Database',
            'Cdswerx_Admin',
            'Cdswerx_Public'
        );
        
        foreach ($critical_classes as $class_name) {
            if (!class_exists($class_name)) {
                $this->load_class($class_name);
            }
        }
    }
}