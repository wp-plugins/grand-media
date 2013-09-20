<?php /** @var $module */
if(isset($_GET['details'])){
	include( dirname( __FILE__ ) . '/details.php' );
	echo '<pre class="module">';
	print_r( $module );
	echo '</pre>';
}