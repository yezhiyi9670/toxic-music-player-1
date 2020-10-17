<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

	if($_POST['isSubmit']=='yes') {
		$id=preSubstr($_GET['_lnk']);
		if(!file_exists(FILES.$id."/")) {
			redirectToNote('该歌曲已经不存在！');
			exit;
		}

		$e=array();
		foreach($_POST as $k=>$v) {
			if(permissionNames($k)) {
				$e[$k]=($v=='off' ? false : true);
			}
		}
		file_put_contents(FILES.cid().'/permission.json',json_encode($e,JSON_PRETTY_PRINT));

		echo '<script>location.href="'.BASIC_URL.'admin#item-'.cid().'"</script>';
		exit;
	}

?>
<script>document.title='<?php echo addslashes(GCM()['N']) ?> > 修改权限 - <?php echo htmlspecial2(_C()['app_name_title']) ?>';</script>
<div class="txmp-page-full">
	<h3>修改权限</h3>
	<?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
		<a href="javascript:;" onclick="F_HideNotice()">知道了</a>
	</p><?php } ?>
	<p>修改权限，以规定访客能够访问本歌曲的哪些内容。<br>
	管理员不受权限限制，因此，若要测试权限的设置，请在网址后加上'?deauth'，这样系统就不认为你是管理员。</p>
	<p><form method="post">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="yes">
		<table>
			<?php
				$spt = getPerm(cid());
				$rtn = permissionNames();
				foreach($rtn as $k=>$v) {
					echo '<tr>';
					echo '<td style="padding-right:16px;padding-bottom:8px;font-size:14px;">';
					echo $v;
					echo '</td>';
					echo '<td style="padding-right:16px;padding-bottom:8px;font-size:14px;">';
					echo '<input type="hidden" name="'.$k.'" value="off">';
					if($k!="admin/edit") echo '<input type="checkbox" name="'.$k.'" '.($spt[$k] ? "checked" : "").'>';
					else echo '<input type="checkbox" name="'.$k.'" '.($spt[$k] ? "checked" : "").' disabled>';
					echo '</td></tr>';
				}
			?>
		</table>
		<input style="margin-top:8px;" type="submit" value="确定" class="am-btn am-btn-primary">
	</form></p>
	<p>
		<a onclick="toggleVisible(this)">
			▶ 提示
		</a>
	</p>
	<p id="notes" style="display:none;margin-left:8px;margin-top:-12px;line-height:26px;">
		<b>L：在主页列出。</b>如果禁用权限，歌曲不会在主页显示。<br>
		<b>P：播放页权限。</b>如果禁用权限，游客不能进入播放页面。若该用户要使用其他权限（如下载），必须通过网址直入。<br>
		<b>C：查看代码权限。</b>如果禁用，游客不能进入类似于'song-id/code'的页面，并且无从获取歌词文件的编译前代码（编译后代码被写入播放页中，故用户可以查看播放页源代码而获得之）。<br>
		<b>K：播放。</b>如果禁用，游客不能加载类似于'song-id/audio.m4a'的资源。这使得游客即使能够进入播放页也无法播放。此时播放页的播放控件会隐藏，并且无法加载出歌曲时长。<br>
		<b>D：下载。</b>如果禁用，游客不能进入类似'song-id/download'的页面，即无法直接下载歌曲。此时，播放页的“下载”按钮隐藏。<span style="color:red">注意，如果K权限开放，专业的用户就可以使用技术手段下载，而绕过D权限的限制（绝大多数音乐网站现在也可以绕过下载客户端的要求而直接下载歌曲）。</span><br>
		<b>A：API歌词权限，</b>请注意，由于使用部分的PJAX加载，该权限已弃用（其有效值为权限P的值）。<br>
		<b>X和W：进入Word下载页/下载Word。</b>播放器支持将歌词转换为Word文档以供下载。如果都启用，播放器会显示“可打印歌词”按钮，并且可以下载。如果只启用W，那么按钮隐藏，下载页面无法进入，但是可以通过形如'song-id/make-doc?font=XXX'的网址进行下载，歌词文档也可以包含在通过歌单制作的歌词本中。如果都不启用，无法下载Word文档。在配置差的服务器上建议关闭这个功能。注意，不建议只启用X，这样会导致有一个虚设的下载页而无法下载。<br>
		<b>E：编辑。</b><span style="color:red">由于编辑器的防注入和权限控制并不强，开放编辑权限会造成严重后果。禁止开放该权限。</span><br>
	</p>
</div>
