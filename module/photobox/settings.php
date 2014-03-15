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

			'title'  => 'Gallery Settings',
			'fields' => array(
				array(
					'id'      => 'history',
					'label'   => 'Browser History',
					'desc'    => 'Enable/disable HTML5 history using hash urls',
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
					'id'    => 'time',
					'label' => 'Slideshow Delay',
					'desc'  => '(default: 3000) minimum 1000ms allowed. The time in miliseconds when autoplaying a gallery. Set as \'0\' to hide the autoplay button completely.',
					'std'   => '3000',
					'type'  => 'text',
					'param' => array('type'=>'number','min'=>'0','step'=>'1000'),
					'class' => ''
				),
				array(
					'id'      => 'autoplay',
					'label'   => 'Auto Slideshow',
					'desc'    => 'Should the gallery autoplay on start or not.',
					'std'     => 'false',
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
					'id'      => 'loop',
					'label'   => 'Gallery Loop',
					'desc'    => 'Loop back to last image before the first one and to the first image after last one.',
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
					'id'      => 'thumbs',
					'label'   => 'Thumbnails in Modal',
					'desc'    => 'Show thumbs of all the images in the gallery at the bottom.',
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
					'id'      => 'image_title',
					'label'   => 'Image Title',
					'desc'    => 'Show the title of the image.',
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
					'id'      => 'counter',
					'label'   => 'Image Counter',
					'desc'    => 'Show the current image index position relative to the whole.',
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
					'id'      => 'image_description',
					'label'   => 'Image Description',
					'desc'    => 'Show the caption of the image.',
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
					'id'      => 'zoomable',
					'label'   => 'Mouse Wheel Image Zoom',
					'desc'    => 'Enable/Disable mousewheel zooming over images',
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
					'id'      => 'hideFlash',
					'label'   => 'Hide Flash',
					'desc'    => 'Hide flash instances when viewing an image in the gallery.',
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
				),
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
				)
			)
		)

	)
);
