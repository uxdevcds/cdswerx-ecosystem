<?php
/**
 * Typography CSS Reader Class
 *
 * Provides read-only access to typography CSS without file modification.
 * This class handles CSS parsing, rule extraction, and validation for
 * the Typography Sync Manager system.
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
 * Typography CSS Reader Class
 *
 * Core responsibilities:
 * - Read typography CSS rules without modification
 * - Parse CSS declarations and validate structure
 * - Extract font-size mappings for typography classes
 * - Provide integration points with Extensions Class
 */
class Cdswerx_Typography_CSS_Reader {

    /**
     * Typography classes to read and parse.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $typography_classes    Typography class selectors.
     */
    private $typography_classes;

    /**
     * Parsed CSS rules cache.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $parsed_rules_cache    Cached parsed rules.
     */
    private $parsed_rules_cache;

    /**
     * Font size mappings extracted from CSS.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $font_size_mappings    Font size values for each class.
     */
    private $font_size_mappings;

    /**
     * Initialize the CSS Reader.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->typography_classes = [
            '.xl',
            '.lg', 
            '.sm',
            '.xs',
            'html' // For base font-size: 62.5%
        ];
        
        $this->parsed_rules_cache = [];
        $this->font_size_mappings = [];
    }

    /**
     * Read typography rules from CSS file.
     * 
     * READ-ONLY operation - no file modifications.
     *
     * @since    1.0.0
     * @param    string    $css_file_path    Path to CSS file
     * @return   array                       Typography rules
     */
    public function read_typography_rules($css_file_path) {
        if (!file_exists($css_file_path)) {
            error_log('CDSWerx Typography CSS Reader: CSS file not found at ' . $css_file_path);
            return [];
        }

        // Check cache first
        $cache_key = md5($css_file_path . filemtime($css_file_path));
        if (isset($this->parsed_rules_cache[$cache_key])) {
            return $this->parsed_rules_cache[$cache_key];
        }

        // Read CSS content (read-only)
        $css_content = file_get_contents($css_file_path);
        
        // Parse CSS declarations
        $typography_rules = $this->parse_css_declarations($css_content);
        
        // Extract font-size mappings
        $this->extract_font_size_mappings($typography_rules);
        
        // Cache results
        $this->parsed_rules_cache[$cache_key] = $typography_rules;
        
        return $typography_rules;
    }

    /**
     * Parse CSS declarations from content.
     * 
     * Extracts typography-related CSS rules while preserving exact formatting.
     *
     * @since    1.0.0
     * @param    string    $css_content    CSS file content
     * @return   array                     Parsed CSS declarations
     */
    public function parse_css_declarations($css_content) {
        $declarations = [];
        
        // Extract base font-size rule (preserve 62.5% exactly)
        $base_font_pattern = '/html\s*\{\s*[^}]*font-size:\s*62\.5%[^}]*\}/is';
        if (preg_match($base_font_pattern, $css_content, $matches)) {
            $declarations['html'] = [
                'selector' => 'html',
                'raw_css' => trim($matches[0]),
                'properties' => $this->extract_css_properties(trim($matches[0]))
            ];
        }

        // Extract typography class rules
        foreach ($this->typography_classes as $class) {
            if ($class === 'html') continue; // Already extracted above
            
            $class_pattern = $this->build_class_pattern($class);
            if (preg_match($class_pattern, $css_content, $matches)) {
                $declarations[$class] = [
                    'selector' => $class,
                    'raw_css' => trim($matches[0]),
                    'properties' => $this->extract_css_properties(trim($matches[0]))
                ];
            }
        }

        return $declarations;
    }

    /**
     * Build regex pattern for CSS class extraction.
     *
     * @since    1.0.0
     * @param    string    $class    CSS class selector
     * @return   string              Regex pattern
     */
    private function build_class_pattern($class) {
        $escaped_class = preg_quote($class, '/');
        
        // Pattern to match class with multiple selectors and full CSS block
        // Example: .xl, body.xl, p.xl, p.xl a { ... }
        return '/' . $escaped_class . '\s*,?\s*[^{]*\{[^}]*\}/s';
    }

    /**
     * Extract CSS properties from a CSS rule.
     *
     * @since    1.0.0
     * @param    string    $css_rule    Complete CSS rule
     * @return   array                  Extracted properties
     */
    private function extract_css_properties($css_rule) {
        $properties = [];
        
        // Extract content within braces
        if (preg_match('/\{([^}]*)\}/', $css_rule, $matches)) {
            $css_declarations = $matches[1];
            
            // Split by semicolon and parse individual properties
            $property_lines = explode(';', $css_declarations);
            
            foreach ($property_lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Split property and value
                if (strpos($line, ':') !== false) {
                    list($property, $value) = explode(':', $line, 2);
                    $properties[trim($property)] = trim($value);
                }
            }
        }
        
        return $properties;
    }

    /**
     * Extract font-size mappings from parsed rules.
     *
     * @since    1.0.0
     * @param    array    $typography_rules    Parsed typography rules
     */
    private function extract_font_size_mappings($typography_rules) {
        $this->font_size_mappings = [];
        
        foreach ($typography_rules as $selector => $rule_data) {
            if (isset($rule_data['properties']['font-size'])) {
                $this->font_size_mappings[$selector] = $rule_data['properties']['font-size'];
            }
        }
    }

    /**
     * Validate CSS structure for typography rules.
     *
     * @since    1.0.0
     * @param    array    $typography_rules    Typography rules to validate
     * @return   array                         Validation results
     */
    public function validate_css_structure($typography_rules) {
        $validation_results = [
            'valid' => true,
            'errors' => [],
            'warnings' => []
        ];

        // Check for base font-size rule
        if (!isset($typography_rules['html'])) {
            $validation_results['errors'][] = 'Base font-size rule (html) not found';
            $validation_results['valid'] = false;
        } else {
            // Validate base font-size value
            $html_props = $typography_rules['html']['properties'] ?? [];
            if (!isset($html_props['font-size']) || $html_props['font-size'] !== '62.5%') {
                $validation_results['errors'][] = 'Base font-size should be 62.5%';
                $validation_results['valid'] = false;
            }
        }

        // Check typography classes
        foreach ($this->typography_classes as $class) {
            if ($class === 'html') continue;
            
            if (!isset($typography_rules[$class])) {
                $validation_results['warnings'][] = "Typography class {$class} not found";
            } else {
                // Validate font-size property exists
                $props = $typography_rules[$class]['properties'] ?? [];
                if (!isset($props['font-size'])) {
                    $validation_results['warnings'][] = "Typography class {$class} missing font-size property";
                }
            }
        }

        return $validation_results;
    }

    /**
     * Get font-size mappings for typography classes.
     *
     * @since    1.0.0
     * @return   array    Font-size values mapped to selectors
     */
    public function get_font_size_mappings() {
        return $this->font_size_mappings;
    }

    /**
     * Get cached parsed rules.
     *
     * @since    1.0.0
     * @return   array    Cached CSS rules
     */
    public function get_cached_rules() {
        return $this->parsed_rules_cache;
    }

    /**
     * Clear CSS parsing cache.
     *
     * @since    1.0.0
     */
    public function clear_cache() {
        $this->parsed_rules_cache = [];
        $this->font_size_mappings = [];
    }

    /**
     * Integration point for Extensions Class init_typography_sync() method.
     * 
     * This method provides the interface for the Extensions Class to
     * initialize typography synchronization.
     *
     * @since    1.0.0
     * @param    string    $css_file_path    Path to CSS file to read
     * @return   array                       Typography sync data
     */
    public function init_typography_sync_integration($css_file_path) {
        // Read typography rules
        $typography_rules = $this->read_typography_rules($css_file_path);
        
        // Validate structure
        $validation = $this->validate_css_structure($typography_rules);
        
        // Prepare sync data
        $sync_data = [
            'typography_rules' => $typography_rules,
            'font_size_mappings' => $this->font_size_mappings,
            'validation' => $validation,
            'css_file_path' => $css_file_path,
            'last_read' => current_time('timestamp')
        ];
        
        // Hook for Extensions Class integration
        do_action('cdswerx_typography_css_reader_initialized', $sync_data);
        
        return $sync_data;
    }

    /**
     * Get typography rule by selector.
     *
     * @since    1.0.0
     * @param    string    $selector    CSS selector
     * @return   array|null             Typography rule or null if not found
     */
    public function get_typography_rule($selector) {
        foreach ($this->parsed_rules_cache as $cache_data) {
            if (isset($cache_data[$selector])) {
                return $cache_data[$selector];
            }
        }
        
        return null;
    }

    /**
     * Check if typography system is properly configured.
     *
     * @since    1.0.0
     * @param    string    $css_file_path    Path to CSS file
     * @return   bool                        True if properly configured
     */
    public function is_typography_system_configured($css_file_path) {
        $rules = $this->read_typography_rules($css_file_path);
        $validation = $this->validate_css_structure($rules);
        
        return $validation['valid'] && count($rules) >= 2; // At least html + one typography class
    }
}