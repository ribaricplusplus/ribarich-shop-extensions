<?php

namespace Ribarich\SE\Admin;

use Ribarich\SE;

class Notices {
	public $notices = array();

	public function init() {
		\add_action( 'admin_notices', array( $this, 'output_notices' ) );
	}

	public function __construct(
		SE\Views_Controller $views_controller
	) {
		$this->views = $views_controller;
	}

	public function output_notices() {
		foreach ( $this->notices as $name => $notice ) {
			$this->views->render( 'notices/error', $notice );
		}
	}

	public function add_notice( string $name, string $message ) {
		$this->notices[ $name ] = array(
			'message' => $message,
		);
	}
}
