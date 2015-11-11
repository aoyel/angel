<?php

namespace angel\web;
use angel\Angel;
use angel\base\Object;

class Respone extends Object{
	
	static $HTTP_HEADERS = array(
			100 => "100 Continue",
			101 => "101 Switching Protocols",
			200 => "200 OK",
			201 => "201 Created",
			204 => "204 No Content",
			206 => "206 Partial Content",
			300 => "300 Multiple Choices",
			301 => "301 Moved Permanently",
			302 => "302 Found",
			303 => "303 See Other",
			304 => "304 Not Modified",
			307 => "307 Temporary Redirect",
			400 => "400 Bad Request",
			401 => "401 Unauthorized",
			403 => "403 Forbidden",
			404 => "404 Not Found",
			405 => "405 Method Not Allowed",
			406 => "406 Not Acceptable",
			408 => "408 Request Timeout",
			410 => "410 Gone",
			413 => "413 Request Entity Too Large",
			414 => "414 Request URI Too Long",
			415 => "415 Unsupported Media Type",
			416 => "416 Requested Range Not Satisfiable",
			417 => "417 Expectation Failed",
			500 => "500 Internal Server Error",
			501 => "501 Method Not Implemented",
			503 => "503 Service Unavailable",
			506 => "506 Variant Also Negotiates",
	);
	
	/**
	 * check http run mode 
	 * @return boolean
	 */
	public function isHttpMode(){
		return Angel::app()->isHttpMode();
	}
	
	/**
	 * get swoole_http_response 
	 * @throws Exception
	 * @return \angel\base\Respone|swoole_http_response
	 */
	protected function getRespone(){
		if($this->isHttpMode()){
			return $this;
		}
		if(empty(Angel::app()->server->respone)){
			throw new Exception("Respone is not availed");
		}
		return Angel::app()->server->respone;
	}
	
	/**
	 * Send a raw HTTP header
	 * @param string $key
	 * @param string $value
	 */
	public function header($key,$value){
		if($this->isHttpMode()){
			header("{$key}:{$value}");
		}else{
			$this->getRespone()->header($key, $value);
		}
	}
	
	/**
	 * Send a cookie
	 * @link setcookie
	 * @param string $name
	 * @param string $value
	 * @param string $expire
	 * @param string $path
	 * @param string $domain
	 * @param string $secure
	 * @param string $httponly
	 */
	public function setcookie($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = null, $httponly = null){
		if($this->isHttpMode()){
			setcookie($name,$value,$expire,$path,$domain,$secure,$httponly);
		}else{
			$this->getRespone()->cookie($name,$value,$expire,$path,$domain,$secure,$httponly);
		}
	}
	
	/**
	 * respone content to browser
	 * @param mixed $content
	 */
	public function write($content){
		if($this->isHttpMode()){
			echo $content;
		}else{
			$this->getRespone()->write($content);
		}
	}
}

?>