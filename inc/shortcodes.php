<?php
/** *********************** **/
/** Shortcodes Declarations **/
/** *********************** **/
add_shortcode('gmedia', 'gmedia_shortcode');
//add_filter('the_content', 'do_shortcode');


/** ******************************* **/
/** Shortcodes Functions and Markup **/
/** ******************************* **/
$gmedia_shortcode_instance = array();
function gmedia_shortcode($atts, $content = ''){
	global $gmDB, $gmGallery, $gmCore;
	global $gmedia_shortcode_instance;
	/** @var $id */
	extract(shortcode_atts(array(
		'id' => 0,
		'preview' => ''
	), $atts));
	$id = intval($id);
	if(!$id){
		return $content;
	}

	$gallery = array(
		'term_id' => 0,
		'name' => '',
		'description' => '',
		'status' => 'public',
		'edited' => '&#8212;',
		'module' => '',
		'query' => array(),
		'settings' => array()
	);

	$taxonomy = 'gmedia_gallery';
	$gallery = $gmDB->get_term($id, $taxonomy, ARRAY_A);
	if(is_wp_error($gallery)){
		return '<div class="gmedia_gallery gmediaShortcodeError">#' . $id . ': ' . $gallery->get_error_message() . '<br />' . $content . '</div>';
	} elseif(empty($gallery)){
		return '<div class="gmedia_gallery gmediaShortcodeError">#' . $id . ': ' . sprintf(__('No gallery with ID #%s in database'), $id) . '<br />' . $content . '</div>';
	} else{
		$gallery_meta = $gmDB->get_metadata('gmedia_term', $id);
		$gallery_meta = array_map('reset', $gallery_meta);
		$gallery_meta = array_map('maybe_unserialize', $gallery_meta);
		$gallery = array_merge($gallery, $gallery_meta);
	}

	if(!empty($preview) && $gallery['module'] != $preview){
		$gallery['module'] = $preview;
		$gallery['settings'][$gallery['module']] = array();
	} elseif(!isset($gallery['settings'][$gallery['module']])){
		$gallery['settings'][$gallery['module']] = array();
	}

	$module = $gmCore->get_module_path($gallery['module']);
	if(!$module){
		return '<div class="gmedia_gallery gmediaShortcodeError">#' . $id . ': ' . __('Gmedia Module folder missed.', 'gmLang') . '<br />' . $content . '</div>';
	}

	if(file_exists($module['path'] . '/index.php') && file_exists($module['path'] . '/settings.php')){
		$module_info = array('dependencies' => '');
		include($module['path'] . '/index.php');
		/** @var $default_options */
		include($module['path'] . '/settings.php');
		$module['info'] = $module_info;
		$module['options'] = $default_options;
	} else{
		return '<div class="gmedia_gallery gmediaShortcodeError">#' . $id . ': ' . sprintf(__('Module `%s` is broken. Choose another module for this gallery'), $gallery['module']) . '<br />' . $content . '</div>';
	}

	$settings = array_merge($module['options'], $gallery['settings'][$gallery['module']]);

	$terms = array();
	$gmedia = array();
	if(!empty($gallery['query'])){
		foreach ( $gallery['query'] as $tax => $term_ids ) {
			if(!empty($term_ids)){
				if('gmedia__in' == $tax){
					$term_id = (int) $gallery['term_id'];
					$terms[$term_id] = $gmDB->get_term($term_id, 'gmedia_gallery');
					$term_ids = implode(',', wp_parse_id_list($term_ids[0]));
					$gmedia[$term_id] = $gmDB->get_gmedias( array('gmedia__in' => $term_ids, 'orderby' => 'gmedia__in', 'order' => 'ASC') );
					continue;
				}
				foreach($term_ids as $term_id){
					$terms[$term_id] = $gmDB->get_term($term_id, $tax);
					if(!empty($terms[$term_id]) && !is_wp_error($terms[$term_id]) && $terms[$term_id]->count){
						if('gmedia_category' == $tax){
							$terms[$term_id]->name = $gmGallery->options['taxonomies']['gmedia_category'][$terms[$term_id]->name];
							$gmedia[$term_id] = $gmDB->get_gmedias( array('category__in' => $term_id) );
						} elseif('gmedia_album' == $tax){
							$term_meta = $gmDB->get_metadata('gmedia_term', $term_id);
							$term_meta = array_map('reset', $term_meta);
							$term_meta = array_merge( array('orderby' => 'ID', 'order' => 'DESC'), $term_meta);
							$args = array('album__in' => $term_id, 'orderby' => $term_meta['orderby'], 'order' => $term_meta['order']);
							$gmedia[$term_id] = $gmDB->get_gmedias($args);
						} elseif('gmedia_tag' == $tax){
							$gmedia[$term_id] = $gmDB->get_gmedias( array('tag__in' => $term_id) );
						}
					} else{
						unset($terms[$term_id]);
					}
				}
			} else{
				return '<div class="gmedia_gallery gmediaShortcodeError">#' . $id . ': ' . sprintf(__('Choose gallery source, please.'), $gallery['module']) . '<br />' . $content . '</div>';
			}
		}
	} else{
		return '<div class="gmedia_gallery gmediaShortcodeError">#' . $id . ': ' . sprintf(__('Choose gallery source, please.'), $gallery['module']) . '<br />' . $content . '</div>';
	}

	$gmGallery->do_module[$gallery['module']] = $module;

	$out = '<div class="gmedia_gallery ' . $gallery['module'] . '_module" id="GmediaGallery_' . $id . '" data-gallery="' . $id . '" data-module="' . $gallery['module'] . '">';
	$out .= $content;

	if(isset($settings['customCSS']) && ('' != trim($settings['customCSS']))){
		$out .= "
<style type='text/css' scoped='scoped'>
		/**** Begin Custom CSS {$gallery['module']} #{$id} ****/
		" . $settings['customCSS'] . "
		/**** End Custom CSS {$gallery['module']} #{$id} ****/
</style>";
	}

	$is_bot = $gmCore->is_bot();

	ob_start();
	include($module['path'].'/init.php');
	$out .= ob_get_contents();
	ob_end_clean();

	$out .= '</div>';

	if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		do_action('gmedia_footer_scripts');
	}

	return $out;

}

