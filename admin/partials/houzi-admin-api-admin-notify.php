<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to show notification configuration area of the plugin
 *
 * @link       https://booleanbites.com
 * @since      1.1.5
 *
 * @package    Houzi_Admin_Api
 * @subpackage Houzi_Admin_Api/admin/partials
 * @author Hasnain Somro
 * Feb 17, 2023
 */
if ( ! class_exists( 'AdminApiNotify' ) ) {
class AdminApiNotify
{
    private $houzi_notify_options;

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
     * The UserNotification class instance
     *
     * @since    1.4.0.1
     * @access   private
     * @var      UserNotification    $user_notifications  user notificaition object.
     */
    private $user_notification;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.1.5
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        add_action('admin_init', array($this, 'houzi_notify_page_init'));

        add_action('wp_ajax_test_notification', array($this, 'test_notification'));

        add_action('send_houzi_notification', array($this, 'parse_notification_data'), 10, 1);

        add_action('houzez_send_notification', array($this, 'parse_notification_data'), 10, 1);

        // add_action('wp_mail', array($this, 'houzi_notify_email_handler'), 10, 1);

        add_action('update_option_houzi_notify_options', function ($old_value, $value) {
            do_action('litespeed_purge_all');
        }, 10, 2);

        $this->houzi_notify_options = get_option('houzi_notify_options');

        // Initialize the UserNotification class
        $this->user_notification = new AdminApiUserNotification();
    }

    function houzi_notify_email_handler($args)
    {
        $this->send_push_notification($args["subject"], $args["subject"], $args["to"]);
    }

    function parse_notification_data($args)
    {
        $title = $args["title"];
        $type = $args["type"];
        $notif_to = $args["to"];
        if (empty($notif_to)) {
        error_log('Email is required to send a notification.');
        return; 
        }

        if (strlen($title) < 1) {
            $title = fave_option('houzez_subject_' . $type);
            $title = apply_filters('wpml_translate_single_string', $title, 'admin_texts_houzez_options', 'houzez_email_subject_' . $title);
        }

        $user = get_user_by('email', $notif_to);
        if ($user) {
            $args['username'] = $user->user_login;
        }

        $args['website_name'] = get_option('blogname');
        $args['website_url'] = get_option('siteurl');
        $args['user_email'] = $notif_to;

        
        $message = $args["message"];
        $orignal_message = $args["message"];

        foreach ($args as $key => $val) {
            $title= str_replace('%' . $key, $val, $title);
            $message= str_replace('%' . $key, $val, $message);
        }
        
        $message = $this->remove_html_tags($message);

        // remove %abc type strings from the message
        $message = preg_replace('/%[^ ]*[\s]?/', '', $message);

        $title = str_replace(get_option('siteurl'), get_option('blogname'), $title);


        switch ($type) {
            case 'review':
                $author_id = get_post_field('post_author', $args['listing_id']);
                $author_email = get_the_author_meta('user_email', $author_id);

                $this->send_push_notification(
                    $title,
                    $message,
                    $author_email, 
                    $message,
                    array(
                        "type" => $type,
                        "listing_id" => $args['listing_id'],
                        "listing_title" => $args['listing_title'],
                        "review_post_type" => $args['review_post_type']
                    )
                );
                break;

            case 'matching_submissions':
                $message_trim = trim(substr($message, 0, 100)) . "...";

                $this->send_push_notification(
                    $title,
                    $message_trim,
                    $notif_to,
                    $message,
                    array(
                        "type" => $type,
                        "search_url" => $args['search_url']
                    )
                );
                break;

            case 'admin_free_submission_listing':
                $this->send_push_notification(
                    $title,
                    $message,
                    $notif_to,
                    $message,
                    array(
                        "type" => $type,
                        "listing_id" => $args['listing_id'],
                        "listing_title" => $args['listing_title'],
                        "listing_url" => $args['listing_url']
                    ),
                );
                break;

            case 'admin_update_listing':
                $this->send_push_notification(
                    $title,
                    $message,
                    $notif_to,
                    $message,
                    array(
                        "type" => $type,
                        "listing_id" => $args['listing_id'],
                        "listing_title" => $args['listing_title'],
                        "listing_url" => $args['listing_url']
                    )
                );
                break;

            case 'report':
                $message_trim = trim(substr($message, 0, 100)) . "...";

                $this->send_push_notification(
                    $title,
                    $message_trim,
                    $notif_to,
                    $message,
                    array(
                        "type" => $type,
                    )
                    
                );
                break;

            case 'messages':

                global $wpdb, $current_user;
                $current_user_id = get_current_user_id();
                $table = $wpdb->prefix . 'houzez_threads';

                $cleanThreadId = '';
                $property_id = '';
                $property_title = '';

                // Split the string based on 'thread_id='
                $strings_array = explode('thread_id=', $orignal_message);

                // String before 'thread_id='
                $beforeThreadId = $strings_array[0];

                // String after 'thread_id=', if it exists
                $afterThreadId = isset($strings_array[1]) ? $strings_array[1] : '';

                if (isset($afterThreadId) && !empty($afterThreadId)) {
                    // Further split the second part based on '&seen' to remove it and anything after it
                    // $afterThreadIdString = explode('&seen', $afterThreadId);
                    $afterThreadIdString = explode('&', $afterThreadId);

                    // Part before '&seen'
                    $cleanThreadId = $afterThreadIdString[0];
                }

                $thread_id = $cleanThreadId;
                $thread_id_int = intval($thread_id);

                $houzez_threads = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$table} WHERE id = %d",
                        $thread_id_int
                    )
                );

                foreach ($houzez_threads as $thread) {
                    if (isset($thread) && !empty($thread)) {
                        $property_id = $thread->property_id;
                        $property_title = get_post_field('post_title', $thread->property_id);
                        $sender_id = $thread->sender_id;
                        $sender_first_name = get_the_author_meta('first_name', $sender_id);
                        $sender_last_name = get_the_author_meta('last_name', $sender_id);
                        $sender_display_name = get_the_author_meta('display_name', $sender_id);
                        $sender_picture = get_the_author_meta('fave_author_custom_picture', $sender_id);

                        if (empty($sender_picture)) {
                            $sender_picture = get_template_directory_uri() . '/img/profile-avatar.png';
                        }

                        $receiver_id = $thread->receiver_id;
                        $receiver_first_name = get_the_author_meta('first_name', $receiver_id);
                        $receiver_last_name = get_the_author_meta('last_name', $receiver_id);
                        $receiver_display_name = get_the_author_meta('display_name', $receiver_id);
                        $receiver_picture = get_the_author_meta('fave_author_custom_picture', $receiver_id);

                        if (empty($receiver_picture)) {
                            $receiver_picture = get_template_directory_uri() . '/img/profile-avatar.png';
                        }
                    }
                }

                if (isset($property_title) && !empty($property_title)) {
                    $title = $property_title;
                }


                $clean_message = str_replace('Click here to see message on website dashboard.', '', $message);
                $clean_message = trim($clean_message);

                $this->send_push_notification(
                    $title,
                    empty($clean_message) ? "new message" : $clean_message,
                    $notif_to,
                    $clean_message,
                    array(
                        "type" => $type,
                        "thread_id" => $thread_id,
                        "property_id" => $property_id,
                        "property_title" => $property_title,
                        "sender_id" => $sender_id,
                        "sender_display_name" => $sender_display_name,
                        "sender_picture" => $sender_picture,
                        "receiver_id" => $receiver_id,
                        "receiver_display_name" => $receiver_display_name,
                        "receiver_picture" => $receiver_picture,
                    )
                );
                break;

            default:
                $this->send_push_notification(
                    $title,
                    $message,
                    $notif_to,
                    $message,
                    array(
                        "type" => $type
                    )
                );
                break;
        }
    }

    public function test_notification()
    {
        $original_app_user_id = get_option('houzi_original_app_user_id');
        $expiry_date    = get_option('houzi_license_expiry');

        if (empty($original_app_user_id) || empty($expiry_date)) {
            wp_send_json_error(array('reason' => 'Please save a valid license (App User ID and expiry) first.'));
            return;
        }

        $dataArray = $_POST['data'];
        $title = $dataArray['title'] ?? 'Houzi Test Notification';
        $message = $dataArray['message'] ?? 'This a test notification from your WordPress website';

        $current_user = wp_get_current_user();
        $email = $current_user->user_email;

        // Call the official send_push_notification method which handles Firebase & validation
        $this->send_push_notification($title, $message, $email, $message, array('type' => 'test'));
        
        wp_send_json_success(array('message' => 'Test notification request sent to Firebase.'));
    }

    public function send_push_notification($title, $message, $email, $message_full, $data = [])
    {
        if (empty($email)) {
            return;
        }

        // Save notification to the in-app notification store.
        if (!empty($data)) {
            $type = (array_key_exists('type', $data) && isset($data['type'])) ? $data['type'] : 'general';
            $this->user_notification->create_notification($email, $title, $message_full, $type, $data);
        }

        // --- License validity check ---
        $original_app_user_id = get_option('houzi_original_app_user_id');
        $expiry_date    = get_option('houzi_license_expiry');

        if (empty($original_app_user_id) || empty($expiry_date)) {
            error_log('Houzi push skipped: no App User ID or expiry stored.');
            return;
        }

        if (strtotime($expiry_date) < time()) {
            error_log('license expired on ' . $expiry_date);
            return;
        }

        // --- Firebase endpoint URL (hardcoded — clients cannot change this) ---
        $firebase_url = defined('HOUZI_FIREBASE_PUSH_URL') ? HOUZI_FIREBASE_PUSH_URL : '';

        if (empty($firebase_url)) {
            error_log('Firebase push URL not configured.');
            return;
        }

        // --- Build payload and call Firebase ---
        $notif_data  = $this->user_notification->get_user_new_notifications($email);
        $badge_count = $notif_data['num_notification'];
        $aliases     = array('external_id' => array(sha1($email)));

        $payload = array(
            'website_address'=> get_site_url(),
            'original_app_user_id' => $original_app_user_id,
            'notification_payload' => array(
                'headings'        => array('en' => $title),
                'contents'        => array('en' => $message),
                'include_aliases' => $aliases,
                'target_channel'  => 'push',
                'ios_badgeType'   => 'SetTo',
                'ios_badgeCount'  => $badge_count,
                'data'            => $data,
            )
        );

        $response = wp_remote_post($firebase_url, array(
            'headers'     => array('Content-Type' => 'application/json'),
            'body'        => wp_json_encode($payload),
            'timeout'     => 15,
            'data_format' => 'body',
        ));

        if (is_wp_error($response)) {
            error_log('Houzi push error: ' . $response->get_error_message());
        }

        // --- DEBUG LOG START (REMOVE IN PRODUCTION) ---
        $debug_logs = get_option('houzi_debug_push_logs', array());
        if (!is_array($debug_logs)) { 
            $debug_logs = array(); 
        }
        $log_entry = array(
            'time' => current_time('mysql'),
            'payload' => $payload,
            'response' => is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_body($response)
        );
        array_unshift($debug_logs, $log_entry);
        $debug_logs = array_slice($debug_logs, 0, 10); // Keep only last 10
        update_option('houzi_debug_push_logs', $debug_logs);
        // --- DEBUG LOG END ---
    }

    function remove_html_tags(string $text): string
    {
        $text = html_entity_decode($text);

        // Remove all HTML tags
        $text = strip_tags($text, '<br>'); // Keep <br> tags if needed, or remove them

        // Optional: Replace remaining <br> tags with actual newlines if desired
        $text = str_replace('<br>', "\n", $text);   
        // Create a regular expression that matches all HTML tags.
        $pattern = '/<[^>]+>/';

        // Replace all HTML line breaks with newline characters ("\n").
        $text = preg_replace('/<br(\s*)?\/?>/i', PHP_EOL, $text);

        // Convert newline characters to HTML line breaks.
        $text = nl2br($text);

        // Use the regular expression to replace all HTML tags with empty strings.
        $text = preg_replace($pattern, '', $text);

        return $text;
    }

    public function houzi_notify_page_init()
    {
        // No settings to register here anymore as OneSignal fields are removed.
        // Purchase token and Expiry are handled via the /save-purchase REST API.
    }

    public function houzi_notify_sanitize($input)
    {
        return array();
    }

    public function houzi_notify_section_info()
    {
    }

    public function onesingnal_app_id_callback()
    {
    }

    public function onesingnal_api_key_token_callback()
    {
    }

    public function houzi_notify_tab()
    {
        $original_app_user_id = get_option('houzi_original_app_user_id');
        $expiry_date    = get_option('houzi_license_expiry');
        $is_licensed    = !empty($original_app_user_id) && !empty($expiry_date);
        ?>
        <p>
            Secure push notifications are now handled via Firebase Cloud Functions. 
            The OneSignal configuration is managed securely on the server side.
        </p>

        <div class="card">
            <h2>License Status</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">License / App User Id</th>
                    <td>
                        <code><?php echo $original_app_user_id ? esc_html($original_app_user_id) : 'Not found'; ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Expiry Date</th>
                    <td>
                        <strong><?php echo $expiry_date ? esc_html($expiry_date) : 'Not found'; ?></strong>
                        <?php 
                        if ($expiry_date && strtotime($expiry_date) < time()) {
                            echo ' <span style="color:red;">(Expired)</span>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <hr style="border-top: 1px solid #bbb;">

        <div>
            <h2>Send OneSignal Notification Message (to Admins only)</h2>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">Notification Title</th>
                        <td>
                            <input class="regular-text" type="text" name="houzi_notify_options[notification_title]"
                                id="notification_title" value="Houzi Test Notification"
                                placeholder="Enter notification title here." required="">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Notification Message</th>
                        <td>
                            <input class="regular-text" type="text" name="houzi_notify_options[notification_message]"
                                id="notification_message" value="This a test notification from your WordPress website"
                                placeholder="Enter notification message here." required="">
                        </td>
                    </tr>
                </tbody>
            </table>
            <button id="test-one-signal-button" type="button" class="button button-primary" style="margin-bottom: 20px;">
                <?php esc_html_e('Send Notification Message', 'houzi'); ?>
            </button>
        </div>

        <!-- DEBUG LOG START (REMOVE IN WHEN uPLOADING rEMEMBER!) -->
        <hr style="border-top: 1px dashed #bbb; margin-top: 20px;">
        <div class="card" style="border-left: 4px solid #ffba00; max-width: 800px; padding: 20px;">
            <h2>Debug Logs (REMOVE IN PRODUCTION)</h2>
            <p>Recent triggers successfully sent to Firebase from Houzi Admin Plugin. Only the last 10 requests are logged.</p>
            <?php 
            if (isset($_GET['clear_houzi_logs'])) {
                delete_option('houzi_debug_push_logs');
            }
            $debug_logs = get_option('houzi_debug_push_logs', array());
            if (empty($debug_logs)) {
                echo '<p><i>No logs recorded yet. Send a test notification.</i></p>';
            } else {
                foreach ($debug_logs as $log) {
                    echo '<div style="background:#f9f9f9; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 4px;">';
                    echo '<strong>Time UTC:</strong> ' . esc_html($log['time']) . '<br/>';
                    echo '<strong style="display:block; margin-top:10px;">Payload Sent to Firebase:</strong><pre style="background:#fff; border:1px solid #ccc; padding:10px; overflow:auto;">' . esc_html(wp_json_encode($log['payload'], JSON_PRETTY_PRINT)) . '</pre>';
                    echo '<strong style="display:block; margin-top:10px;">Firebase Response:</strong><pre style="background:#fff; border:1px solid #ccc; padding:10px; overflow:auto;">' . esc_html($log['response']) . '</pre>';
                    echo '</div>';
                }
            }
            ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=' . sanitize_text_field($_GET['page']) . '&tab=notify&clear_houzi_logs=1')); ?>" class="button button-secondary">Clear Logs</a>
        </div>
        <!-- DEBUG LOG END -->
        <?php
    }
}
}
