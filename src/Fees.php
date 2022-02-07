<?php
declare(strict_types=1);

namespace Ribarich\SE;

defined( 'ABSPATH' ) || exit;

class Fees {

	/** @var array See defaults in $this->get_default_fees(). */
	public $fees;

	public function init() {
		\add_action( 'woocommerce_cart_calculate_fees', array( $this, 'add_insurance_fee' ) );
		\add_action( 'woocommerce_load_cart_from_session', array( $this, 'load_fees_from_session' ) );
		\add_action( 'woocommerce_cart_emptied', array( $this, 'destroy_fees_session' ) );
		\add_action( 'woocommerce_cart_contents', array( $this, 'render_ui' ) );
		\add_action( 'woocommerce_update_cart_action_cart_updated', array( $this, 'cart_update' ) );
	}

	public function __construct(
		\WooCommerce $wc
	) {
		$this->wc       = $wc;
		$this->cart     = $this->wc->cart;
		$this->shipping = $this->wc->shipping();
	}

	public function destroy_fees_session() {
		$this->wc->session->set( 'ribarich_se_fees', null );
	}

	/**
	 * Calculates shipping insurance from cart and shipping information.
	 *
	 * @throws \Exception
	 */
	public function calculate_shipping_insurance() {
		$packages = $this->shipping->get_packages();
		$total    = 0;

		foreach ( $packages as $package ) {
			if ( empty( $package['rates'] ) ) {
				continue;
			}

			$rate = current( $package['rates'] );

			if ( ! is_a( $rate, 'WC_Shipping_Rate' ) ) {
				throw new \Exception( 'Rate not found.' );
			}

			if ( ! $rate->get_instance_id() ) {
				continue;
			}

			$method = \WC_Shipping_Zones::get_shipping_method( $rate->get_instance_id() );
			if ( ! is_a( $method, 'WC_Shipping_Method' ) ) {
				continue;
			}

			$shipping_insurance_percentage = (int) $method->get_option( 'ribarich_se_shipping_insurance' );

			if ( ! $shipping_insurance_percentage ) {
				continue;
			}

			$raw_percentage = $shipping_insurance_percentage / 100;
			$shipping_cost  = array_sum(
				array_map(
					function( $rate ) {
						return $rate->get_cost();
					},
					$package['rates']
				)
			);
			$insurance      = ( $package['contents_cost'] + $shipping_cost ) * $raw_percentage;
			$total         += $insurance;
		}

		return $total;
	}

	/**
	 * @throws \Exception
	 */
	public function get_chosen_shipping_method() {
		$packages = $this->shipping->get_packages();
		if ( empty( $packages ) ) {
			return false;
		}
		$package = current( $packages );

		$rate = current( $package['rates'] );
		if ( ! is_a( $rate, 'WC_Shipping_Rate' ) ) {
			return false;
		}

		if ( ! $rate->get_instance_id() ) {
			return false;
		}

		$method = \WC_Shipping_Zones::get_shipping_method( $rate->get_instance_id() );

		if ( ! is_a( $method, 'WC_Shipping_Method' ) ) {
			throw new \Exception( 'Shipping method not found.' );
		}

		return $method;
	}

	public function get_default_fees() {
		return array(
			'shipping_insurance' => array(
				'enabled' => true,
			),
		);
	}

	public function load_fees_from_session() {
		if ( ! $this->wc->session ) {
			return;
		}

		$this->set_fees( $this->wc->session->get( 'ribarich_se_fees', $this->get_default_fees() ) );
	}

	public function set_fees( $fees ) {
		$this->fees = $fees;
	}

	public function cart_update() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( empty( $_REQUEST['shipping_insurance'] ) ) {
			$this->fees['shipping_insurance']['enabled'] = false;
		} else {
			$this->fees['shipping_insurance']['enabled'] = true;
		}

		$this->wc->session->set( 'ribarich_se_fees', $this->fees );
	}

	public function add_insurance_fee() {
		if ( \is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		if ( ! $this->is_fee_enabled( 'shipping_insurance' ) ) {
			return;
		}

		try {
			$fee = $this->calculate_shipping_insurance();

			$shipping_method = $this->get_chosen_shipping_method();

			if ( ! $fee ) {
				return;
			}

			$taxable = (bool) $shipping_method->get_option( 'ribarich_se_shipping_insurance_apply_taxes' );

			$this->cart->add_fee( $this->get_fee_name( 'shipping_insurance' ), $fee, $taxable );
		} catch ( \Exception $e ) {
			handle_exception( $e );
			return;
		}
	}

	/**
	 * WooCommerce uses fee name to identify a fee... Must make sure it's
	 * consistent everywhere.
	 *
	 * Valid $fee_id is anything that is the a of the $this->fees array.
	 *
	 * @throws \Exception
	 */
	public function get_fee_name( string $fee_id ) {

		switch ( $fee_id ) {
			case 'shipping_insurance':
				return __( 'Shipping insurance', 'ribarich_se' );
		}

		throw new \Exception( "Invalid fee id $fee_id" );
	}

	public function render_ui() {
		$template = '
<tr>
	<td class="ribarich-se-fees" colspan="6">
		<div class="ribarich-se-fee shipping-insurance">
			<div class="ribarich-se-fee__input">
				<input id="ribarich-se-input-shipping-insurance" type="checkbox" name="shipping_insurance" value="1" %2$s/>
				<label for="ribarich-se-input-shipping-insurance">%1$s</label>
			</div>
			<p class="ribarich-se-fee__info">
				%3$s
			</p>
		</div>
	</td>
</tr>
';

		$fee_info = \apply_filters(
			'ribarich_se_fee_info',
			array(
				'shipping_insurance' => __( 'Shipping insurance', 'ribarich_se' ),
			)
		);

		printf(
			$template,
			__( 'Shipping insurance', 'ribarich_se' ),
			$this->is_fee_enabled( 'shipping_insurance' ) ? 'checked' : '',
			\wp_kses_post( $fee_info['shipping_insurance'] )
		);
	}

	/**
	 * @throws \Exception
	 * @param string $fee
	 * @return boolean
	 */
	public function is_fee_enabled( $fee ) {
		if ( ! isset( $this->fees[ $fee ] ) ) {
			throw new \Exception( 'Invalid fee.' );
		}

		return $this->fees[ $fee ]['enabled'];
	}

}
