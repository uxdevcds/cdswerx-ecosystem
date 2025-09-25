<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


/**
 * CDSWerx Admin Dashboard Partial
 *
 * Provides the main admin dashboard view for the CDSWerx plugin.
 *
 * Purpose & Responsibilities:
 * - Renders the main overview dashboard for the plugin administration.
 * - Displays key metrics, status information, and quick action links.
 * - Provides access to plugin features, settings, and documentation.
 * - Shows system health, compatibility status, and important notices.
 *
 * Sections & Code Overview:
 * 1. Header Section:
 *   - Displays plugin header and logo.
 * 2. Main Content:
 *   - Overview Tab:
 *     - Shows plugin status and key metrics.
 *     - Displays quick actions and feature highlights.
 *     - Provides recent activity and important notices.
 *   - Status Tab:
 *     - System compatibility and requirements check.
 *     - Environment information and diagnostic tools.
 *     - Performance metrics and optimization suggestions.
 * 3. Dashboard Widgets:
 *   - Quick Stats Widget: Shows summary statistics and status.
 *   - Recent Activity Widget: Displays recent plugin-related events.
 *   - Getting Started Widget: Provides onboarding guidance and tips.
 * 4. Scripts:
 *   - Tab switching functionality.
 *   - Dashboard widget interaction and refresh logic.
 *   - System diagnostics and status check functionality.
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 * @package    Cdswerx
 * @subpackage Cdswerx/admin/partials
 */
?>
<div id="admin-dashboard" class="cdswerx-wrap">

    <?php $this->render_cdswerx_admin_section("header", $logo_url); ?>

    <div class="cdswerx-admin main-content uk-section uk-padding-remove">

        <div class="cdswerx-admin content-header uk-card uk-background-default uk-card-body">
            <h2><?php _e("CDSWerx Settings", "cdswerx"); ?></h2>
            <p><?php _e(
                    "This is a placeholder & dummy display for now. To be continued.....<br>Configure your CDSWerx plugin settings below.",
                    "cdswerx",
                ); ?></p>
        </div>
        <div class="cdswerx-admin content-body uk-card uk-background-default uk-card-body uk-padding-remove-top">
            <div class="cdswerx-tabs">
                <ul>
                    <li><a href="#general" class="active"><?php _e(
                                                                "General",
                                                                "cdswerx",
                                                            ); ?></a></li>
                    <li><a href="#display"><?php _e(
                                                "CDS Theme",
                                                "cdswerx",
                                            ); ?></a></li>
                    <li><a href="#wordpress-core" class="active"><?php _e(
                                                                        "WordPress Core",
                                                                        "cdswerx",
                                                                    ); ?></a></li>
                    <li><a href="#advanced"><?php _e(
                                                "Advanced",
                                                "cdswerx",
                                            ); ?></a></li>

                    <li><a href="#import-export"><?php _e(
                                                        "Import/Export",
                                                        "cdswerx",
                                                    ); ?></a></li>
                </ul>
            </div>

            <!-- General Tab -->
            <div id="general" class="cdswerx-tab-content">
                <form id="cdswerx-settings-form" class="uk-form-horizontal" method="post" action="options.php">
                    <?php settings_fields("cdswerx_settings_group"); ?>
                    <div class="uk-form-horizontal">
                        <?php foreach ($this->cdswerx_fields as $field) {
                            echo '<div class="uk-margin uk-grid-small uk-child-width-auto uk-grid" uk-grid>';
                            $this->render_field($field);
                            echo "</div>";
                        } ?>
                    </div>
                    
                    <!-- Plugin Information Section -->
                    <div class="uk-card uk-card-default uk-card-body uk-margin">
                        <h4 class="uk-card-title"><?php _e("Plugin Information", "cdswerx"); ?></h4>
                        <div class="user-grid-container">
                            <div class="user-grid-row">
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Information:</span>
                                    <span class="mobile-value">
                                        <label class="uk-form-label">
                                            <?php _e("Plugin Version", "cdswerx"); ?>
                                        </label>
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Details:</span>
                                    <div class="mobile-value">
                                        <div class="uk-text-meta">
                                            <?php echo esc_html(CDSWERX_VERSION); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label"><?php _e("Status:", "cdswerx"); ?></span>
                                    <span class="mobile-value">
                                        <span class="uk-label uk-label-success">✓ CURRENT</span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="user-grid-row">
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Information:</span>
                                    <span class="mobile-value">
                                        <label class="uk-form-label">
                                            <?php _e("WordPress Version", "cdswerx"); ?>
                                        </label>
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Details:</span>
                                    <div class="mobile-value">
                                        <div class="uk-text-meta">
                                            <?php echo esc_html(get_bloginfo("version")); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label"><?php _e("Status:", "cdswerx"); ?></span>
                                    <span class="mobile-value">
                                        <span class="uk-label uk-label-success">✓ ACTIVE</span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="user-grid-row">
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Information:</span>
                                    <span class="mobile-value">
                                        <label class="uk-form-label">
                                            <?php _e("PHP Version", "cdswerx"); ?>
                                        </label>
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Details:</span>
                                    <div class="mobile-value">
                                        <div class="uk-text-meta">
                                            <?php echo esc_html(PHP_VERSION); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label"><?php _e("Status:", "cdswerx"); ?></span>
                                    <span class="mobile-value">
                                        <span class="uk-label uk-label-success">✓ COMPATIBLE</span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="user-grid-row">
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Information:</span>
                                    <span class="mobile-value">
                                        <label class="uk-form-label">
                                            <?php _e("Installation Date", "cdswerx"); ?>
                                        </label>
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Details:</span>
                                    <div class="mobile-value">
                                        <div class="uk-text-meta">
                                            <?php
                                            $options = get_option("cdswerx_options", []);
                                            echo isset($options["activation_date"])
                                                ? esc_html(date("F j, Y", strtotime($options["activation_date"])))
                                                : __("Unknown", "cdswerx");
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label"><?php _e("Status:", "cdswerx"); ?></span>
                                    <span class="mobile-value">
                                        <span class="uk-label uk-label-primary">INSTALLED</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- CDS Theme Tab -->
            <div id="display" class="cdswerx-tab-content" style="display: none;">
                <div class="cdswerx-settings-group">
                    <h3><?php _e("CDSWerx Theme Ecosystem", "cdswerx"); ?></h3>
                    <?php 
                    // Get theme ecosystem status
                    $ecosystem_status = apply_filters('cdswerx_theme_ecosystem_status', []);
                    ?>
                    
                    <!-- Theme Status Overview -->
                    <div class="cdswerx-theme-status-overview" style="background: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #0073aa;">
                        <h4><?php _e("Current Theme Status", "cdswerx"); ?></h4>
                        <p><strong><?php _e("Active Theme:", "cdswerx"); ?></strong> 
                            <?php echo esc_html($ecosystem_status['current_theme_name'] ?? wp_get_theme()->get('Name')); ?>
                        </p>
                        
                        <?php if (!empty($ecosystem_status['recommended_action']) && $ecosystem_status['recommended_action']['action'] !== 'none'): ?>
                            <div class="notice notice-<?php echo $ecosystem_status['recommended_action']['priority'] === 'high' ? 'warning' : 'info'; ?> inline">
                                <p><?php echo esc_html($ecosystem_status['recommended_action']['message']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Available Themes -->
                    <div class="cdswerx-available-themes">
                        <h4><?php _e("Available CDSWerx Themes", "cdswerx"); ?></h4>
                        <?php if (!empty($ecosystem_status['available_themes'])): ?>
                            <table class="uk-table">
                                <thead>
                                    <tr>
                                        <th><?php _e("Theme", "cdswerx"); ?></th>
                                        <th><?php _e("Status", "cdswerx"); ?></th>
                                        <th><?php _e("Action", "cdswerx"); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ecosystem_status['available_themes'] as $slug => $theme_info): ?>
                                        <?php if (strpos($slug, 'cdswerx') !== false): ?>
                                            <tr>
                                                <td><?php echo esc_html($theme_info['name']); ?></td>
                                                <td>
                                                    <?php if ($theme_info['active']): ?>
                                                        <span style="color: #46b450; font-weight: bold;"><?php _e("Active", "cdswerx"); ?></span>
                                                    <?php elseif ($theme_info['installed']): ?>
                                                        <span style="color: #0073aa;"><?php _e("Installed", "cdswerx"); ?></span>
                                                    <?php else: ?>
                                                        <span style="color: #999;"><?php _e("Not Installed", "cdswerx"); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($theme_info['installed'] && !$theme_info['active']): ?>
                                                        <button type="button" class="button button-secondary" onclick="cdswerx_activate_theme('<?php echo esc_attr($slug); ?>')">
                                                            <?php _e("Activate", "cdswerx"); ?>
                                                        </button>
                                                    <?php elseif ($theme_info['active']): ?>
                                                        <span style="color: #46b450;"><?php _e("Currently Active", "cdswerx"); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <p><?php _e("No CDSWerx themes detected. Please ensure CDSWerx themes are properly installed.", "cdswerx"); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Smart Theme Management -->
                    <div class="cdswerx-smart-management" style="margin-top: 30px;">
                        <h4><?php _e("Smart Theme Management", "cdswerx"); ?></h4>
                        <p><?php _e("Let CDSWerx automatically select and activate the optimal theme for your site configuration.", "cdswerx"); ?></p>
                        
                        <div style="margin: 15px 0;">
                            <button type="button" id="cdswerx-auto-optimize" class="button button-primary">
                                <?php _e("Auto-Optimize Theme", "cdswerx"); ?>
                            </button>
                            <button type="button" id="cdswerx-check-status" class="button button-secondary">
                                <?php _e("Check System Status", "cdswerx"); ?>
                            </button>
                        </div>

                        <div id="cdswerx-auto-result" style="margin-top: 15px;"></div>
                    </div>

                    <!-- Theme Ecosystem Documentation -->
                    <div class="cdswerx-documentation" style="margin-top: 30px; padding: 15px; background: #fff; border: 1px solid #ddd;">
                        <h4><?php _e("Theme Ecosystem Information", "cdswerx"); ?></h4>
                        <ul>
                            <li><strong><?php _e("CDSWerx Elementor Theme:", "cdswerx"); ?></strong> <?php _e("White-labeled Hello Elementor theme optimized for CDSWerx ecosystem", "cdswerx"); ?></li>
                            <li><strong><?php _e("CDSWerx Elementor Child:", "cdswerx"); ?></strong> <?php _e("Child theme with intelligent asset management and plugin integration", "cdswerx"); ?></li>
                            <li><strong><?php _e("Asset Management:", "cdswerx"); ?></strong> <?php _e("Automatic detection of Elementor Pro and custom CSS injection capability", "cdswerx"); ?></li>
                        </ul>
                        <p><em><?php _e("For full documentation and implementation details, see the docs/theme-ecosystem/ folder.", "cdswerx"); ?></em></p>
                    </div>

                    <!-- TE-5: Asset Management Section -->
                    <div class="cdswerx-asset-management" style="margin-top: 30px;">
                        <h4><?php _e("TE-5: Intelligent Asset Management", "cdswerx"); ?></h4>
                        
                        <!-- Asset Status Display -->
                        <div id="cdswerx-asset-status" class="asset-status-display" style="background: #f9f9f9; padding: 15px; margin: 15px 0; border-left: 4px solid #0073aa;">
                            <h5><?php _e("Current Asset Configuration", "cdswerx"); ?></h5>
                            <div id="asset-status-content">
                                <p><?php _e("Loading asset status...", "cdswerx"); ?></p>
                            </div>
                        </div>

                        <!-- Performance Mode Controls -->
                        <div class="performance-mode-controls" style="margin: 20px 0;">
                            <h5><?php _e("Performance Mode", "cdswerx"); ?></h5>
                            <div style="display: flex; gap: 10px; margin: 10px 0;">
                                <button type="button" class="button" id="performance-mode-performance" data-mode="performance">
                                    <?php _e("Performance", "cdswerx"); ?>
                                </button>
                                <button type="button" class="button button-primary" id="performance-mode-balanced" data-mode="balanced">
                                    <?php _e("Balanced", "cdswerx"); ?>
                                </button>
                                <button type="button" class="button" id="performance-mode-compatibility" data-mode="compatibility">
                                    <?php _e("Compatibility", "cdswerx"); ?>
                                </button>
                            </div>
                            <p class="description"><?php _e("Performance: Fastest loading, minimal CSS. Balanced: Good speed + compatibility. Compatibility: Maximum plugin support.", "cdswerx"); ?></p>
                        </div>

                        <!-- Asset Optimization Controls -->
                        <div class="asset-optimization-controls" style="margin: 20px 0;">
                            <h5><?php _e("Asset Optimization", "cdswerx"); ?></h5>
                            <div style="margin: 15px 0;">
                                <button type="button" id="cdswerx-optimize-assets" class="button button-secondary">
                                    <?php _e("Optimize Assets Now", "cdswerx"); ?>
                                </button>
                                <button type="button" id="cdswerx-refresh-status" class="button">
                                    <?php _e("Refresh Status", "cdswerx"); ?>
                                </button>
                            </div>
                            <div id="cdswerx-optimization-result" style="margin-top: 15px;"></div>
                        </div>

                        <!-- Elementor Integration Status -->
                        <div class="elementor-integration-status" style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ddd;">
                            <h5><?php _e("Elementor Integration Status", "cdswerx"); ?></h5>
                            <div id="elementor-status-display">
                                <p><?php _e("Checking Elementor integration...", "cdswerx"); ?></p>
                            </div>
                        </div>
                    </div>


                </div>
                

                </form>
            </div>

            <!-- WordPress Core Tab -->
            <div id="wordpress-core" class="cdswerx-tab-content">
                <form id="cdswerx-extensions-form" class="uk-form-horizontal" method="post" action="options.php">
                    <?php settings_fields("cdswerx_extensions_group"); ?>

                    <!-- Settings Grid Container -->
                    <div class="settings-grid-container">

                        <!-- Wordpress Core Features Section -->
                        <div class="uk-card uk-card-default uk-card-body uk-margin">
                            <h4 class="uk-card-title"><?php _e(
                                                            "Wordpress Core Features",
                                                            "cdswerx",
                                                        ); ?></h4>

                            <div class="user-grid-container">
                                <!-- Header Row -->
                                <div class="user-grid-header">
                                    <div>Setting</div>
                                    <div>Description</div>
                                    <div>Status</div>
                                    <div>Actions</div>
                                </div>

                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Disable Comments System",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Disables comments functionality from WordPress.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-label"><?php _e(
                                                                        "Comments:",
                                                                        "cdswerx",
                                                                    ); ?></span>
                                        <span class="mobile-value">
                                            <?php
                                            $is_active = get_option("cdswerx_extensions", [])["disable_comments"] ?? 1;
                                            if ($is_active): ?>
                                                <span class="uk-label uk-label-success">✓ COMMENTS DISABLED</span>
                                            <?php else: ?>
                                                <span class="uk-label uk-label-warning">COMMENTS ENABLED</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <div class="switch-container">
                                                <span>Disable</span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        name="cdswerx_extensions[disable_comments]"
                                                        value="1"
                                                        <?php checked(get_option("cdswerx_extensions", [])["disable_comments"] ?? 1); ?>>
                                                    <span class="slider"></span>
                                                </label>
                                                <span>Enable</span>
                                            </div>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="user-grid-container">

                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Disable Widget Block Editor",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Reverts to classic widget interface.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-label"><?php _e(
                                                                        "Widget Editor:",
                                                                        "cdswerx",
                                                                    ); ?></span>
                                        <span class="mobile-value">
                                            <?php
                                            $is_active = get_option("cdswerx_extensions", [])["disable_widget_blocks"] ?? 1;
                                            if ($is_active): ?>
                                                <span class="uk-label uk-label-success">✓ CLASSIC EDITOR</span>
                                            <?php else: ?>
                                                <span class="uk-label uk-label-warning">BLOCKS EDITOR</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <div class="switch-container">
                                                <span>Disable</span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        name="cdswerx_extensions[disable_widget_blocks]"
                                                        value="1"
                                                        <?php checked(get_option("cdswerx_extensions", [])["disable_widget_blocks"] ?? 1); ?>>
                                                    <span class="slider"></span>
                                                </label>
                                                <span>Enable</span>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Disable Gutenberg Block Editor",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Reverts to classic WordPress editor.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-label"><?php _e(
                                                                        "Content Editor:",
                                                                        "cdswerx",
                                                                    ); ?></span>
                                        <span class="mobile-value">
                                            <?php
                                            $is_active = get_option("cdswerx_extensions", [])["disable_gutenberg"] ?? 1;
                                            if ($is_active): ?>
                                                <span class="uk-label uk-label-success">✓ CLASSIC EDITOR</span>
                                            <?php else: ?>
                                                <span class="uk-label uk-label-warning">BLOCKS EDITOR</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <div class="switch-container">
                                                <span>Disable</span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        name="cdswerx_extensions[disable_gutenberg]"
                                                        value="1"
                                                        <?php checked(get_option("cdswerx_extensions", [])["disable_gutenberg"] ?? 1); ?>>
                                                    <span class="slider"></span>
                                                </label>
                                                <span>Enable</span>
                                            </div>
                                        </span>
                                    </div>
                                </div>
                            </div>


                        </div>


                        <!-- Admin Interface Section -->
                        <div class="uk-card uk-card-default uk-card-body uk-margin">
                            <h4 class="uk-card-title"><?php _e(
                                                            "Admin Interface",
                                                            "cdswerx",
                                                        ); ?></h4>

                            <div class="user-grid-container">
                                <!-- Header Row -->
                                <div class="user-grid-header">
                                    <div>Setting</div>
                                    <div>Description</div>
                                    <div>Status</div>
                                    <div>Actions</div>
                                </div>

                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Set Published Posts As Default View",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Admin post/page lists show published items by default.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-value">
                                            <?php
                                            $is_active = get_option("cdswerx_extensions", [])["default_published_post_view"] ?? 1;
                                            if ($is_active): ?>
                                                <span class="uk-label uk-label-success">✓ ENABLED</span>
                                            <?php else: ?>
                                                <span class="uk-label uk-label-warning">DISABLED</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <div class="switch-container">
                                                <span>Disable</span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        name="cdswerx_extensions[default_published_post_view]"
                                                        value="1"
                                                        <?php checked(get_option("cdswerx_extensions", [])["default_published_post_view"] ?? 1); ?>>
                                                    <span class="slider"></span>
                                                </label>
                                                <span>Enable</span>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Add Image Dimensions Column",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Shows image dimensions in media library.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-value">
                                            <?php
                                            $is_active = get_option("cdswerx_extensions", [])["show_media_dimensions_column"] ?? 1;
                                            if ($is_active): ?>
                                                <span class="uk-label uk-label-success">✓ ENABLED</span>
                                            <?php else: ?>
                                                <span class="uk-label uk-label-warning">DISABLED</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <div class="switch-container">
                                                <span>Disable</span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        name="cdswerx_extensions[show_media_dimensions_column]"
                                                        value="1"
                                                        <?php checked(get_option("cdswerx_extensions", [])["show_media_dimensions_column"] ?? 1); ?>>
                                                    <span class="slider"></span>
                                                </label>
                                                <span>Enable</span>
                                            </div>
                                        </span>
                                    </div>
                                </div>


                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Show Post/Page IDs in Admin Columns",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Displays post and page IDs in admin listing columns.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-value">
                                            <?php
                                            $is_active = get_option("cdswerx_extensions", [])["show_post_id_column"] ?? 1;
                                            if ($is_active): ?>
                                                <span class="uk-label uk-label-success">✓ ENABLED</span>
                                            <?php else: ?>
                                                <span class="uk-label uk-label-warning">DISABLED</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <div class="switch-container">
                                                <span>Disable</span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        name="cdswerx_extensions[show_post_id_column]"
                                                        value="1"
                                                        <?php checked(get_option("cdswerx_extensions", [])["show_post_id_column"] ?? 1); ?>>
                                                    <span class="slider"></span>
                                                </label>
                                                <span>Enable</span>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Show Featured Images in Admin Columns",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Displays thumbnail images in post/page admin columns.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-label"><?php _e(
                                                                        "Featured Images:",
                                                                        "cdswerx",
                                                                    ); ?></span>
                                        <span class="mobile-value">
                                            <?php
                                            $is_active = get_option("cdswerx_extensions", [])["show_featured_image_column"] ?? 1;
                                            if ($is_active): ?>
                                                <span class="uk-label uk-label-success">✓ ENABLED</span>
                                            <?php else: ?>
                                                <span class="uk-label uk-label-warning">DISABLED</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <div class="switch-container">
                                                <span>Disable</span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        name="cdswerx_extensions[show_featured_image_column]"
                                                        value="1"
                                                        <?php checked(get_option("cdswerx_extensions", [])["show_featured_image_column"] ?? 1); ?>>
                                                    <span class="slider"></span>
                                                </label>
                                                <span>Enable</span>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Remove Yoast SEO Columns",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Hides Yoast SEO columns from post/page admin lists.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-label"><?php _e(
                                                                        "Yoast Columns:",
                                                                        "cdswerx",
                                                                    ); ?></span>
                                        <span class="mobile-value">
                                            <?php
                                            $is_active = get_option("cdswerx_extensions", [])["remove_yoast_columns"] ?? 1;
                                            if ($is_active): ?>
                                                <span class="uk-label uk-label-success">✓ ENABLED</span>
                                            <?php else: ?>
                                                <span class="uk-label uk-label-warning">DISABLED</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <div class="switch-container">
                                                <span>Disable</span>
                                                <label class="switch">
                                                    <input type="checkbox"
                                                        name="cdswerx_extensions[remove_yoast_columns]"
                                                        value="1"
                                                        <?php checked(get_option("cdswerx_extensions", [])["remove_yoast_columns"] ?? 1); ?>>
                                                    <span class="slider"></span>
                                                </label>
                                                <span>Enable</span>
                                            </div>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php submit_button(
                        __("Save Extensions Settings", "cdswerx"),
                        "uk-button-primary",
                        "submit",
                        false,
                    ); ?>
                </form>

            </div>

            <!-- Advanced Tab -->
            <div id="advanced" class="cdswerx-tab-content" style="display: none;">
                <div class="cdswerx-settings-group">
                    <h3><?php _e("Advanced Settings", "cdswerx"); ?></h3>

                    <table class="cdswerx-form-table">
                      

                        <tr class="cdswerx-custom-code-row">
                            <th scope="row">
                                <label><?php _e("Custom Code System", "cdswerx"); ?></label>
                            </th>
                            <td class="cdswerx-custom-code-cell">
                                <!-- Debug: Container loaded at <?php echo current_time('H:i:s'); ?> -->
                                <style>
                                /* Hide bottom textareas immediately to prevent dual editor confusion */
                                .cdswerx-hide-when-codemirror {
                                    display: none !important;
                                }
                                
                                /* Make CodeMirror editors take full width */
                                #cdswerx-custom-code-manager .CodeMirror {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                    min-height: 400px !important;
                                }
                                
                                .cdswerx-code-editor-wrapper {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                }
                                
                                .cdswerx-tab-content {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                }
                                
                                /* Ensure the Custom Code table cell expands */
                                .cdswerx-custom-code-cell {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                }
                                
                                /* Make the entire Custom Code manager container full width */
                                #cdswerx-custom-code-manager {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                    box-sizing: border-box;
                                }
                                
                                /* Override WordPress admin table constraints */
                                .cdswerx-custom-code-row td {
                                    width: 100% !important;
                                    max-width: 100% !important;
                                    padding-right: 0 !important;
                                }
                                
                                .cdswerx-custom-code-row th {
                                    width: auto !important;
                                    white-space: nowrap;
                                }
                                
                                /* Make the main form table expand */
                                .form-table .cdswerx-custom-code-row {
                                    width: 100% !important;
                                }
                                
                                /* Increase editor height for better coding experience */
                                #cdswerx-custom-code-manager .CodeMirror {
                                    min-height: 500px !important;
                                }
                                
                                /* Ensure tab content visibility */
                                .cdswerx-tab-content {
                                    display: none;
                                }
                                
                                .cdswerx-tab-content.active {
                                    display: block !important;
                                }
                                
                                .cdswerx-tab-nav {
                                    margin: 0 0 20px 0;
                                    padding: 0;
                                    border-bottom: 1px solid #ccd0d4;
                                    list-style: none;
                                    display: flex;
                                }
                                
                                .cdswerx-tab-nav li {
                                    margin: 0;
                                }
                                
                                .cdswerx-tab-nav a {
                                    display: block;
                                    padding: 10px 20px;
                                    text-decoration: none;
                                    color: #50575e;
                                    border-bottom: 2px solid transparent;
                                    cursor: pointer;
                                }
                                
                                .cdswerx-tab-nav a:hover {
                                    color: #135e96;
                                }
                                
                                .cdswerx-tab-nav a.active {
                                    color: #135e96;
                                    border-bottom-color: #135e96;
                                    font-weight: 600;
                                }
                                </style>
                                <div id="cdswerx-custom-code-manager">
                                    <div class="cdswerx-code-tabs">
                                        <ul class="cdswerx-tab-nav">
                                            <li><a href="#css-tab" class="active"><?php _e("CSS", "cdswerx"); ?></a></li>
                                            <li><a href="#js-tab"><?php _e("JavaScript", "cdswerx"); ?></a></li>
                                            <li><a href="#settings-tab"><?php _e("Settings", "cdswerx"); ?></a></li>
                                        </ul>
                                        
                                        <div id="css-tab" class="cdswerx-tab-content active">
                                            <div class="cdswerx-code-header">
                                                <select id="css-category" class="cdswerx-input">
                                                    <option value="global"><?php _e("Global CSS", "cdswerx"); ?></option>
                                                    <option value="admin"><?php _e("Admin CSS", "cdswerx"); ?></option>
                                                    <option value="icons"><?php _e("Icon CSS", "cdswerx"); ?></option>
                                                    <option value="theme"><?php _e("Theme CSS", "cdswerx"); ?></option>
                                                </select>
                                                <input type="text" id="css-name" class="cdswerx-input" placeholder="<?php _e("Code Name", "cdswerx"); ?>" />
                                                <label for="css-priority" style="display: inline-block; margin-right: 5px; font-weight: 500;"><?php _e("Load Order:", "cdswerx"); ?></label>
                                                <input type="number" id="css-priority" class="cdswerx-input" value="10" min="1" max="999" placeholder="<?php _e("Priority", "cdswerx"); ?>" title="<?php _e("Lower numbers load first (1-999)", "cdswerx"); ?>" style="width: 70px;" />
                                                <label>
                                                    <input type="checkbox" id="css-active" checked /> <?php _e("Active", "cdswerx"); ?>
                                                </label>
                                                <button type="button" id="css-save" class="button button-primary"><?php _e("Save CSS", "cdswerx"); ?></button>
                                                <button type="button" id="css-cancel" class="button" style="display: none; background-color: #ff6b6b;"><?php _e("Cancel Edit", "cdswerx"); ?></button>
                                                <button type="button" id="css-new" class="button" style="background-color: #FF0000; color: white; font-weight: bold;"><?php _e("🔥 NEW CSS (UPDATED)", "cdswerx"); ?></button>
                                            </div>
                                            <div class="cdswerx-monaco-toolbar">
                                                <button type="button" class="button cdswerx-format-code" data-editor="css">
                                                    <span class="dashicons dashicons-editor-code"></span> <?php _e("Format", "cdswerx"); ?>
                                                </button>
                                                <button type="button" class="button cdswerx-find-replace" data-editor="css">
                                                    <span class="dashicons dashicons-search"></span> <?php _e("Find/Replace", "cdswerx"); ?>
                                                </button>
                                                <span class="cdswerx-editor-info">
                                                    <?php _e("Lines:", "cdswerx"); ?> <span class="css-line-count">0</span> | 
                                                    <?php _e("Language: CSS", "cdswerx"); ?>
                                                </span>
                                            </div>
                                            <div class="cdswerx-code-editor-wrapper">
                                                <!-- Debug: CSS textarea element -->
                                                <textarea id="cdswerx-css-editor" class="cdswerx-code-editor cdswerx-hide-when-codemirror" name="cdswerx-css-editor" rows="15" cols="50" placeholder="/* <?php _e("Enter your CSS code here...", "cdswerx"); ?> */"><?php echo esc_textarea(isset($options["custom_css"]) ? $options["custom_css"] : ""); ?></textarea>
                                                <script>console.log('CDSWerx Debug: CSS textarea rendered');</script>
                                            </div>
                                            <div class="cdswerx-code-list">
                                                <h4><?php _e("Saved CSS Code", "cdswerx"); ?></h4>
                                                <div id="css-code-list"></div>
                                            </div>
                                        </div>
                                        
                                        <div id="js-tab" class="cdswerx-tab-content">
                                            <div class="cdswerx-code-header">
                                                <select id="js-category" class="cdswerx-input">
                                                    <option value="global"><?php _e("Global JavaScript", "cdswerx"); ?></option>
                                                    <option value="admin"><?php _e("Admin JavaScript", "cdswerx"); ?></option>
                                                    <option value="theme"><?php _e("Theme JavaScript", "cdswerx"); ?></option>
                                                </select>
                                                <input type="text" id="js-name" class="cdswerx-input" placeholder="<?php _e("Code Name", "cdswerx"); ?>" />
                                                <label for="js-priority" style="display: inline-block; margin-right: 5px; font-weight: 500;"><?php _e("Load Order:", "cdswerx"); ?></label>
                                                <input type="number" id="js-priority" class="cdswerx-input" value="10" min="1" max="999" placeholder="<?php _e("Priority", "cdswerx"); ?>" title="<?php _e("Lower numbers load first (1-999)", "cdswerx"); ?>" style="width: 70px;" />
                                                <label>
                                                    <input type="checkbox" id="js-active" checked /> <?php _e("Active", "cdswerx"); ?>
                                                </label>
                                                <button type="button" id="js-save" class="button button-primary"><?php _e("Save JavaScript", "cdswerx"); ?></button>
                                                <button type="button" id="js-cancel" class="button" style="display: none; background-color: #ff6b6b;"><?php _e("Cancel Edit", "cdswerx"); ?></button>
                                                <button type="button" id="js-new" class="button" style="background-color: #4CAF50;"><?php _e("New JavaScript", "cdswerx"); ?></button>
                                            </div>
                                            <div class="cdswerx-monaco-toolbar">
                                                <button type="button" class="button cdswerx-format-code" data-editor="js">
                                                    <span class="dashicons dashicons-editor-code"></span> <?php _e("Format", "cdswerx"); ?>
                                                </button>
                                                <button type="button" class="button cdswerx-find-replace" data-editor="js">
                                                    <span class="dashicons dashicons-search"></span> <?php _e("Find/Replace", "cdswerx"); ?>
                                                </button>
                                                <span class="cdswerx-editor-info">
                                                    <?php _e("Lines:", "cdswerx"); ?> <span class="js-line-count">0</span> | 
                                                    <?php _e("Language: JavaScript", "cdswerx"); ?>
                                                </span>
                                            </div>
                                            <div class="cdswerx-code-editor-wrapper">
                                                <!-- Debug: JS textarea element -->
                                                <textarea id="cdswerx-js-editor" class="cdswerx-code-editor cdswerx-hide-when-codemirror" name="cdswerx-js-editor" rows="15" cols="50" placeholder="/* <?php _e("Enter your JavaScript code here...", "cdswerx"); ?> */"><?php echo esc_textarea(isset($options["custom_js"]) ? $options["custom_js"] : ""); ?></textarea>
                                                <script>console.log('CDSWerx Debug: JS textarea rendered');</script>
                                            </div>
                                            <div class="cdswerx-code-list">
                                                <h4><?php _e("Saved JavaScript Code", "cdswerx"); ?></h4>
                                                <div id="js-code-list"></div>
                                            </div>
                                        </div>
                                        
                                        <div id="settings-tab" class="cdswerx-tab-content">
                                            <h4><?php _e("Custom Code Settings", "cdswerx"); ?></h4>
                                            <table class="form-table">
                                                <tr>
                                                    <th><?php _e("Code Injection", "cdswerx"); ?></th>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" id="enable-minification" checked /> 
                                                            <?php _e("Enable code minification", "cdswerx"); ?>
                                                        </label>
                                                        <p class="description"><?php _e("Automatically minify CSS and JavaScript for better performance.", "cdswerx"); ?></p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th><?php _e("Critical CSS", "cdswerx"); ?></th>
                                                    <td>
                                                        <label>
                                                            <input type="checkbox" id="enable-critical-css" checked /> 
                                                            <?php _e("Inline critical CSS (priority 1-5)", "cdswerx"); ?>
                                                        </label>
                                                        <p class="description"><?php _e("Critical CSS will be inlined in the document head for faster loading.", "cdswerx"); ?></p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <p class="cdswerx-description"><?php _e("Enhanced custom code management with categories, priorities, and version control.", "cdswerx"); ?></p>
                            </td>
                        </tr>

                    </table>
                </div>
            </div>

            <!-- Import/Export Tab -->
            <div id="import-export" class="cdswerx-tab-content" style="display: none;">
                <form id="cdswerx-import-export-form" class="uk-form-horizontal" method="post" action="options.php">
                    <?php settings_fields("cdswerx_extensions_group"); ?>
                    <!-- Settings Grid Container -->
                    <div class="settings-grid-container">

                        <!-- Import/Export Section -->
                        <div class="uk-card uk-card-default uk-card-body uk-margin">
                            <h4 class="uk-card-title"><?php _e(
                                                            "Import/Export Settings",
                                                            "cdswerx",
                                                        ); ?></h4>

                            <div class="user-grid-container">
                                <!-- Header Row -->
                                <div class="user-grid-header">
                                    <div>Setting</div>
                                    <div>Description</div>
                                    <div>Status</div>
                                    <div>Actions</div>
                                </div>

                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Export Settings",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Export your current settings to a JSON file.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-label"><?php _e(
                                                                        "Ready:",
                                                                        "cdswerx",
                                                                    ); ?></span>
                                        <span class="mobile-value">
                                            <span class="uk-label uk-label-primary">READY</span>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <button type="button" id="cdswerx-export-btn" class="cdswerx-button-primary"><?php _e(
                                                                                                                                "Export",
                                                                                                                                "cdswerx",
                                                                                                                            ); ?></button>
                                        </span>
                                    </div>
                                </div>

                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Import Settings",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Import settings from a previously exported JSON file.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-label"><?php _e(
                                                                        "Ready:",
                                                                        "cdswerx",
                                                                    ); ?></span>
                                        <span class="mobile-value">
                                            <span class="uk-label uk-label-primary">READY</span>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <button type="button" id="cdswerx-import-btn" class="cdswerx-button-primary"><?php _e(
                                                                                                                                "Import",
                                                                                                                                "cdswerx",
                                                                                                                            ); ?></button>
                                        </span>
                                    </div>
                                </div>

                                <div class="user-grid-row">
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">Setting:</span>
                                        <span class="mobile-value">
                                            <label class="uk-form-label">
                                                <?php _e(
                                                    "Reset Settings",
                                                    "cdswerx",
                                                ); ?>
                                            </label>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">Description:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-meta">
                                                <?php _e(
                                                    "Reset all settings to their default values. This action cannot be undone.",
                                                    "cdswerx",
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-label"><?php _e(
                                                                        "Ready:",
                                                                        "cdswerx",
                                                                    ); ?></span>
                                        <span class="mobile-value">
                                            <span class="uk-label uk-label-warning">CAUTION</span>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <button type="button" id="cdswerx-reset-settings" class="cdswerx-button-primary button"><?php _e(
                                                                                                                                        "Reset",
                                                                                                                                        "cdswerx",
                                                                                                                                    ); ?></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>



                    </div> <?php submit_button(
                                __("Save Import Export Settings", "cdswerx"),
                                "primary",
                                "submit",
                                false,
                            ); ?>
                </form>
            </div>
        </div>
        <?php $this->render_cdswerx_admin_section("footer"); ?>

    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($) {
        // Get current options for JavaScript
        var cdswerx_options = <?php echo json_encode(
                                    get_option("cdswerx_options", []),
                                ); ?>;

        // Make options available globally
        window.cdswerx_admin = {
            nonce: '<?php echo wp_create_nonce("cdswerx_admin_nonce"); ?>',
            options: cdswerx_options
        };
    });
</script>