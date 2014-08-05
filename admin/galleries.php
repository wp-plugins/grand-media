<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	die('You are not allowed to call this page directly.');
}

/**
 * gmediaGalleries()
 *
 * @return mixed content
 */
function gmediaGalleries(){
	global $user_ID, $gmDB, $gmCore, $gmGallery, $gmProcessor;

	$url = add_query_arg(array('page' => $gmProcessor->page), admin_url('admin.php'));

	/* todo: per_page and order options for gmedia_terms
	$gm_screen_options = get_user_meta($user_ID, 'gm_screen_options', true);
	if(!is_array($gm_screen_options)){
		$gm_screen_options = array();
	}
	$gm_screen_options = array_merge($gmGallery->options['gm_screen_options'], $gm_screen_options);
	*/

	$filter = ('selected' == $gmCore->_req('filter'))? $gmProcessor->selected_items : null;
	$args = array(
		'orderby' => $gmCore->_get('orderby', 'name'),
		'order' => $gmCore->_get('order', 'ASC'),
		'search' => $gmCore->_get('s', ''),
		'number' => $gmCore->_get('number', 30),
		'hide_empty' => 0,
		'page' => $gmCore->_get('pager', 1),
		'include' => $filter
	);
	$args['offset'] = ($args['page'] - 1) * $args['number'];

	if($gmCore->caps['gmedia_edit_others_media']){
		$args['global'] = $gmCore->_get('author', '');
	} else{
		$args['global'] = array($user_ID);
	}

	$taxonomy = 'gmedia_gallery';
	$gmediaTerms = $gmDB->get_terms($taxonomy, $args);
	if(is_wp_error($gmediaTerms)){
		echo $gmProcessor->alert('danger', $gmediaTerms->get_error_message());
		$gmediaTerms = array();
	}

	$modules = array();
	if(($plugin_modules = glob(GMEDIA_ABSPATH . 'module/*', GLOB_ONLYDIR | GLOB_NOSORT))){
		foreach($plugin_modules as $path){
			$mfold = basename($path);
			$modules[$mfold] = array(
				'module_name' => $mfold,
				'module_url' => $gmCore->gmedia_url . "/module/{$mfold}",
				'module_path' => $path
			);
		}
	}
	if(($upload_modules = glob($gmCore->upload['path'] . '/' . $gmGallery->options['folder']['module'] . '/*', GLOB_ONLYDIR | GLOB_NOSORT))){
		foreach($upload_modules as $path){
			$mfold = basename($path);
			$modules[$mfold] = array(
				'module_name' => $mfold,
				'module_url' => $gmCore->upload['url'] . "/{$gmGallery->options['folder']['module']}/{$mfold}",
				'module_path' => $path
			);
		}
	}
	?>

	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<form class="form-inline gmedia-search-form" role="search" method="get">
				<div class="form-group">
					<input type="hidden" name="page" value="<?php echo $gmProcessor->page; ?>"/>
					<input type="hidden" name="term" value="<?php echo $taxonomy; ?>"/>
					<input id="gmedia-search" class="form-control input-sm" type="text" name="s" placeholder="<?php _e('Search...', 'gmLang'); ?>" value="<?php echo $gmCore->_get('s', ''); ?>"/>
				</div>
				<button type="submit" class="btn btn-default input-sm"><span class="glyphicon glyphicon-search"></span></button>
			</form>
			<?php echo $gmDB->query_pager(); ?>

			<div class="btn-toolbar pull-left">
				<div class="btn-group gm-checkgroup" id="cb_global-btn">
					<span class="btn btn-default active"><input class="doaction" id="cb_global" data-group="cb_media-object" type="checkbox"/></span>
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

				<div class="btn-group" style="margin-right:20px;">
					<a class="btn btn-primary" href="#chooseModuleModal" data-toggle="modal"><?php _e('Create Gallery', 'gmLang'); ?></a>
				</div>

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
						<li class="<?php echo $rel_selected_show; ?>">
							<a href="<?php echo wp_nonce_url($gmCore->get_admin_url(array('delete' => 'selected'), array('filter')), 'gmedia_delete') ?>" class="gmedia-delete" data-confirm="<?php _e("You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang"); ?>"><?php _e('Delete Selected Items', 'gmLang'); ?></a>
						</li>
						<?php do_action('gmedia_term_action_list'); ?>
					</ul>
				</div>

				<form class="btn-group" id="gm-selected-btn" name="gm-selected-form" action="<?php echo add_query_arg(array('filter' => 'selected'), $url); ?>" method="post">
					<button type="submit" class="btn btn<?php echo ('selected' == $gmCore->_req('filter'))? '-success' : '-info' ?>"><?php printf(__('%s selected', 'gmLang'), '<span id="gm-selected-qty">' . count($gmProcessor->selected_items) . '</span>'); ?></button>
					<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown"><span class="caret"></span>
						<span class="sr-only"><?php _e('Toggle Dropdown', 'gmLang'); ?></span></button>
					<input type="hidden" id="gm-selected" data-userid="<?php echo $user_ID; ?>" data-key="<?php echo $taxonomy; ?>" name="selected_items" value="<?php echo implode(',', $gmProcessor->selected_items); ?>"/>
					<ul class="dropdown-menu" role="menu">
						<li><a id="gm-selected-show" href="#show"><?php _e('Show only selected items', 'gmLang'); ?></a></li>
						<li><a id="gm-selected-clear" href="#clear"><?php _e('Clear selected items', 'gmLang'); ?></a></li>
					</ul>
				</form>

			</div>

		</div>
		<div class="panel-body" id="gmedia-msg-panel"></div>
		<form class="list-group" id="gm-list-table" style="margin-bottom:4px;">
			<?php
			if(count($gmediaTerms)){
				$lib_url = add_query_arg(array('page' => 'GrandMedia'), admin_url('admin.php'));
				foreach($gmediaTerms as $term){

					$term_meta = $gmDB->get_metadata('gmedia_term', $term->term_id);
					$term_meta = array_map('reset', $term_meta);
					$term_meta = array_map('maybe_unserialize', $term_meta);

					$module = $gmCore->get_module_path($term_meta['module']);
					$module_info = array('type' => '&#8212;');
					if(file_exists($module['path'] . '/index.php')){
						$broken = false;
						include($module['path'] . '/index.php');
					} else{
						$broken = true;
					}

					if($term->global == $user_ID){
						$allow_edit = true;
					} else{
						$allow_edit = $gmCore->caps['gmedia_edit_others_media'];
					}

					$is_selected = in_array($term->term_id, $gmProcessor->selected_items)? true : false;
					?>
					<div class="list-group-item row d-row<?php echo $is_selected? ' active' : ''; ?>" id="list-item-<?php echo $term->term_id; ?>" data-id="<?php echo $term->term_id; ?>" data-type="<?php echo $term_meta['module']; ?>">
						<div class="term_id">#<?php echo $term->term_id; ?></div>
						<div class="col-xs-7">
							<label class="cb_media-object" style="width:130px;">
								<input name="doaction[]" type="checkbox"<?php echo $is_selected? ' checked="checked"' : ''; ?> data-type="<?php echo $term_meta['module']; ?>" class="hidden" value="<?php echo $term->term_id; ?>"/>
								<?php if(!$broken){ ?>
									<span class="thumbnail"><img src="<?php echo $module['url'] . '/screenshot.png'; ?>" alt="<?php echo esc_attr($term->name); ?>"/></span>
								<?php } else{ ?>
									<div class="bg-danger text-center"><?php _e('Module broken <br>Reinstall module', 'gmLang') ?></div>
								<?php } ?>
							</label>

							<div class="media-body" style="margin-left:145px;">
								<p class="media-title">
									<?php if($allow_edit){ ?>
										<a href="<?php echo add_query_arg(array('edit_gallery' => $term->term_id), $url); ?>"><?php echo esc_html($term->name); ?></a>
									<?php } else{ ?>
										<span><?php echo esc_html($term->name); ?></span>
									<?php } ?>
								</p>

								<p class="media-meta">
									<span class="label label-default"><?php _e('Author', 'gmLang'); ?>
										:</span> <?php echo $term->global? get_the_author_meta('display_name', $term->global) : '&#8212;'; ?>
								</p>

								<p class="media-caption"><?php echo esc_html($term->description); ?></p>

								<p class="media-meta" title="<?php _e('Shortcode', 'gmLang'); ?>" style="font-weight:bold">
									<span class="label label-default"><?php _e('Shortcode', 'gmLang'); ?>:</span> [gmedia id=<?php echo $term->term_id; ?>]
								</p>
							</div>
						</div>
						<div class="col-xs-5">
							<p class="media-meta">
								<span class="label label-default"><?php _e('Module', 'gmLang'); ?>:</span> <?php echo $term_meta['module']; ?>
								<br><span class="label label-default"><?php _e('Type', 'gmLang'); ?>:</span> <?php echo $module_info['type']; ?>
								<br><span class="label label-default"><?php _e('Last Edited', 'gmLang'); ?>:</span> <?php echo $term_meta['edited']; ?>
								<br><span class="label label-default"><?php _e('Status', 'gmLang'); ?>:</span> <?php echo $term->status; ?>
								<br><span class="label label-default"><?php _e('Source', 'gmLang'); ?>:</span>
								<?php
								$gallery_tabs = reset($term_meta['query']);
								$tax_tabs = key($term_meta['query']);
								if('gmedia__in' == $tax_tabs){
									_e('Selected Gmedia', 'gmLang');
									$gmedia_ids = wp_parse_id_list($gallery_tabs[0]);
									$gal_source = sprintf('<a class="selected__in" href="%s">' . __('Show %d items in Gmedia Library', 'gmLang') . '</a>', esc_url(add_query_arg(array('gmedia__in' => implode(',', $gmedia_ids)), $lib_url)), count($gmedia_ids));
									echo " ($gal_source)";
								} else{
									$tabs = $gmDB->get_terms($tax_tabs, array('include' => $gallery_tabs));
									$terms_source = array();
									if('gmedia_category' == $tax_tabs){
										_e('Categories', 'gmLang');
										foreach($tabs as $t){
											$terms_source[] = sprintf('<a class="category" href="%s">%s</a>', esc_url(add_query_arg(array('cat' => $t->term_id), $lib_url)), esc_html($gmGallery->options['taxonomies']['gmedia_category'][$t->name]));
										}
									} elseif('gmedia_album' == $tax_tabs){
										_e('Albums', 'gmLang');
										foreach($tabs as $t){
											$terms_source[] = sprintf('<a class="album" href="%s">%s</a>', esc_url(add_query_arg(array('alb' => $t->term_id), $lib_url)), esc_html($t->name));
										}
									} elseif('gmedia_tag' == $tax_tabs){
										_e('Tags', 'gmLang');
										foreach($tabs as $t){
											$terms_source[] = sprintf('<a class="tag" href="%s">%s</a>', esc_url(add_query_arg(array('tag_id' => $t->term_id), $lib_url)), esc_html($t->name));
										}
									} elseif('gmedia_filter' == $tax_tabs){
										_e('Filters', 'gmLang');
										foreach($tabs as $t){
											$terms_source[] = sprintf('<a class="filter" href="%s">%s</a>', esc_url(add_query_arg(array('stack_id' => $t->term_id), $lib_url)), esc_html($t->name));
										}
									}
									if(!empty($terms_source)){
										echo ' (' . join(', ', $terms_source) . ')';
									}
								}
								?>
							</p>
						</div>
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
	</div>

	<!-- Modal -->
	<div class="modal fade gmedia-modal" id="chooseModuleModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php _e('Choose Module for Gallery'); ?></h4>
				</div>
				<div class="modal-body linkblock">
					<?php
					if(!empty($modules)){
						foreach($modules as $m){
							/**
							 * @var $module_name
							 * @var $module_url
							 * @var $module_path
							 */
							extract($m);
							if(!file_exists($module_path . '/index.php')){
								continue;
							}
							$module_info = array();
							include($module_path . '/index.php');
							if(empty($module_info)){
								continue;
							}
							$mclass = ' module-' . $module_info['type'] . ' module-' . $module_info['status'];
							?>
							<div data-href="<?php echo add_query_arg(array('gallery_module' => $module_name), $url); ?>" class="choose-module media<?php echo $mclass; ?>">
								<a href="<?php echo add_query_arg(array('gallery_module' => $module_name), $url); ?>" class="thumbnail pull-left">
									<img class="media-object" src="<?php echo $module_url . '/screenshot.png'; ?>" alt="<?php echo esc_attr($module_info['title']); ?>" width="160" height="120"/>
								</a>

								<div class="media-body" style="margin-left:180px;">
									<h4 class="media-heading"><?php echo $module_info['title']; ?></h4>

									<p class="version"><?php echo __('Version', 'gmLang') . ': ' . $module_info['version']; ?></p>

									<div class="description"><?php echo str_replace("\n", '<br />', $module_info['description']); ?></div>
								</div>
							</div>
						<?php
						}
					} else{
						_e('No installed modules', 'gmLang');
					}
					?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Cancel', 'gmLang'); ?></button>
				</div>
			</div>
		</div>
	</div>
<?php
}

/**
 * gmediaGalleryEdit()
 *
 * @return mixed content
 */
function gmediaGalleryEdit(){
	global $gmDB, $gmCore, $gmGallery, $gmProcessor, $user_ID;

	$alert = array();

	$module_name = $gmCore->_get('gallery_module');
	$gallery_id = $gmCore->_get('edit_gallery');
	$author_new = false;
	if($gmCore->caps['gmedia_edit_others_media']){
		$author = (int)$gmCore->_get('author', $user_ID);
	} else{
		$author = $user_ID;
	}

	$url = add_query_arg(array('page' => $gmProcessor->page, 'edit_gallery' => $gallery_id), admin_url('admin.php'));

	$gallery = array(
		'name' => '',
		'description' => '',
		'global' => $author,
		'status' => 'public',
		'edited' => '&#8212;',
		'module' => '',
		'query' => array(),
		'settings' => array()
	);
	$taxonomy = 'gmedia_gallery';
	if($gallery_id){
		$url = add_query_arg(array('page' => $gmProcessor->page, 'edit_gallery' => $gallery_id), admin_url('admin.php'));
		$gallery = $gmDB->get_term($gallery_id, $taxonomy, ARRAY_A);
		if(is_wp_error($gallery)){
			$alert[] = $gallery->get_error_message();
		} elseif(empty($gallery)){
			$alert[] = sprintf(__('No gallery with ID #%s in database'), $gallery_id);
		} else{
			if(($gallery['global'] == $author) || $gmCore->caps['gmedia_edit_others_media']){
				$gallery_meta = $gmDB->get_metadata('gmedia_term', $gallery_id);
				$gallery_meta = array_map('reset', $gallery_meta);
				$gallery_meta = array_map('maybe_unserialize', $gallery_meta);
				$gallery = array_merge($gallery, $gallery_meta);
				if(isset($_GET['author']) && ($gallery['global'] != $author)){
					unset($gallery['query']['gmedia_album']);
					$gallery['global'] = $author;
					$author_new = true;
				}
				if(!$module_name){
					$module_name = $gallery['module'];
				}
			} else{
				$alert[] = __('You are not allowed to edit others media');
			}
		}
	} elseif($module_name){
		$url = add_query_arg(array('page' => $gmProcessor->page, 'gallery_module' => $module_name), admin_url('admin.php'));
		$error_post = $gmCore->_post('gallery');
		if($error_post){
			$gallery = $gmCore->array_replace_recursive($gallery, $error_post);
		}
		$gallery['module'] = $module_name;
	}

	if(!empty($alert)){
		echo $gmProcessor->alert('danger', $alert);
		gmediaGalleries();

		return;
	}

	$modules = array();
	if(($plugin_modules = glob(GMEDIA_ABSPATH . 'module/*', GLOB_ONLYDIR | GLOB_NOSORT))){
		foreach($plugin_modules as $path){
			$mfold = basename($path);
			$modules[$mfold] = array(
				'place' => 'plugin',
				'module_name' => $mfold,
				'module_url' => "{$gmCore->gmedia_url}/module/{$mfold}",
				'module_path' => $path
			);
		}
	}
	if(($upload_modules = glob($gmCore->upload['path'] . '/' . $gmGallery->options['folder']['module'] . '/*', GLOB_ONLYDIR | GLOB_NOSORT))){
		foreach($upload_modules as $path){
			$mfold = basename($path);
			$modules[$mfold] = array(
				'place' => 'upload',
				'module_name' => $mfold,
				'module_url' => "{$gmCore->upload['url']}/{$gmGallery->options['folder']['module']}/{$mfold}",
				'module_path' => $path
			);
		}
	}

	$default_options = array();
	/**
	 * @var $place
	 * @var $module_name
	 * @var $module_url
	 * @var $module_path
	 */
	if($module_name){
		if(isset($modules[$module_name])){
			extract($modules[$module_name]);

			/**
			 * @var $module_info
			 *
			 * @var $default_options
			 * @var $options_tree
			 */
			if(file_exists($module_path . '/index.php') && file_exists($module_path . '/settings.php')){
				include($module_path . '/index.php');
				include($module_path . '/settings.php');
			} else{
				$alert[] = sprintf(__('Module `%s` is broken. Choose another module from the list and save settings'), $module_name);
			}
		} else{
			$alert[] = sprintf(__('Can\'t get module with name `%s`. Choose module from the list and save settings'), $module_name);
		}
	} else{
		$alert[] = sprintf(__('Module is not selected for this gallery. Choose module from the list and save settings'), $module_name);
	}

	if(!empty($alert)){
		echo $gmProcessor->alert('danger', $alert);
	}

	if(isset($gallery['settings'][$module_name])){
		$gallery_settings = $gmCore->array_replace_recursive($default_options, $gallery['settings'][$module_name]);
	} else{
		$gallery_settings = $default_options;
	}

	include_once(GMEDIA_ABSPATH . '/inc/module.options.php');

	?>

	<form class="panel panel-default" id="gallerySettingsForm" method="post" action="<?php echo $url; ?>">
	<div class="panel-heading clearfix">
		<div class="btn-toolbar pull-left">
			<div class="btn-group">
				<a href="<?php echo add_query_arg(array('page' => 'GrandMedia_Galleries'), admin_url('admin.php')); ?>" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> <?php _e('Manage Galleries', 'gmLang'); ?>
				</a>
			</div>
			<div class="btn-group">
				<?php if($gallery['module'] != $module_name){ ?>
					<a href="<?php echo $url; ?>" class="btn btn-default"><?php _e('Cancel preview module', 'gmLang'); ?></a>
					<button type="submit" name="gmedia_gallery_save" class="btn btn-primary"><?php _e('Save with new module', 'gmLang'); ?></button>
				<?php } else{ ?>
					<?php $reset_settings = $gmCore->array_diff_keyval_recursive($default_options, $gallery_settings, true);
					if(!empty($reset_settings)){
						?>
						<button type="submit" name="gmedia_gallery_reset" class="btn btn-default" data-confirm="<?php _e('Confirm reset gallery options') ?>"><?php _e('Reset to default', 'gmLang'); ?></button>
					<?php } ?>
					<button type="submit" name="gmedia_gallery_save" class="btn btn-primary"><?php _e('Save', 'gmLang'); ?></button>
				<?php } ?>
			</div>
		</div>
	</div>
	<div class="panel-body" id="gmedia-msg-panel"></div>
	<div class="panel-body" id="gmedia-edit-gallery" style="margin-bottom:4px; padding-top:0;">
	<div class="row">
		<div class="col-lg-6 tabable tabs-left">
			<ul class="nav nav-tabs" id="galleryTabs" style="padding:10px 0;">
				<?php if(isset($module_info)){ ?>
					<li class="text-center">
						<strong><?php echo $module_info['title']; ?></strong><a href="#chooseModuleModal" data-toggle="modal" style="padding:5px 0;"><img src="<?php echo $module_url . '/screenshot.png'; ?>" alt="<?php echo esc_attr($module_info['title']); ?>" width="100" style="height:auto;"/></a>
					</li>
				<?php } else{ ?>
					<li class="text-center"><strong><?php echo $gallery['module']; ?></strong>

						<p><?php _e('This module is broken or outdated. Please, go to Modules page and update/install module.', 'gmLang'); ?></p>
						<a href="#chooseModuleModal" data-toggle="modal" style="padding:5px 0;"><img src="<?php echo $module_url . '/screenshot.png'; ?>" alt="<?php echo esc_attr($gallery['module']); ?>" width="100" style="height:auto;"/></a>
					</li>
				<?php } ?>
				<li class="active"><a href="#general_settings" data-toggle="tab"><?php _e('General Settings', 'gmLang'); ?></a></li>
				<?php
				if(isset($options_tree)){
					gmedia_gallery_options_nav($options_tree);
				}
				?>
			</ul>

			<div id="gallery_options_block" class="tab-content" style="padding-top:20px;">

				<fieldset id="general_settings" class="tab-pane active">
					<p><?php echo '<b>' . __('Gallery module:') . '</b> ' . $gallery['module'];
						if($gallery['module'] != $module_name){
							echo '<br /><b>' . __('Preview module:') . '</b> ' . $module_name;
							echo '<br /><span class="text-muted">' . sprintf(__('Note: Module changed to %s, but not saved yet'), $module_name) . '</span>';
						}  ?></p>

					<p><b><?php _e('Gallery author:', 'gmLang'); ?></b>
						<?php if($gmCore->caps['gmedia_edit_others_media']){ ?>
							<a href="#termsModal" data-modal="filter_authors" data-action="gmedia_terms_modal" class="gmedia-modal" title="<?php _e('Click to choose author for gallery', 'gmLang'); ?>"><?php echo $gallery['global']? get_the_author_meta('display_name', $gallery['global']) : __('(no author / shared albums)'); ?></a>
							<?php if($author_new){
								echo '<br /><span class="text-danger">' . __('Note: Author changed but not saved yet. You can see Albums list only of chosen author') . '</span>';
							} ?>
						<?php } else{
							echo $gallery['global']? get_the_author_meta('display_name', $gallery['global']) : '&#8212;';
						} ?>
						<input type="hidden" name="gallery[global]" value="<?php echo $gallery['global']; ?>"/></p>
					<?php if($gallery_id){ ?>
						<p><b><?php _e('Shortcode:'); ?></b> [gmedia id=<?php echo $gallery_id; ?>]</p>
					<?php } ?>
					<input type="hidden" name="gallery[module]" value="<?php echo esc_attr($module_name); ?>">

					<div class="form-group">
						<label><?php _e('Gallery Name', 'gmLang'); ?></label>
						<input type="text" class="form-control input-sm" name="gallery[name]" placeholder="<?php echo empty($gallery['name'])? esc_attr(__('Gallery Name', 'gmLang')) : esc_attr($gallery['name']); ?>" value="<?php echo esc_attr($gallery['name']); ?>" required="required"/>
					</div>
					<div class="form-group">
						<label><?php _e('Status', 'gmLang'); ?></label>
						<select name="gallery[status]" class="form-control input-sm">
							<option value="public"<?php selected($gallery['status'], 'public'); ?>><?php _e('Public', 'gmLang'); ?></option>
							<?php /* ?>
									<option value="private"<?php selected($gallery['status'], 'private'); ?>><?php _e('Private', 'gmLang'); ?></option>
									<option value="draft"<?php selected($gallery['status'], 'draft'); ?>><?php _e('Draft', 'gmLang'); ?></option>
								<?php */
							?>
						</select>
					</div>
					<div class="form-group">
						<label><?php _e('Show supported files from', 'gmLang'); ?></label>
						<select data-watch="change" id="gmedia_query" class="form-control input-sm" name="gallery[term]">
							<?php reset($gallery['query']);
							$gallery['term'] = key($gallery['query']); ?>
							<?php if($gmCore->caps['gmedia_terms']){ ?>
								<option value="gmedia_album"<?php selected($gallery['term'], 'gmedia_album'); ?>><?php _e('Albums', 'gmLang'); ?></option>
								<option value="gmedia_tag"<?php selected($gallery['term'], 'gmedia_tag'); ?>><?php _e('Tags', 'gmLang'); ?></option>
								<option value="gmedia_category"<?php selected($gallery['term'], 'gmedia_category'); ?>><?php _e('Categories', 'gmLang'); ?></option>
							<?php } ?>
							<option value="gmedia__in"<?php selected($gallery['term'], 'gmedia__in'); ?>><?php _e('Selected Gmedia', 'gmLang'); ?></option>
							<!-- <option value="gmedia_filter"<?php selected($gallery['term'], 'gmedia_filter'); ?>><?php _e('Filter', 'gmLang'); ?></option> -->
						</select>
					</div>

					<?php if($gmCore->caps['gmedia_terms']){ ?>
						<div class="form-group" id="div_gmedia_category">
							<?php
							$term_type = 'gmedia_category';
							$gm_terms_all = $gmGallery->options['taxonomies'][$term_type];
							$gm_terms = $gmDB->get_terms($term_type, array('fields' => 'names_count'));

							$terms_items = '';
							if(count($gm_terms)){
								foreach($gm_terms as $id => $term){
									$selected = (isset($gallery['query'][$term_type]) && in_array($id, $gallery['query'][$term_type]))? ' selected="selected"' : '';
									$terms_items .= '<option value="' . $id . '"' . $selected . '>' . esc_html($gm_terms_all[$term['name']]) . ' (' . $term['count'] . ')</option>' . "\n";
								}
							}
							$setvalue = isset($gallery['query'][$term_type])? 'data-setvalue="' . implode(',', $gallery['query'][$term_type]) . '"' : '';
							?>
							<label><?php _e('Choose Categories', 'gmLang'); ?></label>
							<select data-gmedia_query="is:gmedia_category" <?php echo $setvalue; ?> id="gmedia_category" name="gallery[query][gmedia_category][]" class="gmedia-combobox form-control input-sm" multiple="multiple" placeholder="<?php echo esc_attr(__('Choose Categories...', 'gmLang')); ?>">
								<option value=""><?php _e('Choose Categories...', 'gmLang'); ?></option>
								<?php echo $terms_items; ?>
							</select>
						</div>

						<div class="form-group" id="div_gmedia_tag">
							<?php
							$term_type = 'gmedia_tag';
							$gm_terms = $gmDB->get_terms($term_type, array('fields' => 'names_count'));

							$terms_items = '';
							if(count($gm_terms)){
								foreach($gm_terms as $id => $term){
									$selected = (isset($gallery['query'][$term_type]) && in_array($id, $gallery['query'][$term_type]))? ' selected="selected"' : '';
									$terms_items .= '<option value="' . $id . '"' . $selected . '>' . esc_html($term['name']) . ' (' . $term['count'] . ')</option>' . "\n";
								}
							}
							$setvalue = isset($gallery['query'][$term_type])? 'data-setvalue="' . implode(',', $gallery['query'][$term_type]) . '"' : '';
							?>
							<label><?php _e('Choose Tags', 'gmLang'); ?> </label>
							<select data-gmedia_query="is:gmedia_tag" <?php echo $setvalue; ?> id="gmedia_tag" name="gallery[query][gmedia_tag][]" class="gmedia-combobox form-control input-sm" multiple="multiple" placeholder="<?php echo esc_attr(__('Choose Tags...', 'gmLang')); ?>">
								<option value=""><?php echo __('Choose Tags...', 'gmLang'); ?></option>
								<?php echo $terms_items; ?>
							</select>
						</div>

						<div class="form-group" id="div_gmedia_album">
							<?php
							$term_type = 'gmedia_album';
							$args = array();
							$args['global'] = $gallery['global']? array(0, $gallery['global']) : 0;
							$gm_terms = $gmDB->get_terms($term_type, $args);

							$terms_items = '';
							if(count($gm_terms)){
								foreach($gm_terms as $term){
									//if(!$term->count){ continue; }
									$selected = (isset($gallery['query'][$term_type]) && in_array($term->term_id, $gallery['query'][$term_type]))? ' selected="selected"' : '';
									$terms_items .= '<option value="' . $term->term_id . '"' . $selected . '>' . esc_html($term->name) . ' &nbsp; (' . $term->count . ')</option>' . "\n";
								}
							}
							$setvalue = isset($gallery['query'][$term_type])? 'data-setvalue="' . implode(',', $gallery['query'][$term_type]) . '"' : '';
							?>
							<label><?php _e('Choose Albums', 'gmLang'); ?> </label>
							<select data-gmedia_query="is:gmedia_album" <?php echo $setvalue; ?> id="gmedia_album" name="gallery[query][gmedia_album][]" class="gmedia-combobox form-control input-sm" multiple="multiple" placeholder="<?php echo esc_attr(__('Choose Albums...', 'gmLang')); ?>">
								<option value=""><?php echo __('Choose Albums...', 'gmLang'); ?></option>
								<?php echo $terms_items; ?>
							</select>

							<p class="help-block"><?php _e('You can choose Albums from the same author as Gallery author or Albums without author', 'gmLang'); ?></p>
						</div>
					<?php } ?>

					<div class="form-group" id="div_gmedia__in">
						<label><?php _e('Selected Gmedia IDs <small class="text-muted">separated by comma</small>', 'gmLang'); ?> </label>
						<?php $value = isset($gallery['query']['gmedia__in'][0])? implode(',', wp_parse_id_list($gallery['query']['gmedia__in'][0])) : ''; ?>
						<textarea data-gmedia_query="is:gmedia__in" id="gmedia__in" name="gallery[query][gmedia__in][]" rows="1" class="form-control input-sm" style="resize:vertical;" placeholder="<?php echo esc_attr(__('Gmedia IDs...', 'gmLang')); ?>"><?php echo $value; ?></textarea>
					</div>

					<div class="form-group">
						<label><?php _e('Description', 'gmLang'); ?></label>
						<textarea class="form-control input-sm" rows="5" name="gallery[description]"><?php echo esc_html($gallery['description']) ?></textarea>
					</div>

				</fieldset>

				<?php
				if(isset($options_tree)){
					gmedia_gallery_options_fieldset($options_tree, $default_options, $gallery_settings);
				}
				?>
			</div>

		</div>
		<div class="col-lg-6" style="padding-top:20px;">
			<p><b><?php _e('Last edited:'); ?></b> <?php echo $gallery['edited']; ?></p>
			<?php if($gallery_id){
				$params = array();
				$params['preview'] = ($gallery['module'] != $module_name)? $module_name : false;
				$params['iframe'] = 1;
				?>
				<p><b><?php _e('Gallery ID:'); ?></b> #<?php echo $gallery_id; ?></p>
				<p><b><?php _e('Gallery URL:'); ?></b> <?php
					$gallery_link_default = home_url('index.php?gmedia=' . $gallery_id);
					if(get_option('permalink_structure')){
						$ep = $gmGallery->options['endpoint'];
						$gallery_link = home_url($ep . '/' . $gallery_id);
					} else{
						$gallery_link = $gallery_link_default;
					} ?>
					<a target="_blank" href="<?php echo $gallery_link; ?>"><?php echo $gallery_link; ?></a>
					<br/><?php _e('update <a href="options-permalink.php">Permalink Settings</a> if above link not working', 'gmLang'); ?>
				</p>

				<div><b><?php _e('Gallery Preview:'); ?></b></div>
				<div class="gallery_preview" style="overflow:hidden;">
					<iframe id="gallery_preview" name="gallery_preview" src="<?php echo add_query_arg($params, $gallery_link_default); ?>"></iframe>
				</div>
			<?php } ?>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(function($){
			<?php if(!empty($alert)){ ?>
			$('#chooseModuleModal').modal('show');
			<?php } ?>

			var hash = window.location.hash;
			if(hash){
				$('#galleryTabs a').eq(hash.replace('#tab-', '')).tab('show');
			}
			$('#gallerySettingsForm').on('submit', function(){
				$(this).attr('action', $(this).attr('action') + '#tab-' + $('#galleryTabs li.active').index());
			});

			<?php if($gmCore->caps['gmedia_terms']){ ?>
			$('.gmedia-combobox').each(function(){
				var select = $(this).selectize({
					plugins: ['drag_drop'],
					create: false,
					hideSelected: true
				});
				var val = $(this).data('setvalue');
				if(val){
					val = val.toString().split(',');
					select[0].selectize.setValue(val);
				}
			});
			<?php } ?>

			var main = $('#gallery_options_block');

			$('input', main).filter('[data-type="color"]').minicolors({
				animationSpeed: 50,
				animationEasing: 'swing',
				change: null,
				changeDelay: 0,
				control: 'hue',
				//defaultValue: '',
				hide: null,
				hideSpeed: 100,
				inline: false,
				letterCase: 'lowercase',
				opacity: false,
				position: 'bottom left',
				show: null,
				showSpeed: 100,
				theme: 'bootstrap'
			});

			$('[data-watch]', main).each(function(){
				var el = $(this);
				gmedia_options_conditional_logic(el, 0);
				el.on(el.data('watch'), function(){
					if('change' == el.data('watch')){
						$(this).blur().focus();
					}
					gmedia_options_conditional_logic($(this), 400);
				});
			});

			function gmedia_options_conditional_logic(el, slide){
				if(el.is(':input')){
					var val = el.val();
					var id = el.attr('id').toLowerCase();
					if(el.is(':checkbox') && !el[0].checked){
						val = '0';
					}
					$('[data-' + id + ']', main).each(function(){
						var key = $(this).data(id);
						key = key.split(':');
						//var hidden = $(this).data('hidden')? parseInt($(this).data('hidden')) : 0;
						var hidden = $(this).data('hidden')? $(this).data('hidden') : {};
						var ch = true;
						switch(key[0]){
							case '=':
							case 'is':
								if(val == key[1]){
									delete hidden[id];
									if(slide && $.isEmptyObject(hidden)){
										$(this).prop('disabled', false).closest('.form-group').stop().slideDown(slide, function(){
											$(this).css({display: 'block'});
										});
										if(key[2]){
											key[2] = $(this).data('value');
										} else{
											ch = false;
										}
									} else{
										ch = false;
									}
									$(this).data('hidden', hidden);
								} else{
									if($.isEmptyObject(hidden)){
										if(key[2]){
											$(this).closest('.form-group').stop().slideUp(slide, function(){
												$(this).css({display: 'none'});
											});
										} else{
											$(this).prop('disabled', true).closest('.form-group').stop().slideUp(slide, function(){
												$(this).css({display: 'none'});
											});
										}
									} else{
										ch = false;
									}
									hidden[id] = 1;
									$(this).data('hidden', hidden);
								}
								break;
							case '!=':
							case 'not':
								if(val == key[1]){
									if($.isEmptyObject(hidden)){
										if(key[2]){
											$(this).closest('.form-group').stop().slideUp(slide, function(){
												$(this).css({display: 'none'});
											});
										} else{
											$(this).prop('disabled', true).closest('.form-group').stop().slideUp(slide, function(){
												$(this).css({display: 'none'});
											});
										}
									} else{
										ch = false;
									}
									hidden[id] = 1;
									$(this).data('hidden', hidden);
								} else{
									delete hidden[id];
									if(slide && $.isEmptyObject(hidden)){
										$(this).prop('disabled', false).closest('.form-group').stop().slideDown(slide, function(){
											$(this).css({display: 'block'});
										});
										if(key[2] && slide){
											key[2] = $(this).data('value');
										} else{
											ch = false;
										}
									} else{
										ch = false;
									}
									$(this).data('hidden', hidden);
								}
								break;
						}
						if(key[2] && ch){
							if($(this).is(':checkbox')){
								if(+($(this).prop('checked')) != parseInt(key[2])){
									$(this).data('value', ($(this).prop('checked')? '1' : '0'));
									$(this).prop('checked', ('0' != key[2])).trigger('change');
								}
							} else{
								if($(this).val() != key[2]){
									$(this).data('value', $(this).val());
									$(this).val(key[2]).trigger('change');
								}
							}
						}
					});
				}
			}
		});
	</script>
	</div>
	<?php
	wp_nonce_field('GmediaGallery');
	?>
	</form>

	<!-- Modal -->
	<?php if($gmCore->caps['gmedia_edit_others_media']){ ?>
		<div class="modal fade gmedia-modal" id="termsModal" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog"></div>
		</div>
	<?php } ?>

	<div class="modal fade gmedia-modal" id="chooseModuleModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php _e('Choose Module for Gallery'); ?></h4>
				</div>
				<div class="modal-body linkblock">
					<?php
					if(!empty($alert)){
						echo $gmProcessor->alert('danger', $alert);
					}

					$current_module = $module_name;
					if(!empty($modules)){
						foreach($modules as $m){
							/**
							 * @var $module_name
							 * @var $module_url
							 * @var $module_path
							 */
							extract($m);
							if(($module_name == $current_module) || !file_exists($module_path . '/index.php')){
								continue;
							}
							$module_info = array();
							include($module_path . '/index.php');
							if(empty($module_info)){
								continue;
							}
							$mclass = ' module-' . $module_info['type'] . ' module-' . $module_info['status'];
							?>
							<div data-href="<?php echo add_query_arg(array(
								'edit_gallery' => $gallery_id,
								'gallery_module' => $module_name
							), $url); ?>" class="choose-module media<?php echo $mclass; ?>">
								<a href="<?php echo add_query_arg(array(
									'edit_gallery' => $gallery_id,
									'gallery_module' => $module_name
								), $url); ?>" class="thumbnail pull-left">
									<img class="media-object" src="<?php echo $module_url . '/screenshot.png'; ?>" alt="<?php echo esc_attr($module_info['title']); ?>" width="160" height="120"/>
								</a>

								<div class="media-body" style="margin-left:180px;">
									<h4 class="media-heading"><?php echo $module_info['title']; ?></h4>

									<p class="version"><?php echo __('Version', 'gmLang') . ': ' . $module_info['version']; ?></p>

									<div class="description"><?php echo str_replace("\n", '<br />', $module_info['description']); ?></div>
								</div>
							</div>
						<?php
						}
					} else{
						_e('No installed modules', 'gmLang');
					}
					?>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Cancel', 'gmLang'); ?></button>
				</div>
			</div>
		</div>
	</div>

<?php
}

