<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

$uname = uauth_username();

if(!isset($_POST['isSubmit'])){
	echo '不可接受的请求格式';
}
else if(!$uname) {
	echo '用户没有登录。';
} else {
	if(strlen($_POST['str']) > 51200) {
		echo '歌单大小超过 50KB，不支持保存。请削减信息。';
		exit;
	}

	$listdata = [];
	$isCsv = !(!isset($_POST['isCsv']) || $_POST['isCsv'] != 'yes');
	if(!$isCsv) {
		$listdata = json_decode($_POST['str'],true);
	} else {
		$listdata = plcsv_decode($_POST['str']);
	}
	if(!$listdata) {
		echo '数据不合法。无法解码数据。';
		exit;
	}

	$flag = true;
	if(!isset($listdata['playlist']) || !isset($listdata['transform']) || !isset($listdata['title']) || !isset($listdata['public'])) {
		$flag = false;
	} else {
		if(
			!isset($listdata['transform']['pick']) ||
			!isset($listdata['transform']['random_shuffle']) ||
			!isset($listdata['transform']['constraints']) ||
			!isset($listdata['transform']['constraints']['comparator']) ||
			!isset($listdata['transform']['constraints']['multiplier']) ||
			!isset($listdata['transform']['constraints']['delta']) ||
			!isset($listdata['transform']['termination']) ||
			!isset($listdata['transform']['constraints2']) ||
			!isset($listdata['transform']['constraints2']['comparator']) ||
			!isset($listdata['transform']['constraints2']['multiplier']) ||
			!isset($listdata['transform']['constraints2']['delta'])
		) {
			$flag = false;
		} else {
			if(count($listdata['playlist']) == 0) {
				echo '歌单中没有条目。';
				exit;
			}
			foreach($listdata['playlist'] as $item) {
				if(!isset($item['id']) || !isset($item['rating']) || (isset($item['canonical']) &&
					!preg_match('/^(\w+)$/',$item['canonical'] || preSubstr($item['id'],'_') == 'AK')
				)) {
					$flag = false;
					break;
				}
			}
		}
	}

	if(!$flag) {
		echo '歌单数据不合法。';
		exit;
	}

	// echo '施工中。';
	$ak = [];
	preg_match('/^playlist\/save-list\/(\d+)$/',$_GET['_lnk'],$ak);
	$id = intval($ak[1]);

	if($id == 0) {
		if(count(uauth_list_data($uname,'playlist')) >= _CT('user_playlist_quota')) {
			echo '你已经超出数量限制。';
			exit;
		}
		$id = uauth_request_id($uname,'playlist');
	} else {}

	// echo '歌单编号：'.$id.'（未保存）';

	$jsonfid = 'user/' . $uname . '/playlist/' . $id;
	$csvfid = 'user/' . $uname . '/playlist/' . $id . '-csv';
	if($_POST['delete'] == 'true') {
		wait_file($csvfid);
		if(file_exists(USER_DATA.$uname.'/playlist/'.$id.'.csv')) {
			unlink(USER_DATA.$uname.'/playlist/'.$id.'.csv');
		}
		wait_file($jsonfid);
		if(file_exists(USER_DATA.$uname.'/playlist/'.$id.'.json')) {
			unlink(USER_DATA.$uname.'/playlist/'.$id.'.json');
		}
	} else if(!$isCsv) {
		wait_file($csvfid);
		if(file_exists(USER_DATA.$uname.'/playlist/'.$id.'.csv')) {
			unlink(USER_DATA.$uname.'/playlist/'.$id.'.csv');
		}

		wait_file($jsonfid);
		lock_file($jsonfid);

		file_put_contents(USER_DATA.$uname.'/playlist/'.$id.'.json',
			json_encode($listdata,JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE)
		);

		unlock_file($jsonfid);
	} else {
		wait_file($jsonfid);
		if(file_exists(USER_DATA.$uname.'/playlist/'.$id.'.json')) {
			unlink(USER_DATA.$uname.'/playlist/'.$id.'.json');
		}

		wait_file($csvfid);
		lock_file($csvfid);

		file_put_contents(USER_DATA.$uname.'/playlist/'.$id.'.csv',
			$_POST['str']
		);

		unlock_file($csvfid);
	}

	echo '+'.$id;
}
