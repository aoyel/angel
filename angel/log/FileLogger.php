<?php

namespace angel\log;

use angel\base\Logger;
use angel\base\Exception;
use angel\Angel;

class FileLogger extends Logger{
	
	public function getLogPath(){
		$dir = Angel::app()->runtimePath.DIRECTORY_SEPARATOR."logs";
		if(!is_dir($dir))
			mkdir($dir);
		return $dir;
	}
	
	protected function processLogs($logs)
	{
		$text='';
		$this->isProcess = true;
		$text = $this->parseLogMessage($logs);		
		$logFile=$this->getLogPath().DIRECTORY_SEPARATOR.$this->getLogFile();		
		$fp=@fopen($logFile,'a');
		@flock($fp,LOCK_EX);
		@fwrite($fp,$text);
		@flock($fp,LOCK_UN);
		@fclose($fp);
		$this->isProcess = false;
	}

	protected function parseContent($content){
		if(is_string($content)){
			return $content;
		}elseif (is_array($content)){
			return var_export($content,true);
		}elseif ($content instanceof Exception){
			return $content->getTraceAsString();
		}
		return "";
	}
	
	protected function formatLogMessage($msg,$level,$category,$time){
		$msg = $this->parseContent($msg);
		return @date('Y/m/d H:i:s',$time)." [$level] [$category] $msg\n";
	}
	
	protected function parseLogMessage($logs){
		$content = "";
		if(is_array($logs) && count($logs)>0){
			foreach($logs as $log)
				$content.=$this->formatLogMessage($log[0],$log[1],$log[2],$log[3]);
			$this->flush();
		}
		return $content;
	}
}

?>