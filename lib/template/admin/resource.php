<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php
	$id=preSubstr($_GET['_lnk']);
	if($_POST['isSubmit']=='yes')
	{
		if(!file_exists(FILES.$id."/")) {
			redirectToNote(LNG('editor.msg.tan90'));
			exit;
		}

		file_put_contents(FILES.$id."/ref.txt",$_POST['audio-ref']);

		$has_wrong_ext = false;
		if($_POST['yp-operation']!='no') {
			if($_POST['yp-operation']=='upload' && $_FILES['thefile']['size']) {
				$ext=substr($_FILES['thefile']['name'],strrpos($_FILES['thefile']['name'],'.'));
				if(isAudioExtAllowed($ext)) {
					if(getAudioPath(FILES.$id.'/song')) {
						unlink(getAudioPath(FILES.$id.'/song'));
					}
					move_uploaded_file($_FILES['thefile']['tmp_name'],FILES.$id.'/song'.$ext);
				} else {
					$has_wrong_ext = true;
				}
			} else if($_POST['yp-operation']=='delete' && getAudioPath(FILES.$id.'/song')) {
				unlink(getAudioPath(FILES.$id.'/song'));
			}
		}
		if($_POST['back-operation']!='no') {
			if($_POST['back-operation']=='upload' && $_FILES['theback']['size']) {
				$ext=substr($_FILES['theback']['name'],strrpos($_FILES['theback']['name'],'.'));
				if(isAudioExtAllowed($ext)) {
					if(getAudioPath(FILES.$id.'/back')) {
						unlink(getAudioPath(FILES.$id.'/back'));
					}
					move_uploaded_file($_FILES['theback']['tmp_name'],FILES.$id.'/back'.$ext);
				} else {
					$has_wrong_ext = true;
				}
			} else if($_POST['back-operation']=='delete' && getAudioPath(FILES.$id.'/back')) {
				unlink(getAudioPath(FILES.$id.'/back'));
			}
		}
		if($_POST['avatar-operation']!='no') {
			if($_POST['avatar-operation']=='upload') {
				if($_FILES['theimage']['size']) {
					$ext=substr($_FILES['theimage']['name'],strrpos($_FILES['theimage']['name'],'.'));
					if(isPictureExtAllowed($ext)) {
						if(getPicturePath(FILES.$id.'/avatar')) {
							unlink(getPicturePath(FILES.$id.'/avatar'));
							// 重写 lyric.txt 防止引发缓存问题
							rewrite_file(FILES . $id . '/lyric.txt');
						}
						move_uploaded_file($_FILES['theimage']['tmp_name'],FILES.$id.'/avatar'.$ext);
					} else {
						$has_wrong_ext = true;
					}
				} else if($_POST['theimage_url']) {
					$url = $_POST['theimage_url'];
					$baseurl = preSubstr($url,'?');
					$ext=substr($baseurl,strrpos($baseurl,'.'));
					if(isPictureExtAllowed($ext)) {
						if(getPicturePath(FILES.$id.'/avatar')) {
							unlink(getPicturePath(FILES.$id.'/avatar'));
						}
						@$data = ex_url_get_contents($url);
						if(strlen($data) > 10) file_put_contents(FILES.$id.'/avatar'.$ext, $data);
					} else {
						$has_wrong_ext = true;
					}
				}
			} else if($_POST['avatar-operation']=='delete' && getPicturePath(FILES.$id.'/avatar')) {
				unlink(getPicturePath(FILES.$id.'/avatar'));
				// 重写 lyric.txt 防止引发缓存问题
				rewrite_file(FILES . $id . '/lyric.txt');
			}
		}

		if($has_wrong_ext) {
			redirectToNote(LNG('editor.msg.wrong_ext'));
			exit;
		}
		redirectToGet();
		exit;
	}

?>
<?php
// --- load js ---
load_js('js/resource/resourceapp');
?>
<?php
	load_css('css/resource/resource');
?>
<script>document.title='<?php echo addslashes(GCM()['N']) ?> > <?php LNGe('resource.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';</script>
<form style="position:<?php echo is_wap()?"auto":"fixed" ?>;" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
	<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
	<input type="hidden" name="isSubmit" value="yes">
	<div class="txmp-page-left" style="position:<?php echo is_wap()?"auto":"fixed" ?>;">
		<!--主音频-->
		<p>
			<?php
				$main_path = getAudioPath(FILES.$id.'/song');
			?>
			<span style="float:right;">
				<?php audioFileAnalysisTags(analyzeAudio($main_path)) ?>
			</span>
			<strong><?php LNGe('editor.upload.main') ?></strong>
			<select name="yp-operation">
				<option value="no"><?php LNGe('editor.upload.no') ?></option>
				<option value="upload"><?php LNGe('editor.upload.upload') ?></option>
				<?php if($main_path) { ?><option value="delete"><?php LNGe('editor.upload.delete') ?></option><?php } ?>
			</select>
			<?php if(!$main_path) { ?><span style="color:#F00;" class="wid-lp-8">(<?php LNGe('editor.upload.tan90') ?>)</span><?php } ?>
		</p>
		<p>
			<?php LNGe('editor.upload.tip') ?>
			<input type="file" accept="audio/*" name="thefile" id="thefile">
		</p>
		<p>
			★ <?php echo LNG('editor.upload.replace','<input name="audio-ref" class="input-min" value="'.getReferenceID($id).'" title="'.LNG('editor.upload.replace.tip').'">') ?><br>
		</p>
		<hr>

		<!--从音频-->
		<p>
			<?php
				$back_path = getAudioPath(FILES.$id.'/back');
			?>
			<span style="float:right;">
				<?php audioFileAnalysisTags(analyzeAudio($back_path)) ?>
			</span>
			<strong><?php LNGe('editor.upload.back') ?></strong>
			<select name="back-operation">
				<option value="no"><?php LNGe('editor.upload.no') ?></option>
				<option value="upload"><?php LNGe('editor.upload.upload') ?></option>
				<?php if($back_path) { ?><option value="delete"><?php LNGe('editor.upload.delete') ?></option><?php } ?>
			</select>
			<?php if(!$back_path) { ?><span style="color:#F00;" class="wid-lp-8">(<?php LNGe('editor.upload.tan90') ?>)</span><?php } ?>
		</p>
		<p>
			<?php LNGe('editor.upload.tip') ?>
			<input type="file" accept="audio/*" name="theback" id="theback">
		</p>
		<hr>

		<!--摘要图片-->
		<p>
			<?php
				$avatar_path = getPicturePath(FILES.$id.'/avatar');
			?>
			<?php if($avatar_path) { ?>
				<img style="float:right;height:100px;" src="<?php echo BASIC_URL . $id . '/avatar' ?>" />
			<?php } ?>
			<strong><?php LNGe('editor.upload.avatar') ?></strong>
			<select name="avatar-operation">
				<option value="no"><?php LNGe('editor.upload.no') ?></option>
				<option value="upload"><?php LNGe('editor.upload.upload') ?></option>
				<?php if($avatar_path) { ?><option value="delete"><?php LNGe('editor.upload.delete') ?></option><?php } ?>
			</select>
			<?php if(!$avatar_path) { ?><span style="color:#F00;" class="wid-lp-8">(<?php LNGe('editor.upload.tan90') ?>)</span><?php } ?>
		</p>
		<p>
			<?php LNGe('editor.upload.tip.image') ?> <br/>
			<input type="file" accept="image/*" name="theimage" id="theimage" style="display:inline;width:210px;vertical-align:baseline;">
			<?php LNGe('ui.or') ?>
			<input class="input-min" placeholder="<?php LNGe('editor.upload.down') ?>" name="theimage_url" style="width:250px!important;">
		</p>

		<!--来源链接-->
		<?php $origin_url=GCM()['O'];if(GCM()['O']) { ?>
			<hr>
			<a href="<?php echo $origin_url ?>" target="_blank"><?php LNGe('editor.open_source') ?> <i class="fa fa-external-link"></i></a>
		<?php } ?>
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
		<span class="bid-linking">
			<a href="<?php echo BASIC_URL . cid() ?>/edit"><?php LNGe('editor.title') ?></a>&nbsp;▪
			<strong><?php LNGe('resource.title') ?></strong>&nbsp;▪
			<a href="<?php echo BASIC_URL . cid() ?>/permission"><?php LNGe('permitter.title') ?></a>
		</span>
		<p>
			<button type="submit" class="am-btn am-btn-primary"><?php LNGe('editor.submit.update') ?></button>
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
