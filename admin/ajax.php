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

	die();
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
						$modal_content .= '<div class="checkbox"><label><input type="checkbox" name="cat[]" value="' . $term->term_id . '"> ' . esc_html($term->name) . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
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
					$modal_content .= '<div class="radio"><label><input type="radio" name="cat" value="' . $term_name . '"> ' . esc_html($term_title) . '</label></div>';
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
					$modal_content .= '<div class="checkbox"><label><input type="checkbox" name="alb[]" value="' . $term->term_id . '"> ' . esc_html($term->name) . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
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
					$modal_content .= '<div class="radio"><label><input type="radio" name="alb" value="' . $term->term_id . '"> ' . esc_html($term->name) . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
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
					$modal_content .= '<div class="checkbox"><label><input type="checkbox" name="tag_id[]" value="' . $term->term_id . '"> ' . esc_html($term->name) . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
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
					$modal_content .= '<div class="checkbox"><label><input type="checkbox" name="tag_id[]" value="' . $term->term_id . '"> ' . esc_html($term->name) . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
				}
			} else {
				$modal_content .= '<p class="notags">' . __( 'No tags', 'gmLang' ) . '</p>';
				$modal_button = false;
			}
			break;
		case 'delete_tags':
			global $gmProcessor;

			$button_class = 'btn-danger';
			$modal_title = __( 'Delete Tags from Selected Items', 'gmLang' );
			$modal_content = '';
			$modal_button = __( 'Delete Tags', 'gmLang' );
			$gm_terms = array();
			//$gm_terms = $gmDB->get_terms( 'gmedia_tag' );
			if(!empty($gmProcessor->selected_items)){
				$gm_terms = $gmDB->get_gmedia_terms($gmProcessor->selected_items, 'gmedia_tag');
			}
			if ( count( $gm_terms ) ) {
				foreach ( $gm_terms as $term ) {
					$modal_content .= '<div class="checkbox"><label><input type="checkbox" name="tag_id[]" value="' . $term->term_id . '"> ' . esc_html($term->name) . '</label><span class="badge pull-right">' . $term->count . '</span></div>';
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
	die();
}

add_action( 'wp_ajax_gmedia_tag_edit', 'gmedia_tag_edit' );
function gmedia_tag_edit(){
	global $gmCore, $gmDB;

	check_ajax_referer( 'GmediaTerms' );
	if ( ! current_user_can( 'edit_posts' ) )
		die( '-1' );

	$term = array('taxonomy' => 'gmedia_tag');
	$term['name'] = trim($gmCore->_post('tag_name', ''));
	$term['term_id'] = intval($gmCore->_post('tag_id', 0));
	if( $term['name'] && !$gmCore->is_digit($term['name']) ){
		if ( $term_id = $gmDB->term_exists( $term['term_id'], $term['taxonomy'] ) ) {
			$term_id = $gmDB->update_term( $term['term_id'], $term['taxonomy'], $term );
			if ( is_wp_error( $term_id ) ) {
				$out['error'] = $term_id->get_error_message();
			} else{
				$out['msg'] = sprintf( __( "Tag %d successfuly updated", 'gmLang' ), $term_id );
			}
		} else{
			$out['error'] = __( "A term with the id provided do not exists.", 'gmLang' );
		}
	} else{
		$out['error'] = __( "Term name can't be only digits or empty", 'gmLang' );
	}

	header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
	echo json_encode( $out );

	die();

}

add_action( 'wp_ajax_gmedia_module_install', 'gmedia_module_install' );
function gmedia_module_install(){
	global $gmCore, $gmProcessor, $gmGallery;

	check_ajax_referer( 'GmediaModule' );
	if ( ! current_user_can( 'edit_posts' ) ){
		echo $gmProcessor->alert('danger', __('You are not allowed to install modules'));
		die();
	}

	if($download = $gmCore->_post('download')){
		$module = $gmCore->_post('module');
		$mzip = download_url( $download );
		if(is_wp_error($mzip)){
			echo $gmProcessor->alert('danger', $mzip->get_error_message());
			die();
		}

		$mzip = str_replace( "\\", "/", $mzip );
		$to_folder = $gmCore->upload['path'] . '/' . $gmGallery->options['folder']['module'] . '/';

		global $wp_filesystem;
		// Is a filesystem accessor setup?
		if(!$wp_filesystem || !is_object($wp_filesystem)){
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			WP_Filesystem();
		}
		if(!is_object($wp_filesystem)){
			$result = new WP_Error('fs_unavailable', __('Could not access filesystem.', 'flag'));
		} elseif($wp_filesystem->errors->get_error_code()){
			$result = new WP_Error('fs_error', __('Filesystem error', 'flag'), $wp_filesystem->errors);
		} else{
			$result = unzip_file($mzip, $to_folder);
		}

		// Once extracted, delete the package
		unlink($mzip);

		if(is_wp_error($result)){
			echo $gmProcessor->alert('danger', $result->get_error_message());
			die();
		} else{
			echo $gmProcessor->alert('success', sprintf(__("The `%s` module successfuly installed", 'flag'), $module));
		}
	} else{
		echo $gmProcessor->alert('danger', __('No file specified', 'gmLang'));
	}

die();

}


add_action( 'wp_ajax_gmedia_import_modal', 'gmedia_import_modal' );
function gmedia_import_modal(){
	global $user_ID, $gmDB, $gmCore, $gmGallery;

	check_ajax_referer( 'GmediaGallery' );
	if ( ! current_user_can( 'edit_posts' ) )
		die( '-1' );

	?>
	<div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h4 class="modal-title"><?php _e('Import from WP Media Library'); ?></h4>
		</div>
		<div class="modal-body" style="position:relative;">
			<form id="import_form" name="import_form" target="import_window" action="<?php echo $gmCore->gmedia_url; ?>/admin/import.php" method="POST" accept-charset="utf-8">
				<?php wp_nonce_field('GmediaImport'); ?>
				<input type="hidden" id="import-action" name="import" value="<?php echo esc_attr($gmCore->_post('modal','')); ?>"/>
				<input type="hidden" name="selected" value="<?php $ckey = "gmedia_u{$user_ID}_wpmedia"; if(isset($_COOKIE[$ckey])){ echo $_COOKIE[$ckey]; } ?>"/>
				<div class="form-group">
					<?php
					$term_type = 'gmedia_category';
					$gm_terms = $gmGallery->options['taxonomies'][$term_type];

					$terms_category = '';
					if(count($gm_terms)){
						foreach($gm_terms as $term_name => $term_title){
							$terms_category .= '<option value="' . $term_name . '">' . esc_html($term_title) . '</option>' . "\n";
						}
					}
					?>
					<label><?php _e('Assign Category', 'gmLang'); ?> <small><?php _e('(for images only)') ?></small></label>
					<select id="gmedia_category" name="terms[gmedia_category]" class="form-control input-sm">
						<option value=""><?php _e('Uncategorized', 'gmLang'); ?></option>
						<?php echo $terms_category; ?>
					</select>
				</div>

				<div class="form-group">
					<?php
					$term_type = 'gmedia_album';
					$gm_terms = $gmDB->get_terms($term_type);

					$terms_album = '';
					if(count($gm_terms)){
						foreach($gm_terms as $term){
							$terms_album .= '<option value="' . esc_attr($term->name) . '">' . esc_html($term->name) . '</option>' . "\n";
						}
					}
					?>
					<label><?php _e('Add to Album', 'gmLang'); ?> </label>
					<select id="combobox_gmedia_album" name="terms[gmedia_album]" class="form-control input-sm" placeholder="<?php _e('Album Name...', 'gmLang'); ?>">
						<option value=""></option>
						<?php echo $terms_album; ?>
					</select>
				</div>

				<div class="form-group">
					<?php
					$term_type = 'gmedia_tag';
					$gm_terms = $gmDB->get_terms($term_type, array('fields' => 'names'));
					?>
					<label><?php _e('Add Tags', 'gmLang'); ?> </label>
					<input id="combobox_gmedia_tag" name="terms[gmedia_tag]" class="form-control input-sm" value="" placeholder="<?php _e('Add Tags...', 'gmLang'); ?>" />
				</div>

				<script type="text/javascript">
					jQuery(function($){
						$('#combobox_gmedia_album').selectize({
							create: true,
							persist: false
						});
						var gm_terms = <?php echo json_encode($gm_terms); ?>,
							items = gm_terms.map(function(x){
								return { item: x };
							});
						$('#combobox_gmedia_tag').selectize({
							delimiter: ',',
							maxItems: null,
							persist: false,
							options: items,
							labelField: 'item',
							valueField: 'item',
							create: function(input){
								return {
									item: input
								}
							}
						});

						$('#import-done').one('click', function(e){
							$('#import_form').submit();
							$(this).button('loading').prop('disabled', true);
							$('#import_window').show();
							$(this).one('click', function(e){ $('#importModal').modal('hide'); });
						});

					});
				</script>
			</form>
			<iframe name="import_window" id="import_window" src="about:blank" style="display:none; position:absolute; left:0; top:0; width:100%; height:100%; z-index:1000; background-color:#ffffff; padding:20px 20px 0 20px;" onload="gmedia_import_done()"></iframe>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e( 'Cancel', 'gmLang' ); ?></button>
			<button type="button" id="import-done" class="btn btn-primary" data-complete-text="<?php _e( 'Close', 'gmLang' ); ?>" data-loading-text="<?php _e( 'Working...', 'gmLang' ); ?>"><?php _e( 'Import', 'gmLang' ); ?></button>
		</div>
	</div><!-- /.modal-content -->
	<?php
	die();
}

add_action( 'wp_ajax_gmedia_relimage', 'gmedia_relimage' );
/**
 * Do Actions via Ajax
 *
 * @return void
 */
function gmedia_relimage() {
	/** @var $wpdb wpdb */
	global $wpdb, $gmCore, $gmDB;

	check_ajax_referer( "grandMedia" );

	// check for correct capability
	if ( ! current_user_can( 'edit_posts' ) )
		die( '-1' );

	$post_tags = array_filter(array_map( 'trim', explode(',', stripslashes(urldecode($gmCore->_get('tags', '')))) ));
	$paged = (int) $gmCore->_get('paged', 1);
	$per_page = 20;
	$s = trim( stripslashes(urldecode($gmCore->_get('search'))) );
	if ( $s && strlen( $s ) > 2 ) {
		$post_tags = array();
	} else {
		$s = '';
	}

	$gmediaLib = array();
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
		$gmediaLib = $gmDB->get_gmedias( $arg );
	}

	if( empty( $gmediaLib ) && count($post_tags) ) {

		if($relative == 1){
			$relative = 0;
			$paged = 1;
			$content .= '<li class="emptydb">' . __( 'No items related by tags.', 'gmLang' ) . '</li>'."\n";
		}

		$tag__not_in = "'" . implode( "','", array_map( 'esc_sql', array_unique( (array) $post_tags ) ) ) . "'";
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
		$gmediaLib = $gmDB->get_gmedias( $arg );
	}

	if( $count = count( $gmediaLib ) ) {
		foreach ( $gmediaLib as $item ) {
			$content .= "<li class='gmedia-image-li' id='gm-img-{$item->ID}'>\n";
			$content .= "	<a target='_blank' class='gm-img' data-gmid='{$item->ID}' href='".$gmCore->gm_get_media_image($item)."'><img src='".$gmCore->gm_get_media_image( $item, 'thumb' )."' height='50' style='width:auto;' alt='' title='".esc_attr($item->title)."' /></a>\n";
			$content .= "	<div style='display: none;' class='gm-img-description'>".esc_html($item->description)."</div>\n";
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
			$content .= '<li class="emptydb">' . __( 'No items to show', 'gmLang' ) . '</li>'."\n";
		}
		$continue = false;
	}
	$result = array( 'paged' => $paged, 'rel' => $relative, 'continue' => $continue,  'content' => $content, 'data' => $post_tags );
	header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
	echo json_encode( $result );

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
				if ( in_array( $file, array('wp-admin', 'wp-includes', 'plugins', 'themes', 'thumb', 'thumbs') ) )
					continue;

				if ( file_exists($root . $dir . $file) && $file != '.' && $file != '..' && is_dir($root . $dir . $file) ) {
					echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . esc_attr($dir . $file) . "/\">" . esc_html($file) . "</a></li>";
				}
			}
			echo "</ul>";
		}
	}

	die();
}
