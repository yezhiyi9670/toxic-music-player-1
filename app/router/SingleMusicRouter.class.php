<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class SingleMusicRouter {
	private $_url = "";

	function __construct($_url){
		$this->url = $_url;
	}

	/*
	/(\w+)
	二级URL（按检测顺序）：
		/ - 播放页
		/code - 代码
		/docs - 歌词文档HTML
		/make-doc - 歌词文档 action!
		/audio.(\w+) 或 /audio - 输出音频 file!
		/background.(\w+) 或 /background - 背景音频 file!
		/download - 下载歌曲 file!
		/raw - 已转换的歌词JSON raw!
		/html - HTML碎片输出 raw!（存在下属三级URL）
		/meta - 网址元数据 raw!
		/switch-all - 打包发送全部元数据和HTML碎片 raw!
		/refresh-cache - 刷新RemotePlay缓存 ajax!
		/edit
		/permission
	*/

	public function route(){
		$GLOBALS['linktype'] = 'music';

		$id = preSubstr($this->url);
		$this->url = stripFirstUrl($this->url);
		if(!preg_match('/^(\w+)$/',$id)) {
			return false;
		}

		$type = preSubstr($this->url);

		if($type == '') {
			return $this -> routeMainPage();
		}
		if($type == 'code') {
			return $this -> routeCode();
		}
		if($type == 'docs') {
			return $this -> routeDocs();
		}
		if($type == 'make-doc') {
			return $this -> routeDocAction();
		}
		if($type == 'audio' || preg_match('/^audio\.(\w+)$/',$type)) {
			return $this -> routeAudioPrint();
		}
		if($type == 'background' || preg_match('/^background\.(\w+)$/',$type)) {
			return $this -> routeBackground();
		}
		if($type == 'download') {
			return $this -> routeDownload();
		}
		if($type == 'raw') {
			return $this -> routeRaw();
		}
		if($type == 'html') {
			return $this -> routeHTML();
		}
		if($type == 'meta') {
			return $this -> routeMeta();
		}
		if($type == 'switch-all') {
			return $this -> routeSwitcher();
		}
		if($type == 'refresh-cache') {
			return $this -> routeRefreshCache();
		}
		if($type == 'edit') {
			return $this -> routeEdit();
		}
		if($type == 'permission') {
			return $this -> routePermission();
		}

		return false;
	}

	private function routeMainPage() {
		checkPermission('music/index');
		include_header();
		tpl("player/index");
		include_footer();
		return true;
	}

	private function routeCode() {
		checkUrlEnd(stripFirstUrl($this->url));
		checkPermission('music/code');
		if(!isset($_GET['raw'])) {
			include_header();
			tpl("inner/code");
			include_footer();
		} else {
			header('Content-Type: text/plain');
			if(!isset($_GET['lrc'])) {
				echo getLyricFile(cid());
			} else {
				if($_GET['lrc'] == 'minified') {
					echo buildMinifiedLrc(json_decode(parseCmpLyric(cid(),false),true));
				} else if($_GET['lrc'] == 'fancy') {
					echo buildExtendedLrc(json_decode(parseCmpLyric(cid(),false),true));
				} else {
					print404('Parameter Lrc');
				}
			}
		}
		return true;
	}

	private function routeDocs() {
		checkUrlEnd(stripFirstUrl($this->url));
		checkPermission('music/download_doc');
		include_header();
		tpl("inner/docs");
		include_footer();
		return true;
	}

	private function routeDocAction() {
		checkUrlEnd(stripFirstUrl($this->url));
		checkPermission('music/getdoc');
		tpl("inner/generate");
		return true;
	}

	private function routeAudioPrint() {
		checkUrlEnd(stripFirstUrl($this->url));
		checkPermission('music/audio/out');

		if(isKuwoId(cid())) {
			global $akCrawler;
			global $akCrawlerInfo;
			remoteEncache(sid($d),'K');
			if(substr($_GET['_lnk'],strlen($_GET['_lnk'])-4)!= '.url') {
				header('HTTP/1.1 307 Redirect'); //将RemotePlay请求导向实际音频地址。不允许缓存。
				header('Location: '.$akCrawler[cid()]->url());
			}
			else {
				header('Content-Type: text/plain');
				echo $akCrawler[cid()]->url();
			}
			exit;
		}
		$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/song");
		file_put_out($fn);

		return true;
	}

	private function routeBackground() {
		checkUrlEnd(stripFirstUrl($this->url));
		checkPermission('music/audio/out');

		checkPermission($urltype);
		$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/back");
		if($fn)
			file_put_out($fn);
		else print404('Not Uploaded');

		return true;
	}

	private function routeDownload() {
		checkUrlEnd(stripFirstUrl($this->url));
		checkPermission('music/audio/dl');

		$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/song");
		$c=json_decode(parseCmpLyric(preSubstr($_GET["_lnk"])),true);
		file_put_out($fn,true,preSubstr($_GET["_lnk"])." ".$c['meta']['N'].
			substr($fn,strrpos($fn,'.'))
		);

		return true;
	}

	private function routeRaw() {
		checkUrlEnd(stripFirstUrl($this -> url));

		checkPermission('music/index');
		header("Content-Type: application/json");
		echo parseCmpLyric(preSubstr($_GET["_lnk"]));

		return true;
	}

	private function routeHTML() {
		$t = stripFirstUrl($this -> url);
		$nxt = preSubstr($t);
		$t = stripFirstUrl($t);
		checkUrlEnd($t);
		checkPermission('music/index');
		header("Content-Type: text/plain");

		if($nxt == 'lyric') {
			tpl("player/lyric");
		} else if($nxt == 'fr') {
			tpl("player/firstrow");
		} else if($nxt == 'tr') {
			tpl("player/thirdrow");
		} else if($nxt == 'trn') {
			tpl("player/thirdrow-n");
		} else {
			return false;
		}

		return true;
	}

	private function routeMeta() {
		checkUrlEnd(stripFirstUrl($this -> url));
		checkPermission('music/index');

		header("Content-Type: application/json");
		tpl("player/meta");

		return true;
	}

	private function routeSwitcher() {
		checkUrlEnd(stripFirstUrl($this -> url));
		checkPermission('music/index');

		header("Content-Type: text/plain");
		$boundary="\n--------TxmpSwitchDataBoundary--------\n";
		echo parseCmpLyric(preSubstr($_GET["_lnk"]));
		echo $boundary;
		tpl("player/meta");
		echo $boundary;
		tpl("player/lyric");
		echo $boundary;
		tpl("player/firstrow");
		echo $boundary;
		tpl("player/thirdrow");
		echo $boundary;
		tpl("player/thirdrow-n");

		return true;
	}

	private function routeRefreshCache() {
		if(substr(cid(),0,2) != 'K_') {
			return false;
		}
		remoteEncache(cid(),'K',true);
		global $akCrawler;
		if($akCrawler[cid()] -> success) echo 'Success';
		else echo 'Failed';

		return true;
	}

	private function routeEdit() {
		checkUrlEnd(stripFirstUrl($this -> url));
		checkPermission('admin/edit');

		include_header();
		tpl("admin/editor");
		include_footer();

		return true;
	}

	private function routePermission() {
		checkUrlEnd(stripFirstUrl($this -> url));
		checkROOT();

		include_header();
		tpl("admin/permission");
		include_footer();

		return true;
	}
}
