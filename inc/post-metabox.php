<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

global $grandCore, $gMDb, $post_ID;
$upload = $grandCore->gm_upload_dir();
$t = plugins_url( GRAND_FOLDER ) . '/admin/images/blank.gif';
$post_tags = wp_get_post_tags($post_ID, array('fields' => 'names'));
?>
<div id="gMedia-wraper" data-width="276">
	<?php /*
	<div style="" id="gMedia-head">
		<ul id="gMedia-tabs">
			<li class="gMedia-source"><a href="#">My Gmedia</a></li>
			<li class="gMedia-source"><a href="#">Global</a></li>
		</ul>
	</div>
  */ ?>
	<div id="gMedia-message">
		<span class="info-init" style="display: none;"><?php _e('Initializing...', 'gmLang'); ?></span>
		<span class="info-textarea" style="display: none;"><?php _e('Choose text area first', 'gmLang'); ?></span>
	</div>
	<div id="gMedia-source">
		<div class="pane">

			<div id="gMedia-images"><h2><span class="gMedia-images-title"><?php _e('Related Gmedia files', 'gmLang'); ?></span></h2>
				<div id="gMedia-control">
					<div id="gMedia-control-update"><span class="gMedia-update-text"><?php _e('Update', 'gmLang'); ?></span></div>
					<div id="gMedia-refine">
						<div id="gMedia-refine-box">
							<div id="gMedia-refine-field"><input type="text" value="" id="gMedia-refine-input" placeholder="<?php _e('Search in Gmedia') ?>" autocomplete="off" /></div>
						</div>
					</div>
				</div>
				<div id="gMedia-images-wrap">
					<ul id="gMedia-images-thumbnails">
						<?php
						$arg = array(
							'mime_type' 		=> 'image/*',
							'orderby'   		=> 'ID',
							'order'     		=> 'DESC',
							'per_page'  		=> 20,
							'page'      		=> 1,
							'tag_name__in'	=> $post_tags,
							'null_tags'			=> true
						);
						$gMediaLib = $gMDb->get_gmedias( $arg );

						$relempty = '';
						if( empty( $gMediaLib ) && count($post_tags) ) {
							$relempty = '<li class="emptydb">' . __( 'No items related by tags.', 'gmLang' ) . '</li>';

							$arg = array(
								'mime_type' 		=> 'image/*',
								'orderby'   		=> 'ID',
								'order'     		=> 'DESC',
								'per_page'  		=> 20,
								'page'      		=> 1
							);
							$gMediaLib = $gMDb->get_gmedias( $arg );
						}

						if ( count( $gMediaLib ) ) {
							echo $relempty;
							foreach ( $gMediaLib as $item ) {
								$src = $upload['url'] . 'image/' . $item->gmuid;
								?>
								<li class="gMedia-image-li" id="gM-img-<?php echo $item->ID; ?>">
									<a target="_blank" data-gmid="<?php echo $item->ID; ?>" class="gM-img" href="<?php echo $src; ?>"><?php echo $grandCore->gm_get_media_image( $item, 'thumb', array( 'width' => 50, 'height' => 50 ) ); ?></a>
									<div style="display: none;" class="gM-img-description"><?php echo trim(esc_html(strip_tags($item->description))); ?></div>
									<?php //<div class="gMedia-selector"></div> ?>
								</li>
							<?php
							}
						}
						else {
							echo '<li class="emptydb">' . __( 'Gmedia Library is empty.', 'gmLang' ) . '</li>';
						}
						?>
					</ul>
				</div>
			</div>

			<div id="gMedia-galleries"><h2><span class="gMedia-galleries-title"><?php _e('Gmedia Galleries', 'gmLang'); ?></span></h2>
				<div id="gMedia-galleries-wrap">
					<ul id="gMedia-galleries-list">
						<?php
						$taxonomy = 'gmedia_module';
						$gMediaTerms = $gMDb->get_terms( $taxonomy );

						if ( count( $gMediaTerms ) ) {
							foreach ( $gMediaTerms as $item ) {
								$module_folder = $gMDb->get_metadata( 'gmedia_term', $item->term_id, 'module_name', true );
								$module_dir    = $grandCore->get_module_path( $module_folder );
								if(!$module_dir)
									continue;

								/** @var $module array */
								include_once( $module_dir['path'] . '/details.php' );

								?>
								<li class="gMedia-gallery-li" id="gmModule-<?php echo $item->term_id; ?>">
									<p class="gMedia-gallery-title">
										<span class="gMedia-gallery-preview"><img src="<?php echo $module_dir['url'] . '/screenshot.png'; ?>" alt="" /></span><span><?php echo $item->name; ?></span>
									</p>

									<p class="gMedia-gallery-source">
										<span class="gMedia-gallery-module"><?php echo __( 'module', 'gmLang' ) . ': ' . $module['title']; ?></span>
									</p>

									<div class="gMedia-insert">
										<div class="gMedia-remove-button">
											<img src="<?php echo $t; ?>" alt="" /><?php _e( 'click to remove shortcode', 'gmLang' ); ?>
											<br /><small>[gmedia id=<?php echo $item->term_id; ?>]</small>
										</div>
										<div class="gMedia-insert-button">
											<img src="<?php echo $t; ?>" alt="" /><?php _e( 'click to insert shortcode', 'gmLang' ); ?>
											<br /><small>[gmedia id=<?php echo $item->term_id; ?>]</small>
										</div>
									</div>
									<div class="gMedia-selector"></div>
									<a href="<?php echo admin_url( "admin.php?page=GrandMedia_Modules&amp;module=" . $module_folder . "&amp;term_id=" . $item->term_id, 'grandMedia' ); ?>"
										title="Edit Gallery #<?php echo $item->term_id; ?> in New Window" target="_blank" class="gMedia-gallery-gear"><?php _e( 'edit', 'gmLang' ); ?></a>
								</li>
							<?php
							}
						}
						else {
							echo '<li class="emptydb">' . __( 'No Galleries.', 'gmLang' ) . ' <a target="_blank" href="' . admin_url( 'admin.php?page=GrandMedia_Modules&amp;tab=modules' ) . '">' . __( 'Create', 'gmLang' ) . '</a></li>';
						}
						?>
					</ul>
				</div>
			</div>
			<div id="gMedia-social">
				<p><a target="_blank" href="http://wordpress.org/extend/plugins/grand-media/"><?php _e( 'Rate Gmedia at Wordpress.org', 'gmLang' ); ?></a></p>
			</div>
		</div>
		<?php /*<div class="pane">Text here</div>*/ ?>
	</div>
</div>