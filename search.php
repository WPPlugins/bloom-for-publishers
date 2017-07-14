<?php //Search: search.php

global $app_plugin;
$app_plugin = array();

add_action( 'wp_footer', 'blm_search_footer', 100 );
add_action( 'wp_enqueue_scripts', 'blm_search_scripts' );

/*
* blm_search_footer
* Add search to the footer of the website
*/
function blm_search_footer() {

	//Check requirements
	if( ! blm_search_auth() ) {
		return true;
	}

	// Get application style
	global $app_plugin;
	$api_response = blm_lib_api_process( 'application', array (
		'app_key' => get_option( 'blm_setting_bloom_api_key' ),
		'publisher_key' => get_option( 'blm_setting_bloom_publisher_key' ),
		'app_action' => 'get_search_plugin'
	) );
	$api_response = json_decode( $api_response );

	if( $api_response->success ) {
		$app_plugin = $api_response->message;
		$app_plugin = (array)$app_plugin;
	}else{
		return true;
	}

	// Add plugin
	echo '<bloom data-plugin="'.$app_plugin['key'].'" data-color="'.$app_plugin['color'].'" data-google-key="'.esc_attr( get_option( 'blm_setting_google_api_key' ) ).'"></bloom>';

}// blm_search_footer

/*
* blm_search_scripts
* Add CSS and JS to the plugin
*/
function blm_search_scripts() {

	//Check requirements
	if( ! blm_search_auth() ) {
		return true;
	}

	global $app_plugin;
	if(!isset($app_plugin['media_prefix'])){
		$app_plugin['media_prefix'] = '1.0';
	}

	//Handle if geo file requested
	$blm_setting_google_api_key = esc_attr( get_option( 'blm_setting_google_api_key' ) );

	if( $blm_setting_google_api_key ) {
		wp_enqueue_script( 'blm_search_js_geo', 'https://maps.googleapis.com/maps/api/js?key='.$blm_setting_google_api_key );
	}

	wp_enqueue_script( 'blm_search_js_main', 'https://api.bloom.li/static/nearby/search/js/search.js?prefix='.$app_plugin['media_prefix'] );

}// blm_search_scripts

/*
*blm_search_auth
*Authenticate the display of the plugin
*/
function blm_search_auth() {

	//Ignore if plugin key is not provided
	if( ! get_option( 'blm_setting_bloom_api_key' ) ) {
		return false;
	}

	//Ignore if search is not enabled or if preview is disabled or if user is not logged in
	if('true' === get_option( 'blm_setting_search_enabled' ) ) {
		return true;
	}

	if('false' === get_option( 'blm_setting_search_preview' ) ) {
		return false;
	}

	if( ! is_user_logged_in() ) {
		return false;
	}

	//Get logged in user
	$user = wp_get_current_user();

	//Compare against allowed roles
	$role_diff = array_udiff(
		$user->roles,
		array( 'administrator', 'editor', 'contributor', 'author' ),
		'strcasecmp'
	);

	if( empty( $role_diff ) ) {
		return true;
	}

	return true;

}//blm_search_auth
?>
