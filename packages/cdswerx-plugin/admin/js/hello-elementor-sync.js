/**
 * Hello Elementor Sync Administration JavaScript
 *
 * @since      1.0.4
 * @package    Cdswerx
 * @subpackage Cdswerx/admin/js
 */

(function($) {
    'use strict';

    /**
     * Hello Elementor Sync Admin Controller
     */
    const HelloElementorSyncAdmin = {
        
        /**
         * Initialize the admin interface
         */
        init: function() {
            this.bindEvents();
            this.initDashboard();
            this.loadInitialData();
        },

        /**
         * Bind admin events
         */
        bindEvents: function() {
            // Sync control events
            $(document).on('click', '.cdswerx-manual-sync', this.handleManualSync.bind(this));
            $(document).on('change', '.cdswerx-auto-sync-toggle', this.handleAutoSyncToggle.bind(this));
            $(document).on('click', '.cdswerx-version-increment', this.handleVersionIncrement.bind(this));
            $(document).on('change', '.cdswerx-version-settings', this.handleVersionSettings.bind(this));
            
            // Dashboard refresh events
            $(document).on('click', '.cdswerx-refresh-status', this.refreshSyncStatus.bind(this));
            $(document).on('click', '.cdswerx-clear-cache', this.handleClearCache.bind(this));
            
            // Advanced CSS tab events
            $(document).on('input', '#cdswerx-advanced-css', this.handleCssChange.bind(this));
            $(document).on('click', '.cdswerx-css-version-preview', this.previewCssVersions.bind(this));
        },

        /**
         * Initialize dashboard elements
         */
        initDashboard: function() {
            // Initialize progress bars
            $('.cdswerx-progress-bar').each(function() {
                const progress = $(this).data('progress') || 0;
                $(this).find('.progress-fill').css('width', progress + '%');
            });

            // Initialize status indicators
            this.updateStatusIndicators();
            
            // Initialize tooltips
            $('.cdswerx-tooltip').tooltip();
            
            // Initialize tabs
            $('.cdswerx-tab-nav a').on('click', this.handleTabSwitch.bind(this));
        },

        /**
         * Load initial dashboard data
         */
        loadInitialData: function() {
            this.refreshSyncStatus();
            this.loadVersionHistory();
            this.checkPendingUpdates();
        },

        /**
         * Handle manual sync trigger
         */
        handleManualSync: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const originalText = $button.text();
            
            $button.prop('disabled', true).text(cdswerx_sync.strings.sync_in_progress);
            
            const syncData = {
                action: 'hello_elementor_sync_action',
                sync_action: 'manual_sync',
                nonce: cdswerx_sync.nonce
            };

            $.ajax({
                url: cdswerx_sync.ajax_url,
                type: 'POST',
                data: syncData,
                success: function(response) {
                    if (response.success) {
                        this.showNotice('success', cdswerx_sync.strings.sync_complete);
                        this.refreshSyncStatus();
                    } else {
                        this.showNotice('error', response.message || cdswerx_sync.strings.sync_error);
                    }
                }.bind(this),
                error: function() {
                    this.showNotice('error', cdswerx_sync.strings.sync_error);
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Handle auto sync toggle
         */
        handleAutoSyncToggle: function(e) {
            const $toggle = $(e.currentTarget);
            const isEnabled = $toggle.is(':checked');
            
            const toggleData = {
                action: 'hello_elementor_sync_action',
                sync_action: 'toggle_auto_sync',
                nonce: cdswerx_sync.nonce
            };

            $.ajax({
                url: cdswerx_sync.ajax_url,
                type: 'POST',
                data: toggleData,
                success: function(response) {
                    if (response.success) {
                        this.showNotice('info', response.message);
                        this.updateAutoSyncIndicator(response.auto_sync);
                    }
                }.bind(this)
            });
        },

        /**
         * Handle version increment
         */
        handleVersionIncrement: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const incrementType = $button.data('increment-type') || 'patch';
            
            const versionData = {
                action: 'version_management_action',
                version_action: 'manual_version_increment',
                increment_type: incrementType,
                nonce: cdswerx_sync.nonce
            };

            $.ajax({
                url: cdswerx_sync.ajax_url,
                type: 'POST',
                data: versionData,
                success: function(response) {
                    if (response.success) {
                        this.showNotice('success', `Version incremented to ${response.new_version}`);
                        this.updateVersionDisplay(response.new_version);
                        this.loadVersionHistory();
                    }
                }.bind(this)
            });
        },

        /**
         * Handle version settings change
         */
        handleVersionSettings: function(e) {
            const $input = $(e.currentTarget);
            const setting = $input.data('setting');
            const value = $input.is(':checkbox') ? $input.is(':checked') : $input.val();
            
            // Update settings via AJAX
            const settingsData = {
                action: 'version_management_action',
                version_action: 'update_settings',
                setting: setting,
                value: value,
                nonce: cdswerx_sync.nonce
            };

            $.ajax({
                url: cdswerx_sync.ajax_url,
                type: 'POST',
                data: settingsData,
                success: function(response) {
                    if (response.success) {
                        this.showNotice('info', 'Settings updated');
                    }
                }.bind(this)
            });
        },

        /**
         * Handle CSS changes in Advanced CSS tab
         */
        handleCssChange: function(e) {
            const $textarea = $(e.currentTarget);
            const cssContent = $textarea.val();
            
            // Debounce CSS change detection
            clearTimeout(this.cssChangeTimeout);
            this.cssChangeTimeout = setTimeout(function() {
                this.detectCssVersionChange(cssContent);
            }.bind(this), 1000);
        },

        /**
         * Detect CSS version changes
         */
        detectCssVersionChange: function(cssContent) {
            // Show version increment indicator
            const $indicator = $('.cdswerx-css-version-indicator');
            $indicator.addClass('version-pending').text('Version increment pending...');
            
            // Auto-increment version if enabled
            const $autoVersionToggle = $('#cdswerx-auto-version-css');
            if ($autoVersionToggle.is(':checked')) {
                setTimeout(function() {
                    this.triggerCssVersionIncrement();
                }.bind(this), 2000);
            }
        },

        /**
         * Trigger CSS version increment
         */
        triggerCssVersionIncrement: function() {
            const versionData = {
                action: 'version_management_action',
                version_action: 'css_change_increment',
                nonce: cdswerx_sync.nonce
            };

            $.ajax({
                url: cdswerx_sync.ajax_url,
                type: 'POST',
                data: versionData,
                success: function(response) {
                    if (response.success) {
                        const $indicator = $('.cdswerx-css-version-indicator');
                        $indicator.removeClass('version-pending').addClass('version-updated')
                                .text(`Version updated to ${response.new_version}`);
                        
                        this.updateVersionDisplay(response.new_version);
                    }
                }.bind(this)
            });
        },

        /**
         * Preview CSS versions
         */
        previewCssVersions: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const $modal = $('#cdswerx-version-preview-modal');
            
            // Load version history for preview
            this.loadVersionHistoryModal($modal);
        },

        /**
         * Refresh sync status
         */
        refreshSyncStatus: function() {
            const statusData = {
                action: 'hello_elementor_sync_action',
                sync_action: 'get_sync_status',
                nonce: cdswerx_sync.nonce
            };

            $.ajax({
                url: cdswerx_sync.ajax_url,
                type: 'POST',
                data: statusData,
                success: function(response) {
                    if (response.success) {
                        this.updateSyncStatusDisplay(response);
                    }
                }.bind(this)
            });
        },

        /**
         * Handle cache clearing
         */
        handleClearCache: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const originalText = $button.text();
            
            $button.prop('disabled', true).text('Clearing...');
            
            const cacheData = {
                action: 'version_management_action',
                version_action: 'clear_caches',
                nonce: cdswerx_sync.nonce
            };

            $.ajax({
                url: cdswerx_sync.ajax_url,
                type: 'POST',
                data: cacheData,
                success: function(response) {
                    if (response.success) {
                        this.showNotice('success', response.message);
                    }
                }.bind(this),
                complete: function() {
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Load version history
         */
        loadVersionHistory: function() {
            const historyData = {
                action: 'version_management_action',
                version_action: 'get_version_history',
                limit: 20,
                nonce: cdswerx_sync.nonce
            };

            $.ajax({
                url: cdswerx_sync.ajax_url,
                type: 'POST',
                data: historyData,
                success: function(response) {
                    if (response.success) {
                        this.updateVersionHistoryDisplay(response.history);
                    }
                }.bind(this)
            });
        },

        /**
         * Check for pending updates
         */
        checkPendingUpdates: function() {
            // Check for Hello Elementor updates
            const updateData = {
                action: 'hello_elementor_sync_action',
                sync_action: 'check_updates',
                nonce: cdswerx_sync.nonce
            };

            $.ajax({
                url: cdswerx_sync.ajax_url,
                type: 'POST',
                data: updateData,
                success: function(response) {
                    if (response.success && response.updates_available) {
                        this.showUpdateNotification(response.updates);
                    }
                }.bind(this)
            });
        },

        /**
         * Update sync status display
         */
        updateSyncStatusDisplay: function(data) {
            // Update Hello Elementor status
            const $helloStatus = $('.cdswerx-hello-elementor-status');
            $helloStatus.find('.status-indicator').removeClass().addClass('status-indicator')
                       .addClass(data.hello_elementor_info.installed ? 'status-active' : 'status-inactive');
            $helloStatus.find('.status-text').text(
                data.hello_elementor_info.installed ? 'Installed' : 'Not Installed'
            );
            
            // Update sync mode
            const $syncMode = $('.cdswerx-sync-mode');
            $syncMode.text(data.sync_status.mode.replace('_', ' ').toUpperCase());
            
            // Update last sync time
            if (data.sync_status.last_sync) {
                $('.cdswerx-last-sync').text(this.formatDate(data.sync_status.last_sync));
            }
            
            // Update version info
            if (data.version_info) {
                this.updateVersionInfoDisplay(data.version_info);
            }
        },

        /**
         * Update version info display
         */
        updateVersionInfoDisplay: function(versionInfo) {
            // Update plugin version
            $('.cdswerx-plugin-version').text(versionInfo.plugin.current);
            
            // Update theme versions
            if (versionInfo.theme.current) {
                $('.cdswerx-theme-version').text(versionInfo.theme.current);
            }
            
            if (versionInfo.child_theme.current) {
                $('.cdswerx-child-theme-version').text(versionInfo.child_theme.current);
            }
            
            // Update Hello Elementor version
            if (versionInfo.hello_elementor.current) {
                $('.cdswerx-hello-elementor-version').text(versionInfo.hello_elementor.current);
            }
        },

        /**
         * Update version display
         */
        updateVersionDisplay: function(newVersion) {
            $('.cdswerx-current-version').text(newVersion);
            $('.cdswerx-child-theme-version').text(newVersion);
        },

        /**
         * Update version history display
         */
        updateVersionHistoryDisplay: function(history) {
            const $historyList = $('.cdswerx-version-history-list');
            $historyList.empty();
            
            if (history.length === 0) {
                $historyList.append('<li class="no-history">No version history available</li>');
                return;
            }
            
            history.forEach(function(entry) {
                const $historyItem = $(`
                    <li class="version-history-item">
                        <div class="version-info">
                            <span class="version-type">${entry.type}</span>
                            <span class="version-time">${this.formatDate(entry.timestamp)}</span>
                        </div>
                        <div class="version-details">
                            ${this.formatVersionDetails(entry.data)}
                        </div>
                    </li>
                `);
                $historyList.append($historyItem);
            }.bind(this));
        },

        /**
         * Update status indicators
         */
        updateStatusIndicators: function() {
            $('.cdswerx-status-indicator').each(function() {
                const status = $(this).data('status');
                $(this).removeClass().addClass('cdswerx-status-indicator')
                       .addClass('status-' + status);
            });
        },

        /**
         * Update auto sync indicator
         */
        updateAutoSyncIndicator: function(enabled) {
            const $indicator = $('.cdswerx-auto-sync-indicator');
            $indicator.removeClass().addClass('cdswerx-auto-sync-indicator')
                     .addClass(enabled ? 'auto-sync-enabled' : 'auto-sync-disabled');
            $indicator.text(enabled ? 'Enabled' : 'Disabled');
        },

        /**
         * Show update notification
         */
        showUpdateNotification: function(updates) {
            const $notification = $('.cdswerx-update-notification');
            $notification.removeClass('hidden');
            
            // Populate update details
            const $updateList = $notification.find('.update-list');
            $updateList.empty();
            
            updates.forEach(function(update) {
                $updateList.append(`
                    <div class="update-item">
                        <strong>${update.name}</strong> - Version ${update.version}
                        <button class="button apply-update" data-update-id="${update.id}">Apply Update</button>
                    </div>
                `);
            });
        },

        /**
         * Handle tab switching
         */
        handleTabSwitch: function(e) {
            e.preventDefault();
            
            const $link = $(e.currentTarget);
            const targetTab = $link.attr('href');
            
            // Update active tab
            $('.cdswerx-tab-nav a').removeClass('nav-tab-active');
            $link.addClass('nav-tab-active');
            
            // Show target content
            $('.cdswerx-tab-content').hide();
            $(targetTab).show();
        },

        /**
         * Load version history modal
         */
        loadVersionHistoryModal: function($modal) {
            // Implementation for version history modal
            $modal.show();
        },

        /**
         * Show admin notice
         */
        showNotice: function(type, message) {
            const $notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);
            
            $('.cdswerx-admin-notices').append($notice);
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
        },

        /**
         * Format date for display
         */
        formatDate: function(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString();
        },

        /**
         * Format version details for display
         */
        formatVersionDetails: function(data) {
            if (data.old_version && data.new_version) {
                return `Updated from ${data.old_version} to ${data.new_version}`;
            }
            
            if (data.trigger) {
                return `Triggered by: ${data.trigger.replace('_', ' ')}`;
            }
            
            return JSON.stringify(data);
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        HelloElementorSyncAdmin.init();
    });

})(jQuery);