<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$wpaicg_chat_language = 'en';
?>
<form class="wpaicg-help-form" data-form="chatgpt">
    <input type="hidden" name="action" value="wpaicg_help_chatgpt">
    <?php
    wp_nonce_field('wpaicg-ajax-action');
    ?>
    <div class="wpaicg-step wpaicg-chatgpt-openai">
        <?php
        include __DIR__.'/openai.php';
        ?>
        <div class="wpaicg-align-center wpaicg_btn_actions" style="display: none">
            <button type="button" class="button button-primary wpaicg-btn-next" data-step="wpaicg-chatgpt-addition"><?php echo esc_html__('Next','gpt3-ai-content-generator')?></button>
        </div>
    </div>
    <div class="wpaicg-step wpaicg-chatgpt-addition" style="display: none">
        <?php
        $wpaicg_additions_json = file_get_contents(WPAICG_PLUGIN_DIR.'admin/chat/context.json');
        $wpaicg_additions = json_decode($wpaicg_additions_json, true);
        ?>
        <div class="wpaicg-mb-10 wpaicg-help-field wpaicg-align-center">
            <label><strong><?php echo esc_html__('Give instructions to your bot','gpt3-ai-content-generator')?></strong></label><br/>
            <p class="wpaicg_chat_instruction">
                <?php echo esc_html__("Tell your bot what to do. Simply write your instructions in the box or select a template. It's just like giving directions to a friend!",'gpt3-ai-content-generator')?>
            </p>
            <select class="wpaicg_chat_addition_template">
                <option value=""><?php echo esc_html__('Select Template','gpt3-ai-content-generator')?></option>
                <?php
                foreach($wpaicg_additions as $key=>$wpaicg_addition){
                    echo '<option value="'.esc_html($wpaicg_addition).'">'.esc_html($key).'</option>';
                }
                ?>
            </select>
        </div>
        <div class="wpaicg-mb-10 wpaicg-help-field wpaicg-align-center">
            <textarea class="" name="chatgpt[chat_addition_text]" rows="8"><?php echo esc_html__('You are a helpful AI assistant..')?></textarea>
        </div>
        <div class="wpaicg-align-center wpaicg_btn_actions">
            <button type="button" class="button button-primary wpaicg-btn-prev" data-step="wpaicg-chatgpt-addition"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
            &nbsp;<button type="button" class="button button-primary wpaicg-btn-next" data-step="wpaicg-chatgpt-type"><?php echo esc_html__('Next','gpt3-ai-content-generator')?></button>
        </div>
    </div>
    <div class="wpaicg-step wpaicg-chatgpt-type" style="display: none">
        <div class="wpaicg-mb-10 wpaicg-help-field wpaicg-align-center">
            <label class="wpaicg-mb-10"><strong><?php echo esc_html__('Do you want to add shortcode or widget','gpt3-ai-content-generator')?></strong></label><br/>
            <p class="wpaicg_chat_instruction">
                <?php echo esc_html__("In this step, you decide if you want to use a shortcut or a widget for your bot. A shortcut is a quick way to use the bot. A widget is a small icon that appears on your screen. Choose the one that you find easier.",'gpt3-ai-content-generator')?>
            </p>
            <p>
                <label><input name="chatgpt[type]" value="shortcode" checked type="radio">&nbsp;<?php echo esc_html__('Shortcode','gpt3-ai-content-generator')?></label>
                &nbsp;&nbsp;<label><input name="chatgpt[type]" value="widget" type="radio">&nbsp;<?php echo esc_html__('Widget','gpt3-ai-content-generator')?></label>
            </p>
        </div>
        <div class="wpaicg-align-center wpaicg-action-message"></div>
        <div class="wpaicg-align-center wpaicg_btn_actions">
            <button type="button" class="button button-primary wpaicg-btn-prev" data-step="wpaicg-chatgpt-addition"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
            &nbsp;<button type="button" class="button button-primary wpaicg-help-save-chatgpt" data-step="wpaicg-chatgpt-success-shortcode"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
        </div>
    </div>
    <div class="wpaicg-step wpaicg-chatgpt-position" style="display: none">
        <div class="wpaicg-mb-10 wpaicg-help-field wpaicg-align-center">
            <label class="wpaicg-mb-10"><strong><?php echo esc_html__('Choose Your Widget Location','gpt3-ai-content-generator')?></strong></label>
        </div>
        <p class="wpaicg_chat_instruction">
            <?php echo esc_html__("Decide where you want the widget to appear on your site. You can either place it on your entire website or just on specific pages. Additionally, choose if you want it to be positioned at the bottom left or bottom right.",'gpt3-ai-content-generator')?>
        </p>
        <div class="wpaicg-mb-10 wpaicg-align-center">
            <label><input name="chatgpt[widget]" value="whole" checked type="radio">&nbsp;<?php echo esc_html__('Whole website','gpt3-ai-content-generator')?></label>
            &nbsp;&nbsp;<label><input name="chatgpt[widget]" value="page" type="radio">&nbsp;<?php echo esc_html__('Specific pages','gpt3-ai-content-generator')?></label>
        </div>
        <p class="wpaicg-help-field wpaicg-chatgpt-post-id" style="display: none">
            <label class="wpaicg-mb-10"><strong><?php echo esc_html__('Page/Post ID','gpt3-ai-content-generator')?></strong></label>
            <input type="text" name="chatgpt[pages]">
        </p>
        <div class="wpaicg-mb-10 wpaicg-align-center" style="margin-top: 20px">
            <label><strong><?php echo esc_html__('Position of Widget','gpt3-ai-content-generator')?></strong></label><br>
            <label><input name="chatgpt[position]" value="left" checked type="radio">&nbsp;<?php echo esc_html__('Bottom Left','gpt3-ai-content-generator')?></label>
            &nbsp;&nbsp;<label><input name="chatgpt[position]" value="right" type="radio">&nbsp;<?php echo esc_html__('Bottom Right','gpt3-ai-content-generator')?></label>
        </div>
        <div class="wpaicg-align-center wpaicg-action-message"></div>
        <div class="wpaicg-align-center wpaicg_btn_actions">
            <button type="button" class="button button-primary wpaicg-btn-prev" data-step="wpaicg-chatgpt-type"><?php echo esc_html__('Previous','gpt3-ai-content-generator')?></button>
            &nbsp;<button type="button" class="button button-primary wpaicg-help-save-chatgpt" data-step="wpaicg-chatgpt-success-widget"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
        </div>
    </div>
    <div class="wpaicg-step wpaicg-chatgpt-success-shortcode wpaicg-align-center" style="display: none">
        <p style="color:#187c00"><?php echo esc_html__('Congratulations! Your bot is ready!','gpt3-ai-content-generator')?></p>
        <p style="color:#187c00"><?php echo esc_html__('Copy and paste below code in your page','gpt3-ai-content-generator')?></p>
        <p><strong style="color:#187c00">[wpaicg_chatgpt]</strong></p>
        <p class="wpaicg-align-center">
            <a href="https://docs.aipower.org/docs/ChatGPT/chatgpt-wordpress" target="_blank"><?php echo esc_html__('Read Tutorial','gpt3-ai-content-generator')?></a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a href="<?php echo admin_url('admin.php?page=wpaicg_chatgpt&action=bots')?>"><?php echo esc_html__('Go to Settings','gpt3-ai-content-generator')?></a>
        </p>
    </div>
    <div class="wpaicg-step wpaicg-chatgpt-success-widget wpaicg-align-center" style="display: none">
        <p style="color:#187c00"><?php echo esc_html__('Congratulations! Your bot is ready!','gpt3-ai-content-generator')?></p>
        <p class="wpaicg-align-center">
           <a href="https://docs.aipower.org/docs/ChatGPT/chatgpt-wordpress" target="_blank"><?php echo esc_html__('Read Tutorial','gpt3-ai-content-generator')?></a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a href="<?php echo admin_url('admin.php?page=wpaicg_chatgpt&action=bots')?>"><?php echo esc_html__('Go to Settings','gpt3-ai-content-generator')?></a>
        </p>
    </div>
    <div class="wpaicg-align-center wpaicg-action-message"></div>
</form>

