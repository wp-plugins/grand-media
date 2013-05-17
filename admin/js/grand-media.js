/*
 * jQuery functions for GRAND Flash Media
 */
var MediaLibActions;
if (typeof(play_with_page) == 'undefined')
	play_with_page = false;

(function ($) {
	MediaLibActions = {
		msg_selected: function (single) {
			var qty_v = $('table.gMediaLibTable tr:visible td.cb input').length;
			var sel_v = $('table.gMediaLibTable tr:visible td.cb input:checked').length;
			var c = $('.gMediaLibActions .cb .dropbut :checkbox');
			if (sel_v != qty_v && sel_v != 0) {
				c.css('opacity', '0.5').attr('checked', true);
			}
			else if (sel_v == qty_v && qty_v != 0) {
				c.css('opacity', '1').attr('checked', true);
			}
			else if (sel_v == 0) {
				c.css('opacity', '1').removeAttr('checked');
			}

			var arr = $('#gmSelected').val().split(',');
			arr = $.grep(arr, function (e) {
				return(e);
			});
			if (!single) {
				var cur = false;
				$("table.gMediaLibTable td.cb input").each(function () {
					cur = $(this);
					if (cur.is(':checked') && ($.inArray(cur.val(), arr) === -1)) {
						arr.push(cur.val());
					} else if (!(cur.is(':checked')) && ($.inArray(cur.val(), arr) !== -1)) {
						arr = $.grep(arr, function (e) {
							return e != cur.val();
						});
					}
				});
				$('#gmSelected').val(arr.join(','));
			}
			var storedData = getStorage('gmedia_');
			var storeKey = $('#gmSelected').attr('data-key');
			storedData.set(storeKey + '_selected_items', arr);
			var msg = $('.gMediaLibActions .msg .selectedItems').text(arr.length).parents('.msg');
			if (arr.length)
				msg.addClass('showmore').children('.more').attr('toolTip', '#' + arr.join(', #')).qtip('enable');
			else {
				msg.removeClass('showmore').children('.more').removeAttr('toolTip').qtip('disable');
			}
		},
		chk_all     : function (tr) {
			$('table.gMediaLibTable tbody:visible tr' + tr + ' td.cb :checkbox').attr('checked', true);
		},
		chk_none    : function (tr) {
			$('table.gMediaLibTable tr' + tr + ' td.cb :checkbox').removeAttr('checked');
		},
		chk_toggle  : function (tr) {
			if (tr) {
				if ($('table.gMediaLibTable tbody:visible tr' + tr + ':visible td.cb :checked').length)
					MediaLibActions.chk_none(tr);
				else
					MediaLibActions.chk_all(tr)
			} else {
				tr = '';
				$('table.gMediaLibTable tbody:visible tr' + tr + ':visible td.cb :checkbox').each(function () {
					this.checked = !this.checked
				});
			}
		},
		typedisplay : function () {
			var hid = $('table.gMediaLibTable tbody:visible tr:hidden').not('.noitems').length;
			if (hid) {
				$('.gMediaLibActions .abuts a').removeClass('active');
			} else {
				$('.gMediaLibActions .abuts a').removeClass('active');
				$('.gMediaLibActions .abuts a.total').addClass('active');
			}
		},
		init        : function () {
			if ($('#gmSelected').length) {
				MediaLibActions.msg_selected(true);
			}
			$('#clearSelected').click(function () {
				$('#gmSelected').val('');
				MediaLibActions.chk_none('');
				MediaLibActions.msg_selected(true);
			});
			$('#showSelected').click(function () {
				$('#selectedForm').submit();
			});
			$('.gMediaLibActions .doaction:checkbox').click(function (e) {
				var tr = ':visible';
				$(this).parent().parent().removeClass('active');
				if ($(this).is(':checked')) {
					MediaLibActions.chk_all(tr);
				} else {
					MediaLibActions.chk_none(tr);
				}
				MediaLibActions.msg_selected();
				$('body').trigger('click');
				e.stopPropagation();
			});
			$('.gMediaLibActions .cb .dropbox').click(function (e) {
				var sel = $(e.target).attr('class');
				var tr = '';
				switch (sel) {
					case 'total':
						MediaLibActions.chk_all(tr);
						break;
					case 'none':
						MediaLibActions.chk_none(tr);
						break;
					case 'reverse':
						MediaLibActions.chk_toggle();
						break;
					case 'image':
					case 'audio':
					case 'video':
						tr = '.' + sel;
						MediaLibActions.chk_toggle(tr);
						break;
				}
				MediaLibActions.msg_selected();
			});
			$('#gMediaLibTable td.cb :checkbox').click(function () {
				var arr = $('#gmSelected').val();
				var cur = $(this).val();
				if ($(this).is(':checked')) {
					if (arr) {
						arr = arr + ',' + cur;
					} else {
						arr = cur;
					}
				} else {
					arr = $.grep(arr.split(','),function (a) {
						return a != cur;
					}).join(',');
				}
				$('#gmSelected').val(arr);
				MediaLibActions.msg_selected(true);
			});
			$('#gMediaLibTable td.actions .delete').click(function () {
				var cur = $(this).closest('tr').find(':checkbox');
				if (cur.is(':checked')) {
					cur.removeAttr('checked');
					var arr = $('#gmSelected').val();
					cur = cur.val();
					arr = $.grep(arr.split(','),function (a) {
						return a != cur;
					}).join(',');
					$('#gmSelected').val(arr);
					MediaLibActions.msg_selected(true);
				}
			});
			var grandSearch = $('input[type="search"]');
			if (grandSearch && play_with_page) {
				grandSearch = grandSearch.quicksearch('table.gMediaLibTable tbody tr', {
					'delay'    : 100,
					'loader'   : 'span.loading',
					'bind'     : 'keyup',
					'noResults': 'tr.noitems',
					'onAfter'  : function () {
						if (grandSearch.val()) {
							grandSearch.addClass('val');
							$('.resetSearch').show();
						} else {
							grandSearch.removeClass('val');
							$('.resetSearch').hide();
						}
					}
				});
				$('.resetSearch').click(function () {
					grandSearch.val('').search('');
				});
			}
			$('#grandMedia').on('click', '.gmedia-edit-row .buttons .cancel', function (e) {
				var EditRow = $(this).parents('tr:first');
				EditRow.prev().show();
				EditRow.remove();
				e.preventDefault();
			})
		}
	};
})(jQuery);

jQuery(document).ready(function () {
	var grandMediaDOM = jQuery('#grandMedia');

	MediaLibActions.init();

	jQuery('#toplevel_page_GrandMedia').addClass('current').removeClass('wp-not-current-submenu');

	jQuery('#gm-message').click(function (event) {
		if (jQuery(event.target).hasClass('gm-close'))
			jQuery(event.target).parent('.gm-message').fadeOut(200);
	});

	jQuery('.msg').click(function () {
		jQuery('.actions', this).toggle();
		jQuery(this).one('clickoutside', function () {
			jQuery('.actions', this).hide();
		});
	});

	grandMediaDOM.on('click', '.dropbut', function () {
		jQuery(this).parent().toggleClass('active');
		jQuery(this).on('clickoutside', function (e) {
			if (!jQuery(e.target).closest('.dropchild').length) {
				jQuery(this).parent().removeClass('active');
				jQuery(this).off('clickoutside');
			}
		});
	});

	jQuery('a.gmToggle').click(function () {
		jQuery(jQuery(this).attr('href')).toggle();
		return false;
	});

	grandMediaDOM.on('click', '.gmDelTab', function () {
		jQuery(this).closest('.tabqueryblock').remove();
		return false;
	});


	jQuery('body').ajaxStart(function () {
		jQuery(this).addClass('gmDoingAjax');
		//if(!jQuery('#gMediaQuery').length)
		//jQuery('body,html').animate({ scrollTop: 0 }, 400);
	}).ajaxStop(function () {
				jQuery(this).removeClass('gmDoingAjax');
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
	grandMediaDOM.on('click', '.ajaxPost', function (event) {
		event.preventDefault();
		var arr, node, count;
		var edata = jQuery(this).dataset();
		if (edata.form) {
			var form = edata.form;
			arr = form.split(',');
			jQuery.each(arr, function (i, v) {
				if (v == '#selectedForm' && !jQuery('#gmSelected').val()) {
					alert(jQuery('#selectedItems').text());
					form = false;
				}
				if (!jQuery(v).length) {
					alert('#form = false');
					form = false;
				}
			});
			if (!form)
				return;
			edata.form = jQuery(form + ' :input').serialize().replace(/%5B/g, '[').replace(/%5D/g, ']');
		}
		/** @namespace edata.confirmtxt */
		if (edata.confirmtxt && !gmConfirm(edata.confirmtxt)) {
			return;
		}
		switch (edata.task) {
			case 'gm-update-module':
			case 'gm-install-module':
				gmMessage('info', grandMedia.download, true);
				break;
		}
		//noinspection JSUnresolvedVariable,JSUnusedGlobalSymbols
		/** @namespace edata.task
		 *  @namespace msg.stat
		 *  @namespace msg.postmsg
		 */
		jQuery.ajax({
			type    : "POST",
			url     : ajaxurl,
			data    : edata,
			cache   : false,
			timeout : 10000,
			success : function (msg) {
				gmMessage(msg.stat, msg.message);
				var domel;
				switch (edata.task) {
					case 'gmedia-edit':
						domel = jQuery('tr.gmedia-edit-row');
						domel.prev().show();
						domel.remove();
						node = jQuery(event.target).closest('tr');
						node.hide().after(jQuery('tr', msg));
						break;
					case 'gmedia-update':
						node = jQuery(event.target).closest('tr');
						if (msg.stat == 'OK') {
							node.prev().replaceWith(msg.content);
							node.remove();
						} else if (msg.stat == 'KO') {
							node.prev().show();
							node.remove();
						}
						break;
					case 'gmedia-delete':
						if (msg.stat == 'OK') {
							jQuery('#gmUpdateMessage').val(msg.postmsg).next('#gmUpdateStatus').val(msg.stat).parent('#gmUpdateContent').submit();
						}
						break;
					case 'gmedia-bulk-delete':
						if (msg.stat == 'OK') {
							arr = jQuery('#gmSelected').val().split(',');
							node = jQuery.map(arr, function (i) {
								return document.getElementById('item_' + i);
							});
							count = node.length;
							jQuery(node).addClass(edata.task).fadeTo('slow', '0.7', function () {
								if (!--count) {
									MediaLibActions.chk_none('');
									jQuery('#gmSelected').val('');
									MediaLibActions.msg_selected(true);
									jQuery('#gmUpdateMessage').val(msg.postmsg).next('#gmUpdateStatus').val(msg.stat).parent('#gmUpdateContent').submit();
								}
							});
						}
						break;
					case 'term-edit':
						domel = jQuery('tr.gmedia-edit-row');
						domel.prev().show();
						domel.remove();
						node = jQuery(event.target).closest('tr');
						node.hide().after(jQuery('tr', msg));
						break;
					case 'term-delete':
						if (msg.stat == 'OK') {
							node = jQuery(event.target).closest('tr');
							jQuery(node).addClass(edata.task).fadeTo('slow', '0.7', function () {
								node.remove();
								gmMessage(msg.stat, msg.postmsg, true);
							});
						}
						break;
					case 'terms-delete':
						arr = jQuery('#gmSelected').val().split(',');
						node = jQuery.map(arr, function (i) {
							return document.getElementById('item_' + i);
						});
						count = node.length;
						jQuery(node).addClass(edata.task).fadeTo('slow', '0.7', function () {
							jQuery(this).remove();
							if (!--count) {
								MediaLibActions.chk_none('');
								jQuery('#gmSelected').val('');
								MediaLibActions.msg_selected(true);
								gmMessage(msg.stat, msg.postmsg, true);
							}
						});
						break;
					case 'moveToCategory':
					case 'gm-add-label':
					case 'gm-remove-label':
						if (msg.stat == 'OK') {
							jQuery('#gmUpdateMessage').val(msg.postmsg).next('#gmUpdateStatus').val(msg.stat).parent('#gmUpdateContent').submit();
						}
						break;
					case 'gm-install-module':
					case 'gm-update-module':
					case 'gm-delete-module':
						if (msg.stat == 'OK') {
							jQuery('#gmUpdateMessage').val(msg.postmsg).next('#gmUpdateStatus').val(msg.stat).parent('#gmUpdateContent').submit();
						}
						break;
					case 'hideMedia':
					case 'unhideMedia':
					case 'deleteMedia':
						arr = jQuery('#gmSelected').val().split(',');
						node = jQuery.map(arr, function (i) {
							return document.getElementById('item_' + i);
						});
						count = node.length;
						jQuery(node).addClass(edata.task).fadeTo('slow', '0.7', function () {
							if (!--count) {
								MediaLibActions.chk_none('');
								jQuery('#gmSelected').val('');
								MediaLibActions.msg_selected(true);
								jQuery('#gmUpdateMessage').val(msg.postmsg).next('#gmUpdateStatus').val(msg.stat).parent('#gmUpdateContent').submit();
							}
						});
						break;
					case 'updateMedia':
						node = jQuery(event.target).closest('tr');
						if (msg.stat == 'OK') {
							node.prev().replaceWith(msg.content);
							node.remove();
						} else if (msg.stat == 'KO') {
							node.prev().show();
							node.remove();
						}
						break;
					case 'wpmedia-edit':
						domel = jQuery('tr.gmedia-edit-row');
						domel.prev().show();
						domel.remove();
						node = jQuery(event.target).closest('tr');
						node.hide().after(jQuery('tr', msg));
						break;
					case 'gm-add-tab':
						jQuery('#gMediaQuery').append(msg);
						break;
					case 'gm-tabquery-load':
						if (msg.stat == 'OK') {
							var tabqueryblock = jQuery(event.target).closest('.tabqueryblock');
							tabqueryblock.find('.query_media_vis').html(msg.gMediaLib);
							tabqueryblock.find('.selectedItems').html(msg.gmediaCount);
						}
						break;
				}
			},
			error   : function (msg) {
				gmMessage(msg.stat, msg.message);
			},
			complete: function () {
			}
		});
	});

	jQuery('.confirm').click(function () {
		return gmConfirm(jQuery(this).dataset('txt'));
	});


	if (typeof(jQuery.fn.qtip) != 'undefined') {
		/*		// Define configuration defaults
		 prerender: false,
		 id: false,
		 overwrite: true,
		 metadata: {
		 type: 'class'
		 },
		 content: {
		 text: true,
		 attr: 'title',
		 title: {
		 text: false,
		 button: false
		 }
		 },
		 position: {
		 my: 'top left',
		 at: 'bottom right',
		 target: false,
		 container: false,
		 viewport: false,
		 adjust: {
		 x: 0, y: 0,
		 mouse: true,
		 method: 'flip',
		 resize: true
		 },
		 effect: true
		 },
		 show: {
		 target: false,
		 event: 'mouseenter',
		 effect: true,
		 delay: 90,
		 solo: false,
		 ready: false,
		 modal: {
		 on: false,
		 effect: true,
		 blur: true,
		 keyboard: true
		 }
		 },
		 hide: {
		 target: false,
		 event: 'mouseleave',
		 effect: true,
		 delay: 0,
		 fixed: false,
		 inactive: false,
		 leave: 'window',
		 distance: false
		 },
		 style: {
		 classes: '',
		 widget: false,
		 tip: {
		 corner: true,
		 mimic: false,
		 method: true,
		 width: 9,
		 height: 9,
		 border: 0,
		 offset: 0
		 }
		 },
		 events: {
		 render: null,
		 move: null,
		 show: null,
		 hide: null,
		 toggle: null,
		 focus: null,
		 blur: null
		 }
		 */

		jQuery('.grandmedia').delegate('[toolTip]', 'mouseover', function (event) {
			var toolTip;
			if (toolTip = jQuery(this).attr('toolTip')) {
				jQuery(this).qtip({
					overwrite: true,
					content  : {
						text: function (api) {
							return toolTip;
						}
					},
					position : {
						my      : 'left bottom',
						at      : 'top right',
						viewport: jQuery(window)
					},
					style    : {
						classes: 'mw220'
					},
					show     : {
						solo : true,
						event: event.type,
						ready: true
					},
					hide     : {
						delay: 100,
						fixed: true
					}
				}, event);
			}
		});

		jQuery('.gMediaLibTable td.file img').qtip({
			content : {
				text : function (api) {
					return '<img src="' + jQuery(this).attr('src') + '" width="150" style="height:auto;" alt="' + jQuery(this).attr('alt') + '" />';
				},
				title: function (api) {
					return '<div class="title">' + jQuery(this).attr('title') + '</div>';
				}
			},
			position: {
				my       : 'left center',
				at       : 'top right',
				container: jQuery('div.tooltip-file-preview'),
				//viewport : jQuery(window),
				adjust   : {
					x     : 10, y: 10,
					method: 'shift',
					resize: false
				}
			},
			show    : {
				delay: 300,
				solo : jQuery('div.tooltip-file-preview')
			},
			hide    : {
				delay: 500,
				fixed: true
			},
			style   : {
				classes: 'qtip-jtools qtip-preview',
				tip    : {
					corner: true
				}
			}
		});

		jQuery('a.fancy-listen, a.fancy-watch').each(function () {
			var me;
			// We make use of the .each() loop to gain access to each element via the "this" keyword...
			jQuery(this).qtip({
				content : ' ',
				position: {
					at       : 'left center', // Position the tooltip above the link
					my       : 'right center',
					container: jQuery('div.tooltip-mediaelement')
					//viewport : jQuery(window)
				},
				show    : {
					event: 'click',
					solo : jQuery('div.tooltip-mediaelement') // Only show one tooltip at a time
				},
				hide    : 'unfocus',
				style   : {
					classes: jQuery(this).attr('class')
				},
				events: {
					render: function (event, api) {
						var target = jQuery(event.originalEvent.target);

						if(target.length) {
							var elsize = (target.attr('rel') == 'audio')? ' width="250" height="30"' : ' width="520" height="304"';
							api.set('content.text', '<'+target.attr('rel')+' src="'+target.attr('href')+'" controls="controls" preload="none"'+elsize+'></'+target.attr('rel')+'>');
							me =  new MediaElementPlayer(jQuery(target.attr('rel'), this), {pluginPath: gMediaGlobalVar.pluginPath + '/inc/mediaelement/'});
						}
						//console.log(jQuery(this).html());
					},
					hide: function (event, api) {
						me.pause();
					}
				}
			});
		})
			// Make sure it doesn't follow the link when we click it
				.click(function (event) {
					event.preventDefault();
				});

	}

	if (typeof(jQuery.fn.fancybox) != 'undefined') {
		if (jQuery('.actions .fancy-view').length) {
			jQuery('.fancy-view').fancybox({
				'titleFormat': function (title, currentArray, currentIndex, currentOpts) {
					title = jQuery(currentArray[currentIndex]).parents('tr:first').find("td.title span").text();
					return (title.length ? '<table cellspacing="0" cellpadding="0" id="fancybox-title-float-wrap"><tbody><tr><td id="fancybox-title-float-left"></td><td id="fancybox-title-float-main">' + title + '</td><td id="fancybox-title-float-right"></td></tr></tbody></table>' : '');
				}
			});
		}
		jQuery('.grandbox').fancybox();
		/*
		 if(jQuery('.fancy-watch').length){
		 jQuery('.fancy-watch').fancybox({
		 'type'	: 'iframe',
		 'padding' : 0,
		 'width' : 520,
		 'height': 304,
		 //'showNavArrows' : false,
		 'titleFormat'	: function(title, currentArray, currentIndex, currentOpts) {
		 title = jQuery(currentArray[currentIndex]).parents('tr:first').find("td.title span").text();
		 return (title.length? '<table cellspacing="0" cellpadding="0" id="fancybox-title-float-wrap"><tbody><tr><td id="fancybox-title-float-left"></td><td id="fancybox-title-float-main">'+title+'</td><td id="fancybox-title-float-right"></td></tr></tbody></table>' : '');
		 }
		 });
		 }
		 */
	}

	if (typeof(jQuery.fn.tabs) != 'undefined') {
		if (jQuery('.gmediaModuleSettings .ui-tabs').length) {
			var reset_url = jQuery("a.ui-tab-link").attr('href');
			var back_url = jQuery("a.gm_add_hash").attr('href');
			var form_action = jQuery("form#gm_module_settings_form").attr('action');
			var uitab_id = window.location.hash.replace('#', '');
			jQuery("a.ui-tab-link").attr('href', reset_url + window.location.hash);
			jQuery("a.gm_add_hash").attr('href', back_url + window.location.hash);
			jQuery('form#gm_module_settings_form').attr('action', form_action + window.location.hash);
			jQuery(".gmediaModuleSettings .ui-tabs").tabs({
				fx      : {
					opacity : "toggle",
					duration: "fast"
				},
				selected: uitab_id
			}).on("tabsselect", function (event, ui) {
						jQuery("input[name=\'_wp_http_referer\']").val(ui.tab);
						jQuery("a.ui-tab-link").attr('href', reset_url + '#' + ui.index);
						jQuery("a.gm_add_hash").attr('href', back_url + '#' + ui.index);
						jQuery("form#gm_module_settings_form").attr('action', form_action + '#' + ui.index);
						window.location.hash = ui.index;
					});
		}
	}
});
function getStorage(key_prefix) {
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
		set: function (id, data) {
			document.cookie = key_prefix + id + '=' + encodeURIComponent(data);
		},
		get: function (id, data) {
			var cookies = document.cookie, parsed = {};
			cookies.replace(/([^=]+)=([^;]*);?\s*/g, function (whole, key, value) {
				parsed[key] = decodeURIComponent(value);
			});
			return parsed[key_prefix + id];
		}
	};
	//}
}

function countmedias() {
	var i = jQuery('table.gMediaLibTable tbody:visible tr.image').length;
	var a = jQuery('table.gMediaLibTable tbody:visible tr.audio').length;
	var v = jQuery('table.gMediaLibTable tbody:visible tr.video').length;
	var o = jQuery('table.gMediaLibTable tbody:visible tr.other').length;
	jQuery('.gMediaLibActions .abuts').each(function () {
		jQuery('.total .page', this).text(i + a + v + o);
		jQuery('.image .page', this).text(i);
		jQuery('.audio .page', this).text(a);
		jQuery('.video .page', this).text(v);
		jQuery('.other .page', this).text(o);
	});
}
function gmMessage(stat, message, get_ajax) {
	if (get_ajax) {
		jQuery.post(ajaxurl, { action: 'gmGetAjax', task: 'gmMessage', stat: stat, message: message }, function (response) {
			jQuery('#gm-message').html(response);
		});
	} else {
		jQuery('#gm-message').html(message);
	}
}
function gmConfirm(txt) {
	var r = false;
	try {
		r = confirm(txt);
	}
	catch (err) {
		//noinspection JSUnresolvedVariable
		gmMessage('error', grandMedia.error3, true);
	}
	return r;
}