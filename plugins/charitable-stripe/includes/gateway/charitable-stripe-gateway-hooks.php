<?php
/**
 * Charitable Stripe Gateway Hooks.
 *
 * Action/filter hooks used for handling payments through the Stripe gateway.
 *
 * @package     Charitable Stripe/Hooks/Gateway
 * @version     1.0.3
 * @author      Eric Daams
 * @copyright   Copyright (c) 2018, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Register our new gateway.
 *
 * @see     Charitable_Gateway_Stripe::register_gateway()
 */
add_filter( 'charitable_payment_gateways', array( 'Charitable_Gateway_Stripe', 'register_gateway' ) );

/**
 * Set up Stripe JS or Stripe Checkout in the donation form.
 *
 * @see     Charitable_Gateway_Stripe::setup_scripts()
 */
add_action( 'charitable_form_after_fields', array( 'Charitable_Gateway_Stripe', 'maybe_setup_scripts_in_donation_form' ) );

/**
 * Maybe enqueue the Stripe JS/Checkout scripts after a campaign loop, if modal donations are in use.
 *
 * @see     Charitable_Gateway_Stripe::maybe_setup_scripts_in_campaign_loop()
 */
add_action( 'charitable_campaign_loop_after', array( 'Charitable_Gateway_Stripe', 'maybe_setup_scripts_in_campaign_loop' ) );

/**
 * Include the Stripe token field in the donation form.
 *
 * @see     Charitable_Gateway_Stripe::add_hidden_token_field()
 */
add_filter( 'charitable_donation_form_hidden_fields', array( 'Charitable_Gateway_Stripe', 'add_hidden_token_field' ) );

/**
 * Also make sure that the Stripe token is picked up in the values array.
 *
 * @see     Charitable_Gateway_Stripe::set_submitted_stripe_token()
 */
add_filter( 'charitable_donation_form_submission_values', array( 'Charitable_Gateway_Stripe', 'set_submitted_stripe_token' ), 10, 2 );

/**
 * Validate the donation form submission before processing.
 *
 * @see     Charitable_Gateway_Stripe::validate_donation()
 */
add_filter( 'charitable_validate_donation_form_submission_gateway', array( 'Charitable_Gateway_Stripe', 'validate_donation' ), 10, 3 );

/**
 * Process the donation.
 *
 * @see     Charitable_Gateway_Stripe::process_donation()
 */
if ( -1 == version_compare( charitable()->get_version(), '1.3.0' ) ) {
	/**
	 * This is for backwards-compatibility. Charitable before 1.3 used on action hook, not a filter.
	 *
	 * @see     Charitable_Gateway_Stripe::process_donation_legacy()
	 */
	add_action( 'charitable_process_donation_stripe', array( 'Charitable_Gateway_Stripe', 'process_donation_legacy' ), 10, 2 );
} else {
	add_filter( 'charitable_process_donation_stripe', array( 'Charitable_Gateway_Stripe', 'process_donation' ), 10, 3 );
}

/**
 * Process the Stripe IPN.
 *
 * @see     Charitable_Gateway_Stripe::process_ipn()
 */
add_action( 'charitable_process_ipn_stripe', array( 'Charitable_Gateway_Stripe', 'process_ipn' ) );

/**
 * Handle a Stripe IPN event.
 *
 * @see     Charitable_Gateway_Stripe::process_refund()
 */
add_action( 'charitable_stripe_ipn_event', array( 'Charitable_Gateway_Stripe', 'process_refund' ), 10, 2 );
