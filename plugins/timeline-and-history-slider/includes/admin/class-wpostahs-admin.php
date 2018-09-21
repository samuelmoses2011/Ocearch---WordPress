<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package Timeline and History slider
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Wpostahs_Admin {

	function __construct() {		
		
		// Action to add admin menu
		add_action( 'admin_menu', array($this, 'wpostahs_register_menu'), 12 );
		
		// Admin init process
		add_action( 'admin_init', array($this, 'wpostahs_admin_init_process') );
	}
	
	/**
	 * Function to add menu
	 * 
	 * @package Timeline and History slider
	 * @since 1.0.0
	 */
	function wpostahs_register_menu() {
		// Register plugin premium page
		add_submenu_page( 'edit.php?post_type='.WPOSTAHS_POST_TYPE, __('Upgrade to PRO -  Timeline and History slider', 'timeline-and-history-slider'), '<span style="color:#2ECC71">'.__('Upgrade to PRO', 'timeline-and-history-slider').'</span>', 'manage_options', 'wpostahs-premium', array($this, 'wpostahs_premium_page') );

		// Register plugin hire us page
		add_submenu_page( 'edit.php?post_type='.WPOSTAHS_POST_TYPE, __('Hire Us', 'timeline-and-history-slider'), '<span style="color:#2ECC71">'.__('Hire Us', 'timeline-and-history-slider').'</span>', 'manage_options', 'wpostahs-hireus', array($this, 'wpostahs_hireus_page') );
	}

	/**
	 * Getting Started Page Html
	 * 
	 * @package Timeline and History slider
	 * @since 1.0.0
	 */
	function wpostahs_premium_page() {
		include_once( WPOSTAHS_DIR . '/includes/admin/settings/premium.php' );
	}

	/**
	 * Hire Us Page Html
	 * 
	 * @package Timeline and History Slider
	 * @since 1.0.0
	 */
	function wpostahs_hireus_page() {		
		include_once( WPOSTAHS_DIR . '/includes/admin/settings/hire-us.php' );
	}

	/**
	 * Admin Prior Process
	 * 
	 * @package Timeline and History slider
	 * @since 1.0.0
	 */
	function wpostahs_admin_init_process() {
		// If plugin notice is dismissed
	    if( isset($_GET['message']) && $_GET['message'] == 'wpostahs-plugin-notice' ) {
	    	set_transient( 'wpostahs_install_notice', true, 604800 );
	    }
	}
}

$wpostahs_Admin = new Wpostahs_Admin();