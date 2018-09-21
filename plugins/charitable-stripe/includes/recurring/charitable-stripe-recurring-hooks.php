<?php
/**
 * Charitable Recurring Stripe standard Hooks.
 *
 * Action/filter hooks used for adding support for recurring donations to Stripe gateway.
 *
 * @package     Charitable Stripe/Functions/Recurring Donations
 * @version     1.2.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2018, Eric Daams
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Maybe process a recurring donation.
 *
 * @see     Charitable_Stripe_Recurring::maybe_process_recurring_donation()
 */
add_filter( 'charitable_process_donation_stripe', array( Charitable_Stripe_Recurring::get_instance(), 'maybe_process_recurring_donation' ), 2, 3 );

/**
 * Create a plan in the gateway.
 *
 * @see     Charitable_Stripe_Recurring::create_recurring_donation_plan()
 */
add_filter( 'charitable_recurring_create_gateway_plan_stripe', array( Charitable_Stripe_Recurring::get_instance(), 'create_recurring_donation_plan' ), 10, 4 );

/**
 * Handle Stripe webhooks.
 *
 * @see     Charitable_Stripe_Recurring::process_webhooks()
 */
add_action( 'charitable_stripe_ipn_event', array( Charitable_Stripe_Recurring::get_instance(), 'process_webhooks' ), 10, 2 );
