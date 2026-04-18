<?php
/**
 * RevenueCat Purchases Functions
 *
 * Handles RevenueCat purchase data processing and storage.
 *
 * @link       https://booleanbites.com
 * @since      1.0.0
 *
 * @package    Houzi_Admin_Api
 * @subpackage Houzi_Admin_Api/functions
 * @author     Ahmad Nasir @ booleanbites
 * April 15, 2026
 */

add_action('rest_api_init', function () {
    register_rest_route('houzez-admin-api/v1', '/save-purchase', array(
        'methods'             => 'POST',
        'callback'            => 'handle_save_purchase',
        'permission_callback' => 'houzi_save_purchase_permission',
    ));
});

/**
 * Permission callback for /save-purchase.
 */
function houzi_save_purchase_permission() {
    if (!is_user_logged_in()) {
        return new WP_Error(
            'rest_forbidden',
            'You must be logged in to perform this action.',
            array('status' => 401)
        );
    }

    if (!current_user_can('manage_options')) {
        return new WP_Error(
            'rest_forbidden',
            'Only administrators can perform this action.',
            array('status' => 403)
        );
    }

    return true;
}

/**
 * Handles the save-purchase endpoint.
 *
 * It expects a JSON body (or POST fields) with:
 *   - original_app_user_id  (string, required) – the RevenueCat App User ID
 *   - expiry_date     (string, required) – ISO date the license expires, e.g. "2027-04-15"
 *
 * Stored in wp_options as:
 *   houzi_original_app_user_id
 *   houzi_license_expiry
 */
function handle_save_purchase(WP_REST_Request $request) {

    $params = $request->get_json_params();
    if (empty($params)) {
        $params = $request->get_body_params();
    }

    $original_app_user_id = isset($params['houzi_original_app_user_id']) ? sanitize_text_field($params['houzi_original_app_user_id']) : '';
    $expiry_date    = isset($params['expiry_date'])    ? sanitize_text_field($params['expiry_date'])    : '';
    
    if (empty($original_app_user_id)) {
        wp_send_json(
            array('success' => false, 'reason' => 'App User ID is required.'),
            400
        );
        return;
    }

    if (empty($expiry_date)) {
        wp_send_json(
            array('success' => false, 'reason' => 'Expiry Date is required.'),
            400
        );
        return;
    }


    $parsed_date = date_create($expiry_date);
    if (!$parsed_date) {
        wp_send_json(
            array('success' => false, 'reason' => 'Invalid Expiry Date format.'),
            422
        );
        return;
    }

    $parsed_expiry_date = $parsed_date->format('Y-m-d');

    update_option('houzi_original_app_user_id', $original_app_user_id);
    update_option('houzi_license_expiry', $parsed_expiry_date);


    wp_send_json(
        array(
            'success' => true,
            'message' => 'Subscription Purchased Successfully',
        ),
        200
    );
}