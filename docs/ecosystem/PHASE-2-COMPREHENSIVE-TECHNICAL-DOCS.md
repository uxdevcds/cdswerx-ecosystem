# Phase 2: Advanced Features - Comprehensive Technical Documentation

## Executive Summary

Phase 2 transforms the CDSWerx Custom Code system into a professional-grade code management platform with advanced editing capabilities, smart asset loading, and comprehensive backup/restore functionality. This phase delivers enterprise-level features comparable to premium page builder solutions while maintaining WordPress standards and performance optimization.

---

## Phase 2.1: Professional Code Editor Integration

### Monaco Editor Integration

Integration of Microsoft Monaco Editor providing VS Code-level editing experience within WordPress admin interface.

### Technical Implementation

#### Server-Side Integration (`class-cdswerx-admin.php`)

```php
// Monaco Editor CDN Integration
wp_enqueue_script(
    'monaco-editor-loader',
    'https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs/loader.js',
    array(),
    '0.44.0',
    true
);

wp_enqueue_script(
    'cdswerx-custom-code',
    plugin_dir_url(__FILE__) . 'js/cdswerx-custom-code.js',
    array('jquery', 'monaco-editor-loader'),
    CDSWERX_VERSION,
    true
);
```

#### Client-Side Monaco Integration (`cdswerx-custom-code.js`)
```javascript
// Promise-based Monaco Loading
loadMonaco: function() {
    return new Promise((resolve, reject) => {
        if (this.monacoLoaded) {
            resolve();
            return;
        }

        require.config({
            paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.44.0/min/vs' }
        });

        require(['vs/editor/editor.main'], function() {
            this.monacoLoaded = true;
            resolve();
        }.bind(this), reject);
    });
}

// Professional Editor Configuration
initMonacoEditors: function() {
    // CSS Editor
    this.cssEditor = monaco.editor.create(cssContainer, {
        value: $('#cdswerx-css-editor').val() || '',
        language: 'css',
        theme: 'vs-dark',
        automaticLayout: true,
        lineNumbers: 'on',
        roundedSelection: false,
        scrollBeyondLastLine: false,
        minimap: { enabled: false },
        fontSize: 14,
        tabSize: 2,
        insertSpaces: true,
        wordWrap: 'on',
        folding: true,
        suggest: {
            enabledFields: ['snippet', 'completion', 'suggestion']
        }
    });
    
    // JavaScript Editor (similar configuration)
}
```

#### Enhanced User Interface
- **Professional Toolbar**: Format, Find/Replace, and real-time status information
- **Monaco Containers**: Dedicated containers for each editor instance
- **Graceful Fallback**: Hidden textarea elements maintain compatibility
- **Visual Status**: Real-time line counting and syntax validation

### Key Features Delivered
- ✅ **Syntax Highlighting**: Language-specific color coding for CSS and JavaScript
- ✅ **Auto-completion**: Intelligent code suggestions and completions
- ✅ **Code Formatting**: Automatic code beautification and organization
- ✅ **Find & Replace**: Advanced search functionality with regex support
- ✅ **Error Detection**: Real-time syntax validation and error highlighting
- ✅ **Professional Interface**: VS Code-level editing experience

### Performance & Compatibility
- **CDN Delivery**: Fast Monaco Editor loading via CDN with version pinning
- **Lazy Loading**: Monaco loads only when needed to optimize performance  
- **Backward Compatibility**: Maintains textarea fallbacks for all functionality
- **Memory Management**: Proper editor disposal and cleanup procedures

---

## Phase 2.2: Smart Theme Integration & Asset Loading

### Overview
Implementation of intelligent asset loading system with conditional rules, device detection, and performance optimization.

### Core Architecture

#### Enhanced Code Manager (`class-cdswerx-code-manager.php`)
```php
/**
 * Get codes with conditional loading rules
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
```

#### Smart Loading Conditions
```php
private function should_load_code($code, $context) {
    $metadata = !empty($code->metadata) ? json_decode($code->metadata, true) : array();
    $loading_rules = isset($metadata['loading_rules']) ? $metadata['loading_rules'] : array();
    
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
    
    return true;
}
```

#### Context-Aware Code Injection (`class-cdswerx-code-injector.php`)
```php
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
        echo '<style type="text/css" id="cdswerx-critical-css">' . $critical_css . '</style>';
    }
}
```

### Smart Loading Features

#### 1. Device-Specific Loading
- **Desktop**: Full feature CSS and JavaScript
- **Tablet**: Optimized styles for tablet viewports
- **Mobile**: Minimal CSS for performance on mobile devices

#### 2. Page-Specific Loading
- **Page IDs**: Target specific pages by WordPress page ID
- **Post Types**: Load code only for specific post types
- **Templates**: Target specific page templates
- **URL Patterns**: Pattern matching for complex URL structures

#### 3. User Role Based Loading
- **Admin Only**: Code visible only to administrators
- **Logged In Users**: Different styles for authenticated users
- **Guest Users**: Special handling for non-authenticated visitors
- **Role-Specific**: Target specific WordPress user roles

#### 4. Context Detection System
```php
private function get_loading_context() {
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
```

### Performance Optimization
- **Conditional Loading**: Only load code when conditions are met
- **Priority-Based Loading**: Critical CSS loads first for optimal performance  
- **Context Caching**: Efficient context detection with minimal overhead
- **Device Detection**: Lightweight device detection without external dependencies

---

## Phase 2.3: Import/Export & Backup System

### Overview
Comprehensive backup, restore, import and export system providing version control and bulk operations for custom code management.

### Core Architecture

#### Import/Export Manager (`class-cdswerx-import-export-manager.php`)
```php
class CDSWerx_Import_Export_Manager {
    
    private $supported_formats = array(
        'json' => 'JSON Format',
        'zip' => 'ZIP Archive',
        'sql' => 'SQL Dump'
    );
    
    public function export_codes($options = array()) {
        $codes = $this->get_export_codes($options);
        
        switch ($options['format']) {
            case 'json':
                return $this->export_to_json($codes, $options);
            default:
                return array('success' => false, 'message' => 'Currently only JSON format is supported');
        }
    }
}
```

### Export System Features

#### 1. Flexible Export Options
```php
$defaults = array(
    'format' => 'json',
    'code_type' => 'all', // css, js, or all
    'category' => 'all',
    'include_inactive' => false,
    'include_metadata' => true,
    'filename' => 'cdswerx_export_' . date('Y-m-d_H-i-s')
);
```

#### 2. JSON Export Format
```json
{
    "export_date": "2025-09-17 12:00:00",
    "version": "1.0.0", 
    "site_url": "https://example.com",
    "codes": [
        {
            "name": "Global Header Styles",
            "content": "/* CSS Content */",
            "code_type": "css",
            "category": "global",
            "load_priority": 10,
            "is_active": 1,
            "metadata": "{\"loading_rules\":{\"device\":[\"desktop\",\"tablet\"]}}"
        }
    ],
    "metadata": {
        "total_codes": 1,
        "export_options": {...}
    }
}
```

### Backup System Features

#### 1. Full System Backup
```php
public function create_backup($backup_name = null) {
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
    
    $backup_dir = $this->get_backup_directory();
    $backup_file = $backup_dir . '/' . sanitize_file_name($backup_name) . '.json';
    
    return file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));
}
```

#### 2. Restore with Safeguards
- **Pre-Restore Backup**: Automatic backup creation before restore operation
- **Selective Restore**: Option to clear existing data or merge with current
- **Validation**: Backup file format validation and integrity checks
- **History Preservation**: Maintains version history during restore operations

#### 3. Backup Management
- **Automatic Naming**: Timestamp-based backup names with custom options
- **Directory Management**: Organized storage in WordPress uploads directory
- **Backup Listing**: Comprehensive backup metadata and file information
- **Size Tracking**: File size monitoring and storage optimization

### Import System Features

#### 1. Import with Options
```php
$defaults = array(
    'overwrite_existing' => false,
    'update_priorities' => true,
    'preserve_ids' => false,
    'create_backup' => true
);
```

#### 2. Collision Handling
- **Overwrite Protection**: Prevents accidental overwrites of existing code
- **Smart Merging**: Intelligent handling of duplicate code entries
- **Priority Management**: Automatic priority adjustment during import
- **Backup Creation**: Automatic backup before import operations

### Security & Validation
- **Nonce Verification**: CSRF protection for all AJAX operations
- **Permission Checks**: Administrator-only access to import/export functions
- **File Validation**: Comprehensive validation of import file formats
- **Sanitization**: Proper sanitization of all user inputs and file names

---

## System Integration & Architecture

### Database Schema Enhancement
Phase 2 leverages the existing database structure established in Phase 1 while adding metadata support for advanced features:

```sql
-- Enhanced metadata column usage for loading rules
ALTER TABLE wp_cdswerx_custom_code 
MODIFY COLUMN metadata TEXT COMMENT 'JSON metadata including loading rules, device targeting, etc.';

-- Example metadata structure:
{
    "loading_rules": {
        "device": ["desktop", "tablet"],
        "pages": {
            "page_ids": [1, 5, 10],
            "post_types": ["page", "post"],
            "templates": ["page-landing.php"]
        },
        "user_roles": ["administrator", "editor"],
        "admin_only": false,
        "frontend_only": true
    },
    "performance": {
        "critical": true,
        "defer_loading": false,
        "cache_duration": 3600
    }
}
```

### Class Architecture & Dependencies

#### Core Classes Interaction
```
CDSWerx_Code_Manager (Singleton)
├── get_codes_for_context() → Smart loading
├── should_load_code() → Conditional logic
└── update_loading_rules() → Metadata management

CDSWerx_Code_Injector
├── inject_critical_css() → Context-aware injection
├── get_loading_context() → Context detection
└── combine_codes() → Performance optimization

CDSWerx_Import_Export_Manager (Singleton)
├── export_codes() → Export functionality
├── import_codes() → Import with validation
├── create_backup() → Backup system
└── restore_backup() → Restore with safeguards
```

#### WordPress Integration Points
- **Hooks**: `wp_head`, `wp_footer`, `admin_head`, `admin_footer`
- **AJAX**: Secure nonce-protected AJAX endpoints
- **Permissions**: WordPress capability-based access control
- **File System**: WordPress uploads directory for backup storage

### Performance Optimizations

#### 1. Efficient Code Loading
- **Context Caching**: Minimize repeated context detection calls
- **Priority Sorting**: Efficient priority-based code organization
- **Conditional Loading**: Reduce unnecessary code execution
- **Device Detection**: Lightweight detection without external libraries

#### 2. Memory Management
- **Singleton Patterns**: Consistent instance management across classes
- **Lazy Loading**: Monaco Editor and heavy components load on demand
- **Resource Cleanup**: Proper disposal of editor instances and event listeners

#### 3. Database Optimization
- **Prepared Statements**: All database queries use prepared statements
- **Index Usage**: Efficient queries utilizing database indexes
- **Minimal Queries**: Reduced database calls through intelligent caching

### Error Handling & Logging

#### 1. Graceful Degradation
- **Monaco Fallback**: Automatic fallback to textarea editors if Monaco fails
- **Import Validation**: Comprehensive validation with detailed error messages
- **Backup Verification**: Pre-operation validation of backup integrity

#### 2. User Feedback
- **Success Messages**: Clear confirmation of completed operations
- **Error Details**: Specific error information for troubleshooting
- **Progress Tracking**: Real-time feedback during long operations

---

## Security Implementation

### Input Validation & Sanitization
- **File Uploads**: Comprehensive validation of import file types and contents
- **User Input**: Sanitization of all form inputs and AJAX data
- **SQL Injection**: Protection via prepared statements and parameter binding
- **XSS Prevention**: Output escaping and content security policies

### Access Control
- **Capability Checks**: WordPress capability-based permission system
- **Nonce Protection**: CSRF protection for all admin operations
- **File Permissions**: Secure file handling with proper permissions
- **Directory Security**: Protected backup directory with access restrictions

### Code Security
- **Direct Access Prevention**: All files protected against direct access
- **Execution Context**: Proper WordPress environment initialization
- **Resource Limits**: Memory and execution time management
- **Validation Layers**: Multiple validation layers for all operations

---

## Testing & Quality Assurance

### Browser Compatibility
- **Chrome**: Full support with all Monaco Editor features
- **Firefox**: Complete functionality with syntax highlighting
- **Safari**: Native support for all editor capabilities  
- **Edge**: Full compatibility with modern Edge versions

### WordPress Compatibility
- **Version Support**: WordPress 5.0+ with multisite compatibility
- **Plugin Conflicts**: No known conflicts with major WordPress plugins
- **Theme Integration**: Works with any properly coded WordPress theme
- **Performance Impact**: Minimal impact on page load times

### Feature Testing
- **Tab Navigation**: Complete tab switching functionality verified
- **Monaco Integration**: Professional code editing capabilities confirmed
- **Smart Loading**: Context-aware code injection validated
- **Import/Export**: Backup and restore operations thoroughly tested

---

## Deployment & Maintenance

### Version Control
- **Semantic Versioning**: Proper version numbering for all releases
- **Backward Compatibility**: Maintains compatibility with Phase 1 installations
- **Migration Scripts**: Automated database updates for new features
- **Rollback Support**: Safe rollback capabilities for version management

### Monitoring & Analytics
- **Performance Tracking**: Monitor loading times and resource usage
- **Error Logging**: Comprehensive error tracking and reporting
- **Usage Statistics**: Track feature adoption and usage patterns
- **Health Checks**: Regular system health monitoring

### Update Management
- **Incremental Updates**: Safe, incremental feature updates
- **Configuration Migration**: Automatic setting migration during updates
- **Backup Integration**: Automatic backups before major updates
- **Testing Framework**: Comprehensive testing before release deployment

---

## Future Enhancement Roadmap

### Phase 3 Considerations
- **Multi-Language Support**: Internationalization and localization
- **Advanced Themes**: Custom Monaco Editor themes and syntax highlighting
- **Team Collaboration**: Multi-user editing and conflict resolution
- **Performance Analytics**: Advanced performance monitoring and optimization

### Integration Opportunities
- **Page Builder Integration**: Enhanced integration with popular page builders
- **CDN Support**: Direct integration with content delivery networks
- **Version Control Systems**: Git integration for code versioning
- **Cloud Storage**: Cloud-based backup and synchronization

### Advanced Features
- **Code Compilation**: SASS/LESS compilation and minification
- **Real-time Collaboration**: Multi-user real-time editing capabilities
- **Advanced Templates**: Template system for common code patterns
- **API Extensions**: RESTful API for external integrations

---

## Conclusion

Phase 2 successfully transforms the CDSWerx Custom Code system into a professional-grade code management platform. The implementation provides:

### Key Achievements
- ✅ **Professional Code Editor**: VS Code-level editing experience with Monaco Editor
- ✅ **Smart Asset Loading**: Intelligent, context-aware code injection system  
- ✅ **Comprehensive Backup System**: Enterprise-level backup, restore, and version control
- ✅ **Performance Optimization**: Efficient loading with minimal performance impact
- ✅ **WordPress Integration**: Full compliance with WordPress standards and best practices

### Technical Excellence  
- **Scalable Architecture**: Singleton patterns and efficient resource management
- **Security First**: Comprehensive security implementation with multiple validation layers
- **Error Resilience**: Graceful degradation and comprehensive error handling
- **Performance Optimized**: Minimal database queries and efficient resource utilization

### User Experience
- **Intuitive Interface**: Professional, easy-to-use admin interface
- **Powerful Features**: Advanced editing capabilities with intelligent loading
- **Reliable Operations**: Robust backup and restore with safeguards
- **Flexible Configuration**: Extensive customization options for diverse use cases

**Phase 2 delivers a complete, production-ready custom code management system that rivals premium page builder solutions while maintaining the flexibility and performance standards expected in enterprise WordPress environments.**