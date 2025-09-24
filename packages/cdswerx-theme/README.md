# CDSWerx Theme

A professional, white-labeled WordPress theme built on Hello Elementor's proven foundation. Optimized for performance, designed for professional web development workflows, and seamlessly integrated with the CDSWerx plugin ecosystem.

## 🎯 Features

- **White-Labeled Design**: Professional, brandable theme without external branding
- **Hello Elementor Foundation**: Built on the most popular Elementor theme
- **Performance Optimized**: Minimal footprint with smart asset loading
- **CDSWerx Integration**: Seamless compatibility with CDSWerx plugin features
- **Elementor Ready**: Full compatibility with Elementor and Elementor Pro
- **Developer Friendly**: Clean code structure and extensive customization hooks
- **Multisite Compatible**: Works perfectly in WordPress multisite environments

## 📋 Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 7.4 or higher
- **Elementor**: 3.0+ (recommended)
- **CDSWerx Plugin**: Latest version (for full integration)

## 🚀 Quick Start

1. **Download** the theme ZIP file
2. **Upload** via WordPress Admin > Appearance > Themes > Add New
3. **Activate** the theme
4. **Install CDSWerx Plugin** for enhanced functionality
5. **Create a child theme** for customizations

## 🏗️ Architecture

### Theme Structure
```
cdswerx-theme/
├── style.css              # Main theme stylesheet
├── functions.php          # Core theme functions
├── index.php             # Main template file
├── header.php            # Header template
├── footer.php            # Footer template
├── screenshot.png        # Theme screenshot
├── assets/               # Theme assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── images/          # Theme images
├── includes/            # PHP includes
├── template-parts/      # Template partials
└── modules/            # Feature modules
```

### Key Components

- **Hello Elementor Base**: Inherits all Hello Elementor functionality
- **Custom Enhancements**: CDSWerx-specific improvements and optimizations
- **Asset Management**: Intelligent loading of CSS/JS resources
- **Hook System**: Extensive WordPress hooks for customization
- **Module Architecture**: Organized, maintainable code structure

## 🎨 Customization

### Child Theme (Recommended)

Always use a child theme for customizations:

```php
// style.css header
/*
Theme Name: CDSWerx Child Theme
Template: cdswerx-theme
Version: 1.0.0
*/

@import url("../cdswerx-theme/style.css");

/* Your custom styles here */
```

### Theme Hooks

```php
// Available action hooks
do_action('cdswerx_header_before');
do_action('cdswerx_header_after');
do_action('cdswerx_content_before');
do_action('cdswerx_content_after');
do_action('cdswerx_footer_before');
do_action('cdswerx_footer_after');

// Available filter hooks
apply_filters('cdswerx_body_classes', $classes);
apply_filters('cdswerx_enqueue_assets', $assets);
```

## 🔧 CDSWerx Plugin Integration

When paired with CDSWerx Plugin:

- **Automatic Asset Integration**: Plugin-generated CSS/JS loads seamlessly
- **Enhanced Customization**: Advanced code editor for theme modifications
- **Role-Based Access**: Control who can modify theme elements
- **Backup/Restore**: Save and restore theme customizations

## 📱 Responsive Design

- **Mobile-First**: Optimized for mobile devices
- **Flexible Grid**: Responsive layout system
- **Touch-Friendly**: Optimized for touch interactions
- **Performance**: Fast loading on all devices

## 🛠️ Development

### Local Development Setup

```bash
# Clone the theme
git clone https://github.com/cdswerx/cdswerx-theme.git

# Install dependencies (if using build tools)
npm install

# Build assets (if applicable)
npm run build

# Watch for changes during development
npm run watch
```

### Coding Standards

- Follow [WordPress Theme Standards](https://developer.wordpress.org/themes/getting-started/)
- Use [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Maintain backward compatibility with Hello Elementor
- Test with latest WordPress and Elementor versions

## 🔍 Testing

### Browser Compatibility
- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)

### WordPress Compatibility
- WordPress 6.0+
- Multisite environments
- Common plugin combinations

## 📚 Documentation

- [Installation Guide](docs/installation.md)
- [Customization Guide](docs/customization.md)
- [Child Theme Setup](docs/child-theme.md)
- [CDSWerx Integration](docs/cdswerx-integration.md)
- [Developer Reference](docs/developer-reference.md)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📄 License

Licensed under GPL v2 or later - see [LICENSE](LICENSE) file.

## 🆘 Support

- **GitHub Issues**: [Report bugs and feature requests](https://github.com/cdswerx/cdswerx-theme/issues)
- **Documentation**: [Theme documentation](https://docs.cdswerx.com/theme)
- **Support Forum**: [WordPress.org support](https://wordpress.org/support/theme/cdswerx-theme/)
- **Email**: support@cdswerx.com

## 🔄 Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

---

**Built with ❤️ by the CDSWerx Team**