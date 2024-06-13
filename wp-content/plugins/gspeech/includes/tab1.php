<?php 
// no direct access!
defined('ABSPATH') or die("No direct access");
?>

<div class="old_p">
	<h4 style="margin-bottom: -3px;margin-top: -10px;"><?php _e('GSpeech 2.X', 'GSpeech'); ?><span class="description" style="display:block;font-weight: normal"><?php echo _e('Use 2.X version', 'GSpeech')?></span></h4>
	<div class="old_p">
		<?php $checked1 = $wpgs_options['use_old_plugin'] == 0 ? 'checked="checked"' : ''; ?>
		<?php $checked2 = $wpgs_options['use_old_plugin'] == 1 ? 'checked="checked"' : ''; ?>
		<input id="wpgs_settings[use_old_plugin1]" name="wpgs_settings[use_old_plugin]" type="radio" value="0" <?php echo $checked1;?> /> 
		<label class="description" for="wpgs_settings[use_old_plugin1]"><?php _e('No', 'GSpeech'); ?></label>
		<input id="wpgs_settings[use_old_plugin2]" name="wpgs_settings[use_old_plugin]" type="radio" value="1" <?php echo $checked2;?> /> 
		<label class="description" for="wpgs_settings[use_old_plugin2]"><?php _e('Yes', 'GSpeech'); ?></label>
	</div>

	<h4 style="margin-bottom: -3px;"><?php _e('Language', 'GSpeech'); ?><span class="description" style="display:block;font-weight: normal"><?php echo _e('Your site native language', 'GSpeech')?></span></h4>
	<div class="old_p">
		<select name="wpgs_settings[language]" id="wpgs_settings[language]">
			<?php foreach($languages as $key => $language) { ?>
				<?php if($wpgs_options['language'] == $key) { $selected = 'selected="selected"'; } else { $selected = ''; } ?>
				<?php if($key == 'en' && $wpgs_options['language'] == '') { $selected = 'selected="selected"'; }?>
				<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $language; ?></option>
			<?php } ?>
		</select>
	</div>
	
	<h4 style="margin-bottom: -3px;"><?php _e('Speak Any Text', 'GSpeech'); ?><span class="description" style="display:block;font-weight: normal"><?php echo _e('Show speaker when visitors highlighted text from the site', 'GSpeech')?></span></h4>
	<div class="old_p">
		<?php $checked1 = $wpgs_options['speak_any_text'] == 1 ? 'checked="checked"' : ''; ?>
		<?php $checked2 = $wpgs_options['speak_any_text'] == 0 ? 'checked="checked"' : ''; ?>
		<input id="wpgs_settings[speak_any_text1]" name="wpgs_settings[speak_any_text]" type="radio" value="1" <?php echo $checked1;?> /> 
		<label class="description" for="wpgs_settings[speak_any_text1]"><?php _e('Yes', 'GSpeech'); ?></label>
		<input id="wpgs_settings[speak_any_text2]" name="wpgs_settings[speak_any_text]" type="radio" value="0" <?php echo $checked2;?> /> 
		<label class="description" for="wpgs_settings[speak_any_text2]"><?php _e('No', 'GSpeech'); ?></label>
	</div>

	<h4 style="margin-bottom: -3px;"><?php _e('Player Title', 'GSpeech'); ?><span class="description" style="display:block;font-weight: normal"><?php echo _e('Title when user hover the speaker', 'GSpeech')?></span></h4>
	<div class="old_p">
		<?php $checked1 = $wpgs_options['speak_any_text'] == 1 ? 'checked="checked"' : ''; ?>
		<?php $checked2 = $wpgs_options['speak_any_text'] == 0 ? 'checked="checked"' : ''; ?>
		<?php $v2_title = $wpgs_options['gspeech_v2x_title']; ?>
		<input style="width: 250px;" name="wpgs_settings[gspeech_v2x_title]" type="text" value="<?php echo $v2_title; ?>" /> 
	</div>
	
	<h4 style="margin-bottom: -3px;"><?php _e('Greeting Text', 'GSpeech'); ?></h4>
	<div class="old_p">
		<?php $value = $wpgs_options['greeting_text'] == '' ? '{gspeech style=1 language=en autoplay=1 speechtimeout=0 registered=2 hidespeaker=1}Welcome to SITENAME{/gspeech}{gspeech style=2 language=en autoplay=1 speechtimeout=0 registered=1 hidespeaker=1}Welcome REALNAME{/gspeech}' : $wpgs_options['greeting_text'];?>
		<textarea style="height: 120px;width: 460px;" id="wpgs_settings[greeting_text]" name="wpgs_settings[greeting_text]" type="text" ><?php echo $value; ?></textarea>
		<label style="display: block" class="description" for="wpgs_settings[greeting_text]"><?php _e('Greeting text to speech. Write blank to not use greeting. Use SITENAME to get the site name, USERNAME to get username, REALNAME to get user real name.<br />For more details, please read the <a style="color: #21759b" href="http://creative-solutions.net/wordpress/gspeech/documentation" target="_blank">documentation</a>', 'GSpeech'); ?></label>
	</div>
</div>