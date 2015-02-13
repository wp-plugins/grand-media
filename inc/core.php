<?php
if ( preg_match( '#' . basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Main PHP class for the WordPress plugin GRAND Media

 */
class GmediaCore {

	var $upload;
	var $gmedia_url;
	var $caps = array();


	/**
	 *
	 */
	function __construct() {
		$this->upload     = $this->gm_upload_dir();
		$this->gmedia_url = plugins_url( GMEDIA_FOLDER );

		add_action( 'init', array( &$this, 'capabilities' ), 8 );
	}

	function capabilities() {
		$capabilities = gmedia_plugin_capabilities();
		$capabilities = apply_filters( 'gmedia_capabilities', $capabilities );
		if(is_multisite() && is_super_admin()){
			foreach ( $capabilities as $cap ) {
				$this->caps[ $cap ] = 1;
			}
		} else {
			$curuser = wp_get_current_user();
			foreach ( $capabilities as $cap ) {
				if ( isset( $curuser->allcaps[ $cap ] ) && intval( $curuser->allcaps[ $cap ] ) ) {
					$this->caps[ $cap ] = 1;
				} else {
					$this->caps[ $cap ] = 0;
				}
			}
		}
	}

	/**
	 * Check GET data
	 *
	 * @param string $var
	 * @param mixed $def
	 *
	 * @return mixed
	 */
	function _get( $var = '', $def = false ) {
		return isset( $_GET[ $var ] ) ? $_GET[ $var ] : $def;
	}

	/**
	 * Check POST data
	 *
	 * @param string $var
	 * @param bool|mixed $def
	 *
	 * @return mixed
	 */

	function _post( $var = '', $def = false ) {
		return isset( $_POST[ $var ] ) ? $_POST[ $var ] : $def;
	}

	/**
	 * Check REQUEST data
	 *
	 * @param string $var
	 * @param bool $def
	 *
	 * @return mixed
	 */
	function _req( $var = '', $def = false ) {
		return isset( $_REQUEST[ $var ] ) ? $_REQUEST[ $var ] : $def;
	}

	/**
	 * tooltip()
	 *
	 * @param string $style 'tooltip', 'popover'
	 * @param array $params
	 * @param bool $print
	 *
	 * @return string
	 */
	function tooltip( $style, $params, $print = true ) {
		$show_tip = 0; // TODO show tooltips checkbox in settings
		if ( $show_tip ) {
			$tooltip = " data-toggle='$style'";
			if ( is_array( $params ) && ! empty( $params ) ) {
				foreach ( $params as $key => $val ) {
					$tooltip .= " data-$key='$val'";
				}
			}
			if ( $print ) {
				echo $tooltip;
			} else {
				return $tooltip;
			}
		}

		return false;
	}

	/**
	 * @param array $add_args
	 * @param array $remove_args
	 * @param bool $uri
	 *
	 * @return string
	 */
	function get_admin_url( $add_args = array(), $remove_args = array(), $uri = false ) {
		if ( true === $uri ) {
			$uri = admin_url( 'admin.php' );
		}
		$remove_args = array_unique( array_merge( array( 'doing_wp_cron', '_wpnonce', 'delete', 'update_meta' ), $remove_args, array_keys( $add_args ) ) );
		$new_uri     = remove_query_arg( $remove_args, $uri );
		if ( ! empty( $add_args ) ) {
			$new_uri = add_query_arg( $add_args, $new_uri );
		}

		return $new_uri;
	}

	/**
	 * @param $userAgent
	 *
	 * @return bool
	 */
	function is_crawler( $userAgent ) {
		$crawlers  = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|FeedBurner|' . 'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|' . 'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby|yandex|facebook';
		$isCrawler = ( preg_match( "/$crawlers/i", $userAgent ) > 0 );

		return $isCrawler;
	}

	/**
	 * @param $userAgent
	 *
	 * @return bool
	 */
	function is_browser( $userAgent ) {
		$browsers  = 'opera|aol|msie|firefox|chrome|konqueror|safari|netscape|navigator|mosaic|lynx|amaya|omniweb|avant|camino|flock|seamonkey|mozilla|gecko';
		$isBrowser = ( preg_match( "/$browsers/i", $userAgent ) > 0 );

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
	function gm_upload_dir( $create = true ) {
		$slash = '/';
		// If multisite (and if not the main site)
		if ( is_multisite() && ! is_main_site() ) {
			$slash = '/blogs.dir/' . get_current_blog_id() . '/';
		}

		$dir = WP_CONTENT_DIR . $slash . GMEDIA_UPLOAD_FOLDER;
		$url = WP_CONTENT_URL . $slash . GMEDIA_UPLOAD_FOLDER;

		$url = set_url_scheme($url);

		$uploads = apply_filters( 'gm_upload_dir', array( 'path' => $dir, 'url' => $url, 'error' => false ) );

		if ( $create ) {
			// Make sure we have an uploads dir
			if ( ! wp_mkdir_p( $uploads['path'] ) ) {
				$message          = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $uploads['path'] );
				$uploads['error'] = $message;
			}
		} elseif ( ! is_dir( $uploads['path'] ) ) {
			$uploads['error'] = true;
		}

		return $uploads;
	}

	/**
	 * @param $path
	 *
	 * @return bool|null
	 */
	function delete_folder( $path ) {
		$path = rtrim( $path, '/' );
		if ( is_file( $path ) ) {
			return @unlink( $path );
		} elseif ( is_dir( $path ) ) {
			$files = glob( $path . '/*', GLOB_NOSORT );
			if ( ! empty( $files ) && is_array( $files ) ) {
				array_map( array( $this, 'delete_folder' ), $files );
			}

			return @rmdir( $path );
		}

		return null;
	}

	/**
	 * Get an HTML img element representing an image attachment
	 * @see  add_image_size()
	 * @see  wp_get_attachment_image()
	 * @uses apply_filters() Calls 'gm_get_attachment_image_attributes' hook on attributes array
	 *
	 * @param int|object $item Image object.
	 * @param string $size Optional, default is empty string, could be 'thumb', 'original'
	 * @param bool $cover Optional, try to get cover url
	 * @param bool|string $default Optional, return if no cover
	 *
	 * @return string img url
	 */
	function gm_get_media_image( $item, $size = '', $cover = true, $default = false ) {
		global $gmDB, $gmGallery;

		if ( ! is_object( $item ) ) {
			$item = $gmDB->get_gmedia( $item );
		}
		$type = explode( '/', $item->mime_type );

		if ( 'image' == $type[0] ) {
			$type_folder = $this->upload['url'] . '/' . $gmGallery->options['folder'][ $type[0] ];

			switch ( $size ) {
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

			$image = $type_folder . $size_folder . $item->gmuid;
		} else {
			$ext = ltrim( strrchr( $item->gmuid, '.' ), '.' );
			if ( ! $type = wp_ext2type( $ext ) ) {
				$type = 'application';
			}
			$image = $this->gmedia_url . '/admin/images/' . $type . '.png';

			if ( $cover ) {
				$cover = $gmDB->get_metadata( 'gmedia', $item->ID, 'cover', true );
				if ( ! empty( $cover ) ) {
					if ( $this->is_digit( $cover ) ) {
						$image = $this->gm_get_media_image( (int) $cover, $size, false );
					} elseif ( false !== filter_var( $cover, FILTER_VALIDATE_URL ) ) {
						return $cover;
					}
				} elseif ( false !== $default ) {
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
	function get_module_path( $module_name ) {
		global $gmGallery;
		if ( empty( $module_name ) ) {
			return false;
		}
		$module_dirs = array(
			'upload' => array(
				'path' => $this->upload['path'] . '/' . $gmGallery->options['folder']['module'] . '/' . $module_name,
				'url'  => $this->upload['url'] . '/' . $gmGallery->options['folder']['module'] . '/' . $module_name
			),
			'plugin' => array(
				'path' => GMEDIA_ABSPATH . 'module/' . $module_name,
				'url'  => plugins_url( GMEDIA_FOLDER ) . '/module/' . $module_name
			),
		);
		foreach ( $module_dirs as $dir ) {
			if ( is_dir( $dir['path'] ) ) {
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
	 * @return array|bool
	 */
	function fileinfo( $file, $exists = 0 ) {
		global $gmGallery, $user_ID;

		$file                  = basename( $file );
		$file_lower            = strtolower( $file );
		$filetype              = wp_check_filetype( $file_lower, $mimes = null );
		$title                 = pathinfo( $file, PATHINFO_FILENAME );
		$pathinfo              = pathinfo( preg_replace( '/[^a-z0-9_\.-]+/i', '_', $file ) );
		$pathinfo['extension'] = strtolower( $pathinfo['extension'] );
		$suffix                = ( ( false !== $exists ) && absint( $exists ) ) ? "_$exists" : '';

		$fileinfo['extension'] = ( empty( $filetype['ext'] ) ) ? $pathinfo['extension'] : $filetype['ext'];

		$allowed_ext = get_allowed_mime_types( $user_ID );
		$allowed_ext = array_keys( $allowed_ext );
		$allowed_ext = implode( '|', $allowed_ext );
		$allowed_ext = explode( '|', $allowed_ext );
		if ( ! in_array( $fileinfo['extension'], $allowed_ext ) ) {
			return false;
		}

		$fileinfo['filename']  = $pathinfo['filename'] . $suffix;
		$fileinfo['basename']  = $fileinfo['filename'] . '.' . $fileinfo['extension'];
		$fileinfo['title']     = ucwords( str_replace( '_', ' ', esc_sql( $title ) ) );
		$fileinfo['mime_type'] = ( empty( $filetype['type'] ) ) ? 'application/' . $fileinfo['extension'] : $filetype['type'];
		list( $dirname ) = explode( '/', $fileinfo['mime_type'] );
		$fileinfo['dirname']          = $dirname;
		$fileinfo['dirpath']          = $this->upload['path'] . '/' . $gmGallery->options['folder'][ $dirname ];
		$fileinfo['dirurl']           = $this->upload['url'] . '/' . $gmGallery->options['folder'][ $dirname ];
		$fileinfo['filepath']         = $fileinfo['dirpath'] . '/' . $fileinfo['basename'];
		$fileinfo['fileurl']          = $fileinfo['dirurl'] . '/' . $fileinfo['basename'];
		$fileinfo['fileurl_original'] = $fileinfo['dirurl'] . '/' . $fileinfo['basename'];
		if ( 'image' == $dirname ) {
			$fileinfo['dirpath_original']  = $this->upload['path'] . '/' . $gmGallery->options['folder']['image_original'];
			$fileinfo['dirurl_original']   = $this->upload['url'] . '/' . $gmGallery->options['folder']['image_original'];
			$fileinfo['filepath_original'] = $fileinfo['dirpath_original'] . '/' . $fileinfo['basename'];
			$fileinfo['fileurl_original']  = $fileinfo['dirurl_original'] . '/' . $fileinfo['basename'];
			$fileinfo['dirpath_thumb']     = $this->upload['path'] . '/' . $gmGallery->options['folder']['image_thumb'];
			$fileinfo['dirurl_thumb']      = $this->upload['url'] . '/' . $gmGallery->options['folder']['image_thumb'];
			$fileinfo['filepath_thumb']    = $fileinfo['dirpath_thumb'] . '/' . $fileinfo['basename'];
			$fileinfo['fileurl_thumb']     = $fileinfo['dirurl_thumb'] . '/' . $fileinfo['basename'];
		}

		if ( ( false !== $exists ) && file_exists( $fileinfo['filepath'] ) ) {
			$exists   = absint( $exists ) + 1;
			$fileinfo = $this->fileinfo( $file, $exists );
		}

		return $fileinfo;
	}

	/** Set correct file permissions (chmod)
	 *
	 * @param string $new_file
	 */
	function file_chmod( $new_file ) {
		$stat  = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		@chmod( $new_file, $perms );
	}

	/**
	 * The most complete and efficient way to sanitize a string before using it with your database
	 *
	 * @param $input string
	 *
	 * @return mixed
	 */
	function clean_input( $input ) {
		$search = array(
			/*'@<[\/\!]*?[^<>]*?>@si'*/ /* Strip out HTML tags */
			'@<script[^>]*?>.*?</script>@si' /* Strip out javascript */,
			'@<style[^>]*?>.*?</style>@siU' /* Strip style tags properly */,
			'@<![\s\S]*?--[ \t\n\r]*>@' /* Strip multi-line comments */
			//,'/\s{3,}/'
		);

		$output = preg_replace( $search, '', $input );

		return $output;
	}

	/**
	 * Sanitize a string|array before using it with your database
	 *
	 * @param $input string|array
	 *
	 * @return mixed
	 */
	function sanitize( $input ) {
		$output = $input;
		if ( is_array( $input ) ) {
			foreach ( $input as $var => $val ) {
				$output[ $var ] = $this->sanitize( $val );
			}
		} else {
			if ( get_magic_quotes_gpc() ) {
				$input = stripslashes( $input );
			}
			$input  = $this->clean_input( $input );
			$output = esc_sql( $input );
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
	function is_digit( $digit ) {
		if ( is_int( $digit ) ) {
			return true;
		} elseif ( is_string( $digit ) && ! empty( $digit ) ) {
			return ctype_digit( $digit );
		} else {
			// booleans, floats and others
			return false;
		}
	}

	/**
	 * Check if user can select a author
	 *
	 * @return array
	 */
	function get_editable_user_ids() {
		if ( current_user_can( 'gmedia_show_others_media' ) || current_user_can( 'gmedia_edit_others_media' ) ) {
			return get_users( array( 'who' => 'authors', 'fields' => 'ID' ) );
		}

		return get_current_user_id();
	}

	/**
	 * Extremely simple function to get human filesize
	 *
	 * @param     $file
	 * @param int $decimals
	 *
	 * @return string
	 */
	function filesize( $file, $decimals = 2 ) {
		$bytes  = filesize( $file );
		$sz     = array( 'b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb' );
		$factor = (int) floor( ( strlen( $bytes ) - 1 ) / 3 );

		return sprintf( "%.{$decimals}f", $bytes / pow( 1024, $factor ) ) . $sz[ $factor ];
	}

	/**
	 * @author  Gajus Kuizinas <g.kuizinas@anuary.com>
	 * @version 1.0.0 (2013 03 19)
	 */
	function array_diff_key_recursive( array $arr1, array $arr2 ) {
		$diff      = array_diff_key( $arr1, $arr2 );
		$intersect = array_intersect_key( $arr1, $arr2 );

		foreach ( $intersect as $k => $v ) {
			if ( is_array( $arr1[ $k ] ) && is_array( $arr2[ $k ] ) ) {
				$d = $this->array_diff_key_recursive( $arr1[ $k ], $arr2[ $k ] );

				if ( ! empty( $d ) ) {
					$diff[ $k ] = $d;
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
	function array_diff_keyval_recursive( array $arr1, array $arr2, $update = false ) {
		$diff      = array_diff_key( $arr1, $arr2 );
		$intersect = array_intersect_key( $arr1, $arr2 );

		foreach ( $intersect as $k => $v ) {
			if ( is_array( $arr1[ $k ] ) && is_array( $arr2[ $k ] ) ) {
				$d = $this->array_diff_keyval_recursive( $arr1[ $k ], $arr2[ $k ], $update );

				if ( ! empty( $d ) ) {
					$diff[ $k ] = $d;
				}
			} elseif ( $arr1[ $k ] !== $arr2[ $k ] ) {
				if ( $update ) {
					$diff[ $k ] = $arr2[ $k ];
				} else {
					$diff[ $k ] = $arr1[ $k ];
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
	function array_replace_recursive( $base, $replacements ) {
		if ( function_exists( 'array_replace_recursive' ) ) {
			return array_replace_recursive( $base, $replacements );
		}

		foreach ( array_slice( func_get_args(), 1 ) as $replacements ) {
			$bref_stack = array( &$base );
			$head_stack = array( $replacements );

			do {
				end( $bref_stack );

				$bref = &$bref_stack[ key( $bref_stack ) ];
				$head = array_pop( $head_stack );

				unset( $bref_stack[ key( $bref_stack ) ] );

				foreach ( array_keys( $head ) as $key ) {
					if ( isset( $key, $bref ) && is_array( $bref[ $key ] ) && is_array( $head[ $key ] ) ) {
						$bref_stack[] = &$bref[ $key ];
						$head_stack[] = $head[ $key ];
					} else {
						$bref[ $key ] = $head[ $key ];
					}
				}
			} while ( count( $head_stack ) );
		}

		return $base;
	}

	/**
	 * @param $photo
	 *
	 * @return array|bool
	 */
	function process_gmedit_image( $photo ) {
		$type = null;
		if ( preg_match( '/^data:image\/(jpg|jpeg|png|gif)/i', $photo, $matches ) ) {
			$type = $matches[1];
		} else {
			return false;
		}
		// Remove the mime-type header
		$data = explode( 'base64,', $photo );
		$data = array_reverse( $data );
		$data = reset( $data );

		// Use strict mode to prevent characters from outside the base64 range
		$image = base64_decode( $data, true );

		if ( ! $image ) {
			return false;
		}

		return array(
			'data' => $image,
			'type' => $type
		);
	}

	/**
	 * @return bool
	 */
	function is_bot() {
		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return false;
		}

		$spiders = array(
			"abot",
			"dbot",
			"ebot",
			"hbot",
			"kbot",
			"lbot",
			"mbot",
			"nbot",
			"obot",
			"pbot",
			"rbot",
			"sbot",
			"tbot",
			"vbot",
			"ybot",
			"zbot",
			"bot.",
			"bot/",
			"_bot",
			".bot",
			"/bot",
			"-bot",
			":bot",
			"(bot",
			"crawl",
			"slurp",
			"spider",
			"seek",
			"accoona",
			"acoon",
			"adressendeutschland",
			"ah-ha.com",
			"ahoy",
			"altavista",
			"ananzi",
			"anthill",
			"appie",
			"arachnophilia",
			"arale",
			"araneo",
			"aranha",
			"architext",
			"aretha",
			"arks",
			"asterias",
			"atlocal",
			"atn",
			"atomz",
			"augurfind",
			"backrub",
			"bannana_bot",
			"baypup",
			"bdfetch",
			"big brother",
			"biglotron",
			"bjaaland",
			"blackwidow",
			"blaiz",
			"blog",
			"blo.",
			"bloodhound",
			"boitho",
			"booch",
			"bradley",
			"butterfly",
			"calif",
			"cassandra",
			"ccubee",
			"cfetch",
			"charlotte",
			"churl",
			"cienciaficcion",
			"cmc",
			"collective",
			"comagent",
			"combine",
			"computingsite",
			"csci",
			"curl",
			"cusco",
			"daumoa",
			"deepindex",
			"delorie",
			"depspid",
			"deweb",
			"die blinde kuh",
			"digger",
			"ditto",
			"dmoz",
			"docomo",
			"download express",
			"dtaagent",
			"dwcp",
			"ebiness",
			"ebingbong",
			"e-collector",
			"ejupiter",
			"emacs-w3 search engine",
			"esther",
			"evliya celebi",
			"ezresult",
			"falcon",
			"felix ide",
			"ferret",
			"fetchrover",
			"fido",
			"findlinks",
			"fireball",
			"fish search",
			"fouineur",
			"funnelweb",
			"gazz",
			"gcreep",
			"genieknows",
			"getterroboplus",
			"geturl",
			"glx",
			"goforit",
			"golem",
			"grabber",
			"grapnel",
			"gralon",
			"griffon",
			"gromit",
			"grub",
			"gulliver",
			"hamahakki",
			"harvest",
			"havindex",
			"helix",
			"heritrix",
			"hku www octopus",
			"homerweb",
			"htdig",
			"html index",
			"html_analyzer",
			"htmlgobble",
			"hubater",
			"hyper-decontextualizer",
			"ia_archiver",
			"ibm_planetwide",
			"ichiro",
			"iconsurf",
			"iltrovatore",
			"image.kapsi.net",
			"imagelock",
			"incywincy",
			"indexer",
			"infobee",
			"informant",
			"ingrid",
			"inktomisearch.com",
			"inspector web",
			"intelliagent",
			"internet shinchakubin",
			"ip3000",
			"iron33",
			"israeli-search",
			"ivia",
			"jack",
			"jakarta",
			"javabee",
			"jetbot",
			"jumpstation",
			"katipo",
			"kdd-explorer",
			"kilroy",
			"knowledge",
			"kototoi",
			"kretrieve",
			"labelgrabber",
			"lachesis",
			"larbin",
			"legs",
			"libwww",
			"linkalarm",
			"link validator",
			"linkscan",
			"lockon",
			"lwp",
			"lycos",
			"magpie",
			"mantraagent",
			"mapoftheinternet",
			"marvin/",
			"mattie",
			"mediafox",
			"mediapartners",
			"mercator",
			"merzscope",
			"microsoft url control",
			"minirank",
			"miva",
			"mj12",
			"mnogosearch",
			"moget",
			"monster",
			"moose",
			"motor",
			"multitext",
			"muncher",
			"muscatferret",
			"mwd.search",
			"myweb",
			"najdi",
			"nameprotect",
			"nationaldirectory",
			"nazilla",
			"ncsa beta",
			"nec-meshexplorer",
			"nederland.zoek",
			"netcarta webmap engine",
			"netmechanic",
			"netresearchserver",
			"netscoop",
			"newscan-online",
			"nhse",
			"nokia6682/",
			"nomad",
			"noyona",
			"nutch",
			"nzexplorer",
			"objectssearch",
			"occam",
			"omni",
			"open text",
			"openfind",
			"openintelligencedata",
			"orb search",
			"osis-project",
			"pack rat",
			"pageboy",
			"pagebull",
			"page_verifier",
			"panscient",
			"parasite",
			"partnersite",
			"patric",
			"pear.",
			"pegasus",
			"peregrinator",
			"pgp key agent",
			"phantom",
			"phpdig",
			"picosearch",
			"piltdownman",
			"pimptrain",
			"pinpoint",
			"pioneer",
			"piranha",
			"plumtreewebaccessor",
			"pogodak",
			"poirot",
			"pompos",
			"poppelsdorf",
			"poppi",
			"popular iconoclast",
			"psycheclone",
			"publisher",
			"python",
			"rambler",
			"raven search",
			"roach",
			"road runner",
			"roadhouse",
			"robbie",
			"robofox",
			"robozilla",
			"rules",
			"salty",
			"sbider",
			"scooter",
			"scoutjet",
			"scrubby",
			"search.",
			"searchprocess",
			"semanticdiscovery",
			"senrigan",
			"sg-scout",
			"shai'hulud",
			"shark",
			"shopwiki",
			"sidewinder",
			"sift",
			"silk",
			"simmany",
			"site searcher",
			"site valet",
			"sitetech-rover",
			"skymob.com",
			"sleek",
			"smartwit",
			"sna-",
			"snappy",
			"snooper",
			"sohu",
			"speedfind",
			"sphere",
			"sphider",
			"spinner",
			"spyder",
			"steeler/",
			"suke",
			"suntek",
			"supersnooper",
			"surfnomore",
			"sven",
			"sygol",
			"szukacz",
			"tach black widow",
			"tarantula",
			"templeton",
			"/teoma",
			"t-h-u-n-d-e-r-s-t-o-n-e",
			"theophrastus",
			"titan",
			"titin",
			"tkwww",
			"toutatis",
			"t-rex",
			"tutorgig",
			"twiceler",
			"twisted",
			"ucsd",
			"udmsearch",
			"url check",
			"updated",
			"vagabondo",
			"valkyrie",
			"verticrawl",
			"victoria",
			"vision-search",
			"volcano",
			"voyager/",
			"voyager-hc",
			"w3c_validator",
			"w3m2",
			"w3mir",
			"walker",
			"wallpaper",
			"wanderer",
			"wauuu",
			"wavefire",
			"web core",
			"web hopper",
			"web wombat",
			"webbandit",
			"webcatcher",
			"webcopy",
			"webfoot",
			"weblayers",
			"weblinker",
			"weblog monitor",
			"webmirror",
			"webmonkey",
			"webquest",
			"webreaper",
			"websitepulse",
			"websnarf",
			"webstolperer",
			"webvac",
			"webwalk",
			"webwatch",
			"webwombat",
			"webzinger",
			"wget",
			"whizbang",
			"whowhere",
			"wild ferret",
			"worldlight",
			"wwwc",
			"wwwster",
			"xenu",
			"xget",
			"xift",
			"xirq",
			"yandex",
			"yanga",
			"yeti",
			"yodao",
			"zao/",
			"zippp",
			"zyborg",
			"...."
		);

		foreach ( $spiders as $spider ) {
			//If the spider text is found in the current user agent, then return true
			if ( stripos( $_SERVER['HTTP_USER_AGENT'], $spider ) !== false ) {
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
			if ( ! empty( $data[ $version ]['comments'] ) ) {
				foreach ( $data[ $version ]['comments'] as $key => $list ) {
					if ( ! empty( $list ) ) {
						$metadata[ $key ] = reset( $list );
						// fix bug in byte stream analysis
						if ( 'terms_of_use' === $key && 0 === strpos( $metadata[ $key ], 'yright notice.' ) ) {
							$metadata[ $key ] = 'Cop' . $metadata[ $key ];
						}
					}
				}
				break;
			}
		}

		if ( ! empty( $data['id3v2']['APIC'] ) ) {
			$image = reset( $data['id3v2']['APIC'] );
			if ( ! empty( $image['data'] ) ) {
				$metadata['image'] = array(
					'data'   => $image['data'],
					'mime'   => $image['image_mime'],
					'width'  => $image['image_width'],
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
	 *
	 * @return array|boolean Returns array of metadata, if found.
	 */
	function wp_read_video_metadata( $file ) {
		if ( ! file_exists( $file ) ) {
			return false;
		}

		$metadata = array();

		if ( ! class_exists( 'getID3' ) ) {
			require( ABSPATH . WPINC . '/ID3/getid3.php' );
		}
		$id3  = new getID3();
		$data = $id3->analyze( $file );

		if ( isset( $data['video']['lossless'] ) ) {
			$metadata['lossless'] = $data['video']['lossless'];
		}
		if ( ! empty( $data['video']['bitrate'] ) ) {
			$metadata['bitrate'] = (int) $data['video']['bitrate'];
		}
		if ( ! empty( $data['video']['bitrate_mode'] ) ) {
			$metadata['bitrate_mode'] = $data['video']['bitrate_mode'];
		}
		if ( ! empty( $data['filesize'] ) ) {
			$metadata['filesize'] = (int) $data['filesize'];
		}
		if ( ! empty( $data['mime_type'] ) ) {
			$metadata['mime_type'] = $data['mime_type'];
		}
		if ( ! empty( $data['playtime_seconds'] ) ) {
			$metadata['length'] = (int) ceil( $data['playtime_seconds'] );
		}
		if ( ! empty( $data['playtime_string'] ) ) {
			$metadata['length_formatted'] = $data['playtime_string'];
		}
		if ( ! empty( $data['video']['resolution_x'] ) ) {
			$metadata['width'] = (int) $data['video']['resolution_x'];
		}
		if ( ! empty( $data['video']['resolution_y'] ) ) {
			$metadata['height'] = (int) $data['video']['resolution_y'];
		}
		if ( ! empty( $data['fileformat'] ) ) {
			$metadata['fileformat'] = $data['fileformat'];
		}
		if ( ! empty( $data['video']['dataformat'] ) ) {
			$metadata['dataformat'] = $data['video']['dataformat'];
		}
		if ( ! empty( $data['video']['encoder'] ) ) {
			$metadata['encoder'] = $data['video']['encoder'];
		}
		if ( ! empty( $data['video']['codec'] ) ) {
			$metadata['codec'] = $data['video']['codec'];
		}

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
	 *
	 * @return array|boolean Returns array of metadata, if found.
	 */
	function wp_read_audio_metadata( $file ) {
		if ( ! file_exists( $file ) ) {
			return false;
		}
		$metadata = array();

		if ( ! class_exists( 'getID3' ) ) {
			require( ABSPATH . WPINC . '/ID3/getid3.php' );
		}
		$id3  = new getID3();
		$data = $id3->analyze( $file );

		if ( ! empty( $data['audio'] ) ) {
			unset( $data['audio']['streams'] );
			$metadata = $data['audio'];
		}

		if ( ! empty( $data['fileformat'] ) ) {
			$metadata['fileformat'] = $data['fileformat'];
		}
		if ( ! empty( $data['filesize'] ) ) {
			$metadata['filesize'] = (int) $data['filesize'];
		}
		if ( ! empty( $data['mime_type'] ) ) {
			$metadata['mime_type'] = $data['mime_type'];
		}
		if ( ! empty( $data['playtime_seconds'] ) ) {
			$metadata['length'] = (int) ceil( $data['playtime_seconds'] );
		}
		if ( ! empty( $data['playtime_string'] ) ) {
			$metadata['length_formatted'] = $data['playtime_string'];
		}

		$this->wp_add_id3_tag_data( $metadata, $data );

		if(isset($metadata['image']['data']) && !empty($metadata['image']['data'])){
			$image = 'data:'.$metadata['image']['mime'].';charset=utf-8;base64,'.base64_encode($metadata['image']['data']);
			$metadata['image']['data'] = $image;
		}

		return $metadata;
	}


	/** Write the file
	 *
	 * @param string $file_tmp
	 * @param array $fileinfo
	 * @param string $content_type
	 * @param array $post_data
	 *
	 * @return array
	 */
	function gmedia_upload_handler( $file_tmp, $fileinfo, $content_type, $post_data ) {
		global $gmGallery;
		$cleanup_dir = true; // Remove old files
		$file_age    = 5 * 3600; // Temp file age in seconds
		$chunk       = (int) $this->_req( 'chunk', 0 );
		$chunks      = (int) $this->_req( 'chunks', 0 );

		// try to make grand-media dir if not exists
		if ( ! wp_mkdir_p( $fileinfo['dirpath'] ) ) {
			$return = array(
				"error" => array(
					"code"    => 100,
					"message" => sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?', 'gmLang' ), $fileinfo['dirpath'] )
				),
				"id"    => $fileinfo['basename']
			);

			return $return;
		}
		// Check if grand-media dir is writable
		if ( ! is_writable( $fileinfo['dirpath'] ) ) {
			@chmod( $fileinfo['dirpath'], 0755 );
			if ( ! is_writable( $fileinfo['dirpath'] ) ) {
				$return = array(
					"error" => array(
						"code"    => 100,
						"message" => sprintf( __( 'Directory %s or its subfolders are not writable by the server.', 'gmLang' ), dirname( $fileinfo['dirpath'] ) )
					),
					"id"    => $fileinfo['basename']
				);

				return $return;
			}
		}
		// Remove old temp files
		if ( $cleanup_dir && is_dir( $fileinfo['dirpath'] ) && ( $_dir = opendir( $fileinfo['dirpath'] ) ) ) {
			while ( ( $_file = readdir( $_dir ) ) !== false ) {
				$tmpfilePath = $fileinfo['dirpath'] . DIRECTORY_SEPARATOR . $_file;

				// Remove temp file if it is older than the max age and is not the current file
				if ( preg_match( '/\.part$/', $_file ) && ( filemtime( $tmpfilePath ) < time() - $file_age ) && ( $tmpfilePath != $fileinfo['filepath'] . '.part' ) ) {
					@unlink( $tmpfilePath );
				}
			}

			closedir( $_dir );
		} else {
			$return = array(
				"error" => array( "code" => 100, "message" => sprintf( __( 'Failed to open directory: %s', 'gmLang' ), $fileinfo['dirpath'] ) ),
				"id"    => $fileinfo['basename']
			);

			return $return;
		}

		// Open temp file
		$out = fopen( $fileinfo['filepath'] . '.part', $chunk == 0 ? "wb" : "ab" );
		if ( $out ) {
			// Read binary input stream and append it to temp file
			$in = fopen( $file_tmp, "rb" );

			if ( $in ) {
				while ( ( $buff = fread( $in, 4096 ) ) ) {
					fwrite( $out, $buff );
				}
			} else {
				$return = array( "error" => array( "code" => 101, "message" => __( "Failed to open input stream.", 'gmLang' ) ), "id" => $fileinfo['basename'] );

				return $return;
			}
			fclose( $in );
			fclose( $out );
			if ( strpos( $content_type, "multipart" ) !== false ) {
				@unlink( $file_tmp );
			}
			if ( ! $chunks || $chunk == ( $chunks - 1 ) ) {
				sleep( 1 );
				// Strip the temp .part suffix off
				rename( $fileinfo['filepath'] . '.part', $fileinfo['filepath'] );

				$this->file_chmod( $fileinfo['filepath'] );

				$size        = false;
				$is_webimage = false;
				if ( 'image' == $fileinfo['dirname'] ) {
					/** WordPress Image Administration API */
					require_once( ABSPATH . 'wp-admin/includes/image.php' );

					$size = @getimagesize( $fileinfo['filepath'] );
					if ( $size && file_is_displayable_image( $fileinfo['filepath'] ) ) {

						if ( function_exists( 'memory_get_usage' ) ) {
							$extensions = array( '1' => 'GIF', '2' => 'JPG', '3' => 'PNG', '6' => 'BMP' );
							switch ( $extensions[ $size[2] ] ) {
								case 'GIF':
									$CHANNEL = 1;
									break;
								case 'JPG':
									$CHANNEL = $size['channels'];
									break;
								case 'PNG':
									$CHANNEL = 3;
									break;
								case 'BMP':
								default:
									$CHANNEL = 6;
									break;
							}
							$MB                = 1048576;  // number of bytes in 1M
							$K64               = 65536;    // number of bytes in 64K
							$TWEAKFACTOR       = 1.8;     // Or whatever works for you
							$memoryNeeded      = round( ( $size[0] * $size[1] * $size['bits'] * $CHANNEL / 8 + $K64 ) * $TWEAKFACTOR );
							$memoryNeeded      = memory_get_usage() + $memoryNeeded;
							$current_limit     = @ini_get( 'memory_limit' );
							$current_limit_int = intval( $current_limit );
							if ( false !== strpos( $current_limit, 'M' ) ) {
								$current_limit_int *= $MB;
							}
							if ( false !== strpos( $current_limit, 'G' ) ) {
								$current_limit_int *= 1024;
							}

							if ( - 1 != $current_limit && $memoryNeeded > $current_limit_int ) {
								$newLimit = $current_limit_int / $MB + ceil( ( $memoryNeeded - $current_limit_int ) / $MB );
								@ini_set( 'memory_limit', $newLimit . 'M' );
							}
						}

						if ( ! wp_mkdir_p( $fileinfo['dirpath_thumb'] ) ) {
							$return = array(
								"error" => array(
									"code"    => 100,
									"message" => sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?', 'gmLang' ), $fileinfo['dirpath_thumb'] )
								),
								"id"    => $fileinfo['basename']
							);

							return $return;
						}
						if ( ! is_writable( $fileinfo['dirpath_thumb'] ) ) {
							@chmod( $fileinfo['dirpath_thumb'], 0755 );
							if ( ! is_writable( $fileinfo['dirpath_thumb'] ) ) {
								@unlink( $fileinfo['filepath'] );
								$return = array(
									"error" => array(
										"code"    => 100,
										"message" => sprintf( __( 'Directory %s is not writable by the server.', 'gmLang' ), $fileinfo['dirpath_thumb'] )
									),
									"id"    => $fileinfo['basename']
								);

								return $return;
							}
						}
						if ( ! wp_mkdir_p( $fileinfo['dirpath_original'] ) ) {
							$return = array(
								"error" => array(
									"code"    => 100,
									"message" => sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?', 'gmLang' ), $fileinfo['dirpath_original'] )
								),
								"id"    => $fileinfo['basename']
							);

							return $return;
						}
						if ( ! is_writable( $fileinfo['dirpath_original'] ) ) {
							@chmod( $fileinfo['dirpath_original'], 0755 );
							if ( ! is_writable( $fileinfo['dirpath_original'] ) ) {
								@unlink( $fileinfo['filepath'] );
								$return = array(
									"error" => array(
										"code"    => 100,
										"message" => sprintf( __( 'Directory %s is not writable by the server.', 'gmLang' ), $fileinfo['dirpath_original'] )
									),
									"id"    => $fileinfo['basename']
								);

								return $return;
							}
						}

						// Optimized image
						$webimg   = $gmGallery->options['image'];
						$thumbimg = $gmGallery->options['thumb'];

						$webimg['resize']   = ( ( $webimg['width'] < $size[0] ) || ( $webimg['height'] < $size[1] ) ) ? true : false;
						$thumbimg['resize'] = ( ( $thumbimg['width'] < $size[0] ) || ( $thumbimg['height'] < $size[1] ) ) ? true : false;

						if ( $webimg['resize'] ) {
							rename( $fileinfo['filepath'], $fileinfo['filepath_original'] );
						} else {
							copy( $fileinfo['filepath'], $fileinfo['filepath_original'] );
						}

						if ( $webimg['resize'] || $thumbimg['resize'] ) {
							$editor = wp_get_image_editor( $fileinfo['filepath_original'] );
							if ( is_wp_error( $editor ) ) {
								@unlink( $fileinfo['filepath_original'] );
								$return = array(
									"error" => array( "code" => $editor->get_error_code(), "message" => $editor->get_error_message() ),
									"id"    => $fileinfo['basename'],
									"tip"   => 'wp_get_image_editor'
								);

								return $return;
							}

							if ( $webimg['resize'] ) {
								$editor->set_quality( $webimg['quality'] );

								$resized = $editor->resize( $webimg['width'], $webimg['height'], $webimg['crop'] );
								if ( is_wp_error( $resized ) ) {
									@unlink( $fileinfo['filepath_original'] );
									$return = array(
										"error" => array( "code" => $resized->get_error_code(), "message" => $resized->get_error_message() ),
										"id"    => $fileinfo['basename'],
										"tip"   => "editor->resize->webimage({$webimg['width']}, {$webimg['height']}, {$webimg['crop']})"
									);

									return $return;
								}

								$saved = $editor->save( $fileinfo['filepath'] );
								if ( is_wp_error( $saved ) ) {
									@unlink( $fileinfo['filepath_original'] );
									$return = array(
										"error" => array( "code" => $saved->get_error_code(), "message" => $saved->get_error_message() ),
										"id"    => $fileinfo['basename'],
										"tip"   => 'editor->save->webimage'
									);

									return $return;
								}
							}

							// Thumbnail
							$editor->set_quality( $thumbimg['quality'] );

							$resized = $editor->resize( $thumbimg['width'], $thumbimg['height'], $thumbimg['crop'] );
							if ( is_wp_error( $resized ) ) {
								@unlink( $fileinfo['filepath'] );
								@unlink( $fileinfo['filepath_original'] );
								$return = array(
									"error" => array( "code" => $resized->get_error_code(), "message" => $resized->get_error_message() ),
									"id"    => $fileinfo['basename'],
									"tip"   => "editor->resize->thumb({$thumbimg['width']}, {$thumbimg['height']}, {$thumbimg['crop']})"
								);

								return $return;
							}

							$saved = $editor->save( $fileinfo['filepath_thumb'] );
							if ( is_wp_error( $saved ) ) {
								@unlink( $fileinfo['filepath'] );
								@unlink( $fileinfo['filepath_original'] );
								$return = array(
									"error" => array( "code" => $saved->get_error_code(), "message" => $saved->get_error_message() ),
									"id"    => $fileinfo['basename'],
									"tip"   => 'editor->save->thumb'
								);

								return $return;
							}
						} else {
							copy( $fileinfo['filepath'], $fileinfo['filepath_thumb'] );
						}
						$is_webimage = true;
					} else {
						@unlink( $fileinfo['filepath'] );
						$return = array(
							"error" => array( "code" => 104, "message" => __( "Could not read image size. Invalid image was deleted.", 'gmLang' ) ),
							"id"    => $fileinfo['basename']
						);

						return $return;
					}
				}

				global $gmDB;

				// Write media data to DB
				$title       = '';
				$description = '';
				$link        = '';
				if ( ! isset( $post_data['set_title'] ) ) {
					$post_data['set_title'] = 'filename';
				}
				if ( ! isset( $post_data['set_status'] ) ) {
					$post_data['set_status'] = isset( $post_data['status'] ) ? $post_data['status'] : 'inherit';
				}
				// TODO Option to set title empty string or from metadata or from filename or both
				// use image exif/iptc data for title and caption defaults if possible
				if ( $size ) {
					$image_meta = @wp_read_image_metadata( $fileinfo['filepath_original'] );
					if ( trim( $image_meta['caption'] ) ) {
						$description = $image_meta['caption'];
					}
					if ( 'exif' == $post_data['set_title'] ) {
						if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
							$title = $image_meta['title'];
						}
					}
				}
				if ( ( 'empty' != $post_data['set_title'] ) && empty( $title ) ) {
					$title = $fileinfo['title'];
				}

				$status = $post_data['set_status'];
				if ( 'inherit' == $post_data['set_status'] ) {
					$gmedia_album = isset( $post_data['terms']['gmedia_album'] ) ? $post_data['terms']['gmedia_album'] : false;
					if ( $gmedia_album && $this->is_digit( $gmedia_album ) ) {
						$album = $gmDB->get_term( $gmedia_album, 'gmedia_album' );
						if ( empty( $album ) || is_wp_error( $album ) ) {
							$status = 'public';
						} else {
							$status = $album->status;
						}
					} else {
						$status = 'public';
					}
				}

				unset( $post_data['gmuid'], $post_data['mime_type'], $post_data['set_title'], $post_data['set_status'] );
				if ( ! $is_webimage && isset( $post_data['terms']['gmedia_category'] ) ) {
					unset( $post_data['terms']['gmedia_category'] );
				}

				// Construct the media array
				$media_data = array(
					'mime_type'   => $fileinfo['mime_type'],
					'gmuid'       => $fileinfo['basename'],
					'title'       => $title,
					'link'        => $link,
					'description' => $description,
					'status'      => $status
				);
				$media_data = wp_parse_args( $post_data, $media_data );
				if ( ! current_user_can( 'gmedia_delete_others_media' ) ) {
					$media_data['author'] = get_current_user_id();
				}


				// Save the data
				$id = $gmDB->insert_gmedia( $media_data );
				$gmDB->update_metadata( $meta_type = 'gmedia', $id, $meta_key = '_metadata', $gmDB->generate_gmedia_metadata( $id, $fileinfo ) );

				$return = array(
					"success" => array( "code" => 200, "message" => sprintf( __( 'File uploaded successful. Assigned ID: %s', 'gmLang' ), $id ) ),
					"id"      => $fileinfo['basename']
				);

				return $return;
			} else {
				$return = array( "success" => array( "code" => 199, "message" => $chunk . '/' . $chunks ), "id" => $fileinfo['basename'] );

				return $return;
			}
		} else {
			$return = array( "error" => array( "code" => 102, "message" => __( "Failed to open output stream.", 'gmLang' ) ), "id" => $fileinfo['basename'] );

			return $return;
		}
	}

	/**
	 * @param string $service
	 * @param array $data
	 *
	 * @return array json
	 */
	function app_service($service, $data = array()){
		global $gmProcessor;

		if ( !current_user_can( 'manage_options') ) {
			die('-1');
		}
		if(!$service || !is_array($data)){
			die('0');
		}

		$result = array();
		$defaults = array('email' => '', 'category' => '');
		$data = array_merge($defaults, $data);

		$gm_options = get_option('gmediaOptions');

		$gm_options['site_email'] = $data['email'];
		$gm_options['site_category'] = $data['category'];

		if($service == 'app_deactivate'){
			$gm_options['mobile_app'] = 0;
		}

		if(in_array($service, array('app_activate','app_updateinfo')) && !is_email($data['email'])){
			$result['error'] = $gmProcessor->alert('danger', __('Enter valid email, please', 'gmLang'));
		} else {

			$hash = wp_generate_password('6', false);

			$data['service'] = $service;
			$data['title'] = get_bloginfo('name');
			$data['description'] = get_bloginfo('description');
			$data['url'] = home_url();
			$data['license'] = $gm_options['license_key'];
			$data['site_ID'] = $gm_options['site_ID'];
			$data['site_hash'] = $hash;

			set_transient($hash, $data, 45);

			$pgcpost = wp_remote_post( 'http://mypgc.co/?gmservice=' . $service, array(
				'method'  => 'POST',
				'timeout' => 45,
				'body'    => array( 'hash' => $hash, 'url' => $data['url'] ),
			) );

			if ( is_wp_error( $pgcpost ) ) {
				$result['error'] = $gmProcessor->alert( 'danger', $pgcpost->get_error_message() );
			}
			$pgcpost_body = wp_remote_retrieve_body($pgcpost);
			$result = (array) json_decode($pgcpost_body);
			if(isset($result['error'])){
				$result['error'] = $gmProcessor->alert( 'danger', $result['error'] );
			} else {
				if(isset($result['message'])){
					$result['message'] = $gmProcessor->alert( 'info', $result['message'] );
				}

				if(isset($result['site_ID'])){
					$gm_options['site_ID'] = $result['site_ID'];
				}
				if(isset($result['mobile_app'])){
					$gm_options['mobile_app'] = $result['mobile_app'];
				}
				if(isset($result['site_category'])){
					$gm_options['site_category'] = $result['site_category'];
				}
			}
		}
		update_option('gmediaOptions', $gm_options);

		return $result;
	}

	function i18n_exif_name($key) {
		$_key = strtolower($key);
		$tagnames = array(
			'aperture' 			=> __('Aperture','gmLang'),
			'credit' 			=> __('Credit','gmLang'),
			'camera' 			=> __('Camera','gmLang'),
			'caption' 			=> __('Caption','gmLang'),
			'created_timestamp' => __('Date/Time','gmLang'),
			'copyright' 		=> __('Copyright','gmLang'),
			'focal_length' 		=> __('Focal length','gmLang'),
			'iso' 				=> __('ISO','gmLang'),
			'shutter_speed' 	=> __('Shutter speed','gmLang'),
			'title' 			=> __('Title','gmLang'),
			'author' 			=> __('Author','gmLang'),
			'tags' 				=> __('Tags','gmLang'),
			'subject' 			=> __('Subject','gmLang'),
			'make' 				=> __('Make','gmLang'),
			'status' 			=> __('Edit Status','gmLang'),
			'category'			=> __('Category','gmLang'),
			'keywords' 			=> __('Keywords','gmLang'),
			'created_date' 		=> __('Date Created','gmLang'),
			'created_time'		=> __('Time Created','gmLang'),
			'position'			=> __('Author Position','gmLang'),
			'city'				=> __('City','gmLang'),
			'location'			=> __('Location','gmLang'),
			'state' 			=> __('Province/State','gmLang'),
			'country_code'		=> __('Country code','gmLang'),
			'country'			=> __('Country','gmLang'),
			'headline' 			=> __('Headline','gmLang'),
			'source'			=> __('Source','gmLang'),
			'contact'			=> __('Contact','gmLang'),
			'last_modfied'		=> __('Last modified','gmLang'),
			'tool'				=> __('Program tool','gmLang'),
			'format'			=> __('Format','gmLang'),
			'width'				=> __('Width','gmLang'),
			'height'			=> __('Height','gmLang'),
			'flash'				=> __('Flash','gmLang')
		);

		if (isset($tagnames[$_key])){
			$key = $tagnames[$_key];
		}

		return($key);
	}

	/** Get item Meta
	 *
	 * @param int|object $item
	 *
	 * @return array metadata[key] = array(name, value);
	 */
	function metadata_info($item){
		global $gmDB;

		if(is_object($item)){
			$item_id = $item->ID;
		} elseif($this->is_digit($item)){
			$item_id = (int) $item;
		} else{
			return null;
		}

		$metadata = array();

		$meta = $gmDB->get_metadata( 'gmedia', $item_id, '_metadata', true );
		if(isset($meta['image_meta'])){
			$metainfo = $meta['image_meta'];
		} else{
			$metainfo = $meta;
		}

		if(!empty($metainfo)){
			foreach($metainfo as $key => $value){
				if(empty($value)){
					continue;
				}
				if($key == 'aperture'){
					$value = 'F ' . $value;
				}
				if($key == 'focal_length'){
					$value = $value . ' mm';
				}
				if($key == 'shutter_speed'){
					$value = $value . ' sec';
				}
				if($key == 'created_timestamp'){
					$value = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $value);
				}

				$key_name = $this->i18n_exif_name($key);
				$key_name = ucwords(str_replace('_', ' ', $key_name));

				$metadata[$key] = array('name' => $key_name, 'value' => $value);
			}
		}

		return $metadata;
	}

	/** Get item Meta Text
	 *
	 * @param int $id
	 *
	 * @return string Meta text;
	 */
	function metadata_text($id){
		$metatext = '';
		if(($metadata = $this->metadata_info($id))){
			foreach($metadata as $meta){
				if($meta['name'] == 'Image'){
					continue;
				}
				if(!is_array($meta['value'])){
					$metatext .= "<b>{$meta['name']}:</b> {$meta['value']}\n";
				} else{
					$metatext .= "<b>{$meta['name']}:</b>\n";
					foreach($meta['value'] as $key => $value){
						$key_name = ucwords(str_replace('_', ' ', $key));
						$metatext .= " - <b>{$key_name}:</b> {$value}\n";
					}
				}
			}
		}

		return $metatext;
	}

}

global $gmCore;
$gmCore = new GmediaCore();
