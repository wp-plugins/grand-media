<?php require_once( dirname(__FILE__) . '/config.php');
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<!-- <meta name='GmediaGallery' content='<?php echo GMEDIA_VERSION.' / '.GMEDIA_DBVERSION ?>' /> -->
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<style type="text/css">
		html, body { margin: 0; padding: 0; width: 100%; height: 100%; overflow: auto; min-height: 240px; min-width: 320px; }
	</style>
	<?php
	wp_enqueue_scripts();
	do_action('gmedia_head_scripts');
	wp_print_scripts();
	?>
</head>
<body>
<?php
$gallery_id = isset($_GET['id'])? $_GET['id'] : 0;
$preview = (isset($_GET['preview']) && !empty($_GET['preview']))? ' preview='.$_GET['preview'] : '';
if($gallery_id){
	echo do_shortcode("[gmedia id={$gallery_id}{$preview}]");
} else{
	echo '<br><br><p>'.__('Save gallery to see preview', 'gmLang').'</p>';
}
?>
<?php
do_action('gmedia_footer_scripts');
wp_print_scripts();
?>
</body>
</html>