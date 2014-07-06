<?php
ini_set( 'display_errors', '1' );
ini_set( 'error_reporting', E_ALL );

if ( ! defined( 'ABSPATH' ) ){
	die(0);
	//@require_once(dirname(__FILE__) . '/config.php');
}

global $wp;
$gmedia_app = isset($_GET['gmedia-app'])? $_GET['gmedia-app'] : (isset($wp->query_vars['gmedia-app'])? $wp->query_vars['gmedia-app'] : false);
if(!$gmedia_app){
	die();
}

global $gmCore;
$out = array();

if(isset($GLOBALS['HTTP_RAW_POST_DATA'])){

	$json = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);

	require_once(dirname(__FILE__).'/inc/json.auth.php');
	global $gmAuth;
	$gmAuth = new Gmedia_JSON_API_Auth_Controller();

	if(isset($json->cookie)){
		$user_id = $gmAuth->validate_auth_cookie($json->cookie);
		if($user_id){
			$user = wp_set_current_user($user_id);
			if(isset($json->add_term)){
				$out = gmedia_ios_app_processor('add_term', $json->add_term);
			} elseif(isset($json->delete_term)){
				$out = gmedia_ios_app_processor('delete_term', $json->delete_term);
			}
			elseif($json->library){
				$out = gmedia_ios_app_processor('library', $json->library);
			}


		} else{
			$out['error'] = array('code' => 'nocookie', 'message' => 'No cookie');
		}
	} elseif(isset($json->login)){
		$out = gmedia_ios_app_login($json);
		if(!isset($out['error'])){
			$user = wp_set_current_user($out['user']['id']);
			$data = gmedia_ios_app_library_data();
			$out = $out + $data;
		}
	} else{
		$out = gmedia_ios_app_library_data();
	}

} elseif( 'lostpassword' == $gmCore->_get('action') ){
	if(function_exists('wp_lostpassword_url')){
		$url = wp_lostpassword_url();
	} else{
		$url = add_query_arg('action', 'lostpassword', wp_login_url());
	}
	wp_redirect($url);
	exit;
}


/**
 * @param $json
 *
 * @return array
 */
function gmedia_ios_app_login($json){
	global $gmAuth;

	do{
		if(empty($json->login)){
			$out['error'] = array('code' => 'nologin', 'title' => 'No Login', 'message' => 'No Login');
			break;
		}
		if(!isset($json->password) || empty($json->password)){
			$out['error'] = array('code' => 'nopassword', 'title' => 'No Password', 'message' => 'No Password');
			break;
		}
		if(! ($uid = username_exists($json->login)) ){
			$out['error'] = array('code' => 'nouser', 'title' => 'No User', 'message' => 'No User');
			break;
		}

		$args = array(
			'username' => $json->login
			,'password' => $json->password
			,'nonce' => wp_create_nonce('auth_gmapp')
		);
		$out = $gmAuth->generate_auth_cookie($args);

	} while(0);

	return $out;
}

/**
 * @param array $data
 *
 * @return array
 */
function gmedia_ios_app_library_data($data = array('site','filter','gmedia_category','gmedia_album','gmedia_tag')){
	global $user_ID, $gmCore, $gmDB, $gmGallery;

	$out = array();

	if ( get_option('permalink_structure') ) {
		$ep = $gmGallery->options['endpoint'];
		$share_link = home_url($ep.'/$2/$1');
	} else{
		$share_link = home_url('index.php?gmedia=$1&type=$2');
	}

	if(in_array('site', $data)){
		$out['site'] = array(
			'title' => get_bloginfo('name')
			,'description' => get_bloginfo('description')
		);
	}
	if(in_array('filter', $data)){
		$out['filter'] = $gmDB->count_gmedia();
	}
	if(in_array('gmedia_category', $data)){
		/*
		if($user_ID){
			$cap = (is_super_admin($user_ID) || user_can($user_ID, 'gmedia_category_delete'))? 4 : (user_can($user_ID, 'gmedia_category_edit')? 2 : 0);
		} else{
			$cap = 0;
		}
		*/
		$gmediaTerms = $gmDB->get_terms('gmedia_category', array('fields' => 'name=>all'));
		$terms = $gmGallery->options['taxonomies']['gmedia_category'];
		$out['categories'] = array(
			'list' => $terms,
			'cap' => 0,
			'data' => array()
		);
		if(!empty($gmediaTerms)){
			foreach($gmediaTerms as $name => $term){
				unset(
				$gmediaTerms[$name]->description,
				$gmediaTerms[$name]->global,
				$gmediaTerms[$name]->status
				);
				$gmediaTerms[$name]->title = $terms[$name];
				$gmediaTerms[$name]->sharelink = str_replace(array('$1','$2'), array($term->term_id, 'category'), $share_link);
				$gmediaTerms[$name]->cap = 0;
			}
			$out['categories']['data'] = array_values($gmediaTerms);
		}
	}
	if(in_array('gmedia_album', $data)){
		if($user_ID){
			$cap = (is_super_admin($user_ID) || user_can($user_ID, 'gmedia_album_delete'))? 4 : (user_can($user_ID, 'gmedia_album_edit')? 2 : 0);
		} else{
			$cap = 0;
		}
		$gmediaTerms = $gmDB->get_terms('gmedia_album');
		foreach($gmediaTerms as $i => $term){
			unset(
			$gmediaTerms[$i]->global
			);
			if($term->count){
				$args = array('no_found_rows' => true, 'per_page' => 1, 'album__in' => array($term->term_id));
				$termItems = $gmDB->get_gmedias($args);
				$gmediaTerms[$i]->thumbnail = $gmCore->gm_get_media_image($termItems[0], 'thumb', false);
			}
			$term_meta = $gmDB->get_metadata('gmedia_term', $term->term_id);
			$term_meta = array_map('reset', $term_meta);
			$term_meta = array_merge( array('orderby' => 'ID', 'order' => 'DESC'), $term_meta);
			$gmediaTerms[$i]->meta = $term_meta;
			$gmediaTerms[$i]->sharelink = str_replace(array('$1','$2'), array($term->term_id, 'album'), $share_link);
			$gmediaTerms[$i]->cap = (4 == $cap)? 4 : 0;
		}
		$out['albums'] = array(
			'cap' => $cap,
			'data' => $gmediaTerms
		);
	}
	if(in_array('gmedia_tag', $data)){
		if($user_ID){
			$cap = (is_super_admin($user_ID) || user_can($user_ID, 'gmedia_tag_delete'))? 4 : (user_can($user_ID, 'gmedia_tag_edit')? 2 : 0);
		} else{
			$cap = 0;
		}
		$gmediaTerms = $gmDB->get_terms('gmedia_tag');
		foreach($gmediaTerms as $i => $term){
			unset(
			$gmediaTerms[$i]->description,
			$gmediaTerms[$i]->global,
			$gmediaTerms[$i]->status
			);
			$gmediaTerms[$i]->sharelink = str_replace(array('$1','$2'), array($term->term_id, 'tag'), $share_link);
			$gmediaTerms[$i]->cap = (4 == $cap)? 4 : 0;
		}
		$out['tags'] = array(
			'cap' => $cap,
			'data' => $gmediaTerms
		);
	}

	return $out;
}

/**
 * @param $action
 * @param $data
 *
 * @return array
 */
function gmedia_ios_app_processor($action, $data){
	global $gmCore, $gmDB, $gmGallery;

	$out = array();
	if ( !current_user_can('edit_posts') ){
		$out['error'] = array('code' => 'nocapability', 'title' => "You can't do this", 'message' => 'You have no permission to do this operation');
		return $out;
	}

	$error = array();
	$alert = array();
	$data = (array) $data;
	switch($action){
		case 'library':
			if ( get_option('permalink_structure') ) {
				$ep = $gmGallery->options['endpoint'];
				$share_link = home_url($ep.'/single/');
			} else{
				$share_link = home_url('index.php?type=single&gmedia=');
			}
			$gmedias = $gmDB->get_gmedias($data);
			foreach($gmedias as $i => $item){
				$meta = $gmDB->get_metadata('gmedia', $item->ID);
				$_metadata = unserialize($meta['_metadata'][0]);
				$type = explode('/', $item->mime_type);
				$item_url = $gmCore->upload['url'] . '/' . $gmGallery->options['folder'][$type[0]] . '/' . $item->gmuid;
				$gmedias[$i]->url = $item_url;
				$terms = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_tag');
				$tags = array();
				if($terms){
					$terms = array_values((array) $terms);
					foreach($terms as $term){
						$tags[] = array('term_id' => $term->term_id, 'name' => $term->name);
					}
				}
				$gmedias[$i]->tags = $tags;

				$terms = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_album');
				$albums = array();
				if($terms){
					$terms = array_values((array) $terms);
					foreach($terms as $term){
						$albums[] = array('term_id' => $term->term_id, 'name' => $term->name, 'status' => $term->status);
					}
				}
				$gmedias[$i]->albums = $albums;

				if('image' == $type[0]){
					$terms = $gmDB->get_the_gmedia_terms($item->ID, 'gmedia_category');
					$categories = array();
					if($terms){
						$terms = array_values((array) $terms);
						foreach($terms as $term){
							$categories[] = array('term_id' => $term->term_id, 'name' => $term->name);
						}
					}
					$gmedias[$i]->categories = $categories;

					$gmedias[$i]->meta = array(
						'thumb' => $_metadata['thumb']
						,'web' => $_metadata['web']
						,'original' => $_metadata['original']
					);
					$gmedias[$i]->meta['thumb']['link'] = "{$gmCore->upload['url']}/{$gmGallery->options['folder']['image_thumb']}/{$item->gmuid}";
					$gmedias[$i]->meta['web']['link'] = "{$gmCore->upload['url']}/{$gmGallery->options['folder']['image']}/{$item->gmuid}";
					$gmedias[$i]->meta['original']['link'] = "{$gmCore->upload['url']}/{$gmGallery->options['folder']['image_original']}/{$item->gmuid}";
					if(isset($meta['views'][0])){
						$gmedias[$i]->meta['views'] = $meta['views'][0];
					}
					if(isset($meta['likes'][0])){
						$gmedias[$i]->meta['likes'] = $meta['likes'][0];
					}
					if(isset($_metadata['image_info'])){
						$gmedias[$i]->meta['data'] = $_metadata['image_info'];
					}
				} else{
					$gmedias[$i]->meta = array(
						'thumb' => array(
							'link' => $gmCore->gm_get_media_image($item, 'thumb')
							,'width' => 300
							,'height' => 300
						)
					);
                    			if(!empty($_metadata)){
					    $gmedias[$i]->meta['data'] = $_metadata;
                    			}
				}
				if(isset($meta['rating'][0])){
					$gmedias[$i]->meta['rating'] = unserialize($meta['rating'][0]);
				}
			}
			$out = array(
				'properties' => array(
					'share_link_base' => $share_link
					,'total_pages' => $gmDB->pages
					,'current_page' => $gmDB->openPage
					,'items_count' => $gmDB->gmediaCount
				)
				,'data' => $gmedias
			);
			break;
		case 'delete_term':
			$taxonomy = $data['taxonomy'];
			if(!empty($data['items'])){
				if(!current_user_can('delete_posts')){
					$out['error'] = __('You are not allowed to delete this post.');
					return $out;
				}
				$count = count($data['items']);
				foreach($data['items'] as $item){
					$delete = $gmDB->delete_term($item, $taxonomy);
					if(is_wp_error($delete)){
						$error[] = $delete->get_error_message();
						$count--;
					}
				}
				if($count){
					$alert[] = sprintf(__('%d items deleted successfuly', 'gmLang'), $count);
				}
			}
			$out = gmedia_ios_app_library_data(array('filter',$taxonomy));
			break;
		case 'add_term':
			$taxonomy = $data['taxonomy'];
			$edit_term = isset($data['term_id'])? (int) $data['term_id'] : 0;
			$term = $data;
			if('gmedia_album' == $taxonomy){
				do{
					$term['name'] = trim($term['name']);
					if(empty($term['name'])){
						$error[] = __('Term Name is not specified', 'gmLang');
						break;
					}
					if($gmCore->is_digit($term['name'])){
						$error[] = __("Term Name can't be only digits", 'gmLang');
						break;
					}
					if($edit_term && !$gmDB->term_exists($edit_term, $taxonomy)){
						$alert[] = __('A term with the id provided do not exists', 'gmLang');
						$edit_term = false;
					}
					if(($term_id = $gmDB->term_exists($term['name'], $taxonomy))){
						if($term_id != $edit_term){
							$error[] = __('A term with the name provided already exists', 'gmLang');
							break;
						}
					}
					if($edit_term){
						$term_id = $gmDB->update_term($edit_term, $taxonomy, $term);
					} else{
						$term_id = $gmDB->insert_term($term['name'], $taxonomy, $term);
					}
					if(is_wp_error($term_id)){
						$error[] = $term_id->get_error_message();
						break;
					}

					$term_meta = array(
						'orderby' => $term['orderby']
						,'order' => $term['order']
					);
					foreach($term_meta as $key => $value){
						if($edit_term){
							$gmDB->update_metadata('gmedia_term', $term_id, $key, $value);
						} else{
							$gmDB->add_metadata('gmedia_term', $term_id, $key, $value);
						}
					}

					$alert[] = sprintf(__('Album `%s` successfuly saved', 'gmLang'), $term['name']);

				} while(0);
				$out = gmedia_ios_app_library_data(array('filter',$taxonomy));
			}
			elseif('gmedia_tag' == $taxonomy){
				if($edit_term){
					$term['name'] = trim($term['name']);
					$term['term_id'] = intval($term['term_id']);
					if( $term['name'] && !$gmCore->is_digit($term['name']) ){
						if ( ($term_id = $gmDB->term_exists( $term['term_id'], $taxonomy )) ) {
							$term_id = $gmDB->update_term( $term['term_id'], $taxonomy, $term );
							if ( is_wp_error( $term_id ) ) {
								$error[] = $term_id->get_error_message();
							} else{
								$alert[] = sprintf( __( "Tag %d successfuly updated", 'gmLang' ), $term_id );
							}
						} else{
							$error[] = __( "A term with the id provided do not exists.", 'gmLang' );
						}
					} else{
						$error[] = __( "Term name can't be only digits or empty", 'gmLang' );
					}
				} else{
					$terms = array_filter(array_map('trim', explode(',', $term['name'])));
					$terms_added = 0; $terms_qty = count($terms);
					foreach($terms as $term_name){
						if($gmCore->is_digit($term_name)){ continue; }

						if(!$gmDB->term_exists($term_name, $taxonomy)){
							$term_id = $gmDB->insert_term($term_name, $taxonomy);
							if(is_wp_error($term_id)){
								$error[] = $term_id->get_error_message();
							} else{
								$alert['tag_add'] = sprintf(__('%d of %d tags successfuly added', 'gmLang'), ++$terms_added, $terms_qty);
							}
						} else{
							$alert['tag_add'] = __('Some of provided tags are already exists', 'gmLang');
						}
					}
				}
				$out = gmedia_ios_app_library_data(array('filter',$taxonomy));
			}

			break;
		default:
			break;
	}

	if(!empty($error)){
		$out['error'] = array('code' => $action, 'title' => 'ERROR', 'message' => implode("\n", $error));
	}
	if(!empty($alert)){
		$out['alert'] = array('title' => 'Success', 'message' => implode("\n", $alert));
	}

	return $out;
}


header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ), true );
echo json_encode($out);

/*if(isset($_GET['test'])){
	echo "\n\n".print_r($out, true);
}*/
