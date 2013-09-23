<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * grandMedia_AddMedia()
 *
 * @return mixed content
 */
function grandMedia_AddMedia() {
	global $grandCore;
	$url = $grandCore->get_admin_url();
	$tab = $grandCore->_get('tab','upload');
	$extra_tools = (defined('GMEDIA_IFRAME_TOOL') && GMEDIA_IFRAME_TOOL)? false : true;
	?>
	<div class="gMediaLibActions">
		<?php if($extra_tools){ ?>
		<div class="abuts">
			<a class="upload<?php if($tab=='upload') echo ' active'; ?>" href="<?php echo $url['page']; ?>"><?php _e( 'Upload Files', 'gmLang' ); ?></a>
			<span class="unzip disabled"><?php _e( 'Upload ZIP', 'gmLang' ); ?></span>
			<a class="import<?php if($tab=='import') echo ' active'; ?>" href="<?php echo $url['page'].'&amp;tab=import'; ?>"><?php _e( 'Import', 'gmLang' ); ?></a>
		</div>
		<?php } else {
			echo '<div id="gm-message"></div>';
		} ?>
		<div class="msg0"><span class="msg0_text"><?php
			if($tab == 'upload')
				_e( 'Add files to the upload queue and click the start button', 'gmLang' );
			if($tab == 'import')
				_e( 'Grab files from other sources', 'gmLang' );
		?></span><span class="msg0_progress"></span></div>
	</div>
	<div class="gmAddMedia floatholdviz">
		<?php
		if($tab == 'upload')
			gmedia_upload_files();
		if($tab == 'import')
			gmedia_import();
		?>
		<?php wp_original_referer_field( true, 'previous' ); ?>
	</div>
	<?php
}


function gmedia_upload_files() {
	global $grandCore, $gMDb;
	$gMediaURL = plugins_url( GRAND_FOLDER );
	// link for the flash file
	$swfUploadLink = $gMediaURL . '/admin/upload.php';
	$swfUploadLink = wp_nonce_url( $swfUploadLink, 'grandMedia' );
	//flash doesn't seem to like encoded ampersands, so convert them back here
	$swfUploadLink = str_replace( '&#038;', '&', $swfUploadLink );
	$maxupsize     = wp_max_upload_size();
	$maxupsize     = floor( $maxupsize * 0.99 / 1024 / 1024 );
	// TODO choose runtime from page options
	?>
	<div class="optionsPanel">
		<form method="post" action="" id="gmTerms" onsubmit="return false;">
			<div class="info"><p><?php echo __( 'Maximum file size', 'gmLang' ) . ':' . $maxupsize . 'Mb'; ?></p></div>
			<div class="params" id="termsdiv-gmedia_category">
				<div id="gmedia_category" class="categorydiv">
					<label for="tax-input-gmedia_category"><?php _e( 'Assign Category', 'gmLang' ); ?></label>
					<select name="terms[gmedia_category]" id="tax-input-gmedia_category" class="the-category">
						<option value=""><?php _e( 'Uncategorized', 'gmLang' ); ?></option>
						<?php
						$type = 'gmedia_category';
						$gmedia_cats   = $gMDb->get_terms( $type, array( 'hide_empty' => false ) );
						$opt = '';
						if ( count( $gmedia_cats ) ) {
							$children     = $gMDb->_get_term_hierarchy( $type );
							$terms_hierarrhically = $grandCore->get_terms_hierarrhically( $type, $gmedia_cats, $children, $count = 0 );
							foreach ( $terms_hierarrhically as $termitem ) {
								$pad = str_repeat( '&#8212; ', max( 0, $termitem->level ) );
								$opt .= '<option value="' . $termitem->term_id . '">' . $pad . $termitem->name . '</option>' . "\n";
							}
							echo $opt;
						}
						?>
					</select>
				</div>
			</div>
			<div class="params" id="termsdiv-gmedia_tag">
				<div id="gmedia_tag" class="tagsdiv">
					<div class="jaxtag">
						<div class="nojs-tags hide-if-js">
							<label for="tax-input-gmedia_tag"><?php _e( 'Add tags', 'gmLang' ); ?></label>
							<textarea id="tax-input-gmedia_tag" class="the-tags" cols="20" rows="3" name="terms[gmedia_tag]"></textarea>
						</div>
						<div class="ajaxtag hide-if-no-js">
							<label for="new-tag-gmedia_tag"><?php _e( 'Add Tags', 'gmLang' ); ?></label>
							<input type="text" value="" autocomplete="off" size="16" class="newtag form-input-tip" id="new-tag-gmedia_tag">
							<input type="button" value="<?php _e( 'Add', 'gmLang' ); ?>" class="button tagadd">
						</div>
						<div class="howto"><?php _e( 'Separate tags with commas', 'gmLang' ); ?></div>
					</div>
					<div class="tagchecklist"></div>
				</div>
				<?php $gmedia_tags   = $gMDb->get_terms( 'gmedia_tag', array( 'fields' => 'names' ) );
				if ( count( $gmedia_tags ) ) { ?>
					<div class="hide-if-no-js">
						<a id="link-gmedia_tag" class="tagcloud-link gmToggle" href="#tagcloud-gmedia_tag"><?php _e( 'Choose from early created tags', 'gmLang' ); ?></a>

						<div class="the-tagcloud" id="tagcloud-gmedia_tag" style="display: none;">
							<?php foreach ( $gmedia_tags as $tag ) { ?>
								<span><?php echo $tag; ?></span>
							<?php } ?>
						</div>
					</div>
				<?php } ?>
			</div>
		</form>
	</div>
	<form method="post" action="" id="gmUpload">
		<div id="pluploadUploader"><p><?php _e( "You browser doesn't have Flash or HTML5 support. Check also if page have no JavaScript errors.", 'gmLang' ); ?></p></div>
	</form>
	<script type="text/javascript">
		// Convert divs to queue widgets when the DOM is ready
		jQuery(function () {
			jQuery("#pluploadUploader").pluploadQueue({
				// General settings
				runtimes        		: 'gears,html5,flash,html4',
				url             		: '<?php echo $swfUploadLink; ?>',
				multipart       		: true,
				multipart_params		: { postData: ''},
				//max_file_size			: '<?php echo $maxupsize; ?>Mb',
				max_file_size   		: '2000Mb',
				//chunk_size      		: '10Mb',
				chunk_size					: '<?php echo min(($maxupsize - 1), 8); ?>Mb',
				unique_names    		: false,
				rename          		: true,
				//urlstream_upload	: true,

				// Resize images on clientside if we can
				//resize 						: {width : 150, height : 150, quality : 90},

				// Specify what files to browse for
				filters         		: [{title: "All files", extensions: "*"}],

				// Flash settings
				flash_swf_url   		: '<?php echo $gMediaURL; ?>/assets/plupload/plupload.flash.swf',

				// PreInit events, bound before any internal events
				preinit : {
					Init: function(up, info) {
						//console.log('[Init]', 'Info:', info, 'Features:', up.features);
					},

					UploadFile: function(up, file) {
						//console.log('[UploadFile]', file);
						up.settings.multipart_params = { postData: jQuery('#gmTerms').serialize() }

						// You can override settings before the file is uploaded
						// up.settings.url = 'upload.php?id=' + file.id;
						// up.settings.multipart_params = {param1 : 'value1', param2 : 'value2'};
					}
				},

				// Post init events, bound after the internal events
				init : {
					Refresh: function(up) {
						// Called when upload shim is moved
						//console.log('[Refresh]');
					},

					StateChanged: function(up) {
						// Called when the state of the queue is changed
						//console.log('[StateChanged]', up.state == plupload.STARTED ? "STARTED" : "STOPPED");
					},

					QueueChanged: function(up) {
						// Called when the files in queue are changed by adding/removing files
						//console.log('[QueueChanged]');
					},

					UploadProgress: function(up, file) {
						// Called while a file is being uploaded
						//console.log('[UploadProgress]', 'File:', file, "Total:", up.total);
					},

					FileUploaded: function(up, file, info) {
						// Called when a file has finished uploading
						//console.log('[FileUploaded] File:', file, "Info:", info);
						var response = jQuery.parseJSON(info.response);
						if (response && response.error)
						{
							file.status = plupload.FAILED;
							jQuery('<div/>').addClass('gm-message gm-error').html('<span><u><em>'+response.id+':</em></u> '+response.error.message+'</span><i class="gm-close">X</i>').appendTo('#gm-message');
							console.log(response.error);
						}
					},

					ChunkUploaded: function(up, file, info) {
						// Called when a file chunk has finished uploading
						//console.log('[ChunkUploaded] File:', file, "Info:", info);
						var response = jQuery.parseJSON(info.response);
						if (response && response.error)
						{
							up.stop();
							file.status = plupload.FAILED;
							jQuery('<div/>').addClass('gm-message gm-error').html('<span><u><em>'+response.id+':</em></u> '+response.error.message+'</span><i class="gm-close">X</i>').appendTo('#gm-message');
							console.log(response.error);
							up.trigger('QueueChanged');             // Line A
							up.trigger('UploadProgress', file);     // Line B
							up.start();
						}
					},

					Error: function(up, args) {
						// Called when a error has occured
						jQuery('<div/>').addClass('gm-message gm-error').html('<span><u><em>'+args.file.name+':</em></u> '+args.message+' '+args.status+'</span><i class="gm-close">X</i>').appendTo('#gm-message');
						console.log('[error] ', args);
					},

					UploadComplete: function(up, file) {
						//console.log('[UploadComplete]');
						jQuery(".plupload_buttons").css("display", "inline");
						jQuery(".plupload_upload_status").css("display", "inline");
						jQuery(".plupload_start").addClass("plupload_disabled");
						jQuery("#grandMedia").one("mousedown", ".plupload_add", function () {
							up.splice();
							up.trigger('Refresh');
							//up.refresh();
						});
					}
				}
			});
			/*var uploader = jQuery('#pluploadUploader').pluploadQueue();
			 uploader.bind('Error', function (up, args) {
			 console.log('[error]', args);
			 });
			 uploader.bind('BeforeUpload', function (up, file) {
			 up.settings.multipart_params = { postData: jQuery('#gmTerms').serialize() }
			 });
			 uploader.bind('UploadComplete', function (up, file) {
			 if (up.total.uploaded == uploader.files.length) {
			 jQuery(".plupload_buttons").css("display", "inline");
			 jQuery(".plupload_upload_status").css("display", "inline");
			 jQuery(".plupload_start").addClass("plupload_disabled");
			 jQuery("#grandMedia").one("mousedown", ".plupload_add", function () {
			 uploader.splice();
			 uploader.refresh();
			 });
			 }
			 });*/

		});
	</script>
<?php
}


function gmedia_import() {
	global $grandCore, $wpdb;
	$gMediaURL = plugins_url( GRAND_FOLDER );
	$url = $grandCore->get_admin_url();
	$nonce = wp_create_nonce('grandMedia');
	?>
	<div class="gm-metabox-wrapper">
		<div class="ui-tabs">
			<ul class="ui-tabs-nav">
				<li><a href="#import_folder"><?php _e('Import Server Folder', 'gmLang'); ?></a></li>
				<?php if($import['flagallery'] = $wpdb->get_var("show tables like '{$wpdb->prefix}flag_gallery'")) { ?>
				<li><a href="#import_flagallery"><?php _e('FlAGallery plugin', 'gmLang'); ?></a></li>
				<?php }
				if($import['nextgen'] = $wpdb->get_var("show tables like '{$wpdb->prefix}ngg_gallery'")) { ?>
				<li><a href="#import_nextgen"><?php _e('NextGen plugin', 'gmLang'); ?></a></li>
				<?php } ?>
			</ul>
			<div class="metabox-holder">

				<div id="import_folder" class="postbox ui-tabs-panel">
					<style type="text/css">@import url('<?php echo $gMediaURL; ?>/assets/jqueryFileTree/jqueryFileTree.css');</style>
					<script type="text/javascript" src="<?php echo $gMediaURL; ?>/assets/jqueryFileTree/jqueryFileTree.js"></script>
					<script type="text/javascript">
						/* <![CDATA[ */
						jQuery(document).ready(function () {
							jQuery("#file_browser").fileTree({
								script: ajaxurl+"?action=gmedia_ftp_browser&_ajax_nonce=<?php echo wp_create_nonce( 'grandMedia' ) ;?>",
								root: '/',
								loadMessage: "<?php _e('loading...', 'gmLang'); ?>"
							}, function(path) {
								jQuery("#folderpath").val(path);
							});
						});
						/* ]]> */
					</script>
					<div class="inside">
						<form name="import_folder_form" id="import_folder_form" method="POST" accept-charset="utf-8" >
							<fieldset>
								<input type="hidden" id="folderpath" name="folderpath" value="/" />
								<div id="file_browser" class="file_browser"></div>
								<label class="alignleft"><input type="checkbox" name="delete_source" value="1" /> <?php _e('delete source files after importing') ?></label>
								<input class="alignright button-primary ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#import_folder_form" data-task="gm-import-folder" type="submit" value="<?php _e('Import folder', 'gmLang'); ?>"/>
							</fieldset>
						</form>

					</div>
				</div>

				<?php if(!empty($import['flagallery'])) { ?>
				<div id="import_flagallery" class="postbox ui-tabs-panel" style="display: none;">
					<div class="inside">
						<form name="import_flagallery_form" id="import_flagallery_form" method="POST" accept-charset="utf-8" >
							<fieldset>
								<?php
									$import['flagallery'] = $wpdb->get_results("SELECT gid, title, galdesc FROM `{$wpdb->prefix}flag_gallery`");
									if(!empty($import['flagallery'])) {
								?>
								<span class="gm_toggle_checklist"><?php _e('Toggle checkboxes', 'gmLang') ?></span>
								<div class="gm_checklist">
									<?php foreach($import['flagallery'] as $gallery) { ?>
										<div class="row"><label><input type="checkbox" name="gallery[]" value="<?php echo $gallery->gid ?>" /> <span><?php echo $gallery->title; ?></span></label><?php if(!empty($gallery->galdesc)) { echo '<div class="descr"> ' . stripslashes($gallery->galdesc) . '</div>'; } ?></div>
									<?php } ?>
								</div>
								<input class="alignright button-primary ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#import_flagallery_form" data-task="gm-import-flagallery" type="submit" value="<?php _e('Import', 'gmLang'); ?>"/>
								<?php } else { ?>
								<p><?php _e('There are no created galleries in this plugin.', 'gmLang') ?></p>
								<?php } ?>
							</fieldset>
						</form>

					</div>
				</div>
				<?php } ?>

				<?php if(!empty($import['nextgen'])) { ?>
				<div id="import_nextgen" class="postbox ui-tabs-panel" style="display: none;">
					<div class="inside">
						<form name="import_nextgen_form" id="import_nextgen_form" method="POST" accept-charset="utf-8" >
							<fieldset>
								<?php
									$import['nextgen'] = $wpdb->get_results("SELECT gid, title, galdesc FROM `{$wpdb->prefix}ngg_gallery`");
									if(!empty($import['nextgen'])) {
								?>
								<span class="gm_toggle_checklist"><?php _e('Toggle checkboxes', 'gmLang') ?></span>
								<div class="gm_checklist">
									<?php foreach($import['nextgen'] as $gallery) { ?>
										<div class="row"><label><input type="checkbox" name="gallery[]" value="<?php echo $gallery->gid ?>" /> <span><?php echo $gallery->title; ?></span></label><?php if(!empty($gallery->galdesc)) { echo '<div class="descr"> ' . stripslashes($gallery->galdesc) . '</div>'; } ?></div>
									<?php } ?>
								</div>
								<input class="alignright button-primary ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#import_nextgen_form" data-task="gm-import-nextgen" type="submit" value="<?php _e('Import', 'gmLang'); ?>"/>
								<?php } else { ?>
								<p><?php _e('There are no created galleries in this plugin.', 'gmLang') ?></p>
								<?php } ?>
							</fieldset>
						</form>

					</div>
				</div>
				<?php } ?>

			</div>
			<div class="clear"></div>
		</div>
	</div>

<?php
}
