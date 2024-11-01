<?php
/**
 *
 * Disables user accounts via email address.
 *
 * @since             1.0.1
 * @package           Disable User Login
 *
 * @wordpress-plugin
 * Plugin Name:       Disable User Login
 * Plugin URI:        http://www.brainvire.com/
 * Description:       Disables user accounts via email address.
 * Version:           1.0.1
 * Author:            brainvireinfo
 * Author URI:        http://www.brainvire.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       disable-wp-user-login
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Make sure we don't expose any info if called directly.
if ( ! function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'DWUL_PLUGIN_PATH', plugin_dir_url( __FILE__ ) );
define( 'DWUL_PLUGIN_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'DWUL_PLUGIN_VERSION', '1.0.1' );

register_activation_hook( __FILE__, 'dwul_install' );

require_once DWUL_PLUGIN_DIRPATH . '/class-dwul-user-register-block.php';
require_once DWUL_PLUGIN_DIRPATH . '/class-dwul-user-register-ajax-callback.php';
require_once DWUL_PLUGIN_DIRPATH . '/create-user-schema.php';


/**
 *Add link for settings
*/
add_filter( 'plugin_action_links', 'dwul_admin_settings', 10, 4 );

/**
 * Add the Setting Links
 *
 * @since 1.0.1
 * @name dwul_admin_settings
 * @param array  $actions actions.
 * @param string $plugin_file plugin file name.
 * @return $actions
 * @author Brainvire <https://www.brainvire.com/>
 * @link https://www.brainvire.com/
 */
function dwul_admin_settings( $actions, $plugin_file ) {
	static $plugin;
	if ( ! isset( $plugin ) ) {
		$plugin = plugin_basename( __FILE__ );
	}
	if ( $plugin === $plugin_file ) {
		$settings = array();
		$settings['settings']         = '<a href="' . esc_url( admin_url( 'options-general.php?page=dwul-block-user-setting' ) ) . '">' . esc_html__( 'Settings', 'disable-wp-user-login' ) . '</a>';
		$actions                      = array_merge( $settings, $actions );
	}
	return $actions;
}
