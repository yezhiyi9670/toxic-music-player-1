<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<script>
	document.title="<?php LNGe('err.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
	set_section_name(LNG('err.title'));
</script>
<div class="txmp-page-full">
	<h3><?php LNGe('err.title.404') ?></h3>
	<p><?php LNGe('err.404.try') ?></p>
	<ol>
		<li><?php LNGe('err.404.desc.refresh') ?></li>
		<li><?php LNGe('err.404.desc.slash') ?></li>
		<li><?php LNGe('err.404.desc.redundant') ?></li>
		<li><?php LNGe('err.404.desc.illegal') ?></li>
	</ol>
	<p><?php LNGe('err.404.desc.wd') ?><code><?php echo '404 ' . $GLOBALS['errorWord'] ?></code></p>
</div>
