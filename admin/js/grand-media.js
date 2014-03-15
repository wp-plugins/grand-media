/*
 * jQuery functions for GRAND Flash Media
 */
var GmediaSelect;
var GmediaFunction;
jQuery(function($){
	var gmedia_DOM = $('#gmedia-container');

	GmediaSelect = {
		msg_selected: function(global){
			var gm_cb = $('.cb-media-object input'),
				qty_v = gm_cb.length,
				sel_v = gm_cb.filter(':checked').length,
				c = $('#cb_global'),
				sel = $('#gm-selected'),
				arr = sel.val().split(','),
				cur;
			if(sel_v != qty_v && sel_v != 0){
				c.css('opacity', '0.5').prop('checked', true);
			} else if(sel_v == qty_v && qty_v != 0){
				c.css('opacity', '1').prop('checked', true);
			} else if(sel_v == 0){
				c.css('opacity', '1').prop('checked', false);
			}

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

			var storedData = getStorage('gmedia_u' + sel.data('userid') + '_');
			storedData.set(sel.attr('id'), arr);
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
		chk_all: function(type){
			$('.cb-media-object input').filter(function(){
				return type? $(this).data('type') == type : true;
			}).prop('checked', true).closest('div.list-group-item').addClass('active');
		},
		chk_none: function(type){
			$('.cb-media-object input').filter(function(){
				return type? $(this).data('type') == type : true;
			}).prop('checked', false).closest('div.list-group-item').removeClass('active');
		},
		chk_toggle: function(type){
			if(type){
				if($('.cb-media-object input:checked').filter(function(){
					return $(this).data('type') == type;
				}).length){
					GmediaSelect.chk_none(type);
				} else{
					GmediaSelect.chk_all(type)
				}
			} else{
				$('.cb-media-object input').each(function(){
					$(this).prop("checked", !$(this).prop("checked")).closest('div.list-group-item').toggleClass('active');
				});
			}
		},
		init: function(){
			if($('#gm-selected').length){
				GmediaSelect.msg_selected();
				$('#gm-selected-clear').click(function(e){
					$('#gm-selected').val('');
					GmediaSelect.chk_none();
					GmediaSelect.msg_selected();
					e.preventDefault();
				});
				$('#gm-selected-show').click(function(){
					$('#gm-selected-btn').submit();
					e.preventDefault();
				});
			}
			$('#cb_global').click(function(e){
				if($(this).is(':checked')){
					GmediaSelect.chk_all();
				} else{
					GmediaSelect.chk_none();
				}
				GmediaSelect.msg_selected(true);
			});
			$('#cb_global-btn li a').click(function(e){
				var sel = $(this).data('select');
				switch(sel){
					case 'total':
						GmediaSelect.chk_all();
						break;
					case 'none':
						GmediaSelect.chk_none();
						break;
					case 'reverse':
						GmediaSelect.chk_toggle();
						break;
					case 'image':
					case 'audio':
					case 'video':
						GmediaSelect.chk_toggle(sel);
						break;
				}
				GmediaSelect.msg_selected(true);
				e.preventDefault();
			});
			$('.cb-media-object input:checkbox').change(function(){
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
				GmediaSelect.msg_selected();
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
				gmMessage('error', grandMedia.error3);
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
					action: 'gmedia_terms_modal', modal: $(this).data('action'), _wpnonce: $('#_wpnonce').val()
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
		}
	};

	GmediaSelect.init();
	GmediaFunction.init();


	if($('.grandmedia #gm-message').length){
		var messageY = $('.grandmedia #gm-message').offset().top - $('#wpadminbar').height(),
			fixed = false;
		$(window).scroll(function(e){
			if($(this).scrollTop() > messageY && !fixed){
				fixed = true;
				$('#gm-message').appendTo($('#wpadminbar')[0]);
			} else if($(this).scrollTop() < messageY && fixed){
				fixed = false;
				$('#gm-message').insertAfter($('#grandMedia .grandHeader')[0]);
			}
		});
	}

	gmedia_DOM.on('click', '.gm_toggle_checklist', function(){
		var checkBoxes = $(this).parent().find('.gm_checklist :checkbox');
		checkBoxes.each(function(){
			$(this).prop("checked", !$(this).prop("checked"));
		});
	});


	// here we declare the parameters to send along with the request
	// this means the following action hooks will be fired:
	// wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
	// action : data.action,
	// 'cookie' which contains the cookie required to authenticate you admin access to admin-ajax.php
	// 'cookie' : encodeURIComponent(document.cookie),
	// other parameters can be added along with "action"
	// _ajax_nonce : data._ajax_nonce,
	// data : postdata
	gmedia_DOM.on('click', '.ajaxPost', function(event){
		event.preventDefault();
		var arr, node, count;
		var edata = $.data(this);
		if(edata.form){
			var form = edata.form;
			arr = form.split(',');
			$.each(arr, function(i, v){
				if(v == '#selectedForm' && !$('#gmSelected').val()){
					alert($('#selectedItems').text());
					form = false;
				}
				if(!$(v).length){
					alert('#form = false');
					form = false;
				}
			});
			if(!form){
				return;
			}
			edata.form = $(form + ' :input').serialize().replace(/%5B/g, '[').replace(/%5D/g, ']');
		}
		/** @namespace edata.confirmtxt */
		if(edata.confirmtxt && !GmediaFunction.confirm(edata.confirmtxt)){
			return;
		}
		switch(edata.task){
			case 'gm-update-module':
			case 'gm-install-module':
				gmMessage('info', grandMedia.download);
				break;
			case 'gm-get-key':
				gmMessage('info', grandMedia.wait);
				break;
			case 'gmedia-update':
			case 'updateMedia':
				if(typeof($.fn.qtip) != 'undefined'){
					$(this).closest('tr').prev('tr').find('td.file img, a.fancy-listen, a.fancy-watch').qtip('destroy');
				}
				break;
		}
		//noinspection JSUnresolvedVariable,JSUnusedGlobalSymbols
		/** @namespace edata.task
		 *  @namespace msg.stat
		 *  @namespace msg.postmsg
		 *  @namespace msg.message
		 *  @namespace msg.message2
		 *  @namespace msg.files
		 *  @namespace msg.delete_source
		 *  @namespace msg2.file
		 */
		$.ajax({
			type: "POST",
			url: ajaxurl,
			data: edata,
			cache: false,
			timeout: 10000,
			success: function(msg){
				if(msg.stat && msg.message){
					gmMessage(msg.stat, msg.message);
				}
				var domel;
				switch(edata.task){
					case 'gmedia-edit':
						domel = $('tr.gmedia-edit-row');
						domel.prev().show();
						domel.remove();
						node = $(event.target).closest('tr');
						domel = $('tr', msg);
						domel.find('fieldset').append($('#gmedia-MetaBox').clone().attr('id', 'gm_metabox'));
						node.hide().after(domel);
						break;
					case 'gmedia-update':
						node = $(event.target).closest('tr');
						if(msg.stat == 'OK'){
							node.prev().replaceWith(msg.content);
							gmTableImageTip(node.prev().find('td.file img').get(0));
							gmTableActionTip(node.prev().find('a.fancy-listen, a.fancy-watch').get(0));
						} else if(msg.stat == 'KO'){
							node.prev().show();
						}
						node.remove();
						break;
					case 'gmedia-delete':
						if(msg.stat == 'OK'){
							$('#gmUpdateMessage').val(msg.postmsg).next('#gmUpdateStatus').val(msg.stat).parent('#gmUpdateContent').submit();
						}
						break;
					case 'gmedia-bulk-delete':
						if(msg.stat == 'OK'){
							arr = $('#gmSelected').val().split(',');
							node = $.map(arr, function(i){
								return document.getElementById('item_' + i);
							});
							count = node.length;
							$(node).addClass(edata.task).fadeTo('slow', '0.7', function(){
								if(!--count){
									GmediaSelect.chk_none('');
									$('#gmSelected').val('');
									GmediaSelect.msg_selected(true);
									$('#gmUpdateMessage').val(msg.postmsg).next('#gmUpdateStatus').val(msg.stat).parent('#gmUpdateContent').submit();
								}
							});
						}
						break;
					case 'term-edit':
						domel = $('tr.gmedia-edit-row');
						domel.prev().show();
						domel.remove();
						node = $(event.target).closest('tr');
						node.hide().after($('tr', msg));
						break;
					case 'term-delete':
						if(msg.stat == 'OK'){
							node = $(event.target).closest('tr');
							$(node).addClass(edata.task).fadeTo('slow', '0.7', function(){
								node.remove();
								gmMessage(msg.stat, msg.postmsg, true);
							});
						}
						break;
					case 'terms-delete':
						arr = $('#gmSelected').val().split(',');
						node = $.map(arr, function(i){
							return document.getElementById('item_' + i);
						});
						count = node.length;
						$(node).addClass(edata.task).fadeTo('slow', '0.7', function(){
							$(this).remove();
							if(!--count){
								GmediaSelect.chk_none('');
								$('#gmSelected').val('');
								GmediaSelect.msg_selected(true);
								gmMessage(msg.stat, msg.postmsg, true);
							}
						});
						break;
					case 'moveToAlbum':
					case 'gm-add-label':
					case 'gm-remove-label':
						if(msg.stat == 'OK'){
							$('#gmUpdateMessage').val(msg.postmsg).next('#gmUpdateStatus').val(msg.stat).parent('#gmUpdateContent').submit();
						}
						break;
					case 'gm-install-module':
					case 'gm-update-module':
					case 'gm-delete-module':
						if(msg.stat == 'OK'){
							$('#gmUpdateMessage').val(msg.postmsg).next('#gmUpdateStatus').val(msg.stat).parent('#gmUpdateContent').submit();
						}
						break;
					case 'hideMedia':
					case 'unhideMedia':
					case 'deleteMedia':
						arr = $('#gmSelected').val().split(',');
						node = $.map(arr, function(i){
							return document.getElementById('item_' + i);
						});
						count = node.length;
						$(node).addClass(edata.task).fadeTo('slow', '0.7', function(){
							if(!--count){
								GmediaSelect.chk_none('');
								$('#gmSelected').val('');
								GmediaSelect.msg_selected(true);
								$('#gmUpdateMessage').val(msg.postmsg).next('#gmUpdateStatus').val(msg.stat).parent('#gmUpdateContent').submit();
							}
						});
						break;
					case 'updateMedia':
						node = $(event.target).closest('tr');
						if(msg.stat == 'OK'){
							node.prev().replaceWith(msg.content);
							gmTableImageTip(node.prev().find('td.file img').get(0));
							gmTableActionTip(node.prev().find('a.fancy-listen, a.fancy-watch').get(0));
						} else if(msg.stat == 'KO'){
							node.prev().show();
						}
						node.remove();
						break;
					case 'wpmedia-edit':
						domel = $('tr.gmedia-edit-row');
						domel.prev().show();
						domel.remove();
						node = $(event.target).closest('tr');
						node.hide().after($('tr', msg));
						break;
					case 'gm-add-tab':
						$('#gMediaQuery').append(msg);
						break;
					case 'gm-tabquery-load':
						if(msg.stat == 'OK'){
							var tabqueryblock = $(event.target).closest('.tabqueryblock');
							tabqueryblock.find('.query_media_vis').html(msg.gMediaLib);
							tabqueryblock.find('.selectedItems').html(msg.gmediaCount);
						}
						break;
					case 'gm-import-folder':
						if(msg.files){
							var crunchlength = msg.files.length;
							if(crunchlength){
								var index = 0,
									crunch_file = function(index){
										$.ajax({
											type: "POST",
											url: ajaxurl,
											data: { action: 'gmDoAjax', task: 'gm-import-folder', _ajax_nonce: grandMedia.nonce, post: encodeURI('file=' + encodeURIComponent(msg.files[index]) + '&delete_source=' + msg.delete_source)},
											cache: false,
											timeout: 10000,
											async: true,
											success: function(msg2){
												index++;
												$('.msg0_progress').css({width: (100 / crunchlength * (index)) + '%'});
												if(msg2.error){
													$('<div/>').addClass('gm-message gm-error').html('<span><u><em>' + msg2.id + ':</em></u> ' + msg2.error.message + '</span>').appendTo('#import_folder .inside');
												}
												if(msg.files[index]){
													$('#gm-message').find('.crunch_file').text(msg.files[index].replace(/\\/g, '/').replace(/.*\//, ''));
													crunch_file(index);
												} else{
													gmMessage(msg.stat, msg.message2);
													$('.msg0_progress').css({width: 0});
												}
											}
										});
									};
								crunch_file(index);
							}
						}
						break;
					case 'gm-import-flagallery':
						if(msg.files){
							crunchlength = msg.files.length;
							if(crunchlength){
								index = 0;
								crunch_file = function(index){
									$.ajax({
										type: "POST",
										url: ajaxurl,
										data: { action: 'gmDoAjax', task: 'gm-import-flagallery', _ajax_nonce: grandMedia.nonce, post: $.param(msg.files[index])},
										cache: false,
										timeout: 10000,
										async: true,
										success: function(msg2){
											index++;
											$('.msg0_progress').css({width: (100 / crunchlength * (index)) + '%'});
											if(msg2.error){
												$('<div/>').addClass('gm-message gm-error').html('<span><u><em>' + msg2.id + ':</em></u> ' + msg2.error.message + '</span>').appendTo('#import_flagallery .inside');
											}
											if(msg.files[index]){
												$('#gm-message').find('.crunch_file').text(msg.files[index]['file']);
												crunch_file(index);
											} else{
												gmMessage(msg.stat, msg.message2);
												$('.msg0_progress').css({width: 0});
											}
										}
									});
								};
								crunch_file(index);
							}
						}
						break;
					case 'gm-import-nextgen':
						if(msg.files){
							crunchlength = msg.files.length;
							if(crunchlength){
								index = 0;
								crunch_file = function(index){
									$.ajax({
										type: "POST",
										url: ajaxurl,
										data: { action: 'gmDoAjax', task: 'gm-import-nextgen', _ajax_nonce: grandMedia.nonce, post: $.param(msg.files[index])},
										cache: false,
										timeout: 10000,
										async: true,
										success: function(msg2){
											index++;
											$('.msg0_progress').css({width: (100 / crunchlength * (index)) + '%'});
											if(msg2.error){
												$('<div/>').addClass('gm-message gm-error').html('<span><u><em>' + msg2.id + ':</em></u> ' + msg2.error.message + '</span>').appendTo('#import_nextgen .inside');
											}
											if(msg.files[index]){
												$('#gm-message').find('.crunch_file').text(msg.files[index]['file']);
												crunch_file(index);
											} else{
												gmMessage(msg.stat, msg.message2);
												$('.msg0_progress').css({width: 0});
											}
										}
									});
								};
								crunch_file(index);
							}
						}
						break;
					case 'gm-get-key':
						node = $(event.target).closest('.block-text');
						if(msg.error.code == 200){
							$('#gmedia_key').val(msg.key);
							$('#gmedia_key2').val(msg.key2);
							$('#product_name').val(msg.content);
							$('#gmedia_key_label span').html(': <i>' + msg.content + '</i>');
							gmMessage('info', msg.message);
							node.removeClass('block-error').addClass('block-success');
						} else if(msg.error.code == 100){
							gmMessage('error', msg.message);
							$('#gmedia_key').val('');
							$('#gmedia_key2').val('');
							$('#product_name').val('');
							$('#gmedia_key_label span').text(':');
							node.removeClass('block-success').addClass('block-error');
						} else{
							gmMessage('error', msg.error.message);
						}
						break;
				}
			},
			error: function(msg){
				gmMessage(msg.stat, msg.message);
			},
			complete: function(){
			}
		});
	});

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
			$('.gMedia-images-wrap', fieldset).on('scroll', function(){
				if(dload && ( $(this).scrollTop() >= ($(this)[0].scrollHeight - $(this).outerHeight() - 5) )){
					dload = false;
					var q = $('.gMedia-refine-input', fieldset).val();
					var jqXHR = $.get(ajaxurl, {
						_wpnonce: gMediaGlobalVar.nonce,
						action: 'gmDoAjax',
						task: 'related-image',
						paged: load_page,
						search: q,
						rel: gm_rel,
						tags: cur_tags
					},function(r){
						if(r.paged){
							$('.gMedia-images-thumbnails', fieldset).append(r.content);
							if(r.continue){
								dload = true;
								load_page = r.paged + 1;
								gm_rel = r.rel;
								$('.gMedia-images-wrap', fieldset).trigger('scroll');
							}
						}
						//console.log(r);
					}).fail(function(){
						dload = true;
						$('.gMedia-images-wrap', fieldset).trigger('scroll');
					});
				}
			});
			gm_update_metabox();
			$(this).addClass('loaded');
		}
	});

	var delayTimer;
	$('.grandmedia').on('keyup', '#gm_metabox .gMedia-refine-input',function(e){
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
					_wpnonce: gMediaGlobalVar.nonce,
					action: 'gmDoAjax',
					task: 'related-image',
					search: q
				},function(r){
					if(r.content){
						$('.gMedia-images-thumbnails', fieldset).html(r.content);
						if(r.continue){
							dload = true;
							load_page = r.paged + 1;
							gm_rel = r.rel;
							$('.gMedia-images-wrap', fieldset).trigger('scroll');
						}
					}
					//console.log(r);
				}).fail(function(){
					dload = true;
					$('.gMedia-images-wrap', fieldset).trigger('scroll');
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

	$('.grandmedia').on('click', '#gm_metabox .gMedia-control-update', function(){
		$('.gMedia-refine-input', fieldset).val('');
		gm_update_metabox();
	});

	$('.grandmedia').on('click', 'li.gMedia-image-li', function(e){
		var gm_src = $('.gmedia-thumb', this).attr('src'),
			gm_id = $('.gM-img', this).data('gmid');
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
			_wpnonce: gMediaGlobalVar.nonce,
			action: 'gmDoAjax',
			task: 'related-image',
			tags: cur_tags
		},function(r){
			if(r.content){
				$('.gMedia-images-thumbnails', fieldset).html(r.content);
				if(r.continue){
					dload = true;
					load_page = r.paged + 1;
					gm_rel = r.rel;
					$('.gMedia-images-wrap', fieldset).trigger('scroll');
				}
			}
			//console.log(r);
		}).fail(function(){
			dload = true;
			$('.gMedia-images-wrap', fieldset).trigger('scroll');
		});
	}

	/** End MetaBox */

	if(typeof($.fn.qtip) != 'undefined'){
		$('.grandmedia').on('mouseover', '[toolTip]', function(event){
			var toolTip;
			if(toolTip = $(this).attr('toolTip')){
				$(this).qtip({
					overwrite: true,
					content: {
						text: function(api){
							return toolTip;
						}
					},
					position: {
						my: 'left bottom',
						at: 'top right',
						viewport: $(window)
					},
					style: {
						classes: 'mw220'
					},
					show: {
						solo: true,
						event: event.type,
						ready: true
					},
					hide: {
						delay: 100,
						fixed: true
					}
				}, event);
			}
		});

	}

	gmTableImageTip('.gMediaLibTable td.file img');

	gmTableActionTip('a.fancy-listen, a.fancy-watch');

	if(typeof($.fn.fancybox) != 'undefined'){
		if($('.actions .fancy-view').length){
			$('.fancy-view').fancybox({
				'titleFormat': function(title, currentArray, currentIndex, currentOpts){
					title = $(currentArray[currentIndex]).parents('tr:first').find("td.title span").text();
					return (title.length? '<table cellspacing="0" cellpadding="0" id="fancybox-title-float-wrap"><tbody><tr><td id="fancybox-title-float-left"></td><td id="fancybox-title-float-main">' + title + '</td><td id="fancybox-title-float-right"></td></tr></tbody></table>' : '');
				}
			});
		}
		$('.grandbox').fancybox();
		/*
		 if($('.fancy-watch').length){
		 $('.fancy-watch').fancybox({
		 'type'	: 'iframe',
		 'padding' : 0,
		 'width' : 520,
		 'height': 304,
		 //'showNavArrows' : false,
		 'titleFormat'	: function(title, currentArray, currentIndex, currentOpts) {
		 title = $(currentArray[currentIndex]).parents('tr:first').find("td.title span").text();
		 return (title.length? '<table cellspacing="0" cellpadding="0" id="fancybox-title-float-wrap"><tbody><tr><td id="fancybox-title-float-left"></td><td id="fancybox-title-float-main">'+title+'</td><td id="fancybox-title-float-right"></td></tr></tbody></table>' : '');
		 }
		 });
		 }
		 */
	}

	if(typeof($.fn.tabs) != 'undefined'){
		if($('.gmediaSettings .ui-tabs').length){
			var reset_url = $("a.ui-tab-link").attr('href');
			var back_url = $("a.gm_add_hash").attr('href');
			var form_action = $("form#gm_module_settings_form").attr('action');
			var uitab_id = window.location.hash.replace('#', '');
			$("a.ui-tab-link").attr('href', reset_url + window.location.hash);
			$("a.gm_add_hash").attr('href', back_url + window.location.hash);
			$('form#gm_module_settings_form').attr('action', form_action + window.location.hash);
			$(".gmediaSettings .ui-tabs").tabs({
				active: uitab_id,
				activate: function(event, ui){
					var index = ui.newTab.index();
					window.location.hash = index;
					$("input[name=\'_wp_http_referer\']").val(window.location);
					$("a.ui-tab-link").attr('href', reset_url + '#' + index);
					$("a.gm_add_hash").attr('href', back_url + '#' + index);
					$("form#gm_module_settings_form").attr('action', form_action + '#' + index);
				}
			});
		}
		if($('.gmAddMedia .ui-tabs').length){
			$(".gmAddMedia .ui-tabs").tabs();
		}
	}

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

function countmedias(){
	var i = jQuery('table.gMediaLibTable tbody:visible tr.image').length;
	var a = jQuery('table.gMediaLibTable tbody:visible tr.audio').length;
	var v = jQuery('table.gMediaLibTable tbody:visible tr.video').length;
	var o = jQuery('table.gMediaLibTable tbody:visible tr.other').length;
	jQuery('.gMediaLibActions .abuts').each(function(){
		jQuery('.total .page', this).text(i + a + v + o);
		jQuery('.image .page', this).text(i);
		jQuery('.audio .page', this).text(a);
		jQuery('.video .page', this).text(v);
		jQuery('.other .page', this).text(o);
	});
}
function gmMessage(stat, message, get_ajax, append){
	if(get_ajax){
		jQuery.post(ajaxurl, { action: 'gmGetAjax', task: 'gmMessage', stat: stat, message: message }, function(response){
			if(append){
				jQuery('#gm-message').append(response);
			}
			else{
				jQuery('#gm-message').html(response);
			}
		});
	} else{
		if(append){
			jQuery('#gm-message').append(message);
		}
		else{
			jQuery('#gm-message').html(message);
		}
	}
}
function gmTableImageTip(item){
	if(typeof(jQuery.fn.qtip) != 'undefined'){
		jQuery(item).qtip({
			content: {
				text: function(api){
					var preview_thumb = '<img src="' + jQuery(this).attr('src') + '" width="150" height="150" class="gmedia-thumb" alt="' + jQuery(this).attr('alt') + '" />';
					if(jQuery(this).data('icon')){
						preview_thumb = '<div class="relative">' + preview_thumb + '<img src="' + jQuery(this).data('icon') + '" width="150" height="150" class="gmedia-thumb-icon" alt="' + jQuery(this).attr('alt') + '" /></div>';
					}
					return preview_thumb;
				},
				title: function(api){
					return '<div class="title">' + jQuery(this).attr('title') + '</div>';
				}
			},
			position: {
				my: 'left center',
				at: 'top right',
				container: jQuery('div.tooltip-file-preview'),
				//viewport : jQuery(window),
				adjust: {
					x: 10, y: 10,
					method: 'shift',
					resize: false
				}
			},
			show: {
				delay: 300,
				solo: jQuery('div.tooltip-file-preview')
			},
			hide: {
				delay: 500,
				fixed: true
			},
			style: {
				classes: 'qtip-jtools qtip-preview',
				tip: {
					corner: true
				}
			}
		});
	}
}
function gmTableActionTip(item){
	if(typeof(jQuery.fn.qtip) != 'undefined'){
		var me;
		// We make use of the .each() loop to gain access to each element via the "this" keyword...
		jQuery(item).each(function(){
			jQuery(this).qtip({
				content: ' ',
				position: {
					at: 'left center', // Position the tooltip above the link
					my: 'right center',
					container: jQuery('div.tooltip-mediaelement')
					//viewport : jQuery(window)
				},
				show: {
					event: 'click',
					solo: jQuery('div.tooltip-mediaelement') // Only show one tooltip at a time
				},
				hide: 'unfocus',
				style: {
					classes: jQuery(this).attr('class')
				},
				events: {
					render: function(event, api){
						var target = jQuery(event.originalEvent.target);
						if(target.length){
							var elsize = (target.attr('rel') == 'audio')? ' width="250" height="30"' : ' width="520" height="304"';
							api.set('content.text', '<' + target.attr('rel') + ' src="' + target.attr('href') + '" controls="controls" preload="none"' + elsize + '></' + target.attr('rel') + '>');
							me = new MediaElementPlayer(jQuery(target.attr('rel'), this), {pluginPath: gMediaGlobalVar.pluginPath + '/assets/mediaelement/'});
							//me.play();
						}
						//console.log(jQuery(this).html());
					},
					hide: function(event, api){
						me.pause();
					}
				}
			});
		})
			// Make sure it doesn't follow the link when we click it
			.click(function(event){
				event.preventDefault();
			});
	}
}

function gmedia_confirm(txt){
	var r = false;
	if(!txt){
		txt = 'Confirm...';
	}
	try{
		r = confirm(txt);
	}
	catch(err){
		//noinspection JSUnresolvedVariable
		gmMessage('error', grandMedia.error3);
	}
	return r;
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
