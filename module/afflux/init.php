<?php
/** @var $module_meta
 * @var  $module_dir
 * @var  $upload
 * @var  $jsInit
 * @var  $jsRun
 **/
$jsInit .= "var grandMediaAfflux_ID{$module_meta['term_id']},\n";
$jsInit .= "grandMediaAfflux_ID{$module_meta['term_id']}_Settings = {\n";
$a = array();
if ( isset( $module_meta['width'] ) )
	$a[] = "	'width': '" . intval( $module_meta['width'] ) . ( strpos( $module_meta['width'], '%' ) ? '%' : '' ) . "'";
if ( isset( $module_meta['height'] ) )
	$a[] = "	'height': '" . intval( $module_meta['height'] ) . ( strpos( $module_meta['height'], '%' ) ? '%' : '' ) . "'";
if ( isset( $module_meta['wmode'] ) )
	$a[] = "	'wmode': '{$module_meta['wmode']}'";
if ( isset( $module_meta['swfMouseWheel'] ) )
	$a[] = "	'swfMouseWheel': " . ( empty( $module_meta['swfMouseWheel'] ) ? 'false' : 'true' );
if ( isset( $module_meta['imageZoom'] ) )
	$a[] = "	'imageZoom': '{$module_meta['imageZoom']}'";
if ( isset( $module_meta['autoSlideshow'] ) )
	$a[] = "	'autoSlideshow': " . ( empty( $module_meta['autoSlideshow'] ) ? 'false' : 'true' );
if ( isset( $module_meta['slideshowDelay'] ) )
	$a[] = "	'slideshowDelay': " . intval( $module_meta['slideshowDelay'] );
if ( isset( $module_meta['thumbHeight'] ) )
	$a[] = "	'thumbHeight': " . intval( $module_meta['thumbHeight'] );
if ( isset( $module_meta['descrVisOnMouseover'] ) )
	$a[] = "	'descrVisOnMouseover': " . ( empty( $module_meta['descrVisOnMouseover'] ) ? 'false' : 'true' );

if ( isset( $module_meta['bgColor'] ) )
	$a[] = "	'bgColor': '0x{$module_meta['bgColor']}'";
if ( isset( $module_meta['imagesBgColor'] ) )
	$a[] = "	'imagesBgColor': '0x{$module_meta['imagesBgColor']}'";
if ( isset( $module_meta['barsBgColor'] ) )
	$a[] = "	'barsBgColor': '0x{$module_meta['barsBgColor']}'";
if ( isset( $module_meta['catButtonColor'] ) )
	$a[] = "	'catButtonColor': '0x{$module_meta['catButtonColor']}'";
if ( isset( $module_meta['catButtonColorHover'] ) )
	$a[] = "	'catButtonColorHover': '0x{$module_meta['catButtonColorHover']}'";
if ( isset( $module_meta['scrollBarTrackColor'] ) )
	$a[] = "	'scrollBarTrackColor': '0x{$module_meta['scrollBarTrackColor']}'";
if ( isset( $module_meta['scrollBarButtonColor'] ) )
	$a[] = "	'scrollBarButtonColor': '0x{$module_meta['scrollBarButtonColor']}'";
if ( isset( $module_meta['thumbBgColor'] ) )
	$a[] = "	'thumbBgColor': '0x{$module_meta['thumbBgColor']}'";
if ( isset( $module_meta['thumbLoaderColor'] ) )
	$a[] = "	'thumbLoaderColor': '0x{$module_meta['thumbLoaderColor']}'";
if ( isset( $module_meta['imageTitleColor'] ) )
	$a[] = "	'imageTitleColor': '0x{$module_meta['imageTitleColor']}'";
if ( isset( $module_meta['imageTitleFontSize'] ) )
	$a[] = "	'imageTitleFontSize': " . intval( $module_meta['imageTitleFontSize'] );
if ( isset( $module_meta['imageDescrColor'] ) )
	$a[] = "	'imageDescrColor': '0x{$module_meta['imageDescrColor']}'";
if ( isset( $module_meta['imageDescrFontSize'] ) )
	$a[] = "	'imageDescrFontSize': " . intval( $module_meta['imageDescrFontSize'] );
if ( isset( $module_meta['imageDescrBgColor'] ) )
	$a[] = "	'imageDescrBgColor': '0x{$module_meta['imageDescrBgColor']}'";
if ( isset( $module_meta['imageDescrBgAlpha'] ) )
	$a[] = "	'imageDescrBgAlpha': " . intval( $module_meta['imageDescrBgAlpha'] );

if ( isset( $module_meta['backButtonTextColor'] ) )
	$a[] = "	'backButtonTextColor': '0x{$module_meta['backButtonTextColor']}'";
if ( isset( $module_meta['backButtonBgColor'] ) )
	$a[] = "	'backButtonBgColor': '0x{$module_meta['backButtonBgColor']}'";

if ( isset( $module_meta['hitcounter'] ) )
	$a[] = "	'hitcounter': " . ( empty( $module_meta['hitcounter'] ) ? 'false' : 'true' );
if ( isset( $module_meta['LoveLink'] ) )
	$a[] = "	'loveLink': " . ( empty( $module_meta['loveLink'] ) ? 'false' : 'true' );

$a[] = "	'moduleName': '" . esc_js( $module_meta['name'] ) . "'";
//$a[] = "	'website': '".get_site_url()."'";
$a[] = "	'licenseKey': ''";
$a[] = "	'pluginUrl': '" . plugins_url( GRAND_FOLDER ) . "'";
$a[] = "	'libraryUrl': '" . rtrim( $upload['url'], '/' ) . "'";
$a[] = "	'moduleUrl': '" . $module_dir['url'] . "'";

if ( is_page() ) {
	global $post;
	$a[] = "	'postID': " . intval( $post->ID );
	$a[] = "	'postTitle': '" . esc_url( $post->title ) . "'";
}

$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "},\n";

$jsInit .= "grandMediaAfflux_ID{$module_meta['term_id']}_Content = [\n";
$a = array();
$crunch = array();
/**
 * @var $gMDb
 * @var $grandCore
 */
foreach ( $module_meta['gMediaQuery'] as $i => $tab ) {

	$gMediaQuery = $gMDb->get_gmedias( $tab );
	if ( empty( $gMediaQuery ) ) {
		continue;
	}

	$name   = isset( $tab['tabname'] ) ? $tab['tabname'] : $module_meta['name'];
	$tabkey = sanitize_key( $name );
	$a[$i]  = "	{'cID':'{$tabkey}','name':'" . esc_js( $name ) . "','data':[\n";

	$b = array();
	foreach ( $gMediaQuery as $item ) {
		$meta['views'] = intval($gMDb->get_metadata('gmedia', $item->ID, 'views', true));
		$meta['likes'] = intval($gMDb->get_metadata('gmedia', $item->ID, 'likes', true));
		$_metadata = $gMDb->get_metadata('gmedia', $item->ID, '_metadata', true);
		$args = array(
			'id' => $item->ID,
			'file' => $item->gmuid,
			'width' => $_metadata['width'],
			'height' => $_metadata['height'],
			'max_w' => 0,
			'max_h' => $module_meta['thumbHeight']
		);
		$thumb = $grandCore->linked_img($args, false);
		if(isset($thumb['crunch'])){
			$crunch[] = $thumb['crunch'];
		}
		$b[]   = "		{'id': '{$item->ID}','image': '/{$gmOptions['folder']['image']}/{$item->gmuid}','thumb': '/{$gmOptions['folder']['link']}/{$thumb['file']}','title': '" . str_replace( "\\", "\\\\", esc_html( $item->title )) . "','description': '" . str_replace( array("'", "\\"), array("&#39;", "\\\\"), $item->description ) . "','date': '{$item->date}','views': '{$meta['views']}','likes': '{$meta['likes']}','w': '{$_metadata['width']}','h': '{$_metadata['height']}'}";
	}
	$a[$i] .= implode( ",\n", $b ) . "\n";
	$a[$i] .= "	]}";
}
if ( empty( $a ) ) {
	$continue = true;
}

$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "];\n\n";
if (!empty($crunch)){
	$jsInit .= "grandMediaAfflux_ID{$module_meta['term_id']}_Crunch = ".json_encode($crunch)."\n\n";
}
$jsRun .= "	grandMediaAfflux_ID{$module_meta['term_id']} = jQuery('#grandMediaAfflux_ID{$module_meta['term_id']}').grandMediaAfflux();\n\n";