<?php
// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
require_once(dirname(__FILE__).'/inc/core.php');

if (function_exists('is_multisite') && is_multisite()) {
	global $wpdb;
	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
	if ($blogs) {
		foreach($blogs as $blog) {
			switch_to_blog($blog['blog_id']);
			gmedia_uninstall();
			restore_current_blog();
		}
	}
}
else
{
	gmedia_uninstall();
}

/**
 * Uninstall all settings and tables
 * Called via Setup and register_unstall hook
 *
 * @access internal
 * @return void
 */
function gmedia_uninstall(){
	/** @var $wpdb wpdb */
	global $wpdb, $gmCore;

	// first remove all tables
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}gmedia");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}gmedia_meta");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}gmedia_term");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}gmedia_term_meta");
	$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}gmedia_term_relationships");

	$options = get_option('gmediaOptions');
	// then remove all options
	delete_option('gmediaOptions');
	delete_option('gmediaDbVersion');
	delete_option('gmediaVersion');
	delete_option('gmediaTemp');
	delete_metadata('user', 0, 'gm_screen_options', '', true);

	if($options['uninstall_dropfiles']){
		$files_folder = $gmCore->upload['path'];
		$delete_files = $gmCore->delete_folder($files_folder);
	} else{
		$files_folder = $gmCore->upload['path'].'/'.$options['folder']['module'];
		$delete_files = $gmCore->delete_folder($files_folder);
	}
}
