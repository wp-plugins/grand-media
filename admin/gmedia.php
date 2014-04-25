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


	$gmedia__in = ('selected' == $gmCore->_req('filter'))? $gmProcessor->selected_items : null;
	$args = array('mime_type' => $gmCore->_get('mime_type', null), 'orderby' => $gm_screen_options['orderby_gmedia'],
				  'order' => $gm_screen_options['sortorder_gmedia'],
				  'per_page' => $gm_screen_options['per_page_gmedia'], 'page' => $gmCore->_get('pager', 1),
				  'tag_id' => $gmCore->_get('tag_id', null), 'tag__in' => $gmCore->_get('tag__in', null),
				  'cat' => $gmCore->_get('cat', null), 'category__in' => $gmCore->_get('category__in', null),
				  'alb' => $gmCore->_get('alb', null), 'album__in' => $gmCore->_get('album__in', null),
				  'gmedia__in' => $gmedia__in, 's' => $gmCore->_get('s', null));
	$gmediaQuery = $gmDB->get_gmedias($args);

	$gm_qty = array('total' => '', 'image' => '', 'audio' => '', 'video' => '', 'text' => '', 'application' => '',
					'other' => '');

	$gmDbCount = $gmDB->count_gmedia();
	foreach($gmDbCount as $key => $value){
		$gm_qty[$key] = '<span class="badge pull-right">' . (int)$value . '</span>';
	}

	?>
	<div class="panel panel-default">
	<div class="panel-heading clearfix">
		<form class="form-inline gmedia-search-form" role="search">
			<div class="form-group">
				<?php foreach($_GET as $key => $value){
					if(in_array($key, array('mime_type', 'tag_id', 'tag__in', 'cat', 'category__in', 'alb', 'album__in'))){ ?>
					<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>"/>
				<?php }
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
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span> <span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
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
					<li role="presentation" class="dropdown-header"><?php _e('TYPE', 'gmLang'); ?></li>
					<li class="total<?php if(in_array('total', $curr_mime)){ echo ' active'; } ?>"><a rel="total" href="<?php echo $gmCore->get_admin_url(array(), array('mime_type','pager')); ?>"><?php _e('All', 'gmLang'); echo $gm_qty['total']; ?></a></li>
					<li class="image<?php if(in_array('image', $curr_mime)){ echo ' active'; } if(!$gmDbCount['image']){ echo ' disabled'; } ?>"><a rel="image" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'image'), array('pager')); ?>"><?php _e('Images', 'gmLang'); echo $gm_qty['image']; ?></a></li>
					<li class="audio<?php if(in_array('audio', $curr_mime)){ echo ' active'; } if(!$gmDbCount['audio']){ echo ' disabled'; } ?>"><a rel="audio" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'audio'), array('pager')); ?>"><?php _e('Audio', 'gmLang'); echo $gm_qty['audio']; ?></a></li>
					<li class="video<?php if(in_array('video', $curr_mime)){ echo ' active'; } if(!$gmDbCount['video']){ echo ' disabled'; } ?>"><a rel="video" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'video'), array('pager')); ?>"><?php _e('Video', 'gmLang'); echo $gm_qty['video']; ?></a></li>
					<li class="application<?php if(in_array('application', $curr_mime) || in_array('text', $curr_mime)){ echo ' active'; } if(!$gmDbCount['application']){ echo ' disabled'; } ?>"><a rel="application" href="<?php echo $gmCore->get_admin_url(array('mime_type' => 'application,text'), array('pager')); ?>"><?php _e('Other', 'gmLang'); echo $gm_qty['other']; ?></a></li>
					<li role="presentation" class="dropdown-header"><?php _e('COLLECTIONS', 'gmLang'); ?></li>
					<li class="filter_categories<?php if(isset($gmDB->filter_tax['gmedia_category'])){ echo ' active'; } ?>"><a href="#termsModal" data-modal="filter_categories" data-action="gmedia_terms_modal" class="gmedia-modal"><?php _e('Categories', 'gmLang'); ?></a></li>
					<li class="filter_albums<?php if(isset($gmDB->filter_tax['gmedia_album'])){ echo ' active'; } ?>"><a href="#termsModal" data-modal="filter_albums" data-action="gmedia_terms_modal" class="gmedia-modal"><?php _e('Albums', 'gmLang'); ?></a></li>
					<li class="filter_tags<?php if(isset($gmDB->filter_tax['gmedia_tag'])){ echo ' active'; } ?>"><a href="#termsModal" data-modal="filter_tags" data-action="gmedia_terms_modal" class="gmedia-modal"><?php _e('Tags', 'gmLang'); ?></a></li>
					<?php do_action('gmedia_filter_list'); ?>
				</ul>
			</div>

			<div class="btn-group">
				<a class="btn btn-default" title="<?php _e('Toggle Edit Mode', 'gmLang'); ?>" href="<?php if(!$gmProcessor->mode){ echo $gmCore->get_admin_url(array('mode' => 'edit')); } else{ echo $gmCore->get_admin_url(array(), array('mode')); } ?>"><?php _e('Action', 'gmLang'); ?></a>
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span> <span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
				<?php
				$rel_selected_show = 'rel-selected-show';
				$rel_selected_hide = 'rel-selected-hide';
				?>
				<ul class="dropdown-menu" role="menu">
					<?php if(!$gmProcessor->mode){ ?>
						<li><a href="<?php echo $gmCore->get_admin_url(array('mode' => 'edit')); ?>"><?php _e('Enter Edit Mode', 'gmLang'); ?></a></li>
						<li class="<?php echo $rel_selected_show; ?>"><a href="#termsModal" data-modal="assign_category" data-action="gmedia_terms_modal" class="gmedia-modal"><?php _e('Assign Category...', 'gmLang'); ?></a></li>
						<li class="<?php echo $rel_selected_show; ?>"><a href="#termsModal" data-modal="assign_album" data-action="gmedia_terms_modal" class="gmedia-modal"><?php _e('Move to Album...', 'gmLang'); ?></a></li>
						<li class="<?php echo $rel_selected_show; ?>"><a href="#termsModal" data-modal="add_tags" data-action="gmedia_terms_modal" class="gmedia-modal"><?php _e('Add Tags...', 'gmLang'); ?></a></li>
						<li class="<?php echo $rel_selected_show; ?>"><a href="#termsModal" data-modal="delete_tags" data-action="gmedia_terms_modal" class="gmedia-modal"><?php _e('Delete Tags...', 'gmLang'); ?></a></li>
						<li class="<?php echo $rel_selected_show; ?>"><a href="<?php echo wp_nonce_url($gmCore->get_admin_url(array('delete' => 'selected'), array('filter')), 'gmedia_delete') ?>" class="gmedia-delete" data-confirm="<?php _e("You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang"); ?>"><?php _e('Delete Selected Items', 'gmLang'); ?></a></li>
						<li class="divider <?php echo $rel_selected_hide; ?>"></li>
						<li class="dropdown-header <?php echo $rel_selected_hide; ?>"><span><?php _e("Select items to see more actions", "gmLang"); ?></span></li>
					<?php } else{ ?>
						<li><a href="<?php echo $gmCore->get_admin_url(array(), array('mode')); ?>"><?php _e('Exit Edit Mode', 'gmLang'); ?></a></li>
					<?php }
					do_action('gmedia_action_list');
					?>
				</ul>
			</div>

			<form class="btn-group" id="gm-selected-btn" name="gm-selected-form" action="<?php echo add_query_arg(array('filter' => 'selected'), $url); ?>" method="post">
				<button type="submit" class="btn btn<?php echo ('selected' == $gmCore->_req('filter'))? '-success' : '-info' ?>"><?php printf(__('%s selected', 'gmLang'), '<span id="gm-selected-qty">' . count($gmProcessor->selected_items) . '</span>'); ?></button>
				<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown"><span class="caret"></span> <span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
				<input type="hidden" id="gm-selected" data-userid="<?php echo $user_ID; ?>" data-key="library" name="selected_items" value="<?php echo implode(',', $gmProcessor->selected_items); ?>"/>
				<ul class="dropdown-menu" role="menu">
					<li><a id="gm-selected-show" href="#show"><?php _e('Show only selected items', 'gmLang'); ?></a></li>
					<li><a id="gm-selected-clear" href="#clear"><?php _e('Clear selected items', 'gmLang'); ?></a></li>
				</ul>
			</form>

		</div>

	</div>
	<div class="panel-body"></div>
	<div class="list-group" id="gm-list-table">
	<?php
	if(count($gmediaQuery)){
	foreach($gmediaQuery as $item) {
		$meta = $gmDB->get_metadata('gmedia', $item->ID);
		$type = explode('/', $item->mime_type);
		$item_url = $gmCore->upload['url'] . '/' . $gmGallery->options['folder'][$type[0]] . '/' . $item->gmuid;
		$item_path = $gmCore->upload['path'] . '/' . $gmGallery->options['folder'][$type[0]] . '/' . $item->gmuid;

		$is_webimage = (('image' == $type[0]) && in_array(exif_imagetype($item_path), array(IMAGETYPE_GIF,
																							IMAGETYPE_JPEG,
																							IMAGETYPE_PNG)))? true : false;

		$tags = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_tag');
		$albs = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_album');
		$cats = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_category');
		?>
		<?php if(!$gmProcessor->mode){
		$is_selected = in_array($item->ID, $gmProcessor->selected_items)? true : false; ?>
		<div class="list-group-item row<?php echo $is_selected? ' active' : ''; ?>" id="list-item-<?php echo $item->ID; ?>" data-id="<?php echo $item->ID; ?>" data-type="<?php echo $type[0]; ?>">
			<div class="gmedia_id">#<?php echo $item->ID; ?></div>
			<div class="col-sm-6">
				<label class="cb_media-object" style="width:310px;">
					<input name="doaction[]" type="checkbox"<?php echo $is_selected? ' checked="checked"' : ''; ?> data-type="<?php echo $type[0]; ?>" class="hidden" value="<?php echo $item->ID; ?>"/>
					<span data-target="<?php echo $item_url; ?>" class="thumbnail">
						<img src="<?php echo $gmCore->gm_get_media_image($item, 'thumb'); ?>" alt=""/>
						<?php if(('image' != $type[0]) && isset($meta['cover'][0]) && !empty($meta['cover'][0])){ ?>
							<img class="gmedia-typethumb" src="<?php echo $gmCore->gm_get_media_image($item, 'thumb', false); ?>" alt=""/>
						<?php } ?>
					</span>
				</label>

				<div class="media-body">
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
								$terms_category = sprintf('<a class="category" href="%s">%s</a>', esc_url(add_query_arg(array('cat' => 0), $url)), __('Uncategorized'));
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
			</div>
			<div class="col-sm-6">
				<div class="media-meta">
					<span class="label label-default"><?php _e('Type','gmLang'); ?>:</span> <?php echo $item->mime_type; //echo ucfirst($type[0]); ?>
				</div>
				<?php if('image' == $type[0]){
					$_metadata = unserialize($meta['_metadata'][0]);
					?>
					<div class="media-meta">
						<span class="label label-default"><?php _e('Size','gmLang'); ?>:</span> <?php echo $_metadata['original']['width'] . ' × ' . $_metadata['original']['height']; ?>
					</div>
				<?php } ?>
				<div class="media-meta"><span class="label label-default"><?php _e('Filename','gmLang'); ?>:</span>
					<a href="<?php echo $item_url; ?>"><?php echo $item->gmuid; ?></a></div>
				<div class="media-meta">
					<span class="label label-default"><?php _e('Author','gmLang'); ?>:</span> <?php printf('<a class="gmedia-author" href="%s">%s</a>', esc_url(add_query_arg(array('author' => $item->author), $url)), get_user_option('display_name', $item->author)); ?>
				</div>
				<div class="media-meta"><span class="label label-default"><?php _e('Date', 'gmLang'); ?>:</span> <?php echo $item->date;
					if($item->modified != $item->date){
						echo ' <small title="' . __('Last Modified Date', 'gmLang') . '">[' . $item->modified . ']</small>';
					} ?></div>
				<div class="media-meta"><span class="label label-default"><?php _e('Link','gmLang'); ?>:</span>
					<?php if(!empty($item->link)){ ?>
						<a href="<?php echo $item->link; ?>"><?php echo $item->link; ?></a>
					<?php
					} else{
						echo '&#8212;';
					} ?></div>
			</div>
		</div>

	<?php } else{ ?>

		<form class="list-group-item row edit-gmedia" id="list-item-<?php echo $item->ID; ?>" data-id="<?php echo $item->ID; ?>" data-type="<?php echo $type[0]; ?>" role="form">
			<div class="col-sm-4">
				<input name="ID" type="hidden" value="<?php echo $item->ID; ?>"/>
				<a href="<?php echo $item_url; ?>" class="thumbnail">
					<img src="<?php echo $gmCore->gm_get_media_image($item, 'thumb'); ?>" alt=""/>
					<?php if(('image' != $type[0]) && isset($meta['cover'][0]) && !empty($meta['cover'][0])){ ?>
						<img class="gmedia-typethumb" src="<?php echo $gmCore->gm_get_media_image($item, 'thumb', false); ?>" alt=""/>
					<?php } ?>
				</a>
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
						<?php wp_editor(esc_html($item->description), "gm{$item->ID}_description", array('editor_class' => 'form-control input-sm',
																												 'editor_height' => 140,
																												 'wpautop' => false,
																												 'media_buttons' => false,
																												 'textarea_name' => 'description',
																												 'textarea_rows' => '4',
																												 'tinymce' => false,
																												 'quicktags' => array('buttons' => apply_filters('gmedia_editor_quicktags', 'strong,em,link,ul,li,close')))); ?>
					</div>
					<div class="col-lg-6">
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
						<?php } elseif(('image' != $type[0])){ ?>
							<div class="form-group">
								<label><?php _e('Cover', 'gmLang'); ?></label>
								<input name="meta[cover]" type="text" class="form-control input-sm gmedia-cover" value="<?php if(isset($meta['cover'][0])){ echo $meta['cover'][0]; } ?>" placeholder="<?php _e('Gmedia ID or Image URL', 'gmLang'); ?>"/>
							</div>
						<?php } ?>

						<div class="form-group">
							<?php
							$alb_id = empty($albs)? 0 : reset($albs)->term_id;
							$term_type = 'gmedia_album';
							$gm_terms = $gmDB->get_terms($term_type);

							$terms_album = '';
							if(count($gm_terms)){
								foreach($gm_terms as $term){
									$selected_option = ($alb_id == $term->term_id)? ' selected="selected"' : '';
									$terms_album .= '<option' . $selected_option . ' value="' . $term->term_id . '">' . esc_html($term->name) . '</option>' . "\n";
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
							<textarea name="terms[gmedia_tag]" class="form-control input-sm" rows="1" cols="50"><?php echo $terms_tag; ?></textarea>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-6">
						<div class="form-group">
							<label><?php _e('Filename', 'gmLang'); ?></label>
							<input name="filename" type="text" class="form-control input-sm gmedia-filename" value="<?php echo pathinfo($item->gmuid, PATHINFO_FILENAME); ?>"/>
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
						<div class="form-group hidden">
							<label><?php _e('Author', 'gmLang'); ?></label>
							<?php $user_ids = $gmCore->get_editable_user_ids($user_ID);
							wp_dropdown_users(array('include' => $user_ids, 'include_selected' => true,
													'name' => 'author', 'selected' => $item->author,
													'class' => 'form-control'));
							?>
						</div>
						<div class="media-meta"><span class="label label-default"><?php _e('ID', 'gmLang') ?>:</span> <strong><?php echo $item->ID; ?></strong></div>
						<div class="media-meta"><span class="label label-default"><?php _e('Type', 'gmLang') ?>:</span> <?php echo $item->mime_type; //echo ucfirst($type[0]); ?></div>
						<div class="media-meta"><span class="label label-default"><?php _e('File Size', 'gmLang') ?> :</span> <?php echo $gmCore->filesize($item_path); ?></div>
						<?php if('image' == $type[0]){
							$_metadata = unserialize($meta['_metadata'][0]); ?>
							<div class="media-meta"> <span class="label label-default"><?php _e('Dimensions', 'gmLang') ?>:</span> <?php echo $_metadata['original']['width'] . ' × ' . $_metadata['original']['height']; ?></div>
						<?php } ?>
						<div class="media-meta"><span class="label label-default"><?php _e('Last Edited', 'gmLang') ?>:</span> <span class="gm-last-edited"><?php echo $item->modified; ?></span></div>
					</div>
				</div>
				<?php do_action('gmedia_edit_form'); ?>
			</div>
		</form>
	<?php } ?>
	<?php } ?>
		<script type="text/javascript">
			jQuery(function($){
				<?php if(!$gmProcessor->mode){ } else { ?>
				$('.combobox_gmedia_album').selectize({
					create: true,
					persist: false
				});

				var gmedia_date_temp;
				$('.input-group.date').datetimepicker({useSeconds: true}).on('show.dp',function(e){
					gmedia_date_temp = $('input', this).val();
				}).on('hide.dp', function(e){
					if(gmedia_date_temp != $('input', this).val()){
						$('input', this).change();
					}
				});
				$('input.gmedia-filename').alphanum({
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
				<?php } ?>
			});
		</script>
	<?php } else{ ?>
		<div class="list-group-item">
			<div class="well well-lg text-center">
				<h4><?php _e('No items to show.', 'gmLang'); ?></h4>
				<p><a href="<?php echo admin_url('admin.php?page=GrandMedia_AddMedia') ?>" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add Media', 'gmLang'); ?></a></p>
			</div>
		</div>
	<?php } ?>
	</div>

	<?php
	wp_original_referer_field(true, 'previous');
	wp_nonce_field('GmediaGallery');
	?>
	</div>

	<div class="modal fade gmedia-modal" id="termsModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog"></div>
	</div>
<?php
}
