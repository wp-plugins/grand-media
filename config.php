<?php
/**
 * Bootstrap file for getting the ABSPATH constant to wp-load.php
 * This is requried when a plugin requires access not via the admin screen.
 *
 * If the wp-load.php file is not found, then an error will be displayed
 */

/** Define the server path to the file wp-config here, if you placed WP-CONTENT outside the classic file structure */

$path = ''; // It should be end with a trailing slash

/** That's all, stop editing from here **/

if ( ! defined( 'WP_LOAD_PATH' ) ) {

	/** classic root path if wp-content and plugins is below wp-config.php */
	preg_match( '|^(.*?/)(wp-content)/|i', str_replace( '\\', '/', __FILE__ ), $_m );
	$classic_root = $_m[1];

	if ( file_exists( $classic_root . 'wp-load.php' ) )
		define( 'WP_LOAD_PATH', $classic_root );
	else
		if ( file_exists( $path . 'wp-load.php' ) )
			define( 'WP_LOAD_PATH', $path );
		else
			exit( "Could not find wp-load.php" );
}

// let's load WordPress
/** @define "WP_LOAD_PATH" "D:\server\xampp\htdocs\video-tutorial\" This is just for the IDE */
require_once( WP_LOAD_PATH . 'wp-load.php' );
