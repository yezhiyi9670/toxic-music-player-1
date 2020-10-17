<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

	$isNewLook = setting_gt('new-look','Y');
	$isNewLook = ($isNewLook == 'Y' || $isNewLook == 'y');
	$isLimSel = setting_gt('limited-selection','N');
	$isLimSel = ($isLimSel == 'Y' || $isLimSel == 'y');
?>
<head>
	<title>加载中 - <?php echo htmlspecial2(_C()['app_name_title']) ?></title>
	<?php if(is_wap()){ ?><meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"/><?php } ?>
	<meta name="description" value="私人与演示页面，无参考价值。">
	<script src="<?php echo BASIC_URL ?>static/js/common/jquery-3.3.1.min.js"></script>
	<!--移动端改进用户体验的备选字体-->
	<?php if(!_CT('offline_usage')) { ?><link href="https://cdn.jsdelivr.net/gh/yezhiyi9670/akioi-cdn/fonts/SakuraFonts-v5/part-1.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/gh/yezhiyi9670/akioi-cdn/fonts/Common/Noto%20Sans%20SC/index.css" rel="stylesheet"><?php } else { ?>
	<link href="<?php echo BASIC_URL ?>static/cdn-clone/fonts/SakuraFonts-v5/part-1.css" rel="stylesheet">
	<link href="<?php echo BASIC_URL ?>static/cdn-clone/fonts/Common/Noto%20Sans%20SC/index.css" rel="stylesheet"><?php } ?>
	<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/amazeui/amazeui.min.css?v=2.7.2">
	<script src="<?php echo BASIC_URL ?>static/amazeui/amazeui.min.js?v=2.7.2"></script>
	<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/css/common/main-colored.css.php?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>&A=X000000&S=X000000&G1=XNULL&G2=XNULL" id="mainBasicColoredCss">
	<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/css/common/main-colored.css.php?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>&A=X<?php echo htmlspecial2(GCM()['A']) ?>&S=X<?php echo htmlspecial2(GCM()['X']) ?>&G1=X<?php echo htmlspecial2(GCM()['G1']) ?>&G2=X<?php echo htmlspecial2(GCM()['G2']) ?>" id="mainColoredCss">
	<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/css/common/main.css.php?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>" id="mainCss">
	<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/fa/css/font-awesome.min.css">
	<script src="<?php echo BASIC_URL ?>static/js/common/functions.js.php?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>"></script>
	<script src="<?php echo BASIC_URL ?>static/lib/md5.js"></script>
	<script src="<?php echo BASIC_URL ?>static/lib/base64.js"></script>
	<script>
	var yp_src='<?php echo getAudioUrl(preSubstr($_GET['_lnk'])) ?>';
	</script>
	<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/css/common/toxic-dialog.css">
	<script src="<?php echo BASIC_URL ?>static/js/common/amazeui-modal.js?v=<?php echo VERSION ?>"></script>
	<script>
	G_username = '<?php echo uauth_username(); ?>';
	G_csrf_s1 = '<?php echo $GLOBALS['sess']; ?>';
	G_csrf_s2 = '<?php echo $GLOBALS['token']; ?>';
	G_version = '<?php echo VERSION; ?>';
	G_csv_version = '<?php echo CSV_VERSION; ?>';
	</script>
	<?php if($isNewLook) { ?>
	<!--New styles-->
	<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/css/common/newlook.css.php?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>&A=X000000&S=X000000">
	<?php } ?>
	<?php if($isLimSel) { ?>
	<!--Limited Selection-->
	<link rel="stylesheet" href="<?php echo BASIC_URL ?>static/css/common/limited-selection.css.php?v=<?php echo VERSION ?>&w=<?php echo is_wap() ?>">
	<?php } ?>
	<style>
	<?php printFontStyle('global-font','GlobalFont','body') ?>
	<?php printFontStyle('lyric-font-formal','LyricFormalFont','.lrc-item') ?>
	<?php printFontStyle('lyric-font-title','LyricTitleFont','.para-title') ?>
	<?php printFontStyle('lyric-font-comment','LyricCommentFont','i>.lrc-item') ?>
	<?php printFontStyle('title-font','TitleFont','.header-title-text, .song-title') ?>
	<?php printFontStyle('input-font','InputFont','input[type=text], input[type=password], input[type=select], input[type=number], select, .follow-field') ?>
	</style>
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
			?>'"><?php echo _C()['app_name'] ?></span>
		</h1>
		<div class="am-header-right am-header-nav txmp-nav-icos">
			<a style="padding-right:18px; border-right:2px solid #FFF; margin-right:4px;" href="<?php echo BASIC_URL ?>user" title="<?php
				if(uauth_username()) {
					echo uauth_username();
				} else {
					echo '未登录';
				}
			?>"><i class="fa fa-user"></i>&nbsp;<span style="font-size:0.7em;vertical-align:top;"><?php
				if(uauth_username()) {
					echo uauth_username();
				} else {
					echo '未登录';
				}
			?></span></a>

			<?php if(is_root()) { ?><a href="<?php echo BASIC_URL ?>admin" title="后台管理"><i class="fa fa-key"></i></a><?php } ?>

			<a href="<?php echo BASIC_URL ?>" title="主页"><i class="fa fa-home"></i></a>

			<a href="javascript:void;" id="about-this" title="关于"><i class="fa fa-info-circle"></i></a>

			<a href="javascript:;" id="right-fold-menu" title="菜单" style="display:none" onclick="rmenu_show()">
				<i class="fa fa-folder-open"></i>
			</a>

			<?php tpl("common/about") ?>
		</div>
	</header>
	<?php } ?>

	<div class="txmp-page-main">
