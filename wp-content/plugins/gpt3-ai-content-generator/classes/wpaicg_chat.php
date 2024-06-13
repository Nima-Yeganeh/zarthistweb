<?php

namespace WPAICG;

if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\WPAICG\\WPAICG_Chat')) {
    class WPAICG_Chat
    {
        private static $instance = null;

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'wpaicg_menu' ) );
            add_shortcode( 'wpaicg_chatgpt', [ $this, 'wpaicg_chatbox' ] );
            add_shortcode( 'wpaicg_chatgpt_widget', [ $this, 'wpaicg_chatbox_widget' ] );
            add_action( 'wp_ajax_wpaicg_update_chatbot', array( $this, 'wpaicg_update_chatbot' ) );
            add_action( 'wp_ajax_wpaicg_chatbox_message', array( $this, 'wpaicg_chatbox_message' ) );
            add_action( 'wp_ajax_nopriv_wpaicg_chatbox_message', array( $this, 'wpaicg_chatbox_message' ) );
            add_action( 'wp_ajax_wpaicg_chat_shortcode_message', array( $this, 'wpaicg_chatbox_message' ) );
            add_action( 'wp_ajax_nopriv_wpaicg_chat_shortcode_message', array( $this, 'wpaicg_chatbox_message' ) );
            // wpaicg_reset_settings
            add_action( 'wp_ajax_wpaicg_reset_settings', array( $this, 'wpaicg_reset_settings' ) );
            add_action( 'wp_ajax_nopriv_wpaicg_reset_settings', array( $this, 'wpaicg_reset_settings' ) );
            // wpaicg_export_settings
            add_action( 'wp_ajax_wpaicg_export_settings', array( $this, 'wpaicg_export_settings' ) );
            add_action( 'wp_ajax_nopriv_wpaicg_export_settings', array( $this, 'wpaicg_export_settings' ) );
            // wpaicg_import_settings
            add_action( 'wp_ajax_wpaicg_import_settings', array( $this, 'wpaicg_import_settings' ) );
            add_action( 'wp_ajax_nopriv_wpaicg_import_settings', array( $this, 'wpaicg_import_settings' ) );
            add_action('init', array($this, 'wpaicg_handle_delete_logs'));
            add_action('wp_ajax_wpaicg_export_logs', array($this, 'wpaicg_export_logs_callback'));
            if ( ! wp_next_scheduled( 'wpaicg_remove_chat_tokens_limited' ) ) {
                wp_schedule_event( time(), 'hourly', 'wpaicg_remove_chat_tokens_limited' );
            }
            add_action( 'wpaicg_remove_chat_tokens_limited', array( $this, 'wpaicg_remove_chat_tokens' ) );
        }


        public function wpaicg_reset_settings() {
            if (!wp_verify_nonce($_REQUEST['nonce'], 'wpaicg_reset_settings')) {
                wp_send_json_error(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }
        
            if (!current_user_can('manage_options')) {
                wp_send_json_error(esc_html__('You do not have sufficient permissions to access this page.', 'gpt3-ai-content-generator'));
            }
        
            $source = isset($_REQUEST['source']) ? sanitize_text_field($_REQUEST['source']) : '';
            $success = true;
            $message = '';
        
            switch ($source) {
                case 'shortcode':
                    $options_to_delete = [
                        'wpaicg_chat_shortcode_options',
                        'wpaicg_shortcode_stream',
                        'wpaicg_shortcode_google_model',
                        'wpaicg_conversation_starters',
                    ];
                    foreach ($options_to_delete as $option) {
                        if (get_option($option) !== false) {
                            $success = $success && delete_option($option);
                        }
                    }
                    break;
                case 'widget':
                    $widget_options = [
                        '_wpaicg_chatbox_you',
                        '_wpaicg_ai_thinking',
                        '_wpaicg_typing_placeholder',
                        '_wpaicg_chatbox_welcome_message',
                        '_wpaicg_chatbox_ai_name',
                        'wpaicg_chat_model',
                        'wpaicg_chat_temperature',
                        'wpaicg_chat_max_tokens',
                        'wpaicg_chat_top_p',
                        'wpaicg_chat_best_of',
                        'wpaicg_chat_frequency_penalty',
                        'wpaicg_chat_presence_penalty',
                        'wpaicg_chat_widget',
                        'wpaicg_chat_language',
                        'wpaicg_conversation_cut',
                        'wpaicg_chat_embedding',
                        'wpaicg_chat_addition',
                        'wpaicg_chat_addition_text',
                        'wpaicg_chat_no_answer',
                        'wpaicg_chat_embedding_type',
                        'wpaicg_chat_embedding_top',
                        'wpaicg_widget_google_model',
                        'wpaicg_conversation_starters_widget',
                        'wpaicg_widget_openrouter_model',
                    ];
                    foreach ($widget_options as $option) {
                        if (get_option($option) !== false) {
                            $success = $success && delete_option($option);
                        }
                    }
                    break;
                default:
                    $success = false;
                    $message = esc_html__('Invalid source specified.', 'gpt3-ai-content-generator');
                    break;
            }
        
            if ($success) {
                $message = esc_html__('Settings reset successfully', 'gpt3-ai-content-generator');
                wp_send_json_success($message);
            } else {
                if (empty($message)) {
                    $message = esc_html__('Settings reset failed', 'gpt3-ai-content-generator');
                }
                wp_send_json_error($message);
            }
        }

        function wpaicg_export_settings() {

            global $wpdb, $wp_filesystem;
        
            // Verify the nonce for security wpaicg_export_settings
            if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'wpaicg_export_settings')) {
                wp_send_json_error(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
            }
        
            // Ensure only admins can execute this function
            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'gpt3-ai-content-generator'));
            }
        
            // Include the WP_Filesystem class and initialize it
            if (!function_exists('WP_Filesystem')) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            WP_Filesystem();
        
            // Determine the source and collect the relevant settings
            $source = isset($_REQUEST['source']) ? sanitize_text_field($_REQUEST['source']) : '';
            $settings = array();

            if ($source === 'bot') {
                // Fetch all bots
                $bots = $wpdb->get_results($wpdb->prepare("SELECT post_content, post_title, post_name FROM {$wpdb->posts} WHERE post_type = %s", 'wpaicg_chatbot'), ARRAY_A);

                foreach ($bots as $bot) {
                    // Check if post_content is not empty
                    if (!empty($bot['post_content'])) {
                        $settings[] = array(
                            'post_title' => $bot['post_title'],
                            'post_name' => $bot['post_name'],
                            // If serialization is expected, use maybe_unserialize; otherwise, remove it
                            'post_content' => maybe_unserialize($bot['post_content']),
                        );
                    }
                }
            }

            switch ($source) {
                case 'shortcode':
                    $settings['shortcode_settings'] = get_option('wpaicg_chat_shortcode_options', array());
                    $settings['shortcode_stream'] = get_option('wpaicg_shortcode_stream', array());
                    break;
                case 'widget':
                    // Add all widget-related option keys here
                    $widget_options = array(
                        'wpaicg_chat_widget',
                        '_wpaicg_chatbox_you',
                        '_wpaicg_ai_thinking',
                        '_wpaicg_typing_placeholder',
                        '_wpaicg_chatbox_welcome_message',
                        '_wpaicg_chatbox_ai_name',
                        'wpaicg_chat_model',
                        'wpaicg_chat_temperature',
                        'wpaicg_chat_max_tokens',
                        'wpaicg_chat_top_p',
                        'wpaicg_chat_best_of',
                        'wpaicg_chat_frequency_penalty',
                        'wpaicg_chat_presence_penalty',
                        'wpaicg_chat_language',
                        'wpaicg_conversation_cut',
                        'wpaicg_chat_embedding',
                        'wpaicg_chat_addition',
                        'wpaicg_chat_addition_text',
                        'wpaicg_chat_no_answer',
                        'wpaicg_chat_embedding_type',
                        'wpaicg_chat_embedding_top',
                        'wpaicg_widget_openrouter_model',
                    );
                    foreach ($widget_options as $option_key) {
                        $settings[$option_key] = get_option($option_key, '');
                    }
                    break;
                case 'bot':
                    // Add bot-related settings collection here
                    break;
            }
        
            // Serialize settings to JSON
            $json_content = json_encode($settings);
        
            // Save to file in uploads directory
            $upload_dir = wp_upload_dir();

            $file_name = 'settings_export_' . $source . '_' . wp_rand() . '.json';
            $file_path = $upload_dir['basedir'] . '/' . $file_name;
        
            // Use WP_Filesystem to write the content to the file
            if ($wp_filesystem->put_contents($file_path, $json_content)) {
                // Provide the download URL or a success message with the URL
                wp_send_json_success(array('url' => $upload_dir['baseurl'] . '/' . $file_name));
            } else {
                wp_send_json_error(esc_html__('Failed to export settings.', 'gpt3-ai-content-generator'));
            }
        }
        
        function wpaicg_import_settings() {
            // Security checks
            if (!check_ajax_referer('wpaicg_import_settings_nonce', 'nonce', false)) {
                wp_send_json_error('Nonce verification failed');
            }
        
            if (!current_user_can('manage_options')) {
                wp_send_json_error('You do not have sufficient permissions');
            }
        
            // Check if file is uploaded
            if (isset($_FILES['file']['tmp_name'])) {
                $file_contents = file_get_contents($_FILES['file']['tmp_name']);
                $data = json_decode($file_contents, true);
        
                if (json_last_error() !== JSON_ERROR_NONE) {
                    wp_send_json_error('Invalid JSON file');
                }
        
                // Validate and import settings based on the source
                $source = isset($_POST['source']) ? sanitize_text_field($_POST['source']) : '';
        
                if ($source === 'shortcode' && isset($data['shortcode_settings'])) {
                    update_option('wpaicg_chat_shortcode_options', $data['shortcode_settings']);
                    if (isset($data['shortcode_stream'])) {
                        update_option('wpaicg_shortcode_stream', $data['shortcode_stream']);
                    }
                    wp_send_json_success('Settings imported successfully');
                } elseif ($source === 'widget') {
                    // Check if the primary widget settings key exists
                    if (isset($data['wpaicg_chat_widget'])) {
                        // Update the main widget settings option
                        update_option('wpaicg_chat_widget', $data['wpaicg_chat_widget']);

                        // For additional individual settings, ensure they exist and then update
                        $additional_widget_settings = [
                            '_wpaicg_chatbox_you',
                            '_wpaicg_ai_thinking',
                            '_wpaicg_typing_placeholder',
                            '_wpaicg_chatbox_welcome_message',
                            '_wpaicg_chatbox_ai_name',
                            'wpaicg_chat_model',
                            'wpaicg_chat_temperature',
                            'wpaicg_chat_max_tokens',
                            'wpaicg_chat_top_p',
                            'wpaicg_chat_best_of',
                            'wpaicg_chat_frequency_penalty',
                            'wpaicg_chat_presence_penalty',
                            'wpaicg_chat_language',
                            'wpaicg_conversation_cut',
                            'wpaicg_chat_embedding',
                            'wpaicg_chat_addition',
                            'wpaicg_chat_addition_text',
                            'wpaicg_chat_no_answer',
                            'wpaicg_chat_embedding_type',
                            'wpaicg_chat_embedding_top',
                            'wpaicg_widget_openrouter_model'
                        ];

                        foreach ($additional_widget_settings as $setting) {
                            if (isset($data[$setting])) {
                                update_option($setting, $data[$setting]);
                            }
                        }

                        wp_send_json_success('Widget settings imported successfully');
                    } else {
                        wp_send_json_error('Widget settings key missing in the provided file');
                    }
                } elseif ($source === 'bot') {
                    if (!empty($data) && is_array($data)) {
                        foreach ($data as $botData) {
                            // Assuming each bot's data includes post_content, post_title, and post_name
                            $botPost = [
                                'post_title' => sanitize_text_field($botData['post_title']),
                                'post_name' => sanitize_text_field($botData['post_name']),
                                'post_content' => wp_kses_post($botData['post_content']),
                                'post_status' => 'publish',
                                'post_type' => 'wpaicg_chatbot',
                            ];
                
                            // Insert new bot post into the database
                            wp_insert_post($botPost);
                        }
                        wp_send_json_success('Bots imported successfully');
                    } else {
                        wp_send_json_error('Invalid bot data format');
                    }
                }
                else {
                    wp_send_json_error('Incorrect or missing settings for the selected source');
                }
            } else {
                wp_send_json_error('No file uploaded');
            }
        }

        function wpaicg_export_logs_callback() {
            global $wpdb, $wp_filesystem;

            // Verify the nonce wpaicg_export_logs_nonce
            if ( ! isset($_REQUEST['nonce']) || ! wp_verify_nonce($_REQUEST['nonce'], 'wpaicg_export_logs_nonce') ) {
                die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
            }

            // Ensure only admins can execute this function
            if ( ! current_user_can('manage_options') ) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }

            // Include the WP_Filesystem class and initialize it
            if ( ! function_exists('WP_Filesystem') ) {
                require_once(ABSPATH . 'wp-admin/includes/file.php');
            }
            WP_Filesystem();
            
            $logs_query = "SELECT `data` FROM " . $wpdb->prefix . "wpaicg_chatlogs";
            $logs = $wpdb->get_results($logs_query, ARRAY_A);
        
            $content = '';
            foreach ($logs as $log) {
                $content .= $log['data'] . "\n\n";
            }
        
            $upload_dir = wp_upload_dir();

            $file_name = 'wpaicg_chat_logs_' . wp_rand() . '.txt';
            $file_path = $upload_dir['basedir'] . '/' . $file_name;
            $file_url = $upload_dir['baseurl'] . '/' . $file_name;
        
            // Use WP_Filesystem to write the content to the file
            $wp_filesystem->put_contents($file_path, $content);

            // Set the transient to the file URL
            set_transient('wpaicg_logs_exported_url', $file_url, 10); // This will expire in 10 seconds

            // Redirect after deletion to prevent form resubmission and to load fresh data
            wp_redirect(admin_url('admin.php?page=wpaicg_chatgpt&action=logs'));
            wp_die();
        }

        public function wpaicg_update_chatbot()
        {
            global $wpdb;
            $wpaicg_result = array('status' => 'error', 'msg' => esc_html__('Something went wrong','gpt3-ai-content-generator'));
            if(!current_user_can('wpaicg_chatgpt_bots')){
                $wpaicg_result['msg'] = esc_html__('You do not have permission for this action.','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
            }
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wpaicg_chatbot_save' ) ) {
                $wpaicg_result['msg'] = esc_html__('Nonce verification failed','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
            }
            if(isset($_REQUEST['bot']) && is_array($_REQUEST['bot'])){
                $bot_id = isset($_REQUEST['bot']['id']) ? intval($_REQUEST['bot']['id']) : 0;

                $conversation_starters = isset($_REQUEST['bot']['conversation_starters']) ? wp_unslash($_REQUEST['bot']['conversation_starters']) : array();
                // Extract the footer_text field before sanitization
                $footer_text = isset($_REQUEST['bot']['footer_text']) ? wp_kses_post($_REQUEST['bot']['footer_text']) : '';

                // Remove the footer_text field from the bot array temporarily
                unset($_REQUEST['bot']['footer_text'], $_REQUEST['bot']['conversation_starters']);

                $bot = wpaicg_util_core()->sanitize_text_or_array_field($_REQUEST['bot']);

                // Ensure 'use_default_embedding' is set appropriately
                $bot['use_default_embedding'] = isset($_REQUEST['bot']['use_default_embedding']) ? '1' : '0';

                // Reintroduce the footer_text field into the bot array or process separately as needed
                $bot['footer_text'] = $footer_text;
                $bot['conversation_starters'] = $conversation_starters;

                if(isset($bot['id']) && !empty($bot['id'])){
                    $wpaicg_chatbot_id = $bot['id'];
                    wp_update_post(array(
                        'ID' => $bot['id'],
                        'post_title' => $bot['name'],
                        // We're adding slashes to the JSON string before saving it to the database. Added by Hung Le.
                        'post_content' => wp_slash(wp_json_encode($bot, JSON_UNESCAPED_UNICODE))
                    ));
                }
                else{
                    $wpaicg_chatbot_id = wp_insert_post(array(
                        'post_title' => $bot['name'],
                        // We're adding slashes to the JSON string before saving it to the database. Added by Hung Le.
                        'post_content' => wp_slash(wp_json_encode($bot, JSON_UNESCAPED_UNICODE)),
                        'post_type' => 'wpaicg_chatbot',
                        'post_status' => 'publish'
                    ));
                }
                $wpdb->query($wpdb->prepare("DELETE FROM ".$wpdb->postmeta." WHERE post_id=%d",$wpaicg_chatbot_id));
                if(isset($bot['type']) && $bot['type'] == 'widget' && isset($bot['pages']) && !empty($bot['pages'])){
                    $pages = array_map('trim', explode(',', $bot['pages']));
                    foreach($pages as $page){
                        add_post_meta($wpaicg_chatbot_id,'wpaicg_widget_page_'.$page,'yes');
                    }
                }
                $wpaicg_result['status'] = 'success';
            }
            wp_send_json($wpaicg_result);
        }

        public function wpaicg_remove_chat_tokens()
        {
            global $wpdb;
            $wpaicg_chat_shortcode_options = get_option('wpaicg_chat_shortcode_options',[]);
            $wpaicg_chat_widget = get_option('wpaicg_chat_widget', []);
            $widget_reset_limit = isset($wpaicg_chat_widget['reset_limit']) && !empty($wpaicg_chat_widget['reset_limit']) ? $wpaicg_chat_widget['reset_limit'] : 0;
            $shortcode_reset_limit = isset($wpaicg_chat_shortcode_options['reset_limit']) && !empty($wpaicg_chat_shortcode_options['reset_limit']) ? $wpaicg_chat_shortcode_options['reset_limit'] : 0;
            if($widget_reset_limit > 0) {
                $widget_time = time() - ($widget_reset_limit * 86400);
                $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "wpaicg_chattokens WHERE source='widget' AND created_at < %s",$widget_time));
            }
            if($shortcode_reset_limit > 0) {
                $shortcode_time = time() - ($shortcode_reset_limit * 86400);
                $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "wpaicg_chattokens WHERE source='shortcode' AND created_at < %s",$shortcode_time));
            }
            // New code to handle custom bots
            $custom_bots = $wpdb->get_results("SELECT ID, post_content FROM {$wpdb->posts} WHERE post_type = 'wpaicg_chatbot'");
            foreach ($custom_bots as $bot) {
                $content = json_decode($bot->post_content, true);
                $reset_limit = isset($content['reset_limit']) ? (int) $content['reset_limit'] : 0;
                if ($reset_limit > 0) {
                    $bot_type = isset($content['type']) && in_array(strtolower($content['type']), ['widget', 'shortcode']) ? ucfirst($content['type']) : '';
                    $source_key = $bot_type . ' ID: ' . $bot->ID;
                    $time_threshold = time() - ($reset_limit * 86400);
                    $wpdb->query($wpdb->prepare(
                        "DELETE FROM {$wpdb->prefix}wpaicg_chattokens WHERE source = %s AND created_at < %s",
                        $source_key, $time_threshold
                    ));
                }
            }
        }

        public function wpaicg_menu()
        {
            add_submenu_page(
                'wpaicg',
                esc_html__('ChatGPT','gpt3-ai-content-generator'),
                esc_html__('ChatGPT','gpt3-ai-content-generator'),
                'wpaicg_chatgpt',
                'wpaicg_chatgpt',
                array( $this, 'wpaicg_chatmode' ),
                1
            );
        }

        public function wpaicg_chatmode()
        {
            include WPAICG_PLUGIN_DIR . 'admin/extra/wpaicg_chatmode.php';
        }

        function wpaicg_handle_delete_logs() {
    
            global $wpdb;
            if (isset($_GET['wpaicg_delete_all_logs'])) {
                // Verify the delete nonce
                if (!isset($_GET['wpaicg_delete_nonce']) || !wp_verify_nonce($_GET['wpaicg_delete_nonce'], 'wpaicg_chatlogs_delete_nonce')) {
                    die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
                }

                $where = ' WHERE 1=1'; // Default condition

                // Check for search keyword filter
                if (isset($_GET['wsearch']) && !empty($_GET['wsearch'])) {
                    $search = sanitize_text_field($_GET['wsearch']);
                    $where .= $wpdb->prepare(" AND `data` LIKE %s", '%' . $wpdb->esc_like($search) . '%');
                }

                // Check for date range filter
                if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
                    $start_date = sanitize_text_field($_GET['start_date']);
                    $start_timestamp = strtotime($start_date);  // Start of the selected start date
                    $where .= $wpdb->prepare(" AND created_at >= %d", $start_timestamp);
                }

                if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
                    $end_date = sanitize_text_field($_GET['end_date']);
                    $end_of_day_timestamp = strtotime($end_date . ' +1 day');  // Start of the next day after the selected end date
                    $where .= $wpdb->prepare(" AND created_at <= %d", $end_of_day_timestamp);
                }

                // Check for source filter
                if (isset($_GET['source']) && !empty($_GET['source'])) {
                    $source = sanitize_text_field($_GET['source']);
                    if ($source == "shortcode") {
                        $where .= " AND source LIKE 'Shortcode%'";
                    } else {
                        $where .= $wpdb->prepare(" AND source = %s", $source);
                    }
                }

                // Delete the records based on applied filters
                $wpdb->query("DELETE FROM " . $wpdb->prefix . "wpaicg_chatlogs" . $where);

                if ($wpdb->rows_affected > 0) {
                    set_transient('wpaicg_logs_deleted', true, 10); // This will expire in 10 seconds
                } else {
                    set_transient('wpaicg_no_logs_to_delete', true, 10); // This will expire in 10 seconds
                }
                
                // Redirect after deletion to prevent form resubmission and to load fresh data
                wp_redirect(admin_url('admin.php?page=wpaicg_chatgpt&action=logs'));
                exit;
            }

            // Handle individual log deletion
            if (isset($_GET['delete_log_id'])) {
                // Verify the delete nonce for individual log
                if (!isset($_GET['wpaicg_delete_nonce']) || !wp_verify_nonce($_GET['wpaicg_delete_nonce'], 'wpaicg_chatlogs_delete_nonce')) {
                    die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
                }
                
                $log_id = intval($_GET['delete_log_id']);
                $wpdb->delete($wpdb->prefix . "wpaicg_chatlogs", array('id' => $log_id), array('%d'));

                set_transient('wpaicg_log_deleted', true, 10); // This will expire in 10 seconds

                // Redirect after individual log deletion to prevent form resubmission and to load fresh data
                wp_redirect(admin_url('admin.php?page=wpaicg_chatgpt&action=logs'));
                exit;
            }
        }

        public function wpaicg_chatbox_message()
        {
            $wpaicg_result = [
                'status' => 'error',
                'msg'    => esc_html__('Something went wrong', 'gpt3-ai-content-generator'),
            ];
        
            // Nonce verification
            $wpaicg_nonce = sanitize_text_field($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($wpaicg_nonce, 'wpaicg-chatbox')) {
                $wpaicg_result['msg'] = esc_html__('Nonce verification failed', 'gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
                exit;
            }
        
            global $wpdb;
    
            // check client id
            if (isset($_REQUEST['wpaicg_chat_client_id']) && !empty($_REQUEST['wpaicg_chat_client_id'])) {
                $wpaicg_client_id = sanitize_text_field($_REQUEST['wpaicg_chat_client_id']);
            } else {
                error_log('wpaicg_chat_client_id is not set in the request');
            }

            // Get the default provider option
            $default_provider = get_option('wpaicg_provider', 'OpenAI');
            $wpaicg_provider = $default_provider;

            // Check for bot_id first
            if (isset($_REQUEST['bot_id']) && intval($_REQUEST['bot_id']) > 0) {
                $bot_id = intval($_REQUEST['bot_id']);
                $post = get_post($bot_id);
                if ($post) {
                    $post_content = $post->post_content;
                    $post_content_json = json_decode($post_content, true);
                    $wpaicg_provider = isset($post_content_json['provider']) && !empty($post_content_json['provider']) ? sanitize_text_field($post_content_json['provider']) : $default_provider;
                }
            } elseif (isset($_REQUEST['chatbot_identity'])) {
                $chatbot_identity = sanitize_text_field($_REQUEST['chatbot_identity']);
                if ($chatbot_identity === 'shortcode') {
                    $shortcode_options = get_option('wpaicg_chat_shortcode_options');
                    $wpaicg_provider = isset($shortcode_options['provider']) ? sanitize_text_field($shortcode_options['provider']) : $default_provider;
                } elseif ($chatbot_identity === 'widget') {
                    if (isset($_POST['wpaicg_chat_widget']['provider']) && !empty($_POST['wpaicg_chat_widget']['provider'])) {
                        $wpaicg_provider = sanitize_text_field($_POST['wpaicg_chat_widget']['provider']);
                    } else {
                        $widget_options = get_option('wpaicg_chat_widget');
                        $wpaicg_provider = isset($widget_options['provider']) ? sanitize_text_field($widget_options['provider']) : $default_provider;
                    }
                } else {
                    // Handle other custom chatbot identities if needed
                    $wpaicg_provider = $default_provider;
                }
            } else {
                // Fallback to default provider if no specific identity or bot_id is found
                $wpaicg_provider = $default_provider;
            }

            $open_ai = WPAICG_OpenAI::get_instance()->openai();

            // Determine AI engine based on provider
            switch ($wpaicg_provider) {
                case 'Google':
                    $open_ai = WPAICG_Google::get_instance();
                    break;
                case 'Azure':
                    $open_ai = WPAICG_AzureAI::get_instance()->azureai();
                    break;
                case 'OpenAI':
                    $open_ai = WPAICG_OpenAI::get_instance()->openai();
                    break;
                case 'OpenRouter':
                    $open_ai = WPAICG_OpenRouter::get_instance()->openai();
                    break;
                default:
                    $open_ai = WPAICG_OpenAI::get_instance()->openai();
                    break;
            }
        
            if (!$open_ai) {
                $wpaicg_result['msg'] = esc_html__('Unable to initialize the AI instance. Please make sure the API key is valid.', 'gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
                exit;
            }

            $wpaicg_save_request = false;

            // Get message and URL
            $wpaicg_message = sanitize_text_field($_REQUEST['message'] ?? '');
            $url = sanitize_text_field($_REQUEST['url'] ?? '');

            $wpaicg_pinecone_api = get_option('wpaicg_pinecone_api', '');
            $wpaicg_pinecone_environment = get_option('wpaicg_pinecone_environment', '');
            $wpaicg_total_tokens = 0;
            $wpaicg_limited_tokens = false;
            $wpaicg_token_usage_client = 0;
            $wpaicg_token_limit_message = esc_html__('You have reached your token limit.','gpt3-ai-content-generator');
            $wpaicg_limited_tokens_number = 0;
            $wpaicg_chat_source = 'widget';
            $wpaicg_chat_temperature = get_option('wpaicg_chat_temperature',$open_ai->temperature);
            $wpaicg_chat_max_tokens = get_option('wpaicg_chat_max_tokens',$open_ai->max_tokens);
            $wpaicg_chat_top_p = get_option('wpaicg_chat_top_p',$open_ai->top_p);
            $wpaicg_chat_best_of = get_option('wpaicg_chat_best_of',$open_ai->best_of);
            $wpaicg_chat_frequency_penalty = get_option('wpaicg_chat_frequency_penalty',$open_ai->frequency_penalty);
            $wpaicg_chat_presence_penalty = get_option('wpaicg_chat_presence_penalty',$open_ai->presence_penalty);
            if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'wpaicg_chat_shortcode_message') {
                $wpaicg_chat_source = 'shortcode';
            }

            $use_default_embedding = true;
            $selected_embedding_model = '';
            $selected_embedding_provider = '';

            $wpaicg_moderation = false;
            $wpaicg_moderation_model = 'text-moderation-latest';
            $wpaicg_moderation_notice = esc_html__('Your message has been flagged as potentially harmful or inappropriate. Please ensure that your messages are respectful and do not contain language or content that could be offensive or harmful to others. Thank you for your cooperation.','gpt3-ai-content-generator');
            if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'wpaicg_chat_shortcode_message'){
                $table = $wpdb->prefix . 'wpaicg';
                $existingValue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE name = %s", 'wpaicg_settings' ), ARRAY_A );
                $wpaicg_chat_shortcode_options = get_option('wpaicg_chat_shortcode_options',[]);
                $default_setting = array(
                    'provider' => 'OpenAI',
                    'language' => 'en',
                    'tone' => 'friendly',
                    'profession' => 'none',
                    'model' => 'gpt-3.5-turbo',
                    'temperature' => $existingValue['temperature'],
                    'max_tokens' => $existingValue['max_tokens'],
                    'top_p' => $existingValue['top_p'],
                    'best_of' => $existingValue['best_of'],
                    'frequency_penalty' => $existingValue['frequency_penalty'],
                    'presence_penalty' => $existingValue['presence_penalty'],
                    'ai_name' => esc_html__('AI','gpt3-ai-content-generator'),
                    'you' => esc_html__('You','gpt3-ai-content-generator'),
                    'ai_thinking' => esc_html__('Gathering thoughts','gpt3-ai-content-generator'),
                    'placeholder' => esc_html__('Type a message','gpt3-ai-content-generator'),
                    'welcome' => esc_html__('Hello human, I am a GPT powered AI chat bot. Ask me anything!','gpt3-ai-content-generator'),
                    'remember_conversation' => 'yes',
                    'conversation_cut' => 10,
                    'content_aware' => 'yes',
                    'embedding' =>  false,
                    'embedding_type' =>  false,
                    'embedding_top' =>  false,
                    'embedding_index' => '',
                    'no_answer' => '',
                    'fontsize' => 13,
                    'fontcolor' => '#495057',
                    'user_bg_color' => '#ccf5e1',
                    'ai_bg_color' => '#d1e8ff',
                    'ai_icon_url' => '',
                    'ai_icon' => 'default',
                    'use_avatar' => false,
                    'save_logs' => false,
                    'chat_addition' => false,
                    'chat_addition_text' => '',
                    'user_aware' => 'no',
                    'user_limited' => false,
                    'guest_limited' => false,
                    'user_tokens' => 0,
                    'limited_message'=> esc_html__('You have reached your token limit.','gpt3-ai-content-generator'),
                    'guest_tokens' => 0,
                    'moderation' => false,
                    'moderation_model' => 'text-moderation-latest',
                    'moderation_notice' => esc_html__('Your message has been flagged as potentially harmful or inappropriate. Please ensure that your messages are respectful and do not contain language or content that could be offensive or harmful to others. Thank you for your cooperation.','gpt3-ai-content-generator'),
                    'role_limited' => false,
                    'limited_roles' => [],
                    'log_request' => false,
                    'vectordb' => 'pinecone',
                    'qdrant_collection' => '',
                    'use_default_embedding' => true,
                    'embedding_model' => '',
                    'embedding_provider' => '',
                );
                $wpaicg_settings = shortcode_atts($default_setting, $wpaicg_chat_shortcode_options);

                if(isset($_REQUEST['wpaicg_chat_shortcode_options']) && is_array($_REQUEST['wpaicg_chat_shortcode_options'])){
                    $wpaicg_chat_shortcode_options = wpaicg_util_core()->sanitize_text_or_array_field($_REQUEST['wpaicg_chat_shortcode_options']);
                    $wpaicg_settings = shortcode_atts($wpaicg_settings, $wpaicg_chat_shortcode_options);
                }
                $wpaicg_save_request = isset($wpaicg_settings['log_request']) && $wpaicg_settings['log_request'] ? true : false;
                $wpaicg_chat_embedding = isset($wpaicg_settings['embedding']) && $wpaicg_settings['embedding'] ? true : false;
                $wpaicg_chat_embedding_type = isset($wpaicg_settings['embedding_type']) ? $wpaicg_settings['embedding_type'] : '' ;
                $wpaicg_chat_no_answer = isset($wpaicg_settings['no_answer']) ? $wpaicg_settings['no_answer'] : '' ;
                $wpaicg_chat_embedding_top = isset($wpaicg_settings['embedding_top']) ? $wpaicg_settings['embedding_top'] : 1 ;
                $wpaicg_chat_no_answer = empty($wpaicg_chat_no_answer) ? 'I dont know' : $wpaicg_chat_no_answer;
                $wpaicg_chat_with_embedding = false;
                $wpaicg_chat_language = isset($wpaicg_settings['language']) ? $wpaicg_settings['language'] : 'en' ;
                $wpaicg_chat_tone = isset($wpaicg_settings['tone']) ? $wpaicg_settings['tone'] : 'friendly' ;
                $wpaicg_chat_proffesion = isset($wpaicg_settings['profession']) ? $wpaicg_settings['profession'] : 'none' ;
                $wpaicg_chat_remember_conversation = isset($wpaicg_settings['remember_conversation']) ? $wpaicg_settings['remember_conversation'] : 'yes' ;
                $wpaicg_chat_content_aware = isset($wpaicg_settings['content_aware']) ? $wpaicg_settings['content_aware'] : 'yes' ;
                $wpaicg_chat_vectordb = isset($wpaicg_settings['vectordb']) ? $wpaicg_settings['vectordb'] : 'pinecone' ;
                $wpaicg_chat_qdrant_collection = isset($wpaicg_settings['qdrant_collection']) ? $wpaicg_settings['qdrant_collection'] : '' ;

                $use_default_embedding = isset($wpaicg_settings['use_default_embedding']) ? $wpaicg_settings['use_default_embedding'] : true;
                $selected_embedding_model = isset($wpaicg_settings['embedding_model']) ? $wpaicg_settings['embedding_model'] : "";
                $selected_embedding_provider = isset($wpaicg_settings['embedding_provider']) ? $wpaicg_settings['embedding_provider'] : "";

                $wpaicg_ai_model = isset($wpaicg_settings['model']) ? $wpaicg_settings['model'] : 'gpt-3.5-turbo' ;

                // if OpenAI or OpenRouter
                if ($wpaicg_provider === 'OpenAI' || $wpaicg_provider === 'OpenRouter') {
                    $wpaicg_ai_model = isset($wpaicg_settings['model']) ? $wpaicg_settings['model'] : 'gpt-3.5-turbo';
                } elseif ($wpaicg_provider === 'Azure') {
                    $wpaicg_ai_model = get_option('wpaicg_azure_deployment', ''); 
                }  elseif ($wpaicg_provider === 'Google') {
                    if (isset($wpaicg_settings['provider']) && $wpaicg_settings['provider'] === 'Google') {
                        $wpaicg_ai_model = isset($wpaicg_settings['model']) ? $wpaicg_settings['model'] : get_option('wpaicg_shortcode_google_model', 'gemini-pro');
                    } else {
                        $wpaicg_ai_model = get_option('wpaicg_shortcode_google_model', 'gemini-pro');
                    }
                } else {
                    // Handle other providers or set a default value
                    $wpaicg_ai_model = 'gpt-3.5-turbo';
                }

                $wpaicg_save_logs = isset($wpaicg_settings['save_logs']) && $wpaicg_settings['save_logs'] ? true : false;
                $wpaicg_chat_addition = isset($wpaicg_settings['chat_addition']) && $wpaicg_settings['chat_addition'] ? true : false;
                $wpaicg_chat_addition_text = isset($wpaicg_settings['chat_addition_text']) && !empty($wpaicg_settings['chat_addition_text']) ? $wpaicg_settings['chat_addition_text'] : '';
                $wpaicg_user_aware = isset($wpaicg_settings['user_aware']) ? $wpaicg_settings['user_aware'] : 'no';
                $wpaicg_token_limit_message = isset($wpaicg_settings['limited_message']) ? $wpaicg_settings['limited_message'] : $wpaicg_token_limit_message;
                $wpaicg_chat_temperature = isset($wpaicg_settings['temperature']) && !empty($wpaicg_settings['temperature']) ? $wpaicg_settings['temperature'] :$wpaicg_chat_temperature;
                $wpaicg_chat_max_tokens = isset($wpaicg_settings['max_tokens']) && !empty($wpaicg_settings['max_tokens']) ? $wpaicg_settings['max_tokens'] :$wpaicg_chat_max_tokens;
                $wpaicg_chat_top_p = isset($wpaicg_settings['top_p']) && !empty($wpaicg_settings['top_p']) ? $wpaicg_settings['top_p'] :$wpaicg_chat_top_p;
                $wpaicg_chat_best_of = isset($wpaicg_settings['best_of']) && !empty($wpaicg_settings['best_of']) ? $wpaicg_settings['best_of'] :$wpaicg_chat_best_of;
                $wpaicg_chat_frequency_penalty = isset($wpaicg_settings['frequency_penalty']) && !empty($wpaicg_settings['frequency_penalty']) ? $wpaicg_settings['frequency_penalty'] :$wpaicg_chat_frequency_penalty;
                $wpaicg_chat_presence_penalty = isset($wpaicg_settings['presence_penalty']) && !empty($wpaicg_settings['presence_penalty']) ? $wpaicg_settings['presence_penalty'] :$wpaicg_chat_presence_penalty;
                if(isset($wpaicg_settings['embedding_index']) && !empty($wpaicg_settings['embedding_index'])){
                    $wpaicg_pinecone_environment = $wpaicg_settings['embedding_index'];
                }
                if(is_user_logged_in() && $wpaicg_settings['user_limited'] && $wpaicg_settings['user_tokens'] > 0){
                    $wpaicg_limited_tokens = true;
                    $wpaicg_limited_tokens_number = $wpaicg_settings['user_tokens'];
                }
                /*Check limit base role*/
                if(is_user_logged_in() && isset($wpaicg_settings['role_limited']) && $wpaicg_settings['role_limited']){
                    $wpaicg_roles = ( array )wp_get_current_user()->roles;
                    $limited_current_role = 0;
                    foreach ($wpaicg_roles as $wpaicg_role) {
                        if(
                            isset($wpaicg_settings['limited_roles'])
                            && is_array($wpaicg_settings['limited_roles'])
                            && isset($wpaicg_settings['limited_roles'][$wpaicg_role])
                            && $wpaicg_settings['limited_roles'][$wpaicg_role] > $limited_current_role
                        ){
                            $limited_current_role = $wpaicg_settings['limited_roles'][$wpaicg_role];
                        }
                    }
                    if($limited_current_role > 0){
                        $wpaicg_limited_tokens = true;
                        $wpaicg_limited_tokens_number = $limited_current_role;
                    }
                    else{
                        $wpaicg_limited_tokens = false;
                    }
                }
                /*End check limit base role*/
                if(!is_user_logged_in() && $wpaicg_settings['guest_limited'] && $wpaicg_settings['guest_tokens'] > 0){
                    $wpaicg_limited_tokens = true;
                    $wpaicg_limited_tokens_number = $wpaicg_settings['guest_tokens'];
                }
                if(wpaicg_util_core()->wpaicg_is_pro()) {
                    $wpaicg_chat_pro = WPAICG_Chat_Pro::get_instance();
                    $wpaicg_moderation = $wpaicg_chat_pro->activated($wpaicg_settings);
                    $wpaicg_moderation_model = $wpaicg_chat_pro->model($wpaicg_settings);
                    $wpaicg_moderation_notice = $wpaicg_chat_pro->notice($wpaicg_settings);
                }
            }
            else {
                $wpaicg_limited_tokens = false;
                $wpaicg_chat_widget = get_option('wpaicg_chat_widget', []);
                $wpaicg_chat_embedding = get_option('wpaicg_chat_embedding', false);
                $wpaicg_chat_embedding_type = get_option('wpaicg_chat_embedding_type', false);
                $wpaicg_chat_no_answer = get_option('wpaicg_chat_no_answer', '');
                $wpaicg_chat_embedding_top = get_option('wpaicg_chat_embedding_top', 1);
                $wpaicg_chat_qdrant_collection = get_option('wpaicg_widget_qdrant_collection', '');
                $wpaicg_chat_vectordb = get_option('wpaicg_chat_vectordb', 'pinecone');
                $wpaicg_chat_no_answer = empty($wpaicg_chat_no_answer) ? 'I dont know' : $wpaicg_chat_no_answer;
                $wpaicg_chat_with_embedding = false;
                $wpaicg_chat_language = get_option('wpaicg_chat_language', 'en');
                $wpaicg_chat_tone = isset($wpaicg_chat_widget['tone']) && !empty($wpaicg_chat_widget['tone']) ? $wpaicg_chat_widget['tone'] : 'friendly';
                $wpaicg_chat_proffesion = isset($wpaicg_chat_widget['proffesion']) && !empty($wpaicg_chat_widget['proffesion']) ? $wpaicg_chat_widget['proffesion'] : 'none';
                $wpaicg_chat_remember_conversation = isset($wpaicg_chat_widget['remember_conversation']) && !empty($wpaicg_chat_widget['remember_conversation']) ? $wpaicg_chat_widget['remember_conversation'] : 'yes';
                $wpaicg_chat_content_aware = isset($wpaicg_chat_widget['content_aware']) && !empty($wpaicg_chat_widget['content_aware']) ? $wpaicg_chat_widget['content_aware'] : 'yes';
                if ($wpaicg_provider === 'Azure') {
                    $wpaicg_ai_model = get_option('wpaicg_azure_deployment');
                }  elseif ($wpaicg_provider === 'Google') {
                    $wpaicg_ai_model = get_option('wpaicg_widget_google_model', 'gemini-pro'); 
                } elseif ($wpaicg_provider === 'OpenRouter') {
                    $wpaicg_ai_model = get_option('wpaicg_widget_openrouter_model', 'openrouter/auto'); 
                } else {
                    $wpaicg_ai_model = get_option('wpaicg_chat_model', 'gpt-3.5-turbo');
                }                    
                $wpaicg_save_logs = isset($wpaicg_chat_widget['save_logs']) && $wpaicg_chat_widget['save_logs'] ? true : false;
                $wpaicg_chat_addition = get_option('wpaicg_chat_addition',false);
                $wpaicg_chat_addition_text = get_option('wpaicg_chat_addition_text','');
                $wpaicg_user_aware = isset($wpaicg_chat_widget['user_aware']) ? $wpaicg_chat_widget['user_aware'] : 'no';
                $wpaicg_token_limit_message = isset($wpaicg_chat_widget['limited_message']) ? $wpaicg_chat_widget['limited_message'] : $wpaicg_token_limit_message;
                $wpaicg_save_request = isset($wpaicg_chat_widget['log_request']) && $wpaicg_chat_widget['log_request'] ? true : false;
                if(is_user_logged_in() && isset($wpaicg_chat_widget['user_limited']) && $wpaicg_chat_widget['user_limited'] && $wpaicg_chat_widget['user_tokens'] > 0){
                    $wpaicg_limited_tokens = true;
                    $wpaicg_limited_tokens_number = $wpaicg_chat_widget['user_tokens'];
                }
                /*Check limit base role*/
                if(is_user_logged_in() && isset($wpaicg_chat_widget['role_limited']) && $wpaicg_chat_widget['role_limited']){
                    $wpaicg_roles = ( array )wp_get_current_user()->roles;
                    $limited_current_role = 0;
                    foreach ($wpaicg_roles as $wpaicg_role) {
                        if(
                            isset($wpaicg_chat_widget['limited_roles'])
                            && is_array($wpaicg_chat_widget['limited_roles'])
                            && isset($wpaicg_chat_widget['limited_roles'][$wpaicg_role])
                            && $wpaicg_chat_widget['limited_roles'][$wpaicg_role] > $limited_current_role
                        ){
                            $limited_current_role = $wpaicg_chat_widget['limited_roles'][$wpaicg_role];
                        }
                    }
                    if($limited_current_role > 0){
                        $wpaicg_limited_tokens = true;
                        $wpaicg_limited_tokens_number = $limited_current_role;
                    }
                    else{
                        $wpaicg_limited_tokens = false;
                    }
                }
                /*End check limit base role*/
                if(
                    !is_user_logged_in() && 
                    isset($wpaicg_chat_widget['guest_limited']) && $wpaicg_chat_widget['guest_limited'] && 
                    isset($wpaicg_chat_widget['guest_tokens']) && $wpaicg_chat_widget['guest_tokens'] > 0
                ){
                    $wpaicg_limited_tokens = true;
                    $wpaicg_limited_tokens_number = $wpaicg_chat_widget['guest_tokens'];
                }
                
                if(wpaicg_util_core()->wpaicg_is_pro()){
                    $wpaicg_chat_pro = WPAICG_Chat_Pro::get_instance();
                    $wpaicg_moderation = $wpaicg_chat_pro->activated($wpaicg_chat_widget);
                    $wpaicg_moderation_model = $wpaicg_chat_pro->model($wpaicg_chat_widget);
                    $wpaicg_moderation_notice = $wpaicg_chat_pro->notice($wpaicg_chat_widget);
                }
                if(isset($wpaicg_chat_widget['embedding_index']) && !empty($wpaicg_chat_widget['embedding_index'])){
                    $wpaicg_pinecone_environment = $wpaicg_chat_widget['embedding_index'];
                }
                $use_default_embedding = isset($wpaicg_chat_widget['use_default_embedding']) ? $wpaicg_chat_widget['use_default_embedding'] : true;
                $selected_embedding_model = isset($wpaicg_chat_widget['embedding_model']) ? $wpaicg_chat_widget['embedding_model'] : "";
                $selected_embedding_provider = isset($wpaicg_chat_widget['embedding_provider']) ? $wpaicg_chat_widget['embedding_provider'] : "";
            }
            if(isset($_REQUEST['bot_id']) && !empty($_REQUEST['bot_id'])){
                $wpaicg_bot = get_post(sanitize_text_field($_REQUEST['bot_id']));
                if($wpaicg_bot) {
                    $wpaicg_limited_tokens = false;
                    if(strpos($wpaicg_bot->post_content,'\"') !== false) {
                        $wpaicg_bot->post_content = str_replace('\"', '&quot;', $wpaicg_bot->post_content);
                    }
                    if(strpos($wpaicg_bot->post_content,"\'") !== false) {
                        $wpaicg_bot->post_content = str_replace('\\', '', $wpaicg_bot->post_content);
                    }
                    $wpaicg_chat_widget = json_decode($wpaicg_bot->post_content, true);
                    $wpaicg_bot_type = isset($wpaicg_chat_widget['type']) && $wpaicg_chat_widget['type'] == 'shortcode' ? 'Shortcode ' : 'Widget ';
                    $wpaicg_chat_embedding = isset($wpaicg_chat_widget['embedding']) && $wpaicg_chat_widget['embedding'] ? true : false;
                    $wpaicg_chat_embedding_type = isset($wpaicg_chat_widget['embedding_type']) ? $wpaicg_chat_widget['embedding_type'] : '' ;
                    $wpaicg_chat_no_answer = isset($wpaicg_chat_widget['no_answer']) ? $wpaicg_chat_widget['no_answer'] : '' ;
                    $wpaicg_chat_embedding_top = isset($wpaicg_chat_widget['embedding_top']) ? $wpaicg_chat_widget['embedding_top'] : 1 ;
                    $wpaicg_chat_no_answer = empty($wpaicg_chat_no_answer) ? 'I dont know' : $wpaicg_chat_no_answer;
                    $wpaicg_chat_with_embedding = false;
                    $wpaicg_chat_language = isset($wpaicg_chat_widget['language']) ? $wpaicg_chat_widget['language'] : 'en' ;
                    $wpaicg_chat_tone = isset($wpaicg_chat_widget['tone']) ? $wpaicg_chat_widget['tone'] : 'friendly' ;
                    $wpaicg_chat_proffesion = isset($wpaicg_chat_widget['proffesion']) ? $wpaicg_chat_widget['proffesion'] : 'none' ;
                    $wpaicg_chat_remember_conversation = isset($wpaicg_chat_widget['remember_conversation']) ? $wpaicg_chat_widget['remember_conversation'] : 'yes' ;
                    $wpaicg_chat_content_aware = isset($wpaicg_chat_widget['content_aware']) ? $wpaicg_chat_widget['content_aware'] : 'yes' ;
                    $wpaicg_chat_vectordb = isset($wpaicg_chat_widget['vectordb']) ? $wpaicg_chat_widget['vectordb'] : 'pinecone' ;
                    $wpaicg_chat_qdrant_collection = isset($wpaicg_chat_widget['qdrant_collection']) ? $wpaicg_chat_widget['qdrant_collection'] : '' ;
                    $wpaicg_ai_model = isset($wpaicg_chat_widget['model']) ? $wpaicg_chat_widget['model'] : 'gpt-3.5-turbo' ;

                    if ($wpaicg_provider === 'Azure') {
                        $wpaicg_ai_model = isset($wpaicg_chat_widget['model']) ? $wpaicg_chat_widget['model'] : '';
                    } elseif ($wpaicg_provider === 'Google') {
                        $wpaicg_ai_model = isset($wpaicg_chat_widget['model']) ? $wpaicg_chat_widget['model'] : 'gemini-pro';
                    }  elseif ($wpaicg_provider === 'OpenRouter') {
                        $wpaicg_ai_model = isset($wpaicg_chat_widget['model']) ? $wpaicg_chat_widget['model'] : 'openrouter/auto';
                    } else {
                        $wpaicg_ai_model = isset($wpaicg_chat_widget['model']) ? $wpaicg_chat_widget['model'] : 'gpt-3.5-turbo';
                    }

                    $wpaicg_save_logs = isset($wpaicg_chat_widget['save_logs']) && $wpaicg_chat_widget['save_logs'] ? true : false;
                    $wpaicg_chat_addition = isset($wpaicg_chat_widget['chat_addition']) && $wpaicg_chat_widget['chat_addition'] ? true : false;
                    $wpaicg_chat_addition_text = isset($wpaicg_chat_widget['chat_addition_text']) && !empty($wpaicg_chat_widget['chat_addition_text']) ? $wpaicg_chat_widget['chat_addition_text'] : '';
                    $wpaicg_user_aware = isset($wpaicg_chat_widget['user_aware']) ? $wpaicg_chat_widget['user_aware'] : 'no';
                    $wpaicg_token_limit_message = isset($wpaicg_chat_widget['limited_message']) ? $wpaicg_chat_widget['limited_message'] : $wpaicg_token_limit_message;
                    $wpaicg_save_request = isset($wpaicg_chat_widget['log_request']) && $wpaicg_chat_widget['log_request'] ? true : false;
                    $wpaicg_chat_temperature = isset($wpaicg_chat_widget['temperature']) && !empty($wpaicg_chat_widget['temperature']) ? $wpaicg_chat_widget['temperature'] :$wpaicg_chat_temperature;
                    $wpaicg_chat_max_tokens = isset($wpaicg_chat_widget['max_tokens']) && !empty($wpaicg_chat_widget['max_tokens']) ? $wpaicg_chat_widget['max_tokens'] :$wpaicg_chat_max_tokens;
                    $wpaicg_chat_top_p = isset($wpaicg_chat_widget['top_p']) && !empty($wpaicg_chat_widget['top_p']) ? $wpaicg_chat_widget['top_p'] :$wpaicg_chat_top_p;
                    $wpaicg_chat_best_of = isset($wpaicg_chat_widget['best_of']) && !empty($wpaicg_chat_widget['best_of']) ? $wpaicg_chat_widget['best_of'] :$wpaicg_chat_best_of;
                    $wpaicg_chat_frequency_penalty = isset($wpaicg_chat_widget['frequency_penalty']) && !empty($wpaicg_chat_widget['frequency_penalty']) ? $wpaicg_chat_widget['frequency_penalty'] :$wpaicg_chat_frequency_penalty;
                    $wpaicg_chat_presence_penalty = isset($wpaicg_chat_widget['presence_penalty']) && !empty($wpaicg_chat_widget['presence_penalty']) ? $wpaicg_chat_widget['presence_penalty'] :$wpaicg_chat_presence_penalty;
                    $use_default_embedding = isset($wpaicg_chat_widget['use_default_embedding']) ? $wpaicg_chat_widget['use_default_embedding'] : true;
                    $selected_embedding_model = isset($wpaicg_chat_widget['embedding_model']) ? $wpaicg_chat_widget['embedding_model'] : "";
                    $selected_embedding_provider = isset($wpaicg_chat_widget['embedding_provider']) ? $wpaicg_chat_widget['embedding_provider'] : "";
                    if (is_user_logged_in() && 
                        isset($wpaicg_chat_widget['user_limited']) && $wpaicg_chat_widget['user_limited'] && 
                        isset($wpaicg_chat_widget['user_tokens']) && $wpaicg_chat_widget['user_tokens'] > 0) {
                        $wpaicg_limited_tokens = true;
                        $wpaicg_limited_tokens_number = $wpaicg_chat_widget['user_tokens'];
                    }
                
                    /*Check limit base role*/
                    if(is_user_logged_in() && isset($wpaicg_chat_widget['role_limited']) && $wpaicg_chat_widget['role_limited']){
                        $wpaicg_roles = ( array )wp_get_current_user()->roles;
                        $limited_current_role = 0;
                        foreach ($wpaicg_roles as $wpaicg_role) {
                            if(
                                isset($wpaicg_chat_widget['limited_roles'])
                                && is_array($wpaicg_chat_widget['limited_roles'])
                                && isset($wpaicg_chat_widget['limited_roles'][$wpaicg_role])
                                && $wpaicg_chat_widget['limited_roles'][$wpaicg_role] > $limited_current_role
                            ){
                                $limited_current_role = $wpaicg_chat_widget['limited_roles'][$wpaicg_role];
                            }
                        }
                        if($limited_current_role > 0){
                            $wpaicg_limited_tokens = true;
                            $wpaicg_limited_tokens_number = $limited_current_role;
                        }
                        else{
                            $wpaicg_limited_tokens = false;
                        }
                    }

                    if(
                        !is_user_logged_in() && 
                        isset($wpaicg_chat_widget['guest_limited']) && $wpaicg_chat_widget['guest_limited'] && 
                        isset($wpaicg_chat_widget['guest_tokens']) && $wpaicg_chat_widget['guest_tokens'] > 0
                    ){
                        $wpaicg_limited_tokens = true;
                        $wpaicg_limited_tokens_number = $wpaicg_chat_widget['guest_tokens'];
                    }
                    
                    if(wpaicg_util_core()->wpaicg_is_pro()){
                        $wpaicg_chat_pro = WPAICG_Chat_Pro::get_instance();
                        $wpaicg_moderation = $wpaicg_chat_pro->activated($wpaicg_chat_widget);
                        $wpaicg_moderation_model = $wpaicg_chat_pro->model($wpaicg_chat_widget);
                        $wpaicg_moderation_notice = $wpaicg_chat_pro->notice($wpaicg_chat_widget);
                    }
                    $wpaicg_chat_source = $wpaicg_bot_type.'ID: '.$wpaicg_bot->ID;
                    if(isset($wpaicg_chat_widget['embedding_index']) && !empty($wpaicg_chat_widget['embedding_index'])){
                        $wpaicg_pinecone_environment = $wpaicg_chat_widget['embedding_index'];
                    }
                }
            }
            if(!is_user_logged_in()){
                $wpaicg_user_aware = 'no';
            }
            $wpaicg_human_name = 'Human';
            $wpaicg_user_name = '';
            if($wpaicg_user_aware == 'yes'){
                $wpaicg_human_name = wp_get_current_user()->user_login;
                if(!empty(wp_get_current_user()->display_name)) {
                    $wpaicg_user_name = 'Username: ' . wp_get_current_user()->display_name;
                    $wpaicg_human_name = wp_get_current_user()->display_name;
                }
            }
            /*Token handing*/
            $wpaicg_chat_token_id = false;

            // Check for banned IPs
            $this->check_banned_ips($wpaicg_chat_source, $wpaicg_provider);

            // Check for banned words
            $this->check_banned_words($wpaicg_message, $wpaicg_chat_source, $wpaicg_provider);

            if ($wpaicg_limited_tokens) {
                $wpaicg_chat_token_log = $this->getUserTokenUsage($wpdb, $wpaicg_chat_source, $wpaicg_client_id);
                $wpaicg_token_usage_client = $wpaicg_chat_token_log ? $wpaicg_chat_token_log->tokens : 0;
                $wpaicg_chat_token_id = $wpaicg_chat_token_log ? $wpaicg_chat_token_log->id : false;

                $user_tokens = is_user_logged_in() ? get_user_meta(get_current_user_id(), 'wpaicg_chat_tokens', true) : 0;
                $still_limited = $this->isUserTokenLimited($user_tokens, $wpaicg_limited_tokens_number, $wpaicg_token_usage_client);

                if ($still_limited) {
                    $wpaicg_result = ['msg' => $wpaicg_token_limit_message, 'tokenLimitReached' => true];
                    $stream_nav_setting = $this->determine_stream_nav_setting($wpaicg_chat_source, $wpaicg_provider);

                    if ($stream_nav_setting == 1) {
                        header('Content-Type: text/event-stream');
                        header('Cache-Control: no-cache');
                        header( 'X-Accel-Buffering: no' );
                        echo "data: " . wp_json_encode($wpaicg_result) . "\n\n";
                        ob_implicit_flush( true );
                        // Flush and end buffer if it exists
                        if (ob_get_level() > 0) {
                            ob_end_flush();
                        }
                    } else {
                        wp_send_json($wpaicg_result);
                    }
                    exit;
                }
            }

            /*End check token handing*/

            // inialize the audio_message variable
            $audio_message = '';

            /*Check Audio Recording*/
            if (isset($_FILES['audio']) && empty($_FILES['audio']['error'])) {
                $result = $this->processSpeechToText($_FILES['audio'], $open_ai);
            
                if ($result['error']) {
                    $wpaicg_result['msg'] = $result['msg'];
                    wp_send_json($wpaicg_result);
                }
            
                $wpaicg_message = $result['text'];
                $audio_message = $wpaicg_message;
            }

            /*Start check Log*/
            $wpaicg_chat_log_id = false;
            $wpaicg_chat_log_data = array();

            if(!empty($wpaicg_message) && $wpaicg_save_logs) {
              
                $wpaicg_current_context_id = isset($_REQUEST['post_id']) && !empty($_REQUEST['post_id']) ? sanitize_text_field($_REQUEST['post_id']) : '';
                $wpaicg_current_context_title = !empty($wpaicg_current_context_id) ? get_the_title($wpaicg_current_context_id) : '';
                $wpaicg_unique_chat = md5($wpaicg_client_id . '-' . $wpaicg_current_context_id);
                $wpaicg_chat_log_check = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "wpaicg_chatlogs WHERE source=%s AND log_session=%s",$wpaicg_chat_source,$wpaicg_unique_chat));
                if (!$wpaicg_chat_log_check) {
                    $wpdb->insert($wpdb->prefix . 'wpaicg_chatlogs', array(
                        'log_session' => $wpaicg_unique_chat,
                        'data' => json_encode(array()),
                        'page_title' => $wpaicg_current_context_title,
                        'source' => $wpaicg_chat_source,
                        'created_at' => time()
                    ));
                    $wpaicg_chat_log_id = $wpdb->insert_id;
                } else {
                    $wpaicg_chat_log_id = $wpaicg_chat_log_check->id;
                    $wpaicg_current_log_data = json_decode($wpaicg_chat_log_check->data, true);
                    if ($wpaicg_current_log_data && is_array($wpaicg_current_log_data)) {
                        $wpaicg_chat_log_data = $wpaicg_current_log_data;
                    }
                }
                $wpaicg_chat_log_data[] = array('message' => $wpaicg_message, 'type' => 'user', 'date' => time(),'ip' => $this->getIpAddress(), 'username' => $this->getCurrentUsername());
            }
            /*End Check Log*/

            /* Disable Audio if provider is Azure  or Google */

            if ($wpaicg_provider === 'Azure' || $wpaicg_provider === 'Google') {
                // Fetch the existing options
                $wpaicg_chat_shortcode_options = get_option('wpaicg_chat_shortcode_options', []);

                // Update the audio_enable key
                $wpaicg_chat_shortcode_options['audio_enable'] = 0;

                // Update the option in the database
                update_option('wpaicg_chat_shortcode_options', $wpaicg_chat_shortcode_options);

            }

            /*Check Moderation*/
            // Check if it's the pro version
            $is_pro = \WPAICG\wpaicg_util_core()->wpaicg_is_pro();
            // If it's not the pro version then disable moderation, if it is the pro version and the provider is not OpenAI then disable moderation. if its free version disable moderation regardless of the provider
            if (!$is_pro || $wpaicg_provider !== 'OpenAI') {
                $wpaicg_moderation = false;
            }

            if(!empty($wpaicg_message) && $wpaicg_moderation){
                $stream_nav_setting = $this->determine_stream_nav_setting($wpaicg_chat_source, $wpaicg_provider);
                $wpaicg_chat_pro->moderation($open_ai,$wpaicg_message, $wpaicg_moderation_model, $wpaicg_moderation_notice, $wpaicg_save_logs, $wpaicg_chat_log_id,$wpaicg_chat_log_data, $stream_nav_setting);
            }
            /*End Check Moderation*/
            $wpaicg_embedding_content = '';
            if($wpaicg_chat_embedding){
                /*Using embeddings only*/
                $namespace = false;
                if(isset($_REQUEST['namespace']) && !empty($_REQUEST['namespace'])){
                    $namespace = sanitize_text_field($_REQUEST['namespace']);
                }

                $wpaicg_qdrant_api_key = get_option('wpaicg_qdrant_api_key', '');
                $wpaicg_qdrant_endpoint = get_option('wpaicg_qdrant_endpoint', '');

                // Check if vectordb is set to 'qdrant'
                if ($wpaicg_chat_vectordb === 'qdrant') {
                    // Call the Qdrant specific function
                    $wpaicg_embeddings_result = $this->wpaicg_embeddings_result_qdrant($wpaicg_provider, $use_default_embedding,$selected_embedding_model,$selected_embedding_provider,$open_ai, $wpaicg_qdrant_api_key, $wpaicg_qdrant_endpoint, $wpaicg_chat_qdrant_collection, $wpaicg_message, $wpaicg_chat_source,$wpaicg_chat_embedding_top, $namespace);
                } else {
                    // Continue with the current flow for Pinecone or other DB providers
                    $wpaicg_embeddings_result = $this->wpaicg_embeddings_result($wpaicg_provider, $use_default_embedding,$selected_embedding_model,$selected_embedding_provider,$open_ai,$wpaicg_pinecone_api, $wpaicg_pinecone_environment, $wpaicg_message, $wpaicg_chat_embedding_top, $wpaicg_chat_source, $namespace);
                }

                if($wpaicg_embeddings_result['status'] == 'empty'){
                    $wpaicg_chat_with_embedding = false;
                }
                else {
                    if (!$wpaicg_chat_embedding_type || empty($wpaicg_chat_embedding_type)) {
                        $wpaicg_result['status'] = $wpaicg_embeddings_result['status'];
                        $wpaicg_result['data'] = empty($wpaicg_embeddings_result['data']) ? $wpaicg_chat_no_answer : $wpaicg_embeddings_result['data'];
                        $wpaicg_result['msg'] = empty($wpaicg_embeddings_result['data']) ? $wpaicg_chat_no_answer : $wpaicg_embeddings_result['data'];
                        $this->wpaicg_save_chat_log($wpaicg_chat_log_id, $wpaicg_chat_log_data, 'ai', $wpaicg_result['data']);
                        wp_send_json($wpaicg_result);
                        exit;
                    } else {
                        $wpaicg_result['status'] = $wpaicg_embeddings_result['status'];
                        if ($wpaicg_result['status'] == 'error') {
                            $wpaicg_result['msg'] = empty($wpaicg_embeddings_result['data']) ? $wpaicg_chat_no_answer : $wpaicg_embeddings_result['data'];
                            if (empty($wpaicg_result['data'])) {
                                $this->wpaicg_save_chat_log($wpaicg_chat_log_id, $wpaicg_chat_log_data, 'ai', $wpaicg_result['msg']);
                            } else {
                                $this->wpaicg_save_chat_log($wpaicg_chat_log_id, $wpaicg_chat_log_data, 'ai', $wpaicg_result['data']);
                            }
                            wp_send_json($wpaicg_result);
                            exit;
                        } else {
                            $wpaicg_total_tokens += $wpaicg_embeddings_result['tokens']; // Add embedding tokens
                            $wpaicg_embedding_content = $wpaicg_embeddings_result['data'];
                        }
                        $wpaicg_chat_with_embedding = true;
                    }
                }
            }
            if ($wpaicg_chat_remember_conversation == 'yes') {

                // get wpaicg_chat_history from request
                if (isset($_REQUEST['wpaicg_chat_history']) && !empty($_REQUEST['wpaicg_chat_history'])) {
                    $wpaicg_chat_history = sanitize_text_field($_REQUEST['wpaicg_chat_history']);
                    // remove \\ from wpaicg_chat_history
                    $wpaicg_chat_history = str_replace("\\", '', $wpaicg_chat_history);
                } else {
                    error_log('wpaicg_chat_history is not set in the request');
                }

                $wpaicg_chat_history = isset($wpaicg_chat_history) && !empty($wpaicg_chat_history) ? json_decode($wpaicg_chat_history, true) : array();
                if (!is_array($wpaicg_chat_history)) {
                    $wpaicg_chat_history = array(); // Ensure it's an array even if json_decode fails
                }

                $wpaicg_conversation_end_messages = $wpaicg_chat_history;
            }

            if (!empty($wpaicg_message)) {
                $wpaicg_language_file = WPAICG_PLUGIN_DIR . 'admin/chat/languages/' . $wpaicg_chat_language . '.json';

                if (!file_exists($wpaicg_language_file)) {
                    $wpaicg_language_file = WPAICG_PLUGIN_DIR . 'admin/chat/languages/en.json';
                }
                $wpaicg_language_json = file_get_contents($wpaicg_language_file);
                $wpaicg_languages = json_decode($wpaicg_language_json, true);
                $wpaicg_chat_tone = isset($wpaicg_languages['tone'][$wpaicg_chat_tone]) ? $wpaicg_languages['tone'][$wpaicg_chat_tone] : 'Professional';
                $wpaicg_chat_proffesion = isset($wpaicg_languages['proffesion'][$wpaicg_chat_proffesion]) ? $wpaicg_languages['proffesion'][$wpaicg_chat_proffesion] : 'none';


                $wpaicg_greeting_key = 'greeting';

                if ($wpaicg_chat_proffesion != 'none') {
                    $wpaicg_greeting_key .= '_proffesion';
                }
                $wpaicg_chat_greeting_message = sprintf($wpaicg_languages[$wpaicg_greeting_key], $wpaicg_chat_tone, $wpaicg_chat_proffesion . ".\n");

                if(!empty($wpaicg_chat_addition_text)){
                    $site_url = site_url();
                    $parse_url = wp_parse_url($site_url);
                    $domain_name = isset($parse_url['host']) && !empty($parse_url['host']) ? $parse_url['host'] : '';
                    $date = gmdate(get_option( 'date_format'));
                    $sitename = get_bloginfo('name');
                    $wpaicg_chat_addition_text = str_replace('[siteurl]',$site_url, $wpaicg_chat_addition_text);
                    $wpaicg_chat_addition_text = str_replace('[domain]',$domain_name, $wpaicg_chat_addition_text);
                    $wpaicg_chat_addition_text = str_replace('[sitename]',$sitename, $wpaicg_chat_addition_text);
                    $wpaicg_chat_addition_text = str_replace('[date]',$date, $wpaicg_chat_addition_text);
                }
                if ($wpaicg_chat_content_aware == 'yes') {
                    if($wpaicg_chat_with_embedding && !empty($wpaicg_embedding_content)){
                        $wpaicg_greeting_key .= '_content';
                        $current_context = '"'.$wpaicg_embedding_content.'"';
                        if ($wpaicg_chat_proffesion != 'none') {
                            $wpaicg_chat_greeting_message = sprintf($wpaicg_languages[$wpaicg_greeting_key], $wpaicg_chat_tone, $wpaicg_chat_proffesion . ".\n", $current_context);
                        } else {
                            $wpaicg_chat_greeting_message = sprintf($wpaicg_languages[$wpaicg_greeting_key], $wpaicg_chat_tone . ".\n", $current_context);
                        }
                        if($wpaicg_chat_addition && !empty($wpaicg_chat_addition_text)){
                            $wpaicg_chat_greeting_message .= ' '.sprintf($wpaicg_languages[$wpaicg_greeting_key.'_extra'], $wpaicg_chat_addition_text);
                        }
                    }
                    elseif(isset($_REQUEST['post_id']) && !empty($_REQUEST['post_id'])){
                        $current_post = get_post(sanitize_text_field($_REQUEST['post_id']));
                        if ($current_post) {
                            $wpaicg_greeting_key .= '_content';
                            $current_context = '"' . strip_tags($current_post->post_title);
                            $current_post_excerpt = str_replace('[...]', '', strip_tags(get_the_excerpt($current_post)));
                            if ($current_post_excerpt !== '') {
                                $current_post_excerpt = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                                    return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
                                }, $current_post_excerpt);
                                $current_context .= "\n" . $current_post_excerpt;
                            }
                            $current_context .= '"';
                            if ($wpaicg_chat_proffesion != 'none') {
                                $wpaicg_chat_greeting_message = sprintf($wpaicg_languages[$wpaicg_greeting_key], $wpaicg_chat_tone, $wpaicg_chat_proffesion . ".\n", $current_context);
                            } else {
                                $wpaicg_chat_greeting_message = sprintf($wpaicg_languages[$wpaicg_greeting_key], $wpaicg_chat_tone . ".\n", $current_context);
                            }
                            if($wpaicg_chat_addition && !empty($wpaicg_chat_addition_text)){
                                $wpaicg_chat_greeting_message .= ' '.sprintf($wpaicg_languages[$wpaicg_greeting_key.'_extra'], $wpaicg_chat_addition_text);
                            }
                        }
                    }
                    elseif($wpaicg_chat_addition && !empty($wpaicg_chat_addition_text)){
                        $wpaicg_greeting_key .= '_content';
                        $wpaicg_chat_greeting_message .= ' '.sprintf($wpaicg_languages[$wpaicg_greeting_key.'_extra'], $wpaicg_chat_addition_text);
                    }
                }
                elseif($wpaicg_chat_addition && !empty($wpaicg_chat_addition_text)){
                    $wpaicg_greeting_key .= '_content';
                    $wpaicg_chat_greeting_message .= ' '.sprintf($wpaicg_languages[$wpaicg_greeting_key.'_extra'], $wpaicg_chat_addition_text);
                }
                if(!empty($wpaicg_user_name)){
                    $wpaicg_chat_greeting_message .= '. '.$wpaicg_user_name;
                }
                $wpaicg_result['greeting_message'] = $wpaicg_chat_greeting_message;
                // check to see image is present in the request
                $image_final_data = '';
                // gpt-4-vision-preview or gpt-4o or openai/gpt-4o-2024-05-13
                if ($wpaicg_ai_model === 'gpt-4-vision-preview' || $wpaicg_ai_model === 'gpt-4o' || $wpaicg_ai_model === 'openai/gpt-4-vision-preview' || $wpaicg_ai_model === 'openai/gpt-4o' || $wpaicg_ai_model === 'openai/gpt-4o-2024-05-13') {
                    if (isset($_FILES['image']) && empty($_FILES['image']['error'])) {
                        // Handle the image upload and get the URL or base64 string
                        $image_data = $this->handle_image_upload($_FILES['image']);
                        // Fetch the user's preference for image processing method
                        $wpaicg_img_processing_method = get_option('wpaicg_img_processing_method', 'url');
                        
                        // Assign the appropriate data based on the processing method
                        if ($wpaicg_img_processing_method == 'base64' && isset($image_data['base64'])) {
                            $image_final_data = $image_data['base64'];
                        } elseif (isset($image_data['url'])) {
                            $image_final_data = $image_data['url'];
                        }
                    }
                } 

                $wpaicg_chatgpt_messages = array();

                // Check if there's an image data
                if (!empty($image_final_data)) {
                    // Prepare the message with both text and image
                    $image_quality = get_option('wpaicg_img_vision_quality', 'auto');
                    $textMessage = [
                        "role" => "user",
                        "content" => [
                            [
                                "type" => "text",
                                "text" => html_entity_decode($wpaicg_chat_greeting_message, ENT_QUOTES, 'UTF-8')
                            ],
                            [
                                "type" => "image_url",
                                "image_url" => [
                                    "url" => $image_final_data,
                                    "detail" => $image_quality
                                ]
                            ]
                        ]
                    ];
                } else {
                    // Prepare the message with text only, keeping the original format
                    $textMessage = [
                        "role" => "user",
                        "content" => html_entity_decode($wpaicg_chat_greeting_message, ENT_QUOTES, 'UTF-8')
                    ];
                }
                
                // Add the message to the messages array
                $wpaicg_chatgpt_messages[] = $textMessage;                

                if ($wpaicg_chat_remember_conversation == 'yes') {
                    $wpaicg_conversation_end_messages[] = $wpaicg_human_name.': ' . $wpaicg_message;
                    foreach ($wpaicg_conversation_end_messages as $wpaicg_conversation_end_message) {
                        // Trim the message to remove leading and trailing whitespace
                        $wpaicg_conversation_end_message = trim($wpaicg_conversation_end_message);
                        // Check if the message is from the user
                        if (strpos($wpaicg_conversation_end_message, "Human: ") === 0) {
                            // Extract user message content after "Human: "
                            $wpaicg_chatgpt_message = substr($wpaicg_conversation_end_message, strlen("Human: "));
                            $wpaicg_chatgpt_message = trim($wpaicg_chatgpt_message); // Trim the user message
                            $wpaicg_chatgpt_messages[] = array('role' => 'user', 'content' => $wpaicg_chatgpt_message);
                        } else {
                            // For assistant messages
                            $wpaicg_chatgpt_message = $wpaicg_conversation_end_message;
                            // Remove any instance of "AI: " from the message
                            $wpaicg_chatgpt_message = str_replace("AI: ", '', $wpaicg_chatgpt_message);
                            $wpaicg_chatgpt_message = trim($wpaicg_chatgpt_message); // Trim the assistant message
                            if(!empty($wpaicg_chatgpt_message)) {
                                $wpaicg_chatgpt_messages[] = array('role' => 'assistant', 'content' => $wpaicg_chatgpt_message);
                            }
                        }
                    }
                    $prompt = $wpaicg_chat_greeting_message;
                } else {
                    $prompt = $wpaicg_chat_greeting_message. "\n".$wpaicg_human_name.": " . $wpaicg_message;
                    $wpaicg_chatgpt_messages[] = array('role' => 'user','content' => $wpaicg_message);
                }


                // Get the list of models for chat and completion endpoints
                $chatEndpointModels = $this->getChatEndpointModels();
                $completionEndpointModels = $this->getCompletionEndpointModels();

                // Initialize the data request array with common elements
                $wpaicg_data_request = [
                    'model' => $wpaicg_ai_model,
                    'temperature' => floatval($wpaicg_chat_temperature),
                    'max_tokens' => intval($wpaicg_chat_max_tokens),
                    'frequency_penalty' => floatval($wpaicg_chat_frequency_penalty),
                    'presence_penalty' => floatval($wpaicg_chat_presence_penalty),
                    'top_p' => floatval($wpaicg_chat_top_p)
                ];

                // Determine the appropriate API endpoint and modify the data request accordingly
                if (in_array($wpaicg_ai_model, $chatEndpointModels)) {
                    // Model uses the chat endpoint
                    $wpaicg_data_request['messages'] = $wpaicg_chatgpt_messages;
                } elseif (in_array($wpaicg_ai_model, $completionEndpointModels)) {
                    // Model uses the completion endpoint
                    foreach ($wpaicg_chatgpt_messages as $wpaicg_chatgpt_message) {
                        $prompt .= $wpaicg_chatgpt_message['content'] . "\n";
                    }
                    $wpaicg_data_request += ['prompt' => $prompt, 'best_of' => intval($wpaicg_chat_best_of)];
                } else {
                    // Handle the case where the model is not recognized
                    error_log('Error: Model not recognized');
                }

                // Determine stream navigation setting and modify the data request
                $stream_nav_setting = $this->determine_stream_nav_setting($wpaicg_chat_source, $wpaicg_provider);
                if ($stream_nav_setting == 1) {
                    $wpaicg_data_request['stream'] = true;
                    header("Content-Type: text/event-stream");
                    header("Cache-Control: no-cache");
                    header("X-Accel-Buffering: no");
                    ob_implicit_flush( true );
                    // Flush and end buffer if it exists
                    if (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                }

                $apiFunction = in_array($wpaicg_ai_model, $chatEndpointModels) ? 'chat' : 'completion';

                // Call the new function
                $complete = $this->performOpenAiRequest($wpaicg_provider, $open_ai, $apiFunction, $wpaicg_data_request, $accumulatedData);

                // Process the response based on the stream navigation setting
                if ($stream_nav_setting == 1) {
                    $isChatEndpoint = ($apiFunction === 'chat');
                    $complete = $this->processChunkedData($accumulatedData, $wpaicg_chatgpt_messages, $wpaicg_ai_model, $isChatEndpoint);
                } else {
                    if (is_string($complete)) {
                        $complete = json_decode($complete);
                    } else {
                        error_log('Error: $complete is not a string for non-chunked data');
                    }
                }

                if (isset($complete->error)) {
                    $wpaicg_result['status'] = 'error';
                    $wpaicg_result['msg'] = esc_html(trim($complete->error->message));
                    if(empty($wpaicg_result['msg']) && isset($complete->error->code) && $complete->error->code == 'invalid_api_key'){
                        $wpaicg_result['msg'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                    }
                    $wpaicg_result['log'] = $wpaicg_chat_log_id;

                } else {

                    // Determine if the model is a legacy model using predefined functions
                    $isLegacyModel = in_array($wpaicg_ai_model, $this->getCompletionEndpointModels());

                    // Use the helper function to extract data
                    $wpaicg_result['data'] = $this->extractResponseData($complete, $stream_nav_setting, $isLegacyModel);

                    $wpaicg_total_tokens += $this->extractTotalTokens($complete, $stream_nav_setting);

                    if(!$wpaicg_save_request){
                        $wpaicg_data_request = false;
                    }

                    // Ensure $wpaicg_data_request is an array
                    if (!is_array($wpaicg_data_request)) {
                        $wpaicg_data_request = array();
                    }

                    // Now, you can safely assign the provider
                    $wpaicg_data_request['provider'] = $wpaicg_provider !== false ? $wpaicg_provider : 'OpenAI';

                    $wpaicg_data_request['model'] = $wpaicg_ai_model;

                    // Before saving the log, check if the model is gpt-4-vision-preview and an image file is present openai/gpt-4o-2024-05-13
                    if (($wpaicg_ai_model === 'gpt-4-vision-preview' || $wpaicg_ai_model === 'gpt-4o' || $wpaicg_ai_model === 'openai/gpt-4-vision-preview' || $wpaicg_ai_model === 'openai/gpt-4o' || $wpaicg_ai_model === 'openai/gpt-4o-2024-05-13') && isset($_FILES['image']) && empty($_FILES['image']['error'])) {
                        $wpaicg_img_processing_method = get_option('wpaicg_img_processing_method', 'url');
                        
                        // Proceed only if the image processing method is base64
                        if ($wpaicg_img_processing_method == 'base64') {
                            if (isset($wpaicg_data_request['messages']) && is_array($wpaicg_data_request['messages'])) {
                                // Iterate through the messages to find and replace base64 image data with URL
                                foreach ($wpaicg_data_request['messages'] as &$message) {
                                    if (isset($message['content']) && is_array($message['content'])) {
                                        foreach ($message['content'] as &$content) {
                                            if ($content['type'] == 'image_url' && isset($content['image_url']['url'])) {
                                                // Check if the URL is actually a base64 string
                                                if (strpos($content['image_url']['url'], 'data:image/') === 0) {
                                                    // Replace base64 data with the URL
                                                    $content['image_url']['url'] = $image_data['url'];
                                                }
                                            }
                                        }
                                        unset($content); // Break the reference with the last element
                                    }
                                }
                                unset($message); // Break the reference with the last element
                            }
                        }
                    }
                
                    $this->wpaicg_save_chat_log($wpaicg_chat_log_id, $wpaicg_chat_log_data, 'ai',$wpaicg_result['data'],$wpaicg_total_tokens,false,$wpaicg_data_request);
                    
                    if(is_user_logged_in() && $wpaicg_limited_tokens){
                        WPAICG_Account::get_instance()->save_log('chat', $wpaicg_total_tokens);
                    }

                    $wpaicg_result['status'] = 'success';
                    $wpaicg_result['log'] = $wpaicg_chat_log_id;
                    if($wpaicg_limited_tokens){
                        if($wpaicg_chat_token_id){
                            $wpdb->update($wpdb->prefix.'wpaicg_chattokens', array(
                                'tokens' => ($wpaicg_total_tokens + $wpaicg_token_usage_client)
                            ), array('id' => $wpaicg_chat_token_id));
                        }
                        else{
                            $wpaicg_chattoken_data = array(
                                'tokens' => $wpaicg_total_tokens,
                                'source' => $wpaicg_chat_source,
                                'created_at' => time()
                            );
                            if(is_user_logged_in()){
                                $wpaicg_chattoken_data['user_id'] = get_current_user_id();
                            }
                            else{
                                $wpaicg_chattoken_data['session_id'] = $wpaicg_client_id;
                            }
                            $wpdb->insert($wpdb->prefix.'wpaicg_chattokens',$wpaicg_chattoken_data);
                        }
                    }
                    /*
                        * End save token handing
                        * */
                    if ($wpaicg_chat_remember_conversation == 'yes') {
                        $wpaicg_conversation_end_messages[] = $wpaicg_result['data'];
                    }
                }
            }
            else{
                $wpaicg_result['status'] = 'error';
                $wpaicg_result['msg'] = esc_html__('It appears that nothing was inputted.','gpt3-ai-content-generator');
            }
            wp_send_json( $wpaicg_result );
        }


        public function handle_image_upload($image) {
            $wpaicg_user_uploads = get_option('wpaicg_user_uploads', 'filesystem');
            $wpaicg_img_processing_method = get_option('wpaicg_img_processing_method', 'url'); // Fetch user preference
            $wpaicg_delete_image = get_option('wpaicg_delete_image', 0); // Fetch delete image preference
            $result = ['url' => '', 'base64' => '']; // Initialize result variable with both keys
        
            if ($wpaicg_user_uploads === 'filesystem') {
                // Save the image to a custom folder inside the uploads directory
                $upload_dir = wp_upload_dir();
                $upload_path = $upload_dir['basedir'] . '/wpaicg_user_uploads/';
        
                // Create the directory if it doesn't exist
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0755, true);
                }
        
                $file_path = $upload_path . basename($image['name']);
        
                // Move the uploaded file to the new location
                if (move_uploaded_file($image['tmp_name'], $file_path)) {

                    // Always set the URL of the saved image
                    $result['url'] = $upload_dir['baseurl'] . '/wpaicg_user_uploads/' . basename($image['name']);

                    // Convert to base64 if required
                    $imageData = file_get_contents($file_path);
                    $result['base64'] = 'data:image/' . pathinfo($file_path, PATHINFO_EXTENSION) . ';base64,' . base64_encode($imageData);

                    // Delete the image file after processing if the option is enabled and processing method is not URL
                    if ($wpaicg_delete_image && $wpaicg_img_processing_method !== 'url') {
                        unlink($file_path);
                    }
                } else {
                    error_log('Failed to save image to filesystem.');
                }
            } else if ($wpaicg_user_uploads === 'media_library') {
                // Insert the image into the WordPress Media Library
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                require_once(ABSPATH . 'wp-admin/includes/file.php');
                require_once(ABSPATH . 'wp-admin/includes/media.php');
        
                $attachment_id = media_handle_upload('image', 0);

                if (is_wp_error($attachment_id)) {
                    error_log('Failed to save image to media library: ' . $attachment_id->get_error_message());
                } else {
                    // Get the file path of the uploaded image
                    $file_path = get_attached_file($attachment_id);
        
                    // Always set the URL of the uploaded image
                    $result['url'] = wp_get_attachment_url($attachment_id);
        
                    // Convert to base64 if required
                    $imageData = file_get_contents($file_path);
                    $result['base64'] = 'data:image/' . pathinfo($file_path, PATHINFO_EXTENSION) . ';base64,' . base64_encode($imageData);

                    // Delete the image file after processing if the option is enabled and processing method is not URL
                    if ($wpaicg_delete_image && $wpaicg_img_processing_method !== 'url') {
                        wp_delete_attachment($attachment_id, true);
                    }
                }
            }
            return $result;
        }
        
        /* Token handling */
        public function getUserTokenUsage($wpdb, $wpaicg_chat_source, $wpaicg_client_id) {
            if (is_user_logged_in()) {
                $user_id = get_current_user_id();
                $query = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."wpaicg_chattokens WHERE source = %s AND user_id=%d", $wpaicg_chat_source, $user_id);
            } else {
                $query = $wpdb->prepare("SELECT * FROM ".$wpdb->prefix."wpaicg_chattokens WHERE source = %s AND session_id=%s", $wpaicg_chat_source, $wpaicg_client_id);
            }
            return $wpdb->get_row($query);
        }

        public function isUserTokenLimited($user_tokens, $wpaicg_limited_tokens_number, $wpaicg_token_usage_client) {
            return $user_tokens <= 0 && $wpaicg_token_usage_client > $wpaicg_limited_tokens_number;
        }

        public function processSpeechToText($file, $open_ai) {
            $file_name = sanitize_file_name(basename($file['name']));
            $filetype = wp_check_filetype($file_name);
            $mime_types = ['mp3' => 'audio/mpeg', 'mp4' => 'video/mp4', 'mpeg' => 'video/mpeg', 'm4a' => 'audio/m4a', 'wav' => 'audio/wav', 'webm' => 'video/webm'];
        
            if (!in_array($filetype['type'], $mime_types)) {
                return ['error' => true, 'msg' => esc_html__('Accepted audio and video formats: MP3, MP4, MPEG, M4A, WAV, and WEBM.', 'gpt3-ai-content-generator')];
            }
        
            if ($file['size'] > 26214400) {
                return ['error' => true, 'msg' => esc_html__('Maximum audio file size: 25MB.', 'gpt3-ai-content-generator')];
            }
        
            $tmp_file = $file['tmp_name'];
            $data_audio_request = [
                'audio' => [
                    'filename' => $file_name,
                    'data' => file_get_contents($tmp_file)
                ],
                'model' => 'whisper-1',
                'response_format' => 'json'
            ];
        
            $completion = $open_ai->transcriptions($data_audio_request);
            $completion = json_decode($completion);
        
            if ($completion && isset($completion->error)) {
                $msg = $completion->error->message;
                if (empty($msg) && isset($completion->error->code) && $completion->error->code == 'invalid_api_key') {
                    $msg = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                }
                return ['error' => true, 'msg' => $msg];
            }
        
            return ['error' => false, 'text' => $completion->text];
        }

        public function getChatEndpointModels() {
            // List of models for the chat completions endpoint
            $chatModels = ['gpt-4', 'gpt-4-32k', 'gpt-4-1106-preview','gpt-4o', 'gpt-4-turbo','gpt-4-vision-preview', 'gpt-3.5-turbo', 'gpt-3.5-turbo-16k'];
            
            // Get custom models and Azure deployment model, if any
            $custom_models = get_option('wpaicg_custom_models', []);
            $wpaicg_azure_deployment = get_option('wpaicg_azure_deployment', '');
            $wpaicg_shortcode_google_model = get_option('wpaicg_shortcode_google_model', 'gemini-pro'); 
            $wpaicg_widget_google_model = get_option('wpaicg_widget_google_model', 'gemini-pro');
            $google_models = get_option('wpaicg_google_model_list', []);
            // Retrieve and extract model IDs from wpaicg_openrouter_model_list
            $openrouter_model_list = get_option('wpaicg_openrouter_model_list', []);
            $openrouter_models = [];
            if (is_array($openrouter_model_list)) {
                foreach ($openrouter_model_list as $model) {
                    if (isset($model['id'])) {
                        $openrouter_models[] = $model['id'];
                    }
                }
            }
    
        
            // Merge and filter the list
            return array_filter(array_merge($google_models,$chatModels, $custom_models,$openrouter_models, [$wpaicg_azure_deployment], [$wpaicg_shortcode_google_model], [$wpaicg_widget_google_model]));
        }

        public function getCompletionEndpointModels() {
            // List of legacy models for the completion endpoint
            return ['text-davinci-003', 'text-ada-001', 'text-curie-001', 'text-babbage-001', 'gpt-3.5-turbo-instruct', 'babbage-002', 'davinci-002'];
        }
        
        
        public function extractTotalTokens($complete, $stream_nav_setting) {
            if ($stream_nav_setting == 1) {
                // For chunked data, access 'usage' as an array
                return isset($complete['usage']['total_tokens']) ? $complete['usage']['total_tokens'] : 0;
            } else {
                // For non-chunked data, access 'usage' as it is (assuming it's an object)
                return isset($complete->usage->total_tokens) ? $complete->usage->total_tokens : 0;
            }
        }
        
        public function extractResponseData($complete, $stream_nav_setting, $isLegacyModel) {
            if ($stream_nav_setting == 1) {
                // For chunked data, the content is already concatenated in processChunkedData
                if (isset($complete['choices'][0]['message']['content'])) {
                    return $complete['choices'][0]['message']['content'];
                } elseif (isset($complete['choices'][0]['text'])) {
                    return $complete['choices'][0]['text'];
                } else {
                    return ''; // Return an empty string if no content is found
                }
            } else {
                // For non-chunked data, extract based on legacy or non-legacy model
                $dataKey = $isLegacyModel ? 'text' : 'message';
                return isset($complete->choices[0]->$dataKey->content) ? $complete->choices[0]->$dataKey->content : (isset($complete->choices[0]->$dataKey) ? $complete->choices[0]->$dataKey : '');
            }
        }
        
        
        
        public function performOpenAiRequest($wpaicg_provider, $open_ai, $apiFunction, $wpaicg_data_request, &$accumulatedData) {
            if ($wpaicg_provider == 'Google') {
                // add source = chat to the request
                $wpaicg_data_request['sourceModule'] = 'chat';
                return $open_ai->chat($wpaicg_data_request);
            } else {
                try {
                    return $open_ai->$apiFunction($wpaicg_data_request, function ($curl_info, $data) use (&$accumulatedData) {
                        $response = json_decode($data, true);
                        if (isset($response['error']) && !empty($response['error'])) {
                            $message = isset($response['error']['message']) && !empty($response['error']['message']) ? $response['error']['message'] : '';
                            if (empty($message) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key') {
                                $message = "Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.";
                            }
                            $this->handleStreamErrorMessage($message);
                        } else {
                            echo $data;
                            ob_implicit_flush( true );
                            // Flush and end buffer if it exists
                            if (ob_get_level() > 0) {
                                ob_end_flush();
                            }
                            $accumulatedData .= $data; // Append data to the accumulator
                            return strlen($data);
                        }
                    });
                } catch (\Exception $exception) {
                    $message = $exception->getMessage();
                    $this->wpaicg_event_message($message);
                }
            }

        }

        public function wpaicg_event_message($words)
        {
            $words = explode(' ', $words);
            $words[count($words) + 1] = '[LIMITED]';
            foreach ($words as $key => $word) {
                echo "event: message\n";
                if ($key == 0) {
                    echo 'data: {"choices":[{"delta":{"content":"' . $word . '"}}]}';
                } else {
                    if ($word == '[LIMITED]') {
                        echo 'data: [LIMITED]';
                    } else {
                        echo 'data: {"choices":[{"delta":{"content":" ' . $word . '"}}]}';
                    }
                }
                echo "\n\n";
				ob_implicit_flush( true );
                // Flush and end buffer if it exists
                if (ob_get_level() > 0) {
                    ob_end_flush();
                }
            }
        }
        
        public function handleStreamErrorMessage($message) {
            $words = explode(' ', $message);

            foreach ($words as $key => $word) {
                echo "event: message\n";
                $data = $key == 0 ? '{"choices":[{"delta":{"content":"' . $word . '"}}]}' : '{"choices":[{"delta":{"content":" ' . $word . '"}}]}';
                echo "data: $data\n\n";
				ob_implicit_flush( true );
                // Flush and end buffer if it exists
                if (ob_get_level() > 0) {
                    ob_end_flush();
                }
            }

            // Send finish_reason stop after the message
            echo 'data: {"choices":[{"finish_reason":"stop"}]}';
            echo "\n\n";
            ob_implicit_flush( true );
            // Flush and end buffer if it exists
            if (ob_get_level() > 0) {
                ob_end_flush();
            }
        }

        public function processChunkedData($accumulatedData, $wpaicg_chatgpt_messages, $wpaicg_ai_model, $isChatEndpoint) {
            // First, check for an error in the accumulated data
            $decodedData = json_decode($accumulatedData, true);
            if (isset($decodedData['error']['message'])) {
                echo "event: message\n";
                echo 'data: {"choices":[{"delta":{"content":"' . $decodedData['error']['message'] . '"}}]}';
                echo "\n\n";
                echo 'data: {"choices":[{"finish_reason":"stop"}]}';
                echo "\n\n";
                ob_implicit_flush( true );
                // Flush and end buffer if it exists
                if (ob_get_level() > 0) {
                    ob_end_flush();
                }
                return;
            }
            // Parse the chunked data
            $chunks = explode("\n\n", $accumulatedData);
            $completeData = [];
            $id = $created = null;
        
            foreach ($chunks as $chunk) {
                if (trim($chunk) != "") {
                    $jsonStart = strpos($chunk, "{");
                    if ($jsonStart !== false) {
                        $decodedChunk = json_decode(substr($chunk, $jsonStart), true);
                        if ($decodedChunk !== null) {
                            if ($isChatEndpoint) {
                                if (isset($decodedChunk['choices'][0]['delta']['content'])) {
                                    $completeData[] = $decodedChunk['choices'][0]['delta']['content'];
                                }
                            } else {
                                if (isset($decodedChunk['choices'][0]['text'])) {
                                    $completeData[] = $decodedChunk['choices'][0]['text'];
                                }
                            }
                            if (is_null($id) && isset($decodedChunk['id']) && isset($decodedChunk['created'])) {
                                $id = $decodedChunk['id'];
                                $created = $decodedChunk['created'];
                            }
                        }
                    }
                }
            }
        
            $finalMessage = implode("", $completeData);
        
            // Calculate tokens
            $promptCharacters = array_sum(array_map('strlen', array_column($wpaicg_chatgpt_messages, 'content')));
            $prompt_tokens = intval($promptCharacters / 100 * 21);
            $completionCharacters = strlen($finalMessage);
            $completion_tokens = intval($completionCharacters / 100 * 21);
            $total_tokens = $prompt_tokens + $completion_tokens;
        
            // Construct the complete array with usage information
            return [
                "id" => $id,
                "object" => "chat.completion",
                "created" => $created,
                "model" => $wpaicg_ai_model,
                "choices" => [
                    [
                        "index" => 0,
                        "message" => [
                            "role" => "assistant",
                            "content" => $finalMessage
                        ],
                        "finish_reason" => "stop"
                    ]
                ],
                "usage" => [
                    "prompt_tokens" => $prompt_tokens,
                    "completion_tokens" => $completion_tokens,
                    "total_tokens" => $total_tokens
                ]
            ];
        }

        public function determine_stream_nav_setting($chat_source, $wpaicg_provider) {

            global $wpdb;

            // If the provider is Google, return '0' for streaming
            if ($wpaicg_provider === 'Google') {
                return '0';
            }

            if ($chat_source === 'shortcode') {
                return get_option('wpaicg_shortcode_stream', '1');
            } elseif ($chat_source === 'widget') {
                return get_option('wpaicg_widget_stream', '1');
            } elseif (strpos($chat_source, 'Shortcode ID:') !== false || strpos($chat_source, 'Widget ID:') !== false) {
                // Extracting the numeric ID from the chat source
                $post_id = intval(str_replace(['Shortcode ID:', 'Widget ID:'], '', $chat_source));
                
                // Fetch the post content from the database
                $post_content = $wpdb->get_var($wpdb->prepare("SELECT post_content FROM {$wpdb->posts} WHERE ID = %d", $post_id));

                if ($post_content) {
                    $bot_settings = json_decode($post_content, true);
                    if (isset($bot_settings['openai_stream_nav'])) {
                        return $bot_settings['openai_stream_nav'];
                    }
                }

            }
            return '0';
        }

        public function getIpAddress()
        {
            $ipAddress = '';
            if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
                // to get shared ISP IP address
                $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
            } else if (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // check for IPs passing through proxy servers
                // check if multiple IP addresses are set and take the first one
                $ipAddressList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                foreach ($ipAddressList as $ip) {
                    if (! empty($ip)) {
                        // if you prefer, you can check for valid IP address here
                        $ipAddress = $ip;
                        break;
                    }
                }
            } else if (! empty($_SERVER['HTTP_X_FORWARDED'])) {
                $ipAddress = $_SERVER['HTTP_X_FORWARDED'];
            } else if (! empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
                $ipAddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
            } else if (! empty($_SERVER['HTTP_FORWARDED_FOR'])) {
                $ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
            } else if (! empty($_SERVER['HTTP_FORWARDED'])) {
                $ipAddress = $_SERVER['HTTP_FORWARDED'];
            } else if (! empty($_SERVER['REMOTE_ADDR'])) {
                $ipAddress = $_SERVER['REMOTE_ADDR'];
            }

            // Replace ::1 with 127.0.0.1
            if ($ipAddress === '::1') {
                $ipAddress = '127.0.0.1';
            }

            return $ipAddress;
        }

        public function check_banned_ips($wpaicg_chat_source, $wpaicg_provider) {
            // Get the user's IP
            $user_ip = $this->getIpAddress();
        
            // Retrieve the list of banned IPs from the database
            $banned_ips = explode(',', get_option('wpaicg_banned_ips', ''));
            $banned_ips = array_map('trim', $banned_ips); // Trim spaces
        
            // Check if the user's IP is in the banned list
            if (in_array($user_ip, $banned_ips)) {
                $stream_nav_setting = $this->determine_stream_nav_setting($wpaicg_chat_source, $wpaicg_provider);
                $stream_ban_result = ['msg' => esc_html__('You are not allowed to access this feature.', 'gpt3-ai-content-generator'), 'ipBanned' => true];
        
                if ($stream_nav_setting == 1) {
                    header('Content-Type: text/event-stream');
                    header('Cache-Control: no-cache');
                    header('X-Accel-Buffering: no');
                    echo "data: " . wp_json_encode($stream_ban_result) . "\n\n";
                    ob_implicit_flush( true );
                    // Flush and end buffer if it exists
                    if (ob_get_level() > 0) {
                        ob_end_flush();
                    }
                } else {
                    wp_send_json(array(
                        'status' => 'error',
                        'msg'    => esc_html__('You are not allowed to access this feature.', 'gpt3-ai-content-generator')
                    ));
                    exit;
                }
            }
        }
        

        public function check_banned_words($message, $wpaicg_chat_source, $wpaicg_provider) {
            // Retrieve the list of banned words from the database
            $banned_words = explode(',', get_option('wpaicg_banned_words', ''));
            $banned_words = array_map('trim', $banned_words); // Trim spaces
            $banned_words = array_filter($banned_words); // Remove empty elements
        
            // Convert message and banned words to lowercase for case-insensitive search
            $message_lower = strtolower($message);
            $banned_words_lower = array_map('strtolower', $banned_words);
        
            // Break the message into individual words
            $message_words = explode(' ', $message_lower);
        
            // Check if any word in the message is a banned word
            foreach ($message_words as $word) {
                if (in_array($word, $banned_words_lower)) {
                    $stream_nav_setting = $this->determine_stream_nav_setting($wpaicg_chat_source, $wpaicg_provider);
                    $stream_ban_result = ['msg'    => esc_html__('Your message contains prohibited words. Please modify your message and try again.', 'gpt3-ai-content-generator'), 'messageFlagged' => true];
                    if ($stream_nav_setting == 1) {
                        header('Content-Type: text/event-stream');
                        header('Cache-Control: no-cache');
                        header( 'X-Accel-Buffering: no' );
                        echo "data: " . wp_json_encode($stream_ban_result) . "\n\n";
                        ob_implicit_flush( true );
                        // Flush and end buffer if it exists
                        if (ob_get_level() > 0) {
                            ob_end_flush();
                        }
                    } else {
                        wp_send_json(array(
                            'status' => 'error',
                            'msg'    => esc_html__('Your message contains prohibited words. Please modify your message and try again.', 'gpt3-ai-content-generator')
                        ));
                        exit;
                    }
                }
            }
        }
        
        public function getCurrentUsername() {
            if (is_user_logged_in()) {
                $current_user = wp_get_current_user();
                return $current_user->user_login; // Return the username of the logged-in user.
            } else {
                return null; // Return null if no user is logged in.
            }
        }
        
        public function wpaicg_save_chat_log($wpaicg_log_id, $wpaicg_log_data,$type = 'user', $message = '',$tokens = 0, $flag = false, $request = '')
        {
            global $wpdb;
            if($wpaicg_log_id){
                $wpaicg_log_data[] = array('message' => $message, 'type' => $type, 'date' => time(), 'token' => $tokens, 'flag' => $flag, 'request' => $request);
                $wpdb->update($wpdb->prefix.'wpaicg_chatlogs', array(
                    'data' => json_encode($wpaicg_log_data),
                    'created_at' => time()
                ), array(
                    'id' => $wpaicg_log_id
                ));
            }
        }

        public function wpaicg_embeddings_result($wpaicg_provider,$use_default_embedding,$selected_embedding_model,$selected_embedding_provider,$open_ai,$wpaicg_pinecone_api,$wpaicg_pinecone_environment,$wpaicg_message, $wpaicg_chat_embedding_top,$wpaicg_chat_source, $namespace = false)
        {
            $result = array('status' => 'error','data' => '');
            if(!empty($wpaicg_pinecone_api) && !empty($wpaicg_pinecone_environment) ) {

                // Determine the embedding engine and model
                $embedding_engine = $open_ai; // default engine
                $model = 'text-embedding-ada-002'; // default model

                if (isset($use_default_embedding) && $use_default_embedding != 1) {
                    // Custom embedding logic
                    if (!empty($selected_embedding_model) && !empty($selected_embedding_provider)) {
                        $model = $selected_embedding_model;
                        try {
                            $embedding_engine = WPAICG_Util::get_instance()->initialize_embedding_engine($selected_embedding_provider, $wpaicg_provider);
                        } catch (\Exception $e) {
                            $result['msg'] = $e->getMessage();
                            return $result;
                        }
                    }
                } else {
                    switch ($wpaicg_provider) {
                        case 'Azure':
                            $model = get_option('wpaicg_azure_embeddings', 'text-embedding-ada-002');
                            break;
                        case 'Google':
                            $model = get_option('wpaicg_google_embeddings', 'embedding-001');
                            break;
                        default:
                            $model = get_option('wpaicg_openai_embeddings', 'text-embedding-3-small');
                            break;
                    }
            
                    $main_embedding_model = get_option('wpaicg_main_embedding_model', '');
                    if (!empty($main_embedding_model)) {
                        $model_parts = explode(':', $main_embedding_model);
                        if (count($model_parts) === 2) {
                            $model = $model_parts[1];
                            try {
                                $embedding_engine = WPAICG_Util::get_instance()->initialize_embedding_engine($model_parts[0], $wpaicg_provider);
                            } catch (\Exception $e) {
                                $result['msg'] = $e->getMessage();
                                return $result;
                            }
                        }
                    }
                }

                // Prepare the API call parameters
                $apiParams = [
                    'input' => $wpaicg_message,
                    'model' => $model
                ];

                // Make the API call
                $response = $embedding_engine->embeddings($apiParams);
                $response = json_decode($response, true);
                if (isset($response['error']) && !empty($response['error'])) {
                    $result['data'] = $response['error']['message'];
                    if(empty($result['data']) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key'){
                        $result['data'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                    }
                } else {
                    $embedding = $response['data'][0]['embedding'];
                    if (!empty($embedding)) {
                        $result['tokens'] = $response['usage']['total_tokens'];
                        $headers = array(
                            'Content-Type' => 'application/json',
                            'Api-Key' => $wpaicg_pinecone_api
                        );
                        $pinecone_body = array(
                            'vector' => $embedding,
                            'topK' => $wpaicg_chat_embedding_top
                        );
                        if($namespace){
                            $pinecone_body['namespace'] = $namespace;
                        }
                        $response = wp_remote_post('https://' . $wpaicg_pinecone_environment . '/query', array(
                            'headers' => $headers,
                            'body' => json_encode($pinecone_body)
                        ));
                        if (is_wp_error($response)) {
                            $result['data'] = esc_html($response->get_error_message());
                        } else {
                            $body_content = wp_remote_retrieve_body($response);
                            $body = json_decode($response['body'], true);
                            if ($body) {
                                if (isset($body['matches']) && is_array($body['matches']) && count($body['matches'])) {
                                    $data = '';
                                    foreach($body['matches'] as $match){
                                        $wpaicg_embedding = get_post($match['id']);
                                        if ($wpaicg_embedding) {
                                            $data .= empty($data) ? $wpaicg_embedding->post_content : "\n".$wpaicg_embedding->post_content;
                                        }

                                    }
                                    $result['data'] = $data;
                                    $result['status'] = 'success';
                                }
                                else{
                                    $result['status'] = 'empty';
                                }
                            }
                            else{
                                $stream_nav_setting = $this->determine_stream_nav_setting($wpaicg_chat_source, $wpaicg_provider);
                                $stream_pinecone_error = ['msg'    => esc_html__($body_content, 'gpt3-ai-content-generator'), 'pineconeError' => true];
                                if ($stream_nav_setting == 1) {
                                    header('Content-Type: text/event-stream');
                                    header('Cache-Control: no-cache');
                                    header( 'X-Accel-Buffering: no' );
                                    echo "data: " . wp_json_encode($stream_pinecone_error) . "\n\n";
                                    ob_implicit_flush( true );
                                    // Flush and end buffer if it exists
                                    if (ob_get_level() > 0) {
                                        ob_end_flush();
                                    }
                                    exit;
                                } else {
                                    $result['data'] = $body_content ? $body_content : esc_html__('No results from Pinecone.','gpt3-ai-content-generator');
                                }
                            }
                        }
                    }
                }
            }
            else{
                $result['data'] = esc_html__('Something wrong with Pinecone setup. Check your Pinecone settings.','gpt3-ai-content-generator');
            }
            return $result;
        }
        public function wpaicg_embeddings_result_qdrant($wpaicg_provider, $use_default_embedding,$selected_embedding_model,$selected_embedding_provider,$open_ai, $wpaicg_qdrant_api_key, $wpaicg_qdrant_endpoint, $wpaicg_chat_qdrant_collection, $wpaicg_message, $wpaicg_chat_source,$wpaicg_chat_embedding_top, $namespace = false)
        {
            $result = array('status' => 'error','data' => '');
            if(!empty($wpaicg_qdrant_api_key) && !empty($wpaicg_qdrant_endpoint && !empty($wpaicg_chat_qdrant_collection))) {

                // Determine the embedding engine and model
                $embedding_engine = $open_ai; // default engine
                $model = 'text-embedding-ada-002'; // default model

                if (isset($use_default_embedding) && $use_default_embedding != 1) {
                    // Custom embedding logic
                    if (!empty($selected_embedding_model) && !empty($selected_embedding_provider)) {
                        $model = $selected_embedding_model;
                        try {
                            $embedding_engine = WPAICG_Util::get_instance()->initialize_embedding_engine($selected_embedding_provider, $wpaicg_provider);
                        } catch (\Exception $e) {
                            $result['msg'] = $e->getMessage();
                            return $result;
                        }
                    }
                } else {
                    // Use default embedding logic
                    switch ($wpaicg_provider) {
                        case 'Azure':
                            $model = get_option('wpaicg_azure_embeddings', 'text-embedding-ada-002');
                            break;
                        case 'Google':
                            $model = get_option('wpaicg_google_embeddings', 'embedding-001');
                            break;
                        default:
                            $model = get_option('wpaicg_openai_embeddings', 'text-embedding-3-small');
                            break;
                    }
            
                    $main_embedding_model = get_option('wpaicg_main_embedding_model', '');
                    if (!empty($main_embedding_model)) {
                        $model_parts = explode(':', $main_embedding_model);
                        if (count($model_parts) === 2) {
                            $model = $model_parts[1];
                            try {
                                $embedding_engine = WPAICG_Util::get_instance()->initialize_embedding_engine($model_parts[0], $wpaicg_provider);
                            } catch (\Exception $e) {
                                $result['msg'] = $e->getMessage();
                                return $result;
                            }
                        }
                    }
                }

                // Prepare the API call parameters
                $apiParams = [
                    'input' => $wpaicg_message,
                    'model' => $model
                ];

                // Make the API call
                $response = $embedding_engine->embeddings($apiParams);
                $response = json_decode($response, true);
                if (isset($response['error']) && !empty($response['error'])) {
                    $result['data'] = $response['error']['message'];
                    if(empty($result['data']) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key'){
                        $result['data'] = 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                    }
                } else {
                    $embedding = $response['data'][0]['embedding'];
                    if (!empty($embedding)) {
                        $result['tokens'] = $response['usage']['total_tokens'];
                        // Prepare Qdrant search query
                        $queryData = [
                            'vector' => $embedding,
                            'limit' => intval($wpaicg_chat_embedding_top)
                        ];
                        
                        // Use namespace if it exists and is not empty; otherwise, use a fixed "default" string
                        $group_id_value = $namespace ?: "default";

                        $queryData['filter'] = [
                            'must' => [
                                [
                                    'key' => 'group_id',
                                    'match' => [
                                        'value' => $group_id_value
                                    ]
                                ]
                            ]
                        ];

                        $query = json_encode($queryData);

                        // Send request to Qdrant
                        $response = wp_remote_post($wpaicg_qdrant_endpoint . '/collections/' . $wpaicg_chat_qdrant_collection . '/points/search', array(
                            'method' => 'POST',
                            'headers' => [
                                'api-key' => $wpaicg_qdrant_api_key,
                                'Content-Type' => 'application/json'
                            ],
                            'body' => $query
                        ));
                        if (is_wp_error($response)) {
                            $result['data'] = esc_html($response->get_error_message());
                        } else {
                            $bodyContent = wp_remote_retrieve_body($response);
                            $body = json_decode($bodyContent, true);
                            if (isset($body['result']) && is_array($body['result'])) {
                                $data = '';
                                foreach ($body['result'] as $match) {
                                    // Retrieve post content for each matched ID
                                    $wpaicg_embedding = get_post($match['id']);
                                    if ($wpaicg_embedding) {
                                        $data .= empty($data) ? $wpaicg_embedding->post_content : "\n" . $wpaicg_embedding->post_content;
                                    }
                                }
                                $result['data'] = $data;
                                $result['status'] = 'success';
                            } else {
                                $errror_message_from_api = isset($body['status']['error']) ? $body['status']['error'] : esc_html__('No results from Qdrant.', 'gpt3-ai-content-generator');
                                $errror_message_from_api = esc_html__('Response from Qdrant: ', 'gpt3-ai-content-generator') . $errror_message_from_api;
                                $result['status'] = 'error';
                                $stream_nav_setting = $this->determine_stream_nav_setting($wpaicg_chat_source, $wpaicg_provider);
                                $stream_pinecone_error = ['msg'    => $errror_message_from_api, 'pineconeError' => true];
                                if ($stream_nav_setting == 1) {
                                    header('Content-Type: text/event-stream');
                                    header('Cache-Control: no-cache');
                                    header( 'X-Accel-Buffering: no' );
                                    echo "data: " . wp_json_encode($stream_pinecone_error) . "\n\n";
                                    ob_implicit_flush( true );
                                    // Flush and end buffer if it exists
                                    if (ob_get_level() > 0) {
                                        ob_end_flush();
                                    }
                                    exit;
                                } else {
                                    $result['data'] = $errror_message_from_api;
                                }
                            }
                        }
                    }
                }
            }
            else{
                $result['data'] = esc_html__('Something wrong with Qdrant setup3. Check your Qdrant settings.','gpt3-ai-content-generator');
            }
            return $result;
        }


        public function wpaicg_chatbox($atts)
        {
            ob_start();
            include WPAICG_PLUGIN_DIR . 'admin/extra/wpaicg_chatbox.php';
            $wpaicg_chatbox = ob_get_clean();
            return $wpaicg_chatbox;
        }

        public function wpaicg_chatbox_widget()
        {
            ob_start();
            include WPAICG_PLUGIN_DIR . 'admin/extra/wpaicg_chatbox_widget.php';
            $wpaicg_chatbox = ob_get_clean();
            return $wpaicg_chatbox;
        }
    }
    WPAICG_Chat::get_instance();
}
