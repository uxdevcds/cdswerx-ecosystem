# CDSWerx Integration Guide

## Overview

This guide explains how CDSWerx Theme integrates seamlessly with the CDSWerx Plugin ecosystem to provide enhanced functionality and performance optimizations.

## Plugin Integration Features

### Automatic Detection

The CDSWerx Theme automatically detects when the CDSWerx Plugin is active and enables enhanced features:

```php
// Theme detects plugin automatically
if (class_exists('CDSWerx')) {
    // Enable enhanced features
    add_theme_support('cdswerx-enhanced-features');
}
```

### Enhanced Asset Management

When CDSWerx Plugin is active, the theme provides:

- **Coordinated Loading**: Prevents duplicate CSS/JS loading
- **Priority Management**: Ensures proper loading order
- **Conflict Prevention**: Automatically resolves common conflicts
- **Performance Optimization**: Minimizes HTTP requests

### Custom Code Integration

The theme seamlessly integrates with plugin-generated custom code:

#### CSS Integration

```php
// Theme provides hooks for plugin CSS injection
add_action('cdswerx_theme_head', 'cdswerx_inject_custom_css');

// Smart loading based on theme context
if (cdswerx_is_elementor_page()) {
    // Load Elementor-specific customizations
    cdswerx_load_elementor_enhancements();
}
```

#### JavaScript Integration

```php
// Theme coordinates JavaScript loading
add_action('cdswerx_theme_footer', 'cdswerx_inject_custom_js');

// Conditional loading based on page type
if (is_front_page()) {
    cdswerx_load_homepage_scripts();
}
```

## Theme-Specific Enhancements

### Elementor Integration

When both CDSWerx Plugin and Elementor are active:

#### Widget Enhancements

- **Icon Box Widgets**: Enhanced styling and animations
- **Icon List Items**: Improved typography and spacing
- **Button Widgets**: Additional hover effects and styling options
- **Heading Widgets**: Extended typography controls

#### Custom CSS Classes

The theme provides special CSS classes that work with plugin customizations:

```css
/* Enhanced widget classes */
.cdswerx-enhanced .elementor-widget-icon-box {
    /* Plugin-compatible styling */
}

.cdswerx-optimized .elementor-icon-list-items {
    /* Performance-optimized styles */
}
```

### Performance Features

#### Smart Asset Loading

```php
// Theme coordinates with plugin for optimal loading
function cdswerx_theme_enqueue_assets() {
    // Check if plugin is handling CSS
    if (!cdswerx_plugin_manages_css()) {
        wp_enqueue_style('cdswerx-theme-styles');
    }
    
    // Load theme-specific assets
    wp_enqueue_style('cdswerx-theme-core');
}
```

#### Cache Coordination

The theme works with plugin caching strategies:

- **Automatic Cache Clearing**: When plugin code changes, theme cache is cleared
- **Asset Versioning**: Coordinated version management
- **Minification**: Shared minification settings

## Configuration Options

### Theme Customizer Integration

Access CDSWerx-specific options in the WordPress Customizer:

1. Navigate to **Appearance > Customize**
2. Open **CDSWerx Settings** panel
3. Configure integration options:

#### Plugin Integration Settings

- **Enable Enhanced Features**: Toggle advanced plugin integration
- **Asset Coordination**: Control how theme and plugin assets load together
- **Performance Mode**: Optimize for speed vs. features
- **Debug Mode**: Enable integration debugging

#### Styling Coordination

- **CSS Priority**: Set loading order for theme vs. plugin styles
- **Conflict Resolution**: Automatic handling of style conflicts
- **Responsive Behavior**: Coordinate responsive breakpoints

### Advanced Configuration

#### Developer Settings

```php
// Theme configuration constants
define('CDSWERX_THEME_PLUGIN_INTEGRATION', true);
define('CDSWERX_THEME_PERFORMANCE_MODE', 'balanced');
define('CDSWERX_THEME_DEBUG', false);

// Custom integration options
$cdswerx_integration = array(
    'css_coordination' => 'automatic',
    'js_loading' => 'footer',
    'asset_versioning' => 'synchronized',
    'cache_strategy' => 'coordinated'
);
```

## Child Theme Considerations

### Enhanced Child Theme Support

When using a CDSWerx child theme with the plugin:

#### Automatic Asset Management

```php
// Child theme automatically inherits plugin integration
function cdswerx_child_theme_setup() {
    // Inherit parent theme plugin integration
    add_theme_support('cdswerx-plugin-integration');
    
    // Add child-specific plugin features
    add_theme_support('cdswerx-advanced-customization');
}
```

#### Modular CSS Architecture

The child theme provides modular CSS files that work with plugin features:

- **`cds-globalstyles.css`**: Elementor widget customizations
- **`admin-style.css`**: WordPress admin enhancements
- **`theme-styles.css`**: General theme styling with plugin coordination

### Custom Code Examples

#### Theme Hook Integration

```php
// Use theme hooks with plugin functionality
add_action('cdswerx_theme_before_header', function() {
    // Custom code that works with plugin
    if (function_exists('cdswerx_get_header_code')) {
        echo cdswerx_get_header_code();
    }
});
```

#### Plugin Data Access

```php
// Access plugin data in theme templates
$custom_styles = cdswerx_get_active_css_codes();
foreach ($custom_styles as $style) {
    if ($style->applies_to_current_page()) {
        // Apply theme-specific handling
        add_custom_body_class($style->get_identifier());
    }
}
```

## Troubleshooting Integration

### Common Issues

#### Styles Not Loading

1. **Check Plugin Status**: Ensure CDSWerx Plugin is active
2. **Verify Integration**: Confirm theme detects plugin
3. **Clear Cache**: Clear all caching (plugin, theme, and server)
4. **Debug Mode**: Enable debug mode to see integration status

#### JavaScript Conflicts

1. **Loading Order**: Check if scripts load in correct order
2. **jQuery Dependencies**: Verify jQuery is loaded before custom scripts
3. **Console Errors**: Check browser console for JavaScript errors
4. **Plugin Conflicts**: Temporarily disable other plugins to test

#### Performance Issues

1. **Asset Duplication**: Check for duplicate CSS/JS loading
2. **Priority Settings**: Adjust loading priorities
3. **Caching Strategy**: Optimize cache settings
4. **Minification**: Enable asset minification

### Debug Information

#### Integration Status Check

```php
// Check integration status
function cdswerx_debug_integration() {
    $status = array(
        'plugin_active' => class_exists('CDSWerx'),
        'theme_support' => current_theme_supports('cdswerx-integration'),
        'asset_coordination' => cdswerx_assets_coordinated(),
        'cache_status' => cdswerx_cache_active()
    );
    
    return $status;
}
```

#### Performance Metrics

Enable performance monitoring to track:

- **Asset Loading Time**: Time to load theme and plugin assets
- **HTTP Requests**: Number of requests made
- **Cache Hit Rate**: Percentage of cached vs. fresh requests
- **Page Load Impact**: Overall impact on page load times

## Best Practices

### Development Workflow

1. **Test Integration**: Always test theme and plugin together
2. **Version Compatibility**: Ensure theme and plugin versions are compatible
3. **Performance Testing**: Monitor impact on page load times
4. **User Testing**: Test with different user roles and permissions

### Production Deployment

1. **Staging Environment**: Test integration in staging before production
2. **Backup Strategy**: Backup both theme and plugin configurations
3. **Monitoring**: Set up monitoring for integration-related issues
4. **Documentation**: Document any custom integration modifications

### Updates and Maintenance

1. **Coordinated Updates**: Update theme and plugin together when possible
2. **Testing Protocol**: Test integration after each update
3. **Rollback Plan**: Have a plan to rollback if integration breaks
4. **Change Log**: Document integration changes for team reference

---

For additional help:
- [CDSWerx Plugin Documentation](https://github.com/cdswerx/cdswerx-plugin)
- [Theme Customization Guide](customization.md)
- [Child Theme Setup](child-theme.md)
- [Support Forum](https://github.com/cdswerx/cdswerx-theme/issues)