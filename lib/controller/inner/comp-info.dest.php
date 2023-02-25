<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

if(!isValidMusic(cid(),false)) {
	print404('No Such Music');
}
checkPermission('music/code', cid());
if(!isset($_GET['raw'])) {
	include_header();
	tpl("inner/comp_info");
	include_footer();
} else {
	header('Content-Type: application/json');
	$data = parseCmpLyric(cid(),true,true,'cmpi_ADD_ERROR_P');
	if(!is_array($data['message'])) {
		$data['message'] = [];
	}
	echo encode_data($data['message']);
}
return true;
