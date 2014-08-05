<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	die('You are not allowed to call this page directly.');
}

/**
 * Adds the meta box to the post or page edit screen
 *
 * @param string $page the name of the current page
 * @param string $context the current context
 */
function gmedia_add_meta_box($page, $context){
	if(!current_user_can('gmedia_gallery_manage')){
		return;
	}
	// Plugins that use custom post types can use this filter to show the Gmedia UI in their post type.
	$gm_post_types = apply_filters('gmedia-post-types', array('post', 'page'));

	if(function_exists('add_meta_box') && !empty($gm_post_types) && in_array($page, $gm_post_types) && 'side' === $context){
		add_action('admin_enqueue_scripts', 'gmedia_meta_box_load_scripts', 20);
		add_filter('media_buttons_context', 'gmedia_media_buttons_context', 4);
		add_meta_box('gmedia-MetaBox', __('Gmedia Gallery MetaBox', 'gmLang'), 'gmedia_post_metabox', $page, 'side', 'low');
	}
}

add_action('do_meta_boxes', 'gmedia_add_meta_box', 20, 2);

/**
 * @param $hook
 */
function gmedia_meta_box_load_scripts($hook){
	if((in_array($hook, array(
				'post.php',
				'edit.php'
			)) && isset($_GET['post']) && isset($_GET['action']) && $_GET['action'] == 'edit') || $hook == 'post-new.php'
	){
		wp_enqueue_style('wp-jquery-ui-dialog');
		wp_enqueue_style('gmedia-meta-box', plugins_url(GMEDIA_FOLDER) . '/admin/css/meta-box.css', array('wp-jquery-ui-dialog'), '0.9.0');
		wp_enqueue_script('gmedia-meta-box', plugins_url(GMEDIA_FOLDER) . '/admin/js/meta-box.js', array(
			'jquery',
			'jquery-ui-dialog',
			'gmedia-global-backend'
		), '0.9.0', true);
	}
}

/**
 * @param $context
 *
 * @return string
 */
function gmedia_media_buttons_context($context){
	$button = '<a href="#" class="gmedia_button button hidden" onclick="gm_media_button(this); return false;"><span class="wp-media-buttons-icon"></span> ' . __('Gmedia', 'gmLang') . '</a>';

	return $context . $button;
}

/*
 * add_tinymce_plugin()
 * Load the TinyMCE plugin : tinymce_gmedia_plugin.js
 *
 * @param array $plugin_array
 *
 * @return array $plugin_array

function gmedia_tinymce_plugin( $plugin_array ) {

$plugin_array['gmedia'] = plugins_url( GMEDIA_FOLDER ) . '/admin/js/tinymce_gmedia_plugin.js';

return $plugin_array;
}
//add_filter( 'mce_external_plugins', 'gmedia_tinymce_plugin', 5 );
*/

function gmedia_post_metabox(){
	global $gmCore, $gmDB, $user_ID;
	$t = $gmCore->gmedia_url . '/admin/images/blank.gif';
	?>
	<div id="gmedia-wraper">
		<div id="gmedia-message">
			<span class="info-init text-info" style="display: none;"><?php _e('Initializing...', 'gmLang'); ?></span>
			<span class="info-textarea text-warning" style="display: none;"><?php _e('Choose text area first', 'gmLang'); ?></span>
		</div>
		<div id="gmedia-source">
			<div id="gmedia-galleries">
				<div class="title-bar">
					<span class="gmedia-galleries-title"><?php _e('Gmedia Galleries', 'gmLang'); ?></span><a title="<?php _e('Create Gallery', 'gmLang'); ?>" class="button button-primary button-small gm-add-button" target="_blank" href="<?php echo admin_url('admin.php?page=GrandMedia_Modules'); ?>"><?php _e('Create Gallery', 'gmLang'); ?></a>
				</div>
				<div id="gmedia-galleries-wrap">
					<ul id="gmedia-galleries-list">
						<?php
						$taxonomy = 'gmedia_gallery';
						if($gmCore->caps['gmedia_edit_others_media']){
							$args = array();
						} else{
							$args = array('global' => $user_ID);
						}

						$gmediaTerms = $gmDB->get_terms($taxonomy, $args);

						if(count($gmediaTerms)){
							foreach($gmediaTerms as $item){
								$module_folder = $gmDB->get_metadata('gmedia_term', $item->term_id, 'module', true);
								$module_dir = $gmCore->get_module_path($module_folder);
								if(!$module_dir){
									continue;
								}

								/** @var $module array */
								$module_info = array();
								include($module_dir['path'] . '/index.php');

								?>
								<li class="gmedia-gallery-li" id="gmGallery-<?php echo $item->term_id; ?>">
									<p class="gmedia-gallery-title">
										<span class="gmedia-gallery-preview"><img src="<?php echo $module_dir['url'] . '/screenshot.png'; ?>" alt=""/></span><span><?php echo $item->name; ?></span>
									</p>

									<p class="gmedia-gallery-source">
										<span class="gmedia-gallery-module"><?php echo __('module', 'gmLang') . ': ' . $module_info['title']; ?></span>
									</p>

									<div class="gmedia-insert">
										<div class="gmedia-remove-button">
											<img src="<?php echo $t; ?>" alt=""/><?php _e('click to remove shortcode', 'gmLang'); ?>
											<br/>
											<small>[gmedia id=<?php echo $item->term_id; ?>]</small>
										</div>
										<div class="gmedia-insert-button">
											<img src="<?php echo $t; ?>" alt=""/><?php _e('click to insert shortcode', 'gmLang'); ?>
											<br/>
											<small>[gmedia id=<?php echo $item->term_id; ?>]</small>
										</div>
									</div>
									<div class="gmedia-selector"></div>
									<a href="<?php echo admin_url("admin.php?page=GrandMedia_Galleries&amp;edit_gallery=" . $item->term_id); ?>"
										 title="Edit Gallery #<?php echo $item->term_id; ?> in New Window" target="_blank" class="gmedia-gallery-gear"><?php _e('edit', 'gmLang'); ?></a>
								</li>
							<?php
							}
						} else{
							echo '<li class="emptydb">' . __('No Galleries.', 'gmLang') . ' <a target="_blank" href="' . admin_url('admin.php?page=GrandMedia_Modules') . '">' . __('Create', 'gmLang') . '</a></li>';
						}
						?>
					</ul>
				</div>
			</div>
			<div id="gmedia-social">
				<p><a target="_blank" href="http://wordpress.org/extend/plugins/grand-media/"><?php _e('Rate Gmedia at Wordpress.org', 'gmLang'); ?></a></p>
			</div>
		</div>
	</div>
<?php
}