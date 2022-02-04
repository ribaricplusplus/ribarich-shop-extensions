<?php

namespace Ribarich\SE\Tests;

class Fees_Test extends \WC_Unit_Test_Case {
	public static $shipping_methods = array();

	public function set_up() {
		parent::set_up();

		// Set a valid address for the customer so shipping rates will calculate.
		\WC()->customer->set_shipping_country( 'US' );
		\WC()->customer->set_shipping_state( 'NY' );
		\WC()->customer->set_shipping_postcode( '12345' );

		$zone = new \WC_Shipping_Zone();
		$zone->set_zone_name( 'US' );
		$zone->set_zone_order( 1 );
		$zone->add_location( 'US', 'country' );

		$instance_id                         = $zone->add_shipping_method( 'flat_rate' );
		$method                              = \WC_Shipping_Zones::get_shipping_method( $instance_id );
		self::$shipping_methods['flat_rate'] = array(
			'id'                             => $instance_id,
			'cost'                           => '30',
			'ribarich_se_shipping_insurance' => '2',
		);
		$method->instance_settings['cost']   = self::$shipping_methods['flat_rate']['cost'];
		$method->instance_settings['ribarich_se_shipping_insurance'] = self::$shipping_methods['flat_rate']['ribarich_se_shipping_insurance'];
		\update_option( $method->get_instance_option_key(), $method->instance_settings, 'yes' );

		$zone->save();

		\WC()->cart->empty_cart();
		\WC()->session->set( 'chosen_shipping_methods', array() );
		\WC_Cache_Helper::get_transient_version( 'shipping', true );
		\WC_Cache_Helper::invalidate_cache_group( 'shipping_zones' );
	}

	public function test_shipping_insurance_fee_is_added_correctly() {
		$product = \WC_Helper_Product::create_simple_product(
			true,
			array(
				'regular_price' => 10,
				'price'         => 10,
			)
		);

		\WC()->session->set( 'chosen_shipping_methods', array( 'flat_rate:1' ) );

		\WC()->cart->add_to_cart( $product->get_id(), 1 );
		\WC()->cart->calculate_totals();

		$product_cost = 10;
		$fee          = ( $product_cost + self::$shipping_methods['flat_rate']['cost'] ) * ( self::$shipping_methods['flat_rate']['ribarich_se_shipping_insurance'] / 100 );
		$fees         = \WC()->cart->get_fees();
		$instance     = \Ribarich\SE\get_container()->get( \Ribarich\SE\Fees::class );
		$this->assertEqualsWithDelta( (float) $fee, (float) $fees[ \sanitize_title( $instance->get_fee_name( 'shipping_insurance' ) ) ]->amount, 0.01 );
	}
}
