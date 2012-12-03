/* script for people post type editor page */
jQuery(function($){
	/* image upload */
    $('#upload_image_button').click(function() {
        formfield = 'uol_staff_photo_url';
        tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
        return false;
    });
    /* callback for media upload */
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
    }
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
});