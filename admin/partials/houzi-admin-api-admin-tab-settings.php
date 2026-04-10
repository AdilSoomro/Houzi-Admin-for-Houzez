<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to show settings area of the plugin
 *
 * @link       https://booleanbites.com
 * @since      1.0.0
 *
 * @package    Houzi_Admin_Api
 * @subpackage Houzi_Admin_Api/admin/partials
 * @author Ahmad Nasir @BooleanBites
 * April 10, 2026
 */
if ( ! class_exists( 'AdminApiAdminSettings' ) ) {
class AdminApiAdminSettings {
    private $houzi_admin_api_options;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.1.5
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.1.5
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.1.5
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		add_action( 'admin_init', array( $this, 'houzi_admin_api_page_init' ) );

		add_action('update_option_houzi_admin_api_options', function( $old_value, $value ) {
			do_action( 'litespeed_purge_all' );
	   	}, 10, 2);
		$this->houzi_admin_api_options = get_option( 'houzi_admin_api_options' );
	}
	
	public function admin_settings() {
		?>
		
		<form method="post" action="options.php">
				<?php
					settings_fields( 'houzi_admin_api_option_group' );
					do_settings_sections( 'houzi-admin-api-admin' );
					submit_button();
				?>
		</form>
		<?php
	}

	public function houzi_admin_api_page_init() {
		
		register_setting(
			'houzi_admin_api_option_group', // option_group
			'houzi_admin_api_options', // option_name
			array( $this, 'houzi_admin_api_sanitize' ) // sanitize_callback
		);
		register_setting(
			'houzi_admin_api_option_group', // option_group
			'houzi_admin_app_secret' // option_name
		);
		add_settings_section(
			'houzi_admin_api_setting_section', // id
			'Settings', // title
			array( $this, 'houzi_admin_api_section_info' ), // callback
			'houzi-admin-api-admin' // page
		);
		add_settings_field(
			'houzi_admin_app_secret', // id
			'Admin App Secret', // title
			array( $this, 'app_secret_field_callback' ), // callback
			'houzi-admin-api-admin', // page
			'houzi_admin_api_setting_section' // section
		);
		// if ( HOUZI_ADMIN_API_SHOW_EXPERIMENTAL_FEATURES == true) {
		// 	add_settings_field(
		// 		'onesingnal_app_id_0', // id
		// 		'OneSingnal APP ID', // title
		// 		array( $this, 'onesingnal_app_id_0_callback' ), // callback
		// 		'houzi-admin-api-admin', // page
		// 		'houzi_admin_api_setting_section' // section
		// 	);
		// }
		// if (HOUZI_ADMIN_API_SHOW_EXPERIMENTAL_FEATURES == true) {
		// 	add_settings_field(
		// 		'mobile_app_config_dev', // id
		// 		'App Config (Dev)', // title
		// 		array( $this, 'mobile_app_config_dev_callback' ), // callback
		// 		'houzi-admin-api-admin', // page
		// 		'houzi_admin_api_setting_section' // section
		// 	);
		// }
	}

	public function houzi_admin_api_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['fix_property_type_in_translation_0'] ) ) {
			$sanitary_values['fix_property_type_in_translation_0'] = $input['fix_property_type_in_translation_0'];
		}
		if ( isset( $input['onesingnal_app_id_0'] ) ) {
			$sanitary_values['onesingnal_app_id_0'] = sanitize_text_field( $input['onesingnal_app_id_0'] );
		}
		if ( isset( $input['nonce_security_disabled'] ) ) {
			$sanitary_values['nonce_security_disabled'] = sanitize_text_field( $input['nonce_security_disabled'] );
		}
		/*if ( isset( $input['houzi_admin_app_secret'] ) ) {
			$sanitary_values['houzi_admin_app_secret'] = sanitize_text_field( $input['houzi_admin_app_secret'] );
		}*/
		if ( isset( $input['mobile_app_config'] ) ) {
			$sanitary_values['mobile_app_config'] = esc_textarea( $input['mobile_app_config'] );
		}
		if ( isset( $input['mobile_app_config_dev'] ) ) {
			$sanitary_values['mobile_app_config_dev'] = esc_textarea( $input['mobile_app_config_dev'] );
		}

		return $sanitary_values;
	}

	public function houzi_admin_api_section_info() {
		
	}
	public function app_secret_field_callback() {
		$app_secret = get_option( 'houzi_admin_app_secret', '' );
		printf(
			'<input class="regular-text" type="text" name="houzi_admin_app_secret" id="houzi_admin_app_secret" value="%s" placeholder="Enter a secret key">
			<label for="houzi_admin_app_secret">
				<br>This will be matched with secret key sent from app.<br>So make sure to add this secret key in header hook in your app source. Read: <a  target="_blank" href="https://houzi-docs.booleanbites.com/tools/app_secret">Setup App Secret</><br>
			</label>
			',
			esc_attr( $app_secret )
		);
	}
	
	// public function onesingnal_app_id_0_callback() {
	// 	printf(
	// 		'<input class="regular-text" type="text" name="houzi_admin_api_options[onesingnal_app_id_0]" id="onesingnal_app_id_0" value="%s" placeholder="xxxxxxxx-xxx-xxxx-xxxx-xxxxxxxxxxxx">',
	// 		isset( $this->houzi_admin_api_options['onesingnal_app_id_0'] ) ? esc_attr( $this->houzi_admin_api_options['onesingnal_app_id_0']) : ''
	// 	);
	// }
	
	// public function mobile_app_config_dev_callback() {
	// 	printf(
	// 		'<textarea class="large-text" rows="15" placeholder="JSON config from Houzi Config desktop app" name="houzi_admin_api_options[mobile_app_config_dev]" id="mobile_app_config_dev">%s</textarea>',
	// 		isset( $this->houzi_admin_api_options['mobile_app_config_dev'] ) ? esc_attr( $this->houzi_admin_api_options['mobile_app_config_dev']) : ''
	// 	);
	// }
	

}
}


