<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<?php if(!file_exists(DATA_PATH . 'bc/about.php')) { ?>
<script>
    document.getElementById('about-this').onclick=function(){
        modal_alert('关于 <?php echo _C()['app_name'] ?>',`
        <?php echo _C()['app_name'] ?> 是 yezhiyi9670 的个人项目。现已弃用。<br>
            替代品是 Delta Music Player。欲了解信息，请点击下方版本号。<br>
            Delta Music Player 正在线下开发之中。<br>
            <span style="color:#777777"><a href="https://github.com/yezhiyi9670/toxic-music-player/" disabled target="_blank">Gayhub(不可访问)</a>&nbsp;·&nbsp;<a href="<?php echo BASIC_URL ?>changelog.php" target="_blank">v<?php echo VERSION ?></a></span>
        `);
    };
</script>
<?php } else require(DATA_PATH . 'bc/about.php'); ?>
