import { navigateToShippingZone, waitForShippingZoneMethods } from './util'

describe( 'Admin settings', () => {
	describe( 'Shipping insurance', () => {
		it( 'Should show a setting to input a percentage in a flat rate shipping method.', async () => {
			await navigateToShippingZone();
			await expect(page).toClick('a', { text: /flat rate/i })
			await expect(page).toMatchElement( 'label', { text: /insurance percentage/i } )
		} )

		it( 'Should save insurance percentage correctly.', async () => {
			await navigateToShippingZone();
			await expect(page).toClick( 'a', { text: /flat rate/i } )
			const selector =  'input[name="woocommerce_flat_rate_ribarich_se_shipping_insurance"]'

			// Fill insurance percentage with some value
			let currentValue = Number( await page.$eval( selector, ( e ) => e.value ) )
			if ( ! currentValue ) {
				currentValue = 0;
			}
			const nextValue = ( Math.floor( currentValue ) + 1 ) % 50;
			await expect(page).toFill(selector, String( nextValue ) );

			// Save
			await expect(page).toClick( '.wc-backbone-modal #btn-ok' )
			// Wait for save
			await page.waitForTimeout( 1500 )

			await page.reload()

			await waitForShippingZoneMethods();

			await expect(page).toClick( 'a', { text: /flat rate/i } )
			const savedValue = Number( await page.$eval( selector, ( e ) => e.value ) )
			expect( savedValue ).toBe( nextValue )
		} )
	} )
} )
