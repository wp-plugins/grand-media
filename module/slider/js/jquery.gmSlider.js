/*
 * Title                   : Slider Gallery Module
 * Version                 : 3.3
 * Copyright               : 2013 CodEasily.com
 * Website                 : http://www.codeasily.com
 */
if(typeof jQuery.fn.gmSlider == 'undefined'){
	function gmSlider(ID){
		return window['GmediaGallery_' + ID];
	}

	function thumb_cl(data){
		data = jQuery.parseJSON(data);
		var skin_function = jQuery('#gmSlider_ID' + data.skinId + '_Flash')[0];
		console.log(window['GmediaGallery_' + data.skinId]);

		jQuery.fancybox(
			{
				'showNavArrows': false,
				'overlayShow': true,
				'ajax': { cache: true },
				'overlayOpacity': '0.9',
				'overlayColor': '#000',
				'transitionIn': 'elastic',
				'transitionOut': 'elastic',
				'titlePosition': 'over',
				'titleFormat': function(title, currentArray, currentIndex, currentOpts){
					if(data.alttext.length || data.description.length){
						return '<div class="grand_controls" rel="gm' + data.skinId + '"><span rel="prev" class="g_prev">prev</span><span rel="show" class="g_slideshow ' + data.slideShow + '">play/pause</span><span rel="next" class="g_next">next</span></div><div id="fancybox-title-over">' + (data.alttext.length? '<strong class="title">' + decodeURIComponent(data.alttext) + '</strong>' : '') + (data.description.length? '<div class="descr">' + decodeURIComponent(data.description) + '</div>' : '') + '</div>';
					}
					else{
						return '<div class="grand_controls" rel="gm' + data.skinId + '"><span rel="prev" class="g_prev">prev</span><span rel="show" class="g_slideshow ' + data.slideShow + '">play/pause</span><span rel="next" class="g_next">next</span></div>';
					}
				},
				'href': window['GmediaGallery_' + data.skinId].settings.libraryUrl + data.filename,
				'onStart': function(){
					skin_function['gm' + data.skinId + '_fb']('active');
					jQuery('#fancybox-wrap').addClass('grand');
				},
				'onClosed': function(currentArray, currentIndex){
					skin_function['gm' + data.skinId + '_fb']('close');
					jQuery('#fancybox-wrap').removeClass('grand');
				},
				'onComplete': function(currentArray, currentIndex){
				}
			});
		jQuery('#fancybox-wrap').off('click', '.grand_controls span').on('click', '.grand_controls span', function(){
			skin_function['gm' + data.skinId + '_fb'](jQuery(this).attr('rel'));
			if(jQuery(this).hasClass('g_slideshow')){
				jQuery(this).toggleClass('play stop');
			}
		});
	}

	(function($, window, document, undefined){
		$.fn.gmSlider = function(method){
			var Container = this,
				ID = '',
				tempVar,
				flashVerion = '11',
				Content,
				ratio = 0,

				opt = {
					'linkTarget': '_self', /* lightBox */
					'property0': 'opaque' /* wmode */
				},
				int = {
					'maxwidth': 0,
					'maxheight': 0,
					'property2': 10, /* slideshowDelay */
					'property3': 14, /* navBarHeight */
					'thumbSpace': 10, /* imgSpaceH */
					'titleFontSize': 12, /* imageTitleFontSize */
					'descriptionFontSize': 11, /* imageDescrFontSize */
					'property14': 85, /* imageDescrBgAlpha */
					'postID': 0
				},
				bool = {
					'property5': true, /* autoSlideshow */
					'fancyBox': true, /* lightBox */
					'showLink': true, /* lightBox */
					'property4': true, /* descrVisOnMouseover */
					'loveLink': false,
					'swfMouseWheel': false,
					'hitcounter': false
				},
				hex = {
					'property1': 'ffffff', /* bgColor */
					'property6': '000000', /* barsBgColor */
					'property7': '75c30f', /* catButtonColor */
					'property8': 'ffffff', /* catButtonColorHover */
					'property15': '75c30f', /* imageTitleColor */
					'property16': 'ffffff', /* imageDescrColor */
					'property13': '000000', /* imageDescrBgColor */
					'backButtonColorText': 'ffffff', /* backButtonTextColor */
					'backButtonColorBg': '000000' /* backButtonBgColor */
				},

				methods = {
					init: function(arguments){// Init Plugin.
						if(arguments[1]){
							opt = $.extend(opt, int, hex, bool, arguments[1]);
						}
						$.each(opt, function(key, val){
							if(key in hex){
								opt[key] = '0x' + val;
							} else if(key in bool){
								opt[key] = (!(!val || val == '0' || val == 'false'));
							} else if(key in int){
								opt[key] = parseInt(val);
							}
						});
						ID = opt.ID;
						Content = arguments[0];
						methods.initGallery();
					},
					initGallery: function(){// Init the Gallery
						tempVar = [];
						tempVar.push('<div id="gmSlider_ID' + ID + '_Container"></div>');
						Container.html(tempVar.join(''));

						var parameters = {
								wmode: opt.property0,
								allowfullscreen: 'true',
								allowScriptAccess: 'always',
								saling: 'lt',
								scale: 'noScale',
								menu: 'false',
								bgcolor: '#' + opt.property1.slice(2)
							},
							flashvars = {
								id: ID,
								json: 'gmSlider'
							},
							attributes = {
								styleclass: 'gmSlider_Flash',
								id: 'gmSlider_ID' + ID + '_Flash'
							};
						if(opt.postID){
							flashvars.postID = opt.postID;
							flashvars.postTitle = opt.postTitle;
						}
						prototypes.swfobject_switchOffAutoHideShow();
						/** @namespace opt.ModuleUrl */
						swfobject.embedSWF(opt.moduleUrl + '/gallery.swf', 'gmSlider_ID' + ID + '_Container', '100%', '100%', flashVerion, opt.pluginUrl + '/inc/expressInstall.swf', flashvars, parameters, attributes, methods.callbackFn);

					},
					callbackFn: function(e){// e = {(bool) success, (string) id, (reference to the active HTML object element) ref}
						if(e.success){
							var swfHover = e.ref;
							$('#gmSlider_ID' + ID).on("mouseenter", e.ref,function(){
								if($.isFunction(swfHover['swfHover' + ID])){
									swfHover['swfHover' + ID]('true');
								}
							}).on("mouseleave", e.ref, function(){
								if($.isFunction(swfHover['swfHover' + ID])){
									swfHover['swfHover' + ID]('false');
								}
							});
							/*.on('mousewheel scroll DOMMouseScroll', e.ref, function (event) {
							 return false;
							 });*/

							$.each(Content, function(index){
								$.each(this.data, function(index){
									ratio = Math.max(ratio, (this.w / this.h));
								});
							});
							// set responsive gallery height
							var bars_height = opt.property3 + 10,
								responsive_size = function(){
									var w, h;
									w = Container.width();
									if(0 != opt.maxwidth){
										w = Math.min(opt.maxwidth, w);
									}
									h = Math.floor(w / ratio + bars_height);
									if((0 != opt.maxheight) && (opt.maxheight < h)){
										h = opt.maxheight;
										//w = Math.floor((h - bars_height) * ratio);
									}
									return [w, h];
								};
							var size = responsive_size();
							$('#gmSlider_ID' + ID + '_Flash', Container).css({'width': size[0], 'height': size[1]});
							$(window).resize(function(){
								size = responsive_size();
								$('#gmSlider_ID' + ID + '_Flash', Container).css({'width': size[0], 'height': size[1]});
							});
							$(window).trigger('resize');
						} else{
							methods.noFlash();
						}
					},
					noFlash: function(){
						// add html for gallery
						tempVar = [];
						if(prototypes.isTouchDevice()){
							tempVar.push('<div class="gmSlider_alternative is-touch">');
						} else{
							tempVar.push('<div class="gmSlider_alternative no-touch">');
						}
						tempVar.push('<div class="gmSlider_catLinks">');
						$.each(Content, function(index){
							tempVar.push('<a class="gm_tab" href="#' + Content[index].name + '" rel="' + Content[index].name + '">' + Content[index].title + '</a>');
						});
						tempVar.push('</div>');
						var imgobj, imgdata, img;
						$.each(Content, function(index){
							tempVar.push('<div class="gmSlider_imgContainer" tabindex="-1" id="' + Content[index].name + '">');
							$.each(this.data, function(index){
								ratio = Math.max(ratio, (this.w / this.h));
								imgdata = this;
								imgobj = new Image();
								img = $(imgobj).attr('src', opt.libraryUrl + this.filename);
								tempVar.push('<div class="gmSlider_img">');
								tempVar.push(img[0].outerHTML);
								if(opt.property4 && (this.alttext || this.description)){
									tempVar.push('<div class="gmSlider_imgDescr"><span class="gm_title">' + this.alttext + '</span>' + $("<div />").html(this.description).text() + '</div><span class="gm_close">&times;</span>');
								}
								tempVar.push('</div>');
							});
							tempVar.push('</div>');
						});
						tempVar.push('</div>');
						Container.html(tempVar.join(""));

						// set responsive gallery height
						var bars_height = $('.gmSlider_catLinks', Container).height(),
							responsive_size = function(){
								var w, h, sw;
								w = Container.width();
								if(0 != opt.maxwidth){
									w = Math.min(opt.maxwidth, w);
								}
								h = Math.floor(w / ratio + bars_height);
								if((0 != opt.maxheight) && (opt.maxheight < h)){
									h = opt.maxheight;
									sw = Math.floor((h - bars_height) * ratio) + 10;
								}
								return [w, h, sw];
							};
						var size = responsive_size();
						$('.gmSlider_alternative', Container).css({'width': size[0], 'height': size[1]});
						$('.gmSlider_img', Container).css({'width': size[2]});
						$(window).resize(function(){
							size = responsive_size();
							$('.gmSlider_alternative', Container).css({'width': size[0], 'height': size[1]});
							$('.gmSlider_img', Container).css({'width': size[2]});
						});
						$(window).trigger('resize');

						// append stylesheet to the body
						tempVar = [];
						tempVar.push('div#GmediaGallery_' + ID + ' .gmSlider_imgContainer { background-color: ' + opt.property1.replace('0x', '#') + '; }');
						var imgDescrBg = prototypes.hexToRgb(opt.property13);
						tempVar.push('div#GmediaGallery_' + ID + ' .gmSlider_imgDescr { background-color: rgba(' + imgDescrBg.r + ',' + imgDescrBg.g + ',' + imgDescrBg.b + ',' + (opt.property14 / 100) + '); color: ' + opt.property16.replace('0x', '#') + '; font-size: ' + opt.descriptionFontSize + 'px; }');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmSlider_imgDescr .gm_title { color:  ' + opt.property15.replace('0x', '#') + '; font-size: ' + opt.titleFontSize + 'px; }');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmSlider_catLinks { background-color: ' + opt.property6.replace('0x', '#') + '; overflow: auto; }');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmSlider_catLinks a { color: ' + opt.property7.replace('0x', '#') + '; }');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmSlider_catLinks a:hover, ');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmSlider_catLinks a.active, ');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmSlider_catLinks a.active:hover { color: ' + opt.property8.replace('0x', '#') + '; }');
						Container.append('<style id="gmSlider_ID' + ID + '_styles" type="text/css" scoped="scoped">' + tempVar.join("\n") + '</style>');
						tempVar = [];

						// show image description
						var event = 'dblclick';
						if(prototypes.isTouchDevice()){
							event = 'click'
						}
						$('.gmSlider_imgContainer', Container).on(event, '> div > img', function(){
							var obj = $(this).parent(),
								objDescr = $('.gmSlider_imgDescr', obj);
							if(!objDescr.length){
								return;
							}

							if(obj.hasClass('gm_info')){
								obj.removeClass('gm_info');
								objDescr.stop().animate({'bottom': obj.outerHeight()}, 200, function(){
									$('.gm_close', obj).off('click');
									$(this).css('bottom', '100%');
								});
							} else{
								objDescr.css('bottom', obj.outerHeight()).animate({'bottom': (obj.outerHeight() - $('.gmSlider_imgDescr', obj).outerHeight())}, 200, function(){
									obj.addClass('gm_info');
									$('.gm_close', obj).one('click', function(e){
										$(this).prev().stop().animate({'bottom': '100%'}, 200).parent().removeClass('gm_info');
									});
								});
							}
						});

						// show first album and load big image
						var catID = $('.gmSlider_catLinks a:first', Container).addClass('active').attr('rel');
						$('#' + catID, Container).css('display', 'block').siblings('.gmSlider_imgContainer').css('display', 'none').end().each(function(){
							var swipeWidth = $(this).width(),
								corr = parseInt($(this).children().first().css('margin-left')),
								times = Math.round($(this).scrollLeft() / swipeWidth);
							if(!times || times == 'undefined'){
								times = 0;
							}
							var curimg = $('> div:eq(' + times + ') > img', this);
							$(this).animate({scrollLeft: (curimg.parent().get(0).offsetLeft)}, 1000);
						});

						// switch between albums
						$('.gmSlider_catLinks', Container).on('click', 'a', function(e){
							e.preventDefault();
							if(!$(this).hasClass('active')){
								catID = $(this).attr('rel');
								$(this).addClass('active').siblings().removeClass('active');
								if(prototypes.isTouchDevice()){
									$('#' + catID, Container).css('display', 'block').scrollLeft(0);
								}
								$('#' + catID, Container).css('display', 'block').siblings('.gmSlider_imgContainer').css('display', 'none').end().each(function(){
									var swipeWidth = $(this).width(),
										corr = parseInt($(this).children().first().css('margin-left')),
										times = Math.round($(this).scrollLeft() / swipeWidth);
									var curimg = $('> div:eq(' + times + ') > img', this);
									$(this).scrollLeft(curimg.parent().get(0).offsetLeft);
								});
							}
						});

						methods.navigationSwipe();
					},
					navigationSwipe: function(){
						var prev, curr, touch, scrollX, positionX, initial,
							swipeFix, startTime, endTime,
							drag = false,
							swipeDiv = $('.gmSlider_imgContainer', Container),
							swipeWidth = $('.gmSlider_img', swipeDiv).width(),
							scrollWidth = swipeDiv[0].scrollWidth,
							corr = parseInt($('.gmSlider_img:first', swipeDiv).css('margin-left')),
							timer;

						if(prototypes.isTouchDevice()){
							var prevy, curry;
							swipeDiv.bind('touchstart', function(e){
								prev = curr = e.originalEvent.touches[0].clientX;
								prevy = curry = $(window).scrollTop();
								initial = $(this).scrollLeft();
								swipeWidth = $('.gmSlider_img', this).width();
								scrollWidth = $(this)[0].scrollWidth;
								startTime = (new Date()).getTime();
							});

							swipeDiv.bind('touchmove', function(e){
								curr = e.originalEvent.touches[0].clientX;
							});

							swipeDiv.bind('touchend', function(e){
								//e.preventDefault();
								curry = $(window).scrollTop();
								if((curry - prevy) != 0){
									$(this).scrollLeft(initial);
									return;
								}
								endTime = (new Date()).getTime();
								scrollX = $(this).scrollLeft();
								var swipeSpeed = Math.round((curr - prev) / (endTime - startTime) * 1000),
									times = (Math.abs(swipeSpeed) > 300)? (scrollX / swipeWidth) : Math.round(scrollX / swipeWidth);
								times = ((swipeSpeed < 0) && (scrollX < (scrollWidth - swipeWidth)))? Math.ceil(times) : Math.floor(times);
								positionX = swipeWidth * times + corr;
								$(this).animate({scrollLeft: positionX}, 120);
							});
						} else{
							swipeDiv.on('mousewheel', function(){
								var t = this;
								clearTimeout(timer);
								timer = setTimeout(function(){
									scrollX = $(t).scrollLeft();
									var times = Math.round(scrollX / swipeWidth);
									times = (scrollX < (scrollWidth - swipeWidth))? Math.ceil(times) : Math.floor(times);
									//positionX = swipeWidth * times + corr;
									positionX = $('div.gmSlider_img:eq(' + times + ')', t).get(0).offsetLeft;
									var toload = $('div.gmSlider_img:eq(' + (times + Math.max(-2, (0 - times))) + ')', t).nextUntil($('div.gmSlider_img:eq(' + (times + 3) + ')', t).get(0)).andSelf().filter('.gm_thumb');
									// ? var toload = $('div.gmSlider_img:eq('+(times + Math.max(-2, (0 - times)))+')', t).nextUntil($('div.gmSlider_img:eq('+(times+3)+')', t).get(0)).addBack().filter('.gm_thumb');
									toload.each(function(){
										var curimg = $('img', this);
										methods.preloadImage(curimg, function(url){
											curimg.attr('src', url).parent().removeClass('gm_thumb');
										});
									});
									$(t).animate({scrollLeft: positionX}, 250);
								}, 200);
							});

							swipeDiv.bind('mousedown', function(e){
								if(e.target.nodeName == 'IMG'){
									e.preventDefault();
								}
								drag = true;
								prev = curr = e.pageX;
								initial = $(this).scrollLeft();
								swipeWidth = $('.gmSlider_img', this).width();
								scrollWidth = $(this)[0].scrollWidth;
								startTime = (new Date()).getTime();
								$(this).on('mouseleave', function(){
									$(this).trigger('mouseup');
								});
							});

							swipeDiv.bind('mousemove', function(e){
								if(drag){
									e.preventDefault();
									curr = e.pageX;
									if(e.target != this){
										$(this).scrollLeft(initial + prev - curr);
									}
								}
							});

							swipeDiv.bind('mouseup', function(e){
								e.preventDefault();
								$(this).off('mouseleave');
								drag = false;
								endTime = (new Date()).getTime();
								scrollX = $(this).scrollLeft();
								var swipeSpeed = Math.round((curr - prev) / (endTime - startTime) * 1000),
									times = (Math.abs(swipeSpeed) > 600)? (scrollX / swipeWidth) : Math.round(scrollX / swipeWidth);
								times = ((swipeSpeed < 0) && (scrollX < (scrollWidth - swipeWidth)))? Math.ceil(times) : Math.floor(times);
								//positionX = swipeWidth * times + corr;
								positionX = $('div.gmSlider_img:eq(' + times + ')', this).get(0).offsetLeft;
								$(this).animate({scrollLeft: positionX}, 250);
							});

							swipeDiv.on('click', function(){
								$(this).focus();
							});

							swipeDiv.on('keydown', function(e){
								if(e.keyCode == 37 || e.keyCode == 39){
									e.preventDefault();
									scrollX = $(this).scrollLeft();
									var times = Math.round(scrollX / swipeWidth);
									if(e.keyCode == 37){
										if(times == 0){
											return;
										}
										times = times - 1;
									} else if(e.keyCode == 39){
										times = times + 1;
									}
									if(!$('div.gmSlider_img:eq(' + times + ')', this).length){
										return;
									}
									positionX = $('div.gmSlider_img:eq(' + times + ')', this).get(0).offsetLeft;
									var toload = $('div.gmSlider_img:eq(' + (times + Math.max(-2, (0 - times))) + ')', this).nextUntil($('div.gmSlider_img:eq(' + (times + 3) + ')', this).get(0)).andSelf().filter('.gm_thumb');
									toload.each(function(){
										var curimg = $('img', this);
										methods.preloadImage(curimg, function(url){
											curimg.attr('src', url).parent().removeClass('gm_thumb');
										});
									});
									$(this).stop().animate({scrollLeft: positionX}, 250);
								} else if(e.keyCode == 32){
									e.preventDefault();
									$('> div > img', this).trigger('dblclick');
								}
							});
						}
					},
					flashContent: function(){
						return {'settings': opt, 'content': Content};
					}
				},

				prototypes = {
					swfobject_switchOffAutoHideShow: function(){// SWFObject temporarily hides your SWF or alternative content until the library has decided which content to display
						if($.isFunction(swfobject.switchOffAutoHideShow)){
							swfobject.switchOffAutoHideShow();
						}
					},
					isTouchDevice: function(){// Detect Touchscreen devices
						return 'ontouchend' in document;
					},
					$_GET: function(variable){
						var url = window.location.href.split('?')[1],
							variables = url != undefined? url.split('&') : [],
							i;

						for(i = 0; i < variables.length; i++){
							if(variables[i].indexOf(variable) != -1){
								return variables[i].split('=')[1];
							}
						}

						return undefined;
					},
					hexToRgb: function(hex){
						// Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
						var shorthandRegex = /^(#|0x)?([a-f\d])([a-f\d])([a-f\d])$/i;
						hex = hex.replace(shorthandRegex, function(m, x, r, g, b){
							return r + r + g + g + b + b;
						});

						var result = /^(#|0x)?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
						return result? {
							r: parseInt(result[2], 16),
							g: parseInt(result[3], 16),
							b: parseInt(result[4], 16)
						} : null;
					}
				};

			methods.init.apply(this, arguments);
			return methods.flashContent();
		};

	})(jQuery, window, document);
}
