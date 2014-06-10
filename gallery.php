<?php
ini_set( 'display_errors', '1' );
ini_set( 'error_reporting', E_ALL );

if ( ! defined( 'ABSPATH' ) ){
	@require_once(dirname(__FILE__) . '/config.php');
}

global $wp, $wp_styles, $wp_scripts, $gmCore;

$content = '';
$type = isset($_GET['type'])? $_GET['type'] : (isset($wp->query_vars['type'])? $wp->query_vars['type'] : 'gallery');
if(empty($type)){
	$type = 'gallery';
}
$gmedia = isset($_GET['gmedia'])? $_GET['gmedia'] : (isset($wp->query_vars['gmedia'])? $wp->query_vars['gmedia'] : false);
$gmedia = rawurldecode($gmedia);
if($gmedia){
	global $gmDB;
	if('gallery' == $type || 'album' == $type || 'tag' == $type || 'category' == $type){
		$term_id = $gmDB->term_exists($gmedia, 'gmedia_'.$type);
		if($term_id){
			$atts = array(
				'id' => $term_id
				,'preview' => $gmCore->_get('preview', '')
				,'_tax' => $type
			);
			$content = gmedia_shortcode($atts);
		}
	} elseif('single' == $type && $gmCore->is_digit($gmedia)){
		$gmedia_obj = $gmDB->get_gmedia($gmedia);
		$content = '<img src="'.$gmCore->gm_get_media_image($gmedia_obj->ID).'">';
		$content .= "<h2>{$gmedia_obj->title}</h2>";
		$content .= "<div>{$gmedia_obj->description}</div>";
	}
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width">
	<!-- <meta name='GmediaGallery' content='<?php echo GMEDIA_VERSION.' / '.GMEDIA_DBVERSION ?>' /> -->
	<title><?php wp_title( '|', true, 'right' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<style type="text/css">
		html { width: 100%; height: 100%; }
		body { margin: 0; padding: 0; width: 100%; height: 100%; overflow: auto; min-height: 240px; min-width: 320px; }
	</style>
	<?php
	wp_enqueue_scripts();
	$wp_styles->queue = array();
	$wp_scripts->queue = array();

	do_action('gmedia_head_scripts');
	do_action('gmedia_footer_scripts');
	if(isset($_GET['iframe'])){
		wp_dequeue_script('swfaddress');
	}
	wp_print_scripts();
	?>
</head>
<body>
<?php echo $content; ?>

</body>
</html>