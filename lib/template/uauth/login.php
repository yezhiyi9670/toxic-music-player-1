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
else {redirectToGet();exit;}

?>
<script>
	document.title='<?php echo LNGj('login.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';
	set_section_name(LNG('login.title'));
</script>
<div class="txmp-page-full">
	<h3><?php LNGe('login.caption') ?></h3>
	<?php showToastMessage(); ?>
	<p><form method="post">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="login">
		<input style="margin-bottom:8px;" type="text" name="name" autocomplete="off" placeholder="<?php LNGe('login.field.username') ?>"><br>
		<input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="<?php LNGe('login.field.password') ?>"><br>
		<input type="submit" class="am-btn am-btn-primary" value="<?php LNGe('login.field.login') ?>">
	</form></p>
	<?php if(_CT('can_register')) { ?>
	<hr />
	<p>
		<?php LNGe('login.reg.hint') ?>
		<a href="<?php echo BASIC_URL ?>user/register"><?php LNGe('login.reg.action') ?></a>
	</p>
	<?php } ?>
</div>
