<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

	$isNewLook = setting_gt('new-look','Y');
	$isNewLook = ($isNewLook == 'Y' || $isNewLook == 'y');
	$isLimSel = setting_gt('limited-selection','N');
	$isLimSel = ($isLimSel == 'Y' || $isLimSel == 'y');
?>
<head>
	<title><?php LNGe('ui.loading') ?> - <?php echo htmlspecial2(_CT('app_name_title')) ?></title>
	<?php if(is_wap()){ ?><meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/><?php } ?>
	<meta name="description" value="<?php LNGe('meta.description') ?>">
	<?php
		// --- load css ---
		if(!_CT('offline_usage')) {
			load_css_e('https://cdn.jsdelivr.net/gh/yezhiyi9670/akioi-cdn/fonts/SakuraFonts-v5/part-1.css');
			load_css_e('https://cdn.jsdelivr.net/gh/yezhiyi9670/akioi-cdn/fonts/Common/Noto%20Sans%20SC/index.css');
		} else {
			load_css('cdn-clone/fonts/SakuraFonts-v5/part-1','','5');
			load_css('cdn-clone/fonts/Common/Noto%20Sans%20SC/index','','5');
		}
		load_css('amazeui/amazeui.min','','2.7.2');
		load_css('fa/css/font-awesome.min','','4');
		if($isNewLook) {
			load_css('css/common/newlook','w');
		}
		if($isLimSel) {
			load_css('css/common/limited-selection');
		}
		load_css('css/common/pr-rule-editor');
		load_css('css/common/main','wc',VERSION,'mainColoredCss');

	?>

	<style>
		<?php
			// --- font style ---
			printFontStyle('global-font','GlobalFont','body');
			printFontStyle('lyric-font-formal','LyricFormalFont','.lrc-item');
			printFontStyle('lyric-font-title','LyricTitleFont','.para-title');
			printFontStyle('lyric-font-comment','LyricCommentFont','i>.lrc-item');
			printFontStyle('title-font','TitleFont','.header-title-text, .song-title');
			printFontStyle('input-font','InputFont','input[type=text], input[type=password], input[type=select], input[type=number], select, .follow-field');
		?>
	</style>

	<?php
		// --- load js ---
		load_js('lib/jquery-3.3.1.min','3.3.1');
		load_js('amazeui/amazeui.min','2.7.2');
		load_js_e(BASIC_URL . 'i18n-script?v=' . VERSION);
		load_js('js/common/functions');
		load_js('lib/autoln');
		load_js('lib/md5');
		load_js('lib/base64');
		load_js('lib/amazeui-modal');
		load_js('lib/async-worker');
		load_js('lib/pr-rule-editor');
	?>
	<script>
		// --- global data ---
		G = {};
		G.basic_url = '<?php echo BASIC_URL ?>';
		G.username = '<?php echo uauth_username(); ?>';
		G.csrf_s1 = '<?php echo $GLOBALS['sess']; ?>';
		G.csrf_s2 = '<?php echo $GLOBALS['token']; ?>';
		G.version = '<?php echo VERSION; ?>';
		G.csv_version = '<?php echo CSV_VERSION; ?>';
		G.is_wap = <?php echo is_wap() ? 'true' : 'false' ?>;
		G.app_name = '<?php echo addslashes(_CT('app_name')) ?>';
		G.app_title = '<?php echo addslashes(_CT('app_title')) ?>';
		G.app_prefix = '<?php echo APP_PREFIX ?>';
	</script>
	<script>
		// --- audio url ---
		var yp_src='<?php echo getAudioUrl(preSubstr($_GET['_lnk'])) ?>';
	</script>
</head>

<body>
	<?php if(!isset($_GET['iframe'])) { ?><header data-am-widget="header" class="am-header am-header-fixed txmp-header">
		<h1 class="am-header-title txmp-header-title">
			<i class="fa fa-music txmp-r-ico"></i><span class="header-title-text" onclick="location.href='<?php
				if(preSubstr($GLOBALS['linktype'])=='music') {
					echo BASIC_URL.preSubstr($_GET['_lnk']);
				}
				else if(preSubstr($GLOBALS['linktype'])=='admin') {
					echo BASIC_URL.'admin';
				}
				else {
					echo BASIC_URL;
				}
			?>'"><?php echo _CT('app_name') ?></span>
		</h1>
		<div class="am-header-right am-header-nav txmp-nav-icos">
			<span class="am-dropdown language-switcher" data-am-dropdown>
				<a class="am-dropdown-toggle language-switcher-a" title="<?php echo LNG('ui.menu.language') ?>"><i class="fa fa-globe"></i></a>

				<ul class="am-dropdown-content language-list" onclick="$('.am-dropdown').dropdown('close')">
					<?php
						$language_list = getSupportedLanguage();
						foreach($language_list as $k => $v) {
							if(userLanguage() == $k) echo '<li class="am-active">';
							else echo '<li>';
							echo '<a onclick="switchLanguage(\'' . $k . '\')">';
							echo $v;
							echo ' (' . $k . ')';
							echo '</a>';
							echo '</li>';
						}
					?>
				</ul>
			</span>

			<a style="padding-right:18px; border-right:2px solid #FFF; margin-right:4px;" href="<?php echo BASIC_URL ?>user" title="<?php
				if(uauth_username()) {
					echo uauth_username();
				} else {
					LNGe('uauth.ui.guest');
				}
			?>"><i class="fa fa-user"></i><?php if(!is_wap()) { ?>&nbsp;<span style="font-size:0.7em;vertical-align:top;"><?php
				if(uauth_username()) {
					echo uauth_username();
				} else {
					LNGe('uauth.ui.guest');
				}
			?></span><?php } ?></a>

			<?php if(is_root()) { ?><a href="<?php echo BASIC_URL ?>admin" title="<?php LNGe('ui.menu.admin') ?>"><i class="fa fa-key"></i></a><?php } ?>

			<a href="<?php echo BASIC_URL ?>" title="<?php LNGe('ui.menu.main') ?>"><i class="fa fa-home"></i></a>

			<a href="javascript:void;" id="about-this" title="<?php LNGe('ui.menu.about') ?>"><i class="fa fa-info-circle"></i></a>

			<a href="javascript:;" id="right-fold-menu" title="<?php LNGe('ui.menu.menu') ?>" style="display:none" onclick="rmenu_show()">
				<i class="fa fa-folder-open"></i>
			</a>

			<?php tpl("common/about") ?>
		</div>
	</header>
	<?php } ?>

	<div class="txmp-page-main">
