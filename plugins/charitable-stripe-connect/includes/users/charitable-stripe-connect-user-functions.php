<?php
/**
 * Sets up helper functions to manage user's Stripe Connect setup.
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
 * If it is, do something with the response.
 *
 * @return  void
 * @since   1.0.0
 */
function charitable_stripe_connect_check_response() {

	/**
	 * Both the code and state must be set.
	 *
	 * If there was an error, we don't do anything with it. However,
	 * you can use the [charitable_stripe_connect_response] shortcode
	 * on your page to display a message.
	 */
	if ( ! isset( $_GET['state'] ) || 'stripe' != substr( $_GET['state'], 0, 6 ) ) {
		return;
	}

	$response = new Charitable_Stripe_Connect_Response;

	$connected = $response->process();

	if ( $connected ) {
		$redirect_page = charitable_get_option( array( 'gateways_stripe', 'success_redirect' ) );
		$query_args = array(
			'stripe-connected' => '1',
		);
	} else {
		$redirect_page = charitable_get_option( array( 'gateways_stripe', 'error_redirect' ) );
		$query_args = array(
			'stripe-connected' => '0',
			'error' => isset( $_GET['error'] ) ? urlencode( $_GET['error'] ) : '',
			'error_description' => isset( $_GET['error_description'] ) ? urlencode( $_GET['error_description'] ) : '',
		);
	}

	$redirect_url = 'home' == $redirect_page ? home_url() : get_permalink( $redirect_page );

	$redirect_url = add_query_arg( $query_args, $redirect_url );

	wp_safe_redirect( $redirect_url );

	exit();
}
