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
	
	public function render($view=null,$params = []){
		return Angel::app()->view->render($view,$params);
	}			
	/**
	 * 
	 */
	public function ajaxMsg($status,$data){
		return json_encode([
			'status'=>$status,
			'data'=>$data
		]);
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