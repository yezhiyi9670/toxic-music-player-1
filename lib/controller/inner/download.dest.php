<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(),true)) {
	print404('No Such Music');
}
checkPermission('music/audio/dl');

$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/song");
$c=json_decode(parseCmpLyric(preSubstr($_GET["_lnk"])),true);
file_put_out($fn,true,preSubstr($_GET["_lnk"])." ".$c['meta']['N'].
	substr($fn,strrpos($fn,'.'))
);
