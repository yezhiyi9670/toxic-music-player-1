<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class TopLevelRouter {
	private $_url = "";

	function __construct($_url){
		$this->url = $_url;
	}

	/*
	一级URL（按检测顺序）：
		/ - 主页
		/clean-garbage - 清理垃圾
		/i18n-script - 语言定义脚本
		/system-meta - 系统信息
		/dynamic - “静态”文件
		/admin - 管理
		/user - 用户区
		/playlist - 歌单操作
		/setting - 设置
		/list-maker - 歌单构造程序
		/K_playlist - 酷我RemotePlay歌单专用浏览页
		/version-history - 版本历史（如果存在）
		/(\w+) - 音乐常规页面
	*/

	public function route(){
		$GLOBALS['linktype'] = 'normal';
		$GLOBALS['isMusicCurrent'] = false;

		checkCSRF();

		$type = preSubstr($this->url);
		if(strlen($this->url) > 0 && $this->url[strlen($this->url) - 1] == '/') {
			return false;
		}

		if($type != 'setting') {
			setting_upd();
		}

		if($type == '') {
			return $this -> routeMainPage();
		}
		if($type == 'clean-garbage') {
			@ignore_user_abort(true);
			$cleaner = new GarbageCleaner();
			header('Content-Type: text/plain');
			if($cleaner->needClean()) {
				$cleaner->clean();
				echo LNG('ui.trash_cleaned');
			} else {
				echo LNG('ui.trash_no_clean');
			}
			return true;
		}
		if($type == 'i18n-script') {
			return $this -> routeI18n();
		}
		if($type == 'dynamic') {
			return $this -> routeDynamic();
		}
		if($type == 'system-meta') {
			return $this -> routeSystemMeta();
		}
		if($type == 'admin') {
			return $this -> routeAdmin();
		}
		if($type == 'user') {
			return $this -> routeUser();
		}
		if($type == 'playlist') {
			return $this -> routePlaylist();
		}
		if($type == 'setting') {
			return $this -> routeSetting();
		}
		if($type == 'list-maker') {
			return $this -> routeListMaker();
		}
		if($type == 'K_playlist') {
			return $this -> routeKPlaylist();
		}
		if($type == 'version-history') {
			return $this -> routeVersionHistory();
		}
		require(ROUTER . 'SingleMusicRouter.class.php');
		$router = new SingleMusicRouter($this->url);
		return $router -> route();
	}

	private function routeI18n() {
		checkUrlEnd(stripFirstUrl($this->url));

		printLNGScript();
		return true;
	}

	private function routeDynamic() {
		require(ROUTER . 'DynamicRouter.class.php');
		$router = new DynamicRouter(stripFirstUrl($this->url));
		return $router -> route();
	}

	private function routeMainPage() {
		checkUrlEnd($this->url);

		include_header();
		tpl("list/index");
		include_footer();
		return true;
	}

	private function routeSystemMeta() {
		checkUrlEnd(stripFirstUrl($this->url));

		header('Content-Type: application/json');
		echo encode_data([
			'app_prefix' => APP_PREFIX,
			'app_name' => _CT('app_name'),
			'version' => VERSION,
			'base_url' => BASIC_URL,
			'username' => uauth_username()
		]);
		return true;
	}

	private function routeAdmin() {
		require(ROUTER . 'AdminRouter.class.php');
		$router = new AdminRouter(stripFirstUrl($this->url));
		return $router -> route();
	}

	private function routeUser() {
		require(ROUTER . 'UserRouter.class.php');
		$router = new UserRouter(stripFirstUrl($this->url));
		return $router -> route();
	}

	private function routePlaylist() {
		require(ROUTER . 'PlaylistRouter.class.php');
		$router = new PlaylistRouter(stripFirstUrl($this->url));
		return $router -> route();
	}

	private function routeSetting() {
		checkUrlEnd(stripFirstUrl($this->url));

		include_header();
		tpl('user/setting');
		include_footer();

		return true;
	}

	private function routeListMaker() {
		$t = stripFirstUrl($this->url);
		$id = preSubstr($t);
		$t = stripFirstUrl($t);

		if($id != '') {
			checkUrlEnd($t);
			if(!preg_match('/^(\d+)$/',$id)) {
				return false;
			}
			if(uauth_username() && hasPlaylist(uauth_username(),$id) || uauth_has_data(uauth_username(),'playlist',$id.'.csv')) {
				include_header();
				tpl('user/listmaker');
				include_footer();
			}
			else if(uauth_username()) {
				return false;
			}
			else {
				print401('Require Authentication');
			}

			return true;
		} else {
			include_header();
			tpl('user/listmaker');
			include_footer();
			return true;
		}
	}

	private function routeKPlaylist() {
		$t = stripFirstUrl($this->url);
		$GLOBALS['remote_playlist_id'] = preSubstr($t);
		$t = stripFirstUrl($t);

		if(!preg_match('/^(\d+)$/',$GLOBALS['remote_playlist_id'])) {
			return false;
		}
		checkUrlEnd($t);

		$_GET['_lnk'] = '$FFA000';
		$_GET['return'] = true;
		$_GET['key'] = '^' . $GLOBALS['remote_playlist_id'];
		$_GET['pageid'] = '1';
		$GLOBALS['remote_playlist'] = kuwoSearchSong();
		if($GLOBALS['remote_playlist']['code'] != 200) {
			return false;
		} else {
			include_header();
			tpl('remote_playlist/kuwo');
			include_footer();
		}

		return true;
	}

	private function routeVersionHistory() {
		checkUrlEnd(stripFirstUrl($this->url));

		if(!file_exists(CHANGELOG)) {
			return false;
		}

		include_header();
		tpl('common/version_history');
		include_footer();

		return true;
	}
}
