<?php
/**
 * Typography System Test Script
 * 
 * Tests the automated typography sync functionality
 * to ensure proper CSS extraction and fallback generation.
 */

// Test Typography CSS Reader
echo "=== Typography CSS Reader Test ===\n";
$css_reader = new Cdswerx_Typography_CSS_Reader();
$plugin_css_file = '/public/css/cdswerx-public.css';

if (file_exists($plugin_css_file)) {
    $typography_rules = $css_reader->read_typography_rules($plugin_css_file);
    echo "Found " . count($typography_rules) . " typography rules\n";
    
    $validation = $css_reader->validate_css_structure($typography_rules);
    echo "Validation: " . ($validation['valid'] ? "PASSED" : "FAILED") . "\n";
    
    if (!empty($validation['errors'])) {
        echo "Errors: " . implode(', ', $validation['errors']) . "\n";
    }
    
    $font_mappings = $css_reader->get_font_size_mappings();
    echo "Font size mappings: " . count($font_mappings) . "\n";
    
} else {
    echo "ERROR: Plugin CSS file not found\n";
}

echo "\n=== Typography Sync Manager Test ===\n";
$sync_manager = new Cdswerx_Typography_Sync('cdswerx', '1.0.0');

// Test CSS extraction
$extracted_rules = $sync_manager->extract_typography_css_from_plugin();
echo "Extracted " . count($extracted_rules) . " typography rules\n";

// Test validation
$validation_result = $sync_manager->validate_typography_consistency();
echo "Consistency validation: " . ($validation_result ? "PASSED" : "FAILED") . "\n";

// Test theme fallback file generation
echo "Generating theme fallback files...\n";
$sync_manager->generate_theme_fallback_files();

$fallback_files = $sync_manager->get_theme_fallback_files();
echo "Generated " . count($fallback_files) . " theme fallback file sets\n";

echo "\n=== Test Complete ===\n";