<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php
	$id=preSubstr($_GET['_lnk']);
	if($_POST['isSubmit']=='yes')
	{
		if(!file_exists(FILES.$id."/")) {
			echo LNG('editor.msg.tan90');
			exit;
		}
		file_put_contents(FILES.$id."/lyric.txt",$_POST['lyricfile']);

		echo '+';
		echo parseCmpLyric(preSubstr($_GET['_lnk']));
		exit;
	}

?>
<?php
// --- load js ---
load_js('js/editor/editorflex');
load_js('js/editor/editorapp');
?>
<?php
	load_css('css/editor/editor');
?>
<script>document.title='<?php echo addslashes(GCM()['N']) ?> > <?php LNGe('editor.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';</script>
<script>
	var current_data = <?php echo parseCmpLyric(preSubstr($_GET['_lnk'])) ?>;
</script>
<form style="position:<?php echo is_wap()?"auto":"fixed" ?>;" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
	<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
	<input type="hidden" name="isSubmit" value="yes">
	<div class="txmp-page-left" style="position:<?php echo is_wap()?"auto":"fixed" ?>;">
		<div id="toolbox">
			<span class="am-dropdown" data-am-dropdown>
				<button type="button" class="am-btn am-dropdown-toggle"><?php LNGe('editor.cate.basic') ?></button>
				<ul class="am-dropdown-content" style="max-height:480px;overflow:auto;" onclick="$('.am-dropdown').dropdown('close')">
					<li class="am-dropdown-header"><?php LNGe('editor.cate.basic') ?></li>
					<li><a onclick="editor_nl()"><?php LNGe('editor.action.trim') ?></a></li>
					<li><a onclick="editor_addl()"><?php LNGe('editor.action.addl') ?></a></li>
					<li><a onclick="editor_rmsym()"><?php LNGe('editor.action.strip') ?></a></li>
					<li><a onclick="editor_formatheading()"><?php LNGe('editor.action.reformat') ?></a></li>
					<li><a onclick="editor_cleartime('-')"><?php LNGe('editor.action.deletetime') ?></a></li>
					<li><a onclick="editor_cleartime('__FTIME__')"><?php LNGe('editor.action.emptytime') ?></a></li>
				</ul>
			</span>
			<span class="am-dropdown" data-am-dropdown>
				<button type="button" class="am-btn am-dropdown-toggle"><?php LNGe('editor.cate.fmttag') ?></button>
				<ul class="am-dropdown-content" style="max-height:480px;overflow:auto;" onclick="$('.am-dropdown').dropdown('close')">
					<li class="am-dropdown-header"><?php LNGe('editor.cate.fmttag') ?></li>
					<li><a onclick="editor_entag('U');"><?php LNGe('editor.mark.u') ?></a></li>
					<li><a onclick="editor_entag('S');"><?php LNGe('editor.mark.s') ?></a></li>
					<li><a onclick="editor_entag('R');"><?php LNGe('editor.mark.r') ?></a></li>
					<li class="am-divider"></li>
					<li><a onclick="editor_entag('1');"><?php LNGe('editor.mark.1') ?></a></li>
					<li><a onclick="editor_entag('2');"><?php LNGe('editor.mark.2') ?></a></li>
					<li><a onclick="editor_entag('3');"><?php LNGe('editor.mark.3') ?></a></li>
					<li><a onclick="editor_entag('4');"><?php LNGe('editor.mark.4') ?></a></li>
					<li><a onclick="editor_entag('5');"><?php LNGe('editor.mark.5') ?></a></li>
					<li><a onclick="editor_entag('6');"><?php LNGe('editor.mark.6') ?></a></li>
					<li><a onclick="editor_entag('7');"><?php LNGe('editor.mark.7') ?></a></li>
				</ul>
			</span>
			<span class="am-dropdown" data-am-dropdown>
				<button type="button" class="am-btn am-dropdown-toggle"><?php LNGe('editor.cate.snippet') ?></button>
				<ul class="am-dropdown-content" style="max-height:480px;overflow:auto;" onclick="$('.am-dropdown').dropdown('close')">
					<li class="am-dropdown-header"><?php LNGe('editor.cate.snippet') ?></li>
					<li><a onclick="editor_sni('info');"><?php LNGe('editor.sni.meta') ?></a></li>
					<li><a onclick="editor_sni('para');"><?php LNGe('editor.sni.para') ?></a></li>
					<li><a onclick="editor_sni('hidden');"><?php LNGe('editor.sni.hidden') ?></a></li>
					<li><a onclick="editor_sni('reuse');"><?php LNGe('editor.sni.reuse') ?></a></li>
					<li><a onclick="editor_sni('similar');"><?php LNGe('editor.sni.similar') ?></a></li>
					<li><a onclick="editor_sni('line');"><?php LNGe('editor.sni.line') ?></a></li>
					<li><a onclick="editor_sni('mid');"><?php LNGe('editor.sni.interval') ?></a></li>
				</ul>
			</span>
			<button type="button" class="am-btn" id="input-time-button"><?php LNGe('editor.action.inptime') ?></button>
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
			<a href="javascript:;" onclick="F_HideNotice()" class="notice-confirm"><?php LNGe('ui.hide_notice') ?></a>
		</p><?php } ?>
		<p><?php LNGe('editor.status'); ?><?php
			if(isValidMusic(preSubstr($_GET['_lnk']))) {
				LNGe('editor.status.canplay');
			} else {
				LNGe('editor.status.no_audio');
			}
			echo '&nbsp;&nbsp;&nbsp;&nbsp;';
			audioAnalysisTags(cid());
		?></p>
		
		<p>
			<button type="button" class="am-btn submit-btn am-btn-primary"><?php LNGe('editor.submit.update') ?></button>
			<button type="button" onclick="window.open('<?php echo BASIC_URL.preSubstr($_GET['_lnk']) ?>')" class="am-btn am-btn-secondary"><?php LNGe('editor.submit.view') ?></button>
			<?php if(isValidMusic(preSubstr($_GET['_lnk']))){ ?><button type="button" onclick="$('#preview')[0].contentWindow.location.reload()" class="am-btn am-btn-thirdary"><?php LNGe('editor.submit.refresh') ?></button><?php } ?>
		</p>

		<p>
			<?php if(isValidMusic(preSubstr($_GET['_lnk']))){ ?>
				<iframe id="preview" src="<?php echo BASIC_URL.preSubstr($_GET['_lnk']).'?wap=force-phone' ?>" style="width:100%;height:500px;border:1px solid #000000;<?php if(stristr($_SERVER['HTTP_USER_AGENT'],'firefox/')){ ?>margin-bottom: 51px;<?php } ?>"></iframe>
			<?php } ?>
		</p>
	</div>
</form>
