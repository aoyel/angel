<?php

namespace angel\helper;

use angel\base\Object;

class UploadHelper extends Object {
	protected $options;
	protected $error;
	protected $error_messages = array (
			'post_max_size' => 'The uploaded file exceeds the post_max_size directive in php.ini',
			'max_file_size' => 'File is too big',
			'min_file_size' => 'File is too small',
			'accept_file_types' => 'Filetype not allowed',
			'max_number_of_files' => 'Maximum number of files exceeded',
			'max_width' => 'Image exceeds maximum width',
			'min_width' => 'Image requires a minimum width',
			'max_height' => 'Image exceeds maximum height',
			'min_height' => 'Image requires a minimum height',
			'abort' => 'File upload aborted',
			'image_resize' => 'Failed to resize image',
			'access denied' => 'access denied' 
	);
	function __construct($options) {
		$_default = [ 
				"basePath" => __DIR__,
				"savePath" => __DIR__,
				"baseUrl" => "",
				"allowExt" => [ 
						'gif',
						'jpg',
						'jpeg',
						'bmp',
						'png',
						'swf',
						'stl' 
				],
				"maxSize" => $this->asByts ( $this->getMaxUploadSize () ),
				"displayPath" => false 
		];
		
		$this->options = array_merge ( $_default, $options );
	}
	
	/**
	 * run process to process file
	 * 
	 * @param string $name        	
	 */
	public function run($name = null) {
		$file = null;
		if (! empty ( $name )) {
			if (isset ( $_FILES [$name] )) {
				$file = [ ];
				$file [$name] = $_FILES [$name];
			}
		} else {
			$file = $_FILES;
		}
		if (empty ( $file ))
			return false;
		return $this->handelFile ( $file );
	}
	
	/**
	 * 处理文件
	 */
	protected function handelFile($files) {
		$result = [ ];
		foreach ( $files as $k => $row ) {
			$res = $this->processFile ( $row );
			if ($res)
				$result [$k] = $res;
		}
		return $result;
	}
	
	/**
	 * 处理单个文件
	 */
	protected function processFile($file) {
		/**
		 * 文件数组
		 */
		$result = [ ];
		if (is_array ( $file ['tmp_name'] )) {
			foreach ( $file ['tmp_name'] as $k => $v ) {
				$result [] = $this->moveFile ( $file ['tmp_name'] [$k], $file ['name'] [$k], $file ['size'] [$k], $file ['type'] [$k] );
			}
		} else {
			$result [] = $this->moveFile ( $file ['tmp_name'], $file ['name'], $file ['size'], $file ['type'] );
		}
		return $result;
	}
	
	/**
	 *
	 * @param string $filepath        	
	 * @param string $filename        	
	 * @param string $size        	
	 * @param string $type        	
	 * @param string $error        	
	 */
	protected function moveFile($filepath, $filename, $size, $type) {
		$result = [ ];
		if (! $this->validate ( $filepath, $filename, $size )) {
			$result ['error'] = $this->getError ();
			goto __end;
		}
		/**
		 * 检测存储路劲
		 */
		$dir = $this->options ['savePath'];
		
		if (! is_writable ( $dir )) {
			$this->setError ( $this->error_messages ['access denied'] );
			$result ['error'] = $this->getError ();
			goto __end;
		}
		
		if (! is_dir ( $dir )) {
			$this->mkdir ( $dir );
		}
		
		/**
		 * 新文件的路劲
		 */
		$savepath = $dir . DIRECTORY_SEPARATOR . uniqid () . "." . $this->getFileExtension ( $filename );
		
		if (move_uploaded_file ( $filepath, $savepath )) {
			$result ['filesize'] = $size;
			$result ['filename'] = $filename;
			if ($this->options ['displayPath'])
				$result ['savepath'] = $savepath;
			$result ['type'] = $type;
			$result ['fileurl'] = $this->getFileUrl ( $savepath );
		}
		__end:
		return $result;
	}
	
	/**
	 * 判断文件是否有效
	 */
	public function validate($filepath, $filename, $size) {
		if ($size > $this->options ['maxSize']) {
			$this->setError ( $this->error_messages ['post_max_size'] );
			return false;
		}
		
		$ext = $this->getFileExtension ( $filename );
		
		if (! in_array ( $ext, $this->options ['allowExt'] )) {
			$this->setError ( $this->error_messages ['accept_file_types'] );
			return false;
		}
		if (in_array ( $ext, array (
				'gif',
				'jpg',
				'jpeg',
				'bmp',
				'png' 
		) )) {
			$imginfo = getimagesize ( $filepath );
			if (empty ( $imginfo ) || ($ext == 'gif' && empty ( $imginfo ['bits'] ))) {
				$this->setError ( $this->error_messages ['accept_file_types'] );
				return false;
			}
		}
		return true;
	}
	public function getFileUrl($savepath) {
		return str_replace ( $this->options ['basePath'], $this->options ['baseUrl'], $savepath );
	}
	public function mkdir($dir, $mode = 0777) {
		if (is_dir ( $dir ) || @mkdir ( $dir, $mode ))
			return true;
		if (! $this->mkdir ( dirname ( $dir ), $mode ))
			return false;
		return @mkdir ( $dir, $mode );
	}
	protected function getFileExtension($filename) {
		return strtolower ( pathinfo ( $filename, PATHINFO_EXTENSION ) );
	}
	function asByts($val) {
		$val = trim ( $val );
		$last = strtolower ( $val [strlen ( $val ) - 1] );
		switch ($last) {
			case 'g' :
				$val *= 1024;
			case 'm' :
				$val *= 1024;
			case 'k' :
				$val *= 1024;
		}
		return $this->fix_integer_overflow ( $val );
	}
	protected function fix_integer_overflow($size) {
		if ($size < 0) {
			$size += 2.0 * (PHP_INT_MAX + 1);
		}
		return $size;
	}
	protected function getMaxUploadSize() {
		return ini_get ( "upload_max_filesize" );
	}
	protected function setError($error) {
		$this->error = $error;
	}
	public function getError() {
		return $this->error;
	}
}

?>