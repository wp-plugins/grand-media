<?php
//ini_set( 'display_errors', 0 );
//ini_set( 'error_reporting', 0 );
@ require_once ('config.php');

if ( empty( $_SERVER['HTTP_REFERER'] ) ) {
	die();
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
	global $wpdb, $gmDB;
	if(null === $gmDB->get_gmedia($gmID)){
		die('0');
	}
	$meta['views'] = $gmDB->get_metadata('gmedia', $gmID, 'views', true);
	$meta['likes'] = $gmDB->get_metadata('gmedia', $gmID, 'likes', true);

	$meta = array_map('intval', $meta);
	$meta = gm_hitcounter($gmID, $meta);

	header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
	echo json_encode($meta);
	die();
}

if ( isset($_POST['rate']) ) {
	/** @var $wpdb wpdb */
	global $wpdb, $gmDB;
	/**
	 * @var $uip
	 * @var $gmid
	 * @var $rate
	 */
	extract($_POST['rate'], EXTR_OVERWRITE);
	if(!intval($gmid) || (null === $gmDB->get_gmedia($gmid))){
		die('0');
	}
	$rating = $gmDB->get_metadata('gmedia', $gmid, 'rating', true);
	$old_rate = 0;

	$transient_key = 'gm_rate_day'.date('w');
	$transient_value = get_transient($transient_key);
	if($transient_value){
		if(isset($transient_value[$uip][$gmid])){
			$old_rate = $transient_value[$uip][$gmid];
		}
		$transient_value[$uip][$gmid] = $rate;
	} else{
		$transient_value = array($uip => array($gmid => $rate));
	}
	set_transient($transient_key, $transient_value, 10);

	$rating_votes = $old_rate? $rating['votes'] : $rating['votes'] + 1;
	$rating_value = ($rating['value']*$rating['votes'] + $rate - $old_rate)/$rating_votes;
	$rating = array('value' => $rating_value, 'votes' => $rating_votes);

	$gmDB->update_metadata('gmedia', $gmid, 'rating', $rating);

	header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
	echo json_encode(array($rating));
	die();
}

/**
 * Update media meta in the database
 */
function gm_hitcounter($gmID, $meta) {
	/** @var wpdb $wpdb */
	global $wpdb, $gmDB;
	if( isset($_POST['vote']) ) {
		$meta['likes'] +=1;
		$gmDB->update_metadata('gmedia', $gmID, 'likes', $meta['likes']);
	}
	else {
		$meta['views'] +=1;
		$gmDB->update_metadata('gmedia', $gmID, 'views', $meta['views']);
	}
	return $meta;
}