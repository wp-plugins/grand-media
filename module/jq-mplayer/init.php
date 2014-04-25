<script type="text/javascript">
	jQuery(document).ready(function(){
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
		$settings = array_merge($settings,
			array('ID' => $gallery['term_id'], 'moduleUrl' => $module['url'], 'pluginUrl' => $gmCore->gmedia_url, 'libraryUrl' => $gmCore->upload['url'])
		);
		?>
		var settings = <?php echo json_encode($settings); ?>;
		var content = [
			<?php
			$a = array();
			$i = 0;
			$tab = sanitize_title($gallery['name']);
			foreach ( $terms as $term ) {
				foreach ( $gmedia[$term->term_id] as $item ) {
					$ext = substr( $item->gmuid, -3 );
					if(!in_array($ext, array('mp3', 'ogg'))){
						continue;
					}
					if($ext == 'ogg'){
						$ext = 'oga';
					}
					$cover = $gmCore->gm_get_media_image($item, 'thumb', true, '');
					$a[] = "	{{$ext}: '{$gmCore->upload['url']}/{$gmGallery->options['folder']['audio']}/{$item->gmuid}', cover: '{$cover}', title: " . json_encode( $item->title ) . ", text: " .  json_encode( str_replace(array("\r\n", "\r", "\n"), '', wpautop($item->description)) ) . ", rating: '', button: '{$item->link}'}";
				}
			}
			echo implode( ",\n", $a )."\n"; ?>
		];
		jQuery('#GmediaGallery_<?php echo $gallery['term_id'] ?>').gmMusicPlayer(content, settings);
	});
</script>
