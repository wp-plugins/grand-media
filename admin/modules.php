<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * gmedia_manage_modules()
 *
 * @return mixed content
 */
function gmedia_manage_modules() {
	global $grandCore;
	$gmOptions = get_option( 'gmediaOptions' );
	$upload    = $grandCore->gm_upload_dir();
	$url       = $grandCore->getAdminURL();
	$nonce     = wp_create_nonce( 'grandMedia' );

	?>
	<div class="gMediaLibActions">
		<div class="abuts">
			<?php $curr_tab = $grandCore->_get( 'tab', 'galleries' ); ?>
			<a class="galleries<?php if ( $curr_tab == 'galleries' ) echo ' active'; ?>" href="<?php echo $url['page'] . '&amp;tab=galleries'; ?>"><?php _e( 'Manage Galleries', 'gmLang' ); ?></a>
			<a class="modules<?php if ( $curr_tab == 'modules' ) echo ' active'; ?>" href="<?php echo $url['page'] . '&amp;tab=modules'; ?>"><?php _e( 'Available Modules', 'gmLang' ); ?></a>
		</div>
		<?php if ( $curr_tab == 'modules' ) {
			echo '<div class="msg0">' . __( 'Installed Modules', 'gmLang' ) . '</div>';
		} ?>
	</div>

	<?php
	/* ---------------------------------------GALLERIES--------------------------------------- */
	if ( $curr_tab == 'galleries' ) {
		global $gMDb, $grandAdmin;

		$arg = array(
			'orderby'    => $grandCore->_get( 'orderby', 'name' ),
			'order'      => $grandCore->_get( 'order', 'ASC' ),
			'search'     => $grandCore->_get( 's', '' ),
			'number'     => 0,
			'hide_empty' => 0,
			'page'       => 1
		);
		/** @var $orderby
		 * @var  $order
		 * @var  $search
		 * @var  $page
		 * @var  $number
		 * @var  $hide_empty
		 */
		extract( $arg );
		$arg['offset'] = $offset = ( $page - 1 ) * $number;

		$taxonomy    = 'gmedia_module';
		$gMediaTerms = $gMDb->gmGetTerms( $taxonomy, $arg );


		/** @var $orderby
		 * @var  $order
		 * @var  $search
		 * @var  $include
		 */
		extract( $arg );

		$children             = array();
		$order                = $grandCore->_get( 'order', 'ASC' );
		$sort                 = 'ASC';
		$url_param['tab']     = '&amp;tab=' . $curr_tab;
		$url_param['orderby'] = '&amp;orderby=' . $orderby;
		$url_param['order']   = '&amp;order=' . $order;
		$url_param['s']       = $search ? '&amp;s=' . $search : '';
		?>

		<div id="gMediaLibTable" class="<?php echo $taxonomy; ?>">
			<table class="gMediaLibTable" cellspacing="0">
				<col class="bufer" />
				<col class="module_preview" />
				<col class="id" />
				<col class="name" />
				<col class="descr" />
				<col class="count" />
				<col class="last_edited" />
				<col class="actions" />
				<thead>
				<tr>
					<th class="bufer"><span></span></th>
					<th class="module_preview"><span><?php _e( 'Preview Image', 'gmLang' ); ?></span></th>
					<th class="id <?php if ( $orderby == 'id' ) {
						echo $order;
						$sort = ( $order == 'DESC' ) ? 'ASC' : 'DESC';
					} ?>" title="<?php _e( 'Sort by ID', 'gmLang' ); ?>">
						<a href="<?php echo $url['page'] . $url_param['tab'] . '&amp;orderby=id&amp;order=' . $sort . $url_param['s']; $sort = 'ASC'; ?>"><?php _e( 'ID', 'gmLang' ); ?></a>
					</th>
					<th class="name <?php if ( $orderby == 'name' ) {
						echo $order;
						$sort = ( $order == 'DESC' ) ? 'ASC' : 'DESC';
					} ?>" title="<?php _e( 'Sort by name', 'gmLang' ); ?>">
						<a href="<?php echo $url['page'] . $url_param['tab'] . '&amp;orderby=name&amp;order=' . $sort . $url_param['s']; $sort = 'ASC'; ?>"><?php _e( 'Name', 'gmLang' ); ?></a>
					</th>
					<th class="descr"><span><?php _e( 'Description', 'gmLang' ); ?></span></th>
					<th class="count"><?php _e( 'Count', 'gmLang' ); ?></th>
					<th class="last_edited"><span><?php _e( 'Last Edited', 'gmLang' ); ?></span></th>
					<th class="actions"><span><?php _e( 'Actions', 'gmLang' ); ?></span></th>
				</tr>
				</thead>
				<tbody class="gmLib">
				<?php
				if ( count( $gMediaTerms ) ) {
					$filter = ( empty( $arg['search'] ) ) ? false : true;
					//$count = 0;
					//$termsHierarr = $grandCore->gmGetTermsHierarr( $taxonomy, $gMediaTerms, $children, $count, $offset, $number, 0, 0, $filter );
					foreach ( $gMediaTerms as $termitem ) {
						$grandAdmin->gm_term_row( $termitem );
					}
				}
				else {
					echo '<tr class="emptybd"><td colspan="8">' . __( 'No Galleries.', 'gmLang' ) . ' <a href="' . admin_url( 'admin.php?page=GrandMedia_Modules&amp;tab=modules' ) . '">' . __( 'Create', 'gmLang' ) . '</a></td></tr>';
				}
				?>
				</tbody>
			</table>
			<?php wp_original_referer_field( true, 'previous' ); ?>
		</div>

	<?php
	}
	/* ---------------------------------------MODULES--------------------------------------- */
	if ( $curr_tab == 'modules' ) {
		?>
		<div class="gmediaModules">
			<?php
			// not installed modules
			$modules_xml = @simplexml_load_file( 'http://dl.dropbox.com/u/6295502/gmedia_modules/modules.xml', 'SimpleXMLElement', LIBXML_NOCDATA );
			$all_modules = $modules_by_type = $available_modules = array();
			$modules_xml_message = '';
			if ( ! empty( $modules_xml ) ) {
				foreach ( $modules_xml as $m ) {
					$muid               = (string) $m->uid;
					$type               = (string) $m->type;
					$all_modules[$muid] = get_object_vars( $m );
					//$modules_by_type[$type][$muid] = $all_modules[$muid];
				}
				$modules_xml_message = __( 'All available modules are already installed...', 'flag' );
			}
			else {
				$modules_xml_message = __( 'Error loading remote modules or URL file-access is disabled in the server configuration...', 'flag' ) . ' <a class="ext" href="http://codeasily.com/gmedia/faq">' . __( 'more', 'gmLang' ) . '</a>';
			}

			// plugin's module folder
			$modules = glob( GRAND_ABSPATH . 'module/*', GLOB_NOSORT );
			if ( ! empty( $modules ) ) {
				$modules = array_filter( $modules, 'is_dir' );
				foreach ( $modules as $moduledir ) {
					$module = array();
					include( $moduledir . '/details.php' );
					$moduledir = basename( $moduledir );
					if ( ! empty( $module ) ) {
						$muid                     = $module['uid'];
						$available_modules[$muid] = $module;
						$mclass                   = $module['type'] . ' ' . $module['status'];
						$update                   = '';
						if ( isset( $all_modules[$muid] ) && (string) $all_modules[$muid]['uid'] == $module['uid'] ) {
							if ( version_compare( (float) $all_modules[$muid]['version'], (float) $module['version'], '>' ) ) {
								$update = '<p class="msg">' . __( 'New version available. Module will be updated with latest version of plugin.' ) . '</p>';
								$mclass .= ' update';
							}
							$module['demo'] = $all_modules[$muid]['demo'];
							unset( $all_modules[$muid] );
						}
						?>
						<div class="module <?php echo $mclass; ?>" id="<?php echo $muid; ?>">
							<div class="screenshot">
								<img src="<?php echo plugins_url( GRAND_FOLDER . "/module/$moduledir/screenshot.png" ); ?>" alt="<?php echo $module['title']; ?>" width="320" height="240" />
							</div>
							<div class="content">
								<h3><?php echo $module['title']; ?></h3>
								<span class="version"><?php echo __( 'Version', 'gmLang' ) . ': ' . $module['version']; ?></span>

								<div class="description"><?php echo str_replace("\n", '<br />', $module['description']); ?></div>
								<div class="links">
									<?php if(!empty($module['demo']) && $module['demo'] != '#'){ ?>
									<a class="module_preview button" target="_blank" href="<?php echo $module['demo']; ?>"><?php _e( 'View Demo', 'gmLang' ) ?></a>
									|
									<?php } ?>
									<a class="module_create button-primary" href="<?php echo wp_nonce_url( 'admin.php?page=GrandMedia_Modules&amp;module=' . $moduledir, 'grandMedia' ); ?>"><?php _e( 'Create Gallery', 'gmLang' ) ?></a>
									<?php echo $update; ?>
								</div>
							</div>
						</div>
					<?php
					}
				}
			}

			// installed modules
			$modules = glob( $upload['path'] . $gmOptions['folder']['module'] . '/*', GLOB_NOSORT );
			if ( ! empty( $modules ) ) {
				$modules = array_filter( $modules, 'is_dir' );
				foreach ( $modules as $moduledir ) {
					$module = array();
					include( $moduledir . '/details.php' );
					$moduledir = basename( $moduledir );
					if ( $module ) {
						$muid                     = $module['uid'];
						$available_modules[$muid] = $module;
						$mclass                   = $module['type'] . ' ' . $module['status'];
						$update                   = '';
						if ( isset( $all_modules[$muid] ) && (string) $all_modules[$muid]['uid'] == $module['uid'] ) {
							if ( version_compare( (float) $all_modules[$muid]['version'], (float) $module['version'], '>' ) ) {
								$update = '| <a class="module_update ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-post="module=' . $moduledir . '" data-task="gm-update-module" href="#">' . __( 'update', 'gmLang' ) . '</a>';
								$mclass .= ' module_update';
							}
							$module['demo'] = $all_modules[$muid]['demo'];
							unset( $all_modules[$muid] );
						}
						?>
						<div class="module <?php echo $mclass; ?>" id="<?php echo $muid; ?>">
							<div class="screenshot">
								<img src="<?php echo content_url( GRAND_FOLDER . "/module/$moduledir/screenshot.png" ); ?>" alt="<?php echo $module['title']; ?>" width="320" height="240" />
							</div>
							<div class="content">
								<h3><?php echo $module['title']; ?></h3>
								<span class="version"><?php echo __( 'Version', 'gmLang' ) . ': ' . $module['version']; ?></span>

								<div class="description"><?php echo str_replace("\n", '<br />', $module['description']); ?></div>
								<div class="links">
									<a class="module_delete button button-red ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-post="module=<?php echo $moduledir; ?>" data-task="gm-delete-module" data-confirmtxt="<?php echo sprintf( __( "Are you sure want to delete %s module?\n\r'Cancel' to stop, 'OK' to delete.", "gmLang" ), $module['title'] ); ?>" href="<?php echo $url['page'] . '&amp;delete_module=' . urlencode( $moduledir ) . '&amp;_wpnonce=' . $nonce; ?>"><?php _e( 'delete', 'gmLang' ) ?></a>
									|
									<?php if(!empty($module['demo']) && $module['demo'] != '#'){ ?>
									<a class="module_preview button" target="_blank" href="<?php echo $module['demo']; ?>"><?php _e( 'View Demo', 'gmLang' ) ?></a>
									|
									<?php } ?>
									<a class="module_create button-primary" href="<?php echo wp_nonce_url( 'admin.php?page=GrandMedia_Modules&amp;module=' . $moduledir, 'grandMedia' ); ?>"><?php _e( 'Create Gallery', 'gmLang' ) ?></a>
									<?php echo $update; ?>
								</div>
							</div>
						</div>
					<?php
					}
				}
			}
			?>
		</div>
		<div class="gMediaLibActions" style="margin-top: 20px;">
			<div class="msg0"><?php _e( 'Not Installed Modules', 'gmLang' ) ?></div>
		</div>
		<div class="gmediaModules">
			<?php
			if ( ! empty( $all_modules ) ) {
				?>
				<?php foreach ( $all_modules as $module ) { ?>
					<div class="module <?php echo $module['type'] . ' ' . $module['status']; ?>" id="<?php echo $module['uid']; ?>">
						<div class="screenshot">
							<img src="http://dl.dropbox.com/u/6295502/gmedia_modules/<?php echo $module['filename']; ?>.png" alt="<?php echo $module['title']; ?>" width="320" height="240" />
						</div>
						<div class="content">
							<h3><?php echo $module['title']; ?></h3>
							<span class="version"><?php echo __( 'Version', 'gmLang' ) . ': ' . $module['version']; ?></span>

							<div class="description"><?php echo str_replace("\n", '<br />', $module['description']); ?></div>
							<div class="links">
								<?php if(!empty($module['demo']) && $module['demo'] != '#'){ ?>
								<a class="module_preview button" target="_blank" href="<?php echo $module['demo']; ?>"><?php _e( 'View Demo', 'gmLang' ) ?></a>
								|
								<?php } ?>
								<a class="install ajaxPost button-primary" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-post="module=<?php echo $module['filename']; ?>" data-task="gm-install-module" href="http://dl.dropbox.com/u/6295502/gmedia_modules/<?php echo $module['filename']; ?>.zip"><?php _e( 'Install Module', 'gmLang' ) ?></a>
							</div>
						</div>
					</div>
				<?php
				}
			}
			else {
				?>
				<div class="module nomodules"><?php echo $modules_xml_message; ?></div>
			<?php
			}
			?>
		</div>
	<?php
	}

}


/**
 * gmedia_module_settings()
 *
 * @param string $module_folder
 * @param int    $term_id
 *
 * @return mixed content
 */
function gmedia_module_settings( $module_folder, $term_id = 0 ) {
	global $grandCore, $gMDb;

	// check for correct capability
	if ( ! current_user_can( 'edit_posts' ) )
		die( '-1' );

	$url   = $grandCore->getAdminURL();
	$nonce = wp_create_nonce( 'grandMedia' );

	// module folder
	$module_dir = $grandCore->gm_get_module_path( $module_folder );
	$module_ot  = array();
	if ( is_dir( $module_dir['path'] ) ) {
		include( $module_dir['path'] . '/settings.php' );
	}

	$field_values = array();
	$submit_name  = 'gmedia_module_create';
	$load_default = 1;
	$term_id      = $term_id ? intval( $term_id ) : $grandCore->_get( 'term_id', 0 );
	if ( $term_id ) {
		$load_default = 2;
		/* get current module meta data */
		if ( ! isset( $_GET['settings_default'] ) ) {
			$load_default = 0;
			$field_values = $gMDb->gmGetMetaData( 'gmedia_term', $term_id );
			if ( ! empty( $field_values ) ) {
				$field_values = array_map( array( $grandCore, 'gm_arr_o' ), $field_values );
				$field_values = array_map( 'maybe_unserialize', $field_values );
			}
			else {
				$field_values = array();
			}
		}
		$term_general = $gMDb->gmGetTerm( $term_id, 'gmedia_module', ARRAY_A );
		$field_values = array_merge( $term_general, $field_values );
		$submit_name  = 'gmedia_module_update';
	}
	include( GRAND_ABSPATH . '/inc/module.settings.php' );

	$backlink = $url['page'];
	$gm_hash  = '';
	if ( substr_count( $url['query'], 'term_id=' ) ) {
		if ( substr_count( $url['query'], 'settings_default=' ) ) {
			$backlink = remove_query_arg( 'settings_default' );
			$gm_hash  = ' gm_add_hash';
		}
	}
	else {
		$backlink = $url['page'] . '&amp;tab=modules';
	}
	?>
	<form id="gm_module_settings_form" action="<?php echo $url['page'] . '&module=' . $module_folder . ( $term_id ? '&term_id=' . $term_id : '' ); ?>" method="post">
		<div class="gMediaLibActions">
			<div class="abuts">
				<a class="gm_action_back<?php echo $gm_hash; ?>" href="<?php echo $backlink; ?>"><b>&laquo;</b> <?php _e( 'Back', 'gmLang' ); ?>
				</a>
			</div>
			<div class="abuts">
				<a href="<?php echo remove_query_arg( array( 'doing_wp_cron', '_wpnonce', 'settings_default' ) ) . '&amp;settings_default=' . rand() . '&amp;_wpnonce=' . $nonce; ?>" class="gm_action_button ui-tab-link"><?php _e( 'Load Default Settings', 'gmLang' ); ?></a>
				<span class="gm_action_button gm_action_submit"><input type="submit" name="<?php echo $submit_name; ?>" value="<?php _e( 'Save', 'gmLang' ); ?>" /></span>
			</div>
			<div class="msg0"><?php _e( 'Gallery Settings', 'gmLang' ) ?></div>
		</div>
		<div class="gmediaModuleSettings">
			<div class="gm-metabox-wrapper">
				<div class="ui-tabs">
					<?php
					/* check for sections */
					if ( isset( $module_ot['settings'] ) && count( $module_ot['settings'] ) > 0 ) {

						echo '<ul class="ui-tabs-nav">';

						/* loop through page sections */
						foreach ( $module_ot['settings'] as $key => $section ) {
							echo '<li><a href="#section_' . $key . '">' . $section['title'] . '</a></li>';
						}
						echo '</ul>';

					} ?>
					<div id="poststuff" class="metabox-holder">
						<div id="post-body">
							<div id="post-body-content">
								<?php foreach ( $module_ot['settings'] as $key => $section ) { ?>
									<div id="section_<?php echo $key; ?>" class="postbox ui-tabs-panel">
										<div class="inside">
											<?php
											/* loop through meta box fields */
											foreach ( $section['fields'] as $field ) {
												/* set default to standard value */
												if ( $load_default == 1 ) {
													$field_value = $field['std'];
												}
												else {
													$field_value = isset( $field_values[$field['id']] ) ? $field_values[$field['id']] : $field['std'];
												}
												/* build the arguments array */
												$_args = array(
													'type'           => $field['type'],
													'field_id'       => $field['id'],
													'field_name'     => $field['id'],
													'field_value'    => $field_value,
													'field_desc'     => isset( $field['desc'] ) ? $field['desc'] : '',
													'field_std'      => isset( $field['std'] ) ? $field['std'] : '',
													'field_class'    => isset( $field['class'] ) ? $field['class'] : '',
													'field_choices'  => isset( $field['choices'] ) ? $field['choices'] : array(),
													'field_settings' => isset( $field['settings'] ) && ! empty( $field['settings'] ) ? $field['settings'] : array(),
													'param'          => isset( $field['param'] ) ? $field['param'] : '',
													'term_id'        => $term_id,
													'meta'           => true
												);
												?>
												<div class="format-settings block-<?php if ( $_args['type'] == 'text' && ! empty( $_args['param'] ) ) {
													echo $_args['param'];
												}
												else {
													echo $_args['type'];
												} ?>">
													<div class="format-setting-label">
														<?php if ( in_array( $field['type'], array( 'textblock', 'query' ) ) ) { ?>
															<h3 class="label"><?php echo $field['label']; ?></h3>
														<?php }
														else { ?>
															<label for="<?php echo $_args['field_id']; ?>" class="label"><?php echo $field['label']; ?></label>
														<?php } ?>
													</div>
													<?php gm_return_func_by_type( $_args ); ?>
												</div>

											<?php } ?>
										</div>
									</div>
								<?php } ?>
							</div>
							<div class="clear"></div>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<input type="hidden" name="module_name" value="<?php echo $module_folder; ?>" />
			<input type="hidden" name="term_id" value="<?php echo $term_id; ?>" />
			<?php
			/* Use nonce for verification */
			wp_nonce_field( 'grandMedia' );
			wp_original_referer_field( true, 'previous' );
			?>
		</div>
	</form>
<?php
}
