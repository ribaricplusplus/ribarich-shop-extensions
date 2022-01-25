<?php
/**
 * Plugin Name: Ribarich Shop Extensions
 * Description: Various WooCommerce extensions.
 * Requires at least: 5.8
 * Requires PHP: 7.3
 * Version: 0.1.0
 * Author: Bruno Ribaric
 * Author URI: https://ribarich.me/
 * Text Domain: ribarich_se
 */

define( 'RIBARICH_SE_FILE', __FILE__ );

require 'vendor/autoload.php';
require 'src/functions.php';

try {
	\Ribarich\SE\init();
} catch( \Ribarich\SE\Initialization_Exception $e ) {
	// Just don't fail completely.
}
