<?php
ini_set( 'display_errors', '1' );
ini_set( 'error_reporting', E_ALL );

if ( ! defined( 'ABSPATH' ) ){
	@require_once(dirname(__FILE__) . '/config.php');
}

global $wp, $gmCore;
header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
$out = array();
$gmedia_app = isset($_GET['gmedia-app'])? $_GET['gmedia-app'] : (isset($wp->query_vars['gmedia-app'])? $wp->query_vars['gmedia-app'] : false);

if($gmedia_app){
	$json = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);
	$user = false;
	if(isset($json->login)){
		do{
			if(empty($json->login)){
				$out['error'] = array('code' => 'nologin', 'message' => 'No Login');
				break;
			}
			if(!isset($json->password) || empty($json->password)){
				$out['error'] = array('code' => 'nopassword', 'message' => 'No Password');
				break;
			}
			if(! ($uid = username_exists($json->login)) ){
				$out['error'] = array('code' => 'nouser', 'message' => 'No User');
				break;
			}

			require_once(dirname(__FILE__).'/inc/json.auth.php');
			$gmAuth = new Gmedia_JSON_API_Auth_Controller();

			$args = array(
				'username' => $json->login
				,'password' => $json->password
				,'nonce' => wp_create_nonce('auth_gmapp')
			);
			$out = $gmAuth->generate_auth_cookie($args);

		} while(0);
	} elseif(isset($json->cookie)){
		$auth = $gmAuth->validate_auth_cookie($json->cookie);
		if(!$auth['valid']){
			$out['error'] = array('code' => 'nocookie', 'message' => 'No cookie');
		}
	}

	do{
		if(isset($out['error'])){
			break;
		}

		$out['site'] = array(
			'title' => get_bloginfo('name')
			,'description' => get_bloginfo('description')
		);
	} while(0);
}

echo json_encode($out);
