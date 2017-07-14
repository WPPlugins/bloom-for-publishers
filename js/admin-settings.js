/**
* onload
* Actions to perform on window load
*/
window.onload = function() {

	//Validate Google Key
	if( document.getElementById( 'blm-location-input' ) ) {

		blmGeocode( 'blm-location-input', 'blm-location-results', function( result ){

			//If a geocoding result was found, mark key as valid
			if( result ) {

				document.getElementById( 'blm-field-note-container' ).setAttribute( 'data-code', '1' );

			}

		} );

		setTimeout(function(){

			if( document.getElementById( 'blm-field-note-container' ).getAttribute( 'data-code' ) === '3' ) {
				document.getElementById( 'blm-field-note-container' ).setAttribute( 'data-code', '2' );
			}

		}, 3000);

	}

	//Get the map append setting
	var append_section = document.getElementById('blm-settings-section-map');	
	var append_field =  document.getElementById('blm-setting-map-append-enabled');

	append_field.addEventListener('change', function(e){
		append_section.setAttribute('data-enabled', append_field.value);
	});

	//Banner preview
	document.getElementById('blm-banner-premade-options').addEventListener('change', function(e){
		var imgsrc = '<img src="https://api.bloom.li/static/nearby/search/images/buttons/ad-' + e.target.value + '.jpg" />';
		document.getElementById('blm-banner-premade-preview').innerHTML = imgsrc;
		document.getElementById('blm-shortcode-code-banner').innerHTML = blmHtmlEntities('<blm-link data-type="search">' + imgsrc + '</blm-link>');
	});

}

function blmHtmlEntities(str) {
	return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
