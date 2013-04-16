<?php
global $grandCore, $gMDb;
$upload = $grandCore->gm_upload_dir();
$t = plugins_url( GRAND_FOLDER ) . '/admin/images/blank.gif';

?>
<div id="gMedia-wraper">
	<?php /* TODO search media by post tag
	<div style="" id="gMedia-head">
		<ul id="gMedia-tabs">
			<li class="gMedia-source"><a href="#">My Grand Media</a></li>
			<li class="gMedia-source"><a href="#">Global Share</a></li>
		</ul>
	</div>
	<div id="gMedia-control">
		<div id="gMedia-control-update"><span class="gMedia-update-text">Update</span></div>
		<div id="gMedia-refine">
			<div id="gMedia-refine-box">
				<div id="gMedia-refine-field"><input type="text" value="" id="gMedia-refine-input" name="refine"></div>
			</div>
		</div>
	</div>
  */ ?>
	<div style="display: none;" id="gMedia-message">Initializing...</div>
	<div id="gMedia-source">
		<div class="pane">
			<?php /*
			<div id="gMedia-images"><h2><span class="gMedia-images-title">Related gMedia</span></h2>

				<?php
				$arg = array(
					'mime_type' => 'image/*',
					'orderby'   => 'ID',
					'order'     => 'DESC',
					'per_page'  => 20,
					'page'      => 1,
				);
				$gMediaLib = $gMDb->gmGetMedias( $arg );
				?>
				<div id="gMedia-images-wrap">
					<ul id="gMedia-images-thumbnails">
						<?php
						if ( count( $gMediaLib ) ) {
							foreach ( $gMediaLib as $item ) {
								$src = $upload['url'] . 'image/' . $item->gmuid;
								?>
								<li class="gMedia-image-li" id="gM-img-<?php echo $item->ID; ?>">
									<a target="_blank" class="gM-img" href="<?php echo $src; ?>"><?php echo $grandCore->gmGetMediaImage( $item, 'thumb', array( 'width' => 50, 'height' => 50 ) ); ?></a>

									<div style="display: none;" class="gM-img-description"><?php echo $item->description; ?></div>
									<div class="gMedia-selector"></div>
								</li>
							<?php
							}
						}
						else {
							echo '<li class="emptybd">' . __( 'No items in Grand Media Library.', 'gmLang' ) . '</li>';
						}
						?>
					</ul>
				</div>
			</div>
 			*/ ?>
			<div id="gMedia-galleries"><h2><span class="gMedia-galleries-title">gMedia Galleries</span></h2>

				<div id="gMedia-galleries-wrap">
					<ul id="gMedia-galleries-list">
						<?php
						$taxonomy = 'gmedia_module';
						$gMediaTerms = $gMDb->gmGetTerms( $taxonomy );

						if ( count( $gMediaTerms ) ) {
							foreach ( $gMediaTerms as $item ) {
								$module_folder = $gMDb->gmGetMetaData( 'gmedia_term', $item->term_id, 'module_name', true );
								$module_dir    = $grandCore->gm_get_module_path( $module_folder );
								/** @var $module array */
								include_once( $module_dir['path'] . '/details.php' );

								?>
								<li class="gMedia-gallery-li" id="gmModule-<?php echo $item->term_id; ?>">
									<p class="gMedia-gallery-title">
										<span class="gMedia-gallery-preview"><img src="<?php echo $module_dir['url'] . '/screenshot.png'; ?>" alt="" /></span><span><?php echo $item->name; ?></span>
									</p>

									<p class="gMedia-gallery-source">
										<span class="gMedia-gallery-module"><?php echo __( 'module', 'gmLang' ) . ': ' . $module['title']; ?></span><a
												href="<?php echo admin_url( "admin.php?page=GrandMedia_Modules&amp;module=" . $module_folder . "&amp;term_id=" . $item->term_id, 'grandMedia' ); ?>"
												title="Open Edit Gallery in New Window" target="_blank" class="gMedia-gallery-gear"><?php _e( 'edit', 'gmLang' ); ?></a>
									</p>

									<div class="gMedia-selector"></div>
									<div class="gMedia-insert">
										<div class="gMedia-remove-button">
											<img src="<?php echo $t; ?>" alt="" /><?php _e( 'click to remove', 'gmLang' ); ?></div>
										<div class="gMedia-insert-button">
											<img src="<?php echo $t; ?>" alt="" /><?php _e( 'click to insert', 'gmLang' ); ?></div>
									</div>
								</li>
							<?php
							}
						}
						else {
							echo '<li class="emptybd">' . __( 'No Galleries.', 'gmLang' ) . ' <a target="_blank" href="' . admin_url( 'admin.php?page=GrandMedia_Modules&amp;tab=modules' ) . '">' . __( 'Create', 'gmLang' ) . '</a></li>';
						}
						?>
					</ul>
				</div>
			</div>
			<div id="gMedia-social">
				<p><a target="_blank" href="http://wordpress.org/extend/plugins/grand-media/"><?php _e( 'Rate gMedia at Wordpress.org', 'gmLang' ); ?></a></p>
			</div>
		</div>
		<?php /*<div class="pane">Text here</div>*/ ?>
	</div>
</div>