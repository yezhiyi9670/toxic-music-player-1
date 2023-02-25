<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

@ignore_user_abort(true);
$cleaner = new GarbageCleaner();
header('Content-Type: text/plain');
if($cleaner->needClean()) {
	$cleaner->clean();
	echo LNG('ui.trash_cleaned');
} else {
	echo LNG('ui.trash_no_clean');
}
return true;
