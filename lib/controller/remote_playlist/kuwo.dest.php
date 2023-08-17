<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$GLOBALS['remote_playlist_id'] = $_REQUEST['list_id'];

$_GET['_lnk'] = '$FFA000';
$_GET['return'] = true;
$_GET['key'] = '^' . $GLOBALS['remote_playlist_id'];
$_GET['pageid'] = '1';
$GLOBALS['remote_playlist'] = kuwoSearchSong();
if($GLOBALS['remote_playlist']['code'] ?? 404 != 200) {
	print404('Not Found');
} else {
	include_header();
	tpl('remote_playlist/kuwo');
	include_footer();
}
