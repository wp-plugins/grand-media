<?php
/*
Plugin Name: Gmedia Gallery
Plugin URI: http://wordpress.org/extend/plugins/grand-media/
Description: Grand Media Gallery - powerfull media library plugin for creating beautiful galleries and managing files.
Version: 0.8.3
Author: Rattus
Author URI: http://codeasily.com/

-------------------

		Copyright (C) 2011  Rattus  (email : gmediafolder@gmail.com)

		This program is free software; you can redistribute it and/or
		modify it under the terms of the GNU General Public License
		as published by the Free Software Foundation; either version 2
		of the License, or (at your option) any later version.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Stop direct call
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

//ini_set( 'display_errors', '1' );
//ini_set( 'error_reporting', E_ALL );
if ( ! class_exists( 'grandLoad' ) ) {
	class grandLoad {

		var $version = '0.8.3';
		var $dbversion = '0.6.2';
		var $minium_WP = '3.4';
		var $options = '';
		var $library;
		var $core;
		var $gMDb;
		var $grandAdminPanel;
		var $module_IDs = array();

		function grandLoad() {

			// Stop the plugin if we missed the requirements
			if ( ! $this->required_version() )
				return;

			// Get some constants first
			$this->load_options();
			$this->define_constant();
			$this->define_tables();
			$this->load_dependencies();

			$this->plugin_name = plugin_basename( __FILE__ );

			// Init options & tables during activation & deregister init option
			register_activation_hook( $this->plugin_name, array( &$this, 'activate' ) );
			register_deactivation_hook( $this->plugin_name, array( &$this, 'deactivate' ) );

			// Register a uninstall hook to remove all tables & option automatic
			register_uninstall_hook( $this->plugin_name, array(__CLASS__, 'gmedia_uninstall') );

			// Start this plugin once all other plugins are fully loaded
			add_action( 'plugins_loaded', array( &$this, 'start_plugin' ) );

			//Add some message on the plugin page
			//add_action( 'after_plugin_row', array(&$this, 'check_message_version') );

			//Add some links on the plugin page
			//add_filter( 'plugin_row_meta', array( &$this, 'add_plugin_links' ), 10, 2 );

			add_action( 'do_meta_boxes', array( &$this, 'do_meta_boxes' ), 20, 2 );
			add_filter( 'mce_external_plugins', array( &$this, 'add_tinymce_plugin' ), 5 );
			add_action( 'admin_enqueue_scripts', array( &$this, 'meta_box_load_styles' ), 20 );
			add_filter( 'media_buttons_context', array( &$this, 'media_button'), 4 );

			add_action( 'save_post', array( &$this, 'shortcode_check' ) );

			add_action( 'wpmu_new_blog', array( &$this, 'new_blog'), 10, 6);
		}

		function start_plugin() {

			// Load the language file
			$this->load_textdomain();

			// Check for upgrade
			$this->upgrade();

			// Load the admin panel or the frontend functions
			if ( is_admin() ) {

				// Pass the init check or show a message
				if ( get_option( 'gmediaInitCheck' ) )
					add_action( 'admin_notices', array( &$this, 'admin_notices' ) );

			}
			else {

				// Add the script and style files
				add_action( 'template_redirect', array( &$this, 'load_scripts' ) );

				// Add a version number to the header
				add_action( 'wp_head', create_function( '', 'echo "\n<!-- <meta name=\'GrandMedia\' content=\'' . $this->version . '\' /> -->\n";' ) );
				add_action( 'wp_footer', array( &$this, 'load_scripts_footer' ) );

			}
		}

		function admin_notices() {
			echo '<div id="message" class="error"><p><strong>' . get_option( 'gmediaInitCheck' ) . '</strong></p></div>';
			delete_option( 'gmediaInitCheck' );
		}

		function required_version() {

			global $wp_version;

			// Check for WP version installation
			if ( version_compare( $wp_version, $this->minium_WP, '<' ) ) {
				$note = sprintf( __( 'Sorry, GrandMedia works only under WordPress %s or higher', 'gmLang' ), $this->minium_WP );
				update_option( 'gmediaInitCheck', $note );
				add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
				return false;
			}
			if ( version_compare( '5.2', phpversion(), '>' ) ) {
				$note = sprintf( __( 'Attention! Your server php version is: %s. GrandMedia requires php version 5.2+ in order to run properly. Please upgrade your server!', 'gmLang' ), phpversion() );
				update_option( 'gmediaInitCheck', $note );
				add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
			}
			if ( version_compare( '5.3', phpversion(), '>' ) ) {
				if ( ini_get( 'safe_mode' ) ) {
					$note = __( 'Attention! Your server safe mode is: ON. GrandMedia requires safe mode to be OFF in order to run properly. Please set your server safe mode option!', 'gmLang' );
					update_option( 'gmediaInitCheck', $note );
					add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
				}
			}

			return true;
		}

		function upgrade() {
			return;
		}

		function define_tables() {
			global $wpdb;

			// add database pointer
			$wpdb->gmedia                    = $wpdb->prefix . 'gmedia';
			$wpdb->gmedia_meta               = $wpdb->prefix . 'gmedia_meta';
			$wpdb->gmedia_term               = $wpdb->prefix . 'gmedia_term';
			$wpdb->gmedia_term_meta          = $wpdb->prefix . 'gmedia_term_meta';
			$wpdb->gmedia_term_relationships = $wpdb->prefix . 'gmedia_term_relationships';

		}

		function define_constant() {

			define( 'GRAND_VERSION', $this->version );
			// Minimum required database version
			define( 'GRAND_DBVERSION', $this->dbversion );

			// define plugin dir
			define( 'GRAND_FOLDER', plugin_basename( dirname( __FILE__ ) ) );
			define( 'GRAND_ABSPATH', plugin_dir_path( __FILE__ ) );

		}

		function load_dependencies() {

			// Load global libraries
			require_once ( dirname( __FILE__ ) . '/inc/core.php' );
			require_once ( dirname( __FILE__ ) . '/inc/db.connect.php' );
			require_once ( dirname( __FILE__ ) . '/admin/functions.php' );

			// We didn't need all stuff during a AJAX operation
			if ( defined( 'DOING_AJAX' ) )
				require_once ( dirname( __FILE__ ) . '/admin/ajax.php' );
			else {
				// Load backend libraries
				if ( is_admin() ) {
					require_once ( dirname( __FILE__ ) . '/admin/admin.php' );
					$this->grandAdminPanel = new grandAdminPanel();

					// Load frontend libraries
				}
				else {
					require_once ( dirname( __FILE__ ) . '/inc/shortcodes.php' );
				}
			}
		}

		function load_textdomain() {

			load_plugin_textdomain( 'gmLang', false, GRAND_FOLDER . '/languages/' );

		}

		function load_scripts() {
			global $wp_query, $grandCore, $gMDb;

			$gmOptions 			= get_option( 'gmediaOptions' );
			$gMediaURL      = plugins_url( GRAND_FOLDER );
			$upload         = $grandCore->gm_upload_dir();
			$load_in_footer = true;

			wp_enqueue_script( 'jquery' );
			wp_register_script( 'grandMediaGlobalFrontend', $gMediaURL . '/admin/js/gmedia.global.front.js', array( 'jquery' ), '1.0' );
			wp_localize_script( 'grandMediaGlobalFrontend', 'gMediaGlobalVar', array(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'uploadPath' => rtrim( $upload['url'], '/' ),
				'gmediaKey' => strtolower($gmOptions['gmedia_key'])
			) );
			wp_enqueue_script( 'grandMediaGlobalFrontend' );

			wp_register_script('jplayer', $gMediaURL.'/inc/jplayer/jquery.jplayer.js', array( 'jquery' ), '2.0.22');
			//wp_register_script('swfaddress', $gMediaURL.'/admin/js/swfaddress.js', array(), '2.4');
			//wp_enqueue_script('swfobject');

			/*
			if(is_single() || is_page()){
				$module_IDs = get_post_meta($post->ID, '_gmedia_module_id', true);
				$load_IDs = preg_replace( '/[^a-z0-9,_-]+/i', '', $module_IDs );
				$load_IDs = array_unique(explode(',', $load_IDs));
				if(!empty($module_IDs)){
					$this->load_module_scripts($load_IDs, $load_in_footer);
				}
			}
			*/
			if ( ! empty( $wp_query->posts ) ) {
				$module_IDs = array();
				foreach ( $wp_query->posts as $post ) {
					$module_IDs_str = get_post_meta( $post->ID, '_gmedia_module_id', true );
					if ( $module_IDs_str )
						$module_IDs = array_merge( $module_IDs, explode( ',', $module_IDs_str ) );
				}
				$load_IDs                   = array_unique( $module_IDs );
				$this->module_IDs['loaded'] = $load_IDs;
				if ( ! empty( $load_IDs ) ) {
					$this->load_module_scripts( $load_IDs, $load_in_footer );
				}
			}
		}

		/**
		 * @param string $module_IDs IDs of modules separated by comma
		 * @param bool   $load_in_footer
		 */
		function load_module_scripts( $module_IDs, $load_in_footer = true ) {
			global $gMDb, $grandCore;
			$gMediaURL = plugins_url( GRAND_FOLDER );

			$module_IDs = array_unique( $module_IDs );
			$loaded     = $deps = $bad = array();
			foreach ( $module_IDs as $mID ) {
				$module_name = $gMDb->get_metadata( 'gmedia_term', $mID, 'module_name', true );
				if ( ! $module_name ) {
					$bad[] = $mID;
					continue;
				}
				if ( in_array( $module_name, $loaded ) )
					continue;

				$module_dir = $grandCore->get_module_path( $module_name );
				if ( ! $module_dir )
					continue;

				/** @var $module */
				include( $module_dir['path'] . '/details.php' );
				$deps     = array_merge( $deps, explode( ',', $module['dependencies'] ) );
				$loaded[] = $module_name;
			}
			$this->module_IDs['modules'] = $loaded;
			$deps                        = array_unique( $deps );
			foreach ( $deps as $js ) {
				wp_enqueue_script( $js );
			}

			$module_IDs = array_diff( $module_IDs, $bad );
			wp_enqueue_style( 'gmedia-styles', $gMediaURL . '/inc/load-styles.php?c=1&load=' . implode( ',', $module_IDs ), array() );
			wp_enqueue_script( 'gmedia-scripts', $gMediaURL . '/inc/load-scripts.php?c=1&load=' . implode( ',', $module_IDs ), array( 'jquery', 'grandMediaGlobalFrontend' ), false, $load_in_footer );
		}

		function load_scripts_footer() {
			global $gMDb, $grandCore;
			$gMediaURL = plugins_url( GRAND_FOLDER );
			if ( isset( $this->module_IDs['quene'] ) && is_array( $this->module_IDs['quene'] ) )
				$module_IDs = array_unique( $this->module_IDs['quene'] );
			else
				return;

			$loaded = $deps = $bad = array();
			if ( isset( $this->module_IDs['modules'] ) && is_array( $this->module_IDs['modules'] ) ) {
				$loaded         = array_unique( $this->module_IDs['modules'] );
				$loaded_modules = '&loaded=' . implode( ',', $loaded );
			}
			else {
				$loaded_modules = '';
			}

			foreach ( $module_IDs as $mID ) {
				$module_name = $gMDb->get_metadata( 'gmedia_term', $mID, 'module_name', true );
				if ( ! $module_name ) {
					$bad[] = $mID;
					continue;
				}
				if ( in_array( $module_name, $loaded ) )
					continue;

				$module_dir = $grandCore->get_module_path( $module_name );
				if ( ! $module_dir )
					continue;

				/** @var $module */
				include( $module_dir['path'] . '/details.php' );
				$deps     = array_merge( $deps, explode( ',', $module['dependencies'] ) );
				$loaded[] = $module_name;
			}
			$deps = array_unique( $deps );
			foreach ( $deps as $js ) {
				wp_enqueue_script( $js );
			}

			$module_IDs = array_diff( $module_IDs, $bad );
			echo '<style type="text/css" scoped="scoped">@import url(' . $gMediaURL . '/inc/load-styles.php?c=1&load=' . implode( ',', $module_IDs ) . $loaded_modules . ') all;</style>' . "\n";
			wp_enqueue_script( 'gmedia-scripts-footer', $gMediaURL . '/inc/load-scripts.php?c=1&load=' . implode( ',', $module_IDs ) . $loaded_modules, array( 'jquery', 'grandMediaGlobalFrontend' ), false, true );
		}

		function shortcode_content($mID) {
			global $gMDb, $grandCore;
			$content = '';

			$gmOptions = get_option( 'gmediaOptions' );
			$taxonomy = 'gmedia_module';
			$module_term = $gMDb->get_term( $mID, $taxonomy, ARRAY_A );
			if ( is_wp_error( $module_term ) || empty( $module_term ) )
				return $content;

			$module_meta = $gMDb->get_metadata( 'gmedia_term', $module_term['term_id'] );
			if ( ! empty( $module_meta ) ) {
				$module_meta = array_map( array( $grandCore, 'maybe_array_0' ), $module_meta );
				$module_meta = array_map( 'maybe_unserialize', $module_meta );
			}
			if ( empty( $module_meta['gMediaQuery'] ) ) {
				return $content;
			}

			if(!$grandCore->is_browser($_SERVER['HTTP_USER_AGENT'])) {

				$upload = $grandCore->gm_upload_dir();
				$libraryUrl = rtrim( $upload['url'], '/' );
				$a = array();
				foreach ( $module_meta['gMediaQuery'] as $i => $tab ) {

					$gMediaQuery = $gMDb->get_gmedias( $tab );
					if ( empty( $gMediaQuery ) ) {
						continue;
					}

					$name   = isset( $tab['tabname'] ) ? $tab['tabname'] : $module_term['name'];
					$tabkey = sanitize_key( $name );
					$a[$i]  = "\n<div class='gallery module-content category-{$tabkey}'><h4 class='gallery-title'>{$name}</h4>";

					$b = array();
					foreach ( $gMediaQuery as $item ) {
						$type  = explode( '/', $item->mime_type );
						$ext   = strrchr( $item->gmuid, '.' );
						$thumb = substr( $item->gmuid, 0, strrpos( $item->gmuid, $ext ) ) . '-thumb' . $ext;
						$meta['views'] = intval($gMDb->get_metadata('gmedia', $item->ID, 'views', true));
						$meta['likes'] = intval($gMDb->get_metadata('gmedia', $item->ID, 'likes', true));
						$b[]   = "
<dl class='gallery-item gmedia-item gmID_{$item->ID}' data-item-id='{$item->ID}'>
	<dt class='gallery-icon'>
		<a href='{$libraryUrl}/{$gmOptions['folder'][$type[0]]}/{$item->gmuid}' title='" . esc_attr(strip_tags(stripslashes($item->title))) . "'>
			<img
				src='{$libraryUrl}/link/{$thumb}'
				alt='" . esc_attr(strip_tags(stripslashes($item->title))) . "'
				width='150' height='150'
				data-src='{$libraryUrl}/{$gmOptions['folder'][$type[0]]}/{$item->gmuid}'
				data-date='{$item->date}'
			/>
		</a>
	</dt>
	<dd class='gallery-caption'>" . stripslashes($item->description) . "</dd>
</dl>";
					}
					$a[$i] .= implode( '', $b ) . "\n";
					$a[$i] .= '</div>';
				}
				if ( !empty( $a ) ) {
					$content = implode( "", $a ) . "\n";
				}
			}
			$content = apply_filters('gm_shortcode_content', $content);

			// module folder
			$module_dir = $grandCore->get_module_path( $module_meta['module_name'] );
			if(empty($gmOptions['gmedia_key']) && $module_dir){
				$module = array();
				include($module_dir['path'] . '/details.php');
				if('free' != $module['status']){
					$content .= '<p><a href="http://codeasily.com/">'.__('Best WordPress Gallery Plugin', 'gmLang').'</a></p>';
				}
			}

			return $content;
		}

		function meta_box_load_styles( $hook ) {
			if ( ( in_array( $hook, array( 'post.php', 'edit.php' ) ) && isset( $_GET['post'] ) && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) || $hook == 'post-new.php' ) {
				wp_enqueue_script( 'grandMediaGlobalBackend' );
				wp_enqueue_style( 'gmedia-meta-box', plugins_url( GRAND_FOLDER ) . '/admin/css/meta-box.css', array(), '1.0' );
				wp_enqueue_script( 'jquery-tool-tabs', plugins_url( GRAND_FOLDER ) . '/admin/js/jquery.tool.tabs.min.js', array( 'jquery' ), '1.2.7' );
				wp_enqueue_script( 'gmedia-meta-box', plugins_url( GRAND_FOLDER ) . '/admin/js/meta-box.js', array( 'jquery' ), '1.0', true );
			}
			else
				return;
		}

		function media_button($context){

			$button = '<a href="#" class="gmedia_button button hidden" onclick="gm_media_button(this); return false;"><span class="wp-media-buttons-icon"></span> '.__('Gmedia', 'gmLang').'</a>';

			return $context.$button;
		}

		/**
		 * add_tinymce_plugin()
		 * Load the TinyMCE plugin : editor_plugin.js
		 *
		 * @param array $plugin_array
		 *
		 * @return array $plugin_array
		 */
		function add_tinymce_plugin( $plugin_array ) {

			$plugin_array['gmedia'] = plugins_url( GRAND_FOLDER ) . '/admin/js/editor_plugin.js';

			return $plugin_array;
		}

		function load_options() {
			// Load the options
			$this->options = get_option( 'gmediaOptions' );
		}

		/**
		 * Call user function to all blogs in network
		 * called during register_activation hook
		 *
		 * @param $pfunction string UserFunction name
		 * @param $networkwide bool Check if plugin has been activated for the entire blog network.
		 *
		 * @return void
		 */
		function grand_network_propagate($pfunction, $networkwide) {
			global $wpdb;

			include_once ( dirname( __FILE__ ) . '/setup.php' );

			if (function_exists('is_multisite') && is_multisite()) {
				// check if it is a network activation - if so, run the activation function
				// for each blog id
				if ($networkwide) {
					//$old_blog = $wpdb->blogid;
					// Get all blog ids
					$blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");
					foreach ($blogids as $blog_id) {
						switch_to_blog($blog_id);
						call_user_func($pfunction);
					}
					//switch_to_blog($old_blog);
					restore_current_blog();
					return;
				}
			}
			call_user_func($pfunction);
		}

		function activate($networkwide) {
			$this->grand_network_propagate('grand_install', $networkwide);
		}

		function deactivate($networkwide) {
			$this->grand_network_propagate('grand_deactivate', $networkwide);
		}

		static function gmedia_uninstall($networkwide) {
			//wp_die( '<h1>This is run on <code>init</code> during uninstallation</h1>', 'Uninstallation hook example' );
			grandLoad::grand_network_propagate('grand_uninstall', $networkwide);
		}

		function new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
			global $wpdb;

			if (is_plugin_active_for_network(GRAND_FOLDER.'/grand-media.php')) {
				//$old_blog = $wpdb->blogid;
				include_once ( dirname( __FILE__ ) . '/setup.php' );
				switch_to_blog($blog_id);
				grand_install();
				//switch_to_blog($old_blog);
				restore_current_blog();
			}
		}

		/*
			// PLUGIN MESSAGE ON PLUGINS PAGE
			function check_message_version($file)
			{
				static $this_plugin;
				global $wp_version;
				if (!$this_plugin) $this_plugin = GRAND_FOLDER;

				if ($file == $this_plugin ){
					$checkfile = "http://codeasily.com/grand-flam.chk";

					$message = wp_remote_fopen($checkfile);

					if($message)
					{
						preg_match( '|grand'.str_replace('.','',GRAND_VERSION).':(.*)$|mi', $message, $theMessage );
						$columns = 5;
						if ( !empty( $theMessage ) )
						{
							$theMessage = trim($theMessage[1]);
							echo '<td colspan="'.$columns.'" class="plugin-update" style="line-height:1.2em; font-size:11px; padding:1px;"><div id="flag-update-msg" style="padding-bottom:1px;" >'.$theMessage.'</div></td>';
						} else {
							return;
						}
					}
				}
			}
		*/
		function add_plugin_links( $links, $file ) {
			// TODO plugin links
			if ( $file == plugin_basename( __FILE__ ) ) {
				$links[] = '<a href="admin.php?page=GrandMedia">' . __( 'Overview', 'gmLang' ) . '</a>';
				$links[] = '<a href="#">' . __( 'Get help', 'gmLang' ) . '</a>';
				$links[] = '<a href="#">' . __( 'Contribute', 'gmLang' ) . '</a>';
				$links[] = '<a href="#">' . __( 'Donate', 'gmLang' ) . '</a>';
			}
			return $links;
		}

		/**
		 * Adds the meta box to the post or page edit screen
		 * @param string $page the name of the current page
		 * @param string $context the current context
		 */
		function do_meta_boxes( $page, $context ) {
			global $grandCore;
			// Plugins that use custom post types can use this filter to hide the Gmedia UI in their post type.
			$gm_post_types = apply_filters( 'gmedia-post-types', array_keys( get_post_types( array('show_ui' => true ) ) ) );

			if ( function_exists( 'add_meta_box' ) && in_array( $page, $gm_post_types ) && 'side' === $context ) {
				add_meta_box( 'gMedia-MetaBox', __( 'Gmedia Gallery MetaBox', 'gmLang' ), array( $grandCore, 'metabox' ), $page, 'side', 'high' );
			}
		}

		function metabox() {
			global $grandCore;
			$grandCore->metabox();
		}

		/**
		 * shortcode_check
		 *
		 * Check if post/page have gmedia shortcode and save/delete postmeta
		 */
		function shortcode_check( $post_id ) {
			// verify post is not a revision and exit on autosave
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
				return $post_id;
			}
			// check capabilities
			if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return $post_id;
				}
			}
			elseif ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
			// check nonce
			if ( ! isset( $_POST['_ajax_nonce-add-meta'] ) || ! wp_verify_nonce( $_POST['_ajax_nonce-add-meta'], 'add-meta' ) ) {
				return $post_id;
			}

			if(isset($_POST['content'])) {
				$content = $_POST['content'];
			} else if(isset($_POST['post_content'])){
				$content = $_POST['post_content'];
			} else {
				return $post_id;
			}

			$c = preg_match_all( '/\[gmedia \s*id=(\d+)\s*?\]/', $content, $matches, PREG_PATTERN_ORDER );
			if ( $c ) {
				update_post_meta( $post_id, '_gmedia_module_id', implode( ',', $matches[1] ) );
			}
			else {
				delete_post_meta( $post_id, '_gmedia_module_id' );
			}
			return $post_id;
		}
	}

	// Let's start the holy plugin
	global $grandLoad;
	$grandLoad = new grandLoad();

}
