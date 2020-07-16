<?php
error_reporting(E_ERROR | E_PARSE | E_NOTICE);

//Load Composer's vendor (Facebook,Twitter)
$vendor_fb = dirname(dirname(__FILE__)).'/vendor/autoload.php';
require_once($vendor_fb);
use Abraham\TwitterOAuth\TwitterOAuth;

//Load Pinterest vendor
$vendor_pint = dirname(dirname(__FILE__)).'/vendor/pinterest/Pinterest.php';
require_once($vendor_pint);

//Load Buffer vendor
$vendor_buffer = dirname(dirname(__FILE__)).'/vendor/buffer/Buffer.php';
require_once($vendor_buffer);

//Load my Db class
$lib_db = dirname(dirname(__FILE__)).'/app/base/Db.php';
require_once($lib_db);

//Load my Db class
$lib_date = dirname(dirname(__FILE__)).'/app/lib/DateFactory.php';
require_once($lib_date);

//Load wp-config.php
$wp_config = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/wp-config.php';
require_once($wp_config);

//Load Const
$lib_const = dirname(dirname(__FILE__)).'/const.php';
require_once($lib_const);

//Load cron functions.php
require_once('functions.php');

$ths = (new WpOtoPoster\DateFactory())->getThs();

$fb = null;
$twitter = null;
$buffer = null;
$pinterest = null;
$buffer_profile_ids = array();

$data = get_option(WP_OTO_POSTER);

$timezone = 'UTC';
$my_secret_key = '';
$facebook_app_id = '';
$facebook_app_secret = '';
$facebook_app_token = '';
$facebook_page_id = '';

$twitter_consumer_api_key = '';
$twitter_consumer_api_secret_key = '';
$twitter_access_token = '';
$twitter_token_secret = '';

$buffer_client_id = '';
$buffer_client_secret = '';
$buffer_redirect_uri = '';
$buffer_access_token = '';

$pinterest_user = '';
$pinterest_password = '';
$pinterest_board = '';

if(!empty($data)){
	$data = json_decode($data,true);

	if(isset($data['timezone'])){
		$timezone = $data['timezone'];
	}

	if(isset($data['my_secret_key'])){
		$my_secret_key = $data['my_secret_key'];
	}

	if(isset($data['facebook_app_id'])){
		$facebook_app_id = $data['facebook_app_id'];
	}

	if(isset($data['facebook_app_secret'])){
		$facebook_app_secret = $data['facebook_app_secret'];
	}

	if(isset($data['facebook_app_token'])){
		$facebook_app_token = $data['facebook_app_token'];
	}

	if(isset($data['facebook_page_id'])){
		$facebook_page_id = $data['facebook_page_id'];
	}

	if(isset($data['twitter_consumer_api_key'])){
		$twitter_consumer_api_key = $data['twitter_consumer_api_key'];
	}

	if(isset($data['twitter_consumer_api_secret_key'])){
		$twitter_consumer_api_secret_key = $data['twitter_consumer_api_secret_key'];
	}

	if(isset($data['twitter_access_token'])){
		$twitter_access_token = $data['twitter_access_token'];
	}

	if(isset($data['twitter_token_secret'])){
		$twitter_token_secret = $data['twitter_token_secret'];
	}

	if(isset($data['buffer_client_id'])){
		$buffer_client_id = $data['buffer_client_id'];
	}

	if(isset($data['buffer_client_secret'])){
		$buffer_client_secret = $data['buffer_client_secret'];
	}

	if(isset($data['buffer_redirect_uri'])){
		$buffer_redirect_uri = $data['buffer_redirect_uri'];
	}

	if(isset($data['buffer_access_token'])){
		$buffer_access_token = $data['buffer_access_token'];
	}

	if(isset($data['pinterest_user'])){
		$pinterest_user = $data['pinterest_user'];
	}

	if(isset($data['pinterest_password'])){
		$pinterest_password = $data['pinterest_password'];
	}

	if(isset($data['pinterest_board'])){
		$pinterest_board = $data['pinterest_board'];
	}
}

//Check secret Key
if(empty($my_secret_key) || empty($_GET['secret_key'])){
	echo 'Error (00): Missing secret key'.PHP_EOL;
	exit;
} else {
	if($my_secret_key != $_GET['secret_key']){
		echo 'Error (01): Invalid secret key'.PHP_EOL;
		exit;
	}
}

$now = new DateTime("now", new DateTimeZone($timezone) );
$d = $now->format('d');
$m = $now->format('m');
$Y = $now->format('Y');
$H = $now->format('H');
$i = $now->format('i');
$l = $now->format('l');


if($i < 30){
	$i = '00';
} else {
	$i = '30';
}
$nowDate = "$Y/$m/$d";
$nowTime = "$H:$i";

$db = new WpOtoPoster\Db();
$table = WP_OTO_POSTER_TABLE1;
$sql = "SELECT * FROM $table WHERE is_facebook = 1 OR is_twitter = 1 OR is_instagram = 1 OR is_pinterest = 1"; 
$records = $db->select_query($sql);

if (!empty($records)) {

	//Check social settings
	foreach($records as $record){
		if(intval($record['is_facebook'])==1){
			if(empty($facebook_app_id) || empty($facebook_app_secret) || empty($facebook_app_token) || empty($facebook_page_id)){
				echo 'Error (02): Missing facebook settings'.PHP_EOL;
				exit;
			} else {
				if(empty($fb)){
					$fb = new Facebook\Facebook([
					  'app_id' => $facebook_app_id,
					  'app_secret' => $facebook_app_secret,
					  'default_graph_version' => WP_OTO_POSTER_FB_SDK_V,
					]);
				}
			}
		}
		if(intval($record['is_twitter'])==1){
			
			if(empty($twitter_consumer_api_key) || empty($twitter_consumer_api_secret_key) || empty($twitter_access_token) || empty($twitter_token_secret)){
				echo 'Error (03): Missing twitter settings'.PHP_EOL;
				exit;
			} else {
				if(empty($twitter)){
					$twitter = new TwitterOAuth($twitter_consumer_api_key, $twitter_consumer_api_secret_key, $twitter_access_token, $twitter_token_secret);
				}
			}
		}
		if(intval($record['is_instagram'])==1){
			
			if(empty($buffer_client_id) || empty($buffer_client_secret) || empty($buffer_redirect_uri) || empty($buffer_access_token)){
				echo 'Error (04): Missing instagram settings'.PHP_EOL;
				exit;
			} else {
				if(empty($buffer)){
					$buffer = new Buffer($buffer_client_id,$buffer_client_secret,$buffer_redirect_uri,$buffer_access_token);
					$profiles = $buffer->go('/profiles');

					foreach($profiles as $profile){
						if($profile->service == 'instagram'){
							$buffer_profile_ids[] = $profile->_id;
						}
					}
					if(empty($buffer_profile_ids)){
						echo 'Error (05): Missing instagram account in buffer'.PHP_EOL;
						exit;
					}
				}
			}
		}

		if(intval($record['is_pinterest'])==1){
			if(empty($pinterest_user) || empty($pinterest_password) || empty($pinterest_board)){
				echo 'Error (06): Missing pinterest settings'.PHP_EOL;
				exit;
			} else {
				if(empty($pinterest)){
					$pinterest = new Pinterest();
					if($pinterest->login($pinterest_user, $pinterest_password) != 0){
						$pinterest = null;
					}
				}
			}
		}
	}

	$tmpImages = array();

	//Loop each schedule
	foreach($records as $record){
		$date = '';

		//Get date infos
		if(!empty($record['year'])){
			$year = $record['year'];
		} else {
			$year = $Y;
		}

		if(!empty($record['month'])){
			$month = str_pad($record['month'],2,'0', STR_PAD_LEFT);
		} else {
			$month = $m;
		}

		if(!empty($record['dayoftheweek'])){
			
			if(!empty($record['day']) && $record['day'] <= 5){
				$th = $ths[$record['day']];
				$specificDate = "$th {$record['dayoftheweek']} of $year-$month";
				$convertedTime = strtotime($specificDate);

    			$convertedMonth = date('m',$convertedTime);
				$convertedYear = date('Y',$convertedTime);
				
				if(($convertedYear != $year) || ($convertedMonth != $month)){
					$date = '';
				} else {
					$date = date("Y/m/d", $convertedTime);
				}

			} else {
				
				if($record['dayoftheweek'] == strtolower($l)){
					$day = $d;
					$date = "$year/$month/$day";
				}
			}

		} else {

			if(!empty($record['day'])){
				$day = str_pad($record['day'],2,'0', STR_PAD_LEFT);
			} else {
				$day = $d;
			}
			$date = "$year/$month/$day";
		}

		$hour = str_pad($record['hour'],2,'0', STR_PAD_LEFT);
		$min = str_pad($record['min'],2,'0', STR_PAD_LEFT);
		$time = $hour.':'.$min;

		echo ('ID:'.$record['id'].',date:'.$date.' '.$time.',now:'.$nowDate.' '.$nowTime.PHP_EOL);

		if(($date == $nowDate) && ($time == $nowTime)){

			$tmpImage = '';
			$tmpImageUrl = '';
			$type = 0;
			
			//If url is an image
			if(!empty($record['url'])){
				$headers = get_headers($record['url'], 1);
				if (strpos($headers['Content-Type'], 'image/') !== false) {
					$ext = pathinfo($record['url'], PATHINFO_EXTENSION);

					$tmpImageId = uniqid();
					$tmpImage = WP_OTO_POSTER_TMP_IMAGE_DIR.'img'.$tmpImageId.'.'.$ext;
					$tmpImageUrl = WP_OTO_POSTER_TMP_IMAGE_DIR_URL.'img'.$tmpImageId.'.'.$ext;
					$tmpImages[] = $tmpImage;
					copy($record['url'], $tmpImage);
					$type = 1;
				}
			}

			//If is type post and have an image
			if($type == 0){
				if(!empty($record['post_id'])){
					$featured_img_url = get_the_post_thumbnail_url($record['post_id'],'full');
					if(!empty($tmpImageUrl)){
						$tmpImageId = uniqid();
						$tmpImage = WP_OTO_POSTER_TMP_IMAGE_DIR.'img'.$tmpImageId.'.'.$ext;
						$tmpImageUrl = WP_OTO_POSTER_TMP_IMAGE_DIR_URL.'img'.$tmpImageId.'.'.$ext;
						$tmpImages[] = $tmpImage;
						copy($featured_img_url, $tmpImage);
					}
				} 
			}

			//Publish on Facebook
			if(!empty($record['is_facebook'])){

				$data = array();
				if ($type == 1) {
					
					$data['source'] = $fb->fileToUpload($tmpImage);
					$data['message'] = formatText($record['message'],$now);
				    $feed = "/$facebook_page_id/photos";

				} else {

					if(!empty($record['url'])){
						$data['link'] = $record['url'];
					} 
					$data['message'] = formatText($record['message'],$now);

				    $feed = "/$facebook_page_id/feed";
				}   
				
				try {
					$response = $fb->post($feed, $data, $facebook_app_token);
				} catch(Facebook\Exceptions\FacebookResponseException $e) {
				  	echo 'Error (07): Graph returned an error: ' . $e->getMessage().'\n';
				  	exit;
				} catch(Facebook\Exceptions\FacebookSDKException $e) {
				  	echo 'Error (08): Facebook SDK returned an error: ' . $e->getMessage().PHP_EOL;
				  	exit;
				}
				$graphNode = $response->getGraphNode();
				echo 'ID:'.$record['id']." posted on Facebook".PHP_EOL;

			}

			//Publish on twitter
			if(!empty($record['is_twitter'])){
				$data = array();
				$data['status'] = '';
				if ($type == 1) {
					$media1 = $twitter->upload('media/upload', ['media' => $tmpImage]);
					$data['media_ids'] = $media1->media_id_string;
					$data['status'] = formatText($record['message'],$now,140);
				} else {
					if(!empty($record['url'])){
						$data['status'] .= $record['url'].PHP_EOL;
					}
					$data['status'] .= formatText($record['message'],$now,140);
				}
				$statuses = $twitter->post("statuses/update", $data);
				if(is_object($statuses) && $statuses->id){
					echo 'ID:'.$record['id']." posted on Twitter".PHP_EOL;
				}
			}

			//Publish on Instagram
			if(!empty($record['is_instagram'])){
				$data = array();
				
				if(!empty($tmpImage)){
					$data['text'] = formatText($record['message'],$now,2200);
					$data['profile_ids'] = $buffer_profile_ids;
					$data['media']['photo'] = $tmpImageUrl;
					$data['attachment'] = true;
					$data['now'] = true;
					$response = $buffer->go('/updates/create',$data);
					if($response != false){
						echo 'ID:'.$record['id']." posted on Instagram".PHP_EOL;
					}
				}
			}

			//Publish on Pinterest
			if(!empty($record['is_pinterest']) && !empty($pinterest)){
				
				if(!empty($tmpImage)){
					$pinUrl = '';
					if(!empty($record['url2'])){
						$pinUrl = $record['url2'];
					} else {
						if(!empty($record['url'])){
							$pinUrl = $record['url'];
						}
					}
					
					$pinterest->pin_url = $pinUrl;
					$pinterest->pin_description = formatText($record['message'],$now,500);
					$pinterest->pin_image_preview = $pinterest->generate_image_preview($tmpImage);
					$pinterest->get_boards();
					if($pinterest->pin($pinterest->boards[$pinterest_board]) == 0){
						echo 'ID:'.$record['id']." posted on Pinterest".PHP_EOL;
					}
				}
			}
		}
	}

	if(!empty($tmpImages)){
		sleep(30);
		foreach($tmpImages as $tmpImage){
			unlink($tmpImage);
		}
	}
}
?>