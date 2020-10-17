<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

	if($_POST['isSubmit']=='create-item') {
		if(!isset($_POST['code']) || $_POST['code']=='') {
			redirectToNote('请输入编号！');
			exit;
		}
		if(!preg_match('/^(\w+)$/',$_POST['code']) || $_POST['code']=='DELETE') {
			redirectToNote('非法输入！');
			exit;
		}
		if(!file_exists(FILES.$_POST['code'].'/')) {
			mkdir(FILES.$_POST['code'].'/');
			file_put_contents(FILES.$_POST['code'].'/lyric.txt',
				file_get_contents(RAW.'new-lyric.txt')
			);
			redirectToNote('创建成功！');
			exit;
		}
		else {
			redirectToNote('创建失败：编号已经存在！');
			exit;
		}
	}

	if($_POST['isSubmit']=='rname-item') {
		if(!isset($_POST['ocode']) || $_POST['ocode']=='' || !isset($_POST['ncode']) || $_POST['ncode']=='') {
			redirectToNote('请输入编号！');
			exit;
		}
		if(!preg_match('/^(\w+)$/',$_POST['ocode']) || !preg_match('/^(\w+)$/',$_POST['ncode'])) {
			redirectToNote('非法输入！');
			exit;
		}
		if(file_exists(FILES.$_POST['ocode'].'/')) {
			if(file_exists(FILES.$_POST['ncode'].'/')) {
				redirectToNote('改名错误：新的名称已存在');
				exit;
			}
			if($_POST['ncode']!='DELETE') {
				rename(FILES.$_POST['ocode'].'/',FILES.$_POST['ncode'].'/');
				redirectToNote('改名成功！');
				exit;
			}
			else {
				del_dir(FILES.$_POST['ocode'].'/');
				redirectToNote('删除成功！');
				exit;
			}
		}
		else {
			redirectToNote('改名失败：旧编号不存在。');
			exit;
		}
	}

?>
<script>document.title='歌曲管理面板 - <?php echo htmlspecial2(_C()['app_name_title']) ?>';</script>
<div class="txmp-page-full">
	<h3>歌曲管理面板</h3>
	<p><a href="<?php echo BASIC_URL ?>user/logout">注销账号</a><span style="width:8px;">&nbsp;&nbsp;&nbsp;&nbsp;</span><a href="<?php echo BASIC_URL ?>admin/users">管理用户</a><span style="width:8px;">&nbsp;&nbsp;&nbsp;&nbsp;</span><a href="<?php echo BASIC_URL ?>">返回主页</a></p>
	<?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
		<a href="javascript:;" onclick="F_HideNotice()">知道了</a>
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
		<input type="text" name="code" autocomplete="off" placeholder="输入新歌曲号" style="height:37px;width:30%;padding:4px;">
		<input type="submit" class="am-btn am-btn-primary" value="创建">
	</form><form method="post" id="rename-post">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="rname-item">
		<input type="text" name="ocode" autocomplete="off" placeholder="输入旧歌曲号" style="height:37px;width:15%;padding:4px;">
		<input type="text" id="ncode" name="ncode" autocomplete="off" placeholder="输入新歌曲号" style="height:37px;width:15%;padding:4px;">
		<input type="submit" onclick="confirm_rename();return false;" class="am-btn am-btn-primary" value="更名">
		<script>
			async function confirm_rename() {
				if($('#ncode')[0].value!='DELETE') {
					if(await modal_confirm_p('危','更名将会导致指向该歌曲的链接失效。<br>仅当你查阅歌单发现编号错误，或者你创建时没有妥善管理编号，使用了临时编号时才修改编号。<br>是否继续？')) {
						$('#rename-post').submit();
					}
				} else {
					if(await modal_confirm_p('危','删除过程不可恢复，是否继续？')) {
						$('#rename-post').submit();
					}
				}
			}
		</script>
	</form></p>
</div>
