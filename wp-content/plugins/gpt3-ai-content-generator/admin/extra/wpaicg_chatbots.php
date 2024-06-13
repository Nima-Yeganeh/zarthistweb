<?php
if ( ! defined( 'ABSPATH' ) ) exit;
global $wpdb,$wp;
if(isset($_GET['wpaicg_bot_delete']) && !empty($_GET['wpaicg_bot_delete'])){
    if(!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'wpaicg_delete_'.sanitize_text_field($_GET['wpaicg_bot_delete']))){
        die(esc_html__('Nonce verification failed','gpt3-ai-content-generator'));
    }
    wp_delete_post(sanitize_text_field($_GET['wpaicg_bot_delete']));
    echo '<script>window.location.href = "'.admin_url('admin.php?page=wpaicg_chatgpt&action=bots').'"</script>';
    exit;
}
$wpaicg_provider = '';
$wpaicg_custom_models = get_option('wpaicg_custom_models',array());
$table = $wpdb->prefix . 'wpaicg';
$existingValue = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE name = %s", 'wpaicg_settings' ), ARRAY_A );
$wpaicg_chat_temperature = get_option('wpaicg_chat_temperature',$existingValue['temperature']);
$wpaicg_chat_max_tokens = get_option('wpaicg_chat_max_tokens',$existingValue['max_tokens']);
$wpaicg_chat_top_p = get_option('wpaicg_chat_top_p',$existingValue['top_p']);
$wpaicg_chat_best_of = get_option('wpaicg_chat_best_of',$existingValue['best_of']);
$wpaicg_chat_frequency_penalty = get_option('wpaicg_chat_frequency_penalty',$existingValue['frequency_penalty']);
$wpaicg_chat_presence_penalty = get_option('wpaicg_chat_presence_penalty',$existingValue['presence_penalty']);
$wpaicg_chat_icon = 'default';
$wpaicg_chat_fontsize = '13';
$wpaicg_chat_fontcolor = '#495057';
$wpaicg_chat_bgcolor = '#f8f9fa';
$wpaicg_bg_text_field = '#ffffff';
$wpaicg_send_color = '#d1e8ff';
$wpaicg_footer_font_color = '#495057';
$wpaicg_input_font_color = '#495057';
$wpaicg_footer_color = '#ffffff';
$wpaicg_border_text_field = '#cccccc'; 
$wpaicg_chat_thinking_color = '#d1e8ff';
$wpaicg_footer_text = '';
$wpaicg_user_bg_color = '#ccf5e1';
$wpaicg_ai_bg_color = '#d1e8ff';
$wpaicg_use_avatar = false;
$wpaicg_ai_avatar = 'default';
$wpaicg_ai_avatar_id = '';
$wpaicg_chat_width = '450';
$wpaicg_chat_height = '50%';
$wpaicg_chat_position = 'left';
$wpaicg_chat_tone = 'friendly';
$wpaicg_user_aware = 'no';
$wpaicg_chat_proffesion = 'none';
$wpaicg_chat_icon_url = '';
$wpaicg_chat_remember_conversation = 'yes';
$wpaicg_chat_content_aware = 'yes';
$wpaicg_pinecone_api = get_option('wpaicg_pinecone_api','');
$wpaicg_pinecone_environment = get_option('wpaicg_pinecone_environment','');
$wpaicg_save_logs = false;
$wpaicg_log_notice = false;
$wpaicg_log_notice_message = __('Please note that your conversations will be recorded.','gpt3-ai-content-generator');
$wpaicg_conversation_cut = 10;
$wpaicg_chat_embedding = false;
$wpaicg_chat_addition = false;
$wpaicg_chat_addition_text = false;
$wpaicg_chat_embedding_type = false;
$wpaicg_chat_vectordb = 'pinecone';
$wpaicg_chat_embedding_top = false;
$wpaicg_audio_enable = false;
$wpaicg_image_enable = false;
$wpaicg_mic_color = '#d1e8ff';
$wpaicg_stop_color = '#d1e8ff';
$wpaicg_user_limited = false;
$wpaicg_guest_limited = false;
$wpaicg_user_tokens = 0;
$wpaicg_guest_tokens = 0;
$wpaicg_reset_limit = 0;
$wpaicg_limited_message = __('You have reached your token limit.','gpt3-ai-content-generator');
$wpaicg_include_footer = 0;
$wpaicg_roles = wp_roles()->get_names();
$wpaicg_chat_close_btn = false;
$wpaicg_chat_download_btn = false;
$wpaicg_chat_clear_btn = false;
$wpaicg_streaming = false;
$wpaicg_chat_fullscreen = false;
$wpaicg_elevenlabs_api = get_option('wpaicg_elevenlabs_api', '');
$wpaicg_google_api_key = get_option('wpaicg_google_api_key', '');
$wpaicg_google_voices = get_option('wpaicg_google_voices',[]);
$wpaicg_all_google_models = get_option('wpaicg_google_model_list',[]);
$wpaicg_pinecone_indexes = get_option('wpaicg_pinecone_indexes','');
$wpaicg_pinecone_indexes = empty($wpaicg_pinecone_indexes) ? array() : json_decode($wpaicg_pinecone_indexes,true);
$wpaicg_qdrant_collections = get_option('wpaicg_qdrant_collections',[]);
$wpaicg_qdrant_collections = empty($wpaicg_qdrant_collections) ? array() : $wpaicg_qdrant_collections;
$wpaicg_typewriter_effect = get_option('wpaicg_typewriter_effect', false);
$wpaicg_typewriter_speed = get_option('wpaicg_typewriter_speed', 1);
$conversation_starters = []; 

?>
<style>
    .wpaicg-chat-shortcode {
        width: <?php echo esc_html($wpaicg_chat_width)?>;
        background-color: <?php echo esc_html($wpaicg_chat_bgcolor)?>;
        border-radius: 4px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        max-width: 100%;
        overflow: hidden;
        border: 1px solid #E0E0E0;
        transition: box-shadow 0.3s ease;
        margin-right: 20px; /* Adjust as needed */
    }
    .wpaicg-chat-shortcode:hover {
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2); /* Enhanced shadow on hover for interaction feedback */
    }
    .wpaicg-chat-shortcode-content {
        overflow-y: auto;
        flex-grow: 1;
        padding: 15px; /* Increased padding for more space around messages */
    }
    .wpaicg-chat-shortcode-content ul {
        overflow-y: auto;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .wpaicg-chat-shortcode-footer {
        color: <?php echo esc_html($wpaicg_footer_font_color)?>;
        background: <?php echo esc_html($wpaicg_footer_color)?>;
        font-size: 0.75rem;
        padding: 12px 20px;
        border-top: 1px solid <?php echo esc_html($wpaicg_footer_color)?>;
    }
    .wpaicg-chat-shortcode-footer a {
        color: inherit;
        text-decoration: none;
    }
    
    .wpaicg-chat-shortcode-content ul li {
        color: <?php echo esc_html($wpaicg_chat_fontcolor)?>;
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        margin-right: 10px;
        padding: 10px;
        border-radius: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        width: fit-content;
    }

    .wpaicg-chat-shortcode-content ul .wpaicg-user-message {
        margin-left: auto; /* This pushes the user messages to the right */
        background-color: #ccf5e1; /* Example background color for user messages */
    }

    .wpaicg-chat-shortcode-content ul li .wpaicg-chat-message {
        color: inherit;
    }
    .wpaicg-chat-shortcode-content ul li strong {
        font-weight: bold;
        margin-right: 5px;
        float: left;
        color: inherit;
    }
    .wpaicg-chat-shortcode-content ul li p {
        font-size: inherit;
    }

    .wpaicg-chat-shortcode-content ul li p {
        margin: 0;
        padding: 0;
    }
    .wpaicg-chat-shortcode-content ul li p:after {
        clear: both;
        display: block;
    }
    .wpaicg-chat-shortcode .wpaicg-bot-thinking {
        bottom: 0;
        font-size: 11px;
        display: none;
        padding-left: 15px;
    }

    .wpaicg-chat-shortcode .wpaicg-ai-message .wpaicg-chat-message,
    .wpaicg-chat-shortcode .wpaicg-user-message .wpaicg-chat-message {
        color: inherit;
    }

    .wpaicg-chat-message-error {
        color: #f00;
    }
    .wpaicg-chat-message {
        line-height: auto;
    }

    .wpaicg-jumping-dots span {
        position: relative;
        bottom: 0;
        -webkit-animation: wpaicg-jump 1500ms infinite;
        animation: wpaicg-jump 2s infinite;
    }
    .wpaicg-jumping-dots .wpaicg-dot-1 {
        -webkit-animation-delay: 200ms;
        animation-delay: 200ms;
    }
    .wpaicg-jumping-dots .wpaicg-dot-2 {
        -webkit-animation-delay: 400ms;
        animation-delay: 400ms;
    }
    .wpaicg-jumping-dots .wpaicg-dot-3 {
        -webkit-animation-delay: 600ms;
        animation-delay: 600ms;
    }

    .wpaicg-chat-shortcode-type {
        display: flex;
        align-items: center;
        padding: 15px;
        color: <?php echo esc_html($wpaicg_send_color)?>;
    }

    .wpaicg-chat-shortcode-send {
        color: <?php echo esc_html($wpaicg_send_color)?>;
    }

    textarea.wpaicg-chat-shortcode-typing {
        flex: 1;
        border: 1px solid <?php echo esc_html($wpaicg_border_text_field)?>;
        background-color: <?php echo esc_html($wpaicg_bg_text_field)?>;
        resize: vertical;
        border-radius: 3px;
        line-height: 2;
        padding-left: 1em;
        color: <?php echo esc_html($wpaicg_input_font_color)?>;
        font-size: <?php echo esc_html($wpaicg_chat_fontsize)?>px;
    }

    textarea.auto-expand {
        overflow: hidden; /* Prevents scrollbar flash during size adjustment */
        transition: box-shadow 0.5s ease-in-out;
        color: <?php echo esc_html($wpaicg_input_font_color)?>;
        line-height: 2;
    }

    textarea.auto-expand.resizing {
        transition: box-shadow 0.5s ease-in-out;
        box-shadow: 0 0 12px rgba(81, 203, 238, 0.8);
        color: <?php echo esc_html($wpaicg_input_font_color)?>;
        line-height: 2;
    }


    textarea.auto-expand:focus {
        outline: none;
        box-shadow: 0 0 5px rgba(81, 203, 238, 1);
        color: <?php echo esc_html($wpaicg_input_font_color)?>;
        line-height: 2;
    }

    textarea.wpaicg-chat-shortcode-typing::placeholder {
        color: <?php echo esc_html($wpaicg_input_font_color)?>;
    }

    @-webkit-keyframes wpaicg-jump {
        0%   {bottom: 0px;}
        20%  {bottom: 5px;}
        40%  {bottom: 0px;}
    }

    @keyframes wpaicg-jump {
        0%   {bottom: 0px;}
        20%  {bottom: 5px;}
        40%  {bottom: 0px;}
    }
    /* Adjustments for screens that are 768px wide or less (typical for tablets and smartphones) */
    @media (max-width: 768px) {
        .wpaicg-chat-shortcode {
            /* Adjust the width and right margin for smaller screens */
            width: auto; /* This makes the chat window adapt to the screen size */
            margin-right: 10px; /* Smaller margin for smaller devices */
            margin-left: 10px; /* Add some space on the left as well */
        }
    }

    /* Further adjustments for very small screens, like iPhones */
    @media (max-width: 480px) {
        .wpaicg-chat-shortcode {
            /* You might want even smaller margins here */
            margin-right: 5px;
            margin-left: 5px;
        }
    }
    .wpaicg-chat-shortcode .wpaicg-mic-icon {
        color: <?php echo esc_html($wpaicg_mic_color)?>;
    }
    .wpaicg-chat-shortcode .wpaicg-img-icon {
        color: <?php echo esc_html($wpaicg_send_color)?>;
    }
    .wpaicg-chat-shortcode .wpaicg-pdf-icon {
        color: <?php echo esc_html($wpaicg_send_color)?>;
    }
    .wpaicg-chat-shortcode .wpaicg-pdf-remove {
        color: <?php echo esc_html($wpaicg_send_color)?>;
        font-size: 33px;
        justify-content: center;
        align-items: center;
        width: 16px;
        height: 16px;
        line-height: unset;
        font-family: Arial, serif;
        border-radius: 50%;
        font-weight: normal;
        padding: 0;
        margin: 0;
    }
    .wpaicg-chat-shortcode .wpaicg-pdf-loading {
        border-color: <?php echo esc_html($wpaicg_send_color)?>;
        border-bottom-color: transparent;
    }
    .wpaicg-chat-shortcode .wpaicg-mic-icon.wpaicg-recording {
        color: <?php echo esc_html($wpaicg_stop_color)?>;
    }
    .wpaicg_chat_additions {
        display: flex;
        justify-content: center;
        align-items: center;
        position: absolute;
        right: 20px;
    }

    .wpaicg-chat-shortcode .wpaicg-chatbox-action-bar {
        position: absolute;
        top: 0; /* Position it at the top of the chat window */
        right: 0;
        left: 0; /* Ensure it spans the full width */
        height: 40px;
        padding: 0 10px;
        display: none;
        justify-content: center;
        align-items: center;
        background: <?php echo esc_html($wpaicg_footer_color)?>;
        color: #ffffff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: background-color 0.3s ease;
    }

    /* Button Styles */
    .wpaicg-chatbox-download-btn,
    .wpaicg-chatbox-clear-btn,
    .wpaicg-chatbox-fullscreen,
    .wpaicg-chatbox-close-btn {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center; /* Center content */
        margin: 0 5px; /* Adjust spacing between buttons */
        transition: background-color 0.3s ease; /* Smooth transition for interactions */
    }

    /* SVG Icon Adjustments */
    .wpaicg-chatbox-download-btn svg,
    .wpaicg-chatbox-clear-btn svg,
    .wpaicg-chatbox-fullscreen svg,
    .wpaicg-chatbox-close-btn svg {
        fill: currentColor;
        height: 16px; /* Adjust size for visibility */
        width: 16px;
    }
    /* Hover States for Button Interactions */
    .wpaicg-chatbox-download-btn:hover,
    .wpaicg-chatbox-clear-btn:hover,
    .wpaicg-chatbox-fullscreen:hover,
    .wpaicg-chatbox-close-btn:hover {
        background-color: rgba(0, 0, 0, 0.1); /* Slight highlight on hover */
    }
    .wpaicg-chatbox-fullscreen svg.wpaicg-exit-fullscreen {
        display: none;
        fill: none;
        height: 16px;
        width: 16px;
    }
    /* Fullscreen Button SVG Paths */
    .wpaicg-chatbox-fullscreen svg.wpaicg-exit-fullscreen path,
    .wpaicg-chatbox-fullscreen svg.wpaicg-active-fullscreen path {
        fill: currentColor; /* Ensure visibility */
    }
    .wpaicg-chatbox-fullscreen svg.wpaicg-active-fullscreen {
        fill: none;
        height: 16px;
        width: 16px;
    }

    /* Adjusting visibility for fullscreen icons */
    .wpaicg-chatbox-fullscreen.wpaicg-fullscreen-box svg.wpaicg-active-fullscreen {
        display: none; /* Hide when in fullscreen */
    }
    .wpaicg-chatbox-fullscreen.wpaicg-fullscreen-box svg.wpaicg-exit-fullscreen {
        display: block; /* Show exit icon in fullscreen mode */
    }
    .wpaicg-fullscreened {
        border-radius: 0;
        border: none; /* Remove border in fullscreen */
    }
    .wpaicg-fullscreened .wpaicg-chatbox-action-bar {
        border-radius: 0; /* Remove border-radius in fullscreen */
        top: 0;
        z-index: 99;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }
    /* Ensures that the chat window does not extend beyond the viewport height */
    .wpaicg-fullscreened .wpaicg-chat-shortcode {
        height: 100vh; /* Full viewport height */
        width: 100vw; /* Full viewport width */
        max-width: 100vw; /* Prevents exceeding the viewport width */
        border-radius: 0; /* Removes border radius in fullscreen */
        border: none; /* Removes border in fullscreen */
        box-shadow: none; /* Removes shadow in fullscreen */
        display: flex;
        flex-direction: column;
    }
    
    /* Adjusts the chat content area to not overlap with the message input area */
    .wpaicg-fullscreened .wpaicg-chat-shortcode-content {
        padding: 15px;
        overflow-y: auto; /* Adjust if content exceeds the container height */
    }


    .wpaicg-fullscreened .wpaicg-chat-shortcode-footer,
    .wpaicg-fullscreened .wpaicg-chat-shortcode-type {
        position: fixed;
        bottom: 0;
        width: calc(100% - 30px); /* Adjust padding to prevent overlap */
        padding: 45px 15px; /* Ensure consistent padding */
    }

    .wpaicg-fullscreened .wpaicg-chat-shortcode .wpaicg-bot-thinking {
        position: fixed;
        bottom: 0;
        width: 100%;
        padding: 15px;
    }

    .wpaicg-chat-shortcode .wpaicg-chatbox-action-bar {
        position: relative;
        top: 0;
        display: flex;
        justify-content: flex-end;
    }
</style>
<style>
    .wp-picker-holder{
        z-index: 99;
    }
    .wpaicg-bot-wizard{}
    .wpaicg-bot-wizard .wpaicg-mb-10{}
    .wpaicg-bot-wizard .wpaicg-form-label{
        width: 40%;
        display: inline-block;
    }
    .wpaicg-bot-wizard input[type=text],.wpaicg-bot-wizard input[type=number],.wpaicg-bot-wizard select{
        width: 55%;
        display: inline-block;
    }
    .wpaicg-bot-wizard textarea{
        width: 59%;
        display: inline-block;
    }
    .wpaicg_modal{
        top: 5%;
        height: 90%;
        position: relative;
    }
    .wpaicg_modal_content{
        max-height: calc(100% - 103px);
        overflow-y: auto;
    }
    .wp-picker-holder{
        position: absolute;
    }
  
    .wpaicg_chatbox_avatar,.wpaicg_chatbox_icon{
        cursor: pointer;
    }
    .asdisabled{
        background: #ebebeb!important;
    }
    .wpaicg-bot-footer{
        width: calc(100% - 31px);
        display: flex;
        bottom: 0px;
        position: absolute;
        margin-left: -21px;
        padding: 10px;
        border-top: 1px solid <?php echo esc_html($wpaicg_footer_color)?>;
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
    }
    .wpaicg-bot-footer > div{
        flex: 1;
    }

    
    .wpaicg-grid-3{
        border: 1px solid #d9d9d9;
        border-radius: 5px;
        padding: 10px;
    }
    .wpaicg-jumping-dots span {
        position: relative;
        bottom: 0;
        -webkit-animation: wpaicg-jump 1500ms infinite;
        animation: wpaicg-jump 2s infinite;
    }
    .wpaicg-jumping-dots .wpaicg-dot-1{
        -webkit-animation-delay: 200ms;
        animation-delay: 200ms;
    }
    .wpaicg-jumping-dots .wpaicg-dot-2{
        -webkit-animation-delay: 400ms;
        animation-delay: 400ms;
    }
    .wpaicg-jumping-dots .wpaicg-dot-3{
        -webkit-animation-delay: 600ms;
        animation-delay: 600ms;
    }
    @-webkit-keyframes wpaicg-jump {
        0%   {bottom: 0px;}
        20%  {bottom: 5px;}
        40%  {bottom: 0px;}
    }

    @keyframes wpaicg-jump {
        0%   {bottom: 0px;}
        20%  {bottom: 5px;}
        40%  {bottom: 0px;}
    }
    .wpaicg-notice-info {
    color: #31708f;
    padding: 5px;
    border: 1px solid #31708f;
    border-radius: 3px;
    margin-bottom: 10px;
    }
</style>
<div id="exportMessage" style="display: none;" class="notice notice-success"></div>
<div class="wpaicg-create-bot-default" style="display: none">
    <div class="wpaicg-grid">
        <div class="wpaicg-grid-3">
            <form action="" method="post" class="wpaicg-bot-form">
                <?php wp_nonce_field('wpaicg_chatbot_save'); ?>
                <input value="<?php echo esc_html($wpaicg_chat_icon_url)?>" type="hidden" name="bot[icon_url]" class="wpaicg_chatbot_icon_url">
                <input value="<?php echo esc_html($wpaicg_ai_avatar_id)?>" type="hidden" name="bot[ai_avatar_id]" class="wpaicg_chatbot_ai_avatar_id">
                <input value="" type="hidden" name="bot[id]" class="wpaicg_chatbot_id">
                <input value="wpaicg_update_chatbot" type="hidden" name="action">
                <!--Type-->
                <div class="wpaicg-bot-type wpaicg-bot-wizard">
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Give your bot a name','gpt3-ai-content-generator')?></label>
                        <input type="text" name="bot[name]" class="wpaicg_chatbot_name">
                    </div>
                    <div class="nice-form-group">
                        <label><strong><?php echo esc_html__('What would you like to create?','gpt3-ai-content-generator')?></strong></label>
                    </div>
                    <div class="nice-form-group">
                        <label>
                            <input type="radio" name="bot[type]" value="shortcode" class="wpaicg_chatbot_type_shortcode">&nbsp;<?php echo esc_html__('Shortcode','gpt3-ai-content-generator')?>
                        </label>
                    </div>
                    <div class="nice-form-group">
                        <label>
                            <input type="radio" name="bot[type]" value="widget" class="wpaicg_chatbot_type_widget">&nbsp;<?php echo esc_html__('Widget','gpt3-ai-content-generator')?>
                        </label>
                    </div>
                    <div class="wpaicg-mb-10 wpaicg-widget-pages" style="display: none">
                        <div class="nice-form-group">
                            <label><strong><?php echo esc_html__('Where would you like to display it?','gpt3-ai-content-generator')?></strong></label>
                        </div>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Page / Post ID','gpt3-ai-content-generator')?></label>
                            <input type="text" class="wpaicg_chatbot_pages" name="bot[pages]" placeholder="<?php echo esc_html__('Example: 1,2,3','gpt3-ai-content-generator')?>">
                        </div>
                    </div>
                    <div class="wpaicg-mb-10 wpaicg_chatbot_position" style="display: none">
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Position','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="nice-form-group">
                            <input <?php echo $wpaicg_chat_position == 'left' ? ' checked': ''?> type="radio" value="left" name="bot[position]" class="wpaicg_chatbot_position_left"> <?php echo esc_html__('Bottom Left','gpt3-ai-content-generator')?>
                            <input <?php echo $wpaicg_chat_position == 'right' ? ' checked': ''?> type="radio" value="right" name="bot[position]" class="wpaicg_chatbot_position_right"> <?php echo esc_html__('Bottom Right','gpt3-ai-content-generator')?>
                        </div>
                    </div>
                    <div class="wpaicg-bot-footer">
                        <div>
                            <button type="button" class="button button-primary wpaicg-bot-step" data-type="parameters"><?php echo esc_html__('Next','gpt3-ai-content-generator')?></button>
                        </div>
                        <button class="button button-primary wpaicg-chatbot-submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                    </div>
                </div>
                <!--Language-->
                <div class="wpaicg-bot-language wpaicg-bot-wizard" style="display: none">
                    <h3><?php echo esc_html__('Interface','gpt3-ai-content-generator')?></h3>

                    <div class="wpaicg-bot-footer">
                        <div>
                            <button type="button" class="button wpaicg-bot-step" data-type="style"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
                            <button type="button" class="button button-primary wpaicg-bot-step" data-type="audio"><?php echo esc_html__('Next','gpt3-ai-content-generator')?></button>
                        </div>
                        <button class="button button-primary wpaicg-chatbot-submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                    </div>
                </div>
                <!--Style-->
                <div class="wpaicg-bot-style wpaicg-bot-wizard" style="display: none">
                    <h3 style="margin-top: -0.2em;"><?php echo esc_html__('Themes','gpt3-ai-content-generator')?></h3>
                    <div class="options-container" style="display: flex; justify-content: space-between;margin-top: -0.5em;">
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
                    <h3><?php echo esc_html__('Chat Window','gpt3-ai-content-generator')?></h3>
                    <!-- Color Pickers Container -->
                    <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: -1em;">
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Box','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_chat_bgcolor)?>" type="color" class="wpaicg_chatbot_bgcolor" name="bot[bgcolor]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_chat_fontcolor)?>" type="color" class="wpaicg_chatbot_fontcolor" name="bot[fontcolor]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('AI','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_ai_bg_color)?>" type="color" class="wpaicg_chatbot_ai_bg_color" name="bot[ai_bg_color]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('User','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_user_bg_color)?>" type="color" class="wpaicg_chatbot_user_bg_color" name="bot[user_bg_color]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Width','gpt3-ai-content-generator')?></label>
                            <input style="width: 50px;font-size: 11px;height: 32px;" value="<?php echo esc_html($wpaicg_chat_width)?>" class="wpaicg_chatbot_width" min="100" type="text" name="bot[width]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Height','gpt3-ai-content-generator')?></label>
                            <input style="width: 50px;font-size: 11px;height: 32px;" value="<?php echo esc_html($wpaicg_chat_height)?>" class="wpaicg_chatbot_height" min="100" type="text" name="bot[height]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Radius','gpt3-ai-content-generator')?></label>
                            <input style="width: 50px;font-size: 11px;height: 32px;" value="8" type="number" min="0" class="wpaicg_chatbot_chat_rounded" name="bot[chat_rounded]">
                        </div>
                        <div class="nice-form-group" style="flex: 1;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                            <select style="width: 50px;font-size: 11px;height: 32px;padding: 0 1.2em;background-position: right;" name="bot[fontsize]" class="wpaicg_chatbot_fontsize">
                                <?php
                                for($i = 10; $i <= 30; $i++){
                                    echo '<option'.($wpaicg_chat_fontsize == $i ? ' selected' :'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <!-- Text Field -->
                    <h3><?php echo esc_html__('Text Field','gpt3-ai-content-generator')?></h3>
                    <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: -1em;">
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Box','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_bg_text_field)?>" type="color" class="wpaicg_chatbot_bg_text_field" name="bot[bg_text_field]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_input_font_color)?>" type="color" class="wpaicg_chatbot_input_font_color" name="bot[input_font_color]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Border','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_border_text_field)?>" type="color" class="wpaicg_chatbot_border_text_field" name="bot[border_text_field]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Button','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_send_color)?>" type="color" class="wpaicg_chatbot_send_color" name="bot[send_color]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Mic','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_mic_color)?>" type="color" class="wpaicg_chatbot_mic_color" name="bot[mic_color]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Stop','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_stop_color)?>" type="color" class="wpaicg_chatbot_stop_color" name="bot[stop_color]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Height','gpt3-ai-content-generator')?></label>
                            <input style="width: 55px;font-size: 11px;height: 32px;" value="40" type="number" min="30" class="wpaicg_chatbot_text_height" name="bot[text_height]">
                        </div>
                        <div class="nice-form-group" style="flex: 1;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Radius','gpt3-ai-content-generator')?></label>
                            <input style="width: 55px;font-size: 11px;height: 32px;" value="8" type="number" min="0" class="wpaicg_chatbot_text_rounded" name="bot[text_rounded]">
                        </div>
                    </div>
                    <!-- Header / Footer -->
                    <h3><?php echo esc_html__('Header / Footer','gpt3-ai-content-generator')?></h3>
                    <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: -1em;">
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Box','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_footer_color)?>" type="color" class="wpaicg_chatbot_footer_color" name="bot[footer_color]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Font','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="<?php echo esc_html($wpaicg_footer_font_color)?>" type="color" class="wpaicg_chatbot_footer_font_color" name="bot[footer_font_color]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Icons','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="#495057" type="color" class="wpaicg_chatbot_bar_color" name="bot[bar_color]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Wait','gpt3-ai-content-generator')?></label>
                            <input style="width: 30px;height: 32px;" value="#495057" type="color" class="wpaicg_chatbot_thinking_color" name="bot[thinking_color]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Fullscreen','gpt3-ai-content-generator')?></label>
                            <input style="border-color: #10b981;margin-top: 4px;" <?php echo $wpaicg_chat_fullscreen ? ' checked':''?> value="1" type="checkbox" class="switch wpaicg_chatbot_fullscreen" name="bot[fullscreen]">
                        </div>
                        <div class="nice-form-group" style="flex: 1; padding-right: 3px;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Download','gpt3-ai-content-generator')?></label>
                            <input style="border-color: #10b981;margin-top: 4px;" <?php echo $wpaicg_chat_download_btn ? ' checked':''?> value="1" type="checkbox" class="switch wpaicg_chatbot_download_btn" name="bot[download_btn]">
                        </div>
                        <div class="nice-form-group" style="flex: 1;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Clear','gpt3-ai-content-generator')?></label>
                            <input style="border-color: #10b981;margin-top: 4px;" <?php echo $wpaicg_chat_clear_btn ? ' checked':''?> value="1" type="checkbox" class="switch wpaicg_chatbot_clear_btn" name="bot[clear_btn]">
                        </div>
                        <div class="nice-form-group" style="flex: 1;">
                            <label style="font-size: 11px;"><?php echo esc_html__('Close','gpt3-ai-content-generator')?></label>
                            <input style="border-color: #10b981;margin-top: 4px;" <?php echo $wpaicg_chat_close_btn ? ' checked':''?> value="1" type="checkbox" class="switch wpaicg_chatbot_close_btn" name="bot[close_btn]">
                        </div>
                    </div>
                    <div class="nice-form-group">
                        <input <?php echo $wpaicg_use_avatar ? ' checked':''?> value="1" type="checkbox" class="wpaicg_chatbot_use_avatar" name="bot[use_avatar]">
                        <label><?php echo esc_html__('Use Avatar','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('AI Avatar (40x40)','gpt3-ai-content-generator')?>:</label>
                        <div style="display: inline-flex; align-items: center">
                            <input checked class="wpaicg_chatbox_avatar_default wpaicg_chatbot_ai_avatar_default" type="radio" value="default" name="bot[ai_avatar]">
                            <div style="text-align: center">
                                <img style="display: block;width: 40px; height: 40px" src="<?php echo esc_html(WPAICG_PLUGIN_URL).'admin/images/chatbot.png'?>"<br>
                                <strong><?php echo esc_html__('Default','gpt3-ai-content-generator')?></strong>
                            </div>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="radio" class="wpaicg_chatbox_avatar_custom wpaicg_chatbot_ai_avatar_custom" value="custom" name="bot[ai_avatar]">
                            <div style="text-align: center">
                                <div class="wpaicg_chatbox_avatar">
                                    <svg width="40px" height="40px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M246.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 109.3V320c0 17.7 14.3 32 32 32s32-14.3 32-32V109.3l73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128zM64 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 53 43 96 96 96H352c53 0 96-43 96-96V352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V352z"/></svg><br>
                                </div>
                                <strong><?php echo esc_html__('Custom','gpt3-ai-content-generator');?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="wpaicg-widget-icon" style="display: none">
                        <div class="wpaicg-mb-10">
                            <label class="wpaicg-form-label"><?php echo esc_html__('Widget Icon (75x75)','gpt3-ai-content-generator')?>:</label>
                            <div style="display: inline-flex; align-items: center">
                                <input checked class="wpaicg_chatbox_icon_default wpaicg_chatbot_icon_default" type="radio" value="default" name="bot[icon]">
                                <div style="text-align: center">
                                    <img style="display: block;width: 40px; height: 40px" src="<?php echo esc_html(WPAICG_PLUGIN_URL).'admin/images/chatbot.png'?>"<br>
                                    <strong><?php echo esc_html__('Default','gpt3-ai-content-generator')?></strong>
                                </div>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" class="wpaicg_chatbox_icon_custom wpaicg_chatbot_icon_custom" value="custom" name="bot[icon]">
                                <div style="text-align: center">
                                    <div class="wpaicg_chatbox_icon">
                                        <svg width="40px" height="40px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M246.6 9.4c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 109.3V320c0 17.7 14.3 32 32 32s32-14.3 32-32V109.3l73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128zM64 352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 53 43 96 96 96H352c53 0 96-43 96-96V352c0-17.7-14.3-32-32-32s-32 14.3-32 32v64c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V352z"/></svg><br>
                                    </div>
                                    <strong><?php echo esc_html__('Custom','gpt3-ai-content-generator')?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wpaicg-chatbot-delay-time" style="display: none">
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Display Widget After','gpt3-ai-content-generator')?></label>
                            <input placeholder="<?php echo esc_html__('in seconds. eg. 5','gpt3-ai-content-generator')?>" value="" type="text" class="wpaicg_chatbot_delay_time" name="bot[delay_time]">
                        </div>
                    </div>
                    <div class="wpaicg-bot-footer">
                        <div>
                        <button type="button" class="button wpaicg-bot-step" data-type="context"><?php echo esc_html__('Previous','gpt3-ai-content-generator');?></button>
                        <button type="button" class="button button-primary wpaicg-bot-step" data-type="custom"><?php echo esc_html__('Next','gpt3-ai-content-generator');?></button>
                        </div>
                        <button class="button button-primary wpaicg-chatbot-submit"><?php echo esc_html__('Save','gpt3-ai-content-generator');?></button>
                    </div>
                </div>
                <!--Parameters-->
                <div class="wpaicg-bot-parameters wpaicg-bot-wizard" style="display: none">
                    <h3 style="margin-top: -0.2em;"><?php echo esc_html__('AI Settings','gpt3-ai-content-generator');?></h3>
                    <div class="nice-form-group" style="margin-top: -0.3em;">
                        <!-- AI Provider -->
                        <label><?php echo esc_html__('AI Provider', 'gpt3-ai-content-generator'); ?></label>
                        <select id="wpaicg_provider" class="wpaicg_provider" name="bot[provider]">
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
                            $current_model = 'gpt-3.5-turbo';
                            
                            function display_options($models, $selected_model){
                                foreach ($models as $model_key => $model_name): ?>
                                    <option value="<?php echo esc_attr($model_key); ?>"<?php selected($model_key, $selected_model); ?>><?php echo esc_html($model_name); ?></option>
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
                            <label><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                            <select class="wpaicg_chatbot_model" id="wpaicg_chat_model_openai" name="bot[model]" value="<?php echo esc_attr($current_model); ?>">
                                <optgroup label="GPT-4">
                                    <?php display_options($gpt4_models, $current_model); ?>
                                </optgroup>
                                <optgroup label="GPT-3.5">
                                    <?php display_options($gpt35_models, $current_model); ?>
                                </optgroup>
                                <optgroup label="Custom Models">
                                    <?php display_custom_model_options($custom_models, $current_model); ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="google-models" style="display: none;">
                        <?php
                        // Retrieve the serialized model list from the database
                        $serialized_google_models = get_option('wpaicg_google_model_list', '');
                        // Unserialize the model list; ensure it is an array
                        $google_models = maybe_unserialize($serialized_google_models);
                        if (empty($google_models)) {
                            // Fallback to default model if the list is empty or not set
                            $google_models = ['gemini-pro' => 'Gemini Pro'];
                        } else {
                            // Transform the array to use the same model string as both key and value for compatibility
                            $google_models = array_combine($google_models, array_map(function($model) {
                                return ucwords(str_replace('-', ' ', $model));  // Capitalize and replace hyphens with spaces
                            }, $google_models));
                        }

                        $google_model = get_option('wpaicg_google_default_model', 'gemini-pro');
                        ?>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                            <select class="wpaicg_chatbot_model" id="wpaicg_chat_model_google" name="bot[model]">
                                <?php foreach ($google_models as $model_key => $model_name): 
                                    $is_vision_model = strpos(strtolower($model_name), 'vision') !== false;  // Check if the model name contains 'vision'
                                ?>
                                    <option value="<?php echo esc_attr($model_key); ?>" <?php if ($is_vision_model) echo 'disabled'; ?><?php selected($model_key, $google_model); ?>><?php echo esc_html($model_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="azure-models" style="display: none;">
                        <div class="nice-form-group">
                            <?php $azure_model = get_option('wpaicg_azure_deployment', ''); ?>
                            <label><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                            <input type="text" class="wpaicg_chatbot_model" id="wpaicg_chat_model_azure" name="bot[model]" value="<?php echo esc_attr($azure_model); ?>" >
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

                        $openrouter_selected_model = get_option('wpaicg_openrouter_default_model', 'openrouter/auto');
                        ?>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Model', 'gpt3-ai-content-generator'); ?></label>
                            <select class="wpaicg_chatbot_model" id="wpaicg_chat_model_openrouter" name="bot[model]" style="width: 211.75px;margin-right: 5px;">
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
                        </div>
                    </div>

                    <fieldset class="nice-form-group" style="margin-top: 1em;">
                    <legend><?php echo esc_html__('Options', 'gpt3-ai-content-generator'); ?></legend>
                        <div class="nice-form-group">
                            <input <?php echo $wpaicg_streaming ? ' checked':''?> value="1" type="checkbox" class="wpaicg_chatbot_openai_stream_nav" name="bot[openai_stream_nav]">
                            <label><?php echo esc_html__('Streaming','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="nice-form-group">
                            <input <?php echo $wpaicg_image_enable ? ' checked':''?> value="1" type="checkbox" class="wpaicg_chatbot_image_enable" name="bot[image_enable]">
                            <label><?php echo esc_html__('Image Upload (GPT-4 Vision and GPT-4o Only)','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="nice-form-group">
                            <input name="bot[chat_addition]" class="wpaicg_chatbot_chat_addition" value="1" type="checkbox" id="wpaicg_chat_addition">
                            <label><?php echo esc_html__('Instructions','gpt3-ai-content-generator')?></label>
                        </div>
                    </fieldset>
                    <?php 
                        $wpaicg_additions_json = file_get_contents(WPAICG_PLUGIN_DIR.'admin/chat/context.json');
                        $wpaicg_additions = json_decode($wpaicg_additions_json, true);
                    ?>
                    <div class="nice-form-group">
                        <select disabled class="wpaicg_chat_addition_template">
                            <option value=""><?php echo esc_html__('Select Template','gpt3-ai-content-generator')?></option>
                            <?php
                            foreach($wpaicg_additions as $key=>$wpaicg_addition){
                                echo '<option value="'.esc_html($wpaicg_addition).'">'.esc_html($key).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <textarea style="width: 100%;" rows="8" disabled name="bot[chat_addition_text]" id="wpaicg_chat_addition_text" class="regular-text wpaicg_chatbot_chat_addition_text"><?php echo esc_html__('You are a helpful AI Assistant. Please be friendly.','gpt3-ai-content-generator')?></textarea>
                    </div>
                    <!-- Advanced Parameters -->
                    <p></p>
                    <a href="#" class="wpaicg-advanced-settings-link"><?php echo esc_html__('Show Advanced Parameters','gpt3-ai-content-generator'); ?></a>
                    <div class="wpaicg-advanced-settings" style="display: none;">
                        <div class="nice-form-group" style="display: flex;">
                            <div class="nice-form-group" style="max-width: 60px;padding-right: 25px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('Max Token','gpt3-ai-content-generator');?></label>
                                <input style="width: 60px;" type="text" class="wpaicg_chatbot_max_tokens" id="label_max_tokens" name="bot[max_tokens]" value="<?php echo esc_html( $wpaicg_chat_max_tokens ) ;?>" >
                            </div>
                            <div class="nice-form-group" style="max-width: 60px;padding-right: 25px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('Temperature','gpt3-ai-content-generator');?></label>
                                <input style="width: 50px;" type="text" class="wpaicg_chatbot_temperature" id="label_temperature" name="bot[temperature]" value="<?php echo esc_html( $wpaicg_chat_temperature ) ;?>">
                            </div>
                            <div class="nice-form-group" style="max-width: 60px;padding-right: 25px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('Top P','gpt3-ai-content-generator');?></label>
                                <input style="width: 50px;" type="text" class="wpaicg_chatbot_top_p" id="label_top_p" name="bot[top_p]" value="<?php echo esc_html( $wpaicg_chat_top_p ) ; ?>" >
                            </div>
                            <div class="nice-form-group" style="display: none;max-width: 60px;padding-right: 25px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('Best Of','gpt3-ai-content-generator');?></label>
                                <input style="width: 50px;" type="hidden" class="wpaicg_chatbot_best_of" id="label_best_of" name="bot[best_of]" value="<?php echo esc_html( $wpaicg_chat_best_of ) ;?>" >
                            </div>
                            <div class="nice-form-group" style="max-width: 60px;padding-right: 25px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('FP','gpt3-ai-content-generator');?></label>
                                <input style="width: 50px;" type="text" class="wpaicg_chatbot_frequency_penalty" id="label_frequency_penalty" name="bot[frequency_penalty]" value="<?php echo esc_html( $wpaicg_chat_frequency_penalty ) ;?>" >
                            </div>
                            <div class="nice-form-group" style="max-width: 60px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('PP','gpt3-ai-content-generator');?></label>
                                <input style="width: 50px;" type="text" class="wpaicg_chatbot_presence_penalty" id="label_presence_penalty" name="bot[presence_penalty]" value="<?php echo esc_html( $wpaicg_chat_presence_penalty ) ;?>" >
                            </div>
                        </div>
                    </div>
                    <div class="wpaicg-bot-footer">
                        <div>
                            <button type="button" class="button wpaicg-bot-step" data-type="type"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
                            <button type="button" class="button button-primary wpaicg-bot-step" data-type="context"><?php echo esc_html__('Next','gpt3-ai-content-generator')?></button>
                        </div>
                        <button class="button button-primary wpaicg-chatbot-submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                    </div>
                </div>
                <?php if(\WPAICG\wpaicg_util_core()->wpaicg_is_pro()): ?>
                    <div class="wpaicg-bot-moderation wpaicg-bot-wizard" style="display: none">
                        <h3>Moderation</h3>
                        <div class="wpaicg-mb-10">
                            <label class="wpaicg-form-label"><?php echo esc_html__('Enable','gpt3-ai-content-generator')?>:</label>
                            <input name="bot[moderation]" value="1" type="checkbox" class="wpaicg_chatbot_moderation">
                        </div>
                        <div class="wpaicg-mb-10">
                            <label class="wpaicg-form-label"><?php echo esc_html__('Model','gpt3-ai-content-generator')?>:</label>
                            <select class="regular-text wpaicg_chatbot_moderation_model"  name="bot[moderation_model]" >
                                <option value="text-moderation-latest">text-moderation-latest</option>
                                <option value="text-moderation-stable">text-moderation-stable</option>
                            </select>
                        </div>
                        <div class="wpaicg-mb-10">
                            <label class="wpaicg-form-label"><?php echo esc_html__('Notice','gpt3-ai-content-generator')?>:</label>
                            <textarea class="wpaicg_chatbot_moderation_notice" rows="8" name="bot[moderation_notice]"><?php echo esc_html__('Your message has been flagged as potentially harmful or inappropriate. Please ensure that your messages are respectful and do not contain language or content that could be offensive or harmful to others. Thank you for your cooperation.','gpt3-ai-content-generator')?></textarea>
                        </div>
                        <div class="wpaicg-bot-footer">
                            <div>
                            <button type="button" class="button wpaicg-bot-step" data-type="parameters"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
                            <button type="button" class="button button-primary wpaicg-bot-step" data-type="audio"><?php echo esc_html__('Next','gpt3-ai-content-generator')?></button>
                            </div>
                            <button class="button button-primary wpaicg-chatbot-submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                        </div>
                    </div>
                <?php
                endif;
                ?>
                <div class="wpaicg-bot-audio wpaicg-bot-wizard" style="display: none">
                    <h3 style="margin-top: -0.2em;"><?php echo esc_html__('Speech','gpt3-ai-content-generator')?></h3>
                        <div class="nice-form-group">
                            <input value="1" type="checkbox" class="wpaicg_chatbot_audio_enable" name="bot[audio_enable]">
                            <label><?php echo esc_html__('Enable Speech to Text','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="nice-form-group">
                            <input class="wpaicg_chatbot_chat_to_speech" value="1" type="checkbox" name="bot[chat_to_speech]">
                            <label><?php echo esc_html__('Enable Text to Speech','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Provider','gpt3-ai-content-generator')?></label>
                            <select disabled name="bot[voice_service]" class="wpaicg_chatbot_voice_service">
                                <option value="openai"><?php echo esc_html__('OpenAI','gpt3-ai-content-generator')?></option>
                                <option value="elevenlabs"><?php echo esc_html__('ElevenLabs','gpt3-ai-content-generator')?></option>
                                <option value="google"><?php echo esc_html__('Google','gpt3-ai-content-generator')?></option>
                            </select>
                    </div>
                    <?php
                        // OpenAI settings
                        $wpaicg_openai_model = 'tts-1';
                        $wpaicg_openai_voice = 'alloy';
                        $wpaicg_openai_output_format = 'mp3';
                        $wpaicg_openai_speed = 1;
                    ?>
                    <div class="wpaicg_voice_service_openai" style="display:none">
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Model','gpt3-ai-content-generator')?></label>
                        <select name="bot[openai_model]" class="wpaicg_chatbot_openai_model">
                            <option value="tts-1" <?php echo $wpaicg_openai_model == 'tts-1' ? 'selected' : ''; ?>>tts-1</option>
                            <option value="tts-1-hd" <?php echo $wpaicg_openai_model == 'tts-1-hd' ? 'selected' : ''; ?>>tts-1-hd</option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Voice','gpt3-ai-content-generator')?></label>
                        <select name="bot[openai_voice]" class="wpaicg_chatbot_openai_voice">
                            <option value="alloy">Alloy</option>
                            <option value="echo">Echo</option>
                            <option value="fable">Fable</option>
                            <option value="onyx">Onyx</option>
                            <option value="nova">Nova</option>
                            <option value="shimmer">Shimmer</option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Response Format','gpt3-ai-content-generator')?></label>
                        <select name="bot[openai_output_format]" class="wpaicg_chatbot_openai_output_format">
                            <option value="mp3">MP3</option>
                            <option value="opus">Opus</option>
                            <option value="aac">AAC</option>
                            <option value="flac">FLAC</option>
                        </select>
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Speed','gpt3-ai-content-generator')?></label>
                        <input type="number" min="0.25" max="4.0" step="0.25" value="1" name="bot[openai_voice_speed]" class="wpaicg_chatbot_openai_voice_speed">
                    </div>
                </div>
                    <div class="wpaicg_voice_service_google" style="display:none">
                    <?php
                        $wpaicg_voice_language = 'en-US';
                        $wpaicg_voice_name = 'en-US-Studio-M';
                        $wpaicg_voice_device = '';
                        $wpaicg_voice_speed = 1;
                        $wpaicg_voice_pitch = 0;
                        $wpaicg_google_api_key = get_option('wpaicg_google_api_key', '');
                    ?>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Voice Language','gpt3-ai-content-generator')?></label>
                            <select <?php echo empty($wpaicg_google_api_key) ? ' disabled':''?> name="bot[voice_language]" class="wpaicg_voice_language wpaicg_chatbot_voice_language">
                                <?php
                                foreach(\WPAICG\WPAICG_Google_Speech::get_instance()->languages as $key=>$voice_language){
                                    echo '<option'.($wpaicg_voice_language == $key ? ' selected':'').' value="'.esc_html($key).'">'.esc_html($voice_language).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Voice Name','gpt3-ai-content-generator')?></label>
                            <select <?php echo empty($wpaicg_google_api_key) ? ' disabled':''?> data-value="<?php echo esc_html($wpaicg_voice_name)?>" name="bot[voice_name]" class="wpaicg_voice_name wpaicg_chatbot_voice_name">
                            </select>
                        </div>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Audio Device Profile','gpt3-ai-content-generator')?></label>
                            <select <?php echo empty($wpaicg_google_api_key) ? ' disabled':''?> name="bot[voice_device]" class="wpaicg_chatbot_voice_device">
                                <?php
                                foreach(\WPAICG\WPAICG_Google_Speech::get_instance()->devices() as $key => $device){
                                    echo '<option'.($wpaicg_voice_device == $key ? ' selected':'').' value="'.esc_html($key).'">'.esc_html($device).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Voice Speed','gpt3-ai-content-generator')?></label>
                            <input <?php echo empty($wpaicg_google_api_key) ? ' disabled':''?> type="text" class="wpaicg_voice_speed wpaicg_chatbot_voice_speed" value="<?php echo esc_html($wpaicg_voice_speed)?>" name="bot[voice_speed]">
                        </div>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Voice Pitch','gpt3-ai-content-generator')?>:</label>
                            <input <?php echo empty($wpaicg_google_api_key) ? ' disabled':''?> type="text" class="wpaicg_voice_pitch wpaicg_chatbot_voice_pitch" value="<?php echo esc_html($wpaicg_voice_pitch)?>" name="bot[voice_pitch]">
                        </div>
                    </div>
                    <?php
                        // ElevenLabs settings
                        $wpaicg_elevenlabs_voice = '';
                        $wpaicg_elevenlabs_model = '';
                        $wpaicg_elevenlabs_api_key = get_option('wpaicg_elevenlabs_api_key', '');
                    ?>

                    <div class="wpaicg_voice_service_elevenlabs">
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Select a Voice','gpt3-ai-content-generator')?></label>
                            <select <?php echo empty($wpaicg_elevenlabs_api_key) ? ' disabled':'' ?> name="bot[elevenlabs_voice]" class="wpaicg_chatbot_elevenlabs_voice">
                                <?php
                                foreach(\WPAICG\WPAICG_ElevenLabs::get_instance()->voices as $key => $voice){
                                    echo '<option value="'.esc_html($key).'"'.($wpaicg_elevenlabs_voice == $key ? ' selected' : '').'>'.esc_html($voice).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <!-- Model Dropdown -->
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Select a Model', 'gpt3-ai-content-generator')?></label>
                            <select <?php echo empty($wpaicg_elevenlabs_api_key) ? ' disabled':'' ?> name="bot[elevenlabs_model]" class="wpaicg_chatbot_elevenlabs_model">
                                <?php
                                foreach(\WPAICG\WPAICG_ElevenLabs::get_instance()->models as $key => $model){
                                    echo '<option value="'.esc_html($key).'"'.($wpaicg_elevenlabs_model == $key ? ' selected' : '').'>'.esc_html($model).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="wpaicg-bot-footer">
                        <div>
                        <button type="button" class="button wpaicg-bot-step" data-type="custom"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
                        <button type="button" class="button button-primary wpaicg-bot-step" data-type="logs"><?php echo esc_html__('Next','gpt3-ai-content-generator')?></button>
                        </div>
                        <button class="button button-primary wpaicg-chatbot-submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                    </div>
                </div>
                <div class="wpaicg-bot-custom wpaicg-bot-wizard" style="display: none">
                    <h3 style="margin-top: -0.2em;"><?php echo esc_html__('Interface','gpt3-ai-content-generator')?></h3>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('AI Name','gpt3-ai-content-generator')?></label>
                        <input style="width: 100%;" type="text" class="wpaicg_chatbot_ai_name" name="bot[ai_name]" value="AI" >
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('User Name','gpt3-ai-content-generator')?></label>
                        <input style="width: 100%;" type="text" class="wpaicg_chatbot_you" name="bot[you]" value="<?php echo esc_html__('You','gpt3-ai-content-generator')?>" >
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Response Wait Message','gpt3-ai-content-generator')?></label>
                        <input style="width: 100%;" type="text" class="wpaicg_chatbot_ai_thinking" name="bot[ai_thinking]" value="<?php echo esc_html__('Gathering thoughts','gpt3-ai-content-generator')?>" >
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Placeholder','gpt3-ai-content-generator')?></label>
                        <input style="width: 100%;" type="text" class="wpaicg_chatbot_placeholder" name="bot[placeholder]" value="<?php echo esc_html__('Type message..','gpt3-ai-content-generator')?>" >
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Welcome Message','gpt3-ai-content-generator')?></label>
                        <input style="width: 100%;" type="text" class="wpaicg_chatbot_welcome" name="bot[welcome]" value="<?php echo esc_html__('Hello human, I am a GPT powered AI chat bot. Ask me anything!','gpt3-ai-content-generator')?>" >
                    </div>
                    <div class="nice-form-group" style="display: none">
                        <label><?php echo esc_html__('No Answer Message','gpt3-ai-content-generator')?></label>
                        <input style="width: 100%;" class="wpaicg_chatbot_no_answer" type="hidden" value="" name="bot[no_answer]">
                    </div>
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Footer Note','gpt3-ai-content-generator')?></label>
                        <input style="width: 100%;" class="wpaicg_chatbot_footer_text" value="" type="text" name="bot[footer_text]" placeholder="<?php echo esc_html__('Powered by ...','gpt3-ai-content-generator')?>">
                    </div>
                    <div class="wpaicg-bot-footer">
                        <div>
                        <button type="button" class="button wpaicg-bot-step" data-type="style"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
                        <button type="button" class="button button-primary wpaicg-bot-step" data-type="audio"><?php echo esc_html__('Next','gpt3-ai-content-generator')?></button>
                        </div>
                        <button class="button button-primary wpaicg-chatbot-submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                    </div>
                </div>
                <!--Context-->
                <div class="wpaicg-bot-context wpaicg-bot-wizard" style="display: none">
                    <h3 style="margin-top: -0.2em;"><?php echo esc_html__('Knowledge','gpt3-ai-content-generator')?></h3>
                    <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: 1em;">
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Content Aware','gpt3-ai-content-generator')?></label>
                            <select style="width: 100px;" name="bot[content_aware]" class="wpaicg_chatbot_content_aware">
                                <option value="yes"><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                                <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                            </select>
                        </div>
                        <div class="nice-form-group">
                                <label><?php echo esc_html__('User Aware','gpt3-ai-content-generator')?></label>
                                <select style="width: 100px;" name="bot[user_aware]" class="wpaicg_chatbot_user_aware">
                                    <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                                    <option value="yes"><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                                </select>
                            </div>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Memory','gpt3-ai-content-generator')?></label>
                            <select style="width: 100px;" name="bot[remember_conversation]" class="wpaicg_chatbot_remember_conversation">
                                <option value="yes"><?php echo esc_html__('Yes','gpt3-ai-content-generator')?></option>
                                <option value="no"><?php echo esc_html__('No','gpt3-ai-content-generator')?></option>
                            </select>
                        </div>
                    </div>
                    <fieldset class="nice-form-group" style="margin-top: 1em;">
                    <legend><?php echo esc_html__('Data Source', 'gpt3-ai-content-generator'); ?></legend>
                        <div class="nice-form-group">
                            <input checked type="checkbox" id="wpaicg_chat_excerpt" class="wpaicg_chatbot_chat_excerpt">
                            <label><?php echo esc_html__('Use Excerpt','gpt3-ai-content-generator')?></label>
                        </div>
                        <div class="nice-form-group">
                            <input type="checkbox" value="1" name="bot[embedding]" id="wpaicg_chat_embedding" class="asdisabled wpaicg_chatbot_embedding">
                            <label><?php echo esc_html__('Use Embeddings','gpt3-ai-content-generator')?></label>
                        </div>
                    </fieldset>
                    <div class="nice-form-group" style="display: flex;justify-content: space-between;margin-top: 1em;">
                        <!-- Vector DB Provider -->
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Vector DB','gpt3-ai-content-generator')?></label>
                            <select style="width: 100px;" disabled name="bot[vectordb]" id="wpaicg_chat_vectordb" class="asdisabled wpaicg_chatbot_vectordb">
                                <option value="pinecone"><?php echo esc_html__('Pinecone','gpt3-ai-content-generator')?></option>
                                <option value="qdrant"><?php echo esc_html__('Qdrant','gpt3-ai-content-generator')?></option>
                            </select>
                        </div>
                        <!-- Pinecone Indexes -->
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Index','gpt3-ai-content-generator')?></label>
                            <select style="width: 100px;" disabled name="bot[embedding_index]" id="wpaicg_chat_embedding_index" class="asdisabled wpaicg_chatbot_embedding_index">
                                <option value=""><?php echo esc_html__('Default','gpt3-ai-content-generator')?></option>
                                <?php
                                foreach($wpaicg_pinecone_indexes as $wpaicg_pinecone_index){
                                    echo '<option value="'.esc_html($wpaicg_pinecone_index['url']).'">'.esc_html($wpaicg_pinecone_index['name']).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <!-- Qdrant Collections -->
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Collection','gpt3-ai-content-generator')?></label>
                            <select style="width: 100px;" disabled name="bot[qdrant_collection]" id="wpaicg_chat_qdrant_collection" class="asdisabled wpaicg_chatbot_qdrant_collection">
                            <?php foreach ($wpaicg_qdrant_collections as $collection) {
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
                                $selected = ($name === '') ? ' selected' : '';
                                echo '<option value="' . esc_attr($name) . '"' . $selected . '>' . esc_html($display_name) . '</option>';
                            } ?>
                            </select>
                        </div>
                        <div class="nice-form-group">
                            <label><?php echo esc_html__('Limit','gpt3-ai-content-generator')?></label>
                            <select style="width: 100px;" disabled name="bot[embedding_top]" id="wpaicg_chat_embedding_top" class="asdisabled wpaicg_chatbot_embedding_top">
                                <?php
                                for($i = 1; $i <=5;$i++){
                                    echo '<option value="'.esc_html($i).'">'.esc_html($i).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="nice-form-group">
                        <input type="checkbox" name="bot[use_default_embedding]" value="1" checked="checked" class="wpaicg_chatbot_use_default_embedding">
                        <label><?php echo esc_html__('Use Default Embedding','gpt3-ai-content-generator')?></label>
                    </div>
                    <div class="nice-form-group" style="display: none;" id="embeddingModelDropdown">
                        <label><?php echo esc_html__('Embedding model','gpt3-ai-content-generator')?></label>
                        <select style="width: 75%;" name="bot[embedding_model]" id="wpaicg_chatbot_embedding_model" class="wpaicg_chatbot_embedding_model">
                            <?php
                            $embedding_models = \WPAICG\WPAICG_Util::get_instance()->get_embedding_models();
                            $embedding_model = '';
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
                        <input type="hidden" name="bot[embedding_provider]" id="wpaicg_chatbot_embedding_provider">
                    </div>
                    <!-- Conversation Starters -->
                    <div class="nice-form-group">
                        <label><?php echo esc_html__('Conversation Starters', 'gpt3-ai-content-generator'); ?></label>
                        <?php foreach($conversation_starters as $index => $starter): ?>
                            <input type="hidden" name="bot[conversation_starters][<?php echo $index; ?>]" value="<?php echo esc_attr($starter); ?>">
                        <?php endforeach; ?>
                        <a href="javascript:void(0)" class="wpaicg_add_conversation_starter"><?php echo esc_html__('Edit Conversation Starters', 'gpt3-ai-content-generator'); ?></a>
                    </div>
                    <?php if(\WPAICG\wpaicg_util_core()->wpaicg_is_pro()): ?>
                        <fieldset class="nice-form-group">
                            <div class="nice-form-group">
                                <input disabled type="checkbox" value="1" name="bot[embedding_pdf]" class="asdisabled wpaicg_chatbot_embedding_pdf">
                                <label><?php echo esc_html__('Enable PDF Upload','gpt3-ai-content-generator')?></label>
                            </div>
                        </fieldset>
                        <div class="nice-form-group">
                            <textarea style="width: 100%;" disabled rows="4" name="bot[embedding_pdf_message]" class="asdisabled wpaicg_chatbot_embedding_pdf_message">Congrats! Your PDF is uploaded now! You can ask questions about your document.\nExample Questions:[questions]</textarea>
                        </div>
                    <?php else: ?>
                        <fieldset class="nice-form-group">
                            <div class="nice-form-group">
                                <input type="checkbox" disabled> <?php echo esc_html__('Enable PDF Upload (Pro Plan)','gpt3-ai-content-generator')?>
                            </div>
                        </fieldset>
                    <?php endif; ?>
                    <!-- Additional Options -->
                    <p></p>
                    <a href="#" class="wpaicg-additional-settings-link"><?php echo esc_html__('Show Additional Options','gpt3-ai-content-generator'); ?></a>
                    <div class="wpaicg-additional-settings" style="display: none;">
                        <div class="nice-form-group" style="display: flex;">
                            <div class="nice-form-group" style="padding-right: 5px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('Memory Limit','gpt3-ai-content-generator')?></label>
                                <select style="width: 80px;font-size: 10px;" name="bot[conversation_cut]" class="wpaicg_chatbot_conversation_cut">
                                    <?php
                                    for($i=3;$i<=50;$i++){
                                        echo '<option'.(10 == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <?php if(\WPAICG\wpaicg_util_core()->wpaicg_is_pro()): ?>
                            <div class="nice-form-group" style="padding-right: 5px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('PDF Page Limit','gpt3-ai-content-generator')?></label>
                                <select style="width: 80px;font-size: 10px;" disabled name="bot[pdf_pages]" id="wpaicg_chat_pdf_pages" class="asdisabled wpaicg_chatbot_pdf_pages">
                                    <?php
                                    $pdf_pages = 120;
                                    for($i=1;$i <= 120;$i++){
                                        echo '<option'.($pdf_pages == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="nice-form-group" style="padding-right: 5px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('PDF Icon','gpt3-ai-content-generator')?></label>
                                    <input style="height: 44px;" value="#222222" type="color" class="wpaicg_chatbot_pdf_color" name="bot[pdf_color]">
                                </div>
                            <?php else: ?>
                                <div class="nice-form-group" style="padding-right: 5px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('PDF Page Limit','gpt3-ai-content-generator')?></label>
                                <select style="width: 80px;font-size: 10px;" disabled>
                                    <option><?php echo esc_html__('Available in Pro','gpt3-ai-content-generator')?></option>
                                </select>
                            </div>
                            <div class="nice-form-group" style="padding-right: 5px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('PDF Icon','gpt3-ai-content-generator')?></label>
                                <input style="height: 44px;" disabled value="#222222" type="color" class="wpaicg_chatbot_pdf_color" name="bot[pdf_color]">
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="nice-form-group" style="display: flex;">
                            <div class="nice-form-group" style="padding-right: 5px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('Language','gpt3-ai-content-generator')?></label>
                                <?php $language_options = \WPAICG\WPAICG_Util::get_instance()->chat_language_options; ?>
                                <select style="width: 80px;font-size: 10px;" class="wpaicg_chatbot_language"  name="bot[language]">
                                    <?php foreach($language_options as $key => $value){?>
                                        <option value="<?php echo esc_html($key)?>"><?php echo esc_html($value)?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="nice-form-group" style="padding-right: 5px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('Tone','gpt3-ai-content-generator')?></label>
                                <?php $tone_options = \WPAICG\WPAICG_Util::get_instance()->chat_tone_options; ?>
                                <select style="width: 80px;font-size: 10px;" class="wpaicg_chatbot_tone" name="bot[tone]">
                                    <?php foreach($tone_options as $key => $value){?>
                                        <option value="<?php echo esc_html($key)?>"><?php echo esc_html($value)?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="nice-form-group" style="padding-right: 5px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('Profession','gpt3-ai-content-generator')?></label>
                                <?php $proffesion_options = \WPAICG\WPAICG_Util::get_instance()->chat_profession_options; ?>
                                <select style="width: 80px;font-size: 10px;" name="bot[proffesion]" class="wpaicg_chatbot_proffesion">
                                    <?php foreach($proffesion_options as $key => $value){?>
                                        <option value="<?php echo esc_html($key)?>"><?php echo esc_html($value)?></option>
                                    <?php }?>
                                </select>
                            </div>
                            <div class="nice-form-group" style="padding-right: 5px;">
                                <label style="font-size: 10px;"><?php echo esc_html__('Embedding Type','gpt3-ai-content-generator')?></label>
                                <select style="width: 80px;font-size: 10px;" disabled name="bot[embedding_type]" id="wpaicg_chat_embedding_type" class="asdisabled wpaicg_chatbot_embedding_type">
                                    <option value="openai"><?php echo esc_html__('Conversational','gpt3-ai-content-generator')?></option>
                                    <option value=""><?php echo esc_html__('Non-Conversational','gpt3-ai-content-generator')?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="wpaicg-bot-footer">
                        <div>
                        <button type="button" class="button wpaicg-bot-step" data-type="parameters"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
                        <button type="button" class="button button-primary wpaicg-bot-step" data-type="style"><?php echo esc_html__('Next','gpt3-ai-content-generator')?></button>
                        </div>
                        <button class="button button-primary wpaicg-chatbot-submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                    </div>
                </div>
                <div class="wpaicg-bot-logs wpaicg-bot-wizard" style="display: none">
                    <h3 style="margin-top: -0.2em;"><?php echo esc_html__('Logs','gpt3-ai-content-generator')?></h3>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Save Chat Logs','gpt3-ai-content-generator')?>:</label>
                        <input <?php echo $wpaicg_save_logs ? ' checked': ''?> class="wpaicg_chatbot_save_logs" value="1" type="checkbox" name="bot[save_logs]">
                    </div>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Save Prompt','gpt3-ai-content-generator')?>:</label>
                        <input disabled class="wpaicg_chatbot_log_request" value="1" type="checkbox" name="bot[log_request]">
                    </div>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Display Notice','gpt3-ai-content-generator')?>:</label>
                        <input disabled <?php echo $wpaicg_log_notice ? ' checked': ''?> class="wpaicg_chatbot_log_notice" value="1" type="checkbox" name="bot[log_notice]">
                    </div>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Notice Text','gpt3-ai-content-generator')?>:</label>
                        <textarea disabled class="wpaicg_chatbot_log_notice_message" name="bot[log_notice_message]"><?php echo esc_html($wpaicg_log_notice_message)?></textarea>
                    </div>
                    <div class="wpaicg-bot-footer">
                        <div>
                        <button type="button" class="button wpaicg-bot-step" data-type="audio"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
                        <button type="button" class="button button-primary wpaicg-bot-step" data-type="tokens"><?php echo esc_html__('Next','gpt3-ai-content-generator')?></button>
                        </div>
                        <button class="button button-primary wpaicg-chatbot-submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                    </div>
                </div>
                <div class="wpaicg-bot-tokens wpaicg-bot-wizard" style="display: none">
                    <h3 style="margin-top: -0.2em;"><?php echo esc_html__('Token Management','gpt3-ai-content-generator')?></h3>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Limit Registered User','gpt3-ai-content-generator')?>:</label>
                        <input <?php echo $wpaicg_user_limited ? ' checked': ''?> type="checkbox" value="1" class="wpaicg_user_token_limit wpaicg_chatbot_user_limited" name="bot[user_limited]">
                    </div>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Token Limit','gpt3-ai-content-generator')?>:</label>
                        <input <?php echo $wpaicg_user_limited ? '' : ' disabled'?> style="width: 80px" class="wpaicg_user_token_limit_text wpaicg_chatbot_user_tokens" type="text" value="<?php echo esc_html($wpaicg_user_tokens)?>" name="bot[user_tokens]">
                    </div>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Role based limit','gpt3-ai-content-generator')?>:</label>
                        <?php
                        foreach($wpaicg_roles as $key=>$wpaicg_role){
                            echo '<input class="wpaicg_role_'.esc_html($key).'" type="hidden" name="bot[limited_roles]['.esc_html($key).']">';
                        }
                        ?>
                        <input type="checkbox" value="1" class="wpaicg_role_limited" name="bot[role_limited]">
                        <a href="javascript:void(0)" class="wpaicg_limit_set_role<?php echo $wpaicg_user_limited ? ' ': ' disabled'?>"><?php echo esc_html__('Set Limit','gpt3-ai-content-generator')?></a>
                    </div>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Limit Non-Registered User','gpt3-ai-content-generator')?>:</label>
                        <input <?php echo $wpaicg_guest_limited ? ' checked': ''?> type="checkbox" class="wpaicg_guest_token_limit wpaicg_chatbot_guest_limited" value="1" name="bot[guest_limited]">
                    </div>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Token Limit','gpt3-ai-content-generator')?>:</label>
                        <input <?php echo $wpaicg_guest_limited ? '' : ' disabled'?> class="wpaicg_guest_token_limit_text wpaicg_chatbot_guest_tokens" style="width: 80px" type="text" value="<?php echo esc_html($wpaicg_guest_tokens)?>" name="bot[guest_tokens]">
                    </div>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Notice','gpt3-ai-content-generator')?>:</label>
                        <input type="text" value="<?php echo esc_html($wpaicg_limited_message)?>" name="bot[limited_message]" class="wpaicg_chatbot_limited_message">
                    </div>
                    <div class="wpaicg-mb-10">
                        <label class="wpaicg-form-label"><?php echo esc_html__('Reset Limit','gpt3-ai-content-generator')?>:</label>
                        <select name="bot[reset_limit]" class="wpaicg_chatbot_reset_limit">
                            <option value="0"><?php echo esc_html__('Never','gpt3-ai-content-generator')?></option>
                            <option value="1"><?php echo esc_html__('1 Day','gpt3-ai-content-generator')?></option>
                            <option value="3"><?php echo esc_html__('3 Days','gpt3-ai-content-generator')?></option>
                            <option value="7"><?php echo esc_html__('1 Week','gpt3-ai-content-generator')?></option>
                            <option value="14"><?php echo esc_html__('2 Weeks','gpt3-ai-content-generator')?></option>
                            <option value="30"><?php echo esc_html__('1 Month','gpt3-ai-content-generator')?></option>
                            <option value="60"><?php echo esc_html__('2 Months','gpt3-ai-content-generator')?></option>
                            <option value="90"><?php echo esc_html__('3 Months','gpt3-ai-content-generator')?></option>
                            <option value="180"><?php echo esc_html__('6 Months','gpt3-ai-content-generator')?></option>
                        </select>
                    </div>
                    <div class="wpaicg-bot-footer">
                        <div>
                        <button type="button" class="button wpaicg-bot-step" data-type="logs"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
                        </div>
                        <button class="button button-primary wpaicg-chatbot-submit"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="wpaicg-grid-3">
            <div class="wpaicg-bot-preview">
                <div class="wpaicg-chat-shortcode"
                     data-user-bg-color="<?php echo esc_html($wpaicg_user_bg_color)?>"
                     data-color="<?php echo esc_html($wpaicg_chat_fontcolor)?>"
                     data-fontsize="<?php echo esc_html($wpaicg_chat_fontsize)?>"
                     data-use-avatar="<?php echo $wpaicg_use_avatar ? '1' : '0'?>"
                     data-user-avatar="<?php echo is_user_logged_in() ? get_avatar_url(get_current_user_id()) : WPAICG_PLUGIN_URL . 'admin/images/default_profile.png' ?>"
                     data-you="You"
                     data-ai-avatar="<?php echo $wpaicg_use_avatar && !empty($wpaicg_ai_avatar_id) ? wp_get_attachment_url(esc_html($wpaicg_ai_avatar_id)) : WPAICG_PLUGIN_URL.'admin/images/chatbot.png'?>"
                     data-ai-name="AI"
                     data-ai-bg-color="<?php echo esc_html($wpaicg_ai_bg_color)?>"
                     data-nonce="<?php echo esc_html(wp_create_nonce( 'wpaicg-chatbox' ))?>"
                     data-post-id="<?php echo get_the_ID()?>"
                     data-url="<?php echo home_url( $wp->request )?>"
                     data-width="100%"
                     data-height="50%"
                     data-footer="0"
                     data-text_height="40"
                     data-text_rounded="8"
                     data-chat_rounded="8"
                     style="width: <?php echo esc_html($wpaicg_chat_width)?>px;"
                     data-voice_service=""
                     data-voice_language=""
                     data-voice_name=""
                     data-voice_device=""
                     data-typewriter-effect = <?php echo esc_html($wpaicg_typewriter_effect) ? '1' : '0'?>
                     data-typewriter-speed="<?php echo esc_html(get_option('wpaicg_typewriter_speed', 1)); ?>"
                     data-voice_speed=""
                     data-voice_pitch=""
                     data-openai_model=""
                     data-openai_voice=""
                     data-openai_output_format=""
                     data-openai_voice_speed=""
                     data-openai_stream_nav = ""
                     data-elevenlabs_model=""
                     data-elevenlabs_voice=""
                     data-type="shortcode"
                     >
                    <div class="wpaicg-chat-shortcode-content" style="background-color: <?php echo esc_html($wpaicg_chat_bgcolor)?>;">
                        <ul class="wpaicg-chat-shortcode-messages" style="height: <?php echo esc_html($wpaicg_chat_height)?>">
                            <li style="background: #ccf5e1; padding: 10px;border-radius: unset;font-size: 11px;font-style: italic;display:none;" class="wpaicg_chatbot_log_preview">
                                <p><span class="wpaicg-chat-message"></span></p>
                            </li>
                            <li class="wpaicg-ai-message" style="color: <?php echo esc_html($wpaicg_chat_fontcolor)?>; font-size: <?php echo esc_html($wpaicg_chat_fontsize)?>px; background-color: <?php echo esc_html($wpaicg_ai_bg_color);?>">
                                <p>
                                    <strong style="float: left" class="wpaicg-chat-avatar"><?php echo esc_html__('AI','gpt3-ai-content-generator')?>: </strong>
                                    <span class="wpaicg-chat-message wpaicg_chatbot_welcome_message"><?php echo esc_html__('Hello human, I am a GPT powered AI chat bot. Ask me anything!','gpt3-ai-content-generator')?></span>
                                </p>
                            </li>
                        </ul>
                    </div>
                    <span class="wpaicg-bot-thinking" style="display: none;color:<?php echo esc_html($wpaicg_chat_fontcolor)?>">Gathering thoughts&nbsp;<span class="wpaicg-jumping-dots"><span class="wpaicg-dot-1">.</span><span class="wpaicg-dot-2">.</span><span class="wpaicg-dot-3">.</span></span></span>
                    <div class="wpaicg-chat-shortcode-type" style="background-color: <?php echo esc_html($wpaicg_chat_bgcolor)?>;">
                        <textarea style="border-color: <?php echo esc_html($wpaicg_border_text_field)?>;background-color: <?php echo esc_html($wpaicg_bg_text_field)?>" type="text" class="auto-expand wpaicg-chat-shortcode-typing" placeholder="<?php echo esc_html__('Type message..','gpt3-ai-content-generator')?>"></textarea>
                        <div class="wpaicg_chat_additions">
                            <span class="wpaicg-mic-icon" data-type="shortcode" style="<?php echo $wpaicg_audio_enable ? '' : 'display:none'?>;color: <?php echo esc_html($wpaicg_mic_color)?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path d="M176 0C123 0 80 43 80 96V256c0 53 43 96 96 96s96-43 96-96V96c0-53-43-96-96-96zM48 216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 89.1 66.2 162.7 152 174.4V464H104c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H200V430.4c85.8-11.7 152-85.3 152-174.4V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 70.7-57.3 128-128 128s-128-57.3-128-128V216z"/></svg>
                            </span>
                            <span class="wpaicg-img-icon" data-type="shortcode" style="<?php echo $wpaicg_image_enable ? '' : 'display:none'?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-image"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                                <input type="file" id="imageUpload" class="wpaicg-img-file" accept="image/png, image/jpeg, image/webp, image/gif" style="display: none;" />
                                <!-- add nonce -->
                                <input type="hidden" id="wpaicg-img-nonce" value="<?php echo esc_html(wp_create_nonce( 'wpaicg-img-nonce' ))?>" />
                            </span>
                            <span class="wpaicg-pdf-icon" data-type="shortcode" style="display:none">
                                <svg version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512"  xml:space="preserve"><path class="st0" d="M378.413,0H208.297h-13.182L185.8,9.314L57.02,138.102l-9.314,9.314v13.176v265.514 c0,47.36,38.528,85.895,85.896,85.895h244.811c47.353,0,85.881-38.535,85.881-85.895V85.896C464.294,38.528,425.766,0,378.413,0z M432.497,426.105c0,29.877-24.214,54.091-54.084,54.091H133.602c-29.884,0-54.098-24.214-54.098-54.091V160.591h83.716 c24.885,0,45.077-20.178,45.077-45.07V31.804h170.116c29.87,0,54.084,24.214,54.084,54.092V426.105z"/><path class="st0" d="M171.947,252.785h-28.529c-5.432,0-8.686,3.533-8.686,8.825v73.754c0,6.388,4.204,10.599,10.041,10.599 c5.711,0,9.914-4.21,9.914-10.599v-22.406c0-0.545,0.279-0.817,0.824-0.817h16.436c20.095,0,32.188-12.226,32.188-29.612 C204.136,264.871,192.182,252.785,171.947,252.785z M170.719,294.888h-15.208c-0.545,0-0.824-0.272-0.824-0.81v-23.23 c0-0.545,0.279-0.816,0.824-0.816h15.208c8.42,0,13.447,5.027,13.447,12.498C184.167,290,179.139,294.888,170.719,294.888z"/><path class="st0" d="M250.191,252.785h-21.868c-5.432,0-8.686,3.533-8.686,8.825v74.843c0,5.3,3.253,8.693,8.686,8.693h21.868 c19.69,0,31.923-6.249,36.81-21.324c1.76-5.3,2.723-11.681,2.723-24.857c0-13.175-0.964-19.557-2.723-24.856 C282.113,259.034,269.881,252.785,250.191,252.785z M267.856,316.896c-2.318,7.331-8.965,10.459-18.21,10.459h-9.23 c-0.545,0-0.824-0.272-0.824-0.816v-55.146c0-0.545,0.279-0.817,0.824-0.817h9.23c9.245,0,15.892,3.128,18.21,10.46 c0.95,3.128,1.62,8.56,1.62,17.93C269.476,308.336,268.805,313.768,267.856,316.896z"/><path class="st0" d="M361.167,252.785h-44.812c-5.432,0-8.7,3.533-8.7,8.825v73.754c0,6.388,4.218,10.599,10.055,10.599 c5.697,0,9.914-4.21,9.914-10.599v-26.351c0-0.538,0.265-0.81,0.81-0.81h26.086c5.837,0,9.23-3.532,9.23-8.56 c0-5.028-3.393-8.553-9.23-8.553h-26.086c-0.545,0-0.81-0.272-0.81-0.817v-19.425c0-0.545,0.265-0.816,0.81-0.816h32.733 c5.572,0,9.245-3.666,9.245-8.553C370.411,256.45,366.738,252.785,361.167,252.785z"/></svg>
                            </span>
                            <span class="wpaicg-pdf-loading" style="display: none"></span>
                            <span data-type="shortcode" alt="<?php echo esc_html__('Clear','gpt3-ai-content-generator')?>" title="<?php echo esc_html__('Clear','gpt3-ai-content-generator')?>" class="wpaicg-pdf-remove" style="display: none">&times;</span>
                            <input type="file" accept="application/pdf" class="wpaicg-pdf-file" style="display: none">
                            <span class="wpaicg-chat-shortcode-send">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-send"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                            </span>
                        </div>
                    </div>
                    <div style="<?php echo $wpaicg_include_footer ? '' :' display:none'?>;background-color: <?php echo esc_html($wpaicg_chat_bgcolor)?>" class="wpaicg-chat-shortcode-footer"></div>
                </div>
                <div class="wpaicg-chatbot-widget-icon" style="display: none">
                    <img src="<?php echo esc_html(WPAICG_PLUGIN_URL).'admin/images/chatbot.png'?>" height="75" width="75">
                </div>
            </div>
        </div>
    </div>
</div>
<?php if(isset($_GET['update_success']) && !empty($_GET['update_success'])){ ?>
    <p style="color: #26a300; font-weight: bold;">
        <?php echo esc_html__('Congratulations! Your chatbot has been saved successfully!','gpt3-ai-content-generator')?>
    </p>
    <?php
    }
?>
<?php
$wpaicg_bot_page = isset($_GET['wpage']) && !empty($_GET['wpage']) ? sanitize_text_field($_GET['wpage']) : 1;
$args = array(
    'post_type' => 'wpaicg_chatbot',
    'posts_per_page' => 40,
    'paged' => $wpaicg_bot_page
);
if(isset($_GET['search']) && !empty($_GET['search'])){
    $search = sanitize_text_field($_GET['search']);
    $args['s'] = $search;
}
$wpaicg_bots = new WP_Query($args);
?>
<div class="wpaicg-mb-10">
    <form action="" method="GET">
        <input type="hidden" name="page" value="wpaicg_chatgpt">
        <input type="hidden" name="action" value="bots">
        <input value="<?php echo isset($_GET['search']) && !empty($_GET['search']) ? esc_html($_GET['search']) : ''?>" name="search" type="text" placeholder="<?php echo esc_html__('Search Bot','gpt3-ai-content-generator')?>">
        <button class="button button-primary"><?php echo esc_html__('Search','gpt3-ai-content-generator')?></button>
        <!-- add export button -->
        <button type="button" class="button button-primary wpaicg-export-bot" id="exportButton"><?php echo esc_html__('Export','gpt3-ai-content-generator')?></button>
        <!-- Import Button Trigger -->
        <button type="button" class="button button-primary wpaicg-import-bot" id="importButton"><?php echo esc_html__('Import', 'gpt3-ai-content-generator'); ?></button>
        <!-- Hidden File Input -->
        <input type="file" id="importFileInput" style="display: none;" accept=".json">
        <button type="button" class="button button-primary wpaicg-create-bot"><?php echo esc_html__('Create New Bot','gpt3-ai-content-generator')?></button>
    </form>
</div>
<table class="wp-list-table widefat fixed striped table-view-list posts">
    <thead>
    <tr>
        <th><?php echo esc_html__('Name','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Type','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('ID / Shortcode','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Created','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Updated','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Model','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Context','gpt3-ai-content-generator')?></th>
        <th><?php echo esc_html__('Action','gpt3-ai-content-generator')?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    if($wpaicg_bots->have_posts()){
        foreach($wpaicg_bots->posts as $wpaicg_bot){
            if(strpos($wpaicg_bot->post_content,'\"') !== false) {
                $wpaicg_bot->post_content = str_replace('\"', '&quot;', $wpaicg_bot->post_content);
            }
            if(strpos($wpaicg_bot->post_content,"\'") !== false) {
                $wpaicg_bot->post_content = str_replace('\\', '', $wpaicg_bot->post_content);
            }
            // Check if bot is valid json. Added by Hung Le
            try {
                $bot = json_decode($wpaicg_bot->post_content, true);
            } catch (Exception $e) {
                error_log("Failed to decode bot post_content: " . $e->getMessage());
                continue;
            }

            if($bot && is_array($bot)){
                foreach ($bot as $key=>$value){
                    if(is_string($value)) {
                        $bot[$key] = str_replace("\\", '', $value);
                    }
                }
            $bot['id'] = $wpaicg_bot->ID;
            $bot['ai_avatar_url'] = isset($bot['ai_avatar_id']) && !empty($bot['ai_avatar_id']) ? wp_get_attachment_url($bot['ai_avatar_id']) : '';
            $bot['icon_url_url'] = isset($bot['icon_url']) && !empty($bot['icon_url']) ? wp_get_attachment_url($bot['icon_url']) : '';
            ?>
                <tr>
                    <td><?php echo esc_html($wpaicg_bot->post_title);?></td>
                    <td><?php echo isset($bot['type']) && $bot['type'] == 'shortcode' ? 'Shortcode' : 'Widget';?></td>
                    <td>
                        <code>
                        <?php
                        if(isset($bot['type']) && $bot['type'] === 'shortcode'){
                            echo '[wpaicg_chatgpt id='.esc_html($wpaicg_bot->ID).']';
                        }
                        else{
                            if(isset($bot['pages'])){
                                $pages = array_map('trim', explode(',', $bot['pages']));
                                $key = 0;
                                foreach($pages as $page){
                                    $link = get_permalink($page);
                                    if(!empty($link)){
                                        $key++;
                                        echo ($key == 1 ? '' : ', ').'<a href="'.$link.'" target="_blank">'.$page.'</a>';
                                    }
                                }
                            }
                        }
                        ?>
                        </code>
                    </td>
                    <td><?php echo esc_html(gmdate('d.m.Y H:i',strtotime($wpaicg_bot->post_date)))?></td>
                    <td><?php echo esc_html(gmdate('d.m.Y H:i',strtotime($wpaicg_bot->post_modified)))?></td>
                    <td>
                    <?php 
                        if ($wpaicg_provider === 'Azure') {
                            echo esc_html(get_option('wpaicg_azure_deployment', ''));
                        } else {
                            echo isset($bot['model']) && !empty($bot['model']) ? esc_html($bot['model']) : '';
                        }
                    ?>
                </td>

                    <td>
                        <?php
                        if(isset($bot['content_aware']) && $bot['content_aware'] == 'yes'){
                            if(isset($bot['embedding']) && $bot['embedding']){
                                echo 'Embeddings';
                            }
                            else{
                                echo 'Excerpt';
                            }
                        }
                        else{
                            echo 'No';
                        }
                        ?>
                    </td>
                    <td>
                        <button class="button button-primary button-small wpaicg-bot-edit" data-content="<?php echo htmlspecialchars(json_encode($bot,JSON_UNESCAPED_UNICODE),ENT_QUOTES, 'UTF-8')?>"><?php echo esc_html__('Edit','gpt3-ai-content-generator')?></button>
                        <a class="button-small button button-link-delete" onclick="return confirm('<?php echo esc_html__('Are you sure?','gpt3-ai-content-generator')?>')" href="<?php echo wp_nonce_url(admin_url('admin.php?page=wpaicg_chatgpt&action=bots&wpaicg_bot_delete='.$wpaicg_bot->ID),'wpaicg_delete_'.$wpaicg_bot->ID)?>"><?php echo esc_html__('Delete','gpt3-ai-content-generator')?></a>
                    </td>
                </tr>
            <?php
            }
        }
    }
    ?>
    </tbody>
</table>
<div class="wpaicg-paginate">
    <?php
    echo paginate_links( array(
        'base'         => admin_url('admin.php?page=wpaicg_chatgpt&action=bots&wpage=%#%'),
        'total'        => $wpaicg_bots->max_num_pages,
        'current'      => $wpaicg_bot_page,
        'format'       => '?wpage=%#%',
        'show_all'     => false,
        'prev_next'    => false,
        'add_args'     => false,
    ));
    ?>
</div>
<script>
    jQuery(document).ready(function ($){
        let wpaicg_google_voices = <?php echo json_encode($wpaicg_google_voices)?>;
        let wpaicg_elevenlab_api = '<?php echo esc_html($wpaicg_elevenlabs_api)?>';
        let wpaicg_google_api_key = '<?php echo esc_html($wpaicg_google_api_key)?>';
        let wpaicg_all_google_models = <?php echo json_encode($wpaicg_all_google_models)?>;
        // Function to disable text to speech and speech to text when streaming is enabled
        function checkAndHandleStreaming() {
            let isStreamingEnabled = $('.wpaicg_chatbot_openai_stream_nav').is(':checked');
            if (isStreamingEnabled) {
                $('.wpaicg_chatbot_chat_to_speech').prop('checked', false);
                $('.wpaicg_chatbot_chat_to_speech').attr('disabled', 'disabled');
                $('.wpaicg_chatbot_audio_enable').prop('checked', false);
                $('.wpaicg_chatbot_audio_enable').attr('disabled', 'disabled');
                $('.wpaicg_chatbot_image_enable').prop('checked', false);
                $('.wpaicg_chatbot_image_enable').attr('disabled', 'disabled');
            } else {
                $('.wpaicg_chatbot_chat_to_speech').removeAttr('disabled');
                $('.wpaicg_chatbot_audio_enable').removeAttr('disabled');
                $('.wpaicg_chatbot_image_enable').removeAttr('disabled');
            }
        }

        // Initially check streaming status on document ready
        checkAndHandleStreaming();

        // Watch for changes in the streaming enable/disable checkbox
        $(document).on('change', '.wpaicg_chatbot_openai_stream_nav', function() {
            checkAndHandleStreaming();
        });

        // listen changes on model select.. if gpt-4-vision-preview is not selected then disable and set false the image enable checkbox.. also if streaming is enabled then disable the image enable checkbox regardless of the model selected
        $(document).on('change', '.wpaicg_chatbot_model', function() {
            let isStreamingEnabled = $('.wpaicg_chatbot_openai_stream_nav').is(':checked');
            if (isStreamingEnabled) {
                $('.wpaicg_chatbot_image_enable').prop('checked', false);
                $('.wpaicg_chatbot_image_enable').attr('disabled', 'disabled');
            } else {
                let selectedModel = $(this).val();
                 // gpt-4-vision-preview or gpt-4o
                if (selectedModel !== 'gpt-4-vision-preview' && selectedModel !== 'gpt-4o') {
                    $('.wpaicg_chatbot_image_enable').prop('checked', false);
                    $('.wpaicg_chatbot_image_enable').attr('disabled', 'disabled');
                } else {
                    $('.wpaicg_chatbot_image_enable').removeAttr('disabled');
                }
                let selectprovider = $('.wpaicg_provider').val();
                if (selectprovider === 'Google') {
                    //disable audio and image enable checkbox regardless of the model selected
                    $('.wpaicg_chatbot_audio_enable').prop('checked', false);
                    $('.wpaicg_chatbot_audio_enable').attr('disabled', 'disabled');
                    $('.wpaicg_chatbot_image_enable').prop('checked', false);
                    $('.wpaicg_chatbot_image_enable').attr('disabled', 'disabled');
                }
            }
        });

        // listen changes on provider select: wpaicg_provider...
        $(document).on('change', '.wpaicg_provider', function() {
            let selectedProvider = $(this).val();
            if (selectedProvider === 'OpenAI') {
                $('.openai-models').show();
                $('.google-models').hide();
                $('.azure-models').hide();
                $('.openrouter-models').hide();
                // enable streaming checkbox
                $('.wpaicg_chatbot_openai_stream_nav').removeAttr('disabled');
                // enable audio and image enable checkbox
                $('.wpaicg_chatbot_audio_enable').removeAttr('disabled');
                $('.wpaicg_chatbot_image_enable').removeAttr('disabled');
            } else if (selectedProvider === 'Google') {
                $('.openai-models').hide();
                $('.google-models').show();
                $('.azure-models').hide();
                $('.openrouter-models').hide();
                $('.wpaicg_chatbot_audio_enable').prop('checked', false);
                $('.wpaicg_chatbot_audio_enable').attr('disabled', 'disabled');
                $('.wpaicg_chatbot_image_enable').prop('checked', false);
                $('.wpaicg_chatbot_image_enable').attr('disabled', 'disabled');
                // disable streaming checkbox
                $('.wpaicg_chatbot_openai_stream_nav').prop('checked', false);
                $('.wpaicg_chatbot_openai_stream_nav').attr('disabled', 'disabled');
            } else if (selectedProvider === 'Azure') {
                $('.openai-models').hide();
                $('.google-models').hide();
                $('.openrouter-models').hide();
                $('.azure-models').show();
            }  else if (selectedProvider === 'OpenRouter') {
                $('.openai-models').hide();
                $('.google-models').hide();
                $('.azure-models').hide();
                $('.openrouter-models').show();
                // enable streaming checkbox
                $('.wpaicg_chatbot_openai_stream_nav').removeAttr('disabled');
            }
        });

        function wpaicgChangeVoiceService(element){
            let parent = element.parent().parent();
            let voice_service = parent.find('.wpaicg_chatbot_voice_service');
            if(element.prop('checked')){
                voice_service.removeAttr('disabled');
                let selectedValue = voice_service.val();
                parent.find('.wpaicg_voice_service_google, .wpaicg_voice_service_elevenlabs, .wpaicg_voice_service_openai').hide();
                if(selectedValue === 'google'){
                    parent.find('.wpaicg_voice_service_google').show();
                    parent.find('.wpaicg_chatbot_voice_language, .wpaicg_chatbot_voice_name, .wpaicg_chatbot_voice_device, .wpaicg_chatbot_voice_speed, .wpaicg_chatbot_voice_pitch').removeAttr('disabled');
                } else if(selectedValue === 'elevenlabs' && wpaicg_elevenlab_api !== ''){
                    parent.find('.wpaicg_voice_service_elevenlabs').show();
                    parent.find('.wpaicg_chatbot_elevenlabs_voice, .wpaicg_chatbot_elevenlabs_model').removeAttr('disabled');
                } else if(selectedValue === 'openai'){
                    parent.find('.wpaicg_voice_service_openai').show();
                    parent.find('.wpaicg_chatbot_openai_model, .wpaicg_chatbot_openai_voice, .wpaicg_chatbot_openai_output_format, .wpaicg_chatbot_openai_voice_speed').removeAttr('disabled');
                }
            } else {
                voice_service.attr('disabled', 'disabled');
                parent.find('.wpaicg_chatbot_elevenlabs_voice, .wpaicg_chatbot_elevenlabs_model, .wpaicg_chatbot_voice_language, .wpaicg_chatbot_voice_name, .wpaicg_chatbot_voice_device, .wpaicg_chatbot_voice_speed, .wpaicg_chatbot_voice_pitch, .wpaicg_chatbot_openai_model, .wpaicg_chatbot_openai_voice, .wpaicg_chatbot_openai_output_format, .wpaicg_chatbot_openai_voice_speed').attr('disabled', 'disabled');
                parent.find('.wpaicg_voice_service_google, .wpaicg_voice_service_elevenlabs, .wpaicg_voice_service_openai').hide();
            }
        }

        $(document).on('click','.wpaicg_chatbot_chat_to_speech', function(e){
            wpaicgChangeVoiceService($(e.currentTarget));
        });

        // display advanced settings
        $(document).on('click', '.wpaicg-advanced-settings-link', function(e) {
            e.preventDefault();
            var wpaicgadvancedSettings = $('.wpaicg-advanced-settings');
            var wpaicgadvancedSettingsLink = $(this);

            if (wpaicgadvancedSettings.css('display') === 'none') {
                wpaicgadvancedSettings.css('display', 'block');
                wpaicgadvancedSettingsLink.text('<?php echo esc_js(esc_html__('Hide Advanced Parameters','gpt3-ai-content-generator')); ?>');
            } else {
                wpaicgadvancedSettings.css('display', 'none');
                wpaicgadvancedSettingsLink.text('<?php echo esc_js(esc_html__('Show Advanced Parameters','gpt3-ai-content-generator')); ?>');
            }
        });

        // display additional settings
        $(document).on('click', '.wpaicg-additional-settings-link', function(e) {
            e.preventDefault();
            var wpaicgadvancedSettings = $('.wpaicg-additional-settings');
            var wpaicgadvancedSettingsLink = $(this);

            if (wpaicgadvancedSettings.css('display') === 'none') {
                wpaicgadvancedSettings.css('display', 'block');
                wpaicgadvancedSettingsLink.text('<?php echo esc_js(esc_html__('Hide Additional Options','gpt3-ai-content-generator')); ?>');
            } else {
                wpaicgadvancedSettings.css('display', 'none');
                wpaicgadvancedSettingsLink.text('<?php echo esc_js(esc_html__('Show Additional Options','gpt3-ai-content-generator')); ?>');
            }
        });

        $(document).on('keypress','.wpaicg_voice_speed,.wpaicg_voice_pitch', function (e){
            var charCode = (e.which) ? e.which : e.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode !== 46) {
                return false;
            }
            return true;
        });
        $(document).on('change','.wpaicg_chatbot_voice_service',function(e){
            let parent = $(e.currentTarget).parent().parent();
            let selectedValue = $(e.currentTarget).val();
            parent.find('.wpaicg_voice_service_google, .wpaicg_voice_service_elevenlabs, .wpaicg_voice_service_openai').hide();
            if(selectedValue === 'google'){
                parent.find('.wpaicg_voice_service_google').show();
            } else if(selectedValue === 'elevenlabs'){
                parent.find('.wpaicg_voice_service_elevenlabs').show();
                parent.find('.wpaicg_chatbot_elevenlabs_voice, .wpaicg_chatbot_elevenlabs_model').removeAttr('disabled');
            } else if(selectedValue === 'openai'){
                parent.find('.wpaicg_voice_service_openai').show();
            }
        });

        $(document).on('change', '.wpaicg_chatbot_vectordb', function(e) {
            let parent = $(e.currentTarget).closest('.nice-form-group').parent();
            let selectedDB = $(e.currentTarget).val();
            parent.find('.wpaicg_chatbot_embedding_index, .wpaicg_chatbot_qdrant_collection').closest('.nice-form-group').hide(); // Hide both by default

            if (selectedDB === 'qdrant') {
                // Show Qdrant Collection dropdown if Qdrant is selected
                parent.find('.wpaicg_chatbot_qdrant_collection').closest('.nice-form-group').show();
            } else if (selectedDB === 'pinecone') {
                // Show Pinecone Index dropdown if Pinecone is selected
                parent.find('.wpaicg_chatbot_embedding_index').closest('.nice-form-group').show();
            }
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
        });
        let wpaicg_roles = <?php echo wp_kses_post(json_encode($wpaicg_roles))?>;
        let defaultAIAvatar = '<?php echo esc_html(WPAICG_PLUGIN_URL).'admin/images/chatbot.png'?>';
        let defaultUserAvatar = '<?php echo get_avatar_url(get_current_user_id())?>';
        $(document).on('change','.wpaicg_chatbot_fontsize', function(){
            wpaicgUpdateRealtime();
        });
        $(document).on('click','.wpaicg_chatbot_save_logs,.wpaicg_chatbot_log_notice,.wpaicg_chatbot_audio_enable,.wpaicg_chatbot_image_enable,.wpaicg_chatbot_use_avatar,.wpaicg_chatbot_icon_default,.wpaicg_chatbot_ai_avatar_default,.wpaicg_chatbot_ai_avatar_custom,.wpaicg_chatbot_icon_custom', function(){
            wpaicgUpdateRealtime();
        })
        $(document).on('input','.wpaicg_chatbot_welcome,.wpaicg_chatbot_log_notice_message,.wpaicg_chatbot_footer_text,.wpaicg_chatbot_ai_name,.wpaicg_chatbot_you,.wpaicg_chatbot_placeholder,.wpaicg_chatbot_height,.wpaicg_chatbot_width', function(){
            wpaicgUpdateRealtime();
        });
        $(document).on('click', '.wpaicg_chatbot_save_logs', function(e){
            let modalContent = $(e.currentTarget).closest('.wpaicg_modal_content');
            if($(e.currentTarget).prop('checked')){
                modalContent.find('.wpaicg_chatbot_log_request').removeAttr('disabled');
                modalContent.find('.wpaicg_chatbot_log_notice').removeAttr('disabled');
                modalContent.find('.wpaicg_chatbot_log_notice_message').removeAttr('disabled');
            }
            else{
                modalContent.find('.wpaicg_chatbot_log_request').attr('disabled','disabled');
                modalContent.find('.wpaicg_chatbot_log_request').prop('checked',false);
                modalContent.find('.wpaicg_chatbot_log_notice').attr('disabled','disabled');
                modalContent.find('.wpaicg_chatbot_log_notice').prop('checked',false);
                modalContent.find('.wpaicg_chatbot_log_notice_message').attr('disabled','disabled');
            }
        });

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

        $(document).on('change', 'input[name="chat_theme"]', function () {
            var theme = $(this).val(); // Get the selected theme value
            if (themes[theme]) {
                updateTheme(themes[theme]);
            }
        });

        function updateTheme(theme) {
            let modalContent = $('.wpaicg_modal_content');

            modalContent.find('.wpaicg_chatbot_fontcolor').val(theme.fontColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_ai_bg_color').val(theme.aiBgColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_user_bg_color').val(theme.userBgColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_bgcolor').val(theme.windowBgColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_input_font_color').val(theme.inputFontColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_border_text_field').val(theme.borderTextField).trigger('input');
            modalContent.find('.wpaicg_chatbot_send_color').val(theme.sendColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_bg_text_field').val(theme.bgTextField).trigger('input');
            modalContent.find('.wpaicg_chatbot_footer_color').val(theme.footerColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_thinking_color').val(theme.thinkingColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_footer_font_color').val(theme.footerfontColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_pdf_color').val(theme.pdfColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_mic_color').val(theme.micColor).trigger('input');
            modalContent.find('.wpaicg_chatbot_stop_color').val(theme.stopColor).trigger('input');

            // Call wpaicgUpdateRealtime if needed to immediately apply changes
            wpaicgUpdateRealtime();
        }

        
        $(document).on('input change', '.wpaicg_chatbot_footer_font_color,.wpaicg_chatbot_thinking_color,.wpaicg_chatbot_pdf_color,.wpaicg_chatbot_footer_color, .wpaicg_chatbot_mic_color, .wpaicg_chatbot_send_color, .wpaicg_chatbot_border_text_field, .wpaicg_chatbot_bg_text_field, .wpaicg_chatbot_bgcolor, .wpaicg_chatbot_fontcolor, .wpaicg_chatbot_ai_bg_color, .wpaicg_chatbot_user_bg_color', function() {
            wpaicgUpdateRealtime();
        });

        function wpaicgUpdateRealtime(){
            let modalContent = $('.wpaicg_modal_content');
            let fontsize = modalContent.find('.wpaicg_chatbot_fontsize').val();
            let fontcolor = modalContent.find('.wpaicg_chatbot_fontcolor').val();

            // background color
            let bgcolor = modalContent.find('.wpaicg_chatbot_bgcolor').val();
            $('.wpaicg-chat-shortcode-content').css('background-color', bgcolor);
            $('.wpaicg-chat-shortcode-type').css('background-color', bgcolor);
            $('.wpaicg-bot-thinking').css('background-color', bgcolor);
            
            // ai background color
            let aibg = modalContent.find('.wpaicg_chatbot_ai_bg_color').val();
            // user background color
            let userbg = modalContent.find('.wpaicg_chatbot_user_bg_color').val();

            let inputbg = modalContent.find('.wpaicg_chatbot_bg_text_field').val();

            let inputborder = modalContent.find('.wpaicg_chatbot_border_text_field').val();
            let sendcolor = modalContent.find('.wpaicg_chatbot_send_color').val();
            let miccolor = modalContent.find('.wpaicg_chatbot_mic_color').val();
            let footercolor = modalContent.find('.wpaicg_chatbot_footer_color').val();
            let pdf_color = modalContent.find('.wpaicg_chatbot_pdf_color').val();

            let thinking_color = modalContent.find('.wpaicg_chatbot_thinking_color').val();
            $('.wpaicg-bot-thinking').css('color', thinking_color);

            let footerfontcolor = modalContent.find('.wpaicg_chatbot_footer_font_color').val();
            $('.wpaicg-chat-shortcode-footer').css('color', footerfontcolor);

            let inputfontcolor = modalContent.find('.wpaicg_chatbot_input_font_color').val();

            let useavatar = modalContent.find('.wpaicg_chatbot_use_avatar').prop('checked') ? true : false;
            let chatwidth = modalContent.find('.wpaicg_chatbot_width').val();
            let chatheight = modalContent.find('.wpaicg_chatbot_height').val();
            let enablemic = modalContent.find('.wpaicg_chatbot_audio_enable').prop('checked') ? true :false;
            let enableimg = modalContent.find('.wpaicg_chatbot_image_enable').prop('checked') ? true :false;
            let enablepdf = modalContent.find('.wpaicg_chatbot_embedding_pdf').prop('checked') ? true :false;
            let save_log = modalContent.find('.wpaicg_chatbot_save_logs').prop('checked') ? true :false;
            let log_notice = modalContent.find('.wpaicg_chatbot_log_notice').prop('checked') ? true :false;
            let log_notice_msg = modalContent.find('.wpaicg_chatbot_log_notice_message').val();

            let ai_thinking = modalContent.find('.wpaicg_chatbot_ai_thinking').val();
            let ai_name = modalContent.find('.wpaicg_chatbot_ai_name').val();
            let you_name = modalContent.find('.wpaicg_chatbot_you').val();
            let placeholder = modalContent.find('.wpaicg_chatbot_placeholder').val();
            let welcome = modalContent.find('.wpaicg_chatbot_welcome').val();
            let footer = modalContent.find('.wpaicg_chatbot_footer_text').val();
            let previewWidth = modalContent.find('.wpaicg-bot-preview').width();
            modalContent.find('.wpaicg-chat-shortcode').attr('data-width',chatwidth);
            modalContent.find('.wpaicg-chat-shortcode').attr('data-height',chatheight);
            modalContent.find('.wpaicg-chat-shortcode').attr('data-text_rounded',modalContent.find('.wpaicg_chatbot_text_rounded').val());
            modalContent.find('.wpaicg-chat-shortcode').attr('data-text_height',modalContent.find('.wpaicg_chatbot_text_height').val());
            modalContent.find('.wpaicg-chat-shortcode').attr('data-chat_rounded',modalContent.find('.wpaicg_chatbot_chat_rounded').val());
            if(welcome !== ''){
                modalContent.find('.wpaicg_chatbot_welcome_message').html(welcome);
            }
            if(save_log && log_notice && log_notice_msg !== ''){
                modalContent.find('.wpaicg_chatbot_log_preview span').html(log_notice_msg);
                modalContent.find('.wpaicg_chatbot_log_preview').show();
            }
            else{
                modalContent.find('.wpaicg_chatbot_log_preview').hide();
            }
            if(modalContent.find('.wpaicg_chatbot_icon_custom').prop('checked') && modalContent.find('.wpaicg_chatbox_icon img').length){
                modalContent.find('.wpaicg-chatbot-widget-icon').html('<img src="'+modalContent.find('.wpaicg_chatbox_icon img').attr('src')+'" height="75" width="75">')
            }
            else{
                modalContent.find('.wpaicg-chatbot-widget-icon').html('<img src="'+defaultAIAvatar+'" height="75" width="75">')
            }
            if(chatwidth === ''){
                chatwidth = '100%';
            }
            if(chatheight === ''){
                chatheight = '50%';
            }
            var wpaicgWindowWidth = window.innerWidth;
            var wpaicgWindowHeight = window.innerHeight;
            if(chatwidth.indexOf('%') < 0){
                if(chatwidth.indexOf('px') < 0){
                    chatwidth = parseFloat(chatwidth);
                }
                else{
                    chatwidth = parseFloat(chatwidth.replace(/px/g,''));
                }
            }
            else{
                chatwidth = parseFloat(chatwidth.replace(/%/g,''));
                chatwidth = chatwidth*wpaicgWindowWidth/100;
            }
            if(chatheight.indexOf('%') < 0){
                if(chatheight.indexOf('px') < 0){
                    chatheight = parseFloat(chatheight);
                }
                else{
                    chatheight = parseFloat(chatheight.replace(/px/g,''));
                }
            }
            else{
                chatheight = parseFloat(chatheight.replace(/%/g,''));
                chatheight = chatheight*wpaicgWindowHeight/100;
            }

            if(parseInt(chatwidth) > previewWidth){
                chatwidth = previewWidth;
            }
            modalContent.find('.wpaicg-chat-shortcode').css({
                width: chatwidth+'px'
            });
            let content_height = parseInt(chatheight) - 44;
            if(footer !== ''){
                content_height  = parseInt(chatheight) - 44 - 13;
                $('.wpaicg-chat-shortcode-footer').html(footer);
                $('.wpaicg-chat-shortcode-footer').show();
            }
            else{
                $('.wpaicg-chat-shortcode-footer').hide();
            }
            modalContent.find('.wpaicg-chat-shortcode-content ul').css({
                height: content_height+'px'
            })
            if(enablemic){
                modalContent.find('.wpaicg-mic-icon').show();
            }
            else{
                modalContent.find('.wpaicg-mic-icon').hide();
            }
            if(enableimg){
                modalContent.find('.wpaicg-img-icon').show();
            }
            else{
                modalContent.find('.wpaicg-img-icon').hide();
            }
            if(enablepdf){
                modalContent.find('.wpaicg-pdf-icon').show();
            }
            else{
                modalContent.find('.wpaicg-pdf-icon').hide();
            }
            modalContent.find('.wpaicg-chat-shortcode-messages li').css({
                'font-size': fontsize+'px',
                'color': fontcolor
            });
            modalContent.find('.wpaicg-chat-shortcode-messages li.wpaicg-ai-message').css({
                'background-color': aibg
            });
            modalContent.find('.wpaicg-chat-shortcode-footer').css({
                'background-color': footercolor,
                'color': footerfontcolor,
                'border-top': '1px solid '+footercolor
            });
            modalContent.find('.wpaicg-chat-shortcode').attr('data-fontsize',fontsize);
            modalContent.find('.wpaicg-chat-shortcode').attr('data-color',fontcolor);
            modalContent.find('.wpaicg-chat-shortcode').attr('data-use-avatar',useavatar ? 1 : 0);
            modalContent.find('.wpaicg-chat-shortcode').attr('data-you',you_name);
            modalContent.find('.wpaicg-chat-shortcode').attr('data-ai-name',ai_name);
            modalContent.find('.wpaicg-chat-shortcode').attr('data-ai-bg-color',aibg);
            modalContent.find('.wpaicg-chat-shortcode').attr('data-user-bg-color',userbg);
            if(useavatar){
                let messageAIAvatar = defaultAIAvatar;
                if(modalContent.find('.wpaicg_chatbox_avatar img').length && modalContent.find('.wpaicg_chatbot_ai_avatar_custom').prop('checked')){
                    messageAIAvatar = modalContent.find('.wpaicg_chatbox_avatar img').attr('src');
                }
                modalContent.find('.wpaicg-chat-shortcode').attr('data-ai-avatar',messageAIAvatar);
                modalContent.find('.wpaicg-chat-shortcode-messages li.wpaicg-ai-message .wpaicg-chat-avatar').html('<img src="'+messageAIAvatar+'" height="40" width="40">');
                modalContent.find('.wpaicg-chat-shortcode-messages li.wpaicg-user-message .wpaicg-chat-avatar').html('<img src="'+defaultUserAvatar+'" height="40" width="40">');
            }
            else{
                modalContent.find('.wpaicg-chat-shortcode-messages li.wpaicg-ai-message .wpaicg-chat-avatar').html(ai_name+':&nbsp;');
                modalContent.find('.wpaicg-chat-shortcode-messages li.wpaicg-user-message .wpaicg-chat-avatar').html(you_name+':&nbsp;');
            }
            modalContent.find('.wpaicg-chat-shortcode-messages li.wpaicg-user-message').css({
                'background-color': userbg
            });

            modalContent.find('textarea.wpaicg-chat-shortcode-typing').css({
                'background-color': inputbg,
                'border-color':inputborder,
                'color': inputfontcolor
            });
            modalContent.find('textarea.wpaicg-chat-shortcode-typing').attr('placeholder', placeholder);
            
            modalContent.find('.wpaicg-chat-shortcode-send').css({
                'color': sendcolor
            })
            modalContent.find('.wpaicg-mic-icon').css({
                'color': miccolor
            });
            modalContent.find('.wpaicg-img-icon').css({
                'color': sendcolor
            });
            modalContent.find('.wpaicg-pdf-icon').css({
                'color': pdf_color
            });
            modalContent.find('.wpaicg-pdf-remove').css({
                'color': pdf_color
            });
            modalContent.find('.wpaicg-pdf-loading').css({
                'border-color': pdf_color,
                'border-bottom-color': 'transparent'
            });
            let contentaware = modalContent.find('.wpaicg_chatbot_content_aware').val();
            if(contentaware === 'no'){
                $('.wpaicg_chatbot_chat_excerpt').prop('checked', false);
                $('.wpaicg_chatbot_chat_excerpt').attr('disabled','disabled');
                $('.wpaicg_chatbot_embedding').prop('checked', false);
                $('.wpaicg_chatbot_embedding').attr('disabled','disabled');
                $('.wpaicg_chatbot_embedding_type').attr('disabled','disabled');
                $('.wpaicg_chatbot_vectordb').attr('disabled','disabled');
                $('.wpaicg_chatbot_embedding_index').attr('disabled','disabled');
                $('.wpaicg_chatbot_qdrant_collection').attr('disabled','disabled');
                $('.wpaicg_chatbot_embedding_pdf').attr('disabled','disabled');
                $('.wpaicg_chatbot_embedding_pdf_message').attr('disabled','disabled');
                $('.wpaicg_chatbot_pdf_pages').attr('disabled','disabled');
                $('.wpaicg_chatbot_embedding_top').attr('disabled','disabled');
            }
            let selectedModel = modalContent.find('.wpaicg_chatbot_model').val();

            // Check if the selected model is in the list of all Google models to disable streaming and image upload
            if (wpaicg_all_google_models.includes(selectedModel)) {
                $('.wpaicg_chatbot_openai_stream_nav').prop('checked', false);
                $('.wpaicg_chatbot_openai_stream_nav').attr('disabled', 'disabled');
                $('.wpaicg_chatbot_image_enable').prop('checked', false);
                $('.wpaicg_chatbot_image_enable').attr('disabled', 'disabled');
            }

            wpaicgChatShortcodeSize();

        }
        $(document).on('click','.wpaicg-bot-step',function (e){
            let btn = $(e.currentTarget);
            let step = btn.attr('data-type');
            let wpaicgGrid = btn.closest('.wpaicg-grid');
            wpaicgGrid.find('.wpaicg-bot-wizard').hide();
            wpaicgGrid.find('.wpaicg-bot-'+step).show();
        });
        function wpaicgLoading(btn){
            btn.attr('disabled','disabled');
            if(!btn.find('spinner').length){
                btn.append('<span class="spinner"></span>');
            }
            btn.find('.spinner').css('visibility','unset');
        }
        function wpaicgRmLoading(btn){
            btn.removeAttr('disabled');
            btn.find('.spinner').remove();
        }
        $('.wpaicg_modal_close').click(function (){
            $('.wpaicg_modal_close').closest('.wpaicg_modal').hide();
            $('.wpaicg-overlay').hide();
        });
        $(document).on('click','.wpaicg_chatbot_type_widget', function (){
            $('.wpaicg_modal_content .wpaicg_chatbot_position').show();
            $('.wpaicg_modal_content .wpaicg-widget-icon').show();
            $('.wpaicg_modal_content .wpaicg-chatbot-delay-time').show();
            $('.wpaicg_modal_content .wpaicg-widget-pages').show();
            $('.wpaicg_modal_content .wpaicg-chatbot-widget-icon').show();
        });
        $(document).on('click','.wpaicg_chatbot_type_shortcode', function (){
            $('.wpaicg_modal_content .wpaicg-chatbot-widget-icon').hide();
            $('.wpaicg_modal_content .wpaicg-chatbot-delay-time').hide();
            $('.wpaicg_modal_content .wpaicg_chatbot_position').hide();
            $('.wpaicg_modal_content .wpaicg-widget-pages').hide();
            $('.wpaicg_modal_content .wpaicg-widget-icon').hide();
        });
        $('.wpaicg-create-bot').click(function (){
            $('.wpaicg_modal_title').html('<?php echo esc_html__('Create New Bot','gpt3-ai-content-generator')?>');
            $('.wpaicg_modal_content').html($('.wpaicg-create-bot-default').html());
            $('.wpaicg_modal_content .wpaicg_chatbot_type_shortcode').prop('checked',true);
            $('.wpaicg_modal_content .wpaicg_chatbot_position').hide();
            $('.wpaicg-overlay').show();
            $('.wpaicg_modal').show();
            wpaicgcollectVoices($('.wpaicg_modal_content .wpaicg_voice_language'));
            // on modal load if vectordb is pinecone then we show pinecone index and hide qdrant collection and vice versa
            if ($('.wpaicg_modal_content .wpaicg_chatbot_vectordb').val() === 'pinecone') {
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_index').closest('.nice-form-group').show();
                $('.wpaicg_modal_content .wpaicg_chatbot_qdrant_collection').closest('.nice-form-group').hide();
            } else {
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_index').closest('.nice-form-group').hide();
                $('.wpaicg_modal_content .wpaicg_chatbot_qdrant_collection').closest('.nice-form-group').show();
            }
            let modalContent = $('.wpaicg_modal_content');
            let embeddingCheckbox = modalContent.find('.wpaicg_chatbot_use_default_embedding');
            let embeddingModelDropdown = modalContent.find('#embeddingModelDropdown');
            let embeddingProviderField = modalContent.find('#wpaicg_chatbot_embedding_provider');
            // Set initial visibility of the dropdown based on the checkbox
            if (!embeddingCheckbox.is(':checked')) {
                embeddingModelDropdown.show();
                updateEmbeddingProvider();
            } else {
                embeddingModelDropdown.hide();
                embeddingProviderField.val('');
            }

            // Event handler for changes in the embedding model checkbox
            embeddingCheckbox.change(function() {
                if (!$(this).is(':checked')) {
                    embeddingModelDropdown.show();
                    updateEmbeddingProvider();
                } else {
                    embeddingModelDropdown.hide();
                    embeddingProviderField.val('');
                }
            });

            // Update embedding provider based on the selected model
            function updateEmbeddingProvider() {
                modalContent.find('#wpaicg_chatbot_embedding_model').change(function () {
                    var selectedProvider = $(this).find('option:selected').data('provider');
                    embeddingProviderField.val(selectedProvider);
                });

                // Initialize the provider field when creating a new bot or when model is changed
                var initialProvider = modalContent.find('#wpaicg_chatbot_embedding_model option:selected').data('provider');
                embeddingProviderField.val(initialProvider);
            }
            wpaicgChatInit();
        });
        
        $(document).on('click', '.wpaicg_chatbox_icon', function (e){
            e.preventDefault();
            $('.wpaicg_modal_content .wpaicg_chatbox_icon_default').prop('checked',false);
            $('.wpaicg_modal_content .wpaicg_chatbox_icon_custom').prop('checked',true);
            let button = $(e.currentTarget),
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
                    $('.wpaicg_modal_content .wpaicg_chatbot_icon_url').val(attachment.id);
                    wpaicgUpdateRealtime();
                }).open();
        });
        $(document).on('click', '.wpaicg_chatbox_avatar', function (e){
            e.preventDefault();
            $('.wpaicg_modal_content .wpaicg_chatbot_ai_avatar_default').prop('checked',false);
            $('.wpaicg_modal_content .wpaicg_chatbox_avatar_custom').prop('checked',true);
            let button = $(e.currentTarget),
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
                    $('.wpaicg_modal_content .wpaicg_chatbot_ai_avatar_id').val(attachment.id);
                    wpaicgUpdateRealtime();
                }).open();
        });
        $(document).on('submit','.wpaicg_modal_content .wpaicg-bot-form', function (e){
            e.preventDefault();
            let form = $(e.currentTarget);
            let btn = form.find('.wpaicg-chatbot-submit');
            let data = form.serialize();
            // Collect and append non-empty conversation starters
            $('.wpaicg_conversation_starter_field').each(function(index) {
                let value = $(this).val().trim(); // Trim the value to remove any leading or trailing whitespace
                if (value !== '') { // Check if the value is not empty
                    let key = `bot[conversation_starters][${index}]`;
                    data += `&${encodeURIComponent(key)}=${encodeURIComponent(value)}`;
                }
            });
            // Set the correct model value based on the selected provider
            let selectedProvider = form.find('.wpaicg_provider').val();
            let model = '';
            if (selectedProvider === 'OpenAI') {
                model = form.find('#wpaicg_chat_model_openai').val();
            } else if (selectedProvider === 'Google') {
                model = form.find('#wpaicg_chat_model_google').val();
            } else if (selectedProvider === 'Azure') {
                model = form.find('#wpaicg_chat_model_azure').val();
            } else if (selectedProvider === 'OpenRouter') {
                model = form.find('#wpaicg_chat_model_openrouter').val();
            }
            data += `&bot[model]=${encodeURIComponent(model)}`;
            let name = form.find('.wpaicg_chatbot_name').val();
            let has_error = false;
            if(name === ''){
                has_error = '<?php echo esc_html__('Please enter a name for your awesome chat bot','gpt3-ai-content-generator')?>';
            }
            else if(form.find('.wpaicg_voice_speed').length){
                let wpaicg_voice_speed = parseFloat(form.find('.wpaicg_voice_speed').val());
                let wpaicg_voice_pitch = parseFloat(form.find('.wpaicg_voice_pitch').val());
                let wpaicg_voice_name = parseFloat(form.find('.wpaicg_voice_name').val());
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
            }
            if(has_error){
                alert(has_error);
            }
            else {
                $.ajax({
                    url: '<?php echo esc_url(admin_url('admin-ajax.php'))?>',
                    data: data,
                    type: 'POST',
                    dataType: 'JSON',
                    beforeSend: function () {
                        wpaicgLoading(btn)
                    },
                    success: function (res) {
                        wpaicgRmLoading(btn);
                        if (res.status === 'success') {
                            window.location.href = '<?php echo admin_url('admin.php?page=wpaicg_chatgpt&action=bots&update_success=true')?>';
                        } else {
                            alert(res.msg);
                        }
                    }
                })
            }
        });
        $(document).on('input','.wpaicg_chatbot_chat_rounded,.wpaicg_chatbot_text_height,.wpaicg_chatbot_text_rounded', function(){
            wpaicgUpdateRealtime();
        })
        $('.wpaicg-bot-edit').click(function (){
            let fields = $(this).attr('data-content');
            // fields = fields.replace(/\\/g,'');
            fields = JSON.parse(fields);
            $('.wpaicg_modal_title').html('<?php echo esc_html__('Edit Bot','gpt3-ai-content-generator')?>');
            $('.wpaicg_modal_content').html($('.wpaicg-create-bot-default').html());
            let modalContent = $('.wpaicg_modal_content');
            let wpaicg_save_log = false;
            modalContent.find('.wpaicg_chatbot_log_request').removeAttr('disabled');
            modalContent.find('.wpaicg_chatbot_log_notice').removeAttr('disabled');
            modalContent.find('.wpaicg_chatbot_log_notice_message').removeAttr('disabled');
            modalContent.find('.wpaicg-chat-shortcode').attr('data-bot-id',fields.id);

            // Add current conversation starters as hidden inputs
            if (fields.conversation_starters && fields.conversation_starters.length > 0) {
                fields.conversation_starters.forEach((starter, index) => {
                    modalContent.append(`<input type="hidden" name="bot[conversation_starters][${index}]" value="${starter}">`);
                });
            }

            let embeddingCheckbox = modalContent.find('.wpaicg_chatbot_use_default_embedding');
            let embeddingModelDropdown = modalContent.find('#embeddingModelDropdown');
            let embeddingProviderField = modalContent.find('#wpaicg_chatbot_embedding_provider');

            // Set initial visibility of the dropdown based on the checkbox
            if(fields.use_default_embedding === '0'){
                embeddingModelDropdown.show();
                updateEmbeddingProvider();
            } else {
                embeddingModelDropdown.hide();
                embeddingProviderField.val('');
            }

            // Event handler for changes in the embedding model checkbox
            embeddingCheckbox.change(function() {
                if (!$(this).is(':checked')) {
                    embeddingModelDropdown.show();
                    updateEmbeddingProvider();
                } else {
                    embeddingModelDropdown.hide();
                    embeddingProviderField.val('');
                }
            });

            // Update embedding provider based on the selected model
            function updateEmbeddingProvider() {
                modalContent.find('#wpaicg_chatbot_embedding_model').change(function () {
                    var selectedProvider = $(this).find('option:selected').data('provider');
                    embeddingProviderField.val(selectedProvider);
                });

                // Initialize the provider field based on existing selection
                var initialProvider = modalContent.find('#wpaicg_chatbot_embedding_model option:selected').data('provider');
                embeddingProviderField.val(initialProvider);
            }

            // Set the AI provider and model based on the fields data
            modalContent.find('#wpaicg_provider').val(fields.provider).trigger('change');

            // Function to display the correct model dropdown based on the selected provider
            function displayModelDropdown(provider) {
                switch (provider) {
                    case 'OpenAI':
                        modalContent.find('.openai-models').show();
                        modalContent.find('.google-models').hide();
                        modalContent.find('.azure-models').hide();
                        modalContent.find('.openrouter-models').hide();
                        // display the correct model dropdown based on the selected provider
                        modalContent.find('.wpaicg_chatbot_model').val('gpt-3.5-turbo');
                        break;
                    case 'Google':
                        modalContent.find('.openai-models').hide();
                        modalContent.find('.google-models').show();
                        modalContent.find('.azure-models').hide();
                        modalContent.find('.openrouter-models').hide();
                        modalContent.find('.wpaicg_chatbot_model').val('gemini-pro');
                        // Disable speech-to-text, streaming, and image upload checkboxes
                        modalContent.find('.wpaicg_chatbot_audio_enable').prop('checked', false);
                        modalContent.find('.wpaicg_chatbot_audio_enable').attr('disabled', 'disabled');
                        modalContent.find('.wpaicg_chatbot_image_enable').prop('checked', false);
                        modalContent.find('.wpaicg_chatbot_image_enable').attr('disabled', 'disabled');
                        // disable streaming
                        modalContent.find('.wpaicg_chatbot_openai_stream_nav').prop('checked', false);
                        modalContent.find('.wpaicg_chatbot_openai_stream_nav').attr('disabled', 'disabled');
                        break;
                    case 'Azure':
                        modalContent.find('.openai-models').hide();
                        modalContent.find('.google-models').hide();
                        modalContent.find('.azure-models').show();
                        modalContent.find('.openrouter-models').hide();
                        modalContent.find('.wpaicg_chatbot_model').val('');
                        // Disable speech-to-text, streaming, and image upload checkboxes
                        modalContent.find('.wpaicg_chatbot_audio_enable').prop('checked', false);
                        modalContent.find('.wpaicg_chatbot_audio_enable').attr('disabled', 'disabled');
                        modalContent.find('.wpaicg_chatbot_image_enable').prop('checked', false);
                        modalContent.find('.wpaicg_chatbot_image_enable').attr('disabled', 'disabled');
                        // disable streaming
                        modalContent.find('.wpaicg_chatbot_openai_stream_nav').prop('checked', false);
                        modalContent.find('.wpaicg_chatbot_openai_stream_nav').attr('disabled', 'disabled');
                        break;
                    case 'OpenRouter':
                        modalContent.find('.openai-models').hide();
                        modalContent.find('.google-models').hide();
                        modalContent.find('.azure-models').hide();
                        modalContent.find('.openrouter-models').show();
                        modalContent.find('.wpaicg_chatbot_model').val('');
                        // Disable speech-to-text, streaming, and image upload checkboxes
                        modalContent.find('.wpaicg_chatbot_audio_enable').prop('checked', false);
                        modalContent.find('.wpaicg_chatbot_audio_enable').attr('disabled', 'disabled');
                        break;
                }
            }

            // Initially display the correct model dropdown
            displayModelDropdown(fields.provider);

            // Event handler for changes in the AI provider dropdown
            modalContent.find('#wpaicg_provider').change(function () {
                let selectedProvider = $(this).val();
                displayModelDropdown(selectedProvider);
            });

            // if vector db is pinecone then show pinecone index and hide qdrant collection and vice versa
            if (fields.vectordb === 'pinecone') {
                modalContent.find('.wpaicg_chatbot_embedding_index').closest('.nice-form-group').show();
                modalContent.find('.wpaicg_chatbot_qdrant_collection').closest('.nice-form-group').hide();
            } else if (fields.vectordb === 'qdrant') {
                modalContent.find('.wpaicg_chatbot_embedding_index').closest('.nice-form-group').hide();
                modalContent.find('.wpaicg_chatbot_qdrant_collection').closest('.nice-form-group').show();
            }
            $.each(fields, function (key, field){
                if(key === 'chat_to_speech'){
                    if(field === '1'){
                        modalContent.find('.wpaicg-chat-shortcode').attr('data-speech',1);
                    }
                    else{
                        modalContent.find('.wpaicg-chat-shortcode').attr('data-speech','');
                    }
                }
                if(key === 'elevenlabs_voice'){
                    if(field !== ''){
                        modalContent.find('.wpaicg-chat-shortcode').attr('data-voice',field);
                    }
                    else{
                        modalContent.find('.wpaicg-chat-shortcode').attr('data-voice','');
                    }
                }
                if(key === 'use_default_embedding' && field === '0'){
                    modalContent.find('.wpaicg_chatbot_use_default_embedding').prop('checked', false);
                }
                if(key === 'voice_service' || key === 'openai_model' || key === 'elevenlabs_model' || key === 'openai_voice' || key === 'openai_output_format' || key === 'openai_voice_speed' || key === 'openai_stream_nav' || key === 'voice_language' || key === 'voice_name' || key === 'voice_device' || key === 'voice_speed' || key === 'voice_pitch'){
                    modalContent.find('.wpaicg-chat-shortcode').attr('data-'+key,field);
                }
                if(key === 'chat_to_speech' && field === '1'){
                    if(wpaicg_google_api_key !== ''){
                        modalContent.find('.wpaicg_voice_language').removeAttr('disabled');
                        modalContent.find('.wpaicg_voice_name').removeAttr('disabled');
                        modalContent.find('.wpaicg_voice_device').removeAttr('disabled');
                        modalContent.find('.wpaicg_voice_speed').removeAttr('disabled');
                        modalContent.find('.wpaicg_voice_pitch').removeAttr('disabled');
                    }
                }
                if(key == 'voice_service'){
                    if(field === 'google'){
                        modalContent.find('.wpaicg_voice_service_elevenlabs').hide();
                        modalContent.find('.wpaicg_voice_service_openai').hide();
                        modalContent.find('.wpaicg_voice_service_google').show();
                    }
                    else if(field === 'elevenlabs'){
                        modalContent.find('.wpaicg_voice_service_google').hide();
                        modalContent.find('.wpaicg_voice_service_openai').hide();
                        modalContent.find('.wpaicg_voice_service_elevenlabs').show();
                    }
                    else if(field === 'openai'){
                        modalContent.find('.wpaicg_voice_service_google').hide();
                        modalContent.find('.wpaicg_voice_service_elevenlabs').hide();
                        modalContent.find('.wpaicg_voice_service_openai').show();
                    }
                }

                // if key openai_stream_nav is enabled then disable wpaicg_chatbot_image_enable and set it to false
                if(key === 'openai_stream_nav' && field === '1'){
                    modalContent.find('.wpaicg_chatbot_image_enable').prop('checked', false);
                    modalContent.find('.wpaicg_chatbot_image_enable').attr('disabled', 'disabled');
                }

                if(key === 'width'){
                    modalContent.find('.wpaicg-chat-shortcode').attr('data-width',field);
                }
                if(key === 'height'){
                    modalContent.find('.wpaicg-chat-shortcode').attr('data-height',field);
                }
                if(key === 'text_rounded'){
                    modalContent.find('.wpaicg-chat-shortcode').attr('data-text_rounded',field);
                }
                if(key === 'text_height'){
                    modalContent.find('.wpaicg-chat-shortcode').attr('data-text_height',field);
                }
                if(key === 'chat_rounded'){
                    modalContent.find('.wpaicg-chat-shortcode').attr('data-chat_rounded',field);
                }
                if(key === 'chat_addition' && field === '1'){
                    modalContent.find('.wpaicg_chatbot_chat_addition_text').removeAttr('disabled');
                    modalContent.find('.wpaicg_chat_addition_template').removeAttr('disabled');
                }
                if(typeof field === 'string' && field.indexOf('&quot;') > -1) {
                    field = field.replace(/&quot;/g, '"');
                }
                if(key === 'type'){
                    if(field === 'widget'){
                        modalContent.find('.wpaicg-chatbot-widget-icon').show();
                        modalContent.find('.wpaicg-widget-icon').show();
                        modalContent.find('.wpaicg-chatbot-delay-time').show();
                        modalContent.find('.wpaicg-widget-pages').show();
                        modalContent.find('.wpaicg_chatbot_position').show();
                    }
                    else{
                        modalContent.find('.wpaicg-chatbot-widget-icon').hide();
                        modalContent.find('.wpaicg-chatbot-delay-time').hide();
                    }
                    modalContent.find('.wpaicg_chatbot_type_'+field).prop('checked',true);
                }
                else if(key === 'icon'){
                    modalContent.find('.wpaicg_chatbot_icon_default').prop('checked',false);
                    modalContent.find('.wpaicg_chatbot_icon_custom').prop('checked',false);
                    modalContent.find('.wpaicg_chatbot_icon_'+field).prop('checked',true);
                    if(field === 'custom' && fields.icon_url_url !== ''){
                        modalContent.find('.wpaicg_chatbox_icon').html('<img src="'+fields.icon_url_url+'" height="75" width="75">');
                        modalContent.find('.wpaicg-chatbot-widget-icon').html('<img src="'+fields.icon_url_url+'" height="75" width="75">');
                    }
                }
                else if(key === 'ai_avatar'){
                    modalContent.find('.wpaicg_chatbot_ai_avatar_default').prop('checked',false);
                    modalContent.find('.wpaicg_chatbot_ai_avatar_custom').prop('checked',false);
                    modalContent.find('.wpaicg_chatbot_ai_avatar_'+field).prop('checked',true);
                    if(field === 'custom' && fields.ai_avatar_url !== ''){
                        modalContent.find('.wpaicg_chatbox_avatar').html('<img src="'+fields.ai_avatar_url+'" height="40" width="40">');
                    }
                }
                else if(key === 'moderation_notice'){
                    if(field === ''){
                        field = '<?php echo esc_html__('Your message has been flagged as potentially harmful or inappropriate. Please ensure that your messages are respectful and do not contain language or content that could be offensive or harmful to others. Thank you for your cooperation.','gpt3-ai-content-generator')?>';
                    }
                    modalContent.find('.wpaicg_chatbot_'+key).val(field);
                }
                else if(key === 'position'){
                    modalContent.find('.wpaicg_chatbot_position_left').prop('checked',false);
                    modalContent.find('.wpaicg_chatbot_position_right').prop('checked',false);
                    modalContent.find('.wpaicg_chatbot_position_'+field).prop('checked',true);
                }
                else if(key === 'voice_name'){
                    modalContent.find('.wpaicg_chatbot_voice_name').attr('data-value',field);
                }
                else if((key === 'fullscreen' || key === 'embedding_pdf' || key === 'chat_to_speech' || key === 'close_btn' || key === 'download_btn' || key === 'clear_btn' || key === 'openai_stream_nav' || key === 'log_request' || key === 'audio_enable' || key === 'image_enable' || key === 'moderation' || key === 'use_avatar' || key === 'chat_addition' || key === 'save_logs' || key === 'log_notice') && field === '1'){
                    if(key === 'save_logs'){
                        wpaicg_save_log = true;
                    }
                    if((key === 'log_request' || key === 'log_notice' || key === 'log_request') && wpaicg_save_log){
                        modalContent.find('.wpaicg_chatbot_'+key).prop('checked',true);
                        modalContent.find('.wpaicg_chatbot_'+key).removeAttr('disabled');
                    }
                    else if((key === 'log_request' || key === 'log_notice' || key === 'log_request') && !wpaicg_save_log){
                        modalContent.find('.wpaicg_chatbot_'+key).prop('checked',false);
                        modalContent.find('.wpaicg_chatbot_'+key).attr('disabled','disabled');
                    }
                    else{
                        modalContent.find('.wpaicg_chatbot_'+key).prop('checked',true);
                    }
                }
                else if(key === 'user_limited' && field === '1'){
                    modalContent.find('.wpaicg_chatbot_user_limited').prop('checked',true);
                    modalContent.find('.wpaicg_chatbot_user_tokens').removeAttr('disabled');
                    modalContent.find('.wpaicg_limit_set_role').addClass('disabled');
                    modalContent.find('.wpaicg_role_limited').prop('checked',false);
                }
                else if(key === 'role_limited' && field === '1'){
                    modalContent.find('.wpaicg_role_limited').prop('checked',true);
                    modalContent.find('.wpaicg_chatbot_user_limited').prop('checked',false);
                    modalContent.find('.wpaicg_chatbot_user_tokens').attr('disabled','disabled');
                    modalContent.find('.wpaicg_limit_set_role').removeClass('disabled');
                }
                else if(key === 'guest_limited' && field === '1'){
                    modalContent.find('.wpaicg_chatbot_guest_limited').prop('checked',true);
                    modalContent.find('.wpaicg_chatbot_guest_tokens').removeAttr('disabled');
                }
                else if(key === 'embedding' && field === '1'){
                    modalContent.find('.wpaicg_chatbot_chat_excerpt').prop('checked',false);
                    modalContent.find('.wpaicg_chatbot_chat_excerpt').addClass('asdisabled');
                    modalContent.find('.wpaicg_chatbot_embedding').removeClass('asdisabled');
                    modalContent.find('.wpaicg_chatbot_embedding').prop('checked',true);
                    modalContent.find('.wpaicg_chatbot_embedding_type').removeAttr('disabled');
                    modalContent.find('.wpaicg_chatbot_vectordb').removeAttr('disabled');
                    modalContent.find('.wpaicg_chatbot_embedding_type').removeClass('asdisabled');
                    modalContent.find('.wpaicg_chatbot_vectordb').removeClass('asdisabled');
                    modalContent.find('.wpaicg_chatbot_embedding_index').removeAttr('disabled');
                    modalContent.find('.wpaicg_chatbot_embedding_index').removeClass('asdisabled');
                    modalContent.find('.wpaicg_chatbot_qdrant_collection').removeAttr('disabled');
                    modalContent.find('.wpaicg_chatbot_qdrant_collection').removeClass('asdisabled');
                    modalContent.find('.wpaicg_chatbot_embedding_pdf').removeAttr('disabled');
                    modalContent.find('.wpaicg_chatbot_embedding_pdf').removeClass('asdisabled');
                    modalContent.find('.wpaicg_chatbot_embedding_pdf_message').removeAttr('disabled');
                    modalContent.find('.wpaicg_chatbot_embedding_pdf_message').removeClass('asdisabled');
                    modalContent.find('.wpaicg_chatbot_pdf_pages').removeAttr('disabled');
                    modalContent.find('.wpaicg_chatbot_pdf_pages').removeClass('asdisabled');
                    modalContent.find('.wpaicg_chatbot_embedding_top').removeAttr('disabled');
                    modalContent.find('.wpaicg_chatbot_embedding_top').removeClass('asdisabled');
                }
                if(key === 'limited_roles'){
                    if(typeof field === 'object'){
                        $.each(field, function(role,limit_num){
                            modalContent.find('.wpaicg_role_'+role).val(limit_num);
                        })
                    }
                }
                else if(key === 'chat_addition_text'){
                    if(field !== ''){
                        modalContent.find('.wpaicg_chatbot_chat_addition_text').val(field);
                    }
                }
                else{
                    if(typeof field === 'string' && field.indexOf('&quot;') > -1) {
                        field = field.replace(/&quot;/g, '"');
                    }
                    if(key === 'limited_message' && field === ''){
                        field = '<?php echo esc_html__('You have reached your token limit.','gpt3-ai-content-generator')?>';
                    }
                    if(key === 'log_notice_message' && !wpaicg_save_log){
                        modalContent.find('.wpaicg_chatbot_log_notice_message').attr('disabled','disabled');
                    }
                    modalContent.find('.wpaicg_chatbot_'+key).val(field);
                }
            });
            if(!wpaicg_save_log){
                modalContent.find('.wpaicg_chatbot_log_request').prop('checked',false);
                modalContent.find('.wpaicg_chatbot_log_request').attr('disabled','disabled');
                modalContent.find('.wpaicg_chatbot_log_notice').prop('checked',false);
                modalContent.find('.wpaicg_chatbot_log_notice').attr('disabled','disabled');
                modalContent.find('.wpaicg_chatbot_log_notice_message').attr('disabled','disabled');
            }
            // disable voice services if streaming is enabled wpaicg_chatbot_openai_stream_nav. wpaicg_chatbot_chat_to_speech and wpaicg_chatbot_audio_enable need to be disabled and set to false.
            if (modalContent.find('.wpaicg_chatbot_openai_stream_nav').prop('checked')) {
                modalContent.find('.wpaicg_chatbot_chat_to_speech').prop('checked', false);
                modalContent.find('.wpaicg_chatbot_chat_to_speech').attr('disabled', 'disabled');
                modalContent.find('.wpaicg_chatbot_audio_enable').prop('checked', false);
                modalContent.find('.wpaicg_chatbot_audio_enable').attr('disabled', 'disabled');
            }

            // display when .wpaicg_add_conversation_starter is clicked
            $(document).on('click', '.wpaicg_add_conversation_starter', function() {
                // Get conversation starters from the bot fields
                let conversationStarters = fields.conversation_starters;

                // Dynamically create up to 10 input fields (or as many as needed)
                let html = '';

                for (let i = 0; i < 10; i++) {
                    let starterValue = conversationStarters[i] || '';
                    html += `<div style="padding: 5px;display: flex;justify-content: space-between;align-items: center;">
                                <input class="wpaicg_conversation_starter_field" data-index="${i}" value="${starterValue}" type="text" style="width: 100%;">
                            </div>`;
                }

                html += '<div style="padding: 5px"><button class="button button-primary wpaicg_save_conversation_starters" style="width: 100%;margin: 5px 0;">Save</button></div>';

                // Assume .wpaicg_modal_title_second and .wpaicg_modal_content_second are the elements where the title and content of the modal are set
                $('.wpaicg_modal_title_second').html('Conversation Starters');
                $('.wpaicg_modal_content_second').html(html);
                $('.wpaicg-overlay-second').css('display', 'flex');
                $('.wpaicg_modal_second').show();

            });

            $('.wpaicg-overlay').show();
            $('.wpaicg_modal').show();
            if(modalContent.find('.wpaicg_voice_language').length){
                wpaicgcollectVoices(modalContent.find('.wpaicg_voice_language'));
            }
            wpaicgUpdateRealtime();
            wpaicgChatInit();
            wpaicgChangeVoiceService(modalContent.find('.wpaicg_chatbot_chat_to_speech'));
        });
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
        $(document).on('click','.wpaicg_limit_set_role',function (e){
            if(!$(e.currentTarget).hasClass('disabled')) {
                if ($('.wpaicg_modal_content .wpaicg_role_limited').prop('checked')) {
                    let html = '';
                    $.each(wpaicg_roles, function (key, role) {
                        let valueRole = $('.wpaicg_modal_content .wpaicg_role_'+key).val();
                        html += '<div style="padding: 5px;display: flex;justify-content: space-between;align-items: center;"><label><strong>'+role+'</strong></label><input class="wpaicg_update_role_limit" data-target="'+key+'" value="'+valueRole+'" placeholder="<?php echo esc_html__('Empty for no-limit','gpt3-ai-content-generator')?>" type="text"></div>';
                    });
                    html += '<div style="padding: 5px"><button class="button button-primary wpaicg_save_role_limit" style="width: 100%;margin: 5px 0;"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button></div>';
                    $('.wpaicg_modal_title_second').html('<?php echo esc_html__('Role Limit','gpt3-ai-content-generator')?>');
                    $('.wpaicg_modal_content_second').html(html);
                    $('.wpaicg-overlay-second').css('display','flex');
                    $('.wpaicg_modal_second').show();

                } else {
                    $.each(wpaicg_roles, function (key, role) {
                        $('.wpaicg_modal_content .wpaicg_role_' + key).val('');
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
        $(document).on('click', '.wpaicg_add_conversation_starter', function () {
            let html = '';

            // Dynamically create up to 10 input fields (or as many as needed)
            for (let i = 0; i < 10; i++) {
                let starterValue = $(`input[name="bot[conversation_starters][${i}]"]`).val() || '';
                html += `<div style="padding: 5px;display: flex;justify-content: space-between;align-items: center;">
                            <input class="wpaicg_conversation_starter_field" data-index="${i}" value="${starterValue}" type="text" style="width: 100%;">
                        </div>`;
            }

            html += '<div style="padding: 5px"><button class="button button-primary wpaicg_save_conversation_starters" style="width: 100%;margin: 5px 0;">Save</button></div>';

            // Assume .wpaicg_modal_title_second and .wpaicg_modal_content_second are the elements where the title and content of the modal are set
            $('.wpaicg_modal_title_second').html('Conversation Starters');
            $('.wpaicg_modal_content_second').html(html);
            $('.wpaicg-overlay-second').css('display', 'flex');
            $('.wpaicg_modal_second').show();
        });

        $(document).on('click', '.wpaicg_save_conversation_starters', function () {
            // Remove all existing hidden inputs for conversation starters to prevent outdated entries
            $('input[name^="bot[conversation_starters]"]').remove();

            // Iterate over each field to create updated hidden inputs for submission
            $('.wpaicg_conversation_starter_field').each(function(index) {
                let value = $(this).val().trim();
                if (value) {
                    // Create a new hidden input for this conversation starter
                    const hiddenInput = $('<input>')
                        .attr('type', 'hidden')
                        .attr('name', `bot[conversation_starters][${index}]`)
                        .val(value);

                    // Append the new hidden input to the form (adjust the selector to target your form specifically)
                    $('form').append(hiddenInput);
                }
            });

            // Close the modal after saving
            $('.wpaicg_modal_close_second').closest('.wpaicg_modal_second').hide();
            $('.wpaicg-overlay-second').hide();
        });


        $(document).on('click','.wpaicg_chatbot_embedding', function (e){
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding').prop('checked',true);
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding').removeClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_chat_excerpt').prop('checked',false);
            $('.wpaicg_modal_content .wpaicg_chatbot_chat_excerpt').addClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_type').removeClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_type').removeAttr('disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_vectordb').removeAttr('disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_vectordb').removeClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_index').removeClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_index').removeAttr('disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_qdrant_collection').removeClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_qdrant_collection').removeAttr('disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf').removeClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf').removeAttr('disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf_message').removeClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf_message').removeAttr('disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_pdf_pages').removeClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_pdf_pages').removeAttr('disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_top').removeClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_top').removeAttr('disabled');
        });
        $(document).on('click','.wpaicg_chatbot_chat_addition', function (e){
            if($(e.currentTarget).prop('checked')){
                $('.wpaicg_modal_content .wpaicg_chatbot_chat_addition_text').removeAttr('disabled');
                $('.wpaicg_modal_content .wpaicg_chat_addition_template').removeAttr('disabled');
            }
            else{
                $('.wpaicg_modal_content .wpaicg_chatbot_chat_addition_text').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chat_addition_template').attr('disabled','disabled');
            }
        });
        $(document).on('change', '.wpaicg_chat_addition_template',function (e){
            var addition_text_template = $(e.currentTarget).val();
            if(addition_text_template !== ''){
                $('.wpaicg_modal_content .wpaicg_chatbot_chat_addition_text').val(addition_text_template);
            }
        });
        $(document).on('click','.wpaicg_role_limited', function (e){
            if($(e.currentTarget).prop('checked')){
                $('.wpaicg_modal_content .wpaicg_chatbot_user_tokens').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_user_limited').prop('checked',false);
                $('.wpaicg_modal_content .wpaicg_limit_set_role').removeClass('disabled');
            }
            else{
                $('.wpaicg_modal_content .wpaicg_limit_set_role').addClass('disabled');
            }
        })
        $(document).on('click','.wpaicg_chatbot_user_limited', function (e){
            if($(e.currentTarget).prop('checked')){
                $('.wpaicg_modal_content .wpaicg_chatbot_user_tokens').removeAttr('disabled');
                $('.wpaicg_modal_content .wpaicg_role_limited').prop('checked',false);
                $('.wpaicg_modal_content .wpaicg_limit_set_role').addClass('disabled');
            }
            else{
                $('.wpaicg_modal_content .wpaicg_chatbot_user_tokens').attr('disabled','disabled');
            }
        });
        $(document).on('click','.wpaicg_chatbot_guest_limited', function (e){
            if($(e.currentTarget).prop('checked')){
                $('.wpaicg_modal_content .wpaicg_chatbot_guest_tokens').removeAttr('disabled');
            }
            else{
                $('.wpaicg_modal_content .wpaicg_chatbot_guest_tokens').attr('disabled','disabled');
            }
        });
        $(document).on('click','.wpaicg_chatbot_chat_excerpt', function (e){
            $('.wpaicg_modal_content .wpaicg_chatbot_chat_excerpt').prop('checked',true);
            $('.wpaicg_modal_content .wpaicg_chatbot_chat_excerpt').removeClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding').prop('checked', false);
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding').addClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_type').addClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_type').attr('disabled','disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_vectordb').addClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_vectordb').attr('disabled','disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_index').addClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_index').attr('disabled','disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_qdrant_collection').addClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_qdrant_collection').attr('disabled','disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf').addClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf').attr('disabled','disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf_message').addClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf_message').attr('disabled','disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_pdf_pages').addClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_pdf_pages').attr('disabled','disabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_top').addClass('asdisabled');
            $('.wpaicg_modal_content .wpaicg_chatbot_embedding_top').attr('disabled','disabled');
        });
        $(document).on('change', '.wpaicg_chatbot_content_aware', function (e){
            if($(e.currentTarget).val() === 'yes'){
                $('.wpaicg_modal_content .wpaicg_chatbot_chat_excerpt').prop('checked',true);
                $('.wpaicg_modal_content .wpaicg_chatbot_chat_excerpt').removeClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_chat_excerpt').removeAttr('disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding').prop('checked', false);
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding').removeAttr('disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_type').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_type').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_vectordb').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_vectordb').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_index').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_index').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_qdrant_collection').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_qdrant_collection').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf_message').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf_message').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_pdf_pages').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_pdf_pages').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_top').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_top').attr('disabled','disabled');
            }
            else{
                $('.wpaicg_modal_content .wpaicg_chatbot_chat_excerpt').prop('checked',false);
                $('.wpaicg_modal_content .wpaicg_chatbot_chat_excerpt').removeClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_chat_excerpt').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding').prop('checked', false);
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_type').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_type').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_vectordb').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_vectordb').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_index').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_index').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_qdrant_collection').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_qdrant_collection').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf_message').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_pdf_message').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_pdf_pages').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_pdf_pages').attr('disabled','disabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_top').addClass('asdisabled');
                $('.wpaicg_modal_content .wpaicg_chatbot_embedding_top').attr('disabled','disabled');
            }
        });

        // Function to handle export settings
        function exportSettings() {
            var exportSource = 'bot'; // Adjust this based on the current context (shortcode, widget, bot)

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
        $('#exportButton').on('click', function() {
            exportSettings();
        });

        // Trigger file input when the Import button is clicked
        $('#importButton').on('click', function(e) {
            e.preventDefault();
            $('#importFileInput').click();
        });

        // Handle file selection
        $('#importFileInput').on('change', function() {
            var file = this.files[0]; // Get the file
            var source = 'bot'; // Adjust based on context

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
        
    })
</script>
