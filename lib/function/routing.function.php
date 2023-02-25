<?php if(!defined('IN_SYSTEM')) exit;//Silence is golden ?><?php

// 检查ROOT用户（不返回，错误即转至401页面）
function checkROOT() {
	if(!is_root()) {
		print401("Permission Denied");
	}
}

// 是否为不被页头页脚包装的内部请求
function isUnwrapped() {
	return isset($_REQUEST['isSubmit']) || isset($_GET['raw']);
}
// 是否为不包装的 AJAX 请求
function isAjaxRequest() {
	return isUnwrapped() && isset($_REQUEST['isAjax']);
}

// 输出401错误并终止
function print401($word = 'Require Authentication') {
	@header("HTTP/1.1 401 " . $word);
	if(isUnwrapped()) {
		if(!isAjaxRequest()) echo 'ERROR 401 ' . $word;
		else redirectToNote('ERROR 401 ' . $word);
	} else {
		$GLOBALS['errorWord'] = $word;
		tpl("common/header");
		tpl("errors/401");
		tpl("common/footer");
	}
	exit;
}
// 输出404错误并终止
function print404($word = 'Not Found') {
	@header("HTTP/1.1 404 " . $word);
	if(isUnwrapped()) {
		if(!isAjaxRequest()) echo 'ERROR 404 ' . $word;
		else redirectToNote('ERROR 404 ' . $word);
	} else {
		$GLOBALS['errorWord'] = $word;
		tpl("common/header");
		tpl("errors/404");
		tpl("common/footer");
	}
	exit;
}
// 包含页头（自动跳过非包装页面）
function include_header() {
	if(!isUnwrapped()) tpl('common/header');
}
// 包含页脚（自动跳过非包装页面）
function include_footer() {
	if(!isUnwrapped()) tpl('common/footer');
}
// 删除第一级 URL 以将 URL 传递给下一级 router
function stripFirstUrl($str) {
	if(strpos($str,'/') === false) {
		return '';
	}
	return substr(strstr($str,'/'),1);
}

// 检查URL耗尽
function checkUrlEnd($t) {
	if($t != '') {
		print404('Redundant Url Fragment');
	}
}
