<?php

use SkyVerge\WooCommerce\Facebook;
use SkyVerge\WooCommerce\Facebook\Product_Categories;
use SkyVerge\WooCommerce\Facebook\Products;
use SkyVerge\WooCommerce\PluginFramework\v5_10_0\SV_WC_Plugin_Exception;

/**
 * Tests the Products class.
 */
class Products_Test extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;

	/** @var int excluded product category ID */
	private $excluded_category;


	/**
	 * Runs before each test.
	 */
	protected function _before() {

		$this->add_excluded_category();
	}


	/**
	 * Runs after each test.
	 */
	protected function _after() {

	}


	/** Test methods **************************************************************************************************/


	/** @see Facebook\Products::product_should_be_synced() */
	public function test_product_should_be_synced_simple() {

		// used the tester's method directly to set regular_price to 0
		$product = $this->tester->get_product( [
			'status'        => 'publish',
			'regular_price' => 0,
		] );

		$this->assertTrue( Facebook\Products::product_should_be_synced( $product ) );
	}

	/** @see Facebook\Products::product_should_be_synced() */
	public function test_product_should_be_synced_variation() {

		// used the tester's method directly to set regular_price to 0
		$product = $this->tester->get_variable_product( [
			'status'        => 'publish',
			'regular_price' => 0,
		] );

		foreach ( $product->get_children() as $child_id ) {
			$this->assertTrue( Facebook\Products::product_should_be_synced( wc_get_product( $child_id ) ) );
		}
	}


	/**
	 * Tests that product excluded from the store catalog or from search results should not be synced.
	 *
	 * @see Facebook\Products::product_should_be_synced()
	 *
	 * @param string $term product_visibility term
	 *
	 * @dataProvider provider_product_should_be_synced_with_excluded_products
	 */
	public function test_product_should_be_synced_with_excluded_products( $term ) {

		$product = $this->get_product();

		wp_set_object_terms( $product->get_id(), $term, 'product_visibility' );

		$this->assertFalse( Facebook\Products::product_should_be_synced( $product ) );
	}


	/** @see test_product_should_be_synced_with_excluded_products() */
	public function provider_product_should_be_synced_with_excluded_products() {

		return [
			[ 'exclude-from-catalog' ],
			[ 'exclude-from-search' ],
		];
	}


	/** @see Facebook\Products::product_should_be_synced() */
	public function test_product_should_be_synced_simple_in_excluded_category() {

		$product = $this->get_product();
		$product->set_category_ids( [ $this->excluded_category ] );

		$this->assertFalse( Facebook\Products::product_should_be_synced( $product ) );
	}


	/** @see Facebook\Products::product_should_be_synced() */
	public function test_product_should_be_synced_variation_in_excluded_category() {

		$product = $this->get_variable_product();
		$product->set_category_ids( [ $this->excluded_category ] );
		$product->save();

		foreach ( $product->get_children() as $child_id ) {
			$this->assertFalse( Facebook\Products::product_should_be_synced( wc_get_product( $child_id ) ) );
		}
	}


	/** @see Facebook\Products::enable_sync_for_products() */
	public function test_enable_sync_for_products() {

		$product = $this->get_product();

		Facebook\Products::disable_sync_for_products( [ $product ] );
		Facebook\Products::enable_sync_for_products( [ $product ] );

		// get a fresh product object to ensure the status is stored
		$product = wc_get_product( $product->get_id() );

		$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( $product ) );
	}


	/** @see Facebook\Products::enable_sync_for_products() for variable products  */
	public function test_enable_sync_for_products_variable() {

		$variable_product = $this->get_variable_product();

		Facebook\Products::disable_sync_for_products( [ $variable_product ] );
		Facebook\Products::enable_sync_for_products( [ $variable_product ] );

		// get a fresh product object to ensure the status is stored
		$variable_product = wc_get_product( $variable_product->get_id() );

		$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( $variable_product ) );
	}


	/** @see Facebook\Products::enable_sync_for_products() for variations  */
	public function test_enable_sync_for_products_variation() {

		$variable_product = $this->get_variable_product();

		Facebook\Products::disable_sync_for_products( [ $variable_product ] );
		Facebook\Products::enable_sync_for_products( [ $variable_product ] );

		// get a fresh product object to ensure the status is stored
		$variable_product = wc_get_product( $variable_product->get_id() );

		foreach ( $variable_product->get_children() as $child_product_id ) {
			$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( wc_get_product( $child_product_id ) ) );
		}
	}


	/** @see Facebook\Products::disable_sync_for_products() */
	public function test_disable_sync_for_products() {

		$product = $this->get_product();

		Facebook\Products::enable_sync_for_products( [ $product ] );
		Facebook\Products::disable_sync_for_products( [ $product ] );

		// get a fresh product object to ensure the status is stored
		$product = wc_get_product( $product->get_id() );

		$this->assertFalse( Facebook\Products::is_sync_enabled_for_product( $product ) );
	}


	/** @see Facebook\Products::disable_sync_for_products() for variable products */
	public function test_disable_sync_for_products_variable() {

		$variable_product = $this->get_variable_product();

		Facebook\Products::enable_sync_for_products( [ $variable_product ] );
		Facebook\Products::disable_sync_for_products( [ $variable_product ] );

		// get a fresh product object to ensure the status is stored
		$variable_product = wc_get_product( $variable_product->get_id() );

		$this->assertFalse( Facebook\Products::is_sync_enabled_for_product( $variable_product ) );
	}


	/** @see Facebook\Products::disable_sync_for_products() for variations */
	public function test_disable_sync_for_products_variation() {

		$variable_product = $this->get_variable_product();

		Facebook\Products::enable_sync_for_products( [ $variable_product ] );
		Facebook\Products::disable_sync_for_products( [ $variable_product ] );

		// get a fresh product object to ensure the status is stored
		$variable_product = wc_get_product( $variable_product->get_id() );

		foreach ( $variable_product->get_children() as $child_product_id ) {
			$this->assertFalse( Facebook\Products::is_sync_enabled_for_product( wc_get_product( $child_product_id ) ) );
		}
	}


	/**
	 * @see Facebook\Products::disable_sync_for_products_with_terms()
	 *
	 * @param int $term_id the ID of the term to look for
	 * @param string $taxonomy the name of the taxonomy to look for
	 * @param bool $set_term whether to add the term to the test product
	 * @param bool $is_synced_enabled whether sync should be enabled for the product or not
	 *
	 * @dataProvider provider_disable_sync_for_products_with_terms
	 */
	public function test_disable_sync_for_products_with_terms( $term_id, $taxonomy, $set_term, $is_sync_enabled ) {

		$product = $this->get_product();

		if ( $set_term ) {
			wp_set_object_terms( $product->get_id(), $term_id, $taxonomy );
		}

		Facebook\Products::enable_sync_for_products( [ $product ] );
		Facebook\Products::disable_sync_for_products_with_terms( [ 'taxonomy' => $taxonomy, 'include' => [ $term_id ] ] );

		// get a fresh product object to ensure the status is stored
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( $is_sync_enabled, Facebook\Products::is_sync_enabled_for_product( $product ) );
	}


	public function provider_disable_sync_for_products_with_terms() {

		$category = wp_insert_term( 'product_cat_test', 'product_cat' );
		$tag      = wp_insert_term( 'product_tag_test', 'product_tag' );

		return [
			// the product has the term
			[ $category['term_id'], 'product_cat', true, false ],
			[ $tag['term_id'],      'product_tag', true, false ],

			// the product does not have the term
			[ $category['term_id'], 'product_cat', false, true ],
			[ $tag['term_id'],      'product_tag', false, true ],
		];
	}


	/** @see Facebook\Products::is_sync_enabled_for_product() for products that don't have a preference set */
	public function test_is_sync_enabled_for_product_defaults() {

		$product = $this->get_product();

		$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( $product ) );

		$variable_product = $this->get_variable_product();

		$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( $this->get_variable_product() ) );

		foreach ( $variable_product->get_children() as $child_product_id ) {
			$this->assertTrue( Facebook\Products::is_sync_enabled_for_product( wc_get_product( $child_product_id ) ) );
		}
	}


	/** @see \SkyVerge\WooCommerce\Facebook\Products::set_product_visibility() */
	public function test_set_product_visibility() {

		$product = $this->get_product();

		$visibility = $product->get_meta( Facebook\Products::VISIBILITY_META_KEY );

		$this->assertEmpty( $visibility );

		Facebook\Products::set_product_visibility( $product, true );

		$visibility = $product->get_meta( Facebook\Products::VISIBILITY_META_KEY );

		$this->assertEquals( 'yes', $visibility );

		Facebook\Products::set_product_visibility( $product, false );

		$visibility = $product->get_meta( Facebook\Products::VISIBILITY_META_KEY );

		$this->assertEquals( 'no', $visibility );
	}


	/** @see \SkyVerge\WooCommerce\Facebook\Products::is_product_visible() */
	public function test_is_product_visible() {

		$product = $this->get_product();

		Facebook\Products::set_product_visibility( $product, false );

		$this->assertFalse( Facebook\Products::is_product_visible( $product ) );

		Facebook\Products::set_product_visibility( $product, true );

		$this->assertTrue( Facebook\Products::is_product_visible( $product ) );
	}


	/** @see Facebook\Products::get_product_price() */
	public function test_get_product_price_filter() {

		$product = $this->get_product();

		add_filter( 'wc_facebook_product_price', static function() {
			return 1234;
		} );

		$this->assertSame( 1234, Facebook\Products::get_product_price( $product ) );
	}


	/** @see Products::get_google_product_category_id() */
	public function test_get_google_product_category_id_simple_product() {

		$product = $this->get_product();
		Products::update_google_product_category_id( $product, '1' );

		$this->assertEquals( '1', Products::get_google_product_category_id( $product ) );
	}


	/** @see Products::get_google_product_category_id() */
	public function test_get_google_product_category_id_product_variation() {

		$variable_product = $this->get_variable_product( [ 'children' => 2 ] );
		Products::update_google_product_category_id( $variable_product, '2' );
		$variable_product->save();
		$variable_product = wc_get_product( $variable_product->get_id() );

		foreach ( $variable_product->get_children() as $child_product_id ) {

			$product_variation = wc_get_product( $child_product_id );
			$this->assertEquals( '2', Products::get_google_product_category_id( $product_variation ) );
		}
	}


	/** @see Products::get_google_product_category_id() */
	public function test_get_google_product_category_id_product_single_category() {

		$product         = $this->get_product();
		$parent_category = wp_insert_term( 'Animals & Pet Supplies', 'product_cat' );
		Product_Categories::update_google_product_category_id( $parent_category['term_id'], '3' );
		wp_set_post_terms( $product->get_id(), [ $parent_category['term_id'] ], 'product_cat' );

		$this->assertEquals( '3', Products::get_google_product_category_id( $product ) );
	}


	/** @see Products::get_google_product_category_id() */
	public function test_get_google_product_category_id_product_multiple_categories() {

		$product         = $this->get_product();
		$parent_category = wp_insert_term( 'Animals & Pet Supplies', 'product_cat' );
		Product_Categories::update_google_product_category_id( $parent_category['term_id'], '4' );
		$child_category = wp_insert_term( 'Pet Supplies', 'product_cat', [ 'parent' => $parent_category['term_id'] ] );
		Product_Categories::update_google_product_category_id( $child_category['term_id'], '5' );
		wp_set_post_terms( $product->get_id(), [
			$parent_category['term_id'],
			$child_category['term_id'],
		], 'product_cat' );

		$this->assertEquals( '5', Products::get_google_product_category_id( $product ) );
	}


	/** @see Products::get_google_product_category_id() */
	public function test_get_google_product_category_id_product_conflicting_categories() {

		$product         = $this->get_product();
		$parent_category = wp_insert_term( 'Animals & Pet Supplies', 'product_cat' );
		Product_Categories::update_google_product_category_id( $parent_category['term_id'], '5' );
		$child_category_1 = wp_insert_term( 'Cat Supplies', 'product_cat', [ 'parent' => $parent_category['term_id'] ] );
		Product_Categories::update_google_product_category_id( $child_category_1['term_id'], '6' );
		$child_category_2 = wp_insert_term( 'Dog Supplies', 'product_cat', [ 'parent' => $parent_category['term_id'] ] );
		Product_Categories::update_google_product_category_id( $child_category_2['term_id'], '7' );
		wp_set_post_terms( $product->get_id(), [
			$parent_category['term_id'],
			$child_category_1['term_id'],
			$child_category_2['term_id'],
		], 'product_cat' );

		$this->assertEquals( '', Products::get_google_product_category_id( $product ) );
	}


	/** @see Products::get_google_product_category_id() */
	public function test_get_google_product_category_id_product_variation_multiple_categories() {

		$variable_product = $this->get_variable_product( [ 'children' => 2 ] );

		$parent_category = wp_insert_term( 'Animals & Pet Supplies', 'product_cat' );
		Product_Categories::update_google_product_category_id( $parent_category['term_id'], '8' );
		$child_category = wp_insert_term( 'Pet Supplies', 'product_cat', [ 'parent' => $parent_category['term_id'] ] );
		Product_Categories::update_google_product_category_id( $child_category['term_id'], '9' );

		wp_set_post_terms( $variable_product->get_id(), [
			$parent_category['term_id'],
			$child_category['term_id'],
		], 'product_cat' );

		foreach ( $variable_product->get_children() as $child_product_id ) {

			$product_variation = wc_get_product( $child_product_id );
			$this->assertEquals( '9', Products::get_google_product_category_id( $product_variation ) );
		}
	}


	/** @see Products::get_google_product_category_id() */
	public function test_get_google_product_category_id_default() {

		$product = $this->get_product();
		facebook_for_woocommerce()->get_commerce_handler()->update_default_google_product_category_id( '10' );

		$this->assertEquals( '10', Products::get_google_product_category_id( $product ) );
	}


	/**
	 * Tests that {@see Products::get_google_product_category_id_from_highest_category()} can
	 * handle null or WP_Error return values from {@see get_term()}
	 *
	 * The steps below try to reproduce the scenario described in https://secure.helpscout.net/conversation/1369552988/155780/
	 */
	public function test_get_google_product_category_id_from_highest_category() {
		global $wpdb;

		$product = $this->get_product();

		$parent_category = wp_insert_term( 'Animals & Pet Supplies', 'product_cat' );
		$child_category  = wp_insert_term( 'Pet Supplies', 'product_cat', [ 'parent' => $parent_category['term_id'] ] );

		// set the Google Product Category for the child category
		Product_Categories::update_google_product_category_id( $child_category['term_id'], '9' );

		// assign the child category to the product
		wp_set_post_terms( $product->get_id(), [ (int) $child_category['term_id'] ], 'product_cat' );

		// remove the parent category from the database to force get_term() to return null
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->term_taxonomy} WHERE term_id = %d AND taxonomy = %s", $parent_category['term_id'], 'product_cat' ) );
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->terms} WHERE term_id = %d", $parent_category['term_id'] ) );

		// make sure get_term() checks the database again
		clean_term_cache( $parent_category['term_id'], 'product_cat' );

		$method = new ReflectionMethod( Products::class, 'get_google_product_category_id_from_highest_category' );
		$method->setAccessible( true );

		$this->assertSame( '9', $method->invoke( null, $product ) );
	}


	/**
	 * @see \SkyVerge\WooCommerce\Facebook\Products::update_google_product_category_id()
	 *
	 * @param string $google_product_category_id Google product category ID
	 *
	 * @dataProvider provider_update_google_product_category_id
	 */
	public function test_update_google_product_category_id( $google_product_category_id ) {

		$product = $this->get_product();

		Products::update_google_product_category_id( $product, $google_product_category_id );

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertEquals( $google_product_category_id, $product->get_meta( Products::GOOGLE_PRODUCT_CATEGORY_META_KEY ) );
	}


	/** @see test_update_google_product_category_id */
	public function provider_update_google_product_category_id() {

		return [
			[ '3350' ],
			[ '' ],
		];
	}


	/**
	 * @see Products::get_product_gender
	 *
	 * @param string $meta_value meta value
	 * @param string $expected_result expected result
	 *
	 * @dataProvider provider_get_product_gender
	 */
	public function test_get_product_gender( $meta_value, $expected_result ) {

		$product = $this->get_product();
		if ( null === $meta_value ) {
			$product->delete_meta_data( Products::GENDER_META_KEY );
		} else {
			$product->update_meta_data( Products::GENDER_META_KEY, $meta_value );
		}

		$this->assertSame( $expected_result, Products::get_product_gender( $product ) );
	}


	/** @see test_get_product_gender */
	public function provider_get_product_gender() {

		return [
			[ null, 'unisex' ],
			[ 'female', 'female' ],
			[ 'male', 'male' ],
			[ 'unisex', 'unisex' ],
			[ '', 'unisex' ],
			[ 'invalid', 'unisex' ],
		];
	}


	/**
	 * @see Products::update_product_gender()
	 *
	 * @param string $gender gender
	 *
	 * @dataProvider provider_update_product_gender
	 */
	public function test_update_product_gender( $gender ) {

		$product = $this->get_product();

		Products::update_product_gender( $product, $gender );

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertEquals( $gender, $product->get_meta( Products::GENDER_META_KEY ) );
	}


	/** @see test_update_product_gender */
	public function provider_update_product_gender() {

		return [
			[ 'female' ],
			[ 'male' ],
			[ 'unisex' ],
			[ '' ],
		];
	}


	/** @see Facebook\Products::get_product_color_attribute() */
	public function test_get_product_color_attribute_configured_valid() {

		$color_attribute = $this->tester->create_color_attribute();

		$product = $this->get_product( [ 'attributes' => [ $color_attribute ] ] );
		$product->update_meta_data( Products::COLOR_ATTRIBUTE_META_KEY, $color_attribute->get_name() );
		$product->save_meta_data();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( $color_attribute->get_name(), Products::get_product_color_attribute( $product ) );
	}


	/** @see Facebook\Products::get_product_color_attribute() */
	public function test_get_product_color_attribute_configured_invalid() {

		$color_attribute = $this->tester->create_color_attribute();

		// create the product without attributes
		$product = $this->get_product();
		$product->update_meta_data( Products::COLOR_ATTRIBUTE_META_KEY, $color_attribute->get_name() );
		$product->save_meta_data();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( '', Products::get_product_color_attribute( $product ) );
	}


	/** @see Facebook\Products::get_product_color_attribute() */
	public function test_get_product_color_attribute_string_matching() {

		$color_attribute = $this->tester->create_color_attribute( 'product colour' );

		$product = $this->get_product( [ 'attributes' => [ $color_attribute ] ] );

		$this->assertSame( 'product-colour', Products::get_product_color_attribute( $product ) );
	}


	/** @see Facebook\Products::get_product_color_attribute() */
	public function test_get_product_color_attribute_variation() {

		$color_attribute = $this->tester->create_color_attribute( 'color', [ 'pink', 'blue' ], true );

		$product = $this->get_variable_product();
		$product->set_attributes( [ $color_attribute ] );
		$product->update_meta_data( Products::COLOR_ATTRIBUTE_META_KEY, $color_attribute->get_name() );
		$product->save();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		foreach ( $product->get_children() as $child_id ) {

			$product_variation = wc_get_product( $child_id );
			$this->assertSame( $color_attribute->get_name(), Products::get_product_color_attribute( $product_variation ) );
		}
	}


	/** @see Facebook\Products::update_product_color_attribute() */
	public function test_update_product_color_attribute_valid() {

		$color_attribute = $this->tester->create_color_attribute();

		$product = $this->get_product( [ 'attributes' => [ $color_attribute ] ] );

		Products::update_product_color_attribute( $product, $color_attribute->get_name() );

		// get a fresh product object to ensure the meta is stored
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( $color_attribute->get_name(), $product->get_meta( Products::COLOR_ATTRIBUTE_META_KEY ) );
	}


	/** @see Facebook\Products::update_product_color_attribute() */
	public function test_update_product_color_attribute_invalid() {

		$color_attribute = $this->tester->create_color_attribute();

		$product = $this->get_product( [ 'attributes' => [ $color_attribute ] ] );

		$this->expectException( SV_WC_Plugin_Exception::class );

		Products::update_product_color_attribute( $product, 'colour' );

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( '', $product->get_meta( Products::COLOR_ATTRIBUTE_META_KEY ) );
	}


	/** @see Facebook\Products::update_product_color_attribute() */
	public function test_update_product_color_attribute_already_used() {

		$color_attribute = $this->tester->create_color_attribute();
		$size_attribute  = $this->tester->create_size_attribute();

		$product = $this->get_product( [ 'attributes' => [ $color_attribute, $size_attribute ] ] );
		$product->update_meta_data( Products::COLOR_ATTRIBUTE_META_KEY, $color_attribute->get_name() );
		$product->update_meta_data( Products::SIZE_ATTRIBUTE_META_KEY, $size_attribute->get_name() );
		$product->save_meta_data();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->expectException( SV_WC_Plugin_Exception::class );

		Products::update_product_color_attribute( $product, $size_attribute->get_name() );

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( '', $product->get_meta( Products::COLOR_ATTRIBUTE_META_KEY ) );
	}


	/** @see Facebook\Products::get_product_color() */
	public function test_get_product_color_simple_product_single_value() {

		$color_attribute = $this->tester->create_color_attribute( 'color', [ 'pink' ] );

		$product = $this->get_product( [ 'attributes' => [ $color_attribute ] ] );
		$product->update_meta_data( Products::COLOR_ATTRIBUTE_META_KEY, $color_attribute->get_name() );
		$product->save();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( 'pink', Products::get_product_color( $product ) );
	}


	/** @see Facebook\Products::get_product_color() */
	public function test_get_product_color_variation_with_attribute_set() {

		$color_attribute = $this->tester->create_color_attribute( 'color', [ 'pink', 'blue' ], true );

		$product = $this->get_variable_product();
		$product->set_attributes( [ $color_attribute ] );
		$product->update_meta_data( Products::COLOR_ATTRIBUTE_META_KEY, $color_attribute->get_name() );
		$product->save();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		foreach ( $product->get_children() as $child_id ) {

			$product_variation = wc_get_product( $child_id );

			/**
			 * Unlike the parent product which uses terms, variations are assigned specific attributes using name value pairs.
			 * @see WC_Product_Variation::set_attributes()
			 */
			$product_variation->set_attributes( [ 'color' => 'pink' ] );
			$product_variation->update_meta_data( Products::COLOR_ATTRIBUTE_META_KEY, $color_attribute->get_name() );
			$product_variation->save();

			// get a fresh product object
			$product_variation = wc_get_product( $child_id );

			$this->assertSame( 'pink', Products::get_product_color( $product_variation ) );
		}
	}


	/** @see Facebook\Products::get_product_color() */
	public function test_get_product_color_variation_without_attribute_set() {

		$color_attribute = $this->tester->create_color_attribute( 'color', [ 'pink', 'blue' ], true );

		$product = $this->get_variable_product();
		$product->set_attributes( [ $color_attribute ] );
		$product->update_meta_data( Products::COLOR_ATTRIBUTE_META_KEY, $color_attribute->get_name() );
		$product->save();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		foreach ( $product->get_children() as $child_id ) {

			$product_variation = wc_get_product( $child_id );
			$this->assertSame( 'pink | blue', Products::get_product_color( $product_variation ) );
		}
	}


	/** @see Facebook\Products::get_product_size_attribute() */
	public function test_get_product_size_attribute_configured_valid() {

		$size_attribute = $this->tester->create_size_attribute();

		$product = $this->get_product( [ 'attributes' => [ $size_attribute ] ] );
		$product->update_meta_data( Products::SIZE_ATTRIBUTE_META_KEY, $size_attribute->get_name() );
		$product->save_meta_data();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( $size_attribute->get_name(), Products::get_product_size_attribute( $product ) );
	}


	/** @see Facebook\Products::get_product_size_attribute() */
	public function test_get_product_size_attribute_configured_invalid() {

		$size_attribute = $this->tester->create_size_attribute();

		// create the product without attributes
		$product = $this->get_product();
		$product->update_meta_data( Products::SIZE_ATTRIBUTE_META_KEY, $size_attribute->get_name() );
		$product->save_meta_data();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( '', Products::get_product_size_attribute( $product ) );
	}


	/** @see Facebook\Products::get_product_size_attribute() */
	public function test_get_product_size_attribute_string_matching() {

		$size_attribute = $this->tester->create_size_attribute( 'product size' );

		$product = $this->get_product( [ 'attributes' => [ $size_attribute ] ] );

		$this->assertSame( 'product-size', Products::get_product_size_attribute( $product ) );
	}


	/** @see Facebook\Products::get_product_size_attribute() */
	public function test_get_product_size_attribute_variation() {

		$size_attribute = $this->tester->create_size_attribute( 'size', [ 'small', 'medium', 'large' ], true );

		$product = $this->get_variable_product();
		$product->set_attributes( [ $size_attribute ] );
		$product->update_meta_data( Products::SIZE_ATTRIBUTE_META_KEY, $size_attribute->get_name() );
		$product->save();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		foreach ( $product->get_children() as $child_id ) {

			$product_variation = wc_get_product( $child_id );
			$this->assertSame( $size_attribute->get_name(), Products::get_product_size_attribute( $product_variation ) );
		}
	}


	/** @see Facebook\Products::update_product_size_attribute() */
	public function test_update_product_size_attribute_valid() {

		$size_attribute = $this->tester->create_size_attribute();

		$product = $this->get_product( [ 'attributes' => [ $size_attribute ] ] );

		Products::update_product_size_attribute( $product, $size_attribute->get_name() );

		// get a fresh product object to ensure the meta is stored
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( $size_attribute->get_name(), $product->get_meta( Products::SIZE_ATTRIBUTE_META_KEY ) );
	}


	/** @see Facebook\Products::update_product_size_attribute() */
	public function test_update_product_size_attribute_invalid() {

		$size_attribute = $this->tester->create_size_attribute();

		$product = $this->get_product( [ 'attributes' => [ $size_attribute ] ] );

		$this->expectException( SV_WC_Plugin_Exception::class );

		Products::update_product_size_attribute( $product, 'height' );

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( '', $product->get_meta( Products::SIZE_ATTRIBUTE_META_KEY ) );
	}


	/** @see Facebook\Products::update_product_size_attribute() */
	public function test_update_product_size_attribute_already_used() {

		$color_attribute = $this->tester->create_color_attribute();
		$size_attribute  = $this->tester->create_size_attribute();

		$product = $this->get_product( [ 'attributes' => [ $color_attribute, $size_attribute ] ] );
		$product->update_meta_data( Products::COLOR_ATTRIBUTE_META_KEY, $color_attribute->get_name() );
		$product->update_meta_data( Products::SIZE_ATTRIBUTE_META_KEY, $size_attribute->get_name() );
		$product->save_meta_data();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->expectException( SV_WC_Plugin_Exception::class );

		Products::update_product_size_attribute( $product, $color_attribute->get_name() );
	}


	/** @see Facebook\Products::get_product_size() */
	public function test_get_product_size_simple_product_single_value() {

		$size_attribute = $this->tester->create_size_attribute( 'size', [ 'small' ] );

		$product = $this->get_product( [ 'attributes' => [ $size_attribute ] ] );
		$product->update_meta_data( Products::SIZE_ATTRIBUTE_META_KEY, $size_attribute->get_name() );
		$product->save();

		// get a fresh product object
        $product = wc_get_product( $product->get_id() );

		$this->assertSame( 'small', Products::get_product_size( $product ) );
	}


	/** @see Facebook\Products::get_product_size() */
	public function test_get_product_size_variation_with_attribute_set() {

		$size_attribute = $this->tester->create_size_attribute( 'size', [ 'small', 'medium', 'large' ], true );

		$product = $this->get_variable_product();
		$product->set_attributes( [ $size_attribute ] );
		$product->update_meta_data( Products::SIZE_ATTRIBUTE_META_KEY, $size_attribute->get_name() );
		$product->save();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		foreach ( $product->get_children() as $child_id ) {

			$product_variation = wc_get_product( $child_id );

			/**
			 * Unlike the parent product which uses terms, variations are assigned specific attributes using name value pairs.
			 * @see WC_Product_Variation::set_attributes()
			 */
			$product_variation->set_attributes( [ 'size' => 'small' ] );
			$product_variation->update_meta_data( Products::SIZE_ATTRIBUTE_META_KEY, $size_attribute->get_name() );
			$product_variation->save();

			// get a fresh product object
			$product_variation = wc_get_product( $child_id );

			$this->assertSame( 'small', Products::get_product_size( $product_variation ) );
		}
	}


	/** @see Facebook\Products::get_product_size() */
	public function test_get_product_size_variation_without_attribute_set() {

		$size_attribute = $this->tester->create_size_attribute( 'size', [ 'small', 'medium', 'large' ], true );

		$product = $this->get_variable_product();
		$product->set_attributes( [ $size_attribute ] );
		$product->update_meta_data( Products::SIZE_ATTRIBUTE_META_KEY, $size_attribute->get_name() );
		$product->save();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		foreach ( $product->get_children() as $child_id ) {

			$product_variation = wc_get_product( $child_id );
			$this->assertSame( 'small | medium | large', Products::get_product_size( $product_variation ) );
		}
	}


	/** @see Facebook\Products::get_product_pattern_attribute() */
	public function test_get_product_pattern_attribute_configured_valid() {

		$pattern_attribute = $this->tester->create_pattern_attribute();

		$product = $this->get_product( [ 'attributes' => [ $pattern_attribute ] ] );
		$product->update_meta_data( Products::PATTERN_ATTRIBUTE_META_KEY, $pattern_attribute->get_name() );
		$product->save_meta_data();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( $pattern_attribute->get_name(), Products::get_product_pattern_attribute( $product ) );
	}


	/** @see Facebook\Products::get_product_pattern_attribute() */
	public function test_get_product_pattern_attribute_configured_invalid() {

		$pattern_attribute = $this->tester->create_pattern_attribute();

		// create the product without attributes
		$product = $this->get_product();
		$product->update_meta_data( Products::PATTERN_ATTRIBUTE_META_KEY, $pattern_attribute->get_name() );
		$product->save_meta_data();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( '', Products::get_product_pattern_attribute( $product ) );
	}


	/** @see Facebook\Products::get_product_pattern_attribute() */
	public function test_get_product_pattern_attribute_string_matching() {

		$pattern_attribute = $this->tester->create_pattern_attribute( 'product pattern' );

		$product = $this->get_product( [ 'attributes' => [ $pattern_attribute ] ] );

		$this->assertSame( 'product-pattern', Products::get_product_pattern_attribute( $product ) );
	}


	/** @see Facebook\Products::get_product_pattern_attribute() */
	public function test_get_product_pattern_attribute_variation() {

		$pattern_attribute = $this->tester->create_pattern_attribute( 'pattern', [ 'checked', 'floral', 'leopard' ], true );

		$product = $this->get_variable_product();
		$product->set_attributes( [ $pattern_attribute ] );
		$product->update_meta_data( Products::PATTERN_ATTRIBUTE_META_KEY, $pattern_attribute->get_name() );
		$product->save();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		foreach ( $product->get_children() as $child_id ) {

			$product_variation = wc_get_product( $child_id );
			$this->assertSame( $pattern_attribute->get_name(), Products::get_product_pattern_attribute( $product_variation ) );
		}
	}


	/** @see Facebook\Products::update_product_pattern_attribute() */
	public function test_update_product_pattern_attribute_valid() {

		$pattern_attribute = $this->tester->create_pattern_attribute();

		$product = $this->get_product( [ 'attributes' => [ $pattern_attribute ] ] );

		Products::update_product_pattern_attribute( $product, $pattern_attribute->get_name() );

		// get a fresh product object to ensure the meta is stored
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( $pattern_attribute->get_name(), $product->get_meta( Products::PATTERN_ATTRIBUTE_META_KEY ) );
	}


	/** @see Facebook\Products::update_product_pattern_attribute() */
	public function test_update_product_pattern_attribute_invalid() {

		$pattern_attribute = $this->tester->create_pattern_attribute();

		$product = $this->get_product( [ 'attributes' => [ $pattern_attribute ] ] );

		$this->expectException( SV_WC_Plugin_Exception::class );

		Products::update_product_pattern_attribute( $product, 'print' );

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->assertSame( '', $product->get_meta( Products::PATTERN_ATTRIBUTE_META_KEY ) );
	}


	/** @see Facebook\Products::update_product_pattern_attribute() */
	public function test_update_product_pattern_attribute_already_used() {

		$color_attribute   = $this->tester->create_color_attribute();
		$pattern_attribute = $this->tester->create_pattern_attribute();

		$product = $this->get_product( [ 'attributes' => [ $color_attribute, $pattern_attribute ] ] );
		$product->update_meta_data( Products::COLOR_ATTRIBUTE_META_KEY, $color_attribute->get_name() );
		$product->update_meta_data( Products::PATTERN_ATTRIBUTE_META_KEY, $pattern_attribute->get_name() );
		$product->save_meta_data();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		$this->expectException( SV_WC_Plugin_Exception::class );

		Products::update_product_pattern_attribute( $product, $color_attribute->get_name() );
	}


	/** @see Facebook\Products::get_product_pattern() */
	public function test_get_product_pattern_simple_product_single_value() {

		$pattern_attribute = $this->tester->create_pattern_attribute( 'pattern', [ 'checked' ] );

		$product = $this->get_product( [ 'attributes' => [ $pattern_attribute ] ] );
		$product->update_meta_data( Products::PATTERN_ATTRIBUTE_META_KEY, $pattern_attribute->get_name() );
		$product->save();

		// get a fresh product object
        $product = wc_get_product( $product->get_id() );

		$this->assertSame( 'checked', Products::get_product_pattern( $product ) );
	}


	/** @see Facebook\Products::get_product_pattern() */
	public function test_get_product_pattern_variation_with_attribute_set() {

		$pattern_attribute = $this->tester->create_pattern_attribute( 'pattern', [ 'checked', 'floral', 'leopard' ], true );

		$product = $this->get_variable_product();
		$product->set_attributes( [ $pattern_attribute ] );
		$product->update_meta_data( Products::PATTERN_ATTRIBUTE_META_KEY, $pattern_attribute->get_name() );
		$product->save();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		foreach ( $product->get_children() as $child_id ) {

			$product_variation = wc_get_product( $child_id );

			/**
			 * Unlike the parent product which uses terms, variations are assigned specific attributes using name value pairs.
			 * @see WC_Product_Variation::set_attributes()
			 */
			$product_variation->set_attributes( [ 'pattern' => 'checked' ] );
			$product_variation->update_meta_data( Products::PATTERN_ATTRIBUTE_META_KEY, $pattern_attribute->get_name() );
			$product_variation->save();

			// get a fresh product object
			$product_variation = wc_get_product( $child_id );

			$this->assertSame( 'checked', Products::get_product_pattern( $product_variation ) );
		}
	}


	/** @see Facebook\Products::get_product_pattern() */
	public function test_get_product_pattern_variation_without_attribute_set() {

		$pattern_attribute = $this->tester->create_pattern_attribute( 'pattern', [ 'checked', 'floral', 'leopard' ], true );

		$product = $this->get_variable_product();
		$product->set_attributes( [ $pattern_attribute ] );
		$product->update_meta_data( Products::PATTERN_ATTRIBUTE_META_KEY, $pattern_attribute->get_name() );
		$product->save();

		// get a fresh product object
		$product = wc_get_product( $product->get_id() );

		foreach ( $product->get_children() as $child_id ) {

			$product_variation = wc_get_product( $child_id );
			$this->assertSame( 'checked | floral | leopard', Products::get_product_pattern( $product_variation ) );
		}
	}


	/** @see Facebook\Products::get_available_product_attributes() */
	public function test_get_available_product_attributes() {

		$product = $this->get_product( [ 'attributes' => $this->tester->create_product_attributes() ] );

		$this->assertSame( $product->get_attributes(), Products::get_available_product_attributes( $product ) );
	}


	/** @see Facebook\Products::get_available_product_attributes() */
	public function test_get_available_product_attributes_for_product_variation() {

		$product = $this->get_variable_product( [ 'children' =>	1 ] );
		$product->set_attributes( [ $this->tester->create_size_attribute( 'size', [ 'small' ], true ) ] );
		$product->save();

		$product_variation = wc_get_product( current( $product->get_children() ) );
		$product_variation->set_attributes( [ 'size' => 'small' ] );
		$product_variation->save();

		$available_attributes = Products::get_available_product_attributes( $product_variation );

		foreach ( array_keys( $product_variation->get_attributes() ) as $key ) {
			$this->assertArrayHasKey( $key, $available_attributes );
			$this->assertInstanceOf( \WC_Product_Attribute::class, $available_attributes[ $key ] );
		}
	}


	/** @see Facebook\Products::get_distinct_product_attributes() */
	public function test_get_distinct_product_attributes() {

		$attributes = $this->tester->create_product_attributes();
		$product    = $this->get_product( [ 'attributes' => $attributes ] );

		list( $color_attribute, $size_attribute, $pattern_attribute ) = $attributes;

		Products::update_product_color_attribute( $product, $color_attribute->get_name() );
		Products::update_product_size_attribute( $product, $size_attribute->get_name() );
		Products::update_product_pattern_attribute( $product, $pattern_attribute->get_name() );

		$this->assertSame( array_filter( [
			Products::get_product_color_attribute( $product ),
			Products::get_product_size_attribute( $product ),
			Products::get_product_pattern_attribute( $product ),
		] ), Products::get_distinct_product_attributes( $product ) );
	}


	/**
	 * @see Products::product_has_attribute()
	 *
	 * @param string $attribute_name attribute name to check
	 * @param bool $expected expected result
	 *
	 * @dataProvider provider_product_has_attribute
	 */
	public function test_product_has_attribute( $attribute_name, $expected ) {

		$color_attribute = $this->tester->create_color_attribute( 'color', [ 'red', 'blue' ], false, true );
		$size_attribute  = $this->tester->create_size_attribute( 'Custom attribute' );

		$product = $this->get_product( [
			'attributes' => [ $color_attribute, $size_attribute ],
		] );

		$this->assertSame( $expected, Products::product_has_attribute( $product, $attribute_name ) );
	}


	/** @see test_product_has_attribute */
	public function provider_product_has_attribute() {

		return [
			'taxonomy attribute' => [ 'color', true ],
			'custom attribute'   => [ 'custom-attribute', true ],
			'missing attribute'  => [ 'missing', false ],
		];
	}


	/** @see Products::get_product_by_fb_retailer_id() */
	public function test_get_product_by_fb_retailer_id_with_sku() {

		$product = $this->get_product();
		$product->set_sku( '123456_a' );
		$product->save();

		$retailer_id = \WC_Facebookcommerce_Utils::get_fb_retailer_id( $product );

		$product_found = Facebook\Products::get_product_by_fb_retailer_id( $retailer_id );
		$this->assertInstanceOf( \WC_Product::class, $product_found );
		$this->assertEquals( $product->get_id(), $product_found->get_id() );
	}


	/** @see Products::get_product_by_fb_retailer_id() */
	public function test_get_product_by_fb_retailer_id_without_sku() {

		$product     = $this->get_product();
		$retailer_id = \WC_Facebookcommerce_Utils::get_fb_retailer_id( $product );

		$product_found = Facebook\Products::get_product_by_fb_retailer_id( $retailer_id );
		$this->assertInstanceOf( \WC_Product::class, $product_found );
		$this->assertEquals( $product->get_id(), $product_found->get_id() );
	}


	/** @see Products::get_product_by_fb_retailer_id() */
	public function test_get_product_by_fb_retailer_id_type_sku() {

		update_option( \WC_Facebookcommerce_Integration::SETTING_FB_RETAILER_ID_TYPE, \WC_Facebookcommerce_Integration::FB_RETAILER_ID_TYPE_SKU );

		$product = $this->get_product();
		$product->set_sku( '123456_a' );
		$product->save();

		$retailer_id = \WC_Facebookcommerce_Utils::get_fb_retailer_id( $product );

		$product_found = Facebook\Products::get_product_by_fb_retailer_id( $retailer_id );
		$this->assertInstanceOf( \WC_Product::class, $product_found );
		$this->assertEquals( $product->get_id(), $product_found->get_id() );
	}


		/** @see Products::get_product_by_fb_retailer_id() */
	public function test_get_product_by_fb_retailer_id_type_product_id() {

		update_option( \WC_Facebookcommerce_Integration::SETTING_FB_RETAILER_ID_TYPE, \WC_Facebookcommerce_Integration::FB_RETAILER_ID_TYPE_PRODUCT_ID );

		$product = $this->get_product();
		$product->set_sku( '123456_a' );
		$product->save();

		$retailer_id = \WC_Facebookcommerce_Utils::get_fb_retailer_id( $product );

		$product_found = Facebook\Products::get_product_by_fb_retailer_id( $retailer_id );
		$this->assertInstanceOf( \WC_Product::class, $product_found );
		$this->assertEquals( $product->get_id(), $product_found->get_id() );
	}

	/** Helper methods ************************************************************************************************/


	/**
	 * Gets a new product object.
	 *
	 * @param array $args product configuration parameters
	 * @return \WC_Product
	 */
	private function get_product( $args = [] ) {

		return $this->tester->get_product( array_merge( $args,  [
			'status'        => 'publish',
			'regular_price' => 19.99,
		] ) );
	}


	/**
	 * Gets a new variable product object, with variations.
	 *
	 * @param array $args product configuration parameters
	 * @param int|int[] $children array of variation IDs, if unspecified will generate the amount passed (default 3)
	 * @return \WC_Product_Variable
	 */
	private function get_variable_product( $args = [] ) {

		return $this->tester->get_variable_product( array_merge( $args,  [
			'status'        => 'publish',
			'regular_price' => 19.99,
		] ) );
	}


	/**
	 * Adds and excluded category.
	 */
	private function add_excluded_category() {

		$category = wp_insert_term( 'Excluded category', 'product_cat' );

		$this->excluded_category = $category['term_id'];

		update_option( \WC_Facebookcommerce_Integration::SETTING_EXCLUDED_PRODUCT_CATEGORY_IDS, [ $this->excluded_category ] );
	}


}
