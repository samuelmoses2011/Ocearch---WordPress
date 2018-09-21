<?php
/**
 * Charitable Stripe Connect/Shortcodes Hooks.
 *
 * Action/filter hooks used for Charitable Stripe Connect/shortcodes
 *
 * @package     Charitable Stripe Connect/Functions/Shortcodes
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Register shortcodes.
 *
 * @see     Charitable_Stripe_Connect_Response_Shortcode::display()
 * @see     Charitable_Stripe_Connect_Status_Shortcode::display()
 */
add_shortcode( 'charitable_stripe_connect_response', array( 'Charitable_Stripe_Connect_Response_Shortcode', 'display' ) );

add_shortcode( 'charitable_stripe_connect_status', array( 'Charitable_Stripe_Connect_Status_Shortcode', 'display' ) );
