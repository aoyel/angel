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
}

?>