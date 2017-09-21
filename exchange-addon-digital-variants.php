<?php
/*
 * Plugin Name:         iThemes Exchange - Digital Variants
 * Version:             0.2.0
 * Description:         Sell Digital variants of Physical products sold in iThemes Exchange
 * Plugin URI:          https://github.com/briggySmalls/exchange-addon-digital-variants
 * Author:              Sam Briggs
 * Author URI:          https://github.com/briggySmalls
 * License:             GPL3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * GitHub Plugin URI:   https://github.com/briggySmalls/exchange-addon-digital-variants
 * iThemes Package:     exchange-digital-variants
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the plugin as an iThemes Exchange add-on
 *
 * @since 0.1.0
 *
 * @return void
 */
function it_exchange_register_digital_variants_addon() {
	$options = array(
		'name'              => __( 'Digital Variant Products', 'exchange-addon-digital-variants' ),
		'description'       => __( 'This is a digital variant product type for selling items with a digital variant.', 'exchange-addon-digital-variants' ),
		'author'            => 'Sam Briggs',
		'author_url'        => 'https://github.com/briggySmalls',
		'file'              => dirname( __FILE__ ) . '/init.php',
		'category'          => 'product-type',
		'labels'      		=> array(
			'singular_name' => __( 'Digital Variant Product', 'it-l10n-ithemes-exchange' ),
		),
	);
	it_exchange_register_addon( 'digital-variant-product-type', $options );
}
add_action( 'it_exchange_register_addons', 'it_exchange_register_digital_variants_addon' );


