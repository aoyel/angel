<?php

namespace angel\base;

use angel\Angel;
use angel\exception\NotFoundException;
use angel\helper\FileHelper;
/**
 *
 * @author smile
 *        
 * @property string host
 *          
 * @property string port
 *          
 * @property array config
 *      
 */
class Server extends Object {
	/**
	 *
	 * @param string $host        	
	 * @param int $port        	
	 * @param array $config        	
	 */
	public function start() {
		$server = new \swoole_http_server ( $this->host, $this->port );
		$server->set ( $this->config );
		$server->on ( 'Request', array (
				$this,
				'onRequest' 
		) );
		$server->start ();
	}
	
	/**
	 * request handel
	 * @param \swoole_http_request $request        	
	 * @param \swoole_http_response $respone        	
	 */
	public function onRequest(\swoole_http_request $request, \swoole_http_response $respone) {
		if ($this->beforeRequest ( $request, $respone )) {
			$this->initVars ( $request );
			$this->handelRequest($request,$respone);
		}
		$this->afterRequest ( $request, $respone );
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
	
	/**
	 * handel client request
	 * @param \swoole_http_request $request
	 * @param \swoole_http_response $respone
	 * @throws NotFoundException
	 */
	public function handelRequest(\swoole_http_request $request, \swoole_http_response $respone) {	
		$requestUrl = $_SERVER['REQUEST_URI'];
		if($this->handelStaticFile($requestUrl,$respone))
			return;
		try {
			$content = $this->processRequest($request, $respone);
			if($content)
				$respone->end($content);
			else{
				$respone->status(404);
				$respone->end();
			}
		} catch (Exception $e) {
			$respone->status(500);
			$respone->end($e->getMessage());
		}
	}
	
	public function processRequest(\swoole_http_request $request, \swoole_http_response $respone){
		$requestUrl = $_SERVER['REQUEST_URI'];
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
		return call_user_func(array($m,$a));
	}
	
	public function handelStaticFile($requestUrl,\swoole_http_response $respone){
		$filename = Angel::app()->basePath.$requestUrl;
		if(is_file($filename)){
			$mimeType = FileHelper::getMimeType($filename);
			if(empty($mimeType))
				$mimeType = 'text/html';
			$laseModifyTime = @filemtime($filename);
			if($_SERVER['HTTP_IF_MODIFIED_SINCE'] && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $laseModifyTime){
				$respone->status(304);
				$respone->end();
			}else{
				if(filesize($filename) > 216){
					$respone->gzip();
				}				
				$respone->header('Content-Type', $mimeType);
				$respone->header('last-modified',$laseModifyTime);
				swoole_async_read($filename, function($filename, $content) use ($respone) {
					$respone->end($content);
				});
			}			
			return true;
		}
		return false;
	}
	
	public function beforeRequest($request, $respone) {
		return true;
	}
	
	/**
	 * init global vars
	 *
	 * @param \swoole_http_request $request        	
	 */
	public function initVars($request) {
		$_GET = isset ( $request->get ) ? $request->get : [ ];
		$_POST = isset ( $request->post ) ? $request->post : [ ];
		$_FILES = isset ( $request->files ) ? $request->files : [ ];
		$_COOKIE = isset ( $request->cookie ) ? $request->cookie : [ ];
		$_REQUEST = array_merge ( $_GET, $_POST, $_COOKIE );
		/**
		 * convert key to upper
		 */
		$s = isset ( $request->server ) ? $request->server : [ ];
		
		foreach ( $request->header as $key => $value ) {
			$_key = 'HTTP_' . strtoupper ( str_replace ( '-', '_', $key ) );
			$s [$_key] = $value;
		}
		$_SERVER = [];
		foreach ( $s as $k => $v )
			$_SERVER [strtoupper ( $k )] = $v;
	}
	public function afterRequest($request, $respone) {
	}
}

?>