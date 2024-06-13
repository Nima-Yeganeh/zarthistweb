<?php

// no direct access!
defined('ABSPATH') or die("No direct access");

class GSpeech_Front {

	private static $gsp_widget_id = '';
    private static $version_index_1 = '';
    private static $wpgs_load_sh = '';
    private static $sh_ = '';
    private static $lazy_load = '';
    private static $player_title = '';

	private static function init() {

        global $wpdb;

        $data = get_option('wpgs_settings');
        GSpeech::load_defaults($data);

        $wpgs_options = GSpeech::load_settings($data);

        // get gspeech data
        $sql_g = "SELECT * FROM ".$wpdb->prefix."gspeech_data";
        $row_g = $wpdb->get_row($sql_g);

        $lazy_load = intval($row_g->lazy_load);
        $gsp_widget_id = $row_g->widget_id;
        $gsp_crypto = $row_g->crypto;
        $gsp_reload_session = intval($row_g->reload_session);
        $version_index_1 = intval($row_g->version_index);
        $gsp_user_email = $row_g->email;
        $wpgs_load_sh = intval($row_g->sh_w_loaded);
        $sh_ = intval($row_g->sh_);

        $use_old_plugin = $wpgs_options['use_old_plugin'];
        $player_title = $wpgs_options['gspeech_v2x_title'];

        $gsp_widget_id = $use_old_plugin == 1 ? '' : $gsp_widget_id;

        self::$gsp_widget_id = $gsp_widget_id;
        self::$version_index_1 = $version_index_1;
        self::$wpgs_load_sh = $wpgs_load_sh;
        self::$sh_ = $sh_;
        self::$lazy_load = $lazy_load;
        self::$player_title = $player_title;
    }

	public static function prepare_html($content) {

		global $wpdb;

		global $gspeech_s_enc;
		global $gspeech_h_enc;
		global $gspeech_hh_enc;

		$plugin_version = GSPEECH_PLG_VERSION;

		$gsp_widget_id = self::$gsp_widget_id;
        $version_index_1 = self::$version_index_1;
        $lazy_load = self::$lazy_load;

		$domain = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$current_user = wp_get_current_user();
		$username =  $current_user->user_login;
		$realname = $current_user->display_name;
		$useremail =  $current_user->user_email;
		$m_ = get_option('admin_email','');
		$n_ = get_option('blogname','');

		$str = 'domain=' . $domain . '&email=' . $m_  . '&name=' . $n_  . '&version=' . $plugin_version;
		$d_ = base64_encode($str);

		self::make_statistics($d_);
		
		$widget_id = $gsp_widget_id;

		$gspeech_content = '';
		$greeting_text = '';

		if($widget_id == '') {

			$modified_data = self::render_old_gspeech($content);

			$content_mod = $modified_data[0];
			$gspeech_content_mod = $modified_data[1];
			$greeting_text_mod = $modified_data[2];


			$content = $content_mod;
			$gspeech_content = $gspeech_content_mod;
			$greeting_text = $greeting_text_mod;
		}

	    if($widget_id == '') {

	        $is_htm = strpos($content, '<body');

	        if($is_htm)
	        	$content = preg_replace('/<body([^>]*)?>/si', "<body$1>" . $greeting_text, $content);
	    }

	    if($gspeech_content != '')
	   		$content = str_replace('</body>', $gspeech_content . '</body>', $content);

	   	// 3.1.x
	   	if($widget_id != '') {

	   		$content = preg_replace('/\[gspeech\]/si', '<div class="gsp_full_player"></div>', $content);
	   		$content = preg_replace('/\[gspeech type=full\]/si', '<div class="gsp_full_player"></div>', $content);
	   		$content = preg_replace('/\[gspeech-full\]/si', '<div class="gsp_full_player"></div>', $content);
	   		$content = preg_replace('/\[gspeech type=button\]/si', '<div class="gsp_button_player"></div>', $content);
	   		$content = preg_replace('/\[gspeech-button\]/si', '<div class="gsp_button_player"></div>', $content);
	   		$content = preg_replace('/\[gspeech type=circle\]/si', '<div class="gsp_circle_player"></div>', $content);
	   		$content = preg_replace('/\[gspeech-circle\]/si', '<div class="gsp_circle_player"></div>', $content);

	   		// custom id
	   		$content = preg_replace('/\[gspeech id=(\d+)\]/si', '<div class="gsp_shortcode_$1"></div>', $content);

	   		// shortcode inner text by id
	   		$content = preg_replace('/\{gspeech id=(\d+)\}(.*?)\{\/gspeech\}/si', '
	   			<div class="gsp_inline_shortcode">
					<div class="gsp_inline_shortcode_player_wrp">
						<div class="gsp_shortcode_$1"></div>
					</div>
					<div class="gsp_inline_shortcode_txt_wrp">
						<div class="gsp_shortcode_txt_element gsp_shortcode_wrp_$1">$2</div>
					</div>
				</div>', $content);

	   		// shortcode inner text default
	   		$content = preg_replace('/\{gspeech\}(.*?)\{\/gspeech\}/si', '
	   			<div class="gsp_inline_shortcode">
					<div class="gsp_inline_shortcode_player_wrp">
						<div class="gsp_def_inline_shortcode"></div>
					</div>
					<div class="gsp_inline_shortcode_txt_wrp">
						<div class="gsp_shortcode_txt_element">$1</div>
					</div>
				</div>', $content);

	   		// 3.2.0 fix
	   		$gsp_data_html = '<div id="gsp_data_html" data-g_version="'.$plugin_version.'" data-w_id="'.$widget_id.'" data-s_enc="'.$gspeech_s_enc.'" data-h_enc="'.$gspeech_h_enc.'" data-hh_enc="'.$gspeech_hh_enc.'" data-lazy_load="'.$lazy_load.'" data-vv_index="'.$version_index_1.'"></div>' . "\n";
	   		$content = str_replace('</body>', $gsp_data_html . '</body>', $content);
	   	}

		return $content;
	}

	public static function render_old_gspeech($content_val) {

		$plugin_version = GSPEECH_PLG_VERSION;
		$gsp_player_title = self::$player_title;

		function wpgs_strip_htm($matches) {

	    	$data = get_option('wpgs_settings');
	        GSpeech::load_defaults($data);

	        $wpgs_options = GSpeech::load_settings($data);

	        $gsp_player_title = $wpgs_options['gspeech_v2x_title'];

	    	$userid = get_current_user_id();
	    	$current_user = wp_get_current_user();
	    	$sitename = get_bloginfo('name');
	    	
			$speech_title = $gsp_player_title;
	    	$userRegistered =  $userid == 0 ? 0 : 1;

	    	$username =  $current_user->user_login;
	    	$realname = $current_user->display_name;
	    	 
	    	if($userRegistered == 0 && $matches[10] == 1) {
	    		if($matches[16] != 1) {
	    			return $matches[17];
	    		}
	    		else return;
	    	}
	    	if($userRegistered == 1 && $matches[10] == 2) {
	    		if($matches[16] != 1) {
	    			return $matches[17];
	    		}
	    		else return;
	    	}
	    	 
	    	$htm = strip_tags($matches[17]);
	    	$htm = preg_replace('/<script\b[^>]*>(.*?)<\/script>/si', "", $htm);
	    	$htm = preg_replace('/<style\b[^>]*>(.*?)<\/style>/si', "", $htm);
	    	$htm = str_replace(array("\"","'"),"",$htm);
	    	 
	    	$htm = str_replace("SITENAME",$sitename,$htm);
	    	if($userRegistered == 1) {
	    		$htm = str_replace("USERNAME",$username,$htm);
	    		$htm = str_replace("REALNAME",$realname,$htm);
	    	}
	    	 
	    	$htm_original = str_replace("SITENAME",$sitename,$matches[17]);
	    	if($userRegistered == 1) {
	    		$htm_original = str_replace("USERNAME",$username,$htm_original);
	    		$htm_original = str_replace("REALNAME",$realname,$htm_original);
	    	}
	    	 
	    	$hidespeaker_pre = $matches[16] == 1 ? '<div style="display:none">' : '';
	    	$hidespeaker_af = $matches[16] == 1 ? '</div>' : '';
	    	
	    	$style_index = $matches[2] == '' ? 1 : $matches[2];

	    	return $hidespeaker_pre.'
	    	<span class="gspeech_selection gspeech_style_'.$style_index.'" roll="'.$style_index.'">'.$htm_original.'</span>
	    	<span class="gspeech_pro_main_wrapper">&nbsp;
	    	<span class="sexy_tooltip"><span class="the-tooltip top left sexy_tooltip_'.$style_index.'"><span class="tooltip_inner">'.$speech_title.'</span></span></span>
	    	<span class="sound_container_pro sound_div_'.$style_index.'" language="'.$matches[4].'" roll="'.$style_index.'" autoplaypro="'.$matches[6].'" speechtimeout="'.$matches[8].'" selector="'.$matches[12].'" eventpro="'.$matches[14].'" title="" style=""><span class="sound_text_pro">'.$htm.'</span></span>
	    	</span>'.$hidespeaker_af;
	    }

		$tr_tool = 'g';
		$speech_lenght = '100';
		$plugin_folder_name = 'gspeech';

		$data = get_option('wpgs_settings');
		GSpeech::load_defaults($data);

		$wpgs_options = GSpeech::load_settings($data);

		$gspeech_lang = $wpgs_options['language'];
		$speak_any_text = $wpgs_options['speak_any_text'];
		$greeting_text = $wpgs_options['greeting_text'];
		$greeting_text = $greeting_text == 'blank' ? '' : $greeting_text;

		$bcp1 = $wpgs_options['bcp1'];
		$cp1 = $wpgs_options['cp1'];
		$bca1 = $wpgs_options['bca1'];
		$ca1 = $wpgs_options['ca1'];
		$spop1 = $wpgs_options['spop1'];
		$spop1_ = $spop1 / 100;
		$spoa1 = $wpgs_options['spoa1'];
		$spoa1_ = $spoa1 / 100;
		$animation_time_1 = $wpgs_options['animation_time_1'];
		$speaker_type_1 = $wpgs_options['speaker_type_1'];
		$speaker_size_1 = $wpgs_options['speaker_size_1'];
		$tooltip_1 = $wpgs_options['tooltip_1'];

		$bcp2 = $wpgs_options['bcp2'];
		$cp2 = $wpgs_options['cp2'];
		$bca2 = $wpgs_options['bca2'];
		$ca2 = $wpgs_options['ca2'];
		$spop2 = $wpgs_options['spop2'];
		$spop2_ = $spop2 / 100;
		$spoa2 = $wpgs_options['spoa2'];
		$spoa2_ = $spoa2 / 100;
		$animation_time_2 = $wpgs_options['animation_time_2'];
		$speaker_type_2 = $wpgs_options['speaker_type_2'];
		$speaker_size_2 = $wpgs_options['speaker_size_2'];
		$tooltip_2 = $wpgs_options['tooltip_2'];

		$bcp3 = $wpgs_options['bcp3'];
		$cp3 = $wpgs_options['cp3'];
		$bca3 = $wpgs_options['bca3'];
		$ca3 = $wpgs_options['ca3'];
		$spop3 = $wpgs_options['spop3'];
		$spop3_ = $spop3 / 100;
		$spoa3 = $wpgs_options['spoa3'];
		$spoa3_ = $spoa3 / 100;
		$animation_time_3 = $wpgs_options['animation_time_3'];
		$speaker_type_3 = $wpgs_options['speaker_type_3'];
		$speaker_size_3 = $wpgs_options['speaker_size_3'];
		$tooltip_3 = $wpgs_options['tooltip_3'];

		$bcp4 = $wpgs_options['bcp4'];
		$cp4 = $wpgs_options['cp4'];
		$bca4 = $wpgs_options['bca4'];
		$ca4 = $wpgs_options['ca4'];
		$spop4 = $wpgs_options['spop4'];
		$spop4_ = $spop4 / 100;
		$spoa4 = $wpgs_options['spoa4'];
		$spoa4_ = $spoa4 / 100;
		$animation_time_4 = $wpgs_options['animation_time_4'];
		$speaker_type_4 = $wpgs_options['speaker_type_4'];
		$speaker_size_4 = $wpgs_options['speaker_size_4'];
		$tooltip_4 = $wpgs_options['tooltip_4'];

		$bcp5 = $wpgs_options['bcp5'];
		$cp5 = $wpgs_options['cp5'];
		$bca5 = $wpgs_options['bca5'];
		$ca5 = $wpgs_options['ca5'];
		$spop5 = $wpgs_options['spop5'];
		$spop5_ = $spop5 / 100;
		$spoa5 = $wpgs_options['spoa5'];
		$spoa5_ = $spoa5 / 100;
		$animation_time_5 = $wpgs_options['animation_time_5'];
		$speaker_type_5 = $wpgs_options['speaker_type_5'];
		$speaker_size_5 = $wpgs_options['speaker_size_5'];
		$tooltip_5 = $wpgs_options['tooltip_5'];

		$speaker_types_array = array("1" => $speaker_type_1, "2" => $speaker_type_2, "3" => $speaker_type_3, "4" => $speaker_type_4, "5" => $speaker_type_5);
		$speaker_sizes_array = array("1" => $speaker_size_1, "2" => $speaker_size_2, "3" => $speaker_size_3, "4" => $speaker_size_4, "5" => $speaker_size_5);
		$tooltips_array = array("1" => $tooltip_1, "2" => $tooltip_2, "3" => $tooltip_3, "4" => $tooltip_4, "5" => $tooltip_5);
		
		$code_path =  plugins_url() . '/'. $plugin_folder_name .'/';
		$gspeech_js = $speak_any_text == 1 ? '<script src="'.$code_path.'includes/js/gspeech.js?ver='.$plugin_version.'"></script>' : '';

		$curl_enabled = function_exists('curl_version') ? true : false;
		$streamerphp_folder = $curl_enabled ? $code_path : 'https://gspeech.io/';

		$speech_title = $gsp_player_title;
		$gspeech_content_inner = '
	        <span id="sexy_tooltip_title"><span class="the-tooltip top left '.$tooltip_2.'"><span class="tooltip_inner">'.$speech_title.'</span></span></span>
	        <div id="sound_container" class="sound_div sound_div_basic size_'.$speaker_size_2.' '.$speaker_type_2.'" title="" style=""><div id="sound_text"></div>
	        </div><div id="sound_audio"></div>
	        <script type="text/javascript">
	        	var players = new Array(),
	            	blink_timer = new Array(),
	            	rotate_timer = new Array(),
	            	lang_identifier = "'.$gspeech_lang.'",
	            	selected_txt = "",
	            	sound_container_clicked = false,
	            	sound_container_visible = true,
	            	blinking_enable = true,
	            	basic_plg_enable = true,
	            	pro_container_clicked = false,
	            	streamerphp_folder = "'.$streamerphp_folder.'",
	            	translation_tool = "'.$tr_tool.'",
	            	//translation_audio_type = \'audio/x-wav\',
	            	translation_audio_type = \'audio/mpeg\',
	            	speech_text_length = "'.$speech_lenght.'",
	            	blink_start_enable_pro = true,
	            	createtriggerspeechcount = 0,
					speechtimeoutfinal = 0,
					speechtxt = "",
	            	userRegistered = "'.$userRegistered.'",
	            	gspeech_bcp = ["'.$bcp1.'","'.$bcp2.'","'.$bcp3.'","'.$bcp4.'","'.$bcp5.'"],
			    	gspeech_cp = ["'.$cp1.'","'.$cp2.'","'.$cp3.'","'.$cp4.'","'.$cp5.'"],
			    	gspeech_bca = ["'.$bca1.'","'.$bca2.'","'.$bca3.'","'.$bca4.'","'.$bca5.'"],
			    	gspeech_ca = ["'.$ca1.'","'.$ca2.'","'.$ca3.'","'.$ca4.'","'.$ca5.'"],
			    	gspeech_spop = ["'.$spop1.'","'.$spop2.'","'.$spop3.'","'.$spop4.'","'.$spop5.'"],
			    	gspeech_spoa = ["'.$spoa1.'","'.$spoa2.'","'.$spoa3.'","'.$spoa4.'","'.$spoa5.'"],
			    	gspeech_animation_time = ["'.$animation_time_1.'","'.$animation_time_2.'","'.$animation_time_3.'","'.$animation_time_4.'","'.$animation_time_5.'"];
	        </script>
	        <script type="text/javascript" src="'.$code_path.'includes/js/gspeech_pro.js?ver='.$plugin_version.'"></script>
	        '.$gspeech_js.'
	        <!--[if (gte IE 6)&(lte IE 8)]>
			<script defer src="'.$code_path . 'includes/js/nwmatcher-1.2.4-min.js"></script>
			<script defer src="'.$code_path . 'includes/js/selectivizr-min.js"></script>
			<![endif]-->
	        <style type="text/css">.gspeech_style_,.gspeech_style_1{background-color:'.$bcp1.';color:'.$cp1.';}.gspeech_style_2{background-color:'.$bcp2.';color:'.$cp2.';}.gspeech_style_3{background-color:'.$bcp3.';color:'.$cp3.';}.gspeech_style_4{background-color:'.$bcp4.';color:'.$cp4.';}.gspeech_style_5{background-color:'.$bcp5.';color:'.$cp5.';}</style>
	        <style type="text/css">.gspeech_style_.active,.gspeech_style_1.active{background-color:'.$bca1.' !important;color:'.$ca1.' !important;.gspeech_style_2.active{background-color:'.$bca2.' !important;color:'.$ca2.' !important;.gspeech_style_3.active{background-color:'.$bca3.' !important;color:'.$ca3.' !important;.gspeech_style_4.active{background-color:'.$bca4.' !important;color:'.$ca4.' !important;.gspeech_style_5.active{background-color:'.$bca5.' !important;color:'.$ca5.' !important;</style>
	        <style type="text/css">.sound_div_,.sound_div_1{opacity:'.$spop1_.';filter: alpha(opacity = '.$spop1.')}.sound_div_2{opacity:'.$spop2_.';filter: alpha(opacity = '.$spop2.')}.sound_div_3{opacity:'.$spop3_.';filter: alpha(opacity = '.$spop3.')}.sound_div_4{opacity:'.$spop4_.';filter: alpha(opacity = '.$spop4.')}.sound_div_5{opacity:'.$spop5_.';filter: alpha(opacity = '.$spop5.')}</style>
	        <style type="text/css">
	           ::selection {
					background: '.$bca2.';
					color: '.$ca2.';
				}
				::-moz-selection {
					background: '.$bca2.';
					color: '.$ca2.';
				}
	        </style>
		';
        
	 	$greeting_text = preg_replace_callback('/{gspeech( style=([\d]*?))?( language=([\S]*?))?( autoplay=([\d]*?))?( speechtimeout=([\d]*?))?( registered=([\d]*?))?( selector=(.*?))?( event=(.*?))?( hidespeaker=([\d]*?))?[\s]?}(.*?){\/gspeech}/si', 'wpgs_strip_htm', $greeting_text, 2);
        $greeting_text = preg_replace('/{gspeech[^}]*?}/si', '', $greeting_text);
        $greeting_text = preg_replace('/{\/gspeech}/si', '', $greeting_text);
        $greeting_text = str_replace('sound_container_pro','sound_container_pro greeting_block',$greeting_text);
        
        $content_val = preg_replace_callback('/{gspeech( style=([\d]*?))?( language=([\S]*?))?( autoplay=([\d]*?))?( speechtimeout=([\d]*?))?( registered=([\d]*?))?( selector=(.*?))?( event=(.*?))?( hidespeaker=([\d]*?))?[\s]?}(.*?){\/gspeech}/si', 'wpgs_strip_htm', $content_val, 1);
        $content_val = preg_replace('/{gspeech[^}]*?}/si', '', $content_val);
        $content_val = preg_replace('/{\/gspeech}/si', '', $content_val);
        
        $style_index = 1;
        $speaker_type = $speaker_types_array[$style_index];
        $speaker_size = $speaker_sizes_array[$style_index];
        $replace_val1 = 'sound_div_1';
        $replace_val2 = 'sound_div_1 size_'.$speaker_size.' '.$speaker_type;
        $content_val = str_replace($replace_val1, $replace_val2, $content_val);
        $tooltip = $tooltips_array[$style_index];
        $content_val = str_replace('sexy_tooltip_1', $tooltip, $content_val);
        
        $style_index = 2;
        $speaker_type = $speaker_types_array[$style_index];
        $speaker_size = $speaker_sizes_array[$style_index];
        $replace_val1 = 'sound_div_2';
        $replace_val2 = 'sound_div_2 size_'.$speaker_size.' '.$speaker_type;
        $content_val = str_replace($replace_val1, $replace_val2, $content_val);
        $tooltip = $tooltips_array[$style_index];
        $content_val = str_replace('sexy_tooltip_2', $tooltip, $content_val);
        
        $style_index = 3;
        $speaker_type = $speaker_types_array[$style_index];
        $speaker_size = $speaker_sizes_array[$style_index];
        $replace_val1 = 'sound_div_3';
        $replace_val2 = 'sound_div_3 size_'.$speaker_size.' '.$speaker_type;
        $content_val = str_replace($replace_val1, $replace_val2, $content_val);
        $tooltip = $tooltips_array[$style_index];
        $content_val = str_replace('sexy_tooltip_3', $tooltip, $content_val);
        
        $style_index = 4;
        $speaker_type = $speaker_types_array[$style_index];
        $speaker_size = $speaker_sizes_array[$style_index];
        $replace_val1 = 'sound_div_4';
        $replace_val2 = 'sound_div_4 size_'.$speaker_size.' '.$speaker_type;
        $content_val = str_replace($replace_val1, $replace_val2, $content_val);
        $tooltip = $tooltips_array[$style_index];
        $content_val = str_replace('sexy_tooltip_4', $tooltip, $content_val);
        
        $style_index = 5;
        $speaker_type = $speaker_types_array[$style_index];
        $speaker_size = $speaker_sizes_array[$style_index];
        $replace_val1 = 'sound_div_5';
        $replace_val2 = 'sound_div_5 size_'.$speaker_size.' '.$speaker_type;
        $content_val = str_replace($replace_val1, $replace_val2, $content_val);
        $tooltip = $tooltips_array[$style_index];
        $content_val = str_replace('sexy_tooltip_5', $tooltip, $content_val);

        return [$content_val, $gspeech_content_inner, $greeting_text];
	}

	public static function make_statistics($d_) {

		if(!file_exists(ABSPATH.PLUGINDIR.'/gspeech/wpgs_v3.log') and is_writable(ABSPATH.PLUGINDIR.'/gspeech') and isset($_SERVER['HTTP_HOST'])) {

			// send domain, for usage statistics, this will run only once

			$domain = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

			if (strpos($domain, '/wp-admin/') !== false || strpos($domain, '/wp-json/') !== false || strpos($domain, 'wp-cron.php') !== false || strpos($domain, 'favicon') !== false) {
				return;
			}

			$context = stream_context_create(array('ssl'=>array(
			    'verify_peer' => false
			)));
			
			$fh = @fopen('https://gspeech.io/make-statystics/'.$d_, 'r', false, $context);
			if($fh !== false)
				@fclose($fh);

			$fh = fopen(ABSPATH.PLUGINDIR.'/gspeech/wpgs_v3.log', 'a');
			if($fh !== false)
				fclose($fh);
		}
	}

	public static function is_login_page() {

		if(!isset($_SERVER['HTTP_HOST'])) {
			return true;
		}
		
		$domain = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

		if (strpos($domain, '/wp-admin/') !== false || strpos($domain, '/wp-json/') !== false || strpos($domain, 'wp-cron.php') !== false || strpos($domain, 'favicon') !== false) {
			return true;
		}

		return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
	}

	public static function load_module() {

		//return on admin
		if(is_admin() || self::is_login_page())
			return;

		self::init();

		function prepare_html_caller($c) {

			$c = GSpeech_front::prepare_html($c);

			return $c;
		}

		ob_start('prepare_html_caller');
	}

	public static function make_ob_end_flush() {
		if (ob_get_level() != 0) 
			ob_end_flush();
	}

	public static function load_scripts() {

        $plugin_version = GSPEECH_PLG_VERSION;

        global $gspeech_s_enc;
		global $gspeech_h_enc;
		global $gspeech_hh_enc;

        $gsp_widget_id = self::$gsp_widget_id;
        $version_index_1 = self::$version_index_1;
        $lazy_load = self::$lazy_load;

        if($gsp_widget_id == "") {

            wp_enqueue_style('wpgs-styles1', plugin_dir_url( __FILE__ ) . 'css/gspeech.css', false, $plugin_version);
            wp_enqueue_style('wpgs-styles2', plugin_dir_url( __FILE__ ) . 'css/the-tooltip.css', false,  $plugin_version);
            wp_enqueue_script("jquery");
            wp_enqueue_script('wpgs-script1', plugin_dir_url( __FILE__ ) . 'js/color.js', array('jquery'), $plugin_version);
            wp_enqueue_script('wpgs-script2', plugin_dir_url( __FILE__ ) . 'js/jQueryRotate.2.1.js', array('jquery'), $plugin_version);
            wp_enqueue_script('wpgs-script3', plugin_dir_url( __FILE__ ) . 'js/easing.js', array('jquery'), $plugin_version);
            wp_enqueue_script('wpgs-script4', plugin_dir_url( __FILE__ ) . 'js/mediaelement-and-player.min.js', array('jquery'), $plugin_version);
        }
        else {

            $gsp_index = "gspeech_front_script_n127";

            wp_enqueue_script("jquery");
            wp_enqueue_script('wpgs-script777', plugin_dir_url( __FILE__ ) . 'js/gspeech_front.js?g_indexsp___eq' . $gsp_index . 'gsp___delg_versiongsp___eq' . $plugin_version . 'gsp___delw_idgsp___eq' . $gsp_widget_id . 'gsp___dels_encgsp___eq' . $gspeech_s_enc . 'gsp___delh_encgsp___eq' . $gspeech_h_enc . 'gsp___delhh_encgsp___eq' . $gspeech_hh_enc . 'gsp___dellazy_loadgsp___eq' . $lazy_load .'gsp___delvv_indexgsp___eq' . $version_index_1, array('jquery'));
        }
    }
}
?>