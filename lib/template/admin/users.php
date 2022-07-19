<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isset($_POST['isSubmit']));
else if($_POST['isSubmit']=='changeType') {
	if(!isset(get_all_user_types()[$_POST['status']])) {
		echo LNG('uauth.changetype.wrong',$_POST['status']);
		exit;
	}
	$status=uauth_update_enabled($_POST['name'],get_all_user_types()[$_POST['status']][0]);
	if($status=='illegal') {
		echo LNG('uauth.changetype.illegal');
		exit;
	}
	else if($status=='nxuser') {
		echo LNG('uauth.changetype.nxuser');
		exit;
	}
	else if($status=='success') {
		echo '';
		exit;
	}
	{echo LNG('uauth.msg.abnormal');exit;}
}
else if($_POST['isSubmit']=='remove') {
	$status=uauth_delete($_POST['name']);
	if($status=='illegal') {
		echo LNG('uauth.remove.illegal');
		exit;
	}
	else if($status=='nxuser') {
		echo LNG('uauth.remove.nxuser');
		exit;
	}
	else if($status=='success') {
		echo '';
		exit;
	}
	{echo LNG('uauth.msg.abnormal');exit;}
}
else if($_POST['isSubmit']=='add') {
	if(!$_POST['pass']) {
		echo LNG('uauth.reg.nopass');
		exit;
	}
	if(!$_POST['name'] || strlen($_POST['name'])<3 || strlen($_POST['name'])>14) {
		echo LNG('uauth.reg.usernamelen');
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
			echo LNG('uauth.msg.abnormal');
			exit;
		}
		else if($status=='nxuser') {
			echo LNG('uauth.msg.abnormal');
			exit;
		}
		else if($status=='success') {
			echo '';
			exit;
		}
		else echo LNG('uauth.msg.abnormal');
		exit;
	}
	else if($status == 'illegal') {
		echo LNG('uauth.reg.illegal');
		exit;
	}
	else if($status == 'loggedin') {
		echo LNG('uauth.reg.loggedin');
		exit;
	}
	else if($status == 'limit') {
		echo LNG('uauth.reg.limit');
		exit;
	}
	echo LNG('uauth.msg.abnormal');
	exit;
}
else if($_POST['isSubmit']=='editName') {
	if(!$_POST['newname'] || strlen($_POST['newname'])<3 || strlen($_POST['newname'])>14) {
		echo LNG('uauth.changename.len');
		exit;
	}
	$status = uauth_rename($_POST['name'],$_POST['newname']);
	if($status == 'success') {
		echo '';
		exit;
	}
	else if($status == 'exist') {
		echo LNG('uauth.changename.exist');
		exit;
	}
	else if($status == 'nxuser') {
		echo LNG('uauth.changename.nxuser');
		exit;
	}
	else if($status == 'illegal') {
		echo LNG('uauth.changename.illegal');
		exit;
	}
	echo LNG('uauth.msg.abnormal');
	exit;
}
else if($_POST['isSubmit']=='login') {
	if(uauth_username()) {
		uauth_logout();
		$GLOBALS['uauth_username'] = '-'; // Clear cache
	}
	$status=uauth_login($_POST['name'],'',true);
	if($status=='nxuser') {
		echo LNG('uauth.hackin.nxuser');
		exit;
	}
	else if($status=='ban') {
		echo LNG('uauth.hackin.banned');
		exit;
	}
	else if($status=='loggedin') {
		echo LNG('uauth.hackin.loggedin');
		exit;
	}
	else if($status=='success') {
		echo '';
		exit;
	}
	{echo LNG('uauth.msg.abnormal');exit;}
}
else {
	echo LNG('ui.undefined_submit');
	exit;
}

?>

<script>
	document.title='<?php LNGe('ua.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';
	set_section_name(LNG('ua.title'));
</script>
<?php declare_allow_overscroll() ?>
<script>
	var userTypes = <?php echo encode_data(get_all_user_types()); ?>;
</script>
<div class="txmp-page-full">
	<h3><?php LNGe('ua.title') ?></h3>
	<?php showToastMessage(); ?>
	<p><a href="<?php echo BASIC_URL ?>admin"><?php LNGe('ui.return_admin') ?></a></p>
	<style>
		.user-manage-table th,
		.user-manage-table td {
			padding: 8px;
			border: 1px solid #DDD;
		}
		body {
			overflow-x: auto;
		}
	</style>
	<table class="user-manage-table">
		<tr>
			<th><?php LNGe('ua.caption.username') ?></th>
			<th><?php LNGe('ua.caption.passhash') ?></th>
			<th><?php LNGe('ua.caption.type') ?></th>
			<th><?php LNGe('ua.caption.regip') ?></th>
			<th><?php LNGe('ua.caption.op') ?></th>
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
		<input style="margin-bottom:8px;" type="text" name="name" autocomplete="off" placeholder="<?php LNGe('ua.field.username') ?>"><br>
		<input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="<?php LNGe('ua.field.password') ?>"><br>
		<input style="margin-bottom:8px;" type="text" name="ip" autocomplete="off" placeholder="<?php LNGe('ua.field.regip') ?>"><br>
		<input type="submit" class="am-btn am-btn-danger" value="<?php LNGe('ua.field.submit') ?>">
	</form></p>
	<script>
		async function changeType(uname) {
			$('.am-btn').addClass('am-disabled');
			var dtype = await modal_prompt_p(LNG('ua.qr.changetype'),LNG('ua.qr.changetype.tip'));
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
						modal_alert(LNG('ua.qr.fail'),t);
					}
					else {
						$('.am-btn').removeClass('am-disabled');

						$('[data-username=' + uname + '] .user-ban').html(userTypes[dtype][1]);
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert(LNG('ua.qr.fail'),LNG('ui.uke'));
				},
			});
		}
		async function editName(uname) {
			$('.am-btn').addClass('am-disabled');
			var ddname = await modal_prompt_p(LNG('ua.qr.changename'),LNG('ua.qr.changename.tip'));
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
						modal_alert(LNG('ua.qr.fail'),t);
					}
					else {
						$('.am-btn').removeClass('am-disabled');
						$('[data-username=' + uname + '] .user-name').html(ddname);
						$('[data-username=' + uname + ']').attr('data-username',ddname);
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert(LNG('ua.qr.fail'),LNG('ui.uke'));
				},
			});
		}
		async function editPass(uname) {
			// $('[name=name]')[0].value = uname;
			// location.href = '#editPassForm';

			$('.am-btn').addClass('am-disabled');
			var passwd = await modal_prompt_p(LNG('ua.qr.changepass'),LNG('ua.qr.changepass.tip'),'','password');
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
						modal_alert(LNG('ua.qr.fail'),t);
					}
					else {
						$('.am-btn').removeClass('am-disabled');
						$('[data-username=' + uname + '] .user-hash a').attr('onclick',"modal_alert('"+LNG('uauth.ui.passhash')+"','"+LNG('uauth.ui.passhash.fail')+"')");
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert(LNG('ua.qr.fail'),LNG('ui.uke'));
				},
			});
		}
		async function remove(uname) {
			if(!await modal_confirm_by_input(LNG('ua.qr.remove.action'),LNG('ua.qr.remove.prompt'),uname)) return;

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
						modal_alert(LNG('ua.qr.fail'),t);
					}
					else {
						$('.am-btn').removeClass('am-disabled');
						$('[data-username=' + uname + ']').remove();
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert(LNG('ua.qr.fail'),LNG('ui.uke'));
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
						modal_alert(LNG('ua.qr.fail'),t);
					}
					else {
						$('.am-btn').removeClass('am-disabled');
						history.go(0);
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert(LNG('ua.qr.fail'),LNG('ui.uke'));
				},
			});
		}
		async function loginAs(uname) {
			if(!await modal_confirm_p(LNG('ua.qr.hackin'),LNG('ua.qr.hackin.tip',uname))) return;
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
						modal_alert(LNG('ua.qr.fail'),t);
					}
					else {
						history.go(0);
					}
				},
				error: function(t){
					$('.am-btn').removeClass('am-disabled');
					modal_alert(LNG('ua.qr.fail'),LNG('ui.uke'));
				},
			});
		}

		function gUserName(x) {
			return x.parentElement.parentElement.getAttribute('data-username');
		}
	</script>
</div>

