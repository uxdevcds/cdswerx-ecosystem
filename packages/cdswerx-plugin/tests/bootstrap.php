<?php
/**
 * CDSWerx Plugin Test Bootstrap
 *
 * @package CDSWerx
 * @subpackage Tests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WordPress test environment setup
 */
$_tests_dir = getenv('WP_TESTS_DIR');

if (!$_tests_dir) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

// Require WordPress test functions
require_once $_tests_dir . '/includes/functions.php';

/**
 * Load CDSWerx plugin for testing
 */
function _manually_load_cdswerx_plugin() {
    require dirname(dirname(__FILE__)) . '/cdswerx.php';
}

// Hook plugin loading into WordPress test initialization
tests_add_filter('muplugins_loaded', '_manually_load_cdswerx_plugin');

// Start WordPress test environment
require $_tests_dir . '/includes/bootstrap.php';

/**
 * CDSWerx Test Base Class
 * 
 * Provides common functionality for all CDSWerx tests
 */
class CDSWerx_Test_Base extends WP_UnitTestCase {
    
    /**
     * Setup before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Reset plugin state
        $this->reset_plugin_state();
        
        // Create test user with appropriate capabilities
        $this->create_test_user();
    }
    
    /**
     * Cleanup after each test
     */
    public function tearDown(): void {
        // Clean up test data
        $this->cleanup_test_data();
        
        parent::tearDown();
    }
    
    /**
     * Reset plugin to clean state
     */
    protected function reset_plugin_state() {
        // Clear plugin options
        delete_option('cdswerx_settings');
        delete_option('cdswerx_version');
        
        // Clear transients
        delete_transient('cdswerx_cache');
    }
    
    /**
     * Create test user with CDSWerx capabilities
     */
    protected function create_test_user() {
        $this->test_user = $this->factory->user->create(array(
            'role' => 'administrator'
        ));
        
        wp_set_current_user($this->test_user);
    }
    
    /**
     * Clean up test data
     */
    protected function cleanup_test_data() {
        // Remove test user
        if (isset($this->test_user)) {
            wp_delete_user($this->test_user);
        }
        
        // Clean up database
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'cdswerx_test_%'");
    }
    
    /**
     * Assert that plugin is properly loaded
     */
    protected function assertPluginLoaded() {
        $this->assertTrue(class_exists('Cdswerx'), 'CDSWerx main class should exist');
        $this->assertTrue(defined('CDSWERX_VERSION'), 'CDSWerx version should be defined');
        $this->assertTrue(defined('CDSWERX_PLUGIN_PATH'), 'CDSWerx plugin path should be defined');
    }
    
    /**
     * Assert that database tables exist
     */
    protected function assertDatabaseTablesExist() {
        global $wpdb;
        
        // Check for CDSWerx tables (if any exist)
        $tables = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}cdswerx_%'");
        
        // Add specific table assertions as needed
        // $this->assertNotEmpty($tables, 'CDSWerx database tables should exist');
    }
    
    /**
     * Create mock settings for testing
     */
    protected function createMockSettings() {
        return array(
            'advanced_css' => '.test-class { color: red; }',
            'custom_code' => '<!-- Test comment -->',
            'version' => '1.0.0',
            'sync_enabled' => true
        );
    }
}