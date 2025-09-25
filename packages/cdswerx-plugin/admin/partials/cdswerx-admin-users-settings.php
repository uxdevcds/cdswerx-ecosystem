<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


/**
 * CDSWerx Admin Users Settings Partial
 *
 * Provides the admin area view for managing default users and permissions for the plugin.
 *
 * Purpose & Responsibilities:
 * - Renders the UI for activating, adding, or removing default users (Janice, Corey) and their permissions.
 * - Displays user status, information, and available actions for each managed user.
 * - Handles advanced user management, including permanent disable and debug/testing actions.
 * - Shows role hierarchy and technical debug information for administrators.
 *
 * Sections & Code Overview:
 * 1. Header Section:
 *   - Displays plugin header and logo.
 * 2. Main Content:
 *   - General Tab:
 *     - Shows status, info, and actions for Janice and Corey users.
 *     - Displays role hierarchy and default password info.
 *   - Advanced Tab:
 *     - Corey permanent disable controls and status.
 *     - Advanced actions for testing/debugging activation prompts.
 *     - Debug information and plugin option states.
 * 3. Modals:
 *   - Remove Corey Modal: Handles content reassignment or deletion when removing Corey.
 *   - Permanent Disable Corey Modal: Confirms and manages permanent disable actions for Corey.
 * 4. Scripts:
 *   - Tab switching functionality.
 *   - User activation, removal, and permanent disable logic.
 *   - Debug and advanced management functions.
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 * @package    Cdswerx
 * @subpackage Cdswerx/admin/partials
 */
?>


<div id="admin-users-settings" class="cdswerx-wrap">

    <?php $this->render_cdswerx_admin_section("header", $logo_url); ?>

    <div class="cdswerx-admin main-content uk-section uk-padding-remove">

        <div class="cdswerx-admin content-header uk-card uk-background-default uk-card-body">
            <h2><?php _e("Admin Users Management", "cdswerx"); ?></h2>
            <p><?php _e(
                    "Manage activating, adding, or removing a set of default users and permissions for every website.",
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
                    <li><a href="#general" class="active"><?php _e(
                                                                "General",
                                                                "cdswerx",
                                                            ); ?></a></li>
                    <li><a href="#advanced"><?php _e(
                                                "Advanced",
                                                "cdswerx",
                                            ); ?></a></li>
                </ul>
            </div>

            <form id="cdswerx-settings-form" class="uk-form-horizontal" method="post" action="options.php">

                <!-- General Tab -->
                <div id="general" class="cdswerx-tab-content">
                    <?php settings_fields("cdswerx_settings_group"); ?>

                    <!-- Site Admin Manager Users Section -->
                    <div class="cdswerx-settings-group">
                        <h3><?php _e(
                                "Site Admin Manager Users",
                                "cdswerx",
                            ); ?></h3>

                        <?php
                        // Get user statuses
                        $janice_status = $this->get_janice_activation_status();
                        $corey_status = $this->get_corey_activation_status();
                        $is_current_janice = $this->is_current_user_janice();
                        $is_current_corey = $this->is_current_user_corey();
                        ?>



                        <div class="user-grid-container users-settings">
                            <!-- Header Row -->
                            <div class="user-grid-header">
                                <div>User Status</div>
                                <div>User</div>
                                <div>User Information</div>
                                <div>Actions</div>
                            </div>

                            <!-- Janice User Row -->
                            <div class="user-grid-row">
                                <div class="user-grid-cell-status">
                                    <span class="mobile-label">User Status:</span>
                                    <span class="mobile-value">
                                        <?php if (
                                            $janice_status["activated"]
                                        ): ?>
                                            <span class="uk-label uk-label-success">✓ ACTIVATED</span>
                                        <?php elseif (
                                            $janice_status["primary_user"]
                                        ): ?>
                                            <span class="uk-label uk-label-warning">○ PENDING</span>
                                        <?php else: ?>
                                            <span class="uk-label uk-label-danger">✗ NOT FOUND</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="user-grid-cell-user">
                                    <span class="mobile-label">User:</span>
                                    <span class="mobile-value">
                                        <strong>janice</strong>
                                        <?php if ($is_current_janice): ?>
                                            <br><span class="uk-text-small uk-text-primary">👋 Current User</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="user-grid-cell-info">
                                    <span class="mobile-label">User Information:</span>
                                    <div class="mobile-value">
                                        <div class="uk-text-small">
                                            <div class="uk-margin-small-bottom">
                                                <strong>Expected Email:</strong> webmaster@counterdesign.ca
                                            </div>
                                            <?php if (
                                                $janice_status["primary_user"]
                                            ): ?>
                                                <div class="uk-margin-small-bottom">
                                                    <strong>Current Email:</strong> <?php echo esc_html(
                                                                                        $janice_status["primary_user"]->user_email,
                                                                                    ); ?>
                                                </div>
                                                <?php if (
                                                    is_multisite() &&
                                                    is_super_admin(
                                                        $janice_status["primary_user"]->ID,
                                                    )
                                                ): ?>
                                                    <div class="uk-margin-small-bottom">
                                                        <strong>Roles:</strong> Super Admin, <?php echo implode(
                                                                                                    ", ",
                                                                                                    $janice_status["primary_user"]->roles,
                                                                                                ); ?>
                                                    </div>
                                                <?php elseif (
                                                    !empty($janice_status["primary_user"]->roles)
                                                ): ?>
                                                    <div class="uk-margin-small-bottom">
                                                        <strong>Roles:</strong> <?php echo implode(
                                                                                    ", ",
                                                                                    $janice_status["primary_user"]->roles,
                                                                                ); ?>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <?php if (
                                                !empty($janice_status["conflicts"])
                                            ): ?>
                                                <div class="uk-alert uk-alert-warning uk-padding-small uk-margin-small-top">
                                                    <strong>⚠️ Issues Detected:</strong>
                                                    <?php if (
                                                        in_array(
                                                            "multiple_accounts",
                                                            $janice_status["conflicts"],
                                                        )
                                                    ): ?>
                                                        <br>• Multiple accounts found (<?php echo $janice_status["user_count"]; ?> total)
                                                    <?php endif; ?>
                                                    <?php if (
                                                        in_array(
                                                            "email_mismatch",
                                                            $janice_status["conflicts"],
                                                        )
                                                    ): ?>
                                                        <br>• Email address mismatch
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="user-grid-cell-actions">
                                    <span class="mobile-label">Actions:</span>
                                    <span class="mobile-value">
                                        <?php if (
                                            !$janice_status["activated"]
                                        ): ?>
                                            <?php if (
                                                in_array(
                                                    "multiple_accounts",
                                                    $janice_status["conflicts"],
                                                )
                                            ): ?>
                                                <button type="button" class="uk-button uk-button-primary uk-button-small" onclick="showJaniceUserSelection()">
                                                    ⚠️ Activate<span class="uk-visible@s"> (<?php echo $janice_status["user_count"]; ?> accounts)</span>
                                                </button>
                                            <?php elseif (
                                                in_array(
                                                    "email_mismatch",
                                                    $janice_status["conflicts"],
                                                )
                                            ): ?>
                                                <button type="button" class="uk-button uk-button-primary uk-button-small" onclick="activateJaniceWithEmailUpdate()">
                                                    ⚠️ Activate<span class="uk-visible@m"> & Update</span>
                                                </button>
                                            <?php else: ?>
                                                <button type="button" class="uk-button uk-button-primary uk-button-small" onclick="activateJanice()">
                                                    Activate<span class="uk-visible@s"> Janice</span>
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="uk-text-success uk-text-small">✓ Active</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Corey User Row -->
                            <?php if (
                                !$corey_status["permanently_disabled"]
                            ): ?>
                                <div class="user-grid-row">
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-label">User Status:</span>
                                        <span class="mobile-value">
                                            <?php if (
                                                $corey_status["activated"]
                                            ): ?>
                                                <span class="uk-label uk-label-success">✓ ACTIVATED</span>
                                            <?php elseif (
                                                $corey_status["primary_user"]
                                            ): ?>
                                                <span class="uk-label uk-label-warning">○ PENDING</span>
                                            <?php else: ?>
                                                <span class="uk-label uk-label-danger">✗ NOT FOUND</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">User:</span>
                                        <span class="mobile-value">
                                            <strong>corey</strong>
                                            <?php if ($is_current_corey): ?>
                                                <br><span class="uk-text-small uk-text-primary">👋 Current User</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">User Information:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-small">
                                                <div class="uk-margin-small-bottom">
                                                    <strong>Expected Email:</strong> cjason@cloudsurfingmedia.com
                                                </div>
                                                <?php if (
                                                    $corey_status["primary_user"]
                                                ): ?>
                                                    <div class="uk-margin-small-bottom">
                                                        <strong>Current Email:</strong> <?php echo esc_html(
                                                                                            $corey_status["primary_user"]->user_email,
                                                                                        ); ?>
                                                    </div>
                                                    <?php if (
                                                        is_multisite() &&
                                                        is_super_admin(
                                                            $corey_status["primary_user"]->ID,
                                                        )
                                                    ): ?>
                                                        <div class="uk-margin-small-bottom">
                                                            <strong>Roles:</strong> Super Admin, <?php echo implode(
                                                                                                        ", ",
                                                                                                        $corey_status["primary_user"]->roles,
                                                                                                    ); ?>
                                                        </div>
                                                    <?php elseif (
                                                        !empty($corey_status["primary_user"]->roles)
                                                    ): ?>
                                                        <div class="uk-margin-small-bottom">
                                                            <strong>Roles:</strong> <?php echo implode(
                                                                                        ", ",
                                                                                        $corey_status["primary_user"]->roles,
                                                                                    ); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <?php if (
                                                    !empty($corey_status["conflicts"])
                                                ): ?>
                                                    <div class="uk-alert uk-alert-warning uk-padding-small uk-margin-small-top">
                                                        <strong>⚠️ Issues Detected:</strong>
                                                        <?php if (
                                                            in_array(
                                                                "multiple_accounts",
                                                                $corey_status["conflicts"],
                                                            )
                                                        ): ?>
                                                            <br>• Multiple accounts found (<?php echo $corey_status["user_count"]; ?> total)
                                                        <?php endif; ?>
                                                        <?php if (
                                                            in_array(
                                                                "email_mismatch",
                                                                $corey_status["conflicts"],
                                                            )
                                                        ): ?>
                                                            <br>• Email address mismatch
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <?php if (
                                                !$corey_status["activated"]
                                            ): ?>
                                                <?php if (
                                                    in_array(
                                                        "multiple_accounts",
                                                        $corey_status["conflicts"],
                                                    )
                                                ): ?>
                                                    <button type="button" class="uk-button uk-button-primary uk-button-small" onclick="showCoreyUserSelection()">
                                                        ⚠️ Activate<span class="uk-visible@s"> (<?php echo $corey_status["user_count"]; ?> accounts)</span>
                                                    </button>
                                                <?php elseif (
                                                    in_array(
                                                        "email_mismatch",
                                                        $corey_status["conflicts"],
                                                    )
                                                ): ?>
                                                    <button type="button" class="uk-button uk-button-primary uk-button-small" onclick="activateCoreyWithEmailUpdate()">
                                                        ⚠️ Activate<span class="uk-visible@m"> & Update</span>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="uk-button uk-button-primary uk-button-small" onclick="activateCorey()">
                                                        Activate<span class="uk-visible@s"> Corey</span>
                                                    </button>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <button type="button" class="uk-button uk-button-danger uk-button-small" onclick="confirmRemoveCorey()">
                                                    🗑️ <span class="uk-visible@s">Remove</span>
                                                </button>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Corey Permanently Disabled Row -->
                                <div class="user-grid-row muted">
                                    <div class="user-grid-cell-status">
                                        <span class="mobile-label">User Status:</span>
                                        <span class="mobile-value">
                                            <span class="uk-label uk-label-danger">🚫 DISABLED</span>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-user">
                                        <span class="mobile-label">User:</span>
                                        <span class="mobile-value">
                                            <strong class="uk-text-muted">corey</strong>
                                        </span>
                                    </div>
                                    <div class="user-grid-cell-info">
                                        <span class="mobile-label">User Information:</span>
                                        <div class="mobile-value">
                                            <div class="uk-text-small uk-text-muted">
                                                Permanently disabled for this website.
                                                <br><span class="uk-visible@s">Enable in Advanced tab if needed.</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="user-grid-cell-actions">
                                        <span class="mobile-label">Actions:</span>
                                        <span class="mobile-value">
                                            <span class="uk-text-muted uk-text-small">No actions</span>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Role Information Card (keep this below the table) -->
                        <div class="uk-card uk-card-muted uk-card-body uk-margin">
                            <h4><?php _e("Role Information", "cdswerx"); ?></h4>
                            <p><strong>Role Hierarchy:</strong></p>
                            <?php if (is_multisite()): ?>
                                <ol class="uk-text-small">
                                    <li>Super Admin (network-wide access)</li>
                                    <li>Site Admin Manager (enhanced capabilities)</li>
                                    <li>Administrator (standard capabilities)</li>
                                </ol>
                            <?php else: ?>
                                <ol class="uk-text-small">
                                    <li>Site Admin Manager (enhanced capabilities)</li>
                                    <li>Administrator (standard capabilities)</li>
                                </ol>
                            <?php endif; ?>
                            <p class="uk-text-small uk-text-muted">
                                <strong>Default Password:</strong> <code>passwordhere</code> (change after first login)
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Advanced Tab -->
                <div id="advanced" class="cdswerx-tab-content" style="display: none;">
                    <div class="cdswerx-settings-group">
                        <h3><?php _e("Advanced User Management", "cdswerx"); ?></h3>

                        <!-- Corey Permanent Disable Section -->
                        <div class="uk-card uk-card-default uk-card-body uk-margin">
                            <h4>Corey Permanent Disable</h4>
                            <p class="uk-text-small uk-text-muted">
                                Permanently disable the Corey user for this website. When enabled, Corey cannot be activated or created, and will not appear in the General tab.
                            </p>

                            <?php
                            $corey_disabled = get_option(
                                "cdswerx_corey_permanently_disabled",
                                false,
                            );
                            $corey_status = $this->get_corey_activation_status();

                            // Determine the comprehensive status
                            if ($corey_disabled) {
                                $status_label =
                                    '<span class="uk-label uk-label-danger">🚫 DISABLED</span>';
                                $status_text =
                                    "Permanently disabled - Corey functionality is hidden";
                            } elseif (!$corey_status["primary_user"]) {
                                $status_label =
                                    '<span class="uk-label uk-label-warning">⚠️ NOT CREATED</span>';
                                $status_text =
                                    "User does not exist - can be created or permanently disabled";
                            } elseif ($corey_status["activated"]) {
                                $status_label =
                                    '<span class="uk-label uk-label-success">✓ ENABLED</span>';
                                $status_text = "User exists and is activated";
                            } else {
                                $status_label =
                                    '<span class="uk-label uk-label-warning">○ PENDING</span>';
                                $status_text =
                                    "User exists but not activated - can be activated or permanently disabled";
                            }
                            ?>

                            <div class="uk-grid-small uk-flex-middle" uk-grid>
                                <div class="uk-width-auto">
                                    <?php echo $status_label; ?>
                                </div>
                                <div class="uk-width-expand">
                                    <p class="uk-margin-remove uk-text-small">
                                        Status: <?php echo $status_text; ?>
                                        <?php if (
                                            $corey_disabled &&
                                            $corey_status["activated"]
                                        ): ?>
                                            <br><strong>Note:</strong> User is disabled but still activated. Disable will take effect after deactivation.
                                        <?php elseif (
                                            !$corey_disabled &&
                                            !$corey_status["activated"]
                                        ): ?>
                                            <br><strong>Info:</strong> You can either allow Corey activation or permanently disable all Corey functionality.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <div class="uk-margin-top">
                                <?php // Determine button text and style based on comprehensive status

                                if ($corey_disabled) {
                                    $button_text = "Enable Corey";
                                    $button_class = "uk-button-primary";
                                } elseif (!$corey_status["primary_user"]) {
                                    $button_text = "Enable & Create Corey";
                                    $button_class = "uk-button-secondary";
                                } elseif ($corey_status["activated"]) {
                                    $button_text = "Permanently Disable Corey";
                                    $button_class = "uk-button-danger";
                                } else {
                                    $button_text = "Enable & Activate Corey";
                                    $button_class = "uk-button-secondary";
                                } ?>
                                <?php if ($corey_disabled): ?>
                                    <!-- Corey is already disabled, show enable button only -->
                                    <button type="button" class="uk-button uk-button-primary"
                                        onclick="toggleCoreyPermanentDisable()">
                                        Enable Corey
                                    </button>
                                <?php elseif ($corey_status["activated"]): ?>
                                    <!-- Corey is activated, show disable button only -->
                                    <button type="button" class="uk-button uk-button-danger" onclick="confirmPermanentDisableCorey()">
                                        Permanently Disable Corey
                                    </button>
                                <?php else: ?>
                                    <!-- Corey is not activated and not disabled, show both options -->
                                    <button type="button" class="uk-button <?php echo $button_class; ?> uk-margin-small-right"
                                        onclick="toggleCoreyPermanentDisable()">
                                        <?php echo $button_text; ?>
                                    </button>
                                    <button type="button" class="uk-button uk-button-danger" onclick="confirmPermanentDisableCorey()">
                                        Permanently Disable Corey & Functions
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Advanced Actions -->
                        <div class="uk-card uk-card-default uk-card-body uk-margin">
                            <h4>Advanced Actions</h4>
                            <p class="uk-text-small uk-text-muted">
                                Advanced user and role management options. Use with caution.
                            </p>



                            <!-- Test/Debug Actions -->
                            <div class="uk-margin-top uk-padding-small uk-background-muted uk-border-rounded">
                                <h5 class="uk-margin-remove-bottom uk-text-warning">Testing/Debug Actions</h5>
                                <p class="uk-text-small uk-text-muted uk-margin-small-top">
                                    Use these for testing the activation prompt functionality.
                                </p>
                                <div class="uk-margin-small-top">
                                    <a href="<?php echo admin_url(
                                                    "admin.php?page=cdswerx-users&test_trigger_prompt=1&_wpnonce=" .
                                                        wp_create_nonce("test_trigger_prompt"),
                                                ); ?>"
                                        class="uk-button uk-button-secondary uk-button-small uk-margin-small-right">
                                        🧪 Trigger Activation Prompt
                                    </a>

                                    <a href="<?php echo admin_url(
                                                    "admin.php?page=cdswerx-users&test_clear_dismissal=1&_wpnonce=" .
                                                        wp_create_nonce("test_clear_dismissal"),
                                                ); ?>"
                                        class="uk-button uk-button-secondary uk-button-small uk-margin-small-right">
                                        🔄 Clear Global Dismissal
                                    </a>

                                    <a href="<?php echo admin_url(
                                                    "admin.php?page=cdswerx-users&cdswerx_reset_activation_prompts=1&_wpnonce=" .
                                                        wp_create_nonce(
                                                            "cdswerx_reset_activation_prompts",
                                                        ),
                                                ); ?>"
                                        class="uk-button uk-button-primary uk-button-small">
                                        🔧 Reset Activation Prompts
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Debug Information -->
                        <div class="uk-card uk-card-muted uk-card-body uk-margin">
                            <h4>Debug Information</h4>
                            <details>
                                <summary class="uk-text-small uk-text-primary" style="cursor: pointer;">Click to view technical details</summary>
                                <div class="uk-margin-top">
                                    <p class="uk-text-small">
                                        <strong>Plugin Options:</strong><br>
                                        • Janice Activated: <?php echo get_option(
                                                                "cdswerx_janice_activated",
                                                                0,
                                                            )
                                                                ? "Yes"
                                                                : "No"; ?><br>
                                        • Corey Activated: <?php echo get_option(
                                                                "cdswerx_corey_activated",
                                                                0,
                                                            )
                                                                ? "Yes"
                                                                : "No"; ?><br>
                                        • Corey Permanently Disabled: <?php echo get_option(
                                                                            "cdswerx_corey_permanently_disabled",
                                                                            0,
                                                                        )
                                                                            ? "Yes"
                                                                            : "No"; ?><br>
                                        • Show Activation Prompt: <?php echo get_option(
                                                                        "cdswerx_show_corey_activation_prompt",
                                                                        0,
                                                                    )
                                                                        ? "Yes"
                                                                        : "No"; ?><br>
                                        • Globally Dismissed: <?php echo get_option(
                                                                    "cdswerx_activation_prompts_globally_dismissed",
                                                                    0,
                                                                )
                                                                    ? "Yes"
                                                                    : "No"; ?><br>

                                        <?php if (is_multisite()): ?>
                                            <br><strong>Multisite:</strong> Yes<br>
                                            • Network ID: <?php echo get_current_network_id(); ?><br>
                                            • Site ID: <?php echo get_current_blog_id(); ?>
                                        <?php else: ?>
                                            <br><strong>Multisite:</strong> No
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="uk-card uk-card-muted uk-card-body uk-padding-remove">
                                    <!-- ...existing debug info... -->

                                </div>

                            </details>
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <?php $this->render_cdswerx_admin_section("footer"); ?>

    </div>
</div>


<!-- Remove Corey Modal -->
<div id="remove-corey-modal" class="uk-modal" uk-modal>
    <div class="uk-modal-dialog">
        <div class="uk-modal-header">
            <h3>Remove Corey - Content Assignment</h3>
        </div>
        <div class="uk-modal-body">
            <p><strong>What should happen to Corey's content?</strong></p>
            <div class="uk-margin">
                <label class="uk-form-label">
                    <input class="uk-radio" type="radio" name="corey_content_action" value="delete" checked>
                    Delete all content associated with Corey
                </label>
            </div>
            <div class="uk-margin">
                <label class="uk-form-label">
                    <input class="uk-radio" type="radio" name="corey_content_action" value="reassign">
                    Reassign content to another user:
                </label>
                <div class="uk-margin-small-top">
                    <select class="uk-select" id="corey-reassign-user-select" disabled>
                        <option value="">Select a user...</option>
                        <?php
                        $corey_user = get_user_by("login", "corey");
                        $exclude_ids = $corey_user ? [$corey_user->ID] : [];
                        $users = get_users([
                            "exclude" => $exclude_ids,
                            "orderby" => "display_name",
                            "order" => "ASC",
                        ]);
                        foreach ($users as $user) {
                            echo '<option value="' .
                                esc_attr($user->ID) .
                                '">' .
                                esc_html($user->display_name) .
                                " (" .
                                esc_html($user->user_login) .
                                ")</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="uk-modal-footer uk-text-right">
            <button class="uk-button uk-button-default uk-margin-small-right" type="button" onclick="UIkit.modal('#remove-corey-modal').hide()">Cancel</button>
            <button class="uk-button uk-button-danger" type="button" id="corey-delete-user-btn">Delete User</button>
        </div>
    </div>
</div>

<!-- Permanent Disable Corey Modal -->
<div id="permanent-disable-corey-modal" class="uk-modal" uk-modal>
    <div class="uk-modal-dialog">
        <div class="uk-modal-header">
            <h3>⚠️ Permanently Disable Corey</h3>
        </div>
        <div class="uk-modal-body">
            <div class="uk-alert-warning" uk-alert>
                <p><strong>Warning:</strong> This will permanently disable Corey activation throughout the plugin.</p>
                <ul>
                    <li>Corey will not appear in activation prompts</li>
                    <li>All Corey-related UI will be hidden</li>
                    <li>Future Corey users must be added manually through WordPress</li>
                </ul>
            </div>

            <?php
            $corey_users = get_users(["login" => "corey"]);
            if (!empty($corey_users)): ?>
                <div class="uk-margin-top">
                    <p><strong>Existing Corey users found:</strong></p>
                    <ul>
                        <?php foreach ($corey_users as $user): ?>
                            <li><?php echo esc_html(
                                    $user->user_login,
                                ); ?> (<?php echo esc_html(
                                            $user->user_email,
                                        ); ?>)</li>
                        <?php endforeach; ?>
                    </ul>
                    <p><strong>What should happen to these users?</strong></p>
                    <div class="uk-margin">
                        <label class="uk-form-label">
                            <input class="uk-radio" type="radio" name="permanent_disable_action" value="keep" checked>
                            Keep existing users (just disable plugin functionality)
                        </label>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label">
                            <input class="uk-radio" type="radio" name="permanent_disable_action" value="remove">
                            Remove existing Corey users (with content reassignment)
                        </label>
                    </div>
                </div>
            <?php endif;
            ?>
        </div>
        <div class="uk-modal-footer uk-text-right">
            <button class="uk-button uk-button-default uk-margin-small-right" type="button" onclick="UIkit.modal('#permanent-disable-corey-modal').hide()">Cancel</button>
            <button class="uk-button uk-button-danger" type="button" id="permanent-disable-confirm-btn">Permanently Disable Corey</button>
        </div>
    </div>
</div>


<script type="text/javascript">
    // Tab functionality
    jQuery(document).ready(function($) {
        $('.cdswerx-tabs a').on('click', function(e) {
            e.preventDefault();

            // Remove active class from all tabs
            $('.cdswerx-tabs a').removeClass('active');
            $('.cdswerx-tab-content').hide();

            // Add active class to clicked tab
            $(this).addClass('active');

            // Show corresponding content
            var target = $(this).attr('href');
            $(target).show();
        });

        // Initialize first tab as active
        $('.cdswerx-tabs a:first').addClass('active');
        $('.cdswerx-tab-content:first').show();
    });

    // Debug logging for Remove Corey functionality
    console.log("CDSWerx Admin Script: Starting to load...");

    // Define Remove Corey functions in global scope (outside jQuery ready)
    window.confirmRemoveCorey = function() {
        console.log("confirmRemoveCorey: Function called");
        try {
            if (confirm('Are you sure you want to remove user Corey? This action cannot be undone.')) {
                console.log("confirmRemoveCorey: User confirmed, calling showCoreyContentReassignment");
                showCoreyContentReassignment();
            } else {
                console.log("confirmRemoveCorey: User cancelled");
            }
        } catch (error) {
            console.error("confirmRemoveCorey: Error occurred:", error);
            alert("Error in confirmRemoveCorey: " + error.message);
        }
    };

    window.showCoreyContentReassignment = function() {
        console.log("showCoreyContentReassignment: Function called");
        try {
            // Check if UIkit is available
            if (typeof UIkit === 'undefined') {
                console.error("showCoreyContentReassignment: UIkit is not loaded");
                alert("Error: UIkit is required but not loaded. Please refresh the page and try again.");
                return;
            }

            console.log("showCoreyContentReassignment: Using custom modal");

            // Show the custom modal
            UIkit.modal('#remove-corey-modal').show();

            // Enable/disable user select based on radio selection
            document.querySelectorAll('input[name="corey_content_action"]').forEach(function(radio) {
                radio.addEventListener('change', function() {
                    var reassignSelect = document.getElementById('corey-reassign-user-select');
                    reassignSelect.disabled = this.value !== 'reassign';
                });
            });

            // Set up Delete User button handler (only once)
            var deleteBtn = document.getElementById('corey-delete-user-btn');
            if (!deleteBtn.dataset.bound) {
                deleteBtn.addEventListener('click', function() {
                    var contentAction = document.querySelector('input[name="corey_content_action"]:checked').value;
                    var reassignUserId = '';

                    console.log("executeCoreyRemoval: Content action:", contentAction);

                    if (contentAction === 'reassign') {
                        reassignUserId = document.getElementById('corey-reassign-user-select').value;
                        if (!reassignUserId) {
                            alert('Please select a user to reassign content to.');
                            return;
                        }
                        console.log("executeCoreyRemoval: Reassigning to user ID:", reassignUserId);
                    } else {
                        console.log("executeCoreyRemoval: Deleting all content");
                    }

                    // Redirect to removal handler
                    var url = '<?php echo admin_url(
                                    "admin.php?page=cdswerx-users&cdswerx_remove_corey=1&_wpnonce=" .
                                        wp_create_nonce("cdswerx_remove_corey"),
                                ); ?>';
                    if (reassignUserId) {
                        url += '&reassign_user=' + reassignUserId;
                    }

                    // Check if we need to continue with permanent disable
                    if (sessionStorage.getItem('cdswerx_continue_permanent_disable') === 'true') {
                        url += '&continue_permanent_disable=1';
                        sessionStorage.removeItem('cdswerx_continue_permanent_disable');
                        console.log("executeCoreyRemoval: Continuing with permanent disable after removal");
                    }

                    console.log("executeCoreyRemoval: Redirecting to:", url);
                    window.location.href = url;
                });
                deleteBtn.dataset.bound = "true";
            }

            console.log("showCoreyContentReassignment: Custom modal setup completed");
        } catch (error) {
            console.error("showCoreyContentReassignment: Error occurred:", error);
            // Fallback to old modal method
            console.log("showCoreyContentReassignment: Falling back to dialog modal");
            showCoreyContentReassignmentFallback();
        }
    };

    // Fallback method using UIkit.modal.dialog (keep existing code as backup)
    window.showCoreyContentReassignmentFallback = function() {
        console.log("showCoreyContentReassignmentFallback: Using fallback dialog modal");
        try {
            // Get list of available users for reassignment
            var userOptions = '';
            <?php
            echo 'console.log("showCoreyContentReassignmentFallback: Generating user options");';
            $corey_user = get_user_by("login", "corey");
            $exclude_ids = $corey_user ? [$corey_user->ID] : [];
            $users = get_users([
                "exclude" => $exclude_ids,
                "orderby" => "display_name",
                "order" => "ASC",
            ]);
            if (empty($users)) {
                echo 'console.log("showCoreyContentReassignmentFallback: No users found for reassignment");';
            } else {
                echo 'console.log("showCoreyContentReassignmentFallback: Found ' .
                    count($users) .
                    ' users for reassignment");';
            }
            foreach ($users as $user) {
                echo "userOptions += '<option value=\"{$user->ID}\">" .
                    esc_js($user->display_name) .
                    " (" .
                    esc_js($user->user_login) .
                    ")</option>';";
            }
            ?>

            console.log("showCoreyContentReassignmentFallback: User options generated, creating dialog modal");

            var modal = UIkit.modal.dialog(`
                <div class="uk-modal-header">
                    <h3>Remove Corey - Content Assignment</h3>
                </div>
                <div class="uk-modal-body">
                    <p><strong>What should happen to Corey's content?</strong></p>
                    <div class="uk-margin">
                        <label class="uk-form-label">
                            <input class="uk-radio" type="radio" name="content_action" value="delete" checked>
                            Delete all content associated with Corey
                        </label>
                    </div>
                    <div class="uk-margin">
                        <label class="uk-form-label">
                            <input class="uk-radio" type="radio" name="content_action" value="reassign">
                            Reassign content to another user:
                        </label>
                        <div class="uk-margin-small-top">
                            <select class="uk-select" id="reassign-user-select" disabled>
                                <option value="">Select a user...</option>
                                ${userOptions}
                            </select>
                        </div>
                    </div>
                </div>
                <div class="uk-modal-footer uk-text-right">
                    <button class="uk-button uk-button-default uk-margin-small-right" onclick="UIkit.modal('#' + this.closest('.uk-modal').id).hide()">Cancel</button>
                    <button class="uk-button uk-button-danger" onclick="executeCoreyRemoval()">Delete User</button>
                </div>
            `);

            console.log("showCoreyContentReassignmentFallback: Dialog modal created successfully");
        } catch (error) {
            console.error("showCoreyContentReassignmentFallback: Error occurred:", error);
            alert("Error in fallback modal: " + error.message);
        }
    };

    window.executeCoreyRemoval = function() {
        console.log("executeCoreyRemoval: Function called for fallback dialog");
        try {
            var modal = document.querySelector('.uk-modal.uk-open');
            if (!modal) {
                console.error("executeCoreyRemoval: No open modal found");
                alert("Error: Could not find modal");
                return;
            }

            var contentAction = modal.querySelector('input[name="content_action"]:checked');
            if (!contentAction) {
                console.error("executeCoreyRemoval: No content action selected");
                alert("Error: No content action selected");
                return;
            }

            var contentActionValue = contentAction.value;
            var reassignUserId = '';

            console.log("executeCoreyRemoval: Content action:", contentActionValue);

            if (contentActionValue === 'reassign') {
                var reassignSelect = modal.querySelector('#reassign-user-select');
                if (!reassignSelect) {
                    console.error("executeCoreyRemoval: Reassign select not found");
                    alert("Error: Could not find user selection");
                    return;
                }
                reassignUserId = reassignSelect.value;
                if (!reassignUserId) {
                    alert('Please select a user to reassign content to.');
                    return;
                }
                console.log("executeCoreyRemoval: Reassigning to user ID:", reassignUserId);
            } else {
                console.log("executeCoreyRemoval: Deleting all content");
            }

            // Close the modal
            UIkit.modal(modal).hide();

            // Show loading state
            if (typeof UIkit.notification !== 'undefined') {
                UIkit.notification('Removing Corey user...', {
                    status: 'primary'
                });
            }

            // Redirect to removal handler
            var url = '<?php echo admin_url(
                            "admin.php?page=cdswerx-users&cdswerx_remove_corey=1&_wpnonce=" .
                                wp_create_nonce("cdswerx_remove_corey"),
                        ); ?>';
            if (reassignUserId) {
                url += '&reassign_user=' + reassignUserId;
            }

            // Check if we need to continue with permanent disable
            if (sessionStorage.getItem('cdswerx_continue_permanent_disable') === 'true') {
                url += '&continue_permanent_disable=1';
                sessionStorage.removeItem('cdswerx_continue_permanent_disable');
                console.log("executeCoreyRemoval: Continuing with permanent disable after removal (fallback)");
            }

            console.log("executeCoreyRemoval: Redirecting to:", url);
            window.location.href = url;
        } catch (error) {
            console.error("executeCoreyRemoval: Error occurred:", error);
            alert("Error in executeCoreyRemoval: " + error.message);
        }
    };

    console.log("CDSWerx Admin Script: Remove Corey functions defined globally");

    jQuery(document).ready(function($) {
        console.log("CDSWerx Admin Script: jQuery ready block starting");

        // Get current options for JavaScript
        var cdswerx_options = <?php echo json_encode(
                                    get_option("cdswerx_options", []),
                                ); ?>;

        // Make options available globally
        window.cdswerx_admin = {
            nonce: '<?php echo wp_create_nonce("cdswerx_admin_nonce"); ?>',
            options: cdswerx_options
        };

        console.log("CDSWerx Admin Script: Options loaded, nonce available");

        // Janice user management functions
        window.activateJanice = function() {
            if (confirm('Activate user janice with Site Admin Manager role?')) {
                // Show immediate visual feedback
                $('button[onclick="activateJanice()"]').prop('disabled', true).html('Activating...');

                // Redirect and ensure page refresh
                window.location.href = '<?php echo admin_url(
                                            "admin.php?page=cdswerx-users&cdswerx_activate_janice=1&_wpnonce=" .
                                                wp_create_nonce("cdswerx_activate_janice"),
                                        ); ?>';


            }
        };

        window.activateJaniceWithEmailUpdate = function() {
            if (confirm('Activate janice and update email to webmaster@counterdesign.ca?')) {
                // Show immediate visual feedback
                $('button[onclick="activateJaniceWithEmailUpdate()"]').prop('disabled', true).html('Activating...');

                // Redirect and ensure page refresh
                window.location.href = '<?php echo admin_url(
                                            "admin.php?page=cdswerx-users&cdswerx_activate_janice=1&update_email=1&_wpnonce=" .
                                                wp_create_nonce("cdswerx_activate_janice"),
                                        ); ?>';


            }
        };



        window.showJaniceUserSelection = function() {
            UIkit.modal.alert('Multiple janice accounts detected. Please contact administrator for manual resolution.');
        };

        // Corey user management functions
        window.activateCorey = function() {
            if (confirm('Activate user corey with Site Admin Manager role?')) {
                // Show immediate visual feedback
                $('button[onclick="activateCorey()"]').prop('disabled', true).html('Activating...');

                // Redirect and ensure page refresh
                window.location.href = '<?php echo admin_url(
                                            "admin.php?page=cdswerx-users&cdswerx_activate_corey=1&_wpnonce=" .
                                                wp_create_nonce("cdswerx_activate_corey"),
                                        ); ?>';


            }
        };

        window.activateCoreyWithEmailUpdate = function() {
            if (confirm('Activate corey and update email to cjason@cloudsurfingmedia.com?')) {
                // Show immediate visual feedback
                $('button[onclick="activateCoreyWithEmailUpdate()"]').prop('disabled', true).html('Activating...');

                // Redirect and ensure page refresh
                window.location.href = '<?php echo admin_url(
                                            "admin.php?page=cdswerx-users&cdswerx_activate_corey=1&update_email=1&_wpnonce=" .
                                                wp_create_nonce("cdswerx_activate_corey"),
                                        ); ?>';


            }
        };



        window.showCoreyUserSelection = function() {
            UIkit.modal.alert('Multiple corey accounts detected. Please contact administrator for manual resolution.');
        };

        // Permanent disable Corey functions
        window.confirmPermanentDisableCorey = function() {
            console.log("confirmPermanentDisableCorey: Function called");
            try {
                if (confirm('Are you sure you want to permanently disable Corey functionality? This will hide all Corey features from the plugin.')) {
                    console.log("confirmPermanentDisableCorey: User confirmed, showing modal");
                    showPermanentDisableCoreyModal();
                } else {
                    console.log("confirmPermanentDisableCorey: User cancelled");
                }
            } catch (error) {
                console.error("confirmPermanentDisableCorey: Error occurred:", error);
                alert("Error in confirmPermanentDisableCorey: " + error.message);
            }
        };

        window.showPermanentDisableCoreyModal = function() {
            console.log("showPermanentDisableCoreyModal: Function called");
            try {
                // Check if UIkit is available
                if (typeof UIkit === 'undefined') {
                    console.error("showPermanentDisableCoreyModal: UIkit is not loaded");
                    alert("Error: UIkit is required but not loaded. Please refresh the page and try again.");
                    return;
                }

                console.log("showPermanentDisableCoreyModal: Showing modal");
                UIkit.modal('#permanent-disable-corey-modal').show();

                // Set up confirm button handler (only once)
                var confirmBtn = document.getElementById('permanent-disable-confirm-btn');
                if (!confirmBtn.dataset.bound) {
                    confirmBtn.addEventListener('click', function() {
                        console.log("permanent-disable-confirm-btn: Clicked");

                        var action = 'keep'; // default
                        var actionRadio = document.querySelector('input[name="permanent_disable_action"]:checked');
                        if (actionRadio) {
                            action = actionRadio.value;
                        }

                        console.log("permanent-disable-confirm-btn: Action selected:", action);

                        if (action === 'remove') {
                            // First remove Corey users, then disable
                            console.log("permanent-disable-confirm-btn: Triggering Corey removal first");
                            UIkit.modal('#permanent-disable-corey-modal').hide();
                            if (confirm('This will first remove Corey users with content reassignment, then permanently disable Corey functionality. Continue?')) {
                                showCoreyContentReassignment();
                                // Store flag to continue with permanent disable after removal
                                sessionStorage.setItem('cdswerx_continue_permanent_disable', 'true');
                            }
                        } else {
                            // Direct permanent disable
                            console.log("permanent-disable-confirm-btn: Proceeding with permanent disable");
                            executePermanentDisableCorey();
                        }
                    });
                    confirmBtn.dataset.bound = "true";
                }

                console.log("showPermanentDisableCoreyModal: Modal setup completed");
            } catch (error) {
                console.error("showPermanentDisableCoreyModal: Error occurred:", error);
                alert("Error in showPermanentDisableCoreyModal: " + error.message);
            }
        };

        window.executePermanentDisableCorey = function() {
            console.log("executePermanentDisableCorey: Function called");
            try {
                UIkit.modal('#permanent-disable-corey-modal').hide();

                // Show loading notification
                if (typeof UIkit.notification !== 'undefined') {
                    UIkit.notification('Permanently disabling Corey...', {
                        status: 'primary'
                    });
                }

                // Redirect to handler
                var url = '<?php echo admin_url(
                                "admin.php?page=cdswerx-users&cdswerx_permanent_disable_corey=1&_wpnonce=" .
                                    wp_create_nonce("cdswerx_permanent_disable_corey"),
                            ); ?>';

                console.log("executePermanentDisableCorey: Redirecting to:", url);
                window.location.href = url;
            } catch (error) {
                console.error("executePermanentDisableCorey: Error occurred:", error);
                alert("Error in executePermanentDisableCorey: " + error.message);
            }
        };

        // Note: Corey removal functions have been moved to global scope above for better accessibility
        console.log("CDSWerx Admin Script: Corey removal functions already defined globally");

        // Advanced management functions
        window.toggleCoreyPermanentDisable = function() {
            var currentlyDisabled = <?php echo get_option(
                                        "cdswerx_corey_permanently_disabled",
                                        false,
                                    )
                                        ? "true"
                                        : "false"; ?>;
            var message = currentlyDisabled ?
                'Enable Corey user for this website? This will allow Corey to be activated.' :
                'Permanently disable Corey user for this website? This will deactivate Corey and prevent future activation.';

            if (confirm(message)) {
                window.location.href = '<?php echo admin_url(
                                            "admin.php?page=cdswerx-users&cdswerx_toggle_corey_disable=1&_wpnonce=" .
                                                wp_create_nonce("cdswerx_toggle_corey_disable"),
                                        ); ?>';
            }
        };

        // Activation prompt functions
        window.showActivationPrompt = function() {
            var modal = UIkit.modal.dialog(`
                <div class="uk-modal-header">
                    <h3>User Activation Options</h3>
                </div>
                <div class="uk-modal-body">
                    <p>Choose which users to activate for this website:</p>
                    <div class="uk-margin">
                        <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom" onclick="activateBothUsers()">
                            Activate Both Janice & Corey
                        </button>
                        <button class="uk-button uk-button-default uk-width-1-1 uk-margin-small-bottom" onclick="activateJaniceOnly()">
                            Activate Janice Only
                        </button>
                        <button class="uk-button uk-button-secondary uk-width-1-1" onclick="dismissPrompt()">
                            Dismiss (Do Nothing)
                        </button>
                    </div>
                </div>
            `);
        };



        console.log("CDSWerx Admin Script: jQuery ready block completed successfully");
    });
</script>