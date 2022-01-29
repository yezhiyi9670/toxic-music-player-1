<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class GarbageCleaner {
	function __construct() {

	}

	function judgeLast($txt,$mode) {
		$txt=substr($txt,strlen($txt)-strlen($mode));
		return $txt==$mode;
	}

	function getLastCleaned() {
		if(file_exists(DATA_PATH . 'cache_clean_time.txt')) {
			return intval(trim(file_get_contents(DATA_PATH . 'cache_clean_time.txt')));
		}
		return 0;
	}

	function needClean() {
		return time() - $this->getLastCleaned() >= 600;
	}

	function cleanIfNeed() {
		if($this->needClean()) {
			$this->clean();
		}
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

		// 标记完成
		file_put_contents(DATA_PATH . 'cache_clean_time.txt', time());
	}
}
