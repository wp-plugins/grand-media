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

/** WordPress Image Administration API */
require_once(ABSPATH . 'wp-admin/includes/image.php');

// HTTP headers for no cache etc
nocache_headers();

if(!current_user_can('gmedia_upload')){
	wp_die(__('You do not have permission to upload files.'));
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

gmedia_upload_handler($file_tmp, $fileinfo, $contentType);

/** Write the file
 *
 * @param string $file_tmp
 * @param array  $fileinfo
 * @param string $content_type
 */
function gmedia_upload_handler($file_tmp, $fileinfo, $content_type){
	global $gmGallery, $gmCore;
	$cleanup_dir = true; // Remove old files
	$file_age = 5 * 3600; // Temp file age in seconds
	$chunk = (int) $gmCore->_req('chunk', 0);
	$chunks = (int) $gmCore->_req('chunks', 0);

	// try to make grand-media dir if not exists
	if(!wp_mkdir_p($fileinfo['dirpath'])){
		$return = json_encode(array("error" => array("code" => 100, "message" => sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?', 'gmLang'), $fileinfo['dirpath'])), "id" => $fileinfo['basename']));
		die($return);
	}
	// Check if grand-media dir is writable
	if(!is_writable($fileinfo['dirpath'])){
		@chmod($fileinfo['dirpath'], 0755);
		if(!is_writable($fileinfo['dirpath'])){
			$return = json_encode(array("error" => array("code" => 100, "message" => sprintf(__('Directory %s or its subfolders are not writable by the server.', 'gmLang'), dirname($fileinfo['dirpath']))), "id" => $fileinfo['basename']));
			die($return);
		}
	}
	// Remove old temp files
	if($cleanup_dir && is_dir($fileinfo['dirpath']) && ($_dir = opendir($fileinfo['dirpath']))){
		while(($_file = readdir($_dir)) !== false){
			$tmpfilePath = $fileinfo['dirpath'] . DIRECTORY_SEPARATOR . $_file;

			// Remove temp file if it is older than the max age and is not the current file
			if(preg_match('/\.part$/', $_file) && (filemtime($tmpfilePath) < time() - $file_age) && ($tmpfilePath != $fileinfo['filepath'] . '.part')){
				@unlink($tmpfilePath);
			}
		}

		closedir($_dir);
	} else{
		$return = json_encode(array("error" => array("code" => 100, "message" => sprintf(__('Failed to open directory: %s', 'gmLang'), $fileinfo['dirpath'])), "id" => $fileinfo['basename']));
		die($return);
	}

	// Open temp file
	$out = fopen($fileinfo['filepath'] . '.part', $chunk == 0? "wb" : "ab");
	if($out){
		// Read binary input stream and append it to temp file
		$in = fopen($file_tmp, "rb");

		if($in){
			while(($buff = fread($in, 4096))){
				fwrite($out, $buff);
			}
		} else{
			$return = json_encode(array("error" => array("code" => 101, "message" => __("Failed to open input stream.", 'gmLang')), "id" => $fileinfo['basename']));
			die($return);
		}
		fclose($in);
		fclose($out);
		if(strpos($content_type, "multipart") !== false){
			@unlink($file_tmp);
		}
		if(!$chunks || $chunk == ($chunks - 1)){
			sleep(1);
			// Strip the temp .part suffix off
			rename($fileinfo['filepath'] . '.part', $fileinfo['filepath']);

			$gmCore->file_chmod($fileinfo['filepath']);

			$size = false;
			$is_webimage = false;
			if('image' == $fileinfo['dirname']){
				$size = @getimagesize($fileinfo['filepath']);
				if($size && file_is_displayable_image($fileinfo['filepath'])){
					if(!wp_mkdir_p($fileinfo['dirpath_thumb'])){
						$return = json_encode(array("error" => array("code" => 100, "message" => sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?', 'gmLang'), $fileinfo['dirpath_thumb'])), "id" => $fileinfo['basename']));
						die($return);
					}
					if(!is_writable($fileinfo['dirpath_thumb'])){
						@chmod($fileinfo['dirpath_thumb'], 0755);
						if(!is_writable($fileinfo['dirpath_thumb'])){
							@unlink($fileinfo['filepath']);
							$return = json_encode(array("error" => array("code" => 100, "message" => sprintf(__('Directory %s is not writable by the server.', 'gmLang'), $fileinfo['dirpath_thumb'])), "id" => $fileinfo['basename']));
							die($return);
						}
					}
					if(!wp_mkdir_p($fileinfo['dirpath_original'])){
						$return = json_encode(array("error" => array("code" => 100, "message" => sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?', 'gmLang'), $fileinfo['dirpath_original'])), "id" => $fileinfo['basename']));
						die($return);
					}
					if(!is_writable($fileinfo['dirpath_original'])){
						@chmod($fileinfo['dirpath_original'], 0755);
						if(!is_writable($fileinfo['dirpath_original'])){
							@unlink($fileinfo['filepath']);
							$return = json_encode(array("error" => array("code" => 100, "message" => sprintf(__('Directory %s is not writable by the server.', 'gmLang'), $fileinfo['dirpath_original'])), "id" => $fileinfo['basename']));
							die($return);
						}
					}

					// Optimized image
					$webimg = $gmGallery->options['image'];
					$thumbimg = $gmGallery->options['thumb'];

					$webimg['resize'] = (($webimg['width'] < $size[0]) || ($webimg['height'] < $size[1]))? true : false;
					$thumbimg['resize'] = (($thumbimg['width'] < $size[0]) || ($thumbimg['height'] < $size[1]))? true : false;

					if($webimg['resize']){
						rename($fileinfo['filepath'], $fileinfo['filepath_original']);
					} else{
						copy($fileinfo['filepath'], $fileinfo['filepath_original']);
					}

					if($webimg['resize'] || $thumbimg['resize']){
						$editor = wp_get_image_editor($fileinfo['filepath_original']);
						if(is_wp_error($editor)){
							@unlink($fileinfo['filepath_original']);
							$return = json_encode(array("error" => array("code" => $editor->get_error_code(), "message" => $editor->get_error_message()), "id" => $fileinfo['basename'], "tip" => 'wp_get_image_editor'));
							die($return);
						}

						if($webimg['resize']){
							$editor->set_quality($webimg['quality']);

							$resized = $editor->resize($webimg['width'], $webimg['height'], $webimg['crop']);
							if(is_wp_error($resized)){
								@unlink($fileinfo['filepath_original']);
								$return = json_encode(array("error" => array("code" => $resized->get_error_code(), "message" => $resized->get_error_message()), "id" => $fileinfo['basename'], "tip" => "editor->resize->webimage({$webimg['width']}, {$webimg['height']}, {$webimg['crop']})"));
								die($return);
							}

							$saved = $editor->save($fileinfo['filepath']);
							if(is_wp_error($saved)){
								@unlink($fileinfo['filepath_original']);
								$return = json_encode(array("error" => array("code" => $saved->get_error_code(), "message" => $saved->get_error_message()), "id" => $fileinfo['basename'], "tip" => 'editor->save->webimage'));
								die($return);
							}
						}

						// Thumbnail
						$editor->set_quality($thumbimg['quality']);

						$resized = $editor->resize($thumbimg['width'], $thumbimg['height'], $thumbimg['crop']);
						if(is_wp_error($resized)){
							@unlink($fileinfo['filepath']);
							@unlink($fileinfo['filepath_original']);
							$return = json_encode(array("error" => array("code" => $resized->get_error_code(), "message" => $resized->get_error_message()), "id" => $fileinfo['basename'], "tip" => "editor->resize->thumb({$thumbimg['width']}, {$thumbimg['height']}, {$thumbimg['crop']})"));
							die($return);
						}

						$saved = $editor->save($fileinfo['filepath_thumb']);
						if(is_wp_error($saved)){
							@unlink($fileinfo['filepath']);
							@unlink($fileinfo['filepath_original']);
							$return = json_encode(array("error" => array("code" => $saved->get_error_code(), "message" => $saved->get_error_message()), "id" => $fileinfo['basename'], "tip" => 'editor->save->thumb'));
							die($return);
						}
					} else{
						copy($fileinfo['filepath'], $fileinfo['filepath_thumb']);
					}
					$is_webimage = true;
				} else{
					@unlink($fileinfo['filepath']);
					$return = json_encode(array("error" => array("code" => 104, "message" => __("Could not read image size. Invalid image was deleted.", 'gmLang')), "id" => $fileinfo['basename']));
					die($return);
				}
			}

			// Write media data to DB
			$link = '';
			$description = '';
			// TODO Option to set title empty string or from metadata or from filename or both
			$title = $fileinfo['title'];
			// use image exif/iptc data for title and caption defaults if possible
			if($size){
				$image_meta = @wp_read_image_metadata($fileinfo['filepath_original']);
				if(trim($image_meta['caption'])){
					$description = $image_meta['caption'];
				}
				if(trim($image_meta['title']) && !is_numeric(sanitize_title($image_meta['title']))){
					$title = $image_meta['title'];
				}
			}

			$post_data = array();
			if(isset($_REQUEST['params'])){
				parse_str($_REQUEST['params'], $post_data);

				if(!$is_webimage && isset($post_data['terms']['gmedia_category'])){
					unset($post_data['terms']['gmedia_category']);
				}
			}

			// Construct the media array
			$media_data = array('mime_type' => $fileinfo['mime_type'], 'gmuid' => $fileinfo['basename'], 'title' => $title, 'link' => $link, 'description' => $description);
			$media_data = wp_parse_args($media_data, $post_data);

			global $gmDB;
			// Save the data
			$id = $gmDB->insert_gmedia($media_data);
			$gmDB->update_metadata($meta_type = 'gmedia', $id, $meta_key = '_metadata', $gmDB->generate_gmedia_metadata($id, $fileinfo));

			$return = json_encode(array("success" => array("code" => 200, "message" => sprintf(__('File uploaded successful. Assigned ID: %s', 'gmLang'), $id)), "id" => $fileinfo['basename']));
			die($return);
		} else{
			$return = json_encode(array("success" => array("code" => 199, "message" => $chunk . '/' . $chunks), "id" => $fileinfo['basename']));
			die($return);
		}
	} else{
		$return = json_encode(array("error" => array("code" => 102, "message" => __("Failed to open output stream.", 'gmLang')), "id" => $fileinfo['basename']));
		die($return);
	}
}

die($return);
