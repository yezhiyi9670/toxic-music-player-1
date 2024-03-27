<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

	$isNewLook = true;
	$isLimSel = setting_gt('limited-selection','N');
	$isLimSel = ($isLimSel == 'Y' || $isLimSel == 'y');
	$isAgOp = setting_gt('aggressive-optimize','N');
	$isAgOp = ($isAgOp == 'Y' || $isAgOp == 'y');
?><!DOCTYPE HTML>
<head>
	<title><?php LNGe('ui.loading') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?></title>
	<?php if(is_wap()){
		$wap_scale = max(0.6,min(1.8,setting_gt('wap-scale','0.92')));
		if(floor($wap_scale * 100) == $wap_scale * 100 && $wap_scale != 0) $wap_scale += 0.001; ?><meta name="viewport" content="width=device-width, initial-scale=<?php echo $wap_scale ?>, user-scalable=no, minimum-scale=<?php echo $wap_scale ?>, maximum-scale=<?php echo $wap_scale ?>"/><?php } ?>
	<meta name="description" value="<?php LNGe('meta.description') ?>">
	<?php
		// --- load css ---
		if(!_CT('offline_usage')) {
			load_css_e('https://fastly.jsdelivr.net/gh/yezhiyi9670/akioi-cdn/fonts/SakuraFonts-v5/part-1.css');
			load_css_e('https://fastly.jsdelivr.net/gh/yezhiyi9670/akioi-cdn/fonts/Common/Noto%20Sans%20SC/index.css');
		} else {
			load_css('cdn-clone/fonts/SakuraFonts-v5/part-1','','5');
			load_css('cdn-clone/fonts/Common/Noto%20Sans%20SC/index','','5');
		}
		load_css('amazeui/amazeui.min','','2.7.2');
		load_css('fa/css/font-awesome.min','','4');
		load_css('css/common/optimize');
		if($isAgOp) {
			load_css('css/common/aggressive-optimize');
		}
		if($isLimSel) {
			load_css('css/common/limited-selection');
		}
		load_css('css/common/pr-rule-editor');
		load_css('css/common/main','wc',VERSION,'mainColoredCss');
		if($isNewLook) {
			load_css('css/common/newlook','w');
		}
		load_css('codemirror/lib/codemirror','5.59.2');
	?>

	<style>
		<?php
			// --- font style ---
			printFontStyle('global-font','GlobalFont','body');
			printFontStyle('lyric-font-formal','LyricFormalFont','.lrc-item');
			printFontStyle('lyric-font-title','LyricTitleFont','.para-title');
			printFontStyle('lyric-font-comment','LyricCommentFont','i>.lrc-item');
			printFontStyle('title-font','TitleFont','.header-title-text, .song-title');
			printFontStyle('input-font','InputFont','input[type=text], input[type=password], select.field-large, input[type=number], select, .follow-field');
		?>
	</style>

	<?php
		// --- load js ---
		load_js('lib/jquery-3.3.1.min','3.3.1');
		load_js('amazeui/amazeui.min','2.7.2');
		load_js_e(BASIC_URL . 'i18n-script?v=' . VERSION);
		load_js('js/common/functions');
		// load_js('js/common/wcl-dynamicstyles');
		load_js('js/common/garbage_cleaner');
		load_js('lib/md5');
		load_js('lib/base64');
		load_js('lib/amazeui-modal');
		load_js('lib/toasts');
		load_js('lib/scroller');
		load_js('lib/async-worker');
		load_js('lib/pr-rule-editor');
		load_js('codemirror/lib/codemirror','5.59.2');
	?>
	<script>
		// --- global data ---
		window.G = {};
		G.basic_url = '<?php echo BASIC_URL ?>';
		G.username = '<?php echo uauth_username(); ?>';
		G.csrf_s1 = '<?php echo $GLOBALS['sess']; ?>';
		G.csrf_s2 = '<?php echo $GLOBALS['token']; ?>';
		G.version = '<?php echo VERSION; ?>';
		G.csv_version = '<?php echo CSV_VERSION; ?>';
		G.is_wap = <?php echo is_wap() ? 'true' : 'false' ?>;
		G.is_real_wap = <?php echo is_real_wap() ? 'true' : 'false' ?>;
		G.is_iframe = <?php echo isset($_GET['iframe']) ? 'true' : 'false' ?>;
		G.app_name = '<?php echo jsspecial(_CT('app_name')) ?>';
		G.app_title = '<?php echo jsspecial(_CT('app_name_title')) ?>';
		G.app_prefix = '<?php echo APP_PREFIX ?>';
		G.dataver = '<?php echo DATAVER ?>';
		G.can_pay_play = <?php echo rp_can_pay_play() ? 'true' : 'false' ?>;
		G.TS_IS_COMMENT = <?php echo TS_IS_COMMENT ?>;
	</script>
	<script>
		// --- audio url ---
		var yp_src='<?php echo getAudioUrl(cid()) ?>';
	</script>
	<?php
		// 自定义头部插入内容
		if(file_exists(DATA_PATH.'bc/head.html')) {
			require(DATA_PATH.'bc/head.html');
		}
	?>
	<script>
		/**
		 * 目前没有可用的追踪事件
		 */
		if(!window.sendAnalyticsEvent) {
			window.sendAnalyticsEvent = (name) => {}
		}
	</script>
</head>

<body>
	<?php if(!isset($_GET['iframe'])) { ?><header data-am-widget="header" class="am-header am-header-fixed txmp-header">
		<h1 class="am-header-title txmp-header-title">
			<?php
				$backlink = '';
				if(preSubstr($GLOBALS['linktype'])=='music') {
					$backlink = BASIC_URL.cid();
				}
				else if(preSubstr($GLOBALS['linktype'])=='admin') {
					$backlink = BASIC_URL.'admin';
				}
				else {
					$backlink = BASIC_URL;
				}
			?>
			<i class="fa fa-music txmp-r-ico"></i><span class="header-title-text" onclick="location.href='<?php echo $backlink ?>'">
				<span class="header-title-app-name">
					<?php echo _CT('app_name') ?>
				</span>
				<span class="header-title-section-name">
					<?php LNGe('ui.loading') ?>
				</span>
				<span>&nbsp;</span>
			</span>
		</h1>
		<div class="am-header-right am-header-nav txmp-nav-icos">
			<span class="am-dropdown header-dropdown" data-am-dropdown>
				<a class="am-dropdown-toggle header-dropdown-a" title="<?php echo LNG('ui.menu.language') ?>"><i class="fa fa-globe"></i></a>

				<ul class="am-dropdown-content language-list" onclick="$('.am-dropdown').dropdown('close')">
					<li class="am-dropdown-header"><?php LNGe('ui.menu.language') ?></li>
					<?php
						$language_list = getSupportedLanguage();
						foreach($language_list as $k => $v) {
							if(userLanguage() == $k) echo '<li class="am-active">';
							else echo '<li>';
							echo '<a onclick="switchLanguage(\'' . $k . '\')">';
							if($k != 'ky_cd') echo '<i class="fa fa-flag"></i> ';
							else echo '<i class="fa fa-flag-o"></i> ';
							echo $v;
							echo ' (' . $k . ')';
							echo '</a>';
							echo '</li>';
						}
					?>
				</ul>
			</span>

			
			<?php
				$curr_username = uauth_username();
				$label = '';
				if($curr_username) {
					$label = $curr_username;
				} else {
					$label = htmlspecial2(LNG('uauth.ui.login'));
				}
			?>
			<?php if($curr_username) { ?>
				<span class="am-dropdown header-dropdown" data-am-dropdown>
					<a class="am-dropdown-toggle header-dropdown-a txmp-nav-ico-user" title="<?php echo $label ?>"><i class="fa fa-user"></i><?php if(!is_wap()) { ?>&nbsp;<span style="font-size:0.7em;vertical-align:top;"><?php echo $label ?></span><?php } ?></a>

					<ul class="am-dropdown-content" onclick="$('.am-dropdown').dropdown('close')">
						<li class="am-dropdown-header"><?php LNGe('ui.menu.user') ?></li>
						<li><a href="<?php echo BASIC_URL ?>user"><i class="fa fa-user"></i> <?php LNGe('ui.menu.user.center') ?></a></li>
						<li><a href="<?php echo BASIC_URL ?>user/passwd"><i class="fa fa-cog"></i> <?php LNGe('ui.menu.user.pass') ?></a></li>
						<li><a href="<?php echo BASIC_URL ?>user/logout"><i class="fa fa-sign-out"></i> <?php LNGe('ui.menu.user.logout') ?></a></li>
					</ul>
				</span>
			<?php } else { ?>
				<a class="txmp-nav-ico-user" title="<?php echo $label ?>" href="<?php echo BASIC_URL ?>user/login"><i class="fa fa-sign-in"></i><?php if(!is_wap()) { ?>&nbsp;<span style="font-size:0.7em;vertical-align:top;"><?php echo $label ?></span><?php } ?></a>
			<?php } ?>

			<?php if(is_root()) { ?>
				<span class="am-dropdown header-dropdown" data-am-dropdown>
					<a class="am-dropdown-toggle header-dropdown-a" title="<?php echo LNG('ui.menu.admin') ?>"><i class="fa fa-key"></i></a>

					<ul class="am-dropdown-content" onclick="$('.am-dropdown').dropdown('close')">
						<li class="am-dropdown-header"><?php LNGe('ui.menu.admin') ?></li>
						<li><a href="<?php echo BASIC_URL ?>admin"><i class="fa fa-list"></i> <?php LNGe('ui.menu.admin.music') ?></a></li>
						<li><a href="<?php echo BASIC_URL ?>admin/users"><i class="fa fa-user-md"></i> <?php LNGe('ui.menu.admin.user') ?></a></li>
					</ul>
				</span>
			<?php } ?>

			<a href="<?php echo BASIC_URL ?>" title="<?php LNGe('ui.menu.main') ?>"><i class="fa fa-home"></i></a>

			<a href="javascript:;" id="about-this" title="<?php LNGe('ui.menu.about') ?>"><i class="fa fa-info-circle"></i></a>

			<a href="javascript:;" id="right-fold-menu" title="<?php LNGe('ui.menu.menu') ?>" style="display:none" onclick="rmenu_show()">
				<i class="fa fa-folder-open"></i>
			</a>

			<?php tpl("common/about") ?>
		</div>
	</header>
	<?php } ?>

	<div class="txmp-page-main">
