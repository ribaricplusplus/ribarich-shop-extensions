<?php

namespace Ribarich\SE;

use DI\Container;

class Admin {
	public $notices;

	public $settings;

	public $container;

	public function init() {
		$this->notices->init();
	}

	public function complete_init() {
		\add_action( 'init', array( $this, 'load' ) );
	}

	public function load() {
		$this->settings = $this->container->get( Admin\Settings::class );
		$this->settings->init();
	}

	public function __construct(
		Admin\Notices $notices,
		Container $container
	) {
		$this->notices = $notices;
		$this->container = $container;
	}

	public function add_notice( string $name, string $message ) {
		return $this->notices->add_notice( $name, $message );
	}
}
