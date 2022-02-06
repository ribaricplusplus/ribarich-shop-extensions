const { fromProjectRoot } = require( '@wordpress/scripts/utils' );

const getRootDir = () => {
	return fromProjectRoot( '/' );
};

module.exports = { getRootDir };
