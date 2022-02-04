import domReady from '@wordpress/dom-ready';
import $ from 'jquery';

domReady( () => {
	document.addEventListener( 'change', ( e ) => {
		if (
			! ( e.target as HTMLElement ).closest(
				'#ribarich-se-input-shipping-insurance'
			)
		) {
			// Not a change of the shipping insurance input
			return;
		}
		updateCart();
	} );
} );

function updateCart() {
	const submitButton = $( 'button[name="update_cart"]' );
	enableButton();
	submitButton.trigger( 'click' );
	disableButton();
}

function enableButton() {
	$( '.woocommerce-cart-form :input[name="update_cart"]' )
		.prop( 'disabled', false )
		.attr( 'aria-disabled', false );
}

function disableButton() {
	$( '.woocommerce-cart-form :input[name="update_cart"]' )
		.prop( 'disabled', true )
		.attr( 'aria-disabled', true );
}
