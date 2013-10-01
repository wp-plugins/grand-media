<?php
/**
 * Main PHP class for the WordPress plugin GRAND Media
 *
 */
class grandCore {

	/**
	 * Check GET data
	 *
	 * @param string $var
	 * @param bool   $def
	 *
	 * @return mixed
	 */
	function _get( $var = '', $def = false ) {
		$r = $def;
		if ( isset( $_GET[$var] ) )
			$r = $_GET[$var];
		return $r;
	}

	/**
	 * Check POST data
	 *
	 * @param string $var
	 * @param bool   $def
	 *
	 * @return mixed
	 */

	function _post( $var = '', $def = false ) {
		$r = $def;
		if ( isset( $_POST[$var] ) )
			$r = $_POST[$var];
		return $r;
	}

	/**
	 * Check REQUEST data
	 *
	 * @param string $var
	 * @param bool   $def
	 *
	 * @return mixed
	 */
	function _req( $var = '', $def = false ) {
		$r = $def;
		if ( isset( $_REQUEST[$var] ) && ! empty( $_REQUEST[$var] ) )
			$r = $_REQUEST[$var];
		return $r;
	}

	/**
	 * qTip attributes
	 *
	 * @param      $tip
	 * @param bool $r
	 *
	 * @return string
	 */
	function qTip( $tip, $r = false ) {
		$showTip = '1';
		if ( $showTip ) {
			$toolTip = ' toolTip="' . $tip . '"';
			if ( $r ) {
				return $toolTip;
			}
			else {
				echo $toolTip;
			}
		}
		return false;
	}

	function get_admin_url() {
		unset( $_GET['doing_wp_cron'] );
		$url          = $_GET;
		$params       = http_build_query( $url );
		$url['page']  = admin_url( 'admin.php?page=' . $url['page'] );
		$url['query'] = remove_query_arg( array( 'doing_wp_cron', '_wpnonce' ), admin_url( 'admin.php?' . $params ) );
		return $url;
	}

	/**
	 * Show a system messages
	 *
	 * @param string $message
	 * @param bool|string $type
	 * @param bool   $close
	 *
	 * @return string
	 */
	function message( $message = '', $type = false, $close = true ) {
		$content = '';
		$close = $close ? '<i class="gm-close">' . __( 'Hide', 'gmLang' ) . '</i>' : '';
		$type = $type ? $type : 'info';
		if ( $message ) {
			$content .= '<div class="gm-message gm-' . $type . '"><span>' . stripslashes( $message ) . '</span>' . $close . '</div>';
		}
		if ( $count = grandCore::_get( 'deleted' ) ) {
			$message = sprintf( __( '%d media attachment(s) permanently deleted.' ), $count );
			$type    = 'info';
			$content .= '<div class="gm-message gm-' . $type . '"><span>' . $message . '</span>' . $close . '</div>';
		}
		return $content;
	}


	/**
	 * get_terms_hierarrhically
	 *
	 * @param string $taxonomy
	 * @param object $terms
	 * @param array  $children
	 * @param int    $start
	 * @param int    $per_page
	 * @param int    $count
	 * @param int    $parent
	 * @param int    $level
	 * @param bool   $filter
	 *
	 * @return array
	 */
	function get_terms_hierarrhically( $taxonomy, $terms, &$children, &$count, $start = 0, $per_page = 0, $parent = 0, $level = 0, $filter = false ) {
		global $gMDb;

		$end = $start + $per_page;
		if ( ! isset( $hierarr ) || empty( $hierarr ) && ! is_array( $hierarr ) )
			$hierarr = array();

		$output = '';
		foreach ( $terms as $key => $term ) {

			if ( ! empty( $per_page ) && $count >= $end )
				break;

			if ( $term->global != $parent && ! $filter )
				continue;

			// If the page starts in a subtree, print the parents.
			if ( $count == $start && $term->global > 0 && ! $filter ) {
				$my_parents = $parent_ids = array();
				$p          = $term->global;
				while ( $p ) {
					$my_parent    = $gMDb->get_term( $p, $taxonomy );
					$my_parents[] = $my_parent;
					$p            = $my_parent->global;
					if ( in_array( $p, $parent_ids ) ) // Prevent parent loops.
						break;
					$parent_ids[] = $p;
				}
				unset( $parent_ids );

				$num_parents = count( $my_parents );
				while ( $my_parent = array_pop( $my_parents ) ) {
					$my_parent->level             = $level - $num_parents;
					$hierarr[$my_parent->term_id] = $my_parent;
					$num_parents --;
				}
			}

			if ( $count >= $start ) {
				$term->level             = $level;
				$hierarr[$term->term_id] = $term;
			}

			++$count;

			unset( $terms[$key] );
			if ( isset( $children[$term->term_id] ) && ! $filter ) {
				$subarr  = $this->get_terms_hierarrhically( $taxonomy, $terms, $children, $count, $start, $per_page, $term->term_id, $level + 1 );
				$hierarr = $hierarr + $subarr;
			}
		}

		return $hierarr;

	}

	function is_crawler( $userAgent ) {
		$crawlers  = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|FeedBurner|' .
				'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|' .
				'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby|yandex|facebook';
		$isCrawler = ( preg_match( "/$crawlers/i", $userAgent ) > 0 );
		return $isCrawler;
	}

	function is_browser( $userAgent ) {
		$browsers  = 'opera|aol|msie|firefox|chrome|konqueror|safari|netscape|navigator|mosaic|lynx|amaya|omniweb|avant|camino|flock|seamonkey|mozilla|gecko';
		$isBrowser = ( preg_match( "/$browsers/i", $userAgent ) > 0 );
		return $isBrowser;
	}

	/**
	 * Return relative path to an uploaded file.
	 *
	 * The path is relative to the gMedia upload dir.
	 *
	 * @see  _wp_relative_upload_path()
	 * @uses apply_filters() Calls '_gm_relative_upload_path' on file path.
	 *
	 * @param string $path Full path to the file
	 *
	 * @return string relative path on success, unchanged path on failure.
	 */
	function _gm_relative_upload_path( $path ) {
		$new_path = $path;

		if ( ( $uploads = $this->gm_upload_dir() ) && false === $uploads['error'] ) {
			if ( 0 === strpos( $new_path, $uploads['path'] ) ) {
				$new_path = str_replace( $uploads['path'], '', $new_path );
				$new_path = ltrim( $new_path, '/' );
			}
		}

		return apply_filters( '_gm_relative_upload_path', $new_path, $path );
	}

	/**
	 * Get an array containing the gMedia upload directory's path and url.
	 *
	 * If the path couldn't be created, then an error will be returned with the key
	 * 'error' containing the error message. The error suggests that the parent
	 * directory is not writable by the server.
	 *
	 * On success, the returned array will have many indices:
	 * 'path' - base directory and sub directory or full path to upload directory.
	 * 'url' - base url and sub directory or absolute URL to upload directory.
	 * 'error' - set to false.
	 *
	 * @see  wp_upload_dir()
	 * @uses apply_filters() Calls 'gm_upload_dir' on returned array.
	 *
	 * @return array See above for description.
	 */
	function gm_upload_dir() {
		$slash = '/';
		// If multisite (and if not the main site)
		if ( is_multisite() && ! is_main_site() ) {
			$slash = '/blogs.dir/' . get_current_blog_id() . '/';
		}

		$dir     = WP_CONTENT_DIR . $slash . GRAND_FOLDER . '/';
		$url     = WP_CONTENT_URL . $slash . GRAND_FOLDER . '/';

		$uploads = apply_filters( 'gm_upload_dir', array( 'path' => $dir, 'url' => $url, 'error' => false ) );

		// Make sure we have an uploads dir
		if ( ! wp_mkdir_p( $uploads['path'] ) ) {
			$message = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $uploads['path'] );
			$uploads['error'] = $message;
		}
		return $uploads;
	}

	function delete_folder( $path ) {
		$path = rtrim( $path, '/' );
		return is_file( $path ) ? @unlink( $path ) : array_map( array($this, 'delete_folder'), glob( $path . '/*' ) ) == @rmdir( $path );
	}

	function maybe_array_0( $arr ) {
		if ( is_array( $arr ) )
			$arr = $arr[0];
		return $arr;
	}

	function gm_get_module_settings( $module_folder ) {
		$module_settings = array();
		$module_dir      = $this->get_module_path( $module_folder );
		if ( is_dir( $module_dir['path'] ) ) {
			$module_ot = array();
			include( $module_dir['path'] . '/settings.php' );

			$module_ot = apply_filters('gm_get_module_settings', $module_ot);

			foreach ( $module_ot['settings'] as $key => $section ) {

				//if($key == 'general_default')
				//continue;

				/* loop through meta box fields */
				foreach ( $section['fields'] as $field ) {
					if ( in_array( $field['type'], array( 'textblock', 'textblock_titled' ) ) )
						continue;
					if ( in_array( $field['type'], array( 'checkbox' ) ) && empty( $field['std'] ) )
						$module_settings[$field['id']] = array();
					else
						$module_settings[$field['id']] = $field['std'];
				}
			}
		}
		return $module_settings;
	}

	function metabox() {
		include_once( dirname( __FILE__ ) . '/post-metabox.php' );
	}

	/**
	 * Get an HTML img element representing an image attachment
	 *
	 * @see  add_image_size()
	 * @see  wp_get_attachment_image()
	 * @uses apply_filters() Calls 'gm_get_attachment_image_attributes' hook on attributes array
	 *
	 * @param object $item Image attachment ID.
	 * @param string $size Optional, default is empty string, could be 'thumb' (size from gmediaOptions)
	 *                     or array($width, $height). If empty string, get size from meta.
	 * @param array  $attr Optional, additional attributes.
	 * @param string $return Set 'src' to return only image url
	 *
	 * @return string HTML img element or empty string on failure.
	 */
	function gm_get_media_image( $item, $size = '', $attr = array(), $return = '' ) {
		global $gMDb, $grandCore;

		$html      = '';
		$gmOptions = get_option( 'gmediaOptions' );
		$type      = explode( '/', $item->mime_type );
		if ( $type[0] == 'image' ) {
			$uploads     = $grandCore->gm_upload_dir();
			$uploads_url = $uploads['url'] . $gmOptions['folder']['link'] . '/';
			if ( is_array( $size ) ) {
				list( $width, $height ) = $size;
				$size = '-' . join( 'x', $size );
			}
			else if ( $size == 'thumb' ) {
				$size = '-thumb';
				list( $width, $height ) = explode( 'x', $gmOptions['thumbnail_size'] );
			}
			else {
				$size        = '';
				$meta        = $gMDb->get_metadata( $meta_type = 'gmedia', $item->ID, $meta_key = '_gm_media_metadata', true );
				$width       = $meta['width'];
				$height      = $meta['height'];
				$uploads_url = $uploads['url'] . $gmOptions['folder']['image'] . '/';
			}
			$ext  = strrchr( $item->gmuid, '.' );
			$file = substr( $item->gmuid, 0, strrpos( $item->gmuid, $ext ) );
			$src  = $uploads_url . $file . $size . $ext;
			$alt  = trim( esc_attr( strip_tags( $item->title ) ) );
			$type = $type[0];
		}
		else {
			$size = '-thumb';
			list( $width, $height ) = explode( 'x', $gmOptions['thumbnail_size'] );
			$ext = ltrim( strrchr( $item->gmuid, '.' ), '.' );
			if ( ! $type = wp_ext2type( $ext ) )
				$type = 'application';
			$src = plugins_url( GRAND_FOLDER ) . '/admin/images/' . $type . '.png';
			$alt = 'icon';
			if(!isset($attr['data-icon'])){
				$preview_id  = $gMDb->get_metadata( 'gmedia', $item->ID, 'preview', true );
				if(!empty($preview_id)){
					$preview_item = $gMDb->get_gmedia( intval($preview_id) );
					if(!empty($preview_item)){
						$preview_src = $grandCore->gm_get_media_image( $preview_item, 'thumb', array(), 'src' );
						$attr['src'] = $preview_src;
						$attr['data-icon'] = $src;
					}
				}
			}
		}
		$default_attr = array(
			'id'     => 'gm_' . $item->ID,
			'src'    => $src,
			'class'  => "gmedia{$size} $type",
			'alt'    => $alt, // Use Alt field first
			'title'  => trim( esc_attr( strip_tags( $item->title ) ) ),
			'width'  => $width,
			'height' => $height
		);

		$attr = wp_parse_args( $attr, $default_attr );
		$attr = apply_filters( 'gm_get_media_image_attributes', $attr, $item );
		$attr = array_map( 'esc_attr', $attr );

		if('src' === $return) {
			return $attr['src'];
		}

		//$hwstring = image_hwstring( $width, $height );

		$html = "<img";
		foreach ( $attr as $name => $value ) {
			if(empty($value)){ continue; }
			$html .= " $name=" . '"' . $value . '"';
		}
		$html .= ' />';

		return $html;
	}

	/**
	 * Get path and url to module folder
	 *
	 * @param string $module_name
	 *
	 * @return array|bool Return array( 'path', 'url' ) OR false if no module
	 */
	function get_module_path( $module_name ) {
		$gmOptions   = get_option( 'gmediaOptions' );
		$upload      = $this->gm_upload_dir();
		$module_dir  = array(
			'path' => array(
				$upload['path'] . $gmOptions['folder']['module'] . '/' . $module_name,
				GRAND_ABSPATH . 'module/' . $module_name
			),
			'url'  => array(
				$upload['url'] . $gmOptions['folder']['module'] . '/' . $module_name,
				plugins_url( GRAND_FOLDER ) . '/module/' . $module_name
			)
		);
		$check_paths = array_filter( $module_dir['path'], 'is_dir' );
		if ( empty( $check_paths ) ) {
			return false;
		}

		$module_dir_key = $this->maybe_array_0( array_keys( $check_paths ) );

		return array( 'path' => $module_dir['path'][$module_dir_key], 'url' => $module_dir['url'][$module_dir_key] );
	}

	/**
	 * Generate resized image on the fly if it not exists
	 *
	 * @param array	$args
	 * @param bool	$crunch
	 *
	 * @internal int    $id
	 * @internal string $file
	 * @internal int    $max_w
	 * @internal int    $max_h
	 * @internal int    $crop Default: 0
	 * @internal int    $quality Default: 90
	 * @internal bool   $suffix Default: false
	 *
	 * @return array Return file name
	 */
	function linked_img( $args, $crunch = true ) {
		global $gMDb;
		/**
		 * @var $id int
		 * @var $file string
		 * @var $width int
		 * @var $height int
		 * @var $max_w int
		 * @var $max_h int
		 * @var $crop int
		 * @var $quality int
		 * @var $suffix string
		 */
		$args = wp_parse_args($args, array(
			'id' => 0,
			'file' => '',
			'suffix' => '',
			'max_w' => 0,
			'max_h' => 0,
			'crop' => 0,
			'quality' => 90,
			'width' => 0,
			'height' => 0,
		));
		extract($args);
		if(empty($file)){
			return array( 'file' => 'NaN', 'error' => '$file empty' );
		}
		$gmOptions   = get_option( 'gmediaOptions' );
		$upload      = $this->gm_upload_dir();

		if(empty($width) || empty($height) ){
			$suffix = 'thumb';
		}

		$ext   = strrchr( $file, '.' );
		$filename = substr( $file, 0, strrpos( $file, $ext ) );
		$file_path = $upload['path'] . $gmOptions['folder']['image'] . '/' . $file;
		$file_url = $upload['url'] . $gmOptions['folder']['image'] . '/' . $file;

		if('thumb' != $suffix){
			if((!$crop || !$max_w || !$max_h) && ($crop|$max_w|$max_h) ){
				list($max_w, $max_h) = wp_constrain_dimensions($width, $height, $max_w, $max_h);
				$crop = 1;
			}
			if($crop && ($max_w.'x'.$max_h == $gmOptions['thumbnail_size'])) {
				$suffix = 'thumb';
			}
		}

		if(empty($suffix)) {
			$suffix = $max_w . 'x' . $max_h;
		}

		if( ($max_w.'x'.$max_h) === ($width.'x'.$height) ){
			$link_path = $file_path;
			$link_url = $file_url;
		} else {
			$link_path = $upload['path'] . $gmOptions['folder']['link'] . '/' . $filename . '-' . $suffix . $ext;
			$link_url = $upload['url'] . $gmOptions['folder']['link'] . '/' . $filename . '-' . $suffix . $ext;
		}

		if ( !file_exists($link_path) ) {
			$thumb = $filename . '-thumb' . $ext;
			if( !$crunch ) {
				$args['max_h'] = $max_h;
				$args['max_w'] = $max_w;
				$args['crop']  = $crop;
				return array( 'file' => $filename . '-' . $suffix . $ext, 'crunch' => $args );
			}
			$dest_path = $upload['path'] . $gmOptions['folder']['link'];
			if( function_exists('wp_get_image_editor') ) {
				$editor = wp_get_image_editor( $file_path );
				if ( is_wp_error( $editor ) )
					return array( 'file' => $thumb, 'error' => 'image_editor: '.$editor->get_error_message() );
				$editor->set_quality( $quality );

				$resized = $editor->resize( $max_w, $max_h, $crop );
				if ( is_wp_error( $resized ) )
					return array( 'file' => $thumb, 'error' => 'resize: '.$resized->get_error_message() );

				$dest_file = $editor->generate_filename( $suffix, $dest_path );
				$saved = $editor->save( $dest_file );

				if ( is_wp_error( $saved ) )
					return array( 'file' => $thumb, 'error' => 'save: '.$saved->get_error_message() );
			}
			else {
				$new_file = image_resize( $file_path, $max_w, $max_h, $crop, $suffix, $dest_path, $quality );
				if ( is_wp_error( $new_file ) )
					return array( 'file' => $thumb, 'error' => 'image_resize: '.$new_file->get_error_message() );
			}

			if($id && $args['suffix']) {
				$_metadata = $gMDb->get_metadata('gmedia', $id, '_metadata', true);
				$_metadata['sizes'][$suffix] = array( 'width' => $args['max_w'], 'height' => $args['max_h'], 'crop' => $args['crop'] );
				$gMDb->update_metadata( 'gmedia', $id, '_metadata', $_metadata );
			}
		}

		return array( 'file' => $filename . '-' . $suffix . $ext, 'url' => $link_url, 'original' => $file_url );
	}

	/** Automatic choose upload directory based on media type
	 *
	 * @param string $fileName
	 *
	 * @return array
	 */
	function target_dir( $fileName ) {
		/** @var $wpdb wpdb */
		global $wpdb;

		$fileName = basename($fileName);
		$result = mysql_query( "SHOW TABLE STATUS LIKE '{$wpdb->prefix}gmedia'" );
		$row    = mysql_fetch_array( $result );
		$nextID = $row['Auto_increment'];
		mysql_free_result( $result );

		$ext           = strtolower(strrchr( $fileName, '.' ));
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
	function file_chmod( $new_file ) {
		$stat  = stat( dirname( $new_file ) );
		$perms = $stat['mode'] & 0000666;
		@ chmod( $new_file, $perms );
	}

	/** Import folder
	 *
	 * @param string $source_file
	 * @param array  $file_data
	 * @param bool $delete_source
	 *
	 * @return mixed json data
	 */
	function import( $source_file, $file_data = array(), $delete_source = false ) {
		global $gMDb;
		$gmOptions        = get_option( 'gmediaOptions' );
		$uploads          = $this->gm_upload_dir();
		$source_file			= urldecode($source_file);
		$target_file			= $this->target_dir($source_file);
		$target_dir       = $uploads['path'] . $gmOptions['folder'][$target_file['folder']];
		$target_dir_url   = $uploads['url'] . $gmOptions['folder'][$target_file['folder']];

		// try to make grand-media dir if not exists
		if ( ! wp_mkdir_p( $target_dir ) ) {
			$return = array( "error" => array( "code" => 100, "message" => sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?', 'gmLang' ), $target_dir ) ), "id" => $target_file['name'] );
			return $return;
		}
		// Check if grand-media dir is writable
		if ( ! is_writable( $target_dir ) ) {
			@chmod( $target_dir, 0755 );
			if ( ! is_writable( $target_dir ) ) {
				$return = array( "error" => array( "code" => 100, "message" => sprintf( __( 'Directory %s or its subfolders are not writable by the server.', 'gmLang' ), $target_dir ) ), "id" => $target_file['realname'] );
				return $return;
			}
		}

		$url  = $target_dir_url . '/' . $target_file['name'];
		$file = $target_dir . '/' . $target_file['name'];

		if( copy($source_file, $file) ) {

			$this->file_chmod( $file );

			$size = false;
			if ( basename( $target_dir ) == 'image' ) {
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
							$return = array( "error" => array( "code" => 100, "message" => sprintf( __( 'Directory %s is not writable by the server.', 'gmLang' ), $uploads['path'].$gmOptions['folder']['link'] ) ), "id" => $target_file['realname'] );
							return $return;
						}
					}
					if( function_exists('wp_get_image_editor') ) {
						$editor = wp_get_image_editor( $file );
						if ( is_wp_error( $editor ) ){
							@unlink( $file );
							$return = array( "error" => array( "code" => $editor->get_error_code(), "message" => $editor->get_error_message() ) , "id" => $target_file['name'] );
							die( $return );
						}
						$editor->set_quality( $quality );

						$resized = $editor->resize( $max_w, $max_h, $crop );
						if ( is_wp_error( $resized ) ){
							@unlink( $file );
							$return = array( "error" => array( "code" => $resized->get_error_code(), "message" => $resized->get_error_message() ) , "id" => $target_file['name'] );
							return $return;
						}

						$dest_file = $editor->generate_filename( $suffix, $dest_path );
						$saved = $editor->save( $dest_file );

						if ( is_wp_error( $saved ) ){
							@unlink( $file );
							$return = array( "error" => array( "code" => $saved->get_error_code(), "message" => $saved->get_error_message() ) , "id" => $target_file['name'] );
							return $return;
						}
					}
					else {
						$new_file = image_resize( $file, $max_w, $max_h, $crop, $suffix, $dest_path, $quality );
						if ( is_wp_error( $new_file ) ) {
							@unlink( $file );
							$return = array( "error" => array( "code" => $new_file->get_error_code(), "message" => $new_file->get_error_message() ) , "id" => $target_file['name'] );
							return $return;
						}
					}
				}
				else {
					@unlink( $file );
					$return = array( "error" => array( "code" => 104, "message" => __( "Could not read image size. Invalid image was deleted.", 'gmLang' ) ), "id" => $target_file['realname'] );
					return $return;
				}
			}

			// Write gmedia data to DB
			$content = '';
			// TODO Option to set title empty string or from metadata or from filename or both
			$title = $target_file['title'];
			// use image exif/iptc data for title and caption defaults if possible
			if ( $size ) {
				$image_meta = @wp_read_image_metadata( $file );
				if ( trim( $image_meta['caption'] ) )
					$content = $image_meta['caption'];
				if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
					$title = $image_meta['title'];
			}

			// Construct the media array
			$media_data = array(
				'mime_type'   => $target_file['type'],
				'gmuid'       => $target_file['name'],
				'title'       => $title,
				'description' => $content
			);
			if(!isset($file_data['terms']['gmedia_category'])){
				$category = ucwords(str_replace(array('_','-'), ' ', basename(dirname($source_file))));
				$file_data['terms']['gmedia_category'] = $category;
			}
			$media_data = wp_parse_args( $file_data, $media_data );

			// Save the data
			$id = $gMDb->insert_gmedia( $media_data );
			$gMDb->update_metadata( $meta_type = 'gmedia', $id, $meta_key = '_metadata', $gMDb->generate_gmedia_metadata( $id, $file ) );

			if($delete_source) {
				@unlink($source_file);
			}

			$return = array( "success" => array( "code" => 200, "message" => sprintf( __( 'File imported successful. Assigned ID: %s', 'gmLang' ), $id ) ), "id" => $target_file['realname'] );
			return $return;
		} else {
			$return = array( "error" => array( "code" => 105, "message" => __( 'Could not copy file.', 'gmLang' ) ), "id" => $target_file['realname'] );
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
			/*'@<[\/\!]*?[^<>]*?>@si',          // Strip out HTML tags */
			'@<script[^>]*?>.*?</script>@si',   // Strip out javascript
			'@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
			'@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments
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
		if (is_array($input)) {
			foreach($input as $var=>$val) {
				$output[$var] = $this->sanitize($val);
			}
		}
		else {
			if (get_magic_quotes_gpc()) {
				$input = stripslashes($input);
			}
			$input  = $this->clean_input($input);
			$output = mysql_real_escape_string($input);
		}
		return $output;
	}

}

global $grandCore;
$grandCore = new grandCore();
