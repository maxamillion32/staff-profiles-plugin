<?php
/**
 * People Post Type archive template
 * 
 * @author Peter Edwards <p.l.edwards@leeds.ac.uk>
 * @version 1.1
 * @package Wordpress
 * @subpackage UoL_theme
 */

get_header();

/* get top level staff types */
$staff_types = StaffProfiles::get_staff_types(false);

/**
 * alter the Wordpress query to:
 * - make a taxonomy query using the first staff type (if no query is made)
 * - disable paging
 */
global $wp_query;
if (!is_tax()) {

	/* make sure we get key staff */
	$args = array_merge( $wp_query->query, array(
		'posts_per_page' => -1,
		'numberposts' => -1,
		'nopaging' => 1,
		'orderby' => 'menu_order',
		'order' => 'ASC',
		'tax_query' => array(
			array(
				'taxonomy' => 'staff_type',
				'field' => 'slug',
				'terms' => $staff_types[0]->slug
			)
		)
	));
	$current_term = $staff_types[0];
	query_posts($args);
} else {
	$args = array_merge( $wp_query->query, array(
		'posts_per_page' => -1,
		'numberposts' => -1,
		'nopaging' => 1
	));
	query_posts($args);
	$current_term = $wp_query->get_queried_object();
}

$people = array();

while ( have_posts() ) : the_post();
	
	$staff_id = get_post_meta(get_the_ID(), 'uol_staff_id', true);
    $photo = get_post_meta(get_the_ID(), 'uol_staff_photo_url', true);

	if ( ! empty($staff_id) ) {
		$user = get_user_by('login', $staff_id);
		if ($user) {
			global $post;
			$people[] = array(
				'user' => $user,
				'usermeta' => get_user_meta($user->data->ID),
				'page' => $post,
				'photo' => $photo
			);
		} else {
			//print("no user found with login: " . $user);
		}
	} else {
		$people[] = array(
			'user' => false,
			'usermeta' => false,
			'page' => $post,
			'photo' => $photo
		);
	}
    
endwhile; // End the loop

// Sort by last name
function sortByLastName($a, $b)
{
    if($a["usermeta"]["last_name"][0] < $b["usermeta"]["last_name"][0]) {
        return -1;
    } else if ($a["usermeta"]["last_name"][0] > $b["usermeta"]["last_name"][0]) {
        return 1;
    }
    return 0;
}
// Sort by menu order
function sortByMenuOrder($a, $b) {
	if ($a["page"]->menu_order < $b["page"]->menu_order) {
		return -1;
	} else if ($a["page"]->menu_order > $b["page"]->menu_order) {
		return 1;
	}
	return 0;
}

/* sort by last name unless key staff */
if ($current_term->slug == 'key') {
	usort($people, 'sortByMenuOrder');
} else {
    usort($people, 'sortByLastName');
}

/* print out top staff type tabs */
print('<ul class="tabs">');
foreach ($staff_types as $term) {
	$class = ($term->slug == $current_term->slug)? ' class="current"': '';
	printf('<li><a href="%s/people/%s"%s>%s</a></li>', get_bloginfo('url'), $term->slug, $class, $term->name);
}
print('</ul>');

if (!count($people)) {
?>
    <p>No staff of this type</p>
<?php
} else {

    foreach ($people as $person) {
    	//printf('<pre>%s</pre>', print_r($person, true));
        
		print('<div class="staff-member vcard">');

		/* determine whether there is more content for the current profile */
		$full = ((isset($person["usermeta"]['bio']) && trim($person["usermeta"]['bio'][0] != "")) || (isset($person["usermeta"]['research_interests']) && trim($person["usermeta"]['research_interests'][0]) != "") || (!$person['user'] && trim(strip_tags($person['page']->post_content)) == ""));

		/* get the page URL */
		$pageURL = get_permalink($person["page"]->ID);

		/* get the staff photo */
		if ($person['photo'] && trim($person['photo']) != "") {
		    $photo = trim($person['photo']);
		} else {
			$photo = false;
		}

		/* get the full name */
		$fullname = $person["page"]->post_title;

		/* if there is more content, link from the name and profile picture */
		if ($full) {
		    $nameHTML = sprintf('<a class="url" href="%s" title="%s">%s</a>', $pageURL, esc_attr($fullname), $fullname);
		} else {
		    $nameHTML = $fullname;
		}
		if ($photo) {
		    if ($full) {
		        $photoHTML = sprintf('<a class="url" title="%s" href="%s"><img class="staff-image" src="%s" alt="%s" title="%s" /></a>', esc_attr($fullname), $pageURL, $photo, esc_attr($fullname), esc_attr($fullname));
		    } else {
		        $photoHTML = sprintf('<img class="staff-image" src="%s" alt="%s" title="%s" />', $photo, esc_attr($fullname), esc_attr($fullname));
		    }
		} else {
		    $photoHTML = "";
		}

		/* print staff photo */
		print $photoHTML;

		/* print staff name */
		printf('<h3 class="fn n">%s</h3>', $nameHTML);

		/* print position */
		if (isset($person["usermeta"]['position']) && trim($person["usermeta"]['position'][0]) != "") {
			printf('<p class="position">%s</p>', trim($person["usermeta"]['position'][0]));
		}

		/* output the short biography, or the page excerpt for people not linked to a username */
		if (isset($person["usermeta"]['bio_short']) && trim($person["usermeta"]['bio_short'][0]) != "") {
			printf('<p>%s</p>', trim($person["usermeta"]['bio_short'][0]));
		} elseif (!$person["usermeta"] && trim(strip_tags($person['page']->post_excerpt)) != "") {
			print($person['page']->post_excerpt);
		}

		/* find out which of the contact location/hours fields have been filled in */
		$email = $person['user']->data->user_email;
		$telephone = (isset($person["usermeta"]['telephone']) && trim($person["usermeta"]['telephone'][0]) != "")? trim($person["usermeta"]['telephone'][0]): '';
		$hours = (isset($person["usermeta"]['office_hours']) && trim($person["usermeta"]['office_hours'][0]) != "")? trim($person["usermeta"]['office_hours'][0]): '';
		$location = (isset($person["usermeta"]['location']) && trim($person["usermeta"]['location'][0]) != "")? trim($person["usermeta"]['location'][0]): '';

		/* output the contact details */
		if (!empty($email) || !empty($telephone) || !empty($hours) || !empty($location)) {
			print('<p class="contact-details">');
			if ($email) {
				printf('<a class="email" href="mailto:%1$s">%1$s</a>', $email);
			}
			if ($email && $telephone) {
			    print(' / ');
			}
			if ($telephone) {
			    printf('<span class="tel">%s</span>', $telephone);	
			}
			if ($email || $telephone) {
			    print('<br />');
			}
			if ($location) {
			    printf('<span class="location">%s</span>', $location);
			}
			if ($location && $hours) {
			    print('<br />');
			}
			if ($hours) {
			    printf('<span class="hours">Office hours: %s</span>', $hours);
			}
			print('</p>');
		}

		/* link to the full profile if it exists */
		if ($full) {
			printf('<p class="read-more"><a href="%s">View Full Profile</a></p>', $pageURL);
        }

        print('<div style="clear:both;"></div></div>');
    }
}

get_footer();