# CDSWerx User Guide & Implementation Manual

## **Table of Contents**

1. [Getting Started](#getting-started)
2. [Installation Guide](#installation-guide)
3. [Configuration Walkthrough](#configuration-walkthrough)
4. [Feature Usage Guide](#feature-usage-guide)
5. [Troubleshooting](#troubleshooting)
6. [Advanced Configuration](#advanced-configuration)
7. [Best Practices](#best-practices)
8. [FAQ](#frequently-asked-questions)

---

## **Getting Started**

### **What Is CDSWerx?**

CDSWerx is a comprehensive WordPress ecosystem consisting of:
- **CDSWerx Plugin**: Core functionality and admin management
- **CDSWerx Theme**: Base theme architecture
- **CDSWerx Child Theme**: Customizations and asset management

### **System Requirements**

- WordPress 5.8 or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.3+)
- Recommended: Multisite-compatible hosting

### **Quick Start Checklist**

- [ ] Install CDSWerx Plugin
- [ ] Activate CDSWerx Theme
- [ ] Install CDSWerx Child Theme
- [ ] Configure GitHub update system
- [ ] Run initial quality assurance tests

---

## **Installation Guide**

### **Method 1: WordPress Admin Installation**

1. **Install CDSWerx Plugin**
   ```
   Plugins → Add New → Upload Plugin → Choose cdswerx-plugin.zip → Install Now → Activate
   ```

2. **Install CDSWerx Theme**
   ```
   Appearance → Themes → Add New → Upload Theme → Choose cdswerx-theme.zip → Install Now
   ```

3. **Install CDSWerx Child Theme**
   ```
   Appearance → Themes → Add New → Upload Theme → Choose cdswerx-theme-child.zip → Install Now → Activate
   ```

### **Method 2: FTP Installation**

1. **Upload Plugin Files**
   ```bash
   # Upload to wp-content/plugins/
   unzip cdswerx-plugin.zip
   mv cdswerx-plugin/ /path/to/wordpress/wp-content/plugins/
   ```

2. **Upload Theme Files**
   ```bash
   # Upload themes to wp-content/themes/
   unzip cdswerx-theme.zip
   mv cdswerx-theme/ /path/to/wordpress/wp-content/themes/
   
   unzip cdswerx-theme-child.zip
   mv cdswerx-theme-child/ /path/to/wordpress/wp-content/themes/
   ```

3. **Activate Components**
   - Go to WordPress Admin
   - Activate CDSWerx Plugin
   - Activate CDSWerx Child Theme

### **Method 3: Developer Installation**

```bash
# Clone from ecosystem repository
git clone https://github.com/uxdevcds/cdswerx-ecosystem.git
cd cdswerx-ecosystem

# Install dependencies
npm install

# Sync to WordPress environment
npm run sync:to-wp
```

---

## **Configuration Walkthrough**

### **Initial Setup**

1. **Access CDSWerx Dashboard**
   ```
   WordPress Admin → CDSWerx → Dashboard
   ```

2. **System Status Check**
   - Verify all components are active
   - Check WordPress compatibility score
   - Review system requirements

3. **GitHub Update Configuration**
   ```
   CDSWerx → GitHub Updates → Settings
   ```
   - Add GitHub personal access token
   - Configure repository access
   - Set update preferences

### **Basic Configuration Steps**

#### **Step 1: GitHub Update System**

1. **Generate GitHub Token**
   - Go to GitHub → Settings → Developer settings → Personal access tokens
   - Create new token with `repo` permissions
   - Copy token for CDSWerx configuration

2. **Configure Update Settings**
   ```
   CDSWerx → GitHub Updates → Settings
   GitHub Token: [paste your token]
   Update Check Frequency: Daily
   Backup Before Updates: Enabled
   Auto-backup Retention: 30 days
   ```

#### **Step 2: Performance Optimization**

1. **Enable Caching**
   ```
   CDSWerx → Performance → Caching
   Enable CSS Caching: ✓
   Enable JS Minification: ✓
   Enable Lazy Loading: ✓
   Cache Duration: 24 hours
   ```

2. **Asset Optimization**
   ```
   CDSWerx → Performance → Assets
   Combine CSS Files: ✓
   Combine JS Files: ✓
   Enable Image Optimization: ✓
   ```

#### **Step 3: Admin Interface Setup**

1. **Configure Admin Menus**
   ```
   CDSWerx → Admin Settings → Menu Configuration
   Show Quick Actions: ✓
   Enable Advanced CSS Editor: ✓
   Show System Status: ✓
   ```

2. **User Management**
   ```
   CDSWerx → Admin Settings → User Management
   Enable Enhanced User Profiles: ✓
   Custom User Capabilities: Configure as needed
   ```

---

## **Feature Usage Guide**

### **Advanced CSS Management**

#### **Accessing Advanced CSS**
```
CDSWerx → Advanced CSS → Editor
```

#### **CSS Organization**
- **Global Styles**: Site-wide CSS modifications
- **Component Styles**: Specific widget/element styling
- **Responsive Styles**: Mobile and tablet adjustments
- **Print Styles**: Print-specific styling

#### **CSS Best Practices**
```css
/* Use CDSWerx naming conventions */
.cdswerx-custom-class {
    /* Your styles */
}

/* Target Elementor widgets specifically */
.elementor-widget-icon-box .cdswerx-enhancement {
    /* Widget-specific styles */
}

/* Use CSS custom properties for consistency */
:root {
    --cdswerx-primary-color: #0073aa;
    --cdswerx-secondary-color: #666;
}
```

### **GitHub Update Management**

#### **Checking for Updates**
1. Go to `CDSWerx → GitHub Updates`
2. Click "Check for Updates"
3. Review available updates
4. Select components to update

#### **Installing Updates**
1. **Automatic Backup**: System creates backup before update
2. **Download**: New version downloaded from GitHub
3. **Installation**: Files replaced with new version
4. **Verification**: System tests installation
5. **Completion**: Update notification displayed

#### **Rollback Process**
```
CDSWerx → GitHub Updates → Backup Management
1. Select backup to restore
2. Click "Rollback to This Version"
3. Confirm rollback action
4. System restores previous version
```

### **Quality Assurance Testing**

#### **Running Tests**
```
CDSWerx → QA Testing → Run All Tests
```

#### **Test Categories**
- **Multisite Compatibility**: Cross-site functionality
- **Integration Tests**: Theme-plugin coordination
- **Accessibility Tests**: WCAG compliance
- **Performance Tests**: Speed and optimization

#### **Interpreting Results**
- **✅ Passed**: Feature working correctly
- **❌ Failed**: Issue requires attention
- **⚠️ Warning**: Minor issue or recommendation
- **⏸️ Skipped**: Test not applicable to current setup

### **Performance Optimization**

#### **Cache Management**
```
CDSWerx → Performance → Cache Management
- Clear All Caches
- Clear CSS Cache
- Clear JavaScript Cache
- Clear Image Cache
```

#### **Asset Optimization**
```
CDSWerx → Performance → Asset Optimization
- Minify CSS/JS: Reduces file sizes
- Combine Files: Reduces HTTP requests
- Lazy Loading: Improves page load times
- Image Optimization: Compresses images
```

---

## **Troubleshooting**

### **Common Issues & Solutions**

#### **Issue: Plugin Not Activating**
**Symptoms**: Error during plugin activation
**Solutions**:
1. Check PHP version compatibility
2. Verify file permissions
3. Check for plugin conflicts
4. Review error logs

```bash
# Check error logs
tail -f /path/to/wordpress/wp-content/debug.log
```

#### **Issue: Theme Not Loading Correctly**
**Symptoms**: Site appearance broken or default
**Solutions**:
1. Verify theme installation
2. Check parent theme dependency
3. Clear caches
4. Review CSS loading order

#### **Issue: GitHub Updates Not Working**
**Symptoms**: Updates not found or failing
**Solutions**:
1. Verify GitHub token permissions
2. Check repository access
3. Test internet connectivity
4. Review backup disk space

#### **Issue: Performance Problems**
**Symptoms**: Site loading slowly
**Solutions**:
1. Run performance tests
2. Check cache configuration
3. Optimize database
4. Review asset loading

### **Debug Mode**

#### **Enabling Debug Mode**
```php
// Add to wp-config.php
define('CDSWERX_DEBUG', true);
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

#### **Debug Information Locations**
- WordPress Debug Log: `wp-content/debug.log`
- CDSWerx Test Log: `wp-content/cdswerx-tests/test-log.txt`
- Update Log: `wp-content/cdswerx-updates/update-log.txt`
- Performance Log: `wp-content/cdswerx-cache/performance-log.txt`

### **Error Code Reference**

| Error Code | Description | Solution |
|------------|-------------|----------|
| CDSWERX_001 | Plugin initialization failed | Check file permissions |
| CDSWERX_002 | Theme compatibility issue | Verify theme installation |
| CDSWERX_003 | GitHub API error | Check token and connectivity |
| CDSWERX_004 | Cache write error | Check directory permissions |
| CDSWERX_005 | Database connection issue | Review database settings |

---

## **Advanced Configuration**

### **Multisite Network Setup**

#### **Network Activation**
```
Network Admin → Plugins → CDSWerx → Network Activate
```

#### **Per-Site Configuration**
```php
// Site-specific settings in wp-config.php
$cdswerx_site_config = [
    'github_updates' => true,
    'performance_optimization' => true,
    'testing_enabled' => false
];
```

### **Custom Hook Implementation**

#### **Adding Custom Functionality**
```php
// In your theme's functions.php or custom plugin
add_action('cdswerx_plugin_loaded', function($plugin) {
    // Your custom initialization code
    if (function_exists('my_custom_cdswerx_extension')) {
        my_custom_cdswerx_extension($plugin);
    }
});
```

#### **Modifying Default Behavior**
```php
// Filter CSS migration files
add_filter('cdswerx_css_migration_files', function($files) {
    // Add custom CSS file to migration
    $files[] = get_stylesheet_directory() . '/custom-styles.css';
    return $files;
});
```

### **Database Customization**

#### **Custom Table Creation**
```php
// Hook into CDSWerx activation
add_action('cdswerx_plugin_loaded', function() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'cdswerx_custom_data';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        data_key varchar(100) NOT NULL,
        data_value longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});
```

---

## **Best Practices**

### **Development Workflow**

1. **Use Version Control**
   - Keep customizations in version control
   - Use branching for feature development
   - Document changes in commit messages

2. **Testing Protocol**
   - Test on staging environment first
   - Run CDSWerx QA tests before deployment
   - Verify multisite compatibility

3. **Backup Strategy**
   - Regular database backups
   - File system backups
   - Test backup restoration process

### **Performance Optimization**

1. **Asset Management**
   - Minimize HTTP requests
   - Optimize image sizes
   - Use CDN when possible
   - Enable gzip compression

2. **Database Optimization**
   - Regular database cleanup
   - Optimize database tables
   - Use appropriate indexing
   - Monitor slow queries

### **Security Considerations**

1. **Access Control**
   - Use strong GitHub tokens
   - Limit user capabilities
   - Regular security updates
   - Monitor admin access

2. **File Permissions**
   ```bash
   # Recommended WordPress file permissions
   find /path/to/wordpress -type d -exec chmod 755 {} \;
   find /path/to/wordpress -type f -exec chmod 644 {} \;
   chmod 600 wp-config.php
   ```

### **Maintenance Schedule**

#### **Daily Tasks**
- Monitor error logs
- Check performance metrics
- Review backup status

#### **Weekly Tasks**
- Check for updates
- Run QA tests
- Review security logs
- Optimize database

#### **Monthly Tasks**
- Full system backup
- Performance audit
- Security review
- Documentation updates

---

## **Frequently Asked Questions**

### **General Questions**

**Q: Can CDSWerx work with other themes?**
A: Yes, CDSWerx Plugin can work with other themes, but some features are optimized for CDSWerx Theme.

**Q: Is CDSWerx multisite compatible?**
A: Yes, CDSWerx is fully compatible with WordPress multisite installations.

**Q: How do I update CDSWerx components?**
A: Use the built-in GitHub update system in CDSWerx → GitHub Updates.

### **Technical Questions**

**Q: How do I add custom CSS?**
A: Use CDSWerx → Advanced CSS for site-wide styles, or add to your child theme.

**Q: Can I customize the admin interface?**
A: Yes, CDSWerx provides hooks and filters for admin customization.

**Q: How do I troubleshoot performance issues?**
A: Run CDSWerx → QA Testing → Performance Tests and check the results.

### **Development Questions**

**Q: How do I extend CDSWerx functionality?**
A: Use WordPress hooks and filters, or refer to the API documentation.

**Q: Can I contribute to CDSWerx development?**
A: Yes, CDSWerx is open for contributions. Contact the development team.

**Q: How do I report bugs?**
A: Use the GitHub issue tracker for the specific component.

---

## **Support & Resources**

### **Documentation**
- [API Documentation](API_DOCUMENTATION.md)
- [Developer Guide](DEVELOPER_GUIDE.md)
- [Changelog](../CHANGELOG.md)

### **Community**
- GitHub Discussions
- Development Forums
- User Community

### **Professional Support**
- Priority Support Available
- Custom Development Services
- Training and Consultation

---

*This user guide is maintained by the CDSWerx development team. For the latest version, visit the [CDSWerx Documentation Hub](../README.md).*