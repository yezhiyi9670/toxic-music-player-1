<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

	if($_POST['isSubmit']=='create-item') {
		if(!isset($_POST['code']) || $_POST['code']=='') {
			redirectToNote(LNG('admin.msg.noinput'));
			exit;
		}
		if(!preg_match('/^(\w+)$/',$_POST['code']) || $_POST['code']=='DELETE') {
			redirectToNote(LNG('admin.msg.invalid'));
			exit;
		}
		if(!file_exists(FILES.$_POST['code'].'/')) {
			mkdir(FILES.$_POST['code'].'/');
			file_put_contents(FILES.$_POST['code'].'/lyric.txt',
				file_get_contents(RAW.'new-lyric.txt')
			);
			redirectToNote(LNG('admin.msg.created'));
			exit;
		}
		else {
			redirectToNote(LNG('admin.msg.create.occupied'));
			exit;
		}
	}

	if($_POST['isSubmit']=='rname-item') {
		if(!isset($_POST['ocode']) || $_POST['ocode']=='' || !isset($_POST['ncode']) || $_POST['ncode']=='') {
			redirectToNote(LNG('admin.msg.noinput'));
			exit;
		}
		if(!preg_match('/^(\w+)$/',$_POST['ocode']) || !preg_match('/^(\w+)$/',$_POST['ncode'])) {
			redirectToNote(LNG('admin.msg.invalid'));
			exit;
		}
		if(file_exists(FILES.$_POST['ocode'].'/')) {
			if(file_exists(FILES.$_POST['ncode'].'/')) {
				redirectToNote(LNG('admin.msg.rename.occupied'));
				exit;
			}
			if($_POST['ncode']!='DELETE') {
				rename(FILES.$_POST['ocode'].'/',FILES.$_POST['ncode'].'/');
				redirectToNote(LNG('admin.msg.renamed'));
				exit;
			}
			else {
				del_dir(FILES.$_POST['ocode'].'/');
				redirectToNote(LNG('admin.msg.deleted'));
				exit;
			}
		}
		else {
			redirectToNote(LNG('admin.msg.rename.tan90'));
			exit;
		}
	}

?>
<script>document.title='<?php LNGe('admin.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';</script>
<div class="txmp-page-full">
	<h3><?php LNGe('admin.title') ?></h3>
	<p><a href="<?php echo BASIC_URL ?>admin/users"><?php LNGe('ui.user_manager') ?></a><span style="width:8px;">&nbsp;&nbsp;&nbsp;&nbsp;</span><a href="<?php echo BASIC_URL ?>"><?php LNGe('ui.return_mainpage') ?></a></p>
	<?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
		<a href="javascript:;" onclick="F_HideNotice()" class="notice-confirm"><?php LNGe('ui.hide_notice') ?></a>
	</p><?php } ?>
	<ul>
		<?php
			$menu=dir_list(FILES);
			foreach($menu as $item) {
				if(isValidMusic($item,false)) {
					printAdminList($item);
				}
			}
		?>
	</ul>
	<p><form method="post" style="margin-bottom:4px;">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="create-item">
		<input type="text" name="code" autocomplete="off" placeholder="<?php LNGe('admin.input.newid') ?>" style="height:37px;width:30%;padding:4px;">
		<input type="submit" class="am-btn am-btn-primary" value="<?php LNGe('admin.action.create') ?>">
	</form><form method="post" id="rename-post">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="rname-item">
		<input type="text" name="ocode" autocomplete="off" placeholder="<?php LNGe('admin.input.oldid') ?>" style="height:37px;width:15%;padding:4px;">
		<input type="text" id="ncode" name="ncode" autocomplete="off" placeholder="<?php LNGe('admin.input.newid') ?>" style="height:37px;width:15%;padding:4px;">
		<input type="submit" onclick="confirm_rename();return false;" class="am-btn am-btn-primary" value="<?php LNGe('admin.action.rename') ?>">
		<script>
			async function confirm_rename() {
				if($('#ncode')[0].value!='DELETE') {
					if(await modal_confirm_p(LNG('ui.danger'),LNG('admin.warn.rename'))) {
						$('#rename-post').submit();
					}
				} else {
					if(await modal_confirm_p(LNG('ui.danger'),LNG('admin.warn.delete'))) {
						$('#rename-post').submit();
					}
				}
			}
		</script>
	</form></p>
</div>
