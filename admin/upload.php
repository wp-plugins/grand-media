<?php
/**
 * upload.php
 * Copyright 2009, Moxiecode Systems AB
 * Released under GPL License.
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */

ini_set('display_errors', 0);
ini_set('error_reporting', 0);

require_once(dirname(dirname(__FILE__)) . '/config.php');

// HTTP headers for no cache etc
nocache_headers();

if(!current_user_can('gmedia_upload')){
	wp_die(__('You do not have permission to upload files in Gmedia Library.'));
}

check_admin_referer('grandMedia');

// 5 minutes execution time
@set_time_limit(5 * 60);

// fake upload time
usleep(10);

$return = '';
// Get parameters
if(!isset($_REQUEST["name"])){
	$return = json_encode(array("error" => array("code" => 100, "message" => __("No file name.", 'gmLang')), "id" => $_REQUEST["name"]));
	die($return);
}

global $gmCore;
$filename = $_REQUEST["name"];
$fileinfo = $gmCore->fileinfo($filename);

// Look for the content type header
$contentType = '';
if(isset($_SERVER["HTTP_CONTENT_TYPE"])){
	$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
}

if(isset($_SERVER["CONTENT_TYPE"])){
	$contentType = $_SERVER["CONTENT_TYPE"];
}

// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
if(strpos($contentType, "multipart") !== false){
	if(isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])){
		$file_tmp = $_FILES['file']['tmp_name'];
	} else{
		$return = json_encode(array("error" => array("code" => 103, "message" => __("Failed to move uploaded file.", 'gmLang')), "id" => $filename));
		die($return);
	}
} else{
	$file_tmp = "php://input";
}

$post_data = array();
if(isset($_REQUEST['params'])){
	parse_str($_REQUEST['params'], $post_data);
}

$return = $gmCore->gmedia_upload_handler($file_tmp, $fileinfo, $contentType, $post_data);
$return = json_encode($return);

die($return);
