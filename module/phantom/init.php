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
					if('image' != substr( $item->mime_type, 0, 5 )){
						continue;
					}
					$_metadata = $gmDB->get_metadata('gmedia', $item->ID, '_metadata', true);
					$a[]   = "	{'image': '/{$gmGallery->options['folder']['image']}/{$item->gmuid}','thumb': '/{$gmGallery->options['folder']['image_thumb']}/{$item->gmuid}','captionTitle': " . json_encode( $item->title ) . ",'captionText': " .  json_encode( str_replace(array("\r\n", "\r", "\n"), '', wpautop($item->description)) ) . ",'media': '','link': " . json_encode($item->link) . ",'linkTarget': '_self'}";
				}
			}
			echo implode( ",\n", $a )."\n"; ?>
		];
		jQuery('#GmediaGallery_<?php echo $gallery['term_id'] ?>').gmPhantom([content, settings]);
	});
</script>
