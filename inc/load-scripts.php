<?php
/**
 * Disable error reporting
 *
 * Set this to error_reporting( E_ALL ) or error_reporting( E_ALL | E_STRICT ) for debugging
 */
error_reporting( 0 );

/** load configs */
require_once( dirname( dirname( __FILE__ ) ) . '/config.php' );

if ( ! function_exists( 'gm_get_file' ) ) {
	function gm_get_file( $path ) {

		if ( function_exists( 'realpath' ) )
			$path = realpath( $path );

		if ( ! $path || ! @is_file( $path ) )
			return '';

		return @file_get_contents( $path );
	}
}

$load = preg_replace( '/[^a-z0-9,_-]+/i', '', $_GET['load'] );
$load = explode( ',', $load );

if ( empty( $load ) )
	exit;

$load = array_unique( $load );

$gmOptions   = get_option( 'gmediaOptions' );
$taxonomy       = 'gmedia_module';
$compress       = ( isset( $_GET['c'] ) && $_GET['c'] );
$force_gzip     = ( $compress && 'gzip' == $_GET['c'] );
$expires_offset = 31536000;
global $gMDb, $grandCore;
$upload        = $grandCore->gm_upload_dir();
$loaded        = isset( $_GET['loaded'] ) ? explode( ',', $_GET['loaded'] ) : array();
$module_meta   = array();
$out           = array();
$out['js']     = '';
$out['jsInit'] = '';
$out['jsRun']  = "jQuery(document).ready(function(){\n\n";

foreach ( $load as $mID ) {
	$module = $gMDb->gmGetTerm( $mID, $taxonomy, ARRAY_A );
	if ( is_wp_error( $module ) || empty( $module ) )
		continue;

	$module_meta = $gMDb->gmGetMetaData( 'gmedia_term', $module['term_id'] );
	if ( ! empty( $module_meta ) ) {
		$module_meta = array_map( array( $grandCore, 'gm_arr_o' ), $module_meta );
		$module_meta = array_map( 'maybe_unserialize', $module_meta );
	}

	// module folder
	$module_dir = $grandCore->gm_get_module_path( $module_meta['module_name'] );
	if ( ! $module_dir ) {
		continue;
	}
	// merge all module info into one array
	$module_meta = array_merge( $module, $module_meta );

	$module_ot = array();
	include( $module_dir['path'] . '/settings.php' );

	foreach ( $module_ot['settings'] as $key => $section ) {
		/* loop through meta box fields */
		foreach ( $section['fields'] as $field ) {
			if ( isset( $module_meta[$field['id']] ) || $field['type'] == 'textblock' )
				continue;
			/* set default to standard value */
			$module_meta[$field['id']] = isset( $field['std'] ) ? $field['std'] : '';
		}
	}

	if ( ! isset( $module_meta['gMediaQuery'] ) || ! is_array( $module_meta['gMediaQuery'] ) || empty( $module_meta['gMediaQuery'] ) ) {
		continue;
	}

	$continue = false;
	$jsInit   = '';
	$jsRun    = '';
	if ( file_exists( $module_dir['path'] . '/init.php' ) )
		include( $module_dir['path'] . '/init.php' );
	if ( $continue ) {
		continue;
	}

	if ( ! in_array( $module_meta['module_name'], $loaded ) ) {
		$path = $module_dir['path'] . '/js';
		$jss  = glob( $path . '/*.js', GLOB_NOSORT );
		foreach ( $jss as $js ) {
			$out['js'] .= gm_get_file( $js ) . "\n";
		}
		$loaded[] = $module_meta['module_name'];
	}
	$out['jsInit'] .= $jsInit;
	$out['jsRun'] .= $jsRun;
}

$out['jsRun'] .= "});\n";

$out = implode( "\n", array_filter( $out ) );

header( 'Content-Type: application/x-javascript; charset=UTF-8' );
header( 'Expires: ' . gmdate( "D, d M Y H:i:s", time() + $expires_offset ) . ' GMT' );
header( "Cache-Control: public, max-age=$expires_offset" );

if ( $compress && ! ini_get( 'zlib.output_compression' ) && 'ob_gzhandler' != ini_get( 'output_handler' ) && isset( $_SERVER['HTTP_ACCEPT_ENCODING'] ) ) {
	header( 'Vary: Accept-Encoding' ); // Handle proxies
	if ( false !== stripos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate' ) && function_exists( 'gzdeflate' ) && ! $force_gzip ) {
		header( 'Content-Encoding: deflate' );
		$out = gzdeflate( $out, 3 );
	}
	elseif ( false !== stripos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) && function_exists( 'gzencode' ) ) {
		header( 'Content-Encoding: gzip' );
		$out = gzencode( $out, 3 );
	}
}

echo $out;

exit;
