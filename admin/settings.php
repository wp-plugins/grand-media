<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * grandDashboard()
 *
 * @return mixed content
 */
function gmSettings() {
	include_once( dirname( dirname( __FILE__ ) ) . '/setup.php' );
	$grandOptions = grand_default_options();
	update_option( 'gmediaOptions', $grandOptions );
	?>
	<div class="wrap flag-wrap">
		<h2><?php _e( 'GRAND Media Overview', 'gmLang' ); ?></h2>

		<p>Coming soon...</p>
	</div>
<?php
}
