<?php

ini_set('display_errors', 0);
ini_set('error_reporting', 0);
ini_set('max_execution_time', 600);
/*
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
ob_implicit_flush(1);
*/

require_once(dirname(dirname(__FILE__)) . '/config.php');

/** WordPress Image Administration API */
require_once(ABSPATH . 'wp-admin/includes/image.php');

// HTTP headers for no cache etc
nocache_headers();

check_admin_referer('GmediaImport');
if(!current_user_can('gmedia_import')){
	wp_die(__('You do not have permission to upload files.'));
}

// 10 minutes execution time
@set_time_limit(10 * 60);

// fake upload time
usleep(10);

global $gmCore, $gmGallery;

$import = $gmCore->_post('import');
$terms = $gmCore->_post('terms', array());

/**
 * @param     $files
 * @param     $terms
 * @param     $move
 * @param int $exists
 */
function gmedia_import_files($files, $terms, $move, $exists = 0){
	global $gmCore, $gmGallery;

	if(ob_get_level() == 0){
		ob_start();
	}
	$eol = '</pre>' . PHP_EOL;
	$c = count($files);
	$i = 0;
	foreach($files as $file){

		if(is_array($file)){
			if(isset($file['file'])){
				extract($file);
			} else{
				_e('Something went wrong...', 'gmLang');
				die();
			}
		}

		wp_ob_end_flush_all();
		flush();

		$i++;
		$prefix = "\n<pre>$i/$c - ";
		$prefix_ko = "\n<pre class='ko'>$i/$c - ";

		if(!is_file($file)){
			echo $prefix_ko . sprintf(__('File not exists: %s', 'gmLang'), $file) . $eol;
			continue;
		}


		$fileinfo = $gmCore->fileinfo($file, $exists);

		// try to make grand-media dir if not exists
		if(!wp_mkdir_p($fileinfo['dirpath'])){
			echo $prefix_ko . sprintf(__('Unable to create directory `%s`. Is its parent directory writable by the server?', 'gmLang'), $fileinfo['dirpath']) . $eol;
			continue;
		}
		// Check if grand-media dir is writable
		if(!is_writable($fileinfo['dirpath'])){
			@chmod($fileinfo['dirpath'], 0755);
			if(!is_writable($fileinfo['dirpath'])){
				echo $prefix_ko . sprintf(__('Directory `%s` or its subfolders are not writable by the server.', 'gmLang'), dirname($fileinfo['dirpath'])) . $eol;
				continue;
			}
		}

		if(!copy($file, $fileinfo['filepath'])){
			echo $prefix_ko . sprintf(__("Can't copy file from `%s` to `%s`", 'gmLang'), $file, $fileinfo['filepath']) . $eol;
			continue;
		}

		$gmCore->file_chmod($fileinfo['filepath']);

		$size = false;
		$is_webimage = false;
		if('image' == $fileinfo['dirname']){
			$size = @getimagesize($fileinfo['filepath']);
			if($size && file_is_displayable_image($fileinfo['filepath'])){
				if(!wp_mkdir_p($fileinfo['dirpath_thumb'])){
					echo $prefix_ko . sprintf(__('Unable to create directory `%s`. Is its parent directory writable by the server?', 'gmLang'), $fileinfo['dirpath_thumb']) . $eol;
					continue;
				}
				if(!is_writable($fileinfo['dirpath_thumb'])){
					@chmod($fileinfo['dirpath_thumb'], 0755);
					if(!is_writable($fileinfo['dirpath_thumb'])){
						@unlink($fileinfo['filepath']);
						echo $prefix_ko . sprintf(__('Directory `%s` is not writable by the server.', 'gmLang'), $fileinfo['dirpath_thumb']) . $eol;
						continue;
					}
				}
				if(!wp_mkdir_p($fileinfo['dirpath_original'])){
					echo $prefix_ko . sprintf(__('Unable to create directory `%s`. Is its parent directory writable by the server?', 'gmLang'), $fileinfo['dirpath_original']) . $eol;
					continue;
				}
				if(!is_writable($fileinfo['dirpath_original'])){
					@chmod($fileinfo['dirpath_original'], 0755);
					if(!is_writable($fileinfo['dirpath_original'])){
						@unlink($fileinfo['filepath']);
						echo $prefix_ko . sprintf(__('Directory `%s` is not writable by the server.', 'gmLang'), $fileinfo['dirpath_original']) . $eol;
						continue;
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
						echo $prefix_ko . $fileinfo['basename'] . " (wp_get_image_editor): " . $editor->get_error_message() . $eol;
						continue;
					}

					if($webimg['resize']){
						$editor->set_quality($webimg['quality']);

						$resized = $editor->resize($webimg['width'], $webimg['height'], $webimg['crop']);
						if(is_wp_error($resized)){
							@unlink($fileinfo['filepath_original']);
							echo $prefix_ko . $fileinfo['basename'] . " (" . $resized->get_error_code() . " | editor->resize->webimage({$webimg['width']}, {$webimg['height']}, {$webimg['crop']})): " . $resized->get_error_message() . $eol;
							continue;
						}

						$saved = $editor->save($fileinfo['filepath']);
						if(is_wp_error($saved)){
							@unlink($fileinfo['filepath_original']);
							echo $prefix_ko . $fileinfo['basename'] . " (" . $saved->get_error_code() . " | editor->save->webimage): " . $saved->get_error_message() . $eol;
							continue;
						}
					}

					// Thumbnail
					$editor->set_quality($thumbimg['quality']);

					$resized = $editor->resize($thumbimg['width'], $thumbimg['height'], $thumbimg['crop']);
					if(is_wp_error($resized)){
						@unlink($fileinfo['filepath']);
						@unlink($fileinfo['filepath_original']);
						echo $prefix_ko . $fileinfo['basename'] . " (" . $resized->get_error_code() . " | editor->resize->thumb({$thumbimg['width']}, {$thumbimg['height']}, {$thumbimg['crop']})): " . $resized->get_error_message() . $eol;
						continue;
					}

					$saved = $editor->save($fileinfo['filepath_thumb']);
					if(is_wp_error($saved)){
						@unlink($fileinfo['filepath']);
						@unlink($fileinfo['filepath_original']);
						echo $prefix_ko . $fileinfo['basename'] . " (" . $saved->get_error_code() . " | editor->save->thumb): " . $saved->get_error_message() . $eol;
						continue;
					}
				} else{
					copy($fileinfo['filepath'], $fileinfo['filepath_thumb']);
				}
				$is_webimage = true;
			} else{
				@unlink($fileinfo['filepath']);
				echo $prefix_ko . $fileinfo['basename'] . ": " . __("Could not read image size. Invalid image was deleted.", 'gmLang') . $eol;
				continue;
			}
		}

		// Write media data to DB
		// TODO Option to set title empty string or from metadata or from filename or both
		// use image exif/iptc data for title and caption defaults if possible
		if($size && !isset($title) && !isset($description)){
			$image_meta = @wp_read_image_metadata($fileinfo['filepath_original']);
			if(trim($image_meta['title']) && !is_numeric(sanitize_title($image_meta['title']))){
				$title = $image_meta['title'];
			}
			if(trim($image_meta['caption'])){
				$description = $image_meta['caption'];
			}
		}
		if(!isset($title) || empty($title)){
			$title = $fileinfo['title'];
		}
		if(!isset($description)){
			$description = '';
		}
		if(!isset($link)){
			$link = '';
		}

		$_terms = $terms;
		if(!$is_webimage && isset($_terms['gmedia_category'])){
			unset($_terms['gmedia_category']);
		}

		// Construct the media_data array
		$media_data = array(
			'mime_type' => $fileinfo['mime_type'],
			'gmuid' => $fileinfo['basename'],
			'title' => $title,
			'link' => $link,
			'description' => $description,
			'terms' => $_terms
		);

		unset($title, $description);

		global $gmDB;
		// Save the data
		$id = $gmDB->insert_gmedia($media_data);
		$gmDB->update_metadata($meta_type = 'gmedia', $id, $meta_key = '_metadata', $gmDB->generate_gmedia_metadata($id, $fileinfo));

		echo $prefix . $fileinfo['basename'] . ': <span class="ok">' . sprintf(__('success (ID #%s)', 'gmLang'), $id) . '</span>' . $eol;

		if($move){
			@unlink($file);
		}

	}

	echo '<p><b>' . __('Category') . ':</b> ' . ((isset($terms['gmedia_category']) && !empty($terms['gmedia_category']))? esc_html($gmGallery->options['taxonomies']['gmedia_category'][$terms['gmedia_category']]) : '-') . PHP_EOL;
	echo '<br /><b>' . __('Album') . ':</b> ' . ((isset($terms['gmedia_album']) && !empty($terms['gmedia_album']))? esc_html($terms['gmedia_album']) : '-') . PHP_EOL;
	echo '<br /><b>' . __('Tags') . ':</b> ' . ((isset($terms['gmedia_tag']) && !empty($terms['gmedia_tag']))? esc_html(str_replace(',', ', ', $terms['gmedia_tag'])) : '-') . '</p>' . PHP_EOL;

	wp_ob_end_flush_all();
	flush();
}

if(ob_get_level() == 0){
	ob_start();
}
echo str_pad(' ', 4096) . PHP_EOL;
wp_ob_end_flush_all();
flush();
?>
	<html>
	<style type="text/css">
		* { margin:0; padding:0; }
		pre { display:block; }
		p { padding:10px 0; font-size:14px; }
		.ok { color:darkgreen; }
		.ko { color:darkred; }
	</style>
	<body>
	<?php
	if('import-folder' == $import){

		$path = $gmCore->_post('path');
		echo '<h4 style="margin: 0 0 10px">' . __('Import Server Folder') . " `$path`:</h4>" . PHP_EOL;

		if($path){
			$path = trim(urldecode($path), '/');
			if(!empty($path)){
				$fullpath = ABSPATH . trailingslashit($path);
				$files = glob($fullpath . '?*.?*', GLOB_NOSORT);
				if(!empty($files)){
					if((GMEDIA_UPLOAD_FOLDER == basename(dirname(dirname($path)))) || (GMEDIA_UPLOAD_FOLDER == basename(dirname($path)))){
						global $wpdb;
						$gmedias = $wpdb->get_col("SELECT gmuid FROM {$wpdb->prefix}gmedia");
						foreach($files as $i => $filepath){
							$gmuid = basename($filepath);
							if(in_array($gmuid, $gmedias)){
								unset($files[$i]);
							}
						}
						$move = false;
						$exists = false;
					} else{
						$move = $gmCore->_post('delete_source');
						$exists = 0;
					}
					gmedia_import_files($files, $terms, $move, $exists);
				} else{
					echo sprintf(__('Folder `%s` is empty', 'gmLang'), $path) . PHP_EOL;
				}
			} else{
				echo __('No folder chosen', 'gmLang') . PHP_EOL;
			}
		}
	} elseif('import-flagallery' == $import){

		echo '<h4 style="margin: 0 0 10px">' . __('Import from Flagallery plugin') . ":</h4>" . PHP_EOL;

		$gallery = $gmCore->_post('gallery');
		if(!empty($gallery)){
			global $wpdb, $gmDB;

			$album = (!isset($terms['gmedia_album']) || empty($terms['gmedia_album']))? false : true;
			foreach($gallery as $gid){
				$flag_gallery = $wpdb->get_row($wpdb->prepare("SELECT gid, path, title, galdesc FROM `{$wpdb->prefix}flag_gallery` WHERE gid = %d", $gid), ARRAY_A);
				if(empty($flag_gallery)){
					continue;
				}

				if(!$album){
					$terms['gmedia_album'] = $flag_gallery['title'];
					if(!$gmDB->term_exists($flag_gallery['title'], 'gmedia_album')){
						$term_id = $gmDB->insert_term($flag_gallery['title'], 'gmedia_album', array('description' => htmlspecialchars_decode(stripslashes($flag_gallery['galdesc']))));
					}
				}

				$path = ABSPATH . trailingslashit($flag_gallery['path']);

				echo '<h5 style="margin: 10px 0 5px">' . sprintf(__('Import `%s` gallery', 'gmLang'), $flag_gallery['title']) . ":</h5>" . PHP_EOL;

				$flag_pictures = $wpdb->get_results($wpdb->prepare("SELECT CONCAT('%s', filename) AS file, description, alttext AS title, link FROM `{$wpdb->prefix}flag_pictures` WHERE galleryid = %d", $path, $flag_gallery['gid']), ARRAY_A);
				if(empty($flag_pictures)){
					echo '<pre>' . __('gallery contains 0 images', 'gmLang') . '</pre>';
					continue;
				}
				//echo '<pre>'.print_r($flag_pictures, true).'</pre>';
				gmedia_import_files($flag_pictures, $terms, false);
			}
		} else{
			echo __('No gallery chosen', 'gmLang') . PHP_EOL;
		}
	} elseif('import-nextgen' == $import){

		echo '<h4 style="margin: 0 0 10px">' . __('Import from NextGen plugin') . ":</h4>" . PHP_EOL;

		$gallery = $gmCore->_post('gallery');
		if(!empty($gallery)){
			global $wpdb, $gmDB;

			$album = (!isset($terms['gmedia_album']) || empty($terms['gmedia_album']))? false : true;
			foreach($gallery as $gid){
				$ngg_gallery = $wpdb->get_row($wpdb->prepare("SELECT gid, path, title, galdesc FROM `{$wpdb->prefix}ngg_gallery` WHERE gid = %d", $gid), ARRAY_A);
				if(empty($ngg_gallery)){
					continue;
				}

				if(!$album){
					$terms['gmedia_album'] = $ngg_gallery['title'];
					if(!$gmDB->term_exists($ngg_gallery['title'], 'gmedia_album')){
						$term_id = $gmDB->insert_term($ngg_gallery['title'], 'gmedia_album', array('description' => htmlspecialchars_decode(stripslashes($ngg_gallery['galdesc']))));
					}
				}

				$path = ABSPATH . trailingslashit($ngg_gallery['path']);

				echo '<h5 style="margin: 10px 0 5px">' . sprintf(__('Import `%s` gallery', 'gmLang'), $ngg_gallery['title']) . ":</h5>" . PHP_EOL;

				$ngg_pictures = $wpdb->get_results($wpdb->prepare("SELECT CONCAT('%s', filename) AS file, description, alttext AS title FROM `{$wpdb->prefix}ngg_pictures` WHERE galleryid = %d", $path, $ngg_gallery['gid']), ARRAY_A);
				if(empty($ngg_pictures)){
					echo '<pre>' . __('gallery contains 0 images', 'gmLang') . '</pre>';
					continue;
				}
				gmedia_import_files($ngg_pictures, $terms, false);
			}
		} else{
			echo __('No gallery chosen', 'gmLang') . PHP_EOL;
		}
	} elseif('import-wpmedia' == $import){
		global $user_ID, $gmDB;

		echo '<h4 style="margin: 0 0 10px">' . __('Import from WP Media Library') . ":</h4>" . PHP_EOL;

		$wpMediaLib = $gmDB->get_wp_media_lib(array('filter' => 'selected', 'selected' => $gmCore->_post('selected')));

		if(!empty($wpMediaLib)){

			$wp_media = array();
			foreach($wpMediaLib as $item){
				$wp_media[] = array(
					'file' => get_attached_file($item->ID),
					'title' => $item->post_title,
					'description' => $item->post_content
				);
			}
			//echo '<pre>' . print_r($wp_media, true) . '</pre>';
			gmedia_import_files($wp_media, $terms, false);

		} else{
			echo __('No items chosen', 'gmLang') . PHP_EOL;
		}
	}
	?>
	</body>
	</html>
<?php
wp_ob_end_flush_all();
