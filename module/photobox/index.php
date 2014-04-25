<?php
$module_info = array(
	'base' => 'photobox',
	'name' => 'photobox',
	'title' => 'PhotoBox',
	'version' => '0.5',
	'author' => 'CodEasily.com',
	'description' => 'A lightweight image gallery modal window script which uses only CSS3 for silky-smooth animations and transitions, utilizes GPU rending, which completely controlled and themed with the CSS.',
	'type' => 'gallery',
	'status' => 'premium',
	'price' => '$20',
	'demo' => 'http://codeasily.com/portfolio-item/photobox-module/',
	'download' => 'http://codeasily.com/download/photobox-module-zip/',
	'dependencies' => ''
);
if(isset($_GET['info'])){
	echo '<pre>' . print_r($module_info, true) . '</pre>';
} elseif(preg_match('#' . basename(dirname(__FILE__)).'/'.basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	header("Location: {$module_info['demo']}");
	die();
}