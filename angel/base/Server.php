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
	
	const MIN_GZIP_SIZE = 215;
	
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
	 * handel client request
	 * @param \swoole_http_request $request
	 * @param \swoole_http_response $respone
	 * @throws NotFoundException
	 */
	public function handelRequest(\swoole_http_request $request, \swoole_http_response $respone) {	
		$requestUrl = $_SERVER['REQUEST_URI'];
		if($this->handelStaticFile($requestUrl,$respone))
			return true;
		try {
			ob_start();			
			$content = Angel::app()->dispatch->handelRequest($requestUrl);
			$echo_output = ob_get_contents();
			if($echo_output)
				$respone->write($echo_output);
			if($content)
				$respone->write($content);
			ob_end_clean();
			$respone->end();			
		} catch (Exception $e) {
			$respone->status(500);
			$respone->end($e->getMessage());
		}
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
				if(filesize($filename) > self::MIN_GZIP_SIZE){
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
		$_GET = isset ( $request->get ) ? Angel::app()->request->stripSlashes($request->get) : [ ];
		$_POST = isset ( $request->post ) ? Angel::app()->request->stripSlashes($request->post) : [ ];
		$_FILES = isset ( $request->files ) ? $request->files : [ ];
		$_COOKIE = isset ( $request->cookie ) ? Angel::app()->request->stripSlashes($request->cookie) : [ ];
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