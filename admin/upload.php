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

$fileName   = $_REQUEST["name"];
$targetFile = gmTargetDir( $fileName );

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
	$siteurl 					= get_option( 'siteurl' );
	$targetDirU       = str_replace($siteurl, '', $uploads['url']);

	// try to make grand-media dir if not exists
	if ( ! wp_mkdir_p( $targetDir ) ) {
		$return = json_encode( array( "error" => array( "code" => 100, "message" => sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?', 'gmLang' ), $targetDirU.$gmOptions['folder'][$targetFile['folder']] ) ), "id" => $targetFile['name'] ) );
		die( $return );
	}
	// Check if grand-media dir is writable
	if ( ! is_writable( $targetDir ) ) {
		@chmod( $targetDir, 0755 );
		if ( ! is_writable( $targetDir ) ) {
			$return = json_encode( array( "error" => array( "code" => 100, "message" => sprintf( __( 'Directory %s or its subfolders are not writable by the server.', 'gmLang' ), $targetDirU ) ), "id" => $targetFile['realname'] ) );
			die( $return );
		}
	}
	// Remove old temp files
	if ( $cleanupTargetDir && is_dir( $targetDir ) && ( $dir = opendir( $targetDir ) ) ) {
		while ( ( $file = readdir( $dir ) ) !== false ) {
			$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

			// Remove temp file if it is older than the max age and is not the current file
			if ( preg_match( '/\.part$/', $file ) && ( filemtime( $tmpfilePath ) < time() - $maxFileAge ) && ( $tmpfilePath != $targetDir . '/' . $targetFile['name'] . '.part' ) ) {
				@unlink( $tmpfilePath );
			}
		}

		closedir( $dir );
	}
	else {
		$return = json_encode( array( "error" => array( "code" => 100, "message" => sprintf( __( 'Failed to open directory: %s', 'gmLang' ), $targetDirU.$gmOptions['folder'][$targetFile['folder']] ) ), "id" => $targetFile['realname'] ) );
		die( $return );
	}

	// Open temp file
	$out = fopen( $targetDir . '/' . $targetFile['name'] . '.part', $chunk == 0 ? "wb" : "ab" );
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
			rename( $targetDir . '/' . $targetFile['name'] . '.part', $targetDir . '/' . $targetFile['name'] );

			gmFileChmod( $targetDir . '/' . $targetFile['name'] );

			$url  = $uploads['url'] . $gmOptions['folder'][$targetFile['folder']] . '/' . $targetFile['name'];
			$file = $targetDir . '/' . $targetFile['name'];

			$size = false;
			if ( basename( $targetDir ) == 'image' ) {
				$size = @getimagesize( $targetDir . '/' . $targetFile['name'] );
				if ( $size ) {
					$quality = 90;
					list( $max_w, $max_h ) = explode( 'x', $gmOptions['thumbnail_size'] );
					$crop = 1;
					$suffix = 'thumb';
					$dest_path = $uploads['path'] . $gmOptions['folder']['link'];
					if ( ! is_writable( $dest_path ) ) {
						@chmod( $dest_path, 0755 );
						if ( ! is_writable( $dest_path ) ) {
							$return = json_encode( array( "error" => array( "code" => 100, "message" => sprintf( __( 'Directory %s is not writable by the server.', 'gmLang' ), $targetDirU.$gmOptions['folder']['link'] ) ), "id" => $targetFile['realname'] ) );
							die( $return );
						}
					}
					if( function_exists('wp_get_image_editor') ) {
						$editor = wp_get_image_editor( $file );
						if ( is_wp_error( $editor ) ){
							$return = json_encode( array( "error" => array( "code" => $editor->get_error_code(), "message" => $editor->get_error_message() ) , "id" => $targetFile['name'] ) );
							die( $return );
						}
						$editor->set_quality( $quality );

						$resized = $editor->resize( $max_w, $max_h, $crop );
						if ( is_wp_error( $resized ) ){
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
			$id = $gMDb->gmInsertMedia( $media_data );
			$gMDb->gmUpdateMetaData( $meta_type = 'gmedia', $id, $meta_key = '_metadata', $gMDb->gmGenerateMediaMeta( $id, $file ) );

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

/** Automatic choose upload directory based on media type
 *
 * @param string $fileName
 *
 * @return array
 */
function gmTargetDir( $fileName ) {
	/** @var $wpdb wpdb */
	global $wpdb;

	$result = mysql_query( "SHOW TABLE STATUS LIKE '{$wpdb->prefix}gmedia'" );
	$row    = mysql_fetch_array( $result );
	$nextID = $row['Auto_increment'];
	mysql_free_result( $result );

	$ext           = strrchr( $fileName, '.' );
	$fileName_base = substr( $fileName, 0, strrpos( $fileName, $ext ) );
	// Clean the file Name for security reasons
	$fileTitle       = mysql_real_escape_string( $fileName_base );
	$fileName_base   = preg_replace( '/[^a-z0-9_\.-]+/i', '_', $fileName_base );
	$fileName_id_ext = $fileName_base . '_id' . $nextID . $ext;

	$file = wp_check_filetype( $fileName_id_ext, $mimes = null );
	if ( empty( $file['ext'] ) ) $file['ext'] = ltrim( strrchr( $fileName_id_ext, '.' ), '.' );
	if ( empty( $file['type'] ) ) $file['type'] = 'application/' . $file['ext'];
	$folder            = explode( '/', $file['type'] );
	$file['file_id']   = $nextID;
	$file['folder']    = $folder[0];
	$file['name']      = $fileName_id_ext;
	$file['name_id']   = $fileName_base . '_id' . $nextID;
	$file['name_base'] = $fileName_base;
	$file['realname']  = $fileName;
	$file['title']     = $fileTitle;
	return $file;
}

/** Set correct file permissions (chmod)
 *
 * @param string $new_file
 */
function gmFileChmod( $new_file ) {
	$stat  = stat( dirname( $new_file ) );
	$perms = $stat['mode'] & 0000666;
	@ chmod( $new_file, $perms );
}

die( $return );
