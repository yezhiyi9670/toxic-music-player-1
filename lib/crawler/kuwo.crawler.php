<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// 酷我音乐爬虫专用 HTTP GET 格式
function kuwo_search_httpget($url) {
	// 随机生成 csrf token。
	$token_mask = 'AAA00AAAA00';
	$str = [
		'0' => '0123456789',
		'A' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
	];
	$token = '';
	for($i = 0; $i < 11; $i ++) {
		$idx = mt_rand(0,strlen($str[$token_mask[$i]]) - 1);
		$token .= $str[$token_mask[$i]][$idx];
		if($i == 1 || $i == 9) {
			$i++;
			$token .= $token[$i-1];
		}
	}
	// $token = 'LZZ31RUTJ22';
	return ex_url_get_contents($url,[
		'Referer: https://kuwo.cn/',
		'Cookie: kw_token='.$token,
		'csrf: '.$token,
	]);
}

// 反复尝试（搜索接口看起来不太稳定）
function kuwo_search_get_json($url) {
	$remain_retry = _CT('rp_search_retry');
	$delay = _CT('rp_search_retry_delay');
	$data = kuwo_search_httpget($url);
	while(trim($data)[0] != '{' && strlen($data) < 400 && $remain_retry > 0) {
	  $remain_retry--;
	  sleep($delay);
	  $data = kuwo_search_httpget($url);
	}
	return $data;
  }

// 全局ID是否符合
function kuwo_classify_id($x) {
	return (strlen($x) >= 3 && substr($x,0,2) == 'K_');
}

// Remoteplay 付费状态获取
function kuwoPayStatus($val) {
	$val = intval($val);
	$play = $val & 0xF;
	$download = ($val & 0xF0) >> 4;
	$local_enc = ($val & 0xF00000) >> 20; // 这个可能是错的 这个就是错的
	return [
		'no_play' => false,
		'no_download' => false,
		'pay_play' => ($play == 0xF),
		'pay_download' => ($download == 0xF)
	];
}

// 歌曲搜索接口（直接处理_GET数据，直接输出搜索结果）
function kuwoSearchSong() {
	if(isset($_GET['json'])) {
		header('Content-Type: application/json');
		echo kuwo_search_httpget('https://kuwo.cn/api/www/search/searchMusicBykeyWord?key='.urlencode($_GET['key']).'&pn='.$_GET['pageid'].'&rn=50');
	} else {
		header('Content-Type: text/plain');

		if($_GET['key'][0]==':' || preg_match('/^K_(\d+)$/',$_GET['key'])) {
			if($_GET['key'][0] != ':') {
				$_GET['key'] = ':' . $_GET['key'];
			}
			$item='K_'.substr($_GET['key'],1);
			if(substr($_GET['key'],1,2) == 'K_') {
				$item = substr($_GET['key'],1);
			}
			if(!preg_match('/^K_(\d+)$/',$item)) {
				echo LNG('rp.search.invalid_id');
				exit;
			}

			if(!isValidMusic($item)) {
				echo LNG('rp.search.no_such_id');
				exit;
			}
			echo '<ul>';
			global $akCrawler;
			global $akCrawlerInfo;
			printRmpList($akCrawler[$item]->cache['info'],true);
			// printIndexList();
			echo '</ul>';
			exit;
		}

		// 特殊目的
		if($_GET['key'][0]=='>') {
			$purpose = trim(substr($_GET['key'],1));
			if($purpose == '__mp_suggestions__') {
				// 数据串全局匹配歌单信息。
				// [修复] 酷我音乐试图将变量置入播放量位置干掉爬虫。v127a-pre10。
				$matcher = '/name:"(.+?)",listencnt:([0-9A-Za-z]+),id:(\d+)/';
				@$text = kuwo_search_get_json('https://kuwo.cn/');

				// 当场返回
				if(isset($_GET['initial'])) {
					echo $text;
					exit;
				}

				$lst = [];
				preg_match_all($matcher,$text,$lst);

				echo '<ol>';
				$cnt = count($lst[0]);
				for($i = 0; $i < $cnt; $i++) {
					$ele = [
						"name" => $lst[1][$i],
						"listencnt" => $lst[2][$i],
						"id" => $lst[3][$i]
					];
					printKListList($ele);
				}
				echo '</ol>';
			} else {
				echo LNG('rp.search.invalid_purpose');
			}
			exit;
		}

		// 直接访问一个歌单。（方便起见，有两种数据格式）
		if($_GET['key'][0]=='^') {
			$id = trim(substr($_GET['key'],1));
			@$data = json_decode(kuwo_search_get_json('https://kuwo.cn/api/www/playlist/playListInfo?pid='.$id.'&pn='.$_GET['pageid'].'&rn=50'),true);

			if(isset($_GET['raw'])) echo encode_data($data);
			else if(isset($_GET['return'])) {header('Content-Type: text/html');return $data;}
			else {
				$npage=ceil($data['data']['total']/50.0);
				$startid=50*$_GET['pageid']-49;
				$endid=50*$_GET['pageid'];

				if(!isset($data['data']['musicList'])) {
					echo LNG('rp.search.fail');

					exit;
				}

				echo '<p>' . LNG('page.total',$data['data']['total']) . '</p>' . "\n";
				echo '<ol>';
				foreach($data['data']['musicList'] as $item) {
					printRmpList($item);
				}
				echo '</ol>';
				echo '<p><a onclick="turn_page(1)">' . LNG('page.first') . '</a>&nbsp;&nbsp;<a onclick="'.($_GET['pageid']<=1?'':'turn_page(curr_pageid-1)').'">&lt; ' . LNG('page.prev') . '</a>&nbsp;&nbsp;';

				echo LNG('page.pagedesc',$_GET['pageid'],$npage);
				echo '&nbsp;&nbsp;';
				echo LNG('page.itemdesc',$data['data']['total'],$startid,min($endid,$data['data']['total']));

				echo '&nbsp;&nbsp;<a onclick="'.($_GET['pageid']>=$npage?'':'turn_page(curr_pageid+1)').'">' . LNG('page.next') . ' &gt;</a>&nbsp;&nbsp;<a onclick="turn_page('.$npage.')">' . LNG('page.last') . '</a></p>';
			}

			exit;
		}

		//歌手名称搜索
		if($_GET['key'][0]=='@') {
			$res=[];
			@$res=kuwo_search_get_json('https://kuwo.cn/api/www/search/searchArtistBykeyWord?key='.urlencode(substr($_GET['key'],1)).'&pn='.$_GET['pageid'].'&rn=50'); //获取歌手列表
			@$res=json_decode($res,true);
			if($res['data']['total']==0) {
				echo LNG('rp.search.null');
				exit;
			}
			if(!isset($res['data']['total'])) {
				echo LNG('rp.search.fail');
				exit;
			}

			$npage=ceil($res['data']['total']/50.0);
			$startid=50*$_GET['pageid']-49;
			$endid=50*$_GET['pageid'];

			echo '<p>' . LNG('page.total',$res['data']['total']) . '</p>' . "\n";
			echo '<ol>' . "\n";
			foreach($res['data']['list'] as $item) {
				printKSingerList($item);
				echo "\n";
			}
			echo '</ol>' . "\n";
			echo '<p><a onclick="kuwo_search(1)">' . LNG('page.first') . '</a>&nbsp;&nbsp;<a onclick="'.($_GET['pageid']<=1?'':'kuwo_search(curr_pageid-1)').'">&lt; ' . LNG('page.prev') . '</a>&nbsp;&nbsp;';

			echo LNG('page.pagedesc',$_GET['pageid'],$npage);
			echo '&nbsp;&nbsp;';
			echo LNG('page.itemdesc',$data['data']['total'],$startid,min($endid,$data['data']['total']));

			echo '&nbsp;&nbsp;<a onclick="'.($_GET['pageid']>=$npage?'':'kuwo_search(curr_pageid+1)').'">' . LNG('page.next') . ' &gt;</a>&nbsp;&nbsp;<a onclick="kuwo_search('.$npage.')">' . LNG('page.last') . '</a></p>';
			echo '<p>' . LNG('rp.search.kuworef','https://kuwo.cn/') .'</p>' . "\n";
			echo '<p>' . LNG('rp.search.notres') . '</p>';
			exit;
		}

		//普通搜索
		$res=[];
		if($_GET['key'][0]!='%') @$res=kuwo_search_get_json('https://kuwo.cn/api/www/search/searchMusicBykeyWord?key='.urlencode($_GET['key']).'&pn='.$_GET['pageid'].'&rn=50'); //常规搜索
		else @$res=kuwo_search_get_json('https://kuwo.cn/api/www/artist/artistMusic?artistid='.urlencode(substr($_GET['key'],1)).'&pn='.$_GET['pageid'].'&rn=50'); //指定歌手
		@$res=json_decode($res,true);
		if($res['code'] == -1) {
			echo LNG('rp.search.null');
			exit;
		}
		if($res['data']['total']==0 || !isset($res['data']['total'])) {
			echo LNG('rp.search.fail');
			exit;
		}

		$npage=ceil($res['data']['total']/50.0);
		$startid=50*$_GET['pageid']-49;
		$endid=50*$_GET['pageid'];

		echo '<p>' . LNG('page.total',$res['data']['total']) . '</p>' . "\n";
		echo '<ol>' . "\n";
		foreach($res['data']['list'] as $item) {
			printRmpList($item);
			echo "\n";
		}
		echo '</ol>' . "\n";
		echo '<p><a onclick="kuwo_search(1,'."'".jsspecial($_GET['key'])."'".')">' . LNG('page.first') . '</a>&nbsp;&nbsp;<a onclick="'.($_GET['pageid']<=1?'':'kuwo_search(curr_pageid-1,'."'".jsspecial($_GET['key'])."'".')').'">&lt; ' . LNG('page.prev') . '</a>&nbsp;&nbsp;';

		echo LNG('page.pagedesc',$_GET['pageid'],$npage);
		echo '&nbsp;&nbsp;';
		echo LNG('page.itemdesc',$res['data']['total'],$startid,min($endid,$res['data']['total']));

		echo '&nbsp;&nbsp;<a onclick="'.($_GET['pageid']>=$npage?'':'kuwo_search(curr_pageid+1,'."'".jsspecial($_GET['key'])."'".')').'">' . LNG('page.next') . ' &gt;</a>&nbsp;&nbsp;<a onclick="kuwo_search('.$npage.','."'".jsspecial($_GET['key'])."'".')">' . LNG('page.last') . '</a></p>';
		echo '<p>' . LNG('rp.search.kuworef','https://kuwo.cn/') .'</p>' . "\n";
		echo '<p>' . LNG('rp.search.notres') . '</p>';
	}
}

// （对于单曲的）爬虫类
class kuwoCrawler {
	public $cache;
	public $success;
	public $cachedDate;
	public $cached;

	//构造初始化
	function __construct() {
		$this->cache=array();
		$this->success=false;
	}

	//将爬虫数据[装入内存]
	//  - 有缓存，且缓存Pretest通过，则取缓存
	//  - 没有缓存，爬取酷我音乐，存入缓存
	// 过期的缓存由GarbageCleaner自动[在enCache之前]删除
	//   * 如果在flag处传入true，那么系统总是会重新抓取并刷新缓存。
	function enCache($id,$flag = false) {
		$cachefile = REMOTE_CACHE.$id.'.json';
		if(!$flag && file_exists($cachefile)) {
			if(time() - filemtime($cachefile) <= _CT('cache_expire')) {
				$this->cache=json_decode(file_get_contents($cachefile),true);
				$ok=true;
				if(!isset($this->cache['name']) || $this->cache['name'] == '') $ok=false;
				if(!isset($this->cache['id'])) $ok=false;
				if(!is_array($this->cache['info'])) $ok=false;
				if(!is_array($this->cache['lrclist']) && $this->cache['lrclist']!==null) {
					$ok=false;
				}
				if($ok) {
					$this->cachedDate=date('Y/m/d H:i:s',filemtime($cachefile)+_CT('timezone'));
					$this->cached = filemtime($cachefile);
					if(isset($this->cache['url'])) {
						unset($this->cache['url']);
						file_put_contents($cachefile,encode_data($this->cache,true));
						$this->cachedDate=date('Y/m/d H:i:s',time()+_CT('timezone'));
						$this->cached = time();
					}
					$this->success=true;
					if(!is_array($this->cache['lrclist']) && $this->cache['lrclist']!==null) {
						$this->success=false;
					}
					return;
				}
			}
		}
		try{
			@$lrc=ex_url_get_contents('https://m.kuwo.cn/newh5/singles/songinfoandlrc?musicId='.$id.'&httpsStatus=1');
			// @$name=file_get_contents('https://www.kuwo.cn/play_detail/'.$id.'/');
			@$lrc=json_decode($lrc,true);
			// @$name=strstr($name,'<input id="songinfo-name" type="hidden" value="');
			// @$name=substr($name,strlen('<input id="songinfo-name" type="hidden" value="'));
			// @$name=substr($name,0,strpos($name,'"'));
			@$this->cache['lrclist']=$lrc['data']['lrclist'];
			@$this->cache['info']=$lrc['data']['songinfo'];
			@$this->cache['name']=$this->cache['info']['songName'];
			@$this->cache['id']=$id;

			$this->cachedDate = date('Y/m/d H:i:s',time()+_CT('timezone'));
			$this->cached = time();

			if($this->cache['name'] != '') $this->success = true;
			else $this->success = false;

			file_put_contents($cachefile,encode_data($this->cache,true));
			// if(!is_array($this->cache['lrclist'])) $this->success=false;
		} catch(Exception $e) {
			$this->success=false;
		}
	}

	// 爬虫信息的附加内容
	function printAddition() {
		$id = $this->cache['id'];
		echo LNG('rp.info.internalid') . COLON . $this->cache['id'] . "\n";
		echo LNG('rp.info.mainpage') . COLON . LNG('rp.info.mainpage.null') . "\n";
		echo LNG('rp.info.lrcdata') . COLON . 'https://kuwo.cn/newh5/singles/songinfoandlrc?musicId='.$id . "\n";
		echo LNG('rp.info.audioinfo') . COLON . 'https://www.kuwo.cn/api/v1/www/music/playUrl?mid='.$this->cache['id'].'&type=music&httpsStatus=1&reqId=' . randomUUID() . "\n";
		echo LNG('rp.info.audioloc') . COLON . $this->url() . "\n";
		echo LNG('rp.info.avatar') . COLON . ($this->cache['info']['pic'] ? $this->cache['info']['pic'] : LNG('rp.info.avatar.null')) . "\n";
		echo LNG('rp.info.last_cache') . COLON . $this->cachedDate . "\n";
		echo LNG('rp.info.cache_life') . COLON . (floatval(_CT('cache_expire'))/86400) . " " . LNG('unit.day') . "\n";
	}

	//获取音频地址并[存入内存]
	//由于大多数音乐网站有[反盗链系统]，通常每次使用音频都要重新获取地址。
	function _url() {
		if(!isset($this->cache['url'])){
			if(_CT('rp_allow_pay_crack')) {
				// @$url=ex_url_get_contents('https://kuwo.cn/bd/search/getSongUrlByMid?mid='.$this->cache['id'].'&format=mp3&br=192kmp3&bdfrom=xshow&c=nfmbhi6fxwaj',['User-Agent'=>'Dalvik/2.1.0 (Linux; U; Android 7.1.2; LIO-AN00 Build/N2G47O)']); // This f*cking thing isn't working
				// @$url=json_decode($url,true);
				// if(!is_array($url) || !is_array($url['data'])) {
				// 	$url = ['data' => ['url' => '']];
				// }
				// $url = $url['data']['url'];
				// $arr = [];
				// preg_match('/(https?:\/\/)(.*?)(\.kuwo\.cn\/.*)/',$url,$arr);
				// if($arr) {
				// 	$url = $arr[1] . str_replace('.','-',$arr[2]) . $arr[3];
				// }
				// $this->cache['url'] = $url;
				@$url=ex_url_get_contents('https://www.kuwo.cn/api/v1/www/music/playUrl?mid='.$this->cache['id'].'&type=convert_url3&br=192kmp3', ['User-Agent'=>'Dalvik/2.1.0 (Linux; U; Android 7.1.2; LIO-AN00 Build/N2G47O)']);
				@$url=json_decode($url,true);
				if(!is_array($url) || !is_array($url['data'])) {
					$url = ['data' => ['url' => '']];
				}
				$this->cache['url']=$url['data']['url'];
			} else {
				@$url=ex_url_get_contents('https://www.kuwo.cn/api/v1/www/music/playUrl?mid='.$this->cache['id'].'&type=music&httpsStatus=1&br=192kmp3&reqId=' . randomUUID());
				@$url=json_decode($url,true);
				if(!is_array($url) || !is_array($url['data'])) {
					$url = ['data' => ['url' => '']];
				}
				$this->cache['url']=$url['data']['url'];
			}
			$this->cache['url'] = str_replace('http://','https://',$this->cache['url']);
		}
		return $this->cache['url'];
	}
	function url($remain_retry = 5) {
		if($remain_retry <= 0) return '';
		$res = $this->_url();
		if('' == trim($res)) {
			unset($this->cache['url']);
			return $this->url($remain_retry - 1);
		}
		return $res;
	}

	function picUrl() {
		if(isset($this->cache['info']['pic'])) {
			$url = $this->cache['info']['pic'];
			$url = str_replace('albumcover/240/', 'albumcover/512/', $url);
			return $url;
		}
		return '';
	}

	//清除歌名的说明文字
	//  - 删去中文括号后的内容 '（'   例子：出山（戏腔改进版） -> 出山
	//  - 删去英文括号后的内容 '('    例子：重返十七岁(<82 bytes omitted>) -> 重返十七岁
	//  - 删除来源信息 '-《' '- 《'   例子：凉凉 -《三生三世》电视剧插曲 -> 凉凉
	// 主要为了防止说明文字太长，导致大标题过长出现问题（UI布局一般不能容许超过20bytes的标题）
	function simplify($txt) {
		$txt = preSubstr($txt,' (');
		$txt = preSubstr($txt,'（');
		$txt = preSubstr($txt,'-《');
		$txt = preSubstr($txt,'- 《');
		return trim($txt);
	}

	//预处理得出歌词文件源代码[并存入内存]
	// 由于生成器一般会修改，不缓存此内容[到文件系统]
	// 该功能时间复杂度极低，每次都执行危害不大。
	function thefile(){
		if(!isset($this->cache['file'])) {
			if($this->cache['lrclist'] === null) {
				//暂无歌词
				$cp = hashed_saturate_gradient($this->cache['name'] . ' - ' . $this->cache['info']['artist']);
				$this->cache['file']=
					'!dataver 201801' . "\n" .
					'[Info]' . "\n" . 
					'N  '.$this->simplify($this->cache['name']) . "\n" . 
					'S  '.$this->cache['info']['artist'] . "\n" . 
					'LA --' . "\n" . 
					'MA --' . "\n" . 
					'C  --' . "\n" . 
					// 'A  #'.substr(md5($this->simplify($this->cache['name'])),0,6) . "\n" . 
					'A  #'.rgb2hex($cp[0]) . "\n" . 
					'G1 #'.rgb2hex($cp[0]) . "\n" . 
					'G2 #'.rgb2hex($cp[1]) . "\n" . 
					'O  '.'https://kuwo.cn/play_detail/'.$this->cache['id'].'/' . "\n" .
					($this->cache['info']['pic'] ? 'P  K_' . $this->cache['id'] . '/avatar' . "\n" : '') .
					'' . "\n" . 
					'// ' . LNG('rp.code.bycrawler') . "\n" . 
					'' . "\n" . 
					'[Para M1 ' . LNG('rp.code.info') . ']' . "\n" . 
					'L - ' . LNG('rp.code.nolrc') . "\n";
			}
			else if(!is_array($this->cache['lrclist'])) {
				//不正确歌词数据（防止乱输入URL导致报错）
				$this->cache['file']=
					'!dataver 201801' . "\n" .
					'[Info]' . "\n" .
					'N  ' . LNG('comp.system_error') . "\n" . 
					'S  --' . "\n" . 
					'LA --' . "\n" . 
					'MA --' . "\n" . 
					'C  --' . "\n" . 
					'A  #000000' . "\n" . 
					'' . "\n" . 
					'// ' . LNG('rp.code.bycrawler') . "\n" . 
					'' . "\n" . 
					'[Comment]' . "\n" . 
					'Txmp Kuwo Crawler. ' . LNG('comp.system_error') . "\n";

			}else{
				//正确生成
				$cp = hashed_saturate_gradient($this->cache['name'] . ' - ' . $this->cache['info']['artist']);
				$this->cache['file']=
					'!dataver 201801' . "\n" .
					'[Info]' . "\n" . 
					'N  '.$this->simplify($this->cache['name']) . "\n" . 
					'S  '.$this->cache['info']['artist'] . "\n" . 
					'LA --' . "\n" . 
					'MA --' . "\n" . 
					'C  '. ($this->cache['info']['album'] ? $this->cache['info']['album'] : '--') . "\n" . 
					// 'A  #'.substr(md5($this->simplify($this->cache['name'])),0,6) . "\n" . 
					'A  #'.rgb2hex($cp[0]) . "\n" . 
					'G1 #'.rgb2hex($cp[0]) . "\n" . 
					'G2 #'.rgb2hex($cp[1]) . "\n" . 
					'O  '.'https://kuwo.cn/play_detail/'.$this->cache['id'].'/' . "\n" .
					($this->cache['info']['pic'] ? 'P  K_' . $this->cache['id'] . '/avatar' . "\n" : '') .
					'' . "\n" .
					'// ' . LNG('rp.code.bycrawler') . "\n" . 
					'' . "\n" . 
					'[Para All ' . LNG('rp.code.content') . ']' . "\n" . 
					'L - ' . LNG('rp.code.kuworef') . "\n" . 
					'L - ' . LNG('rp.code.notres') . "\n";

				foreach($this->cache['lrclist'] as $item) {
					$this->cache['file'].=
						'L '.$item['time'].' '.xmlspecial_unescape($item['lineLyric']) . "\n";
				}


				$this->cache['file'] .= '// ' . LNG('rp.info.last_cache') . '：'.$this->cachedDate;
			}
		}
		return $this->cache['file'];
	}

	function cacheContent() {
		$cachefile=REMOTE_CACHE.$this->cache['id'].'.json';
		return file_get_contents($cachefile);
	}
	function cacheObject() {
		return json_decode($this->cacheContent(),true);
	}

	function cacheTime() {
		$cachefile=REMOTE_CACHE.$this->cache['id'].'.json';
		return filemtime($cachefile);
	}
}

global $akCrawler;
$akCrawler=array();
global $akCrawlerInfo;
$akCrawlerInfo="Txmp Kuwo Music Crawler 0.1.6";
