<?php
/** @var $module_meta
 * @var  $jsInit
 * @var  $jsRun
 **/
$jsInit .= "var gmPhotoBox_ID{$module_meta['term_id']}_Data = {\n";
$a = array();
if ( isset( $module_meta['history'] ) )
	$a[] = "	history: {$module_meta['history']}";
if ( isset( $module_meta['time'] ) )
	$a[] = "	time: " . intval( $module_meta['time'] );
if ( isset( $module_meta['autoplay'] ) )
	$a[] = "	autoplay: {$module_meta['autoplay']}";
if ( isset( $module_meta['loop'] ) )
	$a[] = "	loop: {$module_meta['loop']}";
if ( isset( $module_meta['thumbs'] ) )
	$a[] = "	thumbs: {$module_meta['thumbs']}";
if ( isset( $module_meta['image_title'] ) )
	$a[] = "	title: {$module_meta['image_title']}";
if ( isset( $module_meta['counter'] ) )
	$a[] = "	counter: {$module_meta['counter']}";
if ( isset( $module_meta['image_description'] ) )
	$a[] = "	caption: {$module_meta['image_description']}";
if ( isset( $module_meta['zoomable'] ) )
	$a[] = "	zoomable: {$module_meta['zoomable']}";
if ( isset( $module_meta['hideFlash'] ) )
	$a[] = "	hideFlash: {$module_meta['hideFlash']}";

if ( isset( $module_meta['loveLink'] ) )
	$a[] = "	'loveLink': '{$module_meta['loveLink'][0]}'";

$a[] = "	'moduleName': '" . esc_js( $module_meta['name'] ) . "'";
$a[] = "	'pluginUrl': '" . plugins_url( GMEDIA_FOLDER ) . "'";
$a[] = "	'libraryUrl': '" . rtrim( $upload['url'], '/' ) . "'";
$a[] = "	'moduleUrl': '" . $module_dir['url'] . "'";


$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "};\n";

$jsInit .= "gmPhotoBox_ID{$module_meta['term_id']}_Data.content = [\n";
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
	foreach ( $gMediaQuery as $item ) {
		$_metadata = $gMDb->get_metadata('gmedia', $item->ID, '_metadata', true);
		$meta['link'] = $gMDb->get_metadata('gmedia', $item->ID, 'link', true);

		$args = array(
			'id' => $item->ID,
			'file' => $item->gmuid,
			'suffix' => 'thumb',
			'width' => $_metadata['width'],
			'height' => $_metadata['height']
		);
		$thumb = $grandCore->linked_img($args, false);
		/*if(isset($thumb['crunch'])){
			$crunch[] = $thumb['crunch'];
		}*/

		$a[]   = "	{'id':'{$item->ID}','image': '/{$gmOptions['folder']['image']}/{$item->gmuid}','thumb': '/{$gmOptions['folder']['link']}/{$thumb['file']}','title': " . json_encode( strip_tags($item->title) ) . ",'text': " .  json_encode( str_replace(array("\r\n", "\r", "\n"), '', wpautop($item->description)) ) . ",'media': '','link': " . json_encode($meta['link']) . ",'linkTarget': ''}";
	}
}
if ( empty( $a ) ) {
	$continue = true;
}

$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "];\n\n";
/*
if (!empty($crunch)){
	$jsInit .= "gmPhotoBox_ID{$module_meta['term_id']}_Crunch = ".json_encode($crunch).";\n\n";
}
*/
$jsRun .= "	jQuery('#gmPhotoBox_ID{$module_meta['term_id']}').photobox('a', gmPhotoBox_ID{$module_meta['term_id']}_Data);\n\n";

?>
