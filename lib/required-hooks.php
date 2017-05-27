<?php
/**
 * Contains functions that are executed as actions or filters
 *
 * @package IT_Exchange_Addon_Digital_Variants
 * @since   0.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds the downloads feature to physical products.
 *
 * @return     void
 */
function it_exchange_digital_variants_addon_add_downloads_to_physical_products() {
	it_exchange_add_feature_support_to_product_type( 'downloads', 'physical-product-type' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_digital_variants_addon_add_downloads_to_physical_products' );

/**
 * Adds digital/physical variant presets
 *
 * @since 0.1.0
 *
 * @return void
 */
function it_exchange_digital_variants_addon_setup_preset_variants() {
	// Check the preset hasn't already been added.
	$existing_presets = it_exchange_variants_addon_get_presets(
		array(
			'core_only' => false,
	));

	if ( ! isset( $existing_presets['format'] ) ) {
		// Create a preset variant for Digital/Print.
		$format_preset = array(
			'slug' => IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_SLUG,
			'title' => IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_TITLE,
			'values' => array(
				'print' => array(
					'slug' => IT_EXCHANGE_DIGITAL_VARIANTS_PHYSICAL_VARIANT_SLUG,
					'title' => IT_EXCHANGE_DIGITAL_VARIANTS_PHYSICAL_VARIANT_TITLE,
					'order' => 1,
				),
				'digital' => array(
					'slug' => IT_EXCHANGE_DIGITAL_VARIANTS_DIGITAL_VARIANT_SLUG,
					'title' => IT_EXCHANGE_DIGITAL_VARIANTS_DIGITAL_VARIANT_TITLE,
					'order' => 2,
				),
			),
			'default' => IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_DEFAULT,
			'core' => false,
			'ui-type' => IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_UI_TYPE,
			'version' => IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_VERSION,
		);
		it_exchange_variants_addon_create_variant_preset( $format_preset );
	}
}
add_action( 'admin_init', 'it_exchange_digital_variants_addon_setup_preset_variants' );

/**
 * Intercept attempts to discover if the product has downloads, and if it is not
 * marked with the 'digital' variant then deny the existance of digital content
 *
 * @param      $has_downloads  true if previously determined the product had
 *                             downloads, otherwise false
 * @param      $product_id     The identifier for the product being interrogated
 *
 * @return     true if the product is allowed downloads, otherwise false
 */
function it_exchange_digital_variants_has_feature_downloads( $has_downloads, $product_id ) {
	if ( ! it_exchange_digital_variants_addon_is_digital_variant_from_data(
                it_exchange_digital_variants_addon_get_transaction_product_attribute( 'product_id' ),
                it_exchange_digital_variants_addon_get_transaction_product_attribute( 'itemized_data' )) ) {
		$has_downloads = false;
	}
	return $has_downloads;
}
add_filter( 'it_exchange_product_has_feature_downloads', 'it_exchange_digital_variants_has_feature_downloads', 10, 2 );

/**
 * This returns available shipping methods for the cart
 *
 * This is essentially a straight copy of
 * it_exchange_get_available_shipping_methods_for_cart() from
 * ithemes-exchange/api/shipping.php, with a small addition to ignore digital
 * variants. New functionality added to the original function will be lost here,
 * but unfortunately I can't think of a better way for the moment
 *
 * By default, it only returns the highest common denominator for all products.
 * ie: If product one supports methods A and B but product two only supports
 * method A, this function will only return method A. Toggling the first
 * paramater to false will return a composite of all available methods across
 * products
 *
 * @param      $methods  The previously-calculated list of shipping methods for products in the cart
 *
 * @return     An array of shipping methods
 */
function it_exchange_digital_variants_addon_get_available_shipping_methods_for_cart( $methods ) {
	$only_return_methods_available_to_all_cart_products = $GLOBALS['it_exchange']['shipping']['only_return_methods_available_to_all_cart_products'];

	$methods   = array();
	$product_i = 0;

	// Grab all the products in the cart.
	foreach ( it_exchange_get_cart_products() as $cart_product ) {
		// Skip foreach element if it isn't an exchange product - just to be safe.
		if ( empty( $cart_product['product_id'] ) || false === ( $product = it_exchange_get_product( $cart_product['product_id'] ) ) ) {
			continue;
		}

		// Skip product if it doesn't have shipping.
		if ( ! it_exchange_product_has_feature( $product->ID, 'shipping' ) ) {
			continue;
		}

		// <<<<<<< it_exchange_digital_variants_addon
		// Skip product if it is a digital variant.
		if ( it_exchange_digital_variants_addon_is_digital_variant_from_data( $product->ID, $cart_product['itemized_data'] ) ) {
			continue;
		}
		// >>>>>>>
		// Bump product incrementer.
		$product_i++;
		$product_methods = array();

		// Loop through shipping methods available for this product
		foreach ( (array) it_exchange_get_enabled_shipping_methods_for_product( $product ) as $method ) {
			// Skip if method is false
			if ( empty( $method->slug ) ) {
				continue;
			}

			// If this is the first product, put all available methods in methods array.
			if ( ! empty( $method->slug ) && 1 === $product_i ) {
				$methods[ $method->slug ] = $method;
			}

			// If we're returning all methods, even when they aren't available to other products, tack them onto the array.
			if ( ! $only_return_methods_available_to_all_cart_products ) {
				$methods[ $method->slug ] = $method;
			}

			// Keep track of all this products methods.
			$product_methods[] = $method->slug;
		}

		// Remove any methods previously added that aren't supported by this product.
		if ( $only_return_methods_available_to_all_cart_products ) {
			foreach ( $methods as $slug => $object ) {
				if ( ! in_array( $slug, $product_methods, true ) ) {
					unset( $methods[ $slug ] );
				}
			}
		}
	}// End foreach().
	return $methods;
}
add_filter(
	'it_exchange_get_available_shipping_methods_for_cart',
	'it_exchange_digital_variants_addon_get_available_shipping_methods_for_cart',
	10,
1);
