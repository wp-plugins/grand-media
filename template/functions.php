<?php

add_action( 'gmedia_head', 'wp_print_styles', 1000);
add_action( 'gmedia_head', 'wp_print_head_scripts', 1000 );
add_action( 'gmedia_footer', 'wp_print_styles' );
add_action( 'gmedia_footer', 'print_footer_scripts' );
add_action( 'gmedia_footer', 'wp_print_footer_scripts' );

function gmedia_head(){
	global $wp_styles, $wp_scripts, $gmCore;
	global $gmedia_id, $gmedia_type, $gmedia_shortcode_content;

	do_action('wp_enqueue_scripts');
	if($gmCore->_get('iframe')){
		wp_deregister_script('swfaddress');
		add_filter('show_admin_bar', '__return_false');
	}
	$wp_styles->queue = array();
	$wp_scripts->queue = array();

	if(is_admin_bar_showing()){
		add_action('gmedia_head', 'wp_admin_bar_header', 0);
		add_action('gmedia_head', '_admin_bar_bump_cb', 0);
		add_action('gmedia_head', '_wp_admin_bar_init');
		add_action('gmedia_footer', 'wp_admin_bar_render', 1000);
	}

	$gmedia_shortcode_content = get_the_gmedia_content($gmedia_id, $gmedia_type);

	do_action('gmedia_head');
}
function gmedia_footer(){
	do_action('gmedia_footer');
}

/**
 * @param string $sep
 * @param bool $display
 *
 * @return string|void
 */
function gmedia_title($sep = '|', $display = true){
	global $gmedia, $gmedia_type, $gmGallery;

	$_title = __('GmediaGallery', 'gmLang');
	if(is_object($gmedia) && !is_wp_error($gmedia)){
		if(in_array($gmedia_type, array('gallery', 'album', 'tag'))){
			$_title = $gmedia->name;
		} elseif('category' == $gmedia_type){
			$gm_terms_all = $gmGallery->options['taxonomies']['gmedia_category'];
			$_title = $gm_terms_all[$gmedia->name];
		} elseif('single' == $gmedia_type){
			$_title = $gmedia->title;
		}
	}

	$title[] = $_title;

	if ( current_theme_supports( 'title-tag' ) ) {
		$title[] = get_bloginfo( 'name', 'display' );
	}

	$title = implode( " $sep ", $title );

	/**
	 * Filter the text of the gmedia title.
	 *
	 * @param string $title       Page title.
	 * @param string $sep         Title separator.
	 */
	$title = apply_filters( 'gmedia_title', $title, $sep );

	// Send it out
	if ( $display )
		echo $title;
	else
		return $title;
}

function the_gmedia_title(){
	global $gmedia, $gmedia_type, $gmGallery;

	$title = __('GmediaGallery', 'gmLang');
	if(is_object($gmedia) && !is_wp_error($gmedia)){
		if(in_array($gmedia_type, array('gallery', 'album', 'tag'))){
			$title = $gmedia->name;
		} elseif('category' == $gmedia_type){
			$gm_terms_all = $gmGallery->options['taxonomies']['gmedia_category'];
			$title = $gm_terms_all[$gmedia->name];
		} elseif('single' == $gmedia_type){
			$title = $gmedia->title;
		}
	}

	/**
	 * Filter the text of the gmedia title.
	 *
	 * @param string $title       Page title.
	 * @param string $sep         Title separator.
	 */
	$title = apply_filters( 'the_gmedia_title', $title );

	echo $title;
}
/**
 * @param $classes
 *
 * @return array
 */
function gmedia_body_class($classes){
	global $gmedia_type;
	$classes = array_merge($classes, array('gmedia-template', "gmedia-template-{$gmedia_type}"));
	$classes = apply_filters('gmedia_body_class', $classes);
	return (array) $classes;
}
add_filter( 'body_class', 'gmedia_body_class' );

function get_gmedia_header(){
	global $gmedia_module, $gmedia_type, $gmCore;
	$module = $gmCore->get_module_path($gmedia_module);
	if(file_exists($module['path'] . '/template/head.php')){
		include_once($module['path'] . '/template/head.php');
	} else{
		if('single' == $gmedia_type){
			add_filter('show_admin_bar', '__return_false');
		}
		include_once(GMEDIA_ABSPATH . 'template/head.php');
	}
}
function get_gmedia_footer(){
	global $gmedia_module, $gmCore;
	$module = $gmCore->get_module_path($gmedia_module);
	if(file_exists($module['path'] . '/template/foot.php')){
		include_once($module['path'] . '/template/foot.php');
	} else{
		include_once(GMEDIA_ABSPATH . 'template/foot.php');
	}
}

/**
 * @param $gmedia_id
 * @param $gmedia_type
 *
 * @return string
 */
function get_the_gmedia_content($gmedia_id, $gmedia_type){
	global $user_ID, $gmCore;

	$content = '';
	if(in_array($gmedia_type, array('gallery', 'album', 'tag', 'category'))){
		$atts = array(
			'id' => $gmedia_id,
			'set_module' => ($user_ID? $gmCore->_get('set_module', '') : ''),
			'preset' => ($user_ID? $gmCore->_get('preset', 0) : 0),
			'_tax' => $gmedia_type
		);
		$content = gmedia_shortcode($atts);
		do_action('gmedia_enqueue_scripts');
	}

	return $content;
}

function the_gmedia_content(){
	global $gmedia_shortcode_content;
	echo $gmedia_shortcode_content;
}

function gmedia_default_template_styles(){ ?>
<style type="text/css" media="screen">
	* {box-sizing:border-box;}
	body {font-family:"Arial", "Verdana", serif; font-size:13px;}
	header { position:relative; height:30px; background-color:#0f0f0f; color:#f1f1f1;padding:7px 30px 3px; font-family:"Arial", "Verdana", serif; z-index:10;}
	.site-title {display:inline-block; font-size:16px; margin-right: 30px; vertical-align:bottom;}
	.gmedia-header-title {display:inline-block; font-size:16px; vertical-align:bottom;}
	.gmedia-header-description { position:absolute; top:100%; left:0; right:0; font-size:13px; overflow:visible; background-color:#0f0f0f; padding:10px 30px; border-bottom:1px solid #444444;}
	.gmedia-header-description { display:none; }
	.gmedia-header-description-button {
		position: absolute;
		top: 5px;
		right: 15px;
		width: 18px;
		height: 20px;
		background-image: url(data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNi4wLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4IiB3aWR0aD0iNTEycHgiIGhlaWdodD0iNTEycHgiIHZpZXdCb3g9IjAgMCA1MTIgNTEyIiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCA1MTIgNTEyIiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxwYXRoIGZpbGw9IiNGRkZGRkYiIGQ9Ik0yOTMuNzUxLDQ1NS44NjhjLTIwLjE4MSwyMC4xNzktNTMuMTY1LDE5LjkxMy03My42NzMtMC41OTVsMCwwYy0yMC41MDgtMjAuNTA4LTIwLjc3My01My40OTMtMC41OTQtNzMuNjcyICBsMTg5Ljk5OS0xOTBjMjAuMTc4LTIwLjE3OCw1My4xNjQtMTkuOTEzLDczLjY3MiwwLjU5NWwwLDBjMjAuNTA4LDIwLjUwOSwyMC43NzIsNTMuNDkyLDAuNTk1LDczLjY3MUwyOTMuNzUxLDQ1NS44Njh6Ii8+DQo8cGF0aCBmaWxsPSIjRkZGRkZGIiBkPSJNMjIwLjI0OSw0NTUuODY4YzIwLjE4LDIwLjE3OSw1My4xNjQsMTkuOTEzLDczLjY3Mi0wLjU5NWwwLDBjMjAuNTA5LTIwLjUwOCwyMC43NzQtNTMuNDkzLDAuNTk2LTczLjY3MiAgbC0xOTAtMTkwYy0yMC4xNzgtMjAuMTc4LTUzLjE2NC0xOS45MTMtNzMuNjcxLDAuNTk1bDAsMGMtMjAuNTA4LDIwLjUwOS0yMC43NzIsNTMuNDkyLTAuNTk1LDczLjY3MUwyMjAuMjQ5LDQ1NS44Njh6Ii8+DQo8L3N2Zz4=);
		background-size: contain;
		cursor:pointer;
	}
	.gmedia-main-wrapper {
		position:absolute;
		top:30px; left:0; right:0; bottom:0;
		overflow:auto;
	}
	body.admin-bar .gmedia-main-wrapper {
		top:62px;
	}
	.gmedia-main-wrapper .gmedia_gallery {
		width:100%;
		height:100%;
		text-align:center;
	}
	.gmedia-main-wrapper .gmedia_gallery > div {
		margin-left:auto;
		margin-right:auto;
		text-align:left;
	}
	.gmedia-main-wrapper .gmedia_gallery.is_mobile {
		height:auto;
	}
	.gmedia-main-wrapper object {
		width:100% !important;
		height:100% !important;
	}

	a { color:#2e6286; text-decoration:underline; }
	a:hover, a:active, a:visited { color:#2e6286; text-decoration:none; }
	body.gmedia-template-single { background-color:#bbbbbb; text-align:center; }
	.single-view { max-width:1280px; min-width:640px; padding:10px 10px 20px; margin:0 auto; }
	.single-view img { max-width:100%; height:auto; }
	.single-title { font-size:22px; font-weight:bold; }
	.type-download .single-title { font-size:18px; }
	.image-description { text-align:left }
	.gmedia-no-files { text-align:center; font-size:16px; padding:30px 10px;}
	.gmediaShortcodeError { text-align:left; font-size:14px; padding:30px 10px;}
	@media screen and ( max-width: 782px ) {
		body.admin-bar .gmedia-main-wrapper {
			top:76px;
		}
	}
</style>
<?php }

function gmedia_video_head_scripts(){
	wp_enqueue_style('mediaelement');
	wp_enqueue_script('mediaelement');
}
function gmedia_video_foot_scripts(){ ?>
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
<?php
}
