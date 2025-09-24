<?php
/**
 * CDSWerx Typography Fallback Integration
 * Auto-generated typography fallback system
 * Generated: 2025-09-24 21:13:13
 */

// Prevent direct access
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Load typography fallback CSS when CDSWerx plugin is inactive.
 */
function cdswerx_parent_load_typography_fallback() {
    // Check if CDSWerx plugin is active
    if (!class_exists('Cdswerx')) {
        // Load fallback CSS
        wp_enqueue_style(
            'cdswerx-parent-typography-fallback',
            get_template_directory_uri() . '/assets/css/typography-fallback.css',
            [],
            '1.0.3'
        );
    }
}
add_action('wp_enqueue_scripts', 'cdswerx_parent_load_typography_fallback', 10);

/**
 * Detect CDSWerx plugin status.
 */
function cdswerx_parent_detect_plugin_status() {
    return class_exists('Cdswerx') && class_exists('Cdswerx_Typography_Sync');
}
