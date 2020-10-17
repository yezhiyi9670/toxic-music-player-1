<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class PlaylistRouter {
	private $_url = "";

	function __construct($_url){
		$this->url = $_url;
	}

	/*
	/playlist
	二/三/四级URL（按检测顺序）：
		save-list/(\d+) - 保存歌单
		gen-docs/(\w+)/(\d+) - 打印全部歌词文档
		(\w+)/(\d+) - 打开并播放
		(\w+)/(\d+)/embed - 生成可被直接引用的JS
	*/

	public function route(){
		$type = preSubstr($this->url);

		if($type == 'save-list') {
			return $this -> routeSave();
		}
		if($type == 'gen-docs') {
			return $this -> routeDocs();
		}
		return $this -> routePlay();
	}

	private function routeSave() {
		$t = $this -> url;
		$t = stripFirstUrl($t);
		if(!preg_match('/^(\d+)$/',preSubstr($t))) {
			return false;
		}
		checkUrlEnd(stripFirstUrl($t));
		tpl('uauth/savelist');
		return true;
	}

	private function routeDocs() {
		$arr = ['','',''];
		$t = $this -> url;
		$t = stripFirstUrl($t);
		$arr[1] = preSubstr($t);
		$t = stripFirstUrl($t);
		$arr[2] = preSubstr($t);
		$t = stripFirstUrl($t);
		checkUrlEnd($t);
		if(!preg_match('/^(\w+)$/',$arr[1])) {
			return false;
		}
		if(!preg_match('/^(\d+)$/',$arr[2])) {
			return false;
		}

		if(!hasPlaylist($arr[1],$arr[2])) {
			return false;
		} else if(!is_root() && !readPlaylistData($arr[1],$arr[2])['public'] && uauth_username() != $arr[1]) {
			print401('Private Content');
		} else {
			$_GET['_lnk'] = readPlaylistData($arr[1],$arr[2])['playlist'][0]['id'];
			checkPermission('music/getdoc');
			$GLOBALS['listname'] = $arr[1];
			$GLOBALS['listid'] = $arr[2];
			include_header();
			tpl('inner/docs');
			include_footer();
		}
		return true;
	}

	private function routePlay() {
		$arr = ['','',''];
		$t = $this -> url;
		$arr[1] = preSubstr($t);
		$t = stripFirstUrl($t);
		$arr[2] = preSubstr($t);
		$t = stripFirstUrl($t);
		if(!preg_match('/^(\w+)$/',$arr[1])) {
			return false;
		}
		if(!preg_match('/^(\d+)$/',$arr[2])) {
			return false;
		}
		if($t == 'embed') {
			checkUrlEnd(stripFirstUrl($t));
			return $this -> routeEmbed($arr);
		}
		checkUrlEnd($t);

		if(!hasPlaylist($arr[1],$arr[2])) {
			return false;
		} else if(!is_root() && !readPlaylistData($arr[1],$arr[2])['public'] && uauth_username() != $arr[1]) {
			print401('Private Content');
		} else {
			$data = readPlaylistData($arr[1],$arr[2]);
			$_GET['_lnk'] = $data['playlist'][0]['id'];
			checkPermission('music/index');
			$GLOBALS['listname'] = $arr[1];
			$GLOBALS['listid'] = $arr[2];
			if(!isset($_GET['raw'])) {
				include_header();
				tpl('player/index');
				include_footer();
			} else if(isset($_GET['json'])) {
				header('Content-Type: application/json');
				echo json_encode($data,JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE);
			} else {
				header('Content-Type: text/plain');
				echo readPlaylistData($arr[1],$arr[2],true);
			}
		}
		return true;
	}

	private function routeEmbed($arr) {
		if(!hasPlaylist($arr[1],$arr[2])) {
			return false;
		} else if(!is_root() && !readPlaylistData($arr[1],$arr[2])['public'] && uauth_username() != $arr[1]) {
			print401('Private Content');
		} else {
			$GLOBALS['listname'] = $arr[1];
			$GLOBALS['listid'] = $arr[2];
			header('Content-Type: text/javascript');
			tpl('player/embed');
		}
		return true;
	}
}
