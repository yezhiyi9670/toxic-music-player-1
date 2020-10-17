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
				echo '该编号的歌曲不存在。';
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
				$matcher = '/name:"(.+?)",listencnt:(\d+),id:(\d+)/';
				@$text = kuwo_search_httpget('http://kuwo.cn/');
				$lst = [];
				preg_match_all($matcher,$text,$lst);

				// var_dump($lst);

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
				echo '不合文法。';
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

			if(isset($_GET['raw'])) echo json_encode($data,JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE+JSON_UNESCAPED_SLASHES);
			else if(isset($_GET['return'])) {header('Content-Type: text/html');return $data;}
			else {
				if(!isset($data['data']['musicList'])) {
					echo '查询失败，请重试。';

					exit;
				}

				echo '<p>共有 '.$data['data']['total'].' 个项目</p>'."\n";
				echo '<ol>';
				foreach($data['data']['musicList'] as $item) {
					printRmpList($item);
				}
				echo '</ol>';
				echo '<p><a onclick="turn_page(1)">最前</a>&nbsp;&nbsp;<a onclick="'.($_GET['pageid']<=1?'':'turn_page(curr_pageid-1)').'">&lt; 上一页</a>&nbsp;&nbsp;';

				echo '页面'.$_GET['pageid'].'/'.$npage;
				echo '&nbsp;&nbsp;';
				echo ''.$data['data']['total'].'个中的第'.$startid.'-'.min($endid,$data['data']['total']).'个';

				echo '&nbsp;&nbsp;<a onclick="'.($_GET['pageid']>=$npage?'':'turn_page(curr_pageid+1)').'">下一页 &gt;</a>&nbsp;&nbsp;<a onclick="turn_page('.$npage.')">最后</a></p>';
			}

			exit;
		}

		//歌手名称搜索
		if($_GET['key'][0]=='@') {
			$res=[];
			@$res=kuwo_search_httpget('http://kuwo.cn/api/www/search/searchArtistBykeyWord?key='.urlencode(substr($_GET['key'],1)).'&pn='.$_GET['pageid'].'&rn=50'); //获取歌手列表
			@$res=json_decode($res,true);
			if($res['data']['total']==0 || !isset($res['data']['total'])) {
				echo '搜索失败。请重试。';
				exit;
			}

			$npage=ceil($res['data']['total']/50.0);
			$startid=50*$_GET['pageid']-49;
			$endid=50*$_GET['pageid'];

			echo '<p>找到了 '.$res['data']['total'].' 个项目</p>'."\n";
			echo '<ol>'."\n";
			foreach($res['data']['list'] as $item) {
				printKSingerList($item);
				echo "\n";
			}
			echo '</ol>'."\n";
			echo '<p><a onclick="kuwo_search(1)">最前</a>&nbsp;&nbsp;<a onclick="'.($_GET['pageid']<=1?'':'kuwo_search(curr_pageid-1)').'">&lt; 上一页</a>&nbsp;&nbsp;';

			echo '页面'.$_GET['pageid'].'/'.$npage;
			echo '&nbsp;&nbsp;';
			echo ''.$res['data']['total'].'个中的第'.$startid.'-'.min($endid,$res['data']['total']).'个';

			echo '&nbsp;&nbsp;<a onclick="'.($_GET['pageid']>=$npage?'':'kuwo_search(curr_pageid+1)').'">下一页 &gt;</a>&nbsp;&nbsp;<a onclick="kuwo_search('.$npage.')">最后</a></p>';
			echo '<p>如果需要更加宽泛的搜索，请去<a href="http://kuwo.cn/" target="_blank">酷我音乐</a></p>'."\n";
			echo '<p>搜索结果根据从酷我音乐抓取的信息显示。本站系统不对搜索结果负责。</p>';
			exit;
		}

		//普通搜索
		$res=[];
		if($_GET['key'][0]!='%') @$res=kuwo_search_httpget('http://kuwo.cn/api/www/search/searchMusicBykeyWord?key='.urlencode($_GET['key']).'&pn='.$_GET['pageid'].'&rn=50'); //常规搜索
		else @$res=kuwo_search_httpget('http://kuwo.cn/api/www/artist/artistMusic?artistid='.urlencode(substr($_GET['key'],1)).'&pn='.$_GET['pageid'].'&rn=50'); //指定歌手
		@$res=json_decode($res,true);
		if($res['data']['total']==0 || !isset($res['data']['total'])) {
			echo '搜索失败。请重试。';
			exit;
		}

		$npage=ceil($res['data']['total']/50.0);
		$startid=50*$_GET['pageid']-49;
		$endid=50*$_GET['pageid'];

		echo '<p>找到了'.$res['data']['total'].'个项目</p>'."\n";
		echo '<ol>'."\n";
		foreach($res['data']['list'] as $item) {
			printRmpList($item);
			echo "\n";
		}
		echo '</ol>'."\n";
		echo '<p><a onclick="kuwo_search(1)">最前</a>&nbsp;&nbsp;<a onclick="'.($_GET['pageid']<=1?'':'kuwo_search(curr_pageid-1)').'">&lt; 上一页</a>&nbsp;&nbsp;';

		echo '页面'.$_GET['pageid'].'/'.$npage;
		echo '&nbsp;&nbsp;';
		echo ''.$res['data']['total'].'个中的第'.$startid.'-'.min($endid,$res['data']['total']).'个';

		echo '&nbsp;&nbsp;<a onclick="'.($_GET['pageid']>=$npage?'':'kuwo_search(curr_pageid+1)').'">下一页 &gt;</a>&nbsp;&nbsp;<a onclick="kuwo_search('.$npage.')">最后</a></p>';
		echo '<p>如果需要更加宽泛的搜索，请去<a href="http://kuwo.cn/" target="_blank">酷我音乐</a></p>'."\n";
		echo '<p>搜索结果根据从酷我音乐抓取的信息显示。本站系统不对搜索结果负责。</p>';
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
						file_put_contents($cachefile,json_encode($this->cache,JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE));
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

			file_put_contents($cachefile,json_encode($this->cache,JSON_PRETTY_PRINT+JSON_UNESCAPED_UNICODE));
			$this->cachedDate=date('Y/m/d H:i:s',time()+_CT('timezone'));

			if($this->cache['name'] != '') $this->success=true;
			else $this->success = false;
			if(!is_array($this->cache['lrclist'])) $this->success=false;
		} catch(Exception $e) {
			$this->success=false;
		}
	}

	// 爬虫信息的附加内容
	function printAddition() {
		$id = $this->cache['id'];
		echo "内部ID：".$this->cache['id']."\n";
		echo "主页面：（不抓取主页面）"."\n";
		echo "歌词数据：".'http://kuwo.cn/newh5/singles/songinfoandlrc?musicId='.$id."\n";
		echo "音频信息：".'http://www.kuwo.cn/url?format=mp3&rid='.$this->cache['id'].'&response=url&type=convert_url3&br=128kmp3&from=web&t=1560557591647'."\n";
		echo "音频地址（短时间有效）：".$this->url()."\n";
		echo "最后一次缓存：".$this->cachedDate."\n";
		echo "缓存时长：".(floatval(_CT('cache_expire'))/86400)." 天"."\n";
	}

	//获取音频地址并[存入内存]
	//由于大多数音乐网站有[反盗链系统]，通常每次使用音频都要重新获取地址。
	function url() {
		if(!isset($this->cache['url'])){
			@$url=file_get_contents('http://www.kuwo.cn/url?format=mp3&rid='.$this->cache['id'].'&response=url&type=convert_url3&br=128kmp3&from=web&t=1560557591647');
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
					'[Info]'."\n".
					'N  '.$this->simplify($this->cache['name'])."\n".
					'S  '.$this->cache['info']['artist']."\n".
					'LA --'."\n".
					'MA --'."\n".
					'C  --'."\n".
					// 'A  #'.substr(md5($this->simplify($this->cache['name'])),0,6)."\n".
					'A  #'.rgb2hex($cp[0])."\n".
					'G1 #'.rgb2hex($cp[0])."\n".
					'G2 #'.rgb2hex($cp[1])."\n".
					'O  '.'http://www.kuwo.cn/play_detail/'.$this->cache['id'].'/'."\n".
					''."\n".
					'// 此文件由爬虫生成'."\n".
					''."\n".
					'[Para M1 提示]'."\n".
					'L - 暂无歌词'."\n";
			}
			else if(!is_array($this->cache['lrclist'])) {
				//不正确歌词数据（防止乱输入URL导致报错）
				$this->cache['file']=
					'[Info]'."\n".
					'N  系统错误'."\n".
					'S  --'."\n".
					'LA --'."\n".
					'MA --'."\n".
					'C  --'."\n".
					'A  #000000'."\n".
					''."\n".
					'// 此文件由爬虫生成'."\n".
					''."\n".
					'[Comment]'."\n".
					'Txmp Kuwo Crawler. 系统错误，无法生成'."\n";

			}else{
				//正确生成
				$cp = hashed_saturate_gradient($this->cache['name'] . ' - ' . $this->cache['info']['artist']);
				$this->cache['file']=
					'[Info]'."\n".
					'N  '.$this->simplify($this->cache['name'])."\n".
					'S  '.$this->cache['info']['artist']."\n".
					'LA --'."\n".
					'MA --'."\n".
					'C  --'."\n".
					// 'A  #'.substr(md5($this->simplify($this->cache['name'])),0,6)."\n".
					'A  #'.rgb2hex($cp[0])."\n".
					'G1 #'.rgb2hex($cp[0])."\n".
					'G2 #'.rgb2hex($cp[1])."\n".
					'O  '.'http://www.kuwo.cn/play_detail/'.$this->cache['id'].'/'."\n".
					''."\n".
					'// 此文件由爬虫生成'."\n".
					''."\n".
					'[Para All 歌词内容]'."\n".
					'L - 歌曲抓取自：酷我音乐'."\n".
					'L - 本站与本软件不对内容负责'."\n";

				foreach($this->cache['lrclist'] as $item) {
					$this->cache['file'].=
						'L '.$item['time'].' '.($item['lineLyric'])."\n";
				}


				$this->cache['file'] .= '// 最近缓存时间：'.$this->cachedDate;
			}
		}
		return $this->cache['file'];
	}

	function cacheContent() {
		$cachefile=REMOTE_CACHE.$this->cache['id'].'.json';
		return file_get_contents($cachefile);
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
