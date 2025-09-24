<?php
/**
 * Typography Sync Manager Class
 *
 * Handles automated synchronization of typography CSS between plugin and themes.
 * This class implements the read-only CSS extraction and automated fallback generation
 * system to ensure typography consistency regardless of plugin state.
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Typography Sync Manager Class
 *
 * Core responsibilities:
 * - Extract typography CSS from plugin without modification
 * - Generate theme fallback files automatically
 * - Handle plugin activation/deactivation typography sync
 * - Manage theme switching typography coordination
 * - Provide conditional loading logic for themes
 */
class Cdswerx_Typography_Sync {

    /**
     * The plugin name.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The plugin name.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Typography CSS rules extracted from plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $typography_css_rules    Parsed typography rules.
     */
    private $typography_css_rules;

    /**
     * Theme fallback file paths.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $theme_fallback_files    Theme fallback file locations.
     */
    private $theme_fallback_files;

    /**
     * Typography classes to extract and sync.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $typography_classes    Typography class selectors.
     */
    private $typography_classes;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->typography_css_rules = [];
        $this->theme_fallback_files = [];
        
        // Define typography classes to extract (read-only, preserve exactly)
        $this->typography_classes = [
            '.xl',
            '.lg', 
            '.sm',
            '.xs',
            'html' // For base font-size: 62.5%
        ];

        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks for typography sync automation.
     *
     * @since    1.0.0
     */
    private function init_hooks() {
        // Plugin lifecycle hooks
        add_action('plugins_loaded', [$this, 'init_typography_sync']);
        add_action('after_switch_theme', [$this, 'resync_typography_for_new_theme']);
        
        // Theme integration hooks
        add_action('wp_enqueue_scripts', [$this, 'conditional_typography_loading'], 5);
        
        // Admin hooks for manual sync
        add_action('wp_ajax_cdswerx_manual_typography_sync', [$this, 'handle_manual_sync']);
    }

    /**
     * Initialize typography sync system.
     * 
     * Main entry point for typography synchronization process.
     *
     * @since    1.0.0
     */
    public function init_typography_sync() {
        // Step 1: Extract typography CSS from plugin (read-only)
        $this->extract_typography_css_from_plugin();
        
        // Step 2: Generate theme fallback files if needed
        $this->generate_theme_fallback_files();
        
        // Step 3: Validate typography consistency
        $this->validate_typography_consistency();
        
        // Integration with Extensions Class
        do_action('cdswerx_typography_sync_initialized', $this->typography_css_rules);
    }

    /**
     * Extract typography CSS rules from plugin CSS file.
     * 
     * READ-ONLY operation - preserves all existing CSS exactly.
     * No modifications to source files.
     *
     * @since    1.0.0
     * @return   array    Extracted typography rules
     */
    public function extract_typography_css_from_plugin() {
        $plugin_css_file = plugin_dir_path(dirname(__FILE__)) . 'public/css/cdswerx-public.css';
        
        if (!file_exists($plugin_css_file)) {
            error_log('CDSWerx Typography Sync: Plugin CSS file not found at ' . $plugin_css_file);
            return [];
        }

        // Read CSS content (read-only)
        $css_content = file_get_contents($plugin_css_file);
        
        // Extract typography rules (preserve exactly)
        $this->typography_css_rules = $this->parse_typography_rules($css_content);
        
        return $this->typography_css_rules;
    }

    /**
     * Parse typography rules from CSS content.
     * 
     * Extracts specific typography classes while preserving all values exactly.
     *
     * @since    1.0.0
     * @param    string    $css_content    CSS file content
     * @return   array                     Parsed typography rules
     */
    private function parse_typography_rules($css_content) {
        $typography_rules = [];
        
        // Extract base font-size rule (preserve 62.5% exactly)
        if (preg_match('/html\s*\{\s*font-size:\s*62\.5%;\s*\}/i', $css_content, $matches)) {
            $typography_rules['base_font_size'] = trim($matches[0]);
        }

        // Extract typography class rules (preserve all values exactly)
        foreach ($this->typography_classes as $class) {
            if ($class === 'html') continue; // Already extracted above
            
            $pattern = '/' . preg_quote($class, '/') . '\s*,?\s*[^{]*\{[^}]*\}/s';
            if (preg_match($pattern, $css_content, $matches)) {
                $typography_rules[$class] = trim($matches[0]);
            }
        }

        return $typography_rules;
    }

    /**
     * Generate fallback CSS files for themes.
     * 
     * Creates identical typography CSS in theme directories for independent loading.
     *
     * @since    1.0.0
     */
    public function generate_theme_fallback_files() {
        $active_themes = $this->get_active_themes();
        
        foreach ($active_themes as $theme_path) {
            $this->create_theme_fallback_file($theme_path);
        }
    }

    /**
     * Create typography fallback file for specific theme.
     *
     * @since    1.0.0
     * @param    string    $theme_path    Path to theme directory
     */
    private function create_theme_fallback_file($theme_path) {
        // Create includes directory if it doesn't exist
        $includes_dir = $theme_path . '/includes';
        if (!file_exists($includes_dir)) {
            wp_mkdir_p($includes_dir);
        }

        // Create assets/css directory if it doesn't exist
        $css_dir = $theme_path . '/assets/css';
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }

        // Generate fallback CSS file content
        $fallback_css_content = $this->generate_fallback_css_content();
        
        // Write CSS file
        $css_file_path = $css_dir . '/typography-fallback.css';
        file_put_contents($css_file_path, $fallback_css_content);

        // Detect theme type (parent vs child)
        $is_child_theme = (basename($theme_path) === get_stylesheet());
        $theme_type = $is_child_theme ? 'child' : 'parent';

        // Generate PHP integration file with theme-specific function name
        $php_file_content = $this->generate_fallback_php_content($theme_type);
        $php_file_path = $includes_dir . '/typography-fallback.php';
        file_put_contents($php_file_path, $php_file_content);

        // Track fallback files
        $this->theme_fallback_files[] = [
            'theme' => basename($theme_path),
            'css_file' => $css_file_path,
            'php_file' => $php_file_path
        ];
    }

    /**
     * Generate fallback CSS content from extracted typography rules.
     *
     * @since    1.0.0
     * @return   string    CSS content for fallback file
     */
    private function generate_fallback_css_content() {
        $css_content = "/*\n";
        $css_content .= " * CDSWerx Typography Fallback CSS\n";
        $css_content .= " * Auto-generated from plugin typography rules\n";
        $css_content .= " * Preserves typography when CDSWerx plugin is inactive\n";
        $css_content .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
        $css_content .= " */\n\n";

        // Add base font-size rule (preserve 62.5% exactly)
        if (isset($this->typography_css_rules['base_font_size'])) {
            $css_content .= $this->typography_css_rules['base_font_size'] . "\n\n";
        }

        // Add typography class rules (preserve all values exactly)
        foreach ($this->typography_classes as $class) {
            if ($class === 'html') continue; // Already added above
            
            if (isset($this->typography_css_rules[$class])) {
                $css_content .= $this->typography_css_rules[$class] . "\n\n";
            }
        }

        return $css_content;
    }

    /**
     * Generate fallback PHP integration file content.
     *
     * @since    1.0.0
     * @param    string    $theme_type    Theme type ('parent' or 'child')
     * @return   string    PHP content for fallback integration
     */
    private function generate_fallback_php_content($theme_type = 'parent') {
        $php_content = "<?php\n";
        $php_content .= "/**\n";
        $php_content .= " * CDSWerx Typography Fallback Integration\n";
        $php_content .= " * Auto-generated typography fallback system\n";
        $php_content .= " * Generated: " . date('Y-m-d H:i:s') . "\n";
        $php_content .= " */\n\n";
        $php_content .= "// Prevent direct access\n";
        $php_content .= "if (! defined('ABSPATH')) {\n";
        $php_content .= "    exit;\n";
        $php_content .= "}\n\n";
        // Generate theme-specific function name
        $function_name = ($theme_type === 'child') ? 'cdswerx_child_load_typography_fallback' : 'cdswerx_parent_load_typography_fallback';
        
        $php_content .= "/**\n";
        $php_content .= " * Load typography fallback CSS when CDSWerx plugin is inactive.\n";
        $php_content .= " */\n";
        $php_content .= "function {$function_name}() {\n";
        $php_content .= "    // Check if CDSWerx plugin is active\n";
        $php_content .= "    if (!class_exists('Cdswerx')) {\n";
        $php_content .= "        // Load fallback CSS\n";
        // Generate theme-specific CSS path and handle name
        $css_path = ($theme_type === 'child') ? 'get_stylesheet_directory_uri()' : 'get_template_directory_uri()';
        $handle_name = ($theme_type === 'child') ? 'cdswerx-child-typography-fallback' : 'cdswerx-parent-typography-fallback';
        $priority = ($theme_type === 'child') ? 5 : 10;
        
        $php_content .= "        wp_enqueue_style(\n";
        $php_content .= "            '{$handle_name}',\n";
        $php_content .= "            {$css_path} . '/assets/css/typography-fallback.css',\n";
        $php_content .= "            [],\n";
        $php_content .= "            '" . $this->version . "'\n";
        $php_content .= "        );\n";
        $php_content .= "    }\n";
        $php_content .= "}\n";
        $php_content .= "add_action('wp_enqueue_scripts', '{$function_name}', {$priority});\n\n";
        
        // Generate theme-specific detection function name
        $detection_function_name = ($theme_type === 'child') ? 'cdswerx_child_detect_plugin_status' : 'cdswerx_parent_detect_plugin_status';
        
        $php_content .= "/**\n";
        $php_content .= " * Detect CDSWerx plugin status.\n";
        $php_content .= " */\n";
        $php_content .= "function {$detection_function_name}() {\n";
        $php_content .= "    return class_exists('Cdswerx') && class_exists('Cdswerx_Typography_Sync');\n";
        $php_content .= "}\n";

        return $php_content;
    }

    /**
     * Get list of active themes that need fallback files.
     *
     * @since    1.0.0
     * @return   array    Theme directory paths
     */
    private function get_active_themes() {
        $theme_paths = [];
        
        // Current active theme
        $current_theme = get_stylesheet_directory();
        $theme_paths[] = $current_theme;
        
        // Parent theme if using child theme
        if (is_child_theme()) {
            $parent_theme = get_template_directory();
            if ($parent_theme !== $current_theme) {
                $theme_paths[] = $parent_theme;
            }
        }

        return $theme_paths;
    }

    /**
     * Handle theme switching - resync typography for new theme.
     *
     * @since    1.0.0
     */
    public function resync_typography_for_new_theme() {
        // Re-extract CSS rules
        $this->extract_typography_css_from_plugin();
        
        // Generate fallback for new theme
        $this->generate_theme_fallback_files();
        
        // Clear any cached CSS
        do_action('cdswerx_typography_theme_switched');
    }

    /**
     * Conditional typography loading based on plugin status.
     *
     * @since    1.0.0
     */
    public function conditional_typography_loading() {
        // This method coordinates with theme fallback files
        // Theme files handle the actual conditional loading logic
        do_action('cdswerx_typography_conditional_loading_check');
    }

    /**
     * Validate typography consistency across plugin and themes.
     *
     * @since    1.0.0
     * @return   bool    True if consistent, false otherwise
     */
    public function validate_typography_consistency() {
        $validation_results = [];
        
        // Check if base font-size rule exists
        $validation_results['base_font_size'] = isset($this->typography_css_rules['base_font_size']);
        
        // Check if typography classes exist
        foreach ($this->typography_classes as $class) {
            if ($class === 'html') continue;
            $validation_results['class_' . str_replace('.', '', $class)] = isset($this->typography_css_rules[$class]);
        }
        
        // Check theme fallback files
        $validation_results['theme_fallbacks'] = !empty($this->theme_fallback_files);
        
        return !in_array(false, $validation_results, true);
    }

    /**
     * Handle manual typography sync via admin AJAX.
     *
     * @since    1.0.0
     */
    public function handle_manual_sync() {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'], 'cdswerx_typography_sync') || 
            !current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        // Perform full sync
        $this->init_typography_sync();
        
        wp_send_json_success([
            'message' => 'Typography sync completed successfully',
            'rules_count' => count($this->typography_css_rules),
            'theme_files' => count($this->theme_fallback_files)
        ]);
    }

    /**
     * Get extracted typography rules.
     *
     * @since    1.0.0
     * @return   array    Typography CSS rules
     */
    public function get_typography_rules() {
        return $this->typography_css_rules;
    }

    /**
     * Get theme fallback file information.
     *
     * @since    1.0.0
     * @return   array    Theme fallback file details
     */
    public function get_theme_fallback_files() {
        return $this->theme_fallback_files;
    }
}
