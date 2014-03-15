<?php
/** @var $module_meta
 * @var  $jsInit
 * @var  $jsRun
 **/
$jsInit .= "var gmPhantom_ID{$module_meta['term_id']}_Settings = {\n";
$a = array();
if ( isset( $module_meta['width'] ) )
	$a[] = "	'width': " . intval( $module_meta['width'] );
if ( isset( $module_meta['height'] ) )
	$a[] = "	'height': " . intval( $module_meta['height'] );
if ( isset( $module_meta['responsiveEnabled'] ) )
	$a[] = "	'responsiveEnabled': '{$module_meta['responsiveEnabled']}'";
if ( isset( $module_meta['thumbsNavigation'] ) )
	$a[] = "	'thumbsNavigation': '{$module_meta['thumbsNavigation']}'";
if ( isset( $module_meta['thumbCols'] ) )
	$a[] = "	'thumbCols': " . intval( $module_meta['thumbCols'] );
if ( isset( $module_meta['thumbRows'] ) )
	$a[] = "	'thumbRows': " . intval( $module_meta['thumbRows'] );
if ( isset( $module_meta['bgColor'] ) )
	$a[] = "	'bgColor': '{$module_meta['bgColor']}'";
if ( isset( $module_meta['bgAlpha'] ) )
	$a[] = "	'bgAlpha': " . intval( $module_meta['bgAlpha'] );

if ( isset( $module_meta['thumbWidth'] ) )
	$a[] = "	'thumbWidth': " . intval( $module_meta['thumbWidth'] );
if ( isset( $module_meta['thumbHeight'] ) )
	$a[] = "	'thumbHeight': " . intval( $module_meta['thumbHeight'] );
if ( isset( $module_meta['thumbsSpacing'] ) )
	$a[] = "	'thumbsSpacing': " . intval( $module_meta['thumbsSpacing'] );
if ( isset( $module_meta['thumbsVerticalPadding'] ) )
	$a[] = "	'thumbsVerticalPadding': " . intval( $module_meta['thumbsVerticalPadding'] );
if ( isset( $module_meta['thumbsHorizontalPadding'] ) )
	$a[] = "	'thumbsHorizontalPadding': " . intval( $module_meta['thumbsHorizontalPadding'] );
if ( isset( $module_meta['thumbsAlign'] ) )
	$a[] = "	'thumbsAlign': '{$module_meta['thumbsAlign']}'";

if ( isset( $module_meta['thumbAlpha'] ) )
	$a[] = "	'thumbAlpha': " . intval( $module_meta['thumbAlpha'] );
if ( isset( $module_meta['thumbAlphaHover'] ) )
	$a[] = "	'thumbAlphaHover': " . intval( $module_meta['thumbAlphaHover'] );
if ( isset( $module_meta['thumbBorderSize'] ) )
	$a[] = "	'thumbBorderSize': " . intval( $module_meta['thumbBorderSize'] );
if ( isset( $module_meta['thumbBorderColor'] ) )
	$a[] = "	'thumbBorderColor': '{$module_meta['thumbBorderColor']}'";
if ( isset( $module_meta['thumbPadding'] ) )
	$a[] = "	'thumbPadding': " . intval( $module_meta['thumbPadding'] );

if ( isset( $module_meta['thumbsInfo'] ) )
	$a[] = "	'thumbsInfo': '{$module_meta['thumbsInfo']}'";

if ( isset( $module_meta['tooltipBgColor'] ) )
	$a[] = "	'tooltipBgColor': '{$module_meta['tooltipBgColor']}'";
if ( isset( $module_meta['tooltipStrokeColor'] ) )
	$a[] = "	'tooltipStrokeColor': '{$module_meta['tooltipStrokeColor']}'";
if ( isset( $module_meta['tooltipTextColor'] ) )
	$a[] = "	'tooltipTextColor': '{$module_meta['tooltipTextColor']}'";

if ( isset( $module_meta['lightboxPosition'] ) )
	$a[] = "	'lightboxPosition': '{$module_meta['lightboxPosition']}'";
if ( isset( $module_meta['lightboxWindowColor'] ) )
	$a[] = "	'lightboxWindowColor': '{$module_meta['lightboxWindowColor']}'";
if ( isset( $module_meta['lightboxWindowAlpha'] ) )
	$a[] = "	'lightboxWindowAlpha': " . intval( $module_meta['lightboxWindowAlpha'] );

if ( isset( $module_meta['socialShareEnabled'] ) )
	$a[] = "	'socialShareEnabled': '{$module_meta['socialShareEnabled']}'";

$a[] = "	'moduleName': '" . esc_js( $module_meta['name'] ) . "'";
$a[] = "	'pluginUrl': '" . plugins_url( GMEDIA_FOLDER ) . "'";
$a[] = "	'libraryUrl': '" . rtrim( $upload['url'], '/' ) . "'";
$a[] = "	'moduleUrl': '" . $module_dir['url'] . "'";


$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "},\n";

$jsInit .= "gmPhantom_ID{$module_meta['term_id']}_Content = [\n";
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
			'crop' => 1,
			'width' => $_metadata['width'],
			'height' => $_metadata['height'],
			'max_w' => $module_meta['thumbWidth'],
			'max_h' => $module_meta['thumbHeight']
		);
		$thumb = $grandCore->linked_img($args, false);
		if(isset($thumb['crunch'])){
			$crunch[] = $thumb['crunch'];
		}
		$a[]   = "	{'image': '/{$gmOptions['folder']['image']}/{$item->gmuid}','thumb': '/{$gmOptions['folder']['link']}/{$thumb['file']}','captionTitle': " . json_encode( $item->title ) . ",'captionText': " .  json_encode( str_replace(array("\r\n", "\r", "\n"), '', wpautop($item->description)) ) . ",'media': '','link': " . json_encode($meta['link']) . ",'linkTarget': '_self'}";
	}
}
if ( empty( $a ) ) {
	$continue = true;
}

$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "];\n\n";
if (!empty($crunch)){
	$jsInit .= "var gmPhantom_ID{$module_meta['term_id']}_Crunch = ".json_encode($crunch).";\n\n";
}

$jsRun .= "	jQuery('#gmPhantom_ID{$module_meta['term_id']}').gmPhantom();\n\n";

?>
