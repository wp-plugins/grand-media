<?php
$module_ot = array(
	'settings' => array(

		'general_default' => array(

			'title'  => 'Basic Setup',
			'fields' => array(
				array(
					'id'    => 'general_notes',
					'label' => 'Welcome!',
					'desc'  => 'Welcome to the Module Options page! From this panel, you\'ll be able to select the custom options that will fit your site. Have Fun!',
					'std'   => '',
					'type'  => 'textblock',
					'param' => '',
					'class' => ''
				),
				array(
					'id'    => 'name',
					'label' => 'Gallery Name',
					'desc'  => '',
					'std'   => '',
					'type'  => 'text',
					'param' => '',
					'class' => ''
				),
				array(
					'id'    => 'description',
					'label' => 'Gallery Description',
					'desc'  => '',
					'std'   => '',
					'type'  => 'textarea',
					'param' => '10',
					'class' => ''
				),
				array(
					'id'    => 'gMediaQuery',
					'label' => 'Query Builder',
					'desc'  => 'Create query to show gmedia based on various selection criteria. You can use this to display gmedia from a album, with specified tags, random gmedia etc. ',
					'std'   => array(),
					'type'  => 'query_vis',
					'param' => array(
						'mime_type' => 'image',
						'multitab'  => 'false'
					),
					'class' => ''
				)
			)
		),
		'section1'        => array(

			'title'  => 'General View',
			'fields' => array(
				array(
					'id'    => 'width',
					'label' => 'Width',
					'desc'  => 'Width (value in pixels). Default value: 900. Set the width of the gallery. If Responsive Layout enabled this will be max width of gallery.',
					'std'   => '900',
					'type'  => 'text',
					'param' => array('type'=>'number'),
					'class' => ''
				),
				array(
					'id'    => 'height',
					'label' => 'Height',
					'desc'  => 'Height (value in pixels). Default value: 0. Set the height of the gallery. If you set the value to 0 all thumbnails are going to be displayed. If Height value is 0, then Thumbnail Rows value ignored and Thumbnail Columns is a max value.',
					'std'   => '0',
					'type'  => 'text',
					'param' => array('type'=>'number'),
					'class' => ''
				),
				array(
					'id'      => 'responsiveEnabled',
					'label'   => 'Responsive Layout',
					'desc'    => 'Responsive Enabled (true, false). Default value: true. Enable responsive layout. If value is true, then Width value will be max width of gallery',
					'std'     => 'true',
					'type'    => 'select',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => 'true',
							'label' => 'True'
						),
						array(
							'value' => 'false',
							'label' => 'False'
						)
					)
				),
				array(
					'id'    => 'thumbCols',
					'label' => 'Thumbnail Columns',
					'desc'  => 'Number of Columns (number, 0 = auto). Default value: 0. Set the number of columns for the grid. If value is 0, then number of columns will be relative to gallery width or relative to Thumbnail Rows.',
					'std'   => '0',
					'type'  => 'text',
					'param' => 'number',
					'class' => ''
				),
				array(
					'id'    => 'thumbRows',
					'label' => 'Thumbnail Rows',
					'desc'  => 'Number of Lines (number, 0 = auto). Default value: 0. Set the number of lines for the grid. This will be ignored if Thumbnail Columns value is not 0 or if Height value is 0.',
					'std'   => '0',
					'type'  => 'text',
					'param' => 'number',
					'class' => ''
				),
				array(
					'id'      => 'thumbsNavigation',
					'label'   => 'Grid Navigation',
					'desc'    => 'Default value: Mouse Move. Set how you navigate through the thumbnails. Ignore this option if Height value is 0.',
					'std'     => 'mouse',
					'type'    => 'select',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => 'mouse',
							'label' => 'Mouse Move'
						),
						array(
							'value' => 'scroll',
							'label' => 'Scroll Bars'
						)
					)

				),
				array(
					'id'    => 'bgColor',
					'label' => 'Background Color',
					'desc'  => 'Background Color (color hex code). Default value: ffffff. Set gallery background color.',
					'std'   => 'ffffff',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'bgAlpha',
					'label' => 'Background Alpha',
					'desc'  => 'Background Alpha (value from 0 to 100). Default value: 0 (transparent). Set gallery background alpha.',
					'std'   => '0',
					'type'  => 'text',
					'param' => array('type'=>'number','max'=>'100','step'=>'5'),
					'class' => ''
				)
			)
		),
		'section2'        => array(

			'title'  => 'Thumb Grid General',
			'fields' => array(
				array(
					'id'    => 'thumbWidth',
					'label' => 'Thumbnail Width',
					'desc'  => 'Thumbnail Width (the size in pixels). Default value: 150. Set the width of a thumbnail.',
					'std'   => '150',
					'type'  => 'text',
					'param' => 'number',
					'class' => ''
				),
				array(
					'id'    => 'thumbHeight',
					'label' => 'Thumbnail Height',
					'desc'  => 'Thumbnail Height (the size in pixels). Default value: 150. Set the height of a thumbnail.',
					'std'   => '150',
					'type'  => 'text',
					'param' => 'number',
					'class' => ''
				),
				array(
					'id'    => 'thumbsSpacing',
					'label' => 'Thumbnails Spacing',
					'desc'  => 'Thumbnails Spacing (value in pixels). Default value: 10. Set the space between thumbnails.',
					'std'   => '10',
					'type'  => 'text',
					'param' => 'number',
					'class' => ''
				),
				array(
					'id'    => 'thumbsVerticalPadding',
					'label' => 'Thumbnails Vertical Padding',
					'desc'  => 'Thumbnails Vertical Padding (value in pixels). Default value: 5. Set the vertical padding for the thumbnails grid.',
					'std'   => '5',
					'type'  => 'text',
					'param' => 'number',
					'class' => ''
				),
				array(
					'id'    => 'thumbsHorizontalPadding',
					'label' => 'Thumbnails Horizontal Padding',
					'desc'  => 'Thumbnails Horizontal Padding (value in pixels). Default value: 5. Set the horizontal padding for the thumbnails grid.',
					'std'   => '3',
					'type'  => 'text',
					'param' => 'number',
					'class' => ''
				),
				array(
					'id'      => 'thumbsAlign',
					'label'   => 'Thumbnails Align',
					'desc'    => 'Default value: Left. Align thumbnails grid in container. Applied only if grid width less than gallery width',
					'std'     => 'left',
					'type'    => 'select',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => 'left',
							'label' => 'Left'
						),
						array(
							'value' => 'center',
							'label' => 'Center'
						),
						array(
							'value' => 'right',
							'label' => 'Right'
						)
					)

				)
			)
		),
		'section3'        => array(

			'title'  => 'Thumbnail Style',
			'fields' => array(

				array(
					'id'    => 'thumbAlpha',
					'label' => 'Thumbnail Alpha',
					'desc'  => 'Thumbnail Alpha (value from 0 to 100). Default value: 85. Set the transparancy of a thumbnail.',
					'std'   => '85',
					'type'  => 'text',
					'param' => array('type'=>'number','max'=>'100','step'=>'5'),
					'class' => ''
				),
				array(
					'id'    => 'thumbAlphaHover',
					'label' => 'Thumbnail Alpha Hover',
					'desc'  => 'Thumbnail Alpha Hover (value from 0 to 100). Default value: 100. Set the transparancy of a thumbnail when hover.',
					'std'   => '100',
					'type'  => 'text',
					'param' => array('type'=>'number','max'=>'100','step'=>'5'),
					'class' => ''
				),
				array(
					'id'    => 'thumbBorderSize',
					'label' => 'Thumbnail Border Size',
					'desc'  => 'Thumbnail Border Size (value in pixels). Default value: 1. Set the size of a thumbnail\'s border.',
					'std'   => '1',
					'type'  => 'text',
					'param' => 'number',
					'class' => ''
				),
				array(
					'id'    => 'thumbBorderColor',
					'label' => 'Thumbnail Border Color',
					'desc'  => 'Thumbnail Border Color (color hex code). Default value: cccccc. Set the color of a thumbnail\'s border.',
					'std'   => 'cccccc',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'thumbPadding',
					'label' => 'Thumbnail Padding',
					'desc'  => 'Thumbnail Padding (value in pixels). Default value: 5. Set padding for the thumbnail.',
					'std'   => '5',
					'type'  => 'text',
					'param' => 'number',
					'class' => ''
				)
			)
		),
		'section4'        => array(

			'title'  => 'Thumbnails Title',
			'fields' => array(
				array(
					'id'      => 'thumbsInfo',
					'label'   => 'Display Thumbnails Title',
					'desc'    => 'Default value: Label. Display a small info text on the thumbnails, a tooltip or a label.',
					'std'     => 'label',
					'type'    => 'select',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => 'label',
							'label' => 'Label'
						),
						array(
							'value' => 'tooltip',
							'label' => 'Tooltip'
						),
						array(
							'value' => 'none',
							'label' => 'None'
						)
					)

				),
				array(
					'id'    => 'tooltipBgColor',
					'label' => 'Tooltip Background Color',
					'desc'  => 'Tooltip Background Color (color hex code). Default value: ffffff. Set tooltip background color. Ignore this if Display Thumbnails Title value is not Tooltip.',
					'std'   => 'ffffff',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'tooltipStrokeColor',
					'label' => 'Tooltip Stroke Color',
					'desc'  => 'Tooltip Stroke Color (color hex code). Default value: 000000. Set tooltip stroke color. Ignore this if Display Thumbnails Title value is not Tooltip.',
					'std'   => '000000',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'tooltipTextColor',
					'label' => 'Tooltip Text Color',
					'desc'  => 'Tooltip Text Color (color hex code). Default value: 000000. Set tooltip text color. Ignore this if Display Thumbnails Title value is not Tooltip.',
					'std'   => '000000',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				)
			)
		),
		'section6'        => array(

			'title'  => 'Lightbox Settings',
			'fields' => array(
				array(
					'id'    => 'lightboxPosition',
					'label' => 'Lightbox Position',
					'desc'  => 'Lightbox Position (document, gallery). Default value: Document. If the value is Document the lightbox is displayed over the web page fitting in the browser\'s window, else the lightbox is displayed in the gallery\'s container.',
					'std'     => 'document',
					'type'    => 'select',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => 'document',
							'label' => 'Document'
						),
						array(
							'value' => 'gallery',
							'label' => 'Gallery'
						)
					)
				),
				array(
					'id'      => 'lightboxWindowColor',
					'label'   => 'Lightbox Window Color',
					'desc'    => 'Lightbox Window Color (color hex code). Default value: 000000. Set the color for the lightbox window.',
					'std'     => '000000',
					'type'    => 'text',
					'param'   => '',
					'class'   => 'color'
				),
				array(
					'id'      => 'lightboxWindowAlpha',
					'label'   => 'Lightbox Window Alpha',
					'desc'    => 'Lightbox Window Alpha (value from 0 to 100). Default value: 80. Set the transparancy for the lightbox window.',
					'std'     => '80',
					'type'    => 'text',
					'param'   => array('type'=>'number','max'=>'100','step'=>'5'),
					'class'   => ''
				),
				array(
					'id'      => 'socialShareEnabled',
					'label'   => 'Social Share Enabled',
					'desc'    => 'Social Share Enabled (True, False). Default value: True. Enable AddThis Social Share.',
					'std'     => 'true',
					'type'    => 'select',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => 'true',
							'label' => 'True'
						),
						array(
							'value' => 'false',
							'label' => 'False'
						)
					)
				)
			)
		),
		'section7'        => array(

			'title'  => 'Advanced Settings',
			'fields' => array(
				array(
					'id'    => 'customCSS',
					'label' => 'Custom CSS',
					'desc'  => 'You can enter custom style rules into this box if you\'d like. IE: <i>a{color: red !important;}</i><br />This is an advanced option! This is not recommended for users not fluent in CSS... but if you do know CSS, anything you add here will override the default styles.',
					'std'   => '',
					'type'  => 'textarea-simple',
					'param' => '10',
					'class' => ''
				)/*,
				array(
					'id'      => 'loveLink',
					'label'   => 'Display LoveLink?',
					'desc'    => 'Selecting "Yes" will show the lovelink icon (codeasily.com) somewhere on the gallery.',
					'std'     => '',
					'type'    => 'checkbox',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => '1',
							'label' => 'Yes'
						)
					)
				)*/
			)
		)

	)
);
