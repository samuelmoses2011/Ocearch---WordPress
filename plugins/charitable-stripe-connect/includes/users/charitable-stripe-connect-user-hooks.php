<?php
/**
 * Charitable Stripe Connect User hooks.
 *
 * @package     Charitable Stripe Connect/Functions/Users
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Check whether the current request is a redirect from Stripe.
 *
 * @see     charitable_stripe_connect_check_response()
 */
add_action( 'init', 'charitable_stripe_connect_check_response' );

/**
 * Add Stripe field to the profile form.
 *
 * @see     Charitable_Stripe_Connect_Profile_Form::charitable_user_social_fields()
 */
add_action( 'charitable_user_social_fields', array( Charitable_Stripe_Connect_Profile_Form::get_instance(), 'add_stripe_connect_field' ), 10, 2 );
