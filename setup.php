<?php
// Stop direct call
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Setup the default option array for the plugin
 *
 * @access internal
 * @return array
 */
function grand_default_options() {

	$grand['folder']['image']       = 'image'; // default path to the media files relative to wp-content dir
	$grand['folder']['audio']       = 'audio';
	$grand['folder']['video']       = 'video';
	$grand['folder']['application'] = 'application';
	$grand['folder']['link']        = 'link';
	$grand['folder']['module']      = 'module';
	$grand['thumbnail_size']        = '150x150';

	$grand['taxonomies']['gmedia_tag']      = array( 'hierarchical' => false );
	$grand['taxonomies']['gmedia_category'] = array( 'hierarchical' => true );
	$grand['taxonomies']['gmedia_module']   = array( 'hierarchical' => true );

	$grand['per_page_gmedia']       = 30;
	$grand['per_page_wpmedia']      = 30;

	return $grand;

}

/**
 * creates all tables for the plugin
 * called during register_activation hook
 *
 * @access internal
 * @return void
 **/
function grand_install() {
	/** @var $wpdb wpdb */
	global $wpdb;

	// Check for capability
	if ( ! current_user_can( 'activate_plugins' ) )
		return;

	// upgrade function changed in WordPress 2.3	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// add charset & collate like wp core
	$charset_collate = '';

	if ( $wpdb->supports_collation() ) {
		if ( ! empty( $wpdb->charset ) ) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty( $wpdb->collate ) ) $charset_collate .= " COLLATE $wpdb->collate";
	}

	$gmedia                    = $wpdb->prefix . 'gmedia';
	$gmedia_meta               = $wpdb->prefix . 'gmedia_meta';
	$gmedia_term               = $wpdb->prefix . 'gmedia_term';
	$gmedia_term_meta          = $wpdb->prefix . 'gmedia_term_meta';
	$gmedia_term_relationships = $wpdb->prefix . 'gmedia_term_relationships';

	if ( $wpdb->get_var( "show tables like '$gmedia'" ) != $gmedia ) {
		$sql = "CREATE TABLE {$gmedia} (
			`ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`author` bigint(20) unsigned NOT NULL DEFAULT '0',
			`date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`description` longtext NOT NULL,
			`title` text NOT NULL,
			`gmuid` varchar(255) NOT NULL DEFAULT '',
			`modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`mime_type` varchar(100) NOT NULL DEFAULT '',
			PRIMARY KEY (`ID`),
			KEY `gmuid` (`gmuid`),
			KEY `type_date` (`mime_type`,`date`,`ID`),
			KEY `author` (`author`)
		) {$charset_collate}";
		dbDelta( $sql );
	}

	if ( $wpdb->get_var( "show tables like '$gmedia_meta'" ) != $gmedia_meta ) {
		$sql = "CREATE TABLE {$gmedia_meta} (
			`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`gmedia_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`meta_key` varchar(255) DEFAULT NULL,
			`meta_value` longtext,
			PRIMARY KEY (`meta_id`),
			KEY `gmedia_id` (`gmedia_id`),
			KEY `meta_key` (`meta_key`)
		) {$charset_collate}";
		dbDelta( $sql );
	}

	if ( $wpdb->get_var( "show tables like '$gmedia_term'" ) != $gmedia_term ) {
		$sql = "CREATE TABLE {$gmedia_term} (
			`term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(200) NOT NULL DEFAULT '',
			`taxonomy` varchar(32) NOT NULL DEFAULT '',
			`description` longtext NOT NULL,
			`global` bigint(20) unsigned NOT NULL DEFAULT '0',
			`count` bigint(20) NOT NULL DEFAULT '0',
			PRIMARY KEY (`term_id`),
			KEY `taxonomy` (`taxonomy`),
			KEY `name` (`name`)
		) {$charset_collate}";
		dbDelta( $sql );
	}

	if ( $wpdb->get_var( "show tables like '$gmedia_term_meta'" ) != $gmedia_term_meta ) {
		$sql = "CREATE TABLE {$gmedia_term_meta} (
			`meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`gmedia_term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`meta_key` varchar(255) DEFAULT NULL,
			`meta_value` longtext,
			PRIMARY KEY (`meta_id`),
			KEY `gmedia_term_id` (`gmedia_term_id`),
			KEY `meta_key` (`meta_key`)
		) {$charset_collate}";
		dbDelta( $sql );
	}

	if ( $wpdb->get_var( "show tables like '$gmedia_term_relationships'" ) != $gmedia_term_relationships ) {
		$sql = "CREATE TABLE {$gmedia_term_relationships} (
			`gmedia_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`gmedia_term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`term_order` int(11) NOT NULL DEFAULT '0',
			PRIMARY KEY (`gmedia_id`,`gmedia_term_id`),
			KEY `gmedia_term_id` (`gmedia_term_id`)
		) {$charset_collate}";
		dbDelta( $sql );
	}

	// check one table again, to be sure
	if ( $wpdb->get_var( "show tables like '$gmedia'" ) != $gmedia ) {
		update_option( "gmediaInitCheck", __( 'GRAND Media: Tables could not created, please check your database settings', 'gmLang' ) );
		return;
	}

	$gmOptions = get_option( 'gmediaOptions' );
	// set the default settings, if we didn't upgrade
	if ( empty( $gmOptions ) ) {
		$gmOptions = grand_default_options();
		update_option( 'gmediaOptions', $gmOptions );
	}
	else {
		$default_options   = grand_default_options();
		$grand_new_options = array_diff_key( $default_options, $gmOptions );
		$gmOptions         = array_merge( $gmOptions, $grand_new_options );
		update_option( 'gmediaOptions', $gmOptions );
	}

	// if all is passed, save the DBVERSION
	add_option( "gmediaDbVersion", GRAND_DBVERSION );
	add_option( "gmediaVersion", GRAND_VERSION );

	// try to make gallery dir if not exists
	if ( ! is_dir( WP_CONTENT_DIR . '/' . GRAND_FOLDER ) ) {
		$gmOptions = get_option( 'gmediaOptions' );
		wp_mkdir_p( WP_CONTENT_DIR . '/' . GRAND_FOLDER );
		foreach ( $gmOptions['folder'] as $folder ) {
			wp_mkdir_p( WP_CONTENT_DIR . '/' . GRAND_FOLDER . '/' . $folder );
		}
	}
}

/**
 * Uninstall all settings and tables
 * Called via Setup and register_unstall hook
 *
 * @access internal
 * @return void
 */
function grand_uninstall() {
	//if uninstall not called from WordPress exit
	if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
		exit ();

	/** @var $wpdb wpdb */
	global $wpdb;

	// first remove all tables
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}gmedia" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}gmedia_meta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}gmedia_term" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}gmedia_term_meta" );
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}gmedia_term_relationships" );

	// then remove all options
	delete_option( 'gmediaOptions' );
	delete_option( 'gmediaDbVersion' );
	delete_option( 'gmediaVersion' );
	delete_option( 'gmediaTemp' );
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key=`gm_screen_options`" );

}
