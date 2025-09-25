<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 *
 * @package    Cdswerx
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete plugin options
 */
function cdswerx_delete_plugin_options() {
	// Get plugin options to check if cleanup is enabled
	$options = get_option( 'cdswerx_options', array() );

	// Only cleanup if the user has enabled it
	if ( isset( $options['cleanup_on_uninstall'] ) && $options['cleanup_on_uninstall'] ) {

		// Delete main plugin options
		delete_option( 'cdswerx_options' );

		// Delete any transients
		delete_transient( 'cdswerx_cache' );
		delete_transient( 'cdswerx_data' );

		// Delete user meta for all users
		global $wpdb;
		$wpdb->delete(
			$wpdb->usermeta,
			array(
				'meta_key' => 'cdswerx_user_preference'
			)
		);

		// Clean up any custom database tables (if created)
		// Uncomment and modify if you have custom tables
		/*
		$table_name = $wpdb->prefix . 'cdswerx_data';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
		*/

		// Clear any scheduled hooks
		wp_clear_scheduled_hook( 'cdswerx_daily_cleanup' );
		wp_clear_scheduled_hook( 'cdswerx_weekly_maintenance' );

		// Remove custom post types and their posts
		$custom_posts = get_posts( array(
			'post_type' => 'cdswerx_item',
			'numberposts' => -1,
			'post_status' => 'any'
		) );

		foreach ( $custom_posts as $post ) {
			wp_delete_post( $post->ID, true );
		}

		// Remove custom taxonomies
		// This will also remove all terms associated with the taxonomy
		$terms = get_terms( array(
			'taxonomy' => 'cdswerx_category',
			'hide_empty' => false,
		) );

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				wp_delete_term( $term->term_id, 'cdswerx_category' );
			}
		}

		// Remove any uploaded files in plugin directory
		$upload_dir = wp_upload_dir();
		$plugin_upload_dir = $upload_dir['basedir'] . '/cdswerx/';

		if ( is_dir( $plugin_upload_dir ) ) {
			cdswerx_remove_directory( $plugin_upload_dir );
		}

		// Flush rewrite rules
		flush_rewrite_rules();

		// Clear any caches
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}
	}
}

/**
 * Recursively remove directory and its contents
 */
function cdswerx_remove_directory( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return false;
	}

	$files = array_diff( scandir( $dir ), array( '.', '..' ) );

	foreach ( $files as $file ) {
		$path = $dir . '/' . $file;

		if ( is_dir( $path ) ) {
			cdswerx_remove_directory( $path );
		} else {
			unlink( $path );
		}
	}

	return rmdir( $dir );
}

/**
 * Handle multisite uninstall
 */
function cdswerx_uninstall_multisite() {
	if ( is_multisite() ) {
		global $wpdb;

		// Get all blog IDs
		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

		foreach ( $blog_ids as $blog_id ) {
			switch_to_blog( $blog_id );
			cdswerx_delete_plugin_options();
			restore_current_blog();
		}

		// Delete network-wide options if any
		delete_site_option( 'cdswerx_network_options' );
	} else {
		cdswerx_delete_plugin_options();
	}
}

// Execute the uninstall
cdswerx_uninstall_multisite();
