<?php

namespace angel\base;
use angel\base\Object;
use angel\Angel;
use angel\exception\NotFoundException;

/**
 * 
 * @author smile
 *
 */
class Dispatch extends Object{
	
	public function processRequest(\swoole_http_request $request, \swoole_http_response $respone){
		
		$route = $this->parseUrl($requestUrl);
		if(!isset($route['controller']) || !isset($route['action'])){
			throw new NotFoundException("request not found", 404);
		}
		$this->controller = ucwords($route['controller']);
		$this->action = ucwords($route['action']);
		$appNamespace = Angel::app()->appNamespace;
		$controller =  implode("\\", [
				"",
				Angel::app()->appNamespace,
				"controllers",
				$this->controller."Controller"
				]);
	
		if(!class_exists($controller)){
			throw new NotFoundException("{$controller} is not found!");
		}
		ob_start();
		/**
		 * create controller object
		*/
		$m = Angel::createObject([
				'class'=>$controller,
				'id'=>$this->controller,
				'action'=>$this->action,
				'Respone'=>$respone,
				'Request'=>$request
		]);
		Angel::app()->controller = $m;
		$a = "action{$this->action}";
		if(!method_exists($m,$a)){
			throw new NotFoundException("{$a} not exists", 404);
		}
		call_user_func(array($m,$a));
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	/**
	 * handel request
	 * @param string $request
	 */
	public function handelRequest($request){
		$route = $this->parseUrl($request);
		$class =  implode("\\", [
				"",
				Angel::app()->appNamespace,
				"controllers",
				ucfirst($route['controller'])."Controller"
		]);
		if(!class_exists($class)){
			throw new NotFoundException("{$class} is not found!");
		}
		$controller = Angel::createObject([
				'class'=>$class,
				'id'=>$route['controller']
		]);
		Angel::app()->controller = $controller;
		return $controller->run($route['action']);
	}
	
	
		
	/**
	 * parse url
	 * @param string $path
	 * @return multitype:string |multitype:string Ambigous <unknown, string>
	 */
	protected function parseUrl($path) {
		$route = [
			"controller" => "Index",
			"action" => "Index"
		];
		$path = ltrim ( $path, "//" );
		if ($path === '') {
			return $route;
		}
		$request = explode ( '/', $path, 3 );
		if (count ( $request ) < 1 && empty ( $request [0] )) {
			return $route;
		}
		$route ['controller'] = isset ( $request [0] ) ? $request [0] : $route ['controller'];
		$route ['action'] = isset ( $request [1] ) ? $request [1] : $route ['action'];
		return $route;
	}
	
	
}

?>