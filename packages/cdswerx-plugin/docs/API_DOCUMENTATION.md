# CDSWerx API Documentation & Integration Guide

## **Table of Contents**

1. [Plugin API Documentation](#plugin-api-documentation)
2. [Theme Integration API](#theme-integration-api) 
3. [GitHub Update System API](#github-update-system-api)
4. [Performance Optimization API](#performance-optimization-api)
5. [Testing & QA API](#testing--qa-api)
6. [Hook Reference](#hook-reference)
7. [Filter Reference](#filter-reference)
8. [Integration Examples](#integration-examples)

---

## **Plugin API Documentation**

### **CDSWerx Core Class**

```php
class Cdswerx {
    
    /**
     * Get plugin instance
     * @return Cdswerx Single instance of plugin
     */
    public static function get_instance();
    
    /**
     * Get plugin version
     * @return string Current plugin version
     */
    public function get_version();
    
    /**
     * Check if plugin is network active
     * @return bool True if network active
     */
    public function is_network_active();
}
```

### **Admin Manager API**

```php
class CDSWerx_Admin_Manager {
    
    /**
     * Register admin menu item
     * @param string $slug Menu slug
     * @param string $title Menu title
     * @param callable $callback Page callback
     * @param string $capability Required capability
     */
    public function register_menu_item($slug, $title, $callback, $capability = 'manage_options');
    
    /**
     * Add admin notice
     * @param string $message Notice message
     * @param string $type Notice type (success, error, warning, info)
     */
    public function add_admin_notice($message, $type = 'info');
    
    /**
     * Enqueue admin assets
     * @param string $handle Asset handle
     * @param string $src Asset source URL
     * @param array $deps Dependencies
     */
    public function enqueue_admin_asset($handle, $src, $deps = []);
}
```

---

## **Theme Integration API**

### **CDSWerx Theme Functions**

```php
/**
 * Check if CDSWerx plugin is active
 * @return bool True if plugin is active
 */
function cdswerx_plugin_active();

/**
 * Get CDSWerx theme version
 * @return string Theme version
 */
function cdswerx_theme_version();

/**
 * Enqueue CDSWerx theme assets
 * @param string $handle Asset handle
 * @param string $file Asset file name
 * @param array $deps Dependencies
 */
function cdswerx_enqueue_theme_asset($handle, $file, $deps = []);
```

### **Child Theme Integration**

```php
/**
 * Register child theme assets
 * @param array $assets Array of asset configurations
 */
function cdswerx_child_register_assets($assets);

/**
 * Get child theme asset URL
 * @param string $file Asset file path
 * @return string Full asset URL
 */
function cdswerx_child_asset_url($file);

/**
 * Check if parent theme is CDSWerx
 * @return bool True if CDSWerx is parent theme
 */
function is_cdswerx_child_theme();
```

---

## **GitHub Update System API**

### **CDSWerx_GitHub_Update_Manager**

```php
class CDSWerx_GitHub_Update_Manager {
    
    /**
     * Check for available updates
     * @param string $component Component to check (plugin, theme, child-theme)
     * @return array Update information
     */
    public function check_for_updates($component);
    
    /**
     * Download and install update
     * @param string $component Component to update
     * @param string $version Target version
     * @return bool Success status
     */
    public function install_update($component, $version);
    
    /**
     * Create backup before update
     * @param string $component Component to backup
     * @return string Backup file path
     */
    public function create_backup($component);
    
    /**
     * Rollback to previous version
     * @param string $component Component to rollback
     * @param string $backup_path Backup file path
     * @return bool Success status
     */
    public function rollback_update($component, $backup_path);
}
```

### **GitHub API Integration**

```php
/**
 * Get GitHub release information
 * @param string $repo Repository name (owner/repo)
 * @param string $token GitHub access token
 * @return array Release data
 */
function cdswerx_get_github_releases($repo, $token);

/**
 * Download GitHub release asset
 * @param string $asset_url Asset download URL
 * @param string $token GitHub access token
 * @return string Downloaded file path
 */
function cdswerx_download_github_asset($asset_url, $token);
```

---

## **Performance Optimization API**

### **CDSWerx_Performance_Optimization**

```php
class CDSWerx_Performance_Optimization {
    
    /**
     * Enable caching for component
     * @param string $component Component name
     * @param array $cache_config Cache configuration
     */
    public function enable_caching($component, $cache_config);
    
    /**
     * Minify and concatenate assets
     * @param array $assets List of asset files
     * @param string $output_file Output file path
     * @return bool Success status
     */
    public function minify_assets($assets, $output_file);
    
    /**
     * Enable lazy loading for elements
     * @param array $selectors CSS selectors to lazy load
     */
    public function enable_lazy_loading($selectors);
    
    /**
     * Clear all caches
     * @param string $component Specific component or 'all'
     */
    public function clear_cache($component = 'all');
}
```

### **Database Optimization**

```php
/**
 * Optimize database queries
 * @param string $query SQL query to optimize
 * @return string Optimized query
 */
function cdswerx_optimize_query($query);

/**
 * Cache query results
 * @param string $cache_key Cache key
 * @param mixed $data Data to cache
 * @param int $expiration Cache expiration time
 */
function cdswerx_cache_query_result($cache_key, $data, $expiration = 3600);
```

---

## **Testing & QA API**

### **CDSWerx_Testing_QA_Manager**

```php
class CDSWerx_Testing_QA_Manager {
    
    /**
     * Run specific test suite
     * @param string $suite Test suite name
     * @return array Test results
     */
    public function run_test_suite($suite);
    
    /**
     * Register custom test
     * @param string $name Test name
     * @param callable $callback Test callback
     * @param string $suite Test suite
     */
    public function register_test($name, $callback, $suite = 'custom');
    
    /**
     * Get test results
     * @param string $suite Specific suite or 'all'
     * @return array Test results
     */
    public function get_test_results($suite = 'all');
}
```

---

## **Hook Reference**

### **Action Hooks**

```php
/**
 * Fired after CDSWerx plugin initialization
 * @param CDSWerx $plugin Plugin instance
 */
do_action('cdswerx_plugin_loaded', $plugin);

/**
 * Fired before CSS migration process
 * @param array $css_files Files to migrate
 */
do_action('cdswerx_before_css_migration', $css_files);

/**
 * Fired after CSS migration completion
 * @param array $migrated_files Successfully migrated files
 */
do_action('cdswerx_after_css_migration', $migrated_files);

/**
 * Fired when Advanced CSS is updated
 * @param string $version New version number
 */
do_action('cdswerx_advanced_css_updated', $version);

/**
 * Fired before GitHub update check
 * @param string $component Component being checked
 */
do_action('cdswerx_before_update_check', $component);

/**
 * Fired after successful update installation
 * @param string $component Updated component
 * @param string $version New version
 */
do_action('cdswerx_update_installed', $component, $version);

/**
 * Fired before running test suites
 */
do_action('cdswerx_run_tests');

/**
 * Fired when performance optimization is enabled
 * @param array $optimization_config Configuration settings
 */
do_action('cdswerx_performance_optimized', $optimization_config);
```

### **Filter Hooks**

```php
/**
 * Filter plugin configuration
 * @param array $config Default configuration
 * @return array Modified configuration
 */
$config = apply_filters('cdswerx_plugin_config', $config);

/**
 * Filter CSS migration files
 * @param array $files Files to migrate
 * @return array Filtered file list
 */
$files = apply_filters('cdswerx_css_migration_files', $files);

/**
 * Filter GitHub repositories for updates
 * @param array $repos Repository configurations
 * @return array Modified repository list
 */
$repos = apply_filters('cdswerx_github_repositories', $repos);

/**
 * Filter admin menu items
 * @param array $menu_items Menu item configurations
 * @return array Modified menu items
 */
$menu_items = apply_filters('cdswerx_admin_menu_items', $menu_items);

/**
 * Filter test suites
 * @param array $test_suites Available test suites
 * @return array Modified test suites
 */
$test_suites = apply_filters('cdswerx_test_suites', $test_suites);

/**
 * Filter performance optimization settings
 * @param array $settings Optimization settings
 * @return array Modified settings
 */
$settings = apply_filters('cdswerx_performance_settings', $settings);

/**
 * Filter asset loading order
 * @param array $assets Asset loading order
 * @return array Modified asset order
 */
$assets = apply_filters('cdswerx_asset_loading_order', $assets);
```

---

## **Integration Examples**

### **Plugin Integration Example**

```php
// Add custom functionality to CDSWerx
add_action('cdswerx_plugin_loaded', function($plugin) {
    // Your custom initialization code
    if (class_exists('My_Custom_Extension')) {
        new My_Custom_Extension($plugin);
    }
});

// Modify CSS migration process
add_filter('cdswerx_css_migration_files', function($files) {
    // Add custom CSS files to migration
    $files[] = 'path/to/custom-styles.css';
    return $files;
});
```

### **Theme Integration Example**

```php
// In your theme's functions.php
function my_theme_cdswerx_integration() {
    if (cdswerx_plugin_active()) {
        // Load CDSWerx-specific theme features
        add_theme_support('cdswerx-enhanced-features');
        
        // Enqueue coordinated assets
        cdswerx_enqueue_theme_asset('my-theme-cdswerx', 'cdswerx-integration.css');
    }
}
add_action('after_setup_theme', 'my_theme_cdswerx_integration');
```

### **Child Theme Integration Example**

```php
// Register custom child theme assets
add_action('wp_enqueue_scripts', function() {
    if (is_cdswerx_child_theme()) {
        cdswerx_child_register_assets([
            'custom-child-styles' => [
                'file' => 'custom-styles.css',
                'deps' => ['cdswerx-parent-styles']
            ]
        ]);
    }
});
```

### **Custom Testing Example**

```php
// Register custom test
add_action('init', function() {
    if (class_exists('CDSWerx_Testing_QA_Manager')) {
        $testing_manager = new CDSWerx_Testing_QA_Manager();
        
        $testing_manager->register_test(
            'my_custom_test',
            'run_my_custom_test',
            'integration'
        );
    }
});

function run_my_custom_test() {
    // Your custom test logic
    return [
        'name' => 'My Custom Test',
        'status' => 'passed',
        'message' => 'Custom functionality working correctly'
    ];
}
```

### **Performance Optimization Example**

```php
// Enable custom caching
add_action('cdswerx_performance_optimized', function($config) {
    if (class_exists('CDSWerx_Performance_Optimization')) {
        $performance = new CDSWerx_Performance_Optimization();
        
        // Enable caching for custom component
        $performance->enable_caching('my_component', [
            'cache_duration' => 3600,
            'cache_key_prefix' => 'my_component_'
        ]);
    }
});
```

### **GitHub Update Integration Example**

```php
// Add custom repository to update system
add_filter('cdswerx_github_repositories', function($repos) {
    $repos['my-custom-plugin'] = [
        'repo' => 'myusername/my-custom-plugin',
        'file' => 'my-custom-plugin/my-custom-plugin.php',
        'type' => 'plugin'
    ];
    return $repos;
});

// Handle custom update notifications
add_action('cdswerx_update_installed', function($component, $version) {
    if ($component === 'my-custom-plugin') {
        // Custom post-update actions
        flush_rewrite_rules();
        wp_cache_flush();
    }
});
```

---

## **Error Handling & Debugging**

### **Debug Mode**

```php
// Enable debug mode
define('CDSWERX_DEBUG', true);

// Check if debug mode is enabled
if (defined('CDSWERX_DEBUG') && CDSWERX_DEBUG) {
    // Debug-specific code
}
```

### **Logging**

```php
// Log debug messages
cdswerx_log('Debug message', 'info');
cdswerx_log('Error occurred', 'error');
cdswerx_log(['data' => $data], 'debug');
```

### **Error Recovery**

```php
// Implement error recovery
try {
    // Risky operation
    $result = cdswerx_risky_operation();
} catch (Exception $e) {
    // Log error and recover
    cdswerx_log('Operation failed: ' . $e->getMessage(), 'error');
    $result = cdswerx_fallback_operation();
}
```

---

This API documentation provides comprehensive guidance for integrating with and extending the CDSWerx ecosystem. For additional support or questions, refer to the [CDSWerx Documentation Hub](../README.md).