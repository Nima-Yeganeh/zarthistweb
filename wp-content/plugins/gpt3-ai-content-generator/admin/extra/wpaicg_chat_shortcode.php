<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
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
<?php
global  $wpdb ;
$table = $wpdb->prefix . 'wpaicg';
$wpaicg_save_setting_success = false;

if (isset($_POST['wpaicg_conversation_starters'])) {
    $starters = $_POST['wpaicg_conversation_starters'];
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
    update_option('wpaicg_conversation_starters', json_encode($sanitized_starters));
}

if(isset($_POST['wpaicg_chat_shortcode_options']) && is_array($_POST['wpaicg_chat_shortcode_options'])){
    check_admin_referer('wpaicg_chat_shortcode_save');
    $posted_options = stripslashes_deep($_POST['wpaicg_chat_shortcode_options']);
    // Explicitly check for 'send_button_enabled' and set it based on whether the checkbox was checked
    $posted_options['send_button_enabled'] = isset($_POST['wpaicg_chat_shortcode_options']['send_button_enabled']) ? 1 : 0; // 1 if checked, 0 if not
    // Explicitly check for 'chat_addition' and set it based on whether the checkbox was checked
    $posted_options['chat_addition'] = isset($_POST['wpaicg_chat_shortcode_options']['chat_addition']) ? 1 : 0;

    // Handle the checkbox for 'Use Default Embedding Model'
    $posted_options['use_default_embedding'] = isset($_POST['wpaicg_chat_shortcode_options']['use_default_embedding']) && $_POST['wpaicg_chat_shortcode_options']['use_default_embedding'] === 'true';
    // Check if 'Use Default Embedding Model' is set to true
    if ($posted_options['use_default_embedding']) {
        // If true, remove embedding model and provider from the options and the database
        unset($posted_options['embedding_model']);
        unset($posted_options['embedding_provider']);
    } else {
        // Otherwise, sanitize and save the embedding model and provider
        if (isset($posted_options['embedding_model'])) {
            $posted_options['embedding_model'] = sanitize_text_field($posted_options['embedding_model']);
        }
        if (isset($posted_options['embedding_provider'])) {
            $posted_options['embedding_provider'] = sanitize_text_field($posted_options['embedding_provider']);
        }
    }

    // Determine the AI provider and set the model accordingly
    $provider = isset($posted_options['provider']) ? $posted_options['provider'] : get_option('wpaicg_provider', 'OpenAI');
    if ($provider === 'Google') {
        $posted_options['model'] = get_option('wpaicg_shortcode_google_model', 'gemini-pro');
    } elseif ($provider === 'Azure') {
        $posted_options['model'] = get_option('wpaicg_azure_deployment', '');
    } else {
        // Keep the model as is for OpenAI
        if (isset($posted_options['model'])) {
            $posted_options['model'] = sanitize_text_field($posted_options['model']);
        }
    }

    // Save and sanitize options as necessary
    $wpaicg_chat_shortcode_options = \WPAICG\WPAICG_Util::get_instance()->sanitize_text_or_array_field($posted_options);
    update_option('wpaicg_chat_shortcode_options', $wpaicg_chat_shortcode_options);

    // Extract and sanitize the 'limited_message' field separately
    $limited_message = isset($posted_options['limited_message']) ? wp_kses_post($posted_options['limited_message']) : '';

    // Remove 'limited_message' from the array for general sanitization
    unset($posted_options['limited_message']);

    // extract and sanitize the 'footer_text' field separately
    $footer_text = isset($posted_options['footer_text']) ? wp_kses_post($posted_options['footer_text']) : '';

    // Remove 'footer_text' from the array for general sanitization
    unset($posted_options['footer_text']);

    $wpaicg_chat_shortcode_options = \WPAICG\wpaicg_util_core()->sanitize_text_or_array_field($posted_options);
    
    // Merge the separately sanitized 'limited_message' field back into the options
    $wpaicg_chat_shortcode_options['limited_message'] = $limited_message;

    // Merge the separately sanitized 'footer_text' field back into the options
    $wpaicg_chat_shortcode_options['footer_text'] = $footer_text;

    update_option('wpaicg_chat_shortcode_options',$wpaicg_chat_shortcode_options);

    $stream_nav_option = isset($_POST['wpaicg_stream_nav_option']) ? '1' : '0';
    update_option('wpaicg_shortcode_stream', $stream_nav_option);

    // Retrieve and sanitize the 'vectordb' option
    $vectordb = isset($posted_options['vectordb']) ? sanitize_text_field($posted_options['vectordb']) : 'pinecone';
    $wpaicg_chat_shortcode_options['vectordb'] = $vectordb;
    
    $wpaicg_save_setting_success = 'Setting saved successfully';
}
if(isset($_POST['wpaicg_azure_deployment_input'])){
    $new_deployment_name = sanitize_text_field($_POST['wpaicg_azure_deployment_input']);
    update_option('wpaicg_azure_deployment', $new_deployment_name);
}
if(isset($_POST['wpaicg_shortcode_google_model'])){
    $wpaicg_shortcode_google_model = sanitize_text_field($_POST['wpaicg_shortcode_google_model']);
    update_option('wpaicg_shortcode_google_model', $wpaicg_shortcode_google_model);
}

$existingValue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE name = %s", 'wpaicg_settings' ), ARRAY_A );
$wpaicg_chat_shortcode_options = get_option('wpaicg_chat_shortcode_options',[]);
$wpaicg_provider = isset($wpaicg_chat_shortcode_options['provider']) && !empty($wpaicg_chat_shortcode_options['provider'])
    ? $wpaicg_chat_shortcode_options['provider']
    : get_option('wpaicg_provider', 'OpenAI');

$wpaicg_stream_nav_setting = get_option('wpaicg_shortcode_stream', '0'); // Default to '1' if not set
$use_default_embedding = isset($wpaicg_chat_shortcode_options['use_default_embedding']) ? $wpaicg_chat_shortcode_options['use_default_embedding'] : 'yes';

$default_setting = array(
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
    'ai_name' => 'AI',
    'you' => __('You','gpt3-ai-content-generator'),
    'ai_thinking' => __('Gathering thoughts','gpt3-ai-content-generator'),
    'placeholder' => __('Type a message','gpt3-ai-content-generator'),
    'welcome' => __('Hello, I am an AI bot. Ask me anything!','gpt3-ai-content-generator'),
    'remember_conversation' => 'yes',
    'conversation_cut' => 10,
    'content_aware' => 'yes',
    'embedding' =>  false,
    'embedding_type' =>  false,
    'embedding_index' =>  '',
    'embedding_pdf' => false,
    'embedding_pdf_message' => "Congrats! Your PDF is uploaded now! You can ask questions about your document.\nExample Questions:[questions]",
    'embedding_top' =>  false,
    'use_default_embedding' => $use_default_embedding,
    'embedding_model' =>  '',
    'no_answer' => '',
    'fontsize' => 14,
    'fontcolor' => '#495057',
    'user_bg_color' => '#ccf5e1',
    'ai_bg_color' => '#d1e8ff',
    'ai_icon_url' => '',
    'ai_icon' => 'default',
    'use_avatar' => false,
    'width' => '100%',
    'height' => '50%',
    'save_logs' => false,
    'log_notice' => false,
    'log_notice_message' => __('Please note that your conversations will be recorded.','gpt3-ai-content-generator'),
    'bgcolor' => '#f8f9fa',
    'bg_text_field' => '#ffffff',
    'send_color' => '#d1e8ff',
    // Default to true if not set, ensuring checkbox reflects enabled state by default
    'send_button_enabled' => array_key_exists('send_button_enabled', $wpaicg_chat_shortcode_options) ? $wpaicg_chat_shortcode_options['send_button_enabled'] : true,
    'border_text_field' => '#ced4da',
    'footer_text' => '',
    'footer_color' => '#ffffff',
    'footer_font_color' => '#495057',
    'input_font_color' => '#495057',
    'chat_addition' => array_key_exists('chat_addition', $wpaicg_chat_shortcode_options) ? $wpaicg_chat_shortcode_options['chat_addition'] : true,
    'chat_addition_option' => 1,
    'chat_addition_text' => '',
    'audio_enable' => false,
    'image_enable' => false,
    'mic_color' => '#d1e8ff',
    'pdf_color' => '#d1e8ff',
    'stop_color' => '#d1e8ff',
    'user_aware' => 'no',
    'user_limited' => false,
    'guest_limited' => false,
    'user_tokens' => 0,
    'guest_tokens' => 0,
    'reset_limit' => 0,
    'limited_message' => __('You have reached your token limit.','gpt3-ai-content-generator'),
    'moderation' => false,
    'moderation_model' => 'text-moderation-latest',
    'moderation_notice' => __('Your message has been flagged as potentially harmful or inappropriate. Please ensure that your messages are respectful and do not contain language or content that could be offensive or harmful to others. Thank you for your cooperation.','gpt3-ai-content-generator'),
    'role_limited' => false,
    'limited_roles' => [],
    'log_request' => false,
    'fullscreen' => false,
    'download_btn' => false,
    'clear_btn' => false,
    'bar_color' => '#495057',
    'thinking_color' => '#495057',
    'chat_to_speech' => false,
    'elevenlabs_voice' => '',
    'elevenlabs_model' => '',
    'text_height' => 60,
    'text_rounded' => 8,
    'chat_rounded' => 8,
    'pdf_pages' => 120,
    'voice_language' => 'en-US',
    'voice_name' => 'en-US-Studio-M',
    'voice_device' => '',
    'voice_speed' => 1,
    'voice_pitch' => 0,
    'voice_service' => '',
    'openai_model' => 'tts-1',
    'openai_voice' => 'alloy',
    'openai_output_format' => 'mp3',
    'openai_voice_speed' => '1.0',
    'vectordb' => 'pinecone',
    'qdrant_collection' => '',
);
$wpaicg_pinecone_api = get_option('wpaicg_pinecone_api','');
$wpaicg_pinecone_environment = get_option('wpaicg_pinecone_environment','');
$wpaicg_settings = shortcode_atts($default_setting, $wpaicg_chat_shortcode_options);
if (!isset($wpaicg_settings['vectordb'])) {
    $wpaicg_settings['vectordb'] = 'pinecone'; // Set default to Pinecone
}
$wpaicg_custom_models = get_option('wpaicg_custom_models',array());

$wpaicg_save_logs = isset($wpaicg_settings['save_logs']) && !empty($wpaicg_settings['save_logs']) ? $wpaicg_settings['save_logs'] : false;
$wpaicg_log_notice = isset($wpaicg_settings['log_notice']) && !empty($wpaicg_settings['log_notice']) ? $wpaicg_settings['log_notice'] : false;
$wpaicg_log_notice_message = isset($wpaicg_settings['log_notice_message']) && !empty($wpaicg_settings['log_notice_message']) ? $wpaicg_settings['log_notice_message'] : __('Please note that your conversations will be recorded.','gpt3-ai-content-generator');
$wpaicg_user_limited = isset($wpaicg_settings['user_limited']) ? $wpaicg_settings['user_limited'] : false;
$wpaicg_guest_limited = isset($wpaicg_settings['guest_limited']) ? $wpaicg_settings['guest_limited'] : false;
$wpaicg_user_tokens = isset($wpaicg_settings['user_tokens']) ? $wpaicg_settings['user_tokens'] : 0;
$wpaicg_guest_tokens = isset($wpaicg_settings['guest_tokens']) ? $wpaicg_settings['guest_tokens'] : 0;
$wpaicg_reset_limit = isset($wpaicg_settings['reset_limit']) ? $wpaicg_settings['reset_limit'] : 0;
$wpaicg_limited_message = isset($wpaicg_settings['limited_message']) && !empty($wpaicg_settings['limited_message']) ? $wpaicg_settings['limited_message'] : __('You have reached your token limit.','gpt3-ai-content-generator');
$wpaicg_chat_fullscreen = isset($wpaicg_settings['fullscreen']) && !empty($wpaicg_settings['fullscreen']) ? $wpaicg_settings['fullscreen'] : false;
$wpaicg_chat_download_btn = isset($wpaicg_settings['download_btn']) && !empty($wpaicg_settings['download_btn']) ? $wpaicg_settings['download_btn'] : false;
$wpaicg_chat_clear_btn = isset($wpaicg_settings['clear_btn']) && !empty($wpaicg_settings['clear_btn']) ? $wpaicg_settings['clear_btn'] : false;
$wpaicg_bar_color = isset($wpaicg_settings['bar_color']) && !empty($wpaicg_settings['bar_color']) ? $wpaicg_settings['bar_color'] : '#495057';
$wpaicg_thinking_color = isset($wpaicg_settings['thinking_color']) && !empty($wpaicg_settings['thinking_color']) ? $wpaicg_settings['thinking_color'] : '#495057';
$wpaicg_footer_color = isset($wpaicg_settings['footer_color']) && !empty($wpaicg_settings['footer_color']) ? $wpaicg_settings['footer_color'] : '#ffffff';
$wpaicg_chat_to_speech = isset($wpaicg_settings['chat_to_speech']) ? $wpaicg_settings['chat_to_speech'] : false;
$wpaicg_elevenlabs_voice = isset($wpaicg_settings['elevenlabs_voice']) ? $wpaicg_settings['elevenlabs_voice'] : '';
$wpaicg_elevenlabs_model = isset($wpaicg_settings['elevenlabs_model']) ? $wpaicg_settings['elevenlabs_model'] : '';
$wpaicg_elevenlabs_api = get_option('wpaicg_elevenlabs_api', '');
$wpaicg_chat_voice_service = isset($wpaicg_settings['voice_service']) ? $wpaicg_settings['voice_service'] : '';
$wpaicg_google_voices = get_option('wpaicg_google_voices',[]);
$wpaicg_google_api_key = get_option('wpaicg_google_api_key', '');
$wpaicg_roles = wp_roles()->get_names();
$wpaicg_pinecone_indexes = get_option('wpaicg_pinecone_indexes','');
$wpaicg_pinecone_indexes = empty($wpaicg_pinecone_indexes) ? array() : json_decode($wpaicg_pinecone_indexes,true);
$wpaicg_qdrant_collections = get_option('wpaicg_qdrant_collections',[]);
$wpaicg_qdrant_collections = empty($wpaicg_qdrant_collections) ? array() : $wpaicg_qdrant_collections;
$wpaicg_conversation_starters = get_option('wpaicg_conversation_starters', '');
$wpaicg_conversation_starters = empty($wpaicg_conversation_starters) ? array() : json_decode($wpaicg_conversation_starters, true);

?>

<?php if($wpaicg_save_setting_success): ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html($wpaicg_save_setting_success);?></p>
    </div>
<?php endif; ?>
<?php
// Define the options outside of the HTML structure for better readability and maintainability
$tone_options = \WPAICG\WPAICG_Util::get_instance()->chat_tone_options;

// Use the selected tone from settings, default to null if not set
$selected_tone = isset($wpaicg_settings['tone']) ? $wpaicg_settings['tone'] : null;

$profession_options = \WPAICG\WPAICG_Util::get_instance()->chat_profession_options;

$selected_profession = isset($wpaicg_settings['profession']) ? $wpaicg_settings['profession'] : 'none';

$language_options = \WPAICG\WPAICG_Util::get_instance()->chat_language_options;

$selected_language = isset($wpaicg_settings['language']) ? $wpaicg_settings['language'] : 'en';
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
            <?php wp_nonce_field('wpaicg_chat_shortcode_save'); ?>
            <!-- AI SETTINGS -->
            <section id="aisettings" class="tab-content">
                <h3 style="margin-top: -1em;"><?php echo esc_html__('General Settings','gpt3-ai-content-generator')?></h3>
                <p><div class="toggle-shortcode">[wpaicg_chatgpt]</div></p>
                <!-- AI Provider -->
                <div class="nice-form-group">
                    <label><?php echo esc_html__('AI Provider', 'gpt3-ai-content-generator'); ?></label>
                    <select id="wpaicg_provider" name="wpaicg_chat_shortcode_options[provider]">
                        <option value="OpenAI" <?php selected($wpaicg_provider, 'OpenAI'); ?>><?php echo esc_html__('OpenAI', 'gpt3-ai-content-generator'); ?></option>
                        <option value="Google" <?php selected($wpaicg_provider, 'Google'); ?>><?php echo esc_html__('Google', 'gpt3-ai-content-generator'); ?></option>
                        <option value="OpenRouter" <?php selected($wpaicg_provider, 'OpenRouter'); ?>><?php echo esc_html__('OpenRouter', 'gpt3-ai-content-generator'); ?></option>
                        <option value="Azure" <?php selected($wpaicg_provider, 'Azure'); ?>><?php echo esc_html__('Microsoft', 'gpt3-ai-content-generator'); ?></option>
                    </select>
                </div>
                <!-- Model -->
                <div class="openai-models">
                    <?php
                    $util_instance = \WPAICG\WPAICG_Util::get_instance();
                    $gpt4_models = $util_instance->openai_gpt4_models;
                    $gpt35_models = $util_instance->openai_gpt35_models;
                    $custom_models = get_option('wpaicg_custom_models', []);

                    function display_options($models, $selected_model) {
                        foreach ($models as $model_key => $model_name) {
                            echo '<option value="' . esc_attr($model_key) . '"' . selected($model_key, $selected_model, false) . '>' . esc_html($model_name) . '</option>';
                        }
                    }

                    function display_custom_model_options($custom_models, $selected_model) {
                        foreach ($custom_models as $model_identifier) {
                            echo '<option value="' . esc_attr($model_identifier) . '"' . selected($model_identifier, $selected_model, false) . '>' . esc_html($model_identifier) . '</option>';
                        }
                    }
                    ?>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                        <div class="nice-form-group" style="display: flex; align-items: center;">
                            <select id="wpaicg_chat_model" name="wpaicg_chat_shortcode_options[model]" style="width: 211.75px;margin-right: 5px;">
                                <optgroup label="GPT-4">
                                    <?php display_options($gpt4_models, $wpaicg_settings['model']); ?>
                                </optgroup>
                                <optgroup label="GPT-3.5">
                                    <?php display_options($gpt35_models, $wpaicg_settings['model']); ?>
                                </optgroup>
                                <optgroup label="Custom Models">
                                    <?php display_custom_model_options($custom_models, $wpaicg_settings['model']); ?>
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
                        <label><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                        <div class="nice-form-group" style="display: flex; align-items: center;">
                            <select id="wpaicg_shortcode_google_model" name="wpaicg_shortcode_google_model" style="width: 211.75px;margin-right: 5px;">
                                <?php $wpaicg_shortcode_google_model = get_option('wpaicg_shortcode_google_model', 'gemini-pro'); ?>
                                <?php foreach ($google_models as $model_key => $model_name): 
                                    $is_vision_model = strpos(strtolower($model_name), 'vision') !== false;
                                    ?>
                                    <option value="<?php echo esc_attr($model_key); ?>"<?php if ($is_vision_model) echo ' disabled'; ?><?php selected($model_key, $wpaicg_shortcode_google_model); ?>><?php echo esc_html($model_name); ?></option>
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
                        <label><?php echo esc_html__('Deployment', 'gpt3-ai-content-generator'); ?></label>
                        <input type="text" name="wpaicg_azure_deployment_input" placeholder="<?php echo esc_html__('Enter Azure Deployment', 'gpt3-ai-content-generator'); ?>" value="<?php echo esc_attr($deployment_name); ?>">
                    </div>
                </div>
                <div class="openrouter-models" style="display: none;">
                    <?php
                    $openrouter_models = get_option('wpaicg_openrouter_model_list', []);
                    // Group the models by provider
                    $openrouter_grouped_models = [];
                    foreach ($openrouter_models as $openrouter_model) {
                        $openrouter_provider = explode('/', $openrouter_model['id'])[0]; // Extract the provider name
                        if (!isset($openrouter_grouped_models[$openrouter_provider])) {
                            $openrouter_grouped_models[$openrouter_provider] = [];
                        }
                        $openrouter_grouped_models[$openrouter_provider][] = $openrouter_model;
                    }

                    // Sort the providers alphabetically
                    ksort($openrouter_grouped_models);

                    // Get the selected model and provider from the options
                    $openrouter_chat_options = get_option('wpaicg_chat_shortcode_options', []);
                    $openrouter_selected_provider = isset($openrouter_chat_options['provider']) && $openrouter_chat_options['provider'] === 'OpenRouter' ? $openrouter_chat_options['provider'] : '';
                    $openrouter_selected_model = $openrouter_selected_provider === 'OpenRouter' && isset($openrouter_chat_options['model']) ? $openrouter_chat_options['model'] : '';

                    // Default model to 'openrouter/auto' if no model is selected
                    if (empty($openrouter_selected_model)) {
                        $openrouter_selected_model = 'openrouter/auto';
                    }
                    ?>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                        <div class="nice-form-group" style="display: flex; align-items: center;">
                            <select id="wpaicg_openrouter_model" name="wpaicg_chat_shortcode_options[model]" style="width: 211.75px;margin-right: 5px;">
                                <?php
                                // Display models grouped by provider
                                foreach ($openrouter_grouped_models as $openrouter_provider => $openrouter_models) {
                                    echo '<optgroup label="' . esc_attr($openrouter_provider) . '">';
                                    // Sort the models alphabetically by name within each provider
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
                        <input <?php echo isset($wpaicg_settings['image_enable']) && $wpaicg_settings['image_enable'] ? ' checked': ''?> value="1" type="checkbox" class="wpaicg_image_enable" name="wpaicg_chat_shortcode_options[image_enable]" id="wpaicg_image_enable">
                        <label for="wpaicg_image_enable"><?php echo esc_html__('Image Upload','gpt3-ai-content-generator')?></label>
                    </div>
                    <?php 
                    $wpaicg_chat_addition =  array_key_exists('chat_addition', $wpaicg_chat_shortcode_options) ? $wpaicg_chat_shortcode_options['chat_addition'] : true;
                    ?>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_chat_addition ? ' checked': ''?> name="wpaicg_chat_shortcode_options[chat_addition]" value="1" type="checkbox" id="wpaicg_chat_addition">
                        <label for="wpaicg_chat_addition"><?php echo esc_html__('Instructions','gpt3-ai-content-generator')?></label>
                    </div>
                </fieldset>
                <!-- Instructions -->   
                <?php 
                $wpaicg_additions_json = file_get_contents(WPAICG_PLUGIN_DIR.'admin/chat/context.json');
                $wpaicg_additions = json_decode($wpaicg_additions_json, true);
                $wpaicg_settings['chat_addition_text'] = str_replace("\\",'',$wpaicg_settings['chat_addition_text']);
                ?>    
                <div class="nice-form-group">
                <label><?php echo esc_html__('Instructions','gpt3-ai-content-generator')?></label>
                    <select <?php echo !$wpaicg_chat_addition ? ' disabled':'';?> class="wpaicg_chat_addition_template">
                        <option value=""><?php echo esc_html__('Select Template','gpt3-ai-content-generator')?></option>
                        <?php
                        foreach($wpaicg_additions as $key=>$wpaicg_addition){
                            echo '<option value="'.esc_html($wpaicg_addition).'">'.esc_html($key).'</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="nice-form-group">
                    <textarea <?php echo !$wpaicg_chat_addition ? ' disabled':''?> name="wpaicg_chat_shortcode_options[chat_addition_text]" id="wpaicg_chat_addition_text" class="regular-text wpaicg_chat_addition_text" rows="8"><?php echo !empty($wpaicg_settings['chat_addition_text']) ? esc_html($wpaicg_settings['chat_addition_text']) : esc_html__('You are a helpful AI Assistant. Please be friendly. Today\'s date is [date].','gpt3-ai-content-generator')?></textarea>
                </div>
                <p></p>
                <!-- Advanced Parameters -->   
                <a href="#" id="wpaicg-advanced-settings-link"><?php echo esc_html__('Show Advanced Parameters','gpt3-ai-content-generator'); ?></a>
                <div id="wpaicg-advanced-settings" style="display: none;">
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Temperature','gpt3-ai-content-generator')?></label>
                        <input type="text" id="label_temperature" name="wpaicg_chat_shortcode_options[temperature]" value="<?php echo esc_html( $wpaicg_settings['temperature'] ) ;?>">
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Max Tokens','gpt3-ai-content-generator')?></label>
                        <input type="text" id="label_max_tokens" name="wpaicg_chat_shortcode_options[max_tokens]" value="<?php echo esc_html( $wpaicg_settings['max_tokens'] ) ;?>" >
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Top P','gpt3-ai-content-generator')?></label>
                        <input type="text" id="label_top_p" name="wpaicg_chat_shortcode_options[top_p]" value="<?php echo esc_html( $wpaicg_settings['top_p'] ) ; ?>" >
                    </div>

                    <div class="nice-form-group">
                        <input type="hidden" id="label_best_of" name="wpaicg_chat_shortcode_options[best_of]" value="<?php echo esc_html( $wpaicg_settings['best_of'] ) ; ?>" >
                    </div>

                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Frequency Penalty','gpt3-ai-content-generator')?></label>
                        <input type="text" id="label_frequency_penalty" name="wpaicg_chat_shortcode_options[frequency_penalty]" value="<?php echo esc_html( $wpaicg_settings['frequency_penalty'] ) ; ?>" >
                    </div>

                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Presence Penalty','gpt3-ai-content-generator')?></label>
                        <input type="text" id="label_presence_penalty" name="wpaicg_chat_shortcode_options[presence_penalty]" value="<?php echo esc_html( $wpaicg_settings['presence_penalty'] ) ;?>" >
                    </div>
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
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
                    <label><?php echo esc_html__('AI Name','gpt3-ai-content-generator')?></label>
                    <input type="text" class="wpaicg_chat_shortcode_ai_name" name="wpaicg_chat_shortcode_options[ai_name]" value="<?php echo esc_html( $wpaicg_settings['ai_name'] ) ;?>" >
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('User Name','gpt3-ai-content-generator')?></label>
                    <input type="text" class="wpaicg_chat_shortcode_you" name="wpaicg_chat_shortcode_options[you]" value="<?php echo esc_html( $wpaicg_settings['you'] ) ; ?>" >
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Response Wait Message','gpt3-ai-content-generator')?></label>
                    <input type="text" class="wpaicg_chat_shortcode_ai_thinking" name="wpaicg_chat_shortcode_options[ai_thinking]" value="<?php echo esc_html( $wpaicg_settings['ai_thinking'] ) ;?>" >
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Placeholder','gpt3-ai-content-generator')?></label>
                    <input type="text" class="wpaicg_chat_shortcode_placeholder" name="wpaicg_chat_shortcode_options[placeholder]" value="<?php echo esc_html( $wpaicg_settings['placeholder'] ) ;?>" >
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Welcome Message','gpt3-ai-content-generator')?></label>
                    <input type="text" class="wpaicg_chat_shortcode_welcome" name="wpaicg_chat_shortcode_options[welcome]" value="<?php echo esc_html( $wpaicg_settings['welcome'] ) ;?>" >
                </div>
                <div class="nice-form-group">
                    <input type="hidden" value="<?php echo esc_html($wpaicg_settings['no_answer'])?>" name="wpaicg_chat_shortcode_options[no_answer]">
                </div>
                <div class="nice-form-group">
                    <label><?php echo esc_html__('Footer Note','gpt3-ai-content-generator')?></label>
                    <input value="<?php echo esc_html($wpaicg_settings['footer_text'])?>" type="text" name="wpaicg_chat_shortcode_options[footer_text]" placeholder="<?php echo esc_html__('Powered by ...','gpt3-ai-content-generator')?>">
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
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
                <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: -1em;">
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Content Aware','gpt3-ai-content-generator')?></label>
                        <!-- Hidden input to ensure 'no' is submitted if checkbox is unchecked -->
                        <input type="hidden" value="no" name="wpaicg_chat_shortcode_options[content_aware]">
                        <!-- Checkbox input, only submits 'yes' when checked -->
                        <input 
                            <?php echo $wpaicg_settings['content_aware'] == 'yes' ? 'checked' : '' ?> 
                            value="yes" 
                            type="checkbox" 
                            style="border-color: #10b981; margin-top: 4px;" 
                            class="switch" 
                            id="wpaicg_chat_content_aware" 
                            name="wpaicg_chat_shortcode_options[content_aware]">
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('User Aware','gpt3-ai-content-generator')?></label>
                        <!-- Hidden input to ensure 'no' is submitted if checkbox is unchecked -->
                        <input type="hidden" value="no" name="wpaicg_chat_shortcode_options[user_aware]">
                        <input 
                            <?php echo $wpaicg_settings['user_aware'] == 'yes' ? 'checked' : '' ?> 
                            value="yes" 
                            type="checkbox" 
                            style="border-color: #10b981; margin-top: 4px;" 
                            class="switch" 
                            name="wpaicg_chat_shortcode_options[user_aware]">
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Memory','gpt3-ai-content-generator')?></label>
                        <!-- Hidden input to ensure 'no' is submitted if checkbox is unchecked -->
                        <input type="hidden" value="no" name="wpaicg_chat_shortcode_options[remember_conversation]">
                        <!-- Checkbox input, only submits 'yes' when checked -->
                        <input 
                            <?php echo $wpaicg_settings['remember_conversation'] == 'yes' ? 'checked' : '' ?> 
                            value="yes" 
                            type="checkbox" 
                            style="border-color: #10b981; margin-top: 4px;" 
                            class="switch" 
                            id="wpaicgchat_remember_conversation" 
                            name="wpaicg_chat_shortcode_options[remember_conversation]">
                    </div>
                </div>
                <fieldset class="nice-form-group">
                <legend><?php echo esc_html__('Data Source', 'gpt3-ai-content-generator'); ?></legend>
                    <div class="nice-form-group">
                        <input <?php echo !$wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? ' checked': ''?><?php echo $wpaicg_settings['content_aware'] == 'no' ? ' disabled':''?> type="checkbox" id="wpaicg_chat_excerpt" class="<?php echo $wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? 'asdisabled' : ''?>">
                        <label for="wpaicg_chat_excerpt"><?php echo esc_html__('Excerpt','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? ' checked': ''?><?php echo $wpaicg_settings['content_aware'] == 'no' ? ' disabled':''?> type="checkbox" value="1" name="wpaicg_chat_shortcode_options[embedding]" id="wpaicg_chat_embedding" class="<?php echo !$wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? 'asdisabled' : ''?>">
                        <label for="wpaicg_chat_embedding"><?php echo esc_html__('Embeddings','gpt3-ai-content-generator')?></label>
                    </div>
                </fieldset>

                <div class="nice-form-group" style="display: flex;justify-content: space-between;">
                    <div class="nice-form-group" style="margin-right: 5px;">
                        <label><?php echo esc_html__('Vector DB', 'gpt3-ai-content-generator'); ?></label>
                        <select style="width: 100px;" name="wpaicg_chat_shortcode_options[vectordb]" id="wpaicg_db_provider" class="wpaicg-form-select-vectordb" <?php echo !$wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? ' disabled' : '';?>>
                            <option value="pinecone" <?php echo ($wpaicg_settings['vectordb'] === 'pinecone' || empty($wpaicg_settings['vectordb'])) ? 'selected' : ''; ?>><?php echo esc_html__('Pinecone', 'gpt3-ai-content-generator'); ?></option>
                            <option value="qdrant" <?php echo $wpaicg_settings['vectordb'] === 'qdrant' ? 'selected' : ''; ?>><?php echo esc_html__('Qdrant', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                    <div class="nice-form-group" style="margin-right: 5px;">
                        <label><?php echo esc_html__('Index','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_settings['embedding']) || $wpaicg_settings['content_aware'] == 'no' ? ' disabled':''?> name="wpaicg_chat_shortcode_options[embedding_index]" id="wpaicg_chat_embedding_index" class="<?php echo !$wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? 'asdisabled' : ''?>">
                            <option value=""><?php echo esc_html__('Default','gpt3-ai-content-generator')?></option>
                            <?php
                            foreach($wpaicg_pinecone_indexes as $wpaicg_pinecone_index){
                                echo '<option'.(isset($wpaicg_settings['embedding_index']) && $wpaicg_settings['embedding_index'] == $wpaicg_pinecone_index['url'] ? ' selected':'').' value="'.esc_html($wpaicg_pinecone_index['url']).'">'.esc_html($wpaicg_pinecone_index['name']).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group" style="margin-right: 5px;">
                        <label><?php echo esc_html__('Collection', 'gpt3-ai-content-generator'); ?></label>
                        <select <?php echo empty($wpaicg_settings['embedding']) || $wpaicg_settings['content_aware'] == 'no' ? ' disabled' : '' ?> name="wpaicg_chat_shortcode_options[qdrant_collection]" id="wpaicg_chat_qdrant_collection" class="<?php echo !$wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? 'asdisabled' : '' ?>">
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
                                $selected = (isset($wpaicg_settings['qdrant_collection']) && $wpaicg_settings['qdrant_collection'] == $name) ? ' selected' : '';
                                echo '<option value="' . esc_attr($name) . '"' . $selected . '>' . esc_html($display_name) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Limit','gpt3-ai-content-generator')?></label>
                        <select style="width: 50px;" <?php echo empty($wpaicg_settings['embedding']) || $wpaicg_settings['content_aware'] == 'no' ? ' disabled':''?> name="wpaicg_chat_shortcode_options[embedding_top]" id="wpaicg_chat_embedding_top" class="<?php echo !$wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? 'asdisabled' : ''?>">
                            <?php
                            for($i = 1; $i <=5;$i++){
                                echo '<option'.($wpaicg_settings['embedding_top'] == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="nice-form-group" style="display: flex; justify-content: space-between; margin-top: 1em;">
                    <!-- Toggle for using the default embedding model -->
                    <div class="nice-form-group" style="display: flex;align-items: center;">
                        <input type="hidden" name="wpaicg_chat_shortcode_options[use_default_embedding]" value="false">
                        <input type="checkbox" class="switch wpaicg_use_default_embedding"
                            id="wpaicg_use_default_embedding"
                            name="wpaicg_chat_shortcode_options[use_default_embedding]"
                            value="true"
                            <?php echo $use_default_embedding ? 'checked' : ''; ?>
                            style="border-color: #10b981; margin-top: 4px;">
                        <label><?php echo esc_html__('Use Default Embedding', 'gpt3-ai-content-generator'); ?></label>
                    </div>
                </div>
                <div class="nice-form-group" id="wpaicg_embedding_model_div" style="display: none;">
                    <label><?php echo esc_html__('Embedding models', 'gpt3-ai-content-generator'); ?></label>
                    <select name="wpaicg_chat_shortcode_options[embedding_model]" id="wpaicg_embedding_model" onchange="updateProvider()">
                        <?php
                        $embedding_model = isset($wpaicg_chat_shortcode_options['embedding_model']) ? $wpaicg_chat_shortcode_options['embedding_model'] : '';
                        $embedding_models = \WPAICG\WPAICG_Util::get_instance()->get_embedding_models();
                        foreach ($embedding_models as $provider => $models) {
                            echo '<optgroup label="' . esc_attr($provider) . '">';
                            foreach ($models as $model => $dimension) {
                                $selected = ($model === $embedding_model) ? 'selected' : '';
                                echo '<option value="' . esc_attr($model) . '" data-provider="' . esc_attr($provider) . '" ' . $selected . '>' . esc_html($model) . ' (' . esc_html($dimension) . ')</option>';
                            }
                            echo '</optgroup>';
                        }
                        ?>
                    </select>
                    <input type="hidden" name="wpaicg_chat_shortcode_options[embedding_provider]" id="wpaicg_embedding_provider">
                </div>

                <!-- Conversation Starters Section -->
                <div class="nice-form-group" id="wpaicg_conversation_starters_wrapper">
                    <label><?php echo esc_html__('Conversation Starters', 'gpt3-ai-content-generator'); ?></label>
                    <div id="wpaicg_conversation_starters_container">
                        <?php foreach ($wpaicg_conversation_starters as $starter): ?>
                            <div class="nice-form-group">
                                <input type="text" name="wpaicg_conversation_starters[]" oninput="handleInput(event)" value="<?php echo esc_attr($starter['text']); ?>">
                                <?php if ($starter['index'] > 0): // Assuming the first starter should not have a delete button ?>
                                    <button type="button" class="wpaicg-delete-starter" onclick="removeStarter(this)">X</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($wpaicg_conversation_starters)): // Show one empty input if no starters are saved ?>
                            <div class="nice-form-group">
                                <input type="text" name="wpaicg_conversation_starters[]" oninput="handleInput(event)">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if(\WPAICG\wpaicg_util_core()->wpaicg_is_pro()): ?>
                    <div class="nice-form-group">
                        <input <?php echo empty($wpaicg_settings['embedding']) || $wpaicg_settings['content_aware'] == 'no' ? ' disabled':''?><?php echo isset($wpaicg_settings['embedding_pdf']) && $wpaicg_settings['embedding_pdf'] ? ' checked':''?> type="checkbox" value="1" name="wpaicg_chat_shortcode_options[embedding_pdf]" class="<?php echo !$wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? 'asdisabled' : ''?>" id="wpaicg_chat_embedding_pdf">
                        <label for="wpaicg_chat_embedding_pdf"><?php echo esc_html__('Enable PDF Upload','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('PDF Page Limit','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_settings['embedding']) || $wpaicg_settings['content_aware'] == 'no' ? ' disabled':''?> name="wpaicg_chat_shortcode_options[pdf_pages]" id="wpaicg_chat_pdf_pages" class="<?php echo !$wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? 'asdisabled' : ''?>" style="width: 65px!important;">
                            <?php
                            $pdf_pages = isset($wpaicg_settings['pdf_pages']) && !empty($wpaicg_settings['pdf_pages']) ? $wpaicg_settings['pdf_pages'] : 120;
                            for($i=1;$i <= 120;$i++){
                                echo '<option'.($pdf_pages == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('PDF Upload Confirmation Message','gpt3-ai-content-generator')?></label>
                        <textarea <?php echo empty($wpaicg_settings['embedding']) || $wpaicg_settings['content_aware'] == 'no' ? ' disabled':''?> rows="8" name="wpaicg_chat_shortcode_options[embedding_pdf_message]" class="<?php echo !$wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? 'asdisabled' : ''?>" id="wpaicg_chat_embedding_pdf_message"><?php echo isset($wpaicg_settings['embedding_pdf_message']) && $wpaicg_settings['embedding_pdf_message'] ? esc_html(str_replace("\\",'',$wpaicg_settings['embedding_pdf_message'])):''?></textarea>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('PDF Icon','gpt3-ai-content-generator')?></label>
                        <input style="width: 55px;" value="<?php echo esc_html($wpaicg_settings['pdf_color'])?>" type="color" class="wpaicg_pdf_color" id="wpaicg_pdf_color" name="wpaicg_chat_shortcode_options[pdf_color]">
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
                        <select <?php echo empty($wpaicg_settings['embedding']) || $wpaicg_settings['content_aware'] == 'no' ? ' disabled':''?> name="wpaicg_chat_shortcode_options[embedding_type]" id="wpaicg_chat_embedding_type" class="<?php echo !$wpaicg_settings['embedding'] && $wpaicg_settings['content_aware'] == 'yes' ? 'asdisabled' : ''?>">
                            <option <?php echo $wpaicg_settings['embedding_type'] ? ' selected':'';?> value="openai"><?php echo esc_html__('Conversational','gpt3-ai-content-generator')?></option>
                            <option <?php echo empty($wpaicg_settings['embedding_type']) ? ' selected':''?> value=""><?php echo esc_html__('Non-Conversational','gpt3-ai-content-generator')?></option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Memory Limit','gpt3-ai-content-generator')?></label>
                        <select name="wpaicg_chat_shortcode_options[conversation_cut]">
                            <?php
                            for($i=3;$i<=50;$i++){
                                echo '<option'.($wpaicg_settings['conversation_cut'] == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label for="label_wpai_language"><?php echo esc_html__('Language','gpt3-ai-content-generator')?></label>
                        <select id="label_wpai_language" name="wpaicg_chat_shortcode_options[language]" >
                            <?php
                            foreach ($language_options as $value => $label) {
                                $selected = $wpaicg_settings['language'] == $value ? ' selected' : '';
                                echo "<option value=\"{$value}\"{$selected}>". esc_html($label) ."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_chat_tone_id"><?php echo esc_html__('Tone','gpt3-ai-content-generator')?></label>
                        <select name="wpaicg_chat_shortcode_options[tone]" id="wpaicg_chat_tone_id">
                            <?php
                            foreach ($tone_options as $value => $label) {
                                $selected = $wpaicg_settings['tone'] == $value ? ' selected' : '';
                                echo "<option value=\"{$value}\"{$selected}>". esc_html($label) ."</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_chat_act_id"><?php echo esc_html__('Act As','gpt3-ai-content-generator')?></label>
                        <select name="wpaicg_chat_shortcode_options[profession]" id="wpaicg_chat_act_id" >
                            <?php
                            foreach ($profession_options as $value => $label) {
                                $selected = $wpaicg_settings['profession'] == $value ? ' selected' : '';
                                echo "<option value=\"{$value}\"{$selected}>". esc_html($label) ."</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
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
                    // If the provider isn't Azure or Google or OpenRouter, display the fields
                    if ($wpaicg_provider !== 'Azure' && $wpaicg_provider !== 'Google' && $wpaicg_provider !== 'OpenRouter') {
                    ?>
                <div class="nice-form-group">
                    <input <?php echo isset($wpaicg_settings['audio_enable']) && $wpaicg_settings['audio_enable'] ? ' checked': ''?> value="1" type="checkbox" class="wpaicg_audio_enable" name="wpaicg_chat_shortcode_options[audio_enable]" id="wpaicg_audio_enable">
                    <label><?php echo esc_html__('Speech to Text','gpt3-ai-content-generator')?></label>
                </div>
                <?php 
                    } else {  // If the provider is Azure, display the notice
                    ?>
                <div class="nice-form-group">
                    <input type="checkbox" disabled>
                    <label><?php echo esc_html__('Speech to Text','gpt3-ai-content-generator')?></label>
                </div>
                <p>
                    <small><?php echo esc_html__('Speech to Text is not available in Azure or Google. If you want to use it, change your provider to OpenAI under Settings - AI Engine.', 'gpt3-ai-content-generator'); ?></small>
                </p>
                <?php 
                    }
                    ?>
                
                <div class="nice-form-group">
                    <input <?php echo $wpaicg_chat_to_speech ? 'checked' : ''; ?> value="1" type="checkbox" name="wpaicg_chat_shortcode_options[chat_to_speech]" class="wpaicg_chat_to_speech" id="wpaicg_chat_to_speech">
                    <label for="wpaicg_chat_to_speech"><?php echo esc_html__('Text to Speech','gpt3-ai-content-generator')?></label>
                </div>
                <?php
                    $disabled_voice_fields = !$wpaicg_chat_to_speech;
                    $google_disabled = !isset($wpaicg_google_api_key) || empty($wpaicg_google_api_key) ? 'disabled' : '';
                    $elevenlabs_disabled = !isset($wpaicg_elevenlabs_api) || empty($wpaicg_elevenlabs_api) ? 'disabled' : '';
                    //azure or google or OpenRouter 
                    $openai_disabled = $wpaicg_provider === 'Azure' || $wpaicg_provider === 'Google' || $wpaicg_provider === 'OpenRouter' ? 'disabled' : '';
                    ?>
                <div class="nice-form-group">
                    <label for="wpaicg_voice_service"><?php echo esc_html__('Provider','gpt3-ai-content-generator')?></label>
                    <select name="wpaicg_chat_shortcode_options[voice_service]" id="wpaicg_voice_service" class="wpaicg_voice_service" <?php echo $disabled_voice_fields ? 'disabled' : ''; ?>>
                        <option <?php echo $wpaicg_chat_voice_service == 'openai' ? 'selected' : ''; ?> value="openai" <?php echo $openai_disabled; ?>><?php echo esc_html__('OpenAI','gpt3-ai-content-generator')?></option>
                        <option <?php echo $wpaicg_chat_voice_service == 'google' ? ' selected':'';?> value="google" <?php echo $google_disabled; ?>><?php echo esc_html__('Google','gpt3-ai-content-generator')?></option>
                        <option <?php echo $wpaicg_chat_voice_service == 'elevenlabs' ? ' selected':'';?> value="elevenlabs" <?php echo $elevenlabs_disabled; ?>><?php echo esc_html__('ElevenLabs','gpt3-ai-content-generator')?></option> 
                    </select>
                </div>
                <!-- OpenAI -->
                <?php
                    $wpaicg_openai_model = isset($wpaicg_settings['openai_model']) && !empty($wpaicg_settings['openai_model']) ? $wpaicg_settings['openai_model'] : 'tts-1';
                    $wpaicg_openai_voice = isset($wpaicg_settings['openai_voice']) && !empty($wpaicg_settings['openai_voice']) ? $wpaicg_settings['openai_voice'] : 'alloy';
                    $wpaicg_openai_output_format = isset($wpaicg_settings['openai_output_format']) && !empty($wpaicg_settings['openai_output_format']) ? $wpaicg_settings['openai_output_format'] : 'mp3';
                    $wpaicg_openai_voice_speed = isset($wpaicg_settings['openai_voice_speed']) && !empty($wpaicg_settings['openai_voice_speed']) ? $wpaicg_settings['openai_voice_speed'] : '1.0';
                    ?>
                <div class="wpaicg_voice_service_openai" style="<?php echo $wpaicg_chat_voice_service == 'openai' ? '' : 'display:none'?>">
                    <div class="nice-form-group">
                        <label for="wpaicg_openai_model"><?php echo esc_html__('Model','gpt3-ai-content-generator')?></label>
                        <select name="wpaicg_chat_shortcode_options[openai_model]" id="wpaicg_openai_model" class="wpaicg_openai_model" <?php echo $disabled_voice_fields ? 'disabled' : ''; ?>>
                            <option value="tts-1" <?php echo $wpaicg_openai_model == 'tts-1' ? 'selected' : ''; ?>><?php echo esc_html__('tts-1','gpt3-ai-content-generator')?></option>
                            <option value="tts-1-hd" <?php echo $wpaicg_openai_model == 'tts-1-hd' ? 'selected' : ''; ?>><?php echo esc_html__('tts-1-hd','gpt3-ai-content-generator')?></option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_openai_voice"><?php echo esc_html__('Voice','gpt3-ai-content-generator')?></label>
                        <select name="wpaicg_chat_shortcode_options[openai_voice]" id="wpaicg_openai_voice" class="wpaicg_openai_voice" <?php echo $disabled_voice_fields ? 'disabled' : ''; ?>>
                            <option value="alloy" <?php echo $wpaicg_openai_voice == 'alloy' ? 'selected' : ''; ?>><?php echo esc_html__('alloy','gpt3-ai-content-generator')?></option>
                            <option value="echo" <?php echo $wpaicg_openai_voice == 'echo' ? 'selected' : ''; ?>><?php echo esc_html__('echo','gpt3-ai-content-generator')?></option>
                            <option value="fable" <?php echo $wpaicg_openai_voice == 'fable' ? 'selected' : ''; ?>><?php echo esc_html__('fable','gpt3-ai-content-generator')?></option>
                            <option value="nova" <?php echo $wpaicg_openai_voice == 'nova' ? 'selected' : ''; ?>><?php echo esc_html__('nova','gpt3-ai-content-generator')?></option>
                            <option value="onyx" <?php echo $wpaicg_openai_voice == 'onyx' ? 'selected' : ''; ?>><?php echo esc_html__('onyx','gpt3-ai-content-generator')?></option>
                            <option value="shimmer" <?php echo $wpaicg_openai_voice == 'shimmer' ? 'selected' : ''; ?>><?php echo esc_html__('shimmer','gpt3-ai-content-generator')?></option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_openai_output_format"><?php echo esc_html__('Format', 'gpt3-ai-content-generator'); ?></label>
                        <select name="wpaicg_chat_shortcode_options[openai_output_format]" id="wpaicg_openai_output_format" class="wpaicg_openai_output_format" <?php echo $disabled_voice_fields ? 'disabled' : ''; ?>>
                            <option value="mp3" <?php echo $wpaicg_openai_output_format == 'mp3' ? 'selected' : ''; ?>><?php echo esc_html__('mp3', 'gpt3-ai-content-generator'); ?></option>
                            <option value="opus" <?php echo $wpaicg_openai_output_format == 'opus' ? 'selected' : ''; ?>><?php echo esc_html__('opus', 'gpt3-ai-content-generator'); ?></option>
                            <option value="aac" <?php echo $wpaicg_openai_output_format == 'aac' ? 'selected' : ''; ?>><?php echo esc_html__('aac', 'gpt3-ai-content-generator'); ?></option>
                            <option value="flac" <?php echo $wpaicg_openai_output_format == 'flac' ? 'selected' : ''; ?>><?php echo esc_html__('flac', 'gpt3-ai-content-generator'); ?></option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_openai_voice_speed"><?php echo esc_html__('Speed (0.25 to 4.0)', 'gpt3-ai-content-generator'); ?></label>
                        <input type="number" name="wpaicg_chat_shortcode_options[openai_voice_speed]" id="wpaicg_openai_voice_speed" class="wpaicg_openai_voice_speed" min="0.25" max="4.0" step="0.01" value="<?php echo esc_attr($wpaicg_openai_voice_speed); ?>" <?php echo $disabled_voice_fields ? 'disabled' : ''; ?>>
                    </div>
                </div>

                <!-- Google -->
                <?php
                    $wpaicg_voice_language = isset($wpaicg_settings['voice_language']) && !empty($wpaicg_settings['voice_language']) ? $wpaicg_settings['voice_language'] : 'en-US';
                    $wpaicg_voice_name = isset($wpaicg_settings['voice_name']) && !empty($wpaicg_settings['voice_name']) ? $wpaicg_settings['voice_name'] : 'en-US-Studio-M';
                    $wpaicg_voice_device = isset($wpaicg_settings['voice_device']) && !empty($wpaicg_settings['voice_device']) ? $wpaicg_settings['voice_device'] : '';
                    $wpaicg_voice_speed = isset($wpaicg_settings['voice_speed']) && !empty($wpaicg_settings['voice_speed']) ? $wpaicg_settings['voice_speed'] : 1;
                    $wpaicg_voice_pitch = isset($wpaicg_settings['voice_pitch']) && !empty($wpaicg_settings['voice_pitch']) ? $wpaicg_settings['voice_pitch'] : 0;
                    ?>
                <div class="wpaicg_voice_service_google" style="<?php echo $wpaicg_chat_voice_service == 'google' && (!empty($wpaicg_google_api_key) || !empty($wpaicg_elevenlabs_api)) ? '' : 'display:none'?>">
                    <div class="nice-form-group">
                        <label for="wpaicg_voice_language"><?php echo esc_html__('Language','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_google_api_key) || $disabled_voice_fields ? ' disabled':''?> name="wpaicg_chat_shortcode_options[voice_language]" class="wpaicg_voice_language" id="wpaicg_voice_language">
                            <?php
                            foreach(\WPAICG\WPAICG_Google_Speech::get_instance()->languages as $key=>$voice_language){
                                echo '<option'.($wpaicg_voice_language == $key ? ' selected':'').' value="'.esc_html($key).'">'.esc_html($voice_language).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_voice_name"><?php echo esc_html__('Voice','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_google_api_key) || $disabled_voice_fields ? ' disabled':''?> data-value="<?php echo esc_html($wpaicg_voice_name)?>" name="wpaicg_chat_shortcode_options[voice_name]" class="wpaicg_voice_name" id="wpaicg_voice_name"></select>
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_voice_device"><?php echo esc_html__('Audio Device Profile','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_google_api_key) || $disabled_voice_fields ? ' disabled':''?> name="wpaicg_chat_shortcode_options[voice_device]" class="wpaicg_voice_device" id="wpaicg_voice_device">
                            <?php
                            foreach(\WPAICG\WPAICG_Google_Speech::get_instance()->devices() as $key => $device){
                                echo '<option'.($wpaicg_voice_device == $key ? ' selected':'').' value="'.esc_html($key).'">'.esc_html($device).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_voice_speed"><?php echo esc_html__('Speed','gpt3-ai-content-generator')?></label>
                        <input <?php echo empty($wpaicg_google_api_key) || $disabled_voice_fields ? ' disabled':''?> type="text" class="wpaicg_voice_speed" value="<?php echo esc_html($wpaicg_voice_speed)?>" name="wpaicg_chat_shortcode_options[voice_speed]" id="wpaicg_voice_speed">
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_voice_pitch"><?php echo esc_html__('Pitch','gpt3-ai-content-generator')?></label>
                        <input <?php echo empty($wpaicg_google_api_key) ||$disabled_voice_fields ? ' disabled':''?> type="text" class="wpaicg_voice_pitch" value="<?php echo esc_html($wpaicg_voice_pitch)?>" name="wpaicg_chat_shortcode_options[voice_pitch]" id="wpaicg_voice_pitch">
                    </div>
                </div>

                <!-- ElevenLabs -->
                <div class="wpaicg_voice_service_elevenlabs" style="<?php echo $wpaicg_chat_voice_service == 'google' || (empty($wpaicg_google_api_key) && empty($wpaicg_elevenlabs_api)) ? 'display:none' : ''?>">
                    <div class="nice-form-group">
                        <label for="wpaicg_elevenlabs_voice"><?php echo esc_html__('Voice','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_elevenlabs_api) || $disabled_voice_fields ? ' disabled':''?> name="wpaicg_chat_shortcode_options[elevenlabs_voice]" class="wpaicg_elevenlabs_voice" id="wpaicg_elevenlabs_voice">
                            <?php
                            foreach(\WPAICG\WPAICG_ElevenLabs::get_instance()->voices as $key=>$voice){
                                echo '<option'.($wpaicg_elevenlabs_voice == $key ? ' selected':'').' value="'.esc_html($key).'">'.esc_html($voice).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label for="wpaicg_elevenlabs_model"><?php echo esc_html__('Model','gpt3-ai-content-generator')?></label>
                        <select <?php echo empty($wpaicg_elevenlabs_api) || $disabled_voice_fields ? ' disabled':''?> name="wpaicg_chat_shortcode_options[elevenlabs_model]" class="wpaicg_elevenlabs_model" id="wpaicg_elevenlabs_model">
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
                        <button class="button button-primary wpaicg-w-25"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
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
                    <input <?php echo $wpaicg_user_limited ? ' checked': ''?> type="checkbox" value="1" class="wpaicg_user_token_limit" name="wpaicg_chat_shortcode_options[user_limited]" id="wpaicg_user_token_limit">
                    <label for="wpaicg_user_token_limit"><?php echo esc_html__('Limit Registered Users','gpt3-ai-content-generator')?></label>
                </div>
                <div class="nice-form-group">
                    <label for="wpaicg_user_token_limit_text"><?php echo esc_html__('Token Allocation','gpt3-ai-content-generator')?></label>
                    <input <?php echo $wpaicg_user_limited ? '' : ' disabled'?> class="wpaicg_user_token_limit_text" type="text" value="<?php echo esc_html($wpaicg_user_tokens)?>" name="wpaicg_chat_shortcode_options[user_tokens]" id="wpaicg_user_token_limit_text">
                </div>
                <div class="nice-form-group">
                    <label for="wpaicg_role_limited"><?php echo esc_html__('Token Allocation by Role','gpt3-ai-content-generator')?></label>
                    <?php
                    foreach($wpaicg_roles as $key=>$wpaicg_role){
                        echo '<input class="wpaicg_role_'.esc_html($key).'" value="'.(isset($wpaicg_settings['limited_roles'][$key]) && !empty($wpaicg_settings['limited_roles'][$key]) ? esc_html($wpaicg_settings['limited_roles'][$key]) : '').'" type="hidden" name="wpaicg_chat_shortcode_options[limited_roles]['.esc_html($key).']">';
                    }
                    ?>
                    <input <?php echo $wpaicg_user_limited ? '': ($wpaicg_settings['role_limited'] ? ' checked':'')?> type="checkbox" value="1" class="wpaicg_role_limited" name="wpaicg_chat_shortcode_options[role_limited]" id="wpaicg_role_limited">
                    <a href="javascript:void(0)" class="wpaicg_limit_set_role<?php echo $wpaicg_user_limited || !$wpaicg_settings['role_limited'] ? ' disabled': ''?>"><?php echo esc_html__('Configure Role Allocations','gpt3-ai-content-generator')?></a>
                </div>
                <div class="nice-form-group">
                    <input <?php echo $wpaicg_guest_limited ? ' checked': ''?> type="checkbox" class="wpaicg_guest_token_limit" value="1" name="wpaicg_chat_shortcode_options[guest_limited]" id="wpaicg_guest_token_limit">
                    <label for="wpaicg_guest_token_limit"><?php echo esc_html__('Limit Non-Registered Users','gpt3-ai-content-generator')?></label>
                </div>
                <div class="nice-form-group">
                    <label for="wpaicg_guest_token_limit_text"><?php echo esc_html__('Token Allocation','gpt3-ai-content-generator')?></label>
                    <input <?php echo $wpaicg_guest_limited ? '' : ' disabled'?> class="wpaicg_guest_token_limit_text" type="text" value="<?php echo esc_html($wpaicg_guest_tokens)?>" name="wpaicg_chat_shortcode_options[guest_tokens]" id="wpaicg_guest_token_limit_text">
                </div>
                <div class="nice-form-group">
                    <label for="wpaicg_reset_limit"><?php echo esc_html__('Reset Interval','gpt3-ai-content-generator')?></label>
                    <select name="wpaicg_chat_shortcode_options[reset_limit]" id="wpaicg_reset_limit">
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
                    <label for="wpaicg_limited_message"><?php echo esc_html__('Notice','gpt3-ai-content-generator')?></label>
                    <input type="text" value="<?php echo esc_html($wpaicg_limited_message)?>" name="wpaicg_chat_shortcode_options[limited_message]" id="wpaicg_limited_message">
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
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
                        <input <?php echo $wpaicg_save_logs ? ' checked': ''?> value="1" type="checkbox" class="wpaicg_chatbot_save_logs" name="wpaicg_chat_shortcode_options[save_logs]" id="wpaicg_chatbot_save_logs">
                        <label for="wpaicg_chatbot_save_logs"><?php echo esc_html__('Save Chat Logs','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_save_logs ? '' : ' disabled'?><?php echo $wpaicg_save_logs && isset($wpaicg_settings['log_request']) && $wpaicg_settings['log_request'] ? ' checked' : ''?> class="wpaicg_chatbot_log_request" value="1" type="checkbox" name="wpaicg_chat_shortcode_options[log_request]" id="wpaicg_chatbot_log_request">
                        <label for="wpaicg_chatbot_log_request"><?php echo esc_html__('Save Prompt Details','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_save_logs ? '': ' disabled'?><?php echo $wpaicg_log_notice ? ' checked': ''?> value="1" class="wpaicg_chatbot_log_notice" type="checkbox" name="wpaicg_chat_shortcode_options[log_notice]" id="wpaicg_chatbot_log_notice">
                        <label for="wpaicg_chatbot_log_notice"><?php echo esc_html__('Display Notice','gpt3-ai-content-generator')?></label>
                    </div>
                </fieldset>
                <div class="nice-form-group">
                    <label for="wpaicg_chatbot_log_notice_message"><?php echo esc_html__('Notice Text','gpt3-ai-content-generator')?></label>
                    <input <?php echo $wpaicg_save_logs ? '': ' disabled'?> value="<?php echo esc_html($wpaicg_log_notice_message)?>" class="wpaicg_chatbot_log_notice_message" id="wpaicg_chatbot_log_notice_message" type="text" name="wpaicg_chat_shortcode_options[log_notice_message]">
                </div>
                <?php 
                $is_pro = \WPAICG\wpaicg_util_core()->wpaicg_is_pro();
                ?>
                <div class="nice-form-group">
                    <input <?php echo isset($wpaicg_settings['moderation']) && $wpaicg_settings['moderation'] ? ' checked': ''?>  id="openai_moderation_tool" name="wpaicg_chat_shortcode_options[moderation]" value="1" type="checkbox" <?php if(!\WPAICG\wpaicg_util_core()->wpaicg_is_pro() || $wpaicg_provider === 'Azure' || $wpaicg_provider === 'Google' || $wpaicg_provider === 'OpenRouter') echo 'disabled'; ?>>
                    <label for="openai_moderation_tool"><?php echo !$is_pro ? esc_html__('Moderation (Available in Pro)','gpt3-ai-content-generator') : esc_html__('Moderation','gpt3-ai-content-generator'); ?></label>
                </div>
                <?php 
                // Disable if it is Azure or Google or OpenRouter
                if($wpaicg_provider != 'OpenAI'):
                    echo '<small>'. esc_html__('Moderation is available for OpenAI only. If you want to use the moderation tool, please change your provider to OpenAI under Settings - AI Engine tab.', 'gpt3-ai-content-generator') .'</small>';
                endif;
                ?>
                <div class="nice-form-group">
                    <label for="openai_moderation_model"><?php echo esc_html__('Model','gpt3-ai-content-generator')?></label>
                    <select id="openai_moderation_model"  name="wpaicg_chat_shortcode_options[moderation_model]" <?php if(!\WPAICG\wpaicg_util_core()->wpaicg_is_pro() || $wpaicg_provider === 'Azure' || $wpaicg_provider === 'Google' || $wpaicg_provider === 'OpenRouter') echo 'disabled'; ?>>
                        <option <?php echo isset($wpaicg_settings['moderation_model']) && $wpaicg_settings['moderation_model'] == 'text-moderation-latest' ? ' selected':'';?> value="text-moderation-latest">text-moderation-latest</option>
                        <option <?php echo isset($wpaicg_settings['moderation_model']) && $wpaicg_settings['moderation_model'] == 'text-moderation-stable' ? ' selected':'';?> value="text-moderation-stable">text-moderation-stable</option>
                    </select>
                </div>
                <div class="nice-form-group">
                    <label for="openai_moderation_notice"><?php echo esc_html__('Notice','gpt3-ai-content-generator')?></label>
                    <textarea rows="8" id="openai_moderation_notice" name="wpaicg_chat_shortcode_options[moderation_notice]" <?php if(!\WPAICG\wpaicg_util_core()->wpaicg_is_pro() || $wpaicg_provider === 'Azure' || $wpaicg_provider === 'Google' || $wpaicg_provider === 'OpenRouter') echo 'disabled'; ?>><?php echo isset($wpaicg_settings['moderation_notice']) ? esc_html($wpaicg_settings['moderation_notice']) : ''?></textarea>
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
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
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['bgcolor'])?>" type="color" class="wpaicg_bgcolor" id="wpaicg_bgcolor" name="wpaicg_chat_shortcode_options[bgcolor]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['fontcolor'])?>" type="color" id="wpaicg_font_color" class="wpaicg_font_color" name="wpaicg_chat_shortcode_options[fontcolor]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('AI','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['ai_bg_color'])?>" type="color" class="wpaicg_ai_bg_color" id="wpaicg_ai_bg_color" name="wpaicg_chat_shortcode_options[ai_bg_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('User','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['user_bg_color'])?>" type="color" class="wpaicg_user_bg_color" id="wpaicg_user_bg_color" name="wpaicg_chat_shortcode_options[user_bg_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                        <select style="width: 50px;font-size: 11px;height: 32px;padding: 0 1.2em;background-position: right;" name="wpaicg_chat_shortcode_options[fontsize]" class="wpaicg_chat_shortcode_font_size" id="wpaicg_chat_shortcode_font_size">
                            <?php
                            for($i = 10; $i <= 30; $i++){
                                echo '<option'.($wpaicg_settings['fontsize'] == $i ? ' selected': '').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group" style="flex: 1;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Radius','gpt3-ai-content-generator')?></label>
                        <input style="width: 50px;font-size: 11px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['chat_rounded'])?>" type="number" min="0" class="wpaicg_chat_rounded" id="wpaicg_chat_rounded" name="wpaicg_chat_shortcode_options[chat_rounded]">
                    </div>
                </div>
                <div class="nice-form-group" style="display: flex;margin-top: -0.1em;">
                    <div class="nice-form-group" style="padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Width','gpt3-ai-content-generator')?></label>
                        <input style="width: 100px;font-size: 11px;height: 32px;" min="300" type="text" class="wpaicg_chat_shortcode_width" id="wpaicg_chat_shortcode_width" name="wpaicg_chat_shortcode_options[width]" value="<?php echo esc_html( $wpaicg_settings['width'] ) ;?>" >
                    </div>
                    <div class="nice-form-group">
                        <label style="font-size: 11px;"><?php echo esc_html__('Height','gpt3-ai-content-generator')?></label>
                        <input style="width: 100px;font-size: 11px;height: 32px;" min="300" type="text" class="wpaicg_chat_shortcode_height" id="wpaicg_chat_shortcode_height" name="wpaicg_chat_shortcode_options[height]" value="<?php echo esc_html( $wpaicg_settings['height'] ) ;?>" >
                    </div>
                </div>
                <!-- Text Field -->
                <h3 style="font-size: small;"><?php echo esc_html__('Text Field','gpt3-ai-content-generator')?></h3>
                <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: -1em;">
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Box','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['bg_text_field'])?>" type="color" class="wpaicg_bg_text_field" id="wpaicg_bg_text_field" name="wpaicg_chat_shortcode_options[bg_text_field]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['input_font_color'])?>" type="color" class="wpaicg_input_font_color" id="wpaicg_input_font_color" name="wpaicg_chat_shortcode_options[input_font_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Border','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['border_text_field'])?>" type="color" class="wpaicg_border_text_field" id="wpaicg_border_text_field" name="wpaicg_chat_shortcode_options[border_text_field]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Button','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['send_color'])?>" type="color" class="wpaicg_send_color" id="wpaicg_send_color" name="wpaicg_chat_shortcode_options[send_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Mic','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['mic_color'])?>" type="color" class="wpaicg_mic_color" name="wpaicg_chat_shortcode_options[mic_color]" id="wpaicg_mic_color">
                    </div>
                    <div class="nice-form-group" style="flex: 1;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Stop','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['stop_color'])?>" type="color" class="wpaicg_stop_color" name="wpaicg_chat_shortcode_options[stop_color]" id="wpaicg_stop_color">
                    </div>
                </div>
                <div class="nice-form-group" style="display: flex;margin-top: -0.1em;">
                    <div class="nice-form-group" style="padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Height','gpt3-ai-content-generator')?></label>
                        <input style="width: 100px;font-size: 11px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['text_height'])?>" type="number" min="30" class="wpaicg_text_height" id="wpaicg_text_height" name="wpaicg_chat_shortcode_options[text_height]">
                    </div>
                    <div class="nice-form-group">
                        <label style="font-size: 11px;"><?php echo esc_html__('Radius','gpt3-ai-content-generator')?></label>
                        <input style="width: 100px;font-size: 11px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['text_rounded'])?>" type="number" min="0" class="wpaicg_text_rounded" id="wpaicg_text_rounded" name="wpaicg_chat_shortcode_options[text_rounded]">
                    </div>
                    <div class="nice-form-group" style="flex: 1;display: none;">
                        <input type="checkbox" id="enable_send_button" name="wpaicg_chat_shortcode_options[send_button_enabled]" value="1" <?php echo (isset($wpaicg_settings['send_button_enabled']) && $wpaicg_settings['send_button_enabled'] == 1) ? 'checked' : ''; ?>>
                        <label style="font-size: 11px;"><?php echo esc_html__('Enable Send','gpt3-ai-content-generator')?></label>
                    </div>
                </div>
                <!-- Header / Footer -->
                <h3 style="font-size: small;"><?php echo esc_html__('Header / Footer','gpt3-ai-content-generator')?></h3>
                <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: -1em;">
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Box','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_footer_color)?>" type="color" class="wpaicg_footer_color" id="wpaicg_footer_color" name="wpaicg_chat_shortcode_options[footer_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_settings['footer_font_color'])?>" type="color" class="wpaicg_footer_font_color" id="wpaicg_footer_font_color" name="wpaicg_chat_shortcode_options[footer_font_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Icons','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_bar_color)?>" type="color" class="wpaicgchat_bar_color" id="wpaicgchat_bar_color" name="wpaicg_chat_shortcode_options[bar_color]">
                    </div>
                    <div class="nice-form-group" style="flex: 1;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Wait','gpt3-ai-content-generator')?></label>
                        <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_thinking_color)?>" type="color" class="wpaicgchat_thinking_color" id="wpaicgchat_thinking_color" name="wpaicg_chat_shortcode_options[thinking_color]">
                    </div>
                </div>
                <div class="nice-form-group" style="display: flex;margin-top: -0.1em;">
                    <div class="nice-form-group" style="padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Fullscreen','gpt3-ai-content-generator')?></label>
                        <input <?php echo $wpaicg_chat_fullscreen ? ' checked':''?> value="1" type="checkbox" style="border-color: #10b981;margin-top: 4px;" class="switch wpaicgchat_fullscreen" id="wpaicgchat_fullscreen" name="wpaicg_chat_shortcode_options[fullscreen]">
                    </div>
                    <div class="nice-form-group" style="padding-right: 3px;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Download','gpt3-ai-content-generator')?></label>
                        <input <?php echo $wpaicg_chat_download_btn ? ' checked':''?> value="1" type="checkbox" style="border-color: #10b981;margin-top: 4px;" class="switch wpaicgchat_download_btn" id="wpaicgchat_download_btn" name="wpaicg_chat_shortcode_options[download_btn]">
                    </div>
                    <div class="nice-form-group" style="flex: 1;">
                        <label style="font-size: 11px;"><?php echo esc_html__('Clear','gpt3-ai-content-generator')?></label>
                        <input <?php echo $wpaicg_chat_clear_btn ? ' checked':''?> value="1" type="checkbox" style="border-color: #10b981;margin-top: 4px;" class="switch wpaicgchat_clear_btn" id="wpaicgchat_clear_btn" name="wpaicg_chat_shortcode_options[clear_btn]">
                    </div>
                </div>
                <h3 style="font-size: small;"><?php echo esc_html__('Avatar / Icon','gpt3-ai-content-generator')?></h3>
                <div class="nice-form-group" style="display: flex; align-items: center; justify-content: space-between;margin-top: -1em;">
                    <div style="display: inline-flex; align-items: center;">
                        <input value="<?php echo esc_html($wpaicg_settings['ai_icon_url'])?>" type="hidden" name="wpaicg_chat_shortcode_options[ai_icon_url]" class="wpaicg_chat_icon_url">
                        <input <?php echo $wpaicg_settings['ai_icon'] == 'default' ? ' checked': ''?> class="wpaicg_chatbox_icon_default" type="radio" value="default" name="wpaicg_chat_shortcode_options[ai_icon]">
                        <div style="margin-right: 20px;">
                            <img style="display: block;width: 40px; height: 40px;" src="<?php echo esc_html(WPAICG_PLUGIN_URL).'admin/images/chatbot.png'?>">
                            <label style="font-size: 11px;"><?php echo esc_html__('Default','gpt3-ai-content-generator')?></label>
                        </div>
                        <input <?php echo $wpaicg_settings['ai_icon'] == 'custom' ? ' checked': ''?> type="radio" class="wpaicg_chatbox_icon_custom" value="custom" name="wpaicg_chat_shortcode_options[ai_icon]">
                        <div style="margin-right: 10px;">
                            <div class="wpaicg_chatbox_icon">
                                <?php if(!empty($wpaicg_settings['ai_icon_url']) && $wpaicg_settings['ai_icon'] == 'custom'): 
                                    $wpaicg_chatbox_icon_url = wp_get_attachment_url($wpaicg_settings['ai_icon_url']);?>
                                    <img src="<?php echo esc_html($wpaicg_chatbox_icon_url)?>" width="40" height="40">
                                <?php else: ?>
                                    <svg width="40px" height="40px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M246.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 109.3V320c0 17.7 14.3 32 32 32s32-14.3 32-32V109.3l73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128zM64 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 53 43 96 96 96H352c53 0 96-43 96-96V352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V352z"/></svg><br>
                                <?php endif; ?>
                            </div>
                            <label style="font-size: 11px;"><?php echo esc_html__('Custom','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="nice-form-group" style="padding-left: 1em;padding-bottom: 25px;">
                            <input <?php echo $wpaicg_settings['use_avatar'] ? ' checked': ''?> style="border-color: #10b981;margin-top: 4px;" class="switch wpaicg_chat_shortcode_use_avatar" id="wpaicg_chat_shortcode_use_avatar" value="1" type="checkbox" name="wpaicg_chat_shortcode_options[use_avatar]">
                            <label style="font-size: 11px;max-width: fit-content;"><?php echo esc_html__('Use Avatar','gpt3-ai-content-generator')?></label>
                        </div>
                    </div>
                </div>
                <details>
                    <summary>
                        <button class="button button-primary wpaicg-w-25"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
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
            <div class="wpaicg-chat-shortcode-preview">
                <?php echo do_shortcode('[wpaicg_chatgpt]'); ?>
            </div>
    </aside>
  </div>
</div>

<div id="exportMessage" style="display: none;" class="notice notice-success"></div>

<script>
    jQuery(document).ready(function ($){
        $('input[type=radio][name="theme_selection"]:checked').change();
        let wpaicg_google_voices = <?php echo json_encode($wpaicg_google_voices)?>;
        let wpaicg_elevenlab_api = '<?php echo esc_html($wpaicg_elevenlabs_api)?>';
        let wpaicg_google_api_key = '<?php  echo $wpaicg_google_api_key?>';

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

        function resetForm() {
            var resetSource = 'shortcode';

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
            var source = 'shortcode'; // Adjust based on context

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
            var exportSource = 'shortcode'; // Adjust this based on the current context (shortcode, widget, bot)

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

        $(document).on('click', '#exportButton', function() {
            exportSettings();
        });


        function updateImageUploadOption() {
            var provider = $('#wpaicg_provider').val();
            var model = '';

            if (provider === 'OpenAI') {
                model = $('#wpaicg_chat_model').val();
            } else if (provider === 'Google') {
                model = $('#wpaicg_shortcode_google_model').val();
            } else if (provider === 'Azure') {
                model = $('input[name="wpaicg_azure_deployment_input"]').val(); // Assuming deployment name is used as the model identifier
            } else if (provider === 'OpenRouter') {
                model = $('#wpaicg_openrouter_model').val();
            }

            console.log('Selected Provider:', provider); // Debugging line
            console.log('Selected Model:', model); // Debugging line

            var $imageUploadCheckbox = $('.wpaicg_image_enable'); // Update this selector as needed
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

        // Re-check and update whenever the model or provider selection changes
        $('#wpaicg_provider, #wpaicg_chat_model, #wpaicg_shortcode_google_model, #wpaicg_openrouter_model').change(function () {
            updateImageUploadOption();
        });

        // Listen for changes on the "Enable Image Upload" checkbox
        $('.wpaicg_image_enable').on('change', function() {
            var isImageUploadEnabled = $(this).is(':checked');
            var $streamingCheckbox = $('input[name="wpaicg_stream_nav_option"]');

            if (isImageUploadEnabled) {
                $streamingCheckbox.prop('disabled', true).prop('checked', false);
            } else {
                $streamingCheckbox.prop('disabled', false);
            }
        });

        // Trigger the change event on page load in case the "Enable Image Upload" checkbox is already checked
        $('.wpaicg_image_enable').trigger('change');

        // Function to disable the streaming option if the provider is Google
        function disableStreamingForGoogle() {
            if ($('#wpaicg_provider').val() === 'Google') {
                var $streamingCheckbox = $('input[name="wpaicg_stream_nav_option"]');
                $streamingCheckbox.prop('disabled', true).prop('checked', false);
            }
        }

        // Call the function on page load
        disableStreamingForGoogle();

        // Re-check and update whenever the provider selection changes
        $('#wpaicg_provider').change(function () {
            disableStreamingForGoogle();
        });



        $('#form-chatbox-setting').on('submit', function (e){
            if($('.wpaicg_voice_speed').length) {
                let wpaicg_voice_speed = parseFloat($('.wpaicg_voice_speed').val());
                let wpaicg_voice_pitch = parseFloat($('.wpaicg_voice_pitch').val());
                let wpaicg_voice_name = parseFloat($('.wpaicg_voice_name').val());
                let has_error = false;
                if (wpaicg_voice_speed < 0.25 || wpaicg_voice_speed > 4) {
                    has_error = '<?php printf(
                        /* translators: 1: minimum speed, 2: maximum speed */
                        esc_html__('Please enter valid voice speed value between %1$s and %2$s', 'gpt3-ai-content-generator'), 0.25, 4)?>';
                } else if (wpaicg_voice_pitch < -20 || wpaicg_voice_pitch > 20) {
                    has_error = '<?php printf(
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
            var provider = $('#wpaicg_provider').val();
            if (provider === 'OpenAI') {
                $('#wpaicg_chat_model').prop('disabled', false);
                $('#wpaicg_openrouter_model').prop('disabled', true);
            } else if (provider === 'OpenRouter') {
                $('#wpaicg_chat_model').prop('disabled', true);
                $('#wpaicg_openrouter_model').prop('disabled', false);
            } else {
                $('#wpaicg_chat_model').prop('disabled', true);
                $('#wpaicg_openrouter_model').prop('disabled', true);
            }
        })
        let wpaicg_roles = <?php echo wp_kses_post(json_encode($wpaicg_roles))?>;
        $('.wpaicg_modal_close_second').click(function (){
            $('.wpaicg_modal_close_second').closest('.wpaicg_modal_second').hide();
            $('.wpaicg-overlay-second').hide();
        });
        $(document).on('keypress','.wpaicg_user_token_limit_text,.wpaicg_update_role_limit,.wpaicg_guest_token_limit_text', function (e){
            var charCode = (e.which) ? e.which : e.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
                return false;
            }
            return true;
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
        $('.wpaicg_limit_set_role').click(function (){
            if(!$(this).hasClass('disabled')) {
                if ($('.wpaicg_role_limited').prop('checked')) {
                    let html = '';
                    $.each(wpaicg_roles, function (key, role) {
                        let valueRole = $('.wpaicg_role_'+key).val();
                        html += '<div style="padding: 5px;display: flex;justify-content: space-between;align-items: center;"><label><strong>'+role+'</strong></label><input class="wpaicg_update_role_limit" data-target="'+key+'" value="'+valueRole+'" placeholder="Empty for no-limit" type="text"></div>';
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
        $('.wpaicg-collapse-title').click(function (){
            if(!$(this).hasClass('wpaicg-collapse-active')){
                $('.wpaicg-collapse').removeClass('wpaicg-collapse-active');
                $('.wpaicg-collapse-title span').html('+');
                $(this).find('span').html('-');
                $(this).parent().addClass('wpaicg-collapse-active');
            }
        });
        
        // Function to toggle the visibility and enabled state of related fields based on DB Provider selection
        function toggleRelatedFieldsVisibility() {
            var dbProvider = $('#wpaicg_db_provider').val();
            if(dbProvider === 'qdrant') {
                // Hide and disable Pinecone Index section
                $('#wpaicg_chat_embedding_index').closest('.nice-form-group').hide();
                $('#wpaicg_chat_embedding_index').prop('disabled', true);
                
                // Show and enable Qdrant Collection section
                $('#wpaicg_chat_qdrant_collection').closest('.nice-form-group').show();
                $('#wpaicg_chat_qdrant_collection').prop('disabled', false);
            } else {
                // Show and enable Pinecone Index section
                $('#wpaicg_chat_embedding_index').closest('.nice-form-group').show();
                $('#wpaicg_chat_embedding_index').prop('disabled', false);
                
                // Hide and disable Qdrant Collection section
                $('#wpaicg_chat_qdrant_collection').closest('.nice-form-group').hide();
                $('#wpaicg_chat_qdrant_collection').prop('disabled', true);
            }
        }

        // Event handler for DB Provider dropdown change
        $('#wpaicg_db_provider').on('change', function() {
            toggleRelatedFieldsVisibility();
            updateDbProviderState(); // Ensure other related field states are also updated accordingly
        });

        // Function to update the DB Provider field state
        function updateDbProviderState() {
            var useExcerpt = $('#wpaicg_chat_excerpt').prop('checked');
            var useEmbeddings = $('#wpaicg_chat_embedding').prop('checked');
            var isContentAwareYes = $('#wpaicg_chat_content_aware').val() === 'yes';
            
            // Disable DB Provider if useExcerpt is enabled or content aware is 'no'
            // Enable DB Provider if useEmbeddings is enabled and content aware is 'yes'
            if (useExcerpt || !isContentAwareYes) {
                $('#wpaicg_db_provider').prop('disabled', true).addClass('asdisabled');
                //disable wpaicg_chat_embedding_type
                $('#wpaicg_chat_embedding_type').prop('disabled', true).addClass('asdisabled');
                //disable wpaicg_chat_embedding_index
                $('#wpaicg_chat_embedding_index').prop('disabled', true).addClass('asdisabled');
            } else if (useEmbeddings && isContentAwareYes) {
                $('#wpaicg_db_provider').prop('disabled', false).removeClass('asdisabled');
                toggleRelatedFieldsVisibility();
            }
        }
        // Initial call to set the correct state on page load
        toggleRelatedFieldsVisibility();
        updateDbProviderState();

        $('#wpaicg_chat_excerpt').on('click', function (){
            if($(this).prop('checked')){
                $('#wpaicg_chat_excerpt').removeClass('asdisabled');
                $('#wpaicg_chat_embedding').prop('checked',false);
                $('#wpaicg_chat_embedding').addClass('asdisabled');
                $('#wpaicg_chat_embedding_type').val('openai');
                $('#wpaicg_chat_embedding_type').addClass('asdisabled');
                $('#wpaicg_chat_embedding_type').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_index').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_index').addClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_pdf').addClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf_message').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_pdf_message').addClass('asdisabled');
                $('#wpaicg_chat_pdf_pages').attr('disabled','disabled');
                $('#wpaicg_chat_pdf_pages').addClass('asdisabled');
                $('#wpaicg_chat_embedding_top').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_top').val(1);
                $('#wpaicg_chat_qdrant_collection').attr('disabled','disabled');
                $('#wpaicg_chat_qdrant_collection').addClass('asdisabled');
                // Add call to update the DB Provider field
                updateDbProviderState();
            }
            else{
                $(this).prop('checked',true);
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
                $('#wpaicg_chat_embedding_index').removeAttr('disabled');
                $('#wpaicg_chat_embedding_index').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf').removeAttr('disabled');
                $('#wpaicg_chat_embedding_pdf').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf_message').removeAttr('disabled');
                $('#wpaicg_chat_embedding_pdf_message').removeClass('asdisabled');
                $('#wpaicg_chat_pdf_pages').removeAttr('disabled');
                $('#wpaicg_chat_pdf_pages').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_top').val(1);
                $('#wpaicg_chat_embedding_top').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_top').removeAttr('disabled');
                // qdrant collection
                $('#wpaicg_chat_qdrant_collection').attr('disabled','disabled');
                $('#wpaicg_chat_qdrant_collection').addClass('asdisabled');
                // Add call to update the DB Provider field
                updateDbProviderState();
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

            if (isContentAwareYes) {
                $('#wpaicg_chat_excerpt').removeAttr('disabled');
                $('#wpaicg_chat_excerpt').prop('checked',true);
                $('#wpaicg_chat_embedding').removeAttr('disabled');
                $('#wpaicg_chat_embedding_type').removeAttr('disabled');
                $('#wpaicg_chat_embedding_index').removeAttr('disabled');
                $('#wpaicg_chat_embedding_index').addClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf').removeAttr('disabled');
                $('#wpaicg_chat_embedding_pdf').addClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf_message').removeAttr('disabled');
                $('#wpaicg_chat_embedding_pdf_message').addClass('asdisabled');
                $('#wpaicg_chat_pdf_pages').removeAttr('disabled');
                $('#wpaicg_chat_pdf_pages').addClass('asdisabled');
                $('#wpaicg_chat_embedding').addClass('asdisabled');
                $('#wpaicg_chat_embedding_type').val('openai');
                $('#wpaicg_chat_embedding_type').addClass('asdisabled');
                $('#wpaicg_chat_embedding_top').val(1);
                $('#wpaicg_chat_embedding_top').addClass('asdisabled');
                // Add call to update the DB Provider field
                updateDbProviderState();

            }
            else{
                $('#wpaicg_chat_embedding_type').removeClass('asdisabled');
                $('#wpaicg_chat_excerpt').removeClass('asdisabled');
                $('#wpaicg_chat_embedding').removeClass('asdisabled');
                $('#wpaicg_chat_excerpt').prop('checked',false);
                $('#wpaicg_chat_embedding').prop('checked',false);
                $('#wpaicg_chat_excerpt').attr('disabled','disabled');
                $('#wpaicg_chat_embedding').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_type').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_index').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_index').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_pdf').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_pdf_message').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_pdf_message').removeClass('asdisabled');
                $('#wpaicg_chat_pdf_pages').attr('disabled','disabled');
                $('#wpaicg_chat_pdf_pages').removeClass('asdisabled');
                $('#wpaicg_chat_embedding_top').attr('disabled','disabled');
                $('#wpaicg_chat_embedding_top').removeClass('asdisabled');
                $('#wpaicg_db_provider').prop('disabled', true).addClass('asdisabled');
                // qdtant collection
                $('#wpaicg_chat_qdrant_collection').attr('disabled','disabled');
                $('#wpaicg_chat_qdrant_collection').addClass('asdisabled');
            }
            updateDbProviderState();
        })
        
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

        $('.wpaicg_audio_enable').click(function (){
            if($(this).prop('checked')){
                $('.wpaicg-mic-icon').show();
            }
            else{
                $('.wpaicg-mic-icon').hide();
            }
        })
        $('.wpaicg_image_enable').click(function (){
            if($(this).prop('checked')){
                $('.wpaicg-img-icon').show();
            }
            else{
                $('.wpaicg-img-icon').hide();
            }
        })
        // Listen for changes on the color input for real-time preview updates.
        $('.wpaicg_footer_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chat-shortcode-footer').css('background-color', color);
            $('.wpaicg-chat-shortcode-footer').css('border-top-color', color); // Ensure this is border-top-color
            $('.wpaicg-chatbox-action-bar').css('background-color', color);
        });

        // Listen for changes on the color input for real-time preview updates.
        $('.wpaicgchat_bar_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chatbox-action-bar').css('color', color);
        });

        // listen wpaicg-chat-shortcode-footer to change font color to footer_font_color
        $('.wpaicg_footer_font_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chat-shortcode-footer').css('color', color);
        });

        // listen changes on the wpaicg_font_color
        $('.wpaicg_font_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-user-message').css('color', color);
            $('.wpaicg-conversation-starter').css('color', color);
            $('.wpaicg-ai-message').css('color', color);
        });

        // Listen for changes on input_font_color
        $('.wpaicg_input_font_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input

            // Apply the color to textarea directly
            $('textarea.wpaicg-chat-shortcode-typing, textarea.auto-expand').css('color', color);

            // For the placeholder, we need to update the style tag content to increase specificity
            var placeholderStyleContent =
                `textarea.wpaicg-chat-shortcode-typing::placeholder,
                textarea.auto-expand::placeholder,
                textarea.auto-expand.resizing::placeholder,
                textarea.auto-expand:focus::placeholder {
                    color: ${color} !important;
                }`;

            // Check if the style tag for placeholder colors already exists
            var $placeholderStyles = $('#placeholder-colors');
            if ($placeholderStyles.length) {
                // Update existing style content
                $placeholderStyles.html(placeholderStyleContent);
            } else {
                // Create a new style tag and append it to the head
                $placeholderStyles = $('<style>')
                    .attr('id', 'placeholder-colors')
                    .html(placeholderStyleContent);
                $('head').append($placeholderStyles);
            }
        });

        // listen changes on wpaicg_ai_bg_color 
        $('.wpaicg_ai_bg_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-ai-message').css('background-color', color);
        });
        // listen changes on wpaicg_user_bg_color
        $('.wpaicg_user_bg_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-user-message').css('background-color', color);
            $('.wpaicg-conversation-starter').css('background-color', color);
        });

        // listen changes on wpaicg_bgcolor
        $('.wpaicg_bgcolor').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chat-shortcode').css('background-color', color);
        });

        // listen changes on .wpaicg_bg_text_field
        $('.wpaicg_bg_text_field').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chat-shortcode-typing').css('background-color', color);
        });

        // listen changes on .wpaicg_border_text_field
        $('.wpaicg_border_text_field').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chat-shortcode-typing').css('border-color', color);
        });

        // listen changes on .wpaicg_send_color
        $('.wpaicg_send_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-chat-shortcode-send').css('color', color);
            $('.wpaicg-img-icon').css('color', color);
        });

        // listen changes on .wpaicgchat_thinking_color
        $('.wpaicgchat_thinking_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-bot-thinking').css('color', color);
        });

        // listen changes on wpaicg_mic_color
        $('.wpaicg_mic_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-mic-icon').css('color', color);
        });

        // listen changes on wpaicg_pdf_color
        $('.wpaicg_pdf_color').on('input change', function () {
            var color = $(this).val(); // Get the color value from the input
            $('.wpaicg-pdf-icon').css('color', color);
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
                $('.wpaicg_font_color').val(selectedTheme.fontColor).trigger('change');
                $('.wpaicg_ai_bg_color').val(selectedTheme.aiBgColor).trigger('change');
                $('.wpaicg_user_bg_color').val(selectedTheme.userBgColor).trigger('change');
                $('.wpaicg_bgcolor').val(selectedTheme.windowBgColor).trigger('change');
                $('.wpaicg_input_font_color').val(selectedTheme.inputFontColor).trigger('change');
                $('.wpaicg_border_text_field').val(selectedTheme.borderTextField).trigger('change');
                $('.wpaicg_send_color').val(selectedTheme.sendColor).trigger('change');
                $('.wpaicg_bg_text_field').val(selectedTheme.bgTextField).trigger('change');
                $('.wpaicg_footer_color').val(selectedTheme.footerColor).trigger('change');
                $('.wpaicgchat_thinking_color').val(selectedTheme.thinkingColor).trigger('change');
                $('.wpaicg_footer_font_color').val(selectedTheme.footerfontColor).trigger('change');
                $('.wpaicgchat_bar_color').val(selectedTheme.headericonColor).trigger('change');
                $('.wpaicg_pdf_color').val(selectedTheme.pdfColor).trigger('change');
                $('.wpaicg_mic_color').val(selectedTheme.micColor).trigger('change');
                $('.wpaicg_stop_color').val(selectedTheme.stopColor).trigger('change');
            }
        });


        $('.wpaicg_chat_shortcode_width').on('input', function (){
            var chatbox_width = $(this).val();
            var preview_width = $('.wpaicg-chat-shortcode-preview').width();
            if(chatbox_width.indexOf('%') > -1){
                chatbox_width = chatbox_width.replace('%','');
                chatbox_width = parseFloat(chatbox_width);
                chatbox_width = chatbox_width*preview_width/100;
            }
            else{
                chatbox_width = chatbox_width.replace('px','');
                chatbox_width = parseFloat(chatbox_width);
            }
            if(chatbox_width > preview_width){
                chatbox_width = preview_width;
            }
            $('.wpaicg-chat-shortcode').width(chatbox_width+'px');
            $('.wpaicg-chat-shortcode').attr('data-width',chatbox_width);
            wpaicgChatShortcodeSize();
        });
        $('.wpaicg_chat_rounded,.wpaicg_text_height,wpaicg_text_rounded').on('input', function (){
            $('.wpaicg-chat-shortcode').attr('data-chat_rounded',$('.wpaicg_chat_rounded').val());
            $('.wpaicg-chat-shortcode').attr('data-text_height',$('.wpaicg_text_height').val());
            $('.wpaicg-chat-shortcode').attr('data-text_rounded',$('.wpaicg_text_rounded').val());
            wpaicgChatShortcodeSize();
        })
        $('.wpaicg_chat_shortcode_height').on('input', function (){
            var chatbox_height = $(this).val();
            var preview_width = $(window).height();
            if(chatbox_height.indexOf('%') > -1){
                chatbox_height = chatbox_height.replace('%','');
                chatbox_height = parseFloat(chatbox_height);
                chatbox_height = chatbox_height*preview_width/100;
            }
            else{
                chatbox_height = chatbox_height.replace('px','');
                chatbox_height = parseFloat(chatbox_height);
            }
            if(chatbox_height > preview_width){
                chatbox_height = preview_width;
            }
            $('.wpaicg-chat-shortcode-content ul').height((chatbox_height - 44)+'px');
            $('.wpaicg-chat-shortcode').attr('data-height',chatbox_height);
        });
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
                    button.html('<img width="40" height="40" src="'+attachment.url+'">');
                    $('.wpaicg_chat_icon_url').val(attachment.id);
                }).open();
        });
        $('.wpaicg_chat_shortcode_font_size').on('change', function (){
            var font_size = $(this).val();
            $('.wpaicg-chat-shortcode-messages li').each(function (idx, item){
                $(item).css('font-size',font_size+'px');
            })

            $('.wpaicg-chat-shortcode-typing').css('font-size',font_size+'px');
            $('.wpaicg-conversation-starter').css('font-size',font_size+'px');
        });
        function wpaicgChangeAvatarRealtime(){
            var wpaicg_user_avatar_check = $('.wpaicg_chat_shortcode_you').val()+':';
            var wpaicg_ai_avatar_check = $('.wpaicg_chat_shortcode_ai_name').val()+':';
            if($('.wpaicg_chat_shortcode_use_avatar').prop('checked')){
                wpaicg_user_avatar_check = '<img src="<?php echo get_avatar_url(get_current_user_id())?>" height="40" width="40">';
                wpaicg_ai_avatar_check = '<?php echo esc_html(WPAICG_PLUGIN_URL) . 'admin/images/chatbot.png';?>';
                if($('.wpaicg_chatbox_icon_custom').prop('checked') && $('.wpaicg_chatbox_icon img').length){
                    wpaicg_ai_avatar_check = $('.wpaicg_chatbox_icon img').attr('src');
                }
                wpaicg_ai_avatar_check = '<img src="'+wpaicg_ai_avatar_check+'" height="40" width="40">';
            }

            $('.wpaicg-chat-shortcode-messages li.wpaicg-ai-message').each(function (idx, item){
                $(item).find('.wpaicg-chat-avatar').html(wpaicg_ai_avatar_check);
            });
            $('.wpaicg-chat-shortcode-messages li.wpaicg-user-message').each(function (idx, item){
                $(item).find('.wpaicg-chat-avatar').html(wpaicg_user_avatar_check);
            });
        }
        $('.wpaicg_chat_shortcode_ai_name,.wpaicg_chat_shortcode_you').on('input', function (){
            wpaicgChangeAvatarRealtime();
        })
        $('.wpaicg_chat_shortcode_use_avatar,.wpaicg_chatbox_icon_default,.wpaicg_chatbox_icon_custom').on('click', function (){
            wpaicgChangeAvatarRealtime();
        })
        // Function to manage speech options based on streaming status
        function manageSpeechOptionsBasedOnStreaming() {
            var isStreamingChecked = $('input[name="wpaicg_stream_nav_option"]').is(':checked');

            // "Speech to Text" checkbox and its container
            var $speechToTextCheckbox = $('.wpaicg_audio_enable');
            var $textToSpeechCheckbox = $('.wpaicg_chat_to_speech');

            if(isStreamingChecked) {
                // Disable and uncheck "Speech to Text" and "Text to Speech" if streaming is checked
                $speechToTextCheckbox.prop('disabled', true).prop('checked', false);
                $textToSpeechCheckbox.prop('disabled', true).prop('checked', false);
            } else {
                // Enable "Speech to Text" only if provider is not Azure
                <?php if($wpaicg_provider !== 'Azure'): ?>
                $speechToTextCheckbox.prop('disabled', false);
                <?php endif; ?>

                // Always enable "Text to Speech" - you might need to adjust based on additional conditions
                $textToSpeechCheckbox.prop('disabled', false);
            }
        }

        // Listen for changes on the "Streaming" checkbox
        $('input[name="wpaicg_stream_nav_option"]').on('change', function() {
            manageSpeechOptionsBasedOnStreaming();
        });

        // Call the function on page load to apply the correct initial state
        manageSpeechOptionsBasedOnStreaming();

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
    document.addEventListener('DOMContentLoaded', function() {
    var copyCode = document.querySelector('.toggle-shortcode');

    copyCode.addEventListener('click', function() {
        // Copy text
        navigator.clipboard.writeText(this.textContent).then(() => {
        // Temporarily change the text to indicate copy
        const originalText = this.textContent;
        this.textContent = 'Shortcode copied!';
        setTimeout(() => {
            this.textContent = originalText;
        }, 2000); // Reset text after 2 seconds
        }).catch(err => {
        console.error('Failed to copy: ', err);
        });
    });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('wpaicg_conversation_starters_container');
        // Initialize counter based on the number of input fields already present
        let counter = container.querySelectorAll('.nice-form-group').length;

        window.handleInput = function(event) {
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
            const inputHTML = `<input type="text" name="wpaicg_conversation_starters[]" oninput="handleInput(event)">`;
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
</script>
<script>
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
        const providerSelect = document.getElementById('wpaicg_provider');
        const openaiModels = document.querySelector('.openai-models');
        const googleModels = document.querySelector('.google-models');
        const azureModels = document.querySelector('.azure-models');
        const openrouterModels = document.querySelector('.openrouter-models');

        function toggleModelVisibility() {
            const selectedProvider = providerSelect.value;

            openaiModels.style.display = (selectedProvider === 'OpenAI') ? 'flex' : 'none';
            googleModels.style.display = (selectedProvider === 'Google') ? 'block' : 'none';
            azureModels.style.display = (selectedProvider === 'Azure') ? 'block' : 'none';
            openrouterModels.style.display = (selectedProvider === 'OpenRouter') ? 'block' : 'none';
        }

        providerSelect.addEventListener('change', toggleModelVisibility);

        // Initial toggle based on the preselected provider
        toggleModelVisibility();
    });
</script>
