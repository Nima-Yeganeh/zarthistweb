<?php
/*
Plugin Name: LIQUID SPEECH BALLOON
Plugin URI: https://lqd.jp/wp/plugin.html
Description: Create a talk style design with a visual editor Gutenberg.
Author: LIQUID DESIGN Ltd.
Author URI: https://lqd.jp/wp/
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: liquid-speech-balloon
Version: 1.2.4
*/
/*  Copyright 2019 LIQUID DESIGN Ltd. (email : info@lqd.jp)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
*/

// ------------------------------------
// Plugin
// ------------------------------------
function liquid_speech_balloon_init() {
	load_plugin_textdomain( 'liquid-speech-balloon', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'liquid_speech_balloon_init' );

function liquid_speech_balloon_plugin_action_links( $links ) {
	$mylinks = '<a href="'.admin_url( 'options-general.php?page=liquid-speech-balloon' ).'">'.__( 'Settings', 'liquid-speech-balloon' ).'</a>';
    array_unshift( $links, $mylinks);
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'liquid_speech_balloon_plugin_action_links' );

// get_option
$liquid_speech_balloon = get_option( 'liquid_speech_balloon' );
$liquid_speech_balloon_img = get_option( 'liquid_speech_balloon_img' );
$liquid_speech_balloon_name = get_option( 'liquid_speech_balloon_name' );
$liquid_speech_balloon_note = get_option( 'liquid_speech_balloon_note' );

// enqueue
if( empty( $liquid_speech_balloon ) ){
    add_action( 'enqueue_block_editor_assets', 'liquid_speech_balloon_editor_assets' );
    add_action( 'enqueue_block_assets', function () {
        wp_enqueue_style( 'liquid-block-speech', plugins_url( 'css/block.css' , __FILE__ ), array() );
    });
    add_action( 'wp_head', 'liquid_speech_balloon_style' );
}

function liquid_speech_balloon_editor_assets() {
    global $liquid_speech_balloon_name, $liquid_speech_balloon_note;
    // enqueue
    wp_enqueue_script( 'liquid-block-speech', plugins_url( 'lib/block.js', __FILE__ ), array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ));
    wp_enqueue_style( 'liquid-block-speech', plugins_url( 'css/block.css', __FILE__ ), array() );
    // inline
    wp_add_inline_style( 'liquid-block-speech', liquid_speech_balloon_style_data() );
    // register
    wp_register_script( 'liquid-block-speech', plugins_url( 'lib/block.js', __FILE__ ), array( 'wp-i18n' ) );
    // translations
    if ( function_exists( 'wp_set_script_translations' ) ) {
        wp_set_script_translations( 'liquid-block-speech', 'liquid-speech-balloon', plugin_dir_path( __FILE__ ) . 'languages' );
    }
    // localize
    if( empty( $liquid_speech_balloon_name ) ){
        $liquid_speech_balloon_name = [];
    }
    if( empty( $liquid_speech_balloon_note ) ){
        $liquid_speech_balloon_note = [];
    }
    wp_localize_script( 'liquid-block-speech', 'liquid_speech_balloon_name', $liquid_speech_balloon_name );
    wp_localize_script( 'liquid-block-speech', 'liquid_speech_balloon_note', $liquid_speech_balloon_note );
}

// output style
function liquid_speech_balloon_style() {
    $data = liquid_speech_balloon_style_data();
    if( !empty( $data ) ){
        echo '<style type="text/css">'.$data."</style>\n";
    }
}

function liquid_speech_balloon_style_data() {
    global $liquid_speech_balloon_img, $liquid_speech_balloon_name;
    $data = '';
    if( !empty( $liquid_speech_balloon_img ) ){
        $i=0;
        foreach ( $liquid_speech_balloon_img as $key => $value ) {
            if( !empty( $value ) ){
                $data.= '.liquid-speech-balloon-'.$key.' .liquid-speech-balloon-avatar { background-image: url("'.esc_html($value).'"); } ';
            }
            $i++;
        }
    }
    if( !empty( $liquid_speech_balloon_name ) ){
        $i=0;
        foreach ( $liquid_speech_balloon_name as $key => $value ) {
            if( !empty( $value ) ){
                $data.= '.liquid-speech-balloon-'.$key.' .liquid-speech-balloon-avatar::after { content: "'.esc_html($value).'"; } ';
            }
            $i++;
        }
    }
    return $data;
}

// ------------------------------------
// Admin
// ------------------------------------
function liquid_speech_balloon_admin() {
    add_options_page(
        'LIQUID SPEECH BALLOON',
        'LIQUID SPEECH BALLOON',
        'edit_posts',
        'liquid-speech-balloon',
        'liquid_speech_balloon_admin_page'
    );
    register_setting(
        'liquid_speech_balloon_group',
        'liquid_speech_balloon'
    );
    register_setting(
        'liquid_speech_balloon_group',
        'liquid_speech_balloon_img'
    );
    register_setting(
        'liquid_speech_balloon_group',
        'liquid_speech_balloon_name'
    );
    register_setting(
        'liquid_speech_balloon_group',
        'liquid_speech_balloon_note'
    );
}
add_action( 'admin_menu', 'liquid_speech_balloon_admin' );

// admin_page
function liquid_speech_balloon_admin_page() {
    global $json_liquid_speech_balloon, $liquid_speech_balloon, $liquid_speech_balloon_img, $liquid_speech_balloon_name, $liquid_speech_balloon_note;

    // POST
    if( $_POST && check_admin_referer( 'liquid_speech_balloon_nonce_action', 'liquid_speech_balloon_nonce_field' ) ){
        if( isset( $_POST['liquid_speech_balloon'] ) ) {
            update_option( 'liquid_speech_balloon', htmlspecialchars( $_POST['liquid_speech_balloon'] ) );
            $liquid_speech_balloon = htmlspecialchars( $_POST['liquid_speech_balloon'] );
            $update_flag = 1;
        }
        if( isset( $_POST['liquid_speech_balloon_img'] ) ) {
            if( is_array( $_POST['liquid_speech_balloon_img'] ) ){
                $liquid_speech_balloon_img_post = array_map( 'htmlspecialchars', $_POST['liquid_speech_balloon_img'] );
            }else{
                $liquid_speech_balloon_img_post = htmlspecialchars( $_POST['liquid_speech_balloon_img'] );
            }
            update_option( 'liquid_speech_balloon_img', $liquid_speech_balloon_img_post );
            $liquid_speech_balloon_img = $liquid_speech_balloon_img_post;
            $update_flag = 1;
        }
        if( isset( $_POST['liquid_speech_balloon_name'] ) ) {
            if( is_array( $_POST['liquid_speech_balloon_name'] ) ){
                $liquid_speech_balloon_name_post = array_map( 'htmlspecialchars', $_POST['liquid_speech_balloon_name'] );
            }else{
                $liquid_speech_balloon_name_post = htmlspecialchars( $_POST['liquid_speech_balloon_name'] );
            }
            update_option( 'liquid_speech_balloon_name', $liquid_speech_balloon_name_post );
            $liquid_speech_balloon_name = $liquid_speech_balloon_name_post;
            $update_flag = 1;
        }
        if( isset( $_POST['liquid_speech_balloon_note'] ) ) {
            if( is_array( $_POST['liquid_speech_balloon_note'] ) ){
                $liquid_speech_balloon_note_post = array_map( 'htmlspecialchars', $_POST['liquid_speech_balloon_note'] );
            }else{
                $liquid_speech_balloon_note_post = htmlspecialchars( $_POST['liquid_speech_balloon_note'] );
            }
            update_option( 'liquid_speech_balloon_note', $liquid_speech_balloon_note_post );
            $liquid_speech_balloon_note = $liquid_speech_balloon_note_post;
            $update_flag = 1;
        }
    }
    if( empty( $liquid_speech_balloon ) ){
        $checked_on = 'checked="checked"';
        $checked_off = '';
    } else {
        $checked_on = '';
        $checked_off = 'checked="checked"';
    }
?>
<div class="wrap">
<h1>LIQUID SPEECH BALLOON</h1>

<?php if( !empty( $update_flag ) ) { ?>
<div class="notice notice-success is-dismissible"><p><strong><?php _e( 'Settings saved.', 'liquid-speech-balloon' ); ?></strong></p></div>
<?php } ?>

<div id="poststuff">

<!-- Recommend -->
<?php if( !empty($json_liquid_speech_balloon->recommend) ){ ?>
<div class="postbox">
<h2 style="border-bottom: 1px solid #eee;"><?php _e( 'Recommend', 'liquid-speech-balloon' ); ?></h2>
<div class="inside"><?php echo $json_liquid_speech_balloon->recommend; ?></div>
</div>
<?php } ?>

<!-- Settings -->
<div class="postbox">
<h2 style="border-bottom: 1px solid #eee;"><?php _e( 'Settings', 'liquid-speech-balloon' ); ?></h2>
<div class="inside">
<form method="post" name="liquid_speech_balloon_group" action="">
<?php
    settings_fields( 'liquid_speech_balloon_group' );
    do_settings_sections( 'default' );
    wp_nonce_field( 'liquid_speech_balloon_nonce_action', 'liquid_speech_balloon_nonce_field' );
?>
<table class="form-table">
    <tbody>
    <tr>
        <th><?php _e( 'Enable', 'liquid-speech-balloon' ); ?></th>
        <td scope="row" colspan="3">
            <label for="liquid_speech_balloon_on"><input type="radio" id="liquid_speech_balloon_on" name="liquid_speech_balloon" value="0" <?php echo $checked_on; ?>>On</label>
            <label for="liquid_speech_balloon_off"><input type="radio" id="liquid_speech_balloon_off" name="liquid_speech_balloon" value="1" <?php echo $checked_off; ?>>Off</label>
        </td>
    </tr>
    <tr>
        <th scope="row" colspan="4"><?php _e( 'Avatar', 'liquid-speech-balloon' ); ?> [<a href="https://lqd.jp/wp/plugin/speech-balloon.html?utm_source=admin&utm_medium=plugin&utm_campaign=balloon" target="_blank"><?php _e( 'How to use', 'liquid-speech-balloon' ); ?></a>]</th>
    </tr>
    <tr>
        <td style="padding:2px">
            <strong><label><?php _e( 'No.', 'liquid-speech-balloon' ); ?></label></strong>
        </td>
        <td style="padding:2px">
            <strong><label><?php _e( 'Name', 'liquid-speech-balloon' ); ?></label></strong>
        </td>
        <td style="padding:2px">
            <strong><label><a href="<?php echo admin_url('upload.php'); ?>" target="_blank"><?php _e( 'Image URL', 'liquid-speech-balloon' ); ?></a></label></strong>
        </td>
        <td style="padding:2px">
            <strong><label><?php _e( 'Note (For admin screen)', 'liquid-speech-balloon' ); ?></label></strong>
        </td>
    </tr>
<?php
    $count = !empty($liquid_speech_balloon_name) ? count($liquid_speech_balloon_name) : 11;
    $count = 10<$count ? $count : 11;
    for ($i=0; $i<$count; $i++) {
        $j = sprintf('%02d', $i);
        $name = !empty($liquid_speech_balloon_name[$j]) ? $liquid_speech_balloon_name[$j] : "";
        $img = !empty($liquid_speech_balloon_img[$j]) ? $liquid_speech_balloon_img[$j] : "";
        $note = !empty($liquid_speech_balloon_note[$j]) ? $liquid_speech_balloon_note[$j] : "";
        if( $i!=0 ) {
            echo '<tr><td style="padding:2px"><p>'.$j.'</p></td>';
        } else {
            echo '<tr><td style="padding:2px"><p>'.__( 'Default', 'liquid-speech-balloon' ).'</p></td>';
        }
        echo '<td style="padding:2px"><p><input class="widefat" type="text" name="liquid_speech_balloon_name['.$j.']" value="'.esc_html($name).'"></p></td>';
        echo '<td style="padding:2px"><p><input class="widefat" style="width:84%" type="url" name="liquid_speech_balloon_img['.$j.']" value="'.esc_html($img).'"> <img src="'.esc_html($img).'" alt="" style="width:22px; vertical-align:sub;"></p></td>';
        if( $i!=0 ) {
            echo '<td style="padding:2px"><p><input class="widefat" type="text" name="liquid_speech_balloon_note['.$j.']" value="'.esc_html($note).'"></p></td>';
        } else {
            echo '<td style="padding:2px"><p><input class="widefat" type="text" name="liquid_speech_balloon_note['.$j.']" value="'.esc_html($note).'"></p></td>';
        }
        echo '</tr>';
    }
    $j++;
    echo '<tr><td style="padding:2px"><p id="btn_add" class="button">'.__( 'Add', 'liquid-speech-balloon' ).'</p></td>';
    echo '<td style="padding:2px;visibility:hidden;" class="hides"><p><input class="widefat dises" type="text" name="liquid_speech_balloon_name['.$j.']" value="" disabled></p></td>';
    echo '<td style="padding:2px;visibility:hidden;" class="hides"><p><input class="widefat dises" style="width:84%" type="url" name="liquid_speech_balloon_img['.$j.']" value="" disabled></p></td>';
    echo '<td style="padding:2px;visibility:hidden;" class="hides"><p><input class="widefat dises" type="text" name="liquid_speech_balloon_note['.$j.']" value="" disabled></p></td>';
    echo '</tr>';
?>
    </tbody>
</table>
<input type="hidden" name="liquid_speech_balloon_name" value="" disabled class="deletions">
<input type="hidden" name="liquid_speech_balloon_img" value="" disabled class="deletions">
<input type="hidden" name="liquid_speech_balloon_note" value="" disabled class="deletions">
<p id="btn_del"><input type="checkbox" name="delete" value="" id="btn_del_check" onclick="btn_del();"> <?php _e( 'Delete All', 'liquid-speech-balloon' ); ?></p>
<?php submit_button(); ?>
<script>
// add
document.getElementById("btn_add").onclick = function onclick(event) {
    this.classList.remove("button");
    document.querySelectorAll('.dises').forEach(item => item.disabled = false);
    document.querySelectorAll('.hides').forEach(item => item.style.visibility ="visible");
}
// del
function btn_del(){
    check = document.getElementById("btn_del_check").checked;
    if (check == true) {
        document.getElementById("btn_del").style.color = "red";
        document.querySelectorAll('.deletions').forEach(item => item.disabled = false);
        window.alert("<?php esc_html_e( 'Delete All', 'liquid-speech-balloon' ); ?>");
    } else {
        document.getElementById("btn_del").style.color = "";
        document.querySelectorAll('.deletions').forEach(item => item.disabled = true);
    }
}
</script>
</form>

</div>
</div>

</div><!-- /poststuff -->
<hr><a href="https://lqd.jp/wp/" target="_blank">LIQUID PRESS</a>
</div><!-- /wrap -->
<?php }

// json
if ( is_admin() ) {
    $json_liquid_speech_balloon_error = "";
    $json_liquid_speech_balloon_url = "https://lqd.jp/wp/data/p/liquid-speech-balloon.json";
    $json_liquid_speech_balloon = wp_remote_get($json_liquid_speech_balloon_url);
    if ( is_wp_error( $json_liquid_speech_balloon ) ) {
        $json_liquid_speech_balloon_error = $json_liquid_speech_balloon->get_error_message().$json_liquid_speech_balloon_url;
    }else{
        $json_liquid_speech_balloon = json_decode($json_liquid_speech_balloon['body']);
    }
}

// notices
function liquid_speech_balloon_admin_notices() {
    global $json_liquid_speech_balloon, $json_liquid_speech_balloon_error;
    if ( isset( $_GET['liquid_admin_notices_dismissed'] ) ) {
        set_transient( 'liquid_admin_notices', 'dismissed', 60*60*24*30 );
    }
    if ( isset( $_GET['liquid_admin_offer_dismissed'] ) ) {
        set_transient( 'liquid_admin_offer', 'dismissed', 60*60*24*30 );
    }
    if( !empty($json_liquid_speech_balloon->news) && get_transient( 'liquid_admin_notices' ) != 'dismissed' ){
        echo '<div class="notice notice-info" style="position: relative;"><p>'.$json_liquid_speech_balloon->news.'</p><a href="?liquid_admin_notices_dismissed" style="position: absolute; right: 10px; top: 10px;">&times;</a></div>';
    }
    if( !empty($json_liquid_speech_balloon->offer) && get_transient( 'liquid_admin_offer' ) != 'dismissed' ){
        echo '<div class="notice notice-info" style="position: relative;"><p>'.$json_liquid_speech_balloon->offer.'</p><a href="?liquid_admin_offer_dismissed" style="position: absolute; right: 10px; top: 10px;">&times;</a></div>';
    }
    if(!empty($json_liquid_speech_balloon_error)) {
        echo '<script>console.log("'.$json_liquid_speech_balloon_error.'");</script>';
    }
}
add_action( 'admin_notices', 'liquid_speech_balloon_admin_notices' );

?>