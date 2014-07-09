<?php

/**
 * Main PHP class for the WordPress plugin GRAND Media

 */
class GmediaCore {

	var $upload;
	var $gmedia_url;

	/**
	 *
	 */
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

	/**
	 * @param array $add_args
	 * @param array $remove_args
	 * @param bool  $uri
	 *
	 * @return string
	 */
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

	/**
	 * @param $userAgent
	 *
	 * @return bool
	 */
	function is_crawler($userAgent) {
		$crawlers = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|FeedBurner|'.'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|'.'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby|yandex|facebook';
		$isCrawler = (preg_match("/$crawlers/i", $userAgent) > 0);

		return $isCrawler;
	}

	/**
	 * @param $userAgent
	 *
	 * @return bool
	 */
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
	 *
	 * @param bool $create
	 *
	 * @return array See above for description.
	 */
	function gm_upload_dir($create = true) {
		$slash = '/';
		// If multisite (and if not the main site)
		if(is_multisite() && ! is_main_site()){
			$slash = '/blogs.dir/'.get_current_blog_id().'/';
		}

		$dir = WP_CONTENT_DIR.$slash.GMEDIA_UPLOAD_FOLDER;
		$url = WP_CONTENT_URL.$slash.GMEDIA_UPLOAD_FOLDER;

		$uploads = apply_filters('gm_upload_dir', array('path' => $dir, 'url' => $url, 'error' => false));

		if($create){
			// Make sure we have an uploads dir
			if(! wp_mkdir_p($uploads['path'])){
				$message = sprintf(__('Unable to create directory %s. Is its parent directory writable by the server?'), $uploads['path']);
				$uploads['error'] = $message;
			}
		} elseif(!is_dir($uploads['path'])){
			$uploads['error'] = true;
		}

		return $uploads;
	}

	/**
	 * @param $path
	 *
	 * @return bool|null
	 */
	function delete_folder($path) {
		$path = rtrim($path, '/');
		if(is_file($path)){
			return @unlink($path);
		} elseif(is_dir($path)){
			$files = glob($path.'/*', GLOB_NOSORT);
			if(!empty($files) && is_array($files)){
				array_map(array($this, 'delete_folder'), $files);
			}
			return @rmdir($path);
		}
		return null;
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
					} elseif(('thumb' != $size) && (false !== filter_var($cover, FILTER_VALIDATE_URL))){
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
		global $gmGallery;

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
		@chmod($new_file, $perms);
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
			/*'@<[\/\!]*?[^<>]*?>@si'*/ /* Strip out HTML tags */
			 '@<script[^>]*?>.*?</script>@si' /* Strip out javascript */
			,'@<style[^>]*?>.*?</style>@siU' /* Strip style tags properly */
			,'@<![\s\S]*?--[ \t\n\r]*>@' /* Strip multi-line comments */
			//,'/\s{3,}/'
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

	/**
	 * Check if user can select a author
	 *
	 * @param      $user_id
	 * @param bool $exclude_zeros
	 *
	 * @return array
	 */
	function get_editable_user_ids($user_id, $exclude_zeros = true) {
		global $wpdb;

		new WP_User($user_id);

		$query = "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = '{$wpdb->prefix}user_level'";
		if($exclude_zeros){
			$query .= " AND meta_value != '0'";
		}

		return $wpdb->get_col($query);
	}

	/**
	 * Extremely simple function to get human filesize
	 * @param     $file
	 * @param int $decimals
	 *
	 * @return string
	 */
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

				if(!empty($d)){
					$diff[$k] = $d;
				}
			}
		}

		return $diff;
	}

	/**
	 * @param array $arr1
	 * @param array $arr2
	 * @param bool $update
	 *
	 * @return array
	 */
	function array_diff_keyval_recursive(array $arr1, array $arr2, $update = false) {
		$diff = array_diff_key($arr1, $arr2);
		$intersect = array_intersect_key($arr1, $arr2);

		foreach($intersect as $k => $v){
			if(is_array($arr1[$k]) && is_array($arr2[$k])){
				$d = $this->array_diff_keyval_recursive($arr1[$k], $arr2[$k], $update);

				if(!empty($d)){
					$diff[$k] = $d;
				}
			} elseif($arr1[$k] !== $arr2[$k]){
				if($update){
					$diff[$k] = $arr2[$k];
				} else{
					$diff[$k] = $arr1[$k];
				}
			}
		}

		return $diff;
	}

	/**
	 * @param $base
	 * @param $replacements
	 *
	 * @return mixed
	 */
	function array_replace_recursive($base, $replacements){
		if(function_exists('array_replace_recursive')){
			return array_replace_recursive($base, $replacements);
		}

		foreach (array_slice(func_get_args(), 1) as $replacements) {
			$bref_stack = array(&$base);
			$head_stack = array($replacements);

			do {
				end($bref_stack);

				$bref = &$bref_stack[key($bref_stack)];
				$head = array_pop($head_stack);

				unset($bref_stack[key($bref_stack)]);

				foreach (array_keys($head) as $key) {
					if (isset($key, $bref) && is_array($bref[$key]) && is_array($head[$key])) {
						$bref_stack[] = &$bref[$key];
						$head_stack[] = $head[$key];
					} else {
						$bref[$key] = $head[$key];
					}
				}
			} while(count($head_stack));
		}

		return $base;
	}

	/**
	 * @param $photo
	 *
	 * @return array|bool
	 */
	function process_gmedit_image($photo) {
		$type = null;
		if (preg_match('/^data:image\/(jpg|jpeg|png|gif)/i', $photo, $matches)) {
			$type = $matches[1];
		} else {
			return false;
		}

		// Remove the mime-type header
		$data = reset(array_reverse(explode('base64,', $photo)));

		// Use strict mode to prevent characters from outside the base64 range
		$image = base64_decode($data, true);

		if (!$image) { return false; }

		return array(
			'data' => $image,
			'type' => $type
		);
	}

	/**
	 * @return bool
	 */
	function is_bot() {
		$spiders = array(
			"abot", "dbot", "ebot", "hbot", "kbot", "lbot", "mbot", "nbot", "obot", "pbot", "rbot", "sbot", "tbot", "vbot", "ybot", "zbot", "bot.", "bot/", "_bot", ".bot", "/bot", "-bot", ":bot", "(bot", "crawl", "slurp", "spider", "seek",
			"accoona", "acoon", "adressendeutschland", "ah-ha.com", "ahoy", "altavista", "ananzi", "anthill", "appie", "arachnophilia", "arale", "araneo", "aranha", "architext", "aretha", "arks", "asterias", "atlocal", "atn", "atomz", "augurfind",
			"backrub", "bannana_bot", "baypup", "bdfetch", "big brother", "biglotron", "bjaaland", "blackwidow", "blaiz", "blog", "blo.", "bloodhound", "boitho", "booch", "bradley", "butterfly",
			"calif", "cassandra", "ccubee", "cfetch", "charlotte", "churl", "cienciaficcion", "cmc", "collective", "comagent", "combine", "computingsite", "csci", "curl", "cusco",
			"daumoa", "deepindex", "delorie", "depspid", "deweb", "die blinde kuh", "digger", "ditto", "dmoz", "docomo", "download express", "dtaagent", "dwcp",
			"ebiness", "ebingbong", "e-collector", "ejupiter", "emacs-w3 search engine", "esther", "evliya celebi", "ezresult",
			"falcon", "felix ide", "ferret", "fetchrover", "fido", "findlinks", "fireball", "fish search", "fouineur", "funnelweb",
			"gazz", "gcreep", "genieknows", "getterroboplus", "geturl", "glx", "goforit", "golem", "grabber", "grapnel", "gralon", "griffon", "gromit", "grub", "gulliver",
			"hamahakki", "harvest", "havindex", "helix", "heritrix", "hku www octopus", "homerweb", "htdig", "html index", "html_analyzer", "htmlgobble", "hubater", "hyper-decontextualizer",
			"ia_archiver", "ibm_planetwide", "ichiro", "iconsurf", "iltrovatore", "image.kapsi.net", "imagelock", "incywincy", "indexer", "infobee", "informant", "ingrid", "inktomisearch.com", "inspector web", "intelliagent", "internet shinchakubin", "ip3000", "iron33", "israeli-search", "ivia",
			"jack", "jakarta", "javabee", "jetbot", "jumpstation",
			"katipo", "kdd-explorer", "kilroy", "knowledge", "kototoi", "kretrieve",
			"labelgrabber", "lachesis", "larbin", "legs", "libwww", "linkalarm", "link validator", "linkscan", "lockon", "lwp", "lycos",
			"magpie", "mantraagent", "mapoftheinternet", "marvin/", "mattie", "mediafox", "mediapartners", "mercator", "merzscope", "microsoft url control", "minirank", "miva", "mj12", "mnogosearch", "moget", "monster", "moose", "motor", "multitext", "muncher", "muscatferret", "mwd.search", "myweb",
			"najdi", "nameprotect", "nationaldirectory", "nazilla", "ncsa beta", "nec-meshexplorer", "nederland.zoek", "netcarta webmap engine", "netmechanic", "netresearchserver", "netscoop", "newscan-online", "nhse", "nokia6682/", "nomad", "noyona", "nutch", "nzexplorer",
			"objectssearch", "occam", "omni", "open text", "openfind", "openintelligencedata", "orb search", "osis-project",
			"pack rat", "pageboy", "pagebull", "page_verifier", "panscient", "parasite", "partnersite", "patric", "pear.", "pegasus", "peregrinator", "pgp key agent", "phantom", "phpdig", "picosearch", "piltdownman", "pimptrain", "pinpoint", "pioneer", "piranha", "plumtreewebaccessor", "pogodak", "poirot", "pompos", "poppelsdorf", "poppi", "popular iconoclast", "psycheclone", "publisher", "python",
			"rambler", "raven search", "roach", "road runner", "roadhouse", "robbie", "robofox", "robozilla", "rules",
			"salty", "sbider", "scooter", "scoutjet", "scrubby", "search.", "searchprocess", "semanticdiscovery", "senrigan", "sg-scout", "shai'hulud", "shark", "shopwiki", "sidewinder", "sift", "silk", "simmany", "site searcher", "site valet", "sitetech-rover", "skymob.com", "sleek", "smartwit", "sna-", "snappy", "snooper", "sohu", "speedfind", "sphere", "sphider", "spinner", "spyder", "steeler/", "suke", "suntek", "supersnooper", "surfnomore", "sven", "sygol", "szukacz",
			"tach black widow", "tarantula", "templeton", "/teoma", "t-h-u-n-d-e-r-s-t-o-n-e", "theophrastus", "titan", "titin", "tkwww", "toutatis", "t-rex", "tutorgig", "twiceler", "twisted",
			"ucsd", "udmsearch", "url check", "updated",
			"vagabondo", "valkyrie", "verticrawl", "victoria", "vision-search", "volcano", "voyager/", "voyager-hc",
			"w3c_validator", "w3m2", "w3mir", "walker", "wallpaper", "wanderer", "wauuu", "wavefire", "web core", "web hopper", "web wombat", "webbandit", "webcatcher", "webcopy", "webfoot", "weblayers", "weblinker", "weblog monitor", "webmirror", "webmonkey", "webquest", "webreaper", "websitepulse", "websnarf", "webstolperer", "webvac", "webwalk", "webwatch", "webwombat", "webzinger", "wget", "whizbang", "whowhere", "wild ferret", "worldlight", "wwwc", "wwwster",
			"xenu", "xget", "xift", "xirq",
			"yandex", "yanga", "yeti", "yodao",
			"zao/", "zippp", "zyborg",
			"...."
		);

		foreach($spiders as $spider) {
			//If the spider text is found in the current user agent, then return true
			if ( stripos($_SERVER['HTTP_USER_AGENT'], $spider) !== false ) {
				return true;
				break;
			}
		}
		return false;
	}


	/**
	 * Parse ID3v2, ID3v1, and getID3 comments to extract usable data
	 *
	 * @since 3.6.0
	 *
	 * @param array $metadata An existing array with data
	 * @param array $data Data supplied by ID3 tags
	 */
	function wp_add_id3_tag_data( &$metadata, $data ) {
		foreach ( array( 'id3v2', 'id3v1' ) as $version ) {
			if ( ! empty( $data[$version]['comments'] ) ) {
				foreach ( $data[$version]['comments'] as $key => $list ) {
					if ( ! empty( $list ) ) {
						$metadata[$key] = reset( $list );
						// fix bug in byte stream analysis
						if ( 'terms_of_use' === $key && 0 === strpos( $metadata[$key], 'yright notice.' ) )
							$metadata[$key] = 'Cop' . $metadata[$key];
					}
				}
				break;
			}
		}

		if ( ! empty( $data['id3v2']['APIC'] ) ) {
			$image = reset( $data['id3v2']['APIC']);
			if ( ! empty( $image['data'] ) ) {
				$metadata['image'] = array(
					'data' => $image['data'],
					'mime' => $image['image_mime'],
					'width' => $image['image_width'],
					'height' => $image['image_height']
				);
			}
		} elseif ( ! empty( $data['comments']['picture'] ) ) {
			$image = reset( $data['comments']['picture'] );
			if ( ! empty( $image['data'] ) ) {
				$metadata['image'] = array(
					'data' => $image['data'],
					'mime' => $image['image_mime']
				);
			}
		}
	}

	/**
	 * Retrieve metadata from a video file's ID3 tags
	 *
	 * @since 3.6.0
	 *
	 * @param string $file Path to file.
	 * @return array|boolean Returns array of metadata, if found.
	 */
	function wp_read_video_metadata( $file ) {
		if ( ! file_exists( $file ) )
			return false;

		$metadata = array();

		if ( ! class_exists( 'getID3' ) )
			require( ABSPATH . WPINC . '/ID3/getid3.php' );
		$id3 = new getID3();
		$data = $id3->analyze( $file );

		if ( isset( $data['video']['lossless'] ) )
			$metadata['lossless'] = $data['video']['lossless'];
		if ( ! empty( $data['video']['bitrate'] ) )
			$metadata['bitrate'] = (int) $data['video']['bitrate'];
		if ( ! empty( $data['video']['bitrate_mode'] ) )
			$metadata['bitrate_mode'] = $data['video']['bitrate_mode'];
		if ( ! empty( $data['filesize'] ) )
			$metadata['filesize'] = (int) $data['filesize'];
		if ( ! empty( $data['mime_type'] ) )
			$metadata['mime_type'] = $data['mime_type'];
		if ( ! empty( $data['playtime_seconds'] ) )
			$metadata['length'] = (int) ceil( $data['playtime_seconds'] );
		if ( ! empty( $data['playtime_string'] ) )
			$metadata['length_formatted'] = $data['playtime_string'];
		if ( ! empty( $data['video']['resolution_x'] ) )
			$metadata['width'] = (int) $data['video']['resolution_x'];
		if ( ! empty( $data['video']['resolution_y'] ) )
			$metadata['height'] = (int) $data['video']['resolution_y'];
		if ( ! empty( $data['fileformat'] ) )
			$metadata['fileformat'] = $data['fileformat'];
		if ( ! empty( $data['video']['dataformat'] ) )
			$metadata['dataformat'] = $data['video']['dataformat'];
		if ( ! empty( $data['video']['encoder'] ) )
			$metadata['encoder'] = $data['video']['encoder'];
		if ( ! empty( $data['video']['codec'] ) )
			$metadata['codec'] = $data['video']['codec'];

		if ( ! empty( $data['audio'] ) ) {
			unset( $data['audio']['streams'] );
			$metadata['audio'] = $data['audio'];
		}

		$this->wp_add_id3_tag_data( $metadata, $data );

		return $metadata;
	}

	/**
	 * Retrieve metadata from a audio file's ID3 tags
	 *
	 * @since 3.6.0
	 *
	 * @param string $file Path to file.
	 * @return array|boolean Returns array of metadata, if found.
	 */
	function wp_read_audio_metadata( $file ) {
		if ( ! file_exists( $file ) )
			return false;
		$metadata = array();

		if ( ! class_exists( 'getID3' ) )
			require( ABSPATH . WPINC . '/ID3/getid3.php' );
		$id3 = new getID3();
		$data = $id3->analyze( $file );

		if ( ! empty( $data['audio'] ) ) {
			unset( $data['audio']['streams'] );
			$metadata = $data['audio'];
		}

		if ( ! empty( $data['fileformat'] ) )
			$metadata['fileformat'] = $data['fileformat'];
		if ( ! empty( $data['filesize'] ) )
			$metadata['filesize'] = (int) $data['filesize'];
		if ( ! empty( $data['mime_type'] ) )
			$metadata['mime_type'] = $data['mime_type'];
		if ( ! empty( $data['playtime_seconds'] ) )
			$metadata['length'] = (int) ceil( $data['playtime_seconds'] );
		if ( ! empty( $data['playtime_string'] ) )
			$metadata['length_formatted'] = $data['playtime_string'];

		$this->wp_add_id3_tag_data( $metadata, $data );

		return $metadata;
	}

}

global $gmCore;
$gmCore = new GmediaCore();
