<?php$module_ot = array(	'settings' => array(		'general_default' => array(			'title'  => 'Basic Setup',			'fields' => array(				array(					'id'    => 'general_notes',					'label' => 'Welcome!',					'desc'  => 'Welcome to the Module Options page! From this panel, you\'ll be able to select the custom options that will fit your site. Have Fun!',					'std'   => '',					'type'  => 'textblock',					'param' => '',					'class' => ''				),				array(					'id'    => 'name',					'label' => 'Gallery Name',					'desc'  => '',					'std'   => '',					'type'  => 'text',					'param' => '',					'class' => ''				),				array(					'id'    => 'description',					'label' => 'Gallery Description',					'desc'  => '',					'std'   => '',					'type'  => 'textarea',					'param' => '10',					'class' => ''				),				array(					'id'    => 'shortcode',					'label' => 'Shortcode',					'desc'  => 'Name of JS function, Shortcode ID',					'std'   => 'grandMediaAfflux',					'type'  => 'hidden',					'param' => '',					'class' => ''				),				array(					'id'    => 'gMediaQuery',					'label' => 'Query Builder',					'desc'  => 'Create query to show gmedia based on various selection criteria. You can use this to display gmedia from a category, with specified tags, random gmedia etc. ',					'std'   => array(),					'type'  => 'query_vis',					'param' => array(						'mime_type' => 'image',						'multitab'  => 'true'					),					'class' => ''				)			)		),		'section1'        => array(			'title'  => 'General View',			'fields' => array(				array(					'id'    => 'width',					'label' => 'Width',					'desc'  => 'Width (number or number with %). Default value: 100%. Set the width of the gallery.',					'std'   => '100%',					'type'  => 'text',					'param' => '',					'class' => ''				),				array(					'id'    => 'height',					'label' => 'Height',					'desc'  => 'Height (number or number with %). Default value: 500. Set the height of the gallery. Not recommended to set value with % (only if you know what you do.',					'std'   => '500',					'type'  => 'text',					'param' => '',					'class' => ''				),				array(					'id'      => 'wmode',					'label'   => 'Flash Object WMode',					'desc'    => 'Default value: Opaque. If \'transparent\' - "Background Color" option is ignored, but you can position the absolute elements over the flash.',					'std'     => 'opaque',					'type'    => 'select',					'param'   => '',					'class'   => '',					'choices' => array(						array(							'value' => 'opaque',							'label' => 'Opaque'						),						array(							'value' => 'window',							'label' => 'Window'						),						array(							'value' => 'transparent',							'label' => 'Transparent'						)					)				)/*,				array(					'id'      => 'swfMouseWheel',					'label'   => 'SWF Mouse Wheel',					'desc'    => 'Default value: Off. Turn On/Off mouse wheel detection over Gallery.',					'std'     => '',					'type'    => 'checkbox',					'param'   => '',					'class'   => '',					'choices' => array(						array(							'value' => 'true',							'label' => 'On/Off'						)					)				)*/,				array(					'id'      => 'imageZoom',					'label'   => 'Image Zoom',					'desc'    => 'Default value: Fill.',					'std'     => 'FILL',					'type'    => 'select',					'param'   => '',					'class'   => '',					'choices' => array(						array(							'value' => 'FILL',							'label' => 'Fill'						),						array(							'value' => 'FIT',							'label' => 'Fit'						)					)				),				array(					'id'      => 'autoSlideshow',					'label'   => 'Automatic Slideshow',					'desc'    => 'Default value: On.',					'std'     => array( 'true' ),					'type'    => 'checkbox',					'param'   => '',					'class'   => '',					'choices' => array(						array(							'value' => 'true',							'label' => 'On/Off'						)					)				),				array(					'id'    => 'slideshowDelay',					'label' => 'Slideshow Delay',					'desc'  => 'Value from 0 to 30. Default value: 10.',					'std'   => '10',					'type'  => 'text',					'param' => 'number',					'class' => ''				),				array(					'id'    => 'thumbHeight',					'label' => 'Thumbnail Height',					'desc'  => 'Thumbnail Height (the size in pixels). Default value: 100. Set the height of a thumbnail.',					'std'   => '100',					'type'  => 'text',					'param' => 'number',					'class' => ''				),				array(					'id'      => 'descrVisOnMouseover',					'label'   => 'Show Description',					'desc'    => 'Default value: On. Show image description on mouseover',					'std'     => array( 'true' ),					'type'    => 'checkbox',					'param'   => '',					'class'   => '',					'choices' => array(						array(							'value' => 'true',							'label' => 'On/Off'						)					)				)/*,				array(					'id'      => 'hitcounter',					'label'   => 'Count Image Views',					'desc'    => 'Default value: On.',					'std'     => array( 'true' ),					'type'    => 'checkbox',					'param'   => '',					'class'   => '',					'choices' => array(						array(							'value' => 'true',							'label' => 'On/Off'						)					)				)*/			)		),		'section2'        => array(			'title'  => 'Colors and Fonts',			'fields' => array(				array(					'id'    => 'bgColor',					'label' => 'Background Color',					'desc'  => 'Background Color (color hex code). Default value: ffffff. Set gallery background color.',					'std'   => 'ffffff',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'imagesBgColor',					'label' => 'Images Background Color',					'desc'  => 'Background Color (color hex code). Default value: 000000. Set loading images background color.',					'std'   => '000000',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'barsBgColor',					'label' => 'Bars Background Color',					'desc'  => 'Background Color (color hex code). Default value: 000000. Background color for Categories bar, Thumbnails bar and Scroll bar.',					'std'   => '000000',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'catButtonColor',					'label' => 'Category Buttons Color',					'desc'  => 'Default value: 75c30f.',					'std'   => '75c30f',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'catButtonColorHover',					'label' => 'Category Buttons Color on Mouseover',					'desc'  => 'Default value: ffffff.',					'std'   => 'ffffff',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'scrollBarTrackColor',					'label' => 'Scroll Bar Track Color',					'desc'  => 'Default value: 75c30f.',					'std'   => '75c30f',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'scrollBarButtonColor',					'label' => 'Scroll Bar Button Color',					'desc'  => 'Default value: f1f1f1.',					'std'   => 'f1f1f1',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'thumbBgColor',					'label' => 'Thumbnail BG Color',					'desc'  => 'Background Color (color hex code). Default value: ffffff. Background of the thumbnail placeholder while thumbnail is loading.',					'std'   => 'ffffff',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'thumbLoaderColor',					'label' => 'Thumbnail Loader Color',					'desc'  => 'Default value: 75c30f.',					'std'   => '75c30f',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'imageTitleColor',					'label' => 'Image Title Color',					'desc'  => 'Default value: 75c30f. Color of text for image title in the description.',					'std'   => '75c30f',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'imageTitleFontSize',					'label' => 'Image Title Font Size',					'desc'  => 'Value from 10 to 30. Default value: 14.',					'std'   => '14',					'type'  => 'text',					'param' => 'number',					'class' => ''				),				array(					'id'    => 'imageDescrColor',					'label' => 'Image Description Color',					'desc'  => 'Default value: ffffff. Color of text for image description.',					'std'   => 'ffffff',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'imageDescrFontSize',					'label' => 'Image Description Font Size',					'desc'  => 'Value from 10 to 30. Default value: 12.',					'std'   => '12',					'type'  => 'text',					'param' => 'number',					'class' => ''				),				array(					'id'    => 'imageDescrBgColor',					'label' => 'Image Description BG Color',					'desc'  => 'Default value: 000000. Background for the image description that appears on mouseover.',					'std'   => '000000',					'type'  => 'text',					'param' => '',					'class' => 'color'				),				array(					'id'    => 'imageDescrBgAlpha',					'label' => 'Image Description Background Alpha',					'desc'  => 'Background Alpha (value from 0 to 100). Default value: 85. Opacity of the image description background.',					'std'   => '85',					'type'  => 'text',					'param' => 'number',					'class' => ''				)/*,				array(					'id'    => 'backButtonTextColor',					'label' => 'Back Button Text Color',					'desc'  => 'Default value: ffffff. Only for Full Window template.',					'std'   => 'ffffff',					'type'  => 'text',					'param' => 'hidden',					'class' => 'color'				),				array(					'id'    => 'backButtonBgColor',					'label' => 'Back Button Background Color',					'desc'  => 'Default value: 000000. Only for Full Window template.',					'std'   => '000000',					'type'  => 'text',					'param' => 'hidden',					'class' => 'color'				)*/			)		),		'section4'        => array(			'title'  => 'Advanced Settings',			'fields' => array(				array(					'id'    => 'customCSS',					'label' => 'Custom CSS',					'desc'  => 'You can enter custom style rules into this box if you\'d like. IE: <i>a{color: red !important;}</i><br />This is an advanced option! This is not recommended for users not fluent in CSS... but if you do know CSS, anything you add here will override the default styles.',					'std'   => '',					'type'  => 'css',					'param' => '10',					'class' => ''				)/*,				array(					'id'      => 'loveLink',					'label'   => 'Display LoveLink?',					'desc'    => 'Selecting "Yes" will show the lovelink icon (codeasily.com) somewhere on the gallery.',					'std'     => '',					'type'    => 'checkbox',					'param'   => '',					'class'   => '',					'choices' => array(						array(							'value' => 'true',							'label' => 'Yes/No'						)					)				)*/			)		)	));