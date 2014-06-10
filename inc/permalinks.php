<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * gmediaPermalinks class.
 */
class gmediaPermalinks {

	private $endpoint;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return \gmediaPermalinks
	 */
	public function __construct() {
		global $gmGallery;
		$this->endpoint = ( $endpoint = $gmGallery->options['endpoint'] ) ? $endpoint : 'gmedia';

		add_filter( 'query_vars', array( $this, 'add_query_vars') );
		add_action( 'init', array( $this, 'add_endpoint') );
		add_action( 'generate_rewrite_rules', array( $this, 'add_rewrite_rules') );
		add_action( 'parse_request', array( $this, 'handler') );

	}


	/**
	 * @param $wp_rewrite
	 */
	function add_rewrite_rules( $wp_rewrite ) {
		$new_rules = array(
			$this->endpoint . '(/(gallery|single|album|tag|category))?/(.+?)/?$' => 'index.php?gmedia=' . $wp_rewrite->preg_index(3) . '&type=' . ($wp_rewrite->preg_index(2)? $wp_rewrite->preg_index(2) : 'gallery')
			,'gmedia-app/?$' => 'index.php?gmedia-app=1'
		);

		//​ Add the new rewrite rule into the top of the global rules array
		$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

	/**
	 * add_query_vars function.
	 *
	 * @access public
	 * @param $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'gmedia';
		$vars[] = 'type';
		return $vars;
	}

	/**
	 * add_endpoint function.
	 *
	 * @access public
	 * @return void
	 */
	public function add_endpoint() {
		add_rewrite_endpoint( $this->endpoint, EP_NONE );
		add_rewrite_endpoint( 'gmedia-app', EP_NONE );
		//add_rewrite_rule('gmedia(/(gallery|single|album|tag|category))?/(.+?)/?$', 'index.php?gmedia=$matches[3]&type=$matches[2]', 'top');
	}

	/**
	 * Listen for gmedia requets and show gallery template.
	 *
	 * @access public
	 * @return void
	 */
	public function handler() {
		global $wp;

		if ( isset($_GET['gmedia']) && !empty($_GET['gmedia'] ) ) {
			$wp->query_vars['gmedia'] = $_GET['gmedia'];
		}

		if ( isset($_GET['type']) && !empty($_GET['type'] ) ) {
			$wp->query_vars['type'] = $_GET['type'];
		}

		if ( isset($wp->query_vars['gmedia']) && !empty($wp->query_vars['gmedia']) ) {

			global $wp_query;
			$wp_query->is_single = false;
			$wp_query->is_page = false;
			$wp_query->is_archive = false;
			$wp_query->is_search = false;
			$wp_query->is_home = false;

			$template = get_query_template('gmedia-gallery');
			// Get default slug-name.php
			if (!$template){
				$template = GMEDIA_ABSPATH . "/gallery.php";
			}

			load_template( $template, false );
			exit();

		}

		if ( isset($_GET['gmedia-app']) && !empty($_GET['gmedia-app'] ) ) {
			$wp->query_vars['gmedia-app'] = $_GET['gmedia-app'];
		}
		if ( isset($wp->query_vars['gmedia-app']) && !empty($wp->query_vars['gmedia-app']) ) {

			global $wp_query;
			$wp_query->is_single = false;
			$wp_query->is_page = false;
			$wp_query->is_archive = false;
			$wp_query->is_search = false;
			$wp_query->is_home = false;

			$template = GMEDIA_ABSPATH . "/access.php";

			load_template( $template, false );
			exit();

		}

	}

}
global $gmPermalinks;
$gmPermalinks = new gmediaPermalinks();