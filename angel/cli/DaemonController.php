<?php

namespace angel\cli;
use angel\Angel;
use angel\exception\NotFoundException;

abstract class DaemonController extends Console {
	const EVENT_BEFORE_JOB = "EVENT_BEFORE_JOB";
	const EVENT_AFTER_JOB = "EVENT_AFTER_JOB";
	const EVENT_BEFORE_ITERATION = "event_before_iteration";
	const EVENT_AFTER_ITERATION = "event_after_iteration";
	/**
	 *
	 * @var $demonize boolean Run controller as Daemon
	 *      @default false
	 */
	public $demonize = false;
	/**
	 *
	 * @var $isMultiInstance boolean allow daemon create a few instances
	 * @see $maxChildProcesses @default false
	 */
	public $isMultiInstance = false;
	/**
	 *
	 * @var $parentPID int main procces pid
	 */
	protected $parentPID;
	/**
	 *
	 * @var $maxChildProcesses int max daemon instances
	 *      @default 10
	 */
	public $maxChildProcesses = 10;
	public $debug = true;
	/**
	 *
	 * @var $currentJobs [] array of running instances
	 */
	protected static $currentJobs = [ ];
	/**
	 *
	 * @var int Memory limit for daemon, must bee less than php memory_limit
	 *      @default 32M
	 */
	private $memoryLimit = 268435456;
	/**
	 *
	 * @var int used for soft daemon stop, set 1 to stop
	 */
	private static $stopFlag = 0;
	/**
	 *
	 * @var int Delay between task list checking
	 *      @default 5sec
	 */
	protected $sleep = 5;
	protected $pidDir = "/daemons/pids";
	protected $logDir = "/daemons/logs";
	private $shortName = '';
	/**
	 * Init function
	 */
	public function init() {
		parent::init ();
		// set PCNTL signal handlers
		pcntl_signal ( SIGTERM, [ 
				__CLASS__,
				'signalHandler' 
		] );
		pcntl_signal ( SIGHUP, [ 
				__CLASS__,
				'signalHandler' 
		] );
		pcntl_signal ( SIGUSR1, [ 
				__CLASS__,
				'signalHandler' 
		] );
		pcntl_signal ( SIGCHLD, [ 
				__CLASS__,
				'signalHandler' 
		] );
		$this->shortName = $this->shortClassName ();
	}
	
	/**
	 * Daemon worker body
	 *
	 * @param
	 *        	$job
	 * @return boolean
	 */
	abstract protected function doJob($job);
	/**
	 * Base action, you can\t override or create another actions
	 *
	 * @return boolean
	 */
	final public function actionIndex() {
		if ($this->demonize) {
			
			if (file_exists ( $this->getPidPath () )) {
				$this->writeConsole ( "process is runing,place run stop frist!" );
				exit ( 0 );
			}
			
			$pid = pcntl_fork ();
			if ($pid == - 1) {
				$this->halt ( self::EXIT_CODE_ERROR, 'pcntl_fork() rise error' );
			} elseif ($pid) {
				$this->halt ( self::EXIT_CODE_NORMAL );
			} else {
				posix_setsid ();
				// close std streams (unlink console)
				if (is_resource ( STDIN )) {
					fclose ( STDIN );
					$stdIn = fopen ( '/dev/null', 'r' );
				}
				if (is_resource ( STDOUT )) {
					fclose ( STDOUT );
					$stdOut = fopen ( '/dev/null', 'ab' );
				}
				if (is_resource ( STDERR )) {
					fclose ( STDERR );
					$stdErr = fopen ( '/dev/null', 'ab' );
				}
			}
		}
		// rename process
		if (version_compare ( PHP_VERSION, '5.5.0' ) >= 0) {
			cli_set_process_title ( $this->getProcessName () );
		} else {
			if (function_exists ( 'setproctitle' )) {
				setproctitle ( $this->getProcessName () );
			} else {
				throw new NotFoundException( "Can't find cli_set_process_title or setproctitle function" );
			}
		}
		// run iterator
		return $this->loop ();
	}
	
	/**
	 * stop
	 */
	public function actionStop() {
		$pid = intval ( file_get_contents ( $this->getPidPath () ) );
		if ($this->isProcessRunning ( $pid )) {
			posix_kill ( $pid, SIGTERM );
		}
		$this->writeConsole ( "stoped process {$pid}" );
	}
	
	public function actionState() {
		if (file_exists ( $this->getPidPath () )) {
			$pid = intval ( file_get_contents ( $this->getPidPath () ) );
			if ($this->isProcessRunning ( $pid )) {
				$this->writeConsole ( "runing" );
			} else {
				$this->writeConsole ( "unknow" );
			}
		} else {
			$this->writeConsole ( "stoped" );
		}
	}
	
	protected function getProcessName() {
		return $this->shortName;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \angel\base\Controller::beforeAction()
	 */
	public function beforeAction($action) {
		if (parent::beforeAction ( $action )) {			
			$allowAction = [ 
					"index",
					"stop",
					"state",
					"help" 
			];
			if (! in_array ( $action->id, $allowAction )) {
				throw new NotFoundException( "Only index action allowed in daemons. So, don't create and call another" );
			}
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Возвращает доступные опции
	 *
	 * @param string $actionID        	
	 * @return array
	 */
	public function options($actionID) {
		return [ 
				'demonize',
				'taskLimit',
				'isMultiInstance',
				'maxChildProcesses' 
		];
	}
	/**
	 * Extract current unprocessed jobs
	 * You can extract jobs from DB (DataProvider will be great), queue managers (ZMQ, RabbiMQ etc), redis and so on
	 *
	 * @return array with jobs
	 */
	abstract protected function defineJobs();
	/**
	 * Fetch one task from array of tasks
	 *
	 * @param
	 *        	Array
	 * @return mixed one task
	 */
	protected function defineJobExtractor(&$jobs) {
		return array_shift ( $jobs );
	}
	/**
	 * Main iterator
	 *
	 * * @return boolean 0|1
	 */
	final private function loop() {
		if (file_put_contents ( $this->getPidPath (), getmypid () )) {
			$this->parentPID = getmypid ();
			$this->saveLog ( 'Daemon ' . $this->shortName . ' pid ' . getmypid () . ' started.' );
			Angel::debug( 'Daemon ' . $this->shortName . ' pid ' . getmypid () . ' started.' );
			while ( ! self::$stopFlag && (memory_get_usage () < $this->memoryLimit) ) {
				$this->trigger ( self::EVENT_BEFORE_ITERATION );
				$jobs = $this->defineJobs ();
				$this->saveLog ( "get job list is :" . var_export ( $jobs, true ) );
				if ($jobs && count ( $jobs )) {
					while ( ($job = $this->defineJobExtractor ( $jobs )) !== null ) {
						$this->saveLog ( "current run job is :{$job}" );
						// if no free workers, wait
						if (count ( static::$currentJobs ) >= $this->maxChildProcesses) {
							$this->saveLog ( 'Reached maximum number of child processes. Waiting...' );
							Angel::debug ( 'Reached maximum number of child processes. Waiting...' );
							while ( count ( static::$currentJobs ) >= $this->maxChildProcesses ) {
								sleep ( 1 );
								pcntl_signal_dispatch ();
							}
							Angel::debug ( 'Free workers found: ' . ($this->maxChildProcesses - count ( static::$currentJobs )) . ' worker(s). Delegate tasks.' );
						}
						pcntl_signal_dispatch ();
						$this->runDaemon ( $job );
					}
					$this->saveLog ( "jobs was finished" );
				} else {
					sleep ( $this->sleep );
				}
				pcntl_signal_dispatch ();
				$this->trigger ( self::EVENT_AFTER_ITERATION );
			}
			if (memory_get_usage () < $this->memoryLimit) {
				Angel::debug ( 'Daemon ' . $this->shortName . ' pid ' . getmypid () . ' used ' . memory_get_usage () . ' bytes on ' . $this->memoryLimit . ' bytes allowed by memory limit' );
			}
			$this->saveLog ( 'Daemon ' . $this->shortClassName () . ' pid ' . getmypid () . ' is stopped.' );
			Angel::info ( 'Daemon ' . $this->shortClassName () . ' pid ' . getmypid () . ' is stopped.' );
			if (file_exists ( $this->getPidPath () )) {
				@unlink ( $this->getPidPath () );
			} else {
				Angel::error( 'Can\'t unlink pid file ' . $this->getPidPath () );
			}
			return self::EXIT_CODE_NORMAL;
		}
		$this->halt ( self::EXIT_CODE_ERROR, 'Can\'t create pid file ' . $this->getPidPath () );
	}
	/**
	 * Completes the process (soft)
	 */
	public static function stop() {
		self::$stopFlag = 1;
	}
	/**
	 * PCNTL signals handler
	 *
	 * @param
	 *        	$signo
	 * @param null $pid        	
	 * @param null $status        	
	 */
	final function signalHandler($signo, $pid = null, $status = null) {
		$this->saveLog ( "get signo:{$signo}" );
		switch ($signo) {
			case SIGTERM :
				static::stop ();
				break;
			case SIGHUP :
				// restart, not implemented
				break;
			case SIGUSR1 :
				// user signal, not implemented
				break;
			case SIGCHLD :
				if (! $pid) {
					$pid = pcntl_waitpid ( - 1, $status, WNOHANG );
				}
				while ( $pid > 0 ) {
					if ($pid && isset ( static::$currentJobs [$pid] )) {
						unset ( static::$currentJobs [$pid] );
					}
					$pid = pcntl_waitpid ( - 1, $status, WNOHANG );
				}
				break;
		}
	}
	/**
	 * Tasks runner
	 *
	 * @param string $job        	
	 * @return boolean
	 */
	final public function runDaemon($job) {
		$this->saveLog ( "run daemon job:{$job}" );
		if ($this->isMultiInstance) {
			$pid = pcntl_fork ();
			if ($pid == - 1) {
				return false;
			} elseif ($pid) {
				static::$currentJobs [$pid] = true;
			} else {
				// child process must die
				$this->trigger ( self::EVENT_BEFORE_JOB );
				if ($this->doJob ( $job )) {
					$this->trigger ( self::EVENT_AFTER_JOB );
					$this->halt ( self::EXIT_CODE_NORMAL );
				} else {
					$this->trigger ( self::EVENT_AFTER_JOB );
					$this->halt ( self::EXIT_CODE_ERROR, 'Child process #' . $pid . ' return error.' );
				}
			}
			return true;
		} else {
			$this->trigger ( self::EVENT_BEFORE_JOB );
			$status = $this->doJob ( $job );
			$this->saveLog ( "daemon job status is:" . var_export ( $status, true ) );
			$this->trigger ( self::EVENT_AFTER_JOB );
			return $status;
		}
	}
	/**
	 * Stop process and show or write message
	 *
	 * @param $code int
	 *        	код завершения -1|0|1
	 * @param $message string
	 *        	сообщение
	 */
	protected function halt($code, $message = null) {
		if ($message !== null) {
			if ($code == self::EXIT_CODE_ERROR) {
				Angel::error ( $message );
				if (! $this->demonize) {
					$message = Console::ansiFormat ( $message, [ 
							Console::FG_RED 
					] );
				}
			} else {
				Angel::debug( $message );
			}
			if (! $this->demonize) {
				$this->writeConsole ( $message );
			}
		}
		if ($code !== - 1) {
			exit ( $code );
		}
	}
	
	/**
	 * Show message in console
	 *
	 * @param
	 *        	$message
	 */
	
	private function writeConsole($message) {
// 		$out = Console::ansiFormat ( '[' . date ( 'd.m.Y H:i:s' ) . '] ', [ 
// 				Console::BOLD 
// 		] );
// 		$this->stdout ( $out . $message . "\n" );
	}
	
	/**
	 * Get classname without namespace
	 *
	 * @return string
	 */
	public function shortClassName() {
		$classname = $this->className ();
		if (preg_match ( '@\\\\([\w]+)$@', $classname, $matches )) {
			$classname = $matches [1];
		}
		return $classname;
	}
	
	public function getPidPath() {
		$dir = Angel::app()->runtimePath.$this->pidDir;
		if (! file_exists ( $dir )) {
			mkdir ( $dir, 0744, true );
		}
		return $dir . DIRECTORY_SEPARATOR . $this->shortName;
	}
	
	protected function saveLog($msg) {
		$filename = Angel::app()->runtimePath.$this->logDir . DIRECTORY_SEPARATOR . $this->shortName . '.log';
		$fp = fopen($filename, "a+");
		fwrite($fp, date("Y-m-d H:i:s")." ".$msg."\n");
		fclose($fp);
	}
	
	public function isProcessRunning($pid) {
		return ! ! posix_getpgid ( $pid );
	}
}

?>