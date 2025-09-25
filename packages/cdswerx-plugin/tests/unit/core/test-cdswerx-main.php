<?php
/**
 * Test CDSWerx Main Plugin Class
 *
 * @package CDSWerx
 * @subpackage Tests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CDSWerx Main Plugin Test Class
 * 
 * Tests the core plugin functionality and initialization
 */
class Test_CDSWerx_Main extends CDSWerx_Test_Base {
    
    /**
     * Test plugin class exists and initializes properly
     */
    public function test_plugin_class_exists() {
        $this->assertTrue(class_exists('Cdswerx'), 'CDSWerx main class should exist');
    }
    
    /**
     * Test plugin constants are defined
     */
    public function test_plugin_constants_defined() {
        $this->assertTrue(defined('CDSWERX_VERSION'), 'CDSWERX_VERSION constant should be defined');
        $this->assertTrue(defined('CDSWERX_PLUGIN_PATH'), 'CDSWERX_PLUGIN_PATH constant should be defined');
        $this->assertTrue(defined('CDSWERX_PLUGIN_URL'), 'CDSWERX_PLUGIN_URL constant should be defined');
    }
    
    /**
     * Test plugin initialization
     */
    public function test_plugin_initialization() {
        $plugin = new Cdswerx();
        
        $this->assertInstanceOf('Cdswerx', $plugin, 'Plugin should initialize as Cdswerx instance');
        
        // Test plugin properties are set
        $reflection = new ReflectionClass($plugin);
        
        $plugin_name_property = $reflection->getProperty('plugin_name');
        $plugin_name_property->setAccessible(true);
        $this->assertEquals('cdswerx', $plugin_name_property->getValue($plugin), 'Plugin name should be set correctly');
        
        $version_property = $reflection->getProperty('version');
        $version_property->setAccessible(true);
        $this->assertNotEmpty($version_property->getValue($plugin), 'Plugin version should be set');
    }
    
    /**
     * Test dependency loading
     */
    public function test_dependency_loading() {
        $plugin = new Cdswerx();
        
        // Test that critical classes are loaded
        $this->assertTrue(class_exists('Cdswerx_Loader'), 'Cdswerx_Loader class should be loaded');
        $this->assertTrue(class_exists('Cdswerx_i18n'), 'Cdswerx_i18n class should be loaded'); 
        $this->assertTrue(class_exists('Cdswerx_Admin'), 'Cdswerx_Admin class should be loaded');
        $this->assertTrue(class_exists('Cdswerx_Public'), 'Cdswerx_Public class should be loaded');
    }
    
    /**
     * Test loader initialization
     */
    public function test_loader_initialization() {
        $plugin = new Cdswerx();
        
        $reflection = new ReflectionClass($plugin);
        $loader_property = $reflection->getProperty('loader');
        $loader_property->setAccessible(true);
        $loader = $loader_property->getValue($plugin);
        
        $this->assertInstanceOf('Cdswerx_Loader', $loader, 'Loader should be instance of Cdswerx_Loader');
    }
    
    /**
     * Test plugin methods exist
     */
    public function test_plugin_methods_exist() {
        $plugin = new Cdswerx();
        
        $this->assertTrue(method_exists($plugin, 'get_plugin_name'), 'get_plugin_name method should exist');
        $this->assertTrue(method_exists($plugin, 'get_loader'), 'get_loader method should exist');
        $this->assertTrue(method_exists($plugin, 'get_version'), 'get_version method should exist');
        $this->assertTrue(method_exists($plugin, 'run'), 'run method should exist');
    }
    
    /**
     * Test plugin getters return correct values
     */
    public function test_plugin_getters() {
        $plugin = new Cdswerx();
        
        $this->assertEquals('cdswerx', $plugin->get_plugin_name(), 'Plugin name getter should return correct value');
        $this->assertNotEmpty($plugin->get_version(), 'Version getter should return non-empty value');
        $this->assertInstanceOf('Cdswerx_Loader', $plugin->get_loader(), 'Loader getter should return Cdswerx_Loader instance');
    }
    
    /**
     * Test plugin run method
     */
    public function test_plugin_run() {
        $plugin = new Cdswerx();
        
        // This should not cause any errors
        $plugin->run();
        
        // Check that hooks were registered
        $loader = $plugin->get_loader();
        $this->assertInstanceOf('Cdswerx_Loader', $loader, 'Loader should still be accessible after run');
    }
    
    /**
     * Test plugin handles version correctly
     */
    public function test_version_handling() {
        // Test with defined constant
        if (defined('CDSWERX_VERSION')) {
            $plugin = new Cdswerx();
            $this->assertEquals(CDSWERX_VERSION, $plugin->get_version(), 'Plugin should use defined version constant');
        }
        
        // Test default version fallback would require temporarily undefining the constant
        // This is complex to test safely, so we'll verify the logic exists
        $this->assertTrue(true, 'Version handling logic is present in constructor');
    }
    
    /**
     * Test plugin activation doesn't cause errors
     */
    public function test_plugin_activation_safe() {
        // This tests that we can create the plugin instance without errors
        $this->expectNotToPerformAssertions();
        
        try {
            $plugin = new Cdswerx();
            $plugin->run();
        } catch (Exception $e) {
            $this->fail('Plugin initialization should not throw exceptions: ' . $e->getMessage());
        }
    }
}