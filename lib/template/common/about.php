<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?>
<script>
	document.getElementById('about-this').onclick=function(){
		modal_alert(LNG('ui.about',G.app_name),LNG('ui.about.tip',G.app_name,G.dataver)+`<br />
		    <span style="color:#777777"><a href="https://github.com/yezhiyi9670/toxic-music-player-1/" target="_blank">Gayhub</a>&nbsp;·&nbsp;<?php if(file_exists(CHANGELOG)) { ?><a href="<?php echo BASIC_URL ?>version-history" target="_blank">v<?php echo VERSION ?></a><?php } else { ?>v<?php echo VERSION ?><?php } ?>
		    </span>
		`);
	};
</script>
