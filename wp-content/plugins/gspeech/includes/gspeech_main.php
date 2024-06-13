<?php

// no direct access!
defined('ABSPATH') or die("No direct access");

class GSpeech extends WP_Widget {

    public static function init() {

        // gspeech init
    }

    public static function activate() {

        $data = get_option('wpgs_settings');
        self::load_defaults($data);

        add_option('wpgs_settings', $data);
    }

    public static function load_settings($data) {

        $gs_settings = array();
        foreach($data as $k => $v) {

            $gs_settings[$k] = $v;
        }

        return $gs_settings;
    }

    public static function load_defaults(& $data) {

        if(!is_array($data))
            $data = array();

        $gs_settings_defaults = array(
            'gspeech_title' => __('GSpeech - Text To Speech', 'gspeech'),
            'gspeech_v2x_title' => __('Click to listen highlighted text!', 'gspeech'),
            'use_old_plugin' => 0,
            'language' => 'en',
            'speak_any_text' => 1,
            'greeting_text' => '{gspeech style=1 language=en autoplay=1 speechtimeout=0 registered=2 hidespeaker=1}Welcome to SITENAME{/gspeech}{gspeech style=2 language=en autoplay=1 speechtimeout=0 registered=1 hidespeaker=1}Welcome REALNAME{/gspeech}',
            'bcp1' => '#ffffff',
            'cp1' =>  '#111111',
            'bca1' => '#545454',
            'ca1' =>  '#ffffff',
            'spop1' => 90,
            'spop1_' => 0.9,
            'spoa1' => 100,
            'spoa1_' => 1,
            'animation_time_1' => 400,
            'speaker_type_1' => 'speaker_16',
            'speaker_size_1' => 1,
            'tooltip_1' => 'black',
            'bcp2' => '#ffffff',
            'cp2' =>  '#3284c7',
            'bca2' => '#3284c7',
            'ca2' =>  '#ffffff',
            'spop2' => 80,
            'spop2_' => 0.8,
            'spoa2' => 100,
            'spoa2_' => 1,
            'animation_time_2' => 300,
            'speaker_type_2' => 'speaker_32',
            'speaker_size_2' => 1,
            'tooltip_2' => 'dark-midnight-blue',
            'bcp3' => '#ffffff',
            'cp3' =>  '#fc0000',
            'bca3' => '#ff3333',
            'ca3' =>  '#ffffff',
            'spop3' => 90,
            'spop3_' => 0.9,
            'spoa3' => 100,
            'spoa3_' => 1,
            'animation_time_3' => 400,
            'speaker_type_3' => 'speaker_33',
            'speaker_size_3' => 1,
            'tooltip_3' => 'sienna',
            'bcp4' => '#ffffff',
            'cp4' =>  '#0d7300',
            'bca4' => '#0f8901',
            'ca4' =>  '#ffffff',
            'spop4' => 90,
            'spop4_' => 0.9,
            'spoa4' => 100,
            'spoa4_' => 1,
            'animation_time_4' => 400,
            'speaker_type_4' => 'speaker_35',
            'speaker_size_4' => 1,
            'tooltip_4' => 'apple-green',
            'bcp5' => '#ffffff',
            'cp5' =>  '#ea7d00',
            'bca5' => '#ea7d00',
            'ca5' =>  '#ffffff',
            'spop5' => 70,
            'spop5_' => 0.7,
            'spoa5' => 100,
            'spoa5_' => 1,
            'animation_time_5' => 400,
            'speaker_type_5' => 'speaker_20',
            'speaker_size_5' => 1,
            'tooltip_5' => 'carrot-orange'
        );

        foreach ($gs_settings_defaults as $key => $val) {

            $data[$key] = isset($data[$key]) ? $data[$key] : $val;
        }
    }

    public static function get_widget_code($atts) {

        $unique_id = wp_rand(10000000, 77777777);

        $data = get_option('GSpeech');
        self::load_defaults($data);

        // if(isset($atts['wrapper_selector'])) {
        //     $data['wrapper_selector'] = $atts['wrapper_selector'];
        // }

        $gs_settings = self::load_settings($data);

        $widget_code = '<div class="gsp_full_player"></div>';
        
        return $widget_code;
    }

    public static function register() {

        register_widget('GSpeechWidget');
    }

    public static function uninstall() {

        delete_option('wpgs_settings');
		delete_option('gspeech_db_version');
        delete_option('gspeech_admin_notice');

		global $wpdb;

		require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

		$sql = "DROP TABLE IF EXISTS `".$wpdb->prefix."gspeech_data`";
		$wpdb->query($sql);
    }
}