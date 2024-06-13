<?php

namespace WPAICG;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\WPAICG\\WPAICG_Forms')) {
    class WPAICG_Forms
    {
        private static $instance = null;
        public $wpaicg_engine = 'gpt-3.5-turbo';
        public $wpaicg_max_tokens = 2000;
        public $wpaicg_temperature = 0;
        public $wpaicg_top_p = 1;
        public $wpaicg_best_of = 1;
        public $wpaicg_frequency_penalty = 0;
        public $wpaicg_presence_penalty = 0;
        public $wpaicg_stop = [];

        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action('wp_ajax_wpaicg_update_template',[$this,'wpaicg_update_template']);
            add_action('wp_ajax_wpaicg_template_delete',[$this,'wpaicg_template_delete']);
            add_shortcode('wpaicg_form',[$this,'wpaicg_form_shortcode']);
            add_action( 'admin_menu', array( $this, 'wpaicg_menu' ) );
            add_action('wp_enqueue_scripts',[$this,'enqueue_scripts']);
            add_action('wp_ajax_wpaicg_form_log', [$this,'wpaicg_form_log']);
            add_action('wp_ajax_wpaicg_form_duplicate', [$this,'wpaicg_form_duplicate']);
            // wpaicg_export_ai_forms
            add_action('wp_ajax_wpaicg_export_ai_forms', [$this,'wpaicg_export_ai_forms']);
            add_action('wp_ajax_nopriv_wpaicg_export_ai_forms', [$this,'wpaicg_export_ai_forms']);
            // wpaicg_import_ai_forms
            add_action('wp_ajax_wpaicg_import_ai_forms', [$this,'wpaicg_import_ai_forms']);
            add_action('wp_ajax_nopriv_wpaicg_import_ai_forms', [$this,'wpaicg_import_ai_forms']);
            // wpaicg_delete_all_forms
            add_action('wp_ajax_wpaicg_delete_all_forms', [$this,'wpaicg_delete_all_forms']);
            add_action('wp_ajax_nopriv_wpaicg_delete_all_forms', [$this,'wpaicg_delete_all_forms']);
            add_action('wp_ajax_nopriv_wpaicg_form_log', [$this,'wpaicg_form_log']);
            if ( ! wp_next_scheduled( 'wpaicg_remove_forms_tokens_limited' ) ) {
                wp_schedule_event( time(), 'hourly', 'wpaicg_remove_forms_tokens_limited' );
            }
            add_action( 'wpaicg_remove_forms_tokens_limited', array( $this, 'wpaicg_remove_tokens_limit' ) );
            add_action('wp_ajax_wpaicg_save_feedback', array($this, 'wpaicg_save_feedback'));
            add_action('wp_ajax_nopriv_wpaicg_save_feedback', array($this, 'wpaicg_save_feedback'));  
            add_action('wp_ajax_wpaicg_save_prompt_feedback', array($this, 'wpaicg_save_prompt_feedback'));
            add_action('wp_ajax_nopriv_wpaicg_save_prompt_feedback', array($this, 'wpaicg_save_prompt_feedback'));
            // wpaicg_delete_all_logs
            add_action('wp_ajax_wpaicg_delete_all_logs', [$this,'wpaicg_delete_all_logs']);
        }

        public function wpaicg_delete_all_logs() {
            check_ajax_referer('wpaicg_delete_all_logs_nonce', 'nonce');
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'You do not have sufficient permissions']);
                return;
            }
        
            global $wpdb;
            $wpaicgFormLogTable = $wpdb->prefix . 'wpaicg_form_logs';
            $wpaicgFeedbackTable = $wpdb->prefix . 'wpaicg_form_feedback';
        
            // Truncate the form logs table
            $resultLogs = $wpdb->query("TRUNCATE TABLE `$wpaicgFormLogTable`");
            // Truncate the feedback table
            $resultFeedback = $wpdb->query("TRUNCATE TABLE `$wpaicgFeedbackTable`");
        
            if ($resultLogs === false || $resultFeedback === false) {
                wp_send_json_error(['message' => 'Failed to delete logs and feedback']);
            } else {
                wp_send_json_success(['message' => 'All logs and feedback have been deleted successfully']);
            }
        }

        function wpaicg_export_ai_forms() {
            global $wpdb, $wp_filesystem;
        
            // Security and permissions checks
            $nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : '';
            if (!wp_verify_nonce($nonce, 'wpaicg_export_ai_forms')) {
                wp_send_json_error(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
                return;
            }
            if (!current_user_can('manage_options')) {
                wp_send_json_error(esc_html__('You do not have sufficient permissions to access this page.', 'gpt3-ai-content-generator'));
                return;
            }
        
            // WP_Filesystem initialization
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            WP_Filesystem();
        
            // Fetch AI forms and their meta
            $forms = $wpdb->get_results("SELECT ID, post_title, post_content FROM {$wpdb->posts} WHERE post_type = 'wpaicg_form' AND post_status = 'publish'", ARRAY_A);
            $settings = [];
            foreach ($forms as $form) {
                if (!empty($form['post_content'])) {
                    $meta = get_post_meta($form['ID']);
                    // Optionally filter or clean meta data here
                    $settings[] = [
                        'title' => $form['post_title'],
                        'content' => maybe_unserialize($form['post_content']),
                        'meta' => $meta, // Include meta data
                    ];
                }
            }
        
            // JSON encoding and file saving
            $json_content = json_encode($settings);
            $upload_dir = wp_upload_dir();
            $file_name = 'ai_forms_export_' . wp_rand() . '.json';
            $file_path = $upload_dir['basedir'] . '/' . $file_name;
        
            if ($wp_filesystem->put_contents($file_path, $json_content)) {
                wp_send_json_success(['url' => $upload_dir['baseurl'] . '/' . $file_name]);
            } else {
                wp_send_json_error(esc_html__('Failed to export AI forms.', 'gpt3-ai-content-generator'));
            }
        }

        function wpaicg_import_ai_forms() {
            // Security checks
            if (!check_ajax_referer('wpaicg_import_ai_forms_nonce', 'nonce', false)) {
                wp_send_json_error('Nonce verification failed');
                return;
            }
            
            if (!current_user_can('manage_options')) {
                wp_send_json_error('You do not have sufficient permissions');
                return;
            }
            
            // Check if file is uploaded and read its contents
            if (isset($_FILES['file']['tmp_name'])) {
                $file_contents = file_get_contents($_FILES['file']['tmp_name']);
                $data = json_decode($file_contents, true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    wp_send_json_error('Invalid JSON file');
                    return;
                }
                
                foreach ($data as $form_data) {
                    if (empty($form_data['title']) || empty($form_data['content'])) {
                        continue;
                    }
                    
                    $post_data = [
                        'post_title'   => sanitize_text_field($form_data['title']),
                        'post_content' => wp_kses_post($form_data['content']),
                        'post_status'  => 'publish',
                        'post_type'    => 'wpaicg_form',
                    ];
                    
                    $post_id = wp_insert_post($post_data, true);
                    
                    if (is_wp_error($post_id)) {
                        continue;
                    }
                    
                    if (!empty($form_data['meta']) && is_array($form_data['meta'])) {
                        foreach ($form_data['meta'] as $meta_key => $meta_value) {
                            // Ensure meta values are saved as simple strings where appropriate
                            if (is_array($meta_value) && count($meta_value) === 1) {
                                // If it's a single-element array, extract the value directly
                                $meta_value = reset($meta_value);
                            }
                            update_post_meta($post_id, sanitize_text_field($meta_key), $meta_value);
                        }
                    }
                }
                
                wp_send_json_success('AI forms imported successfully');
            } else {
                wp_send_json_error('No file uploaded');
            }
        }

        function wpaicg_delete_all_forms() {
            // Security checks
            if (!check_ajax_referer('wpaicg_delete_all_forms_nonce', 'nonce', false)) {
                wp_send_json_error('Nonce verification failed');
                return;
            }
        
            if (!current_user_can('manage_options')) {
                wp_send_json_error('You do not have sufficient permissions');
                return;
            }
        
            $args = [
                'post_type'      => 'wpaicg_form',
                'posts_per_page' => -1,
                'fields'         => 'ids', // Only get post IDs to improve performance
            ];
        
            $forms = get_posts($args);
        
            // Check if there are forms to delete
            if (empty($forms)) {
                wp_send_json_success('There are no custom forms to delete.');
                return; // Exit the function if no forms found
            }
        
            foreach ($forms as $form_id) {
                wp_delete_post($form_id, true); // Set to true to bypass trash and permanently delete
            }
        
            wp_send_json_success('All custom forms have been deleted.');
        }
        

        public function wpaicg_remove_tokens_limit()
        {
            global $wpdb;
            $wpaicg_settings = get_option('wpaicg_limit_tokens_form',[]);
            $widget_reset_limit = isset($wpaicg_settings['reset_limit']) && !empty($wpaicg_settings['reset_limit']) ? $wpaicg_settings['reset_limit'] : 0;
            if($widget_reset_limit > 0) {
                $widget_time = time() - ($widget_reset_limit * 86400);
                $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "wpaicg_formtokens WHERE created_at < %s",$widget_time));
            }
        }

        public function wpaicg_form_log()
        {
            global $wpdb;
        
            $wpaicg_result = ['status' => 'success'];
            $wpaicg_nonce = sanitize_text_field($_REQUEST['_wpnonce']);
        
            // Verify nonce
            if (!wp_verify_nonce($wpaicg_nonce, 'wpaicg-formlog')) {
                $wpaicg_result['msg'] = esc_html__('Nonce verification failed', 'gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
                exit;
            }
        
            // Required fields
            $required_fields = ['prompt_id', 'prompt_name', 'prompt_response', 'engine'];
            foreach ($required_fields as $field) {
                if (empty($_REQUEST[$field])) {
                    wp_send_json($wpaicg_result);
                    exit;
                }
            }

            $userID = is_user_logged_in() ? get_current_user_id() : '';

            // Retrieve prompt
            if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
                $prompt_id = sanitize_text_field($_REQUEST['id']);
            } 
            $wpaicg_prompt = WPAICG_Playground::get_instance()->get_defined_prompt($prompt_id);

            // Log data
            $log = [
                'prompt' => wp_kses_post($wpaicg_prompt),
                'data' => wp_kses_post($_REQUEST['prompt_response']),
                'prompt_id' => sanitize_text_field($_REQUEST['prompt_id']),
                'name' => sanitize_text_field($_REQUEST['prompt_name']),
                'model' => sanitize_text_field($_REQUEST['engine']),
                'duration' => sanitize_text_field($_REQUEST['duration']),
                'eventID' => sanitize_text_field($_REQUEST['eventID']),
                'userID' => $userID,
                'created_at' => time()
            ];
        
            if (!empty($_REQUEST['source_id'])) {
                $log['source'] = sanitize_text_field($_REQUEST['source_id']);
            }
        
            // Calculate tokens
            $wpaicg_generator = WPAICG_Generator::get_instance();
            $log['tokens'] = ceil($wpaicg_generator->wpaicg_count_words($log['data']) * 1000 / 750);
        
            // Save log and update tokens
            WPAICG_Account::get_instance()->save_log('forms', $log['tokens']);
            $wpdb->insert($wpdb->prefix . 'wpaicg_form_logs', $log);
        
            $wpaicg_playground = WPAICG_Playground::get_instance();
            $wpaicg_tokens_handling = $wpaicg_playground->wpaicg_token_handling('form');
        
            if ($wpaicg_tokens_handling['limit']) {
                if ($wpaicg_tokens_handling['token_id']) {
                    $wpdb->update(
                        $wpdb->prefix . $wpaicg_tokens_handling['table'],
                        ['tokens' => ($log['tokens'] + $wpaicg_tokens_handling['old_tokens'])],
                        ['id' => $wpaicg_tokens_handling['token_id']]
                    );
                } else {
                    $wpaicg_prompt_token_data = [
                        'tokens' => $log['tokens'],
                        'created_at' => time()
                    ];
        
                    if (is_user_logged_in()) {
                        $wpaicg_prompt_token_data['user_id'] = get_current_user_id();
                    } else {
                        $wpaicg_prompt_token_data['session_id'] = $wpaicg_tokens_handling['client_id'];
                    }
        
                    $wpdb->insert($wpdb->prefix . $wpaicg_tokens_handling['table'], $wpaicg_prompt_token_data);
                }
            }
        
            wp_send_json($wpaicg_result);
        }
        

        function wpaicg_save_feedback() {

            // Check the nonce. 
            check_ajax_referer('wpaicg-ajax-nonce', 'nonce');

            // Get the feedback data from the AJAX request
            $formID = sanitize_text_field($_POST['formID']);
            $eventID = sanitize_text_field($_POST['eventID']);
            $feedback = sanitize_text_field($_POST['feedback']);
            $comment = sanitize_textarea_field($_POST['comment']);
            $formname = sanitize_text_field($_POST['formname']);
            $sourceID = sanitize_text_field($_POST['sourceID']);
            $formResponse = sanitize_text_field($_POST['response']);
                
            global $wpdb;
            $feedbackTable = $wpdb->prefix . 'wpaicg_form_feedback';
        
            // Insert feedback into the database
            $inserted = $wpdb->insert($feedbackTable, [
                'formID' => $formID,
                'feedback' => $feedback,
                'comment' => $comment,
                'formname' => $formname,
                'source' => $sourceID,
                'response' => $formResponse,
                'eventID' => $eventID,
                'created_at' => current_time('mysql')
            ]);
        
            if ($inserted) {
                echo json_encode(['status' => 'success', 'msg' => esc_html__('Thank you for your feedback.', 'gpt3-ai-content-generator')]);
            } else {
                echo json_encode(['status' => 'error', 'msg' => esc_html__('Failed to save feedback.', 'gpt3-ai-content-generator')]);
            }
        
            wp_die();
        }

        function wpaicg_save_prompt_feedback() {

            // Check the nonce. 
            check_ajax_referer('wpaicg-ajax-nonce', 'nonce');

            // Get the feedback data from the AJAX request
            $formID = sanitize_text_field($_POST['formID']);
            $eventID = sanitize_text_field($_POST['eventID']);
            $feedback = sanitize_text_field($_POST['feedback']);
            $comment = sanitize_textarea_field($_POST['comment']);
            $formname = sanitize_text_field($_POST['formname']);
            $sourceID = sanitize_text_field($_POST['sourceID']);
            $formResponse = sanitize_text_field($_POST['response']);
                
            global $wpdb;
            $feedbackTable = $wpdb->prefix . 'wpaicg_prompt_feedback';
        
            // Insert feedback into the database
            $inserted = $wpdb->insert($feedbackTable, [
                'formID' => $formID,
                'feedback' => $feedback,
                'comment' => $comment,
                'formname' => $formname,
                'source' => $sourceID,
                'response' => $formResponse,
                'eventID' => $eventID,
                'created_at' => current_time('mysql')
            ]);
        
            if ($inserted) {
                echo json_encode(['status' => 'success', 'msg' => esc_html__('Thank you for your feedback.', 'gpt3-ai-content-generator')]);
            } else {
                echo json_encode(['status' => 'error', 'msg' => esc_html__('Failed to save feedback.', 'gpt3-ai-content-generator')]);
            }
        
            wp_die();
        }

    
        public function enqueue_scripts()
        {
            wp_enqueue_script('wpaicg-gpt-form',WPAICG_PLUGIN_URL.'public/js/wpaicg-form-shortcode.js',array(),null,true);
        }

        public function wpaicg_menu()
        {
            add_submenu_page(
                'wpaicg',
                esc_html__('AI Forms','gpt3-ai-content-generator'),
                esc_html__('AI Forms','gpt3-ai-content-generator'),
                'wpaicg_forms',
                'wpaicg_forms',
                array( $this, 'wpaicg_forms' ),
                5
            );
        }

        public function wpaicg_form_shortcode($atts)
        {
            ob_start();
            include WPAICG_PLUGIN_DIR . 'admin/extra/wpaicg_form_shortcode.php';
            return ob_get_clean();
        }

        public function wpaicg_template_delete()
        {
            $wpaicg_result = array('status' => 'success');
            if(!current_user_can('wpaicg_forms_forms')){
                $wpaicg_result['status'] = 'error';
                $wpaicg_result['msg'] = esc_html__('You do not have permission for this action.','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
            }
            if ( ! wp_verify_nonce( $_POST['nonce'], 'wpaicg-ajax-nonce' ) ) {
                $wpaicg_result['msg'] = esc_html__('Nonce verification failed','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
            }
            if(isset($_POST['id']) && !empty($_POST['id'])){
                wp_delete_post(sanitize_text_field($_POST['id']));
            }
            wp_send_json($wpaicg_result);
        }

        public function wpaicg_form_duplicate()
        {
            $wpaicg_result = array('status' => 'success');
            if(!current_user_can('wpaicg_forms_forms')){
                $wpaicg_result['status'] = 'error';
                $wpaicg_result['msg'] = esc_html__('You do not have permission for this action.','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
            }
            if ( ! wp_verify_nonce( $_POST['nonce'], 'wpaicg-ajax-nonce' ) ) {
                $wpaicg_result['msg'] = esc_html__('Nonce verification failed','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
            }
            if(isset($_POST['id']) && !empty($_POST['id'])){
                $promptbase = get_post(sanitize_post($_REQUEST['id']));
                $wpaicg_prompt_id = wp_insert_post(array(
                    'post_title' => $promptbase->post_title,
                    'post_type' => 'wpaicg_form',
                    'post_content' => $promptbase->post_content,
                    'post_status' => 'publish'
                ));
                $post_meta = get_post_meta( $promptbase->ID );
                if( $post_meta ) {

                    foreach ( $post_meta as $meta_key => $meta_values ) {

                        if( '_wp_old_slug' == $meta_key ) { // do nothing for this meta key
                            continue;
                        }

                        foreach ( $meta_values as $meta_value ) {
                            add_post_meta( $wpaicg_prompt_id, $meta_key, $meta_value );
                        }
                    }
                }
            }
            wp_send_json($wpaicg_result);
        }

        public function wpaicg_update_template()
        {
            $wpaicg_result = array('status' => 'error', 'msg' => esc_html__('Something went wrong','gpt3-ai-content-generator'));
            if(!current_user_can('wpaicg_forms_forms')){
                $wpaicg_result['status'] = 'error';
                $wpaicg_result['msg'] = esc_html__('You do not have permission for this action.','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
            }
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wpaicg_formai_save' ) ) {
                $wpaicg_result['msg'] = esc_html__('Nonce verification failed','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
            }
            if(
                isset($_POST['title'])
                && !empty($_POST['title'])
                && isset($_POST['description'])
                && !empty($_POST['description'])
                && isset($_POST['prompt'])
                && !empty($_POST['prompt'])
            ){
                $title = sanitize_text_field($_POST['title']);
                $description = sanitize_text_field($_POST['description']);
                if(isset($_POST['id']) && !empty($_POST['id'])){
                    $wpaicg_prompt_id = sanitize_text_field($_POST['id']);
                    wp_update_post(array(
                        'ID' => $wpaicg_prompt_id,
                        'post_title' => $title,
                        'post_content' => $description
                    ));
                }
                else{
                    $wpaicg_prompt_id = wp_insert_post(array(
                        'post_title' => $title,
                        'post_type' => 'wpaicg_form',
                        'post_content' => $description,
                        'post_status' => 'publish'
                    ));
                }
                $template_fields = array('prompt','fields','response','category','engine','max_tokens','temperature','top_p','best_of','frequency_penalty','presence_penalty','stop','color','icon','editor','bgcolor','header','embeddings','vectordb','collections','pineconeindexes','suffix_text','suffix_position','embeddings_limit','use_default_embedding_model','selected_embedding_model','selected_embedding_provider','dans','ddraft','dclear','dnotice','generate_text','noanswer_text','draft_text','clear_text','stop_text','cnotice_text','download_text','ddownload','copy_button','copy_text','feedback_buttons');
                
                foreach($template_fields as $template_field){
                    if(isset($_POST[$template_field]) && !empty($_POST[$template_field])){

                        if ($template_field == 'prompt') {
                            $value = wp_kses($_POST['prompt'], wp_kses_allowed_html('post'));
                        } else {
                            $value = wpaicg_util_core()->sanitize_text_or_array_field($_POST[$template_field]);
                        }

                        $key = sanitize_text_field($template_field);
                        
                        if($key == 'fields'){
                            $value = json_encode($value,JSON_UNESCAPED_UNICODE );
                        }
                        update_post_meta($wpaicg_prompt_id, 'wpaicg_form_'.$key, $value);
                    }
                    elseif(in_array($template_field,array('bgcolor','header','dans','ddraft','dclear','dnotice','ddownload','copy_button','feedback_buttons')) && (!isset($_POST[$template_field]) || empty($_POST[$template_field]))){
                        delete_post_meta($wpaicg_prompt_id, 'wpaicg_form_'.$template_field);
                    }
                }
                $wpaicg_result['status'] = 'success';
                $wpaicg_result['id'] = $wpaicg_prompt_id;
            }
            wp_send_json($wpaicg_result);
        }

        public function wpaicg_forms()
        {
            include WPAICG_PLUGIN_DIR . 'admin/extra/wpaicg_forms.php';
        }
    }
    WPAICG_Forms::get_instance();
}
