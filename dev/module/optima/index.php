<?php
$module_info = array(
	'base' => 'optima',
	'name' => 'optima',
	'title' => 'Optima',
	'version' => '3.0',
	'author' => 'CodEasily.com',
	'description' => 'Multi-tab premium photo gallery module with slideshow feature.

Features:
* Play background music
* Shows the number of images in the gallery
* Download Image button
* Image Views counter
* Image Like button
* Auto Slideshow mode
* Full Screen mode
* jQuery gallery for browsers without flash support (iDevices, Android Devices)
* Search Engine Optimized gallery',
	'type' => 'gallery',
	'status' => 'premium',
	'price' => '$20',
	'demo' => 'http://codeasily.com/portfolio-item/gmedia-optima/',
	'download' => 'http://codeasily.com/download/optima-module-zip/',
	'dependencies' => 'swfobject'
);
if(isset($_GET['info'])){
	echo '<pre>' . print_r($module_info, true) . '</pre>';
} elseif(preg_match('#' . basename(dirname(__FILE__)).'/'.basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	header("Location: {$module_info['demo']}");
	die();
}