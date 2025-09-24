<?php
/**
 * Typography Sync Regeneration Script
 * Triggers regeneration of typography fallback files with the new theme-specific function names
 */

// WordPress bootstrap
require_once('wp-config.php');
require_once('wp-load.php');

echo "Starting Typography Sync Regeneration...\n";

// Check if CDSWerx plugin is active
if (!class_exists('Cdswerx')) {
    echo "ERROR: CDSWerx plugin is not active\n";
    exit(1);
}

// Check if Typography Sync class exists
if (!class_exists('Cdswerx_Typography_Sync')) {
    echo "ERROR: Cdswerx_Typography_Sync class not found\n";
    exit(1);
}

// Create Typography Sync instance
$typography_sync = new Cdswerx_Typography_Sync('cdswerx', '1.0.3');

echo "Initializing typography sync system...\n";

// Trigger full sync
$typography_sync->init_typography_sync();

echo "Typography sync regeneration completed!\n";
echo "Check the following files for new theme-specific functions:\n";
echo "- /wp-content/themes/cdswerx-theme/includes/typography-fallback.php\n";
echo "- /wp-content/themes/cdswerx-theme-child/includes/typography-fallback.php\n";
?>
