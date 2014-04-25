<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])){
	die('You are not allowed to call this page directly.');
}

/**
 * gmediaModules()
 *
 * @return mixed content
 */
function gmediaModules(){
	global $gmCore, $gmProcessor, $gmGallery;

	$url = add_query_arg(array('page' => $gmProcessor->page), admin_url('admin.php'));

	$modules = array();
	if($plugin_modules = glob(GMEDIA_ABSPATH . 'module/*', GLOB_ONLYDIR | GLOB_NOSORT)){
		foreach($plugin_modules as $path){
			$mfold = basename($path);
			$modules[$mfold] = array(
				'place' => 'plugin',
				'module_name' => $mfold,
				'module_url' => $gmCore->gmedia_url . "/module/{$mfold}",
				'module_path' => $path
			);
		}
	}
	if($upload_modules = glob($gmCore->upload['path'].'/'.$gmGallery->options['folder']['module'].'/*', GLOB_ONLYDIR | GLOB_NOSORT)){
		foreach($upload_modules as $path){
			$mfold = basename($path);
			$modules[$mfold] = array(
				'place' => 'upload',
				'module_name' => $mfold,
				'module_url' => $gmCore->upload['url'] . "/{$gmGallery->options['folder']['module']}/{$mfold}",
				'module_path' => $path
			);
		}
	}
	// not installed modules
	$xml = array();
	$get_xml = wp_remote_get($gmGallery->options['modules_xml']);
	if(!is_wp_error($get_xml) && (200 == $get_xml['response']['code'])){
		$xml = @simplexml_load_string($get_xml['body']);
	} else{
		$alert = array(__('Error loading remote xml...', 'gmLang'));
		if(is_wp_error($get_xml)){
			$alert[] = $get_xml->get_error_message();
		}
		echo $gmProcessor->alert('danger', $alert);
	}

	if(!empty($xml)){
		foreach($xml as $m){
			$name = (string)$m->name;
			$xml_modules[$name] = get_object_vars($m);
		}
	}

	?>
<div id="gmedia_modules">
	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<a href="#installModuleModal" class="btn btn-primary pull-right" data-toggle="modal"><?php _e('Install Module ZIP'); ?></a>
			<h3 class="panel-title"><?php _e('Installed Modules', 'gmLang'); ?></h3>
		</div>
		<div class="panel-body" id="gmedia-msg-panel"></div>
		<div class="panel-body">
			<?php
			// installed modules
			if(!empty($modules)){
				foreach($modules as $m){
					/**
					 * @var $place
					 * @var $module_name
					 * @var $module_url
					 * @var $module_path
					 */
					extract($m);

					// todo: get broken modules folders and delete them with files in modules root
					if(!file_exists($module_path . '/index.php')){
						continue;
					}

					$module_info = array();
					include($module_path . '/index.php');
					if(empty($module_info)){
						continue;
					}

					$m = isset($xml_modules[$module_name])? array_merge($module_info, $xml_modules[$module_name]) : $module_info;
					$mclass = ' module-'.$m['type'].' module-'.$m['status'];

					$update_button = '';
					if(isset($xml_modules[$module_name])){
						if(version_compare((float)$xml_modules[$module_name]['version'], (float)$module_info['version'], '>')){
							$update_button = '<a class="btn btn-warning module_install" data-module="'.$module_name.'" data-loading-text="'.__('Loading...', 'gmLang').'" href="'.esc_url($xml_modules[$module_name]['download']).'">'.__('Update Module', 'gmLang')." (v{$xml_modules[$module_name]['version']})</a>";
							$mclass .= ' module-update';
						} else{
							unset($xml_modules[$module_name]);
						}
					}
					?>
					<div class="media<?php echo $mclass; ?>">
						<div class="thumbnail pull-left">
							<img class="media-object" src="<?php echo $module_url.'/screenshot.png'; ?>" alt="<?php echo esc_attr($m['title']); ?>" width="320" height="240"/>
						</div>
						<div class="media-body">
							<h4 class="media-heading"><?php echo $m['title']; ?></h4>
							<p class="version"><?php echo __('Version', 'gmLang') . ': ' . $module_info['version']; ?></p>
							<div class="description"><?php echo str_replace("\n", '<br />', (string) $m['description']); ?></div>
							<hr />
							<p class="buttons">
								<?php if(!empty($m['demo']) && $m['demo'] != '#'){ ?>
									<a class="btn btn-default" target="_blank" href="<?php echo $m['demo']; ?>"><?php _e('View Demo', 'gmLang') ?></a>
								<?php } ?>
								<a class="btn btn-success" href="<?php echo $gmCore->get_admin_url(array('page'=>'GrandMedia_Galleries','gallery_module'=>$module_name), array(), true); ?>"><?php _e('Create Gallery', 'gmLang'); ?></a>
								<?php echo $update_button; ?>
							</p>
						</div>
					</div>
				<?php
				}
			}
			?>
		</div>
	</div>

	<?php if(!empty($xml_modules)){ ?>
	<div class="panel panel-default">
		<div class="panel-heading clearfix">
			<h3 class="panel-title"><?php _e('Not Installed Modules', 'gmLang'); ?></h3>
		</div>
		<div class="panel-body" id="gmedia-msg-panel"></div>
		<div class="panel-body">
			<?php
			$xml_dirpath = dirname($gmGallery->options['modules_xml']);
			foreach($xml_modules as $name => $m){
				if(empty($m)){
					continue;
				}
				$mclass = ' module-'.$m['type'].' module-'.$m['status'];
				?>
				<div class="media<?php echo $mclass; ?>">
					<div class="thumbnail pull-left">
						<img class="media-object" src="<?php echo $xml_dirpath.'/'.$m['name']; ?>.png" alt="<?php echo esc_attr($m['title']); ?>" width="320" height="240"/>
					</div>
					<div class="media-body">
						<h4 class="media-heading"><?php echo $m['title']; ?></h4>
						<p class="version"><?php echo __('Version', 'gmLang') . ': ' . $m['version']; ?></p>
						<div class="description"><?php echo str_replace("\n", '<br />', (string) $m['description']); ?></div>
						<hr />
						<p class="buttons">
							<?php if(!empty($m['demo']) && $m['demo'] != '#'){ ?>
								<a class="btn btn-default" target="_blank" href="<?php echo $m['demo']; ?>"><?php _e('View Demo', 'gmLang') ?></a>
							<?php } ?>
							<a class="btn btn-primary module_install" data-module="<?php echo $m['name']; ?>" data-loading-text="<?php _e('Loading...', 'gmLang'); ?>" href="<?php echo $m['download']; ?>"><?php _e('Install Module', 'gmLang'); ?></a>
						</p>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
	<?php } ?>
</div>
	<!-- Modal -->
	<div class="modal fade gmedia-modal" id="installModuleModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<form class="modal-content" method="post" enctype="multipart/form-data" action="<?php echo $url; ?>">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><?php _e('Install a plugin in .zip format'); ?></h4>
				</div>
				<div class="modal-body">
					<p class="install-help"><?php _e('If you have a module in a .zip format, you may install it by uploading it here.'); ?></p>
					<?php wp_nonce_field( 'GmediaModule'); ?>
					<label class="screen-reader-text" for="modulezip"><?php _e('Module zip file'); ?></label>
					<input type="file" id="modulezip" name="modulezip" />
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Cancel', 'gmLang'); ?></button>
					<button type="submit" class="btn btn-primary"><?php _e('Install', 'gmLang'); ?></button>
				</div>
			</form>
		</div>
	</div>
<?php
}

