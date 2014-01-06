<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * gmTagsCategories()
 *
 * @return mixed content
 */
function gmTagsCategories() {
	global $gMDb, $grandCore, $grandAdmin;

	$gMediaURL = WP_PLUGIN_URL . '/' . GRAND_FOLDER . '/';
	$url       = $grandCore->get_admin_url();
	$arg       = array(
		'orderby'    => $grandCore->_get( 'orderby', 'name' ),
		'order'      => $grandCore->_get( 'order', 'ASC' ),
		'search'     => $grandCore->_get( 's', '' ),
		'include'    => $grandCore->_req( 'gmSelected', '' ),
		'number'     => 0,
		'hide_empty' => 0,
		'page'       => 1,
	);
	/** @var $orderby
	 * @var  $order
	 * @var  $search
	 * @var  $include
	 * @var  $page
	 * @var  $number
	 * @var  $hide_empty
	 */
	extract( $arg );
	$arg['offset'] = $offset = ( $page - 1 ) * $number;

	$taxonomy    = $grandCore->_get( 'tab', 'gmedia_tag' );
	$gMediaTerms = $gMDb->get_terms( $taxonomy, $arg );


	/** @var $orderby
	 * @var  $order
	 * @var  $search
	 * @var  $include
	 */
	extract( $arg );

	$gmOptions = get_option( 'gmediaOptions' );
	if ( isset( $gmOptions['taxonomies'][$taxonomy]['hierarchical'] ) )
		$children = $gMDb->_get_term_hierarchy( $taxonomy );
	else
		$children = array();

	$nonce                = wp_create_nonce( 'grandMedia' );
	$order                = $grandCore->_get( 'order', 'ASC' );
	$sort                 = 'ASC';
	$url_param['tab']     = '&amp;tab=';
	$url_param['orderby'] = '&amp;orderby=' . $orderby;
	$url_param['order']   = '&amp;order=' . $order;
	$url_param['s']       = $search ? '&amp;s=' . $search : '';
	$url_param['filter']  = $include ? '&amp;gmSelected=' . $include : '';
	$gmSelected           = isset( $_COOKIE['gmedia_' . $taxonomy . '_selected_items'] ) ? $_COOKIE['gmedia_' . $taxonomy . '_selected_items'] : '';
	?>
	<script type="text/javascript">play_with_page = true;</script>
	<div class="gMediaLibActions floatholdviz">
		<div class="gm-searchdiv">
			<form action="" method="get">
				<div class="gmSearch">
					<?php foreach ( $_GET as $key => $value ) {
						if ( in_array( $key, array( 's' ) ) ) continue; ?>
						<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
					<?php } ?>
					<span class="loading">Loading... </span>
					<input id="gMediaLibSearch" type="search" name="s" autocomplete="off" placeholder="<?php _e( 'Search...', 'gmLang' ); ?>" value="<?php echo $grandCore->_get( 's', '' ); ?>" />
					<span class="resetSearch" style="display: none;">reset</span>
				</div>
			</form>
		</div>
		<div class="gm-buttonsdiv">
			<div class="cb abut">
				<div class="dropbut"><input class="doaction" type="checkbox" /></div>
				<div class="dropbox">
					<span class="all"><?php _e( 'All', 'gmLang' ); ?></span>
					<span class="none"><?php _e( 'None', 'gmLang' ); ?></span>
					<span class="reverse" title="<?php _e( 'Reverse only visible items', 'gmLang' ); ?>"><?php _e( 'Reverse', 'gmLang' ); ?></span>
				</div>
			</div>
			<div class="abuts">
				<a class="gmTags<?php if ( $taxonomy == 'gmedia_tag' ) echo ' active'; ?>" rel="gmTags" href="<?php echo $url['page'] . $url_param['tab'] . 'gmedia_tag'; ?>"><?php _e( 'Tags', 'gmLang' ); ?></a>
				<a class="gmCategories<?php if ( $taxonomy == 'gmedia_category' ) echo ' active'; ?>" rel="gmCategories" href="<?php echo $url['page'] . $url_param['tab'] . 'gmedia_category'; ?>"><?php _e( 'Categories', 'gmLang' ); ?></a>
			</div>
			<div class="more abut">
				<div class="dropbut"><?php _e( 'Actions', 'gmLang' ); ?></div>
				<div class="dropbox">
					<span class="delete ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#selectedForm" data-tax="<?php echo $taxonomy; ?>" data-task="terms-delete" data-confirmtxt="<?php _e( "You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang" ); ?>"><?php _e( 'Delete selected', 'gmLang' ); ?></span>
				</div>
			</div>
			<div class="msg">
				<span id="selectedItems"><span class="selectedItems"><?php if ( ! empty( $include ) ) {
							echo count( explode( ',', $include ) );
						}
						else {
							echo '0';
						} ?></span> <?php _e( 'selected', 'gmLang' ); ?></span>

				<form id="selectedForm" name="selectedForm" style="display: none;" action="<?php echo $url['page'] . $url_param['tab'] . $taxonomy . '&amp;filter=selected'; ?>" method="post">
					<input type="hidden" id="gmSelected" name="gmSelected" data-key="<?php echo $taxonomy; ?>" value="<?php echo $gmSelected; ?>" />
				</form>
				<span class="more">&raquo;</span>

				<div class="actions">
					<span id="showSelected"><?php _e( 'Show only selected items', 'gmLang' ); ?></span>
					<span id="clearSelected"><?php _e( 'Clear selected items', 'gmLang' ); ?></span>
				</div>
			</div>
		</div>
	</div>
	<div id="gMediaLibTable" class="<?php echo $taxonomy; ?>">
		<?php if ( $taxonomy == 'gmedia_tag' ) { ?>
			<form method="post" action="" id="gmAddTerms" name="gmAddTerms">
				<fieldset class="floatholder tagform">
					<legend><?php _e( 'Add tags', 'gmLang' ); ?>
						<span class="howto"><?php _e( 'Separate tags with commas', 'gmLang' ); ?></span></legend>
					<textarea id="tax-input-gmedia_tag" class="the-tags" cols="20" rows="3" name="terms[<?php echo $taxonomy; ?>]"></textarea>
					<?php wp_nonce_field( 'grandMedia' ); ?>
					<input type="submit" value="<?php _e( 'Add', 'gmLang' ); ?>" name="addterms" class="button tagadd">
				</fieldset>
			</form>
		<?php
		}
		if ( $taxonomy == 'gmedia_category' ) {
			?>
			<form method="post" action="" id="gmAddTerms" name="gmAddTerms">
				<fieldset class="floatholder categoryform">
					<legend><?php _e( 'Add category', 'gmLang' ); ?></legend>
					<div class="set">
						<label for="tax-input-gmedia_category"><?php _e( 'Name', 'gmLang' ); ?></label>
						<input type="text" id="tax-input-gmedia_category" class="the-category" name="terms[<?php echo $taxonomy; ?>]" autocomplete="off" value=""<?php $grandCore->qTip( __( "The name is how it appears on your site.", "gmLang" ) ); ?> />
						<hr class="spacer" />
						<label for="tax-input-gm_term_global"><?php _e( 'Parent', 'gmLang' ); ?></label>
						<select id="tax-input-gm_term_global" class="the-category-global" name="gm_term_global"<?php $grandCore->qTip( __( "Categories, unlike tags, can have a hierarchy. You might have a Backgrounds category, and under that have children categories for Abstract and Vintage. Totally optional.", "gmLang" ) ); ?>>
							<option value="0" selected="selected"><?php _e( 'None', 'gmLang' ); ?></option>
							<?php $gmAllTerms = $gMDb->get_terms( $taxonomy );
							if ( count( $gmAllTerms ) ) {
								$termsHierarr = $grandCore->get_terms_hierarrhically( $taxonomy, $gmAllTerms, $children, $count = 0 );
								foreach ( $termsHierarr as $termitem ) {
									if(intval($termitem->level) > 0)
										continue;

									$pad = str_repeat( '&#8212; ', max( 0, $termitem->level ) ); ?>
									<option value="<?php echo $termitem->term_id; ?>"><?php echo $pad . $termitem->name; ?></option>
								<?php
								}
							} ?>
						</select>
					</div>
					<div class="set liq">
						<label for="tax-input-gm_term_description"><?php _e( 'Description', 'gmLang' ); ?></label>
						<textarea id="tax-input-gm_term_description" class="the-category-description" cols="20" rows="3" name="gm_term_description"<?php $grandCore->qTip( __( "The description is not prominent by default; however, some themes may show it.", "gmLang" ) ); ?>></textarea>
					</div>
					<?php wp_nonce_field( 'grandMedia' ); ?>
					<input type="submit" value="<?php _e( 'Add', 'gmLang' ); ?>" name="addterms" class="button categoryadd">
				</fieldset>
			</form>
		<?php } ?>

		<table class="gMediaLibTable" cellspacing="0">
			<col class="bufer" />
			<col class="cb" />
			<col class="id" />
			<col class="name" />
			<col class="descr" />
			<col class="count" />
			<col class="actions" />
			<thead>
			<tr>
				<th class="bufer"><span></span></th>
				<th class="cb"><span>#</span></th>
				<th class="id <?php if ( $orderby == 'ID' ) {
					echo $sort = $grandCore->_get( 'order', 'DESC' );
					$sort = ( $sort == 'DESC' ) ? 'ASC' : 'DESC';
				} ?>">
					<a href="<?php echo $url['page'] . $url_param['tab'] . $taxonomy . '&amp;orderby=ID&amp;order=' . $sort . $url_param['filter'] . $url_param['s']; $sort = 'ASC'; ?>"><?php _e( 'ID', 'gmLang' ); ?></a>
				</th>
				<th class="name <?php if ( $orderby == 'name' ) {
					echo $order;
					$sort = ( $order == 'DESC' ) ? 'ASC' : 'DESC';
				} ?>" title="<?php _e( 'Sort by name', 'gmLang' ); ?>">
					<a href="<?php echo $url['page'] . $url_param['tab'] . $taxonomy . '&amp;orderby=name&amp;order=' . $sort . $url_param['filter'] . $url_param['s']; $sort = 'ASC'; ?>"><?php _e( 'Name', 'gmLang' ); ?></a>
				</th>
				<th class="descr"><span><?php _e( 'Description', 'gmLang' ); ?></span></th>
				<th class="count <?php if ( $orderby == 'count' ) {
					echo $order;
					$sort = ( $order == 'DESC' ) ? 'ASC' : 'DESC';
				} ?>">
					<a href="<?php echo $url['page'] . $url_param['tab'] . $taxonomy . '&amp;orderby=count&amp;order=' . $sort . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'Count', 'gmLang' ); ?></a>
				</th>
				<th class="actions"><span><?php _e( 'Actions', 'gmLang' ); ?></span></th>
			</tr>
			</thead>
			<tbody class="gmLib">
			<?php
			if ( count( $gMediaTerms ) ) {
				$filter       = ( empty( $_GET['s'] ) && empty( $_REQUEST['gmSelected'] ) ) ? false : true;
				$count        = 0;
				$termsHierarr = $grandCore->get_terms_hierarrhically( $taxonomy, $gMediaTerms, $children, $count, $offset, $number, 0, 0, $filter );
				foreach ( $termsHierarr as $termitem ) {
					$grandAdmin->gm_term_row( $termitem );
				}
			}
			else {
				echo '<tr class="emptydb"><td colspan="7">' . __( 'No terms in Gmedia Library.', 'gmLang' ) . '</td></tr>';
			}
			?>
			<tr class="noitems">
				<td colspan="7"><?php _e( 'No results. Type another query.', 'gmLang' ); ?></td>
			</tr>
			</tbody>
		</table>
		<?php wp_original_referer_field( true, 'previous' ); ?>
	</div>
<?php
}
