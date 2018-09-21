<?php
/**
 * Add recurring donations support.
 *
 * @version     1.2.0
 * @package     Charitable Stripe/Classes/Charitable_Stripe_Recurring
 * @author      Eric Daams
 * @copyright   Copyright (c) 2018, Eric Daams
 * @license     http://opensource.org/licenses/gpl-3.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Recurring' ) ) :

	/**
	 * Stripe Payment Gateway support
	 *
	 * @since       1.2.0
	 */
	class Charitable_Stripe_Recurring {

		/**
		 * The single instance of this class.
		 *
		 * @var     Charitable_Stripe_Recurring|null
		 * @access  private
		 * @static
		 */
		private static $instance = null;

		/**
		 * Returns and/or create the single instance of this class.
		 *
		 * @return  Charitable_Stripe_Recurring
		 * @access  public
		 * @since   1.2.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Stripe_Recurring();
			}

			return self::$instance;
		}

		/**
		 * Add subscription data for the donation to the transaction object.
		 *
		 * @param   mixed                         $return The result of the gateway processing.
		 * @param   int                           $donation_id The donation ID.
		 * @param   Charitable_Donation_Processor $processor The Donation Processor helper.
		 * @return  boolean
		 * @access  public
		 * @since   1.2.0
		 */
		public function maybe_process_recurring_donation( $return, $donation_id, Charitable_Donation_Processor $processor ) {

			/* Bail straight away if no donation plan is set. */
			if ( ! $processor->get_donation_data_value( 'donation_plan', false ) ) {
				return $return;
			}

			$recurring_id = $processor->get_donation_data_value( 'donation_plan' );

			if ( ! $recurring_id ) {
				return $return;
			}

			/**
			 * First of all, let's cancel the subsequent transaction
			 * processing since this is a recurring donation.
			 */
			remove_action( 'charitable_process_donation_stripe', array( 'Charitable_Gateway_Stripe', 'process_donation_legacy' ), 10, 2 );
			remove_filter( 'charitable_process_donation_stripe', array( 'Charitable_Gateway_Stripe', 'process_donation' ), 10, 3 );

			$recurring 	  = charitable_get_donation( $recurring_id );
			$donation     = new Charitable_Donation( $donation_id );
			$donor        = $donation->get_donor();
			$campaign_id  = current( $donation->get_campaign_donations() )->campaign_id;
			$gateway 	  = new Charitable_Gateway_Stripe();
			$gateway_id   = $gateway::get_gateway_id();
			$keys         = $gateway->get_keys();

			\Stripe\Stripe::setApiKey( $keys['secret_key'] );

			/* Check for a plan already existing. */
			$plan_id = charitable_recurring_campaign_get_gateway_plan_id(
				$campaign_id,
				$gateway_id,
				array( 'processor' => $processor )
			);

			/* Create the plan if it doesn't exist. */
			if ( ! $plan_id ) {

				/* Save the plan ID since it was successfully created in Stripe. */
				$plan_id = charitable_recurring_campaign_create_gateway_plan_id(
					$campaign_id,
					$gateway_id,
					array( 'processor' => $processor )
				);
			}

			/* Create the customer. */
			$customer = $gateway->get_stripe_customer( $donor, $processor );

			/* Create the subscription. */
			try {

				$subscription = \Stripe\Subscription::create( array(
					'customer' => $customer,
					'plan' 	   => $plan_id,
					'source'   => $gateway->get_source( $donor, $processor ),
				) );

				/* Save the subscription ID. */
				$recurring->set_gateway_subscription_id( $subscription->id );

				$recurring->update_donation_log( sprintf( __( 'Stripe subscription ID: <a href="https://dashboard.stripe.com/%ssubscriptions/%s" target="_blank"><code>%s</code></a>', 'charitable-stripe' ),
					$subscription->livemode ? '' : 'test/',
					$subscription->id,
					$subscription->id
				) );

				$status = $this->get_subscription_status( $subscription );

			} catch ( Stripe_CardError $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] )
					? $body['error']['message']
					: __( 'There was an error processing your payment, please ensure you have entered your card number correctly.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$status = 'charitable-failed';

			} catch ( Stripe_ApiConnectionError $e ) {

				$body = $e->getJsonBody();

				$message = __( 'There was an error processing your payment (our payment gateways\'s API is down), please try again.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$subscription->set_to_failed( __( 'Initial subscription payment failed.', 'charitable-stripe' ) );

			} catch ( Stripe_InvalidRequestError $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] )
					? $body['error']['message']
					: __( 'The payment gateway API request was invalid, please try again.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$status = 'charitable-failed';

			} catch ( Stripe_ApiError $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] )
					? $body['error']['message']
					: __( 'The payment gateway API request was invalid, please try again.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$status = 'charitable-failed';

			} catch ( Stripe_AuthenticationError $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] )
					? $body['error']['message']
					: __( 'The API keys entered in settings are incorrect', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$status = 'charitable-failed';

			} catch ( Stripe_Error $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Something went wrong.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$status = 'charitable-failed';

			} catch ( Exception $e ) {

				$body = $e->getJsonBody();

				$message = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Something went wrong.', 'charitable-stripe' );

				charitable_get_notices()->add_error( $message );

				$status = 'charitable-failed';

			}//end try

			if ( 'charitable-failed' == $status ) {
				$recurring->set_to_failed( __( 'Initial subscription payment failed', 'charitable-stripe' ) );
				return false;
			}

			$recurring->update_status( $status );

			return true;

		}

		/**
		 * Create a recurring donation plan in Stripe.
		 *
		 * @param 	mixed $return      The default return value.
		 * @param 	int   $campaign_id The campaign ID.
		 * @param 	array $plan_args   The plan parameters/arguments.
		 * @param 	array $args 	   Other arguments related to the donation.
		 * @return  string|false
		 * @access  public
		 * @since   1.2.0
		 */
		public function create_recurring_donation_plan( $return, $campaign_id, $plan_args, $args ) {
			$currency				  = charitable_get_currency();
			$is_zero_decimal_currency = Charitable_Gateway_Stripe::is_zero_decimal_currency( $currency );
			$period                   = $this->get_period( $plan_args );
			$interval                 = $this->get_interval( $plan_args );
			$amount                   = $plan_args['amount'];

			/* In older versions of Recurring Donations, we multiplied the amount by 100. */
			if ( version_compare( charitable_recurring()->get_version(), '1.0.5', '<' ) ) {
				if ( $is_zero_decimal_currency ) {
					$amount = $plan_args['amount'] / 100;
				} else {
					$amount = $plan_args['amount'];
				}
			} else {
				$amount = Charitable_Gateway_Stripe::get_amount( $amount, $currency );
			}

			$amount_description = strval( $is_zero_decimal_currency ? $amount : $amount / 100 );
			$plan_id            = $period . '-' . $interval . '-' . $amount . $currency . '-' . $campaign_id;
			$plan_name          = sprintf(
				_x( '%s - %s %s every %s', 'campaign title — amount every period', 'charitable-stripe' ),
				get_the_title( $campaign_id ),
				charitable_sanitize_amount( $amount_description ),
				$currency,
				charitable_recurring_get_donation_periods_i18n( $interval, $period )
			);

			/* Check whether the plan may have been created before. */
			$plans    = get_post_meta( $campaign_id, 'stripe_donation_plans', true );
			$plan_key = charitable_recurring_get_plan_key( $plan_args );
			$mode     = charitable_get_option( 'test_mode' ) ? 'test' : 'live';

			/* The plan exists in the database, so let's check whether it exists in Stripe. */
			if ( is_array( $plans ) && isset( $plans[ $mode ][ $plan_key ] ) ) {

				try {

					$plan = \Stripe\Plan::retrieve( $plan_id );

					return $plan_id;

				} catch ( Exception $e ) {

					/* The plan doesn't exist in Stripe, so we still need to create it. Do nothing. */

				}//end try

			}//end if

			$product_id = $this->get_campaign_stripe_product_id( $campaign_id );

			if ( ! $product_id ) {
				return false;
			}

			try {

				$plan = \Stripe\Plan::create( array(
					'id'             => $plan_id,
					'interval'       => $period,
					'interval_count' => $interval,
					'currency'       => $currency,
					'amount'         => $amount,
					'product'        => $product_id,
				) );

				return $plan_id;

			} catch ( Exception $e ) {

				/* Log the error message and return false. */
				error_log( 'STRIPE - Error creating plan: ' . $e->getMessage() );

				return false;

			}//end try

		}

		/**
		 * Get the Stripe product ID for a campaign, creating the product if necessary.
		 *
		 * @since  1.2.4
		 *
		 * @param  int $campaign_id The campaign ID.
		 * @return string|false
		 */
		public function get_campaign_stripe_product_id( $campaign_id ) {
			$product_id = get_post_meta( $campaign_id, 'stripe_product_id', true );

			/* The product may have been deleted within Stripe, so make sure we can retrieve it. */
			if ( $product_id ) {
				try {
					$product = \Stripe\Product::retrieve( $product_id );

					return $product_id;

				} catch ( Exception $e ) {
				}
			}

			/* No product could be retrieved, so we need to create one. */
			try {
				$product = \Stripe\Product::create( array(
					'name'                 => get_the_title( $campaign_id ),
					'type'                 => 'service',
					'statement_descriptor' => substr( get_the_title( $campaign_id ), 0, 22 ),
					'metadata'             => array(
						'campaign_id' => $campaign_id,
					),
				) );

				update_post_meta( $campaign_id, 'stripe_product_id', $product->id );

				return $product->id;

			} catch ( Exception $e ) {
				/* Log the error message and return false. */
				error_log( 'STRIPE - Error creating product: ' . $e->getMessage() );

				return false;
			}
		}

		/**
		 * Process subscription-related webhooks.
		 *
		 * @see     https://stripe.com/docs/api#events
		 *
		 * @param   string $event_type The type of event.
		 * @param   object $event      The event object received from Stripe.
		 * @return  void
		 * @access  public
		 * @since   1.2.0
		 */
		public function process_webhooks( $event_type, $event ) {

			$webhooks = array(
				'invoice.created' => 'process_invoice_created',
				'invoice.payment_failed' => 'process_invoice_payment_failed',
				'invoice.payment_succeeded' => 'process_invoice_payment_succeeded',
				'customer.subscription.updated' => 'process_customer_subscription_updated',
				'customer.subscription.deleted' => 'process_customer_subscription_deleted',
			);

			if ( ! array_key_exists( $event_type, $webhooks ) ) {
				return;
			}

			call_user_func( array( $this, $webhooks[ $event_type ] ), $event );

		}

		/**
		 * Process the invoice.created webhook.
		 *
		 * @param 	object $event The Stripe event object.
		 * @return  void
		 * @access  public
		 * @since   1.2.0
		 */
		public function process_invoice_created( $event ) {

			$invoice      = $event->data->object;

			$subscription = charitable_recurring_get_subscription_by_gateway_id( $invoice->subscription, 'stripe' );

			if ( empty( $subscription ) ) {
				die( __( 'Recurring Donation IPN: Missing subscription', 'charitable-stripe' ) );
			}

			/* Record the invoice in the subscription. */
			$subscription->update_donation_log( sprintf( __( 'New invoice created for the subscription: <a href="https://dashboard.stripe.com/%sinvoices/%s" target="_blank"><code>%s</code></a>', 'charitable-stripe' ),
				$invoice->livemode ? '' : 'test/',
				$invoice->id,
				$invoice->id
			) );

			die( __( 'Recurring Donation IPN: Invoice created' ) );

		}

		/**
		 * Process the invoice.payment_failed webhook.
		 *
		 * @param 	object $event The Stripe event object.
		 * @return  void
		 * @access  public
		 * @since   1.2.0
		 */
		public function process_invoice_payment_failed( $event ) {

			$invoice      = $event->data->object;
			$subscription = charitable_recurring_get_subscription_by_gateway_id( $invoice->subscription, 'stripe' );

			if ( empty( $subscription ) ) {
				die( __( 'Recurring Donation IPN: Missing subscription', 'charitable-stripe' ) );
			}

			$subscription->set_to_failed( sprintf( __( 'Payment for invoice %s failed. Stripe charge: <a href="https://dashboard.stripe.com/%spayments/%s" target="_blank"><code>%s</code></a>', 'charitable-stripe' ),
				$invoice->id,
				$invoice->livemode ? '' : 'test/',
				$invoice->charge,
				$invoice->charge
			) );

			die( __( 'Recurring Donation IPN: Invoice payment failed', 'charitable-stripe' ) );

		}

		/**
		 * Process the invoice.payment_succeeded webhook.
		 *
		 * @param 	object $event The Stripe event object.
		 * @return  void
		 * @access  public
		 * @since   1.2.0
		 */
		public function process_invoice_payment_succeeded( $event ) {

			$invoice      = $event->data->object;

			$subscription = charitable_recurring_get_subscription_by_gateway_id( $invoice->subscription, 'stripe' );

			if ( empty( $subscription ) ) {
				die( __( 'Recurring Donation IPN: Missing subscription', 'charitable-stripe' ) );
			}

			/* The first donation is pending, which means this is the payment for that webhook. */
			$first_donation = $subscription->get_first_donation_id();

			if ( 'charitable-pending' == get_post_status( $first_donation ) ) {

				$donation = charitable_get_donation( $first_donation );
				$donation->update_status( 'charitable-completed' );

			} else {

				$donation_id = $subscription->create_renewal_donation( array( 'status' => 'charitable-completed' ) );
				$donation    = charitable_get_donation( $donation_id );

			}

			$donation->set_gateway_transaction_id( $invoice->charge );

			$donation->update_donation_log( sprintf( __( 'Stripe charge: <a href="https://dashboard.stripe.com/%spayments/%s" target="_blank"><code>%s</code></a>', 'charitable-stripe' ),
				$invoice->livemode ? '' : 'test/',
				$invoice->charge,
				$invoice->charge
			) );

			/* Store the donation_id in the charge's metadata to support refunds. */
			try {

				$charge = \Stripe\Charge::retrieve( $invoice->charge );
				$charge->metadata->donation_id = $donation->ID;
				$charge->save();

			} catch ( Exception $e ) {
				$donation->update_donation_log( __( 'Unable to save donation ID to Stripe charge metadata.', 'charitable-stripe' ) );
			}

			die( __( 'Recurring Donation IPN: Payment complete', 'charitable-stripe' ) );

		}

		/**
		 * Process the customer.subscription.updated webhook.
		 *
		 * @param 	object $event The Stripe event object.
		 * @return  void
		 * @access  public
		 * @since   1.2.0
		 */
		public function process_customer_subscription_updated( $event ) {

			$object 	  = $event->data->object;
			$subscription = charitable_recurring_get_subscription_by_gateway_id( $object->id, 'stripe' );

			if ( empty( $subscription ) ) {
				die( __( 'Recurring Donation IPN: Missing subscription', 'charitable-stripe' ) );
			}

			$status = $this->get_subscription_status( $object->status );

			if ( 'charitable-failed' == $status ) {
				$subscription->set_to_failed();
			} else {
				$subscription->update_status( $status );
			}

			die( __( 'Recurring Donation IPN: Recurring donation updated', 'charitable-stripe' ) );

		}

		/**
		 * Process the customer.subscription.deleted webhook.
		 *
		 * @param 	object $event The Stripe event object.
		 * @return  void
		 * @access  public
		 * @since   1.2.0
		 */
		public function process_customer_subscription_deleted( $event ) {

			$object 	  = $event->data->object;
			$subscription = charitable_recurring_get_subscription_by_gateway_id( $object->id, 'stripe' );

			if ( empty( $subscription ) ) {
				die( __( 'Recurring Donation IPN: Missing subscription', 'charitable-stripe' ) );
			}

			$subscription->update_status( 'charitable-cancelled' );

			die( __( 'Recurring Donation IPN: Recurring donation cancelled', 'charitable-stripe' ) );

		}

		/**
		 * Get a donation by gateway transaction ID.
		 *
		 * @param 	string $transaction_id The gateway transaction ID.
		 * @return  int|null
		 * @access  private
		 * @since   1.2.0
		 */
		private function get_donation_by_gateway_transaction_id( $transaction_id ) {

			if ( function_exists( 'charitable_get_donation_by_transaction_id' ) ) {
				return charitable_get_donation_by_transaction_id( $transaction_id );
			}

			global $wpdb;

			$sql = "SELECT post_id 
					FROM $wpdb->postmeta 
					WHERE meta_key = '_gateway_transaction_id' 
					AND meta_value = %s";

			return $wpdb->get_var( $wpdb->prepare( $sql, $transaction_id ) );

		}

		/**
		 * Given a Stripe subscription, return the Charitable subscription status.
		 *
		 * @param   object $subscription The subscription object from Stripe.
		 * @return  string
		 * @access  public
		 * @since   1.2.0
		 */
		public function get_subscription_status( $subscription ) {

			switch ( $subscription->status ) {

				case 'active' :
					$status = 'charitable-active';
					break;

				case 'past_due' :
				case 'canceled' :
				case 'unpaid' :
					$status = 'charitable-failed';
					break;

				default :
					$status = 'charitable-pending';

			}

			return apply_filters( 'charitable_stripe_recurring_subscription_status', $status, $subscription->status );

		}

		/**
		 * Return the Stripe period given a set of plan args.
		 *
		 * @since  1.2.4
		 *
		 * @param  array $args The plan args.
		 * @return string
		 */
		public function get_period( $args ) {
			switch ( $args['period'] ) {
				case 'month':
				case 'quarter':
				case 'semiannual':
					$period = 'month';
					break;

				default:
					$period = $args['period'];
			}

			return $period;
		}

		/**
		 * Return the Stripe billing interval, given a set of plan args.
		 *
		 * @since  1.2.4
		 *
		 * @param  array $args The plan args.
		 * @return int
		 */
		public function get_interval( $args ) {
			switch ( $args['period'] ) {
				case 'quarter':
					$interval = 3;
					break;

				case 'semiannual':
					$interval = 6;
					break;

				default:
					$interval = $args['interval'];
			}

			return $interval;
		}
	}

endif;
