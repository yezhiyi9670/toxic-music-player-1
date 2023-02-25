<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$arr = ['','',''];
$arr[1] = $_REQUEST['username'];
$arr[2] = $_REQUEST['list_id'];

if(!hasPlaylist($arr[1],$arr[2])) {
	print404('Not Found');
} else if(!is_root() && !readPlaylistData($arr[1],$arr[2])['public'] && uauth_username() != $arr[1]) {
	print401('Private Content');
} else {
	$data = readPlaylistData($arr[1],$arr[2]);
	$_GET['_lnk'] = $data['playlist'][0]['id'];
	checkPermission('music/index', cid());
	$GLOBALS['listname'] = $arr[1];
	$GLOBALS['listid'] = $arr[2];
	if(!isset($_GET['raw'])) {
		include_header();
		tpl('player/index');
		include_footer();
	} else if(isset($_GET['json'])) {
		header('Content-Type: application/json');
		if(isset($_GET['include-meta'])) {
			foreach($data['playlist'] as $k => $item) {
				$id = $item['id'];
				$data['playlist'][$k]['meta'] = GSM($id);
				if(isValidMusic($item['id'])) {
					$data['playlist'][$k]['modified'] = modifiedTime($id);
				} else {
					$data['playlist'][$k]['modified'] = -1;
				}
			}
		}
		echo encode_data($data);
	} else {
		header('Content-Type: text/plain');
		echo readPlaylistData($arr[1],$arr[2],true);
	}
}
