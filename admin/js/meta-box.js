var gmActiveEditor = false;
var gm_post_tags = '';
var dload = true, load_page = 2, gm_rel = 1;
(function ($, window, document, undefined) {
	$('body').on('click', 'textarea.wp-editor-area', function(){
		gmActiveEditor = $(this).attr('id');
		setTimeout(gm_check_scode(gmActiveEditor), 10);
	});

	$("#gMedia-tabs").gmTabs('#gMedia-source > .pane', {gmTabs: 'li'});
	$("#gMedia-wraper").resizable({
		handles  : 'e',
		start: function(event, ui) {
			$('iframe').css('pointer-events','none');
		},
		stop: function(event, ui) {
			$('iframe').css('pointer-events','auto');
			$('#gMedia-images-wrap').trigger('scroll');
		},
		resize: function( event, ui ) {
			ui.element.height('auto');
		}
	});
	$("#gMedia-images").resizable({
		handles  : 's',
		minHeight: 124,
		stop: function( event, ui ) {
			ui.element.width('auto');
			$('#gMedia-images-wrap').trigger('scroll');
		}
	});
	$("#gMedia-galleries").resizable({
		handles  : 's',
		minHeight: 94,
		start: function(event, ui) {
			$('iframe').css('pointer-events','none');
		},
		stop: function(event, ui) {
			$('iframe').css('pointer-events','auto');
			ui.element.width('auto');
		}
	});

	$('#gMedia-images').on('click', 'li.gMedia-image-li', function (e) {
		var gm_id = $(this).attr('id'),
				gm_href = $('.gM-img', this).attr('href'),
				title = $('.gM-img img', this).attr('title'),
				descr = $('.gM-img-description', this).html(),
				html = '[caption id="" align="alignnone" width="300"]<a href="' + gm_href + '"><img class="gMedia-image" id="' + gm_id + '" src="' + gm_href + '" alt="' + title + '" title="' + title + '" class="gm-image" width="300" style="height:auto;" /></a>' + descr + '[/caption]';
		gm_send_to_editor(html);
		e.preventDefault();
	});

	$('li.gMedia-gallery-li').click(function (e) {
		if ($(e.target).hasClass('gMedia-gallery-gear'))
			return true;
		var html, gm_id = $(this).attr('id').split('-')[1];
		if ($(this).hasClass('gMedia-selected')) {
			if(gm_delete_scode(gm_id)){
				/*
				html = $('textarea#'+gmActiveEditor).val();
				html = html.split('[gmedia id=' + gm_id + ']').join('');
				$('textarea#'+gmActiveEditor).val(html);
				if (tinyMCE.activeEditor) {
					tinyMCE.activeEditor.setContent(html);
				}
			*/
				$(this).removeClass('gMedia-selected');
			} else {
				// Show message "Focus textarea"
				var m = $('#gMedia-message .info-textarea');
				m.slideDown(100, function(){
					setTimeout(function(){ m.slideUp(100); }, 1000);
				});
			}
		} else {
			html = '[gmedia id=' + gm_id + ']';
			/*if (getUserSetting('editor') == 'tinymce') {
				//html = '<p><ins class="mceGMgallery" title="ID#' + gm_id + '">' + gm_id + '</ins></p>';
				html = '<p>[gmedia id=' + gm_id + ']</p>';
			}*/
			if(gm_send_to_editor(html)){
				$(this).addClass('gMedia-selected');
			} else {
				var m = $('#gMedia-message .info-textarea');
				m.slideDown(100, function(){
					setTimeout(function(){ m.slideUp(100); }, 1000);
				});
			}
		}
		e.preventDefault();
	});

	setTimeout(gm_check_scode(false), 100);

	$('textarea.wp-editor-area').on('keyup', function (e) {
		var k = e.keyCode || e.charCode;
		if (k == 8 || k == 13 || k == 46) {
			var m, content = $(this).val();
			m = content.match(/\[gmedia [ ]*id=(\d+)[ ]*?\]/g);
			jQuery('#gMedia-galleries-list li.gMedia-gallery-li').removeClass('gMedia-selected');
			if (m) {
				jQuery.each(m, function (i, shcode) {
					var id = shcode.replace(/\[gmedia [ ]*id=(\d+)[ ]*?\]/, '$1');
					jQuery('#gmModule-' + id).addClass('gMedia-selected');
				});
			}
		}
	});

	var delayTimer;
	$('#gMedia-refine-input').on('keyup', function(e){
		var k = e.keyCode || e.charCode;
		var arr = [16,17,18,20,27,33,34,35,36,37,38,39,40,144];
		if ($.inArray(k,arr) > -1) {
			return;
		}
		var q = $(this).val();
		clearTimeout(delayTimer);
		delayTimer = setTimeout(function() {
			if($.trim(q).length > 2){
				$.get(ajaxurl, {
					_wpnonce: gMediaGlobalVar.nonce,
					action: 'gmDoAjax',
					task: 'related-image',
					search: q
				}, function(r) {
					if(r.content){
						jQuery('#gMedia-images-thumbnails').html(r.content);
						if(r.continue){
							dload = true;
							load_page = r.paged + 1;
							gm_rel = r.rel;
							jQuery('#gMedia-images-wrap').trigger('scroll');
						}
					}
					//console.log(r);
				}).fail(function(){
							dload = true;
							jQuery('#gMedia-images-wrap').trigger('scroll');
						});
			} else if(q.length == 0){
				gm_update_related();
			}
		}, 1000);
		e.preventDefault();
	}).keypress(function (e) {
		if (13 == e.which) {
			e.preventDefault();
		}
	});

	if(jQuery('#tax-input-post_tag').length){
		gm_post_tags = jQuery('#tax-input-post_tag').val();
	}
	$('#gMedia-control-update').click(function(){
		$('#gMedia-refine-input').val('');
		gm_update_related();
	});
	$('#gMedia-images-wrap').on('scroll', function(){
		if( dload && ( $(this).scrollTop() >= ($(this)[0].scrollHeight - $(this).outerHeight() - 62) ) ){
			dload = false;
			var q = $('#gMedia-refine-input').val();
			var jqXHR = $.get(ajaxurl, {
				_wpnonce: gMediaGlobalVar.nonce,
				action: 'gmDoAjax',
				task: 'related-image',
				paged: load_page,
				search: q,
				rel: gm_rel,
				tags: gm_post_tags
			}, function(r) {
				if(r.paged){
					jQuery('#gMedia-images-thumbnails').append(r.content);
					if(r.continue){
						dload = true;
						load_page = r.paged + 1;
						gm_rel = r.rel;
						$('#gMedia-images-wrap').trigger('scroll');
					}
				}
				//console.log(r);
			}).fail(function(){
				dload = true;
				$('#gMedia-images-wrap').trigger('scroll');
			});
		}
	});


})(jQuery, window, document);

function gm_check_scode(id) {
	if( !id && ("undefined" != typeof(tinymce)) && tinymce.activeEditor) {
		id = tinymce.activeEditor.id;
	}
	if(!id){ id = 'content' }

	var temp_html = jQuery('textarea#'+id).val(),
			temp_m = temp_html.match(/\[gmedia [ ]*id=(\d+)[ ]*?\]/g);
	if (temp_html && temp_m) {
		jQuery.each(temp_m, function (i, shcode) {
			var id = shcode.replace(/\[gm.*id=(\d+).*?\]/, '$1');
			jQuery('#gmModule-' + id).addClass('gMedia-selected');
		});
	}
}

function gm_send_to_editor(c) {
	var b, a = typeof(tinymce) != "undefined", f = typeof(QTags) != "undefined";
	if (!wpActiveEditor) {
		if (a && tinymce.activeEditor) {
			b = tinymce.activeEditor;
			wpActiveEditor = b.id;
		} else {
			if(gmActiveEditor){
				wpActiveEditor = gmActiveEditor;
			} else {
				return false;
			}
		}
	} else {
		if (a) {
			if (tinymce.activeEditor && (tinymce.activeEditor.id == "mce_fullscreen" || tinymce.activeEditor.id == "wp_mce_fullscreen")) {
				b = tinymce.activeEditor;
			} else {
				b = tinymce.get(wpActiveEditor);
			}
		}
	}
	if (b && !b.isHidden()) {
		if (tinymce.isIE && b.windowManager.insertimagebookmark) {
			b.selection.moveToBookmark(b.windowManager.insertimagebookmark);
			//console.log(b);
		}
		if (c.indexOf("[caption") === 0) {
			if (b.plugins.wpeditimage) {
				c = b.plugins.wpeditimage._do_shcode(c)
			}
		} else {
			c = '<p>'+c+'</p>';
		}
		b.execCommand("mceInsertContent", false, c);
	} else {
		if (f) {
			QTags.insertContent(c);
		} else {
			document.getElementById(wpActiveEditor).value += c;
		}
	}
	return true;
}

function gm_delete_scode(c) {
	var b, a = typeof(tinymce) != "undefined", re, html;
	if (!wpActiveEditor) {
		if (a && tinymce.activeEditor) {
			b = tinymce.activeEditor;
			wpActiveEditor = b.id;
		} else {
			if(gmActiveEditor){
				wpActiveEditor = gmActiveEditor;
			} else {
				return false;
			}
		}
	} else {
		if (a) {
			if (tinymce.activeEditor && (tinymce.activeEditor.id == "mce_fullscreen" || tinymce.activeEditor.id == "wp_mce_fullscreen")) {
				b = tinymce.activeEditor;
			} else {
				b = tinymce.get(wpActiveEditor);
			}
		}
	}
	if (b && !b.isHidden()) {
		html = b.getContent();
		re = new RegExp("(?:<p>)?\\[gmedia \\s*id=" + c + "\\s*?\\](?:<\\/p>)?","g");
		b.setContent( html.replace(re,'') );
	} else {
		html = document.getElementById(wpActiveEditor).value;
		re = new RegExp("\\[gmedia [ ]*id=" + c + "[ ]*?\\](?:\\n\\n|\\n)?","g");
		html = html.replace(re,'');
		document.getElementById(wpActiveEditor).value = html;
	}
	return true;
}

function gm_set_featured_image(url) {
	return $.ajax({
		url     : ajaxurl,
		type    : "POST",
		cache   : false,
		dataType: "json",
		data    : {action: "gmedia_set_featured_image", post_id: $('#post_ID[name="post_ID"]').val(), image_url: url},
		success : function (r) {
			if (r) {
				if (r.error && r.error.message) {
					return new Error(r.error.message);
				} else {
					if (r.attach_id && r.html) {
						if (WPSetThumbnailID && WPSetThumbnailHTML) {
							WPSetThumbnailID(r.attach_id);
							WPSetThumbnailHTML(r.html);
						}
						return;
					}
				}
			}
			return new Error("Server error.");
		},
		error   : function (m, o, n) {
			return n;
		}
	});
}

function gm_media_button(b) {
	var pos, el = jQuery(b).toggleClass('active');
	if(el.hasClass('active')) {
		pos = el.offset();
		pos.top += el.height() + 1;
		jQuery('#gMedia-wraper').appendTo('body').css({'position':'absolute', 'z-index': 1001, 'width': jQuery('#gMedia-wraper').data('width')}).offset(pos);
		jQuery("#gMedia-wraper").draggable({ handle: "h2" });
	} else {
		jQuery('#gMedia-wraper').removeAttr('style').appendTo('#gMedia-MetaBox .inside');
		jQuery('#gMedia-wraper').draggable('destroy');
	}
}

function gm_update_related() {
	if(jQuery('#tax-input-post_tag').length){
		gm_post_tags = jQuery('#tax-input-post_tag').val();
	}
	jQuery.get(ajaxurl, {
		_wpnonce: gMediaGlobalVar.nonce,
		action: 'gmDoAjax',
		task: 'related-image',
		tags: gm_post_tags
	}, function(r) {
		if(r.content){
			jQuery('#gMedia-images-thumbnails').html(r.content);
			if(r.continue){
				dload = true;
				load_page = r.paged + 1;
				gm_rel = r.rel;
				jQuery('#gMedia-images-wrap').trigger('scroll');
			}
		}
		//console.log(r);
	}).fail(function(){
				dload = true;
				jQuery('#gMedia-images-wrap').trigger('scroll');
			});
}

