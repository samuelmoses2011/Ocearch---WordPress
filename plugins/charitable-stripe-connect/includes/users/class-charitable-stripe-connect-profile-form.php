<?php
/**
 * The class that is responsible for augmenting the base profile form, adding the
 * Stripe field and saving it correctly on form save.
 *
 * @package     Charitable User Avatar/Classes/Charitable_Stripe_Connect_Profile_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Connect_Profile_Form' ) ) :

	/**
	 * Charitable_Stripe_Connect_Profile_Form
	 *
	 * @since       1.0.0
	 */
	class Charitable_Stripe_Connect_Profile_Form {

		/**
		 * One true class instance.
		 *
		 * @var     Charitable_Stripe_Connect_Profile_Form
		 * @access  private
		 * @static
		 * @since   1.0.0
		 */
		private static $instance = null;

		/**
		 * Create class object.
		 *
		 * @access  private
		 * @since   1.0.0
		 */
		private function __construct() {
		}

		/**
		 * Create and return the class object.
		 *
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Stripe_Connect_Profile_Form();
			}

			return self::$instance;
		}

		/**
		 * Add Stripe Connect field to user profile form.
		 *
		 * @param   array[]                 $fields List of fields in the profile form.
		 * @param   Charitable_Profile_Form $form   Profile form object.
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_stripe_connect_field( $fields, $form ) {
			$stripe_user = new Charitable_Stripe_Connect_User( $form->get_user()->ID );

			if ( $stripe_user->is_user_connected() ) {
				$content = __( 'Your Stripe account has been successfully connected.', 'charitable-stripe-connect' );
			} else {
				$content = sprintf( __( 'You haven\'t connected your Stripe account yet.<br /><a href="%s">Connect Now</a>', 'charitable-stripe-connect' ), $stripe_user->get_redirect_url() );
			}

			$fields['stripe_connect'] = apply_filters( 'charitable_stripe_connect_field_args', array(
				'label'     => __( 'Stripe', 'charitable-stripe-connect' ),
				'type'      => 'paragraph',
				'content'   => $content,
				'priority'  => 48,
				'fullwidth' => true,
			) );

			return $fields;
		}
	}

endif;
