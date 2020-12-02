<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// 防止叉爬虫的 HTTP get 格式
function kuwo_search_httpget($url) {
	// 随机生成 csrf token ，以防被发现。
	$str = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$token = '';
	for($i = 0; $i < 11; $i ++) {
		$idx = mt_rand(0,strlen($str) - 1);
		$token .= $str[$idx];
	}
	return ex_url_get_contents($url,[
		'Referer: http://kuwo.cn/',
		'Cookie: kw_token='.$token,
		'csrf: '.$token,
	]);
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
		echo kuwo_search_httpget('http://kuwo.cn/api/www/search/searchMusicBykeyWord?key='.urlencode($_GET['key']).'&pn='.$_GET['pageid'].'&rn=50');
	}
	else {
		header('Content-Type: text/plain');

		if($_GET['key'][0]==':') {
			$item='K_'.substr($_GET['key'],1);
			if(!isValidMusic($item)) {
				echo LNG('rp.search.no_such_id');
				exit;
			}
			echo '<ul>';
			printIndexList($item);
			echo '</ul>';
			exit;
		}

		// 特殊目的
		if($_GET['key'][0]=='>') {
			$purpose = trim(substr($_GET['key'],1));
			if($purpose == '__mp_suggestions__') {
				// 数据串全局匹配歌单信息。
				$matcher = '/name:"(.+?)",listencnt:([0-9O]+),id:(\d+)/';
				@$text = kuwo_search_httpget('http://kuwo.cn/');

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
					if($ele['listencnt'] == 'O') {
						$ele['listencnt'] = 0;
					}
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
			@$data = json_decode(kuwo_search_httpget('http://kuwo.cn/api/www/playlist/playListInfo?pid='.$id.'&pn='.$_GET['pageid'].'&rn=50'),true);

			$npage=ceil($data['data']['total']/50.0);
			$startid=50*$_GET['pageid']-49;
			$endid=50*$_GET['pageid'];

			if(isset($_GET['raw'])) echo encode_data($data);
			else if(isset($_GET['return'])) {header('Content-Type: text/html');return $data;}
			else {
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
			@$res=kuwo_search_httpget('http://kuwo.cn/api/www/search/searchArtistBykeyWord?key='.urlencode(substr($_GET['key'],1)).'&pn='.$_GET['pageid'].'&rn=50'); //获取歌手列表
			@$res=json_decode($res,true);
			if($res['data']['total']==0 || !isset($res['data']['total'])) {
				echo LNG('rp.search.fail');
				exit;
			}

			$npage=ceil($res['data']['total']/50.0);
			$startid=50*$_GET['pageid']-49;
			$endid=50*$_GET['pageid'];

			echo '<p>' . LNG('page.total',$data['data']['total']) . '</p>' . "\n";
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
			echo '<p>' . LNG('rp.search.kuworef','http://kuwo.cn/') .'</p>' . "\n";
			echo '<p>' . LNG('rp.search.notres') . '</p>';
			exit;
		}

		//普通搜索
		$res=[];
		if($_GET['key'][0]!='%') @$res=kuwo_search_httpget('http://kuwo.cn/api/www/search/searchMusicBykeyWord?key='.urlencode($_GET['key']).'&pn='.$_GET['pageid'].'&rn=50'); //常规搜索
		else @$res=kuwo_search_httpget('http://kuwo.cn/api/www/artist/artistMusic?artistid='.urlencode(substr($_GET['key'],1)).'&pn='.$_GET['pageid'].'&rn=50'); //指定歌手
		@$res=json_decode($res,true);
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
		echo '<p><a onclick="kuwo_search(1)">' . LNG('page.first') . '</a>&nbsp;&nbsp;<a onclick="'.($_GET['pageid']<=1?'':'kuwo_search(curr_pageid-1)').'">&lt; ' . LNG('page.prev') . '</a>&nbsp;&nbsp;';

		echo LNG('page.pagedesc',$_GET['pageid'],$npage);
		echo '&nbsp;&nbsp;';
		echo LNG('page.itemdesc',$data['data']['total'],$startid,min($endid,$res['data']['total']));

		echo '&nbsp;&nbsp;<a onclick="'.($_GET['pageid']>=$npage?'':'kuwo_search(curr_pageid+1)').'">' . LNG('page.next') . ' &gt;</a>&nbsp;&nbsp;<a onclick="kuwo_search('.$npage.')">' . LNG('page.last') . '</a></p>';
		echo '<p>' . LNG('rp.search.kuworef','http://kuwo.cn/') .'</p>' . "\n";
		echo '<p>' . LNG('rp.search.notres') . '</p>';
	}
}

// （对于单曲的）爬虫类
class kuwoCrawler {
	public $cache;
	public $success;
	public $cachedDate;

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
		$cachefile=REMOTE_CACHE.$id.'.json';
		if(!$flag && file_exists($cachefile)) {
			if(time()-filemtime($cachefile) <= _CT('cache_expire')) {
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
					if(isset($this->cache['url'])) {
						unset($this->cache['url']);
						file_put_contents($cachefile,encode_data($this->cache,true));
						$this->cachedDate=date('Y/m/d H:i:s',time()+_CT('timezone'));
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
			@$lrc=file_get_contents('http://kuwo.cn/newh5/singles/songinfoandlrc?musicId='.$id);
			// @$name=file_get_contents('http://www.kuwo.cn/play_detail/'.$id.'/');
			@$lrc=json_decode($lrc,true);
			// @$name=strstr($name,'<input id="songinfo-name" type="hidden" value="');
			// @$name=substr($name,strlen('<input id="songinfo-name" type="hidden" value="'));
			// @$name=substr($name,0,strpos($name,'"'));
			@$this->cache['lrclist']=$lrc['data']['lrclist'];
			@$this->cache['info']=$lrc['data']['songinfo'];
			@$this->cache['name']=$this->cache['info']['songName'];
			@$this->cache['id']=$id;

			file_put_contents($cachefile,encode_data($this->cache,true));
			$this->cachedDate=date('Y/m/d H:i:s',time()+_CT('timezone'));

			if($this->cache['name'] != '') $this->success=true;
			else $this->success = false;
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
		echo LNG('rp.info.lrcdata') . COLON . 'http://kuwo.cn/newh5/singles/songinfoandlrc?musicId='.$id . "\n";
		echo LNG('rp.info.audioinfo') . COLON . 'http://www.kuwo.cn/url?format=mp3&rid='.$this->cache['id'].'&response=url&type=convert_url3&br=192kmp3&from=web&t=1560557591647' . "\n";
		echo LNG('rp.info.audioloc') . COLON . $this->url() . "\n";
		echo LNG('rp.info.avatar') . COLON . ($this->cache['info']['pic'] ? $this->cache['info']['pic'] : LNG('rp.info.avatar.null')) . "\n";
		echo LNG('rp.info.last_cache') . COLON . $this->cachedDate . "\n";
		echo LNG('rp.info.cache_life') . COLON . (floatval(_CT('cache_expire'))/86400) . " " . LNG('unit.day') . "\n";
	}

	//获取音频地址并[存入内存]
	//由于大多数音乐网站有[反盗链系统]，通常每次使用音频都要重新获取地址。
	function url() {
		if(!isset($this->cache['url'])){
			@$url=ex_url_get_contents('http://www.kuwo.cn/url?format=mp3&rid='.$this->cache['id'].'&response=url&type=convert_url3&br=192kmp3&from=web&t=1560557591647');
			@$url=json_decode($url,true);
			$this->cache['url']=str_replace('https://','https://',$url['url']);
		}
		return $this->cache['url'];
	}

	//清除歌名的说明文字
	//  - 删去中文括号后的内容 '（'   例子：出山（戏腔改进版） -> 出山
	//  - 删去英文括号后的内容 '('    例子：重返十七岁(<82 bytes omitted>) -> 重返十七岁
	//  - 删除来源信息 '-《' '- 《'   例子：凉凉 -《三生三世》电视剧插曲 -> 凉凉
	// 主要为了防止说明文字太长，导致大标题过长出现问题（UI布局一般不能容许超过20bytes的标题）
	function simplify($txt) {
		$txt=preSubstr($txt,'(');
		$txt=preSubstr($txt,'（');
		$txt=preSubstr($txt,'-《');
		$txt=preSubstr($txt,'- 《');
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
					'O  '.'http://www.kuwo.cn/play_detail/'.$this->cache['id'].'/' . "\n" .
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
					'O  '.'http://www.kuwo.cn/play_detail/'.$this->cache['id'].'/' . "\n" .
					($this->cache['info']['pic'] ? 'P  K_' . $this->cache['id'] . '/avatar' . "\n" : '') .
					'' . "\n" . 
					'// ' . LNG('rp.code.bycrawler') . "\n" . 
					'' . "\n" . 
					'[Para All ' . LNG('rp.code.content') . ']' . "\n" . 
					'L - ' . LNG('rp.code.kuworef') . "\n" . 
					'L - ' . LNG('rp.code.notres') . "\n";

				foreach($this->cache['lrclist'] as $item) {
					$this->cache['file'].=
						'L '.$item['time'].' '.($item['lineLyric']) . "\n";
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
