<?php

namespace Ribarich\SE;

function init() {
	$container = get_container();
	$main      = $container->get( Main::class );
	$main->init();
}

function handle_exception( \Exception $e ) {
	// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_trigger_error
	trigger_error(
		$e->getMessage(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		\E_USER_NOTICE
	);
}
