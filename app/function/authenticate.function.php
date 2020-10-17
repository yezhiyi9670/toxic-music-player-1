<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

/**
 * @deprecated
 */
function is_root_session($flag=true) {
	if(isset($_GET['deauth']) && $flag) return false;
	if(!isset($_COOKIE['X-txmp-session']) || !isset($_COOKIE['X-txmp-token'])) return false;
	$dt=json_decode(file_get_contents(DATA_PATH.'session.json'),true);
	if(isset($dt[$_COOKIE['X-txmp-session']]) && $dt[$_COOKIE['X-txmp-session']]==$_COOKIE['X-txmp-token']) return true;
	return false;
}

function is_root($flag = true) {
	if(isset($_GET['deauth']) && $flag) return false;
	//$ret = is_root_session();

	if($GLOBALS['root_li']) {
		if($GLOBALS['root_li'] == '+') return true;
		return false;
	}

	$username = uauth_username();
	// 如果当前 uauth 用户是 root，那么强制执行登录
	if($username && uauth_get($username)['enabled'] === 3) {
		// root_login('',true);
		$GLOBALS['root_li'] = '+';
		return true;
	}
	$GLOBALS['root_li'] = '-';
	return false;
}

/**
 * @deprecated
 */
function root_logout() {
	if(!is_root()) return;
	$dt=json_decode(file_get_contents(DATA_PATH.'session.json'),true);
	if(isset($dt[$_COOKIE['X-txmp-session']]))
		unset($dt[$_COOKIE['X-txmp-session']]);
	file_put_contents(DATA_PATH.'session.json',json_encode($dt,JSON_PRETTY_PRINT));

	setcookie("X-txmp-session",'',time()+60*60*24*30,"/");
	setcookie("X-txmp-token",'',time()+60*60*24*30,"/");
}

/**
 * @deprecated
 */
function root_login($code,$forcelogin = false) {
	if(!$forcelogin && is_root()) return true;

	if(!in_array($code,json_decode(file_get_contents(DATA_PATH.'passwds.json'),true)) && !$forcelogin) return false;

	$sess=md5(rand());
	$token=md5(rand());

	$dt=json_decode(file_get_contents(DATA_PATH.'session.json'),true);
	$dt[$sess]=$token;
	file_put_contents(DATA_PATH.'session.json',json_encode($dt,JSON_PRETTY_PRINT));

	setcookie("X-txmp-session",$sess,time()+60*60*24*30,"/");
	setcookie("X-txmp-token",$token,time()+60*60*24*30,"/");

	return true;
}
