<?php

namespace Ribarich\SE\Tests;

class Fees_Test extends \WC_Unit_Test_Case {
	public static $shipping_methods = array();
	const DELTA                     = 0.01;

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
		$this->assertEqualsWithDelta( (float) $fee, (float) $this->get_shipping_insurance_fee_from_cart(), self::DELTA );
	}

	/**
	 * @dataProvider taxes_data_provider
	 */
	public function test_shipping_insurance_fee_taxes( $should_fee_be_taxable ) {
		\update_option( 'woocommerce_calc_taxes', 'yes' );
		$tax_rate = array(
			'tax_rate_country'  => '',
			'tax_rate_state'    => '',
			'tax_rate'          => '20.0000',
			'tax_rate_name'     => 'TAX20',
			'tax_rate_priority' => '1',
			'tax_rate_compound' => '0',
			'tax_rate_shipping' => '0',
			'tax_rate_order'    => '1',
			'tax_rate_class'    => '20percent',
		);
		\WC_Tax::_insert_tax_rate( $tax_rate );
		$product_price = 100;
		$product       = \WC_Helper_Product::create_simple_product(
			true,
			array(
				'regular_price' => $product_price,
				'price'         => $product_price,
			)
		);
		$product->set_tax_class( '20percent' );
		$product->save();

		$smdata = self::$shipping_methods['flat_rate'];

		if ( $should_fee_be_taxable ) {
			// Get shipping method and change taxes option
			$method = \WC_Shipping_Zones::get_shipping_method( $smdata['id'] );
			$method->instance_settings['ribarich_se_shipping_insurance_apply_taxes'] = 'yes';
			\update_option( $method->get_instance_option_key(), $method->instance_settings, 'yes' );
		}

		\WC()->session->set( 'chosen_shipping_methods', array( 'flat_rate:1' ) );
		\WC()->cart->add_to_cart( $product->get_id(), 1 );
		\WC()->cart->calculate_totals();

		$fee      = ( $product_price + $smdata['cost'] ) * ( $smdata['ribarich_se_shipping_insurance'] / 100 );
		$cart_fee = $this->get_shipping_insurance_fee_from_cart();

		$this->assertEqualsWithDelta( $fee, $cart_fee, self::DELTA );

		if ( $should_fee_be_taxable ) {
			$taxes = ( $product_price + $fee ) * 0.2;
		} else {
			$taxes = $product_price * 0.2;
		}

		$this->assertEqualsWithDelta( $taxes, \WC()->cart->get_total_tax(), self::DELTA );
	}

	public function get_shipping_insurance_fee_from_cart() {
		$fees = \WC()->cart->get_fees();
		if ( empty( $fees ) ) {
			return 0;
		}
		$instance = \Ribarich\SE\get_container()->get( \Ribarich\SE\Fees::class );
		return $fees[ \sanitize_title( $instance->get_fee_name( 'shipping_insurance' ) ) ]->amount;
	}

	public function taxes_data_provider() {
		return array(
			array(
				true,
			),
			array(
				false,
			),
		);
	}
}
