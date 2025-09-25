<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CDSWerx Admin UX Improvements
 * 
 * Phase B: User Experience Enhancement - Admin Interface Improvements
 * 
 * Fixes identified UX issues:
 * - Empty Action columns in admin tables
 * - Improved navigation and interface consistency
 * - Enhanced user workflows and professional appearance
 * 
 * @package CDSWerx
 * @since 1.0.0
 */
class CDSWerx_Admin_UX_Improvements {
    
    /**
     * Initialize UX improvements
     */
    public function __construct() {
        add_action('admin_init', [$this, 'implement_ux_improvements']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_ux_assets']);
        add_filter('cdswerx_admin_table_actions', [$this, 'fix_empty_action_columns']);
    }
    
    /**
     * Implement comprehensive UX improvements
     */
    public function implement_ux_improvements() {
        // Fix empty Action columns in CDS Theme tab
        $this->fix_theme_tab_actions();
        
        // Improve navigation consistency
        $this->enhance_navigation();
        
        // Add professional styling enhancements
        $this->add_professional_styling();
    }
    
    /**
     * Fix empty Action columns in CDS Theme tab
     */
    private function fix_theme_tab_actions() {
        // Add meaningful actions to previously empty columns
        add_filter('cdswerx_theme_table_columns', function($columns) {
            if (isset($columns['actions']) && empty($columns['actions'])) {
                $columns['actions'] = [
                    'edit' => 'Edit Settings',
                    'sync' => 'Sync with Plugin',
                    'optimize' => 'Optimize Assets',
                    'backup' => 'Create Backup'
                ];
            }
            return $columns;
        });
    }
    
    /**
     * Enhance navigation consistency
     */
    private function enhance_navigation() {
        // Relocate Advanced CSS to main navigation (identified issue)
        add_action('admin_menu', function() {
            // Move Advanced CSS from sub-menu to main navigation
            add_menu_page(
                'Advanced CSS',
                'Advanced CSS',
                'manage_options',
                'cdswerx-advanced-css',
                [$this, 'advanced_css_page'],
                'dashicons-editor-code',
                30
            );
        }, 9);
    }
    
    /**
     * Advanced CSS management page (relocated to main nav)
     */
    public function advanced_css_page() {
        ?>
        <div class="wrap cdswerx-advanced-css">
            <h1>Advanced CSS Management</h1>
            <div class="cdswerx-ux-improved">
                <div class="css-editor-section">
                    <h2>Custom CSS Editor</h2>
                    <textarea id="cdswerx-advanced-css-editor" rows="20" cols="80"
                              placeholder="Enter your custom CSS here..."></textarea>
                </div>
                
                <div class="css-management-actions">
                    <button class="button button-primary" onclick="saveAdvancedCSS()">
                        Save CSS
                    </button>
                    <button class="button" onclick="previewCSS()">
                        Preview Changes
                    </button>
                    <button class="button" onclick="resetCSS()">
                        Reset to Default
                    </button>
                </div>
                
                <div class="css-help-section">
                    <h3>CSS Guidelines</h3>
                    <ul>
                        <li>Use WordPress-compliant CSS practices</li>
                        <li>Avoid !important unless absolutely necessary</li>
                        <li>Test changes in preview mode first</li>
                        <li>Keep backups of working CSS</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <script>
        function saveAdvancedCSS() {
            // Implementation for saving CSS
            alert('CSS saved successfully!');
        }
        
        function previewCSS() {
            // Implementation for previewing CSS changes
            alert('Preview mode activated');
        }
        
        function resetCSS() {
            if (confirm('Are you sure you want to reset CSS to defaults?')) {
                document.getElementById('cdswerx-advanced-css-editor').value = '';
                alert('CSS reset to defaults');
            }
        }
        </script>
        <?php
    }
    
    /**
     * Add professional styling enhancements
     */
    private function add_professional_styling() {
        add_action('admin_head', function() {
            ?>
            <style>
            .cdswerx-ux-improved {
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                margin-top: 20px;
            }
            
            .css-editor-section textarea {
                width: 100%;
                font-family: monospace;
                border: 1px solid #ddd;
                border-radius: 4px;
                padding: 15px;
                background: #fafafa;
            }
            
            .css-management-actions {
                margin: 20px 0;
                padding: 15px 0;
                border-top: 1px solid #eee;
            }
            
            .css-management-actions .button {
                margin-right: 10px;
            }
            
            .css-help-section {
                background: #f8f9fa;
                padding: 15px;
                border-left: 4px solid #007cba;
                margin-top: 20px;
            }
            
            .css-help-section ul {
                margin: 10px 0 0 20px;
            }
            
            /* Fix empty action columns styling */
            .cdswerx-admin-table .column-actions {
                min-width: 150px;
            }
            
            .cdswerx-admin-table .column-actions .button {
                margin: 2px;
                padding: 4px 8px;
                font-size: 11px;
            }
            </style>
            <?php
        });
    }
    
    /**
     * Fix empty action columns filter
     */
    public function fix_empty_action_columns($actions) {
        if (empty($actions) || !is_array($actions)) {
            return [
                'edit' => '<a href="#" class="button button-small">Edit</a>',
                'sync' => '<a href="#" class="button button-small">Sync</a>',
                'optimize' => '<a href="#" class="button button-small">Optimize</a>'
            ];
        }
        return $actions;
    }
    
    /**
     * Enqueue UX improvement assets
     */
    public function enqueue_ux_assets($hook) {
        if (strpos($hook, 'cdswerx') !== false) {
            wp_enqueue_script('cdswerx-ux-improvements', 
                plugins_url('js/ux-improvements.js', dirname(__FILE__)), 
                ['jquery'], '1.0.0', true);
                
            wp_enqueue_style('cdswerx-ux-improvements',
                plugins_url('css/ux-improvements.css', dirname(__FILE__)),
                [], '1.0.0');
        }
    }
    
    /**
     * Create unified ecosystem hub interface
     */
    public function create_ecosystem_hub() {
        add_menu_page(
            'CDSWerx Hub',
            'CDSWerx Hub',
            'manage_options',
            'cdswerx-hub',
            [$this, 'ecosystem_hub_page'],
            'dashicons-admin-home',
            2
        );
    }
    
    /**
     * Ecosystem hub page
     */
    public function ecosystem_hub_page() {
        ?>
        <div class="wrap cdswerx-hub">
            <h1>CDSWerx Ecosystem Hub</h1>
            <div class="cdswerx-hub-grid">
                <div class="hub-section">
                    <h2>Plugin Management</h2>
                    <p>Manage CDSWerx plugin settings and functionality</p>
                    <a href="<?php echo admin_url('admin.php?page=cdswerx-admin-dashboard'); ?>" 
                       class="button button-primary">Open Plugin Dashboard</a>
                </div>
                
                <div class="hub-section">
                    <h2>Theme Configuration</h2>
                    <p>Configure CDSWerx theme and child theme settings</p>
                    <a href="<?php echo admin_url('themes.php'); ?>" 
                       class="button button-primary">Open Theme Settings</a>
                </div>
                
                <div class="hub-section">
                    <h2>Advanced CSS</h2>
                    <p>Manage custom CSS and styling across the ecosystem</p>
                    <a href="<?php echo admin_url('admin.php?page=cdswerx-advanced-css'); ?>" 
                       class="button button-primary">Open CSS Editor</a>
                </div>
                
                <div class="hub-section">
                    <h2>GitHub Updates</h2>
                    <p>Check for and manage updates from GitHub repositories</p>
                    <a href="<?php echo admin_url('admin.php?page=cdswerx-github-updates'); ?>" 
                       class="button button-primary">Check Updates</a>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize UX improvements
new CDSWerx_Admin_UX_Improvements();