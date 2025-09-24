# API Reference

## Overview

CDSWerx Plugin provides a comprehensive API for developers to interact with the custom code management system programmatically. This reference covers all available classes, methods, hooks, and filters.

## Core Classes

### CDSWerx_Code_Manager

The main class for managing custom code operations.

#### Methods

##### `get_codes($type = 'all', $context = array())`

Retrieve custom codes with optional filtering.

```php
// Get all CSS codes
$css_codes = $code_manager->get_codes('css');

// Get codes for specific context
$context = array(
    'device' => 'mobile',
    'page_id' => 123
);
$filtered_codes = $code_manager->get_codes('all', $context);
```

**Parameters:**
- `$type` (string): Code type - 'css', 'js', or 'all'
- `$context` (array): Context for smart loading

**Returns:** Array of code objects

##### `save_code($data)`

Save new or update existing custom code.

```php
$code_data = array(
    'code_name' => 'Header Styling',
    'code_type' => 'css',
    'code_content' => '.header { background: #fff; }',
    'is_active' => 1,
    'loading_conditions' => array(
        'device' => array('desktop', 'tablet'),
        'pages' => array(1, 2, 3)
    )
);

$result = $code_manager->save_code($code_data);
```

**Parameters:**
- `$data` (array): Code data including name, type, content, and conditions

**Returns:** Integer - Code ID on success, false on failure

##### `delete_code($code_id)`

Delete custom code by ID.

```php
$success = $code_manager->delete_code(123);
```

**Parameters:**
- `$code_id` (int): Code ID to delete

**Returns:** Boolean - Success status

##### `get_code_history($code_id, $limit = 10)`

Retrieve version history for specific code.

```php
$history = $code_manager->get_code_history(123, 5);
```

### CDSWerx_Code_Injector

Handles code injection into frontend pages.

#### Methods

##### `inject_header_css()`

Inject CSS code into page head.

```php
// Automatically called by WordPress hooks
add_action('wp_head', array($injector, 'inject_header_css'), 10);
```

##### `inject_footer_js()`

Inject JavaScript code before closing body tag.

```php
// Automatically called by WordPress hooks
add_action('wp_footer', array($injector, 'inject_footer_js'), 10);
```

##### `should_load_code($code, $context)`

Determine if code should load in current context.

```php
$should_load = $injector->should_load_code($code_object, $current_context);
```

### CDSWerx_Import_Export_Manager

Manage import and export operations.

#### Methods

##### `export_codes($options = array())`

Export custom codes to file.

```php
$export_options = array(
    'format' => 'json',
    'code_type' => 'all',
    'include_inactive' => false
);

$export_data = $export_manager->export_codes($export_options);
```

##### `import_codes($file_data, $options = array())`

Import custom codes from file data.

```php
$import_options = array(
    'overwrite_existing' => false,
    'create_backup' => true
);

$result = $export_manager->import_codes($file_content, $import_options);
```

## WordPress Hooks

### Actions

#### `cdswerx_code_saved`

Fired when custom code is saved.

```php
add_action('cdswerx_code_saved', function($code_id, $code_data) {
    // Your custom logic here
    error_log("Code {$code_id} was saved");
}, 10, 2);
```

**Parameters:**
- `$code_id` (int): The saved code ID
- `$code_data` (array): The complete code data

#### `cdswerx_code_deleted`

Fired when custom code is deleted.

```php
add_action('cdswerx_code_deleted', function($code_id) {
    // Cleanup related data
    delete_option("custom_code_{$code_id}_cache");
}, 10, 1);
```

#### `cdswerx_before_inject_css`

Fired before CSS injection.

```php
add_action('cdswerx_before_inject_css', function($codes) {
    // Modify or log CSS codes before injection
}, 10, 1);
```

#### `cdswerx_before_inject_js`

Fired before JavaScript injection.

```php
add_action('cdswerx_before_inject_js', function($codes) {
    // Modify or log JS codes before injection
}, 10, 1);
```

### Filters

#### `cdswerx_css_output`

Filter CSS output before injection.

```php
add_filter('cdswerx_css_output', function($css_content, $codes) {
    // Modify CSS content
    return $css_content . "\n/* Added by custom filter */";
}, 10, 2);
```

**Parameters:**
- `$css_content` (string): Combined CSS content
- `$codes` (array): Array of CSS code objects

#### `cdswerx_js_output`

Filter JavaScript output before injection.

```php
add_filter('cdswerx_js_output', function($js_content, $codes) {
    // Add custom JavaScript
    return $js_content . "\nconsole.log('CDSWerx loaded');";
}, 10, 2);
```

#### `cdswerx_loading_context`

Filter the loading context used for smart loading.

```php
add_filter('cdswerx_loading_context', function($context) {
    // Add custom context data
    $context['custom_condition'] = check_custom_condition();
    return $context;
});
```

#### `cdswerx_should_load_code`

Filter whether specific code should load.

```php
add_filter('cdswerx_should_load_code', function($should_load, $code, $context) {
    // Custom loading logic
    if ($code->code_name === 'Special Code') {
        return is_user_logged_in();
    }
    return $should_load;
}, 10, 3);
```

## Database Schema

### wp_cdswerx_custom_code

Main table for storing custom codes.

```sql
CREATE TABLE wp_cdswerx_custom_code (
    id int(11) NOT NULL AUTO_INCREMENT,
    code_name varchar(255) NOT NULL,
    code_type enum('css','js') NOT NULL,
    code_content longtext NOT NULL,
    is_active tinyint(1) DEFAULT 1,
    priority int(11) DEFAULT 10,
    loading_conditions text,
    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by bigint(20) UNSIGNED,
    PRIMARY KEY (id),
    KEY idx_type_active (code_type, is_active),
    KEY idx_priority (priority)
);
```

### wp_cdswerx_code_history

Version history for code changes.

```sql
CREATE TABLE wp_cdswerx_code_history (
    id int(11) NOT NULL AUTO_INCREMENT,
    code_id int(11) NOT NULL,
    code_content longtext NOT NULL,
    change_type enum('created','updated','deleted') NOT NULL,
    changed_at timestamp DEFAULT CURRENT_TIMESTAMP,
    changed_by bigint(20) UNSIGNED,
    change_note text,
    PRIMARY KEY (id),
    KEY idx_code_id (code_id),
    FOREIGN KEY (code_id) REFERENCES wp_cdswerx_custom_code(id) ON DELETE CASCADE
);
```

## Helper Functions

### `cdswerx_get_code_manager()`

Get the global code manager instance.

```php
$code_manager = cdswerx_get_code_manager();
$codes = $code_manager->get_codes('css');
```

### `cdswerx_add_css($name, $content, $conditions = array())`

Programmatically add CSS code.

```php
cdswerx_add_css(
    'Custom Button Style',
    '.btn-custom { background: #007cba; color: white; }',
    array('pages' => array(1, 2, 3))
);
```

### `cdswerx_add_js($name, $content, $conditions = array())`

Programmatically add JavaScript code.

```php
cdswerx_add_js(
    'Analytics Tracking',
    'gtag("event", "page_view");',
    array('user_roles' => array('customer'))
);
```

### `cdswerx_is_code_active($code_id)`

Check if specific code is active.

```php
if (cdswerx_is_code_active(123)) {
    // Code is active
}
```

## REST API Endpoints

### GET `/wp-json/cdswerx/v1/codes`

Retrieve custom codes.

```javascript
fetch('/wp-json/cdswerx/v1/codes?type=css&active=1')
    .then(response => response.json())
    .then(data => console.log(data));
```

**Parameters:**
- `type` (string): Filter by code type
- `active` (boolean): Filter by active status
- `search` (string): Search in code names

### POST `/wp-json/cdswerx/v1/codes`

Create new custom code.

```javascript
fetch('/wp-json/cdswerx/v1/codes', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': wpApiSettings.nonce
    },
    body: JSON.stringify({
        code_name: 'New CSS',
        code_type: 'css',
        code_content: '.new-style { color: red; }'
    })
});
```

### PUT `/wp-json/cdswerx/v1/codes/{id}`

Update existing custom code.

### DELETE `/wp-json/cdswerx/v1/codes/{id}`

Delete custom code.

## Error Handling

### Exception Classes

#### `CDSWerx_Code_Exception`

Base exception class for code-related errors.

```php
try {
    $code_manager->save_code($invalid_data);
} catch (CDSWerx_Code_Exception $e) {
    error_log('CDSWerx Error: ' . $e->getMessage());
}
```

#### `CDSWerx_Validation_Exception`

Thrown when code validation fails.

#### `CDSWerx_Permission_Exception`

Thrown when user lacks required permissions.

## Security Considerations

### Capability Checks

All API methods respect WordPress capabilities:

- `cdswerx_manage_code`: Create, edit, delete codes
- `cdswerx_edit_settings`: Modify plugin settings
- `cdswerx_import_export`: Import/export functionality

### Data Sanitization

All input is automatically sanitized:

```php
// CSS content sanitization
$sanitized_css = wp_strip_all_tags($css_content);

// JavaScript validation
if (!cdswerx_validate_js_syntax($js_content)) {
    throw new CDSWerx_Validation_Exception('Invalid JavaScript syntax');
}
```

### Nonce Verification

All AJAX and form submissions require valid nonces:

```php
if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_save_code')) {
    wp_die('Security check failed');
}
```

---

For more information:
- [Configuration Guide](configuration.md)
- [Installation Guide](installation.md)
- [Troubleshooting](troubleshooting.md)
- [GitHub Repository](https://github.com/cdswerx/cdswerx-plugin)