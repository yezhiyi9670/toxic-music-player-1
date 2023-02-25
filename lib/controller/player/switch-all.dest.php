<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(),false)) {
	print404('No Such Music');
}
checkPermission('music/index', cid());

header("Content-Type: text/plain");
$boundary="\n--------TxmpSwitchDataBoundary--------\n";
echo parseCmpLyric(preSubstr($_GET["_lnk"]));
echo $boundary;
tpl("player/meta");
echo $boundary;
tpl("player/lyric_content");
echo $boundary;
tpl("player/firstrow");
echo $boundary;
tpl("player/thirdrow");
echo $boundary;
tpl("player/thirdrow-n");
echo $boundary;
tpl("player/lyric_overview");
