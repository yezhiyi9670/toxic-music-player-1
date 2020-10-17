<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<?php if(!file_exists(DATA_PATH . 'bc/about.php')) { ?>
<script>
    document.getElementById('about-this').onclick=function(){
        modal_alert('关于 <?php echo _C()['app_name'] ?>',`
        <?php echo _C()['app_name'] ?> 是 yezhiyi9670 的个人项目。<br />
            <span style="color:#777777"><a href="https://github.com/yezhiyi9670/toxic-music-player/" disabled target="_blank">Gayhub</a>&nbsp;·&nbsp;<?php if(file_exists(CHANGELOG)) { ?><a href="<?php echo BASIC_URL ?>version-history" target="_blank">v<?php echo VERSION ?></a><?php } else { ?>v<?php echo VERSION ?><?php } ?>
            </span>
        `);
    };
</script>
<?php } else require(DATA_PATH . 'bc/about.php'); ?>
