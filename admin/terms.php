<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	die('You are not allowed to call this page directly.');
}

/**
 * gmediaTerms()
 *
 * @return mixed content
 */
function gmediaTerms(){
	global $user_ID, $gmDB, $gmCore, $gmGallery, $gmProcessor;

	$url = add_query_arg(array('page' => $gmProcessor->page), admin_url('admin.php'));

	/* todo: per_page options for gmedia_terms
	$gm_screen_options = get_user_meta($user_ID, 'gm_screen_options', true);
	if(!is_array($gm_screen_options)){
		$gm_screen_options = array();
	}
	$gm_screen_options = array_merge($gmGallery->options['gm_screen_options'], $gm_screen_options);
	*/

	$filter = ('selected' == $gmCore->_req('filter'))? $gmProcessor->selected_items : null;
	$args = array('orderby' => $gmCore->_get('orderby', 'name'), 'order' => $gmCore->_get('order', 'ASC'),
				  'search' => $gmCore->_get('s', ''), 'number' => $gmCore->_get('number', 30),
				  'hide_empty' => $gmCore->_get('hide_empty', 0), 'page' => $gmCore->_get('pager', 1),
				  'include' => $filter);
	$args['offset'] = ($args['page'] - 1) * $args['number'];

	$taxonomy = $gmCore->_get('term', 'gmedia_album');
	if('gmedia_category' == $taxonomy){
		$args['number'] = '';
		$args['offset'] = '';
		$args['search'] = '';
		$args['include'] = null;
	}

	$gmediaTerms = $gmDB->get_terms($taxonomy, $args);

	?>
	<div class="panel panel-default">
		<div class="panel-heading clearfix">

			<?php if('gmedia_category' != $taxonomy){ ?>
				<form class="form-inline gmedia-search-form" role="search" method="get">
					<div class="form-group">
						<input type="hidden" name="page" value="<?php echo $gmProcessor->page; ?>"/>
						<input type="hidden" name="term" value="<?php echo $taxonomy; ?>"/>
						<input id="gmedia-search" class="form-control input-sm" type="text" name="s" placeholder="<?php _e('Search...', 'gmLang'); ?>" value="<?php echo $gmCore->_get('s', ''); ?>"/>
					</div>
					<button type="submit" class="btn btn-default input-sm"><span class="glyphicon glyphicon-search"></span></button>
				</form>
				<?php echo $gmDB->query_pager(); ?>
			<?php } ?>

			<div class="btn-toolbar pull-left">
				<?php if('gmedia_category' != $taxonomy){ ?>
					<div class="btn-group gm-checkgroup" id="cb_global-btn">
						<span class="btn btn-default active"><input class="doaction" id="cb_global" data-group="cb_term-object" type="checkbox"/></span>
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							<span class="caret"></span>
							<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span>
						</button>
						<ul class="dropdown-menu" role="menu">
							<li><a data-select="total" href="#"><?php _e('All', 'gmLang'); ?></a></li>
							<li><a data-select="none" href="#"><?php _e('None', 'gmLang'); ?></a></li>
							<li class="divider"></li>
							<li><a data-select="reverse" href="#" title="<?php _e('Reverse only visible items', 'gmLang'); ?>"><?php _e('Reverse', 'gmLang'); ?></a></li>
						</ul>
					</div>
				<?php } ?>

				<div class="btn-group" style="margin-right:20px;">
					<?php $btn_color = $gmDB->filter? 'warning' : 'primary';
						  $btn_active_title = $gmDB->filter? '" title="'.__('Reset Filter', 'gmLang') : ''; ?>
					<a class="btn btn<?php echo ('gmedia_album' == $taxonomy)? "-$btn_color active".$btn_active_title : '-default'; ?>" href="<?php echo add_query_arg(array('term' => 'gmedia_album'), $url); ?>"><?php _e('Albums', 'gmLang'); ?></a>
					<a class="btn btn<?php echo ('gmedia_tag' == $taxonomy)? "-$btn_color active".$btn_active_title : '-default'; ?>" href="<?php echo add_query_arg(array('term' => 'gmedia_tag'), $url); ?>"><?php _e('Tags', 'gmLang'); ?></a>
					<a class="btn btn<?php echo ('gmedia_category' == $taxonomy)? "-primary active" : '-default'; ?>" href="<?php echo add_query_arg(array('term' => 'gmedia_category'), $url); ?>"><?php _e('Categories', 'gmLang'); ?></a>
				</div>

				<?php if('gmedia_category' != $taxonomy){ ?>
					<div class="btn-group">
						<a class="btn btn-default" href="#"><?php _e('Action', 'gmLang'); ?></a>
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							<span class="caret"></span>
							<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span>
						</button>
						<?php
						$rel_selected_show = 'rel-selected-show';
						$rel_selected_hide = 'rel-selected-hide';
						?>
						<ul class="dropdown-menu" role="menu">
							<li class="dropdown-header <?php echo $rel_selected_hide; ?>"><span><?php _e("Select items to see more actions", "gmLang"); ?></span></li>
							<li class="<?php echo $rel_selected_show; ?>"><a href="<?php echo wp_nonce_url($gmCore->get_admin_url(array('delete' => 'selected'), array('filter')), 'gmedia_delete') ?>" class="gmedia-delete" data-confirm="<?php _e("You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang"); ?>"><?php _e('Delete Selected Items', 'gmLang'); ?></a></li>
							<?php do_action('gmedia_term_action_list'); ?>
						</ul>
					</div>

					<form class="btn-group" id="gm-selected-btn" name="gm-selected-form" action="<?php echo add_query_arg(array('term' => $taxonomy, 'filter' => 'selected'), $url); ?>" method="post">
						<button type="submit" class="btn btn<?php echo ('selected' == $gmCore->_req('filter'))? '-success' : '-info' ?>"><?php printf(__('%s selected', 'gmLang'), '<span id="gm-selected-qty">' . count($gmProcessor->selected_items) . '</span>'); ?></button>
						<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown"><span class="caret"></span> <span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
						<input type="hidden" id="gm-selected" data-userid="<?php echo $user_ID; ?>" data-key="<?php echo $taxonomy; ?>" name="selected_items" value="<?php echo implode(',', $gmProcessor->selected_items); ?>"/>
						<ul class="dropdown-menu" role="menu">
							<li><a id="gm-selected-show" href="#show"><?php _e('Show only selected items', 'gmLang'); ?></a></li>
							<li><a id="gm-selected-clear" href="#clear"><?php _e('Clear selected items', 'gmLang'); ?></a></li>
						</ul>
					</form>
				<?php } ?>

			</div>
		</div>


		<?php if('gmedia_album' == $taxonomy){ ?>
			<form method="post" id="gmedia-edit-term" name="gmAddTerms" class="panel-body" style="padding-bottom:0; border-bottom:1px solid #ddd;">
				<div class="row">
					<div class="col-xs-6">
						<div class="form-group">
							<label><?php _e('Name', 'gmLang'); ?></label>
							<input type="text" class="form-control input-sm" name="term[name]" placeholder="<?php _e('Album Name', 'gmLang'); ?>" required/>
						</div>
						<div class="form-group">
							<label><?php _e('Description', 'gmLang'); ?></label>
							<textarea class="form-control input-sm" style="height:53px;" rows="2" name="term[description]"></textarea>
						</div>
					</div>
					<div class="col-xs-6">
						<div class="form-group row">
							<div class="col-xs-6">
								<label><?php _e('Order gmedia', 'gmLang'); ?></label>
								<select name="term[orderby]" class="form-control input-sm">
									<option value="custom"><?php _e('user defined', 'gmLang'); ?></option>
									<option selected="selected" value="ID"><?php _e('by ID', 'gmLang'); ?></option>
									<option value="title"><?php _e('by title', 'gmLang'); ?></option>
									<option value="date"><?php _e('by date', 'gmLang'); ?></option>
									<option value="modified"><?php _e('by last modified date', 'gmLang'); ?></option>
									<option value="rand"><?php _e('Random', 'gmLang'); ?></option>
								</select>
							</div>
							<div class="col-xs-6">
								<label><?php _e('Sort order', 'gmLang'); ?></label>
								<select name="term[order]" class="form-control input-sm">
									<option value="ASC"><?php _e('ASC', 'gmLang'); ?></option>
									<option selected="selected" value="DESC"><?php _e('DESC', 'gmLang'); ?></option>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="form-group col-xs-6">
								<label><?php _e('Status', 'gmLang'); ?></label>
								<select name="term[status]" class="form-control input-sm">
									<option selected="selected" value="public"><?php _e('Public', 'gmLang'); ?></option>
									<option value="private"><?php _e('Private', 'gmLang'); ?></option>
									<option value="draft"><?php _e('Draft', 'gmLang'); ?></option>
								</select>
							</div>
							<div class="form-group col-xs-6">
								<label>&nbsp;</label>
								<?php
								wp_original_referer_field(true, 'previous');
								wp_nonce_field('GmediaTerms', 'term_save_wpnonce');
								?>
								<input type="hidden" name="term[taxonomy]" value="gmedia_album"/>
								<button style="display:block" type="submit" class="btn btn-primary btn-sm" name="gmedia_album_save"><?php _e('Add New Album', 'gmLang'); ?></button>
							</div>
						</div>
					</div>
				</div>
			</form>
			<form class="list-group" id="gm-list-table" style="margin-bottom:4px;">
				<?php
				if(count($gmediaTerms)){
					foreach($gmediaTerms as $item){
						$termItems = array();
						$per_page = 10;
						if($item->count){
							$args = array('no_found_rows' => true, 'per_page' => $per_page, 'album__in' => array($item->term_id));
							$termItems = $gmDB->get_gmedias($args);
						}
						$is_selected = in_array($item->term_id, $gmProcessor->selected_items)? true : false;
						?>
						<div class="list-group-item term-list-item">
							<div class="row cb_term-object">
								<div class="term_id">#<?php echo $item->term_id; ?></div>
								<div class="col-xs-5 term-label">
									<div class="checkbox">
										<input name="doaction[]" type="checkbox"<?php echo $is_selected? ' checked="checked"' : ''; ?> value="<?php echo $item->term_id; ?>"/>
										<a href="<?php echo add_query_arg(array('edit_album' => $item->term_id), $url); ?>"><?php echo esc_html($item->name); ?></a>
										<?php if($item->count){ ?>
											<a href="<?php echo $gmCore->get_admin_url(array('page' => 'GrandMedia', 'alb' => $item->term_id), array(), true); ?>" class="badge pull-right"><?php echo $item->count; ?></a>
										<?php } else{ ?>
											<span class="badge pull-right"><?php echo $item->count; ?></span>
										<?php } ?>
									</div>
								</div>
								<div class="col-xs-7 term-images">
									<?php if(!empty($termItems)){
										foreach($termItems as $i){
											?>
											<img style="z-index:<?php echo $per_page--; ?>;" src="<?php echo $gmCore->gm_get_media_image($i, 'thumb', false); ?>" alt="<?php echo $i->ID; ?>" title="<?php echo esc_attr($i->title); ?>"/>
										<?php
										}
									}
									if(count($termItems) < $item->count){
										echo '...';
									}
									?>
								</div>
							</div>
							<?php if(!empty($item->description)){ ?>
								<div class="term-description"><?php echo esc_html($item->description); ?></div>
							<?php } ?>
						</div>
					<?php
					}
				} else{
					?>
					<div class="list-group-item">
						<div class="well well-lg text-center">
							<h4><?php _e('No items to show.', 'gmLang'); ?></h4>
						</div>
					</div>
				<?php } ?>
				<?php
				wp_original_referer_field(true, 'previous');
				wp_nonce_field('GmediaTerms');
				?>
			</form>


		<?php } elseif('gmedia_tag' == $taxonomy){ ?>
			<form method="post" id="gmedia-edit-term" name="gmAddTerms" class="panel-body" style="padding-bottom:0; border-bottom:1px solid #ddd;">
				<div class="row">
					<div class="form-group col-xs-9">
						<label><?php _e('Tags', 'gmLang'); ?> <small class="text-muted">(<?php _e('you can type multiple tags separated by comma') ?>)</small></label>
						<input type="text" class="form-control input-sm" name="term[name]" placeholder="<?php _e('Tag Names', 'gmLang'); ?>" required/>
					</div>
					<div class="col-xs-3" style="padding-top:24px;">
						<?php
						wp_original_referer_field(true, 'previous');
						wp_nonce_field('GmediaTerms', 'term_save_wpnonce');
						?>
						<input type="hidden" name="term[taxonomy]" value="gmedia_tag"/>
						<button type="submit" class="btn btn-primary btn-sm" name="gmedia_tag_add"><?php _e('Add New Tags', 'gmLang'); ?></button>
					</div>
				</div>
			</form>
			<form class="list-group" id="gm-list-table" style="margin-bottom:4px;">
				<?php
				if(count($gmediaTerms)){
					foreach($gmediaTerms as $item){
						$termItems = array();
						$per_page = 5;
						if($item->count){
							$args = array('no_found_rows' => true, 'per_page' => $per_page, 'tag_id' => $item->term_id);
							$termItems = $gmDB->get_gmedias($args);
						}
						$is_selected = in_array($item->term_id, $gmProcessor->selected_items)? true : false;
						?>
						<div class="list-group-item term-list-item">
							<div class="row cb_term-object" id="tag_<?php echo $item->term_id; ?>">
								<div class="term_id">#<?php echo $item->term_id; ?></div>
								<div class="col-xs-5 term-label">
									<div class="checkbox">
										<input name="doaction[]" type="checkbox"<?php echo $is_selected? ' checked="checked"' : ''; ?> value="<?php echo $item->term_id; ?>"/>
										<a class="edit_tag_link" href="#tag_<?php echo $item->term_id; ?>"><?php echo esc_html($item->name); ?></a>
										<span class="edit_tag_form" style="display:none;"><input class="edit_tag_input" type="text" data-tag_id="<?php echo $item->term_id; ?>" name="gmedia_tag_name[<?php echo $item->term_id; ?>]" value="<?php echo esc_attr($item->name); ?>" placeholder="<?php echo esc_attr($item->name); ?>"/><a href="#tag_<?php echo $item->term_id; ?>" class="edit_tag_save btn btn-link glyphicon glyphicon-pencil"></a></span>
										<?php if($item->count){ ?>
											<a href="<?php echo $gmCore->get_admin_url(array('page' => 'GrandMedia', 'tag_id' => $item->term_id), array(), true); ?>" class="badge pull-right"><?php echo $item->count; ?></a>
										<?php } else{ ?>
											<span class="badge pull-right"><?php echo $item->count; ?></span>
										<?php } ?>
									</div>
								</div>
								<div class="col-xs-7 term-images">
									<?php if(!empty($termItems)){
										foreach($termItems as $i){
											?>
											<img style="z-index:<?php echo $per_page--; ?>;" src="<?php echo $gmCore->gm_get_media_image($i, 'thumb', false); ?>" alt="<?php echo $i->ID; ?>" title="<?php echo esc_attr($i->title); ?>"/>
										<?php
										}
									}
									if(count($termItems) < $item->count){
										echo '...';
									}
									?>
								</div>
							</div>
						</div>
					<?php } ?>
					<script type="text/javascript">
						jQuery(function($){
							$('#gm-list-table').data('edit',false);
							$('input.edit_tag_input').keypress(function(e){
								var charCode = e.charCode || e.keyCode || e.which;
								if (charCode  == 13) {
									e.preventDefault();
									$(this).next().click();
								}
							});
							$('.edit_tag_link').click(function(e){
								e.preventDefault();
								var id = $(this).attr('href');
								$(this).hide();
								$(id).find('.edit_tag_form').show().find('input').focus();
								$('#gm-list-table').data('edit',true);
							});
							$('.edit_tag_save').click(function(e){
								var id = $(this).attr('href');
								var inp = $(id).find('.edit_tag_form input');
								var new_tag_name = $.trim(inp.val());
								if(('' == new_tag_name) || $.isNumeric()){
									inp.val(inp.attr('placeholder'));
									$(id).find('.edit_tag_form').hide();
									$(id).find('.edit_tag_link').show();
									return;
								}
								var post_data = {
									action: 'gmedia_tag_edit', tag_id: inp.data('tag_id'), tag_name: new_tag_name, _wpnonce: $('#_wpnonce').val()
								};
								$.post(ajaxurl, post_data, function(data, textStatus, jqXHR){
									console.log(data);
									//new_tag_name = new_tag_name.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
									inp.attr('placeholder', new_tag_name);
									$(id).find('.edit_tag_form').hide();
									$(id).find('.edit_tag_link').text(new_tag_name).show();
								});
							});
							$('.edit_tag_input').blur(function(e){
								var t = $(this);
								var id = t.data('tag_id');
								t.val(t.attr('placeholder'));
								$('#tag_'+id).find('.edit_tag_form').hide();
								$('#tag_'+id).find('.edit_tag_link').show();
							});
						});
					</script>
				<?php } else{
					?>
					<div class="list-group-item">
						<div class="well well-lg text-center">
							<h4><?php _e('No items to show.', 'gmLang'); ?></h4>
						</div>
					</div>
				<?php } ?>
				<?php
				wp_original_referer_field(true, 'previous');
				wp_nonce_field('GmediaTerms');
				?>
			</form>


		<?php } elseif('gmedia_category' == $taxonomy){ ?>
			<div class="panel-body"></div>
			<div class="list-group" id="gm-list-table" style="margin-bottom:4px;">
				<?php
				$gmediaCategories = $gmGallery->options['taxonomies']['gmedia_category'];
				foreach($gmediaTerms as $item){
					$cat[$item->name] = $item;
				}
				unset($gmediaTerms);

				foreach($gmediaCategories as $name => $title){
					$termItems = array();
					$per_page = 10;
					if(isset($cat[$name])){
						$count = $cat[$name]->count;
						$term_id = $cat[$name]->term_id;
						if($count){
							$args = array('no_found_rows' => true, 'per_page' => $per_page, 'category__in' => array($term_id));
							$termItems = $gmDB->get_gmedias($args);
						}
					} else {
						$count = 0;
						$term_id = '##';
					}
					?>
					<div class="list-group-item term-list-item">
						<div class="row cb_term-object">
							<div class="term_id">#<?php echo $term_id; ?></div>
							<div class="col-xs-5" style="padding-top:10px; padding-bottom:10px;">
								<?php echo esc_html($title); ?>
								<?php if($count){ ?>
									<a href="<?php echo $gmCore->get_admin_url(array('page' => 'GrandMedia', 'cat' => $term_id), array(), true); ?>" class="badge pull-right"><?php echo $count; ?></a>
								<?php } else{ ?>
									<span class="badge pull-right"><?php echo $count; ?></span>
								<?php } ?>
							</div>
							<div class="col-xs-7 term-images">
								<?php if(!empty($termItems)){
									foreach($termItems as $i){
										?>
										<img style="z-index:<?php echo $per_page--; ?>;" src="<?php echo $gmCore->gm_get_media_image($i, 'thumb', false); ?>" alt="<?php echo $i->ID; ?>" title="<?php echo esc_attr($i->title); ?>"/>
									<?php
									}
								}
								if(count($termItems) < $count){
									echo '...';
								}
								?>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		<?php } ?>

	</div>

<?php
}


/**
 * gmediaAlbumEdit()
 *
 * @return mixed content
 */
function gmediaAlbumEdit(){
	global $gmDB, $gmCore, $gmGallery, $gmProcessor;

	$url = add_query_arg(array('page' => $gmProcessor->page), admin_url('admin.php'));

	$taxonomy = 'gmedia_album';
	$term_id = $gmCore->_get('edit_album');

	$term = $gmDB->get_term($term_id, $taxonomy);

	if(!empty($term) && !is_wp_error($term)){

		$term_meta = $gmDB->get_metadata('gmedia_term', $term->term_id);
		$term_meta = array_map('reset', $term_meta);
		$term_meta = array_merge( array('orderby' => 'ID', 'order' => 'DESC'), $term_meta);
		$per_page = 30;
		$pager = '';
		$mousesort = ('drag-n-drop' === $gmCore->_get('sort'))? true : false;

		$termItems = array();
		if($term->count){
			$args = array('album__in' => $term->term_id, 'orderby' => $term_meta['orderby'], 'order' => $term_meta['order']);
			if($mousesort){
				$args = array_merge($args, array('nopaging' => 1));
			} else{
				$args = array_merge($args, array('per_page' => $per_page, 'page' => $gmCore->_get('pager', 1)));
			}
			$termItems = $gmDB->get_gmedias($args);

			if(!$mousesort){
				$pager = $gmDB->query_pager();
			}
		}

	?>
	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<div class="btn-toolbar pull-left">
				<div class="btn-group" style="margin-right:20px;">
					<a class="btn btn-primary active" href="<?php echo add_query_arg(array('term' => 'gmedia_album'), $url); ?>"><?php _e('Albums', 'gmLang'); ?></a>
					<a class="btn btn-default" href="<?php echo add_query_arg(array('term' => 'gmedia_tag'), $url); ?>"><?php _e('Tags', 'gmLang'); ?></a>
					<a class="btn btn-default" href="<?php echo add_query_arg(array('term' => 'gmedia_category'), $url); ?>"><?php _e('Categories', 'gmLang'); ?></a>
				</div>

				<div class="btn-group">
					<a class="btn btn-default" href="#"><?php _e('Action', 'gmLang'); ?></a>
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						<span class="caret"></span>
						<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						<?php if($mousesort){ ?>
							<li><a href="<?php echo $gmCore->get_admin_url(array(), array('sort')); ?>"><?php _e('Disable Drag and Drop Sorting', 'gmLang'); ?></a></li>
						<?php } else { ?>
							<li><a href="<?php echo $gmCore->get_admin_url(array('sort' => 'drag-n-drop'), array('pager')); ?>"><?php _e('Enable Drag and Drop Sorting', 'gmLang'); ?></a></li>
						<?php } ?>
						<li><a href="<?php echo add_query_arg(array('page' => 'GrandMedia', 'alb' => $term->term_id), admin_url('admin.php')); ?>"><?php _e('Show Album in Gmedia Library', 'gmLang'); ?></a></li>
					</ul>
				</div>
			</div>

			<?php echo $pager; ?>

		</div>

		<form method="post" id="gmedia-edit-term" name="gmEditTerm" class="panel-body">
			<h4 style="margin-top:0;"><?php _e('Edit Album'); ?>: <em><?php echo esc_html($term->name); ?></em></h4>
			<div class="row" style="border-bottom:1px solid #ddd; margin-bottom:15px;">
				<div class="col-xs-6">
					<div class="form-group">
						<label><?php _e('Name', 'gmLang'); ?></label>
						<input type="text" class="form-control input-sm" name="term[name]" value="<?php echo esc_attr($term->name); ?>" placeholder="<?php _e('Album Name', 'gmLang'); ?>" required/>
					</div>
					<div class="form-group">
						<label><?php _e('Description', 'gmLang'); ?></label>
						<textarea class="form-control input-sm" style="height:53px;" rows="2" name="term[description]"><?php echo $term->description; ?></textarea>
					</div>
				</div>
				<div class="col-xs-6">
					<div class="form-group row">
						<div class="col-xs-6">
							<label><?php _e('Order gmedia', 'gmLang'); ?></label>
							<select name="term[orderby]" class="form-control input-sm">
								<option value="custom"<?php selected($term_meta['orderby'], 'custom'); ?>><?php _e('user defined', 'gmLang'); ?></option>
								<option value="ID"<?php selected($term_meta['orderby'], 'ID'); ?>><?php _e('by ID', 'gmLang'); ?></option>
								<option value="title"<?php selected($term_meta['orderby'], 'title'); ?>><?php _e('by title', 'gmLang'); ?></option>
								<option value="date"<?php selected($term_meta['orderby'], 'date'); ?>><?php _e('by date', 'gmLang'); ?></option>
								<option value="modified"<?php selected($term_meta['orderby'], 'modified'); ?>><?php _e('by last modified date', 'gmLang'); ?></option>
								<option value="rand"<?php selected($term_meta['orderby'], 'rand'); ?>><?php _e('Random', 'gmLang'); ?></option>
							</select>
						</div>
						<div class="col-xs-6">
							<label><?php _e('Sort order', 'gmLang'); ?></label>
							<select name="term[order]" class="form-control input-sm">
								<option value="ASC"<?php selected($term_meta['order'], 'ASC'); ?>><?php _e('ASC', 'gmLang'); ?></option>
								<option value="DESC"<?php selected($term_meta['order'], 'DESC'); ?>><?php _e('DESC', 'gmLang'); ?></option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-xs-6">
							<label><?php _e('Status', 'gmLang'); ?></label>
							<select name="term[status]" class="form-control input-sm">
								<option value="public"<?php selected($term->status, 'public'); ?>><?php _e('Public', 'gmLang'); ?></option>
								<?php /* ?>
								<option value="private"<?php selected($term->status, 'private'); ?>><?php _e('Private', 'gmLang'); ?></option>
								<option value="draft"<?php selected($term->status, 'draft'); ?>><?php _e('Draft', 'gmLang'); ?></option>
 								<?php */ ?>
							</select>
						</div>
						<div class="form-group col-xs-6">
							<label><?php echo __('ID', 'gmLang').": {$term->term_id}"; ?></label>
							<?php wp_nonce_field('GmediaTerms', 'term_save_wpnonce'); ?>
							<input type="hidden" name="term[term_id]" value="<?php echo $term->term_id; ?>"/>
							<input type="hidden" name="term[taxonomy]" value="gmedia_album"/>
							<button style="display:block" type="submit" class="btn btn-primary btn-sm" name="gmedia_album_save"><?php _e('Update', 'gmLang'); ?></button>
						</div>
					</div>
				</div>
			</div>

			<div class="termItems clearfix" id="termItems">
				<?php if(!empty($termItems)){
					foreach($termItems as $item){ ?>
						<div class="gm-img-thumbnail" data-gmid="<?php echo $item->ID; ?>">
							<img style="height:80px; width:auto;" src="<?php echo $gmCore->gm_get_media_image($item, 'thumb', false); ?>" alt="<?php echo $item->ID; ?>" title="<?php echo esc_attr($item->title); ?>"/>
							<input type="text" name="term[gmedia_ids][<?php echo $item->ID; ?>]" value="<?php echo isset($item->gmedia_order)? $item->gmedia_order : '0'; ?>"/>
							<span class="label label-default">ID: <?php echo $item->ID; ?></span>
						</div>
					<?php }
				} ?>

			</div>
			<script type="text/javascript">
				jQuery(function($){
					var inputs = $('#gmedia-edit-term').find('input, select').keypress(function(e){
						var charCode = e.charCode || e.keyCode || e.which;
						if (charCode  == 13) {
							e.preventDefault();
							var nextInput = inputs.get(inputs.index(this) + 1);
							if (nextInput) {
								nextInput.focus();
							} else{
								$(this).blur();
							}
						}
					});

					var img_order_asc = <?php echo ('ASC' == $term_meta['order'])? 'true' : 'false'; ?>;
					var sortdiv = $('#termItems');
					var items = $('.gm-img-thumbnail', sortdiv);

					<?php if($mousesort){ ?>
					sortdiv.sortable({
						items: '.gm-img-thumbnail',
						handle: 'img',
						placeholder: 'gm-img-thumbnail ui-highlight-placeholder',
						forcePlaceholderSize: true,
						//revert: true,
						stop: function( event, ui ) {
							items = $('.gm-img-thumbnail',this);
							var	qty = items.length - 1;
							items.each(function(i){
								var order = img_order_asc? i : (qty - i);
								$(this).find('input').val(order);
							});
						}
					});

					<?php } ?>

					$('input',items).on('change',function(){
						sortdiv.css({height:sortdiv.height()});
						var items = $('.gm-img-thumbnail', sortdiv);

						var new_order = $.isNumeric($(this).val()) ? parseInt($(this).val()) : -1,
							new_index;
						$(this).val(new_order).closest('.gm-img-thumbnail').css({zIndex:1000});

						var ipos = [];
						items.each(function(i,el){
							var pos = $(el).position();
							$.data(el,'pos',pos);
							ipos[i] = pos;
						});

						items.tsort('input',{useVal:true, order:(img_order_asc? 'asc' : 'desc')}).each(function(i,el){
							var from = $.data(el,'pos');
							var to = ipos[i];
							$(el).css({position:'absolute',top:from.top,left:from.left}).animate({top:to.top,left:to.left},500);
						}).promise().done(function(){
							items.removeAttr('style');
							sortdiv.removeAttr('style');
						});

						$(this).val( ((new_order < 0)? 0 : new_order)).focus();
					});
				});
			</script>
		</form>
		<div class="panel-body"><?php echo $pager; ?><div class="well well-sm pull-left" style="margin:0;"><?php printf(__('Total items: %d'), $term->count); ?></div></div>
	</div>
<?php
	} else{

	}
}
