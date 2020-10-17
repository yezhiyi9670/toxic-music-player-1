<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// 主页上的列表
function printIndexList($item,$url=true) {
	echo '<li style="color:#'.htmlspecial2(GSM($item)['A']).';" class="song-item">';
	if(!$url) echo '<span class="am-dropdown" data-am-dropdown>';
	echo '<a';
	if($url) {
		echo ' href="'.BASIC_URL.$item.'" target="_blank"';
	}
	else echo ' class="am-dropdown-toggle"';
	echo ' style="color:#'.GSM($item)['A'].';" data-id="'.$item.'">';
	echo htmlspecial2(GSM($item)['N']).' - '.htmlspecial2(GSM($item)['S']);
	echo '</a>';
	if(!$url) {
		echo '<ul class="am-dropdown-content" onclick="$(\'.am-dropdown\').dropdown(\'close\')">';
		echo '<li><a href="'.BASIC_URL.$item.'/edit" style="color:#'.htmlspecial2(GSM($item)['A']).';">编辑</a></li>';
		echo '<li><a href="'.BASIC_URL.$item.'" target="_blank" style="color:#'.htmlspecial2(GSM($item)['A']).';">前台查看</a></li>';
		echo '<li><a href="'.BASIC_URL.$item.'/docs" target="_blank" style="color:#'.htmlspecial2(GSM($item)['A']).';">文档</a></li>';
		echo '<li><a href="'.BASIC_URL.$item.'/code" target="_blank" style="color:#'.htmlspecial2(GSM($item)['A']).';">源代码</a></li>';
		echo '<li><a href="'.getDownloadUrl($item).'" target="_blank" style="color:#'.htmlspecial2(GSM($item)['A']).';">下载</a></li>';
		echo '<li><a href="'.BASIC_URL.$item.'/permission" style="color:#'.htmlspecial2(GSM($item)['A']).';">'.permissionMarks(getPerm($item)).'</a></li>';
		echo '</ul>';
	}
	if(!$url) echo '</span>';
	echo '<br>';
	echo '<span class="addition-cmt"';
	if(is_wap()) echo ' style="line-height:180%"';
	echo '>';
	echo '<span class="txmp-tag tag-default" id="list-id-'.$item.'">'.$item.'</span>';
	echo '<span class="txmp-tag tag-cyan-g">作者：'.htmlspecial2(GSM($item)['LA']).' | '.htmlspecial2(GSM($item)['MA']).'</span>';
	echo '<span class="txmp-tag tag-orange-g">分类：'.htmlspecial2(GSM($item)['C']).'</span>';
	if(!$url && getAudioPath(FILES . $item . '/song',false)) echo '<span class="txmp-tag tag-blue-g">音频时间：'.date('Y/m/d H:i:s',getAudioMtime(FILES . $item . '/song')).'</span>';
	if(!$url) echo '<span class="txmp-tag tag-purple-g">权限：'.permissionMarks(getPerm($item)).'</span>';
	echo '</span>';
	echo '</li>';
}

// 管理员页面上的列表
function printAdminList($item) {
	printIndexList($item,false);
}

// RemotePlay搜索列表（默认配置：酷我音乐）
function printRmpList($item) {
	$cl = rgb2hex(hashed_saturate_gradient($item['name'] . ' - ' . $item['artist'])[0]);
	echo '  <li style="color:#'.$cl.';" class="song-item">';
	echo '<a href="'.BASIC_URL.'K_'.$item['rid'].'" target="_blank" style="color:#'.$cl.';" data-id="'.'K_'.$item['rid'].'">';
	echo htmlspecial2($item['name']).' - '.htmlspecial2($item['artist']);
	echo '</a>';
	echo '<br>';
	echo '<span class="addition-cmt"';
	if(is_wap()) echo ' style="line-height:180%"';
	echo '>';
	echo '<span class="txmp-tag tag-default" id="list-id-K_'.$item['rid'].'">'.'K_'.$item['rid'].'</span>';
	echo '<span class="txmp-tag tag-purple-g">发布日期：'.$item['releaseDate'].'</span>';
	echo '<span class="txmp-tag tag-orange-g">专辑：'.htmlspecial2($item['album']).'</span>';
	echo '</li>';
}

// 歌手列表（默认配置：酷我音乐）
function printKSingerList($item) {
	echo '  <li style="color:#FFA000;">';
	echo '<a onclick="kuwo_search(1,\'%'.$item['id'].'\')" style="color:#FFA000;">';
	echo htmlspecial2($item['name']);
	echo '</a>';
	echo '<br>';
	echo '<span class="addition-cmt"';
	if(is_wap()) echo ' style="line-height:180%"';
	echo '>';
	echo '<span class="txmp-tag tag-default">'.'KS_'.$item['id'].'</span>';
	echo '<span class="txmp-tag tag-purple-g">'.'歌曲数量：'.$item['musicNum'].'</span>';
	if($item['country']!='') {
		echo '<span class="txmp-tag tag-orange-g">国家：'.$item['country'].'</span>';
	}
	echo '</span>';
	echo '</li>';
}

// 今日推荐（默认配置：酷我音乐）
function printKListList($item) {
	echo '  <li style="color:#FFA000;">';
	echo '<a target="_blank" href="'.BASIC_URL.'K_playlist/'.$item['id'].'" style="color:#FFA000;">';
	echo htmlspecial2($item['name']);
	echo '</a>';
	echo '<br>';
	echo '<span class="addition-cmt"';
	if(is_wap()) echo ' style="line-height:180%"';
	echo '>';
	echo '<span class="txmp-tag tag-default">'.'KL_'.$item['id'].'</span>';
	echo '<span class="txmp-tag tag-purple-g">'.'聆听量：'.$item['listencnt'].'</span>';
	echo '</span>';
	echo '</li>';
}

// 歌单上的listname
function printPlayerList($item,$flag = false) {
	$txt="";
	$txt.='<span';
	$txt.='>';
	$txt.=htmlspecial2(GSM($item)['N']).' - '.htmlspecial2(GSM($item)['S']);
	$txt.='</span>';
	$txt.='<br>';
	$txt.='<span class="addition-cmt"';
	if(is_wap()) $txt.=' style="line-height:180%"';
	$txt.='>';


	$txt.='<span class="txmp-tag tag-default" id="list-id-'.$item.'">'.$item.'</span>';


	// if(is_wap()) $txt.='<br>';
	$txt.='<span class="txmp-tag tag-cyan-g"';
	$txt.='>作者：'.htmlspecial2(GSM($item)['LA']).' | '.htmlspecial2(GSM($item)['MA']).'</span>';
	$txt.='<span class="txmp-tag tag-orange-g">分类：'.htmlspecial2(GSM($item)['C']).'</span>';
	if(is_wap()) $txt.='<br>';
	$txt.='<span class="txmp-tag tag-blue-g">计次：<span id="list-playtimes-'.$item.'"></span></span>';
	if($flag) $txt.='<span class="txmp-tag tag-purple-g">评分：<span id="list-rating-'.$item.'"></span>';
	$txt.='</span>';
	echo addslashes($txt);
}

// 用户管理列表
function printUserList($item) {
	$types = [
		'false' => '封禁',
		'true' => '普通',
		'0' => '封禁',
		'1' => '普通',
		'3' => '超级管理员'
	];

	echo '<tr data-username="'.$item['name'].'">';
	echo '<td class="user-name">'.$item['name'].'</td>';
	echo '<td class="user-hash"><a href="javascript:void" onclick="modal_alert(\'密码哈希\',\'密码哈希的前8位是：'.substr($item['pass'],0,8).'\')">显示</a></td>';
	echo '<td class="user-ban">'.($types[$item['enabled']]).'</td>';
	echo '<td class="user-ip">'.$item['ip'].'</td>';
	echo '<td class="user-operation">';

	echo '<button class="opera-ban am-btn" onclick="changeType(gUserName(this))"><i class="fa fa-bomb"></i> 类型</button>';
	echo '<button class="opera-editname am-btn am-btn-warning" onclick="editName(gUserName(this))"><i class="fa fa-pencil"></i> 修改用户名</button>';
	echo '<button class="opera-editpass am-btn am-btn-secondary" onclick="editPass(gUserName(this))"><i class="fa fa-pencil"></i> 修改密码</button>';
	echo '<button class="opera-delete am-btn am-btn-danger" onclick="remove(gUserName(this))"><i class="fa fa-times"></i> 删除</button>';
	echo '<button class="opera-login am-btn am-btn-primary" onclick="loginAs(gUserName(this))"><i class="fa fa-key"></i> 登录</button>';

	echo '</td>';
	echo '</tr>';
}

// 页面提示（不是列表）
function redirectToNote($str) {
	echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.addslashes($str).'")</script>';
}
function redirectToGet() {
	echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))</script>';
}
