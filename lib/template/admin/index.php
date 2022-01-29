<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

	// 创建
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
				str_replace(
					['%{V}'],
					[DATAVER],
					file_get_contents(RAW.'new-lyric.txt')
				)
			);
			// redirectToNote(LNG('admin.msg.created'));
			redirectToPage($_POST['code'] . '/resource');
			exit;
		}
		else {
			redirectToNote(LNG('admin.msg.create.occupied'));
			exit;
		}
	}

	// 改编号
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
<script>
	document.title='<?php LNGe('admin.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';
	set_section_name(LNG('admin.title'));
</script>
<?php declare_allow_overscroll() ?>
<div class="txmp-page-full">
	<h3><?php LNGe('admin.title') ?></h3>
	<p><a href="<?php echo BASIC_URL ?>admin/users"><?php LNGe('ui.user_manager') ?></a><span style="width:8px;">&nbsp;&nbsp;&nbsp;&nbsp;</span><a href="<?php echo BASIC_URL ?>"><?php LNGe('ui.return_mainpage') ?></a></p>
	<?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
		<a href="javascript:;" onclick="F_HideNotice()" class="notice-confirm"><?php LNGe('ui.hide_notice') ?></a>
	</p><?php } ?>
	<div class="tooltip-box">
		<p><?php LNGe('admin.select_page') ?>&nbsp;&nbsp;
			<a onclick="show_page('all')"><?php LNGe('admin.page.all') ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a onclick="show_page('comp')"><?php LNGe('admin.page.comp') ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
			<a onclick="show_page('ann')"><?php LNGe('admin.page.ann') ?></a>
			<script>
				function show_page(name) {
					$('.page').hide();
					$('.page-' + name).show();
				}
			</script>
	</div>
	<div class="page page-all">
		<strong><?php LNGe('admin.page.all') ?></strong>
		<ul>
			<?php
				$menu=dir_list(FILES);
				$item_count = 0;
				foreach($menu as $item) {
					if(isValidMusic($item,false)) {
						printAdminList($item);
						$item_count++;
					}
				}

				if($item_count == 0) {
					echo '<li>';
					LNGe('list.list.empty');
					echo '</li>';
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
	<div class="page page-comp" style="display:none;">
		<strong><?php LNGe('admin.page.comp') ?></strong>
		<p>
			<select class="sel-query-comp field-large">
				<?php
					foreach(['all','unstd','unstd_only','warn','error','fatal'] as $t) {
						echo '<option value="'.$t.'">';
						LNGe('admin.compl.' . $t);
						echo '</option>';
					}
				?>
			</select>
			<button class="am-btn am-btn-primary btn-query-comp" onclick="query_result('comp')"><?php LNGe('admin.query') ?></button>
		</p>
		<style>
			.comp-close,.ann-close {
				margin-left: 32px;
				margin-top: -20px;
				margin-bottom: 15px;
				font-size: 0.9em;
				color: #FF0000;
			}
			.comp-close a,.ann-close a {
				color: #FF0000;
			}
			.codeblock.comp-info,.codeblock.ann-info {
				padding-left: 31px;margin-top: -24px;margin-bottom: 8px;font-size:14px
			}
			.comp-show, .ann-show {
				padding-bottom: 400px;
				margin-bottom: -400px;
			}
		</style>
		<div class="comp-show"></div>
	</div>
	<div class="page page-ann" style="display:none;">
		<strong><?php LNGe('admin.page.ann') ?></strong>
		<p>
			<select class="sel-query-ann field-large">
				<?php
					$nmlist = getListOf('ann');
					foreach($nmlist as $k => $v) {
						echo '<option value="'.$k.'">@' . $k . ' ' . $v . '</option>';
					}
				?>
			</select>
			<button class="am-btn am-btn-primary btn-query-ann" onclick="query_result('ann')"><?php LNGe('admin.query') ?></button>
		</p>
		<div class="ann-show"></div>
	</div>
	<script>
		function query_result(flag) {
			var al_id = modal_loading(LNG('ui.wait','admin.msg.querying'));
			var $btn = $('.btn-query-' + flag);
			var $box = $('.' + flag + '-show');
			$.ajax({
				async:true,
				timeout:9000,
				dataType:"text",
				url:G.basic_url + 'admin/query-' + flag + ('?' + 'key=' + $('.sel-query-' + flag).val()),
				error:function(e){
					close_modal(al_id);
					modal_alert(LNG('ui.error'),LNG('admin.msg.query_fail'));
				},
				success:function(e){
					close_modal(al_id);
					$box.html(e);
				}
			});
		}
	</script>
</div>
