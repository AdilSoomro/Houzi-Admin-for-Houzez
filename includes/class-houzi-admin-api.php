<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://booleanbites.com
 * @since      1.0.0
 *
 * @package    Houzi_Admin_Api
 * @subpackage Houzi_Admin_Api/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Houzi_Admin_Api
 * @subpackage Houzi_Admin_Api/includes
 * @author     BooleanBites Ltd. <houzi@booleanbites.com>
 */
if ( ! class_exists( 'Houzi_Admin_Api' ) ) {
	class Houzi_Admin_Api {
	
		/**
		 * The loader that's responsible for maintaining and registering all hooks that power
		 * the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      Houzi_Admin_Api_Loader    $loader    Maintains and registers all hooks for the plugin.
		 */
		protected $loader;
	
		/**
		 * The unique identifier of this plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
		 */
		protected $plugin_name;
	
		/**
		 * The current version of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string    $version    The current version of the plugin.
		 */
		protected $version;
	
		/**
		 * Define the core functionality of the plugin.
		 *
		 * Set the plugin name and the plugin version that can be used throughout the plugin.
		 * Load the dependencies, define the locale, and set the hooks for the admin area and
		 * the public-facing side of the site.
		 *
		 * @since    1.0.0
		 */
		public function __construct() {
			if ( defined( 'HOUZI_ADMIN_API_VERSION' ) ) {
				$this->version = HOUZI_ADMIN_API_VERSION;
			} else {
				$this->version = '1.0.0';
			}
			$this->plugin_name = 'houzi-admin-api';
	
			$this->load_dependencies();
			$this->set_locale();
			$this->define_admin_hooks();
			$this->define_public_hooks();
	
		}
	
		/**
		 * Load the required dependencies for this plugin.
		 *
		 * Include the following files that make up the plugin:
		 *
		 * - Houzi_Admin_Api_Loader. Orchestrates the hooks of the plugin.
		 * - Houzi_Admin_Api_i18n. Defines internationalization functionality.
		 * - Houzi_Admin_Api_Admin. Defines all hooks for the admin area.
		 * - Houzi_Admin_Api_Public. Defines all hooks for the public side of the site.
		 *
		 * Create an instance of the loader which will be used to register the hooks
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function load_dependencies() {
	
			/**
			 * The class responsible for orchestrating the actions and filters of the
			 * core plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-houzi-admin-api-loader.php';
	
			/**
			 * The class responsible for defining internationalization functionality
			 * of the plugin.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-houzi-admin-api-i18n.php';
	
			/**
			 * The class responsible for defining all actions that occur in the admin area.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-houzi-admin-api-admin.php';
	
			/**
			 * The class responsible for defining all actions that occur in the public-facing
			 * side of the site.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-houzi-admin-api-public.php';
	
			$this->loader = new Houzi_Admin_Api_Loader();
	
		}
	
		/**
		 * Define the locale for this plugin for internationalization.
		 *
		 * Uses the Houzi_Admin_Api_i18n class in order to set the domain and to register the hook
		 * with WordPress.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function set_locale() {
	
			$plugin_i18n = new Houzi_Admin_Api_i18n();
	
			$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	
		}
	
		/**
		 * Register all of the hooks related to the admin area functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_admin_hooks() {
			require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/user_notifications.php');
			$plugin_admin = new Houzi_Admin_Api_Admin( $this->get_plugin_name(), $this->get_version() );
	
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	
			$plugin_admin->load_admin_settings();
		}
	
		/**
		 * Register all of the hooks related to the public-facing functionality
		 * of the plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 */
		private function define_public_hooks() {
	
			$plugin_public = new Houzi_Admin_Api_Public( $this->get_plugin_name(), $this->get_version() );
			
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
			$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
			$this->houzi_admin_api_inc_files();
		}
		
		function houzi_admin_api_inc_files() {
			
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/property_search_functions.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/property_functions.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/property_data_functions.php');
			require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/touch_base_functions.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/houzez_insights.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/agent_agency_functions.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/crm_dashboard.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/user_functions.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/users_verification_functions.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/property_review_functions.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/security_utils.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/houzez_partners.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/houzez_packages.php');
			// require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/article_search_functions.php');
			// require_once (HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/messages_functions.php');
			//require_once( HOUZI_ADMIN_API_PLUGIN_PATH . 'functions/user_notifications.php');
			
		}
		/**
		 * Run the loader to execute all of the hooks with WordPress.
		 *
		 * @since    1.0.0
		 */
		public function run() {
			$this->loader->run();
		}
	
		/**
		 * The name of the plugin used to uniquely identify it within the context of
		 * WordPress and to define internationalization functionality.
		 *
		 * @since     1.0.0
		 * @return    string    The name of the plugin.
		 */
		public function get_plugin_name() {
			return $this->plugin_name;
		}
	
		/**
		 * The reference to the class that orchestrates the hooks with the plugin.
		 *
		 * @since     1.0.0
		 * @return    Houzi_Admin_Api_Loader    Orchestrates the hooks of the plugin.
		 */
		public function get_loader() {
			return $this->loader;
		}
	
		/**
		 * Retrieve the version number of the plugin.
		 *
		 * @since     1.0.0
		 * @return    string    The version number of the plugin.
		 */
		public function get_version() {
			return $this->version;
		}
	
	}
}


