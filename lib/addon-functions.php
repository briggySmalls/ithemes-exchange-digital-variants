<?php
/**
 * iThemes  Exchange Digital Variants Add-on
 * @package IT_Exchange_Addon_Digital_Variants
 * @since   0.1.0
 */

defined( 'ABSPATH' ) OR exit;

/**
 * @brief      Determines if the product is a digital variant of a 'physical' one
 *
 * @param      $itemized_data  The itemized data of the transaction/cart product
 *
 * @return     True if transaction product is digital, False otherwise.
 */
function it_exchange_digital_variants_addon_is_digital_variant_from_data( $product_id, $itemized_data ) {
    // Get the product details from the transaction
    $itemized_data = maybe_unserialize($itemized_data);
    if ( !empty( $itemized_data['it_variant_combo_hash'] ) ) {
        // Product is of a particular variant, get the attributes
        return it_exchange_digital_variants_addon_is_digital_variant( $product_id, $itemized_data['it_variant_combo_hash'] );
    }
    return false;
}

/**
 * @brief      Gets the product attribute from the current transaction product.
 * @note       This function should be called while in a loop using
 *             it_exchange('transaction', 'product-downloads')
 *
 * @param      $attribute  The attribute to retrieve
 *
 * @return     The transaction product attribute.
 */
function it_exchange_digital_variants_addon_get_transaction_product_attribute( $attribute ) {
    $options = array(
        'attribute' => $attribute,
        'format' => '',
        'return' => true 
    );
    return it_exchange( 'transaction', 'product-attribute', $options );
}
