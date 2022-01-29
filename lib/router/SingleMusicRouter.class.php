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
		/comp-info - 编译信息（权限同 code）
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
		/avatar - 输出摘要图片
		/edit
		/resource
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
		if($type == 'comp-info') {
			return $this -> routeCompInfo();
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
		if($type == 'avatar') {
			return $this -> routeAvatar();
		}
		if($type == 'edit') {
			$GLOBALS['linktype'] = 'admin';
			return $this -> routeEdit();
		}
		if($type == 'resource') {
			$GLOBALS['linktype'] = 'admin';
			return $this -> routeResource();
		}
		if($type == 'permission') {
			$GLOBALS['linktype'] = 'admin';
			return $this -> routePermission();
		}

		return false;
	}

	// 主页面
	// 要求：有效
	private function routeMainPage() {
		if(!isValidMusic(cid(),false)) {
			print404('No Such Music');
		}
		checkPermission('music/index');
		include_header();
		tpl("player/index");
		include_footer();
		return true;
	}

	// 代码查看
	// 要求：有效
	private function routeCode() {
		checkUrlEnd(stripFirstUrl($this->url));
		if(!isValidMusic(cid(),false)) {
			print404('No Such Music');
		}
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
				$GLOBALS['lrcopt'] = [
					'delta' => clampLimit($_GET['delta'],0,0.1), // 偏移量
					'comment' => clampLimit($_GET['comment'],0.7,0.1,0,65535), // 注释展示时长
					'precision' => clampLimit($_GET['precision'],0.1,0.1,0.1,60.0), // 基准精度
				];
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

	// 编译信息
	// 要求：有效
	private function routeCompInfo() {
		checkUrlEnd(stripFirstUrl($this->url));
		if(!isValidMusic(cid(),false)) {
			print404('No Such Music');
		}
		checkPermission('music/code');
		if(!isset($_GET['raw'])) {
			include_header();
			tpl("inner/comp_info");
			include_footer();
		} else {
			header('Content-Type: application/json');
			$data = parseCmpLyric(cid(),true,true,'cmpi_ADD_ERROR_P');
			if(!is_array($data['message'])) {
				$data['message'] = [];
			}
			echo encode_data($data['message']);
		}
		return true;
	}

	// 文档生成
	// 要求：存在
	private function routeDocs() {
		checkUrlEnd(stripFirstUrl($this->url));
		if(!isValidMusic(cid(),false)) {
			print404('No Such Music');
		}
		checkPermission('music/download_doc');
		include_header();
		tpl("inner/docs");
		include_footer();
		return true;
	}

	// 文档生成 Action
	// 要求：存在
	private function routeDocAction() {
		checkUrlEnd(stripFirstUrl($this->url));
		if(!isValidMusic(cid(),false)) {
			print404('No Such Music');
		}
		checkPermission('music/getdoc');
		tpl("inner/generate");
		return true;
	}

	// 输出音频
	// 要求：有音频
	private function routeAudioPrint() {
		checkUrlEnd(stripFirstUrl($this->url));
		if(!isValidMusic(cid(),true)) {
			print404('No Such Music');
		}
		checkPermission('music/audio/out');

		if(isKuwoId(cid())) {
			global $akCrawler;
			global $akCrawlerInfo;
			remoteEncache(sid($_GET['_lnk']),'K');
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

	// 输出从音频
	// 要求：有主音频
	private function routeBackground() {
		checkUrlEnd(stripFirstUrl($this->url));
		if(!isValidMusic(cid(),true)) {
			print404('No Such Music');
		}
		checkPermission('music/audio/out');

		$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/back");
		if($fn)
			file_put_out($fn);
		else print404('Not Uploaded');

		return true;
	}

	// 下载
	// 要求：有音频
	private function routeDownload() {
		checkUrlEnd(stripFirstUrl($this->url));
		if(!isValidMusic(cid(),true)) {
			print404('No Such Music');
		}
		checkPermission('music/audio/dl');

		$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/song");
		$c=json_decode(parseCmpLyric(preSubstr($_GET["_lnk"])),true);
		file_put_out($fn,true,preSubstr($_GET["_lnk"])." ".$c['meta']['N'].
			substr($fn,strrpos($fn,'.'))
		);

		return true;
	}

	// 输出 json 数据
	// 要求：存在
	private function routeRaw() {
		checkUrlEnd(stripFirstUrl($this -> url));
		if(!isValidMusic(cid(),false)) {
			print404('No Such Music');
		}

		checkPermission('music/index');
		header("Content-Type: application/json");
		echo parseCmpLyric(preSubstr($_GET["_lnk"]));

		return true;
	}

	// 输出播放器 HTML 片段
	// 要求：有音频
	private function routeHTML() {
		$t = stripFirstUrl($this -> url);
		$nxt = preSubstr($t);
		$t = stripFirstUrl($t);
		checkUrlEnd($t);
		if(!isValidMusic(cid(),true)) {
			print404('No Such Music');
		}
		checkPermission('music/index');
		header("Content-Type: text/plain");

		if($nxt == 'lyric-overview') {
			tpl("player/lyric_overview");
		} else if($nxt == 'lyric-content') {
			tpl("player/lyric_content");
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

	// 输出 Meta
	// 要求：存在
	private function routeMeta() {
		checkUrlEnd(stripFirstUrl($this -> url));
		if(!isValidMusic(cid(),false)) {
			print404('No Such Music');
		}
		checkPermission('music/index');

		header("Content-Type: application/json");
		tpl("player/meta");

		return true;
	}

	// 输出 Switch 信息
	// 要求：有音频
	private function routeSwitcher() {
		checkUrlEnd(stripFirstUrl($this -> url));
		if(!isValidMusic(cid(),false)) {
			print404('No Such Music');
		}
		checkPermission('music/index');

		header("Content-Type: text/plain");
		$boundary="\n--------TxmpSwitchDataBoundary--------\n";
		echo parseCmpLyric(preSubstr($_GET["_lnk"]));
		echo $boundary;
		tpl("player/meta");
		echo $boundary;
		tpl("player/lyric_content");
		echo $boundary;
		tpl("player/firstrow");
		echo $boundary;
		tpl("player/thirdrow");
		echo $boundary;
		tpl("player/thirdrow-n");
		echo $boundary;
		tpl("player/lyric_overview");

		return true;
	}

	// 重置 RemotePlay 缓存
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

	// 输出摘要图像
	// 要求：存在
	private function routeAvatar() {
		checkUrlEnd(stripFirstUrl($this -> url));
		if(!isValidMusic(cid(),false)) {
			print404('No Such Music');
		}
		// 不访问主页，不会用到摘要图像。
		checkPermission('music/index');

		// 酷我音乐
		if(isKuwoId(cid())) {
			global $akCrawler;
			global $akCrawlerInfo;
			remoteEncache(cid(),'K');
			if(!$akCrawler[cid()]->success) {
				return false;
			} else {
				header('Content-Type: image/jpg');
				header("Cache-Control: public max-age=432000");
				header("Last-Modified: " . gmdate('D, d M Y H:i:s',$akCrawler[cid()]->cached));
				echo ex_url_get_contents($akCrawler[cid()]->cache['info']['pic']);
				return true;
			}
		} else {
			$path = getPicturePath(FILES . cid() . '/avatar');
			if(!file_exists($path)) {
				return false;
			} else {
				file_put_out($path);
			}
		}

		return true;
	}

	// 编辑
	// 要求：本地
	private function routeEdit() {
		checkUrlEnd(stripFirstUrl($this -> url));
		if(!isValidMusic(cid(),false,false)) {
			print404('No Such Music');
		}
		checkPermission('admin/edit');

		include_header();
		tpl("admin/editor");
		include_footer();

		return true;
	}

	// 管理资源
	// 要求：本地
	private function routeResource() {
		checkUrlEnd(stripFirstUrl($this -> url));
		if(!isValidMusic(cid(),false,false)) {
			print404('No Such Music');
		}
		checkPermission('admin/edit');

		include_header();
		tpl("admin/resource");
		include_footer();

		return true;
	}

	// 权限修改
	// 要求：本地
	private function routePermission() {
		checkUrlEnd(stripFirstUrl($this -> url));
		if(!isValidMusic(cid(),false,false)) {
			print404('No Such Music');
		}
		checkROOT();

		include_header();
		tpl("admin/permission");
		include_footer();

		return true;
	}
}
