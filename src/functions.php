<?php

namespace Ribarich\SE;

function init() {
	$container = get_container();
	$main = $container->get( Main::class );
	$main->init();
}
