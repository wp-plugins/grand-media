<?php
/**
 * @title  Image Editor
 */

function gmedia_image_editor(){
	global $gmCore;
	$gmid = $gmCore->_get('id');
	//$gmedia = $gmDB->get_gmedia($gmid);
	$gmedia_src = $gmCore->gm_get_media_image($gmid, 'original');
	$fileinfo = $gmCore->fileinfo($gmedia_src, false);
	$gmedia_thumb_src = $gmCore->gm_get_media_image($gmid, 'thumb');
	?>

	<div class="panel panel-default" id="gmedit" data-src="<?php echo $gmedia_src; ?>">
		<div class="panel-heading clearfix">
			<div class="btn-toolbar pull-right">
				<?php if(file_exists($fileinfo['filepath_original'].'_backup')){ ?>
					<button type="button" id="gmedit-restore" name="gmedit_restore" class="btn btn-warning pull-left" data-confirm="<?php _e('Do you really want restore original image?') ?>"><?php _e('Restore Original', 'gmLang'); ?></button>
				<?php } ?>
				<div class="btn-group pull-left">
					<button type="button" id="gmedit-reset" name="gmedit_reset" class="btn btn-default" data-confirm="<?php _e('Do you really want reset all changes?') ?>"><?php _e('Reset', 'gmLang'); ?></button>
					<button type="button" id="gmedit-save" name="gmedit_save" data-loading-text="<?php _e('Working', 'gmLang'); ?>" class="btn btn-primary"><?php _e('Save image', 'gmLang'); ?></button>
				</div>
				<?php wp_nonce_field('gmedit-save'); ?>
			</div>

			<div class="gmedit-tool-button gmedit-rotate left" data-toggle="tooltip" title="<?php _e('Rotate Counterclockwise', 'gmLang'); ?>"></div>
			<div class="gmedit-tool-button gmedit-rotate right" data-toggle="tooltip" title="<?php _e('Rotate Clockwise', 'gmLang'); ?>"></div>
			<div class="gmedit-tool-button gmedit-tool flip_hor" data-toggle="tooltip" data-tool="flip_hor" data-value="0" title="<?php _e('Flip Horizontal', 'gmLang'); ?>"></div>
			<div class="gmedit-tool-button gmedit-tool flip_ver" data-toggle="tooltip" data-tool="flip_ver" data-value="0" title="<?php _e('Flip Vertical', 'gmLang'); ?>"></div>
			<div class="gmedit-tool-button gmedit-tool greyscale" data-toggle="tooltip" data-tool="greyscale" data-value="0" title="<?php _e('Greyscale', 'gmLang'); ?>"></div>
			<div class="gmedit-tool-button gmedit-tool invert" data-toggle="tooltip" data-tool="invert" data-value="0" title="<?php _e('Invert', 'gmLang'); ?>"></div>

		</div>
		<div class="panel-body">
			<div class="row">
				<div class="col-xs-7 col-md-9">
					<div id="gmedit-preview">
						<div id="gmedit-canvas-cont">
							<canvas id="gmedit-canvas"></canvas>
						</div>
						<div id="gmedit-busy"></div>
					</div>
					<div id="gmedit-overlay"><span style="height:100%; width:1px; overflow:hidden;"></span><span><?php _e('Processing image', 'gmLang'); ?></span></div>
				</div>
				<div class="col-xs-5 col-md-3 media-edit-sidebar">
					<div id="media-edit-form-container">
						<div class="alert-box" style="display:none;"></div>
						<h2><?php _e('Filters', 'gmLang'); ?></h2>
						<ul id="gmedit-instruments">
							<li class="gmedit-filter">
								<h3><?php _e('Brightness', 'gmLang'); ?></h3>

								<div class="pull-right">
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="brightness" data-direction="minus">-</a>
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="brightness" data-direction="plus">+</a>
									<span class="gmedit-filter-value" id="brightnessValue">0</span>
								</div>
								<div class="gmedit-filter-edit"></div>
								<div class="gmedit-slider-noui" id="brightness_slider" data-tool="brightness"></div>
							</li>
							<li class="gmedit-filter">
								<h3><?php _e('Contrast', 'gmLang'); ?></h3>

								<div class="pull-right">
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="contrast" data-direction="minus">-</a>
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="contrast" data-direction="plus">+</a>
									<span class="gmedit-filter-value" id="contrastValue">0</span>
								</div>
								<div class="gmedit-filter-edit"></div>
								<div class="gmedit-slider-noui" id="contrast_slider" data-tool="contrast"></div>
							</li>
							<li class="gmedit-filter">
								<h3><?php _e('Saturation', 'gmLang'); ?></h3>

								<div class="pull-right">
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="saturation" data-direction="minus">-</a>
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="saturation" data-direction="plus">+</a>
									<span class="gmedit-filter-value" id="saturationValue">0</span>
								</div>
								<div class="gmedit-filter-edit"></div>
								<div class="gmedit-slider-noui" id="saturation_slider" data-tool="saturation"></div>
							</li>
							<li class="gmedit-filter">
								<h3><?php _e('Vibrance', 'gmLang'); ?></h3>

								<div class="pull-right">
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="vibrance" data-direction="minus">-</a>
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="vibrance" data-direction="plus">+</a>
									<span class="gmedit-filter-value" id="vibranceValue">0</span>
								</div>
								<div class="gmedit-filter-edit"></div>
								<div class="gmedit-slider-noui" id="vibrance_slider" data-tool="vibrance"></div>
							</li>
							<li class="gmedit-filter">
								<h3><?php _e('Exposure', 'gmLang'); ?></h3>

								<div class="pull-right">
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="exposure" data-direction="minus">-</a>
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="exposure" data-direction="plus">+</a>
									<span class="gmedit-filter-value" id="exposureValue">0</span>
								</div>
								<div class="gmedit-filter-edit"></div>
								<div class="gmedit-slider-noui" id="exposure_slider" data-tool="exposure"></div>
							</li>
							<li class="gmedit-filter">
								<h3><?php _e('Hue', 'gmLang'); ?></h3>

								<div class="pull-right">
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="hue" data-direction="minus">-</a>
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="hue" data-direction="plus">+</a>
									<span class="gmedit-filter-value" id="hueValue">0</span>
								</div>
								<div class="gmedit-filter-edit"></div>
								<div class="gmedit-slider-noui" id="hue_slider" data-tool="hue"></div>
							</li>
							<li class="gmedit-filter">
								<h3><?php _e('Sepia', 'gmLang'); ?></h3>

								<div class="pull-right">
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="sepia" data-direction="minus">-</a>
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="sepia" data-direction="plus">+</a>
									<span class="gmedit-filter-value" id="sepiaValue">0</span>
								</div>
								<div class="gmedit-filter-edit"></div>
								<div class="gmedit-slider-noui" id="sepia_slider" data-tool="sepia"></div>
							</li>
							<li class="gmedit-filter">
								<h3><?php _e('Noise', 'gmLang'); ?></h3>

								<div class="pull-right">
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="noise" data-direction="minus">-</a>
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="noise" data-direction="plus">+</a>
									<span class="gmedit-filter-value" id="noiseValue">0</span>
								</div>
								<div class="gmedit-filter-edit"></div>
								<div class="gmedit-slider-noui" id="noise_slider" data-tool="noise"></div>
							</li>
							<li class="gmedit-filter">
								<h3><?php _e('Clip', 'gmLang'); ?></h3>

								<div class="pull-right">
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="clip" data-direction="minus">-</a>
									<a href="#" class="gmedit-filter-pm text-hide" data-tool="clip" data-direction="plus">+</a>
									<span class="gmedit-filter-value" id="clipValue">0</span>
								</div>
								<div class="gmedit-filter-edit"></div>
								<div class="gmedit-slider-noui" id="clip_slider" data-tool="clip"></div>
							</li>
						</ul>
					</div>
					<div class="panel-footer form-inline">
						<div class="form-group pull-right">
							<label class="control-label"><?php _e('Apply to', 'gmLang'); ?>: &nbsp;</label>
							<select name="applyto" id="applyto" class="form-control input-sm">
								<option value="original" selected="selected"><?php _e('Original, Web-image, Thumbnail') ?></option>
								<option value="web"><?php _e('Web-image, Thumbnail') ?></option>
								<option value="thumb"><?php _e('Thumbnail') ?></option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		jQuery(function($){
			function div_frame(){
				$('.panel-body').css({top: $('.panel-heading').outerHeight()});
			}

			div_frame();
			$(window).on('resize', function(){
				div_frame();
			});
			$('.gmedit-tool-button').tooltip({placement: 'bottom'});

			var gmeditSave = function(a, b){
				$('#gmedit-save').button('loading').prop('disabled', true);
				var post_data = {
					action: 'gmedit_save', id: gmid, image: a, applyto: $('#applyto').val(), _wpnonce: $('#_wpnonce').val()
				};
				$.post(ajaxurl, post_data, function(c){
					if(!c.error){
						var parent_doc = window.parent.document;
						$('#list-item-'+gmid, parent_doc)
							.find('.gmedia-thumb').attr('src', '<?php echo $gmedia_thumb_src; ?>?' + time)
							.end().find('.modified').text(c.modified);
						$('#gmedia-panel', parent_doc).before(c.msg);
						window.parent.closeModal('gmeditModal');
					} else{
						$('#gmedit-save').button('reset').prop('disabled', false);
						$('#media-edit-form-container .alert-box').html(c.error).show();
					}
				});
			};

			var gmid = <?php echo $gmid; ?>;
			var preinit_dom = $("#gmedit").clone();
			var time = (new Date).valueOf();
			gmedit_init($("#gmedit").data("src") + "?" + time, "#gmedit", {save: gmeditSave});

			jQuery("#gmedit").on("click", "#gmedit-restore", function(){
				$('#applyto').val('original');
				$('#gmedit-save').button('loading').prop('disabled', true);
				var post_data = {
					action: 'gmedit_restore', id: gmid, _wpnonce: $('#_wpnonce').val()
				};
				$.post(ajaxurl, post_data, function(c){
					if(!c.error){
						var parent_doc = window.parent.document;
						$('#list-item-'+gmid, parent_doc)
							.find('.gmedia-thumb').attr('src', '<?php echo $gmedia_thumb_src; ?>?' + time)
							.end().find('.modified').text(c.modified);
						$('#gmedia-panel', parent_doc).before(c.msg);
						$("#gmedit").replaceWith(preinit_dom);
						gmedit_init($("#gmedit").data("src") + "?" + (new Date).valueOf(), "#gmedit", {save: gmeditSave});
						$('#media-edit-form-container .alert-box').html(c.msg).show();
						$("#gmedit-restore").remove();
					} else{
						$('#media-edit-form-container .alert-box').html(c.error).show();
					}
					$('#gmedit-save').button('reset').prop('disabled', false);
				});
			});

		});
	</script>
<?php
}
