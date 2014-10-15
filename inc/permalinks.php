<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

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
		$this->endpoint = ( isset( $gmGallery->options['endpoint'] ) && ( $endpoint = $gmGallery->options['endpoint'] ) ) ? $endpoint : 'gmedia';

		add_filter( 'rewrite_rules_array', array( $this, 'add_rewrite_rules' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'parse_request', array( $this, 'handler' ) );

		add_filter( 'post_thumbnail_html', array( $this, 'gmedia_post_thumbnail' ), 10, 5 );
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
	 * @param $rules
	 *
	 * @return array
	 */
	function add_rewrite_rules( $rules ) {
		global $wp_rewrite;

		$this->add_endpoint();

		$new_rules = array(
			$this->endpoint . '(/(gallery|single|album|tag|category))?/(.+?)/?$' => 'index.php?gmedia=' . $wp_rewrite->preg_index( 3 ) . '&type=' . ( $wp_rewrite->preg_index( 2 ) ? $wp_rewrite->preg_index( 2 ) : 'gallery' ),
			'gmedia-app/?$'                                                      => 'index.php?gmedia-app=1'
		);

		$new_rules = $new_rules + $rules;

		return $new_rules;
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
		$vars[] = 'gmedia';
		$vars[] = 'type';

		return $vars;
	}

	/**
	 * Listen for gmedia requets and show gallery template.
	 *
	 * @access public
	 * @return void
	 */
	public function handler() {
		global $wp;

		if ( isset( $_GET['gmedia'] ) && ! empty( $_GET['gmedia'] ) ) {
			$wp->query_vars['gmedia'] = $_GET['gmedia'];
		}

		if ( isset( $_GET['type'] ) && ! empty( $_GET['type'] ) ) {
			$wp->query_vars['type'] = $_GET['type'];
		}

		if ( isset( $wp->query_vars['gmedia'] ) && ! empty( $wp->query_vars['gmedia'] ) ) {

			global $wp_query;
			$wp_query->is_single  = false;
			$wp_query->is_page    = false;
			$wp_query->is_archive = false;
			$wp_query->is_search  = false;
			$wp_query->is_home    = false;

			$template = get_query_template( 'gmedia-gallery' );
			// Get default slug-name.php
			if ( ! $template ) {
				$template = GMEDIA_ABSPATH . "/gallery.php";
			}

			load_template( $template, false );
			exit();

		}

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