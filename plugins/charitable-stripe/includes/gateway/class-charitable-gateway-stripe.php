<?php
/**
 * Stripe Gateway class
 *
 * @version     1.1.3
 * @package     Charitable/Classes/Charitable_Gateway_Stripe
 * @author      Eric Daams
 * @copyright   Copyright (c) 2018, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Gateway_Stripe' ) ) :

	/**
	 * Stripe Gateway.
	 *
	 * @since       1.0.0
	 */
	class Charitable_Gateway_Stripe extends Charitable_Gateway {

		/**
		 * The gateway ID.
		 *
		 * @var     string
		 */
		const ID = 'stripe';

		/**
		 * The key to use to store a customer ID.
		 *
		 * @var     string
		 */
		const STRIPE_CUSTOMER_ID_KEY = 'stripe_customer_id';

		/**
		 * The key to use to store a customer ID.
		 *
		 * @var     string
		 */
		const STRIPE_CUSTOMER_ID_KEY_TEST = 'stripe_customer_id_test';

		/**
		 * The array of charges to make in a single transaction.
		 *
		 * @var     array
		 */
		private $charges = array();

		/**
		 * Instantiate the gateway class, defining its key values.
		 *
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct() {
			$this->name = apply_filters( 'charitable_gateway_stripe_name', __( 'Stripe', 'charitable-stripe' ) );

			$this->defaults = array(
				'label' => __( 'Stripe', 'charitable-stripe' ),
			);

			$this->supports = array(
				'1.3.0',
				'credit-card',
				'recurring',
			);

			/**
			 * Needed for backwards compatibility with Charitable < 1.3
			 */
			$this->credit_card_form = true;
		}

		/**
		 * Register the Stripe payment gateway class.
		 *
		 * @param   string[] $gateways The list of registered gateways.
		 * @return  string[]
		 * @access  public
		 * @static
		 * @since   1.0.3
		 */
		public static function register_gateway( $gateways ) {
			$gateways['stripe'] = 'Charitable_Gateway_Stripe';
			return $gateways;
		}

		/**
		 * Register gateway settings.
		 *
		 * @param   array $settings The existing settings to display for the Stripe settings page.
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function gateway_settings( $settings ) {

			$settings['test_secret_key'] = array(
				'type'      => 'text',
				'title'     => __( 'Test Secret Key', 'charitable-stripe' ),
				'priority'  => 6,
				'class'     => 'wide',
			);

			$settings['test_public_key'] = array(
				'type'      => 'text',
				'title'     => __( 'Test Publishable Key', 'charitable-stripe' ),
				'priority'  => 8,
				'class'     => 'wide',
			);

			$settings['live_secret_key'] = array(
				'type'      => 'text',
				'title'     => __( 'Live Secret Key', 'charitable-stripe' ),
				'priority'  => 10,
				'class'     => 'wide',
			);

			$settings['live_public_key'] = array(
				'type'      => 'text',
				'title'     => __( 'Live Publishable Key', 'charitable-stripe' ),
				'priority'  => 12,
				'class'     => 'wide',
			);

			$settings['enable_stripe_checkout'] = array(
				'type'      => 'checkbox',
				'title'     => __( 'Use Stripe Checkout', 'charitable-stripe' ),
				'priority'  => 14,
				'help'      => __( 'When you enable Stripe Checkout, donors enter their credit card details into a secure popup window managed by Stripe. <a href="https://stripe.com/docs/checkout" target="_blank">https://stripe.com/docs/checkout</a>', 'charitable-stripe' ),
			);

			// $settings['enable_bitcoin'] = array(
			//     'type'      => 'checkbox',
			//     'title'     => __( 'Enable Bitcoin donations', 'charitable-stripe' ),
			//     'priority'  => 16,
			//     'attrs'     => array(
			//         'data-show-only-if-key' => 'charitable_settings_gateways_stripe_enable_stripe_checkout',
			//         'data-show-only-if-value' => 'checked'
			//     ),
			//     'help'      => __( 'Allow donors to pay using Bitcoin. <strong>Requires Stripe Checkout.</strong>', 'charitable-stripe' )
			// );

			// $settings['enable_alipay'] = array(
			//     'type'      => 'checkbox',
			//     'title'     => __( 'Enable Alipay donations', 'charitable-stripe' ),
			//     'priority'  => 18,
			//     'attrs'     => array(
			//         'data-show-only-if-key' => 'charitable_settings_gateways_stripe_enable_stripe_checkout',
			//         'data-show-only-if-value' => 'checked'
			//     ),
			//     'help'      => __( 'Allow donors to pay using Alipay. <strong>Requires Stripe Checkout.</strong>', 'charitable-stripe' )
			// );

			return $settings;
		}

		/**
		 * Returns the current gateway's ID.
		 *
		 * @return  string
		 * @access  public
		 * @static
		 * @since   1.0.3
		 */
		public static function get_gateway_id() {
			return self::ID;
		}

		/**
		 * Return the keys to use.
		 *
		 * This will return the test keys if test mode is enabled. Otherwise, returns
		 * the production keys.
		 *
		 * @param   boolean $force_test_mode Forces the test API keys to be used.
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_keys( $force_test_mode = false ) {

			$keys = array();

			if ( charitable_get_option( 'test_mode' ) || $force_test_mode ) {
				$keys['secret_key'] = trim( $this->get_value( 'test_secret_key' ) );
				$keys['public_key'] = trim( $this->get_value( 'test_public_key' ) );
			} else {
				$keys['secret_key'] = trim( $this->get_value( 'live_secret_key' ) );
				$keys['public_key'] = trim( $this->get_value( 'live_public_key' ) );
			}

			return $keys;
		}

		/**
		 * Load Stripe JS or Stripe Checkout, as well as our handling scripts.
		 *
		 * @return  boolean
		 * @access  public
		 * @static
		 * @since   1.1.0
		 */
		public static function enqueue_scripts() {

			if ( ! Charitable_Gateways::get_instance()->is_active_gateway( self::get_gateway_id() ) ) {
				return false;
			}

			if ( version_compare( charitable()->get_version(), '1.4.0', '<' ) ) {
				return self::enqueue_deprecated_scripts();
			}

			wp_enqueue_script( 'charitable-stripe-handler' );

			return true;
		}

		/**
		 * Enqueues deprecated scripts.
		 *
		 * These are only used when you are using a version of Charitable earlier than 1.4.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.1.4
		 */
		public function enqueue_deprecated_scripts() {

			if ( ! wp_script_is( 'charitable-donation-form', 'registered' ) ) {
				return false;
			}

			$gateway = new Charitable_Gateway_Stripe;

			if ( $gateway->get_value( 'enable_stripe_checkout' ) ) {
				wp_enqueue_script( 'charitable-stripe-checkout-handler' );
			} else {
				wp_enqueue_script( 'charitable-stripe-js-handler' );
			}

			return true;
		}

		/**
		 * Load Stripe JS or Stripe Checkout, as well as our handling scripts.
		 *
		 * @uses    Charitable_Gateway_Stripe::enqueue_scripts()
		 *
		 * @param   Charitable_Donation_Form $form The current form object.
		 * @return  boolean
		 * @access  public
		 * @static
		 * @since   1.1.2
		 */
		public static function maybe_setup_scripts_in_donation_form( $form ) {

			if ( ! is_a( $form, 'Charitable_Donation_Form' ) ) {
				return false;
			}

			if ( 'make_donation' !== $form->get_form_action() ) {
				return false;
			}

			return self::enqueue_scripts();
		}

		/**
		 * Enqueue the Stripe JS/Checkout scripts after a campaign loop if modal donations are in use.
		 *
		 * @uses    Charitable_Gateway_Stripe::enqueue_scripts()
		 *
		 * @return  boolean
		 * @access  public
		 * @static
		 * @since   1.1.2
		 */
		public static function maybe_setup_scripts_in_campaign_loop() {

			if ( 'modal' !== charitable_get_option( 'donation_form_display', 'separate_page' ) ) {
				return false;
			}

			return self::enqueue_scripts();
		}

		/**
		 * Return the submitted value for a gateway field.
		 *
		 * @param   string  $key The key of the value we want to get.
		 * @param   mixed[] $values An values in which to search.
		 * @return  string|false
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_gateway_value( $key, $values ) {

			if ( isset( $values['gateways']['stripe'][ $key ] ) ) {
				return $values['gateways']['stripe'][ $key ];
			}

			return false;
		}

		/**
		 * Return the submitted value for a gateway field.
		 *
		 * @param   string                        $key The key of the value we want to get.
		 * @param   Charitable_Donation_Processor $processor The Donation Processor helper object.
		 * @return  string|false
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_gateway_value_from_processor( $key, Charitable_Donation_Processor $processor ) {
			return $this->get_gateway_value( $key, $processor->get_donation_data() );
		}

		/**
		 * Add hidden token field to the donation form.
		 *
		 * @param   array $fields The donation form's hidden fields.
		 * @return  array $fields
		 * @access  public
		 * @static
		 * @since   1.1.0
		 */
		public static function add_hidden_token_field( $fields ) {

			if ( Charitable_Gateways::get_instance()->is_active_gateway( self::get_gateway_id() ) ) {
				$fields['stripe_token'] = '';
			}

			return $fields;

		}

		/**
		 * If a Stripe token was submitted, set it to the gateways array.
		 *
		 * @param   array $fields The filtered values from the donation form submission.
		 * @param   array $submitted The raw POST data.
		 * @return  array
		 * @access  public
		 * @static
		 * @since   1.1.0
		 */
		public static function set_submitted_stripe_token( $fields, $submitted ) {

			$token = isset( $submitted['stripe_token'] ) ? $submitted['stripe_token'] : false;
			$fields['gateways']['stripe']['token'] = $token;
			return $fields;

		}

		/**
		 * Returns an array of credit card fields.
		 *
		 * If the gateway requires different fields, this can simply be redefined
		 * in the child class.
		 *
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_credit_card_fields() {
			 /**
			 * If Stripe Checkout is enabled, remove the credit card fields.
			 */
			if ( $this->get_value( 'enable_stripe_checkout' ) ) {
				return array();
			}

			return parent::get_credit_card_fields();
		}

		/**
		 * Checks if credit card details are required.
		 *
		 * Credit card details are required UNLESS the donation is being
		 * made by a logged in user who has donated before and is using
		 * one of their stored cards (stored on Stripe's server, not ours).
		 *
		 * @param   array $values The filtered values from the donation form submission.
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function require_credit_card_details( $values ) {
			if ( $this->get_gateway_value( 'token', $values ) ) {
				return false;
			}

			if ( ! is_user_logged_in() ) {
				return true;
			}

			/* Unless a source is defined, credit card details are required */
			return $this->get_gateway_value( 'source', $values ) === false;
		}

		/**
		 * Validate the submitted credit card details.
		 *
		 * @param   boolean $valid Whether the donation is valid.
		 * @param   string  $gateway The chosen gateway.
		 * @param   mixed[] $values The filtered values from the donation form submission.
		 * @return  boolean
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function validate_donation( $valid, $gateway, $values ) {

			if ( 'stripe' !== $gateway ) {
				return $valid;
			}

			if ( ! isset( $values['gateways']['stripe'] ) ) {
				return false;
			}

			$gateway = new Charitable_Gateway_Stripe();

			$keys = $gateway->get_keys();

			/* Make sure that the keys are set. */
			if ( empty( $keys['secret_key'] ) || empty( $keys['public_key'] ) ) {

				charitable_get_notices()->add_error( __( 'Missing keys for Stripe payment gateway. Unable to proceed with payment.', 'charitable-stripe' ) );
				return false;

			}

			/* If the credit card details are required (i.e. not using existing source), ensure they are all set */
			if ( $gateway->require_credit_card_details( $values ) && ! $gateway->has_all_credit_card_details( $values ) ) {

				charitable_get_notices()->add_error( __( 'Missing credit card details. Unable to proceed with payment.', 'charitable-stripe' ) );
				return false;

			}

			return $valid;

		}

		/**
		 * Process the donation with the gateway, seamlessly over the Stripe API.
		 *
		 * @param   mixed                         $return The result of the gateway processing.
		 * @param   int                           $donation_id The donation ID.
		 * @param   Charitable_Donation_Processor $processor The Donation Processor helper.
		 * @return  boolean
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function process_donation( $return, $donation_id, $processor ) {
			$gateway = new Charitable_Gateway_Stripe();
			$keys    = $gateway->get_keys();

			\Stripe\Stripe::setApiKey( $keys['secret_key'] );

			$donation = new Charitable_Donation( $donation_id );
			$donor    = $donation->get_donor();
			$amount   = $donation->get_total_donation_amount( true );

			if ( is_wp_error( $amount ) ) {
				return false;
			}

			$currency    = charitable_get_currency();
			$campaigns   = $donation->get_campaigns_donated_to();
			$charge_args = array(
				'amount'               => $gateway->get_amount( $amount, $currency ),
				'currency'             => $currency,
				'description'          => html_entity_decode( $campaigns, ENT_COMPAT, 'UTF-8' ),
				'statement_descriptor' => substr( $campaigns, 0, 22 ),
				'metadata'      	   => array(
					'email'       => $donor->get_email(),
					'donation_id' => $donation_id,
					'name'		  => $donor->get_name(),
					'phone'		  => $donor->get_donor_meta( 'phone' ),
					'city'		  => $donor->get_donor_meta( 'city' ),
					'country' 	  => $donor->get_donor_meta( 'country' ),
					'address'	  => $donor->get_donor_meta( 'address' ),
					'address_2'   => $donor->get_donor_meta( 'address_2' ),
					'postcode'	  => $donor->get_donor_meta( 'postcode' ),
					'state'		  => $donor->get_donor_meta( 'state' ),
				),
			);

			/**
			 * Get the payment source for the transaction. This may be a Customer's stored card, a
			 * token or an array of credit card details.
			 */
			$charge_args['source'] = $gateway->get_source( $donor, $processor );

			if ( ! $charge_args['source'] ) {
				return false;
			}

			/**
			 * To support implementations where a single transaction might require
			 * multiple charges, such as a Stripe Connect charge where there are
			 * multiple destinations (i.e. funds recipients), we turn $charge_args
			 * into an array of arrays.
			 */
			$charges = array( $charge_args );
			$charges = apply_filters( 'charitable_stripe_charge_args', $charges, $donation, $processor, $gateway );

			$gateway->clear_charges();

			/**
			 * Make each charge.
			 */
			foreach ( $charges as $charge_args ) {
				$gateway->make_charge( $charge_args, $donor, $processor );
			}

			$charge_results         = $gateway->get_charges();
			$statuses               = wp_list_pluck( $charge_results, 'status' );
			$has_successful_charges = in_array( 'succeeded', $statuses, true ) || in_array( 'paid', $statuses, true );
			$has_failed_charges 	= in_array( 'failed', $statuses, true );
			$has_errors   			= in_array( 'error', $statuses, true );

			/**
			 * All charges either failed or errored out.
			 */
			if ( ! $has_successful_charges ) {

				/**
				 * We only had errors.
				 */
				if ( ! $has_failed_charges ) {
					return false;
				}

				$donation->update_status( 'charitable-failed' );

				return false;

			}

			/**
			 * Some charges succeeded, but not all.
			 */
			if ( $has_failed_charges || $has_errors ) {
				/**
				 * At this stage, it's not possible for a single donation
				 * to contain multiple charges, since all donations are
				 * for a single campaign.
				 *
				 * @todo
				 */
			}

			$new_status = apply_filters( 'charitable_stripe_donation_status', 'charitable-completed', $charge_args, $donation );

			$donation->update_status( $new_status );

			foreach ( wp_list_pluck( $charge_results, 'result' ) as $charge ) {

				/* Charge includes an application fee. */
				if ( ! is_null( $charge->application_fee ) ) {

					$donation->update_donation_log( sprintf( __( 'Stripe application fee: <a href="https://dashboard.stripe.com/%sapplications/fees/%s" target="_blank"><code>%s</code></a>', 'charitable-stripe' ),
						$charge->livemode ? '' : 'test/',
						$charge->application_fee,
						$charge->application_fee
					) );

				}

				/* Charge is on our account (not directly on a connected account). */
				if ( is_null( $charge->application ) || ! is_null( $charge->destination ) ) {

					$donation->update_donation_log( sprintf( __( 'Stripe charge: <a href="https://dashboard.stripe.com/%spayments/%s" target="_blank"><code>%s</code></a>', 'charitable-stripe' ),
						$charge->livemode ? '' : 'test/',
						$charge->id,
						$charge->id
					) );

				}
			}//end foreach

			return true;
		}

		/**
		 * Attemps a Stripe charge and returns the status of the charge.
		 *
		 * @param   array                         $charge_args The arguments for the charge API request.
		 * @param   Charitable_Donor              $donor       The current user.
		 * @param   Charitable_Donation_Processor $processor   The Donation Processor helper object.
		 * @return  string
		 * @access  public
		 * @since   1.1.0
		 */
		public function make_charge( $charge_args, $donor, $processor ) {

			$options = isset( $charge_args['options'] ) ? $charge_args['options'] : null;

			unset( $charge_args['options'] );

			/* Get (and maybe create) the donor's Stripe Customer ID. */
			$charge_args['customer'] = $this->get_stripe_customer( $donor, $processor );

			if ( ! $charge_args['customer'] ) {
				$this->save_charge_results( __( 'Unable to retrieve customer.', 'charitable-stripe' ), 'error' );
				return;
			}

			/**
			 * If a logged in user has made a donation with a card that wasn't already on file,
			 * we need to set it up as a card with Stripe first.
			 */
			if ( ! $this->get_gateway_value( 'source', $processor->get_donation_data() ) ) {
				$charge_args['source'] = $this->get_customer_card( $charge_args['customer'], $charge_args['source'], $options );

				if ( ! $charge_args['source'] ) {
					$this->save_charge_results( __( 'Unable to set a payment source for the charge.', 'charitable-stripe' ), 'error' );
					return;
				}
			}

			/**
			 * When charges are made directly against different Stripe accounts, the
			 * customer needs to be added to the connected Stripe account.
			 *
			 * @see 	https://stripe.com/docs/connect/shared-customers
			 */
			if ( is_array( $options ) && isset( $options['stripe_account'] ) ) {

				try {

					$token = \Stripe\Token::create(array(
						'customer' => $charge_args['customer'],
					), $options );

					$charge_args['source'] = $token->id;

					unset( $charge_args['customer'] );

				} catch ( Exception $e ) {

					$body = $e->getJsonBody();

					$message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Something went wrong.', 'charitable-stripe' );

					charitable_get_notices()->add_error( $message );

					$this->save_charge_results( $body, 'error' );

					return;

				}//end try

			}//end if

			/* We're ready to proceed with the charge now. */
			try {

		       	$charge = \Stripe\Charge::create( $charge_args, $options );

				$this->save_charge_results( $charge, $charge->status );

				/**
				 * If the charge failed, add an error message to be displayed.
				 */
				if ( 'failed' === $charge->status ) {

					$message = is_null( $charge->failure_message ) ? __( 'There was an error processing your payment. Please try again.', 'charitable-stripe' ) : $charge->failure_message;

					charitable_get_notices()->add_error( $message );
				}
			} catch ( Stripe_CardError $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] )
				? $body['error']['message']
				: __( 'There was an error processing your payment, please ensure you have entered your card number correctly.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$this->save_charge_results( $body, 'error' );

			} catch ( Stripe_ApiConnectionError $e ) {

				$body = $e->getJsonBody();

				$message = __( 'There was an error processing your payment (our payment gateways\'s API is down), please try again.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$this->save_charge_results( $body, 'error' );

			} catch ( Stripe_InvalidRequestError $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] )
					? $body['error']['message']
					: __( 'The payment gateway API request was invalid, please try again.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$this->save_charge_results( $body, 'error' );

			} catch ( Stripe_ApiError $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] )
					? $body['error']['message']
					: __( 'The payment gateway API request was invalid, please try again.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$this->save_charge_results( $body, 'error' );

			} catch ( Stripe_AuthenticationError $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] )
					? $body['error']['message']
					: __( 'The API keys entered in settings are incorrect', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$this->save_charge_results( $body, 'error' );

			} catch ( Stripe_Error $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Something went wrong.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$this->save_charge_results( $body, 'error' );

			} catch ( Exception $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Something went wrong.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$this->save_charge_results( $body, 'error' );

			}//end try
		}

		/**
		 * Set the $charges property to empty.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.1.0
		 */
		public function clear_charges() {
			$this->charges = array();
		}

		/**
		 * Return the results of all charges.
		 *
		 * @return  array
		 * @access  public
		 * @since   1.1.0
		 */
		public function get_charges() {
			return $this->charges;
		}

		/**
		 * Saves the results of a charge.
		 *
		 * @param   mixed  $result The result of a Stripe charge.
		 * @param   string $status The status of the charge.
		 * @return  void
		 * @access  public
		 * @since   1.1.0
		 */
		public function save_charge_results( $result, $status ) {
			$this->charges[] = array(
				'result' => $result,
				'status' => $status,
			);
		}

		/**
		 * Process an IPN request.
		 *
		 * @return  void
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function process_ipn() {
			/* Retrieve and validate the request's body. */
			$event_json = self::get_validated_incoming_event();

			if ( ! $event_json ) {
				status_header( 500 );
				die( __( 'Invalid Stripe event.', 'charitable-stripe' ) );
			}

			/* Set the API keys. If the event is set to livemode=false, we need to use the test API keys. */
			$gateway = new Charitable_Gateway_Stripe();
			$keys    = $gateway->get_keys( false === $event_json->livemode );

			\Stripe\Stripe::setApiKey( $keys['secret_key'] );

			try {
				status_header( 200 );

				/* This is Stripe's test webhook, so just die with a success message. */
				if ( 'evt_00000000000000' == $event_json->id ) {
					die( __( 'Test webhook successfully received.', 'charitable-stripe' ) );
				}

				/* If the event has an account set, this is a Stripe Connect event. */
				$options    = isset( $event_json->account ) ? array( 'stripe_account' => $event_json->account ) : array();
				$event 	    = \Stripe\Event::retrieve( $event_json->id, $options );
				$event_type = $event->type;

				do_action( 'charitable_stripe_ipn_event', $event_type, $event );

			} catch ( Exception $e ) {
				$body = $e->getJsonBody();
				error_log( $body['error']['message'] );
				status_header( 500 );

				die( __( 'Error while retrieving event.', 'charitable-stripe' ) );
			}//end try

			die( __( 'Unknown error.', 'charitable-stripe' ) );
		}

		/**
		 * Process a refund initiated via the Stripe dashboard.
		 *
		 * @see     https://stripe.com/docs/api#events
		 *
		 * @param   string $event_type The type of event.
		 * @param   object $event The event object received from Stripe.
		 * @return  void
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function process_refund( $event_type, $event ) {
			if ( 'charge.refunded' !== $event_type ) {
				return;
			}

			$charge = $event->data->object;
			$donation_id = $charge->metadata->donation_id;
			$refund = $charge->refunds->data[0];
			$refund_amount = $refund->amount;

			if ( ! self::is_zero_decimal_currency( $refund->currency ) ) {
				$refund_amount = $refund_amount / 100;
			}

			if ( Charitable::DONATION_POST_TYPE !== get_post_type( $donation_id ) ) {
				return;
			}

			$donation = new Charitable_Donation( $donation_id );
			$donation->process_refund( $refund_amount, __( 'Donation refunded from the Stripe dashboard.', 'charitable-stripe' ) );
		}

		/**
		 * Get the donation amount in the smallest common currency unit.
		 *
		 * @param 	float       $amount   The donation amount in dollars.
		 * @param 	string|null $currency The currency of the donation. If null, the site currency will be used.
		 * @return  int
		 * @access  public
		 * @static
		 * @since   1.2.3
		 */
		public static function get_amount( $amount, $currency = null ) {
			/* Unless it's a zero decimal currency, multiply the currency x 100 to get the amount in cents. */
			if ( self::is_zero_decimal_currency( $currency ) ) {
				$amount = $amount * 1;
			} else {
				$amount = $amount * 100;
			}

			return $amount;
		}

		/**
		 * Returns whether the currency is a zero decimal currency.
		 *
		 * @param 	string $currency The currency for the charge. If left blank, will check for the site currency.
		 * @return  bool
		 * @access  public
		 * @static
		 * @since   1.2.3
		 */
		public static function is_zero_decimal_currency( $currency = null ) {
			if ( is_null( $currency ) ) {
				$currency = charitable_get_currency();
			}

			return in_array( strtoupper( $currency ), self::get_zero_decimal_currencies() );
		}

		/**
		 * Return all zero-decimal currencies supported by Stripe.
		 *
		 * @return  array
		 * @access  public
		 * @static
		 * @since   1.2.3
		 */
		public static function get_zero_decimal_currencies() {
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

		/**
		 * Return the Stripe Customer ID for the current customer.
		 *
		 * If the donor has donated previously through Stripe, this will return
		 * their ID from the database. If not, this will first set them up as a
		 * customer in Stripe, store their customer ID and then return it.
		 *
		 * @see     https://stripe.com/docs/api#create_customer
		 *
		 * @param   Charitable_User|Charitable_Donor $donor The donor/user object for the logged in user.
		 * @param   Charitable_Donation_Processor    $processor The Donation Procesor helper.
		 * @return  string|false
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_stripe_customer( $donor, Charitable_Donation_Processor $processor ) {

			$key = charitable_get_option( 'test_mode' ) ? self::STRIPE_CUSTOMER_ID_KEY_TEST : self::STRIPE_CUSTOMER_ID_KEY;

			/**
			 * Retrieve current customer ID and verify that the customer still exists
			 * in Stripe.
			 */
			$stripe_customer_id = $donor->$key;

			if ( $stripe_customer_id ) {

				try {
					/* Retrieve the customer object from Stripe. */
					$cu = \Stripe\Customer::retrieve( $stripe_customer_id );

				} catch ( Stripe\Error\InvalidRequest $e ) {
					$cu = null;
				}

				if ( is_null( $cu ) || ( isset( $cu->deleted ) && $cu->deleted ) ) {
					$stripe_customer_id = false;
				}
			}

			/* No Stripe Customer ID found, so we're going to create one. */
			if ( ! $stripe_customer_id ) {

				$stripe_customer_id = $this->create_stripe_customer( $donor, $processor );

				/* Store the customer ID for logged in users. */
				if ( $stripe_customer_id && $donor->ID ) {
					update_user_meta( $donor->ID, $key, $stripe_customer_id );
				}
			}

			return $stripe_customer_id;
		}

		/**
		 * Create a Stripe Customer object through the API.
		 *
		 * @param   Charitable_Donor              $donor     The Donor object.
		 * @param   Charitable_Donation_Processor $processor The Donation Procesor helper.
		 * @return  string|false
		 * @access  public
		 * @since   1.2.2
		 */
		public function create_stripe_customer( $donor, Charitable_Donation_Processor $processor ) {

			$stripe_customer_args = apply_filters( 'charitable_stripe_customer_args', array(
				'description'   => sprintf( '%s %s', __( 'Donor for', 'charitable-stripe' ), $donor->get_email() ),
				'email'         => $donor->get_email(),
				'metadata'      => array(
					'donor_id'  => $processor->get_donor_id(),
					'user_id'   => $donor->ID,
				),
			), $donor, $processor );

			try {
				$customer = \Stripe\Customer::create( $stripe_customer_args );

				return $customer->id;

			} catch ( Exception $e ) {

				$body = $e->getJsonBody();
				$message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Something went wrong.', 'charitable-stripe' );
				charitable_get_notices()->add_error( $message );

				return false;

			}

		}

		/**
		 * Returns the payment source.
		 *
		 * This may return a string, identifying the ID of a payment source such as
		 * a credit card. It may also be an associative array containing the user's
		 * credit card details.
		 *
		 * @see     https://stripe.com/docs/api#create_charge
		 *
		 * @param   Charitable_User|Charitable_Donor $donor The donor/user object for the logged in user.
		 * @param   Charitable_Donation_Processor    $processor The Donation Procesor helper.
		 * @return  string|array
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_source( $donor, Charitable_Donation_Processor $processor ) {

			$values = $processor->get_donation_data();

			/**
			 * If the donation is made by a logged in user who selected
			 * a source (card), return that.
			 */
			if ( $this->get_gateway_value( 'source', $values ) ) {
				return $this->get_gateway_value( 'source', $values );
			}

			/**
			 * If we have a token available, return that.
			 */
			if ( $this->get_gateway_value( 'token', $values ) ) {
				return $this->get_gateway_value( 'token', $values );
			}

			/**
			 * If we're missing a source and a stripe_token, we need valid
			 * credit card details.
			 */
			if ( ! $this->has_all_credit_card_details( $values ) ) {
				charitable_get_notices()->add_error( __( 'Missing credit card details. Unable to proceed with payment.', 'charitable-stripe' ) );
				return false;
			}

			$cc_expiration = $this->get_gateway_value( 'cc_expiration', $values );

			$source = array(
				'object'            => 'card',
				'number'            => $this->get_gateway_value( 'cc_number', $values ),
				'name'              => $this->get_gateway_value( 'cc_name', $values ),
				'exp_month'         => $cc_expiration['month'],
				'exp_year'          => $cc_expiration['year'],
				'cvc'               => $this->get_gateway_value( 'cc_cvc', $values ),
				'address_line1'     => $donor->get( 'donor_address' ),
				'address_line2'     => $donor->get( 'donor_address_2' ),
				'address_city'      => $donor->get( 'donor_city' ),
				'address_zip'       => $donor->get( 'donor_postcode' ),
				'address_state'     => $donor->get( 'donor_state' ),
				'address_country'   => $donor->get( 'donor_country' ),
			);

			return apply_filters( 'charitable_stripe_source_args', $source, $donor, $processor );
		}

		/**
		 * Returns a card ID for the customer.
		 *
		 * @param   string $customer Stripe's customer ID.
		 * @param   string $card     The customer's card details or token.
		 * @return  string|false Card ID or false if Stripe returns an error.
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_customer_card( $customer, $card ) {

			try {

				$cu = \Stripe\Customer::retrieve( $customer );
				$card = $cu->sources->create( array( 'source' => $card ) );
				$card_id = $card->id;

			} catch ( Exception $e ) {

				$body = $e->getJsonBody();
				$message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Something went wrong.', 'charitable-stripe' );
				charitable_get_notices()->add_error( $message );
				$card_id = false;

			}

			return $card_id;
		}

		/**
		 * Returns true if all credit card details are provided.
		 *
		 * @param   array $values The filtered values from the donation form submission.
		 * @return  boolean
		 * @access  private
		 * @since   1.0.0
		 */
		private function has_all_credit_card_details( $values ) {
			if ( ! isset( $values['gateways']['stripe'] ) ) {
				return false;
			}

			$stripe_values = $values['gateways']['stripe'];

			if ( empty( $stripe_values['cc_name'] ) ) {
				return false;
			}

			if ( empty( $stripe_values['cc_number'] ) ) {
				return false;
			}

			if ( empty( $stripe_values['cc_expiration'] ) || ! isset( $stripe_values['cc_expiration']['month'] ) || ! isset( $stripe_values['cc_expiration']['year'] ) ) {
				return false;
			}

			if ( empty( $stripe_values['cc_cvc'] ) ) {
				return false;
			}

			return true;
		}

		/**
		 * For an IPN request, get the validated incoming event object.
		 *
		 * @since  1.2.0
		 *
		 * @return false|object If valid, returns an object. Otherwise false.
		 */
		private static function get_validated_incoming_event() {
			$body       = @file_get_contents( 'php://input' );
			$event_json = json_decode( $body );

			if ( ! is_object( $event_json ) || ! isset( $event_json->id ) ) {
				return false;
			}

			return $event_json;
		}

		/**
		 * This is required for compatibility with Charitable before version 1.3.
		 *
		 * @param 	int $donation_id The donation ID.
		 * @return 	void
		 *
		 * @deprecated
		 */
		public static function redirect_to_donation_form( $donation_id ) {
			charitable_get_session()->add_notices();
			$redirect_url = esc_url( add_query_arg( array( 'donation_id' => $donation_id ), wp_get_referer() ) );
			wp_safe_redirect( $redirect_url );
			die();
		}

		/**
		 * This is required for compatibility with Charitable before version 1.3.
		 *
		 * @param 	int 						  $donation_id The donation ID.
		 * @param   Charitable_Donation_Processor $processor The Donation Processor helper object.
		 *
		 * @deprecated
		 */
		public static function process_donation_legacy( $donation_id, $processor ) {
			$result = self::process_donation( true, $donation_id, $processor );

			/**
			 * A false result means we need to be redirected back to
			 * the donation form.
			 */
			if ( ! $result ) {
				self::redirect_to_donation_form( $donation_id );
			}
		}
	}

endif;
