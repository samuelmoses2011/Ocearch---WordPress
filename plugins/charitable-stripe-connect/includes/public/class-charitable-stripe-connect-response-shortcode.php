<?php
/**
 * [charitable_stripe_connect_response] shortcode class.
 *
 * @version     1.0.0
 * @package     Charitable Stripe Connect/Shortcodes/charitable_stripe_connect_response
 * @category    Class
 * @author      Eric Daams
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Connect_Response_Shortcode' ) ) :

	/**
	 * Charitable_Stripe_Connect_Response_Shortcode class.
	 *
	 * @since       1.0.0
	 */
	class Charitable_Stripe_Connect_Response_Shortcode {

		/**
		 * The callback method for the [charitable_stripe_connect_response] shortcode.
		 *
		 * @param   array $atts User-defined shortcode attributes.
		 * @return  string
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function display( $atts ) {
			if ( ! isset( $_GET['stripe-connected'] ) ) {
				return '';
			}

			$defaults = array(
				'error_header' => __( 'Something went wrong with connecting your Stripe account.', 'charitable-stripe-connect' ),
				'success' => __( 'Your Stripe account has been successfully connected.', 'charitable-stripe-connect' ),
				'show_retry_link' => true,
			);

			$args = shortcode_atts( $defaults, $atts, 'charitable-stripe-connect-response' );

			$args['response'] = new Charitable_Stripe_Connect_Response();

			ob_start();

			charitable_stripe_connect_template( 'shortcodes/connect-response.php', $args );

			return apply_filters( 'charitable_stripe_connect_response_shortcode', ob_get_clean(), $args );
		}
	}

endif;
