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
	function gQTip( $tip, $r = false ) {
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

	function getAdminURL() {
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
	 * @param string $type
	 *
	 * @return string
	 */
	function message( $message = '', $type = 'info' ) {
		$content = '';
		if ( $message ) {
			$content .= '<div class="gm-message gm-' . $type . '"><span>' . stripslashes( $message ) . '</span><i class="gm-close">' . __( 'Hide', 'gmLang' ) . '</i></div>';
		}
		if ( $count = grandCore::_get( 'deleted' ) ) {
			$message = sprintf( __( '%d media attachment(s) permanently deleted.' ), $count );
			$type    = 'info';
			$content .= '<div class="gm-message gm-' . $type . '"><span>' . $message . '</span><i class="gm-close">' . __( 'Hide', 'gmLang' ) . '</i></div>';
		}
		return $content;
	}


	/**
	 * gmGetTermsHierarr
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
	function gmGetTermsHierarr( $taxonomy, $terms, &$children, &$count, $start = 0, $per_page = 0, $parent = 0, $level = 0, $filter = false ) {
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
					$my_parent    = $gMDb->gmGetTerm( $p, $taxonomy );
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
				$subarr  = $this->gmGetTermsHierarr( $taxonomy, $terms, $children, $count, $start, $per_page, $term->term_id, $level + 1 );
				$hierarr = $hierarr + $subarr;
			}
		}

		return $hierarr;

	}

	function isCrawler( $userAgent ) {
		$crawlers  = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|FeedBurner|' .
				'AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|' .
				'GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby|yandex|facebook';
		$isCrawler = ( preg_match( "/$crawlers/i", $userAgent ) > 0 );
		return $isCrawler;
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
		$siteurl = get_option( 'siteurl' );
		$dir     = WP_CONTENT_DIR . '/' . GRAND_FOLDER . '/';
		$url     = WP_CONTENT_URL . '/' . GRAND_FOLDER . '/';

		$uploads = apply_filters( 'gm_upload_dir', array( 'path' => $dir, 'url' => $url, 'error' => false ) );

		// Make sure we have an uploads dir
		if ( ! wp_mkdir_p( $uploads['path'] ) ) {
			$message = sprintf( __( 'Unable to create directory %s. Is its parent directory writable by the server?' ), $uploads['path'] );
			return array( 'error' => $message );
		}
		return $uploads;
	}

	function gm_delete_folder( $path ) {
		$path = rtrim( $path, '/' );
		return is_file( $path ) ? @unlink( $path ) : array_map( 'grandCore::gm_delete_folder', glob( $path . '/*' ) ) == @rmdir( $path );
	}

	function gm_arr_o( $arr ) {
		if ( is_array( $arr ) )
			$arr = $arr[0];
		return $arr;
	}

	function gm_get_module_settings( $module_folder ) {
		$module_settings = array();
		$module_dir      = $this->gm_get_module_path( $module_folder );
		if ( is_dir( $module_dir['path'] ) ) {
			$module_ot = array();
			include( $module_dir['path'] . '/settings.php' );
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

	function gMedia_MetaBox() {
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
	 * @param bool   $noid Optional, add or skip image id attribute.
	 *
	 * @return string HTML img element or empty string on failure.
	 */
	function gmGetMediaImage( $item, $size = '', $attr = array(), $noid = false ) {
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
				$meta        = $gMDb->gmGetMetaData( $meta_type = 'gmedia', $item->ID, $meta_key = '_gm_media_metadata', true );
				$width       = $meta['width'];
				$height      = $meta['height'];
				$uploads_url = $uploads['url'] . $gmOptions['folder']['image'] . '/';
			}
			$ext  = strrchr( $item->gmuid, '.' );
			$file = substr( $item->gmuid, 0, strrpos( $item->gmuid, $ext ) );
			$src  = $uploads_url . $file . $size . $ext;
			$alt  = trim( esc_attr( strip_tags( $item->title ) ) );
		}
		else {
			$size = '-thumb';
			list( $width, $height ) = explode( 'x', $gmOptions['thumbnail_size'] );
			$ext = ltrim( strrchr( $item->gmuid, '.' ), '.' );
			if ( ! $type = wp_ext2type( $ext ) )
				$type = 'application';
			$src = plugins_url( GRAND_FOLDER ) . '/admin/images/' . $type . '.png';
			$alt = 'icon';
		}
		$default_attr = array(
			'id'     => 'gm_' . $item->ID,
			'src'    => $src,
			'class'  => "gmedia{$size}",
			'alt'    => $alt, // Use Alt field first
			'title'  => trim( esc_attr( strip_tags( $item->title ) ) ),
			'width'  => $width,
			'height' => $height
		);

		$attr = wp_parse_args( $attr, $default_attr );
		$attr = apply_filters( 'gm_get_attachment_image_attributes', $attr, $item );
		$attr = array_map( 'esc_attr', $attr );

		$hwstring = image_hwstring( $width, $height );

		$html = "<img";
		foreach ( $attr as $name => $value ) {
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
	function gm_get_module_path( $module_name ) {
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

		$module_dir_key = $this->gm_arr_o( array_keys( $check_paths ) );

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
			'crop' => 0,
			'quality' => 90,
			'suffix' => ''
		));
		extract($args);
		$gmOptions   = get_option( 'gmediaOptions' );
		$upload      = $this->gm_upload_dir();

		if(!$crop || !$max_w || !$max_h){
			list($max_w, $max_h) = wp_constrain_dimensions($width, $height, $max_w, $max_h);
			$crop = 1;
		}
		if($crop && ($max_w.'x'.$max_h == $gmOptions['thumbnail_size']) && !$suffix) {
			$suffix = 'thumb';
		}
		if(!$suffix) {
			$suffix = $max_w . 'x' . $max_h;
		}

		$ext   = strrchr( $file, '.' );
		$filename = substr( $file, 0, strrpos( $file, $ext ) );
		$thumb = $filename . '-thumb' . $ext;
		$file_path = $upload['path'] . $gmOptions['folder']['image'] . '/' . $file;
		$link_path = $upload['path'] . $gmOptions['folder']['link'] . '/' . $filename . '-' . $suffix . $ext;
		$file_url = $upload['url'] . $gmOptions['folder']['image'] . '/' . $file;
		$link_url = $upload['url'] . $gmOptions['folder']['link'] . '/' . $filename . '-' . $suffix . $ext;

		if ( !file_exists($link_path) ) {
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
				$_metadata = $gMDb->gmGetMetaData('gmedia', $id, '_metadata', true);
				$_metadata['sizes'][$suffix] = array( 'width' => $args['max_w'], 'height' => $args['max_h'], 'crop' => $args['crop'] );
				$gMDb->gmUpdateMetaData( 'gmedia', $id, '_metadata', $_metadata );
			}
		}

		return array( 'file' => $filename . '-' . $suffix . $ext, 'url' => $link_url, 'original' => $file_url );
	}


}

global $grandCore;
$grandCore = new grandCore();
