<?php
/**
 * grandAdminPanel - Admin Section for GRAND Media
 *
 */
class grandAdminPanel {

	// constructor
	function grandAdminPanel() {
		global $pagenow;

		// Add the admin menu
		add_action( 'admin_menu', array( &$this, 'add_menu' ) );

		// Add the script and style files
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_styles' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_scripts' ) );

		add_filter( 'contextual_help', array( &$this, 'show_help' ), 10, 3 );
		add_filter( 'screen_settings', array( &$this, 'edit_screen_meta' ), 10, 2 );
		add_filter( 'set-screen-option', array( &$this, 'save_screen_meta'), 11, 3);
		if ( 'media.php' === $pagenow ) {
			add_filter( 'wp_redirect', array( &$this, 'gm_redirect' ), 10, 2 );
		}

		if ( 'admin.php' == $pagenow && isset($_GET['page']) && strpos( $_GET['page'], 'GrandMedia' ) !== false && isset($_GET['iframe']) && $_GET['iframe'] == 'gmedia' ) {
			add_action( 'admin_init', array( &$this, 'gmedia_in_iframe' ) );
		}

	}

	// load gmedia pages in iframe
	function gmedia_in_iframe() {
		global $pagenow;
		$hook = add_management_page( 'Gmedia Tool', 'Gmedia Tool', 'administrator', 'gmedia-tool');
		set_current_screen('gmedia-tool');

		add_filter('admin_body_class', array( &$this, 'gmedia_in_iframe_body_class'));
		define('IFRAME_REQUEST', true);
		define('GMEDIA_IFRAME_TOOL', true);
		iframe_header('Gmedia Tool');

		include_once ( dirname( __FILE__ ) . '/addmedia.php' );
		grandMedia_AddMedia();

		//$this->show_menu();

		iframe_footer();
		exit;
	}
	function gmedia_in_iframe_body_class(){
		return 'gmedia_body';
	}

	// integrate the menu
	function add_menu() {
		$gMediaURL = plugins_url( GRAND_FOLDER );
		add_object_page( __( 'Gmedia Library', 'gmLang' ), 'Gmedia Gallery', 'edit_pages', 'GrandMedia', array( &$this, 'show_menu' ), $gMediaURL . '/admin/images/gm-icon.png' );
		add_submenu_page( 'GrandMedia', __( 'Gmedia Library', 'gmLang' ), __( 'Gmedia Library', 'gmLang' ), 'edit_pages', 'GrandMedia', array( &$this, 'show_menu' ) );
		add_submenu_page( 'GrandMedia', __( 'Add Media Files', 'gmLang' ), __( 'Add Files', 'gmLang' ), 'edit_pages', 'GrandMedia_AddMedia', array( &$this, 'show_menu' ) );
		add_submenu_page( 'GrandMedia', __( 'Gmedia: Tags & Categories', 'gmLang' ), __( 'Tags & Categories', 'gmLang' ), 'edit_pages', 'GrandMedia_Tags_and_Categories', array( &$this, 'show_menu' ) );
		add_submenu_page( 'GrandMedia', __( 'Gallery Manager', 'gmLang' ), __( 'Manage Galleries...', 'gmLang' ), 'edit_pages', 'GrandMedia_Modules', array( &$this, 'show_menu' ) );
		add_submenu_page( 'GrandMedia', __( 'Gmedia Settings', 'gmLang' ), __( 'Settings', 'gmLang' ), 'edit_pages', 'GrandMedia_Settings', array( &$this, 'show_menu' ) );
		add_submenu_page( 'GrandMedia', __( 'Wordpress Media Library', 'gmLang' ), __( 'Wordpress Media Library', 'gmLang' ), 'edit_pages', 'GrandMedia_WordpressLibrary', array( &$this, 'show_menu' ) );

	}

	// load the script for the defined page and load only this code
	function show_menu() {

		global $grandLoad;

		// check for upgrade
		if ( get_option( 'grand_db_version' ) != GRAND_DBVERSION ) {
			//return;			
		}

		// Set installation date
		if ( empty( $grandLoad->options['installDate'] ) ) {
			$grandLoad->options['installDate'] = time();
			update_option( 'gmediaOptions', $grandLoad->options );
		}

		include_once ( dirname( __FILE__ ) . '/functions.php' );
		include_once ( dirname( __FILE__ ) . '/library.php' );
		// Initate the Library page
		$grandLoad->library = new grandLibrary();

		switch ( $_GET['page'] ) {
			case "GrandMedia" :
				break;
		}
	}

	function load_scripts( $hook ) {
		global $grandCore;
		$gMediaURL = plugins_url( GRAND_FOLDER );
		$upload    = $grandCore->gm_upload_dir();

		// no need to go on if it's not a plugin page
		if ( 'admin.php' != $hook && strpos( $grandCore->_get( 'page' ), 'GrandMedia' ) === false )
			return;

		// todo remove this script
		wp_enqueue_script( 'dataset', $gMediaURL . '/assets/jq-plugins/jquery.dataset.js', array( 'jquery' ), '0.1.0' );

		wp_enqueue_script( 'qtip' );
		wp_enqueue_script( 'outside-events' );

		if ( isset( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case "GrandMedia" :
				case "GrandMedia_WordpressLibrary" :
					wp_enqueue_script( 'swfobject' );
					wp_enqueue_script( 'fancybox' );
					wp_enqueue_script( 'mediaelement' );
					break;
				case "GrandMedia_Tags_and_Categories" :
					wp_enqueue_script( 'quicksearch' );
					break;
				case "GrandMedia_AddMedia" :
					$tab = $grandCore->_get('tab', 'upload');
					if($tab == 'upload') {
						wp_enqueue_script( 'plupload', $gMediaURL . '/assets/plupload/plupload.js', array( 'jquery' ), '1.5.7' );
						wp_enqueue_script( 'plupload-flash', $gMediaURL . '/assets/plupload/plupload.flash.js', array( 'jquery' ), '1.5.7' );
						wp_enqueue_script( 'plupload-html4', $gMediaURL . '/assets/plupload/plupload.html4.js', array( 'jquery' ), '1.5.7' );
						wp_enqueue_script( 'plupload-html5', $gMediaURL . '/assets/plupload/plupload.html5.js', array( 'jquery' ), '1.5.7' );
						wp_enqueue_script( 'jquery.plupload.queue', $gMediaURL . '/assets/plupload/jquery.plupload.queue/jquery.plupload.queue.js', array( 'jquery' ), '1.5.7' );
						wp_enqueue_script( 'suggest' );
						wp_enqueue_script( 'termBox', $gMediaURL . '/admin/js/termbox.js', array( 'jquery', 'suggest' ), '0.9.1' );
						wp_localize_script( 'termBox', 'gMediaTermBox', array(
							'nonce' => wp_create_nonce( 'grandMedia' ),
						) );
					} else if($tab == 'import') {
						wp_enqueue_script( 'jquery-ui-tabs' );
					}
					break;
				case "GrandMedia_Settings" :
					// enqueue jscolor
				case "GrandMedia_Modules" :
					if ( isset( $_GET['module'] ) ) {
						wp_enqueue_script( 'jscolor' );
					}
					wp_enqueue_script( 'jquery-ui-tabs' );
					break;
			}
		}

		wp_enqueue_script( 'grand-media' );

	}

	function load_styles( $hook ) {
		global $grandCore;
		// no need to go on if it's not a plugin page
		if ( 'admin.php' != $hook && strpos( $grandCore->_get( 'page' ), 'GrandMedia' ) === false )
			return;
		$gMediaURL = plugins_url( GRAND_FOLDER );

		wp_enqueue_style( 'qtip' );
		wp_enqueue_style( 'fancybox' );
		wp_enqueue_style( 'grand-media' );
		switch ( $_GET['page'] ) {
			case "GrandMedia_AddMedia" :
				$tab = $grandCore->_get('tab', 'upload');
				if($tab == 'upload') {
					wp_enqueue_style( 'jquery.plupload.queue', $gMediaURL . '/assets/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css', array(), '1.5.7', 'screen' );
				} else if($tab == 'import') {
					wp_enqueue_style( 'jquery-ui-tabs', $gMediaURL . '/admin/css/jquery-ui-tabs.css', array(), '0.9.0', 'screen' );
				}
				break;
			case "GrandMedia_Settings" :
			case "GrandMedia_Modules" :
				wp_enqueue_style( 'jquery-ui-tabs', $gMediaURL . '/admin/css/jquery-ui-tabs.css', array(), '0.9.0', 'screen' );
				break;
			case "GrandMedia" :
			case "GrandMedia_WordpressLibrary" :
				wp_enqueue_style( 'mediaelement' );
				break;
		}
	}

	function show_help( $contextual_help, $screen_id ) {
		// since WP3.0 it's an object
		if ( is_object( $screen_id ) )
			$screen_id = $screen_id->id;
		$link = '';
		switch ( $screen_id ) {
			case "toplevel_page_GrandMedia" :
			case "admin_page_GrandMedia_Settings" :
				$link = '<a href="http://codeasily.com/community/forum/gmedia-gallery-wordpress-plugin/" target="_blank">'.__( 'Support Forum', 'gmLang' ).'</a>';
				break;
		}
		if ( ! empty( $link ) ) {
			//$contextual_help = '<p>' . sprintf( __( "Support Forum: %s", 'gmLang' ), $link ) . '</p>';
			$contextual_help = '<p>' . $link . '</p>';
			/*
						$temp = '<div class="metabox-prefs">'.$link.'</div>
			<h5>'.__('More Help & Info', 'gmLang').'</h5>
			<div class="metabox-prefs">
				<a href="#" target="_blank">'.__('GRAND Media Video Tutorial', 'gmLang').'</a>
				| <a href="#" target="_blank">'.__('GRAND Media FAQ', 'gmLang').'</a>
				| <a href="#" target="_blank">'.__('GRAND Media Review', 'gmLang').'</a>
				| <a href="#" target="_blank">'.__('Get your language pack', 'gmLang').'</a>
				| <a href="#" target="_blank">'. __('Flash Modules for GRAND Media', 'gmLang').'</a>
			</div>'."\n";
			*/
		}
		return $contextual_help;
	}

	function edit_screen_meta( $current, $screen ) {
		if ( strpos( $screen->id, 'GrandMedia' ) !== false ) {
			$current  = '<h4>' . __( 'Settings for this page', 'gmLang' ) . '</h4>
<input type="hidden" name="wp_screen_options[option]" value="gm_screen_options" />';
			$button = get_submit_button( __( 'Apply', 'gmLang' ), 'button', 'screen-options-apply', false );
			$gmOptions   = get_option( 'gmediaOptions' );

			switch ( $screen->id ) {
				case "toplevel_page_GrandMedia" :
					$current .= '<input type="hidden" name="wp_screen_options[value]" value="'.$screen->id.'" />
<div class="screen-options">
	<label><input type="number" max="999" min="0" step="5" name="gm_screen_options[per_page_gmedia]" class="screen-per-page" value="'.$gmOptions['per_page_gmedia'].'" /> '.__( 'Posts per page', 'gmLang' ).'</label>
	'.$button.'
</div>';
					break;
				case "admin_page_GrandMedia_WordpressLibrary" :
					$current .= '<input type="hidden" name="wp_screen_options[value]" value="'.$screen->id.'" />
<div class="screen-options">
	<label><input type="number" max="999" min="0" step="5" name="gm_screen_options[per_page_wpmedia]" class="screen-per-page" value="'.$gmOptions['per_page_wpmedia'].'" /> '.__( 'Posts per page', 'gmLang' ).'</label>
	'.$button.'
</div>';
					break;
			}
		}
		return $current;
	}

	function save_screen_meta( $status, $option, $value) {
		global $user_ID;
		if ( 'gm_screen_options' == $option ) {
			$gmOptions = get_option( 'gmediaOptions' );
			foreach ( $_POST['gm_screen_options'] as $key => $val ) {
				$gmOptions[$key] = $val;
			}
			update_option( 'gmediaOptions', $gmOptions );
			$gm_screen_options = get_user_meta($user_ID, 'gm_screen_options', true);
			if(!is_array($gm_screen_options))
				$gm_screen_options = array();
			$value = array_merge($gm_screen_options, array( $value => $_POST['gm_screen_options'] ));
		}

		return $value;
	}

	// redirect to original referer after update
	function gm_redirect( $location ) {
		global $pagenow;
		if ( 'media.php' === $pagenow && isset( $_POST['_wp_original_http_referer'] ) ) {
			if ( strpos( $_POST['_wp_original_http_referer'], 'GrandMedia' ) !== false ) {
				return $_POST['_wp_original_http_referer'];
			}
			else {
				return $location;
			}
		}
		return $location;
	}

}
