<?php 

add_action('wp_enqueue_scripts', 'salient_child_enqueue_styles');

function hide_parent_theme($themes)
{
    unset($themes['salient']);
    return $themes;
}

function salient_child_enqueue_styles()
{
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css', array('font-awesome'));

    if (is_rtl()) {
        wp_enqueue_style('salient-rtl', get_template_directory_uri(). '/rtl.css', array(), '1', 'screen');
    }
}

add_filter('wp_prepare_themes_for_js', 'hide_parent_theme');


/**
 * Add a progress bar to the individual campaign pages.
 */
function en_add_progress_bar_before_summary() {
    add_action( 'charitable_campaign_content_before', 'charitable_template_campaign_progress_bar', 11 );
}
add_action( 'after_setup_theme', 'en_add_progress_bar_before_summary', 11 );



/**
 * Add a featured image to the individual campaign pages.
 *
 * @param   Charitable_Campaign $campaign
 */
function en_add_campaign_featured_image( Charitable_Campaign $campaign ) {
    if ( has_post_thumbnail( $campaign->ID ) ) {
    	the_post_thumbnail( $campaign->ID );

    }
}

add_action( 'charitable_campaign_content_before', 'en_add_campaign_featured_image', 10 );

/**
 * Remove the campaign descriptions from the campaign loop.
 */
function en_remove_campaign_description_in_loop() {
    remove_action( 'charitable_campaign_content_loop_after', 'charitable_template_campaign_description', 4 );
}
add_action( 'after_setup_theme', 'en_remove_campaign_description_in_loop', 11 );

?>
