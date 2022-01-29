<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<script>
	document.title="<?php LNGe('err.title') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?>";
	set_section_name(LNG('err.title'));
</script>
<div class="txmp-page-full">
	<h3><?php LNGe('err.title.401') ?></h3>
	<p><?php LNGe('err.401.tip') ?></p>
	<p><?php LNGe('err.401.try') ?></p>
	<ul>
		<?php if(!is_root(false)) { ?>
		<li><?php LNGe('err.401.desc.login_root') ?></li>
		<li><?php LNGe('err.401.desc.perm_disabled') ?></li>
		<li><?php LNGe('err.401.desc.private_pl_root') ?></li>
		<?php } else { ?>
		<?php if(isset($_GET['deauth'])) { ?>
			<li><?php echo LNG('err.401.desc.no_deauth') ?></li>
		<?php } ?>
		<li><?php LNGe('err.401.desc.private_pl') ?></li>
		<?php } ?>
	</ul>
	<p><?php LNGe('err.401.desc.wd') ?><code><?php echo '401 ' . $GLOBALS['errorWord'] ?></code></p>
	<script>
		function no_deauth() {
			window.location.href = window.location.href.replace("?deauth","").replace("&deauth","");
		}
	</script>

	<div></div>
</div>
