<?php

use SkyVerge\WooCommerce\Facebook\Commerce;

/**
 * Tests the Commerce handler class.
 */
class CommerceTest extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;


	/** Test methods **************************************************************************************************/


	/** @see Commerce::get_default_google_product_category_id() */
	public function test_get_default_google_product_category_id() {

		update_option( Commerce::OPTION_GOOGLE_PRODUCT_CATEGORY_ID, 'default' );

		$this->assertSame( 'default', $this->get_commerce_handler()->get_default_google_product_category_id() );
	}


	/** @see Commerce::get_default_google_product_category_id() */
	public function test_get_default_google_product_category_id_filter() {

		add_filter( 'wc_facebook_commerce_default_google_product_category_id', static function() {

			return 'filtered';
		} );

		$this->assertSame( 'filtered', $this->get_commerce_handler()->get_default_google_product_category_id() );
	}


	/**
	 * @see Commerce::update_default_google_product_category_id()
	 *
	 * @param string $new_value new product category ID
	 * @param string $stored_value expected stored value
	 * @dataProvider provider_update_default_google_product_category_id
	 */
	public function test_update_default_google_product_category_id( $new_value, $stored_value ) {

		$this->get_commerce_handler()->update_default_google_product_category_id( $new_value );

		$this->assertSame( $stored_value, get_option( Commerce::OPTION_GOOGLE_PRODUCT_CATEGORY_ID ) );
	}


	/** @see test_update_default_google_product_category_id */
	public function provider_update_default_google_product_category_id() {

		return [
			[ 'category_id', 'category_id' ],
			[ '12',          '12' ],
			[ 12,            '' ],
			[ null,          '' ]
		];
	}


	/**
	 * @see Commerce::is_available()
	 *
	 * @param string $country_state store country / state
	 * @param bool $available whether commerce features should be available
	 * @dataProvider provider_is_available
	 */
	public function test_is_available( $country_state, $available ) {

		update_option( 'woocommerce_default_country', $country_state );

		$this->assertSame( $available, $this->get_commerce_handler()->is_available() );
	}


	/** @see test_is_available */
	public function provider_is_available() {

		return [
			[ 'UK',    false ],
			[ 'US',    true ],
			[ 'US:MA', true ],
			[ 'CA:QC', false ],
			[ '',      false ],
		];
	}


	/**
	 * @see Commerce::is_available()
	 *
	 * @param bool $filtered filtered value
	 * @dataProvider provider_is_available_filter
	 */
	public function test_is_available_filter( bool $filtered ) {

		update_option( 'woocommerce_default_country', 'US:MA' );

		add_filter( 'wc_facebook_commerce_is_available', static function() use ( $filtered ) {

			return $filtered;
		} );

		$this->assertSame( $filtered, $this->get_commerce_handler()->is_available() );
	}


	/** @see test_is_available_filter */
	public function provider_is_available_filter() {

		return [
			[ true ],
			[ false ],
		];
	}


	/**
	 * @see Commerce::is_connected()
	 *
	 * @dataProvider provider_is_connected
	 */
	public function test_is_connected( $access_token, $manager_id, $is_connected ) {

		facebook_for_woocommerce()->get_connection_handler()->update_page_access_token( $access_token );
		facebook_for_woocommerce()->get_connection_handler()->update_commerce_merchant_settings_id( $manager_id );
		facebook_for_woocommerce()->get_connection_handler()->update_onsite_checkout_connected( true );

		$this->assertSame( $is_connected, $this->get_commerce_handler()->is_connected() );
	}


	/** @see test_is_connected() */
	public function provider_is_connected() {

		return [
			[ '123456', '1', true ],
			[ '',       '',  false ],
			[ '',       '1', false ],
			[ '123456', '',  false ],
		];
	}


	/**
	 * @see Commerce::is_connected()
	 *
	 * @param bool $filtered filtered value
	 * @dataProvider provider_is_available_filter
	 */
	public function test_is_connected_filter( bool $filtered ) {

		add_filter( 'wc_facebook_commerce_is_connected', static function() use ( $filtered ) {

			return $filtered;
		} );

		$this->assertSame( $filtered, $this->get_commerce_handler()->is_connected() );
	}


	/** @see Commerce::get_orders_handler() */
	public function test_get_orders_handler() {

		$this->assertInstanceOf( Commerce\Orders::class, $this->get_commerce_handler()->get_orders_handler() );
	}


	/** Helper methods **************************************************************************************************/


	/**
	 * Gets the commerce handler instance.
	 *
	 * @return Commerce
	 */
	private function get_commerce_handler() {

		return new Commerce();
	}


}
