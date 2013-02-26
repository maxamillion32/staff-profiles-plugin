<?php 
/*
Plugin Name: StaffProfiles
Plugin URI: http://essl-pvac.github.com/plugins/staff-profiles-plugin/
Description: Plugin to enable additional profile fields for members of University staff
Author: Peter Edwards / Small Hadron Collider
Author URI: http://www.essl.leeds.ac.uk/for-staff/staff/edwards.html
Version: 1.1
License: GPL3

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! class_exists("StaffProfiles") ):
/**
 * StaffProfiles class
 * Static class used to:
 * - add new fields to profiles
 * - hide some existing fields from profiles
 * - add a custom post type to display the information
 * @version 1.1
 * @author Peter Edwards <p.l.edwards@leeds.ac.uk>
 */
class StaffProfiles
{
	/**
	 * constructors - disallow instantiation
	 */
	final private function StaffProfiles()	{}
	final private function __construct() {}

	/**
	 * plugin version
	 */
	static $version = '1.1';
	
	/**
	 * registers all actions in Wordpress API
	 */
	public static function register()
	{
		/************************************
		 * Profile additional fields        *
		 ************************************/

		/* add actions to show extra fields in profile form */
		add_action( 'show_user_profile', array('StaffProfiles', 'show_extra_profile_fields_user') );
		add_action( 'edit_user_profile', array('StaffProfiles', 'show_extra_profile_fields_admin') );

		/* add actions to save extra fields */
		add_action( 'personal_options_update', array('StaffProfiles', 'save_extra_profile_fields_user') );
		add_action( 'edit_user_profile_update', array('StaffProfiles', 'save_extra_profile_fields_admin') );

		/* filter to remove password fields from profile */
		add_filter('show_password_fields', array('StaffProfiles', 'remove_password_fields'), 10, 1);

		/* filter to remove contact methods fields from profile */
		add_filter('user_contactmethods', array('StaffProfiles', 'remove_contact_methods'), 10, 1);

		/* filters to add paste buttons to the teeny mce editor */
   		add_filter( 'teeny_mce_plugins', array('StaffProfiles', 'teeny_mce_plugins'), 10, 2);
   		add_filter( 'teeny_mce_buttons', array('StaffProfiles', 'teeny_mce_buttons'), 10, 2);

		/* enqueues scripts for admin side */
		add_action( 'admin_enqueue_scripts', array('StaffProfiles', 'add_admin_scripts'));

		/************************************
		 * People post type                 *
		 ************************************/
		
		/* Add People custom post type and staff types taxonomy */
		add_action( 'init', array('StaffProfiles', 'add_custom_type' ), 181 );

		/* customise People post type */
		add_action( 'add_meta_boxes', array('StaffProfiles', 'register_metaboxes' ) );
		add_action( 'save_post', array('StaffProfiles','save_metaboxes' ));

		/* template redirections */
		add_filter( 'single_template', array('StaffProfiles', 'people_template'));
		add_filter( 'archive_template', array('StaffProfiles', 'people_archive_template'));

		/************************************
		 * Plugin administration            *
		 ************************************/

		/* add an admin page */
		add_action('admin_menu', array('StaffProfiles', 'add_admin_page'));

		/* run the upgrade routine */
		add_action( 'init', array('StaffProfiles', 'upgrade'), 182 );
	}
	
	/**
	 * enqueue plugin script
	 */
 	public static function add_admin_scripts()
 	{   
		wp_enqueue_script('staff-profiles-admin-js', plugins_url('/js/admin.min.js', __FILE__), array('jquery'), self::$version, true);
		//wp_enqueue_style('staff-profiles-admin-css', plugins_url('/css/admin.min.css', __FILE__), array('jquery-ui'));
	}

	/* remove password update field */
	public static function remove_password_fields($profile)
	{
		return false;
	}

	/* remove all those groovy contact methods */
	public static function remove_contact_methods($methods)
	{
		return array();
	}
	
	public static function show_extra_profile_fields_admin($user)
	{
		self::show_extra_profile_fields($user, 'admin');
	}
	
	public static function show_extra_profile_fields_user($user)
	{
		self::show_extra_profile_fields($user, 'user');
	}
	
	/**
	 * prints extra profile fields on the end of the user profile editing form
	 * @param object $user WP_User
	 */
	public static function show_extra_profile_fields($user, $priv)
	{
		$fields = self::get_profile_fields($priv);
		$output = "";
		foreach ($fields as $field) {
			switch ($field["type"]) {
				case "richtext":
					$output .= self::get_textarea($user, $field["name"], $field["label"], $field["description"], true);
					break;
				case "textarea":
					$output .= self::get_textarea($user, $field["name"], $field["label"], $field["description"], false);
					break;
				case "text":
					$output .= self::get_textinput($user, $field["name"], $field["label"], $field["description"]);
					break;
				case "checkbox":
					$output .= self::get_checkbox($user, $field["name"], $field["label"], $field["description"]);
					break;
				case "sortable_types":
					$output .= self::get_sortable_types($user, $field["name"], $field["label"], $field["description"]);
					break;
			}
		}
		printf("<table class=\"profile-table form-table\">\n%s</table>\n", $output);
	}
	
	public static function save_extra_profile_fields_admin($user_id)
	{
		self::save_extra_profile_fields($user_id, 'admin');
	}
	
	public static function save_extra_profile_fields_user($user_id)
	{
		self::save_extra_profile_fields($user_id, 'user');
	}
	
	/**
	 * saves data from the extra profile fields to usermeta
	 * @param integer $user_id
	 */
	public static function save_extra_profile_fields($user_id, $priv)
	{
		/* make sure the current user can do this */
		if ( !current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}
		//print_r($_POST);exit;
		$fields = self::get_profile_fields($priv);
		foreach ($fields as $field)
		{
			if($field["type"] == 'checkbox')
			{
				if(isset($_POST[$field["name"]]))
				{
					update_usermeta( $user_id, $field["name"], 1);
				}
				else
				{
					update_usermeta( $user_id, $field["name"], 0);
				}
			}
			else
			{
				update_usermeta( $user_id, $field["name"], $_POST[$field["name"]] );
			}
			if ($field["name"] == 'pubtypes_sortorder') {
				if (isset($_POST['pubtypes_sortorder_delete'])) {
					delete_user_meta( $user_id, 'pubtypes_sortorder');
				}
			}
		}
	}
	
	/**
	 * returns the HTML for a textarea field
	 * @param object $user WP_User
	 * @param string $field_name
	 * @param string $field_label
	 * @param string $field_description
	 * @param $is_richtext
	 */
	public static function get_textarea($user, $field_name, $field_label, $field_description, $is_richtext = false)
	{
		/* wordpress richtext editor ID can only contain lowercase letters! */
		$editor_id = preg_replace( "/[^a-z]*/", "", strtolower($field_name) );
		$out = sprintf("  <tr>\n	<th><label for=\"%s\">%s</label></th>\n", $editor_id, $field_label);
		if ($is_richtext)
		{
			/* use wp-editor but capture output in a buffer */
			ob_start();
			/* options for editor */
			$options = array(
				//"wpautop" => true,
				"media_buttons" => false,
				"textarea_name" => $field_name,
				"textarea_rows" => 3,
				"teeny" => true //use minimal editor configuration
			);
			/* "echo" the editor */
			wp_editor(get_the_author_meta( $field_name, $user->ID ), $editor_id, $options );
			/* get the output buffer */
			$editor = ob_get_contents();
			/* clean the output buffer */
			ob_clean();
			/* put it where we want it */
			$out .= sprintf("	<td>\n	  %s<br />", $editor); 
		} else {
			$out .= sprintf("	<td>\n	  <textarea name=\"%s\" id=\"%s\" rows=\"8\" cols=\"30\">%s</textarea><br />\n", $field_name, $editor_id, esc_attr( get_the_author_meta( $field_name, $user->ID ) ));
		}
		$out .= sprintf("	  <span class=\"description\">%s</span>\n	</td>\n  </tr>\n", $field_description);
		return $out;
	}

	public static function teeny_mce_plugins($plugins, $editor_id)
	{
		if (!isset($plugins["paste"])) {
			array_push($plugins, "paste");
		}
		if (!isset($plugins["charmap"])) {
			array_push($plugins, "charmap");
		}
		return $plugins;
	}

	public static function teeny_mce_buttons($buttons, $editor_id)
	{
		return array('bold', 'italic', 'underline', 'separator','pastetext', 'pasteword', 'removeformat', 'separator', 'charmap', 'blockquote', 'separator', 'bullist', 'numlist', 'separator', 'justifyleft', 'justifycenter', 'justifyright', 'separator', 'undo', 'redo', 'separator', 'link', 'unlink', 'separator', 'fullscreen');
	}
	
	/**
	 * returns the HTML for a text input field
	 * @param object $user WP_User
	 * @param string $field_name
	 * @param string $field_label
	 * @param string $field_description
	 */
	public static function get_textinput($user, $field_name, $field_label, $field_description)
	{
		$out = sprintf("  <tr>\n	<th><label for=\"%s\">%s</label></th>\n", $field_name, $field_label);
		$out .= sprintf("	<td>\n	  <input type=\"text\" name=\"%s\" id=\"%s\" size=\"30\" value=\"%s\" /><br />\n", $field_name, $field_name, esc_attr( get_the_author_meta( $field_name, $user->ID ) ));
		$out .= sprintf("	  <span class=\"description\">%s</span>\n	</td>\n  </tr>\n", $field_description);
		return $out;
	}

	/**
	 * returns the HTML for a sortable list
	 * @param object $user WP_User
	 * @param string $field_name
	 * @param string $field_label
	 * @param string $possible_values (comma separated list)
	 * @param string $field_description
	 */
	public static function get_sortable_types($user, $field_name, $field_label, $field_description)
	{
		/* first see if publication types are ordered for the user */
		$fields = get_user_meta($user->ID, $field_name, true);
		if (!$fields) {
			/* now see if the site option has been set */
			$fields = get_option("pubtypes_sortorder");
			if (!$fields) {
				/* hard-coded defaults */
				$fields = self::$publication_types;
			} else {
				$fields = explode(",", $fields);
			}
		} else {
			$fields = explode(",", $fields);
		}
		return sprintf('<tr><th><label for="%s">%s</label></th><td>%s<p>%s</p><p><input type="checkbox" name="%s_delete" /> Check this box to revert to the default site order.</p></td></tr>', $field_name, $field_label, self::sortable_list($field_name, $fields), $field_description, $field_name);
	}

	/**
	 * returns a sortable HTML unordered list
	 */
	public static function sortable_list($field_name, $fields)
	{
		wp_enqueue_script('jquery-ui-sortable');
		$out = sprintf('<ul id="%s_sortable">', $field_name);
		if ( ! count(array_filter(array_keys($fields), 'is_string'))) {
			/* fields is not associative */
			foreach ($fields as $f) {
				$out .= sprintf('<li data-value="%s">%s</li>', esc_attr($f), $f);
			}
			$start_value = implode(",", $fields);
		} else {
			foreach ($fields as $k => $v) {
				$out .= sprintf('<li data-value="%s">%s</li>', esc_attr($k), $v);
			}
			$start_value = implode(",", array_keys($fields));
		}
		$out .= sprintf('</ul><input type="hidden" id="%s" name="%s" value="%s" />', $field_name, $field_name, $start_value);
		$out .= '<script type="text/javascript">';
		$out .= "\njQuery(function($){\n";
		$out .= "	$('#" . $field_name . "_sortable li').css({border:'1px solid #ccc',background:'#efefef',margin:'1px 0',padding:'3px',display:'block',width:'300px',cursor:'move'});\n";
		$out .= "	$('#" . $field_name . "_sortable').sortable({\n";
		$out .= "		update:function(event,ui){\n";
		$out .= "			var fields = [];\n";
		$out .= "			$('#" . $field_name . "_sortable li').each(function(){\n";
		$out .= "				fields.push($(this).attr('data-value'));\n";
		$out .= "			});\n";
		$out .= "			$('#" . $field_name . "').val(fields.join(','));\n";
		$out .= "		}\n";
		$out .= "	});\n";
		$out .= "	$('#" . $field_name . "_sortable').disableSelection();\n";
		$out .= "});\n</script>\n";
		return $out;
	}
	
	/**
	 * returns the HTML for a checkbox field
	 * @param object $user WP_User
	 * @param string $field_name
	 * @param string $field_label
	 * @param string $field_description
	 */
	public static function get_checkbox($user, $field_name, $field_label, $field_description)
	{
		$checked = '';
		if(esc_attr(get_the_author_meta( $field_name, $user->ID )))
		{
			$checked = 'checked="checked"';
		}
		
		$out = sprintf("  <tr>\n	<th><label for=\"%s\">%s</label></th>\n", $field_name, $field_label);
		$out .= sprintf("	<td>\n	  <input type=\"checkbox\" name=\"%s\" id=\"%s\" $checked />\n", $field_name, $field_name);
		$out .= sprintf("	  <span class=\"description\">%s</span>\n	</td>\n  </tr>\n", $field_description);
		return $out;
	}
	
	/**
	 * gets currently configured profile fields
	 * Currently uses an array to describe the fields, but could use a plugin administration
	 * page to enable different installations to configure the fields
	 */
	public static function get_profile_fields($priv)
	{
		/**
		 * profile fields used in the form
		 */
		$profile_fields = array();
		
		// Show staff type options only on admin page
		if($priv == 'admin')
		{
			$profile_fields[] = array(
				"name" => "pubtypes_sortorder",
				"label" => "Publication sort order",
				"description" => "Sort the order of the publication types in your list by dragging/dropping the items above",
				"type" => "sortable_types"
			);
		}
		
		$profile_fields[] = array(
			"name" => "title",
			"label" => "Title",
			"description" => "Please enter your title (e.g. Dr, Mr, Mrs)",
			"type" => "text"
		);
		
		// On admin page these boxes display anyway
		if($priv == 'user')
		{
			$profile_fields[] = array(
				"name" => "first_name",
				"label" => "First Name",
				"description" => "",
				"type" => "text"
			);
		   $profile_fields[] =  array(
				"name" => "last_name",
				"label" => "Last Name",
				"description" => "",
				"type" => "text"
			);
		}
		
		$temp_array = array(
			array(
				"name" => "telephone",
				"label" => "Telephone",
				"description" => "Please enter your office phone number in the form 0113 123 4567",
				"type" => "text"
			),
			array(
				"name" => "twitter",
				"label" => "Twitter",
				"description" => "Enter your twitter username here",
				"type" => "text"
			),
			array(
				"name" => "location",
				"label" => "Location",
				"description" => "Please enter your office building and room number (e.g. Clothworkers' Building South, Room 1.02)",
				"type" => "text"
			),
			array(
				"name" => "office_hours",
				"label" => "Office Hours",
				"description" => "Please enter your regular office hours (e.g. Monday - Thursday, 1.00pm. - 4.00pm.)",
				"type" => "text"
			),
			array(
				"name" => "position",
				"label" => "Position",
				"description" => "Please enter your formal job title(s) e.g. Senior Lecturer in International Communications",
				"type" => "text"
			),
			array(
				"name" => "qualification",
				"label" => "Qualifications",
				"description" => "Please enter your academic qualifications",
				"type" => "text"
			),
			array(
				"name" => "bio_short",
				"label" => "Biography Summary",
				"description" => "Please enter a very brief summary of your role including areas of expertise and research interests. Max. 30 words",
				"type" => "richtext"
			),
			array(
				"name" => "bio",
				"label" => "Biographical Info",
				"description" => "Please enter a longer biography (150 - 250 words)",
				"type" => "richtext"
			),
			array(
				"name" => "research_interests",
				"label" => "Research Interests",
				"description" => "Please give details of your research list as a series of bullet points followed by a paragraph giving further detail if required",
				"type" => "richtext"
			),
			array(
				"name" => "teaching",
				"label" => "Teaching",
				"description" => "Please provide deteails of the modules you teach / coordinate along with any other teaching responsibilities",
				"type" => "richtext"
			),
			array(
				"name" => "responsibilities",
				"label" => "Departmental Responsibilities",
				"description" => "Please outline any additional responsibilities you have in your department (e.g. Director of Research, Exams Officer etc.)",
				"type" => "richtext"
			),
			array(
				"name" => "publications",
				"label" => "Publications &amp; Research Outputs",
				"description" => "Please enter your six digit payroll ID (e.g. 901234) (feed from Symplectic)",
				"type" => "text"
			),
			array(
				"name" => "publication_status_null",
				"label" => "",
				"description" => "Tick this box to include publications <em>with no status set</em> in your list",
				"type" => "checkbox"
			),
			array(
				"name" => "publication_status_accepted",
				"label" => "",
				"description" => "Tick this box to include &ldquo;Accepted&rdquo; publications in your list",
				"type" => "checkbox"
			),
			array(
				"name" => "publication_status_submitted",
				"label" => "",
				"description" => "Tick this box to include &ldquo;Submitted&rdquo; publications in your list",
				"type" => "checkbox"
			),
			array(
				"name" => "publication_status_in_preparation",
				"label" => "",
				"description" => "Tick this box to include &ldquo;In Preparation&rdquo; publications in your list",
				"type" => "checkbox"
			),
			array(
				"name" => "publication_status_unpublished",
				"label" => "",
				"description" => "Tick this box to include &ldquo;Unpublished&rdquo; publications in your list",
				"type" => "checkbox"
			),
			array(
				"name" => "publication_include_abstract",
				"label" => "",
				"description" => "Tick this box to include the <strong>abstract</strong> field in your  publications list",
				"type" => "checkbox"
			),
			array(
				"name" => "publication_include_notes",
				"label" => "",
				"description" => "Tick this box to include the <strong>notes</strong> field in your  publications list",
				"type" => "checkbox"
			),
			array(
				"name" => "publication_include_authorurl",
				"label" => "",
				"description" => "Tick this box to include the <strong>author url</strong> field in your  publications list",
				"type" => "checkbox"
			),
			array(
				"name" => "publication_include_repositoryurl",
				"label" => "",
				"description" => "Tick this box to include the <strong>repository url</strong> field in your  publications list",
				"type" => "checkbox"
			),
			array(
				"name" => "research_projects",
				"label" => "Research Projects & Grants",
				"description" => "Please provide details of research projects and awards (past, current and proposed)",
				"type" => "richtext"
			),
			array(
				"name" => "research_groups",
				"label" => "Research Centres & Groups",
				"description" => "Please provide details of any research centres / groups of which you are a member.",
				"type" => "richtext"
			),
			array(
				"name" => "appointments",
				"label" => "External Appointments",
				"description" => "e.g. External examiner, editor, member / chair of advisory boards, conference organising committees etc.",
				"type" => "richtext"
			),
			array(
				"name" => "supervision",
				"label" => "PhD & Postdoctoral Supervision",
				"description" => "Please list current and past PhD and postdoctoral supervisees and the topic of their research",
				"type" => "richtext"
			),
			array(
				"name" => "phd",
				"label" => "PhD Thesis",
				"description" => "Please enter the abstract of your PhD thesis and a link to the full text if available",
				"type" => "richtext"
			),
			array(
				"name" => "practice",
				"label" => "Professional Practice",
				"description" => "e.g. exhibitions, performances, compositions, media projects etc.",
				"type" => "richtext"
			),
			array(
				"name" => "links",
				"label" => "Links",
				"description" => "Please provide links to any websites related to your work which may be of interest to visitors",
				"type" => "richtext"
			)
		);
		return array_merge($profile_fields, $temp_array);
	}

	/**
	 * adds a custom post type for people
	 */
	public static function add_custom_type()
	{
	    register_post_type( 'people',
			array(
				'labels' => array(
					'name' => __( 'People' ),
					'singular_name' => __( 'People' )
				),
				'public' => true,
				'has_archive' => true,
				'hierarchical' => true,
				'menu_position' => 20,
				'menu_icon' => plugins_url('/img/menu-icon.png', __FILE__),
				'supports' => array('title','editor','excerpt')
			)
		);
		/* Add new hierarchical taxonomy (like categories) */
		register_taxonomy('staff_type', array('people'), array(
			'hierarchical' => true,
			'labels' => array(
				'name' => 'Staff types',
				'singular_name' => 'Staff types',
				'search_items' => 'Search Staff types',
				'all_items' => 'All Staff types',
				'parent_item' => 'Parent Staff type',
				'parent_item_colon' => 'Parent Staff type:',
				'edit_item' => 'Edit Staff type', 
				'update_item' => 'Update Staff type',
				'add_new_item' => 'Add New Staff type',
				'new_item_name' => 'New Staff type',
				'menu_name' => 'Staff types'
			),
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => false
		));
		/* do the rewrites for the taxonomy independently of Wordpress */
		add_filter( 'generate_rewrite_rules', array('StaffProfiles', 'taxonomy_slug_rewrite') );
	}

	/**
	 * adds rewrite rules for staff type taxonomy
	 * appends the taxonomy slug to the people post type slug
	 */
	function taxonomy_slug_rewrite($wp_rewrite) 
	{
		$rules = array();
		$staff_types = self::get_staff_types(false);
		foreach ($staff_types as $term) {
			$rules['people/' . $term->slug . '/?$'] = 'index.php?staff_type=' . $term->slug;
		}
		$wp_rewrite->rules = $rules + $wp_rewrite->rules;
	}


	/**
	 * adds a menu item to People
	 */
	public static function add_admin_page()
	{
		add_submenu_page('edit.php?post_type=people', 'People admin', 'People admin', 'list_users', 'people-admin', array('StaffProfiles', 'admin_page') );
	}
	
	/**
	 * prints a admin page which is added to the Wordpress Tools menu
	 */
	public static function admin_page()
	{
		if (!current_user_can('list_users')) 
		{
			wp_die('You do not have sufficient permissions to access this page.');
		}
		
		/* get users */
   		$users = get_users('orderby=nicename');

   		/* get people pages */
		$people_pages = get_posts(array(
			'post_type' => 'people',
			'numberposts' => -1
		));

		/* map people page IDs to usernames */
		$pages_map = array();
		foreach ($people_pages as $pp) {
			$staff_id = get_post_meta($pp->ID, 'uol_staff_id', true);
			if ($staff_id) {
				$pages_map[$staff_id] = $pp->ID;
			}
		}

		/* process POST */
		if (isset($_POST['update-order']))	{

			/* update publication type sort order */
		   	update_option("pubtypes_sortorder", $_POST['pubtypes_sortorder']);
		   	
		   	/* update staff type sort order */
		   	update_option("stafftypes_sortorder", $_POST['stafftypes_sortorder']);

		   	/* get staff types AFTER sort option has been updated */
		   	$staff_types = self::get_staff_types();

		} elseif (isset($_POST['update-list'])) {	
		
		   	/* get staff types */
		   	$staff_types = self::get_staff_types();

		   	/* update symplectic ID and staff types */
			foreach ($users as $user) {

				update_user_meta( $user->data->ID, 'publications', $_POST['symplectic-' . $user->data->ID] );
				
				$user_terms = array();
				foreach ($staff_types as $term) {
					if (isset($_POST['is_' . $term->slug . '-' . $user->data->ID]))	{
						$user_terms[] = $term->term_id;
					}
				}
                $user_terms = array_map('intval', $user_terms);
				$user_terms = array_unique( $user_terms );
				if (count($user_terms) && isset($pages_map[$user->data->user_login])) {
					wp_set_post_terms($pages_map[$user->data->user_login], $user_terms, 'staff_type', false);
				}
			}
		} else {

			/* get staff types for later processing */
			$staff_types = self::get_staff_types();
		}

		print('<div class="wrap" id="profile-page"><div id="icon-users" class="icon32"><br></div>');

		/* tabs */
		$tabs = array( 'list' => 'Staff Pages', 'order' => 'Sort Order' );
		$current = ( isset($_GET['tab']) && in_array($_GET['tab'], array_keys($tabs)) )? $_GET['tab']: 'list';

		$submit_button = sprintf('<p class="submit"><input type="submit" name="update-%s" class="button-primary" value="Update"></p>', $current);

		/* output the tabs */
	    print('<h2 class="nav-tab-wrapper">');
    	foreach( $tabs as $tab => $name ){
        	$class = ( $tab === $current ) ? ' nav-tab-active' : '';
       		printf('<a class="nav-tab%s" href="%s">%s</a>', $class, admin_url('edit.php?post_type=people&amp;page=people-admin&amp;tab=' . $tab), $name);
		}
    	print('</h2>');

    	/* output the form */
		printf('<form id="people-admin" action="%s" method="post">', admin_url('edit.php?post_type=people&amp;page=people-admin&amp;tab=' . $current));

		if ($current === "list") {

			echo $submit_button;

	        print('<table class="staff-profiles widefat">');
			$headers = '<tr><th>Staff Name</th><th>Page</th><th>Symplectic ID</th>';
			foreach ($staff_types as $term) {
				$headers .= sprintf('<th>%s</th>', $term->name);
			}
			$headers .= '</tr>';
			printf('<thead>%s</thead><tfoot>%s</tfoot>', $headers, $headers);
			
			foreach ($users as $user) :
				$userID = $user->data->ID;
				if (!isset($pages_map[$user->data->user_login])) {
					$page_link = sprintf('<a href="%s?post_type=people&amp;user_login=%s&amp;display_name=%s" title="Add page for %s" class="button-primary">Add page</a>', admin_url('post-new.php'), $user->data->user_login, htmlspecialchars(rawurlencode($user->data->display_name)), esc_attr($user->data->display_name));
				} else {
					$page_link = sprintf('<a href="%s?action=edit&amp;post=%d" title="Edit page for %s" class="button-secondary">Edit page</a>', admin_url('post.php'), $pages_map[$user->data->user_login], esc_attr($user->data->display_name));
				}
				print('<tr class="person-row">');
				printf('<td><a href="%s" title="click to edit user profile">%s</a></td>', admin_url( 'user-edit.php?user_id=' . $userID), $user->data->display_name);
				printf('<td>%s</td>', $page_link);
				printf('<td><input name="symplectic-%s" type="text" value="%s" /></td>', $userID, get_user_meta( $userID, 'publications', true));
				foreach ($staff_types as $term) {
					if (!isset($pages_map[$user->data->user_login])) {
						$attr = ' disabled="disabled"';
					} else {
						$attr = has_term($term->term_id, 'staff_type', $pages_map[$user->data->user_login])? ' checked="checked"': '';
					}
					printf('<td class="term-cell"><input name="is_%s-%d" class="user-term" type="checkbox"%s /></td>', $term->slug, $userID, $attr);
				}
				print('</tr>');
			endforeach;

			print('</tbody></table>');

			echo $submit_button;
		}

		if ($current === "order") {

			/* staff types sort order */
			print('<h2>Staff types sort order</h2><p>Set the default sort order for the site by dragging and dropping the different staff types in this list:</p>');

			$default_order = array();
			foreach ($staff_types as $term) {
				$default_order[$term->slug] = $term->name;
			}

			echo self::sortable_list("stafftypes_sortorder", $default_order);

			echo $submit_button;

			/* publication types sort order */
			print('<h2>Publications sort order</h2><p>Set the default sort order for the site by dragging and dropping the different publication types in this list:</p>');

			$defaults = get_option("pubtypes_sortorder");

			if (!$defaults) {
				$default_order = self::$publication_types;
			} else {
				$default_order = explode(",", $defaults);
			}
			echo self::sortable_list("pubtypes_sortorder", $default_order);

			echo $submit_button;

		}

		print('</form></div>');
	}

	/**
	 * gets staff types (in the correct order)
	 * currently gets the top level categories ONLY
	 * would need to add in rewrites to display nested categories
	 */
	public static function get_staff_types($include_empty = true)
	{
		$staff_types = get_terms('staff_type', array('hide_empty' => false, 'parent' => 0) );
		$sortorder = get_option('stafftypes_sortorder');
		/* whether to save the sortorder */
		$save_sortorder = false;
		/* array to place sorted terms in */
		$sorted_types = array();
		if ( ! $sortorder ) {
			/* default sort order */
			$sorted_slugs = array('key', 'academic', 'technical', 'visitor', 'phd', 'graduates');
			$save_sortorder = true;
		} else {
			/* get the sortorder */
			$sorted_slugs = explode(",", $sortorder);
		}
		/* keep track of all names */
		$all_slugs = array();
		/* make sure each name is in the sort list */
		foreach ($staff_types as $term) {
			if ( ! in_array($term->slug, $sorted_slugs) ) {
				$sorted_slugs[] = $term->slug;
				$save_sortorder = true;
			}
			$all_slugs[] = $term->slug;
		}
		/* remove any anomalous names */
		$sorted_slugs = array_intersect($sorted_slugs, $all_slugs);
		/* put the terms in order */
		foreach ($sorted_slugs as $type_slug) {
			foreach ($staff_types as $term) {
				if ($term->slug == $type_slug) {
					if ($include_empty) {
						$sorted_types[] = $term;
					} else {
						if ( intval($term->count) > 0 ) {
							$sorted_types[] = $term;
						}
					}
				}
			}
		}
		if ($save_sortorder) {
			update_option('stafftypes_sortorder', implode(",", $sorted_slugs));
		}
		return $sorted_types;
	}

	/**
	 * upgrade method, called in the init action hook after the post type is registered
	 */
	public static function upgrade()
	{
		$current_version = get_option("staffprofiles_version");
		if ($current_version != self::$version) {
			switch ($current_version) {
				case false:
					/**
					 * v1.0 of this plugin had no version added
					 * v1.1 moved staff types to a custom taxonomy
					 */
					$previous_staff_types = array(
						'Key Staff' => 'key',
						'Academic Staff' => 'academic',
						'Technical & Support Staff' => 'technical',
						'Visiting & Emeritus Staff' => 'visitor',
						'PhD Students' => 'phd',
						'Graduates' => 'graduates'
					);

					/* get people pages */
					$people_pages = get_posts(array(
						'post_type' => 'people',
						'numberposts' => -1
					));

					/* map people page IDs to usernames */
					$pages_map = array();
					foreach ($people_pages as $pp) {
						$staff_id = get_post_meta($pp->ID, 'uol_staff_id', true);
						if ($staff_id) {
							$pages_map[$staff_id] = (int) $pp->ID;
						}
					}

					/* get users */
					$users = get_users(array('blog_id' => get_current_blog_id()));

					/* add terms to custom taxonomy */
					$terms = array();
					foreach ($previous_staff_types as $key => $value) {
						/**
						 * inserts terms into the staff_type taxonomy
						 * return values are stored in the terms array for later use:
						 * format: array('term_id'=>,'term_taxonomy_id'=>)
						 * Had real problems with this, so had to do some sanity checks
						 * which shouldn't be strictly neccessary
						 */
						$term = term_exists($value, 'staff_type');
						if ($term === 0 || $term === null) {
							$terms[$value] = wp_insert_term(
								$key, // Staff type name
	  							'staff_type', // Staff types taxonomy
								array(
									'description'=> '',
									'slug' => $value
								)
							);
						} else {
							$terms[$value] = $term;
						}
						if (is_wp_error($terms[$value])) {
							unset($terms[$value]);
						}
					}

					/* get terms from staff profiles and add categories to people pages */
					foreach ($users as $user) {
						$user_terms = array();
						foreach ($previous_staff_types as $key => $value) {
							if (get_user_meta( $user->data->ID, 'is_' . $value, true)) {
								//print('<p>found type ' . $value . ' for user ' . $user->data->user_login . ' [ID:' . $user->data->ID . '] in profile.</p>');
								$user_terms[] = $terms[$value]['term_id'];
								delete_user_meta($user->data->ID, 'is_' . $value);
							}
						}
						$user_terms = array_map('intval', $user_terms);
						$user_terms = array_unique( $user_terms );
						if (count($user_terms)) {
							if (isset($pages_map[$user->data->user_login])) {
								//print('<p>Adding terms ' . implode(',', $user_terms) . ' to people page ' . $pages_map[$user->data->user_login] . '</p>');
								wp_set_object_terms($pages_map[$user->data->user_login], $user_terms, 'staff_type');
							} else {
								//print('<p>No page found for user ' . $user->data->user_login . '</p>');
							}
						} else {
							//print('<p>No types found for user ' . $user->data->user_login . '</p>');
						}
					}

					/* flush rewrite rules */
					global $wp_rewrite;
					$wp_rewrite->flush_rules();

				case '1.1':
					/* upgrade from 1.1 */
			}

			/* update the version option */
			update_option("staffprofiles_version", self::$version);
		}
	}

	/**
	 * registers metaboxes for staff ID and photo
	 */
	public static function register_metaboxes()
	{
	   add_meta_box(
			'staff_id_meta',
			'Staff Wordpress ID / Username',
			array('StaffProfiles', 'staff_id_box'),
			'people', 'normal', 'high'
		);
		add_meta_box(
			'staff_photo_url',
			'Staff Photo URL',
			array('StaffProfiles', 'staff_photo_url_box'),
			'people', 'normal', 'high'
		);
	}
	
	/**
	 * staff ID metabox
	 */
	public static function staff_id_box()
	{
		wp_nonce_field( 'staff_id_meta_check', 'staff_id_meta_nonce' );
		$post_id = false;
		if (isset($_GET["post"])) {
			$post_id = $_GET['post'];
		} elseif (isset($_POST['post_ID'])) {
			$post_id = $_POST['post_ID'];
		}
		$staff_id = get_post_meta($post_id, 'uol_staff_id', true);
		printf('<label for="uol_staff_id">ID / Username</label><input type="text" id="uol_staff_id" name="uol_staff_id" value="%s" />', addslashes($staff_id));
	}

	/**
	 * staff photo metabox
	 */
	public static function staff_photo_url_box()
	{
		if (isset($_GET["post"])) {
			$post_id = $_GET['post'];
		} elseif (isset($_POST['post_ID'])) {
			$post_id = $_POST['post_ID'];
		}
		$staff_photo_url = get_post_meta($post_id, 'uol_staff_photo_url', true);
		printf('<label for="uol_staff_photo_url">Photo URL:</label><input type="text" id="uol_staff_photo_url" name="uol_staff_photo_url" class="widefat" value="%s" />', addslashes($staff_photo_url));
		if ($staff_photo_url != "") {
			printf('<img id="staff_photo_url_preview" style="float:right;margin:5px 0 0 10px" src="%s" />', $staff_photo_url);
		}
		print('<input id="upload_image_button" type="button" value="Upload" /><input id="clear_image_button" type="button" value="Clear" />');
		print('<p>If you are uploading an image here, or choosing an existing image from the Media Library, clicking on the "Insert into Post" button for the image will put the image URL in the box above.</p><br class="clear" />');
	}
	
	/**
	 * saves staff ID and photo URL metabox values
	 */
	public static function save_metaboxes( $post_id )
	{
		/* verify not auto-saving */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
	
		/* verify nonce */
		if ( !isset($_POST['staff_id_meta_nonce']) || !wp_verify_nonce( $_POST['staff_id_meta_nonce'], 'staff_id_meta_check' ) ) {
			return;
		}
	
		/* Check permissions */
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	
		/* save data from metaboxes */
		$id = $_POST['uol_staff_id'];
		add_post_meta($post_id, 'uol_staff_id', $id, true) or update_post_meta($post_id, 'uol_staff_id', $id);
		$photo_url = $_POST['uol_staff_photo_url'];
		add_post_meta($post_id, 'uol_staff_photo_url', $photo_url , true) or update_post_meta($post_id, 'uol_staff_photo_url', $photo_url );

		/* save layout data (do we ned this?) */
		add_post_meta($post_id, 'layout_columns', '3', true);
	}
	
	/**
	 * single template for people post type
	 */
	public static function people_template($single)
	{
		global $wp_query, $post;
		if ($post->post_type == "people") {
			add_action( 'wp_enqueue_scripts', array( 'StaffProfiles', 'enqueue_frontend_scripts_and_styles' ) );
			$path = dirname(__FILE__) . '/single-people.php';
			if (file_exists($path)) {
				return $path;
			}
		}
		return $single;
	}
	
	/**
	 * archive template for people post type
	 */
	public static function people_archive_template($archive)
	{
		global $wp_query, $post;
		if ($post->post_type == "people") {
			add_action( 'wp_enqueue_scripts', array( 'StaffProfiles', 'enqueue_frontend_scripts_and_styles' ) );
			$path = dirname(__FILE__).'/archive-people.php';
			if (file_exists($path)) {
				return $path;
			}
		}
		return $archive;
	}

	/**
	 * adds script and style to frontend
	 */
	public static function enqueue_frontend_scripts_and_styles()
	{
		wp_enqueue_script('staff-profiles-js', plugins_url('/js/people.min.js', __FILE__), array('jquery'), '1.1', true);
		wp_enqueue_style('staff-profiles-css', plugins_url('/css/people.min.css', __FILE__));
	}
   
	/**
	 * Staff Publications
	 */		

	static $publication_types = array(
		"Book",
		"Journal article",
		"Chapter",
		"Conference",
		"Report",
		"Internet publication",
		"Performance",
		"Composition",
		"Exhibition",
		"Other",
		"Artefact",
		"Design",
		"Patent",
		"Software",
		"Poster",
		"Scholarly edition",
		"Thesis / Dissertation"
	);

	/**
	 * filters publication type sort order
	 * default set as site option, but can be overridden by user option
	 * user option currently only available to admins
	 */
	public static function getPublicationTypesFilter($id = false)
	{
		$site_option = get_option("pubtypes_sortorder");
		if (!$site_option) {
			$site_option = implode(",", self::$publication_types);
		}		
		if (!$id) {
			return $site_option;
		} else {
			$user_option = get_user_meta($id, "pubtypes_sortorder", true);
			if (!$user_option) {
				return $site_option;
			} else {
				return $user_option;
			}
		}
	}

	/**
	 * get publications
	 * function which can be used as a static method - originally developed as a shortcode
	 *		
	 *	Possible $attrs
	 *	===============
	 *	
	 *	$id = numeric ID of symplectic lookup (required)
	 *	$cache = how long to cache symplectic feeds for (seconds) [default = 24 hours]
	 *	$limit = returns this amount of most recent out of all publication types
	 *	$sort = date sort order ("desc" = most recent first, "asc" = most recent last) [default = desc]
	 *	$types = comma separated list of publication types [default = all types]
	 *	Possible types:
	 *  Book,Chapter, Conference, Journal article, Patent, Report, Software, Performance, Composition, Design, Artefact, Exhibition, Other, Internet publication, Scholarly edition, Poster, Thesis / Dissertation
	 *  $status = comma separated list of publishing statuses [default = Published only]
	 *  Possible statuses:
	 *  Published, Accepted, Submitted, Unpublished, In preparation
	 */

	public static function get_publications($atts)
	{
		extract($atts);		// Extract attributes

		$output = "";

		if(!isset($header_level))
		{
			$header_level = 'h4';
		}

		if (!isset($id))
		{
			return $output;
		} 
		else
		{
			/* Include the symplectic parsing classes */
			include_once(dirname(__FILE__) . '/classes.php');			
			
			/* Set cache to 24 hours */
			if(!isset($cache)){$cache = 86400;}		
			
			/* Set cache directory to uploads directory */
			$uploads_dir = wp_upload_dir();
			$cacheDir = $uploads_dir["basedir"] . '/';
			/* maybe not! */
			//$cacheDir = dirname(__FILE__) . "/";		
			
			/* Get, cache, and parse the publications */
			$publicationsLookup = new PublicationsLookup($id, $cache, $cacheDir);

			/* get all the possible attributes for publications to use as placeholders */
			$possible_attributes = $publicationsLookup->getPossibleAttrNames();
			
			if(isset($limit))
			{
				$publicationsLookup->sortByAttr('publicationdate', 'asc');
				$publications = $publicationsLookup->returnAmount($limit);

				if (count($publications))
				{
					$output .= "<ul class=\"publications\">";

					foreach($publications as $publication)
					{
						$pub = array();
						foreach ($possible_attributes as $pa)
						{
							$pub[$pa] = $publication->getAttr($pa);
						}
						$output .= self::format_publication($pub);
					}
				
					$output .= "</ul>";
				}
			}
			else
			{
				// Sort by date (desc or asc)
				if(!isset($sort)){$sort = 'desc';}
				$publicationsLookup->sortByAttr('publicationdate', $sort);
				
				// Looks if publication types have been set
				if(isset($types))
				{
					$possibles = explode(',', $types);
				}
				// If not then show all publication types
				else
				{
					$possibles = $publicationsLookup->getPossibleAttrValues('publicationtype');
					sort($possibles);
				}

				// Looks if publication status has been set
				if (isset($status))
				{
					$status = explode(',', $status);
				}
				else
				{
					$status = array("Published");
				}
				
				if (is_array($possibles) && count($possibles))
				{
					
					// Echo header and results for each set of publication types
					foreach($possibles as $possible)
					{
						// Filter by visibility and publication type
						$publications = $publicationsLookup->filterByAttrs(array('isvisible'=>'true', 'publicationtype'=>$possible,'status'=>$status));
			
						// If there are publications of that type
						if(count($publications))
						{
							$output .= "<" . $header_level . ">" . ucwords($possible) . "s</" . $header_level . "><ul class=\"publications\">";
			
							foreach($publications as $publication)
							{
								$pub = array();
								foreach ($possible_attributes as $pa)
								{
									$pub[$pa] = $publication->getAttr($pa);
								}
								$output .= self::format_publication($pub, $display);
							}
							$output .= "</ul>";
						}
					}
				}
			}
		}
		return $output;
	}

	/**
	 * publication formatting methods
	 * one method for each p[ublication type, with some helpers for common fields
	 */
	private static function format_publication($pub, $display)
	{
		$out = '<li class="publication">';
		$methodname = "format_" . preg_replace("/[^a-zA-Z]/", "", $pub["publicationtype"]);
		if (method_exists(__CLASS__, $methodname)) {
			$out .= self::$methodname($pub, $display);
		} else {
			$out .= self::format_Other($pub, $display);
		}
		$out .= "</li>";
		return $out;
	}

	private static function format_Journalarticle($pub, $display)
	{
		$out = "<p>";
		$out .= self::format_basics($pub);
		$out .= self::format_issue($pub);
		$out .= self::format_status($pub);
		$out .= "</p>";
		$out .= self::format_extras($pub, $display);
		return $out;
	}

	private static function format_Conference($pub, $display)
	{
		$out = "<p>";
		$out .= self::format_basics($pub);
		if (trim($pub["conferencename"]) != "") {
			$out .= '<span class="conferencename">' . trim($pub["conferencename"]) . '</span> ';
		}
		if (trim($pub["location"]) != "") {
			$out .= '<span class="location">(' . trim($pub["location"]) . ')</span> ';
		}
		if (isset($pub["startdate"]) && trim($pub["startdate"]) != "") {
			$out .= '<span class="conferencedate">' . self::format_date(trim($pub["startdate"]), true);
			if (isset($pub["finishdate"]) && trim($pub["finishdate"]) != "") {
				$out .= " - " . self::format_date(trim($pub["finishdate"]), true);
			}
			$out .= '</span> ';
		}
		$out .= self::format_publisher($pub);
		$out .= self::format_issue($pub);
		$out .= self::format_status($pub);
		$out .= "</p>";
		$out .= self::format_extras($pub, $display);
		return $out;
	}

	private static function format_Chapter($pub, $display)
	{
		$out = "<p>";
		$out .= self::format_basics($pub);
		$out .= self::format_publisher($pub);
		$out .= self::format_pages($pub);
		$out .= self::format_status($pub);
		$out .= "</p>";
		$out .= self::format_extras($pub, $display);
		return $out;
	}

	private static function format_Other($pub, $display)
	{
		$out = "<p>";
		$out .= self::format_basics($pub);
		$out .= self::format_publisher($pub);
		$out .= self::format_issue($pub);
		$out .= self::format_status($pub);
		$out .= "</p>";
		$out .= self::format_extras($pub, $display);
		return $out;
	}

	private static function format_Book($pub, $display)
	{
		$out = self::format_minimal($pub);
		$out .= self::format_extras($pub, $display);
		return $out;
	}

	private static function format_Internetpublication($pub, $display)
	{
		$out = self::format_minimal($pub);
		$out .= self::format_extras($pub, $display);
		return $out;
	}

	private static function format_Composition($pub, $display)
	{
		$out = self::format_musical($pub);
		$out .= self::format_extras($pub, $display);
		return $out;
	}

	private static function format_Performance($pub, $display)
	{
		$out = self::format_musical($pub);
		$out .= self::format_extras($pub, $display);
		return $out;
	}

	private static function format_Report($pub, $display)
	{
		$out = self::format_minimal($pub);
		$out .= self::format_extras($pub, $display);
		return $out;
	}

	private static function format_Artefact($pub, $display)
	{
		return self::format_minimal($pub);
	}

	private static function format_Design($pub, $display)
	{
		return self::format_minimal($pub, $display);
	}

	private static function format_Scholarlyedition($pub, $display)
	{
		return self::format_minimal($pub);
	}

	private static function format_ThesisDissertation($pub, $display)
	{
		$out = "<p>";
		$out .= self::format_basics($pub);
		if (isset($pub["fileddate"]) && trim($pub["fileddate"]) != "") {
			$out .= ' <span class="fileddate">' . self::format_date($pub["fileddate"], true) . '</span> ';
		}
		$out .= self::format_status($pub);
		$out .= "</p>";
	}

	private static function format_minimal($pub)
	{
		$out = "<p>";
		$out .= self::format_basics($pub);
		$out .= self::format_publisher($pub);
		$out .= self::format_status($pub);
		$out .= "</p>";
		return $out;
	}

	private static function format_musical($pub)
	{
		$out = "<p>";
		$out .= self::format_basics($pub);
		$out .= self::format_publisher($pub);
		if (isset($pub["medium"]) && trim($pub["medium"]) !== "") {
			$out .= '<span class="music-medium">' . trim($pub["medium"]) . '</span> ';
		}
		if (isset($pub["startdate"]) && trim($pub["startdate"]) != "") {
			$out .= '<span class="startdate">' . self::format_date(trim($pub["startdate"]), true) . '</span> ';
		}
		$out .= self::format_status($pub);
		$out .= "</p>";
		return $out;
	}

	private static function format_basics($pub)
	{
		$out = "";
		if (trim($pub["authors"]) != "") {
			$out .= '<span class="authors">' . trim($pub["authors"]) . '</span> ';
		} else {
			if (isset($pub["editors"]) && trim($pub["editors"]) != "") {
				$out .= '<span class="authors">' . trim($pub["editors"]) . ' (eds.) </span>';
			}
		}
		if (isset($pub["publicationyear"]) && trim($pub["publicationyear"]) != "") {
			$out .= '<span class="publicationyear">(' . trim($pub["publicationyear"]) . ')</span> ';
		}
		if (trim($pub["title"]) != "") {
			if ((isset($pub["parenttitle"]) && trim($pub["parenttitle"]) !== "") || (isset($pub["journal"]) && trim($pub["journal"]) !== "")) {
				$class = "title-with-parent";
				$title = "&ldquo;" . trim($pub["title"]) . "&rdquo;";
			} else {
				$class = "title";
				$title = trim($pub["title"]);
			}
			$out .= '<span class="' . $class . '">' . $title;
		}
		if (isset($pub["parenttitle"]) && trim($pub["parenttitle"]) !== "") {
			$out .= ',</span> ';
			$out .= '<em>In:</em> ';
			if (isset($pub["editors"]) && trim($pub["editors"]) != "") {
				$out .= ' <span class="editors">' . trim($pub["editors"]) . ' (eds.)</span>';
			}
			$out .= ' <span class="parent-title">' . trim($pub["parenttitle"]);
		}
		if (isset($pub["journal"]) && trim($pub["journal"]) !== "") {
			$out .= ',</span> ';
			$out .= '<span class="journal">' . trim($pub["journal"]);
			if (isset($pub["editors"]) && trim($pub["editors"]) != "") {
				$out .= ' <span class="editors">' . trim($pub["editors"]) . ' (eds.)</span>';
			}
		}
		$out .= '.</span> ';
		if (isset($pub["edition"]) && trim($pub["edition"]) != "") {
			$sep = (substr(trim($pub["edition"]), -1) == ".")? "": ".";
			$out .= ' <span class="edition">' . trim($pub["edition"]) . $sep . '</span> ';
		}
		if (isset($pub["series"]) && trim($pub["series"]) != "") {
			$sep = (substr(trim($pub["series"]), -1) == ".")? "": ".";
			$out .= ' <span class="series">' . trim($pub["series"]) . $sep . '</span> ';
		}
		return $out;
	}

	private static function format_publisher($pub)
	{
		$out = "";
		if (isset($pub["placeofpublication"]) && trim($pub["placeofpublication"]) != "") {
			$sep = (substr(trim($pub["placeofpublication"]), -1) == ":")? "": ":";
			$out .= ' <span class="publish-place">' . trim($pub["placeofpublication"]) . $sep . '</span>';
		}
		if (isset($pub["publisher"]) && trim($pub["publisher"]) != "") {
			$sep = (substr(trim($pub["publisher"]), -1) == ".")? "": ".";
			if (isset($pub["publisherurl"]) && trim($pub["publisherurl"]) != "") {
				$out .= ' <span class="publisher"><a href="' . trim($pub["publisherurl"]) . '">' . trim($pub["publisher"]) . '</a>' . $sep . '</span>';
			} else {
				$out .= ' <span class="publisher">' . trim($pub["publisher"]) . $sep . '</span>';
			}
		}
		return $out;
	}

	private static function format_pages($pub)
	{
		$out = "";
		if (trim($pub["beginpage"]) != "") {
			$out .= ' <span class="pages">' . trim($pub["beginpage"]);
			if (trim($pub["endpage"]) != "") {
				$out .= '-' . trim($pub["endpage"]);
			} else {
				$out .= "+";
			}
			$out .= '</span>';
		}
		return $out;
	}

	private static function format_issue($pub)
	{
		$out = "";
		if (trim($pub["volume"]) != "") {
			$out .= ' <span class="volume">' . trim($pub["volume"]) . '</span>';
		}
		if (trim($pub["issue"]) != "") {
			$out .= (trim($pub["volume"]) != "")? ".": "";
			$out .= '<span class="issue">' . trim($pub["issue"]) . '</span>';
		}
		$pages = self::format_pages($pub);
		if ($pages) {
			$out .= ': ' . $pages . '.';
		}
		return $out;
	}

	private static function format_status($pub)
	{
		$out = "";
		if (isset($pub["status"]) && $pub["status"] != "Published" && $pub["status"] != "" && strtolower($pub["status"]) != "null") {
			$out .= ' <span class="publish-status">[' . $pub["status"] . ']</span>';
		}
		return $out;
	}

	private static function format_extras($pub, $display)
	{
		$out = "";
		if ($display["notes"] && isset($pub["notes"]) && trim($pub["notes"]) != "") {
			$out .= '<p class="notes">' . trim($pub["notes"]) . '</p>';
		}
		if ($display["authorurl"] && isset($pub["authorurl"]) && trim($pub["authorurl"]) != "") {
			$parsed = parse_url(trim($pub["authorurl"]));
			if ($parsed !== false && isset($parsed["host"]) && trim($parsed["host"]) != "") {
				$out .= '<p class="authorurl"><a href="' . trim($pub["authorurl"]) . '">Author URL [' . trim($parsed["host"]) . ']</a></p>';				
			}
		}
		if ($display["repositoryurl"] && isset($pub["repositoryurl"]) && trim($pub["repositoryurl"]) != "") {
			$parsed = parse_url(trim($pub["repositoryurl"]));
			if ($parsed !== false && isset($parsed["host"]) && trim($parsed["host"]) != "") {
				$out .= '<p class="repositoryurl"><a href="' . trim($pub["repositoryurl"]) . '">Repository URL [' . trim($parsed["host"]) . ']</a></p>';
			}
		}
		if ($display["abstract"] && isset($pub["abstract"]) && trim($pub["abstract"]) != "") {
			$out .= '<p class="abstract">' . trim($pub["abstract"]) . '</p>';
		}
		return $out;
	}

	private static function format_date($date, $fromtime = false)
	{
		$year = substr($date, 0, 4);
		$month = substr($date, 5, 2);
		$day = substr($date, 8, 2);
		if ($fromtime && (mktime(1, 1, 1, $month, $day, $year) !== false)) {
			return (date("j M. Y", mktime(1, 1, 1, $month, $day, $year)));
		}
		return $day . "/" . $month . "/" . $year;
	}

}
StaffProfiles::register();
endif;

?>