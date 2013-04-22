<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * grandMedia()
 *
 * @return mixed content
 */
function grandMedia_AddMedia() {
	global $grandCore, $gMDb;
	$gMediaURL = plugins_url( GRAND_FOLDER );
	$url       = $grandCore->getAdminURL();
	//$gMediasID = isset($_COOKIE['gmedia_add_selected_items'])? $_COOKIE['gmedia_add_selected_items'] : '';
	// link for the flash file
	$swfUploadLink = $gMediaURL . '/admin/upload.php';
	$swfUploadLink = wp_nonce_url( $swfUploadLink, 'grandMedia' );
	//flash doesn't seem to like encoded ampersands, so convert them back here
	$swfUploadLink = str_replace( '&#038;', '&', $swfUploadLink );
	$maxupsize     = wp_max_upload_size();
	$maxupsize     = floor( $maxupsize * 0.99 / 1024 / 1024 );
	?>
	<div class="gMediaLibActions">
		<?php // TODO choose runtime from page options ?>
		<div class="abuts">
			<a class="upload active" href="<?php echo $url['page']; ?>"><?php _e( 'Upload Files', 'gmLang' ); ?></a>
			<span class="unzip disabled"><?php _e( 'Upload ZIP', 'gmLang' ); ?></span>
			<span class="import disabled"><?php _e( 'Import Folder', 'gmLang' ); ?></span>
		</div>
		<div class="msg0"><?php _e( 'Add files to the upload queue and click the start button.', 'gmLang' ); ?></div>
	</div>
	<div class="gmAddMedia">
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
							$gmedia_cats   = $gMDb->gmGetTerms( $type, array( 'hide_empty' => false ) );
							$opt = '';
							if ( count( $gmedia_cats ) ) {
								$children     = $gMDb->_gm_get_term_hierarchy( $type );
								$termsHierarr = $grandCore->gmGetTermsHierarr( $type, $gmedia_cats, $children, $count = 0 );
								foreach ( $termsHierarr as $termitem ) {
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
					<?php $gmedia_tags   = $gMDb->gmGetTerms( 'gmedia_tag', array( 'fields' => 'names' ) );
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
		<?php wp_original_referer_field( true, 'previous' ); ?>
	</div>
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
				//chunk_size				: '<?php echo $maxupsize; ?>Mb',
				chunk_size      		: '10Mb',
				unique_names    		: false,
				rename          		: true,
				//urlstream_upload	: true,

				// Resize images on clientside if we can
				//resize 						: {width : 150, height : 150, quality : 90},

				// Specify what files to browse for
				filters         		: [{title: "All files", extensions: "*"}],

				// Flash settings
				flash_swf_url   		: '<?php echo $gMediaURL; ?>/admin/js/plupload/plupload.flash.swf',
			});
			var uploader = jQuery('#pluploadUploader').pluploadQueue();
			uploader.bind('Error', function (up, err) {
				console.log('Error', err);
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
			});

		});
	</script>
<?php
}
