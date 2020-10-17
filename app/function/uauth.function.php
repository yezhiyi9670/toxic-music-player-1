<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

///// 用户数据文件访问量较大，使用文件锁定方法，防止一个文件同时被多个进程写入 /////

function lock_file($n) {
	file_put_contents(DATA_PATH.$n.'.writing.lock','');
}

function unlock_file($n) {
	unlink(DATA_PATH.$n.'.writing.lock');
}

function wait_file($n) {
	while(file_exists(DATA_PATH.$n.'.writing.lock')) {
		// 文件被占用超过20秒，多半是废了，强行解锁
		if(time() - filemtime(DATA_PATH.$n.'.writing.lock') > 20) {
			unlock_file($n);
			break;
		}
		sleep(0.8);
	}
}

///// 数据获取与写入接口 /////

function check_data($n) {
	$fname = DATA_PATH.$n.'.json';
	if(!file_exists($fname)) file_put_contents($fname,'{}');
}

function read_data($n) {
	$fname = DATA_PATH.$n.'.json';
	check_data($n);
	return json_decode(file_get_contents($fname),true);
}

function write_data($n,$t) {
	$fname = DATA_PATH.$n.'.json';
	check_data($n);
	file_put_contents($fname,json_encode($t,JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE+JSON_UNESCAPED_SLASHES));
}

// ----- 以上无需namespace，因为都是通用接口 ----- //


///// 用户登录 /////

/**
 * 判断浏览器是否作为用户登录。如果是，获取其用户名。
 * @return  string  username    当前登录的用户名。未登录返回的是一个假值（空字符串）
 */
function uauth_username() {
	if($GLOBALS['uauth_username']) {
		if($GLOBALS['uauth_username'] == '-') return '';
		else return $GLOBALS['uauth_username'];
	}

	wait_file('uauth_session');
	lock_file('uauth_session');
	wait_file('uauth_users');

	$ulist = read_data('uauth_users');
	$t = read_data('uauth_session');

	// 清理已过期
	$rmlist = [];
	foreach($t as $k=>$v) {
		if(time() - $v['time'] > 60*60*24*30 || !isset($v['lastTime']) || time() - $v['lastTime'] > 2*86400) $rmlist[count($rmlist)] = $k;
	}
	foreach($rmlist as $k) {
		unset($t[$k]);
	}

	if(!isset($_COOKIE['X-txmp-uauth-session']) || !isset($_COOKIE['X-txmp-uauth-token'])) {
		write_data('uauth_session',$t);unlock_file('uauth_session',$t);
		$GLOBALS['uauth_username'] = '-';return '';
	}
	$sess=$_COOKIE['X-txmp-uauth-session'];
	$token=$_COOKIE['X-txmp-uauth-token'];
	if(!isset($t[$sess])) {
		write_data('uauth_session',$t);unlock_file('uauth_session',$t);
		$GLOBALS['uauth_username'] = '-';return '';
	}
	if($t[$sess]['token']!=$token) {
		write_data('uauth_session',$t);unlock_file('uauth_session',$t);
		$GLOBALS['uauth_username'] = '-';return '';
	}
	if(!isset($ulist[$t[$sess]['name']])) {
		write_data('uauth_session',$t);unlock_file('uauth_session',$t);
		$GLOBALS['uauth_username'] = '-';return '';
	} // 用户不存在
	if($ulist[$t[$sess]['name']]['pass'] != $t[$sess]['pass']) {
		write_data('uauth_session',$t);unlock_file('uauth_session',$t);
		$GLOBALS['uauth_username'] = '-';return '';
	} // 密码无法匹配
	if($ulist[$t[$sess]['name']]['enabled'] == 0) {
		write_data('uauth_session',$t);unlock_file('uauth_session',$t);
		$GLOBALS['uauth_username'] = '-';return '';
	} // 已查封
	if(time() - $t[$sess]['time'] > 60*60*24*30) {
		write_data('uauth_session',$t);unlock_file('uauth_session',$t);
		$GLOBALS['uauth_username'] = '-';return '';
	} // 过期

	$t[$sess]['lastTime'] = time();

	write_data('uauth_session',$t);unlock_file('uauth_session',$t);

	$GLOBALS['uauth_username'] = $t[$sess]['name'];
	return $t[$sess]['name'];
}

/**
 * 退出当前已经登录的用户
 * @return  bool    success     退出操作是否成功
 */
function uauth_logout() {
	wait_file('uauth_session');
	lock_file('uauth_session');
	$t = read_data('uauth_session');
	if(!isset($_COOKIE['X-txmp-uauth-session']) || !isset($_COOKIE['X-txmp-uauth-token'])) {
		unlock_file('uauth_session');
		return false;
	}
	$sess=$_COOKIE['X-txmp-uauth-session'];
	$token=$_COOKIE['X-txmp-uauth-token'];
	if(!isset($t[$sess])) {
		unlock_file('uauth_session');
		return false;
	}
	if($t[$sess]['token']!=$token) {
		unlock_file('uauth_session');
		return false;
	}
	$ret = (!!$t[$sess]['name']);
	unset($t[$sess]);
	write_data('uauth_session',$t);
	unlock_file('uauth_session');
	return $ret;
}

/**
 * 执行登录
 * @param   string  username    登录用户名
 * @param   string  password    登录密码
 * @return  string  status      登录状态
 *
 * 登录状态：
 * success: 成功
 * nxuser: 用户不存在
 * passwrong: 密码错误
 * ban: 用户被禁用
 * loggedin: 已经登录过了
 */
function uauth_login($name,$pass,$flag = false) {
	if(!!uauth_username()) return 'loggedin';

	wait_file('uauth_users');
	wait_file('uauth_session');
	lock_file('uauth_session');
	$ulist = read_data('uauth_users');
	$slist = read_data('uauth_session');

	if(!isset($ulist[$name]) || $ulist[$name]['name']!=$name) {
		unlock_file('uauth_session');
		return 'nxuser';
	}

	if($ulist[$name]['pass'] != md5($pass) && !$flag) {
		unlock_file('uauth_session');
		return 'passwrong';
	}

	if($ulist[$name]['enabled'] == 0) {
		unlock_file('uauth_session');
		return 'ban';
	}

	$sess=md5(rand());
	$token=md5(rand());

	$slist[$sess] = [
		'token' => $token,
		'name'  => $name,
		'ua'    => $_SERVER['HTTP_USER_AGENT'],
		'pass'  => $ulist[$name]['pass'],
		'time'  => time(),
		'lastTime'=> time(),
	];

	setcookie("X-txmp-uauth-session",$sess,time()+60*60*24*30,"/");
	setcookie("X-txmp-uauth-token",$token,time()+60*60*24*30,"/");

	write_data('uauth_session',$slist);
	unlock_file('uauth_session');

	return 'success';
}

/**
 * 执行注册
 * @param   string  username    用户名
 * @param   string  password    密码
 * @param   bool    flag        是否是管理员的注册操作（将会忽略IP限制和登录状态限制）
 * @param   string  ip          注册者IP，如果未指定，取访问者IP
 * @return  string  status      注册状态
 *
 * 注册状态：
 * success: 成功
 * exist: 用户名已经占用
 * illegal: 用户名不合法
 * loggedin: 已经处于登录状态
 * limit: 当前IP注册数已经到达限制
 */
function uauth_register($name,$pass,$flag=false,$ip='none') {
	if($ip=='none') $ip = $_SERVER['REMOTE_ADDR'];

	if(!$flag && !!uauth_username()) return 'loggedin';
	if(!preg_match('/^(\w+)$/',$name)) return 'illegal';

	wait_file('uauth_ip_limit');
	lock_file('uauth_ip_limit');
	wait_file('uauth_users');
	lock_file('uauth_users');

	$ulist = read_data('uauth_users');
	$iplist = read_data('uauth_ip_limit');

	if(isset($ulist[$name])) {
		unlock_file('uauth_ip_limit');
		unlock_file('uauth_users');
		return 'exist';
	}

	$cnt = 0;
	if(isset($iplist[$ip])) $cnt = $iplist[$ip];
	if($cnt >= _CT('ip_reg_limit') && !$flag) {
		unlock_file('uauth_ip_limit');
		unlock_file('uauth_users');
		return 'limit';
	}

	$iplist[$ip] = $cnt+1;
	$ulist[$name] = [
		"name"  => $name,
		"pass"  => md5($pass),
		"enabled"=> true,
		"ip"    => $ip,
	];

	mkdir(USER_DATA.$name.'/');

	write_data('uauth_ip_limit',$iplist);
	write_data('uauth_users',$ulist);

	unlock_file('uauth_ip_limit');
	unlock_file('uauth_users');

	return 'success';
}

///// 用户管理 /////

/**
 * 用户改名
 * @param   string  name        改名前
 * @param   string  to          改名后
 * @return  string  status      状态
 */
function uauth_rename($name,$to) {
	if($name == $to) return 'success';
	if(!preg_match('/^(\w+)$/',$name)) return 'illegal';
	if(!preg_match('/^(\w+)$/',$to  )) return 'illegal';

	wait_file('uauth_users');
	lock_file('uauth_users');

	$ulist = read_data('uauth_users');

	if(!isset($ulist[$name])) {unlock_file('uauth_users');return 'nxuser';}
	if(isset($ulist[$to])) {unlock_file('uauth_users');return 'exist';}

	$ulist[$to] = $ulist[$name];
	$ulist[$to]['name'] = $to;
	unset($ulist[$name]);

	rename(USER_DATA.$name.'/',USER_DATA.$to.'/');

	write_data('uauth_users',$ulist);
	unlock_file('uauth_users');

	return 'success';
}

/**
 * 用户获取状态
 * @param   string  name        用户名
 * @return  array   data        状态
 */
function uauth_get($name) {
	wait_file('uauth_users');
	$ulist = read_data('uauth_users');

	if(!isset($ulist[$name])) return null;

	return $ulist[$name];
}

/**
 * 用户删除
 * @comment 格式和改名相同
 */
function uauth_delete($name) {
	if(!preg_match('/^(\w+)$/',$name)) return 'illegal';

	wait_file('uauth_users');
	lock_file('uauth_users');
	wait_file('uauth_ip_limit');
	lock_file('uauth_ip_limit');

	$ulist = read_data('uauth_users');
	$iplist = read_data('uauth_ip_limit');

	if(!isset($ulist[$name])) {
		unlock_file('uauth_users');
		unlock_file('uauth_ip_limit');
		return 'nxuser';
	}

	if(isset($iplist[$ulist[$name]['ip']]) && $iplist[$ulist[$name]['ip']]>0)
		$iplist[$ulist[$name]['ip']]--;
	unset($ulist[$name]);

	del_dir(USER_DATA.$name.'/');

	write_data('uauth_users',$ulist);
	write_data('uauth_ip_limit',$iplist);
	unlock_file('uauth_ip_limit');
	unlock_file('uauth_users');

	return 'success';
}

/**
 * 用户全表获取
 */
function uauth_get_all() {
	wait_file('uauth_users');
	return read_data('uauth_users');
}

/**
 * 用户密码验证
 */
function uauth_veri_pass($name,$code) {
	if(!preg_match('/^(\w+)$/',$name)) return false;

	wait_file('uauth_users');
	$ulist = read_data('uauth_users');

	if(!isset($ulist[$name])) return null;

	return $ulist[$name]['pass'] == md5($code);
}

/**
 * 用户密码修改
 */
function uauth_update_pass($name,$newcode) {
	if(!preg_match('/^(\w+)$/',$name)) return 'illegal';

	wait_file('uauth_users');
	lock_file('uauth_users');

	$ulist = read_data('uauth_users');

	if(!isset($ulist[$name])) {unlock_file('uauth_users');return 'nxuser';}

	$ulist[$name]['pass'] = md5($newcode);

	write_data('uauth_users',$ulist);
	unlock_file('uauth_users');

	return 'success';
}

/**
 * 获取可用用户类型
 */
function get_all_user_types() {
	return [
		'ban' => [0,'封禁'],
		'normal' => [1,'普通'],
		'root' => [3,'超级管理员']
	];
}

/**
 * 用户封禁状态修改
 */
function uauth_update_enabled($name,$st) {
	if(!preg_match('/^(\w+)$/',$name)) return 'illegal';

	wait_file('uauth_users');
	lock_file('uauth_users');

	$ulist = read_data('uauth_users');

	if(!isset($ulist[$name])) {unlock_file('uauth_users');return 'nxuser';}

	$ulist[$name]['enabled'] = $st;

	write_data('uauth_users',$ulist);
	unlock_file('uauth_users');

	return 'success';
}

/**
 * IP占用数
 */
function uauth_ip_cnt($ip) {
	wait_file('uauth_ip_limit');
	$ulist = read_data('uauth_ip_limit');

	if(!isset($ulist[$ip])) return 0;

	return $ulist[$ip];
}

/**
 * 获取下一个用户文件ID
 */
function uauth_request_id($uname,$cate) {
	$fid = 'user/'.$uname.'/'.$cate.'/index';
	$dir = USER_DATA.$uname.'/'.$cate.'/';
	if(!file_exists($dir)) mkdir($dir);
	wait_file($fid);
	lock_file($fid);

	$fn = USER_DATA.$uname.'/'.$cate.'/index.txt';
	if(!file_exists($fn)) file_put_contents($fn,'100');
	$idx = intval(file_get_contents($fn));
	$idx ++;
	file_put_contents($fn,strval($idx));

	unlock_file($fid);

	return $idx;
}

/**
 * 判断用户文件夹是否包含指定数据文件
 */
function uauth_has_data($uname,$cate,$fid) {
	if($uname == '__syscall') {global $__syscall;return $__syscall -> dataExist($cate,preSubstr($fid,'.'));}

	return file_exists(USER_DATA.$uname.'/'.$cate.'/'.$fid);
}

/**
 * 枚举用户文件夹下的某文件
 */
function uauth_list_data($uname,$cate,$type='.json') {
	$dir = USER_DATA . $uname . '/' . $cate . '/';
	$list = dir_list($dir);

	$ret = [];
	foreach($list as $item) {
		if(strlen($item) >= strlen($type) && substr($item,strlen($item)-strlen($type)) == $type) {
			$ret[count($ret)] = substr($item,0,strlen($item)-strlen($type));
		}
	}

	return $ret;
}

/**
 * 读取用户json文件
 */
function uauth_read_data($uname,$cate,$name) {
	if($uname == '__syscall') {global $__syscall;return $__syscall -> fetchData($cate,$name);}

	$fid = 'user/' . $uname . '/' . $cate . '/' . $name;
	wait_file($fid);

	$fn = USER_DATA . $uname . '/' . $cate . '/' . $name . '.json';
	return json_decode(file_get_contents($fn),true);
}

/**
 * 占用用户json文件
 */
function uauth_lock_data($uname,$cate,$name) {
	if($uname == '__syscall') {global $__syscall;return $__syscall -> fetchData($cate,$name);}

	$fid = 'user/' . $uname . '/' . $cate . '/' . $name;
	wait_file($fid);
	lock_file($fid);

	$fn = USER_DATA . $uname . '/' . $cate . '/' . $name . '.json';
	return json_decode(file_get_contents($fn),true);
}

/**
 * 写入用户json文件
 */
function uauth_write_data($uname,$cate,$name,$obj) {
	if($uname == '__syscall') return;

	$fid = 'user/' . $uname . '/' . $cate . '/' . $name;

	$fn = USER_DATA . $uname . '/' . $cate . '/' . $name . '.json';
	file_put_contents($fn,json_encode($obj,JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE));
}

/**
 * 归还用户json文件
 */
function uauth_unlock_data($uname,$cate,$name) {
	if($uname == '__syscall') return;

	$fid = 'user/' . $uname . '/' . $cate . '/' . $name;
	unlock_file($fid);
}

if(!file_exists(USER_DATA.'__syscall/')) mkdir(USER_DATA.'__syscall/');
if(!file_exists(USER_DATA.'__syscall/playlist/')) mkdir(USER_DATA.'__syscall/playlist/');
