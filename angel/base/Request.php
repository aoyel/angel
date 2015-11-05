<?php

namespace angel\base;

class Request extends Object{
	
	
	public function init(){
		parent::init();
		$this->filterRequest();
	}
	
	public function getParam($name,$defaultValue = null){
		return isset ( $_GET [$name] ) ? $_GET [$name] : (isset ( $_POST [$name] ) ? $_POST [$name] : $defaultValue);
	}
	
	/**
	 * check is https connection
	 * @return boolean
	 */
	public function isHttps()
	{
		return isset($_SERVER['HTTPS']) && (strcasecmp($_SERVER['HTTPS'],'on')===0 || $_SERVER['HTTPS']==1)
		|| isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'],'https')===0;
	}
	
	
	
	public function isPost(){
		return isset($_SERVER['REQUEST_METHOD']) && !strcasecmp($_SERVER['REQUEST_METHOD'],'POST');
	}
	
	/**
	 * check is ajax request
	 * @return boolean
	 */
	public function isAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest';
	}
	
	/**
	 * This method strips off slashes in request data if get_magic_quotes_gpc() returns true.
	 */
	protected function filterRequest()
	{
		// normalize request
		if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
		{
			if(isset($_GET))
				$_GET=$this->stripSlashes($_GET);
			if(isset($_POST))
				$_POST=$this->stripSlashes($_POST);
			if(isset($_REQUEST))
				$_REQUEST=$this->stripSlashes($_REQUEST);
			if(isset($_COOKIE))
				$_COOKIE=$this->stripSlashes($_COOKIE);
		}
	}
	
	/**
	 * 
	 * @param mixed $data
	 * @return mixed|multitype:|string
	 */
	public function stripSlashes(&$data)
	{
		if(is_array($data))
		{
			if(count($data) == 0)
				return $data;
			$keys=array_map('stripslashes',array_keys($data));
			$data=array_combine($keys,array_values($data));
			return array_map(array($this,'stripSlashes'),$data);
		}
		else
			return stripslashes($data);
	}
	
	
	/**
	 * get server name
	 * @return string 
	 */
	public function getServerName()
	{
		return $_SERVER['SERVER_NAME'];
	}
	
	/**
	 * get client ip
	 * @return Ambigous <string, unknown>
	 */
	public function getUserIp()
	{
		return isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'127.0.0.1';
	}
	
	
	public function redirect($url,$terminate=true,$statusCode=302)
	{
		if(strpos($url,'/')===0 && strpos($url,'//')!==0)
			$url=$this->getHostInfo().$url;
		header('Location: '.$url, true, $statusCode);
		if($terminate)
			Yii::app()->end();
	}
	
	
	
}

?>