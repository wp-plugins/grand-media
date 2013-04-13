<?php
@ require_once ('config.php');
if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
	die('0');
}
$ref = $_SERVER['HTTP_REFERER'];
if ( (false === strpos( $ref, get_home_url() )) && (false === strpos( $ref, get_site_url()) )) {
	echo 'referer:'.$_SERVER['HTTP_REFERER']."\n";
	echo 'home_url:'.get_home_url()."\n";
	echo 'site_url:'.get_site_url()."\n";
	die('-1');
}
if ( isset($_POST['hit']) && ($gmID = intval($_POST['hit'])) ) {
	/** @var $wpdb wpdb */
	global $wpdb, $gMDb;
	$meta['views'] = $gMDb->gmGetMetaData('gmedia', $gmID, 'views', true);
	$meta['likes'] = $gMDb->gmGetMetaData('gmedia', $gmID, 'likes', true);
	gm_hitcounter($gmID, $meta);
	$return = json_encode($meta);
	die($return);
}
/**
 * Update media meta in the database
 */
function gm_hitcounter($gmID, $meta) {
	/** @var wpdb $wpdb */
	global $wpdb, $gMDb;
	$meta = array_map('intval', $meta);
	if( isset($_POST['like']) ) {
		$gMDb->gmUpdateMetaData('gmedia', $gmID, 'likes', $meta['likes'] + 1);
	}
	else {
		$gMDb->gmUpdateMetaData('gmedia', $gmID, 'views', $meta['views'] + 1);
	}
}
