<?php
/**
Plugin Name: SendPress for WooCommerce
Version: 1.0
Plugin URI: http://sendpress.com
Description: A plugin that adds an option to sign up for SendPress lists on WooCommerce checkout
Author: Jared Harbour (BrewLabs)
Author URI: http://sendpress.com
*/

include( dirname( __FILE__ ) . '/classes/woo-sendpress-signup.php' );

register_activation_hook( __FILE__, array( 'SendPress_Woo_Signup', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'SendPress_Woo_Signup', 'plugin_deactivation' ) );

// Initialize!
SendPress_Woo_Signup::get_instance();