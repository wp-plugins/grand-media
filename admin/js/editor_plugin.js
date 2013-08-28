/**
 * GrandMedia plugin.
 */
(function () {
	var DOM = tinymce.DOM;

	tinymce.create('tinymce.plugins.GrandMedia', {

		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {object} ed tinymce.Editor instance that the plugin is initialized in.
		 */
		init: function (ed) {
			var t = this;

			/*ed.addCommand('gMedia_redraw', function () {
				t._handleFunction(ed);
			});*/


			/** "onPreInit","onBeforeRenderUI","onPostRender","onInit","onRemove","onActivate","onDeactivate","onClick","onEvent","onMouseUp","onMouseDown","onDblClick","onKeyDown","onKeyUp","onKeyPress","onContextMenu","onSubmit","onReset","onPaste","onPreProcess","onPostProcess","onBeforeSetContent","onBeforeGetContent","onSetContent","onGetContent","onLoadContent","onSaveContent","onNodeChange","onChange","onBeforeExecCommand","onExecCommand","onUndo","onRedo","onVisualAid","onSetProgressState" */
			ed.onInit.add(function (ed) {

				// make sure these run last
				/*ed.onClick.add(function (ed, e) {
					if (e.target.nodeName == 'IMG' && ed.dom.hasClass(e.target, 'gm-image')) {
						console.log(e.target);
					}
				});*/

				/*ed.onMouseUp.add(function (ed, e) {
					if (e.target.nodeName == 'INS' && ed.dom.hasClass(e.target, 'mceGMgallery')) {
						if (!ed.dom.hasClass(e.target, 'selected')) {
							var nParent = ed.selection.getNode();//.parentNode;
							ed.selection.select(nParent);
							ed.dom.addClass(e.target, 'selected');
						} else {
							ed.selection.collapse(false);
							ed.dom.removeClass(e.target, 'selected');
						}
					} else {
						ed.dom.removeClass(ed.dom.select('ins.mceGMgallery'), 'selected');
					}
				});
				tinymce.dom.Event.add(ed.getBody(), 'dragend', function (e) {
					if (ed.dom.select('ins.mceGMgallery')) {
						ed.selection.collapse(false);
						ed.dom.removeClass(ed.dom.select('ins.mceGMgallery'), 'selected');
					}
				});*/

				if ('undefined' != typeof(jQuery)) {
					ed.onKeyUp.add(function (ed, e, o) {
						var k = e.keyCode || e.charCode;
						/*if (k == 35 || k == 36 || k == 37 || k == 38 || k == 39 || k == 40) {
							if (ed.dom.select('ins.mceGMgallery')) {
								//ed.selection.collapse(false);
								ed.dom.removeClass(ed.dom.select('ins.mceGMgallery'), 'selected');
							}
						}*/
						if (k == 8 || k == 13 || k == 46) {
							var m, content = ed.getContent();
							m = content.match(/\[gmedia \s*id=(\d+)\s*?\]/g);
							jQuery('#gMedia-galleries-list li.gMedia-gallery-li').removeClass('gMedia-selected');
							if (m) {
								jQuery.each(m, function (i, shcode) {
									var id = shcode.replace(/\[gm.*id=(\d+).*?\]/, '$1');
									jQuery('#gmModule-' + id).addClass('gMedia-selected');
								});
							}
						}

					});
				}


			});

			// Add listeners to handle function
			//t._handleFunction(ed);

		},

		getInfo: function () {
			return {
				longname : 'Gmedia Gallery',
				author   : 'Rattus',
				authorurl: 'http://codeasily.com',
				infourl  : 'http://codeasily.com',
				version  : '1.1'
			};
		},

		_handleFunction: function (ed) {

			// Load plugin specific CSS into editor
			ed.onInit.add(function () {
				ed.dom.loadCSS(gMediaGlobalVar.pluginPath + '/admin/css/editor_plugin.css');
			});

			/*
			var galleryHTML = '<ins class="mceGMgallery" title="ID#$1">$1</ins>';

			// Replace morebreak with images
			ed.onBeforeSetContent.add(function (ed, o) {
				if (o.content) {
					if ('undefined' != typeof(jQuery)) {
						var m = o.content.match(/\[gmedia[ ]+id=(\d+)[ ]?\]/g);
						jQuery('#gMedia-galleries-list li.gMedia-gallery-li').removeClass('gMedia-selected');
						if (m) {
							jQuery.each(m, function (i, shcode) {
								var id = shcode.replace(/\[gmedia[ ]+id=(\d+)[ ]?\]/, '$1');
								jQuery('#gmModule-' + id).addClass('gMedia-selected');
							});
						}
					}
					o.content = o.content.replace(/\[gmedia[ ]+id=(\d+)[ ]?\]/g, galleryHTML);
				}
			});

			// Replace images with morebreak
			ed.onPostProcess.add(function (ed, o) {
				if (o.get) {
					if ('undefined' != typeof(jQuery)) {
						jQuery('#gMedia-galleries-list li.gMedia-gallery-li').removeClass('gMedia-selected');
					}
					o.content = o.content.replace(/(?:<ins class="mceGMgallery[^>]*>)(.*?)(?:<\/ins>)/g, function (a, im) {
						if (a.indexOf('title="ID#') !== -1) {
							var m = (m = a.match(/title="ID#(\d+)"/)) ? m[1] : '';
							im = '[gmedia id=' + m + ']';

							if ('undefined' != typeof(jQuery)) {
								jQuery('#gmModule-' + m).addClass('gMedia-selected');
							}
						} else {
							im = '';
						}

						return im;
					});
				}
			});
			*/
		}

	});

	// Register plugin
	tinymce.PluginManager.add('gmedia', tinymce.plugins.GrandMedia);

})();
