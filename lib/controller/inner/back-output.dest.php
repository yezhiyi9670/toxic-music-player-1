<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(),true)) {
	print404('No Such Music');
}
checkPermission('music/audio/out');

$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/back");
if($fn) {
	file_put_out($fn);
}
else print404('Not Uploaded');

