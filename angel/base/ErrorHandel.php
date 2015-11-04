<?php

namespace angel\base;

class ErrorHandel extends Object{
	
	public function init(){		
	}
	
	public function register(){
		set_error_handler([$this, 'handleError']);
		ini_set('display_errors', false);
		set_exception_handler([$this, 'handleException']);
		set_error_handler([$this, 'handleError']);
		register_shutdown_function([$this, 'handleFatalError']);
	}
	
	public function unregister()
	{
		restore_error_handler();
		restore_exception_handler();
	}
	
	public function handleException($exception){
		var_dump($exception);
	}
	
	public function handleError($code, $message, $file, $line){
		
	}
	
	public function handleFatalError(){
		
	}
}

?>