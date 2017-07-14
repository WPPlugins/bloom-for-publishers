<?php //Post: post.php

add_action( 'wp_head', 'blm_post_head' );
add_shortcode( 'bloom', 'blm_post_map_shortcode' );
add_filter( 'the_content', 'blm_post_map_append' );

/*
* blm_post_head
* Add post's metadata to head section
*/
function blm_post_head() {

	// Retrieves the stored value from the database
	$blm_post_location_formatted = get_post_meta( get_the_ID(), 'blm_post_location_formatted', true );
	$blm_post_location_longitude = get_post_meta( get_the_ID(), 'blm_post_location_longitude', true );
	$blm_post_location_latitude = get_post_meta( get_the_ID(), 'blm_post_location_latitude', true );
	$blm_post_key = get_post_meta( get_the_ID(), 'blm_post_key', true );

	// Only show tags if inside post
	if( is_single() && $blm_post_location_formatted && $blm_post_location_latitude && $blm_post_location_longitude ){

		// Checks and displays the retrieved value
		echo '<meta property="geo:formatted_address" content="'.htmlentities( $blm_post_location_formatted, ENT_QUOTES ).'" />'."\n";
		echo '<meta property="geo:latitude" content="'.$blm_post_location_latitude.'" />'."\n";
		echo '<meta property="geo:longitude" content="'.$blm_post_location_longitude.'" />'."\n";

		if($blm_post_key){
			echo '<meta property="bloom:key" content="'.$blm_post_key.'" />'."\n";
		}

	}

}// blm_post_head

/*
*blm_post_map_shortcode
*Get a map shortcode and translate it to display a map
*/
function blm_post_map_shortcode( $atts ) {

	//Check requirements
	if( ! get_post_meta( get_the_ID(), 'blm_post_key', true ) ) {
		return '';
	}

	if( isset( $atts['type'] ) && $atts['type'] == 'map' ) {

		//Get size
		$atts_size = 'rect';
		$atts_size_w = 600;

		if( isset( $atts['size'] ) ) {
			switch( $atts['size'] ) {

				case 'small':
					$atts_size = 'square';
					$atts_size_w = 300;
					break;

			}
		}

		//Get zoom
		$atts_zoom = 16;
		if( isset( $atts['zoom'] ) ) {
			switch( $atts['zoom'] ) {

				case 'neighborhood':
					$atts_zoom = 12;
					break;

				case 'city':
					$atts_zoom = 9;
					break;

			}
		}

		return '<iframe src="https://api.bloom.li/embed/article/map?akey=' . get_post_meta( get_the_ID(), 'blm_post_key', true ) . '&size=' . $atts_size . '&zoom=' . $atts_zoom . '" name="Map" style="border:none;visibility:visible;width: 100% !important;max-width:' . $atts_size_w . 'px;height:235px"></iframe>';

	}

}//blm_post_map_shortcode

/*
*blm_post_map_append
*Append the article's map image to the end of its content
*/
function blm_post_map_append( $blm_content ) {

	//Check page requirements
	if( ! get_post_meta( get_the_ID(), 'blm_post_key', true ) || ! is_single() || 'true' != get_option( 'blm_setting_map_append_enabled' ) || has_shortcode( $blm_content, 'bloom' ) ) {
		return $blm_content;
	}

	//Get append settings
	$blm_append_settings = array(
		'size' => get_option( 'blm_setting_map_append_size' ),
		'zoom' => get_option( 'blm_setting_map_append_zoom' )
	);

	//Get append size
	switch( $blm_append_settings['size'] ) {

		case 'small':
			$blm_append_settings['size'] = 'square';
			$blm_append_settings['size_w'] = 300;
			break;

		case 'large':
		default:
			$blm_append_settings['size'] = 'rect';
			$blm_append_settings['size_w'] = 600;
			break;

	}

	//Get append zoom
	switch( $blm_append_settings['zoom'] ){

		case 'city':
			$blm_append_settings['zoom'] = 9;
			break;

		case 'neighborhood':
			$blm_append_settings['zoom'] = 12;
			break;

		case 'block':
		default:
			$blm_append_settings['zoom'] = 16;
			break;

	}

	$blm_append_map = '<iframe src="https://api.bloom.li/embed/article/map?akey=' . get_post_meta( get_the_ID(), 'blm_post_key', true ) . '&size=' . $blm_append_settings['size'] . '&zoom=' . $blm_append_settings['zoom'] . '" name="Map" style="border:none;visibility:visible;width:100% !important;max-width:' . $blm_append_settings['size_w'] . 'px;height:235px"></iframe>';

	return $blm_content.$blm_append_map;

}//blm_post_map_append

?>
