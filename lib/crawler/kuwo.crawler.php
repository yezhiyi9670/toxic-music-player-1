<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

function kuwo_fuzzy_parseInt($value) {
	if(strstr($value, 'e')) {
		$value = substr($value, 0, strpos($value, 'e'));
	}
	if(doubleval($value) > PHP_INT_MAX) {
		return doubleval($value);
	}
	return intval($value);
}
function kuwo_fuzzy_toString($value) {
	$result = strtolower(strval($value));
	if(strstr($result, 'e')) {
		$result = sprintf("%.16le", $value);
	}
	return strtolower($result);
}

// 酷我音乐令牌加密算法
// Transcripted from kuwo-app-research.js
function kuwo_encrypt_iuvt($value, $key) {
	if(strlen($key) <= 0) {
		return '';
	}

	$n = '';
	for($i = 0; $i < strlen($key); $i++) {
		$n .= strval(ord($key[$i]));
	}

	$r = intval(floor(strlen($n) / 5));
	$o = intval(
		$n[$r] . $n[2 * $r] . $n[3 * $r] . $n[4 * $r] . ($n[5 * $r] ?? '')
	);

	$l = intval(ceil(strlen($key) / 2));
	$c = 2147483647;
	if($o < 2) {
		return '';
	}

	$d = mt_rand(0, 1000000000) % 100000000;
	$n .= sprintf("%d", $d);
	// $times = 0;
	// while(strlen($n) > 10) {
	// 	$n = kuwo_fuzzy_toString(strval(
	// 		kuwo_fuzzy_parseInt(substr($n, 0, 10)) + kuwo_fuzzy_parseInt(substr($n, 10, strlen($n) - 10))
	// 	));
	// 	$times += 1;
	// 	if($times > 24) {
	// 		break;
	// 	}
	// }
	// those above is not working, so dirty hack.
	$n = '599102';

	$n = ($o * intval($n) + $l) % $c;
	$h = '';
	$f = '';
	for($i = 0; $i < strlen($value); $i++) {
		$h = intval(ord($value[$i]) ^ intval(floor($n / $c * 255)));
		$f .= ($h < 16 ? ('0' . dechex($h)) : dechex($h));
		$n = ($o * $n + $l) % $c;
	}
	for($d = dechex($d); strlen($d) < 8; ) {
		$d = '0' . $d;
	}

	return $f . $d;
}

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

	// 随机生成 iuvt
	$str = '0123456789QWERTYUIOPASDFGHJKLKZXCVBNMqwertyuiopasdfghjklzxcvbnm';
	$str0 = 'QWERTYUIOPASDFGHJKLKZXCVBNMqwertyuiopasdfghjklzxcvbnm';
	$iuvt = '';
	for($i = 0; $i < 1; $i ++) {
		$idx = mt_rand(0,strlen($str0) - 1);
		$iuvt .= $str0[$idx];
	}
	for($i = 1; $i < 32; $i ++) {
		$idx = mt_rand(0,strlen($str) - 1);
		$iuvt .= $str[$idx];
	}
	$iuvt_key = 'Hm_Iuvt_cdb524f42f0cer9b268e4v7y735ewrq2324';
	$secret = kuwo_encrypt_iuvt($iuvt, $iuvt_key);

	return ex_url_get_contents($url,[
		'Referer: https://kuwo.cn/',
		'Cookie: kw_token=' . $token . ';' . $iuvt_key . '=' . $iuvt,
		'Secret: ' . $secret,
		'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/116.0',
		'csrf: '.$token,
		'X-Forwarded-For: 223.5.5.5',
	]);
}

// 反复尝试（搜索接口看起来不太稳定）
function kuwo_search_get_json($url, $extras = []) {
	$remain_retry = _CT('rp_search_retry');
	$delay = _CT('rp_search_retry_delay');
	$data = kuwo_search_httpget($url);
	while(trim($data)[0] != '{' && strlen($data) < 400 && $remain_retry > 0) {
	  $remain_retry--;
	  sleep($delay);
	  $data = kuwo_search_httpget($url, $extras);
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

function kuwoSearchSong_print_fail() {
	echo LNG('rp.search.fail', 'try{kuwo_search(curr_pageid,'."'".jsspecial($_GET['key'])."'".')}catch(_){turn_page(curr_pageid)}');
}

// 歌曲搜索接口（直接处理_GET数据，直接输出搜索结果）
function kuwoSearchSong() {
	if(isset($_GET['json'])) {
		header('Content-Type: application/json');
		echo kuwo_search_httpget('https://kuwo.cn/api/www/search/searchMusicBykeyWord?key='.urlencode($_GET['key']).'&pn='.$_GET['pageid'].'&rn=50');
	} else {
		header('Content-Type: text/plain');

		$type_indicator = $_GET['key'][0] ?? '';

		if($type_indicator==':' || preg_match('/^K_(\d+)$/',$_GET['key'])) {
			if($type_indicator != ':') {
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
		if($type_indicator=='>') {
			$purpose = trim(substr($_GET['key'],1));
			if($purpose == '__mp_suggestions__') {
				// 数据串全局匹配歌单信息。
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
		if($type_indicator=='^') {
			$id = trim(substr($_GET['key'],1));
			@$data = json_decode(kuwo_search_get_json('https://kuwo.cn/api/www/playlist/playListInfo?pid='.$id.'&pn='.$_GET['pageid'].'&rn=50'),true);

			if(isset($_GET['raw'])) echo encode_data($data);
			else if(isset($_GET['return'])) {header('Content-Type: text/html');return $data;}
			else {
				$npage=ceil(($data['data']['total'] ?? 0)/50.0);
				$startid=50*$_GET['pageid']-49;
				$endid=50*$_GET['pageid'];

				if(!isset($data['data']['musicList'])) {
					kuwoSearchSong_print_fail();

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
		if($type_indicator=='@') {
			$res=[];
			@$res=kuwo_search_get_json('https://kuwo.cn/api/www/search/searchArtistBykeyWord?key='.urlencode(substr($_GET['key'],1)).'&pn='.$_GET['pageid'].'&rn=50'); //获取歌手列表
			@$res=json_decode($res,true);
			if(($res['data']['total'] ?? 0)==0) {
				echo LNG('rp.search.null');
				exit;
			}
			if(!isset($res['data']['total'])) {
				kuwoSearchSong_print_fail();
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
		if($type_indicator!='%') @$res=kuwo_search_get_json('https://search.kuwo.cn/r.s?client=kt&all='.urlencode($_GET['key']).'&pn='.($_GET['pageid'] - 1).'&rn=50'.'&uid=-1&ver=kwplayer_ar_8.5.4.2&vipver=1&ft=music&encoding=utf8&rformat=json&mobi=1'); //常规搜索
		else @$res=kuwo_search_get_json('https://kuwo.cn/api/www/artist/artistMusic?artistid='.urlencode(substr($_GET['key'],1)).'&pn='.$_GET['pageid'].'&rn=50'); //指定歌手
		@$res=json_decode($res,true);
		if(($res['code'] ?? 0) == -1) {
			echo LNG('rp.search.null');
			exit;
		}
		if(!isset($res['TOTAL'])) {
			$totalCount = $res['data']['total'] ?? 0;
			$songList = $res['data']['list'] ?? [];
		} else {
			$totalCount = $res['TOTAL'] ?? 0;
			$songList = $res['abslist'] ?? [];
		}
		if($totalCount ==0 || !isset($totalCount)) {
			kuwoSearchSong_print_fail();
			exit;
		}

		$npage=ceil($totalCount/50.0);
		$startid=50*$_GET['pageid']-49;
		$endid=50*$_GET['pageid'];

		echo '<p>' . LNG('page.total',$totalCount) . '</p>' . "\n";
		echo '<ol>' . "\n";
		foreach($songList as $item) {
			printRmpList($item);
			echo "\n";
		}
		echo '</ol>' . "\n";
		echo '<p><a onclick="kuwo_search(1,'."'".jsspecial($_GET['key'])."'".')">' . LNG('page.first') . '</a>&nbsp;&nbsp;<a onclick="'.($_GET['pageid']<=1?'':'kuwo_search(curr_pageid-1,'."'".jsspecial($_GET['key'])."'".')').'">&lt; ' . LNG('page.prev') . '</a>&nbsp;&nbsp;';

		echo LNG('page.pagedesc',$_GET['pageid'],$npage);
		echo '&nbsp;&nbsp;';
		echo LNG('page.itemdesc',$totalCount,$startid,min($endid,$totalCount));

		echo '&nbsp;&nbsp;<a onclick="'.($_GET['pageid']>=$npage?'':'kuwo_search(curr_pageid+1,'."'".jsspecial($_GET['key'])."'".')').'">' . LNG('page.next') . ' &gt;</a>&nbsp;&nbsp;<a onclick="kuwo_search('.$npage.','."'".jsspecial($_GET['key'])."'".')">' . LNG('page.last') . '</a></p>';
		echo '<p>' . LNG('rp.search.kuworef','https://kuwo.cn/') .'</p>' . "\n";
		echo '<p>' . LNG('rp.search.notres') . '</p>';
	}
}

global $retrial_remain;
$retrial_remain = 1;

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
		global $retrial_remain;
		
		$cachefile = REMOTE_CACHE.$id.'.json';
		if(!$flag && file_exists($cachefile)) {
			$cache_age = time() - filemtime($cachefile);
			if($cache_age <= _CT('cache_expire')) {
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
				$can_retry = cid() == 'K_' || $id && cid() == 'AK_';
				if(!$can_retry && $retrial_remain > 0) {
					if(mt_rand(0, 99) < 50) {
						$retrial_remain -= 1;
						$can_retry = true;
					}
				}
				if($cache_age > _CT('cache_expire_invalid')) {
					$can_retry = true;
				}
				if(!$can_retry) {
					$this->success=false;
					return;
				}
			}
		}
		try{
			@$lrc=kuwo_search_httpget('https://www.kuwo.cn/newh5/singles/songinfoandlrc?musicId='.$id);
			@$lrc=json_decode($lrc,true);
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
			if(rp_can_pay_play() || true) {
				// API on 2021-10-30
				// @$url=ex_url_get_contents('https://kuwo.cn/bd/search/getSongUrlByMid?mid='.$this->cache['id'].'&format=mp3&br=192kmp3&bdfrom=xshow&c=nfmbhi6fxwaj',['User-Agent'=>'Dalvik/2.1.0 (Linux; U; Android 7.1.2; LIO-AN00 Build/N2G47O)']); // This f*cking thing isn't working
				// show_json(false, $url);
				// @$url=json_decode($url,true);
				// if(!is_array($url) || !is_array($url['data'] ?? null)) {
				// 	$url = ['data' => ['url' => '']];
				// }
				// $url = $url['data']['url'];
				// $arr = [];
				// preg_match('/(https?:\/\/)(.*?)(\.kuwo\.cn\/.*)/',$url,$arr);
				// if($arr) {
				// 	$url = $arr[1] . str_replace('.','-',$arr[2]) . $arr[3];
				// }
				// $this->cache['url'] = $url;

				// Similar one
				// @$url = kuwo_search_httpget('https://antiserver.kuwo.cn/anti.s?type=convert_url2&format=mp3&rid=MUSIC_' . $this->cache['id'] . '&response=url&needanti=0' . randomUUID());
				// show_json(false, $url);
				// if(str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
				// 	preg_match('/(https?:\/\/)(.*?)(\.kuwo\.cn\/.*)/',$url,$arr);
				// 	if($arr) {
				// 		$url = $arr[1] . str_replace('.','-',$arr[2]) . $arr[3];
				// 	}
				// 	$this->cache['url'] = $url;
				// } else {
				// 	$this->cache['url'] = '';
				// }

				// API on 2023-02-25
				// @$url=kuwo_search_httpget('https://www.kuwo.cn/api/v1/www/music/playUrl?mid='.$this->cache['id'].'&type=convert_url3&response=url&httpsStatus=1&br=192kmp3&reqId=' . randomUUID() . '&plat=web_www&from=');
				// show_json(false, $url);
				// @$url=json_decode($url,true);
				// if(!is_array($url) || !is_array($url['data'] ?? null)) {
				// 	$url = ['data' => ['url' => '']];
				// }
				// $this->cache['url']=$url['data']['url'];
				
				// API on 2023-07-19, now working again
				@$url=ex_url_get_contents('https://kuwo.cn/bd/search/getSongUrlByMid?type=convert_url2&format=mp3&bitrate=192&sig=0&mid=' . $this->cache['id'] . '&priority=bitrate&bdfrom=xshow&c=nfmbhi6fxwaj',['User-Agent'=>'Dalvik/2.1.0 (Linux; U; Android 7.1.2; LIO-AN00 Build/N2G47O)']); // This f*cking thing isn't working
				@$url=json_decode($url,true);
				if(!is_array($url) || !is_array($url['data'] ?? null)) {
					$url = ['data' => ['url' => '']];
				}
				$url = $url['data']['url'];
				$arr = [];
				preg_match('/(https?:\/\/)(.*?)(\.kuwo\.cn\/.*)/',$url,$arr);
				if($arr) {
					$url = $arr[1] . str_replace('.','-',$arr[2]) . $arr[3];
				}
				if(strstr($url, '?')) { // sanitizing
					$url = substr($url, 0, strpos($url, '?'));
				}
				$this->cache['url'] = $url;

				// $this->cache['url'] = '';
			} else {
				// Web API url (not working)
				// @$url=kuwo_search_httpget('https://www.kuwo.cn/api/v1/www/music/playUrl?mid='.$this->cache['id'].'&type=music&httpsStatus=1&br=192kmp3&reqId=' . randomUUID() . '&plat=web_www&from=');
				// @$url=json_decode($url,true);
				// if(!is_array($url) || !is_array($url['data'] ?? null)) {
				// 	$url = ['data' => ['url' => '']];
				// }
				// $this->cache['url']=$url['data']['url'];
			}
			$this->cache['url'] = str_replace('http://','https://',$this->cache['url']);
		}
		return $this->cache['url'];
	}
	function url($remain_retry = 2) {
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
