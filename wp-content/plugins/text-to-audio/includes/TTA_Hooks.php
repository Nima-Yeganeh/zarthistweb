<?php
namespace TTA;

use function WPML\FP\apply;

/**
 * Fired during plugin activation
 *
 * @link       http://azizulhasan.com
 * @since      1.0.0
 *
 * @package    TTA
 * @subpackage TTA/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    TTA
 * @subpackage TTA/includes
 * @author     Azizul Hasan <azizulhasan.cr@gmail.com>
 */
class TTA_Hooks {

    private static $excludable_js_arr = [];
    private static $excludable_js_string = '';

    public function __construct() {
        // TODO it should work with new functionality 
        add_action('add_meta_boxes', array($this, 'add_custom_meta_box'));

        // Update hook
        // add_action('upgrader_process_complete', [$this, 'update_tts_default_data']);

        self::$excludable_js_arr = apply_filters('tts_excludable_js_arr', [
            'TextToSpeech.min.js',
            'text-to-audio-button.min.js',
            'text-to-audio-dashboard-ui.min.js',
            'tts_button_settings',
            'tts_button_settings_1',
            'tts_button_settings_2',
            'tts_button_settings_3',
            'tts_button_settings_4',
        ]);

        $strings = implode( ',', self::$excludable_js_arr);

        self::$excludable_js_string = apply_filters('tts_excludable_js_string', $strings);

        // Autoptimize Plugin
        add_filter( 'autoptimize_filter_js_exclude', [$this, 'autoptimize_filter_js_exclude_callback'] );

        // LiteSpeed Cache
        add_filter( 'litespeed_optimize_js_excludes', [$this, 'cache_exclude_js_text_to_speech'] );

        // WP Rocket
        add_filter('rocket_exclude_js', [$this, 'cache_exclude_js_text_to_speech']);
        add_filter('rocket_minify_excluded_external_js', [$this, 'cache_exclude_js_text_to_speech']);

        // WP Rocket inline script exclusions
        add_filter('rocket_defer_inline_exclusions', [$this, 'rocket_defer_inline_exclusions_callback'], 1000, 1);
        add_filter('rocket_exclude_defer_js', [$this, 'rocket_defer_inline_exclusions_callback'], 1000, 1);
        add_filter('rocket_excluded_inline_js_content', [$this, 'rocket_defer_inline_exclusions_callback'], 1000, 1);

        // W3 Total Cache
        add_filter('w3tc_minify_js_do_tag_minification', [$this, 'w3tc_minify_js_do_tag_minification_callback'], 10, 3);

        // WP Optimize
        add_filter('wp-optimize-minify-default-exclusions', [$this, 'cache_exclude_js_text_to_speech'], 10, 1);

        // Siteground SG Optimize
        add_filter('sgo_js_minify_exclude', [$this, 'sgo_js_minify_exclude_callback'], 10, 1);
        add_filter('sgo_javascript_combine_exclude', [$this, 'sgo_js_minify_exclude_callback'], 10, 1);
        add_filter('sgo_javascript_combine_excluded_external_paths', [$this, 'sgo_js_minify_exclude_callback'], 10, 1);

        add_filter( 'tta_before_clean_content', [$this, 'tta_before_clean_content_callback'], 10);

    }


    /**
     * Upgrader process complete.
     *
     * @see \WP_Upgrader::run() (wp-admin/includes/class-wp-upgrader.php)
     * @param \WP_Upgrader $upgrader_object
     * @param array $hook_extra
     * @see https://wordpress.stackexchange.com/questions/144870/wordpress-update-plugin-hook-action-since-3-9
     */
    public function update_settings_data(\WP_Upgrader $upgrader_object, $hook_extra){
        // get current plugin version. ( https://wordpress.stackexchange.com/a/18270/41315 )
        if(!function_exists('get_plugin_data')){
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        // https://developer.wordpress.org/reference/functions/get_plugin_data/
        $plugin_data = get_plugin_data(TEXT_TO_AUDIO_ROOT_FILE);
        $plugin_version = ($plugin_data['Version'] ?? 'unknown.version');
        unset($plugin_data);

        if (
            is_array($hook_extra) && 
            array_key_exists('action', $hook_extra) && 
            $hook_extra['action'] == 'update'
        ) {
            if (
                array_key_exists('type', $hook_extra) && 
                $hook_extra['type'] == 'plugin'
            ) {
                // if updated the plugins.
                $this_plugin = plugin_basename(TEXT_TO_AUDIO_ROOT_FILE);
                $this_plugin_updated = false;
                if (array_key_exists('plugins', $hook_extra)) {
                    // if bulk plugin update (in update page)
                    foreach ($hook_extra['plugins'] as $each_plugin) {
                        if ($each_plugin === $this_plugin) {
                            $this_plugin_updated = true;
                            break;
                        }
                    }// endforeach;
                    unset($each_plugin);
                } elseif (array_key_exists('plugin', $hook_extra)) {
                    // if normal plugin update or via auto update.
                    if ($this_plugin === $hook_extra['plugin']) {
                        $this_plugin_updated = true;
                    }
                }
                if ($this_plugin_updated === true) {
                    // if this plugin is just updated.
                    // do your task here.
                    // DON'T process anything from new version of code here, because it will work on old version of the plugin.
                    // please read again!! the code run here is not new (just updated) version but the version before that.

                    // 
                    
                    $settings =  (array) get_option( 'tta_settings_data' , [] );
                    $data = (object) array_merge( $settings,  array(
                        'tta__settings_enable_button_add'=> true,
                        "tta__settings_allow_listening_for_post_types" => ['post'],
                        "tta__settings_display_btn_icon" => '',
                    ));

                    update_option( 'tta_settings_data', $data );
                }
            } elseif (
                array_key_exists('type', $hook_extra) && 
                $hook_extra['type'] == 'theme'
            ) {
                // if updated the themes.
                // same as plugin, the bulk theme update will be set the name in $hook_extra['themes'] as 'theme1', 'theme2'.
                // normal update or via auto update will be set the name in $hook_extra['theme'] as 'theme1'.
            }
        }// endif; $hook_extra
    }

        /**
     * Upgrader process complete.
     *
     * @see \WP_Upgrader::run() (wp-admin/includes/class-wp-upgrader.php)
     * @param \WP_Upgrader $upgrader_object
     * @param array $hook_extra
     * @see https://wordpress.stackexchange.com/questions/144870/wordpress-update-plugin-hook-action-since-3-9
     */
    public function update_tts_default_data(){
        // $customize_settings =  (array) get_option( 'tta_customize_settings' , [] );
        // $customize_settings['buttonSettings'] = isset( $customize_settings['buttonSettings'] ) ? (array) $customize_settings['buttonSettings'] : [ 'id' => 1];
        // if( ! isset( $customize_settings['buttonSettings'] ) || !isset( $customize_settings['buttonSettings']['id'] ) ) {
        //     $data = (object) array_merge( $customize_settings,  array(
        //         'buttonSettings' => [
        //             'id' => 1,
        //         ]
        //     ));
        //     update_option( 'tta_customize_settings', $data );

        // }
    }

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
            // do something here
    }
    /**
     * Register MetaBox to add PDF Download Button
     */
    public function add_custom_meta_box() {
        $plugin_name = 'Text To Speech TTS';
        if(\is_pro_active()) {
            $plugin_name = 'Text To Speech Pro';
        }
        add_meta_box(
                'wps22-meta-box',
                $plugin_name,
                array(
                    $this,
                    'tta_meta_box',
                ),
                get_current_screen()->post_type,
                'advanced',
                'high',
                null
            );

    }

    /**
     * Add meta box for record, re-record, listen content with loud.
     */
    public function tta_meta_box() {

        // $listening = (array) get_option('tta_listening_settings');
        // $listening = json_encode($listening);
        $customize = (array) get_option('tta_customize_settings');
        // $button_text_arr =  apply_filters( 'tta__button_text_arr', get_option( 'tta__button_text_arr') );
        
        // Button style.
        if (isset($customize) && count($customize)) {
            $btn_style = 'background-color:#184c53;color:#fff;border:0;border-radius:3px;';
        }
        $short_code = '[tta_listen_btn]';
        if (isset($customize['tta_play_btn_shortcode']) && '' != $customize['tta_play_btn_shortcode']) {
            $short_code = $customize['tta_play_btn_shortcode'];
        }
        \do_action('tts_before_metabox_content');
        ?>
        <div class="tta_metabox">
            
            <input
                type="text"
                name="tta_play_btn_shortcode"
                id="tta_play_btn_shortcode"
                value="<?php echo esc_attr($short_code) ?>"
                title="Short code"
            />

            <!-- Copy Button -->
            <button type="button" style='<?php echo esc_attr($btn_style); ?>;cursor: copy;margin-top:10px;padding:6px;' onclick="copyshortcode()">
            <span class="dashicons dashicons-admin-page"></span>
            </button>

            <script>
                const unsecuredCopyToClipboard = (text) => {
                    const textArea = document.createElement("textarea");
                    textArea.value = text;
                    document.body.appendChild(textArea);
                    textArea.select();
                    textArea.setSelectionRange(0, 99999);
                    try {
                        document.execCommand('copy')
                        alert('Copied')
                    }
                    catch (err) {
                        console.error('Unable to copy to clipboard', err)
                    }

                    document.body.removeChild(textArea)
                    };
            /**
             * Copy short Code
             */
            function copyshortcode () {
                /* Get the text field */
                var copyText = document.getElementById("tta_play_btn_shortcode");

                /* Select the text field */
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                if (window.isSecureContext && navigator.clipboard) {
                    /* Copy the text inside the text field */
                    navigator.clipboard
                    .writeText(copyText.value)
                    .then(() => {
                        alert('Copied')
                    })
                    .catch((e) => {
                        alert("Something went wrong! " + e);
                        // toast('Something went wrong! ');
                    });
                } else {
                    unsecuredCopyToClipboard(copyText.value);
                }
            };
            </script>
        </div>
        <?php
        \do_action('tts_after_metabox_content');
    }


    	/**
	 * Autoptimize Plugin
	 *
	 * @param $excluded_js_files
	 *
	 * @return string
	 * @see: https://wordpress.org/plugins/autoptimize/
	 */
	public  function autoptimize_filter_js_exclude_callback( $excluded_js_files ) {

		$excluded_js_files .=   ', ' . self::$excludable_js_string;

		return $excluded_js_files;
	}

	/**
	 * @param $excluded_js_files
	 *
	 * @return mixed
	 *
	 * @see: https://wordpress.org/plugins/litespeed-cache/
	 * @see: https://wordpress.org/plugins/wp-optimize/
	 */
	public  function cache_exclude_js_text_to_speech( $excluded_js_files ) {
        $new_arr = [];
		if(is_array($excluded_js_files)) {
            $new_arr = array_merge( $excluded_js_files, self::$excludable_js_arr);
		}else{
            $new_arr = self::$excludable_js_arr;
        }

		return $new_arr;
	}

	/**
	 * WP Rocket inline script exclusions
	 *
	 * @param $excluded_patterns
	 *
	 * @return string[]
	 */
	public function rocket_defer_inline_exclusions_callback($excluded_patterns) {
        $new_arr = [];
		if(is_array($excluded_patterns) ) {
            $new_arr = array_merge( $excluded_patterns, self::$excludable_js_arr);
		}else{
            $new_arr = self::$excludable_js_arr;
        }

		return $new_arr;

	}


	/**
	 * @param $do_tag_minification
	 * @param $script_tag
	 * @param $file
	 *
	 * @return false|mixed
	 *
	 * @see: https://wordpress.org/plugins/w3-total-cache/
	 */
	public function w3tc_minify_js_do_tag_minification_callback($do_tag_minification, $script_tag, $file) {
        $basename = basename($file);
		if(in_array($basename, self::$excludable_js_arr)) {
			return false;
		}

		return $do_tag_minification;
	}

	/**
	 * @param $excluded_js
	 *
	 * @return array|mixed
	 * @see: https://wordpress.org/plugins/sg-cachepress/
	 */
	public  function sgo_js_minify_exclude_callback($excluded_js) {
		if(!is_array($excluded_js))
			return $excluded_js;

		global $wp_scripts;
		$registered_handles = array_keys($wp_scripts->registered);
		// foreach($registered_handles as $handle) {
        //     error_log(print_r($handle,1));
		// 	if(in_array($handle, self::$excludable_js_arr)) {
		// 		$excluded_js[] = $handle;
		// 	}

		// }

		return $excluded_js;
	}


    public function test() {

    }
	/**
	 * Add a delimiter after specific tags in the HTML string.
	 *
	 * @param string $htmlString The input HTML string.
	 * @param array $tags The array of tags to add delimiter after.
	 * @param string $delimiter The delimiter to add.
	 * @return string The modified HTML string.
	 */
    public function tta_before_clean_content_callback($htmlString) {
	    $tags = apply_filters( 'tts_delimiter_addable_tags', ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
	    $delimiter = \apply_filters('tts_sentence_delimiter', '.');
		// Iterate through each tag
		foreach ($tags as $tag) {
			// Create a regex pattern to match the closing tag
			$pattern = sprintf('/(<\/\s*%s\s*>)(?!\s*%s)/i', $tag, preg_quote($delimiter, '/'));

			// Replace each closing tag with the tag followed by the delimiter if it doesn't already have it
			$htmlString = preg_replace($pattern, '$1' . $delimiter, $htmlString);
		}

		return apply_filters( 'tta_pro_before_clean_content', $htmlString);
	}

}
new TTA_Hooks();