<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class DynamicRouter {
	private $_url = "";

	function __construct($_url){
		$this->url = $_url;
	}

	public function route(){
		$fn = $this -> url;

		if(strpos($fn,'..') !== false) {
			return false;
		}

		$tail = "-colored.css";
		if(substr($fn,strlen($fn)-strlen($tail)) != $tail) {
			return false;
		}
		
		$fs_file = STATICS . $fn . '.php';
		if(!file_exists($fs_file)) {
			return false;
		}
		require($fs_file);
		return true;
	}
}
