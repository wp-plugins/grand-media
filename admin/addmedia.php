<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	die('You are not allowed to call this page directly.');
}

/**
 * gmedia_AddMedia()
 *
 * @return mixed content
 */
function gmedia_AddMedia(){
	global $gmCore;
	$tab = $gmCore->_get('tab', 'upload');
	$extra_tools = (defined('GMEDIA_IFRAME_TOOL') && GMEDIA_IFRAME_TOOL)? false : true;
	?>
	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<?php if($extra_tools){ ?>
				<div class="btn-toolbar pull-left">
					<div class="btn-group">
						<a class="btn btn<?php echo ($tab == 'upload')? '-primary active' : '-default'; ?>" href="<?php echo $gmCore->get_admin_url(array(), array('tab'));; ?>"><?php _e('Upload Files', 'gmLang'); ?></a>
						<a class="btn btn<?php echo ($tab == 'import')? '-primary active' : '-default'; ?>" href="<?php echo $gmCore->get_admin_url(array('tab' => 'import')); ?>"><?php _e('Import', 'gmLang'); ?></a>
					</div>
				</div>
			<?php } ?>
			<div id="total-progress-info" class="progress pull-right">
				<?php
				if($tab == 'upload'){
					$msg = __('Add files to the upload queue and click the start button', 'gmLang');
				}
				if($tab == 'import'){
					$msg = __('Grab files from other sources', 'gmLang');
				}
				?>
				<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0;">
					<div style="padding: 2px 10px;"><?php echo $msg; ?></div>
				</div>
				<div style="padding: 2px 10px;"><?php echo $msg; ?></div>
			</div>
		</div>
		<div class="panel-body" id="gmedia-msg-panel"></div>
		<div class="container-fluid gmAddMedia">
			<?php
			if($tab == 'upload'){
				gmedia_upload_files();
			}
			if($tab == 'import'){
				gmedia_import();
			}
			?>
			<?php wp_original_referer_field(true, 'previous'); ?>
		</div>
	</div>
<?php
}


function gmedia_upload_files(){
	global $gmCore, $gmDB, $gmProcessor, $gmGallery;

	$maxupsize = wp_max_upload_size();
	$maxupsize = floor($maxupsize * 0.99);
	$maxupsize_mb = floor($maxupsize / 1024 / 1024);

	$gm_screen_options = $gmProcessor->user_options();

	?>
	<form class="row" id="gmUpload" name="upload_form" method="POST" accept-charset="utf-8" onsubmit="return false;">
		<div class="col-md-4" id="uploader_multipart_params">
			<br/>

			<p class="clearfix text-right">
				<?php if ('false' == $gm_screen_options['uploader_chunking'] || ('html4' == $gm_screen_options['uploader_runtime'])){ ?>
					<span class="label label-default"><?php echo __('Maximum file size', 'gmLang') . ": {$maxupsize_mb}Mb"; ?></span>
				<?php } else{ ?>
					<span class="label label-default"><?php echo __('Maximum $_POST size', 'gmLang') . ": {$maxupsize_mb}Mb"; ?></span>
					<span class="label label-default hidden"><?php echo __('Chunk size', 'gmLang') . ': ' . min($maxupsize_mb, $gm_screen_options['uploader_chunk_size']) . 'Mb'; ?></span>
				<?php } ?>
			</p>

			<div class="form-group">
				<?php
				$term_type = 'gmedia_category';
				$gm_terms = $gmGallery->options['taxonomies'][$term_type];

				$terms_category = '';
				if(count($gm_terms)){
					foreach($gm_terms as $term_name => $term_title){
						$terms_category .= '<option value="' . $term_name . '">' . $term_title . '</option>' . "\n";
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
						$terms_album .= '<option value="' . $term->name . '">' . $term->name . '</option>' . "\n";
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
					$('#uploader_runtime select').change(function(){
						if('html4' == $(this).val()){
							$('#uploader_chunking').addClass('hide');
							$('#uploader_urlstream_upload').addClass('hide');
						} else {
							$('#uploader_chunking').removeClass('hide');
							$('#uploader_urlstream_upload').removeClass('hide');
						}
					});
				});
			</script>
		</div>
		<div class="col-md-8" id="pluploadUploader">
			<p><?php _e("You browser doesn't have Flash or HTML5 support. Check also if page have no JavaScript errors.", 'gmLang'); ?></p>
			<script type="text/javascript">
				// Convert divs to queue widgets when the DOM is ready
				jQuery(function($){
					$("#pluploadUploader").plupload({
						<?php if('auto' != $gm_screen_options['uploader_runtime']){ ?>
						runtimes: '<?php echo $gm_screen_options['uploader_runtime']; ?>',
						<?php } ?>
						url: '<?php echo wp_nonce_url($gmCore->gmedia_url . '/admin/upload.php', 'grandMedia' ); ?>',
						<?php if(('true' == $gm_screen_options['uploader_urlstream_upload']) && ('html4' != $gm_screen_options['uploader_runtime'])){ ?>
						urlstream_upload: true,
						multipart: false,
						<?php } else{ ?>
						multipart: true,
						<?php } ?>
						multipart_params: { params: ''},
						<?php if('true' == $gm_screen_options['uploader_chunking'] && ('html4' != $gm_screen_options['uploader_runtime'])){ ?>
						max_file_size: '2000Mb',
						chunk_size: 200000<?php //echo min($maxupsize, $gm_screen_options['uploader_chunk_size']*1024*1024); ?>,
						<?php } else{ ?>
						max_file_size: <?php echo $maxupsize; ?>,
						<?php } ?>
						max_retries: 2,
						unique_names: false,
						rename: true,
						sortable: true,
						dragdrop: true,
						views: {
							list: true,
							thumbs: true,
							active: 'thumbs'
						},
						filters: [
							{title: "All files", extensions: "*"}
						],
						flash_swf_url: '<?php echo $gmCore->gmedia_url; ?>/assets/plupload/Moxie.swf',
						silverlight_xap_url: '<?php echo $gmCore->gmedia_url; ?>/assets/plupload/Moxie.xap'

					});
					var closebtn = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
					var uploader = $("#pluploadUploader").plupload('getUploader');
					uploader.bind('StateChanged', function(up){
						if(up.state == plupload.STARTED){
							up.settings.multipart_params = { params: jQuery('#uploader_multipart_params :input').serialize() };
						}
						console.log('[StateChanged]', up.state, up.settings.multipart_params);
					});
					uploader.bind('ChunkUploaded', function(up, file, info){
						console.log('[ChunkUploaded] File:', file, "Info:", info);
						var response = jQuery.parseJSON(info.response);
						if(response && response.error){
							up.stop();
							file.status = plupload.FAILED;
							jQuery('<div/>').addClass('alert alert-danger alert-dismissable').html(closebtn + '<strong>' + response.id + ':</strong> ' + response.error.message).appendTo('#gmedia-msg-panel');
							console.log(response.error);
							up.trigger('QueueChanged StateChanged');
							up.trigger('UploadProgress', file);
							up.start();
						}
					});
					uploader.bind('FileUploaded', function(up, file, info){
						console.log('[FileUploaded] File:', file, "Info:", info);
						var response = jQuery.parseJSON(info.response);
						if(response && response.error){
							file.status = plupload.FAILED;
							jQuery('<div/>').addClass('alert alert-danger alert-dismissable').html(closebtn + '<strong>' + response.id + ':</strong> ' + response.error.message).appendTo('#gmedia-msg-panel');
							console.log(response.error);
						}
					});
					uploader.bind('UploadProgress', function(up, file){
						var percent = uploader.total.percent;
						$('#total-progress-info .progress-bar').css('width', percent + "%").attr('aria-valuenow', percent);
					});
					uploader.bind('Error', function(up, args){
						console.log('[Error] ', args);
						jQuery('<div/>').addClass('alert alert-danger alert-dismissable').html(closebtn + '<strong>' + args.file.name + ':</strong> ' + args.message + ' ' + args.status).appendTo('#gmedia-msg-panel');
					});
					uploader.bind('UploadComplete', function(up, files){
						console.log('[UploadComplete]', files);
						$('#total-progress-info .progress-bar').css('width', '0').attr('aria-valuenow', '0');
					});

				});
			</script>
		</div>
	</form>
<?php
}


function gmedia_import(){
	global $wpdb, $gmCore, $gmGallery, $gmDB;
	$gMediaURL = plugins_url(GMEDIA_FOLDER);
	$url = $gmCore->get_admin_url();
	?>
	<form class="row" id="import_form" name="import_form" target="import_window" action="<?php echo $gmCore->gmedia_url; ?>/admin/import.php" method="POST" accept-charset="utf-8" style="padding:20px 0 10px;">
		<div class="col-md-4">
			<fieldset id="import_params" class="import-params">
				<?php wp_nonce_field('GmediaImport'); ?>
				<input type="hidden" id="import-action" name="import" value=""/>
				<div class="form-group">
					<?php
					$term_type = 'gmedia_category';
					$gm_terms = $gmGallery->options['taxonomies'][$term_type];

					$terms_category = '';
					if(count($gm_terms)){
						foreach($gm_terms as $term_name => $term_title){
							$terms_category .= '<option value="' . $term_name . '">' . $term_title . '</option>' . "\n";
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
							$terms_album .= '<option value="' . $term->name . '">' . $term->name . '</option>' . "\n";
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
					});
				</script>
			</fieldset>
		</div>

		<div class="col-md-8 tabable">
			<ul class="nav nav-tabs" style="padding:0 10px;">
				<li class="active"><a href="#import_folder" data-toggle="tab"><?php _e('Import Server Folder', 'gmLang'); ?></a></li>
				<?php if($import['flagallery'] = $wpdb->get_var("show tables like '{$wpdb->prefix}flag_gallery'")){ ?>
					<li><a href="#import_flagallery" data-toggle="tab"><?php _e('FlAGallery plugin', 'gmLang'); ?></a></li>
				<?php
				}
				if($import['nextgen'] = $wpdb->get_var("show tables like '{$wpdb->prefix}ngg_gallery'")){
					?>
					<li><a href="#import_nextgen" data-toggle="tab"><?php _e('NextGen plugin', 'gmLang'); ?></a></li>
				<?php } ?>
			</ul>
			<div class="tab-content">
				<fieldset id="import_folder" class="tab-pane active">
					<?php echo "<style type='text/css'>@import url('{$gMediaURL}/assets/jqueryFileTree/jqueryFileTree.css');</style>\n"; ?>
					<?php echo "<script type='text/javascript' src='{$gMediaURL}/assets/jqueryFileTree/jqueryFileTree.js'></script>\n"; ?>
					<input type="hidden" id="folderpath" name="path" value="/"/>

					<div class="tab-inside">
						<h5><?php _e('Sever folders') ?>:</h5>
						<div id="file_browser"></div>
					</div>
					<div class="tab-footer">
						<div class="checkbox pull-left"><label><input type="checkbox" name="delete_source" value="1"/> <?php _e('delete source files after importing') ?></label></div>
						<button class="pull-right btn btn-info gmedia-import" type="button" name="import-folder"><?php _e('Import folder', 'gmLang'); ?></button>
					</div>
					<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function(){
							jQuery("#file_browser").fileTree({
								script: ajaxurl + "?action=gmedia_ftp_browser&_ajax_nonce=<?php echo wp_create_nonce( 'grandMedia' ) ;?>",
								root: '/',
								loadMessage: "<?php _e('loading...', 'gmLang'); ?>"
							}, function(path){
								jQuery("#folderpath").val(path);
							});
						});
						/* ]]> */
					</script>
				</fieldset>

				<?php if(!empty($import['flagallery'])){ ?>
					<fieldset id="import_flagallery" class="tab-pane">
						<?php
						$import['flagallery'] = $wpdb->get_results("SELECT gid, title, galdesc FROM `{$wpdb->prefix}flag_gallery`");
						if(!empty($import['flagallery'])){
							?>
						<div class="tab-inside">
							<p><?php _e('If Album is not specified, then gallery name will be used as Album') ?></p>
							<h5><?php _e('Flagallery Galleries') ?>: <small>(<a href="#toggle-flaggalery" class="gm-toggle-cb"><?php _e('Toggle checkboxes', 'gmLang') ?></a>)</small></h5>
							<div id="toggle-flaggalery">
								<?php foreach($import['flagallery'] as $gallery){ ?>
									<div class="checkbox">
										<label><input type="checkbox" name="gallery[]" value="<?php echo $gallery->gid ?>"/> <span><?php echo $gallery->title; ?></span></label>
										<?php /* if(!empty($gallery->galdesc)){
											echo '<div class="help-block"> ' . stripslashes($gallery->galdesc) . '</div>';
										} */ ?>
									</div>
								<?php } ?>
							</div>
						</div>
						<div class="tab-footer">
							<button class="pull-right btn btn-info gmedia-import" type="button" name="import-flagallery"><?php _e('Import', 'gmLang'); ?></button>
						</div>
						<?php } else{ ?>
							<p class="tab-inside"><?php _e('There are no created galleries in this plugin.', 'gmLang') ?></p>
						<?php } ?>
					</fieldset>
				<?php } ?>

				<?php if(!empty($import['nextgen'])){ ?>
					<fieldset id="import_nextgen" class="tab-pane">
						<?php
						$import['nextgen'] = $wpdb->get_results("SELECT gid, title, galdesc FROM `{$wpdb->prefix}ngg_gallery`");
						if(!empty($import['nextgen'])){
							?>
						<div class="tab-inside">
							<p><?php _e('If Album is not specified, then gallery name will be used as Album') ?></p>
							<h5><?php _e('Flagallery Galleries') ?>: <small>(<a href="#toggle-nextgen" class="gm-toggle-cb"><?php _e('Toggle checkboxes', 'gmLang') ?></a>)</small></h5>
							<div id="toggle-nextgen">
								<?php foreach($import['nextgen'] as $gallery){ ?>
									<div class="checkbox">
										<label><input type="checkbox" name="gallery[]" value="<?php echo $gallery->gid ?>"/> <span><?php echo $gallery->title; ?></span></label>
										<?php /* if(!empty($gallery->galdesc)){
											echo '<div class="help-block"> ' . stripslashes($gallery->galdesc) . '</div>';
										} */ ?>
									</div>
								<?php } ?>
							</div>
						</div>
						<div class="tab-footer">
							<button class="pull-right btn btn-info gmedia-import" type="button" name="import-nextgen"><?php _e('Import', 'gmLang'); ?></button>
						</div>
						<?php } else{ ?>
							<p class="tab-inside"><?php _e('There are no created galleries in this plugin.', 'gmLang') ?></p>
						<?php } ?>
					</fieldset>
				<?php } ?>
			</div>
			<div class="clear"></div>
		</div>
	</form>
	<script type="text/javascript">
		function gmedia_import_done(){
			if(jQuery('#importModal').is(':visible')){
				jQuery('#import-done').button('complete').prop('disabled', false);
			}
		}
		jQuery(function($){
		});
	</script>

	<div class="modal fade gmedia-modal" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<form class="modal-content" autocomplete="off" method="post">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php _e('Import'); ?></h4>
				</div>
				<div class="modal-body">
					<iframe name="import_window" id="import_window" src="about:blank" width="100%" height="300" onload="gmedia_import_done()"></iframe>
				</div>
				<div class="modal-footer">
					<button type="button" id="import-done" class="btn btn-primary" data-dismiss="modal" data-complete-text="<?php _e( 'Close', 'gmLang' ); ?>" disabled="disabled"><?php _e( 'Working...', 'gmLang' ); ?></button>
				</div>
			</form><!-- /.modal-content -->

		</div>
	</div>
<?php
}
