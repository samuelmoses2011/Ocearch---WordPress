/* GENERATE RETINA IMAGES ACTION */

var current;
var maxPhpSize = wr2x_admin_server.maxFileSize;
var ids = [];
var errors = 0;
var ajax_action = "generate"; // generate | delete

function wr2x_display_please_refresh() {
	wr2x_refresh_progress_status();
	jQuery('#wr2x_progression').html(jQuery('#wr2x_progression').html() + " - " + wr2x_admin_server.i18n.Refresh);
}

function wr2x_refresh_progress_status() {
	var errortext = "";
	if ( errors > 0 ) {
		errortext = ' - ' + errors + ' error(s)';
	}
	jQuery('#wr2x_progression').text(current + "/" + ids.length +
		" (" + Math.round(current / ids.length * 100) + "%)" + errortext);
}

function wr2x_do_next () {
	var data = { action: 'wr2x_' + ajax_action, attachmentId: ids[current - 1] };
	data.nonce = wr2x_admin_server.nonce[data.action];

	wr2x_refresh_progress_status();
	jQuery.post(ajaxurl, data, function (response) {
		try {
			reply = jQuery.parseJSON(response);
		}
		catch (e) {
			reply = null;
		}
		if ( !reply || !reply.success )
			errors++;
		else {
			wr2x_refresh_media_sizes(reply.results);
			if (reply.results_full)
				wr2x_refresh_full(reply.results_full);
		}
		if (++current <= ids.length)
			wr2x_do_next();
		else {
			current--;
			wr2x_display_please_refresh();
		}
	}).fail(function () {
		errors++;
		if (++current <= ids.length)
			wr2x_do_next();
		else {
			current--;
			wr2x_display_please_refresh();
		}
	});
}

function wr2x_do_all () {
	current = 1;
	ids = [];
	errors = 0;
	var data = { action: 'wr2x_list_all', issuesOnly: 0 };
	data.nonce = wr2x_admin_server.nonce[data.action];

	jQuery('#wr2x_progression').text(wr2x_admin_server.i18n.Wait);
	jQuery.post(ajaxurl, data, function (response) {
		reply = jQuery.parseJSON(response);
		if (reply.success = false) {
			alert('Error: ' + reply.message);
			return;
		}
		if (reply.total == 0) {
			jQuery('#wr2x_progression').html(wr2x_admin_server.i18n.Nothing_to_do);
			return;
		}
		ids = reply.ids;
		jQuery('#wr2x_progression').text(current + "/" + ids.length + " (" + Math.round(current / ids.length * 100) + "%)");
		wr2x_do_next();
	});
}

function wr2x_delete_all () {
	ajax_action = 'delete';
	wr2x_do_all();
}

function wr2x_generate_all () {
	ajax_action = 'generate';
	wr2x_do_all();
}

// Refresh the dashboard retina full with the results from the Ajax operation (Upload)
function wr2x_refresh_full (results) {
	jQuery.each(results, function (id, html) {
		jQuery('#wr2x-info-full-' + id).html(html);
		jQuery('#wr2x-info-full-' + id + ' img').attr('src', jQuery('#wr2x-info-full-' + id + ' img').attr('src')+'?'+ Math.random());
		jQuery('#wr2x-info-full-' + id + ' img').on('click', function (evt) {
			wr2x_delete_full( jQuery(evt.target).parents('.wr2x-file-row').attr('postid') );
		});
	});
}

// Refresh the dashboard media sizes with the results from the Ajax operation (Replace or Generate)
function wr2x_refresh_media_sizes (results) {
	jQuery.each(results, function (id, html) {
		jQuery('#wr2x-info-' + id).html(html);
	});
}

function wr2x_generate (attachmentId, retinaDashboard) {
	var data = { action: 'wr2x_generate', attachmentId: attachmentId };
	data.nonce = wr2x_admin_server.nonce[data.action];

	jQuery('#wr2x_generate_button_' + attachmentId).text(wr2x_admin_server.i18n.Wait);
	jQuery.post(ajaxurl, data, function (response) {
		var reply = jQuery.parseJSON(response);
		if (!reply.success) {
			alert(reply.message);
			return;
		}
		jQuery('#wr2x_generate_button_' + attachmentId).html(wr2x_admin_server.i18n.Generate);
		wr2x_refresh_media_sizes(reply.results);
	});
}

/* REPLACE FUNCTION */

function wr2x_stop_propagation(evt) {
	evt.stopPropagation();
	evt.preventDefault();
}

function wr2x_delete_full(attachmentId) {
	var data = {
		action: 'wr2x_delete_full',
		isAjax: true,
		attachmentId: attachmentId
	};
	data.nonce = wr2x_admin_server.nonce[data.action];

	jQuery.post(ajaxurl, data, function (response) {
		var data = jQuery.parseJSON(response);
		if (data.success === false) {
			alert(data.message);
		}
		else {
			wr2x_refresh_full(data.results);
			wr2x_display_please_refresh();
		}
	});
}

function wr2x_load_details(attachmentId) {
	var data = {
		action: 'wr2x_retina_details',
		isAjax: true,
		attachmentId: attachmentId
	};
	data.nonce = wr2x_admin_server.nonce[data.action];

	jQuery.post(ajaxurl, data, function (response) {
		var data = jQuery.parseJSON(response);
		if (data.success === false) {
			alert(data.message);
		}
		else {
			jQuery('#meow-modal-info .loading').css('display', 'none');
			jQuery('#meow-modal-info .content').html(data.result);
		}
	});
}

function wr2x_filedropped (evt) {
	wr2x_stop_propagation(evt);
	var files = evt.dataTransfer.files;
	var count = files.length;
	if (count < 0) {
		return;
	}

	var wr2x_replace = jQuery(evt.target).parent().hasClass('wr2x-fullsize-replace');
	var wr2x_upload = jQuery(evt.target).parent().hasClass('wr2x-fullsize-retina-upload');

	function wr2x_handleprogress(prg) {
		console.debug("Upload of " + prg.srcElement.filename + ": " + prg.loaded / prg.total * 100 + "%");
	}

	function wr2x_uploadFile(file, attachmentId, filename) {
		var action = "";
		if (wr2x_replace) {
			action = 'wr2x_replace';
		}
		else if (wr2x_upload) {
			action = 'wr2x_upload';
		}
		else {
			alert("Unknown command. Contact the developer.");
		}
		var data = new FormData();
	data.append('file', file);
		data.append('action', action);
		data.append('attachmentId', attachmentId);
		data.append('isAjax', true);
		data.append('filename', filename);
		data.append('nonce', wr2x_admin_server.nonce[action]);

		// var data = {
		// 	action: action,
		// 	isAjax: true,
		// 	filename: evt.target.filename,
		// 	data: form_data,
		// 	attachmentId: attachmentId
		// };

		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			contentType: false,
			processData: false,
			data: data,
			success: function (response) {
				jQuery('[postid=' + attachmentId + '] td').removeClass('wr2x-loading-file');
				jQuery('[postid=' + attachmentId + '] .wr2x-dragdrop').removeClass('wr2x-hover-drop');
				try {
					var data = jQuery.parseJSON(response);
				}
				catch (e) {
					alert("The server-side returned an abnormal response. Check your PHP error logs and also your browser console (WP Retina 2x will try to display it there).");
					console.debug(response);
					return;
				}
				if (wr2x_replace) {
					var imgSelector = '[postid=' + attachmentId + '] .wr2x-info-thumbnail img';
					jQuery(imgSelector).attr('src', jQuery(imgSelector).attr('src')+'?'+ Math.random());
				}
				if (wr2x_upload) {
					var imgSelector = '[postid=' + attachmentId + '] .wr2x-info-full img';
					jQuery(imgSelector).attr('src', jQuery(imgSelector).attr('src')+'?'+ Math.random());
				}
				if (data.success === false) {
					alert(data.message);
				}
				else {
					if ( wr2x_replace ) {
						wr2x_refresh_media_sizes(data.results);
					}
					else if ( wr2x_upload ) {
						wr2x_refresh_full(data.results);
					}
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				jQuery('[postid=' + attachmentId + '] td').removeClass('wr2x-loading-file');
				jQuery('[postid=' + attachmentId + '] .wr2x-dragdrop').removeClass('wr2x-hover-drop');
				alert("An error occurred on the server-side. Please check your PHP error logs.");
		  }
		});
	}
	var file = files[0];
	if (file.size > maxPhpSize) {
		jQuery(this).removeClass('wr2x-hover-drop');
		alert( "Your PHP configuration only allows file upload of a maximum of " + (maxPhpSize / 1000000) + "MB." );
		return;
	}
	var postId = jQuery(evt.target).parents('.wr2x-file-row').attr('postid');
	jQuery(evt.target).parents('td').addClass('wr2x-loading-file');
	wr2x_uploadFile(file, postId, file.name);
}

jQuery(document).ready(function () {
	jQuery('.wr2x-dragdrop').on('dragenter', function (evt) {
		wr2x_stop_propagation(evt);
		jQuery(this).addClass('wr2x-hover-drop');
	});

	jQuery('.wr2x-dragdrop').on('dragover', function (evt) {
		wr2x_stop_propagation(evt);
		jQuery(this).addClass('wr2x-hover-drop');
	});

	jQuery('.wr2x-dragdrop').on('dragleave', function (evt) {
		wr2x_stop_propagation(evt);
		jQuery(this).removeClass('wr2x-hover-drop');
	});

	jQuery('.wr2x-dragdrop').on('dragexit', wr2x_stop_propagation);

	jQuery('.wr2x-dragdrop').each(function (index, elem) {
		this.addEventListener('drop', wr2x_filedropped);
	});

	jQuery('.wr2x-info, .wr2x-button-view').on('click', function (evt) {
		jQuery('#meow-modal-info-backdrop').css('display', 'block');
		jQuery('#meow-modal-info .content').html("");
		jQuery('#meow-modal-info .loading').css('display', 'block');
		jQuery('#meow-modal-info').css('display', 'block');
		jQuery('#meow-modal-info').focus();
		var postid = jQuery(evt.target).parents('.wr2x-info').attr('postid');
		if (!postid)
			postid = jQuery(evt.target).parents('.wr2x-file-row').attr('postid');
		wr2x_load_details(postid);
	});

	jQuery('#meow-modal-info .close, #meow-modal-info-backdrop').on('click', function (evt) {
		jQuery('#meow-modal-info').css('display', 'none');
		jQuery('#meow-modal-info-backdrop').css('display', 'none');
	});

	jQuery('.wr2x-info-full img').on('click', function (evt) {
		wr2x_delete_full( jQuery(evt.target).parents('.wr2x-file-row').attr('postid') );
	});

	jQuery('#meow-modal-info').bind('keydown', function (evt) {
		if (evt.keyCode === 27) {
			jQuery('#meow-modal-info').css('display', 'none');
			jQuery('#meow-modal-info-backdrop').css('display', 'none');
		}
	});
});
