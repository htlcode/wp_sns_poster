<?php
namespace WpOtoPoster;

class CoreController extends Controller{

	public static $instance;
	private $json_posts_list;

	public static function get_instance() {
		if(is_null(self::$instance)) {
			self::$instance = new CoreController();;
		}
		return self::$instance;
	}

	public function __construct(){
		$this->table_name = WP_OTO_POSTER_TABLE1;
		$this->table_name2 = WP_OTO_POSTER_TABLE2;
		$this->charset_collate = WP_OTO_POSTER_CHARSET;
	}

	public function add_hooks(){
		register_activation_hook( WP_OTO_POSTER_FILE, array($this,'create_table') );
		register_deactivation_hook( WP_OTO_POSTER_FILE , array($this,'drop_table') );
		add_action ('admin_menu', array($this, 'register_menu') );
		add_action ('admin_enqueue_scripts', array($this, 'add_scripts') );
	}

	public function add_javascript_footer(){
		//Add post list in javascript
		?>
		<script>
		    var json_posts = <?php echo $this->json_posts_list ?>;
		</script>
		<?php
	}

	public function add_scripts() {
		
		if(isset($_GET["page"]) &&  $_GET["page"] == "wp_oto_poster_schedule"){

			if(!wp_script_is('jquery','enqueued')){
		    	wp_enqueue_script('jquery');
			}

			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-autocomplete');
			wp_enqueue_script('wp-oto-poster-js', plugin_dir_url( dirname(dirname(__FILE__)) ) . 'js/schedule.js', array('jquery','jquery-ui-core','jquery-ui-autocomplete'), false, true);
		}
	}

	public function load_locale(){
		$path = plugin_basename(dirname(dirname(dirname(__FILE__)))) . '/languages';
	    $loaded = load_plugin_textdomain( WP_OTO_POSTER , false, $path);
	}

	public function register_menu(){
	    add_menu_page( 
	        __( 'Wp Oto Poster', WP_OTO_POSTER),
	        'Wp Oto Poster',
	        'manage_options',
	        'wp_oto_poster',
	        array($this,'settings'),
	        'dashicons-share',
	        81
	    ); 

	    add_submenu_page( 
	        'wp_oto_poster', 
	        __('Schedules List', WP_OTO_POSTER), 
	        __('Schedules List', WP_OTO_POSTER), 
	        'manage_options', 
	        'wp_oto_poster_schedules', 
	        array($this,'schedules')
	    );

	    add_submenu_page( 
	        'wp_oto_poster', 
	        __('Add Schedule', WP_OTO_POSTER), 
	        __('Add Schedule', WP_OTO_POSTER), 
	        'manage_options', 
	        'wp_oto_poster_schedule', 
	        array($this,'schedule')
	    );

	    add_submenu_page( 
	        'wp_oto_poster', 
	        __('Randoms List', WP_OTO_POSTER), 
	        __('Randoms List', WP_OTO_POSTER), 
	        'manage_options', 
	        'wp_oto_poster_randoms', 
	        array($this,'randoms')
	    );

	    add_submenu_page( 
	        'wp_oto_poster', 
	        __('Add Random', WP_OTO_POSTER), 
	        __('Add Random', WP_OTO_POSTER), 
	        'manage_options', 
	        'wp_oto_poster_random', 
	        array($this,'random')
	    );
	}

	public function __destruct(){
		self::$instance = null;
	}

	private function get_post_id_from_url($url){
		$post_id = null;
		foreach($this->posts_list as $post){
			if($post['permalink'] == $url){
				$post_id = $post['post_id'];
			}
		}
		return $post_id;
	}

	private function fill_post_list(){
		//Get all posts
		$args = array(
			'post_type' => 'post',
			'post_status' => 'publish',
			'posts_per_page'   => -1
		);

		$posts = get_posts( $args );
		$this->posts_list = array();
		if( $posts ){
			foreach( $posts as $k => $post ) {
				$this->posts_list[$k]['post_id'] = $post->ID;
				$this->posts_list[$k]['label'] = $post->post_title;
				$this->posts_list[$k]['permalink'] = get_permalink( $post->ID );
			}
		}
	}

	public function randoms(){
		if (!current_user_can('manage_options')) {
        	wp_die('Unauthorized user');
    	}

    	//Delete record if delete parameter 
    	$db = new Db();
    	if (isset($_GET['delete'])) {
    		if(is_numeric($_GET['delete'])){
    			$sql = "DELETE FROM $this->table_name2 WHERE id=?";
    			$params = array($_GET['delete']);
				$db->execute_query($sql,'i',$params);
    		}
		}
		//Get all records
		$sql = "SELECT * FROM $this->table_name2";
		$records = $db->select_query($sql);

		//Pass records to view
		$params = compact('records');
		$this->renderView('randoms',$params);
	}

	public function random(){
		if (!current_user_can('manage_options')) {
        	wp_die('Unauthorized user');
    	}

		//Initialize variables
		$dateFactory = new DateFactory();
		$ths = $dateFactory->getThs();
		$db = new Db();
		$errors = array();

		$success = 0;
    	$valid = true;
    	$id = null;
    	$post_year = null;
    	$post_month = null;
    	$post_day = null;
    	$post_hour = 0;
    	$post_min = 0;
    	$post_dayoftheweek = null;
    	$post_title = null;
    	$post_categories = null;
    	$post_tags = null;
    	$post_message0 = null;
    	$post_message = null;
    	$post_type = 0;
    	$post_is_content = 1;
    	$post_is_facebook = 0;
    	$post_is_twitter = 0;
    	$post_is_instagram = 0;
    	$post_is_pinterest = 0;
    	$tmp_year = null;

		//Get current date : Year, Month, Day
		$now = new \DateTime("now");
		$Y = $now->format('Y');
		$m = $now->format('m');
		$d = $now->format('d');
		
		//Get id parameter if exist in GET or POST method
    	if (isset($_GET['id'])) {
    		if(is_numeric($_GET['id'])){
    			$id = $_GET['id'];
    		}
    	} else {
	    	if (isset($_POST['id'])) {
	    		if(is_numeric($_POST['id'])){
	    			$id = $_POST['id'];
	    		}
	    	}
    	}

    	//On POST method (When saving the form)
	    if($_SERVER['REQUEST_METHOD'] == 'POST'){
	    	
	    	//Get posted year	
    		if(isset($_POST['year']) && is_numeric($_POST['year'])){
    			$post_year = $_POST['year'];
    		} 

    		//Get posted month	
    		if(isset($_POST['month']) && is_numeric($_POST['month'])){
    			$post_month = $_POST['month'];
    		} 

    		//Get posted day	
    		if(isset($_POST['day']) && is_numeric($_POST['day'])){
    			$post_day = $_POST['day'];
    		}

    		//Check if date is correct
    		if(!empty($post_month) && !empty($post_day)){
    			$tmp_year = $post_year;
    			if(empty($tmp_year)){
    				$tmp_year = 2000;
    			}
    			if(!checkdate($post_month, $post_day, $tmp_year)){
    				$valid = false;
    				$errors[] = __("Invalid Date : This date is not correct",WP_OTO_POSTER);
    			}
    		}

    		//Get posted hour	
    		if(isset($_POST['hour']) && is_numeric($_POST['hour'])){
    			$post_hour = $_POST['hour'];
    		}

    		//Get posted min
    		if(isset($_POST['min']) && is_numeric($_POST['min'])){
    			$post_min = $_POST['min'];
    		}

    		//Get posted dayoftheweek
    		if(isset($_POST['dayoftheweek']) && $_POST['dayoftheweek'] != '-'){
    			$post_dayoftheweek = $_POST['dayoftheweek'];
    			$specificDate = '';

    			//Check date again with dayoftheweek
    			$th = null;
    			if(!empty($post_day)){
	    			if(!array_key_exists($post_day, $ths)){
	    				$valid = false;
	    				$errors[] = __("Invalid Date : {$post_day}(st/nd/rd/th) {$post_dayoftheweek} don't exist in month",WP_OTO_POSTER);
	    			} else {
	    				$th = $ths[$post_day];
	    			}

	    			if($valid && !empty($post_year) && !empty($post_month)){

	    				$specificDate = "$th $post_dayoftheweek of $post_year-$post_month";
	    				$convertedTime = strtotime($specificDate);
	    				$convertedMonth = date('m',$convertedTime);
						$convertedYear = date('Y',$convertedTime);
	    				
						if(($convertedYear != $post_year) || ($convertedMonth != $post_month)){
	    					$valid = false;
	    					$errors[] = __("Invalid Date : {$post_day}(st/nd/rd/th) {$post_dayoftheweek} don't exist in month",WP_OTO_POSTER);
						}
	    			}
	    		}
    		}

    		//Get posted posted type
    		if(isset($_POST['post_type'])){
    			$post_type = intval($_POST['post_type']);
    		}

    		//Get posted flag for is_facebook
    		if(isset($_POST['is_facebook'])){
    			$post_is_facebook = intval($_POST['is_facebook']);
    		}

    		//Get posted flag for is_twitter
    		if(isset($_POST['is_twitter'])){
    			$post_is_twitter = intval($_POST['is_twitter']);
    		}

    		//Get posted flag for is_instagram
    		if(isset($_POST['is_instagram'])){
    			$post_is_instagram = intval($_POST['is_instagram']);
    		}

    		//Get posted flag for is_pinterest
    		if(isset($_POST['is_pinterest'])){
    			$post_is_pinterest = intval($_POST['is_pinterest']);
    		}

    		//Get posted title
    		if(isset($_POST['title'])){
    			$post_title = stripslashes($_POST['title']);
    		}
    		if(empty($post_title)){
    			$valid = false;
    			$errors[] = __('Title is empty',WP_OTO_POSTER);
    		}

    		//Get posted categories
    		if(isset($_POST['categories']) && !empty($_POST['categories'])){
    			$post_categories = stripslashes($_POST['categories']);
    		}

    		//Get posted categories
    		if(isset($_POST['tags']) && !empty($_POST['tags'])){
    			$post_tags = stripslashes($_POST['tags']);
    		}

    		//Get posted message
    		if(isset($_POST['message0']) && !empty($_POST['message0'])){
    			$post_message0 = stripslashes($_POST['message0']);
    		}

    		//Get posted message
    		if(isset($_POST['message']) && !empty($_POST['message'])){
    			$post_message = stripslashes($_POST['message']);
    		}

    		//Get posted flag for is_content
    		if(isset($_POST['is_content'])){
    			$post_is_content = intval($_POST['is_content']);
    		}

 
    		//Check if all validations passed
    		if($valid){
    			//Insert new record if $id not exist
    			if(empty($id)){
		    		$sql = "INSERT INTO $this->table_name2 
		    				(year,month,day,hour,min,dayoftheweek,title,categories,tags,post_type,message0,message,is_content,is_facebook,is_twitter,is_instagram,is_pinterest) 
		    				VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
		    		$params = array($post_year,$post_month,$post_day,$post_hour,$post_min,$post_dayoftheweek,$post_title,$post_categories,$post_tags,$post_type,$post_message0,$post_message,$post_is_content,$post_is_facebook,$post_is_twitter,$post_is_instagram,$post_is_pinterest);

	    			if($db->execute_query($sql,'iiiiissssissiiiii',$params)){
	    				$id = $db->get_last_id();
	    				$success = 1;
	    			}
	    		//Update record if $id exist;
	    		} else {
	    			$sql = "UPDATE $this->table_name2 SET year = ?, month = ?, day = ?, hour = ?, min = ?, dayoftheweek = ?, title = ?, categories = ?, tags = ?, post_type = ?, message0 = ?, message = ?, is_content = ?, is_facebook = ?, is_twitter = ?, is_instagram = ?, is_pinterest = ? WHERE id = ?";
	    			$params = array($post_year,$post_month,$post_day,$post_hour,$post_min,$post_dayoftheweek,$post_title,$post_categories,$post_tags,$post_type,$post_message0,$post_message,$post_is_content,$post_is_facebook,$post_is_twitter,$post_is_instagram,$post_is_pinterest,$id);

	    			if($db->execute_query($sql,'iiiiissssissiiiiii',$params)){
	    				$success = 1;
	    			}

	    		}
    		}
    	}

    	//If retrieved $id and no errors we get last info from DB
    	if (!empty($id) && empty($errors)) {

			$sql = "SELECT * FROM $this->table_name2 WHERE id =".$id;
			$results = $db->select_query($sql);

			if(!empty($results)){
				$post_year = $results[0]['year'];
				$post_month = $results[0]['month'];
				$post_day = $results[0]['day'];
				$post_hour = $results[0]['hour'];
				$post_min = $results[0]['min'];
				$post_dayoftheweek = $results[0]['dayoftheweek'];
				$post_title = $results[0]['title'];
				$post_categories = $results[0]['categories'];
				$post_tags = $results[0]['tags'];
				$post_type = $results[0]['post_type'];
				$post_message0 = $results[0]['message0'];
				$post_message = $results[0]['message'];
				$post_is_content = $results[0]['is_content'];
				$post_is_facebook = $results[0]['is_facebook'];
		    	$post_is_twitter = $results[0]['is_twitter'];
		    	$post_is_instagram = $results[0]['is_instagram'];
		    	$post_is_pinterest = $results[0]['is_pinterest'];
			} else {
				//If $id not exist
				$id = null;
				wp_die('Invalid id');
			}
    	}

    	//Fill lists for the view
		$years = array_merge(array('-'=>'-'),$dateFactory->getYears());
		$months = array_merge(array('-'=>'-'),$dateFactory->getMonths());
		$days = array_merge(array('-'=>'-'),$dateFactory->getDays());
		$daysOfTheWeek = array_merge(array('-'=>'-'),$dateFactory->getDaysOfTheWeek());
		$hours = $dateFactory->getHours();
		$mins = $dateFactory->getMins();

		//Set parameters
		$params = compact('id',
						  'years',
						  'months',
						  'days',
						  'daysOfTheWeek',
						  'hours',
						  'mins',
						  'post_year',
						  'post_month',
						  'post_day',
						  'post_hour',
						  'post_min',
						  'post_dayoftheweek',
						  'post_title',
						  'post_categories',
						  'post_tags',
						  'post_type',
						  'post_message0',
						  'post_message',
						  'post_is_content',
						  'post_is_facebook',
						  'post_is_twitter',
						  'post_is_instagram',
						  'post_is_pinterest',
						  'errors',
						  'success');

		//Pass params to view
		$this->renderView('random',$params);
	}

	public function schedules(){
		if (!current_user_can('manage_options')) {
        	wp_die('Unauthorized user');
    	}

    	//Delete record if delete parameter 
    	$db = new Db();
    	if (isset($_GET['delete'])) {
    		if(is_numeric($_GET['delete'])){
    			$sql = "DELETE FROM $this->table_name WHERE id=?";
    			$params = array($_GET['delete']);
				$db->execute_query($sql,'i',$params);
    		}
		}
		//Get all records
		$sql = "SELECT * FROM $this->table_name";
		$records = $db->select_query($sql);

		//Pass records to view
		$params = compact('records');
		$this->renderView('schedules',$params);
	}

	public function schedule(){
		if (!current_user_can('manage_options')) {
        	wp_die('Unauthorized user');
    	}

		//Initialize variables
		$dateFactory = new DateFactory();
		$ths = $dateFactory->getThs();
		$db = new Db();
		$errors = array();

		$success = 0;
    	$valid = true;
    	$id = null;
    	$post_year = null;
    	$post_month = null;
    	$post_day = null;
    	$post_hour = 0;
    	$post_min = 0;
    	$post_dayoftheweek = null;
    	$post_title = null;
    	$post_url = null;
    	$post_url2 = null;
    	$post_message = null;
    	$post_id = null;
    	$post_is_facebook = 0;
    	$post_is_twitter = 0;
    	$post_is_instagram = 0;
    	$post_is_pinterest = 0;
    	$tmp_year = null;

    	//Get all posts list end encode them in json format
    	$this->fill_post_list();
		$this->json_posts_list = json_encode($this->posts_list);

		//Get current date : Year, Month, Day
		$now = new \DateTime("now");
		$Y = $now->format('Y');
		$m = $now->format('m');
		$d = $now->format('d');
		
		//Get id parameter if exist in GET or POST method
    	if (isset($_GET['id'])) {
    		if(is_numeric($_GET['id'])){
    			$id = $_GET['id'];
    		}
    	} else {
	    	if (isset($_POST['id'])) {
	    		if(is_numeric($_POST['id'])){
	    			$id = $_POST['id'];
	    		}
	    	}
    	}

    	//On POST method (When saving the form)
	    if($_SERVER['REQUEST_METHOD'] == 'POST'){
	    	
	    	//Get posted year	
    		if(isset($_POST['year']) && is_numeric($_POST['year'])){
    			$post_year = $_POST['year'];
    		} 

    		//Get posted month	
    		if(isset($_POST['month']) && is_numeric($_POST['month'])){
    			$post_month = $_POST['month'];
    		} 

    		//Get posted day	
    		if(isset($_POST['day']) && is_numeric($_POST['day'])){
    			$post_day = $_POST['day'];
    		}

    		//Check if date is correct
    		if(!empty($post_month) && !empty($post_day)){
    			$tmp_year = $post_year;
    			if(empty($tmp_year)){
    				$tmp_year = 2000;
    			}
    			if(!checkdate($post_month, $post_day, $tmp_year)){
    				$valid = false;
    				$errors[] = __("Invalid Date : This date is not correct",WP_OTO_POSTER);
    			}
    		}

    		//Get posted hour	
    		if(isset($_POST['hour']) && is_numeric($_POST['hour'])){
    			$post_hour = $_POST['hour'];
    		}

    		//Get posted min
    		if(isset($_POST['min']) && is_numeric($_POST['min'])){
    			$post_min = $_POST['min'];
    		}

    		//Get posted dayoftheweek
    		if(isset($_POST['dayoftheweek']) && $_POST['dayoftheweek'] != '-'){
    			$post_dayoftheweek = $_POST['dayoftheweek'];
    			$specificDate = '';

    			//Check date again with dayoftheweek
    			$th = null;
    			if(!empty($post_day)){
	    			if(!array_key_exists($post_day, $ths)){
	    				$valid = false;
	    				$errors[] = __("Invalid Date : {$post_day}(st/nd/rd/th) {$post_dayoftheweek} don't exist in month",WP_OTO_POSTER);
	    			} else {
	    				$th = $ths[$post_day];
	    			}

	    			if($valid && !empty($post_year) && !empty($post_month)){

	    				$specificDate = "$th $post_dayoftheweek of $post_year-$post_month";
	    				$convertedTime = strtotime($specificDate);
	    				$convertedMonth = date('m',$convertedTime);
						$convertedYear = date('Y',$convertedTime);
	    				
						if(($convertedYear != $post_year) || ($convertedMonth != $post_month)){
	    					$valid = false;
	    					$errors[] = __("Invalid Date : {$post_day}(st/nd/rd/th) {$post_dayoftheweek} don't exist in month",WP_OTO_POSTER);
						}
	    			}
	    		}
    		}

    		//Get posted url and check if its valid
    		if(isset($_POST['url']) && !empty($_POST['url'])){
    			$post_url = $_POST['url'];
    			if(!filter_var($post_url, FILTER_VALIDATE_URL)){
    				$valid = false;
    				$errors[] = __('Url is not valid',WP_OTO_POSTER);
    			} else {
    				//Try to get the post ID if exists
    				$post_id = $this->get_post_id_from_url($post_url);
    			}
    		}

    		//Get posted flag for is_facebook
    		if(isset($_POST['is_facebook'])){
    			$post_is_facebook = intval($_POST['is_facebook']);
    		}

    		//Get posted flag for is_twitter
    		if(isset($_POST['is_twitter'])){
    			$post_is_twitter = intval($_POST['is_twitter']);
    		}

    		//Get posted flag for is_instagram
    		if(isset($_POST['is_instagram'])){
    			$post_is_instagram = intval($_POST['is_instagram']);
    			if(empty($post_url)){
    				$valid = false;
		    		$errors[] = __("Url must be an image",WP_OTO_POSTER);
    			} else {
    				if(!empty($post_is_instagram)){
	    				$headers = get_headers($post_url, 1);

	    				//If url is not a post and is not an image
						if ((empty($post_id)) && (strpos($headers['Content-Type'], 'image/') === false)) {
							$valid = false;
		    				$errors[] = __("Url must be an image",WP_OTO_POSTER);
						}
    				}
    			}
    			
    		}

    		//Get posted flag for is_instagram
    		if(isset($_POST['is_pinterest'])){
    			$post_is_pinterest = intval($_POST['is_pinterest']);
    			if(empty($post_url)){
    				$valid = false;
		    		$errors[] = __("Url must be an image",WP_OTO_POSTER);
    			} else {
	    			if(!empty($post_is_pinterest)){
	    				$headers = get_headers($post_url, 1);

	    				//If url is not a post and is not an image
						if ((empty($post_id)) && (strpos($headers['Content-Type'], 'image/') === false)) {
							$valid = false;
		    				$errors[] = __("Url must be an image",WP_OTO_POSTER);
						}
	    			}
	    		}
    		}

    		//Get posted title
    		if(isset($_POST['title'])){
    			$post_title = stripslashes($_POST['title']);
    		}
    		if(empty($post_title)){
    			$valid = false;
    			$errors[] = __('Title is empty',WP_OTO_POSTER);
    		}

    		//Get posted url2 and check if its valid
    		if(isset($_POST['url2']) && !empty($_POST['url2'])){
    			$post_url2 = $_POST['url2'];
    			if(!filter_var($post_url, FILTER_VALIDATE_URL)){
    				$valid = false;
    				$errors[] = __('Url #2 is not valid',WP_OTO_POSTER);
    			}
    		}

    		//Get posted message
    		if(isset($_POST['message']) && !empty($_POST['message'])){
    			$post_message = stripslashes($_POST['message']);
    		}

    		//Check if url or message are filled
    		if(empty($post_url) && empty($post_message)){
    			$valid = false;
    			$errors[] = __('Url or Message is empty',WP_OTO_POSTER);
    		}

    		//Check if all validations passed
    		if($valid){
    			//Insert new record if $id not exist
    			if(empty($id)){
		    		$sql = "INSERT INTO $this->table_name 
		    				(year,month,day,hour,min,dayoftheweek,title,url,url2,message,post_id,is_facebook,is_twitter,is_instagram,is_pinterest) 
		    				VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
		    		$params = array($post_year,$post_month,$post_day,$post_hour,$post_min,$post_dayoftheweek,$post_title,$post_url,$post_url2,$post_message,$post_id,$post_is_facebook,$post_is_twitter,$post_is_instagram,$post_is_pinterest);

	    			if($db->execute_query($sql,'iiiiisssssiiiii',$params)){
	    				$id = $db->get_last_id();
	    				$success = 1;
	    			}
	    		//Update record if $id exist;
	    		} else {
	    			$sql = "UPDATE $this->table_name SET year = ?, month = ?, day = ?, hour = ?, min = ?, dayoftheweek = ?, title = ?, url = ?, url2 = ?, message = ?, post_id = ?, is_facebook = ?, is_twitter = ?, is_instagram = ?, is_pinterest = ? WHERE id = ?";
	    			$params = array($post_year,$post_month,$post_day,$post_hour,$post_min,$post_dayoftheweek,$post_title,$post_url,$post_url2,$post_message,$post_id,$post_is_facebook,$post_is_twitter,$post_is_instagram,$post_is_pinterest,$id);

	    			if($db->execute_query($sql,'iiiiisssssiiiiii',$params)){
	    				$success = 1;
	    			}

	    		}
    		}
    	}

    	//If retrieved $id and no errors we get last info from DB
    	if (!empty($id) && empty($errors)) {

			$sql = "SELECT * FROM $this->table_name WHERE id =".$id;
			$results = $db->select_query($sql);

			if(!empty($results)){
				$post_year = $results[0]['year'];
				$post_month = $results[0]['month'];
				$post_day = $results[0]['day'];
				$post_hour = $results[0]['hour'];
				$post_min = $results[0]['min'];
				$post_dayoftheweek = $results[0]['dayoftheweek'];
				$post_title = $results[0]['title'];
				$post_url = $results[0]['url'];
				$post_url2 = $results[0]['url2'];
				$post_message = $results[0]['message'];
				$post_id = $results[0]['post_id'];
				$post_is_facebook = $results[0]['is_facebook'];
		    	$post_is_twitter = $results[0]['is_twitter'];
		    	$post_is_instagram = $results[0]['is_instagram'];
		    	$post_is_pinterest = $results[0]['is_pinterest'];
			} else {
				//If $id not exist
				$id = null;
				wp_die('Invalid id');
			}
    	}

    	//Fill lists for the view
		$years = array_merge(array('-'=>'-'),$dateFactory->getYears());
		$months = array_merge(array('-'=>'-'),$dateFactory->getMonths());
		$days = array_merge(array('-'=>'-'),$dateFactory->getDays());
		$daysOfTheWeek = array_merge(array('-'=>'-'),$dateFactory->getDaysOfTheWeek());
		$hours = $dateFactory->getHours();
		$mins = $dateFactory->getMins();

		//Set parameters
		$params = compact('id',
						  'years',
						  'months',
						  'days',
						  'daysOfTheWeek',
						  'hours',
						  'mins',
						  'post_year',
						  'post_month',
						  'post_day',
						  'post_hour',
						  'post_min',
						  'post_dayoftheweek',
						  'post_title',
						  'post_url',
						  'post_url2',
						  'post_message',
						  'post_is_facebook',
						  'post_is_twitter',
						  'post_is_instagram',
						  'post_is_pinterest',
						  'errors',
						  'success');

		//Insert javascript in footer
		add_action('admin_footer', array($this, 'add_javascript_footer'), 99);
		//Pass params to view
		$this->renderView('schedule',$params);
	}

	public function settings(){
		if (!current_user_can('manage_options')) {
        	wp_die('Unauthorized user');
    	}

    	$dateFactory = new DateFactory();
    	//Create timezone list
    	$timezones = $dateFactory->getTimezones();

		//Initialize variables
		$params = array();
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

		$timezone = '';

		$data = null;

		//On POST method (When saving the form)
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
			$data = array();
			//Get all posted fields and save them
			$data['timezone'] = $_POST['timezone'];
			$data['my_secret_key'] = $_POST['my_secret_key'];
			$data['facebook_app_id'] = $_POST['facebook_app_id'];
			$data['facebook_app_secret'] = $_POST['facebook_app_secret'];
			$data['facebook_app_token'] = $_POST['facebook_app_token'];
			$data['facebook_page_id'] = $_POST['facebook_page_id'];

			$data['twitter_consumer_api_key'] = $_POST['twitter_consumer_api_key'];
			$data['twitter_consumer_api_secret_key'] = $_POST['twitter_consumer_api_secret_key'];
			$data['twitter_access_token'] = $_POST['twitter_access_token'];
			$data['twitter_token_secret'] = $_POST['twitter_token_secret'];

			$data['buffer_client_id'] = $_POST['buffer_client_id'];
			$data['buffer_client_secret'] = $_POST['buffer_client_secret'];
			$data['buffer_redirect_uri'] = $_POST['buffer_redirect_uri'];
			$data['buffer_access_token'] = $_POST['buffer_access_token'];

			$data['pinterest_user'] = $_POST['pinterest_user'];
			$data['pinterest_password'] = $_POST['pinterest_password'];
			$data['pinterest_board'] = $_POST['pinterest_board'];
			update_option(WP_OTO_POSTER, json_encode($data) );
		}

		//Get saved settings data
		$data = get_option(WP_OTO_POSTER);
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

		//Set parameters
		$params = compact('timezones',
						  'timezone',
						  'my_secret_key',
						  'facebook_app_id',
						  'facebook_app_secret',
						  'facebook_app_token',
						  'facebook_page_id',
						  'twitter_consumer_api_key',
						  'twitter_consumer_api_secret_key',
						  'twitter_access_token',
						  'twitter_token_secret',
						  'buffer_client_id',
						  'buffer_client_secret',
						  'buffer_redirect_uri',
						  'buffer_access_token',
						  'pinterest_user',
						  'pinterest_password',
						  'pinterest_board'
						   );

		//Pass parameter to view
		$this->renderView('settings',$params);
	}

	public function create_table() {
		//Initialize table SQL
		$sql_structure = 
		"CREATE TABLE $this->table_name (
			id int NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			url varchar(255) NULL DEFAULT NULL,
			url2 varchar(255) NULL DEFAULT NULL,
			message text NULL DEFAULT NULL,
			year int(4) NULL DEFAULT NULL,
			month int(2) NULL DEFAULT NULL,
			day int(2) NULL DEFAULT NULL,
			dayoftheweek varchar(20) NULL DEFAULT NULL,
			hour int(2) NOT NULL DEFAULT 0,
			min int(2) NOT NULL DEFAULT 0,
			is_facebook int(1) NOT NULL DEFAULT 0,
			is_twitter int(1) NOT NULL DEFAULT 0,
			is_pinterest int(1) NOT NULL DEFAULT 0,
			is_instagram int(1) NOT NULL DEFAULT 0,
			post_id bigint(20) NULL DEFAULT NULL,
			PRIMARY KEY  (id)
		) $this->charset_collate;

		CREATE TABLE $this->table_name2 (
			id int NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL,
			message0 text NULL DEFAULT NULL,
			message text NULL DEFAULT NULL,
			categories text NULL DEFAULT NULL,
			tags text NULL DEFAULT NULL,
			post_type int(2) NOT NULL DEFAULT 0,
			is_content int(2) NOT NULL DEFAULT 0,
			year int(4) NULL DEFAULT NULL,
			month int(2) NULL DEFAULT NULL,
			day int(2) NULL DEFAULT NULL,
			dayoftheweek varchar(20) NULL DEFAULT NULL,
			hour int(2) NOT NULL DEFAULT 0,
			min int(2) NOT NULL DEFAULT 0,
			is_facebook int(1) NOT NULL DEFAULT 0,
			is_twitter int(1) NOT NULL DEFAULT 0,
			is_pinterest int(1) NOT NULL DEFAULT 0,
			is_instagram int(1) NOT NULL DEFAULT 0,
			published_stack text NULL DEFAULT NULL,
			PRIMARY KEY  (id)
		) $this->charset_collate;
		";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_structure);

		add_option( 'wp_oto_poster_version', WP_OTO_POSTER_VERSION );
	}

	public function drop_table() {
		//Delete table SQL
		global $wpdb;
		$sql = "DROP TABLE IF EXISTS $this->table_name;";
		$wpdb->query($sql);
		$sql = "DROP TABLE IF EXISTS $this->table_name2;";
		$wpdb->query($sql);
		delete_option('wp_oto_poster_version');
	}
}
?>
