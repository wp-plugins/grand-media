<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	die('You are not allowed to call this page directly.');
}

/**
 * gmediaLib()
 *
 * @return mixed content
 */
function gmediaLib(){
	global $user_ID, $gmDB, $gmCore, $gmGallery, $gmProcessor;

	$url = add_query_arg(array('page' => $gmProcessor->page, 'mode' => $gmProcessor->mode), admin_url('admin.php'));

	$gm_screen_options = get_user_meta($user_ID, 'gm_screen_options', true);
	if(!is_array($gm_screen_options)){
		$gm_screen_options = array();
	}
	$gm_screen_options = array_merge($gmGallery->options['gm_screen_options'], $gm_screen_options);

	if($gmCore->caps['gmedia_show_others_media']){
		$author = $gmCore->_get('author', 0);
	} else{
		$author = $user_ID;
	}
	$gmedia__in = $gmCore->_get('gmedia__in', null);
	$search_string = $gmCore->_get('s', null);
	if('#' == substr($search_string, 0, 1)){
		$gmedia__in = substr($search_string, 1);
		$search_string = null;
	}
	$orderby = $gm_screen_options['orderby_gmedia'];
	$order = $gm_screen_options['sortorder_gmedia'];
	if(('selected' == $gmCore->_req('filter')) && !empty($gmProcessor->selected_items)){
		$gmedia__in = $gmProcessor->selected_items;
		$orderby = 'gmedia__in';
		$order = 'ASC';
	}
	$args = array(
		'mime_type' => $gmCore->_get('mime_type', null),
		'orderby' => $orderby,
		'order' => $order,
		'per_page' => $gm_screen_options['per_page_gmedia'],
		'page' => $gmCore->_get('pager', 1),
		'tag_id' => $gmCore->_get('tag_id', null),
		'tag__in' => $gmCore->_get('tag__in', null),
		'cat' => $gmCore->_get('cat', null),
		'category__in' => $gmCore->_get('category__in', null),
		'alb' => $gmCore->_get('alb', null),
		'album__in' => $gmCore->_get('album__in', null),
		'author' => $author,
		'gmedia__in' => $gmedia__in,
		's' => $search_string
	);
	$gmediaQuery = $gmDB->get_gmedias($args);

	$gm_qty = array(
		'total' => '',
		'image' => '',
		'audio' => '',
		'video' => '',
		'text' => '',
		'application' => '',
		'other' => ''
	);

	$gmDbCount = $gmDB->count_gmedia();
	foreach($gmDbCount as $key => $value){
		$gm_qty[$key] = '<span class="badge pull-right">' . (int)$value . '</span>';
	}
	?>
	<?php if(!empty($author)){ ?>
		<div class="library-author alert alert-info">
			<strong><?php _e('Selected Author:', 'gmLang'); ?></strong>
			<a href="#libModal" data-modal="filter_authors" data-action="gmedia_get_modal" class="gmedia-modal"><?php echo get_the_author_meta('display_name', $author); ?></a>
		</div>
	<?php } ?>
	<div class="panel panel-default" id="gmedia-panel">
	<div class="panel-heading clearfix">
		<form class="form-inline gmedia-search-form" role="search">
			<div class="form-group">
				<?php foreach($_GET as $key => $value){
					if(in_array($key, array('page', 'mode', 'author', 'mime_type', 'tag_id', 'tag__in', 'cat', 'category__in', 'alb', 'album__in'))){
						?>
						<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>"/>
					<?php
					}
				} ?>
				<input id="gmedia-search" class="form-control input-sm" type="text" name="s" placeholder="<?php _e('Search...', 'gmLang'); ?>" value="<?php echo $gmCore->_get('s', ''); ?>"/>
			</div>
			<button type="submit" class="btn btn-default input-sm"><span class="glyphicon glyphicon-search"></span></button>
		</form>
		<?php echo $gmDB->query_pager(); ?>

		<div class="btn-toolbar pull-left">
			<?php if(!$gmProcessor->mode){ ?>
				<div class="btn-group gm-checkgroup" id="cb_global-btn">
					<span class="btn btn-default active"><input class="doaction" id="cb_global" data-group="cb_media-object" type="checkbox"/></span>
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span>
						<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
					<ul class="dropdown-menu" role="menu">
						<li><a data-select="total" href="#"><?php _e('All', 'gmLang'); ?></a></li>
						<li><a data-select="none" href="#"><?php _e('None', 'gmLang'); ?></a></li>
						<li class="divider"></li>
						<li><a data-select="image" href="#"><?php _e('Images', 'gmLang'); ?></a></li>
						<li><a data-select="audio" href="#"><?php _e('Audio', 'gmLang'); ?></a></li>
						<li><a data-select="video" href="#"><?php _e('Video', 'gmLang'); ?></a></li>
						<li class="divider"></li>
						<li><a data-select="reverse" href="#" title="<?php _e('Reverse only visible items', 'gmLang'); ?>"><?php _e('Reverse', 'gmLang'); ?></a></li>
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
					<li role="presentation" class="dropdown-header"><?php _e('FILTER BY AUTHOR', 'gmLang'); ?></li>
					<li class="gmedia_author">
						<a href="#libModal" data-modal="filter_authors" data-action="gmedia_get_modal" class="gmedia-modal"><?php if(!empty($author)){
								echo get_the_author_meta('display_name', $author);
							} else{
								_e('Show all authors', 'gmLang');
							} ?></a></li>
					<li role="presentation" class="dropdown-header"><?php _e('TYPE', 'gmLang'); ?></li>
					<li class="total<?php if(in_array('total', $curr_mime)){
						echo ' active';
					} ?>"><a rel="total" href="<?php echo $gmCore->get_admin_url(array(), array('mime_type', 'pager')); ?>"><?php echo $gm_qty['total'];
							_e('All', 'gmLang'); ?></a></li>
					<li class="image<?php if(in_array('image', $curr_mime)){
						echo ' active';
					}
					if(!$gmDbCount['image']){
						echo ' disabled';
					} ?>"><a rel="image" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'image'), array('pager')); ?>"><?php echo $gm_qty['image'];
							_e('Images', 'gmLang'); ?></a></li>
					<li class="audio<?php if(in_array('audio', $curr_mime)){
						echo ' active';
					}
					if(!$gmDbCount['audio']){
						echo ' disabled';
					} ?>"><a rel="audio" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'audio'), array('pager')); ?>"><?php echo $gm_qty['audio'];
							_e('Audio', 'gmLang'); ?></a></li>
					<li class="video<?php if(in_array('video', $curr_mime)){
						echo ' active';
					}
					if(!$gmDbCount['video']){
						echo ' disabled';
					} ?>"><a rel="video" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'video'), array('pager')); ?>"><?php echo $gm_qty['video'];
							_e('Video', 'gmLang'); ?></a></li>
					<li class="application<?php if(in_array('application', $curr_mime) || in_array('text', $curr_mime)){
						echo ' active';
					}
					if(!$gmDbCount['application']){
						echo ' disabled';
					} ?>">
						<a rel="application" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'application,text'), array('pager')); ?>"><?php echo $gm_qty['other'];
							_e('Other', 'gmLang'); ?></a></li>
					<li role="presentation" class="dropdown-header"><?php _e('COLLECTIONS', 'gmLang'); ?></li>
					<li class="filter_categories<?php if(isset($gmDB->filter_tax['gmedia_category'])){
						echo ' active';
					} ?>">
						<a href="#libModal" data-modal="filter_categories" data-action="gmedia_get_modal" class="gmedia-modal"><?php _e('Categories', 'gmLang'); ?></a>
					</li>
					<li class="filter_albums<?php if(isset($gmDB->filter_tax['gmedia_album'])){
						echo ' active';
					} ?>"><a href="#libModal" data-modal="filter_albums" data-action="gmedia_get_modal" class="gmedia-modal"><?php _e('Albums', 'gmLang'); ?></a></li>
					<li class="filter_tags<?php if(isset($gmDB->filter_tax['gmedia_tag'])){
						echo ' active';
					} ?>"><a href="#libModal" data-modal="filter_tags" data-action="gmedia_get_modal" class="gmedia-modal"><?php _e('Tags', 'gmLang'); ?></a></li>
					<?php do_action('gmedia_filter_list'); ?>
				</ul>
			</div>

			<div class="btn-group">
				<?php if(!$gmProcessor->mode){
					$args = array('mode' => 'edit');
					$edit_mode_href = $gmCore->get_admin_url($args);
					$args2 = array('mode' => 'edit', 'filter' => 'selected', 'pager' => false, 's' => false);
					$edit_mode_data = 'data-href="' . $edit_mode_href . '" data-href_sel="' . $gmCore->get_admin_url($args2) . '"';
				} else{
					$edit_mode_href = $gmCore->get_admin_url(array(), array('mode'));
					$edit_mode_data = '';
				} ?>
				<?php if($gmCore->caps['gmedia_edit_media']){ ?>
					<a class="btn btn-default edit-mode-link" title="<?php _e('Toggle Edit Mode', 'gmLang'); ?>" href="<?php echo $edit_mode_href; ?>" <?php echo $edit_mode_data; ?>><?php _e('Action', 'gmLang'); ?></a>
				<?php } else{ ?>
					<button type="button" class="btn btn-default"><?php _e('Action', 'gmLang'); ?></button>
				<?php } ?>
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span>
					<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
				<?php
				$rel_selected_show = 'rel-selected-show';
				$rel_selected_hide = 'rel-selected-hide';
				?>
				<ul class="dropdown-menu" role="menu">
					<?php if(!$gmProcessor->mode){ ?>
						<li class="<?php if(!$gmCore->caps['gmedia_edit_media']){
							echo 'disabled';
						} ?>"><a class="edit-mode-link" href="<?php echo $edit_mode_href; ?>" <?php echo $edit_mode_data; ?>><?php _e('Enter Edit Mode', 'gmLang'); ?></a>
						</li>
						<li class="<?php echo $rel_selected_show;
						if(!$gmCore->caps['gmedia_edit_media']){
							echo ' disabled';
						} ?>">
							<a href="#libModal" data-modal="batch_edit" data-action="gmedia_get_modal" class="gmedia-modal"><?php _e('Batch Edit', 'gmLang'); ?></a>
						</li>

						<li class="divider"></li>
						<li class="<?php echo $rel_selected_show;
						if(!$gmCore->caps['gmedia_gallery_manage']){
							echo ' disabled';
						} ?>">
							<a href="#libModal" data-modal="quick_gallery" data-action="gmedia_get_modal" class="gmedia-modal"><?php _e('Quick Gallery from Selected', 'gmLang'); ?></a>
						</li>
						<li class="<?php echo $rel_selected_show;
						if(!$gmCore->caps['gmedia_terms']){
							echo ' disabled';
						} ?>">
							<a href="#libModal" data-modal="assign_category" data-action="gmedia_get_modal" class="gmedia-modal"><?php _e('Assign Category...', 'gmLang'); ?></a>
						</li>
						<li class="<?php echo $rel_selected_show;
						if(!$gmCore->caps['gmedia_terms']){
							echo ' disabled';
						} ?>">
							<a href="#libModal" data-modal="assign_album" data-action="gmedia_get_modal" class="gmedia-modal"><?php _e('Move to Album...', 'gmLang'); ?></a>
						</li>
						<li class="<?php echo $rel_selected_show;
						if(!$gmCore->caps['gmedia_terms']){
							echo ' disabled';
						} ?>"><a href="#libModal" data-modal="add_tags" data-action="gmedia_get_modal" class="gmedia-modal"><?php _e('Add Tags...', 'gmLang'); ?></a>
						</li>
						<li class="<?php echo $rel_selected_show;
						if(!$gmCore->caps['gmedia_terms']){
							echo ' disabled';
						} ?>">
							<a href="#libModal" data-modal="delete_tags" data-action="gmedia_get_modal" class="gmedia-modal"><?php _e('Delete Tags...', 'gmLang'); ?></a>
						</li>
						<li class="<?php echo $rel_selected_show;
						if(!$gmCore->caps['gmedia_delete_media']){
							echo ' disabled';
						} ?>">
							<a href="<?php echo wp_nonce_url($gmCore->get_admin_url(array('delete' => 'selected'), array('filter')), 'gmedia_delete') ?>" class="gmedia-delete" data-confirm="<?php _e("You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang"); ?>"><?php _e('Delete Selected Items', 'gmLang'); ?></a>
						</li>

						<li class="divider <?php echo $rel_selected_show; ?>"></li>
						<li class="<?php echo $rel_selected_show;
						if(!$gmCore->caps['gmedia_edit_media']){
							echo ' disabled';
						} ?>">
							<a href="<?php echo wp_nonce_url($gmCore->get_admin_url(array('update_meta' => 'selected'), array()), 'gmedia_update_meta') ?>" class="gmedia-update"><?php _e('Update Metadata in Database', 'gmLang'); ?></a>
						</li>

						<li class="dropdown-header <?php echo $rel_selected_hide; ?>"><span><?php _e("Select items to see more actions", "gmLang"); ?></span></li>
					<?php } else{ ?>
						<li><a href="<?php echo $edit_mode_href; ?>"><?php _e('Exit Edit Mode', 'gmLang'); ?></a></li>
					<?php
					}
					do_action('gmedia_action_list');
					?>
				</ul>
			</div>

			<?php
			$filter_selected = $gmCore->_req('filter');
			$filter_selected_arg = $filter_selected? false : 'selected';
			?>
			<form class="btn-group" id="gm-selected-btn" name="gm-selected-form" action="<?php echo add_query_arg(array('filter' => $filter_selected_arg), $url); ?>" method="post">
				<button type="submit" class="btn btn<?php echo ('selected' == $filter_selected)? '-success' : '-info' ?>"><?php printf(__('%s selected', 'gmLang'), '<span id="gm-selected-qty">' . count($gmProcessor->selected_items) . '</span>'); ?></button>
				<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown"><span class="caret"></span>
					<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
				<input type="hidden" id="gm-selected" data-userid="<?php echo $user_ID; ?>" data-key="library" name="selected_items" value="<?php echo implode(',', $gmProcessor->selected_items); ?>"/>
				<ul class="dropdown-menu" role="menu">
					<li><a id="gm-selected-show" href="#show"><?php
							if(!$filter_selected){
								_e('Show only selected items', 'gmLang');
							} else{
								_e('Show all gmedia items', 'gmLang');
							}
							?></a></li>
					<li><a id="gm-selected-clear" href="#clear"><?php _e('Clear selected items', 'gmLang'); ?></a></li>
					<li class="<?php if(!$gmCore->caps['gmedia_gallery_manage']){
						echo 'disabled';
					} ?>">
						<a href="#libModal" data-modal="quick_gallery" data-action="gmedia_get_modal" class="gmedia-modal"><?php _e('Quick Gallery from Selected', 'gmLang'); ?></a>
					</li>
				</ul>
			</form>

		</div>

	</div>
	<div class="panel-body"></div>
	<div class="list-group" id="gm-list-table">
	<?php
	if(count($gmediaQuery)){
	if($gmProcessor->mode && $gmCore->caps['gmedia_show_others_media'] && !$gmCore->caps['gmedia_edit_others_media']){
		?>
		<div class="alert alert-warning alert-dismissible" role="alert" style="margin-bottom:0">
			<button type="button" class="close" data-dismiss="alert">
				<span aria-hidden="true">&times;</span><span class="sr-only"><?php _e('Close', 'gmLang'); ?></span></button>
			<strong><?php _e('Info:', 'gmLang'); ?></strong> <?php _e('You are not allowed to edit others media', 'gmLang'); ?>
		</div>
	<?php
	}

	foreach ($gmediaQuery as $item) {
	$meta = $gmDB->get_metadata('gmedia', $item->ID);
	$type = explode('/', $item->mime_type);
	$item_url = $gmCore->upload['url'] . '/' . $gmGallery->options['folder'][$type[0]] . '/' . $item->gmuid;
	$item_path = $gmCore->upload['path'] . '/' . $gmGallery->options['folder'][$type[0]] . '/' . $item->gmuid;

	if(function_exists('exif_imagetype')){
		$is_webimage = (('image' == $type[0]) && in_array(exif_imagetype($item_path), array(
				IMAGETYPE_GIF,
				IMAGETYPE_JPEG,
				IMAGETYPE_PNG
			)))? true : false;
	} else{
		$is_webimage = (('image' == $type[0]) && in_array($type[1], array('jpeg', 'png', 'gif')))? true : false;
	}

	$tags = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_tag');
	$albs = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_album');
	$cats = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_category');
	?>
	<?php if (!$gmProcessor->mode){
	$is_selected = in_array($item->ID, $gmProcessor->selected_items)? true : false; ?>
		<div class="list-group-item d-row clearfix<?php echo $is_selected? ' active' : ''; ?>" id="list-item-<?php echo $item->ID; ?>" data-id="<?php echo $item->ID; ?>" data-type="<?php echo $type[0]; ?>">
			<div class="gmedia_id">#<?php echo $item->ID; ?></div>
			<label class="cb_media-object">
				<input name="doaction[]" type="checkbox"<?php echo $is_selected? ' checked="checked"' : ''; ?> data-type="<?php echo $type[0]; ?>" class="hidden" value="<?php echo $item->ID; ?>"/>
				<span data-target="<?php echo $item_url; ?>" class="thumbnail">
					<img class="gmedia-thumb" src="<?php echo $gmCore->gm_get_media_image($item, 'thumb'); ?>" alt=""/>
					<?php if(('image' != $type[0]) && isset($meta['cover'][0]) && !empty($meta['cover'][0])){ ?>
						<img class="gmedia-typethumb" src="<?php echo $gmCore->gm_get_media_image($item, 'thumb', false); ?>" alt=""/>
					<?php } ?>
				</span>
			</label>

			<div class="media-body">
				<div class="col-md-6">
					<p class="media-title"><?php echo esc_html($item->title); ?>&nbsp;</p>

					<p class="media-caption"><?php echo esc_html($item->description); ?></p>

					<p class="media-meta"><span class="label label-default"><?php _e('Album', 'gmLang'); ?>:</span>
						<?php
						if($albs){
							$terms_album = array();
							foreach($albs as $c){
								$terms_album[] = sprintf('<a class="album" href="%s">%s</a>', esc_url(add_query_arg(array('alb' => $c->term_id), $url)), esc_html($c->name));
							}
							$terms_album = join(', ', $terms_album);
						} else{
							$terms_album = sprintf('<a class="album" href="%s">%s</a>', esc_url(add_query_arg(array('alb' => 0), $url)), '&#8212;');
						}
						echo $terms_album;

						if($is_webimage){
							?>
							<br/><span class="label label-default"><?php _e('Category', 'gmLang'); ?>:</span>
							<?php
							if($cats){
								$terms_category = array();
								foreach($cats as $c){
									$terms_category[] = sprintf('<a class="category" href="%s">%s</a>', esc_url(add_query_arg(array('cat' => $c->term_id), $url)), esc_html($gmGallery->options['taxonomies']['gmedia_category'][$c->name]));
								}
								$terms_category = join(', ', $terms_category);
							} else{
								$terms_category = sprintf('<a class="category" href="%s">%s</a>', esc_url(add_query_arg(array('cat' => 0), $url)), __('Uncategorized', 'gmLang'));
							}
							echo $terms_category;
						} ?>
						<br/><span class="label label-default"><?php _e('Tags', 'gmLang'); ?>:</span>
						<?php
						if($tags){
							$terms_tag = array();
							foreach($tags as $c){
								$terms_tag[] = sprintf('<a class="tag" href="%s">%s</a>', esc_url(add_query_arg(array('tag_id' => $c->term_id), $url)), esc_html($c->name));
							}
							$terms_tag = join(', ', $terms_tag);
						} else{
							$terms_tag = '&#8212;';
						}
						echo $terms_tag;
						?>
					</p>
				</div>
				<div class="col-md-6">
					<div class="media-meta">
						<span class="label label-default"><?php _e('Type', 'gmLang'); ?>:</span> <?php echo $item->mime_type; //echo ucfirst($type[0]); ?>
					</div>
					<?php if('image' == $type[0]){
						$_metadata = unserialize($meta['_metadata'][0]);
						?>
						<div class="media-meta">
							<span class="label label-default"><?php _e('Size', 'gmLang'); ?>
								:</span> <?php echo $_metadata['original']['width'] . ' × ' . $_metadata['original']['height']; ?>
						</div>
					<?php } ?>
					<div class="media-meta"><span class="label label-default"><?php _e('Filename', 'gmLang'); ?>:</span>
						<a href="<?php echo $item_url; ?>"><?php echo $item->gmuid; ?></a></div>
					<div class="media-meta">
						<span class="label label-default"><?php _e('Author', 'gmLang'); ?>
							:</span> <?php printf('<a class="gmedia-author" href="%s">%s</a>', esc_url(add_query_arg(array('author' => $item->author), $url)), get_user_option('display_name', $item->author)); ?>
					</div>
					<div class="media-meta"><span class="label label-default"><?php _e('Date', 'gmLang'); ?>:</span> <?php echo $item->date;
						echo ' <small class="modified" title="' . __('Last Modified Date', 'gmLang') . '">' . (($item->modified != $item->date)? $item->modified : '') . '</small>';
						?></div>
					<div class="media-meta"><span class="label label-default"><?php _e('Link', 'gmLang'); ?>:</span>
						<?php if(!empty($item->link)){ ?>
							<a href="<?php echo $item->link; ?>"><?php echo $item->link; ?></a>
						<?php
						} else{
							echo '&#8212;';
						} ?></div>
					<p class="media-meta" style="margin:5px 4px;">
						<?php $media_action_links = array();
						if(($gmCore->caps['gmedia_edit_media'] && ((int)$item->author == get_current_user_id())) || $gmCore->caps['gmedia_edit_others_media']){
							$media_action_links[] = '<a href="' . admin_url("admin.php?page=GrandMedia&mode=edit&gmedia__in={$item->ID}") . '">' . __('Edit Data', 'gmLang') . '</a>';
						}
						if('image' == $type[0]){
							if(($gmCore->caps['gmedia_edit_media'] && ((int)$item->author == get_current_user_id())) || $gmCore->caps['gmedia_edit_others_media']){
								$media_action_links[] = '<a href="' . admin_url("admin.php?page=GrandMedia&gmediablank=image_editor&id={$item->ID}") . '" data-target="#gmeditModal" class="gmedit-modal">' . __('Edit Image', 'gmLang') . '</a>';
							}
							$media_action_links[] = '<a href="' . $gmCore->gm_get_media_image($item, 'original') . '" data-target="#previewModal" class="preview-modal">' . __('View Original', 'gmLang') . '</a>';

						}
						if(($gmCore->caps['gmedia_delete_media'] && ((int)$item->author == get_current_user_id())) || $gmCore->caps['gmedia_delete_others_media']){
							$media_action_links[] = '<a class="text-danger" href="' . wp_nonce_url($gmCore->get_admin_url(array('delete' => $item->ID)), 'gmedia_delete') . '" data-confirm="' . sprintf(__("You are about to permanently delete %s file.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang"), $item->gmuid) . '">' . __('Delete', 'gmLang') . '</a>';
						}
						echo implode(' | ', $media_action_links);
						?>
					</p>
				</div>
			</div>
		</div>

	<?php } elseif($gmCore->caps['gmedia_edit_media']){ ?>
		<?php if(((int)$item->author != $user_ID) && !$gmCore->caps['gmedia_edit_others_media']){ ?>
		<div class="list-group-item row d-row" id="list-item-<?php echo $item->ID; ?>" data-id="<?php echo $item->ID; ?>" data-type="<?php echo $type[0]; ?>">
			<div class="gmedia_id">#<?php echo $item->ID; ?></div>
			<div class="li_media-object">
				<span data-target="<?php echo $item_url; ?>" class="thumbnail">
					<img class="gmedia-thumb" src="<?php echo $gmCore->gm_get_media_image($item, 'thumb'); ?>" alt=""/>
					<?php if(('image' != $type[0]) && isset($meta['cover'][0]) && !empty($meta['cover'][0])){ ?>
						<img class="gmedia-typethumb" src="<?php echo $gmCore->gm_get_media_image($item, 'thumb', false); ?>" alt=""/>
					<?php } ?>
				</span>
			</div>

			<div class="media-body">
				<div class="col-md-6">
					<p class="media-title"><?php echo esc_html($item->title); ?>&nbsp;</p>

					<p class="media-caption"><?php echo esc_html($item->description); ?></p>

					<p class="media-meta"><span class="label label-default"><?php _e('Album', 'gmLang'); ?>:</span>
						<?php
						if($albs){
							$terms_album = array();
							foreach($albs as $c){
								$terms_album[] = sprintf('<span class="album">%s</span>', esc_html($c->name));
							}
							$terms_album = join(', ', $terms_album);
						} else{
							$terms_album = '<span class="album">&#8212;</span>';
						}
						echo $terms_album;

						if($is_webimage){
							?>
							<br/><span class="label label-default"><?php _e('Category', 'gmLang'); ?>:</span>
							<?php
							if($cats){
								$terms_category = array();
								foreach($cats as $c){
									$terms_category[] = sprintf('<span class="category">%s</span>', esc_html($gmGallery->options['taxonomies']['gmedia_category'][$c->name]));
								}
								$terms_category = join(', ', $terms_category);
							} else{
								$terms_category = sprintf('<span class="category">%s</span>', __('Uncategorized'));
							}
							echo $terms_category;
						} ?>
						<br/><span class="label label-default"><?php _e('Tags', 'gmLang'); ?>:</span>
						<?php
						if($tags){
							$terms_tag = array();
							foreach($tags as $c){
								$terms_tag[] = sprintf('<span class="tag">%s</span>', esc_html($c->name));
							}
							$terms_tag = join(', ', $terms_tag);
						} else{
							$terms_tag = '&#8212;';
						}
						echo $terms_tag;
						?>
					</p>
				</div>
				<div class="col-md-6">
					<div class="media-meta">
						<span class="label label-default"><?php _e('Type', 'gmLang'); ?>:</span> <?php echo $item->mime_type; //echo ucfirst($type[0]); ?>
					</div>
					<?php if('image' == $type[0]){
						$_metadata = unserialize($meta['_metadata'][0]);
						?>
						<div class="media-meta">
							<span class="label label-default"><?php _e('Size', 'gmLang'); ?>
								:</span> <?php echo $_metadata['original']['width'] . ' × ' . $_metadata['original']['height']; ?>
						</div>
					<?php } ?>
					<div class="media-meta"><span class="label label-default"><?php _e('Filename', 'gmLang'); ?>:</span>
						<a href="<?php echo $item_url; ?>"><?php echo $item->gmuid; ?></a></div>
					<div class="media-meta">
						<span class="label label-default"><?php _e('Author', 'gmLang'); ?>
							:</span> <?php printf('<span class="gmedia-author">%s</a>', get_user_option('display_name', $item->author)); ?>
					</div>
					<div class="media-meta"><span class="label label-default"><?php _e('Date', 'gmLang'); ?>:</span> <?php echo $item->date;
						echo ' <small class="modified" title="' . __('Last Modified Date', 'gmLang') . '">' . (($item->modified != $item->date)? $item->modified : '') . '</small>';
						?></div>
					<div class="media-meta"><span class="label label-default"><?php _e('Link', 'gmLang'); ?>:</span>
						<?php if(!empty($item->link)){ ?>
							<a href="<?php echo $item->link; ?>"><?php echo $item->link; ?></a>
						<?php
						} else{
							echo '&#8212;';
						} ?></div>
					<?php if('image' == $type[0]){ ?>
						<p class="media-meta" style="margin:5px 4px;">
							<a href="<?php echo $gmCore->gm_get_media_image($item, 'original'); ?>" data-target="#previewModal" class="preview-modal" title="<?php echo esc_attr($item->title); ?>">
								<?php _e('View Original', 'gmLang'); ?>
							</a>
						</p>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
		continue;
	}
		?>
		<form class="list-group-item row d-row edit-gmedia" id="list-item-<?php echo $item->ID; ?>" data-id="<?php echo $item->ID; ?>" data-type="<?php echo $type[0]; ?>" role="form">
			<div class="col-sm-4" style="max-width:350px;">
				<input name="ID" type="hidden" value="<?php echo $item->ID; ?>"/>
				<?php if(('image' == $type[0])){ ?>
					<a href="<?php echo $item_url; ?>" data-target="#previewModal" class="thumbnail preview-modal" title="<?php echo esc_attr($item->title); ?>">
						<img class="gmedia-thumb" src="<?php echo $gmCore->gm_get_media_image($item, 'thumb'); ?>" alt=""/>
					</a>
					<p>
						<a href="<?php echo admin_url("admin.php?page=GrandMedia&gmediablank=image_editor&id={$item->ID}"); ?>" data-target="#gmeditModal" class="btn btn-link btn-sm gmedit-modal">
							<?php _e('Edit Image', 'gmLang'); ?>
						</a> |
						<a href="<?php echo $gmCore->gm_get_media_image($item, 'original'); ?>" data-target="#previewModal" class="btn btn-link btn-sm preview-modal">
							<?php _e('View Original', 'gmLang'); ?>
						</a>
						<?php if(($gmCore->caps['gmedia_delete_media'] && ((int)$item->author == get_current_user_id())) || $gmCore->caps['gmedia_delete_others_media']){ ?>
							|
							<a class="btn btn-link btn-sm text-danger" href="<?php echo wp_nonce_url($gmCore->get_admin_url(array('delete' => $item->ID)), 'gmedia_delete') ?>" data-confirm="<?php printf(__("You are about to permanently delete %s file.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang"), $item->gmuid); ?>">
								<?php _e('Delete', 'gmLang'); ?>
							</a>
						<?php } ?>
					</p>
				<?php } else{ ?>
					<a href="<?php echo $item_url; ?>" data-target="#previewModal" class="thumbnail preview-modal" title="<?php echo esc_attr($item->title); ?>">
						<img class="gmedia-thumb" src="<?php echo $gmCore->gm_get_media_image($item, 'thumb'); ?>" alt=""/>
						<?php if(isset($meta['cover'][0]) && !empty($meta['cover'][0])){ ?>
							<img class="gmedia-typethumb" src="<?php echo $gmCore->gm_get_media_image($item, 'thumb', false); ?>" alt=""/>
						<?php } ?>
					</a>
				<?php } ?>
			</div>
			<div class="col-sm-8">
				<div class="row">
					<div class="form-group col-lg-6">
						<label><?php _e('Title', 'gmLang'); ?></label>
						<input name="title" type="text" class="form-control input-sm" placeholder="<?php _e('Title', 'gmLang'); ?>" value="<?php echo esc_attr($item->title); ?>">
					</div>
					<div class="form-group col-lg-6">
						<label><?php _e('Link URL', 'gmLang'); ?></label>
						<input name="link" type="text" class="form-control input-sm" value="<?php echo $item->link; ?>"/>
					</div>
				</div>
				<div class="row">
					<div class="form-group col-lg-6">
						<label><?php _e('Description', 'gmLang'); ?></label>
						<?php if('false' == $gm_screen_options['library_edit_quicktags']){
							echo "<textarea id='gm{$item->ID}_description' class='form-control input-sm' name='description' cols='20' rows='4' style='height:174px'>" . esc_html($item->description) . '</textarea>';
						} else{
							wp_editor(esc_html($item->description), "gm{$item->ID}_description", array(
								'editor_class' => 'form-control input-sm',
								'editor_height' => 140,
								'wpautop' => false,
								'media_buttons' => false,
								'textarea_name' => 'description',
								'textarea_rows' => '4',
								'tinymce' => false,
								'quicktags' => array('buttons' => apply_filters('gmedia_editor_quicktags', 'strong,em,link,ul,li,close'))
							));
						} ?>
					</div>
					<div class="col-lg-6">
						<?php if(('image' != $type[0])){ ?>
							<div class="form-group">
								<label><?php _e('Cover', 'gmLang'); ?></label>
								<input name="meta[cover]" type="text" class="form-control input-sm gmedia-cover" value="<?php if(isset($meta['cover'][0])){
									echo $meta['cover'][0];
								} ?>" placeholder="<?php _e('Gmedia ID or Image URL', 'gmLang'); ?>"/>
							</div>
						<?php } ?>
						<?php if($gmCore->caps['gmedia_terms']){ ?>
							<?php if($is_webimage){ ?>
								<div class="form-group">
									<?php
									$cat_name = empty($cats)? 0 : reset($cats)->name;
									$term_type = 'gmedia_category';
									$gm_terms = $gmGallery->options['taxonomies'][$term_type];

									$terms_category = '';
									if(count($gm_terms)){
										foreach($gm_terms as $term_name => $term_title){
											$selected_option = ($cat_name === $term_name)? ' selected="selected"' : '';
											$terms_category .= '<option' . $selected_option . ' value="' . $term_name . '">' . esc_html($term_title) . '</option>' . "\n";
										}
									}
									?>
									<label><?php _e('Category', 'gmLang'); ?> </label>
									<select name="terms[gmedia_category]" class="gmedia_category form-control input-sm">
										<option<?php echo $cat_name? '' : ' selected="selected"'; ?> value=""><?php _e('Uncategorized', 'gmLang'); ?></option>
										<?php echo $terms_category; ?>
									</select>
								</div>
							<?php } ?>

							<div class="form-group">
								<?php
								$alb_id = empty($albs)? 0 : reset($albs)->term_id;
								$term_type = 'gmedia_album';
								$args = array();
								if(!$gmCore->caps['gmedia_edit_others_media']){
									$args = array('global' => array(0, $user_ID), 'orderby' => 'global_desc_name');
								}
								$gm_terms = $gmDB->get_terms($term_type, $args);

								$terms_album = '';
								if(count($gm_terms)){
									foreach($gm_terms as $term){
										$author_name = '';
										if($term->global){
											if($gmCore->caps['gmedia_edit_others_media']){
												$author_name .= ' &nbsp; ' . sprintf(__('by %s', 'gmLang'), get_the_author_meta('display_name', $term->global));
											}
										} else{
											$author_name .= ' &nbsp; (' . __('shared', 'gmLang') . ')';
										}
										$selected_option = ($alb_id == $term->term_id)? ' selected="selected"' : '';
										$terms_album .= '<option' . $selected_option . ' value="' . $term->term_id . '">' . esc_html($term->name) . $author_name . '</option>' . "\n";
									}
								}
								?>
								<label><?php _e('Album ', 'gmLang'); ?></label>
								<select name="terms[gmedia_album]" class="combobox_gmedia_album form-control input-sm" placeholder="<?php _e('Album Name...', 'gmLang'); ?>">
									<option<?php echo $alb_id? '' : ' selected="selected"'; ?> value=""></option>
									<?php echo $terms_album; ?>
								</select>
							</div>
							<div class="form-group">
								<?php
								if(!empty($tags)){
									$terms_tag = array();
									foreach($tags as $c){
										$terms_tag[] = esc_html($c->name);
									}
									$terms_tag = join(', ', $terms_tag);
								} else{
									$terms_tag = '';
								}
								?>
								<label><?php _e('Tags ', 'gmLang'); ?></label>
								<textarea name="terms[gmedia_tag]" class="gmedia_tags_input form-control input-sm" rows="1" cols="50"><?php echo $terms_tag; ?></textarea>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label><?php _e('Filename', 'gmLang'); ?></label>
							<input name="filename" type="text" class="form-control input-sm gmedia-filename" <?php if((int)$item->author !== $user_ID){
								echo 'readonly';
							} ?> value="<?php echo pathinfo($item->gmuid, PATHINFO_FILENAME); ?>"/>
						</div>
						<div class="form-group">
							<label><?php _e('Date', 'gmLang'); ?></label>

							<div class="input-group date input-group-sm" data-date-format="YYYY-MM-DD HH:mm:ss">
								<input name="date" type="text" readonly="readonly" class="form-control input-sm" value="<?php echo $item->date; ?>"/>
								<span class="input-group-btn"><button type="button" class="btn btn-primary">
										<span class="glyphicon glyphicon-calendar"></span></button></span>
							</div>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-group">
							<label><?php _e('Author', 'gmLang'); ?></label>
							<?php $user_ids = $gmCore->caps['gmedia_edit_others_media']? $gmCore->get_editable_user_ids() : false;
							if($user_ids){
								wp_dropdown_users(array(
									'include' => $user_ids,
									'include_selected' => true,
									'name' => 'author',
									'selected' => $item->author,
									'class' => 'form-control',
									'multi' => true
								));
							} else{
								echo '<input type="hidden" name="author" value="' . $item->author . '"/>';
								echo '<div>' . get_the_author_meta('display_name', $item->author) . '</div>';
							}
							?>
						</div>
						<div class="media-meta"><span class="label label-default"><?php _e('ID', 'gmLang') ?>:</span> <strong><?php echo $item->ID; ?></strong></div>
						<div class="media-meta"><span class="label label-default"><?php _e('Type', 'gmLang') ?>
								:</span> <?php echo $item->mime_type; //echo ucfirst($type[0]); ?></div>
						<div class="media-meta"><span class="label label-default"><?php _e('File Size', 'gmLang') ?> :</span> <?php echo $gmCore->filesize($item_path); ?>
						</div>
						<?php if('image' == $type[0]){
							$_metadata = unserialize($meta['_metadata'][0]); ?>
							<div class="media-meta"><span class="label label-default"><?php _e('Dimensions', 'gmLang') ?>
									:</span> <?php echo $_metadata['original']['width'] . ' × ' . $_metadata['original']['height']; ?></div>
						<?php } ?>
						<div class="media-meta"><span class="label label-default"><?php _e('Last Edited', 'gmLang') ?>:</span>
							<span class="gm-last-edited modified"><?php echo $item->modified; ?></span></div>
					</div>
				</div>
				<?php do_action('gmedia_edit_form'); ?>
			</div>
		</form>
	<?php } ?>
	<?php } ?>
		<script type="text/javascript">
			jQuery(function($){
				<?php if(!$gmProcessor->mode){ ?>
				$('#gm-selected').on('change',function(){
					var val = $(this).val();
					$('.edit-mode-link').each(function(){
						if(val){
							$(this).attr('href', $(this).data('href_sel'));
						} else{
							$(this).attr('href', $(this).data('href'));
						}
					});
				}).trigger('change');

				<?php } else { ?>
				<?php if($gmCore->caps['gmedia_terms']){ ?>
				$('.combobox_gmedia_album').selectize({
					create: <?php echo $gmCore->caps['gmedia_album_manage']? 'true' : 'false' ?>,
					persist: false
				});
				<?php } ?>

				var gmedia_date_temp;
				$('.input-group.date').datetimepicker({useSeconds: true}).on('dp.show',function(e){
					gmedia_date_temp = $('input', this).val();
				}).on('dp.hide', function(e){
					if(gmedia_date_temp != $('input', this).val()){
						$('input', this).trigger('change');
					}
				});

				var inp_filename = $('input.gmedia-filename').not('[readonly]');
				if(inp_filename.length){
					inp_filename.alphanum({
						allow: '-_',
						disallow: '',
						allowSpace: false,
						allowNumeric: true,
						allowUpper: true,
						allowLower: true,
						allowCaseless: true,
						allowLatin: true,
						allowOtherCharSets: false,
						forceUpper: false,
						forceLower: false,
						maxLength: NaN
					});
				}

				<?php } ?>
			});
			window.closeModal = function(id){
				jQuery('#' + id).modal('hide');
			};
		</script>
	<?php } else{ ?>
		<div class="list-group-item">
			<div class="well well-lg text-center">
				<h4><?php _e('No items to show.', 'gmLang'); ?></h4>
				<?php if($gmCore->caps['gmedia_upload']){ ?>
					<p>
						<a href="<?php echo admin_url('admin.php?page=GrandMedia_AddMedia') ?>" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add Media', 'gmLang'); ?>
						</a></p>
				<?php } ?>
			</div>
		</div>
	<?php } ?>
	</div>

	<div class="panel-footer clearfix">
		<?php echo $gmDB->query_pager(); ?>

		<a href="#top" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-up"></span> <?php _e('Back to top', 'gmLang'); ?></a>
	</div>

	<?php
	wp_original_referer_field(true, 'previous');
	wp_nonce_field('GmediaGallery');
	?>
	</div>

	<div class="modal fade gmedia-modal" id="libModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog"></div>
	</div>
	<?php if($gmCore->caps['gmedia_edit_media']){ ?>
		<div class="modal fade gmedia-modal" id="gmeditModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content"></div>
			</div>
		</div>
	<?php } ?>
	<div class="modal fade gmedia-modal" id="previewModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body"></div>
			</div>
		</div>
	</div>
<?php
}
