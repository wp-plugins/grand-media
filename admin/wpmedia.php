<?php
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * grandMedia()
 *
 * @return mixed content
 */
function grandWPMedia() {
	global $gMDb, $grandCore, $grandAdmin;
	$gmOptions   = get_option( 'gmediaOptions' );

	//$gMediaURL = plugins_url(GRAND_FOLDER);
	$url        = $grandCore->getAdminURL();
	$arg        = array(
		'mime_type' => $grandCore->_get( 'mime_type', '' ),
		'orderby'   => $grandCore->_get( 'orderby', 'ID' ),
		'order'     => $grandCore->_get( 'order', '' ),
		'limit'     => $gmOptions['per_page_wpmedia'],
		'filter'    => $grandCore->_get( 'filter', '' ),
		's'         => $grandCore->_get( 's', '' )
	);
	$wpMediaLib = $gMDb->get_wp_media_lib( $arg );
	/** @var $mime_type
	 * @var  $orderby
	 * @var  $order
	 * @var  $filter
	 * @var  $s
	 */
	extract( $arg );
	$media = $mCount = array(
		'all'         => '',
		'image'       => '',
		'audio'       => '',
		'video'       => '',
		'application' => ''
	);
	if ( count( $wpMediaLib ) ) {
		foreach ( $wpMediaLib as $item ) {
			$type = explode( '/', $item->post_mime_type );
			$mCount[$type[0]] ++;
			$mCount['all'] ++;
		}
	}
	$nonce     = wp_create_nonce( 'grandMedia' );
	$gmDbCount = $gMDb->wpmediaCount( $arg );
	/** @var $counting array() */
	foreach ( $gmDbCount as $key => $value ) {
		if ( $key == 'hidden' ) {
			$counting[$key] = '<i class="qty"> (<span class="db">' . $value . '</span>)</i>';
		}
		else
			$counting[$key] = '<i class="qty"> (<span class="page">' . intval( $mCount[$key] ) . '</span><b>/</b><span class="db">' . $value . '</span>)</i>';
	}
	?>
	<?php
	$order                  = $grandCore->_get( 'order', 'ASC' );
	$sort                   = 'ASC';
	$url_param['mime_type'] = $mime_type ? '&amp;mime_type=' . $mime_type : '';
	$url_param['orderby']   = '&amp;orderby=' . $orderby;
	$url_param['order']     = '&amp;order=' . $order;
	$url_param['filter']    = $filter ? '&amp;filter=' . $filter : '';
	$url_param['s']         = $s ? '&amp;s=' . $s : '';
	$gmSelected             = isset( $_COOKIE['gmedia_wp_selected_items'] ) ? $_COOKIE['gmedia_wp_selected_items'] : '';
	?>
	<div class="gMediaLibActions">
		<div class="cb abut">
			<div class="dropbut"><input class="doaction" type="checkbox" /></div>
			<div class="dropbox">
				<span class="all"><?php _e( 'All', 'gmLang' ); ?></span>
				<span class="none"><?php _e( 'None', 'gmLang' ); ?></span>
				<span class="image"><?php _e( 'Images', 'gmLang' ); ?></span>
				<span class="audio"><?php _e( 'Audio', 'gmLang' ); ?></span>
				<span class="video"><?php _e( 'Video', 'gmLang' ); ?></span>
				<span class="reverse" title="<?php _e( 'Reverse only visible items', 'gmLang' ); ?>"><?php _e( 'Reverse', 'gmLang' ); ?></span>
			</div>
		</div>
		<div class="abuts">
			<?php $curr_mime = $grandCore->_get( 'mime_type', 'all' ); ?>
			<a class="all<?php if ( $curr_mime == 'all' ) echo ' active'; ?>" rel="all" href="<?php echo $url['page'] . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'All', 'gmLang' ); echo $counting['all']; ?></a>
			<a class="image<?php if ( $curr_mime == 'image' ) echo ' active'; if ( ! $gmDbCount['image'] ) echo ' disabled'; ?>" rel="image" href="<?php echo $url['page'] . '&amp;mime_type=image' . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'Images', 'gmLang' ); echo $counting['image']; ?></a>
			<a class="audio<?php if ( $curr_mime == 'audio' ) echo ' active'; if ( ! $gmDbCount['audio'] ) echo ' disabled'; ?>" rel="audio" href="<?php echo $url['page'] . '&amp;mime_type=audio' . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'Audio', 'gmLang' ); echo $counting['audio']; ?></a>
			<a class="video<?php if ( $curr_mime == 'video' ) echo ' active'; if ( ! $gmDbCount['video'] ) echo ' disabled'; ?>" rel="video" href="<?php echo $url['page'] . '&amp;mime_type=video' . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'Video', 'gmLang' ); echo $counting['video']; ?></a>
			<a class="application<?php if ( $curr_mime == 'application' ) echo ' active'; if ( ! $gmDbCount['application'] ) echo ' disabled'; ?>" rel="application" href="<?php echo $url['page'] . '&amp;mime_type=application' . $url_param['filter'] . $url_param['s']; ?>"><?php _e( 'Other', 'gmLang' ); echo $counting['application']; ?></a>
			<span class="delete ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#selectedForm" data-task="deleteMedia" data-confirmtxt="<?php _e( "You are about to permanently delete the selected items.\n\r'Cancel' to stop, 'OK' to delete.", "gmLang" ); ?>"><?php _e( 'Delete', 'gmLang' ); ?></span>
		</div>
		<div class="more abut">
			<div class="dropbut"><?php _e( 'Actions', 'gmLang' ); ?></div>
			<div class="dropbox">
				<?php if ( ! $filter ) { ?>
					<span class="hide ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#selectedForm" data-task="hideMedia"><?php _e( 'Hide', 'gmLang' ); ?></span>
				<?php }
				else { ?>
					<span class="unhide ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-form="#selectedForm" data-task="unhideMedia"><?php _e( 'Unhide', 'gmLang' ); ?></span>
				<?php
				}
				if ( $counting['hidden'] ) {
					if ( $filter ) {
						?>
						<a class="hidden_media active" href="<?php echo $url['page']; ?>"><?php _e( 'Hidden items', 'gmLang' ); echo $counting['hidden']; ?></a>
					<?php }
					else { ?>
						<a class="hidden_media" href="<?php echo $url['page'] . '&amp;filter=hidden'; ?>"><?php _e( 'Hidden items', 'gmLang' ); echo $counting['hidden']; ?></a>
					<?php
					}
				}
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
				<input type="hidden" id="gmSelected" data-key="wp" name="gmSelected" value="<?php echo $gmSelected; ?>" />
			</form>
			<span class="more">&raquo;</span>

			<div class="actions">
				<span id="showSelected"><?php _e( 'Show only selected items', 'gmLang' ); ?></span>
				<span id="clearSelected"><?php _e( 'Clear selected items', 'gmLang' ); ?></span>
			</div>
		</div>
		<?php echo $gMDb->queryPager(); ?>
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
			if ( count( $wpMediaLib ) ) {
				foreach ( $wpMediaLib as $item ) {
					$grandAdmin->wpMediaRow( $item );
				}
			}
			else {
				echo '<tr class="emptybd"><td colspan="8">' . __( 'No items in WordPress Media Library.', 'gmLang' ) . '</td></tr>';
			}
			?>
			<tr class="noitems">
				<td colspan="8"><?php _e( 'No results. Type another query.', 'gmLang' ); ?></td>
			</tr>
			</tbody>
		</table>
	</div>
	<?php wp_original_referer_field( true, 'previous' ); ?>
<?php
}
