<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php
	$id=preSubstr($_GET['_lnk']);
	if($_POST['isSubmit']=='yes')
	{
		if(!file_exists(FILES.$id."/")) {
			redirectToNote('该歌曲已经不存在！');
			exit;
		}
		file_put_contents(FILES.$id."/lyric.txt",$_POST['lyricfile']);
		file_put_contents(FILES.$id."/ref.txt",$_POST['audio-ref']);

		if($_POST['yp-operation']!='no') {
			if(getAudioPath(FILES.$id.'/song'))
			{
				unlink(getAudioPath(FILES.$id.'/song'));
			}
			if($_POST['yp-operation']=='upload' && $_POST['ftype']!="x/empty") {
				$ext=substr($_FILES['thefile']['name'],strrpos($_FILES['thefile']['name'],'.'));
				move_uploaded_file($_FILES['thefile']['tmp_name'],FILES.$id.'/song'.$ext);
			}
		}
		if($_POST['back-operation']!='no') {
			if(getAudioPath(FILES.$id.'/back'))
			{
				unlink(getAudioPath(FILES.$id.'/back'));
			}
			if($_POST['back-operation']=='upload' && $_POST['back-ftype']!="x/empty") {
				$ext=substr($_FILES['theback']['name'],strrpos($_FILES['theback']['name'],'.'));
				move_uploaded_file($_FILES['theback']['tmp_name'],FILES.$id.'/back'.$ext);
			}
		}

		redirectToGet();
		exit;
	}

?>
<script src="<?php echo BASIC_URL ?>static/js/editor/editorflex.js.php?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>"></script>
<script src="<?php echo BASIC_URL ?>static/js/editor/editorapp.js.php?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>"></script>
<script src="<?php echo BASIC_URL ?>static/js/common/autoln.js?v=<?php echo VERSION ?>"></script>
<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/css/editor/editor.css.php?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>&A=X<?php echo htmlspecial2(GCM()['A']) ?>&S=X<?php echo htmlspecial2(GCM()['X']) ?>">
<script>document.title='<?php echo addslashes(GCM()['N']) ?> > 编辑器 - <?php echo htmlspecial2(_C()['app_name_title']) ?>';</script>
<form style="position:<?php echo is_wap()?"auto":"fixed" ?>;" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
	<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
	<input type="hidden" name="isSubmit" value="yes">
	<div class="txmp-page-left" style="position:<?php echo is_wap()?"auto":"fixed" ?>;">
		<div id="toolbox">
			<span class="am-dropdown" data-am-dropdown>
				<button type="button" class="am-btn am-dropdown-toggle">基础操作</button>
				<ul class="am-dropdown-content" style="max-height:480px;overflow:auto;" onclick="$('.am-dropdown').dropdown('close')">
					<li class="am-dropdown-header">基础操作</li>
					<li><a onclick="editor_nl()">清理空行</a></li>
					<li><a onclick="editor_addl()">补加L开头</a></li>
					<li><a onclick="editor_rmsym()">清除标点</a></li>
					<li><a onclick="editor_formatheading()">考古</a></li>
					<li><a onclick="editor_cleartime('-')">清除时值</a></li>
					<li><a onclick="editor_cleartime('__FTIME__')">清空时值</a></li>
				</ul>
			</span>
			<span class="am-dropdown" data-am-dropdown>
				<button type="button" class="am-btn am-dropdown-toggle">标签</button>
				<ul class="am-dropdown-content" style="max-height:480px;overflow:auto;" onclick="$('.am-dropdown').dropdown('close')">
					<li class="am-dropdown-header">格式标签</li>
					<li><a onclick="editor_entag('U');">需要注意（下划线 U）</a></li>
					<li><a onclick="editor_entag('S');">不发音（删除线 S）</a></li>
					<li><a onclick="editor_entag('R');">人声倒放（反向箭头 R）</a></li>
					<li class="am-divider"></li>
					<li><a onclick="editor_entag('1');">角色：1（红）</a></li>
					<li><a onclick="editor_entag('2');">角色：2（蓝）</a></li>
					<li><a onclick="editor_entag('3');">组合：12（紫）</a></li>
					<li><a onclick="editor_entag('4');">角色：3（橙黄）</a></li>
					<li><a onclick="editor_entag('5');">组合：13（橘红）</a></li>
					<li><a onclick="editor_entag('6');">组合：23（绿）</a></li>
					<li><a onclick="editor_entag('7');">组合：123（粉红）</a></li>
				</ul>
			</span>
			<span class="am-dropdown" data-am-dropdown>
				<button type="button" class="am-btn am-dropdown-toggle">代码片段</button>
				<ul class="am-dropdown-content" style="max-height:480px;overflow:auto;" onclick="$('.am-dropdown').dropdown('close')">
					<li class="am-dropdown-header">代码片段</li>
					<li><a onclick="editor_sni('info');">元数据</a></li>
					<li><a onclick="editor_sni('para');">段落头</a></li>
					<li><a onclick="editor_sni('hidden');">注释段落头</a></li>
					<li><a onclick="editor_sni('reuse');">全等段落头</a></li>
					<li><a onclick="editor_sni('similar');">相似段落头</a></li>
					<li><a onclick="editor_sni('line');">歌词行</a></li>
					<li><a onclick="editor_sni('mid');">间奏</a></li>
				</ul>
			</span>
			<button type="button" class="am-btn" id="input-time-button">输入时值</button>
		</div>
		<div style="font-family:'Consolas','Source Code Pro','Courier New'!important;font-size:16px;" id="lyricfile__wrapper"><textarea style="width:100%;height:300px" id="lyricfile" name="lyricfile"></textarea></div>
		<script>
			document.getElementById('lyricfile').value=`<?php
				echo esline(file_get_contents(FILES.preSubstr($_GET['_lnk']).'/lyric.txt'));
			?>`;
		</script>
	</div>
	<div class="txmp-page-right" style="position:<?php echo is_wap()?"auto":"fixed" ?>; overflow-y:scroll; padding-bottom:64px;">
		<?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
			<a href="javascript:;" onclick="F_HideNotice()">知道了</a>
		</p><?php } ?>
		<p>歌曲状态：<?php
			if(isValidMusic(preSubstr($_GET['_lnk']))) {
				echo '可以播放';
			} else {
				echo '没有上传音频（前端404）';
			}
		?></p>
		<p>
			对歌曲音频：
			<select name="yp-operation">
				<option value="no">不更改</option>
				<option value="upload">上传新的</option>
				<?php if(getAudioPath(FILES.$id.'/song')) { ?><option value="delete">删除原有音频</option><?php } ?>
			</select>
		</p>
		<p>
			上传新的歌曲音频（别忘了点上面的选择框，否则白传）：
			<input type="file" accept="audio/*" name="thefile" id="thefile">
			<input type="hidden" name="ftype" id="ftype" value="x/empty">
		</p>
		<p>
			★ 用代码为 <input name="audio-ref" style="width:140px;border:none;border-bottom:1px solid #000;font-size:16px; margin-top:-4px;" value="<?php echo getReferenceID($id) ?>" title="注：填写本歌曲的代码，或者留空，禁用此功能。否则即使上传了音频，此功能依然生效。"> 的歌曲代替本歌曲音频<br>
		</p>
		<hr>
		<p>
			对伴奏/人声消减件：
			<select name="back-operation">
				<option value="no">不更改</option>
				<option value="upload">上传新的</option>
				<?php if(getAudioPath(FILES.$id.'/back')) { ?><option value="delete">删除原有音频</option><?php } ?>
			</select>
		</p>
		<p>
			上传新的伴奏/人声消减件（别忘了点上面的选择框，否则白传）：
			<input type="file" accept="audio/*" name="theback" id="theback">
			<input type="hidden" name="back-ftype" id="back-ftype" value="x/empty">
		</p>
		<hr>
		<p>
			<button type="submit" class="am-btn am-btn-primary">更新歌曲</button>
			<button type="button" onclick="window.open('<?php echo BASIC_URL.preSubstr($_GET['_lnk']) ?>')" class="am-btn am-btn-secondary">前台查看</button>
			<?php if(isValidMusic(preSubstr($_GET['_lnk']))){ ?><button type="button" onclick="$('#preview')[0].contentWindow.location.reload()" class="am-btn am-btn-thirdary">刷新预览</button><?php } ?>
		</p>

		<p>
			<?php if(isValidMusic(preSubstr($_GET['_lnk']))){ ?>
				<iframe id="preview" src="<?php echo BASIC_URL.preSubstr($_GET['_lnk']).'?wap=force-phone' ?>" style="width:100%;height:500px;border:1px solid #000000;<?php if(stristr($_SERVER['HTTP_USER_AGENT'],'firefox/')){ ?>margin-bottom: 51px;<?php } ?>"></iframe>
			<?php } ?>
		</p>
	</div>
</form>
