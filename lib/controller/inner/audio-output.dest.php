<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(),true)) {
	print404('No Such Music');
}
checkPermission('music/audio/out', cid());

if(isKuwoId(cid())) {
	global $akCrawler;
	global $akCrawlerInfo;
	remoteEncache(sid($_GET['_lnk']),'K');
	if(substr($_GET['_lnk'],strlen($_GET['_lnk'])-4)!= '.url') {
		header('HTTP/1.1 302 Redirect'); // 将RemotePlay请求导向实际音频地址。不允许缓存。
		header('Location: '.$akCrawler[cid()]->url());
	}
	else {
		header('Content-Type: text/plain');
		echo $akCrawler[cid()]->url();
	}
	exit;
}
$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/song");
file_put_out($fn);
