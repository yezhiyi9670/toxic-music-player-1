<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

/* TODO[WMSDFCL/User:4]: 完成功能跳转的清理 */
// 功能跳转
function printFuncLink($id) {
	if($id == 'list/index') {
		echo '<a href="' . BASIC_URL . 'user/login">' . htmlspecial2(LNG('ui.user_center')) . '</a>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<a href="' . BASIC_URL . 'setting">' . htmlspecial2(LNG('ui.user_setting')) . '</a>';
		echo '&nbsp;&nbsp;&nbsp;&nbsp;';
		echo '<a href="' . BASIC_URL . 'list-maker" target="_blank"> ' . htmlspecial2(LNG('ui.list_maker')) . '</a>';
	}
}

// 主页上的列表
function printIndexList($item,$url=true) {
	echo '<li style="color:#'.htmlspecial2(GSM($item)['A']).';" class="song-item" id="item-' . strval($item) . '">';
	if(!$url) echo '<span class="am-dropdown song-item-title" data-am-dropdown>';
	echo '<a';
	if($url) {
		echo ' href="'.BASIC_URL.$item.'" target="_blank"';
		echo ' class="song-item-title"';
	}
	else echo ' class="am-dropdown-toggle"';
	echo ' style="color:#'.GSM($item)['A'].';" data-id="'.$item.'">';
	echo htmlspecial2(GSM($item)['N']).' - '.htmlspecial2(GSM($item)['S']);
	echo '</a>';
	if(!$url) {
		echo '<ul class="am-dropdown-content" onclick="$(\'.am-dropdown\').dropdown(\'close\')">';
		// 编辑
		echo '<li><a href="'.BASIC_URL.$item.'/edit" style="color:#'.htmlspecial2(GSM($item)['A']).';">' . LNG('list.action.edit') . '</a></li>';
		// 资源管理
		echo '<li><a href="'.BASIC_URL.$item.'/resource" style="color:#'.htmlspecial2(GSM($item)['A']).';">' . LNG('list.action.resource') . '</a></li>';
		// 查看
		echo '<li><a href="'.BASIC_URL.$item.'" target="_blank" style="color:#'.htmlspecial2(GSM($item)['A']).';">' . LNG('list.action.view') . '</a></li>';
		// 文档
		echo '<li><a href="'.BASIC_URL.$item.'/docs" target="_blank" style="color:#'.htmlspecial2(GSM($item)['A']).';">' . LNG('list.action.doc') . '</a></li>';
		// 调试代码
		echo '<li><a href="'.BASIC_URL.$item.'/code" target="_blank" style="color:#'.htmlspecial2(GSM($item)['A']).';">' . LNG('list.action.code') . '</a></li>';
		// 下载
		echo '<li><a href="'.getDownloadUrl($item).'" target="_blank" style="color:#'.htmlspecial2(GSM($item)['A']).';">' . LNG('list.action.download') . '</a></li>';
		// 权限设置
		echo '<li><a href="'.BASIC_URL.$item.'/permission" style="color:#'.htmlspecial2(GSM($item)['A']).';">'.permissionMarks(getPerm($item)).'</a></li>';
		echo '</ul>';
	}
	if(!$url) echo '</span>';
	echo '<br>';
	echo '<span class="addition-cmt"';
	if(is_wap()) echo ' style="line-height:180%"';
	echo '>';
	// ID
	echo '<span class="txmp-tag tag-default" id="list-id-'.$item.'">'.fa_icon('hashtag').$item.'</span>';
	$ana = getAudioAnalysis($item);
	if($ana != null) {
		// 时长
		echo '<span class="txmp-tag tag-length">' . fa_icon('clock-o') . formatDuration($ana['time']) . '</span>';
		// 质量
		echo bitrate_tag($ana['bitrate'],$url);
	} else {
		// 有问题
		echo '<span class="txmp-tag tag-red-l">' . fa_icon('exclamation-triangle') . LNG('quality.err') . '</span>';
	}
	// 作者
	echo '<span class="txmp-tag tag-cyan-g txmp-tag-author">'.fa_icon('pencil').htmlspecial2(GSM($item)['LA']).' | '.htmlspecial2(GSM($item)['MA']).'</span>';
	// 专辑
	echo '<span class="txmp-tag tag-orange-g txmp-tag-album">'.fa_icon('book').htmlspecial2(GSM($item)['C']).'</span>';
	// 上传日期
	if(!$url && getAudioPath(FILES . $item . '/song',false)) echo '<span class="txmp-tag tag-blue-g">'.fa_icon('calendar').date('Y/m/d H:i:s',getAudioMtime(FILES . $item . '/song')).'</span>';
	// 权限
	if(!$url) echo '<span class="txmp-tag tag-purple-g">'.fa_icon('key').permissionMarks(getPerm($item)).'</span>';
	// 没有封面
	if(!$url && !GSM($item)['P']) {
		echo '<span class="txmp-tag tag-deep-orange-l">'.fa_icon('exclamation-circle').LNG('list.tag.no_cover').'</span>';
	}
	echo '</span>';
	echo '</li>';
}

// 音频文件分析标签
function audioFileAnalysisTags($ana) {
	if($ana != null) {
		// 时长
		echo '<span class="txmp-tag tag-length">' . fa_icon('clock-o') . formatDuration($ana['time']) . '</span>';
		// 质量
		echo bitrate_tag($ana['bitrate'],true);
	} else {
		// 有问题
		echo '<span class="txmp-tag tag-red-l">' . fa_icon('exclamation-triangle') . LNG('quality.err') . '</span>';
	}
}

// 音频分析标签
function audioAnalysisTags($item) {
	audioFileAnalysisTags(getAudioAnalysis($item));
}

// 管理员页面上的列表
function printAdminList($item) {
	printIndexList($item,false);
}

// RemotePlay 搜索列表（默认配置：酷我音乐）
function printRmpList($item,$dataType=false) {
	if($dataType) {
		$item['rid'] = $item['id'];
		$item['name'] = $item['songName'];
	}
	$cl = rgb2hex(hashed_saturate_gradient($item['name'] . ' - ' . $item['artist'])[0]);
	echo '  <li style="color:#'.$cl.';" class="song-item song-item-rp">';
	echo '<a href="'.BASIC_URL.'K_'.$item['rid'].'" target="_blank" style="color:#'.$cl.';" data-id="'.'K_'.$item['rid'].'">';
	echo ($item['name']).' - '.($item['artist']);
	echo '</a>';
	echo '<br>';
	echo '<span class="addition-cmt"';
	if(is_wap()) echo ' style="line-height:180%"';
	echo '>';
	// ID
	echo '<span class="txmp-tag tag-default" id="list-id-K_'.$item['rid'].'">'.fa_icon('hashtag').'K_'.$item['rid'].'</span>';
	// 时长
	echo '<span class="txmp-tag tag-length">' . fa_icon('clock-o') . formatDuration($item['duration']) . '</span>';
	// 质量
	echo bitrate_tag(192);
	// 上传日期
	// if(!$dataType) echo '<span class="txmp-tag tag-purple-g">'.fa_icon('calendar').$item['releaseDate'].'</span>';
	// 专辑名称
	if($item['album']) {
		echo '<span class="txmp-tag tag-orange-g txmp-tag-album">'.fa_icon('book').($item['album']).'</span>';
	}
	// 评价（我们发现存在 102%）
	echo '<span class="txmp-tag tag-blue-g">'.fa_icon('asterisk').$item['score100'].'%</span>';
	// 限制标签
	if(isset($item['pay'])) {
		$pay = kuwoPayStatus($item['pay']);
		// 禁止在线播放 | 付费播放
		if($pay['no_play']) {
			echo '<span class="txmp-tag tag-red-l tag-rplim tag-rplim-noplay">'.fa_icon('exclamation-triangle').LNG('list.tag.rp_lim.noplay').'</span>';
		}
		else if($pay['pay_play']) {
			echo '<span class="txmp-tag tag-vip tag-rplim tag-rplim-payplay">'.fa_icon('diamond').LNG('list.tag.rp_lim.payplay').'</span>';
		}
		// 禁止下载 | 付费下载
		if($pay['no_download']) {
			echo '<span class="txmp-tag tag-red-g tag-rplim tag-rplim-nodl">'.fa_icon('diamond').LNG('list.tag.rp_lim.nodl').'</span>';
		}
		else if($pay['pay_download']) {
			echo '<span class="txmp-tag tag-red-g tag-rplim tag-rplim-paydl">'.fa_icon('diamond').LNG('list.tag.rp_lim.paydl').'</span>';
		}
	}
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
	echo '<span class="txmp-tag tag-default">'.fa_icon('hashtag').'KS_'.$item['id'].'</span>';
	echo '<span class="txmp-tag tag-purple-g">'.fa_icon('th-list').$item['musicNum'].'</span>';
	if($item['country']!='') {
		echo '<span class="txmp-tag tag-orange-g">'.fa_icon('globe').$item['country'].'</span>';
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
	echo '<span class="txmp-tag tag-default">'.fa_icon('hashtag').'KL_'.$item['id'].'</span>';
	echo '<span class="txmp-tag tag-purple-g">'.fa_icon('eye').$item['listencnt'].'</span>';
	echo '</span>';
	echo '</li>';
}

// 歌单上的listname
function printPlayerList($item, $isCloud = false, $isNull = false) {
	$txt="";
	$txt.='<span';
	$txt.='>';
	if(!$isNull) {
		$txt .= htmlspecial2(GSM($item)['N']).' - '.htmlspecial2(GSM($item)['S']);
	} else {
		// 歌曲不可用
		$txt .= htmlspecial2(LNG('comp.invalid_song')) . ' ';
		$txt.='<span class="txmp-tag tag-default" id="list-id-'.$item.'">'.$item.'</span>';
		if($isCloud) {
			$txt.='<span class="txmp-tag tag-purple-g">'.LNG('list.tag.rating').'<span id="list-rating-'.$item.'"></span>';
		}
	}
	$txt.='</span>';
	if(!$isNull) {
		$txt .= '<br />';

		$txt.='<span class="addition-cmt"';
		if(is_wap()) $txt.=' style="line-height:180%"';
		$txt.='>';


		$txt.='<span class="txmp-tag tag-default" id="list-id-'.$item.'">'.fa_icon('hashtag').$item.'</span>';
		$ana = getAudioAnalysis($item);
		if($ana != null) {
			// 时长
			$txt .= '<span class="txmp-tag tag-length">' . fa_icon('clock-o') . formatDuration($ana['time']) . '</span>';
			// 质量
			// $txt .= bitrate_tag($ana['bitrate']);
		} else {
			// 有问题
			$txt .= '<span class="txmp-tag tag-red-l">' . fa_icon('exclamation-triangle') . LNG('quality.err') . '</span>';
		}

		// if(is_wap()) $txt.='<br>';
		// $txt.='<span class="txmp-tag tag-cyan-g"';
		// if(!is_wap()) $txt.='>'.LNG('list.tag.author').htmlspecial2(GSM($item)['LA']).' | '.htmlspecial2(GSM($item)['MA']).'</span>';
		// $txt.='<span class="txmp-tag tag-orange-g">'.LNG('list.tag.cate').htmlspecial2(GSM($item)['C']).'</span>';
		// 查看次数
		$txt.='<span class="txmp-tag tag-blue-g txmp-tag-times">'.fa_icon('eye').'<span id="list-playtimes-'.$item.'">&nbsp;</span></span>';
		// 权值
		if($isCloud) $txt.='<span class="txmp-tag tag-purple-g txmp-tag-weight">'.fa_icon('asterisk').'<span id="list-rating-'.$item.'"></span></span>';
		// 付费标签
		if(paymentStatus($item)['pay_play']) {
			$txt .= '<span class="txmp-tag tag-vip tag-rplim tag-rplim-payplay">'.fa_icon('diamond').LNG('list.tag.rp_lim.payplay').'</span>';
		}
		$txt.='</span>';
	}

	echo addslashes($txt);
}

// 用户管理列表
function printUserList($item) {
	$types = [
		'false' => LNG('uauth.type.ban'),
		'true' => LNG('uauth.type.normal'),
		'0' => LNG('uauth.type.ban'),
		'1' => LNG('uauth.type.normal'),
		'3' => LNG('uauth.type.root')
	];

	echo '<tr data-username="'.$item['name'].'">';
	echo '<td class="user-name">'.$item['name'].'</td>';
	echo '<td class="user-hash"><a href="javascript:void" onclick="modal_alert(\''.LNGk('uauth.ui.passhash').'\',\''.LNGk('uauth.ui.passhash.tip').uauth_hash_summary($item['pass']).'\')">'.LNG('uauth.ui.show').'</a></td>';
	echo '<td class="user-ban">'.($types[$item['enabled']]).'</td>';
	echo '<td class="user-ip">'.$item['ip'].'</td>';
	echo '<td class="user-operation">';

	echo '<button class="opera-ban am-btn" onclick="changeType(gUserName(this))"><i class="fa fa-bomb"></i> '.LNG('uauth.ui.type').'</button>';
	echo '<button class="opera-editname am-btn am-btn-warning" onclick="editName(gUserName(this))"><i class="fa fa-pencil"></i> '.LNG('uauth.action.changename').'</button>';
	echo '<button class="opera-editpass am-btn am-btn-secondary" onclick="editPass(gUserName(this))"><i class="fa fa-pencil"></i> '.LNG('uauth.action.changepass').'</button>';
	echo '<button class="opera-delete am-btn am-btn-danger" onclick="remove(gUserName(this))"><i class="fa fa-times"></i> '.LNG('uauth.action.delete').'</button>';
	echo '<button class="opera-login am-btn am-btn-primary" onclick="loginAs(gUserName(this))"><i class="fa fa-key"></i> '.LNG('uauth.action.login').'</button>';

	echo '</td>';
	echo '</tr>';
}

// 页面提示（不是列表）
function redirectToNote($str) {
	echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.addslashes($str).'")</script>';
}
function redirectToPage($str) {
	echo '<script>location.href="'.addslashes(BASIC_URL . $str).'"</script>';
}
function redirectToGet() {
	echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))</script>';
}

// 显示 msg 通知（Toast 模式）
function showToastMessage() {
	?><?php if(isset($_GET['msg'])) { ?>
		<script>
			$(() => {
				Toast.make_toast_text('<?php echo addslashes($_GET['msg']) ?>', 2500);
				F_HideNotice();
			});
		</script>
	<?php } ?><?php
}

// 显示 msg 通知（顶部通知模式）
function showTopMessage() {
	?><?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
		<a href="javascript:;" onclick="F_HideNotice()" class="notice-confirm"><?php LNGe('ui.hide_notice') ?></a>
	</p><?php } ?><?php
}
