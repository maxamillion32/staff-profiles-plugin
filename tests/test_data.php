<?php
/**
 * test data for symplectic publications feed formatting
 * scans a directory of cached publication feeds and returns a list of all unique publications
 * weeds out publications where they are the same type and the same feeds are filled in, so
 * all publications of the same type should have unique attributes
 */
require_once('../classes.php');
function get_test_data()
{
	$cache_directory = dirname(__FILE__) . '/data';
	$publications = array();
	$publication_ids = array();
	$publication_track = array();	
	$files = scandir($cache_directory);
	if ( count($files) ) {
		foreach ( $files as $file ) {
			$filepath = $cache_directory . '/' . $file;
			if ( is_file($filepath) ) {
				if ( false !== ($fh = @fopen($filepath, 'r')) ) {
					$input = fread($fh, filesize($filepath));
					fclose($fh);
					$data = unserialize($input);
					if ( count($data) ) {
						foreach ($data as $pub) {
							$attr = $pub->getAttrNames();
							$publication = array();
							foreach ($attr as $att) {
								$publication[$att] = clean_symplectic($pub->getAttr($att));
							}
							if ( ! isset($publication['status']) ) {
								$publication['status'] = "Published";
							}
							if ( ! in_array($publication['publicationid'], $publication_ids) ) {
								$publication_ids[] = $publication['publicationid'];
								$found = false;
								foreach ($publications as $pub) {
									if ($pub['publicationtype'] == $publication['publicationtype'] && has_same_keys($pub, $publication)) {
										$found = true;
									}
								}
								if ( ! $found ) {
									$publications[] = $publication;
								}
							}
						}
					}
				}
			}
		}
	}
	return $publications;
}
function has_same_keys($array1, $array2)
{
	if ( count($array1) !== count($array2) ) {
		return false;
	}
	foreach ($array1 as $key => $value) {
		if ( ! isset($array2[$key]) ) {
			return false;
		}
	}
	return true;
}
function clean_symplectic($text)
{
	return $text;
}
