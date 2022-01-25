<?php

namespace Ribarich\SE;

use DI\ContainerBuilder;
use DI\Container

function get_container(): Container {
	static $container;

	if ( $container ) {
		return $container;
	}

	$builder = new ContainerBuilder();
	$builder->add_definitions(
		array(
			// TODO: Interface definitions go here.
		)
	);

	$container = $builder->build();
	return $container;
}
