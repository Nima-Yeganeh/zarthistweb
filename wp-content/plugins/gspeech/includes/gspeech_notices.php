<?php 
// no direct access!
defined('ABSPATH') or die("No direct access");

class GSpeech_Notices {

    protected $prefix = 'gspeech';
    public $notice_spam = 0;
    public $notice_spam_max = 3;

    // Basic actions to run
    public function __construct() {

        // Runs the admin notice ignore function in case a dismiss button has been clicked
        add_action('admin_init', array($this, 'admin_notice_ignore'));

        // Runs the admin notice temp ignore function in case a temp dismiss link has been clicked
        add_action('admin_init', array($this, 'admin_notice_temp_ignore'));

        // add inline scripts and styles
        add_action('admin_notices', array($this, 'add_inline_scripts'));

        // Adding notices
        add_action('admin_notices', array($this, 'gs_admin_notices'));
    }

    // Checks to ensure notices aren't disabled and the user has the correct permissions.
    public function gs_admin_notice() {

        $gs_settings = get_option($this->prefix . '_admin_notice');

        if (!isset($gs_settings['disable_admin_notices']) || (isset($gs_settings['disable_admin_notices']) && $gs_settings['disable_admin_notices'] == 0)) {

            if (current_user_can('manage_options')) {
                return true;
            }
        }
        return false;
    }

    public function add_inline_scripts() {

        wp_enqueue_style($this->prefix . '-admin-inline', plugins_url(plugin_basename(dirname(__FILE__))) . '/css/gspeech-inline.css', array());

        wp_enqueue_script($this->prefix . '-admin-inline-js', plugins_url(plugin_basename(dirname(__FILE__))) . '/js/gspeech-inline.js', array('jquery'));

        $ajax_nonce = wp_create_nonce("wpgsp_ajax_nonce_value");
        wp_localize_script(
            $this->prefix . '-admin-inline-js',
            'wpgsp_ajax_obj',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => $ajax_nonce,
            )
        );
    }

    // Primary notice function that can be called from an outside function sending necessary variables
    public function admin_notice($admin_notices) {

        // Check options
        if (!$this->gs_admin_notice()) {
            return false;
        }

        foreach ($admin_notices as $slug => $admin_notice) {
            // Call for spam protection

            if ($this->anti_notice_spam()) {
                return false;
            }

            // Check for proper page to display on
            if (isset( $admin_notices[$slug]['pages']) and is_array( $admin_notices[$slug]['pages'])) {

                if (!$this->admin_notice_pages($admin_notices[$slug]['pages'])) {
                    return false;
                }

            }

            // Check for required fields
            if (!$this->required_fields($admin_notices[$slug])) {

                // Get the current date then set start date to either passed value or current date value and add interval
                $current_date = current_time("n/j/Y");
                $start = (isset($admin_notices[$slug]['start']) ? $admin_notices[$slug]['start'] : $current_date);
                $start = date("n/j/Y", strtotime($start));
                $end = ( isset( $admin_notices[ $slug ]['end'] ) ? $admin_notices[ $slug ]['end'] : $start );
                $end = date( "n/j/Y", strtotime( $end ) );
                $date_array = explode('/', $start);
                $interval = (isset($admin_notices[$slug]['int']) ? $admin_notices[$slug]['int'] : 0);
                $date_array[1] += $interval;
                $start = date("n/j/Y", mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]));

                // This is the main notices storage option
                $admin_notices_option = get_option($this->prefix . '_admin_notice', array());
                // Check if the message is already stored and if so just grab the key otherwise store the message and its associated date information
                if (!array_key_exists( $slug, $admin_notices_option)) {
                    $admin_notices_option[$slug]['start'] = $start;
                    $admin_notices_option[$slug]['int'] = $interval;
                    update_option($this->prefix . '_admin_notice', $admin_notices_option);
                }

                // Sanity check to ensure we have accurate information
                // New date information will not overwrite old date information
                $admin_display_check = (isset($admin_notices_option[$slug]['dismissed']) ? $admin_notices_option[$slug]['dismissed'] : 0);
                $admin_display_start = (isset($admin_notices_option[$slug]['start']) ? $admin_notices_option[$slug]['start'] : $start);
                $admin_display_interval = (isset($admin_notices_option[$slug]['int']) ? $admin_notices_option[$slug]['int'] : $interval);
                $admin_display_msg = (isset($admin_notices[$slug]['msg']) ? $admin_notices[$slug]['msg'] : '');
                $admin_display_title = (isset($admin_notices[$slug]['title']) ? $admin_notices[$slug]['title'] : '');
                $admin_display_link = (isset($admin_notices[$slug]['link']) ? $admin_notices[$slug]['link'] : '');
                $admin_display_dismissible= (isset($admin_notices[$slug]['dismissible']) ? $admin_notices[$slug]['dismissible'] : true);
                $output_css = false;

                // Ensure the notice hasn't been hidden and that the current date is after the start date
                if ($admin_display_check == 0 and strtotime($admin_display_start) <= strtotime($current_date)) {
                // if ($admin_display_check == 0) {
                    // Get remaining query string
                    $query_str = esc_url(add_query_arg($this->prefix . '_admin_notice_ignore', $slug));

                    // Admin notice display output
                    echo '<div class="update-nag gs-admin-notice">';
                    echo '<div class="gs-notice-logo"></div>';
                    echo ' <p class="gs-notice-title">';
                    echo $admin_display_title;
                    echo ' </p>';
                    echo ' <p class="gs-notice-body">';
                    echo $admin_display_msg;
                    echo ' </p>';
                    echo '<ul class="gs-notice-body gs-red">
                          ' . $admin_display_link . '
                        </ul>';
                    if($admin_display_dismissible)
                        echo '<a href="' . $query_str . '" class="dashicons dashicons-dismiss"></a>';
                    echo '</div>';

                    $this->notice_spam += 1;
                    $output_css = true;
                }

                if ($output_css) {
                    wp_enqueue_style($this->prefix . '-admin-notices', plugins_url(plugin_basename(dirname(__FILE__))) . '/css/gspeech-notices.css', array());
                }
            }

        }
    }

    // Spam protection check
    public function anti_notice_spam() {

        if ($this->notice_spam >= $this->notice_spam_max) {
            return true;
        }
        return false;
    }

    // Ignore function that gets ran at admin init to ensure any messages that were dismissed get marked
    public function admin_notice_ignore() {

        // If user clicks to ignore the notice, update the option to not show it again
        if (isset($_GET[$this->prefix . '_admin_notice_ignore'])) {

            $admin_notices_option = get_option($this->prefix . '_admin_notice', array());

            $key = $_GET[$this->prefix . '_admin_notice_ignore'];
            if(!preg_match('/^[a-z_0-9]+$/i', $key))
                return;

            $admin_notices_option[$key]['dismissed'] = 1;
            update_option($this->prefix . '_admin_notice', $admin_notices_option);
            $query_str = remove_query_arg($this->prefix . '_admin_notice_ignore');
            wp_redirect($query_str);
            exit;
        }
    }

    // Temp Ignore function that gets ran at admin init to ensure any messages that were temp dismissed get their start date changed
    public function admin_notice_temp_ignore() {

        // If user clicks to temp ignore the notice, update the option to change the start date - default interval of 14 days
        if (isset($_GET[$this->prefix . '_admin_notice_temp_ignore'])) {

            $admin_notices_option = get_option($this->prefix . '_admin_notice', array());
            $current_date = current_time("n/j/Y");
            $date_array   = explode('/', $current_date);
            $interval     = (isset($_GET['gs_int']) ? intval($_GET['gs_int']) : 14);
            $date_array[1] += $interval;
            $new_start = date("n/j/Y", mktime(0, 0, 0, $date_array[0], $date_array[1], $date_array[2]));

            $key = $_GET[$this->prefix . '_admin_notice_temp_ignore'];
            if(!preg_match('/^[a-z_0-9]+$/i', $key))
                return;

            $admin_notices_option[$key]['start'] = $new_start;
            $admin_notices_option[$key]['dismissed'] = 0;
            update_option($this->prefix . '_admin_notice', $admin_notices_option);
            $query_str = remove_query_arg(array($this->prefix . '_admin_notice_temp_ignore', 'gs_int'));
            wp_redirect( $query_str );
            exit;
        }
    }

    public function admin_notice_pages($pages) {

        foreach ($pages as $key => $page) {
            if (is_array($page)) {
                if (isset($_GET['page']) and $_GET['page'] == $page[0] and isset($_GET['tab']) and $_GET['tab'] == $page[1]) {
                    return true;
                }
            } else {
                if ($page == 'all') {
                    return true;
                }
                if (get_current_screen()->id === $page) {
                    return true;
                }

                if (isset($_GET['page']) and $_GET['page'] == $page) {
                    return true;
                }
            }
        }

        return false;
    }

    // Required fields check
    public function required_fields( $fields ) {

        if (!isset( $fields['msg']) or (isset($fields['msg']) and empty($fields['msg']))) {
            return true;
        }
        if (!isset( $fields['title']) or (isset($fields['title']) and empty($fields['title']))) {
            return true;
        }
        return false;
    }

    // Special parameters function that is to be used in any extension of this class
    public function special_parameters($admin_notices) {

        // Intentionally left blank
    }

    public function gs_admin_notices() {

        if(!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $compatible_plugins= array('GTranslate' => 'gtranslate/gtranslate.php');
        $compatible_plugins= array();

        foreach($compatible_plugins as $name => $plugin_file) {

            if(is_plugin_active($plugin_file)) {

                $not_name = 'compatible_plugin_'.strtolower(str_replace(' ', '', $name));

                $compatible_plugin_ignore = esc_url(add_query_arg(array($this->prefix . '_admin_notice_ignore' => $not_name)));

                $notices[$not_name] = array(
                    'title' => sprintf(__('%s and GSpeech', 'gspeech'), $name),
                    'msg' => sprintf(__('<b>%s</b> plugin is fully compatible with <b>GSpeech</b>.', 'gspeech'), $name),
                    'link' => '<li><span class="dashicons dashicons-smiley"></span><a href="' . $compatible_plugin_ignore . '">' . __('Nice to know', 'gspeech') . '</a></li>',
                    'dismissible' => true,
                    'int' => 0
                );
            }
        }

        $two_week_review_ignore = esc_url(add_query_arg(array($this->prefix . '_admin_notice_ignore' => 'two_week_review')));
        $two_week_review_temp = esc_url(add_query_arg(array($this->prefix . '_admin_notice_temp_ignore' => 'two_week_review', 'gs_int' => 6)));

        $notices['two_week_review'] = array(
            'title' => __('Please Leave a Review', 'gspeech'),
            'msg' => __("We hope you have enjoyed using GSpeech! Would you mind taking a few minutes to write a review on WordPress.org? <br>Just writing a simple <b>'thank you'</b> will make us happy!", 'gspeech'),
            'link' => '<li><span class="dashicons dashicons-external"></span><a href="https://wordpress.org/support/plugin/gspeech/reviews/?filter=5" target="_blank" rel="noreferrer">' . __('Sure! I would love to!', 'gspeech') . '</a></li>' .
                      '<li><span class="dashicons dashicons-smiley"></span><a href="' . $two_week_review_ignore . '">' . __('I have already left a review', 'gspeech') . '</a></li>' .
                      '<li><span class="dashicons dashicons-calendar-alt"></span><a href="' . $two_week_review_temp . '">' . __('Maybe later', 'gspeech') . '</a></li>' .
                      '<li><span class="dashicons dashicons-dismiss"></span><a href="' . $two_week_review_ignore . '">' . __('Never show again', 'gspeech') . '</a></li>',
            'dismissible' => true,
            'later_link' => $two_week_review_temp,
            'int' => 5
        );

        $data = get_option('GSpeech');
        GSpeech::load_defaults($data);

        $upgrade_tips_ignore = esc_url(add_query_arg(array($this->prefix . '_admin_notice_ignore' => 'upgrade_tips')));
        $upgrade_tips_temp = esc_url(add_query_arg(array($this->prefix . '_admin_notice_temp_ignore' => 'upgrade_tips', 'gs_int' => 7)));

        $notices['upgrade_tips'][] = array(
            'title' => __('Did you know?', 'gspeech'),
            'msg' => __('You can use the <b>Best AI voices, Download mp3 files</b> and much more by upgrading your GSpeech.', 'gspeech'),
            'link' => '<li><span class="dashicons dashicons-external"></span><a href="https://gspeech.io/#pricing" target="_blank" rel="noreferrer">' . __('Upgrade', 'gspeech') . '</a></li>' .
                      '<li><span class="dashicons dashicons-calendar-alt"></span><a href="' . $upgrade_tips_temp . '">' . __('Maybe later', ' ') . '</a></li>' .
                      '<li><span class="dashicons dashicons-dismiss"></span><a href="' . $upgrade_tips_ignore . '">' . __('Never show again', 'gspeech') . '</a></li>',
            'later_link' => $upgrade_tips_temp,
            'int' => 0
        );

        $notices['upgrade_tips'] = $notices['upgrade_tips'][0];

        $this->admin_notice($notices);
    }
}

if(is_admin()) {
    if(!defined('DOING_AJAX') or !DOING_AJAX)
        new GSpeech_Notices();
}

