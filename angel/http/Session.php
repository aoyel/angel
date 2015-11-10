<?php

namespace angel\http;

use angel\base\Object;
use angel\Angel;

class Session extends Object {
	public function init() {
		parent::init ();
		$this->start();		
		register_shutdown_function ( [ 
				$this,
				'close' 
		] );
	}
	public function registerSessionCookie() {
		if ($this->getUseCookies () && ! Angel::app ()->isHttpMode ()) {
			$cookieName = ini_get ( "session.name" );
			if (empty ( $_COOKIE ) || $_COOKIE [$cookieName] != $this->getId ()) {
				Angel::app ()->respone->setcookie ( $cookieName, $this->getId () );
			}
		}
	}
	
	public function isStart() {
		return session_status () == PHP_SESSION_ACTIVE;
	}
	
	public function destroy() {
		if ($this->isStart ()) {
			@session_unset ();
			@session_destroy ();
		}
	}
	public function close() {
		if ($this->isStart ()) {
			@session_write_close ();
		}
	}
	public function start() {
		if ($this->isStart ()) {
			return;
		}
		@session_start ();
		if ($this->isStart ()) {
			Angel::info ( 'Session started', __METHOD__ );
		} else {
			$error = error_get_last ();
			$message = isset ( $error ['message'] ) ? $error ['message'] : 'Failed to start session.';
			Angel::error ( $message, __METHOD__ );
		}
	}
	/**
	 * get session id
	 * 
	 * @return string
	 */
	public function getId() {
		return session_id ();
	}
	/**
	 * set session id
	 * 
	 * @param integer $value        	
	 */
	public function setId($value) {
		session_id ( $value );
	}
	/**
	 * reset session id
	 * 
	 * @param string $deleteOldSession        	
	 */
	public function regenerateID($deleteOldSession = false) {
		// add @ to inhibit possible warning due to race condition
		// https://github.com/yiisoft/yii2/pull/1812
		@session_regenerate_id ( $deleteOldSession );
	}
	
	/**
	 * get session name
	 * 
	 * @return string return session name
	 */
	public function getName() {
		return session_name ();
	}
	
	public function getSavePath() {
		return session_save_path ();
	}
	
	public function getUseCookies() {
		if (ini_get ( 'session.use_cookies' ) === '0') {
			return false;
		} elseif (ini_get ( 'session.use_only_cookies' ) === '1') {
			return true;
		} else {
			return null;
		}
	}
	
	public function set($key, $value) {
		$this->start ();
		$_SESSION [$key] = $value;
	}
		
	public function get($key,$defaultValue =null){
		$this->start ();
		return isset($_SESSION [$key])?$_SESSION [$key]:$defaultValue;
	}
	
	public function remove($key) {
		$this->start ();
		if (isset ( $_SESSION [$key] )) {
			$value = $_SESSION [$key];
			unset ( $_SESSION [$key] );
			
			return $value;
		} else {
			return null;
		}
	}
	
	public function removeAll() {
		$this->start ();
		foreach ( array_keys ( $_SESSION ) as $key ) {
			unset ( $_SESSION [$key] );
		}
	}
	
	public function has($key) {
		$this->start ();
		return isset ( $_SESSION [$key] );
	}
}

?>