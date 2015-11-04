<?php

namespace angel\base;

use angel\Angel;

class Logger extends Object{
	
	const LEVEL_DEBUG = "debug";
	const LEVEL_INFO = "info";
	const LEVEL_WARNING = "warning";
	const LEVEL_ERROR = "error";
	
	
	public function log($log,$level=self::LEVEL_DEBUG){
		
	}
	
	public function getLogPath(){
		$dir = Angel::app()->runtimePath.DIRECTORY_SEPARATOR."logs";
		if(!is_dir($dir))
			mkdir($dir);
		return $dir;
	}
	
	public function getLogFile(){
		return "app.log";
	}
	
	protected function parseLogMessage($logs){
		return "log";
	}
		
	protected function processLogs($logs)
	{
		$text='';
		$text = $this->parseLogMessage($logs);
		$logFile=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();
		$fp=@fopen($logFile,'a');
		@flock($fp,LOCK_EX);
		@fwrite($fp,$text);
		@flock($fp,LOCK_UN);
		@fclose($fp);
	}
}

?>