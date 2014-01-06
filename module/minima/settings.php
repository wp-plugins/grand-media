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
					'desc'  => 'Create query to show gmedia based on various selection criteria. You can use this to display gmedia from a category, with specified tags, random gmedia etc.',
					'std'   => array(),
					'type'  => 'query_vis',
					'param' => array(
						'mime_type' => 'image',
						'multitab'  => 'true'
					),
					'class' => ''
				)
			)
		),
		'section1'        => array(

			'title'  => 'Gallery Settings',
			'fields' => array(
				array(
					'id'    => 'width',
					'label' => 'Width',
					'desc'  => 'Width (number or number with %). Default value: 100%. Set the width of the gallery',
					'std'   => '100%',
					'type'  => 'text',
					'param' => '',
					'class' => ''
				),
				array(
					'id'    => 'height',
					'label' => 'Height',
					'desc'  => 'Height (number or number with %). Default value: 500. Set the height of the gallery. Not recommended to set value with % (only if you know what you do)',
					'std'   => '500',
					'type'  => 'text',
					'param' => '',
					'class' => ''
				),
				array(
					'id'      => 'autoSlideshow',
					'label'   => 'Automatic Slideshow',
					'desc'    => 'Default value: On.',
					'std'     => array( 'true' ),
					'type'    => 'checkbox',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => 'true',
							'label' => 'On/Off'
						)
					)
				),
				array(
					'id'    => 'slideshowDelay',
					'label' => 'Slideshow Delay',
					'desc'  => 'Value from 0 to 30. Default value: 10.',
					'std'   => '10',
					'type'  => 'text',
					'param' => array('type'=>'number', 'min'=>0, 'max'=>30),
					'class' => ''
				),
				array(
					'id'    => 'thumbnailsWidth',
					'label' => 'Thumbnail Width',
					'desc'  => 'Value from 40 to 300. Default value: 75.',
					'std'   => '75',
					'type'  => 'text',
					'param' => array('type'=>'number', 'min'=>40, 'max'=>300),
					'class' => ''
				),
				array(
					'id'    => 'thumbnailsHeight',
					'label' => 'Thumbnail Height',
					'desc'  => 'Value from 40 to 300. Default value: 75.',
					'std'   => '75',
					'type'  => 'text',
					'param' => array('type'=>'number', 'min'=>40, 'max'=>300),
					'class' => ''
				),
				array(
					'id'      => 'property0',
					'label'   => 'Flash Object WMode',
					'desc'    => 'Default value: Opaque. If \'transparent\' - "Background Color" option is ignored, but you can position the absolute elements over the flash',
					'std'     => 'opaque',
					'type'    => 'select',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => 'opaque',
							'label' => 'Opaque'
						),
						array(
							'value' => 'transparent',
							'label' => 'Transparent'
						)
					)
				),
				array(
					'id'    => 'property1',
					'label' => 'Background Color',
					'desc'  => 'Background Color (color hex code). Default value: ffffff. Set gallery background color',
					'std'   => 'ffffff',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'      => 'counterStatus',
					'label'   => 'Show image views/likes counter',
					'desc'    => 'Default value: On.',
					'std'     => array( 'true' ),
					'type'    => 'checkbox',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => 'true',
							'label' => 'On/Off'
						)
					)
				),
				array(
					'id'    => 'barBgColor',
					'label' => 'Header & Footer Background Color',
					'desc'  => 'Header & Footer Background Color (color hex code). Default value: 282828.',
					'std'   => '282828',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'labelColor',
					'label' => 'Label Color (Buttons)',
					'desc'  => 'Buttons Color (color hex code). Default value: 75c30f',
					'std'   => '75c30f',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'labelColorOver',
					'label' => 'Label on MouseOver Color (Buttons)',
					'desc'  => 'Default value: ffffff',
					'std'   => 'ffffff',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'backgroundColorButton',
					'label' => 'Buttons Background Color',
					'desc'  => 'Default value: 000000',
					'std'   => '000000',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'descriptionBGColor',
					'label' => 'Description BG Color',
					'desc'  => 'Background for the image description that appears on mouseover. Default value: 000000',
					'std'   => '000000',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'descriptionBGAlpha',
					'label' => 'Image Description Background Alpha',
					'desc'  => 'Background Alpha (value from 0 to 100). Default value: 75. Opacity of the image description background',
					'std'   => '75',
					'type'  => 'text',
					'param' => array('type'=>'number','min'=>0,'max'=>100,'step'=>5),
					'class' => ''
				),
				array(
					'id'    => 'imageTitleColor',
					'label' => 'Image Title Color',
					'desc'  => 'Default value: 75c30f. Color of text for image title in the description',
					'std'   => '75c30f',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'galleryTitleFontSize',
					'label' => 'Gallery Title Font Size',
					'desc'  => 'Value from 10 to 30. Default value: 15',
					'std'   => '15',
					'type'  => 'text',
					'param' => array('type'=>'number','min'=>10,'max'=>30),
					'class' => ''
				),
				array(
					'id'    => 'titleFontSize',
					'label' => 'Image Title Font Size',
					'desc'  => 'Value from 10 to 30. Default value: 12',
					'std'   => '12',
					'type'  => 'text',
					'param' => array('type'=>'number','min'=>10,'max'=>30),
					'class' => ''
				),
				array(
					'id'    => 'imageDescriptionColor',
					'label' => 'Image Description Color',
					'desc'  => 'Default value: ffffff. Color of text for image description',
					'std'   => 'ffffff',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				),
				array(
					'id'    => 'descriptionFontSize',
					'label' => 'Image Description Font Size',
					'desc'  => 'Value from 10 to 30. Default value: 12',
					'std'   => '12',
					'type'  => 'text',
					'param' => array('type'=>'number','min'=>10,'max'=>30),
					'class' => ''
				),
				array(
					'id'    => 'linkColor',
					'label' => 'Link Color (image description)',
					'desc'  => 'Default value: 75c30f',
					'std'   => '75c30f',
					'type'  => 'text',
					'param' => '',
					'class' => 'color'
				)/*,
				array(
					'id'    => 'backButtonColorText',
					'label' => 'Back Button Text Color',
					'desc'  => 'Default value: ffffff. Only for Full Window template',
					'std'   => 'ffffff',
					'type'  => 'text',
					'param' => 'hidden',
					'class' => 'color'
				),
				array(
					'id'    => 'backButtonColorBg',
					'label' => 'Back Button Background Color',
					'desc'  => 'Default value: 000000. Only for Full Window template',
					'std'   => '000000',
					'type'  => 'text',
					'param' => 'hidden',
					'class' => 'color'
				)*/
			)
		),
		'section2'        => array(

			'title'  => 'Advanced Settings',
			'fields' => array(
				array(
					'id'    => 'customCSS',
					'label' => 'Custom CSS',
					'desc'  => 'You can enter custom style rules into this box if you\'d like. IE: <i>a{color: red !important;}</i><br />This is an advanced option! This is not recommended for users not fluent in CSS... but if you do know CSS, anything you add here will override the default styles',
					'std'   => '',
					'type'  => 'css',
					'param' => '10',
					'class' => ''
				)/*,
				array(
					'id'      => 'loveLink',
					'label'   => 'Display LoveLink?',
					'desc'    => 'Selecting "Yes" will show the lovelink icon (codeasily.com) somewhere on the gallery',
					'std'     => '',
					'type'    => 'checkbox',
					'param'   => '',
					'class'   => '',
					'choices' => array(
						array(
							'value' => 'true',
							'label' => 'Yes/No'
						)
					)
				)*/
			)
		)

	)
);
