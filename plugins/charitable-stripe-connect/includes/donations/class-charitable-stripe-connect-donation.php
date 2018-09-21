<?php
/**
 * Responsible for ensuring that donations are charged correctly, taking into account the Stripe destinations.
 *
 * @package     Charitable Stripe Connect/Classes/Charitable_Stripe_Connect_Donation
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Connect_Donation' ) ) :

	/**
	 * Charitable_Stripe_Connect_Donation
	 *
	 * @since       1.0.0
	 */
	class Charitable_Stripe_Connect_Donation {

		/**
		 * One true class instance.
		 *
		 * @var     Charitable_Stripe_Connect_Donation
		 * @access  private
		 * @static
		 * @since   1.0.0
		 */
		private static $instance = null;

		/**
		 * Create class object. Private constructor.
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
				self::$instance = new Charitable_Stripe_Connect_Donation();
			}

			return self::$instance;
		}

		/**
		 * Set the Stripe access token of the campaign funds recipient.
		 *
		 * @param   array 	 			$charges  The array of charges to be made through the Stripe API.
		 * @param   Charitable_Donation $donation The donation object.
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function filter_stripe_charges( $charges, Charitable_Donation $donation ) {

			/* Base charge */
			$charge = $charges[0];

			$application_fee = charitable_get_option( array( 'gateways_stripe', 'application_fee' ) );

			$charges = array();

			foreach ( $donation->get_campaign_donations() as $campaign_donation ) {

				$new_charge = $charge;
				$new_charge['amount'] = $this->get_amount( $campaign_donation->amount );
				$new_charge['description'] = $campaign_donation->campaign_name;
				$new_charge['statement_descriptor'] = substr( $campaign_donation->campaign_name, 0, 22 );

				$user_id = get_post_meta( $campaign_donation->campaign_id, '_campaign_stripe_connect_user_id', true );

				if ( ! $user_id ) {

					$charges[] = $new_charge;
					continue;

				}

				$stripe_user = new Charitable_Stripe_Connect_User( $user_id );

				$stripe_user_id = $stripe_user->get( 'stripe_user_id' );

				/* We don't have an access token for the user, so we're just going to send the funds to the platform. */
				if ( ! $stripe_user_id || ! $stripe_user->is_token_valid_for_current_mode() ) {
					$charges[] = $new_charge;
					continue;
				}

				$charge_application_fee = $this->get_application_fee_for_charge( $campaign_donation->amount, $application_fee );

				/* The charge will be made directly on the connected account. */
				if ( 'direct' == charitable_get_option( array( 'gateways_stripe', 'charge_owner' ) ) ) {
					$new_charge['options'] = array(
						'stripe_account' => $stripe_user_id,
					);
				} else {
					$new_charge['destination'] = $stripe_user_id;
				}

				if ( $charge_application_fee ) {
					$new_charge['application_fee'] = $charge_application_fee;
				}

				$charges[] = $new_charge;

			}//end foreach

			return $charges;
		}

		/**
		 * Return the application fee to be charged for a particular amount.
		 *
		 * @param   decimal $amount			 The donation amount.
		 * @param   mixed   $application_fee The application fee.
		 * @return  int
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_application_fee_for_charge( $amount, $application_fee ) {

			$fee		= charitable_get_currency_helper()->sanitize_monetary_amount( $application_fee );
			$multiplier = $this->is_zero_decimal_currency() ? 1 : 100;

			/* Fixed application fee. */
			if ( apply_filters( 'charitable_stripe_connect_enable_fixed_platform_fee', false ) ) {
				return $fee * $multiplier;
			}

			return round( $multiplier * ( $amount * ( $fee / 100 ) ) );
		}

		/**
		 * Get the donation amount in the smallest common currency unit.
		 *
		 * This is a duplicate of the method introduced in Stripe 1.2.3. It is provided here to ensure compatibility
		 * even if the Stripe extension has not been updated yet.
		 *
		 * @param 	float       $amount   The donation amount in dollars.
		 * @param 	string|null $currency The currency of the donation. If null, the site currency will be used.
		 * @return  int
		 * @access  public
		 * @since   1.0.4
		 *
		 * @deprecated
		 */
		private function get_amount( $amount, $currency = null ) {

			if ( method_exists( 'Charitable_Gateway_Stripe', 'get_amount' ) ) {
				return Charitable_Gateway_Stripe::get_amount( $amount );
			}

			/* Unless it's a zero decimal currency, multiply the currency x 100 to get the amount in cents. */
			if ( $this->is_zero_decimal_currency( $currency ) ) {
				$amount = $amount * 1;
			} else {
				$amount = $amount * 100;
			}

			return $amount;
		}

		/**
		 * Returns whether the currency is a zero decimal currency.
		 *
		 * This is a duplicate of the method introduced in Stripe 1.2.3. It is provided here to ensure compatibility
		 * even if the Stripe extension has not been updated yet.
		 *
		 * @param 	string $currency The currency for the charge. If left blank, will check for the site currency.
		 * @return  bool
		 * @access  private
		 * @since   1.0.4
		 *
		 * @deprecated
		 */
		private function is_zero_decimal_currency( $currency = null ) {

			if ( method_exists( 'Charitable_Gateway_Stripe', 'is_zero_decimal_currency' ) ) {
				return Charitable_Gateway_Stripe::is_zero_decimal_currency( $currency );
			}

			if ( is_null( $currency ) ) {
				$currency = charitable_get_currency();
			}

			return in_array( strtoupper( $currency ), $this->get_zero_decimal_currencies() );
		}

		/**
		 * Return all zero-decimal currencies supported by Stripe.
		 *
		 * This is a duplicate of the method introduced in Stripe 1.2.3. It is provided here to ensure compatibility
		 * even if the Stripe extension has not been updated yet.
		 *
		 * @return  array
		 * @access  private
		 * @since   1.0.4
		 *
		 * @deprecated
		 */
		private function get_zero_decimal_currencies() {

			if ( method_exists( 'Charitable_Gateway_Stripe', 'get_zero_decimal_currencies' ) ) {
				return Charitable_Gateway_Stripe::get_zero_decimal_currencies();
			}

			return array(
				'BIF',
				'CLP',
				'DJF',
				'GNF',
				'JPY',
				'KMF',
				'KRW',
				'MGA',
				'PYG',
				'RWF',
				'VND',
				'VUV',
				'XAF',
				'XOF',
				'XPF',
			);
		}
	}

endif;
