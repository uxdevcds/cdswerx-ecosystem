(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(document).ready(function() {

		// Initialize admin functionality
		CDSWerxAdmin.init();

	});

	/**
	 * CDSWerx Admin Object
	 */
	var CDSWerxAdmin = {

		/**
		 * Initialize admin functionality
		 */
		init: function() {
			this.bindEvents();
			this.initTabs();
			this.initTooltips();
		},

		/**
		 * Bind admin events
		 */
		bindEvents: function() {
			// Settings form handling
			$('#cdswerx-settings-form').on('submit', this.handleSettingsSubmit);

			// Toggle switches
			$('.cdswerx-toggle-switch').on('change', this.handleToggleSwitch);

			// Reset settings
			$('#cdswerx-reset-settings').on('click', this.handleResetSettings);

			// Import/Export
			$('#cdswerx-import-btn').on('click', this.handleImport);
			$('#cdswerx-export-btn').on('click', this.handleExport);

			// Theme Management
			$('#cdswerx-auto-optimize').on('click', function() {
				CDSWerxAdmin.autoOptimizeTheme();
			});
			
			$('#cdswerx-check-status').on('click', function() {
				CDSWerxAdmin.checkSystemStatus();
			});

			// TE-5: Asset Management
			$('#cdswerx-optimize-assets').on('click', function() {
				CDSWerxAdmin.optimizeAssets();
			});
			
			$('#cdswerx-refresh-status').on('click', function() {
				CDSWerxAdmin.refreshAssetStatus();
			});

			// Performance mode buttons
			$('.performance-mode-controls button').on('click', function() {
				var mode = $(this).data('mode');
				CDSWerxAdmin.setPerformanceMode(mode);
			});

			// Auto-refresh asset status on page load
			if ($('#cdswerx-asset-status').length) {
				CDSWerxAdmin.refreshAssetStatus();
			}
		},

		/**
		 * Initialize tabs functionality
		 */
		initTabs: function() {
			$('.cdswerx-tabs a').on('click', function(e) {
				e.preventDefault();

				var target = $(this).attr('href');

				// Remove active class from all tabs and content
				$('.cdswerx-tabs a').removeClass('active');
				$('.cdswerx-tab-content').hide();

				// Add active class to clicked tab and show content
				$(this).addClass('active');
				$(target).show();

				// Save active tab in localStorage
				localStorage.setItem('cdswerx_active_tab', target);
			});

			// Restore active tab from localStorage
			var activeTab = localStorage.getItem('cdswerx_active_tab');
			if (activeTab && $(activeTab).length) {
				$('.cdswerx-tabs a[href="' + activeTab + '"]').trigger('click');
			} else {
				$('.cdswerx-tabs a:first').trigger('click');
			}
		},

		/**
		 * Initialize tooltips
		 */
		initTooltips: function() {
			$('[data-tooltip]').each(function() {
				var tooltip = $(this).data('tooltip');
				$(this).attr('title', tooltip);
			});
		},

		/**
		 * Handle settings form submission
		 */
		handleSettingsSubmit: function(e) {
			var $form = $(this);
			var $submitBtn = $form.find('input[type="submit"]');

			// Show loading state
			$submitBtn.prop('disabled', true).val('Saving...');

			// Show success message after form submission
			setTimeout(function() {
				CDSWerxAdmin.showNotice('Settings saved successfully!', 'success');
				$submitBtn.prop('disabled', false).val('Save Changes');
			}, 1000);
		},

		/**
		 * Handle toggle switch changes
		 */
		handleToggleSwitch: function() {
			var $toggle = $(this);
			var isChecked = $toggle.is(':checked');
			var settingName = $toggle.attr('name');

			// Show feedback
			var message = isChecked ? 'Enabled' : 'Disabled';
			CDSWerxAdmin.showNotice(settingName + ' ' + message, 'info', 2000);
		},

		/**
		 * Handle reset settings
		 */
		handleResetSettings: function(e) {
			e.preventDefault();

			if (confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
				// Reset form
				$('#cdswerx-settings-form')[0].reset();
				CDSWerxAdmin.showNotice('Settings reset to defaults', 'warning');
			}
		},

		/**
		 * Handle import functionality
		 */
		handleImport: function(e) {
			e.preventDefault();

			var input = document.createElement('input');
			input.type = 'file';
			input.accept = '.json';

			input.onchange = function(event) {
				var file = event.target.files[0];
				if (file) {
					var reader = new FileReader();
					reader.onload = function(e) {
						try {
							var settings = JSON.parse(e.target.result);
							CDSWerxAdmin.importSettings(settings);
							CDSWerxAdmin.showNotice('Settings imported successfully!', 'success');
						} catch (error) {
							CDSWerxAdmin.showNotice('Invalid settings file', 'error');
						}
					};
					reader.readAsText(file);
				}
			};

			input.click();
		},

		/**
		 * Handle export functionality
		 */
		handleExport: function(e) {
			e.preventDefault();

			var settings = CDSWerxAdmin.exportSettings();
			var dataStr = JSON.stringify(settings, null, 2);
			var dataBlob = new Blob([dataStr], {type: 'application/json'});

			var link = document.createElement('a');
			link.href = URL.createObjectURL(dataBlob);
			link.download = 'cdswerx-settings.json';
			link.click();

			CDSWerxAdmin.showNotice('Settings exported successfully!', 'success');
		},

		/**
		 * Import settings
		 */
		importSettings: function(settings) {
			// Apply settings to form
			for (var key in settings) {
				var $field = $('[name="' + key + '"]');
				if ($field.length) {
					if ($field.is(':checkbox')) {
						$field.prop('checked', settings[key]);
					} else {
						$field.val(settings[key]);
					}
				}
			}
		},

		/**
		 * Export current settings
		 */
		exportSettings: function() {
			var settings = {};
			$('#cdswerx-settings-form').find('input, select, textarea').each(function() {
				var $field = $(this);
				var name = $field.attr('name');
				if (name) {
					if ($field.is(':checkbox')) {
						settings[name] = $field.is(':checked');
					} else {
						settings[name] = $field.val();
					}
				}
			});
			return settings;
		},

		/**
		 * Show admin notice
		 */
		showNotice: function(message, type, duration) {
			type = type || 'info';
			duration = duration || 4000;

			var $notice = $('<div class="cdswerx-notice notice-' + type + '">' + message + '</div>');
			$('#wpbody-content .wrap').prepend($notice);

			// Auto-hide notice
			setTimeout(function() {
				$notice.fadeOut(function() {
					$(this).remove();
				});
			}, duration);
		},

		/**
		 * AJAX helper function
		 */
		ajax: function(action, data, callback) {
			data = data || {};
			data.action = 'cdswerx_' + action;
			data.nonce = cdswerx_admin.nonce;

			$.post(ajaxurl, data, function(response) {
				if (callback && typeof callback === 'function') {
					callback(response);
				}
			}).fail(function() {
				CDSWerxAdmin.showNotice('AJAX request failed', 'error');
			});
		},

		/**
		 * Theme Management Functions
		 */

		/**
		 * Activate a specific CDSWerx theme
		 */
		activateTheme: function(themeSlug) {
			var confirmMessage = 'Are you sure you want to activate the ' + themeSlug + ' theme?';
			if (!confirm(confirmMessage)) {
				return;
			}

			this.ajax('activate_theme', {theme_slug: themeSlug}, function(response) {
				if (response.success) {
					CDSWerxAdmin.showNotice('Theme activated successfully!', 'success');
					// Refresh the page to update theme status
					setTimeout(function() {
						window.location.reload();
					}, 1500);
				} else {
					CDSWerxAdmin.showNotice('Failed to activate theme: ' + (response.data || 'Unknown error'), 'error');
				}
			});
		},

		/**
		 * Auto-optimize theme selection
		 */
		autoOptimizeTheme: function() {
			$('#cdswerx-auto-result').html('<p>Analyzing your site configuration...</p>');
			
			this.ajax('auto_optimize_theme', {}, function(response) {
				if (response.success) {
					var result = response.data;
					var html = '<div class="notice notice-' + (result.changed ? 'success' : 'info') + ' inline">';
					html += '<p>' + result.message + '</p>';
					if (result.details) {
						html += '<ul>';
						result.details.forEach(function(detail) {
							html += '<li>' + detail + '</li>';
						});
						html += '</ul>';
					}
					html += '</div>';
					
					$('#cdswerx-auto-result').html(html);
					
					if (result.changed) {
						// Refresh the page after 2 seconds to show new theme status
						setTimeout(function() {
							window.location.reload();
						}, 2000);
					}
				} else {
					$('#cdswerx-auto-result').html(
						'<div class="notice notice-error inline"><p>Auto-optimization failed: ' + 
						(response.data || 'Unknown error') + '</p></div>'
					);
				}
			});
		},

		/**
		 * Check system status for theme ecosystem
		 */
		checkSystemStatus: function() {
			$('#cdswerx-auto-result').html('<p>Checking system status...</p>');
			
			this.ajax('check_system_status', {}, function(response) {
				if (response.success) {
					var status = response.data;
					var html = '<div class="cdswerx-system-status" style="background: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa;">';
					html += '<h4>System Status Report</h4>';
					html += '<ul>';
					
					// Current theme status
					html += '<li><strong>Active Theme:</strong> ' + status.current_theme + '</li>';
					
					// Plugin detection
					if (status.elementor_active !== undefined) {
						html += '<li><strong>Elementor:</strong> ' + (status.elementor_active ? 'Active' : 'Not Active') + '</li>';
					}
					if (status.elementor_pro_active !== undefined) {
						html += '<li><strong>Elementor Pro:</strong> ' + (status.elementor_pro_active ? 'Active' : 'Not Active') + '</li>';
					}
					
					// Theme availability
					html += '<li><strong>CDSWerx Themes Available:</strong> ' + status.cdswerx_themes_count + '</li>';
					
					// Recommendations
					if (status.recommendations && status.recommendations.length > 0) {
						html += '<li><strong>Recommendations:</strong><ul>';
						status.recommendations.forEach(function(rec) {
							html += '<li>' + rec + '</li>';
						});
						html += '</ul></li>';
					}
					
					html += '</ul></div>';
					$('#cdswerx-auto-result').html(html);
				} else {
					$('#cdswerx-auto-result').html(
						'<div class="notice notice-error inline"><p>Status check failed: ' + 
						(response.data || 'Unknown error') + '</p></div>'
					);
				}
			});
		},

		/**
		 * TE-5: Asset Management Functions
		 */

		/**
		 * Refresh asset status display
		 */
		refreshAssetStatus: function() {
			$('#asset-status-content').html('<p>Loading asset status...</p>');
			$('#elementor-status-display').html('<p>Checking Elementor integration...</p>');
			
			this.ajax('get_asset_status', {}, function(response) {
				if (response.success) {
					CDSWerxAdmin.displayAssetStatus(response.data);
				} else {
					$('#asset-status-content').html('<p style="color: #dc3232;">Failed to load asset status</p>');
				}
			});
		},

		/**
		 * Display asset status information
		 */
		displayAssetStatus: function(status) {
			var html = '<ul>';
			html += '<li><strong>Theme:</strong> ' + status.theme + '</li>';
			html += '<li><strong>Elementor:</strong> ' + (status.elementor_active ? 'Active' : 'Not Active') + '</li>';
			html += '<li><strong>Elementor Pro:</strong> ' + (status.elementor_pro_active ? 'Active' : 'Not Active') + '</li>';
			html += '<li><strong>Asset Optimization:</strong> ' + (status.asset_optimization_enabled ? 'Enabled' : 'Disabled') + '</li>';
			html += '<li><strong>Custom CSS:</strong> ' + (status.custom_css_enabled ? 'Configured' : 'None') + '</li>';
			html += '<li><strong>Performance Mode:</strong> ' + status.performance_mode + '</li>';
			
			if (status.icon_libraries_loaded && status.icon_libraries_loaded.length > 0) {
				html += '<li><strong>Icon Libraries:</strong> ' + status.icon_libraries_loaded.join(', ') + '</li>';
			}
			
			html += '</ul>';
			$('#asset-status-content').html(html);

			// Update Elementor status
			var elementorHtml = '<ul>';
			elementorHtml += '<li><strong>Elementor Free:</strong> ' + (status.elementor_active ? 'Active' : 'Not Active') + '</li>';
			elementorHtml += '<li><strong>Elementor Pro:</strong> ' + (status.elementor_pro_active ? 'Active' : 'Not Active') + '</li>';
			
			if (status.elementor_active) {
				elementorHtml += '<li style="color: #46b450;">✓ Asset optimization active for Elementor pages</li>';
			}
			
			if (status.elementor_pro_active) {
				elementorHtml += '<li style="color: #46b450;">✓ Enhanced Pro optimizations available</li>';
			}
			
			elementorHtml += '</ul>';
			$('#elementor-status-display').html(elementorHtml);

			// Update performance mode button states
			$('.performance-mode-controls button').removeClass('button-primary').addClass('button');
			$('#performance-mode-' + status.performance_mode).removeClass('button').addClass('button-primary');
		},

		/**
		 * Optimize assets based on current configuration
		 */
		optimizeAssets: function() {
			$('#cdswerx-optimization-result').html('<p>Optimizing assets...</p>');
			
			this.ajax('optimize_assets', {optimization_type: 'auto'}, function(response) {
				if (response.success) {
					var result = response.data;
					var html = '<div class="notice notice-success inline">';
					html += '<h4>Optimization Complete!</h4>';
					
					if (result.optimizations_applied && result.optimizations_applied.length > 0) {
						html += '<p><strong>Applied Optimizations:</strong></p><ul>';
						result.optimizations_applied.forEach(function(opt) {
							html += '<li>' + opt + '</li>';
						});
						html += '</ul>';
					}
					
					if (result.performance_impact) {
						html += '<p><strong>Performance Impact:</strong> ' + result.performance_impact + '</p>';
					}
					
					if (result.recommendations && result.recommendations.length > 0) {
						html += '<p><strong>Recommendations:</strong></p><ul>';
						result.recommendations.forEach(function(rec) {
							html += '<li>' + rec + '</li>';
						});
						html += '</ul>';
					}
					
					html += '</div>';
					$('#cdswerx-optimization-result').html(html);
					
					// Refresh status after optimization
					setTimeout(function() {
						CDSWerxAdmin.refreshAssetStatus();
					}, 1000);
				} else {
					$('#cdswerx-optimization-result').html(
						'<div class="notice notice-error inline"><p>Optimization failed: ' + 
						(response.data || 'Unknown error') + '</p></div>'
					);
				}
			});
		},

		/**
		 * Set performance mode
		 */
		setPerformanceMode: function(mode) {
			this.ajax('toggle_performance_mode', {mode: mode}, function(response) {
				if (response.success) {
					var result = response.data;
					CDSWerxAdmin.showNotice(result.message, 'success');
					
					// Update button states
					$('.performance-mode-controls button').removeClass('button-primary').addClass('button');
					$('#performance-mode-' + mode).removeClass('button').addClass('button-primary');
					
					// Show impact information
					if (result.impact) {
						$('#cdswerx-optimization-result').html(
							'<div class="notice notice-info inline"><p><strong>Mode Impact:</strong> ' + 
							result.impact + '</p></div>'
						);
					}
					
					// Refresh status to reflect changes
					setTimeout(function() {
						CDSWerxAdmin.refreshAssetStatus();
					}, 500);
				} else {
					CDSWerxAdmin.showNotice('Failed to set performance mode: ' + (response.data || 'Unknown error'), 'error');
				}
			});
		}

	};

	// Make CDSWerxAdmin globally available
	window.CDSWerxAdmin = CDSWerxAdmin;

	// Global theme activation function (called from dashboard buttons)
	window.cdswerx_activate_theme = function(themeSlug) {
		CDSWerxAdmin.activateTheme(themeSlug);
	};

})( jQuery );
