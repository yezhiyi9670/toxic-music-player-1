<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isset($_POST['isSubmit']));
else if($_POST['isSubmit']=='changeType') {
	if(!isset(get_all_user_types()[$_POST['status']])) {
		echo '用户类型 '.$_POST['status'].' 不被接受！';
		exit;
	}
	$status=uauth_update_enabled($_POST['name'],get_all_user_types()[$_POST['status']][0]);
	if($status=='illegal') {
		echo '系统错误';
		exit;
	}
	else if($status=='nxuser') {
		echo '用户不存在';
		exit;
	}
	else if($status=='success') {
		echo '';
		exit;
	}
	{echo '异常状态';exit;}
}
else if($_POST['isSubmit']=='remove') {
	$status=uauth_delete($_POST['name']);
	if($status=='illegal') {
		echo '系统错误';
		exit;
	}
	else if($status=='nxuser') {
		echo '用户不存在';
		exit;
	}
	else if($status=='success') {
		echo '';
		exit;
	}
	{echo '异常状态';exit;}
}
else if($_POST['isSubmit']=='add') {
	if(!$_POST['pass']) {
		echo '注册错误：不可以没有密码！';
		exit;
	}
	if(!$_POST['name'] || strlen($_POST['name'])<3 || strlen($_POST['name'])>14) {
		echo '注册错误：用户名长度只允许2到14';
		exit;
	}
	$status = uauth_register($_POST['name'],$_POST['pass'],true,$_POST['ip'] ? $_POST['ip'] : 'none');
	if($status == 'success') {
		echo '';
		exit;
	}
	else if($status == 'exist') {
		$status = uauth_update_pass($_POST['name'],$_POST['pass']);
		if($status=='illegal') {
			echo '系统错误';
			exit;
		}
		else if($status=='nxuser') {
			echo '系统错误';
			exit;
		}
		else if($status=='success') {
			echo '';
			exit;
		}
		else echo '异常状态';
		exit;
	}
	else if($status == 'illegal') {
		echo '注册错误：用户名不合法';
		exit;
	}
	else if($status == 'loggedin') {
		echo '注册错误：你已经登录了';
		exit;
	}
	else if($status == 'limit') {
		echo '注册错误：你注册的账户数量已经到达限额';
		exit;
	}
	echo '未预料的注册状态：'.$status;
	exit;
}
else if($_POST['isSubmit']=='editName') {
	if(!$_POST['newname'] || strlen($_POST['newname'])<3 || strlen($_POST['newname'])>14) {
		echo '用户名长度只允许2到14';
		exit;
	}
	$status = uauth_rename($_POST['name'],$_POST['newname']);
	if($status == 'success') {
		echo '';
		exit;
	}
	else if($status == 'exist') {
		echo '用户名已经被占用';
		exit;
	}
	else if($status == 'nxuser') {
		echo '用户不存在';
		exit;
	}
	else if($status == 'illegal') {
		echo '新用户名非法';
		exit;
	}
	echo '未预料的状态：'.$status;
	exit;
}
else if($_POST['isSubmit']=='login') {
	if(uauth_username()) {
		uauth_logout();
		$GLOBALS['uauth_username'] = '-'; // Clear cache
	}
	$status=uauth_login($_POST['name'],'',true);
	if($status=='nxuser') {
		echo '用户不存在';
		exit;
	}
	else if($status=='ban') {
		echo '无法登录被封禁的用户';
		exit;
	}
	else if($status=='loggedin') {
		echo '你已经登录了（非正常状态）';
		exit;
	}
	else if($status=='success') {
		echo '';
		exit;
	}
	{echo '异常状态';exit;}
}
else {
	echo '未预料的操作';
	exit;
}

?>

<script>document.title='用户管理 - <?php echo htmlspecial2(_C()['app_name_title']) ?>';</script>
<script>
	var userTypes = <?php echo json_encode(get_all_user_types()); ?>;
</script>
<div class="txmp-page-full">
	<h3>用户管理</h3>
	<?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
		<a href="javascript:;" onclick="F_HideNotice()">知道了</a>
	</p><?php } ?>
	<p><a href="<?php echo BASIC_URL ?>admin">返回管理主页</a></p>
	<style>
		.user-manage-table th,
		.user-manage-table td {
			padding: 8px;
			border: 1px solid #DDD;
		}
	</style>
	<table class="user-manage-table">
		<tr>
			<th>用户名</th>
			<th>密码哈希</th>
			<th>类型</th>
			<th>注册IP</th>
			<th>操作</th>
		</tr>
		<?php
			$list = uauth_get_all();
			foreach($list as $item) {
				printUserList($item);
			}
		?>
	</table>
	<hr>
	<p><form onsubmit="createUser();return false;" id="editPassForm">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="add">
		<input style="margin-bottom:8px;" type="text" name="name" autocomplete="off" placeholder="用户名"><br>
		<input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="密码"><br>
		<input style="margin-bottom:8px;" type="text" name="ip" autocomplete="off" placeholder="注册IP （留空将会取当前IP）"><br>
		<input type="submit" class="am-btn am-btn-danger" value="注册用户 / 修改密码">
	</form></p>
	<script>
		async function changeType(uname) {
			$('.am-btn').addClass('am-disabled');
			var dtype = await modal_prompt_p('更改用户类型','请输入欲更改的用户类型<br>（ban, normal, root）');
			if(!dtype) {$('.am-btn').removeClass('am-disabled');return;}
			$.ajax({
				async: true,
				type: 'POST',
				url: '<?php echo BASIC_URL ?>admin/users',
				dataType: 'text',
				data: {
					isSubmit: 'changeType',
					isAjax: true,
					'csrf-token-name': '<?php echo $GLOBALS['sess']; ?>',
					'csrf-token-value': '<?php echo $GLOBALS['token']; ?>',
					status: dtype,
					name: uname,
				},
				success: function(t){
					if(t) {
						$('.am-btn').removeClass('am-disabled');
						modal_alert('无法操作',t);
					}
					else {
						$('.am-btn').removeClass('am-disabled');

						$('[data-username=' + uname + '] .user-ban').html(userTypes[dtype][1]);
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert('无法操作','系统错误');
				},
			});
		}
		async function editName(uname) {
			$('.am-btn').addClass('am-disabled');
			var ddname = await modal_prompt_p('修改用户名','输入新用户名');
			if(!ddname) {$('.am-btn').removeClass('am-disabled');return;}
			$.ajax({
				async: true,
				type: 'POST',
				url: '<?php echo BASIC_URL ?>admin/users',
				dataType: 'text',
				data: {
					isSubmit: 'editName',
					isAjax: true,
					'csrf-token-name': '<?php echo $GLOBALS['sess']; ?>',
					'csrf-token-value': '<?php echo $GLOBALS['token']; ?>',
					name: uname,
					newname: ddname,
				},
				success: function(t){
					if(t) {
						$('.am-btn').removeClass('am-disabled');
						modal_alert('无法操作',t);
					}
					else {
						$('.am-btn').removeClass('am-disabled');
						$('[data-username=' + uname + '] .user-name').html(ddname);
						$('[data-username=' + uname + ']').attr('data-username',ddname);
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert('无法操作','系统错误');
				},
			});
		}
		async function editPass(uname) {
			// $('[name=name]')[0].value = uname;
			// location.href = '#editPassForm';

			$('.am-btn').addClass('am-disabled');
			var passwd = await modal_prompt_p('修改密码','输入新密码','','password');
			if(!passwd) {$('.am-btn').removeClass('am-disabled');return;}
			$.ajax({
				async: true,
				type: 'POST',
				url: '<?php echo BASIC_URL ?>admin/users',
				dataType: 'text',
				data: {
					isSubmit: 'add',
					isAjax: true,
					'csrf-token-name': '<?php echo $GLOBALS['sess']; ?>',
					'csrf-token-value': '<?php echo $GLOBALS['token']; ?>',
					name: uname,
					pass: passwd,
				},
				success: function(t){
					if(t) {
						$('.am-btn').removeClass('am-disabled');
						modal_alert('无法操作',t);
					}
					else {
						$('.am-btn').removeClass('am-disabled');
						$('[data-username=' + uname + '] .user-hash a').attr('onclick',"modal_alert('密码哈希','密码哈希的前8位是："+md5(passwd).substr(0,8)+"')");
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert('无法操作','系统错误');
				},
			});
		}
		async function remove(uname) {
			if(!await modal_confirm_by_input('删除用户','该用户的用户名',uname)) return;

			$('.am-btn').addClass('am-disabled');
			$.ajax({
				async: true,
				type: 'POST',
				url: '<?php echo BASIC_URL ?>admin/users',
				dataType: 'text',
				data: {
					isSubmit: 'remove',
					isAjax: true,
					'csrf-token-name': '<?php echo $GLOBALS['sess']; ?>',
					'csrf-token-value': '<?php echo $GLOBALS['token']; ?>',
					name: uname,
				},
				success: function(t){
					if(t) {
						$('.am-btn').removeClass('am-disabled');
						modal_alert('无法操作',t);
					}
					else {
						$('.am-btn').removeClass('am-disabled');
						$('[data-username=' + uname + ']').remove();
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert('无法操作','系统错误');
				},
			});
		}
		function createUser() {
			$('.am-btn').addClass('am-disabled');
			$.ajax({
				async: true,
				type: 'POST',
				url: '<?php echo BASIC_URL ?>admin/users',
				dataType: 'text',
				data: {
					isSubmit: 'add',
					isAjax: true,
					'csrf-token-name': '<?php echo $GLOBALS['sess']; ?>',
					'csrf-token-value': '<?php echo $GLOBALS['token']; ?>',
					name: $('[name=name]')[0].value,
					pass: $('[name=pass]')[0].value,
					ip: $('[name=ip]')[0].value,
				},
				success: function(t){
					if(t) {
						$('.am-btn').removeClass('am-disabled');
						modal_alert('无法操作',t);
					}
					else {
						$('.am-btn').removeClass('am-disabled');
						history.go(0);
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert('无法操作','系统错误');
				},
			});
		}
		async function loginAs(uname) {
			if(!await modal_confirm_p('确定登录','你即将登录另一个用户 ' + uname + '。<br>如需回到管理员状态，需重新进行登录。<br>你确定？')) return;
			$('.am-btn').addClass('am-disabled');
			$.ajax({
				async: true,
				type: 'POST',
				url: '<?php echo BASIC_URL ?>admin/users',
				dataType: 'text',
				data: {
					isSubmit: 'login',
					isAjax: true,
					'csrf-token-name': '<?php echo $GLOBALS['sess']; ?>',
					'csrf-token-value': '<?php echo $GLOBALS['token']; ?>',
					name: uname,
				},
				success: function(t){
					if(t) {
						$('.am-btn').removeClass('am-disabled');
						modal_alert('无法操作',t);
					}
					else {
						history.go(0);
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert('无法操作','系统错误');
				},
			});
		}

		function gUserName(x) {
			return x.parentElement.parentElement.getAttribute('data-username');
		}
	</script>
</div>

