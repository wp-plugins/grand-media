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
	 * @param bool|mixed   $def
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
	 * tooltip()
	 *
	 * @param string $style 'tooltip', 'popover'
	 * @param array  $params
	 * @param bool   $print
	 *
	 * @return string
	 */
	function tooltip($style, $params, $print = true) {
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

	function get_admin_url($add_args = array(), $remove_args = array(), $uri = false) {
		if(true === $uri){
			$uri = admin_url('admin.php');
		}
		$remove_args = array_unique(array_merge(array('doing_wp_cron', '_wpnonce', 'delete'), $remove_args, array_keys($add_args)));
		$new_uri = remove_query_arg($remove_args, $uri);
		if(! empty($add_args)){
			$new_uri = add_query_arg($add_args, $uri);
		}

		return $new_uri;
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
	 * Get an array containing the gmedia upload directory's path and url.
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
		return is_file($path)? @unlink($path) : (is_dir($path)? (array_map(array($this, 'delete_folder'), glob($path.'/*', GLOB_NOSORT)) == @rmdir($path)) : null);
	}

	/**
	 * Get an HTML img element representing an image attachment
	 * @see  add_image_size()
	 * @see  wp_get_attachment_image()
	 * @uses apply_filters() Calls 'gm_get_attachment_image_attributes' hook on attributes array
	 *
	 * @param int|object  $item    Image object.
	 * @param string      $size    Optional, default is empty string, could be 'thumb', 'original'
	 * @param bool        $cover Optional, try to get cover url
	 * @param bool|string $default Optional, return if no cover
	 *
	 * @return string img url
	 */
	function gm_get_media_image($item, $size = '', $cover = true, $default = false) {
		global $gmDB, $gmGallery;

		if(! is_object($item)){
			$item = $gmDB->get_gmedia($item);
		}
		$type = explode('/', $item->mime_type);

		if('image' == $type[0]){
			$type_folder = $this->upload['url'].'/'.$gmGallery->options['folder'][$type[0]];

			switch($size){
				case 'thumb':
					$size_folder = '/thumb/';
					break;
				case 'original':
					$size_folder = '/original/';
					break;
				case 'web':
				default:
					$size_folder = '/';
					break;
			}

			$image = $type_folder.$size_folder.$item->gmuid;
		} else{
			$ext = ltrim(strrchr($item->gmuid, '.'), '.');
			if(! $type = wp_ext2type($ext)){
				$type = 'application';
			}
			$image = $this->gmedia_url.'/admin/images/'.$type.'.png';

			if($cover){
				$cover = $gmDB->get_metadata('gmedia', $item->ID, 'cover', true);
				if(!empty($cover)){
					if($this->is_digit($cover)){
						$image = $this->gm_get_media_image((int) $cover, $size, false);
					} elseif(false !== filter_var($cover, FILTER_VALIDATE_URL)){
						return $cover;
					}
				} elseif(false !== $default){
					return $default;
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
		global $gmGallery;
		if(empty($module_name)){
			return false;
		}
		$module_dirs = array(
			'upload' => array(
				'path' => $this->upload['path'].'/'.$gmGallery->options['folder']['module'].'/'.$module_name,
				'url' => $this->upload['url'].'/'.$gmGallery->options['folder']['module'].'/'.$module_name
			),
			'plugin' => array(
				'path' => GMEDIA_ABSPATH.'module/'.$module_name,
				'url' => plugins_url(GMEDIA_FOLDER).'/module/'.$module_name
			),
		);
		foreach($module_dirs as $dir){
			if(is_dir($dir['path'])){
				return $dir;
			}
		}
		return false;
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
		$file_lower = strtolower($file);
		$filetype = wp_check_filetype($file_lower, $mimes = null);
		$title = pathinfo($file, PATHINFO_FILENAME);
		$pathinfo = pathinfo(preg_replace('/[^a-z0-9_\.-]+/i', '_', $file));
		$pathinfo['extension'] = strtolower($pathinfo['extension']);
		$suffix = ((false !== $exists) && absint($exists))? "_$exists" : '';

		$fileinfo['extension'] = (empty($filetype['ext']))? $pathinfo['extension'] : $filetype['ext'];
		$fileinfo['filename'] = $pathinfo['filename'].$suffix;
		$fileinfo['basename'] = $fileinfo['filename'].'.'.$fileinfo['extension'];
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

		if((false !== $exists) && file_exists($fileinfo['filepath'])){
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
