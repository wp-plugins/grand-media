<?php if ( ! defined( 'GRAND_VERSION' ) ) exit( 'No direct script access allowed' );
/**
 * Functions used to build each option type.
 *
 * Builds the HTML for each of the available option types by calling those
 * function with call_user_func and passing the arguments to the second param.
 *
 * All fields are required!
 *
 * @param     array       $args The array of arguments are as follows:
 *
 * @return    string
 */
if ( ! function_exists( 'gm_return_func_by_type' ) ) {

	function gm_return_func_by_type( $args = array() ) {

		/* allow filters to be executed on the array */
		apply_filters( 'gm_return_func_by_type', $args );

		/* build the function name */
		$function_name_by_type = str_replace( '-', '_', 'gm_type_' . $args['type'] );

		/* call the function & pass in arguments array */
		if ( function_exists( $function_name_by_type ) ) {
			call_user_func( $function_name_by_type, $args );
		}
		else {
			echo '<p>' . __( 'Sorry, this function does not exist', 'gmLang' ) . '</p>';
		}

	}

}

/**
 * Query Builder.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_query_vis' ) ) {

	function gm_type_query_vis( $args = array() ) {
		global $grandAdmin;
		$nonce = wp_create_nonce( 'grandMedia' );
		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     string      $field_name  The field Name.
		 * @param     array       $field_value The query tab values.
		 * @param     string      $field_desc  The field description.
		 * @param     string      $field_class Extra CSS classes.
		 * @param     array       $param       include or exclude new option.
		 */
		extract( $args );

		if ( ! isset( $param ) || empty( $param ) || ! is_array( $param ) )
			$param = array();
		if ( ! isset( $field_value ) || empty( $field_value ) || ! is_array( $field_value ) ) {
			$field_value = array( 0 => array() );
		}
		else {
			$field_value = array_values( $field_value );
		}

		$query_defaults = array(
			'tabname'   => 'gMedia',
			'cat'       => '',
			'tag__in'   => array(),
			'author'    => '',
			'orderby'   => '',
			'order'     => 'DESC',
			'mime_type' => ''
		);

		?>

		<div class="format-setting type-textblock wide-desc">
			<div class="description"><?php echo htmlspecialchars_decode( $field_desc ); ?></div>
		</div>
		<div class="format-setting type-query <?php echo esc_attr( $field_class ); ?>">

			<div id="gMediaQuery">
				<?php $tab = 0;
				foreach ( $field_value as $tab => $value ) {
					$query_args        = array_merge( $query_defaults, $field_value[$tab], $param );
					$query_args['tab'] = $tab;
					$grandAdmin->gm_build_query_tab( $query_args );
				}
				?>
			</div>
			<?php if ( isset( $query_args['multitab'] ) && $query_args['multitab'] == 'true' ) {
				$cookie_arr = array_merge( $query_defaults, $param, array( 'tab' => $tab ) );
				update_option( 'gmediaTemp', $cookie_arr );
				?>
				<div class="gmAddTab ajaxPost" data-action="gmDoAjax" data-_ajax_nonce="<?php echo $nonce; ?>" data-task="gm-add-tab"><?php _e( 'Add Tab', 'gmLang' ); ?></div>
			<?php } ?>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
			<p>&nbsp;</p>
		</div>

	<?php
	}

}

/**
 * Query Builder.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_query' ) ) {

	function gm_type_query( $args = array() ) {
		global $gMDb, $grandCore;

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     string      $field_name  The field Name.
		 * @param     mixed       $field_value The field value is a string or an array of values.
		 * @param     string      $field_desc  The field description.
		 * @param     string      $field_class Extra CSS classes.
		 * @param     array       $param       include or exclude new option.
		 */
		extract( $args );

		if ( ! isset( $param ) || ! is_array( $param ) )
			$param = array();
		if ( ! isset( $field_value ) || ! is_array( $field_value ) )
			$field_value = array();

		$query_defaults = array(
			'author'         => array(),
			'category'       => array( 'key' => 'category__in', 'value' => array() ),
			'tag'            => array( 'key' => 'tag__in', 'value' => array() ),
			'terms_relation' => 'AND',
			'per_page'       => '20',
			'offset'         => '0',
			'orderby'        => 'ID',
			'order'          => 'DESC',
			'mime_type'      => '',
			'cat2tab'        => false
		);

		$field_value = array_merge( $query_defaults, $field_value, $param );

		if ( empty( $field_value['category']['value'] ) )
			$field_value['category']['value'] = array();
		if ( empty( $field_value['tag']['value'] ) )
			$field_value['tag']['value'] = array();

		?>

		<div class="format-setting type-textblock wide-desc">
			<div class="description"><?php echo htmlspecialchars_decode( $field_desc ); ?></div>
		</div>
		<div class="format-setting type-query <?php echo esc_attr( $field_class ); ?>">
			<div class="format-setting-query gm_query_author">
				<h4>
					<span id="author_sign" class="author_sign">
					<?php
						$author_sign = '';
						if ( count( $field_value['author'] ) && ( $field_value['author'][0] < 0 ) ) {
							$author_sign = '-';
							echo '<span class="author_hide">' . __( 'Hide', 'gmLang' ) . '</span>';
						}
						else {
							echo '<span class="author_show">' . __( 'Show', 'gmLang' ) . '</span>';
						}
						?>
					</span>
					<?php _e( 'gMedia from specific authors', 'gmLang' ); ?>
				</h4>
				<select name="gMediaQuery[author][]" id="query_author" class="gmedia-ui-multiselect query_author" multiple="multiple" size="5">
					<?php $args = array(
						'who' => 'authors'
					);
					$blogusers = get_users( $args );
					foreach ( $blogusers as $user ) {
						?>
						<option value="<?php echo $author_sign . $user->ID; ?>"<?php if ( in_array( $author_sign . $user->ID, $field_value['author'] ) ) echo ' selected="selected"'; ?>><?php echo $user->display_name; ?></option>
					<?php } ?>
				</select>
			</div>
			<?php if ( isset( $field_value['cat2tab'] ) && ! empty( $field_value['cat2tab'] ) ) { ?>
				<div class="format-setting-query gm_query_cat2tab">
					<h4><?php _e( 'Make each category as separate tab. (Multicategory Gallery)', 'gmLang' ); ?></h4>

					<p><input type="hidden" name="gMediaQuery[cat2tab]" value="no" />
						<input type="checkbox" name="gMediaQuery[cat2tab]" id="query_cat2tab" value="yes" <?php checked( $field_value['cat2tab'], 'yes' ); ?> class="gmedia-ui-checkbox <?php echo esc_attr( $field_class ); ?>" />
						<label for="query_cat2tab"><?php _e( 'Yes/No', 'gmLang' ); ?></label></p>
				</div>
			<?php } ?>
			<div class="format-setting-query gm_query_terms">
				<h4>
					<select name="gMediaQuery[category][key]" id="query_category_key" class="gmedia-ui-select query_category_key">
						<option value="cat" <?php selected( $field_value['category']['key'], 'cat' ); ?>><?php _e( 'Show gMedia from chosen categories and any children of these categories', 'gmLang' ); ?></option>
						<option value="category__and" <?php selected( $field_value['category']['key'], 'category__and' ); ?>><?php _e( 'Show gMedia that are in multiple categories', 'gmLang' ); ?></option>
						<option value="category__in" <?php selected( $field_value['category']['key'], 'category__in' ); ?>><?php _e( 'Show gMedia from either category (note this does not show gMedia from any children of these categories)', 'gmLang' ); ?></option>
						<option value="category__not_in" <?php selected( $field_value['category']['key'], 'category__not_in' ); ?>><?php _e( 'Hide gMedia from multiple categories', 'gmLang' ); ?></option>
					</select>
				</h4>
				<select name="gMediaQuery[category][value][]" id="query_category_value" class="gmedia-ui-multiselect query_category_value" multiple="multiple" size="5">
					<option value="0"<?php echo in_array( '0', $field_value['category']['value'] ) ? ' selected="selected"' : ''; ?>><?php _e( 'Uncategorized', 'gmLang' ); ?></option>
					<?php
					/* get category array */
					$type = 'gmedia_category';
					$categories = $gMDb->get_terms( $type, array( 'hide_empty' => false ) );
					$opt = '';
					if ( count( $categories ) ) {
						$children     = $gMDb->_get_term_hierarchy( $type );
						$termsHierarr = $grandCore->get_terms_hierarrhically( $type, $categories, $children, $count = 0 );
						foreach ( $termsHierarr as $termitem ) {
							$sel = in_array( $termitem->term_id, $field_value['category']['value'] ) ? ' selected="selected"' : '';
							$pad = str_repeat( '&#8212; ', max( 0, $termitem->level ) );
							$opt .= '<option' . $sel . ' value="' . $termitem->term_id . '">' . $pad . $termitem->name . '</option>' . "\n";
						}
						echo $opt;
					}
					else {
						echo '<option value="" disabled="disabled">' . __( 'No Categories Found', 'gmLang' ) . '</option>';
					}
					?>
				</select>
			</div>
			<div class="format-setting-query gm_query_terms">
				<h4>
					<select name="gMediaQuery[tag][key]" id="query_tag_key" class="gmedia-ui-select query_tag_key">
						<option value="tag__and" <?php selected( $field_value['tag']['key'], 'tag__and' ); ?>><?php _e( 'Show gMedia that are tagged with all chosen tags', 'gmLang' ); ?></option>
						<option value="tag__in" <?php selected( $field_value['tag']['key'], 'tag__in' ); ?>><?php _e( 'Show gMedia with either chosen tags', 'gmLang' ); ?></option>
						<option value="tag__not_in" <?php selected( $field_value['tag']['key'], 'tag__not_in' ); ?>><?php _e( 'Hide gMedia that do not have any of the chosen tags', 'gmLang' ); ?></option>
					</select>
				</h4>
				<select name="gMediaQuery[tag][value][]" id="query_tag_value" class="gmedia-ui-multiselect query_tag_value" multiple="multiple" size="5">
					<?php
					/* get category array */
					$type = 'gmedia_tag';
					$tags = $gMDb->get_terms( $type, array( 'hide_empty' => false ) );
					$opt = '';
					if ( count( $tags ) ) {
						foreach ( $tags as $termitem ) {
							$sel = ( in_array( $termitem->term_id, $field_value['tag']['value'] ) ) ? ' selected="selected"' : '';
							$opt .= '<option' . $sel . ' value="' . $termitem->term_id . '">' . $termitem->name . '</option>' . "\n";
						}
						echo $opt;
					}
					else {
						echo '<option value="" disabled="disabled">' . __( 'No Tags Found', 'gmLang' ) . '</option>';
					}
					?>
				</select>
			</div>
			<div class="format-setting-query gm_query_terms_relation">
				<h4><?php _e( 'The boolean relationship between the taxonomy queries', 'gmLang' ); ?></h4>
				<select name="gMediaQuery[terms_relation]" id="query_terms_relation" class="gmedia-ui-select query_terms_relation">
					<option value="AND" <?php selected( $field_value['terms_relation'], 'AND' ); ?>><?php _e( 'AND', 'gmLang' ); ?></option>
					<option value="OR" <?php selected( $field_value['terms_relation'], 'OR' ); ?>><?php _e( 'OR', 'gmLang' ); ?></option>
				</select>
			</div>
			<div class="format-setting-query gm_query_per_page">
				<h4><?php _e( 'Number of gMedia to show. Use `-1` to show all gMedia', 'gmLang' ); ?></h4>
				<input type="text" name="gMediaQuery[per_page]" id="query_per_page" class="gmedia-ui-select query_per_page" value="<?php echo $field_value['per_page']; ?>" />
			</div>
			<div class="format-setting-query gm_query_offset">
				<h4><?php _e( 'Number of gMedia to displace or pass over', 'gmLang' ); ?></h4>
				<input type="text" name="gMediaQuery[offset]" id="query_offset" class="gmedia-ui-select query_offset" value="<?php echo $field_value['offset']; ?>" />
			</div>
			<div class="format-setting-query gm_query_orderby">
				<h4><?php _e( 'Sort retrieved gMedia by parameter', 'gmLang' ); ?></h4>
				<select name="gMediaQuery[orderby]" id="query_orderby" class="gmedia-ui-select query_orderby">
					<option value="none" <?php selected( $field_value['orderby'], 'none' ); ?>><?php _e( 'No order', 'gmLang' ); ?></option>
					<option value="ID" <?php selected( $field_value['orderby'], 'ID' ); ?>><?php _e( 'Order by gMedia id', 'gmLang' ); ?></option>
					<option value="author" <?php selected( $field_value['orderby'], 'author' ); ?>><?php _e( 'Order by author', 'gmLang' ); ?></option>
					<option value="title" <?php selected( $field_value['orderby'], 'title' ); ?>><?php _e( 'Order by title', 'gmLang' ); ?></option>
					<option value="date" <?php selected( $field_value['orderby'], 'date' ); ?>><?php _e( 'Order by date', 'gmLang' ); ?></option>
					<option value="modified" <?php selected( $field_value['orderby'], 'modified' ); ?>><?php _e( 'Order by last modified date', 'gmLang' ); ?></option>
					<option value="rand" <?php selected( $field_value['orderby'], 'rand' ); ?>><?php _e( 'Random order', 'gmLang' ); ?></option>
				</select>
			</div>
			<div class="format-setting-query gm_query_order">
				<h4><?php _e( 'Designates the ascending or descending order of the `orderby` parameter', 'gmLang' ); ?></h4>
				<select name="gMediaQuery[order]" id="query_order" class="gmedia-ui-select query_order">
					<option value="ASC" <?php selected( $field_value['order'], 'ASC' ); ?>><?php _e( 'ASC', 'gmLang' ); ?></option>
					<option value="DESC" <?php selected( $field_value['order'], 'DESC' ); ?>><?php _e( 'DESC', 'gmLang' ); ?></option>
				</select>
			</div>

			<input type="hidden" name="gMediaQuery[mime_type]" id="query_mime_type" class="gmedia-ui-select query_mime_type" value="<?php echo $field_value['mime_type']; ?>" />
		</div>

	<?php
	}

}

/**
 * Text option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_text' ) ) {

	function gm_type_text( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string   $field_id    The field ID.
		 * @param     string   $field_name  The field Name.
		 * @param     mixed    $field_value The field value is a string or an array of values.
		 * @param     string   $field_desc  The field description.
		 * @param     string   $field_class Extra CSS classes.
		 * @param     mixed		 $param       Extra parameter.
		 * @param     string   $type        The field type.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		if(empty($param)){
			$param = array('type'=>$type);
		} elseif(is_array($param)) {
			if(isset($param['type']) && $param['type'] == 'number'){
				$param = wp_parse_args($param, array('min'=>'0','step'=>'1'));
			} else {
				$param['type'] = $type;
			}
		} else {
			$param = array('type'=>$param);
		}
		$params = '';
		foreach($param as $key => $val){
			$params .= esc_attr($key).'="'.esc_attr($val).'" ';
		}

		/* format setting outer wrapper */
		echo '<div class="format-setting type-text' . ( $has_desc ? ' has-desc' : ' no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* build text input */
		echo '<input ' . $params . ' name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '" value="' . esc_attr( $field_value ) . '" class="gmedia-ui-input ' . esc_attr( $field_class ) . '" />';

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Text option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_hidden' ) ) {

	function gm_type_hidden( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     mixed       $field_name  The field Name.
		 * @param     mixed       $field_value The field value.
		 * @param     string      $field_class Extra CSS classes.
		 * @param     string      $param       Extra parameter.
		 */
		extract( $args );

		/* build hidden input */
		echo '<input type="hidden" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '" value="' . esc_attr( $field_value ) . '" class="gmedia-ui-hidden ' . esc_attr( $field_class ) . '" />';

	}

}

/**
 * Select option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_select' ) ) {

	function gm_type_select( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id      The field ID.
		 * @param     string      $field_name    The field Name.
		 * @param     mixed       $field_value   The field value is a string or an array of values.
		 * @param     string      $field_desc    The field description.
		 * @param     string      $field_class   Extra CSS classes.
		 * @param     array       $field_choices The array of option choices.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-select ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* build select */
		echo '<select name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '" class="gmedia-ui-select ' . esc_attr( $field_class ) . '">';
		foreach ( (array) $field_choices as $choice ) {
			if ( isset( $choice['value'] ) && isset( $choice['label'] ) ) {
				echo '<option value="' . esc_attr( $choice['value'] ) . '"' . selected( $field_value, $choice['value'], false ) . '>' . esc_attr( $choice['label'] ) . '</option>';
			}
		}
		echo '</select>';

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Checkbox option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_checkbox' ) ) {

	function gm_type_checkbox( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id      The field ID.
		 * @param     string      $field_name    The field Name.
		 * @param     mixed       $field_value   The field value is a string or an array of values.
		 * @param     string      $field_desc    The field description.
		 * @param     string      $field_class   Extra CSS classes.
		 * @param     array       $field_choices The array of option choices.
		 */
		extract( $args );
		/* verify a description */
		$has_desc = $field_desc ? true : false;

		if ( ! is_array( $field_value ) ) {
			$field_value = $field_value ? array( $field_value ) : array();
		}

		/* format setting outer wrapper */
		echo '<div class="format-setting type-checkbox ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* build checkbox */
		foreach ( (array) $field_choices as $key => $choice ) {
			if ( isset( $choice['value'] ) && isset( $choice['label'] ) ) {
				echo '<p>';
				echo '<input type="hidden" name="checkbox[' . esc_attr( $field_name ) . '][' . esc_attr( $key ) . ']" value="" />';
				echo '<input type="checkbox" name="' . esc_attr( $field_name ) . '[' . esc_attr( $key ) . ']" id="' . esc_attr( $field_id ) . '-' . esc_attr( $key ) . '" value="' . esc_attr( $choice['value'] ) . '" ' . ( in_array( $choice['value'], $field_value ) ? 'checked="checked"' : '' ) . ' class="gmedia-ui-checkbox ' . esc_attr( $field_class ) . '" />';
				echo '<label for="' . esc_attr( $field_id ) . '-' . esc_attr( $key ) . '">' . esc_attr( $choice['label'] ) . '</label>';
				echo '</p>';
			}
		}
		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Radio option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_radio' ) ) {

	function gm_type_radio( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id      The field ID.
		 * @param     string      $field_name    The field Name.
		 * @param     mixed       $field_value   The field value is a string or an array of values.
		 * @param     string      $field_desc    The field description.
		 * @param     string      $field_class   Extra CSS classes.
		 * @param     array       $field_choices The array of option choices.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-radio ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* build radio */
		foreach ( (array) $field_choices as $key => $choice ) {
			echo '<p><input type="radio" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '-' . esc_attr( $key ) . '" value="' . esc_attr( $choice['value'] ) . '"' . checked( $field_value, $choice['value'], false ) . ' class="radio gmedia-ui-radio ' . esc_attr( $field_class ) . '" /><label for="' . esc_attr( $field_id ) . '-' . esc_attr( $key ) . '">' . esc_attr( $choice['label'] ) . '</label></p>';
		}

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Colorpicker option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_colorpicker' ) ) {

	function gm_type_colorpicker( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     string      $field_name  The field Name.
		 * @param     mixed       $field_value The field value is a string or an array of values.
		 * @param     string      $field_desc  The field description.
		 * @param     string      $field_class Extra CSS classes.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-colorpicker ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* build colorpicker */
		echo '<div class="gmedia-ui-colorpicker-input-wrap">';

		/* colorpicker JS */
		echo '<script>jQuery(document).ready(function($) { GM_UI.bind_colorpicker("' . esc_attr( $field_id ) . '"); });</script>';

		/* input */
		echo '<input type="text" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '" value="' . esc_attr( $field_value ) . '" class="gmedia-ui-input cp_input ' . esc_attr( $field_class ) . '" autocomplete="off" />';

		/* set border color */
		$border_color = in_array( $field_value, array( '#FFFFFF', '#FFF', '#ffffff', '#fff' ) ) ? '#ccc' : esc_attr( $field_value );

		echo '<div id="cp_' . esc_attr( $field_id ) . '" class="cp_box"' . ( $field_value ? " style='background-color:" . esc_attr( $field_value ) . "; border-color:$border_color;'" : '' ) . '></div>';

		echo '</div>';

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * CSS option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_css' ) ) {

	function gm_type_css( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     string      $field_name  The field Name.
		 * @param     mixed       $field_value The field value is a string or an array of values.
		 * @param     string      $field_desc  The field description.
		 * @param     string      $field_class Extra CSS classes.
		 * @param     string      $param       Extra parameter.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-css simple ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* build textarea for CSS */
		echo '<textarea class="textarea ' . esc_attr( $field_class ) . '" rows="' . esc_attr( $param ) . '" cols="40" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '">' . esc_textarea( $field_value ) . '</textarea>';

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Textarea option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_textarea' ) ) {

	function gm_type_textarea( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     string      $field_name  The field Name.
		 * @param     mixed       $field_value The field value is a string or an array of values.
		 * @param     string      $field_desc  The field description.
		 * @param     string      $field_class Extra CSS classes.
		 * @param     string      $param       Extra parameter.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-textarea ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . ' fill-area">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* build textarea */
		wp_editor(
			$field_value,
			esc_attr( $field_id ),
			array(
				'editor_class'  => esc_attr( $field_class ),
				'wpautop'       => apply_filters( 'gm_wpautop', false, $field_id ),
				'media_buttons' => false,
				'textarea_name' => esc_attr( $field_name ),
				'textarea_rows' => esc_attr( $param ),
				'tinymce'       => false,
				'quicktags'     => apply_filters( 'gm_quicktags', array( 'buttons' => 'strong,em,link,block,del,ins,ul,ol,li,code,spell,close' ), $field_id )
			)
		);

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Textarea Simple option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_textarea_simple' ) ) {

	function gm_type_textarea_simple( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     string      $field_name  The field Name.
		 * @param     mixed       $field_value The field value is a string or an array of values.
		 * @param     string      $field_desc  The field description.
		 * @param     string      $field_class Extra CSS classes.
		 * @param     string      $param       Extra parameter.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-textarea simple ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* filter to allow wpautop */
		$wpautop = apply_filters( 'gm_wpautop', false, $field_id );

		/* wpautop $field_value */
		if ( $wpautop == true )
			$field_value = wpautop( $field_value );

		/* build textarea simple */
		echo '<textarea class="textarea ' . esc_attr( $field_class ) . '" rows="' . esc_attr( $param ) . '" cols="40" name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '">' . esc_textarea( $field_value ) . '</textarea>';

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Textblock option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_textblock' ) ) {

	function gm_type_textblock( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $field_desc The field description.
		 */
		extract( $args );

		/* format setting outer wrapper */
		echo '<div class="format-setting type-textblock wide-desc">';

		/* description */
		echo '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>';

		echo '</div>';

	}

}

/**
 * Textblock Titled option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_textblock_titled' ) ) {

	function gm_type_textblock_titled( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $field_desc The field description.
		 */
		extract( $args );

		/* format setting outer wrapper */
		echo '<div class="format-setting type-textblock titled wide-desc">';

		/* description */
		echo '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>';

		echo '</div>';

	}

}

/**
 * Category Checkbox option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_category_checkbox' ) ) {

	function gm_type_category_checkbox( $args = array() ) {
		global $gMDb;

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     string      $field_name  The field Name.
		 * @param     mixed       $field_value The field value is a string or an array of values.
		 * @param     string      $field_desc  The field description.
		 * @param     string      $field_class Extra CSS classes.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-category-checkbox type-checkbox ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* get category array */
		$categories = $gMDb->get_terms( 'gmedia_category', array( 'hide_empty' => false ) );

		/* build categories */
		if ( ! empty( $categories ) ) {
			$count = 0;
			foreach ( $categories as $category ) {
				echo '<p>';
				echo '<input type="checkbox" name="' . esc_attr( $field_name ) . '[' . esc_attr( $count ) . ']" id="' . esc_attr( $field_id ) . '-' . esc_attr( $count ) . '" value="' . esc_attr( $category->term_id ) . '" ' . ( isset( $field_value[$count] ) ? checked( $field_value[$count], $category->term_id, false ) : '' ) . ' class="gmedia-ui-checkbox ' . esc_attr( $field_class ) . '" />';
				echo '<label for="' . esc_attr( $field_id ) . '-' . esc_attr( $count ) . '">' . esc_attr( $category->name ) . '</label>';
				echo '</p>';
				$count ++;
			}
		}
		else {
			echo '<p>' . __( 'No Categories Found', 'gmLang' ) . '</p>';
		}

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Category Select option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_category_select' ) ) {

	function gm_type_category_select( $args = array() ) {
		global $gMDb;

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     string      $field_name  The field Name.
		 * @param     mixed       $field_value The field value is a string or an array of values.
		 * @param     string      $field_desc  The field description.
		 * @param     string      $field_class Extra CSS classes.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-category-select ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* build category */
		echo '<select name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '" class="gmedia-ui-select ' . $field_class . '">';

		/* get category array */
		$categories = $gMDb->get_terms( 'gmedia_category', array( 'hide_empty' => false ) );

		/* has cats */
		if ( ! empty( $categories ) ) {
			echo '<option value="">-- ' . __( 'Choose One', 'gmLang' ) . ' --</option>';
			foreach ( $categories as $category ) {
				echo '<option value="' . esc_attr( $category->term_id ) . '"' . selected( $field_value, $category->term_id, false ) . '>' . esc_attr( $category->name ) . '</option>';
			}
		}
		else {
			echo '<option value="">' . __( 'No Categories Found', 'gmLang' ) . '</option>';
		}
		echo '</select>';

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Tag Checkbox option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_tag_checkbox' ) ) {

	function gm_type_tag_checkbox( $args = array() ) {
		global $gMDb;

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     string      $field_name  The field Name.
		 * @param     mixed       $field_value The field value is a string or an array of values.
		 * @param     string      $field_desc  The field description.
		 * @param     string      $field_class Extra CSS classes.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-tag-checkbox type-checkbox ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* get tags */
		$tags = $gMDb->get_terms( 'gmedia_tag', array( 'hide_empty' => false ) );

		/* has tags */
		if ( $tags ) {
			$count = 0;
			foreach ( $tags as $tag ) {
				echo '<p>';
				echo '<input type="checkbox" name="' . esc_attr( $field_name ) . '[' . esc_attr( $count ) . ']" id="' . esc_attr( $field_id ) . '-' . esc_attr( $count ) . '" value="' . esc_attr( $tag->term_id ) . '" ' . ( isset( $field_value[$count] ) ? checked( $field_value[$count], $tag->term_id, false ) : '' ) . ' class="gmedia-ui-checkbox ' . esc_attr( $field_class ) . '" />';
				echo '<label for="' . esc_attr( $field_id ) . '-' . esc_attr( $count ) . '">' . esc_attr( $tag->name ) . '</label>';
				echo '</p>';
				$count ++;
			}
		}
		else {
			echo '<p>' . __( 'No Tags Found', 'gmLang' ) . '</p>';
		}

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Tag Select option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_tag_select' ) ) {

	function gm_type_tag_select( $args = array() ) {
		global $gMDb;

		/** turns arguments array into variables
		 *
		 * @param     string      $field_id    The field ID.
		 * @param     string      $field_name  The field Name.
		 * @param     mixed       $field_value The field value is a string or an array of values.
		 * @param     string      $field_desc  The field description.
		 * @param     string      $field_class Extra CSS classes.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-tag-select ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* build tag select */
		echo '<select name="' . esc_attr( $field_name ) . '" id="' . esc_attr( $field_id ) . '" class="gmedia-ui-select ' . $field_class . '">';

		/* get tags */
		$tags = $gMDb->get_terms( 'gmedia_tag', array( 'hide_empty' => false ) );

		/* has tags */
		if ( $tags ) {
			echo '<option value="">-- ' . __( 'Choose One', 'gmLang' ) . ' --</option>';
			foreach ( $tags as $tag ) {
				echo '<option value="' . esc_attr( $tag->term_id ) . '"' . selected( $field_value, $tag->term_id, false ) . '>' . esc_attr( $tag->name ) . '</option>';
			}
		}
		else {
			echo '<option value="">' . __( 'No Tags Found', 'gmLang' ) . '</option>';
		}
		echo '</select>';

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Typography option type.
 *
 * See @gm_return_func_by_type to see the full list of available arguments.
 *
 * @param     array     $args An array of arguments.
 *
 * @return    string
 */
if ( ! function_exists( 'gm_type_typography' ) ) {

	function gm_type_typography( $args = array() ) {

		/** turns arguments array into variables
		 *
		 * @param     string      $type           Type of option.
		 * @param     string      $field_id       The field ID.
		 * @param     string      $field_name     The field Name.
		 * @param     mixed       $field_value    The field value is a string or an array of values.
		 * @param     string      $field_desc     The field description.
		 * @param     string      $field_std      The standard value.
		 * @param     string      $field_class    Extra CSS classes.
		 * @param     array       $field_choices  The array of option choices.
		 * @param     array       $field_settings The array of settings for a list item.
		 */
		extract( $args );

		/* verify a description */
		$has_desc = $field_desc ? true : false;

		/* format setting outer wrapper */
		echo '<div class="format-setting type-typography ' . ( $has_desc ? 'has-desc' : 'no-desc' ) . '">';

		/* format setting inner wrapper */
		echo '<div class="format-setting-inner">';

		/* build background colorpicker */
		echo '<div class="gmedia-ui-colorpicker-input-wrap">';

		/* colorpicker JS */
		echo '<script>jQuery(document).ready(function($) { OT_UI.bind_colorpicker("' . esc_attr( $field_id ) . '-picker"); });</script>';

		/* set background color */
		$background_color = isset( $field_value['font-color'] ) ? esc_attr( $field_value['font-color'] ) : '';

		/* set border color */
		$border_color = in_array( $background_color, array( '#FFFFFF', '#FFF', '#ffffff', '#fff' ) ) ? '#ccc' : $background_color;

		/* input */
		echo '<input type="text" name="' . esc_attr( $field_name ) . '[font-color]" id="' . esc_attr( $field_id ) . '-picker" value="' . esc_attr( $background_color ) . '" class="gmedia-ui-input cp_input ' . esc_attr( $field_class ) . '" autocomplete="off" />';

		echo '<div id="cp_' . esc_attr( $field_id ) . '-picker" class="cp_box"' . ( $background_color ? " style='background-color:$background_color; border-color:$border_color;'" : '' ) . '></div>';

		echo '</div>';

		/* build font family */
		$font_family = isset( $field_value['font-family'] ) ? esc_attr( $field_value['font-family'] ) : '';
		echo '<select name="' . esc_attr( $field_name ) . '[font-family]" id="' . esc_attr( $field_id ) . '-family" class="gmedia-ui-select ' . esc_attr( $field_class ) . '">';
		echo '<option value="">font-family</option>';
		foreach ( gm_recognized_font_families( $field_id ) as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $font_family, $key, false ) . '>' . esc_attr( $value ) . '</option>';
		}
		echo '</select>';

		/* build font style */
		$font_style = isset( $field_value['font-style'] ) ? esc_attr( $field_value['font-style'] ) : '';
		echo '<select name="' . esc_attr( $field_name ) . '[font-style]" id="' . esc_attr( $field_id ) . '-style" class="gmedia-ui-select ' . esc_attr( $field_class ) . '">';
		echo '<option value="">font-style</option>';
		foreach ( gm_recognized_font_styles( $field_id ) as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $font_style, $key, false ) . '>' . esc_attr( $value ) . '</option>';
		}
		echo '</select>';

		/* build font variant */
		$font_variant = isset( $field_value['font-variant'] ) ? esc_attr( $field_value['font-variant'] ) : '';
		echo '<select name="' . esc_attr( $field_name ) . '[font-variant]" id="' . esc_attr( $field_id ) . '-variant" class="gmedia-ui-select ' . esc_attr( $field_class ) . '">';
		echo '<option value="">font-variant</option>';
		foreach ( gm_recognized_font_variants( $field_id ) as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $font_variant, $key, false ) . '>' . esc_attr( $value ) . '</option>';
		}
		echo '</select>';

		/* build font weight */
		$font_weight = isset( $field_value['font-weight'] ) ? esc_attr( $field_value['font-weight'] ) : '';
		echo '<select name="' . esc_attr( $field_name ) . '[font-weight]" id="' . esc_attr( $field_id ) . '-weight" class="gmedia-ui-select ' . esc_attr( $field_class ) . '">';
		echo '<option value="">font-weight</option>';
		foreach ( gm_recognized_font_weights( $field_id ) as $key => $value ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $font_weight, $key, false ) . '>' . esc_attr( $value ) . '</option>';
		}
		echo '</select>';

		/* build font size */
		$font_size = isset( $field_value['font-size'] ) ? esc_attr( $field_value['font-size'] ) : '';
		echo '<select name="' . esc_attr( $field_name ) . '[font-size]" id="' . esc_attr( $field_id ) . '-size" class="gmedia-ui-select ' . esc_attr( $field_class ) . '">';
		echo '<option value="">font-size</option>';
		for ( $i = 8; $i <= 72; $i ++ ) {
			$size = $i . 'px';
			echo '<option value="' . esc_attr( $size ) . '" ' . selected( $font_size, $size, false ) . '>' . esc_attr( $size ) . '</option>';
		}
		echo '</select>';

		echo '</div>';

		/* description */
		echo $has_desc ? '<div class="description">' . htmlspecialchars_decode( $field_desc ) . '</div>' : '';

		echo '</div>';

	}

}

/**
 * Recognized font styles.
 * Returns an array of all recognized font styles.
 *
 * @uses      apply_filters()
 *
 * @return    array
 */
if ( ! function_exists( 'gm_recognized_font_styles' ) ) {

	function gm_recognized_font_styles( $field_id = '' ) {

		return apply_filters( 'gm_recognized_font_styles', array(
			'normal'  => 'Normal',
			'italic'  => 'Italic',
			'oblique' => 'Oblique',
			'inherit' => 'Inherit'
		), $field_id );

	}

}

/**
 * Recognized font weights.
 * Returns an array of all recognized font weights.
 *
 * @uses      apply_filters()
 *
 * @return    array
 */
if ( ! function_exists( 'gm_recognized_font_weights' ) ) {

	function gm_recognized_font_weights( $field_id = '' ) {

		return apply_filters( 'gm_recognized_font_weights', array(
			'normal'  => 'Normal',
			'bold'    => 'Bold',
			'bolder'  => 'Bolder',
			'lighter' => 'Lighter',
			'100'     => '100',
			'200'     => '200',
			'300'     => '300',
			'400'     => '400',
			'500'     => '500',
			'600'     => '600',
			'700'     => '700',
			'800'     => '800',
			'900'     => '900',
			'inherit' => 'Inherit'
		), $field_id );

	}

}

/**
 * Recognized font variants.
 * Returns an array of all recognized font variants.
 *
 * @uses      apply_filters()
 *
 * @return    array
 */
if ( ! function_exists( 'gm_recognized_font_variants' ) ) {

	function gm_recognized_font_variants( $field_id = '' ) {

		return apply_filters( 'gm_recognized_font_variants', array(
			'normal'     => 'Normal',
			'small-caps' => 'Small Caps',
			'inherit'    => 'Inherit'
		), $field_id );

	}

}

/**
 * Recognized font families.
 * Returns an array of all recognized font families.
 * Keys are intended to be stored in the database
 * while values are ready for display in html.
 *
 * @uses      apply_filters()
 *
 * @return    array
 */
if ( ! function_exists( 'gm_recognized_font_families' ) ) {

	function gm_recognized_font_families( $field_id = '' ) {

		return apply_filters( 'gm_recognized_font_families', array(
			'arial'     => 'Arial',
			'georgia'   => 'Georgia',
			'helvetica' => 'Helvetica',
			'palatino'  => 'Palatino',
			'tahoma'    => 'Tahoma',
			'times'     => '"Times New Roman", sans-serif',
			'trebuchet' => 'Trebuchet',
			'verdana'   => 'Verdana'
		), $field_id );

	}

}

/**
 * Measurement Units.
 * Returns an array of all available unit types.
 *
 * @uses      apply_filters()
 *
 * @return    array
 */
if ( ! function_exists( 'gm_measurement_unit_types' ) ) {

	function gm_measurement_unit_types( $field_id = '' ) {

		return apply_filters( 'gm_measurement_unit_types', array(
			'px' => 'px',
			'%'  => '%',
			'em' => 'em',
			'pt' => 'pt'
		), $field_id );

	}

}

