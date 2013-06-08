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

$taxonomy       = 'gmedia_module';
$compress       = ( isset( $_GET['c'] ) && $_GET['c'] );
$force_gzip     = ( $compress && 'gzip' == $_GET['c'] );
$rtl            = ( isset( $_GET['dir'] ) && 'rtl' == $_GET['dir'] );
$expires_offset = 31536000;
$out            = $custom_css = '';
global $gMDb, $grandCore;
$loaded      = isset( $_GET['loaded'] ) ? explode( ',', $_GET['loaded'] ) : array();
$module_meta = array();

foreach ( $load as $mID ) {
	$module = $gMDb->get_term( $mID, $taxonomy, ARRAY_A );
	if ( is_wp_error( $module ) || empty( $module ) )
		continue;

	$module_meta = $gMDb->get_metadata( 'gmedia_term', $module['term_id'] );
	if ( ! empty( $module_meta ) ) {
		$module_meta = array_map( array( $grandCore, 'maybe_array_0' ), $module_meta );
		$module_meta = array_map( 'maybe_unserialize', $module_meta );
	}

	if ( isset( $module_meta['customCSS'] ) && trim( $module_meta['customCSS'] ) != '' ) {
		$custom_css .= "\n\n/**** Begin Custom CSS {$module_meta['module_name']} #{$module['term_id']} ****/\n" . $module_meta['customCSS'] . "\n/**** End Custom CSS {$module_meta['module_name']} #{$module['term_id']} ****/";
	}
	if ( in_array( $module_meta['module_name'], $loaded ) )
		continue;

	// module folder
	$module_dir = $grandCore->get_module_path( $module_meta['module_name'] );
	if ( ! $module_dir ) {
		continue;
	}
	// merge all module info into one array
	$module_meta = array_merge( $module, array( 'module_path' => $module_dir['path'], 'module_url' => $module_dir['url'] ), $module_meta );

	$path       = $module_meta['module_path'] . '/css';
	$csss       = glob( $path . '/*.css', GLOB_NOSORT );
	$module_css = '';
	foreach ( $csss as $css ) {
		$module_css .= gm_get_file( $css ) . "\n";
	}
	$out .= str_replace( '../img/', $module_meta['module_url'] . '/img/', $module_css );

	$loaded[] = $module_meta['module_name'];

}

$out .= $custom_css;

header( 'Content-Type: text/css' );
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
