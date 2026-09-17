<?php
/**
 * Plugin Name: Custom Maintenance Mode
 * Description: Puts the site into maintenance mode with a custom title, message and optional end time, while logged-in administrators can keep browsing the site normally.
 * Version: 1.0.0
 * Author: Jegan Jeyaraman
 * Author URI: https://github.com/jeyaramanjegan
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: custom-maintenance-mode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CMM_VERSION', '1.0.0' );
define( 'CMM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CMM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CMM_PLUGIN_DIR . 'includes/class-custom-maintenance-mode.php';

/**
 * Boots the plugin once all active plugins have been loaded.
 */
function cmm_bootstrap() {
	new Custom_Maintenance_Mode();
}
add_action( 'plugins_loaded', 'cmm_bootstrap' );
