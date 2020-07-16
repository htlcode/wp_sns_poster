<?php
namespace WpOtoPoster;

abstract class Controller {

	private static $instances = array();

    protected $data = null;

	public function __construct(){
		
	}

    public function setData($key,$value){
        $this->data[$key] = $value;
    }

    public function getData($key){
        return $this->data[$key];
    }

	public static function load(){

        $class = get_called_class();
        
        if (!array_key_exists($class, self::$instances)) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }

    public function renderView($name, $params = null, $includeViewsBefore = null, $includeViewsAfter = null){
        $view = new View($name);

        if(isset($includeViewsBefore)) {
             foreach($includeViewsBefore as $name) {
                $view->includeViewBefore($name);
            }
        }

        if(isset($params)) {
            foreach($params as $paramName => $paramValue) {
                $view->set($paramName, $paramValue);
            }
        }

        if(isset($includeViewsAfter)) {
             foreach($includeViewsAfter as $name) {
                $view->includeViewAfter($name);
            }
        }

        $view->render();
    }
}

?>
