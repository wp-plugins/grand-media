<?php
/**
 * GmediaAdmin - Admin Section for GRAND Media
 *
 */
class GmediaAdmin {
	var $pages = array();

	// constructor
	function __construct() {
		global $pagenow;

		// Add the admin menu
		add_action( 'admin_menu', array( &$this, 'add_menu' ) );

		// Add the script and style files
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_scripts' ) );

		add_filter( 'screen_settings', array( &$this, 'screen_settings' ), 10, 2 );
		add_filter( 'set-screen-option', array( &$this, 'screen_settings_save'), 11, 3);

		if ( 'admin.php' == $pagenow && isset($_GET['page']) && strpos( $_GET['page'], 'GrandMedia' ) !== false && isset($_GET['iframe']) && $_GET['iframe'] == 'gmedia' ) {
			add_action( 'admin_init', array( &$this, 'gmedia_in_iframe' ) );
		}

	}

	// load gmedia pages in iframe
	function gmedia_in_iframe() {
		global $pagenow;
		$hook = add_management_page( 'Gmedia Tool', 'Gmedia Tool', 'edit_pages', 'gmedia-tool');
		set_current_screen('gmedia-tool');

		add_filter('admin_body_class', array( &$this, 'gmedia_in_iframe_body_class'));
		define('IFRAME_REQUEST', true);
		define('GMEDIA_IFRAME_TOOL', true);
		iframe_header('Gmedia Tool');

		include_once ( dirname( __FILE__ ) . '/addmedia.php' );
		gmedia_AddMedia();

		//$this->show_menu();

		iframe_footer();
		exit;
	}
	function gmedia_in_iframe_body_class(){
		return 'gmedia-body';
	}

	// integrate the menu
	function add_menu() {
		$gMediaURL = plugins_url( GMEDIA_FOLDER );
		$this->pages = array(
				add_object_page( __( 'Gmedia Library', 'gmLang' ), 'Gmedia Gallery', 'edit_pages', 'GrandMedia', array( &$this, 'shell' ), $gMediaURL . '/admin/images/gm-icon.png' ),
				add_submenu_page( 'GrandMedia', __( 'Gmedia Library', 'gmLang' ), __( 'Gmedia Library', 'gmLang' ), 'edit_pages', 'GrandMedia', array( &$this, 'shell' ) ),
				add_submenu_page( 'GrandMedia', __( 'Add Media Files', 'gmLang' ), __( 'Add Files', 'gmLang' ), 'edit_pages', 'GrandMedia_AddMedia', array( &$this, 'shell' ) ),
				add_submenu_page( 'GrandMedia', __( 'Gmedia: Tags & Albums', 'gmLang' ), __( 'Tags & Albums', 'gmLang' ), 'edit_pages', 'GrandMedia_Tags_and_Albums', array( &$this, 'shell' ) ),
				add_submenu_page( 'GrandMedia', __( 'Gallery Manager', 'gmLang' ), __( 'Manage Galleries...', 'gmLang' ), 'edit_pages', 'GrandMedia_Modules', array( &$this, 'shell' ) ),
				add_submenu_page( 'GrandMedia', __( 'Gmedia Settings', 'gmLang' ), __( 'Settings', 'gmLang' ), 'edit_pages', 'GrandMedia_Settings', array( &$this, 'shell' ) ),
				add_submenu_page( 'GrandMedia', __( 'Wordpress Media Library', 'gmLang' ), __( 'WP Media Library', 'gmLang' ), 'edit_pages', 'GrandMedia_WordpressLibrary', array( &$this, 'shell' ) )
		);

		foreach($this->pages as $page){
			add_action("load-$page", array( &$this, 'screen_help' ));
		}
	}

	// load the script for the defined page and load only this code
	// Display shell of plugin
	function shell() {
		global $gmProcessor;

		// check for upgrade
		if ( get_option( 'grand_db_version' ) != GMEDIA_DBVERSION ) {
			//return;
		}

		include_once ( dirname( __FILE__ ) . '/functions.php' );

		$sideLinks = $this->sideLinks();
		?>
		<div id="gmedia-container">
			<div id="gmedia-header" class="clearfix">
				<div id="gmedia-logo">Gmedia<small> by CodEasily.com </small></div>
				<h2><?php echo $sideLinks['grandTitle']; ?></h2>
			</div>
			<div id="gm-message"></div>
			<div class="container-fluid">
				<div class="row row-fx180-fl">
					<div class="col-sm-2 hidden-xs" id="sidebar" role="navigation">
						<?php echo $sideLinks['sideLinks']; ?>
					</div>
					<div class="col-sm-10 col-xs-12">
						<?php
						if(!empty($gmProcessor->msg)){
							echo '<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.implode('<br />', array_filter($gmProcessor->msg)).'</div>';
						}
						if(!empty($gmProcessor->error)){
							echo '<div class="alert alert-danger alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>'.implode('<br />', array_filter($gmProcessor->error)).'</div>';
						}

						$this->controller();
						?>
					</div>
				</div>
			</div>
		</div>
	<?php
	}

	function sideLinks() {
		global $submenu, $gmCore, $gmProcessor;
		$content['sideLinks'] = '
		<div id="gmedia-navbar">
			<div class="row">
				<ul class="list-group">';
		foreach ( $submenu['GrandMedia'] as $menuKey => $menuItem ) {
			if ( $submenu['GrandMedia'][$menuKey][2] == $gmProcessor->page ) {
				$iscur                 = ' active';
				$content['grandTitle'] = $submenu['GrandMedia'][$menuKey][3];
			}
			else {
				$iscur = '';
			}

			$content['sideLinks'] .= "\n".'<a class="list-group-item' . $iscur . '" href="' . admin_url( 'admin.php?page=' . $submenu['GrandMedia'][$menuKey][2] ) . '">' . $submenu['GrandMedia'][$menuKey][0] . '</a>';
		}
		$content['sideLinks'] .= '
				</ul>
			</div>
		</div>';

		return $content;
	}

	function controller() {
		global $gmProcessor;
		switch ( $gmProcessor->page ) {
			case 'GrandMedia_Settings':
				include_once ( dirname( __FILE__ ) . '/settings.php' );
				gmSettings();
				break;
			case 'GrandMedia_Tags_and_Albums':
				include_once(dirname(__FILE__) . '/terms.php');
				gmTagsAlbums();
				break;
			case 'GrandMedia_AddMedia':
				include_once ( dirname( __FILE__ ) . '/addmedia.php' );
				gmedia_AddMedia();
				break;
			case 'GrandMedia_Modules':
				include_once ( dirname( __FILE__ ) . '/modules.php' );
				if ( isset( $_GET['module'] ) ) {
					gmedia_module_settings( $_GET['module'], $gmProcessor->term_id );
				}
				else {
					gmedia_manage_modules();
				}
				break;
			case 'GrandMedia_WordpressLibrary':
				include_once ( dirname( __FILE__ ) . '/wpmedia.php' );
				grandWPMedia();
				break;
			case 'GrandMedia':
			default:
				include_once ( dirname( __FILE__ ) . '/gmedia.php' );
				gmediaLib();
				break;
		}
	}

	function load_scripts( $hook ) {
		global $gmCore, $gmProcessor;

		// no need to go on if it's not a plugin page
		if ( 'admin.php' != $hook && strpos( $gmCore->_get( 'page' ), 'GrandMedia' ) === false )
			return;

		wp_enqueue_style( 'gmedia-bootstrap' );
		//wp_enqueue_style( 'gmedia-bootstrap-theme' );
		wp_enqueue_script( 'gmedia-bootstrap' );

		wp_enqueue_script( 'outside-events' );

		if ( isset( $_GET['page'] ) ) {
			switch ( $_GET['page'] ) {
				case "GrandMedia" :
					if($gmProcessor->mode){
						wp_enqueue_style('selectize', $gmCore->gmedia_url . '/assets/selectize/selectize.bootstrap3.css', array('gmedia-bootstrap'), '0.8.5', 'screen');
						wp_enqueue_script('selectize', $gmCore->gmedia_url . '/assets/selectize/selectize.min.js', array('jquery'), '0.8.5');

						wp_enqueue_script( 'alphanum', $gmCore->gmedia_url . '/assets/jq-plugins/jquery.alphanum.js', array( 'jquery' ), '1.0.16' );

						wp_enqueue_script( 'moment', $gmCore->gmedia_url . '/assets/bootstrap-datetimepicker/moment.min.js', array( 'jquery' ), '2.5.1' );
						wp_enqueue_style( 'datetimepicker', $gmCore->gmedia_url . '/assets/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css', array('gmedia-bootstrap'), '2.1.32' );
						wp_enqueue_script( 'datetimepicker', $gmCore->gmedia_url . '/assets/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js', array( 'jquery', 'moment', 'gmedia-bootstrap' ), '2.1.32' );
					}
					//wp_enqueue_style( 'mediaelement' );
					//wp_enqueue_script( 'mediaelement' );
					break;
				case "GrandMedia_WordpressLibrary" :
					wp_enqueue_style( 'mediaelement' );
					wp_enqueue_script( 'mediaelement' );
					break;
				case "GrandMedia_Tags_and_Albums" :

					break;
				case "GrandMedia_AddMedia" :
					wp_enqueue_style( 'selectize', $gmCore->gmedia_url . '/assets/selectize/selectize.bootstrap3.css', array('gmedia-bootstrap'), '0.8.5', 'screen' );
					wp_enqueue_script('selectize', $gmCore->gmedia_url . '/assets/selectize/selectize.min.js', array('jquery'), '0.8.5');

					$tab = $gmCore->_get('tab', 'upload');
					if($tab == 'upload') {
						wp_enqueue_style('jquery-ui-smoothness', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/smoothness/jquery-ui.min.css', array(), '1.10.2', 'screen');
						wp_enqueue_script('jquery-ui-full', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js', array(), '1.10.2');

						wp_enqueue_script('gmedia-plupload', $gmCore->gmedia_url . '/assets/plupload/plupload.full.min.js', array('jquery', 'jquery-ui-full'), '2.1.1');

						wp_enqueue_style( 'jquery.ui.plupload', $gmCore->gmedia_url . '/assets/plupload/jquery.ui.plupload/css/jquery.ui.plupload.css', array( 'jquery-ui-smoothness' ), '2.1.1', 'screen' );
						wp_enqueue_script( 'jquery.ui.plupload', $gmCore->gmedia_url . '/assets/plupload/jquery.ui.plupload/jquery.ui.plupload.min.js', array( 'gmedia-plupload', 'jquery-ui-full' ), '2.1.1' );

					}
					break;
				case "GrandMedia_Settings" :
					// enqueue jscolor
				case "GrandMedia_Modules" :
					if ( isset( $_GET['module'] ) ) {
						wp_enqueue_script( 'jscolor' );
					}
				wp_enqueue_style( 'jquery-ui-tabs', $gmCore->gmedia_url . '/admin/css/jquery-ui-tabs.css', array(), '0.9.0', 'screen' );
					wp_enqueue_script( 'jquery-ui-tabs' );
					break;
			}
		}

		wp_enqueue_style( 'grand-media' );
		wp_enqueue_script( 'grand-media' );

	}

	function screen_help() {
		$screen = get_current_screen();
		switch ( $screen->id ) {
			case "toplevel_page_GrandMedia" :
				//$link = '<a href="http://codeasily.com/community/forum/gmedia-gallery-wordpress-plugin/" target="_blank">'.__( 'Support Forum', 'gmLang' ).'</a>';
				//$contextual_help = '<p>' . sprintf( __( "Support Forum: %s", 'gmLang' ), $link ) . '</p>';
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
				$screen->add_help_tab( array(
						'id'	=> 'my_help_tab',
						'title'	=> __('My Help Tab'),
						'content'	=> '<p>' . __( 'Descriptive content that will show in My Help Tab-body goes here.' ) . '</p>',
				) );
				break;
			case "admin_page_GrandMedia_Settings" :
				break;
		}
	}

	function screen_settings( $current, $screen ) {
		global $user_ID, $gmGallery, $gmProcessor, $gmCore;
		if ( in_array( $screen->id, $this->pages ) ) {

			$gm_screen_options = $gmProcessor->user_options();

			$title = '<h5><strong>' . __( 'Settings', 'gmLang' ) . '</strong></h5>';
			$wp_screen_options = '<input type="hidden" name="wp_screen_options[option]" value="gm_screen_options" /><input type="hidden" name="wp_screen_options[value]" value="'.$screen->id.'" />';
			$button = get_submit_button( __( 'Apply', 'gmLang' ), 'button', 'screen-options-apply', false );

			$settings = false;

			$screen_id = explode('page_', $screen->id, 2);

			switch ( $screen_id[1] ) {
				case "GrandMedia" :
					$settings = '
					<div class="form-inline pull-left">
						<div class="form-group">
							<input type="number" max="999" min="0" step="5" size="3" name="gm_screen_options[per_page_gmedia]" class="form-control input-sm" style="width: auto;" value="'.$gm_screen_options['per_page_gmedia'].'" /> <span>'.__( 'items per page', 'gmLang' ).'</span>
						</div>
						<div class="form-group">
							<select name="gm_screen_options[orderby_gmedia]" class="form-control input-sm">
								<option'.selected($gm_screen_options['orderby_gmedia'], 'ID', false).' value="ID">'.__('ID','gmLang').'</option>
								<option'.selected($gm_screen_options['orderby_gmedia'], 'title', false).' value="title">'.__('Title','gmLang').'</option>
								<option'.selected($gm_screen_options['orderby_gmedia'], 'date', false).' value="date">'.__('Date','gmLang').'</option>
								<option'.selected($gm_screen_options['orderby_gmedia'], 'modified', false).' value="modified">'.__('Last Modified','gmLang').'</option>
								<option'.selected($gm_screen_options['orderby_gmedia'], 'mime_type', false).' value="mime_type">'.__('MIME Type','gmLang').'</option>
								<option'.selected($gm_screen_options['orderby_gmedia'], 'author', false).' value="author">'.__('Author','gmLang').'</option>
							</select> <span>'.__( 'order items', 'gmLang' ).'</span>
						</div>
						<div class="form-group">
							<select name="gm_screen_options[sortorder_gmedia]" class="form-control input-sm">
								<option'.selected($gm_screen_options['sortorder_gmedia'], 'DESC', false).' value="DESC">'.__('DESC','gmLang').'</option>
								<option'.selected($gm_screen_options['sortorder_gmedia'], 'ASC', false).' value="ASC">'.__('ASC','gmLang').'</option>
							</select> <span>'.__( 'sort order', 'gmLang' ).'</span>
						</div>
					</div>
					';
					break;
				case "GrandMedia_WordpressLibrary" :
					$settings = '
					<div class="form-inline pull-left">
						<div class="form-group">
							<input type="number" max="999" min="0" step="5" size="3" name="gm_screen_options[per_page_wpmedia]" class="form-control input-sm" style="width: auto;" value="' . $gm_screen_options['per_page_wpmedia'] . '" /> <span>' . __('items per page', 'gmLang') . '</span>
						</div>
						<div class="form-group">
							<select name="gm_screen_options[orderby_wpmedia]" class="form-control input-sm">
								<option' . selected($gm_screen_options['orderby_wpmedia'], 'ID', false) . ' value="ID">' . __('ID', 'gmLang') . '</option>
								<option' . selected($gm_screen_options['orderby_wpmedia'], 'title', false) . ' value="title">' . __('Title', 'gmLang') . '</option>
								<option' . selected($gm_screen_options['orderby_wpmedia'], 'date', false) . ' value="date">' . __('Date', 'gmLang') . '</option>
								<option' . selected($gm_screen_options['orderby_wpmedia'], 'modified', false) . ' value="modified">' . __('Last Modified', 'gmLang') . '</option>
								<option' . selected($gm_screen_options['orderby_wpmedia'], 'mime_type', false) . ' value="mime_type">' . __('MIME Type', 'gmLang') . '</option>
								<option' . selected($gm_screen_options['orderby_wpmedia'], 'author', false) . ' value="author">' . __('Author', 'gmLang') . '</option>
							</select> <span>' . __('order items', 'gmLang') . '</span>
						</div>
						<div class="form-group">
							<select name="gm_screen_options[sortorder_wpmedia]" class="form-control input-sm">
								<option' . selected($gm_screen_options['sortorder_wpmedia'], 'DESC', false) . ' value="DESC">' . __('DESC', 'gmLang') . '</option>
								<option' . selected($gm_screen_options['sortorder_wpmedia'], 'ASC', false) . ' value="ASC">' . __('ASC', 'gmLang') . '</option>
							</select> <span>' . __('sort order', 'gmLang') . '</span>
						</div>
					</div>
					';
					break;
				case "GrandMedia_AddMedia" :
					$tab = $gmCore->_get('tab', 'upload');
					if('upload' == $tab){
						$html4_hide = ('html4' == $gm_screen_options['uploader_runtime'])? ' hide' : '';
						$settings = '
						<div class="form-inline pull-left">
							<div id="uploader_runtime" class="form-group"><span>' . __('Uploader runtime:', 'gmLang') . ' </span>
								<select name="gm_screen_options[uploader_runtime]" class="form-control input-sm">
									<option' . selected($gm_screen_options['uploader_runtime'], 'auto', false) . ' value="auto">' . __('Auto', 'gmLang') . '</option>
									<option' . selected($gm_screen_options['uploader_runtime'], 'html5', false) . ' value="html5">' . __('HTML5 Uploader', 'gmLang') . '</option>
									<option' . selected($gm_screen_options['uploader_runtime'], 'flash', false) . ' value="flash">' . __('Flash Uploader', 'gmLang') . '</option>
									<option' . selected($gm_screen_options['uploader_runtime'], 'html4', false) . ' value="html4">' . __('HTML4 Uploader', 'gmLang') . '</option>
								</select>
							</div>
							<div id="uploader_chunking" class="form-group' . $html4_hide . '"><span>' . __('Chunking:', 'gmLang') . ' </span>
								<select name="gm_screen_options[uploader_chunking]" class="form-control input-sm">
									<option' . selected($gm_screen_options['uploader_chunking'], 'true', false) . ' value="true">' . __('TRUE', 'gmLang') . '</option>
									<option' . selected($gm_screen_options['uploader_chunking'], 'false', false) . ' value="false">' . __('FALSE', 'gmLang') . '</option>
								</select>
							</div>
							<div id="uploader_urlstream_upload" class="form-group' . $html4_hide . '"><span>' . __('URL streem upload:', 'gmLang') . ' </span>
								<select name="gm_screen_options[uploader_urlstream_upload]" class="form-control input-sm">
									<option' . selected($gm_screen_options['uploader_urlstream_upload'], 'true', false) . ' value="true">' . __('TRUE', 'gmLang') . '</option>
									<option' . selected($gm_screen_options['uploader_urlstream_upload'], 'false', false) . ' value="false">' . __('FALSE', 'gmLang') . '</option>
								</select>
							</div>
						</div>
						';
					}
					break;
			}

			if($settings){
				$current = $title.$settings.$wp_screen_options.$button;
			}

		}
		return $current;
	}

	function screen_settings_save( $status, $option, $value) {
		global $user_ID;
		if ( 'gm_screen_options' == $option ) {
			/*
			global $gmGallery;
			foreach ( $_POST['gm_screen_options'] as $key => $val ) {
				$gmGallery->options['gm_screen_options'][$key] = $val;
			}
			update_option( 'gmediaOptions', $gmGallery->options );
			*/
			$gm_screen_options = get_user_meta($user_ID, 'gm_screen_options', true);
			if(!is_array($gm_screen_options))
				$gm_screen_options = array();
			$value = array_merge($gm_screen_options, $_POST['gm_screen_options']);
			return $value;
		}

		return $status;
	}

}
