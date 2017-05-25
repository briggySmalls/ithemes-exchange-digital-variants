<?php
/**
 * iThemes  Exchange Digital Variants Add-on
 * @package IT_Exchange_Addon_Digital_Variants
 * @since   0.1.0
 */

defined( 'ABSPATH' ) OR exit;

/**
 * @brief      Checks if the product is a digital variant of a physical product
 *
 * @param      $product_id             The product identifier
 * @param      $it_variant_combo_hash  The variant combo hash
 *
 * @return     true if product is a digital variant, otherwise false
 */
function it_exchange_digital_variants_addon_is_digital_variant($product_id, $it_variant_combo_hash) {
    // Get the variant attributes from the hash
    $atts = it_exchange_get_variant_combo_attributes_from_hash(
        $product_id,
        $it_variant_combo_hash);

    if (is_array($atts['combo'])) {
        // Loop through the product's variant value IDs...
        foreach ($atts['combo'] as $variant => $value_id) {
            // ...Find the matching details for the given variant ID
            $all_variant_values = it_exchange_get_values_for_variant($variant);
            foreach ($all_variant_values as $value) {
                // ...And check if the value title matches the digital variant value title
                if (($value_id == $value->ID) && ($value->title == 'Digital')) {
                    return true;
                }
            }
        }
    }
    return false;
}
