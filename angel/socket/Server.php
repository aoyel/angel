<?php

namespace angel\socket;

use angel\base\Object;
use angel\Angel;


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
		
	protected $server;
	protected $pidfile;
	protected $logfile;
	
	const OPCODE_CONTINUATION_FRAME = 0x0;
	const OPCODE_TEXT_FRAME         = 0x1;
	const OPCODE_BINARY_FRAME       = 0x2;
	const OPCODE_CONNECTION_CLOSE   = 0x8;
	const OPCODE_PING               = 0x9;
	const OPCODE_PONG               = 0xa;
	const CLOSE_NORMAL              = 1000;
	const CLOSE_GOING_AWAY          = 1001;
	const CLOSE_PROTOCOL_ERROR      = 1002;
	const CLOSE_DATA_ERROR          = 1003;
	const CLOSE_STATUS_ERROR        = 1005;
	const CLOSE_ABNORMAL            = 1006;
	const CLOSE_MESSAGE_ERROR       = 1007;
	const CLOSE_POLICY_ERROR        = 1008;
	const CLOSE_MESSAGE_TOO_BIG     = 1009;
	const CLOSE_EXTENSION_MISSING   = 1010;
	const CLOSE_SERVER_ERROR        = 1011;
	const CLOSE_TLS                 = 1015;
	const WEBSOCKET_VERSION         = 13;
	
	
	const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
	
	
	public function init(){
		parent::init();
		$this->pidfile = Angel::app()->runtimePath.DIRECTORY_SEPARATOR."socket_server.pid";
		$this->logfile = Angel::app()->runtimePath.DIRECTORY_SEPARATOR."socket_server.log";
	}
	
	protected function setPidFile($pidFile){
		$this->pidfile = $pidFile;
	}
	
	protected function getDefaultConfig(){
		return [
			'backlog' => 128,        //listen backlog
			'log_file'=>$this->logfile,
			'pid_file'=>$this->pidfile
		];
	}
	
	public function start(){
		$this->server = new \swoole_server($this->host, $this->port,SWOOLE_SOCK_TCP);
		$this->config = array_merge($this->config,$this->getDefaultConfig());
		$this->server->set($this->config);
		$version = explode('.', SWOOLE_VERSION);
		
		
		$this->server->on('Start', array($this, 'onMasterStart'));
		$this->server->on('Shutdown', array($this, 'onMasterStop'));
		$this->server->on('ManagerStop', array($this, 'onManagerStop'));
		$this->server->on('WorkerStart', array($this, 'onWorkerStart'));
		$this->server->on('Connect', array($this, 'onConnect'));
		$this->server->on('Receive', array($this, 'onReceive'));
		$this->server->on('Close', array($this, 'onClose'));
		$this->server->on('WorkerStop', array($this, 'onShutdown'));
		
	}
	
	public function end(){
		$this->server->shutdown();
	}
	
	public function close($client_id){
		$this->server->close($client_id);
	}
	
	function addListener($host, $port, $type)
	{
		return $this->server->addlistener($host, $port, $type);
	}
	
	function send($client_id, $data)
	{
		return $this->server->send($client_id, $data);
	}
	
	protected function onMasterStart($serv)
	{		
		file_put_contents($this->pidfile, $serv->master_pid);
	}
	
	protected function onMasterStop($serv){
		
		if (!empty($this->pidfile))
		{
			@unlink($this->pidfile);
		}
	}
	
	protected function onManagerStop()
	{
	
	}
	
	protected function onWorkerStart($serv, $worker_id)
	{
				
	}
	
	protected function onConnect(){
		
	}
	
	protected function onReceive(){
		
	}
	
	protected function onClose(){
		
	}
	
	protected function onShutdown(){
		
	}
	
	
	
	
	
}

?>