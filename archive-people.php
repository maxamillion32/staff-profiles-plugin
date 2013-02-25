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
$staff_types = get_terms(
	'staff_type',
	array(
		'hide_empty' => true,
		'child_of' => 0
	)
);


if (!is_tax()) {

	/* make sure we get key staff */
	global $wp_query;
	$args = array_merge( $wp_query->query, array(
		'posts_per_page' => -1,
		'numberposts' => -1,
		'orderby' => 'menu_order',
		'order' => 'ASC',
		'tax_query' => array(
			array(
				'taxonomy' => 'staff_type',
				'field' => 'slug',
				'terms' => 'key'
			)
		)
	));
	$current_term = get_term_by('slug', 'key', 'staff_type');
	query_posts($args);
} else {
	$current_term = $wp_query->get_queried_object();
}

$people = array();

while ( have_posts() ) : the_post();
	
	$staff_id = get_post_meta(get_the_ID(), 'uol_staff_id', true);
    $photo = get_post_meta(get_the_ID(), 'uol_staff_photo_url', true);

	$user = get_user_by('login', $staff_id);
	if ($user) {
		global $post;
		$people[] = array(
			'user' => $user,
			'usermeta' => get_user_meta($user->data->ID),
			'page' => $post
		);
	} else {
		print("no user found with login: " . $user);
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
	printf('<li><a href="%s/people/%s">%s</a></li>', get_bloginfo('url'), $term->slug, $term->name);
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

		$full = ((isset($person["usermeta"]['bio']) && trim($person["usermeta"]['bio'][0] != "")) || (isset($person["usermeta"]['research_interests']) && trim($person["usermeta"]['research_interests'][0]) != ""));

		$pageURL = get_permalink($person["page"]->ID);

		if (isset($person["usermeta"]['staff_image_url']) && trim($person["usermeta"]['staff_image_url'][0]) != "") {
		    $photo = trim($person["usermeta"]['staff_image_url'][0]);
		} else {
			$photo = false;
		}

		$fullname = $person["page"]->post_title;

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
		print $photoHTML;

		printf('<h3 class="fn n">%s</h3>', $nameHTML);
		if (isset($person["usermeta"]['position']) && trim($person["usermeta"]['position'][0]) != "") {
			printf('<p class="position">%s</p>', trim($person["usermeta"]['position'][0]));
		}
		if (isset($person["usermeta"]['bio_short']) && trim($person["usermeta"]['bio_short'][0]) != "") {
			printf('<p></p>', trim($person["usermeta"]['bio_short'][0]));
		}
		print('<p class="contact-details">');
		$email = $person->user->data->user_email;
		$telephone = (isset($person["usermeta"]['telephone']) && trim($person["usermeta"]['telephone'][0]) != "")? trim($person["usermeta"]['telephone'][0]): '';
		$hours = (isset($person["usermeta"]['office_hours']) && trim($person["usermeta"]['office_hours'][0]) != "")? trim($person["usermeta"]['office_hours'][0]): '';
		$location = (isset($person["usermeta"]['location']) && trim($person["usermeta"]['location'][0]) != "")? trim($person["usermeta"]['location'][0]): '';
		     
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

		if ($full) {
			printf('<p class="read-more"><a href="%s">View Full Profile</a></p>', $pageURL);
        }
        print('<div style="clear:both;"></div></div>');
    }
}

get_footer();