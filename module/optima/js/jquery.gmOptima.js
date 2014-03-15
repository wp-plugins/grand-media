/*
 * Title                   : Optima Gallery Module
 * Version                 : 2.4
 * Copyright               : 2013 CodEasily.com
 * Website                 : http://www.codeasily.com
 */
if (typeof jQuery.fn.gmOptima == 'undefined') {
	function gmOptima(ID) {
		return window['gmOptima_ID' + ID];
	}

	(function ($, window, document, undefined) {
		$.fn.gmOptima = function (method) {
			var Container = this,
					ID = '',
					tempVar,
					flashVerion = '11',
					Content,
					Music,
					Settings,
					opt,
					ratio = 0,

					defaultSettings = {
						'width'                 : '100%',
						'height'                : '500',
						'autoSlideshow'         : true,        /* autoSlideshow */
						'slideshowDelay'        : 10,          /* slideshowDelay */
						'thumbnailsWidth'       : 75,          /* thumbnailsWidth */
						'thumbnailsHeight'      : 75,          /* thumbnailsHeight */
						'property0'             : 'opaque',    /* wmode */
						'property1'             : '0xffffff',  /* bgColor */
						'descriptionShow'       : false,       /* descriptionShow */
						'showDownload'          : true,        /* showDownload */
						'counterStatus'         : true,        /* counterStatus */
						'barBgColor'            : '0x282828',  /* barsBgColor */
						'labelColor'            : '0x75c30f',  /* catButtonColor */
						'labelColorOver'        : '0xffffff',  /* catButtonColorHover */
						'backgroundColorButton' : '0x000000',  /* backgroundColorButton */
						'descriptionBGColor'    : '0x000000',  /* imageDescrBgColor */
						'descriptionBGAlpha'    : 75,          /* imageDescrBgAlpha */
						'infoButtonText'        : 'Info',      /* infoButtonText */
						'slideshowText'         : 'SLIDESHOW', /* slideshowText */
						'fullscreenText'        : 'FULLSCREEN',/* fullscreenText */
						'galleryTitleFontSize'  : 15,          /* galleryTitleFontSize */
						'imageTitleColor'       : '0x75c30f',  /* imageTitleColor */
						'titleFontSize'         : 12,          /* imageTitleFontSize */
						'imageDescriptionColor' : '0xffffff',  /* imageDescrColor */
						'descriptionFontSize'   : 11,          /* imageDescrFontSize */
						'linkColor'             : '0x75c30f',  /* linkColor */
						'backButtonColorText'   : '0xffffff',  /* backButtonTextColor */
						'backButtonColorBg'     : '0x000000',  /* backButtonBgColor */
						'hitcounter'            : true,
						'swfMouseWheel'         : false,
						'loveLink'              : false,
						'postID'                : 0,

						'moduleUrl'   : '',
						'pluginUrl'   : ''
					},

					methods = {
						init         : function (options) {// Init Plugin.
							return this.each(function () {
								Settings = methods.parseSettings();
								opt = $.extend(defaultSettings, Settings);
								if (options) {
									opt = $.extend(opt, options);
								}
								Content = methods.parseContent('Content');
								Music = methods.parseContent('Music');
								methods.crunching();
							});
						},
						parseSettings: function () {// Parse Settings.
							ID = $(Container).attr('id').split('_ID')[1];
							if (typeof(window['gmOptima_ID' + ID + '_Settings']) === 'object')
								Settings = window['gmOptima_ID' + ID + '_Settings'];
							else
								Settings = {};
							return Settings;
						},
						parseContent: function (obj) {// Parse Content.
							if (typeof(window['gmOptima_ID' + ID + '_' + obj]) === 'object')
								obj = window['gmOptima_ID' + ID + '_' + obj];
							else
								obj = [];
							return obj;
						},
						crunching		 : function () {// create new thumbs if not exists
							if (typeof(window['gmOptima_ID' + ID + '_Crunch']) === 'object') {
								var Crunch = window['gmOptima_ID' + ID + '_Crunch'];
								var crunchlength = Crunch.length;
								if(crunchlength) {
									tempVar = [];
									tempVar.push('<div id="gmOptima_ID' + ID + '_ProgressBar" class="gmOptima_ProgressBar"><div class="gmProgress"><span class="gmBar"></span><div class="gmCounter"><span class="gmCount">0</span>/'+crunchlength+'</div></div></div>');
									Container.html(tempVar.join(''));
									var index = 0,
											crunch_image = function(index){
												$.ajax({
													type    : "POST",
													url     : ajaxurl,
													data    : { action: 'gmedia_crunching', args: Crunch[index]},
													cache   : false,
													timeout : 10000,
													async 	: true,
													success : function (msg) {
														index++;
														$('#gmOptima_ID' + ID + '_ProgressBar .gmBar', Container).animate({width: (100/crunchlength*(index))+'%'}, 500, function(){
															$(this).parent().find('.gmCount').text(index);
															if(!Crunch[index]) {
																methods.initGallery();
															}
														});
														if(Crunch[index]) {
															crunch_image(index);
														}
													}
												});
											};
									crunch_image(index);
								} else {
									methods.initGallery();
								}
							} else {
								methods.initGallery();
							}
						},
						initGallery  : function () {// Init the Gallery
							tempVar = [];
							tempVar.push('<div id="gmOptima_ID' + ID + '_Container"></div>');
							Container.html(tempVar.join(''));

							var parameters = {
										wmode            : opt.property0,
										allowfullscreen  : 'true',
										allowScriptAccess: 'always',
										saling           : 'lt',
										scale            : 'noScale',
										menu             : 'false',
										bgcolor          : '#' + opt.property1.slice(2)
									},
									flashvars = {
										id  : ID,
										json: 'gmOptima'
									},
									attributes = {
										styleclass: 'gmOptima_Flash',
										id        : 'gmOptima_ID' + ID + '_Flash'
									};
							if (opt.postID) {
								flashvars.postID = opt.postID;
								flashvars.postTitle = opt.postTitle;
							}
							prototypes.swfobject_switchOffAutoHideShow();
							/** @namespace opt.ModuleUrl */
							swfobject.embedSWF(opt.moduleUrl + '/gallery.swf', 'gmOptima_ID' + ID + '_Container', opt.width, opt.height, flashVerion, opt.pluginUrl + '/inc/expressInstall.swf', flashvars, parameters, attributes, methods.callbackFn);

						},
						callbackFn   : function (e) {// e = {(bool) success, (string) id, (reference to the active HTML object element) ref}
							if (e.success) {
								var swfHover = e.ref;
								$('#gmOptima_ID' + ID).on("mouseenter", e.ref,function () {
									if($.isFunction(swfHover['swfHover'+ID])) {
										swfHover['swfHover'+ID]('true');
									}
								}).on("mouseleave", e.ref,function () {
									if($.isFunction(swfHover['swfHover'+ID])) {
										swfHover['swfHover'+ID]('false');
									}
								}); /*.on('mousewheel scroll DOMMouseScroll', e.ref, function (event) {
									return false;
								});*/
							} else {
								methods.noFlash();
							}
						},
						noFlash: function () {
							// add html for gallery
							tempVar = [];
							if(prototypes.isTouchDevice())	{
								tempVar.push('<div class="gmOptima_alternative is-touch">');
							} else {
								tempVar.push('<div class="gmOptima_alternative no-touch">');
							}
							tempVar.push('<div class="gmOptima_catLinks">');
							$.each(Content, function (index) {
								tempVar.push('<a class="gm_tab" href="#' + Content[index].name + '" rel="' + Content[index].name + '">' + Content[index].title + '</a>');
							});
							tempVar.push('</div>');
							var imgobj, imgdata, img;
							$.each(Content, function (index) {
								tempVar.push('<div class="gmOptima_imgContainer" id="' + Content[index].name + '">');
								$.each(this.data, function (index) {
									ratio = Math.max(ratio, (this.w/this.h));
									imgdata = this;
									imgobj = new Image();
									img = $(imgobj).attr('src', opt.libraryUrl + this.thumb).attr('data-src', opt.libraryUrl + this.filename);
									tempVar.push('<div class="gmOptima_img gm_thumb">');
									tempVar.push(img[0].outerHTML);
									if(this.alttext || this.description) {
										tempVar.push('<div class="gmOptima_imgDescr"><span class="gm_title">' + this.alttext + '</span>' + $("<div />").html(this.description).text() + '</div><span class="gm_close">&times;</span>');
									}
									tempVar.push('</div>');
								});
								tempVar.push('</div>');
							});
							tempVar.push('</div>');
							Container.html(tempVar.join(""));

							// set responsive gallery height
							var bars_height = $('.gmOptima_catLinks', Container).height(),
									responsive_height = function() { return (opt.width == '100%') ? Math.floor(Container.width() / ratio + bars_height) : Math.floor(Container.width() / (opt.width/opt.height) + bars_height); };
							$('.gmOptima_alternative', Container).css({'height': responsive_height});
							$(window).resize(function(){
								$('.gmOptima_alternative', Container).css({'height': responsive_height});
							});

							// append stylesheet to the body
							tempVar = [];
							tempVar.push('div#gmOptima_ID'+ID+' .gmOptima_imgContainer { background-color: '+ opt.property1.replace('0x','#') +'; }');
							var imgDescrBg = prototypes.hexToRgb(opt.descriptionBGColor);
							tempVar.push('div#gmOptima_ID'+ID+' .gmOptima_imgDescr { background-color: rgba('+imgDescrBg.r+','+imgDescrBg.g+','+imgDescrBg.b+','+(opt.descriptionBGAlpha/100)+'); color: '+ opt.imageDescriptionColor.replace('0x','#') +'; font-size: '+ opt.descriptionFontSize +'px; }');
							tempVar.push('div#gmOptima_ID'+ID+' .gmOptima_imgDescr .gm_title { color:  '+ opt.imageTitleColor.replace('0x','#') +'; font-size: '+ opt.titleFontSize +'px; }');
							tempVar.push('div#gmOptima_ID'+ID+' .gmOptima_catLinks { background-color: '+ opt.barBgColor.replace('0x','#') +'; overflow: auto; }');
							tempVar.push('div#gmOptima_ID'+ID+' .gmOptima_catLinks a { color: '+ opt.labelColor.replace('0x','#') +'; }');
							tempVar.push('div#gmOptima_ID'+ID+' .gmOptima_catLinks a:hover, ');
							tempVar.push('div#gmOptima_ID'+ID+' .gmOptima_catLinks a.active, ');
							tempVar.push('div#gmOptima_ID'+ID+' .gmOptima_catLinks a.active:hover { color: '+ opt.labelColorOver.replace('0x','#') +'; }');
							Container.append('<style id="gmOptima_ID'+ID+'_styles" type="text/css" scoped="scoped">' + tempVar.join("\n") + '</style>');
							tempVar = [];

							// show image description
							var event = 'dblclick';
							if(prototypes.isTouchDevice()) {
								event = 'click'
							}
							$('.gmOptima_imgContainer', Container).on(event, '> div > img', function(){
								var obj = $(this).parent(),
										objDescr = $('.gmOptima_imgDescr', obj);
								if(!objDescr.length)
									return;

								if(obj.hasClass('gm_info')) {
									obj.removeClass('gm_info');
									objDescr.stop().animate({'bottom': obj.outerHeight()}, 200, function(){
										$('.gm_close', obj).off('click');
										$(this).css('bottom','100%');
									});
								} else {
									objDescr.css('bottom',obj.outerHeight()).animate({'bottom': (obj.outerHeight() - $('.gmOptima_imgDescr', obj).outerHeight())}, 200, function(){
										obj.addClass('gm_info');
										$('.gm_close', obj).one('click', function(e){
											$(this).prev().stop().animate({'bottom': '100%'}, 200).parent().removeClass('gm_info');
										});
									});
								}
							});

							// show first album and load big image
							var catID = $('.gmOptima_catLinks a:first', Container).addClass('active').attr('rel');
							$('#' + catID, Container).css('display','block').siblings('.gmOptima_imgContainer').css('display','none').end().each(function(){
								var swipeWidth = $(this).width(),
										corr = parseInt($(this).children().first().css('margin-left')),
										times = Math.round($(this).scrollLeft()/swipeWidth);
								var curimg = $('> div:eq('+times+') > img',this);
								$(this).animate({scrollLeft: (curimg.parent().offsetLeft)}, 1000);
								methods.preloadImage(curimg, function(url){
									curimg.attr('src', url).parent().removeClass('gm_thumb');
								});
							});

							// switch between albums
							$('.gmOptima_catLinks', Container).on('click', 'a', function(e){
								e.preventDefault();
								if(!$(this).hasClass('active')) {
									catID = $(this).attr('rel');
									$(this).addClass('active').siblings().removeClass('active');
									if(prototypes.isTouchDevice())	{
										$('#' + catID, Container).css('display','block').scrollLeft(0);
									}
									$('#' + catID, Container).css('display','block').siblings('.gmOptima_imgContainer').css('display','none').end().each(function(){
										var swipeWidth = $(this).width(),
												corr = parseInt($(this).children().first().css('margin-left')),
												times = Math.round($(this).scrollLeft()/swipeWidth);
										var curimg = $('> div:eq('+times+') > img',this);
										$(this).scrollLeft(curimg.parent().offsetLeft);
										methods.preloadImage(curimg, function(url){
											curimg.attr('src', url).parent().removeClass('gm_thumb');
										});
									});
								}
							});

							methods.navigationSwipe();
						},
						navigationSwipe: function () {
							var prev, curr, touch, scrollX, positionX, initial,
									swipeWidth, swipeFix, startTime, endTime,
									scrollWidth, drag = false,
									swipeDiv = $('.gmOptima_imgContainer', Container),
									corr = parseInt($('.gmOptima_img:first', swipeDiv).css('margin-left'));

							if(prototypes.isTouchDevice())	{
								var prevy, curry;
								swipeDiv.bind('touchstart', function (e) {
									prev = curr = e.originalEvent.touches[0].clientX;
									prevy = curry = $(window).scrollTop();
									initial = $(this).scrollLeft();
									swipeWidth = $(this).width();
									scrollWidth = $(this)[0].scrollWidth;
									startTime = (new Date()).getTime();
								});

								swipeDiv.bind('touchmove', function (e) {
									curr = e.originalEvent.touches[0].clientX;
								});

								swipeDiv.bind('touchend', function (e) {
									//e.preventDefault();
									curry = $(window).scrollTop();
									if((curry - prevy) != 0) {
										$(this).scrollLeft(initial);
										return;
									}
									endTime = (new Date()).getTime();
									scrollX = $(this).scrollLeft();
									var swipeSpeed = Math.round((curr-prev)/(endTime-startTime)*1000),
											times = (Math.abs(swipeSpeed) > 300) ? (scrollX/swipeWidth) : Math.round(scrollX/swipeWidth);
									times = ((swipeSpeed < 0) && (scrollX < (scrollWidth - swipeWidth))) ? Math.ceil(times) : Math.floor(times);
									positionX = swipeWidth * times + corr;
									var toload = $('div.gmOptima_img:eq('+(times + Math.max(-2, (0 - times)))+')', this).nextUntil($('div.gmOptima_img:eq('+(times+3)+')', this).get(0)).andSelf().filter('.gm_thumb');
									// ? var toload = $('div.gmOptima_img:eq('+(times + Math.max(-2, (0 - times)))+')', this).nextUntil($('div.gmOptima_img:eq('+(times+3)+')', this).get(0)).addBack().filter('.gm_thumb');
									toload.each(function(){
										var curimg = $('img',this);
										methods.preloadImage(curimg, function(url){
											curimg.attr('src', url).parent().removeClass('gm_thumb');
										});
									});
									$(this).animate({scrollLeft: positionX}, 120);
								});
							} else {
								swipeDiv.bind('mousedown', function (e) {
									if(e.target.nodeName == 'IMG')
										e.preventDefault();
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

								swipeDiv.bind('mousemove', function (e) {
									if(drag){
										e.preventDefault();
										curr = e.pageX;
										if(e.target != this) {
											$(this).scrollLeft(initial + prev - curr);
										}
									}
								});

								swipeDiv.bind('mouseup', function (e) {
									e.preventDefault();
									$(this).off('mouseleave');
									drag = false;
									endTime = (new Date()).getTime();
									scrollX = $(this).scrollLeft();
									var swipeSpeed = Math.round((curr-prev)/(endTime-startTime)*1000),
											times = (Math.abs(swipeSpeed) > 600) ? (scrollX/swipeWidth) : Math.round(scrollX/swipeWidth);
											times = ((swipeSpeed < 0) && (scrollX < (scrollWidth - swipeWidth))) ? Math.ceil(times) : Math.floor(times);
									//positionX = swipeWidth * times + corr;
									positionX = $('div.gmOptima_img:eq('+times+')', this).get(0).offsetLeft;
									var toload = $('div.gmOptima_img:eq('+(times + Math.max(-2, (0 - times)))+')', this).nextUntil($('div.gmOptima_img:eq('+(times+3)+')', this).get(0)).andSelf().filter('.gm_thumb');
									// ? var toload = $('div.gmOptima_img:eq('+(times + Math.max(-2, (0 - times)))+')', this).nextUntil($('div.gmOptima_img:eq('+(times+3)+')', this).get(0)).addBack().filter('.gm_thumb');
									toload.each(function(){
										var curimg = $('img',this);
										methods.preloadImage(curimg, function(url){
											curimg.attr('src', url).parent().removeClass('gm_thumb');
										});
									});
									$(this).animate({scrollLeft: positionX}, 250);
								});
							}
						},
						flashContent : function () {
							return {'settings': opt, 'content': Content, 'sound': Music};
						},
						preloadImage 									: function(img, callback) {
							var imgobj = new Image();
							imgobj.onload = function(result) {
								callback(this.src);
							};
							imgobj.onabort = function(result) {
								console.log(result);
								callback(img.data('src'));
							};
							imgobj.onerror = function(result) {
								console.log(result);
								callback(img.data('src'));
							};
							imgobj.src = img.data('src');
						}
					},

					prototypes = {
						swfobject_switchOffAutoHideShow: function () {// SWFObject temporarily hides your SWF or alternative content until the library has decided which content to display
							if ($.isFunction(swfobject.switchOffAutoHideShow)) {
								swfobject.switchOffAutoHideShow();
							}
						},
						isTouchDevice                  : function () {// Detect Touchscreen devices
							return 'ontouchend' in document;
						},
						$_GET                          : function (variable) {
							var url = window.location.href.split('?')[1],
									variables = url != undefined ? url.split('&') : [],
									i;

							for (i = 0; i < variables.length; i++) {
								if (variables[i].indexOf(variable) != -1) {
									return variables[i].split('=')[1];
								}
							}

							return undefined;
						},
						preloadImages 									: function(list, callback) {
							if (typeof(list) == 'object' && typeof(callback) === 'function') {
								var callbackAfter = list.length;
								var preloadInterval = window.setInterval(function() {
									if (callbackAfter === 0) {
										window.clearInterval(preloadInterval);
										callback();
									}
								}, 100);
								$.each(list, function(index, image) {
									list[index] = new Image();
									list[index].onload = function(result) {
										callbackAfter--;
									};
									list[index].onabort = function(result) {
										console.log(result);
									};
									list[index].onerror = function(result) {
										console.log(result);
									};
									/*if (!image.match('http://')) {
									 image = image;
									 }*/
									list[index].src = image;
								});
							}
						},
						hexToRgb												: function(hex) {
							// Expand shorthand form (e.g. "03F") to full form (e.g. "0033FF")
							var shorthandRegex = /^(#|0x)?([a-f\d])([a-f\d])([a-f\d])$/i;
							hex = hex.replace(shorthandRegex, function(m, x, r, g, b) {
								return r + r + g + g + b + b;
							});

							var result = /^(#|0x)?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
							return result ? {
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
