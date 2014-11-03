<?php
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	die('You are not allowed to call this page directly.');
}

/**
 * Setup the default option array for the plugin
 *
 * @access internal
 * @return array
 */
function gmedia_default_options(){

	$gm['uninstall_dropdata'] = 'all'; // can be 'all', 'none', 'db'

	$gm['shortcode_raw'] = '0';
	$gm['endpoint'] = 'gmedia';

	$gm['folder']['image'] = 'image';
	$gm['folder']['image_thumb'] = 'image/thumb';
	$gm['folder']['image_original'] = 'image/original';
	$gm['folder']['audio'] = 'audio';
	$gm['folder']['video'] = 'video';
	$gm['folder']['text'] = 'text';
	$gm['folder']['application'] = 'application';
	$gm['folder']['module'] = 'module';

	$gm['thumb'] = array('width' => 300, 'height' => 300, 'quality' => 70, 'crop' => 0);
	$gm['image'] = array('width' => 1600, 'height' => 1600, 'quality' => 85, 'crop' => 0);

	$gm['modules_xml'] = 'https://dl.dropboxusercontent.com/u/6295502/gmedia_modules/modules_v1.xml';
	$gm['license_name'] = '';
	$gm['license_key'] = '';
	$gm['license_key2'] = '';

	$gm['taxonomies']['gmedia_category'] = array(
		'abstract' => __('Abstract', 'gmLang'),
		'animals' => __('Animals', 'gmLang'),
		'black-and-white' => __('Black and White', 'gmLang'),
		'celebrities' => __('Celebrities', 'gmLang'),
		'city-and-architecture' => __('City & Architecture', 'gmLang'),
		'commercial' => __('Commercial', 'gmLang'),
		'concert' => __('Concert', 'gmLang'),
		'family' => __('Family', 'gmLang'),
		'fashion' => __('Fashion', 'gmLang'),
		'film' => __('Film', 'gmLang'),
		'fine-art' => __('Fine Art', 'gmLang'),
		'food' => __('Food', 'gmLang'),
		'journalism' => __('Journalism', 'gmLang'),
		'landscapes' => __('Landscapes', 'gmLang'),
		'macro' => __('Macro', 'gmLang'),
		'nature' => __('Nature', 'gmLang'),
		'nude' => __('Nude', 'gmLang'),
		'people' => __('People', 'gmLang'),
		'performing-arts' => __('Performing Arts', 'gmLang'),
		'sport' => __('Sport', 'gmLang'),
		'still-life' => __('Still Life', 'gmLang'),
		'street' => __('Street', 'gmLang'),
		'transportation' => __('Transportation', 'gmLang'),
		'travel' => __('Travel', 'gmLang'),
		'underwater' => __('Underwater', 'gmLang'),
		'urban-exploration' => __('Urban Exploration', 'gmLang'),
		'wedding' => __('Wedding', 'gmLang')
	);
	$gm['taxonomies']['gmedia_tag'] = array();
	$gm['taxonomies']['gmedia_album'] = array();

	$gm['taxonomies']['gmedia_filter'] = array(); // not linked with gmedia_term_relationships table
	$gm['taxonomies']['gmedia_gallery'] = array(); // not linked with gmedia_term_relationships table
	$gm['taxonomies']['gmedia_module'] = array(); // not linked with gmedia_term_relationships table

	$gm['gm_screen_options']['per_page_gmedia'] = 30;
	$gm['gm_screen_options']['orderby_gmedia'] = 'ID';
	$gm['gm_screen_options']['sortorder_gmedia'] = 'DESC';

	$gm['gm_screen_options']['per_page_wpmedia'] = 30;
	$gm['gm_screen_options']['orderby_wpmedia'] = 'ID';
	$gm['gm_screen_options']['sortorder_wpmedia'] = 'DESC';

	$gm['gm_screen_options']['uploader_runtime'] = 'auto';
	$gm['gm_screen_options']['uploader_chunking'] = 'true';
	$gm['gm_screen_options']['uploader_chunk_size'] = 8; // in Mb
	$gm['gm_screen_options']['uploader_urlstream_upload'] = 'false';

	$gm['gm_screen_options']['library_edit_quicktags'] = 'false';

	return $gm;

}

/**
 * sets gmedia capabilities to administrator role
 **/
function gmedia_capabilities(){
	// Set the capabilities for the administrator
	$role = get_role('administrator');
	// We need this role, no other chance
	if(empty($role)){
		update_option("gmediaInitCheck", __('Sorry, Gmedia Gallery works only with a role called administrator', 'gmLang'));

		return;
	}
	$capabilities = gmedia_plugin_capabilities();
	$capabilities = apply_filters('gmedia_capabilities', $capabilities);
	foreach($capabilities as $cap){
		$role->add_cap($cap);
	}
}

/**
 * creates all tables for the plugin
 * called during register_activation hook
 *
 * @access internal
 * @return void
 **/
function gmedia_install(){
	/** @var $wpdb wpdb */
	global $wpdb, $gmGallery, $gmCore;

	// Check for capability
	if(!current_user_can('activate_plugins')){
		return;
	}

	gmedia_capabilities();

	// upgrade function changed in WordPress 2.3	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

	// add charset & collate like wp core
	$charset_collate = '';

	if($wpdb->has_cap('collation')){
		if(!empty($wpdb->charset)){
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if(!empty($wpdb->collate)){
			$charset_collate .= " COLLATE $wpdb->collate";
		}
	}

	$gmedia = $wpdb->prefix . 'gmedia';
	$gmedia_meta = $wpdb->prefix . 'gmedia_meta';
	$gmedia_term = $wpdb->prefix . 'gmedia_term';
	$gmedia_term_meta = $wpdb->prefix . 'gmedia_term_meta';
	$gmedia_term_relationships = $wpdb->prefix . 'gmedia_term_relationships';

	if($wpdb->get_var("show tables like '$gmedia'") != $gmedia){
		$sql = "CREATE TABLE {$gmedia} (
			ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			author BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			description LONGTEXT NOT NULL,
			title TEXT NOT NULL,
			gmuid VARCHAR(255) NOT NULL DEFAULT '',
			link VARCHAR(255) NOT NULL DEFAULT '',
			modified DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			mime_type VARCHAR(100) NOT NULL DEFAULT '',
			status VARCHAR(20) NOT NULL DEFAULT 'public',
			PRIMARY KEY  (ID),
			KEY gmuid (gmuid),
			KEY type_status_date (mime_type,status,date,ID),
			KEY author (author)
		) {$charset_collate}";
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '$gmedia_meta'") != $gmedia_meta){
		$sql = "CREATE TABLE {$gmedia_meta} (
			meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			gmedia_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			meta_key VARCHAR(255) DEFAULT NULL,
			meta_value LONGTEXT,
			PRIMARY KEY  (meta_id),
			KEY gmedia_id (gmedia_id),
			KEY meta_key (meta_key)
		) {$charset_collate}";
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '$gmedia_term'") != $gmedia_term){
		$sql = "CREATE TABLE {$gmedia_term} (
			term_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(200) NOT NULL DEFAULT '',
			taxonomy VARCHAR(32) NOT NULL DEFAULT '',
			description LONGTEXT NOT NULL,
			global BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			count BIGINT(20) NOT NULL DEFAULT '0',
			status VARCHAR(20) NOT NULL DEFAULT 'public',
			PRIMARY KEY  (term_id),
			KEY taxonomy (taxonomy),
			KEY name (name)
		) {$charset_collate}";
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '$gmedia_term_meta'") != $gmedia_term_meta){
		$sql = "CREATE TABLE {$gmedia_term_meta} (
			meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			gmedia_term_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			meta_key VARCHAR(255) DEFAULT NULL,
			meta_value LONGTEXT,
			PRIMARY KEY  (meta_id),
			KEY gmedia_term_id (gmedia_term_id),
			KEY meta_key (meta_key)
		) {$charset_collate}";
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '$gmedia_term_relationships'") != $gmedia_term_relationships){
		$sql = "CREATE TABLE {$gmedia_term_relationships} (
			gmedia_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			gmedia_term_id BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			term_order INT(11) NOT NULL DEFAULT '0',
			gmedia_order INT(11) NOT NULL DEFAULT '0',
			PRIMARY KEY  (gmedia_id,gmedia_term_id),
			KEY gmedia_term_id (gmedia_term_id)
		) {$charset_collate}";
		dbDelta($sql);
	}

	// check one table again, to be sure
	if($wpdb->get_var("show tables like '$gmedia'") != $gmedia){
		update_option("gmediaInitCheck", __('GmediaGallery: Tables could not created, please check your database settings', 'gmLang'));

		return;
	}

	// Set installation date
	if(empty($gmGallery->options['installDate'])){
		$gmGallery->options['installDate'] = time();
	}

	// set the default settings, if we didn't upgrade
	if(empty($gmGallery->options)){
		$gmGallery->options = gmedia_default_options();
		update_option('gmediaOptions', $gmGallery->options);
	} else{
		$default_options = gmedia_default_options();
		unset($gmGallery->options['folder'], $gmGallery->options['taxonomies']);
		$new_options = $gmCore->array_diff_key_recursive($default_options, $gmGallery->options);
		$gmGallery->options = $gmCore->array_replace_recursive($gmGallery->options, $new_options);
		update_option('gmediaOptions', $gmGallery->options);
	}

	// try to make gallery dirs if not exists
	foreach($gmGallery->options['folder'] as $folder){
		wp_mkdir_p($gmCore->upload['path'] . '/' . $folder);
	}
}

/**
 * Called via Setup and register_deactivate hook
 *
 * @access internal
 * @return void
 */
function gmedia_deactivate(){
	// remove & reset the init check option
	delete_option('gmediaInitCheck');
}

