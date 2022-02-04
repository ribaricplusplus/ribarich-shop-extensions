const baseConfig = require( '@wordpress/scripts/config/webpack.config.js' );

module.exports = {
	...baseConfig,
	entry: {
		cart: './js/cart/index.ts',
	},
};
