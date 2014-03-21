<?php
add_action( 'wp_ajax_gmedia_update_data', 'gmedia_update_data' );
function gmedia_update_data(){
	global $gmDB, $gmCore, $gmGallery;
	check_ajax_referer( "GmediaGallery" );
	if ( ! current_user_can( 'edit_posts' ) )
		die( '-1' );

	$data = $gmCore->_post('data');

	wp_parse_str($data, $gmedia);

	if ( ! empty( $gmedia['ID'] ) ) {
		$item = $gmDB->get_gmedia( $gmedia['ID'] );

		$gmedia['modified'] = current_time( 'mysql' );
		$gmedia['mime_type'] = $item->mime_type;
		$gmedia['gmuid'] = $item->gmuid;

		$gmuid = pathinfo($item->gmuid);

		$gmedia['filename'] = preg_replace( '/[^a-z0-9_\.-]+/i', '_', $gmedia['filename'] );
		if($gmedia['filename'] != $gmuid['filename']){
			$fileinfo = $gmCore->fileinfo($gmedia['filename'].'.'.$gmuid['extension']);
			if ( 'image' == $fileinfo['dirname'] && file_is_displayable_image( $fileinfo['dirpath'].'/'.$item->gmuid ) ) {
				@rename($fileinfo['dirpath_original'].'/'.$item->gmuid, $fileinfo['filepath_original']);
				@rename($fileinfo['dirpath_thumb'].'/'.$item->gmuid, $fileinfo['filepath_thumb']);
			}
			if(@rename($fileinfo['dirpath'].'/'.$item->gmuid, $fileinfo['filepath'])){
				$gmedia['gmuid'] = $fileinfo['basename'];
			}
		}

		$id = $gmDB->insert_gmedia( $gmedia );
		if ( ! is_wp_error( $id ) ) {
			// Meta Stuff
			if ( isset($gmedia['meta']) && is_array($gmedia['meta']) ) {
				foreach ( $gmedia['meta'] as $key => $value ) {
					$gmDB->update_metadata( 'gmedia', $id, $key, $value );
				}
			}
			$result = $gmDB->get_gmedia( $id );
			//$result = array( 'stat' => 'OK', 'message' => $gmCore->message( sprintf( __( 'gmedia #%s updated successfully', 'gmLang' ), $id ), 'info' ), 'content' => $tr );
		}
		else {
			$result = $gmDB->get_gmedia( $id );
			//$result = array( 'stat' => 'KO', 'message' => $gmCore->message( sprintf( __( "Can't update gmedia #%s", 'gmLang' ) . '. ' . __( 'Contact plugin author to solve this problem. Describe your problem and give temporary access to Wordpress Dashboard and to FTP plugins folder.' ) . ' (<a href="mailto:gmediafolder+support@gmail.com?subject=Gmedia Support" target="_blank">Gmedia Support</a>)', $gmID ), 'error' ), 'error' => $id );
		}
		//header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
		echo json_encode( array($gmedia, $result) );
	}

	wp_die();
}

add_action( 'wp_ajax_gmedia_terms_modal', 'gmedia_terms_modal' );
function gmedia_terms_modal(){
	global $gmDB, $gmCore, $gmGallery;
	check_ajax_referer( "GmediaGallery" );
	if ( ! current_user_can( 'edit_posts' ) )
		die( '-1' );

	$button_class = 'btn-primary';
	$modal = $gmCore->_post('modal');
	switch ( $modal ) {
		case 'filter_categories':
			$gm_terms = $gmDB->get_terms( 'gmedia_category' );
			$modal_title = __( 'Show Images from Categories', 'gmLang' );
			$modal_content = '<div class="checkbox"><label><input type="checkbox" name="cat[]" value="0"> ' . __( 'Uncategorized', 'gmLang' ) . '</label></div>';
			$modal_button = __( 'Show Selected', 'gmLang' );
			if ( count( $gm_terms ) ) {
				foreach ($gm_terms as $term ) {
					if($term->count)
						$modal_content .= '<div class="checkbox"><label><input type="checkbox" name="cat[]" value="' . $term->term_id . '"> ' . $term->name . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
				}
			}
			break;
		case 'assign_category':
			$term_type = 'gmedia_category';
			$gm_terms = $gmGallery->options['taxonomies'][$term_type];
			$modal_title = __('Assign Category for Selected Images', 'gmLang');
			$modal_content = '<div class="radio"><label><input type="radio" name="cat" value="0"> ' . __('Uncategorized', 'gmLang') . '</label></div>';
			$modal_button = __('Assign Category', 'gmLang');
			if ( count( $gm_terms ) ) {
				foreach ($gm_terms as $term_name => $term_title ) {
					$modal_content .= '<div class="radio"><label><input type="radio" name="cat" value="' . $term_name . '"> ' . $term_title . '</label></div>';
				}
			}
			break;
		case 'filter_albums':
			$gm_terms = $gmDB->get_terms( 'gmedia_album' );
			$modal_title = __( 'Filter Albums', 'gmLang' );
			$modal_content = '<div class="checkbox"><label><input type="checkbox" name="alb[]" value="0"> ' . __( 'No Album', 'gmLang' ) . '</label></div>';
			$modal_button = __( 'Show Selected', 'gmLang' );
			if ( count( $gm_terms ) ) {
				foreach ($gm_terms as $term ) {
					$modal_content .= '<div class="checkbox"><label><input type="checkbox" name="alb[]" value="' . $term->term_id . '"> ' . $term->name . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
				}
			} else {
				$modal_button = false;
			}
			break;
		case 'assign_album':
			$gm_terms = $gmDB->get_terms( 'gmedia_album' );
			$modal_title = __( 'Assign Album for Selected Items', 'gmLang' );
			$modal_content = '<div class="radio"><label><input type="radio" name="alb" value="0"> ' . __( 'No Album', 'gmLang' ) . '</label></div>';
			$modal_button = __( 'Assign Album', 'gmLang' );
			if ( count( $gm_terms ) ) {
				foreach ($gm_terms as $term ) {
					$modal_content .= '<div class="radio"><label><input type="radio" name="alb" value="' . $term->term_id . '"> ' . $term->name . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
				}
			}
			break;
		case 'filter_tags':
			$gm_terms = $gmDB->get_terms( 'gmedia_tag' );
			$modal_title = __( 'Filter by Tags', 'gmLang' );
			$modal_content = '';
			$modal_button = __( 'Show Selected', 'gmLang' );
			if ( count( $gm_terms ) ) {
				foreach ( $gm_terms as $term ) {
					$modal_content .= '<div class="checkbox"><label><input type="checkbox" name="tag_id[]" value="' . $term->term_id . '"> ' . $term->name . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
				}
			} else {
				$modal_content .= '<p class="notags">' . __( 'No tags', 'gmLang' ) . '</p>';
				$modal_button = false;
			}
			break;
		case 'add_tags':
			$gm_terms = $gmDB->get_terms( 'gmedia_tag' );
			$modal_title = __( 'Add Tags to Selected Items', 'gmLang' );
			$modal_content = '';
			$modal_button = __( 'Add Tags', 'gmLang' );
			if ( count( $gm_terms ) ) {
				foreach ( $gm_terms as $term ) {
					$modal_content .= '<div class="checkbox"><label><input type="checkbox" name="tag_id[]" value="' . $term->term_id . '"> ' . $term->name . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
				}
			} else {
				$modal_content .= '<p class="notags">' . __( 'No tags', 'gmLang' ) . '</p>';
				$modal_button = false;
			}
			break;
		case 'delete_tags':
			$button_class = 'btn-danger';
			$gm_terms = $gmDB->get_terms( 'gmedia_tag' );
			$modal_title = __( 'Delete Tags from Selected Items', 'gmLang' );
			$modal_content = '';
			$modal_button = __( 'Delete Tags', 'gmLang' );
			if ( count( $gm_terms ) ) {
				foreach ( $gm_terms as $term ) {
					$modal_content .= '<div class="checkbox"><label><input type="checkbox" name="tag_id[]" value="' . $term->term_id . '"> ' . $term->name . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
				}
			} else {
				$modal_content .= '<p class="notags">' . __( 'No tags', 'gmLang' ) . '</p>';
				$modal_button = false;
			}
			break;
		default:
			$modal_title = ' ';
			$modal_content = __('Ops! Something wrong.', 'gmLang');
			$modal_button = false;
			break;

	}
?>
	<form class="modal-content" autocomplete="off" method="post">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title"><?php echo $modal_title; ?></h4>
		</div>
		<div class="modal-body">
			<?php echo $modal_content; ?>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e( 'Cancel', 'gmLang' ); ?></button>
			<?php if($modal_button){ ?>
			<button type="submit" name="<?php echo $modal; ?>" class="btn <?php echo $button_class; ?>"><?php echo $modal_button; ?></button>
			<?php } ?>
		</div>
	</form><!-- /.modal-content -->
<?php
	wp_die();
}

add_action( 'wp_ajax_gmedia_import', 'gmedia_import' );
function gmedia_import(){
	global $gmCore;
	check_ajax_referer( "GmediaGallery" );
	if ( ! current_user_can( 'edit_posts' ) )
		die( '-1' );

	$import = $gmCore->_post('import');
	switch ( $import ) {
		case 'gm-import-flagallery':
			/**
			 * @var $gallery array
			 * @var $file string
			 * @var $title string
			 * @var $description string
			 * @var $term_id int
			 */
			$result = array( 'stat' => 'KO', 'message' => __( 'Choose gallery', 'gmLang' ) );
			if(isset($gallery) && is_array($gallery) && !empty($gallery)){
				$files = array();
				foreach($gallery as $gid){
					$flag_gallery = $wpdb->get_row($wpdb->prepare("SELECT gid, path, title, galdesc FROM `{$wpdb->prefix}flag_gallery` WHERE gid = %d", $gid), ARRAY_A);
					if(empty($flag_gallery))
						continue;

					if( !$term = $gmDB->term_exists($flag_gallery['title'], 'gmedia_album') ) {
						$term = $gmDB->insert_term( $flag_gallery['title'], 'gmedia_album', array('description' => htmlspecialchars_decode(stripslashes( $flag_gallery['galdesc'] ))) );
						if(is_wp_error($term)){
							$term['term_id'] = '';
						}
					}

					$term_id = $term['term_id'];
					$path = trailingslashit($flag_gallery['path']);

					$flag_pictures = $wpdb->get_results($wpdb->prepare("SELECT CONCAT('{$path}', filename) AS file, description, alttext AS title, '{$term_id}' AS term_id FROM `{$wpdb->prefix}flag_pictures` WHERE galleryid = %d", $flag_gallery['gid']), ARRAY_A);
					if(empty($flag_pictures))
						continue;

					$files = array_merge($files, $flag_pictures);
				}
				if(!empty($files)) {
					$result = array( 'stat' => 'OK', 'message' => sprintf( __( '%s files for import. Wait please. Crunching', 'gmLang' ), count($files) ) . ' <span class="crunch_file">' . $files[0]['file'] . '</span>', 'message2' => __( 'Import operation is finished', 'gmLang' ), 'files' => $files );
				} else {
					$result = array( 'stat' => 'KO', 'message' => __( 'No files for import', 'gmLang' ) );
				}
			} elseif(isset($file)) {
				$file = ABSPATH . $file;
				if(is_file($file)) {
					$file_data = array(
						'title'				=> $title
						,'description'	=> $description
						,'terms'				=> array('gmedia_album' => $term_id, 'gmedia_tag' => 'flagallery')
					);
					$result = $gmCore->import($file, $file_data);
				} else {
					$result = array( "error" => array( "code" => 100, "message" => __( "File not exist", 'gmLang' ) ), "id" => $file );
				}
			}
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			die();
			break;

		case 'gm-import-nextgen':
			/**
			 * @var $gallery array
			 * @var $pid int
			 * @var $file string
			 * @var $title string
			 * @var $description string
			 * @var $term_id int
			 */
			$result = array( 'stat' => 'KO', 'message' => __( 'Choose gallery', 'gmLang' ) );
			if(isset($gallery) && is_array($gallery) && !empty($gallery)){
				$files = array();
				foreach($gallery as $gid){
					$ngg_gallery = $wpdb->get_row($wpdb->prepare("SELECT gid, path, title, galdesc FROM `{$wpdb->prefix}ngg_gallery` WHERE gid = %d", $gid), ARRAY_A);
					if(empty($ngg_gallery))
						continue;

					if(!$term = $gmDB->term_exists($ngg_gallery['title'], 'gmedia_album')) {
						$term = $gmDB->insert_term( $ngg_gallery['title'], 'gmedia_album', array('description' => htmlspecialchars_decode(stripslashes( $ngg_gallery['galdesc'] ))) );
						if(is_wp_error($term)){
							$term['term_id'] = '';
						}
					}

					$term_id = $term['term_id'];
					$path = trailingslashit($ngg_gallery['path']);

					$ngg_pictures = $wpdb->get_results($wpdb->prepare("SELECT pid, CONCAT('{$path}', filename) AS file, description, alttext AS title, '{$term_id}' AS term_id FROM `{$wpdb->prefix}ngg_pictures` WHERE galleryid = %d", $ngg_gallery['gid']), ARRAY_A);
					if(empty($ngg_pictures))
						continue;

					$files = array_merge($files, $ngg_pictures);
				}
				if(!empty($files)) {
					$result = array( 'stat' => 'OK', 'message' => sprintf( __( '%s files for import. Wait please. Crunching', 'gmLang' ), count($files) ) . ' <span class="crunch_file">' . $files[0]['file'] . '</span>', 'message2' => __( 'Import operation is finished', 'gmLang' ), 'files' => $files );
				} else {
					$result = array( 'stat' => 'KO', 'message' => __( 'No files for import', 'gmLang' ) );
				}
			} elseif(isset($file)) {
				$file = ABSPATH . $file;
				if(is_file($file)) {
					$tags = wp_get_object_terms($pid, 'ngg_tag', 'fields=names');
					if(!is_wp_error($tags) && is_array($tags)) {
						//$tags = array_merge($tags, array('nextgen'));
						array_unshift($tags, 'nextgen');
					} else {
						$tags = array('nextgen');
					}
					$tags = implode(',', $tags);
					$file_data = array(
						'title'				=> $title
						,'description'	=> $description
						,'terms'				=> array('gmedia_album' => $term_id, 'gmedia_tag' => $tags)
					);
					$result = $gmCore->import($file, $file_data);
				} else {
					$result = array( "error" => array( "code" => 100, "message" => __( "File not exist", 'gmLang' ) ), "id" => $file );
				}
			}
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			die();
			break;

	}

	wp_die();
}

add_action( 'wp_ajax_gmDoAjax', 'gmDoAjax' );
/**
 * Do Actions via Ajax
 *
 * @return void
 */
function gmDoAjax() {
	/** @var $wpdb wpdb */
	global $wpdb, $gmCore, $grandAdmin, $gmDB;

	check_ajax_referer( "grandMedia" );

	// check for correct capability
	if ( ! current_user_can( 'edit_posts' ) )
		die( '-1' );

/*	if ( $referer = wp_get_referer() ) {
		if ( false === strpos( $referer, 'GrandMedia' ) )
			die( '0' );
	}
	else {
		die( '0' );
	}
*/
	$_GET    = stripslashes_deep( $_GET    );
	$_POST   = stripslashes_deep( $_POST   );

	$task = isset( $_REQUEST['task'] ) ? $_REQUEST['task'] : false;
	if ( ! $task )
		die( '0' );

	if ( isset( $_POST['form'] ) )
		parse_str( $_POST['form'] );
	if ( isset( $_POST['post'] ) )
		parse_str( $_POST['post'] );

	if ( isset( $gmSelected ) )
		$gmSelected = explode( ',', $gmSelected );

	$update = __( 'Loading...', 'gmLang' );

	/** @var $gmID
	 * @var  $gmTitle
	 * @var  $gmDescription
	 * @var  $gmSelected
	 */
	switch ( $task ) {

		// term - autocomplete
		case 'term-search' :
			if ( isset( $_GET['tax'] ) ) {
				$taxonomy  = sanitize_key( $_GET['tax'] );
				$gmOptions = get_option( 'gmediaOptions' );
				if ( ! isset( $gmOptions['taxonomies'][$taxonomy] ) )
					die( '0' );
				/* TODO current_user_can() assign terms
				if ( ! current_user_can( 'assign_terms' ) )
					die( '-1' );
				*/
			}
			else {
				die( '0' );
			}

			$s = stripslashes( $_GET['q'] );

			if ( false !== strpos( $s, ',' ) ) {
				$s = explode( ',', $s );
				$s = $s[count( $s ) - 1];
			}
			$s = trim( $s );
			if ( strlen( $s ) < 2 )
				die; // require 2 chars for matching

			$results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM {$wpdb->prefix}gmedia_term AS t WHERE t.taxonomy = %s AND t.name LIKE (%s)", $taxonomy, '%' . like_escape( $s ) . '%' ) );

			echo join( $results, "\n" );
			die();
			break;

		case 'term-edit' :
			if ( isset( $_REQUEST['term_id'] ) && isset( $_REQUEST['tax'] ) ) {
				$term_id   = (int) $_REQUEST['term_id'];
				$taxonomy  = sanitize_key( $_REQUEST['tax'] );
				$gmOptions = get_option( 'gmediaOptions' );
				if ( ! isset( $gmOptions['taxonomies'][$taxonomy] ) || ! $term_id )
					die( '0' );
				/* TODO current_user_can() edit terms
				if ( ! current_user_can( 'edit_terms' ) )
					die( '-1' );
				*/
			}
			else {
				die( '0' );
			}
			//include_once(dirname(__FILE__).'/functions.php');
			$result = $grandAdmin->gmEditRow( $term_id, $taxonomy );
			echo $result;
			die();
			break;

		case 'term-delete' :
			if ( isset( $_REQUEST['term_id'] ) && isset( $_REQUEST['tax'] ) ) {
				$term_id   = (int) $_REQUEST['term_id'];
				$taxonomy  = sanitize_key( $_REQUEST['tax'] );
				$gmOptions = get_option( 'gmediaOptions' );
				if ( ! isset( $gmOptions['taxonomies'][$taxonomy] ) || ! $term_id )
					die( '0' );
				/* TODO current_user_can() delete terms
				if ( ! current_user_can( 'assign_terms' ) )
					die( '-1' );
				*/
			}
			else {
				die( '0' );
			}
			$result = $gmDB->delete_term( $term_id, $taxonomy );
			if ( is_wp_error( $result ) || ! $result ) {
				$result = array( 'stat' => 'KO', 'message' => sprintf( __( "Can't delete term #%s", 'gmLang' ), $term_id ) );
			}
			else {
				$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( "Term #%s deleted", 'gmLang' ), $term_id ), 'message' => $update );
			}
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			die();
			break;

		case 'terms-delete' :
			if ( isset( $gmSelected ) && isset( $_REQUEST['tax'] ) ) {
				$term_ids  = array_filter( array_map( 'intval', $gmSelected ) );
				$taxonomy  = sanitize_key( $_REQUEST['tax'] );
				$gmOptions = get_option( 'gmediaOptions' );
				if ( ! isset( $gmOptions['taxonomies'][$taxonomy] ) || ! count( $term_ids ) )
					die( '0' );
				/* TODO current_user_can() delete terms
				if ( ! current_user_can( 'assign_terms' ) )
					die( '-1' );
				*/
			}
			else {
				die( '0' );
			}
			$count = count( $gmSelected );
			foreach ( $term_ids as $term_id ) {
				$result = $gmDB->delete_term( $term_id, $taxonomy );
				if ( is_wp_error( $result ) || ! $result ) {
					$count = $count - 1;
				}
			}
			$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( "%s terms deleted", 'gmLang' ), $count ), 'message' => $update );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			die();
			break;

		case 'gm-update-module':
			if ( isset( $modulezip ) && isset( $modulename ) )
				$postmsg = sprintf( __( "'%s' module updated successfully", 'gmLang' ), $modulename );
		case 'gm-install-module':
			if ( isset( $modulezip ) && isset( $modulename ) ) {
				/** @var $postmsg */
				if ( $task == 'gm-install-module' )
					$postmsg = sprintf( __( '%s module installed successfully', 'gmLang' ), $modulename );
				$update = __( 'Installing...', 'gmLang' );
				$mzip   = download_url( $modulezip );
				if(is_wp_error($mzip)){
					$result = array( 'stat' => 'KO', 'message' => "ERROR : '" . $mzip->get_error_message() . "' ({$modulezip})" );
					header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
					echo json_encode( $result );
					die();
				}

				$mzip   = str_replace( "\\", "/", $mzip );

				$gmOptions = get_option( 'gmediaOptions' );
				$upload    = $gmCore->gm_upload_dir();

				$modules_dir = $upload['path'] . $gmOptions['folder']['module'] . '/';
				if(isset($moduledir) && !empty($moduledir) && is_dir($modules_dir.$moduledir)){
					$gmCore->delete_folder($modules_dir.$moduledir);
				}
				// TODO replace with unzip_file() function
				if ( class_exists( 'ZipArchive' ) ) {
					$zip = new ZipArchive;
					$open = $zip->open( $mzip );
					if($open === true){
						$zip->extractTo( $modules_dir );
						$zip->close();
					} else {
						$result = array( 'stat' => 'KO', 'message' => "ERROR : Can't open archive. Error code: {$open}" );
						header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
						echo json_encode( $result );
						die();
						/*
							ZIPARCHIVE::ER_EXISTS 	- 10: 'File already exists'
							ZIPARCHIVE::ER_INCONS 	- 21: 'Zip archive inconsistent'
							ZIPARCHIVE::ER_INVAL 		- 18: 'Invalid argument'
							ZIPARCHIVE::ER_MEMORY 	- 14: 'Malloc failure'
							ZIPARCHIVE::ER_NOENT 		-  9: 'No such file'
							ZIPARCHIVE::ER_NOZIP 		- 19: 'Not a zip archive'
							ZIPARCHIVE::ER_OPEN 		- 11: 'Can't open file'
							ZIPARCHIVE::ER_READ 		-  5: 'Read error'
							ZIPARCHIVE::ER_SEEK 		-  4: 'Seek error'
						 */
					}
				}
				else {
					require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
					$archive = new PclZip( $mzip );
					$list    = $archive->extract( $modules_dir );
					if ( $list == 0 ) {
						$result = array( 'stat' => 'KO', 'message' => "ERROR : '" . $archive->errorInfo( true ) . "'" );
						header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
						echo json_encode( $result );
						die();
					}

				}
				if ( unlink( $mzip ) ) {
					$result = array( 'stat' => 'OK', 'postmsg' => $postmsg, 'message' => $update );
					header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
					echo json_encode( $result );
				}
			}
			die();
			break;

		case 'gm-delete-module':
			if ( isset( $module ) ) {
				$update     = __( 'Deleting...', 'gmLang' );
				$gmOptions  = get_option( 'gmediaOptions' );
				$upload     = $gmCore->gm_upload_dir();
				$module_dir = $upload['path'] . $gmOptions['folder']['module'] . '/' . $module;
				if ( $gmCore->delete_folder( $module_dir ) ) {
					$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( "'%s' module deleted successfully", 'gmLang' ), $module ), 'message' => $update );
					header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
					echo json_encode( $result );
				}
			}
			die();
			break;

		case 'hideMedia':
			foreach ( $gmSelected as $mID ) {
				update_post_meta( $mID, '_gmedia_hidden', '1' );
			}
			$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( '%s posts was blocked', 'gmLang' ), count( $gmSelected ) ), 'message' => $update );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			die();
			break;

		case 'unhideMedia':
			foreach ( $gmSelected as $mID ) {
				delete_post_meta( $mID, '_gmedia_hidden' );
			}
			$result = array( 'stat' => 'KO', 'postmsg' => sprintf( __( '%s posts was unblocked', 'gmLang' ), count( $gmSelected ) ), 'message' => $update );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			die();
			break;

		case 'deleteMedia':
			foreach ( (array) $gmSelected as $mID ) {
				if ( ! current_user_can( 'delete_post', $mID ) )
					wp_die( __( 'You are not allowed to delete this post.' ) );

				if ( ! wp_delete_attachment( $mID ) )
					wp_die( __( 'Error in deleting...' ) );
			}
			$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( '%s posts was deleted', 'gmLang' ), count( $gmSelected ) ), 'message' => $update );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			die();
			break;

		case 'updateMedia':
			$post['ID']           = $gmID;
			$post['post_title']   = $gmTitle;
			$post['post_content'] = $gmDescription;
			if ( wp_update_post( $post ) ) {
				$gmObject = get_post( $gmID );
				//include_once(dirname(__FILE__).'/functions.php');
				ob_start();
				$grandAdmin->wpMediaRow( $gmObject );
				$tr = ob_get_contents();
				ob_end_clean();
				$result = array( 'stat' => 'OK', 'message' => sprintf( __( 'post #%s updated successfully', 'gmLang' ), $gmID ), 'content' => $tr );
			}
			else {
				$result = array( 'stat' => 'KO', 'message' => sprintf( __( "Can't update post #%s", 'gmLang' ) . '. ' . __( 'Contact plugin author to solve this problem. Describe your problem and give temporary access to Wordpress Dashboard and to FTP plugins folder.' ) . ' (<a href="mailto:gmediafolder+support@gmail.com?subject=Gmedia Support" target="_blank">Gmedia Support</a>)', $gmID ) );
			}
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			die();
			break;

		case 'wpmedia-edit':
			$media_id = (int) $_REQUEST['media_id'];
			if ( ! current_user_can( 'edit_post', $media_id ) )
				die( '-1' );
			//include_once(dirname(__FILE__).'/functions.php');
			$result = $grandAdmin->gmEditRow( $media_id, 'wpmedia' );
			echo $result;
			die();
			break;


		case 'gm-add-tab':
			$query_args = get_option( 'gmediaTemp' );
			$query_args['tab'] ++;
			$grandAdmin->gm_build_query_tab( $query_args );
			update_option( 'gmediaTemp', $query_args );
			die();
			break;

		case 'gm-tabquery-load':
			/** @var $gMediaQuery array parsed from $_POST['form'] */
			$query_args = reset( $gMediaQuery );
			$tab        = key( $gMediaQuery );
			if ( is_array( $query_args ) ) {
				$query_args['nopaging'] = true;
				$gMediaLib   = $gmDB->get_gmedias( $query_args );
				$gmediaCount = $gmDB->gmediaCount;
				$content     = '';
				if ( ! empty( $gMediaLib ) ) {
					$gmOptions = get_option( 'gmediaOptions' );
					$uploads   = $gmCore->gm_upload_dir();
					foreach ( $gMediaLib as $item ) {
						$type     = explode( '/', $item->mime_type );
						$item_url = $uploads['url'] . $gmOptions['folder'][$type[0]] . '/' . $item->gmuid;
						$image    = $gmCore->gm_get_media_image( $item, 'thumb', array( 'width' => 48, 'height' => 48 ) );
						$content .= '<a class="grandbox" title="' . trim( esc_attr( strip_tags( $item->title ) ) ) . '" rel="querybuilder__' . $tab . '" href="' . $item_url . '">' . $image . '</a> ';
					}
				}
				else {
					$content .= '<div style="height:48px; text-align: center; line-height: 48px;">' . __( 'Change filter options or click refresh icon.', 'gmLang' ) . '</div>';
				}
				$result = array( 'stat' => 'OK', 'gmediaCount' => $gmediaCount, 'gMediaLib' => $content );
				header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
				echo json_encode( $result );
			}
			die();
			break;




		case 'related-image':
			$post_tags = array_filter(array_map( 'trim', explode(',', $gmCore->_get('tags', '')) ));
			$paged = (int) $gmCore->_get('paged', 1);
			$per_page = 20;
			$s = trim( $gmCore->_get('search') );
			if ( $s && strlen( $s ) > 2 ) {
				$post_tags = array();
			} else {
				$s = '';
			}

			$gMediaLib = array();
			$relative = (int) $gmCore->_get('rel', 1);
			$continue = true;
			$content = '';

			if($relative == 1){
				$arg = array(
					'mime_type' 		=> 'image/*'
				,	'orderby'   		=> 'ID'
				,	'order'     		=> 'DESC'
				,	'per_page'  		=> $per_page
				,	'page'      		=> $paged
				,	's'							=> $s
				,	'tag_name__in'	=> $post_tags
				,	'null_tags'			=> true
				);
				$gMediaLib = $gmDB->get_gmedias( $arg );
			}

			if( empty( $gMediaLib ) && count($post_tags) ) {

				if($relative == 1){
					$relative = 0;
					$paged = 1;
					$content .= '<li class="emptydb">' . __( 'No items related by tags.', 'gmLang' ) . '</li>'."\n";
				}

				$tag__not_in = "'" . implode( "','", array_map( 'sanitize_title_for_query', array_unique( (array) $post_tags ) ) ) . "'";
				$tag__not_in = $wpdb->get_col( "
					SELECT term_id
					FROM {$wpdb->prefix}gmedia_term
					WHERE taxonomy = 'gmedia_tag'
					AND name IN ({$tag__not_in})
				" );

				$arg = array(
					'mime_type' 		=> 'image/*'
				,	'orderby'   		=> 'ID'
				,	'order'     		=> 'DESC'
				,	'per_page'  		=> $per_page
				,	'page'      		=> $paged
				,	'tag__not_in'		=> $tag__not_in
				);
				$gMediaLib = $gmDB->get_gmedias( $arg );
			}

			if( $count = count( $gMediaLib ) ) {
				$upload = $gmCore->gm_upload_dir();
				foreach ( $gMediaLib as $item ) {
					$src = $upload['url'] . 'image/' . $item->gmuid;

					$content .= "<li class='gMedia-image-li' id='gM-img-{$item->ID}'>\n";
					$content .= "	<a target='_blank' class='gM-img' data-gmid='{$item->ID}' href='{$src}'>".$gmCore->gm_get_media_image( $item, 'thumb', array( 'width' => 50, 'height' => 50 ) )."</a>\n";

					$content .= "	<div style='display: none;' class='gM-img-description'>".trim(esc_html(strip_tags($item->description)))."</div>\n";
					//$content .= "	<div class='gMedia-selector'></div>\n";
					$content .= "</li>\n";

				}
				if(($count < $per_page) && ($relative == 0 || !empty($s))){
					$continue = false;
				}
			}
			else {
				if($s){
					$content .= '<li class="emptydb">' . __( 'No items matching the search query.', 'gmLang' ) . '</li>'."\n";
				} else {
					$content .= '<li class="emptydb">' . __( 'Gmedia Library is empty.', 'gmLang' ) . '</li>'."\n";
				}
				$continue = false;
			}
			$result = array( 'paged' => $paged, 'rel' => $relative, 'continue' => $continue,  'content' => $content );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );

			die();
			break;

		case 'gm-get-key':
			global $wp_version;
			if(isset($set['gmedia_key']) && !empty($set['gmedia_key'])){
				$gmedia_ua = "WordPress/{$wp_version} | ";
				$gmedia_ua .= 'Gmedia/' . constant( 'GMEDIA_VERSION' );

				$response = wp_remote_post( 'http://codeasily.com/rest/gmedia-key.php', array(
						'body' => array( 'key' => $set['gmedia_key'], 'site' => site_url() ),
						'headers'		=> array(
							'Content-Type'	=> 'application/x-www-form-urlencoded; ' .
							'charset=' . get_option( 'blog_charset' ),
							'Host'			=> 'codeasily.com',
							'User-Agent'	=> $gmedia_ua
						),
						'httpversion' => '1.0',
						'timeout' => 45,
					)
				);

				if ( is_wp_error( $response ) ) {
					$error_message = $response->get_error_message();
					$result = array( "error" => array( "code" => 102, "message" => __( "Something went wrong:", 'gmLang' ).' '.$error_message ) );
				} else {
					$gmOptions = get_option( 'gmediaOptions' );
					$result = json_decode($response['body']);
					if($result->error->code == 200){
						$gmOptions['gmedia_key'] = $result->key;
						$gmOptions['gmedia_key2'] = $result->key2;
						$gmOptions['product_name'] = $result->content;
						$result->message = __('License Key activated successfully', 'gmLang');
					} else {
						$gmOptions['gmedia_key'] = '';
						$gmOptions['gmedia_key2'] = '';
						$gmOptions['product_name'] = '';
						$result->message = __('Error', 'gmLang').': '.$result->error->message;
					}
					update_option( 'gmediaOptions', $gmOptions );
				}
			} else {
				$result = array( "error" => array( "code" => 101, "message" => __( "Empty License Key", 'gmLang' ) ) );
			}
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			//echo '<pre>'; print_r($result); echo '</pre>';;
			die();
			break;


	}
	die();
}

add_action( 'wp_ajax_gmedia_crunching', 'gmedia_crunching' );
add_action( 'wp_ajax_nopriv_gmedia_crunching', 'gmedia_crunching' );
/**
 * make thumbs
 *
 * @return void
 */
function gmedia_crunching() {
	global $gmCore;
	$thumb = $gmCore->linked_img($_POST['args']);
	echo json_encode($thumb);
	die();
}

add_action( 'wp_ajax_gmedia_ftp_browser', 'gmedia_ftp_browser' );
/**
 * jQuery File Tree PHP Connector
 * @author Cory S.N. LaViska - A Beautiful Site (http://abeautifulsite.net/)
 * @version 1.0.1
 *
 * @return string folder content
 */
function gmedia_ftp_browser() {
	global $gmCore;
	if ( !current_user_can('upload_files') )
		die('No access');

	// if nonce is not correct it returns -1
	check_ajax_referer( 'grandMedia' );

	// start from the default path
	$root = trailingslashit ( ABSPATH );
	// get the current directory
	$dir = trailingslashit ( urldecode($_POST['dir']) );

	if( file_exists($root . $dir) ) {
		$files = scandir($root . $dir);
		natcasesort($files);

		// The 2 counts for . and ..
		if( count($files) > 2 ) {
			echo "<ul class=\"jqueryDirTree\" style=\"display: none;\">";
			// return only directories
			foreach( $files as $file ) {
				//reserved name for the thumnbnails, don't use it as folder name
				if ( in_array( $file, array('wp-admin', 'wp-includes', GMEDIA_FOLDER, 'plugins', 'themes', 'thumb') ) )
					continue;

				if ( file_exists($root . $dir . $file) && $file != '.' && $file != '..' && is_dir($root . $dir . $file) ) {
					echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . esc_html($dir . $file) . "/\">" . esc_html($file) . "</a></li>";
				}
			}
			echo "</ul>";
		}
	}

	die();
}
