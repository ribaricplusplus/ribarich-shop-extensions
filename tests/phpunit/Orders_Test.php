<?php

namespace Ribarich\SE\Tests;

use Ribarich\SE\Orders;
use function Ribarich\SE\get_container;

class Orders_Test extends \WC_Unit_Test_Case {
	public static $orders        = array();
	public static $sorted_orders = array();

	public function set_up() {
		parent::set_up();
		$props = array(
			array(
				'props' => array(
					'date_created' => gmdate( 'Y-m-d H:i:s', \wc_string_to_timestamp( '2012-01-01 08.00.00' ) ),
					'status'       => 'completed',
				),
				'order' => 2,
			),
			array(
				'props' => array(
					'date_created' => gmdate( 'Y-m-d H:i:s', \wc_string_to_timestamp( '2012-01-01 07.00.00' ) ),
					'status'       => 'completed',
				),
				'order' => 1,
			),
			array(
				'props' => array(
					'date_created' => gmdate( 'Y-m-d H:i:s', \wc_string_to_timestamp( '2012-01-01 09.00.00' ) ),
					'status'       => 'completed',
				),
				'order' => 3,
			),
			array(
				'props' => array(
					'date_created' => gmdate( 'Y-m-d H:i:s', \wc_string_to_timestamp( '2012-01-01 10.00.00' ) ),
					'status'       => 'failed',
				),
				'order' => 4,
			),
			array(
				'props' => array(
					'date_created' => gmdate( 'Y-m-d H:i:s', \wc_string_to_timestamp( '2012-01-02 09.00.00' ) ),
					'status'       => 'completed',
				),
				'order' => 5,
			),
		);
		foreach ( $props as $p ) {
			$order_id                  = $this->create_order( $p['props'] );
			self::$orders[ $order_id ] = $p;
		}

		self::$sorted_orders = self::$orders;
		uasort(
			self::$sorted_orders,
			function( $a, $b ) {
				return $a['order'] - $b['order'];
			}
		);
	}

	/**
	 * Tests that:
	 * - only orders in the proper day are returned
	 * - only proper order statuses are considered
	 * - orders are sorted correctly
	 */
	public function test_get_sorted_orders_in_day() {
		$sut           = get_container()->get( Orders::class );
		$orders_in_day = array_keys( array_slice( self::$sorted_orders, 0, 3, true ) );
		$first_order   = new \WC_Order( $orders_in_day[0] );

		$orders = $sut->get_sorted_orders_in_day( $first_order->get_date_created() );
		$this->assertEquals( 3, count( $orders ) );

		// Assert that $orders and $orders_in_day contain the same orders in the
		// same order. Probably should've used a different assertion.
		for ( $i = 0; $i < count( $orders_in_day ); ++$i ) {
			$this->assertEquals( $orders[ $i ]->get_id(), $orders_in_day[ $i ] );
		}
	}

	public function create_order( $props ) {
		$order = new \WC_Order();
		$order->set_props( $props );
		return $order->save();
	}

}
