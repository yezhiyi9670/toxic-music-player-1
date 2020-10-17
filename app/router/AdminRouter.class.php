<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class AdminRouter {
	private $_url = "";

	function __construct($_url){
		$this->url = $_url;
	}

	/*
	/admin
	二级URL（按检测顺序）：
		/ - 管理主页
		/users - 用户管理
	*/

	public function route(){
		$GLOBALS['linktype'] = 'admin';

		$type = preSubstr($this->url);

		if($type == '') {
			return $this -> routeMainPage();
		}
		if($type == 'users') {
			return $this -> routeUsers();
		}

		return false;
	}

	private function routeMainPage() {
		checkUrlEnd(stripFirstUrl($this->url));
		checkROOT();
		include_header();
		tpl("admin/index");
		include_footer();
		return true;
	}

	private function routeUsers() {
		checkUrlEnd(stripFirstUrl($this->url));
		checkROOT();
		include_header();
		tpl("admin/users");
		include_footer();
		return true;
	}
}
