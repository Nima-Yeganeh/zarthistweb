<?php
namespace TTA_Admin;

use TTA\TTA_Helper;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://azizulhasan.com
 * @since      1.0.0
 *
 * @package    TTA
 * @subpackage TTA/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    TTA
 * @subpackage TTA/admin
 * @author     Azizul Hasan <azizulhasan.cr@gmail.com>
 */
class TTA_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Plugin's localize data.
     *
     * @since    1.3.14
     * @access   private
     * @var      string    $localize_data    Plugin's localize data.
     */
    public $localize_data;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $listening = json_encode(TTA_Helper::tts_get_settings('listening'));
        add_filter('script_loader_tag', [ $this, 'load_script_as_tag'] , 10, 3);
            global $is_iphone, $is_chrome,$is_safari,
        $is_NS4,$is_opera,$is_macIE,$is_winIE, $is_gecko, $is_lynx, $is_IE, $is_edge; 
        
        if( ! function_exists( 'is_plugin_active' ) ) {
            include ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $settings = TTA_Helper::tts_get_settings();

        $color = '#ffffff';
        if(isset($settings['customize']['color'])) {
	        $color = $settings['customize']['color'];
        }
        $this->localize_data  =  [
            'json_url' => esc_url_raw(rest_url()),
            'admin_url' => admin_url('/'),
            'classic_editor_is_active' => is_plugin_active('classic-editor/classic-editor.php'),
            'buttonTextArr' => get_option( 'tta__button_text_arr' ) ,
            'browser' => [
                'is_iphone' =>  $is_iphone, //(boolean): iPhone Safari
                'is_chrome'=> $is_chrome,// (boolean): Google Chrome
                'is_safari' =>  $is_safari,// (boolean): Safari
                'is_NS4' => $is_NS4,//(boolean): Netscape 4
                'is_opera' => $is_opera, //(boolean): Opera
                'is_macIE' => $is_macIE, //(boolean): Mac Internet Explorer
                'is_winIE' => $is_winIE, //(boolean): Windows Internet Explorer
                'is_gecko' => $is_gecko, //(boolean): FireFox
                'is_lynx'=> $is_lynx, //(boolean): Lynx
                'is_IE' => $is_IE, //(boolean): Internet Explorer
                'is_edge' => $is_edge, //(boolean): Microsoft Edge
            ],
            'ajax_url' => admin_url('admin-ajax.php'),
            'api_url' => esc_url_raw(rest_url()),
            'api_namespace' => 'tta',
            'api_version' => 'v1',
            'image_url' => WP_PLUGIN_URL . '/text-to-audio/admin/images',
            'plugin_url' => WP_PLUGIN_URL . '/text-to-audio',
            'nonce' => wp_create_nonce(TEXT_TO_AUDIO_NONCE),
            'plugin_name' => TEXT_TO_AUDIO_PLUGIN_NAME,
            'rest_nonce' => wp_create_nonce('wp_rest'),
            'post_types' => get_post_types(),
            'VERSION' => is_pro_active() ? get_option('TTA_PRO_VERSION' ) :  TEXT_TO_AUDIO_VERSION,
            'is_logged_in' => is_user_logged_in(),
            'is_admin' => current_user_can('administrator'),
            'is_dashboard' => is_admin(),
            'listeningSettings' => $listening,
            'is_pro_active' => is_pro_active(),
            'is_pro_license_active' => is_pro_active(),
            'is_admin_page' => \is_admin(),
            'current_post' => TTA_Helper::tts_post_type(),
            "player_id" => get_player_id(),
            'compatible' => TTA_Helper::get_compatible_plugins_data(),
            'is_folder_writable' => TTA_Helper::is_audio_folder_writable(),
            'gctts_is_authenticated' => get_player_id() == '4',
            'settings' => $settings,
            'post_type' => TTA_Helper::tts_post_type(),
            'player_customizations' => apply_filters('tts_player_customizations', [
	            '1' => [
                    'play' => "<svg height='20' width='20' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'><g id='SVGRepo_bgCarrier' stroke-width='1.5'></g><g id='SVGRepo_tracerCarrier' stroke-linecap='round' stroke-linejoin='round'></g><g id='SVGRepo_iconCarrier'> <path d='M22 12C22 14.7578 20.8836 17.2549 19.0782 19.064M2 12C2 9.235 3.12222 6.73208 4.93603 4.92188M19.1414 5.00003C19.987 5.86254 20.6775 6.87757 21.1679 8.00003M5 19.1415C4.08988 18.2493 3.34958 17.1845 2.83209 16' stroke='$color' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'></path> <path d='M16.2849 8.04397C17.3458 9.05877 18 10.4488 18 11.9822C18 13.5338 17.3302 14.9386 16.2469 15.9564M7.8 16C6.68918 14.9789 6 13.556 6 11.9822C6 10.4266 6.67333 9.01843 7.76162 8' stroke='$color' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'></path> <path d='M13.6563 10.4511C14.5521 11.1088 15 11.4376 15 12C15 12.5624 14.5521 12.8912 13.6563 13.5489C13.4091 13.7304 13.1638 13.9014 12.9384 14.0438C12.7407 14.1688 12.5168 14.298 12.2849 14.4249C11.3913 14.914 10.9444 15.1586 10.5437 14.8878C10.1429 14.617 10.1065 14.0502 10.0337 12.9166C10.0131 12.596 10 12.2817 10 12C10 11.7183 10.0131 11.404 10.0337 11.0834C10.1065 9.94977 10.1429 9.38296 10.5437 9.1122C10.9444 8.84144 11.3913 9.08599 12.2849 9.57509C12.5168 9.70198 12.7407 9.83123 12.9384 9.95619C13.1638 10.0986 13.4091 10.2696 13.6563 10.4511Z' stroke='$color' stroke-width='1.5'></path> </g></svg>",
                    'pause' => "<svg width='20' viewBox='0 0 24 24' fill='none' xmlns='http://www.w3.org/2000/svg'><g id='SVGRepo_bgCarrier' stroke-width='1.5'></g><g id='SVGRepo_tracerCarrier' stroke-linecap='round' stroke-linejoin='round'></g><g id='SVGRepo_iconCarrier'> <path opacity='0.1' d='M3 12C3 4.5885 4.5885 3 12 3C19.4115 3 21 4.5885 21 12C21 19.4115 19.4115 21 12 21C4.5885 21 3 19.4115 3 12Z' fill='$color'></path> <path d='M14 9L14 15' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'></path> <path d='M10 9L10 15' stroke='$color' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'></path> <path d='M3 12C3 4.5885 4.5885 3 12 3C19.4115 3 21 4.5885 21 12C21 19.4115 19.4115 21 12 21C4.5885 21 3 19.4115 3 12Z' stroke='$color' stroke-width='2'></path> </g></svg>",
                    'replay' => "<svg width='20px' height='20px' viewBox='0 0 24.00 24.00' fill='none' xmlns='http://www.w3.org/2000/svg' stroke='$color' stroke-width='1'><g id='SVGRepo_bgCarrier' stroke-width='0'></g><g id='SVGRepo_tracerCarrier' stroke-linecap='round' stroke-linejoin='round'></g><g id='SVGRepo_iconCarrier'> <path d='M12 20.75C10.078 20.7474 8.23546 19.9827 6.8764 18.6236C5.51733 17.2645 4.75265 15.422 4.75 13.5C4.75 13.3011 4.82902 13.1103 4.96967 12.9697C5.11032 12.829 5.30109 12.75 5.5 12.75C5.69891 12.75 5.88968 12.829 6.03033 12.9697C6.17098 13.1103 6.25 13.3011 6.25 13.5C6.25 14.6372 6.58723 15.7489 7.21905 16.6945C7.85087 17.6401 8.74889 18.3771 9.79957 18.8123C10.8502 19.2475 12.0064 19.3614 13.1218 19.1395C14.2372 18.9177 15.2617 18.37 16.0659 17.5659C16.87 16.7617 17.4177 15.7372 17.6395 14.6218C17.8614 13.5064 17.7475 12.3502 17.3123 11.2996C16.8771 10.2489 16.1401 9.35087 15.1945 8.71905C14.2489 8.08723 13.1372 7.75 12 7.75H9.5C9.30109 7.75 9.11032 7.67098 8.96967 7.53033C8.82902 7.38968 8.75 7.19891 8.75 7C8.75 6.80109 8.82902 6.61032 8.96967 6.46967C9.11032 6.32902 9.30109 6.25 9.5 6.25H12C13.9228 6.25 15.7669 7.01384 17.1265 8.37348C18.4862 9.73311 19.25 11.5772 19.25 13.5C19.25 15.4228 18.4862 17.2669 17.1265 18.6265C15.7669 19.9862 13.9228 20.75 12 20.75Z' fill='$color'></path> <path d='M12 10.75C11.9015 10.7505 11.8038 10.7313 11.7128 10.6935C11.6218 10.6557 11.5392 10.6001 11.47 10.53L8.47 7.53003C8.32955 7.38941 8.25066 7.19878 8.25066 7.00003C8.25066 6.80128 8.32955 6.61066 8.47 6.47003L11.47 3.47003C11.5387 3.39634 11.6215 3.33724 11.7135 3.29625C11.8055 3.25526 11.9048 3.23322 12.0055 3.23144C12.1062 3.22966 12.2062 3.24819 12.2996 3.28591C12.393 3.32363 12.4778 3.37977 12.549 3.45099C12.6203 3.52221 12.6764 3.60705 12.7141 3.70043C12.7518 3.79382 12.7704 3.89385 12.7686 3.99455C12.7668 4.09526 12.7448 4.19457 12.7038 4.28657C12.6628 4.37857 12.6037 4.46137 12.53 4.53003L10.06 7.00003L12.53 9.47003C12.6704 9.61066 12.7493 9.80128 12.7493 10C12.7493 10.1988 12.6704 10.3894 12.53 10.53C12.4608 10.6001 12.3782 10.6557 12.2872 10.6935C12.1962 10.7313 12.0985 10.7505 12 10.75Z' fill='$color'></path> </g></svg>",
                    'resume' => "<svg width='20px' height='20px' viewBox='0 0 24.00 24.00' fill='none' xmlns='http://www.w3.org/2000/svg' stroke='$color' stroke-width='1'><g id='SVGRepo_bgCarrier' stroke-width='0'></g><g id='SVGRepo_tracerCarrier' stroke-linecap='round' stroke-linejoin='round'></g><g id='SVGRepo_iconCarrier'> <path d='M12 20.75C10.078 20.7474 8.23546 19.9827 6.8764 18.6236C5.51733 17.2645 4.75265 15.422 4.75 13.5C4.75 13.3011 4.82902 13.1103 4.96967 12.9697C5.11032 12.829 5.30109 12.75 5.5 12.75C5.69891 12.75 5.88968 12.829 6.03033 12.9697C6.17098 13.1103 6.25 13.3011 6.25 13.5C6.25 14.6372 6.58723 15.7489 7.21905 16.6945C7.85087 17.6401 8.74889 18.3771 9.79957 18.8123C10.8502 19.2475 12.0064 19.3614 13.1218 19.1395C14.2372 18.9177 15.2617 18.37 16.0659 17.5659C16.87 16.7617 17.4177 15.7372 17.6395 14.6218C17.8614 13.5064 17.7475 12.3502 17.3123 11.2996C16.8771 10.2489 16.1401 9.35087 15.1945 8.71905C14.2489 8.08723 13.1372 7.75 12 7.75H9.5C9.30109 7.75 9.11032 7.67098 8.96967 7.53033C8.82902 7.38968 8.75 7.19891 8.75 7C8.75 6.80109 8.82902 6.61032 8.96967 6.46967C9.11032 6.32902 9.30109 6.25 9.5 6.25H12C13.9228 6.25 15.7669 7.01384 17.1265 8.37348C18.4862 9.73311 19.25 11.5772 19.25 13.5C19.25 15.4228 18.4862 17.2669 17.1265 18.6265C15.7669 19.9862 13.9228 20.75 12 20.75Z' fill='$color'></path> <path d='M12 10.75C11.9015 10.7505 11.8038 10.7313 11.7128 10.6935C11.6218 10.6557 11.5392 10.6001 11.47 10.53L8.47 7.53003C8.32955 7.38941 8.25066 7.19878 8.25066 7.00003C8.25066 6.80128 8.32955 6.61066 8.47 6.47003L11.47 3.47003C11.5387 3.39634 11.6215 3.33724 11.7135 3.29625C11.8055 3.25526 11.9048 3.23322 12.0055 3.23144C12.1062 3.22966 12.2062 3.24819 12.2996 3.28591C12.393 3.32363 12.4778 3.37977 12.549 3.45099C12.6203 3.52221 12.6764 3.60705 12.7141 3.70043C12.7518 3.79382 12.7704 3.89385 12.7686 3.99455C12.7668 4.09526 12.7448 4.19457 12.7038 4.28657C12.6628 4.37857 12.6037 4.46137 12.53 4.53003L10.06 7.00003L12.53 9.47003C12.6704 9.61066 12.7493 9.80128 12.7493 10C12.7493 10.1988 12.6704 10.3894 12.53 10.53C12.4608 10.6001 12.3782 10.6557 12.2872 10.6935C12.1962 10.7313 12.0985 10.7505 12 10.75Z' fill='$color'></path> </g></svg>",
	            ]
            ]),
            'categories' => TTA_Helper::get_all_categories(),
            'tags' => TTA_Helper::get_all_tags(),
        ]; 
    }

    public function load_script_as_tag($tag, $handle, $src) {
        if(!in_array($handle, ['text-to-audio-button', 'TextToSpeech'])) {
            return $tag;
        }

        $tag = '<script  type="module" src="' . esc_url($src) . '"  ></script>';

        return $tag;

    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        /* Dashicons */
        wp_enqueue_style('dashicons');
        wp_enqueue_style('text-to-audio-dashboard', plugin_dir_url(__FILE__) . 'css/text-to-audio-dashboard.css', [] , $this->version, 'all' );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        /**
         * Looad wp-speeh script
         */

        if( ! function_exists( 'is_plugin_active' ) ) {
            include ABSPATH . 'wp-admin/includes/plugin.php';
        }

        do_action('tta_enqueue_pro_dashboard_scripts');


        if (is_admin() &&  isset($_REQUEST['page']) && ('text-to-audio' == $_REQUEST['page'])) {
            /* Load react js */
                wp_enqueue_script('tts-font-awesome', plugin_dir_url(__FILE__) . 'js/build/font-awesome.min.js', array(), $this->version, true);
                wp_enqueue_style('tts-bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap.css', [] , $this->version, 'all' );
                wp_enqueue_script('TextToSpeech', plugin_dir_url(__FILE__) . 'js/build/TextToSpeech.min.js', array('wp-hooks',), $this->version, true);
                wp_localize_script('TextToSpeech', 'ttsObj', $this->localize_data);
                wp_enqueue_script('text-to-audio-dashboard-ui', plugin_dir_url(__FILE__) . 'js/build/text-to-audio-dashboard-ui.min.js', array('TextToSpeech'), $this->version, true);
                wp_localize_script('text-to-audio-dashboard-ui', 'tta_obj', $this->localize_data);
                wp_enqueue_style('dashicons');


                // Player 2
                wp_enqueue_style('text-to-audio-pro-demo', plugin_dir_url(__FILE__) . 'demos/player2/text-to-audio-pro-demo.css', [] , $this->version, 'all' );
                wp_enqueue_script('TextToSpeechProDemo', plugin_dir_url(__FILE__) .'demos/player2/js/TextToSpeechProDemo.min.js', array('wp-hooks', 'TextToSpeech'), $this->version, true);
                wp_localize_script('TextToSpeechProDemo', 'ttsObjPro', $this->localize_data);

                // Player 3
                wp_enqueue_style('tts-pro-demo-plyr', plugin_dir_url(__FILE__) . 'demos/player3/css/plyr-demo.min.css', [] , $this->version, 'all');
                wp_enqueue_script('text-to-audio-plyr-demo-lib', plugin_dir_url(__FILE__) .'demos/player3/js/build/plyr-demo.lib.min.js', array('wp-hooks'), $this->version, true);
                wp_enqueue_script('text-to-audio-demo-plyr', plugin_dir_url(__FILE__) .'demos/player3/js/build/plyr-demo.min.js', array(), $this->version, true);
                wp_localize_script('text-to-audio-demo-plyr', 'ttsObj', $this->localize_data);
        
            }

            if (is_admin() && isset($GLOBALS['pagenow']) && $GLOBALS['pagenow'] === 'plugins.php') {
                $object = ob_start();
                ?>
                    <script>
                        // let isProActive2 = "<?php echo $this->localize_data['is_pro_active']?>";
                        window.document.addEventListener('DOMContentLoaded', function () {
                            /**
                             * If free version then remove the opt-in link from plugin link.
                             * Also remove the deactivation modal by freemius. So that 
                             * AtlasAiDev tracking software works properly.
                             */
                            // if(isProActive && document.querySelector('.opt-in-or-opt-out.text-to-audio')) {
                            //     document.querySelector('.opt-in-or-opt-out.text-to-audio').style.display = 'none';
                            // }
                            
                            if(document.querySelector('[data-plugin="text-to-audio/text-to-audio.php"]')) {
                                 var moduleIdElement = document.querySelector('i.fs-module-id[data-module-id="13388"]');
                                if (moduleIdElement) {
                                    moduleIdElement.parentNode.removeChild(moduleIdElement);
                                }
                            }
                        })
                    </script>
                    
                <?php
                $object = ob_get_contents();
                echo  $object;
            }

    }

    public function engueue_block_scripts() {
        
        wp_enqueue_script('tta-blocks', plugin_dir_url(dirname(__FILE__)) . 'build/blocks.js', array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'), true, true);
        wp_localize_script('tta-blocks', 'ttaBlocks', $this->localize_data);

        register_block_type('tta/customize-button', [
            'render_callback' => [$this, 'render_button'],
        ]);

    }

    /**
     * @param $customize button.
     *
     * @return string
     */
    public function render_button($customize) {

        return tta_get_button_content($customize, true);
    }

    /**
     * Enqueue wp speech file
     *
     */
    public function enqueue_TTA() {
        $player_id = get_player_id();
        
        if( $player_id > 1){
            wp_enqueue_script('TextToSpeech', plugin_dir_url(__FILE__) . 'js/build/TextToSpeech.min.js', array('wp-hooks',), $this->version, true);
            wp_localize_script('TextToSpeech', 'ttsObj', $this->localize_data);
        }else if($player_id == 1){
            wp_enqueue_script('text-to-audio-button', plugin_dir_url(__FILE__) . 'js/build/text-to-audio-button.min.js', array('wp-hooks', 'wp-shortcode'), $this->version, true);
            wp_localize_script('text-to-audio-button', 'ttsObj', $this->localize_data );
            wp_enqueue_style('dashicons');
        }
    }

    /**
     * Add Menu and Submenu page
     */

    public function TTA_menu() {
        add_menu_page(
            __('Text To Speech Ninja', TEXT_TO_AUDIO_TEXT_DOMAIN),
            __('Text To Speech', TEXT_TO_AUDIO_TEXT_DOMAIN),
            'manage_options',
            TEXT_TO_AUDIO_TEXT_DOMAIN,
            array($this, "TTA_settings"),
            'dashicons-controls-volumeon',
            20
        );
    }

    public function TTA_settings() {
        echo "<div class='wpwrap'><div id='tts_dashboard_ui'></div></div>";
    }

}
