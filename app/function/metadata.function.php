<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

function isKuwoId($x) {
	$head = preSubstr($x,'_');
	return $head == 'K' || $head == 'AK';
}

// 获得歌曲音频的【单次】指向替代ID
function getReferenceID($n) {
	$reffile=FILES.$n.'/ref.txt';
	if(file_exists($reffile)) {
		return file_get_contents($reffile);
	}
	return $n;
}

// 获得歌曲音频在文件系统中的位置。并不考虑指向替代，因为指向替代可能指向不在本地的音频。
// 传入的 $d 是搜寻歌曲的绝对目录。返回值是绝对路径。
function getAudioPath($d,$rn=true,$idx=0) {
	// 最后一项 .zip 不是可被接受的歌曲格式，但是会用于一些奇怪的文件存储。
	// .zip 文件会导致歌曲加载失败
	$exts=array(".flac",".mp4",".mp3",".m4a",".wav",".ogg",".aac",".zip");
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
	$exts=array(".flac",".mp4",".mp3",".m4a",".wav",".ogg",".aac",".zip");
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
function isValidMusic($n,$requireAudio=true,$allowRemote=true)
{
	if(isKuwoId($n) && $allowRemote) {
		global $akCrawler;
		global $akCrawlerInfo;
		remoteEncache($n,'K');
		return $akCrawler[$n]->success;
	}
	if($requireAudio==false) return file_exists(FILES.$n."/lyric.txt");
	return file_exists(FILES.$n."/lyric.txt") && getAudioPath(FILES.$n."/song");
}

// 获取歌曲的音频URL。会处理指向替代和remoteplay。
function getAudioUrl($d,$basename="song",$urlname="audio",$idx=0)
{
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
			json_encode(array(
				"list/show"=>false,
				"music/index"=>false,
				"music/code"=>false,
				"music/audio/out"=>false,
				"music/audio/dl"=>false,
				"music/json"=>false,
				"music/download_doc"=>false,
				"music/getdoc"=>false,
				"admin/edit"=>false,
			),JSON_PRETTY_PRINT)
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
		"list/show"=>'从主页访问(L)',
		"music/index"=>'打开播放页面(P)',
		"music/code"=>'查看代码(C)',
		"music/audio/out"=>'播放(K)',
		"music/audio/dl"=>'下载(D)',
		"music/json"=>'API获取歌词(A)',
		"music/download_doc"=>'打开Word下载页(X)',
		"music/getdoc"=>'下载Word文档(W)',
		"admin/edit"=>'编辑(E)',
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
	if(isValidMusic($id) && (getPerm($id)[$t] || is_root())){
		return true;
	}
	else {
		return false;
	}
}
