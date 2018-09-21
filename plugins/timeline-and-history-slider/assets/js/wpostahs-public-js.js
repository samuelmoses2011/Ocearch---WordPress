jQuery(document).ready(function($){

	// Initialize slick slider
	$( '.wpostahs-slider-nav' ).each(function( index ) {

		var slider_id   	= $(this).attr('id');
		var slider_nav_id 	= $(this).attr('data-slider-nav-for');
		var slider_conf 	= $.parseJSON( $(this).closest('.wpostahs-slider-wrp').find('.wpostahs-slider-conf').attr('data-conf') );
		
		// For Navigation
		if( typeof(slider_nav_id) != 'undefined' && slider_nav_id != '' ) {
			nav_id = '.'+slider_nav_id;
		}

		if( typeof(slider_id) != 'undefined' && slider_id != '' ) {

			jQuery('.'+slider_nav_id).slick({
				dots			: (slider_conf.dots) == "true" ? true : false,
				infinite		: (slider_conf.loop) == "true" ? true : false,
				arrows			: false,
				speed 			: parseInt(slider_conf.speed),
				autoplay 		: (slider_conf.autoplay) 	== "true" ? true : false,
				fade 			: (slider_conf.fade) 		== "true" ? true : false,
				autoplaySpeed 	: parseInt(slider_conf.autoplayInterval),
				asNavFor 		: '#'+slider_id,
				slidesToShow 	: 1,
				mobileFirst    	: (Wpostahs.is_mobile == 1) ? true : false,
				rtl             : (slider_conf.rtl == "true") ? true : false,
				slidesToScroll 	: 1,
				adaptiveHeight	: true
			});
		}

		// For Navigation
		if( typeof(slider_nav_id) != 'undefined' ) {
			
			jQuery('#'+slider_id).slick({
				slidesToShow 	:  parseInt(slider_conf.slidetoshow),
				slidesToScroll 	: 1,
				infinite		: (slider_conf.loop) == "true" ? true : false,
				asNavFor 		: nav_id,
				arrows			: (slider_conf.arrows) == "true" ? true : false,
				dots 			: false,
				speed 			: parseInt(slider_conf.speed),
				centerMode 		: (slider_conf.nav_centermode) == "true" ? true : false,
				rtl             : (slider_conf.rtl == "true") ? true : false,
				focusOnSelect 	: true,
				centerPadding 	: '10px',
				responsive 		: [
					{
						breakpoint: 768,
						settings: {
							arrows: true,
							centerMode: true,
							centerPadding: '10px',
							slidesToShow: 3
						}
					},
					{
						breakpoint: 480,
						settings: {
							arrows: true,
							centerMode: true,
							centerPadding: '10px',
							slidesToShow: 1
						}
					}
				]
			});
		}
	});
});