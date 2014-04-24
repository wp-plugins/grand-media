<?php
$default_options = array(
	'history' => '1',
	'time' => '3000',
	'autoplay' => '0',
	'loop' => '1',
	'thumbs' => '1',
	'title' => '1',
	'counter' => '1',
	'caption' => '1',
	'zoomable' => '1',
	'hideFlash' => '1',
	'customCSS' => ''
);
$options_tree = array(
	array(
		'label' => 'Gallery Settings',
		'fields' => array(
			'history' => array(
				'label' => 'Browser History',
				'tag' => 'checkbox',
				'attr' => '',
				'text' => 'Enable/disable HTML5 history using hash urls',
			),
			'time' => array(
				'label' => 'Slideshow Delay',
				'tag' => 'input',
				'attr' => 'type="number" min="0" step="500"',
				'text' => 'The time in miliseconds when autoplaying a gallery. Set \'0\' to hide the autoplay button completely'
			),
			'autoplay' => array(
				'label' => 'Auto Slideshow',
				'tag' => 'checkbox',
				'attr' => '',
				'text' => 'Should the gallery autoplay on start or not',
			),
			'loop' => array(
				'label' => 'Gallery Loop',
				'tag' => 'checkbox',
				'attr' => '',
				'text' => 'Loop back to last image before the first one and to the first image after last one',
			),
			'thumbs' => array(
				'label' => 'Thumbnails in Modal',
				'tag' => 'checkbox',
				'attr' => '',
				'text' => 'Show thumbs of all the images in the gallery at the bottom',
			),
			'title' => array(
				'label' => 'Image Title',
				'tag' => 'checkbox',
				'attr' => '',
				'text' => 'Show the title of the image',
			),
			'counter' => array(
				'label' => 'Image Counter',
				'tag' => 'checkbox',
				'attr' => '',
				'text' => 'Show the current image index position relative to the whole.',
			),
			'caption' => array(
				'label' => 'Image Description',
				'tag' => 'checkbox',
				'attr' => '',
				'text' => 'Show the caption of the image.',
			),
			'zoomable' => array(
				'label' => 'Mouse Wheel Image Zoom',
				'tag' => 'checkbox',
				'attr' => '',
				'text' => 'Enable/Disable mousewheel zooming over images',
			),
			'hideFlash' => array(
				'label' => 'Hide Flash',
				'tag' => 'checkbox',
				'attr' => '',
				'text' => 'Hide flash instances when viewing an image in the gallery',
			)
		)
	),
	array(
		'label' => 'Advanced Settings',
		'fields' => array(
			'customCSS' => array(
				'label' => 'Custom CSS',
				'tag' => 'textarea',
				'attr' => 'cols="20" rows="10"',
				'text' => 'You can enter custom style rules into this box if you\'d like. IE: <i>a{color: red !important;}</i><br />This is an advanced option! This is not recommended for users not fluent in CSS... but if you do know CSS, anything you add here will override the default styles'
			)
			/*,
			'loveLink' => array(
				'label' => 'Display LoveLink?',
				'tag' => 'checkbox',
				'attr' => '',
				'text' => 'Selecting "Yes" will show the lovelink icon (codeasily.com) somewhere on the gallery',
			)*/
		)
	)
);
