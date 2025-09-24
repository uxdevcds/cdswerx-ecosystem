# Installation Guide

## System Requirements

- **WordPress**: 6.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **Elementor**: 3.0+ (recommended for theme integration)

## Installation Methods

### Method 1: WordPress Admin Upload

1. Download the latest plugin ZIP file from [GitHub Releases](https://github.com/cdswerx/cdswerx-plugin/releases)
2. Log into your WordPress admin dashboard
3. Navigate to **Plugins > Add New**
4. Click **Upload Plugin**
5. Choose the downloaded ZIP file and click **Install Now**
6. After installation, click **Activate Plugin**

### Method 2: Manual FTP Installation

1. Download and extract the plugin ZIP file
2. Upload the `cdswerx` folder to `/wp-content/plugins/` directory via FTP
3. Log into WordPress admin dashboard
4. Navigate to **Plugins > Installed Plugins**
5. Find "CDSWerx Plugin" and click **Activate**

### Method 3: WP-CLI Installation

```bash
# Download and install
wp plugin install https://github.com/cdswerx/cdswerx-plugin/releases/latest/download/cdswerx-plugin.zip

# Activate the plugin
wp plugin activate cdswerx
```

## Post-Installation Setup

### 1. Initial Configuration

After activation, you'll see the CDSWerx menu in your WordPress admin:

1. Navigate to **CDSWerx > Settings**
2. Review and configure basic settings
3. Set user permissions as needed

### 2. Custom Code Setup

1. Go to **CDSWerx > Custom Code**
2. The Monaco Editor will load automatically
3. Start adding your custom CSS and JavaScript

### 3. Theme Integration

If using CDSWerx themes:

1. Install the CDSWerx parent theme
2. Install the CDSWerx child theme
3. Activate the child theme
4. The plugin will automatically integrate with theme features

## Verification

To verify successful installation:

1. Check **Plugins > Installed Plugins** - CDSWerx should show as active
2. Look for the **CDSWerx** menu in the admin sidebar
3. Visit **CDSWerx > Custom Code** to ensure Monaco Editor loads
4. Check the frontend for any custom CSS/JS injection

## Troubleshooting

### Plugin Won't Activate

- Verify PHP version meets requirements (7.4+)
- Check WordPress version compatibility (6.0+)
- Ensure no plugin conflicts exist
- Review error logs for specific issues

### Monaco Editor Not Loading

- Clear browser cache
- Check for JavaScript console errors
- Verify CDN connectivity
- Ensure no ad blockers are interfering

### Custom Code Not Applying

- Check injection settings in **CDSWerx > Settings**
- Verify code syntax is valid
- Clear any caching plugins
- Check if code is being overridden by theme

## Need Help?

- [Configuration Guide](configuration.md)
- [Troubleshooting](troubleshooting.md)
- [GitHub Issues](https://github.com/cdswerx/cdswerx-plugin/issues)
- [Support Forum](https://wordpress.org/support/plugin/cdswerx/)