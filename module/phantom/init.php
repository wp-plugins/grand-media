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
if(!isset($is_bot)){ $is_bot = false; }
$tab = sanitize_title($gallery['name']);
foreach($terms as $term){

	foreach($gmedia[$term->term_id] as $item){
		if('image' != substr($item->mime_type, 0, 5)){
			continue;
		}
		$_metadata = $gmDB->get_metadata('gmedia', $item->ID, '_metadata', true);
		$link_target = '';
		if($item->link){
			$url_host = parse_url($item->link, PHP_URL_HOST);
			$base_url_host = parse_url($gmCore->upload['url'], PHP_URL_HOST);
			if($url_host == $base_url_host || empty($url_host)){
				$link_target = '_self';
			}	else{
				$link_target = '_blank';
			}
		}
		$content[] = array(
			'id' => $item->ID
			,'image' => "/{$gmGallery->options['folder']['image']}/{$item->gmuid}"
			,'thumb' => "/{$gmGallery->options['folder']['image_thumb']}/{$item->gmuid}"
			,'captionTitle' => $item->title
			,'captionText' => str_replace(array("\r\n", "\r", "\n"), '', wpautop($item->description))
			,'media' => ''
			,'link' => $item->link
			,'linkTarget' => $link_target
			,'date' => $item->date
			,'websize' => array_values($_metadata['web'])
			,'thumbsize' => array_values($_metadata['thumb'])
		);
	}
}

if(!empty($content)){
	$settings = array_merge($settings, array(
		'ID' => $gallery['term_id'],
		'moduleUrl' => $module['url'],
		'pluginUrl' => $gmCore->gmedia_url,
		'libraryUrl' => $gmCore->upload['url']
	));
	?>
<script type="text/javascript">
	jQuery(function(){
		var settings = <?php echo json_encode($settings); ?>;
		var content = <?php echo json_encode($content); ?>;
		jQuery('#GmediaGallery_<?php echo $gallery['term_id'] ?>').gmPhantom([content, settings]);
	});
</script>
<div class="gmPhantom_Container delay" style="opacity:0.1">
	<div class="gmPhantom_Background"></div>
	<div class="gmPhantom_thumbsWrapper">
		<?php $i = 0; $wrapper_r = $settings['thumbWidth']/$settings['thumbHeight'];
		$tw_size = "width:{$settings['thumbWidth']}px;height:{$settings['thumbHeight']}px;";
		foreach($content as $item){
			?><div class="gmPhantom_ThumbContainer gmPhantom_ThumbLoader" style="<?php echo $tw_size; ?>" data-no="<?php echo $i++; ?>"><?php
			$thumb_r = $item['thumbsize'][0]/$item['thumbsize'][1];
			if($wrapper_r < $thumb_r){
				$orientation = 'landscape';
				$margin = 'margin:0 0 0 -'.floor(($settings['thumbHeight']*$thumb_r - $settings['thumbWidth'])/$settings['thumbWidth']*50).'%;';
			} else{
				$orientation = 'portrait';
				$margin = 'margin:-'.floor(($settings['thumbWidth']/$thumb_r - $settings['thumbHeight'])/$settings['thumbHeight']*25).'% 0 0 0;';
			}
			?><div class="gmPhantom_Thumb"><img style="<?php echo $margin; ?>" class="<?php echo $orientation; ?>" src="<?php echo $settings['libraryUrl'].$item['thumb']; ?>" alt="<?php echo esc_attr($item['captionTitle']); ?>" /></div><?php
			if(($settings['thumbsInfo'] == 'label') && ($item['captionTitle'] != '')){
				?><div class="gmPhantom_ThumbLabel"><?php echo $item['captionTitle']; ?></div><div style="display:none;" class="gmPhantom_ThumbCaption"><?php echo $item['captionText']; ?></div><?php
			} ?></div><?php
		} ?><br style="clear:both;" />
	</div>
</div>

<?php
} else{
	echo GMEDIA_GALLERY_EMPTY;
}


