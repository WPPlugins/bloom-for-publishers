<?php
/*
Plugin Name: Bloom for Publishers
Plugin URI: https://wordpress.org/plugins/bloom-for-publishers/
Description: Geotag your posts to create a local search and mapping visuals for your readers.
Version: 1.1
Author: Bloom
Author URI: https://www.bloom.li
License: GPL2
Text Domain: bloom

Bloom for Publishers is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Bloom for Publishers is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Bloom for Publishers. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

// Include additional functions
include( plugin_dir_path( __FILE__ ) . 'lib.php' );
include( plugin_dir_path( __FILE__ ) . 'post.php' );
include( plugin_dir_path( __FILE__ ) . 'search.php' );
include( plugin_dir_path( __FILE__ ) . 'admin-settings.php' );
include( plugin_dir_path( __FILE__ ) . 'admin-post.php' );

?>
