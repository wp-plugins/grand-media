<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * grandDashboard()
 *
 * @return mixed content
 */
function gmSettings() {
	global $grandCore;
	/*
	include_once( dirname( dirname( __FILE__ ) ) . '/setup.php' );
	$grandOptions = grand_default_options();
	update_option( 'gmediaOptions', $grandOptions );
	*/
	$gmOptions = get_option( 'gmediaOptions' );
	$url = $grandCore->get_admin_url();
	$nonce = wp_create_nonce( 'grandMedia' );

	?>
	<div id="gmedia_settings"><form id="gmedia_settings_form" action="<?php echo $url['page']; ?>" method="post">
		<div class="gMediaLibActions">
			<div class="abuts">
				<a href="#" class="gm_action_button"><?php _e( 'Reset to Default Settings', 'gmLang' ); ?></a>
				<span class="gm_action_button gm_action_submit"><input type="submit" name="gmedia_settings_save" value="<?php _e( 'Save', 'gmLang' ); ?>" /></span>
			</div>
			<div class="msg0"><?php _e( 'Gmedia Global Settings', 'gmLang' ) ?></div>
		</div>
		<div class="gmediaSettings">
			<div class="gm-metabox-wrapper">
				<div class="ui-tabs">
					<ul class="ui-tabs-nav">
						<li><a href="#section_general"><?php _e( 'General', 'gmLang' ) ?></a></li>
						<li><a href="#section_other"><?php _e( 'Other', 'gmLang' ) ?></a></li>
					</ul>
					<div id="poststuff" class="metabox-holder">
						<div id="post-body">
							<div id="post-body-content">

								<div id="section_general" class="postbox ui-tabs-panel">
									<div class="format-settings block-text">
										<label for="gmedia_key" class="format-setting-label" id="gmedia_key_label"><?php _e( 'License Key', 'gmLang' ) ?><span>: <i><?php if(isset($gmOptions['product_name'])){ echo $gmOptions['product_name']; } ?></i></span></label>
										<div class="format-setting type-text wide-desc">
											<div class="format-setting-inner" id="gmedia_license"><input type="text" name="set[gmedia_key]" id="gmedia_key" value="<?php if(isset($gmOptions['gmedia_key'])){ echo $gmOptions['gmedia_key']; } ?>" class="gmedia-ui-input" /></div>
											<input type="hidden" value="<?php if(isset($gmOptions['product_name'])){ echo $gmOptions['product_name']; } ?>" name="set[product_name]" id="product_name" />
											<input type="hidden" value="<?php if(isset($gmOptions['gmedia_key2'])){ echo $gmOptions['gmedia_key2']; } ?>" name="set[gmedia_key2]" id="gmedia_key2" />
											<span class="hide-if-no-js button button-green ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#gmedia_license" data-task="gm-get-key"><?php _e( 'Activate Key', 'gmLang' ) ?></span>
											<div class="description"><?php _e('Enter License Key to remove backlink label from premium gallery modules.') ?></div>
											<div id="console"></div>
										</div>
									</div>
								</div>

								<div id="section_other" class="postbox ui-tabs-panel"><?php _e( 'Under Development', 'gmLang' ) ?></div>

							</div>
							<div class="clear"></div>
						</div>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<?php
			/* Use nonce for verification */
			wp_nonce_field( 'grandMedia' );
			wp_original_referer_field( true, 'previous' );
			?>
		</div>
	</form></div>
<?php
}