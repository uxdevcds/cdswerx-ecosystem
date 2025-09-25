<?php
/**
 * CDSWerx Typography Fallback Integration
 * Auto-generated typography fallback system
 * Generated: 2025-09-25 23:22:03
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Load typography fallback CSS when CDSWerx plugin is inactive.
 */
function cdswerx_child_load_typography_fallback() {
    // Check if CDSWerx plugin is active
    if (!class_exists('Cdswerx')) {
        // Load fallback CSS
        wp_enqueue_style(
            'cdswerx-child-typography-fallback',
            get_stylesheet_directory_uri() . '/assets/css/typography-fallback.css',
            [],
            '1.1.0'
        );
    }
}
add_action('wp_enqueue_scripts', 'cdswerx_child_load_typography_fallback', 5);

/**
 * Detect CDSWerx plugin status.
 */
function cdswerx_child_detect_plugin_status() {
    return class_exists('Cdswerx') && class_exists('Cdswerx_Typography_Sync');
}
