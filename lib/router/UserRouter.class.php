<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class UserRouter {
	private $_url = "";

	function __construct($_url){
		$this->url = $_url;
	}

	/*
	/users
	二级URL（按检测顺序）：
		/ - 主页
		/admin - 管理
		/user - 用户区
		/playlist - 歌单操作
		/setting - 设置
		/list-maker - 歌单构造程序
		/K_playlist - 酷我RemotePlay歌单专用浏览页
		/(\w+) - 音乐常规页面
	*/

	public function route(){
		$type = preSubstr($this->url);

		if($type == '') {
			return $this->routeMainPage();
		}
		if($type == 'passwd') {
			return $this->routePasswd();
		}
		if($type == 'logout') {
			return $this->routeLogout();
		}
		if($type == 'login') {
			return $this->routeLogin();
		}

		return false;
	}

	private function routeMainPage() {
		checkUrlEnd(stripFirstUrl($this->url));
		if(!uauth_username()) {
			header('HTTP/1.1 307 Not Authenticated');
			header('Location: '.BASIC_URL.'user/login');
			exit;
		}
		include_header();
		tpl("uauth/index");
		include_footer();
		return true;
	}

	private function routePasswd() {
		checkUrlEnd(stripFirstUrl($this->url));
		if(!uauth_username()) {
			header('HTTP/1.1 307 Not Authenticated');
			header('Location: '.BASIC_URL.'user/login');
			exit;
		}
		include_header();
		tpl("uauth/passwd");
		include_footer();
		return true;
	}

	private function routeLogout() {
		checkUrlEnd(stripFirstUrl($this->url));
		uauth_logout();
		header('HTTP/1.1 307 Not Authenticated');
		header('Location: '.BASIC_URL.'user/login');
		return true;
	}

	private function routeLogin() {
		checkUrlEnd(stripFirstUrl($this->url));
		if(!!uauth_username()) {
			header('HTTP/1.1 307 Already Authenticated');
			header('Location: '.BASIC_URL.'user');
		} else {
			include_header();
			tpl("uauth/login");
			include_footer();
		}
		return true;
	}
}
