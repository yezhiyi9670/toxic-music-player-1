<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

/**
 * @deprecated
 */
function is_root_session($flag=true) {
	if(isset($_GET['deauth']) && $flag) return false;
	if(!isset($_COOKIE['X-'.APP_PREFIX.'-session']) || !isset($_COOKIE['X-'.APP_PREFIX.'-token'])) return false;
	$dt=json_decode(file_get_contents(DATA_PATH.'session.json'),true);
	if(isset($dt[$_COOKIE['X-'.APP_PREFIX.'-session']]) && $dt[$_COOKIE['X-'.APP_PREFIX.'-session']]==$_COOKIE['X-'.APP_PREFIX.'-token']) return true;
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
	if(isset($dt[$_COOKIE['X-'.APP_PREFIX.'-session']]))
		unset($dt[$_COOKIE['X-'.APP_PREFIX.'-session']]);
	file_put_contents(DATA_PATH.'session.json',encode_data($dt));

	setcookie('X-'.APP_PREFIX.'-session','',time()+60*60*24*30,"/");
	setcookie('X-'.APP_PREFIX.'-token','',time()+60*60*24*30,"/");
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
	file_put_contents(DATA_PATH.'session.json',encode_data($dt));

	setcookie('X-'.APP_PREFIX.'-session',$sess,time()+60*60*24*30,"/");
	setcookie('X-'.APP_PREFIX.'-token',$token,time()+60*60*24*30,"/");

	return true;
}
