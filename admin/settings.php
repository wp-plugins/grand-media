<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * gmSettings()
 *
 * @return mixed content
 */
function gmSettings() {
	global $gmCore, $gmGallery;

	?>

	<form class="panel panel-default" method="post">
		<div class="panel-heading clearfix">
			<div class="btn-toolbar pull-left">
				<div class="btn-group">
					<button type="submit" name="gmedia_settings_reset" class="btn btn-default" data-confirm="<?php _e('Reset all Gmedia settings?') ?>"><?php _e('Reset Settings', 'gmLang'); ?></button>
					<button type="submit" name="gmedia_settings_save" class="btn btn-primary"><?php _e('Update', 'gmLang'); ?></button>
					<?php
					wp_nonce_field('GmediaSettings');
					?>
				</div>
			</div>
		</div>
		<div class="panel-body" id="gmedia-msg-panel"></div>
		<div class="container-fluid">
			<div class="tabable tabs-left">
				<ul class="nav nav-tabs" style="padding:10px 0;">
					<li class="active"><a href="#gmedia_premium" data-toggle="tab"><?php _e('Premium Settings', 'gmLang'); ?></a></li>
					<li><a href="#gmedia_settings1" data-toggle="tab"><?php _e('Other Settings', 'gmLang'); ?></a></li>
				</ul>
				<div class="tab-content" style="padding-top:21px;">
					<fieldset id="gmedia_premium" class="tab-pane active">
						<p><?php _e('Enter License Key to remove backlink label from premium gallery modules.') ?></p>
						<div class="row">
							<div class="form-group col-xs-5">
								<label><?php _e( 'License Key', 'gmLang' ) ?>: <?php if(isset($gmGallery->options['license_name'])){ echo '<em>'.$gmGallery->options['license_name'].'</em>'; } ?></label>
								<input type="text" name="set[license_key]" id="license_key" class="form-control input-sm" value="<?php if(isset($gmGallery->options['license_key'])){ echo $gmGallery->options['license_key']; } ?>"/>
								<input type="hidden" name="set[license_name]" id="license_name" value="<?php echo $gmGallery->options['license_name']; ?>"/>
								<input type="hidden" name="set[license_key2]" id="license_key2" value="<?php echo $gmGallery->options['license_key2']; ?>"/>
							</div>
							<div class="form-group col-xs-7">
								<label>&nbsp;</label>
								<button style="display:block;" class="btn btn-success btn-sm" type="submit" name="license-key-activate"><?php _e('Activate Key', 'gmLang'); ?></button>
							</div>
						</div>
					</fieldset>
					<fieldset id="gmedia_settings1" class="tab-pane">
						<div class="form-group">
							<label><?php _e( 'Delete uploaded files when delete (uninstall) plugin?', 'gmLang' ) ?>:</label>
							<div class="checkbox" style="margin:0;">
								<input type="hidden" name="set[uninstall_dropfiles]" value=""/>
								<label><input type="checkbox" name="set[uninstall_dropfiles]" value="dropfiles"<?php checked($gmGallery->options['uninstall_dropfiles'], 'dropfiles'); ?> /> <?php _e('delete', 'gmLang'); ?></label>
								<p class="help-block"><?php _e('Note: Database tables will be deleted anyway', 'gmLang'); ?></p>
							</div>
						</div>
						<p><?php _e('Under constraction...') ?></p>

						<?php
						if($gmCore->_get('showdb')){
							global $wpdb, $gmDB;
							$gmedia = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gmedia");
							$terms = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gmedia_term");
							$relation = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}gmedia_term_relationships");
							$images['grand-media'] = glob($gmCore->upload['path'].'/*', GLOB_NOSORT);
							$images['images'] = glob($gmCore->upload['path'].'/image/*', GLOB_NOSORT);
							$images['thumbs'] = glob($gmCore->upload['path'].'/thumb/*', GLOB_NOSORT);
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