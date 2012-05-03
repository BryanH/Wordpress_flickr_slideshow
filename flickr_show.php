<?php
/*
 * Plugin Name: Flickr Slideshow Shortcode
 * Plugin URI: http://hbjitney.com/flickr-show.html
 * Description: Embed a flickr slideshow in your posts by using a simple shortcode: [flickr-show set=7239827373283]<br />You set the height, width and username in the settings.
 * Version: 1.05
 * Author: HBJitney, LLC
 * Author URI: http://hbjitney.com/
 * License: GPL3

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( !class_exists('FlickrShow' ) ) {
	/**
 	* Wrapper class to isolate us from the global space in order
 	* to prevent method collision
 	*/
	class FlickrShow {
		var $plugin_name;

		/**
		 * Set up all actions, instantiate other
		 */
		function __construct() {
			add_action( 'admin_menu', array( $this, 'add_admin' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_filter( 'the_content', array( $this, 'flickr_show_shortcode' ) );
		}

		/**
		 * Add our options to the settings menu
		 */
		function add_admin() {
			add_options_page("Flickr Slideshow Shortcode", "Flickr Slideshow Shortcode", 'manage_options', 'flickr_show_plugin', array( $this, 'plugin_options_page' ) );
		}

		/**
		 * Callback for options page - set up page title and instantiate fields
		 */
		function plugin_options_page() {
?>
		<div class="plugin-options">
		<h2><span>Flickr Slideshow Shortcode Options</span></h2>
                <p>Here you can set the defaults for the shortcode, so you only have to specify the set number in your posts.</p>
		 <form action="options.php" method="post">
<?php
		  settings_fields( 'flickr_show_options' );
		  do_settings_sections( 'flickr_show_plugin' );
?>

		  <input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
		 </form>
		</div>
<?php
		}

		/*
		 * Define options section (only one) and fields (also only one!)
		 */
		function admin_init() {
			// Group = setings_fields, name of options, validation callback
			register_setting( 'flickr_show_options', 'flickr_show_options', array( $this, 'options_validate' ) );
			// Unique ID, section title displayed, section callback, page name = do_settings_section
			add_settings_section( 'flickr_show_section', '', array( $this, 'main_section' ), 'flickr_show_plugin' );
			// Unique ID, Title, function callback, page name = do_settings_section, section name
			add_settings_field( 'flickr_width', 'Width', array( $this, 'width_field'), 'flickr_show_plugin', 'flickr_show_section');
			add_settings_field( 'flickr_height', 'Height', array( $this, 'height_field'), 'flickr_show_plugin', 'flickr_show_section');
			add_settings_field( 'flickr_username', 'Username', array( $this, 'username_field'), 'flickr_show_plugin', 'flickr_show_section');
		}

		/*
		 * Static content for options section
		 */
		function main_section() {
?>
			<h3><span>Defaults</span></h3>
<?php
		}

		/*
		 * Code for height field
		 */
		function height_field() {
			// Matches field # of register_setting
			$options = get_option( 'flickr_show_options' );
?>
			<input id="flickr_height_string" name="flickr_show_options[height_string]" type="text" size="4" value="<?php
_e( $options['height_string'] );?>" />
<?php
		}

		/*
		 * Code for width field
		 */
		function width_field() {
			// Matches field # of register_setting
			$options = get_option( 'flickr_show_options' );
?>
			<input id="flickr_width_string" name="flickr_show_options[width_string]" type="text" size="4" value="<?php
_e( $options['width_string'] );?>" />
<?php
		}
		/*
		 * Code for height field
		 */
		function username_field() {
			// Matches field # of register_setting
			$options = get_option( 'flickr_show_options' );
?>
			<input id="flickr_username_string" name="flickr_show_options[username_string]" type="text" size="20" value="<?php
 _e( $options['username_string'] );?>" />
<?php
		}

		/*
		 * No validation, just remove leading and trailing spaces
		 */
		function options_validate($input) {
			$newinput['height_string'] = trim( $input['height_string'] );
			$newinput['width_string'] = trim( $input['width_string'] );
			$newinput['username_string'] = trim( $input['username_string'] );
			return $newinput;
		}

		/*
		 * Process the content for the shortcode
		 */
		function flickr_show_shortcode( $content ) {
			$options = get_option( 'flickr_show_options' );
			$height = $options['height_string'];
			$width = $options['width_string'];
			$username = $options['username_string'];

				//'[flickr-show (height=([0-9]+)){0,1}\s*(width=([0-9]+)){0,1}\s*set_id=([0-9]+)\s*(username=([a-zA-Z0-9]+)){0,1}]',
			$content = preg_replace(
				'/\[flickr-show\s*set=([a-zA-Z0-9]+)\s*\]/',
				"<object width=\"{$width}\" height=\"{$height}\" classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\"
codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0\">
<param name=\"flashvars\" value=\"offsite=true&amp;lang=en-us&amp;page_show_url=%2Fphotos%2F{$username}%2Fsets%2F$1%2Fshow%2F&amp;page_show_back_url=%2Fphotos%2F{$username}%2Fsets%2F$1%2F&amp;set_id=$1&amp;jump_to=\" />
<param name=\"allowFullScreen\" value=\"true\" />
<param name=\"src\" value=\"http://www.flickr.com/apps/slideshow/show.swf?v=71649\" />
<embed width=\"{$width}\" height=\"{$height}\" type=\"application/x-shockwave-flash\" src=\"http://www.flickr.com/apps/slideshow/show.swf?v=71649\"
flashvars=\"offsite=true&amp;lang=en-us&amp;page_show_url=%2Fphotos%2F{$username}%2Fsets%2F$1%2Fshow%2F&amp;page_show_back_url=%2Fphotos%2F{$username}%2Fsets%2F$1%2F&amp;set_id=$1&amp;jump_to=\"
allowFullScreen=\"true\" />
",
				$content);

			return $content;
		}
	}
}

/*
 * Sanity - was there a problem setting up the class? If so, bail with error
 * Otherwise, class is now defined; create a new one it to get the ball rolling.
 */
if( class_exists( 'FlickrShow' ) ) {
	new FlickrShow();
} else {
	$message = "<h2 style='color:red'>Error in plugin</h2>
	<p>Sorry about that! Plugin <span style='color:blue;font-family:monospace'>flickr-show</span> reports that it was unable to start.</p>
	<p><a href='mailto:support@hbjitney.com?subject=Flickr-show%20error&body=What version of Wordpress are you running? Please paste a list of your current active plugins here:'>Please report this error</a>.
	Meanwhile, here are some things you can try:</p>
	<ul><li>Uninstall (delete) this plugin, then reinstall it.</li>
	<li>Make sure you are running the latest version of the plugin; update the plugin if not.</li>
	<li>There might be a conflict with other plugins. You can try disabling every other plugin; if the problem goes away, there is a conflict.</li>
	<li>Try a different theme to see if there's a conflict between the theme and the plugin.</li>
	</ul>";
	wp_die( $message );
}
?>
