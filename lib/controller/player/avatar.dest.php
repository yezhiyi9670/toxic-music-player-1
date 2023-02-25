<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(),false)) {
	print404('No Such Music');
}
// 不访问主页，不会用到摘要图像。
checkPermission('music/index', cid());

// 酷我音乐
if(isKuwoId(cid())) {
	global $akCrawler;
	global $akCrawlerInfo;
	remoteEncache(cid(),'K');
	if(!$akCrawler[cid()]->success) {
		return false;
	} else {
		header('Content-Type: image/jpg');
		header("Cache-Control: public max-age=432000");
		header("Last-Modified: " . gmdate('D, d M Y H:i:s',$akCrawler[cid()]->cached));
		echo ex_url_get_contents($akCrawler[cid()]->picUrl());
		return true;
	}
} else {
	$path = getPicturePath(FILES . cid() . '/avatar');
	if(!file_exists($path)) {
		return false;
	} else {
		file_put_out($path);
	}
}
