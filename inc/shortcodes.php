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
	global $gMDb, $grandLoad;
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

		$shortcode = $gMDb->get_metadata( 'gmedia_term', $id, 'shortcode', true );
		if ( ! empty( $shortcode ) )
			return '<div class="' . $shortcode . '" id="' . $shortcode . '_ID' . $id . '">' . $content . '</div>';
		if ( $gMDb->term_exists( $id ) )
			return '<div class="GrandMediaShortcode">#' . $id . ': ' . __( 'Update Gmedia Module ID options. Missed `shortcode` option.', 'gmLang' ) . '<br />' . $content . '</div>';
	}

	return '<div class="GrandMediaShortcode">#' . $id . ': ' . __( 'Gmedia Module ID does not exist.', 'gmLang' ) . '</div>';
}

