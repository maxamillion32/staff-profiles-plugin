<?php
/**
 * Test page for publications formatting
 */
require_once("test_data.php");
require_once("../publication_format.php");

/* get the test data */
$data = get_test_data();
usort($data, 'sp_sort_publications');
function sp_sort_publications($a, $b) 
{
	if ( count($a) == count($b) ) {
        return 0;
    }
    return ( count($a) > count($b) ) ? -1 : 1;
}
function get_symplectic_table($pub)
{
	$out = '<h4>Symplectic data</h4><table><thead><tr><th>Field name</th><th>Field content</th></tr></thead><tbody>';
	foreach ( $pub as $field => $value ) {
		$out .= sprintf('<tr><td>%s</td><td>%s</td></tr>', $field, $value);
	}
	$out .= '</tbody></table>';
	return $out;
}

/* set the format */
$format = 'mhra';

/* check it */
if ( ! in_array( $format, array( 'mhra', 'apa', 'harvard' ) ) ) {
	$format = 'mhra';
}

/* set display filters */
$display = array(
	"abstract"      => true,
	"notes"         => false,
	"authorurl"     => true,
	"repositoryurl" => true
);

/* publication type order */
$pub_types = array(
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
?><!DOCTYPE html>
<html>
<head>
<title>Publications format test page</title>
<meta charset="UTF-8">
<link rel="stylesheet" href="http://wwW.pvac.leeds.ac.uk/wp-content/themes/UoL/css/uol.min.css" type="style/css" />
<link rel="stylesheet" href="../css/people.css" type="style/css" />
<style type="text/css">
body {
	width:100%;
	max-width:740px;
	margin:0 auto;
}
table {
	width:100%;
	border:1px solid #333;
	background-color:#ccc;
}
table th,
table td {
	border:1px solid #999;
	margin:2px;
	background-color:#fff;
}
.publication-formatted {
	border:1px dotted #333;
}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
</head>
<body>
<?php
/* get the class name for this format */
$classname = 'sp_' . $format . '_publication';
if ( class_exists( $classname ) ) {
	$format_obj = new $classname();
	$format_obj->set_display($display);
	foreach ($pub_types as $type) {
		printf('<h3>Publication type: %s</h3>', $type);
		foreach ( $data as $pub ) {
			if ( $pub['publicationtype'] == $type ) {
				print(get_symplectic_table($pub));
				$format_obj->set_publication($pub);
				printf('<h4>Formatted entry</h4><div class="publication-formatted"><ul class="publications">%s</ul></div>', $format_obj->get_formatted_publication());
			}
		}
		print('</ul>');
	}
} else {
	die ('class does not exist ' . $classname);
}
?>
<script type="text/javascript" src="../js/people.js"></script>
</body>
</html>