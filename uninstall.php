<?php
/**
 * @package Internals
 *
 * Code used when the plugin is removed (not just deactivated but actively deleted through the WordPress Admin).
 */

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();

// Create a simple function to delete our transient
function edit_term_delete_transient() {
     delete_transient( 'bolcom-sellerlist' );
}
// Add the function to the edit_term hook so it runs when categories/tags are edited
if(!add_action( 'edit_term', 'edit_term_delete_transient' )) return 0;

// My defined admin options
$pluginDefinedOptions = array('bolcom_title', 'bolcom_apikey', 'bolcom_sellerid', 'bolcom_widgetView'); // etc

// Clear up our admin settings
foreach($pluginDefinedOptions as $optionName) {
    if(get_option( $optionName )) if(!delete_option( $optionName )) return 0;
}