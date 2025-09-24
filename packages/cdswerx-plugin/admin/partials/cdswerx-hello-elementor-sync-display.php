<?php
/**
 * Hello Elementor Sync Admin Page Display
 *
 * @since      1.0.4
 * @package    Cdswerx
 * @subpackage Cdswerx/admin/partials
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin instance and Hello Elementor Sync
global $cdswerx_plugin;
$sync_manager = null;
$sync_status = ['mode' => 'independent', 'auto_sync' => false, 'last_sync' => null];
$hello_elementor_info = ['installed' => false, 'active' => false, 'version' => null];
$version_info = ['plugin' => ['current' => CDSWERX_VERSION], 'theme' => ['current' => null], 'child_theme' => ['current' => null]];
$sync_history = get_option('cdswerx_sync_history', []);
$version_history = [];

if ($cdswerx_plugin && method_exists($cdswerx_plugin, 'get_hello_elementor_sync')) {
    $sync_manager = $cdswerx_plugin->get_hello_elementor_sync();
    if ($sync_manager) {
        $sync_status = $sync_manager->get_sync_status();
        $version_manager = $sync_manager->get_version_manager();
        $dependency_checker = $sync_manager->get_dependency_checker();
        
        // Get current status information
        if ($dependency_checker) {
            $hello_elementor_info = $dependency_checker->get_hello_elementor_info();
        }
        if ($version_manager) {
            $version_info = $version_manager->get_all_versions();
            $version_history = $version_manager->get_version_history(10);
        }
    }
}

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Admin Notices Container -->
    <div class="cdswerx-admin-notices"></div>
    
    <!-- Update Notification (Hidden by default) -->
    <div class="cdswerx-update-notification hidden">
        <h3>Updates Available</h3>
        <p>The following updates are available for your Hello Elementor integration:</p>
        <div class="update-list"></div>
    </div>

    <!-- Main Dashboard -->
    <div class="cdswerx-hello-elementor-dashboard">
        
        <!-- Dashboard Header -->
        <div class="cdswerx-dashboard-header">
            <h2 class="cdswerx-dashboard-title">
                <?php esc_html_e('Hello Elementor Sync Dashboard', 'cdswerx'); ?>
            </h2>
            
            <div class="cdswerx-dashboard-actions">
                <button type="button" class="button cdswerx-refresh-status">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Refresh Status', 'cdswerx'); ?>
                </button>
                
                <button type="button" class="button button-primary cdswerx-manual-sync">
                    <span class="dashicons dashicons-sync"></span>
                    <?php esc_html_e('Manual Sync', 'cdswerx'); ?>
                </button>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="cdswerx-dashboard-content">
            
            <!-- Status Grid -->
            <div class="cdswerx-status-grid">
                
                <!-- Hello Elementor Status -->
                <div class="cdswerx-status-card cdswerx-hello-elementor-status">
                    <div class="cdswerx-status-header">
                        <div class="cdswerx-status-icon" style="background: <?php echo $hello_elementor_info['installed'] ? '#46b450' : '#dc3232'; ?>">
                            <span class="dashicons dashicons-admin-appearance"></span>
                        </div>
                        <h3 class="cdswerx-status-title"><?php esc_html_e('Hello Elementor Theme', 'cdswerx'); ?></h3>
                    </div>
                    
                    <div class="cdswerx-status-details">
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('Status:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value">
                                <span class="cdswerx-status-indicator <?php echo $hello_elementor_info['installed'] ? 'status-active' : 'status-inactive'; ?>"></span>
                                <span class="status-text"><?php echo $hello_elementor_info['installed'] ? __('Installed', 'cdswerx') : __('Not Installed', 'cdswerx'); ?></span>
                            </span>
                        </div>
                        
                        <?php if ($hello_elementor_info['installed']): ?>
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('Version:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value cdswerx-hello-elementor-version">
                                <?php echo esc_html($hello_elementor_info['version'] ?? __('Unknown', 'cdswerx')); ?>
                            </span>
                        </div>
                        
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('Active:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value">
                                <?php echo $hello_elementor_info['active'] ? __('Yes', 'cdswerx') : __('No', 'cdswerx'); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Sync Status -->
                <div class="cdswerx-status-card">
                    <div class="cdswerx-status-header">
                        <div class="cdswerx-status-icon" style="background: #2196F3;">
                            <span class="dashicons dashicons-sync"></span>
                        </div>
                        <h3 class="cdswerx-status-title"><?php esc_html_e('Sync Status', 'cdswerx'); ?></h3>
                    </div>
                    
                    <div class="cdswerx-status-details">
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('Mode:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value cdswerx-sync-mode">
                                <?php echo esc_html(ucwords(str_replace('_', ' ', $sync_status['mode']))); ?>
                            </span>
                        </div>
                        
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('Last Sync:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value cdswerx-last-sync">
                                <?php 
                                echo $sync_status['last_sync'] 
                                    ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($sync_status['last_sync']))) 
                                    : __('Never', 'cdswerx'); 
                                ?>
                            </span>
                        </div>
                        
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('Auto Sync:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value">
                                <span class="cdswerx-auto-sync-indicator <?php echo $sync_status['auto_sync'] ? 'auto-sync-enabled' : 'auto-sync-disabled'; ?>">
                                    <?php echo $sync_status['auto_sync'] ? __('Enabled', 'cdswerx') : __('Disabled', 'cdswerx'); ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Version Information -->
                <div class="cdswerx-status-card">
                    <div class="cdswerx-status-header">
                        <div class="cdswerx-status-icon" style="background: #46b450;">
                            <span class="dashicons dashicons-admin-tools"></span>
                        </div>
                        <h3 class="cdswerx-status-title"><?php esc_html_e('Version Information', 'cdswerx'); ?></h3>
                    </div>
                    
                    <div class="cdswerx-status-details">
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('Plugin Version:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value cdswerx-plugin-version">
                                <?php echo esc_html($version_info['plugin']['current'] ?? __('Unknown', 'cdswerx')); ?>
                            </span>
                        </div>
                        
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('CDSWerx Theme:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value cdswerx-theme-version">
                                <?php echo esc_html($version_info['theme']['current'] ?? __('Not Installed', 'cdswerx')); ?>
                            </span>
                        </div>
                        
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('Child Theme:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value cdswerx-child-theme-version">
                                <?php echo esc_html($version_info['child_theme']['current'] ?? __('Not Installed', 'cdswerx')); ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="cdswerx-status-card">
                    <div class="cdswerx-status-header">
                        <div class="cdswerx-status-icon" style="background: #ffb900;">
                            <span class="dashicons dashicons-performance"></span>
                        </div>
                        <h3 class="cdswerx-status-title"><?php esc_html_e('Performance', 'cdswerx'); ?></h3>
                    </div>
                    
                    <div class="cdswerx-status-details">
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('Cache Status:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value">
                                <span class="cdswerx-status-indicator status-active"></span>
                                <?php esc_html_e('Active', 'cdswerx'); ?>
                            </span>
                        </div>
                        
                        <div class="cdswerx-status-item">
                            <span class="cdswerx-status-label"><?php esc_html_e('Last Cache Clear:', 'cdswerx'); ?></span>
                            <span class="cdswerx-status-value">
                                <?php 
                                $last_cache_clear = get_option('cdswerx_last_cache_clear', false);
                                echo $last_cache_clear 
                                    ? esc_html(date_i18n(get_option('time_format'), strtotime($last_cache_clear)))
                                    : __('Never', 'cdswerx'); 
                                ?>
                            </span>
                        </div>
                        
                        <div class="cdswerx-status-item">
                            <button type="button" class="button button-small cdswerx-clear-cache">
                                <?php esc_html_e('Clear Cache', 'cdswerx'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sync Controls -->
            <div class="cdswerx-sync-controls">
                <div class="cdswerx-sync-mode-toggle">
                    <label for="cdswerx-auto-sync">
                        <?php esc_html_e('Auto Sync:', 'cdswerx'); ?>
                    </label>
                    <div class="cdswerx-toggle-switch">
                        <input type="checkbox" id="cdswerx-auto-sync" class="cdswerx-auto-sync-toggle" 
                               <?php checked($sync_status['auto_sync']); ?>>
                        <span class="cdswerx-toggle-slider"></span>
                    </div>
                    <span class="cdswerx-auto-sync-indicator <?php echo $sync_status['auto_sync'] ? 'auto-sync-enabled' : 'auto-sync-disabled'; ?>">
                        <?php echo $sync_status['auto_sync'] ? __('Enabled', 'cdswerx') : __('Disabled', 'cdswerx'); ?>
                    </span>
                </div>
                
                <div class="cdswerx-sync-actions">
                    <button type="button" class="button cdswerx-button-sync cdswerx-manual-sync">
                        <span class="dashicons dashicons-sync"></span>
                        <?php esc_html_e('Run Manual Sync', 'cdswerx'); ?>
                    </button>
                </div>
            </div>

            <!-- Version Management Section -->
            <div class="cdswerx-version-section">
                <div class="cdswerx-version-header">
                    <h3 class="cdswerx-version-title"><?php esc_html_e('Version Management', 'cdswerx'); ?></h3>
                    
                    <div class="cdswerx-version-controls">
                        <button type="button" class="button button-small cdswerx-css-version-preview">
                            <?php esc_html_e('Preview Versions', 'cdswerx'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="cdswerx-version-grid">
                    <!-- Plugin Version -->
                    <div class="cdswerx-version-item">
                        <div class="cdswerx-version-type"><?php esc_html_e('Plugin', 'cdswerx'); ?></div>
                        <div class="cdswerx-current-version">
                            <?php echo esc_html($version_info['plugin']['current'] ?? '1.0.0'); ?>
                        </div>
                        <div class="cdswerx-version-actions">
                            <button type="button" class="cdswerx-version-increment increment-patch" data-increment-type="patch">
                                <?php esc_html_e('Patch', 'cdswerx'); ?>
                            </button>
                            <button type="button" class="cdswerx-version-increment increment-minor" data-increment-type="minor">
                                <?php esc_html_e('Minor', 'cdswerx'); ?>
                            </button>
                            <button type="button" class="cdswerx-version-increment increment-major" data-increment-type="major">
                                <?php esc_html_e('Major', 'cdswerx'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Theme Version -->
                    <div class="cdswerx-version-item">
                        <div class="cdswerx-version-type"><?php esc_html_e('CDSWerx Theme', 'cdswerx'); ?></div>
                        <div class="cdswerx-current-version cdswerx-theme-version">
                            <?php echo esc_html($version_info['theme']['current'] ?? __('N/A', 'cdswerx')); ?>
                        </div>
                        <div class="cdswerx-version-actions">
                            <button type="button" class="cdswerx-version-increment increment-patch" data-increment-type="patch">
                                <?php esc_html_e('Patch', 'cdswerx'); ?>
                            </button>
                            <button type="button" class="cdswerx-version-increment increment-minor" data-increment-type="minor">
                                <?php esc_html_e('Minor', 'cdswerx'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Child Theme Version -->
                    <div class="cdswerx-version-item">
                        <div class="cdswerx-version-type"><?php esc_html_e('Child Theme', 'cdswerx'); ?></div>
                        <div class="cdswerx-current-version cdswerx-child-theme-version">
                            <?php echo esc_html($version_info['child_theme']['current'] ?? __('N/A', 'cdswerx')); ?>
                        </div>
                        <div class="cdswerx-version-actions">
                            <button type="button" class="cdswerx-version-increment increment-patch" data-increment-type="patch">
                                <?php esc_html_e('Patch', 'cdswerx'); ?>
                            </button>
                            <button type="button" class="cdswerx-version-increment increment-minor" data-increment-type="minor">
                                <?php esc_html_e('Minor', 'cdswerx'); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Hello Elementor Version -->
                    <div class="cdswerx-version-item">
                        <div class="cdswerx-version-type"><?php esc_html_e('Hello Elementor', 'cdswerx'); ?></div>
                        <div class="cdswerx-current-version cdswerx-hello-elementor-version">
                            <?php echo esc_html($hello_elementor_info['version'] ?? __('N/A', 'cdswerx')); ?>
                        </div>
                        <div class="cdswerx-version-actions">
                            <span style="font-size: 11px; color: #646970;">
                                <?php esc_html_e('Auto-managed', 'cdswerx'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CSS Version Section -->
            <div class="cdswerx-css-version-section">
                <h3><?php esc_html_e('Advanced CSS Version Management', 'cdswerx'); ?></h3>
                
                <div class="cdswerx-css-version-indicator">
                    <?php esc_html_e('CSS versions are synchronized automatically', 'cdswerx'); ?>
                </div>
                
                <div class="cdswerx-css-version-controls">
                    <div class="cdswerx-css-version-setting">
                        <input type="checkbox" id="cdswerx-auto-version-css" class="cdswerx-version-settings" 
                               data-setting="auto_version_on_css_change" 
                               <?php checked(get_option('cdswerx_auto_version_on_css_change', true)); ?>>
                        <label for="cdswerx-auto-version-css">
                            <?php esc_html_e('Auto-increment version on CSS changes', 'cdswerx'); ?>
                        </label>
                    </div>
                    
                    <div class="cdswerx-css-version-setting">
                        <input type="checkbox" id="cdswerx-cache-bust-css" class="cdswerx-version-settings" 
                               data-setting="cache_bust_on_version_change" 
                               <?php checked(get_option('cdswerx_cache_bust_on_version_change', true)); ?>>
                        <label for="cdswerx-cache-bust-css">
                            <?php esc_html_e('Clear cache on version changes', 'cdswerx'); ?>
                        </label>
                    </div>
                    
                    <div class="cdswerx-css-version-setting">
                        <select class="cdswerx-version-settings" data-setting="css_version_increment_type">
                            <?php 
                            $increment_type = get_option('cdswerx_css_version_increment_type', 'patch');
                            ?>
                            <option value="patch" <?php selected($increment_type, 'patch'); ?>><?php esc_html_e('Patch increments (1.0.1)', 'cdswerx'); ?></option>
                            <option value="minor" <?php selected($increment_type, 'minor'); ?>><?php esc_html_e('Minor increments (1.1.0)', 'cdswerx'); ?></option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Version History -->
            <div class="cdswerx-version-history">
                <div class="cdswerx-version-history-header">
                    <h3 class="cdswerx-version-history-title"><?php esc_html_e('Recent Version Changes', 'cdswerx'); ?></h3>
                </div>
                
                <ul class="cdswerx-version-history-list">
                    <?php if (!empty($version_history)): ?>
                        <?php foreach (array_slice($version_history, 0, 10) as $entry): ?>
                            <li class="version-history-item">
                                <div class="version-info">
                                    <span class="version-type">
                                        <?php echo esc_html(ucwords(str_replace('_', ' ', $entry['type']))); ?>
                                    </span>
                                    <span class="version-time">
                                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($entry['timestamp']))); ?>
                                    </span>
                                </div>
                                <div class="version-details">
                                    <?php 
                                    if (isset($entry['data']['old_version']) && isset($entry['data']['new_version'])) {
                                        printf(
                                            __('Updated from %s to %s', 'cdswerx'),
                                            esc_html($entry['data']['old_version']),
                                            esc_html($entry['data']['new_version'])
                                        );
                                    } elseif (isset($entry['data']['trigger'])) {
                                        printf(
                                            __('Triggered by: %s', 'cdswerx'),
                                            esc_html(str_replace('_', ' ', $entry['data']['trigger']))
                                        );
                                    } else {
                                        echo esc_html($entry['message'] ?? __('Version change recorded', 'cdswerx'));
                                    }
                                    ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="no-history"><?php esc_html_e('No version history available', 'cdswerx'); ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Version Preview Modal (Hidden) -->
<div id="cdswerx-version-preview-modal" style="display: none;">
    <!-- Modal content will be loaded dynamically -->
</div>

<script type="text/javascript">
// Localize script data
window.cdswerx_sync = {
    ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
    nonce: '<?php echo wp_create_nonce('cdswerx_hello_elementor_sync_nonce'); ?>',
    strings: {
        sync_in_progress: '<?php echo esc_js(__('Syncing...', 'cdswerx')); ?>',
        sync_complete: '<?php echo esc_js(__('Sync completed successfully', 'cdswerx')); ?>',
        sync_error: '<?php echo esc_js(__('Sync failed. Please try again.', 'cdswerx')); ?>'
    }
};
</script>