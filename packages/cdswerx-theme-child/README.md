# CDSWerx Theme Child

A professional child theme for CDSWerx Theme with integrated asset management, advanced customization capabilities, and seamless CDSWerx plugin ecosystem support.

## 🎯 Overview

This child theme extends the CDSWerx Theme with additional functionality, custom styling options, and integrated asset management. It serves as both a ready-to-use child theme and a comprehensive example for developers creating their own customizations.

## ✨ Features

- **Professional Asset Management**: Modular CSS architecture with separate concern files
- **Icon System Integration**: Custom brand icons and Carbon icon support
- **Elementor Enhancements**: Widget-specific styling overrides and improvements
- **Admin Customizations**: Enhanced WordPress admin interface styling
- **Performance Optimized**: Smart loading strategies and minimal footprint
- **Developer Ready**: Well-documented code structure with extensive examples

## 📋 Requirements

- **CDSWerx Theme**: Latest version (parent theme)
- **WordPress**: 6.0 or higher
- **PHP**: 7.4 or higher
- **CDSWerx Plugin**: Latest version (recommended)
- **Elementor**: 3.0+ (for full widget enhancements)

## 🚀 Quick Start

### Installation

1. **Install Parent Theme**: Ensure CDSWerx Theme is installed and activated
2. **Download Child Theme**: Get the latest release ZIP file
3. **Upload**: Via WordPress Admin > Appearance > Themes > Add New > Upload
4. **Activate**: Switch to the child theme
5. **Configure**: Customize settings via CDSWerx Plugin integration

### Verification

After activation, verify:
- Parent theme styles are loading correctly
- Child theme customizations are applied
- CDSWerx Plugin integration is working
- Icon fonts are displaying properly

## 🏗️ Architecture

### File Structure

```
cdswerx-child-theme/
├── style.css                    # Main child theme stylesheet
├── functions.php               # Child theme functionality
├── screenshot.png              # Theme screenshot
├── assets/                    # Theme assets
│   ├── css/                  # Modular stylesheets
│   │   ├── admin-style.css      # WordPress admin styling
│   │   ├── bricons-style.css    # Brand icons stylesheet
│   │   ├── carbonguicon-styles.css # Carbon icons
│   │   ├── cds-globalstyles.css # Elementor widget customizations
│   │   ├── elementor-optimizations.css # Performance optimizations
│   │   ├── elementor-pro-enhancements.css # Pro features
│   │   └── theme-styles.css     # General theme styling
│   ├── img/                   # Images and graphics
│   └── js/                    # JavaScript files
└── docs/                      # Documentation
    ├── installation.md
    ├── customization.md
    └── asset-management.md
```

### CSS Architecture

The child theme uses a modular CSS approach:

#### Core Stylesheets

- **`cds-globalstyles.css`**: Elementor widget customizations and fixes
- **`admin-style.css`**: WordPress admin area styling enhancements
- **`theme-styles.css`**: General site styling (conditional loading based on Elementor Pro status)

#### Icon System Stylesheets

- **`bricons-style.css`**: Custom brand icon font definitions
- **`carbonguicon-styles.css`**: Carbon design system icons

#### Elementor Enhancements

- **`elementor-optimizations.css`**: Performance and display improvements
- **`elementor-pro-enhancements.css`**: Elementor Pro specific enhancements

## 🎨 Customization Examples

### Custom CSS Integration

```php
// functions.php example
function cdswerx_child_enqueue_styles() {
    // Enqueue parent theme styles
    wp_enqueue_style(
        'cdswerx-parent-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme()->get('Version')
    );
    
    // Enqueue modular stylesheets
    $stylesheets = [
        'cds-globalstyles' => '/assets/css/cds-globalstyles.css',
        'admin-style' => '/assets/css/admin-style.css',
        'bricons-style' => '/assets/css/bricons-style.css',
    ];
    
    foreach ($stylesheets as $handle => $path) {
        wp_enqueue_style(
            $handle,
            get_stylesheet_directory_uri() . $path,
            array('cdswerx-parent-style'),
            CHILD_THEME_VERSION
        );
    }
}
```

### Elementor Widget Targeting

```css
/* cds-globalstyles.css examples */

/* Icon Box Widget Enhancements */
.elementor-widget-icon-box .elementor-icon-box-content {
    padding: 1.5rem;
    border-radius: 8px;
    background: var(--e-global-color-accent);
}

/* Icon List Widget Styling */
.elementor-icon-list-items .elementor-icon-list-item {
    margin-bottom: 0.75rem;
    padding: 0.5rem;
    transition: all 0.3s ease;
}

.elementor-icon-list-items .elementor-icon-list-item:hover {
    transform: translateX(5px);
}
```

### Icon System Usage

```css
/* Brand Icons (bricons-style.css) */
.bricons-logo:before {
    content: "\e001";
    font-family: "BrandIcons";
}

/* Carbon Icons (carbonguicon-styles.css) */
.carbon-icon-arrow:before {
    content: "\e002";
    font-family: "CarbonIcons";
}
```

## 🔧 Advanced Features

### Smart Asset Loading

The child theme includes intelligent asset loading:

```php
// Conditional loading based on Elementor Pro status
if (cdswerx_is_elementor_pro_active()) {
    wp_enqueue_style('elementor-pro-enhancements');
} else {
    wp_enqueue_style('theme-styles');
}
```

### Performance Optimizations

- **Modular Loading**: Only load required CSS files
- **Conditional Assets**: Load features based on active plugins
- **Minimal Footprint**: Lightweight, focused enhancements
- **Cache-Friendly**: Version management for browser caching

### Admin Interface Enhancements

- **Custom Admin Styling**: Professional admin interface improvements
- **Enhanced Typography**: Improved readability and visual hierarchy
- **Color Scheme Integration**: Consistent branding throughout admin

## 📚 Documentation

- [Installation Guide](docs/installation.md)
- [Customization Guide](docs/customization.md)
- [Asset Management](docs/asset-management.md)
- [Icon System Usage](docs/icon-system.md)
- [Elementor Integration](docs/elementor-integration.md)

## 🛠️ Development

### Local Development Setup

```bash
# Clone the repository
git clone https://github.com/cdswerx/cdswerx-child-theme.git

# Install in WordPress themes directory
cp -r cdswerx-child-theme /path/to/wordpress/wp-content/themes/

# Activate via WordPress admin or WP-CLI
wp theme activate cdswerx-child-theme
```

### Customization Guidelines

1. **Always Use Child Theme**: Never modify the parent theme directly
2. **Modular Approach**: Use separate CSS files for different concerns
3. **Performance First**: Consider loading impact of customizations
4. **Documentation**: Document custom modifications for future reference

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/enhancement-name`)
3. Make your changes with proper documentation
4. Test thoroughly across different configurations
5. Submit a pull request with detailed description

## 📄 License

Licensed under GPL v2 or later - see [LICENSE](LICENSE) file.

## 🆘 Support

- **GitHub Issues**: [Report issues](https://github.com/cdswerx/cdswerx-child-theme/issues)
- **Documentation**: [Complete documentation](https://docs.cdswerx.com/child-theme)
- **Parent Theme**: [CDSWerx Theme repository](https://github.com/cdswerx/cdswerx-theme)
- **Plugin Integration**: [CDSWerx Plugin repository](https://github.com/cdswerx/cdswerx-plugin)

## 📊 Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

---

**Professional WordPress development by the CDSWerx Team**