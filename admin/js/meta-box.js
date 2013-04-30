(function ($, window, document, undefined) {
	$(document).ready(function () {
		$("#gMedia-tabs").gmTabs('#gMedia-source > .pane', {gmTabs: 'li'});
		$("#gMedia-images").resizable({
			handles  : 's',
			minHeight: 94
		});
		$("#gMedia-galleries").resizable({
			handles  : 's',
			minHeight: 94
		});

		$('li.gMedia-image-li').click(function (e) {
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
				html = $('textarea#content').val();
				html = html.split('[gmedia id=' + gm_id + ']').join('');
				$('textarea#content').val(html);
				if (tinyMCE.activeEditor) {
					tinyMCE.activeEditor.setContent(html);
				}
				$(this).removeClass('gMedia-selected');
			} else {
				html = '<p><ins class="mceGMgallery" title="ID#' + gm_id + '">' + gm_id + '</ins></p>';
				if (getUserSetting('editor') == 'html') {
					html = '[gmedia id=' + gm_id + ']';
				}
				gm_send_to_editor(html);
				$(this).addClass('gMedia-selected');
			}
			e.preventDefault();
		});
		if (!tinyMCE.activeEditor) {
			var temp_html = $('textarea#content').val(),
					temp_m = temp_html.match(/\[gmedia id=(\d+)\]/g);
			if (temp_m) {
				jQuery.each(temp_m, function (i, shcode) {
					var id = shcode.replace(/\[gmedia id=(\d+)\]/, '$1');
					jQuery('#gmModule-' + id).addClass('gMedia-selected');
				});
			}
		}
		$('textarea#content').bind('keyup', function (e) {
			var k = e.keyCode || e.charCode;
			if (k == 8 || k == 13 || k == 46) {
				var m, content = $(this).val();
				m = content.match(/\[gmedia id=(\d+)\]/g);
				jQuery('#gMedia-galleries-list li.gMedia-gallery-li').removeClass('gMedia-selected');
				if (m) {
					jQuery.each(m, function (i, shcode) {
						var id = shcode.replace(/\[gmedia id=(\d+)\]/, '$1');
						jQuery('#gmModule-' + id).addClass('gMedia-selected');
					});
				}
			}

		});

	});

	function gm_send_to_editor(c) {
		var b, a = typeof(tinymce) != "undefined", f = typeof(QTags) != "undefined";
		if (!wpActiveEditor) {
			if (a && tinymce.activeEditor) {
				b = tinymce.activeEditor;
				wpActiveEditor = b.id;
			} else {
				wpActiveEditor = 'content';
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
				console.log(b);
			}
			if (c.indexOf("[caption") === 0) {
				if (b.plugins.wpeditimage) {
					c = b.plugins.wpeditimage._do_shcode(c)
				}
			}
			b.execCommand("mceInsertContent", false, c)
		} else {
			if (f) {
				QTags.insertContent(c);
			} else {
				document.getElementById(wpActiveEditor).value += c;
			}
		}

	}

	function set_featured_image(url) {
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

})(jQuery, window, document);
