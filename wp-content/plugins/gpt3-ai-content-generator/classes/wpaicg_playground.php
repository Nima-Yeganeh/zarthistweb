<?php

namespace WPAICG;
if ( ! defined( 'ABSPATH' ) ) exit;
if(!class_exists('\\WPAICG\\WPAICG_Playground')) {
    class WPAICG_Playground
    {
        private static  $instance = null ;

        public static function get_instance()
        {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            add_action('init',[$this,'wpaicg_stream'],1);
            add_action( 'wp_ajax_wpaicg_comparison', array( $this, 'wpaicg_comparison' ) );
            add_action('wp_ajax_wpaicg_generate_content_google', array($this, 'wpaicg_generate_content_google'));
            add_action('wp_ajax_save_wpaicg_google_api_key', array($this, 'save_wpaicg_google_api_key'));
            add_action('wp_ajax_save_wpaicg_togetherai_api_key', array($this, 'save_wpaicg_togetherai_api_key'));
            add_action('wp_ajax_wpaicg_generate_content_togetherai', array($this, 'wpaicg_generate_content_togetherai'));
        }

        /**
         * Handles saving the Together AI API key.
         */
        public function save_wpaicg_togetherai_api_key() {
            check_ajax_referer('wpaicg-save-togetherai-api', 'nonce');

            // Check if the current user has the capability to manage options
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Insufficient permissions']);
                return;
            }

            $apiKey = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';

            // Save the API key in WordPress options
            update_option('wpaicg_togetherai_model_api_key', $apiKey);

            wp_send_json_success(['message' => 'API key saved successfully']);
        }

        /**
         * Handles saving the Google API key.
        */
        public function save_wpaicg_google_api_key() {
            check_ajax_referer('wpaicg-save-google-api', 'nonce');

            // Check if the current user has the capability to manage options
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => 'Insufficient permissions']);
                return;
            }

            $apiKey = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';

            // Save the API key in WordPress options
            update_option('wpaicg_google_model_api_key', $apiKey);

            wp_send_json_success(['message' => 'API key saved successfully']);
        }

        public function wpaicg_generate_content_google() {
            check_ajax_referer('wpaicg_generate_content_google', 'nonce');
        
            $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
            $model = isset($_POST['model']) ? sanitize_text_field($_POST['model']) : '';
            
            $response = $this->send_google_request($title, $model);
        
            wp_send_json_success(['content' => $response]);
        }
        
        private function send_google_request($title, $model) {
            $userPrompt = $title;
            $apiKey = get_option('wpaicg_google_model_api_key', '');
            if (empty($apiKey)) {
                return 'Error: Google API key is not set';
            }
        
            // Dynamically construct the URL using the model name
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
        
            $args = array(
                'headers' => array('Content-Type' => 'application/json'),
                'method' => 'POST',
                'timeout' => 300, // Set timeout to 300 seconds
                'body' => json_encode([
                    "contents" => [
                        ["role" => "user", "parts" => [["text" => $userPrompt]]]
                    ],
                    "generationConfig" => [
                        "temperature" => 0.9, "topK" => 1, "topP" => 1, "maxOutputTokens" => 2048, "stopSequences" => []
                    ],
                    "safetySettings" => [
                        ["category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_MEDIUM_AND_ABOVE"],
                    ]
                ])
            );
        
            $response = wp_remote_post($url, $args);
        
            if (is_wp_error($response)) {
                return 'HTTP request error: ' . $response->get_error_message();
            }
        
            $body = wp_remote_retrieve_body($response);
            $decodedResponse = json_decode($body, true);
        
            if (isset($decodedResponse['error'])) {
                $errorMsg = $decodedResponse['error']['message'] ?? 'Unknown error from Google API';
                return 'Error: ' . $errorMsg;
            }
        
            // Check the expected response structure based on the API documentation
            if (isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) {
                return $decodedResponse['candidates'][0]['content']['parts'][0]['text'];
            } else {
                return 'Error: Invalid response from Google API';
            }
        }
        

        public function wpaicg_generate_content_togetherai() {
            check_ajax_referer('wpaicg_generate_content_togetherai', 'nonce');
        
            $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
            $model = isset($_POST['model']) ? sanitize_text_field($_POST['model']) : '';
        
            $response = $this->send_togetherai_request($title, $model);
        
            wp_send_json_success(['content' => $response]);
        }
        
        private function send_togetherai_request($title, $model) {
            $apiKey = get_option('wpaicg_togetherai_model_api_key', '');
            if (empty($apiKey)) {
                return 'Error: Together AI API key is not set';
            }
        
            $url = "https://api.together.xyz/inference";
            $args = array(
                'method' => 'POST',
                'timeout' => 300, // Set timeout to 300 seconds
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiKey
                ),
                'body' => json_encode(array(
                    "model" => $model,
                    "max_tokens" => 2000,
                    "prompt" => $title,
                    "request_type" => "language-model-inference",
                    "temperature" => 0.7,
                    "top_p" => 0.7,
                    "top_k" => 50,
                    "repetition_penalty" => 1,
                    "stream_tokens" => true,
                    "stop" => array("</s>", "[INST]"),
                    "negative_prompt" => "",
                    "sessionKey" => "your_session_key", // Update or generate as needed
                    "repetitive_penalty" => 1,
                    "update_at" => current_time('c')
                ))
            );
        
            $response = wp_remote_post($url, $args);
        
            if (is_wp_error($response)) {
                return 'HTTP request error: ' . $response->get_error_message();
            }
        
            $body = wp_remote_retrieve_body($response);
            // Process the body to extract the data
            $fullText = $this->process_stream_response($body);

            return $fullText;
        }
        
        private function process_stream_response($body) {
            $lines = explode("\n", $body);
            $fullText = '';
        
            foreach ($lines as $line) {
                if (strpos($line, 'data: ') === 0) {
                    $jsonString = substr($line, 6); // Remove 'data: ' prefix
                    $data = json_decode($jsonString, true);
                    if (isset($data['choices'][0]['text'])) {
                        $fullText .= $data['choices'][0]['text'];
                    }
                }
            }
        
            return $fullText;
        }
        

        public function wpaicg_comparison()
        {
            $wpaicg_result = array('status' => 'error');
            if(!current_user_can('manage_options')){
                $wpaicg_result['status'] = 'error';
                $wpaicg_result['msg'] = esc_html__('You do not have permission for this action.','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
            }
            if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'wpaicg_comparison_generator' ) ) {
                $wpaicg_result['msg'] = esc_html__('Nonce verification failed','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
            }
            $wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');
            $open_ai = WPAICG_OpenAI::get_instance()->openai();
            // if provider not openai then assing azure to $open_ai
            if($wpaicg_provider != 'OpenAI'){
                $open_ai = WPAICG_AzureAI::get_instance()->azureai();
            }
            if(!$open_ai){
                $wpaicg_result['msg'] = esc_html__('Missing API Setting','gpt3-ai-content-generator');
                wp_send_json($wpaicg_result);
                exit;
            }
            $wpaicg_generator = WPAICG_Generator::get_instance();
            $wpaicg_generator->openai($open_ai);

            $wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');
            $model = sanitize_text_field($_REQUEST['model']);

            $prompt = sanitize_text_field($_REQUEST['prompt']);
            $temperature = sanitize_text_field($_REQUEST['temperature']);
            $max_tokens = sanitize_text_field($_REQUEST['max_tokens']);
            $top_p = sanitize_text_field($_REQUEST['top_p']);
            $frequency_penalty = sanitize_text_field($_REQUEST['frequency_penalty']);
            $presence_penalty = sanitize_text_field($_REQUEST['presence_penalty']);
            $complete = $wpaicg_generator->wpaicg_request([
                'model' => $model,
                'prompt' => $prompt,
                'temperature' => (float)$temperature,
                'max_tokens' => (float)$max_tokens,
                'frequency_penalty' => (float)$frequency_penalty,
                'presence_penalty' => (float)$presence_penalty,
                'top_p' => (float)$top_p
            ]);
            if($complete['status'] == 'error'){
                $wpaicg_result['msg'] = $complete['msg'];
            }
            else{
                $wpaicg_estimated = 0;
                $wpaicg_result['text'] = $complete['data'];
                $wpaicg_result['text'] = str_replace("\\",'',$wpaicg_result['text']);
                $wpaicg_result['tokens'] = $complete['tokens'];
                $wpaicg_result['words'] = $complete['length'];
                if($model === 'gpt-3.5-turbo' || $model === 'gpt-3.5-turbo-16k') {
                    $wpaicg_estimated = 0.002 * $wpaicg_result['tokens'] / 1000;
                }
                if($model === 'gpt-4') {
                    $wpaicg_estimated = 0.06 * $wpaicg_result['tokens'] / 1000;
                }
                if($model === 'gpt-4o') {
                    $wpaicg_estimated = 0.03 * $wpaicg_result['tokens'] / 1000;
                }
                if($model === 'gpt-4-32k') {
                    $wpaicg_estimated = 0.12 * $wpaicg_result['tokens'] / 1000;
                }
                else{
                    $wpaicg_estimated = 0.02 * $wpaicg_result['tokens'] / 1000;
                }
                $wpaicg_result['cost'] = $wpaicg_estimated;
                $wpaicg_result['status'] = 'success';
            }
            wp_send_json($wpaicg_result);
        }

        public function wpaicg_token_handling($source)
        {
            global $wpdb;
            $result = array();
            $result['message'] = esc_html__('You have reached your token limit.','gpt3-ai-content-generator');
            $result['table'] = 'wpaicg_formtokens';
            $result['limit'] = false;
            $result['tokens'] = 0;
            $result['source'] = $source;
            $result['token_id'] = false;
            $result['limited'] = false;
            $result['old_tokens'] = 0;
            if(!is_user_logged_in()) {
                $wpaicg_client_id = $this->wpaicg_get_cookie_id($source);
            }
            else{
                $wpaicg_client_id = false;
            }
            $result['client_id'] = $wpaicg_client_id;
            if($result['source'] == 'promptbase'){
                $result['table'] = 'wpaicg_prompttokens';
            }
            if($result['source'] == 'image'){
                $result['table'] = 'wpaicg_imagetokens';
            }
            $wpaicg_settings = get_option('wpaicg_limit_tokens_'.$result['source'],[]);
            $result['message'] = isset($wpaicg_settings['limited_message']) && !empty($wpaicg_settings['limited_message']) ? wp_unslash($wpaicg_settings['limited_message']) : $result['message'];
            if(is_user_logged_in() && isset($wpaicg_settings['user_limited']) && $wpaicg_settings['user_limited'] && $wpaicg_settings['user_tokens'] > 0){
                $result['limit'] = true;
                $result['tokens'] = $wpaicg_settings['user_tokens'];
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
                    $result['limit'] = true;
                    $result['tokens'] = $limited_current_role;
                }
                else{
                    $result['limit'] = false;
                }
            }
            /*End check limit base role*/
            if(!is_user_logged_in() && isset($wpaicg_settings['guest_limited']) && $wpaicg_settings['guest_limited'] && $wpaicg_settings['guest_tokens'] > 0){
                $result['limit'] = true;
                $result['tokens'] = $wpaicg_settings['guest_tokens'];
            }
            if($result['limit']){
                if(is_user_logged_in()){
                    $wpaicg_chat_token_log = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$result['table']." WHERE  user_id=%d",get_current_user_id()));
                }
                else{
                    $wpaicg_chat_token_log = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix.$result['table']." WHERE session_id=%s",$wpaicg_client_id));
                }
                $result['old_tokens'] = $wpaicg_chat_token_log ? $wpaicg_chat_token_log->tokens : 0;
                $wpaicg_chat_token_id = $wpaicg_chat_token_log ? $wpaicg_chat_token_log->id : false;
                if(
                    $result['old_tokens'] > 0
                    && $result['tokens'] > 0
                    && $result['old_tokens'] > $result['tokens']
                ){
                    $result['limited'] = true;
                    $result['token_id'] = $wpaicg_chat_token_id;
                    $result['left_tokens'] = 0;
                }
                else{
                    $result['left_tokens'] = $result['tokens'] - $result['old_tokens'];
                    $result['token_id'] = $wpaicg_chat_token_id;
                    $result['limited'] = false;
                }
                /*Check if logged user has limit tokens in balance*/
                if(is_user_logged_in()){
                    if($source == 'form'){
                        $source = 'forms';
                    }
                    $user_meta_key = 'wpaicg_' . $source . '_tokens';
                    $user_tokens = get_user_meta(get_current_user_id(), $user_meta_key, true);
                    $result['left_tokens'] += (float)$user_tokens;
                }
                if($result['limited'] && is_user_logged_in()){
                    if(!empty($user_tokens) && $user_tokens > 0){
                        $result['limited'] = false;
                    }
                }
            }
            return $result;
        }

        public function get_defined_prompt($post_id)
        {
            $form_fields = get_post_meta($post_id, 'wpaicg_form_fields', true);
            $defined_prompt = get_post_meta($post_id, 'wpaicg_form_prompt', true);
        
            if (empty($form_fields) || empty($defined_prompt)) {
                if (file_exists(WPAICG_PLUGIN_DIR . 'admin/data/gptforms.json')) {
                    $forms_data = json_decode(file_get_contents(WPAICG_PLUGIN_DIR . 'admin/data/gptforms.json'), true);
                    foreach ($forms_data as $form) {
                        if ($form['id'] == $post_id) {
                            $form_fields = json_encode($form['fields']);
                            $defined_prompt = $form['prompt'];
                            break;
                        }
                    }
                }
            }
        
            $form_fields = json_decode($form_fields, true);
            $field_values = array();
        
            foreach ($form_fields as $field) {
                $field_id = $field['id'];
                if (isset($_REQUEST[$field_id])) {
                    $field_values[$field_id] = sanitize_text_field($_REQUEST[$field_id]);
                }
            }
        
            foreach ($field_values as $key => $value) {
                $defined_prompt = str_replace('{' . $key . '}', $value, $defined_prompt);
            }
        
            return $defined_prompt;
        }

        public function wpaicg_stream()
        {
            if(isset($_GET['wpaicg_stream']) && sanitize_text_field($_GET['wpaicg_stream']) == 'yes'){
                global $wpdb;
                header('Content-type: text/event-stream');
                header('Cache-Control: no-cache');
                if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'wpaicg-ajax-nonce' ) ) {
                    $wpaicg_error_message = esc_html__('Nonce verification failed', 'gpt3-ai-content-generator');
                    $this->wpaicg_event_message($wpaicg_error_message);
                } else { 
                    $wpaicg_prompt = '';
                    // Playground & Promptbase
                    if ((isset($_REQUEST['source']) && $_REQUEST['source'] == 'playground') || (isset($_REQUEST['source_stream']) && $_REQUEST['source_stream'] == 'promptbase')) {
                        if (isset($_REQUEST['title']) && !empty($_REQUEST['title'])) {
                            $wpaicg_prompt = sanitize_text_field($_REQUEST['title']);
                        }
                    } else {
                        // AI Forms
                        if (isset($_REQUEST['id']) && !empty($_REQUEST['id'])) {
                            $post_id = intval($_REQUEST['id']);
                            $wpaicg_prompt = $this->get_defined_prompt($post_id);
                        }
                    }
                    if ($wpaicg_prompt) {
                        $embeddingsDetails = $this->get_embeddings_details();

                        if ($embeddingsDetails['embeddingsEnabled']) {
                            $contextLabel = !empty($embeddingsDetails['context_suffix']) ? $embeddingsDetails['context_suffix'] : "";
                            $contextData = ""; // Initialize context data variable
                    
                            // Check which vector database is being used and retrieve context data
                            if ($embeddingsDetails['vectordb'] === 'qdrant') {
                                $embedding_result = $this->wpaicg_embeddings_result_qdrant($embeddingsDetails['collections'], $wpaicg_prompt, $embeddingsDetails['embeddings_limit'], $embeddingsDetails['use_default_embedding_model'], $embeddingsDetails['selected_embedding_model'], $embeddingsDetails['selected_embedding_provider']);
                                if (!empty($embedding_result['data'])) {
                                    $contextData = $contextLabel . " " . $embedding_result['data'];
                                }
                            } elseif ($embeddingsDetails['vectordb'] === 'pinecone') {
                                $embedding_result = $this->wpaicg_embeddings_result_pinecone($embeddingsDetails['collections'], $wpaicg_prompt, $embeddingsDetails['embeddings_limit'], $embeddingsDetails['use_default_embedding_model'], $embeddingsDetails['selected_embedding_model'], $embeddingsDetails['selected_embedding_provider']);
                                if (!empty($embedding_result['data'])) {
                                    $contextData = $contextLabel . " " . $embedding_result['data'];
                                }
                            } else {
                                error_log("Embeddings is enabled but no valid vector DB found");
                            }
                    
                            // Append or prepend the context data based on context_suffix_position
                            if ($embeddingsDetails['context_suffix_position'] === 'before') {
                                $wpaicg_prompt = $contextData . " " . $wpaicg_prompt;
                            } else { // Default to 'after' if not specified or any other value
                                $wpaicg_prompt .= " " . $contextData;
                            }
                        }

                        $wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');

                        try {
                            $ai_engine = WPAICG_Util::get_instance()->initialize_ai_engine();
                        } catch (\Exception $e) {
                            $wpaicg_result['msg'] = $e->getMessage();
                            wp_send_json($wpaicg_result);
                        }


                        if ($ai_engine) {
                            $wpaicg_limited_tokens = false;
                            $wpaicg_args = array(
                                'prompt' => $wpaicg_prompt,
                                'temperature' => (float)$ai_engine->temperature,
                                "max_tokens" => (float)$ai_engine->max_tokens,
                                "frequency_penalty" => (float)$ai_engine->frequency_penalty,
                                "presence_penalty" => (float)$ai_engine->presence_penalty,
                                "stream" => true
                            );
                            if (isset($_REQUEST['temperature']) && !empty($_REQUEST['temperature'])) {
                                $wpaicg_args['temperature'] = (float)sanitize_text_field($_REQUEST['temperature']);
                            }
                            $wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');

                            if ($wpaicg_provider == 'OpenAI') {
                                if (isset($_REQUEST['engine']) && !empty($_REQUEST['engine'])) {
                                    $wpaicg_args['model'] = sanitize_text_field($_REQUEST['engine']);
                                } else {
                                    $wpaicg_args['model'] = 'gpt-3.5-turbo-16k';
                                }
                            } elseif ($wpaicg_provider == 'Google') {
                                // Handling for Google AI
                                if (isset($_REQUEST['engine']) && !empty($_REQUEST['engine'])) {
                                    // Use the selected Google model, sanitize the input to ensure it's safe
                                    $wpaicg_args['model'] = sanitize_text_field($_REQUEST['engine']);
                                } else {
                                    // Default Google model if none is selected
                                    $wpaicg_args['model'] = get_option('wpaicg_google_default_model', 'gemini-pro');
                                }
                            }  elseif ($wpaicg_provider == 'OpenRouter') {
                                // Handling for Google AI
                                if (isset($_REQUEST['engine']) && !empty($_REQUEST['engine'])) {
                                    // Use the selected Google model, sanitize the input to ensure it's safe
                                    $wpaicg_args['model'] = sanitize_text_field($_REQUEST['engine']);
                                } else {
                                    // Default model if none is selected
                                    $wpaicg_args['model'] = get_option('wpaicg_openrouter_default_model', 'openrouter/auto');
                                }
                            } else {  // Assume the remaining provider is AzureAI
                                $wpaicg_args['model'] = get_option('wpaicg_azure_deployment', '');
                            }
                            
                            if (isset($_REQUEST['max_tokens']) && !empty($_REQUEST['max_tokens'])) {
                                $wpaicg_args['max_tokens'] = (float)sanitize_text_field($_REQUEST['max_tokens']);
                            }
                            if (isset($_REQUEST['frequency_penalty']) && !empty($_REQUEST['frequency_penalty'])) {
                                $wpaicg_args['frequency_penalty'] = (float)sanitize_text_field($_REQUEST['frequency_penalty']);
                            }
                            if (isset($_REQUEST['presence_penalty']) && !empty($_REQUEST['presence_penalty'])) {
                                $wpaicg_args['presence_penalty'] = (float)sanitize_text_field($_REQUEST['presence_penalty']);
                            }
                            if (isset($_REQUEST['top_p']) && !empty($_REQUEST['top_p'])) {
                                $wpaicg_args['top_p'] = (float)sanitize_text_field($_REQUEST['top_p']);
                            }
                            if (isset($_REQUEST['best_of']) && !empty($_REQUEST['best_of'])) {
                                $wpaicg_args['best_of'] = (float)sanitize_text_field($_REQUEST['best_of']);
                            }
                            if (isset($_REQUEST['stop']) && !empty($_REQUEST['stop'])) {
                                $wpaicg_args['stop'] = explode(',', sanitize_text_field($_REQUEST['stop']));
                            }
                            $has_limited = false;
                            if (isset($_REQUEST['source_stream']) && in_array($_REQUEST['source_stream'], array('promptbase', 'form'))) {
                                $wpaicg_token_handling = $this->wpaicg_token_handling(sanitize_text_field($_REQUEST['source_stream']));
                                if ($wpaicg_token_handling['limited']) {
                                    $has_limited = true;
                                    $this->wpaicg_event_message($wpaicg_token_handling['message']);
                                }
                            }

                            if (!$has_limited) {
                                $legacy_models = array(
                                    'text-davinci-001',
                                    'davinci',
                                    'babbage',
                                    'text-babbage-001',
                                    'curie-instruct-beta',
                                    'text-davinci-003',
                                    'text-curie-001',
                                    'davinci-instruct-beta',
                                    'text-davinci-002',
                                    'ada',
                                    'text-ada-001',
                                    'curie',
                                    'gpt-3.5-turbo-instruct'
                                );
                                if (!in_array($wpaicg_args['model'], $legacy_models)) {
                                    unset($wpaicg_args['best_of']);
                                    $wpaicg_args['messages'] = array(
                                        array('role' => 'user', 'content' => $wpaicg_args['prompt'])
                                    );
                                    unset($wpaicg_args['prompt']);
                                    $wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');
                                    if ($wpaicg_provider == 'Google') {
                                        // add sourceModule=form to the args
                                        $wpaicg_args['sourceModule'] = 'form';
                                        $response = $ai_engine->chat($wpaicg_args);

                                        if (isset($response['error']) && !empty($response['error'])) {
                                            $words = explode(' ', $response['error']);
                                        } else {
                                            $words = explode(' ', $response['data']);
                                        }
                                        foreach ($words as $key => $word) {
                                            echo "event: message\n";
                                            if ($key == 0) {
                                                echo 'data: {"choices":[{"delta":{"content":"' . $word . '"}}]}';
                                            } else {
                                                echo 'data: {"choices":[{"delta":{"content":" ' . $word . '"}}]}';
                                            }
                                            echo "\n\n";
                                            if (ob_get_level() > 0) {
                                                ob_end_flush();
                                            }
                                            flush();
                                        }
                                        echo 'data: [DONE]';
                                        echo "\n\n";
                                        if (ob_get_length()) {
                                            ob_flush();
                                            flush();
                                        }

                                    } else {
                                        try {
                                            $ai_engine->chat($wpaicg_args, function ($curl_info, $data) {
                                                $response = json_decode($data, true);
                                                if (isset($response['error']) && !empty($response['error'])) {
                                                    $message = isset($response['error']['message']) && !empty($response['error']['message']) ? $response['error']['message'] : '';
                                                    if (empty($message) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key') {
                                                        $message = "Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.";
                                                    }
                                                    $words = explode(' ', $message);
                                                    
                                                    foreach ($words as $key => $word) {
                                                        echo "event: message\n";
                                                        if ($key == 0) {
                                                            echo 'data: {"choices":[{"delta":{"content":"' . $word . '"}}]}';
                                                        } else {
                                                            echo 'data: {"choices":[{"delta":{"content":" ' . $word . '"}}]}';
                                                        }
                                                        echo "\n\n";
                                                        ob_end_flush();
                                                        flush();
                                                    }
    
                                                    echo 'data: {"choices":[{"finish_reason":"stop"}]}';
                                                    echo "\n\n";
                                                    ob_end_flush();
                                                    flush();
                                                } else {
                                                    echo $data;

                                                    ob_flush();
                                                    flush();
                                                    return strlen($data);
                                                }
                                                
                                            });
                                        }
                                        catch (\Exception $exception){
                                            $message = $exception->getMessage();
                                            $this->wpaicg_event_message($message);
                                        }
                                    }

                                } else {
                                    try {
                                        $ai_engine->completion($wpaicg_args, function ($curl_info, $data) {
                                            $response = json_decode($data, true);
                                            if (isset($response['error']) && !empty($response['error'])) {
                                                $message = isset($response['error']['message']) && !empty($response['error']['message']) ? $response['error']['message'] : '';
                                                if (empty($message) && isset($response['error']['code']) && $response['error']['code'] == 'invalid_api_key') {
                                                    $message = "Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.";
                                                }
                                                $words = explode(' ', $message);
                                                
                                                foreach ($words as $key => $word) {
                                                    echo "event: message\n";
                                                    if ($key == 0) {
                                                        echo 'data: {"choices":[{"delta":{"content":"' . $word . '"}}]}';
                                                    } else {
                                                        echo 'data: {"choices":[{"delta":{"content":" ' . $word . '"}}]}';
                                                    }
                                                    echo "\n\n";
                                                    ob_end_flush();
                                                    flush();
                                                }
                                    
                                                // Send finish_reason stop after the message
                                                echo 'data: {"choices":[{"finish_reason":"stop"}]}';
                                                echo "\n\n";
                                                ob_flush();
                                                flush();
                                            } else {
                                                echo _wp_specialchars($data, ENT_NOQUOTES, 'UTF-8', true);
                                                ob_flush();
                                                flush();
                                                return strlen($data);
                                            }
                                        });
                                    }
                                    catch (\Exception $exception){
                                        $message = $exception->getMessage();
                                        $this->wpaicg_event_message($message);
                                    }
                                    
                                }
                            }
                        }
                    }
                }
                exit;
            }
        }

        public function get_embeddings_details() {
            // error log request data
            $wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');
            // Check for necessary conditions: ID exists, is not empty, and source is valid
            if (isset($_REQUEST['id'], $_REQUEST['source_stream']) && 
                !empty($_REQUEST['id']) && 
                in_array($_REQUEST['source_stream'], ['promptbase', 'form'])) {
                
                $wpaicg_post_id = sanitize_text_field($_REQUEST['id']);
                $source = $_REQUEST['source_stream'];
                if($source == 'form'){
                    $embeddingsEnabled = get_post_meta($wpaicg_post_id, 'wpaicg_form_embeddings', true) == 'yes';
                    // get use_default_embedding_model value
                    $use_default_embedding_model = get_post_meta($wpaicg_post_id, 'wpaicg_form_use_default_embedding_model', true);
                    // get wpaicg_form_selected_embedding_model
                    $selected_embedding_model = get_post_meta($wpaicg_post_id, 'wpaicg_form_selected_embedding_model', true);
                    // get wpaicg_form_selected_embedding_provider
                    $selected_embedding_provider = get_post_meta($wpaicg_post_id, 'wpaicg_form_selected_embedding_provider', true);
                    // get context suffix
                    $context_suffix = get_post_meta($wpaicg_post_id, 'wpaicg_form_suffix_text', true);
                    // get context suffix position
                    $context_suffix_position = get_post_meta($wpaicg_post_id, 'wpaicg_form_suffix_position', true);
                    // get embeddings_limit  if not exist or empty then use 1 as default
                    $embeddings_limit = get_post_meta($wpaicg_post_id, 'wpaicg_form_embeddings_limit', true);
                    $vectordb = get_post_meta($wpaicg_post_id, 'wpaicg_form_vectordb', true);
                    // Determine which collections or indexes to fetch based on vectordb value
                    if ($vectordb === 'qdrant') {
                        $collectionsOrIndexes = get_post_meta($wpaicg_post_id, 'wpaicg_form_collections', true);
                    } elseif ($vectordb === 'pinecone') {
                        $collectionsOrIndexes = get_post_meta($wpaicg_post_id, 'wpaicg_form_pineconeindexes', true);
                    } else {
                        // Default to an empty string if vectordb is not set or recognized
                        $collectionsOrIndexes = '';
                    }
                } else {
                    $embeddingsEnabled = get_post_meta($wpaicg_post_id, 'wpaicg_prompt_embeddings', true) == 'yes';
                    // get wpaicg_form_selected_embedding_model value
                    $use_default_embedding_model = get_post_meta($wpaicg_post_id, 'wpaicg_prompt_use_default_embedding_model', true);
                    // get wpaicg_form_selected_embedding_model
                    $selected_embedding_model = get_post_meta($wpaicg_post_id, 'wpaicg_prompt_selected_embedding_model', true);
                    // get wpaicg_form_selected_embedding_provider
                    $selected_embedding_provider = get_post_meta($wpaicg_post_id, 'wpaicg_prompt_selected_embedding_provider', true);
                    // get context suffix
                    $context_suffix = get_post_meta($wpaicg_post_id, 'wpaicg_prompt_suffix_text', true);
                    // get context suffix position
                    $context_suffix_position = get_post_meta($wpaicg_post_id, 'wpaicg_prompt_suffix_position', true);
                    // get embeddings_limit  if not exist or empty then use 1 as default
                    $embeddings_limit = get_post_meta($wpaicg_post_id, 'wpaicg_prompt_embeddings_limit', true);
                    $vectordb = get_post_meta($wpaicg_post_id, 'wpaicg_prompt_vectordb', true);
                    // Determine which collections or indexes to fetch based on vectordb value
                    if ($vectordb === 'qdrant') {
                        $collectionsOrIndexes = get_post_meta($wpaicg_post_id, 'wpaicg_prompt_collections', true);
                    } elseif ($vectordb === 'pinecone') {
                        $collectionsOrIndexes = get_post_meta($wpaicg_post_id, 'wpaicg_prompt_pineconeindexes', true);
                    } else {
                        // Default to an empty string if vectordb is not set or recognized
                        $collectionsOrIndexes = '';
                    }
                }

                if(empty($embeddings_limit)){
                    $embeddings_limit = 1;
                }
        
                // Disable embeddings if provider is OpenRouter
                if ($wpaicg_provider === 'OpenRouter') {
                    $embeddingsEnabled = false;
                }

                // If embeddings are enabled, return vectordb and collections or indexes meta values
                if ($embeddingsEnabled) {

                    return [
                        'embeddingsEnabled' => true,
                        'vectordb' => $vectordb,
                        'collections' => $collectionsOrIndexes,
                        'context_suffix' => $context_suffix,
                        'context_suffix_position' => $context_suffix_position,
                        'embeddings_limit' => intval($embeddings_limit),
                        'use_default_embedding_model' => $use_default_embedding_model,
                        'selected_embedding_model' => $selected_embedding_model,
                        'selected_embedding_provider' => $selected_embedding_provider
                    ];
                }
            }
        
            // Return false by default if conditions are not met or embeddings are not enabled
            return ['embeddingsEnabled' => false];
        }
        

        public function wpaicg_embeddings_result_pinecone($wpaicg_pinecone_environment, $wpaicg_message, $limit, $use_default_embedding_model, $selected_embedding_model, $selected_embedding_provider, $namespace = false) {
            $result = ['status' => 'error', 'data' => ''];
            $wpaicg_pinecone_api_key = get_option('wpaicg_pinecone_api', '');
        
            if (empty($wpaicg_pinecone_api_key) || empty($wpaicg_pinecone_environment)) {
                return ['data' => esc_html__('Required Pinecone or API configuration missing.', 'gpt3-ai-content-generator')];
            }
        
            $model = $this->get_embedding_model($use_default_embedding_model, $selected_embedding_model);
            $apiParams = $this->prepare_api_params($wpaicg_message, $model);
        
            $ai_instance = $this->initialize_ai_instance($use_default_embedding_model, $selected_embedding_provider);
            if (!$ai_instance) {
                return ['data' => esc_html__('Unable to initialize the AI instance.', 'gpt3-ai-content-generator')];
            }
        
            $response = $ai_instance->embeddings($apiParams);
            $response = json_decode($response, true);
        
            if (isset($response['error']) && !empty($response['error'])) {
                $errorMessage = $response['error']['message'] ?? 'Incorrect API key provided.';
                return ['data' => $errorMessage];
            }
        
            $embedding = $response['data'][0]['embedding'] ?? null;
            if (empty($embedding)) {
                return ['data' => esc_html__('No embedding data received from the AI provider.', 'gpt3-ai-content-generator')];
            }
        
            $result = $this->search_pinecone($wpaicg_pinecone_environment, $embedding, $wpaicg_pinecone_api_key, $limit, $namespace);
        
            return $result;
        }
        
        private function search_pinecone($wpaicg_pinecone_environment, $embedding, $wpaicg_pinecone_api_key, $limit, $namespace) {
            $headers = [
                'Content-Type' => 'application/json',
                'Api-Key' => $wpaicg_pinecone_api_key
            ];
        
            $pinecone_body = [
                'vector' => $embedding,
                'topK' => $limit
            ];
        
            if ($namespace) {
                $pinecone_body['namespace'] = $namespace;
            }
        
            $response = wp_remote_post("https://$wpaicg_pinecone_environment/query", [
                'headers' => $headers,
                'body' => json_encode($pinecone_body)
            ]);
        
            if (is_wp_error($response)) {
                return ['data' => esc_html($response->get_error_message())];
            }
        
            $bodyContent = wp_remote_retrieve_body($response);
            $body = json_decode($bodyContent, true);
        
            if (isset($body['matches']) && is_array($body['matches']) && count($body['matches'])) {
                $data = '';
                $processedCount = 0; // Counter to keep track of how many matches have been processed
                foreach ($body['matches'] as $match) {
                    if ($processedCount >= $limit) {
                        break; // Break out of the loop if we've processed the desired number of matches
                    }
                    $wpaicg_embedding = get_post($match['id']);
                    if ($wpaicg_embedding) {
                        $data .= empty($data) ? $wpaicg_embedding->post_content : "\n" . $wpaicg_embedding->post_content;
                    }
                    $processedCount++; // Increment the counter
                }
                return ['status' => 'success', 'data' => $data];
            }
        
            return ['status' => 'error', 'data' => esc_html__('No matches found or error in Pinecone response.', 'gpt3-ai-content-generator')];
        }
                

        public function wpaicg_embeddings_result_qdrant($wpaicg_qdrant_collection, $wpaicg_message, $limit,$use_default_embedding_model, $selected_embedding_model, $selected_embedding_provider) {
            $result = ['status' => 'error', 'data' => ''];
            $wpaicg_qdrant_api_key = get_option('wpaicg_qdrant_api_key', '');
            $wpaicg_qdrant_endpoint = get_option('wpaicg_qdrant_endpoint', '');
        
            if (empty($wpaicg_qdrant_api_key) || empty($wpaicg_qdrant_endpoint) || empty($wpaicg_qdrant_collection)) {
                return ['data' => esc_html__('Required Qdrant or API configuration missing.', 'gpt3-ai-content-generator')];
            }
        
            $model = $this->get_embedding_model($use_default_embedding_model, $selected_embedding_model);
            $apiParams = $this->prepare_api_params($wpaicg_message, $model);
        
            $ai_instance = $this->initialize_ai_instance($use_default_embedding_model, $selected_embedding_provider);
            if (!$ai_instance) {
                return ['data' => esc_html__('Unable to initialize the AI instance.', 'gpt3-ai-content-generator')];
            }
        
            $response = $ai_instance->embeddings($apiParams);
            $response = json_decode($response, true);
        
            if (isset($response['error']) && !empty($response['error'])) {
                $errorMessage = $response['error']['message'] ?? 'Incorrect API key provided. You can find your API key at https://platform.openai.com/account/api-keys.';
                return ['data' => $errorMessage];
            }
        
            $embedding = $response['data'][0]['embedding'] ?? null;
            if (empty($embedding)) {
                return ['data' => esc_html__('No embedding data received from the AI provider.', 'gpt3-ai-content-generator')];
            }
        
            $result = $this->search_qdrant($wpaicg_qdrant_endpoint, $wpaicg_qdrant_collection, $embedding, $limit, $wpaicg_qdrant_api_key);
        
            return $result;
        }
        
        private function get_embedding_model($use_default_embedding_model, $selected_embedding_model) {
            if ($use_default_embedding_model === 'no') {
                return $selected_embedding_model;
            }
        
            $main_embedding_model = get_option('wpaicg_main_embedding_model', '');
            if (!empty($main_embedding_model)) {
                list($provider, $model) = explode(':', $main_embedding_model, 2);
                return $model;
            } else {
                $wpaicg_provider = get_option('wpaicg_provider', 'OpenAI');
                // Retrieve the embedding model based on the provider
                switch ($wpaicg_provider) {
                    case 'OpenAI':
                        return get_option('wpaicg_openai_embeddings', 'text-embedding-ada-002');
                    case 'Azure':
                        return get_option('wpaicg_azure_embeddings', 'text-embedding-ada-002');
                    case 'Google':
                        return get_option('wpaicg_google_embeddings', 'embedding-001');
                    default:
                        // It's a good practice to have a default return if no case matches
                        return 'default-embedding-model';
                }
            }
        }
        
        
        private function prepare_api_params($wpaicg_message, $model) {
            $apiParams = ['input' => $wpaicg_message, 'model' => $model];
            return $apiParams;
        }
        
        private function initialize_ai_instance($use_default_embedding_model, $selected_embedding_provider) {
            $provider = 'OpenAI'; // Default provider

            if ($use_default_embedding_model === 'no') {
                $provider = $selected_embedding_provider;
            } else {
                $main_embedding_model = get_option('wpaicg_main_embedding_model', '');
                if (!empty($main_embedding_model)) {
                    list($provider, $model) = explode(':', $main_embedding_model, 2);
                } else {
                    $provider = get_option('wpaicg_provider', 'OpenAI');
                }
            }
        
            switch ($provider) {
                case 'OpenAI':
                    return WPAICG_OpenAI::get_instance()->openai();
                case 'Azure':
                    return WPAICG_AzureAI::get_instance()->azureai();
                case 'Google':
                    return WPAICG_Google::get_instance();
                default:
                    return null; // Return null or handle the default case as needed
            }
        }
        
        private function search_qdrant($endpoint, $collection, $embedding, $limit, $apiKey) {
            $group_id_value = "default";
            $queryData = [
                'vector' => $embedding,
                'limit' => $limit,
                'filter' => [
                    'must' => [['key' => 'group_id', 'match' => ['value' => $group_id_value]]]
                ]
            ];
        
            $response = wp_remote_post("$endpoint/collections/$collection/points/search", [
                'method' => 'POST',
                'headers' => ['api-key' => $apiKey, 'Content-Type' => 'application/json'],
                'body' => json_encode($queryData)
            ]);
        
            if (is_wp_error($response)) {
                return ['data' => esc_html($response->get_error_message())];
            }
        
            $bodyContent = wp_remote_retrieve_body($response);
            $body = json_decode($bodyContent, true);
            if (isset($body['result']) && is_array($body['result'])) {
                $data = array_reduce($body['result'], function ($carry, $match) {
                    $postContent = get_post($match['id'])->post_content ?? '';
                    return $carry . ($carry ? "\n" : '') . $postContent;
                }, '');
        
                return ['status' => 'success', 'data' => $data];
            }
        
            return ['data' => esc_html__('No matches found or error in Qdrant response.', 'gpt3-ai-content-generator')];
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
                if (ob_get_level() > 0) {
                    ob_end_flush();
                }
                flush();
            }
        }

        public function wpaicg_get_cookie_id($source_stream)
        {
            if(!function_exists('PasswordHash')){
                require_once ABSPATH . 'wp-includes/class-phpass.php';
            }
            if(isset($_COOKIE['wpaicg_'.$source_stream.'_client_id']) && !empty($_COOKIE['wpaicg_'.$source_stream.'_client_id'])){
                return $_COOKIE['wpaicg_'.$source_stream.'_client_id'];
            }
            else{
                $hasher      = new \PasswordHash( 8, false );
                $cookie_id = 't_' . substr( md5( $hasher->get_random_bytes( 32 ) ), 2 );
                setcookie('wpaicg_'.$source_stream.'_client_id', $cookie_id, time() + 604800, COOKIEPATH, COOKIE_DOMAIN);
                return $cookie_id;
            }
        }
    }
    WPAICG_Playground::get_instance();
}
