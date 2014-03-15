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

	$gm['folder']['image'] = 'image';
	$gm['folder']['image_thumb'] = 'image/thumb';
	$gm['folder']['image_original'] = 'image/original';
	$gm['folder']['audio'] = 'audio';
	$gm['folder']['video'] = 'video';
	$gm['folder']['text'] = 'text';
	$gm['folder']['application'] = 'application';
	$gm['folder']['module'] = 'module';

	$gm['thumb'] = array('width' => 300, 'height' => 300, 'quality' => 90);
	$gm['image'] = array('width' => 1600, 'height' => 1600, 'quality' => 85);

	$gm['gmedia_key'] = '';

	$gm['taxonomies']['gmedia_tag'] = array();
	$gm['taxonomies']['gmedia_album'] = array();
	$gm['taxonomies']['gmedia_module'] = array();
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

	return $gm;

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
			`ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`author` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			`date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			`description` LONGTEXT NOT NULL,
			`title` TEXT NOT NULL,
			`gmuid` VARCHAR(255) NOT NULL DEFAULT '',
			`modified` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			`mime_type` VARCHAR(100) NOT NULL DEFAULT '',
			PRIMARY KEY (`ID`),
			KEY `gmuid` (`gmuid`),
			KEY `type_date` (`mime_type`,`date`,`ID`),
			KEY `author` (`author`)
		) {$charset_collate}";
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '$gmedia_meta'") != $gmedia_meta){
		$sql = "CREATE TABLE {$gmedia_meta} (
			`meta_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`gmedia_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			`meta_key` VARCHAR(255) DEFAULT NULL,
			`meta_value` LONGTEXT,
			PRIMARY KEY (`meta_id`),
			KEY `gmedia_id` (`gmedia_id`),
			KEY `meta_key` (`meta_key`)
		) {$charset_collate}";
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '$gmedia_term'") != $gmedia_term){
		$sql = "CREATE TABLE {$gmedia_term} (
			`term_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(200) NOT NULL DEFAULT '',
			`taxonomy` VARCHAR(32) NOT NULL DEFAULT '',
			`description` LONGTEXT NOT NULL,
			`global` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			`count` BIGINT(20) NOT NULL DEFAULT '0',
			PRIMARY KEY (`term_id`),
			KEY `taxonomy` (`taxonomy`),
			KEY `name` (`name`)
		) {$charset_collate}";
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '$gmedia_term_meta'") != $gmedia_term_meta){
		$sql = "CREATE TABLE {$gmedia_term_meta} (
			`meta_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			`gmedia_term_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			`meta_key` VARCHAR(255) DEFAULT NULL,
			`meta_value` LONGTEXT,
			PRIMARY KEY (`meta_id`),
			KEY `gmedia_term_id` (`gmedia_term_id`),
			KEY `meta_key` (`meta_key`)
		) {$charset_collate}";
		dbDelta($sql);
	}

	if($wpdb->get_var("show tables like '$gmedia_term_relationships'") != $gmedia_term_relationships){
		$sql = "CREATE TABLE {$gmedia_term_relationships} (
			`gmedia_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			`gmedia_term_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
			`term_order` INT(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`gmedia_id`,`gmedia_term_id`),
			KEY `gmedia_term_id` (`gmedia_term_id`)
		) {$charset_collate}";
		dbDelta($sql);
	}

	// check one table again, to be sure
	if($wpdb->get_var("show tables like '$gmedia'") != $gmedia){
		update_option("gmediaInitCheck", __('GRAND Media: Tables could not created, please check your database settings', 'gmLang'));

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
		$gmGallery->options = array_merge_recursive($gmGallery->options, $new_options);
		update_option('gmediaOptions', $gmGallery->options);
	}

	// if all is passed, save the DBVERSION
	add_option("gmediaDbVersion", GMEDIA_DBVERSION);
	add_option("gmediaVersion", GMEDIA_VERSION);

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

/**
 * Uninstall all settings and tables
 * Called via Setup and register_unstall hook
 *
 * @access internal
 * @return void
 */
function gmedia_uninstall(){
	//if uninstall not called from WordPress exit
	//if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	//exit ();

	//if(get_option('gmediaVersion'))
	//return;

	/** @var $wpdb wpdb */
	global $wpdb;

	// first remove all tables
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->gmedia}");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->gmedia_meta}");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->gmedia_term}");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->gmedia_term_meta}");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->gmedia_term_relationships}");

	// then remove all options
	delete_option('gmediaOptions');
	delete_option('gmediaDbVersion');
	delete_option('gmediaVersion');
	delete_option('gmediaTemp');
	delete_metadata('user', 0, 'gm_screen_options', '', true);

}
