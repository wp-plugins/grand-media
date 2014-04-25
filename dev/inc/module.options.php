<?php if ( ! defined( 'GMEDIA_VERSION' ) ) exit( 'No direct script access allowed' );

function gmedia_gallery_options_nav($options_tree){
	$i = 0;
	foreach($options_tree as $section){
		$i++;
		echo '<li><a href="#gallery_settings'.$i.'" data-toggle="tab">'.$section['label'].'</a></li>';
	}
}

function gmedia_gallery_options_fieldset($options_tree, $default, $value = array()){
	$i = 0;
	foreach($options_tree as $section){
		$i++;
		?>
		<fieldset id="gallery_settings<?php echo $i; ?>" class="tab-pane">
		<?php
			foreach($section['fields'] as $name => $field){
				if(isset($value[$name])){
					$val = $value[$name];
				} else{
					$val = false;
				}
				gmedia_gallery_options_formgroup($name, $field, $default[$name], $val);
			}
		?>
		</fieldset>
		<?php
	}
}

function gmedia_gallery_options_formgroup($name, $field, $def, $val){
	if(false === $val){
		//$val = $def;
	}
	if('input' == $field['tag']){ ?>
		<div class="form-group" id="div_<?php echo $name; ?>">
			<label><?php echo $field['label']; ?></label>
			<input <?php echo $field['attr']; ?> id="<?php echo $name; ?>" class="form-control input-sm" name="module[<?php echo $name; ?>]" value="<?php echo esc_attr($val); ?>" data-value="<?php echo $def; ?>" placeholder="<?php echo $def; ?>"/>
			<?php if(!empty($field['text'])){ echo "<p class='help-block'>{$field['text']}</p>"; } ?>
		</div>
	<?php } elseif('checkbox' == $field['tag']){ ?>
		<div class="form-group" id="div_<?php echo $name; ?>">
			<div class="checkbox">
				<input type="hidden" name="module[<?php echo $name; ?>]" value="0"/>
				<label><input type="checkbox" <?php echo $field['attr']; ?> id="<?php echo $name; ?>" name="module[<?php echo $name; ?>]" value="1" <?php echo checked($val, $def); ?>/> <?php echo $field['label']; ?></label>
				<?php if(!empty($field['text'])){ echo "<p class='help-block'>{$field['text']}</p>"; } ?>
			</div>
		</div>
	<?php } elseif('select' == $field['tag']){ ?>
		<div class="form-group" id="div_<?php echo $name; ?>">
			<label><?php echo $field['label']; ?></label>
			<select <?php echo $field['attr']; ?> id="<?php echo $name; ?>" class="form-control input-sm" name="module[<?php echo $name; ?>]">
			<?php foreach($field['choices'] as $choice){ ?>
				<option value="<?php echo esc_attr($choice['value']); ?>" <?php echo selected($val, $choice['value']); ?>><?php echo $choice['label']; ?></option>
			<?php } ?>
			</select>
			<?php if(!empty($field['text'])){ echo "<p class='help-block'>{$field['text']}</p>"; } ?>
		</div>
	<?php } elseif('textarea' == $field['tag']){ ?>
		<div class="form-group" id="div_<?php echo $name; ?>">
			<label><?php echo $field['label']; ?></label>
			<textarea <?php echo $field['attr']; ?> id="<?php echo $name; ?>" class="form-control input-sm" name="module[<?php echo $name; ?>]"><?php echo esc_html($val); ?></textarea>
			<?php if(!empty($field['text'])){ echo "<p class='help-block'>{$field['text']}</p>"; } ?>
		</div>
	<?php } ?>
	<?php
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


