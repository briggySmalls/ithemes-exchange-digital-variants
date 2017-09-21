<?php
/**
 * Contains functions that are executed as actions or filters
 *
 * @package IT_Exchange_Addon_Digital_Variants
 * @since   0.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds the necessary product features to digital variant products.
 *
 * @return     void
 */
function it_exchange_digital_variants_addon_add_features_to_digital_variant_products() {
	it_exchange_add_feature_support_to_product_type( 'downloads', 'digital-variant-product-type' );
	it_exchange_add_feature_support_to_product_type( 'shipping', 'digital-variant-product-type' );
}
add_action( 'it_exchange_enabled_addons_loaded', 'it_exchange_digital_variants_addon_add_features_to_digital_variant_products' );

/**
 * @brief      Creates the digital variant that is shared by all digital variant products
 *
 * @return     Variant ID
 */
function it_exchange_digital_variants_addon_create_digital_variant() {
	// Construct the arguments for the variant (inc values)
	$variant_args = array(
		'parent' => array(
			'post_title' => IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_TITLE,
			'post_name' => IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_SLUG,
			'ui_type' => IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_UI_TYPE,
			'preset_slug' => IT_EXCHANGE_DIGITAL_VARIANTS_PRESET_SLUG,
		),
		'values' => array(
			array(
				'post_title' => IT_EXCHANGE_DIGITAL_VARIANTS_PHYSICAL_VARIANT_TITLE,
				'post_name' => IT_EXCHANGE_DIGITAL_VARIANTS_PHYSICAL_VARIANT_SLUG,
				'menu_order' => 0,
			),
			array(
				'post_title' => IT_EXCHANGE_DIGITAL_VARIANTS_DIGITAL_VARIANT_TITLE,
				'post_name' => IT_EXCHANGE_DIGITAL_VARIANTS_DIGITAL_VARIANT_SLUG,
				'menu_order' => 1,
			),
		),
	);

	// Create the variant
	$variant_id = it_exchange_variants_addon_create_variant( $variant_args['parent'] );

	// Create the values
	foreach ( $variant_args['values'] as $value ) {
		// Update the args with the variant
		$value['post_parent'] = $variant_id;

		// Create the variant value
		$value_id = it_exchange_variants_addon_create_variant( $value );

		// Set as default, if necessary
		if ( IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_DEFAULT === $value['post_name'] ) {
			// Update the parent with the default value
			it_exchange_variants_addon_update_variant( $variant_id, array( 'default' => "$value_id" ) );
		}
	}

	return $variant_id;
}

/**
 * @brief      Ensures that there is a digital variant created and recorded
 *
 * @return     None
 */
function it_exchange_digital_variants_addon_init() {
	$addon_settings = it_exchange_get_option( IT_EXCHANGE_DIGITAL_VARIANTS_SETTINGS_KEY );

	// Confirm that the option exists
	if ( isset( $addon_settings['variant_id'] ) ) {
		// Confirm that the variant exists
		if ( it_exchange_variants_addon_get_variant( $addon_settings['variant_id'] ) ) {
			// The variant has already been created
			return;
		}
	}

	// Create the digital variant and save its ID in the database
	if ( $new_id = it_exchange_digital_variants_addon_create_digital_variant() ) {
		// Create an option to the database
		$addon_settings['variant_id'] = $new_id;
		it_exchange_save_option( IT_EXCHANGE_DIGITAL_VARIANTS_SETTINGS_KEY, $addon_settings );
	}
}
add_action( 'admin_init', 'it_exchange_digital_variants_addon_init' );

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

/**
 * @brief      Updates the digital variant product to have the variant attached
 *
 * @param      $product_id  The product identifier
 *
 * @return     None
 */
function it_exchange_digital_variants_addon_save_with_variant( $product_id ) {
	// Ensure that variants are enabled for the product
	$existing_variant_data = (array) it_exchange_get_product_feature( $product_id, 'variants' );
	if ( empty( $new_variant_data['enabled'] ) || 'no' == $new_variant_data['enabled'] ) {
		$existing_variant_data['enabled'] = 'yes';
	}

	// Get any existing variants
	$variants = (array) it_exchange_get_variants_for_product( $product_id );

	// Ensure that the digital variant is among them
	foreach ( $variants as $variant ) {
		if ( it_exchange_digital_variants_addon_is_digital_variant_from_variant( $variant ) ) {
			// The variant is already attached
			return;
		}
	}

	// We need to add the digital variant and its values
	$addon_settings = it_exchange_get_option( IT_EXCHANGE_DIGITAL_VARIANTS_SETTINGS_KEY );
	$existing_variant_data['variants'][] = $addon_settings['variant_id'];

	$values = it_exchange_get_values_for_variant( $addon_settings['variant_id'] );
	foreach ( $values as $value ) {
		$existing_variant_data['variants'][] = $value->ID;
	}

	// Update
	it_exchange_update_product_feature( $product_id, 'variants', $existing_variant_data );
}
add_action( 'it_exchange_save_product_digital-variant-product-type', 'it_exchange_digital_variants_addon_save_with_variant', 11);
