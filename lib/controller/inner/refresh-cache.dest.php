<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(), false, true)) {
	print404('Not Found');
}
if(!isKuwoId(cid())) {
	print404('Not Found');
}
remoteEncache(cid(),'K',true);
global $akCrawler;
if($akCrawler[cid()] -> success) echo 'Success';
else echo 'Failed';
