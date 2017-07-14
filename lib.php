<?php //Library: lib.php

/*
* blm_lib_api_process
* Call the initial API request
*/
function blm_lib_api_process( $method_url, $query ){

	// Process API call
	$curl_handle = curl_init();
	curl_setopt( $curl_handle, CURLOPT_URL, 'https://api.bloom.li/api/'.$method_url );
	curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl_handle, CURLOPT_POST, count( $query ) );
	curl_setopt( $curl_handle, CURLOPT_POSTFIELDS, $query );
	curl_setopt( $curl_handle, CURLOPT_CONNECTTIMEOUT ,0 ); 
	curl_setopt( $curl_handle, CURLOPT_TIMEOUT, 30 );
	$result = curl_exec( $curl_handle );
	curl_close( $curl_handle );

	// Catch API error
	if( $result !== false ) {
		$result = json_decode( $result );
	}

	if( isset( $result->success ) && $result->success ) {
		return $result->data;
	} else {
		return json_encode( array(
			'code' => 0,
			'message' => 'An error occurred with geotagging on Bloom.'.print_r($result, true)
		) );
	}

}// blm_lib_api_process

/*
* blm_lib_keyword_strpad
* Pad the number of a keyword's count
*/
function blm_lib_keyword_strpad( $count ) {

	return str_pad( $count, 5, 0, STR_PAD_LEFT );

}// blm_lib_keyword_strpad

/*
* blm_lib_keyword_format
* Update the keyword to an acceptable format
*/
function blm_lib_keyword_format( $keyword ) {

	$result = str_replace( ' ', '-', $keyword );

	return preg_replace( '/[^\w-]/', '', $result );

}// blm_lib_keyword_format

/*
* blm_lib_error
* Error reporting
*/
function blm_lib_error( $type, $data ) {

	// Process Bloom API call
	blm_lib_api_process( 'application', array(
		'app_key' => get_option( 'blm_setting_bloom_api_key' ),
		'app_user' => get_option( 'blm_setting_bloom_publisher_key' ),
		'app_action' => 'error_wp_api',
		'data' => array(
			'type' => $type,
			'content' => $data
		)
	) );	

}// blm_lib_error

/*
* blm_lib_map_image
* Check if map image needs to be generated
*/
function blm_lib_map_image( $lat, $lng, $blm_post_key ) {

	//Check requirements
	if( !$lat || !$lng ) { return true; }

	//Check if map image was generated
	$headers = @get_headers('https://api.bloom.li/uploads/map/pin/la'.number_format( $lat, 4 ).'/lo'.number_format( $lng, 4 ).'/z16/596_263.png');
	if( strpos( $headers[0], '200' ) !== false ) {
		return true;
	}

	//Generate map image
	@file_get_contents( 'https://api.bloom.li/embed/article/map?akey='.$blm_post_key.'&size=rect&zoom=16' );

	return true;

}//blm_lib_map_image

/*
* blm_lib_get_version
* Get version of the Bloom for Publishers plugin
*/
function blm_lib_get_version(){

	$plugin_data = get_plugin_data(plugin_dir_path( __FILE__ ).'bloom.php');
	return $plugin_data['Version'];

}//blm_lib_get_version

?>
