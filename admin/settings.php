<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	die('You are not allowed to call this page directly.');
}

/**
 * gmSettings()
 *
 * @return mixed content
 */
function gmSettings(){
	global $gmDB, $gmCore, $gmGallery;

	?>

	<form class="panel panel-default" method="post">
		<div class="panel-heading clearfix">
			<div class="btn-toolbar pull-left">
				<div class="btn-group">
					<button type="submit" name="gmedia_settings_reset" class="btn btn-default" data-confirm="<?php _e('Reset all Gmedia settings?', 'gmLang') ?>"><?php _e('Reset Settings', 'gmLang'); ?></button>
					<button type="submit" name="gmedia_settings_save" class="btn btn-primary"><?php _e('Update', 'gmLang'); ?></button>
				</div>
			</div>
			<?php
			wp_nonce_field('GmediaSettings');
			?>
		</div>
		<div class="panel-body" id="gmedia-msg-panel"></div>
		<div class="container-fluid">
			<div class="tabable tabs-left">
				<ul class="nav nav-tabs" style="padding:10px 0;">
					<li class="active"><a href="#gmedia_premium" data-toggle="tab"><?php _e('Premium Settings', 'gmLang'); ?></a></li>
					<?php if(current_user_can('manage_options')){ ?>
						<li><a href="#gmedia_settings1" data-toggle="tab"><?php _e('Roles/Capabilities Manager', 'gmLang'); ?></a></li><?php } ?>
					<li><a href="#gmedia_settings2" data-toggle="tab"><?php _e('Other Settings', 'gmLang'); ?></a></li>
					<li><a href="#gmedia_settings3" data-toggle="tab"><?php _e('System Info', 'gmLang'); ?></a></li>
				</ul>
				<div class="tab-content" style="padding-top:21px;">
					<fieldset id="gmedia_premium" class="tab-pane active">
						<p><?php _e('Enter License Key to remove backlink label from premium gallery modules.') ?></p>

						<div class="row">
							<div class="form-group col-xs-5">
								<label><?php _e('License Key', 'gmLang') ?>: <?php if(isset($gmGallery->options['license_name'])){
										echo '<em>' . $gmGallery->options['license_name'] . '</em>';
									} ?></label>
								<input type="text" name="set[license_key]" id="license_key" class="form-control input-sm" value="<?php if(isset($gmGallery->options['license_key'])){
									echo $gmGallery->options['license_key'];
								} ?>"/>
								<input type="hidden" name="set[license_name]" id="license_name" value="<?php echo $gmGallery->options['license_name']; ?>"/>
								<input type="hidden" name="set[license_key2]" id="license_key2" value="<?php echo $gmGallery->options['license_key2']; ?>"/>
							</div>
							<div class="form-group col-xs-7">
								<label>&nbsp;</label>
								<button style="display:block;" class="btn btn-success btn-sm" type="submit" name="license-key-activate"><?php _e('Activate Key', 'gmLang'); ?></button>
							</div>
						</div>
					</fieldset>
					<?php if(current_user_can('manage_options')){ ?>
						<fieldset id="gmedia_settings1" class="tab-pane">
							<p><?php _e('Select the lowest role which should be able to access the follow capabilities. Gmedia Gallery supports the standard roles from WordPress.', 'gmLang'); ?></p>

							<div class="form-group">
								<label><?php _e('Gmedia Library', 'gmLang') ?>:</label>
								<select name="capability[gmedia_library]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_library')); ?></select>

								<p class="help-block"><?php _e('Who can view Gmedia Gallery admin pages', 'gmLang'); ?></p>
							</div>
							<hr/>

							<div class="form-group">
								<label><?php _e('Upload Media Files', 'gmLang') ?>:</label>
								<select name="capability[gmedia_upload]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_upload')); ?></select>

								<p class="help-block"><?php _e('Who can upload files to Gmedia Library', 'gmLang'); ?></p>
							</div>
							<div class="col-xs-offset-1">
								<div class="form-group">
									<label><?php _e('Import Media Files', 'gmLang') ?>:</label>
									<select name="capability[gmedia_import]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_import')); ?></select>

									<p class="help-block"><?php _e('Who can import files to Gmedia Library', 'gmLang'); ?></p>
								</div>
							</div>

							<div class="form-group">
								<label><?php _e('Show Others Media in Library', 'gmLang') ?>:</label>
								<select name="capability[gmedia_show_others_media]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_show_others_media')); ?></select>

								<p class="help-block"><?php _e('Who can see files uploaded by other users', 'gmLang'); ?></p>
							</div>
							<div class="form-group">
								<label><?php _e('Edit Media', 'gmLang') ?>:</label>
								<select name="capability[gmedia_edit_media]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_edit_media')); ?></select>

								<p class="help-block"><?php _e('Who can edit media title, description and other properties of uploaded files', 'gmLang'); ?></p>
							</div>
							<div class="col-xs-offset-1">
								<div class="form-group">
									<label><?php _e('Edit Others Media', 'gmLang') ?>:</label>
									<select name="capability[gmedia_edit_others_media]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_edit_others_media')); ?></select>

									<p class="help-block"><?php _e('Who can edit files, albums/tags and galleries of other users', 'gmLang'); ?></p>
								</div>
							</div>
							<div class="form-group">
								<label><?php _e('Delete Media', 'gmLang') ?>:</label>
								<select name="capability[gmedia_delete_media]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_delete_media')); ?></select>

								<p class="help-block"><?php _e('Who can delete uploaded files from Gmedia Library', 'gmLang'); ?></p>
							</div>
							<div class="col-xs-offset-1">
								<div class="form-group">
									<label><?php _e('Delete Others Media', 'gmLang') ?>:</label>
									<select name="capability[gmedia_delete_others_media]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_delete_others_media')); ?></select>

									<p class="help-block"><?php _e('Who can delete files, albums/tags and galleries of other users', 'gmLang'); ?></p>
								</div>
							</div>

							<div class="form-group">
								<label><?php _e('Albums, Tags...', 'gmLang') ?>:</label>
								<select name="capability[gmedia_terms]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_terms')); ?></select>

								<p class="help-block"><?php _e('Who can assign available terms to media files', 'gmLang'); ?></p>
							</div>
							<div class="col-xs-offset-1">
								<div class="form-group">
									<label><?php _e('Manage Albums', 'gmLang') ?>:</label>
									<select name="capability[gmedia_album_manage]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_album_manage')); ?></select>

									<p class="help-block"><?php _e('Who can create and edit own albums. It is required "Edit Others Media" capability to edit others and shared albums', 'gmLang'); ?></p>
								</div>
								<div class="form-group">
									<label><?php _e('Manage Tags', 'gmLang') ?>:</label>
									<select name="capability[gmedia_tag_manage]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_tag_manage')); ?></select>

									<p class="help-block"><?php _e('Who can create new tags. It is required "Edit Others Media" capability to edit tags', 'gmLang'); ?></p>
								</div>
								<div class="form-group">
									<label><?php _e('Delete Terms', 'gmLang') ?>:</label>
									<select name="capability[gmedia_terms_delete]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_terms_delete')); ?></select>

									<p class="help-block"><?php _e('Who can delete own albums. It is required "Delete Others Media" capability to delete others terms', 'gmLang'); ?></p>
								</div>
							</div>

							<div class="form-group">
								<label><?php _e('Galleries', 'gmLang') ?>:</label>
								<select name="capability[gmedia_gallery_manage]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_gallery_manage')); ?></select>

								<p class="help-block"><?php _e('Who can create, edit and delete own galleries', 'gmLang'); ?></p>
							</div>

							<div class="form-group">
								<label><?php _e('Modules', 'gmLang') ?>:</label>
								<select name="capability[gmedia_module_manage]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_module_manage')); ?></select>

								<p class="help-block"><?php _e('Who can manage modules', 'gmLang'); ?></p>
							</div>

							<div class="form-group">
								<label><?php _e('Settings', 'gmLang') ?>:</label>
								<select name="capability[gmedia_settings]" class="form-control input-sm"><?php wp_dropdown_roles($gmDB->get_role('gmedia_settings')); ?></select>

								<p class="help-block"><?php _e('Who can change settings. Note: Capabilites can be changed only by administrator', 'gmLang'); ?></p>
							</div>

						</fieldset>
					<?php } ?>
					<fieldset id="gmedia_settings2" class="tab-pane">
						<div class="form-group">
							<label><?php _e('When delete (uninstall) plugin', 'gmLang') ?>:</label>
							<select name="set[uninstall_dropdata]" class="form-control input-sm">
								<option value="all" <?php selected($gmGallery->options['uninstall_dropdata'], 'all'); ?>><?php _e('Delete database and all uploaded files', 'gmLang'); ?></option>
								<option value="db" <?php selected($gmGallery->options['uninstall_dropdata'], 'db'); ?>><?php _e('Delete database only and leave uploaded files', 'gmLang'); ?></option>
								<option value="none" <?php selected($gmGallery->options['uninstall_dropdata'], 'none'); ?>><?php _e('Do not delete database and uploaded files', 'gmLang'); ?></option>
							</select>
						</div>
						<div class="form-group">
							<label><?php _e('Forbid other plugins to load their JS and CSS on Gmedia admin pages', 'gmLang') ?>:</label>

							<div class="checkbox" style="margin:0;">
								<input type="hidden" name="set[isolation_mode]" value="0"/>
								<label><input type="checkbox" name="set[isolation_mode]" value="1" <?php checked($gmGallery->options['isolation_mode'], '1'); ?> /> <?php _e('Enable Gmedia admin panel Isolation Mode', 'gmLang'); ?></label>

								<p class="help-block"><?php _e('This option could help to avoid JS and CSS conflicts with other plugins in admin panel.', 'gmLang'); ?></p>
							</div>
						</div>
						<div class="form-group">
							<label><?php _e('Forbid theme to format Gmedia shortcode\'s content', 'gmLang') ?>:</label>

							<div class="checkbox" style="margin:0;">
								<input type="hidden" name="set[shortcode_raw]" value="0"/>
								<label><input type="checkbox" name="set[shortcode_raw]" value="1" <?php checked($gmGallery->options['shortcode_raw'], '1'); ?> /> <?php _e('Raw output for Gmedia Shortcode', 'gmLang'); ?></label>

								<p class="help-block"><?php _e('Some themes reformat shortcodes and break it functionality (mostly when you add description to images). Turning this on should solve this problem.', 'gmLang'); ?></p>
							</div>
						</div>
						<?php
						$allowed_post_types = (array) $gmGallery->options['gmedia_post_types_support'];
						$args = array(
							'public' => true,
							'_builtin' => false
						);
						$output = 'objects'; // names or objects, note names is the default
						$operator = 'and'; // 'and' or 'or'
						$post_types = get_post_types($args, $output, $operator);
						if(!empty($post_types)){ ?>
							<div class="form-group">
								<label style="margin-bottom:-5px;"><?php _e('Enable Gmedia Library button on custom post types', 'gmLang') ?>:</label>
								<input type="hidden" name="set[gmedia_post_types_support]" value=""/>
								<?php
								foreach($post_types as $post_type){ ?>
									<div class="checkbox">
										<label><input type="checkbox" name="set[gmedia_post_types_support][]" value="<?php echo $post_type->name; ?>" <?php if(in_array($post_type->name, $allowed_post_types)){
												echo 'checked="checked"';
											}; ?> /> <?php echo $post_type->label . ' (' . $post_type->name . ')'; ?></label>
									</div>
								<?php } ?>
							</div>
						<?php } ?>
					</fieldset>
					<fieldset id="gmedia_settings3" class="tab-pane">
						<?php
						if((function_exists('memory_get_usage')) && (ini_get('memory_limit'))){
							$memory_limit = ini_get('memory_limit');
							$memory_usage = memory_get_usage();
							echo '<p>' . __('Memory Limit: ', 'gmLang') . $memory_limit . '</p>';
							echo '<p>' . __('Memory Used: ', 'gmLang') . $memory_usage . '</p>';
						}
						?>
						<p><?php _e('Under constraction...') ?></p>

						<?php
						if($gmCore->_get('showdb')){
							global $wpdb;
							$gmedia = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gmedia");
							$terms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gmedia_term");
							$relation = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gmedia_term_relationships");
							$images['grand-media'] = glob($gmCore->upload['path'] . '/*', GLOB_NOSORT);
							$images['images'] = glob($gmCore->upload['path'] . '/image/*', GLOB_NOSORT);
							$images['thumbs'] = glob($gmCore->upload['path'] . '/thumb/*', GLOB_NOSORT);
							echo '<pre style="max-height:400px; overflow:auto;">' . print_r($gmedia, true) . '</pre>';
							echo '<pre style="max-height:400px; overflow:auto;">' . print_r($images, true) . '</pre>';
							echo '<pre style="max-height:400px; overflow:auto;">' . print_r($terms, true) . '</pre>';
							echo '<pre style="max-height:400px; overflow:auto;">' . print_r($relation, true) . '</pre>';
						}
						?>
					</fieldset>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</form>
<?php
}