<?php
/**
 * Defines the customisations to the Campaign Form added by Charitable Ambassadors.
 *
 * @package     Charitable Stripe Connect/Classes/Charitable_Stripe_Connect_Campaign_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Stripe_Connect_Campaign_Form' ) ) :

/**
 * Charitable_Stripe_Connect_Campaign_Form
 *
 * @since       1.0.0
 */
class Charitable_Stripe_Connect_Campaign_Form {

    /**
     * @var     Charitable_Stripe_Connect_Campaign_Form
     * @access  private
     * @static
     * @since   1.0.0
     */
    private static $instance = null;

    /**
     * @since   1.0.0
     */
    CONST STRIPE_CONNECT_USER_ID_KEY = '_campaign_stripe_connect_user_id';

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
            self::$instance = new Charitable_Stripe_Connect_Campaign_Form();            
        }

        return self::$instance;
    }

    /**
     * Do not auto-publish campaigns when Stripe Connect is used for payouts.  
     *
     * @param   string $status
     * @param   array $submitted
     * @param   int $user_id
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function prevent_campaign_auto_publishing( $status, $submitted, $user_id ) {

        if ( 'publish' != $status ) {
            return $status;
        }

        if ( 'stripe-connect' != charitable_get_option( 'campaign_payout', 'paypal' ) ) {
            return $status;
        }

        if ( ! $this->is_personal_campaign_submission( $submitted ) ) {
            return $status;
        }

        /* If the user is set up with Stripe already, we don't need to do anything. */
        $stripe_user = new Charitable_Stripe_Connect_User( $user_id );

        if ( ! $stripe_user->is_user_connected() ) {
            $status = 'pending';
        }        

        return $status;
    }

    /**
     * Save the WordPress user ID of the user who will receive campaign payments 
     * (by default, the campaign creator).
     *
     * @param   array $submitted
     * @param   int $campaign_id
     * @param   int $user_id
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function save_stripe_receiver_id( $submitted, $campaign_id, $user_id, Charitable_Ambassadors_Campaign_Form $form ) {
        if ( 'stripe-connect' != charitable_get_option( 'campaign_payout', 'paypal' ) ) {
            return;
        }

        $stripe_receiver_id = apply_filters( 'charitable_stripe_connect_campaign_user_id', $user_id, $submitted, $campaign_id );

        update_post_meta( $campaign_id, self::STRIPE_CONNECT_USER_ID_KEY, $stripe_receiver_id );

        /* If the user is set up with Stripe already, auto-publish their campaign if auto-publishing is switched on. */
        $stripe_user = new Charitable_Stripe_Connect_User( $user_id );

        if ( ! $stripe_user->is_user_connected() ) {
            return;
        }

        $context = $form->get_submission_context();

        if ( $context[ 'is_submission' ] && charitable_get_option( 'auto_approve_campaigns', 0 ) ) {
            
            wp_update_post( array( 
                'ID' => $campaign_id,
                'post_status' => 'publish' 
            ) );

        }
    }

    /**
     * Redirect to Stripe after the user has submitted a campaign, 
     * but only if the user has not already linked their Stripe account.

     *
     * @param   string $url
     * @param   array $submitted
     * @param   int $campaign_id
     * @param   int $user_id
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function redirect_to_stripe( $url, $submitted, $campaign_id, $user_id ) {
        /* Don't do anything with campaign previews. */
        if ( isset( $submitted[ 'preview-campaign' ] ) ) {
            return $url;    
        }

        /* Make sure that Stripe Connect is turned on as the campaign payout method. */
        if ( 'stripe-connect' != charitable_get_option( 'campaign_payout', 'paypal' ) ) {
            return $url;
        }

        /* Make sure that this is a personal campaign submission. */
        if ( ! $this->is_personal_campaign_submission( $submitted ) ) {
            return $url;
        }

        /* Make sure that the current $user_id is also set up as the receiver. */
        if ( $user_id != get_post_meta( $campaign_id, '_campaign_stripe_connect_user_id', true ) ) {
            return $url;
        }

        $stripe_connect_user = new Charitable_Stripe_Connect_User( $user_id );

        /* Check whether the user has already set up Stripe. */
        if ( $stripe_connect_user->is_user_connected() ) {
            return $url;
        }

        $stripe_redirect = $stripe_connect_user->get_redirect_url();

        if ( $stripe_redirect ) {
            $url = $stripe_redirect;
        }

        return $url;
    }

    /**
     * Publish a newly connected Stripe user's pending campaigns.
     *
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function publish_user_campaigns() {
        if ( ! charitable_get_option( 'auto_approve_campaigns', 0 ) ) {
            return 0;
        }

        $user = new Charitable_User( wp_get_current_user() );

        $campaigns = $user->get_campaigns( array( 
            'posts_per_page' => -1, 
            'post_status' => 'pending', 
            'fields' => 'ids', 
            'meta_key' => self::STRIPE_CONNECT_USER_ID_KEY,
            'meta_value' => $user->ID
        ) );

        if ( ! $campaigns->have_posts() ) {
            return;
        }

        foreach ( $campaigns->posts as $campaign_id ) {

            wp_update_post( array( 
                'ID' => $campaign_id,
                'post_status' => 'publish' 
            ) );

        }
    }
    
    /**
     * Check whether the campaign submission is for a personal campaign.
     *
     * @param   array $submitted The array of data submitted by the user.
     * @return  boolean
     * @since   1.0.3
     * @access  public
     */
    public function is_personal_campaign_submission( $submitted ) {
        return array_key_exists( 'recipient', $submitted ) && 'personal' == $submitted['recipient'];
    }
}

endif;