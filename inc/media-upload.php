<?php
/**
 * @title  Add action/filter for the upload tab 
 */

function gmedia_upload_tabs ($tabs) {

	$newtab = array('gmedia' => __('Gmedia Gallery','gmLang'));
 
    return array_merge($tabs,$newtab);
}
	
add_filter('media_upload_tabs', 'gmedia_upload_tabs');

function media_upload_gmedia() {
	global $gmCore, $gmDB;

	wp_iframe( 'media_upload_gmedia_form' );

	// Generate TinyMCE HTML output
	if ( isset($_POST['media-upload-insert-gmedia']) ) {

		$id = $gmCore->_post('ID', 0);

		if( ($gmedia = $gmDB->get_gmedia($id)) ){

			$meta = $gmDB->get_metadata('gmedia', $gmedia->ID, '_metadata', true);

			$size = $gmCore->_post('size', 'web');
			$src = $gmCore->gm_get_media_image($gmedia, $size);
			$width = $meta[$size]['width'];
			$height = $meta[$size]['height'];
			$title = esc_attr($gmCore->_post('title', ''));
			$align = esc_attr($gmCore->_post('align', 'none'));
			$link = trim(esc_attr($gmCore->_post('link', '')));
			$caption = trim($gmCore->_post('description', ''));

			$html = "<img src='{$src}' width='{$width}' height='{$height}' alt='{$title}' title='{$title}' id='gmedia-image-{$id}' class='gmedia-singlepic align{$align}' />";

			if($link){
				$html = "<a href='{$link}'>{$html}</a>";
			}
			if($caption){
				$html = image_add_caption($html, false, $caption, $title, $align, $src, $size, $title);
			}

			// Return it to TinyMCE
			media_send_to_editor($html);
		}
	}

}
add_action('media_upload_gmedia', 'media_upload_gmedia');

function media_upload_gmedia_form() {

	global $type;
	global $gmCore, $gmDB, $gmGallery;

	wp_enqueue_style( 'gmedia-bootstrap' );
	wp_enqueue_script( 'gmedia-bootstrap' );

	wp_enqueue_style( 'grand-media' );
	wp_enqueue_script( 'grand-media' );

	//media_upload_header();

	$post_id 	= intval($gmCore->_get('post_id'));
	//$url = admin_url("media-upload.php?type={$type}&tab=gmedia&post_id={$post_id}");

	$args = array('mime_type' => $gmCore->_get('mime_type', 'image/*'), 'orderby' => 'ID',
								'order' => 'DESC',
								'per_page' => 30, 'page' => $gmCore->_get('pager', 1),
								's' => $gmCore->_get('s', null));
	$gmediaQuery = $gmDB->get_gmedias($args);


	?>

<div class="panel panel-default">
	<div class="panel-heading clearfix">
		<form class="form-inline gmedia-search-form" role="search">
			<div class="form-group">
				<?php foreach($_GET as $key => $value){
					if(in_array($key, array('type','post_id','tab', 'mime_type', 'tag_id', 'tag__in', 'cat', 'category__in', 'alb', 'album__in'))){ ?>
						<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>"/>
					<?php }
				} ?>
				<input id="gmedia-search" class="form-control input-sm" type="text" name="s" placeholder="<?php _e('Search...', 'gmLang'); ?>" value="<?php echo $gmCore->_get('s', ''); ?>"/>
			</div>
			<button type="submit" class="btn btn-default input-sm"><span class="glyphicon glyphicon-search"></span></button>
		</form>
		<?php echo $gmDB->query_pager(); ?>

	</div>
	<div class="panel-body" id="gm-list-table">
		<div class="row">
			<div class="col-xs-7 col-md-9" style="text-align:justify;">
	<?php
	if(count($gmediaQuery)){
		foreach($gmediaQuery as $item) {
			$meta = $gmDB->get_metadata('gmedia', $item->ID);
			$type = explode('/', $item->mime_type);

			/*
			$item_url = $gmCore->upload['url'] . '/' . $gmGallery->options['folder'][$type[0]] . '/' . $item->gmuid;
			$item_path = $gmCore->upload['path'] . '/' . $gmGallery->options['folder'][$type[0]] . '/' . $item->gmuid;

			if (function_exists('exif_imagetype')) {
				$is_webimage = (('image' == $type[0]) && in_array(exif_imagetype($item_path), array(IMAGETYPE_GIF,
																																														IMAGETYPE_JPEG,
																																														IMAGETYPE_PNG)))? true : false;
			} else{
				$is_webimage = (('image' == $type[0]) && in_array($type[1], array('jpeg', 'png', 'gif')))? true : false;
			}

			$tags = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_tag');
			$albs = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_album');
			$cats = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_category');
			*/
			?>
			<form class="thumbnail" id="list-item-<?php echo $item->ID; ?>" data-id="<?php echo $item->ID; ?>" data-type="<?php echo $type[0]; ?>">
				<img src="<?php echo $gmCore->gm_get_media_image($item, 'thumb'); ?>" style="height:100px;width:auto;" alt=""/>
				<span class="glyphicon glyphicon-ok text-success"></span>
				<div class="media-upload-form" style="display:none;">
					<input name="ID" type="hidden" value="<?php echo $item->ID; ?>"/>
					<div class="form-group">
						<label><?php _e('Title', 'gmLang'); ?></label>
						<input name="title" type="text" class="form-control input-sm" placeholder="<?php _e('Title', 'gmLang'); ?>" value="<?php echo esc_attr($item->title); ?>">
					</div>
					<div class="form-group">
						<label><?php _e('Link To', 'gmLang'); ?></label>
						<select id="gmedia_url" class="form-control input-sm" style="display:block;margin-bottom:5px;">
							<option value="customurl" selected="selected"><?php _e('Custom URL'); ?></option>
							<option value="weburl"><?php _e('Web size image'); ?></option>
							<option value="originalurl"><?php _e('Original image'); ?></option>
						</select>
						<input name="link" type="text" class="customurl form-control input-sm" value="<?php echo $item->link; ?>" placeholder="http://"/>
						<input name="link" type="text" style="display:none;font-size:80%;" readonly="readonly" disabled="disabled" class="weburl form-control input-sm" value="<?php echo $gmCore->upload['url'].'/'.$gmGallery->options['folder']['image'].'/'.$item->gmuid; ?>"/>
						<input name="link" type="text" style="display:none;font-size:80%;" readonly="readonly" disabled="disabled" class="originalurl form-control input-sm" value="<?php echo $gmCore->upload['url'].'/'.$gmGallery->options['folder']['image_original'].'/'.$item->gmuid; ?>"/>
					</div>
					<div class="form-group">
						<label><?php _e('Description', 'gmLang'); ?></label>
						<textarea name="description" class="form-control input-sm" rows="4" cols="10"><?php echo esc_html($item->description); ?></textarea>
					</div>
					<?php //if($is_webimage){ ?>
						<?php if('image' == $type[0]){
							$_metadata = unserialize($meta['_metadata'][0]); ?>
						<div class="form-group">
							<label><?php _e('Size', 'gmLang'); ?></label>
							<select name="size" class="form-control input-sm">
								<option value="thumb"><?php echo 'Thumb - ' . $_metadata['thumb']['width'] . ' × ' . $_metadata['thumb']['height']; ?></option>
								<option value="web" selected="selected"><?php echo 'Web - ' . $_metadata['web']['width'] . ' × ' . $_metadata['web']['height']; ?></option>
								<option value="original"><?php echo 'Original - ' . $_metadata['original']['width'] . ' × ' . $_metadata['original']['height']; ?></option>
							</select>
						</div>
						<?php } ?>
					<?php //} ?>
					<div class="form-group">
						<label><?php _e('Alignment', 'gmLang'); ?></label>
						<select name="align" class="form-control input-sm">
							<option value="none" selected="selected"><?php _e('None', 'gmLang'); ?></option>
							<option value="left"><?php _e('Left', 'gmLang'); ?></option>
							<option value="center"><?php _e('Center', 'gmLang'); ?></option>
							<option value="right"><?php _e('Right', 'gmLang'); ?></option>
						</select>
					</div>
				</div>
			</form>
		<?php }
	} else{ ?>
		<div class="list-group-item">
			<div class="well well-lg text-center">
				<h4><?php _e('No items to show.', 'gmLang'); ?></h4>
				<p><a href="<?php echo admin_url('admin.php?page=GrandMedia_AddMedia') ?>" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> <?php _e('Add Media', 'gmLang'); ?></a></p>
			</div>
		</div>
	<?php } ?>
			</div>
			<div class="col-xs-5 col-md-3 media-upload-sidebar">
				<form method="post" id="gmedia-form" role="form">
					<div id="media-upload-form-container"></div>
					<div class="panel-footer">
						<input type="hidden" name="post_id" id="post_id" value="<?php echo $post_id; ?>" />
						<?php wp_nonce_field('media-form'); ?>
						<button type="submit" id="media-upload-form-submit" disabled class="btn btn-primary pull-right" name="media-upload-insert-gmedia"><?php _e('Insert into post', 'gmLang'); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(function($){
			function divFrame(){
				$('.panel-body').css({top:$('.panel-heading').outerHeight()});
			}
			divFrame();
			$(window).on('resize', function(){ divFrame(); });
			$('.thumbnail').on('click', function(){
				if($(this).hasClass('active')){
					$(this).removeClass('active');
					$('#media-upload-form-container').empty();
					$('#media-upload-form-submit').prop('disabled', true);
					return;
				}
				$(this).addClass('active').siblings().removeClass('active');
				$('#media-upload-form-container').html($('.media-upload-form', this).html());
				$('#media-upload-form-submit').prop('disabled', false);
			});
			$('#gmedia-form').on('change', '#gmedia_url', function(){
				var val = $(this).val();
				$(this).nextAll('input.'+val).show().prop('disabled', false).siblings('input').hide().prop('disabled', true);
			});
		});
	</script>
</div>
<?php
}
