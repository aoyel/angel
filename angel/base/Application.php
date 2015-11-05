<?php

namespace angel\base;

use angel\Angel;
/**
 *
 * @author smile
 *        
 * Application is the base class for all application classes.
 * 
 * @property string $runtimePath The directory that stores runtime files. Defaults to the "runtime"
 * 
 * @property string $basePath the Application base path
 * 
 * @property array $params the application params
 * 
 * @property string $appNamespace application namespace
 * 
 * @property string $applicationPath application path
 * 
 * @property \angel\base\Logger $log the application Logger object
 * 
 * @property \angel\base\View $view the application view object
 * 
 * @property \angel\base\Cache $cache the application cache object
 * 
 * @property \angel\base\server $server the application server
 * 
 * @property \angel\base\ErrorHandel $errorHandel the application error handel
 * 
 * @property \anel\base\Controller $controller the application controller
 * 
 * @property \anel\base\Action $action the application controller
 * 
 * @property array components configure conponents
 * 
 * @property \anel\base\Dispatch $dispatch configure conponents
 * 
 * @property \anel\base\Request $request configure conponents
 * 
 * @property \anel\base\Respone $respone configure conponents
 * 
 */
class Application extends Object {
	/**
	 * 
	 * @var intger id application id
	 */
	public $id;	
	
	/**
	 * application versiong
	 * @var string
	 * 
	 */
	public $version = 0.1;
	
	/**
	 * application name
	 * @var string
	 */
	public $name = 'My Application';
	
	/**
	 * application defaut charset
	 * @var string
	 */
	public $charset = 'UTF-8';
	
	/**
	 * default layouts
	 * @var string
	 */
	public $layout = 'main';
	
	
	public $runMode = "cli";
	
	/**
	 * application modules
	 * @var string
	 */
	public $loadedModules = [ ];
	
	public function __construct($config = [])
	{
		Angel::$app = $this;
		$this->prepare($config);
		$this->registerErrorHandler($config);
	}
	
	public function coreComponents()
	{
		return [
			'log' => ['class' => '\angel\log\FileLogger'],
			'view' => ['class' => '\angel\base\View'],
			'errorHandel'=>['class'=>'\angel\base\ErrorHandel'],
			'dispatch'=>['class'=>'\angel\base\Dispatch'],
			'request'=>['class'=>'\angel\base\Request'],
			'respone'=>['class'=>'\angel\base\Respone']
		];
	}
	
	public function prepare($config){
		foreach ($config as $k=>$v)
			$this->$k = $v;
		Angel::addNamespaceMap($this->appNamespace, $this->applicationPath);
		$this->runtimePath = $this->basePath.DIRECTORY_SEPARATOR."runtime";
		$coreComponents = $this->coreComponents();
		$components = array_merge($coreComponents,$this->components);
		unset($this->components);
		$this->loadComponents($components);
		$this->initDb();
	}
	
	/**
	 * init data base
	 */
	protected function initDb(){
		$this->db->connection();
	}
	
	protected function loadComponents($components){
		if(empty($components) || !is_array($components))
			return ;
		foreach ($components as $k=>$v){			
			if(isset($v['class']))
				$this->$k = Angel::createObject($v);
		}
	}
	
	protected function registerErrorHandler(&$config)
	{
		$this->errorHandel->register();
	}
	
	/**
	 * check 
	 * @return boolean
	 */
	public function isHttpMode(){
		return $this->runMode === "http";
	}		
	
	public function run(){		
		if(php_sapi_name() === "cli"){
			$this->runMode = "cli";
			$this->server->start();
		}else{
			$this->runMode = "http";
			echo $this->dispatch->handelRequest($_SERVER['REQUEST_URI']);		
		}
	}
}

?>