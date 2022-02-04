import { visitAdminPage } from '@wordpress/e2e-test-utils';

export function clickAndWait( selector, clickOptions = {}, waitOptions = {} ) {
	return Promise.all( [
		page.waitForNavigation( waitOptions ),
		page.click( selector, clickOptions ),
	] );
}

export async function navigateToShippingZone() {
	const testZoneName = 'ShippingInsuranceTestMethod';
	await visitAdminPage( 'admin.php', `page=wc-settings&tab=shipping` );
	const zone = await page.$x(
		`//td/a[contains(text(),"${ testZoneName }")]`
	);
	if ( ! zone || zone.length === 0 ) {
		await clickAndWait( '.wc-shipping-zones-heading > .page-title-action' );
		await page.type( '#zone_name', testZoneName );
		await Promise.all( [
			page.waitForSelector( '.wc-backbone-modal-header' ),
			page.click( '.wc-shipping-zone-add-method' ),
		] );
		await Promise.all( [
			page.waitForSelector(
				'.wc-shipping-zone-method-title > .wc-shipping-zone-method-settings'
			),
			page.click( '.wc-backbone-modal #btn-ok' ),
		] );
	} else {
		await Promise.all( [ zone[ 0 ].click(), page.waitForNavigation() ] );
		await waitForShippingZoneMethods();
	}
}

export async function waitForShippingZoneMethods() {
	await page.waitForSelector(
		'.wc-shipping-zone-method-title > .wc-shipping-zone-method-settings'
	);
}
