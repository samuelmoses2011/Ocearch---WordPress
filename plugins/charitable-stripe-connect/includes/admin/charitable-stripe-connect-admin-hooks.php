<?php
/**
 * Charitable Stripe Connect admin hooks.
 *
 * @package     Charitable Stripe Connect/Functions/Admin
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Add a direct link to the Extensions settings page from the plugin row.
 *
 * @see     Charitable_Stripe_Connect_Admin::add_plugin_action_links()
 */
add_filter( 'plugin_action_links_' . plugin_basename( charitable_stripe_connect()->get_path() ), array( Charitable_Stripe_Connect_Admin::get_instance(), 'add_plugin_action_links' ) );

/**
 * Add a "Connect" section to the Stripe settings page.
 *
 * @see     Charitable_Stripe_Connect_Admin::add_connect_settings()
 */
add_filter( 'charitable_settings_fields_gateways_gateway_stripe', array( Charitable_Stripe_Connect_Admin::get_instance(), 'add_connect_settings' ), 20 );

/**
 * Add Stripe Connect as a Payout method in the Ambassadors settings.
 *
 * @see     Charitable_Stripe_Connect_Admin::add_connect_payout_method()
 */
add_filter( 'charitable_ambassadors_payout_methods', array( Charitable_Stripe_Connect_Admin::get_instance(), 'add_connect_payout_method' ) );

/**
 * Add Stripe Connect section to the admin profiles.
 *
 * @see     Charitable_Stripe_Connect_Admin::add_profile_section()
 */
add_action( 'show_user_profile', array( Charitable_Stripe_Connect_Admin::get_instance(), 'add_profile_section' ) );
add_action( 'edit_user_profile', array( Charitable_Stripe_Connect_Admin::get_instance(), 'add_profile_section' ) );

/**
 * Add the recipient's Stripe Connect details to the funds recipient tab.
 *
 * @see     Charitable_Stripe_Connect_Admin::add_funds_recipient_details()
 */
add_filter( 'charitable_ambassadors_campaign_funds_recipient_data', array( Charitable_Stripe_Connect_Admin::get_instance(), 'add_funds_recipient_details' ), 10, 2 );
