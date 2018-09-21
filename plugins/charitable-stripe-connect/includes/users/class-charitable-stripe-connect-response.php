<?php
/**
 * Processes the responses from Stripe Connect.
 *
 * @version     1.0.0
 * @package     Charitable Stripe Connect/Classes/Stripe Connect Response
 * @category    Class
 * @author      Eric Daams
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Connect_Response' ) ) :

	/**
	 * Charitable_Stripe_Connect_Response class.
	 *
	 * @since       1.0.0
	 */
	class Charitable_Stripe_Connect_Response {

		/**
		 * The Stripe OAuth URL.
		 *
		 * @since   1.0.0
		 */
		const STRIPE_CONNECT_TOKEN_URI = 'https://connect.stripe.com/oauth/token';

		/**
		 * Possible response codes we want to catch from Stripe.
		 *
		 * @var     string[] $response_keys
		 * @access  private
		 * @since   1.0.0
		 */
		private $response_keys = array( 'scope', 'code', 'state', 'error', 'error_description' );

		/**
		 * Response data.
		 *
		 * @var     array $response
		 * @access  private
		 * @since   1.0.0
		 */
		private $response;

		/**
		 * Stripe Connect User object.
		 *
		 * @var     Charitable_Stripe_Connect_User
		 * @access  private
		 * @since   1.0.0
		 */
		private $stripe_connect_user;

		/**
		 * Create class object.
		 *
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct() {
			foreach ( $this->response_keys as $key ) {
				if ( ! isset( $_REQUEST[ $key ] ) ) {
					continue;
				}

				$this->response[ $key ] = $_REQUEST[ $key ];
			}
		}

		/**
		 * Process a response after the user has been redirected to our site.
		 *
		 * This will return false if this was not a valid response. If it was a
		 * valid response, we will ask Stripe for the user's details as a POST
		 * request, and save whatever they give us back (even if it's an error).
		 *
		 * @see     https://stripe.com/docs/connect/standalone-accounts
		 *
		 * @return  false|array
		 * @access  public
		 * @since   1.0.0
		 */
		public function process() {

			/* First of all, we need to validate the response. */
			if ( ! $this->validate() ) {
				return false;
			}

			$gateway = Charitable_Gateways::get_instance()->get_gateway_object( 'stripe' );

			if ( is_null( $gateway ) ) {
				return false;
			}

			$keys = $gateway->get_keys();

			$args = array(
				'grant_type' => 'authorization_code',
				'client_id' => charitable_stripe_connect_get_client_id(),
				'code' => $this->get( 'code' ),
				'client_secret' => $keys['secret_key'],
			);

			$request = wp_remote_post( self::STRIPE_CONNECT_TOKEN_URI, array(
				'body'       => http_build_query( $args ),
				'user-agent' => 'WPCharitable',
			) );

			if ( is_wp_error( $request ) ) {
				return false;
			}

			$response = json_decode( wp_remote_retrieve_body( $request ), true );

			if ( ! $this->response_has_required_details( $response ) ) {
				return false;
			}

			$this->get_stripe_connect_user()->save_user_stripe_details( $response );

			do_action( 'charitable_stripe_connect_user_connected', $this, $response );

			return true;

		}

		/**
		 * Validates the response as being a Stripe Connect redirect.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function validate() {
			if ( ! isset( $this->response['code'] ) ) {
				return false;
			}

			if ( ! isset( $this->response['state'] ) ) {
				return false;
			}

			if ( ! is_user_logged_in() ) {
				return false;
			}

			return $this->get_stripe_connect_user()->validate_token( $this->response['state'] );
		}

		/**
		 * Returns the value of the given key or false if it was not set.
		 *
		 * @param   string $key The key we're searching for.
		 * @return  string|false
		 * @access  public
		 * @since   1.0.0
		 */
		public function get( $key ) {
			return isset( $this->response[ $key ] ) ? $this->response[ $key ] : false;
		}

		/**
		 * Checks if there was an error, either in the initial redirect from Stripe or the subsequent token request.
		 *
		 * @return  false|array Returns false if there is no error.
		 * @access  public
		 * @since   1.0.0
		 */
		public function error() {
			if ( $this->get( 'error' ) ) {
				return $this->response;
			}

			$user_stripe_details = $this->get_stripe_connect_user()->get();

			if ( ! $user_stripe_details ) {
				return array(
					'error' => __( 'unkown_error' ),
					'error_description' => __( 'Connection failed for unknown reason.', 'charitable-stripe-connect' ),
				);
			}

			if ( isset( $user_stripe_details['error'] ) ) {
				return $user_stripe_details;
			}

			return false;
		}

		/**
		 * Get the error message to display to users.
		 *
		 * @return  false|string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_error_message() {
			$error = $this->error();

			if ( ! $error ) {
				return false;
			}

			switch ( $error['error'] ) {

				/* User cancelled the connection from Stripe. */
				case 'access_denied' :
					$message = __( 'You did not connect your Stripe account.', 'charitable-stripe-connect' );
					break;

				/* Other error. */
				default :
					$message = __( 'Your Stripe account failed to be connected.', 'charitable-stripe-connect' );
			}

			return $message;
		}

		/**
		 * Return the Stripe Connect user object for the current logged in user.
		 *
		 * @return  Charitable_Stripe_Connect_User
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_stripe_connect_user() {
			if ( ! isset( $this->stripe_connect_user ) ) {
				$this->stripe_connect_user = new Charitable_Stripe_Connect_User( get_current_user_id() );
			}

			return $this->stripe_connect_user;
		}

		/**
		 * Check whether the response from Stripe contains the data we expect.
		 *
		 * @since  1.0.5
		 *
		 * @param  array $response The response from Stripe.
		 * @return boolean
		 */
		private function response_has_required_details( $response ) {
			return is_array( $response ) && array_key_exists( 'access_token', $response );
		}
	}

endif;
