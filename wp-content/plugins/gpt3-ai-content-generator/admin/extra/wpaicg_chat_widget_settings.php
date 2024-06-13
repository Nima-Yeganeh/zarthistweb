<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb;
$errors = false;
$message = false;
if ( isset( $_POST['wpaicg_submit'] ) ) {
    check_admin_referer('wpaicg_chat_widget_save');

    $stream_nav_option = isset($_POST['wpaicg_stream_nav_option']) ? '1' : '0';
    update_option('wpaicg_widget_stream', $stream_nav_option);

    $wpaicg_chat_vectordb = isset($_POST['wpaicg_chat_vectordb']) ? sanitize_text_field($_POST['wpaicg_chat_vectordb']) : 'pinecone';
    update_option('wpaicg_chat_vectordb', $wpaicg_chat_vectordb);

    if (isset($_POST['wpaicg_widget_qdrant_collection'])) {
        $selectedCollection = sanitize_text_field($_POST['wpaicg_widget_qdrant_collection']);
        update_option('wpaicg_widget_qdrant_collection', $selectedCollection);
    }

    if (isset($_POST['wpaicg_conversation_starters_widget'])) {
        $starters = $_POST['wpaicg_conversation_starters_widget'];
        $sanitized_starters = array();
    
        foreach ($starters as $index => $starter) {
            if (!empty($starter)) { // Only save non-empty starters
                $sanitized_starters[] = array(
                    'index' => $index,
                    'text' => sanitize_text_field($starter)
                );
            }
        }
    
        // Save the structured array as a JSON string
        update_option('wpaicg_conversation_starters_widget', json_encode($sanitized_starters));
    }
    

    if ( isset($_POST['wpaicg_chat_temperature']) && (!is_numeric( $_POST['wpaicg_chat_temperature'] ) || floatval( $_POST['wpaicg_chat_temperature'] ) < 0 || floatval( $_POST['wpaicg_chat_temperature'] ) > 1 )) {
        $errors = sprintf(
            /* translators: 1: minimum temperature, 2: maximum temperature */
            esc_html__('Please enter a valid temperature value between %1$d and %2$d.','gpt3-ai-content-generator'),0,1);
    }
    if (isset($_POST['wpaicg_chat_max_tokens']) && ( !is_numeric( $_POST['wpaicg_chat_max_tokens'] ) || floatval( $_POST['wpaicg_chat_max_tokens'] ) < 64 || floatval( $_POST['wpaicg_chat_max_tokens'] ) > 8000 )) {
        $errors = sprintf(
            /* translators: 1: minimum max_tokens, 2: maximum max_tokens */
            esc_html__('Please enter a valid max token value between %1$d and %2$d.','gpt3-ai-content-generator'),64,8000);
    }
    if (isset($_POST['wpaicg_chat_top_p']) && (!is_numeric( $_POST['wpaicg_chat_top_p'] ) || floatval( $_POST['wpaicg_chat_top_p'] ) < 0 || floatval( $_POST['wpaicg_chat_top_p'] ) > 1 )){
        $errors = sprintf(
            /* translators: 1: minimum top_p, 2: maximum top_p */
            esc_html__('For the widget, please enter a valid top p value between %1$d and %2$d.','gpt3-ai-content-generator'),0,1);
    }
    if (isset($_POST['wpaicg_chat_best_of']) && ( !is_numeric( $_POST['wpaicg_chat_best_of'] ) || floatval( $_POST['wpaicg_chat_best_of'] ) < 1 || floatval( $_POST['wpaicg_chat_best_of'] ) > 20 )) {
        $errors = sprintf(
            /* translators: 1: minimum best of value, 2: maximum best of value */
            esc_html__('Please enter a valid best of value between %1$d and %2$d.','gpt3-ai-content-generator'),1,20);
    }
    if (isset($_POST['wpaicg_chat_frequency_penalty']) && ( !is_numeric( $_POST['wpaicg_chat_frequency_penalty'] ) || floatval( $_POST['wpaicg_chat_frequency_penalty'] ) < 0 || floatval( $_POST['wpaicg_chat_frequency_penalty'] ) > 2 )) {
        $errors = sprintf(
            /* translators: 1: minimum frequency_penalty, 2: maximum frequency_penalty */
            esc_html__('For the widget, please enter a valid frequency penalty value between %1$d and %2$d.','gpt3-ai-content-generator'),0,2);
    }
    if (isset($_POST['wpaicg_chat_presence_penalty']) && ( !is_numeric( $_POST['wpaicg_chat_presence_penalty'] ) || floatval( $_POST['wpaicg_chat_presence_penalty'] ) < 0 || floatval( $_POST['wpaicg_chat_presence_penalty'] ) > 2 ) ){
        $errors = sprintf(
            /* translators: 1: minimum presence_penalty, 2: maximum presence_penalty */
            esc_html__('For the widget, please enter a valid presence penalty value between %1$d and %2$d.','gpt3-ai-content-generator'),0,2);
    }
    if(!$errors){
        $wpaicg_keys = array(
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
            'wpaicg_chat_embedding_top'
        );
        foreach($wpaicg_keys as $wpaicg_key){
            if(isset($_POST[$wpaicg_key]) && !empty($_POST[$wpaicg_key])){
                // Strip slashes from the POST data
                $posted_value = stripslashes_deep($_POST[$wpaicg_key]);
                        // Check if the current key is 'wpaicg_chat_widget' and handle it separately
                if ($wpaicg_key === 'wpaicg_chat_widget' && is_array($posted_value)) {

                    // 'use_default_embedding' checkbox logic
                    $posted_value['use_default_embedding'] = isset($_POST['wpaicg_chat_widget']['use_default_embedding']) && $_POST['wpaicg_chat_widget']['use_default_embedding'] === 'true';

                    // Handle embedding settings based on the 'use_default_embedding' state
                    if (!$posted_value['use_default_embedding']) {
                        $posted_value['embedding_model'] = isset($_POST['wpaicg_chat_widget']['embedding_model']) ? sanitize_text_field($_POST['wpaicg_chat_widget']['embedding_model']) : '';
                        $posted_value['embedding_provider'] = isset($_POST['wpaicg_chat_widget']['embedding_provider']) ? sanitize_text_field($_POST['wpaicg_chat_widget']['embedding_provider']) : '';
                    } else {
                        // If using default, ensure these keys are not set
                        unset($posted_value['embedding_model']);
                        unset($posted_value['embedding_provider']);
                    }
                    // Extract the 'footer_text' field and handle it separately if needed
                    $footer_text = isset($posted_value['footer_text']) ? wp_kses_post($posted_value['footer_text']) : '';

                    // Remove the 'footer_text' field from the array temporarily to sanitize the rest
                    unset($posted_value['footer_text']);

                    // Sanitize the rest of the array
                    $sanitized_value = \WPAICG\wpaicg_util_core()->sanitize_text_or_array_field($posted_value);

                    // Add 'footer_text' back into the array or handle it as necessary
                    $sanitized_value['footer_text'] = $footer_text;

                    // Update the option with the sanitized array including 'footer_text'
                    update_option($wpaicg_key, $sanitized_value);
                } else {
                    // For all other keys, sanitize normally
                    update_option($wpaicg_key, \WPAICG\wpaicg_util_core()->sanitize_text_or_array_field($posted_value));
                }
            }
            else{
                delete_option($wpaicg_key);
            }
        }
        // Save AI provider and model settings
        if (isset($_POST['wpaicg_chat_widget']['provider'])) {
            $provider = sanitize_text_field($_POST['wpaicg_chat_widget']['provider']);
            $_POST['wpaicg_chat_widget']['provider'] = $provider;

            // Save the model for each provider
            if ($provider === 'OpenAI' && isset($_POST['wpaicg_chat_model'])) {
                $openai_model = sanitize_text_field($_POST['wpaicg_chat_model']);
                update_option('wpaicg_chat_model', $openai_model);
            } elseif ($provider === 'Google' && isset($_POST['wpaicg_widget_google_model'])) {
                $google_model = sanitize_text_field($_POST['wpaicg_widget_google_model']);
                update_option('wpaicg_widget_google_model', $google_model);
            } elseif ($provider === 'Azure' && isset($_POST['wpaicg_azure_deployment'])) {
                $azure_deployment = sanitize_text_field($_POST['wpaicg_azure_deployment']);
                update_option('wpaicg_azure_deployment', $azure_deployment);
            } elseif ($provider === 'OpenRouter' && isset($_POST['wpaicg_widget_openrouter_model'])) {
                $openrouter_model = sanitize_text_field($_POST['wpaicg_widget_openrouter_model']);
                update_option('wpaicg_widget_openrouter_model', $openrouter_model);
            }
        }

        $message = esc_html__('Setting saved successfully','gpt3-ai-content-generator');
    }
}

$wpaicg_custom_models = get_option('wpaicg_custom_models',array());
$table = $wpdb->prefix . 'wpaicg';
$existingValue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE name = %s", 'wpaicg_settings' ), ARRAY_A );
$wpaicg_chat_temperature = get_option('wpaicg_chat_temperature',$existingValue['temperature']);
$wpaicg_chat_max_tokens = get_option('wpaicg_chat_max_tokens',$existingValue['max_tokens']);
$wpaicg_chat_top_p = get_option('wpaicg_chat_top_p',$existingValue['top_p']);
$wpaicg_chat_best_of = get_option('wpaicg_chat_best_of',$existingValue['best_of']);
$wpaicg_chat_frequency_penalty = get_option('wpaicg_chat_frequency_penalty',$existingValue['frequency_penalty']);
$wpaicg_chat_presence_penalty = get_option('wpaicg_chat_presence_penalty',$existingValue['presence_penalty']);
$wpaicg_chat_widget = get_option('wpaicg_chat_widget',[]);

if (!isset($wpaicg_chat_widget['model'])) {
    $wpaicg_chat_widget['model'] = ''; // Initialize the model key if not already set
}

$wpaicg_provider = isset($wpaicg_chat_widget['provider']) && !empty($wpaicg_chat_widget['provider'])
    ? $wpaicg_chat_widget['provider']
    : get_option('wpaicg_provider', 'OpenAI');

if ($wpaicg_provider === 'OpenAI') {
    $wpaicg_chat_model = get_option('wpaicg_chat_model', '');
} elseif ($wpaicg_provider === 'Google') {
    $wpaicg_chat_model = get_option('wpaicg_widget_google_model', '');
} elseif ($wpaicg_provider === 'Azure') {
    $wpaicg_chat_model = get_option('wpaicg_azure_deployment', '');
} elseif ($wpaicg_provider === 'OpenRouter') {
    $wpaicg_chat_model = get_option('wpaicg_widget_openrouter_model', 'openrouter/auto');
}
$wpaicg_chat_widget['model'] = $wpaicg_chat_model;

$wpaicg_chat_icon = isset($wpaicg_chat_widget['icon']) && !empty($wpaicg_chat_widget['icon']) ? $wpaicg_chat_widget['icon'] : 'default';
$wpaicg_chat_status = isset($wpaicg_chat_widget['status']) && !empty($wpaicg_chat_widget['status']) ? $wpaicg_chat_widget['status'] : '';
$wpaicg_chat_fontsize = isset($wpaicg_chat_widget['fontsize']) && !empty($wpaicg_chat_widget['fontsize']) ? $wpaicg_chat_widget['fontsize'] : '13';
$wpaicg_chat_fontcolor = isset($wpaicg_chat_widget['fontcolor']) && !empty($wpaicg_chat_widget['fontcolor']) ? $wpaicg_chat_widget['fontcolor'] : '#495057';
$wpaicg_chat_bgcolor = isset($wpaicg_chat_widget['bgcolor']) && !empty($wpaicg_chat_widget['bgcolor']) ? $wpaicg_chat_widget['bgcolor'] : '#f8f9fa';
$wpaicg_bg_text_field = isset($wpaicg_chat_widget['bg_text_field']) && !empty($wpaicg_chat_widget['bg_text_field']) ? $wpaicg_chat_widget['bg_text_field'] : '#ffffff';
$wpaicg_send_color = isset($wpaicg_chat_widget['send_color']) && !empty($wpaicg_chat_widget['send_color']) ? $wpaicg_chat_widget['send_color'] : '#d1e8ff';
$wpaicg_footer_color = isset($wpaicg_chat_widget['footer_color']) && !empty($wpaicg_chat_widget['footer_color']) ? $wpaicg_chat_widget['footer_color'] : '#ffffff';
$wpaicg_footer_font_color = isset($wpaicg_chat_widget['footer_font_color']) && !empty($wpaicg_chat_widget['footer_font_color']) ? $wpaicg_chat_widget['footer_font_color'] : '#495057';
$wpaicg_input_font_color = isset($wpaicg_chat_widget['input_font_color']) && !empty($wpaicg_chat_widget['input_font_color']) ? $wpaicg_chat_widget['input_font_color'] : '#495057';
$wpaicg_border_text_field = isset($wpaicg_chat_widget['border_text_field']) && !empty($wpaicg_chat_widget['border_text_field']) ? $wpaicg_chat_widget['border_text_field'] : '#cccccc';
$wpaicg_footer_text = isset($wpaicg_chat_widget['footer_text']) && !empty($wpaicg_chat_widget['footer_text']) ? $wpaicg_chat_widget['footer_text'] : '';
$wpaicg_user_bg_color = isset($wpaicg_chat_widget['user_bg_color']) && !empty($wpaicg_chat_widget['user_bg_color']) ? $wpaicg_chat_widget['user_bg_color'] : '#ccf5e1';
$wpaicg_ai_bg_color = isset($wpaicg_chat_widget['ai_bg_color']) && !empty($wpaicg_chat_widget['ai_bg_color']) ? $wpaicg_chat_widget['ai_bg_color'] : '#d1e8ff';
$wpaicg_bar_color = isset($wpaicg_chat_widget['bar_color']) && !empty($wpaicg_chat_widget['bar_color']) ? $wpaicg_chat_widget['bar_color'] : '#495057';
$wpaicg_use_avatar = isset($wpaicg_chat_widget['use_avatar']) && !empty($wpaicg_chat_widget['use_avatar']) ? $wpaicg_chat_widget['use_avatar'] : false;
$wpaicg_ai_avatar = isset($wpaicg_chat_widget['ai_avatar']) && !empty($wpaicg_chat_widget['ai_avatar']) ? $wpaicg_chat_widget['ai_avatar'] : 'default';
$wpaicg_ai_avatar_id = isset($wpaicg_chat_widget['ai_avatar_id']) && !empty($wpaicg_chat_widget['ai_avatar_id']) ? $wpaicg_chat_widget['ai_avatar_id'] : '';
$wpaicg_chat_width = isset($wpaicg_chat_widget['width']) && !empty($wpaicg_chat_widget['width']) ? $wpaicg_chat_widget['width'] : '40%';
$wpaicg_chat_height = isset($wpaicg_chat_widget['height']) && !empty($wpaicg_chat_widget['height']) ? $wpaicg_chat_widget['height'] : '50%';
$wpaicg_chat_position = isset($wpaicg_chat_widget['position']) && !empty($wpaicg_chat_widget['position']) ? $wpaicg_chat_widget['position'] : 'left';
$wpaicg_chat_tone = isset($wpaicg_chat_widget['tone']) && !empty($wpaicg_chat_widget['tone']) ? $wpaicg_chat_widget['tone'] : 'friendly';
$wpaicg_user_aware = isset($wpaicg_chat_widget['user_aware']) && !empty($wpaicg_chat_widget['user_aware']) ? $wpaicg_chat_widget['user_aware'] : 'no';
$wpaicg_chat_proffesion = isset($wpaicg_chat_widget['proffesion']) && !empty($wpaicg_chat_widget['proffesion']) ? $wpaicg_chat_widget['proffesion'] : 'none';
$wpaicg_chat_remember_conversation = isset($wpaicg_chat_widget['remember_conversation']) && !empty($wpaicg_chat_widget['remember_conversation']) ? $wpaicg_chat_widget['remember_conversation'] : 'yes';
$wpaicg_chat_content_aware = isset($wpaicg_chat_widget['content_aware']) && !empty($wpaicg_chat_widget['content_aware']) ? $wpaicg_chat_widget['content_aware'] : 'yes';
$wpaicg_pinecone_api = get_option('wpaicg_pinecone_api','');
$wpaicg_pinecone_environment = get_option('wpaicg_pinecone_environment','');
$wpaicg_save_logs = isset($wpaicg_chat_widget['save_logs']) && !empty($wpaicg_chat_widget['save_logs']) ? $wpaicg_chat_widget['save_logs'] : false;
$wpaicg_log_notice = isset($wpaicg_chat_widget['log_notice']) && !empty($wpaicg_chat_widget['log_notice']) ? $wpaicg_chat_widget['log_notice'] : false;
$wpaicg_log_notice_message = isset($wpaicg_chat_widget['log_notice_message']) && !empty($wpaicg_chat_widget['log_notice_message']) ? $wpaicg_chat_widget['log_notice_message'] : esc_html__('Please note that your conversations will be recorded.','gpt3-ai-content-generator');
$wpaicg_conversation_cut = get_option('wpaicg_conversation_cut',10);
$wpaicg_chat_embedding = get_option('wpaicg_chat_embedding',false);
$wpaicg_chat_addition = get_option('wpaicg_chat_addition',false);
$wpaicg_chat_addition_text = get_option('wpaicg_chat_addition_text',false);
$wpaicg_chat_addition_text = str_replace("\\",'',$wpaicg_chat_addition_text);
$wpaicg_chat_embedding_type = get_option('wpaicg_chat_embedding_type',false);
$wpaicg_chat_embedding_top = get_option('wpaicg_chat_embedding_top',false);
$wpaicg_audio_enable = isset($wpaicg_chat_widget['audio_enable']) ? $wpaicg_chat_widget['audio_enable'] : false;
$wpaicg_image_enable = isset($wpaicg_chat_widget['image_enable']) ? $wpaicg_chat_widget['image_enable'] : false;
$wpaicg_mic_color = isset($wpaicg_chat_widget['mic_color']) ? $wpaicg_chat_widget['mic_color'] : '#d1e8ff';
$wpaicg_stop_color = isset($wpaicg_chat_widget['stop_color']) ? $wpaicg_chat_widget['stop_color'] : '#d1e8ff';
$wpaicg_user_limited = isset($wpaicg_chat_widget['user_limited']) ? $wpaicg_chat_widget['user_limited'] : false;
$wpaicg_guest_limited = isset($wpaicg_chat_widget['guest_limited']) ? $wpaicg_chat_widget['guest_limited'] : false;
$wpaicg_user_tokens = isset($wpaicg_chat_widget['user_tokens']) ? $wpaicg_chat_widget['user_tokens'] : 0;
$wpaicg_guest_tokens = isset($wpaicg_chat_widget['guest_tokens']) ? $wpaicg_chat_widget['guest_tokens'] : 0;
$wpaicg_reset_limit = isset($wpaicg_chat_widget['reset_limit']) ? $wpaicg_chat_widget['reset_limit'] : 0;
$wpaicg_limited_message = isset($wpaicg_chat_widget['limited_message']) && !empty($wpaicg_chat_widget['limited_message']) ? $wpaicg_chat_widget['limited_message'] : esc_html__('You have reached your token limit.','gpt3-ai-content-generator');
$wpaicg_include_footer = (isset($wpaicg_chat_widget['footer_text']) && !empty($wpaicg_chat_widget['footer_text'])) ? 5 : 0;
$wpaicg_chat_widget['role_limited'] = isset($wpaicg_chat_widget['role_limited']) && !empty($wpaicg_chat_widget['role_limited']) ? $wpaicg_chat_widget['role_limited'] : false;
$wpaicg_chat_widget['limited_roles'] = isset($wpaicg_chat_widget['limited_roles']) && !empty($wpaicg_chat_widget['limited_roles']) ? $wpaicg_chat_widget['limited_roles'] : array();
$wpaicg_chat_fullscreen = isset($wpaicg_chat_widget['fullscreen']) && !empty($wpaicg_chat_widget['fullscreen']) ? $wpaicg_chat_widget['fullscreen'] : false;
$wpaicg_chat_close_btn = isset($wpaicg_chat_widget['close_btn']) && !empty($wpaicg_chat_widget['close_btn']) ? $wpaicg_chat_widget['close_btn'] : false;
$wpaicg_chat_download_btn = isset($wpaicg_chat_widget['download_btn']) && !empty($wpaicg_chat_widget['download_btn']) ? $wpaicg_chat_widget['download_btn'] : false;
$wpaicg_chat_clear_btn = isset($wpaicg_chat_widget['clear_btn']) && !empty($wpaicg_chat_widget['clear_btn']) ? $wpaicg_chat_widget['clear_btn'] : false;
$wpaicg_thinking_color = isset($wpaicg_chat_widget['thinking_color']) && !empty($wpaicg_chat_widget['thinking_color']) ? $wpaicg_chat_widget['thinking_color'] : '#495057';
$wpaicg_delay_time = isset($wpaicg_chat_widget['delay_time']) && !empty($wpaicg_chat_widget['delay_time']) ? $wpaicg_chat_widget['delay_time'] : '';
$wpaicg_chat_to_speech = isset($wpaicg_chat_widget['chat_to_speech']) ? $wpaicg_chat_widget['chat_to_speech'] : false;
$wpaicg_elevenlabs_voice = isset($wpaicg_chat_widget['elevenlabs_voice']) ? $wpaicg_chat_widget['elevenlabs_voice'] : '';
$wpaicg_elevenlabs_model = isset($wpaicg_chat_widget['elevenlabs_model']) ? $wpaicg_chat_widget['elevenlabs_model'] : '';

$wpaicg_openai_model = isset($wpaicg_chat_widget['openai_model']) && !empty($wpaicg_chat_widget['openai_model']) ? $wpaicg_chat_widget['openai_model'] : 'tts-1';
$wpaicg_openai_voice = isset($wpaicg_chat_widget['openai_voice']) && !empty($wpaicg_chat_widget['openai_voice']) ? $wpaicg_chat_widget['openai_voice'] : 'alloy';
$wpaicg_openai_output_format = isset($wpaicg_chat_widget['openai_output_format']) && !empty($wpaicg_chat_widget['openai_output_format']) ? $wpaicg_chat_widget['openai_output_format'] : 'mp3';
$wpaicg_openai_voice_speed = isset($wpaicg_chat_widget['openai_voice_speed']) && !empty($wpaicg_chat_widget['openai_voice_speed']) ? $wpaicg_chat_widget['openai_voice_speed'] : '1.0';

$wpaicg_text_height = isset($wpaicg_chat_widget['text_height']) && !empty($wpaicg_chat_widget['text_height']) ? $wpaicg_chat_widget['text_height'] : 40;
$wpaicg_text_rounded = isset($wpaicg_chat_widget['text_rounded']) && !empty($wpaicg_chat_widget['text_height']) ? $wpaicg_chat_widget['text_rounded'] : 8;
$wpaicg_chat_rounded = isset($wpaicg_chat_widget['chat_rounded']) && !empty($wpaicg_chat_widget['text_height']) ? $wpaicg_chat_widget['chat_rounded'] : 8;
$wpaicg_elevenlabs_api = get_option('wpaicg_elevenlabs_api', '');
$wpaicg_chat_voice_service = isset($wpaicg_chat_widget['voice_service']) ? $wpaicg_chat_widget['voice_service'] : '';
$wpaicg_google_voices = get_option('wpaicg_google_voices',[]);
$wpaicg_roles = wp_roles()->get_names();
$wpaicg_google_api_key = get_option('wpaicg_google_api_key', '');
$wpaicg_pinecone_indexes = get_option('wpaicg_pinecone_indexes','');
$wpaicg_stream_nav_setting = get_option('wpaicg_widget_stream', true);
$wpaicg_pinecone_indexes = empty($wpaicg_pinecone_indexes) ? array() : json_decode($wpaicg_pinecone_indexes,true);
$wpaicg_chat_vectordb = get_option('wpaicg_chat_vectordb','pinecone');
$wpaicg_qdrant_collections = get_option('wpaicg_qdrant_collections',[]);
$wpaicg_qdrant_collections = empty($wpaicg_qdrant_collections) ? array() : $wpaicg_qdrant_collections;
// Retrieve the currently selected Qdrant collection
$wpaicg_widget_qdrant_collection = get_option('wpaicg_widget_qdrant_collection', '');
$wpaicg_conversation_starters_widget = get_option('wpaicg_conversation_starters_widget', '');
$wpaicg_conversation_starters_widget = empty($wpaicg_conversation_starters_widget) ? array() : json_decode($wpaicg_conversation_starters_widget, true);

// Retrieve embedding model and provider settings
$wpaicg_use_default_embedding = isset($wpaicg_chat_widget['use_default_embedding']) ? $wpaicg_chat_widget['use_default_embedding'] : true; // Default to true if not set
$wpaicg_embedding_model = isset($wpaicg_chat_widget['embedding_model']) ? $wpaicg_chat_widget['embedding_model'] : '';
$wpaicg_embedding_provider = isset($wpaicg_chat_widget['embedding_provider']) ? $wpaicg_chat_widget['embedding_provider'] : '';
?>
<style>
    .wpaicg_chat_widget_content {
        /* Initial state of the chat window - hidden */
        opacity: 0;
        transform: scale(0.9);
        visibility: hidden;
        transition: opacity 0.3s ease, transform 0.3s ease, visibility 0s linear 0.3s;
    }

    .wpaicg_widget_open .wpaicg_chat_widget_content {
        /* Visible state of the chat window */
        opacity: 1;
        transform: scale(1);
        visibility: visible;
        transition-delay: 0s;
    }
    /* Updated shining light effect for hover without background */
    @keyframes shine {
        0% {
            background-position: -150px;
        }
        50% {
            background-position: 150px;
        }
        100% {
            background-position: -150px;
        }
    }

    .wpaicg_chat_widget .wpaicg_toggle {
        position: relative;
        overflow: hidden;
        transition: box-shadow 0.3s ease;
    }

    .wpaicg_chat_widget .wpaicg_toggle::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        /* Ensure gradient is completely transparent except for the shine */
        background: linear-gradient(to right, transparent, rgba(255,255,255,0.8) 50%, transparent) no-repeat;
        transform: rotate(30deg);
        /* Start with the shine outside of the visible area */
        background-position: -150px;
    }

    .wpaicg_chat_widget .wpaicg_toggle:hover::before {
        /* Apply the animation only on hover */
        animation: shine 2s infinite;
    }

    .wpaicg_chat_widget .wpaicg_toggle img {
        display: block;
        transition: opacity 0.3s ease;
    }
</style>

<style>
    .asdisabled{
        background: #ebebeb!important;
    }
    .wpaicg_chatbox_avatar{
        cursor: pointer;
    }
    .wp-picker-holder{
        position: absolute;
    }
    .wpaicg_chatbox_icon{
        cursor: pointer;
    }
    .wpaicg_chatbox_icon svg{
    }
    .wpaicg-collapse-content textarea{
        display: inline-block!important;
        width: 48%!important;
    }
    .wpaicg_widget_open .wpaicg_chat_widget_content .wpaicg-chatbox{
        top: 0;
    }
    .wpaicg_chat_widget .wpaicg_toggle{
        cursor: pointer;
    }
    .wpaicg_chat_widget .wpaicg_toggle img{
        width: 75px;
        height: 75px;
    }
    .wpaicg-chatbox-preview{
        position: relative;
    }

    .wpaicg_toggle{}
    .wpaicg_chat_widget{
        position: absolute;
        bottom: 0;
    }
    .wpaicg_widget_open .wpaicg_chat_widget_content{
        height: <?php echo esc_html($wpaicg_chat_height)?>px;
    }

</style>
<style>
    .wpaicg_chat_widget_content .wpaicg-chatbox{
        height: 100%;
        background-color: <?php echo esc_html($wpaicg_chat_bgcolor)?>;
        border-radius: 5px;
        border: none;
    }

    .wpaicg_chat_widget_content{
        position: absolute;
        bottom: calc(100% + 15px);
        width: <?php echo esc_html($wpaicg_chat_width)?>px;

    }
    .wpaicg_chat_widget_content .wpaicg-chatbox{
        position: absolute;
        top: 100%;
        left: 0;
        width: <?php echo esc_html($wpaicg_chat_width)?>px;
        height: <?php echo esc_html($wpaicg_chat_height)?>px;
        transition: top 300ms cubic-bezier(0.17, 0.04, 0.03, 0.94);
    }

    .wpaicg_chat_widget_content .wpaicg-bot-thinking{
        color: <?php echo esc_html($wpaicg_chat_fontcolor)?>;
    }
    .wpaicg_chat_widget_content .wpaicg-chatbox-type{
        border-top: 0;
    }
    .wpaicg_chat_widget_content .wpaicg-chat-message{
        color: <?php echo esc_html($wpaicg_chat_fontcolor)?>;
    }

    .wpaicg_chat_widget_content .wpaicg-chatbox-send{
        color: <?php echo esc_html($wpaicg_send_color)?>;
    }
    .wpaicg-chatbox-footer{
        color: <?php echo esc_html($wpaicg_chat_fontcolor)?>;
        background: <?php echo esc_html($wpaicg_footer_color)?>;
        font-size: 0.75rem;
        padding: 12px 20px;
        border-top: 1px solid <?php echo esc_html($wpaicg_footer_color)?>;
    }
    /* inherit for hyperlink */
    .wpaicg-chatbox-footer a{
        color: inherit;
    }

    .wpaicg-chat-shortcode-type,.wpaicg-chatbox-type{
        position: relative;
    }
    .wpaicg-mic-icon{
        cursor: pointer;
    }
    .wp-picker-input-wrap input[type=text]{
        width: 4rem!important;
    }
    .wpaicg-mic-icon svg{
        width: 16px;
        height: 16px;
        fill: currentColor;
    }
    .wpaicg_chat_widget_content{
        overflow: hidden;
    }
    .wpaicg_widget_open .wpaicg_chat_widget_content{
        overflow: visible;
    }
    .wpaicg-notice {
    color: #d9534f;
    padding: 5px;
    border: 1px solid #d9534f;
    border-radius: 3px;
}
</style>
<style>
@keyframes rotation {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

.rotating {
    animation: rotation 2s infinite linear;
}
</style>
<div id="exportMessage" style="display: none;" class="notice notice-success"></div>
<?php

$wpaicg_chat_language = get_option('wpaicg_chat_language','');
if ( !empty($errors)) {
    echo  "<h4 id='setting_message' style='color: red;'>" . esc_html( $errors ) . "</h4>" ;
} elseif(!empty($message)) {
    echo  "<h4 id='setting_message' style='color: green;'>" . esc_html( $message ) . "</h4>" ;
}
?>
<?php

$language_options = \WPAICG\WPAICG_Util::get_instance()->chat_language_options;
$tone_options = \WPAICG\WPAICG_Util::get_instance()->chat_tone_options;
$profession_options = \WPAICG\WPAICG_Util::get_instance()->chat_profession_options;

?>
<div class="demo-page">
  <div class="demo-page-navigation">
    <nav>
      <ul>
        <li>
          <a href="javascript:void(0);" data-tab="aisettings">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-tool">
              <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
            </svg>
            Settings</a>
        </li>
        <li>
        <a href="javascript:void(0);" data-tab="knowledge">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-align-justify">
              <line x1="21" y1="10" x2="3" y2="10" />
              <line x1="21" y1="6" x2="3" y2="6" />
              <line x1="21" y1="14" x2="3" y2="14" />
              <line x1="21" y1="18" x2="3" y2="18" />
            </svg>
            Knowledge</a>
        </li>
        <li>
        <a href="javascript:void(0);" data-tab="style">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-feather">
              <path d="M20.24 12.24a6 6 0 0 0-8.49-8.49L5 10.5V19h8.5z" />
              <line x1="16" y1="8" x2="2" y2="22" />
              <line x1="17.5" y1="15" x2="9" y2="15" />
            </svg>
            Style</a>
        </li>
        <li>
          <a href="javascript:void(0);" data-tab="customtext">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-layers">
              <polygon points="12 2 2 7 12 12 22 7 12 2" />
              <polyline points="2 17 12 22 22 17" />
              <polyline points="2 12 12 17 22 12" />
            </svg>
            Interface</a>
        </li>
        <li>
        <a href="javascript:void(0);" data-tab="speech">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-sliders">
              <line x1="4" y1="21" x2="4" y2="14" />
              <line x1="4" y1="10" x2="4" y2="3" />
              <line x1="12" y1="21" x2="12" y2="12" />
              <line x1="12" y1="8" x2="12" y2="3" />
              <line x1="20" y1="21" x2="20" y2="16" />
              <line x1="20" y1="12" x2="20" y2="3" />
              <line x1="1" y1="14" x2="7" y2="14" />
              <line x1="9" y1="8" x2="15" y2="8" />
              <line x1="17" y1="16" x2="23" y2="16" />
        </svg>
        Speech</a>
        </li>
        <li>
        <a href="javascript:void(0);" data-tab="quota">
            <svg xmlns="http://www.w3.org/2000/svg" style="transform: rotate(90deg)" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-columns">
              <path d="M12 3h7a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-7m0-18H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7m0-18v18" />
            </svg>
            Quotas</a>
        </li>

        <li>
        <a href="javascript:void(0);" data-tab="security">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-square">
              <polyline points="9 11 12 14 22 4" />
              <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
            </svg>
            Security</a>
        </li>
      </ul>
    </nav>
  </div>
  <div class="demo-page-main-content">
    <main class="demo-page-content">
        <form action="" method="post" id="form-chatbox-setting">
            <?php wp_nonce_field('wpaicg_chat_widget_save'); ?>
            <!-- AI SETTINGS -->
            <section id="aisettings" class="tab-content">
                <h3 style="margin-top: -1em;"><?php echo esc_html__('AI Settings','gpt3-ai-content-generator')?></h3>
                <div class="nice-form-group" style="display: flex;align-items: center;">
                    <!-- Hidden input to hold the value when checkbox is not checked -->
                    <input type="hidden" name="wpaicg_chat_widget[status]" value="">
                    <!-- Checkbox acting as a switch -->
                    <input type="checkbox" class="switch"
                        id="wpaicg_chat_widget_activate"
                        name="wpaicg_chat_widget[status]"
                        value="active"
                        <?php echo ($wpaicg_chat_status == 'active') ? 'checked' : ''; ?>
                        style="border-color: #10b981; margin-top: 4px;">
                        <label><?php echo esc_html__('Enable Widget', 'gpt3-ai-content-generator'); ?></label>
                </div>

                <!-- AI Provider -->
                <div class="nice-form-group">
                    <label><?php echo esc_html__('AI Provider', 'gpt3-ai-content-generator'); ?></label>
                    <select id="wpaicg_widget_provider" name="wpaicg_chat_widget[provider]">
                        <option value="OpenAI" <?php selected($wpaicg_provider, 'OpenAI'); ?>><?php echo esc_html__('OpenAI', 'gpt3-ai-content-generator'); ?></option>
                        <option value="Google" <?php selected($wpaicg_provider, 'Google'); ?>><?php echo esc_html__('Google', 'gpt3-ai-content-generator'); ?></option>
                        <option value="OpenRouter" <?php selected($wpaicg_provider, 'OpenRouter'); ?>><?php echo esc_html__('OpenRouter', 'gpt3-ai-content-generator'); ?></option>
                        <option value="Azure" <?php selected($wpaicg_provider, 'Azure'); ?>><?php echo esc_html__('Microsoft', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>

                <div class="openai-models">
                    <?php
                    $gpt4_models = \WPAICG\WPAICG_Util::get_instance()->openai_gpt4_models;
                    $gpt35_models = \WPAICG\WPAICG_Util::get_instance()->openai_gpt35_models;
                    $custom_models = get_option('wpaicg_custom_models', []);
                    function display_options($models, $selected_model){
                        foreach ($models as $model_key => $model_name): ?>
                            <option value="<?php echo esc_attr($model_key); ?>" <?php selected($model_key, $selected_model); ?>><?php echo esc_html($model_name); ?></option>
                        <?php endforeach;
                    }
                    function display_custom_model_options($custom_models, $selected_model){
                        foreach ($custom_models as $model_identifier) {
                            ?>
                            <option value="<?php echo esc_attr($model_identifier); ?>" <?php selected($model_identifier, $selected_model); ?>><?php echo esc_html($model_identifier); ?></option>
                            <?php
                        }
                    }
                    ?>
                    <div class="nice-form-group">
                        <label for="wpaicg_chat_model"><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                        <div class="nice-form-group" style="display: flex; align-items: center;">
                            <select id="wpaicg_chat_model" name="wpaicg_chat_model" style="width: 211.75px;margin-right: 5px;">
                                <optgroup label="GPT-4">
                                    <?php display_options($gpt4_models, $wpaicg_chat_model); ?>
                                </optgroup>
                                <optgroup label="GPT-3.5">
                                    <?php display_options($gpt35_models, $wpaicg_chat_model); ?>
                                </optgroup>
                                <optgroup label="Custom Models">
                                    <?php display_custom_model_options($custom_models, $wpaicg_chat_model); ?>
                                </optgroup>
                            </select>
                            <button id="syncButton" class="button button-primary wpaicg_sync_finetune" type="button" style="line-height: 3.5;height: 40px">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="google-models" style="display: none;">
                    <?php
                    $serialized_google_models = get_option('wpaicg_google_model_list', '');
                    $google_models = maybe_unserialize($serialized_google_models);
                    if (empty($google_models)) {
                        $google_models = ['gemini-pro' => 'Gemini Pro'];
                    } else {
                        $google_models = array_combine($google_models, array_map(function($model) {
                            return ucwords(str_replace('-', ' ', $model));
                        }, $google_models));
                    }
                    ?>
                    <div class="nice-form-group">
                        <label for="wpaicg_widget_google_model"><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                        <div class="nice-form-group" style="display: flex; align-items: center;">
                            <select id="wpaicg_widget_google_model" name="wpaicg_widget_google_model" style="width: 211.75px;margin-right: 5px;">
                                <?php $wpaicg_widget_google_model = get_option('wpaicg_widget_google_model', 'gemini-pro'); ?>
                                <?php foreach ($google_models as $model_key => $model_name): 
                                    $is_vision_model = strpos(strtolower($model_name), 'vision') !== false;
                                    ?>
                                    <option value="<?php echo esc_attr($model_key); ?>"<?php if ($is_vision_model) echo ' disabled'; ?><?php selected($model_key, $wpaicg_widget_google_model); ?>><?php echo esc_html($model_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button id="syncGoogleButton" class="button button-primary wpaicg_sync_google_models" type="button" style="line-height: 3.5;height: 40px">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="azure-models" style="display: none;">
                    <?php $deployment_name = get_option('wpaicg_azure_deployment', ''); ?>
                    <div class="nice-form-group">
                        <label for="wpaicg_azure_model"><?php echo esc_html__('Deployment', 'gpt3-ai-content-generator'); ?></label>
                        <input type="text" id="wpaicg_azure_model" name="wpaicg_azure_deployment" placeholder="<?php echo esc_html__('Enter Azure Deployment', 'gpt3-ai-content-generator'); ?>" value="<?php echo esc_attr($deployment_name); ?>">
                    </div>
                </div>

                <div class="openrouter-models" style="display: none;">
                    <?php
                    $openrouter_models = get_option('wpaicg_openrouter_model_list', []);
                    $openrouter_grouped_models = [];
                    foreach ($openrouter_models as $openrouter_model) {
                        $openrouter_provider = explode('/', $openrouter_model['id'])[0];
                        if (!isset($openrouter_grouped_models[$openrouter_provider])) {
                            $openrouter_grouped_models[$openrouter_provider] = [];
                        }
                        $openrouter_grouped_models[$openrouter_provider][] = $openrouter_model;
                    }
                    ksort($openrouter_grouped_models);

                    $openrouter_selected_model = get_option('wpaicg_widget_openrouter_model', 'openrouter/auto');
                    ?>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                        <div class="nice-form-group" style="display: flex; align-items: center;">
                            <select id="wpaicg_openrouter_model" name="wpaicg_widget_openrouter_model" style="width: 211.75px;margin-right: 5px;">
                                <?php
                                foreach ($openrouter_grouped_models as $openrouter_provider => $openrouter_models) {
                                    echo '<optgroup label="' . esc_attr($openrouter_provider) . '">';
                                    usort($openrouter_models, function($a, $b) {
                                        return strcmp($a["name"], $b["name"]);
                                    });
                                    foreach ($openrouter_models as $openrouter_model) {
                                        $openrouter_is_selected = ($openrouter_selected_model === $openrouter_model['id']) ? ' selected' : '';
                                        echo '<option value="' . esc_attr($openrouter_model['id']) . '"' . $openrouter_is_selected . '>' . esc_html($openrouter_model['name']) . '</option>';
                                    }
                                    echo '</optgroup>';
                                }
                                ?>
                            </select>
                            <button id="syncButton" class="button button-primary wpaicg_sync_openrouter_models" type="button" style="line-height: 3.5;height: 40px">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-refresh-cw">
                                    <polyline points="23 4 23 10 17 10"></polyline>
                                    <polyline points="1 20 1 14 7 14"></polyline>
                                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Streaming and Image Upload Options -->
                <fieldset class="nice-form-group">
                <legend><?php echo esc_html__('Options', 'gpt3-ai-content-generator'); ?></legend>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_stream_nav_setting === '1' ? ' checked' : ''; ?> value="1" type="checkbox" name="wpaicg_stream_nav_option" id="wpaicg_stream_nav_option">
                        <label for="wpaicg_stream_nav_option"><?php echo esc_html__('Streaming','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_image_enable ? ' checked' : '' ?> value="1" class="wpaicg_chat_widget_image" type="checkbox" name="wpaicg_chat_widget[image_enable]" id="wpaicg_chat_widget_image">
                        <label for="wpaicg_chat_widget_image"><?php echo esc_html__('Image Upload', 'gpt3-ai-content-generator'); ?></label>
                    </div>
                    <?php
                    if(!isset($wpaicg_chat_widget['chat_addition_option']) || $wpaicg_chat_addition){
                        $wpaicg_chat_addition = true;
                    }
                    ?>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_chat_addition == '1' ? ' checked': ''?> name="wpaicg_chat_addition" value="1" type="checkbox" id="wpaicg_chat_addition">
                        <label for="wpaicg_chat_addition"><?php echo esc_html__('Instructions','gpt3-ai-content-generator')?></label>
                        <input name="wpaicg_chat_widget[chat_addition_option]" value="<?php echo $wpaicg_chat_addition ? 0 : 1?>" type="hidden" id="wpaicg_chat_addition_option">
                    </div>
                </fieldset>
                <!-- Instructions -->
                <?php
                    $wpaicg_additions_json = file_get_contents(WPAICG_PLUGIN_DIR.'admin/chat/context.json');
                    $wpaicg_additions = json_decode($wpaicg_additions_json, true);
                    ?>
                <div class="nice-form-group">
                    <label for="wpaicg_chat_addition_template"><?php echo esc_html__('Instructions','gpt3-ai-content-generator')?></label>
                    <select <?php echo !$wpaicg_chat_addition ? ' disabled':'';?> class="wpaicg_chat_addition_template" id="wpaicg_chat_addition_template">
                        <option value=""><?php echo esc_html__('Select Template','gpt3-ai-content-generator')?></option>
                        <?php
                        foreach($wpaicg_additions as $key=>$wpaicg_addition){
                            echo '<option value="'.esc_html($wpaicg_addition).'">'.esc_html($key).'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="nice-form-group">
                    <textarea <?php echo !$wpaicg_chat_addition ? ' disabled':''?> class="wpaicg_chat_addition_text" rows="8" id="wpaicg_chat_addition_text" name="wpaicg_chat_addition_text"><?php echo !empty($wpaicg_chat_addition_text) ? esc_html($wpaicg_chat_addition_text) : esc_html__('You are a helpful AI Assistant. Please be friendly.','gpt3-ai-content-generator')?></textarea>
                </div>
                <p></p>
                <!-- Advanced Parameters -->
                <a href="#" id="wpaicg-advanced-settings-link"><?php echo esc_html__('Show Advanced Parameters','gpt3-ai-content-generator'); ?></a>
                <div id="wpaicg-advanced-settings" style="display: none;">
                    <div class="nice-form-group">
                        <label for="label_temperature"><?php echo esc_html__('Temperature','gpt3-ai-content-generator')?></label>
                        <input type="text" id="label_temperature" name="wpaicg_chat_temperature" value="<?php echo esc_html( $wpaicg_chat_temperature ) ; ?>">
                    </div>
                    <div class="nice-form-group">
                        <label for="label_max_tokens"><?php echo esc_html__('Max Tokens','gpt3-ai-content-generator')?></label>
                        <input type="text" id="label_max_tokens" name="wpaicg_chat_max_tokens" value="<?php echo esc_html( $wpaicg_chat_max_tokens ) ; ?>" >
                    </div>
                    <div class="nice-form-group">
                        <label for="label_top_p"><?php echo esc_html__('Top P','gpt3-ai-content-generator')?></label>
                        <input type="text" id="label_top_p" name="wpaicg_chat_top_p" value="<?php echo esc_html( $wpaicg_chat_top_p ) ; ?>" >
                    </div>
                    <div class="nice-form-group">
                        <input type="hidden" id="label_best_of" name="wpaicg_chat_best_of" value="<?php echo esc_html( $wpaicg_chat_best_of ) ; ?>" >
                    </div>
                    <div class="nice-form-group">
                        <label for="label_frequency_penalty"><?php echo esc_html__('Frequency Penalty','gpt3-ai-content-generator')?></label>
                        <input type="text" id="label_frequency_penalty" name="wpaicg_chat_frequency_penalty" value="<?php echo esc_html( $wpaicg_chat_frequency_penalty ) ; ?>" >
                    </div>
                    <div class="nice-form-group">
                        <label for="label_presence_penalty"><?php echo esc_html__('Presence Penalty','gpt3-ai-content-generator')?></label>
                        <input type="text" id="label_presence_penalty" name="wpaicg_chat_presence_penalty" value="<?php echo esc_html( $wpaicg_chat_presence_penalty ) ; ?>" >
                    </div>
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25" name="wpaicg_submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                        <!-- Export Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="exportButton"><?php echo esc_html__('Export', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Import Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="importButton"><?php echo esc_html__('Import', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Hidden File Input for Import -->
                        <input type="file" id="importFileInput" style="display: none;" accept=".json">
                        <!-- Reset Button -->
                        <button type="button" class="button button-link-delete" id="resetButton"><?php echo esc_html__('Reset', 'gpt3-ai-content-generator'); ?></button>
                    </summary>
                </details>
            </section>
            <!-- INTERFACE -->
            <section id="customtext" class="tab-content" style="display: none;">
                <h3 style="margin-top: -1em;"><?php echo esc_html__('Interface','gpt3-ai-content-generator')?></h3>        
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Widget Icon (75x75)','gpt3-ai-content-generator')?></label>
                    <div style="display: inline-flex; align-items: center">
                        <input <?php echo $wpaicg_chat_icon == 'default' ? ' checked': ''?> class="wpaicg_chatbox_icon_default" type="radio" value="default" name="wpaicg_chat_widget[icon]">
                        <div>
                            <img style="display: block" width="40" height="40" src="<?php echo esc_html(WPAICG_PLUGIN_URL).'admin/images/chatbot.png'?>">
                            <label><?php echo esc_html__('Default','gpt3-ai-content-generator')?></label>
                        </div>
                        <input <?php echo $wpaicg_chat_icon == 'custom' ? ' checked': ''?> type="radio" class="wpaicg_chatbox_icon_custom" value="custom" name="wpaicg_chat_widget[icon]">
                        <div>
                            <div class="wpaicg_chatbox_icon">
                                <?php
                                $wpaicg_chat_icon_url = isset($wpaicg_chat_widget['icon_url']) && !empty($wpaicg_chat_widget['icon_url']) ? $wpaicg_chat_widget['icon_url'] : '';
                                if(!empty($wpaicg_chat_icon_url) && $wpaicg_chat_icon == 'custom'):
                                    $wpaicg_chatbox_icon_url = wp_get_attachment_url($wpaicg_chat_icon_url);
                                    ?>
                                    <img src="<?php echo esc_html($wpaicg_chatbox_icon_url)?>" width="40" height="40">
                                <?php else: ?>
                                    <svg width="40px" height="40px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M246.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 109.3V320c0 17.7 14.3 32 32 32s32-14.3 32-32V109.3l73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128zM64 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 53 43 96 96 96H352c53 0 96-43 96-96V352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V352z"/></svg><br>
                                <?php endif; ?>
                            </div>
                            <label><?php echo esc_html__('Custom','gpt3-ai-content-generator')?></label>
                        </div>
                    </div>
                </div>
                <fieldset class="nice-form-group">
                <legend><?php echo esc_html__('Widget Position', 'gpt3-ai-content-generator'); ?></legend>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_chat_position == 'left' ? ' checked': ''?> type="radio" value="left" name="wpaicg_chat_widget[position]">
                        <label><?php echo esc_html__('Left','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_chat_position == 'right' ? ' checked': ''?> type="radio" value="right" name="wpaicg_chat_widget[position]">
                        <label><?php echo esc_html__('Right','gpt3-ai-content-generator')?></label>
                    </div>
                </fieldset>

                <div class="nice-form-group">
                    <label><?php echo esc_html__('Display After','gpt3-ai-content-generator')?></label>
                    <input placeholder="<?php echo esc_html__('in seconds. eg. 5','gpt3-ai-content-generator')?>" value="<?php echo esc_html($wpaicg_delay_time)?>" type="text" class="wpaicgchat_delay_time" name="wpaicg_chat_widget[delay_time]">
                </div>

                <div class="nice-form-group">
                    <label><?php echo esc_html__('AI Name','gpt3-ai-content-generator')?></label>
                    <input type="text" name="_wpaicg_chatbox_ai_name" value="<?php echo esc_html( get_option( '_wpaicg_chatbox_ai_name', 'AI' ) ) ; ?>" >
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('User Name','gpt3-ai-content-generator')?></label>
                    <input type="text" name="_wpaicg_chatbox_you" value="<?php echo esc_html( get_option( '_wpaicg_chatbox_you', __('You','gpt3-ai-content-generator') ) ) ;?>" >
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Response Wait Message','gpt3-ai-content-generator')?></label>
                    <input type="text" name="_wpaicg_ai_thinking" value="<?php echo esc_html( get_option( '_wpaicg_ai_thinking', __('AI thinking','gpt3-ai-content-generator') ) ) ;?>" >
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Placeholder','gpt3-ai-content-generator')?></label>
                    <input type="text" name="_wpaicg_typing_placeholder" value="<?php echo esc_html( get_option( '_wpaicg_typing_placeholder', __('Type a message','gpt3-ai-content-generator') ) ) ;?>" >
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Welcome Message','gpt3-ai-content-generator')?></label>
                    <input type="text" name="_wpaicg_chatbox_welcome_message" value="<?php echo esc_html( get_option( '_wpaicg_chatbox_welcome_message', __('Hello, I am an AI bot. Ask me anything!','gpt3-ai-content-generator') ) ) ;?>" >
                </div>
                <div class="nice-form-group">
                    <?php $wpaicg_chat_no_answer = get_option('wpaicg_chat_no_answer','')?>
                    <input type="hidden" value="<?php echo esc_html($wpaicg_chat_no_answer)?>" name="wpaicg_chat_no_answer">
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Footer Note','gpt3-ai-content-generator')?></label>
                    <input value="<?php echo wp_kses_post($wpaicg_footer_text)?>" class="wpaicg-footer-note" type="text" name="wpaicg_chat_widget[footer_text]" placeholder="<?php echo esc_html__('Powered by ...','gpt3-ai-content-generator')?>">
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25" name="wpaicg_submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                        <!-- Export Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="exportButton"><?php echo esc_html__('Export', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Import Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="importButton"><?php echo esc_html__('Import', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Hidden File Input for Import -->
                        <input type="file" id="importFileInput" style="display: none;" accept=".json">
                        <!-- Reset Button -->
                        <button type="button" class="button button-link-delete" id="resetButton"><?php echo esc_html__('Reset', 'gpt3-ai-content-generator'); ?></button>
                    </summary>
                </details>
            </section>
            <!-- Knowledge -->
            <section id="knowledge" class="tab-content" style="display: none;">
                <h3 style="margin-top: -1em;"><?php echo esc_html__('Knowledge','gpt3-ai-content-generator')?></h3>
                <div class="nice-form-group" style="display: flex; justify-content: space-between; margin-top: -1em;">
                    <div class="nice-form-group">
                        <label for="wpaicg_chat_content_aware"><?php echo esc_html__('Content Aware','gpt3-ai-content-generator')?></label>
                        <!-- Hidden input to ensure 'no' is submitted if checkbox is unchecked -->
                        <input type="hidden" value="no" name="wpaicg_chat_widget[content_aware]">
                        <input 
                            <?php echo $wpaicg_chat_content_aware == 'yes' ? 'checked' : '' ?>
                            value="yes" 
                            type="checkbox"
                            style="border-color: #10b981; margin-top: 4px;" 
                            class="switch" 
                            id="wpaicg_chat_content_aware" 
                            name="wpaicg_chat_widget[content_aware]">
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_user_aware"><?php echo esc_html__('User Aware','gpt3-ai-content-generator')?></label>
                        <!-- Hidden input to ensure 'no' is submitted if checkbox is unchecked -->
                        <input type="hidden" value="no" name="wpaicg_chat_widget[user_aware]">
                        <input 
                            <?php echo $wpaicg_user_aware == 'yes' ? 'checked' : '' ?>
                            value="yes" 
                            type="checkbox"
                            style="border-color: #10b981; margin-top: 4px;" 
                            class="switch" 
                            id="wpaicg_user_aware" 
                            name="wpaicg_chat_widget[user_aware]">
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_chat_remember_conversation"><?php echo esc_html__('Memory','gpt3-ai-content-generator')?></label>
                        <!-- Hidden input to ensure 'no' is submitted if checkbox is unchecked -->
                        <input type="hidden" value="no" name="wpaicg_chat_widget[remember_conversation]">
                        <!-- Checkbox input, only submits 'yes' when checked -->
                        <input 
                            <?php echo $wpaicg_chat_remember_conversation == 'yes' ? 'checked' : '' ?>
                            value="yes" 
                            type="checkbox"
                            style="border-color: #10b981; margin-top: 4px;" 
                            class="switch" 
                            id="wpaicg_chat_remember_conversation" 
                            name="wpaicg_chat_widget[remember_conversation]">
                    </div>
                </div>
                <fieldset class="nice-form-group">
                <legend><?php echo esc_html__('Data Source', 'gpt3-ai-content-generator'); ?></legend>
                    <div class="nice-form-group">
                        <input <?php echo !$wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? ' checked': ''?><?php echo $wpaicg_chat_content_aware == 'no' ? ' disabled':''?> type="checkbox" id="wpaicg_chat_excerpt" class="<?php echo $wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? 'asdisabled' : ''?>">
                        <label for="wpaicg_chat_excerpt"><?php echo esc_html__('Excerpt','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? ' checked': ''?><?php echo $wpaicg_chat_content_aware == 'no' ? ' disabled':''?> type="checkbox" value="1" name="wpaicg_chat_embedding" id="wpaicg_chat_embedding" class="<?php echo !$wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? 'asdisabled' : ''?>">
                        <label for="wpaicg_chat_embedding"><?php echo esc_html__('Embeddings','gpt3-ai-content-generator')?></label>
                    </div>
                </fieldset>
                <div class="nice-form-group" style="display: flex;justify-content: space-between;">
                    <div class="nice-form-group" style="margin-right: 5px;">
                        <label><?php echo esc_html__('Vector DB','gpt3-ai-content-generator')?></label>
                        <select style="width: 100px;" <?php echo empty($wpaicg_chat_embedding) || $wpaicg_chat_content_aware == 'no' ? ' disabled':''?> name="wpaicg_chat_vectordb" id="wpaicg_chat_vectordb" class="<?php echo !$wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? 'asdisabled' : ''?>">
                            <option value="pinecone" <?php selected($wpaicg_chat_vectordb, 'pinecone'); ?>><?php echo esc_html__('Pinecone','gpt3-ai-content-generator')?></option>
                            <option value="qdrant" <?php selected($wpaicg_chat_vectordb, 'qdrant'); ?>><?php echo esc_html__('Qdrant','gpt3-ai-content-generator')?></option>
                        </select>
                    </div>
                    <div class="nice-form-group" style="margin-right: 5px;">
                        <label><?php echo esc_html__('Index','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_chat_embedding) || $wpaicg_chat_content_aware == 'no' ? ' disabled':''?> name="wpaicg_chat_widget[embedding_index]" id="wpaicg_chat_embedding_index" class="<?php echo !$wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? 'asdisabled' : ''?>">
                            <option value=""><?php echo esc_html__('Default','gpt3-ai-content-generator')?></option>
                            <?php
                                foreach($wpaicg_pinecone_indexes as $index) {
                                    $dimension = isset($index['dimension']) ? ' (' . $index['dimension'] . ')' : ''; // Display dimension if available
                                    $display_name = $index['name'] . $dimension; // Construct display name with dimension
                                    $selected = (isset($wpaicg_chat_widget['embedding_index']) && $wpaicg_chat_widget['embedding_index'] == $index['url']) ? ' selected' : '';
                                    echo '<option value="' . esc_attr($index['url']) . '"' . $selected . '>' . esc_html($display_name) . '</option>';
                                }
                                ?>
                        </select>
                    </div>
                    <div class="nice-form-group" style="margin-right: 5px;">
                        <label><?php echo esc_html__('Collection', 'gpt3-ai-content-generator'); ?></label>
                        <select <?php echo empty($wpaicg_chat_embedding) || $wpaicg_chat_content_aware == 'no' ? ' disabled' : '' ?> name="wpaicg_widget_qdrant_collection" id="wpaicg_chat_qdrant_collection" class="<?php echo !$wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? 'asdisabled' : '' ?>">
                        <?php
                            foreach ($wpaicg_qdrant_collections as $collection) {
                                if (is_array($collection) && isset($collection['name'])) {
                                    // New structure: collection is an array with 'name' and possibly 'dimension'
                                    $name = $collection['name'];
                                    $dimension = isset($collection['dimension']) ? ' (' . $collection['dimension'] . ')' : '';
                                    $display_name = $name . $dimension;
                                } else {
                                    // Old structure: collection is a string
                                    $name = $collection;
                                    $display_name = $collection;
                                }
                                $selected = (isset($wpaicg_widget_qdrant_collection) && $wpaicg_widget_qdrant_collection == $name) ? ' selected' : '';
                                echo '<option value="' . esc_attr($name) . '"' . $selected . '>' . esc_html($display_name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Limit','gpt3-ai-content-generator')?></label>
                        <select style="width: 50px;" <?php echo empty($wpaicg_chat_embedding) || $wpaicg_chat_content_aware == 'no' ? ' disabled':''?> name="wpaicg_chat_embedding_top" id="wpaicg_chat_embedding_top" class="<?php echo !$wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? 'asdisabled' : ''?>">
                            <?php
                            for($i = 1; $i <=5;$i++){
                                echo '<option'.($wpaicg_chat_embedding_top == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <!-- Toggle for using the default embedding model -->
                <div class="nice-form-group" style="display: flex; justify-content: space-between; margin-top: 1em;">
                    <div class="nice-form-group" style="display: flex; align-items: center;">
                        <input type="hidden" name="wpaicg_chat_widget[use_default_embedding]" value="false">
                        <input type="checkbox" class="switch wpaicg_use_default_embedding"
                            id="wpaicg_use_default_embedding"
                            name="wpaicg_chat_widget[use_default_embedding]"
                            value="true"
                            <?php echo $wpaicg_use_default_embedding ? 'checked' : ''; ?>
                            style="border-color: #10b981; margin-top: 4px;">
                        <label for="wpaicg_use_default_embedding"><?php echo esc_html__('Use Default Embedding', 'gpt3-ai-content-generator'); ?></label>
                    </div>
                </div>
                <div class="nice-form-group" id="wpaicg_embedding_model_div" style="display: <?php echo $wpaicg_use_default_embedding ? 'none' : 'block'; ?>;">
                    <label for="wpaicg_embedding_model"><?php echo esc_html__('Embedding models', 'gpt3-ai-content-generator'); ?></label>
                    <select name="wpaicg_chat_widget[embedding_model]" id="wpaicg_embedding_model" onchange="updateProvider()">
                        <?php
                        $embedding_models = \WPAICG\WPAICG_Util::get_instance()->get_embedding_models();
                        foreach ($embedding_models as $provider => $models) {
                            echo '<optgroup label="' . esc_attr($provider) . '">';
                            foreach ($models as $model => $dimension) {
                                $selected = ($model === $wpaicg_embedding_model) ? 'selected' : '';
                                echo '<option value="' . esc_attr($model) . '" data-provider="' . esc_attr($provider) . '" ' . $selected . '>' . esc_html($model) . ' (' . esc_html($dimension) . ')</option>';
                            }
                            echo '</optgroup>';
                        }
                        ?>
                    </select>
                    <input type="hidden" name="wpaicg_chat_widget[embedding_provider]" id="wpaicg_embedding_provider" value="<?php echo esc_attr($wpaicg_embedding_provider); ?>">
                </div>


                <!-- Conversation Starters Section -->
                <div class="nice-form-group" id="wpaicg_conversation_starters_wrapper">
                    <label><?php echo esc_html__('Conversation Starters', 'gpt3-ai-content-generator'); ?></label>
                    <div id="wpaicg_conversation_starters_widget_container">
                        <?php foreach ($wpaicg_conversation_starters_widget as $starter): ?>
                            <div class="nice-form-group">
                                <input type="text" name="wpaicg_conversation_starters_widget[]" oninput="handleInputWidget(event)" value="<?php echo esc_attr($starter['text']); ?>">
                                <?php if ($starter['index'] > 0): // Assuming the first starter should not have a delete button ?>
                                    <button type="button" class="wpaicg-delete-starter" onclick="removeStarter(this)">X</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($wpaicg_conversation_starters_widget)): // Show one empty input if no starters are saved ?>
                            <div class="nice-form-group">
                                <input type="text" name="wpaicg_conversation_starters_widget[]" oninput="handleInputWidget(event)">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if(\WPAICG\wpaicg_util_core()->wpaicg_is_pro()): ?>
                <div class="nice-form-group">
                    <input <?php echo empty($wpaicg_chat_embedding) || $wpaicg_chat_content_aware == 'no' ? ' disabled':''?><?php echo isset($wpaicg_chat_widget['embedding_pdf']) && $wpaicg_chat_widget['embedding_pdf'] ? ' checked':''?> type="checkbox" value="1" name="wpaicg_chat_widget[embedding_pdf]" class="<?php echo !$wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? 'asdisabled' : ''?>" id="wpaicg_chat_embedding_pdf">
                    <label><?php echo esc_html__('Enable PDF Upload','gpt3-ai-content-generator')?></label>
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('PDF Page Limit','gpt3-ai-content-generator')?></label>
                    <select <?php echo empty($wpaicg_chat_embedding) || $wpaicg_chat_content_aware == 'no' ? ' disabled':''?> name="wpaicg_chat_widget[pdf_pages]" id="wpaicg_chat_pdf_pages" class="<?php echo !$wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? 'asdisabled' : ''?>" style="width: 65px!important;">
                        <?php
                        $pdf_pages = isset($wpaicg_chat_widget['pdf_pages']) && !empty($wpaicg_chat_widget['pdf_pages']) ? $wpaicg_chat_widget['pdf_pages'] : 120;
                        for($i=1;$i <= 120;$i++){
                            echo '<option'.($pdf_pages == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="nice-form-group">
                    <label for="wpaicg_chat_embedding_pdf_message" style="vertical-align:top"><?php echo esc_html__('PDF Upload Confirmation Message','gpt3-ai-content-generator')?></label>
                    <textarea <?php echo empty($wpaicg_chat_embedding) || $wpaicg_chat_content_aware == 'no' ? ' disabled':''?> rows="8" name="wpaicg_chat_widget[embedding_pdf_message]" class="<?php echo !$wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? 'asdisabled' : ''?>" id="wpaicg_chat_embedding_pdf_message"><?php echo isset($wpaicg_chat_widget['embedding_pdf_message']) && !empty($wpaicg_chat_widget['embedding_pdf_message']) ? esc_html(str_replace("\\",'',$wpaicg_chat_widget['embedding_pdf_message'])):"Congrats! Your PDF is uploaded now! You can ask questions about your document.\nExample Questions:[questions]"?></textarea>
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('PDF Icon','gpt3-ai-content-generator')?></label>
                    <input style="width: 55px;" value="<?php echo isset($wpaicg_chat_widget['pdf_color']) ? esc_html($wpaicg_chat_widget['pdf_color']): '#d1e8ff'?>" type="color" class="wpaicg_pdf_color" name="wpaicg_chat_widget[pdf_color]" id="wpaicg_pdf_color">
                </div>
                <?php else: ?>
                    <fieldset class="nice-form-group">
                    <legend><?php echo esc_html__('PDF Upload', 'gpt3-ai-content-generator'); ?></legend>
                        <div class="nice-form-group">
                            <input type="checkbox" disabled id="embedding_pdf_disabled">
                            <label><?php echo esc_html__('Enable','gpt3-ai-content-generator')?></label>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpaicg-pricing')); ?>" class="pro-feature-label"><?php echo esc_html__('Pro','gpt3-ai-content-generator')?></a>
                        </div>
                    </fieldset>
                <?php endif; ?>
                <!-- Advanced Parameters --> 
                <p></p>
                <a href="#" id="wpaicg-advanced-content-settings-link"><?php echo esc_html__('Show Additional Options','gpt3-ai-content-generator'); ?></a>
                <div id="wpaicg-advanced-content-settings" style="display: none;">

                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Embedding Type','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_chat_embedding) || $wpaicg_chat_content_aware == 'no' ? ' disabled':''?> name="wpaicg_chat_embedding_type" id="wpaicg_chat_embedding_type" class="<?php echo !$wpaicg_chat_embedding && $wpaicg_chat_content_aware == 'yes' ? 'asdisabled' : ''?>">
                            <option <?php echo $wpaicg_chat_embedding_type ? ' selected':'';?> value="openai"><?php echo esc_html__('Conversational','gpt3-ai-content-generator')?></option>
                            <option <?php echo empty($wpaicg_chat_embedding_type) ? ' selected':''?> value=""><?php echo esc_html__('Non-Conversational','gpt3-ai-content-generator')?></option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Memory Limit','gpt3-ai-content-generator')?></label>
                        <select name="wpaicg_conversation_cut">
                            <?php
                            for($i=3;$i<=50;$i++){
                                echo '<option'.($wpaicg_conversation_cut == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Language','gpt3-ai-content-generator')?></label>
                        <select class="wpaicg-input" id="label_wpai_language" name="wpaicg_chat_language">
                            <?php
                                foreach ($language_options as $code => $name) {
                                    $selected = esc_html($wpaicg_chat_language) == $code ? 'selected' : '';
                                    echo "<option value=\"$code\" $selected>$name</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Tone','gpt3-ai-content-generator')?></label>
                        <select name="wpaicg_chat_widget[tone]">
                            <?php
                                foreach ($tone_options as $code => $name) {
                                    $selected = esc_html($wpaicg_chat_tone) == $code ? 'selected' : '';
                                    echo "<option value=\"$code\" $selected>$name</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Act As','gpt3-ai-content-generator')?></label>
                        <select name="wpaicg_chat_widget[proffesion]">
                            <?php
                                foreach ($profession_options as $code => $name) {
                                    $selected = esc_html($wpaicg_chat_proffesion) == $code ? 'selected' : '';
                                    echo "<option value=\"$code\" $selected>$name</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25" name="wpaicg_submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                        <!-- Export Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="exportButton"><?php echo esc_html__('Export', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Import Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="importButton"><?php echo esc_html__('Import', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Hidden File Input for Import -->
                        <input type="file" id="importFileInput" style="display: none;" accept=".json">
                        <!-- Reset Button -->
                        <button type="button" class="button button-link-delete" id="resetButton"><?php echo esc_html__('Reset', 'gpt3-ai-content-generator'); ?></button>
                    </summary>
                </details>
            </section>
            <!-- SPEECH -->
            <section id="speech" class="tab-content" style="display: none;">
            <h3 style="margin-top: -1em;"><?php echo esc_html__('Speech','gpt3-ai-content-generator')?></h3>
                <?php
                if ($wpaicg_provider == 'OpenAI') {
                    $wpaicg_audio_enable = isset($wpaicg_chat_widget['audio_enable']) && $wpaicg_chat_widget['audio_enable'];
                ?>
                <div class="nice-form-group">
                    <input <?php echo $wpaicg_audio_enable ? ' checked' : '' ?> value="1" class="wpaicg_chat_widget_audio" type="checkbox" name="wpaicg_chat_widget[audio_enable]">
                    <label><?php echo esc_html__('Speech to Text', 'gpt3-ai-content-generator'); ?></label>
                </div>
                <?php
                    } else {
                        // Notice for Azure/Google
                    ?>
                        <div class="nice-form-group">
                            <input type="checkbox" disabled>
                            <label><?php echo esc_html__('Speech to Text', 'gpt3-ai-content-generator'); ?></label>
                        </div>
                        <p>
                            <small><?php echo esc_html__('Speech to Text is available with OpenAI only. If you want to use it, change your provider to OpenAI under Settings - AI Engine.', 'gpt3-ai-content-generator'); ?></small>
                        </p>
                    <?php
                    }
                    ?>
                <div class="nice-form-group">
                    <input <?php echo $wpaicg_chat_to_speech ? 'checked' : ''; ?> value="1" type="checkbox" name="wpaicg_chat_widget[chat_to_speech]" class="wpaicg_chat_to_speech">
                    <label><?php echo esc_html__('Text to Speech','gpt3-ai-content-generator')?></label>
                </div>
                <?php
                    $disabled_voice_fields = !$wpaicg_chat_to_speech;
                    $google_disabled = empty($wpaicg_google_api_key) ? 'disabled' : '';
                    $elevenlabs_disabled = empty($wpaicg_elevenlabs_api) ? 'disabled' : '';
                    $openai_disabled = $wpaicg_provider === 'Azure'  || $wpaicg_provider === 'Google' || $wpaicg_provider === 'OpenRouter' ? 'disabled' : '';
                    ?>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Provider','gpt3-ai-content-generator')?></label>
                    <select name="wpaicg_chat_widget[voice_service]" class="wpaicg_voice_service" <?php echo $disabled_voice_fields ? 'disabled' : ''; ?>>
                        <option <?php echo $wpaicg_chat_voice_service == 'openai' ? 'selected' : ''; ?> value="openai" <?php echo $openai_disabled; ?>><?php echo esc_html__('OpenAI', 'gpt3-ai-content-generator'); ?></option>
                        <option <?php echo $wpaicg_chat_voice_service == 'google' ? 'selected' : ''; ?> value="google" <?php echo $google_disabled; ?>><?php echo esc_html__('Google', 'gpt3-ai-content-generator'); ?></option>
                        <option <?php echo $wpaicg_chat_voice_service == 'elevenlabs' ? 'selected' : ''; ?> value="elevenlabs" <?php echo $elevenlabs_disabled; ?>><?php echo esc_html__('ElevenLabs', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>
                <!-- OpenAI -->
                <div class="wpaicg_voice_service_openai" style="<?php echo $wpaicg_chat_voice_service == 'openai' ? '' : 'display:none'?>">
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                        <select name="wpaicg_chat_widget[openai_model]" class="wpaicg_openai_model" <?php echo $disabled_voice_fields ? 'disabled' : ''; ?>>
                            <option value="tts-1" <?php echo $wpaicg_openai_model == 'tts-1' ? 'selected' : ''; ?>><?php echo esc_html__('tts-1', 'gpt3-ai-content-generator'); ?></option>
                            <option value="tts-1-hd" <?php echo $wpaicg_openai_model == 'tts-1-hd' ? 'selected' : ''; ?>><?php echo esc_html__('tts-1-hd', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Voice', 'gpt3-ai-content-generator'); ?></label>
                        <select name="wpaicg_chat_widget[openai_voice]" class="wpaicg_openai_voice" <?php echo $disabled_voice_fields ? 'disabled' : ''; ?>>
                            <option value="alloy" <?php echo $wpaicg_openai_voice == 'alloy' ? 'selected' : ''; ?>><?php echo esc_html__('alloy', 'gpt3-ai-content-generator'); ?></option>
                            <option value="echo" <?php echo $wpaicg_openai_voice == 'echo' ? 'selected' : ''; ?>><?php echo esc_html__('echo', 'gpt3-ai-content-generator'); ?></option>
                            <option value="fable" <?php echo $wpaicg_openai_voice == 'fable' ? 'selected' : ''; ?>><?php echo esc_html__('fable', 'gpt3-ai-content-generator'); ?></option>
                            <option value="nova" <?php echo $wpaicg_openai_voice == 'nova' ? 'selected' : ''; ?>><?php echo esc_html__('nova', 'gpt3-ai-content-generator'); ?></option>
                            <option value="onyx" <?php echo $wpaicg_openai_voice == 'onyx' ? 'selected' : ''; ?>><?php echo esc_html__('onyx', 'gpt3-ai-content-generator'); ?></option>
                            <option value="shimmer" <?php echo $wpaicg_openai_voice == 'shimmer' ? 'selected' : ''; ?>><?php echo esc_html__('shimmer', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Format', 'gpt3-ai-content-generator'); ?></label>
                        <select name="wpaicg_chat_widget[openai_output_format]" class="wpaicg_openai_output_format" <?php echo $disabled_voice_fields ? 'disabled' : ''; ?>>
                            <option value="mp3" <?php echo $wpaicg_openai_output_format == 'mp3' ? 'selected' : ''; ?>><?php echo esc_html__('mp3', 'gpt3-ai-content-generator'); ?></option>
                            <option value="opus" <?php echo $wpaicg_openai_output_format == 'opus' ? 'selected' : ''; ?>><?php echo esc_html__('opus', 'gpt3-ai-content-generator'); ?></option>
                            <option value="aac" <?php echo $wpaicg_openai_output_format == 'aac' ? 'selected' : ''; ?>><?php echo esc_html__('aac', 'gpt3-ai-content-generator'); ?></option>
                            <option value="flac" <?php echo $wpaicg_openai_output_format == 'flac' ? 'selected' : ''; ?>><?php echo esc_html__('flac', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Speed (0.25 to 4.0)', 'gpt3-ai-content-generator'); ?></label>
                        <input type="number" name="wpaicg_chat_widget[openai_voice_speed]" class="wpaicg_openai_voice_speed" min="0.25" max="4.0" step="0.01" value="<?php echo esc_attr($wpaicg_openai_voice_speed); ?>" <?php echo $disabled_voice_fields ? 'disabled' : ''; ?>>
                    </div>
                </div>
                <!-- Google -->
                <div class="wpaicg_voice_service_google" style="<?php echo $wpaicg_chat_voice_service == 'google' && !empty($wpaicg_google_api_key) ? '' : 'display:none'?>">
                    <?php
                        $wpaicg_voice_language = isset($wpaicg_chat_widget['voice_language']) && !empty($wpaicg_chat_widget['voice_language']) ? $wpaicg_chat_widget['voice_language'] : 'en-US';
                        $wpaicg_voice_name = isset($wpaicg_chat_widget['voice_name']) && !empty($wpaicg_chat_widget['voice_name']) ? $wpaicg_chat_widget['voice_name'] : 'en-US-Studio-M';
                        $wpaicg_voice_device = isset($wpaicg_chat_widget['voice_device']) && !empty($wpaicg_chat_widget['voice_device']) ? $wpaicg_chat_widget['voice_device'] : '';
                        $wpaicg_voice_speed = isset($wpaicg_chat_widget['voice_speed']) && !empty($wpaicg_chat_widget['voice_speed']) ? $wpaicg_chat_widget['voice_speed'] : 1;
                        $wpaicg_voice_pitch = isset($wpaicg_chat_widget['voice_pitch']) && !empty($wpaicg_chat_widget['voice_pitch']) ? $wpaicg_chat_widget['voice_pitch'] : 0;
                    ?>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Language','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_google_api_key) || $disabled_voice_fields ? ' disabled':''?> name="wpaicg_chat_widget[voice_language]" class="wpaicg_voice_language">
                            <?php
                            foreach(\WPAICG\WPAICG_Google_Speech::get_instance()->languages as $key=>$voice_language){
                                echo '<option'.($wpaicg_voice_language == $key ? ' selected':'').' value="'.esc_html($key).'">'.esc_html($voice_language).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Voice','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_google_api_key) || $disabled_voice_fields ? ' disabled':''?> data-value="<?php echo esc_html($wpaicg_voice_name)?>" name="wpaicg_chat_widget[voice_name]" class="wpaicg_voice_name"></select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Audio Device Profile','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_google_api_key) ? ' disabled':''?> name="wpaicg_chat_widget[voice_device]" class="wpaicg_voice_device">
                            <?php
                            foreach(\WPAICG\WPAICG_Google_Speech::get_instance()->devices() as $key => $device){
                                echo '<option'.($wpaicg_voice_device == $key ? ' selected':'').' value="'.esc_html($key).'">'.esc_html($device).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Speed','gpt3-ai-content-generator')?></label>
                        <input <?php echo empty($wpaicg_google_api_key) || $disabled_voice_fields ? ' disabled':''?> type="text" class="wpaicg_voice_speed" value="<?php echo esc_html($wpaicg_voice_speed)?>" name="wpaicg_chat_widget[voice_speed]">
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Pitch','gpt3-ai-content-generator')?></label>
                        <input <?php echo empty($wpaicg_google_api_key) || $disabled_voice_fields ? ' disabled':''?> type="text" class="wpaicg_voice_pitch" value="<?php echo esc_html($wpaicg_voice_pitch)?>" name="wpaicg_chat_widget[voice_pitch]">
                    </div>
                </div>
                <!-- ElevenLabs -->
                <div class="wpaicg_voice_service_elevenlabs" style="<?php echo $wpaicg_chat_voice_service == 'elevenlabs' && !empty($wpaicg_elevenlabs_api) ? '' : 'display:none'?>">
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Voice','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_elevenlabs_api) || $disabled_voice_fields ? ' disabled':''?> name="wpaicg_chat_widget[elevenlabs_voice]" class="wpaicg_elevenlabs_voice">
                            <?php
                            foreach(\WPAICG\WPAICG_ElevenLabs::get_instance()->voices as $key=>$voice){
                                echo '<option'.($wpaicg_elevenlabs_voice == $key ? ' selected':'').' value="'.esc_html($key).'">'.esc_html($voice).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Model', 'gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_elevenlabs_api) || $disabled_voice_fields ? ' disabled':''?> name="wpaicg_chat_widget[elevenlabs_model]" class="wpaicg_elevenlabs_model">
                            <?php
                            foreach(\WPAICG\WPAICG_ElevenLabs::get_instance()->models as $key=>$model){
                                echo '<option'.($wpaicg_elevenlabs_model == $key ? ' selected':'').' value="'.esc_html($key).'">'.esc_html($model).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25" name="wpaicg_submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                        <!-- Export Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="exportButton"><?php echo esc_html__('Export', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Import Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="importButton"><?php echo esc_html__('Import', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Hidden File Input for Import -->
                        <input type="file" id="importFileInput" style="display: none;" accept=".json">
                        <!-- Reset Button -->
                        <button type="button" class="button button-link-delete" id="resetButton"><?php echo esc_html__('Reset', 'gpt3-ai-content-generator'); ?></button>
                    </summary>
                </details>
            </section>
            <!-- QUOTA -->
            <section id="quota" class="tab-content" style="display: none;">
                <h3 style="margin-top: -1em;"><?php echo esc_html__('Quotas','gpt3-ai-content-generator')?></h3>
                <div class="nice-form-group">
                    <input <?php echo $wpaicg_user_limited ? ' checked': ''?> class="wpaicg_user_token_limit" type="checkbox" value="1" name="wpaicg_chat_widget[user_limited]">
                    <label><?php echo esc_html__('Limit Registered Users','gpt3-ai-content-generator')?></label>
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Token Allocation','gpt3-ai-content-generator')?></label>
                    <input <?php echo $wpaicg_user_limited ? '' : ' disabled'?> class="wpaicg_user_token_limit_text" type="text" value="<?php echo esc_html($wpaicg_user_tokens)?>" name="wpaicg_chat_widget[user_tokens]">
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Token Allocation by Role','gpt3-ai-content-generator')?></label>
                    <?php
                    foreach($wpaicg_roles as $key=>$wpaicg_role){
                        echo '<input class="wpaicg_role_'.esc_html($key).'" value="'.(isset($wpaicg_chat_widget['limited_roles'][$key]) && !empty($wpaicg_chat_widget['limited_roles'][$key]) ? esc_html($wpaicg_chat_widget['limited_roles'][$key]) : '').'" type="hidden" name="wpaicg_chat_widget[limited_roles]['.esc_html($key).']">';
                    }
                    ?>
                    <input <?php echo $wpaicg_user_limited ? '': (isset($wpaicg_chat_widget['role_limited']) && $wpaicg_chat_widget['role_limited'] ? ' checked':'')?> type="checkbox" value="1" class="wpaicg_role_limited" name="wpaicg_chat_widget[role_limited]">
                    <a href="javascript:void(0)" class="wpaicg_limit_set_role<?php echo $wpaicg_user_limited || !isset($wpaicg_chat_widget['role_limited']) || !$wpaicg_chat_widget['role_limited'] ? ' disabled': ''?>"><?php echo esc_html__('Configure Role Allocations','gpt3-ai-content-generator')?></a>
                </div>
                <div class="nice-form-group">
                    <input <?php echo $wpaicg_guest_limited ? ' checked': ''?> class="wpaicg_guest_token_limit" type="checkbox" value="1" name="wpaicg_chat_widget[guest_limited]">
                    <label><?php echo esc_html__('Limit Non-Registered Users','gpt3-ai-content-generator')?></label>
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Token Allocation','gpt3-ai-content-generator')?></label>
                    <input <?php echo $wpaicg_guest_limited ? '' : ' disabled'?> class="wpaicg_guest_token_limit_text" type="text" value="<?php echo esc_html($wpaicg_guest_tokens)?>" name="wpaicg_chat_widget[guest_tokens]">
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Reset Interval','gpt3-ai-content-generator')?></label>
                    <select name="wpaicg_chat_widget[reset_limit]">
                        <option <?php echo $wpaicg_reset_limit == 0 ? ' selected':''?> value="0"><?php echo esc_html__('Never','gpt3-ai-content-generator')?></option>
                        <option <?php echo $wpaicg_reset_limit == 1 ? ' selected':''?> value="1"><?php echo esc_html__('1 Day','gpt3-ai-content-generator')?></option>
                        <option <?php echo $wpaicg_reset_limit == 3 ? ' selected':''?> value="3"><?php echo esc_html__('3 Days','gpt3-ai-content-generator')?></option>
                        <option <?php echo $wpaicg_reset_limit == 7 ? ' selected':''?> value="7"><?php echo esc_html__('1 Week','gpt3-ai-content-generator')?></option>
                        <option <?php echo $wpaicg_reset_limit == 14 ? ' selected':''?> value="14"><?php echo esc_html__('2 Weeks','gpt3-ai-content-generator')?></option>
                        <option <?php echo $wpaicg_reset_limit == 30 ? ' selected':''?> value="30"><?php echo esc_html__('1 Month','gpt3-ai-content-generator')?></option>
                        <option <?php echo $wpaicg_reset_limit == 60 ? ' selected':''?> value="60"><?php echo esc_html__('2 Months','gpt3-ai-content-generator')?></option>
                        <option <?php echo $wpaicg_reset_limit == 90 ? ' selected':''?> value="90"><?php echo esc_html__('3 Months','gpt3-ai-content-generator')?></option>
                        <option <?php echo $wpaicg_reset_limit == 180 ? ' selected':''?> value="180"><?php echo esc_html__('6 Months','gpt3-ai-content-generator')?></option>
                    </select>
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Notice','gpt3-ai-content-generator')?></label>
                    <input type="text" value="<?php echo esc_html($wpaicg_limited_message)?>" name="wpaicg_chat_widget[limited_message]">
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25" name="wpaicg_submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                        <!-- Export Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="exportButton"><?php echo esc_html__('Export', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Import Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="importButton"><?php echo esc_html__('Import', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Hidden File Input for Import -->
                        <input type="file" id="importFileInput" style="display: none;" accept=".json">
                        <!-- Reset Button -->
                        <button type="button" class="button button-link-delete" id="resetButton"><?php echo esc_html__('Reset', 'gpt3-ai-content-generator'); ?></button>
                    </summary>
                </details>
            </section>
            <!-- SECURITY -->
            <section id="security" class="tab-content" style="display: none;">
                <h3 style="margin-top: -1em;"><?php echo esc_html__('Security','gpt3-ai-content-generator')?></h3>
                <fieldset class="nice-form-group">
                <legend><?php echo esc_html__('Logs', 'gpt3-ai-content-generator'); ?></legend>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_save_logs ? ' checked':''?> class="wpaicg_chatbot_save_logs" value="1" type="checkbox" name="wpaicg_chat_widget[save_logs]">
                        <label><?php echo esc_html__('Save Chat Logs','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_save_logs ? '': ' disabled'?><?php echo $wpaicg_save_logs && isset($wpaicg_chat_widget['log_request']) && $wpaicg_chat_widget['log_request'] ? ' checked' : ''?> class="wpaicg_chatbot_log_request" value="1" type="checkbox" name="wpaicg_chat_widget[log_request]">
                        <label><?php echo esc_html__('Save Prompt Details','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_save_logs ? '': ' disabled'?><?php echo $wpaicg_log_notice ? ' checked':''?> class="wpaicg_chatbot_log_notice" value="1" type="checkbox" name="wpaicg_chat_widget[log_notice]">
                        <label><?php echo esc_html__('Display Notice','gpt3-ai-content-generator')?></label>
                    </div>
                </fieldset>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Notice Text','gpt3-ai-content-generator')?></label>
                    <input <?php echo $wpaicg_save_logs ? '': ' disabled'?> class="regular-text wpaicg_chatbot_log_notice_message" value="<?php echo esc_html($wpaicg_log_notice_message)?>" type="text" name="wpaicg_chat_widget[log_notice_message]">
                </div>
                <?php
                if(!\WPAICG\wpaicg_util_core()->wpaicg_is_pro() || $wpaicg_provider === 'Azure' || $wpaicg_provider === 'Google' || $wpaicg_provider === 'OpenRouter'):
                    if($wpaicg_provider === 'Azure' || $wpaicg_provider === 'Google' || $wpaicg_provider === 'OpenRouter'):
                        echo '<small>'. esc_html__('Moderation is available in OpenAI only. If you want to use the moderation tool, please change your model to OpenAI under Settings - AI Engine tab.', 'gpt3-ai-content-generator') .'</small>';
                    endif;
                ?>

                <fieldset class="nice-form-group">
                    <legend><?php echo esc_html__('Moderation', 'gpt3-ai-content-generator'); ?></legend>
                        <div class="nice-form-group">
                            <input <?php echo !\WPAICG\wpaicg_util_core()->wpaicg_is_pro() ? 'disabled' : ''; ?> type="checkbox">
                            <label><?php echo esc_html__('Enable','gpt3-ai-content-generator')?></label>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wpaicg-pricing')); ?>" class="pro-feature-label"><?php echo esc_html__('Pro','gpt3-ai-content-generator')?></a>
                        </div>
                    </fieldset>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Model','gpt3-ai-content-generator')?></label>
                    <select disabled>
                        <option value="text-moderation-latest">text-moderation-latest</option>
                        <option value="text-moderation-stable">text-moderation-stable</option>
                    </select>
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Notice','gpt3-ai-content-generator')?></label>
                    <textarea rows="8" disabled><?php echo esc_html__('Your message has been flagged as potentially harmful or inappropriate. Please ensure that your messages are respectful and do not contain language or content that could be offensive or harmful to others. Thank you for your cooperation.','gpt3-ai-content-generator')?></textarea>
                </div>
                <?php else: ?>
                <div class="nice-form-group">
                    <input <?php echo isset($wpaicg_chat_widget['moderation']) && $wpaicg_chat_widget['moderation'] ? ' checked': ''?>  name="wpaicg_chat_widget[moderation]" value="1" type="checkbox">
                    <label><?php echo esc_html__('Moderation','gpt3-ai-content-generator')?></label>
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Model','gpt3-ai-content-generator')?></label>
                    <select name="wpaicg_chat_widget[moderation_model]">
                        <option <?php echo isset($wpaicg_chat_widget['moderation_model']) && $wpaicg_chat_widget['moderation_model'] == 'text-moderation-latest' ? ' selected':'';?> value="text-moderation-latest">text-moderation-latest</option>
                        <option <?php echo isset($wpaicg_chat_widget['moderation_model']) && $wpaicg_chat_widget['moderation_model'] == 'text-moderation-stable' ? ' selected':'';?> value="text-moderation-stable">text-moderation-stable</option>
                    </select>
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Notice','gpt3-ai-content-generator')?></label>
                    <textarea name="wpaicg_chat_widget[moderation_notice]"><?php echo isset($wpaicg_chat_widget['moderation_notice']) ? esc_html($wpaicg_chat_widget['moderation_notice']) : esc_html__('Your message has been flagged as potentially harmful or inappropriate. Please ensure that your messages are respectful and do not contain language or content that could be offensive or harmful to others. Thank you for your cooperation.','gpt3-ai-content-generator')?></textarea>
                </div>
                <?php endif; ?>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25" name="wpaicg_submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                        <!-- Export Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="exportButton"><?php echo esc_html__('Export', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Import Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="importButton"><?php echo esc_html__('Import', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Hidden File Input for Import -->
                        <input type="file" id="importFileInput" style="display: none;" accept=".json">
                        <!-- Reset Button -->
                        <button type="button" class="button button-link-delete" id="resetButton"><?php echo esc_html__('Reset', 'gpt3-ai-content-generator'); ?></button>
                    </summary>
                </details>
            </section>
            <!-- STYLE -->
            <section id="style" class="tab-content" style="line-height: normal;display: none;">
                <h3 style="margin-top: -1em;"><?php echo esc_html__('Themes','gpt3-ai-content-generator')?></h3>
                <div class="options-container" style="display: flex; justify-content: space-between;margin-top: -1em;">
                    <!-- First Options Group -->
                    <div class="options-group">
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="default" checked>
                            <label><?php echo esc_html__('Default','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="midnightElegance">
                            <label><?php echo esc_html__('Night','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="sunriseSerenity">
                            <label><?php echo esc_html__('Sun','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="forestWhisper">
                            <label><?php echo esc_html__('Forest','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="oceanBreeze">
                            <label><?php echo esc_html__('Ocean','gpt3-ai-content-generator')?></label>
                        </div>
                    </div>
                    <!-- Second Options Group -->
                    <div class="options-group">
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="spaceGalaxy">
                            <label><?php echo esc_html__('Space','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="desertDune">
                            <label><?php echo esc_html__('Desert','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="winterWonderland">
                            <label><?php echo esc_html__('Winter','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="cityscapeGlow">
                            <label><?php echo esc_html__('City','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="mountainPeak">
                            <label><?php echo esc_html__('Mountain','gpt3-ai-content-generator')?></label>
                        </div>
                    </div>
                    <!-- Third Options Group -->
                    <div class="options-group">
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="glade">
                            <label><?php echo esc_html__('Glade','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="dusk">
                            <label><?php echo esc_html__('Dusk','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="dawn">
                            <label><?php echo esc_html__('Dawn','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="mist">
                            <label><?php echo esc_html__('Mist','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="veil">
                            <label><?php echo esc_html__('Veil','gpt3-ai-content-generator')?></label>
                        </div>
                    </div>
                    <!-- Fourth Options Group -->
                    <div class="options-group">
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="peak">
                            <label><?php echo esc_html__('Peak','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="vale">
                            <label><?php echo esc_html__('Vale','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="cove">
                            <label><?php echo esc_html__('Cove','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="rift">
                            <label><?php echo esc_html__('Rift','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="wpaicg-option-container">
                            <input type="radio" name="chat_theme" value="isle">
                            <label><?php echo esc_html__('Isle','gpt3-ai-content-generator')?></label>
                        </div>
                    </div>
                </div>
                <h3 style="font-size: small;"><?php echo esc_html__('Chat Window','gpt3-ai-content-generator')?></h3>
                <!-- Color Pickers Container -->
                <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: -1em;">
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Box','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_chat_bgcolor)?>" type="color" class="wpaicgchat_bg_color" name="wpaicg_chat_widget[bgcolor]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_chat_fontcolor)?>" type="color" class="wpaicgchat_font_color" name="wpaicg_chat_widget[fontcolor]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('AI','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_ai_bg_color)?>" type="color" class="wpaicgchat_ai_color" name="wpaicg_chat_widget[ai_bg_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('User','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_user_bg_color)?>" type="color" class="wpaicgchat_user_color" name="wpaicg_chat_widget[user_bg_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                        <select style="width: 50px;font-size: 11px;height: 32px;padding: 0 1.2em;background-position: right;" name="wpaicg_chat_widget[fontsize]" class="wpaicg_chat_widget_font_size">
                            <?php
                            for($i = 10; $i <= 30; $i++){
                                echo '<option'.($wpaicg_chat_fontsize == $i ? ' selected': '').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group" style="flex: 1;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Radius','gpt3-ai-content-generator')?></label>
                        <input style="width: 50px;font-size: 11px;height: 32px;" value="<?php echo esc_html($wpaicg_chat_rounded)?>" type="number" min="0" class="wpaicg_chat_rounded" name="wpaicg_chat_widget[chat_rounded]">
                    </div>
                </div>
                <div class="nice-form-group" style="display: flex;margin-top: -0.1em;">
                    <div class="nice-form-group" style="padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Width','gpt3-ai-content-generator')?></label>
                        <input style="width: 100px;font-size: 11px;height: 32px;" value="<?php echo esc_html($wpaicg_chat_width)?>" class="wpaicg_chat_widget_width" min="100" type="text" name="wpaicg_chat_widget[width]">
                    </div>
                    <div class="nice-form-group">
                        <label style="font-size: 11px;"><?php echo esc_html__('Height','gpt3-ai-content-generator')?></label>
                        <input style="width: 100px;font-size: 11px;height: 32px;" value="<?php echo esc_html($wpaicg_chat_height)?>" class="wpaicg_chat_widget_height" min="100" type="text" name="wpaicg_chat_widget[height]">
                    </div>
                </div>
                <!-- Text Field -->
                <h3 style="font-size: small;"><?php echo esc_html__('Text Field','gpt3-ai-content-generator')?></h3>
                <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: -1em;">
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Box','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_bg_text_field)?>" type="color" class="wpaicgchat_input_color" name="wpaicg_chat_widget[bg_text_field]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_input_font_color)?>" type="color" class="wpaicgchat_input_font_color" name="wpaicg_chat_widget[input_font_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Border','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_border_text_field)?>" type="color" class="wpaicgchat_input_border" name="wpaicg_chat_widget[border_text_field]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Button','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_send_color)?>" type="color" class="wpaicgchat_send_color" name="wpaicg_chat_widget[send_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Mic', 'gpt3-ai-content-generator'); ?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo $wpaicg_mic_color; ?>" type="color" class="wpaicg_chat_widget_mic_color" name="wpaicg_chat_widget[mic_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Stop', 'gpt3-ai-content-generator'); ?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo $wpaicg_stop_color; ?>" type="color" name="wpaicg_chat_widget[stop_color]" class="wpaicgchat_stop_color">
                    </div>
                </div>
                <div class="nice-form-group" style="display: flex;margin-top: -0.1em;">
                    <div class="nice-form-group" style="padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Height','gpt3-ai-content-generator')?></label>
                        <input style="width: 100px;font-size: 11px;height: 32px;" value="<?php echo esc_html($wpaicg_text_height)?>" type="number" min="30" class="wpaicg_text_height" name="wpaicg_chat_widget[text_height]">
                    </div>
                    <div class="nice-form-group">
                        <label style="font-size: 11px;"><?php echo esc_html__('Radius','gpt3-ai-content-generator')?></label>
                        <input style="width: 100px;font-size: 11px;height: 32px;" value="<?php echo esc_html($wpaicg_text_rounded)?>" type="number" min="0" class="wpaicg_text_rounded" name="wpaicg_chat_widget[text_rounded]">
                    </div>
                </div>
                <!-- Header / Footer -->
                <h3 style="font-size: small;"><?php echo esc_html__('Header / Footer','gpt3-ai-content-generator')?></h3>
                <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: -1em;">
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Box','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_footer_color)?>" type="color" class="wpaicgchat_footer_color" name="wpaicg_chat_widget[footer_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_footer_font_color)?>" type="color" class="wpaicgchat_footer_font_color" name="wpaicg_chat_widget[footer_font_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Icons','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_bar_color)?>" type="color" class="wpaicgchat_bar_color" name="wpaicg_chat_widget[bar_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Wait','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_thinking_color)?>" type="color" class="wpaicgchat_thinking_color" name="wpaicg_chat_widget[thinking_color]">
                    </div>
                </div>
                <div class="nice-form-group" style="display: flex;margin-top: -0.1em;">
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Fullscreen','gpt3-ai-content-generator')?></label>
                        <input style="border-color: #10b981;margin-top: 4px;" <?php echo $wpaicg_chat_fullscreen ? ' checked':''?> value="1" type="checkbox" class="switch wpaicgchat_fullscreen" name="wpaicg_chat_widget[fullscreen]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Download','gpt3-ai-content-generator')?></label>
                        <input style="border-color: #10b981;margin-top: 4px;" <?php echo $wpaicg_chat_download_btn ? ' checked':''?> value="1" type="checkbox" class="switch wpaicgchat_download_btn" name="wpaicg_chat_widget[download_btn]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Clear','gpt3-ai-content-generator')?></label>
                        <input style="border-color: #10b981;margin-top: 4px;" <?php echo $wpaicg_chat_clear_btn ? ' checked':''?> value="1" type="checkbox" class="switch wpaicgchat_clear_btn" name="wpaicg_chat_widget[clear_btn]">
                    </div>
                    <div class="nice-form-group" style="flex: 1;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Close','gpt3-ai-content-generator')?></label>
                        <input style="border-color: #10b981;margin-top: 4px;" <?php echo $wpaicg_chat_close_btn ? ' checked':''?> value="1" type="checkbox" class="switch wpaicgchat_close_btn" name="wpaicg_chat_widget[close_btn]">
                    </div>
                </div>
                <h3 style="font-size: small;"><?php echo esc_html__('Avatar / Icon','gpt3-ai-content-generator')?></h3>
                <div class="nice-form-group" style="display: flex; align-items: center; justify-content: space-between;margin-top: -1em;">
                    <div style="display: inline-flex; align-items: center">
                        <input <?php echo $wpaicg_ai_avatar == 'default' ? ' checked': ''?> class="wpaicg_chatbox_avatar_default" type="radio" value="default" name="wpaicg_chat_widget[ai_avatar]">
                        <input value="<?php echo esc_html($wpaicg_chat_icon_url)?>" type="hidden" name="wpaicg_chat_widget[icon_url]" class="wpaicg_chat_icon_url">
                        <input value="<?php echo esc_html($wpaicg_ai_avatar_id)?>" type="hidden" name="wpaicg_chat_widget[ai_avatar_id]" class="wpaicg_ai_avatar_id">
                        <div style="margin-right: 20px;">
                            <img style="display: block;width: 40px; height: 40px" src="<?php echo esc_html(WPAICG_PLUGIN_URL).'admin/images/chatbot.png'?>">
                            <label style="font-size: 11px;"><?php echo esc_html__('Default','gpt3-ai-content-generator')?></label>
                        </div>
                        <input <?php echo $wpaicg_ai_avatar == 'custom' ? ' checked': ''?> type="radio" class="wpaicg_chatbox_avatar_custom" value="custom" name="wpaicg_chat_widget[ai_avatar]">
                        <div style="margin-right: 10px;">
                            <div class="wpaicg_chatbox_avatar">
                                <?php
                                if(!empty($wpaicg_ai_avatar_id) && $wpaicg_ai_avatar == 'custom'):
                                    $wpaicg_ai_avatar_url = wp_get_attachment_url($wpaicg_ai_avatar_id);
                                    ?>
                                    <img src="<?php echo esc_html($wpaicg_ai_avatar_url)?>" width="40" height="40">
                                <?php else: ?>
                                    <svg width="40px" height="40px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M246.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 109.3V320c0 17.7 14.3 32 32 32s32-14.3 32-32V109.3l73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128zM64 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 53 43 96 96 96H352c53 0 96-43 96-96V352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V352z"/></svg><br>
                                <?php endif; ?>
                            </div>
                            <label style="font-size: 11px;"><?php echo esc_html__('Custom','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="nice-form-group" style="padding-left: 1em;padding-bottom: 25px;">
                            <input <?php echo $wpaicg_use_avatar ? ' checked':''?> value="1" type="checkbox" style="border-color: #10b981;margin-top: 4px;" class="switch wpaicgchat_use_avatar" name="wpaicg_chat_widget[use_avatar]">
                            <label style="font-size: 11px;max-width: fit-content;"><?php echo esc_html__('Use Avatar','gpt3-ai-content-generator')?></label>
                        </div>
                    </div>
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25" name="wpaicg_submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                        <!-- Export Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="exportButton"><?php echo esc_html__('Export', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Import Button -->
                        <button type="button" class="button button-primary wpaicg-w-25" id="importButton"><?php echo esc_html__('Import', 'gpt3-ai-content-generator'); ?></button>
                        <!-- Hidden File Input for Import -->
                        <input type="file" id="importFileInput" style="display: none;" accept=".json">
                        <!-- Reset Button -->
                        <button type="button" class="button button-link-delete" id="resetButton"><?php echo esc_html__('Reset', 'gpt3-ai-content-generator'); ?></button>
                    </summary>
                </details>
            </section>
        </form>
    </main>
    <aside class="demo-page-fixed-content">
        <div class="wpaicg-chatbox-preview">
            <div class="wpaicg-chatbox-preview-box" style="position: relative;">
                <?php
                // Check if $wpaicg_chat_status is set to 'active'
                if($wpaicg_chat_status == 'active') {
                    include __DIR__.'/wpaicg_chat_widget.php';
                } else {
                    // Display this text if $wpaicg_chat_status is not 'active'
                    echo esc_html__('The widget is not active. Once enabled and saved, the preview will be shown here.','gpt3-ai-content-generator');
                }
                ?>
            </div>
        </div>
    </aside>
  </div>
</div>

<script>
    jQuery(document).ready(function ($){
        let wpaicg_google_voices = <?php echo json_encode($wpaicg_google_voices)?>;
        let wpaicg_elevenlab_api = '<?php echo esc_html($wpaicg_elevenlabs_api)?>';
        let wpaicg_google_api_key = '<?php  echo $wpaicg_google_api_key?>';

        function resetForm() {
            var resetSource = 'widget';

            if (confirm('<?php echo esc_html__('Are you sure you want to reset the settings?', 'gpt3-ai-content-generator'); ?>')) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    data: {
                        action: 'wpaicg_reset_settings',
                        nonce: '<?php echo wp_create_nonce('wpaicg_reset_settings'); ?>',
                        source: resetSource
                    },
                    dataType: 'json',
                    type: 'post',
                    success: function(response) {
                        // Check if the request was successful
                        if (response.success) {
                            alert(response.data); // Show success message
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert(response.data); // Show error message
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle potential AJAX errors here
                        alert('An error occurred: ' + error);
                    }
                });
            }
        }

        // trigger when resetButton is clicked
        $(document).on('click', '#resetButton', function() {
            resetForm();
        });

        $(document).on('click', '#importButton', function() {
            //prevent the default action of the button
            event.preventDefault();
            $('#importFileInput').click();
        });

        // Handle file selection
        $('#importFileInput').on('change', function() {
            var file = this.files[0]; // Get the file
            var source = 'widget'; // Adjust based on context

            var formData = new FormData();
            formData.append('action', 'wpaicg_import_settings');
            formData.append('nonce', '<?php echo wp_create_nonce('wpaicg_import_settings_nonce'); ?>');
            formData.append('source', source);
            formData.append('file', file);

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                processData: false, // Important for FormData
                contentType: false, // Important for FormData
                dataType: 'json',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert('Import successful.');
                        location.reload(); // Reload to reflect changes
                    } else {
                        alert('Import failed: ' + response.data);
                    }
                },
                error: function(xhr, status, error) {
                    alert('An error occurred: ' + error);
                }
            });
        });

        // Function to handle export settings
        function exportSettings() {
            var exportSource = 'widget'; // Adjust this based on the current context (shortcode, widget, bot)

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpaicg_export_settings',
                    nonce: '<?php echo wp_create_nonce('wpaicg_export_settings'); ?>',
                    source: exportSource
                },
                success: function(response) {
                    var messageDiv = $('#exportMessage');
                    if (response.success) {
                        // Assuming the response contains a URL to the exported file
                        var downloadLink = '<a href="' + response.data.url + '" download><?php echo esc_html__('Download Exported Settings', 'gpt3-ai-content-generator'); ?></a>';
                        messageDiv.html('<?php echo esc_html__('Export successful.', 'gpt3-ai-content-generator'); ?> ' + downloadLink);
                        $('html, body').animate({scrollTop: 0}, 'slow');
                    } else {
                        messageDiv.html('<?php echo esc_html__('Export failed:', 'gpt3-ai-content-generator'); ?>' + response.data);
                    }
                    messageDiv.show();
                },
                error: function(xhr, status, error) {
                    $('#exportMessage').html('<?php echo esc_html__('An error occurred:', 'gpt3-ai-content-generator'); ?>' + error).show();
                }
            });
        }

        // Attach the exportSettings function to the exportButton's click event
        $(document).on('click', '#exportButton', function() {
            exportSettings();
        });

        // Function to set initial visibility of voice service sections
        function setInitialVoiceServiceVisibility() {
            let selectedVoiceService = $('.wpaicg_voice_service').val();
            toggleVoiceServiceSections(selectedVoiceService);
        }

        // Function to toggle voice service sections based on selected service
        function toggleVoiceServiceSections(service) {
            let parent = $('.wpaicg_voice_service').parent().parent();
            if(service === 'google'){
                parent.find('.wpaicg_voice_service_elevenlabs').hide();
                parent.find('.wpaicg_voice_service_google').show();
                parent.find('.wpaicg_voice_service_openai').hide();
            } else if(service === 'openai'){
                parent.find('.wpaicg_voice_service_elevenlabs').hide();
                parent.find('.wpaicg_voice_service_google').hide();
                parent.find('.wpaicg_voice_service_openai').show();
            } else {
                parent.find('.wpaicg_voice_service_elevenlabs').show();
                parent.find('.wpaicg_voice_service_google').hide();
                parent.find('.wpaicg_voice_service_openai').hide();
            }
        }

        // Set initial visibility on page load
        setInitialVoiceServiceVisibility();

        $(document).on('click','.wpaicg_chat_to_speech', function(e){
            let parent = $(e.currentTarget).parent().parent();
            let voice_service = parent.find('.wpaicg_voice_service');
            let is_openai_selected = voice_service.val() === 'openai';

            if($(e.currentTarget).prop('checked')){
                voice_service.removeAttr('disabled');
                if(is_openai_selected){
                    parent.find('.wpaicg_openai_model').removeAttr('disabled');
                    parent.find('.wpaicg_openai_voice').removeAttr('disabled');
                    parent.find('.wpaicg_openai_output_format').removeAttr('disabled');
                    parent.find('.wpaicg_openai_voice_speed').removeAttr('disabled');
                }
                if(wpaicg_elevenlab_api !== '' || wpaicg_google_api_key !== ''){
                    voice_service.removeAttr('disabled');
                }
                if(wpaicg_elevenlab_api !== ''){
                    parent.find('.wpaicg_elevenlabs_voice').removeAttr('disabled');
                    parent.find('.wpaicg_elevenlabs_model').removeAttr('disabled');
                }
                if(wpaicg_google_api_key !== ''){
                    parent.find('.wpaicg_voice_language').removeAttr('disabled');
                    parent.find('.wpaicg_voice_name').removeAttr('disabled');
                    parent.find('.wpaicg_voice_device').removeAttr('disabled');
                    parent.find('.wpaicg_voice_speed').removeAttr('disabled');
                    parent.find('.wpaicg_voice_pitch').removeAttr('disabled');
                }
            }
            else{
                voice_service.attr('disabled','disabled');
                parent.find('.wpaicg_elevenlabs_voice').attr('disabled','disabled');
                parent.find('.wpaicg_elevenlabs_model').attr('disabled','disabled');
                parent.find('.wpaicg_openai_model').attr('disabled','disabled');
                parent.find('.wpaicg_openai_voice').attr('disabled','disabled');
                parent.find('.wpaicg_openai_output_format').attr('disabled','disabled');
                parent.find('.wpaicg_openai_voice_speed').attr('disabled','disabled');
                parent.find('.wpaicg_voice_language').attr('disabled','disabled');
                parent.find('.wpaicg_voice_name').attr('disabled','disabled');
                parent.find('.wpaicg_voice_device').attr('disabled','disabled');
                parent.find('.wpaicg_voice_speed').attr('disabled','disabled');
                parent.find('.wpaicg_voice_pitch').attr('disabled','disabled');
            }
        });

        $(document).on('change','.wpaicg_voice_service',function(e){
            let parent = $(e.currentTarget).parent().parent();
            if($(e.currentTarget).val() === 'google'){
                parent.find('.wpaicg_voice_service_elevenlabs').hide();
                parent.find('.wpaicg_voice_service_google').show();
                parent.find('.wpaicg_voice_service_openai').hide();
            }
            else if($(e.currentTarget).val() === 'openai'){
                parent.find('.wpaicg_voice_service_elevenlabs').hide();
                parent.find('.wpaicg_voice_service_google').hide();
                parent.find('.wpaicg_voice_service_openai').show();
            }
            else{
                parent.find('.wpaicg_voice_service_elevenlabs').show();
                parent.find('.wpaicg_voice_service_google').hide();
                parent.find('.wpaicg_voice_service_openai').hide();
            }
        })

        $(document).on('keypress','.wpaicg_voice_speed,.wpaicg_voice_pitch', function (e){
            var charCode = (e.which) ? e.which : e.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
                return false;
            }
            return true;
        });

        function wpaicgsetVoices(element){
            let parent = element.parent().parent();
            let language = element.val();
            let voiceNameInput = parent.find('.wpaicg_voice_name');
            voiceNameInput.empty();
            let selected = voiceNameInput.attr('data-value');
            $.each(wpaicg_google_voices[language], function (idx, item){
                voiceNameInput.append('<option'+(selected === item.name ? ' selected':'')+' value="'+item.name+'">'+item.name+' - '+item.ssmlGender+'</option>');
            })
        }
        function wpaicgcollectVoices(element){
            if(!Object.keys(wpaicg_google_voices).length === 0){
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php')?>',
                    data: {action: 'wpaicg_sync_google_voices',nonce: '<?php echo wp_create_nonce('wpaicg_sync_google_voices')?>'},
                    dataType: 'json',
                    type: 'post',
                    success: function(res){
                        if(res.status === 'success'){
                            wpaicg_google_voices = res.voices;
                            wpaicgsetVoices(element);
                        }else{
                            alert(res.message);
                        }
                    }

                });
            }
            else{
                wpaicgsetVoices(element);
            }
        }
        $(document).on('change','.wpaicg_voice_language', function(e){
            wpaicgcollectVoices($(e.currentTarget));
        })
        if($('.wpaicg_voice_language').length){
            wpaicgcollectVoices($('.wpaicg_voice_language'));
        }
        
        $('#form-chatbox-setting').on('submit', function (e){
            if($('.wpaicg_voice_speed').length) {
                let wpaicg_voice_speed = parseFloat($('.wpaicg_voice_speed').val());
                let wpaicg_voice_pitch = parseFloat($('.wpaicg_voice_pitch').val());
                let wpaicg_voice_name = parseFloat($('.wpaicg_voice_name').val());
                let has_error = false;
                if (wpaicg_voice_speed < 0.25 || wpaicg_voice_speed > 4) {
                    has_error = '<?php echo sprintf(
                        /* translators: 1: minimum speed, 2: maximum speed */
                        esc_html__('Please enter valid voice speed value between %1$s and %2$s', 'gpt3-ai-content-generator'), 0.25, 4)?>';
                } else if (wpaicg_voice_pitch < -20 || wpaicg_voice_pitch > 20) {
                    has_error = '<?php echo sprintf(
                        /* translators: 1: minimum pitch, 2: maximum pitch */
                        esc_html__('Please enter valid voice pitch value between %1$s and %2$s', 'gpt3-ai-content-generator'), -20, 20)?>';
                }
                else if(wpaicg_voice_name === ''){
                    has_error = '<?php echo esc_html__('Please select voice name', 'gpt3-ai-content-generator')?>';
                }
                if (has_error) {
                    e.preventDefault();
                    alert(has_error);
                    return false;
                }
            }
        })
        let wpaicg_roles = <?php echo wp_kses_post(json_encode($wpaicg_roles))?>;
        $('.wpaicg_modal_close_second').click(function (){
            $('.wpaicg_modal_close_second').closest('.wpaicg_modal_second').hide();
            $('.wpaicg-overlay-second').hide();
        });
        $(document).on('click', '.wpaicg_chatbot_save_logs', function(e){
            if($(e.currentTarget).prop('checked')){
                $('.wpaicg_chatbot_log_request').removeAttr('disabled');
                $('.wpaicg_chatbot_log_notice').removeAttr('disabled');
                $('.wpaicg_chatbot_log_notice_message').removeAttr('disabled');
            }
            else{
                $('.wpaicg_chatbot_log_request').attr('disabled','disabled');
                $('.wpaicg_chatbot_log_request').prop('checked',false);
                $('.wpaicg_chatbot_log_notice').attr('disabled','disabled');
                $('.wpaicg_chatbot_log_notice').prop('checked',false);
                $('.wpaicg_chatbot_log_notice_message').attr('disabled','disabled');
            }
        });
        $(document).on('keypress','.wpaicg_user_token_limit_text,.wpaicg_update_role_limit,.wpaicg_guest_token_limit_text', function (e){
            var charCode = (e.which) ? e.which : e.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
                return false;
            }
            return true;
        });
        $('.wpaicg_limit_set_role').click(function (){
            if(!$(this).hasClass('disabled')) {
                if ($('.wpaicg_role_limited').prop('checked')) {
                    let html = '';
                    $.each(wpaicg_roles, function (key, role) {
                        let valueRole = $('.wpaicg_role_'+key).val();
                        html += '<div style="padding: 5px;display: flex;justify-content: space-between;align-items: center;"><label><strong>'+role+'</strong></label><input class="wpaicg_update_role_limit" data-target="'+key+'" value="'+valueRole+'" placeholder="<?php echo esc_html__('Empty for no-limit','gpt3-ai-content-generator')?>" type="text"></div>';
                    });
                    html += '<div style="padding: 5px"><button class="button button-primary wpaicg_save_role_limit" style="width: 100%;margin: 5px 0;"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button></div>';
                    $('.wpaicg_modal_title_second').html('<?php echo esc_html__('Role Limit','gpt3-ai-content-generator')?>');
                    $('.wpaicg_modal_content_second').html(html);
                    $('.wpaicg-overlay-second').css('display','flex');
                    $('.wpaicg_modal_second').show();

                } else {
                    $.each(wpaicg_roles, function (key, role) {
                        $('.wpaicg_role_' + key).val('');
                    })
                }
            }
        });
        $(document).on('click','.wpaicg_save_role_limit', function (e){
            $('.wpaicg_update_role_limit').each(function (idx, item){
                let input = $(item);
                let target = input.attr('data-target');
                $('.wpaicg_role_'+target).val(input.val());
            });
            $('.wpaicg_modal_close_second').closest('.wpaicg_modal_second').hide();
            $('.wpaicg-overlay-second').hide();
        });
        $('.wpaicg_guest_token_limit').click(function (){
            if($(this).prop('checked')){
                $('.wpaicg_guest_token_limit_text').removeAttr('disabled');
            }
            else{
                $('.wpaicg_guest_token_limit_text').val('');
                $('.wpaicg_guest_token_limit_text').attr('disabled','disabled');
            }
        });
        $('.wpaicg_role_limited').click(function (){
            if($(this).prop('checked')){
                $('.wpaicg_user_token_limit').prop('checked',false);
                $('.wpaicg_user_token_limit_text').attr('disabled','disabled');
                $('.wpaicg_limit_set_role').removeClass('disabled');
            }
            else{
                $('.wpaicg_limit_set_role').addClass('disabled');
            }
        });
        $('.wpaicg_user_token_limit').click(function (){
            if($(this).prop('checked')){
                $('.wpaicg_user_token_limit_text').removeAttr('disabled');
                $('.wpaicg_role_limited').prop('checked',false);
                $('.wpaicg_limit_set_role').addClass('disabled');
            }
            else{
                $('.wpaicg_user_token_limit_text').val('');
                $('.wpaicg_user_token_limit_text').attr('disabled','disabled');
            }
        });
        $('.wpaicg-chatbox-preview-box > .wpaicg_chat_widget').addClass('wpaicg_widget_open');
        $('.wpaicg-chatbox-preview-box .wpaicg_toggle').addClass('wpaicg_widget_open');
        function wpaicgChangeAvatarRealtime(){
            var wpaicg_user_avatar_check = $('input[name=_wpaicg_chatbox_you]').val()+':';
            var wpaicg_ai_avatar_check = $('input[name=_wpaicg_chatbox_ai_name]').val()+':';
            if($('.wpaicgchat_use_avatar').prop('checked')){
                wpaicg_user_avatar_check = '<img src="<?php echo get_avatar_url(get_current_user_id())?>" height="40" width="40">';
                wpaicg_ai_avatar_check = '<?php echo esc_html(WPAICG_PLUGIN_URL) . 'admin/images/chatbot.png';?>';
                if($('.wpaicg_chatbox_avatar_custom').prop('checked') && $('.wpaicg_chatbox_avatar img').length){
                    wpaicg_ai_avatar_check = $('.wpaicg_chatbox_avatar img').attr('src');
                }
                wpaicg_ai_avatar_check = '<img src="'+wpaicg_ai_avatar_check+'" height="40" width="40">';
            }

            $('.wpaicg-chat-ai-message').each(function (idx, item){
                $(item).find('strong').html(wpaicg_ai_avatar_check);
            });
            $('.wpaicg-chat-user-message').each(function (idx, item){
                $(item).find('strong').html(wpaicg_user_avatar_check);
            });
        }
        $('input[name=_wpaicg_chatbox_you],input[name=_wpaicg_chatbox_ai_name]').on('input', function (){
            wpaicgChangeAvatarRealtime();
        })
        $('.wpaicgchat_use_avatar,.wpaicg_chatbox_avatar_default,.wpaicg_chatbox_avatar_custom').on('click', function (){
            wpaicgChangeAvatarRealtime();
        })
        $('.wpaicg_chat_rounded,.wpaicg_text_rounded,.wpaicg_text_height').on('input', function(){
            wpaicgUpdateRealtime();
        })

        // listen changes on wpaicgchat_bg_color
        $('.wpaicgchat_bg_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox').css('background-color', color); // Set the background color of the chatbox
        });
        // wpaicgchat_font_color
        $('.wpaicgchat_font_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox-messages li').css('color', color); // Set the font color of the chat messages
            // .wpaicg-chatbox .wpaicg-conversation-starter
            $('.wpaicg-chatbox .wpaicg-conversation-starter').css('color', color);
        });

        // wpaicgchat_ai_color
        $('.wpaicgchat_ai_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox-messages li.wpaicg-chat-ai-message').css('background-color', color); // Set the background color of AI messages
        });
        // wpaicgchat_user_color
        $('.wpaicgchat_user_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox-messages li.wpaicg-chat-user-message').css('background-color', color); // Set the background color of user messages
            // .wpaicg-chatbox .wpaicg-conversation-starter
            $('.wpaicg-chatbox .wpaicg-conversation-starter').css('background-color', color);
        });
        // wpaicgchat_input_color
        $('.wpaicgchat_input_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox-typing').css('background-color', color); // Set the background color of the typing area
        });
        // Listen for changes on widget input_font_color
        $('.wpaicgchat_input_font_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input

            // Apply the color to widget textarea directly
            $('textarea.wpaicg-chatbox-typing, textarea.wpaicg-chatbox-auto-expand').css('color', color);

            // For the placeholder in the widget, update the style tag content to increase specificity
            var widgetPlaceholderStyleContent =
                `textarea.wpaicg-chatbox-typing::placeholder,
                textarea.wpaicg-chatbox-auto-expand::placeholder,
                textarea.wpaicg-chatbox-auto-expand.resizing::placeholder,
                textarea.wpaicg-chatbox-auto-expand:focus::placeholder {
                    color: ${color} !important;
                }`;

            // Check if the style tag for widget placeholder colors already exists
            var $widgetPlaceholderStyles = $('#widget-placeholder-colors');
            if ($widgetPlaceholderStyles.length) {
                // Update existing style content
                $widgetPlaceholderStyles.html(widgetPlaceholderStyleContent);
            } else {
                // Create a new style tag and append it to the head
                $widgetPlaceholderStyles = $('<style>')
                    .attr('id', 'widget-placeholder-colors')
                    .html(widgetPlaceholderStyleContent);
                $('head').append($widgetPlaceholderStyles);
            }
        });

        // wpaicgchat_input_border
        $('.wpaicgchat_input_border').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox-typing').css('border-color', color); // Set the border color of the typing area
        });
        // wpaicgchat_send_color
        $('.wpaicgchat_send_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox-send').css('color', color); // Set the background color of the send button
            // .wpaicg-img-icon
            $('.wpaicg-img-icon').css('color', color);
        });
        // wpaicg_chat_widget_mic_color
        $('.wpaicg_chat_widget_mic_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-mic-icon').css('color', color); // Set the color of the mic icon
        });
        // wpaicgchat_footer_color
        $('.wpaicgchat_footer_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox-action-bar').css('background-color', color); // Set the background color of the action bar
            // .wpaicg-chatbox-footer background color
            $('.wpaicg-chatbox-footer').css('background-color', color);
            $('.wpaicg-chatbox-footer').css('border-top', '1px solid ' + color);
        });
        // wpaicgchat_footer_font_color
        $('.wpaicgchat_footer_font_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            // if $('.wpaicg-footer-note').val(); is not empty
            if($('.wpaicg-footer-note').val() !== ''){
                $('.wpaicg-chatbox-footer').css('color', color); // Set the font color of the footer
            }
        });
        // wpaicgchat_bar_color
        $('.wpaicgchat_bar_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox-action-bar').css('color', color); // Set the border color of the action bar
        });
        // wpaicgchat_thinking_color
        $('.wpaicgchat_thinking_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox .wpaicg-bot-thinking').css('color', color);
        });
        // wpaicg_chat_widget_font_size
        $('.wpaicg_chat_widget_font_size').on('input change', function () {
            var size = $(this).val(); // Get the font size value from the input
            $('.wpaicg-chatbox-messages li.wpaicg-chat-ai-message').css('font-size', size + 'px'); // Set the font size of the chat messages
            $('.wpaicg-chatbox-messages li.wpaicg-chat-user-message').css('font-size', size + 'px');
            $('.wpaicg-chatbox .wpaicg-conversation-starter').css('font-size', size + 'px');
            // wpaicg-chat-message
            $('.wpaicg-chat-message').css('font-size', size + 'px');
        });

        // wpaicg_pdf_color
        $('.wpaicg_pdf_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-pdf-icon').css('color', color); // Set the color of the PDF icon
        });

        $('input[name="chat_theme"]').on('change', function () {
            var theme = $(this).val(); // Get the selected theme value

            // Define your themes
            var themes = {
                default: {
                    fontColor: "#495057",
                    aiBgColor: "#d1e8ff",
                    userBgColor: "#ccf5e1",
                    windowBgColor: "#f8f9fa",
                    inputFontColor: "#495057",
                    borderTextField: "#CED4DA",
                    sendColor: "#d1e8ff",
                    bgTextField: "#FFFFFF",
                    footerColor: "#FFFFFF",
                    thinkingColor: "#495057",
                    footerfontColor: "#495057",
                    headericonColor: "#495057",
                    pdfColor: "#d1e8ff",
                    micColor: "#d1e8ff",
                    stopColor: "#d1e8ff",
                },
                midnightElegance: {
                    fontColor: "#E8E8E8",
                    aiBgColor: "#495057",
                    userBgColor: "#6C757D",
                    windowBgColor: "#343A40",
                    inputFontColor: "#F8F9FA",
                    borderTextField: "#6C757D",
                    sendColor: "#F8F9FA",
                    bgTextField: "#495057",
                    footerColor: "#495057",
                    thinkingColor: "#CED4DA",
                    footerfontColor: "#FFFFFF",
                    headericonColor: "#FFFFFF",
                    pdfColor: "#F8F9FA",
                    micColor: "#F8F9FA",
                    stopColor: "#F8F9FA",
                },
                sunriseSerenity: { /* Sunrise Serenity theme settings */ 
                    fontColor: "#543D35",
                    aiBgColor: "#FFD27D",
                    userBgColor: "#FFEBB7",
                    windowBgColor: "#FFF6E5",
                    inputFontColor: "#543D35",
                    borderTextField: "#FFD27D",
                    sendColor: "#FFA500",
                    bgTextField: "#FFF6E5",
                    footerColor: "#FFD27D",
                    thinkingColor: "#FFA500",
                    footerfontColor: "#543D35",
                    headericonColor: "#543D35",
                    pdfColor: "#FFA500",
                    micColor: "#FFA500",
                    stopColor: "#FFA500",
                },
                forestWhisper: {
                    fontColor: "#004225",
                    aiBgColor: "#A9D9C3",
                    userBgColor: "#CDEBDA",
                    windowBgColor: "#E6F4EA",
                    inputFontColor: "#004225",
                    borderTextField: "#A9D9C3",
                    sendColor: "#006400",
                    bgTextField: "#E6F4EA",
                    footerColor: "#A9D9C3",
                    thinkingColor: "#006400",
                    footerfontColor: "#004225",
                    headericonColor: "#004225",
                    pdfColor: "#006400",
                    micColor: "#006400",
                    stopColor: "#006400",
                },
                oceanBreeze: {
                    fontColor: "#02457A",
                    aiBgColor: "#A3D5D9",
                    userBgColor: "#CCEFF5",
                    windowBgColor: "#EAF7FA",
                    inputFontColor: "#02457A",
                    borderTextField: "#A3D5D9",
                    sendColor: "#017991",
                    bgTextField: "#EAF7FA",
                    footerColor: "#A3D5D9",
                    thinkingColor: "#017991",
                    footerfontColor: "#02457A",
                    headericonColor: "#02457A",
                    pdfColor: "#017991",
                    micColor: "#017991",
                    stopColor: "#017991",
                },
                spaceGalaxy: {
                    fontColor: "#CCCCCC", // Light grey for contrast against dark backgrounds
                    aiBgColor: "#2E2E3A", // Deep space dark
                    userBgColor: "#414152", // Darker shade for distinction
                    windowBgColor: "#31313D", // Dark space gray
                    inputFontColor: "#CCCCCC",
                    borderTextField: "#414152",
                    sendColor: "#5D5DFF", // Galaxy inspired blue
                    bgTextField: "#31313D",
                    footerColor: "#2E2E3A",
                    thinkingColor: "#5D5DFF",
                    footerfontColor: "#CCCCCC",
                    headericonColor: "#CCCCCC",
                    pdfColor: "#5D5DFF",
                    micColor: "#5D5DFF",
                    stopColor: "#5D5DFF",
                },
                desertDune: {
                    fontColor: "#4E403B", // Earthy brown
                    aiBgColor: "#F4C07A", // Sandy dune
                    userBgColor: "#FCE5C0", // Lighter sand
                    windowBgColor: "#FAE0AC", // Warm light sand
                    inputFontColor: "#4E403B",
                    borderTextField: "#F4C07A",
                    sendColor: "#D98941", // Desert sunset
                    bgTextField: "#FAE0AC",
                    footerColor: "#F4C07A",
                    thinkingColor: "#D98941",
                    footerfontColor: "#4E403B",
                    headericonColor: "#4E403B",
                    pdfColor: "#D98941",
                    micColor: "#D98941",
                    stopColor: "#D98941",
                },
                winterWonderland: {
                    fontColor: "#004C70", // Deep winter blue
                    aiBgColor: "#AED9E0", // Frosty blue
                    userBgColor: "#D0EFFF", // Light frost
                    windowBgColor: "#CCEFFF", // Very light blue
                    inputFontColor: "#004C70",
                    borderTextField: "#AED9E0",
                    sendColor: "#007BFF", // Bright winter sky blue
                    bgTextField: "#CCEFFF",
                    footerColor: "#AED9E0",
                    thinkingColor: "#007BFF",
                    footerfontColor: "#004C70",
                    headericonColor: "#004C70",
                    pdfColor: "#007BFF",
                    micColor: "#007BFF",
                    stopColor: "#007BFF",
                },
                cityscapeGlow: {
                    fontColor: "#EBEBEB", // Light grey for urban night glow
                    aiBgColor: "#555555", // Dark urban gray
                    userBgColor: "#6E6E6E", // Slightly lighter grey for distinction
                    windowBgColor: "#4D4D4D", // Dark grey representing night
                    inputFontColor: "#EBEBEB",
                    borderTextField: "#6E6E6E",
                    sendColor: "#FF9500", // Neon sign orange
                    bgTextField: "#4D4D4D",
                    footerColor: "#555555",
                    thinkingColor: "#FF9500",
                    footerfontColor: "#EBEBEB",
                    headericonColor: "#EBEBEB",
                    pdfColor: "#FF9500",
                    micColor: "#FF9500",
                    stopColor: "#FF9500",
                },
                mountainPeak: {
                    fontColor: "#3E423F", // Dark greenish-gray
                    aiBgColor: "#A8B4A5", // Mountain mist
                    userBgColor: "#CFD8CD", // Light mountain fog
                    windowBgColor: "#BCC5B9", // Pale greenish-gray, evoking rock surfaces
                    inputFontColor: "#3E423F",
                    borderTextField: "#A8B4A5",
                    sendColor: "#5A7252", // Pine green
                    bgTextField: "#BCC5B9",
                    footerColor: "#A8B4A5",
                    thinkingColor: "#5A7252",
                    footerfontColor: "#3E423F",
                    headericonColor: "#3E423F",
                    pdfColor: "#5A7252",
                    micColor: "#5A7252",
                    stopColor: "#5A7252",
                },
                glade: {
                    fontColor: "#3A6351",
                    aiBgColor: "#A7C4BC",
                    userBgColor: "#D9EAD3",
                    windowBgColor: "#E4EFE7",
                    inputFontColor: "#3A6351",
                    borderTextField: "#A7C4BC",
                    sendColor: "#81B29A",
                    bgTextField: "#E4EFE7",
                    footerColor: "#A7C4BC",
                    thinkingColor: "#81B29A",
                    footerfontColor: "#3A6351",
                    headericonColor: "#3A6351",
                    pdfColor: "#81B29A",
                    micColor: "#81B29A",
                    stopColor: "#81B29A",
                },
                dusk: {
                    fontColor: "#FFE8D6",
                    aiBgColor: "#2B2D42",
                    userBgColor: "#8D99AE",
                    windowBgColor: "#EDF2F4",
                    inputFontColor: "#FFE8D6",
                    borderTextField: "#8D99AE",
                    sendColor: "#EF233C",
                    bgTextField: "#EDF2F4",
                    footerColor: "#2B2D42",
                    thinkingColor: "#EF233C",
                    footerfontColor: "#FFE8D6",
                    headericonColor: "#FFE8D6",
                    pdfColor: "#EF233C",
                    micColor: "#EF233C",
                    stopColor: "#EF233C",
                },
                dawn: {
                    fontColor: "#563F1B",
                    aiBgColor: "#F2CC8F",
                    userBgColor: "#FAF3DD",
                    windowBgColor: "#FFF9EB",
                    inputFontColor: "#563F1B",
                    borderTextField: "#F2CC8F",
                    sendColor: "#E07A5F",
                    bgTextField: "#FFF9EB",
                    footerColor: "#F2CC8F",
                    thinkingColor: "#E07A5F",
                    footerfontColor: "#563F1B",
                    headericonColor: "#563F1B",
                    pdfColor: "#E07A5F",
                    micColor: "#E07A5F",
                    stopColor: "#E07A5F",
                },
                mist: {
                    fontColor: "#646E78",
                    aiBgColor: "#CFD9DF",
                    userBgColor: "#E2E8EE",
                    windowBgColor: "#F4F6F8",
                    inputFontColor: "#646E78",
                    borderTextField: "#CFD9DF",
                    sendColor: "#9FB3C8",
                    bgTextField: "#F4F6F8",
                    footerColor: "#CFD9DF",
                    thinkingColor: "#9FB3C8",
                    footerfontColor: "#646E78",
                    headericonColor: "#646E78",
                    pdfColor: "#9FB3C8",
                    micColor: "#9FB3C8",
                    stopColor: "#9FB3C8",
                },
                veil: {
                    fontColor: "#D1D1D1",
                    aiBgColor: "#B1A2A2",
                    userBgColor: "#EADDDC",
                    windowBgColor: "#F7F3F2",
                    inputFontColor: "#D1D1D1",
                    borderTextField: "#B1A2A2",
                    sendColor: "#C49AAB",
                    bgTextField: "#F7F3F2",
                    footerColor: "#B1A2A2",
                    thinkingColor: "#C49AAB",
                    footerfontColor: "#D1D1D1",
                    headericonColor: "#D1D1D1",
                    pdfColor: "#C49AAB",
                    micColor: "#C49AAB",
                    stopColor: "#C49AAB",
                },
                peak: {
                    fontColor: "#4A4E69",
                    aiBgColor: "#9A8C98",
                    userBgColor: "#C9ADA7",
                    windowBgColor: "#F2E9E4",
                    inputFontColor: "#4A4E69",
                    borderTextField: "#9A8C98",
                    sendColor: "#C2CAD0",
                    bgTextField: "#F2E9E4",
                    footerColor: "#9A8C98",
                    thinkingColor: "#C2CAD0",
                    footerfontColor: "#4A4E69",
                    headericonColor: "#4A4E69",
                    pdfColor: "#C2CAD0",
                    micColor: "#C2CAD0",
                    stopColor: "#C2CAD0",
                },

                vale: {
                    fontColor: "#1D3557",
                    aiBgColor: "#A8DADC",
                    userBgColor: "#F1FAEE",
                    windowBgColor: "#F8FAF9",
                    inputFontColor: "#1D3557",
                    borderTextField: "#A8DADC",
                    sendColor: "#457B9D",
                    bgTextField: "#F8FAF9",
                    footerColor: "#A8DADC",
                    thinkingColor: "#457B9D",
                    footerfontColor: "#1D3557",
                    headericonColor: "#1D3557",
                    pdfColor: "#457B9D",
                    micColor: "#457B9D",
                    stopColor: "#457B9D",
                },

                cove: {
                    fontColor: "#05668D",
                    aiBgColor: "#02C3A7",
                    userBgColor: "#00FA9A",
                    windowBgColor: "#E0F9F5",
                    inputFontColor: "#05668D",
                    borderTextField: "#02C3A7",
                    sendColor: "#028090",
                    bgTextField: "#E0F9F5",
                    footerColor: "#02C3A7",
                    thinkingColor: "#028090",
                    footerfontColor: "#05668D",
                    headericonColor: "#05668D",
                    pdfColor: "#028090",
                    micColor: "#028090",
                    stopColor: "#028090",
                },
                rift: {
                    fontColor: "#555B6E",
                    aiBgColor: "#89B0AE",
                    userBgColor: "#BEE3DB",
                    windowBgColor: "#E5E5E5",
                    inputFontColor: "#555B6E",
                    borderTextField: "#89B0AE",
                    sendColor: "#7A9E9F",
                    bgTextField: "#E5E5E5",
                    footerColor: "#89B0AE",
                    thinkingColor: "#7A9E9F",
                    footerfontColor: "#555B6E",
                    headericonColor: "#555B6E",
                    pdfColor: "#7A9E9F",
                    micColor: "#7A9E9F",
                    stopColor: "#7A9E9F",
                },

                isle: {
                    fontColor: "#3D5A80",
                    aiBgColor: "#98C1D9",
                    userBgColor: "#E0FBFC",
                    windowBgColor: "#EEF6FB",
                    inputFontColor: "#3D5A80",
                    borderTextField: "#98C1D9",
                    sendColor: "#293241",
                    bgTextField: "#EEF6FB",
                    footerColor: "#98C1D9",
                    thinkingColor: "#293241",
                    footerfontColor: "#3D5A80",
                    headericonColor: "#3D5A80",
                    pdfColor: "#293241",
                    micColor: "#293241",
                    stopColor: "#293241",
                }
            };

            if (themes[theme]) {
                var selectedTheme = themes[theme];
                // Update the color inputs
                $('.wpaicgchat_font_color').val(selectedTheme.fontColor).trigger('change');
                $('.wpaicgchat_ai_color').val(selectedTheme.aiBgColor).trigger('change');
                $('.wpaicgchat_user_color').val(selectedTheme.userBgColor).trigger('change');
                $('.wpaicgchat_bg_color').val(selectedTheme.windowBgColor).trigger('change');
                $('.wpaicgchat_input_font_color').val(selectedTheme.inputFontColor).trigger('change');
                $('.wpaicgchat_input_border').val(selectedTheme.borderTextField).trigger('change');
                $('.wpaicgchat_send_color').val(selectedTheme.sendColor).trigger('change');
                $('.wpaicgchat_input_color').val(selectedTheme.bgTextField).trigger('change');
                $('.wpaicgchat_footer_color').val(selectedTheme.footerColor).trigger('change');
                $('.wpaicgchat_thinking_color').val(selectedTheme.thinkingColor).trigger('change');
                $('.wpaicgchat_footer_font_color').val(selectedTheme.footerfontColor).trigger('change');
                $('.wpaicgchat_bar_color').val(selectedTheme.headericonColor).trigger('change');
                $('.wpaicg_pdf_color').val(selectedTheme.pdfColor).trigger('change');
                $('.wpaicg_chat_widget_mic_color').val(selectedTheme.micColor).trigger('change');
                $('.wpaicgchat_stop_color').val(selectedTheme.stopColor).trigger('change');
            }
        });
        
        function wpaicgUpdateRealtime(){
            var wpaicgWindowWidth = window.innerWidth;
            var wpaicgWindowHeight = window.innerHeight;
            let useavatar = $('.wpaicgchat_use_avatar').val();
            let width = $('.wpaicg_chat_widget_width').val();
            let height = $('.wpaicg_chat_widget_height').val();
            let wpaicg_chat_rounded = $('.wpaicg_chat_rounded').val();
            let wpaicg_text_rounded = $('.wpaicg_text_rounded').val();
            let wpaicg_text_height = $('.wpaicg_text_height').val();
            $('.wpaicg-chatbox').attr('data-height',height);
            $('.wpaicg-chatbox').attr('data-width',width);
            $('.wpaicg-chatbox').attr('data-chat_rounded',wpaicg_chat_rounded);
            $('.wpaicg-chatbox').attr('data-text_rounded',wpaicg_text_rounded);
            $('.wpaicg-chatbox').attr('data-text_height',wpaicg_text_height);

            if($('.wpaicg_chat_widget_audio').prop('checked')){
                $('.wpaicg-mic-icon').show();
            }
            else{
                $('.wpaicg-mic-icon').hide();
            }
            if($('.wpaicg_chat_widget_image').prop('checked')){
                $('.wpaicg-img-icon').show();
            }
            else{
                $('.wpaicg-img-icon').hide();
            }

            var previewboxWidth = $('.wpaicg-chatbox-preview-box').width();
            if(width.indexOf('%') < 0){
                if(width.indexOf('px') < 0){
                    width = parseFloat(width);
                }
                else{
                    width = parseFloat(width.replace(/px/g,''));
                }
            }
            else{
                width = parseFloat(width.replace(/%/g,''));
                width = width*previewboxWidth/100;
            }
            if(height.indexOf('%') < 0){
                if(height.indexOf('px') < 0){
                    height = parseFloat(height);
                }
                else{
                    height = parseFloat(height.replace(/px/g,''));
                }
            }
            else{
                height = parseFloat(height.replace(/%/g,''));
                height = height*wpaicgWindowHeight/100;
            }
            $('.wpaicg-chatbox-preview-box').height((parseInt(height)+125)+'px');
            if(width > previewboxWidth){
                width = previewboxWidth;
            }
            $('.wpaicg_chat_widget_content .wpaicg-chatbox,.wpaicg_widget_open .wpaicg_chat_widget_content').css({
                'height': height+'px',
                'width': width+'px',
            });

            wpaicgChatBoxSize();
        }
        $('.wpaicg_chat_widget_width,.wpaicg_chat_widget_height').on('input', function(){
            wpaicgUpdateRealtime();
        });
        $('.wpaicg_chat_widget_audio,.wpaicg_chat_widget_image,.wpaicgchat_use_avatar,.wpaicgchat_fullscreen,.wpaicgchat_close_btn,.wpaicgchat_download_btn,.wpaicgchat_clear_btn').click(function(){
            wpaicgUpdateRealtime();
        })

        $('.wpaicg-footer-note').on('input', function(){
            wpaicgUpdateRealtime();
        })
        $('.wpaicg_chatbox_icon').click(function (e){
            e.preventDefault();
            $('.wpaicg_chatbox_icon_default').prop('checked',false);
            $('.wpaicg_chatbox_icon_custom').prop('checked',true);
            var button = $(e.currentTarget),
                custom_uploader = wp.media({
                    title: '<?php echo esc_html__('Insert image','gpt3-ai-content-generator')?>',
                    library : {
                        type : 'image'
                    },
                    button: {
                        text: '<?php echo esc_html__('Use this image','gpt3-ai-content-generator')?>'
                    },
                    multiple: false
                }).on('select', function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    button.html('<img width="75" height="75" src="'+attachment.url+'">');
                    $('.wpaicg_chat_icon_url').val(attachment.id);
                }).open();
        });
        $('.wpaicg_chatbox_avatar').click(function (e){
            e.preventDefault();
            $('.wpaicg_chatbox_avatar_default').prop('checked',false);
            $('.wpaicg_chatbox_avatar_custom').prop('checked',true);
            var button = $(e.currentTarget),
                custom_uploader = wp.media({
                    title: '<?php echo esc_html__('Insert image','gpt3-ai-content-generator')?>',
                    library : {
                        type : 'image'
                    },
                    button: {
                        text: '<?php echo esc_html__('Use this image','gpt3-ai-content-generator')?>'
                    },
                    multiple: false
                }).on('select', function() {
                    var attachment = custom_uploader.state().get('selection').first().toJSON();
                    button.html('<img width="40" height="40" src="'+attachment.url+'">');
                    $('.wpaicg_ai_avatar_id').val(attachment.id);
                }).open();
        });

        // Function to toggle visibility based on Vector DB selection
        function toggleDBRelatedFields() {
            var selectedDB = $('#wpaicg_chat_vectordb').val();
            if (selectedDB === 'qdrant') {
                // Hide Pinecone Index div and Show Qdrant Collection div if Qdrant is selected
                $('#wpaicg_chat_embedding_index').closest('.nice-form-group').hide();
                $('#wpaicg_chat_qdrant_collection').closest('.nice-form-group').show();
            } else {
                // Show Pinecone Index div and Hide Qdrant Collection div if Pinecone or any other option is selected
                $('#wpaicg_chat_embedding_index').closest('.nice-form-group').show();
                $('#wpaicg_chat_qdrant_collection').closest('.nice-form-group').hide();
            }
        }

        // Initial check on page load
        toggleDBRelatedFields();

        // Set up event listener for changes on the Vector DB dropdown
        $('#wpaicg_chat_vectordb').change(function() {
            toggleDBRelatedFields();
        });


        $('#wpaicg_chat_excerpt').on('click', function (){
            if($(this).prop('checked')){
                $('#wpaicg_chat_excerpt').removeClass('asdisabled');
                $('#wpaicg_chat_embedding').prop('checked',false);
                $('#wpaicg_chat_embedding').addClass('asdisabled');
                $('#wpaicg_chat_embedding_type').val('openai');
                $('#wpaicg_chat_embedding_type').addClass('asdisabled');
                $('#wpaicg_chat_embedding_type').attr('disabled','disabled');
                $('#wpaicg_chat_vectordb').val('pinecone');
                $('#wpaicg_chat_vectordb').attr('disabled','disabled');
                $('#wpaicg_chat_vectordb').addClass('asdisabled');
                $('#wpaicg_chat_embedding_top').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_top').val(1);
                $('#wpaicg_chat_embedding_index').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_index').addClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_pdf').addClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf_message').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_pdf_message').addClass('asdisabled');
                $('#wpaicg_chat_pdf_pages').attr('disabled','disabled');
                $('#wpaicg_chat_pdf_pages').addClass('asdisabled');
                $('#wpaicg_chat_qdrant_collection').attr('disabled','disabled');
                $('#wpaicg_chat_qdrant_collection').addClass('asdisabled');
            }
            else{
                $(this).prop('checked',true);
            }
        });
        $('#wpaicg_chat_addition').on('click', function (){
            if($(this).prop('checked')){
                $('#wpaicg_chat_addition_text').removeAttr('disabled');
                $('.wpaicg_chat_addition_template').removeAttr('disabled');
            }
            else{
                $('#wpaicg_chat_addition_text').attr('disabled','disabled');
                $('.wpaicg_chat_addition_template').attr('disabled','disabled');
            }
        });
        $(document).on('change', '.wpaicg_chat_addition_template',function (e){
            var addition_text_template = $(e.currentTarget).val();
            if(addition_text_template !== ''){
                $('.wpaicg_chat_addition_text').val(addition_text_template);
            }
        });
        $('#wpaicg_chat_embedding').on('click', function (){
            if($(this).prop('checked')){
                $('#wpaicg_chat_excerpt').prop('checked',false);
                $('#wpaicg_chat_excerpt').addClass('asdisabled');
                $('#wpaicg_chat_embedding').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_type').val('openai');
                $('#wpaicg_chat_embedding_type').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_type').removeAttr('disabled');
                $('#wpaicg_chat_vectordb').val('pinecone');
                $('#wpaicg_chat_vectordb').removeAttr('disabled');
                $('#wpaicg_chat_vectordb').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_top').val(1);
                $('#wpaicg_chat_embedding_top').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_top').removeAttr('disabled');
                $('#wpaicg_chat_embedding_index').removeAttr('disabled');
                $('#wpaicg_chat_embedding_index').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf').removeAttr('disabled');
                $('#wpaicg_chat_embedding_pdf').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf_message').removeAttr('disabled');
                $('#wpaicg_chat_embedding_pdf_message').removeClass('asdisabled');
                $('#wpaicg_chat_pdf_pages').removeAttr('disabled');
                $('#wpaicg_chat_pdf_pages').removeClass('asdisabled');
                $('#wpaicg_chat_qdrant_collection').removeAttr('disabled');
                $('#wpaicg_chat_qdrant_collection').removeClass('asdisabled');
            }
            else{
                $(this).prop('checked',true);
            }
        });

        $('#wpaicg_chat_content_aware').on('change', function (){
            // Determine if the checkbox is checked
            var isContentAwareYes = $(this).prop('checked');
            // Set value manually for simulation of select behavior
            $(this).val(isContentAwareYes ? 'yes' : 'no');

            if(isContentAwareYes){
                $('#wpaicg_chat_excerpt').removeAttr('disabled');
                $('#wpaicg_chat_excerpt').prop('checked',true);
                $('#wpaicg_chat_embedding').removeAttr('disabled');
                $('#wpaicg_chat_embedding_type').removeAttr('disabled');
                $('#wpaicg_chat_embedding').addClass('asdisabled');
                $('#wpaicg_chat_embedding_type').val('openai');
                $('#wpaicg_chat_embedding_type').addClass('asdisabled');
                $('#wpaicg_chat_vectordb').val('pinecone');
                $('#wpaicg_chat_vectordb').addClass('asdisabled');
                $('#wpaicg_chat_vectordb').removeAttr('disabled');
                $('#wpaicg_chat_embedding_top').val(1);
                $('#wpaicg_chat_embedding_top').addClass('asdisabled');
                $('#wpaicg_chat_embedding_index').removeAttr('disabled');
                $('#wpaicg_chat_embedding_index').addClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf').removeAttr('disabled');
                $('#wpaicg_chat_embedding_pdf').addClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf_message').removeAttr('disabled');
                $('#wpaicg_chat_embedding_pdf_message').addClass('asdisabled');
                $('#wpaicg_chat_pdf_pages').removeAttr('disabled');
                $('#wpaicg_chat_pdf_pages').addClass('asdisabled');
                $('#wpaicg_chat_qdrant_collection').removeAttr('disabled');
                $('#wpaicg_chat_qdrant_collection').addClass('asdisabled');
                // Add call to update the DB Provider field
                toggleDBRelatedFields();
            }
            else{
                $('#wpaicg_chat_embedding_type').removeClass('asdisabled');
                $('#wpaicg_chat_vectordb').removeClass('asdisabled');
                $('#wpaicg_chat_excerpt').removeClass('asdisabled');
                $('#wpaicg_chat_embedding').removeClass('asdisabled');
                $('#wpaicg_chat_excerpt').prop('checked',false);
                $('#wpaicg_chat_embedding').prop('checked',false);
                $('#wpaicg_chat_excerpt').attr('disabled','disabled');
                $('#wpaicg_chat_embedding').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_type').attr('disabled','disabled');
                $('#wpaicg_chat_vectordb').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_top').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_top').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_index').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_index').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_pdf').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf_message').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_pdf_message').removeClass('asdisabled');
                $('#wpaicg_chat_pdf_pages').attr('disabled','disabled');
                $('#wpaicg_chat_pdf_pages').removeClass('asdisabled');
                $('#wpaicg_chat_qdrant_collection').attr('disabled','disabled');
                $('#wpaicg_chat_qdrant_collection').removeClass('asdisabled');
            }
            toggleDBRelatedFields();
        })

        function updateImageUploadOption() {
            var provider = $('#wpaicg_widget_provider').val();
            var model = '';

            if (provider === 'OpenAI') {
                model = $('#wpaicg_chat_model').val();
            } else if (provider === 'Google') {
                model = $('#wpaicg_widget_google_model').val();
            } else if (provider === 'Azure') {
                model = $('#wpaicg_azure_model').val(); // Assuming deployment name is used as the model identifier
            } else if (provider === 'OpenRouter') {
                model = $('#wpaicg_openrouter_model').val();
            }

            console.log('Selected Provider:', provider); // Debugging line
            console.log('Selected Model:', model); // Debugging line

            var $imageUploadCheckbox = $('.wpaicg_chat_widget_image'); // Update this selector as needed
            var $streamingCheckbox = $('input[name="wpaicg_stream_nav_option"]');

            if (provider === 'OpenAI') {
                if (model === 'gpt-4-vision-preview' || model === 'gpt-4o') {
                    $imageUploadCheckbox.prop('disabled', false); // Enable the image upload checkbox
                } else {
                    $imageUploadCheckbox.prop('disabled', true).prop('checked', false); // Disable and uncheck the image upload checkbox
                }
                $streamingCheckbox.prop('disabled', false); // Enable the streaming checkbox
            } else if (provider === 'OpenRouter') {
                if (model === 'openai/gpt-4-vision-preview' || model === 'openai/gpt-4o' || model === 'openai/gpt-4o-2024-05-13') {
                    $imageUploadCheckbox.prop('disabled', false); // Enable the image upload checkbox
                } else {
                    $imageUploadCheckbox.prop('disabled', true).prop('checked', false); // Disable and uncheck the image upload checkbox
                }
                $streamingCheckbox.prop('disabled', false); // Enable the streaming checkbox
            } else {
                $imageUploadCheckbox.prop('disabled', true).prop('checked', false); // Disable and uncheck the image upload checkbox
                $streamingCheckbox.prop('disabled', true).prop('checked', false); // Disable and uncheck the streaming checkbox
            }
        }

        // Check and update the option on page load
        updateImageUploadOption();

        // Re-check and update whenever the provider or model selection changes
        $('#wpaicg_widget_provider, #wpaicg_chat_model, #wpaicg_widget_google_model, #wpaicg_openrouter_model, #wpaicg_azure_model').change(function () {
            updateImageUploadOption();
        });

        // Listen for changes on the "Enable Image Upload" checkbox
        $('.wpaicg_chat_widget_image').on('change', function() {
            var isImageUploadEnabled = $(this).is(':checked');
            var $streamingCheckbox = $('input[name="wpaicg_stream_nav_option"]');

            if (isImageUploadEnabled) {
                $streamingCheckbox.prop('disabled', true).prop('checked', false);
            } else {
                $streamingCheckbox.prop('disabled', false);
            }
        });

        // Trigger the change event on page load in case the "Enable Image Upload" checkbox is already checked
        $('.wpaicg_chat_widget_image').trigger('change');

        // Function to disable the streaming option if the provider is Google
        function disableStreamingForGoogle() {
            if ($('#wpaicg_widget_provider').val() === 'Google') {
                var $streamingCheckbox = $('input[name="wpaicg_stream_nav_option"]');
                $streamingCheckbox.prop('disabled', true).prop('checked', false);
            }
        }

        // Call the function on page load
        disableStreamingForGoogle();

        // Re-check and update whenever the provider selection changes
        $('#wpaicg_widget_provider').change(function () {
            disableStreamingForGoogle();
        });

        // Function to manage speech options based on streaming status
        function manageSpeechOptionsBasedOnStreaming() {
            var isStreamingChecked = $('input[name="wpaicg_stream_nav_option"]').is(':checked');

            var $speechToTextCheckbox = $('.wpaicg_chat_widget_audio');
            var $textToSpeechCheckbox = $('.wpaicg_chat_to_speech');

            if (isStreamingChecked) {
                $speechToTextCheckbox.prop('disabled', true).prop('checked', false);
                $textToSpeechCheckbox.prop('disabled', true).prop('checked', false);
            } else {
                if ($('#wpaicg_widget_provider').val() === 'OpenAI') {
                    $speechToTextCheckbox.prop('disabled', false);
                }
                $textToSpeechCheckbox.prop('disabled', false);
            }
        }

        // Call the function on page load to apply the correct initial state
        manageSpeechOptionsBasedOnStreaming();

        // Re-check and update whenever the streaming option changes
        $('input[name="wpaicg_stream_nav_option"]').on('change', function() {
            manageSpeechOptionsBasedOnStreaming();
        });


    })
</script>
<script>
    document.getElementById('wpaicg-advanced-settings-link').addEventListener('click', function(e) {
        e.preventDefault();
        var wpaicgadvancedSettings = document.getElementById('wpaicg-advanced-settings');
        var wpaicgadvancedSettingsLink = document.getElementById('wpaicg-advanced-settings-link');
        if (wpaicgadvancedSettings.style.display === 'none') {
            wpaicgadvancedSettings.style.display = 'block';
            wpaicgadvancedSettingsLink.textContent = '<?php echo esc_js(esc_html__('Hide Advanced Parameters','gpt3-ai-content-generator')); ?>';
        } else {
            wpaicgadvancedSettings.style.display = 'none';
            wpaicgadvancedSettingsLink.textContent = '<?php echo esc_js(esc_html__('Show Advanced Parameters','gpt3-ai-content-generator')); ?>';
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const tabs = document.querySelectorAll('.demo-page-navigation a[data-tab]');
  tabs.forEach(tab => {
    tab.addEventListener('click', function(e) {
      const targetId = this.getAttribute('data-tab');
      const sections = document.querySelectorAll('.tab-content');
      sections.forEach(section => {
        if (section.id === targetId) {
          section.style.display = '';
        } else {
          section.style.display = 'none';
        }
      });
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
    });
  });
});
</script>
<script>
    document.getElementById('wpaicg-advanced-content-settings-link').addEventListener('click', function(e) {
        e.preventDefault();
        var wpaicgadvancedContentSettings = document.getElementById('wpaicg-advanced-content-settings');
        var wpaicgadvancedContentSettingsLink = document.getElementById('wpaicg-advanced-content-settings-link');
        if (wpaicgadvancedContentSettings.style.display === 'none') {
            wpaicgadvancedContentSettings.style.display = 'block';
            wpaicgadvancedContentSettingsLink.textContent = '<?php echo esc_js(esc_html__('Hide Additional Options','gpt3-ai-content-generator')); ?>';
        } else {
            wpaicgadvancedContentSettings.style.display = 'none';
            wpaicgadvancedContentSettingsLink.textContent = '<?php echo esc_js(esc_html__('Show Additional Options','gpt3-ai-content-generator')); ?>';
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('wpaicg_conversation_starters_widget_container');
        // Initialize counter based on the number of input fields already present
        let counter = container.querySelectorAll('.nice-form-group').length;

        window.handleInputWidget = function(event) {
            const inputs = container.querySelectorAll('.nice-form-group input');
            const lastInput = inputs[inputs.length - 1];
            // Adjust the condition to check for the actual last input
            if (event.target === lastInput && lastInput.value.length > 0 && counter < 10) {
                addNewStarterInput();
            }
        };

        function addNewStarterInput() {
            const wrapperDiv = document.createElement('div');
            wrapperDiv.classList.add('nice-form-group');
            wrapperDiv.style.marginTop = "10px"; // Add spacing between inputs

            // Add the input and button, making sure to include the delete button except for the first input
            const inputHTML = `<input type="text" name="wpaicg_conversation_starters_widget[]" oninput="handleInputWidget(event)">`;
            const deleteButtonHTML = counter > 0 ? `<button type="button" class="wpaicg-delete-starter" onclick="removeStarter(this)">X</button>` : ``;

            wrapperDiv.innerHTML = inputHTML + deleteButtonHTML;
            container.appendChild(wrapperDiv);
            counter++;
        }

        window.removeStarter = function(button) {
            button.closest('.nice-form-group').remove();
            counter--;
            // Add a new input if all inputs are deleted, keeping at least one input present
            if (container.querySelectorAll('.nice-form-group').length === 0) {
                addNewStarterInput();
            }
        };
    });
</script>
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {
        var checkbox = document.getElementById('wpaicg_use_default_embedding');
        var selectDiv = document.getElementById('wpaicg_embedding_model_div');

        // Function to toggle the display of the select div
        function toggleEmbeddingDropdown() {
            selectDiv.style.display = checkbox.checked ? 'none' : 'block';
        }

        // Event listener for checkbox changes
        checkbox.addEventListener('change', toggleEmbeddingDropdown);

        // Initial check to set the correct display state
        toggleEmbeddingDropdown();
    });

    function updateProvider() {
        var select = document.getElementById('wpaicg_embedding_model');
        var providerInput = document.getElementById('wpaicg_embedding_provider');
        var selectedOption = select.options[select.selectedIndex];
        providerInput.value = selectedOption.getAttribute('data-provider'); // Set the provider to the hidden input
    }
    window.onload = updateProvider; // Update on load to set initial provider
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const providerSelect = document.getElementById('wpaicg_widget_provider');
        const openaiModels = document.querySelector('.openai-models');
        const googleModels = document.querySelector('.google-models');
        const azureModels = document.querySelector('.azure-models');
        const openrouterModels = document.querySelector('.openrouter-models');

        function toggleModelVisibility() {
            const selectedProvider = providerSelect.value;

            openaiModels.style.display = (selectedProvider === 'OpenAI') ? 'block' : 'none';
            googleModels.style.display = (selectedProvider === 'Google') ? 'block' : 'none';
            azureModels.style.display = (selectedProvider === 'Azure') ? 'block' : 'none';
            openrouterModels.style.display = (selectedProvider === 'OpenRouter') ? 'block' : 'none';
        }

        providerSelect.addEventListener('change', toggleModelVisibility);

        // Initial toggle based on the preselected provider
        toggleModelVisibility();
    });
</script>
