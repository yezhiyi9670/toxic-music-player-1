<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

class TopLevelRouter {
	function __construct(){

	}

	/*
	URL:
	播放[网页]：/0106
	下载[源]：/0106/download
	文档[网页]：/0106/docs
	代码[网页]：/0106/code
	源音频[源]：/0106/audio
	源文件[源]：/0106/raw
	*/
	public static function _typeURI($u){
		$x=preSubstr($u,"/");
		$localmusic=isValidMusic($x,false,false);
		$localsmusic=isValidMusic($x,true,false);
		$validmusic=isValidMusic($x);
		$canedit=isValidMusic($x,false);
		//return 'music/index';
		// 主页
		if($u=="") {
			return 'home';
		}
		// 歌曲管理区
		if(preg_match('/^admin$/',$u)) {
			return 'admin/index';
		}
		// 管理员登出【不再有效】
		// if(preg_match('/^admin\/logout$/',$u)) {
		//     return 'admin/logout';
		// }
		// 管理员登入【不再有效】
		// if(preg_match('/^admin\/login$/',$u)) {
		//     return 'admin/login';
		// }
		// 管理用户
		if(preg_match('/^admin\/users$/',$u)) {
			return 'admin/users';
		}
		// 用户个人主页
		if(preg_match('/^user$/',$u)) {
			return 'uauth/index';
		}
		// 用户密码修改
		if(preg_match('/^user\/passwd$/',$u)) {
			return 'uauth/passwd';
		}
		// 用户登出
		if(preg_match('/^user\/logout$/',$u)) {
			return 'uauth/logout';
		}
		// 用户登入
		if(preg_match('/^user\/login$/',$u)) {
			return 'uauth/login';
		}
		// 编辑
		if(preg_match('/^(\w+)\/edit$/',$u) && $localmusic) {
			return 'admin/edit';
		}
		// 权限编辑
		if(preg_match('/^(\w+)\/permission$/',$u) && $localmusic) {
			return 'admin/permission';
		}
		// 用户设置
		if(preg_match('/^setting$/',$u)) {
			return 'user/setting';
		}
		// 歌单构造
		if(preg_match('/^list-maker$/',$u)) {
			return 'user/listmaker';
		}
		// 歌曲
		if(preg_match('/^(\w+)$/',$u) && $validmusic){
			return 'music/index';
		}
		// 代码查看
		if(preg_match('/^(\w+)\/code$/',$u) && $canedit){
			return 'music/code';
		}
		// 文档下载前端页面
		if(preg_match('/^(\w+)\/docs$/',$u) && $canedit){
			return 'music/download_doc';
		}
		// 文档下载action页面
		if(preg_match('/^(\w+)\/make-doc$/',$u) && $canedit){
			return 'music/getdoc';
		}
		// 音频获取
		//   * 此处添加一个后缀是为了防止Safari系列浏览器出现问题
		if(preg_match('/^(\w+)\/audio.(\w+)$/',$u) && $validmusic){
			return 'music/audio/out';
		}
		// 音频获取
		if(preg_match('/^(\w+)\/audio$/',$u) && $validmusic){
			return 'music/audio/out';
		}
		// 背景音乐获取
		if(preg_match('/^(\w+)\/background.(\w+)$/',$u) && $localsmusic){
			return 'music/audio/back';
		}
		// 下载
		//   * RemotePlay将会直接导向音频存储CDN处
		if(preg_match('/^(\w+)\/download$/',$u) && $localsmusic){
			return 'music/audio/dl';
		}
		// 歌词json文件 API
		if(preg_match('/^(\w+)\/raw$/',$u) && $validmusic){
			return 'music/json';
		}
		// 歌词HTML API
		if(preg_match('/^(\w+)\/html\/lyric$/',$u) && $validmusic){
			return 'music/hlyric';
		}
		// first-row API
		//   * 歌单菜单、歌曲名称和ID
		if(preg_match('/^(\w+)\/html\/fr$/',$u) && $validmusic){
			return 'music/hfr';
		}
		// thirdrow API
		if(preg_match('/^(\w+)\/html\/tr$/',$u) && $validmusic){
			return 'music/htr';
		}
		// thirdrow-n API
		if(preg_match('/^(\w+)\/html\/trn$/',$u) && $validmusic){
			return 'music/htrn';
		}
		// 元数据 API
		if(preg_match('/^(\w+)\/meta$/',$u) && $validmusic){
			return 'music/hmeta';
		}
		// 所有API数据打包发送（用于歌单切换）
		if(preg_match('/^(\w+)\/switch-all$/',$u) && $validmusic){
			return 'music/all';
		}
		// RemotePlay刷新缓存
		if(preg_match('/^(\w+)\/refresh-cache$/',$u) && $validmusic) {
			return 'music/recache';
		}
		// 用户保存歌单
		if(preg_match('/^playlist\/save-list\/(\d+)$/',$u)) {
			return 'playlist/save';
		}
		// 用户打开编辑歌单
		if(preg_match('/^list-maker\/(\d+)$/',$u)) {
			return 'playlist/edit';
		}
		// 打开用户的歌单并播放
		if(preg_match('/^playlist\/(\w+)\/(\d+)$/',$u)) {
			return 'playlist/play';
		}
		// 输出用户歌单的引用代码（TXMP-js）
		if(preg_match('/^playlist\/(\w+)\/(\d+)\/embed$/',$u)) {
			return 'playlist/embed';
		}
		// 从用户歌单打印
		if(preg_match('/^playlist\/gen-docs\/(\w+)\/(\d+)$/',$u)) {
			return 'playlist/gen';
		}
		// 酷我音乐·推荐歌单
		if(preg_match('/^K_playlist\/(\d+)$/',$u)) {
			return 'remote_playlist/kuwo';
		}
		return 'invalid';
	}

	public static function _checkCSRF() {
		if(isset($_POST['isSubmit']))
		{
			if(!isset($_COOKIE['X-txmp-csrf'][$_POST['csrf-token-name']]) || $_COOKIE['X-txmp-csrf'][$_POST['csrf-token-name']] !== $_POST['csrf-token-value'])
			{
				if(!$_POST['isAjax']) echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("客户端没有提交正确的TOKEN。这可能是CSRF攻击。提示：请刷新页面")</script>';
				else echo '客户端没有提交正确的TOKEN。这可能是CSRF攻击。提示：请刷新页面';
				exit;
			}
		}
		if(!is_array($_COOKIE['X-txmp-csrf']) || count($_COOKIE['X-txmp-csrf'])==0){
			$GLOBALS['sess']=md5(rand()); //创建新会话
			$GLOBALS['token']=md5(rand());
			setcookie('X-txmp-csrf['.$GLOBALS['sess'].']',$GLOBALS['token'],time()+43200,'/');
		} else {
			if(is_array($_COOKIE['X-txmp-csrf'])) foreach($_COOKIE['X-txmp-csrf'] as $k=>$v) {
				$GLOBALS['sess']=$k;
				$GLOBALS['token']=$v;
				break;
			}
		}
	}



	public function route(){
		/*$cr=new kuwoCrawler();
		$cr->enCache('6877870');
		return;*/

		if(!isset($_GET['_lnk'])) $_GET['_lnk']="";
		$urltype=$this->_typeURI($_GET['_lnk']);
		$GLOBALS['linktype']=$urltype;

		$this->_checkCSRF();

		if($urltype!=='user/setting') {
			setting_upd();
		}

		if($urltype==='home') {
			if(!isset($_GET['isSubmit'])) tpl("common/header");
			tpl("list/index");
			tpl("common/footer");
		}
		if($urltype==='music/index')
		{
			checkPermission($urltype);
			tpl("common/header");
			tpl("player/index");
			tpl("common/footer");
		}
		if($urltype==='music/code')
		{
			checkPermission($urltype);
			if(!isset($_GET['raw'])) {
				tpl("common/header");
				tpl("inner/code");
				tpl("common/footer");
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
						echo "Please check the lrc parameter!\n";
					}
				}
			}
		}
		if($urltype==='music/audio/out')
		{
			checkPermission($urltype);
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
				// header("audio/mp3");
				// echo file_get_contents($akCrawler[cid()]->url());
				exit;
			}
			$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/song");
			file_put_out($fn);
		}
		if($urltype==='music/audio/back')
		{
			checkPermission($urltype);
			$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/back");
			if($fn)
				file_put_out($fn);
			else echo "歌曲没有上传消减人声的音频";
		}
		if($urltype==='music/audio/dl')
		{
			checkPermission($urltype);
			$fn=getAudioPath(FILES.preSubstr($_GET["_lnk"])."/song");
			$c=json_decode(parseCmpLyric(preSubstr($_GET["_lnk"])),true);
			file_put_out($fn,true,preSubstr($_GET["_lnk"])." ".$c['meta']['N'].
				substr($fn,strrpos($fn,'.'))
			);
		}
		if($urltype==='music/json')
		{
			checkPermission('music/index');
			header("Content-Type: application/json");
			echo parseCmpLyric(preSubstr($_GET["_lnk"]));
		}
		if($urltype==='music/hlyric')
		{
			checkPermission('music/index');
			header("Content-Type: text/plain");
			tpl("player/lyric");
		}
		if($urltype==='music/hfr')
		{
			checkPermission('music/index');
			header("Content-Type: text/plain");
			tpl("player/firstrow");
		}
		if($urltype==='music/htr')
		{
			checkPermission('music/index');
			header("Content-Type: text/plain");
			tpl("player/thirdrow");
		}
		if($urltype==='music/htrn')
		{
			checkPermission('music/index');
			header("Content-Type: text/plain");
			tpl("player/thirdrow-n");
		}
		if($urltype==='music/hmeta')
		{
			checkPermission('music/index');
			header("Content-Type: application/json");
			tpl("player/meta");
		}
		if($urltype==='music/all') {
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
		}
		if($urltype==='music/download_doc')
		{
			checkPermission($urltype);
			tpl("common/header");
			tpl("inner/docs");
			tpl("common/footer");
		}
		if($urltype==='music/getdoc')
		{
			checkPermission($urltype);
			tpl("inner/generate");
		}

		if($urltype==='admin/edit')
		{
			checkPermission($urltype);
			tpl("common/header");
			tpl("admin/editor");
			tpl("common/footer");
		}
		if($urltype==='admin/index')
		{
			$r = is_root();
			tpl("common/header");
			if(!$r) {
				tpl("errors/401");
			}
			else {
				tpl("admin/index");
			}
			tpl("common/footer");
		}
		if($urltype==='admin/logout') {
			root_logout();
			header('HTTP/1.1 307 Not Authenticated');
			header('Location: '.BASIC_URL.'admin/login');
		}
		if($urltype==='admin/login')
		{
			if(is_root()) {
				header('HTTP/1.1 307 Already Authenticated');
				header('Location: '.BASIC_URL.'admin');
			}
			else {
				if(!isset($_POST['isSubmit'])) tpl("common/header");
				tpl("admin/login");
				if(!isset($_POST['isSubmit'])) tpl("common/footer");
			}
		}
		if($urltype==='admin/users')
		{
			if(!isset($_POST['isAjax'])) tpl("common/header");
			if(!is_root()) {
				tpl("errors/401");
			}
			else {
				tpl("admin/users");
			}
			tpl("common/footer");
		}
		if($urltype==='uauth/index')
		{
			if(!uauth_username()) {
				header('HTTP/1.1 307 Not Authenticated');
				header('Location: '.BASIC_URL.'user/login');
				exit;
			}
			tpl("common/header");
			tpl("uauth/index");
			tpl("common/footer");
		}
		if($urltype==='uauth/passwd')
		{
			if(!uauth_username()) {
				header('HTTP/1.1 307 Not Authenticated');
				header('Location: '.BASIC_URL.'user/login');
				exit;
			}
			tpl("common/header");
			tpl("uauth/passwd");
			tpl("common/footer");
		}
		if($urltype==='uauth/logout') {
			uauth_logout();
			header('HTTP/1.1 307 Not Authenticated');
			header('Location: '.BASIC_URL.'user/login');
		}
		if($urltype==='uauth/login')
		{
			if(!!uauth_username()) {
				header('HTTP/1.1 307 Already Authenticated');
				header('Location: '.BASIC_URL.'user');
			}
			else {
				if(!isset($_POST['isSubmit'])) tpl("common/header");
				tpl("uauth/login");
				if(!isset($_POST['isSubmit'])) tpl("common/footer");
			}
		}
		if($urltype==='admin/permission') {
			tpl("common/header");
			if(!is_root()) {
				tpl("errors/401");
			}
			else {
				tpl("admin/permission");
			}
			tpl("common/footer");
		}
		if($urltype==="user/setting") {
			if($_POST['isSubmit']!='yes') tpl('common/header');
			tpl('user/setting');
			tpl('common/footer');
		}
		if($urltype==="user/listmaker") {
			tpl('common/header');
			tpl('user/listmaker');
			tpl('common/footer');
		}
		if($urltype==='music/recache') {
			remoteEncache(cid(),'K',true);
			global $akCrawler;
			if($akCrawler[cid()] -> success) echo 'Success';
			else echo 'Failed';
		}
		if($urltype==='playlist/save') {
			tpl('uauth/savelist');
		}
		if($urltype==='playlist/edit') {
			$id = preg_match_return('/^list-maker\/(\d+)$/',$_GET['_lnk'])[1];
			if(uauth_username() && hasPlaylist(uauth_username(),$id) || uauth_has_data(uauth_username(),'playlist',$id.'.csv')) {
				tpl('common/header');
				tpl('user/listmaker');
				tpl('common/footer');
			}
			else if(uauth_username()) {
				header("HTTP/1.1 404 Not Found");
				tpl('common/header');tpl('errors/404');tpl('common/footer');
			}
			else {
				header("HTTP/1.1 401 Login Required");
				tpl('common/header');tpl('errors/401');tpl('common/footer');
			}
		}
		if($urltype==='playlist/play') {
			$arr = preg_match_return('/^playlist\/(\w+)\/(\d+)$/',$_GET['_lnk']);
			if(!hasPlaylist($arr[1],$arr[2])) {
				header("HTTP/1.1 404 Not Found");
				tpl('common/header');tpl('errors/404');tpl('common/footer');
			} else if(!is_root() && !readPlaylistData($arr[1],$arr[2])['public'] && uauth_username() != $arr[1]) {
				header("HTTP/1.1 401 Private Content");
				tpl('common/header');tpl('errors/401');tpl('common/footer');
			} else {
				$data = readPlaylistData($arr[1],$arr[2]);
				$_GET['_lnk'] = $data['playlist'][0]['id'];
				checkPermission('music/index');
				$GLOBALS['listname'] = $arr[1];
				$GLOBALS['listid'] = $arr[2];
				if(!isset($_GET['raw'])) {
					tpl('common/header');
					tpl('player/index');
					tpl('common/footer');
				} else if(isset($_GET['json'])) {
					header('Content-Type: application/json');
					echo json_encode($data,JSON_UNESCAPED_SLASHES+JSON_UNESCAPED_UNICODE);
				} else {
					header('Content-Type: text/plain');
					echo readPlaylistData($arr[1],$arr[2],true);
				}
			}
		}
		if($urltype==='playlist/embed') {
			$arr = preg_match_return('/^playlist\/(\w+)\/(\d+)\/embed$/',$_GET['_lnk']);
			if(!hasPlaylist($arr[1],$arr[2])) {
				header("HTTP/1.1 404 Not Found");
				tpl('common/header');tpl('errors/404');tpl('common/footer');
			} else if(!is_root() && !readPlaylistData($arr[1],$arr[2])['public'] && uauth_username() != $arr[1]) {
				header("HTTP/1.1 401 Private Content");
				tpl('common/header');tpl('errors/401');tpl('common/footer');
			} else {
				$GLOBALS['listname'] = $arr[1];
				$GLOBALS['listid'] = $arr[2];
				header('Content-Type: text/javascript');
				tpl('player/embed');
			}
		}
		if($urltype==='playlist/gen') {
			$arr = preg_match_return('/^playlist\/gen-docs\/(\w+)\/(\d+)$/',$_GET['_lnk']);
			if(!hasPlaylist($arr[1],$arr[2])) {
				header("HTTP/1.1 404 Not Found");
				tpl('common/header');tpl('errors/404');tpl('common/footer');
			} else if(!is_root() && !readPlaylistData($arr[1],$arr[2])['public'] && uauth_username() != $arr[1]) {
				header("HTTP/1.1 401 Private Content");
				tpl('common/header');tpl('errors/401');tpl('common/footer');
			} else {
				$_GET['_lnk'] = readPlaylistData($arr[1],$arr[2])['playlist'][0]['id'];
				checkPermission('music/getdoc');
				$GLOBALS['listname'] = $arr[1];
				$GLOBALS['listid'] = $arr[2];
				tpl('common/header');
				tpl('inner/docs');
				tpl('common/footer');
			}
		}
		if($urltype==='remote_playlist/kuwo') {
			$GLOBALS['remote_playlist_id'] = preg_match_return('/^K_playlist\/(\d+)$/',$_GET['_lnk'])[1];
			$_GET['_lnk'] = '$FFA000';
			$_GET['return'] = true;
			$_GET['key'] = '^' . $GLOBALS['remote_playlist_id'];
			$_GET['pageid'] = '1';
			$GLOBALS['remote_playlist'] = kuwoSearchSong();
			if($GLOBALS['remote_playlist']['code'] != 200) {
				header("HTTP/1.1 404 Not Found");
				tpl('common/header');tpl('errors/404');tpl('common/footer');
			} else {
				tpl('common/header');
				tpl('remote_playlist/kuwo');
				tpl('common/footer');
			}
		}
		if($urltype==="invalid")
		{
			header("HTTP/1.1 404 Not Found");
			tpl("common/header");
			tpl("errors/404");
			tpl("common/footer");
		}
	}
}
