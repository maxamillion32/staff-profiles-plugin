/**
 * StaffProfiles.js
 * hides profile fields in wordpress and enables TinyMCE
 */

jQuery(function ($)
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
        $('#url').parents('tr').hide();
        /* Hides the display name option */
        $('#display_name').parents('tr').hide();

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

    
});