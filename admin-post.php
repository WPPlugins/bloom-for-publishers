<?php //Admin Post: admin-post.php

add_action( 'admin_enqueue_scripts', 'blm_admin_post_scripts' );
add_action( 'admin_notices', 'blm_admin_post_notice' );
add_action( 'add_meta_boxes', 'blm_admin_post_location_display' );
add_action( 'save_post', 'blm_admin_post_save', 10, 3 );
add_filter( 'post_updated_messages', 'blm_admin_post_save_callback' );

/*
* blm_admin_post_scripts
* Add CSS and JS to the admin post page
*/
function blm_admin_post_scripts( $hook ) {

	//Check valid page type
	if( !blm_admin_post_validtype( $hook ) ) {
		return true;
	}

	$blm_setting_google_api_key = esc_attr( get_option( 'blm_setting_google_api_key' ) );

	if( $blm_setting_google_api_key ) {
		wp_enqueue_script( 'blm_meta_js_google', 'https://maps.googleapis.com/maps/api/js?key='.$blm_setting_google_api_key );
		wp_enqueue_script( 'blm_meta_js_geocode', plugin_dir_url( __FILE__ ).'js/geocode.js', null, '1.1' );
		wp_enqueue_script( 'blm_meta_js_adminpost', plugin_dir_url( __FILE__ ).'js/admin-post.js', null, '1.2' );
	}

	wp_enqueue_style( 'blm_meta_css_main', plugin_dir_url( __FILE__ ).'css/admin-post.css', null, '1.2' );

}// blm_admin_post_scripts

/*
* blm_admin_post_notice
* Add a message to the header of the admin
*/
function blm_admin_post_notice(){

	global $pagenow, $post_ID;

	// Check valid page type
	if( !blm_admin_post_validtype( $pagenow ) ) {
		return true;
	}

	// Handle API response notice
	if( $api_response = get_post_meta( $post_ID, 'blm_savepost_response', true ) ){
		$api_response = json_decode( $api_response );
		echo '<div class="notice '.$api_response->notice_type.' is-dismissible" data-code="'.$api_response->code.'"><p>Bloom: '.$api_response->message.'</p></div>';
	}

	blm_admin_post_save_exit();

	return true;

}// blm_admin_post_notice

/*
* blm_admin_post_location_display
* Adds a location input to post editor
*/
function blm_admin_post_location_display() {

	global $pagenow;

	// Check valid page type
	if( !blm_admin_post_validtype( $pagenow ) ) {
		return false;
	}

	add_meta_box( 'blm_location_meta', __( 'Post Location', 'location-textdomain' ), 'blm_admin_post_location_display_callback', null, 'advanced', 'high' );

}// blm_admin_post_location_display

/*
* blm_admin_post_location_display_callback
* Displays the location input in editor
*/
function blm_admin_post_location_display_callback( $post ) {

	// Fetch currently-saved post location data
	$blm_setting_google_api_key = esc_attr( get_option( 'blm_setting_google_api_key' ) );
	$blm_post_key = get_post_meta( $post->ID, 'blm_post_key', true );
	$blm_post_location_formatted = get_post_meta( $post->ID, 'blm_post_location_formatted', true );
	$blm_post_location_components = get_post_meta( $post->ID, 'blm_post_location_components', true );
	$blm_post_location_latitude = get_post_meta( $post->ID, 'blm_post_location_latitude', true );
	$blm_post_location_longitude = get_post_meta( $post->ID, 'blm_post_location_longitude', true );

	// Handle if no Google API key provided
	if( !$blm_setting_google_api_key ) {
		echo '<p>Enter your Google API Key on the Bloom Settings page in order to use this geotagging feature.</p>';
		return true;
	}

	//If post is submitted, generate the map image
	if( $blm_post_location_latitude && $blm_post_location_longitude && $blm_post_key ) {
		blm_lib_map_image( $blm_post_location_latitude, $blm_post_location_longitude, $blm_post_key );
	}
	?>

		<div id="blm-meta-intro">The location selected here should define the location discussed in this post, if applicable.  By saving the location, the post is <a href="https://www.bloom.li/advocacy/metadata" title="Bloom" target="_blank">geotagged</a> by inserting the address and coordinates into the webpage metadata.</div>

		<div id="blm-location-selection-tool">
			<div id="blm-location-search">
				<div class="blm-search-title">Location Search</div>
				<div class="blm-search-body">
					<input type="text" id="blm-location-input" />
					<button type="button" id="blm-location-request" onClick="blmGeocode( 'blm-location-input', 'blm-location-results' );">Search</button>
					<div id="blm-location-results"></div>
				</div>
			</div>

			<div id="blm-location-choice" data-display="<?php echo ( $blm_post_location_formatted ) ? 1 : 0; ?>">
				<div class="blm-search-title">Location Selected<button type="button" id="blm-location-clear" onClick="blmClearLocation();">Clear</button><button type="button" id="blm-location-details" onClick="blmLocationDetailsChange('show');">Show Details</button></div>

				<div class="blm-search-body">
					<div id="blm-location-choice-value"><?php echo $blm_post_location_formatted; ?></div>
					<div id="blm-location-choice-components">
						<ul>
							<?php
							$blm_post_lc_array = json_decode( $blm_post_location_components );

							foreach( $blm_post_lc_array as $blm_post_lc_key => $blm_post_lc_value ) {

								if( ! $blm_post_lc_value ){
									$blm_post_lc_value = '<em>N/A</em>';
								}

								echo '<li><strong>'.ucfirst( str_replace( '_', ' ', $blm_post_lc_key ) ).':</strong> '.$blm_post_lc_value.'</li>';

							}
							?>
						</ul>
					</div>
					<input type="hidden" name="blm-formatted-address" id="blm-formatted-address" value="<?php echo $blm_post_location_formatted; ?>" />
					<input type="hidden" name="blm-address-components" id="blm-address-components" value="<?php echo base64_encode( $blm_post_location_components ); ?>" />
					<input type="hidden" name="blm-latitude" id="blm-latitude" value="<?php echo $blm_post_location_latitude; ?>" />
					<input type="hidden" name="blm-longitude" id="blm-longitude" value="<?php echo $blm_post_location_longitude; ?>" />
				</div>
			</div>
		</div>

	<?

}// blm_admin_post_location_display_callback

/*
* blm_admin_post_save
* Save the post to Bloom
*/
function blm_admin_post_save( $post_ID , $post, $update) {

	global $pagenow;
	$has_location = false;
	$blm_post_type_defaults = array('post', 'page', 'attachment', 'revision', 'nav_menu_item');

	// Check valid page type
	if( !blm_admin_post_validtype( $pagenow ) ) {
		return true;
	}

	// Update location values
	if( isset( $_POST['blm-formatted-address'] ) && $_POST['blm-formatted-address'] ) {
		$has_location = true;
		update_post_meta( $post_ID, 'blm_post_location_formatted', sanitize_text_field( urldecode( $_POST['blm-formatted-address'] ) ) );
	}

	if( isset( $_POST['blm-address-components'] ) && $_POST['blm-address-components'] ) {
		$has_location = true;
		update_post_meta( $post_ID, 'blm_post_location_components', base64_decode( sanitize_text_field( $_POST['blm-address-components'] ) ) );
	}

	if( isset( $_POST['blm-latitude'] ) && $_POST['blm-latitude'] ) {
		$has_location = true;
		update_post_meta( $post_ID, 'blm_post_location_latitude', sanitize_text_field( $_POST['blm-latitude'] ) );
	}

	if( isset( $_POST['blm-longitude'] ) && $_POST['blm-longitude'] ) {
		$has_location = true;
		update_post_meta( $post_ID, 'blm_post_location_longitude', sanitize_text_field( $_POST['blm-longitude'] ) );
	}

	// Save to Bloom only if published
	if( 'publish' !== $post->post_status || !$has_location || wp_is_post_autosave( $post_ID ) ) {
		return blm_admin_post_save_exit();
	}

	// Get Bloom keys
	$blm_setting_bloom_api_key = get_option( 'blm_setting_bloom_api_key' );
	$blm_setting_bloom_publisher_key = get_option( 'blm_setting_bloom_publisher_key' );
	$blm_setting_google_api_key = get_option( 'blm_setting_google_api_key' );

	// Check key requirements
	if( ! $blm_setting_bloom_api_key || ! $blm_setting_bloom_publisher_key || ! $blm_setting_google_api_key ) {

		// Process Bloom API call
		blm_lib_api_process( 'info', array(
			'app_action' => 'anon_geotagging',
			'url' => $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']
		) );

		// Update header message
		update_post_meta( $post_ID, 'blm_savepost_response', json_encode( array(
			'code' => 0,
			'message' => 'This post could not be geotagged with Bloom because your keys are not provided in the Settings.'
		) ) );

		return true;

	}

	// Get location data
	$blm_post_location_formatted = get_post_meta( $post_ID, 'blm_post_location_formatted', true );
	$blm_post_location_latitude = get_post_meta( $post_ID, 'blm_post_location_latitude', true );
	$blm_post_location_longitude = get_post_meta( $post_ID, 'blm_post_location_longitude', true );

	// Check location requirements
	if( ! $blm_post_location_formatted || ! $blm_post_location_latitude || ! $blm_post_location_longitude ) {

		// Update header message
		update_post_meta( $post_ID, 'blm_savepost_response', json_encode( array(
			'code' => 0,
			'message' => 'This post could not be geotagged with Bloom because a valid location was not selected.'
		) ) );

		return true;

	}

	// Update pending field
	update_post_meta( $post_ID, 'blm_savepost_pending', 'true' );

	// Gather post data
	setup_postdata( $post );

	// Gather post tags and categories
	$blm_post_keywords = array();

	// Gather post tags
	$blm_post_tags = wp_get_post_tags( $post_ID );
	if( ! empty( $blm_post_tags ) ) {
		foreach( $blm_post_tags as $t ) {
			$blm_post_keywords[ blm_lib_keyword_strpad( $t->count ) . $t->slug ] = blm_lib_keyword_format( $t->name );
		}
	}

	// Gather post categories
	$blm_post_cats = wp_get_post_categories( $post_ID );
	if( ! empty( $blm_post_cats ) ) {
		foreach( $blm_post_cats as $c ) {

			//Get category details
			$c = get_category( $c );

			// Ignore "uncategorized" category
			if( 'uncategorized' == strtolower($c->name) ) {
				continue;
			} 

			//Store category
			$blm_post_keywords[ blm_lib_keyword_strpad( $c->count ) . $c->slug ] = blm_lib_keyword_format( $c->name );

		}
	}

	// Sort by popularity and alphabet
	krsort( $blm_post_keywords );

	// Custom Post Type
	$blm_post_type = get_post_type( $post );
	if( ! in_array( $blm_post_type, $blm_post_type_defaults ) ) {
		$blm_post_keywords[] = $blm_post_type;
	}

	// Format for API
	if( ! empty( $blm_post_keywords ) ) {
		$blm_post_keywords = implode( ',', $blm_post_keywords );
	}else{
		$blm_post_keywords = '';
	}

	// Format date

	$date_use = get_the_date( 'Y-m-d H:i:s', $post_ID );

	//Check for custom date
	$date_custom = get_option( 'blm_setting_posttype_' . $blm_post_type );
	if( $date_custom && $date_custom_value = get_post_meta( $post_ID, $date_custom, true ) ) {

		//Format into date
		$date_custom_format = strtotime( $date_custom_value );
		if( $date_custom_format ) {
			$date_custom_format = date( 'Y-m-d H:i:s', $date_custom_format );
			if( $date_custom_format ) {
				$date_use = $date_custom_format;
			}
		}

	}

	// Format expiration days
	$expiration_days = 'default';
	$expiration_days_custom = get_option( 'blm_setting_posttype_expiration_' . $blm_post_type );
	if( $expiration_days_custom ) {
		$expiration_days = $expiration_days_custom;
	}

	// Format post data
        $query = array(
		'plugin_system' => 'wordpress',
                'plugin_version' => blm_lib_get_version(),
                'google_key' => $blm_setting_google_api_key,
                'app_key' => $blm_setting_bloom_api_key,
                'app_publisher' => $blm_setting_bloom_publisher_key,
                'app_action' => 'post_add',
                'key' => get_post_meta( $post_ID, 'blm_post_key', true ),
                'date' => $date_use,
                'expiration_days' => $expiration_days,
                'title' => get_the_title( $post ),
                'content' => get_the_excerpt( $post ),
                'keywords' => $blm_post_keywords,
                'location_address' => $blm_post_location_formatted,
                'location_latitude' => $blm_post_location_latitude,
                'location_longitude' => $blm_post_location_longitude,
                'url' => get_permalink( $post ),
                'image_url' => get_the_post_thumbnail_url( $post, 'large' ),
                'user_agent' => $_SERVER['HTTP_USER_AGENT']
        );

	// Process Bloom API call
	update_post_meta( $post_ID, 'blm_savepost_response', blm_lib_api_process( 'post', $query ) );

}// blm_admin_post_save

/*
* blm_admin_post_save_callback
* Add a message to the header of the admin
*/
function blm_admin_post_save_callback( $messages ) {

	global $pagenow, $post, $post_ID;

	// Check valid page type
	if( !blm_admin_post_validtype( $pagenow ) ) {
		return true;
	}

	// Handle if post is not awaiting to be saved
	if( 'true' !== get_post_meta( $post_ID, 'blm_savepost_pending', true ) ) {
		blm_admin_post_save_exit();
		return $messages;
	}

	// Decode the API response
	$api_response_raw = get_post_meta( $post_ID, 'blm_savepost_response', true );

	if( ! $api_response_raw ) {
		blm_lib_error( 'api_response', $api_response_raw );
		blm_admin_post_save_exit();
		return $messages;
	}

	$api_response = json_decode( utf8_encode ( strip_tags( $api_response_raw ) ), true );
	if( ! $api_response || ! isset( $api_response['code'] ) ) {
		blm_lib_error( 'api_jsondecode', $api_response_raw );
		blm_admin_post_save_exit();
		return $messages;
	}

	$api_response['code'] = (int) $api_response['code'];

	// Handle type of response
	if( 1 === $api_response['code'] ) {

		// Handle successful response
		$api_response['notice_type'] = 'updated notice-success';
		update_post_meta( $post_ID, 'blm_post_key', $api_response['data']['key'] );

	} else {

		//Handle if post key is available to update
		if( isset( $api_response['data'] ) && isset( $api_response['data']['key'] ) && $api_response['data']['key'] ){
			update_post_meta( $post_ID, 'blm_post_key', $api_response['data']['key'] );
			blm_admin_post_save_exit();
			return $messages;
		}

		//Handle failed response
		$api_response['notice_type'] = 'notice-error';

	}

	//Save API response data
	update_post_meta( $post_ID, 'blm_savepost_response', json_encode( $api_response ) );

	return $messages;

}// blm_admin_post_save_callback

/*
* blm_admin_post_validtype
* Check for a valid page type
*/
function blm_admin_post_validtype($page){

	if( 'post.php' != $page && 'post-new.php' != $page ) {
		return false;
	}

	return true;

}//blm_admin_post_validtype

/*
* blm_admin_post_save_exit
* Exit the post save process
*/
function blm_admin_post_save_exit() {

	global $post_ID;

	//Remove temporary meta fields
	delete_post_meta( $post_ID, 'blm_savepost_pending' );
	delete_post_meta( $post_ID, 'blm_savepost_response' );

	return true;

}// blm_admin_post_save_exit

?>
