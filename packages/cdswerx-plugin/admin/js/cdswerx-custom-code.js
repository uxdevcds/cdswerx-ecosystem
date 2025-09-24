/**
 * CDSWerx Custom Code Admin JavaScript
 * Enhanced admin interface with Monaco Editor integration
 */

(function($) {
    'use strict';
    
    // Debug: Script loaded
    console.log('CDSWerx Debug: Custom code script loaded - VERSION 1.0.1 WITH UX IMPROVEMENTS');
    console.log('CDSWerx Debug: jQuery version:', $.fn.jquery);
    
    // Add CSS to hide original textareas when CodeMirror is active
    $('<style>').prop('type', 'text/css').html(`
        .CodeMirror + .cdswerx-hide-when-codemirror,
        .wp-editor-wrap .cdswerx-hide-when-codemirror {
            display: none !important;
        }
        .cdswerx-code-editor-wrapper .CodeMirror {
            margin-bottom: 10px;
        }
    `).appendTo('head');

    var CDSWerxCustomCode = {
        
        // CodeMirror Editor instances
        cssEditor: null,
        jsEditor: null,
        
        // Edit mode tracking
        editMode: {
            css: { isEditing: false, codeId: null },
            js: { isEditing: false, codeId: null }
        },
        
        init: function() {
            console.log('CDSWerx Custom Code System initializing...');
            console.log('WordPress version check - wp object:', typeof wp);
            console.log('CodeMirror availability - wp.codeEditor:', typeof wp !== 'undefined' ? typeof wp.codeEditor : 'wp undefined');
            
            this.bindEvents();
            // Don't initialize CodeMirror immediately - wait for Advanced tab to be shown
            this.setupAdvancedTabListener();
            this.loadCodeLists();
        },
        
        initCodeMirrorEditors: function() {
            // Check if editors are already initialized
            if (this.cssEditor && this.jsEditor) {
                console.log('CDSWerx Debug: CodeMirror editors already initialized, skipping');
                return;
            }
            
            // Debug: Check WordPress and CodeMirror availability
            console.log('CDSWerx Debug: wp object exists:', typeof wp !== 'undefined');
            console.log('CDSWerx Debug: wp.codeEditor exists:', typeof wp !== 'undefined' && typeof wp.codeEditor !== 'undefined');
            
            // Check if wp.codeEditor is available
            if (typeof wp === 'undefined' || typeof wp.codeEditor === 'undefined') {
                console.error('CDSWerx Debug: wp.codeEditor not available - falling back to basic textarea');
                alert('DEBUG: CodeMirror not available. Check console for details.');
                return;
            } else {
                console.log('CDSWerx Debug: CodeMirror available, proceeding with initialization');
            }

            // Initialize CSS Editor
            const cssTextarea = document.getElementById('cdswerx-css-editor');
            console.log('CDSWerx Debug: CSS textarea found:', !!cssTextarea);
            console.log('CDSWerx Debug: CSS textarea visible:', cssTextarea ? $(cssTextarea).is(':visible') : false);
            
            if (cssTextarea) {
                try {
                    console.log('CDSWerx Debug: Attempting to initialize CSS CodeMirror...');
                    this.cssEditor = wp.codeEditor.initialize(cssTextarea, {
                        type: 'text/css'
                    });

                    if (this.cssEditor && this.cssEditor.codemirror) {
                        // Update line count on change
                        this.cssEditor.codemirror.on('change', () => {
                            this.updateLineCount('css');
                        });
                        console.log('CDSWerx SUCCESS: CSS CodeMirror initialized successfully');
                        
                        // Hide the basic textarea to eliminate confusion
                        $('#cdswerx_custom_css').hide();
                        console.log('CDSWerx Debug: Basic CSS textarea hidden');
                        
                        // Force visibility and multiple refreshes to handle hidden elements
                        const forceRefresh = () => {
                            if (this.cssEditor && this.cssEditor.codemirror) {
                                this.cssEditor.codemirror.refresh();
                                console.log('CDSWerx Debug: CSS CodeMirror refreshed');
                            }
                        };
                        
                        // Multiple refresh attempts at different intervals
                        setTimeout(forceRefresh, 100);
                        setTimeout(forceRefresh, 500);
                        setTimeout(forceRefresh, 1000);
                    }
                } catch (error) {
                    console.error('CDSWerx ERROR: Failed to initialize CSS CodeMirror:', error);
                }
            } else {
                console.error('CDSWerx ERROR: CSS textarea element not found');
            }

            // Initialize JavaScript Editor
            const jsTextarea = document.getElementById('cdswerx-js-editor');
            console.log('CDSWerx Debug: JS textarea found:', !!jsTextarea);
            console.log('CDSWerx Debug: JS textarea visible:', jsTextarea ? $(jsTextarea).is(':visible') : false);
            
            if (jsTextarea) {
                try {
                    console.log('CDSWerx Debug: Attempting to initialize JS CodeMirror...');
                    this.jsEditor = wp.codeEditor.initialize(jsTextarea, {
                        type: 'application/javascript'
                    });

                    if (this.jsEditor && this.jsEditor.codemirror) {
                        // Update line count on change
                        this.jsEditor.codemirror.on('change', () => {
                            this.updateLineCount('js');
                        });
                        console.log('CDSWerx SUCCESS: JavaScript CodeMirror initialized successfully');
                        
                        // Hide the basic textarea to eliminate confusion
                        $('#cdswerx_custom_javascript').hide();
                        console.log('CDSWerx Debug: Basic JS textarea hidden');
                        
                        // Force visibility and multiple refreshes to handle hidden elements
                        const forceRefresh = () => {
                            if (this.jsEditor && this.jsEditor.codemirror) {
                                this.jsEditor.codemirror.refresh();
                                console.log('CDSWerx Debug: JS CodeMirror refreshed');
                            }
                        };
                        
                        // Multiple refresh attempts at different intervals
                        setTimeout(forceRefresh, 100);
                        setTimeout(forceRefresh, 500);
                        setTimeout(forceRefresh, 1000);
                    }
                } catch (error) {
                    console.error('CDSWerx ERROR: Failed to initialize JavaScript CodeMirror:', error);
                }
            } else {
                console.error('CDSWerx ERROR: JS textarea element not found');
            }
        },

        ensureCodeEditorsVisible: function() {
            console.log('CDSWerx Debug: Ensuring code editors are visible...');
            
            // Make sure CSS tab is active
            $('.cdswerx-tab-nav a').removeClass('active');
            $('.cdswerx-tab-nav a[href="#css-tab"]').addClass('active');
            $('.cdswerx-tab-content').removeClass('active');
            $('#css-tab').addClass('active').show();
            
            // Force refresh both editors
            setTimeout(() => {
                if (this.cssEditor && this.cssEditor.codemirror) {
                    console.log('CDSWerx Debug: Forcing CSS editor refresh and focus');
                    this.cssEditor.codemirror.refresh();
                    this.cssEditor.codemirror.focus();
                }
                if (this.jsEditor && this.jsEditor.codemirror) {
                    console.log('CDSWerx Debug: Forcing JS editor refresh');
                    this.jsEditor.codemirror.refresh();
                }
            }, 200);
        },

        updateLineCount: function(type) {
            const editor = type === 'css' ? this.cssEditor : this.jsEditor;
            if (editor && editor.codemirror) {
                const lineCount = editor.codemirror.lineCount();
                $(`.${type}-line-count`).text(lineCount);
            }
        },

        setupAdvancedTabListener: function() {
            console.log('CDSWerx Debug: Setting up Advanced tab listener');
            
            // Listen for clicks on MAIN Advanced tab (WordPress admin tabs)
            $(document).on('click', '.cdswerx-tabs a[href="#advanced"]', function(e) {
                console.log('CDSWerx Debug: MAIN Advanced tab clicked');
                
                // Use setTimeout to ensure the tab content is visible before initializing/refreshing CodeMirror
                setTimeout(function() {
                    console.log('CDSWerx Debug: Checking CodeMirror status after MAIN tab switch');
                    
                    // If editors exist, refresh them; otherwise initialize
                    if (CDSWerxCustomCode.cssEditor && CDSWerxCustomCode.jsEditor) {
                        console.log('CDSWerx Debug: CodeMirror editors exist, refreshing...');
                        CDSWerxCustomCode.ensureCodeEditorsVisible();
                    } else {
                        console.log('CDSWerx Debug: CodeMirror editors not initialized, initializing now...');
                        CDSWerxCustomCode.initCodeMirrorEditors();
                        setTimeout(() => {
                            CDSWerxCustomCode.ensureCodeEditorsVisible();
                        }, 300);
                    }
                }, 200);
            });
            
            // Also detect when Advanced tab becomes visible via other methods (UIkit tabs, etc.)
            // Use MutationObserver to detect visibility changes
            if (window.MutationObserver) {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                            const target = mutation.target;
                            if (target.id === 'advanced' && $(target).is(':visible')) {
                                console.log('CDSWerx Debug: Advanced tab became visible via mutation observer');
                                setTimeout(function() {
                                    if (CDSWerxCustomCode.cssEditor && CDSWerxCustomCode.jsEditor) {
                                        CDSWerxCustomCode.cssEditor.codemirror.refresh();
                                        CDSWerxCustomCode.jsEditor.codemirror.refresh();
                                    }
                                }, 100);
                            }
                        }
                    });
                });
                
                const advancedTab = document.getElementById('advanced');
                if (advancedTab) {
                    observer.observe(advancedTab, { attributes: true, attributeFilter: ['style'] });
                }
            }
            
            // Also check if we're already on the Advanced tab (direct link or refresh)
            if ($('#advanced').is(':visible')) {
                console.log('CDSWerx Debug: Advanced tab already visible, initializing CodeMirror');
                setTimeout(function() {
                    CDSWerxCustomCode.initCodeMirrorEditors();
                    
                    // Also ensure CSS tab is active by default and force visibility
                    CDSWerxCustomCode.ensureCodeEditorsVisible();
                }, 500);
            }
        },

        bindEvents: function() {
            // Tab switching
            $('.cdswerx-tab-nav a').on('click', CDSWerxCustomCode.switchTab);
            
            // Save buttons
            $('#css-save').on('click', CDSWerxCustomCode.saveCSSCode);
            $('#js-save').on('click', CDSWerxCustomCode.saveJSCode);
            
            // Cancel edit buttons
            $('#css-cancel').on('click', function() { CDSWerxCustomCode.cancelEdit('css'); });
            $('#js-cancel').on('click', function() { CDSWerxCustomCode.cancelEdit('js'); });
            
            // New code buttons
            $('#css-new').on('click', function() { CDSWerxCustomCode.clearForm('css'); });
            $('#js-new').on('click', function() { CDSWerxCustomCode.clearForm('js'); });
            
            // Category change
            $('#css-category, #js-category').on('change', this.loadCodeForCategory);
            
            // Code list actions
            $(document).on('click', '.cdswerx-code-item-edit', this.editCode);
            $(document).on('click', '.cdswerx-code-item-delete', this.deleteCode);
            $(document).on('click', '.cdswerx-code-item-toggle', this.toggleCode);
            
            // Setup Monaco Editor toolbar actions
            this.setupToolbarActions();
        },

        // Update line count display
        updateLineCount: function(editorType) {
            if (editorType === 'css' && this.cssEditor) {
                const lines = this.cssEditor.getModel().getLineCount();
                $('.css-line-count').text(lines);
            } else if (editorType === 'js' && this.jsEditor) {
                const lines = this.jsEditor.getModel().getLineCount();
                $('.js-line-count').text(lines);
            }
        },

        // Monaco Editor Toolbar Actions
        setupToolbarActions: function() {
            const self = this;

            // Format Code functionality
            $(document).on('click', '.cdswerx-format-code', function(e) {
                e.preventDefault();
                const editorType = $(this).data('editor');
                
                if (editorType === 'css' && self.cssEditor) {
                    self.cssEditor.getAction('editor.action.formatDocument').run();
                } else if (editorType === 'js' && self.jsEditor) {
                    self.jsEditor.getAction('editor.action.formatDocument').run();
                }
            });

            // Find/Replace functionality
            $(document).on('click', '.cdswerx-find-replace', function(e) {
                e.preventDefault();
                const editorType = $(this).data('editor');
                
                if (editorType === 'css' && self.cssEditor) {
                    self.cssEditor.getAction('actions.find').run();
                } else if (editorType === 'js' && self.jsEditor) {
                    self.jsEditor.getAction('actions.find').run();
                }
            });
        },
        
        switchTab: function(e) {
            e.preventDefault();
            console.log('CDSWerx Debug: Tab switch clicked');
            
            var $this = $(this);
            var target = $this.attr('href');
            console.log('CDSWerx Debug: Switching to tab:', target);
            
            // Update navigation
            $('.cdswerx-tab-nav a').removeClass('active');
            $this.addClass('active');
            
            // Update content
            $('.cdswerx-tab-content').removeClass('active');
            $(target).addClass('active');
            
            // Refresh CodeMirror editors when switching to CSS or JS tabs
            setTimeout(function() {
                if ((target === '#css-tab' || target === '#js-tab') && CDSWerxCustomCode.cssEditor && CDSWerxCustomCode.jsEditor) {
                    console.log('CDSWerx Debug: Refreshing CodeMirror editors after tab switch to', target);
                    CDSWerxCustomCode.cssEditor.codemirror.refresh();
                    CDSWerxCustomCode.jsEditor.codemirror.refresh();
                }
            }, 100);
        },
        
        initCodeEditor: function() {
            // Basic syntax highlighting for textareas
            $('.cdswerx-code-editor').each(function() {
                var $editor = $(this);
                
                // Add line numbers and basic styling
                $editor.wrap('<div class="cdswerx-editor-container"></div>');
                $editor.addClass('cdswerx-enhanced-editor');
                
                // Add editor toolbar
                var toolbar = '<div class="cdswerx-editor-toolbar">' +
                    '<button type="button" class="button cdswerx-editor-format" data-editor="' + $editor.attr('id') + '">' +
                    'Format Code</button>' +
                    '<span class="cdswerx-editor-info">Lines: <span class="line-count">0</span></span>' +
                    '</div>';
                
                $editor.before(toolbar);
                
                // Update line count on input
                $editor.on('input', function() {
                    var lines = $(this).val().split('\n').length;
                    $(this).siblings('.cdswerx-editor-toolbar').find('.line-count').text(lines);
                });
                
                // Trigger initial count
                $editor.trigger('input');
            });
            
            // Format code button
            $(document).on('click', '.cdswerx-editor-format', function() {
                var editorId = $(this).data('editor');
                var $editor = $('#' + editorId);
                var content = $editor.val();
                
                if (editorId.includes('css')) {
                    $editor.val(CDSWerxCustomCode.formatCSS(content));
                } else if (editorId.includes('js')) {
                    $editor.val(CDSWerxCustomCode.formatJS(content));
                }
                
                $editor.trigger('input');
            });
        },
        
        formatCSS: function(css) {
            // Basic CSS formatting
            return css
                .replace(/\s*{\s*/g, ' {\n    ')
                .replace(/;\s*/g, ';\n    ')
                .replace(/\s*}\s*/g, '\n}\n\n')
                .replace(/\n    \n}/g, '\n}')
                .trim();
        },
        
        formatJS: function(js) {
            // Basic JavaScript formatting
            return js
                .replace(/\s*{\s*/g, ' {\n    ')
                .replace(/;\s*(?![^(]*\))/g, ';\n    ')
                .replace(/\s*}\s*/g, '\n}\n\n')
                .trim();
        },
        
        saveCSSCode: function() {
            CDSWerxCustomCode.saveCode('css');
        },
        
        saveJSCode: function() {
            CDSWerxCustomCode.saveCode('js');
        },
        
        saveCode: function(type) {
            var editor = type === 'css' ? this.cssEditor : this.jsEditor;
            var content = editor && editor.codemirror ? editor.codemirror.getValue() : $('#cdswerx-' + type + '-editor').val();
            var editInfo = this.editMode[type];
            
            var data = {
                action: editInfo.isEditing ? 'cdswerx_update_custom_code' : 'cdswerx_save_custom_code',
                nonce: cdswerx_ajax.nonce,
                code_type: type,
                category: $('#' + type + '-category').val(),
                name: $('#' + type + '-name').val(),
                content: content,
                is_active: $('#' + type + '-active').is(':checked') ? 1 : 0,
                load_priority: $('#' + type + '-priority').val()
            };
            
            // Add code ID if we're updating
            if (editInfo.isEditing && editInfo.codeId) {
                data.code_id = editInfo.codeId;
            }
            
            // Validation
            if (!data.name.trim()) {
                alert('Please enter a name for this code.');
                return;
            }
            
            if (!data.content.trim()) {
                alert('Please enter some code content.');
                return;
            }
            
            var $saveBtn = $('#' + type + '-save');
            $saveBtn.prop('disabled', true).text(editInfo.isEditing ? 'Updating...' : 'Saving...');
            
            $.ajax({
                url: cdswerx_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        alert('Code saved successfully!');
                        CDSWerxCustomCode.clearForm(type);
                        CDSWerxCustomCode.loadCodeList(type);
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Network error occurred. Please try again.');
                },
                complete: function() {
                    $saveBtn.prop('disabled', false).text('Save ' + (type === 'css' ? 'CSS' : 'JavaScript'));
                }
            });
        },
        
        clearForm: function(type) {
            $('#' + type + '-name').val('');
            $('#' + type + '-category').val('global');
            $('#' + type + '-priority').val('10');
            $('#' + type + '-active').prop('checked', true);
            
            // Clear editor content
            var editor = type === 'css' ? this.cssEditor : this.jsEditor;
            if (editor && editor.codemirror) {
                editor.codemirror.setValue('');
            } else {
                $('#cdswerx-' + type + '-editor').val('');
            }
            
            // Reset edit mode
            this.editMode[type] = {
                isEditing: false,
                codeId: null
            };
            
            // Reset save button text and button visibility
            $('#' + type + '-save').text('Save ' + (type === 'css' ? 'CSS' : 'JavaScript'));
            $('#' + type + '-cancel').hide();
            $('#' + type + '-new').show();
        },
        
        cancelEdit: function(type) {
            if (confirm('Are you sure you want to cancel editing? Any unsaved changes will be lost.')) {
                this.clearForm(type);
            }
        },
        
        loadCodeLists: function() {
            this.loadCodeList('css');
            this.loadCodeList('js');
        },
        
        loadCodeList: function(type) {
            var data = {
                action: 'cdswerx_load_custom_code',
                nonce: cdswerx_ajax.nonce,
                code_type: type,
                category: $('#' + type + '-category').val()
            };
            
            $.ajax({
                url: cdswerx_ajax.ajax_url,
                type: 'GET',
                data: data,
                success: function(response) {
                    if (response.success) {
                        CDSWerxCustomCode.renderCodeList(type, response.data.codes);
                    }
                }
            });
        },
        
        loadCodeForCategory: function() {
            var $select = $(this);
            var type = $select.attr('id').replace('-category', '');
            CDSWerxCustomCode.loadCodeList(type);
        },
        
        renderCodeList: function(type, codes) {
            var $list = $('#' + type + '-code-list');
            $list.empty();
            
            if (!codes || codes.length === 0) {
                $list.html('<p class="cdswerx-no-codes">No saved ' + type.toUpperCase() + ' code found.</p>');
                return;
            }
            
            var html = '<div class="cdswerx-code-items">';
            
            $.each(codes, function(index, code) {
                var statusClass = parseInt(code.is_active) ? 'active' : 'inactive';
                var statusText = parseInt(code.is_active) ? 'Active' : 'Inactive';
                
                html += '<div class="cdswerx-code-item ' + statusClass + '" data-id="' + code.id + '">' +
                    '<div class="cdswerx-code-item-header">' +
                    '<h5>' + code.name + '</h5>' +
                    '<span class="cdswerx-code-status ' + statusClass + '">' + statusText + '</span>' +
                    '</div>' +
                    '<div class="cdswerx-code-item-meta">' +
                    'Category: ' + code.category + ' | Priority: ' + code.load_priority + ' | ' +
                    'Created: ' + new Date(code.created_date).toLocaleDateString() +
                    '</div>' +
                    '<div class="cdswerx-code-item-actions">' +
                    '<button class="button cdswerx-code-item-edit">Edit</button>' +
                    '<button class="button cdswerx-code-item-toggle">' + 
                    (parseInt(code.is_active) ? 'Disable' : 'Enable') + '</button>' +
                    '<button class="button cdswerx-code-item-delete">Delete</button>' +
                    '</div>' +
                    '</div>';
            });
            
            html += '</div>';
            $list.html(html);
        },
        
        editCode: function() {
            var $item = $(this).closest('.cdswerx-code-item');
            var codeId = $item.data('id');
            
            // Get code details via AJAX and populate form
            var data = {
                action: 'cdswerx_get_code_by_id',
                nonce: cdswerx_ajax.nonce,
                code_id: codeId
            };
            
            $.ajax({
                url: cdswerx_ajax.ajax_url,
                type: 'GET',
                data: data,
                success: function(response) {
                    if (response.success) {
                        var code = response.data.code;
                        var type = code.code_type;
                        
                        // Populate form fields
                        $('#' + type + '-category').val(code.category);
                        $('#' + type + '-name').val(code.name);
                        $('#' + type + '-priority').val(code.load_priority);
                        $('#' + type + '-active').prop('checked', parseInt(code.is_active));
                        
                        // Set editor content
                        var editor = type === 'css' ? CDSWerxCustomCode.cssEditor : CDSWerxCustomCode.jsEditor;
                        if (editor && editor.codemirror) {
                            editor.codemirror.setValue(code.content);
                        } else {
                            $('#cdswerx-' + type + '-editor').val(code.content);
                        }
                        
                        // Set edit mode
                        CDSWerxCustomCode.editMode[type] = {
                            isEditing: true,
                            codeId: code.id
                        };
                        
                        // Update save button text to indicate editing
                        $('#' + type + '-save').text('Update ' + (type === 'css' ? 'CSS' : 'JavaScript'));
                        
                        // Show cancel button, hide new button
                        $('#' + type + '-cancel').show();
                        $('#' + type + '-new').hide();
                        
                        // Switch to appropriate tab
                        $('.cdswerx-tab-nav a[href="#' + type + '-tab"]').trigger('click');
                        
                        // Scroll to editor
                        $('html, body').animate({
                            scrollTop: $('#cdswerx-' + type + '-editor').offset().top - 100
                        }, 500);
                    }
                }
            });
        },
        
        deleteCode: function() {
            if (!confirm('Are you sure you want to delete this code? This action cannot be undone.')) {
                return;
            }
            
            var $item = $(this).closest('.cdswerx-code-item');
            var codeId = $item.data('id');
            
            var data = {
                action: 'cdswerx_delete_custom_code',
                nonce: cdswerx_ajax.nonce,
                code_id: codeId
            };
            
            $.ajax({
                url: cdswerx_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        $item.fadeOut(300, function() {
                            $(this).remove();
                        });
                        alert('Code deleted successfully.');
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                }
            });
        },
        
        toggleCode: function() {
            var $item = $(this).closest('.cdswerx-code-item');
            var codeId = $item.data('id');
            
            var data = {
                action: 'cdswerx_toggle_code_status',
                nonce: cdswerx_ajax.nonce,
                code_id: codeId
            };
            
            $.ajax({
                url: cdswerx_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        // Refresh the code list to show updated status
                        var activeTab = $('.cdswerx-tab-content.active').attr('id');
                        var type = activeTab.replace('-tab', '');
                        CDSWerxCustomCode.loadCodeList(type);
                        
                        alert('Code status updated successfully.');
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('Document ready - checking for Custom Code manager...');
        console.log('Custom Code manager found:', $('#cdswerx-custom-code-manager').length > 0);
        console.log('CSS textarea found:', $('#cdswerx-css-editor').length > 0);
        console.log('JS textarea found:', $('#cdswerx-js-editor').length > 0);
        console.log('CSS New button found:', $('#css-new').length > 0);
        console.log('CSS Cancel button found:', $('#css-cancel').length > 0);
        console.log('JS New button found:', $('#js-new').length > 0);
        console.log('JS Cancel button found:', $('#js-cancel').length > 0);
        
        if ($('#cdswerx-custom-code-manager').length > 0) {
            CDSWerxCustomCode.init();
        } else {
            console.log('Custom Code manager not found on this page');
        }
    });

})(jQuery);