<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class GarbageCleaner {
	function __construct() {

	}

	function judgeLast($txt,$mode) {
		$txt=substr($txt,strlen($txt)-strlen($mode));
		return $txt==$mode;
	}

	function clean() {
		//清除已过期Cache
		$menu=dir_list(REMOTE_CACHE);
		foreach($menu as $item) {
			$fname=REMOTE_CACHE.$item;
			if($this->judgeLast($fname,'__ListGen.xml')) if(time()-filemtime($fname) > _CT('temp_expire')) {
				unlink($fname);
			}
			else if(time()-filemtime($fname) > _CT('cache_expire')) {
				unlink($fname);
			}
		}
	}
}
