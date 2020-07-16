<?php
/*
Plugin Name: WP Oto Poster
Description: Social network scheduler post
Author: CAISSON Frederic
Version: 1.4
*/
define('WP_OTO_POSTER_FILE',__FILE__);
define('WP_OTO_POSTER_VERSION','1.4');

require_once(dirname(__FILE__).'/const.php');
require_once(dirname(__FILE__).'/app/base/Db.php');
require_once(dirname(__FILE__).'/app/base/Controller.php');
require_once(dirname(__FILE__).'/app/base/View.php');
require_once(dirname(__FILE__).'/app/lib/DateFactory.php');
require_once(dirname(__FILE__).'/app/controller/CoreController.php');

function initialize_wp_oto_poster() {
    $core = WpOtoPoster\CoreController::get_instance();
    $core->add_hooks();
}

initialize_wp_oto_poster();