/**
* onload
* Actions to perform on window load
*/
window.onload = function() {

	// Get location input field
	var blmLocationInput = document.getElementById( 'blm-location-input' );

	if( ! blmLocationInput ){
		return false;
	}

	// Add listener for location input keypress
	blmLocationInput.addEventListener( 'keypress', function( e ) {

		if( 13 === e.keyCode ) {
			blmGeocode( 'blm-location-input', 'blm-location-results' );
			e.preventDefault();
			return false;
		}

	} );

	// Add listener for location input keyup
	blmLocationInput.addEventListener( 'keyup', function( e ) {

		if( 13 === e.keyCode ) {
			blmGeocode( 'blm-location-input', 'blm-location-results' );
			e.preventDefault();
			return false;
		}

	} );

}// onload
