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
	
	public function render($view=null,$params = [],$echo=false){
		if($echo)
			Angel::app()->respone->write(Angel::app()->view->render($view,$params));
		else
			return Angel::app()->view->render($view,$params);
	}			
	
	public function ajaxMsg($status,$data){
		Angel::app()->respone->write(json_encode([
			'status'=>$status,
			'data'=>$data
		]));
	}
	
	protected function beforeAction($action){
		return true;
	}
	
	protected function afterAction($action){
		return $action;
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
			$this->afterAction($action);
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