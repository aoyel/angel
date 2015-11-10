<?php

namespace angel\base;

use angel\Angel;
class ErrorHandel extends Object{
	
	public function init(){
		parent::init();
	}
	
	/**
	 * register error handel
	 */
	public function register(){
		set_error_handler([$this, 'handleError']);
		ini_set('display_errors', false);
		set_exception_handler([$this, 'handleException']);
		set_error_handler([$this, 'handleError']);
		register_shutdown_function([$this, 'handleFatalError']);
	}
	
	/**
	 * un register error handel
	 */
	public function unregister()
	{
		restore_error_handler();
		restore_exception_handler();
	}
	
	/**
	 * handel exception
	 * @param unknown $exception
	 */
	public function handleException($exception){
		Angel::error($exception);		
		Angel::app()->end($exception->getMessage());
	}
	
	/**
	 * handel error
	 * @param int $code
	 * @param string $message
	 * @param string $file
	 * @param string $line
	 */
	public function handleError($code, $message, $file, $line){
		Angel::error("{$code}-{$message}\n{$file}\n{$line}");
	}
	
	/**
	 * hangel fatal error
	 */
	public function handleFatalError(){
		 $error = error_get_last();
		 if($error['type'] === E_ERROR || $error['type'] === E_USER_ERROR) {
		 	//$exception = new Exception($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
		 }
	}
	
	/**
	 * save exception error
	 * @param \Exception $e
	 */
	protected function saveExceptionLogger($e){
		
	}
}

?>