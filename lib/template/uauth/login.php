<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isset($_POST['isSubmit']));
else if($_POST['isSubmit']=='login') {
    $status=uauth_login($_POST['name'],$_POST['pass']);
    if($status=='passwrong') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.login.wrongpass').'")</script>';
        exit;
    }
    else if($status=='loggedin') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.login.loggedin').'")</script>';
        exit;
    }
    else if($status=='ban') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.login.banned').'")</script>';
        exit;
    }
    else if($status=='nxuser') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.login.nxuser').'")</script>';
        exit;
    }
    {echo '<script>location.href=location.href;</script>';exit;}
}
else if($_POST['isSubmit']=='register') {
    if(!_CT('can_register')) {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.ureg.notallow').'")</script>';
        exit;
    }
    if(!$_POST['pass']) {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.ureg.nopass').'")</script>';
        exit;
    }
    if($_POST['pass'] !== $_POST['passagain']) {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.ureg.wrongpass').'")</script>';
        exit;
    }
    if(!$_POST['name'] || strlen($_POST['name'])<3 || strlen($_POST['name'])>14) {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.ureg.namelen').'")</script>';
        exit;
    }
    $status = uauth_register($_POST['name'],$_POST['pass'],false,'none');
    if($status == 'success') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.ureg.success').'")</script>';
        exit;
    }
    else if($status == 'exist') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.ureg.occupied').'")</script>';
        exit;
    }
    else if($status == 'illegal') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.ureg.illegal').'")</script>';
        exit;
    }
    else if($status == 'loggedin') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.ureg.loggedin').'")</script>';
        exit;
    }
    else if($status == 'limit') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.ureg.limited').'")</script>';
        exit;
    }
    echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("'.LNGk('uauth.ureg.uke',$status).'")</script>';
    exit;
}
else {echo '<script>location.href=location.href;</script>';exit;}

?>
<script>
    document.title='<?php echo LNGk('login.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>';
    set_section_name(LNG('login.title'));
</script>
<div class="txmp-page-full">
    <h3><?php LNGe('login.login.caption') ?></h3>
    <?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
        <a href="javascript:;" onclick="F_HideNotice()" class="notice-confirm"><?php LNGe('ui.hide_notice') ?></a>
    </p><?php } ?>
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
