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

$arrayOfIDs = array();

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'key';

/* make sure we get all staff! */
global $wp_query;
$args = array_merge( $wp_query->query, array(
    'posts_per_page' => -1,
    'numberposts' => -1,
    'orderby' => 'menu_order',
    'order' => 'ASC'
));
query_posts($args);

$has_users = array();
$user_fullnames = array();
$user_photos = array();
$userdata = array();

while ( have_posts() ) : the_post();
	
	$userID = get_post_meta(get_the_ID(), 'uol_staff_id', true);
    $photo = get_post_meta(get_the_ID(), 'uol_staff_photo_url', true);

	if(!is_numeric($userID))
    {
        $user = get_userdatabylogin($userID);
        $userID = $user->ID;
    }
    else
    {
        $user = get_userdata($userID);
    }
    
    if(!$userID) {
        continue;
    }

    $userdata[$userID] = $user;

    $user_fullnames[$userID] = get_the_title();
    if ($photo) {
        $user_photos[$userID] = $photo;
    }

    $meta = get_metadata('user', $userID);
    foreach (StaffProfiles::$staff_types as $name => $value) {
        if (isset($meta['is_' . $value]) && $meta['is_' . $value][0] == 1) {
            $has_users[$value] = true;
        }
    }

    if ($filter != 'all' && (!isset($meta['is_' . $filter]) || $meta['is_' . $filter][0] == 0)) {
        continue;
    } 
    
    $arrayOfIDs[] = array($userID, get_the_ID());
    
endwhile; // End the loop

wp_reset_query();

// Sort by last name
function sortByLastName($a, $b) {
    
    $aID = $a[0];
    $bID = $b[0];
    
    $a = get_user_meta( $aID, 'last_name', true);    
    $b = get_user_meta( $bID, 'last_name', true);
    
    if($a < $b) {
        return -1;
    } else if ($a > $b) {
        return 1;
    }
    
    $a = get_user_meta( $aID, 'first_name', true);    
    $b = get_user_meta( $bID, 'first_name', true);
    
    if($a < $b) {
        return -1;
    } else if ($a > $b) {
        return 1;
    }
    
    return 0;
}

/* sort by last name unless key staff */
if ($filter !== 'key') {
    usort($arrayOfIDs, 'sortByLastName');
}

?>
    
    <ul class="tabs">
 <?php /*       <li><a <?php if ($filter=='all'){echo 'class="current"';} ?> href="?filter=all">All Staff</a></li> */ ?>
        <?php
            foreach (StaffProfiles::$staff_types as $name => $value) {
                if (isset($has_users[$value])) {
            ?>
                <li><a <?php if ($filter==$value){echo 'class="current"';} ?> href="?filter=<?php echo $value; ?>"><?php echo $name; ?></a></li>
            <?php
                }
            }
        ?>
    
    </ul>

<?php

if (!count($arrayOfIDs)) {
?>
    <p>No staff of this type</p>
<?php
} else {

    foreach ($arrayOfIDs as $user) {
        
        $userID = $user[0];
        $postID = $user[1];
    ?>
    	<div class="staff-member vcard">
    	<?php
            $full = (get_user_meta( $userID, 'bio', true ) || get_user_meta( $userID, 'research_interests', true ));
            if (isset($user_photos[$userID])) {
                $photo = $user_photos[$userID];
            } else {
        	    $photo = get_user_meta( $userID, 'staff_image_url', true );
            }
            if (isset($user_fullnames[$userID]) && trim($user_fullnames[$userID]) != "") {
                $fullname = $user_fullnames[$userID];
            } else {
                $fullname = trim(get_user_meta( $userID, 'title', true ) . " " . get_user_meta( $userID, 'first_name', true) . " " . get_user_meta( $userID, 'last_name', true));
            }
            if ($full) {
                $nameHTML = sprintf('<a class="url" href="%s" title=%s">%s</a>', get_permalink($postID), $fullname, $fullname);
            } else {
                $nameHTML = $fullname;
            }
    		if ($photo) {
                if ($full) {
                    $photoHTML = sprintf('<a class="url" title="%s" href="%s"><img class="staff-image" src="%s" alt="%s" title="%s" /></a>', $fullname, get_permalink($postID), $photo, $fullname, $fullname);
                } else {
                    $photoHTML = sprintf('<img class="staff-image" src="%s" alt="%s" title="%s" />', $photo, $fullname, $fullname);
                }
            } else {
                $photoHTML = "";
            }
            echo $photoHTML;
        ?>
    	    <h3 class="fn n"><?php echo $nameHTML; ?></h3>
            <p class="position"><?php echo get_user_meta( $userID, 'position', true); ?></p>
            <p><?php echo get_user_meta( $userID, 'bio_short', true); ?></p>
            <p class="contact-details">
                <?php
                    $email = $userdata[$userID]->user_email;
                    $telephone = get_user_meta( $userID, 'telephone', true);
                    $hours = get_user_meta( $userID, 'office_hours', true);
                    $location = get_user_meta( $userID, 'location', true);
                    
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
                ?>
            </p>
            <?php if ($full) { ?>
                <p class="read-more"><a href="<?php echo get_permalink($postID); ?>">View Full Profile</a></p>
            <?php } ?>
            <div style="clear:both;"></div>
        </div>
    <?php
    }
}

get_footer();