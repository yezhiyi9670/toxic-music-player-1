<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

function is_root($flag = true) {
	if(isset($_GET['deauth']) && $flag) return false;

	if($GLOBALS['root_li']) {
		if($GLOBALS['root_li'] == '+') return true;
		return false;
	}

	$username = uauth_username();
	// 如果当前 uauth 用户是 root，那么强制执行登录
	if($username && uauth_get($username)['enabled'] === 3) {
		$GLOBALS['root_li'] = '+';
		return true;
	}
	$GLOBALS['root_li'] = '-';
	return false;
}
