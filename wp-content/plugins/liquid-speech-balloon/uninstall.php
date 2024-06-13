<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}
delete_option( 'liquid_speech_balloon' );
delete_option( 'liquid_speech_balloon_img' );
delete_option( 'liquid_speech_balloon_name' );
?>