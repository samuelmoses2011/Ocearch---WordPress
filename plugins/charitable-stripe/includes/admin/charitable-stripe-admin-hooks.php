<?php
/**
 * Charitable Stripe admin hooks.
 *
 * @package     Charitable Stripe /Functions/Admin
 * @version     1.1.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2018, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Add a direct link to the Extensions settings page from the plugin row.
 *
 * @see     Charitable_Stripe_Admin::add_plugin_action_links()
 */
add_filter( 'plugin_action_links_' . plugin_basename( Charitable_Stripe::get_instance()->get_path() ), array( Charitable_Stripe_Admin::get_instance(), 'add_plugin_action_links' ) );
