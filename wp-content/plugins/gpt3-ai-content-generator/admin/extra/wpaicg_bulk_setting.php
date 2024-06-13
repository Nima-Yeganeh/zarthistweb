<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$success_save = false;
// Check if the form was submitted
if(isset($_POST['save_bulk_setting'])) {
    // Verify nonce for security
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'save_bulk_setting_nonce')) {
        die(esc_html__('Nonce verification failed', 'gpt3-ai-content-generator'));
    }

    // Define a list of options to handle, with their sanitization callbacks
    $options = [
        'wpaicg_restart_queue' => 'sanitize_text_field',
        'wpaicg_try_queue' => 'sanitize_text_field',
        'wpaicg_custom_prompt_auto' => 'wp_kses_post', // Assuming this one needs to allow some HTML
        'wpaicg_custom_prompt_enable' => 'boolval', // Converts to boolean true/false
        'wpaicg_rss_new_title' => 'boolval', // Converts to boolean true/false
        'wpaicg_rss_keywords' => 'sanitize_text_field',
        'wpaicg_rss_use_description' => 'boolval',
    ];

    foreach ($options as $option_name => $sanitization_callback) {
        if (isset($_POST[$option_name]) && !empty($_POST[$option_name])) {
            // Sanitize and update the option
            $value = call_user_func($sanitization_callback, $_POST[$option_name]);
            update_option($option_name, $value);
        } else {
            // Delete the option if not set or empty
            delete_option($option_name);
        }
    }

    // Successfully saved settings
    $success_save = true;
}

$wpaicg_restart_queue = get_option('wpaicg_restart_queue', 20);
$wpaicg_try_queue = get_option('wpaicg_try_queue', '');
$wpaicg_ai_model = get_option('wpaicg_ai_model','');
$wpaicg_custom_prompt_enable = get_option('wpaicg_custom_prompt_enable',false);
$wpaicg_default_custom_prompt = 'Create a compelling and well-researched article of at least 500 words on the topic of "[title]" in English. Structure the article with clear headings enclosed within the appropriate heading tags (e.g., <h1>, <h2>, etc.) and engaging subheadings. Ensure that the content is informative and provides valuable insights to the reader. Incorporate relevant examples, case studies, and statistics to support your points. Organize your ideas using unordered lists with <ul> and <li> tags where appropriate. Conclude with a strong summary that ties together the key takeaways of the article. Remember to enclose headings in the specified heading tags to make parsing the content easier. Additionally, wrap even paragraphs in <p> tags for improved readability. Do not start your response with ```html.';
$wpaicg_custom_prompt_auto = get_option('wpaicg_custom_prompt_auto',$wpaicg_default_custom_prompt);
$wpaicg_rss_new_title = get_option('wpaicg_rss_new_title',false);
$wpaicg_rss_keywords = get_option('wpaicg_rss_keywords', ''); // New field for keywords
$wpaicg_rss_use_description = get_option('wpaicg_rss_use_description', false);
?>
<?php
if($success_save){
    echo '<div class="wpaicg_sheets_cron_msg">Record updated successfully</div>';
}
?>
<form action="" method="post" class="wpaicg_auto_settings">
    <?php wp_nonce_field('save_bulk_setting_nonce'); ?>
    <h1><?php echo esc_html__('Queue','gpt3-ai-content-generator')?></h1>
    <div class="nice-form-group">
        <label><?php echo esc_html__('Restart Failed Jobs After','gpt3-ai-content-generator')?></label>
        <select name="wpaicg_restart_queue" style="width: 120px;">
            <?php
            for($i = 20; $i <=60; $i+=10){
                echo '<option'.($wpaicg_restart_queue == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
            }
            ?>
        </select>
        <?php echo esc_html__('minutes','gpt3-ai-content-generator')?>
        <a href="https://docs.aipower.org/docs/AutoGPT/auto-content-writer/bulk-editor#auto-restart-failed-jobs" target="_blank">?</a>
    </div>
    <div class="nice-form-group">
        <label><?php echo esc_html__('Try Queue','gpt3-ai-content-generator')?></label>
        <select name="wpaicg_try_queue" style="width: 120px;">
            <?php
            for($i = 1; $i <=10; $i++){
                echo '<option'.($wpaicg_try_queue == $i ? ' selected':'').' value="'.esc_html($i).'">'.esc_html($i).'</option>';
            }
            ?>
        </select>
        <?php echo esc_html__('times','gpt3-ai-content-generator')?>
        <a href="https://docs.aipower.org/docs/AutoGPT/auto-content-writer/bulk-editor#auto-restart-failed-jobs" target="_blank">?</a>
    </div>
    <p></p>
    <h1><?php echo esc_html__('RSS','gpt3-ai-content-generator')?></h1>
    <div class="nice-form-group">
        <input <?php echo \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ? '' : ' disabled'?>
        <?php echo \WPAICG\wpaicg_util_core()->wpaicg_is_pro() && $wpaicg_rss_new_title ? ' checked':''?> class="wpaicg_rss_new_title" type="checkbox" value="1" name="wpaicg_rss_new_title">
        <label><?php echo esc_html__('Generate New Title','gpt3-ai-content-generator')?></label>
        <?php if(!\WPAICG\wpaicg_util_core()->wpaicg_is_pro()): ?>
        <!-- Display Pro label instead of Available in Pro text -->
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpaicg-pricing')); ?>" class="pro-feature-label"><?php echo esc_html__('Pro','gpt3-ai-content-generator')?></a>
        <?php endif; ?>
        <a href="https://docs.aipower.org/docs/AutoGPT/auto-content-writer/rss#generate-new-title" target="_blank">?</a>
    </div>
    <div class="nice-form-group">
        <label for="wpaicg_rss_keywords"><?php echo esc_html__('Keywords to Filter (comma separated)','gpt3-ai-content-generator')?></label>
        <input type="text" id="wpaicg_rss_keywords" name="wpaicg_rss_keywords" value="<?php echo esc_attr($wpaicg_rss_keywords); ?>" style="width: 50%;" <?php echo \WPAICG\wpaicg_util_core()->wpaicg_is_pro() ? '' : ' disabled'?>>
        <?php if(!\WPAICG\wpaicg_util_core()->wpaicg_is_pro()): ?>
        <!-- Display Pro label instead of Available in Pro text -->
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpaicg-pricing')); ?>" class="pro-feature-label"><?php echo esc_html__('Pro','gpt3-ai-content-generator')?></a>
        <?php endif; ?>
        <a href="https://docs.aipower.org/docs/AutoGPT/auto-content-writer/rss#keyword-filtering" target="_blank">?</a>
    </div>
    <p></p>
    <h1><?php echo esc_html__('Content Generation','gpt3-ai-content-generator')?></h1>

    <div class="nice-form-group">
        <input <?php echo $wpaicg_custom_prompt_enable ? ' checked':''?> class="wpaicg_custom_prompt_enable" type="checkbox" value="1" name="wpaicg_custom_prompt_enable">
        <label><?php echo esc_html__('Enable Custom Prompt','gpt3-ai-content-generator')?></label>
        <a href="https://docs.aipower.org/docs/AutoGPT/auto-content-writer/bulk-editor#using-custom-prompt" target="_blank">?</a>
    </div>
    <div style="<?php echo $wpaicg_custom_prompt_enable ? '' : 'display:none'?>" class="wpaicg_custom_prompt_auto">
        <div class="nice-form-group">
            <textarea rows="15" class="wpaicg_custom_prompt_auto_text" name="wpaicg_custom_prompt_auto"><?php echo esc_html(str_replace("\\",'',$wpaicg_custom_prompt_auto))?></textarea>
            <p style="display: flex;justify-content: space-between;align-items: flex-start;">
                <?php if(\WPAICG\wpaicg_util_core()->wpaicg_is_pro()): ?>
                <small style="white-space: break-spaces;"><?php echo sprintf(
                    /* translators: 1: title code, 2: keywords_to_include code, 3: keywords_to_avoid code */
                    esc_html__('Make sure to include %1$s in your prompt. You can also add %2$s and %3$s to further customize your prompt.','gpt3-ai-content-generator'),
                    '<code>[title]</code>',
                    '<code>[keywords_to_include]</code>',
                    '<code>[keywords_to_avoid]</code>'
                )?>
                </small>
                <?php else: ?>
                <small>
                    <?php echo sprintf(esc_html__('Make sure %s is included in your prompt.','gpt3-ai-content-generator'),'<code>[title]</code>')?>
                </small>
                <?php endif; ?>
            <button style="color: #fff;background: #df0707;border-color: #df0707;" data-prompt="<?php echo esc_html($wpaicg_default_custom_prompt)?>" class="button wpaicg_custom_prompt_reset" type="button"><?php echo esc_html__('Reset','gpt3-ai-content-generator')?></button>
            </p>
        </div>
        <div class="wpaicg_custom_prompt_auto_error"></div>
    </div>
    <p></p>
    <button class="button-primary button wpaicg_auto_settings_save" name="save_bulk_setting"><?php echo esc_html__('Save','gpt3-ai-content-generator')?></button>
</form>
<script>
    jQuery(document).ready(function ($){
        let wpaicg_ai_model = '<?php echo esc_html($wpaicg_ai_model)?>';
        $('.wpaicg_custom_prompt_enable').click(function (){
            if($(this).prop('checked')){
                $('.wpaicg_custom_prompt_auto').show();
                $('.wpaicg_custom_prompt_guide').show();
            }
            else{
                $('.wpaicg_custom_prompt_auto').hide();
                $('.wpaicg_custom_prompt_guide').hide();
            }
        });
        <?php
        if(!\WPAICG\wpaicg_util_core()->wpaicg_is_pro()):
        ?>
        $('.wpaicg_custom_prompt_auto_text').on('input', function (e){
            let prompt = $(e.currentTarget).val();
            if(prompt.indexOf('[keywords_to_include]') > -1 || prompt.indexOf('[keywords_to_avoid]') > -1){
                $('.wpaicg_custom_prompt_auto_error').html('<div style="color: #f00"><p><?php echo esc_html__('Please note that keywords are only available in pro plan. Please remove keywords from your prompt','gpt3-ai-content-generator')?></p></div>');
                $('.wpaicg_auto_settings_save').attr('disabled','disabled');
            }
            else{
                $('.wpaicg_custom_prompt_auto_error').empty();
                $('.wpaicg_auto_settings_save').removeAttr('disabled');
            }
        });
        <?php
        endif;
        ?>
        $('.wpaicg_custom_prompt_reset').click(function (){
            let prompt = $(this).attr('data-prompt');
            $('textarea[name=wpaicg_custom_prompt_auto]').val(prompt);
            $('.wpaicg_custom_prompt_auto_error').empty();
            $('.wpaicg_auto_settings_save').removeAttr('disabled');
        });
    })
</script>
