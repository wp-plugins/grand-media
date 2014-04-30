<?php
/** @var $gmDB
 * @var  $gmCore
 * @var  $gmGallery
 * @var  $gallery
 * @var  $module
 * @var  $settings
 * @var  $term
 * @var  $gmedia
 **/
$content = array();
$tab = sanitize_title($gallery['name']);
foreach ( $terms as $term ) {

	$c = array(
		 'cID' => "{$tab}_{$term->term_id}"
		,'name' => $term->name
		,'data' => array()
	);

	foreach ( $gmedia[$term->term_id] as $item ) {
		if('image' != substr( $item->mime_type, 0, 5 )){
			continue;
		}
		$meta['views'] = intval($gmDB->get_metadata('gmedia', $item->ID, 'views', true));
		$meta['likes'] = intval($gmDB->get_metadata('gmedia', $item->ID, 'likes', true));
		$_metadata = $gmDB->get_metadata('gmedia', $item->ID, '_metadata', true);
		if(!empty($item->link)){
			$item->title = '<a href="'.$item->link.'"><b>'. $item->title .'</b></a>';
		}
		$c['data'][] = array(
			 'id' => $item->ID
			,'image' => "/{$gmGallery->options['folder']['image']}/{$item->gmuid}"
			,'thumb' => "/{$gmGallery->options['folder']['image_thumb']}/{$item->gmuid}"
			,'title' => $item->title
			,'description' => str_replace(array("\r\n", "\r", "\n"), '', wpautop($item->description))
			,'date' => $item->date
			,'views' => $meta['views']
			,'likes' => $meta['likes']
			,'w' => $_metadata['original']['width']
			,'h' => $_metadata['original']['height']
		);
	}
	if(!count($c['data'])){
		continue;
	}
	$content[] = $c;
}

if(!empty($content)){
	$settings = array_merge($settings,
		array('ID' => $gallery['term_id'], 'moduleUrl' => $module['url'], 'pluginUrl' => $gmCore->gmedia_url, 'libraryUrl' => $gmCore->upload['url'])
	);
	?>
	<script type="text/javascript">
	var GmediaGallery_<?php echo $gallery['term_id']; ?>;
	jQuery(document).ready(function(){
	var settings = <?php echo json_encode($settings); ?>;
	var content = <?php echo json_encode($content); ?>;
	GmediaGallery_<?php echo $gallery['term_id']; ?> = jQuery('#GmediaGallery_<?php echo $gallery['term_id'] ?>').gmAfflux([content, settings]);
	});
	</script>
<?php
} else{
	echo GMEDIA_GALLERY_EMPTY;
}