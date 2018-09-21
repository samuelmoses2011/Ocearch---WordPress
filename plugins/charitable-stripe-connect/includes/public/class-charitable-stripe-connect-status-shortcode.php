<?php
/**
 * [charitable_stripe_connect_status] shortcode class.
 *
 * @version     1.0.0
 * @package     Charitable Stripe Connect/Shortcodes/charitable_stripe_connect_status
 * @category    Class
 * @author      Eric Daams
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Connect_Status_Shortcode' ) ) :

	/**
	 * Charitable_Stripe_Connect_Status_Shortcode class.
	 *
	 * @since       1.0.0
	 */
	class Charitable_Stripe_Connect_Status_Shortcode {

		/**
		 * The callback method for the [charitable_stripe_connect_status] shortcode.
		 *
		 * @param   array $atts User-defined shortcode attributes.
		 * @return  string
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function display( $atts ) {

			$defaults = array(
				'button_text' => __( 'Connect Now', 'charitable-stripe-connect' ),
				'button_class' => '',
				'connected' => __( 'Your Stripe account has been successfully connected.', 'charitable-stripe-connect' ),
				'not_connected' => __( 'You haven\'t connected your Stripe account yet.', 'charitable-stripe-connect' ),
			);

			$args = shortcode_atts( $defaults, $atts, 'charitable_stripe_connect_status' );

			if ( ! is_user_logged_in() ) {
				return;
			}

			$stripe_user = new Charitable_Stripe_Connect_User( get_current_user_id() );

			if ( $stripe_user->is_user_connected() ) {
				$content = $args['connected'];
			} else {
				$content = sprintf( '%s<br /><a href="%s" class="%s">%s</a>',
					$args['not_connected'],
					esc_url( $stripe_user->get_redirect_url() ),
					esc_attr( $args['button_class'] ),
					$args['button_text']
				);
			}

			return apply_filters( 'charitable_stripe_connect_status_shortcode', $content );
		}
	}

endif;
