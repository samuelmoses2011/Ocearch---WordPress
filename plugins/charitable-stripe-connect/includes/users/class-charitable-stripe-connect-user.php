<?php
/**
 * A model to get and set Stripe details relating to a user.
 *
 * @package     Charitable Stripe Connect/Classes/Charitable_Stripe_Connect_User
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Connect_User' ) ) :

	/**
	 * Charitable_Stripe_Connect_User
	 *
	 * @since       1.0.0
	 */
	class Charitable_Stripe_Connect_User {

		/**
		 * Key used to store a user's Stripe details.
		 *
		 * @since   1.0.0
		 */
		const STRIPE_DETAILS_KEY = 'stripe_connect_details';

		/**
		 * Key used to store a user's Stripe token.
		 *
		 * @since   1.0.0
		 */
		const STRIPE_TOKEN_KEY = 'stripe_connect_token';

		/**
		 * User ID.
		 *
		 * @var     int $user_id
		 */
		private $user_id;

		/**
		 * User object.
		 *
		 * @var     Charitable_User $user
		 */
		private $user;

		/**
		 * Array of Stripe details for the user.
		 *
		 * @var     array $stripe_details
		 */
		private $stripe_details;

		/**
		 * Create class object.
		 *
		 * @param   int $user_id User ID.
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $user_id ) {
			$this->user_id = $user_id;
		}

		/**
		 * Checks whether the user is already connected.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function is_user_connected() {
			$stripe_details = $this->get();

			return $stripe_details && ! isset( $stripe_details['error'] );
		}

		/**
		 * Returns the response we received from Stripe after the user connected their account.
		 *
		 * @param   string $key Optional key to just fetch a particular setting from the response.
		 * @return  mixed|false Returns false if the key is not found or the details are not set at all.
		 * @access  public
		 * @since   1.0.0
		 */
		public function get( $key = '' ) {
			if ( ! isset( $this->stripe_details ) ) {
				$this->stripe_details = get_user_meta( $this->user_id, self::STRIPE_DETAILS_KEY, true );
			}

			if ( empty( $this->stripe_details ) ) {
				return false;
			}

			if ( empty( $key ) ) {
				return $this->stripe_details;
			}

			if ( ! isset( $this->stripe_details[ $key ] ) ) {
				return false;
			}

			return $this->stripe_details[ $key ];
		}

		   /**
			* Returns Charitable_User object for the current user.
			*
			* @return 	Charitable_User
			* @access 	public
			* @since    1.0.0
			*/
		public function get_user() {
			if ( ! isset( $this->user ) ) {
				$this->user = new Charitable_User( $this->user_id );
			}

			return $this->user;
		}

		/**
		 * Save the response we receive from Stripe after the user connects their account.
		 *
		 * @param 	mixed $response The response we received from Stripe.
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_user_stripe_details( $response ) {
			update_user_meta( $this->user_id, self::STRIPE_DETAILS_KEY, $response );
		}

		/**
		 * Return the URL to be redirected to when we go to Stripe.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_redirect_url() {
			$client_id = charitable_stripe_connect_get_client_id();

			/* Give up if the client ID isn't set. */
			if ( empty( $client_id ) ) {
				return false;
			}

			$token = $this->generate_token();

			$this->save_token( $token );

			$user = $this->get_user();

			$request_args = array(
			'response_type' => 'code',
			'client_id' => $client_id,
			'scope' => 'read_write',
			'state' => $token,
			);

			$extra_args = apply_filters( 'charitable_stripe_connect_prefill_args', array(
				'redirect_uri' => home_url(),
				'stripe_user[email]' => $user->get_email(),
				'stripe_user[phone_number]' => $user->get( 'donor_phone' ),
				'stripe_user[first_name]' => $user->first_name,
				'stripe_user[last_name]' => $user->last_name,
				'stripe_user[street_address]' => $user->get( 'donor_address' ),
				'stripe_user[city]' => $user->get( 'donor_city' ),
				'stripe_user[state]' => $user->get( 'donor_state' ),
				'stripe_user[zip]' => $user->get( 'donor_postcode' ),
				'stripe_user[physical_product]' => false,
			), $this );

			if ( is_array( $extra_args ) ) {
				$request_args = array_merge( $request_args, $extra_args );
			}

			return 'https://connect.stripe.com/oauth/authorize?' . http_build_query( $request_args );
		}

		/**
		 * Save the token in the user's meta.
		 *
		 * @param   string $token The user's Stripe token.
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_token( $token ) {
			set_transient( self::STRIPE_TOKEN_KEY . '_' . $this->user_id, $token, DAY_IN_SECONDS );
		}

		/**
		 * Validate the token passed through Stripe.
		 *
		 * @param   string $token The Stripe token to be validated.
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function validate_token( $token ) {
			return get_transient( self::STRIPE_TOKEN_KEY . '_' . $this->user_id ) == $token;
		}

		/**
		 * Check whether the user's access token is valid for the current mode (test/production).
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function is_token_valid_for_current_mode() {
			if ( charitable_get_option( 'test_mode' ) ) {
				return $this->get( 'livemode' ) === false;
			}

			return $this->get( 'livemode' ) === true;
		}

		/**
		 * Generate a token that we can pass through to Stripe to
		 *
		 * @return  string
		 * @access  private
		 * @since   1.0.0
		 */
		private function generate_token() {
			return 'stripe-' . md5( uniqid() );
		}
	}

endif;
