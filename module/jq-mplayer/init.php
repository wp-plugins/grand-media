<?php
/** @var $module_meta
 *  @var  $jsInit
 *  @var  $jsRun
 **/
$jsInit .= "var gmMusicPlayer_ID{$module_meta['term_id']}_Settings = {\n";
$a = array();
if ( isset( $module_meta['width'] ) )
	$a[] = "	'width': '" . intval( $module_meta['width'] ) . ( strpos( $module_meta['width'], '%' ) ? '%' : '' ) . "'";
if ( isset( $module_meta['autoPlay'] ) )
	$a[] = "	'autoPlay': " . ( empty( $module_meta['autoPlay'] ) ? 'false' : 'true' );
if ( isset( $module_meta['buttonText'] ) )
	$a[] = "	'linkText': " . json_encode($module_meta['buttonText']);
if ( isset( $module_meta['tracksToShow'] ) )
	$a[] = "	'tracksToShow': " . intval( $module_meta['tracksToShow'] );
if ( isset( $module_meta['moreText'] ) )
	$a[] = "	'moreText': " . json_encode($module_meta['moreText']);

if ( isset( $module_meta['description'] ) )
	$a[] = "	'description': " . json_encode($module_meta['description']);

$a[] = "	'jPlayer': { 'swfPath': " . json_encode( plugins_url( GRAND_FOLDER ).'/inc/jplayer' ) . " }";
$a[] = "	'moduleName': '" . esc_js( $module_meta['name'] ) . "'";
$a[] = "	'pluginUrl': '" . plugins_url( GRAND_FOLDER ) . "'";
$a[] = "	'libraryUrl': '" . rtrim( $upload['url'], '/' ) . "'";
$a[] = "	'moduleUrl': '" . $module_dir['url'] . "'";


$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "},\n";

$jsInit .= "gmMusicPlayer_ID{$module_meta['term_id']}_Content = [\n";
$a = array();
/**
 * @var $gMDb
 * @var $grandCore
 */
foreach ( $module_meta['gMediaQuery'] as $i => $tab ) {
	$gMediaQuery = $gMDb->get_gmedias( $tab );
	if ( empty( $gMediaQuery ) ) {
		continue;
	}
	foreach ( $gMediaQuery as $item ) {
		$ext  = substr( $item->gmuid, -3 );
		if(!in_array($ext, array('mp3', 'ogg'))){
			continue;
		}
		$meta  = $gMDb->get_metadata( 'gmedia', $item->ID );
		$preview_image = '';
		if(isset($meta['preview'][0]) && intval($meta['preview'][0])){
			$preview_item = $gMDb->get_gmedia( intval($meta['preview'][0]) );
			$preview_image = $grandCore->gm_get_media_image( $preview_item, 'thumb', array(), 'src' );
		}
		$button = isset($meta['link'][0])? json_encode($meta['link'][0]) : "''";
		if($ext == 'ogg'){$ext = 'oga';}
		$a[]   = "	{{$ext}: '" . rtrim( $upload['url'], '/' ) . "/{$gmOptions['folder']['audio']}/{$item->gmuid}', cover: '{$preview_image}', title: " . json_encode( $item->title ) . ", text: " .  json_encode(wpautop($item->description)) . ", rating: '', button: {$button}}";
	}
}
if ( empty( $a ) ) {
	$continue = true;
}

$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "];\n\n";

$jsRun .= "	jQuery('#gmMusicPlayer_ID{$module_meta['term_id']}').gmMusicPlayer(gmMusicPlayer_ID{$module_meta['term_id']}_Content, gmMusicPlayer_ID{$module_meta['term_id']}_Settings);\n\n";

?>
