<?php
declare(strict_types=1);

namespace Ribarich\SE;

defined( 'ABSPATH' ) || exit;

use Automattic\Jetpack\Constants;
use DI\Container;

class Main {
	public $admin;

	public $fees;

	public $container;

	public $scripts;

	public function __construct(
		Admin $admin,
		Scripts $scripts,
		Container $container
	) {
		$this->admin     = $admin;
		$this->container = $container;
		$this->scripts   = $scripts;
	}

	public function init() {
		$this->admin->init();

		$missing_dependencies = $this->get_missing_dependencies();

		if ( ! empty( $missing_dependencies ) ) {
			$names = array_values( $missing_dependencies );
			$names = implode( ', ', $names );
			/* translators: %s: Plugin names. */
			$this->handle_error( sprintf( __( 'Missing plugins: %s', 'ribarich_se' ), $names ) );
			return;
		}

		$this->admin->complete_init();
		$this->scripts->init();

		\add_action( 'init', array( $this, 'complete_init' ) );
	}

	/**
	 * Complete initialization after other WordPress plugins have loaded so that
	 * we can access their classes, functions, etc.
	 */
	public function complete_init() {
		$this->fees = $this->container->get( Fees::class );
		$this->fees->init();
	}

	public function handle_error( string $message = '', string $name = 'initialization_failed' ) {
		if ( empty( $message ) ) {
			$message = __( 'Failed to initialize.', 'ribarich_se' );
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
		trigger_error(
			$message, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
