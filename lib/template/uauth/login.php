<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isset($_POST['isSubmit']));
else if($_POST['isSubmit']=='login') {
	$status=uauth_login($_POST['name'],$_POST['pass']);
	if($status=='passwrong') {
		redirectToNote(LNG('uauth.login.wrongpass'));
		exit;
	}
	else if($status=='loggedin') {
		redirectToNote(LNG('uauth.login.loggedin'));
		exit;
	}
	else if($status=='ban') {
		redirectToNote(LNG('uauth.login.banned'));
		exit;
	}
	else if($status=='nxuser') {
		redirectToNote(LNG('uauth.login.nxuser'));
		exit;
	}
	{redirectToGet();exit;}
}
else if($_POST['isSubmit']=='register') {
	if(!_CT('can_register')) {
		redirectToNote(LNG('uauth.ureg.notallow'));
		exit;
	}
	if(!$_POST['pass']) {
		redirectToNote(LNG('uauth.ureg.nopass'));
		exit;
	}
	if($_POST['pass'] !== $_POST['passagain']) {
		redirectToNote(LNG('uauth.ureg.wrongpass'));
		exit;
	}
	if(!$_POST['name'] || strlen($_POST['name'])<3 || strlen($_POST['name'])>14) {
		redirectToNote(LNG('uauth.ureg.namelen'));
		exit;
	}
	$status = uauth_register($_POST['name'],$_POST['pass'],false,'none');
	if($status == 'success') {
		redirectToNote(LNG('uauth.ureg.success'));
		exit;
	}
	else if($status == 'exist') {
		redirectToNote(LNG('uauth.ureg.occupied'));
		exit;
	}
	else if($status == 'illegal') {
		redirectToNote(LNG('uauth.ureg.illegal'));
		exit;
	}
	else if($status == 'loggedin') {
		redirectToNote(LNG('uauth.ureg.loggedin'));
		exit;
	}
	else if($status == 'limit') {
		redirectToNote(LNG('uauth.ureg.limited'));
		exit;
	}
	redirectToNote(LNG('uauth.ureg.uke',$status));
	exit;
}
else {redirectToGet();exit;}

?>
<script>
	document.title='<?php echo LNGj('login.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';
	set_section_name(LNG('login.title'));
</script>
<div class="txmp-page-full">
	<h3><?php LNGe('login.login.caption') ?></h3>
	<?php showToastMessage(); ?>
	<p><form method="post">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="login">
		<input style="margin-bottom:8px;" type="text" name="name" autocomplete="off" placeholder="<?php LNGe('login.field.username') ?>"><br>
		<input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="<?php LNGe('login.field.password') ?>"><br>
		<input type="submit" class="am-btn am-btn-primary" value="<?php LNGe('login.field.login') ?>">
	</form></p>
	<hr>
	<?php if(_CT('can_register')) { ?>
	<p><?php LNGe('login.reg.caption') ?></p>
	<p><form method="post">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="register">
		<input style="margin-bottom:8px;" type="text" name="name" autocomplete="off" placeholder="<?php LNGe('login.field.username') ?>"><br>
		<input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="<?php LNGe('login.field.password') ?>"><br>
		<input style="margin-bottom:8px;" type="password" name="passagain" autocomplete="off" placeholder="<?php LNGe('login.field.password2') ?>"><br>
		<input type="submit" class="am-btn am-btn-danger <?php if(uauth_ip_cnt($_SERVER['REMOTE_ADDR']) >= _CT('ip_reg_limit')) echo 'am-disabled' ?>" value="<?php LNGe('login.field.reg') ?>">
	</form></p>
	<?php } else { ?>
		<?php LNGe('login.reg.notallow') ?>
	<?php } ?>
</div>
