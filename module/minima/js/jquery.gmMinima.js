/*
 * Title                   : Minima Gallery Module
 * Version                 : 1.3
 * Copyright               : 2013 CodEasily.com
 * Website                 : http://www.codeasily.com
 */
if(typeof jQuery.fn.gmMinima == 'undefined'){
	function gmMinima(ID){
		return window['GmediaGallery_' + ID];
	}

	(function($, window, document, undefined){
		$.fn.gmMinima = function(method){
			var Container = this,
				ID = '',
				tempVar,
				flashVerion = '11',
				Content,
				ratio = 0,

				opt = {
					'property0': 'opaque', /* wmode */
					'moduleUrl': '',
					'pluginUrl': ''
				},
				int = {
					'maxwidth': 0,
					'maxheight': 0,
					'slideshowDelay': 10, /* slideshowDelay */
					'thumbnailsWidth': 75, /* thumbnailsWidth */
					'thumbnailsHeight': 75, /* thumbnailsHeight */
					'descriptionBGAlpha': 75, /* imageDescrBgAlpha */
					'galleryTitleFontSize': 15, /* galleryTitleFontSize */
					'titleFontSize': 12, /* imageTitleFontSize */
					'descriptionFontSize': 11, /* imageDescrFontSize */
					'postID': 0
				},
				bool = {
					'autoSlideshow': true, /* autoSlideshow */
					'counterStatus': true, /* counterStatus */
					'hitcounter': false,
					'swfMouseWheel': false,
					'loveLink': false
				},
				hex = {
					'property1': 'ffffff', /* bgColor */
					'barBgColor': '282828', /* barsBgColor */
					'labelColor': '75c30f', /* catButtonColor */
					'labelColorOver': 'ffffff', /* catButtonColorHover */
					'backgroundColorButton': '000000', /* backgroundColorButton */
					'descriptionBGColor': '000000', /* imageDescrBgColor */
					'imageTitleColor': '75c30f', /* imageTitleColor */
					'imageDescriptionColor': 'ffffff', /* imageDescrColor */
					'linkColor': '75c30f', /* linkColor */
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
						tempVar.push('<div id="gmMinima_ID' + ID + '_Container"></div>');
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
								json: 'gmMinima'
							},
							attributes = {
								styleclass: 'gmMinima_Flash',
								id: 'gmMinima_ID' + ID + '_Flash'
							};
						if(opt.postID){
							flashvars.postID = opt.postID;
							flashvars.postTitle = opt.postTitle;
						}
						prototypes.swfobject_switchOffAutoHideShow();
						/** @namespace opt.ModuleUrl */
						swfobject.embedSWF(opt.moduleUrl + '/gallery.swf', 'gmMinima_ID' + ID + '_Container', '100%', '100%', flashVerion, opt.pluginUrl + '/inc/expressInstall.swf', flashvars, parameters, attributes, methods.callbackFn);

					},
					callbackFn: function(e){// e = {(bool) success, (string) id, (reference to the active HTML object element) ref}
						if(e.success){
							var swfHover = e.ref;
							$('#gmMinima_ID' + ID).on("mouseenter", e.ref,function(){
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
							var bars_height = opt.thumbnailsHeight + 50,
								responsive_size = function(){
									var w, h;
									w = Container.width();
									if(0 != opt.maxwidth){
										w = Math.min(opt.maxwidth, w);
									}
									h = Math.floor(w / ratio + bars_height);
									if((0 != opt.maxheight) && (opt.maxheight < h)){
										h = opt.maxheight;
										w = Math.floor((h - bars_height) * ratio);
									}
									return [w, h];
								};
							var size = responsive_size();
							$('#gmMinima_ID' + ID + '_Flash', Container).css({'width': size[0], 'height': size[1]});
							$(window).resize(function(){
								size = responsive_size();
								$('#gmMinima_ID' + ID + '_Flash', Container).css({'width': size[0], 'height': size[1]});
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
							tempVar.push('<div class="gmMinima_alternative is-touch">');
						} else{
							tempVar.push('<div class="gmMinima_alternative no-touch">');
						}
						tempVar.push('<div class="gmMinima_catLinks">');
						$.each(Content, function(index){
							tempVar.push('<a class="gm_tab" href="#' + Content[index].name + '" rel="' + Content[index].name + '">' + Content[index].title + '</a>');
						});
						tempVar.push('</div>');
						var imgobj, imgdata, img;
						$.each(Content, function(index){
							tempVar.push('<div class="gmMinima_imgContainer" tabindex="-1" id="' + Content[index].name + '">');
							$.each(this.data, function(index){
								ratio = Math.max(ratio, (this.w / this.h));
								imgdata = this;
								imgobj = new Image();
								img = $(imgobj).attr('src', opt.libraryUrl + this.thumb).attr('data-src', opt.libraryUrl + this.filename);
								tempVar.push('<div class="gmMinima_img gm_thumb">');
								tempVar.push(img[0].outerHTML);
								if(this.alttext || this.description){
									tempVar.push('<div class="gmMinima_imgDescr"><span class="gm_title">' + this.alttext + '</span>' + $("<div />").html(this.description).text() + '</div><span class="gm_close">&times;</span>');
								}
								tempVar.push('</div>');
							});
							tempVar.push('</div>');
						});
						tempVar.push('</div>');
						Container.html(tempVar.join(""));

						// set responsive gallery height
						var bars_height = $('.gmMinima_catLinks', Container).height(),
							responsive_size = function(){
								var w, h;
								w = Container.width();
								if(0 != opt.maxwidth){
									w = Math.min(opt.maxwidth, w);
								}
								h = Math.floor(w / ratio + bars_height);
								if((0 != opt.maxheight) && (opt.maxheight < h)){
									h = opt.maxheight;
									w = Math.floor((h - bars_height) * ratio);
								}
								return [w, h];
							};
						var size = responsive_size();
						$('.gmMinima_alternative', Container).css({'width': size[0], 'height': size[1]});
						$(window).resize(function(){
							size = responsive_size();
							$('.gmMinima_alternative', Container).css({'width': size[0], 'height': size[1]});
						});
						$(window).trigger('resize');

						// append stylesheet to the body
						tempVar = [];
						tempVar.push('div#GmediaGallery_' + ID + ' .gmMinima_imgContainer { background-color: ' + opt.property1.replace('0x', '#') + '; }');
						var imgDescrBg = prototypes.hexToRgb(opt.descriptionBGColor);
						tempVar.push('div#GmediaGallery_' + ID + ' .gmMinima_imgDescr { background-color: rgba(' + imgDescrBg.r + ',' + imgDescrBg.g + ',' + imgDescrBg.b + ',' + (opt.descriptionBGAlpha / 100) + '); color: ' + opt.imageDescriptionColor.replace('0x', '#') + '; font-size: ' + opt.descriptionFontSize + 'px; }');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmMinima_imgDescr .gm_title { color:  ' + opt.imageTitleColor.replace('0x', '#') + '; font-size: ' + opt.titleFontSize + 'px; }');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmMinima_catLinks { background-color: ' + opt.barBgColor.replace('0x', '#') + '; overflow: auto; }');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmMinima_catLinks a { color: ' + opt.labelColor.replace('0x', '#') + '; }');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmMinima_catLinks a:hover, ');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmMinima_catLinks a.active, ');
						tempVar.push('div#GmediaGallery_' + ID + ' .gmMinima_catLinks a.active:hover { color: ' + opt.labelColorOver.replace('0x', '#') + '; }');
						Container.append('<style id="gmMinima_ID' + ID + '_styles" type="text/css" scoped="scoped">' + tempVar.join("\n") + '</style>');
						tempVar = [];

						// show image description
						var event = 'dblclick';
						if(prototypes.isTouchDevice()){
							event = 'click'
						}
						$('.gmMinima_imgContainer', Container).on(event, '> div > img', function(){
							var obj = $(this).parent(),
								objDescr = $('.gmMinima_imgDescr', obj);
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
								objDescr.css('bottom', obj.outerHeight()).animate({'bottom': (obj.outerHeight() - $('.gmMinima_imgDescr', obj).outerHeight())}, 200, function(){
									obj.addClass('gm_info');
									$('.gm_close', obj).one('click', function(e){
										$(this).prev().stop().animate({'bottom': '100%'}, 200).parent().removeClass('gm_info');
									});
								});
							}
						});

						// show first album and load big image
						var catID = $('.gmMinima_catLinks a:first', Container).addClass('active').attr('rel');
						$('#' + catID, Container).css('display', 'block').siblings('.gmMinima_imgContainer').css('display', 'none').end().each(function(){
							var swipeWidth = $(this).width(),
								corr = parseInt($(this).children().first().css('margin-left')),
								times = Math.round($(this).scrollLeft() / swipeWidth);
							if(!times || times == 'undefined'){
								times = 0;
							}
							var curimg = $('> div:eq(' + times + ') > img', this);
							$(this).animate({scrollLeft: (curimg.parent().get(0).offsetLeft)}, 1000);
							methods.preloadImage(curimg, function(url){
								curimg.attr('src', url).parent().removeClass('gm_thumb');
							});
						});

						// switch between albums
						$('.gmMinima_catLinks', Container).on('click', 'a', function(e){
							e.preventDefault();
							if(!$(this).hasClass('active')){
								catID = $(this).attr('rel');
								$(this).addClass('active').siblings().removeClass('active');
								if(prototypes.isTouchDevice()){
									$('#' + catID, Container).css('display', 'block').scrollLeft(0);
								}
								$('#' + catID, Container).css('display', 'block').siblings('.gmMinima_imgContainer').css('display', 'none').end().each(function(){
									var swipeWidth = $(this).width(),
										corr = parseInt($(this).children().first().css('margin-left')),
										times = Math.round($(this).scrollLeft() / swipeWidth);
									var curimg = $('> div:eq(' + times + ') > img', this);
									$(this).scrollLeft(curimg.parent().get(0).offsetLeft);
									methods.preloadImage(curimg, function(url){
										curimg.attr('src', url).parent().removeClass('gm_thumb');
									});
								});
							}
						});

						methods.navigationSwipe();
					},
					navigationSwipe: function(){
						var prev, curr, touch, scrollX, positionX, initial,
							swipeFix, startTime, endTime,
							drag = false,
							swipeDiv = $('.gmMinima_imgContainer', Container),
							swipeWidth = swipeDiv.width(),
							scrollWidth = swipeDiv[0].scrollWidth,
							corr = parseInt($('.gmMinima_img:first', swipeDiv).css('margin-left')),
							timer;

						if(prototypes.isTouchDevice()){
							var prevy, curry;
							swipeDiv.bind('touchstart', function(e){
								prev = curr = e.originalEvent.touches[0].clientX;
								prevy = curry = $(window).scrollTop();
								initial = $(this).scrollLeft();
								swipeWidth = $(this).width();
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
								var toload = $('div.gmMinima_img:eq(' + (times + Math.max(-2, (0 - times))) + ')', this).nextUntil($('div.gmMinima_img:eq(' + (times + 3) + ')', this).get(0)).andSelf().filter('.gm_thumb');
								// ? var toload = $('div.gmMinima_img:eq('+(times + Math.max(-2, (0 - times)))+')', this).nextUntil($('div.gmMinima_img:eq('+(times+3)+')', this).get(0)).addBack().filter('.gm_thumb');
								toload.each(function(){
									var curimg = $('img', this);
									methods.preloadImage(curimg, function(url){
										curimg.attr('src', url).parent().removeClass('gm_thumb');
									});
								});
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
									positionX = $('div.gmMinima_img:eq(' + times + ')', t).get(0).offsetLeft;
									var toload = $('div.gmMinima_img:eq(' + (times + Math.max(-2, (0 - times))) + ')', t).nextUntil($('div.gmMinima_img:eq(' + (times + 3) + ')', t).get(0)).andSelf().filter('.gm_thumb');
									// ? var toload = $('div.gmMinima_img:eq('+(times + Math.max(-2, (0 - times)))+')', t).nextUntil($('div.gmMinima_img:eq('+(times+3)+')', t).get(0)).addBack().filter('.gm_thumb');
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
								swipeWidth = $(this).width();
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
								positionX = $('div.gmMinima_img:eq(' + times + ')', this).get(0).offsetLeft;
								var toload = $('div.gmMinima_img:eq(' + (times + Math.max(-2, (0 - times))) + ')', this).nextUntil($('div.gmMinima_img:eq(' + (times + 3) + ')', this).get(0)).andSelf().filter('.gm_thumb');
								// ? var toload = $('div.gmMinima_img:eq('+(times + Math.max(-2, (0 - times)))+')', this).nextUntil($('div.gmMinima_img:eq('+(times+3)+')', this).get(0)).addBack().filter('.gm_thumb');
								toload.each(function(){
									var curimg = $('img', this);
									methods.preloadImage(curimg, function(url){
										curimg.attr('src', url).parent().removeClass('gm_thumb');
									});
								});
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
									if(!$('div.gmMinima_img:eq(' + times + ')', this).length){
										return;
									}
									positionX = $('div.gmMinima_img:eq(' + times + ')', this).get(0).offsetLeft;
									var toload = $('div.gmMinima_img:eq(' + (times + Math.max(-2, (0 - times))) + ')', this).nextUntil($('div.gmMinima_img:eq(' + (times + 3) + ')', this).get(0)).andSelf().filter('.gm_thumb');
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
					},
					preloadImage: function(img, callback){
						var imgobj = new Image();
						imgobj.onload = function(result){
							callback(this.src);
						};
						imgobj.onabort = function(result){
							console.log(result);
							callback(img.data('src'));
						};
						imgobj.onerror = function(result){
							console.log(result);
							callback(img.data('src'));
						};
						imgobj.src = img.data('src');
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
					preloadImages: function(list, callback){
						if(typeof(list) == 'object' && typeof(callback) === 'function'){
							var callbackAfter = list.length;
							var preloadInterval = window.setInterval(function(){
								if(callbackAfter === 0){
									window.clearInterval(preloadInterval);
									callback();
								}
							}, 100);
							$.each(list, function(index, image){
								list[index] = new Image();
								list[index].onload = function(result){
									callbackAfter--;
								};
								list[index].onabort = function(result){
									console.log(result);
								};
								list[index].onerror = function(result){
									console.log(result);
								};
								/*if (!image.match('http://')) {
								 image = image;
								 }*/
								list[index].src = image;
							});
						}
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
