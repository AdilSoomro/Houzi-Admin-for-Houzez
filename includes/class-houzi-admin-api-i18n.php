<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://https://booleanbites.com
 * @since      1.0.0
 *
 * @package    Houzi_Admin_Api
 * @subpackage Houzi_Admin_Api/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Houzi_Admin_Api
 * @subpackage Houzi_Admin_Api/includes
 * @author     Houzi <houzi@booleanbites.com>
 */
if ( ! class_exists( 'Houzi_Admin_Api_i18n' ) ) {
	class Houzi_Admin_Api_i18n {
	
	
		/**
		 * Load the plugin text domain for translation.
		 *
		 * @since    1.0.0
		 */
		public function load_plugin_textdomain() {
	
			load_plugin_textdomain(
				'houzi-admin-api',
				false,
				dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
			);
	
		}
	
	
	
	}
}
