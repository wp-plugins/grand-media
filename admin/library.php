<?php if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class grandLibrary {

	var $mode;
	var $page;
	var $msg;
	var $term_id;

	// initiate the manage page
	function grandLibrary() {
		global $grandCore;
		// GET variables
		$this->mode = $grandCore->_get( 'mode', 'main' );
		$this->page = $grandCore->_get( 'page', 'GrandMedia' );
		$this->msg  = $grandCore->message( $grandCore->_post( 'gmUpdateMessage' ), $grandCore->_post( 'gmUpdateStatus', false ) );

		$this->processor();
	}

	// Do diff process before lib shell
	function processor() {
		global $grandCore, $gMDb, $grandAdmin;

		// check for correct capability
		if ( ! current_user_can( 'edit_posts' ) )
			die( '-1' );

		$gmOptions = get_option( 'gmediaOptions' );
		switch ( $this->page ) {
			case 'GrandMedia_Settings':
				break;
			case 'GrandMedia_Tags_and_Categories':
				if ( isset( $_POST['addterms'] ) ) {
					check_admin_referer( 'grandMedia' );
					$term_ids = array();
					$args     = array( 'description' => $grandCore->_post( 'gm_term_description', '' ), 'global' => intval( $grandCore->_post( 'gm_term_global', 0 ) ) );
					foreach ( $_POST['terms'] as $taxonomy => $terms ) {
						$taxonomy = trim( $taxonomy );
						if ( isset( $gmOptions['taxonomies'][$taxonomy]['hierarchical'] ) && $gmOptions['taxonomies'][$taxonomy]['hierarchical'] ) {
							$terms = array( $terms );
						}
						else {
							$terms = explode( ',', $terms );
						}
						$terms = array_filter( array_map( 'trim', $terms ) );
						if ( ! empty( $taxonomy ) && count( $terms ) ) {
							foreach ( (array) $terms as $term ) {
								if ( ! strlen( $term ) )
									continue;

								if ( ! $term_info = $gMDb->term_exists( $term, $taxonomy ) ) {
									// Skip if a non-existent term ID is passed.
									if ( is_int( $term ) )
										continue;
									$term_info = $gMDb->insert_term( $term, $taxonomy, $args );
								}
								if ( ! is_wp_error( $term_info ) ) {
									$term_ids[] = $term_info['term_id'];
								}
							}
							$this->msg .= $grandCore->message( sprintf( __( "%s terms successfuly added", 'gmLang' ), count( $term_ids ) ), 'info' );
						}
					}
				}
				if ( isset( $_POST['updateTerm'] ) ) {
					check_admin_referer( 'grandMedia' );
					$term_id = $grandCore->_post( 'gmID', '' );
					$args    = array( 'description' => $grandCore->_post( 'gm_term_description', '' ), 'global' => intval( $grandCore->_post( 'gm_term_global', 0 ) ) );
					foreach ( $_POST['terms'] as $taxonomy => $term ) {
						$taxonomy     = trim( $taxonomy );
						$args['name'] = trim( $term );
						if ( ! empty( $taxonomy ) && ! empty( $term_id ) ) {
							$term_info = $gMDb->update_term( $term_id, $taxonomy, $args );
							if ( ! is_wp_error( $term_info ) ) {
								$this->msg .= $grandCore->message( sprintf( __( "Term #%s updated successfuly", 'gmLang' ), $term_info['term_id'] ), 'info' );
							}
							else {
								$this->msg .= $grandCore->message( __( "Error. Can't update term", 'gmLang' ), 'error' );
							}

						}
					}
				}
				break;
			case 'GrandMedia_AddMedia':
				break;
			case 'GrandMedia_Modules':
				if ( isset( $_POST['gmedia_module_create'] ) ) {
					$term = trim( $grandCore->_post( 'name', '' ) );
					if ( ! empty( $term ) ) {
						check_admin_referer( 'grandMedia' );
						$args     = array( 'name' => $term, 'description' => $grandCore->_post( 'description', '' ) );
						$taxonomy = 'gmedia_module';
						if ( ! $term_info = $gMDb->term_exists( $term, $taxonomy ) ) {
							$term_info = $gMDb->insert_term( $term, $taxonomy, $args );
							if ( ! is_wp_error( $term_info ) ) {
								$default_settings                = $grandCore->gm_get_module_settings( $_POST['module_name'] );
								$default_settings['module_name'] = $_POST['module_name'];
								$default_settings['last_edited'] = gmdate( 'Y-m-d H:i:s' );
								if ( isset( $_POST['checkbox'] ) ) {
									foreach ( $_POST['checkbox'] as $key => $value ) {
										if ( ! isset( $_POST[$key] ) ) {
											$_POST[$key] = $_POST['checkbox'][$key];
										}
									}
									unset( $_POST['checkbox'] );
								}
								foreach ( $default_settings as $key => $value ) {
									if ( array_key_exists( $key, $args ) )
										continue;

									if ( ! isset( $_POST[$key] ) ) {
										$_POST[$key] = $value;
									}
									$gMDb->update_metadata( 'gmedia_term', $term_info['term_id'], $key, $_POST[$key] );
								}
								$this->msg .= $grandCore->message( sprintf( __( "%s gallery successfuly added", 'gmLang' ), $term ), 'info' );
								$this->term_id = $term_info['term_id'];
							}
							else {
								$this->msg .= $grandCore->message( sprintf( __( "Can't create %s gallery", 'gmLang' ), $term ), 'error' );
							}
						}
						else {
							$this->msg .= $grandCore->message( sprintf( __( "Gallery %s already exists", 'gmLang' ), $term ), 'warning' );
						}
					}
					else {
						$this->msg .= $grandCore->message( __( "Gallery name is empty", 'gmLang' ), 'error' );
					}
				}
				if ( isset( $_POST['gmedia_module_update'] ) ) {
					$term = trim( $_POST['name'] );
					if ( ! empty( $term ) ) {
						check_admin_referer( 'grandMedia' );
						$taxonomy      = 'gmedia_module';
						$this->term_id = $term_id = intval( $_POST['term_id'] );
						$args          = array( 'name' => $term, 'description' => $grandCore->_post( 'description', '' ) );
						if ( $term_id && $term_info = $gMDb->term_exists( $term_id, $taxonomy ) ) {
							$term_info = $gMDb->update_term( $term_id, $taxonomy, $args );
							if ( ! is_wp_error( $term_info ) ) {
								$default_settings                = $grandCore->gm_get_module_settings( $_POST['module_name'] );
								$default_settings['module_name'] = $_POST['module_name'];
								$default_settings['last_edited'] = gmdate( 'Y-m-d H:i:s' );
								if ( isset( $_POST['checkbox'] ) ) {
									foreach ( $_POST['checkbox'] as $key => $value ) {
										if ( ! isset( $_POST[$key] ) ) {
											$_POST[$key] = $_POST['checkbox'][$key];
										}
									}
									unset( $_POST['checkbox'] );
								}
								foreach ( $default_settings as $key => $value ) {
									if ( array_key_exists( $key, $args ) )
										continue;

									if ( ! isset( $_POST[$key] ) ) {
										$_POST[$key] = $value;
									}
									$gMDb->update_metadata( 'gmedia_term', $term_info['term_id'], $key, $_POST[$key] );
								}
								$this->msg .= $grandCore->message( sprintf( __( "%s gallery successfuly updated", 'gmLang' ), $term ), 'info' );
							}
							else {
								$this->msg .= $grandCore->message( sprintf( __( "Can't update %s gallery", 'gmLang' ), $term ), 'error' );
							}
						}
						else {
							$this->msg .= $grandCore->message( sprintf( __( "Update Error. Can't find gallery %s in database", 'gmLang' ), $term ), 'error' );
						}
					}
					else {
						$this->msg .= $grandCore->message( __( "Gallery name is empty", 'gmLang' ), 'error' );
					}
				}
				if ( isset( $_GET['settings_default'] ) ) {
					$this->msg .= $grandCore->message( __( "Default setings loaded", 'gmLang' ), 'info' );
				}
				break;
			case 'GrandMedia_WordpressLibrary':
				if ( isset( $_POST['wpmedia-update'] ) ) {
					$post['ID']           = $_POST['gmID'];
					$post['post_title']   = $_POST['gmTitle'];
					$post['post_content'] = $_POST['gmDescription'];
					if ( wp_update_post( $post ) ) {
						$this->msg .= $grandCore->message( sprintf( __( 'Media #%s updated successfully', 'gmLang' ), $post['ID'] ), 'info' );
					}
					else {
						$this->msg .= $grandCore->message( sprintf( __( "Can't update media #%s", 'gmLang' ), $post['ID'] ), 'error' );
					}
				}
				break;
			case 'GrandMedia':
			default:
				break;
		}
		$this->shell();
	}

	// Display shell of plugin
	function shell() {
		$sideLinks = $this->sideLinks();
		?>
		<div id="grandMedia" class="grandmedia">
			<div class="grandHeader">
				<div class="grandLogo">GrandMedia</div>
				<h2><?php echo $sideLinks['grandTitle']; ?></h2>
			</div>
			<div id="gm-message"><?php echo $this->msg; ?></div>
			<?php echo $sideLinks['sideLinks']; ?>
			<div class="grandLibrary">
				<?php $this->controller();

				$params = $_GET;
				//unset($params["pager"],$params["s"]);
				if ( isset( $params["filter"] ) && $params["filter"] == 'selected' ) unset( $params["filter"] );
				$new_query_string = http_build_query( $params );
				?>
				<form action="<?php echo admin_url( 'admin.php?' . $new_query_string ); ?>" method="post" style="display: none;" id="gmUpdateContent">
					<input id="gmUpdateMessage" type="hidden" name="gmUpdateMessage" value="" />
					<input id="gmUpdateStatus" type="hidden" name="gmUpdateStatus" value="" />
				</form>
			</div>
			<div class="tooltip-file-preview"></div>
			<div class="tooltip-mediaelement"></div>
		</div>
	<?php
	}

	function sideLinks() {
		global $submenu, $grandCore;
		$content['sideLinks'] = '
		<div class="sideLinks">
			<div class="gm-bufer"><a class="button-primary" href="' . admin_url( 'admin.php?page=GrandMedia_AddMedia' ) . '"' . $grandCore->qTip( __( "Click to upload media files", "gmLang" ), true ) . '>' . __( 'Add Files...', 'gmLang' ) . '</a></div>
			<ul>';
		foreach ( $submenu['gmedia-plugin'] as $menuKey => $menuItem ) {
			if ( $submenu['gmedia-plugin'][$menuKey][2] == $this->page ) {
				$iscur                 = ' class="current"';
				$content['grandTitle'] = $submenu['gmedia-plugin'][$menuKey][3];
			}
			else {
				$iscur = '';
			}
			if($submenu['gmedia-plugin'][$menuKey][2] == 'GrandMedia_AddMedia')
				continue;

			$content['sideLinks'] .= '
				<li' . $iscur . '><a href="' . admin_url( 'admin.php?page=' . $submenu['gmedia-plugin'][$menuKey][2] ) . '">' . $submenu['gmedia-plugin'][$menuKey][0] . '</a></li>';
		}
		$content['sideLinks'] .= '
			</ul>
		</div>';
		return $content;
	}

	function controller() {
		switch ( $this->page ) {
			case 'GrandMedia_Settings':
				include_once ( dirname( __FILE__ ) . '/settings.php' );
				gmSettings();
				break;
			case 'GrandMedia_Tags_and_Categories':
				include_once ( dirname( __FILE__ ) . '/labels.php' );
				gmTagsCategories();
				break;
			case 'GrandMedia_AddMedia':
				include_once ( dirname( __FILE__ ) . '/addmedia.php' );
				grandMedia_AddMedia();
				break;
			case 'GrandMedia_Modules':
				include_once ( dirname( __FILE__ ) . '/modules.php' );
				if ( isset( $_GET['module'] ) ) {
					gmedia_module_settings( $_GET['module'], $this->term_id );
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
				grandMedia();
				break;
		}
	}


}
