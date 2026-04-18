<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://booleanbites.com
 * @since             1.0.0
 * @package           Houzi_Admin_Api
 *
 * @wordpress-plugin
 * Plugin Name:       Houzi Admin Api
 * Plugin URI:        https://https://houzi.booleanbites.com
 * Description:       Enhance WP Admin Api for Houzi Admins.
 * Version:           1.0.0
 * Author:            Houzi
 * Author URI:        https://https://booleanbites.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       houzi-admin-api
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Global guard to prevent duplicate loading.
if ( defined( 'HOUZI_ADMIN_API_INIT' ) ) {
	return;
}
define( 'HOUZI_ADMIN_API_INIT', true );

if ( ! defined( 'HOUZI_ADMIN_API_VERSION' ) ) {
	define('HOUZI_ADMIN_API_VERSION', '1.0.0');
}
if ( ! defined( 'HOUZI_ADMIN_API_PLUGIN_PATH' ) ) {
	define('HOUZI_ADMIN_API_PLUGIN_PATH', plugin_dir_path(__FILE__));
}
define('HOUZI_ADMIN_API_IMAGE', plugins_url('/images/', __FILE__));
define('HOUZI_ADMIN_API_SHOW_EXPERIMENTAL_FEATURES', false);
/// Firebase Notify URL HERE!
define('HOUZI_FIREBASE_PUSH_URL', 'https://sendpushnotification-klcub7qt3a-uc.a.run.app');

// Include the Composer autoloader defensively.
if ( ! class_exists( 'ComposerAutoloaderInitAdminApi777' ) ) {
	if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
		require_once dirname( __FILE__ ) . '/vendor/autoload.php';
	}
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-houzi-admin-api-activator.php
 */
function activate_houzi_admin_api()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-houzi-admin-api-activator.php';
	Houzi_Admin_Api_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-houzi-admin-api-deactivator.php
 */
function deactivate_houzi_admin_api()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-houzi-admin-api-deactivator.php';
	Houzi_Admin_Api_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_houzi_admin_api');
register_deactivation_hook(__FILE__, 'deactivate_houzi_admin_api');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-houzi-admin-api.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_houzi_admin_api()
{

	$plugin = new Houzi_Admin_Api();
	$plugin->run();

}
run_houzi_admin_api();