<?php

namespace CDSWerxTheme\Modules\AdminHome\Components;

use CDSWerxTheme\Includes\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Conversion_Banner {

	private function render_conversion_banner() {
		?>
		<div id="ehe-admin-cb" style="width: calc(100% - 48px)">
		</div>
		<?php
	}

	private function is_conversion_banner_active(): bool {
		if ( get_user_meta( get_current_user_id(), '_hello_elementor_install_notice', true ) ) {
			return false;
		}

		if ( Utils::has_pro() && Utils::is_elementor_active() ) {
			return false;
		}

		$current_screen = get_current_screen();

		return false === strpos( $current_screen->id ?? '', EHP_THEME_SLUG );
	}

	private function enqueue_scripts() {
		// CDSWerx Theme operates independently without build system dependencies
		// Conversion banner functionality preserved without JavaScript enhancements
	}

	public function dismiss_theme_notice() {
		check_ajax_referer( 'ehe_cb_nonce', 'nonce' );

		update_user_meta( get_current_user_id(), '_hello_elementor_install_notice', true );

		wp_send_json_success( [ 'message' => __( 'Notice dismissed.', 'cdswerx-theme' ) ] );
	}

	public function __construct() {

		add_action( 'wp_ajax_ehe_dismiss_theme_notice', [ $this, 'dismiss_theme_notice' ] );

		add_action( 'current_screen', function () {
			if ( ! $this->is_conversion_banner_active() ) {
				return;
			}

			add_action( 'in_admin_header', function () {
				$this->render_conversion_banner();
			}, 11 );

			add_action( 'admin_enqueue_scripts', function () {
				$this->enqueue_scripts();
			} );
		} );
	}
}
