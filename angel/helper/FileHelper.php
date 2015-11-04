<?php

namespace angel\helper;

use angel\base\Exception;

class FileHelper {
	public static function createDirectory($path, $mode = 0775, $recursive = true) {
		if (is_dir ( $path )) {
			return true;
		}
		$parentDir = dirname ( $path );
		if ($recursive && ! is_dir ( $parentDir )) {
			static::createDirectory ( $parentDir, $mode, true );
		}
		try {
			$result = mkdir ( $path, $mode );
			chmod ( $path, $mode );
		} catch ( \Exception $e ) {
			throw new Exception ( "Failed to create directory,", $e->getCode (), $e );
		}
		return $result;
	}
	
	public static function getExtension($path) {
		return pathinfo ( $path, PATHINFO_EXTENSION );
	}
	
	
	public static function getMimeType($file, $magicFile = null, $checkExtension = true) {
		if (function_exists ( 'finfo_open' )) {
			$options = defined ( 'FILEINFO_MIME_TYPE' ) ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
			$info = $magicFile === null ? finfo_open ( $options ) : finfo_open ( $options, $magicFile );
			
			if ($info && ($result = finfo_file ( $info, $file )) !== false)
				return $result;
		}
		
		if (function_exists ( 'mime_content_type' ) && ($result = mime_content_type ( $file )) !== false)
			return $result;
		
		return $checkExtension ? self::getMimeTypeByExtension ( $file ) : null;
	}
	
	
	public static function getMimeTypeByExtension($file, $magicFile = null) {
		static $extensions, $customExtensions = array ();
		if ($magicFile === null && $extensions === null)
			$extensions = require (ANGEL_PATH . DIRECTORY_SEPARATOR . "utils" . DIRECTORY_SEPARATOR . 'mimeTypes.php');
		elseif ($magicFile !== null && ! isset ( $customExtensions [$magicFile] ))
			$customExtensions [$magicFile] = require ($magicFile);
		if (($ext = self::getExtension ( $file )) !== '') {
			$ext = strtolower ( $ext );
			if ($magicFile === null && isset ( $extensions [$ext] ))
				return $extensions [$ext];
			elseif ($magicFile !== null && isset ( $customExtensions [$magicFile] [$ext] ))
				return $customExtensions [$magicFile] [$ext];
		}
		return null;
	}
}

?>