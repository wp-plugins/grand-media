<?php
add_action( 'wp_ajax_gmDoAjax', 'gmDoAjax' );
/**
 * Do Actions via Ajax
 *
 * @return void
 */
function gmDoAjax() {
	/** @var $wpdb wpdb */
	global $wpdb, $grandCore, $grandAdmin, $gMDb;

	check_ajax_referer( "grandMedia" );

	// check for correct capability
	if ( ! current_user_can( 'edit_posts' ) )
		die( '-1' );

	if ( $referer = wp_get_referer() ) {
		if ( false === strpos( $referer, 'GrandMedia' ) )
			die( '0' );
	}
	else {
		die( '0' );
	}

	$task = isset( $_REQUEST['task'] ) ? $_REQUEST['task'] : false;
	if ( ! $task )
		die( '0' );

	if ( isset( $_POST['form'] ) )
		parse_str( $_POST['form'] );
	if ( isset( $_POST['post'] ) )
		parse_str( $_POST['post'] );

	if ( isset( $gmSelected ) )
		$gmSelected = explode( ',', $gmSelected );

	$update = $grandCore->message( __( 'Loading...', 'gmLang' ), 'wait' );

	/** @var $gmID
	 * @var  $gmTitle
	 * @var  $gmDescription
	 * @var  $gmSelected
	 */
	switch ( $task ) {

		case 'gmedia-edit':
			$media_id = (int) $_REQUEST['gmedia_id'];
			//include_once(dirname(__FILE__).'/functions.php');
			$result = $grandAdmin->gmEditRow( $media_id, 'gmedia' );
			echo $result;
			die();
			break;

		case 'gmedia-update':
			if ( ! empty( $gmedia['ID'] ) ) {
				$gmedia['modified'] = current_time( 'mysql' );
				$id                 = $gMDb->gmInsertMedia( $gmedia );
				if ( ! is_wp_error( $id ) ) {
					$item = $gMDb->gmGetMedia( $id );
					//include_once(dirname(__FILE__).'/functions.php');
					ob_start();
					$grandAdmin->gMediaRow( $item );
					$tr = ob_get_contents();
					ob_end_clean();
					$result = array( 'stat' => 'OK', 'message' => $grandCore->message( sprintf( __( 'Media #%s updated successfully', 'gmLang' ), $id ), 'info' ), 'content' => $tr );
				}
				else {
					$result = array( 'stat' => 'KO', 'message' => $grandCore->message( sprintf( __( "Can't update media #%s", 'gmLang' ) . '. ' . __( 'Contact plugin author to solve this problem. Describe your problem and give temporary access to Wordpress Dashboard and to FTP plugins folder.' ) . ' (<a href="mailto:gmediafolder+support@gmail.com?subject=Gmedia Support" target="_blank">Gmedia Support</a>)', $gmID ), 'error' ), 'error' => $id );
				}
				header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
				echo json_encode( $result );
			}
			break;

		case 'gmedia-delete':
			if ( isset( $_REQUEST['gmedia_id'] ) ) {
				$update = $grandCore->message( __( 'Deleting...', 'gmLang' ), 'wait' );
				$mID    = absint( $_REQUEST['gmedia_id'] );
				if ( ! $mID )
					die( '0' );
				if ( ! current_user_can( 'delete_posts' ) )
					wp_die( __( 'You are not allowed to delete this post.' ) );
				if ( ! $gMDb->gm_delete_gmedia( $mID ) )
					wp_die( __( 'Error in deleting...' ) );
				$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( 'gMedia #%s was deleted', 'gmLang' ), $mID ), 'message' => $update );
				header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
				echo json_encode( $result );
			}
			else {
				die();
			}
			break;

		case 'gmedia-bulk-delete':
			if ( isset( $gmSelected ) ) {
				$update = $grandCore->message( __( 'Deleting...', 'gmLang' ), 'wait' );
				foreach ( (array) $gmSelected as $mID ) {
					if ( ! current_user_can( 'delete_posts' ) )
						wp_die( __( 'You are not allowed to delete this post.' ) );

					if ( ! $gMDb->gm_delete_gmedia( $mID ) )
						wp_die( __( 'Error in deleting...' ) );
				}
				$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( '%s gmedia(s) was deleted', 'gmLang' ), count( $gmSelected ) ), 'message' => $update );
				header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
				echo json_encode( $result );
			}
			else {
				die();
			}
			break;

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
			$result = $gMDb->gmDeleteTerm( $term_id, $taxonomy );
			if ( is_wp_error( $result ) || ! $result ) {
				$result = array( 'stat' => 'KO', 'message' => $grandCore->message( sprintf( __( "Can't delete term #%s", 'gmLang' ), $term_id ), 'error' ) );
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
				$result = $gMDb->gmDeleteTerm( $term_id, $taxonomy );
				if ( is_wp_error( $result ) || ! $result ) {
					$count = $count - 1;
				}
			}
			$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( "%s terms deleted", 'gmLang' ), $count ), 'message' => $update );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			die();
			break;

		case 'moveToCategory' :
			if ( isset( $gmSelected ) && isset( $_REQUEST['term_id'] ) ) {
				$term_id = absint( $_REQUEST['term_id'] );
				$term_id = array_filter( array( $term_id ) );
				$count   = count( $gmSelected );
				$error   = '';
				foreach ( (array) $gmSelected as $mID ) {
					$result = $gMDb->gmSetMediaTerms( $mID, $term_id, 'gmedia_category', $append = 0 );
					if ( is_wp_error( $result ) || ! $result ) {
						$error[] = $result;
						$count --;
					}
				}
				$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( "%s gmedias was updated with new category", 'gmLang' ), $count ), 'message' => $update, 'error' => $error );
				header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
				echo json_encode( $result );
			}
			die();
			break;

		case 'gm-add-label' :
			$append = 1;
		case 'gm-remove-label' :
			if ( isset( $gmSelected ) && ! empty( $label ) ) {
				/** @var $append */
				if ( $task == 'gm-remove-label' )
					$append = - 1;
				if ( ! is_array( $label ) )
					$label = array_filter( array_map( 'trim', explode( ',', $label ) ) );
				else
					$label = array_map( 'intval', $label );
				$count = count( $gmSelected );
				$error = '';
				foreach ( (array) $gmSelected as $mID ) {
					$result = $gMDb->gmSetMediaTerms( $mID, $label, 'gmedia_tag', $append );
					if ( is_wp_error( $result ) || ! $result ) {
						$error[] = $result;
						$count --;
					}
				}
				$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( "Label(s) updated for %s gmedias", 'gmLang' ), $count ), 'message' => $update, 'error' => $error );
				header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
				echo json_encode( $result );
			}
			die();
			break;

		case 'gm-update-module':
			if ( isset( $module ) )
				$postmsg = sprintf( __( "'%s' module updated successfully", 'gmLang' ), $module );
		case 'gm-install-module':
			if ( isset( $module ) ) {
				/** @var $postmsg */
				if ( $task == 'gm-install-module' )
					$postmsg = sprintf( __( '%s.zip module installed successfully', 'gmLang' ), $module );
				$update = $grandCore->message( __( 'Installing...', 'gmLang' ), 'wait' );
				$url    = "http://dl.dropbox.com/u/6295502/gmedia_modules/$module.zip";
				$mzip   = download_url( $url );
				if(is_wp_error($mzip)){
					echo $url;
					die( "ERROR : '" . $mzip->get_error_message() . "'" );
				}

				$mzip   = str_replace( "\\", "/", $mzip );

				$gmOptions = get_option( 'gmediaOptions' );
				$upload    = $grandCore->gm_upload_dir();

				$module_dir = $upload['path'] . $gmOptions['folder']['module'] . '/';

				if ( class_exists( 'ZipArchive' ) ) {
					$zip = new ZipArchive;
					$zip->open( $mzip );
					$zip->extractTo( $module_dir );
					$zip->close();
				}
				else {
					require_once( ABSPATH . 'wp-admin/includes/class-pclzip.php' );
					$archive = new PclZip( $mzip );
					$list    = $archive->extract( $module_dir );
					if ( $list == 0 ) {
						die( "ERROR : '" . $archive->errorInfo( true ) . "'" );
					}

				}
				if ( unlink( $mzip ) ) {
					$result = array( 'stat' => 'OK', 'postmsg' => $postmsg, 'message' => $update );
					header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
					echo json_encode( $result );
				}
			}
			else {
				die();
			}
			break;

		case 'gm-delete-module':
			if ( isset( $module ) ) {
				$update     = $grandCore->message( __( 'Deleting...', 'gmLang' ), 'wait' );
				$gmOptions  = get_option( 'gmediaOptions' );
				$upload     = $grandCore->gm_upload_dir();
				$module_dir = $upload['path'] . $gmOptions['folder']['module'] . '/' . $module;
				if ( $grandCore->gm_delete_folder( $module_dir ) ) {
					$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( "'%s' module deleted successfully", 'gmLang' ), $module ), 'message' => $update );
					header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
					echo json_encode( $result );
				}
			}
			else {
				die();
			}
			break;

		case 'hideMedia':
			foreach ( $gmSelected as $mID ) {
				update_post_meta( $mID, '_gmedia_hidden', '1' );
			}
			$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( '%s medias was blocked', 'gmLang' ), count( $gmSelected ) ), 'message' => $update );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			break;

		case 'unhideMedia':
			foreach ( $gmSelected as $mID ) {
				delete_post_meta( $mID, '_gmedia_hidden' );
			}
			$result = array( 'stat' => 'KO', 'postmsg' => sprintf( __( '%s medias was unblocked', 'gmLang' ), count( $gmSelected ) ), 'message' => $update );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			break;

		case 'deleteMedia':
			foreach ( (array) $gmSelected as $mID ) {
				if ( ! current_user_can( 'delete_post', $mID ) )
					wp_die( __( 'You are not allowed to delete this post.' ) );

				if ( ! wp_delete_attachment( $mID ) )
					wp_die( __( 'Error in deleting...' ) );
			}
			$result = array( 'stat' => 'OK', 'postmsg' => sprintf( __( '%s medias was deleted', 'gmLang' ), count( $gmSelected ) ), 'message' => $update );
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
			break;

		case 'updateMedia':
			$post['ID']           = $gmID;
			$post['post_title']   = $gmTitle;
			$post['post_content'] = $gmDescription;
			if ( wp_update_post( $post ) ) {
				$gmObject = get_post( $gmID );
				//include_once(dirname(__FILE__).'/functions.php');
				$tr     = $grandAdmin->wpMediaRow( $gmObject );
				$result = array( 'stat' => 'OK', 'message' => $grandCore->message( sprintf( __( 'Media #%s updated successfully', 'gmLang' ), $gmID ), 'info' ), 'content' => $tr );
			}
			else {
				$result = array( 'stat' => 'KO', 'message' => $grandCore->message( sprintf( __( "Can't update media #%s", 'gmLang' ) . '. ' . __( 'Contact plugin author to solve this problem. Describe your problem and give temporary access to Wordpress Dashboard and to FTP plugins folder.' ) . ' (<a href="mailto:gmediafolder+support@gmail.com?subject=Gmedia Support" target="_blank">Gmedia Support</a>)', $gmID ), 'error' ) );
			}
			header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
			echo json_encode( $result );
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
				$gMediaLib   = $gMDb->gmGetMedias( $query_args );
				$gmediaCount = $gMDb->gmediaCount;
				$content     = '';
				if ( ! empty( $gMediaLib ) ) {
					$gmOptions = get_option( 'gmediaOptions' );
					$uploads   = $grandCore->gm_upload_dir();
					foreach ( $gMediaLib as $item ) {
						$type     = explode( '/', $item->mime_type );
						$item_url = $uploads['url'] . $gmOptions['folder'][$type[0]] . '/' . $item->gmuid;
						$image    = $grandCore->gmGetMediaImage( $item, 'thumb', array( 'width' => 48, 'height' => 48 ), true );
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

	}
	die();
}

add_action( 'wp_ajax_gmGetAjax', 'gmGetAjax' );
/**
 * Get data via Ajax
 *
 * @return void
 */
function gmGetAjax() {
	global $grandCore;

	$task = isset( $_REQUEST['task'] ) ? $_REQUEST['task'] : false;
	if ( ! $task )
		die( '0' );

	if ( isset( $_POST['post'] ) )
		parse_str( $_POST['post'] );

	switch ( $task ) {

		case 'gmMessage':
			echo $grandCore->message( $_POST['message'], $_POST['stat'] );
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
	global $grandCore;
	$thumb = $grandCore->linked_img($_POST['args']);
	echo json_encode($thumb);
	die();
}