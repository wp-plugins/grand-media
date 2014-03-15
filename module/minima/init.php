<?php
/** @var $module_meta
 * @var  $module_dir
 * @var  $upload
 * @var  $jsInit
 * @var  $jsRun
 **/
$jsInit .= "var gmMinima_ID{$module_meta['term_id']},\n";
$jsInit .= "gmMinima_ID{$module_meta['term_id']}_Settings = {\n";
$a = array();
if ( isset( $module_meta['width'] ) )
	$a[] = "	'width': '" . intval( $module_meta['width'] ) . ( strpos( $module_meta['width'], '%' ) ? '%' : '' ) . "'";
if ( isset( $module_meta['height'] ) )
	$a[] = "	'height': '" . intval( $module_meta['height'] ) . ( strpos( $module_meta['height'], '%' ) ? '%' : '' ) . "'";
if ( isset( $module_meta['autoSlideshow'][0] ) )
	$a[] = "	'autoSlideshow': " . ( empty( $module_meta['autoSlideshow'][0] ) ? 'false' : 'true' ) . ' /* autoSlideshow */';
if ( isset( $module_meta['slideshowDelay'] ) )
	$a[] = "	'slideshowDelay': " . intval( $module_meta['slideshowDelay'] ) . ' /* slideshowDelay */';
if ( isset( $module_meta['thumbnailsWidth'] ) )
	$a[] = "	'thumbnailsWidth': " . intval( $module_meta['thumbnailsWidth'] ) . ' /* thumbnailsWidth */';
if ( isset( $module_meta['thumbnailsHeight'] ) )
	$a[] = "	'thumbnailsHeight': " . intval( $module_meta['thumbnailsHeight'] ) . ' /* thumbnailsHeight */';
if ( isset( $module_meta['property0'] ) )
	$a[] = "	'property0': '{$module_meta['property0']}'" . ' /* wmode */';
if ( isset( $module_meta['property1'] ) )
	$a[] = "	'property1': '0x{$module_meta['property1']}'" . ' /* bgColor */';
if ( isset( $module_meta['counterStatus'][0] ) )
	$a[] = "	'counterStatus': " . ( empty( $module_meta['counterStatus'][0] ) ? 'false' : 'true' ) . ' /* counterStatus */';
if ( isset( $module_meta['barBgColor'] ) )
	$a[] = "	'barBgColor': '0x{$module_meta['barBgColor']}'" . ' /* barBgColor */';
if ( isset( $module_meta['labelColor'] ) )
	$a[] = "	'labelColor': '0x{$module_meta['labelColor']}'" . ' /* labelColor */';
if ( isset( $module_meta['labelColorOver'] ) )
	$a[] = "	'labelColorOver': '0x{$module_meta['labelColorOver']}'" . ' /* labelColorOver */';
if ( isset( $module_meta['backgroundColorButton'] ) )
	$a[] = "	'backgroundColorButton': '0x{$module_meta['backgroundColorButton']}'" . ' /* backgroundColorButton */';
if ( isset( $module_meta['descriptionBGColor'] ) )
	$a[] = "	'descriptionBGColor': '0x{$module_meta['descriptionBGColor']}'" . ' /* descriptionBGColor */';
if ( isset( $module_meta['descriptionBGAlpha'] ) )
	$a[] = "	'descriptionBGAlpha': " . intval( $module_meta['descriptionBGAlpha'] ) . ' /* descriptionBGAlpha */';
if ( isset( $module_meta['imageTitleColor'] ) )
	$a[] = "	'imageTitleColor': '0x{$module_meta['imageTitleColor']}'" . ' /* imageTitleColor */';
if ( isset( $module_meta['galleryTitleFontSize'] ) )
	$a[] = "	'galleryTitleFontSize': " . intval( $module_meta['galleryTitleFontSize'] ) . ' /* galleryTitleFontSize */';
if ( isset( $module_meta['titleFontSize'] ) )
	$a[] = "	'titleFontSize': " . intval( $module_meta['titleFontSize'] ) . ' /* titleFontSize */';
if ( isset( $module_meta['imageDescriptionColor'] ) )
	$a[] = "	'imageDescriptionColor': '0x{$module_meta['imageDescriptionColor']}'" . ' /* imageDescriptionColor */';
if ( isset( $module_meta['descriptionFontSize'] ) )
	$a[] = "	'descriptionFontSize': " . intval( $module_meta['descriptionFontSize'] ) . ' /* descriptionFontSize */';
if ( isset( $module_meta['linkColor'] ) )
	$a[] = "	'linkColor': '0x{$module_meta['linkColor']}'" . ' /* linkColor */';

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

if ( is_page() ) {
	global $post;
	$a[] = "	'postID': " . intval( $post->ID );
	$a[] = "	'postTitle': '" . esc_url( $post->title ) . "'";
}

$jsInit .= implode( ",\n", $a ) . "\n";
$jsInit .= "},\n";

$jsInit .= "gmMinima_ID{$module_meta['term_id']}_Content = [\n";
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
			'crop' => 1,
			'max_w' => isset($module_meta['thumbnailsWidth'])? $module_meta['thumbnailsWidth'] : 75,
			'max_h' => isset($module_meta['thumbnailsHeight'])? $module_meta['thumbnailsHeight'] : 75
		);
		$thumb = $grandCore->linked_img($args, false);
		if(isset($thumb['crunch'])){
			$crunch[] = $thumb['crunch'];
		}
		$b[]   = "		{'pid': '{$item->ID}','filename': '/{$gmOptions['folder']['image']}/{$item->gmuid}','thumb': '/{$gmOptions['folder']['link']}/{$thumb['file']}','alttext': " . json_encode( $item->title ) . ",'description': " . json_encode( str_replace(array("\r\n", "\r", "\n"), '', wpautop($item->description)) ) . ",'link':" . json_encode($meta['link']) . ",'imagedate': '{$item->date}','views': '{$meta['views']}','likes': '{$meta['likes']}','w': '{$_metadata['width']}','h': '{$_metadata['height']}'}";
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
	$jsInit .= "var gmMinima_ID{$module_meta['term_id']}_Crunch = ".json_encode($crunch).";\n\n";
}
$jsRun .= "	gmMinima_ID{$module_meta['term_id']} = jQuery('#gmMinima_ID{$module_meta['term_id']}').gmMinima();\n\n";