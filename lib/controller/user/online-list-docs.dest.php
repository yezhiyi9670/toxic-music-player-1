<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$arr = ['','',''];
$arr[1] = $_REQUEST['username'];
$arr[2] = $_REQUEST['list_id'];

if(!hasPlaylist($arr[1],$arr[2])) {
	print404('Not Found');
} else if(!is_root() && !readPlaylistData($arr[1],$arr[2])['public'] && uauth_username() != $arr[1]) {
	print401('Private Content');
} else {
	$_GET['_lnk'] = readPlaylistData($arr[1],$arr[2])['playlist'][0]['id'];
	checkPermission('music/getdoc');
	$GLOBALS['listname'] = $arr[1];
	$GLOBALS['listid'] = $arr[2];
	include_header();
	tpl('inner/docs');
	include_footer();
}
