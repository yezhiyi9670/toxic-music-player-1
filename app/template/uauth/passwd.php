<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isset($_POST['isSubmit']));
else if($_POST['isSubmit']=='passwd') {
    $uname = uauth_username();
    if(!$uname) {
        redirectToNote('请先登录');
        exit;
    }
    else if(!uauth_veri_pass($uname,$_POST['pass'])) {
        redirectToNote('原密码输入错误');
        exit;
    }
    else if($_POST['newpass'] != $_POST['newpassagain']) {
        redirectToNote('输入密码不匹配');
        exit;
    }
    else {
        $status = uauth_update_pass($uname,$_POST['newpass']);
        if($status=='illegal') {
            redirectToNote('系统错误');
            exit;
        }
        else if($status=='nxuser') {
            redirectToNote('系统错误');
            exit;
        }
        else if($status=='success') {
            redirectToNote('操作成功');
            exit;
        }
        else {echo '<script>location.href=location.href;</script>';exit;}
    }
}
else if($_POST['isSubmit']=='remove') {
    $uname = uauth_username();
    if(!$uname) {
        redirectToNote('请先登录');
        exit;
    }
    else if(!uauth_veri_pass($uname,$_POST['pass'])) {
        redirectToNote('密码输入错误');
        exit;
    }
    else if($_POST['rm_uname'] != $uname) {
        redirectToNote('用户名输入错误');
        exit;
    }
    else {
        $status = uauth_delete($uname);
        if($status=='illegal') {
            redirectToNote('系统错误');
            exit;
        }
        else if($status=='nxuser') {
            redirectToNote('系统错误');
            exit;
        }
        else if($status=='success') {
            redirectToNote('操作成功');
            exit;
        }
        else {echo '<script>location.href=location.href;</script>';exit;}
    }
}
else {echo '<script>location.href=location.href;</script>';exit;}

?>
<script>document.title='修改密码 - <?php echo htmlspecial2(_C()['app_name_title']) ?>';</script>
<div class="txmp-page-full">
    <h3>修改密码</h3>
    <?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
        <a href="javascript:;" onclick="F_HideNotice()">知道了</a>
    </p><?php } ?>
    <?php if(uauth_username()) { ?>
    <p>填写表单，完成密码修改</p>
    <p><form method="post">
        <input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
        <input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
        <input type="hidden" name="isSubmit" value="passwd">
        <input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="旧密码"><br>
        <input style="margin-bottom:8px;" type="password" name="newpass" autocomplete="off" placeholder="密码"><br>
        <input style="margin-bottom:8px;" type="password" name="newpassagain" autocomplete="off" placeholder="重新输入密码"><br>
        <input type="submit" class="am-btn am-btn-danger <?php if(uauth_ip_cnt($_SERVER['REMOTE_ADDR']) >= _CT('ip_reg_limit')) echo 'am-disabled' ?>" value="修改密码！">
    </form></p>
    <hr>
    <p>删除用户（请慎用。您将不可恢复地丢失所有保存的数据和设置）</p>
    <p><form method="post">
        <input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
        <input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
        <input type="hidden" name="isSubmit" value="remove">
        <input style="margin-bottom:8px;" type="text" name="rm_uname" autocomplete="off" placeholder="重复一遍用户名：<?php echo uauth_username() ?>"><br>
        <input style="margin-bottom:8px;" type="password" name="pass" autocomplete="off" placeholder="密码"><br>
        <input type="submit" class="am-btn am-btn-danger <?php if(uauth_ip_cnt($_SERVER['REMOTE_ADDR']) >= _CT('ip_reg_limit')) echo 'am-disabled' ?>" value="删除我的账户">
    </form></p>
    <?php } else { ?>
    请先登录
    <?php } ?>
</div>
