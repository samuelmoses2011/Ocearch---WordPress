/**
 *
 * wpMediaUploader v1.0 2016-11-05
 * Copyright (c) 2016 Smartcat
 *
 */
( function(jQuery) {
    var $ = jQuery;
    $.wpMediaUploader = function( options ) {
        var settings = $.extend({

            target : '.smartcat-uploader', // The class wrapping the textbox
            uploaderTitle : 'Select or upload image', // The title of the media upload popup
            uploaderButton : 'Set image', // the text of the button in the media upload popup
            multiple : false, // Allow the user to select multiple images
            buttonText : 'Upload image', // The text of the upload button
            buttonClass : '.smartcat-upload', // the class of the upload button
            previewSize : '32px', // The preview image size
            modal : false, // is the upload button within a bootstrap modal ?
            buttonStyle : { // style the button
                color : '#fff',
                background : '#3bafda',
                fontSize : '16px',
                padding : '10px 15px',
            },

        }, options );

        if(typeof $(settings.target).attr("data-image") != 'undefined') {
            var img = $(settings.target).data("image");
        }else{
            var img = "";
        }

        $( settings.target ).append( '<a href="#" class="' + settings.buttonClass.replace('.','') + ' button">' + settings.buttonText + '</a>' );
        $( settings.target ).append('<div><br><div class="button-preview"><img src="'+ (img == '' ? '#' : img) +'" style="' + (img == "" ? 'display: none;' : '') +' width: ' + settings.previewSize + '; vertical-align: middle;"/></div></div>')

        $( settings.buttonClass ).css( settings.buttonStyle );

        $('body').on('click', settings.buttonClass, function(e) {

            e.preventDefault();
            var selector = $(this).parent( settings.target );
            var custom_uploader = wp.media({
                title: settings.uploaderTitle,
                button: {
                    text: settings.uploaderButton
                },
                multiple: settings.multiple
            })
            .on('select', function() {
                var attachment = custom_uploader.state().get('selection').first().toJSON();
                selector.find( 'img' ).attr( 'src', attachment.url).show();
                selector.find( 'input' ).val(attachment.url);
                if( settings.modal ) {
                    $('.modal').css( 'overflowY', 'auto');
                }
            })
            .open();
        });
    }
})(jQuery);
