<?php
/**
 * The Team51 Tracking plugin bootstrap file.
 *
 * @since       1.0.0
 * @version     1.1.0
 * @author      WordPress.com Special Projects
 * @license     GPL-3.0-or-later
 *
 * @noinspection    ALL
 *
 * @wordpress-plugin
 * Plugin Name:             Team51 Tracking
 * Description:             Handles automatic opt-in on parter sites to usage tracking for WooCommerce, Sensei, and other plugins/tools used by Team51.
 * Version:                 1.1.0
 * Requires at least:       6.1
 * Requires PHP:            8.1
 * Author:                  WordPress.com Special Projects
 * Author URI:              https://wpspecialprojects.wordpress.com/
 * License:                 GPL-3.0-or-later
 * License URI:             https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:             team51-tracking
 * Domain Path:             /languages
 */

defined( 'ABSPATH' ) || exit;

// Define plugin constants.
function_exists( 'get_plugin_data' ) || require_once ABSPATH . 'wp-admin/includes/plugin.php';
define( 'WPCOMSP_TRACKING_METADATA', get_plugin_data( __FILE__, false, false ) );

define( 'WPCOMSP_TRACKING_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPCOMSP_TRACKING_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPCOMSP_TRACKING_URL', plugin_dir_url( __FILE__ ) );

// Load plugin translations so they are available even for the error admin notices.
add_action(
	'init',
	static function() {
		load_muplugin_textdomain(
			WPCOMSP_TRACKING_METADATA['TextDomain'],
			plugin_basename( WPCOMSP_TRACKING_PATH ) . WPCOMSP_TRACKING_METADATA['DomainPath']
		);
	}
);

// Include the rest of the tracking plugin's files if system requirements check out.
if ( is_php_version_compatible( WPCOMSP_TRACKING_METADATA['RequiresPHP'] ) && is_wp_version_compatible( WPCOMSP_TRACKING_METADATA['RequiresWP'] ) ) {
	foreach ( glob( __DIR__ . '/includes/*.php' ) as $wpcomsp_tracking_filename ) {
		if ( preg_match( '#/includes/_#i', $wpcomsp_tracking_filename ) ) {
			continue; // Ignore files prefixed with an underscore.
		}

		include $wpcomsp_tracking_filename;
	}
}
