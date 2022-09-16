<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

///// 用户数据文件访问量较大，使用文件锁定方法，防止一个文件同时被多个进程写入 /////

function lock_file($n) {
	@file_put_contents(DATA_PATH.$n.'.writing.lock','');
}

function unlock_file($n) {
	@unlink(DATA_PATH.$n.'.writing.lock');
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
	file_put_contents($fname,encode_data($t,true));
}

// ----- 以上无需namespace，因为都是通用接口 ----- //


///// 密码哈希 /////

/**
 * 生成随机盐
 * （无需使用密码级随机数）
 */
function uauth_salt_gen($len = 64) {
	$charset = '1234567890QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm';
	$ret = '';
	for($i = 0; $i < $len; $i++) {
		$ret .= $charset[mt_rand(0, strlen($charset) - 1)];
	}
	return $ret;
}

/**
 * 随机生成密码哈希
 */
function uauth_hash_create($pass) {
	$salt = uauth_salt_gen();
	$hash = hash_hmac('sha256', $salt . $pass, PASS_KEY);
	return $salt . ':' . $hash;
}

/**
 * 是否是新的哈希标准
 */
function is_uauth_hash($hash) {
	return false !== strpos($hash,':');
}

/**
 * 验证密码哈希
 * 
 * 返回状态：
 * 0 - 匹配失败
 * 1 - 密码哈希是 md5，需要加强
 * 2 - 密码哈希是加盐密钥哈希
 */
function uauth_hash_verify($pass, $hash) {
	if(!is_uauth_hash($hash)) {
		return (md5($pass) == $hash) * 1;
	} else {
		$split = explode(':',$hash);
		$salt = $split[0];
		return (hash_hmac('sha256', $salt . $pass, PASS_KEY) == $split[1]) * 2;
	}
}

/**
 * 哈希摘要
 *
 * 返回：md5 前八位或 uauth 哈希中间 17 位
 */
function uauth_hash_summary($hash) {
	if(!is_uauth_hash($hash)) {
		return substr($hash,0,8);
	} else {
		return substr($hash,56,17);
	}
}

///// 用户登录 /////

/**
 * 判断浏览器是否作为用户登录。如果是，获取其用户名。
 * @return  string  username    当前登录的用户名。未登录返回的是一个假值（空字符串）
 */
function uauth_username() {
	if($GLOBALS['uauth_username'] ?? null) {
		if($GLOBALS['uauth_username'] == '-') return '';
		else return $GLOBALS['uauth_username'];
	}

	wait_file('uauth_session');
	lock_file('uauth_session');
	wait_file('uauth_users');

	$ulist = read_data('uauth_users');
	$t = read_data('uauth_session');

	if($t == null) {
		$t = [];
	}

	// 清理已过期
	$rmlist = [];
	foreach($t as $k=>$v) {
		if(time() - $v['time'] > 60*60*24*30 || !isset($v['lastTime']) || time() - $v['lastTime'] > 2*86400) $rmlist[count($rmlist)] = $k;
	}
	foreach($rmlist as $k) {
		unset($t[$k]);
	}

	if(!isset($_COOKIE['X-'.APP_PREFIX.'-uauth-session']) || !isset($_COOKIE['X-'.APP_PREFIX.'-uauth-token'])) {
		write_data('uauth_session',$t);unlock_file('uauth_session',$t);
		$GLOBALS['uauth_username'] = '-';return '';
	}
	$sess=$_COOKIE['X-'.APP_PREFIX.'-uauth-session'];
	$token=$_COOKIE['X-'.APP_PREFIX.'-uauth-token'];
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
	if($ulist[$t[$sess]['name']]['pass'] != $t[$sess]['pass'] || !is_uauth_hash($t[$sess]['pass'])) {
		write_data('uauth_session',$t);unlock_file('uauth_session',$t);
		$GLOBALS['uauth_username'] = '-';return '';
	} // 密码无法匹配 | md5哈希枪毙
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
	if(!isset($_COOKIE['X-'.APP_PREFIX.'-uauth-session']) || !isset($_COOKIE['X-'.APP_PREFIX.'-uauth-token'])) {
		unlock_file('uauth_session');
		return false;
	}
	$sess=$_COOKIE['X-'.APP_PREFIX.'-uauth-session'];
	$token=$_COOKIE['X-'.APP_PREFIX.'-uauth-token'];
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
	lock_file('uauth_users');
	wait_file('uauth_session');
	lock_file('uauth_session');
	$ulist = read_data('uauth_users');
	$slist = read_data('uauth_session');

	if(!isset($ulist[$name]) || $ulist[$name]['name']!=$name) {
		unlock_file('uauth_session'); unlock_file('uauth_users');
		return 'nxuser';
	}

	$hash_status = uauth_hash_verify($pass,$ulist[$name]['pass']);
	if($hash_status == 0 && !$flag) {
		unlock_file('uauth_session'); unlock_file('uauth_users');
		return 'passwrong';
	}

	if($hash_status == 1) {
		// md5, 枪毙！
		$ulist[$name]['pass'] = uauth_hash_create($pass);
		write_data('uauth_users',$ulist);
	}

	if($ulist[$name]['enabled'] == 0) {
		unlock_file('uauth_session'); unlock_file('uauth_users');
		return 'ban';
	}

	$sess = uauth_salt_gen(32);
	$token = uauth_salt_gen(32);

	$slist[$sess] = [
		'token' => $token,
		'name'  => $name,
		'ua'    => $_SERVER['HTTP_USER_AGENT'],
		'pass'  => $ulist[$name]['pass'],
		'time'  => time(),
		'lastTime'=> time(),
	];

	setcookie('X-'.APP_PREFIX.'-uauth-session',$sess,time()+60*60*24*30,"/");
	setcookie('X-'.APP_PREFIX.'-uauth-token',$token,time()+60*60*24*30,"/");

	write_data('uauth_session',$slist);
	unlock_file('uauth_session'); unlock_file('uauth_users');

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
		"pass"  => uauth_hash_create($pass),
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

	return uauth_hash_verify($code, $ulist[$name]['pass']);
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

	$ulist[$name]['pass'] = uauth_hash_create($newcode);

	write_data('uauth_users',$ulist);
	unlock_file('uauth_users');


	return 'success';
}

/**
 * 获取可用用户类型
 */
function get_all_user_types() {
	return [
		'ban' => [0,LNG('uauth.type.ban')],
		'normal' => [1,LNG('uauth.type.normal')],
		'root' => [3,LNG('uauth.type.root')]
	];
}

/**
 * 用户类型修改
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

///// 用户文件 /////

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
	if($uname == '__syscall') {global $__syscall;return $__syscall -> dataExist($cate,$fid);}

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

	$dir = USER_DATA . $uname . '/' . $cate . '/';
	if(!file_exists($dir)) {
		mkdir($dir);
	}

	$fid = 'user/' . $uname . '/' . $cate . '/' . $name;

	$fn = USER_DATA . $uname . '/' . $cate . '/' . $name . '.json';
	file_put_contents($fn,encode_data($obj,true));
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
