<?php
/**
 * Charitable Stripe Connect Campaign Form hooks.
 *
 * @package     Charitable Stripe Connect/Functions/Ambassadors
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Prevent campaigns from auto-publishing.
 *
 * @see     Charitable_Stripe_Connect_Campaign_Form::prevent_campaign_auto_publishing()
 */
add_filter( 'charitable_campaign_submission_initial_status', array( Charitable_Stripe_Connect_Campaign_Form::get_instance(), 'prevent_campaign_auto_publishing' ), 10, 3 );

/**
 * Save the WordPress user ID of the user who will receive campaign payments.
 *
 * @see     Charitable_Stripe_Connect_Campaign_Form::save_stripe_receiver_id()
 */
add_action( 'charitable_campaign_submission_save', array( Charitable_Stripe_Connect_Campaign_Form::get_instance(), 'save_stripe_receiver_id' ), 10, 4 );

/**
 * Redirect the user to Stripe after they have submitted their campaign.
 *
 * @see     Charitable_Stripe_Connect_Campaign_Form::redirect_to_stripe()
 */
add_filter( 'charitable_campaign_submission_redirect_url', array( Charitable_Stripe_Connect_Campaign_Form::get_instance(), 'redirect_to_stripe' ), 10, 4 );

/**
 * Publish a connected user's campaigns.
 *
 * @see     Charitable_Stripe_Connect_Campaign_Form::publish_user_campaigns()
 */
add_action( 'charitable_stripe_connect_user_connected', array( Charitable_Stripe_Connect_Campaign_Form::get_instance(), 'publish_user_campaigns' ) );
