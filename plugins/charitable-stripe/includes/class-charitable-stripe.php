<?php
/**
 * The main Charitable Stripe class.
 *
 * The responsibility of this class is to load all the plugin's functionality.
 *
 * @package   Charitable Stripe
 * @copyright Copyright (c) 2018, Eric Daams
 * @license   http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @since     1.0.0
 * @version   1.2.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe' ) ) :

	/**
	 * Charitable_Stripe
	 *
	 * @since   1.0.0
	 */
	class Charitable_Stripe {

		/**
		 * The plugin version.
		 */
		const VERSION = '1.2.4';

		/**
		 * The plugin database version.
		 */
		const DB_VERSION = '20150922';

		/**
		 * The product name.
		 */
		const NAME = 'Charitable Stripe';

		/**
		 * The plugin author name.
		 */
		const AUTHOR = 'Studio 164a';

		/**
		 * The one and only class instance.
		 *
		 * @var 	Charitable_Stripe
		 * @static
		 */
		private static $instance = null;

		/**
		 * The root file of the plugin.
		 *
		 * @var     string
		 */
		private $plugin_file;

		/**
		 * The root directory of the plugin.
		 *
		 * @var     string
		 */
		private $directory_path;

		/**
		 * The root directory of the plugin as a URL.
		 *
		 * @var     string
		 */
		private $directory_url;

		/**
		 * Create class instance.
		 *
		 * @param 	string $plugin_file The path to the main plugin file.
		 * @return  void
		 * @since   1.0.0
		 */
		public function __construct( $plugin_file ) {
			$this->plugin_file    = $plugin_file;
			$this->directory_path = plugin_dir_path( $plugin_file );
			$this->directory_url  = plugin_dir_url( $plugin_file );

			add_action( 'charitable_start', array( $this, 'start' ), 1 );
		}

		/**
		 * Returns the original instance of this class.
		 *
		 * @return  Charitable_Stripe
		 * @since   1.0.0
		 */
		public static function get_instance() {
			return self::$instance;
		}

		/**
		 * Run the startup sequence on the charitable_start hook.
		 *
		 * This is only ever executed once.
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function start() {
			/* If we've already started (i.e. run this function once before), do not pass go. */
			if ( $this->started() ) {
				return;
			}

			/* Set static instance. */
			self::$instance = $this;

			$this->load_dependencies();

			$this->setup_licensing();

			$this->setup_i18n();

			$this->maybe_start_admin();

			$this->attach_hooks_and_filters();

			// Hook in here to do something when the plugin is first loaded.
			do_action( 'charitable_stripe_start', $this );
		}

		/**
		 * Include necessary files.
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		private function load_dependencies() {
			$dir = $this->get_path( 'includes' );

			require_once( $dir . 'libraries/vendor/autoload.php' );
			require_once( $dir . 'i18n/class-charitable-stripe-i18n.php' );
			require_once( $dir . 'compat/charitable-stripe-compat-functions.php' );
			require_once( $dir . 'gateway/class-charitable-gateway-stripe.php' );
			require_once( $dir . 'gateway/charitable-stripe-gateway-hooks.php' );

			/* Recurring Donations */
			if ( class_exists( 'Charitable_Recurring' ) ) {
				require_once( $dir . 'recurring/class-charitable-stripe-recurring.php' );
				require_once( $dir . 'recurring/charitable-stripe-recurring-hooks.php' );
			}
		}

		/**
		 * Set up hook and filter callback functions.
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		private function attach_hooks_and_filters() {
			add_action( 'wp_enqueue_scripts', array( $this, 'setup_scripts' ), 11 );

			if ( version_compare( charitable()->get_version(), '1.4.0', '<' ) ) {
				add_filter( 'charitable_javascript_vars', array( $this, 'set_js_error_messages' ) );
			}
		}

		/**
		 * Set up licensing for the extension.
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function setup_licensing() {
			charitable_get_helper( 'licenses' )->register_licensed_product(
				Charitable_Stripe::NAME,
				Charitable_Stripe::AUTHOR,
				Charitable_Stripe::VERSION,
				$this->plugin_file
			);
		}

		/**
		 * Set up the internationalisation for the plugin.
		 *
		 * @return  void
		 * @since   0.1.0
		 */
		private function setup_i18n() {
			if ( class_exists( 'Charitable_i18n' ) ) {

				require_once( $this->get_path( 'includes' ) . 'i18n/class-charitable-stripe-i18n.php' );

				Charitable_Stripe_i18n::get_instance();
			}
		}

		/**
		 * Load the admin-only functionality.
		 *
		 * @return  void
		 * @since   1.1.0
		 */
		private function maybe_start_admin() {
			if ( ! is_admin() ) {
				return;
			}

			require_once( $this->get_path( 'includes' ) . 'admin/class-charitable-stripe-admin.php' );
			require_once( $this->get_path( 'includes' ) . 'admin/charitable-stripe-admin-hooks.php' );
		}

		/**
		 * Register Stripe scripts.
		 *
		 * @since  1.1.0
		 *
		 * @return void
		 */
		public function setup_scripts() {
			if ( is_admin() ) {
				return;
			}

			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$version = '';
				$suffix  = '';
			} else {
				$version = $this->get_version();
				$suffix  = '.min';
			}

			$gateway = new Charitable_Gateway_Stripe;
			$keys    = $gateway->get_keys();

			$stripe_vars = array(
				'key'          => $keys['public_key'],
				'currency'     => charitable_get_currency(),
				'site_name'    => get_option( 'blogname' ),
				'zero_decimal' => Charitable_Gateway_Stripe::is_zero_decimal_currency(),
			);

			/* Register Stripe Checkout scripts. */
			wp_register_script(
				'charitable-stripe-checkout',
				'https://checkout.stripe.com/checkout.js',
				array(),
				$version,
				true
			);

			/* Register Stripe JS scripts. */
			wp_register_script(
				'charitable-stripe-js',
				'https://js.stripe.com/v2/',
				array(),
				$version,
				true
			);

			/* This script will be introduced into Charitable core (probably in 1.4.0), but in the meantime we'll provide it here too. */
			if ( version_compare( charitable()->get_version(), '1.4.0', '<' ) ) {

				wp_register_script(
					'charitable-donation-form',
					$this->get_path( 'assets', false ) . 'js/deprecated/charitable-donation-form' . $suffix . '.js',
					array( 'charitable-script', 'jquery' ),
					$version,
					true
				);

				wp_register_script(
					'charitable-stripe-checkout-handler',
					$this->get_path( 'assets', false ) . 'js/deprecated/charitable-stripe-checkout-handler' . $suffix . '.js',
					array( 'charitable-stripe-checkout', 'charitable-donation-form', 'jquery' ),
					$version,
					true
				);

				wp_localize_script(
					'charitable-stripe-checkout-handler',
					'CHARITABLE_STRIPE_VARS',
					$stripe_vars
				);

				wp_register_script(
					'charitable-stripe-js-handler',
					$this->get_path( 'assets', false ) . 'js/deprecated/charitable-stripe-js-handler' . $suffix . '.js',
					array( 'charitable-stripe-js', 'charitable-donation-form', 'jquery' ),
					$version,
					true
				);

				wp_localize_script(
					'charitable-stripe-js-handler',
					'CHARITABLE_STRIPE_VARS',
					$stripe_vars
				);

			} else {

				$dependencies = array( 'charitable-script', 'jquery' );

				if ( charitable_get_option( array( 'gateways_stripe', 'enable_stripe_checkout' ) ) ) {

					array_unshift( $dependencies, 'charitable-stripe-checkout' );
					$stripe_vars['mode'] = 'checkout';

				} else {

					array_unshift( $dependencies, 'charitable-stripe-js', 'charitable-credit-card' );
					$stripe_vars['mode'] = 'js';

				}

				wp_register_script(
					'charitable-stripe-handler',
					$this->get_path( 'assets', false ) . 'js/charitable-stripe-handler' . $suffix . '.js',
					$dependencies,
					$version,
					true
				);

				wp_localize_script(
					'charitable-stripe-handler',
					'CHARITABLE_STRIPE_VARS',
					$stripe_vars
				);

			}//end if
		}

		/**
		 * Set the error message for invalid amount when a donation form is submitted.
		 *
		 * This function can be removed once Charitable 1.4 is out and about.
		 *
		 * @param   string[] $vars Javascript vars.
		 * @return  string[] $vars
		 * @since   1.1.0
		 */
		public function set_js_error_messages( $vars ) {
			$vars['error_invalid_amount'] = sprintf( __( 'You must donate more than %s.', 'charitable-stripe' ), charitable_format_money( '0' ) );
			$vars['error_invalid_cc_number'] = __( 'The credit card passed is not valid.', 'charitable-stripe' );
			$vars['error_invalid_cc_expiry'] = __( 'The credit card expiry date is not valid.', 'charitable-stripe' );
			return $vars;
		}

		/**
		 * Returns whether we are currently in the start phase of the plugin.
		 *
		 * @return  bool
		 * @since   1.0.0
		 */
		public function is_start() {
			return current_filter() === 'charitable_stripe_start';
		}

		/**
		 * Returns whether the plugin has already started.
		 *
		 * @return  bool
		 * @since   1.0.0
		 */
		public function started() {
			return did_action( 'charitable_stripe_start' ) || current_filter() === 'charitable_stripe_start';
		}

		/**
		 * Returns the plugin's version number.
		 *
		 * @return  string
		 * @since   1.0.0
		 */
		public function get_version() {
			return self::VERSION;
		}

		/**
		 * Returns plugin paths.
		 *
		 * @param   string $type If empty, returns the path to the plugin.
		 * @param   bool   $absolute_path If true, returns the file system path. If false, returns it as a URL.
		 * @return  string
		 * @since   1.0.0
		 */
		public function get_path( $type = '', $absolute_path = true ) {
			$base = $absolute_path ? $this->directory_path : $this->directory_url;

			switch ( $type ) {
				case 'includes' :
					$path = $base . 'includes/';
					break;

				case 'admin' :
					$path = $base . 'includes/admin/';
					break;

				case 'templates' :
					$path = $base . 'templates/';
					break;

				case 'assets' :
					$path = $base . 'assets/';
					break;

				case 'directory' :
					$path = $base;
					break;

				default :
					$path = $this->plugin_file;

			}//end switch

			return $path;
		}

		/**
		 * Throw error on object clone.
		 *
		 * This class is specifically designed to be instantiated once. You can retrieve the instance using charitable()
		 *
		 * @since   1.0.0
		 * @return  void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'charitable-stripe' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since   1.0.0
		 * @return  void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, esc_attr__( 'Cheatin&#8217; huh?', 'charitable-stripe' ), '1.0.0' );
		}
	}

endif;
