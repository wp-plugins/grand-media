<?php
//ini_set('display_errors', '1');
//ini_set('error_reporting', E_ALL);

if(!defined('ABSPATH')){
	@require_once(dirname(__FILE__) . '/config.php');
}

global $wp, $wp_styles, $wp_scripts, $gmCore;

$content = '';
$styles = '';
$type = isset($_GET['type'])? $_GET['type'] : (isset($wp->query_vars['type'])? $wp->query_vars['type'] : 'gallery');
if(empty($type)){
	$type = 'gallery';
}
$gmedia = isset($_GET['gmedia'])? $_GET['gmedia'] : (isset($wp->query_vars['gmedia'])? $wp->query_vars['gmedia'] : false);
$gmedia = rawurldecode($gmedia);
if($gmedia){
	global $gmDB;
	if('gallery' == $type || 'album' == $type || 'tag' == $type || 'category' == $type){
		$term_id = $gmDB->term_exists($gmedia, 'gmedia_' . $type);
		if($term_id){
			$atts = array(
				'id' => $term_id,
				'preview' => $gmCore->_get('preview', ''),
				'preset' => $gmCore->_get('preset', 0),
				'_tax' => $type
			);
			$content = gmedia_shortcode($atts);
		}
	} elseif('single' == $type && $gmCore->is_digit($gmedia)){
		$gmedia_obj = $gmDB->get_gmedia($gmedia);
		$type = explode('/', $gmedia_obj->mime_type, 2);
		if('image' == $type[0]){
			$content .= '<div class="single_view">';
			$content .= '<img src="' . $gmCore->gm_get_media_image($gmedia_obj->ID) . '">';
			$content .= "<h2>{$gmedia_obj->title}</h2>";
			$content .= "<div>{$gmedia_obj->description}</div>";
			$content .= "</div>";
			$styles .= '.single_view { width:100%; height:100%; oveflow:auto; }';
		} elseif('video' == $type[0]){
			$meta = $gmDB->get_metadata('gmedia', $gmedia_obj->ID, '_metadata', true);
			$width = isset($meta['width'])? $meta['width'] : 640;
			$height = isset($meta['height'])? $meta['height'] : 480;
			$url = $gmCore->fileinfo($gmedia_obj->gmuid, false);
			$content .= '<video src="' . $url['fileurl'] . '" width="' . $width . '" height="' . $height . '" style="width:100%;height:100%;"></video>';
			$content .= '
<script type="text/javascript">
	jQuery(function($){
		var video = $("video");
		function video_responsive(){
			var vw = video.width(),
					vh = video.height(),
					r = vw/vh,
					bw = $(window).width(),
					bh = $(window).height(),
					mar = 0;
			if(r > bw/bh){
				vh = bw/r;
				vw = bw;
				mar = (bh - vh)/2;
				mar = (mar > 0)? mar + "px 0 0 0" : "0";
			} else{
				vw = bh*r;
				vh = bh;
				mar = (bh - vh)/2;
				mar = (mar > 0)? "0 0 0 " + mar + "px" : "0";
			}
			$("body").css({margin: mar});
			video.attr("width", vw).attr("height", vh);
		}
		video_responsive();
		$(window).on("resize", function(){
			video_responsive();
		});
		video.mediaelementplayer();
	});
</script>
';
			add_action('gmedia_head_scripts', 'gmedia_additional_scripts');
			function gmedia_additional_scripts(){
				wp_enqueue_style('mediaelement');
				wp_enqueue_script('mediaelement');
			}
		}
	}
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width">
	<!-- <meta name='GmediaGallery' content='<?php echo GMEDIA_VERSION . ' / ' . GMEDIA_DBVERSION ?>' /> -->
	<title><?php wp_title('|', true, 'right'); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<style type="text/css">
		html { width:100%; height:100%; }
		body { margin:0; padding:0; width:100%; height:100%; overflow:hidden; min-height:120px; min-width:160px; }
		<?php echo $styles; ?>
	</style>
	<?php
    do_action( 'wp_enqueue_scripts' );
	$wp_styles->queue = array();
	$wp_scripts->queue = array();

	do_action('gmedia_head_scripts');
	do_action('gmedia_footer_scripts');
	if(isset($_GET['iframe'])){
		wp_dequeue_script('swfaddress');
	}
	wp_print_styles();
	wp_print_scripts();
	?>
</head>
<body>
<?php echo $content; ?>

</body>
</html>