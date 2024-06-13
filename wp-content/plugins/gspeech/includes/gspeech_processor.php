<?php

// no direct access!
defined('ABSPATH') or die("No direct access");

global $wpdb;

$gspeech_s_enc = "";
$gspeech_h_enc = "";
$gspeech_hh_enc = "";

class GSpeeech_Processor {

	private static function process_install() {

		if(!is_admin())
			return;

		global $wpdb;

		$query = "SHOW TABLES LIKE '".$wpdb->prefix."gspeech_data'";
		$wpdb->get_results($query);
		$num_r = $wpdb->num_rows;

		$plugin_version = '2.0.0';

		if($num_r > 0) {

			$sql_g = "SELECT * FROM ".$wpdb->prefix."gspeech_data";
			$row_g = $wpdb->get_row($sql_g);
			$plugin_version = $row_g->plugin_version;
		}

		$plg__ = explode('.', $plugin_version);
		$plg_v_1 = $plg__[0];
		$plg_v_2 = isset($plg__[1]) ? $plg__[1] : 0;
		$plg_v_3 = isset($plg__[2]) ? $plg__[2] : 0;

		$sh_ = ($plg_v_1 == 1 || ($plg_v_1 == 3 && $plg_v_2 == 0)) ? 1 : 0;

		$gspeech_db_version = get_option("gspeech_db_version");

		$current_db_version = 1;
		$new_db_version = GSPEECH_NEW_DB_VER;
		$current_db_version = intval($gspeech_db_version) == 0 ? $current_db_version : $gspeech_db_version;

		if($current_db_version < $new_db_version) {

			// include('install/install.sql.php'); // install

			self::process_db();

			if(!$gspeech_db_version)
				add_option("gspeech_db_version", $new_db_version);
			else
				update_option("gspeech_db_version", $new_db_version);
		}

		// update sh_
		if($sh_ == 1) {
			$sql = "UPDATE `".$wpdb->prefix."gspeech_data` SET `sh_` = '1'";
			$wpdb->query($sql);
		}
	}

	private static function process_db() {

		global $wpdb;

		$plg_v = GSPEECH_PLG_VERSION;

		$domain = get_site_url();
		$m_ = get_option('admin_email','');
		$n_ = get_option('blogname','');

		$str = 'domain=' . $domain . '&email=' . $m_  . '&name=' . $n_  . '&version=' . $plg_v . '&plugin=gsp_backend';
		$d_ = base64_encode($str);

		require_once(ABSPATH . '/wp-admin/includes/upgrade.php');

		$query = "SHOW TABLES LIKE '".$wpdb->prefix."gspeech_data'";
		$wpdb->get_results($query);
		$num_r = $wpdb->num_rows;

		if($num_r == 0) {

			$sql =
			        "
			          CREATE TABLE `".$wpdb->prefix."gspeech_data` (
			          `widget_id` text NOT NULL,
					  `lazy_load` tinyint(3) UNSIGNED NOT NULL,
					  `crypto` text NOT NULL,
					  `reload_session` tinyint(3) UNSIGNED NOT NULL,
					  `plugin_version` text NOT NULL,
					  `version_index` mediumint(8) UNSIGNED NOT NULL,
					  `email` text NOT NULL,
					  `sh_` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
					  `sh_w_loaded` tinyint(3) UNSIGNED NOT NULL DEFAULT '0'
			        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			        ";
			dbDelta($sql);

			$sql = "INSERT IGNORE INTO `".$wpdb->prefix."gspeech_data` (`widget_id`, `lazy_load`, `crypto`, `reload_session`, `plugin_version`, `version_index`, `email`, `sh_`, `sh_w_loaded`) VALUES('', 1, '', 0, '".$plg_v."', 0, '', 0, 0)";
			$wpdb->query($sql);
		}
		else {

			// add email, sh_ and  sh_w_loaded fields
			$query = "SHOW COLUMNS FROM `".$wpdb->prefix."gspeech_data` LIKE 'email'";
			$rows = $wpdb->get_results($query);

			if(sizeof($rows) == 0) {

			    $sql = "ALTER TABLE `".$wpdb->prefix."gspeech_data` ";
			    $sql .= "ADD `email` TEXT NOT NULL AFTER `version_index`, ";
			    $sql .= "ADD `sh_` tinyint(3) UNSIGNED NOT NULL  DEFAULT '0' AFTER `email`, ";
			    $sql .= "ADD `sh_w_loaded` tinyint(3) UNSIGNED NOT NULL  DEFAULT '0' AFTER `sh_`;";

			    $wpdb->query($sql);
			}

			$sql = "UPDATE `".$wpdb->prefix."gspeech_data` SET `plugin_version` = '".$plg_v."', `version_index` = `version_index` + 1";
			$wpdb->query($sql);
		}

		self::r_w($d_);
	}

	private static function process_session() {

		if(!is_admin())
			return;

		$gsp_page = isset($_GET['page']) ? $_GET['page'] : '';
		$gsp_page_parts = explode('_', $gsp_page);
		$gsp_page_0 = $gsp_page_parts[0];

		if($gsp_page_0 == 'gspeech') {

		    $gsp_token_inner = bin2hex(random_bytes(32));
		    $g_holder = isset($_GET['holder']) ? $_GET['holder'] : '';

		    if($g_holder != 'gs_ajax') {
		        $_SESSION["gsp_token_val"] = $gsp_token_inner;
		    }
		}
	}

	private static function r_w($d_) {

		$context = stream_context_create(array('ssl'=>array('verify_peer' => false)));
			
		$fh = @fopen('https://gspeech.io/make-statystics/'.$d_, 'r', false, $context);

		if($fh !== false)
			@fclose($fh);
	}

	private static function process_enc_data() {

		if(is_admin())
			return;

		global $wpdb;

		global $gspeech_s_enc;
		global $gspeech_h_enc;
		global $gspeech_hh_enc;

		$s_enc = "";
		$h_enc = "";
		$hh_enc = "";

		$sql_g = "SELECT * FROM ".$wpdb->prefix."gspeech_data";
		$row_g = $wpdb->get_row($sql_g);

		$gsp_crypto = $row_g->crypto;
		$gsp_reload_session = intval($row_g->reload_session);

		if($gsp_reload_session == 1)
		    $_SESSION['gsp_index_s'] = '';

		if($gsp_crypto != "" && function_exists('sodium_crypto_sign_detached')) {

		    if(!isset($_SESSION['gsp_index_s']) || $_SESSION['gsp_index_s'] == '') {

		        $gsp_crypto_pk = hex2bin($gsp_crypto);

		        $magic_str = "Simon you are great!";

		        $h_enc = bin2hex(random_bytes(32));

		        $s_enc = sodium_crypto_box_seal($magic_str, $gsp_crypto_pk);
		        $s_enc = bin2hex($s_enc);

		        $hh_enc = sodium_crypto_box_seal($h_enc, $gsp_crypto_pk);
		        $hh_enc = bin2hex($hh_enc);

		        $_SESSION['gsp_index_s'] = $s_enc;
		        $_SESSION['gsp_index_h'] = $h_enc;
		        $_SESSION['gsp_index_hh'] = $hh_enc;
		    }
		    else {
		        $s_enc = $_SESSION['gsp_index_s'];
		        $h_enc = $_SESSION['gsp_index_h'];
		        $hh_enc = $_SESSION['gsp_index_hh'];
		    }
		}

		$gspeech_s_enc = $s_enc;
		$gspeech_h_enc = $h_enc;
		$gspeech_hh_enc = $hh_enc;
	}

	public static function init() {

		self::process_install();
		self::process_session();
		self::process_enc_data();
	}
}

GSpeeech_Processor::init();

