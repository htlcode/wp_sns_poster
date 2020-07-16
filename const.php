<?php
global $wpdb;
define('WP_OTO_POSTER','wp-oto-poster');
define('WP_OTO_POSTER_TABLE1', $wpdb->prefix . 'oto_poster_schedules');
define('WP_OTO_POSTER_TABLE2', $wpdb->prefix . 'oto_poster_randoms');
$tmp_image_dir = dirname(__FILE__).'/tmp/';
define('WP_OTO_POSTER_TMP_IMAGE_DIR', $tmp_image_dir);
$tmp_image_dir_url = content_url().'/plugins/'.WP_OTO_POSTER.'/tmp/';
define('WP_OTO_POSTER_TMP_IMAGE_DIR_URL', $tmp_image_dir_url);
define('WP_OTO_POSTER_CHARSET', $wpdb->get_charset_collate());
define('WP_OTO_POSTER_FB_SDK_V', 'v3.0');