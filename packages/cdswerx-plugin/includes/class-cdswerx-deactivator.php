<?php

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Fired during plugin deactivation
 *
 * @link       https://cdswerx.com
 * @since      1.0.0
 *
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Cdswerx
 * @subpackage Cdswerx/includes
 * @author     CDSWerx <info@cdswerx.com>
 */
class Cdswerx_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// Clear any cached data
		wp_cache_flush();

		// Flush rewrite rules
		flush_rewrite_rules();

		// Update deactivation timestamp
		$options = get_option( 'cdswerx_options', array() );
		$options['deactivation_date'] = current_time( 'mysql' );
		update_option( 'cdswerx_options', $options );

	}

}
