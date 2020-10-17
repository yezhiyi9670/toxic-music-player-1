<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(isset($_POST['isSubmit'])) {
    $status=root_login($_POST['code']);
    if(!$status) {
        redirectToNote('密码错误！');
        exit;
    }
    redirectToGet();
}

?>
<script>document.title='登录歌单管理 - <?php echo htmlspecial2(_C()['app_name_title']) ?>';</script>
<div class="txmp-page-full">
    <h3>输入管理密码</h3>
    <?php if(isset($_GET['msg'])) { ?><p id="head-notice"><?php echo htmlspecial($_GET['msg']) ?>
        <a href="javascript:;" onclick="F_HideNotice()">知道了</a>
    </p><?php } ?>
    <p><form method="post">
        <input type="hidden" name="csrf-token-name" value="<?php echo $GLOBALS['sess'] ?>">
        <input type="hidden" name="csrf-token-value" value="<?php echo $GLOBALS['token'] ?>">
        <input type="hidden" name="isSubmit" value="true">
        <input type="password" name="code" autocomplete="off" placeholder="管理员密码">
        <input type="submit" class="am-btn am-btn-primary" value="登录">
    </form></p>
</div>
