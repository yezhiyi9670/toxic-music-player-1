<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// 码率与品质的关系。返回品质描述词。
function bitrate_tag($x,$useName=true) {
	if($x <= -1) {
		return '<span class="txmp-tag tag-quality-ll txmp-tag-quality">' . fa_icon('file') . ($useName ? LNG('quality.400') : $x . 'k') . '</span>';
	} else if($x <= 32) {
		return '<span class="txmp-tag tag-deep-orange-l txmp-tag-quality">' . fa_icon('file') . ($useName ? LNG('quality.24') : $x . 'k') . '</span>';
	} else if($x <= 64) {
		return '<span class="txmp-tag tag-quality-lq txmp-tag-quality">' . fa_icon('file') . ($useName ? LNG('quality.48') : $x . 'k') . '</span>';
	} else if($x <= 160) {
		return '<span class="txmp-tag tag-quality-mq txmp-tag-quality">' . fa_icon('file') . ($useName ? LNG('quality.128') : $x . 'k') . '</span>';
	} else if($x <= 256) {
		return '<span class="txmp-tag tag-quality-hq txmp-tag-quality">' . fa_icon('file') . ($useName ? LNG('quality.192') : $x . 'k') . '</span>';
	} else {
		return '<span class="txmp-tag tag-quality-sq txmp-tag-quality">' . fa_icon('file') . ($useName ? LNG('quality.320') : $x . 'k') . '</span>';
	}
}

// 最近修改
function modifiedTime($u) {
	$modified = -1;
	if(isKuwoId($u)) {
		global $akCrawler;
		remoteEncache($u,'K');
		$modified = $akCrawler[$u]->cached;
	} else {
		$modified = filemtime(FILES . $u . '/lyric.txt');
		$au_loc = getAudioPath(FILES . $u . '/song');
		if($au_loc) {
			$modified = max($modified,filemtime($au_loc));
		}
	}
	return $modified;
}

// 可接受摘要图片扩展名
function getAllowedPictureExt() {
	return ['.bmp','.png','.jpg','.webp','.svg','.tif','.tiff','.ico'];
}

// 图片扩展名是否可接受
function isPictureExtAllowed($x) {
	return in_array($x,getAllowedPictureExt());
}

/**
 * 搜寻摘要图片
 * 注意：传入的是需要搜寻的绝对路径
 */
function getPicturePath($d,$rn=true) {
	$exts = getAllowedPictureExt();
	foreach($exts as $ext)
	{
		if(file_exists($d.$ext))
		{
			if($rn) return $d.$ext;
			else return true;
		}
	}
	return false;
}

// 编码 json
function encode_data($data, $isStore = false) {
	$flag = 0;
	if(!$isStore && _CT('debug')) {
		$flag = JSON_PRETTY_PRINT;
	}

	return json_encode($data,$flag+JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE);
}

// 是否为 kuwo RemotePlay ID
function isKuwoId($x) {
	$head = preSubstr($x,'_');
	return $head == 'K' || $head == 'AK';
}

// 获取付费信息（不是真的付费）
function paymentStatus($n) {
	if(isKuwoId($n)) {
		global $akCrawler;
		global $akCrawlerInfo;
		remoteEncache($n,'K');
		return kuwoPayStatus($akCrawler[$n]->cache['info']['pay']);
	} else {
		return kuwoPayStatus(0);
	}
}

// 获取音频分析信息
function getAudioAnalysis($n) {
	$n = getFinalRefer($n);
	if($n == '') {
		return null;
	}
	if(isKuwoId($n)) {
		global $akCrawler;
		global $akCrawlerInfo;
		remoteEncache($n,'K');
		return $akCrawler[$n]->success ? [
			'format' => 'mp3',
			'bitrate' => 192,
			'time' => intval($akCrawler[$n]->cache['info']['duration'])
		] : null;
	} else {
		$data = [];
		$au_path = getAudioPath(FILES . $n . '/song',true);
		if(!file_exists($au_path)) {
			return null;
		}
		$cache_path = FILES . $n . '/analyze-cache.json';
		if(!file_exists($cache_path) || filemtime($cache_path) < filemtime($au_path) || filemtime($cache_path) < filemtime(__FILE__) || !file_exists($au_path)) {
			$data = analyzeAudio($au_path);
			file_put_contents($cache_path,encode_data($data,true));
		} else {
			$data = json_decode(file_get_contents($cache_path),true);
		}

		return $data;
	}
}

// 获得歌曲音频的【单次】指向替代ID
function getReferenceID($n) {
	$reffile=FILES.$n.'/ref.txt';
	if(file_exists($reffile)) {
		$k = trim(file_get_contents($reffile));
		if(!$k) {
			$k = $n;
		}
		return $k;
	}
	return $n;
}

// 查找最终指向 ID
function getFinalRefer($u,$idx=0) {
	if(getReferenceID($u) != $u) {
		if($idx>=100) return '';
		return getFinalRefer(getReferenceID($u),$idx+1);
	}
	return $u;
}

// 获取可接受的后缀
function getAllowedAudioExt() {
	return [".flac",".mp4",".mp3",".m4a",".wav",".ogg",".aac",".zip"];
}

// 是否可接受后缀
function isAudioExtAllowed($t) {
	return in_array($t,getAllowedAudioExt());
}

/**
 * 获得歌曲音频在文件系统中的位置。并不考虑指向替代，因为指向替代可能指向不在本地的音频。
 * 传入的 $d 是搜寻歌曲的绝对目录。返回值是绝对路径。
 */
function getAudioPath($d,$rn=true,$idx=0) {
	// 最后一项 .zip 不是可被接受的歌曲格式，但是会用于一些奇怪的文件存储。
	// .zip 文件会导致歌曲加载失败
	$exts = getAllowedAudioExt();
	foreach($exts as $ext)
	{
		if(file_exists($d.$ext))
		{
			if($rn) return $d.$ext;
			else return true;
		}
	}
	return false;
}

// 音频文件修改日期
function getAudioMtime($d) {
	// 最后一项 .zip 不是可被接受的歌曲格式，但是会用于一些奇怪的文件存储。
	// .zip 文件会导致歌曲加载失败
	$exts = getAllowedAudioExt();
	foreach($exts as $ext)
	{
		if(file_exists($d.$ext))
		{
			return filemtime($d . $ext);
		}
	}
	return 0;
}

// 获取音频的下载地址，会处理指向替代。
function getDownloadUrl($u,$idx=0) {
	if(getReferenceID($u) != $u) {
		if($idx>=100) return '';
		return getDownloadUrl(getReferenceID($u),$idx+1);
	}
	if(isKuwoId($u)) {
		return BASIC_URL.$u.'/audio.mp3'; //redirect
	}
	return BASIC_URL.$u.'/download';
	//return getAudioUrl($u);
}

// 是否是有效的歌曲ID
// $requireAudio：是否要求有音频（无论是否有效）
// $allowRemote：是否认为remoteplay歌曲有效
function isValidMusic($n,$requireAudio=true,$allowRemote=true) {
	if(!preg_match('/^(\w+)$/',$n)) return false;
	if(isKuwoId($n) && $allowRemote) {
		global $akCrawler;
		global $akCrawlerInfo;
		remoteEncache($n,'K');
		return $akCrawler[$n]->success && (!$requireAudio || _CT('rp_allow_pay_crack') || !paymentStatus($n)['pay_play']);
	}
	if($requireAudio==false) return file_exists(FILES.$n."/lyric.txt");
	return file_exists(FILES.$n."/lyric.txt") && getAudioAnalysis($n) != null;
}

// 获取歌曲的音频URL。会处理指向替代和remoteplay。
function getAudioUrl($d,$basename="song",$urlname="audio",$idx=0) {
	if(getReferenceID($d) != $d) {
		if($idx>=100) return '';
		return getAudioUrl(getReferenceID($d),$basename,$urlname,$idx+1);
	}
	if(isKuwoId($d)) {
		return BASIC_URL.$d.'/'.$urlname.'.mp3'; //Return raw url. (This will be attended to in frontend js)
	}
	$i=$d;
	$g=getAudioPath(FILES.$i."/".$basename);
	return BASIC_URL.$i."/".$urlname.substr($g,strrpos($g,'.'));
	// return BASIC_URL.'data/music/'.$d.'/'.$basename.substr($g,strrpos($g,'.'));
}

// 获取歌曲的封面图URL
function getCoverUrl($n) {
	$picture_info = GSM($n)['P'];
	$pic_url = '';
	if(!$picture_info || strlen($picture_info) == 0) {
		$pic_url = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
	} else if(!str_included($picture_info,['//','data:'])) {
		$pic_url =  BASIC_URL . $picture_info;
	} else {
		$pic_url = $picture_info;
	}
	return $pic_url;
}

// 获取当前歌曲或指定歌曲的meta
global $compilation_cache;
$compilation_cache = [];
function __getCmpLyric2($x) {
	global $compilation_cache;
	if(!isset($compilation_cache[$x])) {
		$data = json_decode(parseCmpLyric(preSubstr($x)),true);
		$noColorSwitch = setting_gt('no-color-switch','Y');
		$noColorSwitch = ($noColorSwitch == 'Y' || $noColorSwitch == 'y');
		if($noColorSwitch) {
			$data['meta']['A'] = MAIN_COLOR;
			$data['meta']['X'] = darkenColorHex(MAIN_COLOR);
			$data['meta']['G1'] = GC_COLOR_1;
			$data['meta']['G2'] = GC_COLOR_2;
		}
		$compilation_cache[$x] = $data;
	}
	return $compilation_cache[$x];
}
function GSM($x) {
	return __getCmpLyric2($x)['meta'];
}
function GCM() {
	$t = GSM(cid());
	if(cid()[0] == '$') {
		$t['X'] = $t['A'] = substr(cid(),1);
	}
	return $t;
}

// 获取当前或指定URL的歌曲ID
function cid(){
	return preSubstr($_GET['_lnk']);
}
function sid($x){
	return preSubstr($x);
}

// 获得某歌曲的权限数组
function getPerm($x) {
	if(isKuwoId(sid($x))) {
		global $akCrawler;
		global $akCrawlerInfo;
		remoteEncache(sid($x),'K');
		if($akCrawler[sid($x)]->success) {
			return array(
				"list/show"=>true,
				"music/index"=>true,
				"music/code"=>true,
				"music/audio/out"=>true,
				"music/audio/dl"=>true,
				"music/json"=>true,
				"music/download_doc"=>true,
				"music/getdoc"=>true,
				"admin/edit"=>false,
			);
		}
		else return array(
			"list/show"=>false,
			"music/index"=>false,
			"music/code"=>false,
			"music/audio/out"=>false,
			"music/audio/dl"=>false,
			"music/json"=>false,
			"music/download_doc"=>false,
			"music/getdoc"=>false,
			"admin/edit"=>false,
		);
	}
	
	if(!file_exists(FILES.sid($x).'/permission.json')) {
		if(!file_exists(FILES.sid($x).'/')) return [];
		file_put_contents(FILES.sid($x).'/permission.json',
			encode_data(array(
				"list/show"=>false,
				"music/index"=>false,
				"music/code"=>false,
				"music/audio/out"=>false,
				"music/audio/dl"=>false,
				"music/json"=>false,
				"music/download_doc"=>false,
				"music/getdoc"=>false,
				"admin/edit"=>false,
			),true)
		);
	}
	
	$t = json_decode(file_get_contents(FILES.sid($x).'/permission.json'),true);
	// 向下兼容：v126a5c 之前的版本没有将list/show从music/index中分离。
	if(!isset($t['list/show'])) {
		$t['list/show'] = $t['music/index'];
	}
	return $t;
}

// 获得权限对应的显示名称（仅管理界面）
function permissionNames($i = "") {
	$arr = array(
		"list/show"=>LNG('perm.list.show'),
		"music/index"=>LNG('perm.index'),
		"music/code"=>LNG('perm.code'),
		"music/audio/out"=>LNG('perm.play'),
		"music/audio/dl"=>LNG('perm.dl'),
		"music/json"=>LNG('perm.api'),
		"music/download_doc"=>LNG('perm.doc'),
		"music/getdoc"=>LNG('perm.doc.action'),
		"admin/edit"=>LNG('perm.edit'),
	);
	if($i) return $arr[$i];
	return $arr;
}

// 获得歌曲权限的缩写
// 传入的参数是权限数组
function permissionMarks($a) {
	$marks = array(
		"list/show" => 'L',
		"music/index"=>'P',
		"music/code"=>'C',
		"music/audio/out"=>'K',
		"music/audio/dl"=>'D',
		"music/json"=>'A',
		"music/download_doc"=>'X',
		"music/getdoc"=>'W',
		"admin/edit"=>'E',
	);
	$egg = "";
	$arr = $a;
	foreach($marks as $k=>$v) {
		if(isset($marks[$k])) {
			if($arr[$k]==true)
				$egg.=$v;
			else
				$egg.='-';
		}
	}
	return $egg;
}

// 检查权限。此函数检查失败会直接转向401页面。
function checkPermission($t,$id="") {
	if($id==="") $id=cid();
	if(!preg_match('/^(\w+)$/',$id)){
		print404("Illegal Data");
	}
	if(isValidMusic($id,false) && (getPerm($id)[$t] || is_root())){
		return;
	}
	else {
		print401("Permission Denied");
	}
}

// 检查权限。此函数返回检查的结果（true/false）
function _checkPermission($t,$id="") {
	if($id==="") $id=cid();
	if(!preg_match('/^(\w+)$/',$id)){
		print404("Illegal Data");
	}
	if(isValidMusic($id,false) && (getPerm($id)[$t] || is_root())){
		return true;
	}
	else {
		return false;
	}
}
