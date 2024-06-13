<?php 
// no direct access!
defined('ABSPATH') or die("No direct access");

class GSpeechWidget extends WP_Widget {

    function __construct() {

        parent::__construct('gspeech', esc_html__('GSpeech', 'gspeech'), array('description' => esc_html__('GSpeech - Text To Speech', 'gspeech')));
    }

    public function widget($args, $instance) {

        echo $args['before_widget'];

        if(!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        echo GSpeech::get_widget_code(array('position' => 'inline', 'wrapper_selector' => '.gtranslate_wrapper'));

        echo $args['after_widget'];
    }

    public function form($instance) {

        $title = !empty($instance['title']) ? $instance['title'] : '';
        ?>
        <p>
        <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:', 'gspeech'); ?></label>
        <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {

        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';

        return $instance;
    }
}