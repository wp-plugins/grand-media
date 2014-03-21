<?php

/**
 * Main PHP class for the WordPress plugin GRAND Media

 */
class GmediaCore {

	var $upload;
	var $gmedia_url;

	function __construct() {
		$this->upload = $this->gm_upload_dir();
		$this->gmedia_url = plugins_url(GMEDIA_FOLDER);
	}

	/**
	 * Check GET data
	 *
	 * @param string $var
	 * @param bool   $def
	 *
	 * @return mixed
	 */
	function _get($var = '', $def = false) {
		return isset($_GET[$var])? $_GET[$var] : $def;
	}

	/**
	 * Check POST data
	 *
	 * @param string $var
	 * @param bool   $def
	 *
	 * @return mixed
	 */

	function _post($var = '', $def = false) {
		return isset($_POST[$var])? $_POST[$var] : $def;
	}

	/**
	 * Check REQUEST data
	 *
	 * @param string $var
	 * @param bool   $def
	 *
	 * @return mixed
	 */
	function _req($var = '', $def = false) {
		return isset($_REQUEST[$var])? $_REQUEST[$var] : $def;
	}

	/**
	 * qTip attributes
	 *
	 * @param string $style 'tooltip', 'popover'
	 * @param array  $params
	 * @param bool   $print
	 *
	 * @return string
	 */
	function qTip($style, $params, $print = true) {
		$tooltip = '';
		$show_tip = 0; // TODO show tooltips checkbox in settings
		if($show_tip){
			$tooltip = " data-toggle='$style'";
			if(is_array($params) && ! empty($params)){
				foreach($params as $key => $val){
					$tooltip .= " data-$key='$val'";
				}
			}
			if($print){
				echo $tooltip;
			} else{
				return $tooltip;
			}
		}

		return false;
	}

	function get_admin_url($add_args = array(), $remove_args = array()) {
		$remove_args = array_unique(array_merge(array('doing_wp_cron', '_wpnonce'), $remove_args, array_keys($add_args)));
		//$url = remove_query_arg( $remove_args, admin_url( 'admin.php?' . http_build_query( $_GET ) ) );
		$url = remove_query_arg($remove_args);
		if(! empty($add_args)){
			$url = add_query_arg($add_args);
		}

		return $url;
	}

	/**
	 * Show a system messages
	 *
	 * @param string      $message
	 * @param bool|string $type
	 * @param bool        $close
	 *
	 * @return string
	 */
	function message($message = '', $type = false, $close = true) {
		$content = '';
		$close = $close? '<i class="gm-close">'.__('Hide', 'gmLang').'</i>' : '';
		$type = $type? $type : 'info';
		if($message){
			$content .= '<div class="gm-message gm-'.$type.'"><span>'.stripslashes($message).'</span>'.$close.'</div>';
		}
		if($count = $this->_get('deleted')){
			$message = sprintf(__('%d media attachment(s) permanently deleted.'), $count);
			$type = 'info';
			$content .= '<div class="gm-message gm-'.$type.'"><span>'.$message.'</span>'.$close.'</div>';
		}

		return $content;
	}


	function is_crawler($userAgent) {
		$crawlers = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|FeedBurner|'.'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|'.'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby|yandex|facebook';
		$isCrawler = (preg_match("/$crawlers/i", $userAgent) > 0);

		return $isCrawler;
	}

	function is_browser($userAgent) {
		$browsers = 'opera|aol|msie|firefox|chrome|konqueror|safari|netscape|navigator|mosaic|lynx|amaya|omniweb|avant|camino|flock|seamonkey|mozilla|gecko';
		$isBrowser = (preg_match("/$browsers/i", $userAgent) > 0);

		return $isBrowser;
	}

	/**
	 * Return relative path to an uploaded file.
	 * The path is relative to the gMedia upload dir.
	 * @see  _wp_relative_upload_path()
	 * @uses apply_filters() Calls '_gm_relative_upload_path' on file path.
	 *
	 * @param string $path Full path to the file
	 *
	 * @return string relative path on success, unchanged path on failure.
	 */
	function _gm_relative_upload_path($path) {
		$new_path = $path;

		if(($uploads = $this->gm_upload_dir()) && false === $uploads['error']){
			if(0 === strpos($new_path, $uploads['path'])){
				$new_path = str_replace($uploads['path'], '', $new_path);
				$new_path = ltrim($new_path, '/');
			}
		}

		return apply_filters('_gm_relative_upload_path', $new_path, $path);
	}

	/**
	 * Get an array containing the gMedia upload directory's path and url.
	 * If the path couldn't be created, then an error will be returned with the key
	 * 'error' containing the error message. The error suggests that the parent
	 * directory is not writable by the server.
	 * On success, the returned array will have many indices:
	 * 'path' - base directory and sub directory or full path to upload directory.
	 * 'url' - base url and sub directory or absolute URL to upload directory.
	 * 'error' - set to false.
	 * @see  wp_upload_dir()
	 * @uses apply_filters() Calls 'gm_upload_dir' on returned array.
	 * @return array See above for description.
	 */
	function gm_upload_dir() {
		$slash = '/';
		// If multisite (and if not the main site)
		if(is_multisite() && ! is_main_site()){
			$slash = '/blogs.dir/'.get_current_blog_id().'/';
		}

		$dir = WP_CONTENT_DIR.$slash.GMEDIA_FOLDER;
		$url = WP_CONTENT_URL.$slash.GMEDIA_FOLDER;

		$uploads = apply_filters('gm_upload_dir', array('path' => $dir, 'url' => $url, 'error' => false));

		// Make sure we have an uploads dir
		if(! wp_mkdir_p($uploads['path'])){
			$message = sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?'), $uploads['path']);
			$uploads['error'] = $message;
		}

		return $uploads;
	}

	function delete_folder($path) {
		$path = rtrim($path, '/');

		return is_file($path)? @unlink($path) : array_map(array($this, 'delete_folder'), glob($path.'/*')) == @rmdir($path);
	}

	function maybe_array_0($arr) {
		if(is_array($arr)){
			$arr = $arr[0];
		}

		return $arr;
	}

	function gm_get_module_settings($module_folder) {
		$module_settings = array();
		$module_dir = $this->get_module_path($module_folder);
		if(is_dir($module_dir['path'])){
			$module_ot = array();
			include($module_dir['path'].'/settings.php');

			$module_ot = apply_filters('gm_get_module_settings', $module_ot);

			foreach($module_ot['settings'] as $key => $section){

				//if($key == 'general_default')
				//continue;

				/* loop through meta box fields */
				foreach($section['fields'] as $field){
					if(in_array($field['type'], array('textblock', 'textblock_titled'))){
						continue;
					}
					if(in_array($field['type'], array('checkbox')) && empty($field['std'])){
						$module_settings[$field['id']] = array();
					} else{
						$module_settings[$field['id']] = $field['std'];
					}
				}
			}
		}

		return $module_settings;
	}

	/**
	 * Get an HTML img element representing an image attachment
	 * @see  add_image_size()
	 * @see  wp_get_attachment_image()
	 * @uses apply_filters() Calls 'gm_get_attachment_image_attributes' hook on attributes array
	 *
	 * @param int|object $item    Image object.
	 * @param string     $size    Optional, default is empty string, could be 'thumb', 'original'
	 * @param bool       $preview Optional, try to get preview url
	 *
	 * @return string img url
	 */
	function gm_get_media_image($item, $size = '', $preview = true) {
		global $gmDB, $gmGallery;

		if(! is_object($item)){
			$item = $gmDB->get_gmedia($item);
		}
		switch($size){
			case 'thumb':
				$size_folder = '/thumb/';
				break;
			case 'original':
				$size_folder = '/original/';
				break;
			default:
				$size_folder = '/';
				break;
		}
		$type = explode('/', $item->mime_type);

		if('image' == $type[0]){
			$type_folder = $this->upload['url'].'/'.$gmGallery->options['folder'][$type[0]];
			$image = $type_folder.$size_folder.$item->gmuid;
		} else{
			$ext = ltrim(strrchr($item->gmuid, '.'), '.');
			if(! $type = wp_ext2type($ext)){
				$type = 'application';
			}
			$image = $this->gmedia_url.'/admin/images/'.$type.'.png';

			if($preview){
				$preview = $gmDB->get_metadata('gmedia', $item->ID, 'preview', true);
				if(! empty($preview)){
					$image = $this->gm_get_media_image($preview, $size, false);
				}
			}
		}

		return $image;
	}

	/**
	 * Get path and url to module folder
	 *
	 * @param string $module_name
	 *
	 * @return array|bool Return array( 'path', 'url' ) OR false if no module
	 */
	function get_module_path($module_name) {
		$gmOptions = get_option('gmediaOptions');
		$upload = $this->gm_upload_dir();
		$module_dir = array('path' => array($upload['path'].$gmOptions['folder']['module'].'/'.$module_name,
																				GMEDIA_ABSPATH.'module/'.$module_name),
												'url' => array($upload['url'].$gmOptions['folder']['module'].'/'.$module_name,
																			 plugins_url(GMEDIA_FOLDER).'/module/'.$module_name));
		$check_paths = array_filter($module_dir['path'], 'is_dir');
		if(empty($check_paths)){
			return false;
		}

		$module_dir_key = $this->maybe_array_0(array_keys($check_paths));

		return array('path' => $module_dir['path'][$module_dir_key], 'url' => $module_dir['url'][$module_dir_key]);
	}

	/**
	 * Generate resized image on the fly if it not exists
	 *
	 * @param array $args
	 * @param bool  $crunch
	 *
	 * @internal int    $id
	 * @internal string $file
	 * @internal int    $max_w
	 * @internal int    $max_h
	 * @internal int    $crop Default: 0
	 * @internal int    $quality Default: 90
	 * @internal bool   $suffix Default: false
	 * @return array Return file name
	 */
	function linked_img($args, $crunch = true) {
		global $gmDB;
		/**
		 * @var $id      int
		 * @var $file    string
		 * @var $width   int
		 * @var $height  int
		 * @var $max_w   int
		 * @var $max_h   int
		 * @var $crop    int
		 * @var $quality int
		 * @var $suffix  string
		 */
		$args = wp_parse_args($args, array('id' => 0, 'file' => '', 'suffix' => '', 'max_w' => 0, 'max_h' => 0, 'crop' => 0,
																			 'quality' => 90, 'width' => 0, 'height' => 0,));
		extract($args);
		if(empty($file)){
			return array('file' => 'NaN', 'error' => '$file empty');
		}
		$gmOptions = get_option('gmediaOptions');
		$upload = $this->gm_upload_dir();

		if(empty($width) || empty($height)){
			$suffix = 'thumb';
		}

		$ext = strrchr($file, '.');
		$filename = substr($file, 0, strrpos($file, $ext));
		$file_path = $upload['path'].$gmOptions['folder']['image'].'/'.$file;
		$file_url = $upload['url'].$gmOptions['folder']['image'].'/'.$file;

		if('thumb' != $suffix){
			if((! $crop || ! $max_w || ! $max_h) && ($crop | $max_w | $max_h)){
				list($max_w, $max_h) = wp_constrain_dimensions($width, $height, $max_w, $max_h);
				$crop = 1;
			}
			if($crop && ($max_w.'x'.$max_h == $gmOptions['thumbnail_size'])){
				$suffix = 'thumb';
			}
		}

		if(empty($suffix)){
			$suffix = $max_w.'x'.$max_h;
		}

		if(($max_w.'x'.$max_h) === ($width.'x'.$height)){
			$link_path = $file_path;
			$link_url = $file_url;
		} else{
			$link_path = $upload['path'].$gmOptions['folder']['link'].'/'.$filename.'-'.$suffix.$ext;
			$link_url = $upload['url'].$gmOptions['folder']['link'].'/'.$filename.'-'.$suffix.$ext;
		}

		if(! file_exists($link_path)){
			$thumb = $filename.'-thumb'.$ext;
			if(! $crunch){
				$args['max_h'] = $max_h;
				$args['max_w'] = $max_w;
				$args['crop'] = $crop;

				return array('file' => $filename.'-'.$suffix.$ext, 'crunch' => $args);
			}
			$dest_path = $upload['path'].$gmOptions['folder']['link'];
			if(function_exists('wp_get_image_editor')){
				$editor = wp_get_image_editor($file_path);
				if(is_wp_error($editor)){
					return array('file' => $thumb, 'error' => 'image_editor: '.$editor->get_error_message());
				}
				$editor->set_quality($quality);

				$resized = $editor->resize($max_w, $max_h, $crop);
				if(is_wp_error($resized)){
					return array('file' => $thumb, 'error' => 'resize: '.$resized->get_error_message());
				}

				$dest_file = $editor->generate_filename($suffix, $dest_path);
				$saved = $editor->save($dest_file);

				if(is_wp_error($saved)){
					return array('file' => $thumb, 'error' => 'save: '.$saved->get_error_message());
				}
			} else{
				$new_file = image_resize($file_path, $max_w, $max_h, $crop, $suffix, $dest_path, $quality);
				if(is_wp_error($new_file)){
					return array('file' => $thumb, 'error' => 'image_resize: '.$new_file->get_error_message());
				}
			}

			if($id && $args['suffix']){
				$_metadata = $gmDB->get_metadata('gmedia', $id, '_metadata', true);
				$_metadata['sizes'][$suffix] = array('width' => $args['max_w'], 'height' => $args['max_h'],
																						 'crop' => $args['crop']);
				$gmDB->update_metadata('gmedia', $id, '_metadata', $_metadata);
			}
		}

		return array('file' => $filename.'-'.$suffix.$ext, 'url' => $link_url, 'original' => $file_url);
	}

	/** Automatic choose upload directory based on media type
	 *
	 * @param string $file
	 * @param int $exists
	 *
	 * @return array
	 */
	function fileinfo($file, $exists = 0) {
		/** @var $wpdb wpdb */
		global $wpdb, $gmGallery;

		$file = basename($file);
		$filetype = wp_check_filetype($file, $mimes = null);
		$title = pathinfo($file, PATHINFO_FILENAME);
		$pathinfo = pathinfo(preg_replace('/[^a-z0-9_\.-]+/i', '_', $file));
		$suffix = absint($exists)? "_$exists" : '';

		$fileinfo['extension'] = (empty($filetype['ext']))? $pathinfo['extension'] : $filetype['ext'];
		$fileinfo['filename'] = $pathinfo['filename'].$suffix;
		$fileinfo['basename'] = $suffix? $pathinfo['filename'].$suffix.'.'.$fileinfo['extension'] : $pathinfo['basename'];
		$fileinfo['title'] = ucwords(str_replace('_', ' ', mysql_real_escape_string($title)));
		$fileinfo['mime_type'] = (empty($filetype['type']))? 'application/'.$fileinfo['extension'] : $filetype['type'];
		list($dirname) = explode('/', $fileinfo['mime_type']);
		$fileinfo['dirname'] = $dirname;
		$fileinfo['dirpath'] = $this->upload['path'].'/'.$gmGallery->options['folder'][$dirname];
		$fileinfo['dirurl'] = $this->upload['url'].'/'.$gmGallery->options['folder'][$dirname];
		$fileinfo['filepath'] = $fileinfo['dirpath'].'/'.$fileinfo['basename'];
		$fileinfo['fileurl'] = $fileinfo['dirurl'].'/'.$fileinfo['basename'];
		if('image' == $dirname){
			$fileinfo['dirpath_original'] = $this->upload['path'].'/'.$gmGallery->options['folder']['image_original'];
			$fileinfo['dirurl_original'] = $this->upload['url'].'/'.$gmGallery->options['folder']['image_original'];
			$fileinfo['filepath_original'] = $fileinfo['dirpath_original'].'/'.$fileinfo['basename'];
			$fileinfo['fileurl_original'] = $fileinfo['dirurl_original'].'/'.$fileinfo['basename'];
			$fileinfo['dirpath_thumb'] = $this->upload['path'].'/'.$gmGallery->options['folder']['image_thumb'];
			$fileinfo['dirurl_thumb'] = $this->upload['url'].'/'.$gmGallery->options['folder']['image_thumb'];
			$fileinfo['filepath_thumb'] = $fileinfo['dirpath_thumb'].'/'.$fileinfo['basename'];
			$fileinfo['fileurl_thumb'] = $fileinfo['dirurl_thumb'].'/'.$fileinfo['basename'];
		}

		if(file_exists($fileinfo['filepath'])){
			$exists = absint($exists) + 1;
			$fileinfo = $this->fileinfo($file, $exists);
		}

		return $fileinfo;
	}

	/** Set correct file permissions (chmod)
	 *
	 * @param string $new_file
	 */
	function file_chmod($new_file) {
		$stat = stat(dirname($new_file));
		$perms = $stat['mode'] & 0000666;
		@ chmod($new_file, $perms);
	}

	/** Import folder
	 *
	 * @param string $source_file
	 * @param array  $file_data
	 * @param bool   $delete_source
	 *
	 * @return mixed json data
	 */
	function import($source_file, $file_data = array(), $delete_source = false) {
		global $gmDB;
		$gmOptions = get_option('gmediaOptions');
		$uploads = $this->gm_upload_dir();
		$source_file = urldecode($source_file);
		$target_file = $this->fileinfo($source_file);
		$target_dir = $uploads['path'].$gmOptions['folder'][$target_file['dirname']];
		$target_dir_url = $uploads['url'].$gmOptions['folder'][$target_file['dirname']];

		// try to make grand-media dir if not exists
		if(! wp_mkdir_p($target_dir)){
			$return = array("error" => array("code" => 100,
																			 "message" => sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?', 'gmLang'), $target_dir)),
											"id" => $target_file['filename']);

			return $return;
		}
		// Check if grand-media dir is writable
		if(! is_writable($target_dir)){
			@chmod($target_dir, 0755);
			if(! is_writable($target_dir)){
				$return = array("error" => array("code" => 100,
																				 "message" => sprintf(__('Directory %s or its subfolders are not writable by the server.', 'gmLang'), $target_dir)),
												"id" => $target_file['filename']);

				return $return;
			}
		}

		$url = $target_dir_url.'/'.$target_file['filename'];
		$file = $target_dir.'/'.$target_file['filename'];

		if(copy($source_file, $file)){

			$this->file_chmod($file);

			$size = false;
			if(basename($target_dir) == 'image'){
				$size = @getimagesize($file);
				if($size){
					$quality = 90;
					list($max_w, $max_h) = explode('x', $gmOptions['thumbnail_size']);
					$crop = 1;
					$suffix = 'thumb';
					$dest_path = $uploads['path'].$gmOptions['folder']['link'];
					if(! is_writable($dest_path)){
						@chmod($dest_path, 0755);
						if(! is_writable($dest_path)){
							@unlink($file);
							$return = array("error" => array("code" => 100,
																							 "message" => sprintf(__('Directory %s is not writable by the server.', 'gmLang'), $uploads['path'].$gmOptions['folder']['link'])),
															"id" => $target_file['filename']);

							return $return;
						}
					}
					if(function_exists('wp_get_image_editor')){
						$editor = wp_get_image_editor($file);
						if(is_wp_error($editor)){
							@unlink($file);
							$return = array("error" => array("code" => $editor->get_error_code(),
																							 "message" => $editor->get_error_message()),
															"id" => $target_file['filename']);
							die($return);
						}
						$editor->set_quality($quality);

						$resized = $editor->resize($max_w, $max_h, $crop);
						if(is_wp_error($resized)){
							@unlink($file);
							$return = array("error" => array("code" => $resized->get_error_code(),
																							 "message" => $resized->get_error_message()),
															"id" => $target_file['filename']);

							return $return;
						}

						$dest_file = $editor->generate_filename($suffix, $dest_path);
						$saved = $editor->save($dest_file);

						if(is_wp_error($saved)){
							@unlink($file);
							$return = array("error" => array("code" => $saved->get_error_code(),
																							 "message" => $saved->get_error_message()),
															"id" => $target_file['filename']);

							return $return;
						}
					} else{
						$new_file = image_resize($file, $max_w, $max_h, $crop, $suffix, $dest_path, $quality);
						if(is_wp_error($new_file)){
							@unlink($file);
							$return = array("error" => array("code" => $new_file->get_error_code(),
																							 "message" => $new_file->get_error_message()),
															"id" => $target_file['filename']);

							return $return;
						}
					}
				} else{
					@unlink($file);
					$return = array("error" => array("code" => 104,
																					 "message" => __("Could not read image size. Invalid image was deleted.", 'gmLang')),
													"id" => $target_file['filename']);

					return $return;
				}
			}

			// Write gmedia data to DB
			$content = '';
			// TODO Option to set title empty string or from metadata or from filename or both
			$title = $target_file['title'];
			// use image exif/iptc data for title and caption defaults if possible
			if($size){
				$image_meta = @wp_read_image_metadata($file);
				if(trim($image_meta['caption'])){
					$content = $image_meta['caption'];
				}
				if(trim($image_meta['title']) && ! is_numeric(sanitize_title($image_meta['title']))){
					$title = $image_meta['title'];
				}
			}

			// Construct the media array
			$media_data = array('mime_type' => $target_file['mime_type'], 'gmuid' => $target_file['filename'],
													'title' => $title, 'description' => $content);
			if(! isset($file_data['terms']['gmedia_album'])){
				$album = ucwords(str_replace(array('_', '-'), ' ', basename(dirname($source_file))));
				$file_data['terms']['gmedia_album'] = $album;
			}
			$media_data = wp_parse_args($file_data, $media_data);

			// Save the data
			$id = $gmDB->insert_gmedia($media_data);
			$gmDB->update_metadata($meta_type = 'gmedia', $id, $meta_key = '_metadata', $gmDB->generate_gmedia_metadata($id, $file));

			if($delete_source){
				@unlink($source_file);
			}

			$return = array("success" => array("code" => 200,
																				 "message" => sprintf(__('File imported successful. Assigned ID: %s', 'gmLang'), $id)),
											"id" => $target_file['filename']);

			return $return;
		} else{
			$return = array("error" => array("code" => 105, "message" => __('Could not copy file.', 'gmLang')),
											"id" => $target_file['filename']);

			return $return;
		}
	}

	/**
	 * The most complete and efficient way to sanitize a string before using it with your database
	 *
	 * @param $input string
	 *
	 * @return mixed
	 */
	function clean_input($input) {
		$search = array(
			/*'@<[\/\!]*?[^<>]*?>@si',*/ /* Strip out HTML tags */
			'@<script[^>]*?>.*?</script>@si', /* Strip out javascript */
			'@<style[^>]*?>.*?</style>@siU', /* Strip style tags properly */
			'@<![\s\S]*?--[ \t\n\r]*>@', /* Strip multi-line comments */
			'/\s{2,}/'
		);

		$output = preg_replace($search, '', $input);

		return $output;
	}

	/**
	 * Check input for existing only of digits (numbers)
	 *
	 * @param $digit
	 *
	 * @return bool
	 */
	function is_digit($digit){
		if(is_int($digit)){
			return true;
		} elseif(is_string($digit) && !empty($digit)){
			return ctype_digit($digit);
		} else{
			// booleans, floats and others
			return false;
		}
	}

	/**
	 * Sanitize a string|array before using it with your database
	 *
	 * @param $input string|array
	 *
	 * @return mixed
	 */
	function sanitize($input) {
		$output = $input;
		if(is_array($input)){
			foreach($input as $var => $val){
				$output[$var] = $this->sanitize($val);
			}
		} else{
			if(get_magic_quotes_gpc()){
				$input = stripslashes($input);
			}
			$input = $this->clean_input($input);
			$output = mysql_real_escape_string($input);
		}

		return $output;
	}

	// Check if user can select a author
	function get_editable_user_ids($user_id, $exclude_zeros = true) {
		global $wpdb;

		$user = new WP_User($user_id);

		$query = "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$wpdb->prefix}user_level'";
		if($exclude_zeros){
			$query .= " AND meta_value != '0'";
		}

		return $wpdb->get_col($query);
	}

	// Extremely simple function to get human filesize
	function filesize($file, $decimals = 2) {
		$bytes = filesize($file);
		$sz = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
		$factor = (int) floor((strlen($bytes) - 1) / 3);

		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).$sz[$factor];
	}

	/**
	 * @author  Gajus Kuizinas <g.kuizinas@anuary.com>
	 * @version 1.0.0 (2013 03 19)
	 */
	function array_diff_key_recursive(array $arr1, array $arr2) {
		$diff = array_diff_key($arr1, $arr2);
		$intersect = array_intersect_key($arr1, $arr2);

		foreach($intersect as $k => $v){
			if(is_array($arr1[$k]) && is_array($arr2[$k])){
				$d = $this->array_diff_key_recursive($arr1[$k], $arr2[$k]);

				if($d){
					$diff[$k] = $d;
				}
			}
		}

		return $diff;
	}

}

global $gmCore;
$gmCore = new GmediaCore();
