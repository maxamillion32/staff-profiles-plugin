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
?>
			
<?php
    
    $post_id = get_the_ID();
    $user_fullname = get_the_title();
    $photo = get_post_meta($post_id, 'uol_staff_photo_url', true);
    $id = get_post_meta($post_id, 'uol_staff_id', true);
    
    if(!is_numeric($id))
    {
        $user = get_userdatabylogin($id);
        $id = $user->ID;
    }
    else
    {
    	$user = get_userdata($id);
    }
	
	if(isset($id))
	{
	  /* display staff image if set / exists */
	      
        if (!$photo) {
	        $photo = get_user_meta( $id, 'staff_image_url', true );
	    }
	    
		if($photo):
		
	?>
        <img class="staff-image" src="<?php echo $photo; ?>" />
	
	<?php
        
        endif;
	      
	?>
	
    <div class="staff-box vcard">
    
    	<h2 class="staff-heading1 fn n">
        <?php
        	/* display full name taken from People page title */
        	if (trim($user_fullname) != "") :
        ?>
            <span class="full-name"><?php echo $user_fullname; ?></span>
        <?php else:
            /* display first name and last name from profile */
    		$title = get_user_meta( $id, 'title', true );
    		$the_title = $title? '<span class="title">' . $title . '</span>': '';
    		echo $the_title;
    	?>
    	    <span class="given-name"><?php echo get_user_meta( $id, 'first_name', true); ?></span>
            <span class="family-name"><?php echo get_user_meta( $id, 'last_name', true); ?></span>
    	<?php endif; ?>
    	</h2>

		<?php    		
    		/* display position if set */
    		if(get_user_meta( $id, 'position', true )):
    	?>
    	
    	<p class="staff-heading2 position"><?php echo get_user_meta( $id, 'position', true ); ?></p>
    	
    	<?php
    		endif;
    		
    		/* display email address if set */
    		if($user->user_email != ""):
    	?>
    	
    	<p class="staff-heading2"><a class="email" href="mailto:<?php echo $user->user_email; ?>"><?php echo $user->user_email; ?></a></p>
    	
    	<?php
    		endif;
    		
    		/* display telephone number if set */
    		if(get_user_meta( $id, 'telephone', true )):
    	?>
    	
    	<p class="staff-heading2 tel"><?php echo get_user_meta( $id, 'telephone', true ); ?></p>
    	
    	<?php
    		endif;

    		/* get twitter stuff */
    		$twitter_username = get_user_meta( $id, 'twitter', true );
    		if ($twitter_username):
    	?>
    	<p class="staff-heading2"><a href="//twitter.com/<?php echo $twitter_username; ?>" style="padding:0 1px 1em;"><img src="<?php echo get_stylesheet_directory_uri(); ?>/img/twitter-18x18.png" /></a>
    	<a href="//twitter.com/<?php echo $twitter_username; ?>" class="twitter-follow-button" data-show-count="false" data-lang="en">Follow @<?php echo $twitter_username; ?></a></p>
    	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
    	<?php
    		endif;
    		
    		/* display location if set */
    		if(get_user_meta( $id, 'location', true )):
    	?>
    	
    	<p class="staff-heading2"><?php echo get_user_meta( $id, 'location', true ); ?></p>
    	
    	<?php
    		endif;
    		
    		/* display office hours number if set */
    		if(get_user_meta( $id, 'office_hours', true )):
    	?>
    	
    	<p class="staff-heading2">Office hours: <?php echo get_user_meta( $id, 'office_hours', true ); ?></p>
    	
    	<?php
    		endif;

    		/* display qualifications if set */
    		if(get_user_meta( $id, 'qualification', true )):
    	?>
    	
    	<p class="staff-heading2"><?php echo get_user_meta( $id, 'qualification', true ); ?></p>
    	
    	<?php
    		endif;
    		
    		/* display biography summary if set */
    		if(get_user_meta( $id, 'bio_short', true )):
    	?>
    	
    	<?php echo apply_filters( "the_content", get_user_meta( $id, 'bio_short', true ) ); ?>
    	
    	<?php
    		
    		endif;
    		
    		/* display extra information accordion */
    	?>
	</div>
	<div class="accordion">
        <?php
			/* display long biography if set */
			if(get_user_meta( $id, 'bio', true )):
		?>
		
		<h3>Biography</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'bio', true ) ); ?>
		</div>
		
		<?php
			endif;

			/* display research interests if set */
			if(get_user_meta( $id, 'research_interests', true )):
		?>
		
		<h3>Research Interests</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'research_interests', true ) ); ?>
		</div>
		
		<?php
			endif;
			
			/* display teaching if set */
			if(get_user_meta( $id, 'teaching', true )):
		?>
		
		<h3>Teaching</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'teaching', true ) ); ?>
		</div>
		
		<?php
			endif;
			
			/* display responsibilities if set */
			if(get_user_meta( $id, 'responsibilities', true )):
		?>
		
		<h3>Responsibilities</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'responsibilities', true ) ); ?>
		</div>
		
		<?php
			endif;
			
			/* display publications if set */
			if(get_user_meta( $id, 'publications', true )):
			
				$publications = get_user_meta( $id, 'publications', true );
				
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

				/* checks plugin exists then calls the shorcode function directly */
				if (class_exists('StaffProfiles')) :

					$publications = StaffProfiles::get_publications( array(
						'id' => $publications,
						'header_level'=>'h4',
						'status' => $status_filter,
						'types' => $types_filter,
						'display' => $display_filters
					) );

					if ($publications != "") :
					?>
		
		<h3>Publications</h3>
		<div>
		<?php echo $publications; ?>
		</div>
		
		<?php
					endif;
				endif;
			endif;
			
			/* display grants if set */
			if(get_user_meta( $id, 'research_projects', true )):
		?>
		
		<h3>Research Projects & Grants</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'research_projects', true ) ); ?>
		</div>
		
		<?php
			endif;
			
			/* display grants if set */
			if(get_user_meta( $id, 'research_groups', true )):
		?>
		
		<h3>Research Centres & Groups</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'research_groups', true ) ); ?>
		</div>
		
		<?php
			endif;
			
			/* display appointments if set */
			if(get_user_meta( $id, 'appointments', true )):
		?>
		
		<h3>External Appointments</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'appointments', true ) ); ?>
		</div>
		
		<?php
			endif;
			
			/* display supervision if set */
			if(get_user_meta( $id, 'supervision', true )):
		?>
		
		<h3>PhD & Postdoctoral Supervision</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'supervision', true ) ); ?>
		</div>
		
		<?php
			endif;
			
			/* display phd if set */
			if(get_user_meta( $id, 'phd', true )):
		?>
		
		<h3>PhD Thesis</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'phd', true ) ); ?>
		</div>
		
		<?php
			endif;
			
			/* display pratice if set */
			if(get_user_meta( $id, 'practice', true )):
		?>
		
		<h3>Professional Practice</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'practice', true ) ); ?>
		</div>
		
		<?php
            endif;
            /* display pratice if set */
			if(get_user_meta( $id, 'links', true )):
		?>
		
		<h3>Links</h3>
		<div>
		<?php echo apply_filters( "the_content", get_user_meta( $id, 'links', true ) ); ?>
		</div>
		
		<?php endif; ?>
	
	</div>
<?php 
}
?>

<?php get_footer(); ?>