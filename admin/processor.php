<?php if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	die('You are not allowed to call this page directly.');
}

/**
 * Class GmediaProcessor
 */
class GmediaProcessor{

	var $mode;
	var $page;
	var $msg;
	var $error;
	var $term_id;
	var $selected_items = array();

	/**
	 * initiate the manage page
	 */
	function __construct(){
		global $pagenow, $gmCore;
		// GET variables
		$this->mode = $gmCore->_get('mode');
		$this->page = $gmCore->_get('page', 'GrandMedia');

		if('media.php' === $pagenow){
			add_filter('wp_redirect', array(&$this, 'redirect'), 10, 2);
		}

		add_action('init', array(&$this, 'selected_items'), 8);
		add_action('init', array(&$this, 'processor'));

	}

	function  selected_items(){
		global $user_ID, $gmCore;
		switch($this->page){
			case 'GrandMedia':
				$ckey = "gmedia_u{$user_ID}_library";
				break;
			case 'GrandMedia_Terms':
				$taxonomy = $gmCore->_get('term', 'gmedia_album');
				$ckey = "gmedia_u{$user_ID}_{$taxonomy}";
				break;
			case 'GrandMedia_Galleries':
				$taxonomy = $gmCore->_get('term', 'gmedia_gallery');
				$ckey = "gmedia_u{$user_ID}_{$taxonomy}";
				break;
			case 'GrandMedia_WordpressLibrary':
				$ckey = "gmedia_u{$user_ID}_wpmedia";
				break;
			default:
				$ckey = false;
				break;
		}

		if($ckey){
			if(isset($_POST['selected_items'])){
				$this->selected_items = array_filter(explode(',', $_POST['selected_items']), 'is_numeric');
			} elseif(isset($_COOKIE[$ckey])){
				$this->selected_items = array_filter(explode(',', $_COOKIE[$ckey]), 'is_numeric');
			}
		}
	}

	/**
	 * @return array|mixed
	 */
	function user_options(){
		global $user_ID, $gmGallery;

		$gm_screen_options = get_user_meta($user_ID, 'gm_screen_options', true);
		if(!is_array($gm_screen_options)){
			$gm_screen_options = array();
		}
		$gm_screen_options = array_merge($gmGallery->options['gm_screen_options'], $gm_screen_options);

		return $gm_screen_options;
	}

	// Do diff process before lib shell
	function processor(){
		global $gmCore, $gmDB, $gmGallery;

		// check for correct capability
		//if ( ! current_user_can( 'edit_posts' ) )
		//	die( '-1' );

		switch($this->page){
			case 'GrandMedia':
				if(isset($_POST['quick_gallery'])){
					do{
						$gallery = $gmCore->_post('gallery');
						$gallery['name'] = trim($gallery['name']);
						if(empty($gallery['name'])){
							$this->error[] = __('Gallery Name is not specified', 'gmLang');
							break;
						}
						if($gmCore->is_digit($gallery['name'])){
							$this->error[] = __("Gallery name can't be only digits", 'gmLang');
							break;
						}
						if(empty($gallery['query']['gmedia__in'])){
							$this->error[] = __('Choose gmedia from library for quick gallery', 'gmLang');
							break;
						}
						$taxonomy = 'gmedia_gallery';
						if(($term_id = $gmDB->term_exists($gallery['name'], $taxonomy))){
							$this->error[] = __('A term with the name provided already exists', 'gmLang');
							break;
						}
						$term_id = $gmDB->insert_term($gallery['name'], $taxonomy);
						if(is_wp_error($term_id)){
							$this->error[] = $term_id->get_error_message();
							break;
						}

						$gallery_meta = array(
							'edited' => gmdate('Y-m-d H:i:s')
							,'module' => $gallery['module']
							,'query' => array('gmedia__in' => $gallery['query']['gmedia__in'])
							,'settings' => array($gallery['module'] => array())
						);
						foreach($gallery_meta as $key => $value){
							$gmDB->add_metadata('gmedia_term', $term_id, $key, $value);
						}
						$this->msg[] = sprintf(__('Gallery "%s" successfuly saved. Shortcode: [gmedia id=%d]', 'gmLang'), esc_attr($gallery['name']), $term_id);
					} while(0);
				}

				if(isset($_POST['filter_categories'])){
					if(($term = $gmCore->_post('cat'))){
						$location = add_query_arg(array('page' => $this->page, 'mode' => $this->mode, 'category__in' => implode(',', $term)), admin_url('admin.php'));
						wp_redirect($location);
					}
				}
				if(isset($_POST['filter_albums'])){
					if(($term = $gmCore->_post('alb'))){
						$location = add_query_arg(array('page' => $this->page, 'mode' => $this->mode, 'album__in' => implode(',', $term)), admin_url('admin.php'));
						wp_redirect($location);
					}
				}
				if(isset($_POST['filter_tags'])){
					if(($term = $gmCore->_post('tag_ids'))){
						$location = add_query_arg(array('page' => $this->page, 'mode' => $this->mode, 'tag__in' => $term), admin_url('admin.php'));
						wp_redirect($location);
					}
				}
				if(!empty($this->selected_items)){
					if(isset($_POST['assign_category'])){
						$term = $gmCore->_post('cat');
						if(false !== $term){
							$count = count($this->selected_items);
							if('0' == $term){
								foreach($this->selected_items as $item){
									$gmDB->delete_gmedia_term_relationships($item, 'gmedia_category');
								}
								$this->msg[] = sprintf(__('%d items updated with "Uncategorized"', 'gmLang'), $count);
							} else{
								foreach($this->selected_items as $item){
									$result = $gmDB->set_gmedia_terms($item, $term, 'gmedia_category', $append = 0);
									if(is_wp_error($result)){
										$this->error[] = $result;
										$count--;
									} elseif(!$result){
										$count--;
									}
								}
								if(isset($gmGallery->options['taxonomies']['gmedia_category'][$term])){
									$cat_name = $gmGallery->options['taxonomies']['gmedia_category'][$term];
									$this->msg[] = sprintf(__("Category `%s` assigned to %d images.", 'gmLang'), esc_html($cat_name), $count);
								} else{
									$this->error[] = sprintf(__("Category `%s` can't be assigned.", 'gmLang'), $term);;
								}
							}
						}
					}
					if(isset($_POST['assign_album'])){
						$term = $gmCore->_post('alb');
						if(false !== $term){
							$count = count($this->selected_items);
							if('0' == $term){
								foreach($this->selected_items as $item){
									$gmDB->delete_gmedia_term_relationships($item, 'gmedia_album');
								}
								$this->msg[] = sprintf(__('%d items updated with "No Album"', 'gmLang'), $count);
							} else{
								foreach($this->selected_items as $item){
									$result = $gmDB->set_gmedia_terms($item, $term, 'gmedia_album', $append = 0);
									if(is_wp_error($result)){
										$this->error[] = $result;
										$count--;
									} elseif(!$result){
										$count--;
									}
								}
								if($gmCore->is_digit($term)){
									$alb_name = $gmDB->get_alb_name($term);
								} else{
									$alb_name = $term;
								}
								$this->msg[] = sprintf(__('Album `%s` assigned to %d items', 'gmLang'), esc_html($alb_name), $count);
							}
						}

					}
					if(isset($_POST['add_tags'])){
						if(($term = $gmCore->_post('tag_names'))){
							$term = explode(',', $term);
							$count = count($this->selected_items);
							foreach($this->selected_items as $item){
								$result = $gmDB->set_gmedia_terms($item, $term, 'gmedia_tag', $append = 1);
								if(is_wp_error($result)){
									$this->error[] = $result;
									$count--;
								} elseif(!$result){
									$count--;
								}
							}
							$this->msg[] = sprintf(__('%d tags added to %d items', 'gmLang'), count($term), $count);
						}
					}
					if(isset($_POST['delete_tags'])){
						if(($term = $gmCore->_post('tag_id'))){
							$term = array_map('intval', $term);
							$count = count($this->selected_items);
							foreach($this->selected_items as $item){
								$result = $gmDB->set_gmedia_terms($item, $term, 'gmedia_tag', $append = -1);
								if(is_wp_error($result)){
									$this->error[] = $result;
									$count--;
								} elseif(!$result){
									$count--;
								}
							}
							$this->msg[] = sprintf(__('%d tags deleted from %d items', 'gmLang'), count($term), $count);
						}
					}
					if('selected' == $gmCore->_get('delete')){
						global $user_ID;
						check_admin_referer('gmedia_delete');
						if(!current_user_can('delete_posts')){
							wp_die(__('You are not allowed to delete this post.'));
						}
						$count = count($this->selected_items);
						foreach($this->selected_items as $item){
							if(!$gmDB->delete_gmedia((int)$item)){
								$this->error[] = "#{$item}: " . __('Error in deleting...', 'gmLang');
								$count--;
							}
						}
						if($count){
							$this->msg[] = sprintf(__('%d items deleted successfuly', 'gmLang'), $count);
						}
						unset($_COOKIE["gmedia_u{$user_ID}_library"]);
						setcookie($_COOKIE["gmedia_u{$user_ID}_library"], '', time() - 3600);
						$this->selected_items = array();
					}
				}
				break;
			case 'GrandMedia_AddMedia':
				break;
			case 'GrandMedia_Terms':
				$taxonomy = $gmCore->_get('term', 'gmedia_album');
				if(!empty($this->selected_items)){
					if('selected' == $gmCore->_get('delete')){
						global $user_ID;
						check_admin_referer('gmedia_delete');
						if(!current_user_can('delete_posts')){
							wp_die(__('You are not allowed to delete this post.'));
						}
						$count = count($this->selected_items);
						foreach($this->selected_items as $item){
							$delete = $gmDB->delete_term($item, $taxonomy);
							if(is_wp_error($delete)){
								$this->error[] = $delete->get_error_message();
								$count--;
							}
						}
						if($count){
							$this->msg[] = sprintf(__('%d items deleted successfuly', 'gmLang'), $count);
						}
						unset($_COOKIE["gmedia_u{$user_ID}_{$taxonomy}"]);
						setcookie($_COOKIE["gmedia_u{$user_ID}_{$taxonomy}"], '', time() - 3600);
						$this->selected_items = array();
					}
				}
				if(isset($_POST['gmedia_album_save'])){
					check_admin_referer('GmediaTerms', 'term_save_wpnonce');
					$edit_term = (int) $gmCore->_get('edit_album');
					do{
						$term = $gmCore->_post('term');
						$term['name'] = trim($term['name']);
						if(empty($term['name'])){
							$this->error[] = __('Term Name is not specified', 'gmLang');
							break;
						}
						if($gmCore->is_digit($term['name'])){
							$this->error[] = __("Term Name can't be only digits", 'gmLang');
							break;
						}
						$taxonomy = 'gmedia_album';
						if($edit_term && !$gmDB->term_exists($edit_term, $taxonomy)){
							$this->error[] = __('A term with the id provided do not exists', 'gmLang');
							$edit_term = false;
						}
						if(($term_id = $gmDB->term_exists($term['name'], $taxonomy))){
							if($term_id != $edit_term){
								$this->error[] = __('A term with the name provided already exists', 'gmLang');
								break;
							}
						}
						if($edit_term){
							$term_id = $gmDB->update_term($edit_term, $term['taxonomy'], $term);
						} else{
							$term_id = $gmDB->insert_term($term['name'], $term['taxonomy'], array('description' => $term['description'], 'status' => $term['status']));
						}
						if(is_wp_error($term_id)){
							$this->error[] = $term_id->get_error_message();
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

						$this->msg[] = sprintf(__('Album `%s` successfuly saved', 'gmLang'), $term['name']);

					} while(0);
				}
				if(isset($_POST['gmedia_tag_add'])){
					check_admin_referer('GmediaTerms', 'term_save_wpnonce');
					$term = $gmCore->_post('term');
					$terms = array_filter(array_map('trim', explode(',', $term['name'])));
					$terms_added = 0; $terms_qty = count($terms);
					foreach($terms as $term_name){
						if($gmCore->is_digit($term_name)){ continue; }

						if(!$gmDB->term_exists($term_name, $term['taxonomy'])){
							$term_id = $gmDB->insert_term($term_name, $term['taxonomy']);
							if(is_wp_error($term_id)){
								$this->error[] = $term_id->get_error_message();
							} else{
								$this->msg['tag_add'] = sprintf(__('%d of %d tags successfuly added', 'gmLang'), ++$terms_added, $terms_qty);
							}
						} else{
							$this->error['tag_add'] = __('Some of provided tags are already exists', 'gmLang');
						}
					}
				}
				break;
			case 'GrandMedia_Galleries':
				if(isset($_POST['gmedia_gallery_save'])){
					$edit_gallery = (int) $gmCore->_get('edit_gallery');
					do{
						$gallery = $gmCore->_post('gallery');
						$gallery['name'] = trim($gallery['name']);
						if(empty($gallery['name'])){
							$this->error[] = __('Gallery Name is not specified', 'gmLang');
							break;
						}
						if($gmCore->is_digit($gallery['name'])){
							$this->error[] = __("Gallery name can't be only digits", 'gmLang');
							break;
						}
						if(empty($gallery['module'])){
							$this->error[] = __('Something goes wrong... Choose module, please', 'gmLang');
							break;
						}
						$term = $gallery['term'];
						if(!isset($gallery['query'][$term]) || empty($gallery['query'][$term])){
							$this->error[] = __('Choose gallery source, please (tags, albums, categories...)', 'gmLang');
							break;
						}
						$taxonomy = 'gmedia_gallery';
						if($edit_gallery && !$gmDB->term_exists($edit_gallery, $taxonomy)){
							$this->error[] = __('A term with the id provided do not exists', 'gmLang');
							$edit_gallery = false;
						}
						if(($term_id = $gmDB->term_exists($gallery['name'], $taxonomy))){
							if($term_id != $edit_gallery){
								$this->error[] = __('A term with the name provided already exists', 'gmLang');
								break;
							}
						}
						if($edit_gallery){
							$term_id = $gmDB->update_term($edit_gallery, $taxonomy, array('name' => $gallery['name'], 'description' => $gallery['description'], 'status' => $gallery['status']));
						} else{
							$term_id = $gmDB->insert_term($gallery['name'], $taxonomy, array('description' => $gallery['description'], 'status' => $gallery['status']));
						}
						if(is_wp_error($term_id)){
							$this->error[] = $term_id->get_error_message();
							break;
						}

						$module_settings = $gmCore->_post('module', array());
						$gallery_meta = array(
							 'edited' => gmdate('Y-m-d H:i:s')
							,'module' => $gallery['module']
							,'query' => array($term => $gallery['query'][$term])
							,'settings' => array($gallery['module'] => $module_settings)
						);
						foreach($gallery_meta as $key => $value){
							if($edit_gallery){
								$gmDB->update_metadata('gmedia_term', $term_id, $key, $value);
							} else{
								$gmDB->add_metadata('gmedia_term', $term_id, $key, $value);
							}
						}
						if($edit_gallery){
							$this->msg[] = sprintf(__('Gallery #%d successfuly saved', 'gmLang'), $term_id);
						} else{
							$location = add_query_arg(array('page' => $this->page, 'edit_gallery' => $term_id, 'message' => 'save'), admin_url('admin.php'));
							wp_redirect($location);
						}
					} while(0);
				}
				if(('save' == $gmCore->_get('message')) && ($term_id = $gmCore->_get('edit_gallery'))){
					$this->msg[] = sprintf(__('Gallery #%d successfuly saved', 'gmLang'), $term_id);
				}

				if(isset($_POST['gmedia_gallery_reset'])){
					$edit_gallery = (int) $gmCore->_get('edit_gallery');
					do{
						$taxonomy = 'gmedia_gallery';
						if(!$gmDB->term_exists($edit_gallery, $taxonomy)){
							$this->error[] = __('A term with the id provided do not exists', 'gmLang');
							break;
						}
						$gallery_settings = $gmDB->get_metadata('gmedia_term', $edit_gallery, 'settings', true);
						reset($gallery_settings);
						$gallery_module = key($gallery_settings);
						$module_path = $gmCore->get_module_path($gallery_module);
						/**
						 * @var $default_options
						 */
						if(file_exists($module_path['path'] . '/settings.php')){
							include($module_path['path'] . '/settings.php');
						} else{
							$this->error[] = sprintf(__('Can\'t load data from `%s` module'), $gallery_module);
							break;
						}

						$gallery_meta = array(
							 'edited' => gmdate('Y-m-d H:i:s')
							,'settings' => array($gallery_module => $default_options)
						);
						foreach($gallery_meta as $key => $value){
							$gmDB->update_metadata('gmedia_term', $edit_gallery, $key, $value);
						}
						$this->msg[] = sprintf(__('Gallery settings are reset', 'gmLang'));

					} while(0);

				}

				if(!empty($this->selected_items)){
					if('selected' == $gmCore->_get('delete')){
						global $user_ID;
						check_admin_referer('gmedia_delete');
						if(!current_user_can('delete_posts')){
							wp_die(__('You are not allowed to delete this post.'));
						}
						$taxonomy = 'gmedia_gallery';
						$count = count($this->selected_items);
						foreach($this->selected_items as $item){
							$delete = $gmDB->delete_term($item, $taxonomy);
							if(is_wp_error($delete)){
								$this->error[] = $delete->get_error_message();
								$count--;
							}
						}
						if($count){
							$this->msg[] = sprintf(__('%d items deleted successfuly', 'gmLang'), $count);
						}
						unset($_COOKIE["gmedia_u{$user_ID}_{$taxonomy}"]);
						setcookie($_COOKIE["gmedia_u{$user_ID}_{$taxonomy}"], '', time() - 3600);
						$this->selected_items = array();
					}
				}

				break;
			case 'GrandMedia_Modules':
				if(isset($_FILES['modulezip']['tmp_name'])){
					if(!empty($_FILES['modulezip']['tmp_name'])){
						check_admin_referer('GmediaModule');
						if(!current_user_can('edit_posts')){
							wp_die(__('You are not allowed to install modules', 'gmLang'));
						}

						$to_folder = $gmCore->upload['path'] . '/' . $gmGallery->options['folder']['module'] . '/';
						$filename = wp_unique_filename($to_folder, $_FILES['modulezip']['name']);

						// Move the file to the modules dir
						if(false === @move_uploaded_file($_FILES['modulezip']['tmp_name'], $to_folder . $filename)){
							$this->error[] = sprintf(__('The uploaded file could not be moved to %s', 'flag'), $to_folder . $filename);
						} else{
							global $wp_filesystem;
							// Is a filesystem accessor setup?
							if(!$wp_filesystem || !is_object($wp_filesystem)){
								require_once(ABSPATH . 'wp-admin/includes/file.php');
								WP_Filesystem();
							}
							if(!is_object($wp_filesystem)){
								$result = new WP_Error('fs_unavailable', __('Could not access filesystem.', 'flag'));
							} elseif($wp_filesystem->errors->get_error_code()){
								$result = new WP_Error('fs_error', __('Filesystem error', 'flag'), $wp_filesystem->errors);
							} else{
								$result = unzip_file($to_folder . $filename, $to_folder);
							}
							// Once extracted, delete the package
							unlink($to_folder . $filename);
							if(is_wp_error($result)){
								$this->error[] = $result->get_error_message();
							} else{
								$this->msg[] = sprintf(__("The `%s` file unzipped to module's directory", 'flag'), $filename);
							}
						}
					} else{
						$this->error[] = __('No file specified', 'gmLang');
					}
				}
				break;
			case 'GrandMedia_Settings':
				$lk_check = isset($_POST['license-key-activate']);
				if(isset($_POST['gmedia_settings_save'])){
					check_admin_referer('GmediaSettings');
					$set = $gmCore->_post('set', array());
					if(!empty($set['license_key']) && empty($set['license_key2'])){
						$lk_check = true;
					}
					if(empty($set['license_key']) && !empty($set['license_key2'])){
						$set['license_name'] = '';
						$set['license_key'] = '';
						$set['license_key2'] = '';
						$this->error[] = __('License Key deactivated', 'gmLang');
					}
					foreach($set as $key => $val){
						$gmGallery->options[$key] = $val;
					}
					update_option('gmediaOptions', $gmGallery->options);
					$this->msg[] .= __('Settings saved', 'gmLang');
				}

				if($lk_check){
					check_admin_referer('GmediaSettings');
					$license_key = $gmCore->_post('set');
					if(!empty($license_key['license_key'])){
						global $wp_version;
						$gmedia_ua = "WordPress/{$wp_version} | ";
						$gmedia_ua .= 'Gmedia/' . constant('GMEDIA_VERSION');

						$response = wp_remote_post('http://codeasily.com/rest/gmedia-key.php', array(
								'body' => array('key' => $license_key['license_key'], 'site' => site_url()),
								'headers' => array(
									'Content-Type' => 'application/x-www-form-urlencoded; ' . 'charset=' . get_option('blog_charset'),
									'Host' => 'codeasily.com',
									'User-Agent' => $gmedia_ua
								),
								'httpversion' => '1.0',
								'timeout' => 10
							));

						if(is_wp_error($response)){
							$this->error[] = $response->get_error_message();
						} else{
							$result = json_decode($response['body']);
							if($result->error->code == 200){
								$gmGallery->options['license_name'] = $result->content;
								$gmGallery->options['license_key'] = $result->key;
								$gmGallery->options['license_key2'] = $result->key2;
								$this->msg[] = __('License Key activated successfully', 'gmLang');
							} else{
								$gmGallery->options['license_name'] = '';
								$gmGallery->options['license_key'] = '';
								$gmGallery->options['license_key2'] = '';
								$this->error[] = __('Error', 'gmLang') . ': ' . $result->error->message;
							}
							update_option('gmediaOptions', $gmGallery->options);
						}
					} else{
						$this->error[] = __('Empty License Key', 'gmLang');
					}
				}

				if(isset($_POST['gmedia_settings_reset'])){
					check_admin_referer('GmediaSettings');
					include_once(dirname(dirname(__FILE__)) . '/setup.php');
					$_temp_options = $gmGallery->options;
					$gmGallery->options = gmedia_default_options();
					$gmGallery->options['license_name'] = $_temp_options['license_name'];
					$gmGallery->options['license_key'] = $_temp_options['license_key'];
					$gmGallery->options['license_key2'] = $_temp_options['license_key2'];
					delete_metadata('user', 0, 'gm_screen_options', '', true);
					update_option('gmediaOptions', $gmGallery->options);
					$this->msg[] .= __('All settings set to default', 'gmLang');
				}
				break;
			case 'GrandMedia_WordpressLibrary':
				break;
			default:
				break;
		}
	}

	/**
	 * @param string $type
	 * @param string $content
	 *
	 * @return string
	 */
	function alert($type = 'info', $content = ''){
		if(empty($content)){
			return '';
		} elseif(is_array($content)){
			$content = implode('<br />', array_filter($content));
		}
		$alert = '<div class="alert alert-' . $type . ' alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' . $content . '</div>';

		return $alert;
	}

	/**
	 * redirect to original referer after update
	 * @param $location
	 * @param $status
	 *
	 * @return mixed
	 */
	function redirect($location, $status){
		global $pagenow;
		if('media.php' === $pagenow && isset($_POST['_wp_original_http_referer'])){
			if(strpos($_POST['_wp_original_http_referer'], 'GrandMedia') !== false){
				return $_POST['_wp_original_http_referer'];
			} else{
				return $location;
			}
		}

		return $location;
	}

}

global $gmProcessor;
$gmProcessor = new GmediaProcessor();