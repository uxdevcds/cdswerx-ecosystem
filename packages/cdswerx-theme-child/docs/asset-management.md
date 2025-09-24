# Asset Management Guide

## Overview

CDSWerx Child Theme uses a modular asset management approach that provides flexibility, performance optimization, and seamless integration with the CDSWerx plugin ecosystem.

## Architecture Overview

### Modular CSS Structure

The child theme organizes CSS into specialized files for different concerns:

```
assets/css/
├── admin-style.css              # WordPress admin interface enhancements
├── bricons-style.css            # Custom brand icon font definitions
├── carbonguicon-styles.css      # Carbon design system icons
├── cds-globalstyles.css         # Elementor widget customizations
├── elementor-optimizations.css   # Performance optimizations
├── elementor-pro-enhancements.css # Elementor Pro specific features
└── theme-styles.css             # General theme styling
```

### Loading Strategy

#### Smart Conditional Loading

The child theme uses intelligent loading conditions to optimize performance:

```php
function cdswerx_child_enqueue_assets() {
    // Base parent theme styles
    wp_enqueue_style('cdswerx-parent-style', 
        get_template_directory_uri() . '/style.css'
    );
    
    // Core child theme styles
    wp_enqueue_style('cdswerx-child-style', 
        get_stylesheet_directory_uri() . '/style.css',
        array('cdswerx-parent-style')
    );
    
    // Conditional asset loading
    cdswerx_load_conditional_assets();
}
```

#### Performance-Based Loading

```php
function cdswerx_load_conditional_assets() {
    // Load admin styles only in admin
    if (is_admin()) {
        wp_enqueue_style('cdswerx-admin-style',
            get_stylesheet_directory_uri() . '/assets/css/admin-style.css'
        );
    }
    
    // Load Elementor enhancements only when Elementor is active
    if (cdswerx_is_elementor_active()) {
        wp_enqueue_style('cds-globalstyles',
            get_stylesheet_directory_uri() . '/assets/css/cds-globalstyles.css'
        );
        
        // Load Pro enhancements only if Elementor Pro is active
        if (cdswerx_is_elementor_pro_active()) {
            wp_enqueue_style('elementor-pro-enhancements',
                get_stylesheet_directory_uri() . '/assets/css/elementor-pro-enhancements.css'
            );
        } else {
            // Fallback styles for free Elementor
            wp_enqueue_style('theme-styles',
                get_stylesheet_directory_uri() . '/assets/css/theme-styles.css'
            );
        }
    }
}
```

## CSS File Breakdown

### cds-globalstyles.css

**Purpose**: Elementor widget customizations and global styling fixes.

**Key Features**:
- Widget-specific styling overrides
- Performance optimizations for Elementor
- Cross-browser compatibility fixes
- Responsive design enhancements

**Example Styles**:
```css
/* Icon Box Widget Enhancements */
.elementor-widget-icon-box .elementor-icon-box-content {
    padding: 1.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

/* Icon List Item Styling */
.elementor-icon-list-items .elementor-icon-list-item {
    margin-bottom: 0.75rem;
    padding: 0.5rem;
}

.elementor-icon-list-items .elementor-icon-list-item:hover {
    transform: translateX(5px);
    background-color: rgba(0, 0, 0, 0.05);
}
```

### admin-style.css

**Purpose**: WordPress admin interface enhancements and customizations.

**Key Features**:
- Improved admin typography
- Enhanced color schemes
- Better visual hierarchy
- Custom admin dashboard styling

**Example Styles**:
```css
/* Admin Interface Enhancements */
.wp-admin .cdswerx-admin-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

/* Custom Admin Typography */
.cdswerx-admin h2 {
    color: #23282d;
    font-weight: 600;
    margin-bottom: 15px;
}
```

### Icon System Files

#### bricons-style.css

**Purpose**: Custom brand icon font definitions and utility classes.

**Features**:
```css
@font-face {
    font-family: 'BrandIcons';
    src: url('../fonts/bricons.woff2') format('woff2'),
         url('../fonts/bricons.woff') format('woff');
    font-weight: normal;
    font-style: normal;
}

.bricons {
    font-family: 'BrandIcons';
    speak: none;
    font-style: normal;
    font-weight: normal;
    font-variant: normal;
    text-transform: none;
    line-height: 1;
}

.bricons-logo:before { content: "\e001"; }
.bricons-arrow:before { content: "\e002"; }
```

#### carbonguicon-styles.css

**Purpose**: Carbon design system icon integration.

**Features**:
```css
@font-face {
    font-family: 'CarbonIcons';
    src: url('../fonts/carbon-icons.woff2') format('woff2');
}

.carbon-icon {
    font-family: 'CarbonIcons';
    font-size: 16px;
    line-height: 1;
}

.carbon-icon--arrow-right:before { content: "\e003"; }
.carbon-icon--menu:before { content: "\e004"; }
```

## Asset Optimization

### Performance Strategies

#### Minification

```php
function cdswerx_minify_css($css_content) {
    // Remove comments
    $css_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css_content);
    
    // Remove whitespace
    $css_content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css_content);
    
    return $css_content;
}
```

#### Critical CSS Loading

```php
function cdswerx_load_critical_css() {
    // Inline critical CSS for above-the-fold content
    $critical_css = cdswerx_get_critical_css();
    if (!empty($critical_css)) {
        echo '<style id="cdswerx-critical-css">' . $critical_css . '</style>';
    }
}
add_action('wp_head', 'cdswerx_load_critical_css', 1);
```

#### Lazy Loading

```php
function cdswerx_lazy_load_assets() {
    // Load non-critical CSS after page load
    wp_enqueue_script('cdswerx-lazy-load', 
        get_stylesheet_directory_uri() . '/assets/js/lazy-load.js',
        array(), 
        CDSWERX_CHILD_VERSION, 
        true
    );
}
```

### Cache Integration

#### Browser Caching

```php
function cdswerx_add_cache_headers() {
    // Add cache headers for static assets
    $assets = array('css', 'js', 'png', 'jpg', 'gif', 'woff', 'woff2');
    
    foreach ($assets as $ext) {
        add_action("wp_enqueue_style", function($handle) use ($ext) {
            if (strpos($handle, $ext) !== false) {
                wp_add_inline_style($handle, '/* Cache: 30 days */');
            }
        });
    }
}
```

#### CDN Integration

```php
function cdswerx_use_cdn_assets() {
    if (defined('CDSWERX_CDN_URL') && CDSWERX_CDN_URL) {
        // Replace local asset URLs with CDN URLs
        add_filter('stylesheet_uri', function($uri) {
            return str_replace(home_url(), CDSWERX_CDN_URL, $uri);
        });
    }
}
```

## Advanced Asset Management

### Dynamic Asset Loading

#### Context-Aware Loading

```php
function cdswerx_load_contextual_assets() {
    $context = cdswerx_get_page_context();
    
    switch ($context['page_type']) {
        case 'homepage':
            wp_enqueue_style('cdswerx-homepage-styles');
            break;
            
        case 'product':
            wp_enqueue_style('cdswerx-product-styles');
            break;
            
        case 'blog':
            wp_enqueue_style('cdswerx-blog-styles');
            break;
    }
}
```

#### Device-Specific Loading

```php
function cdswerx_load_device_assets() {
    $device = cdswerx_detect_device();
    
    if ($device === 'mobile') {
        wp_enqueue_style('cdswerx-mobile-styles');
    } elseif ($device === 'tablet') {
        wp_enqueue_style('cdswerx-tablet-styles');
    } else {
        wp_enqueue_style('cdswerx-desktop-styles');
    }
}
```

### Asset Dependencies

#### Dependency Management

```php
function cdswerx_manage_dependencies() {
    // Ensure proper loading order
    $dependencies = array(
        'cdswerx-parent-style' => array(),
        'cdswerx-child-style' => array('cdswerx-parent-style'),
        'cds-globalstyles' => array('cdswerx-child-style', 'elementor-frontend'),
        'bricons-style' => array('cdswerx-child-style'),
        'admin-style' => array('wp-admin', 'common')
    );
    
    foreach ($dependencies as $handle => $deps) {
        wp_register_style($handle, 
            cdswerx_get_asset_url($handle), 
            $deps, 
            CDSWERX_CHILD_VERSION
        );
    }
}
```

### Version Management

#### Automatic Versioning

```php
function cdswerx_get_asset_version($file_path) {
    // Use file modification time for cache busting
    $file_time = filemtime(get_stylesheet_directory() . $file_path);
    return $file_time ? $file_time : CDSWERX_CHILD_VERSION;
}

function cdswerx_enqueue_versioned_asset($handle, $file_path, $deps = array()) {
    wp_enqueue_style($handle,
        get_stylesheet_directory_uri() . $file_path,
        $deps,
        cdswerx_get_asset_version($file_path)
    );
}
```

## Integration with CDSWerx Plugin

### Plugin Coordination

#### Asset Coordination

```php
function cdswerx_coordinate_with_plugin() {
    // Check if CDSWerx Plugin is handling assets
    if (function_exists('cdswerx_plugin_handles_assets')) {
        if (cdswerx_plugin_handles_assets()) {
            // Let plugin handle primary assets
            remove_action('wp_enqueue_scripts', 'cdswerx_child_enqueue_assets');
            
            // Only load theme-specific enhancements
            add_action('wp_enqueue_scripts', 'cdswerx_load_theme_enhancements');
        }
    }
}
```

#### Shared Asset Management

```php
function cdswerx_sync_with_plugin_assets() {
    // Get plugin-managed assets
    $plugin_assets = cdswerx_get_plugin_assets();
    
    // Coordinate theme assets to avoid conflicts
    foreach ($plugin_assets as $asset) {
        if ($asset['type'] === 'css') {
            // Ensure theme CSS loads after plugin CSS
            wp_enqueue_style('cdswerx-theme-override',
                get_stylesheet_directory_uri() . '/assets/css/plugin-overrides.css',
                array($asset['handle'])
            );
        }
    }
}
```

## Troubleshooting

### Common Issues

#### Assets Not Loading

1. **Check File Paths**: Verify asset file paths are correct
2. **Dependency Issues**: Ensure all dependencies are properly loaded
3. **Cache Problems**: Clear browser and server cache
4. **Permission Issues**: Check file permissions on asset files

#### Performance Problems

1. **Too Many HTTP Requests**: Combine similar assets
2. **Large File Sizes**: Optimize and minify assets
3. **Blocking Resources**: Use async loading for non-critical assets
4. **Cache Misses**: Implement proper cache headers

### Debug Tools

#### Asset Loading Debug

```php
function cdswerx_debug_asset_loading() {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        add_action('wp_footer', function() {
            global $wp_styles;
            echo '<!-- CDSWerx Assets Debug -->';
            echo '<!-- Loaded Styles: ' . implode(', ', $wp_styles->done) . ' -->';
        });
    }
}
```

#### Performance Monitoring

```php
function cdswerx_monitor_asset_performance() {
    $start_time = microtime(true);
    
    add_action('wp_footer', function() use ($start_time) {
        $load_time = microtime(true) - $start_time;
        echo "<!-- Asset Load Time: " . round($load_time, 4) . "s -->";
    });
}
```

---

For more information:
- [Installation Guide](installation.md)
- [Customization Guide](customization.md)
- [Icon System Documentation](icon-system.md)
- [CDSWerx Plugin Integration](../../../cdswerx-plugin/docs/configuration.md)