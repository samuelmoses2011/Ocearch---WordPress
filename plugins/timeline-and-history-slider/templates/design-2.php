<div id="wpostahs-slider-nav-<?php echo $unique; ?>" class="wpostahs-slider-nav-<?php echo $unique; ?> wpostahs-slider-nav wpostahs-slick-slider" <?php echo $slider_as_nav_for; ?>>
		<?php while ( $query->have_posts() ) : $query->the_post(); ?>
		<div class="wpostahs-slider-nav-title">
			<div class="wpostahs-main-title">
				<button></button>
			</div>
			<div class="wpostahs-title"><?php echo the_title(); ?></div>
		</div>
	<?php endwhile; ?>
</div>
<div class="wpostahs-slider-for-<?php echo $unique; ?> wpostahs-slider-for wpostahs-slick-slider">
	<?php while ( $query->have_posts() ) : $query->the_post(); ?>
		<?php $feat_image = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); ?>
		<div class="wpostahs-slider-nav-content">
		<h2 class="wpostahs-centent-title"><?php echo the_title(); ?></h2>
			<div class="wpostahs-centent">
				<?php echo the_content(); ?>
			</div>
		<?php if( !empty($feat_image) ) { ?>	
			<div style="text-align:center">
				<img src="<?php echo $feat_image; ?>" alt="<?php echo the_title(); ?>" />
			</div>
		<?php } ?>	
		</div>
	<?php endwhile; ?>
</div><!-- #post-## -->