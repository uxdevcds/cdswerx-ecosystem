<?php

namespace CDSWerxTheme\Modules\AdminHome\Components;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CDSWerxTheme\Includes\Utils;

class Admin_Menu_Controller {

	const MENU_PAGE_ICON = 'dashicons-plus-alt';
	const MENU_PAGE_POSITION = 59.9;
	const THEME_BUILDER_SLUG = '-theme-builder';

	public function admin_menu(): void {
		add_menu_page(
			__( 'CDSWerx Theme', 'cdswerx-theme' ),
			__( 'CDSWerx Theme', 'cdswerx-theme' ),
			'manage_options',
			EHP_THEME_SLUG,
			[ $this, 'render_home' ],
			self::MENU_PAGE_ICON,
			self::MENU_PAGE_POSITION
		);

		add_submenu_page(
			EHP_THEME_SLUG,
			__( 'Home', 'cdswerx-theme' ),
			__( 'Home', 'cdswerx-theme' ),
			'manage_options',
			EHP_THEME_SLUG,
			[ $this, 'render_home' ]
		);

		add_submenu_page(
			EHP_THEME_SLUG,
			__( 'Settings', 'cdswerx-theme' ),
			__( 'Settings', 'cdswerx-theme' ),
			'manage_options',
			EHP_THEME_SLUG . '-settings',
			[ $this, 'render_settings' ]
		);

		do_action( 'cdswerx-plus-theme/admin-menu', EHP_THEME_SLUG );

		$theme_builder_slug = Utils::get_theme_builder_slug();
		add_submenu_page(
			EHP_THEME_SLUG,
			__( 'Theme Builder', 'cdswerx-theme' ),
			__( 'Theme Builder', 'cdswerx-theme' ),
			'manage_options',
			empty( $theme_builder_slug ) ? EHP_THEME_SLUG . self::THEME_BUILDER_SLUG : $theme_builder_slug,
			[ $this, 'render_home' ]
		);
	}

	public function render_home(): void {
		echo '<div id="ehe-admin-home"></div>';
	}

	public function render_settings(): void {
		// Load CDSWerx Settings Page
		include_once CDSWERX_THEME_PATH . '/includes/cdswerx-settings-page.php';
	}

	public function redirect_menus(): void {
		$page = sanitize_key( filter_input( INPUT_GET, 'page', FILTER_UNSAFE_RAW ) );

		switch ( $page ) {
			case EHP_THEME_SLUG . self::THEME_BUILDER_SLUG:
				wp_redirect( Utils::get_theme_builder_url() );
				exit;

			default:
				break;
		}
	}

	public function admin_enqueue_scripts() {
		// CDSWerx Theme operates independently without build system dependencies
		// Admin menu functionality is preserved without JavaScript enhancements
	}

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'admin_init', [ $this, 'redirect_menus' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}
}
