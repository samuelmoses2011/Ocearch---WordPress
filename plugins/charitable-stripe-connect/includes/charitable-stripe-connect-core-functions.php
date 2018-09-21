<?php
/**
 * Charitable Stripe Connect Core Functions.
 *
 * General core functions.
 *
 * @author      Studio164a
 * @category    Core
 * @package     Charitable Stripe Connect
 * @subpackage  Functions
 * @version     1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * This returns the original Charitable_Stripe_Connect object.
 *
 * Use this whenever you want to get an instance of the class. There is no
 * reason to instantiate a new object, though you can do so if you're stubborn :)
 *
 * @return  Charitable_Stripe_Connect
 * @since   1.0.0
 */
function charitable_stripe_connect() {
	return Charitable_Stripe_Connect::get_instance();
}

/**
 * Displays a template.
 *
 * @param   string|array $template_name A single template name or an ordered array of template.
 * @param   arary       $args          Optional array of arguments to pass to the view.
 * @return  Charitable_Stripe_Connect_Template
 * @since   1.0.0
 */
function charitable_stripe_connect_template( $template_name, array $args = array() ) {
	if ( empty( $args ) ) {
		$template = new Charitable_Stripe_Connect_Template( $template_name ); 
	}
	else {
		$template = new Charitable_Stripe_Connect_Template( $template_name, false ); 
		$template->set_view_args( $args );
		$template->render();
	}

	return $template;
}

/**
 * Return the active client ID.
 *
 * @return  string
 * @since   1.0.0
 */
function charitable_stripe_connect_get_client_id() {
	if ( charitable_get_option( 'test_mode' ) ) {
		return charitable_get_option( array( 'gateways_stripe', 'development_client_id' ) );
	}
   
	return charitable_get_option( array( 'gateways_stripe', 'production_client_id' ) ); 
}