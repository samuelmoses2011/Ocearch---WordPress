<?php
/**
 * Charitable Stripe Connect Upgrade hooks.
 *
 * @package     Charitable Stripe Connect/Functions/Upgrade
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2015, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Connect_Template' ) ) :

	/**
	 * Charitable_Stripe_Connect_Template
	 *
	 * @since       1.0.0
	 */
	class Charitable_Stripe_Connect_Template extends Charitable_Template {

		/**
		 * Set theme template path.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_theme_template_path() {
			return trailingslashit( apply_filters( 'charitable_stripe_connect_theme_template_path', 'charitable/charitable-stripe-connect' ) );
		}

		/**
		 * Return the base template path.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_base_template_path() {
			return charitable_stripe_connect()->get_path( 'templates' );
		}
	}

endif;
