/*!
 * staff-profiles plugin
 * scripts for plugin admin pages
 */
;(function($)
{
	/* only do following on the wp-admin profile page - overwise it hides certain tables on other pages */
	if ($('body.wp-admin.profile-php').length) {
	    /* this will hide the table and header for all name options */
	    $('#first_name').parents('table').hide();
	    $('#first_name').parents('table').prev().hide();
	    /* this will hide all the contact info fields */
	    $('#email').parents('table').hide();
	    $('#email').parents('table').prev().hide();
	    /* this will hide all Personal Options */
	    $('#rich_editing').parents('table').hide();
	    $('#rich_editing').parents('table').prev().hide();
    }
    if ($('body.wp-admin.user-edit-php').length) {

        /* Hide the colour/dashboard options */
        $('#rich_editing').parents('table').hide();
	    $('#rich_editing').parents('table').prev().hide();
	    /* Hides the website field */
        //$('#url').parents('tr').hide();
        /* Hides the display name option */
        //$('#display_name').parents('tr').hide();

        /* repace 'you' with 'they' and 'your' with 'their' in descriptions */
        $('.description').each(function () {

            var current = $(this).html(),
                amended = current.replace(' you ', ' they ');
            amended = amended.replace(' your ', ' their ');
            $(this).html(amended);
        });
    }
    
    /* this hides the default biography box */
    $('#description').parents('tr').hide();

    /* image upload */
    $('#upload_image_button').click(function() {
        formfield = 'uol_staff_photo_url';
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	    /* callback for media upload */
	    window._old_send_to_editor = window.send_to_editor;
	    window.send_to_editor = function(html) {
	        imgurl = $('img',html).attr('src');
	        if (typeof(imgurl) === "undefined") {
	            imgurl = $(html).attr('src');
	        }
	        if (typeof(imgurl) !== "undefined") {
	            $('#uol_staff_photo_url').val(imgurl);
	        }
	        tb_remove();
	        $('#uol_staff_photo_url').trigger("change");
	        window.send_to_editor = window._old_send_to_editor;
	    }
        return false;
    });
    $('#clear_image_button').click(function(){
        $('#uol_staff_photo_url').val("");
        $('#uol_staff_photo_url').trigger("change");
    });
    $('#uol_staff_photo_url').change(function(){
        if ($('#uol_staff_photo_url').val() != "") {
            if (!$('#staff_photo_url_preview').length) {
                $('#upload_image_button').before('<img id="staff_photo_url_preview" style="float:right;margin:5px 0 0 10px" />');
            }
            $('#staff_photo_url_preview').attr("src", $('#uol_staff_photo_url').val());
        } else {
            if ($('#staff_photo_url_preview').length) {
                $('#staff_photo_url_preview').remove();
            }
        }
    });
    var login = getQueryVariable('user_login'),
        display_name = getQueryVariable('display_name');
    if (login && display_name) {
    	window.setTimeout(function(){
			$('#title').focus().val(decodeURIComponent(display_name));
			$('#uol_staff_id').val(login);
    	}, 100);
    }
    function getQueryVariable(variable) {
    	var query = window.location.search.substring(1);
    	var vars = query.split('&');
    	for (var i=0; i<vars.length; i++) {
        	var pair = vars[i].split('=');
        	if (pair[0] == variable) {
            	return pair[1];
        	}
    	}
    	return false;
	};
	function colour_blank_rows()
	{
		if ($('.person-row').length) {
			$('.person-row').each(function(){
				if (!$('.user-term:checked', this).length) {
					$('.term-cell', this).css({background:'#eee'});
				} else {
					$('.term-cell', this).css({background:'inherit'});
				}
			});
		}
	}
	$('.user-term').on('click', function(){
		colour_blank_rows();
	});
	colour_blank_rows();
    
})(jQuery);