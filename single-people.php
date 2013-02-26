<?php
/**
 * Single post template
 * 
 * @author Peter Edwards <p.l.edwards@leeds.ac.uk>
 * @version 1.1
 * @package Wordpress
 * @subpackage UoL_theme
 */

get_header(); 
    
    $post_id = get_the_ID();
    $user_fullname = get_the_title();
    $photo = get_post_meta($post_id, 'uol_staff_photo_url', true);
    $id = get_post_meta($post_id, 'uol_staff_id', true);
    $user = false;
    
    if ( ! empty($id) ) {
		if ( ! is_numeric($id)) {
			$user = get_userdatabylogin($id);
			$id = $user->ID;
		} else {
			$user = get_userdata($id);
		}
	}
	
	/* display staff image if set / exists */
	if ($photo) :
		printf('<img class="staff-image" src="%s" />', $photo);
	endif;

	/* start profile output */
	print('<div class="staff-box vcard">');

	/* person name (post title) */
	printf('<h2 class="staff-heading1 fn n"><span class="full-name">%s</span></h2>', $user_fullname);

	/* see if we are using profile information, or post content */
	if ( ! $user ) {
		the_content();
	} else {

		/* get all the information for the page from the user profile fields */

   		/* display position if set */
   		$position = get_user_meta( $id, 'position', true );
   		if ( ! empty($position) ) :
			printf('<p class="staff-heading2 position">%s</p>', $position);
		endif;
    		
		/* display email address if set */
		if ($user->user_email != "") :
			printf('<p class="staff-heading2"><a class="email" href="mailto:%s">%s</a></p>', $user->user_email, $user->user_email);
		endif;
    		
		/* display telephone number if set */
		$telephone = get_user_meta( $id, 'telephone', true );
		if ( ! empty($telephone) ) :
			printf('<p class="staff-heading2 tel">%s</p>', $telephone);
		endif;

		/* get twitter stuff */
		$twitter_username = get_user_meta( $id, 'twitter', true );
		if ( ! empty($twitter_username) ) :
			printf('<p class="staff-heading2"><a href="//twitter.com/%s" style="padding:0 1px 1em;"><img src="%s/img/twitter-18x18.png" /></a>', $twitter_username, get_stylesheet_directory_uri());
			printf('<a href="//twitter.com/%s" class="twitter-follow-button" data-show-count="false" data-lang="en">Follow @%s</a></p>', $twitter_username, $twitter_username);
			print('<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>');
		endif;
    		
		/* display location if set */
		$location = get_user_meta( $id, 'location', true );
		if ( ! empty($location) ) :
			printf('<p class="staff-heading2">%s</p>', $location);
		endif;
    		
		/* display office hours number if set */
		$hours = get_user_meta( $id, 'office_hours', true );
		if ( ! empty($hours) ) :
			printf('<p class="staff-heading2">Office hours: %s</p>', $hours);
		endif;

		/* display qualifications if set */
		$qualification = get_user_meta( $id, 'qualification', true );
		if ( ! empty($qualification) ) :
			printf('<p class="staff-heading2">%s</p>', $qualification);
		endif;
		
		/* display biography summary if set */
		$bio_short = get_user_meta( $id, 'bio_short', true );
		if ( ! empty($bio_short) ) :
			echo apply_filters( "the_content", $bio_short );
		endif;

    	/* end of vcard fields */
    	print('</div>');

   		/* display extra information accordion */
   		print('<div class="accordion">');

		/* display long biography if set */
		$bio = get_user_meta( $id, 'bio', true );
		if ( ! empty($bio) ) :
			printf('<h3>Biography</h3><div>%s</div>', apply_filters( "the_content", $bio ));
		endif;

		/* display research interests if set */
		$research_interests = get_user_meta( $id, 'research_interests', true );
		if ( ! empty($research_interests) ) :
			printf('<h3>Research Interests</h3><div>%s</div>', apply_filters( "the_content", $research_interests ));
		endif;
			
		/* display teaching if set */
		$teaching = get_user_meta( $id, 'teaching', true );
		if ( ! empty($teaching) ) :
			printf('<h3>Teaching</h3><div>%s</div>', apply_filters( "the_content", $teaching ));
		endif;
			
		/* display responsibilities if set */
		$responsibilities = get_user_meta( $id, 'responsibilities', true );
		if ( ! empty($responsibilities) ) :
			printf('<h3>Responsibilities</h3><div>%s</div>', apply_filters( "the_content", $responsibilities ));
		endif;
			
		/* display publications if set */
		$symplectic_id = get_user_meta( $id, 'publications', true );
		if ( ! empty($symplectic_id) && class_exists('StaffProfiles') ) :
				
			/* set status filters */
			$status_filter = "Published";
			$status_filter .= get_user_meta( $id, 'publication_status_null', true )? ",null": "";
			$status_filter .= get_user_meta( $id, 'publication_status_accepted', true )? ",Accepted": "";
			$status_filter .= get_user_meta( $id, 'publication_status_submitted', true )? ",Submitted": "";
		    $status_filter .= get_user_meta( $id, 'publication_status_unpublished', true )? ",Unpublished": "";
		    $status_filter .= get_user_meta( $id, 'publication_status_in_preparation', true )? ",In Preparation": "";

		    /* set display filters */
		    $display_filters = array();
		    $display_filters["abstract"] = get_user_meta( $id, 'publication_include_abstract', true )? true: false;
		    $display_filters["notes"] = get_user_meta( $id, 'publication_include_notes', true )? true: false;
		    $display_filters["authorurl"] = get_user_meta( $id, 'publication_include_authorurl', true )? true: false;
		    $display_filters["repositoryurl"] = get_user_meta( $id, 'publication_include_repositoryurl', true )? true: false;

            /* set order of publication types */
            $types_filter = StaffProfiles::getPublicationTypesFilter($id);

			$publications = StaffProfiles::get_publications( array(
				'id' => $symplectic_id,
				'header_level'=>'h4',
				'status' => $status_filter,
				'types' => $types_filter,
				'display' => $display_filters
			) );

			if ($publications != "") :
				printf('<h3>Publications</h3><div>%s</div>', $publications);
			endif;
		endif;
			
		/* display grants if set */
		$research_projects = get_user_meta( $id, 'research_projects', true );
		if ( ! empty($research_projects) ) :
			printf('<h3>Research Projects &amp; Grants</h3><div>%s</div>', apply_filters( "the_content", $research_projects ));
		endif;
			
		/* display research groups if set */
		$research_groups = get_user_meta( $id, 'research_groups', true );
		if ( ! empty($research_groups) ) :
			printf('<h3>Research Centres &amp; Groups</h3><div>%s</div>', apply_filters( "the_content", $research_groups ));
		endif;
			
		/* display appointments if set */
		$appointments = get_user_meta( $id, 'appointments', true );
		if ( ! empty($appointments) ) :
			printf('<h3>External Appointments</h3><div>%s</div>', apply_filters( "the_content", $appointments ));
		endif;
			
		/* display supervision if set */
		$supervision = get_user_meta( $id, 'supervision', true );
		if ( ! empty($supervision) ) :
			printf('<h3>PhD & Postdoctoral Supervision</h3><div>%s</div>', apply_filters( "the_content", $supervision ));
		endif;
			
		/* display phd if set */
		$phd = get_user_meta( $id, 'phd', true );
		if ( ! empty($phd) ) :
			printf('<h3>PhD Thesis</h3><div>%s</div>', apply_filters( "the_content", $phd ));
		endif;
			
		/* display pratice if set */
		$practice = get_user_meta( $id, 'practice', true );
		if ( ! empty($practice) ) :
			printf('<h3>Professional Practice</h3><div>%s</div>', apply_filters( "the_content", $practice ));
        endif;

        /* display links if set */
        $links = get_user_meta( $id, 'links', true );
		if ( ! empty($links) ) :
			printf('<h3>Links</h3><div>%s</div>', apply_filters( "the_content", $links ));
		endif;

		/* end of accordion */
		print('</div>');
	}


get_footer();