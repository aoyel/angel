<?php

namespace angel\base;

use angel\Angel;

class Logger extends Object{
	
	const LEVEL_DEBUG = 0x01;
	const LEVEL_INFO = 0x02;
	const LEVEL_WARNING = 0x03;
	const LEVEL_ERROR = 0x04;
	protected $maxCount = 10;
	protected $count = 0;
	protected $logs = [];	
	protected $isProcess = false;
	/**
	 * 
	 * @return multitype:string
	 */
	public static function getProvider(){
		return [
			self::LEVEL_DEBUG=>"DEBUG",
			self::LEVEL_INFO=>"INFO",
			self::LEVEL_WARNING=>"WARNING",
			self::LEVEL_ERROR=>"ERROR"
		];
	}
	
	/**
	 * 
	 * @param integer $v
	 * @return Ambigous <NULL, string>
	 */
	public static function getConfVal($v){
		$data = self::getProvider();
		return isset($data[$v])?$data[$v]:null;
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
	
	protected function processLogs($logs){
	}
	
	public function log($message,$level=self::LEVEL_DEBUG,$category='application'){
		array_push($this->logs, [
			$message,
			self::getConfVal($level),
			$category,
			time()
		]);
		$this->count++;
		
		if( ($this->count > $this->maxCount) && !$this->isProcess){
			$this->processLogs($this->logs);
		}
	}
	
	public function save(){
		$this->processLogs($this->logs);
	}
	
	
	public function flush(){
		$this->logs = [];
		$this->count = 0;
	}
	
	function __destruct(){
		$this->save();
	}
	
}

?>