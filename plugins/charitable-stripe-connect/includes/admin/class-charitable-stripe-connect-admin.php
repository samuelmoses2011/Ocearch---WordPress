<?php
/**
 * The class responsible for adding & saving extra settings in the Charitable admin.
 *
 * @package     Charitable Stripe Connect/Classes/Charitable_Stripe_Connect_Admin
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Connect_Admin' ) ) :

	/**
	 * Charitable_Stripe_Connect_Admin
	 *
	 * @since       1.0.0
	 */
	class Charitable_Stripe_Connect_Admin {

		/**
		 * @var     Charitable_Stripe_Connect_Admin
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
			$this->load_dependencies();
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
				self::$instance = new Charitable_Stripe_Connect_Admin();
			}

			return self::$instance;
		}

		/**
		 * Load any required files.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function load_dependencies() {
			require_once( 'upgrades/class-charitable-stripe-connect-upgrade.php' );
			require_once( 'upgrades/charitable-stripe-connect-upgrade-hooks.php' );
		}

		/**
		 * Add custom links to the plugin actions.
		 *
		 * @param   string[] $links
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
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

				$links[] = '<a href="' . $activate_url . '">' . __( 'Activate Stripe', 'charitable-stripe' ) . '</a>';

			}

			return $links;
		}

		/**
		 * Add settings to the Extensions settings tab.
		 *
		 * @param   array[] $fields
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_connect_settings( $fields ) {
			$new_fields = array(
				'section_gateway' => array(
					'title'     => __( 'Stripe Connect', 'charitable-stripe-connect' ),
					'type'      => 'heading',
					'priority'  => 20,
				),
				'development_client_id' => array(
					'type'      => 'text',
					'title'     => __( 'Development Client ID', 'charitable-stripe-connect' ),
					'priority'  => 22,
					'class'     => 'wide',
					'help'      => __( 'The Development Client ID is used while you are still in Test Mode. You can find your development client ID in <a href="https://dashboard.stripe.com/account/applications/settings" target="_blank">your Stripe dashboard</a>.', 'charitable-stripe-connect' ),
				),
				'production_client_id' => array(
					'type'      => 'text',
					'title'     => __( 'Production Client ID', 'charitable-stripe-connect' ),
					'priority'  => 24,
					'class'     => 'wide',
					'help'      => __( 'The Production Client ID is used when Test Mode is switched off.  You can find your production client ID in <a href="https://dashboard.stripe.com/account/applications/settings" target="_blank">your Stripe dashboard</a>. ', 'charitable-stripe-connect' ),
				),
				'application_fee' => array(
					'type'      => 'text',
					'title'     => __( 'Platform Fee', 'charitable-stripe-connect' ),
					'priority'  => 26,
					'placeholder' => __( 'Enter amount as a percentage.', 'charitable-stripe-connect' ),
					'help'      => __( 'Set the platform fee that you charge for each transaction you process, as a percentage. <a href="https://stripe.com/docs/connect/payments-fees#collecting-fees" target="_blank">https://stripe.com/docs/connect/payments-fees#collecting-fees</a>', 'charitable-stripe-connect' ),
				),
				'charge_owner' => array(
					'type'      => 'select',
					'title'     => __( 'How is the charge created?', 'charitable-stripe-connect' ),
					'priority'  => 28,
					'options'   => array(
						'direct' => __( 'Directly on the connected account', 'charitable-stripe-connect' ),
						'platform' => __( 'Through your platform account', 'charitable-stripe-connect' ),
					),
					'help'      => __( 'There are two ways you can collect donations on behalf of your connected accounts (i.e. the accounts of your users). You can either have the donations charged directly on the connected account, in which case it is responsible for the fees, refunds and chargebacks. Alternatively, you can have the donations charged through the platform account, which makes the platform responsible for the fees, refunds and chargebacks. <a href="https://stripe.com/docs/connect/payments-fees#creating-payments" target="_blank">https://stripe.com/docs/connect/payments-fees#creating-payments</a>', 'charitable-stripe-connect' ),
				),
				'success_redirect' => array(
					'title'     => __( 'Connection Success Page', 'charitable-stripe-connect' ),
					'type'      => 'select',
					'priority'  => 30,
					'options'   => array(
						'home'  => __( 'Homepage', 'charitable-stripe-connect' ),
						'pages' => array(
							'options' => charitable_get_admin_settings()->get_pages(),
							'label' => __( 'Choose a Static Page', 'charitable-stripe-connect' ),
						),
					),
					'help'      => __( 'This is the page that users are redirected to after they have successfully connected their Stripe account.', 'charitable-stripe-connect' ),
				),
				'error_redirect' => array(
					'title'     => __( 'Connection Failure Page', 'charitable-stripe-connect' ),
					'type'      => 'select',
					'priority'  => 32,
					'options'   => array(
						'home'  => __( 'Homepage', 'charitable-stripe-connect' ),
						'pages' => array(
							'options' => charitable_get_admin_settings()->get_pages(),
							'label' => __( 'Choose a Static Page', 'charitable-stripe-connect' ),
						),
					),
					'help'      => __( 'This is the page that users are redirected to after they cancelled or were otherwise unsucessful in connecting their Stripe account.', 'charitable-stripe-connect' ),
				),
			);

			$fields = array_merge( $fields, $new_fields );

			return $fields;
		}

		/**
		 * Add the Stripe Connect payout method.
		 *
		 * @param   string[] $methods
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_connect_payout_method( $methods ) {

			/* Methods are grouped as automatic or manual */
			if ( ! isset( $methods['automatic'] ) ) {
				$methods['automatic'] = array(
					'label'     => __( 'Automatic', 'charitable-stripe-connect' ),
					'options'   => array(),
				);
			}

			$methods['automatic']['options']['stripe-connect'] = __( 'Stripe Connect', 'charitable-stripe-connect' );

			return $methods;
		}

		/**
		 * Add Stripe Connect section to admin profiles.
		 *
		 * @param   WP_User $user
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_profile_section( $user ) {
			$stripe_connect_user = new Charitable_Stripe_Connect_User( $user->ID );
			?>        
			<h3><?php _e( 'Stripe Connect Settings', 'charitable-stripe-connect' ) ?></h3>
			<table class="form-table">
				<tbody>            
					<tr>
						<th scope="row"><?php _e( 'Account Connected', 'charitable-stripe-connect' ) ?></th>
						<td><?php echo $stripe_connect_user->is_user_connected() ? __( 'Yes', 'charitable-stripe-connect' ) : __( 'No', 'charitable-stripe-connect' ) ?></td>
					</tr>
					<?php if ( $stripe_connect_user->get( 'error' ) ) : ?>
						<tr>
							<th scope="row"><?php _e( 'Error', 'charitable-stripe-connect' ) ?></th>
							<td><?php printf( '%s (%s: %s).', $error['error_description'], __( 'error code', 'charitable-stripe-connect' ), $error['error'] ) ?></td>
						</tr>
					<?php elseif ( $stripe_connect_user->get( 'access_token' ) ) : ?>
						<tr>
							<th scope="row"><?php _e( 'Access Token', 'charitable-stripe-connect' ) ?></th>
							<td><?php echo $stripe_connect_user->get( 'access_token' ) ?></td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Stripe User ID', 'charitable-stripe-connect' ) ?></th>
							<td><?php echo $stripe_connect_user->get( 'stripe_user_id' ) ?></td>
						</tr>
					<?php endif ?>
				</tbody>
			</table>
			<?php
		}

		/**
		 * Add details about the funds recipient for the campaign.
		 *
		 * @param   array[]             $details
		 * @param   Charitable_Campaign $campaign The campaign object.
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_funds_recipient_details( $details, Charitable_Campaign $campaign ) {
			$user_id = get_post_meta( $campaign->ID, Charitable_Stripe_Connect_Campaign_Form::STRIPE_CONNECT_USER_ID_KEY, true );

			if ( ! $user_id ) {

				$details['funds_recipient_id'] = array(
					'label' => __( 'Funds Recipient', 'charitable-stripe-connect' ),
					'value' => __( '-', 'charitable-stripe-connect' ),
				);

				return $details;
			}

			$stripe_connect_user = new Charitable_Stripe_Connect_User( $user_id );

			$new_details = array(
				'funds_recipient_id' => array(
					'label' => __( 'Funds Recipient', 'charitable-stripe-connect' ),
					'value' => sprintf( '<a href="%s">%s (%s %d)</a>',
						esc_url( add_query_arg( array( 'user_id' => $user_id ), admin_url( 'user-edit.php' ) ) ),
						get_user_by( 'ID', $user_id )->display_name,
						__( 'User ID', 'charitable-stripe-connect' ),
						$user_id
					),
				),
				'stripe_connect_status' => array(
					'label' => __( 'Account Connected', 'charitable-stripe-connect' ),
					'value' => $stripe_connect_user->is_user_connected() ? __( 'Yes', 'charitable-stripe-connect' ) : __( 'No', 'charitable-stripe-connect' ),
				),
			);

			if ( $stripe_connect_user->get( 'error' ) ) {

				$new_details['stripe_connect_error'] = array(
					'label' => __( 'Error', 'charitable-stripe-connect' ),
					'value' => sprintf( '%s (%s: %s).', $error['error_description'], __( 'error code', 'charitable-stripe-connect' ), $error['error'] ),
				);

			} else {

				$new_details['stripe_connect_access_token'] = array(
					'label' => __( 'Access Token', 'charitable-stripe-connect' ),
					'value' => $stripe_connect_user->get( 'access_token' ),
				);

				$new_details['stripe_connect_stripe_user_id'] = array(
					'label' => __( 'Stripe User ID', 'charitable-stripe-connect' ),
					'value' => $stripe_connect_user->get( 'stripe_user_id' ),
				);

			}//end if

			return array_merge( $details, $new_details );
		}
	}

endif;
