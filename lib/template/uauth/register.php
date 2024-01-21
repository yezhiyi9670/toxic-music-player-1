<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isset($_POST['isSubmit']));
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
		$str = LNG('uauth.ureg.success');
		echo '<script>location.href="' . addslashes(BASIC_URL . 'user/login') .  '"+"?msg="+encodeURIComponent("'.jsspecial($str).'")</script>';
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
	document.title='<?php echo LNGj('reg.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';
	set_section_name(LNG('reg.title'));
</script>
<?php showToastMessage(); ?>
<div class="txmp-page-full">
	<?php if(_CT('can_register')) { ?>
	<h3><?php LNGe('reg.caption') ?></h3>
	<p><form method="post">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="register">
		<input style="margin-bottom:8px;" type="text" name="name" autocomplete="off" placeholder="<?php LNGe('reg.field.username') ?>"><br>
		<input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="<?php LNGe('reg.field.password') ?>"><br>
		<input style="margin-bottom:8px;" type="password" name="passagain" autocomplete="off" placeholder="<?php LNGe('reg.field.password2') ?>"><br>
		<?php $limit_exceeded = uauth_ip_cnt($_SERVER['REMOTE_ADDR']) >= _CT('ip_reg_limit') ?>
		<input type="submit" class="am-btn am-btn-danger <?php if($limit_exceeded) echo 'am-disabled' ?>" value="<?php LNGe('reg.field.reg') ?>">
		<?php if($limit_exceeded) { ?>
		<p><?php LNGe('reg.exceeded') ?></p>
		<?php } ?>
	</form></p>
	<?php } else { ?>
		<p><?php LNGe('reg.notallow') ?></p>
	<?php } ?>
	<hr />
	<p>
		<?php LNGe('reg.login.hint') ?>
		<a href="<?php echo BASIC_URL ?>user/login"><?php LNGe('reg.login.action') ?></a>
	</p>
</div>
