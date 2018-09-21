<?php
/**
 * Plugin Name: Timeline and History Slider
 * Plugin URI: http://www.wponlinesupport.com/
 * Text Domain: timeline-and-history-slider
 * Domain Path: /languages/
 * Description: Easy to add and display history OR timeline for your WordPress website. Also support WordPress POST post type.  
 * Author: WP Online Support
 * Version: 1.2.5
 * Author URI: http://www.wponlinesupport.com/
 *
 * @package WordPress
 * @author WP Online Support
 */

if( !defined('WPOSTAHS_VERSION') ){
	define( 'WPOSTAHS_VERSION', '1.2.5' ); // Plugin version
}
if( !defined( 'WPOSTAHS_DIR' ) ) {
	define( 'WPOSTAHS_DIR', dirname( __FILE__ ) ); // Plugin dir
}
if( !defined( 'WPOSTAHS_URL' ) ) {
    define( 'WPOSTAHS_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}
if( !defined( 'WPOSTAHS_POST_TYPE' ) ) {
    define( 'WPOSTAHS_POST_TYPE', 'timeline_slider_post' ); // Plugin post type
}


add_action('plugins_loaded', 'wpostahs_load_textdomain');
function wpostahs_load_textdomain() {
	load_plugin_textdomain( 'timeline-and-history-slider', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
} 

/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 * 
 * @package Timeline and History Slider
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'wpostahs_install' );

/**
 * Deactivation Hook
 * 
 * Register plugin deactivation hook.
 * 
 * @package Timeline and History Slider
 * @since 1.0.0
 */
register_deactivation_hook( __FILE__, 'wpostahs_uninstall');

/**
 * Plugin Activation Function
 * Does the initial setup, sets the default values for the plugin options
 * 
 * @package Timeline and History Slider
 * @since 1.0.0
 */
function wpostahs_install() {

    // Deactivate free version
    if( is_plugin_active('timeline-and-history-slider-pro/wp-history-and-timeline-slider.php') ) {
        add_action('update_option_active_plugins', 'wpostahs_deactivate_premium_version');
    }
}

/**
 * Deactivate free plugin
 * 
 * @package Timeline and History Slider
 * @since 1.0.0
 */
function wpostahs_deactivate_premium_version() {
    deactivate_plugins('timeline-and-history-slider-pro/wp-history-and-timeline-slider.php', true);
}

/**
 * Plugin Deactivation Function
 * Delete  plugin options
 * 
 * @package Timeline and History Slider
 * @since 1.0.0
 */
function wpostahs_uninstall() {
}

/**
 * Function to display admin notice of activated plugin.
 * 
 * @package Timeline and History Slider
 * @since 1.0.0
 */
function wpostahs_admin_notice() {

    global $pagenow;

    // If PRO plugin is active and free plugin exist
    $dir                = WP_PLUGIN_DIR . '/timeline-and-history-slider-pro/wp-history-and-timeline-slider.php';
    $notice_link        = add_query_arg( array('message' => 'wpostahs-plugin-notice'), admin_url('plugins.php') );
    $notice_transient   = get_transient( 'wpostahs_install_notice' );

    if ( $notice_transient == false && $pagenow == 'plugins.php' && file_exists($dir) && current_user_can( 'install_plugins' ) ) {
        echo '<div class="updated notice" style="position:relative;">
                <p>
                    <strong>'.sprintf( __('Thank you for activating %s', 'timeline-and-history-slider'), 'Timeline and History slider').'</strong>.<br/>
                    '.sprintf( __('It looks like you had PRO version %s of this plugin activated. To avoid conflicts the extra version has been deactivated and we recommend you delete it.', 'timeline-and-history-slider'), '<strong>(<em>Timeline and History slider PRO</em>)</strong>' ).'
                </p>
                <a href="'.esc_url( $notice_link ).'" class="notice-dismiss" style="text-decoration:none;"></a>
            </div>';      
    }
}
add_action( 'admin_notices', 'wpostahs_admin_notice');

/**
 * Function to get plugin image sizes array
 * 
 * @package Timeline and History Slider
 * @since 1.0.0
 */
function wpostahs_get_unique() {
  static $unique = 0;
  $unique++;

  return $unique;
}
 
require_once( 'includes/class-tahs-script.php' );
require_once( 'includes/wpostahs-slider-custom-post.php' );
require_once( 'shortcode/wpsisac-template.php' );

// Admin file Free Vs Pro
require_once( 'includes/admin/class-wpostahs-admin.php' );

// How it work file, Load admin files
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once( WPOSTAHS_DIR . '/includes/admin/wpostahs-how-it-work.php' );
}

/* Plugin Wpos Analytics Data Starts */
function wpos_analytics_anl43_load() {

    require_once dirname( __FILE__ ) . '/wpos-analytics/wpos-analytics.php';

    $wpos_analytics =  wpos_anylc_init_module( array(
                            'id'            => 43,
                            'file'          => plugin_basename( __FILE__ ),
                            'name'          => 'Timeline and History slider',
                            'slug'          => 'timeline-and-history-slider',
                            'type'          => 'plugin',
                            'menu'          => 'edit.php?post_type=timeline_slider_post',
                            'text_domain'   => 'timeline-and-history-slider',
                            'offers'        => array(
                                                    'trial_premium' => array(
                                                        'image' => 'http://analytics.wponlinesupport.com/?anylc_img=43',
                                                        'link'  => 'https://www.wponlinesupport.com/plugins-plus-themes-powerpack-combo-offer/?ref=blogeditor'
                                                    ),
                                                ),
                        ));

    return $wpos_analytics;
}

// Init Analytics
wpos_analytics_anl43_load();
/* Plugin Wpos Analytics Data Ends */