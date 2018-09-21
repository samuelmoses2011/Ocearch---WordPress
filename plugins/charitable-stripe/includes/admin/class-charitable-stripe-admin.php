<?php
/**
 * The class responsible for adding & saving extra settings in the Charitable admin.
 *
 * @package     Charitable Stripe/Classes/Charitable_Stripe_Admin
 * @version     1.1.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2018, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Admin' ) ) :

	/**
	 * Charitable_Stripe_Admin
	 *
	 * @since       1.1.0
	 */
	class Charitable_Stripe_Admin {

		/**
		 * @var     Charitable_Stripe_Admin
		 * @access  private
		 * @static
		 * @since   1.1.0
		 */
		private static $instance = null;

		/**
		 * Create class object. Private constructor.
		 *
		 * @access  private
		 * @since   1.1.0
		 */
		private function __construct() {
		}

		/**
		 * Create and return the class object.
		 *
		 * @access  public
		 * @static
		 * @since   1.1.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Stripe_Admin();
			}

			return self::$instance;
		}

		/**
		 * Add links to activate
		 *
		 * @param   string[] $links Plugin action links.
		 * @return  string[]
		 * @access  public
		 * @since   1.1.0
		 */
		public function add_plugin_action_links( $links ) {

			if ( Charitable_Gateways::get_instance()->is_active_gateway( 'stripe' ) ) {

				$links[] = '<a href="' . admin_url( 'admin.php?page=charitable-settings&tab=gateways&group=gateways_stripe' ) . '">' . __( 'Settings', 'charitable-stripe' ) . '</a>';

			} else {

				$activate_url = esc_url( add_query_arg( array(
					'charitable_action' => 'enable_gateway',
					'gateway_id'        => 'stripe',
					'_nonce'            => wp_create_nonce( 'gateway' ),
				), admin_url( 'admin.php?page=charitable-settings&tab=gateways' ) ) );

				$links[] = '<a href="' . $activate_url . '">' . __( 'Activate Stripe Gateway', 'charitable-stripe' ) . '</a>';

			}

			return $links;
		}
	}

endif;
