<?php
/** @var $gmDB
 * @var  $gmCore
 * @var  $gmGallery
 * @var  $gallery
 * @var  $module
 * @var  $settings
 * @var  $term
 * @var  $gmedia
 * @var  $is_bot
 **/
$content = array();
if(!isset($shortcode_raw)){ $shortcode_raw = false; }
$tab = sanitize_title($gallery['name']);
foreach($terms as $term){

	foreach($gmedia[$term->term_id] as $item){
		$ext = substr( $item->gmuid, -3 );
		if(!in_array($ext, array('mp3', 'ogg'))){
			continue;
		}
		if($ext == 'ogg'){
			$ext = 'oga';
		}
		$cover = $gmCore->gm_get_media_image($item, 'thumb', true, '');
		$rating = $gmDB->get_metadata('gmedia', $item->ID, 'rating', true);
		$rating = array_merge(array('value' => 0, 'votes' => 0), (array) $rating);
		$content[] = array(
			 'id' => $item->ID
			,$ext => "{$gmCore->upload['url']}/{$gmGallery->options['folder']['audio']}/{$item->gmuid}"
			,'cover' => $cover
			,'title' => $item->title
			,'text' => str_replace(array("\r\n", "\r", "\n"), '', wpautop($item->description))
			,'button' => $item->link
			,'rating' => $rating['value']
			,'votes' => $rating['votes']
		);
	}
}

if(!empty($content)){
	$settings = array_merge($settings, array(
		'ID' => $gallery['term_id'],
		'moduleUrl' => $module['url'],
		'pluginUrl' => $gmCore->gmedia_url,
		'libraryUrl' => $gmCore->upload['url'],
		'ip' => str_replace('.', '', $_SERVER['REMOTE_ADDR'])
	));
	$jqmp_autoplay_setting = intval($settings['autoplay']);
	if($jqmp_autoplay_setting){
		$gmedia_shortcode_instance['music_autoplay'] = isset($gmedia_shortcode_instance['music_autoplay'])? $gmedia_shortcode_instance['music_autoplay'] + 1 : 0;
		if($gmedia_shortcode_instance['music_autoplay']){
			$settings['autoplay'] = '0';
		}
	}

	if($shortcode_raw){ echo '<pre style="display:none">'; }
	?><script type="text/javascript">
		jQuery(function(){
			var settings = <?php echo json_encode($settings); ?>;
			var content = <?php echo json_encode($content); ?>;
			jQuery('#GmediaGallery_<?php echo $gallery['term_id'] ?>').data('uid', '<?php echo $gallery['term_id'] ?>').gmMusicPlayer(content, settings);
		});
	</script><?php if($shortcode_raw){ echo '</pre>'; }
} else{
	echo GMEDIA_GALLERY_EMPTY;
}
