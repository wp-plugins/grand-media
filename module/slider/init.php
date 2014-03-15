<?php
/** @var $module_meta
 * @var  $module_dir
 * @var  $upload
 * @var  $jsInit
 * @var  $jsRun
 **/
$jsInit .= "var gmSlider_ID{$module_meta['term_id']},\n";
$jsInit .= "gmSlider_ID{$module_meta['term_id']}_Settings = {\n";
$a = array();
if ( isset( $module_meta['width'] ) )
	$a[] = "	'width': '" . intval( $module_meta['width'] ) . ( strpos( $module_meta['width'], '%' ) ? '%' : '' ) . "'";
if ( isset( $module_meta['height'] ) )
	$a[] = "	'height': '" . intval( $module_meta['height'] ) . ( strpos( $module_meta['height'], '%' ) ? '%' : '' ) . "'";
if ( isset( $module_meta['property5'][0] ) )
	$a[] = "	'property5': " . ( empty( $module_meta['property5'][0] ) ? 'false' : 'true' ) . ' /* autoSlideshow */';
if ( isset( $module_meta['property2'] ) )
	$a[] = "	'property2': " . intval( $module_meta['property2'] ) . ' /* slideshowDelay */';
if ( isset( $module_meta['property3'] ) )
	$a[] = "	'property3': " . intval( $module_meta['property3'] ) . ' /* navBarHeight */';
if ( isset( $module_meta['thumbSpace'] ) )
	$a[] = "	'thumbSpace': " . intval( $module_meta['thumbSpace'] ) . ' /* imgSpaceH */';
if ( isset( $module_meta['fancyBox'][0] ) )
	$a[] = "	'fancyBox': " . ( empty( $module_meta['fancyBox'][0] ) ? 'false' : 'true' ) . ' /* lightBox */';
if ( isset( $module_meta['showLink'][0] ) )
	$a[] = "	'showLink': " . ( empty( $module_meta['showLink'][0] ) ? 'false' : 'true' ) . ' /* showLink */';
if ( isset( $module_meta['linkTarget'] ) )
	$a[] = "	'linkTarget': '{$module_meta['linkTarget']}'" . ' /* linkTarget */';
if ( isset( $module_meta['property4'][0] ) )
	$a[] = "	'property4': " . ( empty( $module_meta['property4'][0] ) ? 'false' : 'true' ) . ' /* descrVisOnMouseover */';
if ( isset( $module_meta['property0'] ) )
	$a[] = "	'property0': '{$module_meta['property0']}'" . ' /* wmode */';

if ( isset( $module_meta['property1'] ) )
	$a[] = "	'property1': '0x{$module_meta['property1']}'" . ' /* bgColor */';
if ( isset( $module_meta['property6'] ) )
	$a[] = "	'property6': '0x{$module_meta['property6']}'" . ' /* barsBgColor */';
if ( isset( $module_meta['property7'] ) )
	$a[] = "	'property7': '0x{$module_meta['property7']}'" . ' /* catButtonColor */';
if ( isset( $module_meta['property8'] ) )
	$a[] = "	'property8': '0x{$module_meta['property8']}'" . ' /* catButtonColorHover */';
if ( isset( $module_meta['property15'] ) )
	$a[] = "	'property15': '0x{$module_meta['property15']}'" . ' /* imageTitleColor */';
if ( isset( $module_meta['titleFontSize'] ) )
	$a[] = "	'titleFontSize': " . intval( $module_meta['titleFontSize'] ) . ' /* imageTitleFontSize */';
if ( isset( $module_meta['property16'] ) )
	$a[] = "	'property16': '0x{$module_meta['property16']}'" . ' /* imageDescrColor */';
if ( isset( $module_meta['descriptionFontSize'] ) )
	$a[] = "	'descriptionFontSize': " . intval( $module_meta['descriptionFontSize'] ) . ' /* imageDescrFontSize */';
if ( isset( $module_meta['property13'] ) )
	$a[] = "	'property13': '0x{$module_meta['property13']}'" . ' /* imageDescrBgColor */';
if ( isset( $module_meta['property14'] ) )
	$a[] = "	'property14': " . intval( $module_meta['property14'] ) . ' /* imageDescrBgAlpha */';

if ( isset( $module_meta['backButtonColorText'] ) )
	$a[] = "	'backButtonColorText': '0x{$module_meta['backButtonColorText']}'" . ' /* backButtonTextColor */';
if ( isset( $module_meta['backButtonColorBg'] ) )
	$a[] = "	'backButtonColorBg': '0x{$module_meta['backButtonColorBg']}'" . ' /* backButtonBgColor */';

if ( isset( $module_meta['swfMouseWheel'][0] ) )
	$a[] = "	'swfMouseWheel': " . ( empty( $module_meta['swfMouseWheel'][0] ) ? 'false' : 'true' );
if ( isset( $module_meta['hitcounter'][0] ) )
	$a[] = "	'hitcounter': " . ( empty( $module_meta['hitcounter'][0] ) ? 'false' : 'true' );


if ( isset( $module_meta['loveLink'][0] ) )
	$a[] = "	'loveLink': " . ( empty( $module_meta['loveLink'][0] ) ? 'false' : 'true' );

$a[] = "	'moduleName': '" . esc_js( $module_meta['name'] ) . "'";
$a[] = "	'pluginUrl': '" . strtolower(plugins_url( GMEDIA_FOLDER )) . "'";
$a[] = "	'libraryUrl': '" . strtolower(rtrim( $upload['url'], '/' )) . "'";
$a[] = "	'moduleUrl': '" . strtolower($module_dir['url']) . "'";
$a[] = "	'key': '" . $gmOptions['gmedia_key2'] . "'";

if ( is_page() ) {
	global $post;
	$a[] = "	'postID': " . intval( $post->ID );
	$a[] = "	'postTitle': '" . esc_url( $post->title ) . "'";
}

$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "},\n";

$jsInit .= "gmSlider_ID{$module_meta['term_id']}_Content = [\n";
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
	$a[$i]  = "	{'gid':'{$i}','name':'{$tabkey}','title':" . json_encode( $name ) . ",'galdesc':" . json_encode( $module_meta['description'] ) . ",'path':" . json_encode(rtrim( $upload['url'], '/' )) . ",'data':[\n";

	$b = array();
	foreach ( $gMediaQuery as $item ) {
		$meta['views'] = intval($gMDb->get_metadata('gmedia', $item->ID, 'views', true));
		$meta['likes'] = intval($gMDb->get_metadata('gmedia', $item->ID, 'likes', true));
		$meta['link'] = $gMDb->get_metadata('gmedia', $item->ID, 'link', true);
		$_metadata = $gMDb->get_metadata('gmedia', $item->ID, '_metadata', true);
		$args = array(
			'id' => $item->ID,
			'file' => $item->gmuid,
			'width' => $_metadata['width'],
			'height' => $_metadata['height'],
			'max_w' => 0,
			'max_h' => $module_meta['thumbHeight']
		);
		$b[]   = "		{'pid': '{$item->ID}','filename': '/{$gmOptions['folder']['image']}/{$item->gmuid}','alttext': " . json_encode( $item->title ) . ",'description': " . json_encode( str_replace(array("\r\n", "\r", "\n"), '', wpautop($item->description)) ) . ",'link':" . json_encode($meta['link']) . ",'imagedate': '{$item->date}','views': '{$meta['views']}','likes': '{$meta['likes']}','w': '{$_metadata['width']}','h': '{$_metadata['height']}'}";
	}
	$a[$i] .= implode( ",\n", $b ) . "\n";
	$a[$i] .= "	]}";
}
if ( empty( $a ) ) {
	$continue = true;
}

$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "];\n\n";

$jsRun .= "	gmSlider_ID{$module_meta['term_id']} = jQuery('#gmSlider_ID{$module_meta['term_id']}').gmSlider();\n\n";