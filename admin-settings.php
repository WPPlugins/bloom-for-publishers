<?php //Admin Settings: admin-settings.php

//Initialize
global $blm_posttypes;
$blm_posttypes = array();

// Create custom plugin settings menu
add_action( 'admin_menu', 'blm_settings_menu' );

/*
* blm_settings_menu
* Add Bloom to the settings menu
*/
function blm_settings_menu() {

	// Create new top-level menu
	add_options_page( 'Bloom for Publishers', 'Bloom for Publishers', 'administrator', __FILE__, 'blm_settings_page' , plugins_url( '/images/icon.png', __FILE__ ) );

	// Call register settings function
	add_action( 'admin_init', 'blm_settings_register' );

	// Register CSS if on settings page
	if( isset( $_GET['page'] ) && 'bloom-for-publishers/admin-settings.php' === $_GET['page'] ) {

		// Register scripts
		add_action( 'admin_enqueue_scripts', 'blm_settings_scripts' );

	}

}// blm_settings_menu

/*
* blm_settings_register
* Register settings page with Wordpress
*/
function blm_settings_register($additional = null) {

	register_setting( 'blm_options_group', 'blm_setting_bloom_api_key' );
	register_setting( 'blm_options_group', 'blm_setting_bloom_publisher_key' );
	register_setting( 'blm_options_group', 'blm_setting_google_api_key' );
	register_setting( 'blm_options_group', 'blm_setting_search_enabled' );
	register_setting( 'blm_options_group', 'blm_setting_search_preview' );
	register_setting( 'blm_options_group', 'blm_setting_map_append_enabled' );
	register_setting( 'blm_options_group', 'blm_setting_map_append_size' );
	register_setting( 'blm_options_group', 'blm_setting_map_append_zoom' );

	//Get custom settings
	$custom_settings = blm_get_posttypes();
	if( $custom_settings ) {
		foreach( $custom_settings as $a ) {
			register_setting( 'blm_options_group', $a );
		}
	}

}// blm_settings_register

/*
* blm_settings_page
* Code for the Bloom settings page
*/
function blm_settings_page() {

	// Form tabs
	$blm_settings_tab = 'general';
	if ( isset ( $_GET['tab'] ) ){
		$blm_settings_tab = $_GET['tab'];
	}

	// Validate application key
	$api_key = get_option( 'blm_setting_bloom_api_key' );

	if( $api_key ) {

		// Process Bloom API call
		$api_response = blm_lib_api_process( 'application', array (
			'key' => $api_key,
			'app_action' => 'validate_key'
		) );

		$api_response = json_decode( $api_response );

		if( $api_response->success ) {
			$api_key_valid = $api_response;
		}

	}

	// Validate publisher key
	$pub_key = get_option( 'blm_setting_bloom_publisher_key' );
	if( $pub_key ) {

		// Process Bloom API call
		$api_response = blm_lib_api_process( 'user', array (
			'key' => $pub_key,
			'app_action' => 'validate_key'
		) );

		$api_response = json_decode( $api_response );

		if( $api_response->success ) {
			$pub_key_valid = $api_response;
		}

	}

	// Validate Google API key
	$google_key = get_option( 'blm_setting_google_api_key' );

	if( $google_key ) {
		wp_enqueue_script( 'blm_meta_js_google', 'https://maps.googleapis.com/maps/api/js?key='.$google_key );
		wp_enqueue_script( 'blm_meta_js_geocode', plugin_dir_url( __FILE__ ).'js/geocode.js', null, '1.2' );
		wp_enqueue_script( 'blm_meta_js_adminsettings', plugin_dir_url( __FILE__ ).'js/admin-settings.js', null, '1.1' );
	}

	?>

	<div class="wrap">

		<h2>Bloom</h2>
		<p>The following settings only apply to publishers who are registered on <a href="https://www.bloom.li" title="Bloom" target="_blank">Bloom</a>.<br />The keys allow you to easily integrate geotagging, local search, mapping into your website.<br /><a href="https://www.youtube.com/watch?v=HJyCtOOGinM" title="Watch video tutorial" target="_blank">Watch our video tutorial</a> on how to configure these settings.</p>

		<form method="post" action="options.php" id="blm-settings-form" data-tab="<?php echo $blm_settings_tab; ?>">

			<?php

			// Identify which settings this page will handle
			settings_fields( 'blm_options_group' );
			do_settings_sections( 'blm_options_group' );
			add_thickbox();
			blm_settings_tabs($blm_settings_tab);

			?>

			<div class="blm-settings-section" data-tab="general">

				<h2>General Plugin Settings</h2>

				<table class="form-table">

					<tr>
						<th scope="row">
							<strong>Bloom API Key</strong>
							<a href="#TB_inline?width=600&height=250&inlineId=blm-tb-bloomapikey" class="thickbox"></a>
						</th>
						<td data-field="bloom-api-key">
							<input type="text" name="blm_setting_bloom_api_key" value="<?php echo esc_attr( get_option( 'blm_setting_bloom_api_key' ) ); ?>" />

							<? if( isset( $api_key_valid ) ) { ?>

							<div data-code="<? echo $api_key_valid->code; ?>" class="blm-field-note">
								<div class="blm-field-message">
									<span><? echo $api_key_valid->message; ?></span>
									<a href="https://www.bloom.li" title="Bloom Plugins" target="_blank" class="blm-field-link">Get your API key</a>
								</div>
							</div>

							<? } ?>

						</td>
					</tr>

					<tr>
						<th scope="row">
							<strong>Bloom Publisher Key</strong>
							<a href="#TB_inline?width=600&height=250&inlineId=blm-tb-bloompublisherkey" class="thickbox"></a>
						</th>
						<td data-field="bloom-publisher-key">
							<input type="text" name="blm_setting_bloom_publisher_key" value="<?php echo esc_attr( get_option( 'blm_setting_bloom_publisher_key' ) ); ?>" />
							<? if( isset( $pub_key_valid ) ) { ?>

							<div data-code="<? echo $pub_key_valid->code; ?>" class="blm-field-note">
								<div class="blm-field-message">
									<span><? echo $pub_key_valid->message; ?></span>
									<a href="https://www.bloom.li" title="Bloom Plugins" target="_blank" class="blm-field-link">Get your publisher key</a>
								</div>
							</div>

							<? } ?>

						</td>
					</tr>

					<tr>
						<th scope="row">
							<strong>Google API Key</strong>
							<a href="#TB_inline?width=600&height=300&inlineId=blm-tb-googleapikey" class="thickbox"></a>
						</th>   
						<td data-field="google-api-key">
							<input type="text" name="blm_setting_google_api_key" value="<?php echo esc_attr( get_option( 'blm_setting_google_api_key' ) ); ?>" />
							<div data-code="3" class="blm-field-note" id="blm-field-note-container">
								<div class="blm-field-message">

									<span id="blm-field-note-message">
										<? if( $google_key ) { ?>
											<span id="blm-field-note-message-validating">Validating</span>
											<span id="blm-field-note-message-invalid">Invalid: <a href="#TB_inline?width=600&height=300&inlineId=blm-tb-googleapikey" class="thickbox">Get your Google API Key</a></span>
											<span id="blm-field-note-message-valid">Valid</span>
										<? }else{ ?>
											<span id="blm-field-note-message-empty"><a href="#TB_inline?width=600&height=300&inlineId=blm-
tb-googleapikey" class="thickbox">Get your Google API Key</a></span>
										<? } ?>
									</span>

								</div>
							</div>

							<? if( $google_key ) { ?>
								<input type="hidden" id="blm-location-input" value="1600 Pennsylvania Ave NW, Washington, DC 20500" />
								<div id="blm-location-results"></div>
							<? } ?>

						</td>

					</tr>

				</table>

				<?php submit_button(); ?>

				<div class="blm-settings-section">

					<h3>Getting started: How to install and configure Bloom</h3>
					<iframe width="560" height="315" src="https://www.youtube.com/embed/HJyCtOOGinM" frameborder="0" allowfullscreen></iframe>

				</div>

				<div id="blm-tb-bloomapikey" style="display: none;">
					<h3>Bloom API Key</h3>
					<p>This is your private key that gives you access to Bloom's geotagging and search tools.  For security purposes, please keep this to yourself.</p>
					<ol>
						<li>Login to your Bloom account</li>
						<li>Go to your Account page</li>
						<li>Select a Publisher</li>
						<li>Go to the News Nearby Search page</li>
						<li>Copy the API Key</li>
					</ol>
				</div>

				<div id="blm-tb-bloompublisherkey" style="display: none;">
					<h3>Bloom Publisher Key</h3>
					<p>This is your key for your publisher account that allows you to send requests to Bloom for geotagging and search.</p>
					<ol>
						<li>Login to your Bloom account</li>
						<li>Go to your Account page</li>
						<li>Select a Publisher</li>
						<li>Go to the Edit Profile page</li>
						<li>Copy the Publisher Key</li>
					</ol>
				</div>

				<div id="blm-tb-googleapikey" style="display: none;">
					<h3>Google API Key</h3>
					<p>This is required in order to run the geocoding feature.</p>
					<ol>
						<li>Visit <a href="https://console.developers.google.com/apis" title="Google Developer API" target="_blank">https://console.developers.google.com/apis</a></li>
						<li>Select a Project, or Create a Project (top right corner in blue header)</li>
						<li>Go back to Google API’s page: <a href="https://console.developers.google.com/apis" title="Google Developer API" target="_blank">https://console.developers.google.com/apis</a></li>
						<li>In the Google APIs tab, search for "Google Maps JavaScript API" and "Google Maps Geocoding API"</li>
						<li>Click the "Enable" button for both of these APIs.</li>
						<li>Add Credentials: follow these steps and it will give you your API key.</li>
						<li>Copy/paste the API key into the Bloom for Publishers settings.</li>
					</ol>
				</div>

			</div>

			<div class="blm-settings-section" data-tab="search">

				<h2>Local Search Settings</h2>

				<table class="form-table">

					<tr>
						<th scope="row">
							<strong>Local search</strong>
							<a href="https://www.bloom.li/discovery/plugins/search" title="Bloom for Publishers" target="_blank" class="blm-external-link"></a>
						</th>
						<td>
							<select name="blm_setting_search_enabled">
								<option <?php echo ( 'true' === esc_attr( get_option( 'blm_setting_search_enabled' ) ) ) ? 'selected="selected"' : ''; ?> value="true">Enabled</option>
								<option <?php echo ( 'false' === esc_attr( get_option( 'blm_setting_search_enabled' ) ) ) ? 'selected="selected"' : ''; ?> value="false">Disabled</option>
							</select>
						</td>
					</tr>

					<tr>

						<th scope="row">
							<strong>Local search preview</strong>
							<a href="#TB_inline?width=600&height=300&inlineId=blm-tb-localsearchpreview" class="thickbox"></a>
						</th>
						<td>
							<select name="blm_setting_search_preview">
								<option <?php echo ( 'true' === esc_attr( get_option( 'blm_setting_search_preview' ) ) ) ? 'selected="selected"' : ''; ?> value="true">On</option>
								<option <?php echo ( 'false' === esc_attr( get_option( 'blm_setting_search_preview' ) ) ) ? 'selected="selected"' : ''; ?> value="false">Off</option>
							</select>
						</td>

					</tr>

				</table>

				<?php submit_button(); ?>

				<div id="blm-tb-localsearchpreview" style="display: none;">
					<h3>Local Search Preview</h3>
					<p>This option allows for users (staff) who are logged into your website to view Bloom's Local Search plugin on any page.  The preview will include all of the interface features and functionality so that you can test before enabling it on your website.</p>
					<p>Other visitors to your website will not see the plugin unless you have selected "Enabled" for the option above.</p>
				</div>

			</div>

			<div class="blm-settings-section" id="blm-settings-section-map" data-tab="map" data-enabled="<?php echo get_option( 'blm_setting_map_append_enabled' ); ?>">

				<table class="form-table">

					<tr>
						<th scope="row">
							<h2>Append Map To Posts</h2>
						</th>
						<td>
							<select name="blm_setting_map_append_enabled" id="blm-setting-map-append-enabled">
								<option <?php echo ( 'false' === esc_attr( get_option( 'blm_setting_map_append_enabled' ) ) ) ? 'selected="selected"' : ''; ?> value="false">Disabled</option>
								<option <?php echo ( 'true' === esc_attr( get_option( 'blm_setting_map_append_enabled' ) ) ) ? 'selected="selected"' : ''; ?> value="true">Enabled</option>
							</select>
							<a href="#TB_inline?width600&height=300&inlineId=blm-tb-mapappend" class="thickbox"></a>
						</td>
					</tr>

					<tr class="blm-append-map-field">
						<th scope="row">
							<strong>Map Size</strong>
						</th>
						<td>
							<select name="blm_setting_map_append_size">
								<option <?php echo ( 'small' === esc_attr( get_option( 'blm_setting_map_append_size' ) ) ) ? 'selected="selected"' : ''; ?> value="small">Small (300 by 300 pixels)</option>
								<option <?php echo ( 'large' === esc_attr( get_option( 'blm_setting_map_append_size' ) ) ) ? 'selected="selected"' : ''; ?> value="large">Large (600 by 300 pixels)</option>
							</select>
						</td>
					</tr>

					<tr class="blm-append-map-field">
						<th scope="row">
							<strong>Map Zoom</strong>
						</th>
						<td>
							<select name="blm_setting_map_append_zoom">
								<option <?php echo ( 'block' === esc_attr( get_option( 'blm_setting_map_append_zoom' ) ) ) ? 'selected="selected"' : ''; ?> value="block">Block</option>
								<option <?php echo ( 'neighborhood' === esc_attr( get_option( 'blm_setting_map_append_zoom' ) ) ) ? 'selected="selected"' : ''; ?> value="neighborhood">Neighborhood</option>
								<option <?php echo ( 'city' === esc_attr( get_option( 'blm_setting_map_append_zoom' ) ) ) ? 'selected="selected"' : ''; ?> value="city">City</option>
							</select>
						</td>
					</tr>

				</table>

				<?php submit_button(); ?>

				<div class="blm-settings-section">

					<h3>Map Shortcode</h3>
					<p>Each article geotagged with Bloom automatically comes with a set of maps that you can use instantly with a simple shortcode.</p>

					<div class="blm-shortcode">
						<h4>Static Map Image</h4>
						<div class="blm-shortcode-example">
							<a href="https://www.bloom.li/discovery/plugins/embed/article" title="Static Map Image" target="_blank">Example</a>
						</div>
						<div class="blm-shortcode-code">[bloom type="map" size="large" zoom="block"]</div>
						<div class="blm-shortcode-options">
							<ul>
								<li>Size: "large" (600 by 300 pixels), "small" (300 by 300 pixels)</li>
								<li>Zoom: "block", "neighborhood", or "city"</li>
							</ul>
						</div>
					</div>

				</div>

				<div id="blm-tb-mapappend" style="display: none;">
					<h3>Append Map To Posts</h3>
					<p>This option will automatically display a map at the end of every geotagged article on your website.  The map replaces the need to copy/paste the shortcode option (below this field) into each individual post.  Each map accurately labels the street address of the article with the address written in text and as a pin on the map.</p>
					<p>The map will only display on the post's individual page and if the map shortcode (below this field) is not being used already.</p>
				</div>

			</div>

			<div class="blm-settings-section" data-tab="buttons">

				<h2>Search and Map Buttons</h2>

				<p>Add these buttons anywhere on your website to help encourage your readers to use the local search plugin and view the article's map.  When clicked, the search button will open your local search plugin, and the map button will open a popup with the article's map and address.</p>
				<p>Simply copy and customize the code below into the single article template file (ex. single.php), preferably near the page title or where your social network buttons are displayed.</p>

				<div class="blm-shortcode">
					<h4>Options</h4>
					<div class="blm-shortcode-code">
						<?php echo htmlentities('<blm-button data-type="search" data-style="dark"></blm-button>'); ?>
					</div>
					<div class="blm-shortcode-options">
						<ul>
							<li>Type: "search" or "map"</li>
							<li>Style: "light" or "dark"</li>
						</ul>
					</div>
				</div>

				<div class="blm-shortcode-preview">
					<blm-button data-type="search" data-style="dark"></blm-button>
					<blm-button data-type="map" data-style="light"></blm-button>
				</div>

				<div class="blm-settings-section">

					<h2>Custom Links for Search and Map</h2>

					<p>Similar to the buttons, this custom link option can encourage your readers to use Bloom's plugins.  The code can be inserted anywhere on your website and will automatically open the Local Search plugin or Map popup for geotagged articles.</p>

					<div class="blm-shortcode">
						<h4>Options</h4>
						<div class="blm-shortcode-code" id="blm-shortcode-code-banner">
							<?php echo htmlentities('<blm-link data-type="search"><img src="YOUR IMAGE" /></blm-link>'); ?>
						</div>
						<div class="blm-shortcode-options">
							<ul>
								<li>Type: "search" or "map"</li>
								<li>Content: Text or &lt;img&gt; tag</li>
							</ul>
							<div id="blm-banner-premade">
								<p>Try one of our pre-made designs:</p>
								<select id="blm-banner-premade-options">
									<option value="none">Select one</option>
									<option value="banner-01">Banner: Green</option>
									<option value="banner-02">Banner: Blue</option>
									<option value="square-01">Square: Green</option>
									<option value="square-02">Square: Blue</option>
								</select>
							</div>
						</div>
					</div>

					<div class="blm-shortcode-preview" id="blm-banner-premade-preview"></div>

				</div>

			</div>

			<?
			global $blm_posttypes;
			if( ! empty( $blm_posttypes ) ) { ?>

				<div class="blm-settings-section" data-tab="post_types">

					<h2>Publish Date Field (beta)</h2>

					<p>By default, Bloom uses a post's "Publish Date" to label when it took place.  If you are using a custom post type, an Event for example, it is likely that the date of the event is not necessarily the "Publish Date".  In this case, we've provided options below for you to specify the correct field we should use.</p>

					<table class="form-table">

						<? foreach( $blm_posttypes as $k => $t ) { ?>

							<tr>
								<th scope="row">
									<strong><?=$t['type']; ?> <? if($k !== 'post'){ echo '<span>('.$k.')</span>'; } ?></strong>
								</th>
								<td>
									<select name="blm_setting_posttype_<?=esc_attr( $k ) ; ?>">
										<option value="default">Use default publish date</option>
										<?
										foreach( $t['fields'] as $f ) {

											//Handle if needs to be marked as selected
											$selected = '';
											if( get_option( 'blm_setting_posttype_' . $k ) == $f ) {
												$selected = ' selected="selected"';
											}
											echo '<option value="' . esc_attr( $f ) . '"' . $selected . '>' . $f . '</option>';
										}
										?>
									</select>
								</td>
							</tr>

						<? } ?>

					</table>

					<h2>Days Until Archived (beta)</h2>

					<p>Posts submitted on Bloom each have an archive date to respect news and events that you may want to archive after a specific number of days.  In this section, you can change the default number of days until a specific post type is archived.  The number of days begins from the published date of the post.  The value must be an integer that is less than or equal to 30.</p>

					<table class="form-table">

						<? foreach( $blm_posttypes as $k => $t ) {

							if(get_option( 'blm_setting_posttype_expiration_' . $k )){
								$expiration_days = get_option( 'blm_setting_posttype_expiration_' . $k );
							}else{
								$expiration_days = 20;
							}
							?>

							<tr>
								<th scope="row">
									<strong><?=$t['type']; ?> <? if($k !== 'post'){ echo '<span>('.$k.')</span>'; } ?></strong>
								</th>
								<td>
									<input name="blm_setting_posttype_expiration_<?=esc_attr( $k ) ; ?>" value="<?=esc_attr( $expiration_days ); ?>" />
								</td>
							</tr>

						<? } ?>

					</table>

					<?php submit_button(); ?>

				</div>

			<? } ?>

		</form>

	</div>

<?php

}// blm_settings_page

/*
* blm_settings_scripts
* Add CSS scripts
*/
function blm_settings_scripts( $hook ) {

        wp_enqueue_style( 'blm_settings_css_main', plugin_dir_url( __FILE__ ).'css/admin-settings.css', null, '1.1' );
	wp_enqueue_script( 'blm_settings_js_main', plugin_dir_url( __FILE__ ).'js/admin-settings.js', null, '1.1' );

}// blm_settings_scripts

/*
* blm_settings_tabs
* Add tabs for each group of settings
*/

function blm_settings_tabs( $current = 'general' ) {

	$tabs = array(
		'general' => 'General',
		'search' => 'Local Search',
		'map' => 'Map',
		'buttons' => 'Buttons'
	);

	//Add Post Type if has custom fields
	global $blm_posttypes;
	if( ! empty( $blm_posttypes ) ) {
		$tabs['post_types'] = 'Post Type Settings';
	}

	echo '<div id="icon-themes" class="icon32"><br></div>';
	echo '<h2 class="nav-tab-wrapper">';

	foreach( $tabs as $tab => $name ){
		$class = ( $tab == $current ) ? ' nav-tab-active' : '';
		echo '<a class="nav-tab'.$class.'" href="?page=bloom-for-publishers/admin-settings.php&tab='.$tab.'">'.$name.'</a>';
	}

	echo '</h2>';

}//blm_settings_tabs

/*
* blm_get_posttypes
* Get custom post types
*/
function blm_get_posttypes() {

	//Initialize
	global $blm_posttypes;
	$types_exclude = array(
		'page',
		'attachment',
		'revision',
		'nav_menu_item'
	);

	//Get post types
	$post_types = get_post_types();
	$blm_posttypes_settings = array();
	foreach( $post_types as $t ) {

		//Exclude non-custom post types
		if( in_array( $t, $types_exclude ) ) { continue; }

		//Get post type
		$type = get_post_type_object( $t );
		if( ! $type ){ continue; }

		//Get posts by post type
		$query = get_posts(array(
			'post_type' => $t,
			'post_status' => 'publish'
		));

		//Handle if has no posts
		if( empty( $query ) ) { continue; }

		//Get custom fields
		$fields = array();
		foreach( $query as $p ) {

			//Get custom fields, if any
			$meta = get_post_custom( $p->ID );
			if( ! $meta ) { break; }

			foreach( $meta as $name => $values ) {
				$fields[] = $name;
			}

			break;

		}

		//Store if custom fields were found or if default post type
		if( $fields || $t == 'post' ) {
			$blm_posttypes[ $t ] = array(
				'type' => $type->labels->singular_name,
				'fields' => $fields
			);
			$blm_posttypes_settings[ $t ] = 'blm_setting_posttype_' . $t;
			$blm_posttypes_settings[ $t ] = 'blm_setting_posttype_expiration_' . $t;
		}

	}

	return $blm_posttypes_settings;

}//blm_get_posttypes
?>
