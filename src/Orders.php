<?php
declare(strict_types=1);

namespace Ribarich\SE;

defined( 'ABSPATH' ) || exit;

class Orders {

	/**
	 * @return \WC_Order[] Array of orders, sorted by date created, from oldest to newest.
	 */
	public function get_sorted_orders_in_day( \WC_DateTime $date ) {
		$orders = \wc_get_orders(
			array(
				'date_created' => $date->date_i18n( 'Y-m-d' ),
				'status'       => array( 'wc-processing', 'wc-completed', 'wc-refunded' ),
				'limit'        => -1,
				'orderby'      => 'date',
				'order'        => 'ASC',
			)
		);
		return $orders;
	}

}
