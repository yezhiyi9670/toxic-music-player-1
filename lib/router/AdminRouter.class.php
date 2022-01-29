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
		/query-comp - 查询编译信息
		/query-ann - 查询代码标记
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
		if($type == 'query-comp') {
			return $this -> queryComp();
		}
		if($type == 'query-ann') {
			return $this -> queryAnn();
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

	private function queryComp() {
		checkUrlEnd(stripFirstUrl($this->url));
		checkROOT();
		header('Content-Type: text/plain');
		tpl("admin/query_comp");
		return true;
	}

	private function queryAnn() {
		checkUrlEnd(stripFirstUrl($this->url));
		checkROOT();
		header('Content-Type: text/plain');
		tpl("admin/query_ann");
		return true;
	}
}
