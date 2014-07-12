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
<link rel="stylesheet" href="http://www.pvac.leeds.ac.uk/wp-content/themes/UoL/css/uol.min.css" type="text/css" />
<link rel="stylesheet" href="../css/people.css" type="text/css" />
<style type="text/css">
body {
	width:100%;
	max-width:740px;
	margin:0 auto;
	color:#5C5B56;
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
/* go through the test data outputting raw symplectic data, and formatted data using different formatting classes */
$mhra_obj = new sp_mhra_publication();
$mhra_obj->set_display($display);
$default_obj = new sp_default_publication();
$default_obj->set_display($display);
foreach ($pub_types as $type) {
	printf('<h3>Publication type: %s</h3>', $type);
	foreach ( $data as $pub ) {
		if ( $pub['publicationtype'] == $type ) {
			print(get_symplectic_table($pub));
			$default_obj->set_publication($pub);
			printf('<h4>Default formatted entry</h4><div class="publication-formatted"><ul class="publications">%s</ul></div>', $default_obj->get_formatted_publication());
			$mhra_obj->set_publication($pub);
			printf('<h4>MHRA formatted entry</h4><div class="publication-formatted"><ul class="publications mhra">%s</ul></div>', $mhra_obj->get_formatted_publication());
		}
	}
}
?>
<script type="text/javascript" src="../js/people.js"></script>
</body>
</html>