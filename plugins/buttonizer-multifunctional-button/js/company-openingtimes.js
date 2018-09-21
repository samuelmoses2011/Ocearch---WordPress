function initOpeningTimes() {
	jQuery(document).ready(function() {
        jQuery(".buttonizer-slider").each(function() {
			var day = jQuery(this).data("day");
			var disabled = jQuery("#buttonizer_"+ day +"_opened").is(":checked");
			var openedFrom = jQuery("#buttonizer_" + day + "_opened_from");
			var closingOn = jQuery("#buttonizer_" + day + "_closing_on");
			var slider = jQuery(this);

			
/* Premium Code Stripped by Freemius */


			if(slider.hasClass("buttonizer-click-to-pro")) {
				slider.slider({
					range: true,
					min: 0,
					max: 1440,
					disabled: true,
					values: [ 420, 1020 ]
				});

				jQuery(".opening-" + day).html("10:00-17:00");
			}
        });
	});
}


/* Premium Code Stripped by Freemius */

