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
						'mime_type' => 'audio',
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
					'desc'  => 'Width (number or number with %). Default value: 400. Set the width of the player',
					'std'   => '400',
					'type'  => 'text',
					'param' => '',
					'class' => ''
				),
				array(
					'id'      => 'autoPlay',
					'label'   => 'Autoplay',
					'desc'    => 'Default value: Off.',
					'std'     => array( '' ),
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
					'id'    => 'buttonText',
					'label' => 'Link Button Text',
					'desc'  => 'If gmedia link field is not empty than button with this text will show near track (ex: Open, Buy, Download)',
					'std'   => 'Download',
					'type'  => 'text',
					'param' => '',
					'class' => ''
				),
				array(
					'id'    => 'tracksToShow',
					'label' => '# of Tracks to Show',
					'desc'  => 'Set how many tracks to see on page load. Others be hided and More button shows.',
					'std'   => '5',
					'type'  => 'text',
					'param' => array('type'=>'number', 'min'=>'-1'),
					'class' => ''
				),
				array(
					'id'    => 'moreText',
					'label' => 'More Button Text',
					'desc'  => 'Button to show more tracks.',
					'std'   => 'View More...',
					'type'  => 'text',
					'param' => '',
					'class' => ''
				)
			)
		),
		'section2'        => array(

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
