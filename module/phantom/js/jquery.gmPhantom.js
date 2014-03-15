/*
 * Title                   : gmPhantom
 * Version                 : 1.3
 * Copyright               : 2013 CodEasily.com
 * Website                 : http://www.codeasily.com
 */
if(typeof jQuery.fn.gmPhantom == 'undefined'){
	(function($, window, document, undefined){
		$.fn.gmPhantom = function(options){
			var Container = this,
					ID = '',
					Settings,
					Content,
					tempVar,
					opt,

					defaultSettings = {
						'width':             900, // Width (value in pixels). Default value: 900. Set the width of the gallery.
						'height':            0, // Height (value in pixels). Default value: 0. Set the height of the gallery. If you set the value to 0 all thumbnails are going to be displayed.
						'responsiveEnabled': 'true', // Responsive Enabled (true, false). Default value: true. Enable responsive layout.
						'thumbsNavigation':  'mouse', // Thumbnails Navigation (mouse, scroll). Default value: mouse. Set how you navigate through the thumbnails.
						'thumbCols':         0, // Number of Columns (auto, number). Default value: 0. Set the number of columns for the grid.
						'thumbRows':         0, // Number of Lines (auto, number). Default value: 0. Set the number of lines for the grid.
						'bgColor':           'ffffff', // Background Color (color hex code). Default value: ffffff. Set gallery background color.
						'bgAlpha':           0, // Background Alpha (value from 0 to 100). Default value: 0. Set gallery background alpha.

						'thumbWidth':          			150, // Thumbnail Width (the size in pixels). Default value: 150. Set the width of a thumbnail.
						'thumbHeight':         			150, // Thumbnail Height (the size in pixels). Default value: 150. Set the height of a thumbnail.
						'thumbsSpacing':       			10, // Thumbnails Spacing (value in pixels). Default value: 10. Set the space between thumbnails.
						'thumbsVerticalPadding':		5, // Thumbnails Padding Top (value in pixels). Default value: 5. Set the top padding for the thumbnails.
						'thumbsHorizontalPadding':	5, // Thumbnails Padding Top (value in pixels). Default value: 5. Set the top padding for the thumbnails.
						'thumbsAlign':  						'left', // Thumbnails align. Default value: left.

						'thumbAlpha':             85, // Thumbnail Alpha (value from 0 to 100). Default value: 85. Set the transparancy of a thumbnail.
						'thumbAlphaHover':        100, // Thumbnail Alpha Hover (value from 0 to 100). Default value: 100. Set the transparancy of a thumbnail when hover.
						'thumbBorderSize':        1, // Thumbnail Border Size (value in pixels). Default value: 1. Set the size of a thumbnail's border.
						'thumbBorderColor':       'cccccc', // Thumbnail Border Color (color hex code). Default value: cccccc. Set the color of a thumbnail's border.
						'thumbPadding':        		5, // Thumbnail Padding (value in pixels). Default value: 3. Set padding value of a thumbnail.

						'thumbsInfo': 'label', // Info Thumbnails Display (none, tooltip, label). Default value: tooltip. Display a small info text on the thumbnails, a tooltip or a label on bottom.

						'tooltipBgColor':     'ffffff', // Tooltip Background Color (color hex code). Default value: ffffff. Set tooltip background color.
						'tooltipStrokeColor': '000000', // Tooltip Stroke Color (color hex code). Default value: 000000. Set tooltip stroke color.
						'tooltipTextColor':   '000000', //   Tooltip Text Color (color hex code). Default value: 000000. Set tooltip text color.

						'lightboxPosition':      'document', // Lightbox Position (document, gallery). Default value: document. If the value is document the lightbox is displayed over the web page fitting in the browser's window, else the lightbox is displayed in the gallery's container.
						'lightboxWindowColor':   '000000', // Lightbox Window Color (color hex code). Default value: 000000. Set the color for the lightbox window.
						'lightboxWindowAlpha':   80, // Lightbox Window Alpha (value from 0 to 100). Default value: 80. Set the transparancy for the lightbox window.

						'socialShareEnabled':  'true', // Social Share Enabled (true, false). Default value: true. Enable AddThis Social Share.

						'moduleUrl': ''

					},

					Images = [],
					Thumbs = [],
					ThumbsLoaded = [],
					ThumbsFirstPosX = [],
					ThumbsFirstPosY = [],
					CaptionTitle = [],
					CaptionText = [],
					Media = [],
					Links = [],
					LinksTarget = [],
					noItems = 0,

					startGalleryID = 0,
					startWith = 0,

					currentItem = 0,
					itemLoaded = false,
					ImageWidth = 0,
					ImageHeight = 0,
					LightboxDisplayTime = 600,
					LightboxNavDisplayTime = 200,
					LightboxTextDisplayTime = 80,
					prevhover = false, nexthover = false,
					resize = false,
					fix_windowW = 0,
					fix_windowH = 0,
					scale = 1, translateX = 0, translate_X = 0, translateY = 0, translate_Y = 0,
					transform_scale = 'scale(1)',
					transform_translate = 'translate(0, 0)',

					methods = {
						init:          function(){// Init Plugin.
							return this.each(function(){
								Settings = methods.parseSettings();
								opt = $.extend(defaultSettings, Settings);
								if(options){
									opt = $.extend(opt, options);
								}
								opt.initialWidth = opt.width;
								opt.initialHeight = opt.height;
								opt.initialCols = opt.thumbCols;
								opt.initialRows = opt.thumbRows;
								opt.thumbWidthDesktop = opt.thumbWidth;
								opt.thumbHeightDesktop = opt.thumbHeight;

								methods.parseContent();
								methods.crunching();
								$(window).bind('resize.gmPhantom', methods.initRP);
							});
						},
						parseSettings: function(){// Parse Settings.
							ID = $(Container).attr('id').split('_ID')[1];
							if(typeof(window['gmPhantom_ID' + ID + '_Settings']) === 'object'){
								Settings = window['gmPhantom_ID' + ID + '_Settings'];
							}
							else {
								Settings = {};
							}
							return Settings;
						},
						parseContent:  function(){// Parse Content.
							if(typeof(window['gmPhantom_ID' + ID + '_Content']) === 'object'){
								Content = window['gmPhantom_ID' + ID + '_Content'];
							}
							else {
								return;
							}
							$.each(Content, function(index){
								$.each(Content[index], function(key){
									switch(key){
										case 'image':
											Images.push(opt.libraryUrl + Content[index][key]);
											break;
										case 'thumb':
											Thumbs.push(opt.libraryUrl + Content[index][key]);
											break;
										case 'captionTitle':
											CaptionTitle.push(Content[index][key]);
											break;
										case 'captionText':
											CaptionText.push(Content[index][key]);
											break;
										case 'media':
											Media.push(Content[index][key]);
											break;
										case 'link':
											Links.push(Content[index][key]);
											break;
										case 'linkTarget':
											if(Content[index][key] == ''){
												LinksTarget.push('_blank');
											}
											else {
												LinksTarget.push(Content[index][key]);
											}
											break;
									}
								});
							});

							noItems = Thumbs.length;

							if(opt.responsiveEnabled == 'true'){
								methods.rpResponsive();
							}

							//methods.initGallery();
						},
						crunching:     function(){// create new thumbs if not exists
							if(typeof(window['gmPhantom_ID' + ID + '_Crunch']) === 'object'){
								var Crunch = window['gmPhantom_ID' + ID + '_Crunch'];
								var crunchlength = Crunch.length;
								if(crunchlength){
									tempVar = [];
									tempVar.push('<div id="gmPhantom_ID' + ID + '_ProgressBar" class="gmPhantom_ProgressBar"><div class="gmProgress"><span class="gmBar"></span><div class="gmCounter"><span class="gmCount">0</span>/' + crunchlength + '</div></div></div>');
									Container.html(tempVar.join(''));
									var index = 0,
											crunch_image = function(index){
												$.ajax({
													type:    "POST",
													url:     ajaxurl,
													data:    { action: 'gmedia_crunching', args: Crunch[index]},
													cache:   false,
													timeout: 10000,
													async:   true,
													success: function(msg){
														index++;
														$('#gmPhantom_ID' + ID + '_ProgressBar .gmBar', Container).animate({width: (100 / crunchlength * (index)) + '%'}, 500, function(){
															$(this).parent().find('.gmCount').text(index);
															if(!Crunch[index]){
																methods.initGallery();
															}
														});
														if(Crunch[index]){
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
						initGallery:   function(){// Init the Gallery
							var LightboxHTML = [],
									HTML = [];
							var browser_class = '';
							if(prototypes.isIEBrowser()){
								if(prototypes.isIEBrowser() < 8){
									browser_class += ' msie msie7';
								} else {
									browser_class += ' msie';
								}
							}
							if(prototypes.isTouchDevice()){
								browser_class += ' istouch';
							}

							LightboxHTML.push('    <div class="gmPhantom_LightboxWrapper' + browser_class + '" id="gmPhantom_LightboxWrapper_' + ID + '">');
							LightboxHTML.push('        <div class="gmPhantom_LightboxBg"></div>');
							LightboxHTML.push('        <div class="gmPhantom_LightboxWindow">');
							LightboxHTML.push('            <div class="gmPhantom_LightboxNav_PrevBtn gm_lbw_nav"><span>&lsaquo;</span></div>');
							LightboxHTML.push('            <div class="gmPhantom_LightboxNav_NextBtn gm_lbw_nav"><span>&rsaquo;</span></div>');
							LightboxHTML.push('            <div class="gmPhantom_LightboxContainer">');
							LightboxHTML.push('                <div class="gmPhantom_Lightbox"></div>');
							LightboxHTML.push('            		 <div class="gmPhantom_LightboxNav_PrevBtn gm_lbc_nav"></div>');
							LightboxHTML.push('                <div class="gmPhantom_LightboxNav_NextBtn gm_lbc_nav"></div>');
							LightboxHTML.push('                <div class="gmPhantom_CaptionTitle">');
							LightboxHTML.push('            		     <div class="gmPhantom_title"></div>');
							LightboxHTML.push('                </div>');
							LightboxHTML.push('                <div class="gmPhantom_CaptionTextContainer">');
							LightboxHTML.push('                    <div class="gmPhantom_CaptionText"></div>');
							LightboxHTML.push('                </div>');
							LightboxHTML.push('            </div>');
							LightboxHTML.push('            <div class="gmPhantom_counter"><span id="gmPhantom_ItemCount_' + ID + '"></span> / ' + noItems + '</div>');
							LightboxHTML.push('            <div class="gmPhantom_LightboxNavExtraButtons">');
							LightboxHTML.push('                <div class="gmPhantom_LightboxNav_CloseBtn"><span>CLOSE</span></div>');
							if(opt.socialShareEnabled == 'true'){
								LightboxHTML.push('              <div class="gmPhantom_LightboxSocialShare"></div>');
							}
							LightboxHTML.push('                <div id="gmlog"><p></p></div><br class="gmPhantom_Clear" />');
							LightboxHTML.push('            </div>');
							if(prototypes.isTouchDevice()){
								LightboxHTML.push('            <div class="gmPhantom_info"><i>i</i></div>');
								LightboxHTML.push('        		 <div id="fix_window">&nbsp;</div>');
							}
							LightboxHTML.push('        </div>');
							LightboxHTML.push('    </div>');
							if(prototypes.isTouchDevice()){
								LightboxHTML.push('    <div class="gmPhantom_bodyBg" id="gmPhantom_bodyBg_' + ID + '" style="background-color: #' + opt.lightboxWindowColor + '"></div>');
							}

							HTML.push('<div class="gmPhantom_Container">');
							HTML.push('   <div class="gmPhantom_Background"></div>');
							HTML.push('   <div class="gmPhantom_thumbsWrapper' + browser_class + '"></div>');

							if(opt.thumbsInfo == 'tooltip' && !prototypes.isTouchDevice()){
								HTML.push('<div class="gmPhantom_Tooltip"></div>');
							}

							if(opt.lightboxPosition != 'document'){
								HTML.push(LightboxHTML.join(''));
							}
							HTML.push('</div>');

							Container.html(HTML.join(''));

							if(opt.lightboxPosition == 'document'){
								$('body').append(LightboxHTML.join(''));
							}
							methods.initSettings();
						},
						initSettings:  function(){// Init Settings
							methods.initContainer();
							methods.initThumbs();
							if(opt.thumbsInfo == 'tooltip' && !prototypes.isTouchDevice()){
								methods.initTooltip();
							}
							methods.initLightbox();
							methods.initCaption();

						},
						initRP:        function(){// Init Resize & Positioning
							if(opt.responsiveEnabled == 'true'){
								methods.rpResponsive();
								methods.rpContainer();
								methods.rpThumbs();

								if(itemLoaded){
									if(Media[currentItem - 1] == ''){
										resize = true;
										methods.rpLightboxImage();
									}
									else {
										methods.rpLightboxMedia();
									}
								}
							}
						},
						rpResponsive:  function(){
							var hiddenBustedItems = prototypes.doHideBuster($(Container));

							if($(Container).width() < opt.initialWidth){
								opt.width = $(Container).width();
							}
							else {
								opt.width = opt.initialWidth;
							}

							if($(window).width() <= 640){
								opt.thumbWidth = opt.thumbWidthDesktop / 2;
								opt.thumbHeight = opt.thumbHeightDesktop / 2;
							}
							else {
								opt.thumbWidth = opt.thumbWidthDesktop;
								opt.thumbHeight = opt.thumbHeightDesktop;
							}

							prototypes.undoHideBuster(hiddenBustedItems);
						},

						initContainer: function(){// Init Container
							$('.gmPhantom_Container', Container).css({'display': 'block', 'text-align': opt.thumbsAlign});

							if(opt.height == 0){
								$('.gmPhantom_Container', Container).css('overflow', 'visible');
							}
							$('.gmPhantom_Background', Container).css('opacity', opt.bgAlpha / 100);
							if(opt.bgAlpha != 0){
								$('.gmPhantom_Background', Container).css('background-color', '#' + opt.bgColor);
							}
							$('.gmPhantom_thumbsWrapper', Container).css({'padding-top': opt.thumbsVerticalPadding, 'padding-bottom': opt.thumbsVerticalPadding, 'padding-left': opt.thumbsHorizontalPadding, 'padding-right': opt.thumbsHorizontalPadding});
							if(opt.thumbsAlign == 'left'){
								$('.gmPhantom_thumbsWrapper', Container).css({'margin-left': 0});
							} else if(opt.thumbsAlign == 'right'){
								$('.gmPhantom_thumbsWrapper', Container).css({'margin-right': 0});
							}
							methods.rpContainer();
						},
						rpContainer:   function(){// Resize & Position Container
							$('.gmPhantom_Container', Container).width(opt.width);

							if(opt.height != 0){
								$('.gmPhantom_Container', Container).height(opt.height);
							}
							else {
								$('.gmPhantom_Container', Container).css('height', 'auto');
								$('.gmPhantom_thumbsWrapper', Container).css('height', 'auto');
							}
						},

						initThumbs:              function(){//Init Thumbnails
							if(opt.height == 0){
								$('.gmPhantom_thumbsWrapper', Container).css({'overflow': 'visible', 'position': 'relative'});
							}

							for(var i = 1; i <= noItems; i++){
								methods.loadThumb(i);
							}

							if(opt.height != 0){
								if(prototypes.isTouchDevice()){
									prototypes.touchNavigation($('.gmPhantom_Container', Container), $('.gmPhantom_thumbsWrapper', Container));
								}
								else if(opt.thumbsNavigation == 'mouse'){
									$('.gmPhantom_thumbsWrapper', Container).css('position', 'absolute');
									methods.moveThumbs();
								}
								else if(opt.thumbsNavigation == 'scroll'){
									methods.initThumbsScroll();
								}
							}

							methods.rpThumbs();
						},
						loadThumb:               function(no){// Load a thumbnail
							methods.initThumb(no);
							var img = new Image();

							$(img).load(function(){
								$('.gmPhantom_Thumb', '#gmPhantom_ThumbContainer_' + ID + '-' + no, Container).html(this);

								methods.loadCompleteThumb(no);
							}).attr({src: Thumbs[no - 1], alt: CaptionTitle[no - 1], width: opt.thumbWidth, height: opt.thumbHeight}).css('opacity',0);
						},
						initThumb:               function(no){// Init thumbnail before loading
							var ThumbHTML = [];

							ThumbHTML.push('<div class="gmPhantom_ThumbContainer gmPhantom_ThumbLoader" id="gmPhantom_ThumbContainer_' + ID + '-' + no + '">');
							ThumbHTML.push('   <div class="gmPhantom_Thumb"></div>');

							if(opt.thumbsInfo == 'label'){
								if(CaptionTitle[no - 1] != ''){
									ThumbHTML.push('   <div class="gmPhantom_ThumbLabel">' + CaptionTitle[no - 1] + '</div>');
								}
							}

							if(no == noItems){
								ThumbHTML.push('</div><br style="clear:both;" />');
							}
							else {
								ThumbHTML.push('</div>');
							}

							$('.gmPhantom_thumbsWrapper', Container).append(ThumbHTML.join(""));

							if(!prototypes.isTouchDevice()){
								$('#gmPhantom_ThumbContainer_' + ID + '-' + no).css('opacity', opt.thumbAlpha / 100);
							}

							if(opt.thumbBorderSize > 0){
								$('#gmPhantom_ThumbContainer_' + ID + '-' + no).css({'border-width': opt.thumbBorderSize, 'border-color': '#' + opt.thumbBorderColor, 'border-style': 'solid'});
								if(opt.thumbsSpacing > 1){
									$('#gmPhantom_ThumbContainer_' + ID + '-' + no).css({'box-shadow': '0 0 5px -2px'});
								}
							}

						},
						loadCompleteThumb:       function(no){// Resize, Position & Edit a thumbnmail after loading

							$('.gmPhantom_Thumb img', '#gmPhantom_ThumbContainer_' + ID + '-' + no).animate({'opacity': '1'}, 600, function(){
								$('#gmPhantom_ThumbContainer_' + ID + '-' + no).removeClass('gmPhantom_ThumbLoader');
							});

							if(!prototypes.isTouchDevice()){
								$('#gmPhantom_ThumbContainer_' + ID + '-' + no).hover(function(){
											if(opt.thumbsInfo == 'label' && CaptionTitle[no - 1] != ''){
												$(this).stop(true, true).animate({'opacity': opt.thumbAlphaHover / 100}, 50);
												var top_to = opt.thumbHeight + opt.thumbPadding - $('.gmPhantom_ThumbLabel', this).outerHeight();
												$('.gmPhantom_ThumbLabel', this).animate({'top': top_to}, 50);
											} else if(opt.thumbsInfo == 'tooltip'){
												$(this).stop().animate({'opacity': opt.thumbAlphaHover / 100}, 50);
												methods.showTooltip(no - 1);
											}
										},
										function(){
											if(opt.thumbsInfo == 'label' && CaptionTitle[no - 1] != ''){
												$('.gmPhantom_ThumbLabel', this).stop(true, true).animate({'top':'100%'}, 50);
												$('#gmPhantom_ThumbContainer_' + ID + '-' + no).animate({'opacity': parseInt(opt.thumbAlpha) / 100}, 50);
											} else if(opt.thumbsInfo == 'tooltip'){
												$('.gmPhantom_Tooltip', Container).css('display', 'none');
												$('#gmPhantom_ThumbContainer_' + ID + '-' + no).stop().animate({'opacity': parseInt(opt.thumbAlpha) / 100}, 50);
											}
										});
							}

							$('#gmPhantom_ThumbContainer_' + ID + '-' + no, Container).click(function(){
								if(Links[no - 1] != ''){
									prototypes.openLink(Links[no - 1], LinksTarget[no - 1]);
								}
								else {
									methods.showLightbox(no);
								}
							});
						},
						rpThumbs:                function(){// Resize & Position Thumbnails
							var thumbW = opt.thumbWidth + opt.thumbBorderSize*2 + opt.thumbPadding*2,
									no = 0,
									hiddenBustedItems = prototypes.doHideBuster($(Container));
							if(opt.initialHeight == 0 || (opt.initialCols == 0 && opt.initialRows == 0)){
								opt.thumbCols = parseInt((opt.width + opt.thumbsSpacing - opt.thumbsHorizontalPadding*2) / (thumbW + opt.thumbsSpacing));
								opt.thumbRows = parseInt(noItems / opt.thumbCols);

								if(opt.thumbCols == 0){
									opt.thumbCols = 1;
								}

								if(opt.thumbRows * opt.thumbCols < noItems){
									opt.thumbRows++;
								}
							} else {
								if((opt.thumbRows * opt.thumbCols < noItems) && opt.thumbCols != 0){
									if(noItems % opt.thumbCols != 0){
										opt.thumbRows = parseInt(noItems / opt.thumbCols) + 1;
									}
									else {
										opt.thumbRows = noItems / opt.thumbCols;
									}
								} else {
									if(noItems % opt.thumbRows != 0){
										opt.thumbCols = parseInt(noItems / opt.thumbRows) + 1;
									}
									else {
										opt.thumbCols = noItems / opt.thumbRows;
									}
								}
							}
							$('.gmPhantom_ThumbContainer', Container).width(opt.thumbWidth).height(opt.thumbHeight).css({'padding': opt.thumbPadding});

							$('.gmPhantom_ThumbContainer', Container).each(function () {
								no++;

								$(this).css('margin', 0);
								if (no > opt.thumbCols) {
									$(this).css('margin-top', opt.thumbsSpacing);
								}
								if (no % opt.thumbCols != 1 && opt.thumbCols != 1) {
									$(this).css('margin-left', opt.thumbsSpacing);
								}
								if (no % opt.thumbCols == 0) {
									$(this).css('margin-right', '-1px');
								}

							});
							var thumbs_el = $('.gmPhantom_thumbsWrapper', Container),
									thumbs_el_width = thumbW * opt.thumbCols + (opt.thumbCols - 1) * opt.thumbsSpacing,
									scrollbar_width = 0;
							thumbs_el.width(thumbs_el_width);
							if(thumbs_el_width >= $('.gmPhantom_Container', Container).width()){
								scrollbar_width = methods.scrollbarWidth();
							}

							if(opt.initialHeight != 0){
								var thumbH = opt.thumbHeight + opt.thumbBorderSize*2 + opt.thumbPadding*2,
										thumbs_el_height = thumbH * opt.thumbRows + (opt.thumbRows - 1) * opt.thumbsSpacing;
								if((thumbs_el_height + scrollbar_width + opt.thumbsVerticalPadding*2) >= $('.gmPhantom_Container', Container).height()){
									$('.gmPhantom_thumbsWrapper', Container).height(thumbs_el_height + scrollbar_width);
								}
								else {
									$('.gmPhantom_thumbsWrapper', Container).height($('.gmPhantom_Container', Container).height() - opt.thumbsVerticalPadding*2);
								}

								if((opt.thumbsNavigation == 'mouse')){
									if($('.gmPhantom_Container', Container).width() > thumbs_el.outerWidth()){
										thumbs_el.css('margin-left', ($('.gmPhantom_Container', Container).width() - thumbs_el.outerWidth())/2);
									} else {
										thumbs_el.css('margin-left', 0);
									}
									thumbs_el.css('margin-top', 0);
								}

								if(opt.thumbsNavigation == 'scroll' && typeof(jQuery.fn.jScrollPane) != 'undefined'){
									$('.gmPhantom_Container .jspContainer', Container).width($('.gmPhantom_thumbsWrapper', Container).width());
								}
							}

							methods.rpContainer();

							prototypes.undoHideBuster(hiddenBustedItems);
						},
						moveThumbs:              function(){// Init thumbnails move
							var thumbs_el = $('.gmPhantom_thumbsWrapper', Container);
							$('.gmPhantom_Container', Container).mousemove(function(e){
								if(itemLoaded){
									return;
								}
								var thumbW, thumbH, mousePosition, thumbsPosition;

								if(thumbs_el.outerWidth() > $(this).width()){
									thumbW = opt.thumbWidth + opt.thumbBorderSize*2 + opt.thumbPadding*2 + opt.thumbsSpacing - opt.thumbsSpacing/opt.thumbRows + opt.thumbsHorizontalPadding/opt.thumbCols;
									mousePosition = e.clientX - $(this).offset().left + parseInt($(this).css('margin-left')) + $(document).scrollLeft();
									thumbsPosition = 0 - (mousePosition - thumbW) * (thumbs_el.outerWidth() - $(this).width()) / ($(this).width() - 2 * thumbW);
									if(thumbsPosition < (-1) * (thumbs_el.outerWidth() - $(this).width())){
										thumbsPosition = (-1) * (thumbs_el.outerWidth() - $(this).width());
									}
									if(thumbsPosition > 0){
										thumbsPosition = 0;
									}
									thumbs_el.css('margin-left', thumbsPosition);
									//thumbs_el.animate({'margin-left': thumbsPosition}, { duration: 200, queue: false });
								}

								if(thumbs_el.outerHeight() > $(this).height()){
									thumbH = opt.thumbHeight + opt.thumbBorderSize*2 + opt.thumbPadding*2 + opt.thumbsSpacing - opt.thumbsSpacing/opt.thumbRows + opt.thumbsVerticalPadding/opt.thumbRows;
									mousePosition = e.clientY - $(this).offset().top + parseInt($(this).css('margin-top')) + $(document).scrollTop();
									thumbsPosition = 0 - (mousePosition - thumbH) * (thumbs_el.outerHeight() - $(this).height()) / ($(this).height() - 2 * thumbH);
									if(thumbsPosition < (-1) * (thumbs_el.outerHeight() - $(this).height())){
										thumbsPosition = (-1) * (thumbs_el.outerHeight() - $(this).height());
									}
									if(thumbsPosition > 0){
										thumbsPosition = 0;
									}
									thumbs_el.css('margin-top', thumbsPosition);
									//thumbs_el.animate({'margin-top': thumbsPosition}, { duration: 200, queue: false });
								}
							});
						},
						initThumbsScroll:        function(){//Init Thumbnails Scroll
							if(typeof(jQuery.fn.jScrollPane) != 'undefined'){
								setTimeout(function(){
									$('.gmPhantom_Container', Container).jScrollPane({autoReinitialise: true});
								}, 10);
							}
							else {
								$('.gmPhantom_Container', Container).css('overflow', 'auto');
							}
						},
						scrollbarWidth:          function(){
							var div = $('<div style="position:absolute;left:-200px;top:-200px;width:50px;height:50px;overflow:scroll"><div>&nbsp;</div></div>').appendTo('body'),
									width = 50 - div.children().innerWidth();
							div.remove();
							return width;
						},
						initLightbox:            function(){// Init Lightbox
							startGalleryID = prototypes.$_GET('gmedia_gallery_id') != undefined? parseInt(prototypes.$_GET('gmedia_gallery_id')) : 0;
							startWith = prototypes.$_GET('gmedia_gallery_share') != undefined && startGalleryID == ID? parseInt(prototypes.$_GET('gmedia_gallery_share')) : 0;

							if(!prototypes.isTouchDevice()){
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').before('<img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" style="width:1px; height:100%; vertical-align:middle; margin-right: -1px; display: inline;" />');
							}
							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxBg').css({'background-color': '#' + opt.lightboxWindowColor, 'opacity': opt.lightboxWindowAlpha / 100});

							if(!prototypes.isTouchDevice()){
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').hover(function(){
									$('.gmPhantom_CaptionTitle',this).animate({'height': 'show'}, LightboxTextDisplayTime);
									$('.gmPhantom_CaptionTextContainer',this).animate({'height': 'show'}, LightboxTextDisplayTime);
								}, function(){
									$('.gmPhantom_CaptionTitle',this).animate({'height': 'hide'}, LightboxTextDisplayTime);
									$('.gmPhantom_CaptionTextContainer',this).animate({'height': 'hide'}, LightboxTextDisplayTime);
								});
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_PrevBtn').hover(function(){
									prevhover = true;
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_PrevBtn span').stop().animate({'left': '0'}, LightboxNavDisplayTime);
								}, function(){
									prevhover = false;
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_PrevBtn span').stop().animate({'left': '-'+$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_PrevBtn span').outerWidth()}, LightboxNavDisplayTime);
								});
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_NextBtn').hover(function(){
									nexthover = true;
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_NextBtn span').stop().animate({'right': '0'}, LightboxNavDisplayTime);
								}, function(){
									nexthover = false;
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_NextBtn span').stop().animate({'right': '-'+$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_NextBtn span').outerWidth()}, LightboxNavDisplayTime);
								});
							} else {
								$('#gmPhantom_bodyBg_' + ID).height($(document).height());
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').click(function(){
									$('.gmPhantom_CaptionTitle',this).animate({'height': 'toggle'}, LightboxTextDisplayTime);
									$('.gmPhantom_CaptionTextContainer',this).animate({'height': 'toggle'}, LightboxTextDisplayTime);
								});
								methods.lightboxNavigationSwipe();
							}

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_PrevBtn').click(function(){
								methods.previousLightbox();
							});

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_NextBtn').click(function(){
								methods.nextLightbox();
							});

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxSocialShare').on('mouseenter', function(){
								setTimeout(function(){
									$('#at15s').css('position', 'fixed');
								}, 10);
							});

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_CloseBtn').click(function(){
								methods.hideLightbox();
							});

							$(document).keydown(function(e){
								if(itemLoaded){
									switch(e.keyCode){
										case 27:
											methods.hideLightbox();
											break;
										case 37:
											methods.previousLightbox();
											break;
										case 39:
											methods.nextLightbox();
											break;
									}
								}
							});

							if(startGalleryID == ID){
								var href = window.location.href,
										variables = 'gmedia_wall_grid_gallery_id=' + startGalleryID + '&gmedia_wall_grid_gallery_share=' + startWith;

								if(href.indexOf('?' + variables) != -1){
									variables = '?' + variables;
								}
								else {
									variables = '&' + variables;
								}

								window.location = '#gmPhantom_ID' + ID;

								try {
									window.history.pushState({'html': '', 'pageTitle': document.title}, '', href.split(variables)[0]);
								} catch(e){
									//console.log(e);
								}
							}

							if(startWith != 0){
								methods.showLightbox(startWith);
								startWith = 0;
							}
						},
						showLightbox:            function(no){// Show Lightbox
							var maxWidth, maxHeight, currW, currH;

							if(opt.lightboxPosition == 'document' && !prototypes.isTouchDevice()){
								$(document).on('mousewheel.photobox', function(e){
									e.preventDefault();
									e.stopPropagation();
									return false;
								});
							} else {
								setTimeout(function(){
									var zoomlevel = 100, dpr = 1;
									if(window.devicePixelRatio !== undefined){ dpr = window.devicePixelRatio; }
									if(window.innerWidth > window.innerHeight){ zoomlevel *= 0.75; }
									if($(window).width() != screen.width){
										zoomlevel *= dpr;
									} else {
										zoomlevel *= 0.66;
									}
									zoomlevel *= ($('#fix_window')[0].offsetLeft + 1)/$(document).width();
									$('.gmPhantom_LightboxWindow', '#gmPhantom_LightboxWrapper_' + ID).css('font-size', zoomlevel+'%');
								}, 10);
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxBg').css('opacity', 1);
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gm_lbw_nav').css({'overflow': 'visible', 'z-index': 10, 'width': '1px', 'min-width': '1px'});
							}

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_PrevBtn span').css({'left': 0});
							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_NextBtn span').css({'right': 0});
							$('#gmPhantom_LightboxWrapper_' + ID).fadeIn(LightboxDisplayTime, function(){
								if(Media[no - 1] != ''){
									methods.loadLightboxMedia(no);
								}
								else {
									methods.loadLightboxImage(no);
								}
								if(!prototypes.isTouchDevice()){
									setTimeout(function(){
										if(!prevhover){
											$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_PrevBtn span').animate({'left': '-'+$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_PrevBtn span').outerWidth()}, LightboxNavDisplayTime);
										}
										if(!nexthover){
											$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_NextBtn span').animate({'right': '-'+$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxNav_NextBtn span').outerWidth()}, LightboxNavDisplayTime);
										}
									}, 2000);
								} else {
									$(this).addClass('gm_show');
								}
							});
						},
						hideLightbox:            function(){// Hide Lightbox
							if(itemLoaded){
								$('#gmPhantom_LightboxWrapper_' + ID).removeClass('gm_show').fadeOut(LightboxDisplayTime, function(){
									$(document).off('mousewheel.photobox');
									//$(document).unbind('touchmove');
									currentItem = 0;
									itemLoaded = false;
									prevhover = nexthover = false;
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css('opacity', 0);
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_Lightbox').html('');
								});
							}
						},
						loadLightboxImage:       function(no){// Load Lightbox Image
							var img = new Image();
							img.src = Images[no - 1];

							currentItem = no;
							$('#gmPhantom_ItemCount_' + ID).html(currentItem);

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxWindow').addClass('gmPhantom_LightboxLoader');

							$(img).one('load', function(){
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxWindow').removeClass('gmPhantom_LightboxLoader');
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_Lightbox').html(this);
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_Lightbox img').attr('alt', CaptionTitle[no - 1]);
								if(opt.socialShareEnabled == 'true'){
									methods.initSocialShare();
								}
								$('#gmPhantom_LightboxWrapper_' + ID).css('display', 'block');
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').removeAttr('style');
								ImageWidth = $(this).width() || this.naturalWidth || 640;
								ImageHeight = $(this).height() || this.naturalHeight || 480;
								//$('#gmPhantom_LightboxWrapper_' + ID).css('display', 'none');

								itemLoaded = true;
								scale = 1; translateX = 0; translate_X = 0; translateY = 0; translate_Y = 0;
								transform_scale = 'scale(1)';
								transform_translate = 'translate(0, 0)';
								methods.showCaption(no);
								methods.rpLightboxImage();

								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').stop(true, true).animate({'opacity': 1}, LightboxDisplayTime);
							}).each(function() {
								if(this.complete) $(this).trigger("load");
							});
						},
						loadLightboxMedia:       function(no){// Load Lightbox Media
							currentItem = no;
							$('#gmPhantom_ItemCount_' + ID).html(currentItem);

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxWindow').removeClass('gmPhantom_LightboxLoader');
							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_Lightbox').html(Media[no - 1]);
							if(opt.socialShareEnabled == 'true'){
								methods.initSocialShare();
							}

							var iframe = $('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_Lightbox').children();
							var iframeSRC = iframe.attr('src');

							if(iframeSRC != null){
								if(iframeSRC.indexOf('?') != -1){
									iframe.attr('src', iframeSRC + '&wmode=transparent');
								}
								else {
									iframe.attr('src', iframeSRC + '?wmode=transparent');
								}
							}

							if(iframe.length && iframe.prop("tagName").toUpperCase() == 'IFRAME'){
								ImageWidth = parseFloat(iframe.attr('width'));
								ImageHeight = parseFloat(iframe.attr('height'));
							}
							else {
								ImageWidth = 0;
								ImageHeight = 0;
							}

							itemLoaded = true;
							scale = 1; translateX = 0; translate_X = 0; translateY = 0; translate_Y = 0;
							transform_scale = 'scale(1)';
							transform_translate = 'translate(0, 0)';
							methods.showCaption(no);
							methods.rpLightboxMedia();

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').stop(true, true).animate({'opacity': 1}, LightboxDisplayTime);
						},
						previousLightbox:        function(){
							var previousItem = currentItem - 1;

							if(currentItem == 1){
								previousItem = noItems;
							}

							if(Links[previousItem - 1] == ''){
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').stop(true, true).animate({'opacity': 0}, LightboxDisplayTime, function(){
									if(Media[previousItem - 1] != ''){
										methods.loadLightboxMedia(previousItem);
									}
									else {
										methods.loadLightboxImage(previousItem);
									}
								});
							}
							else {
								currentItem = previousItem;
								methods.previousLightbox();
							}
						},
						nextLightbox:            function(){
							var nextItem = currentItem + 1;

							if(currentItem == noItems){
								nextItem = 1;
							}

							if(Links[nextItem - 1] == ''){
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').stop(true, true).animate({'opacity': 0}, LightboxDisplayTime, function(){
									if(Media[nextItem - 1] != ''){
										methods.loadLightboxMedia(nextItem);
									}
									else {
										methods.loadLightboxImage(nextItem);
									}
								});
							}
							else {
								currentItem = nextItem;
								methods.nextLightbox();
							}
						},
						rpLightboxImage:         function(){// Resize & Position Lightbox Image
							var windowW, windowH, maxWidth, maxHeight, currW, currH;

							if(itemLoaded){
								if(opt.lightboxPosition == 'document'){
									if(prototypes.isTouchDevice()){
										windowW = $('#gmPhantom_LightboxWrapper_' + ID).width();
										windowH = $('#gmPhantom_LightboxWrapper_' + ID).height();
										maxWidth = windowW;
										maxHeight = windowH;
									} else {
										windowW = $(window).width();
										windowH = $(window).height();
										maxWidth = windowW - (($(window).width() <= 640)? 0 : 40);
										maxHeight = windowH - (($(window).width() <= 640)? 0 : 20);
									}
								} else {
									windowW = maxWidth = $('.gmPhantom_Container', Container).width();
									windowH = maxHeight = $('.gmPhantom_Container', Container).height();
								}

								if(ImageWidth <= maxWidth && ImageHeight <= maxHeight){
									currW = ImageWidth;
									currH = ImageHeight;
								}
								else {
									currH = maxHeight;
									currW = (ImageWidth * maxHeight) / ImageHeight;

									if(currW > maxWidth){
										currW = maxWidth;
										currH = (ImageHeight * maxWidth) / ImageWidth;
									}
								}

								//methods.rpCaption();
								if(prototypes.isTouchDevice()){
									setTimeout(function(){
										fix_windowW = $('#fix_window')[0].offsetLeft + 1;
										fix_windowH = $('#fix_window')[0].offsetTop + 1;
										var fix_top = (windowH - currH)/2,
												fix_left = 'auto';
										if((windowW > fix_windowW) && (currW > fix_windowW)){
											fix_left = (fix_windowW - currW)/2;
										}
										if(windowH != fix_windowH){
											fix_top = (fix_windowH - currH)/2;
										}
										$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').width(currW).height(currH);
										if(resize){
											$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').animate({'margin-top': fix_top, 'margin-left': fix_left, 'width': currW, 'height': currH}, 200);
											resize = false;
										} else {
											$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css({'margin-top': fix_top, 'margin-left': fix_left});
										}
									}, 10);
								} else {
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').width(currW).height(currH);
								}
							}
						},
						rpLightboxMedia:         function(){// Resize & Position Lightbox Media
							var windowW, windowH, maxWidth, maxHeight, currW, currH;

							if(opt.lightboxPosition == 'document'){
								if(prototypes.isTouchDevice()){
									windowW = $('#gmPhantom_LightboxWrapper_' + ID).width();
									windowH = $('#gmPhantom_LightboxWrapper_' + ID).height();
									maxWidth = windowW;
									maxHeight = windowH;
								} else {
									windowW = $(window).width();
									windowH = $(window).height();
									maxWidth = windowW - (($(window).width() <= 640)? 0 : 40);
									maxHeight = windowH - (($(window).width() <= 640)? 0 : 20);
								}
							} else {
								windowW = maxWidth = $('.gmPhantom_Container', Container).width();
								windowH = maxHeight = $('.gmPhantom_Container', Container).height();
							}

							if(ImageWidth <= maxWidth && ImageHeight <= maxHeight){
								currW = ImageWidth;
								currH = ImageHeight;

								if(ImageWidth == 0 && ImageHeight == 0){
									currW = $('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_Lightbox').children().width().height();
								}
							} else {
								currH = maxHeight;
								currW = (ImageWidth * maxHeight) / ImageHeight;

								if(currW > maxWidth){
									currW = maxWidth;
									currH = (ImageHeight * maxWidth) / ImageWidth;
								}
							}

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_Lightbox').width(currW).height(currH).children().width(currW).height(currH);

							//methods.rpCaption();
							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').width(currW).height(currH).css({
								'margin-top':  (windowH - $('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').height()) / 2,
								'margin-left': (windowW - $('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').width()) / 2});
						},
						lightboxNavigationSwipe: function(){
							var touchContainer = $('#gmPhantom_LightboxWrapper_' + ID),
									imgContainer = $('.gmPhantom_LightboxContainer', touchContainer),
									buttons = $('.gmPhantom_LightboxNavExtraButtons', touchContainer),
									closeBtn = $('.gmPhantom_LightboxNav_CloseBtn', touchContainer),
									shareBtn = $('.gmPhantom_LightboxSocialShare', touchContainer),
									touch, startX, startY, currX, currY, start, end,
									moveX = 0, moveY = 0, lastTouch = 0,
									img_w = ImageWidth,
									img_h = ImageHeight;
							$('body').bind('orientationchange', function(){
								scale = 1; translateX = 0; translate_X = 0; translateY = 0; translate_Y = 0;
								transform_scale = 'scale(1)';
								transform_translate = 'translate(0, 0)';
								$('.gmPhantom_Lightbox img', touchContainer).css({'transform':transform_scale+' '+transform_translate, '-webkit-transform':transform_scale+' '+transform_translate});

								var zoomlevel = 100, dpr = 1;
								if(window.devicePixelRatio !== undefined){ dpr = window.devicePixelRatio; }
								if(window.innerWidth > window.innerHeight){ zoomlevel *= 0.75; }
								if($(window).width() != screen.width){
									zoomlevel *= dpr;
								} else {
									zoomlevel *= 0.66;
								}
								zoomlevel *= ($('#fix_window')[0].offsetLeft + 1)/$(document).width();
								$('.gmPhantom_LightboxWindow', touchContainer).css('font-size', zoomlevel+'%');
							});
							touchContainer.bind('touchstart touchmove touchend', function (e) {
								if(e.originalEvent.touches.length > 1){
									return;
								}
								touch = e.originalEvent.touches[0];
								var now;
								if(e.type == 'touchstart'){
									now = e.timeStamp;
									var delta = now - lastTouch;
									if(delta > 20 && delta < 400){
										lastTouch = 0;
										e.preventDefault();
										e.stopPropagation();
										return;
									}else{
										lastTouch = now;
									}
									startX = currX = touch.clientX;
									startY = currY = touch.clientY;
									start = now;
									translate_X = translateX;
									translate_Y = translateY;
								} else if(e.type == 'touchmove'){
									e.preventDefault();
									currX = touch.clientX;
									currY = touch.clientY;
									moveX = currX - startX;
									moveY = currY - startY;
									if(scale == 1){
										translateX = 0;
										translateY = 0;
										$('img',imgContainer).css({'margin-top':moveY, 'margin-left':moveX});
										if(moveX > 0){
											$('.gmPhantom_LightboxNav_PrevBtn',imgContainer).css({'width':moveX});
										} else if(moveX < 0){
											$('.gmPhantom_LightboxNav_NextBtn',imgContainer).css({'width':Math.abs(moveX)});
										} else {
											$('.gm_lbc_nav',imgContainer).css({'width':0});
										}

										if(moveY < 0){
											if(moveY > -fix_windowH/4) {
												shareBtn.removeClass('hover');
											} else {
												shareBtn.addClass('hover');
											}
										} else {
											if(moveY < fix_windowH/4) {
												closeBtn.removeClass('hover');
											} else {
												closeBtn.addClass('hover');
											}
										}
									} else {
										if(fix_windowW < img_w){
											translate_X = translateX + moveX/scale;
											var min_translate_x = (img_w - fix_windowW)/2/scale;
											if(min_translate_x < Math.abs(translate_X)){
												translate_X = min_translate_x*(translate_X < 0 ? -1 : 1);
											}
										} else {
											translate_X = 0;
										}
										if(fix_windowH < img_h){
											translate_Y = translateY + moveY/scale;
											var min_translate_y = (img_h - fix_windowH)/2/scale;
											if(min_translate_y < Math.abs(translate_Y)){
												translate_Y = min_translate_y*(translate_Y < 0 ? -1 : 1);
											}
										} else {
											translate_Y = 0;
										}
									}
									transform_translate = 'translate(' + translate_X + 'px, ' + translate_Y + 'px)';
									$('img', imgContainer).css({'transform':transform_scale+' '+transform_translate, '-webkit-transform':transform_scale+' '+transform_translate});
								} else if(e.type == 'touchend'){
									now = e.timeStamp;
									if(e.originalEvent.touches.length > 0){
										startX = currX = touch.clientX;
										startY = currY = touch.clientY;
										start = now;
									} else {
										translateX = translate_X;
										translateY = translate_Y;
										end = now;
										closeBtn.removeClass('hover');
										shareBtn.removeClass('hover');
										if(scale == 1){
											if(moveY < -fix_windowH/4){
												moveY = 0;
												$('a', shareBtn).click();
											} else if(moveY > fix_windowH/4){
												moveY = 0;
												methods.hideLightbox();
											} else {
												if(moveX > fix_windowW/5){
													moveX = 0;
													methods.previousLightbox();
												} else if(moveX < -fix_windowW/5){
													moveX = 0;
													methods.nextLightbox();
												}
											}
										}
										$('img',imgContainer).animate({'margin-top':0, 'margin-left':0}, 200);
										$('.gm_lbc_nav',imgContainer).animate({'width':0}, 200);
									}
								}
							});
							touchContainer.bind('gesturechange gestureend', function (e) {
								e.preventDefault();
								var orig = e.originalEvent,
										zoom_0 = (Math.round(orig.scale*100)/100 - 1)*scale;

								var zoom = scale+zoom_0;
								if(zoom < 1){
									zoom = 1;
									translate_X = 0;
									translate_Y = 0;
									buttons.show();
								} else {
									buttons.hide();
									img_w = Math.round(ImageWidth*zoom);
									img_h = Math.round(ImageHeight*zoom);
									if(fix_windowW < img_w){
										var min_translate_x = (img_w - fix_windowW)/2/zoom;
										if(min_translate_x < Math.abs(translate_X)){
											translate_X = min_translate_x*(translate_X < 0 ? -1 : 1);
										}
									} else {
										translate_X = 0;
									}
									if(fix_windowH < img_h){
										var min_translate_y = (img_h - fix_windowH)/2/zoom;
										if(min_translate_y < Math.abs(translate_Y)){
											translate_Y = min_translate_y*(translate_Y < 0 ? -1 : 1);
										}
									} else {
										translate_Y = 0;
									}
								}
								transform_scale = 'scale('+zoom+')';
								transform_translate = 'translate(' + translate_X + 'px, ' + translate_Y + 'px)';
								if(e.type == 'gesturechange'){
									$('img', imgContainer).css({'transform':transform_scale+' '+transform_translate, '-webkit-transform':transform_scale+' '+transform_translate});
								}else if(e.type == 'gestureend'){
									scale = zoom;
								}
							});
							/*touchContainer.bind('touchmove', function (e) {
								touch = e.originalEvent.touches[0];
								currX = touch.clientX;
								if(touch.clientY > currY){
									scalesign = -1;
								} else {
									scalesign = 1
								}
								currY = touch.clientY;
								if(Math.abs(prevY - currY) > 10){
									scale = scale + (prevY - currY)/5000*scalesign;
									if(scale < 1){
										scale = 1;
									}
									$('.gmPhantom_Lightbox img', touchContainer).css({'transform':'scale('+scale+')', '-webkit-transform':'scale('+scale+')'});
								}
							});*/
							/*var prev, curr, scrollX, positionX, initial,
									swipeWidth, startTime, endTime,
									scrollWidth,
									swipeDiv = $('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer'),
									corr = parseInt($('.gmPhantom_Lightbox', swipeDiv).css('margin-left'));

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
									var toload = $('div.' + moduleID + '_img:eq('+(times + Math.max(-2, (0 - times)))+')', this).nextUntil($('div.' + moduleID + '_img:eq('+(times+3)+')', this).get(0)).andSelf().filter('.gm_thumb');
									// ? var toload = $('div.' + moduleID + '_img:eq('+(times + Math.max(-2, (0 - times)))+')', this).nextUntil($('div.' + moduleID + '_img:eq('+(times+3)+')', this).get(0)).addBack().filter('.gm_thumb');
									toload.each(function(){
										var curimg = $('img',this);
										methods.preloadImage(curimg, function(url){
											curimg.attr('src', url).parent().removeClass('gm_thumb');
										});
									});
									$(this).animate({scrollLeft: positionX}, 120);
								});
							}*/

							/*var prev, curr, touch, initial, positionX;

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').bind('touchstart', function(e){
								touch = e.originalEvent.touches[0];
								prev = touch.clientX;
								initial = parseFloat($('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css('margin-left'));
							});

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').bind('touchmove', function(e){
								e.preventDefault();
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').siblings().css('opacity', 0);

								touch = e.originalEvent.touches[0];
								curr = touch.clientX;
								positionX = curr > prev? parseInt($('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css('margin-left')) + (curr - prev) : parseInt($('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css('margin-left')) - (prev - curr);

								prev = curr;
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css('margin-left', positionX);
							});

							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').bind('touchend', function(e){
								e.preventDefault();
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').siblings().css('opacity', 1);

								if(parseFloat($('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css('margin-left')) < 0){
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css({'margin-left': initial, 'opacity': 0});
									methods.nextLightbox();
								}
								else if(parseFloat($('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css('margin-left')) + $('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').width() > $(window).width()){
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css({'margin-left': initial, 'opacity': 0});
									methods.previousLightbox();
								}
								else {
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxContainer').css('margin-left', initial);
								}
							});*/
						},

						initCaption: function(){// Init Caption
							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_CaptionTitle').css('color', '#' + opt.captionTitleColor);
							$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_CaptionText').css('color', '#' + opt.captionTextColor);
							if(typeof(jQuery.fn.jScrollPane) != 'undefined'){
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_CaptionTextContainer').jScrollPane();
							}
						},
						showCaption: function(no){// Show Caption
							if(CaptionTitle[no - 1] == ''){
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_CaptionTitle').css('visibility', 'hidden');
							}
							else {
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_CaptionTitle').css('visibility', 'visible');
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_CaptionTitle .gmPhantom_title').html(CaptionTitle[no - 1]);
							}
							if(CaptionText[no - 1] == ''){
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_CaptionTextContainer').css('visibility', 'hidden');
							}
							else {
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_CaptionTextContainer').css('visibility', 'visible');
								$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_CaptionText').html($("<div />").html(CaptionText[no - 1]).text());
							}
							if(prototypes.isTouchDevice()){
								if((CaptionTitle[no - 1] != '' || CaptionText[no - 1] != '')){
									$('.gmPhantom_info').show();
								} else {
									$('.gmPhantom_info').hide();
								}
							}
						},
						rpCaption:   function(){// Resize & Position Caption
							if(typeof(jQuery.fn.jScrollPane) != 'undefined'){
								setTimeout(function(){
									$('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_CaptionTextContainer').jScrollPane();
								}, 100);
							}
						},

						initSocialShare: function(){
							var HTML = [],
									URL = window.location.href + (window.location.href.indexOf('?') != -1? '&' : '?') + 'gmedia_gallery_id=' + ID + '&gmedia_gallery_share=' + currentItem;

							HTML.push('       <div class="gmAddThisShareButton" id="addthis_' + ID + '-' + currentItem + '">');
							HTML.push('            <a class="addthis_button_expanded" href="#" addthis:url="' + URL + '" addthis:title="' + CaptionTitle[currentItem - 1] + '">');
							HTML.push('                <span>SHARE</span>');
							HTML.push('            </a>');
							HTML.push('       </div>');

							var ui_offset_left = -10000,
									ui_offset_top = -12;
							if(opt.lightboxPosition == 'document'){
								ui_offset_left = 5;
							}
							var addthis_wrapper = $('#gmPhantom_LightboxWrapper_' + ID + ' .gmPhantom_LightboxSocialShare'),
									addthis_cur,
									addthis_config = {
										ui_click:         false,
										ui_offset_left:   ui_offset_left,
										ui_offset_top:    ui_offset_top,
										services_exclude: 'print',
										ui_cobrand: 'Gmedia Gallery'
									},
									addthis_share = {
										url:       URL,
										title:     CaptionTitle[currentItem - 1],
										templates: {
											twitter: '{{title}} {{url}}'
										}
									};
							if(window.addthis != undefined){
								addthis_wrapper.html(HTML.join(''));
								addthis_cur = document.getElementById('addthis_' + ID + '-' + currentItem);
								window.addthis.toolbox(addthis_cur);
							} else {

								$.getScript('http://s7.addthis.com/js/300/addthis_widget.js')
										.done(function(){
											if(window.addthis){
												addthis_wrapper.html(HTML.join(''));
											}
										})
										.fail(function(){
											addthis_wrapper.empty();
										});

							}
						},

						initTooltip: function(){// Init Tooltip
							$('.gmPhantom_ThumbContainer', Container).on('mouseover mousemove', function(e){
								var thumbs_wrapper = $('.gmPhantom_thumbsWrapper', Container),
										mousePositionX = e.clientX - $(thumbs_wrapper).offset().left + parseInt($(thumbs_wrapper).css('margin-left')) + $(document).scrollLeft(),
										mousePositionY = e.clientY - $(thumbs_wrapper).offset().top + parseInt($(thumbs_wrapper).css('margin-top')) + $(document).scrollTop();

								$('.gmPhantom_Tooltip', Container).css('left', mousePositionX - 10);
								$('.gmPhantom_Tooltip', Container).css('top', mousePositionY - $('.gmPhantom_Tooltip', Container).height() - 15);
							});
						},
						showTooltip: function(no){// Resize, Position & Display the Tooltip
							var HTML = [];
							HTML.push(CaptionTitle[no]);
							HTML.push('<div class="gmPhantom_Tooltip_ArrowBorder"></div>');
							HTML.push('<div class="gmPhantom_Tooltip_Arrow"></div>');
							$('.gmPhantom_Tooltip', Container).html(HTML.join(""));

							if(opt.tooltipBgColor != 'css'){
								$('.gmPhantom_Tooltip', Container).css('background-color', '#' + opt.tooltipBgColor);
								$('.gmPhantom_Tooltip_Arrow', Container).css('border-top-color', '#' + opt.tooltipBgColor);
							}
							if(opt.tooltipStrokeColor != 'css'){
								$('.gmPhantom_Tooltip', Container).css('border-color', '#' + opt.tooltipStrokeColor);
								$('.gmPhantom_Tooltip_ArrowBorder', Container).css('border-top-color', '#' + opt.tooltipStrokeColor);
							}
							if(opt.tooltipTextColor != 'css'){
								$('.gmPhantom_Tooltip', Container).css('color', '#' + opt.tooltipTextColor);
							}
							if(CaptionTitle[no] != ''){
								$('.gmPhantom_Tooltip', Container).css('display', 'block');
							}
						}
					},

					prototypes = {
						isIEBrowser:     function(){// Detect the browser IE
							var myNav = navigator.userAgent.toLowerCase();
							return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
						},
						isTouchDevice:   function(){// Detect Touchscreen devices
							return 'ontouchend' in document;
						},
						touchNavigation: function(parent, child){// One finger Navigation for touchscreen devices
							var prevX, prevY, currX, currY, touch, moveTo, thumbsPositionX, thumbsPositionY,
									thumbW = opt.thumbWidth + opt.thumbPaddingRight + opt.thumbPaddingLeft + 2 * opt.thumbBorderSize,
									thumbH = opt.thumbHeight + opt.thumbPaddingTop + opt.thumbPaddingBottom + 2 * opt.thumbBorderSize;


							parent.bind('touchstart', function(e){
								touch = e.originalEvent.touches[0];
								prevX = touch.clientX;
								prevY = touch.clientY;
							});

							parent.bind('touchmove', function(e){
								touch = e.originalEvent.touches[0];
								currX = touch.clientX;
								currY = touch.clientY;
								thumbsPositionX = currX > prevX? parseInt(child.css('margin-left')) + (currX - prevX) : parseInt(child.css('margin-left')) - (prevX - currX);
								thumbsPositionY = currY > prevY? parseInt(child.css('margin-top')) + (currY - prevY) : parseInt(child.css('margin-top')) - (prevY - currY);

								if(thumbsPositionX < (-1) * (child.width() - parent.width())){
									thumbsPositionX = (-1) * (child.width() - parent.width());
								}
								else if(thumbsPositionX > 0){
									thumbsPositionX = 0;
								}
								else {
									e.preventDefault();
								}

								if(thumbsPositionY < (-1) * (child.height() - parent.height())){
									thumbsPositionY = (-1) * (child.height() - parent.height());
								}
								else if(thumbsPositionY > 0){
									thumbsPositionY = 0;
								}
								else {
									e.preventDefault();
								}

								prevX = currX;
								prevY = currY;

								if(parent.width() < child.width()){
									child.css('margin-left', thumbsPositionX);
								}
								if(parent.height() < child.height()){
									child.css('margin-top', thumbsPositionY);
								}
							});

							parent.bind('touchend', function(e){
								e.preventDefault();

								if(thumbsPositionX % (opt.thumbWidth + opt.thumbsSpacing) != 0){
									if((thumbsPosition == 'horizontal') && $('.gMedia_thumbScroller_thumbs', Container).width() > $('.gMedia_thumbScroller_thumbsWrapper', Container).width()){
										if(prevX > touch.clientX){
											moveTo = parseInt(thumbsPositionX / (thumbW + opt.thumbsSpacing)) * (thumbW + opt.thumbsSpacing);
										}
										else {
											moveTo = (parseInt(thumbsPositionX / (thumbW + opt.thumbsSpacing)) - 1) * (thumbW + opt.thumbsSpacing);
										}
										arrowsClicked = true;

										$('.gMedia_thumbScroller_thumbs', Container).stop(true, true).animate({'margin-left': moveTo}, thumbsNavigationArrowsSpeed, function(){
											arrowsClicked = false;
										});
									}
								}

								if(thumbsPositionY % (opt.thumbHeight + opt.thumbsSpacing) != 0){
									if((thumbsPosition == 'vertical') && $('.gMedia_thumbScroller_thumbs', Container).height() > $('.gMedia_thumbScroller_thumbsWrapper', Container).height()){
										if(prevY > touch.clientY){
											moveTo = parseInt(thumbsPositionY / (thumbH + opt.thumbsSpacing)) * (thumbH + opt.thumbsSpacing);
										}
										else {
											moveTo = (parseInt(thumbsPositionY / (thumbH + opt.thumbsSpacing)) - 1) * (thumbH + opt.thumbsSpacing);
										}
										arrowsClicked = true;

										$('.gMedia_thumbScroller_thumbs', Container).stop(true, true).animate({'margin-top': moveTo}, thumbsNavigationArrowsSpeed, function(){
											arrowsClicked = false;
										});
									}
								}
							});
						},

						openLink:       function(url, target){// Open a link.
							switch(target.toLowerCase()){
								case '_blank':
									window.open(url);
									break;
								case '_top':
									top.location.href = url;
									break;
								case '_parent':
									parent.location.href = url;
									break;
								default:
									window.location = url;
							}
						},
						$_GET:          function(variable){
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
						doHideBuster:   function(item){// Make all parents & current item visible
							var parent = item.parent(),
									items = [];

							if(item.prop('tagName') != undefined && item.prop('tagName').toLowerCase() != 'body'){
								items = prototypes.doHideBuster(parent);
							}

							if(item.css('display') == 'none'){
								item.css('display', 'block');
								items.push(item);
							}

							return items;
						},
						undoHideBuster: function(items){// Hide items in the array
							var i;

							for(i = 0; i < items.length; i++){
								items[i].css('display', 'none');
							}
						}
					};

			return methods.init.apply(this);
		}
	})(jQuery, window, document);
}