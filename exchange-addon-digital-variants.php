<?php
/* 
 * Plugin Name:         iThemes Exchange - Digital Variants
 * Version:             0.1.0
 * Description:         Sell Digital variants of Physical products sold in iThemes Exchange
 * Plugin URI:          https://github.com/briggySmalls/exchange-addon-digital-variants
 * Author:              Sam Briggs
 * Author URI:          https://github.com/briggySmalls
 * License:             GPL3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * GitHub Plugin URI:   https://github.com/briggySmalls/exchange-addon-digital-variants
 * iThemes Package:     exchange-digital-variants
 */

defined( 'ABSPATH' ) OR exit;

/**
 * Register the plugin as an iThemes Exchange add-on
 *
 * @since 0.1.0
 *
 * @return void
*/
function it_exchange_register_digital_variants_addon() {
    $options = array(
        'name'              => __( 'Digital Variants', 'exchange-addon-digital-variants' ),
        'description'       => __( 'Sell Digital variants of Physical products sold in iThemes Exchange.', 'exchange-addon-digital-variants' ),
        'author'            => 'Sam Briggs',
        'author_url'        => 'https://github.com/briggySmalls',
        // 'icon'              => ITUtility::get_url_from_file( dirname( __FILE__ ) . '/images/stripe50px.png' ),
        'file'              => dirname( __FILE__ ) . '/init.php',
        'category'          => 'product-features',
        // 'supports'          => array( 'transaction_status' => true ),
        // 'settings-callback' => 'it_exchange_digital_variants_addon_settings_callback',    
    );  
    it_exchange_register_addon( 'digital_variants', $options );
}
add_action( 'it_exchange_register_addons', 'it_exchange_register_digital_variants_addon' );

?>