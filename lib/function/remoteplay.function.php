<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// 获取远程歌曲信息并存入内存，等待调用
function remoteEncache($u,$g,$flag = false) {
	if($g == 'K') {
		global $akCrawler;
		global $akCrawlerInfo;
		if(!isset($akCrawler[$u])) $akCrawler[$u]=new KuwoCrawler();
		if($akCrawler[$u]->cache==array() || $flag) $akCrawler[$u]->enCache(R($u),$flag);
	} else {
		print404('Undefined RP Type');
	}
}


