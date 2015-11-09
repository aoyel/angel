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
			Angel::error("{$class} is not found!");
			throw new NotFoundException("{$request} is not found!");
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