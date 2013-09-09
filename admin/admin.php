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

	}

	// integrate the menu
	function add_menu() {
		$gMediaURL = plugins_url( GRAND_FOLDER );
		add_object_page( __( 'Gmedia Library', 'gmLang' ), 'Gmedia Gallery', 'edit_pages', 'GrandMedia', array( &$this, 'show_menu' ), $gMediaURL . '/admin/images/gm-icon.png' );
		add_submenu_page( 'gmedia-plugin', __( 'Gmedia Library', 'gmLang' ), __( 'Gmedia Library', 'gmLang' ), 'edit_pages', 'GrandMedia', array( &$this, 'show_menu' ) );
		add_submenu_page( 'gmedia-plugin', __( 'Gmedia: Tags & Categories', 'gmLang' ), __( 'Tags & Categories', 'gmLang' ), 'edit_pages', 'GrandMedia_Tags_and_Categories', array( &$this, 'show_menu' ) );
		add_submenu_page( 'gmedia-plugin', __( 'Add Media Files', 'gmLang' ), __( 'Add Files', 'gmLang' ), 'edit_pages', 'GrandMedia_AddMedia', array( &$this, 'show_menu' ) );
		add_submenu_page( 'gmedia-plugin', __( 'Gallery Manager', 'gmLang' ), __( 'Manage Galleries...', 'gmLang' ), 'edit_pages', 'GrandMedia_Modules', array( &$this, 'show_menu' ) );
		add_submenu_page( 'gmedia-plugin', __( 'Gmedia Settings', 'gmLang' ), __( 'Settings', 'gmLang' ), 'edit_pages', 'GrandMedia_Settings', array( &$this, 'show_menu' ) );
		add_submenu_page( 'gmedia-plugin', __( 'Wordpress Media Library', 'gmLang' ), __( 'Wordpress Media Library', 'gmLang' ), 'edit_pages', 'GrandMedia_WordpressLibrary', array( &$this, 'show_menu' ) );

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

		wp_register_script( 'grandMediaGlobalBackend', $gMediaURL . '/admin/js/gmedia.global.back.js', array( 'jquery' ), '1.0' );
		wp_localize_script( 'grandMediaGlobalBackend', 'gMediaGlobalVar', array(
			'nonce'      => wp_create_nonce( 'grandMedia' ),
			'loading'    => $gMediaURL . '/admin/images/throbber.gif',
			'uploadPath' => rtrim( $upload['url'], '/' ),
			'pluginPath' => $gMediaURL
		) );
		//wp_enqueue_script('grandMediaGlobalBackend');

		// no need to go on if it's not a plugin page
		if ( 'admin.php' != $hook && strpos( $grandCore->_get( 'page' ), 'GrandMedia' ) === false )
			return;

		wp_enqueue_script( 'dataset', $gMediaURL . '/admin/js/jquery.dataset.js', array( 'jquery' ), '0.1.0' );
		wp_enqueue_script( 'qtip', $gMediaURL . '/admin/js/qtip/jquery.qtip.min.js', array( 'jquery' ), '2.1.1' );
		wp_enqueue_script( 'outside-events', $gMediaURL . '/admin/js/outside-events.js', array( 'jquery' ), '1.1' );
		wp_register_script( 'GrandMedia', $gMediaURL . '/admin/js/grand-media.js', array( 'jquery', 'grandMediaGlobalBackend' ), '3.6.0' );
		wp_localize_script( 'GrandMedia', 'grandMedia', array(
			'error3'   => $grandCore->message(__( 'Disable your Popup Blocker and try again.', 'gmLang' )),
			'download' => $grandCore->message(__( 'downloading...', 'gmLang' )),
			'wait' => $grandCore->message(__( 'Working. Wait please.', 'gmLang' )),
			'nonce' => wp_create_nonce( 'grandMedia' ),
		) );
		wp_enqueue_script( 'GrandMedia' );

		//wp_enqueue_script('jquery.quicksearch', $gMediaURL.'/admin/js/jquery.quicksearch.js', array('jquery'), '10.09.28');
		if ( isset( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case "GrandMedia" :
				case "GrandMedia_WordpressLibrary" :
					wp_enqueue_script( 'swfobject' );
					wp_enqueue_script( 'fancybox', $gMediaURL . '/admin/js/jquery.fancybox.js', array( 'jquery' ), '1.3.4' );
					wp_enqueue_script( 'easing', $gMediaURL . '/admin/js/jquery.easing.js', array( 'jquery' ), '1.3.0' );
					wp_enqueue_script( 'mediaelement', $gMediaURL . '/inc/mediaelement/mediaelement-and-player.min.js', array( 'jquery' ), '2.13.0' );
					break;
				case "GrandMedia_Tags_and_Categories" :
					wp_enqueue_script( 'quicksearch', $gMediaURL . '/admin/js/jquery.quicksearch.js', array( 'jquery' ), '1.0.0' );
					break;
				case "GrandMedia_AddMedia" :
					$tab = $grandCore->_get('tab', 'upload');
					if($tab == 'upload') {
						wp_enqueue_script( 'plupload', $gMediaURL . '/admin/js/plupload/plupload.js', array( 'jquery' ), '1.5.7' );
						wp_enqueue_script( 'plupload-flash', $gMediaURL . '/admin/js/plupload/plupload.flash.js', array( 'jquery' ), '1.5.7' );
						wp_enqueue_script( 'plupload-html4', $gMediaURL . '/admin/js/plupload/plupload.html4.js', array( 'jquery' ), '1.5.7' );
						wp_enqueue_script( 'plupload-html5', $gMediaURL . '/admin/js/plupload/plupload.html5.js', array( 'jquery' ), '1.5.7' );
						wp_enqueue_script( 'jquery.plupload.queue', $gMediaURL . '/admin/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js', array( 'jquery' ), '1.5.7' );
						wp_enqueue_script( 'termBox', $gMediaURL . '/admin/js/termbox.js', array( 'jquery' ), '1.0.0' );
						wp_localize_script( 'termBox', 'gMediaTermBox', array(
							'nonce' => wp_create_nonce( 'grandMedia' ),
						) );
						wp_enqueue_script( 'suggest' );
					} else if($tab == 'import') {
						wp_enqueue_script( array( 'jquery-ui-tabs' ) );
					}
					break;
				case "GrandMedia_Settings" :
					//wp_enqueue_script( 'jscolor', $gMediaURL . '/admin/js/jscolor/jscolor.js', array( 'grandMediaGlobalBackend' ), '1.4.0' );
				case "GrandMedia_Modules" :
					if ( isset( $_GET['module'] ) ) {
						wp_enqueue_script( 'jscolor', $gMediaURL . '/admin/js/jscolor/jscolor.js', array( 'grandMediaGlobalBackend' ), '1.4.0' );
					}
					wp_enqueue_script( array( 'jquery-ui-tabs' ) );
					break;
			}
		}

	}

	function load_styles( $hook ) {
		global $grandCore;
		// no need to go on if it's not a plugin page
		if ( 'admin.php' != $hook && strpos( $grandCore->_get( 'page' ), 'GrandMedia' ) === false )
			return;
		$gMediaURL = plugins_url( GRAND_FOLDER );

		wp_enqueue_style( 'qtip', $gMediaURL . '/admin/js/qtip/jquery.qtip.css', array(), '2.1.1', 'screen' );
		wp_enqueue_style( 'fancybox', $gMediaURL . '/admin/css/jquery.fancybox.css', array(), '1.3.4', 'screen' );
		wp_enqueue_style( 'grand-media', $gMediaURL . '/admin/css/grand-media.css', array(), '3.6.0', 'screen' );
		switch ( $_GET['page'] ) {
			case "GrandMedia_AddMedia" :
				$tab = $grandCore->_get('tab', 'upload');
				if($tab == 'upload') {
					wp_enqueue_style( 'jquery.plupload.queue', $gMediaURL . '/admin/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css', array(), '1.5.7', 'screen' );
				} else if($tab == 'import') {
					wp_enqueue_style( 'jquery-ui-tabs', $gMediaURL . '/admin/css/jquery-ui-tabs.css', array(), '1.0.0', 'screen' );
				}
				break;
			case "GrandMedia_Settings" :
			case "GrandMedia_Modules" :
				wp_enqueue_style( 'jquery-ui-tabs', $gMediaURL . '/admin/css/jquery-ui-tabs.css', array(), '1.0.0', 'screen' );
				break;
			case "GrandMedia" :
			case "GrandMedia_WordpressLibrary" :
				wp_enqueue_style( 'mediaelement', $gMediaURL . '/inc/mediaelement/mediaelementplayer.min.css', array(), '2.13.0', 'screen' );
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
				$link = '<a href="mailto:gmediafolder@gmail.com" target="_blank">gmediafolder@gmail.com</a>';
				break;
		}
		if ( ! empty( $link ) ) {
			$contextual_help = '<p>' . sprintf( __( "Contact deveoper: %s", 'gmLang' ), $link ) . '</p>';
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
