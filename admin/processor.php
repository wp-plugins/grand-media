<?php if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class GmediaProcessor {

	var $mode;
	var $page;
	var $msg;
	var $error;
	var $term_id;
	var $gm_selected = array();

	// initiate the manage page
	function __construct() {
		global $pagenow, $gmCore;
		// GET variables
		$this->mode = $gmCore->_get( 'mode' );
		$this->page = $gmCore->_get( 'page', 'GrandMedia' );

		if ( 'media.php' === $pagenow ) {
			add_filter( 'wp_redirect', array( &$this, 'redirect' ), 10, 2 );
		}

		add_action( 'set_current_user', array( &$this, 'gm_selected' ) );
		add_action( 'init', array( &$this, 'processor' ) );

	}

	function  gm_selected() {
		global $user_ID;
		$ckey = "gmedia_u{$user_ID}_gm-selected";
		if ( isset( $_POST['selected_items'] ) ) {
			$this->gm_selected = array_filter( explode( ',', $_POST['selected_items'] ), 'is_numeric' );
		}	elseif ( isset( $_COOKIE[$ckey] ) ) {
			$this->gm_selected = array_filter( explode( ',', $_COOKIE[$ckey] ), 'is_numeric' );
		}
	}

	function  user_options() {
		global $user_ID, $gmGallery;

		$gm_screen_options = get_user_meta($user_ID, 'gm_screen_options', true);
		if(!is_array($gm_screen_options))
			$gm_screen_options = array();
		$gm_screen_options = array_merge($gmGallery->options['gm_screen_options'], $gm_screen_options);

		return $gm_screen_options;
	}

	// Do diff process before lib shell
	function processor() {
		global $gmCore, $gmDB, $gmGallery;

		// check for correct capability
		//if ( ! current_user_can( 'edit_posts' ) )
		//	die( '-1' );

		$gmOptions = get_option( 'gmediaOptions' );
		switch ( $this->page ) {
			case 'GrandMedia':
				if(isset($_POST['filter_categories'])){
					if($term = $gmCore->_post('cat')){
						$location = add_query_arg( array('page' => $this->page, 'mode' => $this->mode, 'category__in' => implode(',', $term)), admin_url( 'admin.php' ) );
						wp_redirect($location);
					}
				}
				if(isset($_POST['filter_albums'])){
					if($term = $gmCore->_post('alb')){
						$location = add_query_arg( array('page' => $this->page, 'mode' => $this->mode, 'album__in' => implode(',', $term)), admin_url( 'admin.php' ) );
						wp_redirect($location);
					}
				}
				if(isset($_POST['filter_tags'])){
					if($term = $gmCore->_post('tag_id')){
						$location = add_query_arg( array('page' => $this->page, 'mode' => $this->mode, 'tag__in' => implode(',', $term)), admin_url( 'admin.php' ) );
						wp_redirect($location);
					}
				}
				if(!empty($this->gm_selected)){
					if(isset($_POST['assign_category'])){
						if($term = $gmCore->_post('cat')){
							$count = count( $this->gm_selected );
							foreach ( $this->gm_selected as $item ) {
								$result = $gmDB->set_gmedia_terms( $item, $term, 'gmedia_category', $append = 0 );
								if ( is_wp_error( $result ) ) {
									$this->error[] = $result;
									$count --;
								} elseif(!$result){
									$count--;
								}
							}
							if(isset($gmGallery->options['taxonomies']['gmedia_category'][$term])){
								$cat_name = $gmGallery->options['taxonomies']['gmedia_category'][$term];
								$this->msg[] = sprintf( __( "Category `%s` assigned to %d images.", 'gmLang' ), $cat_name, $count );
							} else{
								$this->error[] = sprintf(__("Category `%s` can't be assigned.", 'gmLang'), $term);;
							}
						}
					}
					if(isset($_POST['assign_album'])){
						if($term = $gmCore->_post('alb')){
							$count = count( $this->gm_selected );
							foreach ( $this->gm_selected as $item ) {
								$result = $gmDB->set_gmedia_terms( $item, $term, 'gmedia_album', $append = 0 );
								if(is_wp_error($result)){
									$this->error[] = $result;
									$count--;
								} elseif(!$result){
									$count--;
								}
							}
							$alb_name = $gmDB->get_alb_name($term);
							$this->msg[] = sprintf( __( "Album `%s` assigned to %d items.", 'gmLang' ), $alb_name, $count );
						}
					}
					if(isset($_POST['add_tags'])){
						if($term = $gmCore->_post('tag_id')){
							$term = array_map( 'intval', $term );
							$count = count( $this->gm_selected );
							foreach ( $this->gm_selected as $item ) {
								$result = $gmDB->set_gmedia_terms( $item, $term, 'gmedia_tag', $append = 1 );
								if(is_wp_error($result)){
									$this->error[] = $result;
									$count--;
								} elseif(!$result){
									$count--;
								}
							}
							$this->msg[] = sprintf( __( "%d tags added to %d items.", 'gmLang' ), count($term), $count );
						}
					}
					if(isset($_POST['delete_tags'])){
						if($term = $gmCore->_post('tag_id')){
							$term = array_map( 'intval', $term );
							$count = count( $this->gm_selected );
							foreach ( $this->gm_selected as $item ) {
								$result = $gmDB->set_gmedia_terms( $item, $term, 'gmedia_tag', $append = -1 );
								if(is_wp_error($result)){
									$this->error[] = $result;
									$count--;
								} elseif(!$result){
									$count--;
								}
							}
							$this->msg[] = sprintf( __( "%d tags deleted from %d items.", 'gmLang' ), count($term), $count );
						}
					}
					if('selected' == $gmCore->_get('delete')){
						global $user_ID;
						if ( ! current_user_can( 'delete_posts' ) )
							wp_die( __( 'You are not allowed to delete this post.' ) );
						$count = count( $this->gm_selected );
						foreach ( $this->gm_selected as $item ) {
							if ( ! $gmDB->delete_gmedia( (int) $item ) ){
								$this->error[] = "#{$item}: ".__( 'Error in deleting...', 'gmLang' );
								$count --;
							}
						}
						if($count){
							$this->msg[] = sprintf( __( "%d items deleted successfuly.", 'gmLang' ), $count );
						}
						unset($_COOKIE["gmedia_u{$user_ID}_gm-selected"]);
						setcookie($_COOKIE["gmedia_u{$user_ID}_gm-selected"], '', time() - 3600);
						$this->gm_selected = array();
					}
				}
				break;
			case 'GrandMedia_Settings':
				if ( isset( $_POST['gmedia_settings_save'] ) ) {
					check_admin_referer( 'grandMedia' );
					$gmOptions = get_option( 'gmediaOptions' );
					if(isset($_POST['set']['gmedia_key2']) && empty($_POST['set']['gmedia_key2'])){
						$_POST['set']['gmedia_key'] = '';
					} else if(empty($_POST['set']['gmedia_key'])){
						$_POST['set']['gmedia_key2'] = '';
						$_POST['set']['product_name'] = '';
					}
					foreach ( $_POST['set'] as $key => $val ) {
						$gmOptions[$key] = $val;
					}
					update_option( 'gmediaOptions', $gmOptions );

					$this->msg .= __( "Settings saved", 'gmLang' );
				}
				if ( isset( $_GET['settings_default'] ) ) {
					$this->msg .= __( "Default setings loaded", 'gmLang' );
				}
				break;
			case 'GrandMedia_Tags_and_Albums':
				if ( isset( $_POST['addterms'] ) ) {
					check_admin_referer( 'grandMedia' );
					$term_ids = array();
					$args     = array( 'description' => $gmCore->_post( 'gm_term_description', '' ), 'global' => intval( $gmCore->_post( 'gm_term_global', 0 ) ) );
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

								if ( ! $term_info = $gmDB->term_exists( $term, $taxonomy ) ) {
									// Skip if a non-existent term ID is passed.
									if ( is_int( $term ) )
										continue;
									$term_info = $gmDB->insert_term( $term, $taxonomy, $args );
								}
								if ( ! is_wp_error( $term_info ) ) {
									$term_ids[] = $term_info['term_id'];
								}
							}
							$this->msg .= sprintf( __( "%s terms successfuly added", 'gmLang' ), count( $term_ids ) );
						}
					}
				}
				if ( isset( $_POST['updateTerm'] ) ) {
					check_admin_referer( 'grandMedia' );
					$term_id = $gmCore->_post( 'gmID', '' );
					$args    = array( 'description' => $gmCore->_post( 'gm_term_description', '' ), 'global' => intval( $gmCore->_post( 'gm_term_global', 0 ) ) );
					foreach ( $_POST['terms'] as $taxonomy => $term ) {
						$taxonomy     = trim( $taxonomy );
						$args['name'] = trim( $term );
						if ( ! empty( $taxonomy ) && ! empty( $term_id ) ) {
							$term_info = $gmDB->update_term( $term_id, $taxonomy, $args );
							if ( ! is_wp_error( $term_info ) ) {
								$this->msg .= sprintf( __( "Term #%s updated successfuly", 'gmLang' ), $term_info['term_id'] );
							}
							else {
								$this->msg .= __( "Error. Can't update term", 'gmLang' );
							}

						}
					}
				}
				break;
			case 'GrandMedia_AddMedia':
				break;
			case 'GrandMedia_Modules':
				if ( isset( $_POST['gmedia_module_create'] ) ) {
					$term = trim( $gmCore->_post( 'name', '' ) );
					if ( ! empty( $term ) ) {
						check_admin_referer( 'grandMedia' );
						$args     = array( 'name' => $term, 'description' => $gmCore->_post( 'description', '' ) );
						$taxonomy = 'gmedia_module';
						if ( ! $term_info = $gmDB->term_exists( $term, $taxonomy ) ) {
							$term_info = $gmDB->insert_term( $term, $taxonomy, $args );
							if ( ! is_wp_error( $term_info ) ) {
								$default_settings                = $gmCore->gm_get_module_settings( $_POST['module_name'] );
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
									$gmDB->update_metadata( 'gmedia_term', $term_info['term_id'], $key, $_POST[$key] );
								}
								$this->msg .= sprintf( __( "%s gallery successfuly added", 'gmLang' ), $term );
								$this->term_id = $term_info['term_id'];
							}
							else {
								$this->msg .= sprintf( __( "Can't create %s gallery", 'gmLang' ), $term );
							}
						}
						else {
							$this->msg .= sprintf( __( "Gallery %s already exists", 'gmLang' ), $term );
						}
					}
					else {
						$this->msg .= __( "Gallery name is empty", 'gmLang' );
					}
				}
				if ( isset( $_POST['gmedia_module_update'] ) ) {
					$term = trim( $_POST['name'] );
					if ( ! empty( $term ) ) {
						check_admin_referer( 'grandMedia' );
						$taxonomy      = 'gmedia_module';
						$this->term_id = $term_id = intval( $_POST['term_id'] );
						$args          = array( 'name' => $term, 'description' => $gmCore->_post( 'description', '' ) );
						if ( $term_id && $term_info = $gmDB->term_exists( $term_id, $taxonomy ) ) {
							$term_info = $gmDB->update_term( $term_id, $taxonomy, $args );
							if ( ! is_wp_error( $term_info ) ) {
								$default_settings                = $gmCore->gm_get_module_settings( $_POST['module_name'] );
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
									$gmDB->update_metadata( 'gmedia_term', $term_info['term_id'], $key, $_POST[$key] );
								}
								$this->msg .= sprintf( __( "%s gallery successfuly updated", 'gmLang' ), $term );
							}
							else {
								$this->msg .= sprintf( __( "Can't update %s gallery", 'gmLang' ), $term );
							}
						}
						else {
							$this->msg .= sprintf( __( "Update Error. Can't find gallery %s in database", 'gmLang' ), $term );
						}
					}
					else {
						$this->msg .= __( "Gallery name is empty", 'gmLang' );
					}
				}
				if ( isset( $_GET['settings_default'] ) ) {
					$this->msg .= __( "Default setings loaded", 'gmLang' );
				}
				break;
			case 'GrandMedia_WordpressLibrary':
				if ( isset( $_POST['wpmedia-update'] ) ) {
					$post['ID']           = $_POST['gmID'];
					$post['post_title']   = $_POST['gmTitle'];
					$post['post_content'] = $_POST['gmDescription'];
					if ( wp_update_post( $post ) ) {
						$this->msg .= sprintf( __( 'Media #%s updated successfully', 'gmLang' ), $post['ID'] );
					}
					else {
						$this->msg .= sprintf( __( "Can't update media #%s", 'gmLang' ), $post['ID'] );
					}
				}
				break;
			default:
				break;
		}
	}

	// redirect to original referer after update
	function redirect( $location, $status ) {
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

global $gmProcessor;
$gmProcessor = new GmediaProcessor();