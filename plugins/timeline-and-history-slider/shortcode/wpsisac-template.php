<?php 
function get_wpostahs_slider( $atts, $content = null ){

	extract(shortcode_atts(array(
		"limit"    			=> '20',
		"category" 			=> '',
		"dots"     			=> 'true',
		"arrows"     		=> 'true',
		"autoplay"     		=> 'true',	
		"autoplay_interval" => '3000',
		"speed"             => '300',
		"fade"		        => 'false',
		"design" 			=> 'design-1',
		"loop"              => 'true',
		"rtl"				=> false,
	), $atts));

	$posts_per_page 	= !empty($limit) 				? $limit 					: '20';
	$cat 				= (!empty($category))			? explode(',',$category)	: '';
	$dots 				= ( $dots == 'false' )			? 'false' 					: 'true';
	$arrows 			= ( $arrows == 'false' )		? 'false' 					: 'true';
	$autoplay 			= ( $autoplay == 'false' )		? 'false' 					: 'true';
	$autoplayInterval	= !empty( $autoplay_interval ) 	? $autoplay_interval 		: '3000';
	$speed 				= !empty( $speed ) 				? $speed 					: '300';
	$fade				= ( $fade == 'true' )			? 'true' 					: 'false';
	$loop				= ( $loop == 'true' )			? 'true' 					: 'false';
	$design 			= (!empty($design))				? $design					: 'design-1';
	$design_file 		= WPOSTAHS_DIR . '/templates/' . $design . '.php';
	
	// For RTL
	if( empty($rtl) && is_rtl() ) {
		$rtl = 'true';
	} elseif ( $rtl == 'true' ) {
		$rtl = 'true';
	} else {
		$rtl = 'false';
	}

	// Enqueus required script
	wp_enqueue_script( 'wpos-slick-jquery' );
	wp_enqueue_script( 'wpostahs-public-js' );

	ob_start();	

	$unique 		= wpostahs_get_unique();
	$post_type 		= 'timeline_slider_post';
	$orderby 		= 'post_date';
	$order 			= 'ASC';
	$slider_as_nav_for 	= "data-slider-nav-for='wpostahs-slider-for-{$unique}'";

	$args = array ( 
		'post_type'      => $post_type, 
		'orderby'        => $orderby, 
		'order'          => $order,
		'posts_per_page' => $posts_per_page,
	);

	if($cat != ""){
		$args['tax_query'] = array(
					array(
						'taxonomy' => 'wpostahs-slider-category',
						'field' => 'term_id', 
						'terms' => $cat
					) );
	}

	$query = new WP_Query($args);
	$post_count = $query->post_count;

	$nav_centermode	= (($post_count % 2 == 0) || ($post_count == 5)) 	? 'false' : 'true';
	$slidetoshow	= (($post_count >=5)) 	? 5 : $post_count;
	
	// Slider configuration
	$slider_conf = compact('dots', 'arrows','loop', 'autoplay', 'autoplayInterval', 'speed', 'fade', 'rtl', 'nav_centermode', 'slidetoshow');

	global $post;	

	if ( $query->have_posts() ) : ?>
		<div class="wpostahs-slider-wrp">
			<div class="wpostahs-slider-inner-wrp <?php echo 'wpostahs-slider-'.$design; ?>">
				<?php 
					// Include shortcode html file
					if( $design_file ) {
						include( $design_file );
					}
				?>
			</div>
		<div class="wpostahs-slider-conf" data-conf="<?php echo htmlspecialchars(json_encode( $slider_conf )); ?>"></div>
		</div>
		<?php
	endif; 
	wp_reset_query();
	return ob_get_clean();
}

add_shortcode('th-slider','get_wpostahs_slider');