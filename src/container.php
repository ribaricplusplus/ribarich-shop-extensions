<?php

namespace Ribarich\SE;

use DI\ContainerBuilder;
use DI\Container;

function get_container(): Container {
	static $container;

	if ( $container ) {
		return $container;
	}

	$builder = new ContainerBuilder();
	$builder->addDefinitions(
		array(
			'WooCommerce' => function() {
				return \WC(); },
			'WC_Cart'     => function() {
				return \WC()->cart; },
			'WC_Shipping' => function() {
				return \WC()->shipping(); },
		)
	);
	$container = $builder->build();
	return $container;
}
