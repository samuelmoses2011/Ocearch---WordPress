( function( $ ){

    var $form, helper;

    var handler = StripeCheckout.configure({
        key: CHARITABLE_STRIPE_VARS.key,
        image: '',
        locale: 'auto',
        token: function(token) {
            // Use the token to create the charge with a server-side script.
            // You can access the token ID with `token.id`
            $( '#charitable-donation-form [name=stripe_token]' ).val( token.id );
            
            $( 'body' ).off( 'submit', '#charitable-donation-form-modal-loop #charitable-donation-form[data-use-ajax=1]', on_submit_modal_donation_form );
            
            $form.attr( 'data-use-ajax', 1 ).trigger( 'submit' );
            
            $( 'body' ).on( 'submit', '#charitable-donation-form-modal-loop #charitable-donation-form[data-use-ajax=1]', on_submit_modal_donation_form );
        }
    });    

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

        // If we're not using Stripe, do not process any further
        if ( 'stripe' !== helper.get_payment_method() ) {
            return true;
        }

        if ( helper.validate() ) {

            // Convert the amount to cents
            var amount = helper.get_amount() * 100;

            // Open Checkout with further options
            handler.open({
                name: CHARITABLE_STRIPE_VARS.site_name,
                description: helper.get_description(),
                email: helper.get_email(),
                currency: CHARITABLE_STRIPE_VARS.currency,
                amount: amount
            });
        }        
        else {        
            alert( helper.get_error_message() );
        }

        event.preventDefault();        
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
    $( document ).ready( function(){
        var $form = $( '#charitable-donation-form' );
        
        if ( $form.length ) {
            turn_off_ajax_trigger( $form );
        }

        $( '#charitable-gateway-selector input[name=gateway]' ).trigger( 'change' );
    });

    /**
     * Close Checkout on page navigation
     */ 
    $(window).on('popstate', function() {
        handler.close();
    });

})( jQuery );