<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<script>
    document.title="出问题了 - <?php echo htmlspecial2(_C()['app_name_title']) ?>";
</script>
<div class="txmp-page-full">
    <h3>401 - 拒绝访问</h3>
    <p>你没有查看此页面的权限。</p>
    <?php if(!is_root(false)) { ?>
    <p>该页面由管理员设置禁止访问，你是管理员？请自行找到登录入口。</p>
    <?php } else { ?>
    <p>你已登录管理员，但是你正在使用游客权限测试本页面。<a onclick="no_deauth()">使用管理员权限访问</a></p>
    <?php } ?>
    <p>HTTP 错误代码：<code><?php echo '401 ' . $GLOBALS['errorWord'] ?></code></p>
    <script>
        function no_deauth() {
            window.location.href = window.location.href.replace("?deauth","").replace("&deauth","");
        }
    </script>
</div>
