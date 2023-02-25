<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(),false)) {
	print404('No Such Music');
}
checkPermission('music/code', cid());
if(!isset($_REQUEST['raw'])) {
	include_header();
	tpl("inner/code");
	include_footer();
} else {
	header('Content-Type: text/plain');
	if(!isset($_REQUEST['lrc'])) {
		echo getLyricFile(cid());
	} else {
		$GLOBALS['lrcopt'] = [
			'delta' => clampLimit($_REQUEST['delta'] ?? null,0,0.1), // 偏移量
			'comment' => clampLimit($_REQUEST['comment'] ?? null,0.7,0.1,0,65535), // 注释展示时长
			'precision' => clampLimit($_REQUEST['precision'] ?? null,0.1,0.1,0.1,60.0), // 基准精度
		];
		if($_REQUEST['lrc'] == 'minified') {
			echo buildMinifiedLrc(json_decode(parseCmpLyric(cid(),false),true));
		} else if($_REQUEST['lrc'] == 'fancy') {
			echo buildExtendedLrc(json_decode(parseCmpLyric(cid(),false),true));
		} else {
			print404('Parameter Lrc');
		}
	}
}
