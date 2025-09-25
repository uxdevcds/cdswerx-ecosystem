<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * CDSWerx Extensions Class
 *
 * Implements the extension logic for the CDSWerx plugin by hooking into WordPress core, editor, and third-party plugin behaviors.
 *
 * Purpose & Responsibilities:
 * - Contains all code that modifies WordPress behavior via hooks and filters.
 * - Implements customizations for WordPress core features, TinyMCE editor, Elementor, and utility add-ons.
 * - Responds to option values and settings registered by the admin class to enable/disable features.
 * - Keeps extension logic separate from admin UI and settings management for modularity and maintainability.
 *
 * Separation of Concerns:
 * - This class is focused on actual extension implementation: hooks, filters, and direct modifications to WordPress.
 * - The admin class is responsible for UI, settings registration, and option management.
 * - The extensions class receives option values from the admin class and applies the corresponding extension logic.
 *
 * Sections & Code Overview:
 * 1.Elementor Extensions:
 *   - Modifies post/page row actions to set Elementor as default editor.
 *   - Applies Elementor-specific admin styling.
 *   - Integrates Elementor global colors into TinyMCE.
 * 2.WordPress Core Customizations:
 *   - Disables comments and related admin UI.
 *   - Removes uncategorized links.
 *   - Disables Gutenberg and widget block editors.
 *   - Removes Yoast SEO columns from admin lists.
 *   - Adds custom columns (ID, featured image, dimensions) to posts/pages/media.
 *   - Enables SVG uploads for admins.
 *   - Sets default post/page filters and renames default templates.
 *   - Sets Elementor as default editor globally.
 * 3. Utility Add-ons:
 *   - Adds duplicate post/page functionality.
 *   - Provides date/time shortcodes.
 *   - Customizes admin bar.
 * 4. Editor Customizations:
 *   - Modifies TinyMCE buttons and styles.
 *   - Adds custom style formats for paragraphs, headers, lists, and misc text.
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 * @package    Cdswerx
 * @subpackage Cdswerx/admin
 * @author     CDSWerx <info@cdswerx.com>
 */
class Cdswerx_Extensions
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->init_elementor_extensions();
        $this->init_wp_core_customizations();
        $this->init_utility_addons();
        $this->init_editor_customizations();
        $this->init_typography_sync();
    }

    /***************************************************************************
     * ELEMENTOR EXTENSIONS
     ***************************************************************************/

    /**
     * Initialize Elementor-related extensions
     *
     * @since    1.0.0
     */
    private function init_elementor_extensions()
    {
        // Check if Elementor edit link is enabled
        if (get_option("cdswerx_extensions", [])["elementor_edit_links"] ?? 1) {
            add_filter(
                "page_row_actions",
                [$this, "elementor_modify_list_row_actions"],
                99,
                2,
            );
            add_filter(
                "post_row_actions",
                [$this, "elementor_modify_list_row_actions"],
                99,
                2,
            );
        }

        // Check if Elementor styling is enabled
        if (get_option("cdswerx_extensions", [])["elementor_styling"] ?? 1) {
            add_action("admin_head", [$this, "elementor_styling"]);
        }

        // Check if TinyMCE color integration is enabled
        if (get_option("cdswerx_extensions", [])["elementor_tinymce_colors"] ?? 1) {
            add_filter("tiny_mce_before_init", [
                $this,
                "override_mce_options_elementor",
            ]);
            // Remove any duplicate or incorrect hook registrations
            remove_filter("tiny_mce_before_init", [
                $this,
                "override_MCE_options_elementor_standalone",
            ]);
        }
    }

    /**
     * Rename Elementor's edit link
     *
     * @since    1.0.0
     * @param    array    $actions    The array of row actions
     * @param    WP_Post  $post       The post object
     * @return   array    Modified actions array
     */
    public function elementor_modify_list_row_actions($actions, $post)
    {
        // Only proceed if Elementor is active
        if (!class_exists("Elementor\Plugin")) {
            return $actions;
        }

        // Build the Elementor edit URL
        $elementor_edit_url = admin_url(
            "post.php?post=" . $post->ID . "&action=elementor",
        );

        // Rewrite the normal Edit link
        $actions["edit_with_elementor"] = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url($elementor_edit_url),
            esc_html(__("Elementor Editor", "elementor")),
        );

        return $actions;
    }

    /**
     * Minor styling to make the "- Elementor" non-bold
     *
     * @since    1.0.0
     */
    public function elementor_styling()
    {
        echo '<style>
            td.title.column-title.has-row-actions.column-primary.page-title strong {
                font-weight: 200;
            }
        </style>';
    }

    /**
     * Replace MCE WYSIWYG Colors in Elementor to display defined global colors
     *
     * Override TinyMCE's default colors and replace with Global colors defined in theme settings
     *
     * @since    1.0.0
     * @param    array    $init    TinyMCE initialization array
     * @return   array    Modified initialization array
     */
    public function override_mce_options_elementor($init)
    {
        // Only proceed if Elementor is active
        if (!class_exists("Elementor\Plugin")) {
            return $init;
        }

        // Get Elementor's active kit ID, which will be used to retrieve the page settings
        $elementor_active_kit = get_option("elementor_active_kit", false);

        if (!$elementor_active_kit) {
            return $init;
        }

        // Get the global colors defined in site settings
        $active_kit_settings = get_post_meta(
            $elementor_active_kit,
            "_elementor_page_settings",
            true,
        );

        // Initialize global_colors array
        $global_colors = [];

        // Check if active kit settings are valid and populate global_colors array
        if (!empty($active_kit_settings) && is_array($active_kit_settings)) {
            foreach ($active_kit_settings as $key => $value) {
                $global_colors[$key] = $value;
            }
        }

        // Safely check for system_colors and custom_colors in global_colors
        $system_colors =
            isset($global_colors["system_colors"]) &&
            is_array($global_colors["system_colors"])
            ? $global_colors["system_colors"]
            : [];
        $custom_colors =
            isset($global_colors["custom_colors"]) &&
            is_array($global_colors["custom_colors"])
            ? $global_colors["custom_colors"]
            : [];

        // Safely filter and extract the primary, secondary, accent, and text colors
        $primary_color = array_values(
            array_column(
                array_filter($system_colors, function ($var) {
                    return isset($var["_id"]) && $var["_id"] === "primary";
                }),
                "color",
            ),
        );

        $secondary_color = array_values(
            array_column(
                array_filter($system_colors, function ($var) {
                    return isset($var["_id"]) && $var["_id"] === "secondary";
                }),
                "color",
            ),
        );

        $accent_color = array_values(
            array_column(
                array_filter($system_colors, function ($var) {
                    return isset($var["_id"]) && $var["_id"] === "accent";
                }),
                "color",
            ),
        );

        $text_color = array_values(
            array_column(
                array_filter($system_colors, function ($var) {
                    return isset($var["_id"]) && $var["_id"] === "text";
                }),
                "color",
            ),
        );

        // Safely access custom colors
        $custom_color = !empty($custom_colors)
            ? array_column($custom_colors, "color")
            : [];

        // Build brand colors string, safely checking if each color is available
        $brand_colors = "";
        $brand_colors .= !empty($primary_color)
            ? '"' .
            ltrim(esc_attr($primary_color[0]), "#") .
            '", "Primary brand color",'
            : "";
        $brand_colors .= !empty($secondary_color)
            ? '"' .
            ltrim(esc_attr($secondary_color[0]), "#") .
            '", "Secondary brand color",'
            : "";
        $brand_colors .= !empty($text_color)
            ? '"' .
            ltrim(esc_attr($text_color[0]), "#") .
            '", "Brand text color",'
            : "";
        $brand_colors .= !empty($accent_color)
            ? '"' .
            ltrim(esc_attr($accent_color[0]), "#") .
            '", "Brand accent color",'
            : "";
        $brand_colors .= !empty($custom_color)
            ? '"' .
            ltrim(esc_attr($custom_color[0]), "#") .
            '", "Custom color",'
            : "";

        // Build color grid palette
        if (!empty($brand_colors)) {
            $init["textcolor_map"] = "[" . rtrim($brand_colors, ",") . "]";
        }

        // Change the number of rows in the grid if the number of colors changes (8 swatches per row)
        $init["textcolor_rows"] = 1;

        return $init;
    }

    /***************************************************************************
     * WORDPRESS CORE CUSTOMIZATIONS
     ***************************************************************************/

    /**
     * Initialize WordPress Core customizations
     *
     * @since    1.0.0
     */
    private function init_wp_core_customizations()
    {
        // Disable Comments
        if (get_option("cdswerx_extensions", [])["disable_comments"] ?? 1) {
            add_action('admin_init', [$this, 'disable_comments_admin_init']);
            add_filter('comments_open', '__return_false', 20, 2);
            add_filter('pings_open', '__return_false', 20, 2);
            add_filter('comments_array', '__return_empty_array', 10, 2);
            add_action('admin_menu', [$this, 'disable_comments_admin_menu']);
            add_action('init', [$this, 'disable_comments_init']);
            add_action('wp_before_admin_bar_render', [$this, 'disable_comments_admin_bar']);
        }

        // Remove uncategorized links
        add_filter('get_the_categories', [$this, 'remove_uncategorized_links'], 1);

        // Disable Widget Blocks
        if (get_option("cdswerx_extensions", [])["disable_widget_blocks"] ?? 1) {
            add_filter('gutenberg_use_widgets_block_editor', '__return_false');
            add_filter('use_widgets_block_editor', '__return_false');
        }

        // Disable Gutenberg Block Editor
        if (get_option("cdswerx_extensions", [])["disable_gutenberg"] ?? 1) {
            add_filter('use_block_editor_for_post', '__return_false', 10);
        }

        // Yoast SEO columns removal - always add the filters, the function itself will check if enabled
        add_filter('manage_edit-post_columns', [$this, 'yoast_seo_admin_remove_columns'], 10, 1);
        add_filter('manage_edit-page_columns', [$this, 'yoast_seo_admin_remove_columns'], 10, 1);
        add_filter('manage_edit-ds-specialties_columns', [$this, 'yoast_seo_admin_remove_columns'], 10, 1);
        add_filter('manage_edit-ds-team_columns', [$this, 'yoast_seo_admin_remove_columns'], 10, 1);
        add_filter('manage_edit-ds-locations_columns', [$this, 'yoast_seo_admin_remove_columns'], 10, 1);
        add_filter('manage_edit-ds-services_columns', [$this, 'yoast_seo_admin_remove_columns'], 10, 1);

        // Media Library customizations - dimensions column
        if (get_option("cdswerx_extensions", [])["show_media_dimensions_column"] ?? 1) {
            add_filter('manage_media_columns', [$this, 'add_dimensions_column']);
            add_action('manage_media_custom_column', [$this, 'populate_dimensions_column'], 10, 2);

            // Add specific hooks for attachment screen - this fixes the dimensions display
            add_filter('manage_upload_columns', [$this, 'add_dimensions_column']);
            add_action('manage_attachment_custom_column', [$this, 'populate_dimensions_column'], 10, 2);
        }

        // Post and Page ID columns
        if (get_option("cdswerx_extensions", [])["show_post_id_column"] ?? 1) {
            add_filter('manage_posts_columns', [$this, 'add_custom_id_column']);
            add_filter('manage_pages_columns', [$this, 'add_custom_id_column']);
            add_action('manage_posts_custom_column', [$this, 'show_custom_id_column'], 10, 2);
            add_action('manage_pages_custom_column', [$this, 'show_custom_id_column'], 10, 2);
        }

        // Featured Image columns
        if (get_option("cdswerx_extensions", [])["show_featured_image_column"] ?? 1) {
            add_filter('manage_posts_columns', [$this, 'add_featured_image_column'], 10, 1);
            add_filter('manage_pages_columns', [$this, 'add_featured_image_column'], 10, 1);
            add_action('manage_posts_custom_column', [$this, 'show_featured_image_column'], 10, 2);
            add_action('manage_pages_custom_column', [$this, 'show_featured_image_column'], 10, 2);
        }

        // Add support for custom post types
        $custom_post_types = get_post_types(array('_builtin' => false));
        foreach ($custom_post_types as $post_type) {
            add_filter("manage_{$post_type}_posts_columns", [$this, 'add_featured_image_column'], 10, 1);
            add_action("manage_{$post_type}_posts_custom_column", [$this, 'show_featured_image_column'], 10, 2);
        }

        // Default published posts filter
        if (get_option("cdswerx_extensions", [])["default_published_post_view"] ?? 1) {
            add_action('admin_head', [$this, 'default_published_post']);
        }

        // Default template rename
        if (get_option("cdswerx_extensions", [])["rename_default_template"] ?? 1) {
            add_filter('default_page_template_title', [$this, 'rename_default_template']);
        }

        // Elementor as default editor
        if (get_option("cdswerx_extensions", [])["elementor_default_editor"] ?? 1) {
            add_filter('page_row_actions', [$this, 'elementor_modify_list_row_actions_global'], 99, 2);
            add_filter('post_row_actions', [$this, 'elementor_modify_list_row_actions_global'], 99, 2);
            add_action('admin_head', [$this, 'elementor_styling_global']);
        }
    }

    /**
     * Disable comments admin init actions
     */
    public function disable_comments_admin_init()
    {
        // Redirect any user trying to access comments page
        global $pagenow;

        if ($pagenow === 'edit-comments.php') {
            wp_redirect(admin_url());
            exit;
        }

        // Remove comments metabox from dashboard
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');

        // Disable support for comments and trackbacks in post types
        foreach (get_post_types() as $post_type) {
            if (post_type_supports($post_type, 'comments')) {
                remove_post_type_support($post_type, 'comments');
                remove_post_type_support($post_type, 'trackbacks');
            }
        }
    }

    /**
     * Remove comments page in menu
     */
    public function disable_comments_admin_menu()
    {
        remove_menu_page('edit-comments.php');
    }

    /**
     * Remove comments links from admin bar
     */
    public function disable_comments_init()
    {
        if (is_admin_bar_showing()) {
            remove_action('admin_bar_menu', 'wp_admin_bar_comments_menu', 60);
        }
    }

    /**
     * Remove the comments icon in admin bar
     */
    public function disable_comments_admin_bar()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
    }

    /**
     * Remove uncategorized links
     */
    public function remove_uncategorized_links($categories)
    {
        foreach ($categories as $cat_key => $category) {
            if (1 == $category->term_id) {
                unset($categories[$cat_key]);
            }
        }
        return $categories;
    }

    /**
     * Remove Yoast SEO Columns
     * 
     * Removes Yoast SEO columns from admin post lists if the feature is enabled in settings
     * 
     * @since    1.0.0
     * @param    array    $columns    The array of columns
     * @return   array    Modified columns array
     */
    public function yoast_seo_admin_remove_columns($columns)
    {
        // Check if the feature is enabled (default is enabled/1)
        if (get_option("cdswerx_extensions", [])["remove_yoast_columns"] ?? 1) {
            unset($columns['wpseo-score']);
            unset($columns['wpseo-score-readability']);
            unset($columns['wpseo-title']);
            unset($columns['wpseo-metadesc']);
            unset($columns['wpseo-focuskw']);
            unset($columns['wpseo-links']);
            unset($columns['wpseo-linked']);
        }

        return $columns;
    }

    /**
     * Add custom dimensions column to media library
     */
    public function add_dimensions_column($columns)
    {
        $columns['dimensions'] = 'Dimensions (W x H)';
        return $columns;
    }

    /**
     * Populate dimensions column in media library
     */
    public function populate_dimensions_column($column_name, $id)
    {
        if ($column_name == 'dimensions') {
            $meta = wp_get_attachment_metadata($id);
            if (isset($meta['width']) && isset($meta['height'])) {
                echo $meta['width'] . ' x ' . $meta['height'];
            } else {
                echo 'NA';
            }
        }
    }

    /**
     * Add custom ID column
     */
    public function add_custom_id_column($columns)
    {
        $columns['custom_id'] = __('ID');
        return $columns;
    }

    /**
     * Show custom ID column
     */
    public function show_custom_id_column($column, $post_id)
    {
        if ($column === 'custom_id') {
            echo $post_id;
        }
    }

    /**
     * Add featured image column
     */
    public function add_featured_image_column($columns)
    {
        // You can change this to any other position by changing 'title' to the name of the column you want to put it after.
        $move_after = 'title';
        $move_after_key = array_search($move_after, array_keys($columns), true);

        $first_columns = array_slice($columns, 0, $move_after_key + 1);
        $last_columns = array_slice($columns, $move_after_key + 1);

        return array_merge(
            $first_columns,
            array(
                'featured_image' => __('Featured Image'),
            ),
            $last_columns
        );
    }

    /**
     * Show featured image column
     */
    public function show_featured_image_column($column_name, $post_id)
    {
        if ('featured_image' !== $column_name) {
            return;
        }

        if (has_post_thumbnail($post_id)) {
            echo '<a href="' . get_edit_post_link($post_id) . '">';
            echo get_the_post_thumbnail($post_id, array(50, 50));
            echo '</a>';
        } else {
            echo '—';
        }
    }

    /**
     * Default to showing only published posts
     */
    public function default_published_post()
    {
        global $submenu;

        // POSTS
        if (isset($submenu['edit.php'])) {
            foreach ($submenu['edit.php'] as $key => $value) {
                if (in_array('edit.php', $value)) {
                    $submenu['edit.php'][$key][2] = 'edit.php?post_status=publish&post_type=post';
                }
            }
        }

        // Define post types to check
        $post_types = ['page', 'product', 'portfolio']; // Add other custom post types as needed
        foreach ($post_types as $post_type) {
            $submenu_key = 'edit.php?post_type=' . $post_type;

            // Check if the submenu exists for the post type
            if (isset($submenu[$submenu_key])) {
                foreach ($submenu[$submenu_key] as $key => $value) {
                    if (in_array($submenu_key, $value)) {
                        $submenu[$submenu_key][$key][2] = 'edit.php?post_status=publish&post_type=' . $post_type;
                    }
                }
            }
        }

        // Also hook into admin init for redirects
        add_action('admin_init', array($this, 'redirect_to_published_view'));
    }

    /**
     * Redirect admin to published view
     */
    public function redirect_to_published_view()
    {
        global $pagenow;
        $post_types = ['post', 'page', 'product', 'portfolio']; // Match post types above

        // Check if we're on the posts/pages listing without any status filter already applied
        if ($pagenow === 'edit.php' && !isset($_GET['post_status'])) {
            $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
            if (in_array($post_type, $post_types)) {
                wp_redirect(add_query_arg('post_status', 'publish', $_SERVER['REQUEST_URI']));
                exit;
            }
        }
    }


    /**
     * Rename default template
     */
    public function rename_default_template()
    {
        return __('Default Landing Template', 'salient');
    }

    /**
     * Elementor as default editor - modify list row actions (global version)
     */
    public function elementor_modify_list_row_actions_global($actions, $post)
    {
        // Build the Elementor edit URL
        $elementor_edit_url = admin_url('post.php?post=' . $post->ID . '&action=elementor');

        // Rewrite the normal Edit link
        $actions['edit_with_elementor'] = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url($elementor_edit_url),
            esc_html(__('Elementor Editor', 'elementor'))
        );

        return $actions;
    }

    /**
     * Minor styling for Elementor (global version)
     */
    public function elementor_styling_global()
    {
        echo '<style>
            td.title.column-title.has-row-actions.column-primary.page-title strong {
                font-weight: 200;
            }
        </style>';
    }

    /***************************************************************************
     * UTILITY ADD-ONS
     ***************************************************************************/

    /**
     * Initialize utility add-ons
     *
     * @since    1.0.0
     */
    private function init_utility_addons()
    {
        // Duplicate Posts functionality
        if (get_option("cdswerx_extensions", [])["enable_post_duplication"] ?? 0) {
            add_filter('post_row_actions', [$this, 'rd_duplicate_post_link'], 10, 2);
            add_filter('page_row_actions', [$this, 'rd_duplicate_post_link'], 10, 2);
            add_action('admin_action_rd_duplicate_post_as_draft', [$this, 'rd_duplicate_post_as_draft']);
            add_action('admin_notices', [$this, 'rudr_duplication_admin_notice']);
        }

        // Use native WordPress functions directly for registering shortcodes
        // This is the recommended approach for this plugin
        add_shortcode('year', [$this, 'year_shortcode']);
        add_shortcode('month', [$this, 'month_shortcode']);
        add_shortcode('yyyymmdd', [$this, 'yyyymmdd_shortcode']);
        add_shortcode('monthyear', [$this, 'monthyear_shortcode']);
        add_shortcode('day', [$this, 'day_shortcode']);
        add_shortcode('cdswerx_test', [$this, 'shortcode_test']);

        // Enable shortcodes in widgets
        if (!has_filter('widget_text', 'do_shortcode')) {
            add_filter('widget_text', 'do_shortcode');
        }

        error_log('CDSWerx: Global shortcodes registered directly');

        // SVG Upload support
        if (get_option("cdswerx_extensions", [])["enable_svg_upload"] ?? 0) {
            add_filter('upload_mimes', [$this, 'allow_svg_uploads']);
            add_filter('wp_check_filetype_and_ext', [$this, 'svg_mime_check'], 10, 5);
        }
    }

    /**
     * Register date shortcodes on init for proper loading
     * This ensures shortcodes are available throughout WordPress
     */
    public function register_date_shortcodes()
    {
        // Since we're already directly registering the shortcodes in init_utility_addons,
        // this method is just a backup to ensure they're registered.
        // No need to register them again.
        error_log('CDSWerx: Date shortcodes registered on init');
    }

    /**
     * Add the duplicate link to action list for post_row_actions
     */
    public function rd_duplicate_post_link($actions, $post)
    {
        // Only add duplicate link if not a revision or auto-draft
        if ($post->post_type != 'revision' && $post->post_status != 'auto-draft') {
            $url = wp_nonce_url(
                'admin/post.php?action=edit&post=' .
                    $post->ID .
                    '&duplicate=true',
                'duplicate-' . $post->ID,
            );
            $actions['duplicate'] = '<a href="' . esc_url($url) . '" class="editinline" title="' . esc_attr__('Duplicate', 'cdswerx') . '"> ' . __('Duplicate', 'cdswerx') . '</a>';
        }
        return $actions;
    }

    /**
     * Function for post duplication. Dups appear as drafts. User is redirected to the edit screen
     */
    public function rd_duplicate_post_as_draft()
    {
        global $wpdb;

        // Check the nonce for security
        $nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : '';
        if (!wp_verify_nonce($nonce, 'duplicate-' . $_REQUEST['post'])) {
            return;
        }

        $post_id = absint($_REQUEST['post']);

        // Get the original post data
        $post = get_post($post_id);

        if ($post) {
            /*
             * If this is a WooCommerce product, we need to duplicate the product meta as well.
             * This includes attributes, variations, and other custom meta fields.
             */
            $is_woocommerce = in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', null));
            $is_product = $post->post_type === 'product';

            // Insert the new post as a draft
            $new_post_id = wp_insert_post(
                array(
                    'post_title' => $post->post_title . ' ' . __('Copy', 'cdswerx'),
                    'post_content' => $post->post_content,
                    'post_excerpt' => $post->post_excerpt,
                    'post_status' => 'draft',
                    'post_type' => $post->post_type,
                    'post_author' => get_current_user_id(),
                    'post_date' => current_time('mysql'),
                    'post_date_gmt' => current_time('mysql', 1),
                ),
                $wp_error
            );

            if (!is_wp_error($new_post_id)) {
                // Copy the taxonomy terms (categories, tags, etc.)
                $taxonomies = get_object_taxonomies($post->post_type);
                foreach ($taxonomies as $taxonomy) {
                    $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'ids'));
                    wp_set_object_terms($new_post_id, $terms, $taxonomy);
                }

                /*
                 * If this is a WooCommerce product, duplicate the product-specific data.
                 * This includes attributes, variations, and other custom meta fields.
                 */
                if ($is_woocommerce && $is_product) {
                    // Get the product attributes
                    $attributes = get_post_meta($post_id, '_product_attributes', true);
                    update_post_meta($new_post_id, '_product_attributes', $attributes);

                    // Duplicate variations if they exist
                    $parent_id = $post_id;
                    $children = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_stock_status' AND meta_value != '' AND post_id != %d",
                            $parent_id
                        )
                    );

                    if ($children) {
                        foreach ($children as $child) {
                            $variation_id = $child->post_id;

                            // Duplicate the variation
                            $new_variation_id = wp_insert_post(
                                array(
                                    'post_title' => 'Variation ' . __('Copy', 'cdswerx'),
                                    'post_name' => 'variation-' . $new_post_id,
                                    'post_status' => 'draft',
                                    'post_type' => 'product_variation',
                                    'post_parent' => $new_post_id,
                                    'post_author' => get_current_user_id(),
                                    'post_date' => current_time('mysql'),
                                    'post_date_gmt' => current_time('mysql', 1),
                                ),
                                $wp_error
                            );

                            if (!is_wp_error($new_variation_id)) {
                                // Copy variation meta
                                $meta_keys = $wpdb->get_col(
                                    $wpdb->prepare(
                                        "SELECT meta_key FROM $wpdb->postmeta WHERE post_id = %d",
                                        $variation_id
                                    )
                                );

                                foreach ($meta_keys as $meta_key) {
                                    if ($meta_key == '_wp_old_slug')
                                        continue;
                                    $meta_value = addslashes(get_post_meta($variation_id, $meta_key, true));
                                    $wpdb->query(
                                        $wpdb->prepare(
                                            "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES (%d, %s, %s)",
                                            $new_variation_id,
                                            $meta_key,
                                            $meta_value
                                        )
                                    );
                                }
                            }
                        }
                    }
                }

                // Copy all post meta except the excluded keys
                $post_meta_infos = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d",
                        $post_id
                    )
                );

                $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) VALUES ";
                $sql_query_sel = [];
                foreach ($post_meta_infos as $meta_info) {
                    $meta_key = $meta_info->meta_key;
                    if ($meta_key == '_wp_old_slug')
                        continue;
                    $meta_value = addslashes($meta_info->meta_value);
                    $sql_query_sel[] = "SELECT $new_post_id, '$meta_key', '$meta_value'";
                }
                $sql_query .= implode(" UNION ALL ", $sql_query_sel);
                $wpdb->query($sql_query);
            }

            /*
             * finally, redirect to the edit post screen for the new draft
             */
            wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
            exit;
        } else {
            wp_die('Post creation failed, could not find original post: ' . $post_id);
        }
    }

    /**
     * Admin notices for duplication
     */
    public function rudr_duplication_admin_notice()
    {
        // Get the current screen
        $screen = get_current_screen();

        if ('edit' !== $screen->base) {
            return;
        }

        //Checks if settings updated
        if (isset($_GET['saved']) && 'post_duplication_created' == $_GET['saved']) {
            echo '<div class="notice notice-success is-dismissible"><p>Post copy created.</p></div>';
        }
    }

    /**
     * Year shortcode
     */
    public function year_shortcode($atts = [], $content = null)
    {
        // Debug logging
        error_log('CDSWerx: year_shortcode called');
        $year = date_i18n('Y');
        return $year;
    }

    /**
     * Month shortcode
     */
    public function month_shortcode($atts = [], $content = null)
    {
        // Debug logging
        error_log('CDSWerx: month_shortcode called');
        $month = date_i18n('F');
        return $month;
    }

    /**
     * YYYY-MM-DD shortcode
     */
    public function yyyymmdd_shortcode($atts = [], $content = null)
    {
        // Debug logging
        error_log('CDSWerx: yyyymmdd_shortcode called');
        $yyyymmdd = date_i18n('Y-m-d'); // Corrected from 'y-m-d' to 'Y-m-d' for 4-digit year
        return $yyyymmdd;
    }

    /**
     * Month and year shortcode
     */
    public function monthyear_shortcode($atts = [], $content = null)
    {
        // Debug logging
        error_log('CDSWerx: monthyear_shortcode called');
        $monthyear = date_i18n('F Y');
        return $monthyear;
    }

    /**
     * Day shortcode
     */
    public function day_shortcode($atts = [], $content = null)
    {
        // Debug logging
        error_log('CDSWerx: day_shortcode called');
        $day = date_i18n('l');
        return $day;
    }

    /**
     * Runs diagnostic tests on shortcodes
     * Add [cdswerx_test] to any page to check if shortcodes are working
     */
    public function shortcode_test($atts = [], $content = null)
    {
        error_log('CDSWerx: shortcode_test called - testing shortcode system');

        // Test all shortcodes
        $output = '<div class="shortcode-test">';
        $output .= '<h3>CDSWerx Shortcode Test</h3>';
        $output .= '<p><strong>Current year:</strong> ' . date_i18n('Y') . '</p>';
        $output .= '<p><strong>Current month:</strong> ' . date_i18n('F') . '</p>';
        $output .= '<p><strong>Current day:</strong> ' . date_i18n('l') . '</p>';
        $output .= '<p><strong>Date:</strong> ' . date_i18n('Y-m-d') . '</p>';
        $output .= '<p><strong>Month/Year:</strong> ' . date_i18n('F Y') . '</p>';
        $output .= '</div>';

        return $output;
    }

    /***************************************************************************
     * TINYMCE EDITOR CUSTOMIZATIONS
     ***************************************************************************/

    /**
     * Initialize editor customizations
     *
     * @since    1.0.0
     */
    private function init_editor_customizations()
    {
        // TinyMCE customizations - Force enable for troubleshooting
        // Comment the conditions to always apply the customizations regardless of settings
        // if (get_option("cdswerx_extensions", [])["tinymce_custom_buttons"] ?? 0) {
        add_filter('mce_buttons', [$this, 'remove_tiny_mce_buttons_from_editor']);
        add_filter('mce_buttons_2', [$this, 'add_style_select_buttons']);
        // }

        // if (get_option("cdswerx_extensions", [])["tinymce_custom_styles"] ?? 0) {
        add_filter('tiny_mce_before_init', [$this, 'my_custom_styles']);
        // }
    }

    /**
     * Remove buttons from TinyMCE editor
     */
    public function remove_tiny_mce_buttons_from_editor($buttons)
    {
        $remove_buttons = array(
            //'formatselect', removes select format dropdown
            'blockquote',
            'hr', // horizontal line
            'wp_more', // read more link
            'dfw', // distraction free writing mode
            // 'wp_adv', kitchen sink toggle (if removed, kitchen sink will always display)
        );
        foreach ($buttons as $button_key => $button_value) {
            if (in_array($button_value, $remove_buttons)) {
                unset($buttons[$button_key]);
            }
        }
        return $buttons;
    }

    /**
     * Add style select buttons
     * 
     * Make sure styleselect is added to the right position
     */
    public function add_style_select_buttons($buttons)
    {
        // Debug to check what buttons are present before our modification
        error_log('CDSWerx: TinyMCE buttons before: ' . print_r($buttons, true));

        // Check if styleselect is already in the array
        if (!in_array('styleselect', $buttons)) {
            // Add styleselect as the first button
            array_unshift($buttons, 'styleselect');
        }

        // Debug to see the final buttons array
        error_log('CDSWerx: TinyMCE buttons after: ' . print_r($buttons, true));

        return $buttons;
    }

    /**
     * Add custom styles to TinyMCE
     */
    public function my_custom_styles($init_array)
    {
        // Log that this method is being called
        error_log('CDSWerx: my_custom_styles called for TinyMCE');

        // Set to true to include the default settings.
        // $init_array['style_formats_merge'] = true;

        // These are the custom styles
        $style_formats = array(
            //Additional Paragraph Styles
            array(
                'title' => 'Paragraph Styles',
                'items' => array(
                    array(
                        'title' => 'Paragraph',
                        'block' => 'p',
                    ),
                    array(
                        'title' => 'Paragraph XXL',
                        'block' => 'p',
                        'selector' => 'p',
                        'classes' => 'xxl',
                        'wrapper' => true,
                    ),
                    array(
                        'title' => 'Paragraph XL',
                        'block' => 'p',
                        'selector' => 'p',
                        'classes' => 'xl',
                        'wrapper' => true,
                    ),
                    array(
                        'title' => 'Paragraph L',
                        'block' => 'p',
                        'selector' => 'p',
                        'classes' => 'lg',
                        'wrapper' => true,
                    ),
                    array(
                        'title' => 'Paragraph S',
                        'block' => 'p',
                        'selector' => 'p',
                        'classes' => 'sm',
                        'wrapper' => true,
                    ),
                    array(
                        'title' => 'Paragraph XS',
                        'block' => 'p',
                        'selector' => 'p',
                        'classes' => 'xs',
                        'wrapper' => true,
                    ),
                    array(
                        'title' => 'Paragraph Light Text',
                        'block' => 'p',
                        'selector' => 'p',
                        'classes' => 'light',
                        'wrapper' => true,
                    ),
                )
            ),
            //Extra Header Styles
            array(
                'title' => 'Extra Header Styles',
                'items' => array(
                    array(
                        'title' => 'Hero H1 SEO Style',
                        'block' => 'h1',
                        'classes' => 'hero-header',
                    ),
                    array(
                        'title' => 'Display 1',
                        'block' => 'h1',
                        'classes' => 'display-1',
                    ),
                    array(
                        'title' => 'Display 2',
                        'block' => 'h1',
                        'classes' => 'display-2',
                    ),
                    array(
                        'title' => 'H2 Small',
                        'block' => 'h2',
                        'classes' => 'sm',
                    ),
                    array(
                        'title' => 'Header Light',
                        'block' => 'h1',
                        'classes' => 'light',
                    ),
                    array(
                        'title' => 'Header ExtraLight',
                        'block' => 'h1',
                        'classes' => 'xlight',
                    ),
                    array(
                        'title' => 'Header Underlined',
                        'block' => 'h1',
                        'classes' => 'underline-heading',
                    ),
                    array(
                        'title' => 'Footer Heading',
                        'block' => 'h6',
                        'classes' => 'footer-heading',
                    ),
                ),
            ),
            //Extra Bullet Styles
            array(
                'title' => 'Extra List Styles',
                'items' => array(
                    array(
                        'title' => 'Primary Color Bullet',
                        'block' => 'ul',
                        'selector' => 'ul',
                        'classes' => 'primary-bullet-list',
                    ),
                    array(
                        'title' => 'Secondary Color Bullet',
                        'block' => 'ul',
                        'selector' => 'ul',
                        'classes' => 'secondary-bullet-list',
                    ),
                    array(
                        'title' => 'Accent Color Bullet',
                        'block' => 'ul',
                        'selector' => 'ul',
                        'classes' => 'accent-bullet-list',
                    ),
                    array(
                        'title' => 'Primary Color Numbers',
                        'block' => 'ol',
                        'selector' => 'ol',
                        'classes' => 'primary-number-list',
                    ),
                    array(
                        'title' => 'Secondary Color Numbers',
                        'block' => 'ol',
                        'selector' => 'ol',
                        'classes' => 'secondary-number-list',
                    ),
                    array(
                        'title' => 'Accent Color Numbers',
                        'block' => 'ol',
                        'selector' => 'ol',
                        'classes' => 'accent-number-list',
                    )
                )
            ),
            //Misc Text Styles
            array(
                'title' => 'Misc Text Styles',
                'items' => array(
                    array(
                        'title' => 'Breakline',
                        'inline' => 'span',
                        'classes' => 'breakline',
                        'wrapper' => true,
                    ),
                    array(
                        'title' => 'Text Link',
                        'selector' => 'a',
                        'classes' => 'textlink'
                    ),
                    array(
                        'title' => 'AllCaps Text',
                        'inline' => 'span',
                        'styles' => array('textTransform' => 'uppercase'),
                    ),
                    array(
                        'title' => 'Capitalize',
                        'inline' => 'span',
                        'styles' => array('textTransform' => 'capitalize'),
                    ),
                    array(
                        'title' => 'Justify Text',
                        'block' => 'p',
                        'styles' => array('textAlign' => 'justify'),
                    ),
                    array(
                        'title' => 'ExtraBold Text',
                        'block' => 'p',
                        'classes' => 'xbold',
                    ),
                    array(
                        'title' => 'ExtraLight Text',
                        'block' => 'p',
                        'classes' => 'xlight',
                    ),
                    array(
                        'title' => 'Caption Text',
                        'block' => 'span',
                        'classes' => 'text-caption',
                    ),
                    array(
                        'title' => 'Superscript',
                        'inline' => 'span',
                        'classes' => 'sup',
                    ),
                    array(
                        'title' => 'Superscript $Dollar',
                        'inline' => 'span',
                        'classes' => 'dollar',
                    ),
                    array(
                        'title' => 'Icon Block',
                        'block' => 'div',
                        'classes' => 'icon-block',
                        'wrapper' => true,
                    )
                )
            )
        );

        // Insert the array, JSON ENCODED, into 'style_formats'
        $init_array['style_formats'] = json_encode($style_formats);

        // Log the JSON string we're adding
        error_log('CDSWerx: Adding style_formats: ' . $init_array['style_formats']);

        return $init_array;
    }

    /***************************************************************************
     * TYPOGRAPHY SYNC INTEGRATION
     ***************************************************************************/

    /**
     * Initialize typography synchronization system.
     * 
     * Integrates with Typography Sync Manager and CSS Reader classes
     * to provide automated typography fallback system.
     *
     * @since    1.0.0
     */
    private function init_typography_sync()
    {
        // Initialize Typography Sync Manager if classes exist
        if (class_exists('Cdswerx_Typography_Sync') && class_exists('Cdswerx_Typography_CSS_Reader')) {
            
            // Create Typography CSS Reader instance
            $css_reader = new Cdswerx_Typography_CSS_Reader();
            
            // Get plugin CSS file path
            $plugin_css_file = plugin_dir_path(dirname(__FILE__)) . '../public/css/cdswerx-public.css';
            
            // Initialize CSS reader integration
            $sync_data = $css_reader->init_typography_sync_integration($plugin_css_file);
            
            // Create Typography Sync Manager instance
            $typography_sync = new Cdswerx_Typography_Sync($this->plugin_name, $this->version);
            
            // Hook integration points
            add_action('cdswerx_typography_css_reader_initialized', [$this, 'handle_typography_sync_data']);
            add_action('cdswerx_typography_sync_initialized', [$this, 'handle_typography_rules_update']);
            
            // Log initialization
            error_log('CDSWerx Extensions: Typography sync system initialized');
            
        } else {
            error_log('CDSWerx Extensions: Typography sync classes not found');
        }
    }

    /**
     * Handle typography sync data from CSS Reader.
     *
     * @since    1.0.0
     * @param    array    $sync_data    Typography sync data from CSS Reader
     */
    public function handle_typography_sync_data($sync_data)
    {
        // Validate sync data
        if (empty($sync_data['typography_rules']) || !$sync_data['validation']['valid']) {
            error_log('CDSWerx Extensions: Typography sync validation failed');
            return;
        }

        // Store sync data for Extensions Class usage
        update_option('cdswerx_typography_sync_data', $sync_data);
        
        // Trigger sync completion action
        do_action('cdswerx_extensions_typography_sync_ready', $sync_data);
        
        error_log('CDSWerx Extensions: Typography sync data processed - ' . 
                 count($sync_data['typography_rules']) . ' rules found');
    }

    /**
     * Handle typography rules update from Sync Manager.
     *
     * @since    1.0.0
     * @param    array    $typography_rules    Updated typography rules
     */
    public function handle_typography_rules_update($typography_rules)
    {
        // Update cached typography rules
        update_option('cdswerx_typography_rules_cache', $typography_rules);
        
        // Clear any related caches
        wp_cache_delete('cdswerx_typography_rules', 'cdswerx');
        
        error_log('CDSWerx Extensions: Typography rules updated - ' . 
                 count($typography_rules) . ' rules cached');
    }

    /**
     * Get current typography sync status.
     *
     * @since    1.0.0
     * @return   array    Typography sync status information
     */
    public function get_typography_sync_status()
    {
        $sync_data = get_option('cdswerx_typography_sync_data', []);
        $cached_rules = get_option('cdswerx_typography_rules_cache', []);
        
        return [
            'initialized' => !empty($sync_data),
            'rules_count' => count($cached_rules),
            'last_sync' => $sync_data['last_read'] ?? 0,
            'validation_status' => $sync_data['validation'] ?? ['valid' => false]
        ];
    }
}
