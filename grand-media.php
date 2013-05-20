<?php
/*
Plugin Name: Gmedia Gallery
Plugin URI: http://wordpress.org/extend/plugins/grand-media/
Description: Grand Media Gallery - powerfull media library plugin for creating beautiful galleries and managing files.
Version: 0.6.3
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

		var $version = '0.6.3';
		var $dbversion = '0.6.2';
		var $minium_WP = '3.3';
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
			register_uninstall_hook( $this->plugin_name, 'grandLoad::GrandMedia_uninstall' );

			// Start this plugin once all other plugins are fully loaded
			add_action( 'plugins_loaded', array( &$this, 'start_plugin' ) );

			//Add some message on the plugin page
			//add_action( 'after_plugin_row', array(&$this, 'check_message_version') );

			//Add some links on the plugin page
			add_filter( 'plugin_row_meta', array( &$this, 'add_plugin_links' ), 10, 2 );

			add_action( 'admin_menu', array( &$this, 'add_meta_box' ) );
			add_action( 'admin_enqueue_scripts', array( &$this, 'meta_box_load_styles' ) );
			add_filter( 'mce_external_plugins', array( &$this, 'add_tinymce_plugin' ), 5 );

			add_action( 'save_post', array( &$this, 'gMedia_shortcode_check' ) );
		}

		function gm_admin_notices() {
			echo '<div id="message" class="error"><p><strong>' . get_option( 'gmediaInitCheck' ) . '</strong></p></div>';
			delete_option( 'gmediaInitCheck' );
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
					add_action( 'admin_notices', array( &$this, 'gm_admin_notices' ) );

			}
			else {

				// Add the script and style files
				add_action( 'template_redirect', array( &$this, 'load_scripts' ) );

				// Add a version number to the header
				add_action( 'wp_head', create_function( '', 'echo "\n<!-- <meta name=\'GrandMedia\' content=\'' . $this->version . '\' /> -->\n";' ) );
				add_action( 'wp_footer', array( &$this, 'load_scripts_footer' ) );

			}
		}

		function required_version() {

			global $wp_version;

			// Check for WP version installation
			if ( version_compare( $wp_version, $this->minium_WP, '<' ) ) {
				$note = sprintf( __( 'Sorry, GrandMedia works only under WordPress %s or higher', 'gmLang' ), $this->minium_WP );
				update_option( 'gmediaInitCheck', $note );
				add_action( 'admin_notices', array( &$this, 'gm_admin_notices' ) );
				return false;
			}
			if ( version_compare( '5.2', phpversion(), '>' ) ) {
				$note = sprintf( __( 'Attention! Your server php version is: %s. GrandMedia requires php version 5.2+ in order to run properly. Please upgrade your server!', 'gmLang' ), phpversion() );
				update_option( 'gmediaInitCheck', $note );
				add_action( 'admin_notices', array( &$this, 'gm_admin_notices' ) );
			}
			if ( version_compare( '5.3', phpversion(), '>' ) ) {
				if ( ini_get( 'safe_mode' ) ) {
					$note = __( 'Attention! Your server safe mode is: ON. GrandMedia requires safe mode to be OFF in order to run properly. Please set your server safe mode option!', 'gmLang' );
					update_option( 'gmediaInitCheck', $note );
					add_action( 'admin_notices', array( &$this, 'gm_admin_notices' ) );
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

			$gMediaURL      = plugins_url( GRAND_FOLDER );
			$upload         = $grandCore->gm_upload_dir();
			$load_in_footer = true;

			wp_enqueue_script( 'jquery' );
			wp_register_script( 'grandMediaGlobalFrontend', $gMediaURL . '/admin/js/gmedia.global.front.js', array( 'jquery' ), '1.0' );
			wp_localize_script( 'grandMediaGlobalFrontend', 'gMediaGlobalVar', array(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'loading'    => '/admin/images/throbber.gif',
				'uploadPath' => rtrim( $upload['url'], '/' ),
				'pluginPath' => $gMediaURL
			) );
			wp_enqueue_script( 'grandMediaGlobalFrontend' );
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
				//echo '<pre>'; print_r($wp_query); echo '</pre>';
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
				$module_name = $gMDb->gmGetMetaData( 'gmedia_term', $mID, 'module_name', true );
				if ( ! $module_name ) {
					$bad[] = $mID;
					continue;
				}
				if ( in_array( $module_name, $loaded ) )
					continue;

				$module_dir = $grandCore->gm_get_module_path( $module_name );
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
				$module_name = $gMDb->gmGetMetaData( 'gmedia_term', $mID, 'module_name', true );
				if ( ! $module_name ) {
					$bad[] = $mID;
					continue;
				}
				if ( in_array( $module_name, $loaded ) )
					continue;

				$module_dir = $grandCore->gm_get_module_path( $module_name );
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

			if($grandCore->isCrawler($_SERVER['HTTP_USER_AGENT'])) {
				$taxonomy = 'gmedia_module';
				$module = $gMDb->gmGetTerm( $mID, $taxonomy, ARRAY_A );
				if ( is_wp_error( $module ) || empty( $module ) )
					return '';

				$grandMediaQuery = $gMDb->gmGetMetaData( 'gmedia_term', $module['term_id'], 'gMediaQuery', true );
				if ( empty( $grandMediaQuery ) ) {
					return '';
				}
				$grandMediaQuery = array_map( 'maybe_unserialize', $grandMediaQuery );

				$upload = $grandCore->gm_upload_dir();
				$libraryUrl = rtrim( $upload['url'], '/' );
				$a = array();
				foreach ( $grandMediaQuery as $i => $tab ) {

					$gMediaQuery = $gMDb->gmGetMedias( $tab );
					if ( empty( $gMediaQuery ) ) {
						continue;
					}

					$name   = isset( $tab['tabname'] ) ? $tab['tabname'] : $module['name'];
					$tabkey = sanitize_key( $name );
					$a[$i]  = "\n<div class='{$tabkey}'><h4>" . $name . "</h4>\n";

					$b = array();
					foreach ( $gMediaQuery as $item ) {
						$ext   = strrchr( $item->gmuid, '.' );
						$thumb = substr( $item->gmuid, 0, strrpos( $item->gmuid, $ext ) ) . '-thumb' . $ext;
						$meta['views'] = intval($gMDb->gmGetMetaData('gmedia', $item->ID, 'views', true));
						$meta['likes'] = intval($gMDb->gmGetMetaData('gmedia', $item->ID, 'likes', true));
						$b[]   = "	<p><a id='gmID_{$item->ID}' class='gmLink' style='display:inline-block; margin-right:10px;' href='{$libraryUrl}/image/{$item->gmuid}'><img src='{$libraryUrl}/link/{$thumb}' alt='" . esc_html( $item->title ) . "' data-date='{$item->date}' /></a><span style='display:inline-block;'><strong>' . $item->title . '</strong><br />' . $item->description . '</span></p>";
					}
					$a[$i] .= implode( "\n", $b ) . "\n";
					$a[$i] .= '</div>';
				}
				if ( !empty( $a ) ) {
					$content = implode( "", $a ) . "\n";
				}
			}

			return apply_filters('gm_shortcode_content', $content);
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

		function activate() {
			include_once ( dirname( __FILE__ ) . '/setup.php' );
			// check for tables
			grand_install();
		}

		function deactivate() {
			// remove & reset the init check option
			delete_option( 'gmediaInitCheck' );
		}

		static function GrandMedia_uninstall() {
			// TODO check uninstall hook
			//wp_die( '<h1>This is run on <code>init</code> during uninstallation</h1>', 'Uninstallation hook example' );
			include_once ( dirname( __FILE__ ) . '/setup.php' );
			grand_uninstall();
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
		 * add_meta_box
		 *
		 * Adds meta box to posts/pages
		 */
		function add_meta_box() {
			global $grandCore;
			if ( function_exists( 'add_meta_box' ) ) {
				add_meta_box( 'gMedia-MetaBox', __( 'GrandMedia MetaBox', 'gmLang' ), array( $grandCore, 'gMedia_MetaBox' ), 'post', 'side', 'high' );
				add_meta_box( 'gMedia-MetaBox', __( 'GrandMedia MetaBox', 'gmLang' ), array( $grandCore, 'gMedia_MetaBox' ), 'page', 'side', 'high' );
			}
		}

		function gMedia_MetaBox() {
			global $grandCore;
			$grandCore->gMedia_MetaBox();
		}

		/**
		 * gMedia_shortcode_check
		 *
		 * Check if post/page have gmedia shortcode and save/delete postmeta
		 */
		function gMedia_shortcode_check( $post_id ) {
			// verify post is not a revision and exit on autosave
			if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! wp_is_post_revision( $post_id ) ) {
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

			$c = preg_match_all( '/\[gmedia id=(\d+)\]/', $_POST['post_content'], $matches, PREG_PATTERN_ORDER );
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
