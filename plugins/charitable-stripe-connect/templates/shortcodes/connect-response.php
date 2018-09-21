<?php
/**
 * The template used to display the campaign submission form.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-stripe-connect/shortcodes/connect-response.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$error = $view_args['response']->get_error_message();

if ( ! $error ) :
?>
	<div class="charitable-stripe-connect charitable-stripe-connect-success">
		<p><?php echo $view_args['success'] ?></p>
	</div>
<?php
else :
?>
	<div class="charitable-stripe-connect charitable-stripe-connect-error">
		<?php if ( strlen( $view_args['error_header'] ) ) : ?>
			<p><strong><?php echo $view_args['error_header'] ?></strong></p>
		<?php endif ?>
		<p><?php echo $error ?></p>
		<?php if ( $view_args['show_retry_link'] ) : ?>
			<p><a href="<?php echo $view_args['response']->get_stripe_connect_user()->get_redirect_url() ?>" class="fl-button button" role="button"><?php _e( 'Connect with Stripe', 'charitable-stripe-connect' ) ?></a></p>
		<?php endif ?>
	</div>
<?php
endif;
