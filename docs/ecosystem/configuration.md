# Configuration Guide

## Overview

CDSWerx Plugin provides comprehensive configuration options for custom code management, user permissions, and system behavior. This guide covers all available settings and their usage.

## Basic Configuration

### Initial Setup

After plugin activation, configure basic settings:

1. Navigate to **CDSWerx > Settings**
2. Review **General Settings**
3. Configure **User Permissions**
4. Set up **Code Injection Options**

### General Settings

#### Code Injection Settings

- **Enable CSS Injection**: Toggle custom CSS injection on frontend
- **Enable JS Injection**: Toggle custom JavaScript injection on frontend
- **Injection Priority**: Control loading order (1-999, lower numbers load first)
- **Minify Output**: Automatically minify injected code for performance

#### Performance Settings

- **Smart Loading**: Enable context-aware code loading
- **Device Detection**: Load different code based on device type
- **Cache Integration**: Work with popular caching plugins
- **Asset Optimization**: Combine and minimize HTTP requests

## Advanced Configuration

### Monaco Editor Settings

#### Editor Preferences

```javascript
// Editor configuration options
{
    theme: 'vs-dark',           // vs-light, vs-dark, hc-black
    fontSize: 14,               // Font size (10-30)
    tabSize: 2,                 // Tab width (2, 4, 8)
    wordWrap: 'on',             // on, off, wordWrapColumn
    minimap: { enabled: false }, // Show/hide minimap
    lineNumbers: 'on'           // on, off, relative
}
```

#### Syntax Features

- **Auto-completion**: Intelligent code suggestions
- **Error Detection**: Real-time syntax validation
- **Code Formatting**: Automatic beautification
- **Find & Replace**: Advanced search with regex support

### Smart Loading Configuration

#### Device-Specific Loading

Configure different code for different devices:

- **Desktop**: Full-featured CSS and JavaScript
- **Tablet**: Optimized styles for medium screens
- **Mobile**: Minimal code for performance

#### Page-Specific Loading

Target specific pages or sections:

```php
// Page targeting options
$loading_rules = array(
    'page_ids' => array(1, 2, 3),           // Specific page IDs
    'post_types' => array('product', 'event'), // Custom post types
    'templates' => array('page-landing.php'), // Page templates
    'url_patterns' => array('/shop/*')       // URL pattern matching
);
```

#### User Role Loading

Control code visibility by user roles:

- **Admin Only**: Administrative interface enhancements
- **Logged In Users**: Member-specific features
- **Guest Users**: Public visitor optimizations

### Import/Export Configuration

#### Export Settings

```php
$export_options = array(
    'format' => 'json',              // Export format
    'code_type' => 'all',            // css, js, or all
    'include_inactive' => false,     // Include disabled code
    'include_metadata' => true,      // Include creation dates, etc.
    'compress' => true               // Compress export file
);
```

#### Import Options

```php
$import_options = array(
    'overwrite_existing' => false,   // Handle code conflicts
    'update_priorities' => true,     // Recalculate loading order
    'create_backup' => true,         // Backup before import
    'validate_syntax' => true        // Check code validity
);
```

## User Permission Management

### Role-Based Access Control

#### Administrator Access
- Full plugin configuration access
- Create, edit, and delete custom code
- Import/export functionality
- User management settings

#### Editor Access
- Create and edit custom code
- Limited import functionality
- No user permission changes
- No system settings access

#### Custom Role Configuration

```php
// Grant custom capabilities
$role = get_role('custom_developer');
$role->add_cap('cdswerx_manage_code');
$role->add_cap('cdswerx_edit_advanced_settings');
```

### User-Specific Permissions

- **Individual Access Control**: Grant specific users additional permissions
- **Temporary Access**: Time-limited access for contractors
- **Audit Trail**: Track who made what changes when

## Integration Settings

### Theme Integration

#### CDSWerx Theme Compatibility

When using CDSWerx themes:

- **Automatic Integration**: Plugin automatically detects and integrates
- **Asset Coordination**: Prevents conflicts between theme and plugin assets
- **Enhanced Features**: Additional customization options available

#### Third-Party Themes

- **Compatibility Mode**: Safe injection for unknown themes
- **Manual Testing**: Test custom code before activation
- **Conflict Detection**: Identify potential CSS/JS conflicts

### Plugin Compatibility

#### Caching Plugins

Tested with popular caching solutions:

- **WP Rocket**: Automatic cache clearing after code changes
- **W3 Total Cache**: Integration with minification system
- **WP Super Cache**: Cache invalidation on updates

#### Page Builder Integration

- **Elementor**: Enhanced widget styling capabilities
- **Gutenberg**: Block-specific styling options
- **Beaver Builder**: Module customization support

## Troubleshooting Configuration

### Debug Mode

Enable debug mode for troubleshooting:

```php
// In wp-config.php
define('CDSWERX_DEBUG', true);
```

Debug features:
- **Detailed Logging**: Comprehensive error and activity logs
- **Code Validation**: Extended syntax checking
- **Performance Metrics**: Loading time analysis
- **Conflict Detection**: Identify theme/plugin conflicts

### Common Issues

#### Code Not Loading

1. Check injection settings are enabled
2. Verify user permissions
3. Review loading conditions
4. Clear cache if using caching plugins

#### Editor Not Loading

1. Check browser console for JavaScript errors
2. Verify CDN connectivity for Monaco Editor
3. Test with different browsers
4. Disable conflicting plugins temporarily

#### Performance Issues

1. Enable code minification
2. Use smart loading conditions
3. Optimize code priorities
4. Consider combining multiple code snippets

## Backup and Recovery

### Automatic Backups

Configure automatic backup schedule:

- **Daily Backups**: Recommended for active development
- **Weekly Backups**: Suitable for stable sites
- **Manual Backups**: Before major changes
- **Retention Policy**: How long to keep backup files

### Recovery Procedures

1. **Identify Issue**: Use debug logs to understand problem
2. **Select Backup**: Choose appropriate restoration point
3. **Test Restore**: Verify backup integrity before full restore
4. **Full Recovery**: Restore from backup with admin confirmation

## Security Considerations

### Code Sanitization

All custom code is automatically:
- **Syntax Validated**: Checked for proper formatting
- **Security Scanned**: Analyzed for potential vulnerabilities
- **Access Controlled**: Restricted based on user capabilities
- **Change Tracked**: Logged with user and timestamp information

### Best Practices

- **Regular Backups**: Maintain current backup schedule
- **User Training**: Ensure users understand security implications
- **Code Review**: Review custom code before activation
- **Permission Audits**: Regularly review user access levels

---

For additional help:
- [Installation Guide](installation.md)
- [API Reference](api-reference.md)
- [Troubleshooting Guide](troubleshooting.md)
- [GitHub Issues](https://github.com/cdswerx/cdswerx-plugin/issues)