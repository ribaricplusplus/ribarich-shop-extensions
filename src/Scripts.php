<?php

namespace Ribarich\SE;

defined( 'ABSPATH' ) || exit;

class Scripts {
	public function init() {
		\add_action( 'init', array( $this, 'register_scripts' ) );
		\add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function register_scripts() {
		$data = $this->get_script_data( 'cart' );
		\wp_register_script(
			'ribarich-se-cart',
			\plugins_url( 'build/cart.js', \RIBARICH_SE_FILE ),
			$data['dependencies'],
			$data['version']
		);
	}

	public function enqueue_scripts() {
		if ( \is_cart() ) {
			\wp_enqueue_script( 'ribarich-se-cart' );
		}
	}

	/**
	 * Get script data as produced by dependency extraction webpack plugin
	 *
	 * @param string $script_name Script name defined by a webpack entry point.
	 * @return array Script data (version, dependencies)
	 */
	protected function get_script_data( $script_name ) {
		$assets_path = plugin_dir_path( \RIBARICH_SE_FILE ) . 'build/' . $script_name . '.asset.php';

		if ( file_exists( $assets_path ) ) {
			$data = require $assets_path;
			return $data;
		}

		return array(
			'dependencies' => array(),
			'version'      => '',
		);
	}
}
