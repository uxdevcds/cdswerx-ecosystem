# CDSWerx Plugin Test Suite

**Created:** September 25, 2025  
**Phase:** 2 - Architectural Improvements  
**Purpose:** Comprehensive test coverage for safe architectural optimization  
**Test Framework:** PHPUnit with WordPress Test Suite

---

## **🧪 TEST SUITE ARCHITECTURE**

### **Test Structure**
```
tests/
├── unit/           # Isolated class and method testing
├── integration/    # WordPress integration testing  
├── bootstrap.php   # Test environment setup
├── phpunit.xml     # PHPUnit configuration
└── README.md       # Test documentation (this file)
```

### **Coverage Requirements**
- **Core Plugin Functionality**: 95% code coverage minimum
- **WordPress Integration**: All hooks and filters tested
- **Database Operations**: CRUD operations with rollback
- **Admin Interface**: UI components and AJAX handlers
- **Theme Integration**: Cross-component coordination

---

## **📋 TEST CATEGORIES**

### **1. CORE PLUGIN TESTS** 
**Location:** `tests/unit/core/`
**Purpose:** Test fundamental plugin architecture

#### **Test Files:**
- `test-cdswerx-main.php` - Main plugin class functionality
- `test-cdswerx-loader.php` - Hook registration system
- `test-cdswerx-activator.php` - Plugin activation process
- `test-cdswerx-deactivator.php` - Plugin deactivation process
- `test-cdswerx-i18n.php` - Internationalization features

#### **Critical Tests:**
```php
// Plugin initialization
public function test_plugin_initialization() {
    $this->assertTrue(class_exists('Cdswerx'));
    $this->assertNotEmpty(CDSWERX_VERSION);
}

// Dependency loading
public function test_load_dependencies() {
    $plugin = new Cdswerx();
    $this->assertTrue(class_exists('Cdswerx_Loader'));
    $this->assertTrue(class_exists('Cdswerx_Admin'));
}
```

### **2. DATABASE TESTS**
**Location:** `tests/unit/database/`
**Purpose:** Ensure data integrity and operations

#### **Test Files:**
- `test-cdswerx-database.php` - Database operations
- `test-version-management.php` - Version tracking
- `test-settings-storage.php` - Options and settings

#### **Critical Tests:**
```php
// Database schema
public function test_database_schema_creation() {
    $db = new Cdswerx_Database();
    $this->assertTrue($db->create_tables());
}

// Version management
public function test_version_storage_retrieval() {
    $version_manager = new Version_Manager();
    $version_manager->store_version('1.0.0');
    $this->assertEquals('1.0.0', $version_manager->get_current_version());
}
```

### **3. ADMIN INTERFACE TESTS**
**Location:** `tests/unit/admin/`
**Purpose:** Verify admin functionality and UI components

#### **Test Files:**
- `test-cdswerx-admin.php` - Admin class functionality
- `test-admin-dashboard.php` - Dashboard interface
- `test-admin-ajax.php` - AJAX handlers
- `test-user-roles.php` - User capability management

#### **Critical Tests:**
```php
// Admin interface loading
public function test_admin_interface_loads() {
    $admin = new Cdswerx_Admin();
    $this->assertTrue(method_exists($admin, 'enqueue_styles'));
    $this->assertTrue(method_exists($admin, 'enqueue_scripts'));
}

// User capabilities
public function test_user_capabilities() {
    $user_roles = new Cdswerx_User_Roles();
    $this->assertTrue($user_roles->user_can_access_settings());
}
```

### **4. CODE INJECTION TESTS**
**Location:** `tests/unit/code-injection/`
**Purpose:** Test custom code and CSS injection systems

#### **Test Files:**
- `test-code-manager.php` - Code management system
- `test-code-injector.php` - Code injection mechanisms
- `test-css-management.php` - CSS handling

#### **Critical Tests:**
```php
// Code injection safety
public function test_code_injection_sanitization() {
    $injector = new Cdswerx_Code_Injector();
    $safe_code = $injector->sanitize_code('<script>alert("test")</script>');
    $this->assertStringNotContainsString('<script>', $safe_code);
}
```

### **5. THEME INTEGRATION TESTS**
**Location:** `tests/integration/theme/`
**Purpose:** Test coordination with CDSWerx themes

#### **Test Files:**
- `test-theme-integration.php` - Theme coordination
- `test-hello-elementor-sync.php` - External theme compatibility
- `test-asset-coordination.php` - Asset loading coordination

#### **Critical Tests:**
```php
// Theme detection
public function test_theme_detection() {
    $integration = new Theme_Integration();
    $this->assertTrue(method_exists($integration, 'detect_active_theme'));
}
```

### **6. TYPOGRAPHY SYSTEM TESTS**
**Location:** `tests/unit/typography/`
**Purpose:** Test typography management system

#### **Test Files:**
- `test-typography-sync.php` - Typography synchronization
- `test-typography-css-reader.php` - CSS reading functionality

### **7. HELLO ELEMENTOR SYNC TESTS**
**Location:** `tests/integration/hello-elementor/`
**Purpose:** Test external theme compatibility system

#### **Test Files:**
- `test-hello-elementor-sync.php` - Main sync functionality
- `test-sync-validator.php` - Sync validation
- `test-independent-mode.php` - Independent operation mode

---

## **🔧 TEST ENVIRONMENT SETUP**

### **Bootstrap Requirements**
**File:** `tests/bootstrap.php`

```php
<?php
// WordPress test environment setup
$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir) {
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
    require dirname(dirname(__FILE__)) . '/cdswerx.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

require $_tests_dir . '/includes/bootstrap.php';
```

### **PHPUnit Configuration**
**File:** `tests/phpunit.xml`

```xml
<phpunit bootstrap="bootstrap.php">
    <testsuites>
        <testsuite name="CDSWerx Plugin Test Suite">
            <directory>./unit/</directory>
            <directory>./integration/</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist>
            <directory suffix=".php">../includes/</directory>
            <directory suffix=".php">../admin/</directory>
            <directory suffix=".php">../public/</directory>
        </whitelist>
    </filter>
</phpunit>
```

---

## **⚡ AUTOMATED TESTING WORKFLOW**

### **Continuous Integration**
```bash
# Install dependencies
composer install

# Run test suite
./vendor/bin/phpunit

# Generate coverage report
./vendor/bin/phpunit --coverage-html coverage/
```

### **Pre-Deployment Testing**
```bash
# Quick test suite (unit tests only)
./vendor/bin/phpunit tests/unit/

# Full integration testing
./vendor/bin/phpunit tests/

# Performance benchmarking
php tests/performance/benchmark.php
```

---

## **📊 SUCCESS CRITERIA**

### **Phase 2 Testing Requirements**
- ✅ **95% Code Coverage** - All critical paths tested
- ✅ **Zero Test Failures** - All tests must pass
- ✅ **Performance Baseline** - Current performance documented
- ✅ **Integration Verified** - WordPress integration confirmed
- ✅ **Database Integrity** - All CRUD operations tested

### **Safety Validation**
- ✅ **Plugin Activation** - No fatal errors during activation
- ✅ **Deactivation Clean** - Proper cleanup on deactivation
- ✅ **Multisite Compatible** - Functions in network environment
- ✅ **Theme Independence** - Works with or without CDSWerx themes
- ✅ **Data Preservation** - No data loss during operations

---

## **🚨 TESTING PROTOCOLS**

### **Before Architectural Changes**
1. **Full Test Suite Execution** - Establish baseline
2. **Performance Benchmarking** - Document current metrics
3. **Database Backup** - Ensure data safety
4. **Functionality Verification** - Confirm all features work

### **After Each Change**
1. **Regression Testing** - Ensure no functionality lost
2. **Integration Testing** - Verify component coordination
3. **Performance Comparison** - Measure improvements
4. **Error Log Monitoring** - Check for new issues

### **Phase Completion Criteria**
1. **All Tests Pass** - 100% success rate required
2. **Coverage Maintained** - 95% minimum coverage
3. **Performance Improved** - Measurable optimization
4. **Documentation Updated** - Changes fully documented

---

## **📚 REFERENCE DOCUMENTATION**

**Related Files:**
- Main Plugin: `/cdswerx-ecosystem/packages/cdswerx-plugin/cdswerx.php`
- Phase 2 Plan: `/cdswerx-ecosystem/docs/development/PHASE_2_DEPENDENCY_MAPPING.md`
- TODO Tracking: `/CDSWERX_CONSOLIDATED_TODO_TRACKING.md`

**WordPress Testing:**
- WordPress Test Suite: https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/
- Plugin Testing Best Practices: https://developer.wordpress.org/plugins/testing/
- PHPUnit Documentation: https://phpunit.de/documentation.html

**Next Steps:**
1. Implement test suite files
2. Configure test environment
3. Execute baseline testing
4. Proceed with monolithic class optimization