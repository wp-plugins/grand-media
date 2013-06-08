<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * grandMedia()
 *
 * @return mixed content
 */
function grandMedia() {
	global $gMDb, $grandCore, $grandAdmin;

	$gmOptions   = get_option( 'gmediaOptions' );
	$gMediaURL = WP_PLUGIN_URL . '/' . GRAND_FOLDER . '/';
	$url       = $grandCore->get_admin_url();
	if ( isset( $_POST['selected_items'] ) ) {
		$sel_ids = explode( ',', $_POST['selected_items'] );
		$sel_ids = array_filter( $sel_ids, 'is_numeric' );
	}
	elseif ( isset( $_REQUEST['filter'] ) && $_REQUEST['filter'] == 'selected' && isset( $_COOKIE['gmedia_gm_selected_items'] ) ) {
		$sel_ids = explode( ',', $_COOKIE['gmedia_gm_selected_items'] );
		$sel_ids = array_filter( $sel_ids, 'is_numeric' );
	}
	else {
		$sel_ids = array();
	}
	$arg       = array(
		'mime_type'  => $grandCore->_get( 'mime_type', '' ),
		'orderby'    => $grandCore->_get( 'orderby', '' ),
		'order'      => $grandCore->_get( 'order', '' ),
		'per_page'   => $gmOptions['per_page_gmedia'],
		'page'       => $grandCore->_get( 'pager', 1 ),
		'tag_id'     => $grandCore->_get( 'tag_id', '' ),
		'cat'        => $grandCore->_get( 'cat', '' ),
		'gmedia__in' => $sel_ids,
		's'          => $grandCore->_get( 's', '' )
	);
	$gMediaLib = $gMDb->get_gmedias( $arg );
	//echo '<pre>'; print_r($gMediaLib); echo '</pre>';
	/** @var $mime_type
	 * @var  $orderby
	 * @var  $order
	 * @var  $per_page
	 * @var  $page
	 * @var  $gmedia__in
	 * @var  $s
	 */
	extract( $arg );
	$media = $mCount = array(
		'total'       => '',
		'image'       => '',
		'audio'       => '',
		'video'       => '',
		'application' => ''
	);
	if ( count( $gMediaLib ) ) {
		foreach ( $gMediaLib as $item ) {
			$type = explode( '/', $item->mime_type );
			$mCount[$type[0]] ++;
			$mCount['total'] ++;
		}
	}
	$nonce     = wp_create_nonce( 'grandMedia' );
	$gmDbCount = $gMDb->count_gmedia();
	//echo '<pre>'; print_r($gmDbCount); echo '</pre>';
	/** @var $counting array() */
	foreach ( $gmDbCount as $key => $value ) {
		$counting[$key] = '<i class="qty"> (<span class="page">' . intval( $mCount[$key] ) . '</span><b>/</b><span class="db">' . $value . '</span>)</i>';
	}
	$order                  = $order ? $order : 'ASC';
	$orderby                = $orderby ? $orderby : 'ID';
	$sort                   = 'ASC';
	$url_param['mime_type'] = $mime_type ? '&amp;mime_type=' . $mime_type : '';
	$url_param['orderby']   = '&amp;orderby=' . $orderby;
	$url_param['order']     = '&amp;order=' . $order;
	$url_param['filter']    = $grandCore->_get( 'filter' ) ? '&amp;filter=' . $_GET['filter'] : '';
	$url_param['s']         = $s ? '&amp;s=' . $s : '';
	$gmSelected             = isset( $_COOKIE['gmedia_gm_selected_items'] ) ? $_COOKIE['gmedia_gm_selected_items'] : '';
	?>
	<div class="gMediaLibActions">
		<div class="cb abut">
			<div class="dropbut"><input class="doaction" type="checkbox" /></div>
			<div class="dropbox">
				<span class="total"><?php _e( 'All', 'gmLang' ); ?></span>
				<span class="none"><?php _e( 'None', 'gmLang' ); ?></span>
				<span class="image"><?php _e( 'Images', 'gmLang' ); ?></span>
				<span class="audio"><?php _e( 'Audio', 'gmLang' ); ?></span>
				<span class="video"><?php _e( 'Video', 'gmLang' ); ?></span>
				<span class="reverse" title="<?php _e( 'Reverse only visible items', 'gmLang' ); ?>"><?php _e( 'Reverse', 'gmLang' ); ?></span>
			</div>
		</div>
		<div class="abuts">
			<?php $curr_mime = $grandCore->_get( 'mime_type', 'total' ); ?>
			<a class="total<?php if ( $curr_mime == 'total' ) echo ' active'; ?>" rel="total" href="<?php echo $url['page'] . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'All', 'gmLang' ); echo $counting['total']; ?></a>
			<a class="image<?php if ( $curr_mime == 'image' ) echo ' active'; if ( ! $gmDbCount['image'] ) echo ' disabled'; ?>" rel="image" href="<?php echo $url['page'] . '&amp;mime_type=image' . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'Images', 'gmLang' ); echo $counting['image']; ?></a>
			<a class="audio<?php if ( $curr_mime == 'audio' ) echo ' active'; if ( ! $gmDbCount['audio'] ) echo ' disabled'; ?>" rel="audio" href="<?php echo $url['page'] . '&amp;mime_type=audio' . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'Audio', 'gmLang' ); echo $counting['audio']; ?></a>
			<a class="video<?php if ( $curr_mime == 'video' ) echo ' active'; if ( ! $gmDbCount['video'] ) echo ' disabled'; ?>" rel="video" href="<?php echo $url['page'] . '&amp;mime_type=video' . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'Video', 'gmLang' ); echo $counting['video']; ?></a>
			<a class="application<?php if ( $curr_mime == 'application' ) echo ' active'; if ( ! $gmDbCount['application'] ) echo ' disabled'; ?>" rel="application" href="<?php echo $url['page'] . '&amp;mime_type=application' . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'Other', 'gmLang' ); echo $counting['application']; ?></a>
			<span class="delete ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#selectedForm" data-task="gmedia-bulk-delete" data-confirmtxt="<?php _e( "You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang" ); ?>"><?php _e( 'Delete', 'gmLang' ); ?></span>
		</div>
		<div class="more abut">
			<div class="dropbut"><?php _e( 'Category', 'gmLang' ); ?></div>
			<div class="dropbox">
				<strong class="label"><?php _e( 'Move to / Open category', 'gmLang' ); ?>:</strong>

				<div id="category_list" class="term_list">
					<?php
					$gmTerms = $gMDb->get_terms( 'gmedia_category' );
					$terms = '';
					if ( count( $gmTerms ) ) {
						$children     = $gMDb->_get_term_hierarchy( 'gmedia_category' );
						$termsHierarr = $grandCore->get_terms_hierarrhically( 'gmedia_category', $gmTerms, $children, $count = 0 );
						$terms .= '<div class="item"><span class="ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-form="#selectedForm" data-task="moveToCategory" data-term_id="0">' . __( 'No Category', 'gmLang' ) . '</span>';
						$terms .= '<a class="opencat" href="' . $url['page'] . '&amp;cat=0" title="' . __( 'Show gMedia with no category', 'gmLang' ) . '">' . __( 'Show gMedia with no category', 'gmLang' ) . '</a></div>' . "\n";
						foreach ( $termsHierarr as $termitem ) {
							$pad = str_repeat( '&#8212; ', max( 0, $termitem->level ) );
							$terms .= '<div class="item">';
							$terms .= '<span class="ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-form="#selectedForm" data-task="moveToCategory" data-term_id="' . $termitem->term_id . '">' . $pad . $termitem->name . '</span>';
							$terms .= '<a class="openterm" href="' . $url['page'] . '&amp;cat=' . $termitem->term_id . '" title="' . __( 'Show this category', 'gmLang' ) . '">' . __( 'Show this category', 'gmLang' ) . '</a>';
							$terms .= '</div>' . "\n";
						}
					}
					else {
						$terms = '<a href="' . admin_url( 'admin.php?page=GrandMedia_Tags_and_Categories&tab=gmedia_category' ) . '">' . __( 'Create category', 'gmLang' ) . '</a>';
					}
					echo $terms;
					?>
				</div>
			</div>
		</div>
		<div class="more abut">
			<div class="dropbut"><?php _e( 'Labels', 'gmLang' ); ?></div>
			<div class="dropbox">
				<strong class="label"><?php _e( 'Add new label', 'gmLang' ); ?>:</strong>

				<div class="inp"><input id="new_label" class="dropchild" type="text" name="label" autocomplete="off" />
					<span class="button ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#selectedForm,#new_label" data-task="gm-add-label" title="<?php _e( 'Add labels to selected gmedia', 'gmLang' ); ?>"><?php _e( 'Add', 'gmLang' ); ?></span>
				</div>
				<hr />

				<?php
				$gmTerms = $gMDb->get_terms( 'gmedia_tag' );
				if ( count( $gmTerms ) ) {
					$terms = '<form id="tag_list" name="tag_list" action="" method="post"><div class="term_list">' . "\n";
					foreach ( $gmTerms as $termitem ) {
						$terms .= '	<div class="item">';
						$terms .= '		<span class="dropchild"><input type="checkbox" name="label[]" id="l_ch_' . $termitem->term_id . '" value="' . $termitem->term_id . '" /> <label for="l_ch_' . $termitem->term_id . '">' . $termitem->name . '</label></span>';
						$terms .= '		<a class="openterm" href="' . $url['page'] . '&amp;tag_id=' . $termitem->term_id . '" title="' . __( 'Show gmedia with this label', 'gmLang' ) . '">' . __( 'Show gmedia with this label', 'gmLang' ) . '</a>';
						$terms .= '	</div>' . "\n";
					}
					$terms .= '</div>' . "\n";
					$terms .= '<div class="buttons floatholder">';
					$terms .= '	<span class="button alignleft removeLabels ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-form="#selectedForm,#tag_list" data-task="gm-remove-label" title="' . __( 'Remove labels from selected gmedia', 'gmLang' ) . '">' . __( 'Remove', 'gmLang' ) . '</span>';
					$terms .= '	<span class="button alignright addLabels ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="' . $nonce . '" data-form="#selectedForm,#tag_list" data-task="gm-add-label" title="' . __( 'Add labels to selected gmedia', 'gmLang' ) . '">' . __( 'Add', 'gmLang' ) . '</span>';
					$terms .= '</div></form>' . "\n";
				}
				echo $terms;
				?>
			</div>
		</div>
		<div class="msg">
			<span id="selectedItems"><span class="selectedItems"><?php if ( ! empty( $gmSelected ) ) {
						echo count( explode( ',', $gmSelected ) );
					}
					else {
						echo '0';
					} ?></span> <?php _e( 'selected', 'gmLang' ); ?></span>

			<form id="selectedForm" name="selectedForm" style="display: none;" action="<?php echo $url['page'] . '&amp;filter=selected'; ?>" method="post">
				<input type="hidden" id="gmSelected" data-key="gm" name="gmSelected" value="<?php echo $gmSelected; ?>" />
			</form>
			<!--suppress CheckDtdRefs -->
			<span class="more">&raquo;</span>

			<div class="actions">
				<span id="showSelected"><?php _e( 'Show only selected items', 'gmLang' ); ?></span>
				<span id="clearSelected"><?php _e( 'Clear selected items', 'gmLang' ); ?></span>
			</div>
		</div>
		<?php echo $gMDb->query_pager(); ?>
		<form action="" method="get">
			<div class="gmSearch">
				<?php foreach ( $_GET as $key => $value ) {
					if ( in_array( $key, array( 's', 'pager' ) ) ) continue; ?>
					<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value; ?>" />
				<?php } ?>
				<span class="loading">Loading... </span>
				<input id="gMediaLibSearch" type="search" name="s" placeholder="<?php _e( 'Search...', 'gmLang' ); ?>" value="<?php echo $grandCore->_get( 's', '' ); ?>" />
			</div>
		</form>
	</div>
	<form name="gMediaForm" id="gMediaForm" method="post" action="">
		<div id="gMediaLibTable">
			<table class="gMediaLibTable" cellspacing="0">
				<col class="bufer" />
				<col class="cb" />
				<col class="id" />
				<col class="file" />
				<col class="type" />
				<col class="title" />
				<col class="descr" />
				<col class="actions" />
				<thead>
				<tr>
					<th class="bufer"><span></span></th>
					<th class="cb"><span>#</span></th>
					<th class="id <?php if ( $orderby == 'ID' ) {
						echo $sort = $grandCore->_get( 'order', 'DESC' );
						$sort = ( $sort == 'DESC' ) ? 'ASC' : 'DESC';
					} ?>">
						<a href="<?php echo $url['page'] . $url_param['mime_type'] . '&amp;orderby=ID&amp;order=' . $sort . $url_param['filter'] . $url_param['s']; $sort = 'ASC'; ?>"><?php _e( 'ID', 'gmLang' ); ?></a>
					</th>
					<th class="file <?php if ( $orderby == 'filename' ) {
						echo $order;
						$sort = ( $order == 'DESC' ) ? 'ASC' : 'DESC';
					} ?>" title="<?php _e( 'Sort by filename', 'gmLang' ); ?>">
						<a href="<?php echo $url['page'] . $url_param['mime_type'] . '&amp;orderby=filename&amp;order=' . $sort . $url_param['filter'] . $url_param['s']; $sort = 'ASC'; ?>"><?php _e( 'File', 'gmLang' ); ?></a>
					</th>
					<th class="type"><span><?php _e( 'Type', 'gmLang' ); ?></span></th>
					<th class="title <?php if ( $orderby == 'title' ) {
						echo $order;
						$sort = ( $order == 'DESC' ) ? 'ASC' : 'DESC';
					} ?>">
						<a href="<?php echo $url['page'] . $url_param['mime_type'] . '&amp;orderby=title&amp;order=' . $sort . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'Title', 'gmLang' ); ?></a>
					</th>
					<th class="descr"><span><?php _e( 'Description', 'gmLang' ); ?></span></th>
					<th class="actions"><span><?php _e( 'Actions', 'gmLang' ); ?></span></th>
				</tr>
				</thead>
				<tbody class="gmLib">
				<?php
				if ( count( $gMediaLib ) ) {
					foreach ( $gMediaLib as $item ) {
						$grandAdmin->gMediaRow( $item );
					}
				}
				else {
					echo '<tr class="emptybd"><td colspan="8">' . __( 'No items in Gmedia Library.', 'gmLang' ) . '</td></tr>';
				}
				?>
				<tr class="noitems">
					<td colspan="8"><?php _e( 'No results. Type another query.', 'gmLang' ); ?></td>
				</tr>
				</tbody>
			</table>
		</div>
		<?php wp_original_referer_field( true, 'previous' ); ?>
	</form>
<?php
}
