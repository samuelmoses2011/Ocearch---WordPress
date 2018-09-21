<?php
/**
 * Plugin Name:       Charitable - Stripe Payment Gateway
 * Plugin URI:    	  https://www.wpcharitable.com/extensions/charitable-stripe/
 * Description:       Adds the ability for your donors to make their donations through Stripe.
 * Version:           1.2.4
 * Author:    		  WPCharitable
 * Author URI:    	  https://www.wpcharitable.com/
 * Requires at least: 4.1
 * Tested up to:      4.9.4
 *
 * Text Domain:       charitable-stripe
 * Domain Path:       /languages/
 *
 * @package  Charitable Stripe
 * @category Core
 * @author   Studio164a
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Load plugin class, but only if Charitable is activated.
 *
 * @return 	boolean Whether the class is loaded.
 * @since 	1.0.0
 */
function charitable_stripe_load() {
	require_once( 'includes/class-charitable-stripe.php' );

	/* Check for Charitable */
	if ( ! class_exists( 'Charitable' ) ) {

		if ( ! class_exists( 'Charitable_Extension_Activation' ) ) {
			require_once 'includes/admin/class-charitable-extension-activation.php';
		}

		if ( is_admin() ) {
			$activation = new Charitable_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
			$activation = $activation->run();
		}

		return false;

	} else {

		new Charitable_Stripe( __FILE__ );

		return true;

	}
}

add_action( 'plugins_loaded', 'charitable_stripe_load', 1 );
