<?php

namespace angel\web;
use angel\base\Object;

class UrlManage extends Object{
	
	/**
	 * create url
	 * @param array|string $route
	 */
	public function createUrl($route){
		if(is_string($route)){
			return $this->buildUrl($route);
		}else{
			$path = array_pop($route);
			if(empty($route)){
				return $this->buildUrl($path);
			}
			$query = http_build_query($route);
			return $this->buildUrl($path."?".$query);
		}
	}
	
	/**
	 * build Url
	 * @param string $path
	 * @return unknown
	 */
	protected function buildUrl($path){
		return $path;
	}
}

?>