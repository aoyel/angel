<?php

namespace angel\base;

use angel\Angel;

class View extends Object {
	public $title;
	public $params = [];
	public $content;
	public $suffix = "php";
	public $viewsFolder = "views";
	public $layoutsFolder = "layouts";
	public $js = [];
	public $css = [ ];
	public $jsBlock = [ ];
	
	public function init() {
		parent::init ();
	}
	
	public function beforeRender($viewFile, $params = []) {
		
		return TRUE;
	}
	
	public function afterRender($viewFile, $param = []) {
		
	}
	
	protected function getViewFile($view = null) {
		
		$view = $view ? $view : Angel::app ()->controller->action;
		return implode ( DIRECTORY_SEPARATOR, [ 
				Angel::app ()->applicationPath,
				$this->viewsFolder,
				strtolower ( Angel::app ()->controller->id ),
				strtolower ( $view . "." . $this->suffix ) 
		] );
	}
	protected function getLayoutsFile($layout) {
		return implode ( DIRECTORY_SEPARATOR, [ 
				Angel::app ()->applicationPath,
				$this->viewsFolder,
				$this->layoutsFolder,
				strtolower ( $layout . "." . $this->suffix ) 
		] );
	}
		
	/**
	 * render view file
	 *
	 * @param string $viewFile        	
	 * @param array $params        	
	 */
	public function renderFile($filename, $params = []) {
		if ($this->beforeRender ( $filename, $params )) {
			ob_start ();
			ob_implicit_flush ( false );
			extract ( $params, EXTR_OVERWRITE );
			require ($filename);
			$this->afterRender ( $filename, $params );
			return ob_get_clean ();
		}
	}
	
	public function render($view=null, $params = [], $layout = "default") {
		$filename = $this->getViewFile ( $view );
		$this->content = $this->renderFile ( $filename, $params );
		if ($layout === false)
			return $this->content;
		$layoutFile = $this->getLayoutsFile ( $layout );
		ob_start ();
		ob_implicit_flush ( false );
		require ($layoutFile);
		return ob_get_clean ();
	}
	
	public function beginHead() {
	}
	
	public function endHead() {
	}
	
	public function beginContent() {
	}
	
	public function endContent() {
		$content = "";
		if (! empty ( $this->js )) {
			foreach ( $this->js as $row )
				$content .= "<script src={$row}></script>";
		}
		return $content;
	}
}

?>