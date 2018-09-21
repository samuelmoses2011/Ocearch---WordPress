( function( $ ){

    var $form, helper;

    /**
     * Handle the Stripe response.
     */
    var handle_stripe_response = function( status, response ) {

        if ( response.error ) {
            alert( response.error.message );
        }
        else {
            // Use the token to create the charge with a server-side script.
            // You can access the token ID with `token.id`
            $form.find( '[name=stripe_token]' ).val( response.id );            

            $( 'body' ).off( 'submit', '#charitable-donation-form-modal-loop #charitable-donation-form[data-use-ajax=1]', on_submit_modal_donation_form );

            $form.attr( 'data-use-ajax', 1 ).trigger( 'submit' );

            $( 'body' ).on( 'submit', '#charitable-donation-form-modal-loop #charitable-donation-form[data-use-ajax=1]', on_submit_modal_donation_form );
        }        
    };

    /**
     * Turn off ajax submission trigger.
     */
    var turn_off_ajax_trigger = function( $form ) {
        if ( 'stripe' === ( new CHARITABLE.Donation_Form( $form ) ).get_payment_method() ) {
            $form.attr( 'data-use-ajax', 0 );
        }
    };

    /**
     * On form submission in modal.
     *
     * This is designed to be called before the default Charitable on submit handler for 
     * donations. It turns off the ajax trigger if Stripe is the chosen gateway, and then
     * re-triggers the submit event.
     */
    var on_submit_modal_donation_form = function( event ) {
        event.stopImmediatePropagation();

        turn_off_ajax_trigger( $(this) );

        $( 'body' ).off( 'submit', '#charitable-donation-form-modal-loop #charitable-donation-form[data-use-ajax=1]', on_submit_modal_donation_form );

        $(this).trigger( 'submit' );

        $( 'body' ).on( 'submit', '#charitable-donation-form-modal-loop #charitable-donation-form[data-use-ajax=1]', on_submit_modal_donation_form );

        return false;
    }

    $( 'body' ).on( 'submit', '#charitable-donation-form-modal-loop #charitable-donation-form[data-use-ajax=1]', on_submit_modal_donation_form );

    /**
     * On form submission.
     */
    $( 'body' ).on ( 'submit', '#charitable-donation-form[data-use-ajax=0]', function( event ) {

        // Set up closure objects
        $form = $(this);
        helper = new CHARITABLE.Donation_Form( $form );
        
        var cc_number = helper.get_cc_number(), 
            cc_cvc = helper.get_cc_cvc(), 
            cc_expiry_month = helper.get_cc_expiry_month(), 
            cc_expiry_year = helper.get_cc_expiry_year();

        // Remove credit card field names
        helper.clear_cc_fields();

        // If we're not using Stripe, do not process any further
        if ( 'stripe' !== helper.get_payment_method() ) {
            return true;
        }

        event.preventDefault();

        if ( ! helper.validate() ) {
            alert( helper.get_error_message() );
            return false;
        }        

        if ( ! Stripe.card.validateCardNumber( cc_number ) ) {
            alert( CHARITABLE_VARS.error_invalid_cc_number );    
            return false;
        }        

        if ( ! Stripe.card.validateExpiry( cc_expiry_month, cc_expiry_year ) ) {
            alert( CHARITABLE_VARS.error_invalid_cc_expiry );
            return false;
        }

        Stripe.card.createToken({
            number: cc_number,
            cvc: cc_cvc,
            exp_month: cc_expiry_month,
            exp_year: cc_expiry_year
        }, handle_stripe_response );

        return false;
        
    });

    /**
     * On payment method selection
     */
    $( 'body' ).on( 'change', '#charitable-gateway-selector input[name=gateway]', function() {
        turn_off_ajax_trigger( $(this).parents( '#charitable-donation-form' ) );
    });

    /**
     * Set up events.
     */
    $( document ).ready( function() {
        Stripe.setPublishableKey(CHARITABLE_STRIPE_VARS.key);

        var $form = $( '#charitable-donation-form' );
        
        if ( $form.length ) {
            turn_off_ajax_trigger( $form );
        }

        $( '#charitable-gateway-selector input[name=gateway]' ).trigger( 'change' );
    });

})( jQuery );

