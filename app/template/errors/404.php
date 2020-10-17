<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<script>
	document.title="出问题了 - <?php echo htmlspecial2(_C()['app_name_title']) ?>";
</script>
<div class="txmp-page-full">
	<h3>404 - 页面不存在</h3>
	<p>如果你在访问 RemotePlay 歌曲，请尝试刷新页面。</p>
	<p>注意播放器的网址使用的是伪静态网址识别技术，网址后存在多余的<code>/</code>也会被认为404。</p>
	<p>HTTP 错误代码：<code><?php echo '404 ' . $GLOBALS['errorWord'] ?></code></p>
</div>
