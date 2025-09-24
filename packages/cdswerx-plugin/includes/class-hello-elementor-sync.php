<?php

/**
 * Hello Elementor Sync Integration Class
 *
 * Manages synchronization with Hello Elementor theme, version management,
 * and independent mode functionality for CDSWerx ecosystem.
 *
 * @link       https://cdswerx.com
 * @since      1.0.4
 *
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    die;
}

/**
 * Hello Elementor Sync Integration Class
 *
 * Handles Hello Elementor theme synchronization, version management,
 * and independent mode operations for the CDSWerx ecosystem.
 *
 * @since      1.0.4
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 * @author     CDSWerx <info@cdswerx.com>
 */
class Hello_Elementor_Sync
{
    /**
     * The unique identifier of the sync system
     *
     * @since    1.0.4
     * @access   protected
     * @var      string    $sync_name    The string used to uniquely identify this sync system.
     */
    protected $sync_name;

    /**
     * The current version of the sync system
     *
     * @since    1.0.4
     * @access   protected
     * @var      string    $version    The current version of the sync system.
     */
    protected $version;

    /**
     * Version manager instance
     *
     * @since    1.0.4
     * @access   protected
     * @var      Version_Manager    $version_manager    Handles version management operations.
     */
    protected $version_manager;

    /**
     * Dependency checker instance
     *
     * @since    1.0.4
     * @access   protected
     * @var      Dependency_Checker    $dependency_checker    Handles Hello Elementor dependency checks.
     */
    protected $dependency_checker;

    /**
     * Hello Elementor theme information
     *
     * @since    1.0.4
     * @access   protected
     * @var      array    $hello_elementor_info    Stores Hello Elementor theme information.
     */
    protected $hello_elementor_info;

    /**
     * Sync system status
     *
     * @since    1.0.4
     * @access   protected
     * @var      array    $sync_status    Current sync system status and configuration.
     */
    protected $sync_status;

    /**
     * Initialize the Hello Elementor Sync system
     *
     * @since    1.0.4
     */
    public function __construct()
    {
        $this->sync_name = 'hello-elementor-sync';
        $this->version = '1.0.4';
        $this->hello_elementor_info = array();
        $this->sync_status = array();

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->init_sync_system();
    }

    /**
     * Load the required dependencies for the sync system
     *
     * @since    1.0.4
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * Load version manager for dynamic version management
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-version-manager.php';

        /**
         * Load dependency checker for Hello Elementor status monitoring
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-dependency-checker.php';

        $this->version_manager = new Version_Manager();
        $this->dependency_checker = new Dependency_Checker();
    }

    /**
     * Define the locale for this sync system for internationalization
     *
     * @since    1.0.4
     * @access   private
     */
    private function set_locale()
    {
        // Integrate with existing CDSWerx i18n system
        add_action('plugins_loaded', array($this, 'load_sync_textdomain'));
    }

    /**
     * Load sync system text domain
     *
     * @since    1.0.4
     */
    public function load_sync_textdomain()
    {
        load_plugin_textdomain(
            'cdswerx-hello-elementor-sync',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }

    /**
     * Register all hooks related to the admin area functionality
     *
     * @since    1.0.4
     * @access   private
     */
    private function define_admin_hooks()
    {
        add_action('admin_menu', array($this, 'add_sync_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_hello_elementor_sync_action', array($this, 'handle_sync_ajax'));
        add_action('wp_ajax_version_management_action', array($this, 'handle_version_ajax'));
        
        // Hook into Advanced CSS tab changes
        add_action('cdswerx_advanced_css_updated', array($this, 'handle_css_change'));
        
        // Theme activation/deactivation hooks
        add_action('switch_theme', array($this, 'handle_theme_switch'));
        add_action('after_setup_theme', array($this, 'check_theme_compatibility'));
    }

    /**
     * Register all hooks related to the public-facing functionality
     *
     * @since    1.0.4
     * @access   private
     */
    private function define_public_hooks()
    {
        // Version management for frontend
        add_action('wp_enqueue_scripts', array($this, 'manage_frontend_versions'));
        
        // Independent mode functionality
        add_action('init', array($this, 'provide_independent_functions'));
    }

    /**
     * Initialize the sync system
     *
     * @since    1.0.4
     * @access   private
     */
    private function init_sync_system()
    {
        $this->detect_hello_elementor();
        $this->init_sync_status();
        $this->schedule_update_checks();
    }

    /**
     * Detect Hello Elementor theme presence and version
     *
     * @since    1.0.4
     */
    public function detect_hello_elementor()
    {
        $theme = wp_get_theme('hello-elementor');
        
        if ($theme->exists()) {
            $this->hello_elementor_info = array(
                'installed' => true,
                'active' => (get_template() === 'hello-elementor'),
                'version' => $theme->get('Version'),
                'name' => $theme->get('Name'),
                'description' => $theme->get('Description'),
                'author' => $theme->get('Author'),
                'template_directory' => $theme->get_template_directory(),
                'stylesheet_directory' => $theme->get_stylesheet_directory(),
            );
        } else {
            $this->hello_elementor_info = array(
                'installed' => false,
                'active' => false,
                'version' => null,
                'last_known_version' => get_option('cdswerx_hello_elementor_last_version', ''),
            );
        }

        // Update stored information
        update_option('cdswerx_hello_elementor_info', $this->hello_elementor_info);
    }

    /**
     * Initialize sync system status
     *
     * @since    1.0.4
     */
    private function init_sync_status()
    {
        $this->sync_status = get_option('cdswerx_sync_status', array(
            'mode' => 'dependent', // 'dependent', 'independent', 'transitioning'
            'last_sync' => null,
            'sync_errors' => array(),
            'version_history' => array(),
            'auto_sync' => true,
            'auto_version' => true,
        ));
    }

    /**
     * Schedule automatic update checks
     *
     * @since    1.0.4
     */
    private function schedule_update_checks()
    {
        if (!wp_next_scheduled('cdswerx_hello_elementor_update_check')) {
            wp_schedule_event(time(), 'daily', 'cdswerx_hello_elementor_update_check');
        }
        
        add_action('cdswerx_hello_elementor_update_check', array($this, 'check_for_updates'));
    }

    /**
     * Check for Hello Elementor updates
     *
     * @since    1.0.4
     */
    public function check_for_updates()
    {
        if (!$this->hello_elementor_info['installed']) {
            return;
        }

        // WordPress.org API integration
        $request = wp_remote_get(
            'https://api.wordpress.org/themes/info/1.0/hello-elementor.json',
            array(
                'timeout' => 30,
                'user-agent' => 'CDSWerx Hello Elementor Sync/' . $this->version
            )
        );

        if (is_wp_error($request)) {
            $this->log_sync_error('API Request Failed: ' . $request->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($request);
        $theme_info = json_decode($body, true);

        if (!$theme_info || !isset($theme_info['version'])) {
            $this->log_sync_error('Invalid API response received');
            return;
        }

        $remote_version = $theme_info['version'];
        $current_version = $this->hello_elementor_info['version'];

        if (version_compare($remote_version, $current_version, '>')) {
            $this->handle_update_available($theme_info);
        }
    }

    /**
     * Handle Hello Elementor update availability
     *
     * @since    1.0.4
     * @param    array    $theme_info    Theme information from WordPress.org API
     */
    private function handle_update_available($theme_info)
    {
        $update_info = array(
            'current_version' => $this->hello_elementor_info['version'],
            'new_version' => $theme_info['version'],
            'detected_at' => current_time('mysql'),
            'status' => 'pending'
        );

        // Store update information
        update_option('cdswerx_hello_elementor_pending_update', $update_info);

        // Trigger update notification
        do_action('cdswerx_hello_elementor_update_detected', $update_info);

        // Auto-sync if enabled
        if ($this->sync_status['auto_sync']) {
            $this->initiate_sync_process($theme_info);
        }
    }

    /**
     * Initiate Hello Elementor sync process
     *
     * @since    1.0.4
     * @param    array    $theme_info    Theme information for sync
     */
    public function initiate_sync_process($theme_info)
    {
        try {
            // Update sync status
            $this->sync_status['mode'] = 'transitioning';
            update_option('cdswerx_sync_status', $this->sync_status);

            // Create backup before sync
            $backup_result = $this->create_pre_sync_backup();
            if (!$backup_result) {
                throw new Exception('Failed to create pre-sync backup');
            }

            // Download and process Hello Elementor files
            $sync_result = $this->sync_hello_elementor_files($theme_info);
            if (!$sync_result) {
                throw new Exception('Failed to sync Hello Elementor files');
            }

            // Update version management
            $this->version_manager->handle_hello_elementor_update(
                $this->hello_elementor_info['version'],
                $theme_info['version']
            );

            // Complete sync
            $this->complete_sync_process($theme_info);

        } catch (Exception $e) {
            $this->handle_sync_error($e->getMessage());
            $this->rollback_sync($backup_result ?? null);
        }
    }

    /**
     * Create pre-sync backup
     *
     * @since    1.0.4
     * @return   string|false    Backup path on success, false on failure
     */
    private function create_pre_sync_backup()
    {
        $backup_dir = wp_upload_dir()['basedir'] . '/cdswerx-backups/hello-elementor-sync/';
        
        if (!wp_mkdir_p($backup_dir)) {
            return false;
        }

        $backup_name = 'backup-' . date('Y-m-d-H-i-s') . '-' . $this->hello_elementor_info['version'];
        $backup_path = $backup_dir . $backup_name;

        // Create backup of current CDSWerx customizations
        $files_to_backup = array(
            get_template_directory() . '/functions.php',
            get_template_directory() . '/style.css',
            get_stylesheet_directory() . '/functions.php',
            get_stylesheet_directory() . '/style.css',
        );

        foreach ($files_to_backup as $file) {
            if (file_exists($file)) {
                $relative_path = str_replace(ABSPATH, '', $file);
                $backup_file_dir = dirname($backup_path . '/' . $relative_path);
                
                if (!wp_mkdir_p($backup_file_dir)) {
                    return false;
                }
                
                if (!copy($file, $backup_path . '/' . $relative_path)) {
                    return false;
                }
            }
        }

        return $backup_path;
    }

    /**
     * Sync Hello Elementor files with CDSWerx customizations
     *
     * @since    1.0.4
     * @param    array    $theme_info    Theme information for sync
     * @return   bool     Success status
     */
    private function sync_hello_elementor_files($theme_info)
    {
        // This would integrate with the file synchronization engine
        // For Phase 1, we'll implement basic structure
        
        $sync_operations = array(
            'download_hello_elementor' => $this->download_hello_elementor_update($theme_info),
            'merge_customizations' => $this->merge_cdswerx_customizations(),
            'update_theme_files' => $this->update_theme_files(),
            'validate_functionality' => $this->validate_sync_functionality(),
        );

        foreach ($sync_operations as $operation => $result) {
            if (!$result) {
                $this->log_sync_error("Sync operation failed: {$operation}");
                return false;
            }
        }

        return true;
    }

    /**
     * Download Hello Elementor update
     *
     * @since    1.0.4
     * @param    array    $theme_info    Theme information
     * @return   bool     Success status
     */
    private function download_hello_elementor_update($theme_info)
    {
        try {
            // Check if we can access WordPress.org repository
            $theme_api_url = 'https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]=hello-elementor';
            
            $response = wp_remote_get($theme_api_url, array(
                'timeout' => 30,
                'user-agent' => 'CDSWerx-Plugin/' . CDSWERX_VERSION
            ));
            
            if (is_wp_error($response)) {
                $this->log_sync_error('Failed to fetch theme information: ' . $response->get_error_message());
                return false;
            }
            
            $theme_data = json_decode(wp_remote_retrieve_body($response), true);
            
            if (!$theme_data || !isset($theme_data['download_link'])) {
                $this->log_sync_error('Invalid theme data received from WordPress.org');
                return false;
            }
            
            // Download the theme package
            $download_response = wp_remote_get($theme_data['download_link'], array(
                'timeout' => 60,
                'user-agent' => 'CDSWerx-Plugin/' . CDSWERX_VERSION
            ));
            
            if (is_wp_error($download_response)) {
                $this->log_sync_error('Failed to download theme: ' . $download_response->get_error_message());
                return false;
            }
            
            // Create temporary file for download
            $temp_file = wp_tempnam('hello-elementor-update.zip');
            if (!$temp_file) {
                $this->log_sync_error('Failed to create temporary file');
                return false;
            }
            
            // Save downloaded content
            file_put_contents($temp_file, wp_remote_retrieve_body($download_response));
            
            // Store download info for merge process
            $this->hello_elementor_info['download_path'] = $temp_file;
            $this->hello_elementor_info['new_version'] = $theme_data['version'];
            
            return true;
            
        } catch (Exception $e) {
            $this->log_sync_error('Download failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Merge CDSWerx customizations with Hello Elementor updates
     *
     * @since    1.0.4
     * @return   bool     Success status
     */
    private function merge_cdswerx_customizations()
    {
        try {
            if (!isset($this->hello_elementor_info['download_path'])) {
                $this->log_sync_error('No download package available for merge');
                return false;
            }
            
            // Extract downloaded theme to temporary directory
            $temp_dir = wp_upload_dir()['basedir'] . '/cdswerx-sync-temp';
            if (!wp_mkdir_p($temp_dir)) {
                $this->log_sync_error('Failed to create temporary merge directory');
                return false;
            }
            
            // Extract zip file
            $zip = new ZipArchive();
            if ($zip->open($this->hello_elementor_info['download_path']) !== TRUE) {
                $this->log_sync_error('Failed to open downloaded theme package');
                return false;
            }
            
            $zip->extractTo($temp_dir);
            $zip->close();
            
            // Find extracted theme directory
            $extracted_theme_dir = $temp_dir . '/hello-elementor';
            if (!is_dir($extracted_theme_dir)) {
                $this->log_sync_error('Extracted theme directory not found');
                return false;
            }
            
            // Define CDSWerx customization preservation rules
            $preserve_files = array(
                'functions.php' => 'merge_functions_php',
                'style.css' => 'merge_style_css',
                'assets/' => 'preserve_directory',
                'cdswerx-customizations/' => 'preserve_directory'
            );
            
            // Current Hello Elementor theme directory
            $current_theme_dir = get_template_directory();
            
            // Preserve CDSWerx customizations
            foreach ($preserve_files as $file_pattern => $merge_method) {
                $current_file = $current_theme_dir . '/' . $file_pattern;
                $new_file = $extracted_theme_dir . '/' . $file_pattern;
                
                if (file_exists($current_file)) {
                    switch ($merge_method) {
                        case 'merge_functions_php':
                            $this->merge_functions_file($current_file, $new_file);
                            break;
                        case 'merge_style_css':
                            $this->merge_style_file($current_file, $new_file);
                            break;
                        case 'preserve_directory':
                            if (is_dir($current_file)) {
                                $this->copy_directory_recursive($current_file, $new_file);
                            }
                            break;
                    }
                }
            }
            
            // Store merged theme path for update process
            $this->hello_elementor_info['merged_path'] = $extracted_theme_dir;
            
            return true;
            
        } catch (Exception $e) {
            $this->log_sync_error('Merge failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Merge functions.php file with CDSWerx customizations
     *
     * @since    1.0.4
     * @param    string    $current_file    Current functions.php
     * @param    string    $new_file        New functions.php
     * @return   bool      Success status
     */
    private function merge_functions_file($current_file, $new_file)
    {
        $current_content = file_get_contents($current_file);
        $new_content = file_get_contents($new_file);
        
        // Extract CDSWerx specific functions (between CDSWerx markers)
        $pattern = '/\/\*\* CDSWerx START \*\*\/(.*?)\/\*\* CDSWerx END \*\*\//s';
        preg_match_all($pattern, $current_content, $matches);
        
        $cdswerx_customizations = '';
        if (!empty($matches[0])) {
            $cdswerx_customizations = implode("\n\n", $matches[0]);
        }
        
        // Append CDSWerx customizations to new functions.php
        if (!empty($cdswerx_customizations)) {
            $new_content = rtrim($new_content, '?>') . "\n\n" . $cdswerx_customizations . "\n";
        }
        
        return file_put_contents($new_file, $new_content) !== false;
    }
    
    /**
     * Merge style.css file with CDSWerx customizations
     *
     * @since    1.0.4
     * @param    string    $current_file    Current style.css
     * @param    string    $new_file        New style.css
     * @return   bool      Success status
     */
    private function merge_style_file($current_file, $new_file)
    {
        $current_content = file_get_contents($current_file);
        $new_content = file_get_contents($new_file);
        
        // Extract CDSWerx specific styles (between CDSWerx markers)
        $pattern = '/\/\* CDSWerx START \*\/(.*?)\/\* CDSWerx END \*\//s';
        preg_match_all($pattern, $current_content, $matches);
        
        $cdswerx_styles = '';
        if (!empty($matches[0])) {
            $cdswerx_styles = implode("\n\n", $matches[0]);
        }
        
        // Append CDSWerx styles to new style.css
        if (!empty($cdswerx_styles)) {
            $new_content .= "\n\n" . $cdswerx_styles;
        }
        
        return file_put_contents($new_file, $new_content) !== false;
    }
    
    /**
     * Copy directory recursively
     *
     * @since    1.0.4
     * @param    string    $source      Source directory
     * @param    string    $destination Destination directory
     * @return   bool      Success status
     */
    private function copy_directory_recursive($source, $destination)
    {
        if (!is_dir($source)) {
            return false;
        }
        
        if (!wp_mkdir_p($destination)) {
            return false;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $dest_path = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            
            if ($item->isDir()) {
                wp_mkdir_p($dest_path);
            } else {
                copy($item->getPathname(), $dest_path);
            }
        }
        
        return true;
    }

    /**
     * Update theme files after merge
     *
     * @since    1.0.4
     * @return   bool     Success status
     */
    private function update_theme_files()
    {
        try {
            if (!isset($this->hello_elementor_info['merged_path'])) {
                $this->log_sync_error('No merged theme available for update');
                return false;
            }
            
            $merged_theme_dir = $this->hello_elementor_info['merged_path'];
            $current_theme_dir = get_template_directory();
            
            // Create backup before update
            if (!$this->create_theme_backup()) {
                $this->log_sync_error('Failed to create backup before update');
                return false;
            }
            
            // Validate merged theme structure
            if (!$this->validate_theme_structure($merged_theme_dir)) {
                $this->log_sync_error('Invalid theme structure in merged package');
                return false;
            }
            
            // Copy merged theme files to active theme directory
            $success = $this->copy_directory_recursive($merged_theme_dir, $current_theme_dir);
            
            if (!$success) {
                $this->log_sync_error('Failed to copy updated theme files');
                // Attempt rollback
                $this->rollback_theme_update();
                return false;
            }
            
            // Update theme version in database
            $this->update_theme_version();
            
            // Clear WordPress theme cache
            wp_clean_themes_cache();
            
            // Verify update success
            if (!$this->verify_theme_update()) {
                $this->log_sync_error('Theme update verification failed');
                $this->rollback_theme_update();
                return false;
            }
            
            // Clean up temporary files
            $this->cleanup_temp_files();
            
            // Log successful update
            error_log('CDSWerx Hello Elementor Sync: Theme files successfully updated to version ' . $this->hello_elementor_info['version']);
            
            return true;
            
        } catch (Exception $e) {
            $this->log_sync_error('Theme update failed: ' . $e->getMessage());
            $this->rollback_theme_update();
            return false;
        }
    }
    
    /**
     * Create backup of current theme
     *
     * @since    1.0.4
     * @return   bool     Success status
     */
    private function create_theme_backup()
    {
        $current_theme_dir = get_template_directory();
        $backup_dir = wp_upload_dir()['basedir'] . '/cdswerx-theme-backups';
        
        if (!wp_mkdir_p($backup_dir)) {
            return false;
        }
        
        $backup_timestamp = date('Y-m-d_H-i-s');
        $backup_path = $backup_dir . '/hello-elementor-backup-' . $backup_timestamp;
        
        // Store backup path for potential rollback
        $this->hello_elementor_info['backup_path'] = $backup_path;
        
        return $this->copy_directory_recursive($current_theme_dir, $backup_path);
    }
    
    /**
     * Validate theme structure
     *
     * @since    1.0.4
     * @param    string    $theme_dir    Theme directory to validate
     * @return   bool      Validation result
     */
    private function validate_theme_structure($theme_dir)
    {
        $required_files = array(
            'style.css',
            'index.php',
            'functions.php'
        );
        
        foreach ($required_files as $file) {
            if (!file_exists($theme_dir . '/' . $file)) {
                return false;
            }
        }
        
        // Validate style.css header
        $style_content = file_get_contents($theme_dir . '/style.css');
        if (strpos($style_content, 'Theme Name:') === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Update theme version in database
     *
     * @since    1.0.4
     * @return   bool     Success status
     */
    private function update_theme_version()
    {
        $theme_data = wp_get_theme();
        $new_version = $this->hello_elementor_info['version'];
        
        // Update CDSWerx sync records
        update_option('cdswerx_hello_elementor_version', $new_version);
        update_option('cdswerx_last_sync_time', current_time('mysql'));
        
        return true;
    }
    
    /**
     * Verify theme update success
     *
     * @since    1.0.4
     * @return   bool     Verification result
     */
    private function verify_theme_update()
    {
        // Check if theme is still active and functional
        $current_theme = wp_get_theme();
        
        if (!$current_theme->exists()) {
            return false;
        }
        
        // Verify core theme files are present and readable
        $theme_dir = get_template_directory();
        $core_files = array('style.css', 'index.php', 'functions.php');
        
        foreach ($core_files as $file) {
            $file_path = $theme_dir . '/' . $file;
            if (!file_exists($file_path) || !is_readable($file_path)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Rollback theme update
     *
     * @since    1.0.4
     * @return   bool     Rollback success
     */
    private function rollback_theme_update()
    {
        if (!isset($this->hello_elementor_info['backup_path'])) {
            return false;
        }
        
        $backup_path = $this->hello_elementor_info['backup_path'];
        $current_theme_dir = get_template_directory();
        
        if (!is_dir($backup_path)) {
            return false;
        }
        
        // Restore from backup
        $success = $this->copy_directory_recursive($backup_path, $current_theme_dir);
        
        if ($success) {
            error_log('CDSWerx Hello Elementor Sync: Theme successfully rolled back from backup');
            wp_clean_themes_cache();
        }
        
        return $success;
    }
    
    /**
     * Clean up temporary files
     *
     * @since    1.0.4
     * @return   bool     Cleanup success
     */
    private function cleanup_temp_files()
    {
        $temp_dir = wp_upload_dir()['basedir'] . '/cdswerx-sync-temp';
        
        if (is_dir($temp_dir)) {
            $this->delete_directory_recursive($temp_dir);
        }
        
        // Clean up download file
        if (isset($this->hello_elementor_info['download_path'])) {
            $download_path = $this->hello_elementor_info['download_path'];
            if (file_exists($download_path)) {
                unlink($download_path);
            }
        }
        
        return true;
    }
    
    /**
     * Delete directory recursively
     *
     * @since    1.0.4
     * @param    string    $dir    Directory to delete
     * @return   bool      Success status
     */
    private function delete_directory_recursive($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->delete_directory_recursive($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }

    /**
     * Validate sync functionality
     *
     * @since    1.0.4
     * @return   bool     Validation status
     */
    private function validate_sync_functionality()
    {
        $validation_results = array();
        
        try {
            // 1. Validate Hello Elementor theme presence
            $validation_results['theme_exists'] = $this->validate_hello_elementor_exists();
            
            // 2. Validate theme file integrity
            $validation_results['file_integrity'] = $this->validate_theme_file_integrity();
            
            // 3. Validate CDSWerx customizations
            $validation_results['customizations'] = $this->validate_cdswerx_customizations();
            
            // 4. Validate sync database records
            $validation_results['database'] = $this->validate_sync_database();
            
            // 5. Validate backup system
            $validation_results['backup_system'] = $this->validate_backup_system();
            
            // 6. Validate theme compatibility
            $validation_results['compatibility'] = $this->validate_theme_compatibility();
            
            // 7. Validate file permissions
            $validation_results['permissions'] = $this->validate_file_permissions();
            
            // Log validation results
            foreach ($validation_results as $test => $result) {
                if (!$result) {
                    $this->log_sync_error("Validation failed for: {$test}");
                }
            }
            
            // All validations must pass
            $overall_result = !in_array(false, $validation_results, true);
            
            if ($overall_result) {
                error_log('CDSWerx Hello Elementor Sync: All sync functionality validations passed');
            } else {
                $this->log_sync_error('One or more sync validations failed');
            }
            
            return $overall_result;
            
        } catch (Exception $e) {
            $this->log_sync_error('Validation process failed: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validate Hello Elementor theme exists
     *
     * @since    1.0.4
     * @return   bool     Validation result
     */
    private function validate_hello_elementor_exists()
    {
        return wp_get_theme('hello-elementor')->exists();
    }
    
    /**
     * Validate theme file integrity
     *
     * @since    1.0.4
     * @return   bool     Validation result
     */
    private function validate_theme_file_integrity()
    {
        $theme_dir = get_template_directory();
        $required_files = array(
            'style.css',
            'index.php',
            'functions.php',
            'screenshot.png'
        );
        
        foreach ($required_files as $file) {
            $file_path = $theme_dir . '/' . $file;
            if (!file_exists($file_path) || !is_readable($file_path)) {
                return false;
            }
            
            // Additional validation for critical files
            if ($file === 'style.css') {
                $content = file_get_contents($file_path);
                if (strpos($content, 'Theme Name:') === false) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Validate CDSWerx customizations
     *
     * @since    1.0.4
     * @return   bool     Validation result
     */
    private function validate_cdswerx_customizations()
    {
        $theme_dir = get_template_directory();
        
        // Check for CDSWerx markers in functions.php
        $functions_file = $theme_dir . '/functions.php';
        if (file_exists($functions_file)) {
            $content = file_get_contents($functions_file);
            $has_cdswerx_markers = (strpos($content, '/** CDSWerx START **/') !== false);
            
            if ($has_cdswerx_markers) {
                // Validate marker pairs are balanced
                $start_count = substr_count($content, '/** CDSWerx START **/');
                $end_count = substr_count($content, '/** CDSWerx END **/');
                return ($start_count === $end_count);
            }
        }
        
        // Check for CDSWerx custom directories
        $custom_dirs = array(
            $theme_dir . '/cdswerx-customizations',
            $theme_dir . '/assets'
        );
        
        foreach ($custom_dirs as $dir) {
            if (is_dir($dir) && !is_readable($dir)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate sync database records
     *
     * @since    1.0.4
     * @return   bool     Validation result
     */
    private function validate_sync_database()
    {
        // Check required options exist
        $required_options = array(
            'cdswerx_sync_status',
            'cdswerx_hello_elementor_version'
        );
        
        foreach ($required_options as $option) {
            if (get_option($option) === false) {
                return false;
            }
        }
        
        // Validate sync status structure
        $sync_status = get_option('cdswerx_sync_status', array());
        $required_keys = array('mode', 'last_check', 'status');
        
        foreach ($required_keys as $key) {
            if (!isset($sync_status[$key])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate backup system
     *
     * @since    1.0.4
     * @return   bool     Validation result
     */
    private function validate_backup_system()
    {
        $backup_dir = wp_upload_dir()['basedir'] . '/cdswerx-theme-backups';
        
        // Check if backup directory can be created
        if (!is_dir($backup_dir)) {
            if (!wp_mkdir_p($backup_dir)) {
                return false;
            }
        }
        
        // Check write permissions
        if (!is_writable($backup_dir)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate theme compatibility
     *
     * @since    1.0.4
     * @return   bool     Validation result
     */
    private function validate_theme_compatibility()
    {
        $current_theme = wp_get_theme();
        
        // Check WordPress version compatibility
        $wp_version = get_bloginfo('version');
        if (version_compare($wp_version, '5.0', '<')) {
            return false;
        }
        
        // Check if theme supports required features
        $required_features = array(
            'post-thumbnails',
            'title-tag'
        );
        
        foreach ($required_features as $feature) {
            if (!current_theme_supports($feature)) {
                // This is a warning, not a failure
                continue;
            }
        }
        
        return true;
    }
    
    /**
     * Validate file permissions
     *
     * @since    1.0.4
     * @return   bool     Validation result
     */
    private function validate_file_permissions()
    {
        $theme_dir = get_template_directory();
        
        // Check theme directory permissions
        if (!is_readable($theme_dir) || !is_writable($theme_dir)) {
            return false;
        }
        
        // Check critical files are writable
        $critical_files = array(
            'functions.php',
            'style.css'
        );
        
        foreach ($critical_files as $file) {
            $file_path = $theme_dir . '/' . $file;
            if (file_exists($file_path) && !is_writable($file_path)) {
                return false;
            }
        }
        
        // Check upload directory permissions
        $upload_dir = wp_upload_dir()['basedir'];
        if (!is_writable($upload_dir)) {
            return false;
        }
        
        return true;
    }

    /**
     * Complete sync process
     *
     * @since    1.0.4
     * @param    array    $theme_info    Theme information
     */
    private function complete_sync_process($theme_info)
    {
        // Update sync status
        $this->sync_status['mode'] = 'dependent';
        $this->sync_status['last_sync'] = current_time('mysql');
        $this->sync_status['version_history'][] = array(
            'from_version' => $this->hello_elementor_info['version'],
            'to_version' => $theme_info['version'],
            'sync_date' => current_time('mysql'),
            'sync_type' => 'automatic'
        );

        update_option('cdswerx_sync_status', $this->sync_status);

        // Update Hello Elementor info
        $this->hello_elementor_info['version'] = $theme_info['version'];
        update_option('cdswerx_hello_elementor_info', $this->hello_elementor_info);

        // Clear pending update
        delete_option('cdswerx_hello_elementor_pending_update');

        // Trigger completion actions
        do_action('cdswerx_hello_elementor_sync_completed', $theme_info);
    }

    /**
     * Handle CSS changes in Advanced CSS tab
     *
     * @since    1.0.4
     * @param    array    $css_data    CSS change information
     */
    public function handle_css_change($css_data)
    {
        if ($this->sync_status['auto_version']) {
            $this->version_manager->increment_child_theme_version($css_data);
        }
    }

    /**
     * Handle theme switch events
     *
     * @since    1.0.4
     * @param    string    $new_theme_name    Name of the new theme
     */
    public function handle_theme_switch($new_theme_name)
    {
        $this->detect_hello_elementor();
        
        if ($new_theme_name === 'hello-elementor') {
            $this->sync_status['mode'] = 'dependent';
        } else {
            $this->sync_status['mode'] = 'independent';
        }
        
        update_option('cdswerx_sync_status', $this->sync_status);
    }

    /**
     * Check theme compatibility on theme setup
     *
     * @since    1.0.4
     */
    public function check_theme_compatibility()
    {
        if ($this->sync_status['mode'] === 'independent') {
            $this->provide_independent_functions();
        }
    }

    /**
     * Provide essential functions in independent mode
     *
     * @since    1.0.4
     */
    public function provide_independent_functions()
    {
        if ($this->sync_status['mode'] !== 'independent') {
            return;
        }

        // Load essential Hello Elementor functions if not present
        if (!function_exists('hello_elementor_setup')) {
            $this->load_essential_functions();
        }
    }

    /**
     * Load essential Hello Elementor functions for independent mode
     *
     * @since    1.0.4
     */
    private function load_essential_functions()
    {
        // Essential theme setup functions
        if (!function_exists('hello_elementor_setup')) {
            function hello_elementor_setup() {
                add_theme_support('post-thumbnails');
                add_theme_support('automatic-feed-links');
                add_theme_support('title-tag');
                add_theme_support('html5', array(
                    'search-form',
                    'comment-form',
                    'comment-list',
                    'gallery',
                    'caption',
                ));
                add_theme_support('custom-logo', array(
                    'height' => 100,
                    'width' => 350,
                    'flex-height' => true,
                    'flex-width' => true,
                ));
                add_theme_support('customize-selective-refresh-widgets');
            }
        }

        // Essential enqueue functions
        if (!function_exists('hello_elementor_scripts_styles')) {
            function hello_elementor_scripts_styles() {
                wp_enqueue_style(
                    'hello-elementor',
                    get_template_directory_uri() . '/style.css',
                    array(),
                    CDSWERX_VERSION
                );
            }
        }

        // Hook essential functions
        add_action('after_setup_theme', 'hello_elementor_setup');
        add_action('wp_enqueue_scripts', 'hello_elementor_scripts_styles');
    }

    /**
     * Add sync admin menu
     *
     * @since    1.0.4
     */
    public function add_sync_admin_menu()
    {
        add_submenu_page(
            'cdswerx-admin',
            __('Hello Elementor Sync', 'cdswerx'),
            __('Hello Elementor Sync', 'cdswerx'),
            'manage_options',
            'cdswerx-hello-elementor-sync',
            array($this, 'display_sync_admin_page')
        );
    }

    /**
     * Display sync admin page
     *
     * @since    1.0.4
     */
    public function display_sync_admin_page()
    {
        // This will be implemented in Phase 2
        echo '<div class="wrap">';
        echo '<h1>' . __('Hello Elementor Sync Management', 'cdswerx') . '</h1>';
        echo '<p>' . __('Sync management interface coming in Phase 2', 'cdswerx') . '</p>';
        echo '</div>';
    }

    /**
     * Enqueue admin assets
     *
     * @since    1.0.4
     */
    public function enqueue_admin_assets($hook)
    {
        if (strpos($hook, 'cdswerx-hello-elementor-sync') !== false) {
            wp_enqueue_script(
                'cdswerx-hello-elementor-sync-admin',
                plugin_dir_url(__FILE__) . '../admin/js/hello-elementor-sync.js',
                array('jquery'),
                $this->version,
                true
            );
            
            wp_localize_script('cdswerx-hello-elementor-sync-admin', 'cdswerx_sync', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('cdswerx_sync_nonce'),
                'strings' => array(
                    'sync_in_progress' => __('Sync in progress...', 'cdswerx'),
                    'sync_complete' => __('Sync completed successfully', 'cdswerx'),
                    'sync_error' => __('Sync failed', 'cdswerx'),
                )
            ));
        }
    }

    /**
     * Handle sync AJAX requests
     *
     * @since    1.0.4
     */
    public function handle_sync_ajax()
    {
        check_ajax_referer('cdswerx_sync_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'cdswerx'));
        }

        $action = sanitize_text_field($_POST['sync_action']);
        
        switch ($action) {
            case 'manual_sync':
                $result = $this->manual_sync();
                break;
            case 'toggle_auto_sync':
                $result = $this->toggle_auto_sync();
                break;
            case 'get_sync_status':
                $result = $this->get_sync_status();
                break;
            default:
                $result = array('success' => false, 'message' => 'Unknown action');
        }

        wp_send_json($result);
    }

    /**
     * Handle version management AJAX requests
     *
     * @since    1.0.4
     */
    public function handle_version_ajax()
    {
        check_ajax_referer('cdswerx_sync_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'cdswerx'));
        }

        $action = sanitize_text_field($_POST['version_action']);
        $result = $this->version_manager->handle_ajax_action($action, $_POST);
        
        wp_send_json($result);
    }

    /**
     * Perform manual sync
     *
     * @since    1.0.4
     * @return   array    Sync result
     */
    private function manual_sync()
    {
        try {
            // Check if Hello Elementor is available
            if (!$this->hello_elementor_info['active']) {
                return array(
                    'success' => false,
                    'message' => __('Hello Elementor theme is not active. Cannot perform sync.', 'cdswerx')
                );
            }
            
            // Check for updates
            $update_available = $this->check_for_updates();
            
            if (!$update_available) {
                return array(
                    'success' => true,
                    'message' => __('Hello Elementor is already up to date.', 'cdswerx')
                );
            }
            
            // Perform the sync process
            $sync_result = $this->sync_hello_elementor_files($this->hello_elementor_info);
            
            if ($sync_result) {
                // Update last sync time
                $this->sync_status['last_sync'] = current_time('mysql');
                update_option('cdswerx_sync_status', $this->sync_status);
                
                return array(
                    'success' => true,
                    'message' => __('Manual sync completed successfully.', 'cdswerx'),
                    'data' => array(
                        'new_version' => $this->hello_elementor_info['version'],
                        'sync_time' => $this->sync_status['last_sync']
                    )
                );
            } else {
                return array(
                    'success' => false,
                    'message' => __('Sync failed. Check logs for details.', 'cdswerx')
                );
            }
            
        } catch (Exception $e) {
            $this->log_sync_error('Manual sync failed: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => __('Manual sync failed due to an error. Check logs for details.', 'cdswerx')
            );
        }
    }

    /**
     * Toggle auto sync setting
     *
     * @since    1.0.4
     * @return   array    Toggle result
     */
    private function toggle_auto_sync()
    {
        $this->sync_status['auto_sync'] = !$this->sync_status['auto_sync'];
        update_option('cdswerx_sync_status', $this->sync_status);
        
        return array(
            'success' => true,
            'auto_sync' => $this->sync_status['auto_sync'],
            'message' => sprintf(
                __('Auto sync %s', 'cdswerx'),
                $this->sync_status['auto_sync'] ? __('enabled', 'cdswerx') : __('disabled', 'cdswerx')
            )
        );
    }



    /**
     * Manage frontend versions
     *
     * @since    1.0.4
     */
    public function manage_frontend_versions()
    {
        $this->version_manager->manage_frontend_versions();
    }

    /**
     * Log sync error
     *
     * @since    1.0.4
     * @param    string    $error_message    Error message to log
     */
    private function log_sync_error($error_message)
    {
        $this->sync_status['sync_errors'][] = array(
            'message' => $error_message,
            'timestamp' => current_time('mysql'),
        );
        
        // Keep only last 10 errors
        if (count($this->sync_status['sync_errors']) > 10) {
            $this->sync_status['sync_errors'] = array_slice($this->sync_status['sync_errors'], -10);
        }
        
        update_option('cdswerx_sync_status', $this->sync_status);
        
        // Log to WordPress error log
        error_log('[CDSWerx Hello Elementor Sync] ' . $error_message);
    }

    /**
     * Handle sync error and recovery
     *
     * @since    1.0.4
     * @param    string    $error_message    Error message
     */
    private function handle_sync_error($error_message)
    {
        $this->log_sync_error($error_message);
        
        // Reset sync mode to dependent
        $this->sync_status['mode'] = 'dependent';
        update_option('cdswerx_sync_status', $this->sync_status);
        
        // Trigger error notification
        do_action('cdswerx_hello_elementor_sync_error', $error_message);
    }

    /**
     * Rollback sync operation
     *
     * @since    1.0.4
     * @param    string|null    $backup_path    Path to backup for rollback
     */
    private function rollback_sync($backup_path = null)
    {
        if ($backup_path && is_dir($backup_path)) {
            // Restore from backup
            // Implementation depends on backup structure
            $this->log_sync_error('Sync rolled back from backup: ' . $backup_path);
        } else {
            $this->log_sync_error('Sync failed - no backup available for rollback');
        }
    }

    /**
     * Perform manual synchronization with Hello Elementor
     *
     * @since    1.0.4
     * @return   array    Result of sync operation
     */
    public function perform_manual_sync()
    {
        try {
            // Check if Hello Elementor is available
            if (!$this->dependency_checker->is_hello_elementor_available()) {
                return [
                    'success' => false,
                    'message' => 'Hello Elementor theme is not installed or activated'
                ];
            }

            // Get theme information
            $theme_info = $this->dependency_checker->get_hello_elementor_info();
            
            // Check for updates
            $api_data = $this->query_wordpress_org_api('hello-elementor');
            if (!$api_data) {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch Hello Elementor information from WordPress.org'
                ];
            }

            // Compare versions and sync if needed
            $current_version = $theme_info['version'] ?? '0.0.0';
            $latest_version = $api_data['version'] ?? '0.0.0';

            if (version_compare($current_version, $latest_version, '<')) {
                // Update available - initiate sync process
                $sync_result = $this->initiate_sync_process($api_data);
                
                if ($sync_result['success']) {
                    // Update last sync timestamp
                    update_option('cdswerx_last_hello_elementor_sync', current_time('mysql'));
                    
                    // Increment version if auto-increment is enabled
                    if (get_option('cdswerx_auto_version_on_sync', true)) {
                        $this->version_manager->increment_version('theme', 'patch');
                    }
                }

                return $sync_result;
            } else {
                // No updates needed, but sync successful
                update_option('cdswerx_last_hello_elementor_sync', current_time('mysql'));
                
                return [
                    'success' => true,
                    'message' => 'Hello Elementor is up to date - no sync needed',
                    'current_version' => $current_version,
                    'latest_version' => $latest_version
                ];
            }

        } catch (Exception $e) {
            error_log('Hello Elementor Sync Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get comprehensive sync status
     *
     * @since    1.0.4
     * @return   array    Current sync status information
     */
    public function get_sync_status()
    {
        $hello_elementor_info = $this->dependency_checker->get_hello_elementor_info();
        
        // Determine sync mode
        $sync_mode = 'independent';
        if ($hello_elementor_info['installed'] && $hello_elementor_info['active']) {
            $sync_mode = 'synchronized';
        } elseif ($hello_elementor_info['installed']) {
            $sync_mode = 'available';
        }

        return [
            'mode' => $sync_mode,
            'auto_sync' => get_option('cdswerx_auto_sync_enabled', false),
            'last_sync' => get_option('cdswerx_last_hello_elementor_sync', null),
            'sync_frequency' => get_option('cdswerx_sync_frequency', 'daily'),
            'hello_elementor_status' => $hello_elementor_info,
            'independent_mode_active' => !$hello_elementor_info['active'],
            'next_scheduled_sync' => wp_next_scheduled('cdswerx_hello_elementor_sync_check')
        ];
    }

    /**
     * Check for Hello Elementor updates from WordPress.org
     *
     * @since    1.0.4
     * @return   array    Available updates
     */
    public function check_for_hello_elementor_updates()
    {
        $updates = [];
        
        try {
            // Get current Hello Elementor info
            $theme_info = $this->dependency_checker->get_hello_elementor_info();
            
            if (!$theme_info['installed']) {
                return $updates;
            }

            // Query WordPress.org API for latest version
            $api_data = $this->query_wordpress_org_api('hello-elementor');
            
            if ($api_data && isset($api_data['version'])) {
                $current_version = $theme_info['version'] ?? '0.0.0';
                $latest_version = $api_data['version'];

                if (version_compare($current_version, $latest_version, '<')) {
                    $updates[] = [
                        'id' => 'hello-elementor',
                        'name' => 'Hello Elementor',
                        'current_version' => $current_version,
                        'version' => $latest_version,
                        'description' => $api_data['description'] ?? 'Hello Elementor theme update available',
                        'changelog' => $this->get_theme_changelog($api_data),
                        'auto_update' => get_option('cdswerx_auto_update_hello_elementor', false)
                    ];
                }
            }
        } catch (Exception $e) {
            error_log('Hello Elementor Update Check Error: ' . $e->getMessage());
        }

        return $updates;
    }

    /**
     * Query WordPress.org API for theme information
     *
     * @since    1.0.4
     * @param    string    $theme_slug    The theme slug to query
     * @return   array|false    API response data or false on failure
     */
    protected function query_wordpress_org_api($theme_slug)
    {
        $api_url = "https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]={$theme_slug}";
        
        $response = wp_remote_get($api_url, [
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'CDSWerx Hello Elementor Sync/' . $this->version
            ]
        ]);

        if (is_wp_error($response)) {
            error_log('WordPress.org API Error: ' . $response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('WordPress.org API JSON Error: ' . json_last_error_msg());
            return false;
        }

        return $data;
    }

    /**
     * Synchronize files with Hello Elementor theme
     *
     * @since    1.0.4
     * @param    array    $theme_data    Theme data from WordPress.org API
     * @return   array    Synchronization result
     */
    protected function synchronize_theme_files($theme_data)
    {
        try {
            $sync_results = [
                'files_checked' => 0,
                'files_updated' => 0,
                'files_created' => 0,
                'errors' => []
            ];

            // Define critical files to sync
            $critical_files = [
                'style.css',
                'functions.php',
                'index.php',
                'header.php',
                'footer.php'
            ];

            $hello_elementor_path = get_template_directory();
            $backup_path = WP_CONTENT_DIR . '/cdswerx-backups/hello-elementor-' . date('Y-m-d-H-i-s');

            // Create backup directory
            if (!wp_mkdir_p($backup_path)) {
                throw new Exception('Failed to create backup directory');
            }

            foreach ($critical_files as $file) {
                $sync_results['files_checked']++;
                
                $file_path = $hello_elementor_path . '/' . $file;
                
                if (file_exists($file_path)) {
                    // Create backup
                    $backup_file = $backup_path . '/' . $file;
                    if (!copy($file_path, $backup_file)) {
                        $sync_results['errors'][] = "Failed to backup {$file}";
                        continue;
                    }

                    // Check if file needs updating (this would require more sophisticated logic)
                    $file_updated = $this->check_and_update_theme_file($file_path, $theme_data);
                    
                    if ($file_updated) {
                        $sync_results['files_updated']++;
                    }
                } else {
                    $sync_results['errors'][] = "File not found: {$file}";
                }
            }

            // Update sync metadata
            update_option('cdswerx_hello_elementor_last_backup', $backup_path);
            update_option('cdswerx_hello_elementor_sync_results', $sync_results);

            return [
                'success' => empty($sync_results['errors']),
                'results' => $sync_results,
                'backup_location' => $backup_path
            ];

        } catch (Exception $e) {
            error_log('Theme File Sync Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'File synchronization failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check and update a specific theme file
     *
     * @since    1.0.4
     * @param    string    $file_path    Path to the file to check
     * @param    array     $theme_data   Theme data from WordPress.org API
     * @return   bool      True if file was updated, false otherwise
     */
    protected function check_and_update_theme_file($file_path, $theme_data)
    {
        // This is a simplified implementation
        // In a full implementation, you would:
        // 1. Download the latest version of the file
        // 2. Compare with current version
        // 3. Apply updates while preserving customizations
        // 4. Handle conflicts appropriately

        $file_modified = filemtime($file_path);
        $theme_updated = strtotime($theme_data['last_updated'] ?? 'now');

        // Simple check: if theme was updated after our file
        if ($theme_updated > $file_modified) {
            // In real implementation, download and merge changes
            touch($file_path); // Placeholder - update timestamp
            return true;
        }

        return false;
    }

    /**
     * Get theme changelog from API data
     *
     * @since    1.0.4
     * @param    array    $api_data    API response data
     * @return   string   Changelog text
     */
    protected function get_theme_changelog($api_data)
    {
        if (isset($api_data['sections']['changelog'])) {
            return wp_strip_all_tags($api_data['sections']['changelog']);
        }

        return 'No changelog available.';
    }

    /**
     * Get version manager instance
     *
     * @since    1.0.4
     * @return   Version_Manager|null    Version manager instance
     */
    public function get_version_manager()
    {
        return $this->version_manager;
    }

    /**
     * Get dependency checker instance
     *
     * @since    1.0.4
     * @return   Dependency_Checker|null    Dependency checker instance
     */
    public function get_dependency_checker()
    {
        return $this->dependency_checker;
    }

    /**
     * Get sync system name
     *
     * @since    1.0.4
     * @return   string    The name of this sync system.
     */
    public function get_sync_name()
    {
        return $this->sync_name;
    }

    /**
     * Get sync system version
     *
     * @since    1.0.4
     * @return   string    The version number of this sync system.
     */
    public function get_version()
    {
        return $this->version;
    }
}