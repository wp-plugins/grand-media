<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * gmediaPermalinks class.
 */
class gmediaPermalinks {

	private $endpoint = 'gmedia';

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return \gmediaPermalinks
	 */
	public function __construct() {
		add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'parse_request', array( $this, 'handler' ) );

		add_filter( 'post_thumbnail_html', array( $this, 'gmedia_post_thumbnail' ), 10, 5 );
	}

	/**
	 * @param $rules
	 *
	 * @return array
	 */
	function add_rewrite_rules( $rules ) {
		global $wp_rewrite, $gmGallery;
		$this->endpoint = !empty($gmGallery->options['endpoint'])? $gmGallery->options['endpoint'] : 'gmedia';

		$this->add_endpoint();

		$new_rules = array(
			$this->endpoint . '/(g|s|a|t|k)/(.+?)/?$' => 'index.php?' . $this->endpoint . '=' . $wp_rewrite->preg_index( 2 ) . '&t=' . $wp_rewrite->preg_index( 1 ),
			'gmedia-app/?$' => 'index.php?gmedia-app=1'
		);

		$new_rules = $new_rules + $rules;

		return $new_rules;
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
	}

	/**
	 * add_query_vars function.
	 *
	 * @access public
	 *
	 * @param $vars
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		global $gmGallery;
		$endpoint = !empty($gmGallery->options['endpoint'])? $gmGallery->options['endpoint'] : 'gmedia';

		$vars[] = $endpoint;
		$vars[] = 't';

		return $vars;
	}

	/**
	 * Listen for gmedia requets and show gallery template.
	 *
	 * @access public
	 * @return void
	 */
	public function handler() {
		global $wp, $gmGallery;
		$endpoint = !empty($gmGallery->options['endpoint'])? $gmGallery->options['endpoint'] : 'gmedia';

		if ( isset( $_GET[$endpoint] ) && ! empty( $_GET[$endpoint] ) ) {
			$wp->query_vars[$endpoint] = $_GET[$endpoint];
		}
		if ( isset( $wp->query_vars[$endpoint] ) && ! empty( $wp->query_vars[$endpoint] ) ) {

			if ( isset( $_GET['t'] ) && ! empty( $_GET['t'] ) ) {
				$wp->query_vars['t'] = $_GET['t'];
			}
			global $wp_query;
			$wp_query->is_single  = false;
			$wp_query->is_page    = false;
			$wp_query->is_archive = false;
			$wp_query->is_search  = false;
			$wp_query->is_home    = false;

			/*
			$template = get_query_template( 'gmedia-gallery' );
			// Get default slug-name.php
			if ( ! $template ) {
				$template = GMEDIA_ABSPATH . "/load-template.php";
			}

			load_template( $template, false );
			*/

			require_once(GMEDIA_ABSPATH . "/load-template.php");

			exit();

		}

		/* Application only template */
		if ( isset( $_GET['gmedia-app'] ) && ! empty( $_GET['gmedia-app'] ) ) {
			$wp->query_vars['gmedia-app'] = $_GET['gmedia-app'];
		}
		if ( isset( $wp->query_vars['gmedia-app'] ) && ! empty( $wp->query_vars['gmedia-app'] ) ) {

			global $wp_query;
			$wp_query->is_single  = false;
			$wp_query->is_page    = false;
			$wp_query->is_archive = false;
			$wp_query->is_search  = false;
			$wp_query->is_home    = false;

			$template = GMEDIA_ABSPATH . "/access.php";

			load_template( $template, false );
			exit();

		}

	}

	/**
	 * Filter for the post content
	 *
	 * @param string $html
	 * @param int $post_id
	 * @param int $post_thumbnail_id
	 * @param string|array $size Optional. Image size.  Defaults to 'thumbnail'.
	 * @param string|array $attr Optional. Query string or array of attributes.
	 *
	 * @return string html output
	 */
	function gmedia_post_thumbnail( $html, $post_id, $post_thumbnail_id, $size = 'post-thumbnail', $attr = '' ) {

		$gmedia_id = get_post_meta( $post_thumbnail_id, '_gmedia_image_id', true );
		if ( ! empty( $gmedia_id ) ) {
			$html = str_replace( 'wp-post-image', 'wp-post-image gmedia-post-thumbnail-' . $gmedia_id, $html );
		}

		return $html;
	}

}

global $gmPermalinks;
$gmPermalinks = new gmediaPermalinks();