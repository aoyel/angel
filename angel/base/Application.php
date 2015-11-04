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
			'log' => ['class' => '\angel\base\Logger'],
			'view' => ['class' => '\angel\base\View'],
			'errorHandel'=>['class'=>'\angel\base\ErrorHandel']
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
	
	public function run(){
		$this->server->start();
	}	
}

?>