<?php

add_action('init', 'wpostahs_slider_init');
function wpostahs_slider_init() {
    $wpostahs_slider_labels = array(
    'name'                 => _x('Timeline Slider', 'timeline-and-history-slider'),
    'singular_name'        => _x('Timeline Slider', 'timeline-and-history-slider'),
    'add_new'              => _x('Add Slide', 'timeline-and-history-slider'),
    'add_new_item'         => __('Add New slide', 'timeline-and-history-slider'),
    'edit_item'            => __('Edit', 'timeline-and-history-slider'),
    'new_item'             => __('New', 'timeline-and-history-slider'),
    'view_item'            => __('View', 'timeline-and-history-slider'),
    'search_items'         => __('Search', 'timeline-and-history-slider'),
    'not_found'            =>  __('NoItems found', 'timeline-and-history-slider'),
    'not_found_in_trash'   => __('No Items found in Trash', 'timeline-and-history-slider'), 
    '_builtin'             =>  false, 
    'parent_item_colon'    => '',  
	'menu_name'          => _x( 'Timeline Slider', 'admin menu', 'timeline-and-history-slider' )
  );

  $wpostahs_slider_args = array(
    'labels'              => $wpostahs_slider_labels,
    'public'              => true,
    'publicly_queryable'  => true,
    'exclude_from_search' => false,
    'show_ui'             => true,
    'show_in_menu'        => true, 
    'query_var'           => true,
    'rewrite'             => array( 
							'slug' => 'timeline_slider_post',
							'with_front' => false
							),
    'capability_type'     => 'post',
    'has_archive'         => true,
    'hierarchical'        => false,
    'menu_position'       => 8,
	'menu_icon'   => 'dashicons-feedback',
    'supports'            => array('title','editor','thumbnail')
  );

  register_post_type('timeline_slider_post',$wpostahs_slider_args);

}

/* Register Taxonomy */
add_action( 'init', 'wpostahs_slider_taxonomies');
function wpostahs_slider_taxonomies() {
    $labels = array(
        'name'              => _x( 'Category', 'timeline-and-history-slider' ),
        'singular_name'     => _x( 'Category', 'timeline-and-history-slider' ),
        'search_items'      => __( 'Search Category', 'timeline-and-history-slider' ),
        'all_items'         => __( 'All Category', 'timeline-and-history-slider' ),
        'parent_item'       => __( 'Parent Category', 'timeline-and-history-slider' ),
        'parent_item_colon' => __( 'Parent Category' , 'timeline-and-history-slider' ),
        'edit_item'         => __( 'Edit Category', 'timeline-and-history-slider' ),
        'update_item'       => __( 'Update Category', 'timeline-and-history-slider' ),
        'add_new_item'      => __( 'Add New Category', 'timeline-and-history-slider' ),
        'new_item_name'     => __( 'New Category Name', 'timeline-and-history-slider' ),
        'menu_name'         => __( 'Timeline Category', 'timeline-and-history-slider' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'wpostahs-slider-category' ),
    );

    register_taxonomy( 'wpostahs-slider-category', array( 'timeline_slider_post' ), $args );
}

function wpostahs_slider_rewrite_flush() {  
		wpostahs_slider_init();
    flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'wpostahs_slider_rewrite_flush' );


// Manage Category Shortcode wpcolumns

add_filter("manage_wpostahs_slider-category_custom_column", 'wpostahs_slider_category_columns', 10, 3);
add_filter("manage_edit-wpostahs_slider-category_columns", 'wpostahs_slider_category_manage_columns'); 
function wpostahs_slider_category_manage_columns($theme_columns) {
    $new_columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name'),
            'slider_shortcode' => __( 'Category Shortcode', 'timeline-and-history-slider' ),
            'slug' => __('Slug'),
            'posts' => __('Posts')
			);

    return $new_columns;
}

function wpostahs_slider_category_columns($out, $column_name, $theme_id) {
    $theme = get_term($theme_id, 'wpostahs-slider-category');
    switch ($column_name) {      
        case 'title':
            echo get_the_title();
        break;
        case 'slider_shortcode':
			echo '[th-slider category="' . $theme_id. '"]';
			 	  

        break;
        default:
            break;
    }
    return $out;   

}
