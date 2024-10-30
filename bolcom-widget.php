<?php
/*
Plugin Name: Seller Widget - App for bol.com
Plugin URI: http://www.404design.nl/2013/08/bol-com-seller-wordpress-plugin/
Description: Gets your seller list off bol.com
Version: 1.0.5
Author: Derk Braakman
Author URI: http://www.derkbraakman.com
License: GPL2
*/

/*

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

// -- path definitions
define('bolcom_DIR', plugin_dir_path(__FILE__));
define('bolcom_URL', plugin_dir_url(__FILE__));

// -- activation and deactivation

register_activation_hook(__FILE__, 'bolcom_activation');
register_deactivation_hook(__FILE__, 'bolcom_deactivation');

function bolcom_activation( ) {
	return 1;
}

function bolcom_deactivation( ) {
	return 1;
}


// #############################################################################
// ### load stylesheet CSS

add_action( 'wp_print_styles', 'bolcom_widget_add_stylesheet' );
/**
 * Adds custom stylesheet to head section of the page
 */
function bolcom_widget_add_stylesheet( ) {
	wp_register_style( 'bolcomWidgetStyle', bolcom_URL . '/assets/css/bolcom.css' );
	wp_enqueue_style( 'bolcomWidgetStyle' );
}

function bolcom_load_textdomain() {
	load_plugin_textdomain( 'wordpress-bolcom', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_filter( 'wp_loaded', 'bolcom_load_textdomain' );

// #############################################################################
// ### load actions widget

add_action('widgets_init', 'bolcom_load_actions_widget');

function bolcom_load_actions_widget( ) {
	register_widget ("Bolcom_Seller_Widget");
}


// #############################################################################
// ### Include the widget class

include_once( bolcom_DIR . 'includes/widgetclass-bolcom.php' );



