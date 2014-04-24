/*
 * jQuery functions for GRAND Flash Media
 */
var GmediaSelect;
var GmediaFunction;
jQuery(function($){
	var gmedia_DOM = $('#gmedia-container');

	GmediaSelect = {
		msg_selected: function(obj, global){
			var gm_cb = $('.'+obj+' input'),
				qty_v = gm_cb.length,
				sel_v = gm_cb.filter(':checked').length,
				c = $('#cb_global');
			if(sel_v != qty_v && sel_v != 0){
				c.css('opacity', '0.5').prop('checked', true);
			} else if(sel_v == qty_v && qty_v != 0){
				c.css('opacity', '1').prop('checked', true);
			} else if(sel_v == 0){
				c.css('opacity', '1').prop('checked', false);
			}

			if(!$('#gm-selected').length){ return; }

			var sel = $('#gm-selected'),
				arr = sel.val().split(','),
				cur;

			arr = $.grep(arr, function(e){
				return(e);
			});
			if(global){
				cur = false;
				gm_cb.each(function(){
					cur = $(this);
					if(cur.is(':checked') && ($.inArray(cur.val(), arr) === -1)){
						arr.push(cur.val());
					} else if(!(cur.is(':checked')) && ($.inArray(cur.val(), arr) !== -1)){
						arr = $.grep(arr, function(e){
							return e != cur.val();
						});
					}
				});
				sel.val(arr.join(','));
			}

			if(sel.data('userid')){
				var storedData = getStorage('gmedia_u' + sel.data('userid') + '_');
				storedData.set(sel.data('key'), arr);
			}
			$('#gm-selected-qty').text(arr.length);
			if(arr.length){
				$('#gm-selected-btn').removeClass('hidden');
				$('.rel-selected-show').show();
				$('.rel-selected-hide').hide();
			}
			else{
				$('#gm-selected-btn').addClass('hidden');
				$('.rel-selected-show').hide();
				$('.rel-selected-hide').show();
			}
		},
		chk_all: function(type, obj){
			$('.'+obj+' input').filter(function(){
				return type? $(this).data('type') == type : true;
			}).prop('checked', true).closest('div.list-group-item').addClass('active');
		},
		chk_none: function(type, obj){
			$('.'+obj+' input').filter(function(){
				return type? $(this).data('type') == type : true;
			}).prop('checked', false).closest('div.list-group-item').removeClass('active');
		},
		chk_toggle: function(type, obj){
			if(type){
				if($('.'+obj+' input:checked').filter(function(){
					return $(this).data('type') == type;
				}).length){
					GmediaSelect.chk_none(type, obj);
				} else{
					GmediaSelect.chk_all(type, obj)
				}
			} else{
				$('.'+obj+' input').each(function(){
					$(this).prop("checked", !$(this).prop("checked")).closest('div.list-group-item').toggleClass('active');
				});
			}
		},
		init: function(){
			var cb_obj = $('#cb_global').data('group');

			if($('#gm-selected').length){
				GmediaSelect.msg_selected(cb_obj);
				$('#gm-selected-clear').click(function(e){
					$('#gm-selected').val('');
					var obj = $('#cb_global').data('group');
					GmediaSelect.chk_none(false, cb_obj);
					GmediaSelect.msg_selected(cb_obj);
					e.preventDefault();
				});
				$('#gm-selected-show').click(function(){
					$('#gm-selected-btn').submit();
					e.preventDefault();
				});
			}
			$('#cb_global').click(function(e){
				if($(this).is(':checked')){
					GmediaSelect.chk_all(false, cb_obj);
				} else{
					GmediaSelect.chk_none(false, cb_obj);
				}
				GmediaSelect.msg_selected(cb_obj, true);
			});
			$('#cb_global-btn li a').click(function(e){
				var sel = $(this).data('select');
				switch(sel){
					case 'total':
						GmediaSelect.chk_all(false, cb_obj);
						break;
					case 'none':
						GmediaSelect.chk_none(false, cb_obj);
						break;
					case 'reverse':
						GmediaSelect.chk_toggle(false, cb_obj);
						break;
					case 'image':
					case 'audio':
					case 'video':
						GmediaSelect.chk_toggle(sel, cb_obj);
						break;
				}
				GmediaSelect.msg_selected(cb_obj, true);
				e.preventDefault();
			});
			$('.cb_media-object input:checkbox, .cb_term-object input:checkbox').change(function(){
				var arr = $('#gm-selected').val();
				var cur = $(this).val();
				if($(this).is(':checked')){
					if(arr){
						arr = arr + ',' + cur;
					} else{
						arr = cur;
					}
				} else{
					arr = $.grep(arr.split(','),function(a){
						return a != cur;
					}).join(',');
				}
				$('#list-item-' + cur).toggleClass('active');
				$('#gm-selected').val(arr);
				GmediaSelect.msg_selected(cb_obj);
			});
			$('.term-label').click(function(e){
				if('DIV' == e.target.nodeName){
					if(!$('#gm-list-table').data('edit')){
						var cb = $('input:checkbox', this);
						cb.prop("checked", !cb.prop("checked")).change();
					} else{
						$('#gm-list-table').data('edit', false);
					}
				}
			});
		}
	};

	GmediaFunction = {
		confirm: function(txt){
			if(!txt){
				return true;
			}
			var r = false;
			try{
				r = confirm(txt);
			}
			catch(err){
				alert('Disable Popup Blocker');
			}
			return r;
		},
		init: function(){
			$('#toplevel_page_GrandMedia').addClass('current').removeClass('wp-not-current-submenu');

			$(document).ajaxStart(function(){
				$('body').addClass('gmedia-busy');
			}).ajaxStop(function(){
				$('body').removeClass('gmedia-busy');
			});

			$('[data-confirm]').click(function(){
				return GmediaFunction.confirm($(this).data('confirm'));
			});

			$('div.gmedia-modal').appendTo('body');
			$('a.gmedia-modal').click(function(e){
				var modal_div = $($(this).attr('href'));
				var post_data = {
					action: $(this).data('action'), modal: $(this).data('modal'), _wpnonce: $('#_wpnonce').val()
				};
				$.post(ajaxurl, post_data, function(data, textStatus, jqXHR){
					$('.modal-dialog', modal_div).html(data);
					modal_div.modal({
						//backdrop: false,
						show: true
					}).on('hidden.bs.modal', function(e){
						$('.modal-dialog', this).empty();
					});
				});
				e.preventDefault();
			});

			$('form.edit-gmedia :input').change(function(){
				var post_data = {
					action: 'gmedia_update_data', data: $(this).closest('form').serialize(), _wpnonce: $('#_wpnonce').val()
				};
				$.post(ajaxurl, post_data, function(data, textStatus, jqXHR){
					console.log(data);
				});
			});

			gmedia_DOM.on('click', '.gm-toggle-cb', function(e){
				var checkBoxes = $(this).attr('href');
				$(checkBoxes + ' :checkbox').each(function(){
					$(this).prop("checked", !$(this).prop("checked"));
				});
				e.preventDefault();
			});
			$('.linkblock').on('click', '[data-href]', function(){
				window.location.href = $(this).data('href');
			});

			$('.gmedia-import').click(function(e){
				$('#import-action').val($(this).attr('name'));
				$('#importModal').modal({
					backdrop: 'static',
					show: true
				}).on('shown.bs.modal', function(){
					$('#import_form').submit();
				}).on('hidden.bs.modal', function(){
					$('#import-done').button('reset').prop('disabled', true);
					$('#import_window').attr('src', 'about:blank');
				});
			});

			$('#gmedia_modules').on('click', '.module_install', function(e){
				e.preventDefault();
				var module = $(this).data('module');
				$('.module_install').filter('[data-module="'+module+'"]').button('loading');
				var post_data = {
					action: 'gmedia_module_install', download: $(this).attr('href'), module: module, _wpnonce: $('#_wpnonce').val()
				};
				var pathname = window.location.href;
				$.post(ajaxurl, post_data, function(data, status, xhr){
					$('#gmedia_modules').load(pathname + ' #gmedia_modules > *').before(data);
				});
			});

		}
	};

	GmediaSelect.init();
	GmediaFunction.init();






	/** MetaBox */
	var fieldset, cur_tags = '';
	var dload = true, load_page = 1, gm_rel = 1;
	$('.grandmedia').on('click', '.clear-preview', function(e){
		fieldset = $(this).closest('fieldset');
		$('.gmImage img.gmedia-thumb-preview', fieldset).remove();
		$(this).prev().val('');
	});
	$('.grandmedia').on('click', '.metabox-preview', function(e){
		fieldset = $(this).closest('fieldset');
		cur_tags = $('.gmLabels textarea', fieldset).val();
		$(this).toggleClass('active');
		$('#gm_metabox', fieldset).toggle();
		if(!$(this).hasClass('loaded')){
			$('.gmedia-images-wrap', fieldset).on('scroll', function(){
				if(dload && ( $(this).scrollTop() >= ($(this)[0].scrollHeight - $(this).outerHeight() - 5) )){
					dload = false;
					var q = $('.gmedia-refine-input', fieldset).val();
					var jqXHR = $.get(ajaxurl, {
						_wpnonce: gmediaGlobalVar.nonce,
						action: 'gmDoAjax',
						task: 'related-image',
						paged: load_page,
						search: q,
						rel: gm_rel,
						tags: cur_tags
					},function(r){
						if(r.paged){
							$('.gmedia-images-thumbnails', fieldset).append(r.content);
							if(r.continue){
								dload = true;
								load_page = r.paged + 1;
								gm_rel = r.rel;
								$('.gmedia-images-wrap', fieldset).trigger('scroll');
							}
						}
						//console.log(r);
					}).fail(function(){
						dload = true;
						$('.gmedia-images-wrap', fieldset).trigger('scroll');
					});
				}
			});
			gm_update_metabox();
			$(this).addClass('loaded');
		}
	});

	var delayTimer;
	$('.grandmedia').on('keyup', '#gm_metabox .gmedia-refine-input',function(e){
		var k = e.keyCode || e.charCode;
		var arr = [16, 17, 18, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 144];
		if($.inArray(k, arr) > -1){
			return;
		}
		var q = $(this).val();
		clearTimeout(delayTimer);
		delayTimer = setTimeout(function(){
			if($.trim(q).length > 2){
				$.get(ajaxurl, {
					_wpnonce: gmediaGlobalVar.nonce,
					action: 'gmDoAjax',
					task: 'related-image',
					search: q
				},function(r){
					if(r.content){
						$('.gmedia-images-thumbnails', fieldset).html(r.content);
						if(r.continue){
							dload = true;
							load_page = r.paged + 1;
							gm_rel = r.rel;
							$('.gmedia-images-wrap', fieldset).trigger('scroll');
						}
					}
					//console.log(r);
				}).fail(function(){
					dload = true;
					$('.gmedia-images-wrap', fieldset).trigger('scroll');
				});
			} else if(q.length == 0){
				gm_update_metabox();
			}
		}, 1000);
		e.preventDefault();
	}).keypress(function(e){
		if(13 == e.which){
			e.preventDefault();
		}
	});

	$('.grandmedia').on('click', '#gm_metabox .gmedia-control-update', function(){
		$('.gmedia-refine-input', fieldset).val('');
		gm_update_metabox();
	});

	$('.grandmedia').on('click', 'li.gmedia-image-li', function(e){
		var gm_src = $('.gmedia-thumb', this).attr('src'),
			gm_id = $('.gm-img', this).data('gmid');
		$(this).addClass('active').siblings().removeClass('active');
		$('.gmPreview input', fieldset).val(gm_id);
		if($('.gmImage img.gmedia-thumb-preview', fieldset).length){
			$('.gmImage img.gmedia-thumb-preview', fieldset).attr('src', gm_src);
		} else{
			$('.gmImage img.gmedia-thumb', fieldset).clone().removeAttr('id alt').attr({'src': gm_src, 'class': 'gmedia-thumb-preview'}).prependTo($('.gmImage', fieldset));
		}
		e.preventDefault();
	});


	function gm_update_metabox(){
		cur_tags = $('.gmLabels textarea', fieldset).val();
		$.get(ajaxurl, {
			_wpnonce: gmediaGlobalVar.nonce,
			action: 'gmDoAjax',
			task: 'related-image',
			tags: cur_tags
		},function(r){
			if(r.content){
				$('.gmedia-images-thumbnails', fieldset).html(r.content);
				if(r.continue){
					dload = true;
					load_page = r.paged + 1;
					gm_rel = r.rel;
					$('.gmedia-images-wrap', fieldset).trigger('scroll');
				}
			}
			//console.log(r);
		}).fail(function(){
			dload = true;
			$('.gmedia-images-wrap', fieldset).trigger('scroll');
		});
	}

	/** End MetaBox */


});


function getStorage(key_prefix){
	// this function will return us an object with a "set" and "get" method
	// using either localStorage if available, or defaulting to document.cookie
	/*if (window.localStorage) {
	 // use localStorage:
	 return {
	 set: function(id, data) {
	 localStorage.setItem(key_prefix+id, data);
	 },
	 get: function(id) {
	 return localStorage.getItem(key_prefix+id);
	 }
	 };
	 } else {*/
	// use document.cookie:
	return {
		set: function(id, data){
			document.cookie = key_prefix + id + '=' + encodeURIComponent(data);
		},
		get: function(id, data){
			var cookies = document.cookie, parsed = {};
			cookies.replace(/([^=]+)=([^;]*);?\s*/g, function(whole, key, value){
				parsed[key] = decodeURIComponent(value);
			});
			return parsed[key_prefix + id];
		}
	};
	//}
}

function gmHashCode(str){
	var l = str.length,
		hash = 5381 * l * (str.charCodeAt(0) + l);
	for(var i = 0; i < str.length; i++){
		hash += Math.floor((str.charCodeAt(i) + i + 0.33) / (str.charCodeAt(l - i - 1) + l) + (str.charCodeAt(i) + l) * (str.charCodeAt(l - i - 1) + i + 0.33));
	}
	return hash;
}
function gmCreateKey(site, lic, uuid){
	if(!lic){
		lic = '0:lk';
	}
	if(!uuid){
		uuid = 'xyxx-xxyx-xxxy';
	}
	var d = gmHashCode((site + ':' + lic).toLowerCase());
	var p = d;
	uuid = uuid.replace(/[xy]/g, function(c){
		var r = d % 16 | 0, v = c == 'x'? r : (r & 0x7 | 0x8);
		d = Math.floor(d * 15 / 16);
		return v.toString(16);
	});
	var key = p + ': ' + lic + '-' + uuid;
	return key.toLowerCase();
}
