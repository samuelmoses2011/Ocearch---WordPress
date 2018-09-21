<?php
/**
 * Plugin Name: 		Charitable - Stripe Connect
 * Plugin URI: 			https://www.wpcharitable.com/extensions/charitable-stripe-connect
 * Description: 		Allow your campaign creators to get paid via Stripe Connect. Requires Charitable Stripe.
 * Version: 			1.0.5
 * Author: 				WP Charitable
 * Author URI: 			https://www.wpcharitable.com
 * Requires at least: 	4.2
 * Tested up to: 		4.9
 *
 * Text Domain: 		charitable-stripe-connect
 * Domain Path: 		/languages/
 *
 * @package 			Charitable Stripe Connect
 * @category 			Core
 * @author 				Studio 164a
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Load plugin class, but only if Charitable is found and activated.
 *
 * @return 	void
 * @since 	1.0.0
 */
function charitable_stripe_connect_load() {
	require_once( 'includes/class-charitable-stripe-connect.php' );

	$has_dependencies = true;

	if ( ! class_exists( 'Charitable' ) ) {

		if ( ! class_exists( 'Charitable_Extension_Activation' ) ) {

			require_once 'includes/admin/class-charitable-extension-activation.php';

		}

		$activation = new Charitable_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		$has_dependencies = false;

	} elseif ( ! class_exists( 'Charitable_Stripe' ) ) {

		if ( ! class_exists( 'Charitable_Stripe_Extension_Activation' ) ) {

			require_once 'includes/admin/class-charitable-stripe-extension-activation.php';

		}

		$activation = new Charitable_Stripe_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		$has_dependencies = false;

	} else {

		new Charitable_Stripe_Connect( __FILE__ );

	}//end if
}

add_action( 'plugins_loaded', 'charitable_stripe_connect_load', 2 );
