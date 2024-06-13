<?php

// no direct access!
defined('ABSPATH') or die("No direct access");

/*
Plugin Name: GSpeech
Plugin URI: https://gspeech.io
Description: GSpeech is a universal text to speech audio solution. See <a href="https://gspeech.io/demos">GSpeech Demo</a>. Please <a href="https://gspeech.io/contact-us">Contact Us</a> if you have any questions.
Author: Text-To-Speech AI Audio Solutions
Author URI: https://gspeech.io
Version: 3.4.1
*/

$gspeech_session_started = session_status() === PHP_SESSION_NONE ? false : true;

if (!$gspeech_session_started)
    session_start();

$gspeech_plugin_version = '3.4.1';
$gspeech_new_db_version = 110;

define('GSPEECH_PLG_VERSION', $gspeech_plugin_version);
define('GSPEECH_NEW_DB_VER', $gspeech_new_db_version);

include('includes/gspeech_processor.php');
include('includes/gspeech_main.php');
include('includes/gspeech_widget.php');
include('includes/gspeech_notices.php');
include('includes/gspeech_frontend.php');
include('includes/gspeech_backend.php');

register_activation_hook(__FILE__, array('GSpeech', 'activate'));
register_uninstall_hook(__FILE__, array('GSpeech', 'uninstall'));

add_action('init', array('GSpeech', 'init'));
add_action('widgets_init', array('GSpeech', 'register'));
add_action('admin_menu', array('GSpeech_Admin', 'admin_menu'));
add_action('admin_init', array('GSpeech_Admin', 'admin_init'));
add_action('admin_init', array('GSpeech_Admin', 'admin_settings'));
add_action('wp_enqueue_scripts', array('GSpeech_Front', 'load_scripts'));
add_action('wp_loaded', array('GSpeech_Front', 'load_module'));
add_action('wp_ajax_wpgsp_apply_feedback', array('GSpeech_Admin', 'wpgsp_apply_feedback'));
add_action('wp_ajax_nopriv_wpgsp_apply_feedback', array('GSpeech_Admin', 'wpgsp_apply_feedback'));
add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('GSpeech_Admin', 'plugin_action_links'));

if (!$gspeech_session_started)
    session_write_close();

register_shutdown_function(array('GSpeech_Front', 'make_ob_end_flush'));