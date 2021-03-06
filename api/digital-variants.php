<?php
/**
 * Contains digital variants API functions (and constants)
 *
 * @package IT_Exchange_Addon_Digital_Variants
 * @since   0.1.0
 */

defined( 'ABSPATH' ) || exit;

define( 'IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_SLUG', 'format' );
define( 'IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_TITLE', 'Format' );
define( 'IT_EXCHANGE_DIGITAL_VARIANTS_DIGITAL_VARIANT_SLUG', 'digital' );
define( 'IT_EXCHANGE_DIGITAL_VARIANTS_DIGITAL_VARIANT_TITLE', 'Digital' );
define( 'IT_EXCHANGE_DIGITAL_VARIANTS_PHYSICAL_VARIANT_SLUG', 'print' );
define( 'IT_EXCHANGE_DIGITAL_VARIANTS_PHYSICAL_VARIANT_TITLE', 'Print' );
define( 'IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_DEFAULT', IT_EXCHANGE_DIGITAL_VARIANTS_PHYSICAL_VARIANT_SLUG );
define( 'IT_EXCHANGE_DIGITAL_VARIANTS_VARIANT_UI_TYPE', 'select' );
define( 'IT_EXCHANGE_DIGITAL_VARIANTS_SETTINGS_KEY', 'digital-variants-addon' );
define( 'IT_EXCHANGE_DIGITAL_VARIANTS_PRESET_SLUG', 'template-select' );

/**
 * Checks if the product is a digital variant of a physical product
 *
 * @param      $product_id             The product identifier
 * @param      $it_variant_combo_hash  The variant combo hash
 *
 * @return     true if product is a digital variant, otherwise false
 */
function it_exchange_digital_variants_addon_is_digital_variant_from_product( $product_id, $it_variant_combo_hash ) {
	// Get the variant attributes from the hash.
	$atts = it_exchange_get_variant_combo_attributes_from_hash(
		$product_id,
	$it_variant_combo_hash);

	if ( is_array( $atts['combo'] ) ) {
		// Loop through the product's variant value IDs...
		foreach ( $atts['combo'] as $variant_id => $value_id ) {
			if ( it_exchange_digital_variants_addon_is_digital_variant_from_value( $value_id ) ) {
				// The product has a variant value indicating it is a digital variant.
				return true;
			}
		}
	}
	return false;
}

/**
 * Checks whether the provided variant is a digital variant
 *
 * @param      $variant  The variant object
 *
 * @return     true if variant is a digital variant, otherwise false
 */
function it_exchange_digital_variants_addon_is_digital_variant_from_variant( $variant_id ) {
	$addon_settings = it_exchange_get_option( IT_EXCHANGE_DIGITAL_VARIANTS_SETTINGS_KEY );
	return ( $addon_settings['variant_id'] === $variant_id );
}

/**
 * Checks whether the provided variant value is a digital value
 *
 * @param      $variant  The value object
 *
 * @return     true if value specifies a digital variant, otherwise false
 */
function it_exchange_digital_variants_addon_is_digital_variant_from_value( $value_id ) {
	$addon_settings = it_exchange_get_option( IT_EXCHANGE_DIGITAL_VARIANTS_SETTINGS_KEY );
	return ( $addon_settings['digital_value_id'] === $value_id );
}
