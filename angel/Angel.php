<?php

namespace angel;

use angel\base\Logger;

if(!defined("APP_DEBUG"))
	define("APP_DEBUG", true);

/**
 *
 * @author smile
 */
class Angel{
	
	/**
	 *
	 * @var \angel\base\Application
	 */
	public static $app;	
	/**
	 *
	 * @var array
	 */
	public static $namespaceMap = [];
	
	/**
	 *
	 * @return \angel\base\Application
	 */
	public static function app() {
		return self::$app;
	}
	
	public static function addNamespaceMap($namespace, $path) {
		self::$namespaceMap [$namespace] = $path;
	}
	/**
	 * public static function createObject($type, array $params = [])
	 * {
	 * if (is_string($type)) {
	 * return static::$container->get($type, $params);
	 * } elseif (is_array($type) && isset($type['class'])) {
	 * $class = $type['class'];
	 * unset($type['class']);
	 * return static::$container->get($class, $params, $type);
	 * } elseif (is_callable($type, true)) {
	 * return call_user_func($type, $params);
	 * } elseif (is_array($type)) {
	 * throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
	 * } else {
	 * throw new InvalidConfigException("Unsupported configuration type: " . gettype($type));
	 * }
	 * }
	 */
	public static function createObject($obj, $args = []) {
		if (is_string ( $obj )) {
			return self::createInstance ( $obj, $args );
		} else if (is_array ( $obj ) && isset ( $obj ['class'] )) {
			$instance = self::createInstance ( $obj ['class'], $args );
			unset ( $obj ['class'] );
			foreach ( $obj as $k => $v )
				$instance->$k = $v;
			return $instance;
		}
		return false;
	}
	protected static function createInstance($class, $args = []) {
		if (! class_exists ( $class ))
			return null;
		
		$re_args = [ ];
		if (method_exists ( $class, "__construct" )) {
			$refMethod = new \ReflectionMethod ( $class, '__construct' );
			$params = $refMethod->getParameters ();
			foreach ( $params as $key => $param ) {
				if ($param->isPassedByReference ()) {
					$re_args [$key] = &$args [$key];
				} else {
					$re_args [$key] = $args [$key];
				}
			}
		}
		$refClass = new \ReflectionClass ( $class );
		return $refClass->newInstanceArgs ( ( array ) $re_args );
	}
	
	
	
	public static function autoload($className) {
		$prefix = strstr ( $className, '\\', true );
		if (in_array ( $prefix, array_keys ( self::$namespaceMap ) )) {
			$className = str_replace ( $prefix, self::$namespaceMap [$prefix], $className );
			$fileName = str_replace ( "\\", DIRECTORY_SEPARATOR, $className ) . ".php";
			if (file_exists ( $fileName )) {
				require $fileName;
				return true;
			}
		}
		$className = ltrim ( $className, '\\' );
		$fileName = '';
		$namespace = '';
		if (($lastNsPos = strripos ( $className, '\\' )) !== false) {
			$namespace = substr ( $className, 0, $lastNsPos );
			$className = substr ( $className, $lastNsPos + 1 );
			$fileName = str_replace ( '\\', DIRECTORY_SEPARATOR, $namespace ) . DIRECTORY_SEPARATOR;
		}
		$fileName = __DIR__ . DIRECTORY_SEPARATOR . $fileName . $className . '.php';
		if (file_exists ( $fileName )) {
			require $fileName;
			return true;
		}
	}
	
	public static function debug($msg,$category='application'){
		self::log($msg,Logger::LEVEL_DEBUG,$category);
	}
	
	public static function info($msg,$category='application'){
		self::log($msg,Logger::LEVEL_INFO,$category);
	}
	
	public static function warning($msg,$category='application'){
		self::log($msg,Logger::LEVEL_WARNING,$category);
	}
	
	public static function error($msg,$category='application'){
		self::log($msg,Logger::LEVEL_ERROR,$category);
	}
		
	private static function log($msg,$level,$category){
		self::$app->log->log($msg,$level,$category);
	}
}

?>