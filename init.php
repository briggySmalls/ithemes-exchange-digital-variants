<?php
/**
 * Initialisation for the iThemes Exchange Digital Variants Add-on plugin
 *
 * @package IT_Exchange_Addon_Digital_Variants
 * @since   0.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * New API functions.
 */
include( 'api/load.php' );

/**
 * Functions related to registering the setting page, printing the form, and saving
 * the options using the Exchange storage API to save / retreive options.
 */
include( 'lib/addon-settings.php' );

/**
 * Utility functions specific to the digital variants add-on
 */
include( 'lib/addon-functions.php' );

/**
 * All the hooks into other plugins
 */
include( 'lib/required-hooks.php' );


