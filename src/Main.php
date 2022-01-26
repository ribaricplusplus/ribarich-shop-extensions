<?php

namespace Ribarich\SE;

use Automattic\Jetpack\Constants;

class Main {
	public $admin;

	public function __construct(
		Admin $admin
	) {
		$this->admin = $admin;
	}

	public function init() {
		$this->admin->init();

		$missing_dependencies = $this->get_missing_dependencies();

		if ( ! empty( $missing_dependencies ) ) {
			$names = array_values( $missing_dependencies );
			$names = implode( ', ', $names );
			$this->handle_error( sprintf( __( 'Missing plugins: %s', 'ribarich_se' ), $names ) );
			return;
		}

		$this->admin->complete_init();
	}

	public function handle_error( string $message = '', string $name = 'initialization_failed' ) {
		if ( empty( $message ) ) {
			$message = __( 'Failed to initialize.', 'ribarich_se' );
		}

		trigger_error(
			$message,
			\E_USER_NOTICE
		);

		$this->admin->add_notice( $name, $message );
	}

	public function get_missing_dependencies() {
		if ( ! \function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$missing = array();

		if ( Constants::is_true( 'WP_PHPUNIT_TESTING' ) ) {
			// We assume that all dependencies exist in phpunit tests (does not apply to e2e).
			return $missing;
		}

		if ( ! \is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$missing['woocommerce'] = 'WooCommerce';
		}

		return $missing;
	}
}
