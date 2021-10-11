<?php
/**
Plugin Name: PaperBoy
Description: Generate RSS for various news media.
Plugin URI: https://wordpress.org/plugins/paperboy/
Author: Tarosky INC.
Version: nightly
Author URI: https://tarosky.co.jp/
License: GPL3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: paperboy
Domain Path: /languages
 */

defined( 'ABSPATH' ) or die();

/**
 * Initializer.
 */
function paperboy_init() {
	// Load text domain.
	load_plugin_textdomain( 'rich-taxonomy', false, basename( __DIR__ ) . '/languages' );
	// Initialize.
	$autoloader = __DIR__ . '/vendor/autoload.php';
	if ( file_exists( $autoloader ) ) {
		require $autoloader;
		Tarosky\PaperBoy\Bootstrap::get_instance();
	}
}
add_action( 'plugin_loaded', 'paperboy_init' );

/**
 * Get plugin version.
 *
 * @return string
 */
function paperboy_version() {
	static $version = '';
	if ( $version ) {
		return $version;
	}
	$info    = get_file_data( __FILE__, [
		'version' => 'Version',
	] );
	$version = $info['version'];
	return $version;
}

/**
 * Get plugin's root url.
 *
 * @return string
 */
function paperboy_url() {
	return untrailingslashit( plugin_di_url( __FILE__ ) );
}

/**
 * Get plugn's root directory.
 *
 * @return stirng
 */
function paperboy_dir() {
	return __DIR__;
}
