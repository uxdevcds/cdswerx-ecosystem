<?php

namespace CDSWerxTheme\Modules\AdminHome\Rest;

use CDSWerxTheme\Includes\Utils;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Promotions extends Rest_Base {

	public function get_promotions() {
		$action_links_data = [];

		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) && Utils::is_elementor_active() ) {
			$action_links_data[] = [
				'type' => 'go-pro',
				'image' => CDSWERX_THEME_IMAGES_URL . 'go-pro.svg',
				'url' => 'https://go.elementor.com/hello-upgrade-epro/',
				'alt' => __( 'Elementor Pro', 'cdswerx-theme' ),
				'title' => __( 'Bring your vision to life', 'cdswerx-theme' ),
				'messages' => [
					__( 'Get complete design flexibility for your website with Elementor Pro’s advanced tools and premium features.', 'cdswerx-theme' ),
				],
				'button' => __( 'Upgrade Now', 'cdswerx-theme' ),
				'upgrade' => true,
				'features' => [
					__( 'Popup Builder', 'cdswerx-theme' ),
					__( 'Custom Code & CSS', 'cdswerx-theme' ),
					__( 'E-commerce Features', 'cdswerx-theme' ),
					__( 'Collaborative Notes', 'cdswerx-theme' ),
					__( 'Form Submission', 'cdswerx-theme' ),
					__( 'Form Integrations', 'cdswerx-theme' ),
					__( 'Customs Attribute', 'cdswerx-theme' ),
					__( 'Role Manager', 'cdswerx-theme' ),
				],
			];
		}

		if (
			! defined( 'ELEMENTOR_IMAGE_OPTIMIZER_VERSION' ) &&
			! defined( 'IMAGE_OPTIMIZATION_VERSION' )
		) {
			$action_links_data[] = [
				'type' => 'go-image-optimizer',
				'image' => CDSWERX_THEME_IMAGES_URL . 'image-optimizer.svg',
				'url' => Utils::get_plugin_install_url( 'image-optimization' ),
				'alt' => __( 'Elementor Image Optimizer', 'cdswerx-theme' ),
				'title' => '',
				'messages' => [
					__( 'Optimize Images.', 'cdswerx-theme' ),
					__( 'Reduce Size.', 'cdswerx-theme' ),
					__( 'Improve Speed.', 'cdswerx-theme' ),
					__( 'Try Image Optimizer for free', 'cdswerx-theme' ),
				],
				'button' => __( 'Install', 'cdswerx-theme' ),
				'width' => 72,
				'height' => 'auto',
				'target' => '_self',
				'backgroundImage' => CDSWERX_THEME_IMAGES_URL . 'image-optimization-bg.svg',
			];
		}

		if ( ! defined( 'SEND_VERSION' ) ) {
			$action_links_data[] = [
				'type' => 'go-send',
				'image' => CDSWERX_THEME_IMAGES_URL . 'send-logo.gif',
				'backgroundColor' => '#EFEFFF',
				'url' => 'https://go.elementor.com/Hello_send',
				'alt' => __( 'Send', 'cdswerx-theme' ),
				'title' => '',
				'messages' => [
					__( 'Connect any website to automated Email & SMS workflows in a click with Send.', 'cdswerx-theme' ),
				],
				'button' => __( 'Install', 'cdswerx-theme' ),
				'buttonBgColor' => '#524CFF',
				'width' => 72,
				'height' => 'auto',
			];
		}

		return rest_ensure_response( [ 'links' => $action_links_data ] );
	}

	public function register_routes() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'/promotions',
			[
				'methods' => WP_REST_Server::READABLE,
				'callback' => [ $this, 'get_promotions' ],
				'permission_callback' => [ $this, 'permission_callback' ],
			]
		);
	}
}
