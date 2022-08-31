<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isset($_POST['isSubmit']));
else if($_POST['isSubmit']=='passwd') {
	$uname = uauth_username();
	if(!$uname) {
		redirectToNote(LNG('pass.err.notlogin'));
		exit;
	}
	else if(!uauth_veri_pass($uname,$_POST['pass'])) {
		redirectToNote(LNG('pass.err.wrongpass'));
		exit;
	}
	else if($_POST['newpass'] != $_POST['newpassagain']) {
		redirectToNote(LNG('pass.err.differ'));
		exit;
	}
	else {
		$status = uauth_update_pass($uname,$_POST['newpass']);
		if($status=='illegal') {
			redirectToNote(LNG('ui.uke'));
			exit;
		}
		else if($status=='nxuser') {
			redirectToNote(LNG('ui.uke'));
			exit;
		}
		else if($status=='success') {
			redirectToNote(LNG('pass.success'));
			exit;
		}
		else {echo '<script>location.href=location.href;</script>';exit;}
	}
}
else if($_POST['isSubmit']=='remove') {
	$uname = uauth_username();
	if(!$uname) {
		redirectToNote(LNG('pass.err.notlogin'));
		exit;
	}
	else if(!uauth_veri_pass($uname,$_POST['pass'])) {
		redirectToNote(LNG('pass.err.wrongpass'));
		exit;
	}
	else if($_POST['rm_uname'] != $uname) {
		redirectToNote(LNG('pass.err.wrongname'));
		exit;
	}
	else {
		$status = uauth_delete($uname);
		if($status=='illegal') {
			redirectToNote(LNG('ui.uke'));
			exit;
		}
		else if($status=='nxuser') {
			redirectToNote(LNG('ui.uke'));
			exit;
		}
		else if($status=='success') {
			redirectToNote(LNG('pass.success'));
			exit;
		}
		else {echo '<script>location.href=location.href;</script>';exit;}
	}
}
else {echo '<script>location.href=location.href;</script>';exit;}

?>
<script>
	document.title='<?php LNGe('pass.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';
	set_section_name(LNG('pass.title'));
</script>
<div class="txmp-page-full">
	<h3><?php LNGe('pass.title') ?></h3>
	<?php showToastMessage(); ?>
	<?php if(uauth_username()) { ?>
	<p><form method="post">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="passwd">
		<input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="<?php LNGe('pass.field.old') ?>"><br>
		<input style="margin-bottom:8px;" type="password" name="newpass" autocomplete="off" placeholder="<?php LNGe('pass.field.new') ?>"><br>
		<input style="margin-bottom:8px;" type="password" name="newpassagain" autocomplete="off" placeholder="<?php LNGe('pass.field.new2') ?>"><br>
		<input type="submit" class="am-btn am-btn-secondary" value="<?php LNGe('pass.field.change') ?>">
	</form></p>
	<hr>
	<p><strong><?php LNGe('pass.remove.caption') ?></strong></p>
	<p><form method="post">
		<input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
		<input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
		<input type="hidden" name="isSubmit" value="remove">
		<input style="margin-bottom:8px;" type="text" name="rm_uname" autocomplete="off" placeholder="<?php LNGe('pass.field.username') ?>"><br>
		<input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="<?php LNGe('pass.field.pass') ?>"><br>
		<input type="submit" class="am-btn am-btn-danger" value="<?php LNGe('pass.field.delete') ?>">
	</form></p>
	<hr>
	<p><strong><?php LNGe('pass.dump.caption') ?></strong></p>
	<p><button class="am-btn am-btn-warning" onclick="dump_cookie()"><?php LNGe('pass.dump.action') ?></button></p>
	<script>
		async function dump_cookie() {
			function getCookie(cname) {
				var name = cname + "=";
				var ca = document.cookie.split(';');
				for(var i=0; i<ca.length; i++) {
					var c = ca[i].trim();
					if (c.indexOf(name)==0) return c.substring(name.length,c.length);
				}
				return "";
			}
			if(!await modal_confirm_p(LNG('pass.alert.dump'),LNG('pass.alert.dump.tips'))) {
				return;
			}
			var p1 = getCookie('X-' + G.app_prefix + '-uauth-session');
			var p2 = getCookie('X-' + G.app_prefix + '-uauth-token');
			await modal_promptarea_p(LNG('pass.alert.dumped'),'',
				Base64.encode('X-' + G.app_prefix + '-uauth-session') + "~" +
				Base64.encode(p1) + "~" +
				Base64.encode('X-' + G.app_prefix + '-uauth-token') + "~" +
				Base64.encode(p2)
			);
		}
	</script>
	<?php } else { ?>
		<?php LNGe('pass.login_first') ?>
	<?php } ?>
</div>
