<?php
/**
 * Script Class
 *
 * Handles the script and style functionality of plugin
 *
 * @package Timeline and History slider
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Tahs_Script {
	
	function __construct() {
		
		// Action to add style at front side
		add_action( 'wp_enqueue_scripts', array($this, 'tahs_front_style') );
		
		// Action to add script at front side
		add_action( 'wp_enqueue_scripts', array($this, 'tahs_front_script') );	
		
	}

	/**
	 * Function to add style at front side
	 * 
	 * @package Timeline and History slider
	 * @since 1.0.0
	 */
	function tahs_front_style() {
		
		// Registring and enqueing slick slider css
		if( !wp_style_is( 'wpos-slick-style', 'registered' ) ) {
			wp_register_style( 'wpos-slick-style', WPOSTAHS_URL.'assets/css/slick.css', array(), WPOSTAHS_VERSION );
			wp_enqueue_style( 'wpos-slick-style' );
		}
		
		// Registring and enqueing public css
		wp_register_style( 'tahs-public-style', WPOSTAHS_URL.'assets/css/slick-slider-style.css', array(), WPOSTAHS_VERSION );
		wp_enqueue_style( 'tahs-public-style' );
	}
	
	/**
	 * Function to add script at front side
	 * 
	 * @package Timeline and History slider
	 * @since 1.0.0
	 */
	function tahs_front_script() {
		
		// Registring slick slider script
		if( !wp_script_is( 'wpos-slick-jquery', 'registered' ) ) {
			wp_register_script( 'wpos-slick-jquery', WPOSTAHS_URL.'assets/js/slick.min.js', array('jquery'), WPOSTAHS_VERSION, true );
		}
		
		// Registring and enqueing public script
		wp_register_script( 'wpostahs-public-js', WPOSTAHS_URL.'assets/js/wpostahs-public-js.js', array('jquery'), WPOSTAHS_VERSION, true );
		wp_localize_script( 'wpostahs-public-js', 'Wpostahs', array(
																	'is_mobile' => (wp_is_mobile()) ? 1 : 0,
																	'is_rtl' 	=> (is_rtl()) 		? 1 : 0
																	));
	}
	
}

$tahs_script = new Tahs_Script();