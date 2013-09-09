<?php

if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * gmAdmin - Class for admin operation
 */
class gmAdmin {

	/**
	 * wpMediaRow
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	function wpMediaRow( $item ) {
		$gMediaURL      = plugins_url( GRAND_FOLDER );
		$nonce          = wp_create_nonce( 'grandMedia' );
		$selected_items = isset( $_COOKIE['gmedia_wp_selected_items'] ) ? explode( ',', $_COOKIE['gmedia_wp_selected_items'] ) : array();
		$checked        = in_array( $item->ID, $selected_items ) ? ' checked="checked"' : '';
		$type           = explode( '/', $item->post_mime_type );
		$image          = wp_get_attachment_image( $item->ID, array( 36, 36 ), false );
		$item_url       = wp_get_attachment_url( $item->ID );
		if ( ! $image ) {
			if ( $src = wp_mime_type_icon( $item->ID ) ) {
				$icon_dir_url = $gMediaURL . '/admin/images';
				$src_file     = $icon_dir_url . '/' . wp_basename( $src );
				$image        = '<img src="' . $src_file . '" width="36" height="20" alt="icon" title="' . $item->post_title . '" />';
			}
		}
		$file      = '<a class="grandbox" href="' . $item_url . '">' . $image . '</a>';
		$file_info = pathinfo( $item_url );
		switch ( $type[0] ) {
			case 'image':
				$actions = '<a class="fancy-view" rel="image" href="' . $item_url . '" title="' . __( "View", "gmLang" ) . '">' . __( "View", "gmLang" ) . '</a>';
				break;
			case 'audio':
				$actions = '<a class="fancy-listen" rel="audio" href="' . $item_url . '" title="' . __( "Listen", "gmLang" ) . '">' . __( "Listen", "gmLang" ) . '</a>';
				break;
			case 'video':
				$actions = '<a class="fancy-watch" rel="video" href="' . $item_url . '" title="' . __( "Watch", "gmLang" ) . '">' . __( "Watch", "gmLang" ) . '</a>';
				break;
			default:
				$actions = '<a class="fancy-app" rel="application" href="' . $item_url . '" title="' . __( "Application", "gmLang" ) . '">' . __( "Application", "gmLang" ) . '</a>';
				break;
		}
		$actions .= '<a class="edit ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-media_id="' . $item->ID . '" data-task="wpmedia-edit" href="' . admin_url( 'gmedia.php?attachment_id=' . $item->ID . '&amp;action=edit' ) . '" title="' . __( "Edit", "gmLang" ) . '">' . __( "Edit", "gmLang" ) . '</a>';
		$actions .= '<a class="delete confirm" data-txt="' . __( "You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang" ) . '" href="' . wp_nonce_url( "post.php?action=delete&amp;post=" . $item->ID, 'delete-attachment_' . $item->ID ) . '" title="' . __( "Delete Permanently", "gmLang" ) . '">' . __( "Delete Permanently", "gmLang" ) . '</a>';
		$meta = '';
		if ( get_post_meta( $item->ID, '_gmedia_hidden', true ) ) {
			$meta = ' gmedia_hidden';
			$tip  = ' title="' . __( 'Hidden media', 'gmLang' ) . '"';
		}
		$trClass = 'class="' . $type[0] . $meta . '" id="item_' . $item->ID . '"';
		?>
		<tr <?php echo $trClass; ?>>
			<td class="bufer"><span>&nbsp;</span></td>
			<td class="cb">
				<span><input name="doaction[]" type="checkbox" value="<?php echo $item->ID; ?>" <?php echo $checked; ?> /></span>
			</td>
			<td class="id"><span><?php echo $item->ID; ?></span></td>
			<td class="file"><span><?php echo $file; ?></span></td>
			<td class="type"><span><?php echo $file_info['extension']; ?></span></td>
			<td class="title"><span><?php echo $item->post_title; ?></span></td>
			<td class="descr">
				<div><?php echo htmlspecialchars($item->post_content); ?></div>
			</td>
			<td class="actions">
				<div><?php echo $actions; ?></div>
			</td>
		</tr>
	<?php
	}

	/**
	 * gMediaRow
	 *
	 * @param object $item
	 */
	function gMediaRow( $item ) {
		global $grandCore, $gMDb;

		$nonce          = wp_create_nonce( 'grandMedia' );
		$gMediaURL      = plugins_url( GRAND_FOLDER );
		$selected_items = isset( $_COOKIE['gmedia_gm_selected_items'] ) ? explode( ',', $_COOKIE['gmedia_gm_selected_items'] ) : array();
		$checked        = in_array( $item->ID, $selected_items ) ? ' checked="checked"' : '';
		$gmOptions      = get_option( 'gmediaOptions' );
		$uploads        = $grandCore->gm_upload_dir();
		$type           = explode( '/', $item->mime_type );
		$item_url       = $uploads['url'] . $gmOptions['folder'][$type[0]] . '/' . $item->gmuid;
		$attr = array( 'width' => 36, 'height' => 36 );

		/*if('image' != $type[0]){
			$preview_meta  	= $gMDb->get_metadata( 'gmedia', $item->ID, 'preview', true );
			if(intval($preview_meta)){
				$preview_item = $gMDb->get_gmedia( intval($preview_meta) );
				$preview_image = $grandCore->gm_get_media_image( $preview_item, 'thumb', array(), 'src' );
				$attr['data-preview'] = $preview_image;
			}
		}*/

		$image     = $grandCore->gm_get_media_image( $item, 'thumb', $attr );
		$file      = '<a class="grandbox" href="' . $item_url . '">' . $image . '</a>';
		$file_info = pathinfo( $item_url );
		switch ( $type[0] ) {
			case 'image':
				$actions = '<a class="fancy-view" rel="image" href="' . $item_url . '" title="' . __( "View", "gmLang" ) . '">' . __( "View", "gmLang" ) . '</a>';
				break;
			case 'audio':
				$actions = '<a class="fancy-listen" rel="audio" href="' . $item_url . '" title="' . __( "Listen", "gmLang" ) . '">' . __( "Listen", "gmLang" ) . '</a>';
				break;
			case 'video':
				$actions = '<a class="fancy-watch" rel="video" href="' . $item_url . '" title="' . __( "Watch", "gmLang" ) . '">' . __( "Watch", "gmLang" ) . '</a>';
				break;
			default:
				$actions = '<a class="fancy-app" rel="application" href="' . $item_url . '" title="' . __( "Application", "gmLang" ) . '">' . __( "Application", "gmLang" ) . '</a>';
				break;
		}
		$actions .= '<a class="edit ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-gmedia_id="' . $item->ID . '" data-task="gmedia-edit" href="' . wp_nonce_url( "admin-ajax.php?action=gmDoAjax&amp;gmedia_id=" . $item->ID . "&amp;task=gmedia-edit", 'grandMedia' ) . '" title="' . __( "Edit", "gmLang" ) . '">' . __( "Edit", "gmLang" ) . '</a>';
		$actions .= '<a class="delete ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-gmedia_id="' . $item->ID . '" data-task="gmedia-delete" data-confirmtxt="' . __( "You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang" ) . '" href="' . wp_nonce_url( "admin-ajax.php?action=gmDoAjax&amp;gmedia_id=" . $item->ID . "&amp;task=gmedia-delete", 'grandMedia' ) . '" title="' . __( "Delete Permanently", "gmLang" ) . '">' . __( "Delete Permanently", "gmLang" ) . '</a>';
		$trClass = 'class="' . $type[0] . '" id="item_' . $item->ID . '"';
		$tags    = $gMDb->get_the_gmedia_terms( $item->ID, 'gmedia_tag' );
		if ( ! empty( $tags ) ) {
			$out = array();
			foreach ( $tags as $c ) {
				$out[] = sprintf( '<a class="tag" href="%s">%s</a>',
					esc_url( add_query_arg( array( 'tag_id' => $c->term_id, 'cat' => false, 'pager' => false ) ) ),
					esc_html( $c->name )
				);
			}
			$tags = join( '', $out );
			unset( $out );
		}
		else {
			$tags = '';
		}
		$cats = $gMDb->get_the_gmedia_terms( $item->ID, 'gmedia_category' );
		if ( ! empty( $cats ) ) {
			$out = array();
			foreach ( $cats as $c ) {
				$out[] = sprintf( '<a class="category" href="%s">%s</a>',
					esc_url( add_query_arg( array( 'cat' => $c->term_id, 'tag_id' => false, 'pager' => false ) ) ),
					esc_html( $c->name )
				);
			}
			$cats = join( '', $out );
			unset( $out );
		}
		else {
			$cats = '';
		}
		?>
		<tr <?php echo $trClass; ?>>
			<td class="bufer"><span>&nbsp;</span></td>
			<td class="cb">
				<span><input name="doaction[]" type="checkbox" value="<?php echo $item->ID; ?>" <?php echo $checked; ?> /></span>
			</td>
			<td class="id"><span><?php echo $item->ID; ?></span></td>
			<td class="file"><span><?php echo $file; ?></span></td>
			<td class="type"><span><?php echo $file_info['extension']; ?></span></td>
			<td class="title"><span><?php echo $item->title; ?></span></td>
			<td class="descr">
				<div><?php echo $cats . $tags . htmlspecialchars($item->description); ?></div>
			</td>
			<td class="actions">
				<div><?php echo $actions; ?></div>
			</td>
		</tr>
	<?php
	}

	function gm_term_row( $item, $gmOptions = array() ) {
		global $grandCore;

		$nonce = wp_create_nonce( 'grandMedia' );

		if ( $item->taxonomy == 'gmedia_module' ) {
			global $gMDb;

			$meta_type     = 'gmedia_term';
			$last_edited   = $gMDb->get_metadata( $meta_type, $item->term_id, 'last_edited', true );
			$module_folder = $gMDb->get_metadata( $meta_type, $item->term_id, 'module_name', true );
			$gMediaQuery   = $gMDb->get_metadata( $meta_type, $item->term_id, 'gMediaQuery', true );
			$gmModuleCount = 0;
			foreach ( $gMediaQuery as $query_args ) {
				$query_args['fields'] = 'ids';
				$gmModuleCount += count( $gMDb->get_gmedias( $query_args ) );
			}

			$module_dir = $grandCore->get_module_path( $module_folder );
			/** @var $module array */
			if($module_dir) {
				include( $module_dir['path'] . '/details.php' );
				$actions = '<a class="edit" href="' . admin_url( "admin.php?page=GrandMedia_Modules&amp;module=" . $module_folder . "&amp;term_id=" . $item->term_id, 'grandMedia' ) . '" title="' . __( "Edit", "gmLang" ) . '">' . __( "Edit", "gmLang" ) . '</a>';
			} else {
				$actions = '<span class="pad">&nbsp;</span>';
			}
			$actions .= '<a class="delete ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-term_id="' . $item->term_id . '" data-tax="' . $item->taxonomy . '" data-task="term-delete" data-confirmtxt="' . __( "You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang" ) . '" href="' . wp_nonce_url( "admin-ajax.php?action=gmDoAjax&amp;term_id=" . $item->term_id . "&amp;tax=" . $item->taxonomy . "&amp;task=term-delete", 'grandMedia' ) . '" title="' . __( "Delete Permanently", "gmLang" ) . '">' . __( "Delete Permanently", "gmLang" ) . '</a>';
			$trClass = 'class="gmTermRow" id="item_' . $item->term_id . '"';
			?>
			<tr <?php echo $trClass; ?>>
				<td class="bufer"><span>&nbsp;</span></td>
				<td class="module_preview">
					<span><?php if($module_dir) { ?><img src="<?php echo $module_dir['url'] . '/screenshot.png'; ?>" alt="" width="100" style="height: auto;" /><?php } else { _e('No Module', 'gmLang'); } ?></span>
				</td>
				<td class="id"><p><?php echo $item->term_id; ?></p></td>
				<td class="name"><span><?php echo $item->name; ?></span><br />
					<small><?php echo __( 'module', 'gmLang' ) . ': '; if($module_dir) { echo $module['title']; } else { echo "'{$module_folder}' " . __('not installed or broken', 'gmLang'); } ?></small>
				</td>
				<td class="descr">
					<div><?php echo htmlspecialchars( $item->description ); ?></div>
				</td>
				<td class="count">
					<div><?php echo $gmModuleCount; ?></div>
				</td>
				<td class="last_edited">
					<div><?php echo $last_edited; ?></div>
				</td>
				<td class="actions">
					<div><?php echo $actions; ?></div>
				</td>
			</tr>
		<?php
		}
		else {
			$selected_items = isset( $_COOKIE['gmedia_' . $item->taxonomy . '_selected_items'] ) ? explode( ',', $_COOKIE['gmedia_' . $item->taxonomy . '_selected_items'] ) : array();
			$checked        = in_array( $item->term_id, $selected_items ) ? ' checked="checked"' : '';
			$pad            = str_repeat( '&#8212; ', max( 0, $item->level ) );
			if ( $pad ) $pad = '<i class="gm_has_parent" rel="' . $item->global . '">' . $pad . '</i>';
			$name    = $pad . $item->name;
			$actions = '<a class="edit ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-term_id="' . $item->term_id . '" data-tax="' . $item->taxonomy . '" data-task="term-edit" href="' . wp_nonce_url( "admin-ajax.php?action=gmDoAjax&amp;term_id=" . $item->term_id . "&amp;tax=" . $item->taxonomy . "&amp;task=term-edit", 'grandMedia' ) . '" title="' . __( "Edit", "gmLang" ) . '">' . __( "Edit", "gmLang" ) . '</a>';
			$actions .= '<a class="delete ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-term_id="' . $item->term_id . '" data-tax="' . $item->taxonomy . '" data-task="term-delete" data-confirmtxt="' . __( "You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang" ) . '" href="' . wp_nonce_url( "admin-ajax.php?action=gmDoAjax&amp;term_id=" . $item->term_id . "&amp;tax=" . $item->taxonomy . "&amp;task=term-delete", 'grandMedia' ) . '" title="' . __( "Delete Permanently", "gmLang" ) . '">' . __( "Delete Permanently", "gmLang" ) . '</a>';
			$trClass = 'class="gmTermRow level' . $item->level . '" id="item_' . $item->term_id . '"';
			?>
			<tr <?php echo $trClass; ?>>
				<td class="bufer"><span>&nbsp;</span></td>
				<td class="cb">
					<span><input name="doaction[]" type="checkbox" value="<?php echo $item->term_id; ?>" <?php echo $checked; ?> /></span>
				</td>
				<td class="id"><span><?php echo $item->term_id; ?></span></td>
				<td class="name"><span><?php echo $name; ?></span></td>
				<td class="descr">
					<div><?php echo htmlspecialchars($item->description); ?></div>
				</td>
				<td class="count">
					<div><?php echo $item->count; ?></div>
				</td>
				<td class="actions">
					<div><?php echo $actions; ?></div>
				</td>
			</tr>
		<?php
		}
	}

	/**
	 * gMediaRow
	 *
	 * @param $id
	 * @param $type
	 *
	 * @return string
	 */
	function gmEditRow( $id, $type ) {
		global $grandCore, $gMDb;
		$nonce = wp_create_nonce( 'grandMedia' );

		$gMediaURL = plugins_url( GRAND_FOLDER );
		switch ( $type ) {
			case 'gmedia_category':
			case 'gmedia_tag':
				$item = $gMDb->get_term( $id, $type );
				?>
				<table id="gmedia-edit">
					<tr class="gmedia-edit-row">
						<td class="colspanchange" colspan="8">
							<form action="" method="post" id="gmEdit_<?php echo $item->term_id; ?>">
								<fieldset class="<?php echo $type; ?>">
									<legend><span class="legendID"><?php echo $item->term_id; ?></span></legend>
									<input name="gmID" type="hidden" value="<?php echo $item->term_id; ?>" />
									<?php wp_nonce_field( 'grandMedia' ); ?>
									<div class="set gmName">
										<label for="tax-edit-<?php echo $type; ?>"><?php _e( 'Name', 'gmLang' ); ?></label>
										<input type="text" id="tax-edit-<?php echo $type; ?>" class="the-term" name="terms[<?php echo $type; ?>]" autocomplete="off" value="<?php echo $item->name; ?>" />
										<?php
										if ( $type == 'gmedia_category' ) {
											$gmTerms = $gMDb->get_terms( $type, array( 'exclude_tree' => array( $item->term_id ) ) );
											$opt     = '';
											if ( count( $gmTerms ) ) {
												$children     = $gMDb->_get_term_hierarchy( $type );
												$termsHierarr = $grandCore->get_terms_hierarrhically( $type, $gmTerms, $children, $count = 0 );
												foreach ( $termsHierarr as $termitem ) {
													$sel = ( $item->global == $termitem->term_id ) ? ' selected="selected"' : '';
													$pad = str_repeat( '&#8212; ', max( 0, $termitem->level ) );
													$opt .= '<option' . $sel . ' value="' . $termitem->term_id . '">' . $pad . $termitem->name . '</option>' . "\n";
												}
											}
											$sel = ( $item->global == 0 ) ? ' selected="selected"' : '';
											?>
											<div class="term_global_div">
												<hr class="spacer" />
												<label for="tax-edit-gm_term_global"><?php _e( 'Parent', 'gmLang' ); ?></label>
												<select id="tax-edit-gm_term_global" class="the-term-global" name="gm_term_global">
													<option<?php echo $sel; ?> value="0"><?php _e( 'None', 'gmLang' ); ?></option>
													<?php echo $opt; ?>
												</select>
											</div>
										<?php
										}
										?>
									</div>
									<div class="set gmDescription">
										<label for="tax-edit-gm_term_description"><?php _e( 'Description', 'gmLang' ); ?></label>
										<textarea id="tax-edit-gm_term_description" class="the-term-description" cols="20" rows="3" name="gm_term_description"><?php echo htmlspecialchars($item->description); ?></textarea>
									</div>
									<div class="buttons">
										<input type="button" class="cancel" value="<?php _e( 'Cancel', 'gmLang' ); ?>" title="<?php _e( 'Cancel', 'gmLang' ); ?>" />
										<input type="submit" class="save" name="updateTerm" value="<?php _e( 'Save', 'gmLang' ); ?>" title="<?php _e( 'Save', 'gmLang' ); ?>" />
									</div>
								</fieldset>
							</form>
						</td>
					</tr>
				</table>
				<?php
				die();
				break;
			case 'gmedia':
				$item  = $gMDb->get_gmedia( $id );
				$meta  = $gMDb->get_metadata( 'gmedia', $id );
				$mime_type = explode( '/', $item->mime_type );
				$image = $grandCore->gm_get_media_image( $item, 'thumb', array( 'data-icon' => false ) );
				if(isset($meta['preview'][0]) && intval($meta['preview'][0])){
					$preview_item = $gMDb->get_gmedia( intval($meta['preview'][0]) );
					$preview_image = $grandCore->gm_get_media_image( $preview_item, 'thumb', array( 'id' => false, 'class' => 'gmedia-thumb-preview' ) );
					$image = $preview_image . $image;
				}
			?>
				<table id="gmedia-edit">
					<tr class="gmedia-edit-row">
						<td class="colspanchange" colspan="8">
							<form action="" method="post" id="gmEdit_<?php echo $item->ID; ?>">
								<fieldset class="<?php echo $type; ?>">
									<legend><span class="legendID"><?php echo $item->ID; ?></span></legend>
									<input name="gmedia[ID]" type="hidden" value="<?php echo $item->ID; ?>" />

									<div class="gmImage"><?php echo $image; ?></div>
									<div class="gmFile row va-t">
										<span class="label"><?php _e( 'Filename', 'gmLang' ); ?></span><span class="value"><?php echo $item->gmuid; ?></span>
									</div>
									<div class="gmTitle row va-b">
										<span class="label"><?php _e( 'Title', 'gmLang' ); ?></span><input name="gmedia[title]" type="text" value="<?php echo $item->title; ?>" />
									</div>
									<?php if('image' != $mime_type[0]){ ?>
									<div class="gmPreview row va-b">
										<span class="label"><?php _e( 'Preview ID', 'gmLang' ); ?></span><input name="gmedia[meta][preview]" type="text" value="<?php if(isset($meta['preview'][0]) && intval($meta['preview'][0])){echo $meta['preview'][0];}; ?>" readonly /><span title="<?php _e('clear', 'gmLang'); ?>" class="clear-preview">&times;</span>
										<span class="metabox-preview">#</span>
									</div>
									<?php } ?>
									<div class="gmLink row va-b">
										<span class="label"><?php _e( 'Link', 'gmLang' ); ?></span><input name="gmedia[meta][link]" type="text" value="<?php if(isset($meta['link'][0])){echo $meta['link'][0];}; ?>" />
									</div>
									<?php $cat = $gMDb->get_the_gmedia_terms( $item->ID, 'gmedia_category' );
									if ( empty( $cat ) ) {
										$cat_id = 0;
									}
									else {
										$cat_id = $cat[0]->term_id;
									}
									$ttype = 'gmedia_category';
									$gmTerms = $gMDb->get_terms( $ttype );
									$opt = '';
									if ( count( $gmTerms ) ) {
										$children     = $gMDb->_get_term_hierarchy( $ttype );
										$termsHierarr = $grandCore->get_terms_hierarrhically( $ttype, $gmTerms, $children, $count = 0 );
										foreach ( $termsHierarr as $termitem ) {
											$sel = ( $cat_id == $termitem->term_id ) ? ' selected="selected"' : '';
											$pad = str_repeat( '&#8212; ', max( 0, $termitem->level ) );
											$opt .= '<option' . $sel . ' value="' . $termitem->term_id . '">' . $pad . $termitem->name . '</option>' . "\n";
										}
									}
									$sel = ( $cat_id == 0 ) ? ' selected="selected"' : '';
									?>
									<div class="gmCategory row va-b">
										<span class="label"><?php _e( 'Category', 'gmLang' ); ?></span><select name="gmedia[terms][gmedia_category]">
											<option<?php echo $sel; ?> value="0"><?php _e( 'None', 'gmLang' ); ?></option>
											<?php echo $opt; ?>
										</select></div>
									<?php
									$tags = $gMDb->get_the_gmedia_terms( $item->ID, 'gmedia_tag' );
									if ( ! empty( $tags ) ) {
										$out = array();
										foreach ( $tags as $c ) {
											$out[] = esc_html( $c->name );
										}
										$tags = join( ', ', $out );
										unset( $out );
									}
									else {
										$tags = '';
									}
									?>
									<div class="gmLabels row va-b">
										<span class="label"><?php _e( 'Labels', 'gmLang' ); ?></span><textarea name="gmedia[terms][gmedia_tag]" rows="2" cols="60"><?php echo $tags; ?></textarea>
									</div>
									<div class="gmDescription">
										<span class="label"><?php _e( 'Description', 'gmLang' ); ?></span><textarea name="gmedia[description]" rows="4" cols="10"><?php echo htmlspecialchars($item->description); ?></textarea>
									</div>
									<input name="gmedia[author]" type="hidden" value="<?php echo $item->author; ?>" />
									<input name="gmedia[gmuid]" type="hidden" value="<?php echo $item->gmuid; ?>" />
									<input name="gmedia[mime_type]" type="hidden" value="<?php echo $item->mime_type; ?>" />
									<input name="gmedia[date]" type="hidden" value="<?php echo $item->date; ?>" />

									<div class="buttons">
										<input type="button" class="cancel" value="<?php _e( 'Cancel', 'gmLang' ); ?>" title="<?php _e( 'Cancel', 'gmLang' ); ?>" />
										<input type="submit" class="save ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#gmEdit_<?php echo $item->ID; ?>" data-task="gmedia-update" name="gmedia-update" value="<?php _e( 'Save', 'gmLang' ); ?>" title="<?php _e( 'Save', 'gmLang' ); ?>" />
									</div>
								</fieldset>
							</form>
						</td>
					</tr>
				</table>
				<?php
				die();
				break;
			case 'wpmedia':
				$item     = get_post( $id );
				$image    = wp_get_attachment_image_src( $item->ID, array( 150, 150 ), false );
				$item_url = wp_get_attachment_url( $item->ID );
				$item_url = wp_basename( $item_url );
				if ( ! $image ) {
					if ( $src = wp_mime_type_icon( $item->ID ) ) {
						$icon_dir_url = $gMediaURL . '/admin/images';
						$image[0]     = $icon_dir_url . '/' . wp_basename( $src );
					}
				}
				$edit_url = admin_url( 'media.php?action=edit&amp;attachment_id=' . $item->ID );
				?>
				<table id="gmedia-edit">
					<tr class="gmedia-edit-row">
						<td class="colspanchange" colspan="8">
							<form action="" method="post" id="gmEdit_<?php echo $item->ID; ?>">
								<fieldset class="<?php echo $type; ?>">
									<legend><span class="legendID"><?php echo $item->ID; ?></span></legend>
									<input name="gmID" type="hidden" value="<?php echo $item->ID; ?>" />

									<div class="gmImage">
										<img width="150" height="150" alt="<?php echo $item->post_title; ?>" class="attachment-150x150" src="<?php echo $image[0]; ?>" /><a class="gmImageEdit" href="<?php echo $edit_url; ?>" title="<?php _e( 'Edit media by WordPress Media Library.', 'gmLang' ); ?>"><?php _e( 'Edit', 'gmLang' ); ?></a>
									</div>
									<div class="gmFile row va-t">
										<span class="label"><?php _e( 'Filename', 'gmLang' ); ?></span><span class="value"><?php echo $item_url; ?></span>
									</div>
									<div class="gmTitle row va-b">
										<span class="label"><?php _e( 'Title', 'gmLang' ); ?></span><input name="gmTitle" type="text" value="<?php echo $item->post_title; ?>" />
									</div>
									<div class="gmDescription">
										<span class="label"><?php _e( 'Description', 'gmLang' ); ?></span><textarea name="gmDescription" rows="4" cols="60"><?php echo htmlspecialchars($item->post_content); ?></textarea>
									</div>
									<div class="buttons">
										<input type="button" class="cancel" value="<?php _e( 'Cancel', 'gmLang' ); ?>" title="<?php _e( 'Cancel', 'gmLang' ); ?>" />
										<input type="submit" class="save ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#gmEdit_<?php echo $item->ID; ?>" data-task="updateMedia" name="wpmedia-update" value="<?php _e( 'Save', 'gmLang' ); ?>" title="<?php _e( 'Save', 'gmLang' ); ?>" />
									</div>
								</fieldset>
							</form>
						</td>
					</tr>
				</table>
				<?php
				die();
				break;
		}
		die();
	}

	/**
	 * gm_build_query_tabs
	 *
	 * @param $query_args
	 *
	 * @return string
	 */
	function gm_build_query_tab( $query_args ) {
		global $gMDb, $grandCore;

		$nonce = wp_create_nonce( 'grandMedia' );
		$tab   = $query_args['tab'];

		if ( isset( $_REQUEST['term_id'] ) ) {
			$gMediaLib   = $gMDb->get_gmedias( $query_args );
			$gmediaCount = $gMDb->gmediaCount;
		}
		else {
			$gMediaLib   = array();
			$gmediaCount = 0;
		}
		$gmOptions = get_option( 'gmediaOptions' );
		$uploads   = $grandCore->gm_upload_dir();
		?>
		<div class="tabqueryblock" id="tabqueryblock__<?php echo $tab; ?>">
			<?php if ( isset( $query_args['multitab'] ) && $query_args['multitab'] == 'true' ) { ?>
				<div class="format-setting-query gm_query_tabname">
					<label for="query_tabname__<?php echo $tab; ?>"><?php _e( 'Tab Name', 'gmLang' ); ?>:</label>
					<input type="text" name="gMediaQuery[<?php echo $tab; ?>][tabname]" id="query_tabname__<?php echo $tab; ?>" class="gmedia-ui-select query_tabname" value="<?php echo $query_args['tabname']; ?>" />
				</div>
			<?php } ?>

			<div class="gMediaLibActions">
				<input type="hidden" name="gMediaQuery[<?php echo $tab; ?>][mime_type]" id="query_vis_mime_type__<?php echo $tab; ?>" class="gmedia-ui-select query_mime_type" value="<?php echo $query_args['mime_type']; ?>" />

				<div class="abuts">
					<span class="gm-ui-folder" title="<?php _e( 'Category', 'gmLang' ); ?>"><select name="gMediaQuery[<?php echo $tab; ?>][cat]" id="query_vis_cat__<?php echo $tab; ?>" class="gmedia-ui-select query_cat">
							<option value=""<?php selected( $query_args['cat'], '' ); ?>><?php _e( 'Any', 'gmLang' ); ?></option>
							<option value="0"<?php selected( $query_args['cat'], '0' ); ?>><?php _e( 'Uncategorized', 'gmLang' ); ?></option>
							<?php
							/* get category array */
							$type = 'gmedia_category';
							$categories = $gMDb->get_terms( $type, array( 'hide_empty' => false ) );
							$opt = '';
							if ( count( $categories ) ) {
								$children     = $gMDb->_get_term_hierarchy( $type );
								$termsHierarr = $grandCore->get_terms_hierarrhically( $type, $categories, $children, $count = 0 );
								foreach ( $termsHierarr as $termitem ) {
									$sel = selected( $query_args['cat'], $termitem->term_id, false );
									$pad = str_repeat( '&#8212; ', max( 0, $termitem->level ) );
									$opt .= '<option' . $sel . ' value="' . $termitem->term_id . '">' . $pad . $termitem->name . '</option>' . "\n";
								}
								echo $opt;
							}
							?>
						</select></span>
					<span class="gm-ui-author" title="<?php _e( 'Author', 'gmLang' ); ?>"><select name="gMediaQuery[<?php echo $tab; ?>][author]" id="query_vis_author__<?php echo $tab; ?>" class="gmedia-ui-select query_author">
							<option value=""<?php selected( $query_args['author'], '' ); ?>><?php _e( 'Any', 'gmLang' ); ?></option>
							<?php $args = array(
								'who' => 'authors'
							);
							$blogusers = get_users( $args );
							foreach ( $blogusers as $user ) {
								?>
								<option value="<?php echo $user->ID; ?>" <?php selected( $query_args['author'], $user->ID ); ?>><?php echo $user->display_name; ?></option>
							<?php } ?>
						</select></span>
					<span class="gm-ui-orderby" title="<?php _e( 'Order By', 'gmLang' ); ?>"><select name="gMediaQuery[<?php echo $tab; ?>][orderby]" id="query_vis_orderby__<?php echo $tab; ?>" class="gmedia-ui-select query_orderby">
							<option value="none"<?php selected( $query_args['orderby'], 'none' ); ?>><?php _e( 'No order', 'gmLang' ); ?></option>
							<option value="ID"<?php selected( $query_args['orderby'], 'ID' ); ?>><?php _e( 'Order by gMedia id', 'gmLang' ); ?></option>
							<option value="author"<?php selected( $query_args['orderby'], 'author' ); ?>><?php _e( 'Order by author', 'gmLang' ); ?></option>
							<option value="title"<?php selected( $query_args['orderby'], 'title' ); ?>><?php _e( 'Order by title', 'gmLang' ); ?></option>
							<option value="date"<?php selected( $query_args['orderby'], 'date' ); ?>><?php _e( 'Order by date', 'gmLang' ); ?></option>
							<option value="modified"<?php selected( $query_args['orderby'], 'modified' ); ?>><?php _e( 'Order by last modified date', 'gmLang' ); ?></option>
							<option value="rand"<?php selected( $query_args['orderby'], 'rand' ); ?>><?php _e( 'Random order', 'gmLang' ); ?></option>
						</select></span>
					<span class="gm-ui-order" title="<?php _e( 'Order', 'gmLang' ); ?>"><select name="gMediaQuery[<?php echo $tab; ?>][order]" id="query_vis_order__<?php echo $tab; ?>" class="gmedia-ui-select query_order">
							<option value="DESC"<?php selected( $query_args['order'], 'DESC' ); ?>><?php _e( 'DESC', 'gmLang' ); ?></option>
							<option value="ASC"<?php selected( $query_args['order'], 'ASC' ); ?>><?php _e( 'ASC', 'gmLang' ); ?></option>
						</select></span>
				</div>
				<div class="more abut">
					<div class="dropbut"><?php _e( 'Labels', 'gmLang' ); ?></div>
					<div class="dropbox">
						<div class="term_list" id="query_vis_tag__<?php echo $tab; ?>">
							<?php
							/* get category array */
							$type = 'gmedia_tag';
							$tags = $gMDb->get_terms( $type, array( 'hide_empty' => false ) );
							$opt = '';
							if ( count( $tags ) ) {
								foreach ( $tags as $termitem ) {
									$sel = ( in_array( $termitem->term_id, $query_args['tag__in'] ) ) ? ' checked' : '';
									$opt .= '<div class="item' . $sel . '"><span class="dropchild query_tag_value">';
									$opt .= '<input type="checkbox"' . $sel . ' name="gMediaQuery[' . $tab . '][tag__in][]" id="l_ch_' . $termitem->term_id . '__' . $tab . '" value="' . $termitem->term_id . '" /> ';
									$opt .= '<label for="l_ch_' . $termitem->term_id . '__' . $tab . '">' . $termitem->name . '</label>';
									$opt .= '</span></div>' . "\n";
								}
								echo $opt;
							}
							else {
								echo '<p>' . __( 'No Tags Found', 'gmLang' ) . '</p>';
							}
							?>
						</div>
					</div>
				</div>
				<div class="msg reload ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#tabqueryblock__<?php echo $tab; ?>" data-task="gm-tabquery-load" title="<?php _e( 'Reload', 'gmLang' ); ?>">
					<span id="selectedItems__<?php echo $tab; ?>"><span class="selectedItems"><?php echo $gmediaCount; ?></span> <?php _e( 'loaded', 'gmLang' ); ?></span>
				</div>
				<a href="#" class="gmDelTab"><?php _e( 'Remove Tab', 'gmLang' ); ?></a>
			</div>
			<div id="query_media_vis__<?php echo $tab; ?>" class="query_media_vis">
				<?php
				if ( ! empty( $gMediaLib ) ) {
					foreach ( $gMediaLib as $item ) {
						$type     = explode( '/', $item->mime_type );
						$item_url = $uploads['url'] . $gmOptions['folder'][$type[0]] . '/' . $item->gmuid;
						$image    = $grandCore->gm_get_media_image( $item, 'thumb', array( 'width' => 48, 'height' => 48 ) );
						$title = trim( esc_attr( strip_tags( $item->title ) ) );
						$file     = '<a class="grandbox" title="' . $title . '" rel="querybuilder__' . $tab . '" href="' . $item_url . '">' . $image . '<span>' . $title . '</span></a> ';
						echo $file;
					}
				}
				else {
					echo '<div style="height:48px; text-align: center; line-height: 48px;">' . __( 'Change filter options or click refresh icon.', 'gmLang' ) . '</div>';
				}

				?>

			</div>
		</div>
	<?php
	}

} // END class gmAdmin

global $grandAdmin;
$grandAdmin = new gmAdmin();
