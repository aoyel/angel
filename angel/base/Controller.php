<?php

namespace angel\base;
use angel\Angel;

/**
 * 
 * @author smile
 *
 *
 *@property \swoole_http_request Request
 *
 *@property \swoole_http_response Respone
 *
 */

class Controller extends Object {
	
	public $id;	
	public $layout = "default";
	public $action = null;
	
	public function render($view=null,$params = [],$echo=true){
		if($echo)
			echo Angel::app()->view->render($view,$params);
		else
			return Angel::app()->view->render($view,$params);
	}			
	/**
	 * 
	 */
	public function ajaxMsg($status,$data){
		echo json_encode([
			'status'=>$status,
			'data'=>$data
		]);
		return true;
	}	
	/**
	 * 
	 * @param get $name
	 * @param post $defaultVal
	 */
	public function getParam($name,$defaultVal){
		
	}
	
	protected function beforeAction($action){
		return true;
	}
	
	protected function afterAction(){
		
	}
	
	protected function isValidAction($action){
		return method_exists($this, "action".ucfirst($action));
	}
	
	/**
	 * 
	 * @param string $action
	 * @return string|boolean
	 */
	public function run($action){
		if($this->isValidAction($action) && $this->beforeAction($action)){
			$this->action = $action;
			$content = $this->execute("action".ucfirst($action));
			$this->afterAction();
			return $content;
		}
		return false;
	}
	
	/**
	 * 
	 * @param string $action
	 */
	protected function execute($action){
		return call_user_func([$this,$action]);
	}
}

?>