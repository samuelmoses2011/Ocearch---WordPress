<?php
/**
 * Charitable Stripe Connect Upgrade class.
 *
 * The responsibility of this class is to manage migrations between versions of Charitable Stripe Connect.
 *
 * @package     Charitable Stripe Connect
 * @subpackage  Charitable Stripe Connect/Upgrade
 * @copyright   Copyright (c) 2017, Eric Daams
 * @license     http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Connect_Upgrade' ) ) :

	/**
	 * Charitable_Stripe_Connect_Upgrade
	 *
	 * @see         Charitable_Upgrade
	 * @since       1.0.0
	 */
	class Charitable_Stripe_Connect_Upgrade extends Charitable_Upgrade {

		/**
		 * Array of methods to perform when upgrading to specific versions.
		 *
		 * @var     array
		 * @access  protected
		 */
		protected $upgrade_actions = array();

		/**
		 * Option key for upgrade log.
		 *
		 * @var     string
		 * @access  protected
		 */
		protected $upgrade_log_key = 'charitable_stripe_connect_upgrade_log';

		/**
		 * Option key for plugin version.
		 *
		 * @var     string
		 * @access  protected
		 */
		protected $version_key = 'charitable_stripe_connect_version';

		/**
		 * Upgrade from the current version stored in the database to the live version.
		 *
		 * @param   false|string $db_version
		 * @param   string       $edge_version
		 * @return  void
		 * @static
		 * @access  public
		 * @since   1.0.0
		 */
		public static function upgrade_from( $db_version, $edge_version ) {

			if ( parent::requires_upgrade( $db_version, $edge_version ) ) {

				new Charitable_Stripe_Connect_Upgrade( $db_version, $edge_version );

			}
		}
	}

endif;
