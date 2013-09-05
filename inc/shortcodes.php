<?php
/** *********************** **/
/** Shortcodes Declarations **/
/** *********************** **/
add_shortcode( 'gmedia', 'gmedia_shortcode' );
add_filter( 'the_content', 'do_shortcode' );


/** ******************************* **/
/** Shortcodes Functions and Markup **/
/** ******************************* **/

function gmedia_shortcode( $atts, $content = null ) {
	global $gMDb, $grandLoad, $grandCore;
	/** @var $id */
	extract( shortcode_atts( array(
		"id" => 0
	), $atts ) );
	$id = intval( $id );
	if ( $id ) {
		if( $content == null ){
			$content = $grandLoad->shortcode_content($id);
		}
		if ( isset( $grandLoad->module_IDs['loaded'] ) && ! in_array( $id, $grandLoad->module_IDs['loaded'] ) )
			$grandLoad->module_IDs['quene'][] = $id;

		$module_name = $gMDb->get_metadata( 'gmedia_term', $id, 'module_name', true );
		$module_dir = $grandCore->get_module_path( $module_name );
		if ( $module_dir ){
			$module['uid'] = 'gmModule';
			include($module_dir['path'] . '/details.php');
			return '<div class="gmedia_module ' . $module_name . '_module" id="' . $module['uid'] . '_ID' . $id . '">' . $content . '</div>';
		}
		if ( $gMDb->term_exists( $id ) )
			return '<div class="gmedia_module gmediaShortcodeError">#' . $id . ': ' . __( 'Gmedia Module folder missed.', 'gmLang' ) . '<br />' . $content . '</div>';
	}

	return '<div class="gmedia_module gmediaShortcodeError">#' . $id . ': ' . __( 'Gmedia Module ID does not exist.', 'gmLang' ) . '</div>';
}

