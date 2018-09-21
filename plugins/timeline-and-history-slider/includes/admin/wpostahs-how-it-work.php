<?php
/**
 * Pro Designs and Plugins Feed
 *
 * @package Timeline and History Slider
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Action to add menu
add_action('admin_menu', 'wpostahs_register_design_page');

/**
 * Register plugin design page in admin menu
 * 
 * @package Timeline and History Slider
 * @since 1.0.0
 */
function wpostahs_register_design_page() {
	add_submenu_page( 'edit.php?post_type='.WPOSTAHS_POST_TYPE, __('How it works, our plugins and offers', 'timeline-and-history-slider'), __('How It Works', 'timeline-and-history-slider'), 'manage_options', 'wpostahs-designs', 'wpostahs_designs_page' );
}

/**
 * Function to display plugin design HTML
 * 
 * @package Timeline and History Slider
 * @since 1.0.0
 */
function wpostahs_designs_page() {

	$wpos_feed_tabs = wpostahs_help_tabs();
	$active_tab 	= isset($_GET['tab']) ? $_GET['tab'] : 'how-it-work';
?>
		
	<div class="wrap wpostahs-wrap">

		<h2 class="nav-tab-wrapper">
			<?php
			foreach ($wpos_feed_tabs as $tab_key => $tab_val) {
				$tab_name	= $tab_val['name'];
				$active_cls = ($tab_key == $active_tab) ? 'nav-tab-active' : '';
				$tab_link 	= add_query_arg( array( 'post_type' => WPOSTAHS_POST_TYPE, 'page' => 'wpostahs-designs', 'tab' => $tab_key), admin_url('edit.php') );
			?>

			<a class="nav-tab <?php echo $active_cls; ?>" href="<?php echo $tab_link; ?>"><?php echo $tab_name; ?></a>

			<?php } ?>
		</h2>
		
		<div class="wpostahs-tab-cnt-wrp">
		<?php
			if( isset($active_tab) && $active_tab == 'how-it-work' ) {
				wpostahs_howitwork_page();
			}
			else if( isset($active_tab) && $active_tab == 'plugins-feed' ) {
				echo wpostahs_get_plugin_design( 'plugins-feed' );
			} else {
				echo wpostahs_get_plugin_design( 'offers-feed' );
			}
		?>
		</div><!-- end .wpostahs-tab-cnt-wrp -->

	</div><!-- end .wpostahs-wrap -->

<?php
}

/**
 * Gets the plugin design part feed
 *
 * @package Timeline and History Slider
 * @since 1.0.0
 */
function wpostahs_get_plugin_design( $feed_type = '' ) {
	
	$active_tab = isset($_GET['tab']) ? $_GET['tab'] : '';
	
	// If tab is not set then return
	if( empty($active_tab) ) {
		return false;
	}

	// Taking some variables
	$wpos_feed_tabs = wpostahs_help_tabs();
	$transient_key 	= isset($wpos_feed_tabs[$active_tab]['transient_key']) 	? $wpos_feed_tabs[$active_tab]['transient_key'] 	: 'wpostahs_' . $active_tab;
	$url 			= isset($wpos_feed_tabs[$active_tab]['url']) 			? $wpos_feed_tabs[$active_tab]['url'] 				: '';
	$transient_time = isset($wpos_feed_tabs[$active_tab]['transient_time']) ? $wpos_feed_tabs[$active_tab]['transient_time'] 	: 172800;
	$cache 			= get_transient( $transient_key );
	
	if ( false === $cache ) {
		
		$feed 			= wp_remote_get( esc_url_raw( $url ), array( 'timeout' => 120, 'sslverify' => false ) );
		$response_code 	= wp_remote_retrieve_response_code( $feed );
		
		if ( ! is_wp_error( $feed ) && $response_code == 200 ) {
			if ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
				$cache = wp_remote_retrieve_body( $feed );
				set_transient( $transient_key, $cache, $transient_time );
			}
		} else {
			$cache = '<div class="error"><p>' . __( 'There was an error retrieving the data from the server. Please try again later.', 'timeline-and-history-slider' ) . '</div>';
		}
	}
	return $cache;	
}

/**
 * Function to get plugin feed tabs
 *
 * @package Timeline and History Slider
 * @since 1.0.0
 */
function wpostahs_help_tabs() {
	$wpos_feed_tabs = array(
						'how-it-work' 	=> array(
													'name' => __('How It Works', 'timeline-and-history-slider'),
												),
						'plugins-feed' 	=> array(
													'name' 				=> __('Our Plugins', 'timeline-and-history-slider'),
													'url'				=> 'http://wponlinesupport.com/plugin-data-api/plugins-data.php',
													'transient_key'		=> 'wpos_plugins_feed',
													'transient_time'	=> 172800
												),
						'offers-feed' 	=> array(
													'name'				=> __('Hire Us', 'timeline-and-history-slider'),
													'url'				=> 'http://wponlinesupport.com/plugin-data-api/wpos-offers.php',
													'transient_key'		=> 'wpos_offers_feed',
													'transient_time'	=> 86400,
												)
					);
	return $wpos_feed_tabs;
}

/**
 * Function to get 'How It Works' HTML
 *
 * @package Timeline and History Slider
 * @since 1.0.0
 */
function wpostahs_howitwork_page() { ?>
	
	<style type="text/css">
		.wpos-pro-box .hndle{background-color:#0073AA; color:#fff;}
		.wpos-pro-box .postbox{background:#dbf0fa none repeat scroll 0 0; border:1px solid #0073aa; color:#191e23;}
		.postbox-container .wpos-list li:before{font-family: dashicons; content: "\f139"; font-size:20px; color: #0073aa; vertical-align: middle;}
		.wpostahs-wrap .wpos-button-full{display:block; text-align:center; box-shadow:none; border-radius:0;}
		.wpostahs-shortcode-preview{background-color: #e7e7e7; font-weight: bold; padding: 2px 5px; display: inline-block; margin:0 0 2px 0;}
	</style>

	<div class="post-box-container">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
			
				<!--How it workd HTML -->
				<div id="post-body-content">
					<div class="metabox-holder">
						<div class="meta-box-sortables ui-sortable">
							<div class="postbox">
								
								<h3 class="hndle">
									<span><?php _e( 'How It Works - Display and shortcode', 'timeline-and-history-slider' ); ?></span>
								</h3>
								
								<div class="inside">
									<table class="form-table">
										<tbody>
											<tr>
												<th>
													<label><?php _e('Geeting Started with Timeline and History Slider', 'timeline-and-history-slider'); ?>:</label>
												</th>
												<td>
													<ul>
														<li><?php _e('Step-1. Go to "Timeline Slider --> Add slide tab".', 'timeline-and-history-slider'); ?></li>
														<li><?php _e('Step-2. Add Album title, description and featured image and Publish.', 'timeline-and-history-slider'); ?></li>
														<li><?php _e('Step-3. Also you can display multiple timeline with category shortcode. Just go to "Timeline Slider --> category" and create the category. ', 'timeline-and-history-slider'); ?></li>
														<li><?php _e('Step-4. Asign the timeline post to respective category and use the category shortcode to display', 'timeline-and-history-slider'); ?></li>
														
													</ul>
												</td>
											</tr>

											<tr>
												<th>
													<label><?php _e('How Shortcode Works', 'timeline-and-history-slider'); ?>:</label>
												</th>
												<td>
													<ul>
														<li><?php _e('Step-1. Create a page like Timeline OR Post Timeline.', 'timeline-and-history-slider'); ?></li>
														<li><?php _e('Step-2. Put below shortcode as per your need.', 'timeline-and-history-slider'); ?></li>
													</ul>
												</td>
											</tr>

											<tr>
												<th>
													<label><?php _e('All Shortcodes', 'timeline-and-history-slider'); ?>:</label>
												</th>
												<td>
													<span class="wpostahs-shortcode-preview">[th-slider]</span> – <?php _e('Timeline Slider Shortcode', 'timeline-and-history-slider'); ?> 
												
												</td>
											</tr>						
												
											<tr>
												<th>
													<label><?php _e('Need Support?', 'timeline-and-history-slider'); ?></label>
												</th>
												<td>
													<p><?php _e('Check plugin document for shortcode parameters and demo for designs.', 'timeline-and-history-slider'); ?></p> <br/>
													<a class="button button-primary" href="http://docs.wponlinesupport.com/timeline-and-history-slider/" target="_blank"><?php _e('Documentation', 'timeline-and-history-slider'); ?></a>									
													<a class="button button-primary" href="http://demo.wponlinesupport.com/timeline-and-history-slider-demo/" target="_blank"><?php _e('Demo for Designs', 'timeline-and-history-slider'); ?></a>
												</td>
											</tr>
										</tbody>
									</table>
								</div><!-- .inside -->
							</div><!-- #general -->
						</div><!-- .meta-box-sortables ui-sortable -->
					</div><!-- .metabox-holder -->
				</div><!-- #post-body-content -->
				
				<!--Upgrad to Pro HTML -->
				<div id="postbox-container-1" class="postbox-container">
					<div class="metabox-holder wpos-pro-box">
						<div class="meta-box-sortables ui-sortable">
							<div class="postbox">
								<h3 class="hndle">
									<span><?php _e( 'Upgrate to Pro', 'timeline-and-history-slider' ); ?></span>
								</h3>
								<div class="inside">										
									<ul class="wpos-list">
										<li>12+ cool designs</li>
										<li>Create unlimited timeline stories inside your WordPress website or blog.</li>
										<li>Use via 2 Shortcodes and adding 12+ Designs</li>
										<li>Vertical and Horizontal Timeline</li>
										<li>Also work with WordPress POST</li>
										<li>Timeline Category Management – Add stories in specific category.</li>
										<li>Timeline Stories Content Format – Add font awesome icon to display timeline stories format.</li>
										<li>Timeline Scrolling Navigation – Quickly and easily navigate your timeline with a beautiful scrolling navigation inside your timeline.</li>
										<li>Mobile Compatibility View</li>
										<li>Custom CSS</li>
										<li>Fully responsive</li>
										<li>100% Multi language</li>
									</ul>
									<a class="button button-primary wpos-button-full" href="https://www.wponlinesupport.com/wp-plugin/timeline-history-slider/" target="_blank"><?php _e('Go Premium ', 'timeline-and-history-slider'); ?></a>	
									<p><a class="button button-primary wpos-button-full" href="http://demo.wponlinesupport.com/prodemo/timeline-and-history-slider-pro/" target="_blank"><?php _e('View PRO Demo ', 'timeline-and-history-slider'); ?></a>			</p>								
								</div><!-- .inside -->
							</div><!-- #general -->
						</div><!-- .meta-box-sortables ui-sortable -->
					</div><!-- .metabox-holder -->

					<!-- Help to improve this plugin! -->
					<div class="metabox-holder">
						<div class="meta-box-sortables ui-sortable">
							<div class="postbox">
									<h3 class="hndle">
										<span><?php _e( 'Help to improve this plugin!', 'timeline-and-history-slider' ); ?></span>
									</h3>									
									<div class="inside">										
										<p>Enjoyed this plugin? You can help by rate this plugin <a href="https://wordpress.org/support/plugin/timeline-and-history-slider/reviews/" target="_blank">5 stars!</a></p>
									</div><!-- .inside -->
							</div><!-- #general -->
						</div><!-- .meta-box-sortables ui-sortable -->
					</div><!-- .metabox-holder -->
				</div><!-- #post-container-1 -->

			</div><!-- #post-body -->
		</div><!-- #poststuff -->
	</div><!-- #post-box-container -->
<?php }