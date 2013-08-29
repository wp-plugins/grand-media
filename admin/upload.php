<?php
/**
 * upload.php
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */

ini_set( 'display_errors', 0 );
ini_set( 'error_reporting', 0 );

preg_match( '|^(.*?/)(grand-media)/|i', str_replace( '\\', '/', __FILE__ ), $_m );
require_once( $_m[1] . 'grand-media/config.php' );

/** WordPress Image Administration API */
require_once( ABSPATH . 'wp-admin/includes/image.php' );

// HTTP headers for no cache etc
nocache_headers();

if ( ! current_user_can( 'upload_files' ) )
	wp_die( __( 'You do not have permission to upload files.' ) );

check_admin_referer( 'grandMedia' );

// 5 minutes execution time
@set_time_limit( 5 * 60 );

// Uncomment this one to fake upload time
// usleep(5000);

$return = '';
// Get parameters
if ( ! isset( $_REQUEST["name"] ) ) {
	$return = json_encode( array( "error" => array( "code" => 100, "message" => __( "No file name.", 'gmLang' ) ), "id" => $_REQUEST["name"] ) );
	die( $return );
}

global $grandCore;
$fileName   = $_REQUEST["name"];
$targetFile = $grandCore->target_dir( $fileName );

// Look for the content type header
$contentType = '';
if ( isset( $_SERVER["HTTP_CONTENT_TYPE"] ) )
	$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

if ( isset( $_SERVER["CONTENT_TYPE"] ) )
	$contentType = $_SERVER["CONTENT_TYPE"];

// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
if ( strpos( $contentType, "multipart" ) !== false ) {
	if ( isset( $_FILES['file']['tmp_name'] ) && is_uploaded_file( $_FILES['file']['tmp_name'] ) ) {
		$file_tmp = $_FILES['file']['tmp_name'];
	}
	else {
		$return = json_encode( array( "error" => array( "code" => 103, "message" => __( "Failed to move uploaded file.", 'gmLang' ) ), "id" => $fileName ) );
		die( $return );
	}
}
else {
	$file_tmp = "php://input";
}

gmUploadTMP( $file_tmp, $targetFile, $contentType );

/** Write the file
 *
 * @param string $file_tmp
 * @param array  $targetFile
 * @param string $contentType
 */
function gmUploadTMP( $file_tmp, $targetFile, $contentType ) {
	global $grandCore, $gMDb;
	$gmOptions        = get_option( 'gmediaOptions' );
	$cleanupTargetDir = true; // Remove old files
	$maxFileAge       = 5 * 3600; // Temp file age in seconds
	$uploads          = $grandCore->gm_upload_dir();
	$chunk            = isset( $_REQUEST["chunk"] ) ? intval( $_REQUEST["chunk"] ) : 0;
	$chunks           = isset( $_REQUEST["chunks"] ) ? intval( $_REQUEST["chunks"] ) : 0;
	$targetDir        = $uploads['path'] . $gmOptions['folder'][$targetFile['folder']];
	$url  = $uploads['url'] . $gmOptions['folder'][$targetFile['folder']] . '/' . $targetFile['name'];
	$file = $targetDir . '/' . $targetFile['name'];

	// try to make grand-media dir if not exists
	if ( ! wp_mkdir_p( $targetDir ) ) {
		$return = json_encode( array( "error" => array( "code" => 100, "message" => sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?', 'gmLang' ), $targetDir ) ), "id" => $targetFile['name'] ) );
		die( $return );
	}
	// Check if grand-media dir is writable
	if ( ! is_writable( $targetDir ) ) {
		@chmod( $targetDir, 0755 );
		if ( ! is_writable( $targetDir ) ) {
			$return = json_encode( array( "error" => array( "code" => 100, "message" => sprintf( __( 'Directory %s or its subfolders are not writable by the server.', 'gmLang' ), dirname($targetDir) ) ), "id" => $targetFile['realname'] ) );
			die( $return );
		}
	}
	// Remove old temp files
	if ( $cleanupTargetDir && is_dir( $targetDir ) && ( $dir = opendir( $targetDir ) ) ) {
		while ( ( $_file = readdir( $dir ) ) !== false ) {
			$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $_file;

			// Remove temp file if it is older than the max age and is not the current file
			if ( preg_match( '/\.part$/', $_file ) && ( filemtime( $tmpfilePath ) < time() - $maxFileAge ) && ( $tmpfilePath != $file . '.part' ) ) {
				@unlink( $tmpfilePath );
			}
		}

		closedir( $dir );
	}
	else {
		$return = json_encode( array( "error" => array( "code" => 100, "message" => sprintf( __( 'Failed to open directory: %s', 'gmLang' ), $targetDir ) ), "id" => $targetFile['realname'] ) );
		die( $return );
	}

	// Open temp file
	$out = fopen( $file . '.part', $chunk == 0 ? "wb" : "ab" );
	if ( $out ) {
		// Read binary input stream and append it to temp file
		$in = fopen( $file_tmp, "rb" );

		if ( $in ) {
			while ( $buff = fread( $in, 4096 ) ) {
				fwrite( $out, $buff );
			}
		}
		else {
			$return = json_encode( array( "error" => array( "code" => 101, "message" => __( "Failed to open input stream.", 'gmLang' ) ), "id" => $targetFile['name'] ) );
			die( $return );
		}
		fclose( $in );
		fclose( $out );
		if ( strpos( $contentType, "multipart" ) !== false ) {
			@unlink( $file_tmp );
		}
		if ( ! $chunks || $chunk == ( $chunks - 1 ) ) {
			// Strip the temp .part suffix off
			rename( $file.'.part', $file );

			$grandCore->file_chmod( $file );

			$size = false;
			if ( basename( $targetDir ) == 'image' ) {
				$size = @getimagesize( $file );
				if ( $size ) {
					$quality = 90;
					list( $max_w, $max_h ) = explode( 'x', $gmOptions['thumbnail_size'] );
					$crop = 1;
					$suffix = 'thumb';
					$dest_path = $uploads['path'] . $gmOptions['folder']['link'];
					if ( ! is_writable( $dest_path ) ) {
						@chmod( $dest_path, 0755 );
						if ( ! is_writable( $dest_path ) ) {
							@unlink( $file );
							$return = json_encode( array( "error" => array( "code" => 100, "message" => sprintf( __( 'Directory %s is not writable by the server.', 'gmLang' ), $uploads['path'].$gmOptions['folder']['link'] ) ), "id" => $targetFile['realname'] ) );
							die( $return );
						}
					}
					if( function_exists('wp_get_image_editor') ) {
						$editor = wp_get_image_editor( $file );
						if ( is_wp_error( $editor ) ){
							@unlink( $file );
							$return = json_encode( array( "error" => array( "code" => $editor->get_error_code(), "message" => $editor->get_error_message() ) , "id" => $targetFile['name'] ) );
							die( $return );
						}
						$editor->set_quality( $quality );

						$resized = $editor->resize( $max_w, $max_h, $crop );
						if ( is_wp_error( $resized ) ){
							@unlink( $file );
							$return = json_encode( array( "error" => array( "code" => $resized->get_error_code(), "message" => $resized->get_error_message() ) , "id" => $targetFile['name'] ) );
							die( $return );
						}

						$dest_file = $editor->generate_filename( $suffix, $dest_path );
						$saved = $editor->save( $dest_file );

						if ( is_wp_error( $saved ) ){
							@unlink( $file );
							$return = json_encode( array( "error" => array( "code" => $saved->get_error_code(), "message" => $saved->get_error_message() ) , "id" => $targetFile['name'] ) );
							die( $return );
						}
					}
					else {
						$new_file = image_resize( $file, $max_w, $max_h, $crop, $suffix, $dest_path, $quality );
						if ( is_wp_error( $new_file ) ) {
							@unlink( $file );
							$return = json_encode( array( "error" => array( "code" => $new_file->get_error_code(), "message" => $new_file->get_error_message() ) , "id" => $targetFile['name'] ) );
							die( $return );
						}
					}
				}
				else {
					@unlink( $file );
					$return = json_encode( array( "error" => array( "code" => 104, "message" => __( "Could not read image size. Invalid image was deleted.", 'gmLang' ) ), "id" => $targetFile['realname'] ) );
					die( $return );
				}
			}

			// Write media data to DB
			$content = '';
			// TODO Option to set title empty string or from metadata or from filename or both
			$title = $targetFile['title'];
			// use image exif/iptc data for title and caption defaults if possible
			if ( $size ) {
				$image_meta = @wp_read_image_metadata( $file );
				if ( trim( $image_meta['caption'] ) )
					$content = $image_meta['caption'];
				if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
					$title = $image_meta['title'];
			}

			$post_data = array();
			if ( isset( $_POST['postData'] ) )
				parse_str( $_POST['postData'], $post_data );

			// Construct the media array
			$media_data = array(
				'mime_type'   => $targetFile['type'],
				'gmuid'       => $targetFile['name'],
				'title'       => $title,
				'description' => $content
			);
			$media_data = wp_parse_args( $media_data, $post_data );

			// Save the data
			$id = $gMDb->insert_gmedia( $media_data );
			$gMDb->update_metadata( $meta_type = 'gmedia', $id, $meta_key = '_metadata', $gMDb->generate_gmedia_metadata( $id, $file ) );

			$return = json_encode( array( "success" => array( "code" => 200, "message" => sprintf( __( 'File uploaded successful. Assigned ID: %s', 'gmLang' ), $id ) ), "id" => $targetFile['realname'] ) );
			die( $return );
		}
		else {
			$return = json_encode( array( "success" => array( "code" => 199, "message" => $chunk . '/' . $chunks ), "id" => $targetFile['realname'] ) );
			die( $return );
		}
	}
	else {
		$return = json_encode( array( "error" => array( "code" => 102, "message" => __( "Failed to open output stream.", 'gmLang' ) ), "id" => $targetFile['name'] ) );
		die( $return );
	}
}

die( $return );
