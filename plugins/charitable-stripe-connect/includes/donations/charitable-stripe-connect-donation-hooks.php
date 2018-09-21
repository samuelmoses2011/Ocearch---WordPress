<?php
/**
 * Charitable Stripe Connect Donation hooks.
 *
 * @package     Charitable Stripe Connect/Functions/Donations
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Filter the charge details going to Stripe.
 *
 * @see     Charitable_Stripe_Connect_Donation::filter_stripe_charges()
 */
add_filter( 'charitable_stripe_charge_args', array( Charitable_Stripe_Connect_Donation::get_instance(), 'filter_stripe_charges' ), 10, 2 );
