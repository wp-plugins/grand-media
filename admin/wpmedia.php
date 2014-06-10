<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	die('You are not allowed to call this page directly.');
}

/**
 * grandWPMedia()
 *
 * @return mixed content
 */
function grandWPMedia(){
	global $user_ID, $gmDB, $gmCore, $gmProcessor, $gmGallery;

	$url = add_query_arg(array('page' => $gmProcessor->page, 'mode' => $gmProcessor->mode), admin_url('admin.php'));

	$gm_screen_options = get_user_meta($user_ID, 'gm_screen_options', true);
	if(!is_array($gm_screen_options)){
		$gm_screen_options = array();
	}
	$gm_screen_options = array_merge($gmGallery->options['gm_screen_options'], $gm_screen_options);

	$arg = array('mime_type' => $gmCore->_get('mime_type', ''), 'orderby' => $gmCore->_get('orderby', $gm_screen_options['orderby_wpmedia']),
				 'order' => $gmCore->_get('order', $gm_screen_options['sortorder_wpmedia']), 'limit' => $gm_screen_options['per_page_wpmedia'], 'filter' => $gmCore->_get('filter', ''),
				 's' => $gmCore->_get('s', ''));
	$wpMediaLib = $gmDB->get_wp_media_lib($arg);

	$gm_qty = array('total' => '', 'image' => '', 'audio' => '', 'video' => '', 'text' => '', 'application' => '', 'other' => '');

	$gmDbCount = $gmDB->count_wp_media($arg);
	foreach($gmDbCount as $key => $value){
		$gm_qty[$key] = '<span class="badge pull-right">' . (int)$value . '</span>';
	}
	?>
	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<form class="form-inline gmedia-search-form" role="search">
				<div class="form-group">
					<?php foreach($_GET as $key => $value){
						if(in_array($key, array('page', 'mime_type'))){
							?>
							<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>"/>
						<?php
						}
					} ?>
					<input id="gmedia-search" class="form-control input-sm" type="text" name="s" placeholder="<?php _e('Search...', 'gmLang'); ?>" value="<?php echo $gmCore->_get('s', ''); ?>"/>
				</div>
				<button type="submit" class="btn btn-default input-sm"><span class="glyphicon glyphicon-search"></span>
				</button>
			</form>
			<?php echo $gmDB->query_pager(); ?>

			<div class="btn-toolbar pull-left">
				<?php if(!$gmProcessor->mode){ ?>
					<div class="btn-group gm-checkgroup" id="cb_global-btn">
						<span class="btn btn-default active"><input class="doaction" id="cb_global" data-group="cb_media-object" type="checkbox"/></span>
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							<span class="caret"></span>
							<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
						<ul class="dropdown-menu" role="menu">
							<li><a data-select="total" href="#"><?php _e('All', 'gmLang'); ?></a></li>
							<li><a data-select="none" href="#"><?php _e('None', 'gmLang'); ?></a></li>
							<li class="divider"></li>
							<li><a data-select="image" href="#"><?php _e('Images', 'gmLang'); ?></a></li>
							<li><a data-select="audio" href="#"><?php _e('Audio', 'gmLang'); ?></a></li>
							<li><a data-select="video" href="#"><?php _e('Video', 'gmLang'); ?></a></li>
							<li class="divider"></li>
							<li>
								<a data-select="reverse" href="#" title="<?php _e('Reverse only visible items', 'gmLang'); ?>"><?php _e('Reverse', 'gmLang'); ?></a>
							</li>
						</ul>
					</div>
				<?php } ?>

				<div class="btn-group">
					<?php $curr_mime = explode(',', $gmCore->_get('mime_type', 'total')); ?>
					<?php if($gmDB->filter){ ?>
						<a class="btn btn-warning" title="<?php _e('Reset Filter', 'gmLang'); ?>" rel="total" href="<?php echo $url; ?>"><?php _e('Filter', 'gmLang'); ?></a>
					<?php } else{ ?>
						<button type="button" class="btn btn-default"><?php _e('Filter', 'gmLang'); ?></button>
					<?php } ?>
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						<li role="presentation" class="dropdown-header"><?php _e('TYPE', 'gmLang'); ?></li>
						<li class="total<?php if(in_array('total', $curr_mime)){
							echo ' active';
						} ?>"><a rel="total" href="<?php echo $gmCore->get_admin_url(array(), array('mime_type', 'pager')); ?>"><?php _e('All', 'gmLang');
								echo $gm_qty['total']; ?></a></li>
						<li class="image<?php if(in_array('image', $curr_mime)){
							echo ' active';
						}
						if(!$gmDbCount['image']){
							echo ' disabled';
						} ?>">
							<a rel="image" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'image'), array('pager')); ?>"><?php _e('Images', 'gmLang');
								echo $gm_qty['image']; ?></a></li>
						<li class="audio<?php if(in_array('audio', $curr_mime)){
							echo ' active';
						}
						if(!$gmDbCount['audio']){
							echo ' disabled';
						} ?>">
							<a rel="audio" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'audio'), array('pager')); ?>"><?php _e('Audio', 'gmLang');
								echo $gm_qty['audio']; ?></a></li>
						<li class="video<?php if(in_array('video', $curr_mime)){
							echo ' active';
						}
						if(!$gmDbCount['video']){
							echo ' disabled';
						} ?>">
							<a rel="video" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'video'), array('pager')); ?>"><?php _e('Video', 'gmLang');
								echo $gm_qty['video']; ?></a></li>
						<li class="application<?php if(in_array('application', $curr_mime) || in_array('text', $curr_mime)){
							echo ' active';
						}
						if(!$gmDbCount['application']){
							echo ' disabled';
						} ?>">
							<a rel="application" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'application,text'), array('pager')); ?>"><?php _e('Other', 'gmLang');
								echo $gm_qty['other']; ?></a></li>
						<?php do_action('gmedia_wp_filter_list'); ?>
					</ul>
				</div>

				<div class="btn-group">
					<a class="btn btn-default" href="#"><?php _e('Action', 'gmLang'); ?></a>
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
					<?php
					$rel_selected_show = 'rel-selected-show';
					$rel_selected_hide = 'rel-selected-hide';
					?>
					<ul class="dropdown-menu" role="menu">
						<li class="<?php echo $rel_selected_show; ?>"><a href="#importModal" data-modal="import-wpmedia" data-action="gmedia_import_modal" class="gmedia-modal"><?php _e('Import to Gmedia Library...', 'gmLang'); ?></a></li>
						<!-- <li class="divider <?php echo $rel_selected_hide; ?>"></li> -->
						<li class="dropdown-header <?php echo $rel_selected_hide; ?>"><span><?php _e("Select items to see more actions", "gmLang"); ?></span></li>
						<?php do_action('gmedia_action_list'); ?>
					</ul>
				</div>

				<form class="btn-group" id="gm-selected-btn" name="gm-selected-form" action="<?php echo add_query_arg(array('filter' => 'selected'), $url); ?>" method="post">
					<button type="submit" class="btn btn<?php echo ('selected' == $gmCore->_req('filter'))? '-success' : '-info' ?>"><?php printf(__('%s selected', 'gmLang'), '<span id="gm-selected-qty">' . count($gmProcessor->selected_items) . '</span>'); ?></button>
					<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
					<input type="hidden" id="gm-selected" data-userid="<?php echo $user_ID; ?>" data-key="wpmedia" name="selected_items" value="<?php echo implode(',', $gmProcessor->selected_items); ?>"/>
					<ul class="dropdown-menu" role="menu">
						<li><a id="gm-selected-show" href="#show"><?php _e('Show only selected items', 'gmLang'); ?></a></li>
						<li><a id="gm-selected-clear" href="#clear"><?php _e('Clear selected items', 'gmLang'); ?></a></li>
					</ul>
				</form>

			</div>

		</div>
		<div class="panel-body"></div>
		<?php if(!empty($wpMediaLib)){ ?>
		<table class="table table-striped table-hover table-condenced" cellspacing="0">
			<col class="cb" style="width:40px;"/>
			<col class="id" style="width:80px;"/>
			<col class="file" style="width:100px;"/>
			<col class="type" style="width:80px;"/>
			<col class="title"/>
			<col class="descr hidden-xs"/>
			<thead>
			<tr>
				<th class="cb"><span>#</span></th>
				<th class="id">
					<?php $new_order = ('ID' == $arg['orderby'])? (('DESC' == $arg['order'])? 'ASC' : 'DESC') : 'DESC'; ?>
					<a href="<?php echo $gmCore->get_admin_url(array('orderby' => 'ID', 'order' => $new_order)); ?>"><?php _e('ID', 'gmLang'); ?></a>
				</th>
				<th class="file" title="<?php _e('Sort by filename', 'gmLang'); ?>">
					<?php $new_order = ('filename' == $arg['orderby'])? (('DESC' == $arg['order'])? 'ASC' : 'DESC') : 'DESC'; ?>
					<a href="<?php echo $gmCore->get_admin_url(array('orderby' => 'filename',
																	 'order' => $new_order)); ?>"><?php _e('File', 'gmLang'); ?></a>
				</th>
				<th class="type"><span><?php _e('Type', 'gmLang'); ?></span></th>
				<th class="title">
					<?php $new_order = ('title' == $arg['orderby'])? (('DESC' == $arg['order'])? 'ASC' : 'DESC') : 'DESC'; ?>
					<a href="<?php echo $gmCore->get_admin_url(array('orderby' => 'title', 'order' => $new_order)); ?>"><?php _e('Title', 'gmLang'); ?></a>
				</th>
				<th class="descr hidden-xs"><span><?php _e('Description', 'gmLang'); ?></span></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach($wpMediaLib as $item){
				$is_selected = in_array($item->ID, $gmProcessor->selected_items)? true : false;
				$image = wp_get_attachment_image( $item->ID, array( 50, 50 ), false );
				if ( ! $image ) {
					if ( ($src = wp_mime_type_icon( $item->ID )) ) {
						$src_image = $gmCore->gmedia_url . '/admin/images/' . wp_basename( $src );
						$image = '<img src="' . $src_image . '" width="50" height="50" alt="icon" title="' . esc_attr($item->post_title) . '"/>';
					}
				}
				$item_url       = wp_get_attachment_url( $item->ID );
				$file_info = pathinfo( $item_url );
				$type = explode( '/', $item->post_mime_type );
				?>
				<tr data-id="<?php echo $item->ID; ?>">
					<td class="cb">
						<span class="cb_media-object"><input name="doaction[]" type="checkbox" data-type="<?php echo $type[0]; ?>" value="<?php echo $item->ID; ?>"<?php echo $is_selected? ' checked="checked"' : ''; ?>/></span>
					</td>
					<td class="id"><span><?php echo $item->ID; ?></span></td>
					<td class="file"><span><a href="<?php echo admin_url( 'media.php?action=edit&amp;attachment_id=' . $item->ID ); ?>"><?php echo $image; ?></a></span></td>
					<td class="type"><span><?php echo $file_info['extension']; ?></span></td>
					<td class="title"><span><?php echo esc_html($item->post_title); ?></span></td>
					<td class="descr hidden-xs">
						<div><?php echo esc_html($item->post_content); ?></div>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php } else{ ?>
		<div class="panel-body">
			<div class="well well-lg text-center">
				<h4><?php _e('No items to show.', 'gmLang'); ?></h4>
			</div>
		</div>
		<?php } ?>
		<?php
		wp_original_referer_field(true, 'previous');
		wp_nonce_field('GmediaGallery');
		?>
	</div>

	<script type="text/javascript">
		function gmedia_import_done(){
			if(jQuery('#import_window').is(':visible')){
				jQuery('#import-done').button('complete').prop('disabled', false);
			}
		}
	</script>
	<div class="modal fade gmedia-modal" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog"></div>
	</div>

<?php
}
