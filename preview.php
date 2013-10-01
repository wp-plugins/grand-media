<?php
preg_match( '|^(.*?/)(grand-media)/|i', str_replace( '\\', '/', __FILE__ ), $_m );
/** @noinspection PhpIncludeInspection */
require_once( $_m[1] . 'grand-media/config.php' );
global $grandCore;
$gMediaURL = plugins_url( GRAND_FOLDER );
$gmID = intval( $grandCore->_get( 'gmID', '' ) );
$gmType = $grandCore->_get( 'type', '' );
switch ( $gmType ) {
	case 'video':
		$swfURL                = $gMediaURL . '/inc/watch.swf';
		$flashvars['path']     = $gMediaURL . '/inc/';
		$flashvars['vID']      = $gmID;
		$flashvars['autoplay'] = 'true';
		$w                     = '520';
		$h                     = '304';
		$gmAlt                 = __( 'The <a href="http://www.macromedia.com/go/getflashplayer">Flash Player</a> and a browser with Javascript support are needed.', 'gmLang' );
		break;
	case 'audio':
	default:
		$swfURL               = $gMediaURL . '/inc/listen.swf';
		$flashvars['path']    = str_replace( array( '.mp3' ), array( '' ), wp_get_attachment_url( $gmID ) );
		$flashvars['bgcolor'] = '4f4f4f';
		$flashvars['color1']  = 'ffffff';
		$flashvars['color2']  = '3283A7';
		$w                    = '250';
		$h                    = '20';
		$gmAlt                = __( '<a href="http://www.macromedia.com/go/getflashplayer">Install Flash Player</a>', 'gmLang' );
		break;
}
?>
<html>
<head>
	<script type="text/javascript">
		var flashvars = {};
		<?php foreach($flashvars as $key => $value){ echo "	flashvars.{$key} = \"{$value}\";\n"; } ?>

		var params = {};
		params.scale = "noscale";
		params.salign = "tl";
		params.wmode = "transparent";
		params.allowScriptAccess = "always";
		params.allowFullScreen = "true";
		params.bgcolor = "#000000";

		var attributes = {};
		attributes.styleclass = "gmPreview";
		attributes.id = "gmPreview[<?php echo $gmID; ?>]";

		swfobject.embedSWF("<?php echo $swfURL; ?>", "gmPreview[<?php echo $gmID; ?>]", "<?php echo $w; ?>", "<?php echo $h; ?>", "10.1.52", false, flashvars, params, attributes);
	</script>
</head>
<body style="margin: 0; padding: 0; background: #ffffff; overflow: hidden;">
<div id="gmPreview[<?php echo $gmID; ?>]" style="width:<?php echo $w; ?>px;height:<?php echo $h; ?>px;overflow:hidden;font-size:10px;"><?php echo $gmAlt; ?></div>
</body>
</html>