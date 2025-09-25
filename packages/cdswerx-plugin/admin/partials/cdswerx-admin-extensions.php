<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


/**
 * CDSWerx Admin Extensions Partial
 *
 * Provides the admin area view for managing extensions and add-ons for the plugin.
 *
 * Purpose & Responsibilities:
 * - Renders the UI for browsing, installing, and managing CDSWerx extensions.
 * - Displays extension status, information, and available actions for each extension.
 * - Handles extension activation, deactivation, and configuration settings.
 * - Shows compatibility information and requirements for extensions.
 *
 * Sections & Code Overview:
 * 1. Header Section:
 *   - Displays plugin header and logo.
 * 2. Main Content:
 *   - Extensions Tab:
 *     - Shows installed extensions with status and actions.
 *     - Displays available extensions for installation.
 *     - Provides extension search and filtering options.
 *   - Settings Tab:
 *     - Global extension settings and configurations.
 *     - Extension update preferences and notification settings.
 *     - Extension compatibility checks and requirements.
 * 3. Modals:
 *   - Extension Details Modal: Shows detailed information about extensions.
 *   - Extension Install Modal: Confirms and manages installation process.
 *   - Extension Settings Modal: Provides configuration options for specific extensions.
 * 4. Scripts:
 *   - Tab switching functionality.
 *   - Extension installation, activation, and deactivation logic.
 *   - Extension settings management and configuration validation.
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 * @package    Cdswerx
 * @subpackage Cdswerx/admin/partials
 */
?>
<div id="admin-extensions" class="cdswerx-wrap">
    <?php $this->render_cdswerx_admin_section("header", $logo_url); ?>

    <div class="cdswerx-admin main-content uk-section uk-padding-remove">
        <div class="cdswerx-admin content-header uk-card uk-background-default uk-card-body">
            <h2><?php _e("CDSWerx Extensions", "cdswerx"); ?></h2>
            <p><?php _e(
                    "Configure WordPress extensions, editor settings, and integrations.",
                    "cdswerx",
                ); ?></p>
        </div>

        <div class="cdswerx-admin content-body uk-card uk-background-default uk-card-body uk-padding-remove-top">
            <?php
            // Display admin messages
            $admin_message = get_transient("cdswerx_admin_message");
            if ($admin_message) {
                delete_transient("cdswerx_admin_message");
                $alert_class =
                    $admin_message["type"] === "success"
                    ? "uk-alert-success"
                    : "uk-alert-danger";
                echo '<div class="' . $alert_class . '" uk-alert>';
                echo "<p>" . $admin_message["message"] . "</p>";
                echo "</div>";
            }
            ?>

            <div class="cdswerx-tabs">
                <ul>
                    <li><a href="#editor-tinymce" class="active"><?php _e(
                                                                        "WSYWIG / TinyMCE Styles",
                                                                        "cdswerx",
                                                                    ); ?></a></li>
                    <li><a href="#elementor"><?php _e(
                                                    "Elementor",
                                                    "cdswerx",
                                                ); ?></a></li>
                    <li><a href="#utilities"><?php _e(
                                                    "Utilities",
                                                    "cdswerx",
                                                ); ?></a></li>
                </ul>
            </div>

            <!-- Editor/TinyMCE Tab -->
            <div id="editor-tinymce" class="cdswerx-tab-content">
                <form id="cdswerx-extensions-form" class="uk-form-horizontal" method="post" action="options.php">
                    <?php settings_fields("cdswerx_extensions_group"); ?>
                    <?php do_settings_sections("cdswerx_extensions_group"); ?>
                    <div class="cdswerx-settings-group">
                        <h3><?php _e("Editor & TinyMCE Extensions", "cdswerx"); ?></h3>
                        <p class="uk-text-muted"><?php _e("Extend the WordPress content editor experience.", "cdswerx"); ?></p>

                        <div class="user-grid-container">
                            <!-- Header Row -->
                            <div class="user-grid-header">
                                <div>Setting</div>
                                <div>Description</div>
                                <div>Status</div>
                                <div>Actions</div>
                            </div>

                            <!-- TinyMCE Custom Buttons Row -->
                            <div class="user-grid-row">
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label">Setting:</span>
                                    <span class="mobile-value">
                                        <strong>Customize TinyMCE Buttons</strong>
                                    </span>
                                </div>
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Description:</span>
                                    <span class="mobile-value">
                                        Remove unnecessary buttons and add style selector.
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Status:</span>
                                    <div class="mobile-value">
                                        <?php if (get_option("cdswerx_extensions", [])["tinymce_custom_buttons"] ?? 1): ?>
                                            <span class="uk-label uk-label-success">✓ ACTIVATED</span>
                                        <?php else: ?>
                                            <span class="uk-label uk-label-danger">✗ DISABLED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-grid-cell-actions">
                                    <span class="mobile-label">Actions:</span>
                                    <span class="mobile-value">
                                        <div class="switch-container">
                                            <label class="switch">
                                                <input type="checkbox" name="cdswerx_extensions[tinymce_custom_buttons]" value="1" <?php checked(get_option("cdswerx_extensions", [])["tinymce_custom_buttons"] ?? 1, 1); ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </span>
                                </div>
                            </div>

                            <!-- TinyMCE Custom Styles Row -->
                            <div class="user-grid-row">
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label">Setting:</span>
                                    <span class="mobile-value">
                                        <strong>TinyMCE Customs Text Styles</strong>
                                    </span>
                                </div>
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Description:</span>
                                    <span class="mobile-value">
                                        Adds paragraph, header, and list style options.
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Status:</span>
                                    <div class="mobile-value">
                                        <?php if (get_option("cdswerx_extensions", [])["tinymce_custom_styles"] ?? 1): ?>
                                            <span class="uk-label uk-label-success">✓ ACTIVATED</span>
                                        <?php else: ?>
                                            <span class="uk-label uk-label-danger">✗ DISABLED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-grid-cell-actions">
                                    <span class="mobile-label">Actions:</span>
                                    <span class="mobile-value">
                                        <div class="switch-container">
                                            <label class="switch">
                                                <input type="checkbox" name="cdswerx_extensions[tinymce_custom_styles]" value="1" <?php checked(get_option("cdswerx_extensions", [])["tinymce_custom_styles"] ?? 1, 1); ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php submit_button("Save Extension Settings"); ?>
                </form>
            </div>

            <!-- Elementor Tab -->
            <div id="elementor" class="cdswerx-tab-content">
                <form id="cdswerx-extensions-elementor-form" class="uk-form-horizontal" method="post" action="options.php">
                    <?php settings_fields("cdswerx_extensions_group"); ?>
                    <?php do_settings_sections("cdswerx_extensions_group"); ?>
                    <div class="cdswerx-settings-group">
                        <h3><?php _e("Elementor Integration", "cdswerx"); ?></h3>
                        <p class="uk-text-muted"><?php _e("Elementor page builder extensions and integrations.", "cdswerx"); ?></p>

                        <!-- Elementor Status Card -->
                        <div class="uk-card uk-card-muted uk-card-body uk-margin">
                            <?php if (class_exists("Elementor\Plugin")): ?>
                                <div class="uk-alert uk-alert-success uk-margin-remove">
                                    <p class="uk-margin-remove"><?php _e("✅ Elementor is active and ready for extensions.", "cdswerx"); ?></p>
                                </div>
                            <?php else: ?>
                                <div class="uk-alert uk-alert-warning uk-margin-remove">
                                    <p class="uk-margin-remove"><?php _e("⚠️ Elementor is not active. These settings will only take effect when Elementor is activated.", "cdswerx"); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="user-grid-container">
                            <!-- Header Row -->
                            <div class="user-grid-header">
                                <div>Setting</div>
                                <div>Description</div>
                                <div>Status</div>
                                <div>Actions</div>
                            </div>

                            <!-- Elementor Edit Links Row -->
                            <div class="user-grid-row">
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label">Setting:</span>
                                    <span class="mobile-value">
                                        <strong>Edit with Elementor Link</strong>
                                    </span>
                                </div>
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Description:</span>
                                    <span class="mobile-value">
                                        Adds 'Edit with Elementor' links in admin post/page lists.
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Status:</span>
                                    <div class="mobile-value">
                                        <?php if (get_option("cdswerx_extensions", [])["elementor_edit_links"] ?? 1): ?>
                                            <span class="uk-label uk-label-success">✓ ACTIVATED</span>
                                        <?php else: ?>
                                            <span class="uk-label uk-label-danger">✗ DISABLED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-grid-cell-actions">
                                    <span class="mobile-label">Actions:</span>
                                    <span class="mobile-value">
                                        <div class="switch-container">
                                            <label class="switch">
                                                <input type="checkbox" name="cdswerx_extensions[elementor_edit_links]" value="1" <?php checked(get_option("cdswerx_extensions", [])["elementor_edit_links"] ?? 1, 1); ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </span>
                                </div>
                            </div>

                            <!-- Elementor TinyMCE Colors Row -->
                            <div class="user-grid-row">
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label">Setting:</span>
                                    <span class="mobile-value">
                                        <strong>Elementor Global Colors Synced with TinyMCE</strong>
                                    </span>
                                </div>
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Description:</span>
                                    <span class="mobile-value">
                                        Uses Elementor global colors in the WordPress editor color picker.
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Status:</span>
                                    <div class="mobile-value">
                                        <?php if (get_option("cdswerx_extensions", [])["elementor_tinymce_colors"] ?? 1): ?>
                                            <span class="uk-label uk-label-success">✓ ACTIVATED</span>
                                        <?php else: ?>
                                            <span class="uk-label uk-label-danger">✗ DISABLED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-grid-cell-actions">
                                    <span class="mobile-label">Actions:</span>
                                    <span class="mobile-value">
                                        <div class="switch-container">
                                            <label class="switch">
                                                <input type="checkbox" name="cdswerx_extensions[elementor_tinymce_colors]" value="1" <?php checked(get_option("cdswerx_extensions", [])["elementor_tinymce_colors"] ?? 1, 1); ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <?php submit_button("Save Extension Settings"); ?>
                </form>
            </div>

            <!-- Utilities Tab -->
            <div id="utilities" class="cdswerx-tab-content">
                <form id="cdswerx-extensions-utilities-form" class="uk-form-horizontal" method="post" action="options.php">
                    <?php settings_fields("cdswerx_extensions_group"); ?>
                    <?php do_settings_sections("cdswerx_extensions_group"); ?>
                    <div class="cdswerx-settings-group">
                        <h3><?php _e("Utility Extensions", "cdswerx"); ?></h3>
                        <p class="uk-text-muted"><?php _e("Additional utility extensions and helper tools.", "cdswerx"); ?></p>

                        <div class="user-grid-container">
                            <!-- Header Row -->
                            <div class="user-grid-header">
                                <div>Setting</div>
                                <div>Description</div>
                                <div>Status</div>
                                <div>Actions</div>
                            </div>

                            <!-- Post Duplication Row -->
                            <div class="user-grid-row">
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label">Setting:</span>
                                    <span class="mobile-value">
                                        <strong>Enable Post/Page Duplication</strong>
                                    </span>
                                </div>
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Description:</span>
                                    <span class="mobile-value">
                                        Adds 'Duplicate' action to post/page admin lists.
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Status:</span>
                                    <div class="mobile-value">
                                        <?php if (get_option("cdswerx_extensions", [])["enable_post_duplication"] ?? 1): ?>
                                            <span class="uk-label uk-label-success">✓ ACTIVATED</span>
                                        <?php else: ?>
                                            <span class="uk-label uk-label-danger">✗ DISABLED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-grid-cell-actions">
                                    <span class="mobile-label">Actions:</span>
                                    <span class="mobile-value">
                                        <div class="switch-container">
                                            <label class="switch">
                                                <input type="checkbox" name="cdswerx_extensions[enable_post_duplication]" value="1" <?php checked(get_option("cdswerx_extensions", [])["enable_post_duplication"] ?? 1, 1); ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </span>
                                </div>
                            </div>

                            <!-- Date Shortcodes Row -->
                            <div class="user-grid-row">
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label">Setting:</span>
                                    <span class="mobile-value">
                                        <strong>Enable Date Shortcodes</strong>
                                    </span>
                                </div>
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Description:</span>
                                    <span class="mobile-value">
                                        Enables [year], [month], [day], [monthyear], [yyyymmdd] shortcodes.
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Status:</span>
                                    <div class="mobile-value">
                                        <?php if (get_option("cdswerx_extensions", [])["enable_date_shortcodes"] ?? 1): ?>
                                            <span class="uk-label uk-label-success">✓ ACTIVATED</span>
                                        <?php else: ?>
                                            <span class="uk-label uk-label-danger">✗ DISABLED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-grid-cell-actions">
                                    <span class="mobile-label">Actions:</span>
                                    <span class="mobile-value">
                                        <div class="switch-container">
                                            <label class="switch">
                                                <input type="checkbox" name="cdswerx_extensions[enable_date_shortcodes]" value="1" <?php checked(get_option("cdswerx_extensions", [])["enable_date_shortcodes"] ?? 1, 1); ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </span>
                                </div>
                            </div>

                            <!-- SVG Upload Row -->
                            <div class="user-grid-row">
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label">Setting:</span>
                                    <span class="mobile-value">
                                        <strong>Allow SVG File Uploads</strong>
                                    </span>
                                </div>
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Description:</span>
                                    <span class="mobile-value">
                                        Only for administrators and super admins.
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Status:</span>
                                    <div class="mobile-value">
                                        <?php if (get_option("cdswerx_extensions", [])["enable_svg_upload"] ?? 1): ?>
                                            <span class="uk-label uk-label-success">✓ ACTIVATED</span>
                                        <?php else: ?>
                                            <span class="uk-label uk-label-danger">✗ DISABLED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-grid-cell-actions">
                                    <span class="mobile-label">Actions:</span>
                                    <span class="mobile-value">
                                        <div class="switch-container">
                                            <label class="switch">
                                                <input type="checkbox" name="cdswerx_extensions[enable_svg_upload]" value="1" <?php checked(get_option("cdswerx_extensions", [])["enable_svg_upload"] ?? 1, 1); ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Yoast SEO Columns Row -->
                            <div class="user-grid-row">
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label">Setting:</span>
                                    <span class="mobile-value">
                                        <strong>Remove Yoast SEO Columns</strong>
                                    </span>
                                </div>
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">Description:</span>
                                    <span class="mobile-value">
                                        Remove Yoast SEO columns from post and page lists for a cleaner admin interface.
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">Status:</span>
                                    <div class="mobile-value">
                                        <?php if (get_option("cdswerx_extensions", [])["remove_yoast_seo_columns"] ?? 1): ?>
                                            <span class="uk-label uk-label-success">✓ ACTIVATED</span>
                                        <?php else: ?>
                                            <span class="uk-label uk-label-danger">✗ DISABLED</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="user-grid-cell-actions">
                                    <span class="mobile-label">Actions:</span>
                                    <span class="mobile-value">
                                        <div class="switch-container">
                                            <label class="switch">
                                                <input type="checkbox" name="cdswerx_extensions[remove_yoast_seo_columns]" value="1" <?php checked(get_option("cdswerx_extensions", [])["remove_yoast_seo_columns"] ?? 1, 1); ?>>
                                                <span class="slider"></span>
                                            </label>
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php submit_button("Save Utilities Settings"); ?>
                </form>
            </div>

            <!-- Custom Code Tab -->
            <div id="customcode" class="cdswerx-tab-content">
                <form id="cdswerx-extensions-customcode-form" class="uk-form-horizontal" method="post" action="options.php">
                    <?php settings_fields("cdswerx_extensions_group"); ?>
                    <?php do_settings_sections("cdswerx_extensions_group"); ?>
                    
                    <div class="cdswerx-settings-group">
                        <h3><?php _e("Custom Code", "cdswerx"); ?></h3>
                        <p class="uk-text-muted"><?php _e("Add custom CSS and JavaScript code.", "cdswerx"); ?></p>
                        
                        <p>Custom code functionality coming soon...</p>
                    </div>

                    <?php submit_button("Save Custom Code Settings"); ?>
                </form>
            </div>

        </div>

        <?php $this->render_cdswerx_admin_section("footer"); ?>
    </div>
</div>