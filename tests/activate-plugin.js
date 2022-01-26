const {
	activatePlugin,
	activateTheme,
} = require( '@wordpress/e2e-test-utils' );

const slug = 'ribarich-shop-extensions';

beforeAll( async () => {
	await activatePlugin( 'woocommerce' );
	await activateTheme( 'storefront' );
	await activatePlugin( slug );
} );
