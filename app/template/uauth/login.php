<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isset($_POST['isSubmit']));
else if($_POST['isSubmit']=='login') {
    $status=uauth_login($_POST['name'],$_POST['pass']);
    if($status=='passwrong') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("密码错误！")</script>';
        exit;
    }
    else if($status=='loggedin') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("你已经登录过了")</script>';
        exit;
    }
    else if($status=='ban') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("你的用户已经被查封")</script>';
        exit;
    }
    else if($status=='nxuser') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("用户不存在")</script>';
        exit;
    }
    {echo '<script>location.href=location.href;</script>';exit;}
}
else if($_POST['isSubmit']=='register') {
    if(!_CT('can_register')) {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("注册错误：本站不开放注册！")</script>';
        exit;
    }
    if(!$_POST['pass']) {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("注册错误：不可以没有密码！")</script>';
        exit;
    }
    if($_POST['pass'] !== $_POST['passagain']) {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("注册错误：两次输入密码不匹配")</script>';
        exit;
    }
    if(!$_POST['name'] || strlen($_POST['name'])<3 || strlen($_POST['name'])>14) {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("注册错误：用户名长度只允许2到14")</script>';
        exit;
    }
    $status = uauth_register($_POST['name'],$_POST['pass'],false,'none');
    if($status == 'success') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("注册成功，输入用户名和密码以登录")</script>';
        exit;
    }
    else if($status == 'exist') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("注册错误：用户名已经被占用")</script>';
        exit;
    }
    else if($status == 'illegal') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("注册错误：用户名不合法")</script>';
        exit;
    }
    else if($status == 'loggedin') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("注册错误：你已经登录了")</script>';
        exit;
    }
    else if($status == 'limit') {
        echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("注册错误：你注册的账户数量已经到达限额")</script>';
        exit;
    }
    echo '<script>location.href=location.href.substring(0,location.href.indexOf("?"))+"?msg="+encodeURIComponent("未预料的注册信息：'.$status.'")</script>';
    exit;
}
else {echo '<script>location.href=location.href;</script>';exit;}

?>
<script>document.title='用户登录 - <?php echo htmlspecial2(_C()['app_name_title']) ?>';</script>
<div class="txmp-page-full">
    <h3>输入用户名和密码</h3>
    <?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
        <a href="javascript:;" onclick="F_HideNotice()">知道了</a>
    </p><?php } ?>
    <p><form method="post">
        <input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
        <input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
        <input type="hidden" name="isSubmit" value="login">
        <input style="margin-bottom:8px;" type="text" name="name" autocomplete="off" placeholder="用户名"><br>
        <input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="密码"><br>
        <input type="submit" class="am-btn am-btn-primary" value="登录">
    </form></p>
    <hr>
    <?php if(_CT('can_register')) { ?>
    <p>没有账户？</p>
    <p><form method="post">
        <input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
        <input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
        <input type="hidden" name="isSubmit" value="register">
        <input style="margin-bottom:8px;" type="text" name="name" autocomplete="off" placeholder="用户名"><br>
        <input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="密码"><br>
        <input style="margin-bottom:8px;" type="password" name="passagain" autocomplete="off" placeholder="密码"><br>
        <input type="submit" class="am-btn am-btn-danger <?php if(uauth_ip_cnt($_SERVER['REMOTE_ADDR']) >= _CT('ip_reg_limit')) echo 'am-disabled' ?>" value="注册">
    </form></p>
    <?php } else { ?>
    本站不开放注册
    <?php } ?>
</div>
