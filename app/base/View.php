<?php
namespace WpOtoPoster;

class View 
{
	private $view = null;
	private $vars = null;
	private $includeViewsBefore = null;
	private $includeViewsAfter = null;

	public function __construct($view) {
		$this->includeViewsBefore = array();
		$this->includeViewsAfter = array();
		$this->vars = array();
	    $path = dirname(dirname(__FILE__))."/view/".$view.".view.php";
	    if (file_exists($path)) {
	        $this->view = $path;
	    } else {
	        wp_die(__("View ".$path." not found"));
	    }
	}

	public function set($name,$value) {
    	$this->vars[$name] = $value;
	}

	public function includeViewBefore($name) {
		$this->includeViewsBefore[] = $name;
	}

	public function includeViewAfter($name) {
		$this->includeViewsAfter[] = $name;
	}

	public function render() {
	    
	    ob_start();
	    extract($this->vars,EXTR_SKIP);
	    foreach($this->includeViewsBefore as $name) {
	    	include dirname(dirname(__FILE__))."/view/".$name.".view.php";
	    }

	    include $this->view;

	    foreach($this->includeViewsAfter as $name) {
	    	include dirname(dirname(__FILE__))."/view/".$name.".view.php";
	    }
	    echo ob_get_clean();
	}  
}