# Typography Sync Fatal Error Fix - Implementation Plan

**Created:** September 24, 2025  
**Issue:** PHP Fatal error - Cannot redeclare cdswerx_load_typography_fallback()  
**Root Cause:** Typography Sync system generates identical function names in both parent and child themes

## **Problem Analysis**

### Fatal Error Details
```
[24-Sep-2025 15:21:49 UTC] PHP Fatal error: Cannot redeclare cdswerx_load_typography_fallback() 
(previously declared in /wp-content/themes/cdswerx-theme-child/includes/typography-fallback.php:16) 
in /wp-content/themes/cdswerx-theme/includes/typography-fallback.php on line 16
```

### Root Cause
- CDSWerx Typography Sync system auto-generates `typography-fallback.php` files
- Both parent and child themes get identical function name: `cdswerx_load_typography_fallback()`
- WordPress loads both parent and child theme functions causing redeclaration conflict

## **Implementation Plan**

### **PRIMARY FIX: CDSWerx Plugin (wp-content)**

#### **File 1: Typography Sync Manager Class**
**Location**: `/wp-content/plugins/cdswerx-plugin/includes/class-cdswerx-typography-sync.php`

**Method 1: `create_theme_fallback_file()`** - Line ~200
- **ADD**: Theme type detection parameter
- **MODIFY**: Pass theme context to PHP generator method
- **DETECT**: Parent vs child theme based on `$theme_path`

**Method 2: `generate_fallback_php_content()`** - Line ~281
- **ADD**: `$theme_type` parameter to method signature
- **MODIFY**: Function name generation logic
- **CHANGE**: From `cdswerx_load_typography_fallback()` to theme-specific names
- **UPDATE**: CSS path logic based on theme type

### **CLEANUP: Remove Existing Conflicted Files (wp-content)**

#### **File 2: CDSWerx Theme Typography Fallback**
**Location**: `/wp-content/themes/cdswerx-theme/includes/typography-fallback.php`
- **ACTION**: DELETE existing file
- **REASON**: Will be regenerated with correct function name

#### **File 3: CDSWerx Child Theme Typography Fallback**
**Location**: `/wp-content/themes/cdswerx-theme-child/includes/typography-fallback.php`
- **ACTION**: DELETE existing file  
- **REASON**: Will be regenerated with correct function name

### **TRIGGER REGENERATION: Force Typography Sync**

#### **Step 1: Clear Typography Cache**
- **ACTION**: Trigger `resync_typography_for_new_theme()` method
- **METHOD**: Through WordPress admin or direct PHP execution

#### **Step 2: Regenerate Theme Files**
- **ACTION**: Run `generate_theme_fallback_files()` with updated logic
- **RESULT**: Creates new files with theme-specific function names

## **Specific Code Changes Required**

### **Change 1: Method Signature Update**
```php
// CURRENT (Line ~281):
private function generate_fallback_php_content() {

// UPDATED:
private function generate_fallback_php_content($theme_type = 'parent') {
```

### **Change 2: Function Name Generation**
```php
// CURRENT (Line ~298):
$php_content .= "function cdswerx_load_typography_fallback() {\n";

// UPDATED:
$function_name = ($theme_type === 'child') ? 'cdswerx_child_load_typography_fallback' : 'cdswerx_parent_load_typography_fallback';
$php_content .= "function {$function_name}() {\n";
```

### **Change 3: CSS Path Logic**
```php
// CURRENT (Line ~304):
$php_content .= "            get_stylesheet_directory_uri() . '/assets/css/typography-fallback.css',\n";

// UPDATED:
$css_path = ($theme_type === 'child') ? 'get_stylesheet_directory_uri()' : 'get_template_directory_uri()';
$php_content .= "            {$css_path} . '/assets/css/typography-fallback.css',\n";
```

### **Change 4: Theme Type Detection**
```php
// ADD TO create_theme_fallback_file() method (Line ~205):
$is_child_theme = (basename($theme_path) === get_stylesheet());
$theme_type = $is_child_theme ? 'child' : 'parent';
$php_file_content = $this->generate_fallback_php_content($theme_type);
```

## **Expected File Outputs**

### **Generated Parent Theme File**
**Location**: `/wp-content/themes/cdswerx-theme/includes/typography-fallback.php`
```php
function cdswerx_parent_load_typography_fallback() {
    if (!class_exists('Cdswerx')) {
        wp_enqueue_style(
            'cdswerx-parent-typography-fallback',
            get_template_directory_uri() . '/assets/css/typography-fallback.css'
        );
    }
}
add_action('wp_enqueue_scripts', 'cdswerx_parent_load_typography_fallback', 10);
```

### **Generated Child Theme File**
**Location**: `/wp-content/themes/cdswerx-theme-child/includes/typography-fallback.php`
```php
function cdswerx_child_load_typography_fallback() {
    if (!class_exists('Cdswerx')) {
        wp_enqueue_style(
            'cdswerx-child-typography-fallback',
            get_stylesheet_directory_uri() . '/assets/css/typography-fallback.css'
        );
    }
}
add_action('wp_enqueue_scripts', 'cdswerx_child_load_typography_fallback', 5);
```

## **Implementation Sequence**

1. **MODIFY**: `class-cdswerx-typography-sync.php` in wp-content plugin
2. **DELETE**: Existing conflicted typography-fallback.php files from themes  
3. **TRIGGER**: Typography sync regeneration
4. **VERIFY**: New files generated with unique function names
5. **TEST**: WordPress loads without fatal error

**Files Modified**: 1 (Typography Sync Manager)  
**Files Deleted**: 2 (Existing conflicted files)  
**Files Generated**: 2 (New theme-specific files)  
**Total wp-content Changes**: 5 files affected

## **Success Criteria**

- [ ] No PHP fatal errors in debug.log
- [ ] Typography fallback functions have unique names
- [ ] Child theme typography loads correctly when plugin inactive
- [ ] Parent theme typography loads correctly when plugin inactive
- [ ] WordPress theme options show CDSWerx Child Theme properly

## **Rollback Plan**

If implementation fails:
1. Restore original `class-cdswerx-typography-sync.php` from backup
2. Restore original typography-fallback.php files from backup
3. Clear all typography cache and regenerate

**End of Implementation Plan**
