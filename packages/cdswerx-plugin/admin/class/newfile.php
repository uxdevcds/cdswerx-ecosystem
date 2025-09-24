<?php
/**
 * Plugin Name: CDSWerx Shortcode Test
 * Description: A simple plugin to test if shortcodes are working properly
 * Version: 1.0
 * Author: CDSWerx
 */

// Prevent direct access
if (!defined('ABSPATH')) exit;

// Register the test shortcode
function cdswerx_test_shortcode_function($atts = [], $content = null) {
    // Log the shortcode call
    error_log('CDSWerx Shortcode Test: Test shortcode called');
    
    // Return a simple test message
    return '<div style="border: 2px solid green; padding: 10px; margin: 10px 0; background: #eaffea;">
        <p><strong>CDSWerx Test Shortcode</strong> is working!</p>
        <p>Current time: ' . current_time('mysql') . '</p>
        <p>Current date: ' . date_i18n('Y-m-d') . '</p>
    </div>';
}
add_shortcode('cdswerx_test_shortcode', 'cdswerx_test_shortcode_function');

// Register the time shortcode
function cdswerx_time_shortcode_function($atts = [], $content = null) {
    error_log('CDSWerx Shortcode Test: Time shortcode called');
    return date_i18n('h:i:s A');
}
add_shortcode('cdswerx_time', 'cdswerx_time_shortcode_function');

// Add a simple admin page to explain usage
function cdswerx_shortcode_test_menu() {
    add_submenu_page(
        'tools.php',
        'CDSWerx Shortcode Test',
        'Shortcode Test',
        'manage_options',
        'cdswerx-shortcode-test',
        'cdswerx_shortcode_test_page'
    );
}
add_action('admin_menu', 'cdswerx_shortcode_test_menu');

function cdswerx_shortcode_test_page() {
    ?>
    <div class="wrap">
        <h1>CDSWerx Shortcode Test</h1>
        <p>This plugin provides simple test shortcodes to verify that WordPress shortcode processing is working properly.</p>
        <h2>Available Shortcodes</h2>
        <ul>
            <li><code>[cdswerx_test_shortcode]</code> - Displays a test message with current time</li>
            <li><code>[cdswerx_time]</code> - Displays the current time</li>
        </ul>
        <h2>Testing Instructions</h2>
        <p>Add these shortcodes to a page or post and view it to see if they work.</p>
        <h3>Troubleshooting</h3>
        <p>If these shortcodes work but the CDSWerx plugin shortcodes don't, the issue is within the CDSWerx plugin.</p>
        <p>If none of the shortcodes work, the issue may be with your theme or another plugin interfering with shortcode processing.</p>
    </div>
    <?php
}
